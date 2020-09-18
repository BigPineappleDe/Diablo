<?php
/**
 * 缓存基类
 */

namespace diablo\lib;

use Predis\Client;
use diablo\lib\Config;

class Cache
{
    public static function redis()
    {
        return new Client(Config::get('redis'));
    }
}