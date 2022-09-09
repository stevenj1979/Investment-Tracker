<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
</head>
<?php require('includes/config.php');
include_once '../includes/newConfig.php';?>
<html>
<style>
<?php include 'style/style.css';
include_once ('../../../../SQLData.php'); ?>
</style>
<body>
<?php


//if not logged in redirect to login page
if(!$user->is_logged_in()){ header('Location: login.php'); exit(); }
displayHeader(7);
if(!empty($_GET['addNew'])){ $_GET['addNew'] = null; submitNewUser(); }
if(!empty($_GET['edit'])){ displayEdit($_GET['edit']); }
if(!empty($_GET['nUReady'])){ submitNewUser(); }
if(!empty($_GET['editedUserReady'])){
  if (!empty($_POST['publish'])){
    //Echo "this is a test".$_GET['editedUserReady'].$_POST['select'].$_POST['CPrice'];//displayEdit($_GET['editedUserReady']);
    $ruleID = $_GET['editedUserReady']; $coinPriceMatchNameID = $_POST['coinPriceMatchCmb'];
    echo "<BR> Test: $ruleID | $coinPriceMatchNameID";
    updateNameIDtoRule($ruleID,$coinPriceMatchNameID,"`CoinPriceMatchID`");
    //addpricePatterntoSQL($_GET['editedUserReady'], $_POST['select'], $_POST['CPrice'], $_POST['CPricebtm']);
  }elseif (!empty($_POST['remove'])){
    //Echo "this is a remove test".$_GET['editedUserReady'].$_POST['listbox'];displayEdit($_GET['editedUserReady']);
    removePricePatternfromSQL($_GET['editedUserReady'], $_POST['listbox']);
  }elseif (!empty($_POST['removeHr1'])){
      //Echo " ".$_POST['removeHr1'].$_POST['listbox1Hr'];
      remove1HrPatternfromSQL($_GET['editedUserReady'],$_POST['listbox1Hr']);
  }elseif (!empty($_POST['removeTrend'])){
      Echo " RemoveTrend: ".$_POST['removeTrend']." listBoxTrend: ".$_POST['listboxTrend'];
      removeTrendPatternfromSQL($_GET['editedUserReady'],$_POST['listboxTrend']);
  }elseif (!empty($_POST['publishHr1'])){
      //Echo " ".$_POST['publishHr1'].$_POST['selectCmbo1Hr1'].$_POST['selectCmbo1Hr2'].$_POST['selectCmbo1Hr3'].$_POST['selectCmbo1Hr4'];
      //if ($_POST['selectCmbo1Hr1'] == 2){$temp1 = '*';$temp2 = $_POST['selectCmbo1Hr2']; $temp3 = $_POST['selectCmbo1Hr3'];$temp4 = $_POST['selectCmbo1Hr4'];}
      //elseif ($_POST['selectCmbo1Hr2'] == 2){$temp1 = $_POST['selectCmbo1Hr1'];$temp2 = '*';$temp3 = $_POST['selectCmbo1Hr3'];$temp4 = $_POST['selectCmbo1Hr4'];}
      //elseif ($_POST['selectCmbo1Hr3'] == 2){$temp1 = $_POST['selectCmbo1Hr1'];$temp2 = $_POST['selectCmbo1Hr2'];$temp3 = '*';$temp4 = $_POST['selectCmbo1Hr4'];}
      //elseif ($_POST['selectCmbo1Hr4'] == 2){$temp1 = $_POST['selectCmbo1Hr1'];$temp2 = $_POST['selectCmbo1Hr2'];$temp3 = $_POST['selectCmbo1Hr3'];$temp4 = '*';}
      $temp1 = $_POST['selectCmbo1Hr1'];$temp2 = $_POST['selectCmbo1Hr2'];$temp3 = $_POST['selectCmbo1Hr3'];$temp4 = $_POST['selectCmbo1Hr4'];
      //Echo "$temp1 $temp2 $temp3 $temp4 ".$_GET['editedUserReady'];
      add1HrPatterntoSQL(str_replace("2","*",$temp1.$temp2.$temp3.$temp4), $_GET['editedUserReady']);
  }elseif (!empty($_POST['publishTrend'])){
      $coinPricePatternNameID = $_POST['coinPricePatternCmb'];
      $ruleID = $_GET['editedUserReady'];
      //addTrendPatterntoSQL($id,$_GET['editedUserReady']);
      updateNameIDtoRule($ruleID,$coinPricePatternNameID,"`CoinPricePatternID`");
  }else{
    //if (!empty($_POST['MarketCapEnable'])){if ($_POST['MarketCapEnable']== "Yes"){ $mCapEnChk = 1;}else{$mCapEnChk = 00;}}
    updateEditedUser();
  }
}
if(!empty($_GET['delete'])){ deleteItem($_GET['delete']); }
if(!empty($_GET['copyRule'])){ copyRule($_GET['copyRule']); }

function add1HrPatterntoSQL($pattern, $ruleID){
  $userID = $_SESSION['ID'];
  echo "$ruleID $symbol $price $userID";
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call add1HrPattern('$pattern', $ruleID, $userID);";
  //echo $sql;
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  header('Location: AddNewSetting.php?edit='.$ruleID);
}

function addTrendPatterntoSQL($pattern, $ruleID){
  $userID = $_SESSION['ID'];
  echo "$ruleID $symbol $price $userID";
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = ";";
  echo $sql;
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  header('Location: AddNewSetting.php?edit='.$ruleID);
}

function updateNameIDtoRule($ruleID, $nameID, $table){
  //$userID = $_SESSION['ID'];
  //echo "$ruleID $symbol $price $userID";
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "UPDATE `BuyRules` SET $table = $nameID WHERE `ID` = $ruleID;";
  echo $sql;
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  header('Location: AddNewSetting.php?edit='.$ruleID);
}

function removePricePatternfromSQL($ruleID, $price){
  $splitPrice = explode(':',$price);
  $newPrice = $splitPrice[1]; $symbol = $splitPrice[0]; $lowPrice = $splitPrice[2];
  $userID = $_SESSION['ID'];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "DELETE FROM `CoinPriceMatchRules` WHERE `BuyRuleID` = $ruleID and `CoinPriceMatchID` = (
    select `ID` from `CoinPriceMatch` where `Price` = $newPrice and `UserID` = $userID and `LowPrice` = $lowPrice and `CoinID` = (
      SELECT `ID` FROM `Coin` WHERE `Symbol` = '$symbol' and `BuyCoin` = 1))";
  echo $sql;
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  header('Location: AddNewSetting.php?edit='.$ruleID);
}

function removeTrendPatternfromSQL($ruleID, $pattern){
  $userID = $_SESSION['ID'];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "DELETE FROM `CoinPricePatternRules` WHERE `PatternID` = (SELECT `ID` FROM `CoinPricePattern` WHERE `CoinPattern` = '$pattern') and `BuyRuleID` = $ruleID and `UserID` = $userID";
  echo $sql;
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  header('Location: AddNewSetting.php?edit='.$ruleID);
}

function remove1HrPatternfromSQL($ruleID, $pattern){
  $userID = $_SESSION['ID'];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "DELETE FROM `Coin1HrPatternRules` WHERE `Coin1HrPatternID` = (SELECT `ID` FROM `Coin1HrPattern` WHERE `Pattern` = '$pattern') and `BuyRuleID` = $ruleID and `UserID` = $userID";
  //echo $sql;
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  header('Location: AddNewSetting.php?edit='.$ruleID);
}

function deleteItem($id){
  $_GET['nUReady'] = null;
  // Create connection
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $userID = $_SESSION['ID'];
  $sql = "DELETE FROM `BuyRules` WHERE `ID` = $id";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();
  header('Location: BuySettings.php');
}

