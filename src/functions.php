<?php


function root_path($path = '/')
{
    if (defined('WORKING_DIR')) {
        return dirname(WORKING_DIR) . '/' . ltrim($path, '/');
    }
    // Gets the current working directory
    return getcwd() . '/' . ltrim($path, '/');
}


function cfg($key = null, $default = null)
{
    $data = LiteView\Support\ToolMan::getCfg();
    if (is_null($key)) {
        return $data;
    }
    $need = explode('.', $key);
    foreach ($need as $field) {
        $data = $data[$field] ?? null;
    }
    return $data ?? $default;
}


function lite_view($view, $variables = [])
{
    $v = new LiteView\Kernel\View();
    return $v->render($view, $variables);
}


function domain()
{
    $scheme = 'http';
    if (!empty($_SERVER['HTTPS']) && 'on' === $_SERVER['HTTPS']) {
        $scheme = 'https';
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO']) {
        $scheme = 'https';
    }
    return cfg('app_url', "$scheme://$_SERVER[HTTP_HOST]");
}


function dump()
{
    $args = func_get_args();
    foreach ($args as $one) {
        var_dump($one);
    }
    exit();
}


function print32($num)
{
    /*
    & 对应位都为1时结果为1，否则为0
    | 对应位有一个为1时结果为1，否则为0
    ^ 对应位相异时结果为1，否则为0
    ~ 对每个位取反（0 变为1，1 变为0）
    << 将二进制数向左移动指定的位数
    >> 将二进制数向右移动指定的位数
     * */
    for ($i = 31; $i >= 0; $i--) {
        echo ($num & (1 << $i)) === 0 ? '0' : '1';
    }
    echo PHP_EOL;
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




