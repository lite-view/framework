<?php


//const WORKING_DIR = __DIR__ . '/tests/1';
use LiteView\Kernel\Visitor;

require __DIR__ . '/vendor/autoload.php';

print32(1);


class Pipeline
{
    protected $middleware = [];
    protected $passable;

    /**
     * 设置需要处理的数据
     */
    public function send($passable)
    {
        $this->passable = $passable;
        return $this;
    }

    /**
     * 设置中间件数组
     */
    public function through(array $middleware)
    {
        $this->middleware = $middleware;
        return $this;
    }

    /**
     * 定义最终目标（控制器逻辑）
     */
    public function then(Closure $destination)
    {
        // 将所有中间件反向嵌套成闭包链
        $pipeline = array_reduce(
            array_reverse($this->middleware), // 倒序处理
            function ($next, $middleware) {
                return function ($passable) use ($next, $middleware) {
                    return $middleware($passable, $next);
                };
            },
            $destination
        );

        // 执行闭包链
        return $pipeline($this->passable);
    }
}

$middleware1 = function ($request, $next) {
    echo "Middleware1-Before-";
    $response = $next($request);
    echo "Middleware1-After-";
    return $response;
};

$middleware2 = function ($request, $next) {
    echo "Middleware2-Before-";
    $response = $next($request);
    echo "Middleware2-After-";
    return $response;
};

$middleware3 = function ($request, $next) {
    echo "Middleware3-Before-";
    $response = $next($request);
    echo "Middleware3-After-";
    return $response;
};

$controller = function ($request) {
    echo "ControllerRun-";
    return "FinalResult";
};

$request = "RequestData"; // 模拟请求数据

$result = (new Pipeline())
    ->send($request) // 设置传递的请求数据
    ->through([$middleware1, $middleware2, $middleware3]) // 传递中间件数组
    ->then($controller); // 定义最终目标

echo $result; // 打印最终结果