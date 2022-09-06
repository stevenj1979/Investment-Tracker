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
    $market1HrChangePct = $marketStats[0][1]; $reEnableBuyRule = $newTrackingSellCoins[$b][46]; $reEnableBuyRuleEnabled = $newTrackingSellCoins[$b][45]; $trackingCount = $newTrackingSellCoins[$b][48];
    Echo "<BR> Check Sell: Cp: $CoinPrice | TRID: $TransactionID | Am: $Amount ";
    if ($minsFromDate > 1440 and $trackingType == 'SavingsSell'){
      closeNewTrackingSellCoin($TransactionID);
      updateTransStatus($TransactionID,'Saving');
      //addWebUsage($userID,"Remove","SellTracking");
      logAction("runTrackingSellCoin; CancelSavingSell : $coin | $CoinID | $baseCurrency | $userID | $minsFromDate | $TransactionID ", 'BuySellFlow', 1);
      $finalBool = True;
    }

    echo "<BR> Checking $coin : $CoinPrice ; No Of RISES $NoOfRisesInPrice ! Profit % $ProfitPct | Mins from date $minsFromDate ! Original Coin Price $originalCoinPrice | mins from Start: $minsFromStart | UserID : $userID Falls in Price: $fallsInPrice TrackingCount: $trackingCount";
    $readyToSell = trackingCoinReadyToSell($LiveCoinPrice,$minsFromStart,$type,$baseSellPrice,$TransactionID,$totalRisesInPrice,$ProfitPct,$minsFromDate,$lastPrice,$NoOfRisesInPrice,$trackingSellID,$market1HrChangePct,$originalSellPrice);
    if ($readyToSell == 1 OR $trackingCount >= 5){
      $PurchasePrice = ($Amount*$CoinPrice);
      $salePrice = $LiveCoinPrice * $Amount;
      $profit = $newTrackingSellCoins[$b][43];
      $ProfitPct = $newTrackingSellCoins[$b][44];
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
              newLogToSQL("TrackingSell","$PurchasePrice | $sellFee | $LiveCoinPrice | $Amount | $oldAmount",3,1,"SaveResidual","TransactionID:$TransactionID");
            }
            newLogToSQL("TrackingSell","$oldAmount | $Amount | $PurchasePrice | $sellFee | $LiveCoinPrice | $ProfitPct",3,1,"NewAmountToSQL","TransactionID:$TransactionID");
            if ($origAmount == 0){
              updateSellAmount($TransactionID,$Amount, $oldAmount);
              newLogToSQL("TrackingSell","updateSellAmount($TransactionID,$Amount, $oldAmount);",3,1,"SaveResidualCoins4","TransactionID:$TransactionID");
            }

            newLogToSQL("TrackingSell","$coin | $CoinID | $oldAmount | $CoinPrice | $PurchasePrice | $LiveCoinPrice | $Amount | $TransactionID | $tempFee",3,1,"SaveResidualCoins2","TransactionID:$TransactionID");
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
          //addWebUsage($userID,"Remove","SellTracking");
          //addWebUsage($userID,"Add","BittrexAction");
          newLogToSQL("TrackingSell","sellCoins($APIKey, $APISecret,$coin, $Email, $userID, 0,$newOrderDate, $baseCurrency,$SendEmail,$SellCoin, $FixSellRule,$UserName,$OrderNo,$Amount,$CoinPrice,$TransactionID,$CoinID,$CoinSellOffsetEnabled,$CoinSellOffsetPct,$LiveCoinPrice, $type);",3,$GLOBALS['logToSQLSetting'],"Success","TransactionID:$TransactionID");
          addUSDTBalance('USDT', $BTCAmount,$LiveCoinPrice, $userID);
          logAction("runTrackingSellCoin; sellCoins : $coin | $CoinID | $baseCurrency | $LiveCoinPrice | $CoinPrice | $Amount | $userID | $minsFromDate | $type | $TransactionID", 'BuySellFlow', 1);
          if ($saveResidualCoins == 1){
            $finalResidual = ($oldAmount-$Amount);
            newLogToSQL("TrackingSell:Residual","$finalResidual = ($oldAmount-$Amount);",3,1,"ResidualAmount","TransactionID:$TransactionID");
            saveResidualAmountToBittrex($TransactionID,$finalResidual);
          }
          if ($reEnableBuyRuleEnabled == 1){ buySellProfitEnable($CoinID,$userID,1,1,20,$FixSellRule);}
        }
      }
      $finalBool = True;
    }elseif ($readyToSell == 2){
      $finalBool = True;
    }

  }
  return $finalBool;
}


?>
</html>
