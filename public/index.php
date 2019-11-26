<?php
define('DIR_PATH', __DIR__ . '/..');
require_once DIR_PATH . '/vendor/autoload.php';
define('DEBUG', true);
if (DEBUG) {
    # @ Whoops 组件
    $whoops = new \Whoops\Run;
    $whoops->prependHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();
    ini_set('display_errors', 'On');
} else {
    error_reporting(0);
}

Minimal\App::init()->run();