<?php
include_once ('/home/stevenj1979/SQLData.php');

Define("sQLUpdateLog","0");
Define("SQLProcedureLog","0");

function getBittrexRequests($userID = 0){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  $bittrexQueue = "";
  if ($userID <> 0){$bittrexQueue = " and `UserID` = $userID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Type`,`BittrexRef`,`ActionDate`,`CompletionDate`,`Status`,`SellPrice`,`UserName`,`APIKey`,`APISecret`,`Symbol`,`Amount`,`CoinPrice`,`UserID`, `Email`,`OrderNo`,`TransactionID`,`BaseCurrency`,`RuleID`,`DaysOutstanding`,`timeSinceAction`,`CoinID`,
  `RuleIDSell`,`LiveCoinPrice`,`TimetoCancelBuy`,`BuyOrderCancelTime`,`KEK`, `Live7DChange`,`CoinModeRule`,`OrderDate`,`PctToSave`,`SpreadBetRuleID`,`SpreadBetTransactionID`,`RedirectPurchasesToSpread`,`SpreadBetRuleIDRedirect`,`MinsToPauseAfterPurchase`
  ,`OriginalAmount`,`SaveResidualCoins`
  FROM `BittrexOutstandingRequests` WHERE `Status` = '1' $bittrexQueue";
  $conn->query("SET time_zone = '+04:00';");
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['Type'],$row['BittrexRef'],$row['ActionDate'],$row['CompletionDate'],$row['Status'],$row['SellPrice'],$row['UserName'],$row['APIKey'],$row['APISecret'],$row['Symbol'],$row['Amount'] //10
    ,$row['CoinPrice'],$row['UserID'],$row['Email'],$row['OrderNo'],$row['TransactionID'],$row['BaseCurrency'],$row['RuleID'],$row['DaysOutstanding'],$row['timeSinceAction'],$row['CoinID'],$row['RuleIDSell'],$row['LiveCoinPrice'] //22
    ,$row['TimetoCancelBuy'],$row['BuyOrderCancelTime'],$row['KEK'],$row['Live7DChange'],$row['CoinModeRule'],$row['OrderDate'],$row['PctToSave'],$row['SpreadBetRuleID'],$row['SpreadBetTransactionID'],$row['RedirectPurchasesToSpread'] //32
    ,$row['SpreadBetRuleIDRedirect'],$row['MinsToPauseAfterPurchase'],$row['OriginalAmount'],$row['SaveResidualCoins']);
  }
  $conn->close();
  return $tempAry;
}

function pausePurchases($UserID){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `User` SET `DisableUntil`= date_add(now(),Interval (select `MinsToPauseAfterPurchase` from `UserConfig` WHERE `ID` = $UserID) MINUTE) WHERE `ID` = $UserID";
    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("pausePurchases",$sql,3,0,"SQL","UserID:$UserID");
    logAction("pausePurchases: ".$sql, 'BuySell', 0);
}


function deleteFromBittrexAction($bittrexRef){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `BittrexAction` SET `Status`= 'Closed', `CompletionDate` = NOW() WHERE `BittrexRef`= '$bittrexRef'";
    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("deleteFromBittrexAction",$sql,3,0,"SQL","BittrexRef:$bittrexRef");
    logAction("deleteFromBittrexAction: ".$sql, 'BuySell', 0);
}

function updateBuyToSpread($sbRuleID, $transactionID){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `Transaction` SET `Type` = 'SpreadBuy', `SpreadBetRuleID` = $sbRuleID, `SpreadBetTransactionID` = (SELECT `ID` FROM `SpreadBetTransactions` WHERE `SpreadBetRuleID` = $sbRuleID ) where `ID` = $transactionID";
    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("updateBuyToSpread",$sql,3,0,"SQL","SBRuleID:$sbRuleID TransID:$transactionID");
    logAction("updateBuyToSpread: ".$sql, 'BuySell', 0);
}

function updateSQLSold($amount,$livePrice, $cost, $date, $transactionID,$profit){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    if ($baseCurrency == "BTC"){
        $sql = "UPDATE `Transaction` SET `Status` = 'Sold', `SellPrice` = $livePrice, `Profit` =  $profit, `DateSold` = '$date' WHERE `ID` = $transactionID";
    }
    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("updateSQLSold",$sql,3,0,"SQL","ransID:$transactionID");
    logAction("updateSQLSold: ".$sql, 'BuySell', 0);
}

function bittrexOrder($apikey, $apisecret, $uuid, $versionNum){
    $nonce=time();
    if ($versionNum == 1){
      $uri='https://bittrex.com/api/v1.1/account/getorder?apikey='.$apikey.'&uuid='.$uuid.'&nonce='.$nonce;
      echo "<br>$uri<br>";
      $sign=hash_hmac('sha512',$uri,$apisecret);
      $ch = curl_init($uri);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $execResult = curl_exec($ch);
      $obj = json_decode($execResult, true);
      //$balance = $obj["result"]["IsOpen"];
    }elseif ($versionNum == 3){
      $timestamp = time()*1000;
      $url = "https://api.bittrex.com/v3/orders/{".$uuid."}";
      $method = "GET";
      $content = '';
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
      $execResult = curl_exec($ch);
      curl_close($ch);
      $obj = json_decode($execResult, true);
    }

    return $obj;
}

function getTrackingCoins(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "SELECT `ID`,`Symbol`,`LiveBuyOrders`,`LastBuyOrders`,`BuyOrdersPctChange`,`LiveMarketCap`,`LastMarketCap`,`MarketCapPctChange`,`Live1HrChange`,`Last1HrChange`,`Hr1ChangePctChange`,`Live24HrChange`,`Last24HrChange`,`Hr24ChangePctChange`,`Live7DChange`,`Last7DChange`
    ,`D7ChangePctChange`,`LiveCoinPrice`,`LastCoinPrice`,`CoinPricePctChange`,`LiveSellOrders`,`LastSellOrders`,`SellOrdersPctChange`,`LiveVolume`,`LastVolume`,`VolumePctChange`,`BaseCurrency`,`Price4Trend`,`Price3Trend`, `LastPriceTrend`, `LivePriceTrend`,`1HrPriceChangeLive`
    ,`1HrPriceChangeLast`,`1HrPriceChange3`,`1HrPriceChange4`,`SecondstoUpdate`,`LastUpdated`,`Name`,`Image`,`DoNotBuy`
    FROM `CoinStatsView` ORDER BY `Symbol` ASC";
    //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['ID'],$row['Symbol'],$row['LiveBuyOrders'],$row['LastBuyOrders'],$row['BuyOrdersPctChange'],$row['LiveMarketCap'],$row['LastMarketCap'],$row['MarketCapPctChange'],$row['Live1HrChange'],$row['Last1HrChange'],$row['Hr1ChangePctChange'] //10
    ,$row['Live24HrChange'],$row['Last24HrChange'],$row['Hr24ChangePctChange'],$row['Live7DChange'],$row['Last7DChange'],$row['D7ChangePctChange'],$row['LiveCoinPrice'],$row['LastCoinPrice'],$row['CoinPricePctChange'],$row['LiveSellOrders'],$row['LastSellOrders']//21
    ,$row['SellOrdersPctChange'],$row['LiveVolume'],$row['LastVolume'],$row['VolumePctChange'],$row['BaseCurrency'],$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['1HrPriceChangeLive'],$row['1HrPriceChangeLast'],$row['1HrPriceChange3'] //33
    ,$row['1HrPriceChange4'],$row['SecondstoUpdate'],$row['LastUpdated'],$row['Name'],$row['Image'],$row['DoNotBuy']);
  }
  $conn->close();
  return $tempAry;
}

function getTrackingSellCoins($userID = 0){
  $tempAry = [];
  if ($userID <> 0){ $whereclause = "Where `UserID` = $userID";}else{$whereclause = "";}
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `ID`,`Type`,`CoinID`,`UserID`,`CoinPrice`,`Amount`,`Status`,`OrderDate`,`CompletionDate`,`BittrexID`,`OrderNo`,`Symbol`,`LastBuyOrders`, `LiveBuyOrders`,`BuyOrdersPctChange`,`LastMarketCap`,`LiveMarketCap`,`MarketCapPctChange`,`LastCoinPrice`,`LiveCoinPrice`,`CoinPricePctChange`,`LastSellOrders`
  ,`LiveSellOrders`,`SellOrdersPctChange`,`LastVolume`,`LiveVolume`,`VolumePctChange`,`Last1HrChange`,`Live1HrChange`,`Hr1PctChange`,`Last24HrChange`,`Live24HrChange`,`Hr24PctChange`,`Last7DChange`,`Live7DChange`,`D7PctChange`,`BaseCurrency`
  , `Price4Trend`,`Price3Trend`,`LastPriceTrend`,`LivePriceTrend`,`FixSellRule`,`SellRule`,`BuyRule`,`ToMerge`,`LowPricePurchaseEnabled`,`PurchaseLimit`,`PctToPurchase`,`BTCBuyAmount`,`NoOfPurchases`,`Name`,`Image`,`MaxCoinMerges`,`NoOfCoinSwapsThisWeek`
  ,@OriginalPrice:=`CoinPrice`*`Amount` as OriginalPrice, @CoinFee:=((`CoinPrice`*`Amount`)/100)*0.28 as CoinFee, @LivePrice:=`LiveCoinPrice`*`Amount` as LivePrice, @coinProfit:=@LivePrice-@OriginalPrice-@CoinFee as ProfitUSD, @ProfitPct:=(@coinProfit/@OriginalPrice)*100 as ProfitPct
  ,`CaptureTrend`
  FROM `SellCoinStatsView` $whereclause order by @ProfitPct Desc ";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['ID'],$row['Type'],$row['CoinID'],$row['UserID'],$row['CoinPrice'],$row['Amount'],$row['Status'],$row['OrderDate'],$row['CompletionDate'],$row['BittrexID'],$row['OrderNo'],
    $row['Symbol'],$row['LastBuyOrders'],$row['LiveBuyOrders'],$row['BuyOrdersPctChange'],$row['LastMarketCap'],$row['LiveMarketCap'],$row['MarketCapPctChange'],$row['LastCoinPrice'],$row['LiveCoinPrice'], //19
    $row['CoinPricePctChange'],$row['LastSellOrders'],$row['LiveSellOrders'],$row['SellOrdersPctChange'],$row['LastVolume'],$row['LiveVolume'],$row['VolumePctChange'],$row['Last1HrChange'],$row['Live1HrChange'],$row['Hr1PctChange'],$row['Last24HrChange'],$row['Live24HrChange'] //31
    ,$row['Hr24PctChange'],$row['Last7DChange'],$row['Live7DChange'],$row['D7PctChange'],$row['BaseCurrency'],$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['FixSellRule'],$row['SellRule'],$row['BuyRule'] //43
    ,$row['ToMerge'],$row['LowPricePurchaseEnabled'],$row['PurchaseLimit'],$row['PctToPurchase'],$row['BTCBuyAmount'],$row['NoOfPurchases'],$row['Name'],$row['Image'],$row['MaxCoinMerges'],$row['NoOfCoinSwapsThisWeek'],$row['CaptureTrend']);
  }
  $conn->close();
  return $tempAry;
}
function bittrexCoinStats($apikey, $apisecret, $symbol, $baseCurrency, $versionNum){
    $nonce=time();
    //$uri='https://bittrex.com/api/v1.1/account/getbalance?apikey='.$apikey.'&currency=BTC&nonce='.$nonce;
    if ($versionNum == 1){
      $uri='https://bittrex.com/api/v1.1/public/getmarketsummary?market='.$baseCurrency.'-'.$symbol;
      print_r($uri);
      $sign=hash_hmac('sha512',$uri,$apisecret);
      $ch = curl_init($uri);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $execResult = curl_exec($ch);
      $obj = json_decode($execResult, True);
    }elseif ($versionNum == 3){
      $timestamp = time()*1000;
      $url = "https://api.bittrex.com/v3/markets/".$symbol.'-'.$baseCurrency."/summary";
      //$url = "https://api.bittrex.com/v3/markets/".$symbol."/summary";
      echo "<BR> $url";
      $method = "GET";
      $content = "";
      $subaccountId = "";
      $contentHash = hash('sha512',  $content);
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
      $execResult = curl_exec($ch);
      curl_close($ch);
      $obj = json_decode($execResult, True);
    }
    return $obj;
}

function getUserRules(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
//12
  $sql = "SELECT `UserID`,`BuyOrdersEnabled`,`BuyOrdersTop`,`BuyOrdersBtm`,`MarketCapEnabled`,`MarketCapTop`,`MarketCapBtm`,`1HrChangeEnabled`,`1HrChangeTop`,`1HrChangeBtm`,`24HrChangeEnabled`,`24HrChangeTop`,`24HrChangeBtm`
  ,`7DChangeEnabled`,`7DChangeTop`,`7DChangeBtm`,`CoinPriceEnabled`,`CoinPriceTop`,`CoinPriceBtm`,`SellOrdersEnabled`,`SellOrdersTop`,`SellOrdersBtm`,`VolumeEnabled`,`VolumeTop`,`VolumeBtm`,`BuyCoin`,`SendEmail`,`BTCAmount`
  ,`Email`,`UserName`,`APIKey`,`APISecret`,`EnableDailyBTCLimit`,`DailyBTCLimit`,`EnableTotalBTCLimit`,`TotalBTCLimit`,`RuleID`,`BuyCoinOffsetPct`,`BuyCoinOffsetEnabled`,`PriceTrendEnabled`, `Price4Trend`, `Price3Trend`
  , `LastPriceTrend`, `LivePriceTrend`,`Active`,`DisableUntil`,`BaseCurrency`,`NoOfCoinPurchase`,`BuyType`,`TimeToCancelBuyMins`,`BuyPriceMinEnabled`,`BuyPriceMin`,`LimitToCoin`,`AutoBuyCoinEnabled`,`AutoBuyPrice`
  ,`BuyAmountOverrideEnabled`, `BuyAmountOverride`,`NewBuyPattern`,`KEK`,`SellRuleFixed`,`OverrideDailyLimit`,`CoinPricePatternEnabled`,`CoinPricePattern`,`1HrChangeTrendEnabled`,`1HrChangeTrend`,`BuyRisesInPrice`
  ,`TotalProfitPauseEnabled`,`TotalProfitPause`,`PauseRulesEnabled`,`PauseRules`,`PauseHours`,`MarketDropStopEnabled`,`MarketDropStopPct`,`OverrideDisableRule`,`LimitBuyAmountEnabled`,`LimitBuyAmount`,`OverrideCancelBuyTimeEnabled`
  ,`OverrideCancelBuyTimeMins`,`NoOfBuyModeOverrides`,`CoinModeOverridePriceEnabled`
  FROM `UserBuyRules` where `BuyCoin` = 1";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['UserID'],$row['BuyOrdersEnabled'],$row['BuyOrdersTop'],$row['BuyOrdersBtm'],$row['MarketCapEnabled'],$row['MarketCapTop'], //5
    $row['MarketCapBtm'],$row['1HrChangeEnabled'],$row['1HrChangeTop'],$row['1HrChangeBtm'],$row['24HrChangeEnabled'],$row['24HrChangeTop'],$row['24HrChangeBtm'], //12
    $row['7DChangeEnabled'],$row['7DChangeTop'],$row['7DChangeBtm'],$row['CoinPriceEnabled'],$row['CoinPriceTop'],$row['CoinPriceBtm'],$row['SellOrdersEnabled'],$row['SellOrdersTop'], //20
    $row['SellOrdersBtm'],$row['VolumeEnabled'],$row['VolumeTop'],$row['VolumeBtm'],$row['BuyCoin'],$row['SendEmail'],$row['BTCAmount'],$row['Email'],$row['UserName'],$row['APIKey'], //30
    $row['APISecret'],$row['EnableDailyBTCLimit'],$row['DailyBTCLimit'],$row['EnableTotalBTCLimit'],$row['TotalBTCLimit'],$row['RuleID'],$row['BuyCoinOffsetPct'],$row['BuyCoinOffsetEnabled'], //38
    $row['PriceTrendEnabled'],$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['Active'],$row['DisableUntil'],$row['BaseCurrency'],$row['NoOfCoinPurchase'], //47
    $row['BuyType'],$row['TimeToCancelBuyMins'],$row['BuyPriceMinEnabled'],$row['BuyPriceMin'],$row['LimitToCoin'],$row['AutoBuyCoinEnabled'],$row['AutoBuyPrice'],$row['BuyAmountOverrideEnabled']  //55
    ,$row['BuyAmountOverride'],$row['NewBuyPattern'],$row['KEK'],$row['SellRuleFixed'],$row['OverrideDailyLimit'],$row['CoinPricePatternEnabled'],$row['CoinPricePattern'],$row['1HrChangeTrendEnabled'],$row['1HrChangeTrend'] //64
    ,$row['BuyRisesInPrice'],$row['TotalProfitPauseEnabled'],$row['TotalProfitPause'],$row['PauseRulesEnabled'],$row['PauseRules'],$row['PauseHours'],$row['MarketDropStopEnabled'],$row['MarketDropStopPct']
    ,$row['OverrideDisableRule'],$row['LimitBuyAmountEnabled'],$row['LimitBuyAmount'],$row['OverrideCancelBuyTimeEnabled'],$row['OverrideCancelBuyTimeMins'],$row['NoOfBuyModeOverrides'],$row['CoinModeOverridePriceEnabled']);
  }
  $conn->close();
  return $tempAry;
}

function getUserSellRules(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `ID`,`UserID`,`SellCoin`,`SendEmail`,`BuyOrdersEnabled`,`BuyOrdersTop`,`BuyOrdersBtm`,`MarketCapEnabled`,`MarketCapTop`,`MarketCapBtm`,`1HrChangeEnabled`,`1HrChangeTop`,
  `1HrChangeBtm`,`24HrChangeEnabled`,`24HrChangeTop`,`24HrChangeBtm`,`7DChangeEnabled`,`7DChangeTop`,`7DChangeBtm`,`ProfitPctEnabled`,`ProfitPctTop`,`ProfitPctBtm`,`CoinPriceEnabled`,
  `CoinPriceTop`,`CoinPriceBtm`,`SellOrdersEnabled`,`SellOrdersTop`,`SellOrdersBtm`,`VolumeEnabled`,`VolumeTop`,`VolumeBtm`,`Email`,`UserName`,`APIKey`,`APISecret`, `SellCoinOffsetEnabled`,
  `SellCoinOffsetPct`,`SellPriceMinEnabled`,`SellPriceMin`,`LimitToCoin`,`KEK`,`SellPatternEnabled`,`SellPattern`,`LimitToBuyRule`,`CoinPricePatternEnabled`,`CoinPricePattern`,`AutoSellCoinEnabled`
  ,`SellFallsInPrice`,`SellAllCoinsEnabled`,`SellAllCoinsPct`,`CoinSwapEnabled`,`CoinSwapAmount`,`NoOfCoinSwapsPerWeek`,`MergeCoinEnabled`,`CoinModeRule`
   FROM `UserSellRules` WHERE `SellCoin` = 1";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['ID'],$row['UserID'],$row['SellCoin'],$row['SendEmail'],$row['BuyOrdersEnabled'],$row['BuyOrdersTop'],$row['BuyOrdersBtm'],$row['MarketCapEnabled'],$row['MarketCapTop'],$row['MarketCapBtm'],$row['1HrChangeEnabled'] //10
    ,$row['1HrChangeTop'],$row['1HrChangeBtm'],$row['24HrChangeEnabled'],$row['24HrChangeTop'],$row['24HrChangeBtm'],$row['7DChangeEnabled'],$row['7DChangeTop'],$row['7DChangeBtm'],$row['ProfitPctEnabled'],$row['ProfitPctTop'],$row['ProfitPctBtm']  //21
    ,$row['CoinPriceEnabled'],$row['CoinPriceTop'],$row['CoinPriceBtm'],$row['SellOrdersEnabled'],$row['SellOrdersTop'],$row['SellOrdersBtm'],$row['VolumeEnabled'],$row['VolumeTop'],$row['VolumeBtm'],$row['Email'],$row['UserName'],$row['APIKey'] //33
    ,$row['APISecret'],$row['SellCoinOffsetEnabled'],$row['SellCoinOffsetPct'],$row['SellPriceMinEnabled'],$row['SellPriceMin'],$row['LimitToCoin'],$row['KEK'],$row['SellPatternEnabled'],$row['SellPattern'],$row['LimitToBuyRule'] //43
    ,$row['CoinPricePatternEnabled'],$row['CoinPricePattern'],$row['AutoSellCoinEnabled'],$row['SellFallsInPrice'],$row['SellAllCoinsEnabled'],$row['SellAllCoinsPct'],$row['CoinSwapEnabled'],$row['CoinSwapAmount'],$row['NoOfCoinSwapsPerWeek']
    ,$row['MergeCoinEnabled'],$row['CoinModeRule']);
  }
  $conn->close();
  return $tempAry;
}

function newPrice($bitPrice, $pct, $action){
  if ($action == "Buy"){
    //echo "<BR> 1: ".number_format((float)$bitPrice, 8, '.', '');
    $bitPrice = $bitPrice-(($bitPrice/100)*$pct);
    //echo "<BR> 2: ".number_format((float)$bitPrice, 8, '.', '');
    return round($bitPrice,8, PHP_ROUND_HALF_DOWN);
  }else{
    $bitPrice = $bitPrice+(($bitPrice/100)*$pct);
    //$bitPrice = round($newPrice,8, PHP_ROUND_HALF_UP);
    return round($bitPrice,8, PHP_ROUND_HALF_UP);
  }
}

function returnBuyAmount($coin, $baseCurrency, $btcBuyAmount, $buyType, $BTCBalance, $bitPrice,$apikey,$apisecret){
  //Convert USD to BTC/ETH/BCH
  //$btcBuyAmount = $btcBuyAmount/$bitPrice;
  $testFlag = 0;
  echo "<BR> 0: returnBuyAmount($coin, $baseCurrency, $btcBuyAmount, $buyType, $BTCBalance, $bitPrice,$apikey,$apisecret){";
  if ($btcBuyAmount == 0){
    echo "<BR> 1: $BTCBalance - (($BTCBalance/ 100 ) * 0.28) : ";
    $returnPrice = $BTCBalance - (($BTCBalance/ 100 ) * 0.28);
    echo " $returnPrice ";
    $testFlag = 1;
  }elseif ($btcBuyAmount > 0 && $buyType == 0){
      echo "<BR> 2: returnPrice = ($BTCBalance/100)*$btcBuyAmount; ";
      //$returnPrice = ($btcBuyAmount) - (($BTCBalance/ 100 ) * 0.28);
      $returnPrice = ($BTCBalance/100)*$btcBuyAmount;
      echo " : $returnPrice ";
      $testFlag = 2;
    }elseif ($btcBuyAmount > 0 && $buyType == 1){
      echo "<BR> 3: ($btcBuyAmount) ";
      //$returnPrice = ($BTCBalance*($btcBuyAmount/100))- (($BTCBalance/ 100 ) * 0.28);
      $returnPrice = $btcBuyAmount/$bitPrice;
      echo " $returnPrice ";
      $testFlag = 3;
    }

   if ($returnPrice > $BTCBalance) {
     $returnPrice = $BTCBalance - (($BTCBalance/ 100 ) * 0.28);
     echo "<BR> 4: $returnPrice = $returnPrice > $BTCBalance ";
   }
   LogToSQL("BuyCoinTest","returnBuyAmount: $returnPrice | $BTCBalance | $btcBuyAmount | $testFlag",3,1);
   //echo "<BR> Balance : $BTCBalance ";
   //if ($BTCBalance < 20.00){$returnPrice == 0;}

   return $returnPrice;
}

