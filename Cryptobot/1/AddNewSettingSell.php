<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<?php require('includes/config.php');
include_once ('../../../../SQLData.php');
include_once '../includes/newConfig.php';
?>
<html>
<style>
<?php include 'style/style.css'; ?>
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
    addpricePatterntoSQL($_GET['editedUserReady'], $_POST['select'], $_POST['CPrice'], $_POST['CPricebtm']);
  }elseif (!empty($_POST['remove'])){
    removePricePatternfromSQL($_GET['editedUserReady'], $_POST['listbox']);
  }elseif (!empty($_POST['removeTrend'])){
    removeTrendPatternfromSQL($_GET['editedUserReady'],$_POST['listboxTrend']);
  }elseif (!empty($_POST['publishTrend'])){
    if ($_POST['selectCmboTrend1'] == 2){$temp1 = '*';$temp2 = $_POST['selectCmboTrend2']; $temp3 = $_POST['selectCmboTrend3'];$temp4 = $_POST['selectCmboTrend4'];}
    elseif ($_POST['selectCmboTrend2'] == 2){$temp1 = $_POST['selectCmboTrend1'];$temp2 = '*';$temp3 = $_POST['selectCmboTrend3'];$temp4 = $_POST['selectCmboTrend4'];}
    elseif ($_POST['selectCmboTrend3'] == 2){$temp1 = $_POST['selectCmboTrend1'];$temp2 = $_POST['selectCmboTrend2'];$temp3 = '*';$temp4 = $_POST['selectCmboTrend4'];}
    elseif ($_POST['selectCmboTrend4'] == 2){$temp1 = $_POST['selectCmboTrend1'];$temp2 = $_POST['selectCmboTrend2'];$temp3 = $_POST['selectCmboTrend3'];$temp4 = '*';}
    //Echo "$temp1.$temp2.$temp3.$temp4 ".$_GET['editedUserReady'];
    addTrendPatterntoSQL($temp1.$temp2.$temp3.$temp4,$_GET['editedUserReady']);
  }else{
    updateEditedUser();
  }
}
if(!empty($_GET['delete'])){ deleteItem($_GET['delete']); }
if(!empty($_GET['copyRule'])){ copyRule($_GET['copyRule']); }

function addTrendPatterntoSQL($pattern, $ruleID){
  $userID = $_SESSION['ID'];
  echo "$ruleID $symbol $price $userID";
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call addPricePattern('$pattern', 0, $userID, $ruleID);";
  //echo $sql;
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  header('Location: AddNewSettingSell.php?edit='.$ruleID);
}

function addpricePatterntoSQL($ruleID, $symbol, $price, $lowPrice){
  $userID = $_SESSION['ID'];
  echo "$ruleID $symbol $price $userID";
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call addNewCoinPriceMatchBuy(0,$price,'$symbol',$userID,$ruleID,$lowPrice);";
  echo $sql;
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  header('Location: AddNewSettingSell.php?edit='.$ruleID);
}

function removePricePatternfromSQL($ruleID, $price){
  $splitPrice = explode(':',$price);
  $newPrice = $splitPrice[1]; $symbol = $splitPrice[0]; $lowPrice = $splitPrice[2];
  $userID = $_SESSION['ID'];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "DELETE FROM `CoinPriceMatchRules` WHERE `SellRuleID` = $ruleID and `CoinPriceMatchID` = (
    select `ID` from `CoinPriceMatch` where `Price` = $newPrice and `UserID` = $userID and `LowPrice` = $lowPrice and `CoinID` = (
      SELECT `ID` FROM `Coin` WHERE `Symbol` = '$symbol' and `BuyCoin` = 1))";
  //echo $sql;
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  header('Location: AddNewSettingSell.php?edit='.$ruleID);
}

function removeTrendPatternfromSQL($ruleID, $pattern){
  $userID = $_SESSION['ID'];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "DELETE FROM `CoinPricePatternRules` WHERE `PatternID` = (SELECT `ID` FROM `CoinPricePattern` WHERE `CoinPattern` = '$pattern') and `SellRuleID` = $ruleID and `UserID` = $userID";
  //echo $sql;
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  header('Location: AddNewSettingSell.php?edit='.$ruleID);
}

