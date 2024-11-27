<?php


//const WORKING_DIR = __DIR__ . '/tests/1';
use LiteView\Kernel\Visitor;

require __DIR__ . '/vendor/autoload.php';

print32(1);

class X
{
    public static function work($visitor, $target, $params = null)
    {
        $action = $target['action'];

        if (is_callable($action)) {
            if (is_array($action)) {
                list($class, $method) = $action;
                $instance = new $class($visitor);
                return self::invokeWithParams([$instance, $method], $params);
            }
            return self::invokeWithParams($action, $params);
        }

        list($class, $method) = explode('@', $action);
        $instance = new $class($visitor);
        return self::invokeWithParams([$instance, $method], $params);
    }

    private static function invokeWithParams($callable, $params)
    {
        if (!is_array($params)) {
            return call_user_func($callable);
        }

        // 使用反射解析参数
        $reflection = is_array($callable)
            ? new \ReflectionMethod($callable[0], $callable[1])
            : new \ReflectionFunction($callable);

        $args = [];
        foreach ($reflection->getParameters() as $parameter) {
            $name = $parameter->getName();
            if (isset($params[$name])) {
                $args[] = $params[$name]; // 从 $params 提取对应的值
            } elseif ($parameter->isDefaultValueAvailable()) {
                $args[] = $parameter->getDefaultValue(); // 使用默认值
            } else {
                throw new \InvalidArgumentException("Missing required parameter: $name");
            }
        }

        return call_user_func_array($callable, $args);
    }
}


function f1(Visitor $visitor)
{

}

$ref = new ReflectionFunction('f1');
foreach ($ref->getParameters() as $param) {
//    var_dump($param->getName());
    var_dump($param->getType()->getName());
}