function buyCoins($apikey, $apisecret, $coin, $email, $userID, $date,$baseCurrency, $sendEmail, $buyCoin, $btcBuyAmount, $ruleID,$userName, $coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed, $buyPriceCoin,$overrideCoinAlloc,$noOfPurchases = 0){
  $apiVersion = 3;
  $retBuy = False;
  $BTCBalance = bittrexbalance($apikey, $apisecret,$baseCurrency, $apiVersion);
  if ($baseCurrency == 'USDT'){ $buyMin = 20.00;}
  elseif ($baseCurrency == 'BTC'){ $buyMin = 0.003;}
  elseif ($baseCurrency == 'ETH'){ $buyMin = 0.148;}
  //get min trade
  //if ($buyType == 2){
    //$btcBuyAmount = ($BTCBalance/100.28)*100;
  //    $btcBuyAmount = ($BTCBalance/100.28)*$btcBuyAmount;
  //}

  //if ($btcBuyAmount == 0){
  //  $charges = ($BTCBalance / 100 ) * 0.28;
  //  $btcBuyAmount = $BTCBalance - $charges;
  //}

//if ($buyAmountOverrideEnabled == 1 AND $buyAmountOverride > 0) {
//    $btcBuyAmount = $buyAmountOverride;

//  }
  if ($buyPriceCoin == 0){
    $bitPrice = number_format((float)(bittrexCoinPrice($apikey, $apisecret,$baseCurrency,$coin,$apiVersion)), 8, '.', '');
  }else{
    $bitPrice = $buyPriceCoin;
  }
  echo "<br> returnBuyAmount($coin, $baseCurrency, $btcBuyAmount, $buyType, $BTCBalance, $bitPrice, $apikey, $apisecret);";
  $btcBuyAmount = returnBuyAmount($coin, $baseCurrency, round($btcBuyAmount,10), $buyType, $BTCBalance, round($bitPrice,8), $apikey, $apisecret);
  echo "<BR> btcBuyAmount $btcBuyAmount ";
  $subject = "Coin Alert: ".$coin;
  $from = 'Coin Alert <alert@investment-tracker.net>';
  echo "<BR>Balance: $BTCBalance";
  $minTradeAmount = getMinTradeFromSQL($coinID);
  if ($buyCoin) {
    $subject = "Coin Purchase: ".$coin;
    $from = 'Coin Purchase <purchase@investment-tracker.net>';
  }
  //$btcwithCharge = $btcBuyAmount - (($btcBuyAmount/100)*0.28);
  //echo "<BR> btcwithCharge $btcwithCharge = $btcBuyAmount - (($btcBuyAmount/100)*0.28);";
  //if ($btcBuyAmount > $minTradeAmount) {
    echo "buy Coin - Balance Sufficient";
    //$bitPrice = number_format((float)(bittrexCoinPrice($apikey, $apisecret,$baseCurrency,$coin,$apiVersion)), 8, '.', '');
    if ($CoinSellOffsetEnabled == 1){

      $bitPrice = number_format((float)newPrice($bitPrice,$CoinSellOffsetPct, "Buy"), 8, '.', '');
    }
    //$livePrice = getLiveCoinPrice($tracking[$x][0]);
    //$avgCoinPrice = getAveragePrice($coin);
    //echo "<BR>AvgCoinPrice: ".$avgCoinPrice[0][0]." CoinPrice: ".$bitPrice;
    //if ($avgCoinPrice > $bitPrice){ return; }
    //$quantity = Round($btcBuyAmount/$bitPrice,8,PHP_ROUND_HALF_UP);
    if ($btcBuyAmount>$minTradeAmount[0][0] && $BTCBalance >= $buyMin){
        echo "Quantity above min trade amount";
        //buyCoins($apikey, $apisecret,$coin, $quantity, $bitPrice, $email,$minTradeAmount, $userID, $totalScore,$date, $baseCurrency);
        $orderNo = "ORD".$coin.date("YmdHis", time()).$ruleID;
        echo "Buy Coin = $buyCoin";
        if ($buyCoin){
          $btcBuyAmount = round($btcBuyAmount,10);
          $bitPrice = round($bitPrice,8);
          $obj = bittrexbuy($apikey, $apisecret, $coin, $btcBuyAmount, $bitPrice, $baseCurrency,$apiVersion,FALSE);
          LogToSQL("BuyCoinTest","bittrexbuy($apikey, $apisecret, $coin, $btcBuyAmount, $bitPrice, $baseCurrency,$apiVersion,FALSE);",3,1);
          //writeSQLBuy($coin, $quantity, $bitPrice, $date, $orderNo, $userID, $baseCurrency);
          logToSQL("Bittrex", "bittrexbuy($apikey, $apisecret, $coin, $btcBuyAmount, $bitPrice, $baseCurrency,$apiVersion,FALSE);", $userID,1);
          if ($apiVersion == 1){$bittrexRef = $obj["result"]["uuid"];$status = $obj["success"];}
          else{$bittrexRef = $obj["id"];
            if ($obj['status'] == 'OPEN'){$status = 1; }else{$status = 0;} }

          logToSQL("AddBuyCoin", "$bittrexRef $status ".$obj['status']." $coinID $bitPrice $btcBuyAmount $orderNo", $userID,1);
          if ($bittrexRef <> ""){
            $retBuy = True;
            echo "bittrexBuyAdd($coinID, $userID, 'Buy', $bittrexRef, $status, $ruleID, $bitPrice, $btcBuyAmount, $orderNo);";
            date_default_timezone_set('Asia/Dubai');
            //$tmpTime = $timeToCancelBuyMins;
            //$newDate = date("Y-m-d H:i:s", time());
            //$current_date = date('Y-m-d H:i:s');
            //$newTime = date("Y-m-d H:i:s",strtotime('+'.$timeToCancelBuyMins.'Mins', strtotime($current_date)));
            //$buyCancelTime = strtotime( '+ 16 minute');
            bittrexBuyAdd($coinID, $userID, 'Buy', $bittrexRef, 1, $ruleID, $bitPrice, $btcBuyAmount, $orderNo,$timeToCancelBuyMins);
            bittrexAddNoOfPurchases($bittrexRef,$noOfPurchases);
            addBuyRuletoSQL($bittrexRef,$ruleID,$SellRuleFixed);
            logToSQL("Bittrex", "Add Buy Coin $bitPrice $btcBuyAmount $orderNo", $userID,1);
            //CustomisedSellRule($ruleID,$SellRuleFixed,$coinID);
            //writeBittrexActionBuy($coinID,$userID,'Buy',$bittrexRef,$date,$status,$bitPrice,$ruleID);
            //if ($SellRuleFixed !== "ALL"){writeFixedSellRule($SellRuleFixed,$bittrexRef);}
            addCoinAllocationOverride($overrideCoinAlloc,$bittrexRef);

          }
          logAction("Bittrex Status:  ".json_encode($obj), 'BuySell', 0);
          logToSQL("Bittrex", "Add Buy Coin Error: ".json_encode($obj), $userID,1);
        }
        if ($sendEmail==1 && $buyCoin ==0){
        //if ($sendEmail){
          sendEmail($email, $coin, $btcBuyAmount, $bitPrice, $orderNo, $score, $subject,$userName, $from);
        }
    }else{
      echo "<BR> BITTREX BALANCE INSUFFICIENT $coin: $btcBuyAmount>".$minTradeAmount[0][0];
      logAction("BITTREX BALANCE INSUFFICIENT $coin: $btcBuyAmount>".$minTradeAmount[0][0]." && $BTCBalance >= $buyMin", 'BuySell', 0);
      logToSQL("Bittrex", "BITTREX BALANCE INSUFFICIENT $coin: $btcBuyAmount>".$minTradeAmount[0][0]." && $BTCBalance >= $buyMin", $userID,1);
    }
  //}
  return $retBuy;
}

function addCoinAllocationOverride($overrideCoinAlloc, $bittrexRef){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `Transaction` SET `OverrideCoinAllocation`= $overrideCoinAlloc WHERE `BittrexRef` = $bittrexRef";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    newLogToSQL("addCoinAllocationOverride",$sql,3,0,"SQL","BittrexID:$bittrexRef");
    $conn->close();
}

function writeFixedSellRule($SellRuleFixed,$bittrexRef){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `Transaction` SET `FixSellRule`= '$SellRuleFixed' WHERE `BittrexRef` = '$bittrexRef'";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    newLogToSQL("writeFixedSellRule",$sql,3,0,"SQL","BittrexRef:$bittrexRef");
    $conn->close();
}

function writeSQLTransBuy($type, $coinID,$userID, $cost,$amounttobuy, $date, $BittrexID, $orderNo){
  $currentDate = date("Y-m-d H:i:s", time());
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "INSERT INTO `Transaction`(`Type`, `CoinID`, `UserID`, `CoinPrice`, `Amount`, `Status`, `OrderDate`, `CompletionDate`, `BittrexID`, `OrderNo`)
    VALUES ('$type',$coinID, $userID,$cost,$amounttobuy,'Open', '$date','$currentDate' ,$BittrexID,'$orderNo')";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    logAction("writeSQLTransBuy: ".$sql, 'BuySell', 0);
}

function writeBittrexActionBuy($coinID,$transactionID,$userID,$type,$bittrexRef,$date,$status,$sellPrice,$ruleID){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "INSERT INTO `BittrexAction`(`CoinID`, `TransactionID`, `UserID`, `Type`, `BittrexRef`, `ActionDate`, `CompletionDate`, `Status`, `SellPrice`, `RuleID`)
    VALUES ($coinID,$transactionID,$userID,'$type','$bittrexRef','$date','$status',$sellPrice,$ruleID)";
    //VALUES ('$type','$apikey', '$apisecret','$coin', '$email', $userID, $totalScore,'$date', '$baseCurrency',$sendEmail,$buyCoin,'$ruleID','$userName','$orderNo',$newBTCAmount,$bitPrice,'$status','$bittrexRef')";
    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    logAction("writeBittrexActionBuy: ".$sql, 'BuySell', 0);
}

function sendEmail($to, $symbol, $amount, $cost, $orderNo, $score, $subject, $user, $from){
    $body = "Dear ".$user.", <BR/>";
    $body .= "Congratulations you have bought the following Coin: "."<BR/>";
    $body .= "Coin: ".$symbol." Amount: ".$amount." Price: ".$cost."<BR/>";
    $body .= "Order Number: ".$orderNo."<BR/>";
    $body .= "Score: ".$score."<BR/>";
    $body .= "Kind Regards\nCryptoBot.";
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    //$headers .= 'From: Alerts <Alerts@Investment-Tracker.net>' . "\r\n";
    $headers .= "From:".$from."\r\n";
    $headers .= "To:".$to."\r\n";
    mail($to, $subject, wordwrap($body,70),$headers);
}

function bittrexbalance($apikey, $apisecret, $base, $versionNum){
    $nonce=time();
    if ($versionNum == 1){
        $uri='https://bittrex.com/api/v1.1/account/getbalance?apikey='.$apikey.'&currency='.$base.'&nonce='.$nonce;
        $sign=hash_hmac('sha512',$uri,$apisecret);
        $ch = curl_init($uri);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $execResult = curl_exec($ch);
        $obj = json_decode($execResult, true);
        $balance = $obj["result"]["Available"];
    }elseif ($versionNum == 3){
      $timestamp = time()*1000;
      $url = "https://api.bittrex.com/v3/balances/".$base;
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
      $execResult = curl_exec($ch);
      curl_close($ch);
      $temp = json_decode($execResult, true);
      $balance = $temp['total'];
    }
    return $balance;
}

function bittrexbuy($apikey, $apisecret, $symbol, $quant, $rate,$baseCurrency, $versionNum, $useAwards){
    Echo "bittrexbuy($apikey, $apisecret, $symbol, $quant, $rate,$baseCurrency)";
    $nonce=time();
    if ($versionNum == 1){
      $uri='https://bittrex.com/api/v1.1/market/buylimit?apikey='.$apikey.'&market='.$baseCurrency.'-'.$symbol.'&quantity='.$quant.'&rate='.$rate.'&nonce='.$nonce;
      echo $uri."<BR>";
      $sign=hash_hmac('sha512',$uri,$apisecret);
      $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $execResult = curl_exec($ch);
      $obj = json_decode($execResult, true);
    }elseif ($versionNum == 3){
      $timestamp = time()*1000;
      $url = "https://api.bittrex.com/v3/orders";
      $method = "POST";

      $content = '{
        "marketSymbol": "'.$symbol.'-'.$baseCurrency.'",
        "direction": "BUY",
        "type": "LIMIT",
        "quantity": "'.$quant.'",
        "limit": "'.$rate.'",
        "timeInForce": "GOOD_TIL_CANCELLED",
        "useAwards": "'.$useAwards.'"
      }';
      echo "<BR>".$content;
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
      curl_setopt($ch, CURLOPT_POST, TRUE);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
      $execResult = curl_exec($ch);
      curl_close($ch);
      $obj = json_decode($execResult, true);
    }

    return $obj;
}

function getMinTradeAmount($apiKey, $apisecret){
  $obj = getMinTrade($apiKey, $apisecret, 3);
  //$obj = json_decode($minTrade, true);
  //$minTradeSize = count($minTrade);
  $tradeArraySize = count($obj);
  //print_r($obj);
  $coins = getTrackingCoins();
  $coinsSize = count($coins);
  //echo "<BR> array sizes | TRadeArySize: ".$tradeArraySize." coinsSize: $coinsSize entry1: ";
  for ($x=0; $x<$coinsSize; $x++){
    $baseCurrency = $coins[$x][26]; $coin = $coins[$x][1]; $coinID = $coins[$x][0];
    //echo "<BR> COIN: $coin BASE: $baseCurrency ID: $coinID";
    //for($y = 0; $y < $tradeArraySize; $y++) {
    for ($y=0; $y<$tradeArraySize; $y++){
      //echo "<BR> Symbol: ".$obj[$y]['symbol']."|".$coin."-".$baseCurrency;
      if($obj[$y]['symbol'] == $coin."-".$baseCurrency){
        $minTradeAmount = $obj[$y]['minTradeSize'];
        $precision = $obj[$y]['precision'];
        //return $minTradeAmount;
        echo "<BR> Coin Match: $coin Base: $baseCurrency ID: $coinID Min: $minTradeAmount";
        copyTradeAmountToSQL($coinID, $minTradeAmount,$precision);
        continue;
      }
    }
  }

}

function getMinTradeFromSQL($coinID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `MinTradeSize` FROM `Coin` WHERE `ID` = $coinID";
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['MinTradeSize']);}
  $conn->close();
  return $tempAry;
}

function copyTradeAmountToSQL($coinID, $minTradeAmount, $precision){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `Coin` SET `MinTradeSize`= $minTradeAmount, `CoinPrecision` = $precision WHERE `ID` = $coinID";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("copyTradeAmountToSQL",$sql,3,0,"SQL","CoinID:$coinID");
  logAction("copyTradeAmountToSQL: ".$sql, 'BuySell', 0);
}

function getMinTrade($apikey, $apisecret, $versionNum){
  $nonce=time();
  //$uri='https://bittrex.com/api/v1.1/account/getbalance?apikey='.$apikey.'&currency=BTC&nonce='.$nonce;
  if ($versionNum == 1){
      $uri="https://bittrex.com/api/v1.1/public/getmarkets";
      $sign=hash_hmac('sha512',$uri,$apisecret);
      $ch = curl_init($uri);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $execResult = curl_exec($ch);
      $obj = json_decode($execResult, true);
  }elseif ($versionNum == 3){
    $timestamp = time()*1000;
    $url = "https://api.bittrex.com/v3/markets";
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
    $obj = curl_exec($ch);
    curl_close($ch);
    $obj = json_decode($obj,true);
  }


  //$balance = $obj["result"]["MinTradeSize"];
  return $obj;
}

function bittrexCoinPrice($apikey, $apisecret, $baseCoin, $coin, $versionNum){
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
        $url = "https://api.bittrex.com/v3/markets/".$coin."-".$baseCoin."/ticker";
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
        $balance = $temp['lastTradeRate'];
      }

      return $balance;
}

function writeBittrexAction($coinID,$transactionID,$userID,$type,$bittrexRef,$date,$status,$sellPrice){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "INSERT INTO `BittrexAction`( `CoinID`, `TransactionID`, `UserID`, `Type`, `BittrexRef`, `ActionDate`,  `Status`, `SellPrice`)
    VALUES ($coinID,$transactionID,$userID,'$type','$bittrexRef','$date','$status' , $sellPrice)";
    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    logAction("writeBittrexAction: ".$sql, 'BuySell', 0);
}

function getCoinMarketCapStats(){
    $limit = 99;
    $start = 0;
    for($n=0;$n<5;$n++){
      $cnmkt = "https://api.coinmarketcap.com/v1/ticker/?limit=".$limit."&start=".$start;
      echo "<BR> CNMKT: ".$cnmkt;
      $fgc = json_decode(file_get_contents($cnmkt), true);
      for($i=0;$i<$limit;$i++){
        //echo "<br> : ".$fgc[$i]["symbol"]." : ".$fgc[$i]["market_cap_usd"];
          $tmpCoinPrice[] = Array($fgc[$i]["symbol"],$fgc[$i]["market_cap_usd"],$fgc[$i]["percent_change_1h"],$fgc[$i]["percent_change_24h"],$fgc[$i]["percent_change_7d"],$fgc[$i]["percent_change_30d"],$fgc[$i]["id"]);
      }
      $start = $start + $limit + 1;
    }
    logAction("$cnmkt | ".json_decode($tmpCoinPrice),'CMC', 0);
  return $tmpCoinPrice;
}

function getCMCID($symbol){
    $temp = "";
    $symbol_str = explode(",",$symbol);
    $symbolCount = count($symbol_str);
    for ($x = 0; $x < $symbolCount; $x++) {
      //echo $symbol_str[$x];
      if ($symbol_str[$x] == "BTC"){$temp =$temp."1,";}
      elseif ($symbol_str[$x] == "ETH"){$temp =$temp."1027,";}
      elseif ($symbol_str[$x] == "BCH"){$temp =$temp."1831,";}
      elseif ($symbol_str[$x] == "XRP"){$temp =$temp."52,";}
    }
    return rtrim($temp, ',');
}

function newCoinMarketCapStats($coinMarketID){
  //$coinMarketID = getCMCID($symbol);
  echo "<BR> Getting CMC Stats";
  $url = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest';
  $parameters = [
    'id' => $coinMarketID
  ];
  //echo "<BR> : $coinMarketID";
  $cmcKey = getCMCKey();
  $headers = [
    'Accepts: application/json',
    'X-CMC_PRO_API_KEY: '.$cmcKey
  ];
  $qs = http_build_query($parameters); // query string encode the parameters
  $request = "{$url}?{$qs}"; // create the request URL


  $curl = curl_init(); // Get cURL resource
  // Set cURL options
  curl_setopt_array($curl, array(
    CURLOPT_URL => $request,            // set the request URL
    CURLOPT_HTTPHEADER => $headers,     // set the headers
    CURLOPT_RETURNTRANSFER => 1         // ask for raw response instead of bool
  ));

  $response = curl_exec($curl); // Send the request, save the response
  $temp = json_decode($response, true);
  $tempCount = count($temp);
  //echo "<br>HERE! ".$temp['data'][1][1]['quote'][1]['market_cap'];
  //echo "<br>HERE5! ".$temp['data'][1]['quote']['USD']['market_cap'];
  //print_r($temp);
  //for($i=0;$i<$tempCount;$i++){
  echo "<BR> $coinMarketID";
  $coin = explode(",",$coinMarketID);
  $i = 1;
  $coinCount = count($coin);
  for($i=0;$i<$coinCount;$i++){
    $tempId = (Int)$coin[$i];
    echo "<BR> ".$temp['data'][$tempId]['symbol'];
    $tmpCMCAry[] = Array($temp['data'][$tempId]['symbol'],$temp['data'][$tempId]['quote']['USD']['market_cap'],$temp['data'][$tempId]['quote']['USD']['percent_change_1h'],
    $temp['data'][$tempId]['quote']['USD']['percent_change_24h'],$temp['data'][$tempId]['quote']['USD']['percent_change_7d'],$temp['data'][$tempId]['quote']['USD']['percent_change_30d'],$temp['data'][$tempId]['id']);
  }

  //echo "<BR> ".$temp['data'][52]['symbol'];

  //foreach ($temp as $item) {
    //Print_r($item);


    //echo "<BR>".$item[52]['symbol'];
    //
    //$i++;
  //}
  //print_r(json_decode($response)); // print json decoded response
  curl_close($curl); // Close request
  return $tmpCMCAry;
}

function CoinMarketCapStatstoSQL($coinID,$MarketCap,$hr1Change, $hr24Change, $d7Change){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call UpdateCMCStatstoSQL($coinID, $MarketCap,$hr1Change, $hr24Change, $d7Change);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("CoinMarketCapStatstoSQL($coinID,$MarketCap,$hr1Change, $hr24Change, $d7Change)",'CMC', 0);
  newLogToSQL("CoinMarketCapStatstoSQL","$sql",3,0,"SQL CALL","CoinID:$coinID");
}

function ResidualCoinsToSaving($amount, $orderNo, $transactionID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call ResidualCoinToSaving($amount, '$orderNo',$transactionID);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("ResidualCoinsToSaving($sql)",'SellCoin', 0);
  newLogToSQL("ResidualCoinsToSaving","$sql",3,1,"SQL CALL","TransactionID:$transactionID");
}

function BittrexStatstoSQL($coinID, $volume, $sellOrders, $buyOrders){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call UpdateBittrexStatstoSQL($coinID, $volume, $sellOrders, $buyOrders);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("BittrexStatstoSQL($coinID, $volume, $sellOrders, $buyOrders)",'CMC', 0);
  newLogToSQL("BittrexStatstoSQL","$sql",3,0,"SQL CALL","CoinID:$coinID");
}

function copyNewMarketCap($coinID,$MarketCap){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call UpdateCoinMarketCap($coinID, $MarketCap);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("UpdateCoinMarketCap","$sql",3,0,"SQL CALL","CoinID:$coinID");
}

function addBuyRuletoSQL($bittrexRef, $buyRule,$sellRule){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call AddBuyAndSellRules($buyRule,$sellRule, '$bittrexRef');";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("addBuyRuletoSQL","$sql",3,0,"SQL CALL","BittrexRef:$bittrexRef");
}

function addSellRuletoSQL($transactionID, $sellRule){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `Transaction` set `SellRule`= $buyRule WHERE `ID` = $transactionID";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("copyTradeAmountToSQL",$sql,3,0,"SQL","TransactionID:$transactionID");
}

function copyNewPctChange($coinID,$PctChange1Hr, $PctChange24Hr, $PctChange7D){
  $conn = getSQLConn(rand(1,3));
  Echo "<BR> call newUpdatePctChange($coinID, $PctChange1Hr, $PctChange24Hr, $PctChange7D);";
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call UpdatePctChange($coinID, $PctChange1Hr, $PctChange24Hr, $PctChange7D);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("copyNewPctChange","$sql",3,0,"SQL CALL","CoinID:$coinID");
}


function copyCoinVolume($coinID,$CoinVolume){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call UpdateCoinVolume($coinID, $CoinVolume);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("copyCoinVolume","$sql",3,0,"SQL CALL","CoinID:$coinID");
}

function getVolumeStats($stats, $apiVersion){
  $volume = 0.0; $symbol = "";  $high = 0.0; $low = 0.0;
  $OpenBuyOrders = 0; $OpenSellOrders = 0;
    if ($apiVersion == 1){
      foreach($stats['result'] as $item) {
        //print $item['MarketName'];
        $mktSym = explode("-",$item['MarketName']);
        $symbol = $mktSym[1];
        $market = $mktSym[0];
        $high = $item["High"];
        $low = $item["Low"];
        $volume = $item["Volume"];
        $last = $item["Last"];
        $BaseVolume = $item["BaseVolume"];
        $TimeStamp = $item["TimeStamp"];
        $Bid = $item["Bid"];
        $Ask = $item["Ask"];
        $OpenBuyOrders = $item["OpenBuyOrders"];
        $OpenSellOrders = $item["OpenSellOrders"];
        $PrevDay = $item["PrevDay"];
      }
    }else{
      //$mktSym = explode("-",$item['MarketName']);
      $symbol = $stats["symbol"];
      //$market = $mktSym[0];
      $high = $stats["high"];
      $low = $stats["low"];
      $volume = $stats["volume"];
      //$last = $item["Last"];
      //$BaseVolume = $item["BaseVolume"];
      //$TimeStamp = $item["TimeStamp"];
      //$Bid = $item["Bid"];
      //$Ask = $item["Ask"];
      $OpenBuyOrders = 0;
      $OpenSellOrders = 0;
      //$PrevDay = $item["PrevDay"];
    }

  $tempVolStats[] = Array($volume,$OpenBuyOrders,$OpenSellOrders);
  return $tempVolStats;
}

function copyCoinBuyOrders($coinID,$CoinBuyOrders){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call UpdateCoinBuyOrders($coinID, $CoinBuyOrders);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("copyCoinBuyOrders","$sql",3,0,"SQL CALL","CoinID:$coinID");
}

