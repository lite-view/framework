<?php

namespace LiteView\Exception;

abstract class ExceptionManager
{
    public $use;

    abstract public function handle(array $msg, \Throwable $exception = null): bool;
}