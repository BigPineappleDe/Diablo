<?php
/**
 * diablo基类
 */

namespace diablo;


use diablo\lib\Config;
use diablo\lib\Logger;
use diablo\lib\Middleware;
use diablo\lib\Request;
use diablo\lib\Route;

class Diablo
{
    public static $classMap = [];
    public $assign;
    public static $fileRoute;

    //执行方法
    static public function run()
    {
        $route = new Route();
        $controller = $route->controller;//类
        $action = $route->action;//方法
        $front = $route->front;//前置中间件
        $after = $route->after;//后置中间件
        $middleware = new Middleware();
        $queue = [];
        try {
            //前置中间件
            if ($front) {
                //注册前置中间件
                $queue[] = $middleware::setMiddleware($front);
            }
            $queue[] = self::business($controller, $action);
            //后置中间件
            if ($after) {
                $queue[] = $middleware::setMiddleware($after);
            }
            Middleware::dispatch($queue);
        } catch (\Exception $e) {
            //日志
            if (IS_LOGGER) {
                Logger::setLog('业务代码执行异常：' . $e->getMessage(), 'error');
            }
            //debug
            if (IS_DEBUG) {
                throw new \Exception('业务代码执行异常：' . $e->getMessage());
            }
            if (IS_DEFAULT) {
                $default = Config::get('default');
                return redirect($default['error']['controller'] . '/' . $default['error']['action']);
            }
        }
    }

    //业务执行代码
    static public function business($controller, $action)
    {
        if (strpos($controller, '/') !== false) {
            $controller = explode('/', $controller);
            $file = APP_DIR . $controller[0] . '/controller/' . $controller[1] . '.php';
            $controller = '\app\\' . $controller[0] . '\controller\\' . $controller[1];
            self::$fileRoute = APP_DIR . $controller[0] . '/';
        } else {
            $file = APP_DIR . 'controller/' . $controller . '.php';
            $controller = '\app\controller\\' . $controller;
            self::$fileRoute = APP_DIR;
        }
        //判断文件是否存在
        if (is_file($file)) {
            return [$controller, $action];
        } else {
            //日志
            if (IS_LOGGER) {
                Logger::setLog('找不到控制器文件：' . $file, 'error');
            }
            //debug
            if (IS_DEBUG) {
                throw new \Exception('找不到控制器文件：' . $file);
            }
            if (IS_DEFAULT) {
                $default = Config::get('default');
                return redirect($default['error']['controller'] . '/' . $default['error']['action']);
            }
        }
    }

    //自动加载类库
    static public function load($class)
    {
        //判断类是否存在 避免重复加载
        if (isset($classMap[$class])) {
            return true;
        } else {
            $class = str_replace('\\', '/', $class);
            $file = FOLDER_DIR . $class . '.php';
            //判断文件是否存在
            if (is_file($file)) {
                include $file;
                self::$classMap[$class] = $class;
            } else {
                return false;
            }
        }
    }

    //视图
    public function assign($name, $value)
    {
        $this->assign[$name] = $value;
    }

    public function display($template)
    {
        $arr = explode('.', $template);
        if (count($arr) < 2) {
            $template .= '.html';
        }
        $file = self::$fileRoute . 'views/' . $template;
        if (is_file($file)) {
            //extract($this->assign);
            //include $file;
            $loader = new \Twig\Loader\FilesystemLoader(self::$fileRoute . 'views');
            $twig = new \Twig\Environment($loader, array(
                'cache' => CACHE_DIR . '/views',
                'debug' => IS_DEBUG,
                'auto_reload' => true,
            ));
            $template = $twig->load($template);
            $template->display($this->assign ? $this->assign : []);
        } else {
            //日志
            if (IS_LOGGER) {
                Logger::setLog('找不到模版文件：' . $file, 'error');
            }
            //debug
            if (IS_DEBUG) {
                throw new \Exception('找不到模版文件：' . $file);
            }
            if (IS_DEFAULT) {
                $default = Config::get('default');
                return redirect($default['error']['controller'] . '/' . $default['error']['action']);
            }
        }
    }
}