function copyRule($ID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $userID = $_SESSION['ID'];
  $sql = "INSERT INTO `SellRules`(`RuleName`, `UserID`, `SellCoin`, `SendEmail`, `BuyOrdersEnabled`, `BuyOrdersTop`, `BuyOrdersBtm`, `MarketCapEnabled`, `MarketCapTop`, `MarketCapBtm`, `1HrChangeEnabled`
    , `1HrChangeTop`, `1HrChangeBtm`, `24HrChangeEnabled`, `24HrChangeTop`, `24HrChangeBtm`, `7DChangeEnabled`, `7DChangeTop`, `7DChangeBtm`, `ProfitPctEnabled`, `ProfitPctTop`, `ProfitPctBtm`, `CoinPriceEnabled`
    , `CoinPriceTop`, `CoinPriceBtm`, `SellOrdersEnabled`, `SellOrdersTop`, `SellOrdersBtm`, `VolumeEnabled`, `VolumeTop`, `VolumeBtm`, `CoinOrder`, `SellCoinOffsetEnabled`, `SellCoinOffsetPct`, `SellPriceMinEnabled`
    , `SellPriceMin`, `LimitToCoin`, `LimitToCoinID`, `AutoSellCoinEnabled`, `AutoSellCoinPct`, `SellPatternEnabled`, `SellPattern`, `LimitToBuyRule`, `CoinPricePatternEnabled`, `CoinPricePattern`, `CoinPriceMatchNameID`
    , `CoinPricePatternNameID`, `CoinPrice1HrPatternNameID`, `SellFallsInPrice`, `CoinModeRule`, `CoinSwapEnabled`, `CoinSwapAmount`, `NoOfCoinSwapsPerWeek`, `MergeCoinEnabled`, `ReEnableBuyRuleEnabled`, `ReEnableBuyRule`
    , `PctFromHighSellPriceEnabled`, `NoOfHoursFlatEnabled`, `NoOfHoursFlat`, `PctUnderMaxPrice`, `HoursPastBuyToSellEnabled`, `HoursPastBuyToSell`, `CalculatedSellPctEnabled`, `CalculatedSellPctStart`, `CalculatedSellPctEnd`, `CalculatedSellPctDays`
    ,`BypassTrackingSell`,`CalculatedSellPctReduction`,`BypassTrackingSell`, `OverrideBuyBackAmount`, `OverrideBuyBackSaving`,`HoursAfterPurchaseToStart`, `HoursAfterPurchaseToEnd`,`Category`)
    select `RuleName`, `UserID`, 0, `SendEmail`, `BuyOrdersEnabled`, `BuyOrdersTop`, `BuyOrdersBtm`, `MarketCapEnabled`, `MarketCapTop`, `MarketCapBtm`, `1HrChangeEnabled`, `1HrChangeTop`, `1HrChangeBtm`, `24HrChangeEnabled`
    , `24HrChangeTop`, `24HrChangeBtm`, `7DChangeEnabled`, `7DChangeTop`, `7DChangeBtm`, `ProfitPctEnabled`, `ProfitPctTop`, `ProfitPctBtm`, `CoinPriceEnabled`, `CoinPriceTop`, `CoinPriceBtm`, `SellOrdersEnabled`, `SellOrdersTop`
    , `SellOrdersBtm`, `VolumeEnabled`, `VolumeTop`, `VolumeBtm`, `CoinOrder`, `SellCoinOffsetEnabled`, `SellCoinOffsetPct`, `SellPriceMinEnabled`, `SellPriceMin`, `LimitToCoin`, `LimitToCoinID`, `AutoSellCoinEnabled`
    , `AutoSellCoinPct`, `SellPatternEnabled`, `SellPattern`, `LimitToBuyRule`, `CoinPricePatternEnabled`, `CoinPricePattern`, `CoinPriceMatchNameID`, `CoinPricePatternNameID`, `CoinPrice1HrPatternNameID`, `SellFallsInPrice`
    , `CoinModeRule`, `CoinSwapEnabled`, `CoinSwapAmount`, `NoOfCoinSwapsPerWeek`, `MergeCoinEnabled`, `ReEnableBuyRuleEnabled`, `ReEnableBuyRule`, `PctFromHighSellPriceEnabled`, `NoOfHoursFlatEnabled`, `NoOfHoursFlat`, `PctUnderMaxPrice`
    , `HoursPastBuyToSellEnabled`, `HoursPastBuyToSell`, `CalculatedSellPctEnabled`, `CalculatedSellPctStart`, `CalculatedSellPctEnd`, `CalculatedSellPctDays`,`BypassTrackingSell`,`CalculatedSellPctReduction`,`BypassTrackingSell`
    , `OverrideBuyBackAmount`, `OverrideBuyBackSaving`,`HoursAfterPurchaseToStart`, `HoursAfterPurchaseToEnd`,`Category`
    from `SellRules`
    where `ID` = $ID";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();
  header('Location: SellSettings.php');

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
  $sql = "DELETE FROM `SellRules` WHERE `ID` = $id";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();
  header('Location: SellSettings.php');
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
  $sql = "INSERT INTO `SellRules`(`UserID`, `MarketCapEnabled`, `MarketCapTop`, `MarketCapBtm`) VALUES ($userID,1,-40,-100)";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();
  header('Location: SellSettings.php');
}

