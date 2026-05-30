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
        if (!is_array($data) || !array_key_exists($field, $data)) {
            return $default;
        }
        $data = $data[$field];
    }
    return $data ?? $default;
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
    $origin  = $cors['allow_origins'] ?? '*';
    $methods = $cors['allow_methods'] ?? 'POST, GET, OPTIONS';
    $headers = $cors['allow_headers'] ?? '*';

    if ('*' === ($cors['paths'][0] ?? '')) {
        $applies = true;
    } else {
        $applies = in_array($path, $cors['paths']);
    }

    if ($applies) {
        if ($origin === '*') {
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Methods: $methods");
            header("Access-Control-Allow-Headers: $headers");
            header("Access-Control-Expose-Headers: *");
        } else {
            header("Access-Control-Allow-Origin: $origin");
            header("Access-Control-Allow-Credentials: true");
            header("Access-Control-Allow-Methods: $methods");
            header("Access-Control-Allow-Headers: $headers");
            header("Access-Control-Expose-Headers: *");
        }
    }
}




