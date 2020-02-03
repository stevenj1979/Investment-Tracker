<html>
<?php
ini_set('max_execution_time', 500);
require('includes/newConfig.php');

include_once ('/home/stevenj1979/SQLData.php');
$apikey=getAPIKey();
$apisecret=getAPISecret();
echo "<BR> API Secret: $apisecret";
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

function getUserVariables(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `ID`,`AccountType`,`UserName`,`Active`,`APIKey`,`APISecret`,`EnableDailyBTCLimit`,`EnableTotalBTCLimit`,`DailyBTCLimit`,`TotalBTCLimit` FROM `UserConfigView`  ";
  $result = $conn->query($sql);
  print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['AccountType'],$row['UserName'],$row['Active'],$row['APIKey'],$row['APISecret'],$row['EnableDailyBTCLimit'],$row['EnableTotalBTCLimit'],$row['DailyBTCLimit'],$row['TotalBTCLimit']);
  }
  $conn->close();
  return $tempAry;
}

function findCoinStats($CMCStats, $symbol){
  $tempStats = [];
  $statsLength = count($CMCStats);
  for($y = 0; $y < $statsLength; $y++) {
    //echo "<br> FindCoin=".$CMCStats[$y][0];
    if ($CMCStats[$y][0]== $symbol){
      echo "<br> $statsLength Error Line ".$CMCStats[$y][0].",".$CMCStats[$y][1].",".$CMCStats[$y][2].",".$CMCStats[$y][3].",".$CMCStats[$y][4]."<br>";
      $tempStats[] = Array($CMCStats[$y][0],$CMCStats[$y][1],$CMCStats[$y][2],$CMCStats[$y][3],$CMCStats[$y][4]);
      return $tempStats;
    }
  }
  return $tempStats;
}

//set time
date_default_timezone_set('Asia/Dubai');
$date = date("Y-m-d H:i", time());
$current_date = date('Y-m-d H:i');
//$newTime = date("Y-m-d H:i",strtotime("+5 minutes", strtotime($current_date)));
$newTime = date("Y-m-d H:i",strtotime($tmpTime, strtotime($current_date)));
$i = 0;
$coins = getTrackingCoins();
$coinLength = Count($coins);
echo "<br> coinLength= $coinLength NEWTime=".$newTime." StartTime $date";
//echo "<BR> NewTEST: ".diff($date,$newTime);
$CMCStats = getCoinMarketCapStats();
while($date <= $newTime){
  echo "NEW LOOP ";

  for($x = 0; $x < $coinLength; $x++) {
    //variables
    $coinID = $coins[$x][0]; $symbol = $coins[$x][1]; $baseCurrency = $coins[$x][26];
    //LOG
    echo "<br> i=$i CoinID=$coinID Coin=$symbol baseCurrency=$baseCurrency ";

    //Update Price
    echo "<BR>$bitPrice = number_format((float)(bittrexCoinPrice($apikey,$apisecret,$baseCurrency,$symbol)), 8, '.', '');";
    $bitPrice = number_format((float)(bittrexCoinPrice($apikey,$apisecret,$baseCurrency,$symbol)), 8, '.', '');
    echo "<br> PRICE_UPDATE COIN= $symbol CoinPrice= $bitPrice time ".date("Y-m-d H:i", time());;
    copyCoinPrice($coinID,$bitPrice);
    echo "<br>";
    echo "getCoinMarketCapStats Refresh ";
    if ($i == 0){
      echo "<br> Count=".count($CMCStats);
      $statsForCoin = findCoinStats($CMCStats,$symbol);
      echo "<br> Market Cap ".$statsForCoin[0][1];
      copyNewMarketCap($coinID, $statsForCoin[0][1]);
      copyNewPctChange($coinID, $statsForCoin[0][2], $statsForCoin[0][3], $statsForCoin[0][4]);
      echo "<br> MarketCap=".$statsForCoin[0][1]."PCTChange= ".$statsForCoin[0][2]." ".$statsForCoin[0][3]." ".$statsForCoin[0][4];

      $bittrexStats = bittrexCoinStats($apikey,$apisecret,$symbol,$baseCurrency);
      $coinVolData = getVolumeStats($bittrexStats);
      copyCoinVolume($coinID, $coinVolData[0][0]);
      copyCoinBuyOrders($coinID, $coinVolData[0][1]);
      copyCoinSellOrders($coinID, $coinVolData[0][2]);
      echo "<br> Volume=".$coinVolData[0][0]." BuyOrders=".$coinVolData[0][1]." SellOrders=".$coinVolData[0][2];
    }

    if ($i ==  1){
      copyCoinHistory($coinID);
      copyBuyHistory($coinID);
      copyWebTable($coinID);
      updateWebCoinStatsTable($coinID);
      coinPriceHistory($coinID,$bitPrice,$baseCurrency,date("Y-m-d H:i:s", time()));
      $Hr1Date = date("Y-m-d H",strtotime("-1 Hour"));
      echo "<BR> get1HrChange($coinID,$Hr1Date);";
      $Hr1Price = get1HrChange($coinID,$Hr1Date);
      update1HrPriceChange($Hr1Price[0][0],$coinID);
      $Hr8Date = date("Y-m-d H",strtotime("-1 Day"));
      $Hr8Price = get1HrChange($coinID,$Hr8Date);
      update8HrPriceChange($Hr8Price[0][0],$coinID);
      $Hr24Date = date("Y-m-d H",strtotime("-1 Day"));
      $Hr24Price = get1HrChange($coinID,$Hr24Date);
      update24HrPriceChange($Hr24Price[0][0],$coinID);
    }

    //sleep(1);
  }//loop Coins
  echo "<br> SLEEP START: ".date("Y-m-d H:i:s", time());
  sleep(60);
  //wait(10000000);
  echo "<br> SLEEP END: ".date("Y-m-d H:i:s", time());
  $pauseStart = date("Y-m-d H:i", time());
  $tmpTimeAdd = "+5 seconds";
  $pauseEnd = date("Y-m-d H:i",strtotime($tmpTimeAdd, strtotime($pauseStart)));
  $pauseExit = date("Y-m-d H:i", time());
  //while($pauseExit <= $pauseEnd){
      echo "<BR> Waiting for time... ";
      $pauseExit = date("Y-m-d H:i", time());
  //}

  $i = $i+1;
  $date = date("Y-m-d H:i", time());
}//while loop
echo "EndTime ".date("Y-m-d H:i", time());
//sendEmail('stevenj1979@gmail.com',$i,0,$date,0,'CryptoAuto Loop Finished', 'stevenj1979', 'Coin Purchase <purchase@investment-tracker.net>');
?>
</html>
