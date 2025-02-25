<?php


namespace LiteView\Support;


class ExceptionHandler
{
    public function handle(array $msg, \Throwable $exception = null)
    {
        if ($exception instanceof \Throwable) {
            if ('cli' === php_sapi_name() && 'cli' === PHP_SAPI) {
                dump($msg);
            } else {
                require_once __DIR__ . '/../exception.php';
            }
        } else {
            dump($msg);
        }
    }
}