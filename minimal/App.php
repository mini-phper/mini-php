<?php

namespace Minimal;
defined('DIR_PATH') or define('DIR_PATH', __DIR__ . '/..');;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class App
{
    private static $_init = null;
    private $request;
    private $response;

    private function __construct()
    {
        date_default_timezone_set('PRC');
        $this->request = Request::createFromGlobals();
        $this->response = Response::create();
    }

    public static function init()
    {
        if (is_null(self::$_init)) {
            self::$_init = new self();
        }
        return self::$_init;
    }

    public function run()
    {
        /*加载路由*/
        try {
            $route = route()->load($this->request->getPathInfo(), $this->request->getMethod());
        } catch (\Exception $e) {
            $this->error();
            return null;
        }

        $ctl_class = $route->getClassName();
        $act_name = $route->getActName();

        /*加载配置*/
        $this->loadConfig($route->getAppName());

        $obj = new $ctl_class($this->request, $this->response);
        if (method_exists($obj, $act_name)) {
            $obj->{$act_name}();
        } else {
            $this->error();
        }

        $this->response->send();
    }

    private function error($code = 404, $msg = 'Not Found')
    {
        $this->response->setStatusCode($code);
        $this->response->setContent($msg);
        $this->response->send();
    }


    private function loadConfig($app)
    {
        /*默认配置*/
        $default = require_once(DIR_PATH . '/config/config.php');
        config()->set($default);
        /*应用配置*/
        $app_path = DIR_PATH . '/config/' . strtolower($app) . '.php';
        if (is_file($app_path)) {
            $app_config = require_once($app_path);
            config()->set($app_config);
        }
    }

    private function __clone()
    {
    }
}