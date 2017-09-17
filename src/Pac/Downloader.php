<?php
/**
 * Created by PhpStorm.
 * User: hfcorriez
 * Date: 2017/9/17
 * Time: 下午10:01
 */

namespace Pac;


class Downloader
{
    public static function download($url, $path)
    {
        $channel = curl_init($url);
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $file = fopen($path, "w");
        curl_setopt($channel, CURLOPT_FILE, $file);
        curl_setopt($channel, CURLOPT_HEADER, 0);
        curl_setopt($channel, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($channel, CURLOPT_SSL_VERIFYHOST, false);
        curl_exec($channel);
        curl_close($channel);
        fclose($file);
        return file_exists($path);
    }
}
