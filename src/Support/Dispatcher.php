<?php


namespace LiteView\Support;


class Dispatcher
{
    // 根据环境加载配置
    public static function checkEnv()
    {
        $env_config = root_path() . 'config.' . cfg('app_env') . '.json';
        if (file_exists($env_config)) {
            $string = file_get_contents($env_config);
            $config = json_decode($string, true);
            foreach ($config as $name => $value) {
                ToolMan::setCfg($name, $value);
            }
        }
    }

    // 请求处理
    public static function work($visitor, $action)
    {
        if (is_callable($action)) {
            if (is_array($action)) {
                list($class, $action) = $action;
                return (new $class($visitor))->$action($visitor);
            }
            return $action($visitor);
        }
        list($class, $action) = explode('@', $action);
        return (new $class($visitor))->$action($visitor);
    }

    // 前置中间件
    public static function before($visitor, $mid)
    {
        if (method_exists($mid, 'handle')) {
            return $mid->handle($visitor);
        }
        return null;
    }

    // 后置中间件
    public static function after($visitor, $mid, $response)
    {
        if (method_exists($mid, 'after')) {
            $mid->after($visitor, $response);
        }
    }
}