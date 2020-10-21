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

function getPricePctIncrease($coinID){
  $conn = getHistorySQL(rand(1,4));
  if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `CoinID`,`OneHourPricePct`,`TwentyFourHourPricePct`,`SevenDayPricePct`,`PriceDate` FROM `CoinPricePctIncrease` WHERE `CoinID` = $coinID";
  //echo "<BR>".$sql;
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['CoinID'],$row['OneHourPricePct'],$row['TwentyFourHourPricePct'],$row['SevenDayPricePct'],$row['PriceDate']);
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

function writePctIncrease($coinID, $price1, $price2, $price3,$date){
  //$userID = $_SESSION['ID'];
  //$nameID = $_SESSION['coinPriceMatchNameSelected'];
  //echo "$ruleID $symbol $price $userID";
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "INSERT INTO `CoinPricePctIncrease`(`CoinID`, `OneHourPct`, `TwentyFourHourPct`, `SevenDayPct`, `PriceDate`) VALUES ($coinID, $price1,$price2,$price3,'$date')";

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

$coin = getCoins();
$coinSize = count($coin);
$priceMatch = getPriceMatchID();
$priceMatchSize = Count($priceMatch);
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
    for ($k=0; $k<$priceMatchSize; $k++){
      if ($priceMatch[$k][2] == "Buy"){
        addpricePatterntoSQL($coin[$j][0],$tempAry[0][0],$tempAry2[0][0],$priceMatch[$k][0],$priceMatch[$k][1],$priceMatch[$k][2]);
        echo "BUY: addpricePatterntoSQL(".$coin[$j][0].",".$tempAry[0][0].",".$tempAry2[0][0].",".$priceMatch[$k][0].",".$priceMatch[$k][1].",".$priceMatch[$k][2].");";
      }else{
        $newPrice = $tempAry[0][0]*10;
        addpricePatterntoSQL($coin[$j][0],$tempAry[0][0],$tempAry2[0][0],$priceMatch[$k][0],$priceMatch[$k][1],$priceMatch[$k][2]);
        echo "Sell: addpricePatterntoSQL(".$coin[$j][0].",".$tempAry[0][0].",".$tempAry2[0][0].",".$priceMatch[$k][0].",".$priceMatch[$k][1].",".$priceMatch[$k][2].");";
      }

    }

  }
}
$coinPricePct = getPricePctIncrease(84);
$coinPricePctSize = count($coinPricePct);
for ($i=0; $i<$coinPricePctSize; $i++){
  writePctIncrease($coinPricePct[$i][0],$coinPricePct[$i][1],$coinPricePct[$i][2],$coinPricePct[$i][3],$coinPricePct[$i][4]);
}

?>
</html>
