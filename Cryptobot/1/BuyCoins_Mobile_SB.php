<html>
<head>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
</head>
<?php require('includes/config.php');
include_once '../includes/newConfig.php';

//if not logged in redirect to login page
if(!$user->is_logged_in()){ header('Location: login.php'); exit(); }

//define page title
$title = 'CryptoBot';
$current_url = $_SERVER[ 'REQUEST_URI' ];
header( "Refresh: 120; URL=$current_url" );
//include header template
require($_SERVER['DOCUMENT_ROOT'].'/Investment-Tracker/Cryptobot/1/layout/header.php');
include_once ('../../../../SQLData.php');
$locationStr = "Location: /Investment-Tracker/Cryptobot/1/m/BuyCoins_Spread.php";
setStyle($_SESSION['isMobile']);

if(isset($_GET['override'])){
if ($_SESSION['MobOverride'] == False){$_SESSION['MobOverride'] = True;}
}

if(isset($_GET['noOverride'])){
if ($_SESSION['MobOverride'] == True){$_SESSION['MobOverride'] = False;}
}

//Echo "<BR> isMobile: ".$_SESSION['isMobile']." | MobOverride: ".$_SESSION['MobOverride'];

if ($_SESSION['isMobile'] && $_SESSION['MobOverride'] == False){
  //header('Location: BuyCoins_Mobile_SB.php');
}

function getCoinsfromSQL(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "SELECT `UserID`,`OrderNo`,`Symbol`,`Amount`,`Cost`,`TradeDate` FROM `Transaction` where `Status` = 'Open'";
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['UserID'],$row['OrderNo'],$row['Symbol'],$row['Amount'],$row['Cost'],$row['TradeDate']);
  }
  $conn->close();
  return $tempAry;
}

function getTrackingCoinsLoc($num){
$conn = getSQLConn(rand(1,3));
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
  $sql = "SELECT `ID`,'Symbol',`LiveBuyOrders`,`LastBuyOrders`,`BuyOrdersPctChange`,`LiveMarketCap`,`LastMarketCap`,`MarketCapPctChange`,`Live1HrChange`,`Last1HrChange`,`Hr1ChangePctChange`,`Live24HrChange`,`Last24HrChange`,`Hr24ChangePctChange`,`Live7DChange`,
   `Last7DChange`,`D7ChangePctChange`,`LiveCoinPrice`,`LastCoinPrice`,`CoinPricePctChange`,`LiveSellOrders`,`LastSellOrders`, `SellOrdersPctChange`,`LiveVolume`,`LastVolume`,`VolumePctChange`,`BaseCurrency`
   ,`Price4Trend`, `Price3Trend`, `LastPriceTrend`, `LivePriceTrend`,`Name`
   FROM `SpreadBetCoinStatsView_ALL`  WHERE `Enabled` = $num
   order by `CoinPricePctChange` asc,`Live1HrChange` asc";

   //echo $sql.getHost();
$result = $conn->query($sql);
while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['ID'],$row['Symbol'],$row['LiveBuyOrders'],$row['LastBuyOrders'],$row['BuyOrdersPctChange'],$row['LiveMarketCap'],$row['LastMarketCap'],$row['MarketCapPctChange'],$row['Live1HrChange'] //8
    ,$row['Last1HrChange'],$row['Hr1ChangePctChange'],$row['Live24HrChange'],$row['Last24HrChange'],$row['Hr24ChangePctChange'],$row['Live7DChange'],$row['Last7DChange'],$row['D7ChangePctChange'],$row['LiveCoinPrice'] //17
    ,$row['LastCoinPrice'],$row['CoinPricePctChange'],$row['LiveSellOrders'],$row['LastSellOrders'],$row['SellOrdersPctChange'],$row['LiveVolume'],$row['LastVolume'],$row['VolumePctChange'],$row['BaseCurrency'] //26
  ,$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['Name']);  //31
}
$conn->close();
return $tempAry;
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
$sql = "SELECT `MarketCapBuyPct`,`VolumeBuyPct`,`BuyOrdersPct`,`MinPctGain`  FROM `Config` WHERE `UserID` =  $userID";
$result = $conn->query($sql);
while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['MarketCapBuyPct'],$row['VolumeBuyPct'],$row['BuyOrdersPct'],$row['MinPctGain']);
}
$conn->close();
return $tempAry;
}

