<?php



const WORKING_DIR = __FILE__;
require_once __DIR__ . '/vendor/autoload.php';


use LiteView\Kernel\Route;

Route::get('/', function (LiteView\Kernel\Visitor $visitor) {
    var_dump($visitor->currentPath());
});

Route::post('/', function (LiteView\Kernel\Visitor $visitor) {
    var_dump($visitor->currentPath());
});

//for ($i = 0; $i < 3; $i++) {
//    echo $i;
//    echo PHP_EOL;
//    sleep(1);
//}

Route::_print();