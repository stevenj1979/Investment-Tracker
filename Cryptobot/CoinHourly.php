<html>
<?php
ini_set('max_execution_time', 600);
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
  $sql = "SELECT `Tr`.`CoinID`,`Tr`.`UserID`, count(`Tr`.`CoinID`) as Count, `Usc`.`MergeAllCoinsDaily`, `Tr`.`ID`,`Cn`.`BaseCurrency`
            FROM `Transaction` `Tr`
            join `UserConfig` `Usc` on `Usc`.`UserID` = `Tr`.`UserID`
            join `Coin` `Cn` on `Cn`.`ID` = `Tr`.`CoinID`
            WHERE `Status` = 'Open'
            Group by `CoinID`,`UserID`,`Cn`.`BaseCurrency`";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['CoinID'],$row['UserID'],$row['Count'],$row['MergeAllCoinsDaily'],$row['ID'],$row['BaseCurrency']);}
  $conn->close();
  return $tempAry;

}

function minAmountToSaving(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "Update `Transaction` `Tr`
            join `Coin` `Cn` on `Cn`.`ID` = `Tr`.`CoinID`
            SET `Tr`.`Status` = 'Saving'
            WHERE `Status` = 'Open' and `Tr`.`Amount` < `Cn`.`MinTradeSize` and `Tr`.`ToMerge` = 0";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("minAmountToSaving","$sql",3,0,"SQL","");

}

function closeTransactionswithNoAmountinBittrex(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "Update `Transaction` `Tr`
            join `Coin` `Cn` on `Tr`.`CoinID` = `Cn`.`ID`
            left join `BittrexBalances` `Bb` on  `Cn`.`BaseCurrency` = `Bb`.`BaseCurrency`  and `Cn`.`Symbol` = `Bb`.`Symbol`
            SET `Tr`.`Status` = 'Closed'
            Where `Tr`.`Status` = 'Open'  and isNull(`Bb`.`ID`) AND `OrderDate` > TIME(DATE_SUB(NOW(), INTERVAL 1 HOUR)) ";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("closeTransactionswithNoAmountinBittrex","$sql",3,0,"SQL","CoinID:$coinID UserID:$userID");

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
    $sql = "SELECT `SpreadBetTransactionID`, 'CoinID', `UserID`, datediff(now(),`OrderDate`) as DaysFromPurchase, `PctProfitSell`, 'ProfitPctBtm','SellRuleID',`SpreadBetRuleID`,`IDTr` as TransactionID,`CoinPrice`,`Amount`
    ,`LiveCoinPrice`
       FROM `View7_SpreadBetSell` where `Type` = 'SpreadSell' and `Status` = 'Open'";
    print_r($sql);
    $result = $conn->query($sql);
    while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['SpreadBetTransactionID'],$row['CoinID'],$row['UserID'],$row['DaysFromPurchase'],$row['PctProfitSell'],$row['ProfitPctBtm'],$row['SellRuleID']
          ,$row['SpreadBetRuleID'],$row['TransactionID'],$row['CoinPrice'],$row['Amount'],$row['LiveCoinPrice']);}
    $conn->close();
    return $tempAry;
}

function getOpenUserIDandBaseCurrency(){
    $tempAry = [];
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    //$query = "SET time_zone = 'Asia/Dubai';";
    //$result = $conn->query($query);
    $sql = "SELECT `Tr`.`ID`
              FROM `User` `Tr`";
    print_r($sql);
    $result = $conn->query($sql);
    while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['ID']);}
    $conn->close();
    return $tempAry;
}

function UpdateHolding($userID,$baseCurrency){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call autoUpdateHolding('$baseCurrency',$userID);";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("UpdateHolding","$sql",3,0,"SQL","UserID:$userID");

}

