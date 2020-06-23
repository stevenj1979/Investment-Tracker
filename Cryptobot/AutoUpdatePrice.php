<html>
<?php
ini_set('max_execution_time', 300);
require('includes/newConfig.php');
include_once ('/home/stevenj1979/SQLData.php');
$apikey=getAPIKey();
$apisecret=getAPISecret();

function getCoinPriceStatsSell(){
  $conn = getHistorySQL(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT Min(`Price`) as `Price`,`CoinID` FROM `CountOfCoinPrice` WHERE `Count of Price` > 10 and `Price` <> 0
group by `CoinID`";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  Echo "<BR>";
  print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Price'],$row['CoinID']
    );
  }
  $conn->close();
  return $tempAry;
}

function getCoinPriceStats(){
  $conn = getHistorySQL(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT Max(`Price`) as `Price`,`CoinID` FROM `CountOfCoinPrice` WHERE `Count of Price` > 10 and `Price` <> 0
group by `CoinID`";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  Echo "<BR>";
  print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Price'],$row['CoinID']
    );
  }
  $conn->close();
  return $tempAry;
}

function getTrend($Price4Trend, $Price3Trend, $LastPriceTrend, $LivePriceTrend){
  Echo "<BR> Get Trend: ".$Price3Trend.$LastPriceTrend.$LivePriceTrend;
  $newTrend = $Price4Trend + $Price3Trend + $LastPriceTrend + $LivePriceTrend;
  if ($newTrend == 0){ return 30;}
  //elseif ($newTrend == "") {
  //  // code...
  //}
  else {return $newTrend;}
}

function calculateBuyPrice($coinID, $Live1HrChange, $Live24HrChange, $LiveCoinPrice ,$CoinPricePctChange, $Price4Trend, $Price3Trend, $LastPriceTrend, $LivePriceTrend, $baseCurrency, $coinMultiplier){
  Echo "<BR> calculateBuyPrice($coinID, $Live1HrChange, $Live24HrChange, $LiveCoinPrice ,$CoinPricePctChange, $Price4Trend, $Price3Trend, $LastPriceTrend, $LivePriceTrend, $baseCurrency){";
  $newSellPrice = 0.00;
  $finalTrend = getTrend($Price4Trend, $Price3Trend, $LastPriceTrend, $LivePriceTrend);
  Echo "<BR> Final Trend : $finalTrend ";
  if ($finalTrend >0){
    //Trend Upwards
    $finalTrend = $finalTrend / $coinMultiplier;
    Echo "<BR> New Final Trend : $finalTrend ";
    $newSellPrice = (($LiveCoinPrice / 100) * $finalTrend) + $LiveCoinPrice;
    Echo "<BR> newSellPrice $newSellPrice = (($LiveCoinPrice / 100) * $finalTrend) + $LiveCoinPrice;";
    Echo "<BR> updateBuyPrice($coinID,$LiveCoinPrice);";
    updateBuyPrice($coinID,$LiveCoinPrice,$finalTrend);
    Echo "<BR> updateSellPrice($coinID, $newSellPrice);";
    updateSellPrice($coinID, $newSellPrice,$finalTrend);
  }else{
    //Trend Downwards
    $finalTrend = $finalTrend / $coinMultiplier;
    Echo "<BR> New Final Trend : $finalTrend ";
    $newBuyPrice = (($LiveCoinPrice / 100) * $finalTrend) - $LiveCoinPrice;
    Echo "<BR> newBuyPrice $newBuyPrice = (($LiveCoinPrice / 100) * $finalTrend) - $LiveCoinPrice;";
    Echo "<BR> updateBuyPrice($coinID,$newSellPrice);";
    updateBuyPrice($coinID,$newSellPrice,$finalTrend);
    Echo "<BR> updateBuyPrice($coinID,$LiveCoinPrice);";
    updateSellPrice($coinID, $LiveCoinPrice,$finalTrend);
  }
}

function calculateSellPrice($coinID, $Live1HrChange, $Live24HrChange, $CoinPricePctChange, $Price4Trend, $Price3Trend, $LastPriceTrend, $LivePriceTrend, $baseCurrency, $LiveCoinPrice){

}

