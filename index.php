<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>ProHash Dashboard by API-key</title>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
    <meta name="language" content="NL"/>
    <meta name="robots" content="all"/>
    <meta name="viewport" content="initial-scale=1, maximum-scale=1">
    <link href='//cdn.jsdelivr.net/devicons/1.8.0/css/devicons.min.css' rel='stylesheet'>
    <script src="https://use.fontawesome.com/13e985b26f.js"></script>
    <link href='//pas.ma/gulden/style.css' rel='stylesheet'>
    <link href='foundation.min.css' rel='stylesheet'>
    <link href='prohash.css?<?php echo filemtime(dirname(__FILE__) . "/prohash.css"); ?>' rel='stylesheet'>
    <link href='guldensign.css' rel='stylesheet'>
    <script type="text/javascript" src="//pas.ma/gulden/jquery-1.12.1.min.js"></script>
    <script type="text/javascript" src="foundation.min.js"></script>
    <script type="text/javascript" src="Lockr.min.js"></script>
    <link rel='stylesheet' type='text/css' href='chartist.css'/>
    <script type='text/javascript' src='chartist.min.js?1385'></script>
    <script type='text/javascript' src='autobahn.min.js?1385'></script>
    <script type='text/javascript' src='chartist-plugin-pointlabels.min.js?1385'></script>