function sendEmailLoc($to, $symbol, $amount, $cost){
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

function getLiveCoinPrice($symbol){
  $limit = 500;
  $cnmkt = "https://api.coinmarketcap.com/v1/ticker/?limit=".$limit;
  $fgc = json_decode(file_get_contents($cnmkt), true);
for($i=0;$i<$limit;$i++){
  if ($fgc[$i]["symbol"] == $symbol){
    $tmpCoinPrice = $fgc[$i]["price_btc"];
  }
}
logAction("$cnmkt",'CMC');
return $tmpCoinPrice;
}

function bittrexCoinPriceLoc($apikey, $apisecret, $baseCoin, $coin){
    $nonce=time();
    $uri='https://bittrex.com/api/v1.1/public/getticker?market='.$baseCoin.'-'.$coin;
    $sign=hash_hmac('sha512',$uri,$apisecret);
    $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
    $balance = $obj["result"]["Last"];
    return $balance;
}

function getUserIDs($userID){
$conn = getSQLConn(rand(1,3));
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = " SELECT `ID`, `Username`, `email`, `api_key`, `api_secret` FROM `User` where `ID` = $userID";
$result = $conn->query($sql);
//$result = mysqli_query($link4, $query);
//mysqli_fetch_assoc($result);
while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['ID'],$row['Username'],$row['email'],$row['api_key'],$row['api_secret']);
}
$conn->close();
return $tempAry;
}

function displayMain($tracking, $title){
  $newArrLength = newCount($tracking);
  echo "<H3>$title</H3>";
  for($x = 0; $x < $newArrLength; $x++) {
    //Variables
    $coin = $tracking[$x][31]; $buyOrders = round($tracking[$x][4],$num); $MarketCap = round($tracking[$x][7],$num);
    $Live1HrChange = round($tracking[$x][10],$num); $Live24HrChange = round($tracking[$x][13],$num); $Live7DChange = $tracking[$x][16];
    $bitPrice = round($tracking[$x][17],$num); $LastCoinPrice = $tracking[$x][18];$coinID = $tracking[$x][0];
    $volume = round($tracking[$x][25],$num); $baseCurrency = $tracking[$x][26];
    $price4Trend = $tracking[$x][27];$price3Trend = $tracking[$x][28]; $lastPriceTrend = $tracking[$x][29]; $LivePriceTrend = $tracking[$x][30];
    $priceChange = round(number_format((float)$bitPrice-$LastCoinPrice, 8, '.', ''),$num);
    $priceDiff1 = round(number_format((float)$tracking[$x][19], 2, '.', ''),$num);
    //$Hr1LivePriceChange = $tracking[$x][30];$Hr1LastPriceChange = $tracking[$x][29]; $Hr1PriceChange3 = $tracking[$x][28];$Hr1PriceChange4 = $tracking[$x][27];
    //$new1HrPriceChange = $Hr1PriceChange4.$Hr1PriceChange3.$Hr1LastPriceChange.$Hr1LivePriceChange;
    $name = $tracking[$x][37]; $image = $tracking[$x][38];
    //Table
    echo "<table id='t01'><td rowspan='3'><a href='Stats.php?coin=$coin'>$coin</a></td>"; //change
    //echo "<td><p id='smallText'>".$coin."</p></td>";
    echo "<td><p id='largeText'>".$bitPrice."</p></td>";
    NewEcho("<td><p id='normalText'>Market Cap: $MarketCap</p></td>",$_SESSION['isMobile'],0);

    $tdColour = setTextColour($Live1HrChange, False);
    echo "<td><p id='normalText'>1H: ".round($Live1HrChange,2)."</p></td>";



    NewEcho("<td rowspan='3'><p id='normalText'>".$price4Trend." ".$price3Trend." ".$lastPriceTrend." ".$LivePriceTrend."</p></td>",$_SESSION['isMobile'],0);
    //NewEcho("<td rowspan='3'><p id='normalText'>$new1HrPriceChange</p></td>",$_SESSION['isMobile'],2);

    NewEcho("<td><a href='ManualBuy.php?buy=Yes&coin=$coin&baseCurrency=$baseCurrency&coinID=$coinID&coinPrice=$bitPrice'><i class='fas fa-shopping-cart' style='$fontSize;color:#D4EFDF'></i></a></td>",$_SESSION['isMobile'],2);
    NewEcho("<td><a href='CoinAlerts.php?alert=0&coinAlt=$coin&baseCurrency=$baseCurrency&coinID=$coinID&coinPrice=$bitPrice'><i class='fas fa-bell' style='$fontSize;color:#D4EFDF'></i></a></td>",$_SESSION['isMobile'],2);
    NewEcho("<td><a href='ManualBuy.php?track=Yes&coin=$coin&baseCurrency=$baseCurrency&coinID=$coinID&coinPrice=$bitPrice'><i class='fas fa-clock' style='$fontSize;color:#D4EFDF'></i></a></td>",$_SESSION['isMobile'],2);
    echo "</tr><tr>";
    echo "<td><p id='smallText' style='color:$numCol'>$priceDiff1 %</p></td>";
    NewEcho( "<td><p id='normalText'>Volume: $volume</p></td>",$_SESSION['isMobile'],0);
    NewEcho( "<td><p id='normalText'>24H: ".round($Live24HrChange,2)."</p></td>",$_SESSION['isMobile'],2);
    echo "<td></td><td></td><td></td>";
    echo "</tr><tr>";
    echo "<td><p id='normalText'>".$priceChange."</p></td>";
    $numCol = getNumberColour($priceDiff1);


    NewEcho( "<td><p id='normalText'>Buy Orders: $buyOrders</p></td>",$_SESSION['isMobile'],0);
    NewEcho( "<td><p id='normalText'>7D : ".round($Live7DChange,2)."</p></td>",$_SESSION['isMobile'],2);
    NewEcho("<td><p id='normalText'>".$baseCurrency."</p></td>",$_SESSION['isMobile'],0);
    echo "<td></td><td></td><td></td>";
  }//end for
  print_r("</tr></table><BR>");
}

