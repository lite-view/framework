<?php

namespace LiteView\Utils;


use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Processor\MemoryUsageProcessor;


/**
 * @method static void alert(string $message, array $context = [])
 * @method static void critical(string $message, array $context = [])
 * @method static void debug(string $message, array $context = [])
 * @method static void emergency(string $message, array $context = [])
 * @method static void error(string $message, array $context = [])
 * @method static void info(string $message, array $context = [])
 * @method static void log($level, string $message, array $context = [])
 * @method static void notice(string $message, array $context = [])
 * @method static void warning(string $message, array $context = [])
 */
class Log
{
    protected static $logging = [];

    // 当我们调用一个不存在的静态方法时，会自动调用 __callstatic()
    public static function __callstatic($method, $args)
    {
        $logger = self::employ();
        return call_user_func_array([$logger, $method], $args);
    }

    public static function employ($name = 'default'): Logger
    {
        if (isset(self::$logging[$name])) {
            return self::$logging[$name];
        }
        $config = cfg('logging');
        $config['main'] = self::mainCfg();
        $channel = $config[$name];

        // 创建 logger
        $logger = new Logger($name);

        // push handler
        $handler_arr = $channel['handlers'];
        if (!is_array($handler_arr)) {
            $handler_arr = [$handler_arr];
        }
        foreach ($handler_arr as $handler) {
            $handler->setFormatter(self::lineFormatter($channel));
            $logger->pushHandler($handler);
        }

        // push processor
        if (isset($channel['processors'])) {
            foreach ($channel['processors'] as $class) {
                $logger->pushProcessor(new $class());
            }
        }

        self::$logging[$name] = $logger;
        return self::$logging[$name];
    }

    protected static function mainCfg(): array
    {
        return [
            "handlers" => [
                new StreamHandler(root_path("storage/logs/main.log"), Logger::DEBUG),
            ],
            "processors" => [
                MemoryUsageProcessor::class
            ]
        ];
    }

    protected static function lineFormatter($channel): LineFormatter
    {
        $dateFormat = "Y-m-d H:i:s";
        // the default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
        $output = "%datetime% > %level_name% > %message% %context% %extra%\n";
        if (!empty($channel['format'])) {
            $output = $channel['format'];
        }
        return new LineFormatter($output, $dateFormat);
    }
}
