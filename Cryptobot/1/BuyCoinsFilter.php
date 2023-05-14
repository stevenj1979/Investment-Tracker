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
$locationStr = "Location: /Investment-Tracker/Cryptobot/1/m/BuyCoins.php";
setStyle($_SESSION['isMobile']);

if (isset($_POST['filterSelect']) and $_POST['filterSelect'] <> ""){
  //if ($_POST['filterSelect'] <> ""){
    //echo "<BR> Test".$_POST['filterSelect'];
    $_SESSION['RuleIDSelected'] = $_POST['filterSelect'];
    showMain();

  //}
}else{
  if (!isset($_SESSION['RuleIDSelected'])){
    $userBuyRules = getBuyRules($_SESSION['ID']);
    $_SESSION['RuleIDSelected'] = $userBuyRules[0][35];
  }
  showMain();
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

function getNumberColourLoc($ColourText, $target){
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

function getBuyRules($userID){
$selectedRule = $_SESSION['RuleIDSelected'];
$conn = getSQLConn(rand(1,3));
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT
`BuyOrdersEnabled`,`BuyOrdersTop`,`BuyOrdersBtm`,`MarketCapEnabled`,`MarketCapTop`,`MarketCapBtm`,`1HrChangeEnabled`,`1HrChangeTop`,`1HrChangeBtm`,`24HrChangeEnabled`,`24HrChangeTop`,`24HrChangeBtm`,`7DChangeEnabled`,`7DChangeTop`,`7DChangeBtm`,`CoinPriceEnabled`
,`CoinPriceTop`,`CoinPriceBtm`,`SellOrdersEnabled`,`SellOrdersTop`,`SellOrdersBtm`,`VolumeEnabled`,`VolumeTop`,`VolumeBtm`,`BuyCoin`,`SendEmail`,`BTCAmount`,`Email`,`UserName`,`APIKey`,`APISecret`,`EnableDailyBTCLimit`,`DailyBTCLimit`,`EnableTotalBTCLimit`,`TotalBTCLimit`
,`RuleID`,`BuyCoinOffsetPct`,`BuyCoinOffsetEnabled`,`PriceTrendEnabled`,`Price4Trend`,`Price3Trend`,`LastPriceTrend`,`LivePriceTrend`,`Active`,`DisableUntil`,`BaseCurrency`,`NoOfCoinPurchase`,`TimetoCancelBuy`,`BuyType`,`TimeToCancelBuyMins`,`BuyPriceMinEnabled`
,`BuyPriceMin`,`LimitToCoin`,`AutoBuyCoinEnabled`,`AutoBuyPrice`,`LimitToCoinID`,`BuyAmountOverrideEnabled`,`BuyAmountOverride`,`NewBuyPattern`,`KEK`,`SellRuleFixed`,`OverrideDailyLimit`,`CoinOrder`,`CoinPricePatternEnabled`,`CoinPricePattern`,`1HrChangeTrendEnabled`,`1HrChangeTrend`
FROM `UserBuyRules` WHERE `UserID` = $userID and `RuleID` = $selectedRule";
$result = $conn->query($sql);
//echo $sql;
//$result = mysqli_query($link4, $query);
//mysqli_fetch_assoc($result);
while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['BuyOrdersEnabled'],$row['BuyOrdersTop'],$row['BuyOrdersBtm'],$row['MarketCapEnabled'],$row['MarketCapTop'],$row['MarketCapBtm'],$row['1HrChangeEnabled'],$row['1HrChangeTop'],$row['1HrChangeBtm'],$row['24HrChangeEnabled'],$row['24HrChangeTop'] //10
    ,$row['24HrChangeBtm'],$row['7DChangeEnabled'],$row['7DChangeTop'],$row['7DChangeBtm'],$row['CoinPriceEnabled'],$row['CoinPriceTop'],$row['CoinPriceBtm'],$row['SellOrdersEnabled'],$row['SellOrdersTop'],$row['SellOrdersBtm'],$row['VolumeEnabled'],$row['VolumeTop'] //22
    ,$row['VolumeBtm'],$row['BuyCoin'],$row['SendEmail'],$row['BTCAmount'],$row['Email'],$row['UserName'],$row['APIKey'],$row['APISecret'],$row['EnableDailyBTCLimit'],$row['DailyBTCLimit'],$row['EnableTotalBTCLimit'],$row['TotalBTCLimit'] //34
    ,$row['RuleID'],$row['BuyCoinOffsetPct'],$row['BuyCoinOffsetEnabled'],$row['PriceTrendEnabled'],$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['Active'],$row['DisableUntil'],$row['BaseCurrency'],$row['NoOfCoinPurchase'] //46
    ,$row['TimetoCancelBuy'],$row['BuyType'],$row['TimeToCancelBuyMins'],$row['BuyPriceMinEnabled'],$row['BuyPriceMin'],$row['LimitToCoin'],$row['AutoBuyCoinEnabled'],$row['AutoBuyPrice'],$row['LimitToCoinID'],$row['BuyAmountOverrideEnabled'],$row['BuyAmountOverride'] //57
    ,$row['NewBuyPattern'],$row['KEK'],$row['SellRuleFixed'],$row['OverrideDailyLimit'],$row['CoinOrder'],$row['CoinPricePatternEnabled'],$row['CoinPricePattern'],$row['1HrChangeTrendEnabled'],$row['1HrChangeTrend']);
}
$conn->close();
return $tempAry;
}



