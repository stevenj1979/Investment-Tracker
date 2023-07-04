<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
</head>
<?php require('includes/config.php');
include_once '../includes/newConfig.php';
include_once '../HTML/Displayhtml.php';?>
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


if(isset($_GET['override'])){
  if ($_SESSION['MobOverride'] == False){$_SESSION['MobOverride'] = True;$_SESSION['roundVar'] = 8;}

}

if(isset($_GET['noOverride'])){
  if ($_SESSION['MobOverride'] == True){$_SESSION['MobOverride'] = False;$_SESSION['roundVar'] = 2;}

}

if ($_SESSION['isMobile'] && $_SESSION['MobOverride'] == False){
  $_SESSION['roundVar'] = 2;
  //header('Location: SellCoins_Mobile.php');
}

if(isset($_GET['lowMarketMode'])){
  $userID = $_SESSION['ID'];
  Echo "We are here,$userID";
  enableLowMarketMode($userID);
  header('Location: SellCoins.php');
}

function enableLowMarketMode($userID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "UPDATE `UserConfig` SET `LowMarketModeEnabled` = 1 where `UserID` = $userID";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function getCoinsfromSQLLoc(){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT `UserID`,`OrderNo`,`Symbol`,`Amount`,`Cost`,`TradeDate` FROM `Transaction` where `Status` = 'Open' order by `TradeDate` desc ";
    $result = $conn->query($sql);
    //$result = mysqli_query($link4, $query);
	//mysqli_fetch_assoc($result);
    while ($row = mysqli_fetch_assoc($result)){
        $tempAry[] = Array($row['UserID'],$row['OrderNo'],$row['Symbol'],$row['Amount'],$row['Cost'],$row['TradeDate']);
    }
    $conn->close();
    return $tempAry;
}

function getTrackingSellCoinsLoc($additionalWhere,$userID = 0){
  $tempAry = [];
  if ($userID <> 0){ $whereclause = "Where `UserID` = $userID and `Status` = 'Open' and `Type` = 'Sell' $additionalWhere";}else{$whereclause = "Where `Status` = 'Open' and `Type` = 'Sell' $additionalWhere";}
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `IDTr`,`Type`,`CoinID`,`UserID`,`CoinPrice`,`Amount`,`Status`,`OrderDate`,`CompletionDate`,`BittrexID`,`OrderNo`,`Symbol`,`LastBuyOrders`, `LiveBuyOrders`,`BuyOrdersPctChange`,`LastMarketCap`,`LiveMarketCap`,`MarketCapPctChange`,`LastCoinPrice`,`LiveCoinPrice`,`CoinPricePctChange`,`LastSellOrders`
  ,`LiveSellOrders`,`SellOrdersPctChange`,`LastVolume`,`LiveVolume`,`VolumePctChange`,`Last1HrChange`,`Live1HrChange`,`1HrPriceChangeLive`,`Last24HrChange`,`Live24HrChange`,`Hr24ChangePctChange`,`Last7DChange`,`Live7DChange`,`D7ChangePctChange`,`BaseCurrency`
  , `Price4Trend`,`Price3Trend`,`LastPriceTrend`,`LivePriceTrend`,`FixSellRule`,`SellRule`,`BuyRule`,`ToMerge`,`LowPricePurchaseEnabled`,`DailyBTCLimit`,`PctToPurchase`,`BTCBuyAmount`,`NoOfPurchases`,`Name`,`Image`,10 as `MaxCoinMerges`,`NoOfCoinSwapsThisWeek`
  ,`OriginalPrice`, `CoinFee`, `LivePrice`, `ProfitUSD`, `ProfitPct`
  ,`CaptureTrend`,`IDCn`,'SellPctCsp',`CoinPrecision`,TIMESTAMPDIFF(MINUTE,`DelayCoinSwapUntil`, now()) as MinsDelay
  FROM `View5_SellCoins` $whereclause order by `ProfitPct` Desc ";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['IDTr'],$row['Type'],$row['CoinID'],$row['UserID'],$row['CoinPrice'],$row['Amount'],$row['Status'],$row['OrderDate'],$row['CompletionDate'],$row['BittrexID'],$row['OrderNo'],
    $row['Symbol'],$row['LastBuyOrders'],$row['LiveBuyOrders'],$row['BuyOrdersPctChange'],$row['LastMarketCap'],$row['LiveMarketCap'],$row['MarketCapPctChange'],$row['LastCoinPrice'],$row['LiveCoinPrice'], //19
    $row['CoinPricePctChange'],$row['LastSellOrders'],$row['LiveSellOrders'],$row['SellOrdersPctChange'],$row['LastVolume'],$row['LiveVolume'],$row['VolumePctChange'],$row['Last1HrChange'],$row['Live1HrChange'],$row['1HrPriceChangeLive'],$row['Last24HrChange'],$row['Live24HrChange'] //31
    ,$row['Hr24ChangePctChange'],$row['Last7DChange'],$row['Live7DChange'],$row['D7ChangePctChange'],$row['BaseCurrency'],$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['FixSellRule'],$row['SellRule'],$row['BuyRule'] //43
    ,$row['ToMerge'],$row['LowPricePurchaseEnabled'],$row['DailyBTCLimit'],$row['PctToPurchase'],$row['BTCBuyAmount'],$row['NoOfPurchases'],$row['Name'],$row['Image'],$row['MaxCoinMerges'],$row['NoOfCoinSwapsThisWeek'] //53
    ,$row['OriginalPrice'],$row['CoinFee'],$row['LivePrice'],$row['ProfitUSD'],$row['ProfitPct'],$row['CaptureTrend'] //59
    ,$row['IDCn'],$row['SellPctCsp'],$row['CoinPrecision'],$row['MinsDelay']); //63
  }
  $conn->close();
  return $tempAry;
}

