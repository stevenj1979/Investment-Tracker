<html>
<?php
ini_set('max_execution_time', 300);
require('includes/newConfig.php');
//set_include_path('/home/stevenj1979/repositories/gdax/src/Configuration.php');
include_once ('/home/stevenj1979/SQLData.php');
include_once ('/home/stevenj1979/Encrypt.php');

$apikey=getAPIKey();
$apisecret=getAPISecret();
//echo "<BR>API Secret is:  $apisecret";
$tmpTime = "+5 seconds";
if (!empty($argv[1])){
  parse_str($argv[1], $params);
  $tmpTime = str_replace('_', ' ', $params['mins']);
  echo $tmpTime;
  //error_log($argv[1], 0);
}
//echo "<BR> isEmpty : ".empty($_GET['mins']);
if (!empty($_GET['mins'])){
  $tmpTime = str_replace('_', ' ', $_GET['mins']);
  echo "<br> GETMINS: ".$_GET['mins'];
}



function getSymbols(){
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `ID`,`Symbol`,`BaseCurrency` FROM `Coin` WHERE `BuyCoin` = 1";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['Symbol'],$row['BaseCurrency']);
  }
  $conn->close();
  return $tempAry;
}

function get1HrPrice($coinID){
  $conn = getHistorySQL(rand(1,4));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "select `PriceHistory`.`CoinID` AS `CoinID`,avg(`PriceHistory`.`Price`) AS `Price` from `PriceHistory`
where ((`PriceHistory`.`PriceDate` < ((select max(`PriceHistory`.`PriceDate`) from `PriceHistory`) - interval 55 minute)) and (`PriceHistory`.`PriceDate` > ((select max(`PriceHistory`.`PriceDate`) from `PriceHistory`) - interval 65 minute))) and `CoinID` = $coinID
group by `PriceHistory`.`CoinID`
order by `PriceHistory`.`CoinID` desc ";

  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['CoinID'],$row['Price']);
  }
  $conn->close();
  return $tempAry;
}

function get24HrPrice($coinID){
  $conn = getHistorySQL(rand(1,4));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "select `PriceHistory`.`CoinID` AS `CoinID`,avg(`PriceHistory`.`Price`) AS `Price`
from `PriceHistory` where ((`PriceHistory`.`PriceDate` < ((select max(`PriceHistory`.`PriceDate`) from `PriceHistory`) - interval 1415 minute)) and (`PriceHistory`.`PriceDate` > ((select max(`PriceHistory`.`PriceDate`) from `PriceHistory`) - interval 1445 minute))) and `CoinID` = $coinID
group by `PriceHistory`.`CoinID` order by `PriceHistory`.`CoinID` desc ";

  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['CoinID'],$row['Price']);
  }
  $conn->close();
  return $tempAry;
}

function get7DPrice($coinID){
  $conn = getHistorySQL(rand(1,4));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "select `PriceHistory`.`CoinID` AS `CoinID`,avg(`PriceHistory`.`Price`) AS `Price` from `PriceHistory`
where ((`PriceHistory`.`PriceDate` < ((select max(`PriceHistory`.`PriceDate`) from `PriceHistory`) - interval 10000 minute)) and (`PriceHistory`.`PriceDate` > ((select max(`PriceHistory`.`PriceDate`) from `PriceHistory`) - interval 10500 minute))) and `CoinID` = $coinID
group by `PriceHistory`.`CoinID` order by `PriceHistory`.`CoinID` desc ";

  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['CoinID'],$row['Price']);
  }
  $conn->close();
  return $tempAry;
}

function writePctPrices($coinID, $price1Hr, $price24Hr, $price7D){
  $conn = getHistorySQL(rand(1,4));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call AddPctPrices($coinID, $price1Hr, $price24Hr, $price7D);";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function getCMCstats($CMCStats, $coinStr){
  if (empty($CMCStats)){
    $CMCStats = newCoinMarketCapStats($coinStr);
  }
  return $CMCStats;
}

function findCoinStats($CMCStats, $symbol){
  echo "<BR> FIND: $symbol";
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


$tmpTime = "+2 minutes";
$date = date("Y-m-d H:i", time());$current_date = date('Y-m-d H:i');
$newTime = date("Y-m-d H:i",strtotime($tmpTime, strtotime($current_date)));
$CMCStats = [];
$coins = getSymbols();
$coinCount = count($coins);
$coinStr = getCoinList(getStats(),3);
Echo "<BR> Symbols Count:$coinCount ";
for ($i=0; $i<$coinCount; $i++){
    //variables
    $coinID = $coins[$i][0];
    //Get Prices from History
    $Hr1Price = get1HrPrice($coinID);
    echo "<BR> get1HrPrice($coinID); ".$Hr1Price[0][1];

    //Check if 0
    if (is_null($Hr1Price[0][1])){
       $CMCStats = getCMCstats($CMCStats, $coinStr);
       $tempPrice = findCoinStats($CMCStats,$coins[$i][1]);
       $price1Hr = $tempPrice[0][2];
    }else{
      $price1Hr = $Hr1Price[0][1];
    }
    $Hr24Price = get24HrPrice($coinID);
    if (is_null($Hr24Price[0][1])){
      $CMCStats = getCMCstats($CMCStats, $coinStr);
      $tempPrice = findCoinStats($CMCStats,$coins[$i][1]);
      $price1Hr = $tempPrice[0][3];
    }else{
      $price24Hr = $Hr24Price[0][1];
    }

    $D7Price = get7DPrice($coinID);
    if (is_null($D7Price[0][1])){
       $CMCStats = getCMCstats($CMCStats, $coinStr);
       $tempPrice = findCoinStats($CMCStats,$coins[$i][1]);
       $price7D = $tempPrice[0][3];
    }else{
      $price7D = $D7Price[0][1];
    }
    //Write to PricePctChangeHistory
    writePctPrices($coinID, $price1Hr, $price24Hr, $price7D);
    echo "<BR> write1HrPrice($coinID, $price1Hr, $price24Hr, $price7D);";
}

?>
</html>