</head>
<body>
<?php
define("BASE_PATH", dirname(__FILE__) . "/");
include "HLite.class.php";
include "H.class.php";
if (!isset($_REQUEST['apiKey'])) {
    echo '<form method="get">';
    echo '<label>ProHashing API Key';
    echo '<input type="text" name="apiKey"/>';
    echo '</label>';
    echo '<input type="submit"/>';

    echo '</form>';
    exit;
}
else {
    $apiKey = $_REQUEST['apiKey'];


    ?>
    <div class="container">
        <div class="content">
            <div class="card">
                <div class="firstinfo">
                    <div class="fi-container">
                        <div class="profileinfo showdata">
                            <div class="row">
                                <h2>Balances</h2>
                                <div class="total-balances">Sum of current coin-balances:
                                    <span class="total-eur-balance-container"><i class="fa fa-eur"></i> <span class='total-eur-balance'> <i class="fa fa-spin fa-refresh"> </i></span></span>
                                    <span class="total-btc-balance-container"><i class="fa fa-btc"></i> <span class='total-btc-balance'> <i class="fa fa-spin fa-refresh"> </i></span></span>
                                </div>
                                <div class="calc-est" style="display: none;">Calculated Estimate Profit Today:
                                    <span class="estimatedProfitToday"></span></div>
                                <div class="small-up-2 medium-up-3 large-up-4 coins">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                            </div>
                            <div class="row">
                                <h2>Workers
                                    <span class="total-hashrate-container">(Total Hashrate: <span class="total-hashrate"></span>)</span>
                                </h2>
                                <div class="small-up-2 medium-up-3 large-up-3 workers">
                                    <i class="fa fa-spin fa-spinner"></i>
                                </div>
                                <div class="small-12 columns retry" style="display: none;">
                                    Something went wrong on Prohashing's side.. <a onclick="retry();">Retry</a>
                                </div>
                            </div>
                            <div class="row">
                                <h2>Daily Earnings</h2>
                                <div class="small-up-2 medium-up-3 large-up-3 earnings-username">
                                    <form onsubmit="setUsername(); return false;">
                                        <input class="username" name="username" type="text" placeholder="Prohashing Username"/>
                                    </form>
                                </div>
                                <div class="small-up-2 medium-up-3 large-up-3 earnings-chart" style="display: none;">
                                    Username: <span class="username-val"></span> (<a onclick="delUsername();">Remove</a>)
                                    <div class="ct-chart"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row text-center convert-container">
                <!--                convert BTC-totals to:-->
                <!--                <select class="convert" onchange="cmcUpdate();"></select>-->
            </div>
            <p class="donations">
                <small>
                    <b>Donations:</b><br/> NLG: <a href="Gulden:GfNF4dyvEU47m2AqXzSPLPQAzibYBY5kvZ">GfNF4dyvEU47m2AqXzSPLPQAzibYBY5kvZ</a><br/>
                    BTC:
                    <a href="bitcoin:1Bwbw3Ez7F7phM9G6JbfrkAAV9QEak6Sev">1Bwbw3Ez7F7phM9G6JbfrkAAV9QEak6Sev</a><br/>
                    (Thank you!)
                </small>
            </p>
        </div>
    </div>
    <div class="reveal" id="errorModal" data-reveal>
        <h1>Error</h1>
        <p class="error"></p>
        <button class="close-button" data-close aria-label="Close modal" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <script type="text/javascript">
        var convertRate = Lockr.get('convertRate', 1);
        var convertSym = Lockr.get('convertSym', 'BTC');
        var totalBTC = 0;
        var totalEUR = 0;
        var hiddenCoins = Lockr.get('hiddenCoins', []);
        function cmcUpdate() {
            var selectedItem = cmc[$('.convert option:selected').val()];
            convertRate = (selectedItem.rate);
            convertSym = (selectedItem.symbol);
            Lockr.set('convertRate', convertRate);
            Lockr.set('convertSym', convertSym);
            getData();
        }
        function hashrate(val, precision) {
            if (isNaN(val)) {
                return '0 H/s';
            }
            if (val < 1000) {
                return val + ' H/s'
            }
            val = val / 1000; // KHash
            if (val < 1000) {
                return (val.toFixed(precision ? precision : 2)) + " KH/s"
            }

            val = val / 1000; // MHash
            if (val < 1000) {
                return (val.toFixed(precision ? precision : 2)) + " MH/s"
            }

            return (val / 1000).toFixed(precision ? precision : 2) + " GH/s";
        }
        function parseBalance(amount, precision) {
            if (isNaN(parseFloat(amount))) {
                amount = 0;
            }
            return (parseFloat(amount).toFixed(precision ? precision : 8));
        }
        var cmcReady = false;
        var chart = false;

        function setUsername() {
            var newUname = $('.username').val();
            if (newUname.length > 0) {
                Lockr.set('username', newUname);
                getData();
            }
            return false;
        }
        function delUsername() {
            Lockr.set('username', false);
            getData();
        }

        function showAllCoins() {
            Lockr.set('hiddenCoins', []);
            getData();
        }
        function hideCoin(coin) {
            hiddenCoins = Lockr.get('hiddenCoins', []);
            if (hiddenCoins.indexOf(coin) == -1) {
                hiddenCoins.push(coin);
            }
            Lockr.set('hiddenCoins', hiddenCoins);
            getData();
        }
        var error = false;
        var btcEurPrice = false;
        function getData() {
            if (error)
                return;
            var username = <?php echo(isset($_REQUEST['username']) ? "'" . $_REQUEST['username'] . "'" : "Lockr.get('username', false)"); ?>;

            if (username) {
                $('.username-val').html(username);
            }
            $.getJSON('getInfo.php', 'apiKey=<?php echo $apiKey; ?>' + (username ? '&uname=' + username : ''), function (data) {
                if (data.errors.length) {
                    error = true;
                    $("#errorModal .error").html(data.errors.join(" "))
                    $('#errorModal').foundation('open');
                } else {

                    var coinsData = '';

                    var btcAmount = 0;
                    var cmcRead = cmcReady;
//                    $.each(data.coins, function (index, coin) {
//                        allTime = parseFloat(coin.allTimeEarned).toFixed(coin.symbol == 'Bitcoin' ? 8 : 3);
//                        if (coin.coin == "Bitcoin") {
//                            allTime = ((allTime / convertRate).toFixed(2)) + ' ' + convertSym;
//                        } else {
//                            allTime = allTime + ' ' + coin.symbol;
//                        }
//                        if (hiddenCoins.indexOf(coin.coin) == -1)
//                            coinsData = coinsData + '<div class="column"><div class="coin-symbol">' + coin.symbol + " <span class='hide-item' onclick='hideCoin(\"" + coin.coin + "\")'><i class='fa fa-eye-slash'></i></span></div><div class='coin-balance'>" + parseBalance(coin.balance) + "</div><div class='coin-ate'>All Time Earned:<br/>" + allTime + "</div></div>";
//                        if (cmcRead && cmc && cmc[coin.coin] && cmc[coin.coin].rate) {
//                            btcAmount += parseFloat(parseBalance(coin.balance) * cmc[coin.coin].rate);
//                        }
//                    });

                    btcEurPrice = data.btcEurPrice;
//                    if (cmcRead) {
//                        $('.total-btc-balance').html(parseBalance(btcAmount));
//                        $('.total-eur-balance').html(parseBalance(btcAmount * data.btcEurPrice.rate_float, 3))
//                    }
//                    $('.coins').html(coinsData);
                    if (data.estimatedProfitToday) {
                        $('.calc-est:hidden').slideDown(200);
                        $('.estimatedProfitToday').html('$' + (data.estimatedProfitToday.toFixed(2)))
                    } else {
                        $('.calc-est:visible').slideUp(200);
                    }
                    if (data.earnings) {

                        var valuesEarnings = [];
                        var valuesElectricity = [];
                        var labels = [];
                        $.each(data.earnings, function (date, values) {
                            valuesEarnings.push({"value": values.usdEquivalentValue, "meta": 'Earnings: ' + date});
                            valuesElectricity.push({
                                "value": values.electricityCostUsd,
                                "meta": 'Electricity: ' + date
                            });
                            labels.push(date.substring(5));
                        });

                        if (!chart) {
                            chart = new Chartist.Line('.ct-chart', {
                                labels: labels,
                                series: [
                                    valuesEarnings,
                                    valuesElectricity
                                ]
                            }, {
                                height: '300px',
                                fullWidth: true,
                                chartPadding: {
                                    top: 20,
                                    right: 40
                                },
                                plugins: [
                                    Chartist.plugins.ctPointLabels({
                                        textAnchor: 'middle',
                                        labelInterpolationFnc: function (meta) {
                                            return '$' + meta.toFixed(2)
                                        }
                                    })
                                ]
                            });
                        } else {
                            chart.update({
                                labels: labels,
                                series: [
                                    valuesEarnings,
                                    valuesElectricity
                                ]
                            });
                        }


                        $('.earnings-username:visible').slideUp(500);
                        $('.earnings-chart:hidden').slideDown(500, function () {
                            if (chart)
                                chart.update();
                        });
                    } else {
                        $('.earnings-username:hidden').slideDown(500);
                        $('.earnings-chart:visible').slideUp(500);
                    }
                }
            })
        }
        setInterval(function () {
            getData();
        }, 6000);



        setInterval(function () {
            if (!workersDone)
                retry();
        }, 5000);

        setInterval(function() {
            if (!balanceDone)
                wampSession.call('f_all_balance_updates', ['<?php echo $apiKey; ?>']).then(initialCoinUpdatesReceived);

        }, 25000)






        getData();

        var cmc;
        function getCMC() {
            $.getJSON('getCMC.php', function (jsonObj) {
                cmc = jsonObj;
                $.each(cmc, function (index, coin) {
                    $('.convert').append('<option ' + (convertSym == coin.symbol ? 'selected' : '') + ' value=' + index + '>' + index + '</option>');
                });
                if (!cmcReady)
                    cmcReady = true;
            })
        }
        setInterval(function () {
            getCMC();
        }, 240000);
        getCMC();

        $(document).foundation();

        var wampConnection = null;
        var wampUser = 'web';
        var wampPassword = 'web';
        var wampSession = null;
        var balanceDone = false;
        var workersDone = false;

        function onChallenge(session, method, extra) {
            if (method == 'wampcra') {

                return autobahn.auth_cra.sign(wampPassword, extra.challenge);
            }
        }

        function connectionOpen(session, details) {
            wampSession = session;
//            wampSession.subscribe('found_block_updates', onBlockUpdate);
            try {
                wampSession.call('f_all_miner_updates', ['<?php echo $apiKey; ?>']).then(initialSessionUpdatesReceived);
            } catch (err) {
                console.log(err);
                $('.workers').html("");
                $('.retry').show();
            }
            wampSession.call('f_all_balance_updates', ['<?php echo $apiKey; ?>']).then(initialCoinUpdatesReceived);

        }


        function retry() {
            try {
                $('.retry').hide();
                wampSession.call('f_all_miner_updates', ['<?php echo $apiKey; ?>']).then(initialSessionUpdatesReceived);
            } catch (err) {
                console.log(err);
                $('.retry').show();
            }
        }
        function onBlockUpdate(block) {
//            console.log(block);
        }
        btcAmount = 0;
        function initialCoinUpdatesReceived(coins) {
            if (balanceDone)
                return;
            balanceDone = true;
            coinsData = "";

            console.log(coins);

            $.each(coins, function (name, balance) {

                var coin = {
                    coin: name,
                    balance: balance
                };
//                allTime = parseFloat(coin.allTimeEarned).toFixed(coin.symbol == 'Bitcoin' ? 8 : 3);
//                if (coin.coin == "Bitcoin") {
//                    allTime = ((allTime / convertRate).toFixed(2)) + ' ' + convertSym;
//                } else {
//                    allTime = allTime + ' ' + coin.symbol;
//                }

                coinsData = coinsData + '<div data-coin="' + coin.coin + '" class="column"><div class="coin-symbol">' + coin.coin + " <span class='hide-item' onclick='hideCoin(\"" + coin.coin + "\")'><i class='fa fa-eye-slash'></i></span></div><div class='coin-balance'>" + parseBalance(coin.balance) + "</div></div>"; // <div class='coin-ate'>All Time Earned:<br/>" + 0 + "</div></div>";
                if (cmcReady && cmc && cmc[coin.coin] && cmc[coin.coin].rate) {
                    btcAmount += parseFloat(parseBalance(coin.balance) * cmc[coin.coin].rate);
                }
            });
            hiddenCoins = Lockr.get('hiddenCoins', []);
            coinsData = coinsData + '<div class="colum hidden-coins" style="display: none">(<a onclick="showAllCoins()">Restore hidden coins</a>)</div>';
            hiddenCoins = Lockr.get('hiddenCoins', []);
            if (hiddenCoins.length) {
                $('.hidden-coins').show();
            } else {
                $('.hidden-coins').hide();
            }
            $('.coins').html(coinsData);
            recalcTotals();
            wampSession.subscribe('balance_updates_<?php echo $apiKey; ?>', onCoinUpdate);
        }
        function onCoinUpdate(coin) {
            console.log(coin);
            hiddenCoins = Lockr.get('hiddenCoins', []);
            if (hiddenCoins.length) {
                $('.hidden-coins').show();
            } else {
                $('.hidden-coins').hide();
            }
            var coin = coin[0];

            var coinEl = $('[data-coin="' + coin.coin + '"]');
            var _el = coinEl.find('.coin-balance');

            _el.html(parseBalance(parseFloat(_el.html()) + coin.balance));
            if (hiddenCoins.indexOf(coin.coin) !== -1) {
                $(coinEl).hide();
            } else {


                var newEl = jQuery('<span/>', {
                    text: '+' + parseBalance(coin.balance)
                }).css({
                    position: 'absolute',
                    dispay: 'block',
                    color: '#00b140',
                    top: $(_el).offset().top,
                    'z-index': 100,
                    width: $(_el).width,
                    'text-align': 'right',
                    left: $(_el).offset().left
                });

                $(newEl).appendTo($('body'));
                $(newEl).animate({
                    top: '-=25px',
                    opacity: '0'
                }, 2000, function () {
                    $(this).remove();
                });
            }
            recalcTotals();
        }
        function recalcTotals() {
            btcAmount = 0;

            $.each($('.coin-balance'), function (index, valEl) {
                coin = $(valEl).closest('.column').data('coin');
                if (cmcReady && coin && cmc[coin])
                    btcAmount += parseFloat(parseBalance(parseFloat($(valEl).html()) * cmc[coin].rate));

//                btcAmount+=parseFloat($(valEl).html());
            });

            $('.total-btc-balance').html(parseBalance(btcAmount));
            if (btcEurPrice)
                $('.total-eur-balance').html(parseBalance(btcAmount * btcEurPrice.rate_float, 3))
        }

        function initialSessionUpdatesReceived(workers) {
            if (workersDone)
                return;

            workersDone = true;
            var workersData = '';
            var totalHashrate = 0;
            var workersSorted = [];

            workers.sort(function (a, b) {
                if (isNaN(a.miner_name)) {
                    textA = a.miner_name.toUpperCase();
                } else {
                    textA = parseFloat(a.miner_name);
                }

                if (isNaN(b.miner_name)) {
                    textB = b.miner_name.toUpperCase();
                } else {
                    textB = parseFloat(b.miner_name);
                }
                return (textA < textB) ? -1 : (textA > textB) ? 1 : 0;
            });

            $.each(workers, function (index, worker) {
                workersData = workersData + '<div class="column" data-miner="' + worker.miner_name + '"><div class="worker-container">' + "<div class='worker-name'>" + worker.miner_name + "</div></div><div class='worker-coin'>" + (worker.coin_name) + "</div><div class='worker-hashrate'>" + /*( / 1000 / 1000).toFixed(2)*/hashrate(parseFloat(worker.adjusted_hashrate)) + "</div><div class='worker-hashrate-avarage' data-tooltip title='Avarage hashrate over the last 2 hours'>" + /*( / 1000 / 1000).toFixed(2)*/hashrate(parseFloat(worker.hashrate)) + "</div><div class='worker-difficulty' data-tooltip title='Actual Difficulty'>Diff: <span class='diff'>" + worker.actual_difficulty + "</span></div></div>";
                totalHashrate += parseFloat(worker.adjusted_hashrate);
            });
            $('.total-hashrate').html(hashrate(totalHashrate, 3));
            $('.workers').html(workersData);
            $('.worker-difficulty, .worker-hashrate-avarage').foundation();

            wampSession.subscribe('miner_updates_<?php echo $apiKey; ?>', onMinerUpdate);
        }

        var updateHashrateTimeout = false;
        function onMinerUpdate(update) {
            var info = update[0];
            var miner = $('[data-miner="' + info.miner_name + '"]');
            miner.attr('data-hashrate', info.adjusted_hashrate);
            miner.find('.worker-coin').html(info.coin_name);
            miner.find('.worker-hashrate').html(hashrate(parseFloat(info.adjusted_hashrate)));
            miner.find('.worker-hashrate-avarage').html(hashrate(parseFloat(info.hashrate)));
            miner.find('.diff').html(info.actual_difficulty);

            if (updateHashrateTimeout)
                clearTimeout(updateHashrateTimeout);

            updateHashrateTimeout = setTimeout(function () {
                updateHashrate();
            }, 1000);
        }
        function updateHashrate() {
            var totalHashrate = 0;
            $.each($('[data-miner]'), function (index, minerEl) {
                totalHashrate += parseFloat($(minerEl).attr('data-hashrate'));
            });
            $('.total-hashrate').html(hashrate(totalHashrate, 3));
        }

        wampConnection = new autobahn.Connection({
            url: 'wss://live.prohashing.com:443/ws',
            realm: 'mining',
            authmethods: ['wampcra'],
            authid: wampUser,
            onchallenge: onChallenge
        });

        wampConnection.onopen = connectionOpen;
        wampConnection.open();
    </script>
<?php } ?>
</body>
</html>