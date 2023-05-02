<html>
<?php
ini_set('max_execution_time',1200);
require('includes/newConfig.php');
require('Functions/BuySellFunc.php');

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
    $status = $coinSwaps[$y][1]; $reBuySavingsEnabled = $coinSwaps[$y][22];
    if (!isset($status)) { continue; }
    if ($reBuySavingsEnabled == 0){ continue; }
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
    if (!isset($spreadBuyBack[$u][65])){ continue; }
    $purchasePrice = $spreadBuyBack[$u][4];
    $amount = $spreadBuyBack[$u][5];$coinID = $spreadBuyBack[$u][2];$userID = $spreadBuyBack[$u][3];
    $tempPrice = getCoinPrice($coinID);
    $LiveCoinPrice = $tempPrice[0][0];$symbol = $spreadBuyBack[$u][11];$transactionID = $spreadBuyBack[$u][0];$fallsInPrice = $spreadBuyBack[$u][56];
    $profitSellTarget = $spreadBuyBack[$u][58];$autoBuyBackSell = $spreadBuyBack[$u][59];$bounceTopPrice = $spreadBuyBack[$u][60];$bounceLowPrice = $spreadBuyBack[$u][61];
    $bounceDifference = $spreadBuyBack[$u][62];$noOfBounceSells = $spreadBuyBack[$u][64];$baseCurrency = $spreadBuyBack[$u][36];
    $minsToDelay = $spreadBuyBack[$u][63]; $BTCPrice = $spreadBuyBack[$u][65]; $sellSavingsEnabled = $spreadBuyBack[$u][67];
    $ETHPrice = $spreadBuyBack[$u][66]; $hr1PctChange = $spreadBuyBack[$u][29];
    //echo "<BR> LiveCoinPrice:$LiveCoinPrice | Amount:$amount";
    $sellPrice = ($LiveCoinPrice * $amount);
    //echo "<BR> PurchasePrice:$purchasePrice | Amount:$amount";
    $buyPrice = ($purchasePrice * $amount);
    //echo "<BR> SellPrice:$sellPrice | BuyPrice:$buyPrice";
    $profit = ($sellPrice-$buyPrice);
    //echo "<BR> Profit:$profit | BuyPrice:$buyPrice";
    echo "<BR>";
    //var_dump($spreadBuyBack);
    $profitPCT = ($profit/$buyPrice)*100;

    if (!isset($profitPCT)){ continue; }
    if ($minsToDelay < 0) { continue; }
    if ($sellSavingsEnabled ==0) { continue; }
    if ($baseCurrency == 'USDT'){ $baseMin = 20;}elseif ($baseCurrency == 'BTC'){ $baseMin = 0.00048;}elseif ($baseCurrency == 'ETH'){ $baseMin = 0.0081;}
    if ($profitPCT > 30 OR $profitPCT < -20 OR $hr1PctChange > 13){
      echo "<br> runSellSavings:  $coinID | $baseCurrency | PP:$buyPrice | LP:$sellPrice | Prft:$profit | pct:$profitPCT | mins:$minsToDelay | bounceSell: $noOfBounceSells | bounceDiff: $bounceDifference | 1HrPct: $hr1PctChange";
    }
    echo "<BR> SellSavings Check: $symbol | $coinID | $profitPCT | $profitTarget | $hr1PctChange | $minsToDelay | $noOfBounceSells";
    $profitFlag = False;$hr1Flag = False; $buyMoreFlag = False;
    if ($profitPCT >= $profitTarget){ $profitFlag = True;}
    if ($hr1PctChange >= 7 and $minsToDelay > 0){ $hr1Flag = True; }
    if (($profitPCT <= -50 and $minsToDelay > 0 and $noOfBounceSells <= 1)){ $buyMoreFlag = True; }
    if (($profitFlag == True) OR ($hr1Flag == True)){
      newLogToSQL("runSellSavings_v1","$symbol | $baseCurrency | $sellPrice | $baseMin | $profitPCT | $profitTarget | $hr1PctChange",3,1,"Profit","TransID:$transactionID");
      newTrackingSellCoins($LiveCoinPrice,$userID, $transactionID,1, 1,0,0,10,'SavingsSell','RunSellSavings_1',0);
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
  //echo "<BR>";
  //var_dump($priceDipRules);
  for ($a=0; $a<$priceDipRulesSize;$a++){
    $buyRuleID = $priceDipRules[$a][0]; $enableRuleActivationAfterDip = $priceDipRules[$a][1]; $hr24PriceDipPct = $priceDipRules[$a][2];
    $hr24ChangePctChange = $priceDipRules[$a][3]; $d7ChangePctChange = $priceDipRules[$a][4]; $d7PriceDipPct = $priceDipRules[$a][5];
    $priceDipEnabled = $priceDipRules[$a][7]; $hoursFlat = $priceDipRules[$a][8]; $dipStartTime = $priceDipRules[$a][9];
    $priceDipDisable24Hour = $priceDipRules[$a][12]; $priceDipDisable7Day = $priceDipRules[$a][13]; $hoursFlatSetting = $priceDipRules[$a][10];
    $buyCoin = $priceDipRules[$a][14]; $minHr24ChangePctChange = $priceDipRules[$a][24]; $minD7ChangePctChange = $priceDipRules[$a][25];
    $pctOfAuto = $priceDipRules[$a][26]; $liveCoinPriceMkt = $priceDipRules[$a][28]; $marketHoursFlat = $priceDipRules[$a][29]; $marketHoursFlatTarget = $priceDipRules[$a][30];
    if(!isset($hr24ChangePctChange)){ continue;}
    if(!isset($d7ChangePctChange)){ continue;}

    if($enableRuleActivationAfterDip == 2){

      $pctChangeTargetAvg = ((($minHr24ChangePctChange + $minD7ChangePctChange)/2)/100)*($pctOfAuto/4);
      $enableRuleActivationAfterDip = 1;
      Echo "<BR> Auto Rule Activation set: $pctChangeTargetAvg = ((($minHr24ChangePctChange + $minD7ChangePctChange)/2)/100)*($pctOfAuto/4) )";
    }

    $PctChangeAvg = $priceDipRules[$a][16];
    $pctChangeTargetAvg = $priceDipRules[$a][15];
    $pctChangeDisableTargetAvg = $priceDipRules[$a][27];
    echo "<BR> $hr24ChangePctChange | $hr24PriceDipPct | $d7ChangePctChange | $d7PriceDipPct | $PctChangeAvg | AvgEnable: $pctChangeTargetAvg | AvgDisable: $pctChangeDisableTargetAvg";
      if($PctChangeAvg <= $pctChangeTargetAvg ){
        echo "<BR> enableBuyRule($buyRuleID); Avg:$PctChangeAvg <= AvgTarget:$pctChangeTargetAvg";
        //enableBuyRule($buyRuleID, 1);
        if ($buyCoin <> 1){

          setPriceDipEnable($buyRuleID, 1,$buyCoin,$liveCoinPriceMkt);
          if ($buyCoin <> 1 AND $priceDipEnabled == 1 AND $marketHoursFlat >= $marketHoursFlatTarget){
            enableBuyRule($buyRuleID, 1,$PctChangeAvg,$pctChangeTargetAvg);
          }
          newLogToSQL("runPriceDipRule","<BR> enableBuyRule($buyRuleID); Avg:$PctChangeAvg <= AvgTarget:$pctChangeTargetAvg",3,1,"enableBuyRule1","ruleID:$buyRuleID");
        }
      }
    if ($PctChangeAvg >= $pctChangeDisableTargetAvg){
      if ($buyCoin <> 0){
        enableBuyRule($buyRuleID, 0,$PctChangeAvg,$pctChangeTargetAvg);
        setPriceDipEnable($buyRuleID, 0,$buyCoin,$liveCoinPriceMkt);
        newLogToSQL("runPriceDipRule","<BR> enableBuyRule($buyRuleID); Avg:$PctChangeAvg <= AvgTarget:$pctChangeDisableTargetAvg",3,1,"enableBuyRule0","ruleID:$buyRuleID");
      }
    }

    if ($hoursFlat >= $hoursFlatSetting and $priceDipEnabled == 1){
      echo "<BR> $hoursFlat | $hoursFlatSetting";
      //enableBuyRule($buyRuleID, 1);
    }
  }
}