function runAutoUpdateHolding(){
  $baseAry = Array('USDT','BTC','ETH');
  $openIDs = getOpenUserIDandBaseCurrency();
  $openIDsSize = count($openIDs);
  for ($t=0;$t<$openIDsSize;$t++){
    $userID = $openIDs[$t][0];
    for ($y=0;$y<count($baseAry);$y++){
      $baseCurrency = $baseAry[$y];
      Echo "UpdateHolding($userID,$baseCurrency);";
      UpdateHolding($userID,$baseCurrency);
    }
  }
}


function subPctFromOpenSpreadBetTransactions(){
  $openTransSB = getOpenTransactionsSB();
  $openTransSBSize = Count($openTransSB);
  $startNum = 0.01;
  for ($l=0; $l<$openTransSBSize; $l++){
    $days = $openTransSB[$l][3];$spreadBetRuleID = $openTransSB[$l][7]; $userID = $openTransSB[$l][2]; $sellRuleID = $openTransSB[$l][6];
    $transactionID = $openTransSB[$l][8]; $sBTransID = $openTransSB[$l][0];
    $coinPrice = $openTransSB[$l][9];
    $amount = $openTransSB[$l][10];
    $liveCoinPrice = $openTransSB[$l][11];
    $purchasePrice = $coinPrice * $amount;
    $livePrice = $liveCoinPrice * $amount;
    $fee = ($livePrice/100)*0.28;
    $profit =  $livePrice - $purchasePrice - $fee;
    $pctProfit = ($profit/$purchasePrice)*100;
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
    $buyBackID = $buyBackCoins[$p][0]; $addNum = $buyBackCoins[$p][46];  $buyBackPct = $buyBackCoins[$p][22]; $multiplier = $buyBackCoins[$p][47];
    $bbAutoPctEnabled = $buyBackCoins[$p][56]; $caaOffset = $buyBackCoins[$p][57];
    $bbBase = -4.5;
    if ($bbAutoPctEnabled == 1) {
      if(!is_null($caaOffset)){
        $bbBase = $bbBase - $caaOffset;
      }

    }
    addToBuyBackMultiplier($buyBackID,$addNum,$buyBackPct,$multiplier, $bbBase);
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

Function updateBittrexBals($userConfig){

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
          Echo $value["currencySymbol"]." | ".$value["total"]." | ".$value["available"]."<BR>";
          $openBaseCurr = getOpenBaseCurrency($value["currencySymbol"]);
          $openBaseCurrSize = count($openBaseCurr);
          $runningTotal = 0;
          if($openBaseCurrSize == 0 AND $value["total"] > 0){ updateBittrexBalances($value["currencySymbol"],$value["total"],0, $userID,'', 0);continue;}
          if ($value["currencySymbol"] == 'USDT'){ $base = 'USD';updateBittrexBalances($value["currencySymbol"],$value["total"],0, $userID,$base, 83);continue;}
          elseif($value["currencySymbol"] == 'BTC'){ $base = 'USDT';updateBittrexBalances($value["currencySymbol"],$value["total"],0, $userID,$base, 84);continue;}
          elseif($value["currencySymbol"] == 'ETH'){ $base = 'USDT';updateBittrexBalances($value["currencySymbol"],$value["total"],0, $userID,$base, 85);continue;}
          for ($x=0;$x<$openBaseCurrSize;$x++){
            $base = $openBaseCurr[$x][0]; $coinID = $openBaseCurr[$x][1]; $amount = $openBaseCurr[$x][2];
            if ($x==0){ $runningTotal = $value["total"];}
            $priceAry = bittrexCoinPriceNew($base,$value["currencySymbol"]);
            $price = $priceAry[0][0];
            //echo "Update BittrexBal: ".$value["currencySymbol"]." : ".$value["total"]." : $price | $x | $amount | $runningTotal";
            if (!isset($price)){ $price = 0;}
            if (!isset($base)){ $base = '';}
            if (!isset($coinID)){ $coinID = 0;}
            if (!isset($amount) AND ($openBaseCurrSize <= 1)){ $amount = $value["total"];}
            if ($runningTotal >= $amount){
                updateBittrexBalances($value["currencySymbol"],$amount,$price, $userID,$base, $coinID);
            }
            $runningTotal = $runningTotal - $amount;

          }
        }else{
          Echo $value["currencySymbol"]." | Zero Val";
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

function runCoinPriceDipPrices(){
  //Get Coins + Price
  $coin = getTrackingCoins("WHERE `BuyCoin` = 1 ORDER BY `Symbol` ASC","FROM `View1_BuyCoins` ");
  $coinSize = count($coin);
  for ($t=0;$t<$coinSize; $t++){
      $coinID = $coin[$t][0]; $price = $coin[$t][17];
      writeCoinPriceDipPrice($coinID,$price);
  }
}


function runHoursforPriceDip(){
  $priceDipRules = getPriceDipRules();
  $priceDipRulesSize = count($priceDipRules);
  $inc = 5;
  echo "<BR>***** runHoursforPriceDip ***** priceDipRulesSize: $priceDipRulesSize";
  $liveMarketPriceAry = getLiveMarketPrice(1);
  $dipHourCounter = 0;
  for ($y=0; $y<$priceDipRulesSize; $y++){
      $dipStartTime = $priceDipRules[$y][9]; $priceDipTolerance = $priceDipRules[$y][11];
      $marketPrices = getMarketPrices($dipStartTime);
      $marketPricesSize = count($marketPrices);
      $ruleID = $priceDipRules[$y][0];
      echo "<BR> marketPricesSize: $marketPricesSize | Checking Rule: $ruleID";
      for ($t=0; $t<$marketPricesSize; $t++){
          $liveMarketPrice = $liveMarketPriceAry[0][17];
          $priceWithToleranceBtm = $liveMarketPrice-(($liveMarketPrice/100)*$priceDipTolerance);
          $priceWithToleranceTop = $liveMarketPrice+(($liveMarketPrice/100)*$priceDipTolerance);
          if ($marketPrices[$t][0] >= $priceWithToleranceBtm AND $marketPrices[$t][0] <= $priceWithToleranceTop){
            $dipHourCounter = $dipHourCounter + $inc;
            echo "<BR> Live Price is: $liveMarketPrice | Live with Tol: $priceWithToleranceBtm : $priceWithToleranceTop | Prev Price: ".$marketPrices[$t][0]." | Counter: $dipHourCounter";
          }else {
            echo "<BR> $priceWithToleranceBtm is less than $liveMarketPrice |$priceWithToleranceTop is Greater than $liveMarketPrice | EXIT | OriginalPrice: ".$marketPrices[$t][0];
            writePriceDipHours($ruleID,floor($dipHourCounter/60));
            $dipHourCounter = 0;
            continue 2;
            //$dipHourCounter = 0;
          }
      }
      echo "<BR> Cycle Finished: $dipHourCounter";
      writePriceDipHours($ruleID,floor($dipHourCounter/60));
      $dipHourCounter = 0;
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

function runSQLAvgPrice($coinID, $highLow){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "Call AddAvgCoinPrice($coinID,'$highLow');";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("clearSQLLog: ".$sql, 'SellCoin', 0);

}

function getMultiSellRulesData(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}

    //echo "<BR> Flag2: $lowFlag";
    $sql = "SELECT `Tr`.`ID`,`Tr`.`MultiSellRuleEnabled`,`Tr`.`MultiSellRuleTemplateID`,  `Mti`.`MultiRuleStr`,`Tr`.`UserID`
              FROM `Transaction` `Tr`
              Join `MultiSellRuleTemplate` `Mti` on `Mti`.`ID` = `Tr`.`MultiSellRuleTemplateID`
              WHERE  `Tr`.`Status` = 'Open' and `Tr`.`Type` in  ('Sell','SpreadSell') and `Tr`.`MultiSellRuleEnabled` = 1";
  echo "<BR> $sql";
  //LogToSQL("SQLTest",$sql,3,1);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['ID'],$row['MultiSellRuleEnabled'],$row['MultiSellRuleTemplateID'],$row['MultiRuleStr'],$row['UserID']);}
  $conn->close();
  return $tempAry;
}

function UpdateMultiSellRuleConfig($currentSellRule,$userID,$transactionID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "call updateMultiSellRuleConfig($currentSellRule,$userID,$transactionID);";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("UpdateMultiSellRuleConfig: ".$sql, 'SellCoin', 0);
}

function runMultiSellRulesConfig(){
  $multiSellRules = getMultiSellRulesData();
  $multiSellRulesSize = count($multiSellRules);
  for ($p=0; $p<$multiSellRulesSize; $p++){
    $sellRuleStr = $multiSellRules[$p][3];
    Echo "<BR> Sell Rule String: $sellRuleStr";
    $sellRuleAry = explode(",",$sellRuleStr);
    $sellRuleArySize = count($sellRuleAry);
    for ($o=0; $o<$sellRuleArySize; $o++){
      $currentSellRule =  $sellRuleAry[$o]; $userID = $multiSellRules[$p][4]; $transactionID = $multiSellRules[$p][0];
      Echo "<BR>UpdateMultiSellRuleConfig($currentSellRule,$userID,$transactionID); ";
      UpdateMultiSellRuleConfig($currentSellRule,$userID,$transactionID);
    }

  }
}

function getLiveCoinTable(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}

    //echo "<BR> Flag2: $lowFlag";
    $sql = "SELECT `ID`, `Symbol`, `Name`, `BaseCurrency`, `BuyCoin`, `CMCID`, ifNull(`SecondstoUpdate`,0) as SecondstoUpdate, `Image`, `MinTradeSize`, `CoinPrecision`, ifNull(`DoNotBuy`,0) as DoNotBuy
    FROM `Coin` Where `BuyCoin` = 1";
  echo "<BR> $sql";
  //LogToSQL("SQLTest",$sql,3,1);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['ID'],$row['Symbol'],$row['Name'],$row['BaseCurrency'],$row['BuyCoin'],$row['CMCID'],$row['SecondstoUpdate'],$row['Image']
    ,$row['MinTradeSize'],$row['CoinPrecision'],$row['DoNotBuy']);}
  $conn->close();
  return $tempAry;
}

function writeCoinTableToHistory($coinAry){
  $conn = getHistorySQL(rand(1,4));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $ID = $coinAry[0]; $Symbol = $coinAry[1]; $Name = $coinAry[2]; $BaseCurrency = $coinAry[3]; $BuyCoin = $coinAry[4]; $CMCID = $coinAry[5];
  $SecondstoUpdate = $coinAry[6]; $Image = $coinAry[7]; $MinTradeSize = $coinAry[8]; $CoinPrecision = $coinAry[9]; $DoNotBuy = $coinAry[10];

  $sql = "call writeCoinTableToHistory($ID, '$Symbol', '$Name', '$BaseCurrency', $BuyCoin,$CMCID,$SecondstoUpdate, '$Image',$MinTradeSize,$CoinPrecision,$DoNotBuy);";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("writeCoinTableToHistory: ".$sql, 'SQL Call', 0);
  newLogToSQL("writeCoinTableToHistory","$sql",3,0,"SQL CALL","ID:None");
}

function copyCoinTableToHistory(){
  $liveCoinTblAry = getLiveCoinTable();
  $liveCoinTblArySize = Count($liveCoinTblAry);
  for ($y=0; $y<$liveCoinTblArySize; $y++){
    writeCoinTableToHistory($liveCoinTblAry[$y]);
  }
}

function runUpdateAvgPrices(){
  $coin = getCoinIDs();
  $coinSize = count($coin);

  for ($v=0; $v<$coinSize; $v++){
    $coinID = $coin[$v][0];
    runSQLAvgPrice($coinID,'High');
    echo "<BR> runSQLAvgPrice($coinID,'High');";
    runSQLAvgPrice($coinID,'Low');
    echo "<BR> runSQLAvgPrice($coinID,'Low');";
  }
}

function runAutoActionBuy($autoActionCoins){
  $autoActionCoinsSize = count($autoActionCoins);
  for ($p=0; $p<$autoActionCoinsSize; $p++){
     $profitPct = $autoActionCoins[$p][58]; $hoursSincePurchase = $autoActionCoins[$p][66]; $coinID = $autoActionCoins[$p][2]; $transactionID  = $autoActionCoins[$p][0];
     writeAutoActionBuy($profitPct,$hoursSincePurchase,$coinID,$transactionID,'Buy');
  }
}

function runAutoActionSell($autoActionCoins){
  $autoActionCoinsSize = count($autoActionCoins);
  for ($p=0; $p<$autoActionCoinsSize; $p++){
     $profitPct = $autoActionCoins[$p][58]; $hoursSincePurchase = $autoActionCoins[$p][71]; $coinID = $autoActionCoins[$p][2]; $transactionID  = $autoActionCoins[$p][0];
     writeAutoActionBuy($profitPct,$hoursSincePurchase,$coinID,$transactionID,'Sell');
  }
}

function writeAutoActionBuy($profitPct,$hoursSincePurchase,$coinID,$transactionID,$type){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "call writeAutoActionBuy($transactionID,$coinID,'$type',$profitPct,$hoursSincePurchase);";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("writeAutoActionBuy",$sql,3,0,"SQL","TransID:$transactionID");
  logAction("writeAutoActionBuy: ".$sql, 'SQL_UPDATE', 0);
}

function getSavingsDataAgain(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}

    //echo "<BR> Flag2: $lowFlag";
    $sql = "SELECT `SavingID`, `ID`, `UserID`, `MergeSavingWithPurchase`,`FixSellRule`, `BuyRule`, `FixSellRule`,`MultiSellRuleEnabled`, `MultiSellRuleTemplateID` ,`Type`
    ,`SpreadBetRuleID`,`SpreadBetTransactionID`
    FROM `View24_SavingsReadyToOpenAndMerge` ";
  echo "<BR> $sql";
  //LogToSQL("SQLTest",$sql,3,1);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['SavingID'],$row['ID'],$row['UserID'],$row['MergeSavingWithPurchase'],$row['FixSellRule'],$row['BuyRule'],$row['SellRule'],$row['MultiSellRuleEnabled']
          ,$row['MultiSellRuleTemplateID'],$row['Type'],$row['SpreadBetRuleID'],$row['SpreadBetTransactionID']);}
  $conn->close();
  return $tempAry;
}

