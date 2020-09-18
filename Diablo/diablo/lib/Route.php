<?php
//路由核心基类
namespace diablo\lib;

use diablo\lib\Config;

class Route
{
    public $controller;
    public $action;
    public $front;
    public $after;

    public function __construct()
    {
        //隐藏index.php
        if (DEL_INDEX) {
            //删除index.php
            if (strpos($_SERVER['REQUEST_URI'], '/index.php') !== false) {
                $_SERVER['REQUEST_URI'] = str_replace("/index.php", "", $_SERVER['REQUEST_URI']);
            }
        }
        //判断请求连接是否为空 不为空则删除第一个 /
        if (!empty($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] != '/') {
            $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 1);
        }
        //判断是否携带参数 若携带参数则删除 ？后的数据
        if (strpos($_SERVER['REQUEST_URI'], '?') !== false) {
            $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '?'));
        }
        $route = explode('/', $_SERVER['REQUEST_URI']);
        //若route为空或 / 启用默认路由
        if (empty($_SERVER['REQUEST_URI']) || $_SERVER['REQUEST_URI'] == '/') {
            if (IS_DEFAULT) {
                $default = Config::get('default')['route'];
                $route[0] = $default['controller'];
                $route[1] = $default['action'];
            }
        }
        //开启路由别名
        if (IS_ROUTE_ALIAS) {
            //使用route别名文件
            $controller = Config::route()[$route[0]];
            if (!$controller) {
                //找不到
                //日志
                if (IS_LOGGER) {
                    Logger::setLog('路由别名失败！找不到对应的controller：' . $route[0], 'error');
                }
                //debug
                if (IS_DEBUG) {
                    throw new \Exception('路由别名失败！找不到对应的controller：' . $route[0]);
                }
                //默认
                if (IS_DEFAULT) {
                    $default = Config::get('default')['error'];
                    $route[0] = $default['controller'];
                    $route[1] = $default['action'];
                    $controller = Config::route()[$route[0]];
                }
            }
            //判断是否为数组 是则再取action 否则追加参数
            if (is_array($controller)) {
                $action = $controller[$route[1]];
                if (!$action) {
                    //找不到
                    //日志
                    if (IS_LOGGER) {
                        Logger::setLog('路由别名失败！找不到对应的action：' . $route[1], 'error');
                    }
                    //debug
                    if (IS_DEBUG) {
                        throw new \Exception('路由别名失败！找不到对应的action：' . $route[1]);
                    }
                    //默认
                    if (IS_DEFAULT) {
                        $default = Config::get('default')['error'];
                        $route[0] = $default['controller'];
                        $route[1] = $default['action'];
                        $controller = Config::route()[$route[0]];
                        if($route[1]=='/'||empty($route[1])){
                            $map = $controller;
                        }else{
                            $map = $action;
                        }
                    }
                }else{
                    $map = $action;
                }
            } else {
                $map = $controller;
            }
            if (is_array($map)) {
                if ($map[1]) {
                    $this->front = $map[1];
                } else {
                    $this->front = Config::get('default')['middleware']['front'];
                }
                if ($map[2]) {
                    $this->after = $map[2];
                } else {
                    $this->after = Config::get('default')['middleware']['after'];
                }
                $map = $map[0];
            } else {
                $this->front = Config::get('default')['middleware']['front'];
                $this->after = Config::get('default')['middleware']['after'];
            }
            $map = explode('@', $map);
            $this->controller = $map[0];
            $this->action = $map[1];
        }else{
            $this->controller = $route[0];
            $this->action = $route[1];
        }
    }
}