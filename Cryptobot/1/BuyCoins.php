  <html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
</head>
<?php require('includes/config.php');
include_once '../includes/newConfig.php';
include_once '/home/stevenj1979/public_html/Investment-Tracker/Cryptobot/1/Display HTML/html.php';

//if not logged in redirect to login page
if(!$user->is_logged_in()){ header('Location: login.php'); exit(); }

//define page title
$title = 'CryptoBot';
$current_url = $_SERVER[ 'REQUEST_URI' ];
header( "Refresh: 120; URL=$current_url" );
//include header template
require($_SERVER['DOCUMENT_ROOT'].'/Investment-Tracker/Cryptobot/1/layout/header.php');
include_once ('../../../../SQLData.php');
$locationStr = "Location: /Investment-Tracker/Cryptobot/1/m/BuyCoins.php";
setStyle($_SESSION['isMobile']);

if(isset($_GET['override'])){
  if ($_SESSION['MobOverride'] == False){$_SESSION['MobOverride'] = True;}
}

if(isset($_GET['noOverride'])){
  if ($_SESSION['MobOverride'] == True){$_SESSION['MobOverride'] = False;}
}

//Echo "<BR> isMobile: ".$_SESSION['isMobile']." | MobOverride: ".$_SESSION['MobOverride'];

if ($_SESSION['isMobile'] && $_SESSION['MobOverride'] == False){
  //header('Location: BuyCoins_Mobile.php');
}

