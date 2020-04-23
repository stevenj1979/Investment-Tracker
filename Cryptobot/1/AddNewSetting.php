<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
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
  //if (!empty($_POST['MarketCapEnable'])){if ($_POST['MarketCapEnable']== "Yes"){ $mCapEnChk = 1;}else{$mCapEnChk = 00;}}
  updateEditedUser();
}
if(!empty($_GET['delete'])){ deleteItem($_GET['delete']); }
if(!empty($_GET['copyRule'])){ copyRule($_GET['copyRule']); }

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
  ,`NewBuyPattern`,`SellRuleFixed`,`CoinPricePatternEnabled`,`CoinPricePattern`)
Select `UserID`, `BuyOrdersEnabled`, `BuyOrdersTop`, `BuyOrdersBtm`, `MarketCapEnabled`, `MarketCapTop`, `MarketCapBtm`, `1HrChangeEnabled`, `1HrChangeTop`, `1HrChangeBtm`, `24HrChangeEnabled`, `24HrChangeTop`
, `24HrChangeBtm`, `7DChangeEnabled`, `7DChangeTop`, `7DChangeBtm`, `CoinPriceEnabled`, `CoinPriceTop`, `CoinPriceBtm`, `SellOrdersEnabled`, `SellOrdersTop`, `SellOrdersBtm`, `VolumeEnabled`, `VolumeTop`
, `VolumeBtm`, 0, `SendEmail`, `BTCAmount`, `BuyType`, `CoinOrder`, `BuyCoinOffsetEnabled`, `BuyCoinOffsetPct`, `PriceTrendEnabled`, `Price4Trend`, `Price3Trend`, `LastPriceTrend`, `LivePriceTrend`
, `BuyPriceMinEnabled`, `BuyPriceMin`, `LimitToCoin`, `LimitToCoinID`, `AutoBuyCoinEnabled`, `AutoBuyCoinPct`, `BuyAmountOverrideEnabled`, `BuyAmountOverride`,`NewBuyPattern`,`SellRuleFixed`,`CoinPricePatternEnabled`,`CoinPricePattern`
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
  `CoinPricePatternEnabled` = $coinPricePatternEnabled, `CoinPricePattern` = '$coinPricePattern'
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
,`BuyAmountOverrideEnabled`,`BuyAmountOverride`,`NewBuyPattern`,`SellRuleFixed`, `CoinOrder`,`CoinPricePatternEnabled`,`CoinPricePattern`
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
     ,$row['CoinPricePatternEnabled'],$row['CoinPricePattern']);
  }
  $conn->close();
  return $tempAry;
}

function addNewText($RealName, $idName, $value, $tabIndex, $pHoolder){
  echo "<div class='settingsform'>
    <b>".$RealName."</b><br/>
    <input type='text' name='".$idName."' id='".$idName."' class='form-control input-lg' placeholder='$pHoolder' value='".$value."' tabindex='".$tabIndex."'>
  </div>";

}

function addNewTwoOption($RealName, $idName, $value){
  if ($value == 1 || $value == 'Yes' ){ $option1 = "Yes"; $option2 = "No";}else{$option1 = "No"; $option2 = "Yes";}
  echo "<div class='settingsform'>
    <b>$RealName</b><br/><select name='$idName' id='$idName' class='enableTextBox'>
   <option value='".$option1."'>".$option1."</option>
    <option value='".$option2."'>".$option2."</option></select></div>";
}

function addNewThreeOption($RealName, $idName, $value){
  if ($value == 1){$nOption1 = "Up"; $nOption2 = "Equal";$nOption3 = "Down";}
  elseif ($RealName == -1){$nOption1 = "Down"; $nOption2 = "Equal";$nOption3 = "Up";}
  else{$nOption1 = "Equal"; $nOption2 = "Down";$nOption3 = "Up";}
  echo "<div class='settingsform'>
    <b>$RealName</b><br/><select name='$idName' id='$idName' class='enableTextBox'>
    <option value='".$nOption1."'>".$nOption1."</option>
    <option value='".$nOption2."'>".$nOption2."</option>
    <option value='".$nOption3."'>".$nOption3."</option></select></div>";
}


