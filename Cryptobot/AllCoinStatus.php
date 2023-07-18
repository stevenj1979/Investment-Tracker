<html>
<?php
ini_set('max_execution_time', 500);
require('includes/newConfig.php');
include_once ('/home/stevenj1979/SQLData.php');
include_once ('includes/SQLDbCommands.php');

//Define("sQLUpdateLog","0");
//Define("SQLProcedureLog","0");

$apikey=getAPIKey();
$apisecret=getAPISecret();

$tmpTime = "+5 seconds";
if (!empty($argv[1])){
  parse_str($argv[1], $params);
  $tmpTime = str_replace('_', ' ', $params['mins']);
  //echo $params['code'];
  //error_log($argv[1], 0);
}
echo "<BR> isEmpty : ".empty($_GET['mins']);
if (!empty($_GET['mins'])){
  $tmpTime = str_replace('_', ' ', $_GET['mins']);
  echo "<br> GETMINS: ".$_GET['mins'];
}

function get1HrChangeAll(){
  $tempAry = [];
  /*$conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }*/

  $sql = "SELECT `IDCn`,`Live1HrChange` FROM `View1_BuyCoins`";
  $tempAry = mySQLSelect("get1HrChangeAll: ",$sql,3,1,1,0,"AllCoinStatus",90);
  /*$result = $conn->query($sql);
  //print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['IDCn'],$row['Live1HrChange']);
  }
  $conn->close();*/
  return $tempAry;
}


function getUserConfig(){
  $tempAry = [];
  /*$conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }*/

  $sql = "Select `Active`, `DisableUntil`,`Email`, `UserName`,`IDUs`  FROM `View12_UserConfig`";
  $tempAry = mySQLSelect("getUserConfig: ",$sql,3,1,1,0,"AllCoinStatus",90);
  /*$result = $conn->query($sql);
  //print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Active'],$row['DisableUntil'],$row['Email'],$row['UserName'],$row['IDUs']);
  }
  $conn->close();*/
  return $tempAry;
}

function get1HrChangeSum(){
  $tempAry = [];
  /*$conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }*/

    $sql = "SELECT sum(((`LiveCoinPrice`-`Live1HrChange`)/`Live1HrChange`)*100) as `Hr1Diff`,count(`CoinID`) as `noOfCoins` FROM `View1_BuyCoins`";
    $tempAry = mySQLSelect("get1HrChangeSum: ",$sql,3,1,1,0,"AllCoinStatus",90);
  /*$result = $conn->query($sql);
  //print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Hr1Diff'],$row['noOfCoins']);
  }
  $conn->close();*/
  return $tempAry;
}

function update1HrAllCoin($coinID, $hr1Diff){
  /*$conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}*/
  $sql = "call update1HrAllCoin($coinID,$hr1Diff);"; //Doesnt exist need to Recreate
  //SQLInsertUpdateCall("update1HrAllCoin: ",$sql,3, 1, 1, 0, "AllCoinStatus", 90);
  /*print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("update1HrAllCoin",$sql,3,0,"SQL CALL","CoinID:$coinID");*/
}

function tempDisableUsers($mins){
  $date = date("Y-m-d H:i", time());
  if ($mins > 0){
    $newDate = date('Y-m-d H:i', strtotime($date. " +$mins minutes"));
  }else{
    $newDate = $date;
  }
  /*$conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}*/
  $sql = "call updateTempDisableUsers('$newDate');";
  SQLInsertUpdateCall("tempDisableUsers: ",$sql,3, 1, 1, 0, "AllCoinStatus", 90);
  /*print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("tempDisableUsers",$sql,3,0,"SQL CALL","Mins:$mins");*/
}

function emailUsersDisable($userConfig, $action, $disableUntil){
  $from = 'Coin Alert <alert@investment-tracker.net>';
  $subject = 'Account '.$action;
  $configSize = count($userConfig);
  for($y = 0; $y < $configSize; $y++) {
    $user = $userConfig[$y][3]; $to = $userConfig[$y][2];
    $body = "Dear ".$user.", <BR/>";
    $body .= "Your account as been $action: ".$disableUntil."<BR/>";
    $body .= "An email will be sent if the account can be enabled again.<BR/>";
    $body .= "Kind Regards\nCryptoBot.";
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    //$headers .= 'From: Alerts <Alerts@Investment-Tracker.net>' . "\r\n";
    $headers .= "From:".$from."\r\n";
    $headers .= "To:".$to."\r\n";
    mail($to, $subject, wordwrap($body,70),$headers);
  }
}