function getColour($ColourText){
  if ($ColourText >= 0){
    $colour = "Green" ;
  }else {
    $colour = "Red";
  }
  return $colour;
}
function getSellColour($ColourText){
  if ($ColourText > 0){
    $colour = "#D4EFDF" ;
  }elseif ($ColourText == 0 ){
    $colour = "#FCF3CF";
  }else {
    $colour = "#F1948A";
  }
  return $colour;
}

function upAndDownColour($direction){
  if ($direction == 'Up'){
      $tempDir = '#D4EFDF';
  }else{
    $tempDir = '#F1948A';
  }
  return $tempDir;
}

function getConfig($userID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `CoinSalePct`,`MarketCapSellPct`,`VolumeSellPct`,`SellOrdersPct`,`MinPctGain` FROM `Config` WHERE `UserID` =  $userID";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['CoinSalePct'],$row['MarketCapSellPct'],$row['VolumeSellPct'],$row['SellOrdersPct'],$row['MinPctGain']);
  }
  $conn->close();
  return $tempAry;
}

function sendEmailLoc($to, $symbol, $amount, $cost){

    //$to = $row['Email'];
    //echo $row['Email'];
    $subject = "Coin Sale: ".$symbol;
    $body = "Dear Steven, <BR/>";
    $body .= "Congratulations you have sold the following Coin: "."<BR/>";
    $body .= "Coin: ".$symbol." Amount: ".$amount." Price: ".$cost."<BR/>";
    $body .= "Kind Regards\nCryptoBot.";
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $headers .= 'From: Alerts <Alerts@Investment-Tracker.net>' . "\r\n";
    $headers .= "To:".$to."\r\n";
    mail($to, $subject, wordwrap($body,70),$headers);

}

function bittrexbalanceLoc($apikey, $apisecret){
    $nonce=time();
    $uri='https://bittrex.com/api/v1.1/account/getbalance?apikey='.$apikey.'&currency=BTC&nonce='.$nonce;
    $sign=hash_hmac('sha512',$uri,$apisecret);
    $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
    $balance = $obj["result"]["Available"];
    return $balance;
}

function getLiveCoinPriceLoc($symbol){
    $limit = 500;
    $cnmkt = "https://api.coinmarketcap.com/v1/ticker/?limit=".$limit;
    $fgc = json_decode(file_get_contents($cnmkt), true);

  for($i=0;$i<$limit;$i++){
    //print_r($i);

    if ($fgc[$i]["symbol"] == $symbol){
      //print_r($fgc[$i]["symbol"]);
      $tmpCoinPrice = $fgc[$i]["price_btc"];

    }
  }
  logAction("$cnmkt",'CMC');
  return $tmpCoinPrice;
}

function profitScore($profit){
  if($profit > 60){
    $tempNumber = 10;
  }elseif ($profit < 59.999 && $profit > 40){
    $tempNumber = 5;
  }elseif ($profit < 39.999 && $profit > 20){
    $tempNumber = 2;
  }elseif ($profit < 19.999 && $profit > 0){
    $tempNumber = 0;
  }
  return $tempNumber;
}

