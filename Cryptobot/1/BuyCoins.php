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
include_once ('/home/stevenj1979/SQLData.php');
$locationStr = "Location: /Investment-Tracker/Cryptobot/1/m/BuyCoins.php";
setStyle($_SESSION['isMobile']);

if ($_SESSION['isMobile']){
  header('Location: BuyCoins_Mobile.php');
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
    $sql = "SELECT `ID`,`Symbol`,`LiveBuyOrders`,`LastBuyOrders`,`BuyOrdersPctChange`,`LiveMarketCap`,`LastMarketCap`,`MarketCapPctChange`,`Live1HrChange`,`Last1HrChange`,`Hr1ChangePctChange`,`Live24HrChange`,`Last24HrChange`,`Hr24ChangePctChange`,`Live7DChange`,
     `Last7DChange`,`D7ChangePctChange`,`LiveCoinPrice`,`LastCoinPrice`,`CoinPricePctChange`,`LiveSellOrders`,`LastSellOrders`, `SellOrdersPctChange`,`LiveVolume`,`LastVolume`,`VolumePctChange`,`BaseCurrency`
     ,`Price4Trend`, `Price3Trend`, `LastPriceTrend`, `LivePriceTrend`
     FROM `CoinStatsView` order by `CoinPricePctChange` asc,`Live1HrChange` asc";

     //echo $sql.getHost();
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['Symbol'],$row['LiveBuyOrders'],$row['LastBuyOrders'],$row['BuyOrdersPctChange'],$row['LiveMarketCap'],$row['LastMarketCap'],$row['MarketCapPctChange'],$row['Live1HrChange']
      ,$row['Last1HrChange'],$row['Hr1ChangePctChange'],$row['Live24HrChange'],$row['Last24HrChange'],$row['Hr24ChangePctChange'],$row['Live7DChange'],$row['Last7DChange'],$row['D7ChangePctChange'],$row['LiveCoinPrice']
      ,$row['LastCoinPrice'],$row['CoinPricePctChange'],$row['LiveSellOrders'],$row['LastSellOrders'],$row['SellOrdersPctChange'],$row['LiveVolume'],$row['LastVolume'],$row['VolumePctChange'],$row['BaseCurrency']
    ,$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend']);
  }
  $conn->close();
  return $tempAry;
}

