<?php
/**
 * 路由管理
 */

namespace LiteView\Kernel;

class Route
{
    public static $prefix; //字符串，分组时使用
    public static $middleware;  //数组，分组时使用
    public static $routes = [];

    public static function get($path, $action, $middleware = [])
    {
        $path = self::fix_path($path);
        self::$routes['get'][$path] = [
            'action' => $action,
            'middleware' => self::merge_middleware($middleware)
        ];
    }

    public static function post($path, $action, $middleware = [])
    {
        $path = self::fix_path($path);
        self::$routes['post'][$path] = [
            'action' => $action,
            'middleware' => self::merge_middleware($middleware)
        ];
    }

    public static function any($path, $action, $middleware = [])
    {
        self::get($path, $action, $middleware);
        self::post($path, $action, $middleware);
    }

    public static function group($params, $register)
    {
        $bak_prefix = self::$prefix;
        $bak_middleware = self::$middleware;
        if (is_string($params)) {
            self::$prefix[] = $params;
        }
        if (is_array($params)) {
            self::$prefix[] = $params['prefix'];
            if (isset($params['middleware'])) {
                self::$middleware = self::merge_middleware($params['middleware']);
            }
        }
        $register();
        // 用完之后还原，避免影响下一次分组
        self::$prefix = $bak_prefix;
        self::$middleware = $bak_middleware;
    }

    public static function quick($path, $controller, $middleware = [])
    {
        $methods = get_class_methods($controller);
        foreach ($methods as $action) {
            self::any(rtrim($path, '/') . "/$action", "$controller@$action", $middleware);
        }
    }

    public static function current_route()
    {
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        $path = self::current_path();
        // 跨域处理
        cors($path);
        // options 请求处理
        if ('options' === $method) {
            return [
                'action' => function () {
                },
                'middleware' => []
            ];
        }
        if (isset(self::$routes[$method][$path])) {
            return self::$routes[$method][$path];
        }
        trigger_error('route not found: ' . $method . '@' . $path, E_USER_ERROR);
    }

    public static function current_path()
    {
        if (isset($_SERVER['PATH_INFO'])) {
            $path = $_SERVER['PATH_INFO'];
        } else {
            $request_uri = str_replace($_SERVER['PHP_SELF'], '', $_SERVER['REQUEST_URI']);
            $arr = explode('?', $request_uri);
            $path = $arr[0] ?? '/';
        }
        return '/' . trim($path, '/');
    }

    private static function fix_path($path)
    {
        $path = '/' . trim($path, '/');
        if (!is_null(self::$prefix)) {
            $prefix = implode('/', self::$prefix);
            $path = '/' . trim($prefix, '/') . $path;
        }
        $path = cfg('location', '') . $path;
        return '/' . trim($path, '/');
    }

    private static function merge_middleware($middleware)
    {
        if (!is_null(self::$middleware)) {
            $middleware = array_unique(array_merge(self::$middleware, $middleware));
        }
        return $middleware;
    }
}
