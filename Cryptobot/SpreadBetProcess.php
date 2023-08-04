<html>
<?php
ini_set('max_execution_time', 300);
require('includes/newConfig.php');
//set_include_path('/home/stevenj1979/repositories/gdax/src/Configuration.php');
include_once ('/home/stevenj1979/SQLData.php');
include_once ('/home/stevenj1979/Encrypt.php');

//Define("sQLUpdateLog","0");
//Define("SQLProcedureLog","0");

$apikey=getAPIKey();
$apisecret=getAPISecret();
//echo "<BR>API Secret is:  $apisecret";
$tmpTime = "+5 seconds";
if (!empty($argv[1])){
  parse_str($argv[1], $params);
  $tmpTime = str_replace('_', ' ', $params['mins']);
  echo $tmpTime;
  //error_log($argv[1], 0);
}
//echo "<BR> isEmpty : ".empty($_GET['mins']);
if (!empty($_GET['mins'])){
  $tmpTime = str_replace('_', ' ', $_GET['mins']);
  echo "<br> GETMINS: ".$_GET['mins'];
}

function toggleSBRule($SBRuleID, $action){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "UPDATE `SpreadBetSettings` SET `Enabled`= $action WHERE `SpreadBetRuleID` = $SBRuleID";
    //LogToSQL("updateTransToSpread",$sql,3,1);
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("enableSBRule: ".$sql, 'BuyCoin', 0);
  newLogToSQL("toggleSBRule",$sql,3,0,"SQL","SBRuleID:$SBRuleID");
}

function update24Hrand7DPrice($hr24, $d7, $SBRuleID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "UPDATE `SpreadBetSettings` SET `Hr24BuyPrice`= $hr24,`D7BuyPrice`= $d7  WHERE `SpreadBetRuleID` = $SBRuleID";
    //LogToSQL("updateTransToSpread",$sql,3,1);
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("update24Hrand7DPrice: ".$sql, 'BuyCoin', 0);
  newLogToSQL("update24Hrand7DPrice",$sql,3,0,"SQL","SBRuleID:$SBRuleID");
}

function getSpreadBetAll(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql ="SELECT `ID`, `Name`, `Live1HrChange`, `Last1HrChange`, `Hr1ChangePctChange`, `Live24HrChange`, `Last24HrChange`, `Hr24ChangePctChange`, `Live7DChange`, `Last7DChange`
  , `D7ChangePctChange`, `LiveCoinPrice`, `LastCoinPrice`, `CoinPricePctChange`,  `BaseCurrency`, `Price4Trend`, `Price3Trend`, `LastPriceTrend`, `LivePriceTrend`, `AutoBuyPrice`
  , `1HrPriceChangeLive`, `1HrPriceChangeLast`, `1HrPriceChange3`, `1HrPriceChange4`,`APIKey`,`APISecret`,`KEK`,`UserID`,`Email`,`UserName`,`SpreadBetTransID`, `Hr1BuyPrice`, `Hr24BuyPrice`
  , `D7BuyPrice`,`PctofSixMonthHighPrice`,`PctofAllTimeHighPrice`,`DisableUntil`,`UserID`,`Hr1BuyEnable`,`Hr1BuyDisable`,`Hr24andD7StartPrice`,`Month6TotalPrice`,`AllTimeTotalPrice`
  ,`Hr1EnableStartPrice`,`MinsToCancel`,`BuyFallsInPrice`,`SellRaisesInPrice`,`BullBearStatus`
  FROM `SpreadBetCoinStatsView_ALL`";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'], $row['Name'], $row['Live1HrChange'], $row['Last1HrChange'], $row['Hr1ChangePctChange'], $row['Live24HrChange'], $row['Last24HrChange'], $row['Hr24ChangePctChange'], $row['Live7DChange'], $row['Last7DChange']//9
      , $row['D7ChangePctChange'], $row['LiveCoinPrice'], $row['LastCoinPrice'], $row['CoinPricePctChange'], $row['BaseCurrency'], $row['Price4Trend'], $row['Price3Trend'], $row['LastPriceTrend'], $row['LivePriceTrend'], $row['AutoBuyPrice']//19
      , $row['1HrPriceChangeLive'], $row['1HrPriceChangeLast'], $row['1HrPriceChange3'], $row['1HrPriceChange4'], $row['APIKey'], $row['APISecret'], $row['KEK'], $row['UserID'], $row['Email'], $row['UserName'], $row['SpreadBetTransID'] //30
      , $row['Hr1BuyPrice'], $row['Hr24BuyPrice'], $row['D7BuyPrice'], $row['PctofSixMonthHighPrice'], $row['PctofAllTimeHighPrice'], $row['DisableUntil'], $row['UserID'], $row['Hr1BuyEnable'], $row['Hr1BuyDisable'], $row['Hr24andD7StartPrice'] //40
      , $row['Month6TotalPrice'], $row['AllTimeTotalPrice'], $row['Hr1EnableStartPrice'], $row['MinsToCancel'], $row['BuyFallsInPrice'], $row['SellRaisesInPrice'], $row['BullBearStatus']);
  }
  $conn->close();
  return $tempAry;
}

