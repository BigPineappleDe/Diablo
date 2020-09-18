<?php
namespace app;

use diablo\diablo;
use diablo\lib\model;

class Controller extends diablo
{
    public $model;
    public function __construct()
    {
        $this->model=new model();
    }
}