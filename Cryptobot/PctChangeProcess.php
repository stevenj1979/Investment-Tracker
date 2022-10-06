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

function getPrice($coinID, $t1, $t2){
  $conn = getHistorySQL(rand(1,4));
  $tempAry = [];
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  //$sql2 = "select `PriceHistory`.`CoinID` AS `CoinID`,avg(`PriceHistory`.`Price`) AS `Price` from `PriceHistory`
//where ((`PriceHistory`.`PriceDate` < ((select max(`PriceHistory`.`PriceDate`) from `PriceHistory`) - interval $t1 minute)) and (`PriceHistory`.`PriceDate` > ((select max(`PriceHistory`.`PriceDate`) from `PriceHistory`) - interval $t2 minute))) and `CoinID` = $coinID
//group by `PriceHistory`.`CoinID`
//order by `PriceHistory`.`CoinID` desc ";

  $sql = "select `PriceHistory`.`CoinID` AS `CoinID`,avg(`PriceHistory`.`Price`) AS `Price` from `PriceHistory`
          where `PriceHistory`.`PriceDateTimeID` in (SELECT `ID` FROM `PriceHistoryDate`
          WHERE `PriceDateTime` < ((select max(`PriceHistoryDate`.`PriceDateTime`) from `PriceHistoryDate`) - interval $t1 minute)
          and  `PriceDateTime` > ((select max(`PriceHistoryDate`.`PriceDateTime`) from `PriceHistoryDate`) - interval $t2 minute))
          and `CoinID` = $coinID
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


function writePctPrices($coinID, $price1Hr, $price24Hr, $price7D, $price15Min, $price30Min, $price45Min, $price75Min,$price48Hr,$price72Hr){
  $conn = getHistorySQL(rand(1,4));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call AddPctPrices($coinID, $price1Hr, $price24Hr, $price7D, $price15Min, $price30Min, $price45Min, $price75Min, $price48Hr,$price72Hr);";

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
    Echo "<BR> CMC Array Empty: Running | $coinStr";
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

function getCMCPriceFromSQL($coinID, $column){
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `$column`, `Cp`.`LiveCoinPrice`
        FROM `CMCData` `Cmc`
        join `CoinPrice` `Cp` on `Cp`.`CoinID` = `Cmc`.`CoinID`
        WHERE `Cmc`.`CoinID` = $coinID";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row[$column],$row['LiveCoinPrice']);
  }
  $conn->close();
  return $tempAry;
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
    //$Hr1Price = getPrice($coinID, 55, 65);
    //echo "<BR> get1HrPrice($coinID); ".$Hr1Price[0][1];

    //Check if 0
    //if (!isset($Hr1Price[0][1]) OR is_null($Hr1Price[0][1]) OR $Hr1Price[0][1] == 0){
    //  echo "<BR> IS NULL| 1hr | $coinID";
       //$CMCStats = getCMCstats($CMCStats, $coinStr);
       //$tempPrice = findCoinStats($CMCStats,$coins[$i][1]);
       //$price1Hr = $tempPrice[0][2];
    //}elseif ($Hr1Price[0][1] == 0){
      //echo "<BR> IS ZERO";
      //$CMCStats = getCMCstats($CMCStats, $coinStr);
      //$tempPrice = findCoinStats($CMCStats,$coins[$i][1]);
      $price1Hrtmp = getCMCPriceFromSQL($coinID, '1HrPrice');
      if (!isset($priceDiff)){
        newLogToSQL("getCMCPriceFromSQL","getCMCPriceFromSQL($coinID, '1HrPrice');".$price1Hrtmp[0][0],3,1,"pctChangeProcess","CoinID:$coinID");
      }
      $priceDiff = ($price1Hrtmp[0][1]/100)*abs($price1Hrtmp[0][0]);
      if ($price1Hrtmp[0][0]>0){
        $price1Hr = number_format($price1Hrtmp[0][1]-$priceDiff,8, '.', '');
      }else{
        $price1Hr = number_format($price1Hrtmp[0][1]+$priceDiff,8, '.', '');
      }

      Echo "<BR> 1Hour: ".$price1Hrtmp[0][0]." | Live: ".$price1Hrtmp[0][1]." | NewPrice: $price1Hr";
    //}else{
    //  $price1Hr = $Hr1Price[0][1];
    //}

    //$Hr24Price = getPrice($coinID, 1415, 1445);
    //if (!isset($Hr24Price[0][1]) OR is_null($Hr24Price[0][1]) OR $Hr24Price[0][1] == 0){
    //  echo "<BR> IS NULL| 24hr | $coinID";
      $price24Hrtmp = getCMCPriceFromSQL($coinID, '24HrPrice');
      $priceDiff = ($price24Hrtmp[0][1]/100)*abs($price24Hrtmp[0][0]);
      if($price24Hrtmp[0][0]>0){
        $price24Hr = number_format($price24Hrtmp[0][1]-$priceDiff,8, '.', '');
      }else{
        $price24Hr = number_format($price24Hrtmp[0][1]+$priceDiff,8, '.', '');
      }

    //}else{
  //    $price24Hr = $Hr24Price[0][1];
    //}

    $Hr48Price = getPrice($coinID, 2870, 2890);
    if (!isset($Hr48Price[0][1]) OR is_null($Hr48Price[0][1]) OR $Hr48Price[0][1] == 0){
      echo "<BR> IS NULL| 48hr | $coinID";
      //$price48Hrtmp = getCMCPriceFromSQL($coinID, '48HrPrice');
      $price48Hr = 0;
    }else{
      $price48Hr = number_format($Hr48Price[0][1],8, '.', '');
    }

    $Hr72Price = getPrice($coinID, 4310, 4330);
    if (!isset($Hr72Price[0][1]) OR is_null($Hr72Price[0][1]) OR $Hr72Price[0][1] == 0){
      echo "<BR> IS NULL | 72hr | $coinID";
      //$price72Hrtmp = getCMCPriceFromSQL($coinID, '48HrPrice');
      $price72Hr = 0;
    }else{
      $price72Hr = number_format($Hr72Price[0][1],8, '.', '');
    }

    //$D7Price = getPrice($coinID, 10000, 10500);
    //if (!isset($D7Price[0][1]) OR is_null($D7Price[0][1]) OR $D7Price[0][1] == 0){
      echo "<BR> IS NULL| 7D | $coinID";
      $price7Dtmp = getCMCPriceFromSQL($coinID, '7DayPrice');
      if (!isset($priceDiff)){
        newLogToSQL("getCMCPriceFromSQL","getCMCPriceFromSQL($coinID, '7DayPrice');".$price1Hrtmp[0][0],3,1,"pctChangeProcess","CoinID:$coinID");
      }else{
        $priceDiff = ($price7Dtmp[0][1]/100)*abs($price7Dtmp[0][0]);
      }
      if($price7Dtmp[0][0]>0){
        $price7D = number_format($price7Dtmp[0][1]-$priceDiff,8, '.', '');
      }else{
        $price7D = number_format($price7Dtmp[0][1]+$priceDiff,8, '.', '');
      }

      echo "<BR> $coinID Live Price : ".$price7Dtmp[0][1]." | 7D Price : $price7D |  Pct:".$price7Dtmp[0][0]." | PriceDiff:$priceDiff ";
    //}else{
    //  $price7D = $D7Price[0][1];
    //}

    $Min15Price = getPrice($coinID, 10, 20);
    $price15Min = 0;
    if (isset($Min15Price[0][1])){
      $price15Min = number_format($Min15Price[0][1],8, '.', '');
    }


    $Min30Price = getPrice($coinID, 25, 35);
        $price30Min = 0;
    if (isset($Min30Price[0][1])){
        $price30Min = number_format($Min30Price[0][1],8, '.', '');
    }


    $Min45Price = getPrice($coinID, 40, 50);
    $price45Min = 0;
    if (isset($Min45Price[0][1])){
      $price45Min = number_format($Min45Price[0][1],8, '.', '');
    }

    $Min75Price = getPrice($coinID, 70, 80);
    $price75Min = 0;
    if (isset($Min75Price[0][1])){
      $price75Min = number_format($Min75Price[0][1],8, '.', '');
    }

    //Write to PricePctChangeHistory
    writePctPrices($coinID, $price1Hr, $price24Hr, $price7D,$price15Min, $price30Min, $price45Min,$price75Min,$price48Hr,$price72Hr);
    echo "<BR> write1HrPrice($coinID, $price1Hr, $price24Hr, $price7D);";
}

?>
</html>
