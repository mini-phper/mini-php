<?php
function debug($vars)
{
    echo '<pre>';
    print_r($vars);
    exit;
}


function config()
{
    return \Minimal\Core\Config::init();
}

function route()
{
    return \Minimal\Core\Route::init();
}

function view()
{
    return \Minimal\Core\View::init();
}

function C($key, $default = null)
{
    return config()->get($key, $default);
}

function U($url = '', $params = [])
{
    return route()->generate($url, $params);
}