function runBuyBack($buyBackCoins,$webSettingsAry){
  $nFile = "BuySellCoins"; $nFunc = "BuyBack";
  $tempSettings = getSetting($webSettingsAry,$nFile,$nFunc);
  $logFlowSettingAry = $tempSettings[0]; $logVariSettingAry = $tempSettings[1]; $logSQLSettingAry = $tempSettings[2]; $logExitSettingAry = $tempSettings[3]; $logAPISettingAry = $tempSettings[4]; $logEventsSettingAry = $tempSettings[5];
  //echo "<BR> CHECK SETTINGS: $nFile | $nFunc | $logFlowSettingAry | $logVariSettingAry | $logSQLSettingAry | $logExitSettingAry | $logAPISettingAry";

  $finalBool = False;
  $buyBackCoinsSize = count($buyBackCoins);
  for ($t=0; $t<$buyBackCoinsSize;$t++){
    $bBID = $buyBackCoins[$t][0];    $userID = $buyBackCoins[$t][12];    $TransactionID = $buyBackCoins[$t][1];    $coinID = $buyBackCoins[$t][7];    $spreadBetTransactionID = $buyBackCoins[$t][5];
    $spreadBetRuleID = $buyBackCoins[$t][6];
    $quantity = $buyBackCoins[$t][2];$sellPrice = $buyBackCoins[$t][3];$status = $buyBackCoins[$t][4];
    $sellPriceBA = $buyBackCoins[$t][8];$priceDifferece = $buyBackCoins[$t][10];//$priceDifferecePct = $buyBackCoins[$t][11];
    $email = $buyBackCoins[$t][13];$userName = $buyBackCoins[$t][14];$apiKey = $buyBackCoins[$t][15];$apiSecret = $buyBackCoins[$t][16];$KEK = $buyBackCoins[$t][17];
    $originalSaleProfit = $buyBackCoins[$t][18];
    $originalSaleProfitPct = $buyBackCoins[$t][19]; $profitMultiply = $buyBackCoins[$t][20]; $buyBackPct = $buyBackCoins[$t][22]; //$noOfRaisesInPrice = $buyBackCoins[$t][21];
    $noOfRaisesInPrice = $buyBackCoins[$t][45];
    $minsToCancel = $buyBackCoins[$t][23]; $bullBearStatus = $buyBackCoins[$t][24];$type = $buyBackCoins[$t][25]; $overrideCoinAlloc = $buyBackCoins[$t][26];
    $allBuyBackAsOverride = $buyBackCoins[$t][27]; $BTCPrice = $buyBackCoins[$t][28];$ETHPrice = $buyBackCoins[$t][29];$liveCoinPrice = $buyBackCoins[$t][30];
    $delayMins = $buyBackCoins[$t][31]; $originalAmount = $buyBackCoins[$t][32]; $hoursFlat = $buyBackCoins[$t][33];$coinPrice = $buyBackCoins[$t][34]; $saveMode = $buyBackCoins[$t][35];
    $coinPriceBB = $buyBackCoins[$t][36]; $usdBBAmount = $buyBackCoins[$t][37]; $lowMarketMode = $buyBackCoins[$t][44];
    //$tempPrice = getCoinPrice($CoinID);
    //$liveCoinPrice = $buyBackCoins[$t][9];
    $priceDifferecePct = $buyBackCoins[$t][11];//$lowMarketModeEnabled = $buyBackCoins[$t][39];$pctOnLow = $buyBackCoins[$t][34];
    $hr1ChangePctChange = $buyBackCoins[$t][38];$hr24ChangePctChange = $buyBackCoins[$t][39];$d7ChangePctChange = $buyBackCoins[$t][40];
    $hoursFlatTarget = $buyBackCoins[$t][45]; $delayCoinPurchase = $buyBackCoins[$t][48]; $bbMinsToCancel = $buyBackCoins[$t][55]; $spreadBetRuleIDBB = $buyBackCoins[$t][58];
    //if ($lowMarketModeEnabled > 0){ $lowMarketMultiplier = 100;}else{$lowMarketMultiplier = $pctOnLow;}
    //$BTCAvailable = (($buyBackCoins[$t][31]/100)*$lowMarketMultiplier) - $buyBackCoins[$t][35];
    //$ETHAvailable = (($buyBackCoins[$t][32]/100)*$lowMarketMultiplier) - $buyBackCoins[$t][36];
    //$USDTAvailable = (($buyBackCoins[$t][33]/100)*$lowMarketMultiplier) - $buyBackCoins[$t][37];
    // $lowMarketModeDate = $buyBackCoins[$t][38];
    //$priceDifferecePct = (($liveCoinPrice-$sellPriceBA)/$sellPriceBA)*100;
    $origPurchasePrice = $buyBackCoins[$t][41];
    $livePriceUSD =  $buyBackCoins[$t][42];
    $profit = $buyBackCoins[$t][43];
    $profitPct = $buyBackCoins[$t][11];
    $pctOfAuto = $buyBackCoins[$t][53];
    $buyBackHoursFlatAutoEnabled = $buyBackCoins[$t][50];
    $maxHoursFlat = $buyBackCoins[$t][51];

    if ($buyBackHoursFlatAutoEnabled == 1){
      $hoursFlatTarget = floor(($maxHoursFlat/100)*$pctOfAuto);
    }
    if ($profitPct < $buyBackPct){
      //$pctOfAuto = 100 + $profitPct;
      $hoursFlatTarget = floor(($maxHoursFlat/100)*(($pctOfAuto/100)*Abs(100+($profitPct*4))));
    }
    SuperLog($nFile,"<BR> Check Price: $bBID | $priceDifferecePct | $buyBackPct",$nFunc,"BB1","BBID:$bBID",$logVariSettingAry,'Variables');
    if ($profitPct <=  $buyBackPct AND $delayCoinPurchase <> 1){
      //if($delayMins > 0){ echo "<B> EXIT: Delay:$delayMins"; continue; }
      SuperLog($nFile,"<BR> $priceDifferecePct <=  ($buyBackPct+$profitMultiply)",$nFunc,"BB2","BBID:$bBID",$logVariSettingAry,'Variables');

      //BuyBack
      $marketStats = getMarketstats();
      $reOpenData = reOpenTransactionfromBuyBack($bBID);
      $tmpCoinID = $buyBackCoins[$t][7];$tmpLiveCoinPrice = $buyBackCoins[$t][9];$tmpUserID = $buyBackCoins[$t][12];$tmpBaseCur = $reOpenData[0][3];
      $tmpSendEmail = $reOpenData[0][4];$tmpBuyCoin = $reOpenData[0][5];$tmpSalePrice = $reOpenData[0][6];$tmpBuyRule = $reOpenData[0][7];
      $tmpOffset = $reOpenData[0][8];$tmpOffsetEnabled = $reOpenData[0][9];$tmpBuyType = $reOpenData[0][10];$d11 = $reOpenData[0][11];$tmpFixSellRule = $reOpenData[0][12];$tmpToMerge = $reOpenData[0][13];
      $tmpNoOfPurchases = $reOpenData[0][14];$d15 = $reOpenData[0][15];$tmpType = $reOpenData[0][16];$tmpOriginalPrice = $reOpenData[0][17];
      $tmpSBTransID = $reOpenData[0][18];$tmpSBRuleID = $reOpenData[0][19]; $tmpSymbol = $reOpenData[0][20];
      $tmpMultiSellRuleTemplateID = $reOpenData[0][21];
      SuperLog($nFile,": $priceDifferecePct | BuyBackPct: $buyBackPct Bull/Bear: $bullBearStatus | SellPrice: $sellPriceBA | LivePrice: $liveCoinPrice | BBID: $bBID | LCP: $tmpLiveCoinPrice",$nFunc,"BB3_PriceDiffPct","BBID:$bBID",$logVariSettingAry,'Variables');
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
        $coinAllocMin = 15;
        //$totalAvailable = $USDTAvailable*$tempConvAmt;
      }elseif ($tmpBaseCur == 'BTC'){
        //$tempConvAmt = $BTCPrice;
        //$usdBBAmount = $usdBBAmount * $BTCPrice;
        //$totalAvailable = $BTCAvailable*$tempConvAmt;
        $coinAllocMin = 0.000046;
      }elseif ($tmpBaseCur == 'ETH'){
        //$tempConvAmt = $ETHPrice;
        //$usdBBAmount = $usdBBAmount * $ETHPrice;
        //$totalAvailable = $ETHAvailable*$tempConvAmt;
        $coinAllocMin = 0.00065;
      }
      //if ($allBuyBackAsOverride == 1){ $lowBuyMode = TRUE;}else{$lowBuyMode=FALSE; }
      $coinAllocation = getNewCoinAllocation($tmpBaseCur,$tmpUserID,$lowMarketMode,$allBuyBackAsOverride,0,$spreadBetRuleID);
      if ($coinAllocation <= $coinAllocMin && $allBuyBackAsOverride == 0){
          SuperLog($nFile,"<BR> EXIT CoinAllocation: $tmpBaseCur | $type | $BTCAmount | $ogBTCAmount| $coinAllocation",$nFunc,"BB4","BBID:$bBID",$logExitSettingAry,'Exit');
          //newLogToSQL("BuyBack","CoinAllocation: $coinAllocation",3,0,"Exit","BBID:$bBID");
          continue;
      }

      //$buyBackPurchasePrice = ($tmpLiveCoinPrice*$quantity*$tempConvAmt)+$bbKittyAmount;
      //$buyBackPurchasePrice = (($sellPriceBA + (($sellPriceBA/100)*$priceDifferecePct))*$originalAmount*$tempConvAmt)+$bbKittyAmount;
      $delayMins = $buyBackCoins[$t][31]; $originalAmount = $buyBackCoins[$t][32]; $hoursFlat = $buyBackCoins[$t][33];

      SuperLog($nFile,"<BR> $bBID | $profit | $profitPct | $livePriceUSD | $origPurchasePrice",$nFunc,"BB5","BBID:$bBID",$logVariSettingAry,'Variables');
      //if ($profitPct > 0.25 AND $saveMode = 2){
      //  $buyBackPurchasePrice = ($livePriceUSD - $profit)+$bbKittyAmount;
      //  LogToSQL("BuyBackTEST1A","Qty:$quantity CPBB: $coinPriceBB | $origPurchasePrice SPBA: $livePriceUSD | $livePriceUSD Profit: $profit PCT: $profitPct | HoursFlat: $hoursFlat",3,0);
      //  LogToSQL("BuyBackTEST1B","($buyBackPurchasePrice = ($livePriceUSD - $profit)+$bbKittyAmount; | $saveMode | $profitPct",3,0);
      //}else{
        $buyBackPurchasePrice = $livePriceUSD + $bbKittyAmount;
        //$buyBackPurchasePrice = $tmpLiveCoinPrice/$tmpPrice;
        SuperLog($nFile,"Qty:$quantity CPBB: $coinPriceBB | $origPurchasePrice SPBA: $livePriceUSD | $livePriceUSD Profit: $profit PCT: $profitPct",$nFunc,"BB6","BBID:$bBID",$logVariSettingAry,'Variables');
        SuperLog($nFile,"$buyBackPurchasePrice = $livePriceUSD + $bbKittyAmount; | $originalAmount * $livePriceUSD;| $saveMode | $profitPct",$nFunc,"BB7","BBID:$bBID",$logVariSettingAry,'Variables');
      //}



      updateBuyBackKittyAmount($tmpBaseCur,$bbKittyAmount,$tmpUserID);
      //if($tmpSalePrice <= 0 OR $hr1ChangePctChange > -7){ newLogToSQL("BuyBack","PctProfit: $tmpSalePrice | $hr1ChangePctChange",3,1,"Exit","BBID:$bBID");echo "<B> EXIT: PctProfit:$tmpSalePrice | $profitPct | $hr1ChangePctChange"; continue;}
      if ($hoursFlat<$hoursFlatTarget){ SuperLog($nFile,"HoursFlat: $hoursFlat",$nFunc,"BB8","BBID:$bBID",$logExitSettingAry,'Exit');  continue;}

      //if ($buyBackPurchasePrice < 20 or $totalAvailable < 20 ){ return False;}
      if ($tmpLiveCoinPrice <> 0){
        if ($tmpSBRuleID <> 0){
          $spreadbetBuyBack = getBuyBackData($tmpSBRuleID);
          $spreadbetBuyBackSize = count($spreadbetBuyBack);
          for ($p=0;$p<$spreadbetBuyBackSize;$p++){
            if ($spreadbetBuyBackSize > 3){
              $tmpCoinID = $spreadbetBuyBack[$p][7]; $tmpLiveCoinPrice = $spreadbetBuyBack[$p][9]; $tmpUserID = $spreadbetBuyBack[$p][12]; $tmpBaseCur = $spreadbetBuyBack[$p][59]; $tmpSendEmail = 1; $tmpBuyCoin = 1;
              $usdBBAmount = $spreadbetBuyBack[$p][37];$tmpBuyRule = $spreadbetBuyBack[$p][60];$tmpOffset = $spreadbetBuyBack[$p][62];$tmpOffsetEnabled = $spreadbetBuyBack[$p][61];$tmpBuyType = 1; $bbMinsToCancel = $spreadbetBuyBack[$p][54]; $tmpFixSellRule  = $spreadbetBuyBack[$p][63];
              $tmpToMerge = $spreadbetBuyBack[$p][64]; $tmpNoOfPurchases = $spreadbetBuyBack[$p][65];$hoursFlatTarget = 1; $tmpType  = $spreadbetBuyBack[$p][66];$tmpSBTransID  = $spreadbetBuyBack[$p][5];$overrideCoinAlloc = $spreadbetBuyBack[$p][67]; $bBID = $spreadbetBuyBack[$p][0];
              addTrackingCoin($tmpCoinID, $tmpLiveCoinPrice, $tmpUserID, $tmpBaseCur, $tmpSendEmail, $tmpBuyCoin, $usdBBAmount, $tmpBuyRule, $tmpOffset, $tmpOffsetEnabled, $tmpBuyType, $bbMinsToCancel, $tmpFixSellRule,$tmpToMerge,$tmpNoOfPurchases,$hoursFlatTarget,$tmpType,$tmpLiveCoinPrice,$tmpSBTransID,$tmpSBRuleID,$overrideCoinAlloc,'BuyBack',0);
              SuperLog($nFile,"<BR>addTrackingCoin($tmpCoinID, $tmpLiveCoinPrice, $tmpUserID, $tmpBaseCur, $tmpSendEmail, $tmpBuyCoin, $buyBackPurchasePrice, $tmpBuyRule, $tmpOffset, $tmpOffsetEnabled, $tmpBuyType, 240, $tmpFixSellRule,$tmpToMerge,$tmpNoOfPurchases,$noOfRaisesInPrice,$tmpType,$tmpLiveCoinPrice,$tmpSBTransID,$tmpSBRuleID);",$nFunc,"BB9","BBID:$bBID",$logEventsSettingAry,'Events');
              //LogToSQL("BuyBack","addTrackingCoin($tmpCoinID, $tmpLiveCoinPrice, $tmpUserID, $tmpBaseCur, $tmpSendEmail, $tmpBuyCoin, $buyBackPurchasePrice, $tmpBuyRule, $tmpOffset, $tmpOffsetEnabled, $tmpBuyType, 240, $tmpFixSellRule,$tmpToMerge,$tmpNoOfPurchases,$noOfRaisesInPrice,$tmpType,$tmpLiveCoinPrice,$tmpSBTransID,$tmpSBRuleID);",3,1);
              SuperLog($nFile,"BuyBackKitty","Adding $bbKittyAmount to $bBID | TotalBTC: $BTC_BB_Amount| Total USDT: $usdt_BB_Amount| TotalETH: $eth_BB_Amount | BTC_P: $portionBTC| USDT_P: $portion| ETH_P: $portionETH",$nFunc,"BB10","BBID:$bBID",$logVariSettingAry,'Variables');
              //CloseBuyBack
              closeBuyBack($bBID);
              //addWebUsage($userID,"Remove","BuyBack");
              //addWebUsage($userID,"Add","BuyTracking");
              buyBackDelay($tmpCoinID,4320,$tmpUserID);
              addOldBuyBackTransID($bBID,$tmpCoinID);
              //logAction("runBuyBack; addTrackingCoin : $tmpSymbol | $tmpCoinID | $tmpBaseCur | $tmpLiveCoinPrice | $tmpUserID | $buyBackPurchasePrice | $noOfRaisesInPrice | $tmpType | $tmpOriginalPriceWithBuffer | $overrideCoinAlloc | $bBID | $bbKittyAmount | $TransactionID", 'BuySellFlow', 1);
            }
          }
        }else{
          addTrackingCoin($tmpCoinID, $tmpLiveCoinPrice, $tmpUserID, $tmpBaseCur, $tmpSendEmail, $tmpBuyCoin, $usdBBAmount, $tmpBuyRule, $tmpOffset, $tmpOffsetEnabled, $tmpBuyType, $bbMinsToCancel, $tmpFixSellRule,$tmpToMerge,$tmpNoOfPurchases,$hoursFlatTarget,$tmpType,$tmpLiveCoinPrice,$tmpSBTransID,$tmpSBRuleID,$overrideCoinAlloc,'BuyBack',0);
          //echo "<BR>addTrackingCoin($tmpCoinID, $tmpLiveCoinPrice, $tmpUserID, $tmpBaseCur, $tmpSendEmail, $tmpBuyCoin, $buyBackPurchasePrice, $tmpBuyRule, $tmpOffset, $tmpOffsetEnabled, $tmpBuyType, 240, $tmpFixSellRule,$tmpToMerge,$tmpNoOfPurchases,$noOfRaisesInPrice,$tmpType,$tmpLiveCoinPrice,$tmpSBTransID,$tmpSBRuleID);";
          SuperLog($nFile,"addTrackingCoin($tmpCoinID, $tmpLiveCoinPrice, $tmpUserID, $tmpBaseCur, $tmpSendEmail, $tmpBuyCoin, $buyBackPurchasePrice, $tmpBuyRule, $tmpOffset, $tmpOffsetEnabled, $tmpBuyType, 240, $tmpFixSellRule,$tmpToMerge,$tmpNoOfPurchases,$noOfRaisesInPrice,$tmpType,$tmpLiveCoinPrice,$tmpSBTransID,$tmpSBRuleID);",$nFunc,"BB11","BBID:$bBID",$logEventsSettingAry,'Events');
          SuperLog($nFile,"Adding $bbKittyAmount to $bBID | TotalBTC: $BTC_BB_Amount| Total USDT: $usdt_BB_Amount| TotalETH: $eth_BB_Amount | BTC_P: $portionBTC| USDT_P: $portion| ETH_P: $portionETH",$nFunc,"BB12","BBID:$bBID",$logVariSettingAry,'Variables');
          //CloseBuyBack
          closeBuyBack($bBID);
          //addWebUsage($userID,"Remove","BuyBack");
          //addWebUsage($userID,"Add","BuyTracking");
          buyBackDelay($tmpCoinID,4320,$tmpUserID);
          addOldBuyBackTransID($bBID,$tmpCoinID);
          //logAction("runBuyBack; addTrackingCoin : $tmpSymbol | $tmpCoinID | $tmpBaseCur | $tmpLiveCoinPrice | $tmpUserID | $buyBackPurchasePrice | $noOfRaisesInPrice | $tmpType | $tmpOriginalPriceWithBuffer | $overrideCoinAlloc | $bBID | $bbKittyAmount | $TransactionID", 'BuySellFlow', 1);
        }

        return True;
      }else{
        SuperLog($nFile,"LivePrice: $tmpLiveCoinPrice Coin:$tmpSymbol CoinID:$tmpCoinID PriceWBuffer:$tmpOriginalPriceWithBuffer",$nFunc,"BB13","BBID:$bBID",$logVariSettingAry,'Variables');
      }
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
      newTrackingSellCoins($bounceTopPrice, $userID,$transactionID,1,1,0,0.0,11,'Sell','runSpreadBetSellAndBuyback',0);
      setTransactionPending($transactionID);
      $usd_Price = $LiveCoinPrice * $amount;
      WriteBuyBack($transactionID,$finalProfitPct,11, $totalMins,$LiveCoinPrice,$amount,$purchasePrice,$usd_Price);
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
    $risesInPrice = $spread[$y][38]; $timeToCancelBuyMins = $spread[$y][39]; $lowMarketModeEnabled = $spread[$y][40]; $SpreadBetRuleID = $spread[$y][41];
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
        $spreadBetToBuy = getNewCoinAllocation($baseCurrency,$UserID,$lowMarketModeEnabled,0,0,$SpreadBetRuleID);
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
        $date = date("Y-m-d H:i:s", time()); $SendEmail = 1; $BuyCoin = 1;$ruleIDBuy = 172;$CoinSellOffsetEnabled = 0; $CoinSellOffsetPct = 0;
        $buyType = 1;  $SellRuleFixed = 555;$noOfPurchases = 0;


        //BuyCoins
        //echo "<BR>buyCoins($APIKey, $APISecret,$symbol, $Email, $userID, $date, $baseCurrency,$SendEmail,$BuyCoin,$BTCAmount, $ruleIDBuy,$UserName,$coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed, 0, $noOfPurchases+1);";
        //$checkBuy = buyCoins($APIKey, $APISecret,$symbol, $Email, $userID, $date, $baseCurrency,$SendEmail,$BuyCoin,$BTCAmount, $ruleIDBuy,$UserName,$coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed, 0, $noOfPurchases+1);
        $ogCoinPrice = $liveCoinPrice - (($liveCoinPrice/100)*1);
        if($BTCAmount<= 0 ){ continue;}
        LogToSQL("SpreadBetTracking","addTrackingCoin($coinID, $liveCoinPrice, $userID, $baseCurrency, $SendEmail, $BuyCoin, $BTCAmount, $ruleIDBuy, $CoinSellOffsetPct, $CoinSellOffsetEnabled, $buyType, $timeToCancelBuyMins, $SellRuleFixed,0,0,$risesInPrice,'SpreadBuy',$ogCoinPrice,$spreadBetTransID,$spreadBetRuleID);",3,1);
        addTrackingCoin($coinID, $liveCoinPrice, $userID, $baseCurrency, $SendEmail, $BuyCoin, $BTCAmount, $ruleIDBuy, $CoinSellOffsetPct, $CoinSellOffsetEnabled, $buyType, $timeToCancelBuyMins, $SellRuleFixed,0,0,$risesInPrice,'SpreadBuy',$ogCoinPrice,$spreadBetTransID,$spreadBetRuleID,0,'RunSpreadBet',0);
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


function runTrackingSellCoin($newTrackingSellCoins,$marketStats,$webSettingsAry){
  $finalBool = False;
  $newTrackingSellCoinsSize = count($newTrackingSellCoins);
  $echoExitText = $webSettingsAry[2][1];
  //$echoProgramFlow = 1;
  $echoProgramFlow = $webSettingsAry[0][1];
  //$echoTestText = 0;
  $echoTestText = $webSettingsAry[1][1];
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
    $market1HrChangePct = $marketStats[0][1]; $reEnableBuyRule = $newTrackingSellCoins[$b][46]; $reEnableBuyRuleEnabled = $newTrackingSellCoins[$b][45]; $trackingCount = $newTrackingSellCoins[$b][48];
    $overrideBBAmount = $newTrackingSellCoins[$b][49]; $overrideBBSaving = $newTrackingSellCoins[$b][50];
    Echo "<BR> Check Sell: Cp: $CoinPrice | TRID: $TransactionID | Am: $Amount ";
    if ($minsFromDate > 1440 and $trackingType == 'SavingsSell' and $trackingType <> 'SellBypass'){
      closeNewTrackingSellCoin($TransactionID);
      updateTransStatus($TransactionID,'Saving');
      //addWebUsage($userID,"Remove","SellTracking");
      logAction("runTrackingSellCoin; CancelSavingSell : $coin | $CoinID | $baseCurrency | $userID | $minsFromDate | $TransactionID ", 'BuySellFlow', 1);
      $finalBool = True;
    }
    $ProfitPct = $newTrackingSellCoins[$b][44];
    echo "<BR> Checking $coin : $CoinPrice ; No Of RISES $NoOfRisesInPrice ! Profit % $ProfitPct | Mins from date $minsFromDate ! Original Coin Price $originalCoinPrice | mins from Start: $minsFromStart | UserID : $userID Falls in Price: $fallsInPrice TrackingCount: $trackingCount";
    $readyToSell = trackingCoinReadyToSell($LiveCoinPrice,$minsFromStart,$type,$baseSellPrice,$TransactionID,$totalRisesInPrice,$ProfitPct,$minsFromDate,$lastPrice,$NoOfRisesInPrice,$trackingSellID,$market1HrChangePct,$originalSellPrice);
    if ($readyToSell < 90 OR ($trackingCount >= 5 AND $ProfitPct > 0.25) OR $trackingType == 'SellBypass'){
      $PurchasePrice = ($Amount*$CoinPrice);
      $salePrice = $LiveCoinPrice * $Amount;
      $profit = $newTrackingSellCoins[$b][43];

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
          newLogToSQL("SellSavings", "Sell Savings Coin: $CoinID | $bittrexRef | $FixSellRule", $userID, 1,"Sell Coin","TransactionID:$TransactionID");
          updateCoinSwapTable($TransactionID,'AwaitingSavingsSale',$bittrexRef,$CoinID,$LiveCoinPrice,$baseCurrency,$LiveCoinPrice * $Amount,$CoinPrice * $Amount,'Sell');
          closeNewTrackingSellCoin($TransactionID);
          logAction("runTrackingSellCoin; SavingsSell : $coin | $CoinID | $baseCurrency | $LiveCoinPrice | $Amount | $userID | $minsFromDate | $TransactionID | $bittrexRef", 'BuySellFlow', 1);
          //return True;
        }else{
          newLogToSQL("SellSavingsError", var_dump($obj), $userID, $GLOBALS['logToSQLSetting'],"Sell Coin","TransactionID:$TransactionID");
        }

      }else{
        if (!Empty($KEK)){ $APISecret = Decrypt($KEK,$newTrackingSellCoins[$b][11]);}

        $newOrderDateAgain = date("YmdHis", time());

          //LogToSQL("SaveResidualCoins","$saveResidualCoins",3,1);
          newLogToSQL("TrackingSell","$coin | $CoinID | $CoinPrice | $LiveCoinPrice | $Amount | $TransactionID | $saveResidualCoins $type | $ProfitPct | $PurchasePrice | $salePrice | $profit",3,1,"SaveResidualCoins","TransactionID:$TransactionID");
          if ($saveResidualCoins == 1 and $ProfitPct >= 0.25){
            $oldAmount = $Amount;
            if ($origAmount == 0){
              //$tempFee = number_format(((($LiveCoinPrice*$Amount)/100)*0.25),8);
              //$ogPurchasePrice = $LiveCoinPrice*$Amount;
              $sellFee = ($PurchasePrice/100)*0.82;
              $Amount = (($PurchasePrice+$sellFee) / $LiveCoinPrice);
              newLogToSQL("TrackingSell","$PurchasePrice | $sellFee | $LiveCoinPrice | $Amount | $oldAmount",3,1,"SaveResidual","TransactionID:$TransactionID");
            }
            newLogToSQL("TrackingSell","$oldAmount | $Amount | $PurchasePrice | $sellFee | $LiveCoinPrice | $ProfitPct",3,1,"NewAmountToSQL","TransactionID:$TransactionID");
            if ($origAmount == 0){
              updateSellAmount($TransactionID,$Amount, $oldAmount);
              newLogToSQL("TrackingSell","updateSellAmount($TransactionID,$Amount, $oldAmount);",3,1,"SaveResidualCoins4","TransactionID:$TransactionID");
            }

            newLogToSQL("TrackingSell","$coin | $CoinID | $oldAmount | $CoinPrice | $PurchasePrice | $LiveCoinPrice | $Amount | $TransactionID | $tempFee",3,1,"SaveResidualCoins2","TransactionID:$TransactionID");

            $OrderString = "ORD".$coin.$newOrderDateAgain.$BuyRule;
            $residualAmount = $oldAmount - $Amount;
            //ResidualCoinsToSaving($residualAmount,$OrderString ,$TransactionID);
            //newLogToSQL("TrackingSell","ResidualCoinsToSaving($oldAmount-$Amount, ORD.$coin.$newOrderDate.$BuyRule,$TransactionID);",3,1,"SaveResidualCoins3","TransactionID:$TransactionID");
          }
          newLogToSQL("TrackingSell","sellCoins($APIKey, $APISecret,$coin, $Email, $userID, 0,$newOrderDateAgain, $baseCurrency,$SendEmail,$SellCoin, $FixSellRule,$UserName,$OrderNo,$Amount,$CoinPrice,$TransactionID,$CoinID,$CoinSellOffsetEnabled,$CoinSellOffsetPct,$LiveCoinPrice, $type);[$readyToSell][$trackingCount][$trackingType]",3,1,"Success","TransactionID:$TransactionID");
        $checkSell = sellCoins($APIKey, $APISecret,$coin, $Email, $userID, 0,$newOrderDateAgain, $baseCurrency,$SendEmail,$SellCoin, $FixSellRule,$UserName,$OrderNo,$Amount,$CoinPrice,$TransactionID,$CoinID,$CoinSellOffsetEnabled,$CoinSellOffsetPct,$LiveCoinPrice, $type);

        if ($checkSell){
          closeNewTrackingSellCoin($TransactionID);
          //addWebUsage($userID,"Remove","SellTracking");
          //addWebUsage($userID,"Add","BittrexAction");
          newLogToSQL("TrackingSell","sellCoins($APIKey, $APISecret,$coin, $Email, $userID, 0,$newOrderDateAgain, $baseCurrency,$SendEmail,$SellCoin, $FixSellRule,$UserName,$OrderNo,$Amount,$CoinPrice,$TransactionID,$CoinID,$CoinSellOffsetEnabled,$CoinSellOffsetPct,$LiveCoinPrice, $type);",3,$GLOBALS['logToSQLSetting'],"Success","TransactionID:$TransactionID");
          addUSDTBalance('USDT', $BTCAmount,$LiveCoinPrice, $userID);
          logAction("runTrackingSellCoin; sellCoins : $coin | $CoinID | $baseCurrency | $LiveCoinPrice | $CoinPrice | $Amount | $userID | $minsFromDate | $type | $TransactionID", 'BuySellFlow', 1);
          if ($saveResidualCoins == 1){
            $finalResidual = ($oldAmount-$Amount);
            newLogToSQL("TrackingSell:Residual","$finalResidual = ($oldAmount-$Amount);",3,1,"ResidualAmount","TransactionID:$TransactionID");
            saveResidualAmountToBittrex($TransactionID,$finalResidual);
          }
          //addBuyBackOverride($overrideBBAmount,$overrideBBSaving,$TransactionID);
          if ($reEnableBuyRuleEnabled == 1){ buySellProfitEnable($CoinID,$userID,1,1,20,$FixSellRule);}
        }
      }
      $finalBool = True;
    }elseif ($readyToSell > 90){
      $finalBool = True;
    }

  }
  return $finalBool;
}


