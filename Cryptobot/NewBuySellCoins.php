<html>
<?php
ini_set('max_execution_time',1200);
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
  $coinSwapsSize = count($coinSwaps);
  $apiVersion = 3; $ruleID = 111111;
  for ($y=0; $y<$coinSwapsSize; $y++){
    $status = $coinSwaps[$y][1];
    if ($status == 'AwaitingSavingsBuy'){
      $apikey = $coinSwaps[$y][8];$apisecret = $coinSwaps[$y][9];$KEK = $coinSwaps[$y][10];$ogCoinID = $coinSwaps[$y][12];$ogSymbol = $coinSwaps[$y][13];
       $baseCurrency = $coinSwaps[$y][5]; $totalAmount = $coinSwaps[$y][6]; $transID = $coinSwaps[$y][0];
      $finalPrice = $coinSwaps[$y][15];
      $tempPrice = getCoinPrice($ogCoinID);
      $bitPrice = $tempPrice[0][0];
      //$bitPrice = number_format($coinSwaps[$y][16],8);
      //$orderSale = isSaleComplete($coinSwaps,$y);
      $sellPct = 15;
      $tolerance = 5;
      $sellPricePct = (($finalPrice/100)*$sellPct);
      $sellPriceTolerance = (($finalPrice/100)*$tolerance);
      $lowPrice = $finalPrice-$sellPricePct+$sellPriceTolerance;
      echo "<BR> TEST Buy: $status | $ogCoinID | $ogSymbol | LowPrice:$lowPrice | BitPrice:$bitPrice";
      if ($bitPrice <= $lowPrice){
        $liveCoinPrice = $bitPrice;
        $rate = $liveCoinPrice;
        $quant = $totalAmount/$rate;
        addTrackingCoin($ogCoinID, $LiveCoinPrice, $userID, $baseCurrency, 1, 1, $quant, 999996, 0, 0, 1, 90, 77777,1,1,10,'SavingBuy',$LiveCoinPrice,0,0,0);
      }
    }
  }
}

function runSellSavings($spreadBuyBack){
  $versionNum = 3; $useAwards = False;
  $profitTarget = 40.0;
  $spreadBuyBackSize = COUNT($spreadBuyBack);
  for ($u=0; $u<$spreadBuyBackSize; $u++){
    $purchasePrice = $spreadBuyBack[$u][4];
    $amount = $spreadBuyBack[$u][5];$CoinID = $spreadBuyBack[$u][2];$userID = $spreadBuyBack[$u][3];
    $tempPrice = getCoinPrice($CoinID);
    $LiveCoinPrice = $tempPrice[0][0];$symbol = $spreadBuyBack[$u][11];$transactionID = $spreadBuyBack[$u][0];$fallsInPrice = $spreadBuyBack[$u][56];
    $profitSellTarget = $spreadBuyBack[$u][58];$autoBuyBackSell = $spreadBuyBack[$u][59];$bounceTopPrice = $spreadBuyBack[$u][60];$bounceLowPrice = $spreadBuyBack[$u][61];
    $bounceDifference = $spreadBuyBack[$u][62];$delayCoinSwap = $spreadBuyBack[$u][63];$noOfBounceSells = $spreadBuyBack[$u][64];$baseCurrency = $spreadBuyBack[$u][36];
    //echo "<BR> LiveCoinPrice:$LiveCoinPrice | Amount:$amount";
    $sellPrice = ($LiveCoinPrice * $amount);
    //echo "<BR> PurchasePrice:$purchasePrice | Amount:$amount";
    $buyPrice = ($purchasePrice * $amount);
    //echo "<BR> SellPrice:$sellPrice | BuyPrice:$buyPrice";
    $profit = ($sellPrice-$buyPrice);
    //echo "<BR> Profit:$profit | BuyPrice:$buyPrice";
    $profitPCT = ($profit/$buyPrice)*100;
    if ($baseCurrency == 'USDT'){ $baseMin = 20;}elseif ($baseCurrency == 'BTC'){ $baseMin = 0.00048;}elseif ($baseCurrency == 'ETH'){ $baseMin = 0.0081;}
    if ($profitPCT >= $profitTarget AND ($sellPrice)>= $baseMin){
      newTrackingSellCoins($LiveCoinPrice,$userID, $transactionID,1, 1,0,0,10,'SavingSell');
      setTransactionPending($transactionID);
    }elseif ($profitPCT >= $profitTarget){ Echo "<BR> CoinID: $CoinID | Sym: $symbol | SellPrice: $sellPrice | Min: $baseMin";}
  }
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
        enableBuyRule($buyRuleID);
        LogToSQL("PriceDipRuleEnable","enableBuyRule($buyRuleID); $hr24ChangePctChange | $hr24PriceDipPct | $d7ChangePctChange | $d7PriceDipPct",3,$logToSQLSetting);
      }
    }
  }
}

