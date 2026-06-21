<?php

namespace LiteView\Support;


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
    protected static array $loggers = [];
    protected static ?array $defaultCfgCache = null;

    public static function __callStatic($method, $args)
    {
        $logger = self::channel();
        return call_user_func_array([$logger, $method], $args);
    }

    public static function channel($name = 'default'): Logger
    {
        if (isset(self::$loggers[$name])) {
            return self::$loggers[$name];
        }
        $config = array_merge(['default' => self::defaultCfg()], cfg('logging', []));

        $channel = $config[$name];
        $logger  = new Logger($name); // 创建 logger

        // push handler
        $handlers = $channel['handlers'];
        if (is_callable($handlers)) {
            $handlers = $handlers();  // 延迟（lazy）创建 handler
        } elseif (!is_array($handlers)) {
            $handlers = [$handlers];
        }
        foreach ($handlers as $handler) {
            $handler->setFormatter(self::lineFormatter($channel));
            $logger->pushHandler($handler);
        }

        // push processor
        if (isset($channel['processors'])) {
            foreach ($channel['processors'] as $class) {
                $logger->pushProcessor(new $class());
            }
        }

        self::$loggers[$name] = $logger;
        return self::$loggers[$name];
    }

    //=======================[默认配置]=======================
    protected static function defaultCfg(): array
    {
        if (self::$defaultCfgCache === null) {
            self::$defaultCfgCache = [
                "handlers"   => [
                    new StreamHandler(root_path("storage/logs/app.log"), Logger::DEBUG),
                    new StreamHandler('php://stdout', Logger::DEBUG),
                ],
                "processors" => [
                    MemoryUsageProcessor::class
                ]
            ];
        }
        return self::$defaultCfgCache;
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