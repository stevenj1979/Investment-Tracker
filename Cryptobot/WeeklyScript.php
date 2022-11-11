<html>
<?php
ini_set('max_execution_time', 300);
require('includes/newConfig.php');

include_once ('/home/stevenj1979/SQLData.php');
include_once ('/home/stevenj1979/Encrypt.php');

$apikey=getAPIKey();
$apisecret=getAPISecret();

function clearWeeklyCoinSwaps(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `Transaction` SET `NoOfCoinSwapsThisWeek` = 0 WHERE `Status` = 'Open'";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("clearWeeklyCoinSwaps: ".$sql, 'SellCoin', 0);
}

function getSpreadBetSettings(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `ID`, `SpreadBetRuleID`, `Hr1BuyPrice`, `Hr24BuyPrice`, `D7BuyPrice`, `NextReviewDate`, `PctProfitSell`, `NoOfTransactions`, `LowestPctProfit`, `AvgTimeToSell`
  FROM `SpreadBetSettings`";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['SpreadBetRuleID'],$row['Hr1BuyPrice'],$row['Hr24BuyPrice'],$row['D7BuyPrice'],$row['NextReviewDate'],$row['PctProfitSell'],$row['NoOfTransactions']
      ,$row['LowestPctProfit'],$row['AvgTimeToSell']);
  }
  $conn->close();
  return $tempAry;
}

function updateSpreadBetSettings($Hr24BuyPrice,$D7BuyPrice, $pctProfitSell,$ID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `SpreadBetSettings` SET `Hr24BuyPrice`= $Hr24BuyPrice,`D7BuyPrice`= $D7BuyPrice,`NextReviewDate`= date_add(now(),INTERVAL 1 MONTH),`PctProfitSell`= $pctProfitSell
  WHERE `SpreadBetRuleID` = $ID;";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateSpreadBetSettings: ".$sql, 'SellCoin', 0);
}

function resetSpreadBetSettings(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `SpreadBetSettings` SET `NoOfTransactions`= 0,`LowestPctProfit`= 0,`AvgTimeToSell`= 0, `NextReviewDate`= date_add(`NextReviewDate`, INTERVAL 1 MONTH)";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("resetSpreadBetSettings: ".$sql, 'SellCoin', 0);
}

function spreadBetSettingsUpdate(){
  $spreadBet = getSpreadBetSettings();
  $spreadBetSize = count($spreadBet);
  $resetFlag = False;
  for ($i=0;$i<$spreadBetSize; $i++){
    $ID = $spreadBet[$i][0]; $spreadBetRuleID  = $spreadBet[$i][1]; $nextReviewDate = $spreadBet[$i][5]; $pctProfitSell = $spreadBet[$i][6]; $noOfTrans = $spreadBet[$i][7];
    $lowestPctProfit  = $spreadBet[$i][8]; $avgTimeToSell  = $spreadBet[$i][9]; $Hr24BuyPrice = $spreadBet[$i][3]; $D7BuyPrice = $spreadBet[$i][4];
    if ($noOfTrans == 0){
      //Raise Pct to Buy
      if ($Hr24BuyPrice < -0.5 and $D7BuyPrice < -0.5){
        $Hr24BuyPrice = $Hr24BuyPrice + 0.25;
        $D7BuyPrice = $D7BuyPrice + 0.25;
      }
    }elseif ($nextReviewDate <= date("Y-m-d H:i:s", time())){
        $resetFlag = True;
        if ($lowestPctProfit <= -9){
          //Lower 24 and 7D Pct to Buy
          if ($Hr24BuyPrice > -10 and $D7BuyPrice > -10){
            $Hr24BuyPrice = $Hr24BuyPrice - 0.25;
            $D7BuyPrice = $D7BuyPrice - 0.25;
          }
        }elseif ($avgTimeToSell >= 20200){
          //lower Pct to Sell
          if ($pctProfitSell > 0.5){
            $pctProfitSell = $pctProfitSell - 0.25;
          }
        }
    }
    updateSpreadBetSettings($Hr24BuyPrice,$D7BuyPrice, $pctProfitSell,$ID);
  }

  if ($resetFlag){
    //Reset AvgTime and No of Transactions
    $avgTimeToSell = 0; $lowestPctProfit = 0; $noOfTrans = 0;
    resetSpreadBetSettings();
  }
}

