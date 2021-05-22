<html>
<?php
ini_set('max_execution_time', 300);
require('includes/newConfig.php');
include_once ('/home/stevenj1979/SQLData.php');
Define("sQLUpdateLog","1");
Define("SQLProcedureLog","1");

function getUserConfig(){
    $tempAry = [];
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
     $sql = "SELECT `ID`,`APIKey`,`APISecret`,datediff(`ExpiryDate`, CURDATE()) as DaysRemaining, `Email`, `UserName`, `Active` FROM `UserConfigView`";
    $result = $conn->query($sql);
    while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['ID'],$row['APIKey'],$row['APISecret'],$row['DaysRemaining'],$row['Email'],$row['UserName'],$row['Active']);}
    $conn->close();
    return $tempAry;
}

function DeleteHistory($hours){
  $conn = getHistorySQL(rand(1,4));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  //$date = date('Y-m-d H:i', time());
  $sql = "call NewDeleteHistory($hours);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function userHistory($userID){
    $tempAry = [];
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $query = "SET time_zone = 'Asia/Dubai';";
    $result = $conn->query($query);
    $sql = "SELECT `UserID` AS `UserID`
              ,Sum(Case When `BaseCurrency` = 'BTC'
                Then `LivePrice` Else 0 End) as LiveBTC
              ,Sum(Case When `BaseCurrency` = 'USDT'
                Then `LivePrice` Else 0 End) as LiveUSDT
              ,Sum(Case When `BaseCurrency` = 'ETH'
                Then `LivePrice` Else 0 End) as LiveETH
              FROM `UnrealisedProfit` where `UserID` = $userID
              group by `UserID`;";
    print_r($sql);
    $result = $conn->query($sql);
    while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['UserID'],$row['LiveBTC'],$row['LiveUSDT'],$row['LiveETH']);}
    $conn->close();
    return $tempAry;
}





function updateUserProfit($userID,$liveBTC,$BittrexBTC,$liveUSDT,$BittrexUSDT,$liveETH,$BittrexETH){
    //set time
    setTimeZone();
    $date = date("Y-m-d H:i", time());
    if (empty($liveBTC)){$liveBTC = 0;}
    if (empty($BittrexBTC)){$BittrexBTC = 0;}
    if (empty($liveUSDT)){$liveUSDT = 0;}
    if (empty($BittrexUSDT)){$BittrexUSDT = 0;}
    if (empty($liveETH)){$liveETH = 0;}
    if (empty($BittrexETH)){$BittrexETH = 0;}
    //if (empty($actionDate)){$actionDate = $date;}
    //echo "<br> TEST1: ".isset($BTCfromCoins);
    //echo "<br> TEST2: ".empty($BTCfromCoins);
    //echo "<br> TEST3: ".isnull($BTCfromCoins);
    $tempAry = [];
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $sql = "call UpdateUserProfit($userID,$liveBTC, $BittrexBTC, $liveUSDT, $BittrexUSDT, $liveETH,$BittrexETH,'$date');";
    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}

function coinHistory($hours){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $sql = "call deleteHistory($hours)";
    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}

function updateSQLactive($userID){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $sql = "UPDATE `User` SET `Active` = 'No' WHERE `ID` = $userID";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("updateSQLactive",$sql,3,1,sQLUpdateLog,"SQL","UserID:$userID");
}

function sendRenewEmail($to, $subject, $user, $from, $daysRemaining){
    $body = "Dear ".$user.", <BR/>";
    $body .= "You have $daysRemaining days left before your subscription expires<BR/>";
    $body .= "Please renew on this link http://www.investment-tracker.net/Investment-Tracker/Cryptobot/1/Subscribe.php<BR/>";
    $body .= "Kind Regards\nCryptoBot.";
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    //$headers .= 'From: Alerts <Alerts@Investment-Tracker.net>' . "\r\n";
    $headers .= "From:".$from."\r\n";
    $headers .= "To:".$to."\r\n";
    mail($to, $subject, wordwrap($body,70),$headers);
}

function getSequenceData(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  //$query = "SET time_zone = 'Asia/Dubai';";
  //$result = $conn->query($query);
  $sql = "SELECT `UserID`,`ID` as `SellRuleID`,`CoinOrder`
    FROM `SellRules`
    where `SellCoin` = 1 and `ProfitPctBtm` <> 0 and `CoinModeRule` = 0
    order by `UserID`,`ProfitPctBtm` desc ";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['UserID'],$row['SellRuleID'],$row['CoinOrder']);}
  $conn->close();
  return $tempAry;
}

