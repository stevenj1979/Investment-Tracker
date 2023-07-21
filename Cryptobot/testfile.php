<html>
<?php
ini_set('max_execution_time', 300);
require('includes/newConfig.php');
//set_include_path('/home/stevenj1979/repositories/gdax/src/Configuration.php');
include_once ('/home/stevenj1979/SQLData.php');
include_once ('/home/stevenj1979/Encrypt.php');
include_once ('includes/SQLDbCommands.php');

$SQLUpdateLog = 1;
$SQLProcedureLog = 1;

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

function testBittrexCoinPrice($apikey, $apisecret, $baseCoin, $coin, $versionNum){
      $nonce=time();
      if ($versionNum == 1){
          $uri='https://bittrex.com/api/v1.1/public/getticker?market='.$baseCoin.'-'.$coin;
          $sign=hash_hmac('sha512',$uri,$apisecret);
          $ch = curl_init($uri);
              curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          $execResult = curl_exec($ch);
          $obj = json_decode($execResult, true);
          $balance = $obj["result"]["Last"];
      }elseif ($versionNum == 3){
        $timestamp = time()*1000;
        $url = "https://api.bittrex.com/v3/markets/tickers";
        echo "<BR>".$url;
        $method = "GET";
        $content = "";
        $subaccountId = "";
        $contentHash = hash('sha512', $content);
        $preSign = $timestamp . $url . $method . $contentHash . $subaccountId;
        $signature = hash_hmac('sha512', $preSign, $apisecret);

        $headers = array(
        "Accept: application/json",
        "Content-Type: application/json",
        "Api-Key: ".$apikey."",
        "Api-Signature: ".$signature."",
        "Api-Timestamp: ".$timestamp."",
        "Api-Content-Hash: ".$contentHash.""
        );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        $balance = curl_exec($ch);
        curl_close($ch);
        $temp = json_decode($balance, true);
        //$balance = $temp['lastTradeRate'];
      }

      return $temp;
}

