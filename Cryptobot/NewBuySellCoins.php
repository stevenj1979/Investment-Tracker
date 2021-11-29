<html>
<?php
ini_set('max_execution_time',1200);
require('includes/newConfig.php');

include_once ('/home/stevenj1979/SQLData.php');
include_once ('/home/stevenj1979/Encrypt.php');
$apikey=getAPIKey();
$apisecret=getAPISecret();
$logToSQLSetting = getLogToSQL();
//$GLOBALS['logToFileSetting']  = getLogToFile();
$GLOBALS['logToSQLSetting'] = getLogToSQL();
$GLOBALS['logToFileSetting'] = getLogToFile();

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

function action_Alert($minutes,$email,$symbol,$price,$action,$userName,$category,$reocurring,$id,$userID, $logToFileSetting , $logToSQLSetting, $livePrice){
  if ($minutes > 30){
    sendAlertEmailLocal($email, $symbol, $price, $action, $userName, $livePrice, $category);
    logAction("Alert: $symbol $price $action $userName $category", 'BuySellAlert', $logToFileSetting );
    logToSQL("Alerts", "Coin: $symbol $action $category $price", $userID, $logToSQLSetting);
  }
  //Close Alert
  if ($reocurring == 0){closeCoinAlerts($id,'CoinAlerts');}else{updateAlertTime($id,'CoinAlerts');}
}

function action_Market_Alert($minutes,$email,$price,$action,$userName,$category,$reocurring,$id,$userID, $logToFileSetting , $logToSQLSetting, $livePrice){
  if ($minutes > 30){
    sendAlertEmailLocal($email, 'MarketAlerts', $price, $action, $userName, $livePrice, $category);
    logAction("Alert: $symbol $price $action $userName $category", 'BuySellAlert', $logToFileSetting );
    logToSQL("Alerts", "Coin: $symbol $action $category $price", $userID, $logToSQLSetting);
  }
  //Close Alert
  if ($reocurring == 0){closeCoinAlerts($id,'MarketAlerts');}else{updateAlertTime($id,'MarketAlerts');}
}