function runBuyCoins($coins,$userProfit,$marketProfit,$ruleProfit,$totalBTCSpent,$dailyBTCSpent,$baseMultiplier,$delayCoinPurchase,$buyRules,$coinPriceMatch,$coinPricePatternList,$coin1HrPatternList,$autoBuyPrice,$trackCounter,$buyCounter,$ruleType,$webSettingsAry){
  $nFile = "BuySellCoins"; $nFunc = "BuyCoins";
  $tempSettings = getSetting($webSettingsAry,$nFile,$nFunc);
  $logFlowSettingAry = $tempSettings[0]; $logVariSettingAry = $tempSettings[1]; $logSQLSettingAry = $tempSettings[2]; $logExitSettingAry = $tempSettings[3]; $logAPISettingAry = $tempSettings[4]; $logEventsSettingAry = $tempSettings[5];
  echo "<BR> Variables for Log: $logFlowSettingAry | $logVariSettingAry | $logSQLSettingAry | $logExitSettingAry | $logAPISettingAry";
  $apiVersion = 3;
  $finalBool = False;
  echo "<blockquote>";
  SuperLog($nFile,"BuyCoin Key: ",$nFunc,"BC1","",$logFlowSettingAry,'Flow');
  SuperLog($nFile,"1: MarketCap | 2: Volume | 3: BuyOrders | 4: 1HrPctChange | 5: 24HrPctChange  ",$nFunc,"BC2","",$logVariSettingAry,'Variables');
  SuperLog($nFile,"6: 7DPctChange | 7: CoinPrice | 8: SellOrders | 9: PriceTrend | 10: MinPrice ",$nFunc,"BC3","",$logVariSettingAry,'Variables');
  SuperLog($nFile,"11: AutoPrice | 12: CoinPattern | 13: GlobalAllDisabled | 14: 1HrPattern | 15: HoursFlat ",$nFunc,"BC4","",$logVariSettingAry,'Variables');
  SuperLog($nFile,"16: PriceDipMin <BR>",$nFunc,"BC5","",$logVariSettingAry,'Variables');
  $coinLength = Count($coins);
  $buyRulesSize = count($buyRules);
  SuperLog($nFile,"Coin Length: $coinLength  RuleLength: $buyRulesSize <BR>",$nFunc,"BC6","",$logVariSettingAry,'Variables');
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
    $priceDipHoursFlatTarget = $coins[$x][40]; $priceDipMinPrice = $coins[$x][41]; $hoursSinceAdded = $coins[$x][46];
    $maxHoursFlat = $coins[$x][47]; $month6Low = $coins[$x][43]; $month3Low = $coins[$x][44];
    $risesInPrice = $coins[$x][47]; $caaOffset = $coins[$x][48]; $caahours = $coins[$x][49];
    $hoursFlatHigh = $coins[$x][50];$hoursFlatLow = $coins[$x][51];
    SuperLog($nFile,"Checking: $coinID - $symbol - $baseCurrency  <BR>",$nFunc,"BC7","",$logVariSettingAry,'Variables');
    echo "<blockquote>";
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
      $Hr1ChangeTrendEnabled = $buyRules[$y][63]; $Hr1ChangeTrend = $buyRules[$y][64]; //$risesInPrice = $buyRules[$y][65];
      $totalProfitPauseEnabled = $buyRules[$y][66]; $totalProfitPause = $buyRules[$y][67]; $rulesPauseEnabled = $buyRules[$y][68];
      $rulesPause = $buyRules[$y][69]; $rulesPauseHours = $buyRules[$y][70]; $overrideDisableRule = $buyRules[$y][73];
      $limitBuyAmountEnabled = $buyRules[$y][74]; $limitBuyAmount = $buyRules[$y][75];
      $limitBuyTransactionsEnabled = $buyRules[$y][78]; $limitBuyTransactions = $buyRules[$y][79]; $overrideCoinAlloc = $buyRules[$y][80];
      $oneTimeBuy = $buyRules[$y][81]; $limitToBaseCurrency  = $buyRules[$y][82];
      $priceDipCoinFlatEnabled = $buyRules[$y][85]; $priceDipHours = $buyRules[$y][86]; $priceDipMinPriceEnabled = $buyRules[$y][84];
      $pctOverMinPrice = $buyRules[$y][87]; $finalPriceDipMinPrice = $priceDipMinPrice + (($priceDipMinPrice/100)*$pctOverMinPrice);

      $buyRuleType = $buyRules[$y][89];
      SuperLog($nFile, "This is a Test | $KEK | $APISecret | $APIKey",$nFunc,"BC8","",$logVariSettingAry,'Variables');
      if (!Empty($KEK)){$APISecret = decrypt($KEK,$buyRules[$y][31]);}
      SuperLog($nFile, "FinalPriceDipMinPrice:  $finalPriceDipMinPrice = $priceDipMinPrice - (($priceDipMinPrice/100)*$pctOverMinPrice); LIVE: $LiveCoinPrice<BR>",$nFunc,"BC9","",$logVariSettingAry,'Variables');

      $EnableDailyBTCLimit = $buyRules[$y][32]; $DailyBTCLimit = $buyRules[$y][33]; $EnableTotalBTCLimit = $buyRules[$y][34];
      $TotalBTCLimit= $buyRules[$y][35]; $userID = $buyRules[$y][0]; $ruleIDBuy = $buyRules[$y][36]; $CoinSellOffsetPct = $buyRules[$y][37];
      $CoinSellOffsetEnabled = $buyRules[$y][38];
      $priceTrendEnabled = $buyRules[$y][39]; $price4TrendTrgt = $buyRules[$y][40];$price3TrendTrgt = $buyRules[$y][41];$lastPriceTrendTrgt = $buyRules[$y][42];
      $livePriceTrendTrgt = $buyRules[$y][43]; $userActive = $buyRules[$y][44]; $disableUntil = $buyRules[$y][45]; $hoursDisableUntil = $buyRules[$y][83];
      $userBaseCurrency = $buyRules[$y][46]; $noOfBuys = $buyRules[$y][47]; $buyType = $buyRules[$y][48]; $timeToCancelBuyMins = $buyRules[$y][49];
      $BuyPriceMinEnabled = $buyRules[$y][50]; $BuyPriceMin = $buyRules[$y][51];
      $limitToCoin = $buyRules[$y][52]; $autoBuyCoinEnabled = $buyRules[$y][53];//$autoBuyPrice = $buyRules[$y][54];
      $buyAmountOverrideEnabled = $buyRules[$y][55]; $buyAmountOverride = $buyRules[$y][56];
      if ($buyAmountOverrideEnabled == 1){ $BTCAmount = $buyAmountOverride;}
      $newBuyPattern = $buyRules[$y][57];
      $MarketDropStopEnabled = $buyRules[$y][71]; $marketDropStopPct = $buyRules[$y][72];
      $overrideCancelBuyTimeEnabled = $buyRules[$y][76];
      $overrideCancelBuyTimeMins = $buyRules[$y][77];
      $noOfBuyModeOverrides = $buyRules[$y][78];$coinModeOverridePriceEnabled = $buyRules[$y][79];
      $pctOfAuto = $buyRules[$y][88];
      $buyCounter = initiateAry($buyCounter,$userID."-".$coinID);
      $buyCounter = initiateAry($buyCounter,$userID."-Total");
      SuperLog($nFile,"Placeholder 1:  $coinID - $symbol - $baseCurrency  RuleID: $ruleIDBuy | $ruleType <BR>",$nFunc,"BC10","",$logFlowSettingAry,'Flow');
      if ($buyRuleType != $ruleType){ continue;}
      if ($risesInPrice == 0){
        //$risesInPrice =
      }
      if ($BuyPriceMinEnabled == 2){
        $BuyPriceMin = ($month6Low + $month3Low)/2;
        $BuyPriceMinEnabled = 1;
      }
      if ($priceDipCoinFlatEnabled == 2){
        $priceDipHours = floor(($maxHoursFlat/100)*$pctOfAuto);
        $priceDipCoinFlatEnabled = 1;
      }elseif ($priceDipCoinFlatEnabled == 3){//High
        //$priceDipHoursFlatTarget = $hoursFlatHigh;
        $priceDipHours = $hoursFlatHigh;
        $priceDipCoinFlatEnabled = 1;
      }elseif ($priceDipCoinFlatEnabled == 4){//Low
        $//priceDipHoursFlatTarget = $hoursFlatLow;
        $priceDipHours = $hoursFlatLow;
        $priceDipCoinFlatEnabled = 1;
      }
      SuperLog($nFile,"Placeholder 2:  $coinID - $symbol - $baseCurrency  RuleID: $ruleIDBuy <BR>",$nFunc,"BC11","",$logFlowSettingAry,'Flow');
      if ($overrideCancelBuyTimeEnabled == 1){$timeToCancelBuyMins = $overrideCancelBuyTimeMins;}
      $delayCoinPurchaseSize = 0;
      if (!empty($delayCoinPurchase)){
        $delayCoinPurchaseSize = count($delayCoinPurchase);
      }

      for ($b=0; $b<$delayCoinPurchaseSize; $b++){
        $delayCoinPurchaseUserID = $delayCoinPurchase[$b][2]; $delayCoinPurchaseCoinID = $delayCoinPurchase[$b][1];
        if ($delayCoinPurchaseUserID == $userID AND $delayCoinPurchaseCoinID == $coinID){
          SuperLog($nFile,"EXIT: Delay CoinID: $coinID! ",$nFunc,"BC12","",$logExitSettingAry,'Exit'); continue 2;
        }
      }

      if ($priceDipMinPriceEnabled == 1){
        if ($hoursSinceAdded < 3000){
          SuperLog($nFile,"EXIT: PriceDip Hours Since Added less than 300: $hoursSinceAdded | $coinID - $symbol - $baseCurrency  RuleID: $ruleIDBuy <BR>",$nFunc,"BC13","",$logExitSettingAry,'Exit');
          continue;
        }
      }
      SuperLog($nFile,"Placeholder 3:  $coinID - $symbol - $baseCurrency  RuleID: $ruleIDBuy <BR>",$nFunc,"BC14","",$logFlowSettingAry,'Flow');
      $ruleProfitSize = count($ruleProfit);
      for ($h=0; $h<$ruleProfitSize; $h++){
          if ($limitBuyAmountEnabled == 1){
            SuperLog($nFile,	"TEST limitBuyAmountEnabled: $limitBuyAmountEnabled | ".$ruleProfit[$h][4]." | $ruleIDBuy | ".$ruleProfit[$h][1]." | $limitBuyAmount",$nFunc,"BC15","",$logVariSettingAry,'Variables');

            //echoAndLog("BuyCoin:$buyRuleType","TEST limitBuyAmountEnabled: $limitBuyAmountEnabled | ".$ruleProfit[$h][4]." | $ruleIDBuy | ".$ruleProfit[$h][1]." | $limitBuyAmount",3,$echoTestText,"BuySellCoins","None");
            if ($ruleProfit[$h][4] == $ruleIDBuy and $ruleProfit[$h][1] >= $limitBuyTransactions){SuperLog($nFile, "EXIT: Rule Amount Exceeded! ",$nFunc,"BC16","",$logExitSettingAry,'Exit'); continue;}
          }
          if ($limitBuyTransactionsEnabled == 1 and $coinModeOverridePriceEnabled == 0){
            SuperLog($nFile,"TEST limitBuyTransactionEnabled: $limitBuyTransactionsEnabled | ".$ruleProfit[$h][4]." | $ruleIDBuy | ".$ruleProfit[$h][5]." | $limitBuyTransactions",$nFunc,"BC17","",$logVariSettingAry,'Variables');
            //echoAndLog("BuyCoin:$buyRuleType","TEST limitBuyTransactionEnabled: $limitBuyTransactionsEnabled | ".$ruleProfit[$h][4]." | $ruleIDBuy | ".$ruleProfit[$h][5]." | $limitBuyTransactions",3,$echoTestText,"BuySellCoins","None");
            if ($ruleProfit[$h][4] == $ruleIDBuy and $ruleProfit[$h][5] >= $limitBuyTransactions){SuperLog($nFile,"EXIT: Rule Transaction Count Exceeded! ",$nFunc,"BC18","",$logExitSettingAry,'Exit'); continue;}
          }elseif($coinModeOverridePriceEnabled == 1 and $limitBuyAmountEnabled == 1){
            SuperLog($nFile,"TEST limitBuyTransactionEnabled: $limitBuyAmount | $noOfBuyModeOverrides | ".$ruleProfit[$h][5],$nFunc,"BC19","",$logVariSettingAry,'Variables');
            //echoAndLog("BuyCoin:$buyRuleType","TEST limitBuyTransactionEnabled: $limitBuyAmount | $noOfBuyModeOverrides | ".$ruleProfit[$h][5],3,$echoTestText,"BuySellCoins","None");
            if (($limitBuyAmount + $noOfBuyModeOverrides) >=  $ruleProfit[$h][5]){ SuperLog($nFile,"EXIT: Rule Transaction Count Override Exceeded! ",$nFunc,"BC20","",$logExitSettingAry,'Exit'); continue;}
          }
      }

      if (isset($marketProfit[0][0])){
        if ($MarketDropStopEnabled == 1 and $marketProfit[0][0] <= $marketDropStopPct and $overrideDisableRule == 0){
          SuperLog($nFile, "Market Profit Enbled: $MarketDropStopEnabled Pct: $marketDropStopPct current: ".$marketProfit[0][0]." | RuleID $ruleIDBuy",$nFunc,"BC21","RuleID:$ruleIDBuy CoinID:$coinID",$logVariSettingAry,'Variables');
          pauseRule($ruleIDBuy,4, $userID);
          pauseTracking($userID);

        }elseif ($MarketDropStopEnabled == 1 and $marketProfit[0][1] >= 0.3 and $overrideDisableRule == 0){
          SuperLog($nFile, "pauseRule($ruleIDBuy,0, $userID);| MarketProfit: ".$marketProfit[0][1],$nFunc,"BC22","RuleID:$ruleIDBuy CoinID:$coinID",$logVariSettingAry,'Variables');
          pauseRule($ruleIDBuy,0, $userID);
        }
      }
      SuperLog($nFile, "Placeholder 4:  $coinID - $symbol - $baseCurrency  RuleID: $ruleIDBuy <BR>",$nFunc,"BC23","",$logFlowSettingAry,'Flow');
      //echo "<BR> I'm here1!!! USERID:$userID ; COIN:$symbol($coinID) ; BASE:$baseCurrency ; RULE:$ruleIDBuy";
      $profitNum = findUserProfit($userProfit,$userID);
      //echo "<BR> Profit!!! $totalProfitPauseEnabled ;$profitNum; $totalProfitPause ; $rulesPause ; $rulesPauseEnabled";
      if ($totalProfitPauseEnabled == 1 && $profitNum<= $totalProfitPause && $ruleIDBuy == $rulesPause){
        if ($rulesPauseEnabled == 1){
          SuperLog($nFile, "PAUSING RULES $rulesPause for $rulesPauseHours HOURS",$nFunc,"BC24","",$logExitSettingAry,'Exit');
          //newLogToSQL("BuyCoins", "pauseRule($rulesPause, $rulesPauseHours);", $userID,$GLOBALS['logToSQLSetting'],"RulesPause","RuleID:$ruleIDBuy CoinID:$coinID");
          pauseRule($rulesPause, $rulesPauseHours);
        }
        SuperLog($nFile, "EXIT: TotalProfitPauseEnabled $totalProfitPauseEnabled Profit: $profitNum $totalProfitPause ",$nFunc,"BC25","",$logExitSettingAry,'Exit');
        continue;}
        //else{ echo "<BR> EXIT PROFIT!";}
      $GLOBALS['allDisabled'] = false;
      if (empty($APIKey) && empty($APISecret)){ SuperLog($nFile, "EXIT: No API Key  $coinID - $symbol - $baseCurrency  RuleID: $ruleIDBuy <BR>",$nFunc,"BC26","",$logExitSettingAry,'Exit'); continue;}
      if ($APIKey=="NA" && $APISecret == "NA"){
        //Echo "<BR> EXIT: API Key Missing: $userID $APIKey $ruleIDBuy<BR>";
        SuperLog($nFile, "EXIT: No API Key  $coinID - $symbol - $baseCurrency  RuleID: $ruleIDBuy <BR>",$nFunc,"BC27","",$logExitSettingAry,'Exit');
        continue;}
      if ($limitToBaseCurrency != "ALL" && $baseCurrency != $limitToBaseCurrency){
        SuperLog($nFile,"EXIT: Wrong BaseCurrency  $coinID - $symbol - $baseCurrency | $limitToBaseCurrency RuleID: $ruleIDBuy <BR>",$nFunc,"BC28","",$logExitSettingAry,'Exit');
        continue;}

      if ($limitToCoin != "ALL" && $symbol != $limitToCoin) {
        //Echo "<BR> EXIT: Limit to Coin: $userID $symbol $limitToCoin<BR>";
        SuperLog($nFile,"EXIT: Wrong Symbol for Rule  $coinID - $symbol - $baseCurrency  RuleID: $ruleIDBuy <BR>",$nFunc,"BC29","",$logExitSettingAry,'Exit');
        continue;}
      SuperLog($nFile,"Placeholder 5:  $coinID - $symbol - $baseCurrency  RuleID: $ruleIDBuy <BR>",$nFunc,"BC30","",$logFlowSettingAry,'Flow');
      if ($doNotBuy == 1){
        //Echo "<BR> EXIT: Do Not Buy<BR>";
        SuperLog($nFile,"EXIT: Do NOT Buy  $coinID - $symbol - $baseCurrency  RuleID: $ruleIDBuy <BR>",$nFunc,"BC31","",$logExitSettingAry,'Exit');
        continue;}
      if ($overrideDailyLimit == 0 && $EnableTotalBTCLimit == 1){
        SuperLog($nFile,"Check if over total limit! ",$nFunc,"BC32","",$logFlowSettingAry,'Flow');
        $userBTCSpent = getUserTotalBTC($totalBTCSpent,$userID,$baseCurrency);
        SuperLog($nFile,"Testing Testing Testing| $userID | : ".$totalBTCSpent[0][0],$nFunc,"BC33","",$logFlowSettingAry,'Flow');
          if ($userBTCSpent >= $TotalBTCLimit){ SuperLog($nFile,"EXIT: TOTAL BTC SPENT",$nFunc,"BC34","",$logExitSettingAry,'Exit'); continue;}else{  SuperLog($nFile,"Total Spend ".$userBTCSpent." Limit $TotalBTCLimit",$nFunc,"BC35","",$logExitSettingAry,'Exit');}
      }
      if ($overrideDailyLimit == 0 && $EnableDailyBTCLimit == 1){
        SuperLog($nFile,"Check if over daily limit! ",$nFunc,"BC36","",$logVariSettingAry,'Variables');
        $userDailyBTCSpent = getUserTotalBTC($dailyBTCSpent,$userID,$baseCurrency);
          if ($userDailyBTCSpent >= $DailyBTCLimit){SuperLog($nFile,"EXIT: DAILY BTC SPENT",$nFunc,"BC37","",$logExitSettingAry,'Exit');continue;}else{ SuperLog($nFile,"Daily Spend ".$userDailyBTCSpent." Limit $DailyBTCLimit",$nFunc,"BC38","",$logVariSettingAry,'Variables');}
      }

      SuperLog($nFile,"Placeholder 6:  $coinID - $symbol - $baseCurrency  RuleID: $ruleIDBuy <BR>",$nFunc,"BC38","",$logVariSettingAry,'Variables');
      if ($buyCounter[$userID."-".$coinID] >= 1 && $overrideDailyLimit == 0){ SuperLog($nFile,"EXIT: Buy Counter Met! $noOfBuys ".$buyCounter[$userID."-".$coinID],$nFunc,"BC39","",$logExitSettingAry,'Exit');//continue;
      }
      if ($buyCounter[$userID."-Total"] >= $noOfBuys && $overrideDailyLimit == 0){ SuperLog($nFile,"EXIT: Buy Counter Met! $noOfBuys ".$buyCounter[$userID."-Total"],$nFunc,"BC40","",$logExitSettingAry,'Exit');//continue;
      }
      if ($userActive == False){ SuperLog($nFile,"EXIT: User Not Active!",$nFunc,"BC41","",$logExitSettingAry,'Exit'); continue;}
      if ($hoursDisableUntil > 0){ SuperLog($nFile,"EXIT: Disabled until: ".$hoursDisableUntil,$nFunc,"BC42","",$logExitSettingAry,'Exit'); continue;}
      $LiveBTCPriceAry = bittrexCoinPriceNew('USDT','BTC');
      $LiveBTCPrice = $LiveBTCPriceAry[0][0];
      $newAutoBuyPrice = findAutoBuyPrice($autoBuyPrice,$coinID);
      $test1 = buyWithScore($MarketCapTop,$MarketCapBtm,$MarketCapPctChange,$MarketCapEnabled, $buyWithScore_MrktCap,'MarketCap');
      $buyResultAry[] = Array($test1, "Market Cap $symbol", $MarketCapPctChange);
      $test2 = buyWithScore($VolumeTop,$VolumeBtm,$VolumePctChange,$VolumeEnabled, $buyWithScore_Vol2,'Volume');
      $buyResultAry[] = Array($test2, "Volume $symbol", $VolumePctChange);
      $test3 = buyWithScore($BuyOrdersTop,$BuyOrdersBtm,$BuyOrdersPctChange,$BuyOrdersEnabled,$buyWithScore_BuyOrd3,'BuyOrders');
      $buyResultAry[] = Array($test3, "Buy Orders $symbol", $BuyOrdersPctChange);
      $test4 = buyWithScore($Hr1ChangeTop,$Hr1ChangeBtm,$Hr1ChangePctChange,$Hr1ChangeEnabled,$buyWithScore_1Hr4 ,'1HrChange');
      //echo "<BR> buyWithScore($Hr1ChangeTop,$Hr1ChangeBtm,$Hr1ChangePctChange,$Hr1ChangeEnabled);";
      $buyResultAry[] = Array($test4, "1 Hour Price Change $symbol", $Hr1ChangePctChange);
      $test5 = buyWithScore($Hr24ChangeTop,$Hr24ChangeBtm,$Hr24ChangePctChange,$Hr24ChangeEnabled,$buyWithScore_24Hr5,'24HrChange');
      $buyResultAry[] = Array($test5, "24 Hour Price Change $symbol", $Hr24ChangePctChange);
      $test6 = buyWithScore($D7ChangeTop,$D7ChangeBtm,$D7ChangePctChange,$D7ChangeEnabled,$buyWithScore_7D6 ,'7DayChange');
      $buyResultAry[] = Array($test6, "7 Day Price Change $symbol", $D7ChangePctChange);
      $test7 = buyWithScore($CoinPriceTop,$CoinPriceBtm,$CoinPricePctChange,$CoinPriceEnabled,$buyWithScore_CoinPrice7 ,'CoinPrice');
      $buyResultAry[] = Array($test7, "Coin Price $symbol", $CoinPricePctChange);
      $test8 = buyWithScore($SellOrdersTop,$SellOrdersBtm,$SellOrdersPctChange,$SellOrdersEnabled,$buyWithScore_SellOrd8,'SellOrders');
      $buyResultAry[] = Array($test8, "Sell Orders $symbol", $SellOrdersPctChange);
      //echo "<BR> NEW Buy with Pattern1 : $newPriceTrend,$coinPricePatternList,$priceTrendEnabled,$ruleIDBuy,0 | $coinID | $ruleIDBuy";
      if ($priceTrendEnabled){
        $test9 = newBuywithPattern($newPriceTrend,$coinPricePatternList,$priceTrendEnabled,$ruleIDBuy,0,1,"BuyPattern:9");
      }else{$test9 = True;}

      $buyResultAry[] = Array($test9, "Buy Price Pattern $symbol", $newPriceTrend);
      $test10 = buyWithMin($BuyPriceMinEnabled,$BuyPriceMin,$LiveCoinPrice,$BuyCoinBuyWithMin10,'BuyPrice10');
      SuperLog($nFile,"TEST 10: $BuyPriceMinEnabled | $BuyPriceMin | $LiveCoinPrice | $test10",$nFunc,"BC43","",$logVariSettingAry,'Variables');
      $buyResultAry[] = Array($test10, "Buy Price Minimum $symbol", $LiveCoinPrice);
      $test11 = autoBuyMain($LiveCoinPrice,$autoBuyPrice, $autoBuyCoinEnabled,$coinID,$autoBuyMain11,"Auto Buy Price:11");
      $buyResultAry[] = Array($test11, "Auto Buy Price $symbol", $LiveCoinPrice);
      $test12 = coinMatchPattern($coinPriceMatch,$LiveCoinPrice,$symbol,0,$coinPricePatternEnabled,$ruleIDBuy,0,1,"CoinMatchPattern:12");
      $buyResultAry[] = Array($test12, "Coin Price Pattern $symbol", $LiveCoinPrice);
      SuperLog($nFile,"checkPriceDipCoinFlat($priceDipCoinFlatEnabled,$priceDipHoursFlatTarget, $priceDipHours,$checkPriceDipCoinFlatArt);",$nFunc,"BC44","",$logVariSettingAry,'Variables');
      $test15 = checkPriceDipCoinFlat($priceDipCoinFlatEnabled,$priceDipHoursFlatTarget, $priceDipHours,$checkPriceDipCoinFlatArt,"checkPriceDipCoinFlat:15");
      $buyResultAry[] = Array($test15, "Coin Price Dip Coin Flat $symbol", $LiveCoinPrice);
      $test16 = buyWithMin($priceDipMinPriceEnabled, $finalPriceDipMinPrice, $LiveCoinPrice,$BuyCoinBuyWithMin10,'PriceDipMinPrice16');
      SuperLog($nFile,"buyWithMin($priceDipMinPriceEnabled, $finalPriceDipMinPrice, $LiveCoinPrice,$BuyCoinBuyWithMin10,'PriceDipMinPrice16');",$nFunc,"BC45","",$logVariSettingAry,'Variables');
      $buyResultAry[] = Array($test16, "Coin Price Dip Min Price $symbol", $LiveCoinPrice);
      if ($Hr1ChangeTrendEnabled){
        $test14 = newBuywithPattern($new1HrPriceChange,$coin1HrPatternList,$Hr1ChangeTrendEnabled,$ruleIDBuy,0,1,"CoinPattern:14");
      }else{$test14 = True;}
      $buyResultAry[] = Array($test14, "1 Hour Price Pattern $symbol", $new1HrPriceChange);
      $test13 = $GLOBALS['allDisabled'];
      if (buyAmountOverride($buyAmountOverrideEnabled)){$BTCAmount = $buyAmountOverride; SuperLog($nFile,"13: BuyAmountOverride set to : $buyAmountOverride | BTCAmount: $BTCAmount",$nFunc,"BC46","",$logVariSettingAry,'Variables');}
      $totalScore_Buy = $test1+$test2+$test3+$test4+$test5+$test6+$test7+$test8+$test9+$test10+$test11+$test12+$test13+$test14+$test15+$test16;
      if ($totalScore_Buy >= 15 ){
        $buyOutstanding = getOutStandingBuy($buyResultAry);
        //logAction("UserID: $userID | RuleID: $ruleIDBuy | Coin : $symbol | 1:  $test1  2:  $test2  3:  $test3  4:  $test4  5:  $test5  6:  $test6  7:  $test7  8:  $test8  9:  $test9  10:  $test10  11:  $test11  12:  $test12  13:  $test13  14:  $test14   15:  $test15   16:  $test16 TOTAL: $totalScore_Buy / 16 $buyOutstanding","BuyScore", $GLOBALS['logToFileSetting'] );
        SuperLog($nFile,"UserID: $userID | RuleID: $ruleIDBuy | Coin : $symbol| 1:$test1  2:$test2  3:$test3  4:$test4  5:$test5  6:$test6  7:$test7  8:$test8  9:$test9  10:$test10  11:$test11  12:$test12  13:$test13  14:$test14 15:$test15 16:$test16  TOTAL:$totalScore_Buy / 16",$nFunc,"BC47","",$logFlowSettingAry,'Flow');
      }else{
        if ($showLowScores == 1){
            SuperLog($nFile,"UserID: $userID | RuleID: $ruleIDBuy | Coin : $symbol| 1:$test1  2:$test2  3:$test3  4:$test4  5:$test5  6:$test6  7:$test7  8:$test8  9:$test9  10:$test10  11:$test11  12:$test12  13:$test13  14:$test14 15:$test15 16:$test16  TOTAL:$totalScore_Buy / 16",$nFunc,"BC48","",$logFlowSettingAry,'Flow');
        }
      }
      if ($test1 == True && $test2 == True && $test3 == True && $test4 == True && $test5 == True && $test6 == True && $test7 == True && $test8 == True && $test9 == True && $test10 == True &&
      $test11 == True && $test12 == True && $test13 == True && $test14 == True && $test15 == True && $test16 == True){
        $date = date("Y-m-d H:i:s", time());
        SuperLog($nFile,"Call Bittrex Bal: bittrexbalance($APIKey, $APISecret,$baseCurrency, $apiVersion);",$nFunc,"BC49","",$logFlowSettingAry,'Flow');
        $BTCBalance = bittrexbalance($APIKey, $APISecret,$baseCurrency, $apiVersion);
        $reservedAmount = getReservedAmount($baseCurrency,$userID);
        SuperLog($nFile,"TEST BAL AND RES: $BTCBalance ; $BTCAmount ; ".$reservedAmount[0][0]."| ",$nFunc,"BC50","",$logVariSettingAry,'Variables');//.$BTCBalance-$reservedAmount
        //echoText("TEST BAL AND RES: $BTCBalance ; $BTCAmount ; ".$reservedAmount[0][0]." | ",$echoTestText); //.$BTCBalance-$reservedAmount
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

        SuperLog($nFile,"$usdtReserved | $btcReserved | $ethReserved",$nFunc,"BC51","",$logFlowSettingAry,'Flow');
        $totalReserved = $usdtReserved+$btcReserved+$ethReserved;

        if ($baseCurrency == 'BTC'){
          $finalMultiplier = $baseMultiplier[0][0];
          SuperLog($nFile,"BTC Bal Test : $BTCBalance | $totalReserved | ".$finalMultiplier,$nFunc,"BC52","",$logVariSettingAry,'Variables');
          $totalBal = ($BTCBalance*$finalMultiplier)-$totalReserved;
          $buyQuantity = $BTCAmount / $finalMultiplier;
          SuperLog($nFile,"BaseCurrency is BTC : totalBal: $totalBal | BTC Bal: $BTCBalance | totalReserved: $totalReserved | Multiplier : ".$finalMultiplier,$nFunc,"BC53","RuleID:$ruleIDBuy CoinID:$coinID",$logVariSettingAry,'Variables');
        }elseif ($baseCurrency == 'ETH'){
          $finalMultiplier = $baseMultiplier[0][1];
          SuperLog($nFile,"ETH Bal Test : $BTCBalance | $totalReserved | ".$finalMultiplier,$nFunc,"BC54","",$logVariSettingAry,'Variables');
          $totalBal = ($BTCBalance * $finalMultiplier)-$totalReserved;
          $buyQuantity = $BTCAmount / $finalMultiplier;
          SuperLog($nFile,"BuyCoins","BaseCurrency is ETH : totalBal: $totalBal | Multiplier : ".$finalMultiplier,$nFunc,"BC55","RuleID:$ruleIDBuy CoinID:$coinID",1,'Variables');
        }else{
          $finalMultiplier = $baseMultiplier[0][2];
          SuperLog($nFile,"USDT Bal Test : $BTCBalance | $totalReserved ",$nFunc,"BC56","",$logVariSettingAry,'Variables');
          $totalBal = ($BTCBalance * $finalMultiplier)-$totalReserved;
          $buyQuantity = $BTCAmount / $finalMultiplier;
        }
        //newLogToSQL("BuyCoins"," $totalBal | $BTCAmount",3,$GLOBALS['logToSQLSetting'],"OneTimeBuyRuleTest","RuleID:$ruleIDBuy CoinID:$coinID");
        //Echo "<BR> Here is the Total: $totalBal Here is the BTC: $BTCAmount";
        SuperLog($nFile,"Here is the Total: $totalBal Here is the BTC: $BTCAmount",$nFunc,"BC57A","",$logVariSettingAry,'Variables');
        if ($totalBal > 15 OR $overrideCoinAlloc == 1) {
          if($BTCAmount <= 15 ){ SuperLog($nFile,"EXIT: BTC Amount less than 15!",$nFunc,"BC57","",$logExitSettingAry,'Exit');continue;}
          if ($ruleType == 'Normal'){
            SuperLog($nFile,"addTrackingCoin($coinID, $LiveCoinPrice, $userID, $baseCurrency, $SendEmail, $BuyCoin, $buyQuantity, $ruleIDBuy, $CoinSellOffsetPct, $CoinSellOffsetEnabled, $buyType, $timeToCancelBuyMins, $SellRuleFixed,0,0,$risesInPrice,'Buy',$LiveCoinPrice,0,0,$overrideCoinAlloc,'BuyCoins',0);",$nFunc,"BC58","",$logEventsSettingAry,'Events');
            addTrackingCoin($coinID, $LiveCoinPrice, $userID, $baseCurrency, $SendEmail, $BuyCoin, $buyQuantity, $ruleIDBuy, $CoinSellOffsetPct, $CoinSellOffsetEnabled, $buyType, $timeToCancelBuyMins, $SellRuleFixed,0,0,$risesInPrice,'Buy',$LiveCoinPrice,0,0,$overrideCoinAlloc,'BuyCoins',0);
            //newLogToSQL("addTrackingCoin","addTrackingCoin($coinID, $LiveCoinPrice, $userID, $baseCurrency, $SendEmail, $BuyCoin, $buyQuantity, $ruleIDBuy, $CoinSellOffsetPct, $CoinSellOffsetEnabled, $buyType, $timeToCancelBuyMins, $SellRuleFixed,0,0,$risesInPrice,'Buy',$LiveCoinPrice,0,0,$overrideCoinAlloc,'BuyCoins',0);",3,$buyCoinAddTracking,"NewBuySellCoins","RuleID:$ruleIDBuy CoinID:$coinID");
            //addWebUsage($userID,"Add","BuyTracking");
            $buyCounter[$userID."-".$coinID] = $buyCounter[$userID."-".$coinID] + 1;
            SuperLog($nFile,"Number of Coin Buys: 1 BuyCounter $userID $coinID | New Buy Counter: ".$buyCounter[$userID."-".$coinID],$nFunc,"BC59","",$logVariSettingAry,'Variables');
            $buyCounter[$userID."-Total"] = $buyCounter[$userID."-Total"] + 1;
            SuperLog($nFile,"Number of Total Buys: $noOfBuys BuyCounter: ".$buyCounter[$userID."-Total"],$nFunc,"BC60","",$logVariSettingAry,'Variables');
          }else{
            $spreadBetRuleID =  $coins[$x][52]; $spreadBetPerCoinAmount = $buyRules[$y][93];
            $spreadBetCoins = getSpreadbetCoins($baseCurrency,$ruleIDBuy);
            $spreadBetCoinsSize = count($spreadBetCoins);
            $totalBuyAmount = 0;
            SuperLog($nFile,"<BR> Size: $spreadBetCoinsSize | PerCoin: $spreadBetPerCoinAmount",$nFunc,"BC61","",$logFlowSettingAry,'Variables'); //$logVariSettingAry
            for ($l=0;$l<$spreadBetCoinsSize;$l++){
              $LiveCoinPrice = $spreadBetCoins[$l][2];$coinID = $spreadBetCoins[$l][1];$spreadBetTransID = $spreadBetCoins[$l][4];
              $risesInPrice = $spreadBetCoins[$l][5];
              //$totalBuyAmount = $totalBuyAmount + $buyQuantity;

              if ($totalBuyAmount < $buyQuantity){
                addTrackingCoin($coinID, $LiveCoinPrice, $userID, $baseCurrency, $SendEmail, 1, $spreadBetPerCoinAmount, $ruleIDBuy, $CoinSellOffsetPct, $CoinSellOffsetEnabled, $buyType, $timeToCancelBuyMins, $SellRuleFixed,0,0,$risesInPrice,'SpreadBuy',$LiveCoinPrice,$spreadBetTransID,$spreadBetRuleID,$overrideCoinAlloc,'SpreadBuyCoins',0);
                SuperLog($nFile,"addTrackingCoin($coinID, $LiveCoinPrice, $userID, $baseCurrency, $SendEmail, 1, $spreadBetPerCoinAmount, $ruleIDBuy, $CoinSellOffsetPct, $CoinSellOffsetEnabled, $buyType, $timeToCancelBuyMins, $SellRuleFixed,0,0,$risesInPrice,'SpreadBuy',$LiveCoinPrice,$spreadBetTransID,$spreadBetRuleID,$overrideCoinAlloc,'SpreadBuyCoins',0);",$nFunc,"BC62","",$logEventsSettingAry,'Events');
                //$totalBuyAmount = $totalBuyAmount + $buyQuantity;
                $totalBuyAmount = $totalBuyAmount + $spreadBetPerCoinAmount;
              }else{
                SuperLog($nFile,"TotalAmout over: $buyQuantity | $totalBuyAmount ",$nFunc,"BC63","",$logFlowSettingAry,'Flow');
                pauseRule($ruleIDBuy,4, $userID);
                return True;
              }
            }
          }


          //if ($oneTimeBuy == 1){ disableBuyRule($ruleIDBuy);}
          //logAction("runBuyCoins; addTrackingCoin : $symbol | $coinID | $LiveCoinPrice | $buyQuantity | $userID | $baseCurrency $timeToCancelBuyMins | $risesInPrice | $overrideCoinAlloc", 'BuySellFlow', 1);
          $finalBool = True;
        }else{ SuperLog($nFile,"EXIT: $totalBal Less than 20 | $totalBal",$nFunc,"BC64","",$logExitSettingAry,'Exit');}
      }else{
        if ($limitToCoin != "ALL"){ SuperLog($nFile,"LimitToCoin Found: Continue Rules!",$nFunc,"BC65","",$logFlowSettingAry,'Flow'); continue 2;}
      }

      SuperLog($nFile,"NEXT RULE <BR>",$nFunc,"BC66","",$logFlowSettingAry,'Flow');
    }//Rule Loop
    echo "</blockquote>";
  }//Coin Loop
  echo "</blockquote>";
  return $finalBool;
}