function updateSavingsMerge($savingID, $transID,$fixSellRule,$buyRule,$sellRule,$multiSellEnabled,$multiSellID,$nFile,$nFunc,$logSettingAry,$type,$sbRuleID,$sbTransID){
  $UserID = 3;
  $logSettings = explode(",",$logSettingAry);
  //echo "<BR> LSA: $logSettingAry";
  $enabled = $logSettings[0]; $mode = $logSettings[1]; $days = $logSettings[2];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `Transaction` SET `Status` = 'Open',`ToMerge` = 1,`FixSellRule` = $fixSellRule, `BuyRule` = $buyRule,`SellRule` = $fixSellRule, `MultiSellRuleEnabled` = $multiSellEnabled,`MultiSellRuleTemplateID` = $multiSellID
            , `Type` = '$type', `SpreadBetRuleID` = $sbRuleID, `SpreadBetTransactionID` = $sbTransID WHERE `ID` = $savingID;";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();


  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql2 = "UPDATE `Transaction` SET `ToMerge` = 1 WHERE `ID` = $transID;";

  print_r($sql2);
  if ($conn->query($sql2) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql2 . "<br>" . $conn->error;
  }
  $conn->close();

  // Check connection
  //newLogToSQL("updateSavingsMerge",$sql." ".$sql2,3,0,"SQL","TransID:$transID");
  //logAction("updateSavingsMerge: ".$sql." ".$sql2, 'SQL_UPDATE', 0);
  SuperLog($nFile,"updateSavingsMerge",$nFunc,"SQL1","TransID:$transID",$logVariSettingAry);
}