function copyCoinSellOrders($coinID,$CoinSellOrders){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call UpdateCoinSellOrders($coinID, $CoinSellOrders);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("copyCoinSellOrders","$sql",3,0,"SQL CALL","CoinID:$coinID");
}

function copyCoinPrice($coinID,$CoinPrice){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call NewUpdateCoinPrice($coinID, $CoinPrice);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("copyCoinPrice","$sql",3,0,"SQL CALL","CoinID:$coinID");
}
function copyWebTable($coinID){
  $conn = getSQLConn(rand(1,3));
  Echo "<BR> Updating Web table : $coinID";
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call UpdateWebTable($coinID);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("copyWebTable","$sql",3,0,"SQL CALL","CoinID:$coinID");
}
function updateWebCoinStatsTable($coinID){
  $conn = getSQLConn(rand(1,3));
  Echo "<BR> Update Web Coin Stats : call UpdateWebCoinStats($coinID);";
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call UpdateWebCoinStats($coinID);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("updateWebCoinStatsTable","$sql",3,0,"SQL CALL","CoinID:$coinID");
}


function buyWithScore($buyTop,$buyBtm,$score,$buyEnabled){
  if ($buyEnabled == 0){
      //print_r("True");
      return True;
      exit;
  }elseif ($buyTop >= $score && $buyBtm <= $score && $buyEnabled == 1){
      //print_r("True");
      $GLOBALS['allDisabled'] = true;
      return True;
      exit;
  }else {
    $GLOBALS['allDisabled'] = true;
    //print_r($buyTop >= $score);
    //print_r("False");
    return False;
  }
}

function buyWithMin($buyMinEnabled, $BuyMin, $LiveCoinPrice){
  //echo "BuyMin $BuyMin LiveBTCPrice $LiveCoinPrice";
  if ($buyMinEnabled == 0){
      //print_r("True");
      return True;
      exit;
  }elseif ($LiveCoinPrice >= $BuyMin){
      //echo "BuyMin $BuyMin LiveCoinPrice $LiveCoinPrice";
      $GLOBALS['allDisabled'] = true;
      return True;
      exit;
  }else {
    $GLOBALS['allDisabled'] = true;
    //print_r($buyTop >= $score);
    //print_r("False");
    return False;
  }
}

function sellWithMin($sellMinEnabled, $sellMin, $LiveCoinPrice, $LiveBTCPrice){
  //echo "BuyMin $sellMin LiveBTCPrice $LiveBTCPrice";
  if ($sellMinEnabled == 0){
      //print_r("True");
      return True;
      exit;
  }elseif ($LiveBTCPrice > $sellMin){
      //echo "BuyMin $BuyMin LiveBTCPrice $LiveBTCPrice";
      $GLOBALS['allDisabled'] = true;
      return True;
      exit;
  }else {
    $GLOBALS['allDisabled'] = true;
    //print_r($buyTop >= $score);
    //print_r("False");
    return False;
  }
}

function returnPattern($p3,$p2,$p1, $t3,$t2,$t1){
  $livePattern = $p3.$p2.$p1;
  $rulePattern = $t4.$t3.$t2.$t1;
  Echo "$livePattern : $rulePattern : ";
  if ($livePattern == $rulePattern){
    //Echo "<BR>This is True : $livePattern : $rulePattern <BR>";
    return true;
  }else{
    //Echo "<BR>This is Flase : $livePattern : $rulePattern <BR>";
    return false;
  }
}

function newReturnPattern($livePattern, $rulePattern){
  if ($livePattern == $rulePattern){
    //Echo "<BR>This is True newReturnPattern : $livePattern : $rulePattern <BR>";
    return true;
  }else{
    //Echo "<BR>This is Flase newReturnPattern : $livePattern : $rulePattern <BR>";
    return false;
  }
}

function buyRuleTest($livePattern, $rulePattern){
  if ($livePattern == $rulePattern){
    //Echo "<BR>This is True buyRuleTest : $livePattern : $rulePattern <BR>";
    return true;
  }else{
    //Echo "<BR>This is Flase buyRuleTest : $livePattern : $rulePattern <BR>";
    return false;
  }
}

function buywithPattern($p4,$p3,$p2,$p1,$t4,$t3,$t2,$t1,$tEnabled){
  $retPattern = returnPattern($p3,$p2,$p1,$t3,$t2,$t1);

  if ($tEnabled == 0){
      //print_r("<BR>True buywithPattern");
      return True;
      exit;
  }elseif ($retPattern){
      //print_r("<BR>True buywithPattern");
      $GLOBALS['allDisabled'] = true;
      return True;
      exit;
  }else {
    $GLOBALS['allDisabled'] = true;
    //print_r($buyTop >= $score);
    //print_r("False");
    return False;
  }
}

function newBuywithPattern($livePattern, $savedPattern, $pEnabled, $ruleID, $buySell){
  //$buySell == 0 for buy ; 1 for sell
  $pieces = removeWildcard($savedPattern);
  //echo "<BR> TempStr : ".$tmpStr;
  //$pieces = explode(",", $tmpStr);
  //var_dump($pieces[0]);
  $piecesSize = count($pieces);
  $testTrue = False;
  //echo var_dump($pieces);
  for ($x = 0; $x < $piecesSize; $x++) {
    //Echo "<br> ".$pieces[$x][0];
    if (($ruleID == $pieces[$x][0] && $pieces[$x][1] == 0 && $buySell == 0) OR ($ruleID == $pieces[$x][1] && $pieces[$x][0] == 0&& $buySell == 1)){
      //Echo "<br> ".$pieces[$x][0]." : ".$pieces[$x][1]." : ".$pieces[$x][2];
      if (newReturnPattern($livePattern,$pieces[$x][2])){ $testTrue = True; }//echo "<BR> LivePetern $livePattern TRUE";}
    }
  }
    if ($pEnabled == 0){
      //print_r("True");
      return True;
      exit;
    }
    elseif($testTrue){
      //print_r("True");
      $GLOBALS['allDisabled'] = true;
      return True;
      exit;
    }else{
      $GLOBALS['allDisabled'] = true;
      //print_r($buyTop >= $score);
      //print_r("False");
      return False;
    }
  //}
}

function limitToBuyRule($livePattern, $savedPattern, $pEnabled){
  $pieces = explode(",", $savedPattern);
  $piecesSize = count($pieces);
  $testTrue = False;
  for ($x = 0; $x < $piecesSize; $x++) {
    //Echo "<br> ".$pieces[$x];
    if (buyRuleTest($livePattern,$pieces[$x])){ $testTrue = True;}
  }
    if ($pEnabled == 0){
      //print_r("True");
      return True;
      exit;
    }
    elseif($testTrue){
      //print_r("True");
      $GLOBALS['allDisabled'] = true;
      return True;
      exit;
    }else{
      $GLOBALS['allDisabled'] = true;
      //print_r($buyTop >= $score);
      //print_r("False");
      return False;
    }
  //}
}

function sellWithScore($buyTop,$buyBtm,$score,$buyEnabled){
  if ($buyEnabled == 0){
      //print_r("True");
      return True;
      exit;
  }elseif ($buyTop >= $score && $buyBtm <= $score && $buyEnabled == 1){
      //print_r("True");
      $GLOBALS['allDisabled'] = true;
      return True;
      exit;
  }else {
    $GLOBALS['allDisabled'] = true;
    //print_r($buyTop >= $score);
    //print_r("False ".$score);
    return False;
  }
}

function autoBuyMain($LiveCoinPrice, $autoBuyPrice, $autoBuyCoinEnabled, $coinID){
  $returnBool = False;
  $coinPriceAryCount = count($autoBuyPrice);
  for ($i = 0; $i<$coinPriceAryCount; $i++){
    if ($coinID == $autoBuyPrice[$i][0]){
      //echo "<BR> autoBuy($LiveCoinPrice,".$autoBuyPrice[$i][1].",".$autoBuyPrice[$i][2].",$autoBuyCoinEnabled);";
      $returnBool = autoBuy($LiveCoinPrice,$autoBuyPrice[$i][1],$autoBuyPrice[$i][2],$autoBuyCoinEnabled);
    }
  }
  return $returnBool;
}

function autoSellMain($LiveCoinPrice, $autoBuyPrice, $autoBuyCoinEnabled, $coinID){
  $returnBool = False;
  $coinPriceAryCount = count($autoBuyPrice);
  for ($i = 0; $i<$coinPriceAryCount; $i++){
    if ($coinID == $autoBuyPrice[$i][0]){
      //echo "<BR> autoSell($LiveCoinPrice,".$autoBuyPrice[$i][1].",$autoBuyCoinEnabled); ";
      $returnBool = autoSell($LiveCoinPrice,$autoBuyPrice[$i][1],$autoBuyCoinEnabled);
      //echo $returnBool;
    }
  }
  return $returnBool;
}

function autoSell($LiveCoinPrice, $autoBuyPriceTop, $autoBuyCoinEnabled){
  //Echo "<BR> autoSell2($LiveCoinPrice, $autoBuyPriceTop, $autoBuyCoinEnabled)";
  if ($autoBuyCoinEnabled == 0){
      //print_r("True $autoBuyCoinEnabled");
      return True;
      exit;
  }elseif ($LiveCoinPrice >= $autoBuyPriceTop && $autoBuyCoinEnabled == 1){
      //print_r("True $LiveCoinPrice >= $autoBuyPriceTop && $autoBuyCoinEnabled ");
      $GLOBALS['allDisabled'] = true;
      return True;
      exit;
  }else {
    $GLOBALS['allDisabled'] = true;
    //print_r($buyTop >= $score);
    //print_r("False ");
    return False;
  }
}

function autoBuy($LiveCoinPrice, $autoBuyPriceTop, $autoBuyPriceBtm, $autoBuyCoinEnabled){
  if ($autoBuyCoinEnabled == 0){
      //print_r("True");
      return True;
      exit;
  }elseif ($LiveCoinPrice <= $autoBuyPriceTop && $LiveCoinPrice >= $autoBuyPriceBtm && $autoBuyCoinEnabled == 1){
      //print_r("True");
      $GLOBALS['allDisabled'] = true;
      return True;
      exit;
  }else {
    $GLOBALS['allDisabled'] = true;
    //print_r($buyTop >= $score);
    //print_r("False ");
    return False;
  }
}

function buyAmountOverride($buyAmountOverrideEnabled){
  if ($buyAmountOverrideEnabled == 0){
      //print_r("True");
      return True;
      exit;
  }elseif ($buyAmountOverrideEnabled == 1){
      //print_r("True");
      $GLOBALS['allDisabled'] = true;
      return True;
      exit;
  }else {
    $GLOBALS['allDisabled'] = true;
    //print_r($buyTop >= $score);
    //print_r("False ".$score);
    return False;
  }
}

function sellCoins($apikey, $apisecret, $coin, $email, $userID, $score, $date,$baseCurrency, $sendEmail, $sellCoin, $ruleID,$userName, $orderNo,$amount,$cost,$transactionID,$coinID,$CoinSellOffsetEnabled,$CoinSellOffsetPct,$LiveCoinPrice, $type){
  $apiVersion = 3;
  $retSell = False;
  echo "<BR>$apikey, $apisecret, $coin, $email, $userID, $score, $date,$baseCurrency, $sendEmail, $sellCoin, $ruleID,$userName, $orderNo,$amount,$cost";
  $subject = "Coin Alert: ".$coin."_".$ruleID;
  $from = 'Coin Alert <alerts@investment-tracker.net>';
    //sell Coin
  $bitPrice = $LiveCoinPrice;
  if ($CoinSellOffsetEnabled == 1){
    $bitPrice = number_format((float)$bitPrice + (($bitPrice/100)*$CoinSellOffsetPct), 8, '.', '');
    $bitPrice = round($bitPrice,8, PHP_ROUND_HALF_UP);
  }
  date_default_timezone_set('Asia/Dubai');
  $date = date("Y-m-d H:i:s", time());
  Echo "<br>Here1 $sellCoin";
  if ($sellCoin){
    $subject = "Coin Sale: ".$coin."_".$ruleID;
    $from = 'Coin Sale <sale@investment-tracker.net>';
    echo "<BR>bittrexsell($apikey, $apisecret, $coin ,$amount, $bitPrice, $baseCurrency);";
    $obj = bittrexsell($apikey, $apisecret, $coin ,round($amount,10), round($bitPrice,8), $baseCurrency, $apiVersion, FALSE);
    //Echo "<br>Here2";
    //$bittrexRef = $obj['result'][0]['uuid'];
    if ($apiVersion == 1){$bittrexRef = $obj["result"]["uuid"]; $status = $obj["success"]; }
    else{
      $bittrexRef = $obj["id"];
      Echo "<BR> API V3 Bittrex Ref: $bittrexRef | Direction : ".$obj["direction"];
      if ($bittrexRef <> ""){$status = 1;}else{$status = 0;}
    }

    //echo "<BR>BITTREXREF: $bittrexRef";

    //echo "<br> STATUS: $status";
    if ($status == 1 AND $bittrexRef <> ""){
      //$totalBTC = getTotalLimit($userID);
      $retSell = True;
      Echo "<br>Here3";
      echo "<br>updateSQL($baseCurrency,$transactionID,$bittrexRef)";
      //updateSQL($baseCurrency,$transactionID,$bittrexRef);
      echo "<BR>writeBittrexAction($coinID,$transactionID,$userID,$bittrexRef, $date, $status,$bitPrice,$type);";
      //writeBittrexAction($coinID,$transactionID,$userID,$type,$bittrexRef,$date,$status,$sellPrice){
      //writeBittrexAction($coinID,$transactionID,$userID,"Sell",$bittrexRef, $date, $status,$bitPrice);
      bittrexSellAdd($coinID, $transactionID, $userID, $type, $bittrexRef, $status, $bitPrice, $ruleID);
      logToSQL("Bittrex", "Sell Coin Add $bitPrice ", $userID,1);
    }
    logAction("SellCoins:  ".json_encode($obj), 'BuySell', 0);
    logToSQL("Bittrex", "Sell Coin Error: ".json_encode($obj)."|".$coin."|".$amount."|".$transactionID, $userID,1);
  }
  if ($sendEmail==1 &&  $sellCoin ==0){
  //if ($sendEmail){
    echo "$email, $coin, $amount, $bitPrice, $orderNo, $score,$profitPct,$bitPrice-$cost,$subject,$userName";
    $profitPct = ($bitPrice-$cost)/$cost*100;
    $buyPrice = ($cost*$amount);
    $bitPrice = ($bitPrice*$amount);
    $fee = (($bitPrice)/100)*0.25;
    $profit = $bitPrice - $buyPrice - $fee;
    sendSellEmail($email, $coin, $amount, $bitPrice, $orderNo.$ruleID, $score,$profitPct,$profit,$subject,$userName,$from);
  }
  return $retSell;
}

function bittrexsell($apikey, $apisecret, $symbol, $quant, $rate, $baseCurrency, $versionNum, $useAwards){
    $nonce=time();
    if ($versionNum == 1){
        $uri='https://bittrex.com/api/v1.1/market/selllimit?apikey='.$apikey.'&market='.$baseCurrency.'-'.$symbol.'&quantity='.$quant.'&rate='.$rate.'&nonce='.$nonce;
        echo "<BR>$uri<BR>";
        $sign=hash_hmac('sha512',$uri,$apisecret);
        $ch = curl_init($uri);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    }elseif ($versionNum == 3){
      $timestamp = time()*1000;
      $url = "https://api.bittrex.com/v3/orders";
      $method = "POST";

      $content = '{
        "marketSymbol": "'.$symbol.'-'.$baseCurrency.'",
        "direction": "SELL",
        "type": "LIMIT",
        "quantity": "'.$quant.'",
        "limit": "'.$rate.'",
        "timeInForce": "GOOD_TIL_CANCELLED",
        "useAwards": "'.$useAwards.'"
      }';
      echo "<BR>".$content;
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
      curl_setopt($ch, CURLOPT_POST, TRUE);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
      //$execResult = curl_exec($ch);

    }

    $execResult = curl_exec($ch);
    curl_close($ch);
    $obj = json_decode($execResult, true);
    return $obj;
}

function updateSQL($baseCurrency, $transactionID, $BittrexID){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    if ($baseCurrency == "BTC"){
        $sql = "UPDATE `Transaction` SET `Status` = 'Pending', `BittrexID` =  $BittrexID WHERE `ID` = $transactionID";
    }elseif ($baseCurrency == "ETH") {
        $sql = "UPDATE `Transaction` SET `Status` = 'Pending', `BittrexID` =  $BittrexID WHERE `ID` = $transactionID";
    }
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {echo "Error: " . $sql . "<br>" . $conn->error;}
    $conn->close();
    newLogToSQL("updateSQL",$sql,3,0,"SQL","TransactionID:$transactionID");
}

function clearBittrexRef($transactionID){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "UPDATE `Transaction` SET `BittrexRef` =  '' WHERE `ID` = $transactionID";

    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {echo "Error: " . $sql . "<br>" . $conn->error;}
    $conn->close();
    newLogToSQL("clearBittrexRef",$sql,3,0,"SQL","TransactionID:$transactionID");
}



function sendSellEmail($to, $symbol, $amount, $cost, $orderNo, $score, $profitPct, $profit, $subject, $user, $from){
    $body = "Dear ".$user.", <BR/>";
    $body .= "Congratulations you have sold the following Coin: "."<BR/>";
    $body .= "Coin: ".$symbol." Amount: ".$amount." Price: ".$cost."<BR/>";
    $body .= "Order Number: ".$orderNo."<BR/>";
    $body .= "Score: ".$score."<BR/>";
    $body .= "Profit %: ".$profitPct."<BR/>";
    $body .= "Profit BTC: ".$profit."<BR/>";
    $body .= "Kind Regards\nCryptoBot.";
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    //$headers .= 'From: Alerts <Alerts@Investment-Tracker.net>' . "\r\n";
    $headers .= "From:".$from."\r\n";
    $headers .= "To:".$to."\r\n";
    mail($to, $subject, wordwrap($body,70),$headers);
}

function sendSellEmailDebug($to, $symbol, $amount, $cost, $orderNo, $score, $profitPct, $profit, $subject, $user, $from, $debug){
    $body = "Dear ".$user.", <BR/>";
    $body .= "Congratulations you have sold the following Coin: "."<BR/>";
    $body .= "Coin: ".$symbol." Amount: ".$amount." Price: ".$cost."<BR/>";
    $body .= "Order Number: ".$orderNo."<BR/>";
    $body .= "Score: ".$score."<BR/>";
    $body .= "Profit %: ".$profitPct."<BR/>";
    $body .= "Profit BTC: ".$profit."<BR/>";
    $body .= "PDebug: ".$debug."<BR/>";
    $body .= "Kind Regards\nCryptoBot.";
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    //$headers .= 'From: Alerts <Alerts@Investment-Tracker.net>' . "\r\n";
    $headers .= "From:".$from."\r\n";
    $headers .= "To:".$to."\r\n";
    mail($to, $subject, wordwrap($body,70),$headers);
}

function bittrexCancel($apikey, $apisecret, $uuid, $versionNum){
    $nonce=time();
    if ($versionNum == 1){
        $uri='https://bittrex.com/api/v1.1/market/cancel?apikey='.$apikey.'&uuid='.$uuid.'&nonce='.$nonce;
        $sign=hash_hmac('sha512',$uri,$apisecret);
        $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $execResult = curl_exec($ch);
        $obj = json_decode($execResult, true);
        $balance = $obj["success"];
    }elseif ($versionNum == 3){
      $timestamp = time()*1000;
      $url = "https://api.bittrex.com/v3/orders/{".$uuid."}";
      echo "<BR>".$url;
      $method = "DELETE";
      echo "<BR>".$method;
      $content = '';
      $subaccountId = "";
      $contentHash = hash('sha512', $content);
      $preSign = $timestamp . $url . $method . $contentHash . $subaccountId;
      $signature = hash_hmac('sha512', $preSign, $apisecret);

      //$headers = array(
      //"Accept: application/json",
      //"Content-Type: application/json",
      //"Api-Key: ".$apikey."",
      //"Api-Signature: ".$signature."",
      //"Api-Timestamp: ".$timestamp."",
      //"Api-Content-Hash: ".$contentHash.""
      //);

      $curl = curl_init();

      curl_setopt_array($curl, array(
      CURLOPT_URL => "".$url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "DELETE",
      CURLOPT_HTTPHEADER => array(
      "Accept: application/json",
      "Api-Key: ".$apikey."",
      "Api-Signature: ".$signature."",
      "Api-Timestamp: ".$timestamp."",
      "Api-Content-Hash: ".$contentHash.""
      ),
      ));

      $execResult = curl_exec($curl);

      curl_close($curl);
      //echo $response;
      $balance = json_decode($execResult, true);
    }
    return $balance;
}

function cancelBittrexSQL($id){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "DELETE FROM `BittrexAction` WHERE `BittrexRef` = '$id'";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  logAction("cancelBittrexSQL: ".$sql, 'BuySell', 0);
}

function changeTransStatus($transactionID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `Transaction` SET `Status`= 'Open' WHERE `ID` = $transactionID";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("changeTransStatus",$sql,3,0,"SQL","TransactionID:$transactionID");
  logAction("changeTransStatus: ".$sql, 'BuySell', 0);
}

function bittrexBuyAdd($coinID, $userID, $type, $bittrexRef, $status, $ruleID, $cost, $amount, $orderNo,$timeToCancelBuyMins){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call AddBittrexBuy($coinID, $userID, '$type', '$bittrexRef', '$status', $ruleID, $cost, $amount, '$orderNo',$timeToCancelBuyMins);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("bittrexBuyAdd: ".$sql, 'BuySell', 0);
  newLogToSQL("bittrexBuyAdd","$sql",3,0,"SQL CALL","CoinID:$coinID");
}

function bittrexAddNoOfPurchases($bittrexRef, $noOfPurchases){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `Transaction` SET `NoOfPurchases` = $noOfPurchases WHERE `BittrexRef` = '$bittrexRef'";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("bittrexAddNoOfPurchases",$sql,3,0,"SQL","BittrexRef:$bittrexRef");
  logAction("bittrexAddNoOfPurchases: ".$sql, 'BuySell', 0);
}

function bittrexSellAdd($coinID, $transactionID, $userID, $type, $bittrexRef, $status, $bitPrice, $ruleID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call NewAddBittrexSell($coinID, $transactionID, $userID, '$type', '$bittrexRef', '$status', $bitPrice, $ruleID);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("bittrexSellAdd: ".$sql, 'BuySell', 0);
  newLogToSQL("bittrexSellAdd","$sql",3,0,"SQL CALL","CoinID:$coinID TransactionID:$transactionID");
}

function bittrexSellCancel($bittrexRef, $transactionID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call CancelBittrexSell('$bittrexRef', $transactionID);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("bittrexSellCancel: ".$sql, 'BuySell', 0);
  newLogToSQL("bittrexSellCancel","$sql",3,0,"SQL CALL","BittrexRef:$bittrexRef TransactionID:$transactionID");
}

function bittrexBuyCancel($bittrexRef, $transactionID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call CancelBittrexBuy('$bittrexRef',$transactionID);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("bittrexBuyCancel: ".$sql, 'BuySell', 0);
  newLogToSQL("bittrexBuyCancel","$sql",3,0,"SQL CALL","BittrexRef:$bittrexRef TransactionID:$transactionID");
}

function bittrexBuyComplete($bittrexRef,$transactionID, $finalPrice){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call CompleteBittrexBuy('$bittrexRef', $transactionID,$finalPrice);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("bittrexBuyComplete: ".$sql, 'BuySell', 0);
  newLogToSQL("bittrexBuyComplete","$sql",3,0,"SQL CALL","BittrexRef:$bittrexRef TransactionID:$transactionID");
}

function bittrexSellComplete($bittrexRef,$transactionID, $finalPrice){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call CompleteBittrexSell('$bittrexRef', $transactionID, $finalPrice);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("bittrexSellComplete: ".$sql, 'BuySell', 0);
  newLogToSQL("bittrexSellComplete","$sql",3,0,"SQL CALL","BittrexRef:$bittrexRef TransactionID:$transactionID");
}

function bittrexBuyCompleteUpdateAmount($transactionID, $amount){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call CompleteBittrexBuyUpdateAmount($transactionID, $amount);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("bittrexBuyCompleteUpdateAmount: ".$sql, 'BuySell', 0);
  newLogToSQL("bittrexBuyCompleteUpdateAmount","$sql",3,0,"SQL CALL","TransactionID:$transactionID");
}

