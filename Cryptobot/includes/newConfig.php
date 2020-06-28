<?php
include_once ('/home/stevenj1979/SQLData.php');
$logToFileSetting = getLogToFile();
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
  `RuleIDSell`,`LiveCoinPrice`,`TimetoCancelBuy`,`BuyOrderCancelTime`,`KEK` FROM `BittrexOutstandingRequests` WHERE `Status` = '1' $bittrexQueue";
  $conn->query("SET time_zone = '+04:00';");
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['Type'],$row['BittrexRef'],$row['ActionDate'],$row['CompletionDate'],$row['Status'],$row['SellPrice'],$row['UserName'],$row['APIKey'],$row['APISecret'],$row['Symbol'],$row['Amount'],
    $row['CoinPrice'],$row['UserID'],$row['Email'],$row['OrderNo'],$row['TransactionID'],$row['BaseCurrency'],$row['RuleID'],$row['DaysOutstanding'],$row['timeSinceAction'],$row['CoinID'],$row['RuleIDSell'],$row['LiveCoinPrice'],
    $row['TimetoCancelBuy'],$row['BuyOrderCancelTime'],$row['KEK']);
  }
  $conn->close();
  return $tempAry;
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
    logAction("deleteFromBittrexAction: ".$sql, 'BuySell',$logToFileSetting);
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
    logAction("updateSQLSold: ".$sql, 'BuySell',$logToFileSetting);
}

