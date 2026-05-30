<?php

const WORKING_DIR = __FILE__;
require_once __DIR__ . '/vendor/autoload.php';

use LiteView\Kernel\Route;
use LiteView\Kernel\Visitor;
use LiteView\Kernel\View;
use LiteView\Support\ApiResourceController;

class DemoController extends ApiResourceController
{
    public function index(Visitor $visitor)
    {
        return json_encode(['message' => 'apiResource: list users']);
    }

    public function store(Visitor $visitor)
    {
        return json_encode(['message' => 'apiResource: create user']);
    }

    public function show(Visitor $visitor, $id)
    {
        return json_encode(['message' => "apiResource: show user $id"]);
    }

    public function update(Visitor $visitor, $id)
    {
        return json_encode(['message' => "apiResource: update user $id"]);
    }

    public function destroy(Visitor $visitor, $id)
    {
        return json_encode(['message' => "apiResource: delete user $id"]);
    }
}

class LogMiddleware
{
    public function handle(Visitor $v, $next)
    {
        \LiteView\Utils\Log::info('request', ['path' => $v->currentPath()]);
        return $next($v);
    }
}

class TimeMiddleware
{
    public function handle(Visitor $v, $next)
    {
        $start = microtime(true);
        $r     = $next($v);
        $ms    = round((microtime(true) - $start) * 1000, 2);
        return "$r <!-- {$ms}ms -->";
    }
}


// Visitor demo
Route::get('/demo/visitor', function (Visitor $visitor) {
    return json_encode([
        'path'   => $visitor->currentPath(),
        'ip'     => $visitor->ip(),
        'method' => $_SERVER['REQUEST_METHOD'],
    ]);
});

// Parameter route
Route::get('/demo/user/{id}', function (Visitor $visitor, $id) {
    return json_encode(['id' => $id, 'path' => $visitor->currentPath()]);
});

// Wildcard regex parameter
Route::get('/demo/file/{path}', function (Visitor $visitor, $path) {
    return json_encode(['file' => $path]);
}, [], ['path' => '.+']);

// Middleware pipeline
Route::get('/demo/mw', function (Visitor $visitor) {
    return 'response from action';
}, [TimeMiddleware::class]);

// Error page demo
Route::get('/demo/error', function (Visitor $visitor) {
    echo1(2);
});

// Route group
Route::group('api', function () {
    Route::apiResource('/users', DemoController::class, [LogMiddleware::class]);
});


// Homepage — render Twig template
Route::get('/', function (Visitor $visitor) {
    $rawRoutes = Route::_all_rotes();
    $routes = [];
    foreach ($rawRoutes as $pathKey => $targets) {
        $path = explode('>>>', $pathKey)[0];
        foreach ($targets as $target) {
            $methods = is_array($target['method']) ? $target['method'] : [$target['method']];
            foreach ($methods as $method) {
                $displayMethod = $method === '*' ? 'ANY' : strtoupper($method);
                $cssClass = $method === '*' ? 'm-any' : 'm-' . strtolower($method);
                $isGet = strtolower($method) === 'get';
                $hasParams = str_contains($path, '{');

                $action = $target['action'];
                if (is_array($action)) {
                    $actionStr = (is_object($action[0]) ? get_class($action[0]) : $action[0]) . '@' . $action[1];
                } elseif (is_string($action)) {
                    $actionStr = $action;
                } else {
                    $actionStr = 'Closure';
                }

                $fetchMethod = $method === '*' ? 'GET' : strtoupper($method);
                $isLinkable = $isGet && !$hasParams;

                $routes[] = [
                    'method'      => $displayMethod,
                    'cssClass'    => $cssClass,
                    'path'        => $path,
                    'action'      => $actionStr,
                    'fetchMethod' => $fetchMethod,
                    'isLinkable'  => $isLinkable,
                ];
            }
        }
    }

    View::setVisitor($visitor);
    return View::renderTwig('index.twig', [
        'routes' => $routes,
    ]);
});

// Dispatch
[$target, $params] = Route::match();
if ($target) {
    $rsp = \LiteView\Support\Dispatcher::work($target, $params, new Visitor());
    echo $rsp;
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Not Found']);
}