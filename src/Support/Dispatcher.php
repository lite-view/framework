<?php


namespace LiteView\Support;


use LiteView\Kernel\Visitor;


class Dispatcher
{
    // 根据环境加载配置
    public static function checkEnv()
    {
        $env_config = root_path() . 'config.' . cfg('app_env') . '.json';
        if (file_exists($env_config)) {
            $arr = json_decode(file_get_contents($env_config), true);
            if (!is_array($arr)) {
                exit("环境配置文件解析失败($env_config)！请检查json格式是否有误");
            }
            foreach ($arr as $name => $value) {
                ToolMan::setCfg($name, $value);
            }
        }
    }

    // 请求处理
    public static function work(array $target, ?array $params, Visitor $visitor)
    {
        $params[0]  = $visitor;
        $action     = $target['action'];
        $middleware = $target['middleware'];

        // 将所有中间件嵌套成闭包链
        $pipeline = array_reduce(
            array_reverse($middleware), // 倒序处理
            function ($next, $middleware) {
                return function (Visitor $visitor) use ($next, $middleware) {
                    return call_user_func_array([new $middleware(), 'handle'], [$visitor, $next]);
                };
            },
            function (Visitor $visitor) use ($action, $params) {
                if (is_callable($action) && !is_array($action)) {
                    return call_user_func_array($action, $params);
                }

                if (is_string($action)) {
                    list($class, $method) = explode('@', $action);
                } else {
                    // is_callable
                    list($class, $method) = $action;
                }
                return call_user_func_array([new $class($visitor), $method], $params);
            }
        );

        // 执行闭包链
        return call_user_func_array($pipeline, $params);
    }
}