<?php
/**
 * 中间件基类
 */

namespace diablo\lib;


class Middleware
{
    public static $queue;  //中间件队列 包含前置 控制器 后置
    //注册中间件
    public static function setMiddleware($middleware)
    {
        $middlewareDir = APP_DIR . 'middleware/' . $middleware . '.php';
        if (is_file($middlewareDir)) {
            $middleware = '\app\middleware\\' . $middleware;
            return [$middleware, 'handle'];
        } else {
            self::errLog($middleware);
        }
    }

    //调用中间件
    public static function dispatch($queues)
    {
        self::$queue = $queues;
        self::run();
    }

    //执行
    public static function run()
    {
        foreach (self::$queue as $key=>$vo){
            $ctr=new $vo[0];
            $action=$vo[1];
            if (method_exists($ctr, $action)) {//已定义方法
                $ctr->$action(new Request());
            } else {
                self::errLog($action);
            }

        }
    }

    private static function errLog($e){
        //日志
        if (IS_LOGGER) {
            Logger::setLog('找不到文件：' . $e, 'error');
        }
        //debug
        if (IS_DEBUG) {
            throw new \Exception('找不到文件：' . $e);
        }
        if (IS_DEFAULT) {
            $default=Config::get('default');
            return redirect($default['error']['controller'].'/'.$default['error']['action']);
        }
    }
}