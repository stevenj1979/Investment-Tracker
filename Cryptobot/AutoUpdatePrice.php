<html>
<?php
ini_set('max_execution_time', 900);
require('includes/newConfig.php');
//require '/home/stevenj1979/repositories/Sparkline/autoload.php';
include_once ('/home/stevenj1979/SQLData.php');
$apikey=getAPIKey();
$apisecret=getAPISecret();
$logToFileSetting = getLogToFile();
function getCoinPriceStatsSell(){
  $conn = getHistorySQL(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT Min(`Price`) as `Price`,`CoinID` FROM `CountOfCoinPrice` WHERE `Count of Price` > 10 and `Price` <> 0
group by `CoinID`";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  Echo "<BR>";
  print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Price'],$row['CoinID']
    );
  }
  $conn->close();
  return $tempAry;
}

function getCoinPriceStats(){
  $conn = getHistorySQL(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT Max(`Price`) as `Price`,`CoinID` FROM `CountOfCoinPrice` WHERE `Count of Price` > 10 and `Price` <> 0
group by `CoinID`";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  Echo "<BR>";
  print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Price'],$row['CoinID']
    );
  }
  $conn->close();
  return $tempAry;
}

function getTrend($Price4Trend, $Price3Trend, $LastPriceTrend, $LivePriceTrend){
  Echo "<BR> Get Trend: ".$Price3Trend.$LastPriceTrend.$LivePriceTrend;
  $newTrend = $Price4Trend + $Price3Trend + $LastPriceTrend + $LivePriceTrend;
  if ($newTrend == 0){ return 30;}
  //elseif ($newTrend == "") {
  //  // code...
  //}
  else {return $newTrend;}
}

function calculateBuyPrice($coinID, $Live1HrChange, $Live24HrChange, $LiveCoinPrice ,$CoinPricePctChange, $Price4Trend, $Price3Trend, $LastPriceTrend, $LivePriceTrend, $baseCurrency, $coinMultiplier){
  Echo "<BR> calculateBuyPrice($coinID, $Live1HrChange, $Live24HrChange, $LiveCoinPrice ,$CoinPricePctChange, $Price4Trend, $Price3Trend, $LastPriceTrend, $LivePriceTrend, $baseCurrency){";
  $newSellPrice = 0.00;
  $finalTrend = getTrend($Price4Trend, $Price3Trend, $LastPriceTrend, $LivePriceTrend);
  Echo "<BR> Final Trend : $finalTrend ";
  if ($finalTrend >0){
    //Trend Upwards
    $finalTrend = $finalTrend / $coinMultiplier;
    Echo "<BR> New Final Trend : $finalTrend ";
    $newSellPrice = (($LiveCoinPrice / 100) * $finalTrend) + $LiveCoinPrice;
    Echo "<BR> newSellPrice $newSellPrice = (($LiveCoinPrice / 100) * $finalTrend) + $LiveCoinPrice;";
    Echo "<BR> updateBuyPrice($coinID,$LiveCoinPrice);";
    updateBuyPrice($coinID,$LiveCoinPrice,$finalTrend);
    Echo "<BR> updateSellPrice($coinID, $newSellPrice);";
    updateSellPrice($coinID, $newSellPrice,$finalTrend);
  }else{
    //Trend Downwards
    $finalTrend = $finalTrend / $coinMultiplier;
    Echo "<BR> New Final Trend : $finalTrend ";
    $newBuyPrice = (($LiveCoinPrice / 100) * $finalTrend) - $LiveCoinPrice;
    Echo "<BR> newBuyPrice $newBuyPrice = (($LiveCoinPrice / 100) * $finalTrend) - $LiveCoinPrice;";
    Echo "<BR> updateBuyPrice($coinID,$newSellPrice);";
    updateBuyPrice($coinID,$newSellPrice,$finalTrend);
    Echo "<BR> updateBuyPrice($coinID,$LiveCoinPrice);";
    updateSellPrice($coinID, $LiveCoinPrice,$finalTrend);
  }
}

function calculateSellPrice($coinID, $Live1HrChange, $Live24HrChange, $CoinPricePctChange, $Price4Trend, $Price3Trend, $LastPriceTrend, $LivePriceTrend, $baseCurrency, $LiveCoinPrice){

}