function getOpenBuyBackData(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "SELECT `ID`, `MinsFromAdd`,`UserID` FROM `BuyBackView`";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['MinsFromAdd'],$row['UserID']);
  }
  $conn->close();
  return $tempAry;
}

function closeOpenBuyBack($id,$userID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "call SaveBuyBackKitty($id,$userID);";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("closeBuyBack: ".$sql, 'SellCoin', 0);
}


function clearBuyBack($mins){
  $buyBackAry = getOpenBuyBackData();
  $buyBackArySize = count($buyBackAry);
  for ($b=0; $b<$buyBackArySize; $b++){
    $bBID =$buyBackAry[$b][0]; $minsFromAdd = $buyBackAry[$b][1]; $userID = $buyBackAry[$b][2];
    if ($minsFromAdd >= $mins){
      closeOpenBuyBack($bBID,$userID);
      LogToSQL("WeeklyScript","clearBuyBack($mins) $minsFromAdd",3,1);
    }
  }
}

function setBuySellPriceforProfit(){
  $coinIDs = getCoinIDRuleID();
  $coinIDsSize = count($coinIDs);
  $userIDs = getUserID();
  $userIDsSize = count($userIDs);
  for ($e=0; $e<$coinIDsSize; $e++){
    $CoinID = $coinIDs[$e][1]; $sellRuleID = $coinIDs[$e][0];
    for ($w=0; $w<$userIDsSize; $w++){
        $userID = $userIDs[$w][0];
        buySellProfitEnable($CoinID,$userID,0,0,20,$sellRuleID);
    }
  }
}

function clearSQLLog($days){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "Delete FROM `ActionLog` WHERE datediff(now(),`DateTime`) > $days";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("clearSQLLog: ".$sql, 'SellCoin', 0);

}

function clearPriceDipCoins($days){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "Delete FROM  `PriceDipCoins` WHERE  datediff(now(),`PriceDipDate`) > $days";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("clearPriceDipCoins: ".$sql, 'SellCoin', 0);

}

function ClearCancelledTransactions($sql){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  //$sql = "Delete FROM `ActionLog` WHERE datediff(now(),`DateTime`) > $days";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("ClearCancelledTransactions: ".$sql, 'SellCoin', 0);
}

function getBuyAmountPctOfTotal(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "SELECT `Br`.`ID` , sum(`Ot`.`CoinPrice`*`Ot`.`Amount`) + `Bb`.`Total` as TotalHolding, `Br`.`LimitToBaseCurrency`,`Br`.`BuyAmountPctOfTotal`
          ,getBTCPrice(83) as USDTPrice,getBTCPrice(84) as BTCPrice,getBTCPrice(85) as ETHPrice
            FROM `BuyRules` `Br`
            join `View15_OpenTransactions` `Ot` on `Ot`.`BuyRule` = `Br`.`ID`
            join `BittrexBalances` `Bb`
            WHERE `Br`.`BuyAmountPctOfTotalEnabled` = 1 and `Br`.`LimitToBaseCurrency` = `Ot`.`BaseCurrency`  and  `Bb`.`Symbol` = `Br`.`LimitToBaseCurrency`";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['TotalHolding'],$row['LimitToBaseCurrency'],$row['BuyAmountPctOfTotal'],$row['USDTPrice'],$row['BTCPrice'],$row['ETHPrice']);
  }
  $conn->close();
  return $tempAry;
}

function setBuyAmountPctOfTotal($BuyRuleID,$totalAmount,$baseCurrency,$pct){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $newTotal = ($totalAmount/100)*$pct;
  $sql = "UPDATE `BuyRules` SET `BuyAmountOverride` = $newTotal where `ID` = $BuyRuleID and `BuyAmountOverrideEnabled` = 2";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("clearSQLLog: ".$sql, 'SellCoin', 0);

}

function runBuyAmountPctOfTotal(){

  $IDData = getBuyAmountPctOfTotal();
  $IDDataSize = count($IDData);
  for ($p=0; $p<$IDDataSize; $p++){
    $BuyRuleID = $IDData[$p][0];
    $totalAmount = $IDData[$p][1];
    $baseCurrency = $IDData[$p][2];
    $pct = $IDData[$p][3];
    if ($baseCurrency == 'USDT'){ $multiplier = $IDData[$p][4];}
    elseif ($baseCurrency == 'BTC'){ $multiplier = $IDData[$p][5];}
    elseif ($baseCurrency == 'ETH'){ $multiplier = $IDData[$p][6];}
    setBuyAmountPctOfTotal($BuyRuleID,$totalAmount/$multiplier,$baseCurrency,$pct);
  }
}