function runSellCoins($sellRules,$sellCoins,$userProfit,$coinPriceMatch,$coinPricePatternList,$coin1HrPatternList,$autoBuyPrice,$webSettingsAry,$csp,$ruleType,$multiSellRules,$newWebSettingsAry){
  $finalBool = False; $apiVersion = 3;
  $nFile = "BuySellCoins"; $nFunc = "SellCoins";
  $tempSettings = getSetting($newWebSettingsAry,$nFile,$nFunc);
  $logFlowSettingAry = $tempSettings[0]; $logVariSettingAry = $tempSettings[1]; $logSQLSettingAry = $tempSettings[2]; $logExitSettingAry = $tempSettings[3]; $logAPISettingAry = $tempSettings[4]; $logEventsSettingAry = $tempSettings[5];
  $sellRulesSize = count($sellRules);
  $sellCoinsLength = count($sellCoins);
  //$echoExitText = 0;
  //$echoProgramFlow = 1;
  //$echoTestText = 0;
  $echoExitText = $webSettingsAry[2][1];
  //$echoProgramFlow = 1;
  $echoProgramFlow = $webSettingsAry[0][1];
  //$echoTestText = 0;
  $echoTestText = $webSettingsAry[1][1];
  $checkPriceDipCoinFlatArt = $webSettingsAry[10][1];
  $scoreSettingsAlert = $webSettingsAry[23][1];
  $cspAlert = $webSettingsAry[24][1];
  echoText("SellCoin Key: ",$echoProgramFlow);
  echoText("1: MarketCap | 2: Volume | 3: SellOrders | 4: 1HrPctChange | 5: 24HrPctChange  ",$echoProgramFlow);
  echoText("6: 7DPctChange | 7: PriceTrendPattern | 8: MinPrice | 9: ProfitPct | 10: CoinPrice ",$echoProgramFlow);
  echoText("11: CoinPriceMatch | 12: GlobalAllDisabled | 13: AutoSellPrice | 14: CoinPriceFlat | 15: PriceDipMaxPrice ",$echoProgramFlow);
  echoText("16: HoursPastSell <BR>",$echoProgramFlow);
  echoText("HERE! $sellCoinsLength",$echoTestText);
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
    $sellPctCsp = $sellCoins[$a][67];$maxHoursFlat = $sellCoins[$a][68]; $topPriceExtra = $sellCoins[$a][69]; $bottomPriceExtra = $sellCoins[$a][70];
    $caaOffset = $sellCoins[$a][73]; $caaMinsToCancelSell = $sellCoins[$a][72]; //$profit = $sellCoins[$a][58];
    $sellStatus = $sellCoins[$a][6];
    $ogPriceBuy = $sellCoins[$a][54]; $livePriceSell = $sellCoins[$a][56]; $feeSell = $sellCoins[$a][55];

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
      $calculatedSellPctReduction = $sellRules[$z][66];
      $bypassTrackingSell = $sellRules[$z][65]; $pctOfAuto = $sellRules[$z][67]; $overrideBBAmount = $sellRules[$z][68]; $overrideBBSaving = $sellRules[$z][69];
      $hoursAfterPurchaseToStart = $sellRules[$z][70]; $hoursAfterPurchaseToEnd = $sellRules[$z][71]; $sellRuleType = $sellRules[$z][72];
      if ($sellRuleType != $ruleType){ continue;}
      if ($sellRuleType == 'Normal'){
        $profit = (($livePriceSell- $ogPriceBuy-$feeSell)/$ogPriceBuy)*100;
      }else{
        $profitWithSold = $sellCoins[$a][75];
        $profit = (($profitWithSold - $ogPriceBuy - $feeSell)/$ogPriceBuy)*100;
      }
      if ($hoursAfterPurchaseToStart > $hoursSinceBuy){ echoText("Exit Hours! $coin | $transactionID | $hoursAfterPurchaseToStart | $hoursSinceBuy",$echoExitText); continue;}
      if ($hoursAfterPurchaseToEnd < $hoursSinceBuy){ echoText("Exit Hours! $coin | $transactionID | $hoursAfterPurchaseToEnd | $hoursSinceBuy",$echoExitText); continue;}
      if ($sellCoinOffsetEnabled == 2){
        $sellCoinOffsetEnabled = 1;
        if (!is_null($caaOffset)){
          $sellCoinOffsetPct = $caaOffset;
        }
      }
      if ($ProfitPctEnabled == 2){
        $ProfitPctEnabled = 1;
        if (!is_null($caaOffset)){
          $ProfitPctBtm_Sell = $caaOffset;
        }
      }
      if ($calculatedSellPctEnable == 1){
        //$ProfitPctTop_Sell_Original = $ProfitPctTop_Sell;
        //$ProfitPctTop_Sell = $calculatedSellPctStart - ($hoursSinceBuy * ($calculatedSellPctStart-$calculatedSellPctEnd)/$calculatedSellPctDays);
        //if ($ProfitPctTop_Sell < $ProfitPctTop_Sell_Original){ $ProfitPctTop_Sell = $ProfitPctTop_Sell_Original;}
        $ProfitPctTop_Sell = 999.99;
        $ProfitPctBtm_Sell_Original = $ProfitPctBtm_Sell;
        Echo "<BR> CALCULATED SELL PCT: $sellPctCsp";
        //if (!isset($sellPctCsp)){
        //  echoText("Calculated Sell Pct: Its NOT set!! $sellPctCsp",$echoTestText);
        //  $ProfitPctBtm_Sell = $calculatedSellPctStart;
        //}else{
        //  echoText("Calculated Sell Pct: Its set!! $sellPctCsp - $calculatedSellPctEnd",$echoTestText);
        //if ($sellStatus == 'Closed' OR $sellStatus == 'Sold'){ continue; }
        //}
        $cspSize = count($csp);
        for ($y=0;$y<$cspSize;$y++){
          $cspTransID = $csp[$y][1]; $CspRuleID = $csp[$y][5];
          if ($cspTransID ==  $transactionID AND $CspRuleID == $ruleIDSell){
            $sellPctCsp = $csp[$y][2];
            if (!isset($sellPctCsp)){
              //$ProfitPctBtm_Sell = $calculatedSellPctStart;
            }else{
              if ($ProfitPctEnabled == 1 and $ProfitPctBtm_Sell_Original < $calculatedSellPctEnd){
                //$calculatedSellPctEnd = $ProfitPctBtm_Sell_Original;
              }
              //$ProfitPctBtm_Sell = $sellPctCsp - $calculatedSellPctEnd;
              //$amountToReduce = abs($ProfitPctBtm_Sell -($ProfitPctBtm_Sell/100)*$calculatedSellPctReduction);

              //$ProfitPctBtm_Sell = ($calculatedSellPctEnd + $amountToReduce);
            }
          }
        }
        if ($multiSellRuleEnabled == 0){

        }

        //echoText("writeCalculatedSellPct($transactionID,$sellCoinsUserID,$ProfitPctBtm_Sell);",$echoTestText);
        //echoText("Calculated Sell Pct Enabled:  $ProfitPctBtm_Sell | $ProfitPctTop_Sell | $ProfitPctBtm_Sell_Original | $calculatedSellPctStart | $hoursSinceBuy | $calculatedSellPctEnd | $calculatedSellPctDays",$echoTestText);

      }
      if ($priceDipCoinFlatEnabled == 2){
        $priceDipHoursFlatTarget = Floor(($maxHoursFlat/100)*($pctOfAuto/2));
        $priceDipCoinFlatEnabled = 1;
      }
      if ($sellPriceMinEnabled == 2){
        $sellPriceMin = $topPriceExtra;
        $sellPriceMinEnabled = 1;
      }
      $profitNum = findUserProfit($userProfit,$userID);
      $coinSwapEnabled = $sellRules[$z][50]; $coinSwapAmount = $sellRules[$z][51]; $noOfCoinSwapsPerWeek = $sellRules[$z][52];
      echoText("Starting: $coin | $ruleIDSell",$echoTestText);
      if ($sellAllCoinsEnabled == 1 and $profitNum <= $sellAllCoinsPct){assignNewSellID($transactionID, 25);}//else{Echo "<BR> HERE3!";}
      if ($limitToBuyRule == "ALL"){ $limitToBuyRuleEnabled = 0;}else{$limitToBuyRuleEnabled = 1;}
      echoText("PlaceHolder: 1 | $coin-$BaseCurrency",$echoTestText);

      echoText("PlaceHolder: 2 | $coin",$echoTestText);
      if (!Empty($KEKSell)){ $apisecret = Decrypt($KEKSell,$sellRules[$z][34]);}//else{Echo "<BR> HERE5!";}
      $LiveBTCPriceAry = bittrexCoinPriceNew($BaseCurrency,$coin);
      $LiveBTCPrice = $LiveBTCPriceAry[0][0];
      $limitToCoinSell = $sellRules[$z][39];
      $buyPrice = ($cost * $amount);
      $sellPrice = ($LiveCoinPrice * $amount);
      $fee = (($LiveCoinPrice * $amount)/100)*0.25;
      //$profit = ((($sellPrice-$fee)-$buyPrice)/$buyPrice)*100;
      echo "<BR> PROFIT:$profit ((($sellPrice-$fee)-$buyPrice)/$buyPrice)*100; | SellPrice: $LiveCoinPrice * $amount | BuyPrice: ($cost * $amount); | Fee:(($LiveCoinPrice * $amount)/100)*0.25;";
      echoAndLog("","PlaceHolder: 3",3,$echoTestText,"","");
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
      echoAndLog("","PlaceHolder: 4 | $coin ",3,$echoTestText,"","");
      if ($userID != $sellCoinsUserID){ continue; } //echo "Exit: No3 | $coin | $userID | $BuyRule";
      if ($limitToCoinSell != "ALL" && $coin != $limitToCoinSell) { echoAndLog("","Exit: No4 | $coin | $userID | $ruleIDSell | $limitToCoinSell",3,$echoExitText,"","");continue;}
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
          echoAndLog("","COIN SWAP: No of coins in Buy Mode: $coinID | $coinSwapBuyCoinIDSize",3,$echoTestText,"","");
          if ($coinSwapBuyCoinIDSize > 0){
            //CoinSwap
            echoAndLog("","coinSwapSell($LiveCoinPrice, $transactionID,$coinID,$BuyRule,$coinSwapAmount);",3,$echoTestText,"","");
            coinSwapSell($LiveCoinPrice, $transactionID,$coinID,$BuyRule,$coinSwapAmount);
          }
        }
      }
      if ($multiSellRuleEnabled == 1){

          $multiSellResult = checkMultiSellRules($ruleIDSell,$multiSellRules,$transactionID);
          //echo "<BR> PlaceHolder: 1A Checking MultiSell";
          $multiSellRulesSize = count($multiSellRules);

          if ($multiSellResult == False){
            echoText("Exit: No1 | $coin | $userID | $ruleIDSell | $multiSellResult",$echoExitText); continue;
          } else{
            echoText("FoundSellRule: $coin | $userID | $ruleIDSell | $multiSellResult",$echoTestText);
            //for ($a=0;$a<$multiSellRulesSize;$a++){
              writeCalculatedSellPct($transactionID,$sellCoinsUserID,$ProfitPctBtm_Sell,$ruleIDSell,$calculatedSellPctReduction);
              echoAndLog("CalculatedSellPrice", "writeCalculatedSellPct($transactionID,$sellCoinsUserID,$ProfitPctBtm_Sell,$ruleIDSell,$calculatedSellPctReduction);$coin",3,$cspAlert,"","");
            //}
          }
      }else{
          if ($fixSellRule != "ALL" && (int)$fixSellRule != $ruleIDSell){echoText("Exit: No2 Wrong Sell Rule | $coin | $userID | $ruleIDSell |",$echoExitText);continue;}//else{Echo "<BR> HERE4!";}  //echo "Exit: No2 | $coin | $userID | $BuyRule";
          writeCalculatedSellPct($transactionID,$sellCoinsUserID,$ProfitPctBtm_Sell,$ruleIDSell,$calculatedSellPctReduction);
          echoAndLog("CalculatedSellPrice", "A_writeCalculatedSellPct($transactionID,$sellCoinsUserID,$ProfitPctBtm_Sell,$ruleIDSell);",3,$cspAlert,"","");
      }
      $finalHoursFlat = ($priceDipHoursFlatTarget/100)*(100 - $profit);
      echoAndLog("","Checking:  $coin | $userID | $ruleIDSell",3,$echoTestText,"","");
      $GLOBALS['allDisabled'] = false;
      $sTest12 = false;

      $sTest1 = sellWithScore($MarketCapTop,$MarketCapBtm,$MarketCapPctChange,$MarketCapEnabled,$scoreSettingsAlert,"MarketCap:1");
      $sellResultAry[] = Array($sTest1, "Market Cap $coin", $MarketCapPctChange);
      $sTest2 = sellWithScore($VolumeTop,$VolumeBtm,$VolumePctChange,$VolumeEnabled,$scoreSettingsAlert,"Volume:2");
      $sellResultAry[] = Array($sTest2, "Volume $coin", $VolumePctChange);
      $sTest3 = sellWithScore($SellOrdersTop,$SellOrdersBtm,$SellOrdersPctChange,$SellOrdersEnabled,$scoreSettingsAlert,"SellOrders:3");
      $sellResultAry[] = Array($sTest3, "Sell Orders $coin", $SellOrdersPctChange);
      $sTest4 = sellWithScore($Hr1ChangeTop,$Hr1ChangeBtm,$Hr1ChangePctChange,$Hr1ChangeEnabled,$scoreSettingsAlert,"1HrPct:4");
      $sellResultAry[] = Array($sTest4, "1 Hour Price Change $coin", $Hr1ChangePctChange);
      $sTest5 = sellWithScore($Hr24ChangeTop,$Hr24ChangeBtm,$Hr24ChangePctChange,$Hr24ChangeEnabled,$scoreSettingsAlert,"24HrPct:5");
      $sellResultAry[] = Array($sTest5, "24 Hour Price Change $coin", $Hr24ChangePctChange);
      $sTest6 = sellWithScore($D7ChangeTop,$D7ChangeBtm,$D7ChangePctChange,$D7ChangeEnabled,$scoreSettingsAlert,"7DPct:6");
      $sellResultAry[] = Array($sTest6, "7 Day Price Change $coin", $D7ChangePctChange);
      if ($priceTrendEnabled){
          $sTest7 = newBuywithPattern($price4Trend.$price3Trend.$lastPriceTrend.$livePriceTrend,$coinPricePatternList,$priceTrendEnabled,$ruleIDSell,1,$scoreSettingsAlert,"Pattern:7");
      }else{ $sTest7 = True;}
      $sellResultAry[] = Array($sTest7, "Price Trend Pattern $coin", $price4Trend.$price3Trend.$lastPriceTrend.$livePriceTrend);
      $sTest8 = sellWithMin($sellPriceMinEnabled,$sellPriceMin,$LiveCoinPrice,$LiveBTCPrice,$scoreSettingsAlert,"SellPrice:8");
      $sellResultAry[] = Array($sTest8, "Minimum Price $coin", $LiveCoinPrice);
      $sTest9 = sellWithScore($ProfitPctTop_Sell,$ProfitPctBtm_Sell,$profit,$ProfitPctEnabled,$scoreSettingsAlert,"Profit:9");
      $sellResultAry[] = Array($sTest9, "Profit Percentage $coin", $profit);
      $sTest10 = sellWithScore($CoinPriceTop,$CoinPriceBtm,$CoinPricePctChange,$CoinPriceEnabled,$scoreSettingsAlert,"CoinPrice:10");
      $sellResultAry[] = Array($sTest10, "Minimum Sell Price $coin", $CoinPricePctChange);
      $sTest11 = coinMatchPattern($coinPriceMatch,$LiveCoinPrice,$coin,1,$coinPricePatternSellEnabled,$ruleIDSell,1,$scoreSettingsAlert,"CoinMatch:11");
      $sellResultAry[] = Array($sTest11, "Coin Price Match $coin", $LiveCoinPrice);
      $sTest13 = autoSellMain($LiveCoinPrice,$autoBuyPrice,$autoSellCoinEnabled,$coinID,$scoreSettingsAlert,"AutoSellPrice:13");
      $sellResultAry[] = Array($sTest12, "Auto Sell $coin", $LiveCoinPrice);
      $sTest12 = $GLOBALS['allDisabled'];
      $sTest14 = checkPriceDipCoinFlat($priceDipCoinFlatEnabled,$finalHoursFlat, $priceDipHours,$scoreSettingsAlert,"HoursFlat:14");
      $sellResultAry[] = Array($sTest14, "Coin Price Match $coin", $LiveCoinPrice);
      $sTest15 = sellWithMin($priceDipMaxPriceEnabled,$finalPriceDipMaxPrice,$LiveCoinPrice,$LiveBTCPrice,$scoreSettingsAlert,"DipPrice:15");
      $sellResultAry[] = Array($sTest15, "Coin Price Match $coin", $LiveCoinPrice);
      $sTest16 = sellWithMin($hoursPastBuySellEnable,$hoursPastBuy,$hoursSinceBuy,$hoursSinceBuy,$scoreSettingsAlert,"HoursPastSell:16");
      $sellResultAry[] = Array($sTest16, "Hours Since Buy $coin", $hoursSinceBuy);
      //Echo "<BR> TEST: sellWithScore($ProfitPctTop_Sell,$ProfitPctBtm_Sell,$profit,$ProfitPctEnabled);";
      //$sellOutstanding = getOutStandingBuy($sellResultAry);
      $totalScore_Sell = $sTest1+$sTest2+$sTest3+$sTest4+$sTest5+$sTest6+$sTest7+$sTest8+$sTest9+$sTest10+$sTest11+$sTest12+$sTest13+$sTest14+$sTest15+$sTest16;
      echoAndLog("","UserID: $userID | RuleID: $ruleIDSell | Coin : $coin | 1:$sTest1  2:$sTest2  3:$sTest3  4:$sTest4  5:$sTest5  6:$sTest6  7:$sTest7  8:$sTest8 ",3,$echoProgramFlow,"","");
      echoAndLog("","9:$sTest9  10:$sTest10  11:$sTest11  12:$sTest12 13:$sTest13 14:$sTest14 ($priceDipHours/$priceDipHoursFlatTarget) 15:$sTest15 16:$sTest16 TOTAL:$totalScore_Sell / 16, PROFIT:$profit MinsFromBuy:$minsFromBuy",3,$echoProgramFlow,"","");
      if ($totalScore_Sell >= 15){
        $sellOutstanding = getOutStandingBuy($sellResultAry);
        //logAction("UserID: $userID | RuleID: $ruleIDSell | Coin : $coin | 1:  $sTest1  2:  $sTest2  3:  $sTest3  4:  $sTest4  5:  $sTest5  6:  $sTest6  7:  $sTest7  8:  $sTest8","SellScore", $GLOBALS['logToFileSetting'] );
        //logAction("9:  $sTest9  10:  $sTest10  11:  $sTest11  12:  $sTest12 13: $sTest13 14: $sTest14 15: $sTest15 16: $sTest16 TOTAL:  $totalScore_Sell / 16, PROFIT: $profit $sellOutstanding","SellScore", $GLOBALS['logToFileSetting'] );
        //logToSQL("SellCoins", "RuleID: $ruleIDSell | Coin : $coin | TOTAL: $totalScore_Sell $sellOutstanding", $userID, $GLOBALS['logToSQLSetting']);
      }


      if ($sTest1 == True && $sTest2 == True && $sTest3 == True && $sTest4 == True && $sTest5 == True && $sTest6 == True && $sTest7 == True && $sTest8 == True && $sTest9 == True && $sTest10 == True
      && $sTest11 == True && $sTest12 == True && $sTest13 == True  && $sTest14 == True && $sTest15 == True && $sTest16 == True && $minsFromBuy > 25){
        $date = date("Y-m-d H:i:s", time());
        echoAndLog("","Sell Coins: $APIKey, $apisecret,$coin, $Email, $userID, 0,$date, $BaseCurrency,$SendEmail,$SellCoin, _.$ruleIDSell,$UserName,$orderNo,$amount,$cost,$transactionID,$coinID<BR>",3,$echoProgramFlow,"","");
        //sellCoins($apikey, $apisecret, $coin, $email, $userID, $score, $date,$baseCurrency, $sendEmail, $sellCoin, $ruleID,$userName, $orderNo,$amount,$cost,$transactionID,$coinID){
        //sellCoins($APIKey, $APISecret,$coin, $Email, $userID, 0,$date, $BaseCurrency,$SendEmail,$SellCoin, $ruleIDSell,$UserName,$orderNo,$amount,$cost,$transactionID,$coinID,$sellCoinOffsetEnabled,$sellCoinOffsetPct,$LiveCoinPrice);
        $newType = 'Sell';
        echoAndLog("SellCoins", "SellRule:$ruleIDSell", $userID, 1,"SellRule","TransactionID:$transactionID");
        if ($bypassTrackingSell == 1){
          $newType = 'SellBypass';
        }
        if ($sellRuleType == 'Normal' OR $sellRuleType == 'SpreadSellInd'){
          newTrackingSellCoins($LiveCoinPrice,$userID, $transactionID,$SellCoin, $SendEmail,$sellCoinOffsetEnabled,$sellCoinOffsetPct,$fallsInPrice,$newType,'RunSellCoins',0);
          setTransactionPending($transactionID);
          //echoAndLog("SellCoinResults1","UserID: $userID | RuleID: $ruleIDSell | Coin : $coin | 1:$sTest1  2:$sTest2  3:$sTest3  4:$sTest4  5:$sTest5  6:$sTest6  7:$sTest7  8:$sTest8 ",3,1,"AddTrackingSellCoin","TransactionID:$transactionID");
          SuperLog($nFile,"UserID: $userID | RuleID: $ruleIDSell | Coin : $coin | 1:$sTest1  2:$sTest2  3:$sTest3  4:$sTest4  5:$sTest5  6:$sTest6  7:$sTest7  8:$sTest8 ",$nFunc,"SC1","",$logFlowSettingAry,'Flow');
          //echoAndLog("SellCoinResults2","9:$sTest9  10:$sTest10  11:$sTest11  12:$sTest12 13:$sTest13 14:$sTest14 ($priceDipHours/$priceDipHoursFlatTarget) 15:$sTest15 16:$sTest16 TOTAL:$totalScore_Sell / 16, PROFIT:$profit MinsFromBuy:$minsFromBuy",3,1,"AddTrackingSellCoin","TransactionID:$transactionID");
          SuperLog($nFile,"9:$sTest9  10:$sTest10  11:$sTest11  12:$sTest12 13:$sTest13 14:$sTest14 ($priceDipHours/$priceDipHoursFlatTarget) 15:$sTest15 16:$sTest16 TOTAL:$totalScore_Sell / 16, PROFIT:$profit MinsFromBuy:$minsFromBuy",$nFunc,"SC2","",$logFlowSettingAry,'Flow');
          //echoAndLog("SellCoins", "newTrackingSellCoins($LiveCoinPrice,$userID, $transactionID,$SellCoin, $SendEmail,$sellCoinOffsetEnabled,$sellCoinOffsetPct,$fallsInPrice,'Sell');$profit | $ruleIDSell | $multiSellResult SellRule:$ruleIDSell", $userID, 1,"AddTrackingSellCoin","TransactionID:$transactionID");
          SuperLog($nFile,"newTrackingSellCoins($LiveCoinPrice,$userID, $transactionID,$SellCoin, $SendEmail,$sellCoinOffsetEnabled,$sellCoinOffsetPct,$fallsInPrice,'Sell');$profit | $ruleIDSell | $multiSellResult SellRule:$ruleIDSell",$nFunc,"SC3","",$logFlowSettingAry,'Flow');
          setTransactionPending($transactionID);
        }else{
          $spreadBetTransactionID = $sellCoins[$a][74];
          $spreadBetToSell = getSpreadBetSellCoins($spreadBetTransactionID);
          $spreadBetToSellSize = count($spreadBetToSell);
          echoAndLog("SellCoinsSpreadBet", "getSpreadBetSellCoins($spreadBetTransactionID); $spreadBetToSellSize", $userID, 0,"AddTrackingSellCoin","TransactionID:$transactionID");
          for ($a=0;$a<$spreadBetToSellSize;$a++){
            $LiveCoinPrice = $spreadBetToSell[$a][0]; $transactionID = $spreadBetToSell[$a][1]; $coinID = $spreadBetToSell[$a][3];
            newTrackingSellCoins($LiveCoinPrice,$userID, $transactionID,$SellCoin, $SendEmail,$sellCoinOffsetEnabled,$sellCoinOffsetPct,$fallsInPrice,$newType,'RunSellCoins',1);
            echoAndLog("SellCoinsSpreadBet", "newTrackingSellCoins($LiveCoinPrice,$userID, $transactionID,$SellCoin, $SendEmail,$sellCoinOffsetEnabled,$sellCoinOffsetPct,$fallsInPrice,'Sell');$profit | $ruleIDSell | $multiSellResult SellRule:$ruleIDSell", $userID, 1,"AddTrackingSellCoin","TransactionID:$transactionID");
            setTransactionPending($transactionID);
          }
          cancelReduceLoss($userID,$BaseCurrency);
        }
        $to_time = date("Y-m-d H:i:s", time());
        $from_time = strtotime($orderDate);
        $holdingMins = round(abs($to_time - $from_time) / 60,2);
        logHoldingTimeToSQL($coinID, $holdingMins);
        $finalBool = True;

      }
      echoAndLog("","NEXT RULE <BR>",3,$echoProgramFlow,"","");
    } //Sell Rules
    //$BTCBalance = bittrexbalance($APIKey, $apisecret,$BaseCurrency, $apiVersion);
    $buyPrice = ($cost * $amount);
    $sellPrice = ($LiveCoinPrice * $amount);
    $fee = (($LiveCoinPrice * $amount)/100)*0.25;
    $profit = ((($sellPrice-$fee)-$buyPrice)/$buyPrice)*100;

  }//Sell Coin Loop
  return $finalBool;
}

