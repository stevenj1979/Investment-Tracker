<html>
<?php
ini_set('max_execution_time', 300);
require('includes/newConfig.php');

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

function timerReady($start, $seconds){
  $newDate = date("Y-m-d H:i",strtotime("+".$seconds." seconds", strtotime($start)));
  $current_date = date('Y-m-d H:i', time());
  echo "<BR> NewDate $newDate :: current date $current_date";
  if ($newDate <= $current_date){return true;}else{return false;}

}

function findCoinStats($CMCStats, $symbol){
  $statsLength = count($CMCStats);
  for($y = 0; $y < $statsLength; $y++) {
    echo "<br> FindCoin=".$CMCStats[$y][0];
    if ($CMCStats[$y][0]== $symbol){
      //echo "<br> $statsLength Error Line ".$CMCStats[$y][0].",".$CMCStats[$y][1].",".$CMCStats[$y][2].",".$CMCStats[$y][3].",".$CMCStats[$y][4]."<br>";
      $tempStats[] = Array($CMCStats[$y][0],$CMCStats[$y][1],$CMCStats[$y][2],$CMCStats[$y][3],$CMCStats[$y][4]);
      return $tempStats;
    }
  }
  return $tempStats;
}

function SQLCommand(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "Show Create function AddBittrexSell;";
  $conn->query("SET time_zone = '+04:00';");
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  $i = 0;
  while ($row = mysqli_fetch_assoc($result)){
    Echo $row[$i];
    $i = $i + 1;
  }
  $conn->close();
  return $tempAry;

}

function getOutStandingBuy($tmpAry){
  $tmpStr = "";
  $tmpAryCount = count($tmpAry);
  for ($i=0; $i<$tmpAryCount; $i++){
    if ($tmpAry[$i][0] <> 1){ $tmpStr .= $tmpAry[$i][1].":".$tmpAry[$i][2].",";}
  }
  return rtrim($tmpStr,",");
}

function bittrexTotalbalance($apikey, $apisecret, $base){
    $nonce=time();
    $uri='https://bittrex.com/api/v1.1/account/getbalance?apikey='.$apikey.'&currency='.$base.'&nonce='.$nonce;
    $sign=hash_hmac('sha512',$uri,$apisecret);
    $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
    //$balance = $obj["result"]["Available"];
    return $obj;
}

function getOpenSymbols(){
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Cn`.`Symbol`, round(`Tr`.`Amount`*`Cp`.`LiveCoinPrice`,4) as `TotalPrice`
    FROM `Transaction` `Tr`
    join `Coin` `Cn` on `Cn`.`ID` = `Tr`.`CoinID`
    join `CoinPrice` `Cp` on `Cp`.`CoinID` = `Cn`.`ID`
    where `Tr`.`Status` in  ('Open','Pending')  and `Cn`.`Symbol` not in ('BTC','ETH')";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Symbol'],$row['TotalPrice']);
  }
  $conn->close();
  return $tempAry;
}

$tmpTime = "+2 minutes";
$date = date("Y-m-d H:i", time());$current_date = date('Y-m-d H:i');
$newTime = date("Y-m-d H:i",strtotime($tmpTime, strtotime($current_date)));

//$resultOrd = bittrexOrder($apikey, $apisecret, '2663baf9-8c75-45de-a75f-6bc138f60caf', 3);

//var_dump($resultOrd);

//$newOrd = bittrexCoinStats($apikey, $apisecret, "BTC", "USDT", 3);
echo "<BR>";
//var_dump($newOrd);

//$brandNew = bittrexbalance($apikey, $apisecret, 'USDT', 3);
//$obj = json_decode($brandNew, true);
//for ($x=0; $x<count($obj); $x++){
//    echo "<br> :".$obj[$x]["currencySymbol"];
//}
//}
//var_dump($brandNew);


//newAgain = getMinTrade($apisecret, 3);
//$json=json_decode( $newAgain );

//var_dump($json);

//$newObj = bittrexCoinPrice($apikey, $apisecret, "USDT", "BTC", 3);

//var_dump($newObj);

//$buyTest = bittrexbuy($apikey, $apisecret, 'BCH', 0.4, 288.0,'USDT', 3);

//echo "<BR> ID: ".$buyTest['id'];
//echo "<BR> quantity: ".$buyTest['quantity'];
//echo "<BR> fillQuantity: ".$buyTest['fillQuantity'];
//echo "<BR> commission: ".$buyTest['commission'];
//echo "<BR> status: ".$buyTest['status'];


//$sellTest = bittrexsell($apikey, $apisecret, 'BTC', 0.01, 11790.0, 'USDT', 3);
//echo "<BR> ID: ".$sellTest['id'];
//echo "<BR> quantity: ".$sellTest['quantity'];
//echo "<BR> fillQuantity: ".$sellTest['fillQuantity'];
//echo "<BR> commission: ".$sellTest['commission'];
//echo "<BR> status: ".$sellTest['status'];

//$getOrderTest = bittrexOrder($apikey, $apisecret, '34b51110-be0e-4967-84eb-9fe475f85120', 3);
//echo "<BR> status: ".$getOrderTest['status'];
//echo "<BR> fillQuantity: ".$getOrderTest['fillQuantity'];
//echo "<BR> commission: ".$getOrderTest['commission'];

$cancelTest = bittrexCancel($apikey, $apisecret, '34b51110-be0e-4967-84eb-9fe475f85120', 3);
echo "<BR> Status: ".$cancelTest['status'];
echo "<BR> fillQuantity: ".$cancelTest['fillQuantity'];
echo "<BR> quantity: ".$cancelTest['quantity'];
echo "<BR> id: ".$cancelTest['orderToCancel']["id"];

//$statsTest = bittrexCoinStats($apikey, $apisecret, 'BTC', 'USDT', 3);
//echo "<BR> high: ".$statsTest['high'];
//echo "<BR> low: ".$statsTest['low'];
//echo "<BR> volume: ".$statsTest['volume'];
//echo "<BR> percentChange: ".$statsTest['percentChange'];

//$balTest = bittrexbalance($apikey, $apisecret, 'BTC', 3);
//echo "<BR> total: ".$balTest['total'];



?>
</html>