function getTransData(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  //$query = "SET time_zone = 'Asia/Dubai';";
  //$result = $conn->query($query);
  $sql = "SELECT `ID`,`UserID`, `OrderNo`,`FixSellRule`, mod(datediff(curdate(),`OrderDate`),7) as DaysOver FROM `TransactionsView` WHERE `Status` = 'Open' and datediff(curdate(),`OrderDate`) > 1 ";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['ID'],$row['UserID'],$row['OrderNo'],$row['FixSellRule'],$row['DaysOver']);}
  $conn->close();
  return $tempAry;
}



function updateFixSellRule($newFixRule, $transactionID){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $sql = "UPDATE `Transaction` SET `FixSellRule`= $newFixRule WHERE `ID` = $transactionID";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("updateFixSellRule",$sql,3,1,sQLUpdateLog,"SQL","TransactionID:$transactionID");
}



function checkSellSequence(){
  //get sell sequesnce info
  //get all open trans
  $trans = getTransData();
  $transcount = count($trans);
  $nFlag = False;
  $sequence = getSequenceData();
  $sequenceCount = count($sequence);
  for($x = 0; $x < $transcount; $x++) {
    if ($trans[$x][4] == 0){  //check if the buy Tran Mod 7 days = 0;
      $userID = $trans[$x][1]; $fixSellRule = $trans[$x][3]; $transactionID = $trans[$x][0];
      //get the current sequence number
      //update the fixed sell ID
      for($y = 0; $y < $sequenceCount; $y++) {
        if ($sequence[$y][0] == $userID and $sequence[$y][1] == $fixSellRule){
          //$nFlag = True;
          for ($z =$y+1; $z<$sequenceCount; $z ++ ){
              if ( $sequence[$z][0] == $userID){
                updateFixSellRule($sequence[$z][1],$transactionID);
                logToSQL("Sell Coin Sequence", "Change Fixed Sell Rule from ".$sequence[$y][1]." to ".$sequence[$z][1]." TransactionID: $transactionID", $userID,1);
              }
          }

        }
      }
    }
  }
}

$subject = "Subscription Expiring!"; $from = "CryptoBot <subscription@investment-tracker.net>";
//Get UserID + API Keys
$conf = getUserConfig();
$confSize = count($conf);
for($x = 0; $x < $confSize; $x++) {
  $daysRemaining = $conf[$x][3]; $active = $conf[$x][6]; $userID = $conf[$x][0]; $email = $conf[$x][4]; $userName = $conf[$x][5];
  //$bittrexBalBTC = bittrexbalance($conf[$x][1], $conf[$x][2], 'BTC' );
  //$bittrexBalUSDT = bittrexbalance($conf[$x][1], $conf[$x][2], 'USDT' );
  //$bittrexBalETH = bittrexbalance($conf[$x][1], $conf[$x][2], 'ETH' );
  //$btcToday = userHistory($conf[$x][0]);
  //updateUserProfit($conf[$x][0],$btcToday[0][1],$bittrexBalBTC,$btcToday[0][2],$bittrexBalUSDT,$btcToday[0][3],$bittrexBalETH);
  //$daysRemaining = $userDates[$x][5]; $active = $userDates[$x][3]; $userID = $userDates[$x][0]; $email = $userDates[$x][1]; $userName = $userDates[$x][4];

  if ($active == 'Yes' && $daysRemaining <= 0) {
    echo "Success 1 _ ".$daysRemaining;
    //Update SQL
    updateSQLactive($userID);
  } elseif ($active == 'Yes' && $daysRemaining == 7) {
    echo "Success 2 _ ".$daysRemaining;
    sendRenewEmail($email, $subject, $userName, $from, $daysRemaining);
  } elseif ($active == 'Yes' && $daysRemaining == 3) {
    echo "Success 3 _ ".$daysRemaining;
    sendRenewEmail($email, $subject, $userName, $from, $daysRemaining);
  } elseif ($active == 'Yes' && $daysRemaining == 1) {
    echo "Success 4 _ ".$daysRemaining;
    sendRenewEmail($email, $subject, $userName, $from, $daysRemaining);
  }
}

function getCurrentMonthMinMax(){
  $conn = getHistorySQL(rand(1,4));
  $sql = "SELECT `Cmhp`.`CoinID`,`Cmhp`.`MonthHighPrice`,`Cmhp`.`Month`,`Cmhp`.`Year`,  `Cmmp`.`MonthLowPrice`
  FROM `CurrentMonthHighPrice` `Cmhp`
	join `CurrentMonthLowPrice` `Cmmp` on `Cmmp`.`CoinID` = `Cmhp`.`CoinID` and `Cmmp`.`Month` = `Cmhp`.`Month` and `Cmmp`.`Year` = `Cmhp`.`Year`
  where `Cmhp`.`MonthHighPrice` <> 0 and `Cmmp`.`MonthLowPrice` <> 0";
  echo "<BR>".$sql;
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['CoinID'],$row['MonthHighPrice'],$row['Month'],$row['Year'],$row['MonthLowPrice']);
  }
  $conn->close();
