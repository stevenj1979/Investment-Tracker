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
    $KEK = $BittrexReqs[$b][25]; $Day7Change = $BittrexReqs[$b][26]; $minsSinceAction = $BittrexReqs[$b][37]; $timeToCancelMins = $BittrexReqs[$b][57]; $buyBack = $BittrexReqs[$b][39];
    $oldBuyBackTransID = $BittrexReqs[$b][40]; $newResidualAmount = $BittrexReqs[$b][41]; $mergeSavingwithPurchase = $BittrexReqs[$b][42]; $buyBackEnabled = $BittrexReqs[$b][43];
    $pauseCoinIDAfterPurchaseEnabled  = $BittrexReqs[$b][45]; $daysToPauseCoinIDAfterPurchase = $BittrexReqs[$b][46]; $btc_Price = $BittrexReqs[$b][47]; $eth_Price = $BittrexReqs[$b][48];
    $multiSellRuleEnabled = $BittrexReqs[$b][49]; $multiSellRuleTemplateID = $BittrexReqs[$b][50]; $stopBuyBack = $BittrexReqs[$b][51]; $multiSellRuleID = $BittrexReqs[$b][52]; $typeBA = $BittrexReqs[$b][53];
    $reduceLossBuy = $BittrexReqs[$b][54]; $BittrexID = $BittrexReqs[$b][55]; $minsRemaining = $BittrexReqs[$b][58]; $lowMarketMode = $BittrexReqs[$b][59]; $holdCoinForBuyOut = $BittrexReqs[$b][60];
    $coinForBuyOutPct = $BittrexReqs[$b][61]; $holdingAmount = $BittrexReqs[$b][62]; $noOfPurchases = $BittrexReqs[$b][63]; $hr1PriceMovePct = $BittrexReqs[$b][64]; $pctToCancelBittrexAction = $BittrexReqs[$b][65];
    $pctFromSale = $BittrexReqs[$b][66]; $liveProfitPct = $BittrexReqs[$b][67]; $oneTimeBuy = $BittrexReqs[$b][68]; $cancelTimeCheck = $BittrexReqs[$b][69];
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
        newLogToSQL("CheckOldTransIDBuy","$oldBuyBackTransID | $multiSellRuleTemplateID | $reduceLossBuy",3,1,"RunBittrex","TransID:$transactionID");
        if ($orderIsOpen != 1 && $cancelInit != 1 && $orderQtyRemaining == 0){
          //sendtoSteven($transactionID,$orderQtyRemaining."_".$qtySold."_".$orderQty, $orderNo."_".$finalPrice."_".$liveCoinPriceBit, "BUY - OrderIsOpen != 1 & CancelInitiated != 1");
          if ($sendEmail){
            $subject = "Coin Purchase1: ".$coin;
            $from = 'Coin Purchase <purchase@investment-tracker.net>';
            sendEmail($email, $coin, $amount, $finalPrice, $orderNo, $totalScore, $subject,$userName,$from);
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
          $finalBool = True;
        }elseif ($orderIsOpen != 1 && $cancelInit != 1 && $orderQty <> $orderQtyRemaining && $finalBool == False){
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
          //addWebUsage($userID,"Remove","BittrexAction");
          logAction("runBittrex; bittrexBuyCompletePartial : $coin | $type | $baseCurrency | $userID | $liveCoinPriceBit | $coinID | $type | $finalPrice | $amount | $userID | $uuid | $orderQty | $transactionID", 'BuySellFlow', 1);
          $finalBool = True;
        }
        //if ( substr($timeSinceAction,0,4) == $buyCancelTime){
        //if ( $buyOrderCancelTime < date("Y-m-d H:i:s", time()) && $buyOrderCancelTime != '0000-00-00 00:00:00'){
        echo "<BR> Cancel Check: $cancelTimeCheck | $minsRemaining | $finalBool";
        if ( $cancelTimeCheck == 1 && $finalBool == False){
          echo "<BR>CANCEL time exceeded! CANCELLING! $minsRemaining | $BittrexID";
          newLogToSQL("BittrexBuyCancel", "Order time exceeded for OrderNo: $orderNo Cancel order completed | $minsRemaining | $BittrexID", $userID, 1,"FullOrder","TransactionID:$transactionID");
          if ($orderQty == $orderQtyRemaining){
             $cancelRslt = bittrexCancel($apiKey,$apiSecret,$uuid,$apiVersion);
             //var_dump($cancelRslt);
             $canStatus = $cancelRslt['status']; $errorCode = $cancelRslt['code'];
             echo "<BR> Cancelling: bittrexCancel($apiKey,$apiSecret,$uuid,$apiVersion); $canStatus";
             if ($canStatus == 'CLOSED' OR $errorCode == "ORDER_NOT_OPEN"){
               bittrexBuyCancel($uuid, $transactionID, "CancelMins: $minsSinceAction");
               logAction("runBittrex; bittrexBuyCancelFull : $coin | $type | $baseCurrency | $userID | $liveCoinPriceBit | $coinID | $type | $finalPrice | $amount | $userID | $uuid | $orderQty | $transactionID | $minsSinceAction | $timeToCancelMins", 'BuySellFlow', 1);
               newLogToSQL("BittrexBuyCancel", "Order time exceeded for OrderNo: $orderNo Cancel order completed", $userID, 1,"FullOrder","TransactionID:$transactionID");
               reopenCoinSwapCancel($BittrexID,1);
               removeTransactionDelay($coinID, $userID);
             }else{
               logAction("bittrexCancelBuyOrder: ".$cancelRslt, 'Bittrex', $GLOBALS['logToFileSetting'] );
               newLogToSQL("BittrexBuyCancel", "Order time exceeded for OrderNo: $orderNo Cancel order Error: $cancelRslt | $timeToCancelMins | $minsSinceAction", $userID, 1,"FullOrder","TransactionID:$transactionID");
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
              //addWebUsage($userID,"Add","SellCoin");
              //addBuyRuletoSQL($transactionID, $ruleIDBTBuy);
            }else{ logAction("bittrexCancelBuyOrder: ".$result, 'Bittrex', $GLOBALS['logToFileSetting'] );}
          }
          addUSDTBalance('USDT',$amount*$finalPrice,$finalPrice,$userID);
          if ($buyBack == 1){ reopenCoinSwapCancel($BittrexID,1); }
          $finalBool = True;
          //($userID,"Remove","BittrexAction");
          //reOpenBuySellProfitRule($ruleIDBTBuy,$userID,$coinID);
        }
      }elseif (($type == "Sell" && $finalBool == False)or ($type == "SpreadSell" && $finalBool == False) or ($type == "SavingsSell" && $finalBool == False) ){ // $type Sell
        //logToSQL("Bittrex", "Sell Order | OrderNo: $orderNo Final Price: $finalPrice | $orderIsOpen | $cancelInit | $orderQtyRemaining", $userID, $GLOBALS['logToSQLSetting']);
        echo "<BR> SELL TEST: $orderIsOpen | $cancelInit | $orderQtyRemaining | $amount | $finalPrice | $uuid";
        newLogToSQL("BittrexSell", "$type | $orderIsOpen | $cancelInit | $orderQtyRemaining | $amount| $finalPrice | $uuid", $userID,0,"SellComplete","TransactionID:$transactionID");
        echo "<BR> Pct From Sale: $pctFromSale Lice Profit Pct: $liveProfitPct Cancel Sale Pct Target: $pctToCancelBittrexAction Days Outstanding: $daysOutstanding";
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
            newLogToSQL("BittrexSell", "$finalPrice | $amount | $sellPrice | $cost | $originalAmount | $buyPrice | $fee | $profit | $profitPct | $realSellPrice | $realProfitPct", $userID, 0,"OriginalPrice","TransactionID:$transactionID");
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
              newLogToSQL("BittrexSell", "bittrexSellComplete($uuid, $transactionID, $finalPrice); $originalAmount ", $userID, 1,"bittrexSellComplete","TransactionID:$transactionID");
              bittrexSellComplete($uuid, $transactionID, $finalPrice); //add sell price - $finalPrice
              ////addWebUsage($userID,"Remove","SellCoin");
              extendPctToBuy($coinID,$userID);
              $allocationType = 'Standard';
              if ($type == 'SpreadSell'){ $allocationType = 'SpreadBet';}elseif ($coinModeRule >0){$allocationType = 'CoinMode';}
              if ($saveMode == 1 AND $profitPct > 0.25){
                $newProfit = ($profit / 100)*$pctToSave;
                addProfitToAllocation($userID, $newProfit,$saveMode, $baseCurrency);
              }elseif ($saveMode == 2 AND $profitPct > 0.25){
                //$newProfit = ($profit / 100)*$pctToSave;
                $newProfit = $profit;
                addProfitToAllocation($userID, $newProfit,$saveMode, $baseCurrency);
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

                newLogToSQL("BittrexSell", "WriteBuyBack($transactionID,$realProfitPct,10, 60,$finalPrice,$amount,$cost,$usd_Amount);", $userID, 1,"BuyBack","TransactionID:$transactionID");
                if ($buyBackEnabled == 1){
                  if ($stopBuyBack == 0){
                    $tempRises = floor(($hr1PriceMovePct + 30)/5)+5;
                    $tempmins = floor(100-(($hr1PriceMovePct/60)*100));
                    if ($tempRises <= 0){ $tempRises = 2;}
                    if ($tempmins <= 0){ $tempmins = 120;}
                    WriteBuyBack($transactionID,$realProfitPct,$tempRises, $tempmins,$finalPrice,$amount,$cost,$usd_Amount);
                    //addWebUsage($userID,"Add","BuyBack");
                  }
                }
              }else{
                //Update Coin ModeRule
                $buyTrendPct = updateBuyTrendHistory($coinID,$orderDate);
                $Hr1Trnd = $buyTrendPct[0][0]; $Hr24Trnd = $buyTrendPct[0][1]; $d7Trnd = $buyTrendPct[0][2];
                newLogToSQL("BittrexSell", "updateBuyTrend($coinID, $transactionID, CoinMode, $ruleIDBTBuy, $Hr1Trnd,$Hr24Trnd,$d7Trnd);", $userID, $GLOBALS['logToSQLSetting'],"updateBuyTrend","TransactionID:$transactionID");
                updateBuyTrend($coinID, $transactionID, 'CoinMode', $ruleIDBTBuy, $Hr1Trnd,$Hr24Trnd,$d7Trnd);
                newLogToSQL("BittrexSell", "WriteBuyBack($transactionID,$realProfitPct,10, 60,$finalPrice,$amount,$cost,$usd_Amount);", $userID, 1,"BuyBack","TransactionID:$transactionID");
                if ($buyBackEnabled == 1){WriteBuyBack($transactionID,$realProfitPct,10, 60,$finalPrice,$amount,$cost,$usd_Amount);}
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
            $finalBool = True;
        }
        if ($daysOutstanding <= -28){
          echo "<BR>days from sale! $daysOutstanding CANCELLING!";
          if ($orderQtyRemaining == $orderQty){
            //complete sell update amount
            $cancelRslt = bittrexCancel($apiKey,$apiSecret,$uuid,$apiVersion);
            $canStatus = $cancelRslt['status'];
            if ($canStatus == 'CLOSED'){
              bittrexSellCancel($uuid, $transactionID, "DaysOutstanding: $daysOutstanding");
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
          //addWebUsage($userID,"Remove","BittrexAction");
          //addWebUsage($userID,"Add","SellCoin");
          subUSDTBalance('USDT',$amount*$finalPrice,$finalPrice,$userID);
        }
        if (($pctFromSale <= $pctToCancelBittrexAction && $finalBool == False) or ($pctFromSale >= 4 && $finalBool == False)){
          if ($type == 'SpreadSell') { continue;}
          echo "<BR>% from sale! $pctFromSale CANCELLING!";
          if ($orderQtyRemaining == $orderQty){
            $cancelRslt = bittrexCancel($apiKey,$apiSecret,$uuid,$apiVersion);
            if($apiVersion == 1){ $canResStatus = $cancelRslt;}
            else{ if ($cancelRslt['status'] == 'CLOSED'){$canResStatus = 1;}else{$canResStatus =0;}}
            if ($canResStatus == 1){
              bittrexSellCancel($uuid, $transactionID, "PctFromSale: $pctFromSale CancelAction: $pctToCancelBittrexAction");
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
    echo "<br>Time Since Action $minsRemaining ".$BittrexReqs[$b][57]." | ".$BittrexReqs[$b][58]." | ".$BittrexReqs[$b][59];

    echo "<BR> ORDERQTY: $orderQty - OrderQTYREMAINING: $orderQtyRemaining";
  }//Bittrex Loop
  return $finalBool;
}





?>
</html>
