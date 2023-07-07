<?php

namespace LiteView\Aides;


use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
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
        $logger = self::by();
        return call_user_func_array([$logger, $method], $args);
    }

    public static function by($name = 'default')
    {
        if (!isset(self::$logging[$name])) {
            $config = cfg('logging');
            $config['main'] = [
                "Handler" => StreamHandler::class,
                "path" => root_path("storage/logs/main.log"),
                "level" => Logger::DEBUG,
                "processors" => [
                    MemoryUsageProcessor::class
                ]
            ];
            $channel = $config[$name];

            // 创建 handler
            if (RotatingFileHandler::class === $channel['Handler']) {
                $stream = new RotatingFileHandler($channel['path'], $channel['days'] ?? 7, $channel['level']);
            } else {
                $stream = new $channel['Handler']($channel['path'], $channel['level']);
            }
            $stream->setFormatter(self::line_formatter());

            // 创建 logger
            $logger = new Logger($name);
            $logger->pushHandler($stream);

            // push processor
            if (isset($channel['processors'])) {
                foreach ($channel['processors'] as $class) {
                    $logger->pushProcessor(new $class());
                }
            }
            self::$logging[$name] = $logger;
        }

        return self::$logging[$name];
    }

    protected static function line_formatter()
    {
        $dateFormat = "Y-m-d H:i:s";
        // the default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
        $output = "%datetime% > %level_name% > %message% %context% %extra%\n";
        return new LineFormatter($output, $dateFormat);
    }
}