function runBittrex($BittrexReqs,$apiVersion,$webSettingsAry){
  $finalBool = False;
  $CancelBittrexLogFlag = $webSettingsAry[3][1];
  $CancelBittrexPartial = $webSettingsAry[7][1];
  $CancelBittrexBuyComplete = $webSettingsAry[8][1];
  $BittrexReqsSize = count($BittrexReqs);
  sleep(1);
  for($b = 0; $b < $BittrexReqsSize; $b++) {
    $finalBool = False;
    //Variables
    $type = $BittrexReqs[$b][0]; $uuid = $BittrexReqs[$b][1]; $date = $BittrexReqs[$b][2]; $status = $BittrexReqs[$b][4];   $bitPrice = $BittrexReqs[$b][5]; $userName = $BittrexReqs[$b][6];
    $apiKey = $BittrexReqs[$b][7]; $apiSecret = $BittrexReqs[$b][8]; $coin = $BittrexReqs[$b][9];$amount = $BittrexReqs[$b][10];$cost = $BittrexReqs[$b][11];$userID = $BittrexReqs[$b][12];
    $email = $BittrexReqs[$b][13]; $orderNo = $BittrexReqs[$b][14];$transactionID = $BittrexReqs[$b][15]; $totalScore = 0; $baseCurrency = $BittrexReqs[$b][16]; $ruleIDBTBuy = $BittrexReqs[$b][17];
    $sendEmail = 1; $daysOutstanding = $BittrexReqs[$b][18]; $timeSinceAction = $BittrexReqs[$b][19]; $coinID = $BittrexReqs[$b][20]; $ruleIDBTSell = $BittrexReqs[$b][21]; $orderDate = $BittrexReqs[$b][28];
    $liveCoinPriceBit = $BittrexReqs[$b][22]; $buyCancelTime = substr($BittrexReqs[$b][23],0,strlen($BittrexReqs[$b][23])-1); $sellFlag = false; $spreadBetRuleID = $BittrexReqs[$b][30];
    $spreadBetTransactionID  = $BittrexReqs[$b][31]; $redirectPurchasesToSpread = $BittrexReqs[$b][32]; $spreadBetIDRedirect = $BittrexReqs[$b][33];
    $coinModeRule = $BittrexReqs[$b][27]; $pctToSave = $BittrexReqs[$b][29]; $minsToPause = $BittrexReqs[$b][34]; $originalAmount = $BittrexReqs[$b][35]; $saveResidualCoins = $BittrexReqs[$b][36];
    $KEK = $BittrexReqs[$b][25]; $Day7Change = $BittrexReqs[$b][26]; $minsSinceAction = $BittrexReqs[$b][37]; $timeToCancelMins = $BittrexReqs[$b][57]; $buyBack = $BittrexReqs[$b][39];
    $oldBuyBackTransID = $BittrexReqs[$b][40]; $newResidualAmount = $BittrexReqs[$b][41]; $mergeSavingwithPurchase = $BittrexReqs[$b][42]; $buyBackEnabled = $BittrexReqs[$b][43];
    $pauseCoinIDAfterPurchaseEnabled  = $BittrexReqs[$b][45]; $daysToPauseCoinIDAfterPurchase = $BittrexReqs[$b][46]; $btc_Price = $BittrexReqs[$b][47]; $eth_Price = $BittrexReqs[$b][48];
    $multiSellRuleEnabled = $BittrexReqs[$b][49]; $multiSellRuleTemplateID = $BittrexReqs[$b][50]; $stopBuyBack = $BittrexReqs[$b][51]; $multiSellRuleID = $BittrexReqs[$b][52]; $typeBA = $BittrexReqs[$b][53];
    $reduceLossBuy = $BittrexReqs[$b][54]; $BittrexID = $BittrexReqs[$b][55]; $minsRemaining = $BittrexReqs[$b][58]; $lowMarketMode = $BittrexReqs[$b][59]; $holdCoinForBuyOut = $BittrexReqs[$b][60];
    $coinForBuyOutPct = $BittrexReqs[$b][61]; $holdingAmount = $BittrexReqs[$b][62]; $noOfPurchases = $BittrexReqs[$b][63]; $hr1PriceMovePct = $BittrexReqs[$b][64]; $pctToCancelBittrexAction = $BittrexReqs[$b][65];
    $pctFromSale = $BittrexReqs[$b][66]; $liveProfitPct = $BittrexReqs[$b][67]; $oneTimeBuy = $BittrexReqs[$b][68];  $timeToCancel = $BittrexReqs[$b][70];
    $overrideBittrexCancellation = $BittrexReqs[$b][71]; $currentTime = $BittrexReqs[$b][73]; $dateAdd = $BittrexReqs[$b][69]; $actionMins = $BittrexReqs[$b][74];
    $overrideBBAmount = $BittrexReqs[$b][75];$overrideBBSaving = $BittrexReqs[$b][76]; $overrideBBAmountSR = $BittrexReqs[$b][77]; $overrideBBSavingSR  = $BittrexReqs[$b][78];
    $bbminsToCancel = $BittrexReqs[$b][79]; $timeToCancelBa = $BittrexReqs[$b][80]; $timeStampNow = $BittrexReqs[$b][81]; $timeStampTimeToCancel = $BittrexReqs[$b][82];
    $buyBackMax = $BittrexReqs[$b][84]; $buyBackCounter = $BittrexReqs[$b][83]; $disableBuyBack = $BittrexReqs[$b][85];
    //$cancelTimeCheck = $BittrexReqs[$b][69];
    //$sqlDate = Date("Y-m-d H:i",$date);
    //$stringToTime = strtotime("+ $timeToCancelMins Minutes", $sqlDate);
    //$finalTimeToCancel = date("Y-m-d H:i",$stringToTime);
    //echo "<BR> Test Time: $stringToTime | $finalTimeToCancel | $sqlDate | $date";
    //date("Y-m-d H:i",strtotime($tmpTime, strtotime($current_date)));
    //$finalCurrentTime = date("Y-m-d H:i",strtotime($currentTime));
    //$cancelTimeAry = getCancelTime($transactionID);
    Echo "<BR>------- New Bittrex Check---------<blockquote>";

    $cancelTimeCheck = 0;
    if (isset($timeStampNow) AND isset($timeStampTimeToCancel)){
      if ($timeStampTimeToCancel > $timeStampNow){ echo "<BR> DO NOT CANCEL: 0 | $timeStampTimeToCancel | $timeStampNow | $transactionID"; $cancelTimeCheck = 0;}
      else {
        echo "<BR> CANCEL: 1 |$timeStampTimeToCancel | $timeStampNow | $transactionID"; $cancelTimeCheck = 1;
        newLogToSQL("BittrexBuyCancel", "Order time exceeded for $BittrexID Cancel order completed | $date | $timeToCancel | $minsRemaining | $BittrexID | $cancelTimeCheck | $finalBool | $actionMins | $timeToCancelMins", $userID, 0,"TimeCheck","TransactionID:$transactionID");
      }
    }
    //$cancelTimeCheck = 0;
    echo "<BR> CurrentTime: | Cancel Time $actionMins | $timeToCancelMins | $cancelTimeCheck ";
    if (!Empty($KEK)){$apiSecret = decrypt($KEK,$BittrexReqs[$b][8]);}
    $buyOrderCancelTime = $BittrexReqs[$b][24]; $saveMode = $BittrexReqs[$b][44];
    //if ($liveCoinPriceBit != 0 && $bitPrice != 0){$pctFromSale =  (($liveCoinPriceBit-$bitPrice)/$bitPrice)*100;}
    //if ($liveCoinPriceBit != 0 && $cost != 0){$liveProfitPct = ($liveCoinPriceBit-$cost)/$cost*100;}
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
      newLogToSQL("Bittrex", "Final Price: $tempPrice / $orderQty = $finalPrice", $userID, 0,"UpdateQtyFilled","TransactionID:$transactionID");
      //$cancelInit = $resultOrd["result"]["CancelInitiated"];
      $qtySold = $resultOrd["fillQuantity"];

      $orderQtyRemaining = $orderQty-$qtySold;

      if ($resultOrd["status"] == 'OPEN'){$status = 1;$cancelInit = 1;$orderIsOpen = 1;}else{$status = 0; $cancelInit = 0;$orderIsOpen = 0;}
      if ($resultOrd["status"] == 'CLOSED' AND $qtySold == 0){ $cancelInit = 1;$orderIsOpen = 0; }
      Echo "<BR> OrderQuantity: $orderQtyRemaining = $orderQty-$qtySold | $orderIsOpen | $cancelInit";
    }

    $newPurchasePrice = $amount*$cost;
    $newCost = ($amount*$cost)/$liveCoinPriceBit;
    //$newResidualAmount =  $cost - $newCost;
    //if ($orderQtyRemaining=0){$orderIsOpen = false;}
    echo "<BR> ------COIN to $type: ".$coin."-------- USER: ".$userName;
    //echo "<BR> Buy Cancel Time: $buyCancelTime";
    echo "TIME SINCE ACTION $type: $cancelTimeCheck | $minsRemaining ".$BittrexReqs[$b][57]." | ".$BittrexReqs[$b][58]." | ".$BittrexReqs[$b][59];
    //Print_r("What is Happening? // BITREXTI.D = ".$uuid."<br>");
    //echo "<BR> Result IS OPEN? : ".$orderIsOpen." // CANCEL initiated: ".$cancelInit;
    updateBittrexQuantityFilled($qtySold,$uuid);
    if ($qtySold <> 0){ newLogToSQL("Bittrex", "Quantity Updated to : $qtySold for OrderNo: $orderNo", $userID, $GLOBALS['logToSQLSetting'],"UpdateQtyFilled","TransactionID:$transactionID");}
    echo "<BR> New Test: $type | ".$resultOrd["quantity"];
    //if (!isset($resultOrd["quantity"])){
      if ($type == "Buy" or $type == "SpreadBuy"){
        newLogToSQL("CheckOldTransIDBuy","$oldBuyBackTransID | $multiSellRuleTemplateID | $reduceLossBuy",3,0,"RunBittrex","TransID:$transactionID");
        if ($resultOrd["status"] == 'CLOSED' &&  $qtySold == 0){
          //cancel
          Echo "<BR> CANCEL Buy ORDER ".$resultOrd["status"]." $qtySold";
          bittrexBuyCancel($uuid, $transactionID, "CancelMins: $minsSinceAction");
        }elseif ($orderIsOpen != 1 && $cancelInit != 1 && $orderQtyRemaining == 0){
          //sendtoSteven($transactionID,$orderQtyRemaining."_".$qtySold."_".$orderQty, $orderNo."_".$finalPrice."_".$liveCoinPriceBit, "BUY - OrderIsOpen != 1 & CancelInitiated != 1");
          if ($sendEmail){
            $subject = "Coin Purchase1: ".$coin;
            $from = 'Coin Purchase <purchase@investment-tracker.net>';
            sendEmail($email, $coin, $amount, $finalPrice, $orderNo, $totalScore, $subject,$userName,$from,$baseCurrency);
          }

          updateBuyAmount($transactionID,$orderQty);
          if($redirectPurchasesToSpread == 1){
            $type = 'SpreadBuy';
            newLogToSQL("BittrexBuy", "Redirect All to SpreadBet: $redirectPurchasesToSpread | $spreadBetIDRedirect", $userID, 1,"SpreadBuy","TransactionID:$transactionID");
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

          if ($pauseCoinIDAfterPurchaseEnabled == 1 ){
              addCoinPurchaseDelay($coinID,$userID,$daysToPauseCoinIDAfterPurchase,1);
          }
          if ($reduceLossBuy == 1){
            updateTrackingCoinToMerge($transactionID);
            if ($holdCoinForBuyOut == 1 and $holdingAmount > 0){
              $holdAmount = ($finalPrice*$amount);
              removeHoldingAmount($userID,$holdAmount,$baseCurrency,$transactionID);
            }
            $holdingAmount = 1;
          }

          if ($holdCoinForBuyOut == 1 and $holdingAmount == 0){
            $holdAmount = ($finalPrice*$amount)*($coinForBuyOutPct/100);
            saveHoldingAmount($userID,$holdAmount,$baseCurrency,$transactionID);
          }

          clearBittrexRef($transactionID);
          UpdateProfit();
          newLogToSQL("CheckOldTransID","$oldBuyBackTransID | $multiSellRuleTemplateID | $reduceLossBuy",3,1,"RunBittrex","TransID:$transactionID");
          //if ($oldBuyBackTransID <> 0){
            addBuyBackTransID($BittrexID,$transactionID);
            //delaySavingBuy($oldBuyBackTransID,80);
            $oldMultiSellStatus = getOldMultiSell($oldBuyBackTransID);
            echo "<br> Old MultiSell : ".$oldMultiSellStatus[0][0]." | ".$oldMultiSellStatus[0][1];
            if ($oldMultiSellStatus[0][0] == 1){$multiSellRuleEnabled = 1;$multiSellRuleTemplateID=$oldMultiSellStatus[0][1];}
          //}
          copyMultiSellIDfromRule($ruleIDBTBuy, $transactionID);

          if ($multiSellRuleTemplateID <> 0){
              $ruleStr = getMultiSellRulesTemplate($multiSellRuleTemplateID);
              $str_arr = explode (",", $ruleStr);
              $str_arrSize = count($str_arr);
              for ($t=0; $t<$str_arrSize; $t++){
                $sellRuleIDFromTemplate = $str_arr[$t];
                writeMultiRule($sellRuleIDFromTemplate,$transactionID,$userID);
              }
              writeMultiRuleTemplateID($transactionID,$multiSellRuleTemplateID);
          }else{
            setCustomisedSellRule($ruleIDBTBuy,$coinID);
            if ($type == 'Buy' and $coinModeRule == 0){
                setCustomisedSellRuleBased($coinID, $ruleIDBTBuy, 40.00);
            }
          }
          newLogToSQL("BittrexBuy", "bittrexBuyComplete($uuid, $transactionID, $finalPrice,$type);", $userID, 1,"OrderComplete","TransactionID:$transactionID");
          bittrexBuyComplete($uuid, $transactionID, $finalPrice,$type); //add buy price - $finalPrice
          //addWebUsage($userID,"Add","SellCoin");
          //updateAmount $uuid  $resultOrd["result"]["Quantity"]
          updateSQLQuantity($uuid,$orderQty);
          newLogToSQL("BittrexBuy", "Order Complete for OrderNo: $orderNo Final Price: $finalPrice | Type: $type", $userID, $GLOBALS['logToSQLSetting'],"OrderComplete","TransactionID:$transactionID");
          //addBuyRuletoSQL($transactionID, $ruleIDBTBuy);
          echo "<BR>Buy Order COMPLETE!";
          //continue;
          updateCoinAllocation($userID, $lowMarketMode, $baseCurrency, $finalPrice*$amount);
          logAction("runBittrex; bittrexBuyComplete : $coin | $type | $baseCurrency | $userID | $liveCoinPriceBit | $coinID | $type | $finalPrice | $amount | $userID | $uuid | $orderQty | $transactionID", 'BuySellFlow', 1);
          if ($oneTimeBuy == 1){ disableBuyRule($ruleIDBTBuy);}
          return True;
          echo "<BR> FinalBool 1";
        }elseif ($orderIsOpen != 1 && $cancelInit != 1 && $orderQty <> $orderQtyRemaining && $finalBool == False){
          bittrexUpdateBuyQty($transactionID, $orderQty-$orderQtyRemaining);
          if ($sendEmail){
            $subject = "Coin Purchase1: ".$coin;
            $from = 'Coin Purchase <purchase@investment-tracker.net>';
            sendEmail($email, $coin, $amount, $cost, $orderNo, $totalScore, $subject,$userName,$from, $baseCurrency);
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

          if ($mergeSavingwithPurchase == 1){
	           setSavingToLivewithMerge($userID,$coinID,$transactionID);
          }
          if ($reduceLossBuy = 1){
            updateTrackingCoinToMerge($transactionID);
          }

          if ($oldBuyBackTransID <> 0){
            addBuyBackTransID($oldBuyBackTransID,$transactionID);
            delaySavingBuy($oldBuyBackTransID,80);
            $oldMultiSellStatus = getOldMultiSell($oldBuyBackTransID);
            echo "<br> Old MultiSell : ".$oldMultiSellStatus[0][0]." | ".$oldMultiSellStatus[0][1];
            if ($oldMultiSellStatus[0][0] == 1){$multiSellRuleEnabled = 1;$multiSellRuleTemplateID=$oldMultiSellStatus[0][1];}
          }
          if ($multiSellRuleTemplateID <> 0){
              $ruleStr = getMultiSellRulesTemplate($multiSellRuleTemplateID);
              $str_arr = explode (",", $ruleStr);
              $str_arrSize = count($str_arr);
              for ($t=0; $t<$str_arrSize; $t++){
                $sellRuleIDFromTemplate = $str_arr[$t];
                writeMultiRule($sellRuleIDFromTemplate,$transactionID,$userID);
              }
              writeMultiRuleTemplateID($transactionID,$multiSellRuleTemplateID);
          }else{
            setCustomisedSellRule($ruleIDBTBuy,$coinID);
            if ($type == 'Buy' and $coinModeRule == 0){
                setCustomisedSellRuleBased($coinID, $ruleIDBTBuy, 40.00);
            }
          }
          bittrexBuyComplete($uuid, $transactionID, $finalPrice); //add buy price - $finalPrice
          newLogToSQL("BittrexBuyComplete", "bittrexBuyComplete($uuid, $transactionID, $finalPrice);", $userID, $CancelBittrexBuyComplete,"NewBuySellCoins","TransactionID:$transactionID");
          //addWebUsage($userID,"Remove","BittrexAction");
          logAction("runBittrex; bittrexBuyCompletePartial : $coin | $type | $baseCurrency | $userID | $liveCoinPriceBit | $coinID | $type | $finalPrice | $amount | $userID | $uuid | $orderQty | $transactionID", 'BuySellFlow', 1);
          echo "<BR> FinalBool 2";
          $finalBool = True;
        }
        //if ( substr($timeSinceAction,0,4) == $buyCancelTime){
        //if ( $buyOrderCancelTime < date("Y-m-d H:i:s", time()) && $buyOrderCancelTime != '0000-00-00 00:00:00'){
        echo "<BR> Cancel Check: $cancelTimeCheck | $minsRemaining | $finalBool";
        if ( $cancelTimeCheck == 1 && $finalBool == False){
          echo "<BR>CANCEL time exceeded! CANCELLING! $minsRemaining | $BittrexID";
          //newLogToSQL("BittrexBuyCancel", "Order time exceeded for $BittrexID Cancel order completed | $date | $timeToCancel | $minsRemaining | $BittrexID | $cancelTimeCheck | $finalBool", $userID, 0,"FullOrder","TransactionID:$transactionID");
          if ($orderQty == $orderQtyRemaining){
             $cancelRslt = bittrexCancel($apiKey,$apiSecret,$uuid,$apiVersion);
             LogToSQL("BittrexAPI","1_bittrexCancel($apiKey,$apiSecret,$uuid,$apiVersion);".json_encode($cancelRslt),3,1);
             //var_dump($cancelRslt);
             $canStatus = $cancelRslt['status']; $errorCode = $cancelRslt['code'];
             echo "<BR> Cancelling: bittrexCancel($apiKey,$apiSecret,$uuid,$apiVersion); $canStatus";
             if ($canStatus == 'CLOSED' OR $errorCode == "ORDER_NOT_OPEN"){
               bittrexBuyCancel($uuid, $transactionID, "CancelMins: $minsSinceAction");
               logAction("runBittrex; bittrexBuyCancelFull : $coin | $type | $baseCurrency | $userID | $liveCoinPriceBit | $coinID | $type | $finalPrice | $amount | $userID | $uuid | $orderQty | $transactionID | $minsSinceAction | $timeToCancelMins", 'BuySellFlow', 1);
               //newLogToSQL("BittrexBuyCancel", "Order time exceeded for OrderNo: $orderNo Cancel order completed", $userID, 0,"FullOrder","TransactionID:$transactionID");
               newLogToSQL("BittrexBuyFullCancel", "bittrexBuyCancel($uuid, $transactionID, 'CancelMins: $minsSinceAction');", $userID, $CancelBittrexLogFlag,"NewBuySellCoins","TransactionID:$transactionID");
               reopenCoinSwapCancel($BittrexID,1);
               removeTransactionDelay($coinID, $userID);
             }else{
               logAction("bittrexCancelBuyOrder: ".$cancelRslt, 'Bittrex', $GLOBALS['logToFileSetting'] );
               newLogToSQL("BittrexBuyCancel", "Order time exceeded for OrderNo: $orderNo Cancel order Error: $cancelRslt | $timeToCancelMins | $minsSinceAction", $userID, 1,"FullOrder","TransactionID:$transactionID");
             }
          }else{
            $result = bittrexCancel($apiKey,$apiSecret,$uuid,$apiVersion);
            LogToSQL("BittrexAPI","2_bittrexCancel($apiKey,$apiSecret,$uuid,$apiVersion);".json_encode($result),3,1);
            $canStatus = $result['status'];
            if ($canStatus == 'CLOSED'){
              bittrexUpdateBuyQty($transactionID, $orderQty-$orderQtyRemaining);
              //newLogToSQL("BittrexBuyCancel", "Order time exceeded for OrderNo: $orderNo Order cancelled and new Order Created. QTY: $orderQty | QTY Remaining: $orderQtyRemaining", $userID, 1,"PartialOrder","TransactionID:$transactionID");
              logAction("runBittrex; bittrexBuyCancelPartial : $coin | $type | $baseCurrency | $userID | $liveCoinPriceBit | $coinID | $type | $finalPrice | $amount | $userID | $uuid | $orderQty | $transactionID", 'BuySellFlow', 1);
              if ($sendEmail){
                $subject = "Coin Purchase1: ".$coin;
                $from = 'Coin Purchase <purchase@investment-tracker.net>';
                sendEmail($email, $coin, $amount, $cost, $orderNo, $totalScore, $subject,$userName,$from, $baseCurrency);
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
              newLogToSQL("BittrexBuyPartialCancel", "bittrexBuyComplete($uuid, $transactionID, $finalPrice);bittrexUpdateBuyQty($transactionID, $orderQty-$orderQtyRemaining);", $userID, $CancelBittrexPartial,"NewBuySellCoins","TransactionID:$transactionID");
              //addWebUsage($userID,"Add","SellCoin");
              //addBuyRuletoSQL($transactionID, $ruleIDBTBuy);
            }else{ logAction("bittrexCancelBuyOrder: ".$result, 'Bittrex', $GLOBALS['logToFileSetting'] );}
          }
          addUSDTBalance('USDT',$amount*$finalPrice,$finalPrice,$userID);
          if ($buyBack == 1){ reopenCoinSwapCancel($BittrexID,1); }
          echo "<BR> FinalBool 3";
          $finalBool = True;
          //($userID,"Remove","BittrexAction");
          //reOpenBuySellProfitRule($ruleIDBTBuy,$userID,$coinID);
        }
      }elseif (($type == "Sell" && $finalBool == False)or ($type == "SpreadSell" && $finalBool == False) or ($type == "SavingsSell" && $finalBool == False) ){ // $type Sell
        //logToSQL("Bittrex", "Sell Order | OrderNo: $orderNo Final Price: $finalPrice | $orderIsOpen | $cancelInit | $orderQtyRemaining", $userID, $GLOBALS['logToSQLSetting']);
        $finalPrice = $BittrexReqs[$b][86]; $diffToSale = $BittrexReqs[$b][90];
        echo "<BR> SELL TEST: $orderIsOpen | $cancelInit | $orderQtyRemaining | $amount | $finalPrice | $uuid";
        newLogToSQL("BittrexSell", "$type | $orderIsOpen | $cancelInit | $orderQtyRemaining | $amount| $finalPrice | $uuid", $userID,0,"SellComplete","TransactionID:$transactionID");
        echo "<BR> Pct From Sale: $pctFromSale Lice Profit Pct: $liveProfitPct Cancel Sale Pct Target: $pctToCancelBittrexAction Days Outstanding: $daysOutstanding";
        if ($originalAmount == 0){ $originalAmount = $amount;}
        $sellPrice = ($finalPrice*$amount);
        $livePrice = ($liveCoinPriceBit*$amount);
        $buyPrice = $cost*$originalAmount;
        $fee = (($sellPrice)/100)*0.25;
        $profit = number_format((float)($sellPrice-$buyPrice)-$fee, 8, '.', '');
        $profitPct = ($profit/$buyPrice)*100;
        //$diffToSale = (($livePrice-$sellPrice)/$sellPrice)*100;
        Echo "<BR> DiffToSale: $diffToSale | $sellPrice ($finalPrice) ($bitPrice) | $livePrice ($liveCoinPriceBit)";
        if ($resultOrd["status"] == 'CLOSED' &&  $qtySold == 0){
          //cancel order
          Echo "<BR> CANCEL Sell ORDER ".$resultOrd["status"]." $qtySold <BR>";
          bittrexSellCancel($uuid, $transactionID, "DaysOutstanding: $daysOutstanding");
          newLogToSQL("BittrexSell", "bittrexSellCancel($uuid, $transactionID, DaysOutstanding: $daysOutstanding);$qtySold | ".$resultOrd["status"], $userID, 1,"CancelBittrexSell","TransactionID:$transactionID");
          var_dump($resultOrd);
        }elseif ($diffToSale > 4.50 and $qtySold == 0){
          ECHO "<BR> RE-RUN BittrexSell ($diffToSale): reRunBittrexSell($uuid, $transactionID,$apiKey,$apiSecret,$apiVersion,$BittrexID,$sellPrice,$coin,$baseCurrency,$liveCoinPriceBit,$amount);";
          $returnVal = reRunBittrexSell($uuid, $transactionID,$apiKey,$apiSecret,$apiVersion,$BittrexID,$sellPrice,$coin,$baseCurrency,$liveCoinPriceBit,$amount);
          if ($returnVal){
            $finalBool = True;
          }
        }elseif (($orderIsOpen == 0) AND ($cancelInit == 0)){
          echo "<BR>SELL Order COMPLETE!";
            //$profitPct = ($finalPrice-$cost)/$cost*100;
            //if ($originalAmount == 0){ $originalAmount = $amount;}
            //$sellPrice = ($finalPrice*$amount);
            //$buyPrice = $cost*$originalAmount;
            //$fee = (($sellPrice)/100)*0.25;
            //$profit = number_format((float)($sellPrice-$buyPrice)-$fee, 8, '.', '');
            //$profitPct = ($profit/$buyPrice)*100;
            $realSellPrice = ($finalPrice*$originalAmount);
            $realProfitPct = (($realSellPrice-$buyPrice)/$buyPrice)*100;
            newLogToSQL("BittrexSell", "$finalPrice | $amount | $sellPrice | $cost | $originalAmount | $buyPrice | $fee | $profit | $profitPct | $realSellPrice | $realProfitPct", $userID, 0,"OriginalPrice","TransactionID:$transactionID");
            //sendtoSteven($transactionID,$orderQtyRemaining."_".$qtySold."_".$orderQty, $orderNo."_".$finalPrice."_".$liveCoinPriceBit, "SELL - Order Is Open != 1 & CancelInitiated != 1");
            if ($sendEmail){
              $subject = "Coin Sale: ".$coin." RuleID:".$ruleIDBTSell;
              $from = 'Coin Sale <sale@investment-tracker.net>';
              sendSellEmail($email, $coin, $amount, $finalPrice, $orderNo, $totalScore,$profitPct,$profit,$subject,$userName,$from,$baseCurrency);
            }
            //if ($type == "CoinSwapSell"){
              //update transaction to new Coin ID and amount
            //  $coinSwapBuyCoinID = coinSwapBuyModeLookup();
              //Initiate buy


            //}else{
              echo "<BR> Bittrex Sell Complete: $uuid | $transactionID | $finalPrice";
              newLogToSQL("BittrexSell", "bittrexSellComplete($uuid, $transactionID, $finalPrice); $originalAmount ", $userID, 1,"bittrexSellComplete","TransactionID:$transactionID");
              bittrexSellComplete($uuid, $transactionID, $finalPrice); //add sell price - $finalPrice
              ////addWebUsage($userID,"Remove","SellCoin");
              extendPctToBuy($coinID,$userID);
              $allocationType = 'Standard';
              if ($type == 'SpreadSell'){
                $allocationType = 'SpreadBet';
                $sBCountAry = checkSpreadBetComplete($spreadBetRuleID);
                $sBCount = $sBCountAry[0][0];
                if ($sBCount == 0){
                  runSpreadBetComplete($spreadBetRuleID);
                }
              }elseif ($coinModeRule >0){
                $allocationType = 'CoinMode';
              }
              if ($saveMode == 1 AND $profitPct > 0.25 and $type <> 'SpreadSell'){
                $newProfit = ($profit / 100)*$pctToSave;
                addProfitToAllocation($userID, $newProfit,$saveMode, $baseCurrency,$overrideBBSaving,$type,$spreadBetTransactionID);
              }elseif ($saveMode == 2 AND $profitPct > 0.25 and $type <> 'SpreadSell'){
                //$newProfit = ($profit / 100)*$pctToSave;
                $newProfit = $profit;
                addProfitToAllocation($userID, $newProfit,$saveMode, $baseCurrency,$overrideBBSaving,$type,$spreadBetTransactionID);
              }elseif ($profitPct < 0.25){
                $newProfit = 0;
              }
              //SaveMode: 0 = Off ; 1 = Save % of Total Profit ; 2 = Save Residual as USDT.
              $newBuyBackAmount = $amount*$cost;
              $usd_Amount = $sellPrice-$newProfit;
              if ($baseCurrency == 'BTC'){
                //$usd_Amount = $newBuyBackAmount * $btc_Price;
              }elseif ($baseCurrency == 'ETH'){
                //$usd_Amount = $newBuyBackAmount * $eth_Price;
              }
              newLogToSQL("BittrexSell", "Sell Order Complete for OrderNo: $orderNo Final Price: $finalPrice", $userID, $GLOBALS['logToSQLSetting'],"SellComplete","TransactionID:$transactionID");
              if ((is_null($coinModeRule)) OR ($coinModeRule == 0) ){
                //Update Buy Rule
                $buyTrendPct = updateBuyTrendHistory($coinID,$orderDate);
                $Hr1Trnd = $buyTrendPct[0][0]; $Hr24Trnd = $buyTrendPct[0][1]; $d7Trnd = $buyTrendPct[0][2];
                newLogToSQL("BittrexSell", "updateBuyTrend($coinID, $transactionID, Rule, $ruleIDBTSell, $Hr1Trnd,$Hr24Trnd,$d7Trnd);", $userID, $GLOBALS['logToSQLSetting'],"updateBuyTrend","TransactionID:$transactionID");
                updateBuyTrend($coinID, $transactionID, 'Rule', $ruleIDBTSell, $Hr1Trnd,$Hr24Trnd,$d7Trnd);

                newLogToSQL("BittrexSell", "WriteBuyBack($transactionID,$realProfitPct,10, 60,$finalPrice,$amount,$cost,$usd_Amount);$buyBackCounter / $buyBackMax", $userID, 1,"BuyBack1","TransactionID:$transactionID");
                if (($buyBackEnabled == 1) AND ($buyBackCounter <= $buyBackMax) AND ($disableBuyBack == 0)){
                  //if ($stopBuyBack == 0){ $buyBackEnabled = 0;}
                    $tempRises = floor(($hr1PriceMovePct + 30)/5)+5;
                    $tempmins = floor(100-(($hr1PriceMovePct/60)*100));
                    if ($tempRises <= 0){ $tempRises = 2;}
                    if ($tempmins <= 0){ $tempmins = 120;}
                    WriteBuyBack($transactionID,$realProfitPct,$tempRises, $bbminsToCancel,$finalPrice,$amount,$cost,$usd_Amount,$stopBuyBack,$overrideBBAmountSR,$overrideBBSavingSR,$spreadBetRuleID);
                    //addWebUsage($userID,"Add","BuyBack");
                  //}
                }
              }else{
                //Update Coin ModeRule
                $buyTrendPct = updateBuyTrendHistory($coinID,$orderDate);
                $Hr1Trnd = $buyTrendPct[0][0]; $Hr24Trnd = $buyTrendPct[0][1]; $d7Trnd = $buyTrendPct[0][2];
                newLogToSQL("BittrexSell", "updateBuyTrend($coinID, $transactionID, CoinMode, $ruleIDBTBuy, $Hr1Trnd,$Hr24Trnd,$d7Trnd);", $userID, $GLOBALS['logToSQLSetting'],"updateBuyTrend","TransactionID:$transactionID");
                updateBuyTrend($coinID, $transactionID, 'CoinMode', $ruleIDBTBuy, $Hr1Trnd,$Hr24Trnd,$d7Trnd);
                newLogToSQL("BittrexSell", "WriteBuyBack($transactionID,$realProfitPct,10, 60,$finalPrice,$amount,$cost,$usd_Amount);", $userID, 1,"BuyBack2","TransactionID:$transactionID");
                if (($buyBackEnabled == 1) AND ($disableBuyBack == 0)){WriteBuyBack($transactionID,$realProfitPct,10, 60,$finalPrice,$amount,$cost,$usd_Amount,$stopBuyBack,$overrideBBAmountSR,$overrideBBSavingSR,$spreadBetRuleID);}
              }
              if ($allocationType == 'SpreadBet' ){
                updateSpreadBetTotalProfitSell($transactionID,$finalPrice);
                subPctFromProfitSB($spreadBetTransactionID,0.01, $transactionID);
                if ($sBCount[0] == 0){
                  addProfitToAllocation($userID, 0,$saveMode, $baseCurrency,$overrideBBSaving,$type,$spreadBetTransactionID);
                }
              }
              newLogToSQL("BittrexSell","Test1: $saveResidualCoins | $realProfitPct | $originalAmount | $amount | $finalPrice | $cost | $buyPrice | $sellPrice | $realSellPrice",3,$GLOBALS['logToSQLSetting'],"SaveResidualCoins3","TransactionID:$transactionID");
              if ($saveResidualCoins == 1 and $realProfitPct >= 0.25 AND $originalAmount <> 0){
                $newOrderDate = date("YmdHis", time());
                $OrderString = "ORD".$coin.$newOrderDate.$ruleIDBTBuy;
                $residualAmount = $originalAmount - $amount;
                ResidualCoinsToSaving($residualAmount,$OrderString ,$transactionID,$originalAmount);
                newLogToSQL("BittrexSell","ResidualCoinsToSaving($newResidualAmount, $originalAmount, $amount, $OrderString, $transactionID, $realProfitPct);",3,1,"SaveResidualCoins3","TransactionID:$transactionID");
              }
              UpdateProfit();
              addCoinPurchaseDelay($coinID,$userID,1,0);
              deleteMultiSellRuleConfig($transactionID);
              if ($holdCoinForBuyOut == 1 and $holdingAmount > 0){
                $holdAmount = ($finalPrice*$amount)*(($coinForBuyOutPct/100)-($noOfPurchases-1));
                removeHoldingAmount($userID,$holdAmount,$baseCurrency,$transactionID);
              }
              logAction("runBittrex; bittrexSellComplete : $coin | $type | $baseCurrency | $userID | $liveCoinPriceBit | $coinID | $type | $finalPrice | $amount | $userID | $uuid | $orderQty | $originalAmount | $residualAmount | $transactionID", 'BuySellFlow', 1);
            //addSellRuletoSQL($transactionID, $ruleIDBTSell);
            echo "<BR> FinalBool 4";
            $finalBool = True;
        }
        if ($daysOutstanding <= -28){
          echo "<BR>days from sale! $daysOutstanding CANCELLING!";
          if ($orderQtyRemaining == $orderQty){
            //complete sell update amount
            $cancelRslt = bittrexCancel($apiKey,$apiSecret,$uuid,$apiVersion);
            LogToSQL("BittrexAPI","3_bittrexCancel($apiKey,$apiSecret,$uuid,$apiVersion);".json_encode($cancelRslt),3,1);
            $canStatus = $cancelRslt['status'];
            if ($canStatus == 'CLOSED'){
              bittrexSellCancel($uuid, $transactionID, "DaysOutstanding: $daysOutstanding");
              logAction("runBittrex; bittrexSellCancelFull : $coin | $daysOutstanding | $type | $baseCurrency | $userID | $liveCoinPriceBit | $coinID | $type | $finalPrice | $amount | $userID | $uuid | $orderQty | $originalAmount | $residualAmount | $transactionID", 'BuySellFlow', 1);
              newLogToSQL("BittrexSell", "Sell Order over 28 Days. Cancelling OrderNo: $orderNo", $userID, $GLOBALS['logToSQLSetting'],"CancelFull","TransactionID:$transactionID");
              echo "<BR> FinalBool 5";
              $finalBool = True;
            }else{
              logAction("bittrexCancelSellOrder: ".$cancelRslt, 'Bittrex', $GLOBALS['logToFileSetting'] );
              newLogToSQL("BittrexSell", "Sell Order over 28 Days. Error cancelling OrderNo: $orderNo : $cancelRslt", $userID, $GLOBALS['logToSQLSetting'],"CancelFullError","TransactionID:$transactionID");
            }
          }else{
             $result = bittrexCancel($apiKey,$apiSecret,$uuid,$apiVersion);
             LogToSQL("BittrexAPI","4_bittrexCancel($apiKey,$apiSecret,$uuid,$apiVersion);".json_encode($result),3,1);
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
                 sendSellEmail($email, $coin, $orderQty-$orderQtyRemaining, $finalPrice, $orderNo, $totalScore,$profitPct,$profit,$subject,$userName,$from,$baseCurrency);
               }
               echo "<BR> FinalBool 6";
               $finalBool = True;
             }else{
               logAction("bittrexCancelSellOrder: ".$result, 'Bittrex', $GLOBALS['logToFileSetting'] );
               newLogToSQL("BittrexSell", "Sell Order over 28 Days. Error cancelling OrderNo: $orderNo : $result", $userID, $GLOBALS['logToSQLSetting'],"CancelPartialError","TransactionID:$transactionID");
             }
          }
          //addWebUsage($userID,"Remove","BittrexAction");
          //addWebUsage($userID,"Add","SellCoin");
          subUSDTBalance('USDT',$amount*$finalPrice,$finalPrice,$userID);
        }
        if (($pctFromSale <= $pctToCancelBittrexAction && $finalBool == False and $type <> 'SpreadSell') or ($pctFromSale >= 4 && $finalBool == False and $allocationType <> 'SpreadBet')){
          if ($overrideBittrexCancellation == 1){ continue;}
          if ($type == 'SpreadSell' AND $pctFromSale >= 4 AND $orderQtyRemaining == $orderQty) {
            reSellAtCurrentPrice($apiKey,$apiSecret,$uuid,$apiVersion,$bitPrice,$amount,$baseCurrency,$coin);
            continue;
          }
          echo "<BR>% from sale! $pctFromSale CANCELLING!";
          if ($orderQtyRemaining == $orderQty){
            $cancelRslt = bittrexCancel($apiKey,$apiSecret,$uuid,$apiVersion);
            LogToSQL("BittrexAPI","5_bittrexCancel($apiKey,$apiSecret,$uuid,$apiVersion);".json_encode($cancelRslt),3,1);
            if($apiVersion == 1){ $canResStatus = $cancelRslt;}
            else{ if ($cancelRslt['status'] == 'CLOSED'){$canResStatus = 1;}else{$canResStatus =0;}}
            if ($canResStatus == 1){
              bittrexSellCancel($uuid, $transactionID, "PctFromSale: $pctFromSale CancelAction: $pctToCancelBittrexAction Override:$overrideBittrexCancellation");
              logAction("runBittrex; bittrexSellCancelFull_v2 : $coin | $pctFromSale | $type | $baseCurrency | $userID | $liveCoinPriceBit | $coinID | $type | $finalPrice | $amount | $userID | $uuid | $orderQty | $originalAmount | $residualAmount | $transactionID", 'BuySellFlow', 1);
              newLogToSQL("BittrexSell", "Sell Order 3% Less or 4% above. Cancelling OrderNo: $orderNo", $userID, $GLOBALS['logToSQLSetting'],"CancelFullPriceRise","TransactionID:$transactionID");
              echo "<BR> FinalBool 7";
              $finalBool = True;
            }else{
              logAction("bittrexCancelSellOrder: ".$result, 'Bittrex', $GLOBALS['logToFileSetting'] );
              newLogToSQL("BittrexSell", "Sell Order 3% Less or 4% above. Error cancelling OrderNo: $orderNo : $result", $userID, $GLOBALS['logToSQLSetting'],"CancelFullPriceRiseError","TransactionID:$transactionID");
            }
          }else{
            $canResult = bittrexCancel($apiKey,$apiSecret,$uuid,$apiVersion);
            LogToSQL("BittrexAPI","6_bittrexCancel($apiKey,$apiSecret,$uuid,$apiVersion);".json_encode($canResult),3,1);
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
                sendSellEmail($email, $coin, $orderQty-$orderQtyRemaining, $finalPrice, $orderNo, $totalScore,$profitPct,$profit,$subject,$userName,$from,$baseCurrency);
              }
              echo "<BR> FinalBool 8";
              $finalBool = True;
            }else{
              logAction("bittrexCancelSellOrder: ".$result, 'Bittrex', $GLOBALS['logToFileSetting'] );
              newLogToSQL("BittrexSell", "Sell Order 3% Less or 4% above. Error cancelling OrderNo: $orderNo : $result", $userID, $GLOBALS['logToSQLSetting'],"CancelPartialPriceRiseError","TransactionID:$transactionID");
            }
          }
          subUSDTBalance('USDT',$amount*$finalPrice,$finalPrice,$userID);
        }
      }else{ echo "<BR> EXIT : Type: $type | Bool:$finalBool ";} //end $type Buy Sell
    //}else{
    //  echo "<BR> NOT SET!!!";
  //    logAction("bittrexCheckOrder: ".$status, 'Bittrex', $GLOBALS['logToFileSetting'] );
  //    newLogToSQL("Bittrex", "Check OrderNo: $orderNo Success:".$status, $userID, $GLOBALS['logToSQLSetting'],"Error","TransactionID:$transactionID");
  //  }//end bittrex order check
    echo "<br> Profit Pct $liveProfitPct Live Coin Price: $liveCoinPriceBit cost $cost";
    echo "<br>Time Since Action $minsRemaining ".$BittrexReqs[$b][57]." | ".$BittrexReqs[$b][58]." | ".$BittrexReqs[$b][59];

    echo "<BR> ORDERQTY: $orderQty - OrderQTYREMAINING: $orderQtyRemaining";
  }//Bittrex Loop
  echo "</blockquote>";
  return $finalBool;
}