function runBuyBack(){
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
    $allBuyBackAsOverride = $buyBackCoins[$t][27];
    $tempPrice = getCoinPrice($CoinID);
    //$liveCoinPrice = $buyBackCoins[$t][9];
    $liveCoinPrice = $tempPrice[0][0];
    $priceDifferecePct = (($liveCoinPrice-$sellPriceBA)/$sellPriceBA)*100;
    ECHO "<BR> Check Price: $priceDifferecePct | $buyBackPct";
    if (($priceDifferecePct <=  $buyBackPct) OR ($bullBearStatus == 'BULL')){
      Echo "<BR> $priceDifferecePct <=  ($buyBackPct+$profitMultiply)";
      LogToSQL("BuyBack","PriceDiffPct: $priceDifferecePct | BuyBackPct: $buyBackPct Bull/Bear: $bullBearStatus",3,$logToSQLSetting);
      //BuyBack
      $marketStats = getMarketstats();
      $reOpenData = reOpenTransactionfromBuyBack($bBID);
      $tmpCoinID = $reOpenData[0][0];$tmpLiveCoinPrice = $reOpenData[0][1];$tmpUserID = $reOpenData[0][2];$tmpBaseCur = $reOpenData[0][3];
      $tmpSendEmail = $reOpenData[0][4];$tmpBuyCoin = $reOpenData[0][5];$tmpSalePrice = $reOpenData[0][6];$tmpBuyRule = $reOpenData[0][7];
      $tmpOffset = $reOpenData[0][8];$tmpOffsetEnabled = $reOpenData[0][9];$tmpBuyType = $reOpenData[0][10];$d11 = $reOpenData[0][11];$tmpFixSellRule = $reOpenData[0][12];$tmpToMerge = $reOpenData[0][13];
      $tmpNoOfPurchases = $reOpenData[0][14];$d15 = $reOpenData[0][15];$tmpType = $reOpenData[0][16];$tmpOriginalPrice = $reOpenData[0][17];
      $tmpSBTransID = $reOpenData[0][18];$tmpSBRuleID = $reOpenData[0][19];
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
      $buyBackPurchasePrice = ($liveCoinPrice*$quantity)+$bbKittyAmount;
      updateBuyBackKittyAmount($tmpBaseCur,$bbKittyAmount,$tmpUserID);
      if($tmpSalePrice <= 0 ){ continue;}
      addTrackingCoin($tmpCoinID, $tmpLiveCoinPrice, $tmpUserID, $tmpBaseCur, $tmpSendEmail, $tmpBuyCoin, $buyBackPurchasePrice, $tmpBuyRule, $tmpOffset, $tmpOffsetEnabled, $tmpBuyType, 240, $tmpFixSellRule,$tmpToMerge,$tmpNoOfPurchases,$noOfRaisesInPrice,$tmpType,$tmpOriginalPriceWithBuffer,$tmpSBTransID,$tmpSBRuleID,$overrideCoinAlloc);
      echo "<BR>addTrackingCoin($tmpCoinID, $tmpLiveCoinPrice, $tmpUserID, $tmpBaseCur, $tmpSendEmail, $tmpBuyCoin, $buyBackPurchasePrice, $tmpBuyRule, $tmpOffset, $tmpOffsetEnabled, $tmpBuyType, 240, $tmpFixSellRule,$tmpToMerge,$tmpNoOfPurchases,$noOfRaisesInPrice,$tmpType,$tmpOriginalPriceWithBuffer,$tmpSBTransID,$tmpSBRuleID);";
      LogToSQL("BuyBack","addTrackingCoin($tmpCoinID, $tmpLiveCoinPrice, $tmpUserID, $tmpBaseCur, $tmpSendEmail, $tmpBuyCoin, $buyBackPurchasePrice, $tmpBuyRule, $tmpOffset, $tmpOffsetEnabled, $tmpBuyType, 240, $tmpFixSellRule,$tmpToMerge,$tmpNoOfPurchases,$noOfRaisesInPrice,$tmpType,$tmpOriginalPriceWithBuffer,$tmpSBTransID,$tmpSBRuleID);",3,$logToSQLSetting);
      LogToSQL("BuyBackKitty","Adding $bbKittyAmount to $bBID | TotalBTC: $BTC_BB_Amount| Total USDT: $usdt_BB_Amount| TotalETH: $eth_BB_Amount | BTC_P: $portionBTC| USDT_P: $portion| ETH_P: $portionETH",3,$logToSQLSetting);
      //CloseBuyBack
      closeBuyBack($bBID);
    }
  }
}

