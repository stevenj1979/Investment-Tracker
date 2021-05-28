<html>
<?php
ini_set('max_execution_time', 300);
require('includes/newConfig.php');
include_once ('/home/stevenj1979/SQLData.php');

Define("sQLUpdateLog","0");
Define("SQLProcedureLog","0");

function getTransStats(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  //$query = "SET time_zone = 'Asia/Dubai';";
  //$result = $conn->query($query);
  $sql = "SELECT `Tr`.`CoinID`,`Tr`.`UserID`, count(`Tr`.`CoinID`) as Count, `Usc`.`MergeAllCoinsDaily`, `Tr`.`ID`
FROM `Transaction` `Tr`
join `UserConfig` `Usc` on `Usc`.`UserID` = `Tr`.`UserID`
WHERE `Status` = 'Open'
Group by `CoinID`,`UserID`";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['CoinID'],$row['UserID'],$row['Count'],$row['MergeAllCoinsDaily'],$row['ID']);}
  $conn->close();
  return $tempAry;

}

function UpdateMerge($coinID,$userID,$mode){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "UPDATE `Transaction` SET `ToMerge`= 1 WHERE `CoinID` = $coinID and `UserID` = $userID and `Status` = '$mode'";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("UpdateMerge","$sql",3,0,"SQL","CoinID:$coinID UserID:$userID");

}

function getCurrentMonthMinMax(){
  $conn = getHistorySQL(rand(1,4));
  $sql = "SELECT `Cmhp`.`CoinID`,`Cmhp`.`MonthHighPrice`,`Cmhp`.`Month`,`Cmhp`.`Year`,  `Cmmp`.`MonthLowPrice`
  FROM `CurrentMonthHighPrice` `Cmhp`
	join `CurrentMonthLowPrice` `Cmmp` on `Cmmp`.`CoinID` = `Cmhp`.`CoinID` and `Cmmp`.`Month` = `Cmhp`.`Month` and `Cmmp`.`Year` = `Cmhp`.`Year`
  where `Cmhp`.`MonthHighPrice` <> 0 and `Cmmp`.`MonthLowPrice` <> 0 ";
  echo "<BR>".$sql;
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['CoinID'],$row['MonthHighPrice'],$row['Month'],$row['Year'],$row['MonthLowPrice']);
  }
  $conn->close();
return $tempAry;
}

function getOpenTransactionsSB(){
    $tempAry = [];
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    //$query = "SET time_zone = 'Asia/Dubai';";
    //$result = $conn->query($query);
    $sql = "SELECT `SpreadBetTransactionID`, 'CoinID', `UserID`, datediff(now(),`OrderDate`) as DaysFromPurchase, `PctProfitSell`, 'ProfitPctBtm','SellRuleID',`SpreadBetRuleID`,`ID` as TransactionID FROM `SellCoinsSpreadView`";
    print_r($sql);
    $result = $conn->query($sql);
    while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['SpreadBetTransactionID'],$row['CoinID'],$row['UserID'],$row['DaysFromPurchase'],$row['PctProfitSell'],$row['ProfitPctBtm'],$row['SellRuleID'],$row['SpreadBetRuleID'],$row['TransactionID']);}
    $conn->close();
    return $tempAry;
}



function subPctFromOpenSpreadBetTransactions(){
  $openTransSB = getOpenTransactionsSB();
  $openTransSBSize = Count($openTransSB);

  for ($l=0; $l<$openTransSBSize; $l++){
    $days = $openTransSB[$l][3];$spreadBetRuleID = $openTransSB[$l][7]; $userID = $openTransSB[$l][2]; $sellRuleID = $openTransSB[$l][6];
    $transactionID = $openTransSB[$l][8]; $sBTransID = $openTransSB[$l][0];
    echo "<BR>subPctFromOpenSpreadBetTransactions DAYS: $days | spreadBetRuleID: $spreadBetRuleID | sellRuleID: $sellRuleID | SBTransID: $sBTransID";
    //if ($days >= 3){
      //if ($days % 2 == 0){
          subPctFromProfitSB($sBTransID, 0.01,$transactionID);
          echo "<BR> subPctFromProfitSB($sBTransID, 0.01,$transactionID);";
      //}
    //}
  }

}

function subPctFromProfit($coinID,$userID,$pctToSub,$sellRuleID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call SubFromPct($coinID, $userID, $pctToSub,$sellRuleID);";
  //print_r("<BR>".$sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("subPctFromProfit","$sql",3,0,"SQL CALL","CoinID:$coinID");
}

