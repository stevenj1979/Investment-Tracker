

<html>
<?php
ini_set('max_execution_time',1200);
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

  $sql = "SELECT  `NextRunTime`, `Name`,`Command`,`LastRunTime`,`MinsToRun`, TimeStampDiff(MINUTE, now(), `NextRunTime`) as MinsRemaining FROM `CryptoBotDirector`";
  //echo "<BR> $sql";
  newLogToSQL("getMultiSellRulesTemplate", "$sql", 3, 0,"SQL CALL","");
    $result = $conn->query($sql);
    //$result = mysqli_query($link4, $query);
    //mysqli_fetch_assoc($result);
    while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['NextRunTime'],$row['Name'],$row['Command'],$row['LastRunTime'],$row['MinsToRun'],$row['MinsRemaining']);
    }
    $conn->close();
    return $tempAry;
}

function writeSQLTime($name, $minsToRun){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `CryptoBotDirector` SET `LastRunTime` = `NextRunTime`, `NextRunTime` = Date_Add(`LastRunTime`, INTERVAL `MinsToRun` MINUTE) WHERE `Name`= '$name' ";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("writeSQLTime: ".$sql, 'SQL_CALL', 0);
  newLogToSQL("writeSQLTime","$sql",3,0,"CryptoBotDirector","Name:$name");
}

