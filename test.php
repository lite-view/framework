<?php


require __DIR__ . '/vendor/autoload.php';

use LiteView\Utils\Log;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\MemoryUsageProcessor;


\LiteView\Support\ToolMan::setCfg('logging', [
    'test' => [
        "handlers" => [
            new StreamHandler('php://stdout', Logger::DEBUG),
            new StreamHandler(root_path("storage/logs/test.log"), Logger::DEBUG),
        ],
        "format" => null,
        "processors" => [
            MemoryUsageProcessor::class,
        ]
    ]
]);

Log::employ('test')->info('1');