function runCoinAlerts($coinAlerts,$marketAlerts,$spreadBetAlerts){
  $finalBool = False;
  if (isset($coinAlerts)){
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
  }
  if (isset($marketAlerts)){
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
  }
  if (isset($spreadBetAlerts)){
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
  }
  return $finalBool;
}

function buyToreduceLoss($lossCoins,$newWebSettingsAry){
  $nFile = "BuySellCoins"; $nFunc = "ReduceLoss";
  $tempSettings = getSetting($newWebSettingsAry,$nFile,$nFunc);
  $logFlowSettingAry = $tempSettings[0]; $logVariSettingAry = $tempSettings[1]; $logSQLSettingAry = $tempSettings[2]; $logExitSettingAry = $tempSettings[3]; $logAPISettingAry = $tempSettings[4]; $logEventsSettingAry = $tempSettings[5];
  $finalBool = False;
  $lossCoinsSize = count($lossCoins);
  Echo "<BR>0: Total: $lossCoinsSize";
  //$apiVersion = 3; $ruleID = 111111;
  for ($y=0; $y<$lossCoinsSize; $y++){
    $pctProfit = $lossCoins[$y][58]; $transactionID = $lossCoins[$y][0]; $minsToDelay = $lossCoins[$y][60]; $userID = $lossCoins[$y][3]; $coinID = $lossCoins[$y][2];
    $liveCoinPrice = $lossCoins[$y][19]; $baseCurrency = $lossCoins[$y][36]; $totalAmount = $lossCoins[$y][54];
    $reduceLossEnabled = $lossCoins[$y][61]; $reduceLossSellPct = $lossCoins[$y][62]; $reduceLossMultiplier = $lossCoins[$y][63]; $reduceLossCounter = $lossCoins[$y][64]; $reduceLossMaxCounter = $lossCoins[$y][65];
    $hoursFlat = $lossCoins[$y][68];$overrideReduceLoss = $lossCoins[$y][67]; $symbol = $lossCoins[$y][11]; $type = $lossCoins[$y][1];
    $holdCoinForBuyOut = $lossCoins[$y][69];
    $coinForBuyOutPct = $lossCoins[$y][70];
    $holdingAmount = $lossCoins[$y][71]; $savingOverride = $lossCoins[$y][72]; $hoursFlatTarget = $lossCoins[$y][73]; $spreadBetTransactionID = $lossCoins[$y][74]; $coinSwapDelayed = $lossCoins[$y][75];
    $hoursFlatAutoEnabled = $lossCoins[$y][80]; $pctOfAuto = $lossCoins[$y][79]; $maxHoursFlat = $lossCoins[$y][76]; $minsToCancel = $lossCoins[$y][81]; $spreadBetRuleID = $lossCoins[$y][82];
    $market24HrPctChange = $lossCoins[$y][83]; $market7DPctChange = $lossCoins[$y][84];
    $avgMarketPctChange = ($market24HrPctChange + $market7DPctChange)/2;
    $marketPctAvg = 1; $profitPctAvg = 1;
    if ($avgMarketPctChange < -2) { $marketPctAvg = 1-($avgMarketPctChange/-15); }
    if ($pctProfit <= -40){ $overrideReduceLoss = 1; }
    if ($pctProfit <= -50){ $pctOfAuto = 4.0; }
    if ($overrideReduceLoss == 1){
      $finalReduceLoss = 1;
      $pctOfAuto = $pctOfAuto/4;
      $hoursFlatTarget = floor(($maxHoursFlat/100)*5);
      echo "<BR> Override ReduceLoss | Target: $hoursFlatTarget | Max: $maxHoursFlat";
    }elseif ($reduceLossEnabled == 1){
      $finalReduceLoss = 1;
    }else{
      $finalReduceLoss = 0;
    }
    $excludeSpreadBet = 1;
    //if ($excludeSpreadBet = 1 and ($spreadBetTransactionID <> 0 and $overrideReduceLoss == 0 )){ echo "<BR> ExcludeSpreadBet: EXIT! "; continue;}

    Echo "<BR>1: PctOfAuto: $pctOfAuto | $pctProfit";
    if ($pctProfit < -20){
        //$pctOfAuto = 100+$pctProfit;
        Echo "<BR>2: PctOfAuto: $pctOfAuto";
        //$hoursFlatTarget = floor(($maxHoursFlat/100)*$pctOfAuto);
        //$hoursFlatTarget = floor(($maxHoursFlat/100)*(($pctOfAuto/100)*Abs(100+$pctProfit)));
        $profitPctAvg = 1- ($pctProfit/-50);

    }
    //and $minsToDelay > 0
    $totalAvg = ($marketPctAvg+$profitPctAvg)/2;
    if ($hoursFlatAutoEnabled == 1 AND $overrideReduceLoss == 0){
      $hoursFlatTarget = floor(($maxHoursFlat/100)*($pctOfAuto*$totalAvg));
    }
    //$hoursFlatTarget = $maxHoursFlat
    //if ($overrideReduceLoss == 0){
    $hoursFlatPct = ($hoursFlat/$hoursFlatTarget)*100;
    //}
    //echo "<BR> buyToreduceLoss: $pctProfit : $reduceLossSellPct | $coinSwapDelayed | $transactionID | $userID | $coinID | $symbol | $liveCoinPrice | $baseCurrency | $totalAmount |$reduceLossEnabled | $reduceLossSellPct | $hoursFlat / $hoursFlatTarget ($hoursFlatPct %) | $overrideReduceLoss | $finalReduceLoss | $reduceLossCounter : $reduceLossMaxCounter";
    SuperLog($nFile,"buyToreduceLoss: $pctProfit : $reduceLossSellPct | $coinSwapDelayed | $transactionID | $userID | $coinID | $symbol | $liveCoinPrice | $baseCurrency | $totalAmount |$reduceLossEnabled | $reduceLossSellPct | $hoursFlat / $hoursFlatTarget ($hoursFlatPct %) | $overrideReduceLoss | $finalReduceLoss | $reduceLossCounter : $reduceLossMaxCounter;",$nFunc,"RL2","TransactionID:$transactionID",$logFlowSettingAry,'Flow');
    if (($pctProfit <= $reduceLossSellPct  and $coinSwapDelayed == 0 AND $finalReduceLoss == 1  ) OR ($pctProfit <= $reduceLossSellPct AND $overrideReduceLoss == 1)){
      if (!isset($pctProfit)){ echo "<BR> PctProfit note set: EXIT! "; continue; }
      if ($minsToDelay < 0){ echo "<BR> MinsToDelay $minsToDelay: EXIT! "; continue; }
      if ($liveCoinPrice == 0){ echo "<BR> LiveCoinPrice = 0 $liveCoinPrice: EXIT! "; continue; }
      if ($reduceLossCounter > $reduceLossMaxCounter){ echo "<BR> ReduceLossCounter: $reduceLossCounter | $reduceLossMaxCounter : EXIT! "; continue; }
      if ($pctProfit <= -16 AND $hoursFlat >= 10){
        $hoursFlat = $hoursFlatTarget;
        echo "<BR> buyToreduceLoss3: $pctProfit | $hoursFlat | $hoursFlatTarget";
      }
      if ($hoursFlat < $hoursFlatTarget){ echo "<BR> HoursFlat: $hoursFlat | $hoursFlatTarget : EXIT! "; continue; }
      echo "<BR> buyToreduceLoss2: $pctProfit |$reduceLossSellPct | $minsToDelay | $reduceLossEnabled";

      //get multiplier
      //$openTransNoAry = getOpenTransNo($userID, $coinID);
      $currentBuy = $reduceLossMultiplier;
      $profitMultiplier = ABS($reduceLossSellPct)/ABS($pctProfit);
      $quant = $totalAmount*($currentBuy*$profitMultiplier);
      $newPurchase = ($totalAmount*$reduceLossMultiplier);
      echo "<BR> buyToreduceLoss2: 2 | $currentBuy | $quant | $profitMultiplier | $totalAmount";
      //newLogToSQL("buyToreduceLoss","addTrackingCoin($coinID, $liveCoinPrice, $userID, $baseCurrency, 1, 1, $newPurchase, 97, 0, 0, 1, $minsToCancel, 229,1,1,10,'Buy',$liveCoinPrice,0,0,1,'buyToreduceLoss',$transactionID);",3,1,"addTrackingCoin","TransactionID:$transactionID");
      SuperLog($nFile,"addTrackingCoin($coinID, $liveCoinPrice, $userID, $baseCurrency, 1, 1, $newPurchase, 97, 0, 0, 1, $minsToCancel, 229,1,1,$hoursFlat,$type,$liveCoinPrice,0,0,1,'buyToreduceLoss',$savingOverride,$transactionID);",$nFunc,"RL1","TransactionID:$transactionID",$logEventsSettingAry,'Events');
      //Buy Coin with Merge
      addTrackingCoin($coinID, $liveCoinPrice, $userID, $baseCurrency, 1, 1, $newPurchase, 97, 0, 0, 1, $minsToCancel, 229,1,1,$hoursFlat,$type,$liveCoinPrice,$spreadBetTransactionID,$spreadBetRuleID,1,'buyToreduceLoss',$savingOverride,$transactionID);
      //addWebUsage($userID,"Add","BuyTracking");
      //Set Merge for current Coin
      //updateTrackingCoinToMerge($transactionID, $currentBuy);
      //Set Delay
      delaySavingBuy($transactionID,4320);
      setNewTargetPrice($transactionID);
      updateReduceLossCounter($transactionID,'buyToreduceLoss');
      $finalBool =  True;
    }
  }
  return $finalBool;
}

