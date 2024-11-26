<?php

namespace Test;


use LiteView\Kernel\Route;
use PHPUnit\Framework\TestCase;


class RouteTest extends TestCase
{
    public function test01()
    {
        Route::rule('*', 'a', null);
        Route::rule('get', 'b1', null, [1, 2, 3]);
        Route::rule('post', 'b2', null, [1, 2, 3]);
        Route::get('get', null, [1, 2, 3]);
        Route::post('post', null, [1, 2, 3]);
        Route::any('any', null, [1, 2, 3]);
        Route::group('g1', function () {
            Route::rule('*', 'a', null, [1, 2, 3]);
            Route::rule('get', 'b1', null, [1, 2, 3]);
            Route::rule('post', 'b2', null, [1, 2, 3]);
            Route::get('get', null, [1, 2, 3]);
            Route::post('post', null, [1, 2, 3]);
            Route::any('any', null, [1, 2, 3]);
            Route::group(['prefix' => 'g2', 'middleware' => [0]], function () {
                Route::rule('*', 'a', null, [1, 2, 3]);
                Route::rule('get', 'b1', null, [1, 2, 3]);
                Route::rule('post', 'b2', null, [1, 2, 3]);
                Route::get('get', null, [1, 2, 3]);
                Route::post('post', null, [1, 2, 3]);
                Route::any('any', null, [1, 2, 3]);
                Route::group(['prefix' => 'g2', 'middleware' => [11]], function () {
                    Route::any('any', null, [1, 2, 3]);
                });
            });
        });
        Route::group(['prefix' => 'g3', 'middleware' => [-1, 0]], function () {
            Route::rule('*', 'a', null, [1, 2, 3]);
            Route::rule('get', 'b1', null, [1, 2, 3]);
            Route::rule('post', 'b2', null, [1, 2, 3]);
            Route::get('get', null, [1, 2, 3]);
            Route::post('post', null, [1, 2, 3]);
            Route::any('any', null, [1, 2, 3]);
        });
        Route::_print();
        $this->assertEquals(1, 1);
    }

    public function test02()
    {
        Route::get('a/{id}', null);
        $this->assertEquals(['/a/1', '1'], Route::matchParamRoute('/a/1', 'get')[1]);

        Route::get('b/{id?}', null);
        $this->assertEquals(['/b/', ''], Route::matchParamRoute('/b/', 'get')[1]);
        $this->assertEquals(['/b/1', '1'], Route::matchParamRoute('/b/1', 'get')[1]);

        Route::get('c/{id}/name/{name}', null);
        $this->assertEquals(['/c/1/name/scj', '1', 'scj'], Route::matchParamRoute('/c/1/name/scj', 'get')[1]);

        Route::get('d/{id}/name/{name?}', null);
        $this->assertEquals(['/d/1/name/scj', '1', 'scj'], Route::matchParamRoute('/d/1/name/scj', 'get')[1]);
        $this->assertEquals(['/d/1/name/', '1', ''], Route::matchParamRoute('/d/1/name/', 'get')[1]);

        Route::get('e/{id?}/name/{name?}', null);
        $this->assertEquals(['/e/1/name/scj', '1', 'scj'], Route::matchParamRoute('/e/1/name/scj', 'get')[1]);
        $this->assertEquals(['/e/1/name/', '1', ''], Route::matchParamRoute('/e/1/name/', 'get')[1]);
        $this->assertEquals(['/e/name/', '', ''], Route::matchParamRoute('/e/name/', 'get')[1]);

        Route::get('f/{id?}/{tel?}/{name?}', null);
        $this->assertEquals(['/f', '', '', ''], Route::matchParamRoute('/f', 'get')[1]);
        $this->assertEquals(['/f/1', '1', '', ''], Route::matchParamRoute('/f/1', 'get')[1]);
        $this->assertEquals(['/f/1/2', '1', '2', ''], Route::matchParamRoute('/f/1/2', 'get')[1]);
        $this->assertEquals(['/f/1/2/3', '1', '2', '3'], Route::matchParamRoute('/f/1/2/3', 'get')[1]);
    }

    public function test03()
    {
        Route::get('/ai_img/{path}', null);
        $this->assertEquals(['/ai_img/a', 'a'], Route::matchParamRoute('/ai_img/a', 'get')[1]);
        $this->assertEquals(null, Route::matchParamRoute('/ai_img/a/b/c', 'get')[1]);
        Route::get('/ai_img/{path}', null, [], ['path' => '.+']);
        $this->assertEquals(['/ai_img/a/b/c', 'a/b/c'], Route::matchParamRoute('/ai_img/a/b/c', 'get')[1]);
        $this->assertEquals(['/ai_img/a/b/c.png', 'a/b/c.png'], Route::matchParamRoute('/ai_img/a/b/c.png', 'get')[1]);

        Route::get('/book/{id}.html', null);
        $this->assertEquals(['/book/xxx.html', 'xxx'], Route::matchParamRoute('/book/xxx.html', 'get')[1]);
    }

    public function test04()
    {
        Route::get('/shudan/{tag}/', null, [], ['tag' => '(?!.*\.html$).*']);
        Route::get('/shudan/{tag}/{id}.html', null);
        $this->assertEquals(['/shudan/t1/1.html', 't1', '1'], Route::matchParamRoute('/shudan/t1/1.html', 'get')[1]);
        $this->assertEquals(['/shudan/t1', 't1'], Route::matchParamRoute('/shudan/t1', 'get')[1]);
    }
}