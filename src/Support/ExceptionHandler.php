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
}