if($_POST['BaseArray'] <> ""){
  if ($_POST['BaseArray']=='All'){
     $_SESSION['BaseSelected'] = "All";
     //$dropArray[] = Array("Open","Sold","All");
  }elseif ($_POST['BaseArray']=='USDT'){
    $_SESSION['BaseSelected'] = "USDT";
    //$dropArray[] = Array("Sold","Open","All");
  }elseif ($_POST['BaseArray']=='BTC'){
    $_SESSION['BaseSelected'] = "BTC";
    //$dropArray[] = Array("Sold","Open","All");
  }elseif ($_POST['BaseArray']=='ETH'){
    $_SESSION['BaseSelected'] = "ETH";
    //$dropArray[] = Array("All","Open","Sold");
  }
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

function getTrackingCoinsLoc(){

  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
    $sql = "SELECT `IDTr`,`Symbol`,`LiveBuyOrders`,`LastBuyOrders`,`BuyOrdersPctChange`,`LiveMarketCap`,`LastMarketCap`,`MarketCapPctChange`,`Live1HrChange`,`Last1HrChange`,`Hr1ChangePctChange`,`Live24HrChange`,`Last24HrChange`,`Hr24ChangePctChange`,`Live7DChange`,
     `Last7DChange`,`D7ChangePctChange`,`LiveCoinPrice`,`LastCoinPrice`,`CoinPricePctChange`,`LiveSellOrders`,`LastSellOrders`, `SellOrdersPctChange`,`LiveVolume`,`LastVolume`,`VolumePctChange`,`BaseCurrency`
     ,`Price4Trend`, `Price3Trend`, `LastPriceTrend`, `LivePriceTrend`
     FROM `View5_SellCoins`
     Where `Status` = 'Open' and `SpreadBetTransactionID` = 0
     order by `CoinPricePctChange` asc,`Live1HrChange` asc ";
     echo "<br> $sql";
     //echo $sql.getHost();
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['IDTr'],$row['Symbol'],$row['LiveBuyOrders'],$row['LastBuyOrders'],$row['BuyOrdersPctChange'],$row['LiveMarketCap'],$row['LastMarketCap'],$row['MarketCapPctChange'],$row['Live1HrChange']
      ,$row['Last1HrChange'],$row['Hr1ChangePctChange'],$row['Live24HrChange'],$row['Last24HrChange'],$row['Hr24ChangePctChange'],$row['Live7DChange'],$row['Last7DChange'],$row['D7ChangePctChange'],$row['LiveCoinPrice']
      ,$row['LastCoinPrice'],$row['CoinPricePctChange'],$row['LiveSellOrders'],$row['LastSellOrders'],$row['SellOrdersPctChange'],$row['LiveVolume'],$row['LastVolume'],$row['VolumePctChange'],$row['BaseCurrency']
    ,$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend']);
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

function displayBuyCoin($tracking, $title){
  if ($_SESSION['isMobile']){ $num = 2; $fontSize = "font-size:60px"; }else{$num = 8;$fontSize = "font-size:32px"; }
  $newArrLength = count($tracking);
  echo "<h2>$title</h2>";
  for($x = 0; $x < $newArrLength; $x++) {
    //Variables
    $coin = $tracking[$x][1]; $buyOrders = round($tracking[$x][4],$num); $MarketCap = round($tracking[$x][7],$num);
    $Live1HrChange = round($tracking[$x][10],$num); $Live24HrChange = round($tracking[$x][13],$num); $Live7DChange = $tracking[$x][16];
    $bitPrice = round($tracking[$x][17],$num); $LastCoinPrice = $tracking[$x][18];$coinID = $tracking[$x][0];
    $volume = round($tracking[$x][25],$num); $baseCurrency = $tracking[$x][26];
    $price4Trend = $tracking[$x][27];$price3Trend = $tracking[$x][28]; $lastPriceTrend = $tracking[$x][29]; $LivePriceTrend = $tracking[$x][30];
    $priceChange = round(number_format((float)$bitPrice-$LastCoinPrice, 8, '.', ''),$num);
    $priceDiff1 = round(number_format((float)$tracking[$x][19], 2, '.', ''),$num);
    $Hr1LivePriceChange = $tracking[$x][31];$Hr1LastPriceChange = $tracking[$x][32]; $Hr1PriceChange3 = $tracking[$x][33];$Hr1PriceChange4 = $tracking[$x][34];
    $new1HrPriceChange = $Hr1PriceChange4.$Hr1PriceChange3.$Hr1LastPriceChange.$Hr1LivePriceChange;
    $name = $tracking[$x][37]; $image = $tracking[$x][38];
    $hoursFlat = $tracking[$x][40]; $month6Low = $tracking[$x][41];$month3Low = $tracking[$x][42]; $avgLow = $tracking[$x][43];
    //Table

    echo "<table id='t01'><td rowspan='3'><a href='Stats.php?coin=$coinID'><img src='$image' width='64' height='64'></img></a></td>";
    echo "<td><p id='largeText'>".$name."</p></td>";
    echo "<td rowspan='3'><p id='largeText'>".$bitPrice."</p></td>";
    NewEcho("<td><p id='normalText'>Market Cap: $MarketCap</p></td>",$_SESSION['isMobile'],2);

    $tdColour = setTextColour($Live1HrChange, False);
    echo "<td><p id='normalText'> 1Hr Change: ".round($Live1HrChange,2)."</p></td>";

    echo "<td rowspan='2'><p id='normalText'>".$priceChange." ".$baseCurrency."</p></td>";

    NewEcho("<td rowspan='3'><p id='normalText'>".$price4Trend." ".$price3Trend." ".$lastPriceTrend." ".$LivePriceTrend."</p></td>",$_SESSION['isMobile'],2);
    NewEcho("<td rowspan='3'><p id='normalText'>$new1HrPriceChange</p></td>",$_SESSION['isMobile'],2);
    newEcho("<td rowspan='3'><p id='normalText'>$hoursFlat</p></td>",$_SESSION['isMobile'],2);
    newEcho("<td rowspan='3'><p id='normalText'>$avgLow</p></td>",$_SESSION['isMobile'],2);
    NewEcho("<td rowspan='3'><div title='Manual Buy!'><a href='ManualBuy.php?buy=Yes&coin=$coin&baseCurrency=$baseCurrency&coinID=$coinID&coinPrice=$bitPrice'><i class='fas fa-shopping-cart' style='$fontSize;color:#D4EFDF'></i></a></DIV></td>",$_SESSION['isMobile'],2);
    NewEcho("<td rowspan='3'><div title='Set Coin Alert!'><a href='CoinAlerts.php?alert=0&coinAlt=$coin&baseCurrency=$baseCurrency&coinID=$coinID&coinPrice=$bitPrice'><i class='fas fa-bell' style='$fontSize;color:#D4EFDF'></i></a></DIV></td>",$_SESSION['isMobile'],2);
    NewEcho("<td rowspan='3'><div title='Manual Buy with Tracking!'><a href='ManualBuy.php?track=Yes&coin=$coin&baseCurrency=$baseCurrency&coinID=$coinID&coinPrice=$bitPrice'><i class='fas fa-clock' style='$fontSize;color:#D4EFDF'></i></a></DIV></td>",$_SESSION['isMobile'],2);
    echo "</tr><tr>";
    echo "<td><p id='smallText'>".$coin."</p></td>";
    NewEcho( "<td><p id='normalText'>Volume: $volume</p></td>",$_SESSION['isMobile'],2);
    NewEcho( "<td><p id='normalText'>24 Hr Change: ".round($Live24HrChange,2)."</p></td>",$_SESSION['isMobile'],2);

    echo "</tr><tr>";
    $numCol = getNumberColour($priceDiff1);
    echo "<td><p id='smallText' style='color:$numCol'>$priceDiff1 %</p></td>";
    NewEcho( "<td><p id='normalText'>Buy Orders: $buyOrders</p></td>",$_SESSION['isMobile'],2);
    NewEcho( "<td><p id='normalText'>7 Day Change: ".round($Live7DChange,2)."</p></td>",$_SESSION['isMobile'],2);
    echo "<td><p id='normalText'>".$baseCurrency."</p></td>";
  }//end for
  print_r("</tr></table>");
}

displayHeader(3);


        $baseSelection = "";

        if (!isset($_SESSION['BaseSelected'])){
          $_SESSION['BaseSelected'] = 'All';
        }elseif ($_SESSION['BaseSelected'] != "All"){
          $baseSelection = " and `BaseCurrency` = '".$_SESSION['BaseSelected']."'";
        }
				$tracking = getTrackingCoins("WHERE `DoNotBuy` = 0 and `BuyCoin` = 1 $baseSelection ORDER BY `Symbol` ASC","FROM `View1_BuyCoins` ");

        //echo $newArrLength;
        //$userConfig = getConfig($_SESSION['ID']);
        //$user = getUserIDs($_SESSION['ID']);
				//print_r("<HTML><Table><th>Coin</th><th>BuyPattern</th><th>MarketCapHigherThan5Pct</th><th>VolumeHigherThan5Pct</th><th>BuyOrdersHigherThan5Pct</th><th>PctChange</th><tr>");
        displaySubHeader("BuyCoin");
        $baseArr = ['All','USDT','BTC','ETH'];
        echo "<form action='BuyCoins.php?dropdown=Yes' method='post'>";
        displayDropDown($baseArr,$_SESSION['BaseSelected'],0,0, 'BaseArray');
        echo "<input type='submit' name='submit' value='Update' class='settingsformsubmit' tabindex='36'></form>";
        //if($_SESSION['isMobile'] == False){
        //print_r("<Table><th>&nbspCoin</th><TH>&nbspBase Currency</th><TH>&nbspPrice</th>");
        //  NewEcho("<TH>&nbspMarket Cap %</th><TH>&nbspVolume by %</th><TH>&nbspBuy Orders %</th>",$_SESSION['isMobile'],0);
        //  echo "<TH>&nbsp% Change 1Hr</th>";
        //  NewEcho("<TH>&nbsp% Change 24 Hrs</th><TH>&nbsp% Change 7 Days</th>",$_SESSION['isMobile'],0);
        //}

        //echo "<TH>&nbspPrice Diff 1</th><TH>&nbspPrice Change</th>";
        //echo "<TH>&nbspBuy Pattern</th><TH>&nbsp1HR Change Pattern</th><TH>&nbspManual Buy</th><TH>&nbspSet Alert</th><tr>";
        //$roundNum = 2;
        //echo "<TH></TH>";
        displayBuyCoin($tracking, 'Enabled');
        $tracking = getTrackingCoins("WHERE `DoNotBuy` = 1 and `BuyCoin` = 1 $baseSelection ORDER BY `Symbol` ASC","FROM `View1_BuyCoins` ");
        displayBuyCoin($tracking, 'Disabled');
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