function copyRule($ID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $userID = $_SESSION['ID'];
  $sql = "INSERT INTO `BuyRules`(`RuleName`, `UserID`, `BuyOrdersEnabled`, `BuyOrdersTop`, `BuyOrdersBtm`, `MarketCapEnabled`, `MarketCapTop`, `MarketCapBtm`, `1HrChangeEnabled`, `1HrChangeTop`, `1HrChangeBtm`, `24HrChangeEnabled`, `24HrChangeTop`
    , `24HrChangeBtm`, `7DChangeEnabled`, `7DChangeTop`, `7DChangeBtm`, `CoinPriceEnabled`, `CoinPriceTop`, `CoinPriceBtm`, `SellOrdersEnabled`, `SellOrdersTop`, `SellOrdersBtm`, `VolumeEnabled`, `VolumeTop`, `VolumeBtm`, `BuyCoin`, `SendEmail`, `BTCAmount`
    , `BuyType`, `CoinOrder`, `BuyCoinOffsetEnabled`, `BuyCoinOffsetPct`, `PriceTrendEnabled`, `Price4Trend`, `Price3Trend`, `LastPriceTrend`, `LivePriceTrend`, `BuyPriceMinEnabled`, `BuyPriceMin`, `LimitToCoin`, `LimitToCoinID`,`AutoBuyCoinEnabled`
    , `AutoBuyCoinPct`, `BuyAmountOverrideEnabled`, `BuyAmountOverride`, `NewBuyPattern`, `SellRuleFixed`, `OverrideDailyLimit`, `CoinPricePatternEnabled`, `CoinPricePattern`, `1HrChangeTrendEnabled`, `1HrChangeTrend`, `CoinPriceMatchPattern`, `CoinPriceMatchID`
    , `CoinPricePatternID`, `Coin1HrPatternID`, `BuyRisesInPrice`, `DisableUntil`, `OverrideDisableRule`, `LimitBuyAmountEnabled`, `LimitBuyAmount`, `OverrideCancelBuyTimeEnabled`, `OverrideCancelBuyTimeMins`, `LimitBuyTransactionsEnabled`, `LimitBuyTransactions`
    , `NoOfBuyModeOverrides`, `CoinModeOverridePriceEnabled`, `BuyModeActivate`, `CoinMode`, `OverrideCoinAllocation`, `OneTimeBuyRule`, `LimitToBaseCurrency`, `EnableRuleActivationAfterDip`, `24HrPriceDipPct`, `7DPriceDipPct`, `BuyAmountCalculationEnabled`
    , `TotalPurchasesPerRule`, `RedirectPurchasesToSpread`, `RedirectPurchasesToSpreadID`, `PctFromLowBuyPriceEnabled`, `NoOfHoursFlatEnabled`, `NoOfHoursFlat`, `PctOverMinPrice`,`DefaultRule`,`MultiSellRuleEnabled`,`MultiSellRuleTemplateID`)
Select `RuleName`, `UserID`, `BuyOrdersEnabled`, `BuyOrdersTop`, `BuyOrdersBtm`, `MarketCapEnabled`, `MarketCapTop`, `MarketCapBtm`, `1HrChangeEnabled`, `1HrChangeTop`, `1HrChangeBtm`, `24HrChangeEnabled`, `24HrChangeTop`, `24HrChangeBtm`, `7DChangeEnabled`, `7DChangeTop`
, `7DChangeBtm`, `CoinPriceEnabled`, `CoinPriceTop`, `CoinPriceBtm`, `SellOrdersEnabled`, `SellOrdersTop`, `SellOrdersBtm`, `VolumeEnabled`, `VolumeTop`, `VolumeBtm`, 0, `SendEmail`, `BTCAmount`, `BuyType`, `CoinOrder`, `BuyCoinOffsetEnabled`, `BuyCoinOffsetPct`
, `PriceTrendEnabled`, `Price4Trend`, `Price3Trend`, `LastPriceTrend`, `LivePriceTrend`, `BuyPriceMinEnabled`, `BuyPriceMin`, `LimitToCoin`, `LimitToCoinID`, `AutoBuyCoinEnabled`, `AutoBuyCoinPct`, `BuyAmountOverrideEnabled`, `BuyAmountOverride`, `NewBuyPattern`
, `SellRuleFixed`, `OverrideDailyLimit`, `CoinPricePatternEnabled`, `CoinPricePattern`, `1HrChangeTrendEnabled`, `1HrChangeTrend`, `CoinPriceMatchPattern`, `CoinPriceMatchID`, `CoinPricePatternID`, `Coin1HrPatternID`, `BuyRisesInPrice`, `DisableUntil`, `OverrideDisableRule`
, `LimitBuyAmountEnabled`, `LimitBuyAmount`, `OverrideCancelBuyTimeEnabled`, `OverrideCancelBuyTimeMins`, `LimitBuyTransactionsEnabled`, `LimitBuyTransactions`, `NoOfBuyModeOverrides`, `CoinModeOverridePriceEnabled`, `BuyModeActivate`, `CoinMode`, `OverrideCoinAllocation`
, `OneTimeBuyRule`, `LimitToBaseCurrency`, `EnableRuleActivationAfterDip`, `24HrPriceDipPct`, `7DPriceDipPct`, `BuyAmountCalculationEnabled`, `TotalPurchasesPerRule`, `RedirectPurchasesToSpread`, `RedirectPurchasesToSpreadID`, `PctFromLowBuyPriceEnabled`, `NoOfHoursFlatEnabled`
, `NoOfHoursFlat`, `PctOverMinPrice`,`DefaultRule`,`MultiSellRuleEnabled`,`MultiSellRuleTemplateID`
from `BuyRules`
where `ID` = $ID";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();
  header('Location: BuySettings.php');

}

function submitNewUser(){

  $_GET['nUReady'] = null;
  // Create connection
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $userID = $_SESSION['ID'];
  $sql = "INSERT INTO `BuyRules`(`UserID`) VALUES ($userID)";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();
  //header('Location: BuySettings.php');
}

function postDataYesNo($postValue){
  if (!empty($postValue)){
    if ($postValue == "Yes"){
      return 1;
    }else{
      return 0;
    }
  }else{
    return 0;
  }
}


function postData($postValue){
  if (!empty($postValue)){
    return $postValue;
  }else{
    return 0;
  }
}

function withDefaultVal($val, $default){
  if (!isset($val)){ return $default;}
}