function action_SpreadBet_Alert($minutes,$email,$price,$action,$userName,$category,$reocurring,$id,$userID, $logToFileSetting, $logToSQLSetting, $livePrice){
  if ($minutes > 30){
    sendAlertEmailLocal($email, 'SpreadBetAlerts', $price, $action, $userName, $livePrice, $category);
    logAction("Alert: $symbol $price $action $userName $category", 'BuySellAlert', $logToFileSetting);
    logToSQL("Alerts", "Coin: $symbol $action $category $price", $userID, $logToSQLSetting);
  }
  //Close Alert
  if ($reocurring == 0){closeCoinAlerts($id,'SpreadBetAlerts');}else{updateAlertTime($id,'SpreadBetAlerts');}
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

function runReBuySavings($coinSwaps){
  $finalBool = False;
  $coinSwapsSize = count($coinSwaps);
  $apiVersion = 3; $ruleID = 111111;
  for ($y=0; $y<$coinSwapsSize; $y++){
    $status = $coinSwaps[$y][1];
    if (!isset($status)) { continue; }
    if ($status == 'AwaitingSavingsBuy'){
      $apikey = $coinSwaps[$y][8];$apisecret = $coinSwaps[$y][9];$KEK = $coinSwaps[$y][10];$ogCoinID = $coinSwaps[$y][12];$ogSymbol = $coinSwaps[$y][13];
       $baseCurrency = $coinSwaps[$y][5]; $totalAmount = $coinSwaps[$y][6]; $transID = $coinSwaps[$y][0];
      $finalPrice = $coinSwaps[$y][15]; $userID = $coinSwaps[$y][17]; $coinSwapID = $coinSwaps[$y][18]; $hr1PctChange = $coinSwaps[$y][20];
      $tempPrice = getCoinPrice($ogCoinID);
      $bitPrice = $tempPrice[0][0];
      //$bitPrice = number_format($coinSwaps[$y][16],8);
      //$orderSale = isSaleComplete($coinSwaps,$y);
      $sellPct = $coinSwaps[$y][19];
      $tolerance = 5;
      $sellPricePct = (($finalPrice/100)*$sellPct);
      $sellPriceTolerance = (($finalPrice/100)*$tolerance);
      $lowPrice = $finalPrice-$sellPricePct+$sellPriceTolerance;
      echo "<BR> TEST Buy: $status | $ogCoinID | $ogSymbol | LowPrice:$lowPrice | BitPrice:$bitPrice";
      if (($bitPrice <= $lowPrice) OR ($hr1PctChange <= -8)){
        $liveCoinPrice = $bitPrice;
        $rate = $liveCoinPrice;
        $quant = $totalAmount;
        newLogToSQL("ReBuySavings","addTrackingCoin($ogCoinID, $liveCoinPrice, $userID, $baseCurrency, 1, 1, $quant, 999996, 0, 0, 1, 240, 77777,1,1,10,'SavingsBuy',$liveCoinPrice,0,0,1);",3,1,"AwaitingSavingsBuy","TransID:$transID");
        addTrackingCoin($ogCoinID, $liveCoinPrice, $userID, $baseCurrency, 1, 1, $quant, 999996, 0, 0, 1, 240, 77777,0,1,10,'SavingsBuy',$liveCoinPrice,0,0,1,'reBuySavings',$transID);
        updateCoinSwapStatus('AwaitingSavingsPurchaseTracking',$transID);
        addCoinSwapIDtoTracking($coinSwapID,$transID);
        logAction("runReBuySavings; addTrackingCoin : $ogSymbol | $baseCurrency | $ogCoinID | $quant | $userID | $bitPrice | $lowPrice | $transID", 'BuySellFlow', 1);
        $finalBool = True;
      }
    }
  }
  return $finalBool;
}

function runSellSavings($spreadBuyBack){
  $finalBool = False;
  $versionNum = 3; $useAwards = False;
  $profitTarget = 40.0;
  $spreadBuyBackSize = COUNT($spreadBuyBack);
  for ($u=0; $u<$spreadBuyBackSize; $u++){
    $purchasePrice = $spreadBuyBack[$u][4];
    $amount = $spreadBuyBack[$u][5];$coinID = $spreadBuyBack[$u][2];$userID = $spreadBuyBack[$u][3];
    $tempPrice = getCoinPrice($coinID);
    $LiveCoinPrice = $tempPrice[0][0];$symbol = $spreadBuyBack[$u][11];$transactionID = $spreadBuyBack[$u][0];$fallsInPrice = $spreadBuyBack[$u][56];
    $profitSellTarget = $spreadBuyBack[$u][58];$autoBuyBackSell = $spreadBuyBack[$u][59];$bounceTopPrice = $spreadBuyBack[$u][60];$bounceLowPrice = $spreadBuyBack[$u][61];
    $bounceDifference = $spreadBuyBack[$u][62];$noOfBounceSells = $spreadBuyBack[$u][64];$baseCurrency = $spreadBuyBack[$u][36];
    $minsToDelay = $spreadBuyBack[$u][63]; $BTCPrice = $spreadBuyBack[$u][65]; $ETHPrice = $spreadBuyBack[$u][66]; $hr1PctChange = $spreadBuyBack[$u][29];
    //echo "<BR> LiveCoinPrice:$LiveCoinPrice | Amount:$amount";
    $sellPrice = ($LiveCoinPrice * $amount);
    //echo "<BR> PurchasePrice:$purchasePrice | Amount:$amount";
    $buyPrice = ($purchasePrice * $amount);
    //echo "<BR> SellPrice:$sellPrice | BuyPrice:$buyPrice";
    $profit = ($sellPrice-$buyPrice);
    //echo "<BR> Profit:$profit | BuyPrice:$buyPrice";
    $profitPCT = ($profit/$buyPrice)*100;
    if (!isset($profitPCT)){ continue; }
    if ($minsToDelay < 0) { continue; }
    if ($baseCurrency == 'USDT'){ $baseMin = 20;}elseif ($baseCurrency == 'BTC'){ $baseMin = 0.00048;}elseif ($baseCurrency == 'ETH'){ $baseMin = 0.0081;}
    if ($profitPCT > 30 OR $profitPCT < -20 OR $hr1PctChange > 13){
      echo "<br> runSellSavings:  $coinID | $baseCurrency | PP:$buyPrice | LP:$sellPrice | Prft:$profit | pct:$profitPCT | mins:$minsToDelay | bounceSell: $noOfBounceSells | bounceDiff: $bounceDifference | 1HrPct: $hr1PctChange";
    }
    $profitFlag = False;$hr1Flag = False; $buyMoreFlag = False;
    if ($profitPCT >= $profitTarget){ $profitFlag = True;}
    if ($hr1PctChange >= 7 and $minsToDelay > 0){ $hr1Flag = True; }
    if (($profitPCT <= -50 and $minsToDelay > 0 and $noOfBounceSells <= 1)){ $buyMoreFlag = True; }
    if (($profitFlag == True) OR ($hr1Flag == True)){
      newLogToSQL("runSellSavings_v1","$symbol | $baseCurrency | $sellPrice | $baseMin | $profitPCT | $profitTarget | $hr1PctChange",3,1,"Profit","TransID:$transactionID");
      newTrackingSellCoins($LiveCoinPrice,$userID, $transactionID,1, 1,0,0,10,'SavingsSell','RunSellSavings_1');
      setTransactionStatus($transactionID,"SavingsSell");
      logAction("runSellSavings; newTrackingSellCoins_v1 : $symbol | $baseCurrency | $sellPrice | $baseMin | $profitPCT | $profitTarget | $transactionID | $hr1PctChange", 'BuySellFlow', 1);
      updateCoinSwapTransactionStatus('SavingsSell',$transactionID);
      $finalBool = True;
    //}elseif ($profitPCT >= $profitTarget){
    //  Echo "<BR> CoinID: $CoinID | Sym: $symbol | SellPrice: $sellPrice | Min: $baseMin";
  }elseif ($buyMoreFlag == True){
      echo "<BR> runSellSavings $profitPCT | $minsToDelay";
      newLogToSQL("runSellSavings_v3","$symbol | $baseCurrency | $sellPrice | $baseMin | $profitPCT | $profitTarget | $noOfBounceSells",3,1,"Profit","TransID:$transactionID");
      addTrackingCoin($coinID, $LiveCoinPrice, $userID, $baseCurrency, 1, 1, 150, 96, 0, 0, 1, 720, 219,0,0,15,'Buy',$LiveCoinPrice,0,0,1,'runSellSavings');
      delaySavingBuy($transactionID,7200);
      //setTransactionStatus($transactionID,"SavingsSell");
      //updateCoinSwapTransactionStatus('SavingsSell',$transactionID);
      logAction("runSellSavings; newTrackingSellCoins_v3 : $symbol | $baseCurrency | $sellPrice | $baseMin | $profitPCT | $profitTarget | $transactionID | $minsToDelay | $noOfBounceSells", 'BuySellFlow', 1);
      $finalBool = True;
    }
  }
  return $finalBool;
}

function runPriceDipRule($priceDipRules){
  $priceDipRulesSize = count($priceDipRules);

  for ($a=0; $a<$priceDipRulesSize;$a++){
    $buyRuleID = $priceDipRules[$a][0]; $enableRuleActivationAfterDip = $priceDipRules[$a][1]; $hr24PriceDipPct = $priceDipRules[$a][2];
    $hr24ChangePctChange = $priceDipRules[$a][3]; $d7ChangePctChange = $priceDipRules[$a][4]; $d7PriceDipPct = $priceDipRules[$a][5];
    echo "<BR> $hr24ChangePctChange | $hr24PriceDipPct | $d7ChangePctChange | $d7PriceDipPct";
    if(isset($hr24ChangePctChange) && $hr24ChangePctChange <= $hr24PriceDipPct && $hr24ChangePctChange > -999){
      if(isset($d7ChangePctChange) && $d7ChangePctChange <= $d7PriceDipPct && $d7ChangePctChange > -999){
        echo "<BR> enableBuyRule($buyRuleID); $hr24ChangePctChange | $hr24PriceDipPct | $d7ChangePctChange | $d7PriceDipPct";
        enableBuyRule($buyRuleID, 1);
        LogToSQL("PriceDipRuleEnable","enableBuyRule($buyRuleID); $hr24ChangePctChange | $hr24PriceDipPct | $d7ChangePctChange | $d7PriceDipPct",3,$GLOBALS['logToSQLSetting']);
      }
    }
    if (isset($hr24ChangePctChange) && $hr24ChangePctChange >= 2 and isset($d7ChangePctChange) and $d7ChangePctChange >= 2){
      enableBuyRule($buyRuleID, 0);
    }
  }
}

function runBuyBack($buyBackCoins){
  $finalBool = False;
  $buyBackCoinsSize = count($buyBackCoins);
  for ($t=0; $t<$buyBackCoinsSize;$t++){
    $bBID = $buyBackCoins[$t][0];    $userID = $buyBackCoins[$t][12];    $TransactionID = $buyBackCoins[$t][1];    $coinID = $buyBackCoins[$t][7];    $spreadBetTransactionID = $buyBackCoins[$t][5];
    $spreadBetRuleID = $buyBackCoins[$t][6];
    $quantity = $buyBackCoins[$t][2];$sellPrice = $buyBackCoins[$t][3];$status = $buyBackCoins[$t][4];
    $sellPriceBA = $buyBackCoins[$t][8];$priceDifferece = $buyBackCoins[$t][10];//$priceDifferecePct = $buyBackCoins[$t][11];
    $email = $buyBackCoins[$t][13];$userName = $buyBackCoins[$t][14];$apiKey = $buyBackCoins[$t][15];$apiSecret = $buyBackCoins[$t][16];$KEK = $buyBackCoins[$t][17];
    $originalSaleProfit = $buyBackCoins[$t][18];
    $originalSaleProfitPct = $buyBackCoins[$t][19]; $profitMultiply = $buyBackCoins[$t][20]; $buyBackPct = $buyBackCoins[$t][22]; $noOfRaisesInPrice = $buyBackCoins[$t][21];
    $minsToCancel = $buyBackCoins[$t][23]; $bullBearStatus = $buyBackCoins[$t][24];$type = $buyBackCoins[$t][25]; $overrideCoinAlloc = $buyBackCoins[$t][26];
    $allBuyBackAsOverride = $buyBackCoins[$t][27]; $BTCPrice = $buyBackCoins[$t][28];$ETHPrice = $buyBackCoins[$t][29];$liveCoinPrice = $buyBackCoins[$t][30];
    $delayMins = $buyBackCoins[$t][31];
    //$tempPrice = getCoinPrice($CoinID);
    //$liveCoinPrice = $buyBackCoins[$t][9];
    $priceDifferecePct = $buyBackCoins[$t][11];//$lowMarketModeEnabled = $buyBackCoins[$t][39];$pctOnLow = $buyBackCoins[$t][34];
    //if ($lowMarketModeEnabled > 0){ $lowMarketMultiplier = 100;}else{$lowMarketMultiplier = $pctOnLow;}
    //$BTCAvailable = (($buyBackCoins[$t][31]/100)*$lowMarketMultiplier) - $buyBackCoins[$t][35];
    //$ETHAvailable = (($buyBackCoins[$t][32]/100)*$lowMarketMultiplier) - $buyBackCoins[$t][36];
    //$USDTAvailable = (($buyBackCoins[$t][33]/100)*$lowMarketMultiplier) - $buyBackCoins[$t][37];
    // $lowMarketModeDate = $buyBackCoins[$t][38];
    //$priceDifferecePct = (($liveCoinPrice-$sellPriceBA)/$sellPriceBA)*100;

    ECHO "<BR> Check Price: $priceDifferecePct | $buyBackPct";
    if (($priceDifferecePct <=  $buyBackPct) OR ($bullBearStatus == 'BULL')){
      if($delayMins > 0){ continue; }
      Echo "<BR> $priceDifferecePct <=  ($buyBackPct+$profitMultiply)";
      LogToSQL("BuyBack","PriceDiffPct: $priceDifferecePct | BuyBackPct: $buyBackPct Bull/Bear: $bullBearStatus | SellPrice: $sellPriceBA | LivePrice: $liveCoinPrice",3,1);
      //BuyBack
      $marketStats = getMarketstats();
      $reOpenData = reOpenTransactionfromBuyBack($bBID);
      $tmpCoinID = $reOpenData[0][0];$tmpLiveCoinPrice = $reOpenData[0][1];$tmpUserID = $reOpenData[0][2];$tmpBaseCur = $reOpenData[0][3];
      $tmpSendEmail = $reOpenData[0][4];$tmpBuyCoin = $reOpenData[0][5];$tmpSalePrice = $reOpenData[0][6];$tmpBuyRule = $reOpenData[0][7];
      $tmpOffset = $reOpenData[0][8];$tmpOffsetEnabled = $reOpenData[0][9];$tmpBuyType = $reOpenData[0][10];$d11 = $reOpenData[0][11];$tmpFixSellRule = $reOpenData[0][12];$tmpToMerge = $reOpenData[0][13];
      $tmpNoOfPurchases = $reOpenData[0][14];$d15 = $reOpenData[0][15];$tmpType = $reOpenData[0][16];$tmpOriginalPrice = $reOpenData[0][17];
      $tmpSBTransID = $reOpenData[0][18];$tmpSBRuleID = $reOpenData[0][19]; $tmpSymbol = $reOpenData[0][20];
      if ($bullBearStatus == 'BULL'){
        $tmpOriginalPriceWithBuffer = $tmpLiveCoinPrice-(($tmpLiveCoinPrice/100)*1.0);
      }else{
        $tmpOriginalPriceWithBuffer = $tmpOriginalPrice-(($tmpOriginalPrice/100)*1.0);
      }
      $buyBackKittyAry = getBuyBackKittyAmount($tmpUserID);
      $usdt_BB_Amount = $buyBackKittyAry[0][0];
      $BTC_BB_Amount = $buyBackKittyAry[0][1];
      $eth_BB_Amount =$buyBackKittyAry[0][2];
      $portion = $buyBackKittyAry[0][3];
      $portionBTC = $buyBackKittyAry[0][4];
      $portionETH = $buyBackKittyAry[0][5];
      if ($tmpBaseCur == 'USDT'){ if ($usdt_BB_Amount > 0 && $portion > 0) {$bbKittyAmount = $usdt_BB_Amount/$portion;}else {$bbKittyAmount = 0;}}
      elseif ($tmpBaseCur == 'BTC'){ if ($BTC_BB_Amount > 0 && $portionBTC > 0) {$bbKittyAmount = $BTC_BB_Amount/$portionBTC;}else {$bbKittyAmount = 0;}}
      elseif ($tmpBaseCur == 'ETH'){ if ($eth_BB_Amount > 0 && $portionETH > 0) {$bbKittyAmount = $eth_BB_Amount/$portionETH;}else {$bbKittyAmount = 0;}}

      if($allBuyBackAsOverride == 1){
        $overrideCoinAlloc = 1;
      }

      if ($tmpBaseCur == 'USDT'){
        $tempConvAmt = 1;
        //$totalAvailable = $USDTAvailable*$tempConvAmt;
      }elseif ($tmpBaseCur == 'BTC'){
        $tempConvAmt = $BTCPrice;
        //$totalAvailable = $BTCAvailable*$tempConvAmt;
      }elseif ($tmpBaseCur == 'ETH'){
        $tempConvAmt = $ETHPrice;
        //$totalAvailable = $ETHAvailable*$tempConvAmt;
      }
      if ($allBuyBackAsOverride == 1){ $lowBuyMode = TRUE;}else{$lowBuyMode=FALSE; }
      $coinAllocation = getNewCoinAllocation($tmpBaseCur,$tmpUserID,$lowBuyMode);
      if ($coinAllocation <= 20 && $allBuyBackAsOverride == 0){
          echo "<BR> EXIT CoinAllocation: $tmpBaseCur | $type | $BTCAmount | $ogBTCAmount| $coinAllocation";
          continue;
      }
      //$buyBackPurchasePrice = ($tmpLiveCoinPrice*$quantity*$tempConvAmt)+$bbKittyAmount;
      $buyBackPurchasePrice = (($sellPriceBA + (($sellPriceBA/100)*$priceDifferecePct))*$quantity*$tempConvAmt)+$bbKittyAmount;
      LogToSQL("BuyBackTEST","$tmpLiveCoinPrice*$quantity*$tempConvAmt)+$bbKittyAmount | $buyBackPurchasePrice",3,1);
      updateBuyBackKittyAmount($tmpBaseCur,$bbKittyAmount,$tmpUserID);
      if($tmpSalePrice <= 0 ){ continue;}
      //if ($buyBackPurchasePrice < 20 or $totalAvailable < 20 ){ return False;}
      addTrackingCoin($tmpCoinID, $tmpLiveCoinPrice, $tmpUserID, $tmpBaseCur, $tmpSendEmail, $tmpBuyCoin, $buyBackPurchasePrice, $tmpBuyRule, $tmpOffset, $tmpOffsetEnabled, $tmpBuyType, 240, $tmpFixSellRule,$tmpToMerge,$tmpNoOfPurchases,$noOfRaisesInPrice,$tmpType,$tmpOriginalPriceWithBuffer,$tmpSBTransID,$tmpSBRuleID,$overrideCoinAlloc,'RunBuyBack');
      echo "<BR>addTrackingCoin($tmpCoinID, $tmpLiveCoinPrice, $tmpUserID, $tmpBaseCur, $tmpSendEmail, $tmpBuyCoin, $buyBackPurchasePrice, $tmpBuyRule, $tmpOffset, $tmpOffsetEnabled, $tmpBuyType, 240, $tmpFixSellRule,$tmpToMerge,$tmpNoOfPurchases,$noOfRaisesInPrice,$tmpType,$tmpOriginalPriceWithBuffer,$tmpSBTransID,$tmpSBRuleID);";
      LogToSQL("BuyBack","addTrackingCoin($tmpCoinID, $tmpLiveCoinPrice, $tmpUserID, $tmpBaseCur, $tmpSendEmail, $tmpBuyCoin, $buyBackPurchasePrice, $tmpBuyRule, $tmpOffset, $tmpOffsetEnabled, $tmpBuyType, 240, $tmpFixSellRule,$tmpToMerge,$tmpNoOfPurchases,$noOfRaisesInPrice,$tmpType,$tmpOriginalPriceWithBuffer,$tmpSBTransID,$tmpSBRuleID);",3,1);
      LogToSQL("BuyBackKitty","Adding $bbKittyAmount to $bBID | TotalBTC: $BTC_BB_Amount| Total USDT: $usdt_BB_Amount| TotalETH: $eth_BB_Amount | BTC_P: $portionBTC| USDT_P: $portion| ETH_P: $portionETH",3,$GLOBALS['logToSQLSetting']);
      //CloseBuyBack
      closeBuyBack($bBID);
      buyBackDelay($tmpCoinID,4320,$tmpUserID);
      addOldBuyBackTransID($bBID,$tmpCoinID);
      logAction("runBuyBack; addTrackingCoin : $tmpSymbol | $tmpCoinID | $tmpBaseCur | $tmpLiveCoinPrice | $tmpUserID | $buyBackPurchasePrice | $noOfRaisesInPrice | $tmpType | $tmpOriginalPriceWithBuffer | $overrideCoinAlloc | $bBID | $bbKittyAmount | $TransactionID", 'BuySellFlow', 1);
      return True;
    }
  }
  return $finalBool;
}

function runSpreadBetSellAndBuyback($spreadBuyBack){
  $finalBool = False;
  $spreadBuyBackSize = COUNT($spreadBuyBack);
  for ($u=0; $u<$spreadBuyBackSize; $u++){
    $purchasePrice = $spreadBuyBack[$u][4];$amount = $spreadBuyBack[$u][5];$CoinID = $spreadBuyBack[$u][2];
    $userID = $spreadBuyBack[$u][3];$symbol = $spreadBuyBack[$u][11];$transactionID = $spreadBuyBack[$u][0];
    //$fallsInPrice = $spreadBuyBack[$u][56];
    $fallsInPrice = 11;
    $profitSellTarget = $spreadBuyBack[$u][58];$autoBuyBackSell = $spreadBuyBack[$u][59];$bounceTopPrice = $spreadBuyBack[$u][60];
    $bounceLowPrice = $spreadBuyBack[$u][61];$bounceDifference = $spreadBuyBack[$u][62];$delayCoinSwap = $spreadBuyBack[$u][63];
    $noOfBounceSells = $spreadBuyBack[$u][64];$baseCurrency = $spreadBuyBack[$u][36];
    $tempPrice = getCoinPrice($CoinID);
    $LiveCoinPrice = $tempPrice[0][0];
    $profit = ($LiveCoinPrice * $amount)-($purchasePrice * $amount);
    $profitPCT = ($profit/($purchasePrice * $amount))*100;
    $runAutoBuyBack = False;
    if ($autoBuyBackSell <> -999999.99999000 AND $profitPCT <= $autoBuyBackSell){ $runAutoBuyBack = True; }
    echo "<BR>CoinID: $CoinID | Bounce: $bounceDifference | LiveCoinPrice: $LiveCoinPrice |BounceTopPrice: $bounceTopPrice | DelayCoinSwap: $delayCoinSwap | ProfitPct: $profitPCT | ProfitSellTarget: $profitSellTarget";
    if ( $runAutoBuyBack == True OR ($profitPCT >= $profitSellTarget) OR (($profitPCT < -30) AND ($bounceDifference >= 2.0) AND ($LiveCoinPrice >= $bounceTopPrice) AND ($delayCoinSwap <= 0))){
      if (!isset($autoBuyBackSell)){ continue; }elseif (!is_numeric($autoBuyBackSell)){ continue; }
      if (!isset($profitPCT)){ continue; }elseif (!is_numeric($profitPCT)){ continue; }
      newLogToSQL("NewBuySellCoins","$symbol | $CoinID | ProfitPct: $profitPCT | AutoBuyBackSell: $autoBuyBackSell | ProfitSellTarget: $profitSellTarget",3,1,"SellSpreadBetandBuyBack","TransID:$transactionID");
      //$tempAry = $spreadBuyBack[$u];
      //sellSpreadBetCoins($tempAry);
      $finalProfitPct = $profitPCT;
      if (($profitPCT < -20) AND ($bounceDifference >= 2.5) and ($LiveCoinPrice == $bounceTopPrice)){
          $finalProfitPct = $bounceDifference;
          LogToSQL("SellSpreadBet and BuyBack","Bounce ProfitPct: $finalProfitPct | AutoBuyBackSell: $autoBuyBackSell | ProfitSellTarget: $profitSellTarget",3,1);
      }

      if ($profitPCT > 0){
          $totalMins = 10080;
          $totalRisesBuy = $fallsInPrice;
          $totalRisesSell = $fallsInPrice;
      }else{
          $totalMins = 20160;
          $totalRisesBuy = 15;
          $totalRisesSell = 1;
      }
      LogToSQL("SellSpreadBet and BuyBack","newTrackingSellCoins($LiveCoinPrice, $userID,$transactionID,1,1,0,0.0,11);",3,1);
      newTrackingSellCoins($bounceTopPrice, $userID,$transactionID,1,1,0,0.0,11,'Sell','runSpreadBetSellAndBuyback');
      setTransactionPending($transactionID);
      WriteBuyBack($transactionID,$finalProfitPct,11, $totalMins);
      LogToSQL("SellSpreadBet and BuyBack","WriteBuyBack($transactionID,$finalProfitPct,$totalRisesBuy, $totalMins);",3,1);
      logAction("runSpreadBetSellAndBuyback; newTrackingSellCoins_v1 : $symbol | $CoinID | $baseCurrency | $userID | $bounceDifference | $LiveCoinPrice | $amount | $bounceTopPrice | $delayCoinSwap | 11 | $profitPCT | $transactionID", 'BuySellFlow', 1);
      $finalBool = True;
    }else if(($profitPCT < -20) AND ($noOfBounceSells == 0) AND ($LiveCoinPrice >= $bounceTopPrice) AND ($delayCoinSwap <= 0)){
        $versionNum = 3; $useAwards = False;
        //Swap Coin
          //Choose new Coin
          newLogToSQL("SellSpreadBet and BuyBack", "Profit below -20: $CoinID | $profitPCT | $noOfBounceSells | $LiveCoinPrice | $bounceTopPrice", $userID, 1,"Sell Coin","TransactionID:$transactionID");
          $newCoinSwap = getNewSwapCoin($baseCurrency);
          if (count($newCoinSwap)>0){
            //Change Transaction Status to CoinSwap
            updateCoinSwapTransactionStatus('CoinSwap',$transactionID);
            //Sell COIN
            $rate = $newCoinSwap[0][4];
            $quant = $amount;
            $apiConfig = getAPIConfig($userID);
            $apikey = $apiConfig[0][0];$apisecret = $apiConfig[0][1]; $kek = $apiConfig[0][2];
            if (!Empty($kek)){ $apisecret = Decrypt($kek,$apiConfig[0][1]);}
            newLogToSQL("SellSpreadBet and BuyBack", "bittrexsell($apikey, $apisecret, $symbol, $amount, $LiveCoinPrice, $baseCurrency, $versionNum, $useAwards);", $userID, 1,"Sell Coin","TransactionID:$transactionID");
            $obj = bittrexsell($apikey, $apisecret, $symbol, $amount, $LiveCoinPrice, $baseCurrency, $versionNum, $useAwards);
            //Add to Swap Coin Table
            $bittrexRef = $obj["id"];
            if ($bittrexRef <> ""){
              newLogToSQL("SellSpreadBet and BuyBack", "Sell Live Coin: $CoinID | $bittrexRef", $userID, 1,"Sell Coin","TransactionID:$transactionID");
              updateCoinSwapTable($transactionID,'AwaitingSale',$bittrexRef,$newCoinSwap[0][0],$newCoinSwap[0][2],$baseCurrency,$LiveCoinPrice * $amount,$purchasePrice * $amount,'Sell');
              logAction("runSpreadBetSellAndBuyback; bittrexsell : $symbol | $CoinID | $baseCurrency | $userID | $bounceDifference | $LiveCoinPrice | $amount | $bounceTopPrice | $delayCoinSwap | $totalRisesSell | $profitPCT | $transactionID", 'BuySellFlow', 1);
              $finalBool = True;
            }
          }

    }
  }
  return $finalBool;
}

function runSellSpreadBet($sellSpread){
  $finalBool = False;
  $sellSpreadSize = count($sellSpread);
  for ($w=0; $w<$sellSpreadSize; $w++){
    $CoinPriceTot = $sellSpread[$w][3]; $TotAmount = $sellSpread[$w][4]; $LiveCoinPriceTot = $sellSpread[$w][15];
    $ID = $sellSpread[$w][0]; $APIKey = $sellSpread[$w][50]; $APISecret = $sellSpread[$w][51]; $KEK = $sellSpread[$w][52];
    $Email = $sellSpread[$w][53]; $userID = $sellSpread[$w][2]; $UserName = $sellSpread[$w][54]; $captureTrend = $sellSpread[$w][57];
    $purchasePrice = $sellSpread[$w][59];$currentPrice = $sellSpread[$w][60];
    $spreadBetPctProfitSell = $sellSpread[$w][55]; $spreadBetRuleID = $sellSpread[$w][56]; $orderDate = $sellSpread[$w][6];
    //$profitPct = ($profit/$purchasePrice)*100;
    $hr1Pct = $sellSpread[$w][25];  $hr24Pct = $sellSpread[$w][28]; $d7Pct = $sellSpread[$w][31];
    $baseCurrency_new = $sellSpread[$w][32];
    $fallsInPrice = $sellSpread[$w][61];

    $purchasePrice = $sellSpread[$w][59];// + $sellSpread[$w][63];
    //$livePrice = $tempProfit[0][1] + $tempProfit[0][2];
    //$livePrice = $sellSpread[$w][60];
    //$tempPrice = getSBCoinPrice($spreadBetRuleID);
    //$hr1Pct = $tempPrice[0][1]; $hr24Pct = $tempPrice[0][2]; $d7Pct = $tempPrice[0][3];
    //$LiveCoinPriceTot = $tempPrice[0][0];
    $LiveCoinPriceTot = $sellSpread[$w][15];
    //Echo "<BR> TEST!: $hr1Pct | $hr24Pct | $d7Pct | $CoinID";
    //$livePrice = ($LiveCoinPriceTot * $TotAmount);
    $livePrice = $sellSpread[$w][64];
    //$soldPrice = $sellSpread[$w][66] + $sellSpread[$w][67];
    //$profit = ($livePrice-$purchasePrice);//+$soldPrice;
    $profit = $sellSpread[$w][58];
    //$profitPct = (($profit-$purchasePrice)/$purchasePrice)*100;
    $profitPct = $sellSpread[$w][65];
    echo "<BR> PROFIT: $profit / $purchasePrice * 100 = $profitPct";
    if (!Empty($KEK)){$APISecret = decrypt($KEK,$sellSpread[$w][51]);}
    //coinPriceHistorySpreadBet($ID,$LiveCoinPriceTot,$baseCurrency_new,date("Y-m-d H:i:s", time()),$hr1Pct,$hr24Pct,$d7Pct);
    echo "<BR> Checking $ID | $profitPct | $spreadBetPctProfitSell | TotPP: $CoinPriceTot | TotAm: $TotAmount | TotLive: $LiveCoinPriceTot | TotProfit: $profit";
    updateSpreadProfit($spreadBetRuleID,$profitPct);
    if ($captureTrend == 0 and $profitPct >= 0.5){
      //updateBuyTrend(0, 0, 'SpreadBet', $spreadBetRuleID);
    }
    if (($profitPct >= $spreadBetPctProfitSell) AND ($profitPct > -999) and ($profitPct < 999) and (isset($profitPct))){
      //get coin data
      echo "<BR> getSpreadCoinSellData($ID);";
      $spreadSellCoins = getSpreadCoinSellData($ID);
      sellSpreadBetCoins($spreadSellCoins);
      //Close all buyback for this SpreadBetTransID
      //CloseAllBuyBack($ID);
      deleteSpreadBetTotalProfit($ID);
      deleteSpreadBetTrackingCoins($ID);
      writeProfitToWebTable($ID,$purchasePrice,$livePrice,0);
      $finalBool = True;
    }
  }
  return $finalBool;
}

function runSpreadBet($spread,$SpreadBetUserSettings){
  $finalBool = False;
  $spreadSize = count($spread);
  //if ($spreadSize == 0){LogToSQL("SpreadBetBuy","ERROR : Empty record set for getSpreadBetData",3,1);}
  //$noOfBuys = 2;
  $SpreadBetUserSettingsSize = count($SpreadBetUserSettings);

  for ($y=0; $y<$spreadSize; $y++){
    $ID = $spread[$y][0]; $Hr1ChangePctChange = $spread[$y][4]; $Hr24ChangePctChange = $spread[$y][7];$d7ChangePctChange = $spread[$y][10];
    $APIKey = $spread[$y][24]; $APISecret = $spread[$y][25]; $KEK = $spread[$y][26]; $UserID = $spread[$y][27];$UserName = $spread[$y][29];
    $spreadBetTransID = $spread[$y][30]; $Email =  $spread[$y][28]; $pctofSixMonthHigh = $spread[$y][34]; $pctofAllTimeHigh = $spread[$y][35];
    $baseCurrency = $spread[$y][14];
    $disableUntil  = $spread[$y][36];
    $Hr1BuyPrice = $spread[$y][31];$Hr24BuyPrice = $spread[$y][32];$D7BuyPrice = $spread[$y][33]; $userID = $spread[$y][37];
    $inverseAvgHighPct = 100-(($pctofSixMonthHigh + $pctofAllTimeHigh)/2);
    $risesInPrice = $spread[$y][38]; $timeToCancelBuyMins = $spread[$y][39]; $lowMarketModeEnabled = $spread[$y][40];
    for ($q=0;$q<$SpreadBetUserSettingsSize;$q++){
      $tempUserID = $SpreadBetUserSettings[$q][3];
      if ($UserID == $tempUserID){
        $totalNoOfBuys = $SpreadBetUserSettings[$q][1];
        $noOfBuysPerCoin = $SpreadBetUserSettings[$q][0];
        $divideAllocation = $SpreadBetUserSettings[$q][2];
      }
    }
    Echo "<BR> Checking $ID | 1Hr: $Hr1ChangePctChange | 24Hr: $Hr24ChangePctChange | 7d: $d7ChangePctChange";
    if (!Empty($KEK)){$APISecret = decrypt($KEK,$spread[$y][25]);}
    if ($disableUntil > date("Y-m-d H:i:s", time())){ echo "<BR> EXIT: Disabled until: ".$disableUntil; continue;}
    if ($pctofSixMonthHigh > 90){echo "<BR> EXIT: SixMonthHigh: $pctofSixMonthHigh"; continue;}
    if ($pctofAllTimeHigh > 90){echo "<BR> EXIT: AllTimeMonthHigh: $pctofAllTimeHigh"; continue;}
    Echo "<BR>1) $Hr24ChangePctChange : $Hr24BuyPrice | $d7ChangePctChange : $D7BuyPrice | $Hr1ChangePctChange : $Hr1BuyPrice";
    if ($Hr24ChangePctChange <= $Hr24BuyPrice and $d7ChangePctChange <= $D7BuyPrice and $Hr1ChangePctChange >= $Hr1BuyPrice){
      $openCoins = getOpenSpreadCoins($userID);
      $openCoinsSize = count($openCoins);

      for ($v=0; $v<$openCoinsSize; $v++){
        Echo "<BR> Checking getOpenSpreadCoins : $ID | ".$openCoins[$v][0];
        if ($openCoins[$v][0] == $ID AND $openCoins[$v][1] == $userID AND $openCoins[$v][2] >= $noOfBuysPerCoin){
          if ($openCoinsSize >= $totalNoOfBuys){
            continue 2;
          }
        }
      }
      //GetCoinData
      echo "<BR> getSpreadCoinData($ID); ";
      $spreadCoins = getSpreadCoinData($ID);
      $spreadCoinsSize = count($spreadCoins);
      Echo "<BR> Buy Spread Coins : $spreadCoinsSize | $spreadBetTransID | $spreadCoinsSize";
      //How much to buy
      Echo "<BR>checkOpenSpreadBet($UserID,$ID); ";
      $openCoins = checkOpenSpreadBet($UserID,$ID);
      $openCoinsSize = $openCoins[0][4];
      if(!isset($openCoinsSize)){$openCoinsSize=0;}
      $purchasePrice = $openCoins[0][1]; $totalAmountToBuy = $openCoins[0][2];
      $savedBTCAmount = $openCoins[0][3];
      $loopNum = 0;
      $availableTrans = $totalNoOfBuys - $openCoinsSize;
      Echo "<BR> Test for SpreadBetRePurchase: $purchasePrice | $totalAmountToBuy | $openCoinsSize | $totalNoOfBuys | $availableTrans";
      if ($openCoinsSize < $totalNoOfBuys and $availableTrans > 0){
        //$spreadBetToBuy = getCoinAllocation($UserID);
        //if ($lowMarketModeEnabled > 0){ $lowMarketMode = True;}else {$lowMarketMode = False;}
        $spreadBetToBuy = getNewCoinAllocation($baseCurrency,$UserID,FALSE);
        $BTCtoSQL = ($spreadBetToBuy/($divideAllocation - $openCoinsSize));
        $buyPerCoin = ($spreadBetToBuy/($divideAllocation - $openCoinsSize)); //*$inverseAvgHighPct
        $BTCAmount =  $buyPerCoin/$spreadCoinsSize;
        LogToSQL("SpreadBetCoinAllocation","BTCAmount: $BTCAmount | DivAlloc: $divideAllocation | OpenCoinSize: $openCoinsSize | $inverseAvgHighPct | $totalNoOfBuys | $availableTrans | ".$spreadBetToBuy[0][0],3,$GLOBALS['logToSQLSetting']);
        if ($BTCAmount < 10){ ECHO "<BR> EXIT: Coin Allocation: ".$spreadBetToBuy[0][0]." | Div Alloc: $divideAllocation | inv pct: $inverseAvgHighPct | Buy Per Coin: $buyPerCoin | BTCAmount: $BTCAmount"; continue;}
      //}elseif ($availableTrans == 0){
      //  $BTCAmount =  $spreadBetToBuy[0][0]/$spreadCoinsSize;
      }else{ ECHO "<BR> EXIT: $openCoinsSize | $totalNoOfBuys | $availableTrans"; continue;}

      if ($purchasePrice < $totalAmountToBuy) {
        $buyPerCoin =  $totalAmountToBuy - $purchasePrice;
        $noOfLoops = floor($buyPerCoin/$savedBTCAmount);
        $BTCAmount = $buyPerCoin /$noOfLoops;
        $loopNum = rand(0,$spreadCoinsSize- $noOfLoops);
        $spreadCoinsSize = $loopNum + $noOfLoops;
        LogToSQL("SpreadBetRePurchase","PurchasePrice: $purchasePrice | TotalAmountToBuy: $totalAmountToBuy | BuyPerCoin: $buyPerCoin | NoOfLoops:$noOfLoops | BTCAmount: $BTCAmount | LoppNum:$loopNum | SpreadCoinSize: $spreadCoinsSize" ,$UserID,$GLOBALS['logToSQLSetting']);
        if ($BTCAmount < 10){ ECHO "<BR> EXIT: Coin Allocation: ".$spreadBetToBuy[0][0]." | Div Alloc: $divideAllocation | inv pct: $inverseAvgHighPct | Buy Per Coin: $buyPerCoin | BTCAmount: $BTCAmount"; continue;}

      }else{ ECHO "<BR> EXIT: $openCoinsSize | $totalNoOfBuys | $availableTrans"; continue;}

      LogToSQL("SpreadBetBuy","1)ID: $ID | $Hr24ChangePctChange : $Hr24BuyPrice | $d7ChangePctChange : $D7BuyPrice | $Hr1ChangePctChange : $Hr1BuyPrice;",3,$GLOBALS['logToSQLSetting']);
      LogToSQL("SpreadBetBuy","Buy Spread Coins : $spreadCoinsSize | $spreadBetTransID | $spreadCoinsSize | BTCAmount: $BTCAmount",3,$GLOBALS['logToSQLSetting']);
      for ($t=$loopNum; $t<$spreadCoinsSize; $t++){
        Echo "<BR> Purchasing Coin: $coinID | $t | $spreadCoinsSize";
        $coinID = $spreadCoins[$t][0];$symbol = $spreadCoins[$t][1]; $spreadBetRuleID = $spreadCoins[$t][41];
        $liveCoinPrice = $spreadCoins[$t][17];
        $date = date("Y-m-d H:i:s", time()); $SendEmail = 1; $BuyCoin = 1;$ruleIDBuy = 9999995;$CoinSellOffsetEnabled = 0; $CoinSellOffsetPct = 0;
        $buyType = 1;  $SellRuleFixed = 9999995;$noOfPurchases = 0;


        //BuyCoins
        echo "<BR>buyCoins($APIKey, $APISecret,$symbol, $Email, $userID, $date, $baseCurrency,$SendEmail,$BuyCoin,$BTCAmount, $ruleIDBuy,$UserName,$coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed, 0, $noOfPurchases+1);";
        //$checkBuy = buyCoins($APIKey, $APISecret,$symbol, $Email, $userID, $date, $baseCurrency,$SendEmail,$BuyCoin,$BTCAmount, $ruleIDBuy,$UserName,$coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed, 0, $noOfPurchases+1);
        $ogCoinPrice = $liveCoinPrice - (($liveCoinPrice/100)*1);
        if($BTCAmount<= 0 ){ continue;}
        LogToSQL("SpreadBetTracking","addTrackingCoin($coinID, $liveCoinPrice, $userID, $baseCurrency, $SendEmail, $BuyCoin, $BTCAmount, $ruleIDBuy, $CoinSellOffsetPct, $CoinSellOffsetEnabled, $buyType, $timeToCancelBuyMins, $SellRuleFixed,0,0,$risesInPrice,'SpreadBuy',$ogCoinPrice,$spreadBetTransID,$spreadBetRuleID);",3,$GLOBALS['logToSQLSetting']);
        addTrackingCoin($coinID, $liveCoinPrice, $userID, $baseCurrency, $SendEmail, $BuyCoin, $BTCAmount, $ruleIDBuy, $CoinSellOffsetPct, $CoinSellOffsetEnabled, $buyType, $timeToCancelBuyMins, $SellRuleFixed,0,0,$risesInPrice,'SpreadBuy',$ogCoinPrice,$spreadBetTransID,$spreadBetRuleID,0,'RunSpreadBet');
        updateSpreadBetTransactionAmount($buyPerCoin, $spreadBetRuleID,$BTCtoSQL);
        LogToSQL("SpreadBetBuy","buyCoins($coinID)",3,$GLOBALS['logToSQLSetting']);
        //update Transaction to Spread
        //updateTransToSpread($ID,$coinID,$UserID,$spreadBetTransID);
        LogToSQL("SpreadBetBuy","updateTransToSpread($ID,$coinID,$UserID,$spreadBetTransID);",3,$GLOBALS['logToSQLSetting']);
        //updateSpreadBuy($ID);
        LogToSQL("SpreadBetBuy","updateSpreadBuy($ID);",3,$GLOBALS['logToSQLSetting']);
        //add new number in SpreadBetTransactions
        if ($t == $spreadCoinsSize-1 AND $spreadCoinsSize > 0){
          echo "<BR> newSpreadTransactionID($UserID,$spreadBetRuleID); | $t";
          //newSpreadTransactionID($UserID,$spreadBetRuleID);
          LogToSQL("SpreadBetBuy","newSpreadTransactionID($UserID,$spreadBetRuleID);",3,$GLOBALS['logToSQLSetting']);
          UpdateProfit();
          LogToSQL("SpreadBetBuy","UpdateProfit();",3,$GLOBALS['logToSQLSetting']);
        }
        //subUSDTBalance('USDT', $BTCAmount,$liveCoinPrice, $userID);
        LogToSQL("SpreadBetBuy","subUSDTBalance('USDT', $BTCAmount,$liveCoinPrice, $userID);",3,$GLOBALS['logToSQLSetting']);
        logAction("runSpreadBet; addTrackingCoin : $symbol | $coinID | $liveCoinPrice | $userID | $baseCurrency | $BTCAmount | $timeToCancelBuyMins | $risesInPrice | $ogCoinPrice | $spreadBetRuleID | $ID", 'BuySellFlow', 1);
        $finalBool = True;
      }
    }
  }
  return $finalBool;
}

function runNewTrackingCoins($newTrackingCoins,$marketStats,$baseMultiplier,$ruleProfit,$coinPurchaseSettings,$clearCoinQueue,$openTransactions,$delayCoinPurchase){
  $finalBool = False;
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
    $trackCounter = initiateAry($trackCounter,$userID."-".$coinID);
    $trackCounter = initiateAry($trackCounter,$userID."-Total");
    $pctToBuy = (($allTimeHighPrice - $liveCoinPrice)/$allTimeHighPrice)*100;
    if ($baseCurrency == 'BTC'){
      $ogBTCAmount = (float)$newTrackingCoins[$a][11];
      Echo "<BR> Base Multiplier $BTCAmount | ".$baseMultiplier[0][0];
      $BTCAmount = $BTCAmount / $baseMultiplier[0][0];
    }elseif ($baseCurrency == 'ETH'){
      $ogBTCAmount = (float)$newTrackingCoins[$a][11];
      $BTCAmount = $BTCAmount / $baseMultiplier[0][1];
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
      closeNewTrackingCoin($newTrackingCoinID, True);
      reOpenOneTimeBuyRule($trackingID);
      reopenCoinSwapCancel($oldBuyBackTransID);
      if ($type == 'SavingsBuy'){ updateCoinSwapStatusCoinSwapID('AwaitingSavingsBuy',$transactionID);}
      newLogToSQL("TrackingCoins", "closeNewTrackingCoin($newTrackingCoinID); $pctProfit | $minsFromDate | $timeToCancelBuyMins", $userID, $GLOBALS['logToSQLSetting'],"MinsFromDateExceed","TrackingCoinID:$newTrackingCoinID"); Echo "<BR> MinsFromDate: $minsFromDate | ";
      $finalBool = True;
      continue;
    }
    Echo "<BR> Tracking Buy Count 1 <BR>";

    $ruleProfitSize = count($ruleProfit);
    for ($h=0; $h<$ruleProfitSize; $h++){
        if ($limitBuyAmountEnabled == 1 and $overrideCoinAlloc == 0){
          //echo "<BR> TEST limitBuyAmountEnabled: $limitBuyAmountEnabled | ".$ruleProfit[$h][4]." | $ruleIDBuy | ".$ruleProfit[$h][1]." | $limitBuyAmount";
          if ($ruleProfit[$h][4] == $ruleIDBuy and $ruleProfit[$h][1] >= $limitBuyTransactions){echo "<BR>EXIT: Rule Amount Exceeded! "; cancelTrackingBuy($ruleIDBuy); continue;}
        }
        if ($limitBuyTransactionsEnabled == 1 and $overrideCoinAlloc == 0){
          //echo "<BR> TEST limitBuyTransactionEnabled: $limitBuyTransactionsEnabled | ".$ruleProfit[$h][4]." | $ruleIDBuy | ".$ruleProfit[$h][5]." | $limitBuyTransactions";
          if ($ruleProfit[$h][4] == $ruleIDBuy and $ruleProfit[$h][5] >= $limitBuyTransactions){echo "<BR>EXIT: Rule Transaction Count Exceeded! "; cancelTrackingBuy($ruleIDBuy); continue;}
        }elseif($coinModeOverridePriceEnabled == 1 and $overrideCoinAlloc == 0){
          //echo "<BR> TEST limitBuyTransactionEnabled: $limitBuyAmount | $noOfBuyModeOverrides | ".$ruleProfit[$h][5];
          if ($ruleProfit[$h][4] == $ruleIDBuy and ($limitBuyAmount + $noOfBuyModeOverrides) >=  $ruleProfit[$h][5]){echo "<BR>EXIT: Rule Transaction Count Override Exceeded! ";cancelTrackingBuy($ruleIDBuy); continue;}
        }
    }
    Echo "<BR> Tracking Buy Count 2 <BR>";
    if ($overrideCoinAlloc == 1){ $lowBuyMode = TRUE;}else{$lowBuyMode=FALSE; }
    $coinAllocation = getNewCoinAllocation($baseCurrency,$userID,$lowBuyMode);
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
    }elseif ($coinMode == 0 AND $type == 'Buy'){
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
    if ($trackCounter[$userID."-Total"] >= $noOfBuys and $overrideCoinAlloc == 0){ echo "<BR>EXIT: Buy Counter Met! $noOfBuys ".$trackCounter[$userID."-Total"];continue;}//else{ Echo "<BR> Number of Buys: $noOfBuys BuyCounter ".$trackCounter[$userID];}
    if ($trackCounter[$userID."-".$coinID] >= 1 and $overrideCoinAlloc == 0){ echo "<BR>EXIT: Buy Counter Met! $noOfBuys ".$trackCounter[$userID."-".$coinID];continue;}//else{ Echo "<BR> Number of Buys: $noOfBuys BuyCounter ".$trackCounter[$userID];}

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
          continue;
        }
      }
      for ($u=0;$u<$totalCoinPurchasesSize;$u++){
        for ($r=0;$r<$coinPurchaseSettingsSize;$r++){
            if ($userID == $totalCoinPurchases[$u][0] and $userID == $coinPurchaseSettings[$r][0] and $totalCoinPurchases[$u][1]>=$coinPurchaseSettings[$r][2] and $overrideCoinAlloc < 1){
              echo "<BR> EXIT: User over total Coin Purchases: $coinID | $userID".$totalCoinPurchases[$u][1]."|".$coinPurchaseSettings[$r][2];
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
          closeNewTrackingCoin($newTrackingCoinID, False);
          logAction("runNewTrackingCoins; SavingsBuy : $symbol | $transactionID | $coinID | $liveCoinPrice | $newTrackingCoinID | 'AwaitingSavingsPurchase' | $quant | $rate | $baseCurrency | $type", 'BuySellFlow', 1);
          $finalBool = True;
        }
      }else{
        newLogToSQL("TrackingCoin","trackingCoinReadyToBuy($liveCoinPrice,$timeToCancelBuyMins,$type,$originalPrice,$newTrackingCoinID,$noOfRisesInPrice,$pctProfit,$minsFromDate,$lastPrice,$risesInPrice,$trackingID,$quickBuyCount,$market1HrChangePct)$coinID|$overrideCoinAlloc|".$coinAllocation[0][0]." | $type | $coinMode;",$userID,1,"TrackingSuccess","TrackingCoinID:$newTrackingCoinID");
        if (!Empty($KEK)){ $APISecret = Decrypt($KEK,$newTrackingCoins[$a][19]);}
        //if ($baseCurrency == 'BTC' OR $baseCurrency == 'ETH'){ $ogBTCAmount = (float)$ogBTCAmount;}
        if ($buyAmountCalculationEnabled == 1){
            $ogBTCAmount = ($ogBTCAmount/100)*$pctToBuy;
        }

        $checkBuy = buyCoins($APIKey, $APISecret,$symbol, $Email, $userID, $date, $baseCurrency,$SendEmail,$BuyCoin,$ogBTCAmount, $ruleIDBuy,$UserName,$coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed, $buyCoinPrice, $overrideCoinAlloc,$noOfPurchases+1);
        if ($checkBuy == 1){
          newLogToSQL("TrackingCoin","buyCoins($APIKey, $APISecret,$symbol, $Email, $userID, $date, $baseCurrency,$SendEmail,$BuyCoin,$ogBTCAmount, $ruleIDBuy,$UserName,$coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed, $buyCoinPrice, $noOfPurchases+1);",$userID,1,"BuyCoin","TrackingCoinID:$newTrackingCoinID");
          //logToSQL("TrackingCoin", "buyCoins($APIKey, $APISecret,$symbol, $Email, $userID, $date, $baseCurrency,$SendEmail,$BuyCoin,$ogBTCAmount, $ruleIDBuy,$UserName,$coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed, $buyCoinPrice, $noOfPurchases+1);", $userID,1);
          UpdateProfit();
          //subUSDTBalance('USDT', $BTCAmount,$liveCoinPrice, $userID);
          closeNewTrackingCoin($newTrackingCoinID, False);
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
          $aryCount = count($clearCoinQueue);
          //$clearCoinQueue[$aryCount] = Array($userID,$coinID);
          if (!empty($clearCoinQueue)) {
              array_push($clearCoinQueue,$userID,$coinID);
          }else{
            $clearCoinQueue = Array($userID,$coinID);
          }

          updateCoinAllocationOverride($coinID,$userID,$overrideCoinAlloc,$toMerge);
          //continue;
          if ($type == 'BuyBack'){  bittrexActionBuyBack($coinID,$oldBuyBackTransID); }
          if ($type == 'Buy' and $transactionID <> 0) { bittrexActionBuyBack($coinID,$transactionID,0);}
          logAction("runNewTrackingCoins; buyCoins : $symbol | $coinID | $coinID | $baseCurrency | $ogBTCAmount | $timeToCancelBuyMins | $buyCoinPrice | $overrideCoinAlloc | $SBRuleID", 'BuySellFlow', 1);
          buyBackDelay($coinID,0,$userID);
          return True;
        }elseif ($checkBuy == 2){
          //2 = INSUFFICIENT BAL
          closeNewTrackingCoin($newTrackingCoinID, False);
          removeTransactionDelay($coinID, $userID);
          reOpenTransactionfromBuyBack($oldBuyBackTransID);
        }
      }
    }elseif ($readyToBuy == 2){ $finalBool = True;}
  }
  return $finalBool;
}

