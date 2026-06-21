<?php
/**
 * 请求参数管理
 */

namespace LiteView\Kernel;

use ArrayObject;

class Visitor
{
    const SESSION_USER_ID = 'session419028750685ec5af44e5bff70e8a296';
    public $user;
    private $data = [];
    private $inputCache;

    public function __construct()
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        $this->data['id'] = $_SESSION[Visitor::SESSION_USER_ID] ?? null;
    }

    public function ip($long = false)
    {
        if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"]) && cfg('trust_proxy')) {
            $ip = trim(explode(',', $_SERVER["HTTP_X_FORWARDED_FOR"])[0]);
        } else {
            $ip = $_SERVER["REMOTE_ADDR"];
        }
        if ($long) {
            return ip2long($ip);
        }
        return $ip;
    }

    public function login($uid)
    {
        $_SESSION[Visitor::SESSION_USER_ID] = $uid;
        $this->data['id']                   = $uid;
    }

    public function logout()
    {
        $_SESSION[Visitor::SESSION_USER_ID] = null;
    }

    public function __get($attribute)
    {
        return $this->data[$attribute];
    }

    public function __set($attribute, $value)
    {
        if ('id' == $attribute) {
            $this->login($value);
        } else {
            $this->data[$attribute] = $value;
        }
    }

    public function __call($name, $arguments)
    {
        // twig 获取不存在的属性时会调用同名的方法
        return $this->data[$name];
    }

    public function get($key = null, $default = null)
    {
        if (is_null($key)) {
            return new ArrayObject($_GET, ArrayObject::ARRAY_AS_PROPS);
        }
        if (isset($_GET[$key])) {
            return $_GET[$key];
        }
        return $default;
    }

    public function post($key = null, $default = null)
    {
        if (is_null($key)) {
            return new ArrayObject($_POST, ArrayObject::ARRAY_AS_PROPS);
        }
        if (isset($_POST[$key])) {
            return $_POST[$key];
        }
        return $default;
    }

    public function input($key = null, $default = null)
    {
        if (null === $this->inputCache) {
            $input = array_merge($_GET, $_POST, $this->data);
            // 读取json
            $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
            if (stripos($contentType, 'application/json') !== false) {
                $rawInput = @file_get_contents("php://input"); // 防止waring
                if ($rawInput !== false) {
                    $json = json_decode($rawInput, true);
                    if (is_array($json)) {
                        $input = array_merge($input, $json);
                    }
                }
            }
            $this->inputCache = $input;
        }

        if (is_null($key)) {
            return new ArrayObject($this->inputCache, ArrayObject::ARRAY_AS_PROPS);
        }
        if (isset($this->inputCache[$key])) {
            return $this->inputCache[$key];
        }
        return $default;
    }

    public function only(array $only = [], $null_fill = true): array
    {
        $arr  = [];
        $data = $this->input();
        foreach ($only as $field) {
            if (isset($data[$field])) {
                $arr[$field] = $data[$field];
            } else {
                if ($null_fill) {
                    $arr[$field] = null;
                }
            }
        }
        return $arr;
    }

    public function except(array $except = []): array
    {
        $arr  = [];
        $data = $this->input();
        foreach ($data as $field => $value) {
            if (!in_array($field, $except)) {
                $arr[$field] = $data[$field];
            }
        }
        return $arr;
    }

    public function currentUri($params = []): string
    {
        $arr   = parse_url($_SERVER['REQUEST_URI']);
        $path  = $arr['path'] ?? '/';
        $query = $arr['query'] ?? '';
        $path  = '/' . trim($path, '/');
        parse_str($query, $_params);
        $params = array_merge($_params, $params);
        if (empty($params)) {
            return $path;
        }
        return $path . '?' . http_build_query($params);
    }

    public function currentPath($no_prefix = false)
    {
        $pre = cfg('location');
        if ($pre && $no_prefix) {
            $pre = '/' . trim($pre, '/');
            return str_replace($pre, '', Route::currentPath());
        }
        return Route::currentPath();
    }
}