function write1HrEnablePrice($hr1Price, $SBRuleID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "UPDATE `SpreadBetSettings` SET `Hr1BuyEnable` = $hr1Price WHERE `SpreadBetRuleID` = $SBRuleID ";
    //LogToSQL("updateTransToSpread",$sql,3,1);
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("write1HrEnablePrice: ".$sql, 'BuyCoin', 0);
  newLogToSQL("write1HrEnablePrice",$sql,3,0,"SQL","SBRuleID:$SBRuleID");
}

function writeMinsToCancel($mins, $SBRuleID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "UPDATE `SpreadBetSettings` SET `CalculatedMinsToCancel` = $mins WHERE `SpreadBetRuleID` = $SBRuleID ";
    //LogToSQL("updateTransToSpread",$sql,3,1);
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("writeMinsToCancel: ".$sql, 'BuyCoin', 0);
  newLogToSQL("writeMinsToCancel",$sql,3,0,"SQL","SBRuleID:$SBRuleID");
}

function writeFallsinPrice($falls, $SBRuleID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "UPDATE `SpreadBetSettings` SET `CalculatedFallsinPrice` = $falls WHERE `SpreadBetRuleID` = $SBRuleID ";
    //LogToSQL("updateTransToSpread",$sql,3,1);
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("writeFallsinPrice: ".$sql, 'BuyCoin', 0);
  newLogToSQL("writeFallsinPrice",$sql,3,0,"SQL","SBRuleID:$SBRuleID");
}

function writeRaisesinPrice($raises, $SBRuleID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "UPDATE `SpreadBetSettings` SET `CalculatedRisesInPrice` = $raises WHERE `SpreadBetRuleID` = $SBRuleID ";
    //LogToSQL("updateTransToSpread",$sql,3,1);
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("writeRaisesinPrice: ".$sql, 'BuyCoin', 0);
  newLogToSQL("writeRaisesinPrice",$sql,3,0,"SQL","SBRuleID:$SBRuleID");
}

function updateSBTransactionsToNew($sBRuleID,$sBTransID ){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "UPDATE `Transaction` SET `SpreadBetTransactionID` = (SELECT `ID` FROM `SpreadBetTransactions` WHERE `SpreadBetRuleID` = $sBRuleID) where `SpreadBetTransactionID` = $sBTransID
    and `Status` in ('Open','Pending')";
    //LogToSQL("updateTransToSpread",$sql,3,1);
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateSBTransactionsToNew: ".$sql, 'BuyCoin', 0);
  newLogToSQL("updateSBTransactionsToNew",$sql,3,0,"SQL","SBRuleID:$SBRuleID");
}

function updateSBSellTarget($sBRuleID,$sBTransID ){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "Update `SpreadBetSellTarget` `Sbst` Join `Transaction` `Tr` on `Tr`.`ID` = `Sbst`.`TransactionID`
      SET `Sbst`.`SBTransactionID` = (SELECT `ID` FROM `SpreadBetTransactions` WHERE `SpreadBetRuleID` = $sBRuleID)
      where `Tr`.`Status` in ('Open','Pending') and `Sbst`.`SBTransactionID` = $sBTransID ";
    //LogToSQL("updateTransToSpread",$sql,3,1);
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateSBSellTarget: ".$sql, 'BuyCoin', 0);
  newLogToSQL("updateSBSellTarget",$sql,3,0,"SQL","SBRuleID:$sBRuleID");
}

