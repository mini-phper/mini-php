<?php

namespace Minimal\Core;
class Route
{
    private static $_init = null;
    private static $_group_stack = [];
    private static $_uri_map = [];

    protected static $request_uri = '/';
    protected static $app_name = 'Home';
    protected static $ctl_name = '';
    protected static $act_name = '';


    private function __construct()
    {

    }

    public static function init()
    {
        if (is_null(self::$_init)) {
            self::$_init = new self();
        }
        return self::$_init;
    }

    public static function get($uri, $action)
    {
        list($uri, $action) = self::setUriAction($uri, $action);
        self::$_uri_map[$uri]['get'] = $action;
    }

    public static function post($uri, $action)
    {
        list($uri, $action) = self::setUriAction($uri, $action);
        self::$_uri_map[$uri]['post'] = $action;
    }

    public static function match($methods, $uri, $action)
    {
        list($uri, $action) = self::setUriAction($uri, $action);
        foreach ($methods as $method) {
            self::$_uri_map[$uri][$method] = $action;
        }
    }

    public static function any($uri, $action)
    {
        list($uri, $action) = self::setUriAction($uri, $action);
        self::$_uri_map[$uri]['any'] = $action;
    }

    public static function resource($uri, $action)
    {
        list($uri, $action) = self::setUriAction($uri, $action);

        self::$_uri_map[$uri]['get'] = $action . '@index';
        self::$_uri_map[$uri]['post'] = $action . '@save';
        self::$_uri_map[$uri . '/all']['get'] = $action . '@all';
        self::$_uri_map[$uri . '/edit']['get'] = $action . '@edit';
        self::$_uri_map[$uri . '/show']['get'] = $action . '@show';
        self::$_uri_map[$uri . '/delete']['post'] = $action . '@destroy';
    }

    /*namespace prefix*/
    public static function group($params, $callback)
    {
        array_push(self::$_group_stack, $params);
        call_user_func($callback);
        array_pop(self::$_group_stack);
    }


    public function load($path_info, $method)
    {
        $this->parsePathInfo($path_info);
        @require_once(DIR_PATH . '/route/' . strtolower(self::$app_name) . '.php');
        if ($this->foundRoute($method) === false) {
            throw new \Exception('Not Found', '404');
        }
        return $this;
    }

    public function generate($url = '', $params = null)
    {
        $url = empty($url) ? self::$ctl_name . '/' . self::$act_name : $url;
        $url = $url == '/' ? '/' . self::$app_name : '/' . self::$app_name . '/' . $url;
        if (!empty($params)) {
            $url .= '?' . (is_array($params) ? http_build_query($params) : $params);
        }
        return $url;
    }

    public function getClassName()
    {
        return join('\\', ['App', self::$app_name, 'Controller', self::$ctl_name]);
    }

    public function getAppName()
    {
        return self::$app_name;
    }

    public function getCtlName()
    {
        return self::$ctl_name;
    }

    public function getActName()
    {
        return self::$act_name;
    }

    public function getRequestUri()
    {
        return self::$request_uri;
    }


    private static function setUriAction($uri, $action)
    {
        $namespaces = [];
        $prefixes = [];
        foreach (self::$_group_stack as $group) {
            if (isset($group['namespace'])) {
                $namespaces[] = trim($group['namespace'], '\\');
            }
            if (isset($group['prefix'])) {
                $prefixes[] = trim($group['prefix'], '/');
            }
        }

        array_push($prefixes, $uri);
        array_push($namespaces, $action);

        $key = join('/', $prefixes);
        $key = $key == '/' ? '/' : '/' . $key;

        return [$key, '\\' . join('\\', $namespaces)];
    }


    /*(/index.php/app/ctl/act)*/
    private function parsePathInfo($path_info)
    {
        /*去掉index.php*/
        if (strpos($path_info, '.php') !== false) $path_info = strstr($path_info, '.php');
        if (!preg_match('|^[/a-zA-Z0-9_]+$|', $path_info)) return null;
        $path_info = trim($path_info, '/');
        if (empty($path_info)) return null;
        /*解析app_name*/
        $paths = explode('/', $path_info);
        $app_name = array_shift($paths);
        if (file_exists(DIR_PATH . '/route/' . strtolower($app_name) . '.php')) {
            self::$app_name = $app_name;
        } else {
            array_unshift($paths, $app_name);
        }
        /*解析request_uri*/
        if (empty($paths)) {
            self::$request_uri = '/';
        } else {
            self::$request_uri = '/' . join('/', $paths);
        }
    }

    private function foundRoute($method)
    {
        if (!isset(self::$_uri_map[self::$request_uri])) return false;
        $method_map = self::$_uri_map[self::$request_uri];
        if (isset($method_map[strtolower($method)])) {
            $action = $method_map[strtolower($method)];
        } elseif (isset($method_map['any'])) {
            $action = $method_map['any'];
        } else {
            $action = '';
        }
        if (empty($action)) return false;
        $segments = explode('@', $action);
        self::$ctl_name = trim($segments[0], '\\');
        self::$act_name = empty($segments[1]) ? 'index' : $segments[1];

        return true;
    }

    private function __clone()
    {
        //Ban on cloning
    }
}