<?php

/**
 * Quantum PHP Framework
 *
 * An open source software development framework for PHP
 *
 * @package Quantum
 * @author Arman Ag. <arman.ag@softberg.org>
 * @copyright Copyright (c) 2018 Softberg LLC (https://softberg.org)
 * @link http://quantum.softberg.org/
 * @since 2.6.0
 */

namespace Quantum;

use Quantum\Tracer\ErrorHandler;
use Quantum\Di\Di;

if (!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

/**
 * Class App
 * @package Quantum
 */
class App
{

    /**
     * @var string
     */
    public static $baseDir = __DIR__;

    /**
     * Starts the app
     * @param string $baseDir
     * @throws \ErrorException
     * @throws \Quantum\Exceptions\ConfigException
     * @throws \Quantum\Exceptions\ControllerException
     * @throws \Quantum\Exceptions\CsrfException
     * @throws \Quantum\Exceptions\DatabaseException
     * @throws \Quantum\Exceptions\DiException
     * @throws \Quantum\Exceptions\EnvException
     * @throws \Quantum\Exceptions\HookException
     * @throws \Quantum\Exceptions\LangException
     * @throws \Quantum\Exceptions\MiddlewareException
     * @throws \Quantum\Exceptions\ModuleLoaderException
     * @throws \Quantum\Exceptions\RouteException
     * @throws \Quantum\Exceptions\SessionException
     * @throws \Quantum\Exceptions\ViewException
     * @throws \ReflectionException
     */
    public static function start(string $baseDir)
    {
        self::loadCoreFunctions($baseDir . DS . 'vendor' . DS . 'quantum' . DS . 'framework' . DS . 'src' . DS . 'Helpers');

        self::setBaseDir($baseDir);

        Di::loadDefinitions();

        ErrorHandler::setup();

        Bootstrap::run();
    }

    /**
     * Loads the core functions
     * @param string $path
     */
    public static function loadCoreFunctions(string $path)
    {
        foreach (glob($path . DS . '*.php') as $filename) {
            require_once $filename;
        }
    }

    /**
     * Sets the app base directory
     * @param string $baseDir
     */
    public static function setBaseDir(string $baseDir)
    {
        self::$baseDir = $baseDir;
    }

    /**
     * Gets the app base directory
     * @return string
     */
    public static function getBaseDir(): string
    {
        return self::$baseDir;
    }

}

