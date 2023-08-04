<html>
<?php
ini_set('max_execution_time', 900);
require('includes/newConfig.php');
//require '/home/stevenj1979/repositories/Sparkline/autoload.php';
include_once ('/home/stevenj1979/SQLData.php');
include_once ('includes/SQLDbCommands.php');
$apikey=getAPIKey();
$apisecret=getAPISecret();
$logToFileSetting = getLogToFile();


function getLastMonthCoinPrice(){
  $tempAry = [];
  /*$conn = getHistorySQL(rand(1,4));
  if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error);}*/
  $sql = "SELECT `Lmhp`.`CoinID`,`Lmhp`.`MonthHighPrice` as MonthHighPrice,`Lmhp`.`Month`,`Lmhp`.`Year`,  `Lmmp`.`MonthLowPrice` as MonthLowPrice
  FROM `LastMonthHighPrice` `Lmhp`
	join `LastMonthLowPrice` `Lmmp` on `Lmmp`.`CoinID` = `Lmhp`.`CoinID` and `Lmmp`.`Month` = `Lmhp`.`Month` and `Lmmp`.`Year` = `Lmhp`.`Year`
  where `Lmhp`.`MonthHighPrice` <> 0 and `Lmmp`.`MonthLowPrice` <> 0  ";
  //echo "<BR>".$sql;
  $tempAry = mySQLSelect("getLastMonthCoinPrice: ",$sql,3,1,1,1,"MonthlyHighLow", 90);
  /*$result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['CoinID'],$row['MonthHighPrice'],$row['Month'],$row['Year'],$row['MonthLowPrice']);
  }
  $conn->close();*/
return $tempAry;
}

function writePrice($coinID, $price, $month, $year, $minPrice){
  /*$conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}*/
  $sql = "call UpdateMonthlyMinMaxPrice($coinID,$minPrice,$price,$month,$year);";
  SQLInsertUpdateCall("writePrice: ",$sql,3, 1, 1, 0, "MonthlyHighLow", 90);
  /*print_r("<BR>".$sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();*/
}

function writePctDatatoSQL($coinID, $hr1Price, $hr24Price, $d7Price, $month, $year){
  /*$conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }*/
  $sql = "Call AddMinPctChangeByMonth($coinID, $hr1Price, $hr24Price, $d7Price, $month, $year);";
  SQLInsertUpdateCall("writePctDatatoSQL: ",$sql,3, 1, 1, 0, "MonthlyHighLow", 90);
  /*print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("writePctDatatoSQL: ".$sql, 'BuyCoin', 0);*/
}

function getPctChangeFromHistory(){
  $tempAry = [];
  /*$conn = getHistorySQL(rand(1,4));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }*/
  $sql = "SELECT `CoinID`,min(`Hr1Pct`) as Hr1Pct, min(`Hr24Pct`) as Hr24Pct, min(`D7Pct`) as D7Pct, Month(`PriceDate`) as Month,Year(`PriceDate`) as Year  FROM `PriceHistory`
          WHERE YEAR(`PriceDate`) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)
          AND MONTH(`PriceDate`) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)
          and `Hr1Pct` > -20 and `Hr1Pct` < 20
          and `Hr24Pct` > -30 and `Hr24Pct` < 30
          and `D7Pct` > -50 and `D7Pct` < 50
          group by `CoinID`";
  $tempAry = mySQLSelect("getPctChangeFromHistory: ",$sql,3,1,1,1,"MonthlyHighLow", 90);
  /*$result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  echo "<BR>$sql";
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['CoinID'],$row['Hr1Pct'],$row['Hr24Pct'],$row['D7Pct'],$row['Month'],$row['Year']);
  }
  $conn->close();*/
  return $tempAry;
}

function writeAvgPctDatatoSQL($coinID, $hr1Price, $hr24Price, $d7Price, $month, $year){
  /*$conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }*/
  $sql = "Call AddAvgMinPctChangeByMonth($coinID, $hr1Price, $hr24Price, $d7Price, $month, $year);";
  SQLInsertUpdateCall("writeAvgPctDatatoSQL: ",$sql,3, 1, 1, 0, "MonthlyHighLow", 90);
  /*print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("writeAvgPctDatatoSQL: ".$sql, 'BuyCoin', 0);*/
}

