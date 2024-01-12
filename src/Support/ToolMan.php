<?php


namespace LiteView\Support;


class ToolMan
{
    private static $cfg = null; //配置信息

    public static function getCfg()
    {
        if (is_null(self::$cfg)) {
            if (!file_exists(root_path('/config.json'))) {
                exit('缺少配置文件！请在项目根目录添加配置文件 config.json');
            }
            self::$cfg = json_decode(file_get_contents(root_path('/config.json')), true);
            if (!is_array(self::$cfg)) {
                exit('配置文件解析失败(' . root_path('/config.json') . ')！请检查json格式是否有误');
            }
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