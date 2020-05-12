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
include_once ('/home/stevenj1979/SQLData.php'); ?>
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
    Echo "this is a test".$_GET['editedUserReady'].$_POST['select'].$_POST['CPrice'];//displayEdit($_GET['editedUserReady']);
    addpricePatterntoSQL($_GET['editedUserReady'], $_POST['select'], $_POST['CPrice']);
  }elseif (!empty($_POST['remove'])){
    //Echo "this is a remove test".$_GET['editedUserReady'].$_POST['listbox'];displayEdit($_GET['editedUserReady']);
    removePricePatternfromSQL($_GET['editedUserReady'], $_POST['listbox']);
  }elseif (!empty($_POST['removeHr1'])){
      Echo " ".$_POST['removeHr1'].$_POST['listbox1Hr'];
  }elseif (!empty($_POST['removeTrend'])){
      Echo " ".$_POST['removeTrend'].$_POST['listboxTrend'];
  }elseif (!empty($_POST['publishHr1'])){
      Echo " ".$_POST['publishHr1'].$_POST['selectCmbo1Hr1'].$_POST['selectCmbo1Hr2'].$_POST['selectCmbo1Hr3'].$_POST['selectCmbo1Hr4'];
  }elseif (!empty($_POST['publishTrend'])){
      Echo " ".$_POST['publishTrend'].$_POST['selectCmboTrend1'].$_POST['selectCmboTrend2'].$_POST['selectCmboTrend3'].$_POST['selectCmboTrend4'];
      if ($_POST['selectCmboTrend1'] == 2){$temp1 = '*';$temp2 ==$_POST['selectCmboTrend2']; $temp3 == $_POST['selectCmboTrend3'];$temp4 == $_POST['selectCmboTrend4'];}
      elseif ($_POST['selectCmboTrend2'] == 2){$temp1 == $_POST['selectCmboTrend1'];$temp2 = '*';$temp3 == $_POST['selectCmboTrend3'];$temp4 == $_POST['selectCmboTrend4'];}
      elseif ($_POST['selectCmboTrend3'] == 2){$temp1 == $_POST['selectCmboTrend1'];$temp2 ==$_POST['selectCmboTrend2'];$temp3 = '*';$temp4 == $_POST['selectCmboTrend4'];}
      elseif ($_POST['selectCmboTrend4'] == 2){$temp1 == $_POST['selectCmboTrend1'];$temp2 ==$_POST['selectCmboTrend2'];$temp3 == $_POST['selectCmboTrend3'];$temp4 = '*';}
      Echo "$temp1 $temp2 $temp3 $temp4 ";
  }else{
    //if (!empty($_POST['MarketCapEnable'])){if ($_POST['MarketCapEnable']== "Yes"){ $mCapEnChk = 1;}else{$mCapEnChk = 00;}}
    updateEditedUser();
  }
}
if(!empty($_GET['delete'])){ deleteItem($_GET['delete']); }
if(!empty($_GET['copyRule'])){ copyRule($_GET['copyRule']); }

function addpricePatterntoSQL($ruleID, $symbol, $price){
  $userID = $_SESSION['ID'];
  echo "$ruleID $symbol $price $userID";
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call addNewCoinPriceMatchBuy($ruleID,$price,'$symbol',$userID);";
  //echo $sql;
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
  $newPrice = $splitPrice[1]; $symbol = $splitPrice[0];
  $userID = $_SESSION['ID'];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "DELETE FROM `CoinPriceMatchRules` WHERE `BuyRuleID` = $ruleID and `CoinPriceMatchID` = (
    select `ID` from `CoinPriceMatch` where `Price` = $newPrice and `UserID` = $userID and `CoinID` = (
      SELECT `ID` FROM `Coin` WHERE `Symbol` = '$symbol' and `BuyCoin` = 1))";
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
  $sql = "INSERT INTO `BuyRules`(`UserID`, `BuyOrdersEnabled`, `BuyOrdersTop`, `BuyOrdersBtm`, `MarketCapEnabled`, `MarketCapTop`, `MarketCapBtm`, `1HrChangeEnabled`, `1HrChangeTop`, `1HrChangeBtm`
    , `24HrChangeEnabled`, `24HrChangeTop`, `24HrChangeBtm`, `7DChangeEnabled`, `7DChangeTop`, `7DChangeBtm`, `CoinPriceEnabled`, `CoinPriceTop`, `CoinPriceBtm`, `SellOrdersEnabled`, `SellOrdersTop`
    , `SellOrdersBtm`, `VolumeEnabled`, `VolumeTop`, `VolumeBtm`, `BuyCoin`, `SendEmail`, `BTCAmount`, `BuyType`, `CoinOrder`, `BuyCoinOffsetEnabled`, `BuyCoinOffsetPct`, `PriceTrendEnabled`, `Price4Trend`
    , `Price3Trend`, `LastPriceTrend`, `LivePriceTrend`, `BuyPriceMinEnabled`, `BuyPriceMin`, `LimitToCoin`, `LimitToCoinID`, `AutoBuyCoinEnabled`, `AutoBuyCoinPct`, `BuyAmountOverrideEnabled`, `BuyAmountOverride`
  ,`NewBuyPattern`,`SellRuleFixed`,`CoinPricePatternEnabled`,`CoinPricePattern`,`1HrChangeTrendEnabled`,`1HrChangeTrend`)
