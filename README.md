# lite-view/framework

[应用实例参考](https://github.com/lite-view/lite-view)

## 介绍

PHP mini vc 框架

## 框架的初衷和意义

- **学习价值** — 从零构建框架是理解 PHP 请求生命周期、路由、中间件管道的最佳方式，比读大型框架源码更直观
- **个人/团队定制** — 没有不需要的抽象，改起来零负担，自己的项目用着最顺手
- **可控性** — 依赖只有 Twig + Monolog，没有黑盒，出问题能从根上排查
- **够用就好** — 很多 PHP 项目就是 CRUD + 几个接口，不需要重型框架，轻量即是优势

## 项目状态

项目版本**遵循** [语义化版本号](https://semver.org/lang/zh-CN/)

## 安装

`composer require lite-view/framework`

## 本地调试

`php -S 127.0.0.1:8000`

### php内置服务器运行模式

- 路由模式：使用 php -S 127.0.0.1:888 index.php 命令运行 PHP 内置服务器时，所有请求都路由到 index.php 文件
    - 如果匹配到存在的目录 PATH_INFO 不存在
    - 如果uri中带文件后缀 PATH_INFO 不存在
- 标准模式：不指定路由脚本，使用 php -S 127.0.0.1:888（没有 index.php）
    - 首先尝试直接提供请求的文件（即在路径中带后缀名），如果不存在则返回404，且不会进入index.php
    - 如果请求的是目录，则查找目录中的 index.php 或 index.html

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