function bittrexOrder($apikey, $apisecret, $uuid){
    $nonce=time();
    $uri='https://bittrex.com/api/v1.1/account/getorder?apikey='.$apikey.'&uuid='.$uuid.'&nonce='.$nonce;
    echo "<br>$uri<br>";
    $sign=hash_hmac('sha512',$uri,$apisecret);
    $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
    //$balance = $obj["result"]["IsOpen"];
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
    ,`1HrPriceChangeLast`,`1HrPriceChange3`,`1HrPriceChange4`,`SecondstoUpdate`,`LastUpdated`,`Name`,`Image`
    FROM `CoinStatsView` ORDER BY `Symbol` ASC";
    //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['ID'],$row['Symbol'],$row['LiveBuyOrders'],$row['LastBuyOrders'],$row['BuyOrdersPctChange'],$row['LiveMarketCap'],$row['LastMarketCap'],$row['MarketCapPctChange'],$row['Live1HrChange'],$row['Last1HrChange'],$row['Hr1ChangePctChange'] //10
    ,$row['Live24HrChange'],$row['Last24HrChange'],$row['Hr24ChangePctChange'],$row['Live7DChange'],$row['Last7DChange'],$row['D7ChangePctChange'],$row['LiveCoinPrice'],$row['LastCoinPrice'],$row['CoinPricePctChange'],$row['LiveSellOrders'],$row['LastSellOrders']//21
    ,$row['SellOrdersPctChange'],$row['LiveVolume'],$row['LastVolume'],$row['VolumePctChange'],$row['BaseCurrency'],$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['1HrPriceChangeLive'],$row['1HrPriceChangeLast'],$row['1HrPriceChange3'] //33
    ,$row['1HrPriceChange4'],$row['SecondstoUpdate'],$row['LastUpdated'],$row['Name'],$row['Image']);
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
  , `Price4Trend`,`Price3Trend`,`LastPriceTrend`,`LivePriceTrend`,`FixSellRule`,`SellRule`,`BuyRule`,`ToMerge`,`LowPricePurchaseEnabled`,`PurchaseLimit`,`PctToPurchase`,`BTCBuyAmount`,`NoOfPurchases`,`Name`,`Image`
  FROM `SellCoinStatsView` $whereclause";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['ID'],$row['Type'],$row['CoinID'],$row['UserID'],$row['CoinPrice'],$row['Amount'],$row['Status'],$row['OrderDate'],$row['CompletionDate'],$row['BittrexID'],$row['OrderNo'],
    $row['Symbol'],$row['LastBuyOrders'],$row['LiveBuyOrders'],$row['BuyOrdersPctChange'],$row['LastMarketCap'],$row['LiveMarketCap'],$row['MarketCapPctChange'],$row['LastCoinPrice'],$row['LiveCoinPrice'], //19
    $row['CoinPricePctChange'],$row['LastSellOrders'],$row['LiveSellOrders'],$row['SellOrdersPctChange'],$row['LastVolume'],$row['LiveVolume'],$row['VolumePctChange'],$row['Last1HrChange'],$row['Live1HrChange'],$row['Hr1PctChange'],$row['Last24HrChange'],$row['Live24HrChange'] //31
    ,$row['Hr24PctChange'],$row['Last7DChange'],$row['Live7DChange'],$row['D7PctChange'],$row['BaseCurrency'],$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['FixSellRule'],$row['SellRule'],$row['BuyRule'] //43
    ,$row['ToMerge'],$row['LowPricePurchaseEnabled'],$row['PurchaseLimit'],$row['PctToPurchase'],$row['BTCBuyAmount'],$row['NoOfPurchases'],$row['Name'],$row['Image']);
  }
  $conn->close();
  return $tempAry;
}
function bittrexCoinStats($apikey, $apisecret, $symbol, $baseCurrency){
    $nonce=time();
    //$uri='https://bittrex.com/api/v1.1/account/getbalance?apikey='.$apikey.'&currency=BTC&nonce='.$nonce;
    $uri='https://bittrex.com/api/v1.1/public/getmarketsummary?market='.$baseCurrency.'-'.$symbol;
    print_r($uri);
    $sign=hash_hmac('sha512',$uri,$apisecret);
    $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, True);
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
  ,`BuyAmountOverrideEnabled`, `BuyAmountOverride`,`NewBuyPattern`,`KEK`,`SellRuleFixed`,`OverrideDailyLimit`,`CoinPricePatternEnabled`,`CoinPricePattern`,`1HrChangeTrendEnabled`,`1HrChangeTrend`
  FROM `UserBuyRules`";
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
    ,$row['BuyAmountOverride'],$row['NewBuyPattern'],$row['KEK'],$row['SellRuleFixed'],$row['OverrideDailyLimit'],$row['CoinPricePatternEnabled'],$row['CoinPricePattern'],$row['1HrChangeTrendEnabled'],$row['1HrChangeTrend']);
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
   FROM `UserSellRules`";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['ID'],$row['UserID'],$row['SellCoin'],$row['SendEmail'],$row['BuyOrdersEnabled'],$row['BuyOrdersTop'],$row['BuyOrdersBtm'],$row['MarketCapEnabled'],$row['MarketCapTop'],$row['MarketCapBtm'],$row['1HrChangeEnabled'],
    $row['1HrChangeTop'],$row['1HrChangeBtm'],$row['24HrChangeEnabled'],$row['24HrChangeTop'],$row['24HrChangeBtm'],$row['7DChangeEnabled'],$row['7DChangeTop'],$row['7DChangeBtm'],$row['ProfitPctEnabled'],$row['ProfitPctTop'],$row['ProfitPctBtm'],
    $row['CoinPriceEnabled'],$row['CoinPriceTop'],$row['CoinPriceBtm'],$row['SellOrdersEnabled'],$row['SellOrdersTop'],$row['SellOrdersBtm'],$row['VolumeEnabled'],$row['VolumeTop'],$row['VolumeBtm'],$row['Email'],$row['UserName'],$row['APIKey'],
    $row['APISecret'],$row['SellCoinOffsetEnabled'],$row['SellCoinOffsetPct'],$row['SellPriceMinEnabled'],$row['SellPriceMin'],$row['LimitToCoin'],$row['KEK'],$row['SellPatternEnabled'],$row['SellPattern'],$row['LimitToBuyRule'],
    $row['CoinPricePatternEnabled'],$row['CoinPricePattern'],$row['AutoSellCoinEnabled']);
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
  echo "<BR> 0: returnBuyAmount($coin, $baseCurrency, $btcBuyAmount, $buyType, $BTCBalance, $bitPrice,$apikey,$apisecret){";
  if ($btcBuyAmount == 0){
    echo "<BR> 1: $BTCBalance - (($BTCBalance/ 100 ) * 0.28) : ";
    $returnPrice = $BTCBalance - (($BTCBalance/ 100 ) * 0.28);
    echo " $returnPrice ";
  }elseif ($btcBuyAmount > 0 && $buyType == 0){
      echo "<BR> 2: returnPrice = ($BTCBalance/100)*$btcBuyAmount; ";
      //$returnPrice = ($btcBuyAmount) - (($BTCBalance/ 100 ) * 0.28);
      $returnPrice = ($BTCBalance/100)*$btcBuyAmount;
      echo " : $returnPrice ";
    }elseif ($btcBuyAmount > 0 && $buyType == 1){
      echo "<BR> 3: ($btcBuyAmount) ";
      //$returnPrice = ($BTCBalance*($btcBuyAmount/100))- (($BTCBalance/ 100 ) * 0.28);
      $returnPrice = $btcBuyAmount;
      echo " $returnPrice ";
    }

   if ($returnPrice > $BTCBalance) {
     $returnPrice = $BTCBalance - (($BTCBalance/ 100 ) * 0.28);
     echo "<BR> 4: $returnPrice = $returnPrice > $BTCBalance ";
   }
   //echo "<BR> Balance : $BTCBalance ";
   //if ($BTCBalance < 20.00){$returnPrice == 0;}

   return $returnPrice/$bitPrice;
}

