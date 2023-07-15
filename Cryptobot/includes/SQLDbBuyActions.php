<?php
require('../../SQLDb.php');

function getTrackingCoinsLoc(){
  $tempAry = [];
  $conn = getNewSQL(rand(1,4));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "SELECT `ID`,`Symbol`,`LiveBuyOrders`,`LastBuyOrders`,`BuyOrdersPctChange`,`LiveMarketCap`,`LastMarketCap`,`MarketCapPctChange`,`Live1HrChange`,`Last1HrChange`,`Hr1ChangePctChange`,`Live24HrChange`,`Last24HrChange`,`Hr24ChangePctChange`,`Live7DChange`,`Last7DChange`,`D7ChangePctChange`,`LiveCoinPrice`,`LastCoinPrice`,`CoinPricePctChange`,`LiveSellOrders` ,
    `LastSellOrders`,`SellOrdersPctChange`,`LiveVolume`,`LastVolume`,`VolumePctChange`,`BaseCurrency`,`Price4Trend`,`Price3Trend`, `LastPriceTrend`, `LivePriceTrend` FROM `CoinStatsView`";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['ID'],$row['Symbol'],$row['LiveBuyOrders'],$row['LastBuyOrders'],$row['BuyOrdersPctChange'],$row['LiveMarketCap'],$row['LastMarketCap'],$row['MarketCapPctChange'],$row['Live1HrChange'],$row['Last1HrChange'],$row['Hr1ChangePctChange'],
    $row['Live24HrChange'],$row['Last24HrChange'],$row['Hr24ChangePctChange'],$row['Live7DChange'],$row['Last7DChange'],$row['D7ChangePctChange'],$row['LiveCoinPrice'],$row['LastCoinPrice'],$row['CoinPricePctChange'],$row['LiveSellOrders'],$row['LastSellOrders'],
    $row['SellOrdersPctChange'],$row['LiveVolume'],$row['LastVolume'],$row['VolumePctChange'],$row['BaseCurrency'],$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend']);
  }
  $conn->close();
  return $tempAry;
}

function getUserRulesLoc(){
  $tempAry = [];
  $conn = getNewSQL(rand(1,4));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `UserID`,`BuyOrdersEnabled`,`BuyOrdersTop`,`BuyOrdersBtm`,`MarketCapEnabled`,`MarketCapTop`,`MarketCapBtm`,`1HrChangeEnabled`
  ,`1HrChangeTop`,`1HrChangeBtm`,`24HrChangeEnabled`,`24HrChangeTop`,`24HrChangeBtm`,
  `7DChangeEnabled`,`7DChangeTop`,`7DChangeBtm`,`CoinPriceEnabled`,`CoinPriceTop`,`CoinPriceBtm`,`SellOrdersEnabled`,`SellOrdersTop`,`SellOrdersBtm`
  ,`VolumeEnabled`,`VolumeTop`,`VolumeBtm`,`BuyCoin`,`SendEmail`,`BTCAmount`,`Email`,`UserName`,`APIKey`,`APISecret`,`EnableDailyBTCLimit`,`DailyBTCLimit`,`EnableTotalBTCLimit`
  ,`TotalBTCLimit`,`RuleID`,`BuyCoinOffsetPct`,`BuyCoinOffsetEnabled`,`PriceTrendEnabled`, `Price4Trend`, `Price3Trend`, `LastPriceTrend`, `LivePriceTrend`,`Active`,`DisableUntil`,`BaseCurrency`,`NoOfCoinPurchase`,
  `BuyType`,`TimeToCancelBuyMins` FROM `UserBuyRules`";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['UserID'],$row['BuyOrdersEnabled'],$row['BuyOrdersTop'],$row['BuyOrdersBtm'],$row['MarketCapEnabled'],$row['MarketCapTop'],
    $row['MarketCapBtm'],$row['1HrChangeEnabled'],$row['1HrChangeTop'],$row['1HrChangeBtm'],$row['24HrChangeEnabled'],$row['24HrChangeTop'],$row['24HrChangeBtm'],
    $row['7DChangeEnabled'],$row['7DChangeTop'],$row['7DChangeBtm'],$row['CoinPriceEnabled'],$row['CoinPriceTop'],$row['CoinPriceBtm'],$row['SellOrdersEnabled'],$row['SellOrdersTop'],
    $row['SellOrdersBtm'],$row['VolumeEnabled'],$row['VolumeTop'],$row['VolumeBtm'],$row['BuyCoin'],$row['SendEmail'],$row['BTCAmount'],$row['Email'],$row['UserName'],$row['APIKey'],
    $row['APISecret'],$row['EnableDailyBTCLimit'],$row['DailyBTCLimit'],$row['EnableTotalBTCLimit'],$row['TotalBTCLimit'],$row['RuleID'],$row['BuyCoinOffsetPct'],$row['BuyCoinOffsetEnabled'],
    $row['PriceTrendEnabled'],$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['Active'],$row['DisableUntil'],$row['BaseCurrency'],$row['NoOfCoinPurchase'],
    $row['BuyType'],$row['TimeToCancelBuyMins']);
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

