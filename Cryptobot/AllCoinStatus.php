<html>
<?php
ini_set('max_execution_time', 500);
require('includes/newConfig.php');
include_once ('/home/stevenj1979/SQLData.php');
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
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `ID`,`Live1HrChange` FROM `CoinStatsView`";
  $result = $conn->query($sql);
  //print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['Live1HrChange']);
  }
  $conn->close();
  return $tempAry;
}


function getUserConfig(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "Select `Active`, `DisableUntil`,`Email`, `UserName`,`ID` FROM `UserConfigView`";
  $result = $conn->query($sql);
  //print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Active'],$row['DisableUntil'],$row['Email'],$row['UserName'],$row['ID']);
  }
  $conn->close();
  return $tempAry;
}

function get1HrChangeSum(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Hr1Diff`,`noOfCoins` FROM `AllCoinStatusView`";
  $result = $conn->query($sql);
  //print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Hr1Diff'],$row['noOfCoins']);
  }
  $conn->close();
  return $tempAry;
}

function update1HrAllCoin($coinID, $hr1Diff){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call update1HrAllCoin($coinID,$hr1Diff);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function tempDisableUsers($mins){
  $date = date("Y-m-d H:i", time());
  if ($mins > 0){
    $newDate = date('Y-m-d H:i', strtotime($date. " +$mins minutes"));
  }else{
    $newDate = $date;
  }
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call updateTempDisableUsers('$newDate');";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
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
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  //$date = date('Y-m-d H:i', time());
  $sql = "DELETE FROM `NewUserProfit`";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function updateTotalProfit(){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  //$date = date('Y-m-d H:i', time());
  $sql = "Insert into `NewUserProfit`
  SELECT `Tv`.`CoinID`,`Tv`.`CoinPrice`,`Tv`.`Amount`,`Tv`.`Status`,`Tv`.`UserID`, `Cp`.`LiveCoinPrice`
  , `Tv`.`CoinPrice`*`Tv`.`Amount`as PurchasePrice
  ,`Cp`.`LiveCoinPrice` *`Tv`.`Amount`as LivePrice
  ,(`Cp`.`LiveCoinPrice` *`Tv`.`Amount`) - (`Tv`.`CoinPrice`*`Tv`.`Amount`)  as Profit
  ,`Tv`.`BuyRule` as `RuleID`
  FROM `TransactionsView` `Tv`
  join `CoinPrice` `Cp` on `Cp`.`CoinID` = `Tv`.`CoinID`
  WHERE (`Tv`.`Status` = 'Open') OR (`Tv`.`Status` = 'Pending')";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function updateAllCoinRunningPrice(){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  //$date = date('Y-m-d H:i', time());
  $sql = "Insert into `AllCoinRunningPrice` (`Price`)
          SELECT sum(`Cp`.`LiveCoinPrice`)
          FROM `CoinPrice` `Cp`
          join `Coin` `Cn` on `Cn`.`ID` = `Cp`.`CoinID`
          WHERE `Cn`.`BuyCoin` = 1 ";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}


function ConsolidatePriceHostory(){
  $conn = getHistorySQL(rand(1,4));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  //$date = date('Y-m-d H:i', time());
  $sql = "call PriceHistoryConsolidate(date_sub(now(),INTERVAL 5 Minute),now())";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function update1Hr_24Hr_7DPct(){
  $coins = getTrackingCoins();
  $coinsSize = count($coins);
  for ($u=0; $u<$coinsSize;$u++){
    $coinID = $coins[$u][0]; $bitPrice = $coins[$u][17]; $baseCurrency = $coins[$u][26]; $newhr1_Pct = $coins[$u][10]; $newhr24_Pct = $coins[$u][13];
    $newd7_Pct = $coins[$u][16];
    $price1Hr = get1HrChange($coinID);
    //$tmpPrice = (($price1Hr[0][0]-$bitPrice)/$price1Hr[0][0])*100;
    update1HrPriceChange($price1Hr[0][0],$coinID);
    $price24Hr = get24HrChange($coinID);
    //$tmpPrice = (($price24Hr[0][0]-$bitPrice)/$price24Hr[0][0])*100;
    update24HrPriceChange($price24Hr[0][0],$coinID);
    $price7Day = get7DayChange($coinID);
    //$tmpPrice = (($price7Day[0][0]-$bitPrice)/$price7Day[0][0])*100;
    update7DPriceChange($price7Day[0][0],$coinID);
    $nDate = date("Y-m-d H:i:s", time());
    //$newhr1_Pct = (($newhr1_Pct-$bitPrice)/$newhr1_Pct)*100;
    //$newhr24_Pct = (($newhr24_Pct-$bitPrice)/$newhr24_Pct)*100;
    //$newd7_Pct = (($newd7_Pct-$bitPrice)/$newd7_Pct)*100;
    echo "<BR> coinPriceHistory($coinID,$bitPrice,$baseCurrency,$nDate,$newhr1_Pct,$newhr24_Pct,$newd7_Pct";
    coinPriceHistory($coinID,$bitPrice,$baseCurrency,$nDate,$newhr1_Pct,$newhr24_Pct,$newd7_Pct);
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

update1Hr_24Hr_7DPct();

?>
</html>
