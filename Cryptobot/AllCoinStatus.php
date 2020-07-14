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
  if ($hours > 0){
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
    tempDisableUsers(360);
    //emailUsersDisable($userConfig, "suspended", date("Y-m-d H:i",strtotime("+6 hours", strtotime( date('Y-m-d H:i')))));
}
echo "<BR> DisableUntil: ".$userConfig[0][1]." PCT: ".($hr1ChangeSum[0][0]/$hr1ChangeSum[0][1])*100;

if (($hr1ChangeSum[0][0]/$hr1ChangeSum[0][1])*100 > 50 && $userConfig[0][1] > date("Y-m-d H:i", time())){
    tempDisableUsers(20);
    emailUsersReenable($userConfig, "re-activated", date('Y-m-d H:i'));
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
?>
</html>
