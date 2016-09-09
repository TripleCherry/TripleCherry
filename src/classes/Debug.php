<?php

namespace DoubleCherry;

class Debug
{

    function getTime($time = false)
    {
        return $time === false? microtime(true) : microtime(true) - $time;
    }

}