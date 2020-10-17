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
  if ($isMax == True) { $nGroup = "max(`Price`) as Price ";}
  else {$nGroup = "min(`Price`)  as Price ";}
  $conn = getHistorySQL(rand(1,4));
  if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT $nGroup FROM `PriceHistory`
          WHERE `CoinID` = $coinID
          and `PriceDate` > (SELECT DATE_SUB(Max(`PriceDate`), INTERVAL $time2 MINUTE) FROM `PriceHistory`)
          and `PriceDate` < (SELECT DATE_SUB(Max(`PriceDate`), INTERVAL $time1 MINUTE) FROM `PriceHistory`)";
  //echo "<BR>".$sql;
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['Price']);
  }
  $conn->close();
return $tempAry;
}

function writePrice($coinID, $price, $isMax, $nColumn){
  if ($isMax == True) { $nTable = "`ProjectedPriceMax`";}
  else {$nTable = "`ProjectedPriceMin`";}
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "UPDATE $nTable SET $nColumn = $price
          WHERE `CoinID` = $coinID";
  //print_r("<BR>".$sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function getCoins(){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `ID` FROM `Coin` WHERE `BuyCoin` = 1 ";
  //echo "<BR>".$sql;
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['ID']);
  }
  $conn->close();
return $tempAry;
}

$coin = getCoins();
$coinSize = count($coin);
//$tempAry = getPrice(84,0,15,True);
//echo "<br>".$tempAry[0][0];
//writePrice(84,$tempAry[0][0],True);
//$tempAry2 = getPrice(84,0,15,False);
//echo "<br>".$tempAry2[0][0];
//writePrice(84,$tempAry2[0][0],False);
for ($j=0; $j<$coinSize; $j++){
  for ($i=1; $i<7; $i++){
    $lastNum = ($i-1)*15;
    $tempAry = getPrice($coin[$j][0],$lastNum,($i*15),True);
    writePrice($coin[$j][0],$tempAry[0][0],True,"`".$lastNum."Min`");
    $tempAry2 = getPrice($coin[$j][0],$lastNum,($i*15),False);
    writePrice($coin[$j][0],$tempAry2[0][0],False,"`".$lastNum."Min`");

  }
}
?>
</html>
