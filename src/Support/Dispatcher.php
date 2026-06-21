<?php


namespace LiteView\Support;


use LiteView\Exception\ExceptionManager;
use LiteView\Kernel\Visitor;


class Dispatcher
{
    public static ?ExceptionManager $exceptionManager = null;

    // 根据环境加载配置
    public static function checkEnv()
    {
        $env_config = root_path() . 'config.' . cfg('app_env') . '.json';
        if (file_exists($env_config)) {
            $arr = json_decode(file_get_contents($env_config), true);
            if (!is_array($arr)) {
                exit("环境配置文件解析失败($env_config)！请检查json格式是否有误");
            }
            foreach ($arr as $name => $value) {
                ToolMan::setCfg($name, $value);
            }
        }
    }

    // 请求处理
    public static function work(array $target, ?array $params, Visitor $visitor)
    {
        $params     = $params ?? [];
        $params[0]  = $visitor;
        $action     = $target['action'];
        $middleware = $target['middleware'];

        // 将所有中间件嵌套成闭包链
        $pipeline = array_reduce(
            array_reverse($middleware), // 倒序处理
            function ($next, $middleware) {
                return function (Visitor $visitor) use ($next, $middleware) {
                    return call_user_func_array([new $middleware(), 'handle'], [$visitor, $next]);
                };
            },
            function (Visitor $visitor) use ($action, $params) {
                if (is_callable($action) && !is_array($action)) {
                    return call_user_func_array($action, $params);
                }

                if (is_string($action)) {
                    list($class, $method) = explode('@', $action);
                } else {
                    // is_callable
                    list($class, $method) = $action;
                }
                return call_user_func_array([new $class($visitor), $method], $params);
            }
        );

        // 执行闭包链
        return call_user_func($pipeline, $visitor);
    }

    // 异常打印
    public static function exceptionPrint(array $msg, \Throwable $exception = null)
    {
        if (!empty($_SERVER['HTTP_HOST'])) {
            // ob_get_contents() — 返回当前输出缓冲区的内容（字符串）。如果没开启缓冲区则返回 false。缓冲区激活但无内容时返回空字符串 ""，此时 empty("") 为 true，导致误判为"没有缓冲区"而跳过清理。
            // ob_get_level() — 返回输出缓冲区的嵌套层数（整数）。未开启时返回 0，开启后 ≥ 1。不依赖缓冲区内容，所以 ob_get_level() > 0 能准确判断是否有活动缓冲区，更可靠。
            // 清理输出缓冲区，确保错误信息不被之前的内容干扰
            if (ob_get_level() > 0) {
                ob_clean();
            }

            // header("HTTP/1.1 500 Internal Server Error");
            // header("Status: 500 Internal Server Error"); // FastCGI/PHP-FPM 兼容的状态头写法
            http_response_code(500); //（PHP 5.4+）
        }

        try {
            \LiteView\Support\Log::error('SystemError', $msg);

            if (self::$exceptionManager && self::$exceptionManager->use) {
                $stop = self::$exceptionManager->handle($msg, $exception);
                if ($stop) {
                    return;
                }
            }

            if ($exception === null) {
                $exception = new \ErrorException(
                    $msg['message'],
                    $msg['type'] ?? 0,
                    $msg['type'] ?? 0,
                    $msg['file'] ?? '',
                    $msg['line'] ?? 0
                );
            }

            if ('cli' === PHP_SAPI) {
                dump($msg);
            } else {
                require_once __DIR__ . '/../exception.php';
            }
        } catch (\Exception $e) {
            echo 'Dispatcher@exceptionPrint: ' . $e->getMessage();
        }
    }
}