<html>
<?php
ini_set('max_execution_time', 300);
require('includes/newConfig.php');

include_once ('/home/stevenj1979/SQLData.php');
include_once ('/home/stevenj1979/Encrypt.php');

//$apikey=getAPIKey();
$apisecret=getAPISecret();
//echo "<BR>API Secret is:  $apisecret";
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

function timerReady($start, $seconds){
  $newDate = date("Y-m-d H:i",strtotime("+".$seconds." seconds", strtotime($start)));
  $current_date = date('Y-m-d H:i', time());
  echo "<BR> NewDate $newDate :: current date $current_date";
  if ($newDate <= $current_date){return true;}else{return false;}

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

function SQLCommand(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "Show Create function AddBittrexSell;";
  $conn->query("SET time_zone = '+04:00';");
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  $i = 0;
  while ($row = mysqli_fetch_assoc($result)){
    Echo $row[$i];
    $i = $i + 1;
  }
  $conn->close();
  return $tempAry;

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


//SQLCommand();
//buyCoins('714f3f7873a2481c9f89b7c1f3801f2d', '2377fc19e47b4c7fb9dd32a70edd3b9f','BTC', 'stevenj1979@gmail.com', 3, '2020-03-21 12:54:14', 'USDT',1,1,0.10000000, 26,'stevenj1979',84,1.5,0,1,90,8);

//buyCoins('714f3f7873a2481c9f89b7c1f3801f2d', '2377fc19e47b4c7fb9dd32a70edd3b9f','BTC', 'stevenj1979@gmail.com', 3, '2020-03-21 16:16:41', 'USDT',1,1,0.00000000, 26,'stevenj1979',84,1.50,0,1,90,8);
//$BTCBalance = bittrexbalance('714f3f7873a2481c9f89b7c1f3801f2d', '2377fc19e47b4c7fb9dd32a70edd3b9f','USDT');
//echo "<BR> Balance : $BTCBalance ";
//714f3f7873a2481c9f89b7c1f3801f2d, 2377fc19e47b4c7fb9dd32a70edd3b9f,'BTC', 'stevenj1979@gmail.com', 3, '2020-03-21 16:16:41', 'USDT',1,1,0.10000000, 26,'stevenj1979',84,1.50,0,1,90,8


echo "<BR> 1. ".returnBuyAmount('BTC', 'USDT', 0.0, 1, 184.66010708, 6174.68721687,'714f3f7873a2481c9f89b7c1f3801f2d', '2377fc19e47b4c7fb9dd32a70edd3b9f');
//echo "<BR> 2. ".returnBuyAmount('BTC', 'USDT', 50, 1, 0.20, 6166,'714f3f7873a2481c9f89b7c1f3801f2d', '2377fc19e47b4c7fb9dd32a70edd3b9f');
//echo "<BR> 3. ".returnBuyAmount('ETH', 'USDT', 73, 0, 3, 128,'714f3f7873a2481c9f89b7c1f3801f2d', '2377fc19e47b4c7fb9dd32a70edd3b9f');
//echo "<BR> 4. ".returnBuyAmount('ETH', 'USDT', 50, 1, 3, 128,'714f3f7873a2481c9f89b7c1f3801f2d', '2377fc19e47b4c7fb9dd32a70edd3b9f');

//echo "<BR> 5. ".returnBuyAmount('BCH', 'USDT', 73, 0, 3, 212,'714f3f7873a2481c9f89b7c1f3801f2d', '2377fc19e47b4c7fb9dd32a70edd3b9f');
//echo "<BR> 6. ".returnBuyAmount('BCH', 'USDT', 50, 1, 3, 212,'714f3f7873a2481c9f89b7c1f3801f2d', '2377fc19e47b4c7fb9dd32a70edd3b9f');




?>
</html>
