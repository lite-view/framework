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

Route::get('/ai_img/{path}', function ($a, $b) {
    var_dump($a, $b);
});
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



