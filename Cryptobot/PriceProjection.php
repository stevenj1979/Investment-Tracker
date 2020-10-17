<html>
<?php
ini_set('max_execution_time', 900);
require('includes/newConfig.php');
//require '/home/stevenj1979/repositories/Sparkline/autoload.php';
include_once ('/home/stevenj1979/SQLData.php');
$apikey=getAPIKey();
$apisecret=getAPISecret();
$logToFileSetting = getLogToFile();

function getPrice($coinID, $time1, $time2, $isMax){
  if ($isMax = True) { $nGroup = "max(`Price`) as Price ";}
  else {$nGroup = "min(`Price`)  as Price ";}
  $conn = getHistorySQL(rand(1,4));
  if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT $nGroup FROM `PriceHistory`
          WHERE `CoinID` = $coinID
          and `PriceDate` > (SELECT DATE_SUB(Max(`PriceDate`), INTERVAL $time2 MINUTE) FROM `PriceHistory`)
          and `PriceDate` < (SELECT DATE_SUB(Max(`PriceDate`), INTERVAL $time1 MINUTE) FROM `PriceHistory`)";
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['Price']);
  }
  $conn->close();
return $tempAry;
}

echo "<br>".getPrice(84,0,15,True);
echo "<br>".getPrice(84,0,15,False);

?>
</html>