function showSellCoins($trackingSell,$title){
  $arrLengthSell = count($trackingSell);
  $roundVar = $_SESSION['roundVar'];
  //$userConfig = getConfig($_SESSION['ID']);
  if ($_SESSION['isMobile']){  $fontSize = "font-size:60px"; }else{ $fontSize = "font-size:32px"; }
  echo "<h3>$title</h3>";
  for($x = 0; $x < $arrLengthSell; $x++) {
      //Variables
      //$roundNum = 2;
      //if($_SESSION['isMobile'] == False){$roundNum = 8;}
      $coin = $trackingSell[$x][11];  $livePrice = $trackingSell[$x][19]; $LastCoinPrice = $trackingSell[$x][18]; $baseCurrency = $trackingSell[$x][36];
      $amount = $trackingSell[$x][5];  $orderNo = $trackingSell[$x][10]; $transactionID = $trackingSell[$x][0];
       $purchaseCost = $trackingSell[$x][4]; $realAmount = $trackingSell[$x][26];
      $mrktCap = $trackingSell[$x][17];  $volume = $trackingSell[$x][26]; $sellOrders = $trackingSell[$x][23];
      $pctChange1Hr = $trackingSell[$x][29]; $pctChange24Hr = $trackingSell[$x][32]; $pctChange7D = $trackingSell[$x][35];
      $priceDiff1 = $livePrice - $LastCoinPrice;
      $fee = (($livePrice* $amount)/100)*0.8;
      $liveTotalCost = ($livePrice * $amount);
      $originalPurchaseCost = ($purchaseCost * $amount);
      //$profit = ($liveTotalCost - $originalPurchaseCost - $fee);
      $profit = $trackingSell[$x][58];
      //$profitBtc = $profit/($originalPurchaseCost)*100;
      $profitBtc = $trackingSell[$x][57];
      $userID = $_SESSION['ID']; $coinID = $trackingSell[$x][2];
      $name = $trackingSell[$x][50]; $image = $trackingSell[$x][51]; $targetSellPct = $trackingSell[$x][56]; $num = $trackingSell[$x][62];
      $minsDelay = $trackingSell[$x][63];
      /*echo "<table><td rowspan='3'><a href='Stats.php?coin=$coinID'><img src='$image' width=60 height=60></a></td>";
      echo "<td><p id='largeText' >$name</p></td>";
      echo "<td rowspan='2'><p id='largeText' >".round($livePrice,$roundVar)."</p></td>";
      NewEcho("<td><p id='normalText'>".round($mrktCap,$roundVar)."</p></td>",$_SESSION['isMobile'],0);
      NewEcho("<td><p id='normalText'>".round($pctChange1Hr,$roundVar)."</p></td>",$_SESSION['isMobile'],2);
      echo "<td><p id='largeText' >".round($amount,$roundVar)." $coin</p></td>";
      echo "<td rowspan='3'></td>";
      echo "<td rowspan='3'><div title='Manual Sell!'><a href='ManualSell.php?manSell=Yes&coin=$coin&amount=".$amount."&cost=$originalPurchaseCost&baseCurrency=$baseCurrency&orderNo=$orderNo&transactionID=$transactionID&salePrice=$livePrice'><i class='fas fa-shopping-cart' style='$fontSize;color:DodgerBlue'></i></a></DIV></td>";
      echo "<td rowspan='3'><div title='Split Coins'><a href='ManualSell.php?splitCoin=$coin&amount=".$amount."&cost=$originalPurchaseCost&baseCurrency=$baseCurrency&orderNo=$orderNo&transactionID=$transactionID&salePrice=$livePrice'><i class='fas fa-file-archive' style='$fontSize;color:DodgerBlue'></i></a></DIV></td>";
      echo "<td rowspan='3'><div title='Manual Sell with Tracking!'><a href='ManualSell.php?trackCoin=Yes&baseCurrency=$baseCurrency&transactionID=$transactionID&salePrice=$livePrice&userID=$userID'><i class='fas fa-clock' style='$fontSize;color:DodgerBlue'></i></a></DIV></td>";
      echo "<td rowspan='3'><div title='Save Coins!'><a href='ManualSell.php?manReopen=Yes&transactionID=$transactionID'><i class='fas fa-hryvnia' style='$fontSize;color:DodgerBlue'></i></a></DIV></td>";

      echo "</tr><tr>";
      echo "<td><p id='normalText'>$coin</p></td>";
      NewEcho("<td><p id='normalText'>".round($volume,$roundVar)."</p></td>",$_SESSION['isMobile'],0);
      NewEcho("<td><p id='normalText'>".round($pctChange24Hr,$roundVar)."</p></td>",$_SESSION['isMobile'],2);*/
      $cost = round(number_format((float)$trackingSell[$x][4], 10, '.', ''),8);
      //echo "<td><p id='normalText'>$cost</p></td>";

      //echo "</tr><tr>";


      //$numCol = getNumberColour($profitBtc);
      //echo "<td><p id='smallText' style='color:$numCol'>".round($profitBtc,8)."</p></td>";

      /*$numCol = getNumberColour($priceDiff1);
      echo "<td><p id='smallText' style='color:$numCol'>".number_format($priceDiff1, $roundVar, '.', '')."</p></td>";
      echo "<td><p id='largeText' >".number_format($profit, $roundVar, '.', '')." $baseCurrency</p></td>";

      NewEcho("<td><p id='normalText'>".round($sellOrders,$roundVar)."</p></td>",$_SESSION['isMobile'],0);
      NewEcho("<td><p id='normalText'>".round($pctChange7D,$roundVar)."</p></td>",$_SESSION['isMobile'],0);
      $numCol = getNumberColour($profitBtc);
      echo "<td><p id='smallText' style='color:$numCol'>".number_format($profitBtc, $roundVar, '.', '')."</p></td>";*/
      $boxAry = array (
        array("Image","Stats.php?coin=$coinID","$image","","Image",5,"","","Text",0),
        array("",$name."-".$baseCurrency,"","","",5,"","","Text",0),
        array("CoinPrice",$livePrice,"","","",0,"","$baseCurrency","Float",$roundVar),
        array("PriceDiff",$priceDiff1,"","","Colour",0,$numColPD,"%","Float",$roundVar),
        array("Mins Delay",$minsDelay,"","","",0,"","","Text",0),

        array("PurchasePrice",$originalPurchaseCost,"","","",1,"","$baseCurrency","Float",$roundVar),
        array("LivePrice",$liveTotalCost,"","","",1,"","$baseCurrency","Float",$roundVar),
        array("Profit",$profit,"","","Colour",1,$numColProfit,"$baseCurrency","Float",$roundVar),
        array("Profit",$profitPct,"","Colour","Colour",1,"","%","Float",2),
        array("Cost per Coin",$cost,"","","",1,"","$baseCurrency","Float",$roundVar),
        array("Amount",$amount,"","","",1,"","$coin","Float",$roundVar),

        array("MarketCap",$mrktCap,"","","Colour",2,"","%","Float",$roundVar),
        array("Volume",$volume,"","","Colour",2,"","%","Float",$roundVar),

        array("1HrChange",$pctChange1Hr,"","","Colour",3,"","%","Float",$roundVar),
        array("24HrChange",$pctChange24Hr,"","","Colour",3,"","%","Float",$roundVar),
        array("7DChange",$pctChange7D,"","","Colour",3,"","%","Float",$roundVar),

        array("Manual Sell","ManualSell.php?manSell=Yes&coin=$coin&amount=".$amount."&cost=$originalPurchaseCost&baseCurrency=$baseCurrency&orderNo=$orderNo&transactionID=$transactionID&salePrice=$livePrice","","","Link",4,"","","Text",0),
        array("Split Coins","ManualSell.php?splitCoin=$coin&amount=".$amount."&cost=$originalPurchaseCost&baseCurrency=$baseCurrency&orderNo=$orderNo&transactionID=$transactionID&salePrice=$livePrice","","","Link",4,"","","Text",0),
        array("Manual Sell with Tracking","ManualSell.php?trackCoin=Yes&baseCurrency=$baseCurrency&transactionID=$transactionID&salePrice=$livePrice&userID=$userID","","","Link",4,"","","Text",0),
        array("Save Coins","ManualSell.php?manReopen=Yes&transactionID=$transactionID","","","Link",4,"","","Text",0)
      );
      displayBox($boxAry);
  }
  //print_r("</table>");
  //echo "<BR><a href='SellCoins.php?lowMarketMode=1'>Enable Low Market Mode</a>";
  //Echo "<BR><a href='SellCoins.php?noOverride=Yes'>View Mobile Page</a>".$_SESSION['MobOverride'];

}