function displayEdit($id){
  $formSettings = getRules($id);
  $_GET['edit'] = null;
  echo "<h3><a href='Settings.php'>User Settings</a> &nbsp > &nbsp <a href='BuySettings.php'>Buy Settings</a> &nbsp > &nbsp <a href='SellSettings.php'>Sell Settings</a></h3>";
  echo "<form action='AddNewSetting.php?editedUserReady=".$id."' method='post'>";
  addNewTwoOption('MarketCapEnable: ', 'MarketCapEnable', $formSettings[0][4]);
  addNewText('MarketCapTop: ', 'MarketCapTop', $formSettings[0][5], 2, 'Eg 50');
  addNewText('MarketCapBtm: ', 'MarketCapBtm', $formSettings[0][6], 3, 'Eg 50');
  addNewTwoOption('VolumeEnable: ', 'VolumeEnable', $formSettings[0][22]);
  addNewText('VolumeTop: ', 'VolumeTop', $formSettings[0][23], 5, 'Eg 50');
  addNewText('VolumeBtm: ', 'VolumeBtm', $formSettings[0][24], 6, 'Eg 50');
  addNewTwoOption('BuyOrdersEnabled: ', 'BuyOrdersEnabled', $formSettings[0][1]);
  addNewText('BuyOrdersTop: ', 'BuyOrdersTop', $formSettings[0][2], 8, 'Eg 50');
  addNewText('BuyOrdersBtm: ', 'BuyOrdersBtm', $formSettings[0][3], 9, 'Eg 50');
  addNewTwoOption('1HrEnable: ', '1HrEnable', $formSettings[0][7]);
  addNewText('PriceChange1HrTop: ', 'PriceChange1HrTop', $formSettings[0][8], 11, 'Eg 50');
  addNewText('PriceChange1HrBtm: ', 'PriceChange1HrBtm', $formSettings[0][9], 12, 'Eg 50');
  addNewTwoOption('24HrEnable: ', '24HrEnable', $formSettings[0][10]);
  addNewText('PriceChange24HrTop: ', 'PriceChange24HrTop', $formSettings[0][11], 14, 'Eg 50');
  addNewText('PriceChange24HrBtm: ', 'PriceChange24HrBtm', $formSettings[0][12], 15, 'Eg 50');
  addNewTwoOption('7DEnable: ', '7DEnable', $formSettings[0][13]);
  addNewText('PriceChange7DTop: ', 'PriceChange7DTop', $formSettings[0][14], 17, 'Eg 50');
  addNewText('PriceChange7DBtm: ', 'PriceChange7DBtm', $formSettings[0][15], 18, 'Eg 50');
  addNewTwoOption('PriceDiff1Enable: ', 'PriceDiff1Enable', $formSettings[0][16]);
  addNewText('PriceDiff1Top: ', 'PriceDiff1Top', $formSettings[0][17], 25, 'Eg 50');
  addNewText('PriceDiff1Btm: ', 'PriceDiff1Btm', $formSettings[0][18], 26, 'Eg 50');
  addNewTwoOption('Price Trend Enabled: ', 'PriceTrendEnabled', $formSettings[0][31]);
  addNewThreeOption('Price Trend 4: ','Price4Trend',$formSettings[0][32]);
  addNewThreeOption('Price Trend 3: ','Price3Trend',$formSettings[0][33]);
  addNewThreeOption('Last Price Trend: ','LastPriceTrend',$formSettings[0][34]);
  addNewThreeOption('Live Price Trend: ','LivePriceTrend',$formSettings[0][35]);
  addNewTwoOption('Send Email: ', 'sendEmail', $formSettings[0][26]);
  addNewTwoOption('Buy Coin: ', 'buyCoin', $formSettings[0][25]);
  addNewTwoOption('Buy Coin Offset Enabled: ', 'BuyCoinOffsetEnabled', $formSettings[0][29]);
  addNewText('Buy Coin Offset Pct: ', 'BuyCoinOffsetPct', $formSettings[0][30], 26, 'Eg 50');
  addNewText('BTC Buy Amount: ', 'bTCBuyAmount', $formSettings[0][27], 38, 'Eg 0 for full balance');
  addNewTwoOption('Buy Price Min Enabled: ', 'BuyPriceMinEnabled', $formSettings[0][43]);
  addNewText('Buy Price Min: ', 'BuyPriceMin', $formSettings[0][44], 44, 'Eg 7000');
  addNewText('Limit To Coin: ', 'limitToCoin', $formSettings[0][45], 45, 'Eg ALL');

  addNewTwoOption('Auto Buy Enabled: ', 'AutoBuyEnabled', $formSettings[0][46]);
  addNewText('Auto Buy Price: ', 'AutoBuyPrice', $formSettings[0][47], 47, 'Eg 7000');
  addNewTwoOption('Buy Amount Override Enabled: ', 'BuyAmountOverrideEnabled', $formSettings[0][48]);
  addNewText('Buy Amount Override: ', 'BuyAmountOverride', $formSettings[0][49], 48, 'Eg 7000');
  addNewText('New Buy Pattern: ', 'NewBuyPattern', $formSettings[0][50], 49, 'Eg 7000');
  addNewText('Sell Rule Fixed: ', 'sellRuleFixed', $formSettings[0][51], 50, 'Eg ALL');
  addNewText('Coin Order: ', 'CoinOrderTxt', $formSettings[0][52], 51, 'Eg ALL');
  addNewTwoOption('Coin Price Pattern Enabled: ', 'CoinPricePatternEnabled', $formSettings[0][53]);
  addNewText('Coin Price Pattern: ', 'CoinPricePattern', $formSettings[0][54], 52, 'Eg BTC:7000,ETH:140,BCH:230');
  echo "<div class='settingsform'>
    <input type='submit' name='submit' value='Update' class='settingsformsubmit' tabindex='36'>
  </div>";
  echo "</form>";
}
displaySideColumn();
?>
</body>
</html>
