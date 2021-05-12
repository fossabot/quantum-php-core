<?php


namespace Quantum\Http\Request;


trait Header
{
    /**
     * Request headers
     * @var array
     */
    private static $__headers = [];

    /**
     * Checks the request header existence by given key
     * @param string $key
     * @return bool
     */
    public static function hasHeader(string $key): bool
    {
        return isset(self::$__headers[strtolower($key)]);
    }

    /**
     * Gets the request header by given key
     * @param string $key
     * @return string|null
     */
    public static function getHeader(string $key): ?string
    {
        return self::hasHeader($key) ? self::$__headers[strtolower($key)] : null;
    }

    /**
     * Sets the request header
     * @param string $key
     * @param mixed $value
     */
    public static function setHeader(string $key, $value)
    {
        self::$__headers[strtolower($key)] = $value;
    }

    /**
     * Gets all request headers
     * @return array
     */
    public static function allHeaders(): array
    {
        return self::$__headers;
    }

    /**
     * Deletes the header by given key
     * @param string $key
     */
    public static function deleteHeader(string $key)
    {
        if (self::hasHeader($key)) {
            unset(self::$__headers[strtolower($key)]);
        }
    }
}