function bittrexSellCompleteUpdateAmount($transactionID, $amount){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call CompleteBittrexSellUpdateAmount($transactionID, $amount);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("bittrexSellCompleteUpdateAmount: ".$sql, 'BuySell', 0);
  newLogToSQL("bittrexSellCompleteUpdateAmount","$sql",3,0,"SQL CALL","TransactionID:$transactionID");
}

function getTotalBTC(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `AmountOpen`,`UserID`,`BaseCurrency` FROM `AllTimeBTC`";
  //LogToSQL("SQLTest",$sql,3,1);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['AmountOpen'],$row['UserID'],$row['BaseCurrency']);}
  $conn->close();
  return $tempAry;
}

function getUserTotalBTC($totalBTCSpent,$userID,$baseCurrency){
  $totalBTCSpentSize = count($totalBTCSpent);
  for ($j=0; $j < $totalBTCSpentSize; $j++){
    if ($totalBTCSpent[$j][1] == $userID and $totalBTCSpent[$j][1] == $baseCurrency){
      return $totalBTCSpent[$j][0];
    }
  }
  return 0;
}

function getDailyBTC(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `AmountOpen`,`UserID`,`BaseCurrency` FROM `DailyBTC` where `AmountOpen` > 0";
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['AmountOpen'],$row['UserID'],$row['BaseCurrency']);}
  $conn->close();
  return $tempAry;
}

function copyCoinHistory($coin){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call copyCoinHistory($coin);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("copyCoinHistory","$sql",3,0,"SQL CALL","Coin:$coin");
}

function copyBuyHistory($coinID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call copyBuyHistory($coinID);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("copyBuyHistory","$sql",3,0,"SQL CALL","CoinID:$coinID");
}

function getAveragePrice($symbol){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `AvgCoinPrice` FROM `AvgCoinPriceView` WHERE `Symbol` = '$symbol'";
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['AvgCoinPrice']);
  }
  $conn->close();
  return $tempAry;
}

function coinPriceHistory($coinID,$price,$baseCurrency,$date,$hr1Pct,$hr24Pct,$d7Pct){
  $conn = getHistorySQL(rand(1,4));
  Echo "<BR> UpdateHistoryPrice : call UpdateHistoryPrice($coinID,$price,'$baseCurrency','$date');";
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call NewUpdatePriceHistory($coinID,$price,'$baseCurrency','$date',$hr1Pct,$hr24Pct,$d7Pct);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("coinPriceHistory","$sql",3,0,"SQL CALL","CoinID:$coinID");
}

function coinPriceHistorySpreadBet($coinID,$price,$baseCurrency,$date,$hr1Pct,$hr24Pct,$d7Pct){
  $conn = getHistorySQL(rand(1,4));
  Echo "<BR> UpdateHistoryPrice : call UpdateHistoryPrice($coinID,$price,'$baseCurrency','$date');";
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call UpdateSpreadBetPriceHistory($coinID,$price,'$baseCurrency','$date',$hr1Pct,$hr24Pct,$d7Pct);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("coinPriceHistorySpreadBet","$sql",3,0,"SQL CALL","CoinID:$coinID");
}

function get1HrChange($coinID){
  $tempAry = [];
  $conn = getHistorySQL(rand(1,4));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `Price` FROM `OneHourPrice` WHERE `CoinID` = $coinID";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['Price']);
  }
  $conn->close();
  return $tempAry;
}

function update1HrPriceChange($price,$coinID){
  $conn = getSQLConn(rand(1,3));
  Echo "<BR> Update1HrPriceChange : call Update1HrPriceChange($price,$coinID);";
  $newPrice = Round($price,8);
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "Update `CoinPctChange` SET `Live1HrChange` = $newPrice where `CoinID` = $coinID;";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("update1HrPriceChange",$sql,3,0,"SQL","CoinID:$coinID");
}

function get24HrChange($coinID){
  $tempAry = [];
  $conn = getHistorySQL(rand(1,4));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `Price` FROM `TwentyFourHourPrice` WHERE `CoinID` = $coinID";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['Price']);
  }
  $conn->close();
  return $tempAry;
}

function get7DayChange($coinID){
  $tempAry = [];
  $conn = getHistorySQL(rand(1,4));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `Price` FROM `SevenDayPrice` WHERE `CoinID` = $coinID";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['Price']);
  }
  $conn->close();
  return $tempAry;
}

function update7DPriceChange($sevenDayPrice,$coinID){
  $conn = getSQLConn(rand(1,3));
  echo "<BR> Update7DPriceChange : call Update7DPriceChange($sevenDayPrice,$coinID);";
  $newPrice = Round($sevenDayPrice,8);
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "Update `CoinPctChange` SET `Live7DChange` = $newPrice where `CoinID` = $coinID;";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("update7DPriceChange",$sql,3,0,"SQL","CoinID:$coinID");
}
function updatePctChange($coinID,$sevenDayPrice,$hr24Price,$hr1Price){
  $conn = getSQLConn(rand(1,3));
  echo "<BR> Update7DPriceChange : call Update7DPriceChange($sevenDayPrice,$coinID);";
  $newPrice = Round($sevenDayPrice,8);
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call UpdateCoinPctChange($coinID,$sevenDayPrice,$hr24Price,$hr1Price);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("updatePctChange","$sql",3,0,"SQL CALL","CoinID:$coinID");
}


function update24HrPriceChange($price,$coinID){
  $conn = getSQLConn(rand(1,3));
  echo "<BR> Update24HrPriceChange : call Update24HrPriceChange($price,$coinID);";
  $newPrice = Round($price,8);
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "Update `CoinPctChange` SET `Live24HrChange` = $newPrice where `CoinID` = $coinID;";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("update24HrPriceChange",$sql,3,0,"SQL","CoinID:$coinID");
}

function update8HrPriceChange($price,$coinID){
  $conn = getSQLConn(rand(1,3));
  Echo "<BR> Update8HrPriceChange : call Update8HrPriceChange($price,$coinID);";
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call Update8HrPriceChange($price,$coinID);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("update8HrPriceChange","$sql",3,0,"SQL CALL","CoinID:$coinID");
}

function bittrexUpdateBuyQty($transactionID, $quantity){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call CompleteBittrexBuyUpdateAmount($transactionID,$quantity);";
  print_r("<br>".$sql);
  if ($conn->query($sql) === TRUE) {echo "New record created successfully";
  } else {echo "Error: " . $sql . "<br>" . $conn->error;}
  $conn->close();
  logAction("bittrexUpdateBuyQty: ".$sql, 'BuySell', 0);
  newLogToSQL("bittrexUpdateBuyQty","$sql",3,0,"SQL CALL","TransactionID:$transactionID");
}

function bittrexUpdateSellQty($transactionID, $quantity){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call CompleteBittrexSellUpdateAmount($transactionID,$quantity);";
  print_r("<br>".$sql);
  if ($conn->query($sql) === TRUE) {echo "New record created successfully";
  } else {echo "Error: " . $sql . "<br>" . $conn->error;}
  $conn->close();
  logAction("bittrexUpdateSellQty: ".$sql, 'BuySell', 0);
  newLogToSQL("bittrexUpdateSellQty","$sql",3,0,"SQL CALL","TransactionID:$transactionID");
}

function bittrexCopyTransNewAmount($transactionID, $oQuantity, $nQuantity, $orderNo){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call newCopyTransNewAmount($nQuantity,$oQuantity,$transactionID,'$orderNo');";
  print_r("<br>".$sql);
  if ($conn->query($sql) === TRUE) {echo "New record created successfully";
  } else {
    echo "Error: " . $sql . "<br>" . $conn->error;
    sqltoSteven("Error: " . $sql . "<br>" . $conn->error);
  }
  $conn->close();
  logAction("bittrexCopyTransNewAmount: ".$sql, 'BuySell', 0);
  newLogToSQL("bittrexCopyTransNewAmount","$sql",3,0,"SQL CALL","TransactionID:$transactionID");
}

function sendtoSteven($transactionID,$newOrderQtyRemaining,$newOrderNo, $errorText){
  $body = "DEBUG FILE <BR/>";
  $body .= "Congratulations you have sold the following Coin: "."<BR/>";
  $body .= "Transaction: ".$transactionID."<BR/>";
  $body .= "Order Number: ".$newOrderNo."<BR/>";
  $body .= "Order Qty Remaining: ".$newOrderQtyRemaining."<BR/>";
  $body .= $errorText."<BR/>";
  $body .= "Kind Regards\nCryptoBot.";
  $headers = 'MIME-Version: 1.0' . "\r\n";
  $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
  $headers .= 'From: Coin Sale <sale@investment-tracker.net>' . "\r\n";
  //$headers .= "From:Coin Sale <sale@investment-tracker.net>\r\n";
  $headers .= 'To: stevenj1979@gmail.com'."\r\n";
  $subject = "COIN SALE DEBUG";
  mail($to, $subject, wordwrap($body,70),$headers);
}

function sqltoSteven($errorSQL){
  $body = "DEBUG FILE <BR/>";
  $body .= "Congratulations you have sold the following Coin: "."<BR/>";
  $body .= "SQL ERROR: ".$errorSQL."<BR/>";
  $body .= "Kind Regards\nCryptoBot.";
  $headers = 'MIME-Version: 1.0' . "\r\n";
  $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
  $headers .= 'From: Coin Sale <sale@investment-tracker.net>' . "\r\n";
  //$headers .= "From:Coin Sale <sale@investment-tracker.net>\r\n";
  $headers .= 'To: stevenj1979@gmail.com'."\r\n";
  $subject = "COIN SALE SQL DEBUG";
  mail($to, $subject, wordwrap($body,70),$headers);
}

function logAction($log, $logFile, $enabled){
  if ($enabled == 1){
    file_put_contents('./log/log_'.$logFile.'_'.date("j.n.Y").'.log', date("F j, Y, g:i a").':'.$log.PHP_EOL, FILE_APPEND);
  }
}

function logToSQL($subject, $comments, $UserID, $enabled){
  if ($enabled == 1){
    $comments = str_replace("'","/",$comments);
    $conn = getSQLConn(rand(1,3));
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $sql = "call LogToSQL($UserID,'$subject','$comments',300)";
    print_r("<br>".$sql);
    if ($conn->query($sql) === TRUE) {echo "New record created successfully";
    } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
      sqltoSteven("Error: " . $sql . "<br>" . $conn->error);
    }
    $conn->close();
  }
}

function newLogToSQL($subject, $comments, $UserID, $enabled, $subTitle, $ref){
  if ($enabled == 1){
    $comments = str_replace("'","/",$comments);
    $conn = getSQLConn(rand(1,3));
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $sql = "call newLogToSQL($UserID,'$subject','$comments',300,'$subTitle','$ref')";
    print_r("<br>".$sql);
    if ($conn->query($sql) === TRUE) {echo "New record created successfully";
    } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
      sqltoSteven("Error: " . $sql . "<br>" . $conn->error);
    }
    $conn->close();
  }
}

function displayHeader($n){
  $_SESSION['sellCoinsQueue'] = count(getTrackingSellCoins($_SESSION['ID']));
  $_SESSION['bittrexQueue'] = count(getBittrexRequests($_SESSION['ID']));
  $userDisabledUntil = getUserDisabled($_SESSION['ID']);
  $_SESSION['DisableUntil'] = $userDisabledUntil[0][0];
  if ($_SESSION['DisableUntil'] <= date("Y-m-d H:i:s", time())){$_SESSION['isDisabled'] = False;} else{$_SESSION['isDisabled'] = True;}

  $headers = array("Dashboard.php", "Transactions.php", "Stats.php","BuyCoins.php","SellCoins.php","Profit.php","bittrexOrders.php","Settings.php", "CoinAlerts.php","console.php","CoinMode.php","AdminSettings.php");
  $ref = array("Dashboard", "Transactions", "Stats","Buy Coins","Sell Coins","Profit","Bittrex Orders","Settings","Coin Alerts","Console","CoinMode","Admin Settings");
  $headerLen = count($headers);
  $imgpath = '/Investment-Tracker/Cryptobot/1/Images/CBLogoSmall.png';
  ?><div class="header">
    <table>
      <tr><TH><img src='<?php echo $imgpath; ?>' width="40"> </TH>
      <TH>Logged in as: <i class="glyphicon glyphicon-user"></i>  <?php echo $_SESSION['username']; ?><?php if ($_SESSION['isDisabled']){echo " Disabled Until : ".$_SESSION['DisableUntil'];} ?></th></tr></Table><br>
     </div>
     <div class="topnav"> <?php

     echo "<ul>";
      for($x = 0; $x < $headerLen; $x++) {
        $sellQueue = "";$active = "";
        $h1 = $headers[$x];
        $r1 = $ref[$x];
        if ($ref[$x] == "Bittrex Orders" and $_SESSION['bittrexQueue'] > 0) {$sellQueue = "(".$_SESSION['bittrexQueue'].")";}
        if ($ref[$x] == "Sell Coins" and $_SESSION['sellCoinsQueue'] > 0){$sellQueue = "(".$_SESSION['sellCoinsQueue'].")"; }
        if ($n == $x) { $active = " class='active'";}
        if ($_SESSION['AccountType']==1 && $x == $headerLen){Echo "<li><a href='$h1'$active>$r1 $sellQueue</a></li>";}
        else{Echo "<li><a href='$h1'$active>$r1 $sellQueue</a></li>";}
        //$active = '';
      }
      echo "<ul>";
      //if ($n > $headerLen ){ $active = " class='active'"; }
      //if ($_SESSION['AccountType']==1){echo "<a href='AdminSettings.php'$active>Admin Settings</a>";}?>
    </div>
    <div class="row">
      <div class="settingCol1"><?php
}

function displaySideColumn(){
  //Echo "";?>
      </div>
      <div class="column side">

      </div>
    </div>

    <div class="footer">
      <hr>
      <!-- <input type="button" value="Logout">
      <a href='logout.php'>Logout</a>-->

      <input type="button" onclick="location='logout.php'" value="Logout"/>

    </div><?php
}

function displayMiddleColumn(){
  //Echo "<div class='column middle'>";
}

function displayFarSideColumn(){
  //Echo "</div>";
  //Echo "<div id='visualization' style='width: 600px; height: 400px;'></div>";
  //Echo "</div>";
  //Echo "<div class='column side'>";
}

function displayFooter(){
  //Echo "</div>";
  //Echo "</div>";
  //Echo "<div class='footer'>";
  //Echo "<hr>";
  //Echo "<input type='button' onClick='location.href=\"logout.php\"' value='Logout'/>";
  //Echo "</div>";
}

function getCoinAlerts(){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `ID`,`CoinID`, `Action`, `Price`, `Symbol`, `UserName`,`Email` ,`LiveCoinPrice`,`Category`,`Live1HrChange` ,`Live24HrChange` ,`Live7DChange`,`ReocurringAlert`,`DateTimeSent`
  ,`LiveSellOrders`,`LiveBuyOrders`,`LiveMarketCap`,`UserID`,TIMESTAMPDIFF(MINUTE,`DateTimeSent`, now()) as MinsSinceSent, `LivePricePct` FROM `CoinAlertsView`";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['ID'],$row['CoinID'],$row['Action'],$row['Price'],$row['Symbol'],$row['UserName'],$row['Email'],$row['LiveCoinPrice'],$row['Category'],$row['Live1HrChange'] //9
    ,$row['Live24HrChange'],$row['Live7DChange'],$row['ReocurringAlert'],$row['DateTimeSent'],$row['LiveSellOrders'],$row['LiveBuyOrders'],$row['LiveMarketCap'],$row['UserID'],$row['MinsSinceSent'] //18
    ,$row['LivePricePct']);
  }
  $conn->close();
  return $tempAry;
}

function getMarketstats(){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `LiveCoinPrice`,((`LiveCoinPrice`-`Live1HrChange`)/`Live1HrChange`)*100 as `Hr1ChangePctChange`
,((`LiveCoinPrice`-`Live24HrChange`)/`Live24HrChange`)*100 as `Hr24ChangePctChange`
,((`LiveCoinPrice`-`Live7DChange`)/`Live7DChange`)*100 as `D7ChangePctChange`
  ,((`LiveCoinPrice`-`LastCoinPrice`)/`LastCoinPrice`)*100 as`LiveMarketPctChange`
  ,((`LiveMarketCap`-`LastMarketCap`)/`LastMarketCap`)*100 as `MarketCapPctChange`
  , `Live1HrChange`, `Live24HrChange`, `Live7DChange` FROM `MarketCoinStats` ";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['LiveCoinPrice'],$row['Hr1ChangePctChange'],$row['Hr24ChangePctChange'],$row['D7ChangePctChange'],$row['LiveMarketPctChange'],$row['MarketCapPctChange']
  ,$row['Live1HrChange'],$row['Live24HrChange'],$row['Live7DChange']);
  }
  $conn->close();
  return $tempAry;
}

function getMarketAlerts($userID = 0){
  $whereClause = " where `UserID` = $userID";
  if ($userID = 0){ $whereClause = "";}
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $sql = "SELECT `UserID`, `UserName`, `Email`, `DateTimeSent`, `ReocurringAlert`, `Category`, `Action`, `Minutes`, `MarketAlertRuleID` as `MarketAlertRuleID`, `Price`
     FROM `MarketAlertsView`$whereClause";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['UserID'],$row['UserName'],$row['Email'],$row['DateTimeSent'],$row['ReocurringAlert'] //4
    ,$row['Category'],$row['Action'],$row['Minutes'],$row['MarketAlertRuleID'],$row['Price']);
  }
  $conn->close();
  return $tempAry;
}

function getMarketAlertsTotal(){
  //$whereClause = " where `UserID` = $userID";
  //if ($userID = 0){ $whereClause = "";}
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $sql = "SELECT `UserID`, `UserName`, `Email`, `DateTimeSent`, `ReocurringAlert`, `Category`, `Action`, `Minutes`, `MarketAlertRuleID` as `MarketAlertRuleID`, `Price`
     FROM `MarketAlertsView`";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['UserID'],$row['UserName'],$row['Email'],$row['DateTimeSent'],$row['ReocurringAlert'] //4
    ,$row['Category'],$row['Action'],$row['Minutes'],$row['MarketAlertRuleID'],$row['Price']);
  }
  $conn->close();
  return $tempAry;
}

function getSpreadBetAlerts($userID = 0){
  $whereClause = " where `UserID` = $userID";
  if ($userID = 0){ $whereClause = "";}
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `LiveCoinPrice`, `Live1HrChange`, `Live24HrChange`, `Live7DChange`, `LiveMarketCap`, `UserID`, `UserName`, `Email`, `DateTimeSent`, `ReocurringAlert`, `Category`, `Action`, `Minutes`, `SpreadBetAlertRuleID`, `Price`
  , `LivePricePct`,`SpreadBetRuleID`
  FROM `SpreadBetAlertsView`$whereClause group by `SpreadBetAlertRuleID`";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['LiveCoinPrice'],$row['Live1HrChange'],$row['Live24HrChange'],$row['Live7DChange'],$row['LiveMarketCap'],$row['UserID'],$row['UserName'],$row['Email'],$row['DateTimeSent'],$row['ReocurringAlert'] //9
    ,$row['Category'],$row['Action'],$row['Minutes'],$row['SpreadBetAlertRuleID'],$row['Price'],$row['LivePricePct'],$row['SpreadBetRuleID']);
  }
  $conn->close();
  return $tempAry;
}

function getSpreadBetAlertsTotal(){
  //$whereClause = " where `UserID` = $userID";
  //if ($userID = 0){ $whereClause = "";}
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `LiveCoinPrice`, `Live1HrChange`, `Live24HrChange`, `Live7DChange`, `LiveMarketCap`, `UserID`, `UserName`, `Email`, `DateTimeSent`, `ReocurringAlert`, `Category`, `Action`, `Minutes`, `SpreadBetAlertRuleID`, `Price`
  , `LivePricePct`
  FROM `SpreadBetAlertsView`";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['LiveCoinPrice'],$row['Live1HrChange'],$row['Live24HrChange'],$row['Live7DChange'],$row['LiveMarketCap'],$row['UserID'],$row['UserName'],$row['Email'],$row['DateTimeSent'],$row['ReocurringAlert'] //9
    ,$row['Category'],$row['Action'],$row['Minutes'],$row['SpreadBetAlertRuleID'],$row['Price'],$row['LivePricePct']);
  }
  $conn->close();
  return $tempAry;
}

function getCoinAlertsUser($userId){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `ID`,`CoinID`, `Action`, `Price`, `Symbol`, `UserName`,`Email` ,`LiveCoinPrice`,`Category`,`Live1HrChange` ,`Live24HrChange` ,`Live7DChange`,`ReocurringAlert`,`DateTimeSent`,`CoinAlertRuleID`
  FROM `CoinAlertsView` WHERE `UserID` = $userId group by `CoinAlertRuleID`";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['ID'],$row['CoinID'],$row['Action'],$row['Price'],$row['Symbol'],$row['UserName'],$row['Email'],$row['LiveCoinPrice'],$row['Category'],$row['Live1HrChange']
    ,$row['Live24HrChange'],$row['Live7DChange'],$row['ReocurringAlert'],$row['DateTimeSent'],$row['CoinAlertRuleID']);
  }
  $conn->close();
  return $tempAry;
}

function getCoinAlertsbyID($id){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `ID`,`CoinID`, `Action`, `Price`, `Symbol`, `UserName`,`Email` ,`LiveCoinPrice`,`Category`,`Live1HrChange` ,`Live24HrChange` ,`Live7DChange`,`ReocurringAlert`,`DateTimeSent`
  FROM `CoinAlertsView` WHERE `CoinAlertRuleID` = $id";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['ID'],$row['CoinID'],$row['Action'],$row['Price'],$row['Symbol'],$row['UserName'],$row['Email'],$row['LiveCoinPrice'],$row['Category'],$row['Live1HrChange']
    ,$row['Live24HrChange'],$row['Live7DChange'],$row['ReocurringAlert'],$row['DateTimeSent']);
  }
  $conn->close();
  return $tempAry;
}

function updateCoinAlertsbyID($id, $coinID, $action, $userID, $category, $reocurring, $price){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
  $sql = "UPDATE `CoinAlerts` SET `Action`= '$action', `UserID`= $userID, `Category` = '$category', `ReocurringAlert`= $reocurring, `Price` = $price WHERE `CoinAlertRuleID` = $id";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("updateCoinAlertsbyID",$sql,3,0,"SQL","CoinAlertRuleID:$id");
}

function closeCoinAlerts($id, $table){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    if ($table == 'CoinAlerts'){$sql = "UPDATE `CoinAlerts` SET `Status`= 'Closed' WHERE `ID` = $id";}
    elseif ($table == 'MarketAlerts'){$sql = "UPDATE `MarketAlerts` SET `Status`= 'Closed' WHERE `MarketAlertRuleID` = $id";}
    elseif ($table == 'SpreadBetAlerts'){$sql = "UPDATE `SpreadBetAlerts` SET `Status`= 'Closed' WHERE `SpreadBetAlertRuleID` = $id";}
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("updateCoinAlertsbyID",$sql,3,0,"SQL","CoinAlertRuleID:$id");
}

function updateAlertTime($id, $table){
  $conn = getSQLConn(rand(1,3));
  $current_date = date('Y-m-d H:i');
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    if ($table == 'CoinAlerts'){$sql = "UPDATE `CoinAlerts` SET `DateTimeSent`= now() WHERE `ID` = $id";}
    elseif ($table == 'MarketAlerts'){$sql = "UPDATE `MarketAlerts` SET `DateTimeSent`= now() WHERE `MarketAlertRuleID` = $id";}
    elseif ($table == 'SpreadBetAlerts'){$sql = "UPDATE `MarketAlerts` SET `DateTimeSent`= now() WHERE `SpreadBetAlertRuleID` = $id";}

    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("updateAlertTime",$sql,3,0,"SQL","CoinAlertRuleID:$id");
}

function updateBittrexQuantityFilled($quantFilled, $bittrexRef){
  $conn = getSQLConn(rand(1,3));
  $current_date = date('Y-m-d H:i');
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `BittrexAction` SET `QuantityFilled` = $quantFilled WHERE `BittrexRef` = '$bittrexRef'";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("updateBittrexQuantityFilled",$sql,3,0,"SQL","BittrexRef:$bittrexRef");
}