function updateEditedUser(){

  $id = $_GET['editedUserReady'];
  $_GET['editedUserReady'] = null;

  //if (!empty($_POST['MarketCapEnable'])){if ($_POST['MarketCapEnable'] == "Yes"){$MarketCapEnable = 1;}else{$MarketCapEnable = 0;}}else{ $MarketCapEnable = 0;}
  $MarketCapEnable = postDataYesNo($_POST['MarketCapEnable']);
  $MarketCapTop = postData($_POST['MarketCapTop']);
  $MarketCapBtm = postData($_POST['MarketCapBtm']);
  //if (!empty($_POST['MarketCapTop'])){$MarketCapTop = $_POST['MarketCapTop'];}else{$MarketCapTop = 0;}
  //if (!empty($_POST['MarketCapBtm'])){$MarketCapBtm = $_POST['MarketCapBtm'];}else{$MarketCapBtm = 0;}

  //if (!empty($_POST['VolumeEnable'])){if ($_POST['VolumeEnable'] == "Yes"){$VolumeEnable = 1;}else{$VolumeEnable = 0;}}else{ $VolumeEnable = 0;}
  //if (!empty($_POST['VolumeTop'])){$VolumeTop = $_POST['VolumeTop'];}else{$VolumeTop = 0;}
  //if (!empty($_POST['VolumeBtm'])){$VolumeBtm = $_POST['VolumeBtm'];}else{$VolumeBtm = 0;}
  $VolumeEnable = postDataYesNo($_POST['VolumeEnable']);
  $VolumeTop = postData($_POST['VolumeTop']);
  $VolumeBtm = postData($_POST['VolumeBtm']);

  //if (!empty($_POST['BuyOrdersEnabled'])){if ($_POST['BuyOrdersEnabled'] == "Yes"){$BuyOrdersEnabled = 1;}else{$BuyOrdersEnabled = 0;}}else{ $BuyOrdersEnabled = 0;}
  //if (!empty($_POST['BuyOrdersEnabled'])){$BuyOrdersEnabled = $_POST['BuyOrdersEnabled'];}else{$BuyOrdersEnabled = 0;}
  //if (!empty($_POST['BuyOrdersTop'])){$BuyOrdersTop = $_POST['BuyOrdersTop'];}else{$BuyOrdersTop = 0;}
  //if (!empty($_POST['BuyOrdersBtm'])){$BuyOrdersBtm = $_POST['BuyOrdersBtm'];}else{$BuyOrdersBtm = 0;}
  $BuyOrdersEnabled = postDataYesNo($_POST['BuyOrdersEnabled']);
  $BuyOrdersTop = postData($_POST['BuyOrdersTop']);
  $BuyOrdersBtm = postData($_POST['BuyOrdersBtm']);

  //if (!empty($_POST['1HrEnable'])){if ($_POST['1HrEnable'] == "Yes"){$oneHrEnable = 1;}else{$oneHrEnable = 0;}}else{ $oneHrEnable = 0;}
  //if (!empty($_POST['1HrEnable'])){$oneHrEnable = $_POST['1HrEnable'];}else{$oneHrEnable = 0;}
  //if (!empty($_POST['PriceChange1HrTop'])){$PriceChange1HrTop = $_POST['PriceChange1HrTop'];}else{$PriceChange1HrTop = 0;}
  //if (!empty($_POST['PriceChange1HrBtm'])){$PriceChange1HrBtm = $_POST['PriceChange1HrBtm'];}else{$PriceChange1HrBtm = 0;}
  $oneHrEnable = postDataYesNo($_POST['1HrEnable']);
  $PriceChange1HrTop = postData($_POST['PriceChange1HrTop']);
  $PriceChange1HrBtm = postData($_POST['PriceChange1HrBtm']);

  $overrideCoinAllocationEnable = postDataYesNo($_POST['OverrideCoinAllocationEnabled']);
  $oneTimeBuyRuleEnable = postDataYesNo($_POST['OneTimeBuyRuleEnabled']);
  //if (!empty($_POST['24HrEnable'])){if ($_POST['24HrEnable'] == "Yes"){$t4HrEnable = 1;}else{$t4HrEnable = 0;}}else{ $t4HrEnable = 0;}
  //if (!empty($_POST['24HrEnable'])){$t4HrEnable = $_POST['24HrEnable'];}else{$t4HrEnable = 0;}
  //if (!empty($_POST['PriceChange24HrTop'])){$PriceChange24HrTop = $_POST['PriceChange24HrTop'];}else{$PriceChange24HrTop = 0;}
  //if (!empty($_POST['PriceChange24HrBtm'])){$PriceChange24HrBtm = $_POST['PriceChange24HrBtm'];}else{$PriceChange24HrBtm = 0;}

  $t4HrEnable = postDataYesNo($_POST['24HrEnable']);
  $PriceChange24HrTop = postData($_POST['PriceChange24HrTop']);
  $PriceChange24HrBtm = postData($_POST['PriceChange24HrBtm']);

  //if (!empty($_POST['7DEnable'])){if ($_POST['7DEnable'] == "Yes"){$sDEnable = 1;}else{$sDEnable = 0;}}else{ $sDEnable = 0;}
  //if (!empty($_POST['7DEnable'])){$sDEnable = $_POST['7DEnable'];}else{$sDEnable = 0;}
  //if (!empty($_POST['PriceChange7DTop'])){$PriceChange7DTop = $_POST['PriceChange7DTop'];}else{$PriceChange7DTop = 0;}
  //if (!empty($_POST['PriceChange7DBtm'])){$PriceChange7DBtm = $_POST['PriceChange7DBtm'];}else{$PriceChange7DBtm = 0;}

  $sDEnable = postDataYesNo($_POST['7DEnable']);
  $PriceChange7DTop = postData($_POST['PriceChange7DTop']);
  $PriceChange7DBtm = postData($_POST['PriceChange7DBtm']);

  //if (!empty($_POST['BuyPatternEnabled'])){if ($_POST['BuyPatternEnabled'] == "Yes"){$BuyPatternEnabled = 1;}else{$BuyPatternEnabled = 0;}}else{ $BuyPatternEnabled = 0;}
  $BuyPatternEnabled = postDataYesNo($_POST['BuyPatternEnabled']);
  //if (!empty($_POST['PriceDiff1Enable'])){if ($_POST['PriceDiff1Enable'] == "Yes"){$PriceDiff1Enable = 1;}else{$PriceDiff1Enable = 0;}}else{ $PriceDiff1Enable = 0;}
  $PriceDiff1Enable = postDataYesNo($_POST['PriceDiff1Enable']);
  //if (!empty($_POST['PriceDiff1Enable'])){$PriceDiff1Enable = $_POST['PriceDiff1Enable'];}else{$PriceDiff1Enable = 0;}
  //if (!empty($_POST['PriceDiff1Top'])){$PriceDiff1Top = $_POST['PriceDiff1Top'];}else{$PriceDiff1Top = 0;}
  //if (!empty($_POST['PriceDiff1Btm'])){$PriceDiff1Btm = $_POST['PriceDiff1Btm'];}else{$PriceDiff1Btm = 0;}
  $PriceDiff1Top = postData($_POST['PriceDiff1Top']);
  $PriceDiff1Btm = postData($_POST['PriceDiff1Btm']);

  //if (!empty($_POST['BuyCoinOffsetEnabled'])){if ($_POST['BuyCoinOffsetEnabled'] == "Yes"){$BuyCoinOffsetEnable = 1;}else{$BuyCoinOffsetEnable = 0;}}else{ $BuyCoinOffsetEnable = 0;}
  $BuyCoinOffsetEnable = postDataYesNo($_POST['BuyCoinOffsetEnabled']);
  //if (!empty($_POST['BuyCoinOffsetPct'])){ $BuyCoinOffsetPct = $_POST['BuyCoinOffsetPct'];}else{ $BuyCoinOffsetPct = 0;}
  $BuyCoinOffsetPct = postData($_POST['BuyCoinOffsetPct']);
  $SellRuleFixed = postData($_POST['sellRuleFixed']);

  if (!empty($_POST['sendEmail'])){if ($_POST['sendEmail'] == "Yes"){$sendEmail = 1;}else{$sendEmail = 0;}}else{ $sendEmail = 0;}
  $coinPricePatternEnabled = postDataYesNo($_POST['CoinPricePatternEnabled']);
  $coinPricePattern = $_POST['CoinPricePattern'];
  //if (!empty($_POST['PriceTrendEnabled'])){if ($_POST['PriceTrendEnabled'] == "Yes"){$priceTrendEnabled = 1;}else{$priceTrendEnabled = 0;}}else{ $priceTrendEnabled = 0;}
  $priceTrendEnabled = postDataYesNo($_POST['PriceTrendEnabled']);
  $hr1ChangeEnabled = postDataYesNo($_POST['Hr1ChangeEnabled']);
  if (!empty($_POST['Price4Trend'])){
    if ($_POST['Price4Trend'] == "Up"){$price4Trend = 1;}elseif ($_POST['Price4Trend'] == "Down"){$price4Trend = -1;
    }else{$price4Trend = 0;}}else{ $price4Trend = 0;}
  if (!empty($_POST['Price3Trend'])){
    if ($_POST['Price3Trend'] == "Up"){$price3Trend = 1;}elseif ($_POST['Price3Trend'] == "Down"){$price3Trend = -1;
    }else{$price3Trend = 0;}}else{ $price3Trend = 0;}
  if (!empty($_POST['LastPriceTrend'])){
    if ($_POST['LastPriceTrend'] == "Up"){$lastPriceTrend = 1;}elseif ($_POST['LastPriceTrend'] == "Down"){$lastPriceTrend = -1;
    }else{$lastPriceTrend = 0;}}else{ $lastPriceTrend = 0;}
  if (!empty($_POST['LivePriceTrend'])){
    if ($_POST['LivePriceTrend'] == "Up"){$livePriceTrend = 1;}elseif ($_POST['LivePriceTrend'] == "Down"){$livePriceTrend = -1;
    }else{$livePriceTrend = 0;}}else{ $livePriceTrend = 0;}
  if (!empty($_POST['buyCoin'])){if ($_POST['buyCoin'] == "Yes"){$buyCoin = 1;}else{$buyCoin = 0;}}else{ $buyCoin = 0;}
  //if (!empty($_POST['buyCoin'])){ $buyCoin = $_POST['buyCoin'];}else{ $buyCoin = 0;}
  if (!empty($_POST['bTCBuyAmount'])){ $bTCBuyAmount = $_POST['bTCBuyAmount'];}else{ $bTCBuyAmount = 0;}
  //$disabledUntil = postData($_POST['disabledUntil']);
  //$nBaseCurrency = postData($_POST['baseCurrency']);
  //$NoOfCoinPurchase = postData($_POST['NoOfCoinPurchase']);
  //$TimetoCancelBuy = postData($_POST['TimetoCancelBuy']);
  //$BuyType = postData($_POST['BuyType']);
  //$TimeToCancelBuyMins = postData($_POST['TimeToCancelBuyMins']);
  $BuyPriceMinEnabled = postDataYesNo($_POST['BuyPriceMinEnabled']);
  $BuyPriceMin = postData($_POST['BuyPriceMin']);
  $limitToCoin = $_POST['LimitToCoinID'];
  $limitToBaseCurrency = $_POST['limitToBaseCurrency'];
  $autoBuyCoinEnabled = postDataYesNo($_POST['AutoBuyEnabled']);
  $autoBuyPrice = $_POST['AutoBuyPrice'];
  $buyAmountOverrideEnabled = postDataYesNo($_POST['BuyAmountOverrideEnabled']);
  $buyAmountOverride = postData($_POST['BuyAmountOverride']);

  $newBuyPattern = '';
  $coinOrder = postData($_POST['CoinOrderTxt']);
  $hr1ChangePattern = $_POST['Hr1ChangePattern'];
  $overrideDailyLimitEnabled = postDataYesNo($_POST['OverrideDailyLimitEnabled']);
  $coinPctFromLowBuyPriceEnabled = postDataYesNo($_POST['CoinPctFromLowBuyPriceEnabled']);
  $pctFromLowBuyPrice = postData($_POST['PctFromLowBuyPrice']);
  $coinHoursFlatEnabled = postDataYesNo($_POST['CoinHoursFlatEnabled']);
    $coinHoursFlat = postData($_POST['CoinHoursFlat']);
    $ruleName = $_POST['RuleName'];
    $reEnableBuyRuleAfterDip = postDataYesNo($_POST['ReEnableBuyRuleAfterDip']);
    $priceDip24Hr  = postData($_POST['PriceDip24Hr']);
    $priceDip7D = postData($_POST['PriceDip7D']);
    $priceDipPctTolerance  = postData($_POST['PriceDipPctTolerance']);
    $priceDipHoursFlat = postData($_POST['PriceDipHoursFlat']);
  $overrideCancelBuyTimeEnabled = 1;
  $buyRisesInPrice   = postData($_POST['BuyRisesInPrice']);
  $overrideCancelBuyTimeMins   = postData($_POST['TimeToCancelMins']);
  $multiSellEnabled = postDataYesNo($_POST['MultiSellRulesEnabled']);
  $multiSellTemplate = $_POST['MultiSellRules'];
    //$ruleID =  $_POST['RuleID'];
  //$nActive = $_POST['nActive'];
  // Create connection
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $userID = $_SESSION['ID'];
  $sql = "UPDATE `BuyRules` SET `UserID`=$userID,`MarketCapEnabled`=$MarketCapEnable,`MarketCapTop`=$MarketCapTop,`MarketCapBtm`=$MarketCapBtm,`VolumeEnabled`=$VolumeEnable,`VolumeTop`=$VolumeTop,
  `VolumeBtm`=$VolumeBtm,`BuyOrdersEnabled`=$BuyOrdersEnabled,`BuyOrdersTop`=$BuyOrdersTop,`BuyOrdersBtm`=$BuyOrdersBtm,`1HrChangeEnabled`=$oneHrEnable,`1HrChangeTop`=$PriceChange1HrTop,`1HrChangeBtm`=$PriceChange1HrBtm,
  `24HrChangeEnabled`=$t4HrEnable,`24HrChangeTop`=$PriceChange24HrTop,`24HrChangeBtm`=$PriceChange24HrBtm,`7DChangeEnabled`=$sDEnable,`7DChangeTop`=$PriceChange7DTop,`7DChangeBtm`=$PriceChange7DBtm,
  `CoinPriceEnabled`=$PriceDiff1Enable,`CoinPriceTop`=$PriceDiff1Top,`CoinPriceBtm`=$PriceDiff1Btm, `SendEmail`=$sendEmail, `BuyCoin`=$buyCoin, `BTCAmount`=$bTCBuyAmount, `BuyCoinOffsetPct`=$BuyCoinOffsetPct,
  `BuyCoinOffsetEnabled`=$BuyCoinOffsetEnable ,`PriceTrendEnabled` = $priceTrendEnabled,`Price4Trend` = $price4Trend,`Price3Trend` = $price3Trend,`LastPriceTrend` = $lastPriceTrend,`LivePriceTrend` = $livePriceTrend,
  `BuyPriceMinEnabled`=$BuyPriceMinEnabled,`BuyPriceMin`=$BuyPriceMin, `LimitToCoin` = '$limitToCoin', `AutoBuyCoinEnabled` = $autoBuyCoinEnabled, `BuyAmountOverrideEnabled` = $buyAmountOverrideEnabled,
  `BuyAmountOverride` = $buyAmountOverride, `NewBuyPattern` = '$newBuyPattern',`SellRuleFixed` = $SellRuleFixed
  ,`LimitToCoinID` = CASE
	       WHEN '$limitToCoin' = 'ALL'  THEN 'ALL'
         ELSE (SELECT `ID` FROM `Coin` WHERE `Symbol` = 'ALL' and `BuyCoin` = 1)
         END
  , `CoinOrder` = $coinOrder,
  `CoinPricePatternEnabled` = $coinPricePatternEnabled, `CoinPricePattern` = '$coinPricePattern', `1HrChangeTrendEnabled` = $hr1ChangeEnabled, `1HrChangeTrend` = '$hr1ChangePattern', `OverrideDailyLimit` = $overrideDailyLimitEnabled
  ,`OverrideCoinAllocation` = $overrideCoinAllocationEnable, `OneTimeBuyRule` = $oneTimeBuyRuleEnable, `LimitToBaseCurrency` = '$limitToBaseCurrency',`PctFromLowBuyPriceEnabled` = $coinPctFromLowBuyPriceEnabled, `NoOfHoursFlatEnabled` = $coinHoursFlatEnabled
  ,`NoOfHoursFlat` = $coinHoursFlat,  `PctOverMinPrice` = $pctFromLowBuyPrice, `RuleName` = '$ruleName',`EnableRuleActivationAfterDip` = $reEnableBuyRuleAfterDip, `OverrideCancelBuyTimeEnabled` = $overrideCancelBuyTimeEnabled
  , `OverrideCancelBuyTimeMins` = $overrideCancelBuyTimeMins, `BuyRisesInPrice` = $buyRisesInPrice,`MultiSellRuleEnabled` = $multiSellEnabled,`MultiSellRuleTemplateID` = $multiSellTemplate
  WHERE `ID` = $id";
  print_r($sql);

  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `PriceDipSettings` SET `PriceDipEnable24Hour`=$priceDip24Hr,`PriceDipDisable24Hour`=($priceDip24Hr+10),`PriceDipEnable7Day`=$priceDip7D,`PriceDipDisable7Day`=($priceDip7D+10),`PctTolerance`=$priceDipPctTolerance,`HoursFlat`=$priceDipHoursFlat
          WHERE `UserID`=$userID";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();
  //http://www.investment-tracker.net/Investment-Tracker/Cryptobot/1/AddNewSetting.php?edit=164
  //header('Location: AddNewSetting.php?edit='.$id);
}



