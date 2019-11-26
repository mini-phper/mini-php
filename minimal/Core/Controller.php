<?php

namespace Minimal\Core;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Controller
{
    protected $request;
    protected $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    protected function getParams($type = 'get', $key = null, $default = null)
    {
        switch ($type) {
            case 'post':
                $obj = $this->request->request;
                break;
            case 'files':
                $obj = $this->request->files;
                break;
            case 'server':
                $obj = $this->request->server;
                break;
            default:
                $obj = $this->request->query;
        }

        return is_null($key) ? $obj->all() : $obj->get($key, $default);
    }

    protected function fetch($name = null, $params = [])
    {
        $content = view()->with($params)->fetch($name);
        $this->response->setContent($content);
    }

    protected function display($name = null, $params = [])
    {
        $content = view()->with($params)->display($name);
        $this->response->setContent($content);
    }

    protected function redirect($url = '', $params = [])
    {
        $this->response->headers->set('Location', U($url, $params));
        $this->response->setStatusCode(302);
        $this->response->send();
        exit();
    }

    protected function error($msg, $redirect = '')
    {
        $content = view()->with(['error' => $msg, 'url' => $redirect])->fetch('error');
        $this->response->setContent($content);
        $this->response->send();
        exit();
    }

}