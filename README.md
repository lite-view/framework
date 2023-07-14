# lite-view

## 介绍
PHP mini vc 框架

## 项目状态
项目版本**遵循** [语义化版本号](https://semver.org/lang/zh-CN/)

## 安装
`composer require lite-view/framework`

## 本地调试
`php -S 127.0.0.1:8000`

## 目录结构

```
├── Kernel
│       ├── Route.php         # 路由管理
│       ├── View.php          # 路由管理
│       └── Visitor.php       # 请求管理
├── Aides
│       └── Log.php           # log
├── environment.php           # 框架环境初始化
└── functions.php             # 框架提供的通用函数
 ```