function emailUsersReenable($userConfig, $action, $disableUntil){
  $from = 'Coin Alert <alert@investment-tracker.net>';
  $subject = 'Account '.$action;
  $configSize = count($userConfig);
  for($y = 0; $y < $configSize; $y++) {
    $user = $userConfig[$y][3]; $to = $userConfig[$y][2];
    $body = "Dear ".$user.", <BR/>";
    $body .= "Your account was $action at: ".$disableUntil."<BR/>";
    $body .= "Kind Regards\nCryptoBot.";
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    //$headers .= 'From: Alerts <Alerts@Investment-Tracker.net>' . "\r\n";
    $headers .= "From:".$from."\r\n";
    $headers .= "To:".$to."\r\n";
    mail($to, $subject, wordwrap($body,70),$headers);
  }
}

function deleteTotalProfit(){
  /*$conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}*/
  //$date = date('Y-m-d H:i', time());
  $sql = "DELETE FROM `NewUserProfit`";
  SQLInsertUpdateCall("deleteTotalProfit: ",$sql,3, 1, 1, 0, "AllCoinStatus", 90);
  /*print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();*/
}

function updateTotalProfit(){
  /*$conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}*/
  //$date = date('Y-m-d H:i', time());
  $sql = "INSERT into `NewUserProfit`  ( `CoinID`, `CoinPrice`, `Amount`, `Status`, `UserID`, `LiveCoinPrice`, `PurchasePrice`, `LivePrice`, `Profit`, `RuleID`, `SpreadBetRuleID`, `SpreadBetTransactionID` )
          SELECT `Tv`.`CoinID` as `CoinID`,`Tv`.`CoinPrice` as `CoinPrice`,`Tv`.`Amount` as `Amount` ,`Tv`.`Status` as `Status`,`Tv`.`UserID` as `UserID`, `Cp`.`LiveCoinPrice` as `LiveCoinPrice`
            , `Tv`.`CoinPrice`*`Tv`.`Amount`as `PurchasePrice`
            ,`Cp`.`LiveCoinPrice` *`Tv`.`Amount`as `LivePrice`
            ,(`Cp`.`LiveCoinPrice` *`Tv`.`Amount`) - (`Tv`.`CoinPrice`*`Tv`.`Amount`)  as `Profit`
            ,`Tv`.`BuyRule` as `RuleID`,`Tv`.`SpreadBetRuleID` as `SpreadBetRuleID`,`Tv`.`SpreadBetTransactionID` as `SpreadBetTransactionID`
            FROM `Transaction` `Tv`
            join `CoinPrice` `Cp` on `Cp`.`CoinID` = `Tv`.`CoinID`
            WHERE (`Tv`.`Status` = 'Open') OR (`Tv`.`Status` = 'Pending')";
  SQLInsertUpdateCall("updateTotalProfit: ",$sql,3, 1, 1, 0, "AllCoinStatus", 90);
  /*print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();*/
}

function updateAllCoinRunningPrice(){
  /*$conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}*/
  //$date = date('Y-m-d H:i', time());
  $sql = "Insert into `AllCoinRunningPrice` (`Price`)
          SELECT sum(`Cp`.`LiveCoinPrice`)
          FROM `CoinPrice` `Cp`
          join `Coin` `Cn` on `Cn`.`ID` = `Cp`.`CoinID`
          WHERE `Cn`.`BuyCoin` = 1 ";
  SQLInsertUpdateCall("updateAllCoinRunningPrice: ",$sql,3, 1, 1, 0, "AllCoinStatus", 90);
  /*print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();*/
}


