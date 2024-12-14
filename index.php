<?php


const WORKING_DIR = __FILE__;
require_once __DIR__ . '/vendor/autoload.php';


use LiteView\Kernel\Route;

Route::get('/', function (LiteView\Kernel\Visitor $visitor) {
    var_dump($visitor->currentPath());
});


Route::group('group', function () {
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

    LiteView\Kernel\Route::quick('/quick', a::class);
});


Route::get('/error', function (LiteView\Kernel\Visitor $visitor) {
    try {
        echo 1 / 0;
        echo $a;
    } catch (Throwable $e) {
        var_dump($e->getMessage());
    }
});

Route::get('/exception', function (LiteView\Kernel\Visitor $visitor) {
    echo1(2);
    echo 'aaaaaaaaaaaaaaaaaaaaaaaaaaaa';
});

Route::get('/a/b/c', function (LiteView\Kernel\Visitor $visitor) {
    //var_dump($visitor->currentUri(['a' => 1]));
    var_dump($visitor->currentPath());
});

class M1
{
    public function handle($v, $next)
    {
        echo 'm1', PHP_EOL;
        return $next($v);
    }
}

class M2
{
    public function handle($v, $next)
    {
        $r = $next($v);
        echo 'm2 : ' . $r, PHP_EOL;
        return $r;
    }
}

Route::get('/ai_img/{path}', function ($v, $b) {
    var_dump($b);
    return 'end';
}, [M1::class, M2::class]);
Route::get('/ai_img/{path}', function (LiteView\Kernel\Visitor $visitor) {
    var_dump($visitor->currentPath());
    var_dump($visitor->currentUri());
    echo 1;
}, [], ['path' => '.+']);


// 获取路由
list($target, $params) = Route::match();
if ($target) {
    $rsp = \LiteView\Support\Dispatcher::work($target, $params, new \LiteView\Kernel\Visitor());
    echo $rsp;
} else {
    echo 404;
}



