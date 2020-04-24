<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<?php require('includes/config.php');
include_once '../includes/newConfig.php';?>
<html>
<style>
<?php include 'style/style.css'; ?>
</style>
<body>
<?php


//if not logged in redirect to login page
if(!$user->is_logged_in()){ header('Location: login.php'); exit(); }

if(isset($_POST['submit'])){
  if(isset($_POST['buyWithScore'])){
    $tempChecked = 1;
  }else{
    $tempChecked = 0;
  }

  if(isset($_POST['sellWithScore'])){
    $tempSellChecked = 1;
  }else{
    $tempSellChecked = 0;
  }
	//if (!isset($_POST['username'])) $error[] = "Please fill out all fields";
	//if (!isset($_POST['email'])) $error[] = "Please fill out all fields";
  //if (!isset($_POST['API_Key'])) $error[] = "Please fill out all fields";
  //if (!isset($_POST['API_Secret'])) $error[] = "Please fill out all fields";
  if (isset($_POST['user'])){
    updateUser($_SESSION['ID'],$_POST['newusername'],$_POST['email'],$_POST['API_Key'],$_POST['API_Secret']);
  }elseif (isset($_POST['buy'])){
    echo "BUY!!! ";
    updateBuyConfig($_SESSION['ID'],$_POST['BTC'],$_POST['MarketCapBuyPct'],$_POST['VolumeBuyPct'],$_POST['BuyOrdersPct'],$tempChecked,$_POST['score']);
  }elseif (isset($_POST['sell'])){
    updateSellConfig($_SESSION['ID'],$_POST['CoinSalePct'],$_POST['MarketCapSellPct'],$_POST['VolumeSellPct'],$_POST['SellOrdersPct'],$_POST['MinPctGain'],$tempSellChecked,$_POST['SellScore']);
  }
	//Update User table


}//end if submit
//define page title
$title = 'CryptoBot';

//include header template
require('layout/header.php');
include_once ('/home/stevenj1979/SQLData.php');

function getUserIDs($userID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = " SELECT `ID`, `Username`, `email`, `api_key`, `api_secret` FROM `User` where `ID` = $userID";
	//echo $sql;
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['Username'],$row['email'],$row['api_key'],$row['api_secret']);
  }
  $conn->close();
  return $tempAry;
}

