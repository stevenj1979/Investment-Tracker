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

if(isset($_GET['Mode'])){
  if ($_GET['Mode'] == 1){
    //BuyBack
    $ID = $_GET['ID']; $profitPct = $_GET['ProfitPct'];
    //echo "<BR>BuyBack ID: $ID";
    //Sell Coin
    $buyBackData = getTrackingSellData($ID);

    //Write to BuyBack Table
    if ($profitPct > 0){
        $totalMins = 10080;
        $totalRisesBuy = 10;
        $totalRisesSell = 10;
    }else{
        $totalMins = 20160;
        $totalRisesBuy = 15;
        $totalRisesSell = 1;
    }
    newTrackingSellCoins($buyBackData[0][0], $buyBackData[0][1],$ID,1,1,0,0.0,$totalRisesSell);
    setTransactionPending($ID);
    WriteBuyBack($ID,$profitPct,$totalRisesBuy, $totalMins);
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


function getTrackingSellData($transactionID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "select `Cp`.`LiveCoinPrice`, `Tr`.`UserID`
    from `Transaction` `Tr`
    join `CoinPrice` `Cp` on `Cp`.`CoinID` = `Tr`.`CoinID`
    where `Tr`.`ID` = $transactionID";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['LiveCoinPrice'],$row['UserID']);
  }
  $conn->close();
  return $tempAry;
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

function getTrackingSellCoinsLoc($userID,$spreadBetRuleName, $enabled){
  $tempAry = [];
  if ($userID <> 0){ $whereclause = "Where `UserID` = $userID and `Status` = 'Open' and `Type` = 'Sell' ";}else{$whereclause = "Where `Status` = 'Open' and `Type` = 'Sell' ";}
  if ($enabled == 1){ $enabledStr = " and `DelayCoinSwapUntil` < now() ";}
  else { $enabledStr = " and `DelayCoinSwapUntil` > now() "; }
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "SELECT `IDTr`,`Type`,`CoinID`,`UserID`,`CoinPrice`,`Amount`,`Status`,`OrderDate`,`CompletionDate`,`BittrexID`,`OrderNo`,`Symbol`,`LastBuyOrders`, `LiveBuyOrders`,`BuyOrdersPctChange`,`LastMarketCap`,`LiveMarketCap`,`MarketCapPctChange`,`LastCoinPrice`,`LiveCoinPrice`,`CoinPricePctChange`,`LastSellOrders`
    ,`LiveSellOrders`,`SellOrdersPctChange`,`LastVolume`,`LiveVolume`,`VolumePctChange`,`Last1HrChange`,`Live1HrChange`,`1HrPriceChangeLive`,`Last24HrChange`,`Live24HrChange`,`Hr24ChangePctChange`,`Last7DChange`,`Live7DChange`,`D7ChangePctChange`,`BaseCurrency`
    , `Price4Trend`,`Price3Trend`,`LastPriceTrend`,`LivePriceTrend`,`FixSellRule`,`SellRule`,`BuyRule`,`ToMerge`,`LowPricePurchaseEnabled`,`DailyBTCLimit`,`PctToPurchase`,`BTCBuyAmount`,`NoOfPurchases`,`Name`,`Image`,10 as `MaxCoinMerges`,`NoOfCoinSwapsThisWeek`
    ,`OriginalPrice`, `CoinFee`, `LivePrice`, `ProfitUSD`, `ProfitPct`
    ,`CaptureTrend`,`IDCn`,'SellPctCsp'
    FROM `View5_SellCoins`
    WHERE `UserID` = $userID and `Type` = 'SpreadSell' and `Status` = 'Open' and `SpreadBetRuleName` = '$spreadBetRuleName' $enabledStr
    ORDER BY `ProfitPct` Desc";
    //a
  $result = $conn->query($sql);
    //print_r("<BR>$sql<BR>");
  //$result = mysqli_query($link4, $query);
//mysqli_fetch_assoc($result);`PctChange1Hr`, `PctChange24Hr`, `PctChange7D`
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['IDTr'],$row['Type'],$row['CoinID'],$row['UserID'],$row['CoinPrice'],$row['Amount'],$row['Status'],$row['OrderDate'],$row['CompletionDate'],$row['BittrexID'],$row['OrderNo'],
    $row['Symbol'],$row['LastBuyOrders'],$row['LiveBuyOrders'],$row['BuyOrdersPctChange'],$row['LastMarketCap'],$row['LiveMarketCap'],$row['MarketCapPctChange'],$row['LastCoinPrice'],$row['LiveCoinPrice'], //19
    $row['CoinPricePctChange'],$row['LastSellOrders'],$row['LiveSellOrders'],$row['SellOrdersPctChange'],$row['LastVolume'],$row['LiveVolume'],$row['VolumePctChange'],$row['Last1HrChange'],$row['Live1HrChange'],$row['1HrPriceChangeLive'],$row['Last24HrChange'],$row['Live24HrChange'] //31
    ,$row['Hr24ChangePctChange'],$row['Last7DChange'],$row['Live7DChange'],$row['D7ChangePctChange'],$row['BaseCurrency'],$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['FixSellRule'],$row['SellRule'],$row['BuyRule'] //43
    ,$row['ToMerge'],$row['LowPricePurchaseEnabled'],$row['PurchaseLimit'],$row['PctToPurchase'],$row['BTCBuyAmount'],$row['NoOfPurchases'],$row['Name'],$row['Image'],$row['MaxCoinMerges'],$row['NoOfCoinSwapsThisWeek'] //53
    ,$row['OriginalPrice'],$row['CoinFee'],$row['LivePrice'],$row['ProfitUSD'],$row['ProfitPct'],$row['CaptureTrend'] //59
    ,$row['IDCn'],$row['SellPctCsp']); //61
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

function displaySpreadBetCoins($trackingSell, $arrLengthSell,$roundVar, $name,$fontSize){
  echo "<h3> $name</h3><br>";
  for($x = 0; $x < $arrLengthSell; $x++) {
      //Variables
      //$roundNum = 2;
      //if($_SESSION['isMobile'] == False){$roundNum = 8;}
      $coin = $trackingSell[$x][11];  $livePrice = $trackingSell[$x][19]; $LastCoinPrice = $trackingSell[$x][18]; $baseCurrency = $trackingSell[$x][36];
      $amount = $trackingSell[$x][5];  $orderNo = $trackingSell[$x][10]; $transactionID = $trackingSell[$x][0];
       $purchaseCost = $trackingSell[$x][4]; $realAmount = $trackingSell[$x][26];
      $mrktCap = $trackingSell[$x][17];  $volume = $trackingSell[$x][26]; $sellOrders = $trackingSell[$x][23];
      $pctChange1Hr = $trackingSell[$x][29]; $pctChange24Hr = $trackingSell[$x][32]; $pctChange7D = $trackingSell[$x][35]; $originalPrice = $trackingSell[$x][54];
      $priceDiff1 = $livePrice - $LastCoinPrice; $coinID = $trackingSell[$x][2];
      $fee = (($livePrice* $amount)/100)*0.28;
      $liveTotalCost = $trackingSell[$x][56];
      $originalPurchaseCost = $trackingSell[$x][54];
      $profit = $trackingSell[$x][57];
      $profitBtc = $trackingSell[$x][58];
      $userID = $_SESSION['ID'];
      $name = $trackingSell[$x][50]; $image = $trackingSell[$x][51];
      echo "<table><td rowspan='4'><a href='Stats.php?coin=$coinID'><img src='$image'></a></td>";
      echo "<td><p id='largeText' >".round((float)$originalPrice+0,$roundVar)."</p></td>";
      echo "<td rowspan='2'><p id='largeText' >".round((float)$livePrice+0,$roundVar)."</p></td>";
      NewEcho("<td><p id='normalText'>".round((float)$mrktCap,$roundVar)."</p></td>",$_SESSION['isMobile'],0);
      NewEcho("<td><p id='normalText'>".round((float)$pctChange1Hr,$roundVar)."</p></td>",$_SESSION['isMobile'],2);
      echo "<td><p id='largeText' >".round((float)$amount,$roundVar)." $coin</p></td>";



      echo "</tr><tr>";
      //Rowspan
      echo "<td><p id='normalText'>$coin</p></td>";
      //Rowspan
      NewEcho("<td><p id='normalText'>".round((float)$volume,$roundVar)."</p></td>",$_SESSION['isMobile'],0);

      NewEcho("<td><p id='normalText'>".round((float)$pctChange24Hr,$roundVar)."</p></td>",$_SESSION['isMobile'],2);
      $cost = round(number_format((float)$trackingSell[$x][4], 10, '.', ''),8);
      echo "<td><p id='normalText'>$cost</p></td>";



      echo "</tr><tr>";
      //$numCol = getNumberColour($profitBtc);
      //echo "<td><p id='smallText' style='color:$numCol'>".round($profitBtc,8)."</p></td>";

      $numCol = getNumberColour($priceDiff1);
      echo "<td><p id='smallText' style='color:$numCol'>".round($priceDiff1,$roundVar)."</p></td>";
      echo "<td><p id='largeText' >".round((float)$profit,$roundVar)." $baseCurrency</p></td>";

      NewEcho("<td><p id='normalText'>".round((float)$sellOrders,$roundVar)."</p></td>",$_SESSION['isMobile'],0);
      NewEcho("<td><p id='normalText'>".round((float)$pctChange7D,$roundVar)."</p></td>",$_SESSION['isMobile'],0);
      $numCol = getNumberColour($profitBtc);
      echo "<td><p id='smallText' style='color:$numCol'>".round((float)$profitBtc,$roundVar)."</p></td>";

      //Last Line
      echo "</tr><tr>";
      echo "<td><div title='Manual Sell!'><a href='ManualSell.php?manSell=Yes&coin=$coin&amount=".$amount."&cost=$originalPurchaseCost&baseCurrency=$baseCurrency&orderNo=$orderNo&transactionID=$transactionID&salePrice=$livePrice'><i class='fas fa-shopping-cart' style='$fontSize;color:DodgerBlue'></i></a></DIV></td>";
      echo "<td><div title='Split Coin!'><a href='ManualSell.php?splitCoin=$coin&amount=".$amount."&cost=$originalPurchaseCost&baseCurrency=$baseCurrency&orderNo=$orderNo&transactionID=$transactionID&salePrice=$livePrice'><i class='fas fa-file-archive' style='$fontSize;color:DodgerBlue'></i></a></DIV></td>";
      echo "<td><div title='TrackCoin!'><a href='ManualSell.php?trackCoin=Yes&baseCurrency=$baseCurrency&transactionID=$transactionID&salePrice=$livePrice&userID=$userID'><i class='fas fa-clock' style='$fontSize;color:DodgerBlue'></i></a></DIV></td>";
      echo "<td><div title='Saving!'><a href='ManualSell.php?manReopen=Yes&transactionID=$transactionID'><i class='fas fa-hryvnia' style='$fontSize;color:DodgerBlue'></i></a></DIV></td>";
      echo "<td><div title='BuyBack!'><a href='SellCoins_SpreadCoin.php?Mode=1&ID=$transactionID&ProfitPct=$profitBtc'>Buy Back</a></DIV></td>";
  }
  print_r("</table><br>");
}

function newDisplaySpreadBetCoins($trackingSell, $arrLengthSell,$roundVar, $name,$fontSize){
  echo "<h3> $name</h3><br>";
  for($x = 0; $x < $arrLengthSell; $x++) {
      //Variables
      //$roundNum = 2;
      //if($_SESSION['isMobile'] == False){$roundNum = 8;}
      $coin = $trackingSell[$x][11];  $livePrice = $trackingSell[$x][19]; $LastCoinPrice = $trackingSell[$x][18]; $baseCurrency = $trackingSell[$x][36];
      $amount = $trackingSell[$x][5];  $orderNo = $trackingSell[$x][10]; $transactionID = $trackingSell[$x][0];
       $purchaseCost = $trackingSell[$x][4]; $realAmount = $trackingSell[$x][26];
      $mrktCap = $trackingSell[$x][17];  $volume = $trackingSell[$x][26]; $sellOrders = $trackingSell[$x][23];
      $pctChange1Hr = $trackingSell[$x][29]; $pctChange24Hr = $trackingSell[$x][32]; $pctChange7D = $trackingSell[$x][35]; $originalPrice = $trackingSell[$x][54];
      $priceDiff1 = $livePrice - $LastCoinPrice; $coinID = $trackingSell[$x][2];
      $fee = (($livePrice* $amount)/100)*0.28;
      $liveTotalCost = $trackingSell[$x][56];
      $originalPurchaseCost = $trackingSell[$x][54];
      $profit = $trackingSell[$x][57];
      $profitBtc = $trackingSell[$x][58];
      $userID = $_SESSION['ID'];
      $name = $trackingSell[$x][50]; $image = $trackingSell[$x][51];
      $boxAry = array (
        array("Image","Stats.php?coin=$coinID","$image",""),
        array("PurchasePrice",round((float)$originalPrice+0,$roundVar),"",""),
        array("LivePrice",round((float)$livePrice+0,$roundVar),"",""),
        array("MarketCap",round((float)$mrktCap,$roundVar),"",""),
        array("1HrChange",round((float)$pctChange1Hr,$roundVar),"","")

      );
      displayBox($boxAry);
}

function getSpreadBetIDOpen($userID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "SELECT `Tr`.`ID`,`Sbr`.`Name` as `SpreadBetRuleName`
    FROM `Transaction` `Tr`
    join `SpreadBetRules` `Sbr` on `Sbr`.`ID` = `Tr`.`SpreadBetRuleID`
    WHERE `Tr`.`UserID` = $userID and `Type` = 'SpreadSell' and `Status` = 'Open'
    group by `SpreadBetRuleName`";
  $result = $conn->query($sql);
    //print_r($sql);
  //$result = mysqli_query($link4, $query);
//mysqli_fetch_assoc($result);`PctChange1Hr`, `PctChange24Hr`, `PctChange7D`
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['SpreadBetRuleName']);
  }
  $conn->close();
  return $tempAry;
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
        $spreadBetID = getSpreadBetIDOpen($_SESSION['ID']);
        $spreadBetIDSize = count($spreadBetID);
        $roundVar = $_SESSION['roundVar'];
        //$userConfig = getConfig($_SESSION['ID']);
        print_r("<h2>Sell Some Coins Now!</h2>");
        echo "<h3><a href='SellCoins.php'>Sell Coins</a> &nbsp > &nbsp <a href='SellCoins_Tracking.php'>Tracking</a> &nbsp > &nbsp <a href='SellCoins_Saving.php'>Saving</a> &nbsp > &nbsp <a href='SellCoins_Spread.php'>Spread Bet</a> &nbsp > &nbsp <a href='SellCoins_SpreadCoin.php'>Spread Bet Coin</a>
         &nbsp > &nbsp <a href='SellCoins_SwapCoins.php'>Swap Coins</a></h3>";
        for ($s=0; $s<$spreadBetIDSize; $s++){
          $trackingSell = getTrackingSellCoinsLoc($_SESSION['ID'],$spreadBetID[$s][1],1);
          $arrLengthSell = count($trackingSell);
          //displaySpreadBetCoins($trackingSell, $arrLengthSell,$roundVar, $spreadBetID[$s][1]."Enabled",$fontSize);
          newDisplaySpreadBetCoins($trackingSell, $arrLengthSell,$roundVar, $spreadBetID[$s][1]."Enabled",$fontSize);
          $trackingSell = getTrackingSellCoinsLoc($_SESSION['ID'],$spreadBetID[$s][1],0);
          $arrLengthSell = count($trackingSell);
          //displaySpreadBetCoins($trackingSell, $arrLengthSell,$roundVar, $spreadBetID[$s][1]."Disabled",$fontSize);
          newDisplaySpreadBetCoins($trackingSell, $arrLengthSell,$roundVar, $spreadBetID[$s][1]."Disabled",$fontSize);
        }


        Echo "<a href='SellCoins.php?noOverride=Yes'>View Mobile Page</a>".$_SESSION['MobOverride'];
				displaySideColumn();
//include header template
require('layout/footer.php');
$date = date('Y/m/d H:i:s', time());
echo " Last Updated :".$date;
?>
