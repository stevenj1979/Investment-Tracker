<?php
require('includes/config.php');
//if not logged in redirect to login page
if(!$user->is_logged_in()){ header('Location: login.php'); exit(); }
include_once ('../../../../SQLData.php');
include '../includes/newConfig.php';

?>


  <?php newHeaderHTML('Dashboard'); ?>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
  <script type="text/javascript" src="http://www.google.com/jsapi"></script>
  <script src="script.js" defer></script>
  <script type="text/javascript">
  google.load('visualization', '1', {packages: ['corechart']});
  google.load('visualization2', '1', {packages: ['corechart']});
</script>

<script type="text/javascript">
  function drawVisualization() {
    var jsonData = null;
    var userID = "<?php echo $_SESSION['ID']; ?>";
    var json = $.ajax({
      url: "http://www.investment-tracker.net/Investment-Tracker/Cryptobot/1/getNewDashboardChart.php?ID=" + userID, // make this url point to the data file
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

<?php
  //include 'includes/functions.php';
  closeHTMLHeader();
?>


<?php
$title = 'CryptoBot';
$current_url = $_SERVER[ 'REQUEST_URI' ];
header( "Refresh: 120; URL=$current_url" );
//include header template
//require('layout/header.php');
//include_once ('../../../../SQLData.php');
$locationStr = "Location: /Investment-Tracker/Cryptobot/1/m/SellCoins.php";

//.page-wrapper
  //{
   //width:1000px;
  // margin:0 auto;
// }?>

<?php

if ($_GET['zeroBTCSaving'] <> ""){
  $userID = $_GET['UserID'];
  runZeroSaving('BTC',$userID);
}elseif ($_GET['zeroUSDTSaving'] <> ""){
  $userID = $_GET['UserID'];
  runZeroSaving('USDT',$userID);
}elseif ($_GET['zeroETHSaving'] <> ""){
  $userID = $_GET['UserID'];
  runZeroSaving('ETH',$userID);
}

function runZeroSaving($coin,$userID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  if ($coin == 'BTC'){
    $sql = "UPDATE `UserCoinSavings` SET `SavingBTC`= 0 WHERE `UserID` = $userID";
  }elseif ($coin == 'USDT'){
    $sql = "UPDATE `UserCoinSavings` SET `SavingUSDT`= 0 WHERE `UserID` = $userID ";
  }elseif ($coin == 'ETH'){
    $sql = "UPDATE `UserCoinSavings` SET `SavingETH`= 0 WHERE `UserID` = $userID";
  }


  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("runZeroSaving",$sql,3,1,"SQL","CoinID:$coin");
  logAction("runZeroSaving: ".$sql, 'BuySell', 0);
}

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
  $sql = "SELECT
            (SELECT  `Date`FROM `BittrexBalances` WHERE `Symbol` = 'BTC') as ActionDate
            ,(SELECT (`Total`*`Price`) FROM `BittrexBalances` WHERE `Symbol` = 'BTC' and `UserID` = $userID) as TotalBTC
            ,(SELECT (`Total`*`Price`) FROM `BittrexBalances` WHERE `Symbol` = 'ETH' and `UserID` = $userID) as TotalETH
            ,(SELECT `Total` FROM `BittrexBalances` WHERE `Symbol` = 'USDT' and `UserID` = $userID) as TotalUSDT
            ,(SELECT `SavingBTC`* getBTCPrice(84) FROM `UserCoinSavings`   WHERE `UserID` = $userID) as SavingBTC
            ,(SELECT `SavingUSDT`* getBTCPrice(83) FROM `UserCoinSavings` WHERE `UserID` = $userID) as SavingUSDT
            ,(SELECT `SavingETH`* getBTCPrice(85)  FROM `UserCoinSavings` WHERE `UserID` = $userID) as SavingETH
            ,Trim(getNewCoinAllocation($userID,(SELECT `LowMarketModeEnabled` FROM `UserConfig` WHERE `UserID` = $userID),'BTC',0,0,0))+0 As BTCAllocation
            ,Trim(getNewCoinAllocation($userID,(SELECT `LowMarketModeEnabled` FROM `UserConfig` WHERE `UserID` = $userID),'USDT',0,0,0))+0 As USDTAllocation
            ,Trim(getNewCoinAllocation($userID,(SELECT `LowMarketModeEnabled` FROM `UserConfig` WHERE `UserID` = $userID),'ETH',0,0,0))+0 As ETHAllocation
            ,(SELECT `LowMarketModeEnabled` FROM `UserConfig` WHERE `UserID` = $userID) as LowMarketMode
            ,(SELECT Trim(`HoldingUSDT`* getBTCPrice(83))+0 FROM `UserCoinSavings`  WHERE `UserID` = $userID) as TotalHoldingUSDT
            ,(SELECT Trim(`HoldingBTC`* getBTCPrice(84))+0   FROM `UserCoinSavings` WHERE `UserID` = $userID)as TotalHoldingBTC
            ,(SELECT Trim(`HoldingETH` * getBTCPrice(85))+0  FROM `UserCoinSavings` WHERE `UserID` = $userID) as TotalHoldingETH";
  //echo $sql;
  $result = $conn->query($sql);

  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ActionDate'],$row['TotalBTC'],$row['TotalETH'],$row['TotalUSDT'],$row['SavingBTC'],$row['SavingUSDT'],$row['SavingETH'],$row['BTCAllocation'],$row['USDTAllocation'] //8
      ,$row['ETHAllocation'],$row['LowMarketMode'],$row['TotalHoldingUSDT'],$row['TotalHoldingBTC'],$row['TotalHoldingETH']); //13
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
$webMarketStats = getWebMarketStats();
//$btcPrice = getLiveCoinPriceUSD("BTC");

displayHeader(0);
//cssButtonHeader();
              //$profitUSD = $uProfit[0][2]*$btcPrice;
              if ($_SESSION['isMobile']){
                $num = 2; $fontSize = "<i class='fas fa-bolt' style='font-size:60px;color:#D4EFDF'>"; $dformat ="YYYY-mm-dd";
              }else{
                $num = 8; $fontSize = "<i class='fas fa-bolt' style='font-size:32px;color:#D4EFDF'>"; $dformat ="YYYY-mm-dd H:i:s";
              }
              //echo "<form action='Dashboard.php?dropdown=Yes' method='post'><select name='currencySelect'>";
              //echo "<Option value='BTC'>BTC</option>";
              //echo "<Option value='USD'>USD</option>";
              //echo "</select><input type='submit' value='Update'/></form>";

              //if ($_POST['currencySelect'] == 'BTC'){
              //   $conversion = 1;
              //   $curSymbol = 'BTC';
              //   $round = 8;
            //}else{
                 $conversion = $btcPrice;
                 $curSymbol = '$';
                 $round = 2;
              //}
              $Id = $_SESSION['ID'];
              $apiKey = getAPIKeyread(); $apiSecret = getAPISecretRead();
              $btcPrice = (float)$uProfit[0][1];
              $usdtPrice = (float)$uProfit[0][3];
              $ethProfit = (float)$uProfit[0][2];
              $bittrexTotal = $btcPrice + $usdtPrice + $ethProfit;
              $btcSaving = $uProfit[0][4];
              $usdtSaving = $uProfit[0][5];
              $ethSaving = $uProfit[0][6];
              $savingTotal = $btcSaving + $usdtSaving + $ethSaving;

              //echo "<BR> $btcPrice : $usdtPrice : $ethProfit ";
              //$LiveBTCPrice = number_format((float)(bittrexCoinPrice($apiKey, $apiSecret,'USDT','BTC')), 8, '.', '');
              //$LiveBTCPrice = (float)$uProfit[0][3];
              //$LiveETHPrice = number_format((float)(bittrexCoinPrice($apiKey, $apiSecret,'USDT','ETH')), 8, '.', '');
              //$LiveETHPrice = (float)$uProfit[0][4];
              //$LiveUSDTPrice = (float)$uProfit[0][5];
              //$pendingUSDT = (float)$uProfit[0][6];
              //echo "<BR> $LiveBTCPrice : $LiveETHPrice";
              //$totalProfit = ($btcPrice*$LiveBTCPrice)+($usdtPrice*$LiveUSDTPrice)+($ethProfit*$LiveETHPrice)+$pendingUSDT;
              echo "<h3>Dashboard</h3>";
              $avgPrice = ($webMarketStats[0][1]+$webMarketStats[0][2])/2;
              echo "<BR><H3>1Hr:".round($webMarketStats[0][0],2)."% \t| 24Hr:".round($webMarketStats[0][1],2)."%\t| 7D:".round($webMarketStats[0][2],2)."%\t | Avg: ".round($avgPrice,2)."%</H3><BR>";
              echo "<table><TH></TH><TH>BTC</TH><TH>ETH</TH><TH>USDT</TH><TH>Total</TH><tr>";

                echo "<tr><td>&nbspHolding</td><td>&nbspUSD&nbsp".round($btcPrice,2)."</td><td>&nbspUSD&nbsp".round($ethProfit,2)."</td><td>&nbspUSD&nbsp".round($usdtPrice,2)."</td><td>USD&nbsp".round($bittrexTotal,2)."</td></tr>";
                echo "<tr><td>&nbspSaving</td><td>&nbspUSD&nbsp".round($btcSaving,2)."</td><td>&nbspUSD&nbsp".round($ethSaving,2)."</td><td>&nbspUSD&nbsp".round($usdtSaving,2)."</td><td>USD&nbsp".round($savingTotal,2)."</td></tr>";
                //echo "<tr><td>&nbspBuy With Saving</td><td>&nbsp<a href='Dashboard.php?zeroBTCSaving=Yes&UserID=$Id'>$fontSize</i></a> </td>";
                //echo "<td>&nbsp<a href='Dashboard.php?zeroETHSaving=Yes&UserID=$Id'>$fontSize</i></a> </td>";
                //echo "<td>&nbsp<a href='Dashboard.php?zeroUSDTSaving=Yes&UserID=$Id'>$fontSize</i></a> </td>";
                $btcAlloc = $uProfit[0][7];$usdtAlloc = $uProfit[0][8];$ethAlloc = $uProfit[0][9]; $lowMarketMode = $uProfit[0][10];
                echo "<tr><td>Coin Allocation</td><td>".round($btcAlloc,6)."</td><td>".round($ethAlloc,4)."</td><td>".round($usdtAlloc,2)."</td><td>$lowMarketMode</td></tr>";
                $holdingBTC = $uProfit[0][12]; $holdingUSDT = $uProfit[0][11]; $holdingETH = $uProfit[0][13];
                $holdingTotal = $holdingBTC + $holdingUSDT + $holdingETH;
                echo "<tr><td>Coin Holding</td><td>".round($holdingBTC,2)."</td><td>".round($holdingETH,2)."</td><td>".round($holdingUSDT,2)."</td><td>".round($holdingTotal,2)."</td></tr>";
                echo "</tr>";

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
              //$date = date('Y/m/d H:i:s', time());
              displaySideColumn();
          //require($_SERVER['DOCUMENT_ROOT'].'/Investment-Tracker/Cryptobot/1/layout/footer.php');
            ?>


        </body>
        </html>
