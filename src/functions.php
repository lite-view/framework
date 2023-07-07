<?php


//入口文件所在的路径
function root_path($path = '/')
{
    if (defined('WORKING_DIR')) {
        return dirname(WORKING_DIR) . '/' . ltrim($path, '/');
    }
    // Gets the current working directory
    return getcwd() . '/' . ltrim($path, '/');
}


class ToolMan
{
    private static $cfg = null; //配置信息

    public static function getCfg()
    {
        if (!is_null(self::$cfg)) {
            return self::$cfg;
        }
        $string = file_get_contents(root_path('/config.json'));
        self::$cfg = json_decode($string, true);
        return self::$cfg;
    }

    public static function setCfg($name, $value)
    {
        $cfg = self::getCfg();
        if (isset($cfg[$name])) {
            trigger_error("the name `$name` already exists in the configuration.", E_USER_ERROR);
        }
        self::$cfg[$name] = $value;
    }
}


function cfg($key = null, $default = null)
{
    $data = ToolMan::getCfg();
    if (is_null($key)) {
        return $data;
    }
    $need = explode('.', $key);
    foreach ($need as $field) {
        $data = $data[$field] ?? null;
    }
    return $data ?? $default;
}


function lite_view($view, $variables = [], $layout = null)
{
    $v = new LiteView\Kernel\View();
    if (is_null($layout)) {
        $v->renderPartial($view, $variables);
    } else {
        $v->render($view, $layout, $variables);
    }
}


function domain()
{
    $scheme = 'http';
    if (!empty($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS']) {
        $scheme = 'https';
    }
    return cfg('app_url', "$scheme://$_SERVER[HTTP_HOST]");
}


function debug()
{
    $args = func_get_args();
    foreach ($args as $one) {
        var_dump($one);
    }
    exit();
}


function cors($path)
{
    $cors = cfg('cors');
    if (empty($cors)) {
        return;
    }
    if ('*' === $cors['paths'][0]) {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: *");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
        header("Access-Control-Expose-Headers: *");
        return;
    }
    if (in_array($path, $cors['paths'])) {
        //
    }
}




