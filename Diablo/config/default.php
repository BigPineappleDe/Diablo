<?php
//默认配置
return [
    'route'=>[
        'controller'=>'index',
        'action'=>'index'
    ],
    'error'=>[
        'controller'=>'error',
        'action'=>'/',
    ],
    'middleware'=>[
        'front'=>'FrontMiddleware',//前
        'after'=>'AfterMiddleware',//后
    ]
];