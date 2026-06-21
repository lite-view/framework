<?php

namespace Test;

use LiteView\Support\Log;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\MemoryUsageProcessor;
use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
    public function test01()
    {
        \LiteView\Support\ToolMan::setCfg('logging', [
            'test'  => [
                "handlers"   => function () {
                    echo 'test start', PHP_EOL;
                    return [
                        new StreamHandler('php://stdout', Logger::DEBUG),
                        new StreamHandler(root_path("storage/logs/test.log"), Logger::DEBUG)
                    ];
                },
                "format"     => null,
                "processors" => [
                    MemoryUsageProcessor::class,
                ]
            ],
            'test2' => [
                "handlers"   => new StreamHandler('php://stdout', Logger::DEBUG),
                "format"     => null,
                "processors" => [
                    MemoryUsageProcessor::class,
                ]
            ],
            'test3' => [
                "handlers"   => [
                    new StreamHandler('php://stdout', Logger::DEBUG),
                ],
                "format"     => null,
                "processors" => [
                    MemoryUsageProcessor::class,
                ]
            ],
        ]);

        Log::channel('test')->info('1');
        Log::channel('test')->info('1');
        Log::channel('test2')->info('2');
        Log::channel('test2')->info('2');
        Log::channel('test3')->info('3');
        Log::channel('test3')->info('3');
        Log::info('info');
        $this->assertEquals(1, 1);
    }
}