function runSpreadBetSellAndBuyback($spreadBuyBack){
  $spreadBuyBackSize = COUNT($spreadBuyBack);
  for ($u=0; $u<$spreadBuyBackSize; $u++){
    $purchasePrice = $spreadBuyBack[$u][4];$amount = $spreadBuyBack[$u][5];$CoinID = $spreadBuyBack[$u][2];
    $userID = $spreadBuyBack[$u][3];$symbol = $spreadBuyBack[$u][11];$transactionID = $spreadBuyBack[$u][0];$fallsInPrice = $spreadBuyBack[$u][56];
    $profitSellTarget = $spreadBuyBack[$u][58];$autoBuyBackSell = $spreadBuyBack[$u][59];$bounceTopPrice = $spreadBuyBack[$u][60];
    $bounceLowPrice = $spreadBuyBack[$u][61];$bounceDifference = $spreadBuyBack[$u][62];$delayCoinSwap = $spreadBuyBack[$u][63];
    $noOfBounceSells = $spreadBuyBack[$u][64];$baseCurrency = $spreadBuyBack[$u][36];
    $tempPrice = getCoinPrice($CoinID);
    $LiveCoinPrice = $tempPrice[0][0];
    $profit = ($LiveCoinPrice * $amount)-($purchasePrice * $amount);
    $profitPCT = ($profit/($purchasePrice * $amount))*100;
    echo "<BR>CoinID: $CoinID | Bounce: $bounceDifference | LiveCoinPrice: $LiveCoinPrice |BounceTopPrice: $bounceTopPrice | DelayCoinSwap: $delayCoinSwap";
    if (($profitPCT <= $autoBuyBackSell) OR ($profitPCT >= $profitSellTarget) OR (($profitPCT < -30) AND ($bounceDifference >= 2.0) AND ($LiveCoinPrice >= $bounceTopPrice) AND ($delayCoinSwap <= 0))){
      LogToSQL("SellSpreadBet and BuyBack","ProfitPct: $profitPCT | AutoBuyBackSell: $autoBuyBackSell | ProfitSellTarget: $profitSellTarget",3,$logToSQLSetting);
      //$tempAry = $spreadBuyBack[$u];
      //sellSpreadBetCoins($tempAry);
      $finalProfitPct = $profitPCT;
      if (($profitPCT < -20) AND ($bounceDifference >= 2.5) and ($LiveCoinPrice == $bounceTopPrice)){
          $finalProfitPct = $bounceDifference;
          LogToSQL("SellSpreadBet and BuyBack","Bounce ProfitPct: $finalProfitPct | AutoBuyBackSell: $autoBuyBackSell | ProfitSellTarget: $profitSellTarget",3,$logToSQLSetting);
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
      LogToSQL("SellSpreadBet and BuyBack","newTrackingSellCoins($LiveCoinPrice, $userID,$transactionID,1,1,0,0.0,$totalRisesSell);",3,$logToSQLSetting);
      newTrackingSellCoins($bounceTopPrice, $userID,$transactionID,1,1,0,0.0,$totalRisesSell,'Sell');
      setTransactionPending($transactionID);
      WriteBuyBack($transactionID,$finalProfitPct,$totalRisesBuy, $totalMins);
      LogToSQL("SellSpreadBet and BuyBack","WriteBuyBack($transactionID,$finalProfitPct,$totalRisesBuy, $totalMins);",3,$logToSQLSetting);
    }else if(($profitPCT < -20) AND ($noOfBounceSells == 0) AND ($LiveCoinPrice >= $bounceTopPrice) AND ($delayCoinSwap <= 0)){
        $versionNum = 3; $useAwards = False;
        //Swap Coin
          //Choose new Coin
          newLogToSQL("SellSpreadBet and BuyBack", "Profit below -20: $CoinID | $profitPCT | $noOfBounceSells | $LiveCoinPrice | $bounceTopPrice", $userID, $logToSQLSetting,"Sell Coin","TransactionID:$transactionID");
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
            newLogToSQL("SellSpreadBet and BuyBack", "bittrexsell($apikey, $apisecret, $symbol, $amount, $LiveCoinPrice, $baseCurrency, $versionNum, $useAwards);", $userID, $logToSQLSetting,"Sell Coin","TransactionID:$transactionID");
            $obj = bittrexsell($apikey, $apisecret, $symbol, $amount, $LiveCoinPrice, $baseCurrency, $versionNum, $useAwards);
            //Add to Swap Coin Table
            $bittrexRef = $obj["id"];
            if ($bittrexRef <> ""){
              newLogToSQL("SellSpreadBet and BuyBack", "Sell Live Coin: $CoinID | $bittrexRef", $userID, $logToSQLSetting,"Sell Coin","TransactionID:$transactionID");
              updateCoinSwapTable($transactionID,'AwaitingSale',$bittrexRef,$newCoinSwap[0][0],$newCoinSwap[0][2],$baseCurrency,$LiveCoinPrice * $amount,$purchasePrice * $amount,'Sell');
            }
          }

    }
  }
}

