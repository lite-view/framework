<?php

namespace LiteView\Support;


use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\MemoryUsageProcessor;
use Psr\Log\AbstractLogger;


class Log extends AbstractLogger
{
    protected static array $logging = [];

    public static function __callstatic($method, $args)
    {
        $logger = self::employ();
        return call_user_func_array([$logger, $method], $args);
    }

    /**
     * @inheritDoc
     */
    public function log($level, $message, array $context = []): void
    {
        $logger = self::employ();
        $logger->log($level, $message, $context);
    }

    public static function employ($name = 'default'): Logger
    {
        if (isset(self::$logging[$name])) {
            return self::$logging[$name];
        }
        $config = array_merge(
            cfg('logging', []),
            ['main' => self::mainCfg()]
        );

        $cfg = $config[$name];
        $logger = new Logger($name);

        $handlers = $cfg['handlers'];
        if (is_callable($handlers)) {
            $handlers = $handlers();
        }
        if (!is_array($handlers)) {
            $handlers = [$handlers];
        }
        foreach ($handlers as $handler) {
            $handler->setFormatter(self::lineFormatter($cfg));
            $logger->pushHandler($handler);
        }

        if (isset($cfg['processors'])) {
            foreach ($cfg['processors'] as $class) {
                $logger->pushProcessor(new $class());
            }
        }

        self::$logging[$name] = $logger;
        return self::$logging[$name];
    }

    protected static function mainCfg(): array
    {
        return [
            "handlers"   => [
                new StreamHandler(root_path("storage/logs/main.log"), Logger::DEBUG),
            ],
            "processors" => [
                MemoryUsageProcessor::class
            ]
        ];
    }

    protected static function lineFormatter($cfg): LineFormatter
    {
        $dateFormat = "Y-m-d H:i:s";
        $output = "%datetime% > %level_name% > %message% %context% %extra%\n";
        if (!empty($cfg['format'])) {
            $output = $cfg['format'];
        }
        return new LineFormatter($output, $dateFormat);
    }
}
