<?php

namespace LiteView\Kernel;


class Route
{
    private static $routes = [];
    private static $prefix = [];
    private static $middleware = [];

    private static function validateRegular(array $regular): void
    {
        foreach ($regular as $name => $pattern) {
            if (@preg_match("/$pattern/", '') === false) {
                throw new \InvalidArgumentException("Invalid regex for {$name}: {$pattern}");
            }
        }
    }

    private static function add($path, $target)
    {
        if (!empty($target['regular'])) {
            self::validateRegular($target['regular']);
        }
        $path = self::fixPath($path);
        if (!empty($target['regular'])) {
            $path = "$path>>>" . json_encode($target['regular']);
        }
        if (isset(self::$routes[$path])) {
            foreach (self::$routes[$path] as $existing) {
                if (self::methodsOverlap($target['method'], $existing['method'])) {
                    trigger_error("Route already exists: $path", E_USER_ERROR);
                }
            }
        }
        $target['middleware']  = self::mergeMiddleware($target['middleware']);
        self::$routes[$path][] = $target;
    }

    private static function methodsOverlap($a, $b): bool
    {
        if ($a === '*' || $b === '*') {
            return true;
        }
        $a = array_map('strtolower', (array)$a);
        $b = array_map('strtolower', (array)$b);
        return (bool)array_intersect($a, $b);
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
        if ($pass === '*' || in_array($method, array_map('strtolower', (array)$pass))) {
            return $target;
        }
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

    public static function apiResource($path, $controller, $middleware = [])
    {
        self::get($path, [$controller, 'index'], $middleware);
        self::post($path, [$controller, 'store'], $middleware);
        self::get($path . '/{id}', [$controller, 'show'], $middleware);
        self::rule(['PUT', 'PATCH'], $path . '/{id}', [$controller, 'update'], $middleware);
        self::rule('DELETE', $path . '/{id}', [$controller, 'destroy'], $middleware);
    }

    public static function quick($path, $controller, $middleware = [])
    {
        $methods = get_class_methods($controller);
        foreach ($methods as $action) {
            if (0 === strpos($action, '__')) {
                continue;
            }
            self::rule(['GET', 'POST'], rtrim($path, '/') . "/$action", [$controller, $action], $middleware);
        }
    }

    /**
     * @return array $target, $params
     */
    public static function match(): array
    {
        $path    = self::currentPath();
        $method  = strtolower($_SERVER['REQUEST_METHOD']);
        $targets = self::$routes[$path] ?? null;
        if ($targets) {
            foreach ($targets as $target) {
                $filtered = self::filterMethod($target, $method);
                if ($filtered) {
                    return [$filtered, null];
                }
            }
            return [null, null];
        }
        return self::matchParamRoute($path, $method);
    }

    public static function matchParamRoute($path, $method): array
    {
        foreach (self::$routes as $key => $targets) {
            $regular  = $targets[0]['regular'];
            $path_exp = explode('>>>', $key)[0];
            $pattern  = preg_replace_callback(
                '#(/?){(.+?)}#',
                function ($arg) use ($regular) {
                    $slash    = $arg[1];
                    $name_raw = $arg[2];
                    $name     = trim($name_raw, '?');
                    $reg      = $regular[$name] ?? null;
                    if ($reg) {
                        if ($name_raw !== $name) {
                            return $slash ? "(?:/($reg))?" : "($reg)?";
                        }
                        return $slash . "($reg)";
                    }

                    $reg = '[0-9a-zA-Z\._-]';
                    if ($name_raw === $name) {
                        return $slash . "($reg+)";
                    }
                    return $slash ? "(?:/($reg*))?" : "($reg*)?";
                },
                $path_exp
            );

            $success = preg_match("#^$pattern/?$#", $path, $parameters);
            if ($success && $path === $parameters[0]) {
                foreach ($targets as $target) {
                    $filtered = self::filterMethod($target, $method);
                    if ($filtered) {
                        return [$filtered, $parameters];
                    }
                }
            }
        }

        return [null, null];
    }

    public static function currentPath(): string
    {
        if (!empty($_SERVER['PATH_INFO'])) {
            $path = $_SERVER['PATH_INFO'];
        } else {
            $uri  = str_replace($_SERVER['PHP_SELF'], '', $_SERVER['REQUEST_URI']);
            $path = explode('?', $uri)[0];
        }
        return '/' . trim($path, '/');
    }

    public static function _print()
    {
        foreach (self::$routes as $k => $targets) {
            foreach ($targets as $v) {
                echo $k, PHP_EOL;
                echo '    ┕ ', json_encode($v), PHP_EOL;
            }
        }
    }

    public static function _all_rotes(): array
    {
        return self::$routes;
    }

    public static function reset(): void
    {
        self::$routes     = [];
        self::$prefix     = [];
        self::$middleware = [];
    }
}