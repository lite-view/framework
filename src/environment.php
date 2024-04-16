<?php


require_once __DIR__ . '/functions.php';


error_reporting(-1);


set_error_handler(function ($level, $message, $file = '', $line = 0, $context = []) {
    // 注意：error 不能被 try catch 捕获，所以在这里把它转成 ErrorException
    if (error_reporting() & $level) {
        throw new ErrorException($message, $level, $level, $file, $line);
    }
});


set_exception_handler(function (Throwable $e) {
    // 注意：当被 try catch 捕获后就不会触发 set_exception_handler
    if (!empty($_SERVER['HTTP_HOST'])) {
        if (ob_get_contents()) {
            ob_clean(); // 在浏览器中时清除之前的输出
        }
        header("HTTP/1.1 500 Internal Server Error");
    }

    $msg = [
        // 'level' => $e->getSeverity(), Exception 没有这个方法
        'message' => $e->getMessage(),
        'exception' => get_class($e),
        'code' => $e->getCode(),
        'file' => $e->getFile() . '(' . $e->getLine() . ')',
        'line' => $e->getLine(),
        'trace' => $e->getTrace(),
    ];

    try {
        \LiteView\Utils\Log::employ('main')->error('SystemError', $msg);
        if (cfg('debug')) {
            $classes = get_declared_classes();
            foreach ($classes as $class) {
                $ref = new ReflectionClass($class);
                if ($ref->isSubclassOf(\LiteView\Support\ExceptionHandler::class)) {
                    return (new $class())->handle($msg, $e);
                }
            }
            return (new \LiteView\Support\ExceptionHandler())->handle($msg, $e);
        }
    } catch (Exception $e) {
    }
    // 进入 set_exception_handler 后不会再执行后面的代码所以是可以不用 exit 的
    exit('系统繁忙');
});


//注册一个在PHP脚本执行完毕后执行的回调函数，无论是正常结束还是非正常结束（如服务器崩溃等）都会执行该回调函数
register_shutdown_function(function () {

});


\LiteView\Support\Dispatcher::checkEnv();
