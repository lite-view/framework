<?php


namespace LiteView\Support;


class ExceptionHandler
{
    public function handle(array $e, \Throwable $exception = null)
    {
        echo json_encode($e);
    }
}