function getOpenTransactionsLoc(){
    $tempAry = [];
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    //$query = "SET time_zone = 'Asia/Dubai';";
    //$result = $conn->query($query);
    $sql = "SELECT `ID`, `CoinID`, `UserID`, `DaysFromPurchase`, `PctToBuy`, `ProfitPctBtm`,`SellRuleID` FROM `CoinModeRuleOpenTransactions`";
    print_r($sql);
    $result = $conn->query($sql);
    while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['ID'],$row['CoinID'],$row['UserID'],$row['DaysFromPurchase'],$row['PctToBuy'],$row['ProfitPctBtm'],$row['SellRuleID']);}
    $conn->close();
    return $tempAry;
}

function subPctFromOpenCoinModeTransactions(){
  $openTrans = getOpenTransactionsLoc();
  $openTransSize = Count($openTrans);

  for ($l=0; $l<$openTransSize; $l++){
    $days = $openTrans[$l][3];$coinID = $openTrans[$l][1]; $userID = $openTrans[$l][2]; $sellRuleID = $openTrans[$l][6];
    //if ($days >= 3){
    //  if ($days % 2 == 0){
          subPctFromProfit($coinID,$userID, 0.01, $sellRuleID);
    //  }
    //}
  }
}

function writePrice($coinID, $price, $month, $year, $minPrice){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call UpdateMonthlyMinMaxPrice($coinID,$minPrice,$price,$month,$year);";
  print_r("<BR>".$sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("writePrice","$sql",3,0,"SQL CALL","CoinID:$coinID");
}

function addToBuyBackMultiplierHourly(){
  $buyBackCoins = getBuyBackData();
  $buyBackCoinsSize = count($buyBackCoins);
  for ($p=0; $p<$buyBackCoinsSize; $p++){
    $buyBackID = $buyBackCoins[$p][0];
    addToBuyBackMultiplier($buyBackID);
  }
}

function getbuyBack(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  //$query = "SET time_zone = 'Asia/Dubai';";
  //$result = $conn->query($query);
  $sql = "SELECT `TransactionID` FROM `BuyBack` where `SellPrice` = 0.0 or isnull(`SellPrice`)";
  print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['TransactionID']);}
  $conn->close();
  return $tempAry;
}

function writeSellPriceToBuyBack($transactionID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "UPDATE `BuyBack` SET `SellPrice`= (SELECT `SellPrice` FROM `BittrexAction` WHERE `TransactionID` = $transactionID and `Type` in ('Sell','SpreadSell'))  WHERE `TransactionID` = $transactionID";
  print_r("<BR>".$sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("writeSellPriceToBuyBack","$sql",3,0,"SQL","TransactionID:$transactionID");
}

function updateSellPricetoBuyBack(){
  $buyBackData = getbuyBack();
  $buyBackDataSize = count($buyBackData);
  for ($f=0; $f<$buyBackDataSize; $f++){
    $transactionID = $buyBackData[$f][0];
    writeSellPriceToBuyBack($transactionID);
  }
}

function getUserID(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  //$query = "SET time_zone = 'Asia/Dubai';";
  //$result = $conn->query($query);
  $sql = "SELECT `ID` FROM `User`";
  print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['ID']);}
  $conn->close();
  return $tempAry;
}

function updateBuyAmountSplitinSQL($userID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "Call UpdateSplitAmountForRule ($userID);";
  print_r("<BR>".$sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("updateBuyAmountSplitinSQL","$sql",3,0,"SQL CALL","UserID:$userID");
}

function updateSplitBuyAmountforRule(){
  $user = getUserID();
  $userSize = count($user);
  for ($s=0; $s<$userSize; $s++){
    $userID = $user[$s][0];
    updateBuyAmountSplitinSQL($userID);
    Echo "<BR> updateBuyAmountSplitinSQL($userID);";
  }
}

function getUserData(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  //$query = "SET time_zone = 'Asia/Dubai';";
  //$result = $conn->query($query);
  $sql = "SELECT `APIKey`,`APISecret`,`ID`, `KEK` FROM `UserConfigView`";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['APIKey'],$row['APISecret'],$row['ID'],$row['KEK']);}
  $conn->close();
  return $tempAry;
}

