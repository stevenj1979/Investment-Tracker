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


if(isset($_GET['Mode'])){
  if ($_GET['Mode'] == 1){
    //Sell SpreadBetCoins
    $ID = $_GET['SBTransID'];
    echo "<BR>SpreadBet Transaction ID: $ID";
    $spreadSellCoins = getSpreadCoinSellData($ID);
    $spreadSellCoinsSize = count($spreadSellCoins);
    for ($r=0; $r<$spreadSellCoinsSize; $r++){
      echo "<BR>sellSpreadBetCoins($spreadSellCoins);";
      sellSpreadBetCoins($spreadSellCoins);
    }
  }
}

if(isset($_GET['override'])){
  if ($_SESSION['MobOverride'] == False){$_SESSION['MobOverride'] = True;$_SESSION['roundVar'] = 8;}

}

if(isset($_GET['noOverride'])){
  if ($_SESSION['MobOverride'] == True){$_SESSION['MobOverride'] = False;$_SESSION['roundVar'] = 2;}

}

if ($_SESSION['isMobile'] && $_SESSION['MobOverride'] == False){
  $_SESSION['roundVar'] = 2;
  //header('Location: SellCoins_Mobile_SB.php');
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

function getTotalProfitSpreadBetSellLoc($spreadBetTransactionID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT ifNull(sum(`OriginalPurchasePrice`),0) as OriginalPurchasePrice ,ifNull(sum(`LiveTotalPrice`),0) as LiveTotalPrice,ifNull(sum(`SaleTotalPrice`),0) as SaleTotalPrice, Sum(`ProfitUSD`) as ProfitUSD
            FROM `View28_SpreadBetTotalProfitView`
            where `SpreadBetTransactionID` = $spreadBetTransactionID ";

  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['OriginalPurchasePrice'],$row['LiveTotalPrice'],$row['SaleTotalPrice'],$row['ProfitUSD']);
      //13  14  15

  }
  $conn->close();
  return $tempAry;
}