function ConsolidatePriceHostory(){
  /*$conn = getHistorySQL(rand(1,4));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}*/
  //$date = date('Y-m-d H:i', time());
  $sql = "call PriceHistoryConsolidate(date_sub(now(),INTERVAL 5 Minute),now())";
  SQLInsertUpdateCall("ConsolidatePriceHostory: ",$sql,3, 1, 1, 1, "AllCoinStatus", 90);
  /*print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("AllCoinStatus",$sql,3,0,"ConsolidatePriceHostory","");*/
}

function update1Hr_24Hr_7DPct(){
  $coins = getTrackingCoins("WHERE `DoNotBuy` = 0 and `BuyCoin` = 1 ORDER BY `Symbol` ASC","FROM `View1_BuyCoins` ");
  $coinsSize = count($coins);
  for ($u=0; $u<$coinsSize;$u++){
    $coinID = $coins[$u][0]; $bitPrice = $coins[$u][17]; $baseCurrency = $coins[$u][26]; $newhr1_Pct = $coins[$u][10]; $newhr24_Pct = $coins[$u][13];
    $newd7_Pct = $coins[$u][16];
    $price1Hr = get1HrChange($coinID);
    //$tmpPrice = (($price1Hr[0][0]-$bitPrice)/$price1Hr[0][0])*100;
    $Hr1PriceTmp = $price1Hr[0][0];
    Echo "<BR> update1HrPriceChange($Hr1PriceTmp,$coinID);";
    update1HrPriceChange($Hr1PriceTmp,$coinID);
    $price24Hr = get24HrChange($coinID);
    //$tmpPrice = (($price24Hr[0][0]-$bitPrice)/$price24Hr[0][0])*100;
    $Hr24PriceTmp = $price24Hr[0][0];
    Echo "<BR> update24HrPriceChange($Hr24PriceTmp,$coinID);";
    update24HrPriceChange($Hr24PriceTmp,$coinID);
    $price7Day = get7DayChange($coinID);
    //$tmpPrice = (($price7Day[0][0]-$bitPrice)/$price7Day[0][0])*100;
    $D7PriceTmp = $price7Day[0][0];
    Echo "<BR> update7DPriceChange($D7PriceTmp,$coinID);";
    update7DPriceChange($D7PriceTmp,$coinID);
    $nDate = date("Y-m-d H:i:s", time());
    //$newhr1_Pct = (($newhr1_Pct-$bitPrice)/$newhr1_Pct)*100;
    //$newhr24_Pct = (($newhr24_Pct-$bitPrice)/$newhr24_Pct)*100;
    //$newd7_Pct = (($newd7_Pct-$bitPrice)/$newd7_Pct)*100;
    echo "<BR> coinPriceHistory($coinID,$bitPrice,$baseCurrency,$nDate,$newhr1_Pct,$newhr24_Pct,$newd7_Pct";
    coinPriceHistory($coinID,$bitPrice,$baseCurrency,$nDate,$newhr1_Pct,$newhr24_Pct,$newd7_Pct);
  }

}

function addBearBullStatsToSQL($price,$coinID){
  /*$conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }*/
  $sql = "Call UpdateLastPriceBearBull($coinID,$price);";
  SQLInsertUpdateCall("addBearBullStatsToSQL: ",$sql,3, 1, 1, 0, "AllCoinStatus", 90);
  /*print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("addBearBullStatsToSQL: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("AllCoinStatus",$sql,3,0,"addBearBullStatsToSQL","CoinID:$coinID");*/
}

function addMarketBearBullStatsToSQL($price){
  /*$conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }*/
  $sql = "UPDATE `BearBullStats` SET `MarketPriceChange`= $price";
  SQLInsertUpdateCall("addMarketBearBullStatsToSQL: ",$sql,3, 1, 1, 0, "AllCoinStatus", 90);
  /*print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("addMarketBearBullStatsToSQL: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("AllCoinStatus",$sql,3,0,"addMarketBearBullStatsToSQL","Price:$price");*/
}

