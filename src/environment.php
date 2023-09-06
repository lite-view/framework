<?php


require_once __DIR__ . '/functions.php';


class ErrorHandlerException extends Exception
{
}


set_error_handler(function () {
    // error 不能被 try catch 捕获，所以在这里把它转成 Exception
    $args = func_get_args();
    throw new ErrorHandlerException($args[1]);
});


set_exception_handler(function ($exception) {
    // 当被 try catch 捕获后就不会触发 set_exception_handler
    if (!empty($_SERVER['HTTP_HOST'])) {
        if (ob_get_contents()) {
            ob_clean(); // 在浏览器中时清除之前的输出
        }
        header("HTTP/1.1 500 Internal Server Error");
    }
    if ($exception instanceof ErrorHandlerException) {
        $msg = [
            'type' => 'error',
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'trace' => $exception->getTrace(),
        ];
    } else {
        $msg = [
            'type' => 'exception',
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile() . '(' . $exception->getLine() . ')',
            'trace' => $exception->getTrace(),
        ];
    }

    LiteView\Utils\Log::employ('main')->error('SystemError', $msg);
    if (cfg('debug')) {
        echo json_encode($msg);
    } else {
        echo '系统繁忙';
    }
    exit();
});


\LiteView\Support\Dispatcher::checkEnv();
