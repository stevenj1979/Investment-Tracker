<?php
require('includes/config.php');
//if not logged in redirect to login page
if(!$user->is_logged_in()){ header('Location: login.php'); exit(); }
include_once ('/home/stevenj1979/SQLData.php');
include '../includes/newConfig.php';
?>

<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
  <script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript">
  google.load('visualization', '1', {packages: ['corechart']});
  google.load('visualization2', '1', {packages: ['corechart']});
</script>
<script type="text/javascript">
  function drawVisualization() {
    var jsonData = null;
    var userID = "<?php echo $_SESSION['ID']; ?>";
    var json = $.ajax({
      url: "http://www.investment-tracker.net/Investment-Tracker/Cryptobot/1/getDashboardChart.php?ID=" + userID, // make this url point to the data file
      dataType: "json",
      async: false,
      success: (
    function(data) {
        jsonData = data;
    })
    }).responseText;



    // Create and populate the data table.
    var data = new google.visualization.DataTable(jsonData);


    // Create and draw the visualization.
  //var chart= new google.visualization.LineChart(document.getElementById('visualization')).
  //      draw(data, {curveType: "function",
  //                  width: 900, height: 400,
  //                  }
  //          );
  var chart = new google.visualization.AreaChart(document.getElementById('visualization')).
  draw(data, {curveType: "function",
                    width: 900, height: 400,
                    }
            );
  }

  function drawVisualization2() {
    var jsonData = null;
    var userID = "<?php echo $_SESSION['ID']; ?>";
    var json = $.ajax({
      url: "http://www.investment-tracker.net/Investment-Tracker/Cryptobot/1/getDashboardChart.php?ID=" + userID, // make this url point to the data file
      dataType: "json",
      async: false,
      success: (
    function(data) {
        jsonData = data;
    })
    }).responseText;



    // Create and populate the data table.
    var data = new google.visualization.DataTable(jsonData);


    // Create and draw the visualization.
  //var chart= new google.visualization.LineChart(document.getElementById('visualization')).
  //      draw(data, {curveType: "function",
  //                  width: 900, height: 400,
  //                  }
  //          );
  var chart = new google.visualization.Table(document.getElementById('visualization2')).
  draw(data, {curveType: "function",
                    width: 900, height: 400,
                    }
            );
  }

  google.setOnLoadCallback(drawVisualization);
  google.setOnLoadCallback(drawVisualization2);
</script>
</head>
<?php
  //include 'includes/functions.php';

?>
<html>
<style>
<?php
$title = 'CryptoBot';
$current_url = $_SERVER[ 'REQUEST_URI' ];
header( "Refresh: 120; URL=$current_url" );
//include header template
require($_SERVER['DOCUMENT_ROOT'].'/Investment-Tracker/Cryptobot/1/layout/header.php');
include_once ('/home/stevenj1979/SQLData.php');
$locationStr = "Location: /Investment-Tracker/Cryptobot/1/m/BuyCoins.php";
setStyle($_SESSION['isMobile']);
//.page-wrapper
  //{
   //width:1000px;
  // margin:0 auto;
// }?>
</style>
<body>
<?php

function getLiveCoinPriceUSD($symbol){
    $limit = 100;
    $cnmkt = "https://api.coinmarketcap.com/v1/ticker/?limit=".$limit;
    $fgc = json_decode(file_get_contents($cnmkt), true);

  for($i=0;$i<$limit;$i++){
    //print_r($i);
    if ($fgc[$i]["symbol"] == $symbol){
      //print_r($fgc[$i]["symbol"]);
      $tmpCoinPrice = $fgc[$i]["price_usd"];
    }
  }
  logAction("$cnmkt",'CMC');
  return $tmpCoinPrice;
}

function bittrex_balance($apikey, $apisecret, $base ){
    $nonce=time();
    $uri='https://bittrex.com/api/v1.1/account/getbalance?apikey='.$apikey.'&currency='.$base.'&nonce='.$nonce;
    $sign=hash_hmac('sha512',$uri,$apisecret);
    $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
    $balance = $obj["result"]["Available"];
    return $balance;
}

function getTotalHoldings($userID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "SELECT `BittrexBTC`,`BittrexUSDT`,`BittrexETH`, `BTCPrice`, `ETHPrice`, `USDTPrice`,`PendingCoinsUSD` FROM `UserProfit` WHERE `UserID` = $userID and DATE_FORMAT(`ActionDate`, '%Y-%m-%d') = CURDATE()";
  //echo $sql;
  $result = $conn->query($sql);

  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['BittrexBTC'],$row['BittrexUSDT'],$row['BittrexETH'],$row['BTCPrice'],$row['ETHPrice'],$row['USDTPrice'],$row['PendingCoinsUSD']);
  }
  $conn->close();
  return $tempAry;
}