function addHistoryBearBullStatsToSQL($coinID,$hr1Pct,$hr24Pct,$d7Pct,$min15Pct,$min30Pct,$min45Pct,$min75Pct){
  /*$conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }*/
  $sql = "UPDATE `BearBullStats` SET `OneHrPriceChange`= $hr1Pct, `Twenty4HrPriceChange` = $hr24Pct, `Min15PriceChange`=$min15Pct, `Min30PriceChange`=$min30Pct
  ,`Min45PriceChange`=$min45Pct, `Min75PriceChange`=$min75Pct, `Days7PriceChange` = $d7Pct where `CoinID` = $coinID ";
  SQLInsertUpdateCall("addHistoryBearBullStatsToSQL: ",$sql,3, 1, 1, 0, "AllCoinStatus", 90);
  /*print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("addHistoryBearBullStatsToSQL: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("AllCoinStatus",$sql,3,0,"addHistoryBearBullStatsToSQL","CoinID:$coinID");*/
}

function getBearBullStats(){
  $tempAry = [];
  /*$conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }*/

  $sql = "SELECT `LiveCoinPrice`,`LastCoinPrice`,`IDCn` FROM `View1_BuyCoins` ";
  $tempAry = mySQLSelect("getBearBullStats: ",$sql,3,1,1,0,"AllCoinStatus",90);
  /*echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['LiveCoinPrice'],$row['LastCoinPrice'],$row['IDCn']);
  }
  $conn->close();*/
  return $tempAry;
}

function getHistoryStats(){
  $tempAry = [];
  /*$conn = getHistorySQL(rand(1,4));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}*/
  $sql = "SELECT `ID`, `CoinID`, `1HrPrice`, `24HrPrice`, `7DPrice`, `15MinPrice`, `30MinPrice`, `45MinPrice`, `75MinPrice` FROM `PricePctChangeHistory`";
  $tempAry = mySQLSelect("getHistoryStats: ",$sql,3,1,1,1,"AllCoinStatus",90);
  /*print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['ID'],$row['CoinID'],$row['1HrPrice'],$row['24HrPrice'],$row['7DPrice'],$row['15MinPrice'],$row['30MinPrice'],$row['45MinPrice'],$row['75MinPrice']);
  }
  $conn->close();*/
  return $tempAry;
}



function BearBullStats(){
  $livePriceChange = getBearBullStats();
  $livePriceChangeSize = count($livePriceChange);
  for ($m=0;$m<$livePriceChangeSize;$m++){
      $price =  $livePriceChange[$m][0] - $livePriceChange[$m][1];
      $pricePct = ($price/$livePriceChange[$m][1])*100;
      $coinID =  $livePriceChange[$m][2];
      addBearBullStatsToSQL($pricePct,$coinID);
  }
  $marketStats = getMarketChange();
  $marketStatsSize = count($marketStats);
  for ($p=0;$p<$marketStatsSize;$p++){
    $price = $marketStats[$p][0] - $marketStats[$p][1];
    $pricePct = ($price/$marketStats[$p][1])*100;
    echo "<BR>Price $price |  $pricePct LivePrice ".$marketStats[$p][0]." | LastPrice ".$marketStats[$p][1]."<BR>";
    addMarketBearBullStatsToSQL($pricePct);
  }
  $historyStats = getHistoryStats();
  $historyStatsSize = count($historyStats);
  $livePriceAry = getBearBullStats();
  $livePriceArySize = count($livePriceAry);
  for ($l=0;$l<$historyStatsSize;$l++){
      $coinID = $historyStats[$l][1]; $hr1Price = $historyStats[$l][2];$hr24Price = $historyStats[$l][3]; $d7Price = $historyStats[$l][4];
      $min15Price = $historyStats[$l][5]; $min30Price = $historyStats[$l][6]; $min45Price = $historyStats[$l][7]; $min75Price = $historyStats[$l][8];
      for ($k=0;$k<$livePriceArySize;$k++){
          $livePrice = $livePriceAry[$k][0]; $PriceCoinID = $livePriceAry[$k][2];
          if ($coinID == $PriceCoinID ){
            $hr1Pct = (($livePrice - $hr1Price)/$hr1Price)*100;
            $hr24Pct = (($livePrice - $hr24Price)/$hr24Price)*100;
            $d7Pct = (($livePrice - $d7Price)/$d7Price)*100;
            echo "<BR> Min15Pct:$livePrice - $min15Price / $min15Price ;<br>";
            if ($min15Price == 0){
              $min15Pct = 0;
            }else{
              $min15Pct = (($livePrice - $min15Price)/$min15Price)*100;
            }
            if ($min30Price == 0){
              $min30Pct = 0;
            }else{
              $min30Pct = (($livePrice - $min30Price)/$min30Price)*100;
            }
            if ($min45Price == 0){
              $min45Pct = 0;
            }else{
              $min45Pct = (($livePrice - $min45Price)/$min45Price)*100;
            }
            if($min75Price == 0){
              $min75Pct = 0;
            }else{
              $min75Pct = (($livePrice - $min75Price)/$min75Price)*100;
            }

            echo "<BR> addHistoryBearBullStatsToSQL($coinID,$hr1Pct,$hr24Pct,$d7Pct,$min15Pct,$min30Pct,$min45Pct,$min75Pct);<br>";
            addHistoryBearBullStatsToSQL($coinID,$hr1Pct,$hr24Pct,$d7Pct,$min15Pct,$min30Pct,$min45Pct,$min75Pct);
          }
      }
  }
}

