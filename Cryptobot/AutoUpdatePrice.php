<html>
<?php
ini_set('max_execution_time', 300);
require('includes/newConfig.php');

$apikey='8363893012e5441a9d667a09cff9d717';
$apisecret='4229026e95454f37af92bff669243f86';



function getCoinPriceStats(){
  $conn = getNewSQL(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `ID`,`Live1HrChange`,`Live24HrChange`,`LiveCoinPrice`,`CoinPricePctChange`,`Price4Trend`,`Price3Trend`,`LastPriceTrend`,`LivePriceTrend`,`BaseCurrency` FROM `CoinStatsView`";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  Echo "<BR>";
  print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['Live1HrChange'],$row['Live24HrChange'],$row['LiveCoinPrice'],$row['CoinPricePctChange'],$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend']
      ,$row['BaseCurrency']
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

function calculateBuyPrice($coinID, $Live1HrChange, $Live24HrChange, $LiveCoinPrice ,$CoinPricePctChange, $Price4Trend, $Price3Trend, $LastPriceTrend, $LivePriceTrend, $baseCurrency){
  Echo "<BR> calculateBuyPrice($coinID, $Live1HrChange, $Live24HrChange, $LiveCoinPrice ,$CoinPricePctChange, $Price4Trend, $Price3Trend, $LastPriceTrend, $LivePriceTrend, $baseCurrency){";
  $finalTrend = getTrend($Price4Trend, $Price3Trend, $LastPriceTrend, $LivePriceTrend);
  Echo "<BR> Final Trend : $finalTrend ";
  if ($finalTrend >0){
    //Trend Upwards
    $finalTrend = $finalTrend / 2;
    Echo "<BR> New Final Trend : $finalTrend ";
    $newSellPrice = (($LiveCoinPrice / 100) * $finalTrend) + $LiveCoinPrice;
    Echo "<BR> newSellPrice $newSellPrice = (($LiveCoinPrice / 100) * $finalTrend) + $LiveCoinPrice;";
    Echo "<BR> updateBuyPrice($coinID,$LiveCoinPrice);";
    updateBuyPrice($coinID,$LiveCoinPrice,$finalTrend);
    Echo "<BR> updateSellPrice($coinID, $newSellPrice);";
    updateSellPrice($coinID, $newSellPrice,$finalTrend);
  }else{
    //Trend Downwards
    $finalTrend = $finalTrend / 2;
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

function updateBuyPrice($coinID, $newBuyPrice,$finalTrend){
  $conn = getNewSQL(rand(1,4));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `CryptoAuto` SET `AutoBuyPrice`= $newBuyPrice, `Trend` = $finalTrend WHERE `CoinID` = $coinID";
  echo "<BR>";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function updateSellPrice($coinID, $newSellPrice,$finalTrend){
  $conn = getNewSQL(rand(1,4));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `CryptoAuto` SET `AutoSellPrice`= $newSellPrice, `Trend` = $finalTrend WHERE `CoinID` = $coinID";
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
date_default_timezone_set('Asia/Dubai');
$date = date("Y-m-d H", time());

$coinStatsAry = getCoinPriceStats();
$coinStatsSize = count($coinStatsAry);

for($x = 0; $x < $coinStatsSize; $x++) {
  calculateBuyPrice($coinStatsAry[$x][0],$coinStatsAry[$x][1],$coinStatsAry[$x][2],$coinStatsAry[$x][3],$coinStatsAry[$x][4],$coinStatsAry[$x][5],$coinStatsAry[$x][6],$coinStatsAry[$x][7],$coinStatsAry[$x][8]
  ,$coinStatsAry[$x][9],$coinStatsAry[$x][10],$coinStatsAry[$x][11]);
  //calculateSellPrice($coinStatsAry[$x][0],$coinStatsAry[$x][1],$coinStatsAry[$x][2],$coinStatsAry[$x][3],$coinStatsAry[$x][4],$coinStatsAry[$x][5],$coinStatsAry[$x][6],$coinStatsAry[$x][7],$coinStatsAry[$x][8]
  //,$coinStatsAry[$x][9],$coinStatsAry[$x][10]);
  Echo "<BR><BR> NEW COIN!!! ------------------------------------------------------------";
}

?>
</html>
