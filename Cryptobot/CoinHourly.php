<html>
<?php
ini_set('max_execution_time', 300);
require('includes/newConfig.php');
include_once ('/home/stevenj1979/SQLData.php');

//Define("sQLUpdateLog","0");
//Define("SQLProcedureLog","0");

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
  $sql = "SELECT `Cmhp`.`CoinID`,`Cmhp`.`MaxPrice` as `MonthHighPrice`,`Cmhp`.`Month`,`Cmhp`.`Year`,  `Cmmp`.`MinPrice` as `MonthLowPrice`
    FROM `MonthlyMaxPrices` `Cmhp`
  	join `MonthlyMinPrices` `Cmmp` on `Cmmp`.`CoinID` = `Cmhp`.`CoinID` and `Cmmp`.`Month` = `Cmhp`.`Month` and `Cmmp`.`Year` = `Cmhp`.`Year`
    where `Cmhp`.`MaxPrice` <> 0 and `Cmmp`.`MinPrice` <> 0
    and `Cmhp`.`Month` = month(now()) ";
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
    $sql = "SELECT `SpreadBetTransactionID`, 'CoinID', `UserID`, datediff(now(),`OrderDate`) as DaysFromPurchase, `PctProfitSell`, 'ProfitPctBtm','SellRuleID',`SpreadBetRuleID`,`ID` as TransactionID
       FROM `View7_SpreadBetSell` where `Type` = 'SpreadSell' and `Status` = 'Open'";
    print_r($sql);
    $result = $conn->query($sql);
    while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['SpreadBetTransactionID'],$row['CoinID'],$row['UserID'],$row['DaysFromPurchase'],$row['PctProfitSell'],$row['ProfitPctBtm'],$row['SellRuleID']
          ,$row['SpreadBetRuleID'],$row['TransactionID']);}
    $conn->close();
    return $tempAry;
}



function subPctFromOpenSpreadBetTransactions(){
  $openTransSB = getOpenTransactionsSB();
  $openTransSBSize = Count($openTransSB);
  $startNum = 0.01;
  for ($l=0; $l<$openTransSBSize; $l++){
    $days = $openTransSB[$l][3];$spreadBetRuleID = $openTransSB[$l][7]; $userID = $openTransSB[$l][2]; $sellRuleID = $openTransSB[$l][6];
    $transactionID = $openTransSB[$l][8]; $sBTransID = $openTransSB[$l][0]; $pctProfit = $openTransSB[$l][9];
    echo "<BR>subPctFromOpenSpreadBetTransactions DAYS: $days | spreadBetRuleID: $spreadBetRuleID | sellRuleID: $sellRuleID | SBTransID: $sBTransID";
    //if ($days >= 3){
      //if ($days % 2 == 0){
      if ($pctProfit >= 5.0){ $finalNum = 0.75;}
      elseif ($pctProfit < 5.0 AND $pctProfit > 0.25){ $finalNum = $startNum * 2;}
      elseif ($pctProfit <= 0.25){ $finalNum = $startNum; }
          subPctFromProfitSB($sBTransID, $finalNum,$transactionID);
          echo "<BR> subPctFromProfitSB($sBTransID, $finalNum,$transactionID);";
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
  echo "<BR> Checking buyBack Sell Price | $buyBackDataSize";
  for ($f=0; $f<$buyBackDataSize; $f++){
    $transactionID = $buyBackData[$f][0];
    echo "<BR> writeSellPriceToBuyBack($transactionID);";
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
  $sql = "SELECT `APIKey`,`APISecret`,`IDUs`, `KEK` FROM `View12_UserConfig` ";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['APIKey'],$row['APISecret'],$row['IDUs'],$row['KEK']);}
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

function getBounceIDs(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  //$query = "SET time_zone = 'Asia/Dubai';";
  //$result = $conn->query($query);
  $sql = "SELECT `ID` FROM `Coin` WHERE `BuyCoin` = 1 ";
  print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['ID']);}
  $conn->close();
  return $tempAry;
}

function getBouncePricesHistory($coinID,$flag){
  $tempAry = [];
  $conn = getHistorySQL(rand(1,4));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  //$query = "SET time_zone = 'Asia/Dubai';";
  //$result = $conn->query($query);

  $sql_a = "SELECT MAX(`Price`) as TopPrice, MIN(Price) as LowPrice,  (MAX(`Price`) - MIN(Price))/MAX(`Price`)*100 as Difference, `PriceDateTimeID`
          FROM `PriceHistory` WHERE `PriceDateTimeID` in (SELECT `ID` FROM `PriceHistoryDate` WHERE `PriceDateTime` BETWEEN DATE_SUB(NOW(), INTERVAL 2 HOUR) and NOW())
          and `CoinID` = $coinID";
  $sql_b = "SELECT `Price` as TopPrice, 0 as LowPrice,  0 as Difference, `PriceDateTimeID`
          FROM `PriceHistory` WHERE `PriceDateTimeID` in (SELECT `ID` FROM `PriceHistoryDate` WHERE `PriceDateTime` BETWEEN DATE_SUB(NOW(), INTERVAL 2 HOUR) and NOW())
          and `CoinID` = $coinID";
  if ($flag == 1){ $sql =  $sql_a;} else {$sql =  $sql_b;}
  print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['TopPrice'],$row['LowPrice'],$row['Difference'],$row['PriceDateTimeID']);}
  $conn->close();
  return $tempAry;
}