function runSellSpreadBet($sellSpread){
  $sellSpreadSize = count($sellSpread);
  for ($w=0; $w<$sellSpreadSize; $w++){
    $CoinPriceTot = $sellSpread[$w][3]; $TotAmount = $sellSpread[$w][4]; $LiveCoinPriceTot = $sellSpread[$w][15];
    $ID = $sellSpread[$w][0]; $APIKey = $sellSpread[$w][50]; $APISecret = $sellSpread[$w][51]; $KEK = $sellSpread[$w][52];
    $Email = $sellSpread[$w][53]; $userID = $sellSpread[$w][2]; $UserName = $sellSpread[$w][54]; $captureTrend = $sellSpread[$w][57];
    $purchasePrice = $sellSpread[$w][59];$currentPrice = $sellSpread[$w][60];
    $spreadBetPctProfitSell = $sellSpread[$w][55]; $spreadBetRuleID = $sellSpread[$w][56]; $orderDate = $sellSpread[$w][6];
    //$profitPct = ($profit/$purchasePrice)*100;
    //$hr1Pct = $sellSpread[$w][25];  $hr24Pct = $sellSpread[$w][28]; $d7Pct = $sellSpread[$w][31];
    $baseCurrency_new = $sellSpread[$w][32];
    $fallsInPrice = $sellSpread[$w][61];

    $purchasePrice = $sellSpread[$w][59];// + $sellSpread[$w][63];
    //$livePrice = $tempProfit[0][1] + $tempProfit[0][2];
    $tempPrice = getCoinPrice($CoinID);
    $hr1Pct = $tempPrice[0][1]; $hr24Pct = $tempPrice[0][2]; $d7Pct = $tempPrice[0][3];
    $LiveCoinPriceTot = $tempPrice[0][0];
    //Echo "<BR> TEST!: $hr1Pct | $hr24Pct | $d7Pct | $CoinID";
    $livePrice = ($LiveCoinPriceTot * $TotAmount);
    //$soldPrice = $sellSpread[$w][66] + $sellSpread[$w][67];
    $profit = ($livePrice-$purchasePrice);//+$soldPrice;
    $profitPct = (($profit-$purchasePrice)/$purchasePrice)*100;
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
    }
    writeProfitToWebTable($ID,$purchasePrice,$livePrice,0);
  }
}