function getRules($userID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT
`UserID`,`BuyOrdersEnabled`,`BuyOrdersTop`,`BuyOrdersBtm`,`MarketCapEnabled`,`MarketCapTop`,`MarketCapBtm`,`1HrChangeEnabled`,
`1HrChangeTop`,`1HrChangeBtm`,`24HrChangeEnabled`,`24HrChangeTop`,`24HrChangeBtm`,`7DChangeEnabled`,`7DChangeTop`,`7DChangeBtm`,
`CoinPriceEnabled`,`CoinPriceTop`,`CoinPriceBtm`,`SellOrdersEnabled`,`SellOrdersTop`,`SellOrdersBtm`,`VolumeEnabled`,`VolumeTop`,
`VolumeBtm`,`BuyCoin`,`SendEmail`,`BTCAmount`,`RuleID`,`BuyCoinOffsetEnabled`,`BuyCoinOffsetPct`,`PriceTrendEnabled`, `Price4Trend`, `Price3Trend`, `LastPriceTrend`, `LivePriceTrend`
, `Active`, `DisableUntil`, `BaseCurrency`, `NoOfCoinPurchase`, `TimetoCancelBuy`, `BuyType`, `TimeToCancelBuyMins`, `BuyPriceMinEnabled`, `BuyPriceMin`, `LimitToCoin`,`AutoBuyCoinEnabled`,`AutoBuyPrice`
,`BuyAmountOverrideEnabled`,`BuyAmountOverride`,`NewBuyPattern`,`SellRuleFixed`,`CoinOrder`,`CoinPricePatternEnabled`,`CoinPricePattern`
FROM `UserBuyRules` WHERE `UserID` =  $userID Order by `CoinOrder` Asc";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  //print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['UserID'],$row['BuyOrdersEnabled'],$row['BuyOrdersTop'],$row['BuyOrdersBtm'],$row['MarketCapEnabled'],$row['MarketCapTop'],
      $row['MarketCapBtm'],$row['1HrChangeEnabled'],$row['1HrChangeTop'],$row['1HrChangeBtm'],$row['24HrChangeEnabled'],$row['24HrChangeTop'],$row['24HrChangeBtm'],
      $row['7DChangeEnabled'],$row['7DChangeTop'],$row['7DChangeBtm'],$row['CoinPriceEnabled'],$row['CoinPriceTop'],$row['CoinPriceBtm'],$row['SellOrdersEnabled'],
      $row['SellOrdersTop'],$row['SellOrdersBtm'],$row['VolumeEnabled'],$row['VolumeTop'],$row['VolumeBtm'],$row['BuyCoin'],$row['SendEmail'],$row['BTCAmount'],$row['RuleID']
      ,$row['BuyCoinOffsetEnabled'],$row['BuyCoinOffsetPct'],$row['PriceTrendEnabled'],$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend']
     ,$row['Active'],$row['DisableUntil'],$row['BaseCurrency'],$row['NoOfCoinPurchase'],$row['TimetoCancelBuy'],$row['BuyType'],$row['TimeToCancelBuyMins'],$row['BuyPriceMinEnabled'],$row['BuyPriceMin']
      ,$row['LimitToCoin'],$row['AutoBuyCoinEnabled'],$row['AutoBuyPrice'],$row['BuyAmountOverrideEnabled'],$row['BuyAmountOverride'],$row['NewBuyPattern'],$row['SellRuleFixed'],$row['CoinOrder']
      ,$row['CoinPricePatternEnabled'],$row['CoinPricePattern']);//35
  }
  $conn->close();
  return $tempAry;
}

function updateUser($userID, $newusername, $email, $apikey, $apisecret){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `User` SET `Username` = '$newusername', `email` = '$email', `api_key` = '$apikey', `api_secret` = '$apisecret' where `ID` = $userID";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();

}

function updateBuyConfig($userID, $BTC,$MarketCapBuyPct,  $VolumeBuyPct,  $BuyOrdersPct,  $buyWithScore, $score){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `Config` SET `BTC` = $BTC,  `MarketCapBuyPct` = $MarketCapBuyPct,  `VolumeBuyPct` = $VolumeBuyPct, `BuyOrdersPct` = $BuyOrdersPct,
   `BuyWithScore` = $buyWithScore,`Score` = $score  where `UserID` = $userID";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();

}

function updateSellConfig($userID, $CoinSalePct, $MarketCapSellPct,  $VolumeSellPct, $SellOrdersPct, $minProfitPct,  $sellWithScore, $sellScore){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `Config` SET `CoinSalePct` = $CoinSalePct,  `MarketCapSellPct` = $MarketCapSellPct,  `VolumeSellPct` = $VolumeSellPct, `SellOrdersPct` = $SellOrdersPct,
  `MinPctGain` = $minProfitPct, `SellWithScore` = $sellWithScore,`SellScore` = $sellScore where `UserID` = $userID";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();

}

function drawCheckbox($checked){
  if ($checked == 1){
    ?> <input type="checkbox" name="buyWithScore" id="buyWithScore" class="form-control input-lg"  value="1" checked tabindex="14"> <?php
  }else{
    ?> <input type="checkbox" name="buyWithScore" id="buyWithScore" class="form-control input-lg"  value="0" tabindex="14"> <?php
  }
}

function drawSellCheckbox($checked){
  if ($checked == 1){
    ?> <input type="checkbox" name="sellWithScore" id="sellWithScore" class="form-control input-lg"  value="1" checked tabindex="16"> <?php
  }else{
    ?> <input type="checkbox" name="sellWithScore" id="sellWithScore" class="form-control input-lg"  value="0" tabindex="16"> <?php
  }
}



