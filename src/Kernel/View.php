<?php


namespace LiteView\Kernel;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
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

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function render(string $view, array $variables = []): string
    {
        return $this->twig->load($view)->render($variables);
    }

    public function renderFile(string $view, array $variables = [])
    {
        ob_start();
        ob_implicit_flush(false);
        extract($variables);
        require $this->path . $view;
        return ob_get_clean();
    }

    public function renderPhp(string $view, array $variables = [])
    {
        extract($variables);
        require $this->path . $view;
    }
}
