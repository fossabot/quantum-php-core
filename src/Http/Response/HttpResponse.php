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

namespace Quantum\Http\Response;

use Quantum\Exceptions\HttpException;
use Quantum\Bootstrap;
use SimpleXMLElement;
use DOMDocument;

/**
 * Class HttpResponse
 * @package Quantum\Http\Response
 */
abstract class HttpResponse
{

    use Header;
    use Body;

    /**
     * HTML content type
     */
    const CONTENT_HTML = 'text/html';

    /**
     * XML content type
     */
    const CONTENT_XML = 'application/xml';

    /**
     * JSON content type
     */
    const CONTENT_JSON = 'application/json';

    /**
     * Status code
     * @var int
     */
    private static $__statusCode = 200;

    /**
     * XML root element
     * @var string
     */
    private static $xmlRoot = '<data></data>';

    /**
     * Status texts
     * @var array
     */
    public static $statusTexts = [];

    /**
     * Initialize the Response
     * @throws \Quantum\Exceptions\HttpException
     */
    public static function init()
    {
        if (get_caller_class(3) !== Bootstrap::class) {
            throw HttpException::unexpectedResponseInitialization();
        }

        self::$statusTexts = self::$statusTexts ?: require_once 'statuses.php';
    }

    /**
     * Flushes the response header and body
     */
    public static function flush()
    {
        self::$__statusCode = 200;
        self::$__headers = [];
        self::$__response = [];
    }

    /**
     * Sends all response data to the client and finishes the request.
     * @throws \Exception
     */
    public static function send()
    {
        foreach (self::$__headers as $key => $value) {
            header($key . ': ' . $value);
        }

        echo self::getContent();
    }

    /**
     * Gets the response content
     * @return string
     * @throws \Exception
     */
    public static function getContent(): string
    {
        $content = '';

        switch (self::getContentType()) {
            case self::CONTENT_JSON:
                $content = json_encode(self::all());
                break;
            case self::CONTENT_XML:
                $content = self::arrayToXml(self::all());
                break;
            case self::CONTENT_HTML:
                $content = self::get('_qt_rendered_view');
                break;
            default :
                break;
        }

        return $content;
    }

    /**
     * Set the status code
     * @param int $code
     */
    public static function setStatusCode(int $code)
    {
        if (!array_key_exists($code, self::$statusTexts)) {
            throw new \InvalidArgumentException(sprintf('The HTTP status code "%s" is not valid.', $code));
        }

        self::$__statusCode = $code;
    }

    /**
     * Gets the status code
     * @return int
     */
    public static function getStatusCode(): int
    {
        return self::$__statusCode;
    }

    /**
     * Gets the status text
     * @return string
     */
    public static function getStatusText(): string
    {
        return self::$statusTexts[self::$__statusCode];
    }

    /**
     * Redirect
     * @param string $url
     * @param int|null $code
     * @throws \Quantum\Exceptions\StopExecutionException
     */
    public static function redirect(string $url, int $code = null)
    {
        if (!is_null($code)) {
            self::setStatusCode($code);
        }

        self::setHeader('Location', $url);

        stop();
    }

    /**
     * Prepares the JSON response
     * @param array|null $data
     * @param int|null $code
     */
    public static function json(array $data = null, int $code = null)
    {
        self::setContentType(self::CONTENT_JSON);

        if (!is_null($code)) {
            self::setStatusCode($code);
        }

        if ($data) {
            foreach ($data as $key => $value) {
                self::$__response[$key] = $value;
            }
        }
    }

    /**
     * Prepares the XML response
     * @param array|null $data
     * @param int|null $code
     */
    public static function xml(array $data = null, $root = '<data></data>', int $code = null)
    {
        self::setContentType(self::CONTENT_XML);

        self::$xmlRoot = $root;

        if (!is_null($code)) {
            self::setStatusCode($code);
        }

        if ($data) {
            foreach ($data as $key => $value) {
                self::$__response[$key] = $value;
            }
        }
    }

    /**
     * Prepares the HTML content
     * @param string $html
     * @param int|null $code
     */
    public static function html(string $html, int $code = null)
    {
        self::setContentType(self::CONTENT_HTML);

        if (!is_null($code)) {
            self::setStatusCode($code);
        }

        self::$__response['_qt_rendered_view'] = $html;
    }

    /**
     * Transforms array to XML
     * @param array $arr
     * @return string
     * @throws \Exception
     */
    private static function arrayToXML(array $arr): string
    {
        $simpleXML = new SimpleXMLElement(self::$xmlRoot);
        self::composeXML($arr, $simpleXML);

        $dom = new DOMDocument();
        $dom->loadXML($simpleXML->asXML());
        $dom->formatOutput = true;
        return $dom->saveXML();
    }

    /**
     * Compose XML
     * @param array $arr
     * @param \SimpleXMLElement $simpleXML
     */
    private static function composeXML(array $arr, SimpleXMLElement &$simpleXML)
    {
        foreach ($arr as $key => $value) {
            if (is_numeric($key)) {
                $key = 'item' . $key;
            }

            $tag = $key;
            $attributes = null;

            if (strpos($key, '@') !== false) {
                list($tag, $attributes) = explode('@', $key);
                $attributes = json_decode($attributes);
            }

            if (is_array($value)) {
                $child = $simpleXML->addChild($tag);
                if ($attributes) {
                    foreach ($attributes as $attrKey => $attrVal) {
                        $child->addAttribute($attrKey, $attrVal);
                    }
                }

                self::composeXML($value, $child);
            } else {
                $child = $simpleXML->addChild($tag, htmlspecialchars($value));

                if ($attributes) {
                    foreach ($attributes as $attrKey => $attrVal) {
                        $child->addAttribute($attrKey, $attrVal);
                    }
                }
            }
        }
    }

}