function runSavingsMerge(){
  $savingsAry = getSavingsDataAgain();
  $savingsArySize = count($savingsAry);
  for ($g=0;$g<$savingsArySize;$g++){
    $savingID = $savingsAry[$g][0]; $transID = $savingsAry[$g][1];
    $fixSellRule = $savingsAry[$g][4];$buyRule = $savingsAry[$g][5]; $sellRule = $savingsAry[$g][4]; $multiSellEnabled = $savingsAry[$g][7]; $multiSellID = $savingsAry[$g][8];
    $type = $savingsAry[$g][9]; $sbRuleID = $savingsAry[$g][10];$sbTransID = $savingsAry[$g][11];
    updateSavingsMerge($savingID, $transID,$fixSellRule,$buyRule,$sellRule,$multiSellEnabled,$multiSellID,"CoinHourly","updateSavingsMerge","1,2,3",$type,$sbRuleID,$sbTransID);
  }
}



function getMultiBuyIDs(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}

    //echo "<BR> Flag2: $lowFlag";
    $sql = "SELECT `ID`,`MultiSellRuleTemplateID`,`UserID` FROM `Transaction` WHERE `MultiSellRuleEnabled` = 1 and `Status` = 'Open' ";
  echo "<BR> $sql";
  //LogToSQL("SQLTest",$sql,3,1);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['ID'],$row['MultiSellRuleTemplateID'],$row['UserID']);}
  $conn->close();
}

