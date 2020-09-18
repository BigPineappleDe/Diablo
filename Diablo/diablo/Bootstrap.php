<?php
//核心配置文件
define('FOLDER_DIR', dirname(__DIR__) . '/');//主目录
define('APP_DIR', FOLDER_DIR . 'app/');//app目录
define('COMMON_DIR', FOLDER_DIR . 'common/');//common目录
define('CONFIG_DIR', FOLDER_DIR . 'config/');//config目录
define('DIABLO_DIR', FOLDER_DIR . 'diablo/');//框架核心目录
define('PUBLIC_DIR', FOLDER_DIR . 'public/');//public目录
define('ROUTE_DIR', FOLDER_DIR . 'route/');//route目录
define('CACHE_DIR', FOLDER_DIR . 'cache/');//缓存目录
include CONFIG_DIR . 'config.php';//配置文件
if (IS_COMPOSER) {
    include FOLDER_DIR . 'vendor/autoload.php';//引入插件
}
include DIABLO_DIR . 'Diablo.php';//核心文件
include DIABLO_DIR . 'lib/Route.php';//核心路由文件
include COMMON_DIR . 'function.php';//注册公共方法

//debug
if (IS_DEBUG) {//开启
    if (IS_COMPOSER) {
        //错误展示 whoops插件
        $whoops = new \Whoops\Run();
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
        $whoops->register();
    }
    ini_set('display_error', 'on');
} else {//关闭
    ini_set('display_error', 'off');
}

//框架信息
//欢迎信息
function helloWorld(){
    return '<div style="text-align: center;margin-top: 10%"><h1>欢迎进入Diablo框架的世界！</h1><p><a href="https://blog.fenglide.com.cn/blog/content?class=D4A694D3" target="_blank" style="color: #3498db;">官方文档</a> 当前版本：'.version().'</p></div>';
}

//版本
function version()
{
    return 'V2020.09.08A';
}

//重定向
function redirect($url)
{
    header('location:' . \diablo\lib\Config::get('webSetting')['WEB_HOST'] . $url);
}