function updateBuyPrice($newBuyPrice,$coinID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `CryptoAuto` SET `AutoBuyPrice` = $newBuyPrice WHERE `CoinID` = $coinID";
  echo "<BR>";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function updateSellPrice($newSellPrice,$coinID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `CryptoAuto` SET `AutoSellPrice` = $newSellPrice WHERE `CoinID` = $coinID";
  echo "<BR>";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function getCoinTrend(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `IDCn` as `ID`,`Symbol`,`Price4Trend`,`Price3Trend`,`LastPriceTrend`,`LivePriceTrend`,(`Price4Trend`*1 +`Price3Trend`*1 +`LastPriceTrend`*1 +`LivePriceTrend`*1 ) as CoinPriceTrend
  ,`1HrPriceChangeLive`,`1HrPriceChangeLast`,`1HrPriceChange3`,`1HrPriceChange4`, (`1HrPriceChangeLive`*1 +`1HrPriceChangeLast`*1 +`1HrPriceChange3`*1 +`1HrPriceChange4`*1 ) as Hr1Trend FROM `View1_BuyCoins`";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  Echo "<BR>";
  print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['Symbol'],$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['CoinPriceTrend']
      ,$row['1HrPriceChangeLive'],$row['1HrPriceChangeLast'],$row['1HrPriceChange3'],$row['1HrPriceChange4'],$row['Hr1Trend']
    );
  }
  $conn->close();
  return $tempAry;
}

function updateCoinTrend($coinID, $priceTrend, $hr1Trend){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `AllCoinStatus` SET `CoinTrendPrice`= $priceTrend,`CoinTrend1Hr`= $hr1Trend WHERE `CoinID` = $coinID";
  echo "<BR>";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function removeCoinStatsWebTable(){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "DELETE FROM `SellCoinStatsWeb`";
  echo "<BR>";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}
function refreshCoinStatsWebTable(){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "INSERT INTO `SellCoinStatsWeb`(`ID`, `Type`, `CoinID`, `UserID`, `CoinPrice`, `Amount`, `Status`, `OrderDate`, `CompletionDate`, `BittrexID`, `OrderNo`, `Symbol`, `LastBuyOrders`, `LiveBuyOrders`
            ,`BuyOrdersPctChange`, `LastMarketCap`, `LiveMarketCap`, `MarketCapPctChange`
            , `LastCoinPrice`, `LiveCoinPrice`, `CoinPricePctChange`, `LastSellOrders`, `LiveSellOrders`, `SellOrdersPctChange`, `LastVolume`, `LiveVolume`, `VolumePctChange`, `Last1HrChange`, `Live1HrChange`
            , `Hr1PctChange`, `Last24HrChange`, `Live24HrChange`, `Hr24PctChange`, `Last7DChange`
            , `Live7DChange`, `D7PctChange`, `BaseCurrency`, `AutoSellPrice`, `Price4Trend`, `Price3Trend`, `LastPriceTrend`, `LivePriceTrend`, `FixSellRule`, `SellRule`, `BuyRule`, `ToMerge`, `LowPricePurchaseEnabled`, `PurchaseLimit`, `PctToPurchase`, `BTCBuyAmount`, `NoOfPurchases`, `Name`
            , `Image`, `MaxCoinMerges`, `NoOfCoinSwapsThisWeek`, `CaptureTrend`)  SELECT `IDTr` as `ID`, `Type`, `CoinID`, `UserID`, `CoinPrice`, `Amount`, `Status`, `OrderDate`, `CompletionDate`, `BittrexID`, `OrderNo`, `Symbol`, `LastBuyOrders`, `LiveBuyOrders`, `BuyOrdersPctChange`, `LastMarketCap`
            , `LiveMarketCap`, `MarketCapPctChange`, `LastCoinPrice`, `LiveCoinPrice`
          , `CoinPricePctChange`, `LastSellOrders`, `LiveSellOrders`, `SellOrdersPctChange`, `LastVolume`, `LiveVolume`, `VolumePctChange`, `Last1HrChange`, `Live1HrChange`, `Hr1ChangePctChange` as `Hr1PctChange`, `Last24HrChange`, `Live24HrChange`,`Hr24ChangePctChange` as `Hr24PctChange`, `Last7DChange`, `Live7DChange`,`D7ChangePctChange` as `D7PctChange`
          , `BaseCurrency`, 'AutoSellPrice', `Price4Trend`, `Price3Trend`, `LastPriceTrend`, `LivePriceTrend`, `FixSellRule`, `SellRule`, `BuyRule`, `ToMerge`, `LowPricePurchaseEnabled`,`DailyBTCLimit` as `PurchaseLimit`, `PctToPurchase`, `BTCBuyAmount`, `NoOfPurchases`, `Name`, `Image`,10 as  `MaxCoinMerges`
          , `NoOfCoinSwapsThisWeek`, `CaptureTrend`  FROM `View5_SellCoins` WHERE `Status` in ('Open','Pending')";
  echo "<BR>";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function get1HrTopandBottom($coinID){
  $conn = getHistorySQL(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT Max(`Price`) as TopPrice, Min(`Price`) as BottomPrice FROM `PriceHistory` WHERE `CoinID` = $coinID and `PriceDateTimeID` in (SELECT `ID`  FROM `PriceHistoryDate` WHERE `PriceDateTime` BETWEEN CURDATE() - INTERVAL 60 MINUTE AND CURDATE())";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  Echo "<BR>";
  print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Price'],$row['CoinID']
    );
  }
  $conn->close();
  return $tempAry;
}

