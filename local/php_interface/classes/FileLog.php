<?php

class FileLog
{
    private static $path = '/local/php_interface/logs/';

    public static function add($message, $data = [], $json = true, $path = '')
    {
        if (!strlen($path)) {
            $path = self::$path;
        }

        $pathLog = $_SERVER['DOCUMENT_ROOT'] . $path . date('Ym') . '/';
        if (!file_exists($pathLog)) {
            mkdir($pathLog);
        }

        $log = date('Y-m-d H:i:s') . ' '
            . $message
            . (!empty($data) ? ": " . ($json ? json_encode($data, JSON_UNESCAPED_UNICODE) : var_export($data, true)) : '');

        file_put_contents($pathLog . date('Y-m-d') . '.log', $log . "\r\n", FILE_APPEND);
    }
}