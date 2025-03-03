# lite-view/framework

[应用实例参考](https://github.com/lite-view/lite-view)

## 介绍

PHP mini vc 框架

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