Select `UserID`, `BuyOrdersEnabled`, `BuyOrdersTop`, `BuyOrdersBtm`, `MarketCapEnabled`, `MarketCapTop`, `MarketCapBtm`, `1HrChangeEnabled`, `1HrChangeTop`, `1HrChangeBtm`, `24HrChangeEnabled`, `24HrChangeTop`
, `24HrChangeBtm`, `7DChangeEnabled`, `7DChangeTop`, `7DChangeBtm`, `CoinPriceEnabled`, `CoinPriceTop`, `CoinPriceBtm`, `SellOrdersEnabled`, `SellOrdersTop`, `SellOrdersBtm`, `VolumeEnabled`, `VolumeTop`
, `VolumeBtm`, 0, `SendEmail`, `BTCAmount`, `BuyType`, `CoinOrder`, `BuyCoinOffsetEnabled`, `BuyCoinOffsetPct`, `PriceTrendEnabled`, `Price4Trend`, `Price3Trend`, `LastPriceTrend`, `LivePriceTrend`
, `BuyPriceMinEnabled`, `BuyPriceMin`, `LimitToCoin`, `LimitToCoinID`, `AutoBuyCoinEnabled`, `AutoBuyCoinPct`, `BuyAmountOverrideEnabled`, `BuyAmountOverride`,`NewBuyPattern`,`SellRuleFixed`,`CoinPricePatternEnabled`,`CoinPricePattern`
,`1HrChangeTrendEnabled`,`1HrChangeTrend`
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
  $limitToCoin = $_POST['limitToCoin'];
  $autoBuyCoinEnabled = postDataYesNo($_POST['AutoBuyEnabled']);
  $autoBuyPrice = $_POST['AutoBuyPrice'];
  $buyAmountOverrideEnabled = postDataYesNo($_POST['BuyAmountOverrideEnabled']);
  $buyAmountOverride = $_POST['BuyAmountOverride'];
  $newBuyPattern = $_POST['NewBuyPattern'];
  $coinOrder = $_POST['CoinOrderTxt'];
  $hr1ChangePattern = $_POST['Hr1ChangePattern'];

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
  `BuyPriceMinEnabled`=$BuyPriceMinEnabled,`BuyPriceMin`=$BuyPriceMin, `LimitToCoin` = '$limitToCoin', `AutoBuyCoinEnabled` = $autoBuyCoinEnabled, `BuyAmountOverrideEnabled` = $buyAmountOverrideEnabled, `BuyAmountOverride` = $buyAmountOverride
  , `NewBuyPattern` = '$newBuyPattern',`SellRuleFixed` = '$SellRuleFixed', `LimitToCoinID` = (SELECT `ID` FROM `Coin` WHERE `Symbol` = '$limitToCoin' and `BuyCoin` = 1), `CoinOrder` = $coinOrder,
  `CoinPricePatternEnabled` = $coinPricePatternEnabled, `CoinPricePattern` = '$coinPricePattern', `1HrChangeTrendEnabled` = $hr1ChangeEnabled, `1HrChangeTrend` = '$hr1ChangePattern'
  WHERE `ID` = $id";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();
  header('Location: BuySettings.php');
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
,`BuyAmountOverrideEnabled`,`BuyAmountOverride`,`NewBuyPattern`,`SellRuleFixed`, `CoinOrder`,`CoinPricePatternEnabled`,`CoinPricePattern`,`1HrChangeTrendEnabled`,`1HrChangeTrend`
FROM `UserBuyRules` WHERE `RuleID` = $id order by `CoinOrder` ASC";
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
     ,$row['CoinPricePatternEnabled'],$row['CoinPricePattern'],$row['1HrChangeTrendEnabled'],$row['1HrChangeTrend']);
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

  $sql = "SELECT `BuyRuleID`,`SellRuleID`,`CoinID`,`Price`,`Symbol`,`UserID` FROM `CoinPriceMatchView` WHERE (`BuyRuleID` = $id )";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  //print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['BuyRuleID'],$row['SellRuleID'],$row['CoinID'],$row['Price'],$row['Symbol'],$row['UserID']);
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

