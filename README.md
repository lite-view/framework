# lite-view/framework

[应用实例参考](https://github.com/lite-view/lite-view)

## 介绍

LiteView 是一个极简的 PHP MVC 框架，专注于提供清晰、可控、无黑盒的 Web 开发体验。依赖仅有 **Twig**（模板引擎）和 **Monolog**（日志），其余核心功能全部自研。

## 框架的初衷和意义

- **学习价值** — 从零构建框架是理解 PHP 请求生命周期、路由、中间件管道的最佳方式，比读大型框架源码更直观
- **个人/团队定制** — 没有不需要的抽象，改起来零负担，自己的项目用着最顺手
- **可控性** — 依赖只有 Twig + Monolog，没有黑盒，出问题能从根上排查
- **够用就好** — 很多 PHP 项目就是 CRUD + 几个接口，不需要重型框架，轻量即是优势

## 项目状态

项目版本**遵循** [语义化版本号](https://semver.org/lang/zh-CN/)

## 安装

```bash
composer require lite-view/framework
```

## 快速开始

> **注意**：仓库根目录的 `index.php` 是框架功能演示示例，展示了路由、中间件、控制器和模板渲染的用法。**请勿在生产环境中直接使用它**，而是参考示例编写你自己的入口文件。

创建你的项目入口文件（例如 `public/index.php`）：

```php
<?php

const WORKING_DIR = __FILE__;
require_once __DIR__ . '/../vendor/autoload.php';

use LiteView\Kernel\Route;
use LiteView\Kernel\Visitor;
use LiteView\Support\Dispatcher;

// 定义路由
Route::get('/', function (Visitor $visitor) {
    return json_encode(['message' => 'Hello, LiteView!']);
});

Route::get('/user/{id}', function (Visitor $visitor, $id) {
    return json_encode(['user_id' => $id]);
});

// 处理请求
[$target, $params] = Route::match();
if ($target) {
    echo Dispatcher::work($target, $params, new Visitor());
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Not Found']);
}
```

并在项目根目录创建 `config.json`：

```json
{
    "debug": true,
    "app_env": "dev"
}
```

## 本地调试

```bash
php -S 127.0.0.1:8000 public/index.php
```

### php内置服务器运行模式

- **路由模式**：`php -S 127.0.0.1:8888 public/index.php`
    - 所有请求都路由到 `index.php`
    - 如果匹配到存在的目录，`PATH_INFO` 不存在
    - 如果 URI 中带文件后缀，`PATH_INFO` 不存在
- **标准模式**：`php -S 127.0.0.1:8888`（不指定入口文件）
    - 首先尝试直接提供请求的文件（带后缀名），不存在则返回 404，不会进入 PHP 路由
    - 如果请求的是目录，则查找目录中的 `index.php` 或 `index.html`

---

## 核心功能

### 1. 路由系统 (`LiteView\Kernel\Route`)

支持静态路由、参数路由、正则约束、可选参数和路由分组。

```php
// 静态路由
Route::get('/hello', 'Controller@hello');
Route::post('/submit', 'Controller@submit');

// 参数路由
Route::get('/user/{id}', 'Controller@show');

// 可选参数
Route::get('/list/{page?}', 'Controller@list');

// 正则约束
Route::get('/file/{path}', 'Controller@file', [], ['path' => '.+']);

// 任意 HTTP 方法
Route::any('/api/data', 'Controller@data');

// 路由分组（支持前缀和中间件继承）
Route::group('api', function () {
    Route::get('/users', 'UserController@index');
    Route::post('/users', 'UserController@store');
});

// RESTful 资源路由（自动生成 index/store/show/update/destroy）
Route::apiResource('/users', UserController::class);

// 快捷路由（自动注册控制器所有公共方法）
Route::quick('/ctrl', MyController::class);
```

### 2. 中间件管道 (`LiteView\Support\Dispatcher`)

基于洋葱模型的中间件管道，支持前置处理、后置处理和响应修改。

```php
class LogMiddleware
{
    public function handle(Visitor $v, $next)
    {
        $start = microtime(true);
        $response = $next($v);
        $ms = round((microtime(true) - $start) * 1000, 2);
        return $response . " <!-- {$ms}ms -->";
    }
}

Route::get('/demo', 'Controller@demo', [LogMiddleware::class]);
```

### 3. 请求对象 (`LiteView\Kernel\Visitor`)

封装请求参数、Session 用户管理和 IP 获取。

```php
$visitor = new Visitor();

// 获取 GET / POST / JSON 输入（自动合并）
$name = $visitor->input('name', 'default');
$all = $visitor->input();          // ArrayObject

// 批量取值 / 排除字段
$data = $visitor->only(['name', 'email']);
$data = $visitor->except(['password']);

// 获取 GET / POST 原始数据
$get = $visitor->get('page', 1);
$post = $visitor->post('content');

// 获取客户端 IP
$ip = $visitor->ip();

// 登录 / 登出
$visitor->login(42);
$visitor->logout();
```

### 4. 视图渲染 (`LiteView\Kernel\View`)

支持 Twig 模板和原生 PHP 模板。

```php
// Twig 模板（推荐）
View::setPath('/path/to/views');
return View::renderTwig('index.twig', ['title' => 'Home']);

// 原生 PHP 模板
View::renderFile('page.php', ['data' => $data]);
```

### 5. 异常与错误处理

框架自动捕获异常、Error 和 Fatal Error，根据 `debug` 配置决定输出详细错误页或友好提示。

- `debug: true` — 显示交互式错误页（堆栈跟踪、代码片段高亮、点击展开）
- `debug: false` — 只显示"系统繁忙"，同时记录日志

### 6. API Token (`LiteView\Utils\ApiToken`)

内置无状态 Token 认证，支持签名验证、过期检查和 Guard 隔离。

```php
// 生成 Token（有效期 1 小时）
$token = ApiToken::create(['user_id' => 42], 3600, 'api');

// 验证 Token
$result = ApiToken::auth($token, 'api', $info);
// 返回值：0=成功, 1=空, 2=签名错误, 3=过期, 4=Guard不匹配

// 密码哈希
$hash = ApiToken::passwdMake('password');
$ok = ApiToken::passwdAuth('password', $hash);
```

### 7. 日志系统 (`LiteView\Utils\Log`)

基于 Monolog 的日志通道，支持多通道、自定义 Handler 和 Processor。

```php
// 使用默认通道
Log::info('user_login', ['user_id' => 42]);
Log::error('db_error', ['sql' => $sql]);

// 使用自定义通道
$logger = Log::employ('custom');
$logger->debug('debug_info');
```

### 8. 辅助函数

```php
root_path('storage/logs/app.log');  // 获取相对于项目根目录的绝对路径
cfg('app_url');                      // 读取配置
cfg('database.host', 'localhost'); // 读取嵌套配置，带默认值
domain();                            // 自动推断站点 URL
cors('/api');                        // 根据配置输出 CORS 响应头
```

---

## 配置

在项目根目录创建 `config.json`，框架通过 `cfg('key')` 读取配置。环境配置会从 `config.{app_env}.json` 合并覆盖。

| 配置项 | 使用位置 | 说明 |
|--------|----------|------|
| `debug` | `Dispatcher` | `true` 时显示详细错误信息，`false` 时只显示"系统繁忙" |
| `app_env` | `Dispatcher` | 环境名称，框架启动时加载 `config.{app_env}.json` 并合并到配置 |
| `app_url` | `domain()` | 站点 URL 覆盖，默认根据 `$_SERVER` 自动推断 |
| `app_key` | `ApiToken` | 密码哈希 (passwdMake) 的加密密钥 |
| `api_token_secret` | `ApiToken` | API Token 签名密钥 |
| `location` | `Route`, `Visitor` | URL 路径前缀，用于同一项目部署在不同目录时自动加前缀 |
| `trust_proxy` | `Visitor::ip()` | 是否信任 `X-Forwarded-For` 头获取真实 IP |
| `template_path` | `View` | Twig 模板目录路径（相对于项目根目录），默认 `resources/views/` |
| `cors` | `cors()` | CORS 跨域配置，结构见下方 |
| `logging` | `Log` | Monolog 日志通道配置，结构见下方 |

### CORS 配置

```json
{
    "cors": {
        "paths": ["*"],
        "allow_origins": "*",
        "allow_methods": "POST, GET, OPTIONS",
        "allow_headers": "*"
    }
}
```

- `paths`: 应用 CORS 的路径列表，`["*"]` 表示全部路径
- `allow_origins`: 允许的来源，设置具体域名时会自动启用 `Access-Control-Allow-Credentials`

### 日志配置

```json
{
    "logging": {
        "default": {
            "handlers": [],
            "processors": []
        }
    }
}
```

`handlers` 支持数组或可调用函数返回数组；`processors` 为 Monolog 处理器类名数组。框架内置 `main` 通道写 `storage/logs/main.log`。
