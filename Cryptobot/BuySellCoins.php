<html>
<?php
ini_set('max_execution_time', 600);
require('includes/newConfig.php');

include_once ('/home/stevenj1979/SQLData.php');
include_once ('/home/stevenj1979/Encrypt.php');
$apikey=getAPIKey();
$apisecret=getAPISecret();
$logToSQLSetting = getLogToSQL();
$logToFileSetting = getLogToFile();
//$buyCancelTime = "01:0";
//$noOfBuys = 5;
$buyCounter = [];
$tmpTime = "+5 seconds";
if (!empty($argv[1])){
  parse_str($argv[1], $params);
  $tmpTime = str_replace('_', ' ', $params['mins']);
  //echo $tmpTime;
  //error_log($argv[1], 0);
}
//echo "<BR> isEmpty : ".empty($_GET['mins']);
if (!empty($_GET['mins'])){
  $tmpTime = str_replace('_', ' ', $_GET['mins']);
  //echo "<br> GETMINS: ".$_GET['mins'];
}

function action_Alert($minutes,$email,$symbol,$price,$action,$userName,$category,$reocurring,$id,$userID, $logToFileSetting, $logToSQLSetting, $livePrice){
  if ($minutes > 30){
    sendAlertEmailLocal($email, $symbol, $price, $action, $userName, $livePrice, $category);
    logAction("Alert: $symbol $price $action $userName $category", 'BuySellAlert', $logToFileSetting);
    logToSQL("Alerts", "Coin: $symbol $action $category $price", $userID, $logToSQLSetting);
  }
  //Close Alert
  if ($reocurring == 0){closeCoinAlerts($id,'CoinAlerts');}else{updateAlertTime($id,'CoinAlerts');}
}

function action_Market_Alert($minutes,$email,$price,$action,$userName,$category,$reocurring,$id,$userID, $logToFileSetting, $logToSQLSetting, $livePrice){
  if ($minutes > 30){
    sendAlertEmailLocal($email, 'MarketAlerts', $price, $action, $userName, $livePrice, $category);
    logAction("Alert: $symbol $price $action $userName $category", 'BuySellAlert', $logToFileSetting);
    logToSQL("Alerts", "Coin: $symbol $action $category $price", $userID, $logToSQLSetting);
  }
  //Close Alert
  if ($reocurring == 0){closeCoinAlerts($id,'MarketAlerts');}else{updateAlertTime($id,'MarketAlerts');}
}

function sendAlertEmailLocal($to, $symbol , $price, $action, $user,$livePrice,$category){
    $subject = "Coin Alert: ".$coin;
    $from = 'Coin Alert <alert@investment-tracker.net>';
    $body = "Dear ".$user.", <BR/>";
    $body .= "Your coin Alert for $symbol has been triggered : "."<BR/>";
    $body .= "Category: $category <BR/>";
    $body .= "Coin: $symbol Action: $action Price: $price<BR/>";
    $body .= "Live Price: $livePrice <BR/>";
    $body .= "Kind Regards\nCryptoBot.";
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    //$headers .= 'From: Alerts <Alerts@Investment-Tracker.net>' . "\r\n";
    $headers .= "From:".$from."\r\n";
    $headers .= "To:".$to."\r\n";
    mail($to, $subject, wordwrap($body,70),$headers);
}

function getOutStandingBuy($tmpAry){
  $tmpStr = "";
  $tmpAryCount = count($tmpAry);
  for ($i=0; $i<$tmpAryCount; $i++){
    //Echo "<BR> getOutStandingBuy: ".$tmpAry[$i][1].":".$tmpAry[$i][2].",";
    if ($tmpAry[$i][0] <> 1){ $tmpStr .= $tmpAry[$i][1].":".$tmpAry[$i][2].",";}
  }
  return rtrim($tmpStr,",");
}

function getOutStandingSell(){

}

function initiateAry($ary, $userID){
  if (array_key_exists($userID,$ary)){
      //echo "<BR> Key Exists $userID";
  }else{
      //echo "<BR> Set Key to 0 $userID";
    $ary[$userID] = 0;
  }
  return $ary;
}

function returnAlert($price,$livePrice,$action){
  $returnBool = False;
  if (isset($price)){
    if ($action == 'LessThan'){
      if ($price > $livePrice){
        Echo "<BR> $action | $price | $livePrice";
        $returnBool = True;
      }
    }else{
      if ($price < $livePrice){
        Echo "<BR> $action | $price | $livePrice";
        $returnBool = True;
      }
    }
  }
  return $returnBool;
}


//set time
setTimeZone();
$date = date("Y-m-d H:i", time());
$current_date = date('Y-m-d H:i');
$completeFlag = False;
$newTime = date("Y-m-d H:i",strtotime($tmpTime, strtotime($current_date)));
logAction("Buy Sell Coins Start : End set to $newTime : $date", 'BuySellTiming', $logToFileSetting);
$buyRules = getUserRules();
$buyRulesSize = count($buyRules);
$sellRules = getUserSellRules();
$sellRulesSize = count($sellRules);
$i = 0;
$coins = getTrackingCoins();
$coinLength = Count($coins);
//echo "<BR> coinPriceMatch";
$coinPriceMatch = getCoinPriceMatchList();
//echo "<BR> coinPricePatternList";
$coinPricePatternList = getCoinPricePattenList();
//echo "<BR> coin1HrPatternList";
$coin1HrPatternList = getCoin1HrPattenList();
//echo "<BR> autoBuyPrice";
$autoBuyPrice = getAutoBuyPrices();
$apiVersion = 1;

