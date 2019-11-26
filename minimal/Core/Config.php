<?php

namespace Minimal\Core;

class Config
{

    private static $_init = null;
    private static $_data = [];

    private function __construct()
    {

    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    public static function init()
    {
        if (is_null(self::$_init)) {
            self::$_init = new self();
        }
        return self::$_init;
    }

    public static function set($name, $value = null)
    {
        if (is_array($name)) {
            self::$_data = array_merge(self::$_data, $name);
        } else {
            self::$_data[$name] = $value;
        }
    }

    public static function get($key, $default = null)
    {
        return isset(self::$_data[$key]) ? self::$_data[$key] : $default;
    }
}