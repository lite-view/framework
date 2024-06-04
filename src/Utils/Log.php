<?php

namespace LiteView\Utils;


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
        $logger = self::employ();
        return call_user_func_array([$logger, $method], $args);
    }

    public static function employ($name = 'default'): Logger
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

            // 创建 logger
            $logger = new Logger($name);
            
            // 创建 handler
            $Handler_arr = $channel['Handler'];
            if(!is_array($Handler_arr)){
                $Handler_arr = [$Handler_arr];
            }
            foreach($Handler_arr as $Handler){
                if (RotatingFileHandler::class === $Handler) {
                    $stream = new RotatingFileHandler($channel['path'], $channel['days'] ?? 7, $channel['level']);
                } else {
                    $stream = new $Handler($channel['path'], $channel['level']);
                }
                $stream->setFormatter(self::line_formatter());
                $logger->pushHandler($stream);
            }

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