return $tempAry;
}

function writePrice($coinID, $price, $month, $year, $minPrice){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call UpdateMonthlyMinMaxPrice($coinID,$minPrice,$price,$month,$year);";
  //print_r("<BR>".$sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("writePrice",$sql,3,1,SQLProcedureLog,"SQL CALL","CoinID:$coinID");
}



function getSecondaryRules(){
  $conn = getSQLConn(rand(1,3));
  $sql = "select `SecondarySellRules` from `CoinModeRules`";
  echo "<BR>".$sql;
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['SecondarySellRules']);
  }
  $conn->close();
return $tempAry;
}

function checkRuleIsOpen($id){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call deleteSecondaryRules($id);";
  //print_r("<BR>".$sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("checkRuleIsOpen",$sql,3,1,SQLProcedureLog,"SQL CALL","RuleID:$id");
}

function getSellRules(){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `ID`,`LimitToCoinID`,`UserID` FROM `SellRules` WHERE `CoinModeRule` = 1 ";
  echo "<BR>".$sql;
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['ID'],$row['LimitToCoinID'],$row['UserID']);
  }
  $conn->close();
return $tempAry;
}

function clearOrphanedRules($sellRule, $coinID, $userID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call OrphanedCoinModeSellRules($sellRule, $coinID, $userID);";
  //print_r("<BR>".$sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("clearOrphanedRules",$sql,3,1,SQLProcedureLog,"SQL CALL","SellRuleID:$sellRule UserID:$userID");
}

function getCoinHigh(){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `CoinID`,max(`MaxPrice`) as MaxPrice  FROM `MonthlyMaxPrices` group by `CoinID`";
  echo "<BR>".$sql;
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['CoinID'],$row['MaxPrice']);
  }
  $conn->close();
return $tempAry;
}

function getCoinLow(){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `CoinID`,Min(`MinPrice`) as MinPrice FROM `MonthlyMinPrices` group by `CoinID`";
  echo "<BR>".$sql;
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['CoinID'],$row['MinPrice']);
  }
  $conn->close();
return $tempAry;
}

function writeHigh($coinID, $price){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call AddAllTimeHigh($coinID, 'High', $price);";
  //print_r("<BR>".$sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("writeHigh",$sql,3,1,SQLProcedureLog,"SQL CALL","CoinID:$coinID");
}

function writeLow($coinID, $price){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call AddAllTimeLow($coinID, 'Low', $price);";
  //print_r("<BR>".$sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("writeLow",$sql,3,1,SQLProcedureLog,"SQL CALL","CoinID:$coinID");
}

function updateCoinPct($coinID,$buyRuleID, $mode){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call UpdateCoinModeRules($coinID, $buyRuleID, '$mode');";
  //print_r("<BR>".$sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("updateCoinPct",$sql,3,1,SQLProcedureLog,"SQL CALL","CoinID:$coinID BuyRuleID:$buyRuleID");
}

function updateSpreadPct($coinID,$spreadBetRuleID, $mode){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "UPDATE `CoinModeRules` SET `Hr1Btm` = Get1HrPct($coinID,$spreadBetRuleID,'$mode'), `Hr1Top` = (Get1HrPct($coinID,$spreadBetRuleID,'$mode') + 0.8)
  , `Hr24Btm` = -99.9, `Hr24Top` = Get24HrPct($coinID,$spreadBetRuleID,'$mode'), `D7Btm` = -99.9, `D7Top` = Get7DPct($coinID,$spreadBetRuleID,'$mode')
  WHERE `CoinID` = $coinID and `RuleID` = $spreadBetRuleID; ";
  //$sql = "call UpdateSpreadBetRules($coinID,$spreadBetRuleID,'$mode')";
  //print_r("<BR>".$sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("updateSpreadPct",$sql,3,1,sQLUpdateLog,"SQL","CoinID:$coinID SBRuleID:$spreadBetRuleID");
}

function updateCoinModeBuyPct(){
  $coinModeAry = getCoinModeData();
  $coinModeArySize = count($coinModeAry);
  for ($t=0; $t<$coinModeArySize; $t++){
    $coinID = $coinModeAry[$t][0];$buyRuleID = $coinModeAry[$t][1];
    updateCoinPct($coinID, $buyRuleID, 'CoinMode');
  }

}

