<?php


require __DIR__ . '/vendor/autoload.php';

use Monolog\Logger;
use LiteView\Utils\Log;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\MemoryUsageProcessor;


$r = \LiteView\Utils\Validate::enum(1, ['required' => true, 'list' => [1, 2]]);
var_dump($r);