function getSettings(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  //12
  $sql = "SELECT `SettingName`, `Setting` FROM `WebSettings`";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['SettingName'],$row['Setting']); //
  }
  $conn->close();
  return $tempAry;
}

function getNewSettings(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  //12
  $sql = "SELECT `FileName`, `Funcname`, `Flow`, `Variables`, `nSQL`, `nExit`, `nAPI`,`nEvents` FROM `LogSettings`";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['FileName'],$row['Funcname'],$row['Flow'],$row['Variables'],$row['nSQL'],$row['nExit'],$row['nAPI'],$row['nEvents']); //
  }
  $conn->close();
  return $tempAry;
}

function getCalculatedSellPct(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  //12
  $sql = "SELECT `Csp`.`ID`, `Csp`.`TransactionID`, `Csp`.`SellPct`,`Csp`.`UserID`, `Csp`.`LastUpdated`, `Csp`.`RuleID`
            FROM `CalculatedSellPct` `Csp`
            Join `Transaction` `Tr` on `Tr`.`ID` = `Csp`.`TransactionID`
            where `Tr`.`Status` in ('Open','Pending')";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['ID'],$row['TransactionID'],$row['SellPct'],$row['UserID'],$row['LastUpdated'],$row['RuleID']); //
  }
  $conn->close();
  return $tempAry;
}

