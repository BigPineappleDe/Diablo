<?php
//前置中间件
namespace app\middleware;

use diablo\lib\Request;

class FrontMiddleware
{
    public function handle(Request $request)
    {
        //dump('前置中间件');
    }
}