Function updateBittrexBals(){
  $userConfig = getUserData();
  $userConfigSize = count($userConfig);
  echo "<BR> Array Size:$userConfigSize ";
  for ($j=0; $j<$userConfigSize; $j++){
    $userID = $userConfig[$j][2]; $apikey = $userConfig[$j][0]; $apisecret = $userConfig[$j][1];
    $KEK = $userConfig[$j][3];
    if (!Empty($KEK)){ $apisecret = Decrypt($KEK,$userConfig[$j][1]);}
    if ($apikey == 'NA'){ continue;}
    $bittrexBals = getDailyBalance($apikey,$apisecret);
    $bittrexBalsSize = count($bittrexBals);
    echo "<BR> Array Size : $bittrexBalsSize";
    foreach ($bittrexBals as $value){
        if ($value["total"] > 0){
          Echo $value["currencySymbol"];
          Echo $value["total"];
          Echo $value["available"];
          echo "<BR>";
          if ($value["currencySymbol"] == 'USDT'){ $base = 'USD';}
          elseif ($value["currencySymbol"] == 'BNT' or $value["currencySymbol"] == 'MANA' or $value["currencySymbol"] == 'MONA' or $value["currencySymbol"] == 'PAY'
          or $value["currencySymbol"] == 'REPV2' or $value["currencySymbol"] == 'STEEM' or $value["currencySymbol"] == 'STRAT' ){$base = 'BTC';}
          else { $base = 'USDT'; }
          $price = bittrexCoinPrice($apikey,$apisecret,$base,$value["currencySymbol"], 3);
          echo "Update BittrexBal: ".$value["currencySymbol"]." : ".$value["total"]." : ".$price;
          updateBittrexBalances($value["currencySymbol"],$value["total"],$price, $userID);
        }
    }
  }
}

function prepareToMergeSavings(){
    $savingsAry = getSavings();
    $savingsArySize = count($savingsAry);
    for ($g=0; $g<$savingsArySize; $g++){
      setSavingsToMerge($savingsAry[$g][0]);
    }
}

function getSavings(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  //$query = "SET time_zone = 'Asia/Dubai';";
  //$result = $conn->query($query);
  $sql = "SELECT `UserID` FROM `UserConfig` where `AutoMergeSavings` = 1 ";
  print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['UserID']);}
  $conn->close();
  return $tempAry;
}

function setSavingsToMerge($userID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "UPDATE `Transaction` SET `ToMerge` = 1 where `UserID` = $userID and `Status` = 'Savings'";
  print_r("<BR>".$sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("setSavingsToMerge","$sql",3,0,"SQL","UserID:$userID");
}

function getWebSavings(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  //$query = "SET time_zone = 'Asia/Dubai';";
  //$result = $conn->query($query);
  $sql = "SELECT `UserID`
          ,sum(if(`BaseCurrency` = 'USDT',`Amount`*`CoinPrice`, if(`BaseCurrency` = 'BTC',(`Amount`*`CoinPrice`)*getBTCPrice(), if(`BaseCurrency` = 'ETH',(`Amount`*`CoinPrice`)*getETHPrice(), 0)))) as TotalUSD
          ,sum(`LiveCoinPrice` * `Amount`) as LivePrice
           FROM `SellCoinSavings`
           group by `UserID`";
  print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['UserID'],$row['TotalUSD'],$row['LivePrice']);}
  $conn->close();
  return $tempAry;
}

function writeWebSavings($userID, $totalUSD, $livePrice){
  $conn = getSQLConn(rand(1,3));
  $profit = $livePrice - $totalUSD;
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call addWebSavings($userID,$totalUSD,$livePrice,$profit);";
  print_r("<BR>".$sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("writeWebSavings","$sql",3,0,"SQL CALL","UserID:$userID");
}

function updateWebSavings(){
  $saving = getWebSavings();
  $savingSize = count($saving);
  for ($p=0; $p<$savingSize; $p++){
    $userID = $saving[$p][0]; $SavingUSD = $saving[$p][1]; $livePrice = $saving[$p][2];
    writeWebSavings($userID, $SavingUSD, $livePrice);
  }
}

function runMerge($transStats,$mode){
  $transStatsSize = count($transStats);
  for ($g=0; $g<$transStatsSize; $g++){
    $coinID = $transStats[$g][0]; $userID = $transStats[$g][1];
    $count = $transStats[$g][2]; $mergeAllCoinsDaily = $transStats[$g][3];
    $ID = $transStats[$g][4];
    if ($count>=2 && $mergeAllCoinsDaily == 1){
      Echo "<BR> $coinID $userID $count $mergeAllCoinsDaily $ID";
      //Update merge for $ID
      UpdateMerge($coinID,$userID);
    }
  }
}

prepareToMergeSavings();

$transStats = getTransStats();
runMerge($transStats,'Open');

$minMaxPrice = getCurrentMonthMinMax();
$minMaxPriceSize = count($minMaxPrice);

for ($i=0; $i<$minMaxPriceSize; $i++){
  writePrice($minMaxPrice[$i][0],$minMaxPrice[$i][1],$minMaxPrice[$i][2],$minMaxPrice[$i][3],$minMaxPrice[$i][4]);
}

subPctFromOpenSpreadBetTransactions();
subPctFromOpenCoinModeTransactions();
addToBuyBackMultiplierHourly();
updateSellPricetoBuyBack();
UpdateSpreadBetTotalProfit();
updateSplitBuyAmountforRule();
updateBittrexBals();

updateWebSavings();
?>
</html>
