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



function buyToreduceLoss($lossCoins){
  $finalBool = False;
  $lossCoinsSize = count($lossCoins);
  //$apiVersion = 3; $ruleID = 111111;
  for ($y=0; $y<$lossCoinsSize; $y++){
    $pctProfit = $lossCoins[$y][58]; $transactionID = $lossCoins[$y][0]; $minsToDelay = $lossCoins[$y][60]; $userID = $lossCoins[$y][3]; $coinID = $lossCoins[$y][2];
    $liveCoinPrice = $lossCoins[$y][19]; $baseCurrency = $lossCoins[$y][36]; $totalAmount = $lossCoins[$y][54];
    $reduceLossEnabled = $lossCoins[$y][61]; $reduceLossSellPct = $lossCoins[$y][62]; $reduceLossMultiplier = $lossCoins[$y][63]; $reduceLossCounter = $lossCoins[$y][64]; $reduceLossMaxCounter = $lossCoins[$y][65];
    $hoursFlat = $lossCoins[$y][68];$overrideReduceLoss = $lossCoins[$y][67];
    $holdCoinForBuyOut = $lossCoins[$y][69];
    $coinForBuyOutPct = $lossCoins[$y][70];
    $holdingAmount = $lossCoins[$y][71]; $savingOverride = $lossCoins[$y][72]; $hoursFlatTarget = $lossCoins[$y][73]; $spreadBetTransactionID = $lossCoins[$y][74]; $coinSwapDelayed = $lossCoins[$y][75];

    if ($overrideReduceLoss == 1){
      $finalReduceLoss = 1;
    }elseif ($reduceLossEnabled == 1){
      $finalReduceLoss = 1;
    }else{
      $finalReduceLoss = 0;
    }
    $excludeSpreadBet = 1;
    if ($excludeSpreadBet = 1 and $spreadBetTransactionID <> 0 ){ continue;}
    //and $minsToDelay > 0
    echo "<BR> buyToreduceLoss: $pctProfit : $reduceLossSellPct | $coinSwapDelayed | $transactionID | $userID | $coinID | $liveCoinPrice | $baseCurrency | $totalAmount |$reduceLossEnabled | $reduceLossSellPct | $hoursFlat | $hoursFlatTarget | $overrideReduceLoss | $finalReduceLoss | $reduceLossCounter : $reduceLossMaxCounter";
    if ($pctProfit <= $reduceLossSellPct  and $coinSwapDelayed == 0 AND $finalReduceLoss == 1 AND $reduceLossCounter < $reduceLossMaxCounter AND $hoursFlat >= $hoursFlatTarget){
      if (!isset($pctProfit)){ echo "<BR> PctProfit note set: EXIT! "; continue; }
      echo "<BR> buyToreduceLoss2: $pctProfit |$reduceLossSellPct | $minsToDelay | $reduceLossEnabled";
      //get multiplier
      //$openTransNoAry = getOpenTransNo($userID, $coinID);
      $currentBuy = $reduceLossMultiplier;
      $profitMultiplier = ABS($reduceLossSellPct)/ABS($pctProfit);
      $quant = $totalAmount*($currentBuy*$profitMultiplier);
      echo "<BR> buyToreduceLoss2: 2 | $currentBuy | $quant | $profitMultiplier | $totalAmount";
      newLogToSQL("buyToreduceLoss","addTrackingCoin($coinID, $liveCoinPrice, $userID, $baseCurrency, 1, 1, $quant, 97, 0, 0, 1, 240, 229,1,1,10,'Buy',$liveCoinPrice,0,0,1,'buyToreduceLoss',$transactionID);",3,1,"addTrackingCoin","TransactionID:$transactionID");
      //Buy Coin with Merge
      addTrackingCoin($coinID, $liveCoinPrice, $userID, $baseCurrency, 1, 1, $quant, 97, 0, 0, 1, 240, 229,1,1,10,'Buy',$liveCoinPrice,0,0,1,'buyToreduceLoss',$savingOverride,$transactionID);
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




?>
</html>
