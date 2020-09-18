<?php
//后置中间件
namespace app\middleware;

use diablo\lib\Request;

class AfterMiddleware
{
    public function handle(Request $request)
    {
        //dump("后置中间件");
    }
}