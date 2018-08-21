<?php
/**
 * Created by PhpStorm.
 * User: focus
 * Date: 20.07.18
 * Time: 15:08
 */

namespace Lum\Wybor;


class Utils
{
    public static function csvFileToArray ($path)
    {
        $output = [];
        if (($handle = fopen($path, "r")) !== FALSE) {
            ini_set("memory_limit", -1);
            while (($data = fgetcsv($handle, PHP_INT_MAX/1024/1024/1024, ";")) !== FALSE) {
                $output[] = $data;
            }
            fclose($handle);
        }
        return $output;
    }

    public static function dumpArrayToCsvFile ($outputArray, $path) {
        $fp = fopen($path, 'w');

        foreach ($outputArray as $outputItem) {
            fputcsv($fp, $outputItem, ';', '"');
        }

        fclose($fp);
    }

    public static function checkUrlForCode200 ($url)
    {
        return get_headers($url, 1)[0] === 'HTTP/1.1 200 OK';
    }
}