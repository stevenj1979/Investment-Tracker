

<html>
<?php
include_once ('/home/stevenj1979/public_html/Investment-Tracker/Cryptobot/includes/newConfig.php');
include_once ('/home/stevenj1979/SQLData.php');
include_once ('/home/stevenj1979/Encrypt.php');

$apikey=getAPIKey();
$apisecret=getAPISecret();
$logToSQLSetting = getLogToSQL();
//$GLOBALS['logToFileSetting']  = getLogToFile();
$GLOBALS['logToSQLSetting'] = getLogToSQL();
$GLOBALS['logToFileSetting'] = getLogToFile();

$programRunTime = "+5 seconds";
if (!empty($argv[1])){
  parse_str($argv[1], $params);
  $programRunTime = str_replace('_', ' ', $params['mins']);
  //echo $tmpTime;
  //error_log($argv[1], 0);
}
function getTimeFromSQL(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT  `NextRunTime`, `Name`  FROM `CryptoBotDirector`";
  //echo "<BR> $sql";
  newLogToSQL("getMultiSellRulesTemplate", "$sql", 3, 1,"SQL CALL","RULEID:$ruleID");
    $result = $conn->query($sql);
    //$result = mysqli_query($link4, $query);
    //mysqli_fetch_assoc($result);
    while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['NextRunTime'],$row['Name']);
    }
    $conn->close();
    return $tempAry;
}

function writeSQLTime($name, $time){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `CryptoBotDirector` SET `NextRunTime`= '$time' WHERE `Name`= '$name' ";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("writeSQLTime: ".$sql, 'SQL_CALL', 0);
  newLogToSQL("writeSQLTime","$sql",3,1,"CryptoBotDirector","Name:$name");
}

function getTimer($timerAry, $name){
  $timerArySize = count($timerAry);
  //echo "<BR> ArraySize: $timerArySize";
  for($e=0;$e<$timerArySize;$e++){
    //echo"<BR>Name: $name | ".$timerAry[$e][1];
    if ($timerAry[$e][1] == $name){
      //echo "<BR>Found : ".$timerAry[$e][1]." | ".$timerAry[$e][0];
      return $timerAry[$e][0];
    }
  }
}
$timerAry = getTimeFromSQL();
//Program
//$programRunTime = "+30 minutes";
$newTime = date("Y-m-d H:i",strtotime($programRunTime, strtotime(date('Y-m-d H:i'))));
//AllCoinStatus
$allCoinsStatusRunTime = "+20 minutes";
//$allCoinStatusTimer = date("Y-m-d H:i",strtotime($allCoinsStatusRunTime, strtotime(date('Y-m-d H:i'))));
$allCoinStatusTimer = getTimer($timerAry,"allCoinStatus");
echo "<BR> AllCoinStatusTimer: $allCoinStatusTimer | currentTime ".date('Y-m-d H:i');
//Dashboard
$dashBoardRunTime = "+20 minutes";
//$dashboardTimer = date("Y-m-d H:i",strtotime($dashBoardRunTime, strtotime(date('Y-m-d H:i'))));
$dashboardTimer = getTimer($timerAry,"dashBoard");
//AutoUpdatePrice
$autoUpdatePriceRunTime = "+20 minutes";
//$autoUpdatePriceTimer = date("Y-m-d H:i",strtotime($autoUpdatePriceRunTime, strtotime(date('Y-m-d H:i'))));
$autoUpdatePriceTimer = getTimer($timerAry,"autoUpdatePrice");
//Hourly
$coinHourlyRunTime = "+60 minutes";
//$coinHourlyTimer = date("Y-m-d H:i",strtotime($coinHourlyRunTime, strtotime(date('Y-m-d H:i'))));
$coinHourlyTimer = getTimer($timerAry,"coinHourly");
//CoinMode
$coinModeRunTime = "+30 minutes";
//$coinModeTimer = date("Y-m-d H:i",strtotime($coinModeRunTime, strtotime(date('Y-m-d H:i'))));
$coinModeTimer = getTimer($timerAry,"coinMode");
//PctChangeProcess
$pctChangeProcessRunTime = "+20 minutes";
//$pctChangeProcessTimer = date("Y-m-d H:i",strtotime($pctChangeProcessRunTime, strtotime(date('Y-m-d H:i'))));
$pctChangeProcessTimer = getTimer($timerAry,"pctChangeProcess");
//coinSwap
$coinSwapRunTime = "+20 minutes";
//$coinSwapTimer = date("Y-m-d H:i",strtotime($coinSwapRunTime, strtotime(date('Y-m-d H:i'))));
$coinSwapTimer = getTimer($timerAry,"coinSwap");
//CoinAdmin
$coinAdminRunTime = "+24 hours";
$coinAdminTimer = getTimer($timerAry,"coinAdmin");

$completeFlag = False;