function getBounceCoinIDs(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  //$query = "SET time_zone = 'Asia/Dubai';";
  //$result = $conn->query($query);
  $sql = "SELECT `CoinID`,`TopPrice`,`LowPrice`,`Difference` FROM `BounceIndex` WHERE `Difference` > 2.5";
  print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['CoinID'],$row['TopPrice'],$row['LowPrice'],$row['Difference']);}
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

function writeBouncePrice($topPrice,$lowPrice, $diff, $coinID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call writeBouncePrice($topPrice,$lowPrice,$diff,$coinID);";
  print_r("<BR>".$sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("writeBouncePrice","$sql",3,0,"SQL CALL","CoinID:$coinID");
}

function getBounceIndex(){
  $bounceID = getBounceIDs();
  $bounceIDSize = count($bounceID);
  for ($r=0;$r<$bounceIDSize;$r++){
    $coinID = $bounceID[$r][0];
    $bouncePrice = getBouncePricesHistory($coinID,1);
    $bouncePriceSize = count($bouncePrice);
    for ($t=0;$t<$bouncePriceSize;$t++){
      $topPrice = $bouncePrice[$t][0]; $lowPrice = $bouncePrice[$t][1]; $diff = $bouncePrice[$t][2];
      writeBouncePrice($topPrice,$lowPrice,$diff,$coinID);
    }
  }
}

function testBuyScript($priceAry,$topPrice,$lowPrice,$difference,$coinID){
  $status = 'BuyCoin';
  $nPrice = $lowPrice;
  $buyPrice = 0;
  $nCounterBuy = 0;
  $nCounter = 0;
  $nCounterSell = 0;
  $priceArySize = count($priceAry);
  for ($t=0;$t<$priceArySize;$t++){
    $curPrice = $priceAry[$t][0];
    Echo "<BR> Test: $coinID | $curPrice | $topPrice | $lowPrice | $difference";
    if (($curPrice <= $lowPrice) AND ($status == 'BuyCoin')){
      Echo "<BR> BuyCoin: $coinID | BuyPrice: $curPrice";
      $nCounter++;
      $status = 'SellCoin';
      $buyPrice = $curPrice;
      $minSellPrice = (($curPrice/100)*$difference)+$curPrice;
    }else if (($status == 'SellCoin') AND ($curPrice > $minSellPrice)){
      $nCounterSell++;
      Echo "<BR> SellCoin: $coinID | SellPrice: $curPrice";
    }
  }
  return $nCounterSell;
}

function writeNoOfSells($coinID,$noOfSells){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "UPDATE `BounceIndex` SET `NoOfSells`= $noOfSells WHERE `CoinID` = $coinID; ";
  print_r("<BR>".$sql);
  if ($conn->query($sql) === TRUE) {
      echo "<BR>New record created successfully";
  } else {
      echo "<BR>Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("writeNoOfSells","$sql",3,0,"SQL CALL","CoinID:$coinID");
}

function coinSwapBuyPct($coinSwapID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call SubPctFromCoinSwap($coinSwapID);";
  print_r("<BR>".$sql);
  if ($conn->query($sql) === TRUE) {
      echo "<BR>New record created successfully";
  } else {
      echo "<BR>Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("coinSwapBuyPct","$sql",3,0,"SQL CALL","CoinSwapID:$coinSwapID");
}

function runBounceTestBuy(){
  $bounceIDs = getBounceCoinIDs();
  $bounceIDSize = count($bounceIDs);
  echo "<BR> Running BounceTestBuy: $bounceIDSize";
  for ($s=0;$s<$bounceIDSize;$s++){
    $coinID = $bounceIDs[$s][0]; $topPrice  = $bounceIDs[$s][1];$lowPrice = $bounceIDs[$s][2]; $difference = $bounceIDs[$s][3];
    $bouncePrice = getBouncePricesHistory($coinID,2);
    $bouncePriceSize = count($bouncePrice);
    //for ($u=0;$u<$bouncePriceSize;$u++){
      $noOfSells = testBuyScript($bouncePrice,$topPrice,$lowPrice,$difference,$coinID);
      writeNoOfSells($coinID,$noOfSells);
    //}
  }
}

function getWebSavings(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  //$query = "SET time_zone = 'Asia/Dubai';";
  //$result = $conn->query($query);
  $sql = "SELECT `UserID`
          ,sum(if(`BaseCurrency` = 'USDT',`Amount`*`CoinPrice`, if(`BaseCurrency` = 'BTC',(`Amount`*`CoinPrice`)*getBTCPrice(84), if(`BaseCurrency` = 'ETH',(`Amount`*`CoinPrice`)*getBTCPrice(85), 0)))) as TotalUSD
          ,sum(`LiveCoinPrice` * `Amount`) as LivePrice  FROM `View5_SellCoins` WHERE `Status` = 'Saving'
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

function runMarketPrice(){
  $priceDip = getLiveMarketPrice(1);
  $priceDipSize = count($priceDip);
  $liveCoinPrice = $priceDip[0][17];
  echo "<BR> MarketPrice: writeMarketPrice($liveCoinPrice);";
  writeMarketPrice($liveCoinPrice);
}

function setPriceDipEnabled(){

}

function runHoursforPriceDip(){
  $priceDipRules = getPriceDipRules();
  $priceDipRulesSize = count($priceDipRules);
  echo "<BR> priceDipRulesSize: $priceDipRulesSize";
  for ($y=0; $y<$priceDipRulesSize; $y++){
      $dipHourCounter = 0;
      $dipStartTime = $priceDipRules[$y][9];
      $marketPrices = getMarketPrices($dipStartTime);
      $marketPricesSize = count($marketPrices);
      echo "<BR> marketPricesSize: $marketPricesSize";
      $liveMarketPriceAry = getLiveMarketPrice(1); $ruleID = $priceDipRules[$y][0];
      for ($t=0; $t<$marketPricesSize; $t++){
          $liveMarketPrice = $liveMarketPriceAry[0][17];
          $priceWithTolerance = $marketPrices[$t][0]-(($marketPrices[$t][0]/100)*0.25);
          if ($priceWithTolerance <= $liveMarketPrice){
            $dipHourCounter = $dipHourCounter + 1;
          }else {
            echo "<BR> $priceWithTolerance is less than $liveMarketPrice | EXIT | OriginalPrice: ".$marketPrices[$t][0];
            continue 1;
            //$dipHourCounter = 0;
          }
      }
    echo "<BR> MarketDipHours: writePriceDipHours($ruleID,$dipHourCounter);";
    writePriceDipHours($ruleID,$dipHourCounter);
  }
}

function  getCoinSwapIDs(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}

    //echo "<BR> Flag2: $lowFlag";
    $sql = "SELECT `ID`  FROM `SwapCoins` WHERE `Status` = 'AwaitingSavingsBuy'";
  echo "<BR> $sql";
  //LogToSQL("SQLTest",$sql,3,1);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['ID']);}
  $conn->close();
  return $tempAry;
}

function runReduceCoinSwapPct(){
  $coinSwapIDs = getCoinSwapIDs();
  $coinSwapIDsSize = count($coinSwapIDs);
  for ($u=0; $u<$coinSwapIDsSize; $u++){
    $coinSwapID = $coinSwapIDs[$u][0];
    coinSwapBuyPct($coinSwapID);
  }
}
Echo "<BR> CoinHourly";
Echo "<BR> 1. prepareToMergeSavings();";
prepareToMergeSavings();

Echo "<BR> 2. runMerge($transStats,'Open');";
$transStats = getTransStats();
runMerge($transStats,'Open');

Echo "<BR> 3. getCurrentMonthMinMax();";
$minMaxPrice = getCurrentMonthMinMax();
$minMaxPriceSize = count($minMaxPrice);

for ($i=0; $i<$minMaxPriceSize; $i++){
  writePrice($minMaxPrice[$i][0],$minMaxPrice[$i][1],$minMaxPrice[$i][2],$minMaxPrice[$i][3],$minMaxPrice[$i][4]);
}
Echo "<BR> 4. subPctFromOpenSpreadBetTransactions();";
subPctFromOpenSpreadBetTransactions();
Echo "<BR> 5. subPctFromOpenCoinModeTransactions();";
//subPctFromOpenCoinModeTransactions();
Echo "<BR> 6. addToBuyBackMultiplierHourly();";
addToBuyBackMultiplierHourly();
Echo "<BR> 7. updateSellPricetoBuyBack();";
updateSellPricetoBuyBack();
Echo "<BR> 8. UpdateSpreadBetTotalProfit();";
UpdateSpreadBetTotalProfit();
Echo "<BR> 9. updateSplitBuyAmountforRule();";
updateSplitBuyAmountforRule();
Echo "<BR> 10. deleteBittrexBalances();";
deleteBittrexBalances();
Echo "<BR> 11. updateBittrexBals();";
updateBittrexBals();
Echo "<BR> 12. updateWebSavings();";
updateWebSavings();
Echo "<BR> 13. getBounceIndex();";
getBounceIndex();
Echo "<BR> 14. runBounceTestBuy();";
runBounceTestBuy();
Echo "<BR> 15. runReduceCoinSwapPct();";
runReduceCoinSwapPct();
setPriceDipEnabled();
runMarketPrice();
runHoursforPriceDip();
?>
</html>