function addNewText($RealName, $idName, $value, $tabIndex, $pHoolder, $longText){
  if ($longText == True){ $textClass = 'enableTextBoxLong'; $divClass = 'settingsformLong'; } else {$textClass = 'enableTextBox'; $divClass = 'settingsform';}
  echo "<br/><b>".$RealName."</b>
    <input type='text' name='".$idName."' id='".$idName."' class='".$textClass."' placeholder='$pHoolder' value='".$value."' tabindex='".$tabIndex."'>";

}

function addNewTwoOption($RealName, $idName, $value){
  if ($value == 1 || $value == 'Yes' ){ $option1 = "Yes"; $option2 = "No";}else{$option1 = "No"; $option2 = "Yes";}
  echo "<br/><b>$RealName</b><select name='$idName' id='$idName' class='enableTextBox'>
   <option value='".$option1."'>".$option1."</option>
    <option value='".$option2."'>".$option2."</option></select>";
}

function addNewThreeOption($RealName, $idName, $value){
  if ($value == 1){$nOption1 = "Up"; $nOption2 = "Equal";$nOption3 = "Down";}
  elseif ($RealName == -1){$nOption1 = "Down"; $nOption2 = "Equal";$nOption3 = "Up";}
  else{$nOption1 = "Equal"; $nOption2 = "Down";$nOption3 = "Up";}
  echo "<br/><b>$RealName</b><select name='$idName' id='$idName' class='enableTextBox'>
    <option value='".$nOption1."'>".$nOption1."</option>
    <option value='".$nOption2."'>".$nOption2."</option>
    <option value='".$nOption3."'>".$nOption3."</option></select>";
}

function displayListBox($tempAry){
  $tempCount = count($tempAry);
  for ($i=0; $i<$tempCount; $i++){
    $price = $tempAry[$i][3]; $symbol = $tempAry[$i][4]; $result = $symbol.":".$price;

      echo "<option value='$result'>$result</option>";
  }
}

function displayListBoxNormal($tempAry, $num){
  $tempCount = count($tempAry);
  for ($i=0; $i<$tempCount; $i++){
    $result = $tempAry[$i][$num]; //$symbol = $tempAry[$i][4]; $result = $symbol.":".$price;

      echo "<option value='$result'>$result</option>";
  }
}

function displaySymbols($symbolList,$num){
  $symbolListCount = count($symbolList);
  for ($i=0; $i<$symbolListCount; $i++){
    $symbol = $symbolList[$i][$num];
    //$name = str_replace('-1','Minus1',$name);
    echo "<option value='$i'>$symbol</option>";
  }
}

function displayTrendSymbols($symbolList){
  $symbolListCount = count($symbolList);
  for ($i=0; $i<$symbolListCount; $i++){
    $symbol = $symbolList[$i];
    $num = $i-1;
    //$name = str_replace('-1','Minus1',$name);
    echo "<option value='$num'>$symbol</option>";
  }
}




