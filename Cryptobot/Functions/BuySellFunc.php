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


function runSellCoins($sellRules,$sellCoins,$userProfit,$coinPriceMatch,$coinPricePatternList,$coin1HrPatternList,$autoBuyPrice){
  $finalBool = False; $apiVersion = 3;
  $sellRulesSize = count($sellRules);
  $sellCoinsLength = count($sellCoins);
  Echo "<BR> HERE! $sellCoinsLength";
  for($a = 0; $a < $sellCoinsLength; $a++) {
    //Variables
    $coin = $sellCoins[$a][11]; $MarketCapPctChange = $sellCoins[$a][17]; $VolumePctChange = $sellCoins[$a][26];
    $SellOrdersPctChange = $sellCoins[$a][23]; $Hr1ChangePctChange = $sellCoins[$a][29]; $Hr24ChangePctChange = $sellCoins[$a][34];
    $D7ChangePctChange = $sellCoins[$a][34]; $LiveCoinPrice = $sellCoins[$a][19]; $CoinPricePctChange = $sellCoins[$a][20];
    $BaseCurrency = $sellCoins[$a][36]; $orderNo = $sellCoins[$a][10]; $amount = $sellCoins[$a][5]; $cost = $sellCoins[$a][4];
    $transactionID = $sellCoins[$a][0]; $coinID = $sellCoins[$a][2]; $sellCoinsUserID = $sellCoins[$a][3];
    $fixSellRule = $sellCoins[$a][41]; $BuyRule = $sellCoins[$a][43];
    $lowPricePurchaseEnabled = $sellCoins[$a][45]; $purchaseLimit = $sellCoins[$a][46]; $pctToPurchase = $sellCoins[$a][47]; $btcBuyAmountSell = $sellCoins[$a][48];
    $noOfPurchases = $sellCoins[$a][49]; $toMerge = $sellCoins[$a][44]; $orderDate = $sellCoins[$a][7];
    $noOfCoinSwapsThisWeek  = $sellCoins[$a][53]; $captureTrend = $sellCoins[$a][59]; $minsFromBuy = $sellCoins[$a][61];
    $price4Trend = $sellCoins[$a][37]; $price3Trend = $sellCoins[$a][38]; $lastPriceTrend = $sellCoins[$a][39];  $livePriceTrend = $sellCoins[$a][40];
    $priceDipHours = $sellCoins[$a][62]; $priceDipMaxPrice = $sellCoins[$a][63]; $multiSellRuleEnabled = $sellCoins[$a][65];$hoursSinceBuy =$sellCoins[$a][66];
    //Echo "<BR> HERE2! $sellRulesSize";
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
      $userID = $sellRules[$z][1]; $ruleIDSell = $sellRules[$z][0];
      $sellCoinOffsetEnabled = $sellRules[$z][35]; $sellCoinOffsetPct = $sellRules[$z][36];
      $sellPriceMinEnabled = $sellRules[$z][37]; $sellPriceMin = $sellRules[$z][38];
      $KEKSell = $sellRules[$z][40];
      $priceTrendEnabled = $sellRules[$z][41]; $newSellPattern = $sellRules[$z][42];
      $limitToBuyRule = $sellRules[$z][43];
      $pctUnderMaxPrice = $sellRules[$z][58];
      $finalPriceDipMaxPrice = $priceDipMaxPrice - (($priceDipMaxPrice/100)*$pctUnderMaxPrice);
      $sellAllCoinsEnabled = $sellRules[$z][48]; $sellAllCoinsPct = $sellRules[$z][49];
      $priceDipMaxPriceEnabled = $sellRules[$z][55]; $priceDipCoinFlatEnabled = $sellRules[$z][56]; $priceDipHoursFlatTarget = $sellRules[$z][57];
      $hoursPastBuySellEnable  = $sellRules[$z][59];$hoursPastBuy = $sellRules[$z][60];
      $calculatedSellPctEnable = $sellRules[$z][61];$calculatedSellPctStart = $sellRules[$z][62];$calculatedSellPctEnd = $sellRules[$z][63];$calculatedSellPctDays = $sellRules[$z][64];
      if ($calculatedSellPctEnable == 1){
        $ProfitPctTop_Sell = $calculatedSellPctStart - ($hoursSinceBuy * ($calculatedSellPctStart-$calculatedSellPctEnd)/$calculatedSellPctDays);
        $ProfitPctBtm_Sell = -99.99;
        Echo "<BR>Calculated Sell Pct Enabnled:  $ProfitPctTop_Sell";
      }
      $profitNum = findUserProfit($userProfit,$userID);
      $coinSwapEnabled = $sellRules[$z][50]; $coinSwapAmount = $sellRules[$z][51]; $noOfCoinSwapsPerWeek = $sellRules[$z][52];
      echo "<BR> Starting: $coin | $ruleIDSell";
      if ($sellAllCoinsEnabled == 1 and $profitNum <= $sellAllCoinsPct){assignNewSellID($transactionID, 25);}//else{Echo "<BR> HERE3!";}
      if ($limitToBuyRule == "ALL"){ $limitToBuyRuleEnabled = 0;}else{$limitToBuyRuleEnabled = 1;}
      echo "<BR> PlaceHolder: 1";
      if ($multiSellRuleEnabled == 1){
          $multiSellRules = getMultiSellRules($transactionID);
          $multiSellResult = checkMultiSellRules($ruleIDSell,$multiSellRules);
          //echo "<BR> PlaceHolder: 1A Checking MultiSell";
          if ($multiSellResult == False){ echo "Exit: No1 | $coin | $userID | $ruleIDSell | $multiSellResult"; continue;} else{echo "<BR>FoundSellRule: $coin | $userID | $ruleIDSell | $multiSellResult";}
      }else{
          if ($fixSellRule != "ALL" && (int)$fixSellRule != $ruleIDSell){continue;}//else{Echo "<BR> HERE4!";}  //echo "Exit: No2 | $coin | $userID | $BuyRule";
      }
      echo "<BR> PlaceHolder: 2";
      if (!Empty($KEKSell)){ $apisecret = Decrypt($KEKSell,$sellRules[$z][34]);}//else{Echo "<BR> HERE5!";}
      $LiveBTCPrice = number_format((float)(bittrexCoinPrice($APIKey, $apisecret,$BaseCurrency,$coin,$apiVersion)), 8, '.', '');
      $limitToCoinSell = $sellRules[$z][39];
      $buyPrice = ($cost * $amount);
      $sellPrice = ($LiveCoinPrice * $amount);
      $fee = (($LiveCoinPrice * $amount)/100)*0.25;
      $profit = ((($sellPrice-$fee)-$buyPrice)/$buyPrice)*100;
      echo "<BR> PlaceHolder: 3";
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
      echo "<BR> PlaceHolder: 4";
      if ($userID != $sellCoinsUserID){ continue; } //echo "Exit: No3 | $coin | $userID | $BuyRule";
      if ($limitToCoinSell != "ALL" && $coin != $limitToCoinSell) { echo "Exit: No4 | $coin | $userID | $ruleIDSell | $limitToCoinSell";continue;}
      //echo "<BR> PlaceHolder: 5";
      $current_date = date('Y-m-d H:i');
      $threeWeeksAgoDate = date("Y-m-d H:i",strtotime("-3 week", strtotime($current_date)));
      if ($coinSwapEnabled == 1 and $noOfCoinSwapsPerWeek > $noOfCoinSwapsThisWeek){
        $now = time();
        $your_date = strtotime($orderDate);
        $datediff = $now - $your_date;
        $daysSinceCoinPurchase = round($datediff / (60 * 60 * 24));
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
      echo "<BR>Checking:  $coin | $userID | $ruleIDSell";
      $GLOBALS['allDisabled'] = false;
      $sTest12 = false;

      $sTest1 = sellWithScore($MarketCapTop,$MarketCapBtm,$MarketCapPctChange,$MarketCapEnabled);
      $sellResultAry[] = Array($sTest1, "Market Cap $coin", $MarketCapPctChange);
      //Echo "<BR> sTEST1: $sTest1";
      $sTest2 = sellWithScore($VolumeTop,$VolumeBtm,$VolumePctChange,$VolumeEnabled);
      $sellResultAry[] = Array($sTest2, "Volume $coin", $VolumePctChange);
      $sTest3 = sellWithScore($SellOrdersTop,$SellOrdersBtm,$SellOrdersPctChange,$SellOrdersEnabled);
      $sellResultAry[] = Array($sTest3, "Sell Orders $coin", $SellOrdersPctChange);
      $sTest4 = sellWithScore($Hr1ChangeTop,$Hr1ChangeBtm,$Hr1ChangePctChange,$Hr1ChangeEnabled);
      //Echo "<BR> 1Hour % $Hr1ChangePctChange ";
      $sellResultAry[] = Array($sTest4, "1 Hour Price Change $coin", $Hr1ChangePctChange);
      $sTest5 = sellWithScore($Hr24ChangeTop,$Hr24ChangeBtm,$Hr24ChangePctChange,$Hr24ChangeEnabled);
      $sellResultAry[] = Array($sTest5, "24 Hour Price Change $coin", $Hr24ChangePctChange);
      $sTest6 = sellWithScore($D7ChangeTop,$D7ChangeBtm,$D7ChangePctChange,$D7ChangeEnabled);
      $sellResultAry[] = Array($sTest6, "7 Day Price Change $coin", $D7ChangePctChange);
      if ($priceTrendEnabled){
          $sTest7 = newBuywithPattern($price4Trend.$price3Trend.$lastPriceTrend.$livePriceTrend,$coinPricePatternList,$priceTrendEnabled,$ruleIDSell,1);
      }else{ $sTest7 = True;}
      //echo "Exit: No6 | $coin | $userID | $BuyRule";
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
      //Echo "<BR> Hours Flat  $priceDipHours $priceDipHoursFlatTarget";
      $sTest14 = checkPriceDipCoinFlat($priceDipCoinFlatEnabled,$priceDipHoursFlatTarget, $priceDipHours);
      $sellResultAry[] = Array($sTest14, "Coin Price Match $coin", $LiveCoinPrice);
      $sTest15 = sellWithMin($priceDipMaxPriceEnabled,$finalPriceDipMaxPrice,$LiveCoinPrice,$LiveBTCPrice);
      $sellResultAry[] = Array($sTest15, "Coin Price Match $coin", $LiveCoinPrice);
      $sTest16 = sellWithMin($hoursPastBuySellEnable,$hoursPastBuy,$hoursSinceBuy,$hoursSinceBuy);
      $sellResultAry[] = Array($sTest16, "Hours Since Buy $coin", $hoursSinceBuy);
      //Echo "<BR> TEST: sellWithScore($ProfitPctTop_Sell,$ProfitPctBtm_Sell,$profit,$ProfitPctEnabled);";
      //$sellOutstanding = getOutStandingBuy($sellResultAry);
      $totalScore_Sell = $sTest1+$sTest2+$sTest3+$sTest4+$sTest5+$sTest6+$sTest7+$sTest8+$sTest9+$sTest10+$sTest11+$sTest12+$sTest13+$sTest14+$sTest15+$sTest16;
      Echo "<BR> UserID: $userID | RuleID: $ruleIDSell | Coin : $coin | 1:$sTest1  2:$sTest2  3:$sTest3  4:$sTest4  5:$sTest5  6:$sTest6  7:$sTest7  8:$sTest8 ";
      echo "9:$sTest9  10:$sTest10  11:$sTest11  12:$sTest12 13:$sTest13 14:$sTest14 15:$sTest15 16:$sTest16 TOTAL:$totalScore_Sell / 16, PROFIT:$profit MinsFromBuy:$minsFromBuy";
      if ($totalScore_Sell >= 15){
        $sellOutstanding = getOutStandingBuy($sellResultAry);
        logAction("UserID: $userID | RuleID: $ruleIDSell | Coin : $coin | 1:  $sTest1  2:  $sTest2  3:  $sTest3  4:  $sTest4  5:  $sTest5  6:  $sTest6  7:  $sTest7  8:  $sTest8","SellScore", $GLOBALS['logToFileSetting'] );
        logAction("9:  $sTest9  10:  $sTest10  11:  $sTest11  12:  $sTest12 13: $sTest13 14: $sTest14 15: $sTest15 16: $sTest16 TOTAL:  $totalScore_Sell / 16, PROFIT: $profit $sellOutstanding","SellScore", $GLOBALS['logToFileSetting'] );
        //logToSQL("SellCoins", "RuleID: $ruleIDSell | Coin : $coin | TOTAL: $totalScore_Sell $sellOutstanding", $userID, $GLOBALS['logToSQLSetting']);
      }


      if ($sTest1 == True && $sTest2 == True && $sTest3 == True && $sTest4 == True && $sTest5 == True && $sTest6 == True && $sTest7 == True && $sTest8 == True && $sTest9 == True && $sTest10 == True
      && $sTest11 == True && $sTest12 == True && $sTest13 == True  && $sTest14 == True && $sTest15 == True && $sTest16 == True && $minsFromBuy > 25){
        $date = date("Y-m-d H:i:s", time());
        echo "<BR>Sell Coins: $APIKey, $apisecret,$coin, $Email, $userID, 0,$date, $BaseCurrency,$SendEmail,$SellCoin, _.$ruleIDSell,$UserName,$orderNo,$amount,$cost,$transactionID,$coinID<BR>";
        //sellCoins($apikey, $apisecret, $coin, $email, $userID, $score, $date,$baseCurrency, $sendEmail, $sellCoin, $ruleID,$userName, $orderNo,$amount,$cost,$transactionID,$coinID){
        //sellCoins($APIKey, $APISecret,$coin, $Email, $userID, 0,$date, $BaseCurrency,$SendEmail,$SellCoin, $ruleIDSell,$UserName,$orderNo,$amount,$cost,$transactionID,$coinID,$sellCoinOffsetEnabled,$sellCoinOffsetPct,$LiveCoinPrice);
        newTrackingSellCoins($LiveCoinPrice,$userID, $transactionID,$SellCoin, $SendEmail,$sellCoinOffsetEnabled,$sellCoinOffsetPct,$fallsInPrice,'Sell','RunSellCoins');
        setTransactionPending($transactionID);
        //addWebUsage($userID,"Remove","SellCoin");
        //addWebUsage($userID,"Add","SellTracking");
        logAction("sellCoins($APIKey, $apisecret,$coin, $Email, $userID, 0,$date, $BaseCurrency,$SendEmail,$SellCoin, $ruleIDSell,$UserName,$orderNo,$amount,$cost,$transactionID,$coinID,$sellCoinOffsetEnabled,$sellCoinOffsetPct,$LiveCoinPrice)",'BuySell', $GLOBALS['logToFileSetting'] );
        logAction("UserID: $userID | Coin : $coin | 1: $sTest1 2: $sTest2 3: $sTest3 4: $sTest4 5: $sTest5 6: $sTest6 7: $sTest7 8: $sTest8 9: $sTest9 10: $sTest10 11: $sTest11",'BuySell', $GLOBALS['logToFileSetting'] );
        newLogToSQL("SellCoins", "newTrackingSellCoins($LiveCoinPrice,$userID, $transactionID,$SellCoin, $SendEmail,$sellCoinOffsetEnabled,$sellCoinOffsetPct,$fallsInPrice,'Sell');", $userID, 1,"AddTrackingSellCoin","TransactionID:$transactionID");
        newLogToSQL("SellCoins", "setTransactionPending($transactionID);", $userID, $GLOBALS['logToSQLSetting'],"setTransactionPending","TransactionID:$transactionID");
        //break;
        $to_time = date("Y-m-d H:i:s", time());
        $from_time = strtotime($orderDate);
        $holdingMins = round(abs($to_time - $from_time) / 60,2);
        logHoldingTimeToSQL($coinID, $holdingMins);
        logAction("runSellCoins; newTrackingSellCoins : $coin | $coinID | $amount | $cost | $LiveCoinPrice | $BaseCurrency | $userID | $transactionID", 'BuySellFlow', 1);
        $finalBool = True;
        //addSellRuletoSQL()
      }
      echo "<BR> NEXT RULE <BR>";
    } //Sell Rules
    //$BTCBalance = bittrexbalance($APIKey, $apisecret,$BaseCurrency, $apiVersion);
    $buyPrice = ($cost * $amount);
    $sellPrice = ($LiveCoinPrice * $amount);
    $fee = (($LiveCoinPrice * $amount)/100)*0.25;
    $profit = ((($sellPrice-$fee)-$buyPrice)/$buyPrice)*100;
    //echo "<BR> TESTING: Profit $profit PctToPurchase $pctToPurchase LowPricePurchaseEnabled $lowPricePurchaseEnabled NoOfPurchases $noOfPurchases PurchaseLimit $purchaseLimit ToMerge $toMerge";
    //if ($profit <= $pctToPurchase  && $BTCBalance >= 20 && $lowPricePurchaseEnabled == 1 && $noOfPurchases < $purchaseLimit && $toMerge == 0 && $mergeCoinEnabled == 1){
      //Buy Coin
      /*if($btcBuyAmountSell <= 0 ){ continue;}
      addTrackingCoin($coinID, $LiveCoinPrice, $userID, $baseCurrency, $SendEmail, 1, $btcBuyAmountSell, 999991, 0, 0, 1, 90, $fixSellRule,1,$noOfPurchases,1,'Buy',$LiveCoinPrice,0,0,0);
      echo "<BR> TEST New Buy Coin addTrackingCoin($coinID, $LiveCoinPrice, $userID, $baseCurrency, $SendEmail, 1, $btcBuyAmountSell, 999991, 0, 0, 1, 90, $fixSellRule, 1);";
      newLogToSQL("SellCoins", "addTrackingCoin($coinID, $LiveCoinPrice, $userID, $baseCurrency, $SendEmail, 1, $btcBuyAmountSell, 999991, 0, 0, 1, 90, $fixSellRule,1,$noOfPurchases);", $userID, $GLOBALS['logToSQLSetting'],"MergeCoins","TransactionID:$transactionID");
      //Update ToMerge
      updateTrackingCoinToMerge($transactionID,$noOfPurchases);
      newLogToSQL("SellCoins", "updateTrackingCoinToMerge($transactionID,$noOfPurchases);", $userID, $GLOBALS['logToSQLSetting'],"MergeCoins","TransactionID:$transactionID");*/
    //}
  }//Sell Coin Loop
  return $finalBool;
}






?>
</html>
