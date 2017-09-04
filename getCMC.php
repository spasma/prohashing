<?php
define("BASE_PATH", dirname(__FILE__) . "/");
include "HLite.class.php";
include "H.class.php";
include "TLite.class.php";
include "T.class.php";

if ($content = H::getCachedDataByKey('cmc', 120)) {
    echo $content;
}
else {
    $coinData = json_decode(H::getCachedContent('https://api.coinmarketcap.com/v1/ticker?limit=200', 120), true);
    /**
     * array(14) {
     * ["id"]=>
     * string(7) "bitcoin"
     * ["name"]=>
     * string(7) "Bitcoin"
     * ["symbol"]=>
     * string(3) "BTC"
     * ["rank"]=>
     * string(1) "1"
     * ["price_usd"]=>
     * string(7) "876.258"
     * ["price_btc"]=>
     * string(3) "1.0"
     * ["24h_volume_usd"]=>
     * string(11) "141546000.0"
     * ["market_cap_usd"]=>
     * string(13) "14116614521.0"
     * ["available_supply"]=>
     * string(10) "16110112.0"
     * ["total_supply"]=>
     * string(10) "16110112.0"
     * ["percent_change_1h"]=>
     * string(4) "0.99"
     * ["percent_change_24h"]=>
     * string(5) "-0.91"
     * ["percent_change_7d"]=>
     * string(4) "9.52"
     * ["last_updated"]=>
     * string(10) "1484755467"
     * }
     */
    $coins = [];
    foreach ($coinData as $coin) {
        $coins[($coin["name"])] = [
            'name' => $coin["name"],
            'rate' => $coin['price_btc'],
            'symbol' => $coin['symbol']
        ];
    }
    ksort($coins);


    echo H::setCachedDataByKey('cmc', json_encode($coins));
}