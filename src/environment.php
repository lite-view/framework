<?php


require_once __DIR__ . '/functions.php';


//ob_start 是 PHP 中的一个输出缓冲函数。它开启了一个输出缓冲区，用于存储由 PHP 脚本产生的输出内容(echo、print、var_dump...)，而不是直接将这些内容发送到浏览器或者其他输出设备。
//ob_get_clean 函数获取并清空缓冲区
ob_start();
error_reporting(-1);


set_error_handler(function ($level, $message, $file = '', $line = 0, $context = []) {
    /*
     * 注意(windows 下 php7.2 测试得出结论)：
     * 1、error 不能被 try catch 捕获，所以在这里把它转成 ErrorException 交给 set_exception_handler 处理
     * 2、require 引起的 Fatal error 会进入这里，但不会再往下执行，即 ErrorException 不能被 set_exception_handler 处理
     * 3、如果想处理 Fatal error 可以使用 register_shutdown_function 函数来注册一个函数，在脚本执行完成后处理致命错误
     * */
    if (error_reporting() & $level) {
        throw new ErrorException($message, $level, $level, $file, $line);
    }
});


set_exception_handler(function (Throwable $e) {
    // 当被 try catch 捕获后就不会触发 set_exception_handler
    // 进入 set_exception_handler 后不会再执行其它的代码，所以可以不用 exit
    $msg = [
        // 'level' => $e->getSeverity(), Exception 没有这个方法
        'message'   => $e->getMessage(),
        'exception' => get_class($e),
        'code'      => $e->getCode(),
        'file'      => $e->getFile() . '(' . $e->getLine() . ')',
        'line'      => $e->getLine(),
        'trace'     => $e->getTrace(),
    ];

    \LiteView\Support\Dispatcher::exceptionPrint($msg, $e);
});


//注册一个在PHP脚本执行完毕后执行的回调函数，无论是正常结束还是非正常结束（如服务器崩溃等）都会执行该回调函数
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // 处理致命错误，可以在这里调用你的错误处理函数
        \LiteView\Support\Dispatcher::exceptionPrint($error);
    }
});


\LiteView\Support\Dispatcher::checkEnv();