function getRules($id){

  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `UserID`,`BuyOrdersEnabled`,`BuyOrdersTop`,`BuyOrdersBtm`,`MarketCapEnabled`,`MarketCapTop`,`MarketCapBtm`,`1HrChangeEnabled`,`1HrChangeTop`,`1HrChangeBtm`,`24HrChangeEnabled`,
`24HrChangeTop`,`24HrChangeBtm`,`7DChangeEnabled`,`7DChangeTop`,`7DChangeBtm`,`CoinPriceEnabled`,`CoinPriceTop`,`CoinPriceBtm`,`SellOrdersEnabled`,`SellOrdersTop`,`SellOrdersBtm`,`VolumeEnabled`,`VolumeTop`,
`VolumeBtm`,`BuyCoin`,`SendEmail`,`BTCAmount`,`RuleID`,`BuyCoinOffsetEnabled`,`BuyCoinOffsetPct`,`PriceTrendEnabled`, `Price4Trend`, `Price3Trend`, `LastPriceTrend`, `LivePriceTrend`
, `Active`, `DisableUntil`, `BaseCurrency`, `NoOfCoinPurchase`, `TimetoCancelBuy`, `BuyType`, `TimeToCancelBuyMins`, `BuyPriceMinEnabled`, `BuyPriceMin`,`LimitToCoin`,`AutoBuyCoinEnabled`,`AutoBuyPrice`
,`BuyAmountOverrideEnabled`,`BuyAmountOverride`,`NewBuyPattern`,`SellRuleFixed`, `CoinOrder`,`CoinPricePatternEnabled`,`CoinPricePattern`,`1HrChangeTrendEnabled`,`1HrChangeTrend`,`OverrideDailyLimit`
,`NameCpmn` as `CoinPriceMatchName`,`CoinPriceMatchID`,`CoinPricePatternID`, `NameCppn` as `CoinPricePatternName`,`Coin1HrPatternID`,`NameC1hPn` as `Coin1HrPatternName`,`OverrideCoinAllocation`,`OneTimeBuyRule`,`LimitToBaseCurrency`
,`PctFromLowBuyPriceEnabled`,`PctOverMinPrice`,`NoOfHoursFlatEnabled`,`NoOfHoursFlat`,`RuleName`,`EnableRuleActivationAfterDip`,`PriceDipEnable24Hour`,`PriceDipEnable7Day`,`PctTolerance`,`HoursFlat`,`BuyRisesInPrice`,`OverrideCancelBuyTimeEnabled`
,`OverrideCancelBuyTimeMins`,`TimeToCancelBuyMins`,`MultiSellRuleEnabled`,`MultiSellRuleTemplateID`
FROM `View13_UserBuyRules` WHERE `RuleID` = $id order by `CoinOrder` ASC";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  //print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['UserID'],$row['BuyOrdersEnabled'],$row['BuyOrdersTop'],$row['BuyOrdersBtm'],$row['MarketCapEnabled'],$row['MarketCapTop'],//5
      $row['MarketCapBtm'],$row['1HrChangeEnabled'],$row['1HrChangeTop'],$row['1HrChangeBtm'],$row['24HrChangeEnabled'],$row['24HrChangeTop'],$row['24HrChangeBtm'],//12
      $row['7DChangeEnabled'],$row['7DChangeTop'],$row['7DChangeBtm'],$row['CoinPriceEnabled'],$row['CoinPriceTop'],$row['CoinPriceBtm'],$row['SellOrdersEnabled'], //19
      $row['SellOrdersTop'],$row['SellOrdersBtm'],$row['VolumeEnabled'],$row['VolumeTop'],$row['VolumeBtm'],$row['BuyCoin'],$row['SendEmail'],$row['BTCAmount'],$row['RuleID']//28
     ,$row['BuyCoinOffsetEnabled'],$row['BuyCoinOffsetPct'],$row['PriceTrendEnabled'],$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend']//35
     ,$row['Active'],$row['DisableUntil'],$row['BaseCurrency'],$row['NoOfCoinPurchase'],$row['TimetoCancelBuy'],$row['BuyType'],$row['TimeToCancelBuyMins'],$row['BuyPriceMinEnabled'],$row['BuyPriceMin']//44
     ,$row['LimitToCoin'],$row['AutoBuyCoinEnabled'],$row['AutoBuyPrice'],$row['BuyAmountOverrideEnabled'],$row['BuyAmountOverride'],$row['NewBuyPattern'],$row['SellRuleFixed'],$row['CoinOrder']//52
     ,$row['CoinPricePatternEnabled'],$row['CoinPricePattern'],$row['1HrChangeTrendEnabled'],$row['1HrChangeTrend'],$row['OverrideDailyLimit'],$row['CoinPriceMatchName'],$row['CoinPriceMatchID'] //59
     ,$row['CoinPricePatternID'],$row['CoinPricePatternName'],$row['Coin1HrPatternID'],$row['Coin1HrPatternName'],$row['OverrideCoinAllocation'],$row['OneTimeBuyRule'],$row['LimitToBaseCurrency'] //66
     ,$row['PctFromLowBuyPriceEnabled'],$row['PctOverMinPrice'],$row['NoOfHoursFlatEnabled'],$row['NoOfHoursFlat'],$row['RuleName'],$row['EnableRuleActivationAfterDip'],$row['PriceDipEnable24Hour']//73
     ,$row['PriceDipEnable7Day'],$row['PctTolerance'],$row['HoursFlat'],$row['BuyRisesInPrice'],$row['OverrideCancelBuyTimeEnabled'],$row['OverrideCancelBuyTimeMins'],$row['TimeToCancelBuyMins'] //80
     ,$row['MultiSellRuleEnabled'],$row['MultiSellRuleTemplateID']); //82
  }
  $conn->close();
  return $tempAry;
}