function sendAlertEmail($to, $symbol , $price, $action, $user){
    $subject = "Coin Alert: ".$coin;
    $from = 'Coin Alert <alert@investment-tracker.net>';
    $body = "Dear ".$user.", <BR/>";
    $body .= "Your coin Alert for $symbol has been triggered : "."<BR/>";
    $body .= "Coin: $symbol Action: $action Price: $price<BR/>";
    $body .= "Kind Regards\nCryptoBot.";
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    //$headers .= 'From: Alerts <Alerts@Investment-Tracker.net>' . "\r\n";
    $headers .= "From:".$from."\r\n";
    $headers .= "To:".$to."\r\n";
    mail($to, $subject, wordwrap($body,70),$headers);
}

function setStyle($isMobile){
  if ($isMobile){
      echo "<style>";
      include 'style/mStyle.css';
      echo "</style>";
  }else{
    echo "<style>";
    include 'style/style.css';
    echo "</style>";
  }
}

function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}


function isCoinMatch($bitPrice, $symbol, $livePrice, $liveSymbol, $isGreater,$lowPrice){
  //echo "<BR> isCoinMatch($bitPrice, $symbol, $livePrice, $liveSymbol, $isGreater)";
  $symbolBool = False; $priceBool = False;
  //Echo "<BR> $symbol : $liveSymbol";
  if ($symbol == $liveSymbol){
    $symbolBool = True;
    //if ($isGreater == 1){
      //Echo "<BR> LIVE Price: $livePrice High Price: $bitPrice LowPrice: $lowPrice";
      if ( $livePrice <= $bitPrice  && $livePrice >= $lowPrice){$priceBool = True;}

      //echo "<BR> if ($livePrice > $bitPrice){";
    //}else{
      //Echo "<BR> Is Less Than: $isGreater";
      //if ($bitPrice >= $livePrice && $lowPrice <= $livePrice){$priceBool = True;}
      //echo "<BR> if ($livePrice < $bitPrice){";
    //}
  }
  if ($symbolBool == True && $priceBool == True) { return True;}
  else{ return False; }

}

function coinMatchPattern($coinPattern, $livePrice, $liveSymbol, $isGreater, $pEnabled, $ruleID, $buySell){
  //$pieces = explode(",", $coinPattern);
  $piecesSize = count($coinPattern);
  $testTrue = False;
  //echo "<BR> Count : ".$piecesSize;
  for ($x = 0; $x < $piecesSize; $x++) {
    $buyRuleID = $coinPattern[$x][0]; $sellRuleID = $coinPattern[$x][1];
    //echo "<BR> pattern : ".$coinPattern[$x][2];
    $coinPriceMatchPrice = $coinPattern[$x][3]; $coinPriceMatchSymbol = $coinPattern[$x][4];$coinPriceMatchLowPrice = $coinPattern[$x][5];
    //Echo "<br> ".$pieces[$x];
    //$row = explode(":", $pieces[$x]);
    //echo "<BR> coinMatchPattern : $buyRuleID $sellRuleID $coinPriceMatchPrice $coinPriceMatchSymbol";
    if (($buyRuleID == $ruleID && $sellRuleID == 0 && $buySell == 0) OR ($sellRuleID == $ruleID && $buyRuleID == 0 && $buySell == 1)){
      //echo "<BR> coinMatchPattern : $buyRuleID $sellRuleID $coinPriceMatchPrice $coinPriceMatchSymbol";
      if (isCoinMatch((float)$coinPriceMatchPrice,$coinPriceMatchSymbol,$livePrice, $liveSymbol, $isGreater,$coinPriceMatchLowPrice)){ $testTrue = True;}
    //echo "<BR>isCoinMatch((float)$row[1],$row[0],$livePrice, $liveSymbol, $isGreater)";
    }
  }
    if ($pEnabled == 0){
      //print_r("True");
      return True;
      exit;
    }
    elseif($testTrue){
      //print_r("True");
      $GLOBALS['allDisabled'] = true;
      return True;
      exit;
    }else{
      $GLOBALS['allDisabled'] = true;
      //print_r($buyTop >= $score);
      //print_r("False");
      return False;
    }
}

function getStats(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Symbol`,`ID`,`BaseCurrency`,`CMCID` FROM `CoinStatsView` order by `Symbol` asc";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Symbol'],$row['ID'],$row['BaseCurrency'],$row['CMCID']);
  }
  $conn->close();
  return $tempAry;
}

function setStats(){
  $statsAry = getStats();
  $_SESSION['StatsList'] = $statsAry;
  $_SESSION['StatsListSelected'] =  $statsAry[0][0];
  $_SESSION['StatsListTime'] = "15 Minute";
}

function getBase($selected, $statsAry){
    $statsOptionCount = Count($statsAry);
    for($x = 0; $x < $statsOptionCount; $x++) {
      //echo "<BR> If check : 1; ".$statsAry[$x][2]." : 2; ".$statsAry[$x][1]." : SELECTED; ".$selected;
      if ($statsAry[$x][0] == $selected){
        //echo "<BR> Base Currency = ".$statsAry[$x][2];
        return $statsAry[$x][2];
        exit;
      }
    }
}

function NewEcho($textStr, $isMobile, $display, $round = 0){
  // display: 0 = desktop only; 1 = mobile only; 2 = mobile and desktop
  if ($display == 0 && $isMobile == 0){
      Echo $textStr;
  }elseif ($display == 1 && $isMobile == 1){
      Echo $textStr;
  }elseif ($display == 2){
      Echo $textStr;
  }
}

function replaceStars($tempStr,$starCount){
  $returnStr = "";
  if ($starCount == 1){
    for ($k = -1; $k<3-1; $k++){
      //echo "<BR> $k ".str_replace_first("*",$k,$tempStr);
      $tempStr = str_replace_first("*",$k,$tempStr);
      //Echo "<BR> $tempStr";
    }
    $returnStr .= $tempStr;
  }elseif ($starCount == 2){
    for ($j = -1; $j<$starCount; $j++){
      for ($k = -1; $k<3-1; $k++){
        //echo "<BR> $j $k";
        //echo "<BR> $j ".str_replace_first("*",$j,$tempStr);
        $tempStr = str_replace_first("*",$j,$tempStr);
        //echo "<BR> $k ".str_replace_first("*",$k,$tempStr1);
        $tempStr = str_replace_first("*",$k,$tempStr);
        //Echo "<BR> $tempStr2";
      }
    }
    $returnStr .= $tempStr;
  }elseif ($starCount == 3){
    for ($j = -1; $j<$starCount-1; $j++){
      for ($k = -1; $k<$starCount-1; $k++){
        for ($l = -1; $l<3-1; $l++){
          //echo "<BR> $j $k $l";
          //echo "<BR> $j ".str_replace_first("*",$j,$tempStr);
          $tempStr = str_replace_first("*",$j,$tempStr);
          //echo "<BR> $k ".str_replace_first("*",$k,$tempStr1);
          $tempStr = str_replace_first("*",$k,$tempStr);
          //echo "<BR> $l ".str_replace_first("*",$l,$tempStr2);
          $tempStr = str_replace_first("*",$l,$tempStr);
          //Echo "<BR> $tempStr3";
        }
      }
    }
    $returnStr .= $tempStr;
  }
  //echo "<BR> Return String: $returnStr";
return $returnStr;
}

function str_replace_first($search, $replace, $subject) {
    $pos = strpos($subject, $search);
    if ($pos !== false) {
        return substr_replace($subject, $replace, $pos, strlen($search));
    }
    return $subject;
}

function returnWildcardStr($tempStr, $starCount){
  $returnStr = "";
  if ($starCount == 1){
    for ($n=0; $n<1; $n++){
        $returnStr .= $tempStr.",".$tempStr.",".$tempStr.",";
    }
  }elseif ($starCount == 2){
    for ($n=0; $n<3; $n++){
        $returnStr .= $tempStr.",".$tempStr.",".$tempStr.",";
    }
  }elseif ($starCount == 3){
    for ($n=0; $n<9; $n++){
        $returnStr .= $tempStr.",".$tempStr.",".$tempStr.",";
    }
  }
  //echo "<BR> Test Return Str: $returnStr";
  return $returnStr;
}

function stringsToArray($str1, $str2, $str3, $str4){
  //echo "<BR> STR1 ".$str1;
  $tmpAry1 = explode(',',$str1); $tmpAry2 = explode(',',$str2);
  $tmpAry3 = explode(',',$str3); $tmpAry4 = explode(',',$str4);

  $aryCount = count($tmpAry1); $returnAry = [];
  //echo "<BR> Count ".$aryCount." number 1 ".$tmpAry1[0];
  //$aryCount2 = count($tmpAry2);

  for ($i=0; $i<$aryCount; $i++){
    //echo "<BR> B :".$tmpAry1[$i];
    $returnAry[$i][0] = $tmpAry1[$i];
    $returnAry[$i][1] = $tmpAry2[$i];
    $returnAry[$i][2] = $tmpAry3[$i];
    $returnAry[$i][3] = $tmpAry4[$i];
  }
  return $returnAry;
}

Function removeWildcard($tempStr){
	//$tempStr = explode(',',$wildcardStr);
  $returnUserIDStr ="";$returnSellRuleIDStr = ""; $returnBuyRuleIDStr ="";
	$tempStrCount = count($tempStr);
  $returnStr = "";
	for($i=0; $i < $tempStrCount; $i++){
    //echo "<BR> Test: ".$tempStr[$i][2]." | ".strpos($tempStr[$i][2], '*');
    if (strpos($tempStr[$i][2], '*') !== false) {
        $starCount =substr_count($tempStr[$i][2],"*");
        //$returntempStr = $tempStr[$i].",";
        for ($x=0; $x<$starCount; $x++){
          //echo "<BR> returnWildcardStr(".$tempStr[$i][2].",$starCount);";
          $newStr = returnWildcardStr($tempStr[$i][2],$starCount);
          $returntempStr = replaceStars($newStr,$starCount);
          $buyRuleIDStr = returnWildcardStr($tempStr[$i][0],$starCount);
          $sellRuleIDStr = returnWildcardStr($tempStr[$i][1],$starCount);
          $userIDStr = returnWildcardStr($tempStr[$i][3],$starCount);
        }
        $returnStr .= $returntempStr;
        $returnBuyRuleIDStr .= $buyRuleIDStr;
        $returnSellRuleIDStr .= $sellRuleIDStr;
        $returnUserIDStr .= $userIDStr;
    }else{
        //no instances of * - add the string to the return string
        $returnStr .=$tempStr[$i][2].",";
        $returnBuyRuleIDStr .= $tempStr[$i][0].",";
        $returnSellRuleIDStr .= $tempStr[$i][1].",";
        $returnUserIDStr .= $tempStr[$i][3].",";
    }
	}
  //echo "<BR> returnBuyRuleIDStr $returnBuyRuleIDStr";
  //echo "<BR> returnSellRuleIDStr $returnSellRuleIDStr";
  //echo "<BR> returnStr $returnStr";
  //echo "<BR> returnUserIDStr $returnUserIDStr";
  $finalReturnStr = stringsToArray(rtrim($returnBuyRuleIDStr,','),rtrim($returnSellRuleIDStr,','),rtrim($returnStr,','),rtrim($returnUserIDStr,','));
 return $finalReturnStr;
}

function setTimeZone(){
  date_default_timezone_set('Asia/Dubai');
}

function getCoinList($coinStats, $num){
  $returnStr = "";
  $coinStatsCount = count($coinStats);
  for ($i=0; $i<$coinStatsCount; $i++){
    $returnStr .= $coinStats[$i][$num].",";
  }
  //LogToSQL("CMCStats","".rtrim($returnStr,','),3,1);
  return rtrim($returnStr,',');
}

function getCoinPriceMatchList($userID = 0){
  $conn = getSQLConn(rand(1,3));
  $whereClause = "";
  if ($userID <> 0){ $whereClause = " where `UserID` = $userID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `BuyRuleID`,`SellRuleID`,`CoinID`,`Price`,`Symbol`,`LowPrice` FROM `NewCoinPriceMatchView`$whereClause";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['BuyRuleID'],$row['SellRuleID'],$row['CoinID'],$row['Price'],$row['Symbol'],$row['LowPrice']);
  }
  $conn->close();
  return $tempAry;
}

function getCoinPriceMatchSettings($whereClause = ""){
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `CoinID`,`Price`,`Symbol`,`LowPrice`,`Name`FROM `NewCoinPriceMatchSettingsView` $whereClause";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['CoinID'],$row['Price'],$row['Symbol'],$row['LowPrice'],$row['Name'],$row['UserID']);
  }
  $conn->close();
  return $tempAry;
}

function getCoinPricePattenList($userID = 0){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  $whereClause = "";
  if ($userID <> 0){ $whereClause = " where `UserID` = $userID";}
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `BuyRuleID`,`SellRuleID`,`CoinPattern`,`UserID` FROM `NewCoinPricePatternView` $whereClause";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['BuyRuleID'],$row['SellRuleID'],$row['CoinPattern'],$row['UserID']);
  }
  $conn->close();
  return $tempAry;
}

function getCoinPricePattenSettings(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Name`,`CoinPattern` FROM `NewCoinPricePatternSettingsView`";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Name'],$row['CoinPattern']);
  }
  $conn->close();
  return $tempAry;
}

function getCoin1HrPattenList($userID = 0){
  $conn = getSQLConn(rand(1,3));
  $whereClause = "";
  if ($userID <> 0){ $whereClause = " where `UserID` = $userID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `BuyRuleID`,`SellRuleID`,`Pattern`,`UserID` FROM `NewCoin1HrPatternView` $whereClause order by `BuyRuleID`,`SellRuleID`";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['BuyRuleID'],$row['SellRuleID'],$row['Pattern'],$row['UserID']);
  }
  $conn->close();
  return $tempAry;
}