//get Settings
$webSettingsAry = getSettings();
$newWebSettingsAry = getNewSettings();
//MultiRule
$multiSellRules = getMultiSellRules();
//get CSP
$csp = getCalculatedSellPct();
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
$sbBuyCoinTimer = date('Y-m-d H:i');
$sellCoinTimer = date('Y-m-d H:i');
$sellCoinSBTimer = date('Y-m-d H:i');
$sellCoinSBIndTimer = date('Y-m-d H:i');
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
$runSellCoinsFlag = False;
$runSellCoinsSBFlag = False;
$runBuyCoinsFlag = False;
$runSbBuyCoinsFlag = False;
$buyToReduceLossFlag = False;
$runSellCoinsSBIndFlag = False;
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
  echo "<BR> CHECK Re-Buy Savings!! $i<blockquote>";
          if (($i == 0) or ($reRunBuySavingsFlag == True)){
            $reBuySavingsFixed = getOpenCoinSwaps();
            $reRunBuySavingsFlag = False;
          }
          $reRunBuySavingsFlag = runReBuySavings($reBuySavingsFixed);
  echo "</blockquote><BR> CHECK Sell Savings!! $i<blockquote>";
          if ($i == 0 or $runSellSavingsFlag == True){
            $spreadBuyBack = getSavingsData();
            $runSellSavingsFlag = False;
          }
          $runSellSavingsFlag = runSellSavings($spreadBuyBack);
  echo "</blockquote><BR>CHECK PriceDip Rule Enable!! $i<blockquote>";
        if (date("Y-m-d H:i", time()) >= $priceDipTimer){
          $PDcurrent_date = date('Y-m-d H:i');
          $priceDipTimer = date("Y-m-d H:i",strtotime("+3 minutes 40 seconds", strtotime($PDcurrent_date)));
          $priceDipRules = getPriceDipRules();
        }
        runPriceDipRule($priceDipRules);
  echo "</blockquote><BR> CHECK BuyBack!! $i<blockquote>";
        if ($i == 0 or $runBuyBackFlag == True){
          $buyBackCoins = getBuyBackData();
          $runBuyBackFlag = False;
        }
        $runBuyBackFlag = runBuyBack($buyBackCoins,$newWebSettingsAry);
  echo "</blockquote><BR> CHECK Spreadbet Buy!! $i<blockquote>";
  if ($i == 0 OR $runSbBuyCoinsFlag == True){$sbBuyRules = getUserRules(2);}  //getSpreadBetUserRules
  if (date("Y-m-d H:i", time()) >= $sbBuyCoinTimer or $runSbBuyCoinsFlag == True){
    $BCcurrent_date = date('Y-m-d H:i');
    $sbBuyCoinTimer = date("Y-m-d H:i",strtotime("+2 minutes 50 seconds", strtotime($BCcurrent_date)));
    $userProfit = getTotalProfit();
    $marketProfit = getMarketProfit();
    $ruleProfit = getRuleProfit();
    $totalBTCSpent = getTotalBTC();
    $dailyBTCSpent = getDailyBTC();
    $baseMultiplier = getBasePrices();
    $delayCoinPurchase = getDelayCoinPurchaseTimes();
    $sbCoins = getSpreadBetTrackingCoins("WHERE `DoNotBuy` = 0 and `BuyCoin` = 1 Group by `Sbc`.`SpreadBetRuleID`  ORDER BY `Symbol` ASC","FROM `View1_BuyCoins` ");
    //getSpreadBetTrackingCoins - Group by `Sbc`.`SpreadBetRuleID`
    $runSbBuyCoinsFlag = False;
  }
  $runSbBuyCoinsFlag = runBuyCoins($sbCoins,$userProfit,$marketProfit,$ruleProfit,$totalBTCSpent,$dailyBTCSpent,$baseMultiplier,$delayCoinPurchase,$sbBuyRules,$coinPriceMatch,$coinPricePatternList,$coin1HrPatternList,$autoBuyPrice,$trackCounter,$buyCounter,'SpreadBet',$newWebSettingsAry);
  //echo "</blockquote><BR>CHECK Sell Spread Bet!! $i<blockquote>";
  //      if (date("Y-m-d H:i", time()) >= $sellSpreadBetTimer or $runSellSpreadBet == True){
  //        $sSBcurrent_date = date('Y-m-d H:i');
  //        $sellSpreadBetTimer = date("Y-m-d H:i",strtotime("+2 minutes 28 seconds", strtotime($sSBcurrent_date)));
  //        $sellSpread = getSpreadBetSellData();
  //        $runSellSpreadBet = False;
  //      }
  //      $runSellSpreadBet = runSellSpreadBet($sellSpread);
  echo "</blockquote><BR>CHECK Spread Sell!! $i<blockquote>";
        //if ($i == 0 or $runSpreadBetFlag == True){
        //  $SpreadBetUserSettings = getSpreadBetUserSettings();
        //}
        //if (date("Y-m-d H:i", time()) >= $spreadBetTimer or $runSpreadBetFlag == True){
        //  $SBcurrent_date = date('Y-m-d H:i');
        //  $spreadBetTimer = date("Y-m-d H:i",strtotime("+3 minutes 18 seconds", strtotime($SBcurrent_date)));
        //  $spread = getSpreadBetData();
        //  $runSpreadBetFlag = False;
        //}
        //$runSpreadBetFlag = runSpreadBet($spread,$SpreadBetUserSettings);
        if ($i == 0 OR $runSellCoinsSBFlag == True){$sellRulesSB = getUserSellRules('SpreadBet');}
        if (date("Y-m-d H:i", time()) >= $sellCoinSBTimer or $runSellCoinsSBFlag == True){
          $SCcurrent_date = date('Y-m-d H:i');
          $sellCoinSBTimer = date("Y-m-d H:i",strtotime("+2 minutes 25 seconds", strtotime($SCcurrent_date)));
          $sellCoinsSB = getTrackingSpreadBetSellCoins("SpreadSell");
          $userProfit = getTotalProfit();
          $runSellCoinsSBFlag = False;
          $buyToReduceLossFlag = True;
        }
        $runSellCoinsFlag = runSellCoins($sellRulesSB,$sellCoinsSB,$userProfit,$coinPriceMatch,$coinPricePatternList,$coin1HrPatternList,$autoBuyPrice,$webSettingsAry,$csp,'SpreadBet',$multiSellRules,$newWebSettingsAry);

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
        $runNewTrackingCoinFlag = runNewTrackingCoins($newTrackingCoins,$marketStats,$baseMultiplier,$ruleProfit,$coinPurchaseSettings,$clearCoinQueue,$openTransactions,$delayCoinPurchase,$webSettingsAry,$newWebSettingsAry);
  echo "</blockquote><BR> Tracking SELL COINS!! $i<blockquote>";
        if ((date("Y-m-d H:i", time()) >= $trackingSellCoinTimer) Or ($runTrackingSellCoinFlag == True)) {
          $TSCcurrent_date = date('Y-m-d H:i');
          $trackingSellCoinTimer = date("Y-m-d H:i",strtotime("+2 minutes 15 seconds", strtotime($TSCcurrent_date)));
          $newTrackingSellCoins = getNewTrackingSellCoins();
          $marketStats = getMarketstats();
          $runTrackingSellCoinFlag = False;
        }
        $runTrackingSellCoinFlag = runTrackingSellCoin($newTrackingSellCoins,$marketStats,$webSettingsAry);
  echo "</blockquote><BR> BUY COINS!! $i<blockquote>";
        if ($i == 0 OR $runBuyCoinsFlag == True){$buyRules = getUserRules(1);}  //getSpreadBetUserRules
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
          $coins = getTrackingCoins("WHERE `DoNotBuy` = 0 and `BuyCoin` = 1  ORDER BY `Symbol` ASC","FROM `View1_BuyCoins` ");
          //getSpreadBetTrackingCoins - Group by `Sbc`.`SpreadBetRuleID`
          $runBuyCoinsFlag = False;
        }
        $runBuyCoinsFlag = runBuyCoins($coins,$userProfit,$marketProfit,$ruleProfit,$totalBTCSpent,$dailyBTCSpent,$baseMultiplier,$delayCoinPurchase,$buyRules,$coinPriceMatch,$coinPricePatternList,$coin1HrPatternList,$autoBuyPrice,$trackCounter,$buyCounter,'Normal',$newWebSettingsAry);
  echo "</blockquote><BR> SELL COINS!! $i<blockquote>";
        if ($i == 0 OR $runSellCoinsFlag == True){$sellRules = getUserSellRules('Normal');}
        if (date("Y-m-d H:i", time()) >= $sellCoinTimer or $runSellCoinsFlag == True){
          $SCcurrent_date = date('Y-m-d H:i');
          $sellCoinTimer = date("Y-m-d H:i",strtotime("+2 minutes 25 seconds", strtotime($SCcurrent_date)));
          $sellCoins = getTrackingSellCoins("Sell"); //getTrackingSpreadBetSellCoins("Sell");
          $userProfit = getTotalProfit();
          $runSellCoinsFlag = False;
          $buyToReduceLossFlag = True;
        }
        $runSellCoinsFlag = runSellCoins($sellRules,$sellCoins,$userProfit,$coinPriceMatch,$coinPricePatternList,$coin1HrPatternList,$autoBuyPrice,$webSettingsAry,$csp,'Normal',$multiSellRules,$newWebSettingsAry);
  echo "</blockquote><BR> SELL SPREAD COINS (Individual)!! $i<blockquote>";
              if ($i == 0 OR $runSellCoinsSBIndFlag == True){$sellRules = getUserSellRules('SpreadBet');}
              if (date("Y-m-d H:i", time()) >= $sellCoinSBIndTimer or $runSellCoinsSBIndFlag == True){
                $SCcurrent_date = date('Y-m-d H:i');
                $sellCoinSBIndTimer = date("Y-m-d H:i",strtotime("+2 minutes 25 seconds", strtotime($SCcurrent_date)));
                $sellCoins = getTrackingSellCoins("SpreadSell"); //getTrackingSpreadBetSellCoins("Sell");
                $userProfit = getTotalProfit();
                $runSellCoinsSBIndFlag = False;
                $buyToReduceLossFlag = True;
              }
              $runSellCoinsSBIndFlag = runSellCoins($sellRules,$sellCoins,$userProfit,$coinPriceMatch,$coinPricePatternList,$coin1HrPatternList,$autoBuyPrice,$webSettingsAry,$csp,'SpreadSellInd',$multiSellRules,$newWebSettingsAry);
  echo "</blockquote><BR> CHECK BITTREX!! $i<blockquote>";
        if (date("Y-m-d H:i", time()) >= $bittrexReqsTimer or $refreshBittrexFlag == True){
          $BRcurrent_date = date('Y-m-d H:i');
          $bittrexReqsTimer = date("Y-m-d H:i",strtotime("+2 minutes 34 seconds", strtotime($BRcurrent_date)));
          $BittrexReqs = getBittrexRequests();
          $refreshBittrexFlag = False;
        }
        $refreshBittrexFlag = runBittrex($BittrexReqs,$apiVersion,$webSettingsAry);
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
        $buyToReduceLossFlag = buyToreduceLoss($lossCoins,$newWebSettingsAry);


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