function getPricePatternBuy($id){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `BuyRuleID`,`SellRuleID`,`CoinID`,`Price`,`Symbol`,`UserID`,`LowPrice` FROM `CoinPriceMatchView` WHERE (`BuyRuleID` = $id )";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  //print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['BuyRuleID'],$row['SellRuleID'],$row['CoinID'],$row['Price'],$row['Symbol'],$row['UserID'],$row['LowPrice']);
  }
  $conn->close();
  return $tempAry;
}

function get1HrchangeBuy($id){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `BuyRuleID`,`SellRuleID`,`Pattern`,`UserID` FROM `Coin1HrPatternView` WHERE `BuyRuleID` = $id ";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  //print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['BuyRuleID'],$row['SellRuleID'],$row['Pattern'],$row['UserID']);
  }
  $conn->close();
  return $tempAry;
}

function getPriceTrendBuy($id){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `BuyRuleID`,`SellRuleID`,`CoinPattern`,`UserID` FROM `CoinPricePatternView` WHERE `BuyRuleID` = $id ";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  //print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['BuyRuleID'],$row['SellRuleID'],$row['CoinPattern'],$row['UserID']);
  }
  $conn->close();
  return $tempAry;
}

function getSymbols(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Symbol` FROM `Coin` WHERE `BuyCoin` = 1";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  //print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Symbol']);
  }
  $conn->close();
  return $tempAry;
}

function addNewText($RealName, $idName, $value, $tabIndex, $pHoolder, $longText, $enabled){
  $readOnly = "";
  //echo "<BR> ENABLED: ".$enabled;
  if ($enabled == 0){$readOnly = " style='color:Gray' readonly ";}
  if ($longText == True){ $textClass = 'enableTextBoxLong'; $divClass = 'settingsformLong'; } else {$textClass = 'enableTextBox'; $divClass = 'settingsform';}
  echo "<input type='text' name='".$idName."' id='".$idName."' class='".$textClass."' placeholder='$pHoolder' $readOnly value='".$value."' tabindex='".$tabIndex."'>
  <label for='$idName'>".$RealName."</label>";

}

function addNewTwoOption($RealName, $idName, $value){
  if ($value == 1 || $value == 'Yes' ){ $option1 = "Yes"; $option2 = "No";}else{$option1 = "No"; $option2 = "Yes";}
  echo "<select name='$idName' id='$idName' class='enableTextBox'>
   <option value='".$option1."'>".$option1."</option>
    <option value='".$option2."'>".$option2."</option></select>
    <label for='$idName'>$RealName</label>";
}

function addNewThreeOption($RealName, $idName, $value){

  if ($value == 1){$nOption1 = "Up"; $nOption2 = "Equal";$nOption3 = "Down";}
  elseif ($RealName == -1){$nOption1 = "Down"; $nOption2 = "Equal";$nOption3 = "Up";}
  else{$nOption1 = "Equal"; $nOption2 = "Down";$nOption3 = "Up";}
  echo "<select name='$idName' id='$idName' class='enableTextBox'>
    <option value='".$nOption1."'>".$nOption1."</option>
    <option value='".$nOption2."'>".$nOption2."</option>
    <option value='".$nOption3."'>".$nOption3."</option></select>
    <label for='$idName'>$RealName</label>";
}

function displayAutoListBox($tempAry){
  $tempCount = count($tempAry);
  for ($i=0; $i<$tempCount; $i++){
     $symbol = $tempAry[$i][3]; $topPrice = $tempAry[$i][1]; $bottomPrice = $tempAry[$i][2];
     $result = $symbol.":".$topPrice.":".$bottomPrice;

      echo "<option value='$symbol'>$result</option>";
  }
}

function displayListBox($tempAry){
  $tempCount = count($tempAry);
  for ($i=0; $i<$tempCount; $i++){
    $price = $tempAry[$i][3]; $symbol = $tempAry[$i][4]; $lowPrice = $tempAry[$i][6];
    $result = $symbol.":".$price.":".$lowPrice;

      echo "<option value='$result'>$result</option>";
  }
}

function displayListBoxNormal($tempAry, $num, $name, $enabled){
  $tempCount = count($tempAry);
  //$symbolListCount = count($symbolList);
  $readOnly = "";
  //echo "<BR> ENABLED: ".$enabled;
  if ($enabled == 0){$readOnly = " style='color:Gray' readonly ";}
  Echo "<select name='$name' size='3' $readOnly>";
  for ($i=0; $i<$tempCount; $i++){
    $result = $tempAry[$i][$num]; //$symbol = $tempAry[$i][4]; $result = $symbol.":".$price;

      echo "<option value='$result'>$result</option>";
  }
  echo "</select>";
}

function displaySymbols($symbolList,$num, $name, $enabled, $num2, $selected){
  $symbolListCount = count($symbolList);
  //$symbolListCount = count($symbolList);
  $readOnly = "";
  //echo "<BR> ENABLED: ".$enabled;
  if ($enabled == 0){$readOnly = " style='color:Gray' readonly ";}
  Echo "<select name='$name' $readOnly>";
  for ($i=0; $i<$symbolListCount; $i++){
    $symbol = $symbolList[$i][$num];
    $ID = $symbolList[$i][$num2];
    //$name = str_replace('-1','Minus1',$name);
    if ($selected == $symbol){
      echo "<option value='$ID' selected>$symbol</option>";
    }else{
      echo "<option value='$ID'>$symbol</option>";
    }

  }
  Echo "</SELECT>";
}

function displayTrendSymbols($symbolList, $name, $enabled){
  $symbolListCount = count($symbolList);
  $readOnly = "";
  //echo "<BR> ENABLED: ".$enabled;
  if ($enabled == 0){$readOnly = " style='color:Gray' readonly ";}
  Echo "<select name='$name' $readOnly>";
  for ($i=0; $i<$symbolListCount; $i++){
    $symbol = $symbolList[$i];
    $num = $i-1;
    //$name = str_replace('-1','Minus1',$name);
    echo "<option value='$num'>$symbol</option>";
  }
  echo "</select>";
}

function getAutoPrices(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `CoinID`,`AutoBuyPrice`,`AutoSellPrice`,`Symbol` FROM `CryptoAutoPrices`";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['CoinID'],$row['AutoBuyPrice'],$row['AutoSellPrice'],$row['Symbol']);
  }
  $conn->close();
  return $tempAry;
}

function getMultiSellTemplates(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `ID`, `MultiRuleStr`, `UserID` FROM `MultiSellRuleTemplate` WHERE `UserID` = 3";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['MultiRuleStr'],$row['UserID'],$row['Symbol']);
  }
  $conn->close();
  return $tempAry;
}

function displayMultiSell($symbolList, $name, $enabled, $selected){
  $symbolListCount = count($symbolList);
  $readOnly = "";
  //echo "<BR> ENABLED: ".$enabled;
  if ($enabled == 0){$readOnly = " style='color:Gray' readonly ";}
  Echo "<select name='$name' $readOnly>";
  for ($i=0; $i<$symbolListCount; $i++){
    $symbol = $symbolList[$i][1];
    $num = $symbolList[$i][0];
    //$name = str_replace('-1','Minus1',$name);
    //echo "<option value='$num'>$symbol</option>";
    if ($selected == $num){
      echo "<option value='$num' selected>$symbol</option>";
    }else{
      echo "<option value='$num'>$symbol</option>";
    }
  }
  echo "</select>";
}

function displayEdit($id){
  $formSettings = getRules($id);
  $pricePattern = getPricePatternBuy($id);
  $symbolList = getSymbols();
  $Hr1ChangeList = get1HrchangeBuy($id);
  $priceTrendList = getPriceTrendBuy($id);
  $cryptoAutoPrices = getAutoPrices();
  $coinList = getCoinIDs();
  $comboList = Array('-1','0','1','*');
  $_GET['edit'] = null;
  echo "<h3><a href='Settings.php'>User Settings</a> &nbsp > &nbsp <a href='BuySettings.php'>Buy Settings</a> &nbsp > &nbsp <a href='SellSettings.php'>Sell Settings</a> &nbsp > &nbsp <a href='Settings_Patterns.php'>Setting Patterns</a></h3>";
  echo "<form action='AddNewSetting.php?editedUserReady=".$id."' method='post'>";
  echo "<div class='settingsformMain'>";echo "<div class='settingsform'>";
  addNewText('Rule ID: ', 'RuleID', $id, 1, 'Eg 50', False,1);
  addNewText('Rule Name: ', 'RuleName', $formSettings[0][71], 2, 'Eg 50', False,1);
  echo "</div>";
  echo "<div class='settingsform'>";
    echo "<H3>Market Cap</H3>";
    addNewTwoOption('MarketCapEnable: ', 'MarketCapEnable', $formSettings[0][4]);
    addNewText('MarketCapTop: ', 'MarketCapTop', $formSettings[0][5], 2, 'Eg 50', False,$formSettings[0][4]);
    addNewText('MarketCapBtm: ', 'MarketCapBtm', $formSettings[0][6], 3, 'Eg 50', False,$formSettings[0][4]);
  echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>Volume</H3>";
  addNewTwoOption('VolumeEnable: ', 'VolumeEnable', $formSettings[0][22]);
  addNewText('VolumeTop: ', 'VolumeTop', $formSettings[0][23], 5, 'Eg 50', False,$formSettings[0][22]);
  addNewText('VolumeBtm: ', 'VolumeBtm', $formSettings[0][24], 6, 'Eg 50', False,$formSettings[0][22]);
  echo "</div>";
  //echo "<div class='settingsform'>";
  ///echo "<H3>Buy Orders</H3>";
  //addNewTwoOption('BuyOrdersEnabled: ', 'BuyOrdersEnabled', $formSettings[0][1]);
  //addNewText('BuyOrdersTop: ', 'BuyOrdersTop', $formSettings[0][2], 8, 'Eg 50', False,$formSettings[0][1]);
  //addNewText('BuyOrdersBtm: ', 'BuyOrdersBtm', $formSettings[0][3], 9, 'Eg 50', False,$formSettings[0][1]);
  //echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>1 Hour Price</H3>";
  addNewTwoOption('1HrEnable: ', '1HrEnable', $formSettings[0][7]);
  addNewText('PriceChange1HrTop: ', 'PriceChange1HrTop', $formSettings[0][8], 11, 'Eg 50', False,$formSettings[0][7]);
  addNewText('PriceChange1HrBtm: ', 'PriceChange1HrBtm', $formSettings[0][9], 12, 'Eg 50', False,$formSettings[0][7]);
  echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>24 Hour Price</H3>";
  addNewTwoOption('24HrEnable: ', '24HrEnable', $formSettings[0][10]);
  addNewText('PriceChange24HrTop: ', 'PriceChange24HrTop', $formSettings[0][11], 14, 'Eg 50', False,$formSettings[0][10]);
  addNewText('PriceChange24HrBtm: ', 'PriceChange24HrBtm', $formSettings[0][12], 15, 'Eg 50', False,$formSettings[0][10]);
  echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>7 Day Price</H3>";
  addNewTwoOption('7DEnable: ', '7DEnable', $formSettings[0][13]);
  addNewText('PriceChange7DTop: ', 'PriceChange7DTop', $formSettings[0][14], 17, 'Eg 50', False,$formSettings[0][13]);
  addNewText('PriceChange7DBtm: ', 'PriceChange7DBtm', $formSettings[0][15], 18, 'Eg 50', False,$formSettings[0][13]);
  echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>Price Difference</H3>";
  addNewTwoOption('PriceDiff1Enable: ', 'PriceDiff1Enable', $formSettings[0][16]);
  addNewText('PriceDiff1Top: ', 'PriceDiff1Top', $formSettings[0][17], 25, 'Eg 50', False,$formSettings[0][16]);
  addNewText('PriceDiff1Btm: ', 'PriceDiff1Btm', $formSettings[0][18], 26, 'Eg 50', False,$formSettings[0][16]);
  echo "</div>";
  //echo "<div class='settingsform'>";

  //addNewThreeOption('Price Trend 4: ','Price4Trend',$formSettings[0][32]);
  //addNewThreeOption('Price Trend 3: ','Price3Trend',$formSettings[0][33]);
  //addNewThreeOption('Last Price Trend: ','LastPriceTrend',$formSettings[0][34]);
  //addNewThreeOption('Live Price Trend: ','LivePriceTrend',$formSettings[0][35]);
  //echo "</div>";

  echo "<div class='settingsform'>";
  echo "<H3>Coin Price Offset</H3>";
  addNewTwoOption('Buy Coin Offset Enabled: ', 'BuyCoinOffsetEnabled', $formSettings[0][29]);
  addNewText('Buy Coin Offset Pct: ', 'BuyCoinOffsetPct', $formSettings[0][30], 26, 'Eg 50', False,$formSettings[0][29]);
  echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>Buy Price Minimum</H3>";
  addNewTwoOption('Buy Price Min Enabled: ', 'BuyPriceMinEnabled', $formSettings[0][43]);
  addNewText('Buy Price Min: ', 'BuyPriceMin', $formSettings[0][44], 44, 'Eg 7000', False,$formSettings[0][43]);
  echo "</div>";

  echo "<div class='settingsform'>";
  echo "<H3>Auto Buy</H3>";
  addNewTwoOption('Auto Buy Enabled: ', 'AutoBuyEnabled', $formSettings[0][46]);
  //addNewText('Auto Buy Price: ', 'AutoBuyPrice', $formSettings[0][47], 47, 'Eg 7000', False,$formSettings[0][46]);
  echo "<select name='listbox' size='3' readonly>";
  displayAutoListBox($cryptoAutoPrices);
  echo "</select>";
  echo "</div>";

  echo "<div class='settingsform'>";
  echo "<H3>Buy Amount Override</H3>";
  addNewTwoOption('Buy Amount Override Enabled: ', 'BuyAmountOverrideEnabled', $formSettings[0][48]);
  addNewText('Buy Amount Override: ', 'BuyAmountOverride', $formSettings[0][49], 48, 'Eg 7000', False,$formSettings[0][48]);
  echo "</div>";
  //echo "<div class='settingsform'>";
  //echo "<H3>Price Trend</H3>";
  //addNewTwoOption('Price Trend Enabled: ', 'PriceTrendEnabled', $formSettings[0][31]);
  //addNewText('New Buy Pattern: ', 'NewBuyPattern', $formSettings[0][50], 49, 'Eg 7000', True);

  //echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>New Price Trend</H3>";
  addNewTwoOption('Price Trend Enabled: ', 'PriceTrendEnabled', $formSettings[0][31]);
  echo "<div class='settingsformCmbo'>";
  $coinPricePatternNames = getCoinPriceMatchNames($id, "`CoinPricePatternName`","");
  $coinPricePatternNamesSize = count($coinPricePatternNames);
  $coinPricePatternID = $formSettings[0][60];
  $coinPricePatternName = $formSettings[0][61];

  //echo "<div class='settingsformCmbo'>";
  addNewTwoOption('Coin Price Pattern Enabled: ', 'CoinPricePatternEnabled', $formSettings[0][53]);
  displaySymbols($coinPricePatternNames,0,'coinPricePatternCmb',$formSettings[0][31],1,$coinPricePatternName);
  //displayTrendSymbols($comboList,'selectCmboTrend1', $formSettings[0][31]);
  //displayTrendSymbols($comboList,'selectCmboTrend2', $formSettings[0][31]);
  //displayTrendSymbols($comboList,'selectCmboTrend3', $formSettings[0][31]);
  //displayTrendSymbols($comboList,'selectCmboTrend4', $formSettings[0][31]);
  //displayListBoxNormal($priceTrendList,2,'listboxTrend',$formSettings[0][31]);
  echo "<input type='submit' name='publishTrend' value='Apply'></div></div>";
  //echo "<div class='settingsform'>";
  //echo "<H3>Coin Price Pattern</H3>";
  //  addNewTwoOption('Coin Price Pattern Enabled: ', 'CoinPricePatternEnabled', $formSettings[0][53]);
  //  addNewText('Coin Price Pattern: ', 'CoinPricePattern', $formSettings[0][54], 52, 'Eg BTC:7000,ETH:140,BCH:230', True);
  //echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>New Coin Price Pattern</H3>";
  $coinPriceMatchNames = getCoinPriceMatchNames($id, "`CoinPriceMatchName`","");
  $coinPriceMatchNamesSize = count($coinPriceMatchNames);
  $coinPriceMatchID = $formSettings[0][59];
  $coinPriceMatchName = $formSettings[0][58];
  //$coinPriceMatchNameSelected = $_SESSION['coinPriceMatchNameSelected'];

  echo "<div class='settingsformCmbo'>";
  addNewTwoOption('Coin Price Match Enabled: ', 'CoinPriceMatchEnabled', $formSettings[0][53]);
  displaySymbols($coinPriceMatchNames,0,'coinPriceMatchCmb',$formSettings[0][53],1,$coinPriceMatchName);
  //addNewText('Coin Price Top: ', 'CPrice', 0, 52, 'Eg 7000.00', True,$formSettings[0][53]);
  //addNewText('Coin Price Bottom: ', 'CPricebtm', 0, 52, 'Eg 7000.00', True,$formSettings[0][53]);
  //echo "<a href='AddNewSetting.php?add=$id'>Add</a>";
  //Echo "<select name='listbox' size='3'>";
  //displayListBox($pricePattern);
  //echo "</select>";
  echo "<input type='submit' name='publish' value='Apply'></div></div>";

  //echo "<div class='settingsform'>";
  //echo "<H3>1Hr Change Pattern</H3>";
  //addNewTwoOption('1Hr Change Enabled: ', 'Hr1ChangeEnabled', $formSettings[0][55]);
  //addNewText('1Hr Change Pattern: ', 'Hr1ChangePattern', $formSettings[0][56], 52, 'Eg BTC:7000,ETH:140,BCH:230', True);
  //echo "</div>";

  echo "<div class='settingsform'>";
  echo "<H3>Coin % from Low Buy Price</H3>";
  addNewTwoOption('Coin Pct from Low Buy Price: ', 'CoinPctFromLowBuyPriceEnabled', $formSettings[0][67]);
  addNewText('Pct From Low Buy Price: ', 'PctFromLowBuyPrice', $formSettings[0][68], 48, '10%', False,$formSettings[0][67]);
  echo "</div>";

  echo "<div class='settingsform'>";
  echo "<H3>New Coin Hours Flat</H3>";
  addNewTwoOption('Coin Hours Flat: ', 'CoinHoursFlatEnabled', $formSettings[0][69]);
  addNewText('Coin Hours Flat: ', 'CoinHoursFlat', $formSettings[0][70], 48, '10', False,$formSettings[0][69]);
  echo "</div>";

  echo "<div class='settingsform'>";

  echo "<H3>New 1Hr Change Pattern</H3>";
  addNewTwoOption('1Hr Change Enabled: ', 'Hr1ChangeEnabled', $formSettings[0][55]);
  echo "<div class='settingsformCmbo'>";
  displayTrendSymbols($comboList,'selectCmbo1Hr1', $formSettings[0][55]);
  displayTrendSymbols($comboList,'selectCmbo1Hr2', $formSettings[0][55]);
  displayTrendSymbols($comboList,'selectCmbo1Hr3', $formSettings[0][55]);
  displayTrendSymbols($comboList,'selectCmbo1Hr4', $formSettings[0][55]);
  displayListBoxNormal($Hr1ChangeList,2,'listbox1Hr',$formSettings[0][55]);
  echo "<input type='submit' name='publishHr1' value='+'><input type='submit' name='removeHr1' value='-'></div></div>";

  echo "<div class='settingsform'>";

  echo "<H3>Price Dip Settings</H3>";

  addNewTwoOption('Re Enable Buy Rule after Dip:','ReEnableBuyRuleAfterDip',$formSettings[0][72]);
  addNewText('PriceDip 24Hr: ', 'PriceDip24Hr', $formSettings[0][73], 51, 'Eg ALL', False,1);
  addNewText('PriceDip 7D: ', 'PriceDip7D', $formSettings[0][74], 51, 'Eg ALL', False,1);
  addNewText('PriceDip Pct Tolerance: ', 'PriceDipPctTolerance', $formSettings[0][75], 51, 'Eg ALL', False,1);
  addNewText('PriceDip Hours Flat: ', 'PriceDipHoursFlat', $formSettings[0][76], 51, 'Eg ALL', False,1);

  echo "</div>";
  echo "<div class='settingsform'>";

  echo "<H3>Admin</H3>";
    addNewTwoOption('Send Email: ', 'sendEmail', $formSettings[0][26]);
    addNewTwoOption('Buy Coin: ', 'buyCoin', $formSettings[0][25]);
    //addNewText('BTC Buy Amount: ', 'bTCBuyAmount', $formSettings[0][27], 38, 'Eg 0 for full balance', False,1);
    //addNewText('Limit To Coin: ', 'limitToCoin', $formSettings[0][45], 45, 'Eg ALL', False,1);
    $coinListSize = Count($coinList);
    Echo "<select name='LimitToCoinID'>";
    for ($w=0; $w<$coinListSize;$w++){
      $limitCoinID = $coinList[$w][0];   $sym = $coinList[$w][1]; $base = $coinList[$w][2];
      $savedSym = $formSettings[0][45];
      $symBase = $sym."-".$base;
      //echo "<BR> $w Here!! $limitCoinID $symBase $sym $base $savedSym";
      if ($savedSym == $sym){
          echo "<option value='$sym' selected>$symBase</option>";
      }else{
          echo "<option value='$sym' >$symBase</option>";
      }

    }
    if ($savedSym == 'ALL'){ echo "<option value='ALL' selected>ALL</option>";}else{echo "<option value='ALL'>ALL</option>";}
    echo "</select>";Echo ":Limit To Coin";
    addNewText('Limit To BaseCurrency: ', 'limitToBaseCurrency', $formSettings[0][66], 46, 'Eg ALL', False,1);
    addNewText('Sell Rule Fixed: ', 'sellRuleFixed', $formSettings[0][51], 50, 'Eg ALL', False,1);
    addNewText('Coin Order: ', 'CoinOrderTxt', $formSettings[0][52], 51, 'Eg ALL', False,1);
    addNewTwoOption('Override Daily Limit Enabled:','OverrideDailyLimitEnabled',$formSettings[0][57]);
    addNewTwoOption('Override Coin Allocation Enabled:','OverrideCoinAllocationEnabled',$formSettings[0][64]);
    addNewTwoOption('One-Time Buy Rule Enabled:','OneTimeBuyRuleEnabled',$formSettings[0][65]);
    //addNewTwoOption('Re Enable Buy Rule after Dip:','ReEnableBuyRuleAfterDip',$formSettings[0][72]);
    //addNewText('PriceDip 24Hr: ', 'PriceDip24Hr', $formSettings[0][73], 51, 'Eg ALL', False,1);
    //addNewText('PriceDip 7D: ', 'PriceDip7D', $formSettings[0][74], 51, 'Eg ALL', False,1);
    //addNewText('PriceDip Pct Tolerance: ', 'PriceDipPctTolerance', $formSettings[0][75], 51, 'Eg ALL', False,1);
    //addNewText('PriceDip Hours Flat: ', 'PriceDipHoursFlat', $formSettings[0][76], 51, 'Eg ALL', False,1);
    addNewText('BuyRisesInPrice: ', 'BuyRisesInPrice', $formSettings[0][77], 51, 'Eg ALL', False,1);
    addNewTwoOption('OverrideCancelBuyTimeEnabled:','OverrideCancelBuyTimeEnabled',$formSettings[0][78]);
    $finalTime = $formSettings[0][79];
    if ($formSettings[0][78] == 0){
      $finalTime = $formSettings[0][80];
    }
    addNewText('Time To Cancel Mins: ', 'TimeToCancelMins', $finalTime, 51, 'Eg ALL', False,1);
    addNewTwoOption('Multi Sell Rules Enabled:','MultiSellRulesEnabled',$formSettings[0][81]);
    $multiSellTemplates = getMultiSellTemplates();
    displayMultiSell($multiSellTemplates,'MultiSellRules',$formSettings[0][81],$formSettings[0][82]);
  echo "</div>";
  echo "<div class='settingsform'>
    <input type='submit' name='submit' value='Update' class='settingsformsubmit' tabindex='36'>
  </div></div>";
  echo "</form>";
}
displaySideColumn();
?>
</body>
</html>