function getCoin1HrPattenSettings(){
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Name`,`Pattern` FROM `NewCoin1HrPatternSettingsView`";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Name'],$row['Pattern']);
  }
  $conn->close();
  return $tempAry;
}

function getAutoBuyPrices(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `CoinID`,`AutoBuyPrice`,`AutoSellPrice` FROM `CryptoAuto`";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['CoinID'],$row['AutoBuyPrice'],$row['AutoSellPrice']);
  }
  $conn->close();
  return $tempAry;
}

function setTextColour($num, $onOffFlag){
  $colour = "";
  if ($onOffFlag == False){
    if ($num < -0.5 and $num > -0.75){ $colour = "background-color:Orange;";}
    elseif ($num < -0.76){$colour = "background-color:MediumSeaGreen;";}

  }else{
    //Echo "<BR> Test2: $num";
    if ($num == ""){ $colour = "background-color:Orange;";}
    elseif ($num == "1"){$colour = "background-color:MediumSeaGreen;";}
    //return $colour;
  }
return $colour;
}

function setTextColourTarget($num, $onOffFlag, $targetTop, $targetBtm){

  $flagNum = 0;
  $colour = "";
  if ($onOffFlag == False){
    if ($num <= $targetTop){
      $flagNum = 1;
      $colour = "background-color:LightSkyBlue;";
    }elseif ($num > $targetTop and $num <= 0){
      $flagNum = 2;
      $colour = "background-color:MediumSeaGreen;";
    }elseif ($num > abs($targetTop) and $num < (abs($targetTop) * 2)){
      $flagNum = 3;
      $colour = "background-color:Orange;";
    }else{
      $flagNum = 4;
      $colour = "background-color:Crimson;";
    }
  }else{
    //Echo "<BR> Test2: $num";
    if ($num == ""){ $colour = "background-color:Orange;";}
    elseif ($num == "1"){$colour = "background-color:MediumSeaGreen;";}
    //return $colour;
  }
  //echo "<BR> COLOUR TEST: $num, $targetTop, $targetBtm, $flagNum, $colour";
return $colour;
}

function getBuyRulesIDs($userID){
$conn = getSQLConn(rand(1,3));
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT `RuleID` FROM `UserBuyRules` WHERE `UserID` = $userID and `BuyCoin` = 1";
$result = $conn->query($sql);
//$result = mysqli_query($link4, $query);
//mysqli_fetch_assoc($result);
while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['RuleID'],);
}
$conn->close();
return $tempAry;
}

function addTrackingCoin($coinID, $coinPrice, $userID, $baseCurrency, $sendEmail, $buyCoin, $quantity, $ruleIDBuy, $coinSellOffsetPct, $coinSellOffsetEnabled, $buyType, $minsToCancelBuy, $sellRuleFixed, $toMerge, $noOfPurchases, $risesInPrice, $type, $originalPrice,$spreadBetTransID,$spreadBetRuleID,$overrideCoinAlloc){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "INSERT INTO `TrackingCoins`(`CoinID`, `CoinPrice`, `UserID`, `BaseCurrency`, `SendEmail`, `BuyCoin`, `Quantity`, `RuleIDBuy`, `CoinSellOffsetPct`, `CoinSellOffsetEnabled`, `BuyType`, `MinsToCancelBuy`, `SellRuleFixed`, `Status`, `ToMerge`
    ,`NoOfPurchases`,`OriginalPrice`,`BuyRisesInPrice`,`Type`,`LastPrice`,`SBRuleID`,`SBTransID`,`OverrideCoinAllocation`)
  VALUES ($coinID,$coinPrice,$userID,'$baseCurrency', $sendEmail, $buyCoin, $quantity, $ruleIDBuy, $coinSellOffsetPct, $coinSellOffsetEnabled, $buyType, $minsToCancelBuy, $sellRuleFixed, 'Open', $toMerge, $noOfPurchases,$originalPrice, $risesInPrice, '$type',$coinPrice,$spreadBetRuleID
  ,$spreadBetTransID,$overrideCoinAlloc)";

  print_r($sql);
  LogToSQL("SpreadBetTrackingSQL","$sql",3,1);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("AddTrackingCoin: ".$sql, 'TrackingCoins', 0);
}

function runLowMarketMode($userID,$mode){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call setLowMarketMode($userID,90,$mode);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("runLowMarketMode: ".$sql,'TrackingCoins', 0);
  newLogToSQL("runLowMarketMode","$sql",3,0,"SQL CALL","UserID:$userID");
}

function getNewTrackingCoins($userID = 0){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
//12
  $whereClause = " ";
  if ($userID <> 0){ $whereClause = " WHERE `UserID` = $userID";}
  $sql = "SELECT `CoinID`,`CoinPrice`,`TrackDate`,`Symbol`,`LiveCoinPrice`,`PriceDifference`,`PctDifference`,`UserID`,`BaseCurrency`,`SendEmail`,`BuyCoin`,`Quantity`,`RuleIDBuy`,`CoinSellOffsetPct`
    ,`CoinSellOffsetEnabled`,`BuyType`,`MinsToCancelBuy`,`SellRuleFixed`,`APIKey`,`APISecret`,`KEK`,`Email`,`UserName`,`ID`,TIMESTAMPDIFF(MINUTE,`TrackDate`,  NOW()) as MinsFromDate, `NoOfPurchases`,`NoOfRisesInPrice`
    ,`TotalRisesInPrice`,`DisableUntil`,`NoOfCoinPurchase`,`OriginalPrice`,`BuyRisesInPrice`,`LimitBuyAmountEnabled`, `LimitBuyAmount`,`LimitBuyTransactionsEnabled`, `LimitBuyTransactions`
    ,`NoOfBuyModeOverrides`,`CoinModeOverridePriceEnabled`,ifnull(`CoinMode`,0) as CoinMode,`Type`, `LastPrice`,`SBRuleID`,`SBTransID`,`TrackingID`,`quickBuyCount`,timestampdiff(MINUTE,now(),`DisableUntil`) as MinsDisabled
    ,`OverrideCoinAllocation`
    FROM `TrackingCoinView`$whereClause";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['CoinID'],$row['CoinPrice'],$row['TrackDate'],$row['Symbol'],$row['LiveCoinPrice'],$row['PriceDifference'],$row['PctDifference'],$row['UserID'],$row['BaseCurrency'],$row['SendEmail'] //9
    ,$row['BuyCoin'],$row['Quantity'],$row['RuleIDBuy'],$row['CoinSellOffsetPct'],$row['CoinSellOffsetEnabled'],$row['BuyType'],$row['MinsToCancelBuy'],$row['SellRuleFixed'],$row['APIKey'],$row['APISecret'] //19
    ,$row['KEK'],$row['Email'],$row['UserName'],$row['ID'],$row['MinsFromDate'],$row['NoOfPurchases'],$row['NoOfRisesInPrice'],$row['TotalRisesInPrice'],$row['DisableUntil'],$row['NoOfCoinPurchase'],$row['OriginalPrice'] //30
    ,$row['BuyRisesInPrice'],$row['LimitBuyAmountEnabled'],$row['LimitBuyAmount'],$row['LimitBuyTransactionsEnabled'],$row['LimitBuyTransactions'],$row['NoOfBuyModeOverrides'],$row['CoinModeOverridePriceEnabled'] //37
    ,$row['CoinMode'],$row['Type'],$row['LastPrice'],$row['SBRuleID'],$row['SBTransID'],$row['TrackingID'],$row['quickBuyCount'],$row['MinsDisabled'],$row['OverrideCoinAllocation']);
  }
  $conn->close();
  return $tempAry;

}

function getOpenTransactions(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
//12
  $whereClause = " ";
  //if ($userID <> 0){ $whereClause = " WHERE `UserID` = $userID";}
  $sql = "SELECT `Uc`.`UserID`
            , ifnull(`Oct`.`NoOfTransactions`,0) as CoinModeTransactions
            , ifnull(`Orbt`.`NoOfTransactions`,0) as RuleBasedTransactions
            , ifnull(`Osbt`.`NoOfTransactions`,0) as SpreadBetTransactions
            FROM `UserConfig` `Uc`
            left join `OpenCoinModeTransactions` `Oct` on `Oct`.`UserID` = `Uc`.`UserID`
            left join `OpenRuleBasedTransactions` `Orbt` on `Orbt`.`UserID` = `Uc`.`UserID`
            left join `OpenSpreadBetTransactions` `Osbt` on `Osbt`.`UserID` =  `Uc`.`UserID`";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['UserID'],$row['CoinModeTransactions'],$row['RuleBasedTransactions'],$row['SpreadBetTransactions']);
  }
  $conn->close();
  return $tempAry;
}

function setNewTrackingPrice($coinPrice, $ID, $mode){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  if ($mode == 'Buy'){
      $sql = "UPDATE `TrackingCoins` SET `CoinPrice` = $coinPrice WHERE `ID` = $ID";
  }else{
    $sql = "UPDATE `TrackingSellCoins` SET `CoinPrice` = $coinPrice WHERE `ID` = $ID";
  }


  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("setNewTrackingPrice: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("setNewTrackingPrice",$sql,3,0,"SQL","TrackingID:$mode:$ID");
}

function setLastPrice($coinPrice, $ID, $mode){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  if ($mode == 'Buy'){
      $sql = "UPDATE `TrackingCoins` SET `LastPrice` = $coinPrice WHERE `ID` = $ID";
  }else{
    $sql = "UPDATE `TrackingSellCoins` SET `LastPrice` = $coinPrice WHERE `ID` = $ID";
  }


  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("setNewTrackingPrice: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("setNewTrackingPrice",$sql,3,0,"SQL","TrackingID:$mode:$ID");
}

function closeNewTrackingCoin($ID, $deleteFlag){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  if ($deleteFlag == True){
    $updateSQL = "DELETE from `TrackingCoins` ";
  }else{
    $updateSQL = "UPDATE `TrackingCoins` SET `Status` = 'Closed' ";
  }

  $sql = "$updateSQL WHERE `ID` = $ID";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("closeNewTrackingCoin: ".$sql. $conn->error, 'TrackingCoins', 0);
}

function updateTrackingCoinToMerge($ID, $noOfPurchases){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `Transaction` SET `ToMerge`= 1, `NoOfPurchases` =  $noOfPurchases WHERE `ID` = $ID ";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateTrackingCoinToMerge: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("updateTrackingCoinToMerge",$sql,3,0,"SQL","TransactionID:$ID");
}

function updateMergeAry($toMergeAry, $finalMergeAry){
  $finalMergeArySize = Count($finalMergeAry);
  $existing = False;
  for ($j=0; $j<$finalMergeArySize; $j++){
    echo "<BR> TEST ".$toMergeAry[0]."=".$finalMergeAry[$j][0]." & ".$toMergeAry[1]."=".$finalMergeAry[$j][1];
    if ($toMergeAry[0] == $finalMergeAry[$j][0] && $toMergeAry[1] == $finalMergeAry[$j][1]){
      //User/Coin exist
      echo "<BR> EXISTING is TRUE ".$toMergeAry[0]."=".$finalMergeAry[$j][0]." & ".$toMergeAry[1]."=".$finalMergeAry[$j][1];
      $existing = True;
      $finalMergeAry[$j][4] = $finalMergeAry[$j][4]+$toMergeAry[4];
      echo "<BR> adding ".$finalMergeAry[$j][4]."+".$toMergeAry[4];
      $finalMergeAry[$j][5] = $finalMergeAry[$j][5]+$toMergeAry[5];
      echo "<BR> adding ".$finalMergeAry[$j][5]."+".$toMergeAry[5];
      $finalMergeAry[$j][6] = $finalMergeAry[$j][6].$toMergeAry[3].",";
      echo "<BR> adding ".$toMergeAry[5];
      $finalMergeAry[$j][7] = $finalMergeAry[$j][7]+1;
      echo "<BR> adding ".$finalMergeAry[$j][7]."+1";
      $finalMergeAry[$j][8] = $finalMergeAry[$j][8];
      $finalMergeAry[$j][9] = $finalMergeAry[$j][9];
    }
  }
  if ($existing == False){
    echo "<BR> EXISTING is FALSE";
    //if ($finalMergeArySize == 0) {$finalMergeArySize = $finalMergeArySize;} else {$finalMergeArySize = $finalMergeArySize+1;}
    $finalMergeAry[$finalMergeArySize][0] = $toMergeAry[0];
    echo "<BR> SETTING: ".$toMergeAry[0];
    $finalMergeAry[$finalMergeArySize][1] = $toMergeAry[1];
    echo "<BR> SETTING: ".$toMergeAry[1];
    $finalMergeAry[$finalMergeArySize][2] = $toMergeAry[2];
    echo "<BR> SETTING: ".$toMergeAry[2];
    $finalMergeAry[$finalMergeArySize][3] = $toMergeAry[3];
    $finalMergeAry[$finalMergeArySize][4] = $toMergeAry[4];
    $finalMergeAry[$finalMergeArySize][5] = $toMergeAry[5];
    //$finalMergeAry[$finalMergeArySize][6] = $toMergeAry[0][3].",";
    $finalMergeAry[$finalMergeArySize][7] = 1;
    $finalMergeAry[$finalMergeArySize][8] = $toMergeAry[6];
    $finalMergeAry[$finalMergeArySize][9] = $toMergeAry[7];
  }
  return $finalMergeAry;
}


function UpdateTransCount($count,$transactionID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call UpdateTransCount($count,$transactionID);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("UpdateTransCount($count,$transactionID)",'TrackingCoins', 0);
  newLogToSQL("UpdateTransCount","$sql",3,0,"SQL CALL","TransactionID:$transactionID");
}

function mergeTransactions($transactionID, $amount, $avCost){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call MergeTransactions($avCost,$transactionID,$amount);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("mergeTransactions($avCost,$transactionID,$amount,$lastTransID)",'TrackingCoins', 0);
  newLogToSQL("mergeTransactions","$sql",3,0,"SQL CALL","TransactionID:$transactionID");
}

function deleteOldTrans($lastTransID){
  $piecesAry = explode(",",$lastTransID);
  $piecesArySize = count($piecesAry);

  for ($i=1; $i<$piecesArySize; $i++){
    $oldTransID = $piecesAry[$i];
    closeOldTransSQL($oldTransID);
  }
}

function closeOldTransSQL($id){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "DELETE FROM `Transaction` WHERE `ID` in ($id)";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("closeOldTransSQL: $sql",'TrackingCoins', 0);
}

function updateNoOfRisesInPrice($newTrackingCoinID, $num){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `TrackingCoins` SET `NoOfRisesInPrice`= $num WHERE `ID` = $newTrackingCoinID ";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateNoOfRisesInPrice: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("updateTrackingCoinToMerge",$sql,3,0,"SQL","TrackingCoinID:$newTrackingCoinID");
}

function getNumberColour($ColourText){
  if ($ColourText >= 0){
    $colour = "#3cb371";
  }elseif ($ColourText == 0) {
    $colour = "#ffa500";
  }else{
    $colour = "#ff0000";
  }
  return $colour;
}

function getSparklineData($coin){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `LiveCoinPrice` as LiveCoinPrice
    FROM `CoinBuyHistory`
    WHERE  (`ActionDate` > DATE_SUB((select Max(`ActionDate`) from `CoinBuyHistory`), INTERVAL 15 MINUTE)) and `ID` = (select Max(`ID`) from `Coin` where `Symbol` = '$coin')
    order by `ActionDate` asc ";
    $result = $conn->query($sql);
    //$result = mysqli_query($link4, $query);
    //mysqli_fetch_assoc($result);
    while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['LiveCoinPrice']);
    }
    $conn->close();
    return $tempAry;
}

function dataToString($seperator, $array){
  $num = count($array);
  $returnStr = "";
  for ($i=0; $i<$num; $i++){
    //echo "<BR> ".$array[$i][0];
    $returnStr .= round($array[$i][0],4).",";
  }
  return rtrim($returnStr,',');
}

function saveImage($coin, $url, $savePath){
  echo "<BR>".$url;
  echo "<BR>".$savePath.$coin.'.png';
  $ch = curl_init($url);
  $fp = fopen($savePath.$coin.'.png', 'wb');
  curl_setopt($ch, CURLOPT_FILE, $fp);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_exec($ch);
  curl_close($ch);
  fclose($fp);
}

function newTrackingSellCoins($LiveCoinPrice, $userID,$transactionID,$SellCoin,$SendEmail,$sellCoinOffsetEnabled,$sellCoinOffsetPct,$fallsInPrice){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "call AddTrackingSellCoin($LiveCoinPrice, $userID,$transactionID,$SellCoin,$SendEmail,$sellCoinOffsetEnabled,$sellCoinOffsetPct,$fallsInPrice);";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("newTrackingSellCoins: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("newTrackingSellCoins","$sql",3,0,"SQL CALL","TransactionID:$transactionID");
}

function setTransactionPending($id){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `Transaction` SET `Status`= 'Pending' WHERE `ID` = $id ";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("setTransactionPending: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("updateTrackingCoinToMerge",$sql,3,0,"SQL","TransactionID:$id");
}

function updateSellAmount($TransactionID,$Amount,$oldAmount){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `Transaction` SET `Amount`= $Amount,`OriginalAmount` = $oldAmount WHERE `ID` = $TransactionID ";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateSellAmount: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("updateSellAmount",$sql,3,1,"SQL","TransactionID:$TransactionID");
}

function getNewTrackingSellCoins($userID = 0){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
//12
  $whereClause = "";
  if ($userID <> 0){ $whereClause = " WHERE `UserID` = $userID";}
  $sql = "SELECT `CoinPrice`,`TrackDate`,`UserID`,`NoOfRisesInPrice`,`TransactionID`,`BuyRule`,`FixSellRule`,`OrderNo`,`Amount`,`CoinID`,`APIKey`,`APISecret`,`KEK`,`Email`,`UserName`,`BaseCurrency`
  ,`SendEmail`,`SellCoin`,`CoinSellOffsetEnabled`,`CoinSellOffsetPct`,`LiveCoinPrice`,TIMESTAMPDIFF(MINUTE, `TrackDate`, Now()) as MinsFromDate, `ProfitUSD`, `Fee`
  , (`LiveSellPrice`-`OriginalPurchasePrice`)/ `OriginalPurchasePrice` * 100 as `PctProfit`
  , `TotalRisesInPrice`, `Symbol`
  , (`LiveSellPrice`-(`OriginalCoinPrice` * `Amount`))/ (`OriginalCoinPrice` * `Amount`) * 100 as `OgPctProfit`, `OriginalPurchasePrice`,`OriginalCoinPrice`,`TotalRisesInPriceSell`,`TrackStartDate`
  ,TIMESTAMPDIFF(MINUTE, `TrackStartDate`, Now()) as MinsFromStart, `SellFallsInPrice`,`Type`,`BaseSellPrice`,`LastPrice`,`Amount`*`LiveSellPrice` as BTCBuyAmount, `TrackingSellID`,`SaveResidualCoins`
  FROM `TrackingSellCoinView`$whereClause";
  //echo $sql;
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['CoinPrice'],$row['TrackDate'],$row['UserID'],$row['NoOfRisesInPrice'],$row['TransactionID'],$row['BuyRule'],$row['FixSellRule'],$row['OrderNo'],$row['Amount'] //8
    ,$row['CoinID'],$row['APIKey'],$row['APISecret'],$row['KEK'],$row['Email'],$row['UserName'],$row['BaseCurrency'],$row['SendEmail'],$row['SellCoin'],$row['CoinSellOffsetEnabled'],$row['CoinSellOffsetPct'] //19
    ,$row['LiveCoinPrice'],$row['MinsFromDate'],$row['ProfitUSD'],$row['Fee'],$row['PctProfit'],$row['TotalRisesInPrice'],$row['Symbol'],$row['OgPctProfit'],$row['OriginalPurchasePrice'],$row['OriginalCoinPrice'] //29
    ,$row['TotalRisesInPriceSell'],$row['TrackStartDate'],$row['MinsFromStart'],$row['SellFallsInPrice'], $row['Type'], $row['BaseSellPrice'], $row['LastPrice'], $row['BTCBuyAmount'], $row['TrackingSellID'] //38
  , $row['SaveResidualCoins']);
  }
  $conn->close();
  return $tempAry;

}

function closeNewTrackingSellCoin($ID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `TrackingSellCoins` SET `Status` = 'Closed' WHERE `TransactionID` = $ID";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("closeNewTrackingSellCoin: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("closeNewTrackingSellCoin",$sql,3,0,"SQL","TrackingSellCoinsID:$ID");
}

function setNewTrackingSellPrice($coinPrice, $ID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `TrackingSellCoins` SET `CoinPrice` = $coinPrice, `TrackDate` = CURRENT_TIMESTAMP() WHERE `TransactionID` = $ID";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("setNewTrackingSellPrice: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("setNewTrackingSellPrice",$sql,3,0,"SQL","TrackingSellCoinsID:$ID");
}

function updateNoOfRisesInSellPrice($newTrackingCoinID, $num, $price){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `TrackingSellCoins` SET `NoOfRisesInPrice`= $num, `CoinPrice` = $price WHERE `ID` = $newTrackingCoinID ";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateNoOfRisesInSellPrice: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("updateNoOfRisesInSellPrice",$sql,3,0,"SQL","TrackingSellCoinsID:$newTrackingCoinID");
}

function reopenTransaction($id){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `Transaction` SET `Status`= 'Open' WHERE `ID` = $id";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("reopenTransaction: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("reopenTransaction",$sql,3,0,"SQL","TransactionID:$id");
}

function getReservedAmount($baseCurrency, $userID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
//12

  $sql = "select (
            SELECT sum(`CoinPrice` * `Quantity`)
            from `TrackingCoins` where `BaseCurrency` = 'USDT' and `UserID` = 3 and `Status` = 'Open') as TotalReservedUSDT
            ,ifnull((SELECT sum(`CoinPrice`*`Quantity` )
            from `TrackingCoins` where `BaseCurrency` = 'BTC' and `UserID` = 3 and `Status` = 'Open'),0)as TotalReservedBTC
            ,ifnull((SELECT sum(`CoinPrice`*`Quantity` )
            from `TrackingCoins` where `BaseCurrency` = 'ETH' and `UserID` = 3 and `Status` = 'Open'),0)as TotalReservedETH";
  //echo $sql;
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['TotalReservedUSDT'],$row['TotalReservedBTC'],$row['TotalReservedETH']);
  }
  $conn->close();
  return $tempAry;
}

function getBasePrices(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
//12

  $sql = "select (SELECT `LiveCoinPrice`  FROM `CoinPrice` WHERE `CoinID` in (84)) as BTCPrice
          , (SELECT `LiveCoinPrice` FROM `CoinPrice` WHERE `CoinID` in (85) )as ETHPrice ";
  //echo $sql;
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['BTCPrice'],$row['ETHPrice']);
  }
  $conn->close();
  return $tempAry;
}

function getUserDisabled($userID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
//12

  $sql = "SELECT `DisableUntil` FROM `User` WHERE `ID` = $userID";
  //echo $sql;
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['DisableUntil']);
  }
  $conn->close();
  return $tempAry;
}

function logHoldingTimeToSQL($coinID, $holdingMins){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "INSERT INTO `AverageCoinHolingTime`(`CoinID`, `MinsHolding`) VALUES ($coinID, $holdingMins)";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("logHoldingTimeToSQL: ".$sql, 'SellCoins', 0);
}

function cancelTrackingSell($id){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `TrackingSellCoins` SET `Status`= 'Closed' WHERE `TransactionID` = $id";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("cancelTrackingSell: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("cancelTrackingSell",$sql,3,0,"SQL","TransactionID:$id");
}

function getNewTrackingSellCoinTrans($ID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
//12
  //$whereClause = "";
  //if ($userID <> 0){ $whereClause = " WHERE `UserID` = $userID";}
  $sql = "SELECT `CoinPrice`,`TrackDate`,`UserID`,`NoOfRisesInPrice`,`TransactionID`,`BuyRule`,`FixSellRule`,`OrderNo`,`Amount`,`CoinID`,`APIKey`,`APISecret`,`KEK`,`Email`,`UserName`,`BaseCurrency`
  ,`SendEmail`,`SellCoin`,`CoinSellOffsetEnabled`,`CoinSellOffsetPct`,`LiveCoinPrice`,TIMESTAMPDIFF(MINUTE,  NOW(),`TrackDate`) as MinsFromDate, `ProfitUSD`, `Fee`
  , (`LiveSellPrice`-`OriginalPurchasePrice`)/ `OriginalPurchasePrice` * 100 as `PctProfit`
  , `TotalRisesInPrice`, `Symbol`
  , (`LiveSellPrice`-(`OriginalCoinPrice` * `Amount`))/ (`OriginalCoinPrice` * `Amount`) * 100 as `OgPctProfit`, `OriginalPurchasePrice`,`OriginalCoinPrice`,`TotalRisesInPriceSell`
  FROM `TrackingSellCoinView` where `TransactionID` = $ID";
  //echo $sql;
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['CoinPrice'],$row['TrackDate'],$row['UserID'],$row['NoOfRisesInPrice'],$row['TransactionID'],$row['BuyRule'],$row['FixSellRule'],$row['OrderNo'],$row['Amount'] //8
    ,$row['CoinID'],$row['APIKey'],$row['APISecret'],$row['KEK'],$row['Email'],$row['UserName'],$row['BaseCurrency'],$row['SendEmail'],$row['SellCoin'],$row['CoinSellOffsetEnabled'],$row['CoinSellOffsetPct'] //19
    ,$row['LiveCoinPrice'],$row['MinsFromDate'],$row['ProfitUSD'],$row['Fee'],$row['PctProfit'],$row['TotalRisesInPrice'],$row['Symbol'],$row['OgPctProfit'],$row['OriginalPurchasePrice'],$row['OriginalCoinPrice']
    ,$row['TotalRisesInPriceSell']);
  }
  $conn->close();
  return $tempAry;

}

function setMobileVariables(){
  if ($_SESSION['MobOverride'] == False && $_SESSION['isMobile']){
    $_SESSION['MobDisplay'] = 0;
  }elseif ($_SESSION['MobOverride']){
    $_SESSION['MobDisplay'] = 2;
  }
}

function updateSQLQuantity($uuid, $quantity){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `Transaction` SET `Amount` = $quantity WHERE `BittrexRef` = '$uuid'";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateSQLQuantity: ".$sql, 'BuyCoin', 0);
  newLogToSQL("updateSQLQuantity",$sql,3,0,"SQL","BittrexRef:$uuid");
}

function getCoinPriceMatchNames($userID, $table, $limit){
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Name`,`ID` FROM $table $limit";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Name'],$row['ID']);
  }
  $conn->close();
  return $tempAry;
}

function getTotalProfit(){
  $conn = getSQLConn(rand(1,3));
  $tempAry = [];
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT sum(`LivePrice`) as TotalLivePrice,sum(`PurchasePrice`) as TotalPurchasePrice, sum(`Profit`) as TotalProfit
        , if (sum(`Profit`)<0, -1*abs(sum(`Profit`))/sum(`PurchasePrice`)*100 , abs(sum(`Profit`))/sum(`PurchasePrice`)*100) as ProfitPct
        ,`UserID`
        FROM `NewUserProfit`
        group by `UserID`";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['TotalLivePrice'],$row['TotalPurchasePrice'],$row['TotalProfit'],$row['ProfitPct'],$row['UserID']);
  }
  $conn->close();
  return $tempAry;
}

function findUserProfit($userProfit, $userID){
  if (!is_null($userProfit)){
    $userProfitSize = count($userProfit);
    for ($i=0; $i<$userProfitSize; $i++){
      if ($userProfit[$i][4] == $userID){
        return $userProfit[$i][3];
      }
    }
  }
}

function pauseRule($id, $hours, $userID = 0){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $whereClause = "";
  if ($hours == 0){ $dateClause = "DATE_SUB(now(),interval 24 hour)";}else{ $dateClause = "DATE_ADD(now(),interval $hours hour)";}
  if ($userID <> 0){ $whereClause = " and `UserID` = $userID ";}

  $sql = "UPDATE `BuyRules` SET `DisableUntil`= CONVERT_TZ($dateClause ,'-08:00','+04:00')
          WHERE `ID` in ($id) $whereClause and `OverrideDisableRule` = 0";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("pauseRule: ".$sql, 'BuyCoin', 0);
  newLogToSQL("pauseRule",$sql,3,0,"SQL","BuyRuleID:$id");
}

function getDailyBalance($apikey,$apisecret){
  $timestamp = time()*1000;
  $url = "https://api.bittrex.com/v3/balances/";
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
  $execResult = curl_exec($ch);
  curl_close($ch);
  $temp = json_decode($execResult, true);
  return $temp;
}

function updateBittrexBalances($symbol, $total, $price, $userID){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $sql = "Call AddBittrexBal('$symbol',$total,$price, $userID);";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("updateBittrexBalances","$sql",3,0,"SQL CALL","UserID:$userID");
}

function addUSDTBalance($symbol, $usdtPurchase, $price, $userID){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $sql = "Call UpdateUSDTBal('$symbol',$usdtPurchase, $userID, $price);";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("addUSDTBalance","$sql",3,0,"SQL CALL","UserID:$userID");
}

function subUSDTBalance($symbol, $usdtPurchase, $price, $userID){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $sql = "Call subUSDTBal('$symbol',$usdtPurchase, $userID, $price);";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("subUSDTBalance","$sql",3,0,"SQL CALL","UserID:$userID");
}

function pauseTracking($userID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `TrackingCoins` SET `BuyCoin` = 0 WHERE `Status` = 'Open' and `UserID` = $userID";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("pauseTracking: ".$sql, 'BuyCoin', 0);
  newLogToSQL("pauseTracking",$sql,3,0,"SQL","UserID:$userID");
}

function getMarketProfit(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `5MinProfitPct`,`1HrProfitPct` FROM `MarketProfitPct` ";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['5MinProfitPct'],$row['1HrProfitPct']);
  }
  $conn->close();
  return $tempAry;
}

function getRuleProfit(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT IFNULL(sum(`Nup`.`LivePrice`),0) as TotalLivePrice,IFNULL(sum(`Nup`.`PurchasePrice`),0) as TotalPurchasePrice, IFNULL(sum(`Nup`.`Profit`),0) as TotalProfit
        , IFNULL(if (sum(`Nup`.`Profit`)<0, -1*abs(sum(`Nup`.`Profit`))/sum(`Nup`.`PurchasePrice`)*100 , abs(sum(`Nup`.`Profit`))/sum(`Nup`.`PurchasePrice`)*100),0) as ProfitPct
        ,`Br`.`ID` as RuleID
        ,count(`Nup`.`RuleID`) as RuleIDCount
        FROM `NewUserProfit` `Nup`
        right join `BuyRules` `Br` on `Br`.`ID` =  `Nup`.`RuleID`
        group by `RuleID`";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['TotalLivePrice'],$row['TotalPurchasePrice'],$row['TotalProfit'],$row['ProfitPct'],$row['RuleID'],$row['RuleIDCount']);
  }
  $conn->close();
  return $tempAry;
}

function assignNewSellID($transID, $sellRuleID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `Transaction` SET `FixSellRule` = $sellRuleID WHERE `Status` = 'Open' and `ID` = $transID";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("assignNewSellID: ".$sql, 'BuyCoin', 0);
  newLogToSQL("assignNewSellID",$sql,3,0,"SQL","TransactionID:$transID");
}

function setTransStatus($status,$transID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `Transaction` SET `Status` = '$status' WHERE `ID` = $transID";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("setTransStatus: ".$sql, 'SellCoin', 0);
  newLogToSQL("setTransStatus",$sql,3,0,"SQL","TransactionID:$transID");
}

function setCustomisedSellRule($buyRule, $coinID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "Call CustomisedSellRule($buyRule,$coinID);";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("setCustomisedSellRule: ".$sql, 'SellCoin', 0);
  newLogToSQL("setCustomisedSellRule","$sql",3,0,"SQL CALL","CoinID:$coinID RuleID:$buyRule");
}

function setCustomisedSellRuleBased($buyRule, $coinID, $pctToSell){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "call CustomisedSell_RuleBased($coinID,$buyRule,$pctToSell);";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("setCustomisedSellRuleBased: ".$sql, 'SellCoin', 0);
  newLogToSQL("setCustomisedSellRuleBased","$sql",3,0,"SQL CALL","CoinID:$coinID RuleID:$buyRule");
}

function coinSwapBuyModeLookup($coinID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `CoinID` FROM `CoinModeRules` WHERE `CoinID` != $coinID and `ModeID` = 1  limit 1 ";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['CoinID']);
  }
  $conn->close();
  return $tempAry;
}

function coinSwapSell($livePrice, $transactionID,$coinID,$buyRule, $buyAmount){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "Call CoinSwapSell($livePrice, $transactionID,$coinID,$buyRule, $buyAmount);";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("coinSwapSell: ".$sql, 'SellCoin', 0);
  newLogToSQL("coinSwapSell","$sql",3,0,"SQL CALL","CoinID:$coinID TransactionID:$transactionID");
}

function getCoinMode($userID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `RuleID`, `CoinID`, `LiveCoinPrice`, `Avg6MonthMax`, `Avg6MonthMin`, `0MinsMin`, `15MinsMin`, `30MinsMin`, `45MinsMin`, `0MinsMax`, `15MinsMax`, `30MinsMax`
  , `45MinsMax`, `Live1HrChange`, `Last1HrChange`, `Live24HrChange`, `Last24HrChange`, `Live7DChange`, `Last7DChange`, `RuleIDSell`, `USDBuyAmount`, `1HourAvgPrice`, `ProjectedPriceMax`
  , `ProjectedPriceMin`, `UserID`, `ModeID`, `Hr1Top`, `Hr1Btm`, `Hr24Top`, `Hr24Btm`, `D7Top`, `D7Btm`, `SecondarySellRules`, `CoinModeEmails`, `Email`, `UserName`, `Symbol`
  , `CoinModeEmailsSell`, `CoinModeMinsToCancelBuy`,`PctToBuy`,`PctOfAllTimeHigh`,`1HrChange` FROM `CoinModePricesView` WHERE `UserID` = $userID ";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['RuleID'],$row['CoinID'],$row['LiveCoinPrice'],$row['Avg6MonthMax'],$row['Avg6MonthMin'],$row['0MinsMin'],$row['15MinsMin'],$row['30MinsMin'],$row['45MinsMin'],$row['0MinsMax'],$row['15MinsMax'] //10
      ,$row['30MinsMax'],$row['45MinsMax'],$row['Live1HrChange'],$row['Last1HrChange'],$row['Live24HrChange'],$row['Last24HrChange'],$row['Live7DChange'],$row['Last7DChange'],$row['RuleIDSell'],$row['USDBuyAmount'] //20
      ,$row['1HourAvgPrice'],$row['ProjectedPriceMax'],$row['ProjectedPriceMin'],$row['UserID'],$row['ModeID'],$row['Hr1Top'],$row['Hr1Btm'],$row['Hr24Top'],$row['Hr24Btm'],$row['D7Top'],$row['D7Btm'],$row['SecondarySellRules'] //32
      ,$row['CoinModeEmails'],$row['Email'],$row['UserName'],$row['Symbol'],$row['CoinModeEmailsSell'],$row['CoinModeMinsToCancelBuy'],$row['PctToBuy'],$row['PctOfAllTimeHigh'],$row['1HrChange']);
  }
  $conn->close();
  return $tempAry;
}