function updateBuyPrice($newBuyPrice,$coinID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `CryptoAuto` SET `AutoBuyPrice` = $newBuyPrice WHERE `CoinID` = $coinID";
  echo "<BR>";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function updateSellPrice($newSellPrice,$coinID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `CryptoAuto` SET `AutoSellPrice` = $newSellPrice WHERE `CoinID` = $coinID";
  echo "<BR>";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function getCoinTrend(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `ID`,`Symbol`,`Price4Trend`,`Price3Trend`,`LastPriceTrend`,`LivePriceTrend`,(`Price4Trend`*1 +`Price3Trend`*1 +`LastPriceTrend`*1 +`LivePriceTrend`*1 ) as CoinPriceTrend
  ,`1HrPriceChangeLive`,`1HrPriceChangeLast`,`1HrPriceChange3`,`1HrPriceChange4`, (`1HrPriceChangeLive`*1 +`1HrPriceChangeLast`*1 +`1HrPriceChange3`*1 +`1HrPriceChange4`*1 ) as Hr1Trend FROM `CoinStatsView` ";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  Echo "<BR>";
  print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['Symbol'],$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['CoinPriceTrend']
      ,$row['1HrPriceChangeLive'],$row['1HrPriceChangeLast'],$row['1HrPriceChange3'],$row['1HrPriceChange4'],$row['Hr1Trend']
    );
  }
  $conn->close();
  return $tempAry;
}

function updateCoinTrend($coinID, $priceTrend, $hr1Trend){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `AllCoinStatus` SET `CoinTrendPrice`= $priceTrend,`CoinTrend1Hr`= $hr1Trend WHERE `CoinID` = $coinID";
  echo "<BR>";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

//set time
setTimeZone();
$date = date("Y-m-d H", time());

$coinStatsAry = getCoinPriceStats();
$coinStatsSize = count($coinStatsAry);

for($x = 0; $x < $coinStatsSize; $x++) {
  $newBuyPrice = $coinStatsAry[$x][0]; $coinID = $coinStatsAry[$x][1];
  $buyPricePct = ($newBuyPrice/100)*3;
  $finalBitPrice = $newBuyPrice-$buyPricePct;
  updateBuyPrice($finalBitPrice,$coinID);
  Echo "<BR>Update Buy Price $finalBitPrice , $coinID";
  logAction("Update Buy Price $finalBitPrice , $coinID",'AutoUpdatePrice');
}
$coinStatsSellAry = getCoinPriceStatsSell();
$coinStatsSellSize = count($coinStatsSellAry);
for($x = 0; $x < $coinStatsSellSize; $x++) {
  $newSellPrice = $coinStatsSellAry[$x][0]; $coinID = $coinStatsSellAry[$x][1];
  $sellPricePct = ($newSellPrice/100)*3;
  updateSellPrice($newSellPrice,$coinID);
  Echo "<BR>Update Sell Price $newSellPrice , $coinID";
  logAction("Update Sell Price $newSellPrice , $coinID",'AutoUpdatePrice');
}
$coinTrend = getCoinTrend();
$coinTrendSize = Count($coinTrend);
Echo "<BR> coinTrendSize: $coinTrendSize";
for($x = 0; $x < $coinTrendSize; $x++) {
  $coinID = $coinTrend[$x][0]; $priceTrend = $coinTrend[$x][6]; $hr1Trend = $coinTrend[$x][11];
  Echo "<BR> updateCoinTrend($coinID,$priceTrend,$hr1Trend);";
  updateCoinTrend($coinID,$priceTrend,$hr1Trend);
}

$sellTrackingCoins = getTrackingSellCoins();
$sellTrackingCoinsSize = Count($sellTrackingCoins);
$z = 0;$toMergeAry = []; $finalMergeAry = [][];
for($x = 0; $x < $sellTrackingCoinsSize; $x++) {
  $toMerge = $sellTrackingCoins[$x][44]; $userID = $sellTrackingCoins[$x][3]; $coinID = $sellTrackingCoins[$x][2]; $symbol = $sellTrackingCoins[$x][11];
  $transactionID = $sellTrackingCoins[$x][0]; $amount = $sellTrackingCoins[$x][5]; $cost = $sellTrackingCoins[$x][4];

  if ($toMerge == 1){
    $toMergeAry[0] = Array($userID,$coinID,$symbol,$transactionID,$amount,$cost);
    $finalMergeAry = updateMergeAry($toMergeAry,$finalMergeAry);
  }
}
$finalMergeArySize = Count($finalMergeAry);
for($x = 0; $x < $finalMergeArySize; $x++) {
  $userID = $finalMergeAry[$x][0]; $coinID = $finalMergeAry[$x][1]; $symbol = $finalMergeAry[$x][2]; $transactionID = $finalMergeAry[$x][3];
  $amount = $finalMergeAry[$x][4]; $cost = $finalMergeAry[$x][5]; $lastTransID = $finalMergeAry[$x][6]; $count = $finalMergeAry[$x][7];
  $avCost = $cost/$count;
  mergeTransactions($transactionID, $amount, $avCost, $lastTransID);
}

?>
</html>
