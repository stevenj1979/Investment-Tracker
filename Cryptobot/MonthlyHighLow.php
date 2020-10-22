<html>
<?php
ini_set('max_execution_time', 900);
require('includes/newConfig.php');
//require '/home/stevenj1979/repositories/Sparkline/autoload.php';
include_once ('/home/stevenj1979/SQLData.php');
$apikey=getAPIKey();
$apisecret=getAPISecret();
$logToFileSetting = getLogToFile();


function getLastMonthCoinPrice($minMax){

  $conn = getHistorySQL(rand(1,4));
  if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error);}
  if ($minMax == "Max"){ $sql = "SELECT `CoinID`,`MonthHighPrice`,`Month`,`Year` FROM `LastMonthHighPrice` "; }
  else{ $sql = "SELECT `CoinID`,`MonthHighPrice`,`Month`,`Year` FROM `LastMonthMinPrice` ";}
  //echo "<BR>".$sql;
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['Price']);
  }
  $conn->close();
return $tempAry;
}

function writePrice($coinID, $price, $month, $year, $isMax){
  if ($isMax == True) { $nTable = "`MonthlyMaxPrices`"; $maxMin = "MaxPrice";}
  else {$nTable = "`MonthlyMinPrices`";$maxMin = "MinPrice";}
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "INSERT INTO $nTable (`CoinID`, `$maxMin`, `Month`, `Year`) VALUES ($coinID,$price,$month ,$year)";
  //print_r("<BR>".$sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

$maxPrices = getLastMonthCoinPrice("Max");
$maxPricesSize = Count($maxPrices);

for ($i=0; $i<$maxPricesSize; $i++){
  writePrice($maxPrices[$i][0],$maxPrices[$i][1],$maxPrices[$i][2],$maxPrices[$i][3],True);
}

$minPrices = getLastMonthCoinPrice("Max");
$minPricesSize = count($minPrices);

for ($j=0; $j<$minPricesSize; $j++){
  writePrice($minPrices[$j][0],$minPrices[$j][1],$minPrices[$j][2],$minPrices[$j][3], False);
}

?>
</html>