function buyCoins($apikey, $apisecret, $coin, $email, $userID, $date,$baseCurrency, $sendEmail, $buyCoin, $btcBuyAmount, $ruleID,$userName, $coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed, $buyPriceCoin,$noOfPurchases = 0){
  $BTCBalance = bittrexbalance($apikey, $apisecret,$baseCurrency);

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
    $bitPrice = number_format((float)(bittrexCoinPrice($apikey, $apisecret,$baseCurrency,$coin)), 8, '.', '');
  }else{
    $bitPrice = $buyPriceCoin;
  }
  echo "<br> returnBuyAmount($coin, $baseCurrency, $btcBuyAmount, $buyType, $BTCBalance, $bitPrice, $apikey, $apisecret);";
  $btcBuyAmount = returnBuyAmount($coin, $baseCurrency, $btcBuyAmount, $buyType, $BTCBalance, $bitPrice, $apikey, $apisecret);
  echo "<BR> btcBuyAmount $btcBuyAmount ";
  $subject = "Coin Alert: ".$coin;
  $from = 'Coin Alert <alert@investment-tracker.net>';
  echo "<BR>Balance: $BTCBalance";
  $minTradeAmount = getMinTradeAmount($coin,$baseCurrency,$apisecret);
  if ($buyCoin) {
    $subject = "Coin Purchase: ".$coin;
    $from = 'Coin Purchase <purchase@investment-tracker.net>';
  }
  //$btcwithCharge = $btcBuyAmount - (($btcBuyAmount/100)*0.28);
  //echo "<BR> btcwithCharge $btcwithCharge = $btcBuyAmount - (($btcBuyAmount/100)*0.28);";
  //if ($btcBuyAmount > $minTradeAmount) {
    echo "buy Coin - Balance Sufficient";
    $bitPrice = number_format((float)(bittrexCoinPrice($apikey, $apisecret,$baseCurrency,$coin)), 8, '.', '');
    if ($CoinSellOffsetEnabled == 1){

      $bitPrice = number_format((float)newPrice($bitPrice,$CoinSellOffsetPct, "Buy"), 8, '.', '');
    }
    //$livePrice = getLiveCoinPrice($tracking[$x][0]);
    //$avgCoinPrice = getAveragePrice($coin);
    //echo "<BR>AvgCoinPrice: ".$avgCoinPrice[0][0]." CoinPrice: ".$bitPrice;
    //if ($avgCoinPrice > $bitPrice){ return; }
    //$quantity = Round($btcBuyAmount/$bitPrice,8,PHP_ROUND_HALF_UP);
    if ($btcBuyAmount>$minTradeAmount && $BTCBalance >= $buyMin){
        echo "Quantity above min trade amount";
        //buyCoins($apikey, $apisecret,$coin, $quantity, $bitPrice, $email,$minTradeAmount, $userID, $totalScore,$date, $baseCurrency);
        $orderNo = "ORD".$coin.date("YmdHis", time()).$ruleID;
        echo "Buy Coin = $buyCoin";
        if ($buyCoin){
          $obj = bittrexbuy($apikey, $apisecret, $coin, $btcBuyAmount, $bitPrice, $baseCurrency);
          //writeSQLBuy($coin, $quantity, $bitPrice, $date, $orderNo, $userID, $baseCurrency);
          $bittrexRef = $obj["result"]["uuid"];
          $status = $obj["success"];
          if ($status == 1){
            echo "bittrexBuyAdd($coinID, $userID, 'Buy', $bittrexRef, $status, $ruleID, $bitPrice, $btcBuyAmount, $orderNo);";
            date_default_timezone_set('Asia/Dubai');
            //$tmpTime = $timeToCancelBuyMins;
            //$newDate = date("Y-m-d H:i:s", time());
            //$current_date = date('Y-m-d H:i:s');
            //$newTime = date("Y-m-d H:i:s",strtotime('+'.$timeToCancelBuyMins.'Mins', strtotime($current_date)));
            //$buyCancelTime = strtotime( '+ 16 minute');
            bittrexBuyAdd($coinID, $userID, 'Buy', $bittrexRef, $status, $ruleID, $bitPrice, $btcBuyAmount, $orderNo,$timeToCancelBuyMins);
            bittrexAddNoOfPurchases($bittrexRef,$noOfPurchases);
            addBuyRuletoSQL($bittrexRef,$ruleID);
            logToSQL("Bittrex", "Add Buy Coin $bitPrice $btcBuyAmount $orderNo", $userID);
            //writeBittrexActionBuy($coinID,$userID,'Buy',$bittrexRef,$date,$status,$bitPrice,$ruleID);
            if ($SellRuleFixed !== "ALL"){writeFixedSellRule($SellRuleFixed,$bittrexRef);}

          }
          logAction("Bittrex Status:  ".json_encode($obj), 'BuySell',$logToFileSetting);
          logToSQL("Bittrex", "Add Buy Coin Error: ".json_encode($obj), $userID);
        }
        if ($sendEmail==1 && $buyCoin ==0){
        //if ($sendEmail){
          sendEmail($email, $coin, $btcBuyAmount, $bitPrice, $orderNo, $score, $subject,$userName, $from);
        }
    }else{
      echo "<BR> BITTREX BALANCE INSUFFICIENT $coin: $btcBuyAmount>$minTradeAmount";
      logAction("BITTREX BALANCE INSUFFICIENT $coin: $btcBuyAmount>$minTradeAmount && $BTCBalance >= $buyMin", 'BuySell',$logToFileSetting);
      logToSQL("Bittrex", "BITTREX BALANCE INSUFFICIENT $coin: $btcBuyAmount>$minTradeAmount && $BTCBalance >= $buyMin", $userID);
    }
  //}
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
    logAction("writeSQLTransBuy: ".$sql, 'BuySell',$logToFileSetting);
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
    logAction("writeBittrexActionBuy: ".$sql, 'BuySell',$logToFileSetting);
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

