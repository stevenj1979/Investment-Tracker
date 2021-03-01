<html>
<?php
ini_set('max_execution_time', 300);
require('includes/newConfig.php');
//set_include_path('/home/stevenj1979/repositories/gdax/src/Configuration.php');
include_once ('/home/stevenj1979/SQLData.php');
include_once ('/home/stevenj1979/Encrypt.php');

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
  ,`Hr1EnableStartPrice`,`MinsToCancel`,`CalculatedFallsInPrice`
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
      , $row['Month6TotalPrice'], $row['AllTimeTotalPrice'], $row['Hr1EnableStartPrice'], $row['MinsToCancel'], $row['CalculatedFallsInPrice']);
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
  logAction("write1HrEnablePrice: ".$sql, 'BuyCoin', 0);
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
  logAction("write1HrEnablePrice: ".$sql, 'BuyCoin', 0);
}

function getSBProgress($userID, $target){
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

$spreadBet = getSpreadBetAll();
$spreadBetSize = count($spreadBet);

for ($i=0;$i<$spreadBetSize;$i++){
  $SBRuleID = $spreadBet[$i][0]; $userID = $spreadBet[$i][37]; $pctOfAllTimeHigh = $spreadBet[$i][35]; $pctofSixMonthHigh = $spreadBet[$i][34];
  $CoinPricePctChange = $spreadBet[$i][13]; $Live1HrChange = $spreadBet[$i][4];
  $hr1BuyEnableSet = $spreadBet[$i][43];
  $hr1BuyDisableSet = $spreadBet[$i][39];
  $hr24andD7StartPrice = $spreadBet[$i][40];
  $month6TotalPrice = $spreadBet[$i][41];
  $allTimTotalPrice = $spreadBet[$i][42]; $userID = $spreadBet[$i][27];
  $progress = getSBProgress($userID,20);
  if (!isset($progress)){
    echo "<BR> Progress not set = 0";
    $pctOfTarget = 0;
  }else{
    $pctOfTarget = $progress[0][6];
    echo "<BR> Progress is set $pctOfTarget";
  }
  $minsToCancel = $spreadBet[$i][44]; $fallsinPrice = $spreadBet[$i][45];
  //1Hr Price Drop below -5% to activate
  //1Hr Price raise above 2% to deactivate

  $temp1Hr = ($hr1BuyEnableSet*(1-($pctOfTarget/100)));
  ECHO "<BR> $hr1BuyEnableSet*$pctOfTarget | $temp1Hr";
  write1HrEnablePrice($temp1Hr, $SBRuleID);
  if ($Live1HrChange < $temp1Hr){
    toggleSBRule($SBRuleID,1);
  }elseif ($Live1HrChange > ($hr1BuyDisableSet)){
    toggleSBRule($SBRuleID,0);
  }


  //6Month price to change the 24Hr and 7 D %
  //All Time price to change the 24Hr and 7 D %
  $month6For24 = ($month6TotalPrice * ($pctofSixMonthHigh/100))*($pctOfTarget/100);
  $allTimeFor24 = ($allTimTotalPrice * ($pctOfAllTimeHigh / 100))*($pctOfTarget/100);
  $hr24Price = $hr24andD7StartPrice - $month6For24 - $allTimeFor24;

  update24Hrand7DPrice($hr24Price,$hr24Price,$SBRuleID);

  $avgPct = ($pctOfAllTimeHigh + $pctofSixMonthHigh)/2;
  $newMinsToCancel = floor($minsToCancel * (1-($avgPct/100)));

  writeMinsToCancel($newMinsToCancel,$SBRuleID);

  $newFallsinPrice = floor($fallsinPrice * (1-($avgPct/100)));
  writeFallsinPrice($newFallsinPrice,$SBRuleID);
}

$progress = getSBProgress(3,20);


?>
</html>
