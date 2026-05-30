<?php

namespace Test;

use LiteView\Kernel\Route;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    protected function setUp(): void
    {
        Route::reset();
    }

    public function testStaticGetRoute()
    {
        Route::get('/hello', 'Handler@hello');
        $_SERVER['PATH_INFO'] = '/hello';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        [$target, $params] = Route::match();
        $this->assertNotNull($target);
        $this->assertEquals(['GET'], $target['method']);
        $this->assertNull($params);
    }

    public function testStaticPostRoute()
    {
        Route::post('/submit', 'Handler@submit');
        $_SERVER['PATH_INFO'] = '/submit';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        [$target, $params] = Route::match();
        $this->assertNotNull($target);
        $this->assertEquals(['POST'], $target['method']);
    }

    public function testAnyRouteMatchAllMethods()
    {
        Route::any('/any', 'Handler@any');

        $_SERVER['PATH_INFO'] = '/any';

        $_SERVER['REQUEST_METHOD'] = 'GET';
        [$target,] = Route::match();
        $this->assertNotNull($target);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        [$target,] = Route::match();
        $this->assertNotNull($target);

        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        [$target,] = Route::match();
        $this->assertNotNull($target);
    }

    public function testMethodNotMatchReturnsNull()
    {
        Route::get('/only-get', 'Handler@get');
        $_SERVER['PATH_INFO'] = '/only-get';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        [$target, $params] = Route::match();
        $this->assertNull($target);
    }

    public function testStaticRouteNotFound()
    {
        Route::get('/exists', 'Handler@get');
        $_SERVER['PATH_INFO'] = '/not-exists';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        [$target, $params] = Route::match();
        $this->assertNull($target);
    }

    public function testRequiredParameter()
    {
        Route::get('/user/{id}', 'Handler@show');
        [$target, $params] = Route::matchParamRoute('/user/123', 'get');
        $this->assertNotNull($target);
        $this->assertEquals('/user/123', $params[0]);
        $this->assertEquals('123', $params[1]);
    }

    public function testOptionalParameter()
    {
        Route::get('/list/{page?}', 'Handler@list');

        [$target, $params] = Route::matchParamRoute('/list/2', 'get');
        $this->assertNotNull($target);
        $this->assertEquals('2', $params[1]);

        [$target, $params] = Route::matchParamRoute('/list', 'get');
        $this->assertNotNull($target);
    }

    public function testMixedRequiredAndOptionalParameters()
    {
        Route::get('/post/{id}/{slug?}', 'Handler@post');

        [$target, $params] = Route::matchParamRoute('/post/5/hello', 'get');
        $this->assertNotNull($target);
        $this->assertEquals('5', $params[1]);
        $this->assertEquals('hello', $params[2]);

        [$target, $params] = Route::matchParamRoute('/post/5', 'get');
        $this->assertNotNull($target);
        $this->assertEquals('5', $params[1]);
    }

    public function testCustomRegexParameter()
    {
        Route::get('/img/{path}', 'Handler@img', [], ['path' => '.+']);

        [$target, $params] = Route::matchParamRoute('/img/a/b/c.png', 'get');
        $this->assertNotNull($target);
        $this->assertEquals('a/b/c.png', $params[1]);
    }

    public function testCustomRegexParameterMustBeValid()
    {
        $this->expectException(\InvalidArgumentException::class);
        Route::get('/bad/{id}', 'Handler@bad', [], ['id' => '[invalid']);
    }

    public function testTrailingSlashTolerance()
    {
        Route::get('/hello', 'Handler@hello');

        [$target, $params] = Route::matchParamRoute('/hello', 'get');
        $this->assertNotNull($target);

        [$target, $params] = Route::matchParamRoute('/hello/', 'get');
        $this->assertNotNull($target);
    }

    public function testStaticRouteNotMatchPartial()
    {
        Route::get('/user/{id}', 'Handler@show');

        [$target, $params] = Route::matchParamRoute('/userabc', 'get');
        $this->assertNull($target);
    }

    public function testGroupPrefix()
    {
        Route::group('api', function () {
            Route::get('/users', 'Handler@users');
        });

        $_SERVER['PATH_INFO'] = '/api/users';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        [$target, $params] = Route::match();
        $this->assertNotNull($target);
    }

    public function testGroupMiddleware()
    {
        Route::group(['prefix' => 'api', 'middleware' => ['AuthMiddleware']], function () {
            Route::get('/posts', 'Handler@posts');
        });

        $_SERVER['PATH_INFO'] = '/api/posts';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        [$target, $params] = Route::match();
        $this->assertNotNull($target);
        $this->assertContains('AuthMiddleware', $target['middleware']);
    }

    public function testNestedGroup()
    {
        Route::group('api', function () {
            Route::group('v1', function () {
                Route::get('/status', 'Handler@status');
            });
        });

        $_SERVER['PATH_INFO'] = '/api/v1/status';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        [$target, $params] = Route::match();
        $this->assertNotNull($target);
    }

    public function testGroupDoesNotLeakPrefix()
    {
        Route::group('api', function () {
            Route::get('/inner', 'Handler@inner');
        });
        Route::get('/outer', 'Handler@outer');

        $_SERVER['PATH_INFO'] = '/outer';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        [$target, $params] = Route::match();
        $this->assertNotNull($target);
        $this->assertEquals('Handler@outer', $target['action']);
    }

    public function testQuickSkipsMagicMethods()
    {
        Route::quick('/ctrl', QuickTestController::class);

        $_SERVER['REQUEST_METHOD'] = 'GET';

        $_SERVER['PATH_INFO'] = '/ctrl/action1';
        [$target,] = Route::match();
        $this->assertNotNull($target);

        $_SERVER['PATH_INFO'] = '/ctrl/__construct';
        [$target,] = Route::match();
        $this->assertNull($target);
    }

    public function testAllOptionalParameters()
    {
        Route::get('/search/{keyword?}/{page?}', 'Handler@search');

        [$target, $params] = Route::matchParamRoute('/search', 'get');
        $this->assertNotNull($target);

        [$target, $params] = Route::matchParamRoute('/search/php', 'get');
        $this->assertNotNull($target);
        $this->assertEquals('php', $params[1]);

        [$target, $params] = Route::matchParamRoute('/search/php/2', 'get');
        $this->assertNotNull($target);
        $this->assertEquals('php', $params[1]);
        $this->assertEquals('2', $params[2]);
    }

    public function testRouteWithSuffix()
    {
        Route::get('/book/{id}.html', 'Handler@book');

        [$target, $params] = Route::matchParamRoute('/book/123.html', 'get');
        $this->assertNotNull($target);
        $this->assertEquals('123', $params[1]);
    }

    public function testStaticRoutePriorityOverParamRoute()
    {
        Route::get('/user/me', 'Handler@me');
        Route::get('/user/{id}', 'Handler@id');

        $_SERVER['PATH_INFO'] = '/user/me';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        [$target, $params] = Route::match();
        $this->assertNotNull($target);
        $this->assertEquals('Handler@me', $target['action']);
        $this->assertNull($params);
    }

    public function testOptionalParamWithLiteralBetween()
    {
        Route::get('/e/{id?}/name/{name?}', 'Handler@e');

        [$target, $params] = Route::matchParamRoute('/e/1/name/scj', 'get');
        $this->assertNotNull($target);
        $this->assertEquals('1', $params[1]);
        $this->assertEquals('scj', $params[2]);

        [$target, $params] = Route::matchParamRoute('/e/name', 'get');
        $this->assertNotNull($target);
    }

    public function testQuickRegistersPublicMethods()
    {
        Route::quick('/ctrl', QuickTestController::class);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['PATH_INFO'] = '/ctrl/action1';
        [$target,] = Route::match();
        $this->assertNotNull($target);
        $this->assertEquals([QuickTestController::class, 'action1'], $target['action']);

        $_SERVER['PATH_INFO'] = '/ctrl/action2';
        [$target,] = Route::match();
        $this->assertNotNull($target);
    }

    public function testSamePathDifferentMethods()
    {
        Route::get('/users', 'Handler@index');
        Route::post('/users', 'Handler@store');

        $_SERVER['PATH_INFO'] = '/users';

        $_SERVER['REQUEST_METHOD'] = 'GET';
        [$target, $params] = Route::match();
        $this->assertNotNull($target);
        $this->assertEquals('Handler@index', $target['action']);
        $this->assertNull($params);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        [$target, $params] = Route::match();
        $this->assertNotNull($target);
        $this->assertEquals('Handler@store', $target['action']);
        $this->assertNull($params);
    }

    public function testSamePathSameMethodTriggersError()
    {
        $this->expectException(\ErrorException::class);
        Route::get('/dup', 'Handler@first');
        Route::get('/dup', 'Handler@second');
    }

    public function testMethodNotAllowedOnSamePath()
    {
        Route::get('/users', 'Handler@index');
        Route::post('/users', 'Handler@store');

        $_SERVER['PATH_INFO'] = '/users';
        $_SERVER['REQUEST_METHOD'] = 'DELETE';

        [$target, $params] = Route::match();
        $this->assertNull($target);
    }

    public function testSamePathParamRouteDifferentMethods()
    {
        Route::get('/items/{id}', 'Handler@show');
        Route::rule(['PUT', 'PATCH'], '/items/{id}', 'Handler@update');

        $_SERVER['PATH_INFO'] = '/items/42';

        $_SERVER['REQUEST_METHOD'] = 'GET';
        [$target, $params] = Route::match();
        $this->assertNotNull($target);
        $this->assertEquals('Handler@show', $target['action']);
        $this->assertEquals('42', $params[1]);

        $_SERVER['REQUEST_METHOD'] = 'PUT';
        [$target, $params] = Route::match();
        $this->assertNotNull($target);
        $this->assertEquals('Handler@update', $target['action']);

        $_SERVER['REQUEST_METHOD'] = 'PATCH';
        [$target, $params] = Route::match();
        $this->assertNotNull($target);
        $this->assertEquals('Handler@update', $target['action']);
    }

    public function testApiResource()
    {
        Route::apiResource('/users', ApiResourceTestController::class);

        $_SERVER['PATH_INFO'] = '/users';

        $_SERVER['REQUEST_METHOD'] = 'GET';
        [$target, $params] = Route::match();
        $this->assertNotNull($target);
        $this->assertEquals([ApiResourceTestController::class, 'index'], $target['action']);

        $_SERVER['REQUEST_METHOD'] = 'POST';
        [$target, $params] = Route::match();
        $this->assertNotNull($target);
        $this->assertEquals([ApiResourceTestController::class, 'store'], $target['action']);

        $_SERVER['PATH_INFO'] = '/users/5';

        $_SERVER['REQUEST_METHOD'] = 'GET';
        [$target, $params] = Route::match();
        $this->assertNotNull($target);
        $this->assertEquals([ApiResourceTestController::class, 'show'], $target['action']);

        $_SERVER['REQUEST_METHOD'] = 'PUT';
        [$target, $params] = Route::match();
        $this->assertNotNull($target);
        $this->assertEquals([ApiResourceTestController::class, 'update'], $target['action']);

        $_SERVER['REQUEST_METHOD'] = 'PATCH';
        [$target, $params] = Route::match();
        $this->assertNotNull($target);
        $this->assertEquals([ApiResourceTestController::class, 'update'], $target['action']);

        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        [$target, $params] = Route::match();
        $this->assertNotNull($target);
        $this->assertEquals([ApiResourceTestController::class, 'destroy'], $target['action']);
    }

    public function testApiResourceDeleteNotMatchGet()
    {
        Route::apiResource('/posts', ApiResourceTestController::class);

        $_SERVER['PATH_INFO'] = '/posts';
        $_SERVER['REQUEST_METHOD'] = 'DELETE';

        [$target, $params] = Route::match();
        $this->assertNull($target);
    }

    public function testAnyWithSamePathOverlap()
    {
        $this->expectException(\ErrorException::class);
        Route::any('/overlap', 'Handler@any');
        Route::get('/overlap', 'Handler@get');
    }
}

class QuickTestController
{
    public function __construct() {}
    public function action1() {}
    public function action2() {}
    public function __toString() { return ''; }
}

class ApiResourceTestController
{
    public function index() {}
    public function store() {}
    public function show($id) {}
    public function update($id) {}
    public function destroy($id) {}
}