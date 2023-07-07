<?php


namespace LiteView\Kernel;

ob_start();

class View
{
    public $path;

    public function __construct()
    {
        $this->path = root_path('resources/views/');
    }

    /**
     * 渲染php文件，并返回渲染后的内容
     * @param string $view
     * @param array $variables
     * @return false|string
     */
    private function renderFile(string $view, array $variables = [])
    {
        ob_start();
        ob_implicit_flush(false);
        extract($variables);
        require $this->path . $view;
        return ob_get_clean();
    }

    //视图渲染，带布局文件
    public function render($view, $layout, $variables = [])
    {
        extract($variables);
        $content = $this->renderFile($view, $variables);
        require $this->path . $layout;
    }

    //视图渲染，不带布局文件
    public function renderPartial($view, $variables = [])
    {
        extract($variables);
        require $this->path . $view;
    }
}