function runTrackingSellCoin($newTrackingSellCoins,$marketStats){
  $finalBool = False;
  $newTrackingSellCoinsSize = count($newTrackingSellCoins);
  //$marketStats = getMarketstats();
  sleep(1);
  for($b = 0; $b < $newTrackingSellCoinsSize; $b++) {
    $CoinPrice = $newTrackingSellCoins[$b][0]; $TrackDate = $newTrackingSellCoins[$b][1];  $userID = $newTrackingSellCoins[$b][2]; $NoOfRisesInPrice = $newTrackingSellCoins[$b][3]; $TransactionID = $newTrackingSellCoins[$b][4];
    $BuyRule = $newTrackingSellCoins[$b][5]; $FixSellRule = $newTrackingSellCoins[$b][6]; $OrderNo = $newTrackingSellCoins[$b][7]; $Amount = $newTrackingSellCoins[$b][8]; $CoinID = $newTrackingSellCoins[$b][9];
    $APIKey = $newTrackingSellCoins[$b][10]; $APISecret = $newTrackingSellCoins[$b][11]; $KEK = $newTrackingSellCoins[$b][12]; $Email = $newTrackingSellCoins[$b][13]; $UserName = $newTrackingSellCoins[$b][14];
    $baseCurrency = $newTrackingSellCoins[$b][15]; $SendEmail = $newTrackingSellCoins[$b][16]; $SellCoin = $newTrackingSellCoins[$b][17]; $CoinSellOffsetEnabled = $newTrackingSellCoins[$b][18]; $CoinSellOffsetPct = $newTrackingSellCoins[$b][19];
    $LiveCoinPrice = $newTrackingSellCoins[$b][20]; $minsFromDate = $newTrackingSellCoins[$b][21]; $profit = $newTrackingSellCoins[$b][22]; $fee = $newTrackingSellCoins[$b][23]; $ProfitPct = $newTrackingSellCoins[$b][24];
    $totalRisesInPrice =  $newTrackingSellCoins[$b][33]; $coin = $newTrackingSellCoins[$b][26]; $ogPctProfit = $newTrackingSellCoins[$b][27]; $originalCoinPrice = $newTrackingSellCoins[$b][29];
    $minsFromStart = $newTrackingSellCoins[$b][32]; $fallsInPrice = $newTrackingSellCoins[$b][33]; $type = $newTrackingSellCoins[$b][34]; $baseSellPrice = $newTrackingSellCoins[$b][35];
    $lastPrice  = $newTrackingSellCoins[$b][36]; $BTCAmount = $newTrackingSellCoins[$b][37]; $trackingSellID = $newTrackingSellCoins[$b][38]; $saveResidualCoins = $newTrackingSellCoins[$b][39];
    $origAmount = $newTrackingSellCoins[$b][40];$trackingType = $newTrackingSellCoins[$b][41]; $originalSellPrice = $newTrackingSellCoins[$b][42];
    $market1HrChangePct = $marketStats[0][1];
    Echo "<BR> Check Sell: Cp: $CoinPrice | TRID: $TransactionID | Am: $Amount ";
    if ($minsFromDate > 1440 and $trackingType == 'SavingsSell'){
      closeNewTrackingSellCoin($TransactionID);
      updateTransStatus($TransactionID,'Saving');
      logAction("runTrackingSellCoin; CancelSavingSell : $coin | $CoinID | $baseCurrency | $userID | $minsFromDate | $TransactionID ", 'BuySellFlow', 1);
      $finalBool = True;
    }

    echo "<BR> Checking $coin : $CoinPrice ; No Of RISES $NoOfRisesInPrice ! Profit % $ProfitPct | Mins from date $minsFromDate ! Original Coin Price $originalCoinPrice | mins from Start: $minsFromStart | UserID : $userID Falls in Price: $fallsInPrice";
    $readyToSell = trackingCoinReadyToSell($LiveCoinPrice,$minsFromStart,$type,$baseSellPrice,$TransactionID,$totalRisesInPrice,$ProfitPct,$minsFromDate,$lastPrice,$NoOfRisesInPrice,$trackingSellID,$market1HrChangePct,$originalSellPrice);
    if ($readyToSell == 1){
      $PurchasePrice = ($Amount*$CoinPrice);
      $salePrice = $LiveCoinPrice * $Amount;
      $profit = $salePrice - $PurchasePrice;
      $ProfitPct = ($profit/$PurchasePrice)*100;
      if ($trackingType == 'SavingsSell'){
        echo "<BR> $CoinID | $coin | $ProfitPct";
        $quant = $Amount;
        $apiConfig = getAPIConfig($userID);
        $apikey = $apiConfig[0][0];$apisecret = $apiConfig[0][1]; $kek = $apiConfig[0][2];

        if (!Empty($kek)){ $apisecret = Decrypt($kek,$apiConfig[0][1]);}
        newLogToSQL("SellSavings", "bittrexsell($apikey, $apisecret, $coin, $Amount, $LiveCoinPrice, $baseCurrency, 3, False);", $userID, $GLOBALS['logToSQLSetting'],"Sell Coin","TransactionID:$transactionID");
        $obj = bittrexsell($apikey, $apisecret, $coin, $Amount, $LiveCoinPrice, $baseCurrency, 3, False);
        //Add to Swap Coin Table
        $bittrexRef = $obj["id"];
        if ($bittrexRef <> ""){
          updateCoinSwapTransactionStatus('SavingsSell',$TransactionID);
          newLogToSQL("SellSavings", "Sell Savings Coin: $CoinID | $bittrexRef", $userID, 1,"Sell Coin","TransactionID:$TransactionID");
          updateCoinSwapTable($TransactionID,'AwaitingSavingsSale',$bittrexRef,$CoinID,$LiveCoinPrice,$baseCurrency,$LiveCoinPrice * $Amount,$CoinPrice * $Amount,'Sell');
          closeNewTrackingSellCoin($TransactionID);
          logAction("runTrackingSellCoin; SavingsSell : $coin | $CoinID | $baseCurrency | $LiveCoinPrice | $Amount | $userID | $minsFromDate | $TransactionID | $bittrexRef", 'BuySellFlow', 1);
          //return True;
        }else{
          newLogToSQL("SellSavingsError", var_dump($obj), $userID, $GLOBALS['logToSQLSetting'],"Sell Coin","TransactionID:$TransactionID");
        }

      }else{
        if (!Empty($KEK)){ $APISecret = Decrypt($KEK,$newTrackingSellCoins[$b][11]);}



          //LogToSQL("SaveResidualCoins","$saveResidualCoins",3,1);
          newLogToSQL("TrackingSell","$coin | $CoinID | $CoinPrice | $LiveCoinPrice | $Amount | $TransactionID | $saveResidualCoins $type | $ProfitPct | $PurchasePrice | $salePrice | $profit",3,1,"SaveResidualCoins","TransactionID:$TransactionID");
          if ($saveResidualCoins == 1 and $ProfitPct >= 0.25){
            $oldAmount = $Amount;
            if ($origAmount == 0){
              //$tempFee = number_format(((($LiveCoinPrice*$Amount)/100)*0.25),8);
              //$ogPurchasePrice = $LiveCoinPrice*$Amount;
              $sellFee = ($PurchasePrice/100)*0.28;
              $Amount = (($PurchasePrice+$sellFee) / $LiveCoinPrice);
              newLogToSQL("TrackingSell","$PurchasePrice | $sellFee | $LiveCoinPrice | $Amount",3,1,"SaveResidual","TransactionID:$TransactionID");
            }
            newLogToSQL("TrackingSell","$oldAmount | $Amount | $PurchasePrice | $sellFee | $LiveCoinPrice | $ProfitPct",3,$GLOBALS['logToSQLSetting'],"NewAmountToSQL","TransactionID:$TransactionID");
            if ($origAmount == 0){
              updateSellAmount($TransactionID,$Amount, $oldAmount);
            }
            newLogToSQL("TrackingSell","updateSellAmount($TransactionID,$Amount, $oldAmount);",3,$GLOBALS['logToSQLSetting'],"SaveResidualCoins4","TransactionID:$TransactionID");
            newLogToSQL("TrackingSell","$coin | $CoinID | $oldAmount | $CoinPrice | $PurchasePrice | $LiveCoinPrice | $Amount | $TransactionID | $tempFee",3,$GLOBALS['logToSQLSetting'],"SaveResidualCoins2","TransactionID:$TransactionID");
            $newOrderDate = date("YmdHis", time());
            $OrderString = "ORD".$coin.$newOrderDate.$BuyRule;
            $residualAmount = $oldAmount - $Amount;
            //ResidualCoinsToSaving($residualAmount,$OrderString ,$TransactionID);
            //newLogToSQL("TrackingSell","ResidualCoinsToSaving($oldAmount-$Amount, ORD.$coin.$newOrderDate.$BuyRule,$TransactionID);",3,1,"SaveResidualCoins3","TransactionID:$TransactionID");
          }
          newLogToSQL("TrackingSell","sellCoins($APIKey, $APISecret,$coin, $Email, $userID, 0,$newOrderDate, $baseCurrency,$SendEmail,$SellCoin, $FixSellRule,$UserName,$OrderNo,$Amount,$CoinPrice,$TransactionID,$CoinID,$CoinSellOffsetEnabled,$CoinSellOffsetPct,$LiveCoinPrice, $type);",3,1,"Success","TransactionID:$TransactionID");
        $checkSell = sellCoins($APIKey, $APISecret,$coin, $Email, $userID, 0,$newOrderDate, $baseCurrency,$SendEmail,$SellCoin, $FixSellRule,$UserName,$OrderNo,$Amount,$CoinPrice,$TransactionID,$CoinID,$CoinSellOffsetEnabled,$CoinSellOffsetPct,$LiveCoinPrice, $type);

        if ($checkSell){
          closeNewTrackingSellCoin($TransactionID);
          newLogToSQL("TrackingSell","sellCoins($APIKey, $APISecret,$coin, $Email, $userID, 0,$newOrderDate, $baseCurrency,$SendEmail,$SellCoin, $FixSellRule,$UserName,$OrderNo,$Amount,$CoinPrice,$TransactionID,$CoinID,$CoinSellOffsetEnabled,$CoinSellOffsetPct,$LiveCoinPrice, $type);",3,$GLOBALS['logToSQLSetting'],"Success","TransactionID:$TransactionID");
          addUSDTBalance('USDT', $BTCAmount,$LiveCoinPrice, $userID);
          logAction("runTrackingSellCoin; sellCoins : $coin | $CoinID | $baseCurrency | $LiveCoinPrice | $CoinPrice | $Amount | $userID | $minsFromDate | $type | $TransactionID", 'BuySellFlow', 1);
          if ($saveResidualCoins == 1){
            $finalResidual = ($oldAmount-$Amount);
            newLogToSQL("TrackingSell:Residual","$finalResidual = ($oldAmount-$Amount);",3,1,"ResidualAmount","TransactionID:$TransactionID");
            saveResidualAmountToBittrex($TransactionID,$finalResidual);
          }
        }
      }
      $finalBool = True;
    }elseif ($readyToSell == 2){
      $finalBool = True;
    }

  }
  return $finalBool;
}

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
      if (!Empty($KEK)){$APISecret = decrypt($KEK,$buyRules[$y][31]);}

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

      $profitNum = findUserProfit($userProfit,$userID);
      if ($totalProfitPauseEnabled == 1 && $profitNum<= $totalProfitPause && $ruleIDBuy == $rulesPause){
        if ($rulesPauseEnabled == 1){
          echo "<BR> PAUSING RULES $rulesPause for $rulesPauseHours HOURS";
          newLogToSQL("BuyCoins", "pauseRule($rulesPause, $rulesPauseHours);", $userID,$GLOBALS['logToSQLSetting'],"RulesPause","RuleID:$ruleIDBuy CoinID:$coinID");
          pauseRule($rulesPause, $rulesPauseHours);
        }
        echo "<BR>EXIT: TotalProfitPauseEnabled $totalProfitPauseEnabled Profit: $profitNum $totalProfitPause ";
        continue;}
      $GLOBALS['allDisabled'] = false;
      if (empty($APIKey) && empty($APISecret)){ continue;}
      if ($APIKey=="NA" && $APISecret == "NA"){ Echo "<BR> EXIT: API Key Missing: $userID $APIKey $ruleIDBuy<BR>"; continue;}
      if ($limitToBaseCurrency != "ALL" && $baseCurrency != $limitToBaseCurrency){ Echo "<BR> EXIT: Wrong Base Currency: $userID $baseCurrency $limitToBaseCurrency $ruleIDBuy<BR>";continue;}
      if ($baseCurrency != $userBaseCurrency && $userBaseCurrency != "ALL"){ Echo "<BR> EXIT: Wrong User Base Currency: $userID $baseCurrency $userBaseCurrency $ruleIDBuy<BR>";continue;}
      if ($limitToCoin != "ALL" && $symbol != $limitToCoin) { Echo "<BR> EXIT: Limit to Coin: $userID $symbol $limitToCoin<BR>"; continue;}
      if ($doNotBuy == 1){Echo "<BR> EXIT: Do Not Buy<BR>"; continue;}
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
      if ($buyCounter[$userID."-".$coinID] >= 1 && $overrideDailyLimit == 0){ echo "<BR>EXIT: Buy Counter Met! $noOfBuys ".$buyCounter[$userID."-".$coinID];continue;
      }else{ Echo "<BR> Number of Coin Buys: 1 BuyCounter ".$buyCounter[$userID."-".$coinID];}
      if ($buyCounter[$userID."-Total"] >= $noOfBuys && $overrideDailyLimit == 0){ echo "<BR>EXIT: Buy Counter Met! $noOfBuys ".$buyCounter[$userID."-Total"];continue;
      }else{ Echo "<BR> Number of Total Buys: $noOfBuys BuyCounter ".$buyCounter[$userID."-Total"];}
      if ($userActive == False){ echo "<BR>EXIT: User Not Active!"; continue;}
      if ($disableUntil > date("Y-m-d H:i:s", time())){ echo "<BR> EXIT: Disabled until: ".$disableUntil; continue;}
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
      //Echo "<BR> TEST 10: $BuyPriceMinEnabled | $BuyPriceMin | $LiveCoinPrice | $test10";
      $buyResultAry[] = Array($test10, "Buy Price Minimum $symbol", $LiveCoinPrice);
      $test11 = autoBuyMain($LiveCoinPrice,$autoBuyPrice, $autoBuyCoinEnabled,$coinID);
      $buyResultAry[] = Array($test11, "Auto Buy Price $symbol", $LiveCoinPrice);
      $test12 = coinMatchPattern($coinPriceMatch,$LiveCoinPrice,$symbol,0,$coinPricePatternEnabled,$ruleIDBuy,0);
      $buyResultAry[] = Array($test12, "Coin Price Pattern $symbol", $LiveCoinPrice);
      if ($Hr1ChangeTrendEnabled){
        $test14 = newBuywithPattern($new1HrPriceChange,$coin1HrPatternList,$Hr1ChangeTrendEnabled,$ruleIDBuy,0);
      }else{$test14 = True;}
      $buyResultAry[] = Array($test14, "1 Hour Price Pattern $symbol", $new1HrPriceChange);
      $test13 = $GLOBALS['allDisabled'];
      if (buyAmountOverride($buyAmountOverrideEnabled)){$BTCAmount = $buyAmountOverride; Echo "<BR> 13: BuyAmountOverride set to : $buyAmountOverride | BTCAmount: $BTCAmount";}
      $totalScore_Buy = $test1+$test2+$test3+$test4+$test5+$test6+$test7+$test8+$test9+$test10+$test11+$test12+$test13+$test14;
      if ($totalScore_Buy >= 13 ){
        $buyOutstanding = getOutStandingBuy($buyResultAry);
        logAction("UserID: $userID | RuleID: $ruleIDBuy | Coin : $symbol | 1:  $test1  2:  $test2  3:  $test3  4:  $test4  5:  $test5  6:  $test6  7:  $test7  8:  $test8  9:  $test9  10:  $test10  11:  $test11  12:  $test12  13:  $test13  14:  $test14  TOTAL: $totalScore_Buy / 14 $buyOutstanding","BuyScore", $GLOBALS['logToFileSetting'] );
      }
      Echo "<BR> UserID: $userID | RuleID: $ruleIDBuy | Coin : $symbol| 1:$test1  2:$test2  3:$test3  4:$test4  5:$test5  6:$test6  7:$test7  8:$test8  9:$test9  10:$test10  11:$test11  12:$test12  13:$test13  14:$test14  TOTAL:$totalScore_Buy / 14";
      if ($test1 == True && $test2 == True && $test3 == True && $test4 == True && $test5 == True && $test6 == True && $test7 == True && $test8 == True && $test9 == True && $test10 == True &&
      $test11 == True && $test12 == True && $test13 == True && $test14 == True){
        $date = date("Y-m-d H:i:s", time());
      echo "<BR> Call Bittrex Bal: bittrexbalance($APIKey, $APISecret,$baseCurrency, $apiVersion);";
        $BTCBalance = bittrexbalance($APIKey, $APISecret,$baseCurrency, $apiVersion);
        $reservedAmount = getReservedAmount($baseCurrency,$userID);
        Echo "<BR> TEST BAL AND RES: $BTCBalance ; $BTCAmount ; ".$reservedAmount[0][0]."| "; //.$BTCBalance-$reservedAmount
        Echo "<BR> TEST BAL AND RES: $BTCBalance ; $BTCAmount ; ".$reservedAmount[0][0]." | "; //.$BTCBalance-$reservedAmount
        $usdtReserved = $reservedAmount[0][0] * $reservedAmount[0][3];
        $btcReserved = ($reservedAmount[0][1] * $reservedAmount[0][4])*$baseMultiplier[0][0];
        $ethReserved = ($reservedAmount[0][2] * $reservedAmount[0][5])*$baseMultiplier[0][1];
        $totalReserved = $usdtReserved+$btcReserved+$ethReserved;

        if ($baseCurrency == 'BTC'){
          echo "<BR> BTC Bal Test : $BTCBalance | $totalReserved | ".$baseMultiplier[0][0];
          $totalBal = ($BTCBalance*$baseMultiplier[0][0])-$totalReserved;
          $buyQuantity = $BTCAmount / $baseMultiplier[0][0];
          newLogToSQL("BuyCoins","BaseCurrency is BTC : totalBal: $totalBal | BTC Bal: $BTCBalance | totalReserved: $totalReserved | Multiplier : ".$baseMultiplier[0][0],3,$GLOBALS['logToSQLSetting'],"BTCTest","RuleID:$ruleIDBuy CoinID:$coinID");
        }elseif ($baseCurrency == 'ETH'){
          $totalBal = ($BTCBalance * $baseMultiplier[0][1])-$totalReserved;
          $buyQuantity = $BTCAmount / $baseMultiplier[0][1];
          newLogToSQL("BuyCoins","BaseCurrency is ETH : totalBal: $totalBal | Multiplier : ".$baseMultiplier[0][1],3,$GLOBALS['logToSQLSetting'],"ETHTest","RuleID:$ruleIDBuy CoinID:$coinID");
        }else{
          $totalBal = $BTCBalance-$totalReserved;
          $buyQuantity = $BTCAmount;
        }
        newLogToSQL("BuyCoins"," $totalBal | $BTCAmount",3,$GLOBALS['logToSQLSetting'],"OneTimeBuyRuleTest","RuleID:$ruleIDBuy CoinID:$coinID");
        if ($totalBal > 20 OR $overrideCoinAlloc == 1) {
          echo "<BR>Buying Coins: $APIKey, $APISecret,$symbol, $Email, $userID, $date, $baseCurrency,$SendEmail,$BuyCoin,$BTCAmount, $ruleIDBuy,$UserName,$coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed";
          if($BTCAmount <= 0 ){ continue;}
          addTrackingCoin($coinID, $LiveCoinPrice, $userID, $baseCurrency, $SendEmail, $BuyCoin, $buyQuantity, $ruleIDBuy, $CoinSellOffsetPct, $CoinSellOffsetEnabled, $buyType, $timeToCancelBuyMins, $SellRuleFixed,0,0,$risesInPrice,'Buy',$LiveCoinPrice,0,0,$overrideCoinAlloc,'BuyCoins');
          newLogToSQL("BuyCoins","addTrackingCoin($coinID, $LiveCoinPrice, $userID, $baseCurrency, $SendEmail, $BuyCoin, $buyQuantity, $ruleIDBuy, $CoinSellOffsetPct, $CoinSellOffsetEnabled, $buyType, $timeToCancelBuyMins, $SellRuleFixed,0,0,$risesInPrice,'Buy',$LiveCoinPrice,0,0);",3,$GLOBALS['logToSQLSetting'],"AddTrackingCoin","RuleID:$ruleIDBuy CoinID:$coinID");
          $buyCounter[$userID."-".$coinID] = $buyCounter[$userID."-".$coinID] + 1;
          $buyCounter[$userID."-Total"] = $buyCounter[$userID."-Total"] + 1;
          if ($oneTimeBuy == 1){ disableBuyRule($ruleIDBuy);}
          logAction("runBuyCoins; addTrackingCoin : $symbol | $coinID | $LiveCoinPrice | $buyQuantity | $userID | $baseCurrency $timeToCancelBuyMins | $risesInPrice | $overrideCoinAlloc", 'BuySellFlow', 1);
          $finalBool = True;
        }else{ echo "<BR> EXIT: $totalBal Less than 20 | $totalBal";}
      }

      echo "<BR> NEXT RULE <BR>";
    }//Rule Loop
  }//Coin Loop
  return $finalBool;
}