function getMarketChange(){
  $tempAry = [];
  /*$conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }*/

  $sql = "SELECT sum(`LiveCoinPrice`) as `LiveCoinPrice`,sum(`LastCoinPrice`) as `LastCoinPrice` FROM `View1_BuyCoins` WHERE `BuyCoin` = 1 and `DoNotBuy`= 0";
  $tempAry = mySQLSelect("getMarketChange: ",$sql,3,1,1,0,"AllCoinStatus",90);
  /*echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['LiveCoinPrice'],$row['LastCoinPrice']);
  }
  $conn->close();*/
  return $tempAry;
}

function runMarketStats(){

    writeMarketStatsCurr('USDT');
    writeMarketStatsCurr('BTC');
    writeMarketStatsCurr('ETH');
    writeMarketStats();
}

function writeMarketStats(){

  /*$conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }*/

  $sql = "Call UpdateMarketStats();";
  SQLInsertUpdateCall("writeMarketStats: ",$sql,3, 1, 1, 0, "AllCoinStatus", 90);
  /*print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("writeMarketStats: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("writeMarketStats","$sql",3,0,"SQL CALL","");*/
}

function writeMarketStatsCurr($baseCurrency){

  /*$conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }*/

  $sql = "Call UpdateMarketStatsCurr('$baseCurrency');";
  SQLInsertUpdateCall("writeMarketStatsCurr: ",$sql,3, 1, 1, 0, "AllCoinStatus", 90);
  /*print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("writeMarketStatsCurr: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("writeMarketStatsCurr","$sql",3,0,"SQL CALL","");*/
}

function DeleteMarketStats(){
  /*$conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }*/

  $sql = "DELETE FROM `MarketCoinStats`";
  SQLInsertUpdateCall("DeleteMarketStats: ",$sql,3, 1, 1, 0, "AllCoinStatus", 90);
  /*print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("DeleteMarketStats: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("DeleteMarketStats","$sql",3,1,"SQL CALL","");*/
}

