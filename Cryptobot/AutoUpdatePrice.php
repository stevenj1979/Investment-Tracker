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
?>
</html>
