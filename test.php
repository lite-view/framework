<?php


//const WORKING_DIR = __DIR__ . '/tests/1';
use LiteView\Kernel\Visitor;

require __DIR__ . '/vendor/autoload.php';

for ($i = 0; $i < 3; $i++) {
    echo $i;
    echo PHP_EOL;
    sleep(1);
}