function postDataYesNo($postValue){
  if (!empty($postValue)){
    if ($postValue == "Yes"){
      return 1;
    }elseif ($postValue == "Auto"){
      return 2;
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

  if (!empty($_POST['MarketCapEnable'])){if ($_POST['MarketCapEnable'] == "Yes"){$MarketCapEnable = 1;}else{$MarketCapEnable = 0;}}else{ $MarketCapEnable = 0;}
  if (!empty($_POST['MarketCapTop'])){$MarketCapTop = $_POST['MarketCapTop'];}else{$MarketCapTop = 0;}
  if (!empty($_POST['MarketCapBtm'])){$MarketCapBtm = $_POST['MarketCapBtm'];}else{$MarketCapBtm = 0;}
  if (!empty($_POST['VolumeEnable'])){if ($_POST['VolumeEnable'] == "Yes"){$VolumeEnable = 1;}else{$VolumeEnable = 0;}}else{ $VolumeEnable = 0;}
  if (!empty($_POST['VolumeTop'])){$VolumeTop = $_POST['VolumeTop'];}else{$VolumeTop = 0;}
  if (!empty($_POST['VolumeBtm'])){$VolumeBtm = $_POST['VolumeBtm'];}else{$VolumeBtm = 0;}
  if (!empty($_POST['BuyOrdersEnabled'])){if ($_POST['BuyOrdersEnabled'] == "Yes"){$BuyOrdersEnabled = 1;}else{$BuyOrdersEnabled = 0;}}else{ $BuyOrdersEnabled = 0;}
  //if (!empty($_POST['BuyOrdersEnabled'])){$BuyOrdersEnabled = $_POST['BuyOrdersEnabled'];}else{$BuyOrdersEnabled = 0;}
  if (!empty($_POST['BuyOrdersTop'])){$BuyOrdersTop = $_POST['BuyOrdersTop'];}else{$BuyOrdersTop = 0;}
  if (!empty($_POST['BuyOrdersBtm'])){$BuyOrdersBtm = $_POST['BuyOrdersBtm'];}else{$BuyOrdersBtm = 0;}
  if (!empty($_POST['1HrEnable'])){if ($_POST['1HrEnable'] == "Yes"){$oneHrEnable = 1;}else{$oneHrEnable = 0;}}else{ $oneHrEnable = 0;}
  //if (!empty($_POST['1HrEnable'])){$oneHrEnable = $_POST['1HrEnable'];}else{$oneHrEnable = 0;}
  if (!empty($_POST['PriceChange1HrTop'])){$PriceChange1HrTop = $_POST['PriceChange1HrTop'];}else{$PriceChange1HrTop = 0;}
  if (!empty($_POST['PriceChange1HrBtm'])){$PriceChange1HrBtm = $_POST['PriceChange1HrBtm'];}else{$PriceChange1HrBtm = 0;}
  if (!empty($_POST['24HrEnable'])){if ($_POST['24HrEnable'] == "Yes"){$t4HrEnable = 1;}else{$t4HrEnable = 0;}}else{ $t4HrEnable = 0;}
  //if (!empty($_POST['24HrEnable'])){$t4HrEnable = $_POST['24HrEnable'];}else{$t4HrEnable = 0;}
  if (!empty($_POST['PriceChange24HrTop'])){$PriceChange24HrTop = $_POST['PriceChange24HrTop'];}else{$PriceChange24HrTop = 0;}
  if (!empty($_POST['PriceChange24HrBtm'])){$PriceChange24HrBtm = $_POST['PriceChange24HrBtm'];}else{$PriceChange24HrBtm = 0;}
  if (!empty($_POST['7DEnable'])){if ($_POST['7DEnable'] == "Yes"){$sDEnable = 1;}else{$sDEnable = 0;}}else{ $sDEnable = 0;}
  //if (!empty($_POST['7DEnable'])){$sDEnable = $_POST['7DEnable'];}else{$sDEnable = 0;}
  if (!empty($_POST['PriceChange7DTop'])){$PriceChange7DTop = $_POST['PriceChange7DTop'];}else{$PriceChange7DTop = 0;}
  if (!empty($_POST['PriceChange7DBtm'])){$PriceChange7DBtm = $_POST['PriceChange7DBtm'];}else{$PriceChange7DBtm = 0;}
  if (!empty($_POST['BuyPatternEnabled'])){if ($_POST['BuyPatternEnabled'] == "Yes"){$BuyPatternEnabled = 1;}else{$BuyPatternEnabled = 0;}}else{ $BuyPatternEnabled = 0;}
  //if (!empty($_POST['BuyPatternEnabled'])){$BuyPatternEnabled = $_POST['BuyPatternEnabled'];}else{$BuyPatternEnabled = 0;}
  //if (!empty($_POST['BuyPattern1'])){$BuyPattern1 = $_POST['BuyPattern1'];}else{$BuyPattern1 = "";}
  //if (!empty($_POST['BuyPattern2'])){$BuyPattern2 = $_POST['BuyPattern2'];}else{$BuyPattern2 = "";}
  //if (!empty($_POST['BuyPattern3'])){$BuyPattern3 = $_POST['BuyPattern3'];}else{$BuyPattern3 = "";}
  //if (!empty($_POST['BuyPattern4'])){$BuyPattern4 = $_POST['BuyPattern4'];}else{$BuyPattern4 = "";}
  if (!empty($_POST['PriceDiff1Enable'])){if ($_POST['PriceDiff1Enable'] == "Yes"){$PriceDiff1Enable = 1;}else{$PriceDiff1Enable = 0;}}else{ $PriceDiff1Enable = 0;}
  //if (!empty($_POST['PriceDiff1Enable'])){$PriceDiff1Enable = $_POST['PriceDiff1Enable'];}else{$PriceDiff1Enable = 0;}
  if (!empty($_POST['PriceDiff1Top'])){$PriceDiff1Top = $_POST['PriceDiff1Top'];}else{$PriceDiff1Top = 0;}
  if (!empty($_POST['PriceDiff1Btm'])){$PriceDiff1Btm = $_POST['PriceDiff1Btm'];}else{$PriceDiff1Btm = 0;}
  //if (!empty($_POST['PriceDiff2Enable'])){if ($_POST['PriceDiff2Enable'] == "Yes"){$PriceDiff2Enable = 1;}else{$PriceDiff2Enable = 0;}}else{ $PriceDiff2Enable = 0;}
  //if (!empty($_POST['PriceDiff2Enable'])){$PriceDiff2Enable = $_POST['PriceDiff2Enable'];}else{$PriceDiff2Enable = 0;}
  //if (!empty($_POST['PriceDiff2Top'])){$PriceDiff2Top = $_POST['PriceDiff2Top'];}else{$PriceDiff2Top = 0;}
  //if (!empty($_POST['PriceDiff2Btm'])){$PriceDiff2Btm = $_POST['PriceDiff2Btm'];}else{$PriceDiff2Btm = 0;}
  //if (!empty($_POST['PriceDiff3Enable'])){if ($_POST['PriceDiff3Enable'] == "Yes"){$PriceDiff3Enable = 1;}else{$PriceDiff3Enable = 0;}}else{ $PriceDiff3Enable = 0;}
  //if (!empty($_POST['PriceDiff3Enable'])){$PriceDiff3Enable = $_POST['PriceDiff3Enable'];}else{$PriceDiff3Enable = 0;}
  //if (!empty($_POST['PriceDiff3Top'])){$PriceDiff3Top = $_POST['PriceDiff3Top'];}else{$PriceDiff3Top = 0;}
  //if (!empty($_POST['PriceDiff3Btm'])){$PriceDiff3Btm = $_POST['PriceDiff3Btm'];}else{$PriceDiff3Btm = 0;}
  //if (!empty($_POST['PriceDiff4Enable'])){if ($_POST['PriceDiff4Enable'] == "Yes"){$PriceDiff4Enable = 1;}else{$PriceDiff4Enable = 0;}}else{ $PriceDiff4Enable = 0;}
  //if (!empty($_POST['PriceDiff4Enable'])){$PriceDiff4Enable = $_POST['PriceDiff4Enable'];}else{$PriceDiff4Enable = 0;}
  //if (!empty($_POST['PriceDiff4Top'])){$PriceDiff4Top = $_POST['PriceDiff4Top'];}else{$PriceDiff4Top = 0;}
  //if (!empty($_POST['PriceDiff4Btm'])){$PriceDiff4Btm = $_POST['PriceDiff4Btm'];}else{$PriceDiff4Btm = 0;}
  if (!empty($_POST['sendEmail'])){if ($_POST['sendEmail'] == "Yes"){$sendEmail = 1;}else{$sendEmail = 0;}}else{ $sendEmail = 0;}
  //if (!empty($_POST['sendEmail'])){ $sendEmail = $_POST['sendEmail'];}else{ $sendEmail = 0;}
  if (!empty($_POST['sellCoin'])){if ($_POST['sellCoin'] == "Yes"){$sellCoin = 1;}else{$sellCoin = 0;}}else{ $sellCoin = 0;}
  //if (!empty($_POST['sellCoin'])){ $sellCoin = $_POST['sellCoin'];}else{ $sellCoin = 0;}
  if (!empty($_POST['ProfitSaleEnable'])){if ($_POST['ProfitSaleEnable'] == "Yes"){$ProfitSaleEnable = 1;}else{$ProfitSaleEnable = 0;}}else{ $ProfitSaleEnable = 0;}
  //if (!empty($_POST['ProfitSaleEnable'])){$ProfitSaleEnable = $_POST['ProfitSaleEnable'];}else{$ProfitSaleEnable = 0;}
  if (!empty($_POST['ProfitSaleTop'])){$ProfitSaleTop = $_POST['ProfitSaleTop'];}else{$ProfitSaleTop = 0;}
  if (!empty($_POST['ProfitSaleBtm'])){$ProfitSaleBtm = $_POST['ProfitSaleBtm'];}else{$ProfitSaleBtm = 0;}
  //if (!empty($_POST['OverrideBuyBackAmount'])){$overrideBuyBackAmount = $_POST['OverrideBuyBackAmount'];}else{$overrideBuyBackAmount = 0;}
  if (!empty($_POST['OverrideBuyBackAmount'])){if ($_POST['OverrideBuyBackAmount'] == "Yes"){$overrideBuyBackAmount = 1;}else{$overrideBuyBackAmount = 0;}}else{ $overrideBuyBackAmount = 0;}
  //if (!empty($_POST['OverrideBuyBackSaving'])){$overrideBuyBackSaving = $_POST['OverrideBuyBackSaving'];}else{$overrideBuyBackSaving = 0;}
  if (!empty($_POST['OverrideBuyBackSaving'])){if ($_POST['OverrideBuyBackSaving'] == "Yes"){$overrideBuyBackSaving = 1;}else{$overrideBuyBackSaving = 0;}}else{ $overrideBuyBackSaving = 0;}
  $sellPriceMinEnabled = postDataYesNo($_POST['sellPriceMinEnabled']);
  $sellPriceMin = postData($_POST['sellPriceMin']);
  $limitToCoin = $_POST['LimitToCoinID'];
  $sellPatternEnabled = postDataYesNo($_POST['SellPatternEnabled']);
  $sellPattern =  $_POST['SellPattern'];
  $autoSellCoinEnabled = postDataYesNo($_POST['AutoSellCoinEnabled']);
  $coinPricePatternEnabled = postDataYesNo($_POST['CoinPricePatternEnabled']);
  $coinPricePattern = $_POST['PctFromHighSellPriceEnable'];
  $pctFromHighSellPriceEnable = postDataYesNo($_POST['PctFromHighSellPriceEnable']);
  $pctFromHighSellPrice = $_POST['PctFromHighSellPrice'];
  $hoursFlatEnable = postDataYesNo($_POST['HoursFlatEnable']);
  $hoursFlat = $_POST['HoursFlat'];
  $reEnableBuyRuleEnable = postDataYesNo($_POST['ReEnableBuyRuleEnable']);
  $ruleName  = $_POST['RuleName'];
  $sellFallsInPrice = $_POST['SellFallsInPrice'];
  $hoursPastBuySellEnable  = postDataYesNo($_POST['HoursPastBuySellEnable']);
  $hoursPastBuy = $_POST['HoursPastBuy'];
  $calculatedSellPctEnable = postDataYesNo($_POST['CalculatedSellPctEnable']);
  $calculatedSellPctStart = $_POST['CalculatedSellPctStart'];
  $calculatedSellPctEnd = $_POST['CalculatedSellPctEnd'];
  $calculatedSellPctDays = $_POST['CalculatedSellPctDays'];
  $calculatedSellPctReduction  = $_POST['CalculatedSellPctReduction'];
  $bypassTrackingSellEnable = postDataYesNo($_POST['BypassTrackingSellEnable']);
  $hoursAfterPurchaseStart = $_POST['HoursAfterPurchaseStart'];
  $hoursAfterPurchaseEnd = $_POST['HoursAfterPurchaseEnd'];
  if (!empty($_POST['HoursAfterPurchaseStart'])){$hoursAfterPurchaseStart = $_POST['HoursAfterPurchaseStart'];}else{$hoursAfterPurchaseStart = 0;}
  if (!empty($_POST['HoursAfterPurchaseEnd'])){$hoursAfterPurchaseEnd = $_POST['HoursAfterPurchaseEnd'];}else{$hoursAfterPurchaseEnd = 0;}
  //$autoSellCoinEnabled = postDataYesNo($_POST['AutoSellCoinEnabled']);
  // Create connection
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $userID = $_SESSION['ID'];
  $sql = "UPDATE `SellRules` SET `SellCoin`=$sellCoin,`SendEmail`=$sendEmail,`MarketCapEnabled`=$MarketCapEnable,`MarketCapTop`=$MarketCapTop,
  `MarketCapBtm`=$MarketCapBtm,`1HrChangeEnabled`=$oneHrEnable,`1HrChangeTop`=$PriceChange1HrTop,`1HrChangeBtm`=$PriceChange1HrBtm,`24HrChangeEnabled`=$t4HrEnable,`24HrChangeTop`=$PriceChange24HrTop,`24HrChangeBtm`=$PriceChange24HrBtm,`7DChangeEnabled`=$sDEnable,
  `7DChangeTop`=$PriceChange7DTop,`7DChangeBtm`=$PriceChange7DBtm,`ProfitPctEnabled`=$ProfitSaleEnable,`ProfitPctTop`=$ProfitSaleTop,`ProfitPctBtm`=$ProfitSaleBtm,`CoinPriceEnabled`=$PriceDiff1Enable,`CoinPriceTop`=$PriceDiff1Top,`CoinPriceBtm`=$PriceDiff1Btm,
  `SellOrdersEnabled`=$BuyOrdersEnabled,`SellOrdersTop`=$BuyOrdersTop,`SellOrdersBtm`=$BuyOrdersBtm,`VolumeEnabled`=$VolumeEnable,`VolumeTop`=$VolumeTop,`VolumeBtm`=$VolumeBtm ,`sellPriceMinEnabled`=$sellPriceMinEnabled,`sellPriceMin`=$sellPriceMin
  ,`AutoSellCoinEnabled` = $autoSellCoinEnabled, `LimitToCoinID` = (SELECT `ID` FROM `Coin` WHERE `Symbol` = '$limitToCoin' and `BuyCoin` = 1), `LimitToCoin` = '$limitToCoin', `SellPatternEnabled` = $sellPatternEnabled, `SellPattern` = '$sellPattern',
  `CoinPricePatternEnabled` = $coinPricePatternEnabled, `CoinPricePattern` = '$coinPricePattern', `AutoSellCoinEnabled` = $autoSellCoinEnabled,`PctFromHighSellPriceEnabled` = $pctFromHighSellPriceEnable,`NoOfHoursFlatEnabled` = $hoursFlatEnable,`NoOfHoursFlat` = $hoursFlat
  ,`PctUnderMaxPrice` = $pctFromHighSellPrice, `ReEnableBuyRuleEnabled` = $reEnableBuyRuleEnable, `RuleName` = '$ruleName', `SellFallsInPrice` = $sellFallsInPrice,`HoursPastBuyToSellEnabled` = $hoursPastBuySellEnable, `HoursPastBuyToSell` = $hoursPastBuy
  , `CalculatedSellPctEnabled` = $calculatedSellPctEnable, `CalculatedSellPctStart` = $calculatedSellPctStart, `CalculatedSellPctEnd` = $calculatedSellPctEnd, `CalculatedSellPctDays` = $calculatedSellPctDays, `BypassTrackingSell` = $bypassTrackingSellEnable
  ,`CalculatedSellPctReduction` = $calculatedSellPctReduction,`OverrideBuyBackAmount` = $overrideBuyBackAmount, `OverrideBuyBackSaving` = $overrideBuyBackSaving, `HoursAfterPurchaseToStart` = $hoursAfterPurchaseStart, `HoursAfterPurchaseToEnd` = $hoursAfterPurchaseEnd
  WHERE `ID` = $id";
  print_r($sql);


  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();
  header('Location: AddNewSettingSell.php?edit='.$id);
  //http://www.investment-tracker.net/Investment-Tracker/Cryptobot/1/AddNewSettingSell.php?edit=24
}

function getRules($id){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT
`ID`,`UserID`,`SellCoin`,`SendEmail`,`BuyOrdersEnabled`,`BuyOrdersTop`,`BuyOrdersBtm`,`MarketCapEnabled`, `MarketCapTop`, `MarketCapBtm`, `1HrChangeEnabled`,
 `1HrChangeTop`, `1HrChangeBtm`, `24HrChangeEnabled`, `24HrChangeTop`,`24HrChangeBtm`, `7DChangeEnabled`, `7DChangeTop`, `7DChangeBtm`, `ProfitPctEnabled`,
 `ProfitPctTop`, `ProfitPctBtm`, `CoinPriceEnabled`, `CoinPriceTop`, `CoinPriceBtm`, `SellOrdersEnabled`, `SellOrdersTop`, `SellOrdersBtm`, `VolumeEnabled`,
  `VolumeTop`, `VolumeBtm`, `Email`, `UserName`, `APIKey`, `APISecret`,`SellPriceMinEnabled`,`SellPriceMin`,`LimitToCoin`,`AutoSellCoinEnabled`, 'AutoSellPrice'
  ,`SellPatternEnabled`, `SellPattern`,`CoinPricePatternEnabled`,`CoinPricePattern`,`PctFromHighSellPriceEnabled`,`NoOfHoursFlatEnabled`,`NoOfHoursFlat`,`PctUnderMaxPrice`
,`ReEnableBuyRuleEnabled`,`RuleName`,`SellFallsInPrice`,`HoursPastBuyToSellEnabled`, `HoursPastBuyToSell`, `CalculatedSellPctEnabled`, `CalculatedSellPctStart`, `CalculatedSellPctEnd`
, `CalculatedSellPctDays`,`BypassTrackingSell`,`CalculatedSellPctReduction`,`OverrideBuyBackAmount`, `OverrideBuyBackSaving`,`HoursAfterPurchaseToStart`, `HoursAfterPurchaseToEnd`
FROM `View14_UserSellRules` WHERE `ID` = $id";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  //print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['UserID'],$row['SellCoin'],$row['SendEmail'],$row['BuyOrdersEnabled'],$row['BuyOrdersTop'],$row['BuyOrdersBtm'], //6
      $row['MarketCapEnabled'],$row['MarketCapTop'],$row['MarketCapBtm'],$row['1HrChangeEnabled'],$row['1HrChangeTop'],$row['1HrChangeBtm'],$row['24HrChangeEnabled'],//13
      $row['24HrChangeTop'],$row['24HrChangeBtm'],$row['7DChangeEnabled'],$row['7DChangeTop'],$row['7DChangeBtm'],$row['ProfitPctEnabled'],$row['ProfitPctTop'],//20
      $row['ProfitPctBtm'],$row['CoinPriceEnabled'],$row['CoinPriceTop'],$row['CoinPriceBtm'],$row['SellOrdersEnabled'],$row['SellOrdersTop'],$row['SellOrdersBtm'],//27
      $row['VolumeEnabled'],$row['VolumeTop'],$row['VolumeBtm'],$row['Email'],$row['UserName'],$row['APIKey'],$row['APISecret'],$row['SellPriceMinEnabled']//35
      ,$row['SellPriceMin'],$row['LimitToCoin'],$row['AutoSellCoinEnabled'],$row['AutoSellPrice'],$row['SellPatternEnabled'],$row['SellPattern'],$row['CoinPricePatternEnabled'],$row['CoinPricePattern']//43
      ,$row['PctFromHighSellPriceEnabled'],$row['NoOfHoursFlatEnabled'],$row['NoOfHoursFlat'],$row['PctUnderMaxPrice'],$row['ReEnableBuyRuleEnabled'],$row['RuleName']//49
      ,$row['SellFallsInPrice'],$row['HoursPastBuyToSellEnabled'],$row['HoursPastBuyToSell'],$row['CalculatedSellPctEnabled'],$row['CalculatedSellPctStart'],$row['CalculatedSellPctEnd'] //55
      ,$row['CalculatedSellPctDays'],$row['BypassTrackingSell'],$row['CalculatedSellPctReduction'],$row['OverrideBuyBackAmount'],$row['OverrideBuyBackSaving']//60
      ,$row['HoursAfterPurchaseToStart'],$row['HoursAfterPurchaseToEnd']);//62
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
  if ($value == 1 || $value == 'Yes' ){
    $option1 = "Yes"; $option2 = "No";
  }else{
    $option1 = "No"; $option2 = "Yes";
  }
  echo "<select name='$idName' id='$idName' class='enableTextBox'>
  <option value='".$option1."'>".$option1."</option>
    <option value='".$option2."'>".$option2."</option></select>
    <label for='$idName'>$RealName</label>
     <br/>";
}

function addNewTwoOptionAuto($RealName, $idName, $value){
  if ($value == 1 || $value == 'Yes' ){
    $option1 = "Yes"; $option2 = "No";$option3 = "Auto";
  }elseif ($value == 2 || $value == 'Auto' ){
    $option1 = "Auto"; $option2 = "Yes";$option3 = "No";
  }else{
    $option1 = "No"; $option2 = "Yes";$option3 = "Auto";
  }
  echo "<select name='$idName' id='$idName' class='enableTextBox'>
  <option value='".$option1."'>".$option1."</option>
    <option value='".$option2."'>".$option2."</option>
      <option value='".$option3."'>".$option3."</option></select>
    <label for='$idName'>$RealName</label>
     <br/>";
}

function addNewThreeOption($RealName, $idName, $value){
  if ($value == 1){$nOption1 = "Up"; $nOption2 = "Equal";$nOption3 = "Down";}
  elseif ($RealName == -1){$nOption1 = "Down"; $nOption2 = "Equal";$nOption3 = "Up";}
  else{$nOption1 = "Equal"; $nOption2 = "Down";$nOption3 = "Up";}
  echo "<select name='$idName' id='$idName' class='enableTextBox'>
  <option value='".$nOption1."'>".$nOption1."</option>
    <option value='".$nOption2."'>".$nOption2."</option>
    <option value='".$nOption3."'>".$nOption3."</option></select>
    <label for='$idName'>".$RealName."</label><br/><br/>";
}

function getPricePatternSell($id){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `BuyRuleID`,`SellRuleID`,`CoinID`,`Price`,`Symbol`,`UserID`,`LowPrice` FROM `CoinPriceMatchView` WHERE (`SellRuleID` = $id )";
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

function getPriceTrendSell($id){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `BuyRuleID`,`SellRuleID`,`CoinPattern`,`UserID` FROM `CoinPricePatternView` WHERE `SellRuleID` = $id ";
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

function displayListBox($tempAry, $name, $enabled){
  $tempCount = count($tempAry);
  $readOnly = "";
  //echo "<BR> ENABLED: ".$enabled;
  if ($enabled == 0){$readOnly = " style='color:Gray' readonly ";}
  Echo "<select name='$name' size='3' $readOnly>";
  for ($i=0; $i<$tempCount; $i++){
    $price = $tempAry[$i][3]; $symbol = $tempAry[$i][4];  $lowPrice = $tempAry[$i][6];
    $result = $symbol.":".$price.":".$lowPrice;

      echo "<option value='$result'>$result</option>";
  }
  echo "</Select>";
}

function displayListBoxNormal($tempAry, $num, $name, $enabled){
  $tempCount = count($tempAry);
  $readOnly = "";
  //echo "<BR> ENABLED: ".$enabled;
  if ($enabled == 0){$readOnly = " style='color:Gray' readonly ";}
  Echo "<select name='$name' size='3' $readOnly>";
  for ($i=0; $i<$tempCount; $i++){
    $result = $tempAry[$i][$num]; //$symbol = $tempAry[$i][4]; $result = $symbol.":".$price;

      echo "<option value='$result'>$result</option>";
  }
  echo "</Select>";
}

function displaySymbols($symbolList,$num, $name, $enabled){
  $symbolListCount = count($symbolList);
  $readOnly = "";
  //echo "<BR> ENABLED: ".$enabled;
  if ($enabled == 0){$readOnly = " style='color:Gray' readonly ";}
  Echo "<select name='$name' $readOnly>";
  for ($i=0; $i<$symbolListCount; $i++){
    $symbol = $symbolList[$i][$num];
    //$name = str_replace('-1','Minus1',$name);
    echo "<option value='$symbol'>$symbol</option>";
  }
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

function displayAutoListBox($tempAry){
  $tempCount = count($tempAry);
  for ($i=0; $i<$tempCount; $i++){
     $symbol = $tempAry[$i][3]; $topPrice = $tempAry[$i][1];
     $result = $symbol.":".$topPrice;

      echo "<option value='$symbol'>$result</option>";
  }
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

function displayEdit($id){
  $formSettings = getRules($id);
  $pricePattern = getPricePatternSell($id);
  $priceTrendList = getPriceTrendSell($id);
  $symbolList = getSymbols();
  $cryptoAutoPrices = getAutoPrices();
  $coinList = getCoinIDs();
  $comboList = Array('-1','0','1','*');
  $_GET['edit'] = null;
  echo "<h3><a href='Settings.php'>User Settings</a> &nbsp > &nbsp <a href='BuySettings.php'>Buy Settings</a> &nbsp > &nbsp <a href='SellSettings.php'>Sell Settings</a> &nbsp > &nbsp <a href='Settings_Patterns.php'>Setting Patterns</a></h3>";
  echo "<form action='AddNewSettingSell.php?editedUserReady=".$id."' method='post'>";
  echo "<div class='settingsformMain'>";echo "<div class='settingsform'>";
  addNewText('Rule Name: ','RuleName',$formSettings[0][49],37, 'Eg 50', False,1);
  echo "</div>";
  echo "<div class='settingsform'>";
    echo "<H3>Market Cap</H3>";
  addNewTwoOption('MarketCapEnable: ','VolumeEnable',$formSettings[0][7]);
  addNewText('MarketCapTop: ','VolumeTop',$formSettings[0][8],37, 'Eg 50', False,$formSettings[0][7]);
  addNewText('MarketCapBtm: ','VolumeBtm',$formSettings[0][9],37, 'Eg 50', False,$formSettings[0][7]);
  echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>Volume</H3>";


  addNewTwoOption('VolumeEnable: ','VolumeEnable',$formSettings[0][28]);
  addNewText('VolumeTop: ','VolumeTop',$formSettings[0][29],37, 'Eg 50', False,$formSettings[0][28]);
  addNewText('VolumeBtm: ','VolumeBtm',$formSettings[0][30],37, 'Eg 50', False,$formSettings[0][28]);

  echo "</div>";
  //echo "<div class='settingsform'>";
  //echo "<H3>Sell Orders</H3>";
  //addNewTwoOption('SellOrdersEnabled: ','VolumeEnable',$formSettings[0][25]);
  //addNewText('SellOrdersTop: ','BuyOrdersTop',$formSettings[0][26],37, 'Eg 50', False,$formSettings[0][25]);
  //addNewText('SellOrdersBtm: ','BuyOrdersBtm',$formSettings[0][27],37, 'Eg 50', False,$formSettings[0][25]);

  //echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>1 Hour Price</H3>";
  addNewTwoOption('1HrEnable: ','1HrEnable',$formSettings[0][10]);
  addNewText('PriceChange1HrTop: ','PriceChange1HrTop',$formSettings[0][11],37, 'Eg 50', False,$formSettings[0][10]);
  addNewText('PriceChange1HrBtm: ','PriceChange1HrBtm',$formSettings[0][12],37, 'Eg 50', False,$formSettings[0][10]);

  echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>24 Hour Price</H3>";
  addNewTwoOption('24HrEnable:','24HrEnable',$formSettings[0][13]);
  addNewText('PriceChange24HrTop: ','PriceChange24HrTop',$formSettings[0][14],37, 'Eg 50', False,$formSettings[0][13]);
  addNewText('PriceChange24HrBtm: ','PriceChange24HrBtm',$formSettings[0][15],37, 'Eg 50', False,$formSettings[0][13]);

  echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>7 Day Price</H3>";
  addNewTwoOption('7DEnable: ','7DEnable',$formSettings[0][16]);
  addNewText('PriceChange7DTop: ','PriceChange7DTop',$formSettings[0][17],37, 'Eg 50', False,$formSettings[0][16]);
  addNewText('PriceChange7DBtm: ','PriceChange7DBtm',$formSettings[0][18],37, 'Eg 50', False,$formSettings[0][16]);

  echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>Price Difference</H3>";
  addNewTwoOption('PriceDiff1Enable: ','PriceDiff1Enable',$formSettings[0][22]);
  addNewText('PriceDiff1Top: ','PriceDiff1Top',$formSettings[0][23],37, 'Eg 50', False,$formSettings[0][22]);
  addNewText('PriceDiff1Btm: ','PriceDiff1Btm',$formSettings[0][24],37, 'Eg 50', False,$formSettings[0][22]);

  echo "</div>";


  echo "<div class='settingsform'>";
  echo "<H3>Profit Sale</H3>";
  addNewTwoOptionAuto('Profit Sale Enable: ','ProfitSaleEnable',$formSettings[0][19]);
  addNewText('Profit Sale Top: ','ProfitSaleTop',$formSettings[0][20],37, 'Eg 50', False,$formSettings[0][19]);
  addNewText('rofit Sale Btm: ','ProfitSaleBtm',$formSettings[0][21],37, 'Eg 50', False,$formSettings[0][19]);

  echo "</div>";

  ///echo "<div class='settingsform'>";
  //echo "<H3>Sell Pattern</H3>";
  //addNewTwoOption('Sell Pattern Enabled:','SellPatternEnabled',$formSettings[0][40]);
  //addNewText('Sell Pattern: ','SellPattern',$formSettings[0][41],40);
  //echo "</div>";

  echo "<div class='settingsform'>";
  echo "<H3>New Sell Pattern</H3>";
  addNewTwoOption('Sell Pattern Enabled:','SellPatternEnabled',$formSettings[0][40]);
  echo "<div class='settingsformCmbo'>";
  displayTrendSymbols($comboList,'selectCmboTrend1',$formSettings[0][40]);
  displayTrendSymbols($comboList,'selectCmboTrend2',$formSettings[0][40]);
  displayTrendSymbols($comboList,'selectCmboTrend3',$formSettings[0][40]);
  displayTrendSymbols($comboList,'selectCmboTrend4',$formSettings[0][40]);
  displayListBoxNormal($priceTrendList,2,'listboxTrend',$formSettings[0][40]);
  echo "<input type='submit' name='publishTrend' value='+'><input type='submit' name='removeTrend' value='-'></div></div>";


  echo "<div class='settingsform'>";
  echo "<H3>Auto Sell</H3>";
  addNewTwoOption('Auto Sell Coin Enabled:','AutoSellCoinEnabled',$formSettings[0][38]);
  //addNewText('Coin Price Pattern: ','CoinPricePattern',$formSettings[0][43],41);
  echo "<select name='listbox' size='3' readonly>";
  displayAutoListBox($cryptoAutoPrices);
  echo "</select>";
  echo "</div>";


  echo "<div class='settingsform'>";
  echo "<H3>New Coin Price Pattern</H3>";
  //$coinPricePatEnabled = $formSettings[0][42];
  //addNewTwoOption('Coin Price Pattern Enabled:','CoinPricePatternEnabled',$coinPricePatEnabled);
  //echo "<div class='settingsformCmbo'>";
  //displaySymbols($symbolList,0,'select',$coinPricePatEnabled);
  //addNewText('Coin Price Top: ', 'CPrice', 0, 52, 'Eg 7000.00', True,$coinPricePatEnabled);
  //addNewText('Coin Price: Bottom', 'CPricebtm', 0, 52, 'Eg 7000.00', True,$coinPricePatEnabled);
  //echo "<a href='AddNewSetting.php?add=$id'>Add</a>";
  //displayListBox($pricePattern,'listbox',$coinPricePatEnabled);
  //echo "<input type='submit' name='publish' value='+'><input type='submit' name='remove' value='-'></div></div>";
  echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>% From High Sell Price</H3>";
  addNewTwoOption('Pct From High Sell Price Enable: ','PctFromHighSellPriceEnable',$formSettings[0][44]);
  addNewText('Pct From High Sell Price: ','PctFromHighSellPrice',$formSettings[0][47],37, '5%', False,$formSettings[0][44]);
  echo "</div>";


  echo "<div class='settingsform'>";
  echo "<H3>Hour Rules</H3>";
  addNewTwoOptionAuto('Hours Flat Enable: ','HoursFlatEnable',$formSettings[0][45]);
  addNewText('Hours Flat: ','HoursFlat',$formSettings[0][46],37, '30', False,$formSettings[0][45]);
  addNewTwoOption('Hours Past Buy To Sell Enable: ','HoursPastBuySellEnable',$formSettings[0][51]);
  addNewText('Hours Past Buy To Sell: ','HoursPastBuy',$formSettings[0][52],37, '30', False,$formSettings[0][51]);
  addNewTwoOption('Calculated Sell Pct Enable: ','CalculatedSellPctEnable',$formSettings[0][53]);
  addNewText('Calculated Sell Pct Start: ','CalculatedSellPctStart',$formSettings[0][54],37, '30', False,$formSettings[0][53]);
  addNewText('Calculated Sell Pct End: ','CalculatedSellPctEnd',$formSettings[0][55],37, '30', False,$formSettings[0][53]);
  addNewText('Calculated Sell Pct Days: ','CalculatedSellPctDays',$formSettings[0][56],37, '30', False,$formSettings[0][53]);
  addNewText('Calculated Sell Pct Reduction: ','CalculatedSellPctReduction',$formSettings[0][58],37, '30', False,$formSettings[0][53]);
  echo "</div>";

  echo "<div class='settingsform'>";
    echo "<H3>BuyBack Rules</H3>";
    //addNewText('Override BuyBack Amount: ','OverrideBuyBackAmount',$formSettings[0][59],37, '30', False,1);
    addNewTwoOption('Override BuyBack Amount: ','OverrideBuyBackAmount',$formSettings[0][59]);
    //addNewText('Override BuyBack Saving: ','OverrideBuyBackSaving',$formSettings[0][60],37, '30', False,1);
    addNewTwoOption('Override BuyBack Saving: ','OverrideBuyBackSaving',$formSettings[0][60]);
  echo "</div>";

  echo "<div class='settingsform'>";
  echo "<H3>Admin</H3>";
  addNewTwoOption('Send Email: ','sendEmail',$formSettings[0][3]);

  addNewTwoOption('Sell Coin: ','sellCoin',$formSettings[0][2]);
  addNewTwoOptionAuto('Sell Price Min Enabled:','sellPriceMinEnabled',$formSettings[0][35]);
  addNewText('Sell Price Min: ','sellPriceMin',$formSettings[0][36],37, 'Eg 50', False,1);

  //addNewText('Limit To Coin: ','limitToCoin',$formSettings[0][37],38, 'Eg 50', False,1);
  $coinListSize = Count($coinList);
    Echo "<select name='LimitToCoinID'>";
    for ($w=0; $w<$coinListSize;$w++){
      $limitCoinID = $coinList[$w][0];   $sym = $coinList[$w][1]; $base = $coinList[$w][2];
      $savedSym = $formSettings[0][37];
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
  addNewTwoOption('ReEnable Buy Rule Enable: ','ReEnableBuyRuleEnable',$formSettings[0][48]);
  addNewText('SellFallsInPrice: ','SellFallsInPrice',$formSettings[0][50],37, 'Eg 50', False,1);
  addNewTwoOption('Bypass Tracking Sell Enable: ','BypassTrackingSellEnable',$formSettings[0][57]);
  //addNewTwoOption('Auto Sell Enabled:','AutoSellCoinEnabled',$formSettings[0][38]);
  //addNewText('Auto Sell Price: ','AutoSellPrice',$formSettings[0][39],39, 'Eg 50', False,0);
addNewText('Hours After Purchase To Start Sell Rule: ','HoursAfterPurchaseStart',$formSettings[0][61],38, 'Eg 50', False,1);
addNewText('Hours After Purchase To End Sell Rule','HoursAfterPurchaseEnd',$formSettings[0][62],39, 'Eg 50', False,1);
  //addNewText('Auto Sell Price: ','AutoSellPrice',$formSettings[0][39],39, 'Eg 50', False,0);
  echo "</div>";
  echo "<div class='settingsform'>
    <input type='submit' name='submit' value='Update' class='settingsformsubmit' tabindex='39'>
  </div></div>";
  echo "</form>";
}
displaySideColumn();
?>
</body>
</html>