date_default_timezone_set('Asia/Dubai');
$date = date('Y/m/d H:i:s', time());


?>

<!--<div class="container">

	<div class="row">

	    <div class="col-xs-12 col-sm-8 col-md-8 col-sm-offset-2">-->


				<?php

        displayHeader(4);
        $trackingSell = getTrackingSellCoinsLoc(' and `CoinSwapDelayed` = 0',$_SESSION['ID']);
        print_r("<h2>Sell Some Coins Now!</h2>");
        //echo "<h3><a href='SellCoins.php'>Sell Coins</a> &nbsp > &nbsp <a href='SellCoins_Tracking.php'>Tracking</a> &nbsp > &nbsp <a href='SellCoins_Saving.php'>Saving</a> &nbsp > &nbsp <a href='SellCoins_Spread.php'>Spread Bet</a> &nbsp > &nbsp <a href='SellCoins_SpreadCoin.php'>Spread Bet Coin</a>
        // &nbsp > &nbsp <a href='SellCoins_SwapCoins.php'>Swap Coins</a></h3>";
        displaySubHeader("SellCoin");
        showSellCoins($trackingSell,'Reduce Loss Enabled');
        $trackingSell = getTrackingSellCoinsLoc(' and `CoinSwapDelayed` = 1',$_SESSION['ID']);
        showSellCoins($trackingSell,'Reduce Loss Disabled');
				displaySideColumn();
//include header template
require('layout/footer.php');
$date = date('Y/m/d H:i:s', time());
echo " Last Updated :".$date;
?>