function getTrackingSellCoinsLoc($userID, $comma_separated){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  var_dump($comma_separated);
  $idList = $comma_separated;
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "SELECT `IDTr` as `ID`,`Type`,'CoinID',`UserID`,sum(`CoinPrice`) as `CoinPrice`, sum(`Amount`) as `Amount`,`Status`,`OrderDate`,`CompletionDate`,'BittrexID','OrderNo','Symbol',sum(`LastBuyOrders`) as `LastBuyOrders`
    , sum(`LiveBuyOrders`) as `LiveBuyOrders`,((sum(`LiveBuyOrders`-`LastBuyOrders`))/sum(`LastBuyOrders`))*100 as `BuyOrdersPctChange`, sum(`LastMarketCap`) as `LastMarketCap` , sum(`LiveMarketCap`) as `LiveMarketCap`
  ,((sum(`LiveMarketCap`-`LastMarketCap`))/sum(`LastMarketCap`))*100 as `MarketCapPctChange`, sum(`LastCoinPrice`) as `LastCoinPrice `, sum(`LiveCoinPrice`) as `LiveCoinPrice `
  , ((sum(`LiveCoinPrice`-`LastCoinPrice`))/sum(`LastCoinPrice`))*100 as `CoinPricePctChange`, sum(`LastSellOrders`) as `LastSellOrders `, sum(`LiveSellOrders`) as `LiveSellOrders`
  ,((sum(`LiveSellOrders`-`LastSellOrders`))/sum(`LastSellOrders`))*100 as `SellOrdersPctChange`, sum(`LastVolume`) as `LastVolume `, sum(`LiveVolume`) as `LiveVolume `
  , ((sum(`LiveVolume`-`LastVolume`))/sum(`LastVolume`))*100 as `VolumePctChange`, sum(`Last1HrChange`) as `Last1HrChange `, sum(`Live1HrChange`) as `Live1HrChange `, ((sum(`Live1HrChange`-`Last1HrChange`))/sum(`Last1HrChange`))*100 as `Hr1PctChange`
  , sum(`Last24HrChange`) as `Last24HrChange `, sum(`Live24HrChange`) as `Live24HrChange `, ((sum(`Live24HrChange`-`Last24HrChange`))/sum(`Last24HrChange`))*100 as `Hr24PctChange`, sum(`Last7DChange`) as `Last7DChange `
  , sum(`Live7DChange`) as `Live7DChange `, ((sum(`Live7DChange`-`Last7DChange`))/sum(`Last7DChange`))*100 as `D7PctChange`,`BaseCurrency`
    , if(sum(`Price4`-`Price5`) > 0, 1, if(sum(`Price4` -`Price5`) < 0, -1, 0)) as  `Price4Trend`
    ,if(sum(`Price3` -`Price4`) > 0, 1, if(sum(`Price3` -`Price4`) < 0, -1, 0)) as `Price3Trend`
    ,if(sum(`LastCoinPrice` -`Price3`) > 0, 1, if(sum(`LastCoinPrice`-`Price3`) < 0, -1, 0)) as  `LastPriceTrend`
    ,if(sum(`LiveCoinPrice` -`LastCoinPrice`) > 0, 1, if(sum(`LiveCoinPrice`-`LastCoinPrice`) < 0, -1, 0)) as  `LivePriceTrend`,`FixSellRule`,`SellRule`,`BuyRule`,`ToMerge`,`LowPricePurchaseEnabled`,`DailyBTCLimit`
    ,`PctToPurchase`,`BTCBuyAmount`,`NoOfPurchases`,`Name`,`Image`,10 as `MaxCoinMerges`,'NoOfCoinSwapsThisWeek',`SpreadBetRuleName`
    ,(sum(`CoinPrice`*`Amount`)) as `OriginalPrice`, ((sum(`LiveCoinPrice`*`Amount`))/100)*0.28 as `CoinFee`, (sum(`LiveCoinPrice`*`Amount`)) as `LivePrice`
    , (sum(`LiveCoinPrice`*`Amount`))-(sum(`CoinPrice`*`Amount`)) as `ProfitUSD`
    ,sum(((`LiveCoinPrice`*`Amount`)-(`CoinPrice`*`Amount`)-((((`LiveCoinPrice`*`Amount`))/100)*0.28))/(`CoinPrice`*`Amount`))*100 as ProfitPct
    ,`SpreadBetRuleName`,(sum(`LiveCoinPrice`*`Amount`))-(sum(`CoinPrice`*`Amount`)) as `ProfitUSD`,(sum(`CoinPrice`*`Amount`)) as `OriginalPrice`,(sum(`LiveCoinPrice`*`Amount`)) as `LivePrice`
    FROM `View5_SellCoins`WHERE `UserID` = $userID and `SpreadBetTransactionID` in ($comma_separated) and `Type` = 'SpreadSell' Group by `SpreadBetTransactionID` ORDER BY `ProfitPct` Desc";
  $result = $conn->query($sql);
    print_r($sql);
  //$result = mysqli_query($link4, $query);
//mysqli_fetch_assoc($result);`PctChange1Hr`, `PctChange24Hr`, `PctChange7D`
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['Type'],$row['CoinID'],$row['UserID'],$row['CoinPrice'],$row['Amount'],$row['Status'],$row['OrderDate'],$row['CompletionDate'],$row['BittrexID'],$row['OrderNo'],
      $row['Symbol'],$row['LastBuyOrders'],$row['LiveBuyOrders'],$row['BuyOrdersPctChange'],$row['LastMarketCap'],$row['LiveMarketCap'],$row['MarketCapPctChange'],$row['LastCoinPrice'],$row['LiveCoinPrice'], //19
      $row['CoinPricePctChange'],$row['LastSellOrders'],$row['LiveSellOrders'],$row['SellOrdersPctChange'],$row['LastVolume'],$row['LiveVolume'],$row['VolumePctChange'],$row['Last1HrChange'],$row['Live1HrChange'],$row['Hr1ChangePctChange'],$row['Last24HrChange'],$row['Live24HrChange'] //31
      ,$row['Hr24ChangePctChange'],$row['Last7DChange'],$row['Live7DChange'],$row['D7ChangePctChange'],$row['BaseCurrency'],$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['FixSellRule'],$row['SellRule'],$row['BuyRule'] //43
      ,$row['ToMerge'],$row['LowPricePurchaseEnabled'],$row['DailyBTCLimit'],$row['PctToPurchase'],$row['BTCBuyAmount'],$row['NoOfPurchases'],$row['Name'],$row['Image'],$row['MaxCoinMerges'],$row['NoOfCoinSwapsThisWeek'],$row['SpreadBetRuleName'] //54
    ,$row['OriginalPrice'],$row['CoinFee'],$row['LivePrice'],$row['ProfitUSD'],$row['ProfitPct'],$row['SpreadBetRuleName'],$row['ProfitUSD'],$row['OriginalPrice'],$row['LivePrice']); //63
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
        $openSpreadBetTransID = getDistinctSpreadBetID();
        var_dump($openSpreadBetTransID);
        echo "<BR>".count($openSpreadBetTransID)."<BR>";
        if (count($openSpreadBetTransID)>1){
          $comma_separated = implode(",", $openSpreadBetTransID);
        }

        $trackingSell = getTrackingSellCoinsLoc($_SESSION['ID'],$comma_separated);
        $arrLengthSell = count($trackingSell);
        $roundVar = $_SESSION['roundVar'];
        //$userConfig = getConfig($_SESSION['ID']);
        print_r("<h2>Sell Some Coins Now!</h2>");
        echo "<h3><a href='SellCoins.php'>Sell Coins</a> &nbsp > &nbsp <a href='SellCoins_Tracking.php'>Tracking</a> &nbsp > &nbsp <a href='SellCoins_Saving.php'>Saving</a> &nbsp > &nbsp <a href='SellCoins_Spread.php'>Spread Bet</a> &nbsp > &nbsp <a href='SellCoins_SpreadCoin.php'>Spread Bet Coin</a>
         &nbsp > &nbsp <a href='SellCoins_SwapCoins.php'>Swap Coins</a></h3>";

        for($x = 0; $x < $arrLengthSell; $x++) {
            //Variables
            //$roundNum = 2;
            //if($_SESSION['isMobile'] == False){$roundNum = 8;}
            $coin = $trackingSell[$x][11];  $livePrice = $trackingSell[$x][19]; $LastCoinPrice = $trackingSell[$x][18]; $baseCurrency = $trackingSell[$x][36];
            $amount = $trackingSell[$x][5];  $orderNo = $trackingSell[$x][10]; $transactionID = $trackingSell[$x][0];
            $purchaseCost = $trackingSell[$x][4]; $realAmount = $trackingSell[$x][26];
            $mrktCap = $trackingSell[$x][17];  $volume = $trackingSell[$x][26]; $sellOrders = $trackingSell[$x][23];
            $pctChange1Hr = $trackingSell[$x][29]; $pctChange24Hr = $trackingSell[$x][32]; $pctChange7D = $trackingSell[$x][35]; $spreadBetRuleName = $trackingSell[$x][54];
            $priceDiff1 = $livePrice - $LastCoinPrice;

            $liveTotalCost = $trackingSell[$x][57];
            $originalPurchaseCost = $trackingSell[$x][55];
            $fee = $trackingSell[$x][56];
            //$profit = $trackingSell[$x][55];
            //$profitBtc = $profit/($originalPurchaseCost)*100;
            //$profitPct = getTotalProfitSpreadBetSell($transactionID);
            $tempProfit = getTotalProfitSpreadBetSellLoc($transactionID);
            //$tempSoldProfit = getSoldProfitSpreadBetSell($transactionID);
            $purchasePrice = $tempProfit[0][0];
            $livePrice = $tempProfit[0][1] + $tempProfit[0][2];
            $profit = $tempProfit[0][3]-$fee;
            //$profitPct = $trackingSell[$x][59];
            //echo "<BR> HELP: $liveTotalCost | $originalPurchaseCost | $fee | $profit";
            $profitPct = ($profit/$purchasePrice)*100;
            //$profitPct = ($profit/$purchasePrice)*100;
            echo "PROFIT CALC: $profit | $livePrice | $liveTotalCost | $purchasePrice | $originalPurchaseCost | $fee | $profitPct";
            $userID = $_SESSION['ID'];
            $name = $trackingSell[$x][50]; $image = $trackingSell[$x][51];
            echo "<table><td rowspan='3'><a href='SellCoins_SpreadCoin.php'><img src='$image'></a></td>";
            echo "<td><p id='largeText' >$spreadBetRuleName</p></td>";
            echo "<td rowspan='2'><p id='largeText' >".round((float)$livePrice,$roundVar)."</p></td>";
            NewEcho("<td><p id='normalText'>".round((float)$mrktCap,$roundVar)."</p></td>",$_SESSION['isMobile'],0);
            NewEcho("<td><p id='normalText'>".round((float)$pctChange1Hr,$roundVar)."</p></td>",$_SESSION['isMobile'],2);
            echo "<td><p id='largeText' >".round((float)$amount,$roundVar)." $coin</p></td>";

            echo "</tr><tr>";
            echo "<td><p id='normalText'>$coin</p></td>";
            NewEcho("<td><p id='normalText'>".round((float)$volume,$roundVar)."</p></td>",$_SESSION['isMobile'],0);
            NewEcho("<td><p id='normalText'>".round((float)$pctChange24Hr,$roundVar)."</p></td>",$_SESSION['isMobile'],2);
            $cost = round(number_format((float)$trackingSell[$x][4], 10, '.', ''),8);
            echo "<td><p id='normalText'>$cost</p></td>";

            echo "</tr><tr>";


            //$numCol = getNumberColour($profitBtc);
            //echo "<td><p id='smallText' style='color:$numCol'>".round($profitBtc,8)."</p></td>";

            $numCol = getNumberColour($priceDiff1);
            echo "<td><p id='smallText' style='color:$numCol'>".round((float)$priceDiff1,$roundVar)."</p></td>";
            echo "<td><p id='largeText' >".round((float)$profit,$roundVar)." $baseCurrency</p></td>";

            NewEcho("<td><p id='normalText'>".round((float)$sellOrders,$roundVar)."</p></td>",$_SESSION['isMobile'],0);
            NewEcho("<td><p id='normalText'>".round((float)$pctChange7D,$roundVar)."</p></td>",$_SESSION['isMobile'],0);
            $numCol = getNumberColour($profitPct);
            echo "<td><p id='smallText' style='color:$numCol'>".round((float)$profitPct,$roundVar)."</p></td>";

            echo "</tr><tr>";

            echo "<td><a href='SellCoins_Spread.php?Mode=1&SBTransID=$transactionID'>Sell Coins</a></td>";
            echo "<td><a href=''></a></td>";
            echo "<td><a href=''></a></td>";
            echo "<td><a href=''></a></td>";
            echo "<td><a href=''></a></td>";
            echo "<td><a href=''></a></td>";
        }
        print_r("</table>");
        Echo "<a href='SellCoins.php?noOverride=Yes'>View Mobile Page</a>".$_SESSION['MobOverride'];
				displaySideColumn();
//include header template
require('layout/footer.php');
$date = date('Y/m/d H:i:s', time());
echo " Last Updated :".$date;
?>
