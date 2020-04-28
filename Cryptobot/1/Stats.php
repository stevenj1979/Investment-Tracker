<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<?php require('includes/config.php');
include_once '../includes/newConfig.php';?>
<style>
<?php include 'style/style.css'; ?>
</style> <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript">
google.load('visualization', '1', {packages: ['corechart']});
</script>
<script type="text/javascript">
function drawVisualization() {
  var jsonData = null;
  <?php if ($_POST['coinSelect'] <> ""){
          $coinOption = explode(":",$_POST['coinSelect']);
          $sql_option = "`Symbol` = '".$coinOption[0]."' ";
          $_SESSION['symbol'] = $coinOption[0];
          $sql_option_base = "`BaseCurrency` = '".$coinOption[1]."'";
        }else{
          $sql_option = "`Symbol` = 'ETH' ";
          $_SESSION['symbol'] = "ETH";
          $sql_option_base = "`BaseCurrency` = 'BTC'";
        }
?>
  var symbol = "<?php echo $_SESSION['symbol']; ?>";
  var json = $.ajax({
    url: "http://www.investment-tracker.net/Investment-Tracker/Cryptobot/1/getCoinChart.php?coinID=" + symbol , // make this url point to the data file
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
var chart= new google.visualization.LineChart(document.getElementById('visualization')).
      draw(data, {curveType: "function",
                  width: 1300, height: 400,
                 }
          );
}


google.setOnLoadCallback(drawVisualization);
</script><?php

//if not logged in redirect to login page
if(!$user->is_logged_in()){ header('Location: login.php'); exit(); }

//define page title
$title = 'CryptoBot';
$current_url = $_SERVER[ 'REQUEST_URI' ];
header( "Refresh: 120; URL=$current_url" );
//include header template
require($_SERVER['DOCUMENT_ROOT'].'/Investment-Tracker/Cryptobot/1/layout/header.php');
include_once ('/home/stevenj1979/SQLData.php');
$locationStr = "Location: /Investment-Tracker/Cryptobot/1/m/BuyCoins.php";
setStyle($_SESSION['isMobile']);
//$coinSymbol = "`Symbol` = 'ETH'";

//if(empty($sql_option)){

//}

function getHistoryFromSQL(){
    global $sql_option;
    global $sql_option_base;
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    //$coinOption = explode(":",$sql_option);
    $sql = "SELECT
    `ID`,`Symbol`,`LiveBuyOrders`,`LastBuyOrders`,`BuyOrdersPctChange`,`LiveMarketCap`,`LastMarketCap`,`MarketCapPctChange`,`Live1HrChange`,`Last1HrChange`,
    `Hr1ChangePctChange`,`Live24HrChange`,`Last24HrChange`,`Hr24ChangePctChange`,`Live7DChange`,`Last7DChange`,`D7ChangePctChange`,`LiveCoinPrice`,`LastCoinPrice`,
    `CoinPricePctChange`,`LiveSellOrders`,`LastSellOrders`,`SellOrdersPctChange`,`LiveVolume`,`LastVolume`,`VolumePctChange`,`BaseCurrency`,`ActionDate`
    FROM `CoinBuyHistory` WHERE $sql_option and $sql_option_base and `ActionDate` >= curdate() - INTERVAL DAYOFWEEK(curdate())-15 MINUTE
    order by `ActionDate` desc";
    $result = $conn->query($sql);
    //echo $sql;
    //$result = mysqli_query($link4, $query);
	//mysqli_fetch_assoc($result);
    while ($row = mysqli_fetch_assoc($result)){
        $tempAry[] = Array($row['ID'],$row['Symbol'],$row['LiveBuyOrders'],$row['LastBuyOrders'],$row['BuyOrdersPctChange'],$row['LiveMarketCap'],$row['LastMarketCap'],$row['MarketCapPctChange'],
        $row['Live1HrChange'],$row['Last1HrChange'],$row['Hr1ChangePctChange'],$row['Live24HrChange'],$row['Last24HrChange'],$row['Hr24ChangePctChange'],$row['Live7DChange'],$row['Last7DChange'],
        $row['D7ChangePctChange'],$row['LiveCoinPrice'],$row['LastCoinPrice'],$row['CoinPricePctChange'],$row['LiveSellOrders'],$row['LastSellOrders'],$row['SellOrdersPctChange'],$row['LiveVolume'],
        $row['LastVolume'],$row['VolumePctChange'],$row['BaseCurrency'],$row['ActionDate']);
    }
    $conn->close();
    return $tempAry;
}

function getCoinsFromSQL(){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT `Symbol`,`ID`,`BaseCurrency` FROM `CoinStatsView` order by `Symbol` asc";
    $result = $conn->query($sql);
    //$result = mysqli_query($link4, $query);
	//mysqli_fetch_assoc($result);
    while ($row = mysqli_fetch_assoc($result)){
        $tempAry[] = Array($row['Symbol'],$row['ID'],$row['BaseCurrency']);
    }
    $conn->close();
    return $tempAry;
}

displayHeader(2);
        $coinStats = getCoinsFromSQL();
        $StatsArrLength = count($coinStats);
        $historyStats = getHistoryFromSQL();
        $historySize = count($historyStats);
        echo "<h2>Stats</h2><form action='Stats.php?dropdown=Yes' method='post'><select name='coinSelect'>";

        for($x = 0; $x < $StatsArrLength; $x++) {
            echo "<Option value='".$coinStats[$x][0].":".$coinStats[$x][2]."'>".$coinStats[$x][0].":".$coinStats[$x][2]."</option>";
        }
        echo "</select><input type='submit' value='Update'/></form>";?>
        <h2 align="center">Coin Price History</h2>
        <div id="visualization" style="width: 600px; height: 400px;"></div> <?php
        echo "<table><TH>ID</TH><TH>Symbol</TH> <TH>LiveBuyOrders</TH> <TH>LastBuyOrders</TH> <TH>BuyOrdersPctChange</TH> <TH>LiveMarketCap</TH> <TH>LastMarketCap</TH> <TH>MarketCapPctChange</TH>
        <TH>Live1HrChange</TH> <TH>Last1HrChange</TH> <TH>Hr1ChangePctChange</TH> <TH>Live24HrChange</TH> <TH>Last24HrChange</TH> <TH>Hr24ChangePctChange</TH> <TH>Live7DChange</TH> <TH>Last7DChange</TH>
        <TH>D7ChangePctChange</TH> <TH>LiveCoinPrice</TH> <TH>LastCoinPrice</TH> <TH>CoinPricePctChange</TH> <TH>LiveSellOrders</TH> <TH>LastSellOrders</TH> <TH>SellOrdersPctChange</TH> <TH>LiveVolume</TH>
        <TH>LastVolume</TH> <TH>VolumePctChange</TH> <TH>BaseCurrency</TH> <TH>ActionDate</TH><tr>";
        for($y = 0; $y < $historySize; $y++) {
          echo "<td>".$historyStats[$y][0]."</td>";echo "<td>".$historyStats[$y][1]."</td>";echo "<td>".$historyStats[$y][2]."</td>";echo "<td>".$historyStats[$y][3]."</td>";echo "<td>".$historyStats[$y][4]."</td>";
          echo "<td>".$historyStats[$y][5]."</td>";echo "<td>".$historyStats[$y][6]."</td>";echo "<td>".$historyStats[$y][7]."</td>";echo "<td>".$historyStats[$y][8]."</td>";
          echo "<td>".$historyStats[$y][9]."</td>";echo "<td>".$historyStats[$y][10]."</td>";echo "<td>".$historyStats[$y][11]."</td>";echo "<td>".$historyStats[$y][12]."</td>";
          echo "<td>".$historyStats[$y][13]."</td>";echo "<td>".$historyStats[$y][14]."</td>";echo "<td>".$historyStats[$y][15]."</td>";echo "<td>".$historyStats[$y][16]."</td>";
          echo "<td>".$historyStats[$y][17]."</td>";echo "<td>".$historyStats[$y][18]."</td>";echo "<td>".$historyStats[$y][19]."</td>";echo "<td>".$historyStats[$y][20]."</td>";
          echo "<td>".$historyStats[$y][21]."</td>";echo "<td>".$historyStats[$y][22]."</td>";echo "<td>".$historyStats[$y][23]."</td>";echo "<td>".$historyStats[$y][24]."</td>";
          echo "<td>".$historyStats[$y][25]."</td>";echo "<td>".$historyStats[$y][26]."</td>";echo "<td>".$historyStats[$y][27]."</td><tr>";
        }
        echo "</table>";
				displaySideColumn();

//include header template
require('layout/footer.php');
?>
