<?php


namespace LiteView\Support;


class ToolMan
{
    private static $cfg = null; //配置信息

    public static function getCfg()
    {
        if (is_null(self::$cfg)) {
            $string = file_get_contents(root_path('/config.json'));
            self::$cfg = json_decode($string, true);
        }
        return self::$cfg;
    }

    public static function setCfg($name, $value)
    {
        $cfg = self::getCfg();
        if (isset($cfg[$name])) {
            trigger_error("The name `$name` already exists in the configuration.", E_USER_ERROR);
        }
        self::$cfg[$name] = $value;
    }
}