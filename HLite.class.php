<?php

class HLite
{
    static $getViewCache = [];

    public static function applyReplaceArray($replaceArray, $text) {
        foreach ($replaceArray as $__template_key => $__template_value) {
            $text = str_replace($__template_key, $__template_value, $text);
        }

        return $text;
    }

    public static function getDomain() {
        if (!isset($_SERVER['HTTP_HOST'])) {
            $parsed = (parse_url(BASE_URL));
            $_SERVER['HTTP_HOST'] = $parsed['host'];
        }

        if (isset($_SERVER['HTTP_HOST'])) {
            $exp = explode(".", $_SERVER['HTTP_HOST']);
            if (strtolower($_SERVER['HTTP_HOST']) == "workspace.pixelq.nl" || (isset($exp[0]) && $exp[0] == "beta" || isset($exp[0]) && sizeof($exp) == 3 && $exp[0] !== "www")) {
                return strtolower($_SERVER['HTTP_HOST']);
            }

            $host = $_SERVER['HTTP_HOST'];
            preg_match("/[^\.\/]+\.[^\.\/]+$/", $host, $matches);

            if (isset($matches[0])) {
                return $matches[0];
            }
            else {
                return "Unknown!";
            }
        }
        else {
            return "Vreemd";
        }
    }

    public static function curl($url, $headers = false, $extraOptions = [], $postFields = []) {

        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_COOKIEJAR      => '/tmp/cookie.txt',
            CURLOPT_COOKIEFILE     => '/tmp/cookie.txt',
            CURLOPT_CONNECTTIMEOUT => 120,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_USERAGENT      => "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.154 Safari/537.36",
            CURLOPT_URL            => $url,
        ];
        $ch = curl_init();
        curl_setopt_array($ch, $options);

        foreach ($extraOptions as $option => $value) {
            curl_setopt($ch, $option, $value);
        }

        if ($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if (sizeof($postFields)) {
            $fields_string = "";
            foreach ($postFields as $key => $value) {
                $fields_string .= $key . '=' . urlencode($value) . '&';
            }
            rtrim($fields_string, '&');
            curl_setopt($ch, CURLOPT_POST, count($postFields));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            D::writeDebug($fields_string);
        }


        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

    /**
     * @param      $bytes
     * @param bool $alsoUnit
     *
     * @param bool $forceMegabytes
     * @return int
     * @author Sebastiaan Pasma
     */
    public static function formatBytes($bytes, $alsoUnit = false, $forceMegabytes = true) {
        if ($bytes >= 1073741824 && $forceMegabytes == false) {
            $bytes = number_format($bytes / 1073741824, 2) . ($alsoUnit ? ' GB' : "");
        }
        elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ($alsoUnit ? ' MB' : "");
        }
        else {
            $bytes = number_format($bytes / 1048576, 2) . ($alsoUnit ? ' MB' : "");
        }

        if (!$alsoUnit)
            return doubleval($bytes);

        return $bytes;
    }

    /**
     * @param $bytes
     * @param int $precision
     * @return string
     * @author Sebastiaan Pasma
     * @since 2017-01-18
     * from: http://stackoverflow.com/questions/2510434/format-bytes-to-kilobytes-megabytes-gigabytes
     */
    public static function formatBytes2($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
         $bytes /= pow(1024, $pow);
        // $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public static function getIp() {
        $ip = false;
        if (isset($_SERVER)) {

            if (isset($_SERVER['SERVER_ADDR']))
                $ip = $_SERVER['SERVER_ADDR'];

            if (isset($_SERVER["HTTP_X_ORIGINAL_TO"])) {
                $ip = $_SERVER["HTTP_X_ORIGINAL_TO"];
            }
        }
        if ($ip == "127.0.0.1") {
            $ip = gethostbyname(gethostbyname(gethostname()));
        }

        return $ip;
    }

    public static function is_dir($dir) {
        return ((fileperms("$dir") & 0x4000) == 0x4000);
    }
}