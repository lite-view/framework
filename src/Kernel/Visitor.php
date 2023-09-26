<?php
/**
 * 请求参数管理
 */

namespace LiteView\Kernel;

use ArrayObject;

class Visitor
{
    const SESSION_USER_ID = 'session419028750685ec5af44e5bff70e8a296';
    private $_id; //注意不能用empty函数来判断
    public $user;
    private $data = [];

    public function __construct()
    {
        # SESSION 登录
        if (!isset($_SESSION)) {
            session_start();
        }
        if (!empty($_SESSION[Visitor::SESSION_USER_ID])) {
            $this->_id = $_SESSION[Visitor::SESSION_USER_ID];
        }
    }

    public function ip($long = false)
    {
        $ip = $_SERVER["HTTP_X_FORWARDED_FOR"] ?? $_SERVER["REMOTE_ADDR"];
        if ($long) {
            return ip2long($ip); // 逆函数：long2ip()
        }
        return $ip;
    }

    public function login($uid)
    {
        $_SESSION[Visitor::SESSION_USER_ID] = $uid;
        $this->_id = $uid;
    }

    public function logout()
    {
        $_SESSION[Visitor::SESSION_USER_ID] = null;
    }

    public function __get($attribute)
    {
        if ('id' == $attribute) {
            return (int)$this->_id;
        }
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
        $input = array_merge($_GET, $_POST, $this->data);
        $json = json_decode(file_get_contents("php://input"), true);
        if (is_array($json)) {
            $input = array_merge($input, $json);
        }
        if (is_null($key)) {
            return new ArrayObject($input, ArrayObject::ARRAY_AS_PROPS);
        }
        if (isset($input[$key])) {
            return $input[$key];
        }
        return $default;
    }

    public function only(array $only = [], $null_fill = true)
    {
        $arr = [];
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

    public function except(array $except = [])
    {
        $arr = [];
        $data = $this->input();
        foreach ($data as $field => $value) {
            if (!in_array($field, $except)) {
                $arr[$field] = $data[$field];
            }
        }
        return $arr;
    }

    public function currentUri($params = [])
    {
        $arr = parse_url($_SERVER['REQUEST_URI']);
        $path = $arr['path'] ?? '/';
        $query = $arr['query'] ?? '';
        $path = '/' . trim($path, '/');
        parse_str($query, $_params);
        $params = array_merge($_params, $params);
        if (empty($params)) {
            return $path;
        }
        return $path . '?' . http_build_query($params);
    }

    public function currentPath()
    {
        return Route::current_path();
    }
}