function updateBuyAmount($transactionID, $amount){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `Transaction` SET `Amount` = $amount WHERE `ID` = $transactionID";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateBuyAmount: ".$sql, 'BuyCoin', 0);
  newLogToSQL("updateBuyAmount",$sql,3,0,"SQL","TransactionID:$transactionID");
}

function cancelTrackingBuy($ruleId){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `TrackingCoins` SET `Status` = 'Closed' where `RuleIDBuy` = $ruleId";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("cancelTrackingBuy: ".$sql, 'BuyCoin', 0);
  newLogToSQL("cancelTrackingBuy",$sql,3,0,"SQL","BuyRuleID:$ruleId");
}

function UpdateProfit(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "call UpdateNewUserProfit();";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("UpdateProfit: ".$sql, 'BuyCoin', 0);
  newLogToSQL("UpdateProfit","$sql",3,0,"SQL CALL","All Users");
}

function UpdateSpreadBetTotalProfit(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "call UpdateSpreadBetTotalProfit();";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("UpdateSpreadBetTotalProfit: ".$sql, 'BuyCoin', 0);
  newLogToSQL("UpdateSpreadBetTotalProfit","$sql",3,0,"SQL CALL","All Users");
}

function extendPctToBuy($coinID, $userID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "Call AddToPct($coinID,$userID, 0.24);";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("extendPctToBuy: ".$sql, 'SellCoin', 0);
  newLogToSQL("extendPctToBuy","$sql",3,0,"SQL CALL","UserID:$userID CoinID:$coinID");
}

function getSpreadBetData(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `ID`, `Name`, `Live1HrChange`, `Last1HrChange`, `Hr1ChangePctChange`, `Live24HrChange`, `Last24HrChange`, `Hr24ChangePctChange`, `Live7DChange`, `Last7DChange`
  , `D7ChangePctChange`, `LiveCoinPrice`, `LastCoinPrice`, `CoinPricePctChange`,  `BaseCurrency`, `Price4Trend`, `Price3Trend`, `LastPriceTrend`, `LivePriceTrend`, `AutoBuyPrice`
  , `1HrPriceChangeLive`, `1HrPriceChangeLast`, `1HrPriceChange3`, `1HrPriceChange4`,`APIKey`,`APISecret`,`KEK`,`UserID`,`Email`,`UserName`,`SpreadBetTransID`, `Hr1BuyPrice`, `Hr24BuyPrice`
  , `D7BuyPrice`,`PctofSixMonthHighPrice`,`PctofAllTimeHighPrice`,`DisableUntil`,`UserID`,`CalculatedFallsinPrice`,`CalculatedMinsToCancel` FROM `SpreadBetCoinStatsView` ";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'], $row['Name'], $row['Live1HrChange'], $row['Last1HrChange'], $row['Hr1ChangePctChange'], $row['Live24HrChange'], $row['Last24HrChange'], $row['Hr24ChangePctChange'], $row['Live7DChange'], $row['Last7DChange']//9
      , $row['D7ChangePctChange'], $row['LiveCoinPrice'], $row['LastCoinPrice'], $row['CoinPricePctChange'], $row['BaseCurrency'], $row['Price4Trend'], $row['Price3Trend'], $row['LastPriceTrend'], $row['LivePriceTrend'], $row['AutoBuyPrice']//19
      , $row['1HrPriceChangeLive'], $row['1HrPriceChangeLast'], $row['1HrPriceChange3'], $row['1HrPriceChange4'], $row['APIKey'], $row['APISecret'], $row['KEK'], $row['UserID'], $row['Email'], $row['UserName'], $row['SpreadBetTransID'] //30
      , $row['Hr1BuyPrice'], $row['Hr24BuyPrice'], $row['D7BuyPrice'], $row['PctofSixMonthHighPrice'], $row['PctofAllTimeHighPrice'], $row['DisableUntil'], $row['UserID'], $row['CalculatedFallsinPrice'], $row['CalculatedMinsToCancel']);
  }
  $conn->close();
  return $tempAry;
}

function getSpreadCoinData($ID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `ID`, `Symbol`, `LiveBuyOrders`, `LastBuyOrders`, `BuyOrdersPctChange`, `LiveMarketCap`, `LastMarketCap`, `MarketCapPctChange`, `Live1HrChange`, `Last1HrChange`, `Hr1ChangePctChange`, `Live24HrChange`, `Last24HrChange`, `Hr24ChangePctChange`
  , `Live7DChange`, `Last7DChange`, `D7ChangePctChange`, `LiveCoinPrice`, `LastCoinPrice`, `CoinPricePctChange`, `LiveSellOrders`, `LastSellOrders`, `SellOrdersPctChange`, `LiveVolume`, `LastVolume`, `VolumePctChange`, `BaseCurrency`, `Price4Trend`
  , `Price3Trend`, `LastPriceTrend`, `LivePriceTrend`, `AutoBuyPrice`, `1HrPriceChangeLive`, `1HrPriceChangeLast`, `1HrPriceChange3`, `1HrPriceChange4`, `CMCID`, `SecondstoUpdate`, `LastUpdated`, `Name`, `Image`, `SpreadBetRuleID`,`SpreadBetTransactionID`
  FROM `SpreadBetCoinStatsCoinView` where `SpreadBetRuleID` = $ID";
  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'], $row['Symbol'], $row['LiveBuyOrders'], $row['LastBuyOrders'], $row['BuyOrdersPctChange'], $row['LiveMarketCap'], $row['LastMarketCap'], $row['MarketCapPctChange'], $row['Live1HrChange'], $row['Last1HrChange'] //9
      , $row['Hr1ChangePctChange'], $row['Live24HrChange'], $row['Last24HrChange'], $row['Hr24ChangePctChange'], $row['Live7DChange'], $row['Last7DChange'], $row['D7ChangePctChange'], $row['LiveCoinPrice'], $row['LastCoinPrice'], $row['CoinPricePctChange'] //19
      , $row['LiveSellOrders'], $row['LastSellOrders'], $row['SellOrdersPctChange'], $row['LiveVolume'], $row['LastVolume'], $row['VolumePctChange'], $row['BaseCurrency'], $row['Price4Trend'], $row['Price3Trend'], $row['LastPriceTrend'] //29
      , $row['LivePriceTrend'], $row['AutoBuyPrice'], $row['1HrPriceChangeLive'], $row['1HrPriceChangeLast'], $row['1HrPriceChange3'], $row['1HrPriceChange4'], $row['CMCID'], $row['SecondstoUpdate'], $row['LastUpdated'], $row['Name'], $row['Image']  //40
      , $row['SpreadBetRuleID'], $row['SpreadBetTransactionID']);
  }
  $conn->close();
  return $tempAry;
}

function SpreadBetBittrexCancelPartialSell($oldID,$coinID, $quantity){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
    $sql = "UPDATE `Transaction` AS `t1` JOIN `Transaction` AS `t2` ON `t2`.`ID` = $oldID
            SET    `t1`.`Type` = 'SpreadSell'
            , `t1`.`SpreadBetRuleID` = `t2`.`SpreadBetRuleID`
            , `t1`.`SpreadBetTransactionID` = `t2`.`SpreadBetTransactionID`
            WHERE `t1`.`CoinID` = $coinID and `t1`.`Amount` = $quantity
            order by `ID` Desc
            limit 1 ";
    LogToSQL("SpreadBetBittrexCancelPartialSell",$sql,3,1);
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("SpreadBetBittrexCancelPartialSell: ".$sql, 'BuyCoin', 0);
  newLogToSQL("SpreadBetBittrexCancelPartialSell",$sql,3,0,"SQL","TransactionID:$oldID");
}

function SpreadBetBittrexCancelPartialBuy($transactionID,$quantity){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
    $sql = "UPDATE `Transaction` SET `Amount` =  $quantity where `ID` = $transactionID";
    LogToSQL("SpreadBetBittrexCancelPartialBuy",$sql,3,1);
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("SpreadBetBittrexCancelPartialBuy: ".$sql, 'BuyCoin', 0);
  newLogToSQL("SpreadBetBittrexCancelPartialBuy",$sql,3,0,"SQL","TransactionID:$transactionID");
}

function updateTransToSpread($SBRuleID,$coinID, $userID,$SBTransID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "Call UpdateTransToSpread($SBRuleID,$coinID,$userID,$SBTransID);";
    LogToSQL("updateTransToSpread",$sql,3,1);
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateTransToSpread: ".$sql, 'BuyCoin', 0);
  newLogToSQL("updateTransToSpread","$sql",3,0,"SQL CALL","UserID:$userID CoinID:$coinID");
}

function getCoinAllocation($userID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `RuleBasedAvailable`,`CoinModeAvailable`,`SpreadBetAvailable`,`ID` FROM `CoinAmountsAvailableToBuy`
          where `ID` = $userID";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['RuleBasedAvailable'],$row['CoinModeAvailable'],$row['SpreadBetAvailable'],$row['ID']);
  }
  $conn->close();
  return $tempAry;
}

function updateToSpreadSell($transID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "UPDATE `Transaction` SET `Type`= 'SpreadSell' WHERE  `ID` = $transID";
    LogToSQL("updateToSpreadSell",$sql,3,1);
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateToSpreadSell: ".$sql, 'BuyCoin', 0);
  newLogToSQL("updateToSpreadSell",$sql,3,0,"SQL","TransactionID:$transID");
}

function getSpreadBetSellData($ID = 0){
  $tempAry = [];
  $whereClause = '';
  if ($ID <> 0) { $whereClause = " Where `UserID` = $ID";}
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `ID`, `Type`, `UserID`, `CoinPrice`, `Amount`, `Status`, `OrderDate`, `CompletionDate`, `LastBuyOrders`, `LiveBuyOrders`, `BuyOrdersPctChange`, `LastMarketCap`, `LiveMarketCap`, `MarketCapPctChange`
  , `LastCoinPrice`, `LiveCoinPrice`, `CoinPricePctChange`, `LastSellOrders`, `LiveSellOrders`, `SellOrdersPctChange`, `LastVolume`, `LiveVolume`, `VolumePctChange`, `Last1HrChange`, `Live1HrChange`, `Hr1PctChange`
  , `Last24HrChange`, `Live24HrChange`, `Hr24PctChange`, `Last7DChange`, `Live7DChange`, `D7PctChange`, `BaseCurrency`, `AutoSellPrice`, `Price4Trend`, `Price3Trend`, `LastPriceTrend`, `LivePriceTrend`, `FixSellRule`
  , `SellRule`, `BuyRule`, `ToMerge`, `LowPricePurchaseEnabled`, `PurchaseLimit`, `PctToPurchase`, `BTCBuyAmount`, `NoOfPurchases`, `Name`, `Image`, `MaxCoinMerges`,`APIKey`,`APISecret`,`KEK`,`Email`,`UserName`,`PctProfitSell`
  ,`SpreadBetRuleID`,`CaptureTrend`,`Profit`,`PurchasePrice`,`LivePrice`,`CalculatedRisesInPrice`
  FROM `SellCoinsSpreadGroupView` $whereClause";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array( $row['ID'],$row['Type'],$row['UserID'],$row['CoinPrice'],$row['Amount'],$row['Status'],$row['OrderDate'],$row['CompletionDate'],$row['LastBuyOrders'],$row['LiveBuyOrders'],$row['BuyOrdersPctChange'] //10
      ,$row['LastMarketCap'],$row['LiveMarketCap'],$row['MarketCapPctChange'],$row['LastCoinPrice'],$row['LiveCoinPrice'],$row['CoinPricePctChange'],$row['LastSellOrders'],$row['LiveSellOrders'],$row['SellOrdersPctChange'] //19
      ,$row['LastVolume'],$row['LiveVolume'],$row['VolumePctChange'],$row['Last1HrChange'],$row['Live1HrChange'],$row['Hr1PctChange'],$row['Last24HrChange'],$row['Live24HrChange'],$row['Hr24PctChange'],$row['Last7DChange'] //29
      ,$row['Live7DChange'],$row['D7PctChange'],$row['BaseCurrency'],$row['AutoSellPrice'],$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['FixSellRule'],$row['SellRule'],$row['BuyRule']//40
      ,$row['ToMerge'],$row['LowPricePurchaseEnabled'],$row['PurchaseLimit'],$row['PctToPurchase'],$row['BTCBuyAmount'],$row['NoOfPurchases'],$row['Name'],$row['Image'],$row['MaxCoinMerges'],$row['APIKey'],$row['APISecret'],$row['KEK'] //52
      ,$row['Email'],$row['UserName'],$row['PctProfitSell'],$row['SpreadBetRuleID'],$row['CaptureTrend'],$row['Profit'],$row['PurchasePrice'],$row['LivePrice'],$row['CalculatedRisesInPrice']); //61
  }
  $conn->close();
  return $tempAry;
}

function getSpreadCoinSellData($ID = 0){
  if ($ID == 0){
    $whereclause = "";
  }else{
    $whereclause = " WHERE `SpreadBetTransactionID` = $ID ";
  }
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `ID`, `Type`, `CoinID`, `UserID`, `CoinPrice`, `Amount`, `Status`, `OrderDate`, `CompletionDate`, `BittrexID`, `OrderNo`, `Symbol`, `LastBuyOrders`, `LiveBuyOrders`, `BuyOrdersPctChange`, `LastMarketCap`
  , `LiveMarketCap`, `MarketCapPctChange`, `LastCoinPrice`, `LiveCoinPrice`, `CoinPricePctChange`, `LastSellOrders`, `LiveSellOrders`, `SellOrdersPctChange`, `LastVolume`, `LiveVolume`, `VolumePctChange`, `Last1HrChange`
  , `Live1HrChange`, `Hr1PctChange`, `Last24HrChange`, `Live24HrChange`, `Hr24PctChange`, `Last7DChange`, `Live7DChange`, `D7PctChange`, `BaseCurrency`, `AutoSellPrice`, `Price4Trend`, `Price3Trend`, `LastPriceTrend`
  , `LivePriceTrend`, `FixSellRule`, `SellRule`, `BuyRule`, `ToMerge`, `LowPricePurchaseEnabled`, `PurchaseLimit`, `PctToPurchase`, `BTCBuyAmount`, `NoOfPurchases`, `Name`, `Image`, `MaxCoinMerges`, `SpreadBetTransactionID`
  ,`PctToSave`,`CalculatedRisesInPrice`,`SpreadBetRuleID`,`PctProfitSell`,`AutoBuyBackSell`
  FROM `SellCoinsSpreadView` $whereclause";
  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['Type'],$row['CoinID'],$row['UserID'],$row['CoinPrice'],$row['Amount'],$row['Status'],$row['OrderDate'],$row['CompletionDate'],$row['BittrexID'],$row['OrderNo'] //10
      ,$row['Symbol'],$row['LastBuyOrders'],$row['LiveBuyOrders'],$row['BuyOrdersPctChange'],$row['LastMarketCap'],$row['LiveMarketCap'],$row['MarketCapPctChange'],$row['LastCoinPrice'],$row['LiveCoinPrice'] //19
      ,$row['CoinPricePctChange'],$row['LastSellOrders'],$row['LiveSellOrders'],$row['SellOrdersPctChange'],$row['LastVolume'],$row['LiveVolume'],$row['VolumePctChange'],$row['Last1HrChange'],$row['Live1HrChange'] //28
      ,$row['Hr1PctChange'],$row['Last24HrChange'],$row['Live24HrChange'],$row['Hr24PctChange'],$row['Last7DChange'],$row['Live7DChange'],$row['D7PctChange'],$row['BaseCurrency'],$row['AutoSellPrice'] //37
      ,$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['FixSellRule'],$row['SellRule'],$row['BuyRule'],$row['ToMerge'],$row['LowPricePurchaseEnabled'],$row['PurchaseLimit'] //47
      ,$row['PctToPurchase'],$row['BTCBuyAmount'],$row['NoOfPurchases'],$row['Name'],$row['Image'],$row['MaxCoinMerges'],$row['SpreadBetTransactionID'],$row['PctToSave'],$row['CalculatedRisesInPrice'] //56
    ,$row['SpreadBetRuleID'],$row['PctProfitSell'],$row['AutoBuyBackSell']);
  }
  $conn->close();
  return $tempAry;
}

function newSpreadTransactionID($UserID, $spreadBetRuleID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "Call NewSpreadBetTransaction($UserID,$spreadBetRuleID);";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("newSpreadTransactionID: ".$sql, 'BuyCoin', 0);
  newLogToSQL("newSpreadTransactionID","$sql",3,0,"SQL CALL","UserID:$userID SBRuleID:$spreadBetRuleID");
}

function addProfitToAllocation($UserID, $totalProfit, $type, $profitPct, $coinID){
  $savingUsdt = $totalProfit * $profitPct;
  $typeUsdt = $totalProfit - $savingUsdt;
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  if ($type == 'CoinMode'){
      $sql = "UPDATE `CoinAllocations` SET `Saving`=(`Saving`+ $savingUsdt),`CoinMode`= (`CoinMode` + $typeUsdt) WHERE `UserID` = $UserID";
  }elseif ($type == 'SpreadBet'){
      $sql = "UPDATE `CoinAllocations` SET `Saving`=(`Saving`+ $savingUsdt),`SpreadBet`=(`SpreadBet` + $typeUsdt) WHERE `UserID` = $UserID";
  }else{
      $sql = "UPDATE `CoinAllocations` SET `Saving`=(`Saving`+ $savingUsdt) WHERE `UserID` = $UserID";
  }

  print_r($sql);
  logToSQL("ProfitAllocation","$sql | $coinID",$UserID,1);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("addProfitToAllocation: ".$sql, 'BuyCoin', 0);
  newLogToSQL("addProfitToAllocation",$sql,3,0,"SQL","UserID:$UserID");
}

function getOpenSpreadCoins($userID, $spreadBetRuleID = 0){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  $whereClause = "";
  if ($spreadBetRuleID <> 0){
    $whereClause = " and `Tr`.`SpreadBetRuleID` = $spreadBetRuleID";
  }
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Tr`.`SpreadBetRuleID` as SpreadBetRuleID, `Tr`.`UserID` , count( DISTINCT `Tr`.`SpreadBetTransactionID`) as countOfTransactions
FROM `Transaction` `Tr`
    WHERE `Tr`.`Type` in ('SpreadBuy','SpreadSell') and `Tr`.`Status` in ('Open','Pending') and `Tr`.`UserID` = $userID $whereClause
    group by `Tr`.`SpreadBetRuleID` ";
  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['SpreadBetRuleID'],$row['UserID'],$row['countOfTransactions']);
  }
  $conn->close();
  return $tempAry;
}

function updateSpreadBuy($spreadBetRuleID){
  //$savingUsdt = $totalProfit * 0.1;
  //$typeUsdt = $totalProfit - $savingUsdt;
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `SpreadBetSettings` SET `NoOfTransactions` = (`NoOfTransactions` + 1) WHERE `SpreadBetRuleID` = $spreadBetRuleID;";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateSpreadBuy: ".$sql, 'BuyCoin', 0);
  newLogToSQL("updateSpreadBuy",$sql,3,0,"SQL","SBRuleID:$spreadBetRuleID");
}

function updateSpreadProfit($spreadBetRuleID, $pctProfit){
  //$savingUsdt = $totalProfit * 0.1;
  //$typeUsdt = $totalProfit - $savingUsdt;
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call updateSpreadProfit($spreadBetRuleID,$pctProfit);";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateSpreadProfit: ".$sql, 'BuyCoin', 0);
  newLogToSQL("updateSpreadProfit","$sql",3,0,"SQL CALL","SBRuleID:$spreadBetRuleID");
}

function updateSpreadSell($spreadBetRuleID, $orderDate){
  //$savingUsdt = $totalProfit * 0.1;
  //$typeUsdt = $totalProfit - $savingUsdt;
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call updateSpreadSell($spreadBetRuleID,'$orderDate');";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  //logAction("updateSpreadSell: ".$sql, 'BuyCoin', 0);
  newLogToSQL("updateSpreadSell","$sql",3,0,"SQL CALL","SBRuleID:$spreadBetRuleID");
}

function updateBuyTrend($coinID, $transactionID, $mode, $ID, $hr1, $hr24, $d7){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call UpdateBuyTrend($coinID, $transactionID, '$mode', $ID,$hr1, $hr24, $d7);";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateBuyTrend: ".$sql, 'BuyCoin', 0);
  newLogToSQL("updateBuyTrend","$sql",3,0,"SQL CALL","TransactionID:$transactionID CoinID:$coinID");
}

function updateBuyTrendHistory($coinID, $buyDate){
  $conn = getHistorySQL(rand(1,4));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "SELECT `Hr1Pct`,`Hr24Pct`,`D7Pct` FROM `PriceHistory` WHERE `CoinID` = $coinID and `PriceDate` > '$buyDate' and `Price` =
  (SELECT Min(`Price`) FROM `PriceHistory` WHERE `CoinID` = $coinID and `PriceDate` > '$buyDate' and `Price` <> 0.0)";

  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Hr1Pct'],$row['Hr24Pct'],$row['D7Pct']);
  }
  $conn->close();
  return $tempAry;
}

function updateBuyTrendHistorySB($spreadBetRuleID, $buyDate){
  $conn = getHistorySQL(rand(1,4));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "SELECT `Hr1Pct`,`Hr24Pct`,`D7Pct` FROM `SpreadBetPriceHistory` WHERE `SpreadBetRuleID` = $spreadBetRuleID and `PriceDate` > '$buyDate' and `Price` =
  (SELECT Min(`Price`) FROM `SpreadBetPriceHistory` WHERE `SpreadBetRuleID` = $spreadBetRuleID and `PriceDate` > '$buyDate' and `Price` <> 0.0)";

  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Hr1Pct'],$row['Hr24Pct'],$row['D7Pct']);
  }
  $conn->close();
  return $tempAry;
}

function updateSpreadBetPctAmount($spreadBetRuleID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `SpreadBetSettings` SET `PctProfitSell` = (`PctProfitSell` + 0.24) WHERE `SpreadBetRuleID` = $spreadBetRuleID and  `PctProfitSell` <= 15.0;";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateSpreadBetPctAmount: ".$sql, 'BuyCoin', 0);
  newLogToSQL("updateSpreadBetPctAmount",$sql,3,0,"SQL","SBRuleID:$spreadBetRuleID");
}

function updateSpreadBetTransactionAmount($nPrice, $spreadBetRuleID, $BTCAmount){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `SpreadBetTransactions` SET `TotalAmountToBuy`= $nPrice, `AmountPerCoin` = $BTCAmount WHERE `SpreadBetRuleID` = $spreadBetRuleID and `TotalAmountToBuy` = 0.00";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateSpreadBetTransactionAmount: ".$sql, 'BuyCoin', 0);
  newLogToSQL("updateSpreadBetTransactionAmount",$sql,3,0,"SQL","SBRuleID:$spreadBetRuleID");
}

function checkOpenSpreadBet($userID, $spreadBetRuleID = 0){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  $whereClause = "";
  if ($spreadBetRuleID <> 0){ $whereClause = " and `Tr`.`SpreadBetRuleID` = $spreadBetRuleID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Tr`.`SpreadBetRuleID` as SpreadBetRuleID, sum(`Tr`.`CoinPrice` * `Amount`)  as PurchasePriceUSD, `Sbt`.`TotalAmountToBuy`,`Sbt`.`AmountPerCoin`
    FROM `Transaction` `Tr`
    join `SpreadBetTransactions` `Sbt` on `Sbt`.`SpreadBetRuleID` = `Tr`.`SpreadBetRuleID`
    WHERE `Tr`.`Type` in ('SpreadBuy','SpreadSell') and `Tr`.`Status` in ('Open','Pending') and `Tr`.`UserID` = $userID $whereClause
    group by `Tr`.`SpreadBetRuleID` ";
  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['SpreadBetRuleID'],$row['PurchasePriceUSD'],$row['TotalAmountToBuy'],$row['AmountPerCoin']);
  }
  $conn->close();
  return $tempAry;
}

function getMaxPct($date,$coinID){
  $conn = getHistorySQL(rand(1,4));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "SELECT getMaxPrice('$date',$coinID) as MaxPrice;";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['MaxPrice']);
  }
  $conn->close();
  return $tempAry;
}

function updateMaxPctToSql($price, $coinID, $mode, $ruleID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "Call UpdateSellTrendToSQL('$mode', $ruleID, $coinID, $price);";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateMaxPctToSql: ".$sql, 'BuyCoin', 0);
  newLogToSQL("updateMaxPctToSql","$sql",3,0,"SQL CALL","CoinID:$coinID RuleID:$ruleID");
}

