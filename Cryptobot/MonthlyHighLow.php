<html>
<?php
ini_set('max_execution_time', 900);
require('includes/newConfig.php');
//require '/home/stevenj1979/repositories/Sparkline/autoload.php';
include_once ('/home/stevenj1979/SQLData.php');
$apikey=getAPIKey();
$apisecret=getAPISecret();
$logToFileSetting = getLogToFile();


function getLastMonthCoinPrice(){

  $conn = getHistorySQL(rand(1,4));
  if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `Lmhp`.`CoinID`,`Lmhp`.`MonthHighPrice` as MonthHighPrice,`Lmhp`.`Month`,`Lmhp`.`Year`,  `Lmmp`.`MonthLowPrice` as MonthLowPrice
  FROM `LastMonthHighPrice` `Lmhp`
	join `LastMonthLowPrice` `Lmmp` on `Lmmp`.`CoinID` = `Lmhp`.`CoinID` and `Lmmp`.`Month` = `Lmhp`.`Month` and `Lmmp`.`Year` = `Lmhp`.`Year`
  where `Lmhp`.`MonthHighPrice` <> 0 and `Lmmp`.`MonthLowPrice` <> 0  ";
  echo "<BR>".$sql;
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['CoinID'],$row['MonthHighPrice'],$row['Month'],$row['Year'],$row['MonthLowPrice']);
  }
  $conn->close();
return $tempAry;
}

function writePrice($coinID, $price, $month, $year, $minPrice){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call UpdateMonthlyMinMaxPrice($coinID,$minPrice,$price,$month,$year);";
  //print_r("<BR>".$sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function writePctDatatoSQL($coinID, $hr1Price, $hr24Price, $d7Price, $month, $year){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "Call AddMinPctChangeByMonth($coinID, $hr1Price, $hr24Price, $d7Price, $month, $year);";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("writePctDatatoSQL: ".$sql, 'BuyCoin', 0);
}

function getPctChangeFromHistory(){
  $conn = getHistorySQL(rand(1,4));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "SELECT `CoinID`,min(`Hr1Pct`) as Hr1Pct, min(`Hr24Pct`) as Hr24Pct, min(`D7Pct`) as D7Pct, Month(`PriceDate`) as Month,Year(`PriceDate`) as Year  FROM `PriceHistory`
WHERE YEAR(`PriceDate`) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)
AND MONTH(`PriceDate`) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)
and `Hr1Pct` > -200 and `Hr1Pct` < 200
and `Hr24Pct` > -200 and `Hr24Pct` < 200
and `D7Pct` > -200 and `D7Pct` < 200
group by `CoinID`";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  echo "<BR>$sql";
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['CoinID'],$row['Hr1Pct'],$row['Hr24Pct'],$row['D7Pct'],$row['Month'],$row['Year']);
  }
  $conn->close();
  return $tempAry;
}

$maxPrices = getLastMonthCoinPrice();
$maxPricesSize = Count($maxPrices);

for ($i=0; $i<$maxPricesSize; $i++){
  writePrice($maxPrices[$i][0],$maxPrices[$i][1],$maxPrices[$i][2],$maxPrices[$i][3],$maxPrices[$i][4]);
}

// GET Min 1Hr / 24 hr and 7 day pct from History - Write to Live

$pctData = getPctChangeFromHistory();
$pctDataSize = count($pctData);
for ($i=0;$i<$pctDataSize; $i++){
  $coinID = $pctData[$i][0]; $hr1Price = $pctData[$i][1]; $hr24Price = $pctData[$i][2]; $d7Price = $pctData[$i][3];
  $month = $pctData[$i][4]; $year = $pctData[$i][5];
  echo "<BR>writePctDatatoSQL($coinID,$hr1Price,$hr24Price,$d7Price,$month,$year);";
  writePctDatatoSQL($coinID,$hr1Price,$hr24Price,$d7Price,$month,$year);
}

?>
</html>