function bittrexbalance($apikey, $apisecret, $base ){
    $nonce=time();
    $uri='https://bittrex.com/api/v1.1/account/getbalance?apikey='.$apikey.'&currency='.$base.'&nonce='.$nonce;
    $sign=hash_hmac('sha512',$uri,$apisecret);
    $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
    $balance = $obj["result"]["Available"];
    return $balance;
}

function bittrexbuy($apikey, $apisecret, $symbol, $quant, $rate,$baseCurrency){
    Echo "bittrexbuy($apikey, $apisecret, $symbol, $quant, $rate,$baseCurrency)";
    $nonce=time();
    $uri='https://bittrex.com/api/v1.1/market/buylimit?apikey='.$apikey.'&market='.$baseCurrency.'-'.$symbol.'&quantity='.$quant.'&rate='.$rate.'&nonce='.$nonce;
    echo $uri."<BR>";
    $sign=hash_hmac('sha512',$uri,$apisecret);
    $ch = curl_init($uri);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
    return $obj;
}

function getMinTradeAmount($coin, $baseCurrency, $apisecret){
  $minTradeSize = getMinTrade($apisecret);
  $tradeArraySize = count($minTradeSize['result']);
  //print_r($tradeArraySize);
  for($y = 0; $y < $tradeArraySize; $y++) {
    if($minTradeSize['result'][$y]['MarketCurrency']==$coin && $minTradeSize['result'][$y]['BaseCurrency']==$baseCurrency){
      $minTradeAmount= $minTradeSize['result'][$y]['MinTradeSize'];
      return $minTradeAmount;
      exit;
    }
  }
}

function getMinTrade($apisecret){
  $nonce=time();
  //$uri='https://bittrex.com/api/v1.1/account/getbalance?apikey='.$apikey.'&currency=BTC&nonce='.$nonce;
  $uri="https://bittrex.com/api/v1.1/public/getmarkets";
  $sign=hash_hmac('sha512',$uri,$apisecret);
  $ch = curl_init($uri);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $execResult = curl_exec($ch);
  $obj = json_decode($execResult, true);
  //$balance = $obj["result"]["MinTradeSize"];
  return $obj;
}

function bittrexCoinPrice($apikey, $apisecret, $baseCoin, $coin){
      $nonce=time();
      $uri='https://bittrex.com/api/v1.1/public/getticker?market='.$baseCoin.'-'.$coin;
      $sign=hash_hmac('sha512',$uri,$apisecret);
      $ch = curl_init($uri);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $execResult = curl_exec($ch);
      $obj = json_decode($execResult, true);
      $balance = $obj["result"]["Last"];
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
    logAction("writeBittrexAction: ".$sql, 'BuySell',$logToFileSetting);
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
          $tmpCoinPrice[] = Array($fgc[$i]["symbol"],$fgc[$i]["market_cap_usd"],$fgc[$i]["percent_change_1h"],$fgc[$i]["percent_change_24h"],$fgc[$i]["percent_change_7d"]);
      }
      $start = $start + $limit + 1;
    }
    logAction("$cnmkt | ".json_decode($tmpCoinPrice),'CMC',$logToFileSetting);
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
    $temp['data'][$tempId]['quote']['USD']['percent_change_24h'],$temp['data'][$tempId]['quote']['USD']['percent_change_7d']);
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
  logAction("CoinMarketCapStatstoSQL($coinID,$MarketCap,$hr1Change, $hr24Change, $d7Change)",'CMC',$logToFileSetting);
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
  logAction("BittrexStatstoSQL($coinID, $volume, $sellOrders, $buyOrders)",'CMC',$logToFileSetting);
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
}

function addBuyRuletoSQL($bittrexRef, $buyRule){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `Transaction` set `BuyRule`= $buyRule WHERE `BittrexRef` = '$bittrexRef'";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
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
}

