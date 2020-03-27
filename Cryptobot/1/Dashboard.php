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
<?php include 'style/style.css';
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
  return $tmpCoinPrice;
}

function bittrexbalance($apikey, $apisecret, $base ){
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
  $sql = "SELECT `BittrexBTC`,`BittrexUSDT`,`BittrexETH`FROM `UserProfit` WHERE `UserID` = $userID and DATE_FORMAT(`ActionDate`, '%Y-%m-%d') = CURDATE()";
  //echo $sql;
  $result = $conn->query($sql);

  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['BittrexBTC'],$row['BittrexUSDT'],$row['BittrexETH']);
  }
  $conn->close();
  return $tempAry;
}



function newPrice($bitPrice, $pct, $action){
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
    $bitPrice = round($newPrice,8, PHP_ROUND_HALF_DOWN);
    return $bitPrice;
  }
}


$uProfit = getTotalHoldings($_SESSION['ID']);
$btcPrice = getLiveCoinPriceUSD("BTC");
?>
<div class="header">
  <table><TH><table class="CompanyName"><td rowspan="2" class="CompanyName"><img src='Images/CBLogoSmall.png' width="40"></td><td class="CompanyName"><div class="Crypto">Crypto</Div><td><tr class="CompanyName">
      <td class="CompanyName"><Div class="Bot">Bot</Div></td></table></TH><TH>: Logged in as:</th><th> <i class="glyphicon glyphicon-user"></i>  <?php echo $_SESSION['username'] ?></th></Table><br>
  </div>
<?php
//$tempOutput = getNewHeader();
?>
  <div class="topnav">
    <a href="Dashboard.php" class="active">Dashboard</a>
    <a href="Transactions.php">Transactions</a>
    <a href="Stats.php">Stats</a>
    <a href="BuyCoins.php">Buy Coins</a>
    <a href="SellCoins.php">Sell Coins</a>
    <a href="Profit.php">Profit</a>
    <a href="bittrexOrders.php">Bittrex Orders</a>
    <a href="Settings.php">Settings</a><?php
    if ($_SESSION['AccountType']==1){echo "<a href='AdminSettings.php'>Admin Settings</a>";}
//echo $tempOutput;
?>
</div>
      <div class="row">
            <div class="settingCol1">
              <?php
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
              $btcPrice = number_format((float)$uProfit[0][0] * $conversion, $round, '.', '');
              $usdtPrice = number_format((float)$uProfit[0][1] * $conversion, $round, '.', '');
              $ethProfit = number_format((float)$uProfit[0][2] * $conversion, $round, '.', '');
              $LiveBTCPrice = number_format((float)(bittrexCoinPrice($apiKey, $apiSecret,'USDT','BTC')), 8, '.', '');
              $LiveETHPrice = number_format((float)(bittrexCoinPrice($apiKey, $apiSecret,'USDT','ETH')), 8, '.', '');
              $totalProfit = ($btcPrice*$LiveBTCPrice)+$usdtPrice+($ethProfit*$LiveETHPrice);
              echo "<h3>Dashboard</h3>";
              echo "<table><TH>BTC</TH><TH>USDT</TH><TH>ETH</TH><TH>Total USD</TH><tr>";
              echo "<td>BTC $btcPrice</td><td>USDT $usdtPrice</td><td>ETH $ethProfit</td><td>USD $totalProfit</td>";
              echo "</table>";

              //$tableData = chartData();
              //echo $tableData;

              //echo $_SESSION['ID'];
              ?>
              <div class="page-wrapper">
                 <br />
                 <h2 align="center">Current Coin Holdings</h2>
                 <div id="visualization" style="width: 1200px; height: 400px;"></div>
                 <div id="visualization2" style="width: 1200px; height: 400px;"></div>
              </div>
            </div>
        </div>

        <div class="footer">
          <hr>
          <input type="button" onclick="location='logout.php'" value="Logout"/>
        </div>

        </body>
        </html>
