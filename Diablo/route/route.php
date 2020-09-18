<?php
/**
 * 注册路由
 */
return [
    'open' => 'IndexController@open',
    'index' => [
        'index' => 'IndexController@open',
        'min' => ['IndexController@open', 'FrontMiddleware', 'AfterMiddleware'],
        'open' => 'index/IndexController@indexOpen',
    ],
    'error' => 'IndexController@error'
];