function write1HrTopandBottom($coinID, $top, $bottom){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "call Update1hrTopandBottomPrice($coinID,$top,$bottom);";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    newLogToSQL("write1HrTopandBottom",$sql,3,0,"SQL","CoinID:$coinID");
    $conn->close();
}

function run1HrTopandBottom(){
  $coin = getCoinIDs();
  $coinSize = count($coin);
  for ($g=0; $g<$coinSize; $g++){
    $coinID = $coin[$g][0];
    $prices = get1HrTopandBottom($coinID);
    $top = $prices[0];$bottom = $prices[1];
    write1HrTopandBottom($coinID,$top,$bottom);
  }
}

//set time
setTimeZone();
$date = date("Y-m-d H", time());
// ***  UPDATE Buy price : for Autobuy
$coinStatsAry = getCoinPriceStats();
$coinStatsSize = count($coinStatsAry);

for($x = 0; $x < $coinStatsSize; $x++) {
  $newBuyPrice = $coinStatsAry[$x][0]; $coinID = $coinStatsAry[$x][1];
  $buyPricePct = ($newBuyPrice/100)*3;
  $finalBitPrice = $newBuyPrice-$buyPricePct;
  updateBuyPrice($finalBitPrice,$coinID);
  Echo "<BR>Update Buy Price $finalBitPrice , $coinID";
  logAction("Update Buy Price $finalBitPrice , $coinID",'AutoUpdatePrice',$logToFileSetting);
}
// ***  UPDATE Sell price : for Autosell
$coinStatsSellAry = getCoinPriceStatsSell();
$coinStatsSellSize = count($coinStatsSellAry);
for($x = 0; $x < $coinStatsSellSize; $x++) {
  $newSellPrice = $coinStatsSellAry[$x][0]; $coinID = $coinStatsSellAry[$x][1];
  $sellPricePct = ($newSellPrice/100)*3;
  updateSellPrice($newSellPrice,$coinID);
  Echo "<BR>Update Sell Price $newSellPrice , $coinID";
  logAction("Update Sell Price $newSellPrice , $coinID",'AutoUpdatePrice',$logToFileSetting);
}
// ***  UPDATE Coin Trend
$coinTrend = getCoinTrend();
$coinTrendSize = Count($coinTrend);
Echo "<BR> coinTrendSize: $coinTrendSize";
for($x = 0; $x < $coinTrendSize; $x++) {
  $coinID = $coinTrend[$x][0]; $priceTrend = $coinTrend[$x][6]; $hr1Trend = $coinTrend[$x][11];
  Echo "<BR> updateCoinTrend($coinID,$priceTrend,$hr1Trend);";
  updateCoinTrend($coinID,$priceTrend,$hr1Trend);
}

// ***  Sparkline Images

echo "<BR> Generate sparkline Images";
//$sparklineAry = [];
$trackingCoins = getTrackingCoins("WHERE `DoNotBuy` = 0 and `BuyCoin` = 1 ORDER BY `Symbol` ASC","FROM `View1_BuyCoins` ");
$coinSize = Count($trackingCoins);

for ($j=0; $j<$coinSize; $j++){
  Echo "<BR> Fetching ".$trackingCoins[$j][1];
  $coinID = $trackingCoins[$j][0];
  $sparklineAry = getSparklineData($trackingCoins[$j][1]);
  $url ="http://www.investment-tracker.net/Sparkline/sparkline.php?size=150x80&data=";
  $url2 = "&back=fff&line=5bb763&fill=d5f7d8";
  $data = dataToString(",",$sparklineAry);
  $savePath ="/home/stevenj1979/public_html/Investment-Tracker/Cryptobot/Images/";
  saveImage($coinID,$url.$data.$url2,$savePath);
}

//$sparkline = new Davaxi\Sparkline();
//$sparkline->setData(array(2,4,5,6,10,7,8,5,7,7,11,8,6,9,11,9,13,14,12,16));
//$sparkline->save('/home/stevenj1979/repositories/Sparkline/BTC');
//$sparkline->display();
//echo "<BR> Test data : $data";

//Echo "<img src='".$url.$data.$url2."' />";
//phpinfo();

removeCoinStatsWebTable();
refreshCoinStatsWebTable();
run1HrTopandBottom();
?>
</html>
