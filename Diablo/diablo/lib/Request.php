<?php
/**
 * 获取请求参数基类
 */

namespace diablo\lib;


class Request
{
    public $get;
    public $post;
    public $param;
    public $server;

    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->param = $_REQUEST;
        $this->server = $this->server();
    }

    private function server()
    {
        return [
            'server_name' => $_SERVER['SERVER_NAME'],
            'route' => $_SERVER['REQUEST_URI'],
            'ip' => $_SERVER['REMOTE_ADDR'],
            'server_ip' => $_SERVER['SERVER_ADDR'],
            'getParam' => $_SERVER['QUERY_STRING']
        ];
    }
}