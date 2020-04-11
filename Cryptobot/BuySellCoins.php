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
  echo $tmpTime;
  //error_log($argv[1], 0);
}
echo "<BR> isEmpty : ".empty($_GET['mins']);
if (!empty($_GET['mins'])){
  $tmpTime = str_replace('_', ' ', $_GET['mins']);
  echo "<br> GETMINS: ".$_GET['mins'];
}


//set time
date_default_timezone_set('Asia/Dubai');
$date = date("Y-m-d H:i", time());
$current_date = date('Y-m-d H:i');

$newTime = date("Y-m-d H:i",strtotime($tmpTime, strtotime($current_date)));

$buyRules = getUserRules();
$buyRulesSize = count($buyRules);
$sellRules = getUserSellRules();
$sellRulesSize = count($sellRules);
$i = 0;
$coins = getTrackingCoins();

$coinLength = Count($coins);

//echo "<br> coinLength= $coinLength NEWTime=".$newTime." StartTime $date EndTime $newTime";
while($date <= $newTime){
  echo "<BR> BUY COINS!! ";
  for($x = 0; $x < $coinLength; $x++) {
    //variables
    $coinID = $coins[$x][0]; $symbol = $coins[$x][1]; $baseCurrency = $coins[$x][26];
    $BuyOrdersPctChange = $coins[$x][4]; $MarketCapPctChange = $coins[$x][7]; $Hr1ChangePctChange = $coins[$x][10];
    $Hr24ChangePctChange = $coins[$x][13]; $D7ChangePctChange = $coins[$x][14]; $CoinPricePctChange = $coins[$x][19];
    $SellOrdersPctChange = $coins[$x][22]; $VolumePctChange = $coins[$x][25];
    $price4Trend = $coins[$x][27]; $price3Trend = $coins[$x][28]; $lastPriceTrend = $coins[$x][29];  $livePriceTrend = $coins[$x][30];
    $newPriceTrend = $price4Trend.$price3Trend.$lastPriceTrend.$livePriceTrend;
    $LiveCoinPrice = $coins[$x][17];
    //$timeToCancelBuyMins = $coins[$x][31];
    //LOG
    //echo "<br> i=$i CoinID=$coinID Coin=$symbol baseCurrency=$baseCurrency ";
    //echo "<blockquote>";
    for($y = 0; $y < $buyRulesSize; $y++) {
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
      $APISecret = $buyRules[$y][31];
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
      $limitToCoin = $buyRules[$y][52]; $autoBuyCoinEnabled = $buyRules[$y][53];$autoBuyPrice = $buyRules[$y][54];
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
      $test2 = buyWithScore($VolumeTop,$VolumeBtm,$VolumePctChange,$VolumeEnabled);
      $test3 = buyWithScore($BuyOrdersTop,$BuyOrdersBtm,$BuyOrdersPctChange,$BuyOrdersEnabled);
      $test4 = buyWithScore($Hr1ChangeTop,$Hr1ChangeBtm,$Hr1ChangePctChange,$Hr1ChangeEnabled);
      $test5 = buyWithScore($Hr24ChangeTop,$Hr24ChangeBtm,$Hr24ChangePctChange,$Hr24ChangeEnabled);
      $test6 = buyWithScore($D7ChangeTop,$D7ChangeBtm,$D7ChangePctChange,$D7ChangeEnabled);
      $test7 = buyWithScore($CoinPriceTop,$CoinPriceBtm,$CoinPricePctChange,$CoinPriceEnabled);
      $test8 = buyWithScore($SellOrdersTop,$SellOrdersBtm,$SellOrdersPctChange,$SellOrdersEnabled);
      $test9 = newBuywithPattern($newPriceTrend,$newBuyPattern,$priceTrendEnabled);
      $test10 = buyWithMin($BuyPriceMinEnabled,$BuyPriceMin,$LiveCoinPrice);
      $test11 = autoBuy($LiveCoinPrice,$autoBuyPrice, $autoBuyCoinEnabled);
      $test12 = $GLOBALS['allDisabled'];
      if (buyAmountOverride($buyAmountOverrideEnabled)){$BTCAmount = $buyAmountOverride; Echo "<BR> 13: BuyAmountOverride set to : $buyAmountOverride";}
      //logAction("1: $test1 2: $test2 3: $test3 4: $test4 5: $test5 6: $test6 7: $test7 8: $test8 9: $test9 10: $test10 11: $test11 12: $test12 ", 'BuySell');
      //Echo "<BR> New Boolean Test! 1: $test1 2: $test2 3: $test3 4: $test4 5: $test5 6: $test6 7: $test7 8: $test8 9: $test9 10: $test10 11: $test11 12: $test12 ";
      Echo "<BR> UserID: $userID | Coin : $symbol | 1: $test1 2: $test2 3: $test3 4: $test4 5: $test5 6: $test6 7: $test7 8: $test8 9: $test9 10: $test10 11: $test11 12: $test12 ";
      //if (buyWithScore($MarketCapTop,$MarketCapBtm,$MarketCapPctChange,$MarketCapEnabled)){
      //  echo "2: Volume buyWithScore($VolumeTop,$VolumeBtm,$VolumePctChange,$VolumeEnabled)<br>";
      //  if (buyWithScore($VolumeTop,$VolumeBtm,$VolumePctChange,$VolumeEnabled)){
      //    echo "3: BuyOrders buyWithScore($BuyOrdersTop,$BuyOrdersBtm,$BuyOrdersPctChange,$BuyOrdersEnabled)<br>";
      //    if (buyWithScore($BuyOrdersTop,$BuyOrdersBtm,$BuyOrdersPctChange,$BuyOrdersEnabled)){
              //logAction("buyWithScore($BuyOrdersTop,$BuyOrdersBtm,$BuyOrdersPctChange,$BuyOrdersEnabled)", 'BuySell');
      //        echo "4: 1HrChange buyWithScore($Hr1ChangeTop,$Hr1ChangeBtm,$Hr1ChangePctChange,$Hr1ChangeEnabled)<br>";
      //      if (buyWithScore($Hr1ChangeTop,$Hr1ChangeBtm,$Hr1ChangePctChange,$Hr1ChangeEnabled)){
      //          echo "5: 24HrChange buyWithScore($Hr24ChangeTop,$Hr24ChangeBtm,$Hr24ChangePctChange,$Hr24ChangeEnabled)<BR>";
      //        if (buyWithScore($Hr24ChangeTop,$Hr24ChangeBtm,$Hr24ChangePctChange,$Hr24ChangeEnabled)){
      //            echo "6: 7DChange buyWithScore($D7ChangeTop,$D7ChangeBtm,$D7ChangePctChange,$D7ChangeEnabled)<BR>";
      //          if (buyWithScore($D7ChangeTop,$D7ChangeBtm,$D7ChangePctChange,$D7ChangeEnabled)){
      //              echo "7: CoinPrice buyWithScore($CoinPriceTop,$CoinPriceBtm,$CoinPricePctChange,$CoinPriceEnabled)<BR>";
      //            if (buyWithScore($CoinPriceTop,$CoinPriceBtm,$CoinPricePctChange,$CoinPriceEnabled)){
      //                echo "8: SellOrders buyWithScore($SellOrdersTop,$SellOrdersBtm,$SellOrdersPctChange,$SellOrdersEnabled)<BR>";
      //              if (buyWithScore($SellOrdersTop,$SellOrdersBtm,$SellOrdersPctChange,$SellOrdersEnabled)){
      //                echo "9: PriceTrend newBuywithPattern($newPriceTrend,$newBuyPattern,$priceTrendEnabled)<BR>";
      //                  //if (buywithPattern($price4Trend,$price3Trend,$lastPriceTrend,$livePriceTrend,$price4TrendTrgt,$price3TrendTrgt,$lastPriceTrendTrgt,$livePriceTrendTrgt,$priceTrendEnabled)){
      //                  if(newBuywithPattern($newPriceTrend,$newBuyPattern,$priceTrendEnabled)){
      //                    logAction("PriceTrend newBuywithPattern($newPriceTrend,$newBuyPattern,$priceTrendEnabled)", 'BuySell');
      //                    echo "10: BuyPriceMinEnabled $BuyPriceMinEnabled BuyPriceMin $BuyPriceMin LiveCoinPrice $LiveCoinPrice LiveBTCPrice $LiveBTCPrice<BR>";
      //                    if (buyWithMin($BuyPriceMinEnabled,$BuyPriceMin,$LiveCoinPrice)){
      //                      logAction("buyWithMin($BuyPriceMinEnabled,$BuyPriceMin,$LiveCoinPrice)", 'BuySell');
      //                      echo "<BR> 11: buyWithMin($BuyPriceMinEnabled,$BuyPriceMin,$LiveCoinPrice)";
      //                      if (autoBuy($LiveCoinPrice,$autoBuyPrice, $autoBuyCoinEnabled)){
      //                        logAction("autoBuy($LiveCoinPrice,$autoBuyPrice, $autoBuyCoinEnabled)", 'BuySell');
      //                        echo "<BR> 12: autoBuy($LiveCoinPrice,$autoBuyPrice, $autoBuyCoinEnabled)";
      //                        if (buyAmountOverride($buyAmountOverrideEnabled)){$BTCAmount = $buyAmountOverride; Echo "<BR> 13: BuyAmountOverride set to : $buyAmountOverride";}
      //                        if ($GLOBALS['allDisabled'] == true){
        //                        logAction("GLOBALS['allDisabled'] : ".$GLOBALS['allDisabled'], 'BuySell');
      if ($test1 == True && $test2 == True && $test3 == True && $test4 == True && $test5 == True && $test6 == True && $test7 == True && $test8 == True && $test9 == True && $test10 == True &&
      $test11 == True && $test12 == True){
        $date = date("Y-m-d H:i:s", time());
        echo "<BR>Buying Coins: $APIKey, $APISecret,$symbol, $Email, $userID, $date, $baseCurrency,$SendEmail,$BuyCoin,$BTCAmount, $ruleIDBuy,$UserName,$coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed";
        buyCoins($APIKey, $APISecret,$symbol, $Email, $userID, $date, $baseCurrency,$SendEmail,$BuyCoin,$BTCAmount, $ruleIDBuy,$UserName,$coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed);
        logAction("buyCoins($APIKey,$symbol, $Email, $userID, $date, $baseCurrency,$SendEmail,$BuyCoin,$BTCAmount, $ruleIDBuy,$UserName,$coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed)", 'BuySell');
        $buyCounter = $buyCounter + 1;
      }
                                //break;
        //                      }

        //                    }
        //                  }
        //                }
        //            }
        //          }
        //        }
        //      }
        //    }
        //  }
        //}
      //}
      echo "<BR> NEXT RULE <BR>";
    }//Rule Loop
    echo "</blockquote>";
  }//Coin Loop
  echo "<BR> SELL COINS!! ";
  echo "<blockquote>";
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

    $price4Trend = $sellCoins[$a][37]; $price3Trend = $sellCoins[$a][38]; $lastPriceTrend = $sellCoins[$a][39];  $livePriceTrend = $sellCoins[$a][40];
    //$BuyRuleLength = strlen($orderNo - 20);
    //$BuyRuleLength = $BuyRuleLength-$BuyRuleLength-$BuyRuleLength;

    for($z = 0; $z < $sellRulesSize; $z++) {//Sell Rules

      //Variables
      $BuyOrdersEnabled = $sellRules[$z][4]; $BuyOrdersTop = $sellRules[$z][5]; $BuyOrdersBtm = $sellRules[$z][6];
      $MarketCapEnabled = $sellRules[$z][7]; $MarketCapTop = $sellRules[$z][8];$MarketCapBtm= $sellRules[$z][9];
      $Hr1ChangeEnabled = $sellRules[$z][10]; $Hr1ChangeTop = $sellRules[$z][11]; $Hr1ChangeBtm = $sellRules[$z][12];
      $Hr24ChangeEnabled = $sellRules[$z][13]; $Hr24ChangeTop = $sellRules[$z][14]; $Hr24ChangeBtm = $sellRules[$z][15];
      $D7ChangeEnabled = $sellRules[$z][16]; $D7ChangeTop = $sellRules[$z][17]; $D7ChangeBtm = $sellRules[$z][18];
      $ProfitPctEnabled = $sellRules[$z][19]; $ProfitPctTop = $sellRules[$z][20];  $ProfitPctBtm = $sellRules[$z][21];
      $CoinPriceEnabled = $sellRules[$z][22]; $CoinPriceTop = $sellRules[$z][23]; $CoinPriceBtm = $sellRules[$z][24];
      $SellOrdersEnabled = $sellRules[$z][25]; $SellOrdersTop = $sellRules[$z][26]; $SellOrdersBtm = $sellRules[$z][27];
      $VolumeEnabled = $sellRules[$z][28]; $VolumeTop = $sellRules[$z][29]; $VolumeBtm = $sellRules[$z][30];
      $SellCoin = $sellRules[$z][2]; $SendEmail = $sellRules[$z][3];
      $Email = $sellRules[$z][31]; $UserName = $sellRules[$z][32]; $APIKey = $sellRules[$z][33];
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
      //Echo "MarketCap $marketCapTop,$marketCapBtm,$marketCapbyPct,$marketCapEnable <BR>";
      $sTest1 = sellWithScore($MarketCapTop,$MarketCapBtm,$MarketCapPctChange,$MarketCapEnabled);
      $sTest2 = sellWithScore($VolumeTop,$VolumeBtm,$VolumePctChange,$VolumeEnabled);
      $sTest3 = sellWithScore($SellOrdersTop,$SellOrdersBtm,$SellOrdersPctChange,$SellOrdersEnabled);
      $sTest4 = sellWithScore($Hr1ChangeTop,$Hr1ChangeBtm,$Hr1ChangePctChange,$Hr1ChangeEnabled);
      $sTest5 = sellWithScore($Hr24ChangeTop,$Hr24ChangeBtm,$Hr24ChangePctChange,$Hr24ChangeEnabled);
      $sTest6 = sellWithScore($D7ChangeTop,$D7ChangeBtm,$D7ChangePctChange,$D7ChangeEnabled);
      $sTest7 = newBuywithPattern($price4Trend.$price3Trend.$lastPriceTrend.$livePriceTrend,$newSellPattern,$priceTrendEnabled);
      $sTest8 = sellWithMin($sellPriceMinEnabled,$sellPriceMin,$LiveCoinPrice,$LiveBTCPrice);
      $sTest9 = sellWithScore($ProfitPctTop,$ProfitPctBtm,$profit,$ProfitPctEnabled);
      $sTest10 = sellWithScore($CoinPriceTop,$CoinPriceBtm,$CoinPricePctChange,$CoinPriceEnabled);
      $sTest11 = $GLOBALS['allDisabled'];

      $buyPrice = ($cost * $amount);
      $sellPrice = ($LiveCoinPrice * $amount);
      $fee = (($LiveCoinPrice * $amount)/100)*0.25;
      $profit = (($sellPrice-$fee)-$buyPrice)/$buyPrice*100;
      Echo "<BR> UserID: $userID | Coin : $symbol | 1: $sTest1 2: $sTest2 3: $sTest3 4: $sTest4 5: $sTest5 6: $sTest6 7: $sTest7 8: $sTest8 9: $sTest9 10: $sTest10 11: $sTest11";
      //   echo "<br>1: MarketCap sellWithScore($MarketCapTop,$MarketCapBtm,$MarketCapPctChange,$MarketCapEnabled)";
      //  if (sellWithScore($MarketCapTop,$MarketCapBtm,$MarketCapPctChange,$MarketCapEnabled)){
      //    echo "<br>2: Volume sellWithScore($VolumeTop,$VolumeBtm,$VolumePctChange,$VolumeEnabled)";
      //    if (sellWithScore($VolumeTop,$VolumeBtm,$VolumePctChange,$VolumeEnabled)){
      //      echo "<br>3: SellOrders sellWithScore($SellOrdersTop,$SellOrdersBtm,$SellOrdersPctChange,$SellOrdersEnabled)";
      //      if (sellWithScore($SellOrdersTop,$SellOrdersBtm,$SellOrdersPctChange,$SellOrdersEnabled)){
      //        echo "<br>4: 1Hr Price Change sellWithScore($Hr1ChangeTop,$Hr1ChangeBtm,$Hr1ChangePctChange,$Hr1ChangeEnabled)";
      //        if (sellWithScore($Hr1ChangeTop,$Hr1ChangeBtm,$Hr1ChangePctChange,$Hr1ChangeEnabled)){
      //          echo "<br>5: 24Hr Price Change sellWithScore($Hr24ChangeTop,$Hr24ChangeBtm,$Hr24ChangePctChange,$Hr24ChangeEnabled)";
      //          if (sellWithScore($Hr24ChangeTop,$Hr24ChangeBtm,$Hr24ChangePctChange,$Hr24ChangeEnabled)){
      //            echo "<br>6: 7D Price Change sellWithScore($D7ChangeTop,$D7ChangeBtm,$D7ChangePctChange,$D7ChangeEnabled)";
      //            if (sellWithScore($D7ChangeTop,$D7ChangeBtm,$D7ChangePctChange,$D7ChangeEnabled)){
      //              echo "<br>7: Sell With Score sellWithScore($D7ChangeTop,$D7ChangeBtm,$D7ChangePctChange,$D7ChangeEnabled)";
      //              if(newBuywithPattern($price4Trend.$price3Trend.$lastPriceTrend.$livePriceTrend,$newSellPattern,$priceTrendEnabled)){
      //                echo "<br>8: Sell with Pattern newBuywithPattern($price4Trend.$price3Trend.$lastPriceTrend.$livePriceTrend,$newSellPattern,$priceTrendEnabled)";
      //                if (sellWithMin($sellPriceMinEnabled,$sellPriceMin,$LiveCoinPrice,$LiveBTCPrice)){

      //                  echo "<br>9: Profit $ProfitPctTop,$ProfitPctBtm,$profit,$ProfitPctEnabled";
      //                  if (sellWithScore($ProfitPctTop,$ProfitPctBtm,$profit,$ProfitPctEnabled)){
      //                      echo "<br>10: PriceDiff1 $CoinPriceTop,$CoinPriceBtm,$CoinPricePctChange,$CoinPriceEnabled";
      //                      if (sellWithScore($CoinPriceTop,$CoinPriceBtm,$CoinPricePctChange,$CoinPriceEnabled)){
                              //echo "<br>10 PriceDiff2 ";

      //                        if ($GLOBALS['allDisabled'] == true){
                                //print_r(" Sell Sell Sell!!");
                                //sendEmail($email, $coin, $quantity, $bitPrice, "Testing : ".$z, $totalScore);
                              if ($sTest1 == True && $sTest2 == True && $sTest3 == True && $sTest4 == True && $sTest5 == True && $sTest6 == True && $sTest7 == True && $sTest8 == True && $sTest9 == True && $sTest10 == True &&
                                $sTest11 == True){
                                $date = date("Y-m-d H:i:s", time());
                                echo "<BR>Sell Coins: $APIKey, $APISecret,$coin, $Email, $userID, 0,$date, $BaseCurrency,$SendEmail,$SellCoin, _.$ruleIDSell,$UserName,$orderNo,$amount,$cost,$transactionID,$coinID<BR>";
                                //sellCoins($apikey, $apisecret, $coin, $email, $userID, $score, $date,$baseCurrency, $sendEmail, $sellCoin, $ruleID,$userName, $orderNo,$amount,$cost,$transactionID,$coinID){
                                sellCoins($APIKey, $APISecret,$coin, $Email, $userID, 0,$date, $BaseCurrency,$SendEmail,$SellCoin, $ruleIDSell,$UserName,$orderNo,$amount,$cost,$transactionID,$coinID,$sellCoinOffsetEnabled,$sellCoinOffsetPct,$LiveCoinPrice);
                                logAction("sellCoins($APIKey, $APISecret,$coin, $Email, $userID, 0,$date, $BaseCurrency,$SendEmail,$SellCoin, $ruleIDSell,$UserName,$orderNo,$amount,$cost,$transactionID,$coinID,$sellCoinOffsetEnabled,$sellCoinOffsetPct,$LiveCoinPrice)",'BuySell');
                                //break;
                                //addSellRuletoSQL()
                              }

      //                      }
      //                  }
      //                }
      //              }
      //            }
      //          }
      //        }
      //      }
      //    }
      //}
      echo "<BR> NEXT RULE <BR>";
    }//Sell Rules

  }//Sell Coin Loop
  echo "</blockquote>";
    echo "<BR> CHECK BITTREX!! ";
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
             }
          }else{
            $result = bittrexCancel($apiKey,$apiSecret,$uuid);
            if ($result == 1){
              bittrexUpdateBuyQty($transactionID, $orderQty-$orderQtyRemaining);
              if ($sendEmail){
                $subject = "Coin Purchase1: ".$coin;
                $from = 'Coin Purchase <purchase@investment-tracker.net>';
                sendEmail($email, $coin, $amount, $cost, $orderNo, $totalScore, $subject,$userName,$from);
              }
              bittrexBuyComplete($uuid, $transactionID, $finalPrice); //add buy price - $finalPrice
              //addBuyRuletoSQL($transactionID, $ruleIDBTBuy);
            }
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
              continue;
            }
          }else{
             $result = bittrexCancel($apiKey,$apiSecret,$uuid);
             if ($result == 1){
               $newOrderNo = "ORD".$coin.date("YmdHis", time()).$ruleIDBTSell;
               //sendtoSteven($transactionID,$orderQtyRemaining."_".$qtySold."_".$orderQty, $newOrderNo."_".$orderNo, "SELL - Greater 28 days");
               bittrexCopyTransNewAmount($transactionID,$orderQtyRemaining,$newOrderNo);
               //Update QTY
               bittrexUpdateSellQty($transactionID,$qtySold);
               bittrexSellCancel($uuid, $transactionID);

               if ($sendEmail){
                 $subject = "Coin Sale: ".$coin." RuleID:".$ruleIDBTSell." Qty: ".$orderQty." : ".$orderQtyRemaining;
                 $from = 'Coin Sale <sale@investment-tracker.net>';
                 sendSellEmail($email, $coin, $orderQty-$orderQtyRemaining, $finalPrice, $orderNo, $totalScore,$profitPct,$profit,$subject,$userName,$from);
               }
               continue;
             }
          }
        }
        if ($pctFromSale <= -3 or $pctFromSale >= 4){
          echo "<BR>% from sale! $pctFromSale CANCELLING!";
          if ($orderQtyRemaining == $orderQty){
            $cancelRslt = bittrexCancel($apiKey,$apiSecret,$uuid);
            if ($cancelRslt == 1){
              bittrexSellCancel($uuid, $transactionID);
              continue;
            }
          }else{
            $result = bittrexCancel($apiKey,$apiSecret,$uuid);
            if ($result == 1){
              $newOrderNo = "ORD".$coin.date("YmdHis", time()).$ruleIDBTSell;
              //sendtoSteven($transactionID,"QTYRemaining: ".$orderQtyRemaining."_QTYSold: ".$qtySold."_OrderQTY: ".$orderQty."_UUID: ".$uuid, "NewOrderNo: ".$newOrderNo."_OrderNo: ".$orderNo, "SELL - Less -2 Greater 2.5");
              bittrexCopyTransNewAmount($transactionID,$orderQtyRemaining,$newOrderNo);
              //Update QTY
              bittrexUpdateSellQty($transactionID,$qtySold);
              bittrexSellCancel($uuid, $transactionID);

              if ($sendEmail){
                $subject = "Coin Sale2: ".$coin." RuleID:".$ruleIDBTSell." Qty: ".$orderQty." : ".$orderQtyRemaining;
                $from = 'Coin Sale <sale@investment-tracker.net>';
                //$debug = "$uuid : $transactionID - $orderQtyRemaining + $qtySold / $pctFromSale ! $liveProfitPct";
                sendSellEmail($email, $coin, $orderQty-$orderQtyRemaining, $finalPrice, $orderNo, $totalScore,$profitPct,$profit,$subject,$userName,$from);
              }
              continue;
            }
          }
        }
      } //end $type Buy Sell
    } //end bittrex order check
    echo "<br> Profit Pct $liveProfitPct Live Coin Price: $liveCoinPriceBit cost $cost";
    echo "<br>Time Since Action ".substr($timeSinceAction,0,4);

    echo "<BR> ORDERQTY: $orderQty - OrderQTYREMAINING: $orderQtyRemaining";
  }//Bittrex Loop

  echo "</blockquote>";
  sleep(15);
  $i = $i+1;
  $date = date("Y-m-d H:i:s", time());
}//end While
//$to, $symbol, $amount, $cost, $orderNo, $score, $subject, $user, $from){
//sendEmail('stevenj1979@gmail.com',$i,0,$date,0,'BuySell Loop Finished', 'stevenj1979', 'Coin Purchase <purchase@investment-tracker.net>');
echo "<br>EndTime ".date("Y-m-d H:i:s", time());
?>
</html>