$userSettings = getRules($_SESSION['ID']);
$userSettingsLen = count($userSettings);
//echo $userDetails[0][1];

displayHeader(7);?>

           <h3><a href='Settings.php'>User Settings</a> &nbsp > &nbsp <a href='BuySettings.php'>Buy Settings</a> &nbsp > &nbsp <a href='SellSettings.php'>Sell Settings</a></h3>
           <table>
             <th>&nbspEdit</th><th>&nbspCopy</th><th>&nbspDelete</th><TH>&nbspRuleID</TH><TH>&nbspUserID</TH><TH>&nbspBuyOrdersEnabled</TH><TH>&nbspBuyOrdersTop</TH><TH>&nbspBuyOrdersBtm</TH><TH>&nbspMarketCapEnabled</TH><TH>&nbspMarketCapTop</TH><TH>&nbspMarketCapBtm</TH>
             <TH>&nbsp1HrChangeEnabled</TH><TH>&nbsp1HrChangeTop</TH><TH>&nbsp1HrChangeBtm</TH><TH>&nbsp24HrChangeEnabled</TH><TH>&nbsp24HrChangeTop</TH><TH>&nbsp24HrChangeBtm</TH><TH>&nbsp7DChangeEnabled</TH><TH>&nbsp7DChangeTop</TH>
             <TH>&nbsp7DChangeBtm</TH><TH>&nbspCoinPriceEnabled</TH><TH>&nbspCoinPriceTop</TH><TH>&nbspCoinPriceBtm</TH><TH>&nbspSellOrdersEnabled</TH><TH>&nbspSellOrdersTop</TH><TH>&nbspSellOrdersBtm</TH><TH>&nbspVolumeEnabled</TH>
             <TH>&nbspVolumeTop</TH><TH>&nbspVolumeBtm</TH><TH>&nbspBuyCoin</TH><TH>&nbspSendEmail</TH><TH>&nbspBTCAmount</TH><TH>&nbspBuyCoinOffsetEnabled</TH><TH>&nbspBuyCoinOffsetPct</TH><TH>&nbspPriceTrendEnabled</TH><TH>&nbspPrice4Trend</TH>
             <TH>&nbspPrice3Trend</TH><TH>&nbspLastPriceTrend</TH><TH>&nbspLivePriceTrend</TH>
            <TH>&nbspActive</TH><TH>&nbspDisableUntil</TH><TH>&nbspBaseCurrency</TH><TH>&nbspNoOfCoinPurchase</TH><TH>&nbspTimetoCancelBuy</TH><TH>&nbspBuyType</TH><TH>&nbspTimeToCancelBuyMins</TH><TH>&nbspBuyPriceMinEnabled</TH><TH>&nbspBuyPriceMin</TH>
            <TH>&nbspLimitToCoin</TH><TH>&nbspAutoBuyCoinEnabled</TH><TH>&nbspAutoBuyPrice</TH><TH>&nbspBuyAmountOverrideEnabled</TH><TH>&nbspBuyAmountOverride</TH><TH>&nbspNewBuyPattern</TH><TH>&nbspSellRuleFixed</TH><TH>&nbspCoinOrder</TH>
            <TH>&nbspCoinPricePatternEnabled</TH><TH>&nbspCoinPricePattern</TH>
             <tr>
          <?php
          for($x = 0; $x < $userSettingsLen; $x++) {
            $ruleID = $userSettings[$x][28]; $userID = $userSettings[$x][0];
            $buyOrdersEnabled = $userSettings[$x][1]; $buyOrdersTop = $userSettings[$x][2]; $buyOrdersBtm = $userSettings[$x][3];
            $marketCapEnabled = $userSettings[$x][4];$marketCapTop = $userSettings[$x][5]; $marketCapBtm = $userSettings[$x][6];
            $hr1ChangeEnabled = $userSettings[$x][7]; $hr1ChangeTop = $userSettings[$x][8]; $hr1ChangeBtm = $userSettings[$x][9];
            $hr24ChangeEnabled = $userSettings[$x][10]; $hr24ChangeTop = $userSettings[$x][11]; $hr24ChangeBtm = $userSettings[$x][12];
            $d7ChangeEnabled = $userSettings[$x][13]; $d7ChangeTop = $userSettings[$x][14]; $d7ChangeBtm = $userSettings[$x][15];
            $coinPriceEnabled = $userSettings[$x][16];$coinPriceTop = $userSettings[$x][17];$coinPriceBtm = $userSettings[$x][18];
            $sellOrdersEnabled = $userSettings[$x][19];$sellOrdersTop = $userSettings[$x][20];$sellOrdersBtm = $userSettings[$x][21];
            $volumeEnabled = $userSettings[$x][22];$volumeTop = $userSettings[$x][23];$volumeBtm = $userSettings[$x][24];
            $buyCoin = $userSettings[$x][25];$sendEmail = $userSettings[$x][26];$bTCAmount = $userSettings[$x][27];
            $buyCoinOffsetEnabled = $userSettings[$x][29];$buyCoinOffsetPct = $userSettings[$x][30];
            $priceTrendEnabled = $userSettings[$x][31];$price4Trend = $userSettings[$x][32];$price3Trend = $userSettings[$x][33];$lastPriceTrend = $userSettings[$x][34];$livePriceTrend = $userSettings[$x][35];
            $active = $userSettings[$x][36];$disableUntil = $userSettings[$x][37];$baseCurrency = $userSettings[$x][38];$noOfCoinPurchase = $userSettings[$x][39];$timetoCancelBuy = $userSettings[$x][40];
            $buyType = $userSettings[$x][41];$timeToCancelBuyMins = $userSettings[$x][42];
            $buyPriceMinEnabled = $userSettings[$x][43];$buyPriceMin = $userSettings[$x][44];$limitToCoin = $userSettings[$x][45];
            $autoBuyCoinEnabled = $userSettings[$x][46];$autoBuyPrice = $userSettings[$x][47];
            $buyAmountOverrideEnabled = $userSettings[$x][48];$buyAmountOverride = $userSettings[$x][49];$newBuyPattern = $userSettings[$x][50];
            $sellRuleFixed = $userSettings[$x][51];$coinOrder = $userSettings[$x][52];$coinPricePatternEnabled = $userSettings[$x][53];$coinPricePattern = $userSettings[$x][54];
            //addBuyTableLine($userSettings[$x][28],$userSettings[$x][0],$userSettings[$x][1],$userSettings[$x][2],$userSettings[$x][3])
            if ($buyCoin == 1){
              echo "<td><a href='AddNewSetting.php?edit=".$ruleID."'><span class='glyphicon glyphicon-pencil' style='font-size:22px;'></span></a></td>";
              echo "<td><a href='AddNewSetting.php?copyRule=".$ruleID."'><span class='glyphicon glyphicon-copy' style='font-size:22px;'></span></a></td>";
              echo "<td><a href='AddNewSetting.php?delete=".$ruleID."'><span class='glyphicon glyphicon-trash' style='font-size:22px;'></span></a></td>";
              echo "<td>".$ruleID."</td>";echo "<td>".$userID."</td>";
              echo "<td>".$buyOrdersEnabled."</td>";echo "<td>".$buyOrdersTop."</td>";echo "<td>".$buyOrdersBtm."</td>";
              echo "<td>".$marketCapEnabled."</td>";echo "<td>".$marketCapTop."</td>";echo "<td>".$marketCapBtm."</td>";
              echo "<td>".$hr1ChangeEnabled."</td>";echo "<td>".$hr1ChangeTop."</td>";echo "<td>".$hr1ChangeBtm."</td>";
              echo "<td>".$hr24ChangeEnabled."</td>";echo "<td>".$hr24ChangeTop."</td>";echo "<td>".$hr24ChangeBtm."</td>";
              echo "<td>".$d7ChangeEnabled."</td>";echo "<td>".$d7ChangeTop."</td>";echo "<td>".$d7ChangeBtm."</td>";
              echo "<td>".$coinPriceEnabled."</td>";echo "<td>".$coinPriceTop."</td>";echo "<td>".$coinPriceBtm."</td>";
              echo "<td>".$sellOrdersEnabled."</td>";echo "<td>".$volumeTop."</td>";echo "<td>".$volumeEnabled."</td>";echo "<td>".$sellOrdersBtm."</td>";echo "<td>".$sellOrdersTop."</td>";
              echo "<td>".$volumeBtm."</td>";echo "<td>".$buyCoin."</td>";echo "<td>".$sendEmail."</td>";
              echo "<td>".$bTCAmount."</td><td>".$buyCoinOffsetEnabled."</td><td>".$buyCoinOffsetPct."</td>";
              echo "<td>".$priceTrendEnabled."</td><td>".$price4Trend."</td><td>".$price3Trend."</td><td>".$lastPriceTrend."</td><td>".$livePriceTrend."</td>";
              echo "<td>".$active."</td><td>".$disableUntil."</td><td>".$baseCurrency."</td><td>".$noOfCoinPurchase."</td><td>".$timetoCancelBuy."</td>";
              echo "<td>".$buyType."</td><td>".$timeToCancelBuyMins."</td><td>".$buyPriceMinEnabled."</td><td>".$buyPriceMin."</td>";
              echo "<td>".$limitToCoin."</td><td>".$autoBuyCoinEnabled."</td><td>".$autoBuyPrice."</td>";
              echo "<td>".$buyAmountOverrideEnabled."</td><td>".$buyAmountOverride."</td>";
              echo "<td>".$newBuyPattern."</td><td>".$sellRuleFixed."</td>";
              echo "<td>".$coinOrder."</td><td>".$coinPricePatternEnabled."</td><td>".$coinPricePattern."</td><tr>";
            }
          }
          echo "</table> <br><a href='AddNewSetting.php?addNew=Yes'><span class='glyphicon glyphicon-plus' style='font-size:48px;'></span></a>";
          //echo "</table> <br><a href='AddNewSetting.php?addNew=Yes'>Add New</a>";?>
          <table>
            <th>&nbspEdit</th><th>&nbspCopy</th><th>&nbspDelete</th><TH>&nbspRuleID</TH><TH>&nbspUserID</TH><TH>&nbspBuyOrdersEnabled</TH><TH>&nbspBuyOrdersTop</TH><TH>&nbspBuyOrdersBtm</TH><TH>&nbspMarketCapEnabled</TH><TH>&nbspMarketCapTop</TH><TH>&nbspMarketCapBtm</TH>
            <TH>&nbsp1HrChangeEnabled</TH><TH>&nbsp1HrChangeTop</TH><TH>&nbsp1HrChangeBtm</TH><TH>&nbsp24HrChangeEnabled</TH><TH>&nbsp24HrChangeTop</TH><TH>&nbsp24HrChangeBtm</TH><TH>&nbsp7DChangeEnabled</TH><TH>&nbsp7DChangeTop</TH>
            <TH>&nbsp7DChangeBtm</TH><TH>&nbspCoinPriceEnabled</TH><TH>&nbspCoinPriceTop</TH><TH>&nbspCoinPriceBtm</TH><TH>&nbspSellOrdersEnabled</TH><TH>&nbspSellOrdersTop</TH><TH>&nbspSellOrdersBtm</TH><TH>&nbspVolumeEnabled</TH>
            <TH>&nbspVolumeTop</TH><TH>&nbspVolumeBtm</TH><TH>&nbspBuyCoin</TH><TH>&nbspSendEmail</TH><TH>&nbspBTCAmount</TH><TH>&nbspBuyCoinOffsetEnabled</TH><TH>&nbspBuyCoinOffsetPct</TH><TH>&nbspPriceTrendEnabled</TH><TH>&nbspPrice4Trend</TH>
            <TH>&nbspPrice3Trend</TH><TH>&nbspLastPriceTrend</TH><TH>&nbspLivePriceTrend</TH>
           <TH>&nbspActive</TH><TH>&nbspDisableUntil</TH><TH>&nbspBaseCurrency</TH><TH>&nbspNoOfCoinPurchase</TH><TH>&nbspTimetoCancelBuy</TH><TH>&nbspBuyType</TH><TH>&nbspTimeToCancelBuyMins</TH><TH>&nbspBuyPriceMinEnabled</TH><TH>&nbspBuyPriceMin</TH>
           <TH>&nbspLimitToCoin</TH><TH>&nbspAutoBuyCoinEnabled</TH><TH>&nbspAutoBuyPrice</TH><TH>&nbspBuyAmountOverrideEnabled</TH><TH>&nbspBuyAmountOverride</TH><TH>&nbspNewBuyPattern</TH><TH>&nbspSellRuleFixed</TH><TH>&nbspCoinOrder</TH>
           <TH>&nbspCoinPricePatternEnabled</TH><TH>&nbspCoinPricePattern</TH>
            <tr>
         <?php
         for($x = 0; $x < $userSettingsLen; $x++) {
           $ruleID = $userSettings[$x][28]; $userID = $userSettings[$x][0];
           $buyOrdersEnabled = $userSettings[$x][1]; $buyOrdersTop = $userSettings[$x][2]; $buyOrdersBtm = $userSettings[$x][3];
           $marketCapEnabled = $userSettings[$x][4];$marketCapTop = $userSettings[$x][5]; $marketCapBtm = $userSettings[$x][6];
           $hr1ChangeEnabled = $userSettings[$x][7]; $hr1ChangeTop = $userSettings[$x][8]; $hr1ChangeBtm = $userSettings[$x][9];
           $hr24ChangeEnabled = $userSettings[$x][10]; $hr24ChangeTop = $userSettings[$x][11]; $hr24ChangeBtm = $userSettings[$x][12];
           $d7ChangeEnabled = $userSettings[$x][13]; $d7ChangeTop = $userSettings[$x][14]; $d7ChangeBtm = $userSettings[$x][15];
           $coinPriceEnabled = $userSettings[$x][16];$coinPriceTop = $userSettings[$x][17];$coinPriceBtm = $userSettings[$x][18];
           $sellOrdersEnabled = $userSettings[$x][19];$sellOrdersTop = $userSettings[$x][20];$sellOrdersBtm = $userSettings[$x][21];
           $volumeEnabled = $userSettings[$x][22];$volumeTop = $userSettings[$x][23];$volumeBtm = $userSettings[$x][24];
           $buyCoin = $userSettings[$x][25];$sendEmail = $userSettings[$x][26];$bTCAmount = $userSettings[$x][27];
           $buyCoinOffsetEnabled = $userSettings[$x][29];$buyCoinOffsetPct = $userSettings[$x][30];
           $priceTrendEnabled = $userSettings[$x][31];$price4Trend = $userSettings[$x][32];$price3Trend = $userSettings[$x][33];$lastPriceTrend = $userSettings[$x][34];$livePriceTrend = $userSettings[$x][35];
           $active = $userSettings[$x][36];$disableUntil = $userSettings[$x][37];$baseCurrency = $userSettings[$x][38];$noOfCoinPurchase = $userSettings[$x][39];$timetoCancelBuy = $userSettings[$x][40];
           $buyType = $userSettings[$x][41];$timeToCancelBuyMins = $userSettings[$x][42];
           $buyPriceMinEnabled = $userSettings[$x][43];$buyPriceMin = $userSettings[$x][44];$limitToCoin = $userSettings[$x][45];
           $autoBuyCoinEnabled = $userSettings[$x][46];$autoBuyPrice = $userSettings[$x][47];
           $buyAmountOverrideEnabled = $userSettings[$x][48];$buyAmountOverride = $userSettings[$x][49];$newBuyPattern = $userSettings[$x][50];
           $sellRuleFixed = $userSettings[$x][51];$coinOrder = $userSettings[$x][52];$coinPricePatternEnabled = $userSettings[$x][53];$coinPricePattern = $userSettings[$x][54];
           //addBuyTableLine($userSettings[$x][28],$userSettings[$x][0],$userSettings[$x][1],$userSettings[$x][2],$userSettings[$x][3])
           if ($buyCoin == 0){
             echo "<td><a href='AddNewSetting.php?edit=".$ruleID."'><span class='glyphicon glyphicon-pencil' style='font-size:22px;'></span></a></td>";
             echo "<td><a href='AddNewSetting.php?copyRule=".$ruleID."'><span class='glyphicon glyphicon-copy' style='font-size:22px;'></span></a></td>";
             echo "<td><a href='AddNewSetting.php?delete=".$ruleID."'><span class='glyphicon glyphicon-trash' style='font-size:22px;'></span></a></td>";
             echo "<td>".$ruleID."</td>";echo "<td>".$userID."</td>";
             echo "<td>".$buyOrdersEnabled."</td>";echo "<td>".$buyOrdersTop."</td>";echo "<td>".$buyOrdersBtm."</td>";
             echo "<td>".$marketCapEnabled."</td>";echo "<td>".$marketCapTop."</td>";echo "<td>".$marketCapBtm."</td>";
             echo "<td>".$hr1ChangeEnabled."</td>";echo "<td>".$hr1ChangeTop."</td>";echo "<td>".$hr1ChangeBtm."</td>";
             echo "<td>".$hr24ChangeEnabled."</td>";echo "<td>".$hr24ChangeTop."</td>";echo "<td>".$hr24ChangeBtm."</td>";
             echo "<td>".$d7ChangeEnabled."</td>";echo "<td>".$d7ChangeTop."</td>";echo "<td>".$d7ChangeBtm."</td>";
             echo "<td>".$coinPriceEnabled."</td>";echo "<td>".$coinPriceTop."</td>";echo "<td>".$coinPriceBtm."</td>";
             echo "<td>".$sellOrdersEnabled."</td>";echo "<td>".$volumeTop."</td>";echo "<td>".$volumeEnabled."</td>";echo "<td>".$sellOrdersBtm."</td>";echo "<td>".$sellOrdersTop."</td>";
             echo "<td>".$volumeBtm."</td>";echo "<td>".$buyCoin."</td>";echo "<td>".$sendEmail."</td>";
             echo "<td>".$bTCAmount."</td><td>".$buyCoinOffsetEnabled."</td><td>".$buyCoinOffsetPct."</td>";
             echo "<td>".$priceTrendEnabled."</td><td>".$price4Trend."</td><td>".$price3Trend."</td><td>".$lastPriceTrend."</td><td>".$livePriceTrend."</td>";
             echo "<td>".$active."</td><td>".$disableUntil."</td><td>".$baseCurrency."</td><td>".$noOfCoinPurchase."</td><td>".$timetoCancelBuy."</td>";
             echo "<td>".$buyType."</td><td>".$timeToCancelBuyMins."</td><td>".$buyPriceMinEnabled."</td><td>".$buyPriceMin."</td>";
             echo "<td>".$limitToCoin."</td><td>".$autoBuyCoinEnabled."</td><td>".$autoBuyPrice."</td>";
             echo "<td>".$buyAmountOverrideEnabled."</td><td>".$buyAmountOverride."</td>";
             echo "<td>".$newBuyPattern."</td><td>".$sellRuleFixed."</td>";
             echo "<td>".$coinOrder."</td><td>".$coinPricePatternEnabled."</td><td>".$coinPricePattern."</td><tr>";
           }
         }
         echo "</table> <br><a href='AddNewSetting.php?addNew=Yes'><span class='glyphicon glyphicon-plus' style='font-size:48px;'></span></a>";
          displaySideColumn();?>


</body>
</html>
