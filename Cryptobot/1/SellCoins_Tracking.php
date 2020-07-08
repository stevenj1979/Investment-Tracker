<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
</head>
<?php require('includes/config.php');
include_once '../includes/newConfig.php';?>
<style>
<?php include 'style/style.css'; ?>
</style> <?php
ini_set("max_execution_time", 150);
//if not logged in redirect to login page
if(!$user->is_logged_in()){ header('Location: login.php'); exit(); }

//define page title
$title = 'CryptoBot';
$current_url = $_SERVER[ 'REQUEST_URI' ];
header( "Refresh: 120; URL=$current_url" );
//include header template
require('layout/header.php');
include_once ('/home/stevenj1979/SQLData.php');
$locationStr = "Location: /Investment-Tracker/Cryptobot/1/m/SellCoins.php";
setStyle($_SESSION['isMobile']);

date_default_timezone_set('Asia/Dubai');
$date = date('Y/m/d H:i:s', time());


?>

<!--<div class="container">

	<div class="row">

	    <div class="col-xs-12 col-sm-8 col-md-8 col-sm-offset-2">-->


				<?php
        if ($_SESSION['isMobile']){ $num = 2; $fontSize = "font-size:60px"; }else{$num = 8;$fontSize = "font-size:32px"; }
        displayHeader(4);
        $trackingSell = getNewTrackingSellCoins($_SESSION['ID']);
        $arrLengthSell = count($trackingSell);
        //$userConfig = getConfig($_SESSION['ID']);
        print_r("<h2>Tracking Sell Coins</h2>");
        echo "<h3><a href='SellCoins.php'>Sell Coins</a> &nbsp > &nbsp <a href='SellCoins_Tracking.php'>Sell Coins Tracking</a></h3>";
        echo "<table>";
        for($x = 0; $x < $arrLengthSell; $x++) {
          $CoinPrice = $trackingSell[$x][0]; $TrackDate = $trackingSell[$x][1];  $UserID = $trackingSell[$x][2]; $NoOfRisesInPrice = $trackingSell[$x][3]; $TransactionID = $trackingSell[$x][4];
          $BuyRule = $trackingSell[$x][5]; $FixSellRule = $trackingSell[$x][6]; $OrderNo = $trackingSell[$x][7]; $Amount = $trackingSell[$x][8]; $CoinID = $trackingSell[$x][9];
          $APIKey = $trackingSell[$x][10]; $APISecret = $trackingSell[$x][11]; $KEK = $trackingSell[$x][12]; $Email = $trackingSell[$x][13]; $UserName = $trackingSell[$x][14];
          $BaseCurrency = $trackingSell[$x][15]; $SendEmail = $trackingSell[$x][16]; $SellCoin = $trackingSell[$x][17]; $CoinSellOffsetEnabled = $trackingSell[$x][18]; $CoinSellOffsetPct = $trackingSell[$x][19];
          $LiveCoinPrice = $trackingSell[$x][20]; $minsFromDate = $trackingSell[$x][21]; $profit = $trackingSell[$x][22]; $fee = $trackingSell[$x][23]; $ProfitPct = $trackingSell[$x][24];
          $totalRisesInPrice =  $trackingSell[$x][25]; $coin = $trackingSell[$x][26];
          echo "<tr>";
          echo "<td>$coin</td>";
          echo "<td>$CoinPrice</td>";
          echo "<td>$Amount</td>";
          echo "<td>$NoOfRisesInPrice</td>";
          echo "<td>$TransactionID</td>";
          echo "<td>$OrderNo</td>";
          echo "<td>$FixSellRule</td>";
          echo "<td>$LiveCoinPrice</td>";
          echo "<td>$profit</td>";
          echo "<td>$fee</td>";
          echo "<td>$ProfitPct</td>";
          echo "<td>$totalRisesInPrice</td>";
          echo "</tr>";
        }
        print_r("</table>");
				displaySideColumn();
//include header template
require('layout/footer.php');
$date = date('Y/m/d H:i:s', time());
echo " Last Updated :".$date;
?>
