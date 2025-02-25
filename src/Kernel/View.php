<?php


namespace LiteView\Kernel;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;


class View
{
    private $path;
    private $twig;
    private static $instances = [];

    private function __construct($path)
    {
        $this->path = $path;
        $this->twig = new Environment(new FilesystemLoader($this->path));
    }

    public static function load($path = ''): View
    {
        $key = md5($path);
        if (!isset(self::$instances[$key])) {
            if ('' === $path) {
                $path = root_path(cfg('template_path', 'resources/views/'));
            }
            self::$instances[$key] = new self($path);
        }
        return self::$instances[$key];
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function renderTwig(string $name, array $variables = []): string
    {
        return $this->twig->load($name)->render($variables);
    }

    public function renderFile(string $name, array $variables = [])
    {
        ob_start();
        ob_implicit_flush(false);
        extract($variables);
        require $this->path . $name;
        return ob_get_clean();
    }

    public function render(string $name, array $variables = [])
    {
        extract($variables);
        require $this->path . $name;
    }
}
