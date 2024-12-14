<?php


namespace LiteView\Support;


class ExceptionHandler
{
    public function handle(array $msg, \Throwable $exception = null)
    {
        if ($exception instanceof \Throwable) {
            require_once __DIR__ . '/../exception.php';
        } else {
            dump($msg);
        }
    }
}