function runHoursforCoinPriceDip(){
  //$priceDipTolerance = 1.0;
  $dipHourCounter = 0; $dipHourCounterLow = 0; $dipHourCounterHigh = 0;
  $lowFlag = True;$highFlag = True;$flatFlag = True;
  //get ID's
  $coinIDAry = getHoursforCoinPriceDip("WHERE `BuyCoin` = 1 ");
  $coinIDArySize = count($coinIDAry);
  echo "<BR>***** runHoursforCoinPriceDip ***** CoinIDSize: $coinIDArySize";
  for ($u=0;$u<$coinIDArySize; $u++){
    $coinID = $coinIDAry[$u][0]; $liveCoinPrice = $coinIDAry[$u][1]; $userID = $coinIDAry[$u][2]; $priceDipTolerance = $coinIDAry[$u][3];
    //getPrice
    $coinPriceAry = getPriceDipCoinPrices($coinID);
    $coinPriceArySize = count($coinPriceAry);
    $priceWithToleranceBtm = $liveCoinPrice-(($liveCoinPrice/100)*$priceDipTolerance);
    $priceWithToleranceTop = $liveCoinPrice+(($liveCoinPrice/100)*$priceDipTolerance);
    echo "<BR> CHECKING : $coinID | $coinPriceArySize | $liveCoinPrice | $priceWithToleranceTop | $priceWithToleranceBtm";
    for ($p=0;$p<$coinPriceArySize; $p++){
      $coinDipPrice = $coinPriceAry[$p][2]; $coinDipDate = $coinPriceAry[$p][3];


      if ($lowFlag == true){
      	if ($coinDipPrice > $priceWithToleranceBtm){
              $dipHourCounterLow = $dipHourCounterLow + 1;
              //Echo " | LOW | ";
        }else{
      		    $lowFlag = False;
      	}
      }

      if ($highFlag == true){
          if ($coinDipPrice < $priceWithToleranceTop){
              $dipHourCounterHigh = $dipHourCounterHigh + 1;
              //Echo " | HIGH | ";
          }else{
      		    $highFlag = False;
      	  }
      }

      if ($flatFlag == true){
            if ($coinDipPrice >= $priceWithToleranceBtm AND $coinDipPrice <= $priceWithToleranceTop){
              $dipHourCounter = $dipHourCounter + 1;
              //echo "| $coinID : Live Price is: $liveCoinPrice | Live with Tol: $priceWithToleranceBtm : $priceWithToleranceTop | Prev Price: $coinDipPrice | Counter: $dipHourCounter";
              Echo " | Within Tol | ";
            }else{
             	$flatFlag = False;
              Echo " | Outside Tol | $coinDipPrice ";
            }
      }

      if ($flatFlag == False AND $highFlag == False AND $lowFlag == False){
        echo " FINAL:  $flatFlag | $highFlag | $lowFlag | writePriceDipCoinHours($coinID, $dipHourCounter, $dipHourCounterLow , $dipHourCounterHigh);";
      	writePriceDipCoinHours($coinID, $dipHourCounter, $dipHourCounterLow , $dipHourCounterHigh);
          	$dipHourCounter = 0; $dipHourCounterLow = 0; $dipHourCounterHigh = 0;
         	$lowFlag = True;$highFlag = True; $flatFlag = True;
      	continue 2;
      }
    }
    writePriceDipCoinHours($coinID, $dipHourCounter, $dipHourCounterLow , $dipHourCounterHigh);
    $dipHourCounter = 0; $dipHourCounterLow = 0; $dipHourCounterHigh = 0;
    $lowFlag = True;$highFlag = True; $flatFlag = True;
  }
}

function runCoinBuyHistoryAverage(){
  /*$conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }*/

  $sql = "call HourlyAvgCoinPriceCoinBuyHistory();";
  SQLInsertUpdateCall("runCoinBuyHistoryAverage: ",$sql,3, 1, 1, 0, "AllCoinStatus", 90);
  /*print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("runCoinBuyHistoryAverage: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("runCoinBuyHistoryAverage","$sql",3,0,"SQL CALL","");*/

}

function writeCalculatedPrices($hr1Price,$hr24Price, $d7Price, $coinID){
  $sql = "call WriteCalcPrices($hr1Price,$hr24Price, $d7Price, $coinID);";
  SQLInsertUpdateCall("writeCalculatedPrices: ",$sql,3, 1, 1, 0, "AllCoinStatus", 90);
}

function getCalculatedPrices(){
  $tempAry = [];
  $sql ="SELECT `Cn`.`ID`,`Cp`.`LiveCoinPrice`,`Cmc`.`1HrPrice`,`Cmc`.`24HrPrice`,`Cmc`.`7DayPrice`
  ,`Cp`.`LiveCoinPrice`+(`Cp`.`LiveCoinPrice`/100)*`Cmc`.`1HrPrice` as `Hr1CalculatedPrice`
  ,`Cp`.`LiveCoinPrice`+(`Cp`.`LiveCoinPrice`/100)*`Cmc`.`24HrPrice` as `Hr24CalculatedPrice`
  ,`Cp`.`LiveCoinPrice`+(`Cp`.`LiveCoinPrice`/100)*`Cmc`.`7DayPrice` as `D7CalculatedPrice`
  FROM `Coin` `Cn`
  join `CMCData` `Cmc` on `Cn`.`ID` = `Cmc`.`CoinID`
  join `CoinPrice` `Cp` on `Cp`.`CoinID` = `Cn`.`ID`
  WHERE `Cn`.`BuyCoin` = 1";
  $tempAry = mySQLSelect("getAveragePrice: ",$sql,3,1,1,0,"NewConfig",90);
  return $tempAry;
}