function getTimer($timerAry, $name, $start_date){
  $timerArySize = count($timerAry);
  //echo "<BR> ArraySize: $timerArySize";
  for($e=0;$e<$timerArySize;$e++){
    //echo"<BR>Name: $name | ".$timerAry[$e][1];
    if ($timerAry[$e][1] == $name){

      //$nextRunTime =  date( 'Y-m-d H:i:s', strtotime($timerAry[$e][0]) );
      //$currentTime = date('d-m-y h:i:s');
      $minsRemaining = $timerAry[$e][5];
      $since_start = $start_date->diff(new DateTime('now'));
      $minutes = $since_start->days * 24 * 60;
      $minutes += $since_start->h * 60;
      $minutes += $since_start->i;
      //$revMinsRemaining = round(($currentTime-$nextRunTime)/60,2);
      echo "<BR>Found $name | MINS:$minsRemaining | ProgramRunMins:$minutes";
      return Array($minsRemaining,$minutes);
    }
  }
}
$timerAry = getTimeFromSQL();
//Program
//$programRunTime = "+30 minutes";
$newTime = date("Y-m-d H:i",strtotime($programRunTime, strtotime(date('Y-m-d H:i'))));
//AllCoinStatus
//$allCoinsStatusRunTime = "+20 minutes";
//$allCoinStatusTimer = date("Y-m-d H:i",strtotime($allCoinsStatusRunTime, strtotime(date('Y-m-d H:i'))));
$currentTime = new DateTime('now');
$completeFlag = False;
//$allCoinStatusTimerMins = getTimer($timerAry,"allCoinStatus");
Echo "<BR>Starting Program | Complete time: $newTime | CurrentTime: ".date('Y-m-d H:i');
while($completeFlag == False){

  $allCoinStatusTimerAry = getTimer($timerAry,"allCoinStatus",$currentTime);
  $allCoinStatusTimerMins = $allCoinStatusTimerAry[0];
  $minsFromStart = $allCoinStatusTimerAry[1];
  echo "<BR> AllCoin: $allCoinStatusTimerMins | $minsFromStart";
  if ($allCoinStatusTimerMins >= $minsFromStart OR $allCoinStatusTimerMins < 0){
    Echo "<BR> Running AllCoinStatus.php";
    exec ('/usr/bin/php /home/stevenj1979/public_html/Investment-Tracker/Cryptobot/AllCoinStatus.php');
    //$allCoinStatusTimer = date("Y-m-d H:i",strtotime($allCoinsStatusRunTime, strtotime(date('Y-m-d H:i'))));
    Echo "<BR> Setting Run Time for AllCoinStatus.php : $allCoinStatusTimerMins | CurrentTime: ".date('Y-m-d H:i');
    writeSQLTime("allCoinStatus",$allCoinStatusTimerMins);
    sleep (15);
  }else{
    echo "<BR> Waiting Timer!!!";
  }

  $dashboardTimerAry = getTimer($timerAry,"dashBoard",$currentTime);
  $dashboardTimerMins = $dashboardTimerAry[0];
  $minsFromStart = $dashboardTimerAry[1];
  if ($dashboardTimerMins >= $minsFromStart OR $dashboardTimerMins < 0){
    Echo "<BR> Running Dashboard.php";
    exec ('/usr/bin/php /home/stevenj1979/public_html/Investment-Tracker/Cryptobot/Dashboard.php');
    //sleep (15);
    //$dashboardTimer = date("Y-m-d H:i",strtotime($dashBoardRunTime, strtotime(date('Y-m-d H:i'))));
    Echo "<BR> Setting Run Time for Dashboard.php : $dashboardTimerMins | CurrentTime: ".date('Y-m-d H:i');
    writeSQLTime("dashBoard",$dashboardTimerMins);
    sleep (15);
  }else{
    echo "<BR> Waiting Timer!!!";
  }

  $autoUpdatePriceTimerAry = getTimer($timerAry,"autoUpdatePrice",$currentTime);
  $autoUpdatePriceTimerMins = $autoUpdatePriceTimerAry[0];
  $minsFromStart = $autoUpdatePriceTimerAry[1];
  if ($autoUpdatePriceTimerMins >= $minsFromStart OR $autoUpdatePriceTimerMins < 0){
    Echo "<BR> Running AutoUpdatePrice.php";
    exec ('/usr/bin/php /home/stevenj1979/public_html/Investment-Tracker/Cryptobot/AutoUpdatePrice.php');
    //sleep (15);
    //$autoUpdatePriceTimer = date("Y-m-d H:i",strtotime($autoUpdatePriceRunTime, strtotime(date('Y-m-d H:i'))));
    Echo "<BR> Setting Run Time for AutoUpdatePrice.php : $autoUpdatePriceTimerMins | CurrentTime: ".date('Y-m-d H:i');
    writeSQLTime("autoUpdatePrice",$autoUpdatePriceTimerMins);
    sleep (15);
  }else{
    echo "<BR> Waiting Timer!!!";
  }

  $coinHourlyTimerAry = getTimer($timerAry,"coinHourly",$currentTime);
  $coinHourlyTimerMins = $coinHourlyTimerAry[0];
  $minsFromStart = $coinHourlyTimerAry[1];
  if ($coinHourlyTimerMins >= $minsFromStart OR $coinHourlyTimerMins < 0){
    Echo "<BR> Running CoinHourly.php";
    exec ('/usr/bin/php /home/stevenj1979/public_html/Investment-Tracker/Cryptobot/CoinHourly.php');
    //sleep (15);
    //$coinHourlyTimer = date("Y-m-d H:i",strtotime($coinHourlyRunTime, strtotime(date('Y-m-d H:i'))));
    Echo "<BR> Setting Run Time for CoinHourly.php : $coinHourlyTimerMins | CurrentTime: ".date('Y-m-d H:i');
    writeSQLTime("coinHourly",$coinHourlyTimerMins);
    sleep (15);
  }else{
    echo "<BR> Waiting Timer!!!";
  }

  $coinModeTimerAry = getTimer($timerAry,"coinMode",$currentTime);
  $coinModeTimerMins = $coinModeTimerAry[0];
  $minsFromStart = $coinModeTimerAry[1];
  if ($coinModeTimerMins >= $minsFromStart OR $coinModeTimerMins < 0){
    Echo "<BR> Running CoinMode.php";
    exec ('/usr/bin/php /home/stevenj1979/public_html/Investment-Tracker/Cryptobot/CoinMode.php');
    //sleep (15);
    //$coinModeTimer = date("Y-m-d H:i",strtotime($coinModeRunTime, strtotime(date('Y-m-d H:i'))));
    Echo "<BR> Setting Run Time for CoinMode.php : $coinModeTimerMins | CurrentTime: ".date('Y-m-d H:i');
    writeSQLTime("coinMode",$coinModeTimerMins);
    sleep (15);
  }else{
    echo "<BR> Waiting Timer!!!";
  }

  $pctChangeProcessTimerAry = getTimer($timerAry,"pctChangeProcess",$currentTime);
  $pctChangeProcessTimerMins = $pctChangeProcessTimerAry[0];
  $minsFromStart = $pctChangeProcessTimerAry[1];
  if ($pctChangeProcessTimerMins >= $minsFromStart OR  $pctChangeProcessTimerMins < 0){
    Echo "<BR> Running PctChangeProcess.php";
    exec ('/usr/bin/php /home/stevenj1979/public_html/Investment-Tracker/Cryptobot/PctChangeProcess.php');
    //sleep (15);
    //$pctChangeProcessTimer = date("Y-m-d H:i",strtotime($pctChangeProcessRunTime, strtotime(date('Y-m-d H:i'))));
    Echo "<BR> Setting Run Time for PctChangeProcess.php : $pctChangeProcessTimerMins | CurrentTime: ".date('Y-m-d H:i');
    writeSQLTime("pctChangeProcess",$pctChangeProcessTimerMins);
    sleep (15);
  }else{
    echo "<BR> Waiting Timer!!!";
  }

  $coinSwapTimerAry = getTimer($timerAry,"coinSwap",$currentTime);
  $coinSwapTimerMins = $coinSwapTimerAry[0];
  $minsFromStart = $coinSwapTimerAry[1];
  if ($coinSwapTimerMins >= $minsFromStart OR $coinSwapTimerMins < 0){
    Echo "<BR> Running CoinSwap.php";
    exec ('/usr/bin/php /home/stevenj1979/public_html/Investment-Tracker/Cryptobot/CoinSwap.php');
    //sleep (15);
    //$coinSwapTimer = date("Y-m-d H:i",strtotime($coinSwapRunTime, strtotime(date($coinSwapTimer))));
    Echo "<BR> Setting Run Time for CoinSwap.php : $coinSwapTimerMins | Mins: $coinSwapRunTimeMins";
    writeSQLTime("coinSwap",$coinSwapTimerMins);
    sleep (15);
  }else{
    echo "<BR> Waiting Timer!!!";
  }

  $coinAdminTimerAry = getTimer($timerAry,"coinAdmin",$currentTime);
  $coinAdminTimerMins = $coinAdminTimerAry[0];
  $minsFromStart = $coinAdminTimerAry[1];
  if ($coinAdminTimerMins >= $minsFromStart OR $coinAdminTimerMins < 0){
    Echo "<BR> Running CoinAdmin.php";
    exec ('/usr/bin/php /home/stevenj1979/public_html/Investment-Tracker/Cryptobot/CoinAdmin.php');
    //sleep (15);
    //$coinAdminTimer = date("Y-m-d H:i",strtotime($coinAdminRunTime, strtotime(date('Y-m-d H:i'))));
    Echo "<BR> Setting Run Time for CoinAdmin.php : $coinAdminTimerMins | CurrentTime: ".date('Y-m-d H:i');
    writeSQLTime("coinAdmin",$coinAdminTimerMins);
    sleep (15);
  }else{
    echo "<BR> Waiting Timer!!!";
  }

  if (date("Y-m-d H:i", time()) >= $newTime){ $completeFlag = True;}
}//end While

?>
</html>