function updateSpreadBetBuyPct(){
  $SpreadBetAry = getSpreadBetDataLoc();
  $SpreadBetArySize = count($SpreadBetAry);
  for ($t=0; $t<$SpreadBetArySize; $t++){
    $spreadBetRuleID = $SpreadBetAry[$t][0];
    updateSpreadPct(0, $spreadBetRuleID, 'SpreadBet');
  }

}

function getCoinModeData(){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `CoinID`,`RuleID` FROM `CoinModeRules` ";
  echo "<BR>".$sql;
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['CoinID'],$row['RuleID']);
  }
  $conn->close();
return $tempAry;
}

function getSpreadBetDataLoc(){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `ID` FROM `SpreadBetCoinStatsView` ";
  echo "<BR>".$sql;
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['ID']);
  }
  $conn->close();
return $tempAry;
}

function updateSpreadBetCoinHistory(){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call RefreshSpreadBetCoins();";
  //print_r("<BR>".$sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("updateSpreadBetCoinHistory",$sql,3,1,SQLProcedureLog,"SQL CALL","");
}

function getSoldfromSQL(){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `CoinID`, `DaysSinceSale`, `Type`, `ID`, `CompletionDate`,`PurchasePrice`,`SpreadBetRuleID` FROM `SellCoinStatsView_Sold`";
  echo "<BR>".$sql;
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['CoinID'],$row['DaysSinceSale'],$row['Type'],$row['ID'],$row['CompletionDate'],$row['PurchasePrice'],$row['SpreadBetRuleID']);
  }
  $conn->close();
return $tempAry;
}

function RunSellTrendUpdate(){
  $soldCoins = getSoldfromSQL();
  $soldCoinsSize = count($soldCoins);

  for ($e=0; $e<$soldCoinsSize; $e++){
    $date = $soldCoins[$e][4];
    $coinID = $soldCoins[$e][0];
    $purchasePrice = $soldCoins[$e][5];
    $spreadBetRuleID = $soldCoins[$e][6];
    $maxPrice = getMaxPct($date,$coinID);
    updateMaxPctToSql($maxPrice[0][0], $coinID, 'SpreadBetRuleID', $spreadBetRuleID);
  }

}

function DeleteCMCDisabledCoins(){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "Delete from `CMCData` where `ID` in (
            Select * From (
            Select `Cmc`.`ID` FROM `CMCData` `Cmc`
            join `Coin` `Cn` on `Cn`.`ID` = `Cmc`.`CoinID`
            WHERE `BuyCoin` = 0) as P
            )";
  //print_r("<BR>".$sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}




coinHistory(10);
DeleteHistory(192);
checkSellSequence();
$apisecret=getAPISecret();
$apikey=getAPIKey();
getMinTradeAmount($apikey,$apisecret);


//$minMaxPrice = getCurrentMonthMinMax();
//$minMaxPriceSize = count($minMaxPrice);

//for ($i=0; $i<$minMaxPriceSize; $i++){
//  writePrice($minMaxPrice[$i][0],$minMaxPrice[$i][1],$minMaxPrice[$i][2],$minMaxPrice[$i][3],$minMaxPrice[$i][4]);
//}

$secondarySellRulesAry = getSecondaryRules();
$secondarySellRulesSize = count($secondarySellRulesAry);

for ($j=0; $j<$secondarySellRulesSize; $j++){
  $smallerAry = explode(',',$secondarySellRulesAry[$j][0]);
  $smallerArySize = count($smallerAry);
  for ($k=0; $k<$smallerArySize; $k++){
    if ($smallerAry[$k] <> ""){
      checkRuleIsOpen($smallerAry[$k]);
    }
  }
}

$orphanRules = getSellRules();
$orphanRulesSize = count($orphanRules);

for ($k=0; $k<$orphanRulesSize; $k++){
    clearOrphanedRules($orphanRules[$k][0],$orphanRules[$k][1],$orphanRules[$k][2]);
}



$coinHigh = getCoinHigh();
$coinHighSize = count($coinHigh);
for ($q=0; $q<$coinHighSize; $q++){
    $coinID = $coinHigh[$q][0]; $price = $coinHigh[$q][1];
    writeHigh($coinID,$price);
}

$coinLow = getCoinLow();
$coinLowSize = count($coinLow);
for ($w=0; $w<$coinLowSize; $w++){
  $coinID = $coinLow[$w][0]; $price = $coinLow[$w][1];
  writeLow($coinID,$price);
}

//updateCoinModeBuyPct();
updateSpreadBetCoinHistory();

RunSellTrendUpdate();

DeleteCMCDisabledCoins();

?>
</html>
