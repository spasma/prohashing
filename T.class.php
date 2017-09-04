<?php

Class T extends TLite
{
    public static $dayNames = [
        "nl" => [
            "Maandag",
            "Dinsdag",
            "Woensdag",
            "Donderdag",
            "Vrijdag",
            "Zaterdag",
            "Zondag"
        ],
        "en" => [
            "Monday",
            "Tuesday",
            "Wednesday",
            "Thursday",
            "Friday",
            "Saturday",
            "Sunday"
        ]
    ];

    public static $monthNames = [
        "nl" => [
            'Januari',
            'Februari',
            'Maart',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Augustus',
            'September',
            'Oktober',
            'November',
            'December'
        ]
    ];
    public static $monthNamesShort = [
        "nl" => [
            'Jan',
            'Feb',
            'Maa',
            'Apr',
            'Mei',
            'Jun',
            'Jul',
            'Aug',
            'Sep',
            'Okt',
            'Nov',
            'Dec'
        ]
    ];

    /**
     * Een zin hard afkappen..
     *
     * @param string $text Input text
     * @param integer $limit Limiet aan letters (Standaard 25)
     * @author Sebastiaan Pasma
     * @return string
     */
    public static function trunc($text, $limit = 25) {
        $origTxt = $text;
        $text = $text . " ";
        $text = substr($text, 0, $limit);
        $text .= ((strlen($origTxt) > $limit) ? ".." : "");

        return trim($text);
    }

    // equiv to rand, mt_rand
    // returns int in *closed* interval [$min,$max]
    //
    public static function dev_urandom_rand($min = 0, $max = 0x7FFFFFFF) {
        $diff = $max - $min;
        if ($diff < 0 || $diff > 0x7FFFFFFF) {
            throw new RuntimeException("Bad range");
        }
        $bytes = mcrypt_create_iv(4, MCRYPT_DEV_URANDOM);
        if ($bytes === false || strlen($bytes) != 4) {
            throw new RuntimeException("Unable to get 4 bytes");
        }
        $ary = unpack("Nint", $bytes);
        $val = $ary['int'] & 0x7FFFFFFF;   // 32-bit safe
        $fp = (float) $val / 2147483647.0; // convert to [0,1]
        return round($fp * $diff) + $min;
    }

    /**
     * @param int $min
     * @param int $max
     * @return int
     * @author Jesse Thompson [http://lightsecond.com/]
     * @see comment [from 2015] @ https://codeascraft.com/2012/07/19/better-random-numbers-in-php-using-devurandom/#comment-231443
     */
    public static function myrand($min = 0, $max = 0x7FFFFFFF) {
        $diff = $max - $min + 1;
        if ($diff > 0x7FFFFFFF) {
            throw new RuntimeException('Bad range');
        }

        $ceiling = floor(0x7FFFFFFF / $diff) * $diff; // The largest *multiple* of diff less than our sample

        do {
            $bytes = mcrypt_create_iv(4, MCRYPT_DEV_URANDOM);
            if ($bytes === false || strlen($bytes) != 4) {
                throw new RuntimeException('Unable to get 4 bytes');
            }
            $ary = unpack("Nint", $bytes);
            $val = $ary['int'] & 0x7FFFFFFF; // 32-bit safe
        } while ($val > $ceiling);
// In the unlikely case our sample is bigger than largest multiple,
// just do over until it’s not any more. Perfectly even sampling in
// our 0<output<diff domain is mathematically impossible unless
// the total number of *valid* inputs is an exact multiple of diff.

        return $val % $diff + $min; // Modulo for President, 2016! 😀
    }

    public static function price($price, $nostreep = false, $noValuta = false, $emptyOrText = false) {
        if ($price === null) {
            return 'op aanvraag';
        }
        $sResult = self::priceRound($price);
        if (strpos($sResult, ',00')) {
            if ($nostreep == false) {
                $sResult = substr($sResult, 0, -3);
                $sResult .= ",-";
                if ($sResult == "0,-") {
                    $sResult = "gratis";
                    if ($emptyOrText)
                        $sResult = is_string($emptyOrText) ? $emptyOrText : "";
                    $noValuta = true;
                }
            }
        }

        return (!$noValuta ? "€&nbsp;" : '') . $sResult;
    }


    public static function smartDate($time, $includeTime = false) {
        if (!is_numeric($time))
            $time = strtotime($time);
        if (!$time)
            return "Onbekend";
        if (defined("TS_DATETIME_FORMAT" . ($includeTime ? "GI" : "")))
            if ($includeTime)
                return date(TS_DATETIME_FORMATGI, $time);
            else
                return date(TS_DATETIME_FORMAT, $time);
        elseif (defined("TS_DATETIME_FORMAT_DMY" . ($includeTime ? "GI" : "")))
            if ($includeTime)
                return date(TS_DATETIME_FORMAT_DMYGI, $time);
            else
                return date(TS_DATETIME_FORMAT_DMY, $time);
        else
            return date("d-m-Y" . ($includeTime ? " G:i" : ""), $time);
    }

    /**
     * check if service of email is a free service.
     *
     * @param type $email
     *
     * @deprecated since 26-11-2013
     */
    public static function isFreeEmailService($email) {
        T::addDeprecationNotification(__FUNCTION__, __CLASS__, __FILE__, __LINE__);

        return Validator::isFreeEmailService($email);
    }

    /**
     * string Padding en prepend voor bijv. €
     * @param $value
     * @param $num
     * @param string $prepend
     * @return string
     * @author Sebastiaan Pasma
     */
    public static function leadingZeroes($value, $num, $prepend = "") {
        return $prepend . str_pad($value, $num, '0', STR_PAD_LEFT);
    }

    /**
     * Dutch DayName from date("N")
     *
     * @param      $dayNum          date("N")
     * @param bool $zeroIndexed niet aanzitten eigenlijk dit, experimenteel.
     *
     * @return string
     */
    public static function dayName($dayNum = false, $zeroIndexed = true, $language = "nl") {
        if ($dayNum > 400 && Validator::isValidTimeStamp($dayNum)) { // FIXME SP: Die 400 is een lelijke oplossing.. FIX LATER!
            $dayNum = date("N", $dayNum);
        }

        if ($dayNum == false) // Today
            $dayNum = date("N");

        if ($zeroIndexed == true)
            $dayNum--;

        if (!array_key_exists($dayNum, self::$dayNames[$language]))
            return "Error";

        return self::$dayNames[$language][$dayNum];
    }

    public static function monthName($monthNum = false, $inputIsZeroIndexed = true, $short = false) {

        $monthArr = $short ? self::$monthNamesShort["nl"] : self::$monthNames["nl"];

        if (!is_numeric($monthNum)) {
            $monthNum = date("m", strtotime($monthNum));
            $zeroIndexed = false;
        }
        $monthNum = (int)$monthNum;

        if ($inputIsZeroIndexed == false)
            $monthNum--;

        if (!array_key_exists($monthNum, $monthArr))
            return "Error";

        return $monthArr[$monthNum];
    }

    public static function percentage($number, $total, $decimals = 2, $percentageSign = true) {
        if ($total > 0) {
            $num = (round($number / ($total / 100), $decimals));
            if ($percentageSign)
                return $num . "%";

            return $num;

        }
        else {
            return $percentageSign ? "0%" : 0;
        }
    }

    public static function getWeekNumber($timestamp = null) {
        if (Validator::isValidTimeStamp($timestamp))
            return date('W', $timestamp);

        return date('W');
    }

    /**
     * Copyright year, als de datum eerder is dan dit jaar "{eerste jaar}-{dit jaar}"
     * @param null $year
     * @param bool $spaces
     * @return string
     * @author Sebastiaan Pasma
     * @since 2012
     */
    static function copyrightYear($year = null, $spaces = true) {
        $current_year = date("Y", time());
        if ($year == null)
            $year = date("Y", time());

        return (($current_year != $year) ? $year . ($spaces ? " - " : "-") : '') . $current_year;

    }

    /**
     * Color Shade
     * @param $color
     * @param int $shadenum
     * @return string
     * @author Sebastiaan Pasma
     * @since 2015
     */
    public static function colorShade($color, $shadenum = 34) {
        $color = trim($color);
        if (strlen($color) > 6) {
            $color = substr($color, -6, 6);
        }
        $rgb = array_map('hexdec', str_split($color, 2));

        $rgb[0] += $shadenum;
        $rgb[1] += $shadenum;
        $rgb[2] += $shadenum;//floor($shadenum*0.8);

        $result = implode('', array_map('dechex', $rgb));

        return '#' . $result;
    }


    public static function toHex($red, $green, $blue, $alpha) {
        return sprintf("%02X%02X%02X", $red, $green, $blue, $alpha);
    }


    /**
     * Calculate age in years based on timestamp and reference timestamp
     * If the reference $now is set to 0, then current time is used
     *
     * @param int $timestamp
     * @param int $now
     *
     * @return int
     */
    public static function calculateAge($timestamp = 0, $now = 0) {
        # default to current time when $now not given
        if ($now == 0)
            $now = time();

        # calculate differences between timestamp and current Y/m/d
        $yearDiff = date("Y", $now) - date("Y", $timestamp);
        $monthDiff = date("m", $now) - date("m", $timestamp);
        $dayDiff = date("d", $now) - date("d", $timestamp);

        # check if we already had our birthday
        if ($monthDiff < 0)
            $yearDiff--;
        elseif (($monthDiff == 0) && ($dayDiff < 0))
            $yearDiff--;

        # set the result: age in years
        $result = intval($yearDiff);

        # deliver the result
        return $result;
    }

    /**
     * function xml2array
     *
     * This function is part of the PHP manual.
     *
     * The PHP manual text and comments are covered by the Creative Commons
     * Attribution 3.0 License, copyright (c) the PHP Documentation Group
     *
     * @author  k dot antczak at livedata dot pl
     * @date    2011-04-22 06:08 UTC
     * @link    http://www.php.net/manual/en/ref.simplexml.php#103617
     * @license http://www.php.net/license/index.php#doc-lic
     * @license http://creativecommons.org/licenses/by/3.0/
     * @license CC-BY-3.0 <http://spdx.org/licenses/CC-BY-3.0>
     */
    public static function xml2array($xmlObject, $out = []) {
        foreach ((array)$xmlObject as $index => $node) {
            $out[$index] = (is_object($node)) ? T::xml2array($node) : $node;
        }

        return $out;
    }

    /**
     * Wordt nog nergens gebruikt, eventuele future use
     * @param $data
     * @param $blacklist
     * @return array
     * @author Sebastiaan Pasma
     * @since 4-apr-2016
     */
    public static function arrayRemoveBlacklist($data, $blacklist) {
        return array_diff_key($data, array_flip($blacklist));
    }

    /**
     * Wordt nog nergens gebruikt, eventuele future use
     * @param $data
     * @param $whitelist
     * @return array
     * @author Sebastiaan Pasma
     * @since 4-apr-2016
     */
    public static function arrayKeepWhitelist($data, $whitelist) {
        return array_intersect_key($data, array_flip($whitelist));
    }

    /**
     * @param $num
     * @param $one
     * @param $more
     * @return mixed
     * @author Sebastiaan Pasma
     * @since 8-apr-2016
     */
    public static function pluralReturn($num, $one, $more) {
        if ($num == 1)
            return $one;
        else
            return $more;
    }

    /**
     * Removes an item from the array and returns its value.
     *
     * @param array $arr The input array
     * @param $key The key pointing to the desired value
     * @return The value mapped to $key or null if none
     * Nog niet gebruikt..
     */
    public static function array_remove_key(array &$arr, $_key) {
        if (array_key_exists($_key, $arr)) {
            $val = $arr[$_key];
            unset($arr[$_key]);

            return $val;
        }

        return null;
    }

    public static function array_remove_value(array &$array, $value) {
        if (($key = array_search($value, $array)) !== false) {
            unset($array[$key]);
        }
    }

    public static function array_shuffle_assoc(&$array) {
        $keys = array_keys($array);
        $new = [];
        shuffle($keys);

        foreach ($keys as $key) {
            $new[$key] = $array[$key];
        }

        $array = $new;

        return true;
    }
}
