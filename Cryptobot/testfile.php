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

//$temp = stringsToArray("4,4,4,3,0","0,0,0,0,8","1111,1101,1100,1001,1000","3,3,3,3,3");

//echo "<BR> TEST: ".var_dump($temp);

///usr/bin/uapi VersionControlDeployment create repository_root=/home/stevenj1979/public_html/Investment-Tracker
///usr/bin/uapi VersionControlDeployment create repository_root=/home/stevenj1979/public_html/Investment-Tracker

$coinPriceMatch = getCoinPriceMatchList();
$coinPricePatternList = getCoinPricePattenList();
$coin1HrPatternList = getCoin1HrPattenList();
//set time
//echo var_dump($coinPriceMatch);
//$tempSry = newCoinMarketCapStats("1831,3602,1,1027,52");
//Echo "<BR> ".var_dump($tempSry);
Echo "<BR> newBuywithPattern1 SELL : ".newBuywithPattern("1"."1"."1"."-1",$coinPricePatternList,1,8,1);
Echo "<BR> newBuywithPattern2 BUY : ".newBuywithPattern("-1"."-1"."-1"."1",$coinPricePatternList,1,14,0);

Echo "<BR> CoinMatch Pattern1  SELL : ".coinMatchPattern($coinPricePatternList,8600.00,"BTC",1,1,8,1);
Echo "<BR> CoinMatch Pattern2  BUY : ".coinMatchPattern($coinPricePatternList,8600.00,"BTC",1,1,14,0);



?>
</html>
