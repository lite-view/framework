<?php


namespace LiteView\Kernel;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;


class View
{
    protected $path;
    protected $twig;

    public function __construct()
    {
        $this->path = root_path(cfg('template_path', 'resources/views/'));
        $this->twig = new Environment(new FilesystemLoader($this->path));
    }

    public function renderFile(string $view, array $variables = [])
    {
        ob_start();
        //ob_start 是 PHP 中的一个输出缓冲函数。它开启了一个输出缓冲区，用于存储由 PHP 脚本产生的输出内容(echo、print、var_dump...)，而不是直接将这些内容发送到浏览器或者其他输出设备。
        //ob_get_clean 函数获取并清空缓冲区

        ob_implicit_flush(false);
        extract($variables);
        require $this->path . $view;
        return ob_get_clean();
    }

    public function render(string $view, array $variables = [])
    {
        echo $this->twig->load($view)->render($variables);
    }

    public function renderPhp(string $view, array $variables = [])
    {
        extract($variables);
        require $this->path . $view;
    }
}
