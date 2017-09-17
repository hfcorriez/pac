<?php
/**
 * Created by PhpStorm.
 * User: hfcorriez
 * Date: 2017/9/17
 * Time: 下午10:20
 */

namespace Pac;


class FileSystem
{
    public static function copy($source, $dest)
    {
        if (!is_dir($dest)) {
            @mkdir($dest, 0755, true);
        }

        if (is_dir($source)) {
            $dir_handle = opendir($source);
            while ($file = readdir($dir_handle)) {
                if ($file != "." && $file != "..") {
                    if (is_dir($source . "/" . $file)) {
                        if (!is_dir($dest . "/" . $file)) {
                            mkdir($dest . "/" . $file);
                        }
                        self::copy($source . "/" . $file, $dest . "/" . $file);
                    } else {
                        copy($source . "/" . $file, $dest . "/" . $file);
                    }
                }
            }
            closedir($dir_handle);
        } else {
            copy($source, $dest);
        }
    }
}
