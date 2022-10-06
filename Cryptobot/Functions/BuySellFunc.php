<html>
<?php
include_once ('/home/stevenj1979/public_html/Investment-Tracker/Cryptobot/includes/newConfig.php');
include_once ('/home/stevenj1979/SQLData.php');
include_once ('/home/stevenj1979/Encrypt.php');

$apikey=getAPIKey();
$apisecret=getAPISecret();
$logToSQLSetting = getLogToSQL();
//$GLOBALS['logToFileSetting']  = getLogToFile();
$GLOBALS['logToSQLSetting'] = getLogToSQL();
$GLOBALS['logToFileSetting'] = getLogToFile();



function runNewTrackingCoins($newTrackingCoins,$marketStats,$baseMultiplier,$ruleProfit,$coinPurchaseSettings,$clearCoinQueue,$openTransactions,$delayCoinPurchase){
  $finalBool = False;
  $trackCounter = [];
  $coinPurchaseSettingsSize = count($coinPurchaseSettings);
  $newTrackingCoinsSize = count($newTrackingCoins);
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
    $type = $newTrackingCoins[$a][39]; $lastPrice = $newTrackingCoins[$a][40]; $SBRuleID = $newTrackingCoins[$a][41]; $SBTransID = $newTrackingCoins[$a][42]; $buyCoinPrice = 0;
    $trackingID = $newTrackingCoins[$a][43]; $quickBuyCount = $newTrackingCoins[$a][44]; $minsDisabled = $newTrackingCoins[$a][45]; $overrideCoinAlloc  = $newTrackingCoins[$a][46];
    $market1HrChangePct = $marketStats[0][1]; $oneTimeBuy = $newTrackingCoins[$a][47]; $buyAmountCalculationEnabled = $newTrackingCoins[$a][48]; $allTimeHighPrice = $newTrackingCoins[$a][49];
    $transactionID = $newTrackingCoins[$a][50]; $oldBuyBackTransID = $newTrackingCoins[$a][52]; $toMerge = $newTrackingCoins[$a][53]; $baseBuyPrice = $newTrackingCoins[$a][54];
    $reduceLossCounter = $newTrackingCoins[$a][55]; $lowBuyMode = $newTrackingCoins[$a][56]; $savingOverride = $newTrackingCoins[$a][57];
    $trackCounter = initiateAry($trackCounter,$userID."-".$coinID);
    $trackCounter = initiateAry($trackCounter,$userID."-Total");
    $pctToBuy = (($allTimeHighPrice - $liveCoinPrice)/$allTimeHighPrice)*100;
    if ($baseCurrency == 'BTC'){
      $ogBTCAmount = (float)$newTrackingCoins[$a][11];
      Echo "<BR> Base Multiplier $BTCAmount | ".$baseMultiplier[0][0];
      $BTCAmount = $BTCAmount /$liveCoinPrice;
    }elseif ($baseCurrency == 'ETH'){
      $ogBTCAmount = (float)$newTrackingCoins[$a][11];
      $BTCAmount = $BTCAmount /$liveCoinPrice;
    }else{
      $ogBTCAmount = $BTCAmount;
    }
    //if ($openTransactionFlag == True){

    //  $openTransactionFlag = False;
    //}

    echo "<BR> Tracking Coin: Checking $symbol | $buyType";
    //$minusMinsToCancel = $timeToCancelBuyMins-$timeToCancelBuyMins-$timeToCancelBuyMins;
    if ($disableUntil > date("Y-m-d H:i:s", time())){ echo "<BR> EXIT: Disabled until: ".$disableUntil; continue;}
    $delayCoinPurchaseSize = count($delayCoinPurchase);
    for ($b=0; $b<$delayCoinPurchaseSize; $b++){
      $delayCoinPurchaseUserID = $delayCoinPurchase[$b][2]; $delayCoinPurchaseCoinID = $delayCoinPurchase[$b][1];
      if ($delayCoinPurchaseUserID == $userID AND $delayCoinPurchaseCoinID == $coinID){
        echo "<BR>EXIT: Delay CoinID: $coinID! "; continue;
      }
    }
    if($minsFromDate >= $timeToCancelBuyMins){
      //reOpenOneTimeBuyRule($trackingID);
      closeNewTrackingCoin($newTrackingCoinID, True,1,"Mins From Date");
      //if ($oldBuyBackTransID <> 0){
      reopenCoinSwapCancel($oldBuyBackTransID,0);
      buyBackDelay($coinID,120,$userID);
      //}
      if ($type == 'SavingsBuy'){ updateCoinSwapStatusCoinSwapID('AwaitingSavingsBuy',$transactionID);}
      newLogToSQL("TrackingCoins", "closeNewTrackingCoin($newTrackingCoinID); $pctProfit | $minsFromDate | $timeToCancelBuyMins", $userID, $GLOBALS['logToSQLSetting'],"MinsFromDateExceed","TrackingCoinID:$newTrackingCoinID"); Echo "<BR> MinsFromDate: $minsFromDate | ";
      $finalBool = True;
      //reOpenBuySellProfitRule($ruleIDBuy,$userID,$coinID);
      continue;
    }
    Echo "<BR> Tracking Buy Count 1 <BR>";

    $ruleProfitSize = count($ruleProfit);
    for ($h=0; $h<$ruleProfitSize; $h++){
        if ($limitBuyAmountEnabled == 1 and $overrideCoinAlloc == 0){
          //echo "<BR> TEST limitBuyAmountEnabled: $limitBuyAmountEnabled | ".$ruleProfit[$h][4]." | $ruleIDBuy | ".$ruleProfit[$h][1]." | $limitBuyAmount";
          if ($ruleProfit[$h][4] == $ruleIDBuy and $ruleProfit[$h][1] >= $limitBuyTransactions){echo "<BR>EXIT: Rule Amount Exceeded! "; cancelTrackingBuy($ruleIDBuy, 'Rule Amount Exceeded'); continue;}// reOpenBuySellProfitRule($ruleIDBuy,$userID,$coinID);
        }
        if ($limitBuyTransactionsEnabled == 1 and $overrideCoinAlloc == 0){
          //echo "<BR> TEST limitBuyTransactionEnabled: $limitBuyTransactionsEnabled | ".$ruleProfit[$h][4]." | $ruleIDBuy | ".$ruleProfit[$h][5]." | $limitBuyTransactions";
          if ($ruleProfit[$h][4] == $ruleIDBuy and $ruleProfit[$h][5] >= $limitBuyTransactions){echo "<BR>EXIT: Rule Transaction Count Exceeded! "; cancelTrackingBuy($ruleIDBuy,'Rule Transaction Count Exceeded');  continue;} //reOpenBuySellProfitRule($ruleIDBuy,$userID,$coinID);
        }elseif($coinModeOverridePriceEnabled == 1 and $overrideCoinAlloc == 0){
          //echo "<BR> TEST limitBuyTransactionEnabled: $limitBuyAmount | $noOfBuyModeOverrides | ".$ruleProfit[$h][5];
          if ($ruleProfit[$h][4] == $ruleIDBuy and ($limitBuyAmount + $noOfBuyModeOverrides) >=  $ruleProfit[$h][5]){echo "<BR>EXIT: Rule Transaction Count Override Exceeded! ";cancelTrackingBuy($ruleIDBuy,'Rule Transaction Count Override Exceeded');  continue;} //reOpenBuySellProfitRule($ruleIDBuy,$userID,$coinID);
        }
    }
    Echo "<BR> Tracking Buy Count 2 <BR>";
    //if ($overrideCoinAlloc == 1){ $lowBuyMode = 1;}else{$lowBuyMode=0; }
    Echo "<BR> LOWBuyMode: $lowBuyMode CoinOverrride: $overrideCoinAlloc";
    $coinAllocation = getNewCoinAllocation($baseCurrency,$userID,$lowBuyMode,$overrideCoinAlloc,$savingOverride);
    //$coinAllocation = getCoinAllocation($userID);
    Echo "<BR> Tracking CoinAllocation: ".$coinAllocation." | $BTCAmount | $ruleIDBuy | $baseCurrency";
    if ($coinAllocation <= 20 and $overrideCoinAlloc == 0){
        echo "<BR> EXIT CoinAllocation: $symbol | $baseCurrency | $type | $BTCAmount | $ogBTCAmount| $coinAllocation";
        continue;
    }
    Echo "<BR> Tracking Buy Count 3 <BR>";
    if ($coinMode > 0 and $overrideCoinAlloc == 0){
      $indexLookup = 1;
    }elseif ($coinMode == 0 AND ($type == 'SpreadBuy' OR $type == 'SpreadSell')){
      $indexLookup = 3;
    }else{
      $indexLookup = 2;
    }
  Echo "<BR> Tracking Buy Count 4 <BR>";
    $openTransactionsSize = count($openTransactions);
    for ($h=0; $h<$openTransactionsSize; $h++){
      if ($openTransactions[$h][0] == $userID){
        $oldBTCAmount = $BTCAmount;
        $liveOpenTrans = $openTransactions[$h][$indexLookup]+$openTransactions[$h][4];
        //$BTCAmount = $BTCAmount / ($liveOpenTrans-$noOfBuys);
        //LogToSQL("TrackingCoin","BTC Alloction: $oldBTCAmount | $BTCAmount | $indexLookup | $liveOpenTrans | $noOfBuys",3,1);
      }
    }
    Echo "<BR> Tracking Buy Count 5 <BR>";
    if ($minsDisabled>0){ Echo "<BR> Exit Disabled : $minsDisabled"; continue;}
    if ($trackCounter[$userID."-Total"] >= $noOfBuys and $overrideCoinAlloc == 0){ echo "<BR>EXIT: Buy Counter Met! $noOfBuys ".$trackCounter[$userID."-Total"]; reOpenTransactionfromBuyBackNew($oldBuyBackTransID); continue;}//else{ Echo "<BR> Number of Buys: $noOfBuys BuyCounter ".$trackCounter[$userID];}
    if ($trackCounter[$userID."-".$coinID] >= 1 and $overrideCoinAlloc == 0){ echo "<BR>EXIT: Buy Counter Met! $noOfBuys ".$trackCounter[$userID."-".$coinID]; reOpenTransactionfromBuyBackNew($oldBuyBackTransID); continue;}//else{ Echo "<BR> Number of Buys: $noOfBuys BuyCounter ".$trackCounter[$userID];}

    Echo "<BR> Price Check: Live:$liveCoinPrice Original: $originalPrice";
    $readyToBuy = trackingCoinReadyToBuy($liveCoinPrice,$timeToCancelBuyMins,$type,$baseBuyPrice,$newTrackingCoinID,$noOfRisesInPrice,$pctProfit,$minsFromDate,$lastPrice,$risesInPrice,$trackingID,$quickBuyCount,$market1HrChangePct,$oneTimeBuy);
    echo "<BR> Ready To Buy: $readyToBuy";
    if ($readyToBuy == 1){
      $delayCoinPurchase = getDelayCoinPurchaseTimes();
      $totalCoinPurchases = getTotalCoinPurchases();
      $totalCoinPurchasesSize = count($totalCoinPurchases);
      $coinPurchasesPerCoin = getCoinPurchasesByCoin();
      $coinPurchasesPerCoinSize = count($coinPurchasesPerCoin);
      $clearCoinQueueSize = count($clearCoinQueue);
      for ($p=0; $p<$clearCoinQueueSize; $p++){
        if ($coinID == $clearCoinQueue[$p][1] AND $userID == $clearCoinQueue[$p][0] and $overrideCoinAlloc < 1){
          echo "<BR> EXIT: CoinID and USERID in Clear Coin Queue: $coinID | $userID";
          reOpenTransactionfromBuyBackNew($oldBuyBackTransID);
          continue;
        }
      }
      for ($u=0;$u<$totalCoinPurchasesSize;$u++){
        for ($r=0;$r<$coinPurchaseSettingsSize;$r++){
            if ($userID == $totalCoinPurchases[$u][0] and $userID == $coinPurchaseSettings[$r][0] and $totalCoinPurchases[$u][1]>=$coinPurchaseSettings[$r][2] and $overrideCoinAlloc < 1){
              echo "<BR> EXIT: User over total Coin Purchases: $coinID | $userID".$totalCoinPurchases[$u][1]."|".$coinPurchaseSettings[$r][2];
              reOpenTransactionfromBuyBackNew($oldBuyBackTransID);
              continue;
            }
        }
      }
      for ($e=0;$e<$coinPurchasesPerCoinSize;$e++){
        for ($w=0;$w<$coinPurchaseSettingsSize;$w++){
            if ($userID == $coinPurchasesPerCoin[$e][0] and $userID == $coinPurchaseSettings[$w][0] and $overrideCoinAlloc < 1){
              if($coinID == $coinPurchasesPerCoin[$e][1] ){
                if($coinPurchasesPerCoin[$e][1]>=$coinPurchaseSettings[$w][1]){
                  echo "<BR> EXIT: User over Coin Purchases per Coin: $coinID | $userID".$coinPurchasesPerCoin[$e][1]."|".$coinPurchaseSettings[$w][1];
                  reOpenTransactionfromBuyBackNew($oldBuyBackTransID);
                  continue;
                }
              }
            }
        }
      }
      if ($type == 'SavingsBuy'){
        //$swapCoinID = $newTrackingCoins[$a][50];
        if (!Empty($KEK)){ $APISecret = Decrypt($KEK,$newTrackingCoins[$a][19]);}
        //$liveCoinPrice = $bitPrice;
        $rate = $liveCoinPrice;
        $quant = $ogBTCAmount/$rate;
        echo"<BR> bittrexbuy($APIKey, $APISecret, $symbol, $quant, $rate, $baseCurrency,3,FALSE);";
        $obj = bittrexbuy($APIKey, $APISecret, $symbol,$quant , $rate, $baseCurrency,3,FALSE);
        $bittrexRef = $obj["id"];
        newLogToSQL("CoinSwapBittrexID",$bittrexRef." | ".$symbol,3,1,"SaveBittrexRef","BittrexID:$bittrexRef");
        if ($bittrexRef <> ""){
          Echo "<BR> Bittrex ID: $bittrexRef";
          updateCoinSwapBittrexID($bittrexRef,$transactionID,$coinID,$liveCoinPrice,'Buy');
          //Change Status to AwaitingBuy
          updateCoinSwapStatusCoinSwapID('AwaitingSavingsPurchase',$transactionID);
          closeNewTrackingCoin($newTrackingCoinID, False,2, "Tracking Complete: Savingsbuy");
          logAction("runNewTrackingCoins; SavingsBuy : $symbol | $transactionID | $coinID | $liveCoinPrice | $newTrackingCoinID | 'AwaitingSavingsPurchase' | $quant | $rate | $baseCurrency | $type", 'BuySellFlow', 1);
          $finalBool = True;
        }
      }else{
        newLogToSQL("TrackingCoin","trackingCoinReadyToBuy($liveCoinPrice,$timeToCancelBuyMins,$type,$originalPrice,$newTrackingCoinID,$noOfRisesInPrice,$pctProfit,$minsFromDate,$lastPrice,$risesInPrice,$trackingID,$quickBuyCount,$market1HrChangePct)$coinID|$overrideCoinAlloc|".$coinAllocation[0][0]." | $type | $coinMode;",$userID,1,"TrackingSuccess","TrackingCoinID:$newTrackingCoinID");
        if (!Empty($KEK)){ $APISecret = Decrypt($KEK,$newTrackingCoins[$a][19]);}
        //if ($baseCurrency == 'BTC' OR $baseCurrency == 'ETH'){ $ogBTCAmount = (float)$ogBTCAmount;}
        if ($buyAmountCalculationEnabled == 1){
            newLogToSQL("TrackingCoinAmountCalc","$symbol | $coinID | $pctToBuy = (($allTimeHighPrice - $liveCoinPrice)/$allTimeHighPrice)*100; | ($ogBTCAmount/100)*$pctToBuy",$userID,1,"BuyCoinAmountCalculation","TrackingCoinID:$newTrackingCoinID");
            if ($type == 'BuyBack'){
              $pctToBuy = 100;
            }
            if ($overrideCoinAlloc == 1 AND $BTCAmount < 20){
              $pctToBuy = 400;
            }
            $ogBTCAmount = ($ogBTCAmount/100)*$pctToBuy;
        }
        $date = date("Y-m-d H:i:s", time());
        $checkBuy = buyCoins($APIKey, $APISecret,$symbol, $Email, $userID, $date, $baseCurrency,$SendEmail,$BuyCoin,$ogBTCAmount, $ruleIDBuy,$UserName,$coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed, $buyCoinPrice, $overrideCoinAlloc,$noOfPurchases+1);
        $delayResponse = getCoinDelayState($coinID,$userID);
        echo "<BR> delay response: $delayResponse";
        if ($checkBuy == 1){
          newLogToSQL("TrackingCoin","buyCoins($APIKey, $APISecret,$symbol, $Email, $userID, $date, $baseCurrency,$SendEmail,$BuyCoin,$ogBTCAmount, $ruleIDBuy,$UserName,$coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed, $buyCoinPrice, $noOfPurchases+1);",$userID,1,"BuyCoin","TrackingCoinID:$newTrackingCoinID");
          //logToSQL("TrackingCoin", "buyCoins($APIKey, $APISecret,$symbol, $Email, $userID, $date, $baseCurrency,$SendEmail,$BuyCoin,$ogBTCAmount, $ruleIDBuy,$UserName,$coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed, $buyCoinPrice, $noOfPurchases+1);", $userID,1);
          UpdateProfit();
          //subUSDTBalance('USDT', $BTCAmount,$liveCoinPrice, $userID);
          closeNewTrackingCoin($newTrackingCoinID, False,3, "Tracking Complete");
          addReduceLossCounterToTrans($reduceLossCounter,$coinID,$userID,'TrackingCoins');
          //if ($type == 'SavingsBuy'){
            //updateTypeToBittrex($type,$transactionID);
            //updateTypeToTrans($type,$transactionID);
          //}
          $trackCounter[$userID."-".$coinID] = $trackCounter[$userID."-".$coinID] + 1;
          $trackCounter[$userID."-Total"] = $trackCounter[$userID."-Total"] + 1;
          if ($type == 'SpreadBuy'){
            updateTransToSpread($SBRuleID,$coinID,$userID,$SBTransID);
            $finishedSBBuy = getSpreadBetCount($SBTransID);
            if ((!isset($finishedSBBuy)) OR ($finishedSBBuy == 0)){
              updateSpreadBuy($SBRuleID);
            }

          }
          clearTrackingCoinQueue($userID,$coinID);
          addCoinPurchaseDelay($coinID,$userID,1,1);
          $aryCount = count($clearCoinQueue);
          //$clearCoinQueue[$aryCount] = Array($userID,$coinID);
          if (!empty($clearCoinQueue)) {
              array_push($clearCoinQueue,$userID,$coinID);
          }else{
            $clearCoinQueue = Array($userID,$coinID);
          }

          updateCoinAllocationOverride($coinID,$userID,$overrideCoinAlloc,$toMerge);
          //continue;
          newLogToSQL("CheckBuyBackType","bittrexActionBuyBack($coinID,$oldBuyBackTransID); | $type",$userID,1,"BuyCoin","TrackingCoinID:$newTrackingCoinID");
          if ($type == 'BuyBack'){
            bittrexActionBuyBack($coinID,$oldBuyBackTransID);
          }
          if ($type == 'buyToreduceLoss'){
            bittrexActionReduceLoss($coinID,$trackingID);
          }
          if ($type == 'Buy' and $transactionID <> 0) { bittrexActionBuyBack($coinID,$transactionID,0);}
          logAction("runNewTrackingCoins; buyCoins : $symbol | $coinID | $coinID | $baseCurrency | $ogBTCAmount | $timeToCancelBuyMins | $buyCoinPrice | $overrideCoinAlloc | $SBRuleID", 'BuySellFlow', 1);
          buyBackDelay($coinID,0,$userID);
          return True;
        }elseif ($checkBuy == 2){
          //2 = INSUFFICIENT BAL
          closeNewTrackingCoin($newTrackingCoinID, False,4,"CheckBuy = 2");
          removeTransactionDelay($coinID, $userID);
          newLogToSQL("TrackingCoins","$oldBuyBackTransID",3,1,"ReOpen BuyBack","TrackingCoinID:$newTrackingCoinID");
          reOpenTransactionfromBuyBackNew($oldBuyBackTransID);
          //reOpenBuySellProfitRule($ruleIDBuy,$userID,$coinID);
        }
      }
    }elseif ($readyToBuy == 2){ $finalBool = True;}
  }
  return $finalBool;
}


?>
</html>