function updateSBTotalProfit($sBRuleID,$sBTransID ){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "UPDATE `SpreadBetTotalProfit` `Sbtp`
              join `Transaction` `Tr` on `Tr`.`ID` = `Sbtp`.`TransactionID`
              SET `Sbtp`.`SpreadBetTransactionID` = (SELECT `ID` FROM `SpreadBetTransactions` WHERE `SpreadBetRuleID` = $sBRuleID)
              where `Sbtp`.`SpreadBetTransactionID` = $sBTransID and `Tr`.`Status` in ('Open','Pending') ";
    //LogToSQL("updateTransToSpread",$sql,3,1);
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateSBTotalProfit: ".$sql, 'BuyCoin', 0);
  newLogToSQL("updateSBTotalProfit",$sql,3,0,"SQL","SBRuleID:$sBRuleID");
}


function getSBProgress($userID, $target){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `UserID`, `PurchasePrice`, `SalePrice`, `Profit`, `CountOfTransactions`, `SpreadBet`, (((1-(`PctProfit`/100))*$target)/$target)*100 as PctOfTarget FROM `SpreadBetTargetsAndProgress` WHERE `UserID` = $userID";
  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['UserID'], $row['PurchasePrice'], $row['SalePrice'], $row['Profit'], $row['CountOfTransactions'], $row['SpreadBet'], $row['PctOfTarget']);
  }
  $conn->close();
  return $tempAry;
}

