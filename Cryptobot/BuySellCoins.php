<html>
<?php
ini_set('max_execution_time', 600);
require('includes/newConfig.php');

include_once ('/home/stevenj1979/SQLData.php');
include_once ('/home/stevenj1979/Encrypt.php');
$apikey=getAPIKey();
$apisecret=getAPISecret();
//$buyCancelTime = "01:0";
//$noOfBuys = 5;
$buyCounter = 0;
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

function actionAlert($minutes,$email,$symbol,$price,$action,$userName,$category,$reocurring,$id,$userID){
  if ($minutes < -30){
    sendAlertEmail($email, $symbol, $price, $action, $userName);
    logAction("Alert: $symbol $price $action $userName $category", 'BuySellAlert');
    logToSQL("Alerts", "Coin: $symbol $action $category $price", $userID);
  }
  //Close Alert
  if ($reocurring == 0){closeCoinAlerts($id);}else{updateAlertTime($id);}
}

function getOutStandingBuy($tmpAry){
  $tmpStr = "";
  $tmpAryCount = count($tmpAry);
  for ($i=0; $i<$tmpAryCount; $i++){
    Echo "<BR> getOutStandingBuy: ".$tmpAry[$i][1].":".$tmpAry[$i][2].",";
    if ($tmpAry[$i][0] <> 1){ $tmpStr .= $tmpAry[$i][1].":".$tmpAry[$i][2].",";}
  }
  return rtrim($tmpStr,",");
}

function getOutStandingSell(){

}


