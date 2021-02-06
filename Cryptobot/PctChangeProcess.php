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

  $sql = "SELECT `ID` FROM `Coin` WHERE `BuyCoin` = 1";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID']);
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

function write1HrPrice($coinID, $price){
  $conn = getHistorySQL(rand(1,4));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call Add1HrPrice($coinID, $price);";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}


$tmpTime = "+2 minutes";
$date = date("Y-m-d H:i", time());$current_date = date('Y-m-d H:i');
$newTime = date("Y-m-d H:i",strtotime($tmpTime, strtotime($current_date)));

$coins = getSymbols();
$coinCount = count($coins);

for ($i=0; $i<$coinCount; $i++){
    //variables
      $coinID = $coins[$i][0];
    //Get Prices from History
    $Hr1Price = get1HrPrice($coinID);
    //Check if 0
    if (is_null($Hr1Price)){

    }else{
      $price = $Hr1Price
    }

    //Write to PricePctChangeHistory
    write1HrPrice($coinID, $price);
}

?>
</html>
