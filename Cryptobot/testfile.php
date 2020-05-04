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
//echo "<BR> 1: ".isCoinMatch(240, "BCH", 239.00, "BTC", 1);
//echo "<BR> 2: ".isCoinMatch(7100, "BTC", 6956, "BTC", 0);
//echo "<BR> 3: ".isCoinMatch(7000, "BTC", 7001, "BTC", 1);
//echo "<BR> 3: ".isCoinMatch(7000, "BTC", 7001, "BTC", 0);
//echo "<BR> 1: ".coinMatchPattern("BTC:7000,ETH:140,BCH:240", 7001.00, "BTC", 1,1);
//echo "<BR> 2: ".coinMatchPattern("BTC:7000,ETH:140,BCH:240", 239.00, "BCH", 1,1);
//echo "<BR> 3: ".coinMatchPattern("BTC:7000,ETH:140,BCH:240", 241.00, "BCH", 1,1);
//echo "<BR> 4: ".coinMatchPattern("BTC:6500,ETH:140,BCH:240", 239.00, "BCH", 0,1);
//echo "<BR> 5: ".coinMatchPattern("BTC:6500,ETH:140,BCH:240", 239.00, "BCH", 0,1);
//BuyCoins('714f3f7873a2481c9f89b7c1f3801f2d', '2377fc19e47b4c7fb9dd32a70edd3b9f','BTC', 'stevenj1979@gmail.com', 3, '2020-04-26 20:27:57', 'USDT',
//1,0,75.00000000, 29,'stevenj1979',84,1.50,0,1,90,11);


//function buyCoins($apikey, $apisecret, $coin, $email, $userID, $date,$baseCurrency,
//$sendEmail, $buyCoin, $btcBuyAmount, $ruleID,$userName, $coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed, $buyPriceCoin){
//$newTemp = getCMCID("BTC,ETH,BCH");
//echo "<BR> ".$newTemp;
//$temp = newCoinMarketCapStats("BTC,ETH,BCH,XRP");
//$tempCount = count($temp);
//echo "<br>HERE! ".$temp['data'][1][1]['quote'][1]['market_cap'];
//echo "<br>HERE5! ".$temp['data'][1]['quote']['USD']['market_cap'];
//print_r($temp);

//$newTemp = findCoinStats($temp, "XRP");
//echo "<BR> newTemp ".$newTemp[0][0]." ; ".$newTemp[0][1]." ; ".$newTemp[0][2]." ; ".$newTemp[0][3]." ; ".$newTemp[0][4];
//echo "<BR> Return String 1 : ".removeWildcard("-1-1-11,1-1-11");
//echo "<BR> Return String 2 : ".removeWildcard("*111,*-101");
echo "<BR> Return String 2 : ".replaceStars("*111,*111,*111,",1);
//echo "<BR> Return String 2 : ".replaceStars("*1-11",1);
//echo "<BR> Return String 3 : ".removeWildcard("*-1-11,*0-11");
//echo "<BR> Return String 4 : ".removeWildcard("**-11,**11");

?>
</html>
