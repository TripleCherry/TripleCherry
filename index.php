<?php

error_reporting(E_ALL & ~E_NOTICE);

require __DIR__ . '/vendor/autoload.php';

$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

function __autoload($className) {

    $prefix = 'DoubleCherry\\';

    $len = strlen($prefix);
    if (strncmp($prefix, $className, $len) !== 0) {
        return;
    }

    $relative_class = substr($className, $len);

    $file = __DIR__ . '/src/classes/' . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
}



$Debug = new \DoubleCherry\Debug;
$start = $Debug->getTime();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/sources/functions.php';
require_once __DIR__ . '/sources/mySQL.php';

$engine = new FUNCTIONS;
$print = new display();
$DB = new db_driver;
$DB->connect();

$engine->input = $engine->parse_incoming();
$engine->start = $start;

$url_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri_parts = explode('/', trim($url_path));

switch ($uri_parts[1]) {

    case 'articles':

        $act = 'index';
        $parameter = @array_pop($uri_parts);
        $instruction = @implode('/', $uri_parts);
        $category = str_replace("/articles/", "", $instruction);
        //     die($category);
        if (!$parameter) $parameter = NULL;
        $instruction = '/articles';
        break;

    case 'admin_':
        $session = new session();

        $value = 5;
        $name = "is_auth";
        $session->set_my_cookie($name, $value);

        if ($session->get_my_cookie('is_auth' == 5)) {

            $act = 'index';
            $parameter = @array_pop($uri_parts);
            $instruction = @implode('/', $uri_parts);
            if (!$parameter) $parameter = NULL;
            if (!$instruction) $instruction = '/';
        }

        else {

            die("Who are you?");
        }

        break;

    default:
        $act = 'index';
        $parameter = @array_pop($uri_parts);
        $instruction = @implode('/', $uri_parts);
        if (!$parameter) $parameter = NULL;
        if (!$instruction) $instruction = '/';
        break;
}

$query = "SELECT name, value FROM tde_config";
$DB->query($query);

while ($row = $DB->fetch_array()) {

    $engine->variables->$row['name'] = $row['value'];

}

if ($act == 'index') {

    $query = "SELECT json_schema, json_schema_wp FROM `tde_html_scheme` WHERE full_path  = '$instruction' LIMIT 1";
    $DB->query($query);

    if ($row = $DB->fetch_array()) {

        if (isset($parameter)) {

            $Items = json_decode($row['json_schema_wp'], true);

        }
        else {

            $Items = json_decode($row['json_schema'], true);

        }

        $html = $engine->htmlConstruct($Items);
        $print->do_output($html);

    } else {

        $print->make_404();

    }
}