function getSymbols(){
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Symbol`,`BaseCurrency` FROM `Coin` WHERE `BuyCoin` = 1";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Symbol'],$row['BaseCurrency']);
  }
  $conn->close();
  return $tempAry;
}

function getArrayPrice($coinAry, $symbol, $baseCurrency){
  $coinArySize = count($coinAry);
  echo "<BR> Size : $coinArySize";
  $nPrice = 0.0;
  for ($j=0; $j<$coinArySize; $j++){
    if ($coinAry[$j]['symbol'] == $symbol."-".$baseCurrency){
      echo "<BR> ".$coinAry[$j]['symbol']." == $symbol - $baseCurrency";
      $nPrice = $coinAry[$j]['askRate'];
      break;
    }
  }
  return $nPrice;
}

$tmpTime = "+2 minutes";
$date = date("Y-m-d H:i", time());$current_date = date('Y-m-d H:i');
$newTime = date("Y-m-d H:i",strtotime($tmpTime, strtotime($current_date)));

function trackingCoinReadyToBuyLoc($livePrice, $mins, $type, $buyPrice, $TransactionID, $NoOfRisesInPrice, $pctProfit, $minsFromDate, $lastPrice, $totalRisesInPrice){
  $swingPrice = (($livePrice/100)*0.25);
  $currentPrice = abs($livePrice-$lastPrice);
  //$bottomPrice = $livePrice-$swingPrice;

  //if liveprice is stable, add 1 - -0.5 - 0.5
  if ($minsFromDate < 5){
      return False;
  }

  if (($mins >= 60 && $livePrice > $buyPrice) OR ($NoOfRisesInPrice > $totalRisesInPrice && $livePrice > $buyPrice)){
    //if time is over 60 min and livePrice is > original price,  sell
    // if no of buys is greater than total needed - Buy
    reopenTransaction($TransactionID);
    return True;
  }

  if($currentPrice <= $swingPrice){
    updateNoOfRisesInPrice($TransactionID, $noOfRisesInPrice+1);
    setNewTrackingPrice($livePrice, $TransactionID);
    return False;
  }

  //if liveprice is greater than or less than, reset to 0
  if (($currentPrice > $swingPrice) OR ($currentPrice < $swingPrice)){
    updateNoOfRisesInPrice($newTrackingCoinID, 0);
    setNewTrackingPrice($livePrice, $TransactionID);
    return False;
  }

  if (($type == 'Buy' && $pctProfit < -3) OR ($type == 'Buy' && $pctProfit > 3)){
    //Cancel Transaction
    reopenTransaction($TransactionID);
    closeNewTrackingCoin($TransactionID, True);
    return False;
  }

}

function trackingCoinReadyToSellLoc($livePrice, $mins, $type, $sellPrice, $TransactionID, $NoOfRisesInPrice, $pctProfit, $minsFromDate, $lastPrice, $totalRisesInPrice){
    $swingPrice = (($livePrice/100)*0.25);
    $currentPrice = abs($livePrice-$lastPrice);
    //$bottomPrice = $livePrice-$swingPrice;

    //if liveprice is stable, add 1 - -0.5 - 0.5
    if ($minsFromDate < 5){
        return False;
    }

    if (($mins >= 60 && $livePrice < $sellPrice) OR ($NoOfRisesInPrice > $totalRisesInPrice && $livePrice < $sellPrice)){
      //if time is over 60 min and livePrice is > original price,  sell
      // if no of buys is greater than total needed - Buy
      reopenTransaction($TransactionID);
      return True;
    }

    if($currentPrice <= $swingPrice){
      updateNoOfRisesInSellPrice($TransactionID, $NoOfRisesInPrice+1, $livePrice);
      setNewTrackingPrice($livePrice, $TransactionID, 'Sell');
      return False;
    }

    //if liveprice is greater than or less than, reset to 0
    if (($currentPrice > $swingPrice) OR ($currentPrice < $swingPrice)){
      updateNoOfRisesInSellPrice($TransactionID, 0, $livePrice);
      setNewTrackingPrice($livePrice, $TransactionID, 'Sell');
      return False;
    }

    if (($type == 'Sell' && $pctProfit < -3) OR ($type == 'Sell' && $pctProfit > 3)){
      //Cancel Transaction
      reopenTransaction($TransactionID);
      closeNewTrackingSellCoin($TransactionID);
      return False;
    }

}

function test(){
  global $SQLUpdateLog, $$SQLProcedureLog;
  echo $SQLUpdateLog;
  echo "TEST";
}

function findCoinStatsLoc($CMCStats, $symbol){
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

bittrexOpenOrders($apikey,$apisecret,3);

$bal = bittrexAllBalances($apikey, $apisecret, 3);
$balSize = count($bal);

for ($t=0;$t<$balSize; $t++){
  $symbol = $bal[$t]['currencySymbol']; $total = $bal[$t]['total'];
  if ($total > 0){
      //SuperLog("", "<BR> $symbol | $total","","","1-1-3");
  }

}
$uuid = '8ac96efe-f1ed-4d3a-849c-377254ca6529';
$resultOrd = bittrexOrder($apikey, $apisecret, $uuid, 3);
echo "<BR> OrderStatus: ".$resultOrd["status"];
echo "<BR>";
var_dump($resultOrd);

$resultOrd = bittrexOrderClosed($apikey, $apisecret, $uuid, 3);
Echo "<BR> CLOSED ORDERS!!!";
var_dump($resultOrd);

Echo "<BR> TEST New SELECT <BR> ";
$sql = "SELECT `IDTr`,`Type`,`CoinID`,`UserID`,`CoinPrice`,`Amount`,`Status`,`OrderDate`,`CompletionDate`,`BittrexID`,`OrderNo`,`Symbol`,`LastBuyOrders`,`LiveBuyOrders`,`BuyOrdersPctChange`,`LastMarketCap`
,`LiveMarketCap`,`MarketCapPctChange`,`LastCoinPrice`,`LiveCoinPrice`,`CoinPricePctChange`,`LastSellOrders`,`LiveSellOrders`,`SellOrdersPctChange`,`LastVolume`,`LiveVolume`,`VolumePctChange`,`Last1HrChange`
,`Live1HrChange`,`Hr1ChangePctChange`,`Last24HrChange`,`Live24HrChange`,`Hr24ChangePctChange`,`Last7DChange`,`Live7DChange`,`D7ChangePctChange`,`BaseCurrency`,`LivePriceTrend`,`LastPriceTrend`,`Price3Trend`
,`Price4Trend`,`FixSellRule`,`SellRule`,`BuyRule`,`ToMerge`,`LowPricePurchaseEnabled`,`TotalPurchasesPerCoin` as `PurchaseLimit`,`PctToPurchase`, `BTCBuyAmount`,`NoOfPurchases`,`Name`,`Image`,10 as `MaxCoinMerges`
,`NoOfCoinSwapsThisWeek`,`OriginalPrice`, `CoinFee`,`LivePrice`, `ProfitUSD`, `ProfitPct`,`CaptureTrend`,`minsToDelay`,`MinsFromBuy`,`HoursFlatHighPdcs`,`MaxPriceFromHigh`,`PctFromLiveToHigh`,`MultiSellRuleEnabled`
,floor(timestampdiff(second,`OrderDate`, now())/3600) as `HoursSinceBuy`, 'SellPctCsp',`MaxHoursFlat`,`Hr1Top`,`Hr1Bottom`,`CaaOffset`,`CaaMinsToCancelSell`,`CaaSellOffset`,`SpreadBetTransactionID`
FROM `View5_SellCoins` Where `UserID` = 3 and `Status` = 'Open' and `Type` = 'SpreadSell' order by `ProfitPct` Desc";
//$data = mySQLSelect($sql);
$data = assocSQLSelect("TestSQL: ",$sql,3,1,1,0,"TestFile",90);
foreach ($data as $row) {
    echo "<BR> BittrexRef: ".$row['BittrexRef'];
}
//$datasize = count($data);
//Echo "<BR> Count: $datasize <BR>";
//var_dump($data);
//for ($y=0; $y<$datasize;$y++){
//  echo "<br> HERE";
//  echo "<BR> Here ".$data[$y][0]." | ".$data[$y][1];
//}

?>
</html>
