<html>
<?php
ini_set('max_execution_time', 300);
require('includes/newConfig.php');

include_once ('/home/stevenj1979/SQLData.php');
include_once ('/home/stevenj1979/Encrypt.php');

$apikey=getAPIKeyread();
$apisecret=getAPISecretRead();

$tmpTime = "+5 seconds";
if (!empty($argv[1])){
  parse_str($argv[1], $params);
  $tmpTime = str_replace('_', ' ', $params['mins']);
  echo $tmpTime;
  //error_log($argv[1], 0);
}
//echo "<BR> isEmpty : ".empty($_GET['mins']);
if (!empty($_GET['mins'])){
  $tmpTime = str_replace('_', ' ', $_GET['mins']);
  echo "<br> GETMINS: ".$_GET['mins'];
}

function findCoinStats($CMCStats, $symbol){
  $statsLength = count($CMCStats);
  for($y = 0; $y < $statsLength; $y++) {
    echo "<br> FindCoin=".$CMCStats[$y][0];
    if ($CMCStats[$y][0]== $symbol){
      //echo "<br> $statsLength Error Line ".$CMCStats[$y][0].",".$CMCStats[$y][1].",".$CMCStats[$y][2].",".$CMCStats[$y][3].",".$CMCStats[$y][4]."<br>";
      $tempStats[] = Array($CMCStats[$y][0],$CMCStats[$y][1],$CMCStats[$y][2],$CMCStats[$y][3],$CMCStats[$y][4]);
      return $tempStats;
    }
  }
  return $tempStats;
}


///usr/bin/uapi VersionControlDeployment create repository_root=/home/stevenj1979/public_html/Investment-Tracker
///usr/bin/uapi VersionControlDeployment create repository_root=/home/stevenj1979/public_html/Investment-Tracker


//set time
date_default_timezone_set('Asia/Dubai');
$date = date("Y-m-d H", time());
//echo "<BR> Date1: $date";
//$date2 = date("Y-m-d H:", time());
//echo "<BR> Date1: $date2";
$current_date = date('Y-m-d H:i');




//encrypt('This is a test!');
phpinfo();

?>
</html>
