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
	join `LastMonthLowPrice` `Lmmp` on `Lmmp`.`CoinID` = `Lmhp`.`CoinID` and `Lmmp`.`Month` = `Lmhp`.`Month` and `Lmmp`.`Year` = `Lmhp`.`Year` ";
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

$maxPrices = getLastMonthCoinPrice();
$maxPricesSize = Count($maxPrices);

for ($i=0; $i<$maxPricesSize; $i++){
  writePrice($maxPrices[$i][0],$maxPrices[$i][1],$maxPrices[$i][2],$maxPrices[$i][3],$maxPrices[$i][4]);
}



?>
</html>