function runSellCoins($sellRules,$sellCoins,$userProfit,$coinPriceMatch,$coinPricePatternList,$coin1HrPatternList,$autoBuyPrice){
  $finalBool = False;
  $sellRulesSize = count($sellRules);
  $sellCoinsLength = count($sellCoins);
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
    $noOfCoinSwapsThisWeek  = $sellCoins[$a][53]; $captureTrend = $sellCoins[$a][59]; $minsFromBuy = $sellCoins[$a][61];
    $price4Trend = $sellCoins[$a][37]; $price3Trend = $sellCoins[$a][38]; $lastPriceTrend = $sellCoins[$a][39];  $livePriceTrend = $sellCoins[$a][40];
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
      $sellAllCoinsEnabled = $sellRules[$z][48]; $sellAllCoinsPct = $sellRules[$z][49];
      $profitNum = findUserProfit($userProfit,$userID);
      $coinSwapEnabled = $sellRules[$z][50]; $coinSwapAmount = $sellRules[$z][51]; $noOfCoinSwapsPerWeek = $sellRules[$z][52];
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
      if ($userID != $sellCoinsUserID){ continue; }
      if ($limitToCoinSell != "ALL" && $coin != $limitToCoinSell) { continue;}

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
      Echo "<BR> UserID: $userID | RuleID: $ruleIDSell | Coin : $coin | 1:$sTest1  2:$sTest2  3:$sTest3  4:$sTest4  5:$sTest5  6:$sTest6  7:$sTest7  8:$sTest8  9:$sTest9  10:$sTest10  11:$sTest11  12:$sTest12 13:$sTest13 TOTAL:$totalScore_Sell / 13, PROFIT:$profit MinsFromBuy:$minsFromBuy";
      if ($totalScore_Sell >= 12){
        $sellOutstanding = getOutStandingBuy($sellResultAry);
        logAction("UserID: $userID | RuleID: $ruleIDSell | Coin : $coin | 1:  $sTest1  2:  $sTest2  3:  $sTest3  4:  $sTest4  5:  $sTest5  6:  $sTest6  7:  $sTest7  8:  $sTest8  9:  $sTest9  10:  $sTest10  11:  $sTest11  12:  $sTest12 13: $sTest13 TOTAL:  $totalScore_Sell / 13, PROFIT: $profit $sellOutstanding","SellScore", $GLOBALS['logToFileSetting'] );
        //logToSQL("SellCoins", "RuleID: $ruleIDSell | Coin : $coin | TOTAL: $totalScore_Sell $sellOutstanding", $userID, $GLOBALS['logToSQLSetting']);
      }


      if ($sTest1 == True && $sTest2 == True && $sTest3 == True && $sTest4 == True && $sTest5 == True && $sTest6 == True && $sTest7 == True && $sTest8 == True && $sTest9 == True && $sTest10 == True
      && $sTest11 == True && $sTest12 == True && $sTest13 == True && $minsFromBuy > 25){
        $date = date("Y-m-d H:i:s", time());
        echo "<BR>Sell Coins: $APIKey, $APISecret,$coin, $Email, $userID, 0,$date, $BaseCurrency,$SendEmail,$SellCoin, _.$ruleIDSell,$UserName,$orderNo,$amount,$cost,$transactionID,$coinID<BR>";
        //sellCoins($apikey, $apisecret, $coin, $email, $userID, $score, $date,$baseCurrency, $sendEmail, $sellCoin, $ruleID,$userName, $orderNo,$amount,$cost,$transactionID,$coinID){
        //sellCoins($APIKey, $APISecret,$coin, $Email, $userID, 0,$date, $BaseCurrency,$SendEmail,$SellCoin, $ruleIDSell,$UserName,$orderNo,$amount,$cost,$transactionID,$coinID,$sellCoinOffsetEnabled,$sellCoinOffsetPct,$LiveCoinPrice);
        newTrackingSellCoins($LiveCoinPrice,$userID, $transactionID,$SellCoin, $SendEmail,$sellCoinOffsetEnabled,$sellCoinOffsetPct,$fallsInPrice,'Sell','RunSellCoins');
        setTransactionPending($transactionID);
        logAction("sellCoins($APIKey, $APISecret,$coin, $Email, $userID, 0,$date, $BaseCurrency,$SendEmail,$SellCoin, $ruleIDSell,$UserName,$orderNo,$amount,$cost,$transactionID,$coinID,$sellCoinOffsetEnabled,$sellCoinOffsetPct,$LiveCoinPrice)",'BuySell', $GLOBALS['logToFileSetting'] );
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
    $BTCBalance = bittrexbalance($apikey, $apisecret,$baseCurrency, $apiVersion);
    $buyPrice = ($cost * $amount);
    $sellPrice = ($LiveCoinPrice * $amount);
    $fee = (($LiveCoinPrice * $amount)/100)*0.25;
    $profit = ((($sellPrice-$fee)-$buyPrice)/$buyPrice)*100;
    //echo "<BR> TESTING: Profit $profit PctToPurchase $pctToPurchase LowPricePurchaseEnabled $lowPricePurchaseEnabled NoOfPurchases $noOfPurchases PurchaseLimit $purchaseLimit ToMerge $toMerge";
    if ($profit <= $pctToPurchase  && $BTCBalance >= 20 && $lowPricePurchaseEnabled == 1 && $noOfPurchases < $purchaseLimit && $toMerge == 0 && $mergeCoinEnabled == 1){
      //Buy Coin
      /*if($btcBuyAmountSell <= 0 ){ continue;}
      addTrackingCoin($coinID, $LiveCoinPrice, $userID, $baseCurrency, $SendEmail, 1, $btcBuyAmountSell, 999991, 0, 0, 1, 90, $fixSellRule,1,$noOfPurchases,1,'Buy',$LiveCoinPrice,0,0,0);
      echo "<BR> TEST New Buy Coin addTrackingCoin($coinID, $LiveCoinPrice, $userID, $baseCurrency, $SendEmail, 1, $btcBuyAmountSell, 999991, 0, 0, 1, 90, $fixSellRule, 1);";
      newLogToSQL("SellCoins", "addTrackingCoin($coinID, $LiveCoinPrice, $userID, $baseCurrency, $SendEmail, 1, $btcBuyAmountSell, 999991, 0, 0, 1, 90, $fixSellRule,1,$noOfPurchases);", $userID, $GLOBALS['logToSQLSetting'],"MergeCoins","TransactionID:$transactionID");
      //Update ToMerge
      updateTrackingCoinToMerge($transactionID,$noOfPurchases);
      newLogToSQL("SellCoins", "updateTrackingCoinToMerge($transactionID,$noOfPurchases);", $userID, $GLOBALS['logToSQLSetting'],"MergeCoins","TransactionID:$transactionID");*/
    }
  }//Sell Coin Loop
  return $finalBool;
}