function runCalculatedPrices(){
  $calcPrice = getCalculatedPrices();
  $calcPriceSize = count($calcPrice);
  for ($w=0;$w<$calcPriceSize;$w++){
    $hr1Price = $calcPrice[$w][5];$hr24Price = $calcPrice[$w][6];$d7Price = $calcPrice[$w][7]; $coinID = $calcPrice[$w][0];
    writeCalculatedPrices($hr1Price,$hr24Price, $d7Price, $coinID);
  }
}

//set time
setTimeZone();
$date = date("Y-m-d H:i", time());
$current_date = date('Y-m-d H:i');
//$newTime = date("Y-m-d H:i",strtotime("+5 minutes", strtotime($current_date)));
$newTime = date("Y-m-d H:i",strtotime("+6 hours", strtotime( date('Y-m-d H:i'))));
$i = 0;
$coins = get1HrChangeAll();
$coinLength = Count($coins);


echo "NEW LOOP ";
// ***  UPDATE 1Hour Coin price
for($x = 0; $x < $coinLength; $x++) {
  //variables
  if ($coins[$x][1] > 0){
    //update SQL with 1
    update1HrAllCoin($coins[$x][0],1);
  }else{
    //update SQL with 0
    update1HrAllCoin($coins[$x][0],0);
  }
}//loop Coins

$hr1ChangeSum = get1HrChangeSum();
$userConfig = getUserConfig();
$disabledEndTime = date("Y-m-d H:i",strtotime("-5 minutes", strtotime( $userConfig[0][1])));
echo "<BR> SUM: ".$hr1ChangeSum[0][0]." Count: ".$hr1ChangeSum[0][1]." PCT: ".(($hr1ChangeSum[0][0]/$hr1ChangeSum[0][1])*100)."disabledEndTime : $disabledEndTime";

if (($hr1ChangeSum[0][0]/$hr1ChangeSum[0][1])*100 <= 50 &&  date("Y-m-d H:i", time()) > $disabledEndTime){
    //disable for 6 hours
    echo "<BR> Disabling Users for 6 hours!";
    //tempDisableUsers(360);
    //emailUsersDisable($userConfig, "suspended", date("Y-m-d H:i",strtotime("+6 hours", strtotime( date('Y-m-d H:i')))));
}
echo "<BR> DisableUntil: ".$userConfig[0][1]." PCT: ".($hr1ChangeSum[0][0]/$hr1ChangeSum[0][1])*100;

if (($hr1ChangeSum[0][0]/$hr1ChangeSum[0][1])*100 > 50 && $userConfig[0][1] > date("Y-m-d H:i", time())){
    //tempDisableUsers(20);
    //emailUsersReenable($userConfig, "re-activated", date('Y-m-d H:i'));
}



$i = $i+1;
$date = date("Y-m-d H:i", time());
$hours = 6;
$newDate = date('Y-m-d H:i', strtotime($date. " +$hours hours"));
echo "<BR> NEWDATE plus 6: $newDate";
$hours = 0;
$newDate = date('Y-m-d H:i', strtotime($date. " +$hours hours"));
echo "<BR> NEWDATE plus 0: $newDate";
echo "EndTime ".date("Y-m-d H:i", time());
//tempDisableUsers(0);
//sendEmail('stevenj1979@gmail.com',$i,0,$date,0,'CryptoAuto Loop Finished', 'stevenj1979', 'Coin Purchase <purchase@investment-tracker.net>');
//DeleteTotalProfit();
//updateTotalProfit();
//updateAllCoinRunningPrice();

//update1Hr_24Hr_7DPct();

//BearBullStats();
runHoursforCoinPriceDip();
runCalculatedPrices();
runMarketStats();
for ($t=0;$t<5;$t++){  //run 5 times
    runCoinBuyHistoryAverage();
}

?>
</html>