function runSpreadBet($spread,$SpreadBetUserSettings){
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
    $risesInPrice = $spread[$y][38]; $timeToCancelBuyMins = $spread[$y][39];
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
      $openCoins = checkOpenSpreadBet($UserID,$ID);
      $openCoinsSize = count($openCoins);
      $purchasePrice = $openCoins[0][1]; $totalAmountToBuy = $openCoins[0][2];
      $savedBTCAmount = $openCoins[0][3];
      $loopNum = 0;
      $availableTrans = $totalNoOfBuys - $openCoinsSize;
      Echo "<BR> Test for SpreadBetRePurchase: $purchasePrice | $totalAmountToBuy | $openCoinsSize | $totalNoOfBuys | $availableTrans";
      if ($openCoinsSize < $totalNoOfBuys and $availableTrans > 0){
        //$spreadBetToBuy = getCoinAllocation($UserID);
        $spreadBetToBuy = getNewCoinAllocation($baseCurrency,$UserID,False);
        $BTCtoSQL = ($spreadBetToBuy[0][0]/($divideAllocation - $openCoinsSize));
        $buyPerCoin = ($spreadBetToBuy[0][0]/($divideAllocation - $openCoinsSize)); //*$inverseAvgHighPct
        $BTCAmount =  $buyPerCoin/$spreadCoinsSize;
        LogToSQL("SpreadBetCoinAllocation","BTCAmount: $BTCAmount | DivAlloc: $divideAllocation | OpenCoinSize: $openCoinsSize | $inverseAvgHighPct | $totalNoOfBuys | $availableTrans | ".$spreadBetToBuy[0][0],3,$logToSQLSetting);
        if ($BTCAmount < 10){ ECHO "<BR> EXIT: Coin Allocation: ".$spreadBetToBuy[0][0]." | Div Alloc: $divideAllocation | inv pct: $inverseAvgHighPct | Buy Per Coin: $buyPerCoin | BTCAmount: $BTCAmount"; continue;}
      //}elseif ($availableTrans == 0){
      //  $BTCAmount =  $spreadBetToBuy[0][0]/$spreadCoinsSize;
      }elseif ($purchasePrice < $totalAmountToBuy) {
        $buyPerCoin =  $totalAmountToBuy - $purchasePrice;
        $noOfLoops = floor($buyPerCoin/$savedBTCAmount);
        $BTCAmount = $buyPerCoin /$noOfLoops;
        $loopNum = rand(0,$spreadCoinsSize- $noOfLoops);
        $spreadCoinsSize = $loopNum + $noOfLoops;
        LogToSQL("SpreadBetRePurchase","PurchasePrice: $purchasePrice | TotalAmountToBuy: $totalAmountToBuy | BuyPerCoin: $buyPerCoin | NoOfLoops:$noOfLoops | BTCAmount: $BTCAmount | LoppNum:$loopNum | SpreadCoinSize: $spreadCoinsSize" ,$UserID,$logToSQLSetting);
        if ($BTCAmount < 10){ ECHO "<BR> EXIT: Coin Allocation: ".$spreadBetToBuy[0][0]." | Div Alloc: $divideAllocation | inv pct: $inverseAvgHighPct | Buy Per Coin: $buyPerCoin | BTCAmount: $BTCAmount"; continue;}

      }else{ ECHO "<BR> EXIT: $openCoinsSize | $totalNoOfBuys | $availableTrans"; continue;}
      LogToSQL("SpreadBetBuy","1)ID: $ID | $Hr24ChangePctChange : $Hr24BuyPrice | $d7ChangePctChange : $D7BuyPrice | $Hr1ChangePctChange : $Hr1BuyPrice;",3,$logToSQLSetting);
      LogToSQL("SpreadBetBuy","Buy Spread Coins : $spreadCoinsSize | $spreadBetTransID | $spreadCoinsSize | BTCAmount: $BTCAmount",3,$logToSQLSetting);
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
        LogToSQL("SpreadBetTracking","addTrackingCoin($coinID, $liveCoinPrice, $userID, $baseCurrency, $SendEmail, $BuyCoin, $BTCAmount, $ruleIDBuy, $CoinSellOffsetPct, $CoinSellOffsetEnabled, $buyType, $timeToCancelBuyMins, $SellRuleFixed,0,0,$risesInPrice,'SpreadBuy',$ogCoinPrice,$spreadBetTransID,$spreadBetRuleID);",3,$logToSQLSetting);
        addTrackingCoin($coinID, $liveCoinPrice, $userID, $baseCurrency, $SendEmail, $BuyCoin, $BTCAmount, $ruleIDBuy, $CoinSellOffsetPct, $CoinSellOffsetEnabled, $buyType, $timeToCancelBuyMins, $SellRuleFixed,0,0,$risesInPrice,'SpreadBuy',$ogCoinPrice,$spreadBetTransID,$spreadBetRuleID,0);
        updateSpreadBetTransactionAmount($buyPerCoin, $spreadBetRuleID,$BTCtoSQL);
        LogToSQL("SpreadBetBuy","buyCoins($coinID)",3,$logToSQLSetting);
        //update Transaction to Spread
        //updateTransToSpread($ID,$coinID,$UserID,$spreadBetTransID);
        LogToSQL("SpreadBetBuy","updateTransToSpread($ID,$coinID,$UserID,$spreadBetTransID);",3,$logToSQLSetting);
        //updateSpreadBuy($ID);
        LogToSQL("SpreadBetBuy","updateSpreadBuy($ID);",3,$logToSQLSetting);
        //add new number in SpreadBetTransactions
        if ($t == $spreadCoinsSize-1 AND $spreadCoinsSize > 0){
          echo "<BR> newSpreadTransactionID($UserID,$spreadBetRuleID); | $t";
          //newSpreadTransactionID($UserID,$spreadBetRuleID);
          LogToSQL("SpreadBetBuy","newSpreadTransactionID($UserID,$spreadBetRuleID);",3,$logToSQLSetting);
          UpdateProfit();
          LogToSQL("SpreadBetBuy","UpdateProfit();",3,$logToSQLSetting);
        }
        //subUSDTBalance('USDT', $BTCAmount,$liveCoinPrice, $userID);
        LogToSQL("SpreadBetBuy","subUSDTBalance('USDT', $BTCAmount,$liveCoinPrice, $userID);",3,$logToSQLSetting);
      }
    }
  }
}