function copyNewPctChange($coinID,$PctChange1Hr, $PctChange24Hr, $PctChange7D){
  $conn = getSQLConn(rand(1,3));
  Echo "<BR> call UpdatePctChange($coinID, $PctChange1Hr, $PctChange24Hr, $PctChange7D);";
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
}

function getVolumeStats($stats){
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
  $piecesSize = count($pieces);
  $testTrue = False;
  //echo var_dump($pieces);
  for ($x = 0; $x < $piecesSize; $x++) {
    //Echo "<br> ".$pieces[$x];
    if (($ruleID == $pieces[$x][0] && $pieces[$x][1] == 0 && $buySell == 0) OR ($ruleID == $pieces[$x][1] && $pieces[$x][0] == 0&& $buySell == 1)){
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

function sellCoins($apikey, $apisecret, $coin, $email, $userID, $score, $date,$baseCurrency, $sendEmail, $sellCoin, $ruleID,$userName, $orderNo,$amount,$cost,$transactionID,$coinID,$CoinSellOffsetEnabled,$CoinSellOffsetPct,$LiveCoinPrice){
  echo "$apikey, $apisecret, $coin, $email, $userID, $score, $date,$baseCurrency, $sendEmail, $sellCoin, $ruleID,$userName, $orderNo,$amount,$cost";
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
    $obj = bittrexsell($apikey, $apisecret, $coin ,$amount, $bitPrice, $baseCurrency);
    Echo "<br>Here2";
    //$bittrexRef = $obj['result'][0]['uuid'];
    $bittrexRef = $obj["result"]["uuid"];
    echo "<BR>BITTREXREF: $bittrexRef";
    $status = $obj["success"];
    echo "<br> STATUS: $status";
    if ($status == 1){
      //$totalBTC = getTotalLimit($userID);
      Echo "<br>Here3";
      echo "<br>updateSQL($baseCurrency,$transactionID,$bittrexRef)";
      //updateSQL($baseCurrency,$transactionID,$bittrexRef);
      echo "<BR>writeBittrexAction($coinID,$transactionID,$userID,$bittrexRef, $date, $status,$bitPrice,Sell);";
      //writeBittrexAction($coinID,$transactionID,$userID,$type,$bittrexRef,$date,$status,$sellPrice){
      //writeBittrexAction($coinID,$transactionID,$userID,"Sell",$bittrexRef, $date, $status,$bitPrice);
      bittrexSellAdd($coinID, $transactionID, $userID, 'Sell', $bittrexRef, $status, $bitPrice, $ruleID);
      logToSQL("Bittrex", "Sell Coin Add $bitPrice ", $userID);
    }
    logAction("SellCoins:  ".json_encode($obj), 'BuySell',$logToFileSetting);
    logToSQL("Bittrex", "Sell Coin Error: ".json_encode($obj), $userID);
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
}

function bittrexsell($apikey, $apisecret, $symbol, $quant, $rate, $baseCurrency){
    $nonce=time();
    $uri='https://bittrex.com/api/v1.1/market/selllimit?apikey='.$apikey.'&market='.$baseCurrency.'-'.$symbol.'&quantity='.$quant.'&rate='.$rate.'&nonce='.$nonce;
    echo "<BR>$uri<BR>";
    $sign=hash_hmac('sha512',$uri,$apisecret);
    $ch = curl_init($uri);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
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

function bittrexCancel($apikey, $apisecret, $uuid){
    $nonce=time();
    $uri='https://bittrex.com/api/v1.1/market/cancel?apikey='.$apikey.'&uuid='.$uuid.'&nonce='.$nonce;
    $sign=hash_hmac('sha512',$uri,$apisecret);
    $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
    $balance = $obj["success"];
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
  logAction("cancelBittrexSQL: ".$sql, 'BuySell',$logToFileSetting);
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
  logAction("changeTransStatus: ".$sql, 'BuySell',$logToFileSetting);
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
  logAction("bittrexBuyAdd: ".$sql, 'BuySell',$logToFileSetting);
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
  logAction("bittrexAddNoOfPurchases: ".$sql, 'BuySell',$logToFileSetting);
}

function bittrexSellAdd($coinID, $transactionID, $userID, $type, $bittrexRef, $status, $bitPrice, $ruleID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call AddBittrexSell($coinID, $transactionID, $userID, '$type', '$bittrexRef', '$status', $bitPrice, $ruleID);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("bittrexSellAdd: ".$sql, 'BuySell',$logToFileSetting);
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
  logAction("bittrexSellCancel: ".$sql, 'BuySell',$logToFileSetting);
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
  logAction("bittrexBuyCancel: ".$sql, 'BuySell',$logToFileSetting);
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
  logAction("bittrexBuyComplete: ".$sql, 'BuySell',$logToFileSetting);
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
  logAction("bittrexSellComplete: ".$sql, 'BuySell',$logToFileSetting);
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
  logAction("bittrexBuyCompleteUpdateAmount: ".$sql, 'BuySell',$logToFileSetting);
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
  logAction("bittrexSellCompleteUpdateAmount: ".$sql, 'BuySell',$logToFileSetting);
}

function getTotalBTC($userID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `OpenBTC` FROM `AllTimeBTC` WHERE `UserID` = $userID";
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['OpenBTC']);}
  $conn->close();
  return $tempAry;
}

function getDailyBTC($userID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `OpenBTC` FROM `DailyBTC` WHERE `UserID` = $userID";
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['OpenBTC']);}
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

function coinPriceHistory($coinID,$price,$baseCurrency,$date){
  $conn = getHistorySQL(rand(1,4));
  Echo "<BR> UpdateHistoryPrice : call UpdateHistoryPrice($coinID,$price,'$baseCurrency','$date');";
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call UpdateHistoryPrice($coinID,$price,'$baseCurrency','$date');";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function get1HrChange($coinID, $date){
  $tempAry = [];
  $conn = getHistorySQL(rand(1,4));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT Ph.`Price` from `PriceHistory` Ph
    where `PriceDate` like '$date%' and CoinID = $coinID limit 1";
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
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call call NewUpdate1HrPriceChange($price,$coinID);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function update24HrPriceChange($price,$coinID){
  $conn = getSQLConn(rand(1,3));
  echo "<BR> Update24HrPriceChange : call Update24HrPriceChange($price,$coinID);";
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call Update24HrPriceChange($price,$coinID);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
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
}

function bittrexUpdateBuyQty($transactionID, $quantity){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call CompleteBittrexBuyUpdateAmount($transactionID,$quantity);";
  print_r("<br>".$sql);
  if ($conn->query($sql) === TRUE) {echo "New record created successfully";
  } else {echo "Error: " . $sql . "<br>" . $conn->error;}
  $conn->close();
  logAction("bittrexUpdateBuyQty: ".$sql, 'BuySell',$logToFileSetting);
}

function bittrexUpdateSellQty($transactionID, $quantity){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call CompleteBittrexSellUpdateAmount($transactionID,$quantity);";
  print_r("<br>".$sql);
  if ($conn->query($sql) === TRUE) {echo "New record created successfully";
  } else {echo "Error: " . $sql . "<br>" . $conn->error;}
  $conn->close();
  logAction("bittrexUpdateSellQty: ".$sql, 'BuySell',$logToFileSetting);
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
  logAction("bittrexCopyTransNewAmount: ".$sql, 'BuySell',$logToFileSetting);
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

function logToSQL($subject, $comments, $UserID, $enabled = 0){
  if ($enabled == 1){
    $conn = getSQLConn(rand(1,3));
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $sql = "call LogToSQL($UserID,'$subject','$comments')";
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
  $headers = array("Dashboard.php", "Transactions.php", "Stats.php","BuyCoins.php","SellCoins.php","Profit.php","bittrexOrders.php","Settings.php", "CoinAlerts.php","console.php","AdminSettings.php");
  $ref = array("Dashboard", "Transactions", "Stats","Buy Coins","Sell Coins","Profit","Bittrex Orders","Settings","Coin Alerts","Console","Admin Settings");
  $headerLen = count($headers);
  $imgpath = '/Investment-Tracker/Cryptobot/1/Images/CBLogoSmall.png';
  ?><div class="header">
    <table>
      <TH><img src='<?php echo $imgpath; ?>' width="40"> </TH>
      <TH>Logged in as: <i class="glyphicon glyphicon-user"></i>  <?php echo $_SESSION['username']; ?></th></Table><br>
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
  ,`LiveSellOrders`,`LiveBuyOrders`,`LiveMarketCap`,`UserID` FROM `CoinAlertsView`";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['ID'],$row['CoinID'],$row['Action'],$row['Price'],$row['Symbol'],$row['UserName'],$row['Email'],$row['LiveCoinPrice'],$row['Category'],$row['Live1HrChange']
    ,$row['Live24HrChange'],$row['Live7DChange'],$row['ReocurringAlert'],$row['DateTimeSent'],$row['LiveSellOrders'],$row['LiveBuyOrders'],$row['LiveMarketCap'],$row['UserID']);
  }
  $conn->close();
  return $tempAry;
}

function getCoinAlertsUser($userId){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `ID`,`CoinID`, `Action`, `Price`, `Symbol`, `UserName`,`Email` ,`LiveCoinPrice`,`Category`,`Live1HrChange` ,`Live24HrChange` ,`Live7DChange`,`ReocurringAlert`,`DateTimeSent` FROM `CoinAlertsView` WHERE `UserID` = $userId";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['ID'],$row['CoinID'],$row['Action'],$row['Price'],$row['Symbol'],$row['UserName'],$row['Email'],$row['LiveCoinPrice'],$row['Category'],$row['Live1HrChange']
    ,$row['Live24HrChange'],$row['Live7DChange'],$row['ReocurringAlert'],$row['DateTimeSent']);
  }
  $conn->close();
  return $tempAry;
}

