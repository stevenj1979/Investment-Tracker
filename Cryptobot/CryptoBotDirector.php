

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
$allCoinStatusTimer = date("Y-m-d H:i",strtotime(allCoinsStatusRunTime, strtotime(date('Y-m-d H:i'))));


$completeFlag = False;


while($completeFlag == False){

  if (date("Y-m-d H:i", time()) >= $allCoinStatusTimer ){
    exec ('/usr/bin/php /home/stevenj1979/public_html/Investment-Tracker/Cryptobot/AllCoinStatus.php');
    $allCoinStatusTimer = date("Y-m-d H:i",strtotime($allCoinsStatusRunTime, strtotime(date('Y-m-d H:i'))));
  }

  if (date("Y-m-d H:i", time()) >= $newTime){ $completeFlag = True;}
}//end While

?>
</html>
