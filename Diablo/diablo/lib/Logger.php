<?php
/**
 * 日志系统
 */

namespace diablo\lib;
class Logger
{
    //存入logger
    static public function setLog($msg, $file = 'log', $drive = 'txt')
    {
        $fileName=$file;
        $file_dir = CACHE_DIR . $file . '/' . date('Y') . '/' . date('m') . '/';
        if (!is_dir($file_dir)) {
            mkdir($file_dir, 0777, true);
        }
        if ($drive == 'txt') {
            $file = $file_dir . date('d') . '.log';
            $msg = '[ ' . date('Y-m-d H:i:s') . ' IP:'.$_SERVER['REMOTE_ADDR'].' ] ' . $fileName . ':' . $msg . PHP_EOL;
            file_put_contents($file, $msg, FILE_APPEND);
        }
    }
}