function trackingCoinReadyToBuy($livePrice, $mins, $type, $buyPrice, $TransactionID, $NoOfRisesInPrice, $pctProfit, $minsFromDate, $lastPrice, $totalRisesInPrice, $trackingID,$quickBuyCount,$market1HrChangePct){
  $swingPrice = (($livePrice/100)*0.25);
  $currentPrice = abs($livePrice-$lastPrice);
  //$bottomPrice = $livePrice-$swingPrice;
  Echo "<BR> Swing:$swingPrice Current:$currentPrice ";
  //if liveprice is stable, add 1 - -0.5 - 0.5
  if ($minsFromDate < 5){
      Echo "<BR>Less Than 5 Mins | OPT 1 : $minsFromDate";
      return False;
  }
  if (abs($market1HrChangePct) > 0.25){
    $totalRisesInPrice = $totalRisesInPrice * (abs($market1HrChangePct)/0.25);
  }
  if (($minsFromDate >= 60 && $livePrice <= $buyPrice) OR ($NoOfRisesInPrice > $totalRisesInPrice && $livePrice <= $buyPrice) OR ($quickBuyCount >= 3)){
    //if time is over 60 min and livePrice is > original price,  sell
    // if no of buys is greater than total needed - Buy
    Echo "<BR>Sell the Coin | OPT 2 : $minsFromDate| $mins | $livePrice | $NoOfRisesInPrice | $totalRisesInPrice";
    newLogToSQL("TrackingCoin", "OPT 2 : $minsFromDate| $mins | $livePrice | $NoOfRisesInPrice | $totalRisesInPrice", 3, 1,"trackingCoinReadyToBuy_2","TrackingCoinID:$trackingID");
    //reopenTransaction($TransactionID);
    return True;
  }
  if($currentPrice <= $swingPrice){
    Echo "<BR>Update No Of Rises | OPT 3 : $currentPrice | $swingPrice | $NoOfRisesInPrice | $TransactionID | $livePrice";
    newLogToSQL("TrackingCoin", "OPT 3 : $currentPrice | $swingPrice | $NoOfRisesInPrice | $TransactionID | $livePrice", 3, 1,"trackingCoinReadyToBuy_3","TrackingCoinID:$trackingID");
    if ($livePrice > $lastPrice){ updateQuickBuyCount($trackingID);}else {resetQuickBuyCount($trackingID);}
    updateNoOfRisesInPrice($trackingID, $NoOfRisesInPrice+1);
    setNewTrackingPrice($livePrice, $trackingID, 'Buy');
    return False;
  }
  //if liveprice is greater than or less than, reset to 0
  if ($currentPrice > $swingPrice){ //OR ($currentPrice < $swingPrice)
    //logToSQL("trackingCoinReadyToBuy", "OPT 4 : $currentPrice | $swingPrice - RESET TO 0 ", 3, 1);
    if ($livePrice > $lastPrice){ updateQuickBuyCount($trackingID);}else {resetQuickBuyCount($trackingID);}
    Echo "<BR>Outside the swing | OPT 4 : $currentPrice | $swingPrice - RESET TO 0 ";
    updateNoOfRisesInPrice($trackingID, 0);
    setNewTrackingPrice($livePrice, $trackingID, 'Buy');
    return False;
  }
  if (($type == 'Buy' && $pctProfit < -3) OR ($type == 'Buy' && $pctProfit > 3)){
    //Cancel Transaction
    Echo "<BR>Cancel Transaction | OPT 5 : $type | $pctProfit";
    newLogToSQL("TrackingCoin", "OPT 5 : $type | $pctProfit", 3, 1,"trackingCoinReadyToBuy_5","TrackingCoinID:$trackingID");
    //reopenTransaction($TransactionID);
    closeNewTrackingCoin($trackingID, True);
    return False;
  }
  echo "<BR> Exit trackingCoinReadyToBuy";
  setLastPrice($livePrice,$trackingID, 'Buy');
  return False;
}

function resetQuickBuyCount($trackingID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `TrackingCoins` SET `quickBuyCount`= 0 WHERE `ID` = $trackingID";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("resetQuickBuyCount: ".$sql, 'BuyCoin', 0);
  newLogToSQL("resetQuickBuyCount",$sql,3,0,"SQL","TrackingID:$trackingID");
}

function updateQuickBuyCount($trackingID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `TrackingCoins` SET `quickBuyCount`= (`quickBuyCount` +1) WHERE `ID` = $trackingID";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateQuickBuyCount: ".$sql, 'BuyCoin', 0);
  newLogToSQL("updateQuickBuyCount",$sql,3,0,"SQL","TrackingID:$trackingID");
}

function trackingCoinReadyToSell($livePrice, $mins, $type, $sellPrice, $TransactionID, $NoOfRisesInPrice, $pctProfit, $minsFromDate, $lastPrice, $totalRisesInPrice, $trackingSellID,$market1HrChangePct){
    $swingPrice = (($livePrice/100)*0.25);
    $currentPrice = abs($livePrice-$lastPrice);
    //$bottomPrice = $livePrice-$swingPrice;

    //if liveprice is stable, add 1 - -0.5 - 0.5
    if ($minsFromDate < 5){
        //: OPT 1
        return False;
    }

    if ($type == 'SpreadSell' && $minsFromDate > 14400){
      newLogToSQL("TrackingSell", "OPT 6 : $type | $minsFromDate", 3, 1,"trackingCoinReadyToSell_6","TransactionID:$TransactionID");
      updateSQLcancelSpreadBetTrackingSell($TransactionID);
      reopenTransaction($TransactionID);
      closeNewTrackingSellCoin($TransactionID);
      return False;
    }

    if (abs($market1HrChangePct) > 0.25){
      $totalRisesInPrice = $totalRisesInPrice * (abs($market1HrChangePct)/0.25);
    }
    echo "<BR>trackingCoinReadyToSell: $mins | $livePrice | $sellPrice | $NoOfRisesInPrice | $totalRisesInPrice";
    if (($minsFromDate >= 60 && $livePrice >= $sellPrice) OR ($NoOfRisesInPrice > $totalRisesInPrice && $livePrice >= $sellPrice)){
      //if time is over 60 min and livePrice is > original price,  sell : OPT 2
      // if no of buys is greater than total needed - Buy
      newLogToSQL("TrackingSell", "OPT 2 : $mins | $livePrice | $sellPrice | $NoOfRisesInPrice | $totalRisesInPrice", 3, 1,"trackingCoinReadyToSell_2","TransactionID:$TransactionID");
      reopenTransaction($TransactionID);
      return True;
    }
    if($currentPrice <= $swingPrice){
      //: OPT 3
      newLogToSQL("TrackingSell", "OPT 3 : $currentPrice | $swingPrice | $NoOfRisesInPrice | $TransactionID | $livePrice", 3, 1,"trackingCoinReadyToSell_3","TransactionID:$TransactionID");
      updateNoOfRisesInSellPrice($trackingSellID, $NoOfRisesInPrice+1, $livePrice);
      setNewTrackingPrice($livePrice, $trackingSellID, 'Sell');
      return False;
    }
    //if liveprice is greater than or less than, reset to 0
    if ($currentPrice > $swingPrice){  //OR ($currentPrice < $swingPrice)
      // : OPT 4
      //logToSQL("trackingCoinReadyToSell", "OPT 4 Current: $currentPrice | Swing: $swingPrice | Live: $livePrice | Sell: $sellPrice - RESET TO 0 ", 3, 1);
      updateNoOfRisesInSellPrice($trackingSellID, 0, $livePrice);
      setNewTrackingPrice($livePrice, $trackingSellID, 'Sell');
      return False;
    }
    if (($type == 'Sell' && $pctProfit < -3) OR ($type == 'Sell' && $pctProfit > 3)){
      //Cancel Transaction : OPT 5
      newLogToSQL("TrackingSell", "OPT 5 : $type | $pctProfit", 3, 1,"trackingCoinReadyToSell_5","TransactionID:$TransactionID");
      reopenTransaction($TransactionID);
      closeNewTrackingSellCoin($TransactionID);
      return False;
    }


    setLastPrice($livePrice,$trackingSellID, 'Sell');
}

function updateSQLcancelSpreadBetTrackingSell($TransactionID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call cancelSpreadBetTrackingSell($TransactionID);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateSQLcancelSpreadBetTrackingSell: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("updateSQLcancelSpreadBetTrackingSell","$sql",3,0,"SQL CALL","TransactionID:$TransactionID");
}

function getSpreadBetCount($SBTransID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT count(`SBTransID`) as countOfOpenRules FROM `TrackingCoins` WHERE `SBTransID` = $SBTransID";
  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['countOfOpenRules']);
  }
  $conn->close();
  return $tempAry;
}

function getSpreadBerUserSettings(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `NoOfBuysPerCoin`,`TotalNoOfBuys`,`DivideAllocation`,`UserID` FROM `SpreadBetUserSettings`";
  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['NoOfBuysPerCoin'],$row['TotalNoOfBuys'],$row['DivideAllocation'],$row['UserID']);
  }
  $conn->close();
  return $tempAry;
}

function getBuyBackData(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `ID`, `TransactionID`, `Quantity`, `SellPrice`, `Status`, `SpreadBetTransactionID`, `SpreadBetRuleID`, `CoinID`, `SellPriceBA`, `LiveCoinPrice`, `PriceDifferece`
  , `PriceDifferecePct`, `UserID`, `Email`, `UserName`, `ApiKey`, `ApiSecret`, `KEK`
  , `OriginalSaleProfit`, `OriginalSaleProfitPct`, `ProfitMultiply`, `NoOfRaisesInPrice`, `BuyBackPct`,`MinsToCancel`,`BullBearStatus`,`Type`,`OverrideCoinAllocation`
   FROM `BuyBackView`";
  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['TransactionID'],$row['Quantity'],$row['SellPrice'],$row['Status'],$row['SpreadBetTransactionID'],$row['SpreadBetRuleID'],$row['CoinID']
      ,$row['SellPriceBA'],$row['LiveCoinPrice'],$row['PriceDifferece'],$row['PriceDifferecePct'],$row['UserID'],$row['Email'],$row['UserName'],$row['ApiKey'],$row['ApiSecret'],$row['KEK']
      ,$row['OriginalSaleProfit'],$row['OriginalSaleProfitPct'],$row['ProfitMultiply'],$row['NoOfRaisesInPrice'],$row['BuyBackPct'],$row['MinsToCancel'],$row['BullBearStatus'],$row['Type']
      ,$row['OverrideCoinAllocation']);
  }
  $conn->close();
  return $tempAry;
}

function reOpenTransactionfromBuyBack($buyBackID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "Select `Tr`.`CoinID`, `Cp`.`LiveCoinPrice`, `Tr`.`UserID`, `Cn`.`BaseCurrency`,1 as SendEmail,1 as BuyCoin,
            (SELECT `SellPrice` from `BittrexAction` where `TransactionID` = (SELECT `TransactionID` FROM `BuyBack` WHERE `ID` = $buyBackID)and `Type` in ('Sell','SpreadSell')) * `Tr`.`Amount` as SalePrice, `Tr`.`BuyRule`, 0.0 as CoinOffset,0 as CoinOffsetEnabled,1 as BuyType
            ,90 as MinsToCancel, `Tr`.`FixSellRule`,0 as toMerge,0 as noOfPurchases,5 as RisesInPrice, `Tr`.`Type`,`Tr`.`CoinPrice` as OriginalPrice,`Tr`.`SpreadBetTransactionID`, `Tr`.`SpreadBetRuleID`
            from `Transaction` `Tr`
            join `CoinPrice` `Cp` on `Cp`.`CoinID` = `Tr`.`CoinID`
            join `Coin` `Cn` on `Cn`.`ID` = `Tr`.`CoinID`
            where `Tr`.`ID` = (SELECT `TransactionID` FROM `BuyBack` WHERE `ID` = $buyBackID)";

            echo "<BR> $sql";
            $result = $conn->query($sql);
            //$result = mysqli_query($link4, $query);
            //mysqli_fetch_assoc($result);
            while ($row = mysqli_fetch_assoc($result)){
                $tempAry[] = Array($row['CoinID'],$row['LiveCoinPrice'],$row['UserID'],$row['BaseCurrency'],$row['SendEmail'],$row['BuyCoin'],$row['SalePrice'],$row['BuyRule']
              ,$row['CoinOffset'],$row['CoinOffsetEnabled'],$row['BuyType'],$row['MinsToCancel'],$row['FixSellRule'],$row['toMerge'],$row['noOfPurchases'],$row['RisesInPrice'],$row['Type'],$row['OriginalPrice']
            ,$row['SpreadBetTransactionID'],$row['SpreadBetRuleID']);
            }
            $conn->close();
            return $tempAry;
}

function addToBuyBackMultiplier($buyBackID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "Call addToBuyBackMultiply($buyBackID);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("addToBuyBackMultiplier: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("addToBuyBackMultiplier","$sql",3,0,"SQL CALL","BuyBackID:$buyBackID");
}

function closeBuyBack($buyBackID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `BuyBack` SET `Status`= 'Closed' WHERE `ID` = $buyBackID ";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("closeBuyBack: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("closeBuyBack",$sql,3,0,"SQL","BuyBackID:$buyBackID");
}

function sellSpreadBetCoins($spreadSellCoins){
  $spreadSellCoinsSize = count($spreadSellCoins);
  echo "<BR> Sell Spread Coins | $spreadSellCoinsSize";
  for ($q=0; $q<$spreadSellCoinsSize; $q++){

    $coin = $spreadSellCoins[$q][11];  $BaseCurrency =  $spreadSellCoins[$q][36]; $TransactionID = $spreadSellCoins[$q][0];
    $CoinID = $spreadSellCoins[$q][2]; $OrderNo = $spreadSellCoins[$q][10]; $LiveCoinPrice = $spreadSellCoins[$q][19];
    $date = date("Y-m-d H:i:s", time()); $SendEmail = 1; $SellCoin = 1; $CoinSellOffsetEnabled = 0; $CoinSellOffsetPct = 0.0;
    $Amount = $spreadSellCoins[$q][5]; $CoinPrice = $spreadSellCoins[$q][4]; $FixSellRule = $spreadSellCoins[$q][42];
    $orderDate = $spreadSellCoins[$q][7]; $pctToSave = $spreadSellCoins[$q][55]; $userID = $spreadSellCoins[$q][3];
    $type = $spreadSellCoins[$q][1]; $fallsInPrice = $spreadSellCoins[$q][56]; $spreadBetRuleID = $spreadSellCoins[$q][57];
    LogToSQL("SpreadBetSell","profitPct :$profitPct | spreadBetPctProfitSell: $spreadBetPctProfitSell | ID: $spreadBetRuleID NoOfCoins:$spreadSellCoinsSize;",3,1);
    //echo "<BR> sellCoins($APIKey, $APISecret,$coin, $Email, $userID, 0,$date, $BaseCurrency,$SendEmail,$SellCoin, $FixSellRule,$UserName,$OrderNo,$Amount,$CoinPrice,$TransactionID,$CoinID,$CoinSellOffsetEnabled,$CoinSellOffsetPct,$LiveCoinPrice, $type);";
    LogToSQL("SpreadBetSell","sellCoins($TransactionID,$CoinID);",3,1);
    //$checkSell = sellCoins($APIKey, $APISecret,$coin, $Email, $userID, 0,$date, $BaseCurrency,$SendEmail,$SellCoin, $FixSellRule,$UserName,$OrderNo,$Amount,$CoinPrice,$TransactionID,$CoinID,$CoinSellOffsetEnabled,$CoinSellOffsetPct,$LiveCoinPrice,$type);
    LogToSQL("SpreadBetTrackingSell","newTrackingSellCoins($LiveCoinPrice,$userID, $TransactionID,$SellCoin, $SendEmail,0,0.0,2);",3,1);
    newTrackingSellCoins($LiveCoinPrice,$userID, $TransactionID,$SellCoin, $SendEmail,0,0.0,$fallsInPrice);
    setTransactionPending($TransactionID);
    LogToSQL("SpreadBetTest1","newTrackingSellCoins($LiveCoinPrice,$userID, $TransactionID,1, 1,0,0.0,2);",3,1);
    //newTrackingSellCoins($LiveCoinPrice,$userID, $TransactionID,1, 1,0,0.0,2);
    LogToSQL("SpreadBetTest2","setTransactionPending($TransactionID);",3,1);
    //setTransactionPending($TransactionID);
    updateSpreadSell($spreadBetRuleID,$orderDate);
    $buyTrendPct = updateBuyTrendHistorySB($spreadBetRuleID,$orderDate);
    $Hr1Trnd = $buyTrendPct[0][0]; $Hr24Trnd = $buyTrendPct[0][1]; $d7Trnd = $buyTrendPct[0][2];
    updateBuyTrend(0, 0, 'SpreadBet', $spreadBetRuleID, $Hr1Trnd,$Hr24Trnd,$d7Trnd);
    if ($q == $spreadSellCoinsSize -1 AND $spreadSellCoinsSize > 0){
        updateSpreadBetPctAmount($spreadBetRuleID);
        LogToSQL("SpreadBetSell","updateSpreadBetPctAmount($spreadBetRuleID);",3,1);
        UpdateProfit();
        LogToSQL("SpreadBetSell","UpdateProfit();",3,1);
    }
    $profitPct = ($LiveCoinPrice-$CoinPrice)/$CoinPrice*100;
    $sellPrice = ($LiveCoinPrice*$Amount);
    $buyPrice = $CoinPrice*$Amount;
    $fee = (($sellPrice)/100)*0.25;
    $profit = number_format((float)($sellPrice-$buyPrice)-$fee, 8, '.', '');
    $pctToSave = $pctToSave / 100;
    addProfitToAllocation($userID, $profit, 'SpreadBet', $pctToSave,$CoinID);
    LogToSQL("SpreadBetSell","addProfitToAllocation($userID, $profit, 'SpreadBet', $pctToSave,$CoinID);",3,1);
  }
}

function WriteBuyBack($transactionID, $profitPct, $noOfRisesInPrice, $minsToCancel){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "Call WriteBuyBack($transactionID,$noOfRisesInPrice,$profitPct,$minsToCancel);";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("WriteBuyBack: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("WriteBuyBack","$sql",3,0,"SQL CALL","TransactionID:$transactionID");
}

function writeProfitToWebTable($spreadBetTransactionID,$originalPurchasePrice, $liveTotalPrice, $saleTotalPrice){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "call AddProfitForWebTable($spreadBetTransactionID,$originalPurchasePrice, $liveTotalPrice, $saleTotalPrice);";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("writeProfitToWebTable: ".$sql, 'SpreadBetSell', 0);
  newLogToSQL("writeProfitToWebTable","$sql",3,0,"SQL CALL","TransactionID:$transactionID");
}

function WriteWebMarketStats($marketPctChangeHr1,$marketPctChangeHr24,$marketPctChangeD7){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `WebMarketStats` SET `1HrPrice`=$marketPctChangeHr1,`24HrPrice`=$marketPctChangeHr24,`7DPrice`=$marketPctChangeD7 WHERE `ID` = 1";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("WriteWebMarketStats: ".$sql, 'SpreadBetSell', 0);
  newLogToSQL("WriteWebMarketStats","$sql",3,0,"SQL CALL","");
}

function getTotalProfitSpreadBetSell($spreadBetTransactionID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT ifNull(sum(`OriginalPurchasePrice`),0) as OriginalPurchasePrice ,ifNull(sum(`LiveTotalPrice`),0) as LiveTotalPrice,ifNull(sum(`SaleTotalPrice`),0) as SaleTotalPrice
    ,getBTCPrice() as getBTCPrice, getETHPrice() as getETHPrice
            FROM `SpreadBetTotalProfitView`
            where `SpreadBetTransactionID` = $spreadBetTransactionID ";

  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['OriginalPurchasePrice'],$row['LiveTotalPrice'],$row['SaleTotalPrice'],$row['getBTCPrice'],$row['getETHPrice']);
      //13  14  15

  }
  $conn->close();
  return $tempAry;
}

function getWebMarketStats(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `1HrPrice`, `24HrPrice`, `7DPrice` FROM `WebMarketStats`";

  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['1HrPrice'],$row['24HrPrice'],$row['7DPrice']);
      //13  14  15

  }
  $conn->close();
  return $tempAry;
}

function getSavingTotal($userID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `TotalUSDT`,`LivePrice`,`Profit` FROM `WebSavings` WHERE `UserID` = $userID";

  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['TotalUSDT'],$row['LivePrice'],$row['Profit']);
      //13  14  15

  }
  $conn->close();
  return $tempAry;
}

function getSoldProfitSpreadBetSell($spreadBetTransactionID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT sum(`Tr`.`CoinPrice`*`Tr`.`Amount`) as OriginalPurchasePrice, sum(`Ba`.`SellPrice`*`Tr`.`Amount`) as SellPrice
      From `BittrexAction` `Ba`
      join `Transaction` `Tr` on `Tr`.`ID` = `Ba`.`TransactionID`
      join `CoinPrice` `Cp` on `Cp`.`CoinID` = `Tr`.`CoinID`
      WHERE `Tr`.`SpreadBetTransactionID` = $spreadBetTransactionID and `Tr`.`Status` = 'Sold'
      and `Ba`.`Type` in ('Sell','SpreadSell')";

  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['OriginalPurchasePrice'],$row['SellPrice']);
  }
  $conn->close();
  return $tempAry;
}

function updateSpreadBetTotalProfitBuy($transactionID, $coinPrice, $amount){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "INSERT INTO `SpreadBetTotalProfit`( `TransactionID`, `SpreadBetTransactionID`, `CoinPrice`, `Amount`)
  VALUES ($transactionID,(SELECT `SpreadBetTransactionID` FROM `Transaction` WHERE `ID` = $transactionID),$coinPrice,$amount);";
  logToSQL("BittrexSQL", "$sql", 3, 1);
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateSpreadBetTotalProfitBuy: ".$sql, 'TrackingCoins', 0);
}

function updateSpreadBetTotalProfitSell($transactionID,$salePrice){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `SpreadBetTotalProfit` SET `SalePrice` = $salePrice, `Status`='Sold' WHERE `TransactionID` = $transactionID";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateSpreadBetTotalProfitSell: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("updateSpreadBetTotalProfitSell",$sql,3,0,"SQL","TransactionID:$transactionID");
}

function updateSpreadBetSellTarget($transactionID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "INSERT INTO `SpreadBetSellTarget`( `TransactionID`, `SBTransactionID`, `SellPct`)
  VALUES ($transactionID,(SELECT `SpreadBetTransactionID` FROM `Transaction` WHERE `ID` = $transactionID )
  ,(SELECT `PctProfitSell`/4 FROM `SpreadBetSettings` WHERE `SpreadBetRuleID` = (SELECT `SpreadBetRuleID` FROM `Transaction` WHERE `ID` = $transactionID )) )";
  logToSQL("BittrexSQL", "$sql", 3, 1);
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateSpreadBetSellTarget: ".$sql, 'TrackingCoins', 0);
}

function deleteSpreadBetTotalProfit($spreadBetTransactionID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "DELETE FROM `SpreadBetTotalProfit`  WHERE `SpreadBetTransactionID` = $spreadBetTransactionID";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("deleteSpreadBetTotalProfit: ".$sql, 'TrackingCoins', 0);
}

function deleteSpreadBetTrackingCoins($spreadBetTransactionID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "DELETE FROM `TrackingCoins` WHERE `SBTransID` = $spreadBetTransactionID";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("deleteSpreadBetTrackingCoins: ".$sql, 'TrackingCoins', 0);
}

function CloseAllBuyBack($spreadBetTransactionID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `BuyBack`
            SET `Status` = 'Closed'
            WHERE `TransactionID` in (SELECT `ID` FROM `Transaction` WHERE `SpreadBetTransactionID` = $spreadBetTransactionID)";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("CloseAllBuyBack: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("CloseAllBuyBack",$sql,3,0,"SQL","SBTransactionID:$spreadBetTransactionID");
}

function subPctFromProfitSB($sBTransID,$pctToSub, $transactionID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "Call UpdateOrAddSBTransSellTargetPct($transactionID, $pctToSub,$sBTransID);";
  print_r("<BR>".$sql."<BR>");
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("subPctFromProfitSB","$sql",3,0,"SQL CALL","TransactionID:$transactionID SBTransID:$sBTransID");
}
?>
