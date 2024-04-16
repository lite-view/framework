<?php


namespace LiteView\Support;


class ExceptionHandler
{
    public function handle(array $e, \Throwable $exception = null)
    {
        $errMsg = json_encode($e);
        if ($errMsg) {
            echo $errMsg;
        } else {
            var_dump($e);
        }
    }

    public static function exception_print(array $msg, \Throwable $exception = null)
    {
        if (!empty($_SERVER['HTTP_HOST'])) {
            if (ob_get_contents()) {
                ob_clean(); // 在浏览器中时清除之前的输出
            }
            header("HTTP/1.1 500 Internal Server Error");
        }

        try {
            \LiteView\Utils\Log::employ('main')->error('SystemError', $msg);
            if (cfg('debug')) {
                $classes = get_declared_classes();
                $dealt = false;
                foreach ($classes as $class) {
                    $ref = new \ReflectionClass($class);
                    if ($ref->isSubclassOf(ExceptionHandler::class)) {
                        (new $class())->handle($msg, $exception); //使用自定义异常打印
                        $dealt = true;
                    }
                }
                if (!$dealt) {
                    // 如果没有自定义异常打印，那么就用默认的异常打印
                    (new ExceptionHandler())->handle($msg, $exception);
                }
            } else {
                echo '系统繁忙';
            }
        } catch (\Exception $e) {
            echo 'ExceptionHandler@exception_print error: ' . $e->getMessage();
        }
    }
}