function new_Price($bitPrice, $pct, $action){
  if ($action == "Buy"){
    //$bitPrice = 0.00000742;
    echo "<BR> 1: ".number_format((float)$bitPrice, 8, '.', '');
    $bitPrice = $bitPrice-(($bitPrice/100)*$pct);
    echo "<BR> 2: ".number_format((float)$bitPrice, 8, '.', '');
    //$bitPrice = number_format((float)$bitPrice, 8, '.', '');
    //echo "<BR> 3: $bitPrice";
    return round($bitPrice,8, PHP_ROUND_HALF_DOWN);
  }else{
    $bitPrice = $bitPrice+(($bitPrice/100)*$pct);
    $bitPrice = round($new_Price,8, PHP_ROUND_HALF_DOWN);
    return $bitPrice;
  }
}


$uProfit = getTotalHoldings($_SESSION['ID']);
//$btcPrice = getLiveCoinPriceUSD("BTC");

displayHeader(0);
              //$profitUSD = $uProfit[0][2]*$btcPrice;

              echo "<form action='Dashboard.php?dropdown=Yes' method='post'><select name='currencySelect'>";
              echo "<Option value='BTC'>BTC</option>";
              echo "<Option value='USD'>USD</option>";
              echo "</select><input type='submit' value='Update'/></form>";

              if ($_POST['currencySelect'] == 'BTC'){
                 $conversion = 1;
                 $curSymbol = 'BTC';
                 $round = 8;
              }else{
                 $conversion = $btcPrice;
                 $curSymbol = '$';
                 $round = 2;
              }
              $apiKey = getAPIKeyread(); $apiSecret = getAPISecretRead();
              $btcPrice = (float)$uProfit[0][0];
              $usdtPrice = (float)$uProfit[0][1];
              $ethProfit = (float)$uProfit[0][2];
              //echo "<BR> $btcPrice : $usdtPrice : $ethProfit ";
              //$LiveBTCPrice = number_format((float)(bittrexCoinPrice($apiKey, $apiSecret,'USDT','BTC')), 8, '.', '');
              $LiveBTCPrice = (float)$uProfit[0][3];
              //$LiveETHPrice = number_format((float)(bittrexCoinPrice($apiKey, $apiSecret,'USDT','ETH')), 8, '.', '');
              $LiveETHPrice = (float)$uProfit[0][4];
              $LiveUSDTPrice = (float)$uProfit[0][5];
              $pendingUSDT = (float)$uProfit[0][6];
              //echo "<BR> $LiveBTCPrice : $LiveETHPrice";
              $totalProfit = ($btcPrice*$LiveBTCPrice)+($usdtPrice*$LiveUSDTPrice)+($ethProfit*$LiveETHPrice);
              echo "<h3>Dashboard</h3>";
              echo "<table><TH>BTC</TH><TH>USDT</TH><TH>ETH</TH><TH>Purchased Coins USD</TH><TH>Total USD</TH><tr>";
              if ($_SESSION['isMobile']){
                $btcPrice = round($btcPrice,3); $usdtPrice = round($usdtPrice,3); $ethProfit = round($ethProfit,3);$totalProfit = round($totalProfit,3);
                echo "<td>&nbspBTC $btcPrice</td><td>&nbspUSDT $usdtPrice</td><td>&nbspETH $ethProfit</td><td>&nbspUSD $pendingUSDT</td><td>&nbspUSD $totalProfit</td>";
              }else{
                echo "<td>&nbspBTC $btcPrice</td><td>&nbspUSDT $usdtPrice</td><td>&nbspETH $ethProfit</td><td>&nbspUSD $pendingUSDT</td><td>&nbspUSD $totalProfit</td>";
              }
              echo "</table>";

              //$tableData = chartData();
              //echo $tableData;

              //echo $_SESSION['ID'];
              ?>
              <div class="page-wrapper">
                 <br />
                 <h2 align="center">Current Coin Holdings</h2>
                 <!--<div id="visualization" style="width: 1200px; height: 400px;"></div>
                 <div id="visualization2" style="width: 1200px; height: 400px;"></div>-->
                 <div id="visualization" ></div>
                 <div id="visualization2" ></div>
              </div><?php
          displaySideColumn();
          require($_SERVER['DOCUMENT_ROOT'].'/Investment-Tracker/Cryptobot/1/layout/footer.php');
          $date = date('Y/m/d H:i:s', time());
          echo " Last Updated :".$date;?>

        </body>
        </html>
