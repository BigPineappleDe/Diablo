<?php
include dirname(__DIR__) . '/diablo/Bootstrap.php';
//自动加载类
spl_autoload_register('\diablo\Diablo::load');
//跑
\diablo\Diablo::run();