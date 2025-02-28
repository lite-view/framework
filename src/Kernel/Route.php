<?php

namespace LiteView\Kernel;


class Route
{
    private static $routes = [];
    private static $prefix = [];
    private static $middleware = [];

    private static function add($path, $target)
    {
        $path = self::fixPath($path);
        if (!empty($target['regular'])) {
            $path = "$path>>>" . json_encode($target['regular']);
        }
        if (isset(self::$routes[$path])) {
            trigger_error("Route already exists: $path", E_USER_ERROR);
        }
        $target['middleware'] = self::mergeMiddleware($target['middleware']);
        self::$routes[$path]  = $target;
    }

    private static function mergeMiddleware($middleware): array
    {
        if (is_array($middleware)) {
            return array_unique(array_merge(self::$middleware, $middleware));
        }
        return array_unique(self::$middleware);
    }

    private static function fixPath($path): string
    {
        $path = '/' . trim($path, '/');
        if (self::$prefix) {
            // 只有分组时才会带 prefix ， prefix 不能以 / 开头或结尾
            $prefix = implode('/', self::$prefix);
            $path   = '/' . $prefix . $path;
        }
        // 同一个项目不同目录
        $location = trim(cfg('location', ''), '/');
        if ($location) {
            $path = '/' . $location . $path;
        }
        return '/' . trim($path, '/');
    }

    private static function filterMethod(array $target, string $method): ?array
    {
        $pass = $target['method'];
        if (is_string($pass)) {
            $pass = [$pass];
        }
        foreach ($pass as $key) {
            $pass[strtolower($key)] = 1;
        }
        if (isset($pass[$method]) || isset($pass['*'])) {
            return $target;
        }
        //trigger_error('route not found: ' . $method . '@' . $path, E_USER_ERROR);
        return null;
    }

    public static function rule($method, $path, $action, $middleware = [], $regular = [])
    {
        self::add($path, ['action' => $action, 'middleware' => $middleware, 'regular' => $regular, 'method' => $method]);
    }

    public static function get($path, $action, $middleware = [], $regular = [])
    {
        self::add($path, ['action' => $action, 'middleware' => $middleware, 'regular' => $regular, 'method' => ['GET']]);
    }

    public static function post($path, $action, $middleware = [], $regular = [])
    {
        self::add($path, ['action' => $action, 'middleware' => $middleware, 'regular' => $regular, 'method' => ['POST']]);
    }

    public static function any($path, $action, $middleware = [], $regular = [])
    {
        self::add($path, ['action' => $action, 'middleware' => $middleware, 'regular' => $regular, 'method' => '*']);
    }

    public static function group($params, callable $register)
    {
        $bak_prefix     = self::$prefix;
        $bak_middleware = self::$middleware;
        if (is_string($params)) {
            self::$prefix[] = $params;
        } else if (is_array($params)) {
            self::$prefix[] = $params['prefix'];
            if (isset($params['middleware'])) {
                self::$middleware = self::mergeMiddleware($params['middleware']);
            }
        }
        $register();
        // 用完之后还原，避免影响下一次分组
        self::$prefix     = $bak_prefix;
        self::$middleware = $bak_middleware;
    }

    public static function quick($path, $controller, $middleware = [])
    {
        $methods = get_class_methods($controller);
        foreach ($methods as $action) {
            self::rule(['GET', 'POST'], rtrim($path, '/') . "/$action", [$controller, $action], $middleware);
        }
    }

    public static function match(): array
    {
        $path   = self::currentPath();
        $method = strtolower($_SERVER['REQUEST_METHOD']);
        $target = self::$routes[$path] ?? null;
        if ($target) {
            return [self::filterMethod($target, $method), null];
        }
        return self::matchParamRoute($path, $method);
    }

    public static function matchParamRoute($path, $method): array
    {
        foreach (self::$routes as $key => $target) {
            $regular = $target['regular'];
            $pattern = preg_replace_callback(
                '#[/]*{(.+?)}#',
                function ($arg) use ($regular) {
                    list($full, $name_raw) = $arg;

                    $name = trim($name_raw, '?');
                    $reg  = $regular[$name] ?? null;
                    if ($reg) {
                        return "[/]*($reg)";
                    }

                    $reg = '[0-9a-zA-Z\._-]';
                    if ($name_raw === $name) {
                        // 必需参数(不带?)
                        return "[/]*($reg+)";
                    }
                    // 非必需参数(带?)
                    return "[/]*($reg*)";
                },
                $key
            );
            $pattern = explode('>>>', $pattern)[0];
            $success = preg_match("#$pattern#", $path, $parameters);
            if ($success && $path === $parameters[0]) {
                return [self::filterMethod($target, $method), $parameters];
            }
        }
        // trigger_error('route not found: ' . $method . '@' . $path, E_USER_ERROR);

        return [null, null];
    }

    public static function currentPath(): string
    {
        /* php -s index.php # 指定路由文件，无论如何都会进入index.php
         *      如果匹配到存在的目录 PATH_INFO 不存在
         *      PHP_SELF 等于 REQUEST_URI 的path部份
         * php -s # 不指定路由文件，默认用index.php
         *      遇到带后缀的地址不会进入index.php
         * */
        if (isset($_SERVER['PATH_INFO'])) {
            $path = $_SERVER['PATH_INFO'];
        } else {
            $uri  = str_replace($_SERVER['PHP_SELF'], '', $_SERVER['REQUEST_URI']);
            $arr  = explode('?', $uri);
            $path = $arr[0] ?? '/';
        }
        return '/' . trim($path, '/');
    }

    public static function _print()
    {
        foreach (self::$routes as $k => $v) {
            echo $k, PHP_EOL;
            echo '    ┕ ', json_encode($v), PHP_EOL;
        }
    }
}