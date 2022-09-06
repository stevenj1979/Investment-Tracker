<html>
<?php
require('includes/newConfig.php');
include_once ('/home/stevenj1979/SQLData.php');
include_once ('/home/stevenj1979/Encrypt.php');

$apikey=getAPIKey();
$apisecret=getAPISecret();
$logToSQLSetting = getLogToSQL();
//$GLOBALS['logToFileSetting']  = getLogToFile();
$GLOBALS['logToSQLSetting'] = getLogToSQL();
$GLOBALS['logToFileSetting'] = getLogToFile();



function runBuyCoins($coins,$userProfit,$marketProfit,$ruleProfit,$totalBTCSpent,$dailyBTCSpent,$baseMultiplier,$delayCoinPurchase,$buyRules,$coinPriceMatch,$coinPricePatternList,$coin1HrPatternList,$autoBuyPrice,$trackCounter,$buyCounter){
  $apiVersion = 3;
  $finalBool = False;
  $coinLength = Count($coins);
  $buyRulesSize = count($buyRules);
  for($x = 0; $x < $coinLength; $x++) {
    //variables
    $coinID = $coins[$x][0]; $symbol = $coins[$x][1]; $baseCurrency = $coins[$x][26];
    $BuyOrdersPctChange = $coins[$x][4]; $MarketCapPctChange = $coins[$x][7]; $Hr1ChangePctChange = $coins[$x][10];
    $Hr24ChangePctChange = $coins[$x][13]; $D7ChangePctChange = $coins[$x][16]; $CoinPricePctChange = $coins[$x][19];
    $SellOrdersPctChange = $coins[$x][22]; $VolumePctChange = $coins[$x][25];
    $price4Trend = $coins[$x][27]; $price3Trend = $coins[$x][28]; $lastPriceTrend = $coins[$x][29];  $livePriceTrend = $coins[$x][30];
    $newPriceTrend = $price4Trend.$price3Trend.$lastPriceTrend.$livePriceTrend;
    $LiveCoinPrice = $coins[$x][17]; $Hr1LivePriceChange = $coins[$x][31];$Hr1LastPriceChange = $coins[$x][32]; $Hr1PriceChange3 = $coins[$x][33];$Hr1PriceChange4 = $coins[$x][34];
    $new1HrPriceChange = $Hr1PriceChange4.$Hr1PriceChange3.$Hr1LastPriceChange.$Hr1LivePriceChange; $doNotBuy = $coins[$x][39];
    $priceDipHoursFlatTarget = $coins[$x][40]; $priceDipMinPrice = $coins[$x][41];

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
      $limitBuyTransactionsEnabled = $buyRules[$y][78]; $limitBuyTransactions = $buyRules[$y][79]; $overrideCoinAlloc = $buyRules[$y][80];
      $oneTimeBuy = $buyRules[$y][81]; $limitToBaseCurrency  = $buyRules[$y][82];
      $priceDipCoinFlatEnabled = $buyRules[$y][85]; $priceDipHours = $buyRules[$y][86]; $priceDipMinPriceEnabled = $buyRules[$y][84];
      $pctOverMinPrice = $buyRules[$y][87]; $finalPriceDipMinPrice = $priceDipMinPrice + (($priceDipMinPrice/100)*$pctOverMinPrice);
      if (!Empty($KEK)){$APISecret = decrypt($KEK,$buyRules[$y][31]);}

      $EnableDailyBTCLimit = $buyRules[$y][32]; $DailyBTCLimit = $buyRules[$y][33]; $EnableTotalBTCLimit = $buyRules[$y][34];
      $TotalBTCLimit= $buyRules[$y][35]; $userID = $buyRules[$y][0]; $ruleIDBuy = $buyRules[$y][36]; $CoinSellOffsetPct = $buyRules[$y][37];
      $CoinSellOffsetEnabled = $buyRules[$y][38];
      $priceTrendEnabled = $buyRules[$y][39]; $price4TrendTrgt = $buyRules[$y][40];$price3TrendTrgt = $buyRules[$y][41];$lastPriceTrendTrgt = $buyRules[$y][42];
      $livePriceTrendTrgt = $buyRules[$y][43]; $userActive = $buyRules[$y][44]; $disableUntil = $buyRules[$y][45]; $hoursDisableUntil = $buyRules[$y][83];
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
      $delayCoinPurchaseSize = 0;
      if (!empty($delayCoinPurchase)){
        $delayCoinPurchaseSize = count($delayCoinPurchase);
      }

      for ($b=0; $b<$delayCoinPurchaseSize; $b++){
        $delayCoinPurchaseUserID = $delayCoinPurchase[$b][2]; $delayCoinPurchaseCoinID = $delayCoinPurchase[$b][1];
        if ($delayCoinPurchaseUserID == $userID AND $delayCoinPurchaseCoinID == $coinID){
          echo "<BR>EXIT: Delay CoinID: $coinID! "; continue;
        }
      }
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

      if (isset($marketProfit[0][0])){
        if ($MarketDropStopEnabled == 1 and $marketProfit[0][0] <= $marketDropStopPct and $overrideDisableRule == 0){
          newLogToSQL("BuyCoins", "Market Profit Enbled: $MarketDropStopEnabled Pct: $marketDropStopPct current: ".$marketProfit[0][0]." | RuleID $ruleIDBuy", $userID,$GLOBALS['logToSQLSetting'],"MarketDropStop","RuleID:$ruleIDBuy CoinID:$coinID");
          pauseRule($ruleIDBuy,4, $userID);
          pauseTracking($userID);

        }elseif ($MarketDropStopEnabled == 1 and $marketProfit[0][1] >= 0.3 and $overrideDisableRule == 0){
          newLogToSQL("BuyCoins", "pauseRule($ruleIDBuy,0, $userID);| MarketProfit: ".$marketProfit[0][1], $userID,$GLOBALS['logToSQLSetting'],"MarketDropStart","RuleID:$ruleIDBuy CoinID:$coinID");
          pauseRule($ruleIDBuy,0, $userID);
        }
      }
      //echo "<BR> I'm here1!!! USERID:$userID ; COIN:$symbol($coinID) ; BASE:$baseCurrency ; RULE:$ruleIDBuy";
      $profitNum = findUserProfit($userProfit,$userID);
      //echo "<BR> Profit!!! $totalProfitPauseEnabled ;$profitNum; $totalProfitPause ; $rulesPause ; $rulesPauseEnabled";
      if ($totalProfitPauseEnabled == 1 && $profitNum<= $totalProfitPause && $ruleIDBuy == $rulesPause){
        if ($rulesPauseEnabled == 1){
          echo "<BR> PAUSING RULES $rulesPause for $rulesPauseHours HOURS";
          newLogToSQL("BuyCoins", "pauseRule($rulesPause, $rulesPauseHours);", $userID,$GLOBALS['logToSQLSetting'],"RulesPause","RuleID:$ruleIDBuy CoinID:$coinID");
          pauseRule($rulesPause, $rulesPauseHours);
        }
        echo "<BR>EXIT: TotalProfitPauseEnabled $totalProfitPauseEnabled Profit: $profitNum $totalProfitPause ";
        continue;}
        //else{ echo "<BR> EXIT PROFIT!";}
      $GLOBALS['allDisabled'] = false;
      if (empty($APIKey) && empty($APISecret)){ continue;}
      if ($APIKey=="NA" && $APISecret == "NA"){
        //Echo "<BR> EXIT: API Key Missing: $userID $APIKey $ruleIDBuy<BR>";
        continue;}
      //echo "<BR> I'm here1A!!! USERID:$userID ; COIN:$symbol($coinID) ; BASE:$baseCurrency ; RULE:$ruleIDBuy";
      if ($limitToBaseCurrency != "ALL" && $baseCurrency != $limitToBaseCurrency){
        //Echo "<BR> EXIT: Wrong Base Currency: $userID $baseCurrency $limitToBaseCurrency $ruleIDBuy<BR>";
        continue;}
      //echo "<BR> I'm here1B!!! USERID:$userID ; COIN:$symbol($coinID) ; BASE:$baseCurrency ; RULE:$ruleIDBuy";
      //echo "<BR> BASE!! $baseCurrency : $userBaseCurrency";
      //if ($baseCurrency != $userBaseCurrency && $userBaseCurrency != "ALL"){
        //Echo "<BR> EXIT: Wrong User Base Currency: $userID $baseCurrency $userBaseCurrency $ruleIDBuy<BR>";
      //  continue;}
      //echo "<BR> I'm here1C!!! USERID:$userID ; COIN:$symbol($coinID) ; BASE:$baseCurrency ; RULE:$ruleIDBuy";
      if ($limitToCoin != "ALL" && $symbol != $limitToCoin) {
        //Echo "<BR> EXIT: Limit to Coin: $userID $symbol $limitToCoin<BR>";
        continue;}
      echo "<BR> I'm here1D!!! USERID:$userID ; COIN:$symbol($coinID) ; BASE:$baseCurrency ; RULE:$ruleIDBuy";
      if ($doNotBuy == 1){
        //Echo "<BR> EXIT: Do Not Buy<BR>";
        continue;}
      if ($overrideDailyLimit == 0 && $EnableTotalBTCLimit == 1){
        echo "<BR> Check if over total limit! ";
        $userBTCSpent = getUserTotalBTC($totalBTCSpent,$userID,$baseCurrency);
        echo "<BR> Testing Testing Testing| $userID | : ".$totalBTCSpent[0][0];
          if ($userBTCSpent >= $TotalBTCLimit){ echo "<BR>EXIT: TOTAL BTC SPENT"; continue;}else{ echo "<BR> Total Spend ".$userBTCSpent." Limit $TotalBTCLimit";}
      }
      if ($overrideDailyLimit == 0 && $EnableDailyBTCLimit == 1){
        echo "<BR> Check if over daily limit! ";
        $userDailyBTCSpent = getUserTotalBTC($dailyBTCSpent,$userID,$baseCurrency);
          if ($userDailyBTCSpent >= $DailyBTCLimit){echo "<BR>EXIT: DAILY BTC SPENT";continue;}else{ echo "<BR> Daily Spend ".$userDailyBTCSpent." Limit $DailyBTCLimit";}
      }
      echo "<BR> I'm here2!!! USERID:$userID ; COIN:$symbol($coinID) ; BASE:$baseCurrency ; RULE:$ruleIDBuy";
      if ($buyCounter[$userID."-".$coinID] >= 1 && $overrideDailyLimit == 0){ echo "<BR>EXIT: Buy Counter Met! $noOfBuys ".$buyCounter[$userID."-".$coinID];continue;
      }else{ Echo "<BR> Number of Coin Buys: 1 BuyCounter ".$buyCounter[$userID."-".$coinID];}
      if ($buyCounter[$userID."-Total"] >= $noOfBuys && $overrideDailyLimit == 0){ echo "<BR>EXIT: Buy Counter Met! $noOfBuys ".$buyCounter[$userID."-Total"];continue;
      }else{ Echo "<BR> Number of Total Buys: $noOfBuys BuyCounter ".$buyCounter[$userID."-Total"];}
      if ($userActive == False){ echo "<BR>EXIT: User Not Active!"; continue;}
      if ($hoursDisableUntil > 0){ echo "<BR> EXIT: Disabled until: ".$hoursDisableUntil; continue;}
      $LiveBTCPrice = number_format((float)(bittrexCoinPrice($APIKey, $APISecret,'USD','BTC',$apiVersion)), 8, '.', '');
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
      Echo "<BR> TEST 10: $BuyPriceMinEnabled | $BuyPriceMin | $LiveCoinPrice | $test10";
      $buyResultAry[] = Array($test10, "Buy Price Minimum $symbol", $LiveCoinPrice);
      $test11 = autoBuyMain($LiveCoinPrice,$autoBuyPrice, $autoBuyCoinEnabled,$coinID);
      $buyResultAry[] = Array($test11, "Auto Buy Price $symbol", $LiveCoinPrice);
      $test12 = coinMatchPattern($coinPriceMatch,$LiveCoinPrice,$symbol,0,$coinPricePatternEnabled,$ruleIDBuy,0);
      $buyResultAry[] = Array($test12, "Coin Price Pattern $symbol", $LiveCoinPrice);
      $test15 = checkPriceDipCoinFlat($priceDipCoinFlatEnabled,$priceDipHoursFlatTarget, $priceDipHours);
      $buyResultAry[] = Array($test15, "Coin Price Dip Coin Flat $symbol", $LiveCoinPrice);
      $test16 = buyWithMin($priceDipMinPriceEnabled, $finalPriceDipMinPrice, $LiveCoinPrice);
      $buyResultAry[] = Array($test16, "Coin Price Dip Min Price $symbol", $LiveCoinPrice);
      if ($Hr1ChangeTrendEnabled){
        $test14 = newBuywithPattern($new1HrPriceChange,$coin1HrPatternList,$Hr1ChangeTrendEnabled,$ruleIDBuy,0);
      }else{$test14 = True;}
      $buyResultAry[] = Array($test14, "1 Hour Price Pattern $symbol", $new1HrPriceChange);
      $test13 = $GLOBALS['allDisabled'];
      if (buyAmountOverride($buyAmountOverrideEnabled)){$BTCAmount = $buyAmountOverride; Echo "<BR> 13: BuyAmountOverride set to : $buyAmountOverride | BTCAmount: $BTCAmount";}
      $totalScore_Buy = $test1+$test2+$test3+$test4+$test5+$test6+$test7+$test8+$test9+$test10+$test11+$test12+$test13+$test14+$test15+$test16;
      if ($totalScore_Buy >= 15 ){
        $buyOutstanding = getOutStandingBuy($buyResultAry);
        logAction("UserID: $userID | RuleID: $ruleIDBuy | Coin : $symbol | 1:  $test1  2:  $test2  3:  $test3  4:  $test4  5:  $test5  6:  $test6  7:  $test7  8:  $test8  9:  $test9  10:  $test10  11:  $test11  12:  $test12  13:  $test13  14:  $test14   15:  $test15   16:  $test16 TOTAL: $totalScore_Buy / 16 $buyOutstanding","BuyScore", $GLOBALS['logToFileSetting'] );
      }
      Echo "<BR> UserID: $userID | RuleID: $ruleIDBuy | Coin : $symbol| 1:$test1  2:$test2  3:$test3  4:$test4  5:$test5  6:$test6  7:$test7  8:$test8  9:$test9  10:$test10  11:$test11  12:$test12  13:$test13  14:$test14 15:$test15 16:$test16  TOTAL:$totalScore_Buy / 16";
      if ($test1 == True && $test2 == True && $test3 == True && $test4 == True && $test5 == True && $test6 == True && $test7 == True && $test8 == True && $test9 == True && $test10 == True &&
      $test11 == True && $test12 == True && $test13 == True && $test14 == True && $test15 == True && $test16 == True){
        $date = date("Y-m-d H:i:s", time());
      echo "<BR> Call Bittrex Bal: bittrexbalance($APIKey, $APISecret,$baseCurrency, $apiVersion);";
        $BTCBalance = bittrexbalance($APIKey, $APISecret,$baseCurrency, $apiVersion);
        $reservedAmount = getReservedAmount($baseCurrency,$userID);
        Echo "<BR> TEST BAL AND RES: $BTCBalance ; $BTCAmount ; ".$reservedAmount[0][0]."| "; //.$BTCBalance-$reservedAmount
        Echo "<BR> TEST BAL AND RES: $BTCBalance ; $BTCAmount ; ".$reservedAmount[0][0]." | "; //.$BTCBalance-$reservedAmount
        if ($reservedAmount[0][0] == 0 OR $reservedAmount[0][3] == 0){
          $usdtReserved = 0;
        }else{
          $usdtReserved = $reservedAmount[0][3]/$reservedAmount[0][0];
        }
        if ($reservedAmount[0][4] == 0 OR $reservedAmount[0][1] == 0){
          $btcReserved = 0;
        }else{
          $btcReserved = ($reservedAmount[0][4]/$reservedAmount[0][1]);
        }
        if ($reservedAmount[0][5] ==0 OR $reservedAmount[0][2] == 0){
          $ethReserved = 0;
        }else{
          $ethReserved = ($reservedAmount[0][5]/$reservedAmount[0][2]);
        }

        echo "<BR> $usdtReserved | $btcReserved | $ethReserved";
        $totalReserved = $usdtReserved+$btcReserved+$ethReserved;

        if ($baseCurrency == 'BTC'){
          echo "<BR> BTC Bal Test : $BTCBalance | $totalReserved | ".$baseMultiplier[0][0];
          $totalBal = ($BTCBalance*$baseMultiplier[0][0])-$totalReserved;
          $buyQuantity = $BTCAmount / $baseMultiplier[0][0];
          newLogToSQL("BuyCoins","BaseCurrency is BTC : totalBal: $totalBal | BTC Bal: $BTCBalance | totalReserved: $totalReserved | Multiplier : ".$baseMultiplier[0][0],3,$GLOBALS['logToSQLSetting'],"BTCTest","RuleID:$ruleIDBuy CoinID:$coinID");
        }elseif ($baseCurrency == 'ETH'){
          echo "<BR> ETH Bal Test : $BTCBalance | $totalReserved | ".$baseMultiplier[0][1];
          $totalBal = ($BTCBalance * $baseMultiplier[0][1])-$totalReserved;
          $buyQuantity = $BTCAmount / $baseMultiplier[0][1];
          newLogToSQL("BuyCoins","BaseCurrency is ETH : totalBal: $totalBal | Multiplier : ".$baseMultiplier[0][1],3,$GLOBALS['logToSQLSetting'],"ETHTest","RuleID:$ruleIDBuy CoinID:$coinID");
        }else{
          echo "<BR> USDT Bal Test : $BTCBalance | $totalReserved ";
          $totalBal = $BTCBalance-$totalReserved;
          $buyQuantity = $BTCAmount;
        }
        newLogToSQL("BuyCoins"," $totalBal | $BTCAmount",3,$GLOBALS['logToSQLSetting'],"OneTimeBuyRuleTest","RuleID:$ruleIDBuy CoinID:$coinID");
        if ($totalBal > 20 OR $overrideCoinAlloc == 1) {
          echo "<BR>Buying Coins: $APIKey, $APISecret,$symbol, $Email, $userID, $date, $baseCurrency,$SendEmail,$BuyCoin,$BTCAmount, $ruleIDBuy,$UserName,$coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed";
          if($BTCAmount <= 0 ){ continue;}
          addTrackingCoin($coinID, $LiveCoinPrice, $userID, $baseCurrency, $SendEmail, $BuyCoin, $buyQuantity, $ruleIDBuy, $CoinSellOffsetPct, $CoinSellOffsetEnabled, $buyType, $timeToCancelBuyMins, $SellRuleFixed,0,0,$risesInPrice,'Buy',$LiveCoinPrice,0,0,$overrideCoinAlloc,'BuyCoins',0);
          newLogToSQL("BuyCoins","addTrackingCoin($coinID, $LiveCoinPrice, $userID, $baseCurrency, $SendEmail, $BuyCoin, $buyQuantity, $ruleIDBuy, $CoinSellOffsetPct, $CoinSellOffsetEnabled, $buyType, $timeToCancelBuyMins, $SellRuleFixed,0,0,$risesInPrice,'Buy',$LiveCoinPrice,0,0);",3,0,"AddTrackingCoin","RuleID:$ruleIDBuy CoinID:$coinID");
          //addWebUsage($userID,"Add","BuyTracking");
          $buyCounter[$userID."-".$coinID] = $buyCounter[$userID."-".$coinID] + 1;
          $buyCounter[$userID."-Total"] = $buyCounter[$userID."-Total"] + 1;
          //if ($oneTimeBuy == 1){ disableBuyRule($ruleIDBuy);}
          logAction("runBuyCoins; addTrackingCoin : $symbol | $coinID | $LiveCoinPrice | $buyQuantity | $userID | $baseCurrency $timeToCancelBuyMins | $risesInPrice | $overrideCoinAlloc", 'BuySellFlow', 1);
          $finalBool = True;
        }else{ echo "<BR> EXIT: $totalBal Less than 20 | $totalBal";}
      }else{
        if ($limitToCoin != "ALL"){ echo "<BR> LimitToCoin Found: Continue Rules!"; continue 2;}
      }

      echo "<BR> NEXT RULE <BR>";
    }//Rule Loop
  }//Coin Loop
  return $finalBool;
}


?>
</html>