function runBittrex($BittrexReqs,$apiVersion){
  $finalBool = False;
  $BittrexReqsSize = count($BittrexReqs);
  sleep(1);
  for($b = 0; $b < $BittrexReqsSize; $b++) {
    //Variables
    $type = $BittrexReqs[$b][0]; $uuid = $BittrexReqs[$b][1]; $date = $BittrexReqs[$b][2]; $status = $BittrexReqs[$b][4];   $bitPrice = $BittrexReqs[$b][5]; $userName = $BittrexReqs[$b][6];
    $apiKey = $BittrexReqs[$b][7]; $apiSecret = $BittrexReqs[$b][8]; $coin = $BittrexReqs[$b][9];$amount = $BittrexReqs[$b][10];$cost = $BittrexReqs[$b][11];$userID = $BittrexReqs[$b][12];
    $email = $BittrexReqs[$b][13]; $orderNo = $BittrexReqs[$b][14];$transactionID = $BittrexReqs[$b][15]; $totalScore = 0; $baseCurrency = $BittrexReqs[$b][16]; $ruleIDBTBuy = $BittrexReqs[$b][17];
    $sendEmail = 1; $daysOutstanding = $BittrexReqs[$b][18]; $timeSinceAction = $BittrexReqs[$b][19]; $coinID = $BittrexReqs[$b][20]; $ruleIDBTSell = $BittrexReqs[$b][21]; $orderDate = $BittrexReqs[$b][28];
    $liveCoinPriceBit = $BittrexReqs[$b][22]; $buyCancelTime = substr($BittrexReqs[$b][23],0,strlen($BittrexReqs[$b][23])-1); $sellFlag = false; $spreadBetRuleID = $BittrexReqs[$b][30];
    $spreadBetTransactionID  = $BittrexReqs[$b][31]; $redirectPurchasesToSpread = $BittrexReqs[$b][32]; $spreadBetIDRedirect = $BittrexReqs[$b][33];
    $coinModeRule = $BittrexReqs[$b][27]; $pctToSave = $BittrexReqs[$b][29]; $minsToPause = $BittrexReqs[$b][34]; $originalAmount = $BittrexReqs[$b][35]; $saveResidualCoins = $BittrexReqs[$b][36];
    $KEK = $BittrexReqs[$b][25]; $Day7Change = $BittrexReqs[$b][26]; $minsSinceAction = $BittrexReqs[$b][37]; $timeToCancelMins = $BittrexReqs[$b][38]; $buyBack = $BittrexReqs[$b][39];
    $oldBuyBackTransID = $BittrexReqs[$b][40]; $newResidualAmount = $BittrexReqs[$b][41];
    if (!Empty($KEK)){$apiSecret = decrypt($KEK,$BittrexReqs[$b][8]);}
    $buyOrderCancelTime = $BittrexReqs[$b][24];
    if ($liveCoinPriceBit != 0 && $bitPrice != 0){$pctFromSale =  (($liveCoinPriceBit-$bitPrice)/$bitPrice)*100;}
    if ($liveCoinPriceBit != 0 && $cost != 0){$liveProfitPct = ($liveCoinPriceBit-$cost)/$cost*100;}
    //echo "<BR> bittrexOrder($apiKey, $apiSecret, $uuid);";
    $resultOrd = bittrexOrder($apiKey, $apiSecret, $uuid, $apiVersion);
    if ($apiVersion == 1){
      $finalPrice = number_format((float)$resultOrd["result"]["PricePerUnit"], 8, '.', '');
      $orderQty = $resultOrd["result"]["Quantity"]; $orderQtyRemaining = $resultOrd["result"]["QuantityRemaining"];
      $orderIsOpen = $resultOrd["result"]["IsOpen"];
      $cancelInit = $resultOrd["result"]["CancelInitiated"];$status = $resultOrd["success"];
      $qtySold = $orderQty-$orderQtyRemaining;
    }else{
      $tempPrice = number_format((float)$resultOrd["proceeds"], 8, '.', '');
      $orderQty = $resultOrd["quantity"];
      $finalPrice = $tempPrice/$orderQty;
      //$cancelInit = $resultOrd["result"]["CancelInitiated"];
      $qtySold = $resultOrd["fillQuantity"];

      $orderQtyRemaining = $orderQty-$qtySold;
      if ($resultOrd["status"] == 'OPEN'){$status = 1;$cancelInit = 1;$orderIsOpen = 1;}else{$status = 0; $cancelInit = 0;$orderIsOpen = 0;}

    }

    $newPurchasePrice = $amount*$cost;
    $newCost = ($amount*$cost)/$liveCoinPriceBit;
    //$newResidualAmount =  $cost - $newCost;
    //if ($orderQtyRemaining=0){$orderIsOpen = false;}
    echo "<BR> ------COIN to Sell: ".$coin."-------- USER: ".$userName;
    //echo "<BR> Buy Cancel Time: $buyCancelTime";
    echo "TIME SINCE ACTION: $minsSinceAction | $timeToCancelMins";
    //Print_r("What is Happening? // BITREXTID = ".$uuid."<br>");
    //echo "<BR> Result IS OPEN? : ".$orderIsOpen." // CANCEL initiated: ".$cancelInit;
    updateBittrexQuantityFilled($qtySold,$uuid);
    if ($qtySold <> 0){ newLogToSQL("Bittrex", "Quantity Updated to : $qtySold for OrderNo: $orderNo", $userID, $GLOBALS['logToSQLSetting'],"UpdateQtyFilled","TransactionID:$transactionID");}
    echo "<BR> New Test: $type | ".$resultOrd["quantity"];
    //if (!isset($resultOrd["quantity"])){
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
          newLogToSQL("BittrexBuy", "Order Complete for OrderNo: $orderNo Final Price: $finalPrice | Type: $type", $userID, $GLOBALS['logToSQLSetting'],"OrderComplete","TransactionID:$transactionID");
          //addBuyRuletoSQL($transactionID, $ruleIDBTBuy);
          echo "<BR>Buy Order COMPLETE!";
          setCustomisedSellRule($ruleIDBTBuy,$coinID);
          if ($type == 'Buy' and $coinModeRule == 0){
              setCustomisedSellRuleBased($coinID, $ruleIDBTBuy, 40.00);
          }
          updateBuyAmount($transactionID,$orderQty);
          if($redirectPurchasesToSpread == 1){
            $type = 'SpreadBuy';
            updateBuyToSpread($spreadBetIDRedirect,$transactionID);
          }
          if ($type == 'SpreadBuy'){
            updateToSpreadSell($transactionID);
            newLogToSQL("BittrexBuy", "updateToSpreadSell($transactionID) $type;", $userID, $GLOBALS['logToSQLSetting'],"SpreadBuy","TransactionID:$transactionID");
            updateSpreadBetTotalProfitBuy($transactionID ,$finalPrice,$amount);
            newLogToSQL("BittrexBuy", "updateSpreadBetTotalProfitBuy($transactionID ,$finalPrice,$amount);", $userID, $GLOBALS['logToSQLSetting'],"SpreadBuy","TransactionID:$transactionID");
            updateSpreadBetSellTarget($transactionID);
            newLogToSQL("BittrexBuy", "updateSpreadBetTotalProfitBuy($transactionID ,$finalPrice,$amount);", $userID, $GLOBALS['logToSQLSetting'],"SpreadBuy","TransactionID:$transactionID");
          }
          newLogToSQL("BittrexBuy", "setCustomisedSellRule($ruleIDBTBuy,$coinID);", $userID, 1,"SpreadBuy","TransactionID:$transactionID");
          //if ($type == "SpreadBuy"){ updateSpreadSell();}
          pausePurchases($userID);
          addCoinPurchaseDelay($coinID,$userID,60);
          clearBittrexRef($transactionID);
          UpdateProfit();
          if ($oldBuyBackTransID <> 0){ delaySavingBuy($oldBuyBackTransID,80); }


          //continue;
          logAction("runBittrex; bittrexBuyComplete : $coin | $type | $baseCurrency | $userID | $liveCoinPriceBit | $coinID | $type | $finalPrice | $amount | $userID | $uuid | $orderQty | $transactionID", 'BuySellFlow', 1);

          $finalBool = True;
        }elseif ($orderIsOpen != 1 && $cancelInit != 1 && $orderQty <> $orderQtyRemaining){
          bittrexUpdateBuyQty($transactionID, $orderQty-$orderQtyRemaining);
          if ($sendEmail){
            $subject = "Coin Purchase1: ".$coin;
            $from = 'Coin Purchase <purchase@investment-tracker.net>';
            sendEmail($email, $coin, $amount, $cost, $orderNo, $totalScore, $subject,$userName,$from);
          }
          if($redirectPurchasesToSpread == 1){
            $type = 'SpreadBuy';
            updateBuyToSpread($spreadBetIDRedirect,$transactionID);
          }
          if ($type == 'SpreadBuy'){
            //SpreadBetBittrexCancelPartialBuy($transactionID,$orderQty-$orderQtyRemaining);
            updateToSpreadSell($transactionID);
            newLogToSQL("BittrexBuyCancel", "SpreadBetBittrexCancelPartialSell($transactionID,$coinID,$orderQty-$orderQtyRemaining);", $userID, $GLOBALS['logToSQLSetting'],"PartialOrder","TransactionID:$transactionID");
          }
          bittrexBuyComplete($uuid, $transactionID, $finalPrice); //add buy price - $finalPrice
          logAction("runBittrex; bittrexBuyCompletePartial : $coin | $type | $baseCurrency | $userID | $liveCoinPriceBit | $coinID | $type | $finalPrice | $amount | $userID | $uuid | $orderQty | $transactionID", 'BuySellFlow', 1);
        }
        //if ( substr($timeSinceAction,0,4) == $buyCancelTime){
        //if ( $buyOrderCancelTime < date("Y-m-d H:i:s", time()) && $buyOrderCancelTime != '0000-00-00 00:00:00'){
        if ( $minsSinceAction >= $timeToCancelMins){
          echo "<BR>CANCEL time exceeded! CANCELLING! $minsSinceAction | $timeToCancelMins ";
          if ($orderQty == $orderQtyRemaining){
             $cancelRslt = bittrexCancel($apiKey,$apiSecret,$uuid,$apiVersion);
             var_dump($cancelRslt);
             $canStatus = $cancelRslt['status']; $errorCode = $cancelRslt['code'];
             echo "<BR> Cancelling: bittrexCancel($apiKey,$apiSecret,$uuid,$apiVersion); $canStatus";
             if ($canStatus == 'CLOSED' OR $errorCode == "ORDER_NOT_OPEN"){
               bittrexBuyCancel($uuid, $transactionID);
               logAction("runBittrex; bittrexBuyCancelFull : $coin | $type | $baseCurrency | $userID | $liveCoinPriceBit | $coinID | $type | $finalPrice | $amount | $userID | $uuid | $orderQty | $transactionID", 'BuySellFlow', 1);
               newLogToSQL("BittrexBuyCancel", "Order time exceeded for OrderNo: $orderNo Cancel order completed", $userID, 1,"FullOrder","TransactionID:$transactionID");
               reopenCoinSwapCancel($oldBuyBackTransID);
               removeTransactionDelay($coinID, $userID);
             }else{
               logAction("bittrexCancelBuyOrder: ".$cancelRslt, 'Bittrex', $GLOBALS['logToFileSetting'] );
               newLogToSQL("BittrexBuyCancel", "Order time exceeded for OrderNo: $orderNo Cancel order Error: $cancelRslt", $userID, $GLOBALS['logToSQLSetting'],"FullOrder","TransactionID:$transactionID");
             }
          }else{
            $result = bittrexCancel($apiKey,$apiSecret,$uuid,$apiVersion);
            $canStatus = $result['status'];
            if ($canStatus == 'CLOSED'){
              bittrexUpdateBuyQty($transactionID, $orderQty-$orderQtyRemaining);
              newLogToSQL("BittrexBuyCancel", "Order time exceeded for OrderNo: $orderNo Order cancelled and new Order Created. QTY: $orderQty | QTY Remaining: $orderQtyRemaining", $userID, 1,"PartialOrder","TransactionID:$transactionID");
              logAction("runBittrex; bittrexBuyCancelPartial : $coin | $type | $baseCurrency | $userID | $liveCoinPriceBit | $coinID | $type | $finalPrice | $amount | $userID | $uuid | $orderQty | $transactionID", 'BuySellFlow', 1);
              if ($sendEmail){
                $subject = "Coin Purchase1: ".$coin;
                $from = 'Coin Purchase <purchase@investment-tracker.net>';
                sendEmail($email, $coin, $amount, $cost, $orderNo, $totalScore, $subject,$userName,$from);
              }
              if($redirectPurchasesToSpread == 1){
                $type = 'SpreadBuy';
                updateBuyToSpread($spreadBetIDRedirect,$transactionID);
              }

              if ($type == 'SpreadBuy'){
                //SpreadBetBittrexCancelPartialBuy($transactionID,$orderQty-$orderQtyRemaining);
                updateToSpreadSell($transactionID);
                newLogToSQL("BittrexBuyCancel", "SpreadBetBittrexCancelPartialSell($transactionID,$coinID,$orderQty-$orderQtyRemaining);", $userID, $GLOBALS['logToSQLSetting'],"PartialOrder","TransactionID:$transactionID");
              }
              bittrexBuyComplete($uuid, $transactionID, $finalPrice); //add buy price - $finalPrice
              //addBuyRuletoSQL($transactionID, $ruleIDBTBuy);
            }else{ logAction("bittrexCancelBuyOrder: ".$result, 'Bittrex', $GLOBALS['logToFileSetting'] );}
          }
          addUSDTBalance('USDT',$amount*$finalPrice,$finalPrice,$userID);
          if ($buyBack == 1){ reopenCoinSwapCancel($oldBuyBackTransID); }
          $finalBool = True;
        }
      }elseif ($type == "Sell" or $type == "SpreadSell" or $type == "SavingsSell"){ // $type Sell
        //logToSQL("Bittrex", "Sell Order | OrderNo: $orderNo Final Price: $finalPrice | $orderIsOpen | $cancelInit | $orderQtyRemaining", $userID, $GLOBALS['logToSQLSetting']);
        echo "<BR> SELL TEST: $orderIsOpen | $cancelInit | $orderQtyRemaining | $amount | $finalPrice | $uuid";
        newLogToSQL("BittrexSell", "$type | $orderIsOpen | $cancelInit | $orderQtyRemaining | $amount| $finalPrice | $uuid", $userID,0,"SellComplete","TransactionID:$transactionID");
        if (($orderIsOpen == 0) OR ($cancelInit == 0)){
          echo "<BR>SELL Order COMPLETE!";
            //$profitPct = ($finalPrice-$cost)/$cost*100;
            if ($originalAmount == 0){ $originalAmount = $amount;}
            $sellPrice = ($finalPrice*$amount);
            $buyPrice = $cost*$originalAmount;
            $fee = (($sellPrice)/100)*0.25;
            $profit = number_format((float)($sellPrice-$buyPrice)-$fee, 8, '.', '');
            $profitPct = ($profit/$buyPrice)*100;
            $realSellPrice = ($finalPrice*$originalAmount);
            $realProfitPct = (($realSellPrice-$buyPrice)/$buyPrice)*100;
            newLogToSQL("BittrexSell", "$finalPrice | $amount | $sellPrice | $cost | $originalAmount | $buyPrice | $fee | $profit | $profitPct | $realSellPrice | $realProfitPct", $userID, 1,"OriginalPrice","TransactionID:$transactionID");
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
              echo "<BR> Bittrex Sell Complete: $uuid | $transactionID | $finalPrice";
              bittrexSellComplete($uuid, $transactionID, $finalPrice); //add sell price - $finalPrice
              extendPctToBuy($coinID,$userID);
              $allocationType = 'Standard';
              if ($type == 'SpreadSell'){ $allocationType = 'SpreadBet';}elseif ($coinModeRule >0){$allocationType = 'CoinMode';}
              $pctToSave = $pctToSave / 100;
              addProfitToAllocation($userID, $profit,$allocationType, $pctToSave, $coinID);
              newLogToSQL("BittrexSell", "Sell Order Complete for OrderNo: $orderNo Final Price: $finalPrice", $userID, $GLOBALS['logToSQLSetting'],"SellComplete","TransactionID:$transactionID");
              if ((is_null($coinModeRule)) OR ($coinModeRule == 0) ){
                //Update Buy Rule
                $buyTrendPct = updateBuyTrendHistory($coinID,$orderDate);
                $Hr1Trnd = $buyTrendPct[0][0]; $Hr24Trnd = $buyTrendPct[0][1]; $d7Trnd = $buyTrendPct[0][2];
                newLogToSQL("BittrexSell", "updateBuyTrend($coinID, $transactionID, Rule, $ruleIDBTSell, $Hr1Trnd,$Hr24Trnd,$d7Trnd);", $userID, $GLOBALS['logToSQLSetting'],"updateBuyTrend","TransactionID:$transactionID");
                updateBuyTrend($coinID, $transactionID, 'Rule', $ruleIDBTSell, $Hr1Trnd,$Hr24Trnd,$d7Trnd);
                newLogToSQL("BittrexSell", "WriteBuyBack($transactionID,$realProfitPct,10, 60);", $userID, $GLOBALS['logToSQLSetting'],"BuyBack","TransactionID:$transactionID");
                WriteBuyBack($transactionID,$realProfitPct,10, 60);
              }else{
                //Update Coin ModeRule
                $buyTrendPct = updateBuyTrendHistory($coinID,$orderDate);
                $Hr1Trnd = $buyTrendPct[0][0]; $Hr24Trnd = $buyTrendPct[0][1]; $d7Trnd = $buyTrendPct[0][2];
                newLogToSQL("BittrexSell", "updateBuyTrend($coinID, $transactionID, CoinMode, $ruleIDBTBuy, $Hr1Trnd,$Hr24Trnd,$d7Trnd);", $userID, $GLOBALS['logToSQLSetting'],"updateBuyTrend","TransactionID:$transactionID");
                updateBuyTrend($coinID, $transactionID, 'CoinMode', $ruleIDBTBuy, $Hr1Trnd,$Hr24Trnd,$d7Trnd);
                newLogToSQL("BittrexSell", "WriteBuyBack($transactionID,$realProfitPct,10, 60);", $userID, $GLOBALS['logToSQLSetting'],"BuyBack","TransactionID:$transactionID");
                WriteBuyBack($transactionID,$realProfitPct,10, 60);
              }
              if ($allocationType == 'SpreadBet'){
                updateSpreadBetTotalProfitSell($transactionID,$finalPrice);
                subPctFromProfitSB($spreadBetTransactionID,0.01, $transactionID);
                //$openTransSB = getOpenSpreadCoins($userID,$spreadBetRuleID);
                //if (count($openTransSB) == 0){
                //  newSpreadTransactionID($UserID,$spreadBetRuleID);
                //}
              }
              newLogToSQL("BittrexSell","Test1: $saveResidualCoins | $realProfitPct | $originalAmount | $amount | $finalPrice | $cost | $buyPrice | $sellPrice | $realSellPrice",3,$GLOBALS['logToSQLSetting'],"SaveResidualCoins3","TransactionID:$transactionID");
              if ($saveResidualCoins == 1 and $realProfitPct >= 0.25 AND $originalAmount <> 0){
                $newOrderDate = date("YmdHis", time());
                $OrderString = "ORD".$coin.$newOrderDate.$ruleIDBTBuy;
                $residualAmount = $originalAmount - $amount;
                ResidualCoinsToSaving($newResidualAmount,$OrderString ,$transactionID);
                newLogToSQL("BittrexSell","ResidualCoinsToSaving($newResidualAmount, $originalAmount, $amount, $OrderString, $transactionID, $realProfitPct);",3,1,"SaveResidualCoins3","TransactionID:$transactionID");
              }
              UpdateProfit();

              logAction("runBittrex; bittrexSellComplete : $coin | $type | $baseCurrency | $userID | $liveCoinPriceBit | $coinID | $type | $finalPrice | $amount | $userID | $uuid | $orderQty | $originalAmount | $residualAmount | $transactionID", 'BuySellFlow', 1);
            //addSellRuletoSQL($transactionID, $ruleIDBTSell);
            $finalBool = True;
        }
        if ($daysOutstanding <= -28){
          echo "<BR>days from sale! $daysOutstanding CANCELLING!";
          if ($orderQtyRemaining == $orderQty){
            //complete sell update amount
            $cancelRslt = bittrexCancel($apiKey,$apiSecret,$uuid,$apiVersion);
            $canStatus = $cancelRslt['status'];
            if ($canStatus == 'CLOSED'){
              bittrexSellCancel($uuid, $transactionID);
              logAction("runBittrex; bittrexSellCancelFull : $coin | $daysOutstanding | $type | $baseCurrency | $userID | $liveCoinPriceBit | $coinID | $type | $finalPrice | $amount | $userID | $uuid | $orderQty | $originalAmount | $residualAmount | $transactionID", 'BuySellFlow', 1);
              newLogToSQL("BittrexSell", "Sell Order over 28 Days. Cancelling OrderNo: $orderNo", $userID, $GLOBALS['logToSQLSetting'],"CancelFull","TransactionID:$transactionID");
              $finalBool = True;
            }else{
              logAction("bittrexCancelSellOrder: ".$cancelRslt, 'Bittrex', $GLOBALS['logToFileSetting'] );
              newLogToSQL("BittrexSell", "Sell Order over 28 Days. Error cancelling OrderNo: $orderNo : $cancelRslt", $userID, $GLOBALS['logToSQLSetting'],"CancelFullError","TransactionID:$transactionID");
            }
          }else{
             $result = bittrexCancel($apiKey,$apiSecret,$uuid,$apiVersion);
             $canStatus = $result['status'];
             if ($apiVersion == 1){ $resultStatus = $result;}
             else{ if ($canStatus == 'CLOSED'){$resultStatus = 1;}else{$resultStatus =0;}}
             if ($resultStatus == 1){
               $newOrderNo = "ORD".$coin.date("YmdHis", time()).$ruleIDBTSell;
               //sendtoSteven($transactionID,$orderQtyRemaining."_".$qtySold."_".$orderQty, $newOrderNo."_".$orderNo, "SELL - Greater 28 days");
               bittrexCopyTransNewAmount($transactionID,$qtySold,$orderQtyRemaining,$newOrderNo);
               bittrexSellComplete($uuid, $transactionID, $finalPrice);
               logAction("runBittrex; bittrexSellCancelPartial : $coin | $daysOutstanding | $type | $baseCurrency | $userID | $liveCoinPriceBit | $coinID | $type | $finalPrice | $amount | $userID | $uuid | $orderQty | $originalAmount | $residualAmount | $transactionID", 'BuySellFlow', 1);
               newLogToSQL("BittrexSell", "Sell Order over 28 Days. Cancelling OrderNo: $orderNo | Creating new Transaction", $userID, $GLOBALS['logToSQLSetting'],"CancelPartial","TransactionID:$transactionID");
               //Update QTY
               //bittrexUpdateSellQty($transactionID,$qtySold);
               //bittrexSellCancel($uuid, $transactionID);

               if ($sendEmail){
                 $subject = "Coin Sale: ".$coin." RuleID:".$ruleIDBTSell." Qty: ".$orderQty." : ".$orderQtyRemaining;
                 $from = 'Coin Sale <sale@investment-tracker.net>';
                 sendSellEmail($email, $coin, $orderQty-$orderQtyRemaining, $finalPrice, $orderNo, $totalScore,$profitPct,$profit,$subject,$userName,$from);
               }
               $finalBool = True;
             }else{
               logAction("bittrexCancelSellOrder: ".$result, 'Bittrex', $GLOBALS['logToFileSetting'] );
               newLogToSQL("BittrexSell", "Sell Order over 28 Days. Error cancelling OrderNo: $orderNo : $result", $userID, $GLOBALS['logToSQLSetting'],"CancelPartialError","TransactionID:$transactionID");
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
            else{ if ($cancelRslt['status'] == 'CLOSED'){$canResStatus = 1;}else{$canResStatus =0;}}
            if ($canResStatus == 1){
              bittrexSellCancel($uuid, $transactionID);
              logAction("runBittrex; bittrexSellCancelFull_v2 : $coin | $pctFromSale | $type | $baseCurrency | $userID | $liveCoinPriceBit | $coinID | $type | $finalPrice | $amount | $userID | $uuid | $orderQty | $originalAmount | $residualAmount | $transactionID", 'BuySellFlow', 1);
              newLogToSQL("BittrexSell", "Sell Order 3% Less or 4% above. Cancelling OrderNo: $orderNo", $userID, $GLOBALS['logToSQLSetting'],"CancelFullPriceRise","TransactionID:$transactionID");
              $finalBool = True;
            }else{
              logAction("bittrexCancelSellOrder: ".$result, 'Bittrex', $GLOBALS['logToFileSetting'] );
              newLogToSQL("BittrexSell", "Sell Order 3% Less or 4% above. Error cancelling OrderNo: $orderNo : $result", $userID, $GLOBALS['logToSQLSetting'],"CancelFullPriceRiseError","TransactionID:$transactionID");
            }
          }else{
            $canResult = bittrexCancel($apiKey,$apiSecret,$uuid,$apiVersion);
            if($apiVersion == 1){ $newCanResStatus = $canResult;}
            else{ if ($canResult['status'] == 'CLOSED'){$newCanResStatus = 1;}else{$newCanResStatus =0;}}
            if ($newCanResStatus == 1){
              $newOrderNo = "ORD".$coin.date("YmdHis", time()).$ruleIDBTSell;
              //sendtoSteven($transactionID,"QTYRemaining: ".$orderQtyRemaining."_QTYSold: ".$qtySold."_OrderQTY: ".$orderQty."_UUID: ".$uuid, "NewOrderNo: ".$newOrderNo."_OrderNo: ".$orderNo, "SELL - Less -2 Greater 2.5");
              bittrexCopyTransNewAmount($transactionID,$qtySold,$orderQtyRemaining,$newOrderNo);
              bittrexSellComplete($uuid, $transactionID, $finalPrice);
              logAction("runBittrex; bittrexSellCancelPartial_v2 : $coin | $pctFromSale | $type | $baseCurrency | $userID | $liveCoinPriceBit | $coinID | $type | $finalPrice | $amount | $userID | $uuid | $orderQty | $originalAmount | $residualAmount | $transactionID", 'BuySellFlow', 1);
              newLogToSQL("BittrexSell", "Sell Order 3% Less or 4% above. Cancelling OrderNo: $orderNo | Creating new Transaction", $userID, $GLOBALS['logToSQLSetting'],"CancelPartialPriceRise","TransactionID:$transactionID");
              //Update QTY
              //bittrexUpdateSellQty($transactionID,$qtySold);
              //bittrexSellCancel($uuid, $transactionID);

              if ($sendEmail){
                $subject = "Coin Sale2: ".$coin." RuleID:".$ruleIDBTSell." Qty: ".$orderQty." : ".$orderQtyRemaining;
                $from = 'Coin Sale <sale@investment-tracker.net>';
                //$debug = "$uuid : $transactionID - $orderQtyRemaining + $qtySold / $pctFromSale ! $liveProfitPct";
                sendSellEmail($email, $coin, $orderQty-$orderQtyRemaining, $finalPrice, $orderNo, $totalScore,$profitPct,$profit,$subject,$userName,$from);
              }
              $finalBool = True;
            }else{
              logAction("bittrexCancelSellOrder: ".$result, 'Bittrex', $GLOBALS['logToFileSetting'] );
              newLogToSQL("BittrexSell", "Sell Order 3% Less or 4% above. Error cancelling OrderNo: $orderNo : $result", $userID, $GLOBALS['logToSQLSetting'],"CancelPartialPriceRiseError","TransactionID:$transactionID");
            }
          }
          subUSDTBalance('USDT',$amount*$finalPrice,$finalPrice,$userID);
        }
      } //end $type Buy Sell
    //}else{
    //  echo "<BR> NOT SET!!!";
  //    logAction("bittrexCheckOrder: ".$status, 'Bittrex', $GLOBALS['logToFileSetting'] );
  //    newLogToSQL("Bittrex", "Check OrderNo: $orderNo Success:".$status, $userID, $GLOBALS['logToSQLSetting'],"Error","TransactionID:$transactionID");
  //  }//end bittrex order check
    echo "<br> Profit Pct $liveProfitPct Live Coin Price: $liveCoinPriceBit cost $cost";
    echo "<br>Time Since Action ".substr($timeSinceAction,0,4);

    echo "<BR> ORDERQTY: $orderQty - OrderQTYREMAINING: $orderQtyRemaining";
  }//Bittrex Loop
  return $finalBool;
}