function getCoinAlertsbyID($id){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `ID`,`CoinID`, `Action`, `Price`, `Symbol`, `UserName`,`Email` ,`LiveCoinPrice`,`Category`,`Live1HrChange` ,`Live24HrChange` ,`Live7DChange`,`ReocurringAlert`,`DateTimeSent` FROM `CoinAlertsView` WHERE `ID` = $id";
  print_r($sql);
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
  $sql = "UPDATE `CoinAlerts` SET `CoinID`= $coinID, `Action`= '$action', `UserID`= $userID, `Category` = '$category', `ReocurringAlert`= $reocurring, `Price` = $price WHERE `ID` = $id";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function closeCoinAlerts($id){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `CoinAlerts` SET `Status`= 'Closed' WHERE `ID` = $id";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}

function updateAlertTime($id){
  $conn = getSQLConn(rand(1,3));
  $current_date = date('Y-m-d H:i');
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `CoinAlerts` SET `DateTimeSent`= '$current_date' WHERE `ID` = $id";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
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
      //Echo "<BR> $tempStr1";
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
  for ($n=0; $n<$starCount; $n++){
      $returnStr .= $tempStr.",".$tempStr.",".$tempStr.",";
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
    //echo "<BR> Test: ".$tempStr[$i][2];
    if (strpos($tempStr[$i][2], '*') !== false) {
        $starCount =substr_count($tempStr[$i][2],"*");
        //$returntempStr = $tempStr[$i].",";
        for ($x=0; $x<$starCount; $x++){

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
  //echo "<BR> $returnBuyRuleIDStr";
  //echo "<BR> $returnSellRuleIDStr";
  //echo "<BR> $returnStr";
  //echo "<BR> $returnUserIDStr";
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
  return rtrim($returnStr,',');
}

function getCoinPriceMatchList(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `BuyRuleID`,`SellRuleID`,`CoinID`,`Price`,`Symbol`,`LowPrice` FROM `CoinPriceMatchView`";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['BuyRuleID'],$row['SellRuleID'],$row['CoinID'],$row['Price'],$row['Symbol'],$row['LowPrice']);
  }
  $conn->close();
  return $tempAry;
}

function getCoinPricePattenList(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `BuyRuleID`,`SellRuleID`,`CoinPattern`,`UserID` FROM `CoinPricePatternView` ";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['BuyRuleID'],$row['SellRuleID'],$row['CoinPattern'],$row['UserID']);
  }
  $conn->close();
  return $tempAry;
}

function getCoin1HrPattenList(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `BuyRuleID`,`SellRuleID`,`Pattern`,`UserID` FROM `Coin1HrPatternView` order by `BuyRuleID`,`SellRuleID`";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['BuyRuleID'],$row['SellRuleID'],$row['Pattern'],$row['UserID']);
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

function getBuyRulesIDs($userID){
$conn = getSQLConn(rand(1,3));
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT `RuleID` FROM `UserBuyRules` WHERE `UserID` = $userID";
$result = $conn->query($sql);
//$result = mysqli_query($link4, $query);
//mysqli_fetch_assoc($result);
while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['RuleID'],);
}
$conn->close();
return $tempAry;
}

function addTrackingCoin($coinID, $coinPrice, $userID, $baseCurrency, $sendEmail, $buyCoin, $quantity, $ruleIDBuy, $coinSellOffsetPct, $coinSellOffsetEnabled, $buyType, $minsToCancelBuy, $sellRuleFixed, $toMerge, $noOfPurchases){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "INSERT INTO `TrackingCoins`(`CoinID`, `CoinPrice`, `UserID`, `BaseCurrency`, `SendEmail`, `BuyCoin`, `Quantity`, `RuleIDBuy`, `CoinSellOffsetPct`, `CoinSellOffsetEnabled`, `BuyType`, `MinsToCancelBuy`, `SellRuleFixed`, `Status`, `ToMerge`,`NoOfPurchases`)
  VALUES ($coinID,$coinPrice,$userID,$baseCurrency, $sendEmail, $buyCoin, $quantity, $ruleIDBuy, $coinSellOffsetPct, $coinSellOffsetEnabled, $buyType, $minsToCancelBuy, $sellRuleFixed, 'Open', $toMerge, $noOfPurchases)";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("AddTrackingCoin: ".$sql, 'TrackingCoins',$logToFileSetting);
}

function getNewTrackingCoins($userID = 0){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
//12
  $whereClause = "";
  if ($userID <> 0){ $whereClause = " WHERE `UserID` = $userID";}
  $sql = "SELECT `CoinID`,`CoinPrice`,`TrackDate`,`Symbol`,`LiveCoinPrice`,`PriceDifference`,`PctDifference`,`UserID`,`BaseCurrency`,`SendEmail`,`BuyCoin`,`Quantity`,`RuleIDBuy`,`CoinSellOffsetPct`
    ,`CoinSellOffsetEnabled`,`BuyType`,`MinsToCancelBuy`,`SellRuleFixed`,`APIKey`,`APISecret`,`KEK`,`Email`,`UserName`,`ID`,TIMESTAMPDIFF(MINUTE, NOW(), `TrackDate`) as MinsFromDate, `NoOfPurchases`,`NoOfRisesInPrice`
    ,`TotalRisesInPrice`
    FROM `TrackingCoinView`$whereClause";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['CoinID'],$row['CoinPrice'],$row['TrackDate'],$row['Symbol'],$row['LiveCoinPrice'],$row['PriceDifference'],$row['PctDifference'],$row['UserID'],$row['BaseCurrency'],$row['SendEmail'] //9
    ,$row['BuyCoin'],$row['Quantity'],$row['RuleIDBuy'],$row['CoinSellOffsetPct'],$row['CoinSellOffsetEnabled'],$row['BuyType'],$row['MinsToCancelBuy'],$row['SellRuleFixed'],$row['APIKey'],$row['APISecret'] //19
    ,$row['KEK'],$row['Email'],$row['UserName'],$row['ID'],$row['MinsFromDate'],$row['NoOfPurchases'],$row['NoOfRisesInPrice'],$row['TotalRisesInPrice']);
  }
  $conn->close();
  return $tempAry;

}

