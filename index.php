<?php


const WORKING_DIR = __FILE__;
require_once __DIR__ . '/vendor/autoload.php';


class a
{
    function x()
    {
        echo 'x';
    }

    function y()
    {
        echo $a;
    }

    function z()
    {
        echo 'z';
    }
}

LiteView\Kernel\Route::get('/', function (LiteView\Kernel\Visitor $visitor) {
    LiteView\Log\Log::error('xxx');
    var_dump($visitor->currentPath());//
});


LiteView\Kernel\Route::group('group', function () {
    LiteView\Kernel\Route::quick('/quick', 'a');
});


LiteView\Kernel\Route::get('/error', function (LiteView\Kernel\Visitor $visitor) {
    try {
        echo 1 / 0;
        echo $a;
    } catch (Throwable $e) {
        var_dump($e->getMessage());
    }
});

LiteView\Kernel\Route::get('/exception', function (LiteView\Kernel\Visitor $visitor) {
    echo1(1);
});

LiteView\Kernel\Route::get('/a/b/c', function (LiteView\Kernel\Visitor $visitor) {
    //var_dump($visitor->currentUri(['a' => 1]));
    var_dump($visitor->currentPath());
});

// 获取路由
$route = LiteView\Kernel\Route::current_route();
list($action, $middleware) = array_values($route);

// 请求处理
$visitor = new LiteView\Kernel\Visitor();
if (is_callable($action)) {
    $response = $action($visitor);
} else {
    list($class, $action) = explode('@', $action);
    $response = (new $class($visitor))->$action($visitor);
}
echo $response;
