

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
//Program
//$programRunTime = "+30 minutes";
$newTime = date("Y-m-d H:i",strtotime($programRunTime, strtotime(date('Y-m-d H:i'))));
//AllCoinStatus
$allCoinsStatusRunTime = "+20 minutes";
$allCoinStatusTimer = date("Y-m-d H:i",strtotime($allCoinsStatusRunTime, strtotime(date('Y-m-d H:i'))));
//Dashboard
$dashBoardRunTime = "+20 minutes";
$dashboardTimer = date("Y-m-d H:i",strtotime($dashBoardRunTime, strtotime(date('Y-m-d H:i'))));
//AutoUpdatePrice
$autoUpdatePriceRunTime = "+20 minutes";
$autoUpdatePriceTimer = date("Y-m-d H:i",strtotime($autoUpdatePriceRunTime, strtotime(date('Y-m-d H:i'))));
//Hourly
$coinHourlyRunTime = "+60 minutes";
$coinHourlyTimer = date("Y-m-d H:i",strtotime($coinHourlyRunTime, strtotime(date('Y-m-d H:i'))));
//CoinMode
$coinModeRunTime = "+30 minutes";
$coinModeTimer = date("Y-m-d H:i",strtotime($coinModeRunTime, strtotime(date('Y-m-d H:i'))));
//PctChangeProcess
$pctChangeProcessRunTime = "+20 minutes";
$pctChangeProcessTimer = date("Y-m-d H:i",strtotime($pctChangeProcessRunTime, strtotime(date('Y-m-d H:i'))));

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

  if (date("Y-m-d H:i", time()) >= $newTime){ $completeFlag = True;}
}//end While

?>
</html>