function setNewTrackingPrice($coinPrice, $ID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `TrackingCoins` SET `CoinPrice` = $coinPrice, `TrackDate` = CURRENT_TIMESTAMP() WHERE `ID` = $ID";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("setNewTrackingPrice: ".$sql, 'TrackingCoins',$logToFileSetting);
}

function closeNewTrackingCoin($ID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `TrackingCoins` SET `Status` = 'Closed' WHERE `ID` = $ID";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("closeNewTrackingCoin: ".$sql, 'TrackingCoins',$logToFileSetting);
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
  logAction("updateTrackingCoinToMerge: ".$sql, 'TrackingCoins',$logToFileSetting);
}

function updateMergeAry($toMergeAry, $finalMergeAry){
  $finalMergeArySize = Count($finalMergeAry);
  $existing = False;
  for ($j=0; $j<$finalMergeArySize; $j++){
    if ($toMergeAry[0] == $finalMergeAry[$j][0] && $toMergeAry[1] == $finalMergeAry[$j][1]){
      //User/Coin exist
      $existing = True;
      $finalMergeAry[$j][4] = $finalMergeAry[$j][4]+$toMergeAry[4];
      $finalMergeAry[$j][5] = $finalMergeAry[$j][5]+$toMergeAry[5];
      $finalMergeAry[$j][6] = $toMergeAry[3];
      $finalMergeAry[$j][7] = $finalMergeAry[$j][7]+1;
    }
  }
  if ($existing == False){
    $finalMergeAry[$finalMergeArySize+1][0] = $toMergeAry[0];
    $finalMergeAry[$finalMergeArySize+1][1] = $toMergeAry[1];
    $finalMergeAry[$finalMergeArySize+1][2] = $toMergeAry[2];
    $finalMergeAry[$finalMergeArySize+1][3] = $toMergeAry[3];
    $finalMergeAry[$finalMergeArySize+1][4] = $toMergeAry[4];
    $finalMergeAry[$finalMergeArySize+1][5] = $toMergeAry[5];
    $finalMergeAry[$finalMergeArySize+1][6] = $toMergeAry[3];
    $finalMergeAry[$finalMergeArySize+1][7] = 1;
  }
  return $finalMergeAry;
}

function mergeTransactions($transactionID, $amount, $avCost, $lastTransID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call MergeTransactions($avCost,$transactionID,$amount,$lastTransID);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("mergeTransactions($avCost,$transactionID,$amount,$lastTransID)",'TrackingCoins',$logToFileSetting);
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
  logAction("updateNoOfRisesInPrice: ".$sql, 'TrackingCoins',$logToFileSetting);
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
    WHERE  (`ActionDate` > DATE_SUB((select Max(`ActionDate`) from `CoinBuyHistory`), INTERVAL 1 Hour)) and `ID` = (select Max(`ID`) from `Coin` where `Symbol` = '$coin')
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
    $returnStr .= round($array[$i][0],4);
  }
  return $returnStr;
}

function saveImage($coin, $url, $savePath){
  $ch = curl_init($url);
  $fp = fopen($savePath.$coin.'.png', 'wb');
  curl_setopt($ch, CURLOPT_FILE, $fp);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_exec($ch);
  curl_close($ch);
  fclose($fp);
}
?>