function runCoinAlerts($coinAlerts,$marketAlerts,$spreadBetAlerts){
  $finalBool = False;
  $coinAlertsLength = count($coinAlerts);
  for($d = 0; $d < $coinAlertsLength; $d++) {
    $id = $coinAlerts[$d][0];
    $coinID = $coinAlerts[$d][1]; $action = $coinAlerts[$d][2]; $price  = $coinAlerts[$d][3]; $symbol  = $coinAlerts[$d][4];
    $userName  = $coinAlerts[$d][5]; $email  = $coinAlerts[$d][6]; $liveCoinPrice = $coinAlerts[$d][7]; $category = $coinAlerts[$d][8];
    $Live1HrChangeAlrt = $coinAlerts[$d][9]; $Live24HrChangeAlrt = $coinAlerts[$d][10]; $Live7DChangeAlrt = $coinAlerts[$d][11];
    $reocurring = $coinAlerts[$d][12]; $dateTimeSent = $coinAlerts[$d][13]; $liveSellOrderAlert = $coinAlerts[$d][14];
    $liveBuyOrderAlert = $coinAlerts[$d][15];$liveMarketCapAlert = $coinAlerts[$d][16];
    $userID = $coinAlerts[$d][17]; $livePricePct = $coinAlerts[$d][19];
    $minutes = $coinAlerts[$d][18];
    $returnFlag = False; $tempPrice = 0;
    Echo "<BR> Checking $symbol, $price, $action, $userName , $liveCoinPrice, $category, $dateTimeSent, $minutes, $reocurring, $Live1HrChangeAlrt";

    if ($category == "Price"){
      $tempPrice = $liveCoinPrice;
    }elseif ($category == "Pct Price in 1 Hour"){
      $tempPrice = $Live1HrChangeAlrt;
    }elseif ($category == "Market Cap Pct Change"){
      $tempPrice = $liveMarketCapAlert;
    }elseif ($category == "Live Price Pct Change"){
      $tempPrice = $livePricePct;
    }elseif ($category == "Pct Price in 24 Hours"){
      $tempPrice = $Live24HrChangeAlrt;
    }elseif ($category == "Pct Price in 7 Days"){
      $tempPrice = $Live7DChangeAlrt;
    }
    $returnFlag = returnAlert($price,$tempPrice,$action);
    if ($returnFlag){
      echo "<BR> $category Alert True. Sending Alert for $symbol $price $action $tempPrice";
      action_Alert($minutes,$email,$symbol,$price,$action,$userName,$category,$reocurring,$id,$userID, $GLOBALS['logToFileSetting'], $GLOBALS['logToSQLSetting'],$tempPrice);
      $finalBool = True;
    }
  }
  $marketAlertsSize = count($marketAlerts);
  echo "<BR> MARKETS ALERT ARRAY SIZE: $marketAlertsSize";
  $marketStats = getMarketstats();
  for ($q=0; $q<$marketAlertsSize; $q++){
    $userName  = $marketAlerts[$q][1];$email = $marketAlerts[$q][2];$userID = $marketAlerts[$q][0];
    $dateTimeSent = $marketAlerts[$q][8];
    $Live1HrChangeAlrt = $marketStats[0][1];$Live24HrChangeAlrt = $marketStats[0][2];$Live7DChangeAlrt = $marketStats[0][3]; $liveCoinPrice = $marketStats[0][0];$liveMarketCapAlert = $marketStats[0][5];
    $category = $marketAlerts[$q][5];$price = $marketAlerts[$q][9];$action = $marketAlerts[$q][6];$reocurring = $marketAlerts[$q][4];
    $minutes = $marketAlerts[$q][7]; $id = $marketAlerts[$q][8];
    $liveHr1Price = $marketStats[0][6];$liveHr24Price = $marketStats[0][7];$liveD7Price = $marketStats[0][8];
    Echo "<BR> Checking Market Alerts $price, $action, $userName , $liveCoinPrice, $category, $dateTimeSent, $minutes, $reocurring, $Live1HrChangeAlrt";
    if ($category == "Price"){
      //Price
      $returnFlag = returnAlert($price,$liveCoinPrice,$action);
      if ($returnFlag){
        echo "<BR> $category Alert True. Sending Alert for $price $action";
        action_Market_Alert($minutes,$email,$price,$action,$userName,$category,$reocurring,$id,$userID, $GLOBALS['logToFileSetting'], $GLOBALS['logToSQLSetting'], $liveCoinPrice);
        $finalBool = True;
      }
    }elseif ($category == "Pct Price in 1 Hour"){
      //1Hr
      $Live24HrChangeAlrt = (($liveCoinPrice - $liveHr1Price)/$liveHr1Price)*100;
      $returnFlag = returnAlert($price,$Live1HrChangeAlrt,$action);
      if ($returnFlag){
        echo "<BR> $category Alert True. Sending Alert for $price $action";
        action_Market_Alert($minutes,$email,$price,$action,$userName,$category,$reocurring,$id,$userID, $GLOBALS['logToFileSetting'], $GLOBALS['logToSQLSetting'], $Live1HrChangeAlrt);
        $finalBool = True;
      }
    }elseif ($category == "Market Cap Pct Change"){
      //MarketCap
      $returnFlag = returnAlert($price,$liveMarketCapAlert,$action);
      if ($returnFlag){
        echo "<BR> $category Alert True. Sending Alert for $price $action";
        action_Market_Alert($minutes,$email,$price,$action,$userName,$category,$reocurring,$id,$userID, $GLOBALS['logToFileSetting'], $GLOBALS['logToSQLSetting'], $liveMarketCapAlert);
        $finalBool = True;
      }
    }
  }

  $spreadBetAlertsSize = count($spreadBetAlerts);
  echo "<BR> SPREADBET ALERT ARRAY SIZE: $spreadBetAlertsSize";
  for ($g=0; $g<$spreadBetAlertsSize; $g++){
    $userName  = $spreadBetAlerts[$g][6];$email = $spreadBetAlerts[$g][7];$userID = $spreadBetAlerts[$g][5];
    $dateTimeSent = $spreadBetAlerts[$g][8];
    $Live1HrChangeAlrt = $spreadBetAlerts[$g][1];$Live24HrChangeAlrt = $spreadBetAlerts[$g][2];$Live7DChangeAlrt = $spreadBetAlerts[$g][3]; $liveCoinPrice = $spreadBetAlerts[$g][0];$liveMarketCapAlert = $spreadBetAlerts[$g][4];
    $category = $spreadBetAlerts[$g][10];$price = $spreadBetAlerts[$g][14];$action = $spreadBetAlerts[$g][11];$reocurring = $spreadBetAlerts[$g][9];
    $minutes = $spreadBetAlerts[$g][12]; $id = $spreadBetAlerts[$g][13];
    Echo "<BR> Checking SpreadBet Alerts $price, $action, $userName , $liveCoinPrice, $category, $dateTimeSent, $minutes, $reocurring, $Live1HrChangeAlrt";
    if ($category == "Price"){
      //Price
      $returnFlag = returnAlert($price,$liveCoinPrice,$action);
      if ($returnFlag){
        echo "<BR> $category Alert True. Sending Alert for $price $action";
        action_SpreadBet_Alert($minutes,$email,$price,$action,$userName,$category,$reocurring,$id,$userID, $GLOBALS['logToFileSetting'], $GLOBALS['logToSQLSetting'], $liveCoinPrice);
        $finalBool = True;
      }
    }elseif ($category == "Pct Price in 1 Hour"){
      //1Hr
      $returnFlag = returnAlert($price,$Live1HrChangeAlrt,$action);
      if ($returnFlag){
        echo "<BR> $category Alert True. Sending Alert for $price $action";
        action_SpreadBet_Alert($minutes,$email,$price,$action,$userName,$category,$reocurring,$id,$userID, $GLOBALS['logToFileSetting'], $GLOBALS['logToSQLSetting'], $Live1HrChangeAlrt);
        $finalBool = True;
      }
    }elseif ($category == "Market Cap Pct Change"){
      //MarketCap
      $returnFlag = returnAlert($price,$liveMarketCapAlert,$action);
      if ($returnFlag){
        echo "<BR> $category Alert True. Sending Alert for $price $action";
        action_SpreadBet_Alert($minutes,$email,$price,$action,$userName,$category,$reocurring,$id,$userID, $GLOBALS['logToFileSetting'], $GLOBALS['logToSQLSetting'], $liveMarketCapAlert);
        $finalBool = True;
      }
    }
  }
  return $finalBool;
}

