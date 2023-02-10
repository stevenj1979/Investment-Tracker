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
include_once ('../../../../SQLData.php');
$locationStr = "Location: /Investment-Tracker/Cryptobot/1/m/SellCoins.php";
setStyle($_SESSION['isMobile']);

date_default_timezone_set('Asia/Dubai');
$date = date('Y/m/d H:i:s', time());

if(isset($_GET['CancelTrack'])){
  $TransactionID = $_GET['TransID'];
  echo "<BR> Cancel Tracking ID: $TransactionID";
  cancelTrackingSell($TransactionID);
  reopenTransaction($TransactionID);
}
if(isset($_GET['SellNow'])){
  $TransactionID = $_GET['TransID'];
  echo "<BR> Cancel Tracking ID: $TransactionID";
  $transData = getNewTrackingSellCoinTrans($TransactionID);
  $apikey = $transData[0][10]; $apiSecret = $transData[0][11]; $kek = $transData[0][12];$coin = $transData[0][26];$email= $transData[0][13];
  $userID = $transData[0][2]; $baseCurrency = $transData[0][15]; $userName = $transData[0][14]; $orderNo = $transData[0][7];
  $coinID = $transData[0][9];
  if (!Empty($kek)){$apiSecret = decrypt($kek,$transData[0][11]);}
  $salePrice = number_format((float)round($transData[0][20],8, PHP_ROUND_HALF_UP), 8, '.', '');
  $amount = number_format((float)round($transData[0][8],8, PHP_ROUND_HALF_UP), 8, '.', '');
  $cost = number_format((float)round($transData[0][29],8, PHP_ROUND_HALF_UP), 8, '.', '');
  reopenTransaction($TransactionID);
  //echo "sellCoins($apikey, $apiSecret', $coin, $email, $userID, 0, '$date',$baseCurrency, 1, 1, 99999,'$userName', '$orderNo' ,$amount,$cost,$TransactionID,$coinID,0,0,$salePrice)";
  //sellCoins($apikey, $apiSecret, $coin, $email, $userID, 0, $date,$baseCurrency, 1, 1, 99999,$userName, $orderNo ,$amount,$cost,$TransactionID,$coinID,0,0,$salePrice);
  cancelTrackingSell($TransactionID);
  header("Location: ManualSell.php?manSell=Yes&coin=$coin&amount=".$amount."&cost=$cost&baseCurrency=$baseCurrency&orderNo=$orderNo&transactionID=$TransactionID&salePrice=$salePrice");

}

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
        //echo "<h3><a href='SellCoins.php'>Sell Coins</a> &nbsp > &nbsp <a href='SellCoins_Tracking.php'>Tracking</a> &nbsp > &nbsp <a href='SellCoins_Saving.php'>Saving</a> &nbsp > &nbsp <a href='SellCoins_Spread.php'>Spread Bet</a> &nbsp > &nbsp <a href='SellCoins_SpreadCoin.php'>Spread Bet Coin</a>
        // &nbsp > &nbsp <a href='SellCoins_SwapCoins.php'>Swap Coins</a></h3>";
        displaySubHeader("SellCoin");
        echo "<table border=1>";
        NewEcho ("<th>Image</th><th>Coin</th><TH>Type</th><th>Price</th><th>PurchasePrice</th>",$_SESSION['isMobile'],2);
        NewEcho ("<th>Trans ID</th><th>OrderNo</th><th>Live Total Price</th>",$_SESSION['isMobile'],0);
        NewEcho ("<th>Base to Live Diff %</th><th>Profit</th>",$_SESSION['isMobile'],2);
        NewEcho ("<th>Fee</th>",$_SESSION['isMobile'],0);
        NewEcho ("<th>Profit Pct</th>",$_SESSION['isMobile'],2);
        //NewEcho ("<th>Base Sell Price</th>",$_SESSION['isMobile'],2);
        NewEcho ("<th>Total Rises in Price</th>",$_SESSION['isMobile'],2);
        NewEcho ("<th>OG Profit Pct</th><th>Cancel</th><th>Sellnow</th>",$_SESSION['isMobile'],2);

        for($x = 0; $x < $arrLengthSell; $x++) {
          $CoinPrice = $trackingSell[$x][0]; $TrackDate = $trackingSell[$x][1];  $UserID = $trackingSell[$x][2]; $NoOfRisesInPrice = $trackingSell[$x][3]; $TransactionID = $trackingSell[$x][4];
          $BuyRule = $trackingSell[$x][5]; $FixSellRule = $trackingSell[$x][6]; $OrderNo = $trackingSell[$x][7]; $Amount = $trackingSell[$x][8]; $CoinID = $trackingSell[$x][9];
          $APIKey = $trackingSell[$x][10]; $APISecret = $trackingSell[$x][11]; $KEK = $trackingSell[$x][12]; $Email = $trackingSell[$x][13]; $UserName = $trackingSell[$x][14];
          $BaseCurrency = $trackingSell[$x][15]; $SendEmail = $trackingSell[$x][16]; $SellCoin = $trackingSell[$x][17]; $CoinSellOffsetEnabled = $trackingSell[$x][18]; $CoinSellOffsetPct = $trackingSell[$x][19];
          $LiveCoinPrice = $trackingSell[$x][20]; $minsFromDate = $trackingSell[$x][21]; $profit = $trackingSell[$x][22]; $fee = $trackingSell[$x][23]; $ProfitPct = $trackingSell[$x][24];
          $totalRisesInPrice =  $trackingSell[$x][33]; $coin = $trackingSell[$x][26];$ogPctProfit = $trackingSell[$x][27];$baseSellPrice = $trackingSell[$x][35];
          $trackingType = $trackingSell[$x][41]; $image = $trackingSell[$x][51];
          if ($BaseCurrency == 'BTC'){ $num = 8;}
          echo "<tr>";
          NewEcho ("<td><a href='Stats.php?coin=$CoinID'><img src='$image' width=60 height=60></a></td><td>|$coin</td><td>$trackingType</td>",$_SESSION['isMobile'],2);
          NewEcho ("<td>|".number_format($CoinPrice,$num)."</td>",$_SESSION['isMobile'],2);
          $purchasePrice = $CoinPrice * $Amount;
          NewEcho ("<td>|".number_format( $purchasePrice,$num)."</td>",$_SESSION['isMobile'],2);
          //NewEcho ("<td>|</td>",$_SESSION['isMobile'],2);
          NewEcho ("<td>|$TransactionID</td>",$_SESSION['isMobile'],0);
          NewEcho ("<td>|$OrderNo</td>",$_SESSION['isMobile'],0);
          $livePriceUSD = $LiveCoinPrice * $Amount;
          //$profitPct = ($profit/$purchasePrice)*100;
          NewEcho ("<td>|$livePriceUSD</td>",$_SESSION['isMobile'],0);
          NewEcho ("<td>|".number_format((($LiveCoinPrice-$baseSellPrice)/$baseSellPrice)*100,2)."</td>",$_SESSION['isMobile'],2);
          NewEcho ("<td>|".number_format($profit,$num)."</td>",$_SESSION['isMobile'],2);
          NewEcho ("<td>|".number_format($fee,$num)."</td>",$_SESSION['isMobile'],0);
          NewEcho ("<td>|".number_format($ProfitPct,$num)."</td>",$_SESSION['isMobile'],2);
          //NewEcho ("<td>|".number_format($baseSellPrice,$num)."</td>",$_SESSION['isMobile'],2);
          //NewEcho ("<td>|0</td>",$_SESSION['isMobile'],2);
          NewEcho ("<td>|$NoOfRisesInPrice / $totalRisesInPrice</td>",$_SESSION['isMobile'],2);
          NewEcho ("<td>|".number_format($ogPctProfit,$num)."</td>",$_SESSION['isMobile'],2);
          NewEcho ("<td><a href='SellCoins_Tracking.php?CancelTrack=Yes&TransID=$TransactionID'><i class='fas fa-ban' style='$fontSize;color:DodgerBlue'></i></a></td>",$_SESSION['isMobile'],2);
          NewEcho ("<td><a href='SellCoins_Tracking.php?SellNow=Yes&TransID=$TransactionID'><i class='fas fa-shopping-cart' style='$fontSize;color:DodgerBlue'></i></a></td>",$_SESSION['isMobile'],2);
          echo "</tr>";
        }
        print_r("</table>");
				displaySideColumn();
//include header template
require('layout/footer.php');
$date = date('Y/m/d H:i:s', time());
echo " Last Updated :".$date;
?>
