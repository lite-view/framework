<?php

namespace LiteView\Exception;

abstract class ExceptionManager
{
    public bool $use = false;

    abstract public function handle(array $msg, ?\Throwable $exception = null): bool;
}