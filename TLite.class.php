<?php

class TLite
{
    public static function createMySQLTime($time = null) {
        if ($time == null)
            $time = time();

        return date("Y-m-d H:i:s", $time);
    }

    public static function stripNonLetters($str, $additional = "") {
        return preg_replace("/[^A-Za-z0-9" . $additional . "]/", '', $str);
    }

    public static function stripNonAlphaCharacters($str, $evenSpace = false) {
        return preg_replace("/[^A-Za-z0-9" . ($evenSpace ? '' : ' ') . "]/", '', $str);
    }

    public static function stripLetters($str) {
        return preg_replace("/[^0-9.]/", '', $str);
    }

    public static function stripLettersAndNumbers($str) {
        return preg_replace("/[0-9a-zA-Z]/", '', $str);
    }

    public static function toCleanUrl($str, $replace = [], $delimiter = '-', $ignoredChars = '') {
        setlocale(LC_CTYPE, 'en_US.UTF8');
        $str = trim($str);
        if (!empty($replace)) {
            $str = str_replace((array)$replace, ' ', $str);
        }
        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
        $clean = preg_replace("/[^a-zA-Z0-9" . $ignoredChars . "\/_|+ -]/", '', $clean);
        $clean = strtolower(trim($clean, '-'));
        $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

        return $clean;
    }

    // T::addDeprecationNotification(__FUNCTION__, __CLASS__, __FILE__, __LINE__);
    public static function addDeprecationNotification($function, $class, $file = "", $line = 0, $extraMessage = null) {
        $trace = "";
        $backtrace = array_reverse(debug_backtrace());
        $streep = str_repeat("=", 50) . "\n";
        $i = 1;
        foreach ($backtrace as $node) {
            $trace .= "$i. " . basename($node['file']) . ":" . $node['function'] . "(" . $node['line'] . ")" . PHP_EOL;
            $i++;
        }
        $append = $streep . "Deprecated feature " . ($extraMessage ? $extraMessage : "") . " used in '" . SITE_NAME . "': " . $function . " (" . $file . ")" . " (" . $class . ":" . $line . ") in" . PHP_EOL . $trace . $streep . PHP_EOL . PHP_EOL;
        $shaAppend = sha1($append) . ".txt";

        if (!is_dir(BASE_PATH . "/_cache/notifCache")) {
            mkdir(BASE_PATH . "/_cache/notifCache");
        }
        if (!file_exists(BASE_PATH . "/_cache/notifCache/" . $shaAppend) && is_dir(BASE_PATH . "/_cache/notifCache")) {
            if (!file_exists(BASE_PATH . "/_cache/notifCache/" . $shaAppend)) {
                file_put_contents(BASE_PATH . "/_cache/notifCache/" . $shaAppend, $append);
            }
        }
    }

    /**
     * @deprecated SEB: 21-september-2016: functienaam bevat underscore
     */
    public static function multidimensional_search($parents, $searched) {
        T::addDeprecationNotification(__FUNCTION__, __CLASS__, __FILE__, __LINE__);

        return static::multidimensionalSearch($parents, $searched);
    }

    /**
     * @param $parents
     * @param $searched
     *
     * @return bool|int|string
     * @author Sebastiaan Pasma
     * @example
     * $parents = array();
     * $parents[] = array('date'=>1320883200, 'uid'=>3);
     * $parents[] = array('date'=>1320883200, 'uid'=>5);
     * $parents[] = array('date'=>1318204800, 'uid'=>5);
     *
     * echo multidimensional_search($parents, array('date'=>1320883200, 'uid'=>5)); // 1
     */
    public static function multidimensionalSearch($parents, $searched) {
        if (empty($searched) || empty($parents)) {
            return false;
        }

        foreach ($parents as $key => $value) {
            $exists = true;
            foreach ($searched as $skey => $svalue) {
                $exists = ($exists && IsSet($parents[$key][$skey]) && $parents[$key][$skey] == $svalue);
            }
            if ($exists) {
                return $key;
            }
        }

        return false;
    }

    public static function dashesToCamelCase($string, $capitalizeFirstCharacter = false) {
        return self::toCamelCase($string, $capitalizeFirstCharacter, '-');
    }

    public static function toCamelCase($string, $capitalizeFirstCharacter = false, $character = "_") {
        $str = str_replace(' ', '', ucwords(str_replace($character, ' ', $string)));
        if (!$capitalizeFirstCharacter) {
            $str[0] = strtolower($str[0]);
        }

        return $str;
    }

    public static function toOnlyFirstLetters($string) {
        $str = T::stripNonAlphaCharacters($string);
        $ret = "";
        $str = str_replace("  ", " ", $str);
        $str = str_replace("  ", " ", $str);
        foreach (explode(" ", $str) as $word) {
            $ret .= substr($word, 0, 1);
        }

        return $ret;
    }

    public static function getIpAddress() {
        foreach ([
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ] as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (array_map('trim', explode(',', $_SERVER[$key])) as $ip) {
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }

        return 0;
    }

    /**
     * @link http://gist.github.com/385876
     */
    public static function csvToArray($filename = '', $delimiter = ';') {
        if (!file_exists($filename) || !is_readable($filename))
            return false;

        $header = null;
        $data = [];
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
                if (!$header) {
                    $header = $row;
                    foreach ($header as $index => $item) {
                        $header[$index] = trim(strtolower($item));
                    }
                }
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }

        return $data;
    }
}