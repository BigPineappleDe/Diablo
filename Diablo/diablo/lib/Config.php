<?php
//获取配置文件
namespace diablo\lib;

class Config
{
    static public $config=[];

    //获取配置文件
    static public function get($fileName)
    {
        if (isset(self::$config[$fileName])) {
            return self::$config[$fileName];
        }else{
            $file = CONFIG_DIR . $fileName . '.php';
            if (file_exists($file)) {
                self::$config[$fileName]=include $file;
                return self::$config[$fileName];
            } else {
                if (IS_DEBUG) {
                    if (IS_LOGGER){
                        Logger::setLog('找不到配置文件：' . $file,'error');
                    }
                    throw new \Exception('找不到配置文件：' . $file);
                }
            }
        }
    }

    //获取路由别名文件
    static public function route($fileName='route')
    {
        if (isset(self::$config[$fileName])) {
            return self::$config[$fileName];
        }else{
            $file = ROUTE_DIR . $fileName . '.php';
            if (file_exists($file)) {
                self::$config[$fileName]=include $file;
                return self::$config[$fileName];
            } else {
                if (IS_DEBUG) {
                    if (IS_LOGGER){
                        Logger::setLog('找不到路由文件：' . $file,'error');
                    }
                    throw new \Exception('找不到路由文件：' . $file);
                }
            }
        }
    }
}