function runNewTrackingCoins($newTrackingCoins,$marketStats,$baseMultiplier,$ruleProfit){
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
    if ($openTransactionFlag == True){
      $openTransactions = getOpenTransactions();
      $openTransactionFlag = False;
    }


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
      newLogToSQL("TrackingCoins", "closeNewTrackingCoin($newTrackingCoinID); $pctProfit | $minsFromDate | $timeToCancelBuyMins", $userID, $logToSQLSetting,"MinsFromDateExceed","TrackingCoinID:$newTrackingCoinID"); Echo "<BR> MinsFromDate: $minsFromDate | ";
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
    if ($overrideCoinAlloc == 1){ $lowBuyMode = False;}else{$lowBuyMode=True; }
    $coinAllocation = getNewCoinAllocation($baseCurrency,$userID,$lowBuyMode);
    //$coinAllocation = getCoinAllocation($userID);
    Echo "<BR> Tracking CoinAllocation: ".$coinAllocation[0][0]." | $BTCAmount | $ruleIDBuy | $baseCurrency";
    if ($coinAllocation <= 0){
        echo "<BR> EXIT CoinAllocation: $baseCurrency | $type | $BTCAmount | $ogBTCAmount| $coinAllocation";
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
        $liveOpenTrans = $openTransactions[$h][$indexLookup];
        //$BTCAmount = $BTCAmount / ($liveOpenTrans-$noOfBuys);
        //LogToSQL("TrackingCoin","BTC Alloction: $oldBTCAmount | $BTCAmount | $indexLookup | $liveOpenTrans | $noOfBuys",3,1);
      }
    }
    Echo "<BR> Tracking Buy Count 5 <BR>";
    if ($minsDisabled>0){ Echo "<BR> Exit Disabled : $minsDisabled"; continue;}
    if ($trackCounter[$userID."-Total"] >= $noOfBuys and $overrideCoinAlloc == 0){ echo "<BR>EXIT: Buy Counter Met! $noOfBuys ".$trackCounter[$userID."-Total"];continue;}//else{ Echo "<BR> Number of Buys: $noOfBuys BuyCounter ".$trackCounter[$userID];}
    if ($trackCounter[$userID."-".$coinID] >= 1 and $overrideCoinAlloc == 0){ echo "<BR>EXIT: Buy Counter Met! $noOfBuys ".$trackCounter[$userID."-".$coinID];continue;}//else{ Echo "<BR> Number of Buys: $noOfBuys BuyCounter ".$trackCounter[$userID];}

    Echo "<BR> Price Check: Live:$liveCoinPrice Original: $originalPrice";
    $readyToBuy = trackingCoinReadyToBuy($liveCoinPrice,$timeToCancelBuyMins,$type,$originalPrice,$newTrackingCoinID,$noOfRisesInPrice,$pctProfit,$minsFromDate,$lastPrice,$risesInPrice,$trackingID,$quickBuyCount,$market1HrChangePct,$oneTimeBuy);
    echo "<BR> Ready To Buy: $readyToBuy";
    if ($readyToBuy == True){
      $delayCoinPurchase = getDelayCoinPurchaseTimes();
      $totalCoinPurchases = getTotalCoinPurchases();
      $totalCoinPurchasesSize = count($totalCoinPurchases);
      $coinPurchasesPerCoin = getCoinPurchasesByCoin();
      $coinPurchasesPerCoinSize = count($coinPurchasesPerCoin);
      $clearCoinQueueSize = count($clearCoinQueue);
      for ($p=0; $p<$clearCoinQueueSize; $p++){
        if ($coinID == $clearCoinQueue[$p][1] AND $userID == $clearCoinQueue[$p][0]){
          echo "<BR> EXIT: CoinID and USERID in Clear Coin Queue: $coinID | $userID";
          continue;
        }
      }
      for ($u=0;$u<$totalCoinPurchasesSize;$u++){
        for ($r=0;$r<$coinPurchaseSettingsSize;$r++){
            if ($userID == $totalCoinPurchases[$u][0] and $userID == $coinPurchaseSettings[$r][0] and $totalCoinPurchases[$u][1]>=$coinPurchaseSettings[$r][2]){
              echo "<BR> EXIT: User over total Coin Purchases: $coinID | $userID".$totalCoinPurchases[$u][1]."|".$coinPurchaseSettings[$r][2];
              continue;
            }
        }
      }
      for ($e=0;$e<$coinPurchasesPerCoinSize;$e++){
        for ($w=0;$w<$coinPurchaseSettingsSize;$w++){
            if ($userID == $coinPurchasesPerCoin[$e][0] and $userID == $coinPurchaseSettings[$w][0]){
              if($coinID == $coinPurchasesPerCoin[$e][1] ){
                if($coinPurchasesPerCoin[$e][1]>=$coinPurchaseSettings[$w][1]){
                  echo "<BR> EXIT: User over Coin Purchases per Coin: $coinID | $userID".$coinPurchasesPerCoin[$e][1]."|".$coinPurchaseSettings[$w][1];
                  continue;
                }
              }
            }
        }
      }
      if ($type == 'SavingBuy'){
        $transID = $newTrackingCoins[$a][50];
        if (!Empty($KEK)){ $APISecret = Decrypt($KEK,$newTrackingCoins[$a][19]);}
        //$liveCoinPrice = $bitPrice;
        $rate = $liveCoinPrice;
        $quant = $totalAmount/$rate;
        echo"<BR> bittrexbuy($APIKey, $APISecret, $symbol, $ogBTCAmount, $rate, $baseCurrency,3,FALSE);";
        $obj = bittrexbuy($APISecret, $APISecret, $symbol, $ogBTCAmount, $rate, $baseCurrency,3,FALSE);
        $bittrexRef = $obj["id"];
        if ($bittrexRef <> ""){
          Echo "<BR> Bittrex ID: $bittrexRef";
          updateCoinSwapBittrexID($bittrexRef,$transID,$coinID,$liveCoinPrice,'Buy');
          //Change Status to AwaitingBuy
          updateCoinSwapStatus('AwaitingSavingsPurchase',$transID);
        }
      }else{
        newLogToSQL("TrackingCoin","trackingCoinReadyToBuy($liveCoinPrice,$timeToCancelBuyMins,$type,$originalPrice,$newTrackingCoinID,$noOfRisesInPrice,$pctProfit,$minsFromDate,$lastPrice,$risesInPrice,$trackingID,$quickBuyCount,$market1HrChangePct)$coinID|$overrideCoinAlloc|".$coinAllocation[0][0]." | $type | $coinMode;",$userID,$logToSQLSetting,"TrackingSuccess","TrackingCoinID:$newTrackingCoinID");
        if (!Empty($KEK)){ $APISecret = Decrypt($KEK,$newTrackingCoins[$a][19]);}
        //if ($baseCurrency == 'BTC' OR $baseCurrency == 'ETH'){ $ogBTCAmount = (float)$ogBTCAmount;}
        if ($buyAmountCalculationEnabled == 1){
            $ogBTCAmount = ($ogBTCAmount/100)*$pctToBuy;
        }

        $checkBuy = buyCoins($APIKey, $APISecret,$symbol, $Email, $userID, $date, $baseCurrency,$SendEmail,$BuyCoin,$ogBTCAmount, $ruleIDBuy,$UserName,$coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed, $buyCoinPrice, $overrideCoinAlloc,$noOfPurchases+1);
        newLogToSQL("TrackingCoin","buyCoins($APIKey, $APISecret,$symbol, $Email, $userID, $date, $baseCurrency,$SendEmail,$BuyCoin,$ogBTCAmount, $ruleIDBuy,$UserName,$coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed, $buyCoinPrice, $noOfPurchases+1);",$userID,$logToSQLSetting,"BuyCoin","TrackingCoinID:$newTrackingCoinID");
        //logToSQL("TrackingCoin", "buyCoins($APIKey, $APISecret,$symbol, $Email, $userID, $date, $baseCurrency,$SendEmail,$BuyCoin,$ogBTCAmount, $ruleIDBuy,$UserName,$coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed, $buyCoinPrice, $noOfPurchases+1);", $userID,1);
        UpdateProfit();
        //subUSDTBalance('USDT', $BTCAmount,$liveCoinPrice, $userID);
        closeNewTrackingCoin($newTrackingCoinID, False);
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

        updateCoinAllocationOverride($coinID,$userID,$overrideCoinAlloc);
      //continue;
      }
    }
  }
}