//echo "<br> coinLength= $coinLength NEWTime=".$newTime." StartTime $date EndTime $newTime";
while($completeFlag == False){
  $newTrackingCoins = getNewTrackingCoins();
  $newTrackingCoinsSize = count($newTrackingCoins);
  echo "<BR> Tracking COINS!! ";
  echo "<blockquote>";
  sleep(1);
  $ruleProfit = getRuleProfit();
  for($a = 0; $a < $newTrackingCoinsSize; $a++) {
    $APIKey = $newTrackingCoins[$a][18];$APISecret = $newTrackingCoins[$a][19];$KEK = $newTrackingCoins[$a][20];
    $symbol = $newTrackingCoins[$a][3];$baseCurrency = $newTrackingCoins[$a][8];
    $Email = $newTrackingCoins[$a][21];$userID = $newTrackingCoins[$a][7];$UserName = $newTrackingCoins[$a][22];
    $SendEmail = $newTrackingCoins[$a][9];$BuyCoin = $newTrackingCoins[$a][10];$BTCAmount = $newTrackingCoins[$a][11];
    $ruleIDBuy = $newTrackingCoins[$a][12];$coinID = $newTrackingCoins[$a][0];$CoinSellOffsetPct = $newTrackingCoins[$a][13];$CoinSellOffsetEnabled = $newTrackingCoins[$a][14];
    $buyType = $newTrackingCoins[$a][15];$timeToCancelBuyMins = $newTrackingCoins[$a][16];$SellRuleFixed = $newTrackingCoins[$a][17];
    $pctProfit = $newTrackingCoins[$a][6]; $newTrackingCoinID = $newTrackingCoins[$a][23]; $liveCoinPrice = $newTrackingCoins[$a][4];
    $minsFromDate = $newTrackingCoins[$a][24]; $noOfPurchases = $newTrackingCoins[$a][25]; $noOfRisesInPrice = $newTrackingCoins[$a][26]; $totalRisesInPrice = $newTrackingCoins[$a][27];
    $disableUntil = $newTrackingCoins[$a][28]; $noOfBuys = $newTrackingCoins[$a][29]; $originalPrice = $newTrackingCoins[$a][30];
    $risesInPrice = $newTrackingCoins[$a][31]; $limitBuyAmountEnabled = $newTrackingCoins[$a][32]; $limitBuyAmount = $newTrackingCoins[$a][33];
    $limitBuyTransactionsEnabled = $newTrackingCoins[$a][34];$limitBuyTransactions = $newTrackingCoins[$a][35];
    $noOfBuyModeOverrides = $newTrackingCoins[$a][36]; $coinModeOverridePriceEnabled = $newTrackingCoins[$a][37]; $coinMode = $newTrackingCoins[$a][38];
    $type = $newTrackingCoins[$a][39];
    $trackCounter = initiateAry($trackCounter,$userID."-".$coinID);
    $trackCounter = initiateAry($trackCounter,$userID."-Total");
    $minusMinsToCancel = $timeToCancelBuyMins-$timeToCancelBuyMins-$timeToCancelBuyMins;
    if ($disableUntil > date("Y-m-d H:i:s", time())){ echo "<BR> EXIT: Disabled until: ".$disableUntil; continue;}
    $ruleProfitSize = count($ruleProfit);
    for ($h=0; $h<$ruleProfitSize; $h++){
        if ($limitBuyAmountEnabled == 1){
          //echo "<BR> TEST limitBuyAmountEnabled: $limitBuyAmountEnabled | ".$ruleProfit[$h][4]." | $ruleIDBuy | ".$ruleProfit[$h][1]." | $limitBuyAmount";
          if ($ruleProfit[$h][4] == $ruleIDBuy and $ruleProfit[$h][1] >= $limitBuyTransactions){echo "<BR>EXIT: Rule Amount Exceeded! "; cancelTrackingBuy($ruleIDBuy); continue;}
        }
        if ($limitBuyTransactionsEnabled == 1){
          //echo "<BR> TEST limitBuyTransactionEnabled: $limitBuyTransactionsEnabled | ".$ruleProfit[$h][4]." | $ruleIDBuy | ".$ruleProfit[$h][5]." | $limitBuyTransactions";
          if ($ruleProfit[$h][4] == $ruleIDBuy and $ruleProfit[$h][5] >= $limitBuyTransactions){echo "<BR>EXIT: Rule Transaction Count Exceeded! "; cancelTrackingBuy($ruleIDBuy); continue;}
        }elseif($coinModeOverridePriceEnabled == 1 ){
          //echo "<BR> TEST limitBuyTransactionEnabled: $limitBuyAmount | $noOfBuyModeOverrides | ".$ruleProfit[$h][5];
          if ($ruleProfit[$h][4] == $ruleIDBuy and ($limitBuyAmount + $noOfBuyModeOverrides) >=  $ruleProfit[$h][5]){echo "<BR>EXIT: Rule Transaction Count Override Exceeded! ";cancelTrackingBuy($ruleIDBuy); continue;}
        }
    }
    $coinAllocation = getCoinAllocation($userID);
    if ($coinMode > 0){if ($coinAllocation[0][2]== 0){ continue;}}
    if ($coinMode == 0){if ($coinAllocation[0][0]== 0){ continue;}}
    if ($coinMode > 0){ if ($coinAllocation[0][2]<$BTCAmount){$BTCAmount = $coinAllocation[0][2]; }}elseif ($coinMode == 0){if ($coinAllocation[0][0]<$BTCAmount){$BTCAmount = $coinAllocation[0][0]; }}
    if ($trackCounter[$userID."-Total"] >= $noOfBuys){ echo "<BR>EXIT: Buy Counter Met! $noOfBuys ".$trackCounter[$userID."-Total"];continue;}//else{ Echo "<BR> Number of Buys: $noOfBuys BuyCounter ".$trackCounter[$userID];}
    if ($trackCounter[$userID."-".$coinID] >= 1){ echo "<BR>EXIT: Buy Counter Met! $noOfBuys ".$trackCounter[$userID."-".$coinID];continue;}//else{ Echo "<BR> Number of Buys: $noOfBuys BuyCounter ".$trackCounter[$userID];}
    if($minsFromDate <= $minusMinsToCancel){closeNewTrackingCoin($newTrackingCoinID, True);logToSQL("TrackingCoins", "closeNewTrackingCoin($newTrackingCoinID); $pctProfit", $userID, $logToSQLSetting); Echo "<BR> MinsFromDate: $minsFromDate | $minusMinsToCancel"; continue;}
    if ($pctProfit > 0 && $minsFromDate <= -5 && $pctProfit < 3){
      //Buy
      if ($noOfRisesInPrice >= $risesInPrice){
        if ($limitBuyAmountEnabled == 1){
          $ruleProfitSize = count($ruleProfit);
          for ($g=0; $g<$ruleProfitSize; $g++){
            //echo "<BR> TEST limitBuyAmountEnabled: $limitBuyAmountEnabled | ".$ruleProfit[$g][4]." | $ruleIDBuy | ".$ruleProfit[$g][1]." | $limitBuyAmount";
            logToSQL("TrackingCoins", "limitBuyAmountEnabled: $limitBuyAmountEnabled | ".$ruleProfit[$g][4]." | $ruleIDBuy | ".$ruleProfit[$g][1]." | $limitBuyAmount", $userID, 1);
            if ($ruleProfit[$g][4] == $ruleIDBuy and $ruleProfit[$g][1] >= $limitBuyAmount){echo "<BR>EXIT: Rule Amount Exceeded! "; continue;}
          }
        }
        if (!Empty($KEK)){ $APISecret = Decrypt($KEK,$newTrackingCoins[$a][19]);}
        $date = date("Y-m-d H:i:s", time());
        if ($liveCoinPrice < $originalPrice){ $buyCoinPrice = $originalPrice; }else{ $buyCoinPrice=0;}
        $checkBuy = buyCoins($APIKey, $APISecret,$symbol, $Email, $userID, $date, $baseCurrency,$SendEmail,$BuyCoin,$BTCAmount, $ruleIDBuy,$UserName,$coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed, $buyCoinPrice, $noOfPurchases+1);
        logToSQL("BuyCoin", "Symbol: $symbol | Amount: $BTCAmount | Profit:  $pctProfit | CheckBuy: $checkBuy", $userID, $logToSQLSetting);
        UpdateProfit();
        subUSDTBalance('USDT', $BTCAmount,$liveCoinPrice, $userID);
        //if ($checkBuy){

        closeNewTrackingCoin($newTrackingCoinID);
        logToSQL("TrackingCoins", "closeNewTrackingCoin($newTrackingCoinID);", $userID, $logToSQLSetting);
        $trackCounter[$userID."-".$coinID] = $trackCounter[$userID."-".$coinID] + 1;
        $trackCounter[$userID."-Total"] = $trackCounter[$userID."-Total"] + 1;
        break;
        //}
      }else{
        //add 1 $noOfRisesInPrice
        updateNoOfRisesInPrice($newTrackingCoinID, $noOfRisesInPrice+1);
        $newNoOfRisesInPrice =  $noOfRisesInPrice+1;
        logToSQL("TrackingCoins", "updateNoOfRisesInPrice($newTrackingCoinID, ".$newNoOfRisesInPrice.");", $userID, $logToSQLSetting);
      }

    }elseif ($pctProfit < 0 && $minsFromDate <= -5){
      // set $noOfRisesInPrice to 0
      updateNoOfRisesInPrice($newTrackingCoinID, 0);
      logToSQL("TrackingCoins", "updateNoOfRisesInPrice($newTrackingCoinID, 0);", $userID, $logToSQLSetting);
      // set new price
      setNewTrackingPrice($liveCoinPrice, $newTrackingCoinID);
      Echo "<BR> setNewTrackingPrice($liveCoinPrice, $newTrackingCoinID)";
      logToSQL("TrackingCoins", "setNewTrackingPrice($liveCoinPrice, $newTrackingCoinID); $pctProfit", $userID, $logToSQLSetting);
    }elseif ($pctProfit > 5 && $minsFromDate <= -5 ){
      closeNewTrackingCoin($newTrackingCoinID, True);
      logToSQL("TrackingCoins", "closeNewTrackingCoin($newTrackingCoinID); $pctProfit", $userID, $logToSQLSetting);
    }
    //elseif($minsFromDate <= -45){
    //  closeNewTrackingCoin($newTrackingCoinID, True);
    //  logToSQL("TrackingCoins", "closeNewTrackingCoin($newTrackingCoinID); $pctProfit", $userID, $logToSQLSetting);
    //}


  }
  echo "</blockquote>";
  echo "<BR> Tracking SELL COINS!! ";
  echo "<blockquote>";
  $newTrackingSellCoins = getNewTrackingSellCoins();
  $newTrackingSellCoinsSize = count($newTrackingSellCoins);
  sleep(1);
  for($b = 0; $b < $newTrackingSellCoinsSize; $b++) {
    $CoinPrice = $newTrackingSellCoins[$b][0]; $TrackDate = $newTrackingSellCoins[$b][1];  $userID = $newTrackingSellCoins[$b][2]; $NoOfRisesInPrice = $newTrackingSellCoins[$b][3]; $TransactionID = $newTrackingSellCoins[$b][4];
    $BuyRule = $newTrackingSellCoins[$b][5]; $FixSellRule = $newTrackingSellCoins[$b][6]; $OrderNo = $newTrackingSellCoins[$b][7]; $Amount = $newTrackingSellCoins[$b][8]; $CoinID = $newTrackingSellCoins[$b][9];
    $APIKey = $newTrackingSellCoins[$b][10]; $APISecret = $newTrackingSellCoins[$b][11]; $KEK = $newTrackingSellCoins[$b][12]; $Email = $newTrackingSellCoins[$b][13]; $UserName = $newTrackingSellCoins[$b][14];
    $BaseCurrency = $newTrackingSellCoins[$b][15]; $SendEmail = $newTrackingSellCoins[$b][16]; $SellCoin = $newTrackingSellCoins[$b][17]; $CoinSellOffsetEnabled = $newTrackingSellCoins[$b][18]; $CoinSellOffsetPct = $newTrackingSellCoins[$b][19];
    $LiveCoinPrice = $newTrackingSellCoins[$b][20]; $minsFromDate = $newTrackingSellCoins[$b][21]; $profit = $newTrackingSellCoins[$b][22]; $fee = $newTrackingSellCoins[$b][23]; $ProfitPct = $newTrackingSellCoins[$b][24];
    $totalRisesInPrice =  $newTrackingSellCoins[$b][30]; $coin = $newTrackingSellCoins[$b][26]; $ogPctProfit = $newTrackingSellCoins[$b][27]; $originalCoinPrice = $newTrackingSellCoins[$b][29];
    $minsFromStart = $newTrackingSellCoins[$b][32]; $fallsInPrice = $newTrackingSellCoins[$b][33]; $type = $newTrackingSellCoins[$b][34];
    echo "<BR> Checking $coin : $CoinPrice ; No Of RISES $NoOfRisesInPrice ! Profit % $ProfitPct | Mins from date $minsFromDate ! Original Coin Price $originalCoinPrice | mins from Start: $minsFromStart | UserID : $userID Falls in Price: $fallsInPrice";
    if ($ProfitPct < -5 && $minsFromDate <= -5 OR $ogPctProfit < 0){
      closeNewTrackingSellCoin($TransactionID);
      reopenTransaction($TransactionID);
    }
    if ($minsFromStart <= -60 &&  $ogPctProfit > 1.5){
      $date = date("Y-m-d H:i:s", time());
      reopenTransaction($TransactionID);
      if (!Empty($KEK)){ $APISecret = Decrypt($KEK,$newTrackingSellCoins[$b][11]);}
      logAction("sellCoins($APIKey, $APISecret,$coin, $Email, $userID, 0,$date, $BaseCurrency,$SendEmail,$SellCoin, $FixSellRule,$UserName,$OrderNo,$Amount,$CoinPrice,$TransactionID,$CoinID,$CoinSellOffsetEnabled,$CoinSellOffsetPct,$LiveCoinPrice);", 'SellCoins', 1);
      logToSQL("TrackingSellCoins", "sellCoins($APIKey, $APISecret,$coin, $Email, $userID, 0,$date, $BaseCurrency,$SendEmail,$SellCoin, $FixSellRule,$UserName,$OrderNo,$Amount,$CoinPrice,$TransactionID,$CoinID,$CoinSellOffsetEnabled,$CoinSellOffsetPct,$LiveCoinPrice);", $userID, 1);
      $checkSell = sellCoins($APIKey, $APISecret,$coin, $Email, $userID, 0,$date, $BaseCurrency,$SendEmail,$SellCoin, $FixSellRule,$UserName,$OrderNo,$Amount,$CoinPrice,$TransactionID,$CoinID,$CoinSellOffsetEnabled,$CoinSellOffsetPct,$LiveCoinPrice);
      addUSDTBalance('USDT', $BTCAmount,$liveCoinPrice, $userID);
      //CloseTrackingSellCoin
      if ($checkSell){closeNewTrackingSellCoin($TransactionID);}
    }
    if ($ProfitPct < 0 && $minsFromDate <= -5 && $ProfitPct > -3){
      echo "<BR> Option 1 | $ProfitPct < -0.25 && $minsFromDate >= 4 && $ProfitPct > -1.25";
      if ($NoOfRisesInPrice >= $fallsInPrice && $ogPctProfit >= 0.25){
        //Sell CoinS
        $date = date("Y-m-d H:i:s", time());
        reopenTransaction($TransactionID);
        if (!Empty($KEK)){ $APISecret = Decrypt($KEK,$newTrackingSellCoins[$b][11]);}
        logAction("sellCoins($APIKey, $APISecret,$coin, $Email, $userID, 0,$date, $BaseCurrency,$SendEmail,$SellCoin, $FixSellRule,$UserName,$OrderNo,$Amount,$CoinPrice,$TransactionID,$CoinID,$CoinSellOffsetEnabled,$CoinSellOffsetPct,$LiveCoinPrice);", 'SellCoins', 1);
        logToSQL("TrackingSellCoins", "sellCoins($APIKey, $APISecret,$coin, $Email, $userID, 0,$date, $BaseCurrency,$SendEmail,$SellCoin, $FixSellRule,$UserName,$OrderNo,$Amount,$CoinPrice,$TransactionID,$CoinID,$CoinSellOffsetEnabled,$CoinSellOffsetPct,$LiveCoinPrice);", $userID, 1);
        $checkSell = sellCoins($APIKey, $APISecret,$coin, $Email, $userID, 0,$date, $BaseCurrency,$SendEmail,$SellCoin, $FixSellRule,$UserName,$OrderNo,$Amount,$CoinPrice,$TransactionID,$CoinID,$CoinSellOffsetEnabled,$CoinSellOffsetPct,$LiveCoinPrice,$type);
        //CloseTrackingSellCoin
        if ($checkSell){closeNewTrackingSellCoin($TransactionID);}
      }else{
        //UpdatePrice
        updateNoOfRisesInSellPrice($TransactionID, $NoOfRisesInPrice+1, $LiveCoinPrice);
        echo "<BR> No of rises in price for $coin = ".$NoOfRisesInPrice+1;
        //Add 1 to number of rises in price
      }
    }elseif ($ProfitPct > 0 && $minsFromDate <= -5){
      echo "<BR> Option 2";
      //Update Rises in price
      updateNoOfRisesInSellPrice($TransactionID, 0, $LiveCoinPrice);
      //Set new Tracking Price
      //setNewTrackingSellPrice($LiveCoinPrice, $TransactionID);
      echo "<BR> Reset No of rises in price for $coin : Price =  $LiveCoinPrice";
    }

  }
  echo "</blockquote>";
  echo "<BR> BUY COINS!! ";
  echo "<blockquote>";
  //logAction("Check Buy Coins Start", 'BuySellTiming');
  $userProfit = getTotalProfit();
  $marketProfit = getMarketProfit();
  $ruleProfit = getRuleProfit();
  //$pauseRulesFlag = True;
  //echo "<BR> Coin Length: $coinLength";
  sleep(1);
  for($x = 0; $x < $coinLength; $x++) {
    //variables
    $coinID = $coins[$x][0]; $symbol = $coins[$x][1]; $baseCurrency = $coins[$x][26];
    $BuyOrdersPctChange = $coins[$x][4]; $MarketCapPctChange = $coins[$x][7]; $Hr1ChangePctChange = $coins[$x][10];
    $Hr24ChangePctChange = $coins[$x][13]; $D7ChangePctChange = $coins[$x][16]; $CoinPricePctChange = $coins[$x][19];
    $SellOrdersPctChange = $coins[$x][22]; $VolumePctChange = $coins[$x][25];
    $price4Trend = $coins[$x][27]; $price3Trend = $coins[$x][28]; $lastPriceTrend = $coins[$x][29];  $livePriceTrend = $coins[$x][30];
    $newPriceTrend = $price4Trend.$price3Trend.$lastPriceTrend.$livePriceTrend;
    $LiveCoinPrice = $coins[$x][17]; $Hr1LivePriceChange = $coins[$x][31];$Hr1LastPriceChange = $coins[$x][32]; $Hr1PriceChange3 = $coins[$x][33];$Hr1PriceChange4 = $coins[$x][34];
    $new1HrPriceChange = $Hr1PriceChange4.$Hr1PriceChange3.$Hr1LastPriceChange.$Hr1LivePriceChange;
    //$timeToCancelBuyMins = $coins[$x][31];
    //LOG
    //echo "<br> i=$i CoinID=$coinID Coin=$symbol baseCurrency=$baseCurrency ";
    //echo "<blockquote>";
    //echo "<BR> Rule Length: $buyRulesSize";
    for($y = 0; $y < $buyRulesSize; $y++) {
      $buyResultAry = [];
      $buyOutstanding = "";
      //Variables
      $BuyOrdersEnabled = $buyRules[$y][1]; $BuyOrdersTop = $buyRules[$y][2]; $BuyOrdersBtm = $buyRules[$y][3];
      $MarketCapEnabled = $buyRules[$y][4]; $MarketCapTop = $buyRules[$y][5];$MarketCapBtm= $buyRules[$y][6];
      $Hr1ChangeEnabled = $buyRules[$y][7]; $Hr1ChangeTop = $buyRules[$y][8]; $Hr1ChangeBtm = $buyRules[$y][9];
      $Hr24ChangeEnabled = $buyRules[$y][10]; $Hr24ChangeTop = $buyRules[$y][11]; $Hr24ChangeBtm = $buyRules[$y][12];
      $D7ChangeEnabled = $buyRules[$y][13]; $D7ChangeTop = $buyRules[$y][14]; $D7ChangeBtm = $buyRules[$y][15];
      $CoinPriceEnabled = $buyRules[$y][16]; $CoinPriceTop = $buyRules[$y][17]; $CoinPriceBtm = $buyRules[$y][18];
      $SellOrdersEnabled = $buyRules[$y][19]; $SellOrdersTop = $buyRules[$y][20]; $SellOrdersBtm = $buyRules[$y][21];
      $VolumeEnabled = $buyRules[$y][22]; $VolumeTop = $buyRules[$y][23]; $VolumeBtm = $buyRules[$y][24];
      $BuyCoin = $buyRules[$y][25]; $SendEmail = $buyRules[$y][26];$BTCAmount = $buyRules[$y][27]; $KEK = $buyRules[$y][58];
      $SellRuleFixed = $buyRules[$y][59]; $overrideDailyLimit = $buyRules[$y][60];
      $Email = $buyRules[$y][28]; $UserName = $buyRules[$y][29]; $APIKey = $buyRules[$y][30];
      $APISecret = $buyRules[$y][31]; $coinPricePatternEnabled = $buyRules[$y][61]; $coinPricePattern = $buyRules[$y][62];
      $Hr1ChangeTrendEnabled = $buyRules[$y][63]; $Hr1ChangeTrend = $buyRules[$y][64]; $risesInPrice = $buyRules[$y][65];
      $totalProfitPauseEnabled = $buyRules[$y][66]; $totalProfitPause = $buyRules[$y][67]; $rulesPauseEnabled = $buyRules[$y][68];
      $rulesPause = $buyRules[$y][69]; $rulesPauseHours = $buyRules[$y][70]; $overrideDisableRule = $buyRules[$y][73];
      $limitBuyAmountEnabled = $buyRules[$y][74]; $limitBuyAmount = $buyRules[$y][75];
      $limitBuyTransactionsEnabled = $buyRules[$y][78]; $limitBuyTransactions = $buyRules[$y][79];
      if (!Empty($KEK)){$APISecret = decrypt($KEK,$buyRules[$y][31]);}
      //$APISecret = $buyRules[$y][31];
      //Echo " KEK $KEK APISecret $APISecret API ".$buyRules[$y][31];
      $EnableDailyBTCLimit = $buyRules[$y][32]; $DailyBTCLimit = $buyRules[$y][33]; $EnableTotalBTCLimit = $buyRules[$y][34];
      $TotalBTCLimit= $buyRules[$y][35]; $userID = $buyRules[$y][0]; $ruleIDBuy = $buyRules[$y][36]; $CoinSellOffsetPct = $buyRules[$y][37];
      $CoinSellOffsetEnabled = $buyRules[$y][38];
      $priceTrendEnabled = $buyRules[$y][39]; $price4TrendTrgt = $buyRules[$y][40];$price3TrendTrgt = $buyRules[$y][41];$lastPriceTrendTrgt = $buyRules[$y][42];
      $livePriceTrendTrgt = $buyRules[$y][43]; $userActive = $buyRules[$y][44]; $disableUntil = $buyRules[$y][45];
      $userBaseCurrency = $buyRules[$y][46]; $noOfBuys = $buyRules[$y][47]; $buyType = $buyRules[$y][48]; $timeToCancelBuyMins = $buyRules[$y][49];
      $BuyPriceMinEnabled = $buyRules[$y][50]; $BuyPriceMin = $buyRules[$y][51];
      $limitToCoin = $buyRules[$y][52]; $autoBuyCoinEnabled = $buyRules[$y][53];//$autoBuyPrice = $buyRules[$y][54];
      $buyAmountOverrideEnabled = $buyRules[$y][55]; $buyAmountOverride = $buyRules[$y][56];
      $newBuyPattern = $buyRules[$y][57];
      $MarketDropStopEnabled = $buyRules[$y][71]; $marketDropStopPct = $buyRules[$y][72];
      $overrideCancelBuyTimeEnabled = $buyRules[$y][76];
      $overrideCancelBuyTimeMins = $buyRules[$y][77];
      $noOfBuyModeOverrides = $buyRules[$y][78];$coinModeOverridePriceEnabled = $buyRules[$y][79];
      $buyCounter = initiateAry($buyCounter,$userID."-".$coinID);
      $buyCounter = initiateAry($buyCounter,$userID."-Total");

      if ($overrideCancelBuyTimeEnabled == 1){$timeToCancelBuyMins = $overrideCancelBuyTimeMins;}
      //if ($userID != ){ continue; }
      //echo "<BR> BUYCOINOFFSET Enabled: $CoinSellOffsetEnabled  - BUYCoinOffsetPct: $CoinSellOffsetPct";
      //echo "<BR> Buy PATTERN Enabled: $priceTrendEnabled - Buy Rule: $price4TrendTrgt : $price3TrendTrgt : $lastPriceTrendTrgt : $livePriceTrendTrgt";
      //echo "<BR> Disable Until $disableUntil";
      //echo "<BR>RULE: $ruleIDBuy USER: $userID API $APIKey Sectret: $APISecret ";
      //echo "<BR> BASE: $baseCurrency USERBASE: $userBaseCurrency ";

      //if ($limitBuyAmountEnabled == 1 ){
      //  $ruleProfitSize = count($ruleProfit);
      //  for ($g=0; $g<$ruleProfitSize; $g++){
      //    echo "<BR> TEST limitBuyAmountEnabled: $limitBuyAmountEnabled | ".$ruleProfit[$g][4]." | $ruleIDBuy | ".$ruleProfit[$g][1]." | $limitBuyAmount";
      //    if ($ruleProfit[$g][4] == $ruleIDBuy and $ruleProfit[$g][1] >= $limitBuyTransactions){echo "<BR>EXIT: Rule Amount Exceeded! "; continue;}
      //  }
      //}

      $ruleProfitSize = count($ruleProfit);
      for ($h=0; $h<$ruleProfitSize; $h++){
          if ($limitBuyAmountEnabled == 1){
            echo "<BR> TEST limitBuyAmountEnabled: $limitBuyAmountEnabled | ".$ruleProfit[$h][4]." | $ruleIDBuy | ".$ruleProfit[$h][1]." | $limitBuyAmount";
            if ($ruleProfit[$h][4] == $ruleIDBuy and $ruleProfit[$h][1] >= $limitBuyTransactions){echo "<BR>EXIT: Rule Amount Exceeded! "; continue;}
          }
          if ($limitBuyTransactionsEnabled == 1 and $coinModeOverridePriceEnabled == 0){
            echo "<BR> TEST limitBuyTransactionEnabled: $limitBuyTransactionsEnabled | ".$ruleProfit[$h][4]." | $ruleIDBuy | ".$ruleProfit[$h][5]." | $limitBuyTransactions";
            if ($ruleProfit[$h][4] == $ruleIDBuy and $ruleProfit[$h][5] >= $limitBuyTransactions){echo "<BR>EXIT: Rule Transaction Count Exceeded! "; continue;}
          }elseif($coinModeOverridePriceEnabled == 1 and $limitBuyAmountEnabled == 1){
            echo "<BR> TEST limitBuyTransactionEnabled: $limitBuyAmount | $noOfBuyModeOverrides | ".$ruleProfit[$h][5];
            if (($limitBuyAmount + $noOfBuyModeOverrides) >=  $ruleProfit[$h][5]){echo "<BR>EXIT: Rule Transaction Count Override Exceeded! "; continue;}
          }
      }
      //echo "<BR> Market Profit Enbled: $MarketDropStopEnabled Pct: $marketDropStopPct current: ".$marketProfit[0][0];
      if (isset($marketProfit[0][0])){
        if ($MarketDropStopEnabled == 1 and $marketProfit[0][0] <= $marketDropStopPct and $overrideDisableRule == 0){
          logToSQL("MarketDropStop", "Market Profit Enbled: $MarketDropStopEnabled Pct: $marketDropStopPct current: ".$marketProfit[0][0]." | RuleID $ruleIDBuy", $userID,1);
          pauseRule($ruleIDBuy,4, $userID);
          pauseTracking($userID);

        }elseif ($MarketDropStopEnabled == 1 and $marketProfit[0][1] >= 0.3 and $overrideDisableRule == 0){
          logToSQL("MarketDropStart", "pauseRule($ruleIDBuy,0, $userID);| MarketProfit: ".$marketProfit[0][1], $userID,1);
          pauseRule($ruleIDBuy,0, $userID);
        }
      }

      $profitNum = findUserProfit($userProfit,$userID);
      if ($totalProfitPauseEnabled == 1 && $profitNum<= $totalProfitPause && $ruleIDBuy == $rulesPause){
        if ($rulesPauseEnabled == 1){
          echo "<BR> PAUSING RULES $rulesPause for $rulesPauseHours HOURS";
          logToSQL("RulesPause", "pauseRule($rulesPause, $rulesPauseHours);", $userID,1);
          pauseRule($rulesPause, $rulesPauseHours);
          //$pauseRulesFlag = False;
        }
        echo "<BR>EXIT: TotalProfitPauseEnabled $totalProfitPauseEnabled Profit: $profitNum $totalProfitPause ";
        continue;}
      $GLOBALS['allDisabled'] = false;
      if (empty($APIKey) && empty($APISecret)){ continue;}
      if ($APIKey=="NA" && $APISecret == "NA"){ continue;}
      if ($baseCurrency != $userBaseCurrency && $userBaseCurrency != "All"){ continue;}
      if ($limitToCoin != "ALL" && $symbol != $limitToCoin) { continue;}
      //Echo "<BR>Rule Limited to :  $limitToCoin";

      //echo "<BR> Total Spend ".$totalBTCSpent[0][0]." Limit $TotalBTCLimit | Override: $overrideDailyLimit | Enable Total BTC Limit: $EnableTotalBTCLimit";
      if ($overrideDailyLimit == 0 && $EnableTotalBTCLimit == 1){
        echo "<BR> Check if over total limit! ";
        $totalBTCSpent = getTotalBTC($userID,$baseCurrency);
        //if (!empty($totalBTCSpent[0][0]) && $buyAmountOverrideEnabled == False){
        if ($totalBTCSpent[0][0] >= $TotalBTCLimit){ echo "<BR>EXIT: TOTAL BTC SPENT"; continue;}else{ echo "<BR> Total Spend ".$totalBTCSpent[0][0]." Limit $TotalBTCLimit";}
        //}
      }

      if ($overrideDailyLimit == 0 && $EnableDailyBTCLimit == 1){
        echo "<BR> Check if over daily limit! ";
        $dailyBTCSpent = getDailyBTC($userID,$baseCurrency);
        //echo "<BR> Daily Spend ".$dailyBTCSpent[0][0]." Limit $DailyBTCLimit";
        //if (!empty($dailyBTCSpent[0][0])){
          if ($dailyBTCSpent[0][0] >= $DailyBTCLimit){echo "<BR>EXIT: DAILY BTC SPENT";continue;}else{ echo "<BR> Daily Spend ".$dailyBTCSpent[0][0]." Limit $DailyBTCLimit";}
      //  }
      }

      if ($buyCounter[$userID."-".$coinID] >= 1 && $overrideDailyLimit == 0){ echo "<BR>EXIT: Buy Counter Met! $noOfBuys ".$buyCounter[$userID."-".$coinID];continue;
      }else{ Echo "<BR> Number of Coin Buys: 1 BuyCounter ".$buyCounter[$userID."-".$coinID];}
      if ($buyCounter[$userID."-Total"] >= $noOfBuys && $overrideDailyLimit == 0){ echo "<BR>EXIT: Buy Counter Met! $noOfBuys ".$buyCounter[$userID."-Total"];continue;
      }else{ Echo "<BR> Number of Total Buys: $noOfBuys BuyCounter ".$buyCounter[$userID."-Total"];}

      if ($userActive == False){ echo "<BR>EXIT: User Not Active!"; continue;}
      if ($disableUntil > date("Y-m-d H:i:s", time())){ echo "<BR> EXIT: Disabled until: ".$disableUntil; continue;}
      $LiveBTCPrice = number_format((float)(bittrexCoinPrice($apikey, $apisecret,'USD','BTC',$apiVersion)), 8, '.', '');
      //echo "<br> buyAmountOverride($buyAmountOverrideEnabled)) $BTCAmount = $buyAmountOverride; Echo <BR> 13: BuyAmountOverride set to : $buyAmountOverride;";
      //echo "Buying Coins: $APIKey, $APISecret,$symbol, $Email, $userID, $date, $baseCurrency,$SendEmail,$BuyCoin,$BTCAmount, $ruleIDBuy,$UserName,$coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed";
      //echo "1: MarketCap buyWithScore($MarketCapTop,$MarketCapBtm,$MarketCapPctChange,$MarketCapEnabled)<br>";

      $test1 = buyWithScore($MarketCapTop,$MarketCapBtm,$MarketCapPctChange,$MarketCapEnabled);
      $buyResultAry[] = Array($test1, "Market Cap $symbol", $MarketCapPctChange);
      $test2 = buyWithScore($VolumeTop,$VolumeBtm,$VolumePctChange,$VolumeEnabled);
      $buyResultAry[] = Array($test2, "Volume $symbol", $VolumePctChange);
      $test3 = buyWithScore($BuyOrdersTop,$BuyOrdersBtm,$BuyOrdersPctChange,$BuyOrdersEnabled);
      $buyResultAry[] = Array($test3, "Buy Orders $symbol", $BuyOrdersPctChange);
      $test4 = buyWithScore($Hr1ChangeTop,$Hr1ChangeBtm,$Hr1ChangePctChange,$Hr1ChangeEnabled);
      //echo "<BR> buyWithScore($Hr1ChangeTop,$Hr1ChangeBtm,$Hr1ChangePctChange,$Hr1ChangeEnabled);";
      $buyResultAry[] = Array($test4, "1 Hour Price Change $symbol", $Hr1ChangePctChange);
      $test5 = buyWithScore($Hr24ChangeTop,$Hr24ChangeBtm,$Hr24ChangePctChange,$Hr24ChangeEnabled);
      $buyResultAry[] = Array($test5, "24 Hour Price Change $symbol", $Hr24ChangePctChange);
      $test6 = buyWithScore($D7ChangeTop,$D7ChangeBtm,$D7ChangePctChange,$D7ChangeEnabled);
      $buyResultAry[] = Array($test6, "7 Day Price Change $symbol", $D7ChangePctChange);
      $test7 = buyWithScore($CoinPriceTop,$CoinPriceBtm,$CoinPricePctChange,$CoinPriceEnabled);
      $buyResultAry[] = Array($test7, "Coin Price $symbol", $CoinPricePctChange);
      $test8 = buyWithScore($SellOrdersTop,$SellOrdersBtm,$SellOrdersPctChange,$SellOrdersEnabled);
      $buyResultAry[] = Array($test8, "Sell Orders $symbol", $SellOrdersPctChange);
      //echo "<BR> NEW Buy with Pattern1 : $newPriceTrend,$coinPricePatternList,$priceTrendEnabled,$ruleIDBuy,0 | $coinID | $ruleIDBuy";
      if ($priceTrendEnabled){
        $test9 = newBuywithPattern($newPriceTrend,$coinPricePatternList,$priceTrendEnabled,$ruleIDBuy,0);
      }else{$test9 = True;}

      $buyResultAry[] = Array($test9, "Buy Price Pattern $symbol", $newPriceTrend);
      $test10 = buyWithMin($BuyPriceMinEnabled,$BuyPriceMin,$LiveCoinPrice);
      //Echo "<BR> TEST 10: $BuyPriceMinEnabled | $BuyPriceMin | $LiveCoinPrice | $test10";
      $buyResultAry[] = Array($test10, "Buy Price Minimum $symbol", $LiveCoinPrice);
      $test11 = autoBuyMain($LiveCoinPrice,$autoBuyPrice, $autoBuyCoinEnabled,$coinID);
      $buyResultAry[] = Array($test11, "Auto Buy Price $symbol", $LiveCoinPrice);
      $test12 = coinMatchPattern($coinPriceMatch,$LiveCoinPrice,$symbol,0,$coinPricePatternEnabled,$ruleIDBuy,0);
      $buyResultAry[] = Array($test12, "Coin Price Pattern $symbol", $LiveCoinPrice);
      //Echo "<BR> newBuywithPattern($new1HrPriceChange,$coin1HrPatternList,$Hr1ChangeTrendEnabled,$ruleIDBuy,0);";
      //echo "<BR> NEW Buy with Pattern2 : $new1HrPriceChange,$coin1HrPatternList,$Hr1ChangeTrendEnabled,$ruleIDBuy,0 | $coinID | $ruleIDBuy";
      if ($Hr1ChangeTrendEnabled){
        $test14 = newBuywithPattern($new1HrPriceChange,$coin1HrPatternList,$Hr1ChangeTrendEnabled,$ruleIDBuy,0);
      }else{$test14 = True;}
      $buyResultAry[] = Array($test14, "1 Hour Price Pattern $symbol", $new1HrPriceChange);
      $test13 = $GLOBALS['allDisabled'];
      if (buyAmountOverride($buyAmountOverrideEnabled)){$BTCAmount = $buyAmountOverride; Echo "<BR> 13: BuyAmountOverride set to : $buyAmountOverride";}
      //logAction("1: $test1 2: $test2 3: $test3 4: $test4 5: $test5 6: $test6 7: $test7 8: $test8 9: $test9 10: $test10 11: $test11 12: $test12 ", 'BuySell');
      //Echo "<BR> New Boolean Test! 1: $test1 2: $test2 3: $test3 4: $test4 5: $test5 6: $test6 7: $test7 8: $test8 9: $test9 10: $test10 11: $test11 12: $test12 ";
      $totalScore_Buy = $test1+$test2+$test3+$test4+$test5+$test6+$test7+$test8+$test9+$test10+$test11+$test12+$test13+$test14;
      if ($totalScore_Buy >= 13 ){
        $buyOutstanding = getOutStandingBuy($buyResultAry);
        logAction("UserID: $userID | RuleID: $ruleIDBuy | Coin : $symbol | 1:  $test1  2:  $test2  3:  $test3  4:  $test4  5:  $test5  6:  $test6  7:  $test7  8:  $test8  9:  $test9  10:  $test10  11:  $test11  12:  $test12  13:  $test13  14:  $test14  TOTAL: $totalScore_Buy / 14 $buyOutstanding","BuyScore", $logToFileSetting);
        logToSQL("TrackingCoins", "RuleID: $ruleIDBuy | Coin : $symbol | TOTAL:  $totalScore_Buy $buyOutstanding", $userID, $logToSQLSetting);
      }
      Echo "<BR> UserID: $userID | RuleID: $ruleIDBuy | Coin : $symbol| 1:$test1  2:$test2  3:$test3  4:$test4  5:$test5  6:$test6  7:$test7  8:$test8  9:$test9  10:$test10  11:$test11  12:$test12  13:$test13  14:$test14  TOTAL:$totalScore_Buy / 14";


      if ($test1 == True && $test2 == True && $test3 == True && $test4 == True && $test5 == True && $test6 == True && $test7 == True && $test8 == True && $test9 == True && $test10 == True &&
      $test11 == True && $test12 == True && $test13 == True && $test14 == True){
        $date = date("Y-m-d H:i:s", time());
        $BTCBalance = bittrexbalance($apikey, $apisecret,$baseCurrency, $apiVersion);
        $reservedAmount = getReservedAmount($baseCurrency,$userID);
        Echo "<BR> TEST BAL AND RES: $BTCBalance ; ".$reservedAmount[0][0]."| "; //.$BTCBalance-$reservedAmount
        //if ($reservedAmount <> 0){
        Echo "<BR> TEST BAL AND RES: $BTCBalance ; ".$reservedAmount[0][0]." | "; //.$BTCBalance-$reservedAmount
        $totalBal = $BTCBalance-$reservedAmount[0][0];
        //} else{ $totalBal = $BTCBalance;}
        if ($totalBal > 20) {
          echo "<BR>Buying Coins: $APIKey, $APISecret,$symbol, $Email, $userID, $date, $baseCurrency,$SendEmail,$BuyCoin,$BTCAmount, $ruleIDBuy,$UserName,$coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed";
          //buyCoins($APIKey, $APISecret,$symbol, $Email, $userID, $date, $baseCurrency,$SendEmail,$BuyCoin,$BTCAmount, $ruleIDBuy,$UserName,$coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed, 0);
          //updateReservedAmount($BTCAmount*$LiveCoinPrice,$baseCurrency,$userID);
          addTrackingCoin($coinID, $LiveCoinPrice, $userID, $baseCurrency, $SendEmail, $BuyCoin, $BTCAmount, $ruleIDBuy, $CoinSellOffsetPct, $CoinSellOffsetEnabled, $buyType, $timeToCancelBuyMins, $SellRuleFixed,0,0,$risesInPrice,'Buy',$LiveCoinPrice);
          //logAction("buyCoins($APIKey,$symbol, $Email, $userID, $date, $baseCurrency,$SendEmail,$BuyCoin,$BTCAmount, $ruleIDBuy,$UserName,$coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed,0)", 'BuySell');
          logToSQL("TrackingCoins", "addTrackingCoin($coinID, $LiveCoinPrice, $userID, $baseCurrency, $SendEmail, $BuyCoin, $BTCAmount, $ruleIDBuy, $CoinSellOffsetPct, $CoinSellOffsetEnabled, $buyType, $timeToCancelBuyMins, $SellRuleFixed,0);", $userID, $logToSQLSetting);
          $buyCounter[$userID."-".$coinID] = $buyCounter[$userID."-".$coinID] + 1;
          $buyCounter[$userID."-Total"] = $buyCounter[$userID."-Total"] + 1;
        }
      }

      echo "<BR> NEXT RULE <BR>";
    }//Rule Loop
    //echo "</blockquote>";
  }//Coin Loop
  echo "</blockquote>";
  echo "<BR> SELL COINS!! ";
  echo "<blockquote>";
  //logAction("Check Sell Coins Start", 'BuySellTiming');
  //echo "<blockquote>";
  $sellCoins = getTrackingSellCoins();
  $sellCoinsLength = count($sellCoins);
  $userProfit = getTotalProfit();
  sleep(1);
  for($a = 0; $a < $sellCoinsLength; $a++) {
    //Variables
    $coin = $sellCoins[$a][11]; $MarketCapPctChange = $sellCoins[$a][17]; $VolumePctChange = $sellCoins[$a][26];
    $SellOrdersPctChange = $sellCoins[$a][23]; $Hr1ChangePctChange = $sellCoins[$a][28]; $Hr24ChangePctChange = $sellCoins[$a][31];
    $D7ChangePctChange = $sellCoins[$a][34]; $LiveCoinPrice = $sellCoins[$a][19]; $CoinPricePctChange = $sellCoins[$a][20];
    $BaseCurrency = $sellCoins[$a][36]; $orderNo = $sellCoins[$a][10]; $amount = $sellCoins[$a][5]; $cost = $sellCoins[$a][4];
    $transactionID = $sellCoins[$a][0]; $coinID = $sellCoins[$a][2]; $sellCoinsUserID = $sellCoins[$a][3];
    $fixSellRule = $sellCoins[$a][41]; $BuyRule = $sellCoins[$a][43];
    $lowPricePurchaseEnabled = $sellCoins[$a][45]; $purchaseLimit = $sellCoins[$a][46]; $pctToPurchase = $sellCoins[$a][47]; $btcBuyAmountSell = $sellCoins[$a][48];
    $noOfPurchases = $sellCoins[$a][49]; $toMerge = $sellCoins[$a][44]; $orderDate = $sellCoins[$a][7];
    $noOfCoinSwapsThisWeek  = $sellCoins[$a][53]; $captureTrend = $sellCoins[$a][54];
    //$symbol = $sellCoins[$a][11];

    $price4Trend = $sellCoins[$a][37]; $price3Trend = $sellCoins[$a][38]; $lastPriceTrend = $sellCoins[$a][39];  $livePriceTrend = $sellCoins[$a][40];
    //$BuyRuleLength = strlen($orderNo - 20);
    //$BuyRuleLength = $BuyRuleLength-$BuyRuleLength-$BuyRuleLength;

    for($z = 0; $z < $sellRulesSize; $z++) {//Sell Rules
      $sellResultAry = [];
      $sellOutstanding = "";
      //Variables
      $BuyOrdersEnabled = $sellRules[$z][4]; $BuyOrdersTop = $sellRules[$z][5]; $BuyOrdersBtm = $sellRules[$z][6];
      $MarketCapEnabled = $sellRules[$z][7]; $MarketCapTop = $sellRules[$z][8];$MarketCapBtm= $sellRules[$z][9];
      $Hr1ChangeEnabled = $sellRules[$z][10]; $Hr1ChangeTop = $sellRules[$z][11]; $Hr1ChangeBtm = $sellRules[$z][12];
      $Hr24ChangeEnabled = $sellRules[$z][13]; $Hr24ChangeTop = $sellRules[$z][14]; $Hr24ChangeBtm = $sellRules[$z][15];
      $D7ChangeEnabled = $sellRules[$z][16]; $D7ChangeTop = $sellRules[$z][17]; $D7ChangeBtm = $sellRules[$z][18];
      $ProfitPctEnabled = $sellRules[$z][19]; $ProfitPctTop_Sell = $sellRules[$z][20];  $ProfitPctBtm_Sell = $sellRules[$z][21];
      $CoinPriceEnabled = $sellRules[$z][22]; $CoinPriceTop = $sellRules[$z][23]; $CoinPriceBtm = $sellRules[$z][24];
      $SellOrdersEnabled = $sellRules[$z][25]; $SellOrdersTop = $sellRules[$z][26]; $SellOrdersBtm = $sellRules[$z][27];
      $VolumeEnabled = $sellRules[$z][28]; $VolumeTop = $sellRules[$z][29]; $VolumeBtm = $sellRules[$z][30];
      $SellCoin = $sellRules[$z][2]; $SendEmail = $sellRules[$z][3];
      $Email = $sellRules[$z][31]; $UserName = $sellRules[$z][32]; $APIKey = $sellRules[$z][33];
      $coinPricePatternSellEnabled = $sellRules[$z][44]; $coinPricePatternSell = $sellRules[$z][45]; $autoSellCoinEnabled = $sellRules[$z][46];
      $fallsInPrice = $sellRules[$z][47]; $mergeCoinEnabled = $sellRules[$z][53]; $coinModeRule = $sellRules[$z][54];
      //$profit = ((($amount*$liveCoinPrice)-($amount*$cost))/($amount*$cost))*100;
      //$APISecret = $sellRules[$z][34];
      $userID = $sellRules[$z][1]; $ruleIDSell = $sellRules[$z][0];
      $sellCoinOffsetEnabled = $sellRules[$z][35]; $sellCoinOffsetPct = $sellRules[$z][36];
      $sellPriceMinEnabled = $sellRules[$z][37]; $sellPriceMin = $sellRules[$z][38];
      $KEKSell = $sellRules[$z][40];
      $priceTrendEnabled = $sellRules[$z][41]; $newSellPattern = $sellRules[$z][42];
      $limitToBuyRule = $sellRules[$z][43];
      $sellAllCoinsEnabled = $sellRules[$z][48]; $sellAllCoinsPct = $sellRules[$z][49];
      $profitNum = findUserProfit($userProfit,$userID);
      $coinSwapEnabled = $sellRules[$z][50]; $coinSwapAmount = $sellRules[$z][51]; $noOfCoinSwapsPerWeek = $sellRules[$z][52];
      //echo "<BR> SellAllCoinsEnabled: $sellAllCoinsEnabled SellAllCoinsPct: $sellAllCoinsPct ProfitNum: $profitNum";
      if ($sellAllCoinsEnabled == 1 and $profitNum <= $sellAllCoinsPct){assignNewSellID($transactionID, 25);}
      if ($limitToBuyRule == "ALL"){ $limitToBuyRuleEnabled = 0;}else{$limitToBuyRuleEnabled = 1;}
      if ($fixSellRule != "ALL" && (int)$fixSellRule != $ruleIDSell){ continue;}
      if (!Empty($KEKSell)){ $apisecret = Decrypt($KEKSell,$sellRules[$z][34]);}
      $LiveBTCPrice = number_format((float)(bittrexCoinPrice($apikey, $apisecret,'USD','BTC',$apiVersion)), 8, '.', '');
      $limitToCoinSell = $sellRules[$z][39];
      $buyPrice = ($cost * $amount);
      $sellPrice = ($LiveCoinPrice * $amount);
      $fee = (($LiveCoinPrice * $amount)/100)*0.25;
      $profit = ((($sellPrice-$fee)-$buyPrice)/$buyPrice)*100;
      if ($captureTrend == 0 and $profit >= 0.5){
        //Capture 1Hr / 24Hr and 7D trend
        if ($coinModeRule > 0){
            //Update Coin ModeRule
            //updateBuyTrend($coinID, $transactionID, 'CoinMode', $BuyRule);
        }else{
            //Update Buy Rule
            //updateBuyTrend($coinID, $transactionID, 'Rule', $ruleIDSell);
        }
      }
      //echo "<BR> RULE: $ruleIDSell Coin: $coin FixSellRule: $fixSellRule Profit: $profit";
      //echo "<BR> SellCOINOFFSET Enabled: $sellCoinOffsetEnabled  - SellCoinOffsetPct: $sellCoinOffsetPct";
      if ($userID != $sellCoinsUserID){ continue; }
      if ($limitToCoinSell != "ALL" && $coin != $limitToCoinSell) { continue;}
      //$limitToBuyRuleTest = limitToBuyRule($BuyRule,$limitToBuyRule,$limitToBuyRuleEnabled);
      //Echo "Limit to Buy Rule : $limitToBuyRuleTest | $BuyRule | $limitToBuyRule | $limitToBuyRuleEnabled";
      //if ($limitToBuyRule != "ALL" && $limitToBuyRuleTest == False){echo "<BR>EXIT: Limited to Buy rule $limitToBuyRule : $BuyRule"; continue;}else{ Echo "<BR>BUY RULE CORRECT";}
      //Echo "<BR> Start of TEST!";
      $current_date = date('Y-m-d H:i');
      $threeWeeksAgoDate = date("Y-m-d H:i",strtotime("-3 week", strtotime($current_date)));
      //echo "<BR>COIN Swap: $coinSwapEnabled $noOfCoinSwapsPerWeek $noOfCoinSwapsThisWeek";
      if ($coinSwapEnabled == 1 and $noOfCoinSwapsPerWeek > $noOfCoinSwapsThisWeek){
        $now = time();
        $your_date = strtotime($orderDate);
        $datediff = $now - $your_date;
        $daysSinceCoinPurchase = round($datediff / (60 * 60 * 24));
        //echo "<BR>COIN Swap: $profit $orderDate $threeWeeksAgoDate $daysSinceCoinPurchase";
        if ($profit < -4 and $daysSinceCoinPurchase > 21){
          //lookup if any Coin in Buy Mode currently
          $coinSwapBuyCoinID = coinSwapBuyModeLookup($coinID);
          $coinSwapBuyCoinIDSize = count($coinSwapBuyCoinID);
          echo "<BR> COIN SWAP: No of coins in Buy Mode: $coinID | $coinSwapBuyCoinIDSize";
          if ($coinSwapBuyCoinIDSize > 0){
            //CoinSwap
            echo "<BR>coinSwapSell($LiveCoinPrice, $transactionID,$coinID,$BuyRule,$coinSwapAmount);";
            coinSwapSell($LiveCoinPrice, $transactionID,$coinID,$BuyRule,$coinSwapAmount);
          }
        }
      }
      $GLOBALS['allDisabled'] = false;
      $sTest12 = false;

      //Echo "MarketCap $marketCapTop,$marketCapBtm,$marketCapbyPct,$marketCapEnable <BR>";
      $sTest1 = sellWithScore($MarketCapTop,$MarketCapBtm,$MarketCapPctChange,$MarketCapEnabled);
      $sellResultAry[] = Array($sTest1, "Market Cap $coin", $MarketCapPctChange);
      //Echo "<BR> sTEST1: $sTest1";
      $sTest2 = sellWithScore($VolumeTop,$VolumeBtm,$VolumePctChange,$VolumeEnabled);
      $sellResultAry[] = Array($sTest2, "Volume $coin", $VolumePctChange);
      $sTest3 = sellWithScore($SellOrdersTop,$SellOrdersBtm,$SellOrdersPctChange,$SellOrdersEnabled);
      $sellResultAry[] = Array($sTest3, "Sell Orders $coin", $SellOrdersPctChange);
      $sTest4 = sellWithScore($Hr1ChangeTop,$Hr1ChangeBtm,$Hr1ChangePctChange,$Hr1ChangeEnabled);
      $sellResultAry[] = Array($sTest4, "1 Hour Price Change $coin", $Hr1ChangePctChange);
      $sTest5 = sellWithScore($Hr24ChangeTop,$Hr24ChangeBtm,$Hr24ChangePctChange,$Hr24ChangeEnabled);
      $sellResultAry[] = Array($sTest5, "24 Hour Price Change $coin", $Hr24ChangePctChange);
      $sTest6 = sellWithScore($D7ChangeTop,$D7ChangeBtm,$D7ChangePctChange,$D7ChangeEnabled);
      $sellResultAry[] = Array($sTest6, "7 Day Price Change $coin", $D7ChangePctChange);
      if ($priceTrendEnabled){
          $sTest7 = newBuywithPattern($price4Trend.$price3Trend.$lastPriceTrend.$livePriceTrend,$coinPricePatternList,$priceTrendEnabled,$ruleIDSell,1);
      }else{ $sTest7 = True;}

      $sellResultAry[] = Array($sTest7, "Price Trend Pattern $coin", $price4Trend.$price3Trend.$lastPriceTrend.$livePriceTrend);
      $sTest8 = sellWithMin($sellPriceMinEnabled,$sellPriceMin,$LiveCoinPrice,$LiveBTCPrice);
      $sellResultAry[] = Array($sTest8, "Minimum Price $coin", $LiveCoinPrice);
      $sTest9 = sellWithScore($ProfitPctTop_Sell,$ProfitPctBtm_Sell,$profit,$ProfitPctEnabled);
      $sellResultAry[] = Array($sTest9, "Profit Percentage $coin", $profit);
      //Echo "<BR>TEST Sell Price: $CoinPriceTop | $CoinPriceBtm | $CoinPricePctChange | $CoinPriceEnabled";
      $sTest10 = sellWithScore($CoinPriceTop,$CoinPriceBtm,$CoinPricePctChange,$CoinPriceEnabled);
      $sellResultAry[] = Array($sTest10, "Minimum Sell Price $coin", $CoinPricePctChange);
      $sTest11 = coinMatchPattern($coinPriceMatch,$LiveCoinPrice,$coin,1,$coinPricePatternSellEnabled,$ruleIDSell,1);
      $sellResultAry[] = Array($sTest11, "Coin Price Match $coin", $LiveCoinPrice);
      $sTest13 = autoSellMain($LiveCoinPrice,$autoBuyPrice,$autoSellCoinEnabled,$coinID);
      //Echo "<BR> sTEST13: $sTest13";
      $sellResultAry[] = Array($sTest12, "Auto Sell $coin", $LiveCoinPrice);
      $sTest12 = $GLOBALS['allDisabled'];
      //Echo "<BR> TEST: sellWithScore($ProfitPctTop_Sell,$ProfitPctBtm_Sell,$profit,$ProfitPctEnabled);";
      //$sellOutstanding = getOutStandingBuy($sellResultAry);
      $totalScore_Sell = $sTest1+$sTest2+$sTest3+$sTest4+$sTest5+$sTest6+$sTest7+$sTest8+$sTest9+$sTest10+$sTest11+$sTest12+$sTest13;
      Echo "<BR> UserID: $userID | RuleID: $ruleIDSell | Coin : $coin | 1:$sTest1  2:$sTest2  3:$sTest3  4:$sTest4  5:$sTest5  6:$sTest6  7:$sTest7  8:$sTest8  9:$sTest9  10:$sTest10  11:$sTest11  12:$sTest12 13:$sTest13 TOTAL:$totalScore_Sell / 13, PROFIT:$profit";
      if ($totalScore_Sell >= 12){
        $sellOutstanding = getOutStandingBuy($sellResultAry);
        logAction("UserID: $userID | RuleID: $ruleIDSell | Coin : $coin | 1:  $sTest1  2:  $sTest2  3:  $sTest3  4:  $sTest4  5:  $sTest5  6:  $sTest6  7:  $sTest7  8:  $sTest8  9:  $sTest9  10:  $sTest10  11:  $sTest11  12:  $sTest12 13: $sTest13 TOTAL:  $totalScore_Sell / 13, PROFIT: $profit $sellOutstanding","SellScore", $logToFileSetting);
        logToSQL("SellCoins", "RuleID: $ruleIDSell | Coin : $coin | TOTAL: $totalScore_Sell $sellOutstanding", $userID, $logToSQLSetting);
      }


      if ($sTest1 == True && $sTest2 == True && $sTest3 == True && $sTest4 == True && $sTest5 == True && $sTest6 == True && $sTest7 == True && $sTest8 == True && $sTest9 == True && $sTest10 == True
      && $sTest11 == True && $sTest12 == True && $sTest13 == True){
        $date = date("Y-m-d H:i:s", time());
        echo "<BR>Sell Coins: $APIKey, $APISecret,$coin, $Email, $userID, 0,$date, $BaseCurrency,$SendEmail,$SellCoin, _.$ruleIDSell,$UserName,$orderNo,$amount,$cost,$transactionID,$coinID<BR>";
        //sellCoins($apikey, $apisecret, $coin, $email, $userID, $score, $date,$baseCurrency, $sendEmail, $sellCoin, $ruleID,$userName, $orderNo,$amount,$cost,$transactionID,$coinID){
        //sellCoins($APIKey, $APISecret,$coin, $Email, $userID, 0,$date, $BaseCurrency,$SendEmail,$SellCoin, $ruleIDSell,$UserName,$orderNo,$amount,$cost,$transactionID,$coinID,$sellCoinOffsetEnabled,$sellCoinOffsetPct,$LiveCoinPrice);
        newTrackingSellCoins($LiveCoinPrice,$userID, $transactionID,$SellCoin, $SendEmail,$sellCoinOffsetEnabled,$sellCoinOffsetPct,$fallsInPrice);
        setTransactionPending($transactionID);
        logAction("sellCoins($APIKey, $APISecret,$coin, $Email, $userID, 0,$date, $BaseCurrency,$SendEmail,$SellCoin, $ruleIDSell,$UserName,$orderNo,$amount,$cost,$transactionID,$coinID,$sellCoinOffsetEnabled,$sellCoinOffsetPct,$LiveCoinPrice)",'BuySell', $logToFileSetting);
        logAction("UserID: $userID | Coin : $coin | 1: $sTest1 2: $sTest2 3: $sTest3 4: $sTest4 5: $sTest5 6: $sTest6 7: $sTest7 8: $sTest8 9: $sTest9 10: $sTest10 11: $sTest11",'BuySell', $logToFileSetting);
        logToSQL("SellCoins", "newTrackingSellCoins($LiveCoinPrice,$userID, $transactionID,$SellCoin, $SendEmail,$sellCoinOffsetEnabled,$sellCoinOffsetPct,$fallsInPrice);", $userID, 1);
        logToSQL("SellCoins", "setTransactionPending($transactionID);", $userID, 1);
        //break;
        $to_time = date("Y-m-d H:i:s", time());
        $from_time = strtotime($orderDate);
        $holdingMins =  round(abs($to_time - $from_time) / 60,2);
        logHoldingTimeToSQL($coinID, $holdingMins);
        //addSellRuletoSQL()
      }
      echo "<BR> NEXT RULE <BR>";
    } //Sell Rules
    $BTCBalance = bittrexbalance($apikey, $apisecret,$baseCurrency, $apiVersion);
    $buyPrice = ($cost * $amount);
    $sellPrice = ($LiveCoinPrice * $amount);
    $fee = (($LiveCoinPrice * $amount)/100)*0.25;
    $profit = ((($sellPrice-$fee)-$buyPrice)/$buyPrice)*100;
    //echo "<BR> TESTING: Profit $profit PctToPurchase $pctToPurchase LowPricePurchaseEnabled $lowPricePurchaseEnabled NoOfPurchases $noOfPurchases PurchaseLimit $purchaseLimit ToMerge $toMerge";
    if ($profit <= $pctToPurchase  && $BTCBalance >= 20 && $lowPricePurchaseEnabled == 1 && $noOfPurchases < $purchaseLimit && $toMerge == 0 && $mergeCoinEnabled == 1){
      //Buy Coin
      addTrackingCoin($coinID, $LiveCoinPrice, $userID, $baseCurrency, $SendEmail, 1, $btcBuyAmountSell, 999991, 0, 0, 1, 90, $fixSellRule,1,$noOfPurchases,1,'Buy',$LiveCoinPrice);
      echo "<BR> TEST New Buy Coin addTrackingCoin($coinID, $LiveCoinPrice, $userID, $baseCurrency, $SendEmail, 1, $btcBuyAmountSell, 999991, 0, 0, 1, 90, $fixSellRule, 1);";
      logToSQL("TrackingCoins", "addTrackingCoin($coinID, $LiveCoinPrice, $userID, $baseCurrency, $SendEmail, 1, $btcBuyAmountSell, 999991, 0, 0, 1, 90, $fixSellRule,1,$noOfPurchases);", $userID, $logToSQLSetting);
      //Update ToMerge
      updateTrackingCoinToMerge($transactionID,$noOfPurchases);
      logToSQL("TrackingCoins", "updateTrackingCoinToMerge($transactionID,$noOfPurchases);", $userID, $logToSQLSetting);
    }
  }//Sell Coin Loop
  //echo "</blockquote>";
  echo "</blockquote>";
    echo "<BR> CHECK BITTREX!! ";
    //logAction("Check Bittrex Orders Start", 'BuySellTiming');
  echo "<blockquote>";
  $BittrexReqs = getBittrexRequests();
  $BittrexReqsSize = count($BittrexReqs);
  sleep(1);
  for($b = 0; $b < $BittrexReqsSize; $b++) {
    //Variables
    $type = $BittrexReqs[$b][0]; $uuid = $BittrexReqs[$b][1]; $date = $BittrexReqs[$b][2]; $status = $BittrexReqs[$b][4];   $bitPrice = $BittrexReqs[$b][5]; $userName = $BittrexReqs[$b][6];
    $apiKey = $BittrexReqs[$b][7]; $apiSecret = $BittrexReqs[$b][8]; $coin = $BittrexReqs[$b][9];$amount = $BittrexReqs[$b][10];$cost = $BittrexReqs[$b][11];$userID = $BittrexReqs[$b][12];
    $email = $BittrexReqs[$b][13]; $orderNo = $BittrexReqs[$b][14];$transactionID = $BittrexReqs[$b][15]; $totalScore = 0; $baseCurrency = $BittrexReqs[$b][16]; $ruleIDBTBuy = $BittrexReqs[$b][17];
    $sendEmail = 1; $daysOutstanding = $BittrexReqs[$b][18]; $timeSinceAction = $BittrexReqs[$b][19]; $coinID = $BittrexReqs[$b][20]; $ruleIDBTSell = $BittrexReqs[$b][21]; $orderDate = $BittrexReqs[$b][28];
    $liveCoinPriceBit = $BittrexReqs[$b][22]; $buyCancelTime = substr($BittrexReqs[$b][23],0,strlen($BittrexReqs[$b][23])-1); $sellFlag = false;
    $coinModeRule = $BittrexReqs[$b][27]; $pctToSave = $BittrexReqs[$b][29];
    $KEK = $BittrexReqs[$b][25]; $Day7Change = $BittrexReqs[$b][26];
    if (!Empty($KEK)){$apiSecret = decrypt($KEK,$BittrexReqs[$b][8]);}
    $buyOrderCancelTime = $BittrexReqs[$b][24];
    if ($liveCoinPriceBit != 0 && $bitPrice != 0){$pctFromSale =  (($liveCoinPriceBit-$bitPrice)/$bitPrice)*100;}
    if ($liveCoinPriceBit != 0 && $cost != 0){$liveProfitPct = ($liveCoinPriceBit-$cost)/$cost*100;}
    echo "<BR> bittrexOrder($apiKey, $apiSecret, $uuid);";
    $resultOrd = bittrexOrder($apiKey, $apiSecret, $uuid, $apiVersion);
    if ($apiVersion == 1){
      $finalPrice = number_format((float)$resultOrd["result"]["PricePerUnit"], 8, '.', '');
      $orderQty = $resultOrd["result"]["Quantity"]; $orderQtyRemaining = $resultOrd["result"]["QuantityRemaining"];
      $orderIsOpen = $resultOrd["result"]["IsOpen"];
      $cancelInit = $resultOrd["result"]["CancelInitiated"];$status = $resultOrd["success"];
      $qtySold = $orderQty-$orderQtyRemaining;
    }else{
      $finalPrice = number_format((float)$resultOrd["result"]["PricePerUnit"], 8, '.', '');
      $orderQty = $resultOrd["quantity"];
      //$cancelInit = $resultOrd["result"]["CancelInitiated"];
      $qtySold = $resultOrd["fillQuantity"];

      $orderQtyRemaining = $orderQty-$qtySold;
      if ($resultOrd["status"] == 'OPEN'){$status = 1;$cancelInit = 1;$orderIsOpen = 1;}else{$status = 0; $cancelInit = 0;$orderIsOpen = 0;}

    }


    //if ($orderQtyRemaining=0){$orderIsOpen = false;}
    echo "<BR> ------COIN to Sell: ".$coin."-------- USER: ".$userName;
    echo "<BR> Buy Cancel Time: $buyCancelTime";
    echo "TIME SINCE ACTION: $timeSinceAction";
    Print_r("What is Happening? // BITREXTID = ".$uuid."<br>");
    echo "<BR> Result IS OPEN? : ".$orderIsOpen." // CANCEL initiated: ".$cancelInit;
    updateBittrexQuantityFilled($qtySold,$uuid);
    if ($qtySold <> 0){ logToSQL("Bittrex", "Quantity Updated to : $qtySold for OrderNo: $orderNo", $userID, $logToSQLSetting);}
    if ($status == 1){
      if ($type == "Buy" or $type == "SpreadBuy"){
        if ($orderIsOpen != 1 && $cancelInit != 1 && $orderQtyRemaining == 0){
          //sendtoSteven($transactionID,$orderQtyRemaining."_".$qtySold."_".$orderQty, $orderNo."_".$finalPrice."_".$liveCoinPriceBit, "BUY - OrderIsOpen != 1 & CancelInitiated != 1");
          if ($sendEmail){
            $subject = "Coin Purchase1: ".$coin;
            $from = 'Coin Purchase <purchase@investment-tracker.net>';
            sendEmail($email, $coin, $amount, $finalPrice, $orderNo, $totalScore, $subject,$userName,$from);
          }
          bittrexBuyComplete($uuid, $transactionID, $finalPrice); //add buy price - $finalPrice
          //updateAmount $uuid  $resultOrd["result"]["Quantity"]
          updateSQLQuantity($uuid,$orderQty);
          logToSQL("Bittrex", "Order Complete for OrderNo: $orderNo Final Price: $finalPrice", $userID, $logToSQLSetting);
          //addBuyRuletoSQL($transactionID, $ruleIDBTBuy);
          echo "<BR>Buy Order COMPLETE!";
          setCustomisedSellRule($ruleIDBTBuy,$coinID);
          updateBuyAmount($transactionID,$resultOrd['quantity']);
          if ($type == 'SpreadBuy'){updateToSpreadSell($transactionID);}
          logToSQL("Bittrex", "setCustomisedSellRule($ruleIDBTBuy,$coinID);", $userID, 1);
          //if ($type == "SpreadBuy"){ updateSpreadSell();}
          UpdateProfit();
          continue;
        }
        //if ( substr($timeSinceAction,0,4) == $buyCancelTime){
        if ( $buyOrderCancelTime < date("Y-m-d H:i:s", time()) && $buyOrderCancelTime != '0000-00-00 00:00:00'){
          echo "<BR>CANCEL time exceeded! CANCELLING!";
          if ($orderQty == $orderQtyRemaining){
             $cancelRslt = bittrexCancel($apiKey,$apiSecret,$uuid,$apiVersion);
             if ($cancelRslt == 1){
               bittrexBuyCancel($uuid, $transactionID);

               logToSQL("Bittrex", "Order time exceeded for OrderNo: $orderNo Cancel order completed", $userID, $logToSQLSetting);
             }else{
               logAction("bittrexCancelBuyOrder: ".$cancelRslt, 'Bittrex', $logToFileSetting);
               logToSQL("Bittrex", "Order time exceeded for OrderNo: $orderNo Cancel order Error: $cancelRslt", $userID, $logToSQLSetting);
             }
          }else{
            $result = bittrexCancel($apiKey,$apiSecret,$uuid,$apiVersion);
            if ($result == 1){
              bittrexUpdateBuyQty($transactionID, $orderQty-$orderQtyRemaining);
              logToSQL("Bittrex", "Order time exceeded for OrderNo: $orderNo Order cancelled and new Order Created", $userID, $logToSQLSetting);
              if ($sendEmail){
                $subject = "Coin Purchase1: ".$coin;
                $from = 'Coin Purchase <purchase@investment-tracker.net>';
                sendEmail($email, $coin, $amount, $cost, $orderNo, $totalScore, $subject,$userName,$from);
              }
              bittrexBuyComplete($uuid, $transactionID, $finalPrice); //add buy price - $finalPrice
              //addBuyRuletoSQL($transactionID, $ruleIDBTBuy);
            }else{ logAction("bittrexCancelBuyOrder: ".$result, 'Bittrex', $logToFileSetting);}
          }
          addUSDTBalance('USDT',$amount*$finalPrice,$finalPrice,$userID);
          continue;
        }
      }elseif ($type == "Sell" or $type == "SpreadSell"){ // $type Sell
        if ($orderIsOpen != 1 && $cancelInit != 1 && $orderQtyRemaining == 0){
          echo "<BR>SELL Order COMPLETE!";
            $profitPct = ($finalPrice-$cost)/$cost*100;
            $sellPrice = ($finalPrice*$amount);
            $buyPrice = $cost*$amount;
            $fee = (($sellPrice)/100)*0.25;
            $profit = number_format((float)($sellPrice-$buyPrice)-$fee, 8, '.', '');
            //sendtoSteven($transactionID,$orderQtyRemaining."_".$qtySold."_".$orderQty, $orderNo."_".$finalPrice."_".$liveCoinPriceBit, "SELL - Order Is Open != 1 & CancelInitiated != 1");
            if ($sendEmail){
              $subject = "Coin Sale: ".$coin." RuleID:".$ruleIDBTSell;
              $from = 'Coin Sale <sale@investment-tracker.net>';
              sendSellEmail($email, $coin, $amount, $finalPrice, $orderNo, $totalScore,$profitPct,$profit,$subject,$userName,$from);
            }
            //if ($type == "CoinSwapSell"){
              //update transaction to new Coin ID and amount
            //  $coinSwapBuyCoinID = coinSwapBuyModeLookup();
              //Initiate buy


            //}else{
              bittrexSellComplete($uuid, $transactionID, $finalPrice); //add sell price - $finalPrice
              extendPctToBuy($coinID,$userID);
              $allocationType = 'Standard';
              if ($type == 'SpreadSell'){ $allocationType = 'SpreadBet';}elseif ($coinModeRule >0){$allocationType = 'CoinMode';}
              $pctToSave = $pctToSave / 100;
              addProfitToAllocation($userID, $profit,$allocationType, $pctToSave, $coinID);
              logToSQL("Bittrex", "Sell Order Complete for OrderNo: $orderNo Final Price: $finalPrice", $userID, $logToSQLSetting);
              if (is_null($coinModeRule)){
                //Update Buy Rule
                $buyTrendPct = updateBuyTrendHistory($coinID,$orderDate);
                $Hr1Trnd = $buyTrendPct[0][0]; $Hr24Trnd = $buyTrendPct[0][1]; $d7Trnd = $buyTrendPct[0][2];
                logToSQL("updateBuyTrend", "updateBuyTrend($coinID, $transactionID, Rule, $ruleIDBTSell, $Hr1Trnd,$Hr24Trnd,$d7Trnd);", $userID, 1);
                updateBuyTrend($coinID, $transactionID, 'Rule', $ruleIDBTSell, $Hr1Trnd,$Hr24Trnd,$d7Trnd);
              }else{
                //Update Coin ModeRule
                $buyTrendPct = updateBuyTrendHistory($coinID,$orderDate);
                $Hr1Trnd = $buyTrendPct[0][0]; $Hr24Trnd = $buyTrendPct[0][1]; $d7Trnd = $buyTrendPct[0][2];
                logToSQL("updateBuyTrend", "updateBuyTrend($coinID, $transactionID, CoinMode, $ruleIDBTBuy, $Hr1Trnd,$Hr24Trnd,$d7Trnd);", $userID, 1);
                updateBuyTrend($coinID, $transactionID, 'CoinMode', $ruleIDBTBuy, $Hr1Trnd,$Hr24Trnd,$d7Trnd);
              }
              UpdateProfit();


            //addSellRuletoSQL($transactionID, $ruleIDBTSell);
            continue;
        }
        if ($daysOutstanding <= -28){
          echo "<BR>days from sale! $daysOutstanding CANCELLING!";
          if ($orderQtyRemaining == $orderQty){
            //complete sell update amount
            $cancelRslt = bittrexCancel($apiKey,$apiSecret,$uuid,$apiVersion);
            if ($cancelRslt == 1){
              bittrexSellCancel($uuid, $transactionID);
              logToSQL("Bittrex", "Sell Order over 28 Days. Cancelling OrderNo: $orderNo", $userID, $logToSQLSetting);
              continue;
            }else{
              logAction("bittrexCancelSellOrder: ".$cancelRslt, 'Bittrex', $logToFileSetting);
              logToSQL("Bittrex", "Sell Order over 28 Days. Error cancelling OrderNo: $orderNo : $cancelRslt", $userID, $logToSQLSetting);
            }
          }else{
             $result = bittrexCancel($apiKey,$apiSecret,$uuid,$apiVersion);
             if ($apiVersion == 1){ $resultStatus = $result;}
             else{ if ($result == 'CLOSED'){$resultStatus = 1;}else{$resultStatus =0;}}
             if ($resultStatus == 1){
               $newOrderNo = "ORD".$coin.date("YmdHis", time()).$ruleIDBTSell;
               //sendtoSteven($transactionID,$orderQtyRemaining."_".$qtySold."_".$orderQty, $newOrderNo."_".$orderNo, "SELL - Greater 28 days");
               bittrexCopyTransNewAmount($transactionID,$qtySold,$orderQtyRemaining,$newOrderNo);
               bittrexSellComplete($uuid, $transactionID, $finalPrice);
               logToSQL("Bittrex", "Sell Order over 28 Days. Cancelling OrderNo: $orderNo | Creating new Transaction", $userID, $logToSQLSetting);
               //Update QTY
               //bittrexUpdateSellQty($transactionID,$qtySold);
               //bittrexSellCancel($uuid, $transactionID);

               if ($sendEmail){
                 $subject = "Coin Sale: ".$coin." RuleID:".$ruleIDBTSell." Qty: ".$orderQty." : ".$orderQtyRemaining;
                 $from = 'Coin Sale <sale@investment-tracker.net>';
                 sendSellEmail($email, $coin, $orderQty-$orderQtyRemaining, $finalPrice, $orderNo, $totalScore,$profitPct,$profit,$subject,$userName,$from);
               }
               continue;
             }else{
               logAction("bittrexCancelSellOrder: ".$result, 'Bittrex', $logToFileSetting);
               logToSQL("Bittrex", "Sell Order over 28 Days. Error cancelling OrderNo: $orderNo : $result", $userID, $logToSQLSetting);
             }
          }
          subUSDTBalance('USDT',$amount*$finalPrice,$finalPrice,$userID);
        }
        if ($pctFromSale <= -3 or $pctFromSale >= 4){
          if ($type == 'SpreadSell') { continue;}
          echo "<BR>% from sale! $pctFromSale CANCELLING!";
          if ($orderQtyRemaining == $orderQty){
            $cancelRslt = bittrexCancel($apiKey,$apiSecret,$uuid,$apiVersion);
            if($apiVersion == 1){ $canResStatus = $cancelRslt;}
            else{ if ($cancelRslt == 'CLOSED'){$canResStatus = 1;}else{$canResStatus =0;}}
            if ($canResStatus == 1){
              bittrexSellCancel($uuid, $transactionID);
              logToSQL("Bittrex", "Sell Order 3% Less or 4% above. Cancelling OrderNo: $orderNo", $userID, $logToSQLSetting);
              continue;
            }else{
              logAction("bittrexCancelSellOrder: ".$result, 'Bittrex', $logToFileSetting);
              logToSQL("Bittrex", "Sell Order 3% Less or 4% above. Error cancelling OrderNo: $orderNo : $result", $userID, $logToSQLSetting);
            }
          }else{
            $canResult = bittrexCancel($apiKey,$apiSecret,$uuid,$apiVersion);
            if($apiVersion == 1){ $newCanResStatus = $canResult;}
            else{ if ($canResult == 'CLOSED'){$newCanResStatus = 1;}else{$newCanResStatus =0;}}
            if ($newCanResStatus == 1){
              $newOrderNo = "ORD".$coin.date("YmdHis", time()).$ruleIDBTSell;
              //sendtoSteven($transactionID,"QTYRemaining: ".$orderQtyRemaining."_QTYSold: ".$qtySold."_OrderQTY: ".$orderQty."_UUID: ".$uuid, "NewOrderNo: ".$newOrderNo."_OrderNo: ".$orderNo, "SELL - Less -2 Greater 2.5");
              bittrexCopyTransNewAmount($transactionID,$qtySold,$orderQtyRemaining,$newOrderNo);
              bittrexSellComplete($uuid, $transactionID, $finalPrice);
              logToSQL("Bittrex", "Sell Order 3% Less or 4% above. Cancelling OrderNo: $orderNo | Creating new Transaction", $userID, $logToSQLSetting);
              //Update QTY
              //bittrexUpdateSellQty($transactionID,$qtySold);
              //bittrexSellCancel($uuid, $transactionID);

              if ($sendEmail){
                $subject = "Coin Sale2: ".$coin." RuleID:".$ruleIDBTSell." Qty: ".$orderQty." : ".$orderQtyRemaining;
                $from = 'Coin Sale <sale@investment-tracker.net>';
                //$debug = "$uuid : $transactionID - $orderQtyRemaining + $qtySold / $pctFromSale ! $liveProfitPct";
                sendSellEmail($email, $coin, $orderQty-$orderQtyRemaining, $finalPrice, $orderNo, $totalScore,$profitPct,$profit,$subject,$userName,$from);
              }
              continue;
            }else{
              logAction("bittrexCancelSellOrder: ".$result, 'Bittrex', $logToFileSetting);
              logToSQL("Bittrex", "Sell Order 3% Less or 4% above. Error cancelling OrderNo: $orderNo : $result", $userID, $logToSQLSetting);
            }
          }
          subUSDTBalance('USDT',$amount*$finalPrice,$finalPrice,$userID);
        }
      } //end $type Buy Sell
    }else{
      logAction("bittrexCheckOrder: ".$status, 'Bittrex', $logToFileSetting);
      logToSQL("Bittrex", "Check OrderNo: $orderNo Success:".$status, $userID, $logToSQLSetting);
    }//end bittrex order check
    echo "<br> Profit Pct $liveProfitPct Live Coin Price: $liveCoinPriceBit cost $cost";
    echo "<br>Time Since Action ".substr($timeSinceAction,0,4);

    echo "<BR> ORDERQTY: $orderQty - OrderQTYREMAINING: $orderQtyRemaining";
  }//Bittrex Loop

  $coinAlerts = getCoinAlerts();
  $coinAlertsLength = count($coinAlerts);
  echo "</blockquote>";
  echo "<BR> CHECK Alerts!! ";
  echo "<blockquote>";
  //logAction("Check Alerts Start", 'BuySellTiming');
  sleep(1);
  for($d = 0; $d < $coinAlertsLength; $d++) {
    $id = $coinAlerts[$d][0];
    $coinID = $coinAlerts[$d][1]; $action = $coinAlerts[$d][2]; $price  = $coinAlerts[$d][3]; $symbol  = $coinAlerts[$d][4];
    $userName  = $coinAlerts[$d][5]; $email  = $coinAlerts[$d][6]; $liveCoinPrice = $coinAlerts[$d][7]; $category = $coinAlerts[$d][8];
    $Live1HrChangeAlrt = $coinAlerts[$d][9]; $Live24HrChangeAlrt = $coinAlerts[$d][10]; $Live7DChangeAlrt = $coinAlerts[$d][11];
    $reocurring = $coinAlerts[$d][12]; $dateTimeSent = $coinAlerts[$d][13]; $liveSellOrderAlert = $coinAlerts[$d][14];
    $liveBuyOrderAlert = $coinAlerts[$d][15];$liveMarketCapAlert = $coinAlerts[$d][16];
    $userID = $coinAlerts[$d][17]; $livePricePct = $coinAlerts[$d][19];
    //$current_date = date('Y-m-d H:i');
    //$newTime = date("Y-m-d H:i",strtotime("-30 mins", strtotime($current_date)));
    //$dateFlag = ($newTime > $dateTimeSent);
    $minutes = $coinAlerts[$d][18];
    $returnFlag = False; $tempPrice = 0;
    //$newTimeAlrt = $dateTimeSent - $current_date ;
    Echo "<BR> Checking $symbol, $price, $action, $userName , $liveCoinPrice, $category, $dateTimeSent, $minutes, $reocurring, $Live1HrChangeAlrt";

    if ($category == "Price"){
      //Price
      //$returnFlag = returnAlert($price,$liveCoinPrice,$action);
      //if ($returnFlag){
      //  echo "<BR> $category Alert True. Sending Alert for $symbol $price $action";
      //  action_Alert($minutes,$email,$symbol,$price,$action,$userName,$category,$reocurring,$id, $logToFileSetting, $logToSQLSetting,$liveCoinPrice);
      //}
      $tempPrice = $liveCoinPrice;
    }elseif ($category == "Pct Price in 1 Hour"){
      //1Hr
      //$returnFlag = returnAlert($price,$Live1HrChangeAlrt,$action);
      //if ($returnFlag){
      //  echo "<BR> $category Alert True. Sending Alert for $symbol $price $action";
      //  action_Alert($minutes,$email,$symbol,$price,$action,$userName,$category,$reocurring,$id,$userID, $logToFileSetting, $logToSQLSetting,$Live1HrChangeAlrt);
      //}
      $tempPrice = $Live1HrChangeAlrt;
    }elseif ($category == "Market Cap Pct Change"){
      //MarketCap
      //$returnFlag = returnAlert($price,$liveMarketCapAlert,$action);
      //if ($returnFlag){
      //  echo "<BR> $category Alert True. Sending Alert for $symbol $price $action";
      //  action_Alert($minutes,$email,$symbol,$price,$action,$userName,$category,$reocurring,$id,$userID, $logToFileSetting, $logToSQLSetting,$liveMarketCapAlert);
      //}
      $tempPrice = $liveMarketCapAlert;
    }elseif ($category == "Live Price Pct Change"){
      //$returnFlag = returnAlert($price,$livePricePct,$action);
      //if ($returnFlag){
      //  echo "<BR> $category Alert True. Sending Alert for $symbol $price $action";
      //  action_Alert($minutes,$email,$symbol,$price,$action,$userName,$category,$reocurring,$id,$userID, $logToFileSetting, $logToSQLSetting,$livePricePct);
      //}
      $tempPrice = $livePricePct;
    }elseif ($category == "Pct Price in 24 Hours"){
      $tempPrice = $Live24HrChangeAlrt;
    }elseif ($category == "Pct Price in 7 Days"){
      //$returnFlag = returnAlert($price,$Live7DChangeAlrt,$action);
      //if ($returnFlag){
      //  echo "<BR> $category Alert True. Sending Alert for $symbol $price $action";
      //  action_Alert($minutes,$email,$symbol,$price,$action,$userName,$category,$reocurring,$id,$userID, $logToFileSetting, $logToSQLSetting,$Live7DChangeAlrt);
      //}
      $tempPrice = $Live7DChangeAlrt;
    }
    $returnFlag = returnAlert($price,$tempPrice,$action);
    if ($returnFlag){
      echo "<BR> $category Alert True. Sending Alert for $symbol $price $action $tempPrice";
      action_Alert($minutes,$email,$symbol,$price,$action,$userName,$category,$reocurring,$id,$userID, $logToFileSetting, $logToSQLSetting,$tempPrice);
    }

  }

    $marketAlerts = getMarketAlerts();
    $marketAlertsSize = count($marketAlerts);

    for ($q=0; $q<$marketAlertsSize; $q++){
      $userName  = $marketAlerts[$q][6];$email = $marketAlerts[$q][7];$userID = $marketAlerts[$q][5];
      $dateTimeSent = $marketAlerts[$q][8];
      $Live1HrChangeAlrt = $marketAlerts[$q][1];$Live24HrChangeAlrt = $marketAlerts[$q][2];$Live7DChangeAlrt = $marketAlerts[$q][3]; $liveCoinPrice = $marketAlerts[$q][0];$liveMarketCapAlert = $marketAlerts[$q][4];
      $category = $marketAlerts[$q][10];$price = $marketAlerts[$q][14];$action = $marketAlerts[$q][11];$reocurring = $marketAlerts[$q][9];
      $minutes = $marketAlerts[$q][12]; $id = $marketAlerts[$q][13];
      Echo "<BR> Checking Market Alerts $price, $action, $userName , $liveCoinPrice, $category, $dateTimeSent, $minutes, $reocurring, $Live1HrChangeAlrt";
      if ($category == "Price"){
        //Price
        $returnFlag = returnAlert($price,$liveCoinPrice,$action);
        if ($returnFlag){
          echo "<BR> $category Alert True. Sending Alert for $price $action";
          action_Market_Alert($minutes,$email,$price,$action,$userName,$category,$reocurring,$id,$userID, $logToFileSetting, $logToSQLSetting, $liveCoinPrice);
        }
      }elseif ($category == "Pct Price in 1 Hour"){
        //1Hr
        $returnFlag = returnAlert($price,$Live1HrChangeAlrt,$action);
        if ($returnFlag){
          echo "<BR> $category Alert True. Sending Alert for $price $action";
          action_Market_Alert($minutes,$email,$price,$action,$userName,$category,$reocurring,$id,$userID, $logToFileSetting, $logToSQLSetting, $Live1HrChangeAlrt);
        }
      }elseif ($category == "Market Cap Pct Change"){
        //MarketCap
        $returnFlag = returnAlert($price,$liveMarketCapAlert,$action);
        if ($returnFlag){
          echo "<BR> $category Alert True. Sending Alert for $price $action";
          action_Market_Alert($minutes,$email,$price,$action,$userName,$category,$reocurring,$id,$userID, $logToFileSetting, $logToSQLSetting, $liveMarketCapAlert);
        }
      }
  }
  $spread = getSpreadBetData();
  $spreadSize = count($spread);
  if ($spreadSize == 0){LogToSQL("SpreadBetBuy","ERROR : Empty record set for getSpreadBetData",3,1);}
  $noOfBuys = 2;
  echo "</blockquote>";
  echo "<BR> CHECK Spread Bet!! ";
  echo "<blockquote>";
  for ($y=0; $y<$spreadSize; $y++){
    $ID = $spread[$y][0];  $Hr1ChangePctChange = $spread[$y][4]; $Hr24ChangePctChange = $spread[$y][7];$d7ChangePctChange = $spread[$y][10];
    $APIKey = $spread[$y][24]; $APISecret = $spread[$y][25]; $KEK = $spread[$y][26]; $UserID = $spread[$y][27];$UserName = $spread[$y][29];
    $spreadBetTransID = $spread[$y][30]; $Email =  $spread[$y][28]; $pctofSixMonthHigh = $spread[$y][34]; $pctofAllTimeHigh = $spread[$y][35];
    $disableUntil  = $spread[$y][36];
    $Hr1BuyPrice = $spread[$y][31];
    $Hr24BuyPrice = $spread[$y][32];
    $D7BuyPrice = $spread[$y][33]; $userID = $spread[$y][37];
    Echo "<BR> Checking $ID | 1Hr: $Hr1ChangePctChange | 24Hr: $Hr24ChangePctChange | 7d: $d7ChangePctChange";
    if (!Empty($KEK)){$APISecret = decrypt($KEK,$spread[$y][25]);}
    if ($disableUntil > date("Y-m-d H:i:s", time())){ echo "<BR> EXIT: Disabled until: ".$disableUntil; continue;}
    if ($pctofSixMonthHigh > 90){echo "<BR> EXIT: SixMonthHigh: $pctofSixMonthHigh"; continue;}
    if ($pctofAllTimeHigh > 90){echo "<BR> EXIT: AllTimeMonthHigh: $pctofAllTimeHigh"; continue;}
    Echo "<BR>1) $Hr24ChangePctChange : $Hr24BuyPrice | $d7ChangePctChange : $D7BuyPrice | $Hr1ChangePctChange : $Hr1BuyPrice";
    if ($Hr24ChangePctChange <= $Hr24BuyPrice and $d7ChangePctChange <= $D7BuyPrice and $Hr1ChangePctChange >= $Hr1BuyPrice){
      $openCoins = getOpenSpreadCoins();
      $openCoinsSize = count($openCoins);

      for ($v=0; $v<$openCoinsSize; $v++){
        Echo "<BR> Checking getOpenSpreadCoins : $ID | ".$openCoins[$v][0];
        if ($openCoins[$v][0] == $ID AND $openCoins[$v][1] == $userID){ continue 2;}
      }
      //GetCoinData
      echo "<BR> getSpreadCoinData($spreadBetTransID); ";
      $spreadCoins = getSpreadCoinData($spreadBetTransID);
      $spreadCoinsSize = count($spreadCoins);
      Echo "<BR> Buy Spread Coins : $spreadCoinsSize | $spreadBetTransID | $spreadCoinsSize";
      //How much to buy
      $openCoins = checkOpenSpreadBet($UserID);
      $openCoinsSize = count($openCoins);
      $availableTrans = $noOfBuys - $openCoinsSize;
      if ($openCoinsSize < $noOfBuys and $availableTrans > 0){
        $spreadBetToBuy = getCoinAllocation($UserID);
        $buyPerCoin = $spreadBetToBuy[0][0]/($noOfBuys - $openCoinsSize);
        $BTCAmount =  $buyPerCoin/$spreadCoinsSize;
      //}elseif ($availableTrans == 0){
      //  $BTCAmount =  $spreadBetToBuy[0][0]/$spreadCoinsSize;
      }else{ continue;}
      LogToSQL("SpreadBetBuy","1)ID: $ID | $Hr24ChangePctChange : $Hr24BuyPrice | $d7ChangePctChange : $D7BuyPrice | $Hr1ChangePctChange : $Hr1BuyPrice;",3,1);
      LogToSQL("SpreadBetBuy","Buy Spread Coins : $spreadCoinsSize | $spreadBetTransID | $spreadCoinsSize | BTCAmount: $BTCAmount",3,1);
      for ($t=0; $t<$spreadCoinsSize; $t++){
        Echo "<BR> Purchasing Coin: $coinID | $t | $spreadCoinsSize";
        $coinID = $spreadCoins[$t][0];$symbol = $spreadCoins[$t][1]; $spreadBetRuleID = $spreadCoins[$t][41];
        $liveCoinPrice = $spreadCoins[$t][17];
        $date = date("Y-m-d H:i:s", time()); $SendEmail = 1; $BuyCoin = 1;$ruleIDBuy = 9999995;$CoinSellOffsetEnabled = 0; $CoinSellOffsetPct = 0;
        $buyType = 1; $timeToCancelBuyMins = 20; $SellRuleFixed = 9999995;$noOfPurchases = 0;

        //BuyCoins
        echo "<BR>buyCoins($APIKey, $APISecret,$symbol, $Email, $userID, $date, $baseCurrency,$SendEmail,$BuyCoin,$BTCAmount, $ruleIDBuy,$UserName,$coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed, 0, $noOfPurchases+1);";
        $checkBuy = buyCoins($APIKey, $APISecret,$symbol, $Email, $userID, $date, $baseCurrency,$SendEmail,$BuyCoin,$BTCAmount, $ruleIDBuy,$UserName,$coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed, 0, $noOfPurchases+1);
        addTrackingCoin($coinID, $LiveCoinPrice, $userID, $baseCurrency, $SendEmail, $BuyCoin, $BTCAmount, $ruleIDBuy, $CoinSellOffsetPct, $CoinSellOffsetEnabled, $buyType, $timeToCancelBuyMins, $SellRuleFixed,0,0,$risesInPrice,'SpreadBuy',$liveCoinPrice);
        LogToSQL("SpreadBetBuy","buyCoins($coinID)",3,1);
        //update Transaction to Spread
        updateTransToSpread($ID,$coinID,$UserID,$spreadBetTransID);
        LogToSQL("SpreadBetBuy","updateTransToSpread($ID,$coinID,$UserID,$spreadBetTransID);",3,1);
        updateSpreadBuy($ID);
        LogToSQL("SpreadBetBuy","updateSpreadBuy($ID);",3,1);
        //add new number in SpreadBetTransactions
        if ($t == $spreadCoinsSize-1 AND $spreadCoinsSize > 0){
          echo "<BR> newSpreadTransactionID($UserID,$spreadBetRuleID); | $t";
          newSpreadTransactionID($UserID,$spreadBetRuleID);
          LogToSQL("SpreadBetBuy","newSpreadTransactionID($UserID,$spreadBetRuleID);",3,1);
          UpdateProfit();
          LogToSQL("SpreadBetBuy","UpdateProfit();",3,1);
        }
        subUSDTBalance('USDT', $BTCAmount,$liveCoinPrice, $userID);
        LogToSQL("SpreadBetBuy","subUSDTBalance('USDT', $BTCAmount,$liveCoinPrice, $userID);",3,1);
      }
    }
  }


  $sellSpread = getSpreadBetSellData();
  $sellSpreadSize = count($sellSpread);
  echo "</blockquote>";
  echo "<BR> CHECK Sell Spread Bet!! ";
  echo "<blockquote>";
  for ($w=0; $w<$sellSpreadSize; $w++){
    $CoinPriceTot = $sellSpread[$w][3]; $TotAmount = $sellSpread[$w][4]; $LiveCoinPriceTot = $sellSpread[$w][15];
    $ID = $sellSpread[$w][0]; $APIKey = $sellSpread[$w][50]; $APISecret = $sellSpread[$w][51]; $KEK = $sellSpread[$w][52];
    $Email = $sellSpread[$w][53]; $userID = $sellSpread[$w][2]; $UserName = $sellSpread[$w][54]; $captureTrend = $sellSpread[$w][57];
    $purchasePrice = $sellSpread[$w][59];
    $currentPrice = $sellSpread[$w][60];
    $profit = $sellSpread[$w][58];
    $spreadBetPctProfitSell = $sellSpread[$w][55]; $spreadBetRuleID = $sellSpread[$w][56]; $orderDate = $sellSpread[$w][6];
    $profitPct = ($profit/$purchasePrice)*100;
    $hr1Pct = $sellSpread[$w][25];  $hr24Pct = $sellSpread[$w][28]; $d7Pct = $sellSpread[$w][31]; $baseCurrency_new = $sellSpread[$w][32];
    if (!Empty($KEK)){$APISecret = decrypt($KEK,$sellSpread[$w][51]);}
    //coinPriceHistorySpreadBet($ID,$LiveCoinPriceTot,$baseCurrency_new,date("Y-m-d H:i:s", time()),$hr1Pct,$hr24Pct,$d7Pct);
    echo "<BR> Checking $ID | $profitPct | $spreadBetPctProfitSell | TotPP: $CoinPriceTot | TotAm: $TotAmount | TotLive: $LiveCoinPriceTot | TotProfit: $profit";
    updateSpreadProfit($spreadBetRuleID,$profitPct);
    if ($captureTrend == 0 and $profitPct >= 0.5){
      //updateBuyTrend(0, 0, 'SpreadBet', $spreadBetRuleID);
    }
    if ($profitPct >= $spreadBetPctProfitSell){
      //get coin data
      echo "<BR> getSpreadCoinSellData($ID);";
      $spreadSellCoins = getSpreadCoinSellData($ID);
      $spreadSellCoinsSize = count($spreadSellCoins);
      echo "<BR> Sell Spread Coins | $spreadSellCoinsSize";
      for ($q=0; $q<$spreadSellCoinsSize; $q++){
        LogToSQL("SpreadBetSell","profitPct :$profitPct | spreadBetPctProfitSell: $spreadBetPctProfitSell | ID: $ID NoOfCoins:$spreadSellCoinsSize;",3,1);
        $coin = $spreadSellCoins[$q][11];  $BaseCurrency =  $spreadSellCoins[$q][36]; $TransactionID = $spreadSellCoins[$q][0];
        $CoinID = $spreadSellCoins[$q][2]; $OrderNo = $spreadSellCoins[$q][10]; $LiveCoinPrice = $spreadSellCoins[$q][19];
        $date = date("Y-m-d H:i:s", time()); $SendEmail = 1; $SellCoin = 1; $CoinSellOffsetEnabled = 0; $CoinSellOffsetPct = 0.0;
        $Amount = $spreadSellCoins[$q][5]; $CoinPrice = $spreadSellCoins[$q][4]; $FixSellRule = $spreadSellCoins[$q][42];
        $orderDate = $spreadSellCoins[$q][7]; $pctToSave = $spreadSellCoins[$q][55];
        $type = $spreadSellCoins[$q][1];
        echo "<BR> sellCoins($APIKey, $APISecret,$coin, $Email, $userID, 0,$date, $BaseCurrency,$SendEmail,$SellCoin, $FixSellRule,$UserName,$OrderNo,$Amount,$CoinPrice,$TransactionID,$CoinID,$CoinSellOffsetEnabled,$CoinSellOffsetPct,$LiveCoinPrice, $type);";
        LogToSQL("SpreadBetSell","sellCoins($TransactionID,$CoinID);",3,1);
        $checkSell = sellCoins($APIKey, $APISecret,$coin, $Email, $userID, 0,$date, $BaseCurrency,$SendEmail,$SellCoin, $FixSellRule,$UserName,$OrderNo,$Amount,$CoinPrice,$TransactionID,$CoinID,$CoinSellOffsetEnabled,$CoinSellOffsetPct,$LiveCoinPrice,$type);
        LogToSQL("SpreadBetTest1","newTrackingSellCoins($LiveCoinPrice,$userID, $transactionID,1, 1,0,0.0,2);",3,1);
        //newTrackingSellCoins($LiveCoinPrice,$userID, $transactionID,1, 1,0,0.0,2);
        LogToSQL("SpreadBetTest2","setTransactionPending($transactionID);",3,1);
        //setTransactionPending($transactionID);
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
        addProfitToAllocation($UserID, $profit, 'SpreadBet', $pctToSave,$CoinID);
        LogToSQL("SpreadBetSell","addProfitToAllocation($UserID, $profit, 'SpreadBet', $pctToSave,$CoinID);",3,1);
      }
    }
  }

  echo "</blockquote>";
  //logAction("Buy Sell Coins Sleep 10 ", 'BuySellTiming');
  sleep(15);
  $i = $i+1;
  $date = date("Y-m-d H:i:s", time());
  if (date("Y-m-d H:i", time()) >= $newTime){ $completeFlag = True;}
}//end While
logAction("Buy Sell Coins End $date : $i", 'BuySellTiming', $logToFileSetting);
//$to, $symbol, $amount, $cost, $orderNo, $score, $subject, $user, $from){
//sendEmail('stevenj1979@gmail.com',$i,0,$date,0,'BuySell Loop Finished', 'stevenj1979', 'Coin Purchase <purchase@investment-tracker.net>');
echo "<br>EndTime ".date("Y-m-d H:i:s", time());
?>
</html>
