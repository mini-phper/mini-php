<?php

namespace Minimal\Core;


class View
{
    private static $_init = null;
    private $data = [];

    private function __construct()
    {

    }

    private function __clone()
    {
    }

    public static function init()
    {
        if (is_null(self::$_init)) {
            self::$_init = new self();
        }
        return self::$_init;
    }


    public function with($data = [])
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    public function fetch($templates = null)
    {
        $file = $this->viewPath($templates);
        return $this->renderPhpFile($file, $this->data);
    }

    public function display($templates = null)
    {
        return $this->with(['_templates_path' => $this->viewPath($templates)])
            ->renderPhpFile($this->viewPath('layout'), $this->data);
    }


    private function viewPath($name)
    {
        $route = route();
        if (empty($name)) {
            $path = join('/', [$route->getAppName(), $route->getCtlName(), $route->getActName()]);
        } else {
            $path = join('/', [$route->getAppName(), $name]);
        }
        return DIR_PATH . '/templates/' . strtolower(str_replace('\\', '/', $path)) . '.php';
    }

    private function renderPhpFile($file, $params = [])
    {
        ob_start();
        ob_implicit_flush(false);
        extract($params, EXTR_OVERWRITE);
        require($file);

        return ob_get_clean();
    }
}