function getSavingPctOfTotal(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "SELECT `Ucs`.`UserID`,`Uc`.`SavingPctOfTotal`
            FROM `UserConfig` `Uc`
            join `UserCoinSavings` `Ucs` on `Uc`.`UserID` = `Ucs`.`UserID`
            where `Uc`.`SavingPctOfTotalEnabled` = 1 ";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['UserID'],$row['SavingPctOfTotal']);
  }
  $conn->close();
  return $tempAry;
}

function setSavingPctOfTotal($UserID,$pct){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $newTotal = ($totalAmount/100)*$pct;
  $sql = "call runSetSavingsPctOfTotal($UserID,$pct);";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("clearSQLLog: ".$sql, 'SellCoin', 0);
}

function runSavingPctOfTotal(){

  $IDData = getSavingPctOfTotal();
  $IDDataSize = count($IDData);
  for ($o=0; $o<$IDDataSize; $o++){
    $UserID = $IDData[$o][0];
    //$totalAmount = $IDData[$o][1];
    //$baseCurrency = $IDData[$o][2];
    $pct = $IDData[$o][1];
    setSavingPctOfTotal($UserID,$pct);
  }
}

function getCoinTrackingActions($type){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "SELECT `CoinID`, Avg(`Pct`) as Pct,  Max(`MinsSincePurchase`) as MinsSincePurchase FROM `CoinTrackingActions` WHERE `Type` = '$type'
            GROUP BY `CoinID`";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['CoinID'],$row['Pct'],$row['MinsSincePurchase']);
  }
  $conn->close();
  return $tempAry;
}

Function updateCoinAutoActions($type, $coinID, $pct, $hoursSincePurchase){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $finalMins = ($hoursSincePurchase + 1)*60;
  $sql = "call updateCoinAutoActions('$type',$coinID, $pct, $finalMins)";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateCoinAutoActions: ".$sql, 'SellCoin', 0);
}

Function runCoinAutoActions($coinTrackingActions,$type){
  $coinTrackingActionsSize = count($coinTrackingActions);
  for ($o = 0; $o<$coinTrackingActionsSize; $o++){
    $coinID = $coinTrackingActions[$o][0]; $pct = $coinTrackingActions[$o][1]; $hours = $coinTrackingActions[$o][2];
    updateCoinAutoActions($type,$coinID,$pct,$hours);
  }

}



// MAIN PROGRAMME
clearWeeklyCoinSwaps();
spreadBetSettingsUpdate();
clearBuyBack(5760);
clearSQLLog(90);
//setBuySellPriceforProfit();
ClearCancelledTransactions("DELETE FROM `BittrexAction` WHERE `Status` = 'Cancelled' and `ActionDate` < DATE_SUB(now(), INTERVAL 14 DAY);");
ClearCancelledTransactions("DELETE FROM `Transaction` WHERE `Status` = 'Cancelled' and `OrderDate` < DATE_SUB(now(), INTERVAL 14 DAY);");
ClearCancelledTransactions("DELETE FROM `Transaction` WHERE `Status` = 'Merged' and `OrderDate` < DATE_SUB(now(), INTERVAL 14 DAY);");
ClearCancelledTransactions("DELETE FROM `TrackingCoins` WHERE `Status` = 'Cancelled' and `TrackDate` < DATE_SUB(now(), INTERVAL 14 DAY);");
ClearCancelledTransactions("DELETE FROM `TrackingSellCoins` WHERE `Status` = 'Cancelled' and `TrackDate` < DATE_SUB(now(), INTERVAL 14 DAY);");
runBuyAmountPctOfTotal();
runSavingPctOfTotal();
clearPriceDipCoins(90);
$coinTrackingActions = getCoinTrackingActions('Buy');
runCoinAutoActions($coinTrackingActions,'Buy');
$coinTrackingActions = getCoinTrackingActions('Sell');
runCoinAutoActions($coinTrackingActions,'Sell');
?>
</html>