function displayEdit($id){
  $formSettings = getRules($id);
  $pricePattern = getPricePatternBuy($id);
  $symbolList = getSymbols();
  $Hr1ChangeList = get1HrchangeBuy($id);
  $priceTrendList = getPriceTrendBuy($id);
  $comboList = Array('-1','0','1','*');
  $_GET['edit'] = null;
  echo "<h3><a href='Settings.php'>User Settings</a> &nbsp > &nbsp <a href='BuySettings.php'>Buy Settings</a> &nbsp > &nbsp <a href='SellSettings.php'>Sell Settings</a></h3>";
  echo "<form action='AddNewSetting.php?editedUserReady=".$id."' method='post'>";
  echo "<div class='settingsform'>";
    echo "<H3>Market Cap</H3>";
    addNewTwoOption('MarketCapEnable: ', 'MarketCapEnable', $formSettings[0][4]);
    addNewText('MarketCapTop: ', 'MarketCapTop', $formSettings[0][5], 2, 'Eg 50', False);
    addNewText('MarketCapBtm: ', 'MarketCapBtm', $formSettings[0][6], 3, 'Eg 50', False);
  echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>Volume</H3>";
  addNewTwoOption('VolumeEnable: ', 'VolumeEnable', $formSettings[0][22]);
  addNewText('VolumeTop: ', 'VolumeTop', $formSettings[0][23], 5, 'Eg 50', False);
  addNewText('VolumeBtm: ', 'VolumeBtm', $formSettings[0][24], 6, 'Eg 50', False);
  echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>Buy Orders</H3>";
  addNewTwoOption('BuyOrdersEnabled: ', 'BuyOrdersEnabled', $formSettings[0][1]);
  addNewText('BuyOrdersTop: ', 'BuyOrdersTop', $formSettings[0][2], 8, 'Eg 50', False);
  addNewText('BuyOrdersBtm: ', 'BuyOrdersBtm', $formSettings[0][3], 9, 'Eg 50', False);
  echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>1 Hour Price</H3>";
  addNewTwoOption('1HrEnable: ', '1HrEnable', $formSettings[0][7]);
  addNewText('PriceChange1HrTop: ', 'PriceChange1HrTop', $formSettings[0][8], 11, 'Eg 50', False);
  addNewText('PriceChange1HrBtm: ', 'PriceChange1HrBtm', $formSettings[0][9], 12, 'Eg 50', False);
  echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>24 Hour Price</H3>";
  addNewTwoOption('24HrEnable: ', '24HrEnable', $formSettings[0][10]);
  addNewText('PriceChange24HrTop: ', 'PriceChange24HrTop', $formSettings[0][11], 14, 'Eg 50', False);
  addNewText('PriceChange24HrBtm: ', 'PriceChange24HrBtm', $formSettings[0][12], 15, 'Eg 50', False);
  echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>7 Day Price</H3>";
  addNewTwoOption('7DEnable: ', '7DEnable', $formSettings[0][13]);
  addNewText('PriceChange7DTop: ', 'PriceChange7DTop', $formSettings[0][14], 17, 'Eg 50', False);
  addNewText('PriceChange7DBtm: ', 'PriceChange7DBtm', $formSettings[0][15], 18, 'Eg 50', False);
  echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>Price Difference</H3>";
  addNewTwoOption('PriceDiff1Enable: ', 'PriceDiff1Enable', $formSettings[0][16]);
  addNewText('PriceDiff1Top: ', 'PriceDiff1Top', $formSettings[0][17], 25, 'Eg 50', False);
  addNewText('PriceDiff1Btm: ', 'PriceDiff1Btm', $formSettings[0][18], 26, 'Eg 50', False);
  echo "</div>";
  //echo "<div class='settingsform'>";

  //addNewThreeOption('Price Trend 4: ','Price4Trend',$formSettings[0][32]);
  //addNewThreeOption('Price Trend 3: ','Price3Trend',$formSettings[0][33]);
  //addNewThreeOption('Last Price Trend: ','LastPriceTrend',$formSettings[0][34]);
  //addNewThreeOption('Live Price Trend: ','LivePriceTrend',$formSettings[0][35]);
  //echo "</div>";

  echo "<div class='settingsform'>";
  addNewTwoOption('Buy Coin Offset Enabled: ', 'BuyCoinOffsetEnabled', $formSettings[0][29]);
  addNewText('Buy Coin Offset Pct: ', 'BuyCoinOffsetPct', $formSettings[0][30], 26, 'Eg 50', False);
  echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>Buy Price Minimum</H3>";
  addNewTwoOption('Buy Price Min Enabled: ', 'BuyPriceMinEnabled', $formSettings[0][43]);
  addNewText('Buy Price Min: ', 'BuyPriceMin', $formSettings[0][44], 44, 'Eg 7000', False);
  echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>Auto Buy</H3>";
  addNewTwoOption('Auto Buy Enabled: ', 'AutoBuyEnabled', $formSettings[0][46]);
  addNewText('Auto Buy Price: ', 'AutoBuyPrice', $formSettings[0][47], 47, 'Eg 7000', False);
  echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>Buy Amount Override</H3>";
  addNewTwoOption('Buy Amount Override Enabled: ', 'BuyAmountOverrideEnabled', $formSettings[0][48]);
  addNewText('Buy Amount Override: ', 'BuyAmountOverride', $formSettings[0][49], 48, 'Eg 7000', False);
  echo "</div>";
  //echo "<div class='settingsform'>";
  //echo "<H3>Price Trend</H3>";
  //addNewTwoOption('Price Trend Enabled: ', 'PriceTrendEnabled', $formSettings[0][31]);
  //addNewText('New Buy Pattern: ', 'NewBuyPattern', $formSettings[0][50], 49, 'Eg 7000', True);

  //echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>New Price Trend</H3>";
  Echo "<select name='selectCmboTrend1'>";
  displayTrendSymbols($comboList);
  echo "</select>";
  Echo "<select name='selectCmboTrend2'>";
  displayTrendSymbols($comboList);
  echo "</select>";
  Echo "<select name='selectCmboTrend3'>";
  displayTrendSymbols($comboList);
  echo "</select>";
  Echo "<select name='selectCmboTrend4'>";
  displayTrendSymbols($comboList);
  echo "</select>";
  Echo "<select name='listboxTrend' size='3'>";
  displayListBoxNormal($priceTrendList,2);
  echo "</select>";
  echo "<input type='submit' name='publishTrend' value='+'><input type='submit' name='removeTrend' value='-'></div>";
  //echo "<div class='settingsform'>";
  //echo "<H3>Coin Price Pattern</H3>";
  //  addNewTwoOption('Coin Price Pattern Enabled: ', 'CoinPricePatternEnabled', $formSettings[0][53]);
  //  addNewText('Coin Price Pattern: ', 'CoinPricePattern', $formSettings[0][54], 52, 'Eg BTC:7000,ETH:140,BCH:230', True);
  //echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>New Coin Price Pattern</H3>";

  Echo "<select name='select'>";
  displaySymbols($symbolList,0);
  echo "</select>";
  addNewText('Coin Price: ', 'CPrice', 0, 52, 'Eg 7000.00', True);
  //echo "<a href='AddNewSetting.php?add=$id'>Add</a>";
  Echo "<select name='listbox' size='3'>";
  displayListBox($pricePattern);
  echo "</select>";
  echo "<input type='submit' name='publish' value='+'><input type='submit' name='remove' value='-'></div>";

  //echo "<div class='settingsform'>";
  //echo "<H3>1Hr Change Pattern</H3>";
  //addNewTwoOption('1Hr Change Enabled: ', 'Hr1ChangeEnabled', $formSettings[0][55]);
  //addNewText('1Hr Change Pattern: ', 'Hr1ChangePattern', $formSettings[0][56], 52, 'Eg BTC:7000,ETH:140,BCH:230', True);
  //echo "</div>";

  echo "<div class='settingsform'>";
  echo "<H3>New 1Hr Change Pattern</H3>";
  Echo "<select name='selectCmbo1Hr1'>";
  displayTrendSymbols($comboList);
  echo "</select><select name='selectCmbo1Hr2'>";
  displayTrendSymbols($comboList);
  echo "</select><select name='selectCmbo1Hr3'>";
  displayTrendSymbols($comboList);
  echo "</select><select name='selectCmbo1Hr4'>";
  displayTrendSymbols($comboList);
  echo "</select>";
  Echo "<select name='listbox1Hr' size='3'>";
  displayListBoxNormal($Hr1ChangeList,2);
  echo "</select>";
  echo "<input type='submit' name='publishHr1' value='+'><input type='submit' name='removeHr1' value='-'></div>";
  echo "<div class='settingsform'>";

  echo "<H3>Admin</H3>";
    addNewTwoOption('Send Email: ', 'sendEmail', $formSettings[0][26]);
    addNewTwoOption('Buy Coin: ', 'buyCoin', $formSettings[0][25]);
    addNewText('BTC Buy Amount: ', 'bTCBuyAmount', $formSettings[0][27], 38, 'Eg 0 for full balance', False);
    addNewText('Limit To Coin: ', 'limitToCoin', $formSettings[0][45], 45, 'Eg ALL', False);
    addNewText('Sell Rule Fixed: ', 'sellRuleFixed', $formSettings[0][51], 50, 'Eg ALL', False);
    addNewText('Coin Order: ', 'CoinOrderTxt', $formSettings[0][52], 51, 'Eg ALL', False);
  echo "</div>";
  echo "<div class='settingsform'>
    <input type='submit' name='submit' value='Update' class='settingsformsubmit' tabindex='36'>
  </div>";
  echo "</form>";
}
displaySideColumn();
?>
</body>
</html>
