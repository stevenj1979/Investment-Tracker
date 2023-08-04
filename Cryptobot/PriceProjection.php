<html>
<?php
ini_set('max_execution_time', 900);
require('includes/newConfig.php');
//require '/home/stevenj1979/repositories/Sparkline/autoload.php';
include_once ('/home/stevenj1979/SQLData.php');
$apikey=getAPIKey();
$apisecret=getAPISecret();
$logToFileSetting = getLogToFile();

function getPrice(){
  $conn = getHistorySQL(rand(1,4));
  $tempAry = [];
  if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `CoinID`,`MaxLivePrice`,`Max15MinsPrice`,`Max30MinsPrice`,`Max45MinsPrice`,`Max60MinsPrice`,`Max75MinsPrice`,`MinLivePrice`
  ,`Min15MinsPrice`,`Min30MinsPrice`,`Min45MinsPrice`,`Min60MinsPrice`,`Min75MinsPrice` FROM `PriceProjectionView` ";
  //echo "<BR>".$sql;
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['CoinID'],$row['MaxLivePrice'],$row['Max15MinsPrice'],$row['Max30MinsPrice'],$row['Max45MinsPrice'],$row['Max60MinsPrice'],$row['Max75MinsPrice']
    ,$row['MinLivePrice'],$row['Min15MinsPrice'],$row['Min30MinsPrice'],$row['Min45MinsPrice'],$row['Min60MinsPrice'],$row['Min75MinsPrice']);
  }
  $conn->close();
return $tempAry;
}

function getPricePctIncrease(){
  $conn = getHistorySQL(rand(1,4));
  $tempAry = [];
  if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `CoinID`,`OneHourPricePct`,`TwentyFourHourPricePct`,`SevenDayPricePct`,`PriceDate`,`TenMinsPricePct`,`TwentyMinsPricePct`,`ThirtyMinsPricePct`
  FROM `CoinPricePctIncrease`";
  //echo "<BR>".$sql;
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['CoinID'],$row['OneHourPricePct'],$row['TwentyFourHourPricePct'],$row['SevenDayPricePct'],$row['PriceDate'],$row['TenMinsPricePct']
    ,$row['TwentyMinsPricePct'],$row['ThirtyMinsPricePct']);
  }
  $conn->close();
return $tempAry;
}

function writePrice($coinID, $maxPrice1,$maxPrice2,$maxPrice3,$maxPrice4,$maxPrice5,$maxPrice6, $minPrice1,$minPrice2,$minPrice3,$minPrice4,$minPrice5,$minPrice6){

  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call WritePriceProjection($coinID,$maxPrice1,$minPrice1,$maxPrice2,$minPrice2,$maxPrice3,$minPrice3,$maxPrice4,$minPrice4,$maxPrice5,$minPrice5,$maxPrice6,$minPrice6);";
  print_r("<BR>".$sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function getCoins(){
  $conn = getSQLConn(rand(1,3));
  $tempAry = [];
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

function addpricePatterntoSQL($coinID, $price, $lowPrice, $userID,$nameID, $buySell){
  //$userID = $_SESSION['ID'];
  //$nameID = $_SESSION['coinPriceMatchNameSelected'];
  //echo "$ruleID $symbol $price $userID";
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  if ($buySell == "Buy"){
      $sql = "call PriceProjectionUpdatePrice($lowPrice,$coinID,$userID,0,$nameID);";
  }else{
    $newPrice = $price * 10;
    $sql = "call PriceProjectionUpdatePrice($newPrice,$coinID,$userID,$price,$nameID);";
  }

  echo "<BR>".$sql." : ".$buySell."<BR>";
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  //header('Location: Settings_Patterns.php');
}

function writePctIncrease($coinID, $price1, $price2, $price3,$date, $price4,$price5,$price6){
  //$userID = $_SESSION['ID'];
  //$nameID = $_SESSION['coinPriceMatchNameSelected'];
  //echo "$ruleID $symbol $price $userID";
  $nPrice1 = round($price1,8);
  $nPrice2 = round($price2,8);
  $nPrice3 = round($price3,8);
  $nPrice4 = round($price4,8);
  $nPrice5 = round($price5,8);
  $nPrice6 = round($price6,8);
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call WritePricePctIncrease($coinID, $nPrice1,$nPrice2,$nPrice3,$nPrice4,$nPrice5,$nPrice6,'$date')";
  echo "<BR>$sql";
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  //header('Location: Settings_Patterns.php');
}

function getPriceMatchID(){
  $conn = getSQLConn(rand(1,3));
  $tempAry = [];
  if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `UserID`,`ID`,`BuySell` FROM `PriceProjectionUpdate`";
  //echo "<BR>".$sql;
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['UserID'],$row['ID'],$row['BuySell']);
  }
  $conn->close();
return $tempAry;
}


    $coinPrices = getPrice();
    $coinPricesSize = newCount($coinPrices);

    for ($i=0; $i<$coinPricesSize; $i++){

      writePrice($coinPrices[$i][0],$coinPrices[$i][1],$coinPrices[$i][2],$coinPrices[$i][3],$coinPrices[$i][4],$coinPrices[$i][5],$coinPrices[$i][6],$coinPrices[$i][7]
      ,$coinPrices[$i][8],$coinPrices[$i][9],$coinPrices[$i][10],$coinPrices[$i][11],$coinPrices[$i][12]);

    }

$coinPricePct = getPricePctIncrease();
$coinPricePctSize = newCount($coinPricePct);
for ($i=0; $i<$coinPricePctSize; $i++){
  echo "<BR> Update Price ".$coinPricePct[$i][0];
  writePctIncrease($coinPricePct[$i][0],$coinPricePct[$i][1],$coinPricePct[$i][2],$coinPricePct[$i][3],$coinPricePct[$i][4],$coinPricePct[$i][5],$coinPricePct[$i][6],$coinPricePct[$i][7]);
}

?>
</html>