displayHeader(3);

      if ($_SESSION['isMobile']){ $num = 2; $fontSize = "font-size:60px"; }else{$num = 8;$fontSize = "font-size:32px"; }
      $trackingEnable = getTrackingCoinsLoc(1);
      $trackingDisable = getTrackingCoinsLoc(0);
      //echo $newArrLength;
      //$userConfig = getConfig($_SESSION['ID']);
      //$user = getUserIDs($_SESSION['ID']);
      //print_r("<HTML><Table><th>Coin</th><th>BuyPattern</th><th>MarketCapHigherThan5Pct</th><th>VolumeHigherThan5Pct</th><th>BuyOrdersHigherThan5Pct</th><th>PctChange</th><tr>");

      echo "<h3><a href='BuyCoins.php'>Buy Coins</a> &nbsp > &nbsp <a href='BuyCoinsFilter.php'>Buy Coins Filter</a> &nbsp > &nbsp <a href='BuyCoinsTracking.php'>Buy Coins Tracking</a> &nbsp > &nbsp <a href='BuyCoins_Spread.php'>Buy Coins SpreadBet</a>
       &nbsp > &nbsp <a href='BuyCoins_BuyBack.php'>Buy Back</a></h3>";
      //if($_SESSION['isMobile'] == False){
      //print_r("<Table><th>&nbspCoin</th><TH>&nbspBase Currency</th><TH>&nbspPrice</th>");
      //  NewEcho("<TH>&nbspMarket Cap %</th><TH>&nbspVolume by %</th><TH>&nbspBuy Orders %</th>",$_SESSION['isMobile'],0);
      //  echo "<TH>&nbsp% Change 1Hr</th>";
      //  NewEcho("<TH>&nbsp% Change 24 Hrs</th><TH>&nbsp% Change 7 Days</th>",$_SESSION['isMobile'],0);
      //}

      //echo "<TH>&nbspPrice Diff 1</th><TH>&nbspPrice Change</th>";
      //echo "<TH>&nbspBuy Pattern</th><TH>&nbsp1HR Change Pattern</th><TH>&nbspManual Buy</th><TH>&nbspSet Alert</th><tr>";
      //$roundNum = 2;
      displayMain($trackingEnable, "Enabled");
      displayMain($trackingDisable, "Disabled");
      Echo "<a href='BuyCoins.php?noOverride=Yes'>View Mobile Page</a>".$_SESSION['MobOverride'];
      displaySideColumn();
      //displayMiddleColumn();
      //displayFarSideColumn();
      //displayFooter();

//include header template
require($_SERVER['DOCUMENT_ROOT'].'/Investment-Tracker/Cryptobot/1/layout/footer.php');
$date = date('Y/m/d H:i:s', time());
echo " Last Updated :".$date;
?>
