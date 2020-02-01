<html>
<?php
ini_set('max_execution_time', 300);
require('includes/newConfig.php');

include_once ('/home/stevenj1979/SQLData.php');
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


$symbol = "USDT";
$baseCurrency = "BTC";
$balance = bittrexbalance($apikey,$apisecret,$baseCurrency);
echo "<BR> BALANCE: $balance";
$bitPrice = number_format((float)(bittrexCoinPrice($apikey,$apisecret,$baseCurrency,$symbol)), 8, '.', '');
echo "<BR> $symbol : $baseCurrency : $bitPrice";

//-1,-1,0,-1,
//0,-1,-1,1,
//1

echo "<BR> Return Pattern: ".returnPattern(-1,-1,1,-1,-1,1);

echo "<BR> Buy With Min: ".buyWithMin(1,8500.000, 8300.0000);

echo "<BR> AutoBuy: ".autoBuy(8500.000, 8300.0000,1);

Echo "<BR> THIS IS A TEST! ";

Echo "<BR> THIS IS A NEW TEST! ";

//buyCoins('8363893012e5441a9d667a09cff9d717', '4229026e95454f37af92bff669243f86','BTC', 'stevenj1979@gmail.com', 3, '2020-01-25 20:31:17', 'USDT',1,1,0.00000000, 22,'stevenj1979',84,1.50,0,1,90);

//782.94515487 USDT BALANCE
//8368.73560680 BTC Price
//2.192246 Charge

echo "<BR> newBuywithPattern : ".newBuywithPattern('-1-1-11','-100-1,000-1,-1-1-11',1);
//echo "<BR> newReturnPattern : ".newReturnPattern('1-1-11','1-1-11');
//echo "<BR> newReturnPattern : ".newReturnPattern('1-111','1-1-11');
//echo "<BR> newReturnPattern : ".newReturnPattern('10-11','1-1-11');

?>
</html>
