<?php


namespace LiteView\Kernel;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;


class View
{
    private static $twigs = [];
    private static $path = '';
    private static $visitor;

    private static function getTwigEnvironment(): Environment
    {
        $path = self::getPath();
        $key  = md5($path);
        if (!isset(self::$twigs[$key])) {
            self::$twigs[$key] = new Environment(new FilesystemLoader($path));
        }
        return self::$twigs[$key];
    }

    public static function setPath($path)
    {
        self::$path = $path;
    }

    public static function setVisitor(Visitor $visitor)
    {
        self::$visitor = $visitor;
    }

    public static function getPath(): string
    {
        if (self::$path) {
            return self::$path;
        }
        return root_path(cfg('template_path', 'resources/views/'));
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public static function renderTwig(string $name, array $variables = []): string
    {
        if (!isset($variables['visitor'])) {
            $variables['visitor'] = self::$visitor;
        }
        return self::getTwigEnvironment()->load($name)->render($variables);
    }

    public static function renderFile(string $name, array $variables = [])
    {
        if (substr_compare($name, '.php', -4) === 0) {
            if (!isset($variables['visitor'])) {
                $variables['visitor'] = self::$visitor;
            }
            ob_start();
            ob_implicit_flush(false);
            extract($variables);
            require self::getPath() . $name;
            return ob_get_clean();
        } else {
            return file_get_contents(self::getPath() . $name);
        }
    }

    public static function render(string $name, array $variables = [])
    {
        if (substr_compare($name, '.php', -4) === 0) {
            if (!isset($variables['visitor'])) {
                $variables['visitor'] = self::$visitor;
            }
            extract($variables);
            require self::getPath() . $name;
        } else {
            echo file_get_contents(self::getPath() . $name);
        }
    }
}