function runMultiBuy(){
  $multiBuyAry = getMultiBuyIDs();
  if (isset($multiBuyAry)){
    $multiBuyArySize = count($multiBuyAry);
  }else{$multiBuyArySize = 0;}

  for($d=0;$d<$multiBuyArySize;$d++){
    $multiSellRuleTemplateID = $multiBuyAry[$d][1]; $transactionID = $multiBuyAry[$d][0]; $userID = $multiBuyAry[$d][2];
    $ruleStr = getMultiSellRulesTemplate($multiSellRuleTemplateID);
    $str_arr = explode (",", $ruleStr);
    $str_arrSize = count($str_arr);
    for ($t=0; $t<$str_arrSize; $t++){
      $sellRuleIDFromTemplate = $str_arr[$t];
      writeMultiRule($sellRuleIDFromTemplate,$transactionID,$userID);
    }
    //writeMultiRuleTemplateID($transactionID,$multiSellRuleTemplateID);
  }
}

function disableEnableRules($userID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "call DisableRuleOnLowBalance($userID)";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("disableEnableRules",$sql,3,0,"SQL","UserID:$userID");
  logAction("disableEnableRules: ".$sql, 'SQL_UPDATE', 0);
}

function runDisableOnLowBalance($userConfig){
  $userConfigSize = count($userConfig);
  for ($b = 0; $b<$userConfigSize; $b++){
    $userID = $userConfig[$b][2];
    disableEnableRules($userID);
  }
}

$userConfig = getUserData();
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
//updateSellPricetoBuyBack();
Echo "<BR> 8. UpdateSpreadBetTotalProfit();";
UpdateSpreadBetTotalProfit();
Echo "<BR> 9. updateSplitBuyAmountforRule();";
updateSplitBuyAmountforRule();
Echo "<BR> 10. deleteBittrexBalances();";
deleteBittrexBalances();
Echo "<BR> 11. updateBittrexBals();";
updateBittrexBals($userConfig);
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
runHoursforPriceDip(); //Market
runCoinPriceDipPrices();

runUpdateAvgPrices();
runMultiSellRulesConfig();
copyCoinTableToHistory();
runClosedCalculatedSellPct();
$autoActionCoins = getAutoActionCoins('Sell','Open',168);
runAutoActionBuy($autoActionCoins);
$autoActionCoins = getAutoActionCoins('Sell','Sold',168);
runAutoActionSell($autoActionCoins);
runSavingsMerge();

runMultiBuy();
minAmountToSaving();
//closeTransactionswithNoAmountinBittrex();
runAutoUpdateHolding();
runDisableOnLowBalance($userConfig);
?>
</html>
