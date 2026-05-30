<?php

namespace Test;

use LiteView\Kernel\Visitor;
use LiteView\Support\Dispatcher;
use PHPUnit\Framework\TestCase;

class DispatcherTest extends TestCase
{
    public function testClosureAction()
    {
        $target = [
            'action' => function (Visitor $visitor) {
                return 'hello';
            },
            'middleware' => [],
        ];

        $result = Dispatcher::work($target, null, new Visitor());
        $this->assertEquals('hello', $result);
    }

    public function testClassArrayAction()
    {
        $target = [
            'action' => [TestController::class, 'index'],
            'middleware' => [],
        ];

        $result = Dispatcher::work($target, null, new Visitor());
        $this->assertEquals('index_response', $result);
    }

    public function testClassAtMethodAction()
    {
        $target = [
            'action' => TestController::class . '@show',
            'middleware' => [],
        ];

        $result = Dispatcher::work($target, null, new Visitor());
        $this->assertEquals('show_response', $result);
    }

    public function testMiddlewarePipelineOrder()
    {
        $target = [
            'action' => function (Visitor $visitor) {
                return 'core';
            },
            'middleware' => [MiddlewareA::class, MiddlewareB::class],
        ];

        $result = Dispatcher::work($target, null, new Visitor());
        $this->assertEquals('[A][B]core[B][A]', $result);
    }

    public function testMiddlewareCanModifyResponse()
    {
        $target = [
            'action' => function (Visitor $visitor) {
                return 'ok';
            },
            'middleware' => [MiddlewareUpper::class],
        ];

        $result = Dispatcher::work($target, null, new Visitor());
        $this->assertEquals('OK', $result);
    }

    public function testActionReceivesParams()
    {
        $target = [
            'action' => function (Visitor $visitor, $id) {
                return 'id:' . $id;
            },
            'middleware' => [],
        ];
        $params = [null, '123'];
        $result = Dispatcher::work($target, $params, new Visitor());
        $this->assertEquals('id:123', $result);
    }

    public function testNoMiddleware()
    {
        $target = [
            'action' => function (Visitor $visitor) {
                return 'direct';
            },
            'middleware' => [],
        ];

        $result = Dispatcher::work($target, null, new Visitor());
        $this->assertEquals('direct', $result);
    }
}

class TestController
{
    private $visitor;

    public function __construct(Visitor $visitor)
    {
        $this->visitor = $visitor;
    }

    public function index()
    {
        return 'index_response';
    }

    public function show()
    {
        return 'show_response';
    }
}

class MiddlewareA
{
    public function handle(Visitor $visitor, $next)
    {
        return '[A]' . $next($visitor) . '[A]';
    }
}

class MiddlewareB
{
    public function handle(Visitor $visitor, $next)
    {
        return '[B]' . $next($visitor) . '[B]';
    }
}

class MiddlewareUpper
{
    public function handle(Visitor $visitor, $next)
    {
        $result = $next($visitor);
        return strtoupper($result);
    }
}