function buyCoinsLoc($apikey, $apisecret, $coin, $email, $userID, $date,$baseCurrency, $sendEmail, $buyCoin, $btcBuyAmount, $ruleID,$userName, $coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins){
  $BTCBalance = bittrexbalance($apikey, $apisecret,$baseCurrency);
  //get min trade
  if ($buyType == 2){
    //$btcBuyAmount = ($BTCBalance/100.28)*100;
    $btcBuyAmount = ($BTCBalance/100.28)*$btcBuyAmount;
  }
  $subject = "Coin Alert: ".$coin;
  $from = 'Coin Alert <alert@investment-tracker.net>';
  echo "Balance: $BTCBalance";
  $minTradeAmount = getMinTradeAmount($coin,$baseCurrency,$apisecret);
  if ($buyCoin) {
    $subject = "Coin Purchase: ".$coin;
    $from = 'Coin Purchase <purchase@investment-tracker.net>';
  }
  $btcwithCharge = (($btcBuyAmount/100)*0.25)+$btcBuyAmount;
  echo "$btcwithCharge = (($btcBuyAmount/100)*0.25)+$btcBuyAmount;";
  if ($BTCBalance >= $btcwithCharge) {
    echo "buy Coin - Balance Sufficient";
    $bitPrice = number_format((float)(bittrexCoinPrice($apikey, $apisecret,$baseCurrency,$coin)), 8, '.', '');
    if ($CoinSellOffsetEnabled == 1){

      $bitPrice = number_format((float)newPrice($bitPrice,$CoinSellOffsetPct, "Buy"), 8, '.', '');
    }
    //$livePrice = getLiveCoinPrice($tracking[$x][0]);
    //$avgCoinPrice = getAveragePrice($coin);
    //echo "<BR>AvgCoinPrice: ".$avgCoinPrice[0][0]." CoinPrice: ".$bitPrice;
    //if ($avgCoinPrice > $bitPrice){ return; }
    $quantity = Round($btcBuyAmount/$bitPrice,8,PHP_ROUND_HALF_UP);
    if ($quantity>$minTradeAmount){
        echo "Quantity above min trade amount";
        //buyCoins($apikey, $apisecret,$coin, $quantity, $bitPrice, $email,$minTradeAmount, $userID, $totalScore,$date, $baseCurrency);
        $orderNo = "ORD".$coin.date("YmdHis", time()).$ruleID;
        echo "Buy Coin = $buyCoin";
        if ($buyCoin){
          $obj = bittrexbuy($apikey, $apisecret, $coin, $quantity, $bitPrice);
          //writeSQLBuy($coin, $quantity, $bitPrice, $date, $orderNo, $userID, $baseCurrency);
          $bittrexRef = $obj["result"]["uuid"];
          $status = $obj["success"];
          if ($status == 1){
            echo "bittrexBuyAdd($coinID, $userID, 'Buy', $bittrexRef, $status, $ruleID, $bitPrice, $quantity, $orderNo);";
            date_default_timezone_set('Asia/Dubai');
            //$tmpTime = $timeToCancelBuyMins;
            //$newDate = date("Y-m-d H:i:s", time());
            //$current_date = date('Y-m-d H:i:s');
            //$newTime = date("Y-m-d H:i:s",strtotime('+'.$timeToCancelBuyMins.'Mins', strtotime($current_date)));
            //$buyCancelTime = strtotime( '+ 16 minute');
            bittrexBuyAdd($coinID, $userID, 'Buy', $bittrexRef, $status, $ruleID, $bitPrice, $quantity, $orderNo,$timeToCancelBuyMins);
            //writeBittrexActionBuy($coinID,$userID,'Buy',$bittrexRef,$date,$status,$bitPrice,$ruleID);
          }
        }
        if ($sendEmail==1 && $buyCoin ==0){
        //if ($sendEmail){
          sendEmail($email, $coin, $quantity, $bitPrice, $orderNo, $score, $subject,$userName, $from);
        }
    }else{ echo "<BR> BITTREX BALANCE INSUFFICIENT $quantity>$minTradeAmount"; }
  }
}

function writeSQLTransBuyLoc($type, $coinID,$userID, $cost,$amounttobuy, $date, $BittrexID, $orderNo){
  $currentDate = date("Y-m-d H:i:s", time());
  $conn = getNewSQL(rand(1,4));
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
}




?>
