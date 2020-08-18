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


if(isset($_GET['override'])){
  if ($_SESSION['MobOverride'] == False){$_SESSION['MobOverride'] = True;$_SESSION['roundVar'] = 8;}

}

if(isset($_GET['noOverride'])){
  if ($_SESSION['MobOverride'] == True){$_SESSION['MobOverride'] = False;$_SESSION['roundVar'] = 2;}

}

if ($_SESSION['isMobile'] && $_SESSION['MobOverride'] == False){
  $_SESSION['roundVar'] = 2;
  header('Location: SellCoins_Mobile.php');
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

function getTrackingSellCoinsLoc($userID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "SELECT `ID`,`Symbol`,`LiveBuyOrders`,`LastBuyOrders`,`BuyOrdersPctChange`,`LiveMarketCap`,`LastMarketCap`,`MarketCapPctChange`,`Live1HrChange`,`Hr1ChangePctChange`,`Live24HrChange`,`Last24HrChange`,`Hr24ChangePctChange`,`Live7DChange`,`Last7DChange`,`D7ChangePctChange`,`LiveCoinPrice`,
`LastCoinPrice`,`CoinPricePctChange`,`LiveSellOrders`,`LastSellOrders`,`SellOrdersPctChange`,`LiveVolume`,`LastVolume`,`VolumePctChange`,`BaseCurrency`,`Amount`,`OrderNo`, `CoinPrice`,`Profit`,`TransactionID`, `BittrexID`,`UserID`,`ProfitPct`,`Live1HrChange`,`Name`,`Image`
FROM `WebOwnedCoinsStats` WHERE `UserID` = $userID ORDER BY `ProfitPct` Desc";
  $result = $conn->query($sql);
    //print_r($sql);
  //$result = mysqli_query($link4, $query);
//mysqli_fetch_assoc($result);`PctChange1Hr`, `PctChange24Hr`, `PctChange7D`
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['Symbol'],$row['LiveBuyOrders'],$row['LastBuyOrders'],$row['BuyOrdersPctChange'],$row['LiveMarketCap'],$row['LastMarketCap'],$row['MarketCapPctChange'],$row['Live1HrChange'],$row['Hr1ChangePctChange'],$row['Live24HrChange'],
      $row['Last24HrChange'],$row['Hr24ChangePctChange'],$row['Live7DChange'],$row['Last7DChange'],$row['D7ChangePctChange'],$row['LiveCoinPrice'],$row['LastCoinPrice'],$row['CoinPricePctChange'],$row['LiveSellOrders'],$row['LastSellOrders'],$row['SellOrdersPctChange'],
      $row['LiveVolume'],$row['LastVolume'],$row['VolumePctChange'],$row['BaseCurrency'],$row['Amount'],$row['OrderNo'],$row['CoinPrice'],$row['Profit'],$row['TransactionID'],$row['BittrexID'],$row['UserID'],$row['ProfitPct']
      ,$row['Live1HrChange'],$row['Name'],$row['Image']);
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


date_default_timezone_set('Asia/Dubai');
$date = date('Y/m/d H:i:s', time());


?>

<!--<div class="container">

	<div class="row">

	    <div class="col-xs-12 col-sm-8 col-md-8 col-sm-offset-2">-->


				<?php
        if ($_SESSION['isMobile']){ $num = 2; $fontSize = "font-size:60px"; }else{$num = 8;$fontSize = "font-size:32px"; }
        displayHeader(4);
        $trackingSell = getTrackingSellCoins($_SESSION['ID']);
        $arrLengthSell = count($trackingSell);
        $roundVar = $_SESSION['roundVar'];
        //$userConfig = getConfig($_SESSION['ID']);
        print_r("<h2>Sell Some Coins Now!</h2>");
        echo "<h3><a href='SellCoins.php'>Sell Coins</a> &nbsp > &nbsp <a href='SellCoins_Tracking.php'>Sell Coins Tracking</a></h3>";
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
            $fee = (($livePrice* $amount)/100)*0.28;
            $liveTotalCost = ($livePrice * $amount);
            $originalPurchaseCost = ($purchaseCost * $amount);
            $profit = ($liveTotalCost - $originalPurchaseCost - $fee);
            $profitBtc = $profit/($originalPurchaseCost)*100;
            $userID = $_SESSION['ID'];
            $name = $trackingSell[$x][50]; $image = $trackingSell[$x][51];
            echo "<table><td rowspan='3'><a href='Stats.php?coin=$coin'><img src='$image'></a></td>";
            echo "<td><p id='largeText' >$name</p></td>";
            echo "<td rowspan='2'><p id='largeText' >".round($livePrice,$roundVar)."</p></td>";
            NewEcho("<td><p id='normalText'>".round($mrktCap,$roundVar)."</p></td>",$_SESSION['isMobile'],0);
            NewEcho("<td><p id='normalText'>".round($pctChange1Hr,$roundVar)."</p></td>",$_SESSION['isMobile'],2);
            echo "<td><p id='largeText' >".round($amount,$roundVar)." $coin</p></td>";

            echo "<td rowspan='3'><a href='ManualSell.php?manSell=Yes&coin=$coin&amount=".$amount."&cost=$originalPurchaseCost&baseCurrency=$baseCurrency&orderNo=$orderNo&transactionID=$transactionID&salePrice=$livePrice'><i class='fas fa-shopping-cart' style='$fontSize;color:DodgerBlue'></i></a></td>";
            echo "<td rowspan='3'><a href='ManualSell.php?splitCoin=$coin&amount=".$amount."&cost=$originalPurchaseCost&baseCurrency=$baseCurrency&orderNo=$orderNo&transactionID=$transactionID&salePrice=$livePrice'><i class='fas fa-file-archive' style='$fontSize;color:DodgerBlue'></i></a></td>";
            echo "<td rowspan='3'><a href='ManualSell.php?trackCoin=Yes&baseCurrency=$baseCurrency&transactionID=$transactionID&salePrice=$livePrice&userID=$userID'><i class='fas fa-clock' style='$fontSize;color:DodgerBlue'></i></a></td>";

            echo "</tr><tr>";
            echo "<td><p id='normalText'>$coin</p></td>";
            NewEcho("<td><p id='normalText'>".round($volume,$roundVar)."</p></td>",$_SESSION['isMobile'],0);
            NewEcho("<td><p id='normalText'>".round($pctChange24Hr,$roundVar)."</p></td>",$_SESSION['isMobile'],2);
            $cost = round(number_format((float)$trackingSell[$x][4], 10, '.', ''),8);
            echo "<td><p id='normalText'>$cost</p></td>";

            echo "</tr><tr>";


            //$numCol = getNumberColour($profitBtc);
            //echo "<td><p id='smallText' style='color:$numCol'>".round($profitBtc,8)."</p></td>";

            $numCol = getNumberColour($priceDiff1);
            echo "<td><p id='smallText' style='color:$numCol'>".round($priceDiff1,$roundVar)."</p></td>";
            echo "<td><p id='largeText' >".round($profit,$roundVar)." $baseCurrency</p></td>";

            NewEcho("<td><p id='normalText'>".round($sellOrders,$roundVar)."</p></td>",$_SESSION['isMobile'],0);
            NewEcho("<td><p id='normalText'>".round($pctChange7D,$roundVar)."</p></td>",$_SESSION['isMobile'],0);
            $numCol = getNumberColour($profitBtc);
            echo "<td><p id='smallText' style='color:$numCol'>".round($profitBtc,$roundVar)."</p></td>";
        }
        print_r("</table>");
        Echo "<a href='SellCoins.php?noOverride=Yes'>View Mobile Page</a>".$_SESSION['MobOverride'];
				displaySideColumn();
//include header template
require('layout/footer.php');
$date = date('Y/m/d H:i:s', time());
echo " Last Updated :".$date;
?>