Echo "<BR>Starting Program | Complete time: $newTime | CurrentTime: ".date('Y-m-d H:i');
while($completeFlag == False){

  if (date("Y-m-d H:i", time()) >= $allCoinStatusTimer ){
    Echo "<BR> Running AllCoinStatus.php";
    exec ('/usr/bin/php /home/stevenj1979/public_html/Investment-Tracker/Cryptobot/AllCoinStatus.php');
    sleep (30);
    $allCoinStatusTimer = date("Y-m-d H:i",strtotime($allCoinsStatusRunTime, strtotime(date('Y-m-d H:i'))));
    Echo "<BR> Setting Run Time for AllCoinStatus.php : $allCoinStatusTimer | CurrentTime: ".date('Y-m-d H:i');
  }

  if (date("Y-m-d H:i", time()) >= $dashboardTimer ){
    Echo "<BR> Running Dashboard.php";
    exec ('/usr/bin/php /home/stevenj1979/public_html/Investment-Tracker/Cryptobot/Dashboard.php');
    sleep (30);
    $dashboardTimer = date("Y-m-d H:i",strtotime($dashBoardRunTime, strtotime(date('Y-m-d H:i'))));
    Echo "<BR> Setting Run Time for Dashboard.php : $dashboardTimer | CurrentTime: ".date('Y-m-d H:i');
  }

  if (date("Y-m-d H:i", time()) >= $autoUpdatePriceTimer ){
    Echo "<BR> Running AutoUpdatePrice.php";
    exec ('/usr/bin/php /home/stevenj1979/public_html/Investment-Tracker/Cryptobot/AutoUpdatePrice.php');
    sleep (30);
    $autoUpdatePriceTimer = date("Y-m-d H:i",strtotime($autoUpdatePriceRunTime, strtotime(date('Y-m-d H:i'))));
    Echo "<BR> Setting Run Time for AutoUpdatePrice.php : $autoUpdatePriceTimer | CurrentTime: ".date('Y-m-d H:i');
  }

  if (date("Y-m-d H:i", time()) >= $coinHourlyTimer ){
    Echo "<BR> Running CoinHourly.php";
    exec ('/usr/bin/php /home/stevenj1979/public_html/Investment-Tracker/Cryptobot/CoinHourly.php');
    sleep (30);
    $coinHourlyTimer = date("Y-m-d H:i",strtotime($coinHourlyRunTime, strtotime(date('Y-m-d H:i'))));
    Echo "<BR> Setting Run Time for CoinHourly.php : $coinHourlyTimer | CurrentTime: ".date('Y-m-d H:i');
  }

  if (date("Y-m-d H:i", time()) >= $coinModeTimer ){
    Echo "<BR> Running CoinMode.php";
    exec ('/usr/bin/php /home/stevenj1979/public_html/Investment-Tracker/Cryptobot/CoinMode.php');
    sleep (30);
    $coinModeTimer = date("Y-m-d H:i",strtotime($coinModeRunTime, strtotime(date('Y-m-d H:i'))));
    Echo "<BR> Setting Run Time for CoinMode.php : $coinModeTimer | CurrentTime: ".date('Y-m-d H:i');
  }

  if (date("Y-m-d H:i", time()) >= $pctChangeProcessTimer ){
    Echo "<BR> Running PctChangeProcess.php";
    exec ('/usr/bin/php /home/stevenj1979/public_html/Investment-Tracker/Cryptobot/PctChangeProcess.php');
    sleep (30);
    $pctChangeProcessTimer = date("Y-m-d H:i",strtotime($pctChangeProcessRunTime, strtotime(date('Y-m-d H:i'))));
    Echo "<BR> Setting Run Time for PctChangeProcess.php : $pctChangeProcessTimer | CurrentTime: ".date('Y-m-d H:i');
  }

  if (date("Y-m-d H:i", time()) >= $coinSwapTimer ){
    Echo "<BR> Running CoinSwap.php";
    exec ('/usr/bin/php /home/stevenj1979/public_html/Investment-Tracker/Cryptobot/CoinSwap.php');
    sleep (30);
    $coinSwapTimer = date("Y-m-d H:i",strtotime($coinSwapRunTime, strtotime(date('Y-m-d H:i'))));
    Echo "<BR> Setting Run Time for CoinSwap.php : $coinSwapTimer | CurrentTime: ".date('Y-m-d H:i');
  }

  if (date("Y-m-d H:i", time()) >= $coinAdminTimer ){
    Echo "<BR> Running CoinAdmin.php";
    exec ('/usr/bin/php /home/stevenj1979/public_html/Investment-Tracker/Cryptobot/CoinAdmin.php');
    sleep (30);
    $coinAdminTimer = date("Y-m-d H:i",strtotime($coinAdminRunTime, strtotime(date('Y-m-d H:i'))));
    Echo "<BR> Setting Run Time for CoinAdmin.php : $coinAdminTimer | CurrentTime: ".date('Y-m-d H:i');
  }

  if (date("Y-m-d H:i", time()) >= $newTime){ $completeFlag = True;}
}//end While
writeSQLTime("allCoinStatus",$allCoinStatusTimer);
writeSQLTime("dashBoard",$dashboardTimer);
writeSQLTime("autoUpdatePrice",$autoUpdatePriceTimer);
writeSQLTime("coinHourly",$coinHourlyTimer);
writeSQLTime("coinMode",$coinModeTimer);
writeSQLTime("pctChangeProcess",$pctChangeProcessTimer);
writeSQLTime("coinSwap",$coinSwapTimer);
writeSQLTime("coinAdmin",$coinAdminTimer);
?>
</html>