function buyToreduceLoss($lossCoins){
  $finalBool = False;
  $lossCoinsSize = count($lossCoins);
  //$apiVersion = 3; $ruleID = 111111;
  for ($y=0; $y<$lossCoinsSize; $y++){
    $pctProfit = $lossCoins[$y][58];
    $transactionID = $lossCoins[$y][0];
    $minsToDelay = $lossCoins[$y][60];
    $userID = $lossCoins[$y][3];
    $coinID = $lossCoins[$y][2];
    $liveCoinPrice = $lossCoins[$y][19];
    $baseCurrency = $lossCoins[$y][36];
    $totalAmount = $lossCoins[$y][54];
    $reduceLossEnabled = $lossCoins[$y][61];
    $reduceLossSellPct = $lossCoins[$y][62];
    $reduceLossMultiplier = $lossCoins[$y][63];
    echo "<BR> buyToreduceLoss: $pctProfit | $minsToDelay | $transactionID | $userID | $coinID | $liveCoinPrice | $baseCurrency | $totalAmount |$reduceLossEnabled | $reduceLossSellPct";

    if ($pctProfit <= $reduceLossSellPct and $minsToDelay > 0 AND $reduceLossEnabled == 1){
      if (!isset($pctProfit)){ echo "<BR> PctProfit note set: EXIT! "; continue; }
      echo "<BR> buyToreduceLoss2: $pctProfit |$reduceLossSellPct | $minsToDelay | $reduceLossEnabled";
      //get multiplier
      //$openTransNoAry = getOpenTransNo($userID, $coinID);
      $currentBuy = $reduceLossMultiplier;
      $profitMultiplier = ABS($reduceLossSellPct)/ABS($pctProfit);
      $quant = $totalAmount*($currentBuy*$profitMultiplier);
      echo "<BR> buyToreduceLoss2: 2 | $currentBuy | $quant | $profitMultiplier | $totalAmount";
      //Buy Coin with Merge
      addTrackingCoin($coinID, $liveCoinPrice, $userID, $baseCurrency, 1, 1, $quant, 97, 0, 0, 1, 240, 229,1,1,10,'Buy',$liveCoinPrice,0,0,1,'buyToreduceLoss',$transactionID);
      //Set Merge for current Coin
      updateTrackingCoinToMerge($transactionID, $currentBuy);
      //Set Delay
      delaySavingBuy($transactionID,4320);
      setNewTargetPrice($transactionID);
      $finalBool =  True;
    }
  }
  return $finalBool;
}

