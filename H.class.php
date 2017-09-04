<?php

if (!defined("LIBSDIR")) {
    define("LIBSDIR", dirname(__FILE__) . "/../");
}

/**
 * H-Class (Hoofd Class)
 * Regelt alle functies met de Views
 */
class H extends HLite
{
    public static function putCache($file, $content) {
        return file_put_contents($file, $content);
    }

    public static function getCachedDataByKeyFile($key, $prefix = 'fcache') {
        if (!is_dir(BASE_PATH . "_cache/" . $prefix))
            mkdir(BASE_PATH . "_cache/" . $prefix);

        $file = BASE_PATH . "_cache/" . $prefix . "/" . ($key) . ".cache";
        if (!file_exists($file)) {
            return false;
        }

        return $file;
    }

    public static function getCachedDataByKey($key, $cacheTime = 300, $prefix = 'fcache') {
        if (!is_dir(BASE_PATH . "_cache/" . $prefix))
            mkdir(BASE_PATH . "_cache/" . $prefix);

        $file = BASE_PATH . "_cache/" . $prefix . "/" . ($key) . ".cache";
        if (!file_exists($file) || filemtime($file) < time() - $cacheTime) {
            return false;
        }

        return file_get_contents($file);
    }

    public static function setCachedDataByKey($key, $data, $prefix = 'fcache') {
        if (!is_dir(BASE_PATH . "_cache/" . $prefix))
            mkdir(BASE_PATH . "_cache/" . $prefix);
        $file = BASE_PATH . "_cache/" . $prefix . "/" . ($key) . ".cache";
        static::putCache($file, $data);

        return $data;
    }

    public static function getCachedContentFilePath($URL, $cacheTime = 300, $prefix = 'fcache') {
        $file = BASE_PATH . "_cache/" . $prefix . "-" . md5($URL) . ".cache";
        if (file_exists($file))
            return $file;
        return false;
    }

    public static function getCachedContent($URL, $cacheTime = 300, $prefix = 'fcache') {
        $file = BASE_PATH . "_cache/" . $prefix . "-" . md5($URL) . ".cache";
        if (!file_exists($file) || filemtime($file) < time() - $cacheTime) {
            static::putCache($file, H::curl($URL));
        }

        return file_get_contents($file);
    }

    public static function getCachedJSONUrl($URL, $cacheTime = 300, $prefix = 'fcache', $onlySaveValidJSON = true) {
        $file = BASE_PATH . "_cache/" . $prefix . "-" . md5($URL) . ".cache";
        if (!file_exists($file) || filemtime($file) < time() - $cacheTime) {
            $json = H::curl($URL);
            if ((json_decode($json) && $onlySaveValidJSON) || !$onlySaveValidJSON) {
                static::putCache($file, $json);
            }
        }

        return json_decode(file_get_contents($file), true);
    }

    /**
     * @param $url
     * @param $saveTo
     * @param $headers
     * @param $extraOptions
     *
     * @return bool
     * @author Sebastiaan Pasma
     */
    public static function curl_to_file($url, $saveTo, $headers = [], $extraOptions = []) {
        if (file_exists($saveTo)) {
            unlink($saveTo);
        }
        $data = static::curl($url, $headers, $extraOptions);
        if ($data) {
            $fp = fopen($saveTo, 'w');
            fwrite($fp, $data);
            fclose($fp);
        }

        return file_exists($saveTo);
    }
}
