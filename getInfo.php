<?php
function valuta($name) {
    if ($name == "Guldencoin" || $name == "Gulden") {
        return "<i class=\"guldensign\"></i>";
    }
    elseif ($name == "Bitcoin") {
        return "<i class='fa fa-btc'></i>";
    }
    elseif ($name == "Digibyte") {
        return "DGB";
    }

    return $name;
}

define("BASE_PATH", dirname(__FILE__) . "/");
include "HLite.class.php";
include "H.class.php";
include "TLite.class.php";
include "T.class.php";


function createDateRangeArray($strDateFrom, $strDateTo) {
    // takes two dates formatted as YYYY-MM-DD and creates an
    // inclusive array of the dates between the from and to dates.

    // could test validity of dates here but I'm already doing
    // that in the main script

    $aryRange = [];

    $iDateFrom = mktime(1, 0, 0, substr($strDateFrom, 5, 2), substr($strDateFrom, 8, 2), substr($strDateFrom, 0, 4));
    $iDateTo = mktime(1, 0, 0, substr($strDateTo, 5, 2), substr($strDateTo, 8, 2), substr($strDateTo, 0, 4));

    if ($iDateTo >= $iDateFrom) {
        array_push($aryRange, date('Y-m-d', $iDateFrom)); // first entry
        while ($iDateFrom < $iDateTo) {
            $iDateFrom += 86400; // add 24 hours
            array_push($aryRange, date('Y-m-d', $iDateFrom));
        }
    }

    return $aryRange;
}
$errors = '';
if (isset($_REQUEST['apiKey'])) {
    if (isset($_REQUEST['uname'])) {
        $earningsArr = [];
        $milisec = (strtotime(date('Y-m-d') . " 06:00:00") * 1000);
        $earnings = 'https://prohashing.com/exchange/dailyEarnings?endTime=' . $milisec . '&startTime=1&username=' . $_REQUEST['uname'];
        $jsonObj = (json_decode(H::getCachedContent($earnings, 120), true));
        foreach ($jsonObj as $item) {
            $timestamp = $item['date'] / 1000;
            if ($timestamp > strtotime('-7 days')) {
                $date = date("Y-m-d", ($timestamp));

                if ((date('Y-m-d', strtotime($date . " 06:00:00")) == date('Y-m-d', time())) || ($item['date'] === $jsonObj[sizeof($jsonObj)-1]['date'])) {
                    $todayStart = strtotime($date . " 06:00:00");
                    $timeNow = filemtime(H::getCachedContentFilePath($earnings));
                    $diffHoursSinceStartToday = $timeNow - $todayStart;
                    $secondsSinceStartToday = $diffHoursSinceStartToday;
                    try {
                        $estimatedProfitToday = ($item['usdEquivalentValue'] / $secondsSinceStartToday) * (24 * 60 * 60);
                    } catch (Exception $e) {
                        $errors = $e->getMessage();
                    }
                }
 
                unset($item['date']);
                $earningsArr[$date] = $item;
            }
        }
    }
    $btcPriceData = (json_decode(H::getCachedContent('https://api.coindesk.com/v1/bpi/currentprice/EUR.json', 300), true));
//    $coinsData = H::getCachedContent('https://prohashing.com/api/balances?key=' . $_REQUEST['apiKey'], 10);
//    $coinsData = (json_decode($coinsData, true));
//    if (isset($coinsData['message'])) {
//        $errors[] = 'Error (' . $coinsData['message'] . ')';
//    }
//    $statusData = H::getCachedContent('https://prohashing.com/api/status?key=' . $_REQUEST['apiKey'], 12);
//    $statusData = (json_decode($statusData, true));

    $ret = [
        'coins'       => [],
        'workers'     => null,
        'btcEurPrice' => $btcPriceData['bpi']['EUR'],
        'earnings'    => false,
        'errors'       => $errors,
        'earningsURL' => $earnings
    ];
    if (isset($earningsArr)) {
        $ret['earnings'] = $earningsArr;
    }
    if (isset($estimatedProfitToday)) {
        $ret['estimatedProfitToday'] = $estimatedProfitToday;
    }
//    foreach ($coinsData as $coin) {
//        $ret['coins'][] = [
//            'coin'          => isset($coin['coin'])?$coin['coin']:"Error",
//            'symbol'        => isset($coin['coin'])?valuta($coin['coin']):"Error",
//            'allTimeEarned' => isset($coin['allTimeEarned'])?number_format($coin['allTimeEarned'], 8, '.', ''):99,
//            'balance'       => isset($coin['balance'])?number_format($coin['balance'], 8, '.', ''):99
//            T::price($coin['balance'], false, true, (string)$coin['balance']),
//        ];
//    }

    echo json_encode($ret);
}