function getSpreadBetTransactionID(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Sbtpv`.`SpreadBetTransactionID`,sum(`Sbtpv`.`TotalProfit`) as TotalProfit ,sum(`Sbtpv`.`TotalProfitPct`) as TotalProfitPct, `Tr`.`UserID`,`Sbt`.`SpreadBetRuleID`
            FROM `SpreadBetTotalProfitView` `Sbtpv`
            join `Transaction` `Tr` on `Tr`.`ID` = `Sbtpv`.`TransactionID`
            join `SpreadBetTransactions` `Sbt` on `Sbt`.`ID` = `Sbtpv`.`SpreadBetTransactionID`
            group by `SpreadBetTransactionID`";
  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['SpreadBetTransactionID'],$row['TotalProfit'],$row['TotalProfitPct'],$row['UserID'],$row['SpreadBetRuleID']);
  }
  $conn->close();
  return $tempAry;
}

function getSpreadBetTotalProfit($spreadBetTransID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT ifNull(sum(`SalePrice`*`Amount`),0) as TotalProfit  FROM `SpreadBetTotalProfitView` WHERE `SpreadBetTransactionID` = $spreadBetTransID  and `Status` = 'Sold' ";
  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['TotalProfit']);
  }
  $conn->close();
  return $tempAry;
}

function getSpreadBetPurchasePrice($spreadBetTransID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT ifNull(sum(`CoinPrice`*`Amount`),0) as PurchasePrice  FROM `SpreadBetTotalProfitView` WHERE `SpreadBetTransactionID` = $spreadBetTransID";
  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['PurchasePrice']);
  }
  $conn->close();
  return $tempAry;
}

function getSpreadBetLivePrice($spreadBetTransID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT sum(`LiveCoinPrice`*`Amount`) as LivePrice  FROM `SpreadBetTotalProfitView` WHERE `SpreadBetTransactionID` = $spreadBetTransID  and `Status` in ('Open','Pending')";
  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['LivePrice']);
  }
  $conn->close();
  return $tempAry;
}

function getSpreadBetOpenTrans($spreadBetTransID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT count(`TransactionID`) as OpenTransactions  FROM `SpreadBetTotalProfitView` WHERE `SpreadBetTransactionID` = $spreadBetTransID  and `Status` in ('Open','Pending')";
  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['OpenTransactions']);
  }
  $conn->close();
  return $tempAry;
}

function getSpreadBetTargetSellPct($spreadBetRuleID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `PctProfitSell` FROM `SpreadBetSettings` WHERE `SpreadBetRuleID` = $spreadBetRuleID ";
  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['PctProfitSell']);
  }
  $conn->close();
  return $tempAry;
}

function renewSpreadBetTransactionID(){
  $SBTrans = getSpreadBetSellData();
  $SBTransSize = newCount($SBTrans);
  for ($c=0; $c<$SBTransSize; $c++){
    $sBTransID = $SBTrans[$c][0]; $sBRuleID = $SBTrans[$c][56]; $userID = $SBTrans[$c][2]; $profit = $SBTrans[$c][58]; //$sellTargetPct = $SBTrans[$c][55];
    //$SBOpenTotalProfit = getSpreadBetTotalProfit($sBTransID);
    //$SBPurchasePrice = getSpreadBetPurchasePrice($sBTransID);
    //$SBLivePrice = getSpreadBetLivePrice($sBTransID);
    //$SBOpenTransactions = getSpreadBetOpenTrans($sBTransID);
    $tempProfit = getTotalProfitSpreadBetSell($sBTransID);
    //$tempSoldProfit = getSoldProfitSpreadBetSell($ID);
    $purchasePrice = $tempProfit[0][0];
    $livePrice = $tempProfit[0][1] + $tempProfit[0][2];
    $profitTotal = $livePrice-$purchasePrice;
    $profitPct = ($profitTotal/$purchasePrice)*100;
    $sellTargetPct = $tempProfit[0][5];
    //$profit = $SBOpenTotalProfit[0][0]; $purchasePrice = $SBPurchasePrice[0][0]; //$openTrans = $SBOpenTransactions[0][0]; //$livePrice = $SBLivePrice[0][0];
    //$sellTargetPct = $SBTargetSellPct[0][0];
    //$profitPct = (($profit - $purchasePrice)/$purchasePrice)*100;
    echo "<BR> Test Renew SpreadBet TransID: $profitTotal  | $sellTargetPct | $profitPct | $userID | $sBTransID | $sBRuleID | $purchasePrice | $livePrice";
    if (($profitPct >= $sellTargetPct) AND ($profitPct > -999) and ($profitPct < 999) and (isset($profitPct))){
      //Sell
      LogToSQL("SpreadBetClose","RENEWING SPREADBET TRANS ID:  $sBRuleID : $sBTransID is $profit $profitPct Selling ALL",$userID,1);
      $spreadSellCoins = getSpreadCoinSellData($sBTransID);
      sellSpreadBetCoins($spreadSellCoins);
      //Close all buyback for this SpreadBetTransID
      //CloseAllBuyBack($ID);
      deleteSpreadBetTotalProfit($ID);
      deleteSpreadBetTrackingCoins($ID);
      //New TransactionID
      newSpreadTransactionID($userID,$sBRuleID);
      //Reassign Open to new ID
      //updateSBTransactionsToNew($sBRuleID,$sBTransID);
      //updateSBSellTarget($sBRuleID,$sBTransID);
      //updateSBTotalProfit($sBRuleID,$sBTransID);
      LogToSQL("SpreadBetClose","Profit for $sBRuleID : $sBTransID is $profit ($profitPct %) Selling ALL",$userID,1);
    }
  }
}

function getCoinIDs(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `ID` FROM `CoinStatsView` WHERE `Hr1ChangePctChange` < 4.0 and `Hr1ChangePctChange` > 0.0 and `Hr24ChangePctChange` <= -4.0 and `D7ChangePctChange` <= -4.0 ";
  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID']);
  }
  $conn->close();
  return $tempAry;
}

function writeNewSpreadBetRules($coinID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "Call InsertDynamicRule($coinID);";
    //LogToSQL("updateTransToSpread",$sql,3,1);
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("writeNewSpreadBetRules: ".$sql, 'BuyCoin', 0);
  newLogToSQL("writeNewSpreadBetRules",$sql,3,0,"SQL CALL","CoinID:$coinID");
}

function clearDynamicRules(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "DELETE FROM `SpreadBetCoins` WHERE `SpreadBetRuleID` = 6";
    //LogToSQL("updateTransToSpread",$sql,3,1);
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("clearDynamicRules: ".$sql, 'BuyCoin', 0);
}

function dynamicSpreadBetRules(){
  $coinIDs = getCoinIDs();
  $coinIDsize = newCount($coinIDs);

  if ($coinIDsize >= 3){
    clearDynamicRules();
    for ($e=0; $e<$coinIDsize; $e++){
      writeNewSpreadBetRules($coinIDs[$e][0]);
    }
  }

}

$spreadBet = getSpreadBetAll();
$spreadBetSize = newCount($spreadBet);

for ($i=0;$i<$spreadBetSize;$i++){
  $SBRuleID = $spreadBet[$i][0]; $userID = $spreadBet[$i][37]; $pctOfAllTimeHigh = $spreadBet[$i][35]; $pctofSixMonthHigh = $spreadBet[$i][34];
  $CoinPricePctChange = $spreadBet[$i][13]; $Live1HrChange = $spreadBet[$i][4];
  $hr1BuyEnableSet = $spreadBet[$i][43];
  $hr1BuyDisableSet = $spreadBet[$i][39];
  $hr24andD7StartPrice = $spreadBet[$i][40];
  $month6TotalPrice = $spreadBet[$i][41];
  $allTimTotalPrice = $spreadBet[$i][42]; $userID = $spreadBet[$i][27];
  $progress = getSBProgress($userID,20); $bullBearStatus = $spreadBet[$i][47];
  if (!isset($progress)){
    echo "<BR> Progress not set = 0";
    $pctOfTarget = 0;
  }else{
    $pctOfTarget = $progress[0][6];
    echo "<BR> Progress is set $pctOfTarget";
  }
  $minsToCancel = $spreadBet[$i][44]; $fallsinPrice = $spreadBet[$i][45]; $raisesinPrice = $spreadBet[$i][46];
  //if ($bullBearStatus == 'BEAR'){
  //    $hr1BuyDisableSet = $spreadBet[$i][39] / 2;
  //    $hr1BuyEnableSet = $spreadBet[$i][43] / 2;
  //    $hr24andD7StartPrice =  $spreadBet[$i][40] * 2;
  //    $month6TotalPrice = $spreadBet[$i][41] * 2;
  //    $allTimTotalPrice = $spreadBet[$i][42] * 2;
  //    $minsToCancel = 10;
  //    $raisesinPrice = 20;
  if ($bullBearStatus == 'BULL'){
      $hr1BuyDisableSet = $spreadBet[$i][39] * 2;
      $hr1BuyEnableSet = 10;
      $hr24andD7StartPrice =  10;
      $month6TotalPrice = 0.5;
      $allTimTotalPrice = 0.5;
      $minsToCancel = 10080;
      $raisesinPrice = 2;
      $pctOfTarget = 0;
  }
  //1Hr Price Drop below -5% to activate
  //1Hr Price raise above 2% to deactivate

  $temp1Hr = ($hr1BuyEnableSet*(1-($pctOfTarget/100)));
  ECHO "<BR> 1HrBuy Enabled: $hr1BuyEnableSet*$pctOfTarget | $temp1Hr";
  write1HrEnablePrice($temp1Hr, $SBRuleID);
  if ($Live1HrChange < $temp1Hr){
    toggleSBRule($SBRuleID,1);
  }elseif ($Live1HrChange > ($hr1BuyDisableSet)){
    toggleSBRule($SBRuleID,0);
  }


  //6Month price to change the 24Hr and 7 D %
  //All Time price to change the 24Hr and 7 D %


  $startPrice =  $hr24andD7StartPrice * ($pctOfTarget/100);
  $month6For24 = $month6TotalPrice * ($pctofSixMonthHigh/100);
  $allTimeFor24 = $allTimTotalPrice * ($pctOfAllTimeHigh / 100);
  $hr24Price = $startPrice - $month6For24 - $allTimeFor24;

  //update24Hrand7DPrice($hr24Price,$hr24Price,$SBRuleID);

  $avgPct = ($pctOfAllTimeHigh + $pctofSixMonthHigh)/2;
  Echo "<BR> MINSTOCANCEL: $minsToCancel | $avgPct ";
  $newMinsToCancel = floor($minsToCancel * (1-($avgPct/100)));

  writeMinsToCancel($newMinsToCancel,$SBRuleID);

  $newFallsinPrice = floor($fallsinPrice * ($avgPct/100));
  writeFallsinPrice($newFallsinPrice,$SBRuleID);

  $newRaisesinPrice = floor($raisesinPrice * ($avgPct/100));
  writeRaisesinPrice($newRaisesinPrice, $SBRuleID);
  newLogToSQL("SpreadBetProcess","writeRaisesinPrice($newRaisesinPrice, $SBRuleID);",3,0,"","SBRuleID:$SBRuleID");
}

$progress = getSBProgress(3,20);

renewSpreadBetTransactionID();

//dynamicSpreadBetRules();
?>
</html>