function displayRules($buyRulesAry){
  $selectedRule = $_SESSION['RuleIDSelected'];
  $buyRulesAryCount = count($buyRulesAry);
  for ($i=0; $i<$buyRulesAryCount; $i++){
    $ruleID = $buyRulesAry[$i][0];
    if ($selectedRule == $ruleID){
      echo "<Option selected='selected' value='$ruleID'>$ruleID</option>";
    }else{
      echo "<Option value='$ruleID'>$ruleID</option>";
    }
  }

}

function showMain(){
  displayHeader(3);

        if ($_SESSION['isMobile']){ $num = 2; $fontSize = "font-size:60px"; }else{$num = 8;$fontSize = "font-size:32px"; }
        $tracking = getTrackingCoins("WHERE `DoNotBuy` = 0 and `BuyCoin` = 1 ORDER BY `Symbol` ASC","FROM `View1_BuyCoins` ");
        $newArrLength = count($tracking);
        $buyRuleAry = getBuyRules($_SESSION['ID']);
        $autoBuyPrice = getAutoBuyPrices();
        $coinPricePatternList = getCoinPricePattenList();
        $coin1HrPatternList = getCoin1HrPattenList();
        $coinPriceMatch = getCoinPriceMatchList();
        //save Rules
        $Hr1ChangeEnabled = $buyRuleAry[0][6];$Hr1ChangeTop = $buyRuleAry[0][7]; $Hr1ChangeBtm = $buyRuleAry[0][8];
        $autoBuyCoinEnabled = $buyRuleAry[0][53]; $MarketCapTop = $buyRuleAry[0][4]; $MarketCapBtm = $buyRuleAry[0][5];$MarketCapEnabled = $buyRuleAry[0][3];
        $VolumeTop = $buyRuleAry[0][23]; $VolumeBtm = $buyRuleAry[0][22]; $VolumeEnabled = $buyRuleAry[0][21]; $BuyOrdersTop = $buyRuleAry[0][1];
        $BuyOrdersBtm = $buyRuleAry[0][2];$BuyOrdersEnabled = $buyRuleAry[0][0];
        $Hr24ChangeEnabled = $buyRuleAry[0][9];$Hr24ChangeTop = $buyRuleAry[0][10];$Hr24ChangeBtm = $buyRuleAry[0][11];
        $D7ChangeEnabled = $buyRuleAry[0][12];$D7ChangeTop = $buyRuleAry[0][13]; $D7ChangeBtm = $buyRuleAry[0][14];
        $livePriceTrend = $buyRuleAry[0][42];$lastPriceTrend = $buyRuleAry[0][41];$price3Trend = $buyRuleAry[0][40];$price4Trend = $buyRuleAry[0][39];
        //$newPriceTrend = $price4Trend.$price3Trend.$lastPriceTrend.$livePriceTrend;
        $priceTrendEnabled = $buyRuleAry[0][38]; $Hr1ChangeTrendEnabled = $buyRuleAry[0][65]; $coinPricePatternEnabled = $buyRuleAry[0][63];
        $ruleID = $buyRuleAry[0][35]; $limitToCoinID = $buyRuleAry[0][55]; $limitToCoin = $buyRuleAry[0][52];
        //print_r("<h2>Buy Some Coins Now!</h2><Table><th>&nbspCoin</th><TH>&nbspBase Currency</th><TH>&nbspPrice</th>");
        echo "<h3><a href='BuyCoins.php'>Buy Coins</a> &nbsp > &nbsp <a href='BuyCoinsFilter.php'>Buy Coins Filter</a> &nbsp > &nbsp <a href='BuyCoinsTracking.php'>Buy Coins Tracking</a>&nbsp > &nbsp <a href='BuyCoins_Spread.php'>Buy Coins Spread Bet</a>
        &nbsp > &nbsp <a href='BuyCoins_BuyBack.php'>Buy Back</a></h3>";
        //if($_SESSION['isMobile'] == False){
        $buyRulesIDAry = getBuyRulesIDs($_SESSION['ID']);
        Echo "<form action='BuyCoinsFilter.php?dropdown=Yes' method='post'><SELECT name='filterSelect'>";
        displayRules($buyRulesIDAry);
        Echo "</SELECT>";
        echo "<input type='submit' value='Update'/></form>";
          print_r("<Table><th>&nbspCoin</th><TH>&nbspBase Currency</th><TH>&nbspPrice</th>");
          NewEcho("<TH>&nbspMarket Cap %</th><TH>&nbspVolume by %</th><TH>&nbspBuy Orders %</th>",$_SESSION['isMobile'],0);
          echo "<TH>&nbsp% Change 1Hr</th>";
          NewEcho("<TH>&nbsp% Change 24 Hrs</th><TH>&nbsp% Change 7 Days</th>",$_SESSION['isMobile'],0);
        //}

        echo "<TH>&nbspPrice Diff 1</th><TH>&nbspPrice Change</th>";
        echo "<TH>&nbspBuy Pattern</th><TH>&nbsp1HR Change Pattern</th><TH>&nbspManual Buy</th><TH>&nbspSet Alert</th><tr>";
        //$roundNum = 2;
        //echo "<BR> NewArrLength : $newArrLength";
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
          $newPriceTrend = $price4Trend.$price3Trend.$lastPriceTrend.$LivePriceTrend;
          //TestRules

          $Hr1Test = buyWithScore($Hr1ChangeTop,$Hr1ChangeBtm,$tracking[$x][10],$Hr1ChangeEnabled);
          $priceTest = autoBuyMain($tracking[$x][17],$autoBuyPrice, $autoBuyCoinEnabled,$coinID);
          $marketCaptest = buyWithScore($MarketCapTop,$MarketCapBtm,$tracking[$x][7],$MarketCapEnabled);
          $volumetest = buyWithScore($VolumeTop,$VolumeBtm,$tracking[$x][25],$VolumeEnabled);
          $buyOrderstest = buyWithScore($BuyOrdersTop,$BuyOrdersBtm,$tracking[$x][4],$BuyOrdersEnabled);
          $Hr24test = buyWithScore($Hr24ChangeTop,$Hr24ChangeBtm,$tracking[$x][13],$Hr24ChangeEnabled);
          $D7test = buyWithScore($D7ChangeTop,$D7ChangeBtm,$tracking[$x][16],$D7ChangeEnabled);
          $priceTrendtest = newBuywithPattern($newPriceTrend,$coinPricePatternList,$priceTrendEnabled,$ruleID,0);
          $Hr1PriceTrendtest = newBuywithPattern($new1HrPriceChange,$coin1HrPatternList,$Hr1ChangeTrendEnabled,$ruleID,0);
          $coinMatchPatterntest = coinMatchPattern($coinPriceMatch,$tracking[$x][17],$coin,0,$coinPricePatternEnabled,$ruleID,0);
          //echo "<BR> TEST: buyWithScore($Hr1ChangeTop,$Hr1ChangeBtm,$Live1HrChange,$Hr1ChangeEnabled);$Hr1Test";
          //Table
          //echo "<BR> Limit to Coin : $coinID | $limitToCoinID | $limitToCoin";
          //if ($coinID <> $limitToCoinID and $limitToCoin <> "All"){ continue;}
          echo "<td><a href='Stats.php?coin=$coin'>$coin</a></td>";
          echo "<td>".$baseCurrency."</td>";
          if ($autoBuyCoinEnabled == False){
            $tdColour = setTextColour($coinMatchPatterntest, True);
          }else{
            $tdColour = setTextColour($priceTest, True);
          }
          echo "<td Style='$tdColour'>".$bitPrice."</td>";
          //if ($_SESSION['isMobile'] == False){
          $tdColour = setTextColour($marketCaptest, True);
            NewEcho("<td Style='$tdColour'>$MarketCap</td>",$_SESSION['isMobile'],0);
            $tdColour = setTextColour($volumetest, True);
            NewEcho( "<td Style='$tdColour'>$volume</td>",$_SESSION['isMobile'],0);
            $tdColour = setTextColour($buyOrderstest, True);
            NewEcho( "<td Style='$tdColour'>$buyOrders</td>",$_SESSION['isMobile'],0);
            $tdColour = setTextColourTarget($tracking[$x][10], False, $Hr1ChangeTop, $Hr1ChangeBtm);
            echo "<td Style='$tdColour'>".round($Live1HrChange,8)."</td>";
            $tdColour = setTextColourTarget($tracking[$x][13], False,$Hr24ChangeTop, $Hr24ChangeBtm);
            NewEcho( "<td Style='$tdColour'>".round($Live24HrChange,8)."</td>",$_SESSION['isMobile'],0);
            $tdColour = setTextColourTarget($tracking[$x][16], False,$D7ChangeTop, $D7ChangeBtm);
            NewEcho( "<td Style='$tdColour'>".round($Live7DChange,8)."</td>",$_SESSION['isMobile'],0);
        //  }
          echo "<td>".round($priceDiff1,8)."</td>";
          echo "<td>".round($priceChange,8)." ".$baseCurrency."</td>";

          //if ($_SESSION['isMobile'] == False){
            $tdColour = setTextColour($priceTrendtest, True);
            NewEcho("<td Style='$tdColour'>$newPriceTrend</td>",$_SESSION['isMobile'],2);
            $tdColour = setTextColour($Hr1PriceTrendtest, True);
            NewEcho("<td Style='$tdColour'>$new1HrPriceChange</td>",$_SESSION['isMobile'],2);
            NewEcho("<td><a href='ManualBuy.php?coin=$coin&baseCurrency=$baseCurrency&coinID=$coinID&coinPrice=$bitPrice'><i class='fas fa-shopping-cart' style='$fontSize;color:#D4EFDF'></i></a></td>",$_SESSION['isMobile'],2);
            NewEcho("<td><a href='CoinAlerts.php?alert=0&coinAlt=$coin&baseCurrency=$baseCurrency&coinID=$coinID&coinPrice=$bitPrice'><i class='fas fa-bell' style='$fontSize;color:#D4EFDF'></i></a></td>",$_SESSION['isMobile'],2);
        //  }else{
            //NewEcho("<td>".$price4Trend."".$price3Trend."".$lastPriceTrend."".$LivePriceTrend."</td>",$_SESSION['isMobile'],1);
          //  NewEcho("<td>$new1HrPriceChange</td>",$_SESSION['isMobile'],1);
            //NewEcho("<td><a href='ManualBuy.php?coin=$coin&baseCurrency=$baseCurrency&coinID=$coinID&coinPrice=$bitPrice'><i class='fas fa-shopping-cart' style='$fontSize;color:#D4EFDF'></i></a></td>",$_SESSION['isMobile'],1);
            //NewEcho("<td><a href='CoinAlerts.php?alert=0&coinAlt=$coin&baseCurrency=$baseCurrency&coinID=$coinID&coinPrice=$bitPrice'><i class='fas fa-bell' style='$fontSize;color:#D4EFDF'></i></a></td>",$_SESSION['isMobile'],1);
        //  }

          echo "<tr>";
        }//end for
        print_r("</table>");

        displaySideColumn();
        //displayMiddleColumn();
        //displayFarSideColumn();
        //displayFooter();
}


//include header template
require($_SERVER['DOCUMENT_ROOT'].'/Investment-Tracker/Cryptobot/1/layout/footer.php');
$date = date('Y/m/d H:i:s', time());
echo " Last Updated :".$date;
?>
