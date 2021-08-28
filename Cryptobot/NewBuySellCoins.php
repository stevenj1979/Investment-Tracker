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

function runReBuySavings($reBuySavingsFixed){
  $reBuySavingsFixedSize = count($reBuySavingsFixed);
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
    $sellPrice = $LiveCoinPrice * $amount;$buyPrice = $purchasePrice * $amount;$profit = ($sellPrice)-($buyPrice);$profitPCT = ($profit/($buyPrice))*100;
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
    echo "<BR> $hr24ChangePctChange | $hr24PriceDipPct";
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
          $newCoinSwap = getNewSwapCoin();
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


//set time
setTimeZone();
$date = date("Y-m-d H:i", time());
$current_date = date('Y-m-d H:i');
$completeFlag = False;
$newTime = date("Y-m-d H:i",strtotime($tmpTime, strtotime($current_date)));
logAction("Buy Sell Coins Start : End set to $newTime : $date", 'BuySellTiming', $logToFileSetting);

while($completeFlag == False){
  echo "<blockquote><BR> CHECK Re-Buy Savings!! ";
          if ($i == 0){$reBuySavingsFixed = getOpenCoinSwaps();}
          runReBuySavings($reBuySavingsFixed);
  echo "</blockquote><BR> CHECK Sell Savings!! <blockquote>";
          if ($i == 0){$spreadBuyBack = getSavingsData();}
          runSellSavings($spreadBuyBack);
  echo "</blockquote><BR>CHECK PriceDip Rule Enable!! <blockquote>";
        if ($i == 0){
          $priceDipRules = getPriceDipRules();
          $PDcurrent_date = date('Y-m-d H:i');
          $priceDipTimer = date("Y-m-d H:i",strtotime("+2 minutes", strtotime($PDcurrent_date)));
        }
        if (date("Y-m-d H:i", time()) >= $priceDipTimer){
          $PDcurrent_date = date('Y-m-d H:i');
          $priceDipTimer = date("Y-m-d H:i",strtotime("+2 minutes", strtotime($PDcurrent_date)));
          $priceDipRules = getPriceDipRules();
        }
        runPriceDipRule($priceDipRules);
  echo "</blockquote><BR> CHECK BuyBack!!<blockquote>";
        if ($i == 0){$buyBackCoins = getBuyBackData();}
        runBuyBack($buyBackCoins);
  echo "</blockquote><BR> CHECK Spreadbet Sell & BuyBack!! <blockquote>";
        if ($i == 0){$spreadBuyBack = getSpreadCoinSellDataFixed();}
        runSpreadBetSellAndBuyback($spreadBuyBack);
  echo "</blockquote><BR>CHECK Sell Spread Bet!! <blockquote>";
        if($i==0){$sellSpread = getSpreadBetSellData();}
        runSellSpreadBet($sellSpread);
  echo "</blockquote><BR>CHECK Spread Bet!!<blockquote>";

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