//set time
setTimeZone();
$i=0;
$date = date("Y-m-d H:i", time());
$current_date = date('Y-m-d H:i');
$priceDipTimer = date('Y-m-d H:i');
$spreadBetTimer = date('Y-m-d H:i');
$trackingCoinTimer = date('Y-m-d H:i');
$completeFlag = False;
$newTime = date("Y-m-d H:i",strtotime($tmpTime, strtotime($current_date)));
logAction("Buy Sell Coins Start : End set to $newTime : $date", 'BuySellTiming', $logToFileSetting);
$SpreadBetUserSettings = getSpreadBerUserSettings();
while($completeFlag == False){
  echo "<blockquote><BR> CHECK Re-Buy Savings!! $i";
          if ($i == 0){$reBuySavingsFixed = getOpenCoinSwaps();}
          runReBuySavings($reBuySavingsFixed);
  echo "</blockquote><BR> CHECK Sell Savings!! $i<blockquote>";
          if ($i == 0){$spreadBuyBack = getSavingsData();}
          runSellSavings($spreadBuyBack);
  echo "</blockquote><BR>CHECK PriceDip Rule Enable!! $i<blockquote>";
        if (date("Y-m-d H:i", time()) >= $priceDipTimer){
          $PDcurrent_date = date('Y-m-d H:i');
          $priceDipTimer = date("Y-m-d H:i",strtotime("+2 minutes", strtotime($PDcurrent_date)));
          $priceDipRules = getPriceDipRules();
        }
        runPriceDipRule($priceDipRules);
  echo "</blockquote><BR> CHECK BuyBack!! $i<blockquote>";
        if ($i == 0){$buyBackCoins = getBuyBackData();}
        runBuyBack($buyBackCoins);
  echo "</blockquote><BR> CHECK Spreadbet Sell & BuyBack!! $i<blockquote>";
        if ($i == 0){$spreadBuyBack = getSpreadCoinSellDataFixed();}
        runSpreadBetSellAndBuyback($spreadBuyBack);
  echo "</blockquote><BR>CHECK Sell Spread Bet!! $i<blockquote>";
        if($i==0){$sellSpread = getSpreadBetSellData();}
        runSellSpreadBet($sellSpread);
  echo "</blockquote><BR>CHECK Spread Bet!! $i<blockquote>";
        if (date("Y-m-d H:i", time()) >= $spreadBetTimer){
          $SBcurrent_date = date('Y-m-d H:i');
          $spreadBetTimer = date("Y-m-d H:i",strtotime("+3 minutes", strtotime($SBcurrent_date)));
          $spread = getSpreadBetData();
        }
        runSpreadBet($spread,$SpreadBetUserSettings);

  echo "</blockquote><BR> Tracking COINS!! $i<blockquote>";
        if (date("Y-m-d H:i", time()) >= $trackingCoinTimer){
          $TCcurrent_date = date('Y-m-d H:i');
          $trackingCoinTimer = date("Y-m-d H:i",strtotime("+2 minutes", strtotime($TCcurrent_date)));
          $newTrackingCoins = getNewTrackingCoins();
          $marketStats = getMarketstats();
          $baseMultiplier = getBasePrices();
          $ruleProfit = getRuleProfit();
        }
        runNewTrackingCoins($newTrackingCoins,$marketStats,$baseMultiplier,$ruleProfit);

  sleep(20);
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
