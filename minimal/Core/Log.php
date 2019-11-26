<?php

namespace Minimal\Core;

use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Symfony\Component\HttpFoundation\Request;

class Log
{
    private static $init = null;
    private static $loggers;
    private static $fileName = '/runtime/logs/';
    private static $maxFiles = 31;
    private static $level = Logger::DEBUG;
    private static $filePermission = 0666;
    protected static $request;
    protected $_name;
    protected $_title;

    private function __construct($name = null)
    {
        self::$request = Request::createFromGlobals();
        $route = route();
        $this->_name = is_null($name) ? strtolower($route->getAppName()) : $name;
        $this->_title = $route->getRequestUri() . ' ==>';
    }

    public static function init($name = null)
    {
        if (is_null(self::$init)) {
            self::$init = new self($name);
        }
        return self::$init;
    }

    public function info($context)
    {
        return $this->write($context, Logger::INFO);
    }

    public function error($context)
    {
        if (empty($context)) return true;
        return self::createLogger('error')->addRecord(Logger::ERROR, $this->_title, $context);
    }

    public function write($context, $level = Logger::DEBUG)
    {
        if (empty($context)) return true;
        return self::createLogger($this->_name)->addRecord($level, $this->_title, $context);
    }

    private static function createLogger($name)
    {
        if (empty(self::$loggers[$name])) {
            // client
            $client = self::$request->getClientIp();
            // 日志文件目录
            $fileName = DIR_PATH . self::$fileName;
            // 日志保存时间
            $maxFiles = self::$maxFiles;
            // 日志等级
            $level = self::$level;
            // 权限
            $filePermission = self::$filePermission;

            // 创建日志
            $logger = new Logger($client);
            // 日志文件相关操作
            $handler = new RotatingFileHandler("{$fileName}{$name}.log", $maxFiles, $level, true, $filePermission);
            // 日志格式
            $formatter = new LineFormatter("%datetime% %channel%:%level_name% %message% %context% %extra%\n", "Y-m-d H:i:s", false, true);

            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);

            self::$loggers[$name] = $logger;
        }
        return self::$loggers[$name];
    }
}