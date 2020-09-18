<?php
namespace app\controller;

use app\Controller;
use app\model\UserModel;

class IndexController extends Controller
{
    public $userModel;
    public function __construct()
    {
        $this->userModel=new UserModel();
    }

    public function open(){

        echo helloWorld();

        $this->display('index.html');
    }
    public function error(){
        dump('error');
    }
}