function getAvgPctChangeFromHistory(){
  $tempAry = [];
  /*$conn = getHistorySQL(rand(1,4));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }*/
  $sql = "SELECT `CoinID`,avg(`Hr1Pct`) as Hr1Pct, avg(`Hr24Pct`) as Hr24Pct, avg(`D7Pct`) as D7Pct, Month(`PriceDate`) as Month,Year(`PriceDate`) as Year  FROM `PriceHistory`
            WHERE YEAR(`PriceDate`) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)
            AND MONTH(`PriceDate`) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)
            and `Hr1Pct` > -20 and `Hr1Pct` < 0
            and `Hr24Pct` > -30 and `Hr24Pct` < 0
            and `D7Pct` > -50 and `D7Pct` < 0
            group by `CoinID`";
  $tempAry = mySQLSelect("getAvgPctChangeFromHistory: ",$sql,3,1,1,1,"MonthlyHighLow", 90);
  /*$result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  echo "<BR>$sql";
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['CoinID'],$row['Hr1Pct'],$row['Hr24Pct'],$row['D7Pct'],$row['Month'],$row['Year']);
  }
  $conn->close();*/
  return $tempAry;
}

function writeMinPriceDatatoSQL($coinID, $hr1Price, $hr24Price, $d7Price, $month, $year){
  /*$conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }*/
  $sql = "Call AddMinPriceChangeByMonth($coinID, $hr1Price, $hr24Price, $d7Price, $month, $year);";
  SQLInsertUpdateCall("writeMinPriceDatatoSQL: ",$sql,3, 1, 1, 0, "MonthlyHighLow", 90);
  /*print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("writeAvgPctDatatoSQL: ".$sql, 'BuyCoin', 0);*/
}

function getMinPriceChangeFromHistory(){
  $tempAry = [];
  /*$conn = getHistorySQL(rand(1,4));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }*/
  $sql = "SELECT `CoinID`,`Hr1Pct` as Hr1Pct, `Hr24Pct` as Hr24Pct, `D7Pct` as D7Pct, Month(`PriceDate`) as Month,Year(`PriceDate`) as Year, Min(`Price`) as Price FROM `PriceHistory`
            WHERE YEAR(`PriceDate`) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH)
            AND MONTH(`PriceDate`) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)
            and `Price` > 0
            and `Price` < 999999
            and `Hr1Pct` <> 0
            and `Hr24Pct` <> 0
            and `D7Pct` <> 0
            group by `CoinID`";
  $tempAry = mySQLSelect("getMinPriceChangeFromHistory: ",$sql,3,1,1,1,"MonthlyHighLow", 90);
  /*$result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  echo "<BR>$sql";
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['CoinID'],$row['Hr1Pct'],$row['Hr24Pct'],$row['D7Pct'],$row['Month'],$row['Year'],$row['Price']);
  }
  $conn->close();*/
  return $tempAry;
}

$maxPrices = getLastMonthCoinPrice();
$maxPricesSize = newCount($maxPrices);

for ($i=0; $i<$maxPricesSize; $i++){
  writePrice($maxPrices[$i][0],$maxPrices[$i][1],$maxPrices[$i][2],$maxPrices[$i][3],$maxPrices[$i][4]);
}

// GET Min 1Hr / 24 hr and 7 day pct from History - Write to Live

$pctData = getPctChangeFromHistory();
$pctDataSize = newCount($pctData);
for ($i=0;$i<$pctDataSize; $i++){
  $coinID = $pctData[$i][0]; $hr1Price = $pctData[$i][1]; $hr24Price = $pctData[$i][2]; $d7Price = $pctData[$i][3];
  $month = $pctData[$i][4]; $year = $pctData[$i][5];
  echo "<BR>writePctDatatoSQL($coinID,$hr1Price,$hr24Price,$d7Price,$month,$year);";
  writePctDatatoSQL($coinID,$hr1Price,$hr24Price,$d7Price,$month,$year);
}

$pctAvgData = getAvgPctChangeFromHistory();
$pctAvgDataSize = newCount($pctAvgData);
for ($i=0;$i<$pctAvgDataSize; $i++){
  $coinID = $pctAvgData[$i][0]; $hr1Price = $pctAvgData[$i][1]; $hr24Price = $pctAvgData[$i][2]; $d7Price = $pctAvgData[$i][3];
  $month = $pctAvgData[$i][4]; $year = $pctAvgData[$i][5];
  echo "<BR>writePctDatatoSQL($coinID,$hr1Price,$hr24Price,$d7Price,$month,$year);";
  writeAvgPctDatatoSQL($coinID,$hr1Price,$hr24Price,$d7Price,$month,$year);
}

$minPriceData = getMinPriceChangeFromHistory();
$minPriceDataSize = newCount($minPriceData);
for ($i=0;$i<$minPriceDataSize; $i++){
  $coinID = $minPriceData[$i][0]; $hr1Price = $minPriceData[$i][1]; $hr24Price = $minPriceData[$i][2]; $d7Price = $minPriceData[$i][3];
  $month = $minPriceData[$i][4]; $year = $minPriceData[$i][5];
  echo "<BR>writePctDatatoSQL($coinID,$hr1Price,$hr24Price,$d7Price,$month,$year);";
  writeMinPriceDatatoSQL($coinID,$hr1Price,$hr24Price,$d7Price,$month,$year);
}


?>
</html>