//set time
setTimeZone();
$date = date("Y-m-d H:i", time());
$current_date = date('Y-m-d H:i');
$completeFlag = False;
$newTime = date("Y-m-d H:i",strtotime($tmpTime, strtotime($current_date)));
logAction("Buy Sell Coins Start : End set to $newTime : $date", 'BuySellTiming');
$buyRules = getUserRules();
$buyRulesSize = count($buyRules);
$sellRules = getUserSellRules();
$sellRulesSize = count($sellRules);
$i = 0;
$coins = getTrackingCoins();
$coinLength = Count($coins);
$coinPriceMatch = getCoinPriceMatchList();
$coinPricePatternList = getCoinPricePattenList();
$coin1HrPatternList = getCoin1HrPattenList();
$autoBuyPrice = getAutoBuyPrices();
//echo "<br> coinLength= $coinLength NEWTime=".$newTime." StartTime $date EndTime $newTime";
while($completeFlag == False){
  echo "<BR> BUY COINS!! ";
  //logAction("Check Buy Coins Start", 'BuySellTiming');
  for($x = 0; $x < $coinLength; $x++) {
    //variables
    $coinID = $coins[$x][0]; $symbol = $coins[$x][1]; $baseCurrency = $coins[$x][26];
    $BuyOrdersPctChange = $coins[$x][4]; $MarketCapPctChange = $coins[$x][7]; $Hr1ChangePctChange = $coins[$x][10];
    $Hr24ChangePctChange = $coins[$x][13]; $D7ChangePctChange = $coins[$x][14]; $CoinPricePctChange = $coins[$x][19];
    $SellOrdersPctChange = $coins[$x][22]; $VolumePctChange = $coins[$x][25];
    $price4Trend = $coins[$x][27]; $price3Trend = $coins[$x][28]; $lastPriceTrend = $coins[$x][29];  $livePriceTrend = $coins[$x][30];
    $newPriceTrend = $price4Trend.$price3Trend.$lastPriceTrend.$livePriceTrend;
    $LiveCoinPrice = $coins[$x][17]; $Hr1LivePriceChange = $coins[$x][31];$Hr1LastPriceChange = $coins[$x][32]; $Hr1PriceChange3 = $coins[$x][33];$Hr1PriceChange4 = $coins[$x][34];
    $new1HrPriceChange = $Hr1PriceChange4.$Hr1PriceChange3.$Hr1LastPriceChange.$Hr1LivePriceChange;
    //$timeToCancelBuyMins = $coins[$x][31];
    //LOG
    //echo "<br> i=$i CoinID=$coinID Coin=$symbol baseCurrency=$baseCurrency ";
    //echo "<blockquote>";
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
      $Hr1ChangeTrendEnabled = $buyRules[$y][63]; $Hr1ChangeTrend = $buyRules[$y][64];
      if (!Empty($KEK)){$APISecret = decrypt($KEK,$buyRules[$y][31]);}
      //$APISecret = $buyRules[$y][31];
      //Echo " KEK $KEK APISecret $APISecret API ".$buyRules[$y][31];
      $EnableDailyBTCLimit = $buyRules[$y][32]; $DailyBTCLimit = $buyRules[$y][33]; $EnableTotalBTCLimit = $buyRules[$y][34];
      $TotalBTCLimit= $buyRules[$y][34]; $userID = $buyRules[$y][0]; $ruleIDBuy = $buyRules[$y][36]; $CoinSellOffsetPct = $buyRules[$y][37];
      $CoinSellOffsetEnabled = $buyRules[$y][38];
      $priceTrendEnabled = $buyRules[$y][39]; $price4TrendTrgt = $buyRules[$y][40];$price3TrendTrgt = $buyRules[$y][41];$lastPriceTrendTrgt = $buyRules[$y][42];
      $livePriceTrendTrgt = $buyRules[$y][43]; $userActive = $buyRules[$y][44]; $disableUntil = $buyRules[$y][45];
      $userBaseCurrency = $buyRules[$y][46]; $noOfBuys = $buyRules[$y][47]; $buyType = $buyRules[$y][48]; $timeToCancelBuyMins = $buyRules[$y][49];
      $BuyPriceMinEnabled = $buyRules[$y][50]; $BuyPriceMin = $buyRules[$y][51];
      $limitToCoin = $buyRules[$y][52]; $autoBuyCoinEnabled = $buyRules[$y][53];//$autoBuyPrice = $buyRules[$y][54];
      $buyAmountOverrideEnabled = $buyRules[$y][55]; $buyAmountOverride = $buyRules[$y][56];
      $newBuyPattern = $buyRules[$y][57];
      //if ($userID != ){ continue; }
      //echo "<BR> BUYCOINOFFSET Enabled: $CoinSellOffsetEnabled  - BUYCoinOffsetPct: $CoinSellOffsetPct";
      //echo "<BR> Buy PATTERN Enabled: $priceTrendEnabled - Buy Rule: $price4TrendTrgt : $price3TrendTrgt : $lastPriceTrendTrgt : $livePriceTrendTrgt";
      //echo "<BR> Disable Until $disableUntil";
      //echo "<BR>RULE: $ruleIDBuy USER: $userID API $APIKey Sectret: $APISecret ";
      //echo "<BR> BASE: $baseCurrency USERBASE: $userBaseCurrency ";
      $GLOBALS['allDisabled'] = false;
      if (empty($APIKey) && empty($APISecret)){echo "<BR>EXIT: API KEY NOT SET! "; continue;}
      if ($APIKey=="NA" && $APISecret == "NA"){echo "<BR>EXIT: API KEY NOT SET! "; continue;}
      if ($baseCurrency != $userBaseCurrency && $userBaseCurrency != "All"){echo "<BR>EXIT: Wrong Base Currency! "; continue;}
      if ($limitToCoin != "ALL" && $symbol != $limitToCoin) {echo "<BR>EXIT: Rule Limited to Coin! $limitToCoin ; $symbol"; continue;}
      //Echo "<BR>Rule Limited to :  $limitToCoin";
      $totalBTCSpent = getTotalBTC($userID);

      if (!empty($totalBTCSpent[0][0])){
        if ($totalBTCSpent[0][0] >= $TotalBTCLimit && $EnableTotalBTCLimit == 1){ echo "<BR>EXIT: TOTAL BTC SPENT"; continue;}
      }

      if ($overrideDailyLimit == 0){
        echo "<BR> DAILY LIMIT OVERRIDE OFF : $overrideDailyLimit";
        $dailyBTCSpent = getDailyBTC($userID);
        if (!empty($dailyBTCSpent[0][0])){
          if ($dailyBTCSpent[0][0] >= $DailyBTCLimit && $EnableDailyBTCLimit == 1){echo "<BR>EXIT: DAILY BTC SPENT";continue;}
        }
        if ($noOfBuys == $buyCounter){ echo "<BR>EXIT: Buy Counter Met!";continue;}
      }

      if ($userActive == False){ echo "<BR>EXIT: User Not Active!"; continue;}
      if ($disableUntil > date("Y-m-d H:i:s", time())){ echo "<BR> EXIT: Disabled until: ".$disableUntil; continue;}
      $LiveBTCPrice = number_format((float)(bittrexCoinPrice($apikey, $apisecret,'USD','BTC')), 8, '.', '');
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
      $buyResultAry[] = Array($test4, "1 Hour Price Change $symbol", $Hr1ChangePctChange);
      $test5 = buyWithScore($Hr24ChangeTop,$Hr24ChangeBtm,$Hr24ChangePctChange,$Hr24ChangeEnabled);
      $buyResultAry[] = Array($test5, "24 Hour Price Change $symbol", $Hr24ChangePctChange);
      $test6 = buyWithScore($D7ChangeTop,$D7ChangeBtm,$D7ChangePctChange,$D7ChangeEnabled);
      $buyResultAry[] = Array($test6, "7 Day Price Change $symbol", $D7ChangePctChange);
      $test7 = buyWithScore($CoinPriceTop,$CoinPriceBtm,$CoinPricePctChange,$CoinPriceEnabled);
      $buyResultAry[] = Array($test7, "Coin Price $symbol", $CoinPricePctChange);
      $test8 = buyWithScore($SellOrdersTop,$SellOrdersBtm,$SellOrdersPctChange,$SellOrdersEnabled);
      $buyResultAry[] = Array($test8, "Sell Orders $symbol", $SellOrdersPctChange);
      $test9 = newBuywithPattern($newPriceTrend,$coinPricePatternList,$priceTrendEnabled,$ruleIDBuy,0);
      $buyResultAry[] = Array($test9, "Buy Price Pattern $symbol", $newPriceTrend);
      $test10 = buyWithMin($BuyPriceMinEnabled,$BuyPriceMin,$LiveCoinPrice);
      $buyResultAry[] = Array($test10, "Buy Price Minimum $symbol", $LiveCoinPrice);
      $test11 = autoBuyMain($LiveCoinPrice,$autoBuyPrice, $autoBuyCoinEnabled,$coinID);
      $buyResultAry[] = Array($test11, "Auto Buy Price $symbol", $LiveCoinPrice);
      $test12 = coinMatchPattern($coinPriceMatch,$LiveCoinPrice,$symbol,0,$coinPricePatternEnabled,$ruleIDBuy,0);
      $buyResultAry[] = Array($test12, "Coin Price Pattern $symbol", $LiveCoinPrice);
      $test14 = newBuywithPattern($new1HrPriceChange,$coin1HrPatternList,$Hr1ChangeTrendEnabled,$ruleIDBuy,0);
      $buyResultAry[] = Array($test14, "1 Hour Price Pattern $symbol", $new1HrPriceChange);
      $test13 = $GLOBALS['allDisabled'];
      if (buyAmountOverride($buyAmountOverrideEnabled)){$BTCAmount = $buyAmountOverride; Echo "<BR> 13: BuyAmountOverride set to : $buyAmountOverride";}
      //logAction("1: $test1 2: $test2 3: $test3 4: $test4 5: $test5 6: $test6 7: $test7 8: $test8 9: $test9 10: $test10 11: $test11 12: $test12 ", 'BuySell');
      //Echo "<BR> New Boolean Test! 1: $test1 2: $test2 3: $test3 4: $test4 5: $test5 6: $test6 7: $test7 8: $test8 9: $test9 10: $test10 11: $test11 12: $test12 ";
      $totalScore_Buy = $test1+$test2+$test3+$test4+$test5+$test6+$test7+$test8+$test9+$test10+$test11+$test12+$test13+$test14;
      if ($totalScore_Buy >= 13 ){
        $buyOutstanding = getOutStandingBuy($buyResultAry);
        logAction("UserID: $userID | RuleID: $ruleIDBuy | Coin : $symbol | 1:  $test1  2:  $test2  3:  $test3  4:  $test4  5:  $test5  6:  $test6  7:  $test7  8:  $test8  9:  $test9  10:  $test10  11:  $test11  12:  $test12  13:  $test13  14:  $test14  TOTAL: $totalScore_Buy / 14 $buyOutstanding","BuyScore");
        logToSQL("BuyCoin", "RuleID: $ruleIDBuy | Coin : $symbol | TOTAL:  $totalScore_Buy $buyOutstanding", $userID);
      }
      Echo "<BR> UserID: $userID | RuleID: $ruleIDBuy | Coin : $symbol| 1:  $test1  2:  $test2  3:  $test3  4:  $test4  5:  $test5  6:  $test6  7:  $test7  8:  $test8  9:  $test9  10:  $test10  11:  $test11  12:  $test12  13:  $test13  14:  $test14  TOTAL: $totalScore_Buy / 14";

      if ($test1 == True && $test2 == True && $test3 == True && $test4 == True && $test5 == True && $test6 == True && $test7 == True && $test8 == True && $test9 == True && $test10 == True &&
      $test11 == True && $test12 == True && $test13 == True && $test14 == True){
        $date = date("Y-m-d H:i:s", time());
        echo "<BR>Buying Coins: $APIKey, $APISecret,$symbol, $Email, $userID, $date, $baseCurrency,$SendEmail,$BuyCoin,$BTCAmount, $ruleIDBuy,$UserName,$coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed";
        buyCoins($APIKey, $APISecret,$symbol, $Email, $userID, $date, $baseCurrency,$SendEmail,$BuyCoin,$BTCAmount, $ruleIDBuy,$UserName,$coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed, 0);
        logAction("buyCoins($APIKey,$symbol, $Email, $userID, $date, $baseCurrency,$SendEmail,$BuyCoin,$BTCAmount, $ruleIDBuy,$UserName,$coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed)", 'BuySell');
        logToSQL("BuyCoin", "RuleID: $ruleIDBuy | Amount: $BTCAmount | TOTAL:  $totalScore_Buy", $userID);
        $buyCounter = $buyCounter + 1;
      }

      echo "<BR> NEXT RULE <BR>";
    }//Rule Loop
    //echo "</blockquote>";
  }//Coin Loop
  echo "<BR> SELL COINS!! ";
  //logAction("Check Sell Coins Start", 'BuySellTiming');
  //echo "<blockquote>";
  $sellCoins = getTrackingSellCoins();
  $sellCoinsLength = count($sellCoins);
  for($a = 0; $a < $sellCoinsLength; $a++) {
    //Variables
    $coin = $sellCoins[$a][11]; $MarketCapPctChange = $sellCoins[$a][17]; $VolumePctChange = $sellCoins[$a][26];
    $SellOrdersPctChange = $sellCoins[$a][23]; $Hr1ChangePctChange = $sellCoins[$a][28]; $Hr24ChangePctChange = $sellCoins[$a][31];
    $D7ChangePctChange = $sellCoins[$a][34]; $LiveCoinPrice = $sellCoins[$a][19]; $CoinPricePctChange = $sellCoins[$a][20];
    $BaseCurrency = $sellCoins[$a][36]; $orderNo = $sellCoins[$a][10]; $amount = $sellCoins[$a][5]; $cost = $sellCoins[$a][4];
    $transactionID = $sellCoins[$a][0]; $coinID = $sellCoins[$a][2]; $sellCoinsUserID = $sellCoins[$a][3];
    $fixSellRule = $sellCoins[$a][41]; $BuyRule = $sellCoins[$a][43];
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
      //$profit = ((($amount*$liveCoinPrice)-($amount*$cost))/($amount*$cost))*100;
      //$APISecret = $sellRules[$z][34];
      $userID = $sellRules[$z][1]; $ruleIDSell = $sellRules[$z][0];
      $sellCoinOffsetEnabled = $sellRules[$z][35]; $sellCoinOffsetPct = $sellRules[$z][36];
      $sellPriceMinEnabled = $sellRules[$z][37]; $sellPriceMin = $sellRules[$z][38];
      $KEKSell = $sellRules[$z][40];
      $priceTrendEnabled = $sellRules[$z][41]; $newSellPattern = $sellRules[$z][42];
      $limitToBuyRule = $sellRules[$z][43];
      if ($limitToBuyRule == "ALL"){ $limitToBuyRuleEnabled = 0;}else{$limitToBuyRuleEnabled = 1;}
      if ($fixSellRule != "ALL" && (int)$fixSellRule != $ruleIDSell){echo "<BR>EXIT: Sell Rule Limited! $fixSellRule ; $ruleIDSell"; continue;}
      if (!Empty($KEKSell)){ $apisecret = Decrypt($KEKSell,$sellRules[$z][34]);}
      $LiveBTCPrice = number_format((float)(bittrexCoinPrice($apikey, $apisecret,'USD','BTC')), 8, '.', '');
      $limitToCoinSell = $sellRules[$z][39];

      //echo "<BR> RULE: ".$ruleIDSell;
      //echo "<BR> SellCOINOFFSET Enabled: $sellCoinOffsetEnabled  - SellCoinOffsetPct: $sellCoinOffsetPct";
      if ($userID != $sellCoinsUserID){ echo "<BR>EXIT: Wrong User!"; continue; }
      if ($limitToCoinSell != "ALL" && $coin != $limitToCoinSell) {echo "<BR>EXIT: SELL Rule Limited to Coin! $limitToCoinSell ; $coin"; continue;}
      if ($limitToBuyRule != "ALL" && limitToBuyRule($BuyRule,$limitToBuyRule,$limitToBuyRuleEnabled) == False){echo "<BR>EXIT: Limited to Buy rule $limitToBuyRule : $BuyRule"; continue;}
      $GLOBALS['allDisabled'] = false;

      $buyPrice = ($cost * $amount);
      $sellPrice = ($LiveCoinPrice * $amount);
      $fee = (($LiveCoinPrice * $amount)/100)*0.25;
      $profit = ((($sellPrice-$fee)-$buyPrice)/$buyPrice)*100;
      //Echo "MarketCap $marketCapTop,$marketCapBtm,$marketCapbyPct,$marketCapEnable <BR>";
      $sTest1 = sellWithScore($MarketCapTop,$MarketCapBtm,$MarketCapPctChange,$MarketCapEnabled);
      $sellResultAry[] = Array($sTest1, "Market Cap $coin", $MarketCapPctChange);
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
      $sTest7 = newBuywithPattern($price4Trend.$price3Trend.$lastPriceTrend.$livePriceTrend,$coinPricePatternList,$priceTrendEnabled,$ruleIDSell,1);
      $sellResultAry[] = Array($sTest7, "Price Trend Pattern $coin", $price4Trend.$price3Trend.$lastPriceTrend.$livePriceTrend);
      $sTest8 = sellWithMin($sellPriceMinEnabled,$sellPriceMin,$LiveCoinPrice,$LiveBTCPrice);
      $sellResultAry[] = Array($sTest8, "Minimum Price $coin", $LiveCoinPrice);
      $sTest9 = sellWithScore($ProfitPctTop_Sell,$ProfitPctBtm_Sell,$profit,$ProfitPctEnabled);
      $sellResultAry[] = Array($sTest9, "Profit Percentage $coin", $profit);
      $sTest10 = sellWithScore($CoinPriceTop,$CoinPriceBtm,$CoinPricePctChange,$CoinPriceEnabled);
      $sellResultAry[] = Array($sTest10, "Minimum Sell Price $coin", $CoinPricePctChange);
      $sTest11 = coinMatchPattern($coinPriceMatch,$LiveCoinPrice,$coin,1,$coinPricePatternSellEnabled,$ruleIDSell,1);
      $sellResultAry[] = Array($sTest11, "Coin Price Match $coin", $LiveCoinPrice);
      $sTest13 = autoSellMain($LiveCoinPrice,$autoBuyPrice,$autoSellCoinEnabled,$coinID);
      $sellResultAry[] = Array($sTest12, "Auto Sell $coin", $LiveCoinPrice);
      $sTest12 = $GLOBALS['allDisabled'];
      Echo "<BR> TEST: sellWithScore($ProfitPctTop_Sell,$ProfitPctBtm_Sell,$profit,$ProfitPctEnabled);";
      $sellOutstanding = getOutStandingBuy($sellResultAry);
      $totalScore_Sell = $sTest1+$sTest2+$sTest3+$sTest4+$sTest5+$sTest6+$sTest7+$sTest8+$sTest9+$sTest10+$sTest11+$sTest12+$sTest13;
      if ($totalScore_Sell >= 12){
        $sellOutstanding = getOutStandingBuy($sellResultAry);
        logAction("UserID: $userID | RuleID: $ruleIDSell | Coin : $coin | 1:  $sTest1  2:  $sTest2  3:  $sTest3  4:  $sTest4  5:  $sTest5  6:  $sTest6  7:  $sTest7  8:  $sTest8  9:  $sTest9  10:  $sTest10  11:  $sTest11  12:  $sTest12 13: $sTest13 TOTAL:  $totalScore_Sell / 13, PROFIT: $profit $sellOutstanding","SellScore");
        logToSQL("SellCoin", "RuleID: $ruleIDSell | Coin : $coin | TOTAL: $totalScore_Sell $sellOutstanding", $userID);
      }
      Echo "<BR> UserID: $userID | RuleID: $ruleIDSell | Coin : $coin | 1:  $sTest1  2:  $sTest2  3:  $sTest3  4:  $sTest4  5:  $sTest5  6:  $sTest6  7:  $sTest7  8:  $sTest8  9:  $sTest9  10:  $sTest10  11:  $sTest11  12:  $sTest12 13: $sTest13 TOTAL:  $totalScore_Sell / 13, PROFIT: $profit";

      if ($sTest1 == True && $sTest2 == True && $sTest3 == True && $sTest4 == True && $sTest5 == True && $sTest6 == True && $sTest7 == True && $sTest8 == True && $sTest9 == True && $sTest10 == True
      && $sTest11 == True && $sTest12 == True && $sTest13 == True){
        $date = date("Y-m-d H:i:s", time());
        echo "<BR>Sell Coins: $APIKey, $APISecret,$coin, $Email, $userID, 0,$date, $BaseCurrency,$SendEmail,$SellCoin, _.$ruleIDSell,$UserName,$orderNo,$amount,$cost,$transactionID,$coinID<BR>";
        //sellCoins($apikey, $apisecret, $coin, $email, $userID, $score, $date,$baseCurrency, $sendEmail, $sellCoin, $ruleID,$userName, $orderNo,$amount,$cost,$transactionID,$coinID){
        sellCoins($APIKey, $APISecret,$coin, $Email, $userID, 0,$date, $BaseCurrency,$SendEmail,$SellCoin, $ruleIDSell,$UserName,$orderNo,$amount,$cost,$transactionID,$coinID,$sellCoinOffsetEnabled,$sellCoinOffsetPct,$LiveCoinPrice);
        logAction("sellCoins($APIKey, $APISecret,$coin, $Email, $userID, 0,$date, $BaseCurrency,$SendEmail,$SellCoin, $ruleIDSell,$UserName,$orderNo,$amount,$cost,$transactionID,$coinID,$sellCoinOffsetEnabled,$sellCoinOffsetPct,$LiveCoinPrice)",'BuySell');
        logAction("UserID: $userID | Coin : $coin | 1: $sTest1 2: $sTest2 3: $sTest3 4: $sTest4 5: $sTest5 6: $sTest6 7: $sTest7 8: $sTest8 9: $sTest9 10: $sTest10 11: $sTest11",'BuySell');
        logToSQL("SellCoin", "RuleID: $ruleIDSell | Coin : $coin | Amount: $amount | Cost: $cost TOTAL: $totalScore_Sell", $userID);
        //break;
        //addSellRuletoSQL()
      }

      echo "<BR> NEXT RULE <BR>";
    }//Sell Rules

  }//Sell Coin Loop
  //echo "</blockquote>";
    echo "<BR> CHECK BITTREX!! ";
    //logAction("Check Bittrex Orders Start", 'BuySellTiming');
  echo "<blockquote>";
  $BittrexReqs = getBittrexRequests();
  $BittrexReqsSize = count($BittrexReqs);
  for($b = 0; $b < $BittrexReqsSize; $b++) {
    //Variables
    $type = $BittrexReqs[$b][0]; $uuid = $BittrexReqs[$b][1]; $date = $BittrexReqs[$b][2]; $status = $BittrexReqs[$b][4];   $bitPrice = $BittrexReqs[$b][5]; $userName = $BittrexReqs[$b][6];
    $apiKey = $BittrexReqs[$b][7]; $apiSecret = $BittrexReqs[$b][8]; $coin = $BittrexReqs[$b][9];$amount = $BittrexReqs[$b][10];$cost = $BittrexReqs[$b][11];$userID = $BittrexReqs[$b][12];
    $email = $BittrexReqs[$b][13]; $orderNo = $BittrexReqs[$b][14];$transactionID = $BittrexReqs[$b][15]; $totalScore = 0; $baseCurrency = $BittrexReqs[$b][16]; $ruleIDBTBuy = $BittrexReqs[$b][17];
    $sendEmail = 1; $daysOutstanding = $BittrexReqs[$b][18]; $timeSinceAction = $BittrexReqs[$b][19]; $coinID = $BittrexReqs[$b][20]; $ruleIDBTSell = $BittrexReqs[$b][21];
    $liveCoinPriceBit = $BittrexReqs[$b][22]; $buyCancelTime = substr($BittrexReqs[$b][23],0,strlen($BittrexReqs[$b][23])-1); $sellFlag = false;
    $KEK = $BittrexReqs[$b][25];
    if (!Empty($KEK)){$apiSecret = decrypt($KEK,$BittrexReqs[$b][8]);}
    $buyOrderCancelTime = $BittrexReqs[$b][24];
    if ($liveCoinPriceBit != 0 && $bitPrice != 0){$pctFromSale =  (($liveCoinPriceBit-$bitPrice)/$bitPrice)*100;}
    if ($liveCoinPriceBit != 0 && $cost != 0){$liveProfitPct = ($liveCoinPriceBit-$cost)/$cost*100;}
    echo "<BR> bittrexOrder($apiKey, $apiSecret, $uuid);";
    $resultOrd = bittrexOrder($apiKey, $apiSecret, $uuid);

    $finalPrice = number_format((float)$resultOrd["result"]["PricePerUnit"], 8, '.', '');
    $orderQty = $resultOrd["result"]["Quantity"]; $orderQtyRemaining = $resultOrd["result"]["QuantityRemaining"]; $qtySold = $orderQty-$orderQtyRemaining;
    $orderIsOpen = $resultOrd["result"]["IsOpen"];
    //if ($orderQtyRemaining=0){$orderIsOpen = false;}
    echo "<BR> ------COIN to Sell: ".$coin."-------- USER: ".$userName;
    echo "<BR> Buy Cancel Time: $buyCancelTime";
    echo "TIME SINCE ACTION: $timeSinceAction";
    Print_r("What is Happening? // BITREXTID = ".$uuid."<br>");
    echo "<BR> Result IS OPEN? : ".$orderIsOpen." // CANCEL initiated: ".$resultOrd["result"]["CancelInitiated"];
    updateBittrexQuantityFilled($qtySold,$uuid);
    if ($orderQtyRemaining <> 0){ logToSQL("Bittrex", "Quantity Updated to : $qtySold for OrderNo: $orderNo", $userID);}
    if ($resultOrd["success"] == 1){
      if ($type == "Buy"){
        if ($orderIsOpen != 1 && $resultOrd["result"]["CancelInitiated"] != 1 && $resultOrd["result"]["QuantityRemaining"] == 0){
          //sendtoSteven($transactionID,$orderQtyRemaining."_".$qtySold."_".$orderQty, $orderNo."_".$finalPrice."_".$liveCoinPriceBit, "BUY - OrderIsOpen != 1 & CancelInitiated != 1");
          if ($sendEmail){
            $subject = "Coin Purchase1: ".$coin;
            $from = 'Coin Purchase <purchase@investment-tracker.net>';
            sendEmail($email, $coin, $amount, $finalPrice, $orderNo, $totalScore, $subject,$userName,$from);
          }
          bittrexBuyComplete($uuid, $transactionID, $finalPrice); //add buy price - $finalPrice
          logToSQL("Bittrex", "Order Complete for OrderNo: $orderNo Final Price: $finalPrice", $userID);
          //addBuyRuletoSQL($transactionID, $ruleIDBTBuy);
          echo "<BR>Buy Order COMPLETE!";
          continue;
        }
        //if ( substr($timeSinceAction,0,4) == $buyCancelTime){
        if ( $buyOrderCancelTime < date("Y-m-d H:i:s", time()) && $buyOrderCancelTime != '0000-00-00 00:00:00'){
          echo "<BR>CANCEL time exceeded! CANCELLING!";
          if ($orderQty == $orderQtyRemaining){
             $cancelRslt = bittrexCancel($apiKey,$apiSecret,$uuid);
             if ($cancelRslt == 1){
               bittrexBuyCancel($uuid, $transactionID);
               logToSQL("Bittrex", "Order time exceeded for OrderNo: $orderNo Cancel order completed", $userID);
             }else{
               logAction("bittrexCancelBuyOrder: ".$cancelRslt, 'Bittrex');
               logToSQL("Bittrex", "Order time exceeded for OrderNo: $orderNo Cancel order Error: $cancelRslt", $userID);
             }
          }else{
            $result = bittrexCancel($apiKey,$apiSecret,$uuid);
            if ($result == 1){
              bittrexUpdateBuyQty($transactionID, $orderQty-$orderQtyRemaining);
              logToSQL("Bittrex", "Order time exceeded for OrderNo: $orderNo Order cancelled and new Order Created", $userID);
              if ($sendEmail){
                $subject = "Coin Purchase1: ".$coin;
                $from = 'Coin Purchase <purchase@investment-tracker.net>';
                sendEmail($email, $coin, $amount, $cost, $orderNo, $totalScore, $subject,$userName,$from);
              }
              bittrexBuyComplete($uuid, $transactionID, $finalPrice); //add buy price - $finalPrice
              //addBuyRuletoSQL($transactionID, $ruleIDBTBuy);
            }else{ logAction("bittrexCancelBuyOrder: ".$result, 'Bittrex');}
          }
          continue;
        }
      }else{ // $type Sell
        if ($orderIsOpen != 1 && $resultOrd["result"]["CancelInitiated"] != 1 && $resultOrd["result"]["QuantityRemaining"] == 0){
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
            bittrexSellComplete($uuid, $transactionID, $finalPrice); //add sell price - $finalPrice
            logToSQL("Bittrex", "Sell Order Complete for OrderNo: $orderNo Final Price: $finalPrice", $userID);
            //addSellRuletoSQL($transactionID, $ruleIDBTSell);
            continue;
        }
        if ($daysOutstanding <= -28){
          echo "<BR>days from sale! $daysOutstanding CANCELLING!";
          if ($orderQtyRemaining == $orderQty){
            //complete sell update amount
            $cancelRslt = bittrexCancel($apiKey,$apiSecret,$uuid);
            if ($cancelRslt == 1){
              bittrexSellCancel($uuid, $transactionID);
              logToSQL("Bittrex", "Sell Order over 28 Days. Cancelling OrderNo: $orderNo", $userID);
              continue;
            }else{
              logAction("bittrexCancelSellOrder: ".$cancelRslt, 'Bittrex');
              logToSQL("Bittrex", "Sell Order over 28 Days. Error cancelling OrderNo: $orderNo : $cancelRslt", $userID);
            }
          }else{
             $result = bittrexCancel($apiKey,$apiSecret,$uuid);
             if ($result == 1){
               $newOrderNo = "ORD".$coin.date("YmdHis", time()).$ruleIDBTSell;
               //sendtoSteven($transactionID,$orderQtyRemaining."_".$qtySold."_".$orderQty, $newOrderNo."_".$orderNo, "SELL - Greater 28 days");
               bittrexCopyTransNewAmount($transactionID,$qtySold,$orderQtyRemaining,$newOrderNo);
               bittrexSellComplete($uuid, $transactionID, $finalPrice);
               logToSQL("Bittrex", "Sell Order over 28 Days. Cancelling OrderNo: $orderNo | Creating new Transaction", $userID);
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
               logAction("bittrexCancelSellOrder: ".$result, 'Bittrex');
               logToSQL("Bittrex", "Sell Order over 28 Days. Error cancelling OrderNo: $orderNo : $result", $userID);
             }
          }
        }
        if ($pctFromSale <= -3 or $pctFromSale >= 4){
          echo "<BR>% from sale! $pctFromSale CANCELLING!";
          if ($orderQtyRemaining == $orderQty){
            $cancelRslt = bittrexCancel($apiKey,$apiSecret,$uuid);
            if ($cancelRslt == 1){
              bittrexSellCancel($uuid, $transactionID);
              logToSQL("Bittrex", "Sell Order 3% Less or 4% above. Cancelling OrderNo: $orderNo", $userID);
              continue;
            }else{
              logAction("bittrexCancelSellOrder: ".$result, 'Bittrex');
              logToSQL("Bittrex", "Sell Order 3% Less or 4% above. Error cancelling OrderNo: $orderNo : $result", $userID);
            }
          }else{
            $result = bittrexCancel($apiKey,$apiSecret,$uuid);
            if ($result == 1){
              $newOrderNo = "ORD".$coin.date("YmdHis", time()).$ruleIDBTSell;
              //sendtoSteven($transactionID,"QTYRemaining: ".$orderQtyRemaining."_QTYSold: ".$qtySold."_OrderQTY: ".$orderQty."_UUID: ".$uuid, "NewOrderNo: ".$newOrderNo."_OrderNo: ".$orderNo, "SELL - Less -2 Greater 2.5");
              bittrexCopyTransNewAmount($transactionID,$qtySold,$orderQtyRemaining,$newOrderNo);
              bittrexSellComplete($uuid, $transactionID, $finalPrice);
              logToSQL("Bittrex", "Sell Order 3% Less or 4% above. Cancelling OrderNo: $orderNo | Creating new Transaction", $userID);
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
              logAction("bittrexCancelSellOrder: ".$result, 'Bittrex');
              logToSQL("Bittrex", "Sell Order 3% Less or 4% above. Error cancelling OrderNo: $orderNo : $result", $userID);
            }
          }
        }
      } //end $type Buy Sell
    }else{
      logAction("bittrexCheckOrder: ".$resultOrd["success"], 'Bittrex');
      logToSQL("Bittrex", "Check OrderNo: $orderNo Success:".$resultOrd["success"], $userID);
    }//end bittrex order check
    echo "<br> Profit Pct $liveProfitPct Live Coin Price: $liveCoinPriceBit cost $cost";
    echo "<br>Time Since Action ".substr($timeSinceAction,0,4);

    echo "<BR> ORDERQTY: $orderQty - OrderQTYREMAINING: $orderQtyRemaining";
  }//Bittrex Loop

  $coinAlerts = getCoinAlerts();
  $coinAlertsLength = count($coinAlerts);
  echo "<BR> CHECK Alerts!! ";
  //logAction("Check Alerts Start", 'BuySellTiming');
  for($d = 0; $d < $coinAlertsLength; $d++) {
    $id = $coinAlerts[$d][0];
    $coinID = $coinAlerts[$d][1]; $action = $coinAlerts[$d][2]; $price  = $coinAlerts[$d][3]; $symbol  = $coinAlerts[$d][4];
    $userName  = $coinAlerts[$d][5]; $email  = $coinAlerts[$d][6]; $liveCoinPrice = $coinAlerts[$d][7]; $category = $coinAlerts[$d][8];
    $Live1HrChangeAlrt = $coinAlerts[$d][9]; $Live24HrChangeAlrt = $coinAlerts[$d][10]; $Live7DChangeAlrt = $coinAlerts[$d][11];
    $reocurring = $coinAlerts[$d][12]; $dateTimeSent = $coinAlerts[$d][13]; $liveSellOrderAlert = $coinAlerts[$d][14];
    $liveBuyOrderAlert = $coinAlerts[$d][15];$liveMarketCapAlert = $coinAlerts[$d][16];
    $userID = $coinAlerts[$d][17];
    //$current_date = date('Y-m-d H:i');
    //$newTime = date("Y-m-d H:i",strtotime("-30 mins", strtotime($current_date)));
    //$dateFlag = ($newTime > $dateTimeSent);
    $minutes = (strtotime($dateTimeSent) - time()) / 60;
    //$newTimeAlrt = $dateTimeSent - $current_date ;
    Echo "<BR> Checking $symbol, $price, $action, $userName , $liveCoinPrice, $category, $dateTimeSent, $minutes, $reocurring, $Live1HrChangeAlrt";

    if ($action == 'LessThan' && $category == "Price"){
      if ($liveCoinPrice <= $price) {
        actionAlert($minutes,$email,$symbol,$price,$action,$userName,$category,$reocurring,$id);

      }
    } elseif ($action == 'GreaterThan' && $category == "Price"){
      if ($liveCoinPrice >= $price) {
        actionAlert($minutes,$email,$symbol,$price,$action,$userName,$category,$reocurring,$id,$userID);
        //logToSQL("Alerts", "Coin: $symbol $action $category $price | Live Price: $liveCoinPrice", $userID);
      }
    } elseif ($action == 'LessThan' && $category == "Pct Price in 1 Hour"){
      if ($Live1HrChangeAlrt <= $price) {
        actionAlert($minutes,$email,$symbol,$price,$action,$userName,$category,$reocurring,$id,$userID);
        //logToSQL("Alerts", "Coin: $symbol $action $category $price | Live Price: $liveCoinPrice", $userID);
      }
    } elseif ($action == 'GreaterThan' && $category == "Pct Price in 1 Hour"){
      if ($Live1HrChangeAlrt >= $price) {
        actionAlert($minutes,$email,$symbol,$price,$action,$userName,$category,$reocurring,$id,$userID);
        //logToSQL("Alerts", "Coin: $symbol $action $category $price | Live Price: $liveCoinPrice", $userID);
      }
    } elseif ($action == 'LessThan' && $category == "Market Cap Pct Change"){
      if ($liveMarketCapAlert <= $price) {
        actionAlert($minutes,$email,$symbol,$price,$action,$userName,$category,$reocurring,$id,$userID);
        //logToSQL("Alerts", "Coin: $symbol $action $category $price | Live Price: $liveCoinPrice", $userID);
      }
    } elseif ($action == 'GreaterThan' && $category == "Market Cap Pct Change"){
      if ($liveMarketCapAlert >= $price) {
        actionAlert($minutes,$email,$symbol,$price,$action,$userName,$category,$reocurring,$id,$userID);
        //logToSQL("Alerts", "Coin: $symbol $action $category $price | Live Price: $liveCoinPrice", $userID);
      }
    } elseif ($action == 'LessThan' && $category == "Buy Orders Pct Change"){
      if ($liveBuyOrderAlert <= $price) {
        actionAlert($minutes,$email,$symbol,$price,$action,$userName,$category,$reocurring,$id,$userID);
        //logToSQL("Alerts", "Coin: $symbol $action $category $price | Live Price: $liveCoinPrice", $userID);
      }
    } elseif ($action == 'GreaterThan' && $category == "Buy Orders Pct Change"){
      if ($liveBuyOrderAlert >= $price) {
        actionAlert($minutes,$email,$symbol,$price,$action,$userName,$category,$reocurring,$id,$userID);
        //logToSQL("Alerts", "Coin: $symbol $action $category $price | Live Price: $liveCoinPrice", $userID);
      }
    } elseif ($action == 'LessThan' && $category == "Sell Orders Pct Change"){
      if ($liveSellOrderAlert <= $price) {
        actionAlert($minutes,$email,$symbol,$price,$action,$userName,$category,$reocurring,$id,$userID);
        //logToSQL("Alerts", "Coin: $symbol $action $category $price | Live Price: $liveCoinPrice", $userID);
      }
    } elseif ($action == 'GreaterThan' && $category == "Sell Orders Pct Change"){
      if ($liveSellOrderAlert >= $price) {
        actionAlert($minutes,$email,$symbol,$price,$action,$userName,$category,$reocurring,$id,$userID);
        //logToSQL("Alerts", "Coin: $symbol $action $category $price | Live Price: $liveCoinPrice", $userID);
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
logAction("Buy Sell Coins End $date : $i", 'BuySellTiming');
//$to, $symbol, $amount, $cost, $orderNo, $score, $subject, $user, $from){
//sendEmail('stevenj1979@gmail.com',$i,0,$date,0,'BuySell Loop Finished', 'stevenj1979', 'Coin Purchase <purchase@investment-tracker.net>');
echo "<br>EndTime ".date("Y-m-d H:i:s", time());
?>
</html>
