<?php


require __DIR__ . '/vendor/autoload.php';

use Monolog\Logger;
use LiteView\Utils\Log;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\MemoryUsageProcessor;


\LiteView\Support\ToolMan::setCfg('logging', [
    'test' => [
        "handlers" => function () {
            echo 'test start', PHP_EOL;
            return [
                new StreamHandler('php://stdout', Logger::DEBUG),
                new StreamHandler(root_path("storage/logs/test.log"), Logger::DEBUG)
            ];
        },
        "format" => null,
        "processors" => [
            MemoryUsageProcessor::class,
        ]
    ],
    'test2' => [
        "handlers" => new StreamHandler('php://stdout', Logger::DEBUG),
        "format" => null,
        "processors" => [
            MemoryUsageProcessor::class,
        ]
    ],
    'test3' => [
        "handlers" => [
            new StreamHandler('php://stdout', Logger::DEBUG),
        ],
        "format" => null,
        "processors" => [
            MemoryUsageProcessor::class,
        ]
    ],
]);

Log::employ('test')->info('1');
Log::employ('test')->info('1');
Log::employ('test2')->info('2');
Log::employ('test2')->info('2');
Log::employ('test3')->info('3');
Log::employ('test3')->info('3');