//set time
setTimeZone();
$i=0;
$date = date("Y-m-d H:i", time());
$current_date = date('Y-m-d H:i');
$priceDipTimer = date('Y-m-d H:i');
$spreadBetTimer = date('Y-m-d H:i');
$trackingCoinTimer = date('Y-m-d H:i');
$trackingSellCoinTimer = date('Y-m-d H:i');
$sellSpreadBetTimer = date('Y-m-d H:i');
$buyCoinTimer = date('Y-m-d H:i');
$sellCoinTimer = date('Y-m-d H:i');
$sharedVariablesTimer = date('Y-m-d H:i');
$alertRunTimer = date('Y-m-d H:i');
$bittrexReqsTimer = date('Y-m-d H:i');
$completeFlag = False;
$reRunBuySavingsFlag = False;
$runTrackingSellCoinFlag = False;
$runNewTrackingCoinFlag = False;
$runSpreadBetSellAndBuybackFlag = False;
$runSellSavingsFlag = False;
$runBuyBackFlag = False;
$runSellSpreadBet = False;
$runSpreadBetFlag = False;
$runSellCoinsFlag == False;
$runBuyCoinsFlag == False;
$buyToReduceLossFlag == False;
$apiVersion = 3;
$trackCounter = [];
$clearCoinQueue = [];
$buyCounter = [];
$openTransactionFlag = True;
$refreshBittrexFlag = True;
$refreshAlertsFlag = True;
$newTime = date("Y-m-d H:i",strtotime($tmpTime, strtotime($current_date)));
logAction("Buy Sell Coins Start : End set to $newTime : $date", 'BuySellTiming', $GLOBALS['logToFileSetting'] );

while($completeFlag == False){
  if (date("Y-m-d H:i", time()) >= $sharedVariablesTimer){
    $SVcurrent_date = date('Y-m-d H:i');
    $sharedVariablesTimer = date("Y-m-d H:i",strtotime("+2 minutes 50 seconds", strtotime($SVcurrent_date)));
    $coinPriceMatch = getCoinPriceMatchList();
    $coinPricePatternList = getCoinPricePattenList();
    $coin1HrPatternList = getCoin1HrPattenList();
    $autoBuyPrice = getAutoBuyPrices();
  }
  echo "<blockquote><BR> CHECK Re-Buy Savings!! $i";
          if (($i == 0) or ($reRunBuySavingsFlag == True)){
            $reBuySavingsFixed = getOpenCoinSwaps();
            $reRunBuySavingsFlag = False;
          }
          $reRunBuySavingsFlag = runReBuySavings($reBuySavingsFixed);
  echo "</blockquote><BR> CHECK Sell Savings!! $i<blockquote>";
          if ($i == 0or $runSellSavingsFlag == True){
            $spreadBuyBack = getSavingsData();
            $runSellSavingsFlag = False;
          }
          $runSellSavingsFlag = runSellSavings($spreadBuyBack);
  echo "</blockquote><BR>CHECK PriceDip Rule Enable!! $i<blockquote>";
        if (date("Y-m-d H:i", time()) >= $priceDipTimer){
          $PDcurrent_date = date('Y-m-d H:i');
          $priceDipTimer = date("Y-m-d H:i",strtotime("+2 minutes 40 seconds", strtotime($PDcurrent_date)));
          $priceDipRules = getPriceDipRules();
        }
        runPriceDipRule($priceDipRules);
  echo "</blockquote><BR> CHECK BuyBack!! $i<blockquote>";
        if ($i == 0 or $runBuyBackFlag == True){
          $buyBackCoins = getBuyBackData();
          $runBuyBackFlag = False;
        }
        $runBuyBackFlag = runBuyBack($buyBackCoins);
  echo "</blockquote><BR> CHECK Spreadbet Sell & BuyBack!! $i<blockquote>";
        if ($i == 0 OR $runSpreadBetSellAndBuybackFlag == True ){
          $spreadBuyBack = getSpreadCoinSellDataFixed();
          $runSpreadBetSellAndBuybackFlag = False;
        }
        $runSpreadBetSellAndBuybackFlag = runSpreadBetSellAndBuyback($spreadBuyBack);
  echo "</blockquote><BR>CHECK Sell Spread Bet!! $i<blockquote>";
        if (date("Y-m-d H:i", time()) >= $sellSpreadBetTimer or $runSellSpreadBet == True){
          $sSBcurrent_date = date('Y-m-d H:i');
          $sellSpreadBetTimer = date("Y-m-d H:i",strtotime("+2 minutes 28 seconds", strtotime($sSBcurrent_date)));
          $sellSpread = getSpreadBetSellData();
          $runSellSpreadBet = False;
        }
        $runSellSpreadBet = runSellSpreadBet($sellSpread);
  echo "</blockquote><BR>CHECK Spread Bet!! $i<blockquote>";
        if ($i == 0 or $runSpreadBetFlag == True){
          $SpreadBetUserSettings = getSpreadBetUserSettings();
        }
        if (date("Y-m-d H:i", time()) >= $spreadBetTimer or $runSpreadBetFlag == True){
          $SBcurrent_date = date('Y-m-d H:i');
          $spreadBetTimer = date("Y-m-d H:i",strtotime("+3 minutes 18 seconds", strtotime($SBcurrent_date)));
          $spread = getSpreadBetData();
          $runSpreadBetFlag = False;
        }
        $runSpreadBetFlag = runSpreadBet($spread,$SpreadBetUserSettings);

  echo "</blockquote><BR> Tracking COINS!! $i<blockquote>";
        if (date("Y-m-d H:i", time()) >= $trackingCoinTimer OR $runNewTrackingCoinFlag == True){
          $TCcurrent_date = date('Y-m-d H:i');
          $trackingCoinTimer = date("Y-m-d H:i",strtotime("+2 minutes 8 seconds", strtotime($TCcurrent_date)));
          $newTrackingCoins = getNewTrackingCoins();
          $marketStats = getMarketstats();
          $baseMultiplier = getBasePrices();
          $ruleProfit = getRuleProfit();
          $coinPurchaseSettings = getCoinPurchaseSettings();
          $openTransactions = getOpenTransactions();
          $delayCoinPurchase = getDelayCoinPurchaseTimes();
          $runNewTrackingCoinFlag = False;
        }
        $runNewTrackingCoinFlag = runNewTrackingCoins($newTrackingCoins,$marketStats,$baseMultiplier,$ruleProfit,$coinPurchaseSettings,$clearCoinQueue,$openTransactions,$delayCoinPurchase);
  echo "</blockquote><BR> Tracking SELL COINS!! $i<blockquote>";
        if ((date("Y-m-d H:i", time()) >= $trackingSellCoinTimer) Or ($runTrackingSellCoinFlag == True)) {
          $TSCcurrent_date = date('Y-m-d H:i');
          $trackingSellCoinTimer = date("Y-m-d H:i",strtotime("+2 minutes 15 seconds", strtotime($TSCcurrent_date)));
          $newTrackingSellCoins = getNewTrackingSellCoins();
          $marketStats = getMarketstats();
          $runTrackingSellCoinFlag = False;
        }
        $runTrackingSellCoinFlag = runTrackingSellCoin($newTrackingSellCoins,$marketStats);
  echo "</blockquote><BR> BUY COINS!! $i<blockquote>";
        if ($i == 0 OR $runBuyCoinsFlag == True){$buyRules = getUserRules();}
        if (date("Y-m-d H:i", time()) >= $buyCoinTimer or $runBuyCoinsFlag == True){
          $BCcurrent_date = date('Y-m-d H:i');
          $buyCoinTimer = date("Y-m-d H:i",strtotime("+2 minutes 30 seconds", strtotime($BCcurrent_date)));
          $userProfit = getTotalProfit();
          $marketProfit = getMarketProfit();
          $ruleProfit = getRuleProfit();
          $totalBTCSpent = getTotalBTC();
          $dailyBTCSpent = getDailyBTC();
          $baseMultiplier = getBasePrices();
          $delayCoinPurchase = getDelayCoinPurchaseTimes();
          $coins = getTrackingCoins("WHERE `DoNotBuy` = 0 and `BuyCoin` = 1 ORDER BY `Symbol` ASC");
          $runBuyCoinsFlag = False;
        }
        $runBuyCoinsFlag = runBuyCoins($coins,$userProfit,$marketProfit,$ruleProfit,$totalBTCSpent,$dailyBTCSpent,$baseMultiplier,$delayCoinPurchase,$buyRules,$coinPriceMatch,$coinPricePatternList,$coin1HrPatternList,$autoBuyPrice,$trackCounter,$buyCounter);
  echo "</blockquote><BR> SELL COINS!! $i<blockquote>";
        if ($i == 0 OR $runSellCoinsFlag == True){$sellRules = getUserSellRules();}
        if (date("Y-m-d H:i", time()) >= $sellCoinTimer or $runSellCoinsFlag == True){
          $SCcurrent_date = date('Y-m-d H:i');
          $sellCoinTimer = date("Y-m-d H:i",strtotime("+2 minutes 25 seconds", strtotime($SCcurrent_date)));
          $sellCoins = getTrackingSellCoins("Sell");
          $userProfit = getTotalProfit();
          $runSellCoinsFlag = False;
          $buyToReduceLossFlag = True;
        }
        $runSellCoinsFlag = runSellCoins($sellRules,$sellCoins,$userProfit,$coinPriceMatch,$coinPricePatternList,$coin1HrPatternList,$autoBuyPrice);
  echo "</blockquote><BR> CHECK BITTREX!! $i<blockquote>";
        if (date("Y-m-d H:i", time()) >= $bittrexReqsTimer or$refreshBittrexFlag == True){
          $BRcurrent_date = date('Y-m-d H:i');
          $bittrexReqsTimer = date("Y-m-d H:i",strtotime("+2 minutes 34 seconds", strtotime($BRcurrent_date)));
          $BittrexReqs = getBittrexRequests();
          $refreshBittrexFlag = False;
        }
        $refreshBittrexFlag = runBittrex($BittrexReqs,$apiVersion);
  echo "</blockquote><BR> CHECK Alerts!! $i<blockquote>";
        if ($refreshAlertsFlag == True){
          $coinAlerts = getCoinAlerts();
          $marketAlerts = getMarketAlertsTotal();
          $spreadBetAlerts = getSpreadBetAlertsTotal();
          $refreshAlertsFlag = False;
        }
        if (date("Y-m-d H:i", time()) >= $alertRunTimer){
          $ALcurrent_date = date('Y-m-d H:i');
          $alertRunTimer = date("Y-m-d H:i",strtotime("+3 minutes 10 seconds", strtotime($ALcurrent_date)));
          $refreshAlertsFlag = runCoinAlerts($coinAlerts,$marketAlerts,$spreadBetAlerts);
        }
  echo "</blockquote><BR> CHECK BUY TO REDUCE LOSS!! $i<blockquote>";
        if ($buyToReduceLossFlag == True){
          $lossCoins = getTrackingSellCoinsAll();
          $buyToReduceLossFlag = False;
          $runSellCoinsFlag = True;
        }
        $buyToReduceLossFlag = buyToreduceLoss($lossCoins);


  sleep(15);
  $i = $i+1;
  $date = date("Y-m-d H:i:s", time());
  if (date("Y-m-d H:i", time()) >= $newTime){ $completeFlag = True;}
}//end While
logAction("Buy Sell Coins End $date : $i", 'BuySellTiming', $GLOBALS['logToFileSetting'] );
//$to, $symbol, $amount, $cost, $orderNo, $score, $subject, $user, $from){
//sendEmail('stevenj1979@gmail.com',$i,0,$date,0,'BuySell Loop Finished', 'stevenj1979', 'Coin Purchase <purchase@investment-tracker.net>');
echo "<br>EndTime ".date("Y-m-d H:i:s", time());
?>
</html>