function getNumberColour($ColourText, $target){
  if ($ColourText >= 0){
    $colour = "#D4EFDF";
  }elseif ($ColourText == 0) {
    $colour = "#FCF3CF";
  }else{
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

displayHeader(3);

        if ($_SESSION['isMobile']){ $num = 2; $fontSize = "font-size:60px"; }else{$num = 8;$fontSize = "font-size:32px"; }
				$tracking = getTrackingCoins();
				$newArrLength = count($tracking);
        //echo $newArrLength;
        //$userConfig = getConfig($_SESSION['ID']);
        //$user = getUserIDs($_SESSION['ID']);
				//print_r("<HTML><Table><th>Coin</th><th>BuyPattern</th><th>MarketCapHigherThan5Pct</th><th>VolumeHigherThan5Pct</th><th>BuyOrdersHigherThan5Pct</th><th>PctChange</th><tr>");

        echo "<h3><a href='BuyCoins.php'>Buy Coins</a> &nbsp > &nbsp <a href='BuyCoinsFilter.php'>Buy Coins Filter</a> &nbsp > &nbsp <a href='BuyCoinsTracking.php'>Buy Coins Tracking</a></h3>";
        //if($_SESSION['isMobile'] == False){
        print_r("<Table><th>&nbspCoin</th><TH>&nbspBase Currency</th><TH>&nbspPrice</th>");
          NewEcho("<TH>&nbspMarket Cap %</th><TH>&nbspVolume by %</th><TH>&nbspBuy Orders %</th>",$_SESSION['isMobile'],0);
          echo "<TH>&nbsp% Change 1Hr</th>";
          NewEcho("<TH>&nbsp% Change 24 Hrs</th><TH>&nbsp% Change 7 Days</th>",$_SESSION['isMobile'],0);
        //}

        echo "<TH>&nbspPrice Diff 1</th><TH>&nbspPrice Change</th>";
        echo "<TH>&nbspBuy Pattern</th><TH>&nbsp1HR Change Pattern</th><TH>&nbspManual Buy</th><TH>&nbspSet Alert</th><tr>";
        //$roundNum = 2;
				for($x = 0; $x < $newArrLength; $x++) {
          //Variables
          $coin = $tracking[$x][1]; $buyOrders = round($tracking[$x][4],$num); $MarketCap = round($tracking[$x][7],$num);
          $Live1HrChange = round($tracking[$x][8],$num); $Live24HrChange = round($tracking[$x][11],$num); $Live7DChange = $tracking[$x][14];
          $bitPrice = round($tracking[$x][17],$num); $LastCoinPrice = $tracking[$x][18];$coinID = $tracking[$x][0];
          $volume = round($tracking[$x][25],$num); $baseCurrency = $tracking[$x][26];
          $price4Trend = $tracking[$x][27];$price3Trend = $tracking[$x][28]; $lastPriceTrend = $tracking[$x][29]; $LivePriceTrend = $tracking[$x][30];
          $priceChange = round(number_format((float)$bitPrice-$LastCoinPrice, 8, '.', ''),$num);
          $priceDiff1 = round(number_format((float)$tracking[$x][19], 2, '.', ''),$num);
          $Hr1LivePriceChange = $tracking[$x][31];$Hr1LastPriceChange = $tracking[$x][32]; $Hr1PriceChange3 = $tracking[$x][33];$Hr1PriceChange4 = $tracking[$x][34];
          $new1HrPriceChange = $Hr1PriceChange4.$Hr1PriceChange3.$Hr1LastPriceChange.$Hr1LivePriceChange;
          //Table
          echo "<td><a href='Stats.php?coin=$coin'>$coin</a></td>";
          echo "<td>".$baseCurrency."</td>";
          echo "<td>".$bitPrice."</td>";
          //if ($_SESSION['isMobile'] == False){
            NewEcho("<td>$MarketCap</td>",$_SESSION['isMobile'],0);
            NewEcho( "<td>$volume</td>",$_SESSION['isMobile'],0);
            NewEcho( "<td>$buyOrders</td>",$_SESSION['isMobile'],0);
            $tdColour = setTextColour($Live1HrChange, False);
            echo "<td Style='$tdColour'>".$Live1HrChange."</td>";
            NewEcho( "<td>".$Live24HrChange."</td>",$_SESSION['isMobile'],0);
            NewEcho( "<td>".$Live7DChange."</td>",$_SESSION['isMobile'],0);
        //  }
          echo "<td>% $priceDiff1</td>";
          echo "<td>".$priceChange." ".$baseCurrency."</td>";

          //if ($_SESSION['isMobile'] == False){
            NewEcho("<td>".$price4Trend." ".$price3Trend." ".$lastPriceTrend." ".$LivePriceTrend."</td>",$_SESSION['isMobile'],0);

            NewEcho("<td>$new1HrPriceChange</td>",$_SESSION['isMobile'],0);
            NewEcho("<td><a href='ManualBuy.php?coin=$coin&baseCurrency=$baseCurrency&coinID=$coinID&coinPrice=$bitPrice'><i class='fas fa-shopping-cart' style='$fontSize;color:#D4EFDF'></i></a></td>",$_SESSION['isMobile'],0);
            NewEcho("<td><a href='CoinAlerts.php?alert=0&coinAlt=$coin&baseCurrency=$baseCurrency&coinID=$coinID&coinPrice=$bitPrice'><i class='fas fa-bell' style='$fontSize;color:#D4EFDF'></i></a></td>",$_SESSION['isMobile'],0);
        //  }else{
            NewEcho("<td>".$price4Trend."".$price3Trend."".$lastPriceTrend."".$LivePriceTrend."</td>",$_SESSION['isMobile'],1);
            NewEcho("<td>$new1HrPriceChange</td>",$_SESSION['isMobile'],1);
            NewEcho("<td><a href='ManualBuy.php?coin=$coin&baseCurrency=$baseCurrency&coinID=$coinID&coinPrice=$bitPrice'><i class='fas fa-shopping-cart' style='$fontSize;color:#D4EFDF'></i></a></td>",$_SESSION['isMobile'],1);
            NewEcho("<td><a href='CoinAlerts.php?alert=0&coinAlt=$coin&baseCurrency=$baseCurrency&coinID=$coinID&coinPrice=$bitPrice'><i class='fas fa-bell' style='$fontSize;color:#D4EFDF'></i></a></td>",$_SESSION['isMobile'],1);
        //  }

          echo "<tr>";
				}//end for
				print_r("</table>");

        displaySideColumn();
        //displayMiddleColumn();
				//displayFarSideColumn();
        //displayFooter();

//include header template
require($_SERVER['DOCUMENT_ROOT'].'/Investment-Tracker/Cryptobot/1/layout/footer.php');
$date = date('Y/m/d H:i:s', time());
echo " Last Updated :".$date;
?>
