<?php
include_once ('/home/stevenj1979/SQLData.php');

Define("sQLUpdateLog","0");
Define("SQLProcedureLog","0");
Define("SQLAdvancedLog","0");
Define("logToSQLSetting","0");

function getBittrexRequests($userID = 0){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  $bittrexQueue = "";
  if ($userID <> 0){$bittrexQueue = " and `UserIDBa` = $userID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Type`,`BittrexRefBa` as `BittrexRef`,`ActionDate`,`CompletionDate`,`Status`,`SellPrice`,`UserName`,`APIKey`,`APISecret`,`Symbol`,`Amount`,`CoinPrice`,`UserIDBa`,`Email`,`OrderNo`,`TransactionID`,`BaseCurrency`,`BuyRule`,`DaysOutstanding`,`timeSinceAction`
  ,`CoinID4`,`RuleIDSell`,`LiveCoinPrice`,`TimetoCancelBuy`,`BuyOrderCancelTime`,`KEK`,`Live7DChange`,`CoinModeRule`,`OrderDate`,`PctToSave`,`SpreadBetRuleID`,`SpreadBetTransactionID`,`RedirectPurchasesToSpread`,`RedirectPurchasesToSpreadID` as`SpreadBetRuleIDRedirect`
  ,`MinsToPauseAfterPurchase`,`OriginalAmount`,`SaveResidualCoins`,`MinsSinceAction`,`TimeToCancelBuyMins`,`BuyBack`,`OldBuyBackTransID`,`ResidualAmount`,`MergeSavingWithPurchase`,`BuyBackEnabled`,`SaveMode`, `PauseCoinIDAfterPurchaseEnabled`, `DaysToPauseCoinIDAfterPurchase`
  ,getBTCPrice(84) as BTCPrice,getBTCPrice(85) as ETHPrice,`MultiSellRuleEnabled`,`MultiSellRuleTemplateID`,`StopBuyBack`,`MultiSellRuleID`,`TypeBa`,`ReduceLossBuy`,`IDBa`,IfNull(`BuyOrderCancelTimeMins`,0) as BuyOrderCancelTimeMins,`MinsToCancelAction`,`MinsRemaining`,`LowMarketModeEnabled`,`HoldCoinForBuyOut`
  ,`CoinForBuyOutPct`,`holdingAmount`,`NoOfPurchases`,(((`LiveCoinPrice`-`Live1HrChange`))/`LiveCoinPrice`)*100 as Hr1PriceMovePct,`PctToCancelBittrexAction`,((`LiveCoinPrice`-`SellPrice`)/`SellPrice`)*100 as PctFromSale, ((`LiveCoinPrice`-`CoinPrice`)/`CoinPrice`)*100 as LiveProfitPct
  ,`OneTimeBuyRuleBr`,`DateADD`,`timeToCancel`,`OverrideBittrexCancellation`,`Image`,now() as `CurrentTime`, TIMESTAMPDIFF(MINUTE,`ActionDate`,NOW()) as MinsFromAction,`OverrideBBAmount`, `OverrideBBSaving`,`OverrideBuyBackAmount` as OverrideBuyBackAmountSR,`OverrideBuyBackSaving` as OverrideBuyBackSavingSR
  ,`BuyBackMinsToCancel`,`TimeToCancelBa`,`TimeStampNow`,`TimeStampTimeToCancel`,`BuyBackCounter`,`BuyBackMax`
  FROM `View4_BittrexBuySell`
  where (`StatusBa` = '1') $bittrexQueue order by `ActionDate` desc";
  $conn->query("SET time_zone = '-07:00';");
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['Type'],	$row['BittrexRef'],	$row['ActionDate'],	$row['CompletionDate'],	$row['Status'],	$row['SellPrice'],	$row['UserName'],	$row['APIKey'],	$row['APISecret'],	$row['Symbol']//9
        ,	$row['Amount'],	$row['CoinPrice'],	$row['UserIDBa'],	$row['Email'],	$row['OrderNo'],	$row['TransactionID'],	$row['BaseCurrency'],	$row['BuyRule'],	$row['DaysOutstanding'],	$row['timeSinceAction'] //19
        ,	$row['CoinID4'],	$row['RuleIDSell'],	$row['LiveCoinPrice'],	$row['TimetoCancelBuy'],	$row['BuyOrderCancelTime'],	$row['KEK'],	$row['Live7DChange'],	$row['CoinModeRule'],	$row['OrderDate'],	$row['PctToSave']//29
        ,	$row['SpreadBetRuleID'],	$row['SpreadBetTransactionID'],	$row['RedirectPurchasesToSpread'], $row['SpreadBetRuleIDRedirect'],	$row['MinsToPauseAfterPurchase'],	$row['OriginalAmount']//35
        ,	$row['SaveResidualCoins'],	$row['MinsSinceAction'],	$row['TimeToCancelBuyMins'],	$row['BuyBack'],	$row['OldBuyBackTransID'],	$row['ResidualAmount'],	$row['MergeSavingWithPurchase'],	$row['BuyBackEnabled'],	$row['SaveMode']//44
        ,	$row['PauseCoinIDAfterPurchaseEnabled'],	$row['DaysToPauseCoinIDAfterPurchase'],	$row['BTCPrice']	,$row['ETHPrice'],	$row['MultiSellRuleEnabled'],	$row['MultiSellRuleTemplateID'],	$row['StopBuyBack'],	$row['MultiSellRuleID']//52
        ,	$row['TypeBa'],	$row['ReduceLossBuy'],	$row['IDBa'],	$row['BuyOrderCancelTimeMins'],	$row['MinsToCancelAction'],	$row['MinsRemaining'],	$row['LowMarketModeEnabled'],	$row['HoldCoinForBuyOut'],	$row['CoinForBuyOutPct']//61
        ,	$row['holdingAmount'],	$row['NoOfPurchases'],	$row['Hr1PriceMovePct'],	$row['PctToCancelBittrexAction'],	$row['PctFromSale'],	$row['LiveProfitPct'],	$row['OneTimeBuyRuleBr'],	$row['DateADD'],	$row['timeToCancel'] //70
        ,	$row['OverrideBittrexCancellation'],$row['Image'],$row['CurrentTime'],$row['MinsFromAction'],$row['OverrideBBAmount'],$row['OverrideBBSaving'],$row['OverrideBuyBackAmountSR'],$row['OverrideBuyBackSavingSR'] //78
        ,$row['BuyBackMinsToCancel'],$row['TimeToCancelBa'],$row['TimeStampNow'],$row['TimeStampTimeToCancel'],$row['BuyBackCounter'],$row['BuyBackMax']); //84
  }
  $conn->close();
  return $tempAry;
}

function getCancelTime($transactionID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));

  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `TimeStampNow`,`TimeStampTimeToCancel`
  FROM `View4_BittrexBuySell`
  where `TransactionID` = $transactionID";
  $conn->query("SET time_zone = '+04:00';");
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['TimeStampNow'],	$row['TimeStampTimeToCancel']); //2
  }
  $conn->close();
  return $tempAry;
}

function setSavingToLivewithMerge($userID, $coinID, $transactionID){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
        $sql = "call savingToLivewithMerge($coinID,$userID,$transactionID);";

    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("setSavingToLivewithMerge",$sql,3,1,"SQL","CoinID:$coinID");
    logAction("setSavingToLivewithMerge: ".$sql, 'BuySell', 0);
}

function runClosedCalculatedSellPct(){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
        $sql = "Delete `Csp`
                  FROM `CalculatedSellPct` `Csp`
                  join `Transaction` `Tr` on `Csp`.`TransactionID` = `Tr`.`ID`
                  Where `Tr`.`Status` in ('Closed','Sold')";

    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("runClosedCalculatedSellPct",$sql,3,0,"SQL","");
    logAction("runClosedCalculatedSellPct: ".$sql, 'BuySell', 0);
}

function saveHoldingAmount($userID, $holdAmount,$baseCurrency,$transactionID){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
        $sql = "call updateHoldingAmount($userID,'$baseCurrency',$holdAmount,$transactionID);";

    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("saveHoldingAmount",$sql,3,1,"SQL","UserID:$userID");
    logAction("saveHoldingAmount: ".$sql, 'BuySell', 0);
}

function removeHoldingAmount($userID, $holdAmount,$baseCurrency,$transactionID){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
        $sql = "call RemoveHoldingAmount($userID,'$baseCurrency',$holdAmount,$transactionID);";

    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("removeHoldingAmount",$sql,3,1,"SQL","UserID:$userID");
    logAction("removeHoldingAmount: ".$sql, 'BuySell', 0);
}

function removeFromSpread($transID){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
        $sql = "UPDATE `Transaction` SET `Status` = 'Open', `SpreadBetTransactionID` = 0,`SpreadBetRuleID` = 0, `Type` = 'Sell' where  `ID` = $transID;";

    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("removeFromSpread",$sql,3,1,"SQL","TransID:$transID");
    logAction("removeFromSpread: ".$sql, 'BuySell', 0);
}

function pausePurchases($UserID){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `User` SET `DisableUntil`= date_add(now(),Interval (select `MinsToPauseAfterPurchase` from `UserConfig` WHERE `ID` = $UserID) MINUTE) WHERE `ID` = $UserID";
    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("pausePurchases",$sql,3,0,"SQL","UserID:$UserID");
    logAction("pausePurchases: ".$sql, 'BuySell', 0);
}

function saveResidualAmountToBittrex($TransactionID,$residualAmount){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `BittrexAction` SET `ResidualAmount`= $residualAmount WHERE `TransactionID` = $TransactionID and `Type` in ('Sell','SpreadSell')";
    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("saveResidualAmountToBittrex",$sql,3,1,"SQL","UserID:$UserID");
    logAction("saveResidualAmountToBittrex: ".$sql, 'BuySell', 0);
}

function reopenCoinSwapCancel($transID, $nFlag){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    if ($nFlag == 1){
        $sql = "UPDATE `BuyBack` SET `Status` = 'Open', `DelayTime` = date_add(now(), INTERVAL 120 Minute)  WHERE `TransactionID` = (SELECT `OldBuyBackTransID` FROM `BittrexAction` WHERE `ID` = $transID) and `TransactionID` <> 0";
    }else{
      $sql = "UPDATE `BuyBack` SET `Status` = 'Open', `DelayTime` = date_add(now(), INTERVAL 120 Minute) WHERE `TransactionID` = $transID and `TransactionID` <> 0";
    }

    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("reopenCoinSwap",$sql,3,0,"SQL","TransID:$transID");
    logAction("reopenCoinSwap: ".$sql, 'BuySell', 0);
}

function removeTransactionDelay($coinID, $userID){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `Transaction` SET `DelayCoinSwapUntil` = now() where `Status` = 'Open' and `UserID` = $userID and `CoinID` = $coinID and `DelayCoinSwapUntil` > now() Order by `CoinPrice` Desc Limit 1";
    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("removeTransactionDelay",$sql,3,0,"SQL","TransID:$transID");
    logAction("removeTransactionDelay: ".$sql, 'BuySell', 0);
}

function bittrexActionBuyBack($coinID,$oldBuyBackTransID,$buyBack = 1){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `BittrexAction` SET `BuyBack` = $buyBack, `oldBuyBackTransID` = $oldBuyBackTransID, `MultiSellRuleID` = (SELECT `MultiSellRuleTemplateID` FROM `Transaction` WHERE `ID` = $oldBuyBackTransID)
             where `CoinID` = $coinID order by `ID` desc limit 1 ";
    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("bittrexActionBuyBack",$sql,3,1,"SQL","TransID:$transID");
    logAction("bittrexActionBuyBack: ".$sql, 'BuySell', 0);
}

function bittrexActionReduceLoss($coinID,$trackingID){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `BittrexAction` SET `ReduceLossBuy`  = 1, `OldBuyBackTransID` = (SELECT `TransactionID` FROM `TrackingCoins` WHERE `ID` = $trackingID) where `CoinID` = $coinID order by `ID` desc limit 1 ";
    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("bittrexActionReduceLoss",$sql,3,1,"SQL","TransID:$oldBuyBackTransID");
    logAction("bittrexActionReduceLoss: ".$sql, 'BuySell', 0);
}

function clearTrackingCoinQueue($UserID,$coinID){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `TrackingCoins` SET `Status` = 'Closed' where `CoinID` = $coinID and `UserID` = $UserID and `Type` <> 'BuyBack'";
    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("clearTrackingCoinQueue",$sql."|".$conn->error,3,1,"SQL","UserID:$UserID; CoinID:$coinID");
    logAction("clearTrackingCoinQueue: ".$sql, 'BuySell', 0);
}


function deleteFromBittrexAction($bittrexRef){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `BittrexAction` SET `Status`= 'Closed', `CompletionDate` = NOW() WHERE `BittrexRef`= '$bittrexRef'";
    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("deleteFromBittrexAction",$sql,3,sQLUpdateLog,"SQL","BittrexRef:$bittrexRef");
    logAction("deleteFromBittrexAction: ".$sql, 'BuySell', 0);
}

function updateBuyToSpread($sbRuleID, $transactionID){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `Transaction` SET `Type` = 'SpreadBuy', `SpreadBetRuleID` = $sbRuleID, `SpreadBetTransactionID` = (SELECT `ID` FROM `SpreadBetTransactions` WHERE `SpreadBetRuleID` = $sbRuleID ) where `ID` = $transactionID";
    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("updateBuyToSpread",$sql,3,sQLUpdateLog,"SQL","SBRuleID:$sbRuleID TransID:$transactionID");
    logAction("updateBuyToSpread: ".$sql, 'SQL_UPDATE', 0);
}

function updateSQLSold($amount,$livePrice, $cost, $date, $transactionID,$profit){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    if ($baseCurrency == "BTC"){
        $sql = "UPDATE `Transaction` SET `Status` = 'Sold', `SellPrice` = $livePrice, `Profit` =  $profit, `DateSold` = '$date' WHERE `ID` = $transactionID";
    }
    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("updateSQLSold",$sql,3,sQLUpdateLog,"SQL","ransID:$transactionID");
    logAction("updateSQLSold: ".$sql, 'SQL_UPDATE', 0);
}

function addCoinPurchaseDelay($coinID,$userID,$days, $daysEnabled){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
        $sql = "call addCoinPurchaseDelay($coinID,$userID,$days,$daysEnabled);";

    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("addCoinPurchaseDelay",$sql,3,1,"SQL","CoinID:$coinID");
    logAction("addCoinPurchaseDelay: ".$sql, 'BuySell', 0);
}

function bittrexOrder($apikey, $apisecret, $uuid, $versionNum){
    $nonce=time();
    if ($versionNum == 1){
      $uri='https://bittrex.com/api/v1.1/account/getorder?apikey='.$apikey.'&uuid='.$uuid.'&nonce='.$nonce;
      //echo "<br>$uri<br>";
      $sign=hash_hmac('sha512',$uri,$apisecret);
      $ch = curl_init($uri);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $execResult = curl_exec($ch);
      $obj = json_decode($execResult, true);
      //$balance = $obj["result"]["IsOpen"];
    }elseif ($versionNum == 3){
      $timestamp = time()*1000;
      $url = "https://api.bittrex.com/v3/orders/{".$uuid."}";
      $method = "GET";
      $content = '';
      $subaccountId = "";
      $contentHash = hash('sha512', $content);
      $preSign = $timestamp . $url . $method . $contentHash . $subaccountId;
      $signature = hash_hmac('sha512', $preSign, $apisecret);

      $headers = array(
      "Accept: application/json",
      "Content-Type: application/json",
      "Api-Key: ".$apikey."",
      "Api-Signature: ".$signature."",
      "Api-Timestamp: ".$timestamp."",
      "Api-Content-Hash: ".$contentHash.""
      );

      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($ch, CURLOPT_HEADER, FALSE);
      $execResult = curl_exec($ch);
      curl_close($ch);
      $obj = json_decode($execResult, true);
      //echo "<BR> URL : $url";
      //newLogToSQL("CoinSwap",var_dump($obj),3,0,"bittrexOrder","BittrexID:$uuid");
      //var_dump($obj);
    }

    return $obj;
}

function getTrackingCoins($whereclause, $table){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "SELECT `IDCn`,`Symbol`,`LiveBuyOrders`,`LastBuyOrders`,`BuyOrdersPctChange`,`LiveMarketCap`,`LastMarketCap`,`MarketCapPctChange`,`Live1HrChange`,`Last1HrChange`,`Hr1ChangePctChange`,`Live24HrChange`,`Last24HrChange`,`Hr24ChangePctChange`,`Live7DChange`,`Last7DChange`
    ,`D7ChangePctChange`,Trim(`LiveCoinPrice`)+0 as LiveCoinPrice,Trim(`LastCoinPrice`)+0 as LastCoinPrice,`CoinPricePctChange`,`LiveSellOrders`,`LastSellOrders`,`SellOrdersPctChange`,`LiveVolume`,`LastVolume`,`VolumePctChange`,`BaseCurrency`,`Price4Trend`,`Price3Trend`, `LastPriceTrend`, `LivePriceTrend`,`1HrPriceChangeLive`
    ,`1HrPriceChangeLast`,`1HrPriceChange3`,`1HrPriceChange4`,`SecondstoUpdate`,`LastUpdated`,`Name`,`Image`,`DoNotBuy`,`HoursFlatPdcs`,`MinPriceFromLow`,`PctFromLiveToLow`,Trim(`Month6Low`)+0 as 6MonthPrice ,Trim(`Month3Low`)+0 as 3MonthPrice,Trim(`AverageLowPrice`)+0 as AverageLowPrice,`HoursSinceAdded`
    ,`MaxHoursFlat`,`CaaOffset`,`CaaMinsToCancelBuy`,`HoursFlatHighPdcs`,`HoursFlatLowPdcs`
    $table $whereclause ";
    echoAndLog("", "$sql",3,0,"","");
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['IDCn'],$row['Symbol'],$row['LiveBuyOrders'],$row['LastBuyOrders'],$row['BuyOrdersPctChange'],$row['LiveMarketCap'],$row['LastMarketCap'],$row['MarketCapPctChange'],$row['Live1HrChange'],$row['Last1HrChange'],$row['Hr1ChangePctChange'] //10
    ,$row['Live24HrChange'],$row['Last24HrChange'],$row['Hr24ChangePctChange'],$row['Live7DChange'],$row['Last7DChange'],$row['D7ChangePctChange'],$row['LiveCoinPrice'],$row['LastCoinPrice'],$row['CoinPricePctChange'],$row['LiveSellOrders'],$row['LastSellOrders']//21
    ,$row['SellOrdersPctChange'],$row['LiveVolume'],$row['LastVolume'],$row['VolumePctChange'],$row['BaseCurrency'],$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['1HrPriceChangeLive'],$row['1HrPriceChangeLast'],$row['1HrPriceChange3'] //33
    ,$row['1HrPriceChange4'],$row['SecondstoUpdate'],$row['LastUpdated'],$row['Name'],$row['Image'],$row['DoNotBuy'],$row['HoursFlatPdcs'],$row['MinPriceFromLow'],$row['PctFromLiveToLow'],$row['6MonthPrice'],$row['3MonthPrice'],$row['AverageLowPrice'] //45
    ,$row['HoursSinceAdded'],$row['MaxHoursFlat'],$row['CaaOffset'],$row['CaaMinsToCancelBuy'],$row['HoursFlatHighPdcs'],$row['HoursFlatLowPdcs']);//51
  }
  $conn->close();
  return $tempAry;
}

function getSpreadBetTrackingCoins($whereclause, $table){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "SELECT `IDCn`,`Symbol`,sum(`LiveBuyOrders`) as LiveBuyOrders,sum(`LastBuyOrders`) as LastBuyOrders,sum(`BuyOrdersPctChange`) as BuyOrdersPctChange,sum(`LiveMarketCap`) as LiveMarketCap,sum(`LastMarketCap`) as LastMarketCap,sum(`MarketCapPctChange`) as MarketCapPctChange,sum(`Live1HrChange`) as Live1HrChange
    ,sum(`Last1HrChange`) as Last1HrChange,sum(`Hr1ChangePctChange`) as Hr1ChangePctChange ,sum(`Live24HrChange`) as Live24HrChange,sum(`Last24HrChange`) as Last24HrChange,sum(`Hr24ChangePctChange`) as Hr24ChangePctChange,sum(`Live7DChange`) as Live7DChange,sum(`Last7DChange`) as Last7DChange
    ,sum(`D7ChangePctChange`) as D7ChangePctChange,Trim(sum(`LiveCoinPrice`))+0 as LiveCoinPrice,Trim(sum(`LastCoinPrice`))+0 as LastCoinPrice,sum(`CoinPricePctChange`) as CoinPricePctChange,sum(`LiveSellOrders`) as LiveSellOrders,sum(`LastSellOrders`) as LastSellOrders,sum(`SellOrdersPctChange`) as SellOrdersPctChange
    ,sum(`LiveVolume`) as LiveVolume,sum(`LastVolume`) as LastVolume,sum(`VolumePctChange`) as VolumePctChange,`BaseCurrency`,avg(`Price4Trend`) as Price4Trend,avg(`Price3Trend`) as Price3Trend, avg(`LastPriceTrend`) as LastPriceTrend, avg(`LivePriceTrend`) as LivePriceTrend,sum(`1HrPriceChangeLive`) as 1HrPriceChangeLive
    ,sum(`1HrPriceChangeLast`) as 1HrPriceChangeLast,sum(`1HrPriceChange3`) as 1HrPriceChange3,sum(`1HrPriceChange4`) as 1HrPriceChange4,avg(`SecondstoUpdate`) as SecondstoUpdate,`LastUpdated`,`Name`,`Image`,`DoNotBuy`,avg(`HoursFlatPdcs`) as HoursFlatPdcs,sum(`MinPriceFromLow`) as MinPriceFromLow
    ,avg(`PctFromLiveToLow`) as PctFromLiveToLow,Trim(sum(`Month6Low`))+0 as 6MonthPrice ,Trim(sum(`Month3Low`))+0 as 3MonthPrice,Trim(sum(`AverageLowPrice`))+0 as AverageLowPrice,Max(`HoursSinceAdded`) as HoursSinceAdded
    ,avg(`MaxHoursFlat`) as MaxHoursFlat,avg(`CaaOffset`) as CaaOffset,avg(`CaaMinsToCancelBuy`) as CaaMinsToCancelBuy,avg(`HoursFlatHighPdcs`) as HoursFlatHighPdcs,avg(`HoursFlatLowPdcs`) as HoursFlatLowPdcs
    ,`Sbc`.`SpreadBetRuleID` as SpreadBetRuleID
    $table
    join `SpreadBetCoins` `Sbc` on `Sbc`.`CoinID` =  `IDCn`
    $whereclause";
    //echo "<BR> $sql <BR>";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['IDCn'],$row['Symbol'],$row['LiveBuyOrders'],$row['LastBuyOrders'],$row['BuyOrdersPctChange'],$row['LiveMarketCap'],$row['LastMarketCap'],$row['MarketCapPctChange'],$row['Live1HrChange'],$row['Last1HrChange'],$row['Hr1ChangePctChange'] //10
    ,$row['Live24HrChange'],$row['Last24HrChange'],$row['Hr24ChangePctChange'],$row['Live7DChange'],$row['Last7DChange'],$row['D7ChangePctChange'],$row['LiveCoinPrice'],$row['LastCoinPrice'],$row['CoinPricePctChange'],$row['LiveSellOrders'],$row['LastSellOrders']//21
    ,$row['SellOrdersPctChange'],$row['LiveVolume'],$row['LastVolume'],$row['VolumePctChange'],$row['BaseCurrency'],$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['1HrPriceChangeLive'],$row['1HrPriceChangeLast'],$row['1HrPriceChange3'] //33
    ,$row['1HrPriceChange4'],$row['SecondstoUpdate'],$row['LastUpdated'],$row['Name'],$row['Image'],$row['DoNotBuy'],$row['HoursFlatPdcs'],$row['MinPriceFromLow'],$row['PctFromLiveToLow'],$row['6MonthPrice'],$row['3MonthPrice'],$row['AverageLowPrice'] //45
    ,$row['HoursSinceAdded'],$row['MaxHoursFlat'],$row['CaaOffset'],$row['CaaMinsToCancelBuy'],$row['HoursFlatHighPdcs'],$row['HoursFlatLowPdcs'],$row['SpreadBetRuleID']);//52
  }
  $conn->close();
  return $tempAry;
}

function getHoursforCoinPriceDip($whereclause){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "SELECT `IDCn`,`LiveCoinPrice`, `Us`.`ID` as `UserID`, `Usc`.`HoursFlatTolerance`
    FROM `View1_BuyCoins`
    join `User` `Us`
    join `UserConfig` `Usc` on `Usc`.`UserID` = `Us`.`ID`
    $whereclause
    order by `ID`,`IDCn`";
    echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['IDCn'],$row['LiveCoinPrice'],$row['UserID'],$row['HoursFlatTolerance']);//42
  }
  $conn->close();
  return $tempAry;
}

function getTrackingSellCoins($type, $userID = 0){
  $tempAry = [];
  if ($userID <> 0){ $whereclause = "Where `UserID` = $userID and `Status` = 'Open' and `Type` = '$type'";}else{$whereclause = "Where `Status` = 'Open' and `Type` = '$type'";}
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `IDTr`,`Type`,`CoinID`,`UserID`,`CoinPrice`,`Amount`,`Status`,`OrderDate`,`CompletionDate`,`BittrexID`,`OrderNo`,`Symbol`,`LastBuyOrders`,`LiveBuyOrders`,`BuyOrdersPctChange`,`LastMarketCap`
  ,`LiveMarketCap`,`MarketCapPctChange`,`LastCoinPrice`,`LiveCoinPrice`,`CoinPricePctChange`,`LastSellOrders`,`LiveSellOrders`,`SellOrdersPctChange`,`LastVolume`,`LiveVolume`,`VolumePctChange`,`Last1HrChange`
  ,`Live1HrChange`,`Hr1ChangePctChange`,`Last24HrChange`,`Live24HrChange`,`Hr24ChangePctChange`,`Last7DChange`,`Live7DChange`,`D7ChangePctChange`,`BaseCurrency`,`LivePriceTrend`,`LastPriceTrend`,`Price3Trend`
  ,`Price4Trend`,`FixSellRule`,`SellRule`,`BuyRule`,`ToMerge`,`LowPricePurchaseEnabled`,`TotalPurchasesPerCoin` as `PurchaseLimit`,`PctToPurchase`, `BTCBuyAmount`,`NoOfPurchases`,`Name`,`Image`,10 as `MaxCoinMerges`
  ,`NoOfCoinSwapsThisWeek`,`OriginalPrice`, `CoinFee`,`LivePrice`, `ProfitUSD`, `ProfitPct`,`CaptureTrend`,`minsToDelay`,`MinsFromBuy`,`HoursFlatHighPdcs`,`MaxPriceFromHigh`,`PctFromLiveToHigh`,`MultiSellRuleEnabled`
  ,floor(timestampdiff(second,`OrderDate`, now())/3600) as `HoursSinceBuy`, 'SellPctCsp',`MaxHoursFlat`,`Hr1Top`,`Hr1Bottom`,`CaaOffset`,`CaaMinsToCancelSell`,`CaaSellOffset`
  FROM `View5_SellCoins` $whereclause order by `ProfitPct` Desc ";
  $result = $conn->query($sql);
  echo "<BR>$sql<BR>";
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['IDTr'],$row['Type'],$row['CoinID'],$row['UserID'],$row['CoinPrice'],$row['Amount'],$row['Status'],$row['OrderDate'],$row['CompletionDate'],$row['BittrexID'],$row['OrderNo'],
    $row['Symbol'],$row['LastBuyOrders'],$row['LiveBuyOrders'],$row['BuyOrdersPctChange'],$row['LastMarketCap'],$row['LiveMarketCap'],$row['MarketCapPctChange'],$row['LastCoinPrice'],$row['LiveCoinPrice'], //19
    $row['CoinPricePctChange'],$row['LastSellOrders'],$row['LiveSellOrders'],$row['SellOrdersPctChange'],$row['LastVolume'],$row['LiveVolume'],$row['VolumePctChange'],$row['Last1HrChange'],$row['Live1HrChange'],$row['Hr1ChangePctChange'],$row['Last24HrChange'],$row['Live24HrChange'] //31
    ,$row['Hr24ChangePctChange'],$row['Last7DChange'],$row['Live7DChange'],$row['D7ChangePctChange'],$row['BaseCurrency'],$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['FixSellRule'],$row['SellRule'],$row['BuyRule'] //43
    ,$row['ToMerge'],$row['LowPricePurchaseEnabled'],$row['PurchaseLimit'],$row['PctToPurchase'],$row['BTCBuyAmount'],$row['NoOfPurchases'],$row['Name'],$row['Image'],$row['MaxCoinMerges'],$row['NoOfCoinSwapsThisWeek'] //53
    ,$row['OriginalPrice'],$row['CoinFee'],$row['LivePrice'],$row['ProfitUSD'],$row['ProfitPct'],$row['CaptureTrend'],$row['minsToDelay'],$row['MinsFromBuy'],$row['HoursFlatHighPdcs'],$row['MaxPriceFromHigh'],$row['PctFromLiveToHigh'] //64
    ,$row['MultiSellRuleEnabled'],$row['HoursSinceBuy'],$row['SellPctCsp'],$row['MaxHoursFlat'],$row['Hr1Top'],$row['Hr1Bottom'],$row['CaaOffset'],$row['CaaMinsToCancelSell'],$row['CaaSellOffset']); //73
  }
  $conn->close();
  return $tempAry;
}

function getTrackingSpreadBetSellCoins($type, $userID = 0){
  $tempAry = [];
  if ($userID <> 0){ $whereclause = "Where `UserID` = $userID and `Status` = 'Open' and `Type` = '$type'";}else{$whereclause = "Where `Status` = 'Open' and `Type` = '$type'";}
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `IDTr`,`Type`,`CoinID`,`UserID`,sum(`CoinPrice`) as `CoinPrice`,sum(`Amount`) as `Amount`,`Status`,`OrderDate`,`CompletionDate`,`BittrexID`,`OrderNo`,`Symbol`,sum(`LastBuyOrders`) as `LastBuyOrders`
  ,sum(`LiveBuyOrders`) as `LiveBuyOrders`,sum(((`LiveBuyOrders` - `LastBuyOrders`) / `LastBuyOrders`) * 100)  as `BuyOrdersPctChange`,sum(`LastMarketCap`) as `LastMarketCap`
  ,sum(`LiveMarketCap`) as `LiveMarketCap`,sum(((`LiveMarketCap` - `LastMarketCap`) / `LastMarketCap`) * 100) as `MarketCapPctChange`,sum(`LastCoinPrice`) as `LastCoinPrice`,sum(`LiveCoinPrice`) as `LiveCoinPrice`,sum(((`LiveCoinPrice` - `LastCoinPrice`) / `LastCoinPrice`) * 100) as `CoinPricePctChange`
  ,sum(`LastSellOrders`) as `LastSellOrders`,sum(`LiveSellOrders`) as `LiveSellOrders`,sum(((`LiveSellOrders` - `LastSellOrders`) / `LastSellOrders`) * 100) as `SellOrdersPctChange`,sum(`LastVolume`) as `LastVolume`,sum(`LiveVolume`) as `LiveVolume`,sum(((`LiveVolume` - `LastVolume`) / `LastVolume`) * 100) as `VolumePctChange`
  ,sum(`Last1HrChange`) as `Last1HrChange`
  ,sum(`Live1HrChange`) as `Live1HrChange`,sum(((`LiveCoinPrice`- `Live1HrChange`) / `Live1HrChange`) * 100) as `Hr1ChangePctChange`,sum(`Last24HrChange`) as `Last24HrChange`,sum(`Live24HrChange`) as `Live24HrChange`,sum(((`LiveCoinPrice` - `Live24HrChange`) / `Live24HrChange`) * 100)  as `Hr24ChangePctChange`,sum(`Last7DChange`) as `Last7DChange`
  ,sum(`Live7DChange`) as `Live7DChange`,sum(((`LiveCoinPrice` - `Live7DChange`) / `Live7DChange`) * 100) as `D7ChangePctChange`,`BaseCurrency`,avg(`LivePriceTrend`) as `LivePriceTrend`,avg(`LastPriceTrend`) as `LastPriceTrend`,avg(`Price3Trend`) as `Price3Trend`
  ,`Price4Trend`,`FixSellRule`,`SellRule`,`BuyRule`,`ToMerge`,`LowPricePurchaseEnabled`,`TotalPurchasesPerCoin` as `PurchaseLimit`,`PctToPurchase`, `BTCBuyAmount`,`NoOfPurchases`,`Name`,`Image`,10 as `MaxCoinMerges`
  ,sum(`NoOfCoinSwapsThisWeek`) as `NoOfCoinSwapsThisWeek`,sum(`OriginalPrice`) as `OriginalPrice`, sum(`CoinFee`) as `CoinFee`,sum(`LivePrice`) as `LivePrice`, sum(`ProfitUSD`) as `ProfitUSD`, sum((((`LiveCoinPrice`*`Amount`)-(`CoinPrice`*`Amount`)-(((`CoinPrice`*`Amount`)/100)*0.28)) /(`CoinPrice`*`Amount`))*100) as `ProfitPct`,avg(`CaptureTrend`) as `CaptureTrend`
  ,avg(`minsToDelay`) as `minsToDelay`,avg(`MinsFromBuy`) as `MinsFromBuy`,avg(`HoursFlatHighPdcs`) as `HoursFlatHighPdcs`,sum(`MaxPriceFromHigh`) as `MaxPriceFromHigh`,sum(`PctFromLiveToHigh`) as `PctFromLiveToHigh`,`MultiSellRuleEnabled`
  ,floor(timestampdiff(second,`OrderDate`, now())/3600) as `HoursSinceBuy`, 'SellPctCsp' as `SellPctCsp`,avg(`MaxHoursFlat`) as `MaxHoursFlat`,sum(`Hr1Top`) as `Hr1Top`,sum(`Hr1Bottom`) as `Hr1Bottom`,avg(`CaaOffset`) as `CaaOffset`
  ,avg(`CaaMinsToCancelSell`) as `CaaMinsToCancelSell`,avg(`CaaSellOffset`) as `CaaSellOffset`,`SpreadBetTransactionID`
  FROM `View5_SellCoins` $whereclause group by `SpreadBetTransactionID` order by `ProfitPct` Desc ";
  $result = $conn->query($sql);
  echo "<BR>$sql<BR>";
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['IDTr'],$row['Type'],$row['CoinID'],$row['UserID'],$row['CoinPrice'],$row['Amount'],$row['Status'],$row['OrderDate'],$row['CompletionDate'],$row['BittrexID'],$row['OrderNo'],
    $row['Symbol'],$row['LastBuyOrders'],$row['LiveBuyOrders'],$row['BuyOrdersPctChange'],$row['LastMarketCap'],$row['LiveMarketCap'],$row['MarketCapPctChange'],$row['LastCoinPrice'],$row['LiveCoinPrice'], //19
    $row['CoinPricePctChange'],$row['LastSellOrders'],$row['LiveSellOrders'],$row['SellOrdersPctChange'],$row['LastVolume'],$row['LiveVolume'],$row['VolumePctChange'],$row['Last1HrChange'],$row['Live1HrChange'],$row['Hr1ChangePctChange'],$row['Last24HrChange'],$row['Live24HrChange'] //31
    ,$row['Hr24ChangePctChange'],$row['Last7DChange'],$row['Live7DChange'],$row['D7ChangePctChange'],$row['BaseCurrency'],$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['FixSellRule'],$row['SellRule'],$row['BuyRule'] //43
    ,$row['ToMerge'],$row['LowPricePurchaseEnabled'],$row['PurchaseLimit'],$row['PctToPurchase'],$row['BTCBuyAmount'],$row['NoOfPurchases'],$row['Name'],$row['Image'],$row['MaxCoinMerges'],$row['NoOfCoinSwapsThisWeek'] //53
    ,$row['OriginalPrice'],$row['CoinFee'],$row['LivePrice'],$row['ProfitUSD'],$row['ProfitPct'],$row['CaptureTrend'],$row['minsToDelay'],$row['MinsFromBuy'],$row['HoursFlatHighPdcs'],$row['MaxPriceFromHigh'],$row['PctFromLiveToHigh'] //64
    ,$row['MultiSellRuleEnabled'],$row['HoursSinceBuy'],$row['SellPctCsp'],$row['MaxHoursFlat'],$row['Hr1Top'],$row['Hr1Bottom'],$row['CaaOffset'],$row['CaaMinsToCancelSell'],$row['CaaSellOffset'],$row['SpreadBetTransactionID']); //74
  }
  $conn->close();
  return $tempAry;
}

function getAutoActionCoins($type, $status,$hoursSinceBuy){
  $tempAry = [];
  if ($status == 'Open'){
   $whereclause = "Where `Status` = '$status' and `Type` = '$type' and `OrderDate`  BETWEEN DATE_SUB(NOW(),INTERVAL $hoursSinceBuy HOUR) AND NOW() ";
 }else{
   $whereclause = "Where `Status` = '$status' and `Type` = '$type' and `CompletionDate` BETWEEN DATE_SUB(NOW(),INTERVAL $hoursSinceBuy HOUR) AND NOW() ";
 }

 //WHERE `CompletionDate` BETWEEN DATE_SUB(NOW(),INTERVAL 168 HOUR) AND NOW()
   $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `IDTr`,`Type`,`CoinID`,`UserID`,`CoinPrice`,`Amount`,`Status`,`OrderDate`,`CompletionDate`,`BittrexID`,`OrderNo`,`Symbol`,`LastBuyOrders`,`LiveBuyOrders`,`BuyOrdersPctChange`,`LastMarketCap`
  ,`LiveMarketCap`,`MarketCapPctChange`,`LastCoinPrice`,`LiveCoinPrice`,`CoinPricePctChange`,`LastSellOrders`,`LiveSellOrders`,`SellOrdersPctChange`,`LastVolume`,`LiveVolume`,`VolumePctChange`,`Last1HrChange`
  ,`Live1HrChange`,`Hr1ChangePctChange`,`Last24HrChange`,`Live24HrChange`,`Hr24ChangePctChange`,`Last7DChange`,`Live7DChange`,`D7ChangePctChange`,`BaseCurrency`,`LivePriceTrend`,`LastPriceTrend`,`Price3Trend`
  ,`Price4Trend`,`FixSellRule`,`SellRule`,`BuyRule`,`ToMerge`,`LowPricePurchaseEnabled`,`TotalPurchasesPerCoin` as `PurchaseLimit`,`PctToPurchase`, `BTCBuyAmount`,`NoOfPurchases`,`Name`,`Image`,10 as `MaxCoinMerges`
  ,`NoOfCoinSwapsThisWeek`,`OriginalPrice`, `CoinFee`,`LivePrice`, `ProfitUSD`, `ProfitPct`,`CaptureTrend`,`minsToDelay`,`MinsFromBuy`,`HoursFlatHighPdcs`,`MaxPriceFromHigh`,`PctFromLiveToHigh`,`MultiSellRuleEnabled`
  ,floor(timestampdiff(second,`OrderDate`, now())/3600) as `HoursSinceBuy`, `SellPctCsp`,`MaxHoursFlat`,`Hr1Top`,`Hr1Bottom`,floor(timestampdiff(second,`CompletionDate`, now())/3600) as `HoursSinceSell`
  FROM `View5_SellCoins` $whereclause order by `ProfitPct` Desc ";
  $result = $conn->query($sql);
  echo "<BR>$sql<BR>";
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['IDTr'],$row['Type'],$row['CoinID'],$row['UserID'],$row['CoinPrice'],$row['Amount'],$row['Status'],$row['OrderDate'],$row['CompletionDate'],$row['BittrexID'],$row['OrderNo'],
    $row['Symbol'],$row['LastBuyOrders'],$row['LiveBuyOrders'],$row['BuyOrdersPctChange'],$row['LastMarketCap'],$row['LiveMarketCap'],$row['MarketCapPctChange'],$row['LastCoinPrice'],$row['LiveCoinPrice'], //19
    $row['CoinPricePctChange'],$row['LastSellOrders'],$row['LiveSellOrders'],$row['SellOrdersPctChange'],$row['LastVolume'],$row['LiveVolume'],$row['VolumePctChange'],$row['Last1HrChange'],$row['Live1HrChange'],$row['Hr1ChangePctChange'],$row['Last24HrChange'],$row['Live24HrChange'] //31
    ,$row['Hr24ChangePctChange'],$row['Last7DChange'],$row['Live7DChange'],$row['D7ChangePctChange'],$row['BaseCurrency'],$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['FixSellRule'],$row['SellRule'],$row['BuyRule'] //43
    ,$row['ToMerge'],$row['LowPricePurchaseEnabled'],$row['PurchaseLimit'],$row['PctToPurchase'],$row['BTCBuyAmount'],$row['NoOfPurchases'],$row['Name'],$row['Image'],$row['MaxCoinMerges'],$row['NoOfCoinSwapsThisWeek'] //53
    ,$row['OriginalPrice'],$row['CoinFee'],$row['LivePrice'],$row['ProfitUSD'],$row['ProfitPct'],$row['CaptureTrend'],$row['minsToDelay'],$row['MinsFromBuy'],$row['HoursFlatHighPdcs'],$row['MaxPriceFromHigh'],$row['PctFromLiveToHigh'] //64
    ,$row['MultiSellRuleEnabled'],$row['HoursSinceBuy'],$row['SellPctCsp'],$row['MaxHoursFlat'],$row['Hr1Top'],$row['Hr1Bottom'],$row['HoursSinceSell']); //71
  }
  $conn->close();
  return $tempAry;
}

function getTrackingSellCoinsAll(){
  $tempAry = [];
  //if ($userID <> 0){ $whereclause = "Where `UserID` = $userID";}else{$whereclause = "";}
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `IDTr`,`Type`,`CoinID`,`UserID`,`CoinPrice`,`Amount`,`Status`,`OrderDate`,`CompletionDate`,`BittrexID`,`OrderNo`,`Symbol`,`LastBuyOrders`, `LiveBuyOrders`,`BuyOrdersPctChange`,`LastMarketCap`,`LiveMarketCap`,`MarketCapPctChange`,`LastCoinPrice`,`LiveCoinPrice`,`CoinPricePctChange`,`LastSellOrders`
  ,`LiveSellOrders`,`SellOrdersPctChange`,`LastVolume`,`LiveVolume`,`VolumePctChange`,`Last1HrChange`,`Live1HrChange`,`Hr1ChangePctChange`,`Last24HrChange`,`Live24HrChange`,`Hr24ChangePctChange`,`Last7DChange`,`Live7DChange`,`D7ChangePctChange`,`BaseCurrency`
  , `Price4Trend`,`Price3Trend`,`LastPriceTrend`,`LivePriceTrend`,`FixSellRule`,`SellRule`,`BuyRule`,`ToMerge`,`LowPricePurchaseEnabled`,`DailyBTCLimit`,`PctToPurchase`,`BTCBuyAmount`,`NoOfPurchases`,`Name`,`Image`,10 as `MaxCoinMerges`,`NoOfCoinSwapsThisWeek`
  ,`CoinPrice`*`Amount` as OriginalPrice, ((`CoinPrice`*`Amount`)/100)*0.28 as CoinFee, `LiveCoinPrice`*`Amount` as LivePrice, (`LiveCoinPrice`*`Amount`)-(`CoinPrice`*`Amount`)-( ((`CoinPrice`*`Amount`)/100)*0.28) as ProfitUSD
  , (ProfitUSD/OriginalPrice )*100 as ProfitPct
  ,`CaptureTrend`,`minsToDelay`,`Enabled` as `ReduceLossEnabled`,`SellPct` as `ReduceLossSellPct`,`OriginalPriceMultiplier`,`ReduceLossCounter`,`ReduceLossMaxCounter`,`HoursFlatLowPdcs` as `HoursFlat`,`OverrideReduceLoss`,`HoursFlatPdcs`,`HoldCoinForBuyOut`,`CoinForBuyOutPct`,`holdingAmount`
  ,`SavingOverride`,`HoursFlatRls`, `SpreadBetTransactionID`,`CoinSwapDelayed`,`MaxHoursFlat`,`PctOfAuto`,`PctOfAutoBuyBack`,`PctOfAutoReduceLoss`,`HoursFlatAutoEnabled`,`ReduceLossMinsToCancel`
 FROM `View5_SellCoins`  WHERE `Status` = 'Open' order by ProfitPct Asc ";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['IDTr'],$row['Type'],$row['CoinID'],$row['UserID'],$row['CoinPrice'],$row['Amount'],$row['Status'],$row['OrderDate'],$row['CompletionDate'],$row['BittrexID'],$row['OrderNo'],
    $row['Symbol'],$row['LastBuyOrders'],$row['LiveBuyOrders'],$row['BuyOrdersPctChange'],$row['LastMarketCap'],$row['LiveMarketCap'],$row['MarketCapPctChange'],$row['LastCoinPrice'],$row['LiveCoinPrice'], //19
    $row['CoinPricePctChange'],$row['LastSellOrders'],$row['LiveSellOrders'],$row['SellOrdersPctChange'],$row['LastVolume'],$row['LiveVolume'],$row['VolumePctChange'],$row['Last1HrChange'],$row['Live1HrChange'],$row['Hr1ChangePctChange'],$row['Last24HrChange'],$row['Live24HrChange'] //31
    ,$row['Hr24ChangePctChange'],$row['Last7DChange'],$row['Live7DChange'],$row['D7ChangePctChange'],$row['BaseCurrency'],$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['FixSellRule'],$row['SellRule'],$row['BuyRule'] //43
    ,$row['ToMerge'],$row['LowPricePurchaseEnabled'],$row['DailyBTCLimit'],$row['PctToPurchase'],$row['BTCBuyAmount'],$row['NoOfPurchases'],$row['Name'],$row['Image'],$row['MaxCoinMerges'],$row['NoOfCoinSwapsThisWeek'] //53
    ,$row['OriginalPrice'],$row['CoinFee'],$row['LivePrice'],$row['ProfitUSD'],$row['ProfitPct'],$row['CaptureTrend'],$row['minsToDelay'],$row['ReduceLossEnabled'],$row['ReduceLossSellPct'],$row['OriginalPriceMultiplier'] //63
    ,$row['ReduceLossCounter'],$row['ReduceLossMaxCounter'],$row['HoursFlat'],$row['OverrideReduceLoss'],$row['HoursFlatPdcs'],$row['HoldCoinForBuyOut'],$row['CoinForBuyOutPct'],$row['holdingAmount'],$row['SavingOverride']//72
    ,$row['HoursFlatRls'],$row['SpreadBetTransactionID'],$row['CoinSwapDelayed'],$row['MaxHoursFlat'],$row['PctOfAuto'],$row['PctOfAutoBuyBack'],$row['PctOfAutoReduceLoss'],$row['HoursFlatAutoEnabled'],$row['ReduceLossMinsToCancel']); //81
  }
  $conn->close();
  return $tempAry;
}

function bittrexCoinStats($apikey, $apisecret, $symbol, $baseCurrency, $versionNum){
    $nonce=time();
    //$uri='https://bittrex.com/api/v1.1/account/getbalance?apikey='.$apikey.'&currency=BTC&nonce='.$nonce;
    if ($versionNum == 1){
      $uri='https://bittrex.com/api/v1.1/public/getmarketsummary?market='.$baseCurrency.'-'.$symbol;
      print_r($uri);
      $sign=hash_hmac('sha512',$uri,$apisecret);
      $ch = curl_init($uri);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $execResult = curl_exec($ch);
      $obj = json_decode($execResult, True);
    }elseif ($versionNum == 3){
      $timestamp = time()*1000;
      $url = "https://api.bittrex.com/v3/markets/".$symbol.'-'.$baseCurrency."/summary";
      //$url = "https://api.bittrex.com/v3/markets/".$symbol."/summary";
      //echo "<BR> $url";
      $method = "GET";
      $content = "";
      $subaccountId = "";
      $contentHash = hash('sha512',  $content);
      $preSign = $timestamp . $url . $method . $contentHash . $subaccountId;
      $signature = hash_hmac('sha512', $preSign, $apisecret);

      $headers = array(
      "Accept: application/json",
      "Content-Type: application/json",
      "Api-Key: ".$apikey."",
      "Api-Signature: ".$signature."",
      "Api-Timestamp: ".$timestamp."",
      "Api-Content-Hash: ".$contentHash.""
      );

      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($ch, CURLOPT_HEADER, FALSE);
      $execResult = curl_exec($ch);
      curl_close($ch);
      $obj = json_decode($execResult, True);
    }
    return $obj;
}

function getUserRules($type){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  if ($type == 1){
    $ruleType = "'Normal'";
  }else{
    $ruleType = "'SpreadBet'";
  }
//12
  $sql = "SELECT `UserID`,`BuyOrdersEnabled`,`BuyOrdersTop`,`BuyOrdersBtm`,`MarketCapEnabled`,`MarketCapTop`,`MarketCapBtm`,`1HrChangeEnabled`,`1HrChangeTop`,`1HrChangeBtm`,`24HrChangeEnabled`,`24HrChangeTop`,`24HrChangeBtm`
  ,`7DChangeEnabled`,`7DChangeTop`,`7DChangeBtm`,`CoinPriceEnabled`,`CoinPriceTop`,`CoinPriceBtm`,`SellOrdersEnabled`,`SellOrdersTop`,`SellOrdersBtm`,`VolumeEnabled`,`VolumeTop`,`VolumeBtm`,`BuyCoin`,`SendEmail`,`BTCAmount`
  ,`Email`,`UserName`,`APIKey`,`APISecret`,`EnableDailyBTCLimit`,`DailyBTCLimit`,`EnableTotalBTCLimit`,`TotalBTCLimit`,`RuleID`,`BuyCoinOffsetPct`,`BuyCoinOffsetEnabled`,`PriceTrendEnabled`, `Price4Trend`, `Price3Trend`
  , `LastPriceTrend`, `LivePriceTrend`,`Active`,`DisableUntil`,`BaseCurrency`,`NoOfCoinPurchase`,`BuyType`,`TimeToCancelBuyMins`,`BuyPriceMinEnabled`,`BuyPriceMin`,`LimitToCoin`,`AutoBuyCoinEnabled`,`AutoBuyPrice`
  ,`BuyAmountOverrideEnabled`, `BuyAmountOverride`,`NewBuyPattern`,`KEK`,`SellRuleFixed`,`OverrideDailyLimit`,`CoinPricePatternEnabled`,`CoinPricePattern`,`1HrChangeTrendEnabled`,`1HrChangeTrend`,`BuyRisesInPrice`
  ,`TotalProfitPauseEnabled`,`TotalProfitPause`,`PauseRulesEnabled`,`PauseRules`,`PauseHours`,`MarketDropStopEnabled`,`MarketDropStopPct`,`OverrideDisableRule`,`LimitBuyAmountEnabled`,`LimitBuyAmount`,`OverrideCancelBuyTimeEnabled`
  ,`OverrideCancelBuyTimeMins`,`NoOfBuyModeOverrides`,`CoinModeOverridePriceEnabled`,`OverrideCoinAllocation`,`OneTimeBuyRule`,`LimitToBaseCurrency`,`HoursDisableUntil`,`PctFromLowBuyPriceEnabled`,`NoOfHoursFlatEnabled`,`NoOfHoursFlat`
  ,`PctOverMinPrice`,`PctOfAuto`,`RuleType`,`OpenTransactions`,`TotalPurchasesPerRule`,`RuleDisabledBr`
   FROM `View13_UserBuyRules` where `BuyCoin` = 1 and `RuleType` = $ruleType and ((`OpenTransactions` <= `TotalPurchasesPerRule`) OR `OpenTransactions` is Null) and `APIKey` <> 'NA' and `RuleDisabledBr` = 0";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['UserID'],$row['BuyOrdersEnabled'],$row['BuyOrdersTop'],$row['BuyOrdersBtm'],$row['MarketCapEnabled'],$row['MarketCapTop'], //5
    $row['MarketCapBtm'],$row['1HrChangeEnabled'],$row['1HrChangeTop'],$row['1HrChangeBtm'],$row['24HrChangeEnabled'],$row['24HrChangeTop'],$row['24HrChangeBtm'], //12
    $row['7DChangeEnabled'],$row['7DChangeTop'],$row['7DChangeBtm'],$row['CoinPriceEnabled'],$row['CoinPriceTop'],$row['CoinPriceBtm'],$row['SellOrdersEnabled'],$row['SellOrdersTop'], //20
    $row['SellOrdersBtm'],$row['VolumeEnabled'],$row['VolumeTop'],$row['VolumeBtm'],$row['BuyCoin'],$row['SendEmail'],$row['BTCAmount'],$row['Email'],$row['UserName'],$row['APIKey'], //30
    $row['APISecret'],$row['EnableDailyBTCLimit'],$row['DailyBTCLimit'],$row['EnableTotalBTCLimit'],$row['TotalBTCLimit'],$row['RuleID'],$row['BuyCoinOffsetPct'],$row['BuyCoinOffsetEnabled'], //38
    $row['PriceTrendEnabled'],$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['Active'],$row['DisableUntil'],$row['BaseCurrency'],$row['NoOfCoinPurchase'], //47
    $row['BuyType'],$row['TimeToCancelBuyMins'],$row['BuyPriceMinEnabled'],$row['BuyPriceMin'],$row['LimitToCoin'],$row['AutoBuyCoinEnabled'],$row['AutoBuyPrice'],$row['BuyAmountOverrideEnabled']  //55
    ,$row['BuyAmountOverride'],$row['NewBuyPattern'],$row['KEK'],$row['SellRuleFixed'],$row['OverrideDailyLimit'],$row['CoinPricePatternEnabled'],$row['CoinPricePattern'],$row['1HrChangeTrendEnabled'],$row['1HrChangeTrend'] //64
    ,$row['BuyRisesInPrice'],$row['TotalProfitPauseEnabled'],$row['TotalProfitPause'],$row['PauseRulesEnabled'],$row['PauseRules'],$row['PauseHours'],$row['MarketDropStopEnabled'],$row['MarketDropStopPct'] //72
    ,$row['OverrideDisableRule'],$row['LimitBuyAmountEnabled'],$row['LimitBuyAmount'],$row['OverrideCancelBuyTimeEnabled'],$row['OverrideCancelBuyTimeMins'],$row['NoOfBuyModeOverrides'],$row['CoinModeOverridePriceEnabled'] //79
   ,$row['OverrideCoinAllocation'],$row['OneTimeBuyRule'],$row['LimitToBaseCurrency'],$row['HoursDisableUntil'],$row['PctFromLowBuyPriceEnabled'],$row['NoOfHoursFlatEnabled'],$row['NoOfHoursFlat'] //86
   ,$row['PctOverMinPrice'],$row['PctOfAuto'],$row['RuleType'],$row['OpenTransactions'],$row['TotalPurchasesPerRule'],$row['RuleDisabledBr']); //92
  }
  $conn->close();
  return $tempAry;
}

function getSpreadBetUserRules(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
//12
  $sql = "SELECT `UserID`,`BuyOrdersEnabled`,`BuyOrdersTop`,`BuyOrdersBtm`,`MarketCapEnabled`,`MarketCapTop`,`MarketCapBtm`,`1HrChangeEnabled`,`1HrChangeTop`,`1HrChangeBtm`,`24HrChangeEnabled`,`24HrChangeTop`,`24HrChangeBtm`
  ,`7DChangeEnabled`,`7DChangeTop`,`7DChangeBtm`,`CoinPriceEnabled`,`CoinPriceTop`,`CoinPriceBtm`,`SellOrdersEnabled`,`SellOrdersTop`,`SellOrdersBtm`,`VolumeEnabled`,`VolumeTop`,`VolumeBtm`,`BuyCoin`,`SendEmail`,`BTCAmount`
  ,`Email`,`UserName`,`APIKey`,`APISecret`,`EnableDailyBTCLimit`,`DailyBTCLimit`,`EnableTotalBTCLimit`,`TotalBTCLimit`,`RuleID`,`BuyCoinOffsetPct`,`BuyCoinOffsetEnabled`,`PriceTrendEnabled`, `Price4Trend`, `Price3Trend`
  , `LastPriceTrend`, `LivePriceTrend`,`Active`,`DisableUntil`,`BaseCurrency`,`NoOfCoinPurchase`,`BuyType`,`TimeToCancelBuyMins`,`BuyPriceMinEnabled`,`BuyPriceMin`,`LimitToCoin`,`AutoBuyCoinEnabled`,`AutoBuyPrice`
  ,`BuyAmountOverrideEnabled`, `BuyAmountOverride`,`NewBuyPattern`,`KEK`,`SellRuleFixed`,`OverrideDailyLimit`,`CoinPricePatternEnabled`,`CoinPricePattern`,`1HrChangeTrendEnabled`,`1HrChangeTrend`,`BuyRisesInPrice`
  ,`TotalProfitPauseEnabled`,`TotalProfitPause`,`PauseRulesEnabled`,`PauseRules`,`PauseHours`,`MarketDropStopEnabled`,`MarketDropStopPct`,`OverrideDisableRule`,`LimitBuyAmountEnabled`,`LimitBuyAmount`,`OverrideCancelBuyTimeEnabled`
  ,`OverrideCancelBuyTimeMins`,`NoOfBuyModeOverrides`,`CoinModeOverridePriceEnabled`,`OverrideCoinAllocation`,`OneTimeBuyRule`,`LimitToBaseCurrency`,`HoursDisableUntil`,`PctFromLowBuyPriceEnabled`,`NoOfHoursFlatEnabled`,`NoOfHoursFlat`
  ,`PctOverMinPrice`,`PctOfAuto`,`SpreadBetTotalAmount`
   FROM `View13_UserBuyRules` where `BuyCoin` = 1 and `RuleType` = 'SpreadBet' and `RuleDisabledBr` = 0";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['UserID'],$row['BuyOrdersEnabled'],$row['BuyOrdersTop'],$row['BuyOrdersBtm'],$row['MarketCapEnabled'],$row['MarketCapTop'], //5
    $row['MarketCapBtm'],$row['1HrChangeEnabled'],$row['1HrChangeTop'],$row['1HrChangeBtm'],$row['24HrChangeEnabled'],$row['24HrChangeTop'],$row['24HrChangeBtm'], //12
    $row['7DChangeEnabled'],$row['7DChangeTop'],$row['7DChangeBtm'],$row['CoinPriceEnabled'],$row['CoinPriceTop'],$row['CoinPriceBtm'],$row['SellOrdersEnabled'],$row['SellOrdersTop'], //20
    $row['SellOrdersBtm'],$row['VolumeEnabled'],$row['VolumeTop'],$row['VolumeBtm'],$row['BuyCoin'],$row['SendEmail'],$row['BTCAmount'],$row['Email'],$row['UserName'],$row['APIKey'], //30
    $row['APISecret'],$row['EnableDailyBTCLimit'],$row['DailyBTCLimit'],$row['EnableTotalBTCLimit'],$row['TotalBTCLimit'],$row['RuleID'],$row['BuyCoinOffsetPct'],$row['BuyCoinOffsetEnabled'], //38
    $row['PriceTrendEnabled'],$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['Active'],$row['DisableUntil'],$row['BaseCurrency'],$row['NoOfCoinPurchase'], //47
    $row['BuyType'],$row['TimeToCancelBuyMins'],$row['BuyPriceMinEnabled'],$row['BuyPriceMin'],$row['LimitToCoin'],$row['AutoBuyCoinEnabled'],$row['AutoBuyPrice'],$row['BuyAmountOverrideEnabled']  //55
    ,$row['BuyAmountOverride'],$row['NewBuyPattern'],$row['KEK'],$row['SellRuleFixed'],$row['OverrideDailyLimit'],$row['CoinPricePatternEnabled'],$row['CoinPricePattern'],$row['1HrChangeTrendEnabled'],$row['1HrChangeTrend'] //64
    ,$row['BuyRisesInPrice'],$row['TotalProfitPauseEnabled'],$row['TotalProfitPause'],$row['PauseRulesEnabled'],$row['PauseRules'],$row['PauseHours'],$row['MarketDropStopEnabled'],$row['MarketDropStopPct'] //72
    ,$row['OverrideDisableRule'],$row['LimitBuyAmountEnabled'],$row['LimitBuyAmount'],$row['OverrideCancelBuyTimeEnabled'],$row['OverrideCancelBuyTimeMins'],$row['NoOfBuyModeOverrides'],$row['CoinModeOverridePriceEnabled'] //79
   ,$row['OverrideCoinAllocation'],$row['OneTimeBuyRule'],$row['LimitToBaseCurrency'],$row['HoursDisableUntil'],$row['PctFromLowBuyPriceEnabled'],$row['NoOfHoursFlatEnabled'],$row['NoOfHoursFlat'] //86
   ,$row['PctOverMinPrice'],$row['PctOfAuto'],$row['SpreadBetTotalAmount']); //89
  }
  $conn->close();
  return $tempAry;
}

function getUserSellRules($sellType){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `ID`,`UserID`,`SellCoin`,`SendEmail`,`BuyOrdersEnabled`,`BuyOrdersTop`,`BuyOrdersBtm`,`MarketCapEnabled`,`MarketCapTop`,`MarketCapBtm`,`1HrChangeEnabled`,`1HrChangeTop`,
  `1HrChangeBtm`,`24HrChangeEnabled`,`24HrChangeTop`,`24HrChangeBtm`,`7DChangeEnabled`,`7DChangeTop`,`7DChangeBtm`,`ProfitPctEnabled`,`ProfitPctTop`,`ProfitPctBtm`,`CoinPriceEnabled`,
  `CoinPriceTop`,`CoinPriceBtm`,`SellOrdersEnabled`,`SellOrdersTop`,`SellOrdersBtm`,`VolumeEnabled`,`VolumeTop`,`VolumeBtm`,`Email`,`UserName`,`APIKey`,`APISecret`, `SellCoinOffsetEnabled`,
  `SellCoinOffsetPct`,`SellPriceMinEnabled`,`SellPriceMin`,`LimitToCoin`,`KEK`,`SellPatternEnabled`,`SellPattern`,`LimitToBuyRule`,`CoinPricePatternEnabled`,`CoinPricePattern`,`AutoSellCoinEnabled`
  ,`SellFallsInPrice`,`SellAllCoinsEnabled`,`SellAllCoinsPct`,`CoinSwapEnabled`,`CoinSwapAmount`,`NoOfCoinSwapsPerWeek`,`MergeCoinEnabled`,`CoinModeRule`,`PctFromHighSellPriceEnabled`,`NoOfHoursFlatEnabled`,`NoOfHoursFlat`
  ,`PctUnderMaxPrice`,`HoursPastBuyToSellEnabled`, `HoursPastBuyToSell`, `CalculatedSellPctEnabled`, `CalculatedSellPctStart`, `CalculatedSellPctEnd`, `CalculatedSellPctDays`,`BypassTrackingSell`,`CalculatedSellPctReduction`
  ,`PctOfAuto`,`OverrideBuyBackAmount`, `OverrideBuyBackSaving`,`HoursAfterPurchaseToStart`,`HoursAfterPurchaseToEnd`,`SellRuleType`
    FROM `View14_UserSellRules` WHERE `SellCoin` = 1 and `SellRuleType` = '$sellType'";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['ID'],$row['UserID'],$row['SellCoin'],$row['SendEmail'],$row['BuyOrdersEnabled'],$row['BuyOrdersTop'],$row['BuyOrdersBtm'],$row['MarketCapEnabled'],$row['MarketCapTop'],$row['MarketCapBtm'],$row['1HrChangeEnabled'] //10
    ,$row['1HrChangeTop'],$row['1HrChangeBtm'],$row['24HrChangeEnabled'],$row['24HrChangeTop'],$row['24HrChangeBtm'],$row['7DChangeEnabled'],$row['7DChangeTop'],$row['7DChangeBtm'],$row['ProfitPctEnabled'],$row['ProfitPctTop'],$row['ProfitPctBtm']  //21
    ,$row['CoinPriceEnabled'],$row['CoinPriceTop'],$row['CoinPriceBtm'],$row['SellOrdersEnabled'],$row['SellOrdersTop'],$row['SellOrdersBtm'],$row['VolumeEnabled'],$row['VolumeTop'],$row['VolumeBtm'],$row['Email'],$row['UserName'],$row['APIKey'] //33
    ,$row['APISecret'],$row['SellCoinOffsetEnabled'],$row['SellCoinOffsetPct'],$row['SellPriceMinEnabled'],$row['SellPriceMin'],$row['LimitToCoin'],$row['KEK'],$row['SellPatternEnabled'],$row['SellPattern'],$row['LimitToBuyRule'] //43
    ,$row['CoinPricePatternEnabled'],$row['CoinPricePattern'],$row['AutoSellCoinEnabled'],$row['SellFallsInPrice'],$row['SellAllCoinsEnabled'],$row['SellAllCoinsPct'],$row['CoinSwapEnabled'],$row['CoinSwapAmount'],$row['NoOfCoinSwapsPerWeek']  //52
    ,$row['MergeCoinEnabled'],$row['CoinModeRule'],$row['PctFromHighSellPriceEnabled'],$row['NoOfHoursFlatEnabled'],$row['NoOfHoursFlat'],$row['PctUnderMaxPrice'],$row['HoursPastBuyToSellEnabled'],$row['HoursPastBuyToSell'],$row['CalculatedSellPctEnabled'] //61
    ,$row['CalculatedSellPctStart'],$row['CalculatedSellPctEnd'],$row['CalculatedSellPctDays'],$row['BypassTrackingSell'],$row['CalculatedSellPctReduction'],$row['PctOfAuto'],$row['OverrideBuyBackAmount'],$row['OverrideBuyBackSaving']//69
    ,$row['HoursAfterPurchaseToStart'],$row['HoursAfterPurchaseToEnd'],$row['SellRuleType']); //72
  }
  $conn->close();
  return $tempAry;
}

function newPrice($bitPrice, $pct, $action){
  if ($action == "Buy"){
    //echo "<BR> 1: ".number_format((float)$bitPrice, 8, '.', '');
    $bitPrice = $bitPrice-(($bitPrice/100)*$pct);
    //echo "<BR> 2: ".number_format((float)$bitPrice, 8, '.', '');
    return round($bitPrice,8, PHP_ROUND_HALF_DOWN);
  }else{
    $bitPrice = $bitPrice+(($bitPrice/100)*$pct);
    //$bitPrice = round($newPrice,8, PHP_ROUND_HALF_UP);
    return round($bitPrice,8, PHP_ROUND_HALF_UP);
  }
}

function returnBuyAmount($coin, $baseCurrency, $btcBuyAmount, $buyType, $BTCBalance, $bitPrice,$apikey,$apisecret){
  //Convert USD to BTC/ETH/BCH
  //$btcBuyAmount = $btcBuyAmount/$bitPrice;
  $testFlag = 0;
  echo "<BR> 0: returnBuyAmount($coin, $baseCurrency, $btcBuyAmount, $buyType, $BTCBalance, $bitPrice,$apikey,$apisecret){";
  if ($btcBuyAmount == 0){
    echo "<BR> 1: $BTCBalance - (($BTCBalance/ 100 ) * 0.28) : ";
    $returnPrice = $BTCBalance - (($BTCBalance/ 100 ) * 0.28);
    echo " $returnPrice ";
    $testFlag = 1;
  }elseif ($btcBuyAmount > 0 && $buyType == 0){
      echo "<BR> 2: returnPrice = ($BTCBalance/100)*$btcBuyAmount; ";
      //$returnPrice = ($btcBuyAmount) - (($BTCBalance/ 100 ) * 0.28);
      $returnPrice = ($BTCBalance/100)*$btcBuyAmount;
      echo " : $returnPrice ";
      $testFlag = 2;
    }elseif ($btcBuyAmount > 0 && $buyType == 1){
      echo "<BR> 3: ($btcBuyAmount) ";
      //$returnPrice = ($BTCBalance*($btcBuyAmount/100))- (($BTCBalance/ 100 ) * 0.28);
      //if ($BTCBalance < $btcBuyAmount){
    //     $tempFee = ($BTCBalance/100)*0.28;
    //      $returnPrice = ($BTCBalance-$tempFee)/$bitPrice;
    //  }else{
        $returnPrice = $btcBuyAmount/$bitPrice;
      //}

      echo " $returnPrice ";
      $testFlag = 3;
    }

   if ($btcBuyAmount > $BTCBalance) {
     //$returnPrice = $BTCBalance - (($BTCBalance/ 100 ) * 0.28);
     $tempPrice = $BTCBalance - (($BTCBalance/ 100 ) * 0.28);
     $returnPrice = $tempPrice/$bitPrice;
    // echo "<BR> 4: $returnPrice = $returnPrice > $BTCBalance ";
   }
   LogToSQL("BuyCoinTest","returnBuyAmount: $returnPrice | $BTCBalance | $btcBuyAmount | $testFlag",3,1);
   //echo "<BR> Balance : $BTCBalance ";
   //if ($BTCBalance < 20.00){$returnPrice == 0;}

   return $returnPrice;
}

function getPriceConversion($price, $base){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  if ($base == 'USDT'){  $sql = "SELECT $price / getBTCPrice(83) as `Price`";}
  elseif ($base == 'BTC'){  $sql = "SELECT $price / getBTCPrice(84) as `Price`";}
  elseif ($base == 'ETH'){  $sql = "SELECT $price / getBTCPrice(85) as `Price`";}
  echo "<BR> $sql | $base";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['Price']);
  }
  $conn->close();
  return $tempAry;
}

function buyCoins($apikey, $apisecret, $coin, $email, $userID, $date,$baseCurrency, $sendEmail, $buyCoin, $btcBuyAmount, $ruleID,$userName, $coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins,$SellRuleFixed, $buyPriceCoin,$overrideCoinAlloc,$noOfPurchases = 0){
  $apiVersion = 3;
  $retBuy = 0;
  $originalBuyAmount = $btcBuyAmount;
  $minPurchaseUSD = 15.0;
  $BTCBalance = bittrexbalance($apikey, $apisecret,$baseCurrency, $apiVersion);
  if ($baseCurrency == 'USDT'){ $tmpPrice = getPriceConversion($minPurchaseUSD,'USDT');}
  elseif ($baseCurrency == 'BTC'){ $tmpPrice = getPriceConversion($minPurchaseUSD,'BTC');}
  elseif ($baseCurrency == 'ETH'){ $tmpPrice = getPriceConversion($minPurchaseUSD,'ETH');}
  $buyMin = $tmpPrice[0][0];
  //get min trade
  //if ($buyType == 2){
    //$btcBuyAmount = ($BTCBalance/100.28)*100;
  //    $btcBuyAmount = ($BTCBalance/100.28)*$btcBuyAmount;
  //}

  //if ($btcBuyAmount == 0){
  //  $charges = ($BTCBalance / 100 ) * 0.28;
  //  $btcBuyAmount = $BTCBalance - $charges;
  //}

//if ($buyAmountOverrideEnabled == 1 AND $buyAmountOverride > 0) {
//    $btcBuyAmount = $buyAmountOverride;

//  }
  if ($buyPriceCoin == 0){
    $bitPrice = number_format((float)(bittrexCoinPrice($apikey, $apisecret,$baseCurrency,$coin,$apiVersion)), 8, '.', '');
  }else{
    $bitPrice = $buyPriceCoin;
  }
  echo "<br> returnBuyAmount($coin, $baseCurrency, $btcBuyAmount, $buyType, $BTCBalance, $bitPrice, $apikey, $apisecret);";
  LogToSQL("BuyCoinAmount","returnBuyAmount($coin, $baseCurrency, round($btcBuyAmount,10), $buyType, $BTCBalance, round($bitPrice,8), $apikey, $apisecret);",3,1);
  $btcBuyAmount = returnBuyAmount($coin, $baseCurrency, round($btcBuyAmount,10), $buyType, $BTCBalance, round($bitPrice,8), $apikey, $apisecret);
  $userSaving = getNewSavingTotal($userID,$baseCurrency);
  echo "<BR> btcBuyAmount $btcBuyAmount ";
  //if ($baseCurrency == 'BTC' OR $baseCurrency == 'ETH'){
    //$btcBuyAmount = $btcBuyAmount;
    $userSavingAmount = $userSaving[0][0];
  //}
  LogToSQL("BuyCoinAmount","btcBuyAmount: $btcBuyAmount | Saving: $userSavingAmount | BuyMin: $buyMin",3,1);
  $subject = "Coin Alert: ".$coin;
  $from = 'Coin Alert <alert@investment-tracker.net>';
  echo "<BR>Balance: $BTCBalance";
  $minTradeAmount = getMinTradeFromSQL($coinID);
  if ($buyCoin) {
    $subject = "Coin Purchase: ".$coin;
    $from = 'Coin Purchase <purchase@investment-tracker.net>';
  }
  //$btcwithCharge = $btcBuyAmount - (($btcBuyAmount/100)*0.28);
  //echo "<BR> btcwithCharge $btcwithCharge = $btcBuyAmount - (($btcBuyAmount/100)*0.28);";
  //if ($btcBuyAmount > $minTradeAmount) {
    echo "buy Coin - Balance Sufficient";
    //$bitPrice = number_format((float)(bittrexCoinPrice($apikey, $apisecret,$baseCurrency,$coin,$apiVersion)), 8, '.', '');
    if ($CoinSellOffsetEnabled == 1){

      $bitPrice = number_format((float)newPrice($bitPrice,$CoinSellOffsetPct, "Buy"), 8, '.', '');
    }
    //$livePrice = getLiveCoinPrice($tracking[$x][0]);
    //$avgCoinPrice = getAveragePrice($coin);
    //echo "<BR>AvgCoinPrice: ".$avgCoinPrice[0][0]." CoinPrice: ".$bitPrice;
    //if ($avgCoinPrice > $bitPrice){ return; }
    //$quantity = Round($btcBuyAmount/$bitPrice,8,PHP_ROUND_HALF_UP);
    //if ($baseCurrency == 'BTC'){
    //  $bitCoinPrice = number_format((float)(bittrexCoinPrice($apikey, $apisecret,'USDT','BTC',$apiVersion)), 8, '.', '');
    //  $newMinTradeAmount = $minTradeAmount[0][0]/$bitCoinPrice;
    //}elseif ($baseCurrency == 'ETH'){
    //  $ethCoinPrice = number_format((float)(bittrexCoinPrice($apikey, $apisecret,'USDT','ETH',$apiVersion)), 8, '.', '');
    //  $newMinTradeAmount = $minTradeAmount[0][0]/$ethCoinPrice;
    //}else{
      $newMinTradeAmount = $minTradeAmount[0][0];
    //}

    if ($btcBuyAmount >= $newMinTradeAmount  && $BTCBalance >= $buyMin){
        echo "Quantity above min trade amount";
        //buyCoins($apikey, $apisecret,$coin, $quantity, $bitPrice, $email,$minTradeAmount, $userID, $totalScore,$date, $baseCurrency);
        $orderNo = "ORD".$coin.date("YmdHis", time()).$ruleID;
        echo "Buy Coin = $buyCoin";
        if ($buyCoin){
          if ($BTCBalance < $originalBuyAmount){ $btcBuyAmount = round(($BTCBalance-$userSavingAmount)/$bitPrice,10);}
            //if ($BTCBalance-$userSavingAmount >= $buyMin){

          $btcBuyAmount = number_format($btcBuyAmount,10);
          $bitPrice = number_format($bitPrice,8);
          if ($baseCurrency == 'BTC' OR $baseCurrency == 'ETH'){
            //$btcBuyAmount = number_format($btcBuyAmount/$bitPrice,10);
          }
          $obj = bittrexbuy($apikey, $apisecret, $coin, $btcBuyAmount, $bitPrice, $baseCurrency,$apiVersion,FALSE);
          LogToSQL("BuyCoinTest","bittrexbuy($apikey, $apisecret, $coin, $btcBuyAmount, $bitPrice, $baseCurrency,$apiVersion,FALSE);",3,1);
          //writeSQLBuy($coin, $quantity, $bitPrice, $date, $orderNo, $userID, $baseCurrency);
          logToSQL("Bittrex", "bittrexbuy($apikey, $apisecret, $coin, $btcBuyAmount, $bitPrice, $baseCurrency,$apiVersion,FALSE);", $userID,1);
          if ($apiVersion == 1){$bittrexRef = $obj["result"]["uuid"];$status = $obj["success"];}
          else{$bittrexRef = $obj["id"];
            if ($obj['status'] == 'OPEN'){$status = 1; }else{$status = 0;} }

          logToSQL("AddBuyCoin", "$bittrexRef $status ".$obj['status']." $coinID $bitPrice $btcBuyAmount $orderNo", $userID,1);
          if ($bittrexRef <> ""){
            $retBuy = 1;
            echo "bittrexBuyAdd($coinID, $userID, 'Buy', $bittrexRef, $status, $ruleID, $bitPrice, $btcBuyAmount, $orderNo);";
            date_default_timezone_set('Asia/Dubai');
            //$tmpTime = $timeToCancelBuyMins;
            //$newDate = date("Y-m-d H:i:s", time());
            //$current_date = date('Y-m-d H:i:s');
            //$newTime = date("Y-m-d H:i:s",strtotime('+'.$timeToCancelBuyMins.'Mins', strtotime($current_date)));
            //$buyCancelTime = strtotime( '+ 16 minute');
            bittrexBuyAdd($coinID, $userID, 'Buy', $bittrexRef, 1, $ruleID, $bitPrice, $btcBuyAmount, $orderNo,$timeToCancelBuyMins,$SellRuleFixed);
            LogToSQL("bittrexBuyAdd","bittrexBuyAdd($coinID, $userID, 'Buy', $bittrexRef, 1, $ruleID, $bitPrice, $btcBuyAmount, $orderNo,$timeToCancelBuyMins);", $userID,1);
            bittrexAddNoOfPurchases($bittrexRef,$noOfPurchases);
            LogToSQL("bittrexBuyAdd","bittrexAddNoOfPurchases($bittrexRef,$noOfPurchases);", $userID,1);
            addBuyRuletoSQL($bittrexRef,$ruleID,$SellRuleFixed);
            LogToSQL("bittrexBuyAdd","addBuyRuletoSQL($bittrexRef,$ruleID,$SellRuleFixed);", $userID,1);
            logToSQL("Bittrex", "Add Buy Coin $bitPrice $btcBuyAmount $orderNo", $userID,1);
            //CustomisedSellRule($ruleID,$SellRuleFixed,$coinID);
            //writeBittrexActionBuy($coinID,$userID,'Buy',$bittrexRef,$date,$status,$bitPrice,$ruleID);
            //if ($SellRuleFixed !== "ALL"){writeFixedSellRule($SellRuleFixed,$bittrexRef);}
            addCoinAllocationOverride($overrideCoinAlloc,$bittrexRef);
            logAction("BITTREX BUY COIN| $coin | $bitPrice | $btcBuyAmount | ", 'BuySellFlow', 1);
          }
          logAction("Bittrex Status:  ".json_encode($obj), 'BuySell', 0);
          logToSQL("Bittrex", "Add Buy Coin: ".json_encode($obj), $userID,1);
        }else{
          $retBuy = 2;
        }
        if ($sendEmail==1 && $buyCoin ==0){
        //if ($sendEmail){
          sendEmail($email, $coin, $btcBuyAmount, $bitPrice, $orderNo, $score, $subject,$userName, $from,$baseCurrency);
        }
    }else{
      //addCoinPurchaseDelay($coinID,$userID,120,0);
      pauseRule($ruleID, 24);
      clearTrackingCoinQueue($userID,$coinID);
      buyBackDelay($coinID,4320,$userID);
      echo "<BR> BITTREX BALANCE INSUFFICIENT $coin: $btcBuyAmount>".$newMinTradeAmount;
      logAction("BITTREX BALANCE INSUFFICIENT| $coin: $btcBuyAmount>".$newMinTradeAmount." && $BTCBalance >= $buyMin", 'BuySellFlow', 0);
      logToSQL("Bittrex", "BITTREX BALANCE INSUFFICIENT $coin: $btcBuyAmount>".$newMinTradeAmount." && $BTCBalance >= $buyMin", $userID,1);
      $retBuy = 2;
    }
  //}
  return $retBuy;
}

function getNewSwapCoin($baseCurrency){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "SELECT `Bi`.`CoinID`,`Bi`.`TopPrice`,`Bi`.`LowPrice`,`Bi`.`Difference`,`Cp`.`LiveCoinPrice`, `Cn`.`Symbol`
          FROM `BounceIndex` `Bi`
			     Join `CoinPrice` `Cp` on `Cp`.`CoinID` = `Bi`.`CoinID`
           Join `Coin` `Cn` on `Cn`.`ID` = `Bi`.`CoinID`
           where `Bi`.`Difference` > 2.5 and `Cn`.`BaseCurrency` = '$baseCurrency'
           and `Cn`.`DoNotBuy` = 0 and `Cn`.`BuyCoin` = 1
            Order by `Difference` desc
            limit 1 ";
    print_r($sql);
    $result = $conn->query($sql);
    while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['CoinID'],$row['TopPrice'],$row['LowPrice'],$row['Difference'],$row['LiveCoinPrice']
      ,$row['Symbol']);}
    $conn->close();
    return $tempAry;
}

function getAPIConfig($userID){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "SELECT `APIKey`,`APISecret`,`KEK` FROM `UserConfig` WHERE `UserID` = $userID";
    print_r($sql);
    $result = $conn->query($sql);
    while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['APIKey'],$row['APISecret'],$row['KEK']);}
    $conn->close();
    return $tempAry;
}

function getCoinPurchaseSettings(){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "SELECT `Uscf`.`UserID`,`Uscf`.`NoOfCoinPurchase`,`Uscf`.`NoOfPurchases`
            FROM `UserConfig` `Uscf`";
    print_r($sql);
    $result = $conn->query($sql);
    while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['UserID'],$row['NoOfCoinPurchase'],$row['NoOfPurchases']);}
    $conn->close();
    return $tempAry;
}

function getCoinIDs(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  //$query = "SET time_zone = 'Asia/Dubai';";
  //$result = $conn->query($query);
  $sql = "SELECT `ID`, `Symbol`,`BaseCurrency` FROM `Coin` WHERE `BuyCoin` = 1 ";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['ID'],$row['Symbol'],$row['BaseCurrency']);}
  $conn->close();
  return $tempAry;
}

function getCoinDelayState($coinID,$userID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  //$query = "SET time_zone = 'Asia/Dubai';";
  //$result = $conn->query($query);
  $sql = "SELECT if(`DelayTime`<now(),1,0) as `DelayTime` FROM `DelayCoinPurchase` WHERE `CoinID` = $coinID and `UserID` = $userID
            Union Select 0;";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['DelayTime']);}
  $conn->close();
  return $tempAry;
}

function getCoinIDRuleID(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  //$query = "SET time_zone = 'Asia/Dubai';";
  //$result = $conn->query($query);
  $sql = "SELECT `ID`,`LimitToCoinID` FROM `SellRules` WHERE `PctFromHighSellPriceEnabled` = 1";
  print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['ID'],$row['LimitToCoinID']);}
  $conn->close();
  return $tempAry;
}

function getPriceDipCoinPrices($coinID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  //$query = "SET time_zone = 'Asia/Dubai';";
  //$result = $conn->query($query);
  $sql = "SELECT `ID`, `CoinID`, `Price`, `PriceDipDate` FROM `PriceDipCoins` WHERE `CoinID` = $coinID order by `PriceDipDate` desc ";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['ID'],$row['CoinID'],$row['Price'],$row['PriceDipDate']);}
  $conn->close();
  return $tempAry;
}

function getTotalCoinPurchases(){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "SELECT `UserID`, count(`CoinID`)as CountOfCoinID FROM `Transaction` WHERE `Status` in ('Pending','Open')";
    print_r($sql);
    $result = $conn->query($sql);
    while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['UserID'],$row['CountOfCoinID']);}
    $conn->close();
    return $tempAry;
}

function getCoinPurchasesByCoin(){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "SELECT `UserID`,`CoinID`, count(`CoinID`)as CountOfCoinID FROM `Transaction` WHERE `Status` in ('Pending','Open')
            group by `CoinID`";
    print_r($sql);
    $result = $conn->query($sql);
    while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['UserID'],$row['CoinID'],$row['CountOfCoinID']);}
    $conn->close();
    return $tempAry;
}

function updateCoinSwapTable($transactionID,$status,$bittrexRef,$newCoinID,$newCoinPrice,$baseCurrency,$totalAmount,$purchasePrice,$buyFlag){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    if ($buyFlag == 'Buy'){
      $sql = "INSERT INTO `SwapCoins`(`TransactionID`, `Status`, `BittrexRef`, `NewCoinIDCandidate`, `NewCoinPrice`, `BaseCurrency`, `TotalAmount`, `OriginalPurchaseAmount`)
      VALUES ($transactionID,'$status','$bittrexRef',$newCoinID,$newCoinPrice,'$baseCurrency',$totalAmount,$purchasePrice)";
    }else{
      $sql = "INSERT INTO `SwapCoins`(`TransactionID`, `Status`, `BittrexRefSell`, `NewCoinIDCandidate`, `NewCoinPrice`, `BaseCurrency`, `TotalAmount`, `OriginalPurchaseAmount`)
      VALUES ($transactionID,'$status','$bittrexRef',$newCoinID,$newCoinPrice,'$baseCurrency',$totalAmount,$purchasePrice)";
    }

    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    newLogToSQL("updateCoinSwapTable",$sql,3,0,"SQL","BittrexID:$bittrexRef");
    $conn->close();
}

function updateCoinSwapTransactionStatus($status,$transID){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `Transaction` SET `Status` = '$status' where `ID` = $transID";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    newLogToSQL("updateCoinSwapTransactionStatus",$sql,3,0,"SQL","TransID:$transID");
    LogAction("updateCoinSwapTransactionStatus:".$sql, 'SQL_UPDATE', 0);
    $conn->close();
}

function addOldBuyBackTransID($bBID,$tmpCoinID){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `TrackingCoins` SET `OldBuyBackTransID` = (SELECT `TransactionID` FROM `BuyBack` WHERE `ID` = $bBID),`Type` = 'BuyBack'
    WHERE `CoinID` = $tmpCoinID order by `TrackDate` desc limit 1";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    newLogToSQL("addOldBuyBackTransID",$sql,3,1,"SQL","BbID:$bBID");
    LogAction("addOldBuyBackTransID:".$sql, 'SQL_UPDATE', 0);
    $conn->close();
}

function addBuyBackTransID($bBTransID,$transactionID){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "call addOldBuyBackTransID($transactionID,$bBTransID);";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    newLogToSQL("addBuyBackTransID",$sql,3,1,"SQL","BbID:$bBTransID;TransID:$transactionID");
    LogAction("addBuyBackTransID:".$sql, 'SQL_UPDATE', 0);
    $conn->close();
}

function deleteMultiSellRuleConfig($transactionID){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "DELETE FROM `MultiSellRuleConfig` WHERE `TransactionID` =  $transactionID; ";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    newLogToSQL("deleteMultiSellRuleConfig",$sql,3,1,"SQL","BbID:$bBID;TrackID:$trackingID");
    LogAction("deleteMultiSellRuleConfig:".$sql, 'SQL_UPDATE', 0);
    $conn->close();
}

Function getOpenCoinSwaps(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  //$query = "SET time_zone = 'Asia/Dubai';";
  //$result = $conn->query($query);
  $sql = "SELECT `IDTr`, `Status`, `BittrexRef`, `NewCoinIDCandidate`, `NewCoinPrice`, `BaseCurrency`, `TotalAmount`, `OriginalPurchaseAmount`
            , `Apikey`, `ApiSecret`, `KEK`,`Symbol`,`CoinID` as `OriginalCoinID`, `OriginalSymbol` ,`BittrexRefSell`,`SellFinalPrice`,`LiveCoinPrice`,`UserID`
            ,`IDSc` as CoinSwapID,`PctToBuy`, ((`LiveCoinPrice`-`Live1HrChange`)/`Live1HrChange`)*100 as `Hr1PctChange`,`LiveCoinPrice`,`RebuySavingsEnabled`
            FROM `View8_SwapCoin`";
  print_r("<BR>".$sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['IDTr'],$row['Status'],$row['BittrexRef'],$row['NewCoinIDCandidate'],$row['NewCoinPrice'],$row['BaseCurrency'],$row['TotalAmount'],$row['OriginalPurchaseAmount'],$row['APIKey'],$row['APISecret'] //9
    ,$row['KEK'],$row['Symbol'],$row['OriginalCoinID'],$row['OriginalSymbol'],$row['BittrexRefSell'],$row['SellFinalPrice'],$row['LiveCoinPrice'],$row['UserID'],$row['CoinSwapID'],$row['PctToBuy'],$row['Hr1PctChange'],$row['LiveCoinPrice'] //21
  ,$row['RebuySavingsEnabled']);
  }
  $conn->close();
  return $tempAry;
}

Function getOldMultiSell($oldBuyBackTransID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  //$query = "SET time_zone = 'Asia/Dubai';";
  //$result = $conn->query($query);
  $sql = "SELECT `MultiSellRuleEnabled`,`MultiSellRuleTemplateID` FROM `Transaction` WHERE `ID` = (SELECT `OldBuyBackTransID` FROM `BittrexAction` WHERE `ID` = $oldBuyBackTransID)";
  print_r("<BR>".$sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['MultiSellRuleEnabled'],$row['MultiSellRuleTemplateID']);
  }
  $conn->close();
  newLogToSQL("getOldMultiSell", "$sql", 3, 1,"SQL CALL","TransactionID:$oldBuyBackTransID");
  return $tempAry;
}

Function getCoinPrice($coinID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  //$query = "SET time_zone = 'Asia/Dubai';";
  //$result = $conn->query($query);
  $sql = "SELECT `Cp`.`LiveCoinPrice`
, ((`Cp`.`LiveCoinPrice`-`Cpc`.`Live1HrChange`)/`Cpc`.`Live1HrChange`)*100 as Hr1PctChange
, ((`Cp`.`LiveCoinPrice`-`Cpc`.`Live24HrChange`)/`Cpc`.`Live24HrChange`)*100 as Hr24PctChange
,((`Cp`.`LiveCoinPrice`-`Cpc`.`Live7DChange`)/`Cpc`.`Live7DChange`)*100 as D7PctChange
from `CoinPrice` `Cp`
join `CoinPctChange` `Cpc` on `Cpc`.`CoinID` = `Cp`.`CoinID`
where `Cp`.`CoinID` = $coinID";
  //print_r("<BR>".$sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['LiveCoinPrice'],$row['Hr1PctChange'],$row['Hr24PctChange'],$row['D7PctChange']);
  }
  $conn->close();
  return $tempAry;
}

function updateCoinSwapStatus($status,$transID){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "UPDATE `SwapCoins` SET `Status` = '$status' where `TransactionID` = $transID";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    newLogToSQL("updateCoinSwapStatus",$sql,3,0,"SQL","BittrexID:$bittrexRef");
    $conn->close();
}

function updateCoinSwapStatusCoinSwapID($status,$transactionID){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "UPDATE `SwapCoins` SET `Status` = '$status' where `TransactionID` = $transactionID";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    newLogToSQL("updateCoinSwapStatusCoinSwapID",$sql,3,1,"SQL","TransID:$transactionID");
    $conn->close();
}

function reopenCoinSwap($transID){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "UPDATE `SwapCoins` SET `Status` = 'AwaitingSavingBuy' where `TransactionID` = $transID";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    newLogToSQL("reopenCoinSwap",$sql,3,1,"SQL","TransID:$transID");
    $conn->close();
}

function updateCoinSwapStatusFinalPrice($status,$transID,$finalPrice){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `SwapCoins` SET `Status` = '$status', `SellFinalPrice` = $finalPrice where `TransactionID` = $transID";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    newLogToSQL("updateCoinSwapStatusFinalPrice",$sql,3,1,"SQL","BittrexID:$bittrexRef");
    $conn->close();
}

function updateCoinSwapCoinDetails($coinID, $coinPrice, $amount, $orderNo, $status, $transID){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    //$sql = "UPDATE `Transaction` SET `CoinID` = $coinID, `CoinPrice` = CASE When `CoinID` = $coinID THEN (`CoinPrice` + $coinPrice)/2 ELSE `CoinPrice`= $coinPrice END
    //`Amount` = $amount,`OrderNo` = '$orderNo', `Status` = '$status', `DelayCoinSwapUntil` = date_add(now(),INTERVAL 14 DAY),`OriginalAmount` = 0
    // where `ID` = $transID";
    $sql = "UPDATE `Transaction` SET `CoinID` = $coinID, `CoinPrice` = $coinPrice,
    `Amount` = $amount,`OrderNo` = '$orderNo', `Status` = '$status', `DelayCoinSwapUntil` = date_add(now(),INTERVAL 14 DAY),`OriginalAmount` = 0
    where `ID` = $transID";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    newLogToSQL("updateCoinSwapCoinDetails",$sql,3,0,"SQL","BittrexID:$bittrexRef");
    LogAction("updateCoinSwapCoinDetails:",$sql, 'SQL_UPDATE', 0);
    $conn->close();
}

function updateCoinSwapBittrexID($bittrexRef,$transactionID,$newCoinID,$newPrice,$buyFlag, $sellFinalPrice = 0.0){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    if ($buyFlag == 'Buy'){
        $sql = "UPDATE `SwapCoins` SET `BittrexRef` = '$bittrexRef',`NewCoinIDCandidate`= $newCoinID,`NewCoinPrice` = $newPrice, `SellFinalPrice` = $sellFinalPrice where `TransactionID` = $transactionID";
    }else{
      $sql = "UPDATE `SwapCoins` SET `BittrexRefSell` = '$bittrexRef',`NewCoinIDCandidate`= $newCoinID,`NewCoinPrice` = $newPrice, `SellFinalPrice` = $sellFinalPrice where `TransactionID` = $transactionID";
    }

    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    newLogToSQL("updateCoinSwapBittrexID",$sql,3,1,"SQL","BittrexID:$bittrexRef");
    $conn->close();
}

function addCoinAllocationOverride($overrideCoinAlloc, $bittrexRef){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `Transaction` SET `OverrideCoinAllocation`= $overrideCoinAlloc WHERE `BittrexRef` = '$bittrexRef'";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    newLogToSQL("addCoinAllocationOverride",$sql,3,sQLUpdateLog,"SQL","BittrexID:$bittrexRef");
    $conn->close();
}

function writeFixedSellRule($SellRuleFixed,$bittrexRef){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `Transaction` SET `FixSellRule`= '$SellRuleFixed' WHERE `BittrexRef` = '$bittrexRef'";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    newLogToSQL("writeFixedSellRule",$sql,3,sQLUpdateLog,"SQL","BittrexRef:$bittrexRef");
    $conn->close();
}

function writeSQLTransBuy($type, $coinID,$userID, $cost,$amounttobuy, $date, $BittrexID, $orderNo){
  $currentDate = date("Y-m-d H:i:s", time());
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "INSERT INTO `Transaction`(`Type`, `CoinID`, `UserID`, `CoinPrice`, `Amount`, `Status`, `OrderDate`, `CompletionDate`, `BittrexID`, `OrderNo`)
    VALUES ('$type',$coinID, $userID,$cost,$amounttobuy,'Open', '$date','$currentDate' ,$BittrexID,'$orderNo')";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    logAction("writeSQLTransBuy: ".$sql, 'SQL_INSERT', 0);
}

function writeBittrexActionBuy($coinID,$transactionID,$userID,$type,$bittrexRef,$date,$status,$sellPrice,$ruleID){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "INSERT INTO `BittrexAction`(`CoinID`, `TransactionID`, `UserID`, `Type`, `BittrexRef`, `ActionDate`, `CompletionDate`, `Status`, `SellPrice`, `RuleID`)
    VALUES ($coinID,$transactionID,$userID,'$type','$bittrexRef','$date','$status',$sellPrice,$ruleID)";
    //VALUES ('$type','$apikey', '$apisecret','$coin', '$email', $userID, $totalScore,'$date', '$baseCurrency',$sendEmail,$buyCoin,'$ruleID','$userName','$orderNo',$newBTCAmount,$bitPrice,'$status','$bittrexRef')";
    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    logAction("writeBittrexActionBuy: ".$sql, 'BuySell', 0);
}

function disableBuyRule($ruleIDBuy){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `BuyRules` SET `BuyCoin` = 0 where `ID` = $ruleIDBuy";
    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    logAction("disableBuyRule: ".$sql, 'BuySell', 0);
    newLogToSQL("disableBuyRule",$sql,3,1,"SQL","RuleID:$ruleIDBuy");
}

function reOpenOneTimeBuyRule($trackingID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "Update `BuyRules` set `BuyCoin` = 1 where `ID` = (SELECT `RuleIDBuy` FROM `TrackingCoins` WHERE `ID` = $trackingID ) and `OneTimeBuyRule` = 1; ";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("reOpenOneTimeBuyRule: ".$sql, 'BuySell', 0);
  newLogToSQL("reOpenOneTimeBuyRule",$sql,3,1,"SQL","TrackingID:$trackingID");
}

function sendEmail($to, $symbol, $amount, $cost, $orderNo, $score, $subject, $user, $from, $baseCurrency){
    $body = "Dear ".$user.", <BR/>";
    $body .= "Congratulations you have bought the following Coin: "."<BR/>";
    $body .= "Coin: ".$symbol.":".$baseCurrency." Amount: ".$amount." Price: ".$cost."<BR/>";
    $body .= "Order Number: ".$orderNo."<BR/>";
    //$body .= "Score: ".$score."<BR/>";
    $body .= "Kind Regards\nCryptoBot.";
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    //$headers .= 'From: Alerts <Alerts@Investment-Tracker.net>' . "\r\n";
    $headers .= "From:".$from."\r\n";
    $headers .= "To:".$to."\r\n";
    mail($to, $subject, wordwrap($body,70),$headers);
}

function bittrexbalance($apikey, $apisecret, $base, $versionNum){
    $nonce=time();
    if ($versionNum == 1){
        $uri='https://bittrex.com/api/v1.1/account/getbalance?apikey='.$apikey.'&currency='.$base.'&nonce='.$nonce;
        $sign=hash_hmac('sha512',$uri,$apisecret);
        $ch = curl_init($uri);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $execResult = curl_exec($ch);
        $obj = json_decode($execResult, true);
        $balance = $obj["result"]["Available"];
    }elseif ($versionNum == 3){
      $timestamp = time()*1000;
      $url = "https://api.bittrex.com/v3/balances/".$base;
      $method = "GET";
      $content = "";
      $subaccountId = "";
      $contentHash = hash('sha512', $content);
      $preSign = $timestamp . $url . $method . $contentHash . $subaccountId;
      $signature = hash_hmac('sha512', $preSign, $apisecret);

      $headers = array(
      "Accept: application/json",
      "Content-Type: application/json",
      "Api-Key: ".$apikey."",
      "Api-Signature: ".$signature."",
      "Api-Timestamp: ".$timestamp."",
      "Api-Content-Hash: ".$contentHash.""
      );

      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($ch, CURLOPT_HEADER, FALSE);
      $execResult = curl_exec($ch);
      curl_close($ch);
      $temp = json_decode($execResult, true);
      echo "<BR> $url";
      newLogtoSQL("BittrexAPI",var_dump($temp),3,0,"Balance","Base:$base");
      $balance = $temp['total'];
    }
    return $balance;
}

function bittrexbuy($apikey, $apisecret, $symbol, $quant, $rate,$baseCurrency, $versionNum, $useAwards){
    Echo "bittrexbuy($apikey, $apisecret, $symbol, $quant, $rate,$baseCurrency)";
    $nonce=time();
    if ($versionNum == 1){
      $uri='https://bittrex.com/api/v1.1/market/buylimit?apikey='.$apikey.'&market='.$baseCurrency.'-'.$symbol.'&quantity='.$quant.'&rate='.$rate.'&nonce='.$nonce;
      echo "<BR>".$uri."<BR>";
      $sign=hash_hmac('sha512',$uri,$apisecret);
      $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $execResult = curl_exec($ch);
      $obj = json_decode($execResult, true);
    }elseif ($versionNum == 3){
      $timestamp = time()*1000;
      $url = "https://api.bittrex.com/v3/orders";
      $method = "POST";

      $content = '{
        "marketSymbol": "'.$symbol.'-'.$baseCurrency.'",
        "direction": "BUY",
        "type": "LIMIT",
        "quantity": "'.$quant.'",
        "limit": "'.$rate.'",
        "timeInForce": "GOOD_TIL_CANCELLED",
        "useAwards": "'.$useAwards.'"
      }';
      echo "<BR>".$content;
      $subaccountId = "";
      $contentHash = hash('sha512', $content);
      $preSign = $timestamp . $url . $method . $contentHash . $subaccountId;
      $signature = hash_hmac('sha512', $preSign, $apisecret);

      $headers = array(
      "Accept: application/json",
      "Content-Type: application/json",
      "Api-Key: ".$apikey."",
      "Api-Signature: ".$signature."",
      "Api-Timestamp: ".$timestamp."",
      "Api-Content-Hash: ".$contentHash.""
      );

      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($ch, CURLOPT_HEADER, FALSE);
      curl_setopt($ch, CURLOPT_POST, TRUE);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
      $execResult = curl_exec($ch);
      curl_close($ch);
      echo "<BR>$execResult";
      $obj = json_decode($execResult, true);
    }

    return $obj;
}

function getMinTradeAmount($apiKey, $apisecret){
  $obj = getMinTrade($apiKey, $apisecret, 3);
  //$obj = json_decode($minTrade, true);
  //$minTradeSize = count($minTrade);
  $tradeArraySize = count($obj);
  //print_r($obj);
  $coins = getTrackingCoins("WHERE `DoNotBuy` = 0 and `BuyCoin` = 1 ORDER BY `Symbol` ASC","FROM `View1_BuyCoins` ");
  $coinsSize = count($coins);
  //echo "<BR> array sizes | TRadeArySize: ".$tradeArraySize." coinsSize: $coinsSize entry1: ";
  for ($x=0; $x<$coinsSize; $x++){
    $baseCurrency = $coins[$x][26]; $coin = $coins[$x][1]; $coinID = $coins[$x][0];
    //echo "<BR> COIN: $coin BASE: $baseCurrency ID: $coinID";
    //for($y = 0; $y < $tradeArraySize; $y++) {
    for ($y=0; $y<$tradeArraySize; $y++){
      //echo "<BR> Symbol: ".$obj[$y]['symbol']."|".$coin."-".$baseCurrency;
      if($obj[$y]['symbol'] == $coin."-".$baseCurrency){
        $minTradeAmount = $obj[$y]['minTradeSize'];
        $precision = $obj[$y]['precision'];
        //return $minTradeAmount;
        echo "<BR> Coin Match: $coin Base: $baseCurrency ID: $coinID Min: $minTradeAmount";
        copyTradeAmountToSQL($coinID, $minTradeAmount,$precision);
        continue;
      }
    }
  }

}

function getMinTradeFromSQL($coinID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `MinTradeSize` FROM `Coin` WHERE `ID` = $coinID";
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['MinTradeSize']);}
  $conn->close();
  return $tempAry;
}

function copyTradeAmountToSQL($coinID, $minTradeAmount, $precision){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `Coin` SET `MinTradeSize`= $minTradeAmount, `CoinPrecision` = $precision WHERE `ID` = $coinID";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("copyTradeAmountToSQL",$sql,3,sQLUpdateLog,"SQL","CoinID:$coinID");
  logAction("copyTradeAmountToSQL: ".$sql, 'BuySell', 0);
}

function getMinTrade($apikey, $apisecret, $versionNum){
  $nonce=time();
  //$uri='https://bittrex.com/api/v1.1/account/getbalance?apikey='.$apikey.'&currency=BTC&nonce='.$nonce;
  if ($versionNum == 1){
      $uri="https://bittrex.com/api/v1.1/public/getmarkets";
      $sign=hash_hmac('sha512',$uri,$apisecret);
      $ch = curl_init($uri);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $execResult = curl_exec($ch);
      $obj = json_decode($execResult, true);
  }elseif ($versionNum == 3){
    $timestamp = time()*1000;
    $url = "https://api.bittrex.com/v3/markets";
    $method = "GET";
    $content = "";
    $subaccountId = "";
    $contentHash = hash('sha512', $content);
    $preSign = $timestamp . $url . $method . $contentHash . $subaccountId;
    $signature = hash_hmac('sha512', $preSign, $apisecret);

    $headers = array(
    "Accept: application/json",
    "Content-Type: application/json",
    "Api-Key: ".$apikey."",
    "Api-Signature: ".$signature."",
    "Api-Timestamp: ".$timestamp."",
    "Api-Content-Hash: ".$contentHash.""
    );

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    $obj = curl_exec($ch);
    curl_close($ch);
    $obj = json_decode($obj,true);
  }


  //$balance = $obj["result"]["MinTradeSize"];
  return $obj;
}

function bittrexCoinPrice($apikey, $apisecret, $baseCoin, $coin, $versionNum){
      $nonce=time();
      if ($versionNum == 1){
          $uri='https://bittrex.com/api/v1.1/public/getticker?market='.$baseCoin.'-'.$coin;
          $sign=hash_hmac('sha512',$uri,$apisecret);
          $ch = curl_init($uri);
              curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          $execResult = curl_exec($ch);
          $obj = json_decode($execResult, true);
          $balance = $obj["result"]["Last"];
      }elseif ($versionNum == 3){
        $timestamp = time()*1000;
        $url = "https://api.bittrex.com/v3/markets/".$coin."-".$baseCoin."/ticker";
        //echo "<BR>".$url;
        $method = "GET";
        $content = "";
        $subaccountId = "";
        $contentHash = hash('sha512', $content);
        $preSign = $timestamp . $url . $method . $contentHash . $subaccountId;
        $signature = hash_hmac('sha512', $preSign, $apisecret);

        $headers = array(
        "Accept: application/json",
        "Content-Type: application/json",
        "Api-Key: ".$apikey."",
        "Api-Signature: ".$signature."",
        "Api-Timestamp: ".$timestamp."",
        "Api-Content-Hash: ".$contentHash.""
        );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        $balance = curl_exec($ch);
        curl_close($ch);
        $temp = json_decode($balance, true);
        //var_dump($temp);
        $balance = $temp['lastTradeRate'];
      }
      //echo "<br> CoinPrice: $coin : $baseCoin<br>";
      //var_dump($temp);
      return $balance;
}

function writeBittrexAction($coinID,$transactionID,$userID,$type,$bittrexRef,$date,$status,$sellPrice){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "INSERT INTO `BittrexAction`( `CoinID`, `TransactionID`, `UserID`, `Type`, `BittrexRef`, `ActionDate`,  `Status`, `SellPrice`)
    VALUES ($coinID,$transactionID,$userID,'$type','$bittrexRef','$date','$status' , $sellPrice)";
    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    logAction("writeBittrexAction: ".$sql, 'BuySell', 0);
}

function getCoinMarketCapStats(){
    $limit = 99;
    $start = 0;
    for($n=0;$n<5;$n++){
      $cnmkt = "https://api.coinmarketcap.com/v1/ticker/?limit=".$limit."&start=".$start;
      echo "<BR> CNMKT: ".$cnmkt;
      $fgc = json_decode(file_get_contents($cnmkt), true);
      for($i=0;$i<$limit;$i++){
        //echo "<br> : ".$fgc[$i]["symbol"]." : ".$fgc[$i]["market_cap_usd"];
          $tmpCoinPrice[] = Array($fgc[$i]["symbol"],$fgc[$i]["market_cap_usd"],$fgc[$i]["percent_change_1h"],$fgc[$i]["percent_change_24h"],$fgc[$i]["percent_change_7d"],$fgc[$i]["percent_change_30d"],$fgc[$i]["id"]);
      }
      $start = $start + $limit + 1;
    }
    logAction("$cnmkt | ".json_decode($tmpCoinPrice),'CMC', 0);
  return $tmpCoinPrice;
}

function getCMCID($symbol){
    $temp = "";
    $symbol_str = explode(",",$symbol);
    $symbolCount = count($symbol_str);
    for ($x = 0; $x < $symbolCount; $x++) {
      //echo $symbol_str[$x];
      if ($symbol_str[$x] == "BTC"){$temp =$temp."1,";}
      elseif ($symbol_str[$x] == "ETH"){$temp =$temp."1027,";}
      elseif ($symbol_str[$x] == "BCH"){$temp =$temp."1831,";}
      elseif ($symbol_str[$x] == "XRP"){$temp =$temp."52,";}
    }
    return rtrim($temp, ',');
}

function newCoinMarketCapStats($coinMarketID){
  //$coinMarketID = getCMCID($symbol);
  echo "<BR> Getting CMC Stats";
  $url = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest';
  $parameters = [
    'id' => $coinMarketID
  ];
  //echo "<BR> : $coinMarketID";
  $cmcKey = getCMCKey();
  $headers = [
    'Accepts: application/json',
    'X-CMC_PRO_API_KEY: '.$cmcKey
  ];
  $qs = http_build_query($parameters); // query string encode the parameters
  $request = "{$url}?{$qs}"; // create the request URL


  $curl = curl_init(); // Get cURL resource
  // Set cURL options
  curl_setopt_array($curl, array(
    CURLOPT_URL => $request,            // set the request URL
    CURLOPT_HTTPHEADER => $headers,     // set the headers
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0'         // ask for raw response instead of bool
  ));

  $response = curl_exec($curl); // Send the request, save the response
  //var_dump($response);
  $temp = json_decode($response, true);
  //$tempCount = count($temp);
  //echo "<br>HERE! ".$temp['data'][1][1]['quote'][1]['market_cap'];
  //echo "<br>HERE5! ".$temp['data'][1]['quote']['USD']['market_cap'];
  //print_r($temp);
  //for($i=0;$i<$tempCount;$i++){
  echo "<BR> $coinMarketID";
  $coin = explode(",",$coinMarketID);
  $i = 1;
  $coinCount = count($coin);
  for($i=0;$i<$coinCount;$i++){
    $tempId = (Int)$coin[$i];
    echo "<BR> newCoinMarketCapStats: ".$temp['data'][$tempId]['symbol'];
    $tmpCMCAry[] = Array($temp['data'][$tempId]['symbol'],$temp['data'][$tempId]['quote']['USD']['market_cap'],$temp['data'][$tempId]['quote']['USD']['percent_change_1h'],
    $temp['data'][$tempId]['quote']['USD']['percent_change_24h'],$temp['data'][$tempId]['quote']['USD']['percent_change_7d'],$temp['data'][$tempId]['quote']['USD']['percent_change_30d'],$temp['data'][$tempId]['id']);
  }

  //echo "<BR> ".$temp['data'][52]['symbol'];

  //foreach ($temp as $item) {
    //Print_r($item);


    //echo "<BR>".$item[52]['symbol'];
    //
    //$i++;
  //}
  //print_r(json_decode($response)); // print json decoded response
  curl_close($curl); // Close request
  var_dump($temp);
  return $tmpCMCAry;
}

function CoinMarketCapStatstoSQL($coinID,$MarketCap,$hr1Change, $hr24Change, $d7Change){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call UpdateCMCStatstoSQL($coinID, $MarketCap,$hr1Change, $hr24Change, $d7Change);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("CoinMarketCapStatstoSQL($coinID,$MarketCap,$hr1Change, $hr24Change, $d7Change)",'CMC', 0);
  newLogToSQL("CoinMarketCapStatstoSQL","$sql",3,0,"SQL CALL","CoinID:$coinID");
}

function ResidualCoinsToSaving($amount, $orderNo, $transactionID,$originalAmount){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call ResidualCoinToSaving($amount, '$orderNo',$transactionID,$originalAmount);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("ResidualCoinsToSaving($sql)", 'SQL_CALL', 0);
  newLogToSQL("ResidualCoinsToSaving","$sql",3,0,"SQL CALL","TransactionID:$transactionID");
}

function BittrexStatstoSQL($coinID, $volume, $sellOrders, $buyOrders){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call UpdateBittrexStatstoSQL($coinID, $volume, $sellOrders, $buyOrders);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("BittrexStatstoSQL($coinID, $volume, $sellOrders, $buyOrders)",'CMC', 0);
  newLogToSQL("BittrexStatstoSQL","$sql",3,sQLUpdateLog,"SQL CALL","CoinID:$coinID");
}

function copyNewMarketCap($coinID,$MarketCap){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call UpdateCoinMarketCap($coinID, $MarketCap);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("UpdateCoinMarketCap","$sql",3,sQLUpdateLog,"SQL CALL","CoinID:$coinID");
}

function addBuyRuletoSQL($bittrexRef, $buyRule,$sellRule){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call AddBuyAndSellRules($buyRule,$sellRule, '$bittrexRef');";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("addBuyRuletoSQL","$sql",3,0,"SQL CALL","BittrexRef:$bittrexRef");
}

function addSellRuletoSQL($transactionID, $sellRule){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `Transaction` set `SellRule`= $buyRule WHERE `ID` = $transactionID";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("copyTradeAmountToSQL",$sql,3,sQLUpdateLog,"SQL","TransactionID:$transactionID");
}

function copyNewPctChange($coinID,$PctChange1Hr, $PctChange24Hr, $PctChange7D){
  $conn = getSQLConn(rand(1,3));
  Echo "<BR> call newUpdatePctChange($coinID, $PctChange1Hr, $PctChange24Hr, $PctChange7D);";
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call UpdatePctChange($coinID, $PctChange1Hr, $PctChange24Hr, $PctChange7D);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("copyNewPctChange","$sql",3,sQLUpdateLog,"SQL CALL","CoinID:$coinID");
}


function copyCoinVolume($coinID,$CoinVolume){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call UpdateCoinVolume($coinID, $CoinVolume);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("copyCoinVolume","$sql",3,sQLUpdateLog,"SQL CALL","CoinID:$coinID");
}

function getVolumeStats($stats, $apiVersion){
  $volume = 0.0; $symbol = "";  $high = 0.0; $low = 0.0;
  $OpenBuyOrders = 0; $OpenSellOrders = 0;
    if ($apiVersion == 1){
      foreach($stats['result'] as $item) {
        //print $item['MarketName'];
        $mktSym = explode("-",$item['MarketName']);
        $symbol = $mktSym[1];
        $market = $mktSym[0];
        $high = $item["High"];
        $low = $item["Low"];
        $volume = $item["Volume"];
        $last = $item["Last"];
        $BaseVolume = $item["BaseVolume"];
        $TimeStamp = $item["TimeStamp"];
        $Bid = $item["Bid"];
        $Ask = $item["Ask"];
        $OpenBuyOrders = $item["OpenBuyOrders"];
        $OpenSellOrders = $item["OpenSellOrders"];
        $PrevDay = $item["PrevDay"];
      }
    }else{
      //$mktSym = explode("-",$item['MarketName']);
      $symbol = $stats["symbol"];
      //$market = $mktSym[0];
      $high = $stats["high"];
      $low = $stats["low"];
      $volume = $stats["volume"];
      //$last = $item["Last"];
      //$BaseVolume = $item["BaseVolume"];
      //$TimeStamp = $item["TimeStamp"];
      //$Bid = $item["Bid"];
      //$Ask = $item["Ask"];
      $OpenBuyOrders = 0;
      $OpenSellOrders = 0;
      //$PrevDay = $item["PrevDay"];
    }

  $tempVolStats[] = Array($volume,$OpenBuyOrders,$OpenSellOrders);
  return $tempVolStats;
}

function copyCoinBuyOrders($coinID,$CoinBuyOrders){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call UpdateCoinBuyOrders($coinID, $CoinBuyOrders);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("copyCoinBuyOrders","$sql",3,sQLUpdateLog,"SQL CALL","CoinID:$coinID");
}

function copyCoinSellOrders($coinID,$CoinSellOrders){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call UpdateCoinSellOrders($coinID, $CoinSellOrders);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("copyCoinSellOrders","$sql",3,sQLUpdateLog,"SQL CALL","CoinID:$coinID");
}

function copyCoinPrice($coinID,$CoinPrice){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call NewUpdateCoinPrice($coinID, $CoinPrice);";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("copyCoinPrice","$sql",3,0,"SQL CALL","CoinID:$coinID");
}
function copyWebTable($coinID){
  $conn = getSQLConn(rand(1,3));
  Echo "<BR> Updating Web table : $coinID";
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call UpdateWebTable($coinID);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("copyWebTable","$sql",3,sQLUpdateLog,"SQL CALL","CoinID:$coinID");
}
function updateWebCoinStatsTable($coinID){
  $conn = getSQLConn(rand(1,3));
  Echo "<BR> Update Web Coin Stats : call UpdateWebCoinStats($coinID);";
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call UpdateWebCoinStats($coinID);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("updateWebCoinStatsTable","$sql",3,sQLUpdateLog,"SQL CALL","CoinID:$coinID");
}


function buyWithScore($buyTop,$buyBtm,$score,$buyEnabled,$echoEnabled, $name){
  $returnFlag = False;
  if ($buyEnabled == 0){
      //print_r("True");
      $returnFlag = True;
      //exit;
  }elseif ($buyTop >= $score && $buyBtm <= $score && $buyEnabled == 1){
      //print_r("True");
      $GLOBALS['allDisabled'] = true;
      $returnFlag = True;
      //exit;
  }else {
    $GLOBALS['allDisabled'] = true;
    //print_r($buyTop >= $score);
    //print_r("False");
    $returnFlag = False;
  }
  //echo " buyWithScore $name Enabled:$returnFlag | Top:$buyTop btm:$buyBtm Score:$score ";
  echoAndLog($name, "buyWithScore $name Enabled:$returnFlag | Top:$buyTop btm:$buyBtm Score:$score",3,$echoEnabled,"","");
  return $returnFlag;
}

function buyWithMin($buyMinEnabled, $BuyMin, $LiveCoinPrice,$echoEnabled,$name){
  //echo "<BR>BuyMin $BuyMin LiveBTCPrice $LiveCoinPrice | $buyMinEnabled";
  $returnFlag = False;
  if ($buyMinEnabled == 0){
      //print_r("True");
      $returnFlag = True;
      //exit;
  }elseif ($LiveCoinPrice <= $BuyMin){
      //echo "<BR>BuyMin $BuyMin LiveCoinPrice $LiveCoinPrice | Live Greater than Buy Min";
      $GLOBALS['allDisabled'] = true;
      $returnFlag = True;
      //exit;
  }else {
    $GLOBALS['allDisabled'] = true;
    //print_r($buyTop >= $score);
    //print_r("False");
    $returnFlag = False;
  }
  //echo " buyWithMin $name Enabled:$returnFlag | Live:$LiveCoinPrice BuyMin:$BuyMin ";
  echoAndLog($name, "buyWithMin $name Enabled:$returnFlag | Live:$LiveCoinPrice BuyMin:$BuyMin",3,$echoEnabled,"","");
  return $returnFlag;
}

function checkPriceDipCoinFlat($priceDipCoinFlatEnabled,$priceDipHoursFlatTarget, $priceDipHours,$echoEnabled,$name){
  //echo "<BR>checkPriceDipCoinFlat $priceDipHoursFlatTarget LiveBTCPrice $priceDipHours | $priceDipCoinFlatEnabled";
  $returnFlag = False;
  if ($priceDipCoinFlatEnabled == 0){
      //print_r("True");
      $returnFlag = True;
      //exit;
  }elseif ($priceDipHours >= $priceDipHoursFlatTarget){
      //echo "<BR>BuyMin $priceDipHoursFlatTarget LiveCoinPrice $priceDipHours | Live Greater than Buy Min";
      $GLOBALS['allDisabled'] = true;
      //print_r("-True");
      $returnFlag = True;
      //exit;
  }else {
    $GLOBALS['allDisabled'] = true;
    //print_r($buyTop >= $score);
    //print_r("-False");
    $returnFlag = False;
  }
  echoAndLog($name, "checkPriceDipCoinFlat Enabled:$returnFlag | FlatTarget:$priceDipHoursFlatTarget | FlatHours:$priceDipHours",3,$echoEnabled,"","");
  return $returnFlag;
}

function sellWithMin($sellMinEnabled, $sellMin, $LiveCoinPrice, $LiveBTCPrice,$echoEnabled,$name){
  //echo "<BR>BuyMin: $sellMin | LiveBTCPrice: $LiveBTCPrice | LiveCoinPrice: $LiveCoinPrice | Enabled: $sellMinEnabled";
  $returnFlag = False;
  if ($sellMinEnabled == 0){
      //print_r("True");
      $returnFlag = True;
      //exit;
  }elseif ($LiveBTCPrice >= $sellMin){
      //echo "<BR>SellMin  LiveBTCPrice $LiveBTCPrice is less than $sellMin";
      $GLOBALS['allDisabled'] = true;
      //print_r("True");
      $returnFlag = True;
      //xit;
  }else {
    $GLOBALS['allDisabled'] = true;
    //print_r($buyTop >= $score);
    //print_r("False");
    $returnFlag = False;
  }
  echoAndLog($name, " sellWithMin Enabled:$returnFlag | MinPrice:$sellMin | LivePrice:$LiveCoinPrice | BTCPrice:$LiveBTCPrice ",3,$echoEnabled,"","");
  return $returnFlag;
}

function returnPattern($p3,$p2,$p1, $t3,$t2,$t1){
  $livePattern = $p3.$p2.$p1;
  $rulePattern = $t4.$t3.$t2.$t1;
  Echo "$livePattern : $rulePattern : ";
  if ($livePattern == $rulePattern){
    //Echo "<BR>This is True : $livePattern : $rulePattern <BR>";
    return true;
  }else{
    //Echo "<BR>This is Flase : $livePattern : $rulePattern <BR>";
    return false;
  }
}

function newReturnPattern($livePattern, $rulePattern){
  if ($livePattern == $rulePattern){
    //Echo "<BR>This is True newReturnPattern : $livePattern : $rulePattern <BR>";
    return true;
  }else{
    //Echo "<BR>This is Flase newReturnPattern : $livePattern : $rulePattern <BR>";
    return false;
  }
}

function buyRuleTest($livePattern, $rulePattern){
  if ($livePattern == $rulePattern){
    //Echo "<BR>This is True buyRuleTest : $livePattern : $rulePattern <BR>";
    return true;
  }else{
    //Echo "<BR>This is Flase buyRuleTest : $livePattern : $rulePattern <BR>";
    return false;
  }
}

function buywithPattern($p4,$p3,$p2,$p1,$t4,$t3,$t2,$t1,$tEnabled){
  $retPattern = returnPattern($p3,$p2,$p1,$t3,$t2,$t1);

  if ($tEnabled == 0){
      //print_r("<BR>True buywithPattern");
      return True;
      exit;
  }elseif ($retPattern){
      //print_r("<BR>True buywithPattern");
      $GLOBALS['allDisabled'] = true;
      return True;
      exit;
  }else {
    $GLOBALS['allDisabled'] = true;
    //print_r($buyTop >= $score);
    //print_r("False");
    return False;
  }
}

function newBuywithPattern($livePattern, $savedPattern, $pEnabled, $ruleID, $buySell,$echoEnabled,$name){
  //$buySell == 0 for buy ; 1 for sell
  $returnFlag = False;
  $pieces = removeWildcard($savedPattern);
  //echo "<BR> TempStr : ".$tmpStr;
  //$pieces = explode(",", $tmpStr);
  //var_dump($pieces[0]);
  $piecesSize = count($pieces);
  $testTrue = False;
  //echo var_dump($pieces);
  for ($x = 0; $x < $piecesSize; $x++) {
    //Echo "<br> ".$pieces[$x][0];
    if (($ruleID == $pieces[$x][0] && $pieces[$x][1] == 0 && $buySell == 0) OR ($ruleID == $pieces[$x][1] && $pieces[$x][0] == 0&& $buySell == 1)){
      //Echo "<br> ".$pieces[$x][0]." : ".$pieces[$x][1]." : ".$pieces[$x][2];
      if (newReturnPattern($livePattern,$pieces[$x][2])){ $testTrue = True; }//echo "<BR> LivePetern $livePattern TRUE";}
    }
  }
    if ($pEnabled == 0){
      //print_r("True");
      $returnFlag =  True;
      //exit;
    }
    elseif($testTrue){
      //print_r("True");
      $GLOBALS['allDisabled'] = true;
      $returnFlag =  True;
      //exit;
    }else{
      $GLOBALS['allDisabled'] = true;
      //print_r($buyTop >= $score);
      //print_r("False");
      $returnFlag =  False;
    }
  //}
  echoAndLog($name, " newBuywithPattern Enabled:$returnFlag | Live:$livePattern | Test:$savedPattern | Rule:$ruleID ",3,$echoEnabled,"","");
  return $returnFlag;

}

function limitToBuyRule($livePattern, $savedPattern, $pEnabled){
  $pieces = explode(",", $savedPattern);
  $piecesSize = count($pieces);
  $testTrue = False;
  for ($x = 0; $x < $piecesSize; $x++) {
    //Echo "<br> ".$pieces[$x];
    if (buyRuleTest($livePattern,$pieces[$x])){ $testTrue = True;}
  }
    if ($pEnabled == 0){
      //print_r("True");
      return True;
      exit;
    }
    elseif($testTrue){
      //print_r("True");
      $GLOBALS['allDisabled'] = true;
      return True;
      exit;
    }else{
      $GLOBALS['allDisabled'] = true;
      //print_r($buyTop >= $score);
      //print_r("False");
      return False;
    }
  //}
}

function sellWithScore($buyTop,$buyBtm,$score,$buyEnabled,$echoEnabled,$name){
  $returnFlag = False;
  if ($buyEnabled == 0){
      //print_r("True");
      $returnFlag =  True;
      //exit;
  }elseif ($buyTop >= $score && $buyBtm <= $score && $buyEnabled == 1){
      //print_r("True");
      $GLOBALS['allDisabled'] = true;
      $returnFlag =  True;
      //exit;
  }else {
    $GLOBALS['allDisabled'] = true;
    //print_r($buyTop >= $score);
    //print_r("False ".$score);
    $returnFlag =  False;
  }

  echoAndLog($name, " sellWithScore Enabled:$returnFlag | Top:$buyTop | Bottom:$buyBtm | Score:$score ",3,$echoEnabled,"","");
  return $returnFlag;
}

function autoBuyMain($LiveCoinPrice, $autoBuyPrice, $autoBuyCoinEnabled, $coinID,$echoEnabled,$name){
  $returnBool = False;
  $coinPriceAryCount = count($autoBuyPrice);
  if ($autoBuyCoinEnabled == 0){
    $returnBool = True;
    $coinPriceAryCount = 0;
  }
  for ($i = 0; $i<$coinPriceAryCount; $i++){
    $newCoinID = $autoBuyPrice[$i][0];  $newAutoBuyPrice = $autoBuyPrice[$i][1]; $autoSellPrice = $autoBuyPrice[$i][2];
    if ($coinID == $newCoinID){

      $returnBool = autoBuy($LiveCoinPrice,$newAutoBuyPrice,$autoSellPrice,$autoBuyCoinEnabled);
      //echo "<BR> autoBuy($LiveCoinPrice,$newAutoBuyPrice,$autoSellPrice,$autoBuyCoinEnabled); $returnBool";
      $finalNo = $i;
    }
  }

  //echo "<BR> autoBuyMain Enabled:$returnBool | Live:$LiveCoinPrice | AutoBuy:".$autoBuyPrice[$finalNo][1]." | CoinID:$coinID";
  echoAndLog($name, "autoBuyMain Enabled:$returnBool | Live:$LiveCoinPrice | AutoBuy:$newAutoBuyPrice | CoinID:$coinID",3,$echoEnabled,"","");
  return $returnBool;
}

function autoSellMain($LiveCoinPrice, $autoBuyPrice, $autoBuyCoinEnabled, $coinID,$echoEnabled,$name){
  $returnBool = False;
  $coinPriceAryCount = count($autoBuyPrice);
  for ($i = 0; $i<$coinPriceAryCount; $i++){
    if ($coinID == $autoBuyPrice[$i][0]){
      //echo "<BR> autoSell($LiveCoinPrice,".$autoBuyPrice[$i][1].",$autoBuyCoinEnabled); ";
      $autoPrice = $autoBuyPrice[$i][1];
      $returnBool = autoSell($LiveCoinPrice,$autoPrice,$autoBuyCoinEnabled);

      //echo $returnBool;
    }
  }
  echoAndLog($name, " autoSellMain Enabled:$returnBool | LivePrice:$LiveCoinPrice | AutoPrice:$autoPrice | CoinID:$coinID ",3,$echoEnabled,"","");
  return $returnBool;
}

function autoSell($LiveCoinPrice, $autoBuyPriceTop, $autoBuyCoinEnabled){
  //Echo "<BR> autoSell2($LiveCoinPrice, $autoBuyPriceTop, $autoBuyCoinEnabled)";
  if ($autoBuyCoinEnabled == 0){
      //print_r("True $autoBuyCoinEnabled");
      return True;
      exit;
  }elseif ($LiveCoinPrice >= $autoBuyPriceTop && $autoBuyCoinEnabled == 1){
      //print_r("True $LiveCoinPrice >= $autoBuyPriceTop && $autoBuyCoinEnabled ");
      $GLOBALS['allDisabled'] = true;
      return True;
      exit;
  }else {
    $GLOBALS['allDisabled'] = true;
    //print_r($buyTop >= $score);
    //print_r("False ");
    return False;
  }
}

function autoBuy($LiveCoinPrice, $autoBuyPriceTop, $autoBuyPriceBtm, $autoBuyCoinEnabled){
  if ($autoBuyCoinEnabled == 0){
      //print_r("True");
      return True;
      exit;
  }elseif ($LiveCoinPrice <= $autoBuyPriceTop && $LiveCoinPrice >= $autoBuyPriceBtm && $autoBuyCoinEnabled == 1){
      //print_r("True");
      $GLOBALS['allDisabled'] = true;
      return True;
      exit;
  }else {
    $GLOBALS['allDisabled'] = true;
    //print_r($buyTop >= $score);
    //print_r("False ");
    return False;
  }
}

function buyAmountOverride($buyAmountOverrideEnabled){
  if ($buyAmountOverrideEnabled == 0){
      //print_r("True");
      return True;
      exit;
  }elseif ($buyAmountOverrideEnabled == 1){
      //print_r("True");
      $GLOBALS['allDisabled'] = true;
      return True;
      exit;
  }else {
    $GLOBALS['allDisabled'] = true;
    //print_r($buyTop >= $score);
    //print_r("False ".$score);
    return False;
  }
}

function sellCoins($apikey, $apisecret, $coin, $email, $userID, $score, $date,$baseCurrency, $sendEmail, $sellCoin, $ruleID,$userName, $orderNo,$amount,$cost,$transactionID,$coinID,$CoinSellOffsetEnabled,$CoinSellOffsetPct,$LiveCoinPrice, $type){
  $apiVersion = 3;
  $retSell = False;
  echo "<BR>$apikey, $apisecret, $coin, $email, $userID, $score, $date,$baseCurrency, $sendEmail, $sellCoin, $ruleID,$userName, $orderNo,$amount,$cost";
  $subject = "Coin Alert: ".$coin."_".$ruleID;
  $from = 'Coin Alert <alerts@investment-tracker.net>';
    //sell Coin
  $bitPrice = $LiveCoinPrice;
  if ($CoinSellOffsetEnabled == 1){
    $bitPrice = number_format((float)$bitPrice + (($bitPrice/100)*$CoinSellOffsetPct), 8, '.', '');
    $bitPrice = round($bitPrice,8, PHP_ROUND_HALF_UP);
  }
  date_default_timezone_set('Asia/Dubai');
  $date = date("Y-m-d H:i:s", time());
  Echo "<br>Here1 $sellCoin";
  if ($sellCoin){
    $subject = "Coin Sale: ".$coin."_".$ruleID;
    $from = 'Coin Sale <sale@investment-tracker.net>';
    echo "<BR>bittrexsell($apikey, $apisecret, $coin ,$amount, $bitPrice, $baseCurrency);";
    $obj = bittrexsell($apikey, $apisecret, $coin ,round($amount,10), number_format($bitPrice,8), $baseCurrency, $apiVersion, FALSE);
    //Echo "<br>Here2";
    //$bittrexRef = $obj['result'][0]['uuid'];
    if ($apiVersion == 1){$bittrexRef = $obj["result"]["uuid"]; $status = $obj["success"]; }
    else{
      $bittrexRef = $obj["id"];
      Echo "<BR> API V3 Bittrex Ref: $bittrexRef | Direction : ".$obj["direction"];
      if ($bittrexRef <> ""){$status = 1;}else{$status = 0;}
    }

    //echo "<BR>BITTREXREF: $bittrexRef";

    //echo "<br> STATUS: $status";
    if ($status == 1 AND $bittrexRef <> ""){
      //$totalBTC = getTotalLimit($userID);
      $retSell = True;
      Echo "<br>Here3";
      echo "<br>updateSQL($baseCurrency,$transactionID,$bittrexRef)";
      //updateSQL($baseCurrency,$transactionID,$bittrexRef);
      echo "<BR>writeBittrexAction($coinID,$transactionID,$userID,$bittrexRef, $date, $status,$bitPrice,$type);";
      //writeBittrexAction($coinID,$transactionID,$userID,$type,$bittrexRef,$date,$status,$sellPrice){
      //writeBittrexAction($coinID,$transactionID,$userID,"Sell",$bittrexRef, $date, $status,$bitPrice);
      bittrexSellAdd($coinID, $transactionID, $userID, $type, $bittrexRef, $status, $bitPrice, $ruleID);
      logToSQL("Bittrex", "Sell Coin Add $bitPrice ", $userID,1);
      logAction("BITTREX SELL COIN| $coin | $bitPrice | $amount | $baseCurrency", 'BuySellFlow', 1);
    }
    logAction("SellCoins:  ".json_encode($obj), 'BuySell', 0);
    logToSQL("Bittrex", "Sell Coin Data: ".json_encode($obj)."|".$coin."|".$amount."|".$transactionID, $userID,1);
  }
  if ($sendEmail==1 &&  $sellCoin ==0){
  //if ($sendEmail){
    echo "$email, $coin, $amount, $bitPrice, $orderNo, $score,$profitPct,$bitPrice-$cost,$subject,$userName";
    $profitPct = ($bitPrice-$cost)/$cost*100;
    $buyPrice = ($cost*$amount);
    $bitPrice = ($bitPrice*$amount);
    $fee = (($bitPrice)/100)*0.25;
    $profit = $bitPrice - $buyPrice - $fee;
    sendSellEmail($email, $coin, $amount, $bitPrice, $orderNo.$ruleID, $score,$profitPct,$profit,$subject,$userName,$from,$baseCurrency);
  }
  return $retSell;
}

function updateCoinAllocation($userID, $mode, $baseCurrency, $buyAmount){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call updateCoinAllocationAfterPurchase($userID, $mode, '$baseCurrency', $buyAmount);";

  print_r("<BR>".$sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function getNewCoinAllocation($baseCurrency,$userID,$lowBuyMode,$overrideFlag,$savingOverride){
  if ($baseCurrency == 'USDT'){
    //call USDT SQL
    //echo "<BR> BaseCurrency1: $baseCurrency";
    //if ($overrideFlag == 1){ $newFlag = 1;} else{$newFlag = 0;}
    echo "<BR> getNewUSDTAlloc($userID,$overrideFlag);";
    $newCoinAlloc = getNewUSDTAlloc($userID,$lowBuyMode,$overrideFlag,$savingOverride);
  }elseif ($baseCurrency == 'BTC'){
    //echo "<BR> BaseCurrency2: $baseCurrency";
    $newCoinAlloc = getNewBTCAlloc($userID,$lowBuyMode,$overrideFlag,$savingOverride);
  }elseif ($baseCurrency == 'ETH'){
    //echo "<BR> BaseCurrency3: $baseCurrency";
    $newCoinAlloc = getNewETHAlloc($userID,$lowBuyMode,$overrideFlag,$savingOverride);
  }
  //echo "<BR> ".$newCoinAlloc[0][0]." | ".$newCoinAlloc[0][1];
  echo "<BR> Return(".$newCoinAlloc[0][0]."-) $baseCurrency;";
  return $newCoinAlloc[0][0];
}

function getNewUSDTAlloc($userID,$lowBuyMode,$overrideFlag,$savingOverride){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    //echo "<BR> Flag1: $lowFlag";
    $sql = "SELECT getNewCoinAllocation($userID,$lowBuyMode,'USDT',$overrideFlag,$savingOverride) as AllocTotal";

  echo "<BR> $sql";
  //LogToSQL("SQLTest",$sql,3,1);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['AllocTotal']);}
  $conn->close();
  return $tempAry;
}

function getOpenBaseCurrency($symbol){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    //echo "<BR> Flag1: $lowFlag";
    $sql = "Select `Cn`.`BaseCurrency` as BaseCurrency
              From `Coin` `Cn`
              join `Transaction` `Tr` on `Tr`.`CoinID` = `Cn`.`ID`
              WHERE `Cn`.`Symbol` = '$symbol' and `Tr`.`Status` in ('Open','Pending','Saving')
              Limit 1";

  echo "<BR> $sql";
  //LogToSQL("SQLTest",$sql,3,1);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['BaseCurrency']);}
  $conn->close();
  return $tempAry;
}

function getNewBTCAlloc($userID,$lowBuyMode,$overrideFlag,$savingOverride){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}

    //echo "<BR> Flag1: $lowFlag";
    $sql = "SELECT getNewCoinAllocation($userID,$lowBuyMode,'BTC',$overrideFlag,$savingOverride)*getBTCPrice(84) as AllocTotal";

  echo "<BR> $sql";
  //LogToSQL("SQLTest",$sql,3,1);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['AllocTotal']);}
  $conn->close();
  return $tempAry;
}

function getNewETHAlloc($userID,$lowBuyMode,$overrideFlag,$savingOverride){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}

    //echo "<BR> Flag1: $lowFlag";
    $sql = "SELECT getNewCoinAllocation($userID,$lowBuyMode,'ETH',$overrideFlag,$savingOverride)*getBTCPrice(85) as AllocTotal";

  //echo "<BR> $sql";
  //LogToSQL("SQLTest",$sql,3,1);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['AllocTotal']);}
  $conn->close();
  return $tempAry;
}

function bittrexsell($apikey, $apisecret, $symbol, $quant, $rate, $baseCurrency, $versionNum, $useAwards){
    $nonce=time();
    if ($versionNum == 1){
        $uri='https://bittrex.com/api/v1.1/market/selllimit?apikey='.$apikey.'&market='.$baseCurrency.'-'.$symbol.'&quantity='.$quant.'&rate='.$rate.'&nonce='.$nonce;
        echo "<BR>$uri<BR>";
        $sign=hash_hmac('sha512',$uri,$apisecret);
        $ch = curl_init($uri);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    }elseif ($versionNum == 3){
      $timestamp = time()*1000;
      $url = "https://api.bittrex.com/v3/orders";
      $method = "POST";

      $content = '{
        "marketSymbol": "'.$symbol.'-'.$baseCurrency.'",
        "direction": "SELL",
        "type": "LIMIT",
        "quantity": "'.$quant.'",
        "limit": "'.$rate.'",
        "timeInForce": "GOOD_TIL_CANCELLED",
        "useAwards": "'.$useAwards.'"
      }';
      echo "<BR>".$content;
      $subaccountId = "";
      $contentHash = hash('sha512', $content);
      $preSign = $timestamp . $url . $method . $contentHash . $subaccountId;
      $signature = hash_hmac('sha512', $preSign, $apisecret);

      $headers = array(
      "Accept: application/json",
      "Content-Type: application/json",
      "Api-Key: ".$apikey."",
      "Api-Signature: ".$signature."",
      "Api-Timestamp: ".$timestamp."",
      "Api-Content-Hash: ".$contentHash.""
      );

      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($ch, CURLOPT_HEADER, FALSE);
      curl_setopt($ch, CURLOPT_POST, TRUE);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
      //$execResult = curl_exec($ch);

    }

    $execResult = curl_exec($ch);
    curl_close($ch);
    newLogToSQL("bittrexsell", "$execResult", 3, 1,"EXEC Result","");
    $obj = json_decode($execResult, true);
    return $obj;
}

function updateSQL($baseCurrency, $transactionID, $BittrexID){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    if ($baseCurrency == "BTC"){
        $sql = "UPDATE `Transaction` SET `Status` = 'Pending', `BittrexID` =  $BittrexID WHERE `ID` = $transactionID";
    }elseif ($baseCurrency == "ETH") {
        $sql = "UPDATE `Transaction` SET `Status` = 'Pending', `BittrexID` =  $BittrexID WHERE `ID` = $transactionID";
    }
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {echo "Error: " . $sql . "<br>" . $conn->error;}
    $conn->close();
    newLogToSQL("updateSQL",$sql,3,sQLUpdateLog,"SQL","TransactionID:$transactionID");
    LogAction("updateSQL:".$sql, 'SQL_UPDATE', 0);
}

function updateTransStatus($transactionID,$status){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `Transaction` SET `Status` = '$status' WHERE `ID` = $transactionID";

    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {echo "Error: " . $sql . "<br>" . $conn->error;}
    $conn->close();
    newLogToSQL("updateTransStatus",$sql,3,sQLUpdateLog,"SQL","TransactionID:$transactionID");
    LogAction("updateTransStatus:".$sql, 'SQL_UPDATE', 0);
}

function clearBittrexRef($transactionID){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "UPDATE `Transaction` SET `BittrexRef` =  '' WHERE `ID` = $transactionID";

    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {echo "Error: " . $sql . "<br>" . $conn->error;}
    $conn->close();
    newLogToSQL("clearBittrexRef",$sql,3,sQLUpdateLog,"SQL","TransactionID:$transactionID");
    LogAction("clearBittrexRef:".$sql, 'SQL_UPDATE', 0);
}



function sendSellEmail($to, $symbol, $amount, $cost, $orderNo, $score, $profitPct, $profit, $subject, $user, $from, $baseCurrency){
    $body = "Dear ".$user.", <BR/>";
    $body .= "Congratulations you have sold the following Coin: "."<BR/>";
    $body .= "Coin: ".$symbol." Amount: ".$amount." Price: ".$cost."<BR/>";
    $body .= "Order Number: ".$orderNo."<BR/>";
    //$body .= "Score: ".$score."<BR/>";
    $body .= "Profit %: ".$profitPct."<BR/>";
    $body .= "Profit ".$baseCurrency.": ".$profit."<BR/>";
    $body .= "Kind Regards\nCryptoBot.";
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    //$headers .= 'From: Alerts <Alerts@Investment-Tracker.net>' . "\r\n";
    $headers .= "From:".$from."\r\n";
    $headers .= "To:".$to."\r\n";
    mail($to, $subject, wordwrap($body,70),$headers);
}

function sendSellEmailDebug($to, $symbol, $amount, $cost, $orderNo, $score, $profitPct, $profit, $subject, $user, $from, $debug){
    $body = "Dear ".$user.", <BR/>";
    $body .= "Congratulations you have sold the following Coin: "."<BR/>";
    $body .= "Coin: ".$symbol." Amount: ".$amount." Price: ".$cost."<BR/>";
    $body .= "Order Number: ".$orderNo."<BR/>";
    $body .= "Score: ".$score."<BR/>";
    $body .= "Profit %: ".$profitPct."<BR/>";
    $body .= "Profit BTC: ".$profit."<BR/>";
    $body .= "PDebug: ".$debug."<BR/>";
    $body .= "Kind Regards\nCryptoBot.";
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    //$headers .= 'From: Alerts <Alerts@Investment-Tracker.net>' . "\r\n";
    $headers .= "From:".$from."\r\n";
    $headers .= "To:".$to."\r\n";
    mail($to, $subject, wordwrap($body,70),$headers);
}

function bittrexCancel($apikey, $apisecret, $uuid, $versionNum){
    $nonce=time();
    if ($versionNum == 1){
        $uri='https://bittrex.com/api/v1.1/market/cancel?apikey='.$apikey.'&uuid='.$uuid.'&nonce='.$nonce;
        $sign=hash_hmac('sha512',$uri,$apisecret);
        $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $execResult = curl_exec($ch);
        $obj = json_decode($execResult, true);
        $balance = $obj["success"];
    }elseif ($versionNum == 3){
      $timestamp = time()*1000;
      $url = "https://api.bittrex.com/v3/orders/{".$uuid."}";
      echo "<BR>".$url;
      $method = "DELETE";
      echo "<BR>".$method;
      $content = '';
      $subaccountId = "";
      $contentHash = hash('sha512', $content);
      $preSign = $timestamp . $url . $method . $contentHash . $subaccountId;
      $signature = hash_hmac('sha512', $preSign, $apisecret);

      //$headers = array(
      //"Accept: application/json",
      //"Content-Type: application/json",
      //"Api-Key: ".$apikey."",
      //"Api-Signature: ".$signature."",
      //"Api-Timestamp: ".$timestamp."",
      //"Api-Content-Hash: ".$contentHash.""
      //);

      $curl = curl_init();

      curl_setopt_array($curl, array(
      CURLOPT_URL => "".$url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "DELETE",
      CURLOPT_HTTPHEADER => array(
      "Accept: application/json",
      "Api-Key: ".$apikey."",
      "Api-Signature: ".$signature."",
      "Api-Timestamp: ".$timestamp."",
      "Api-Content-Hash: ".$contentHash.""
      ),
      ));

      $execResult = curl_exec($curl);

      curl_close($curl);
      //echo $response;
      $balance = json_decode($execResult, true);
    }
    return $balance;
}

function cancelBittrexSQL($id){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "DELETE FROM `BittrexAction` WHERE `BittrexRef` = '$id'";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  logAction("cancelBittrexSQL: ".$sql, 'BuySell', 0);
}

function changeTransStatus($transactionID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `Transaction` SET `Status`= 'Open' WHERE `ID` = $transactionID";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("changeTransStatus",$sql,3,sQLUpdateLog,"SQL","TransactionID:$transactionID");
  logAction("changeTransStatus: ".$sql, 'SQL_UPDATE', 0);
}

function bittrexBuyAdd($coinID, $userID, $type, $bittrexRef, $status, $ruleID, $cost, $amount, $orderNo,$timeToCancelBuyMins,$sellRuleFixed){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call AddBittrexBuy($coinID, $userID, '$type', '$bittrexRef', '$status', $ruleID, $cost, $amount, '$orderNo',$timeToCancelBuyMins,$sellRuleFixed);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("bittrexBuyAdd: ".$sql, 'SQL_CALL', 0);
  newLogToSQL("bittrexBuyAdd","$sql",3,1,"SQL CALL","CoinID:$coinID");
}

function bittrexAddNoOfPurchases($bittrexRef, $noOfPurchases){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `Transaction` SET `NoOfPurchases` = $noOfPurchases WHERE `BittrexRef` = '$bittrexRef'";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("bittrexAddNoOfPurchases",$sql,3,0,"SQL","BittrexRef:$bittrexRef");
  logAction("bittrexAddNoOfPurchases: ".$sql, 'SQL_UPDATE', 0);
}

function bittrexSellAdd($coinID, $transactionID, $userID, $type, $bittrexRef, $status, $bitPrice, $ruleID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call NewAddBittrexSell($coinID, $transactionID, $userID, '$type', '$bittrexRef', '$status', $bitPrice, $ruleID);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("bittrexSellAdd: ".$sql, 'SQL_CALL', 0);
  newLogToSQL("bittrexSellAdd","$sql",3,1,"SQL CALL","CoinID:$coinID TransactionID:$transactionID");
}

function bittrexSellCancel($bittrexRef, $transactionID, $errorCode){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call CancelBittrexSell('$bittrexRef', $transactionID);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("bittrexSellCancel: ".$sql, 'SQL_CALL', 0);
  newLogToSQL("bittrexSellCancel","$sql :$errorCode",3,1,"SQL CALL","BittrexRef:$bittrexRef TransactionID:$transactionID");
}

function bittrexBuyCancel($bittrexRef, $transactionID, $errorCode){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call CancelBittrexBuy('$bittrexRef',$transactionID);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("bittrexBuyCancel: ".$sql, 'SQL_CALL', 0);
  newLogToSQL("bittrexBuyCancel","$sql :$errorCode",3,1,"SQL CALL","BittrexRef:$bittrexRef TransactionID:$transactionID");
}

function bittrexBuyComplete($bittrexRef,$transactionID, $finalPrice, $type){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call CompleteBittrexBuy('$bittrexRef', $transactionID,$finalPrice, '$type');";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("bittrexBuyComplete: ".$sql, 'SQL_CALL', 0);
  newLogToSQL("bittrexBuyComplete","$sql",3,1,"SQL CALL","BittrexRef:$bittrexRef TransactionID:$transactionID");
}

function bittrexSellComplete($bittrexRef,$transactionID, $finalPrice){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call CompleteBittrexSell('$bittrexRef', $transactionID, $finalPrice);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("bittrexSellComplete: ".$sql, 'SQL_CALL', 0);
  newLogToSQL("bittrexSellComplete","$sql",3,1,"SQL CALL","BittrexRef:$bittrexRef TransactionID:$transactionID");
}

function bittrexBuyCompleteUpdateAmount($transactionID, $amount){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call CompleteBittrexBuyUpdateAmount($transactionID, $amount);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("bittrexBuyCompleteUpdateAmount: ".$sql, 'SQL_CALL', 0);
  newLogToSQL("bittrexBuyCompleteUpdateAmount","$sql",3,sQLUpdateLog,"SQL CALL","TransactionID:$transactionID");
}

function bittrexSellCompleteUpdateAmount($transactionID, $amount){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call CompleteBittrexSellUpdateAmount($transactionID, $amount);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("bittrexSellCompleteUpdateAmount: ".$sql, 'SQL_CALL', 0);
  newLogToSQL("bittrexSellCompleteUpdateAmount","$sql",3,sQLUpdateLog,"SQL CALL","TransactionID:$transactionID");
}

function getTotalBTC(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT sum(`CoinPrice` * `Amount`) as `AmountOpen`,`UserID`,`BaseCurrency` FROM `View15_OpenTransactions`
            where `StatusTr` in ('Open','Pending')
            group by `BaseCurrency`, `UserID`";
  //LogToSQL("SQLTest",$sql,3,1);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['AmountOpen'],$row['UserID'],$row['BaseCurrency']);}
  $conn->close();
  return $tempAry;
}

function getUserTotalBTC($totalBTCSpent,$userID,$baseCurrency){
  $totalBTCSpentSize = count($totalBTCSpent);
  for ($j=0; $j < $totalBTCSpentSize; $j++){
    if ($totalBTCSpent[$j][1] == $userID and $totalBTCSpent[$j][1] == $baseCurrency){
      return $totalBTCSpent[$j][0];
    }
  }
  return 0;
}

function getDailyBTC(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT sum(`CoinPrice`*`Amount`) as `AmountOpen`,`UserID`,`BaseCurrency` FROM `View15_OpenTransactions` where timeStampDiff(Hour,`OrderDate`,now()) <  24
          Group by `BaseCurrency`,`UserID`";
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['AmountOpen'],$row['UserID'],$row['BaseCurrency']);}
  $conn->close();
  return $tempAry;
}

function copyCoinHistory($coin){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call copyCoinHistory($coin);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("copyCoinHistory","$sql",3,sQLUpdateLog,"SQL CALL","Coin:$coin");
}

function copyCoinBuyHistoryStats($coinID,$bitPrice,$baseCurrency,$coinPriceHistoryTime){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call writeCoinBuyHistoryStats($coinID,$bitPrice,'$baseCurrency','$coinPriceHistoryTime');";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("copyCoinBuyHistoryStats","$sql",3,0,"SQL CALL","CoinID:$coinID");
}

function copyBuyHistory($coinID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call copyBuyHistory($coinID);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("copyBuyHistory","$sql",3,sQLUpdateLog,"SQL CALL","CoinID:$coinID");
}

function getAveragePrice($symbol){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `AvgCoinPrice` FROM `AvgCoinPriceView` WHERE `Symbol` = '$symbol'";
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['AvgCoinPrice']);
  }
  $conn->close();
  return $tempAry;
}

function coinPriceHistory($coinID,$price,$baseCurrency,$date,$hr1Pct,$hr24Pct,$d7Pct){
  $conn = getHistorySQL(rand(1,4));
  Echo "<BR> UpdateHistoryPrice : call UpdateHistoryPrice($coinID,$price,'$baseCurrency','$date');";
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call NewUpdatePriceHistory($coinID,$price,'$baseCurrency','$date',$hr1Pct,$hr24Pct,$d7Pct);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("coinPriceHistory","$sql",3,0,"SQL CALL","CoinID:$coinID");
}

function coinPriceHistorySpreadBet($coinID,$price,$baseCurrency,$date,$hr1Pct,$hr24Pct,$d7Pct){
  $conn = getHistorySQL(rand(1,4));
  Echo "<BR> UpdateHistoryPrice : call UpdateHistoryPrice($coinID,$price,'$baseCurrency','$date');";
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call UpdateSpreadBetPriceHistory($coinID,$price,'$baseCurrency','$date',$hr1Pct,$hr24Pct,$d7Pct);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("coinPriceHistorySpreadBet","$sql",3,sQLUpdateLog,"SQL CALL","CoinID:$coinID");
}

function get1HrChange($coinID){
  $tempAry = [];
  $conn = getHistorySQL(rand(1,4));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `Price` FROM `OneHourPrice` WHERE `CoinID` = $coinID";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['Price']);
  }
  $conn->close();
  return $tempAry;
}

function update1HrPriceChange($price,$coinID){
  $conn = getSQLConn(rand(1,3));
  Echo "<BR> Update1HrPriceChange : call Update1HrPriceChange($price,$coinID);";
  $newPrice = Round($price,8);
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  //$sql = "Update `CoinPctChange` SET `Live1HrChange` = $newPrice where `CoinID` = $coinID;";
  $sql = "call Update1HrPriceChangeAndHighLow($newPrice,$coinID);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  if ($coinID == 130){
      newLogToSQL("update1HrPriceChange",$sql,3,0,"SQL","CoinID:$coinID");
  }
}

function get24HrChange($coinID){
  $tempAry = [];
  $conn = getHistorySQL(rand(1,4));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `Price` FROM `TwentyFourHourPrice` WHERE `CoinID` = $coinID";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['Price']);
  }
  $conn->close();
  return $tempAry;
}

function get7DayChange($coinID){
  $tempAry = [];
  $conn = getHistorySQL(rand(1,4));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `Price` FROM `SevenDayPrice` WHERE `CoinID` = $coinID";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['Price']);
  }
  $conn->close();
  return $tempAry;
}

function update7DPriceChange($sevenDayPrice,$coinID){
  $conn = getSQLConn(rand(1,3));
  echo "<BR> Update7DPriceChange : call Update7DPriceChange($sevenDayPrice,$coinID);";
  $newPrice = Round($sevenDayPrice,8);
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  //$sql = "Update `CoinPctChange` SET `Live7DChange` = $newPrice where `CoinID` = $coinID;";
  $sql = "Call Update7DPriceChangeAndHighLow($newPrice,$coinID);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("update7DPriceChange",$sql,3,0,"SQL","CoinID:$coinID");
}
function updatePctChange($coinID,$sevenDayPrice,$hr24Price,$hr1Price){
  $conn = getSQLConn(rand(1,3));
  echo "<BR> Update7DPriceChange : call Update7DPriceChange($sevenDayPrice,$coinID);";
  $newPrice = Round($sevenDayPrice,8);
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call UpdateCoinPctChange($coinID,$sevenDayPrice,$hr24Price,$hr1Price);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("updatePctChange","$sql",3,sQLUpdateLog,"SQL CALL","CoinID:$coinID");
}


function update24HrPriceChange($price,$coinID){
  $conn = getSQLConn(rand(1,3));
  echo "<BR> Update24HrPriceChange : call Update24HrPriceChange($price,$coinID);";
  $newPrice = Round($price,8);
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  //$sql = "Update `CoinPctChange` SET `Live24HrChange` = $newPrice where `CoinID` = $coinID;";
  $sql = "Call Update24HrPriceChangeAndHighLow($newPrice,$coinID);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("update24HrPriceChange",$sql,3,0,"SQL","CoinID:$coinID");
}

function update8HrPriceChange($price,$coinID){
  $conn = getSQLConn(rand(1,3));
  Echo "<BR> Update8HrPriceChange : call Update8HrPriceChange($price,$coinID);";
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call Update8HrPriceChange($price,$coinID);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("update8HrPriceChange","$sql",3,sQLUpdateLog,"SQL CALL","CoinID:$coinID");
}

function bittrexUpdateBuyQty($transactionID, $quantity){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call CompleteBittrexBuyUpdateAmount($transactionID,$quantity);";
  print_r("<br>".$sql);
  if ($conn->query($sql) === TRUE) {echo "New record created successfully";
  } else {echo "Error: " . $sql . "<br>" . $conn->error;}
  $conn->close();
  logAction("bittrexUpdateBuyQty: ".$sql, 'BuySell', 0);
  newLogToSQL("bittrexUpdateBuyQty","$sql",3,sQLUpdateLog,"SQL CALL","TransactionID:$transactionID");
}

function bittrexUpdateSellQty($transactionID, $quantity){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call CompleteBittrexSellUpdateAmount($transactionID,$quantity);";
  print_r("<br>".$sql);
  if ($conn->query($sql) === TRUE) {echo "New record created successfully";
  } else {echo "Error: " . $sql . "<br>" . $conn->error;}
  $conn->close();
  logAction("bittrexUpdateSellQty: ".$sql, 'SQL_CALL', 1);
  newLogToSQL("bittrexUpdateSellQty","$sql",3,sQLUpdateLog,"SQL CALL","TransactionID:$transactionID");
}

function bittrexCopyTransNewAmount($transactionID, $oQuantity, $nQuantity, $orderNo){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call newCopyTransNewAmount($nQuantity,$oQuantity,$transactionID,'$orderNo');";
  print_r("<br>".$sql);
  if ($conn->query($sql) === TRUE) {echo "New record created successfully";
  } else {
    echo "Error: " . $sql . "<br>" . $conn->error;
    sqltoSteven("Error: " . $sql . "<br>" . $conn->error);
  }
  $conn->close();
  logAction("bittrexCopyTransNewAmount: ".$sql, 'SQL_CALL', 0);
  newLogToSQL("bittrexCopyTransNewAmount","$sql",3,sQLUpdateLog,"SQL CALL","TransactionID:$transactionID");
}

function sendtoSteven($transactionID,$newOrderQtyRemaining,$newOrderNo, $errorText){
  $body = "DEBUG FILE <BR/>";
  $body .= "Congratulations you have sold the following Coin: "."<BR/>";
  $body .= "Transaction: ".$transactionID."<BR/>";
  $body .= "Order Number: ".$newOrderNo."<BR/>";
  $body .= "Order Qty Remaining: ".$newOrderQtyRemaining."<BR/>";
  $body .= $errorText."<BR/>";
  $body .= "Kind Regards\nCryptoBot.";
  $headers = 'MIME-Version: 1.0' . "\r\n";
  $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
  $headers .= 'From: Coin Sale <sale@investment-tracker.net>' . "\r\n";
  //$headers .= "From:Coin Sale <sale@investment-tracker.net>\r\n";
  $headers .= 'To: stevenj1979@gmail.com'."\r\n";
  $subject = "COIN SALE DEBUG";
  mail($to, $subject, wordwrap($body,70),$headers);
}

function sqltoSteven($errorSQL){
  $body = "DEBUG FILE <BR/>";
  $body .= "Congratulations you have sold the following Coin: "."<BR/>";
  $body .= "SQL ERROR: ".$errorSQL."<BR/>";
  $body .= "Kind Regards\nCryptoBot.";
  $headers = 'MIME-Version: 1.0' . "\r\n";
  $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
  $headers .= 'From: Coin Sale <sale@investment-tracker.net>' . "\r\n";
  //$headers .= "From:Coin Sale <sale@investment-tracker.net>\r\n";
  $headers .= 'To: stevenj1979@gmail.com'."\r\n";
  $subject = "COIN SALE SQL DEBUG";
  mail($to, $subject, wordwrap($body,70),$headers);
}

function logAction($log, $logFile, $enabled){
  if ($enabled == 1){
    file_put_contents('./log/log_'.$logFile.'_'.date("j.n.Y").'.log', date("F j, Y, g:i a").':'.$log.PHP_EOL, FILE_APPEND);
  }
}

function logToSQL($subject, $comments, $UserID, $enabled){
  if ($enabled == 1){
    $comments = str_replace("'","/",$comments);
    $conn = getSQLConn(rand(1,3));
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $sql = "call LogToSQL($UserID,'$subject','$comments',100)";
    print_r("<br>".$sql);
    if ($conn->query($sql) === TRUE) {echo "New record created successfully";
    } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
      sqltoSteven("Error: " . $sql . "<br>" . $conn->error);
    }
    $conn->close();
  }
}

function newLogToSQL($subject, $comments, $UserID, $enabled, $subTitle, $ref){
  if ($enabled == 1){
    $comments = str_replace("'","/",$comments);
    $conn = getSQLConn(rand(1,3));
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $sql = "call newLogToSQL($UserID,'$subject','$comments',100,'$subTitle','$ref')";
    print_r("<br>".$sql);
    if ($conn->query($sql) === TRUE) {echo "New record created successfully";
    } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
      sqltoSteven("Error: " . $sql . "<br>" . $conn->error);
    }
    $conn->close();
  }
}

function echoAndLog($subject, $comments, $UserID, $enabled, $subTitle, $ref){
  $sql = "call newLogToSQL($UserID,'$subject','$comments',100,'$subTitle','$ref')";
  $sql = str_replace("'","/",$comments);
  if ($enabled > 0){
    echo "<BR> $subject | $subTitle | $comments";
  }

  if ($enabled > 1){

    $conn = getSQLConn(rand(1,3));
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    //print_r("<br>".$sql);
    if ($conn->query($sql) === TRUE) {echo "";
    } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
      //sqltoSteven("Error: " . $sql . "<br>" . $conn->error);
    }
    $conn->close();
  }

  if ($enabled > 2){
    file_put_contents('./log/log_'.$subject.'_'.date("j.n.Y").'.log', date("F j, Y, g:i a").':'.$comments."|".$subTitle."|".$UserID.PHP_EOL, FILE_APPEND);
  }
}

function displayHeader($n){
  //$_SESSION['sellCoinsQueue'] = count(getTrackingSellCoins($_SESSION['ID']));
  //$_SESSION['bittrexQueue'] = count(getBittrexRequests($_SESSION['ID']));
  $userDisabledUntil = getUserDisabled($_SESSION['ID']);
  $_SESSION['DisableUntil'] = $userDisabledUntil[0][0];

  //$_SESSION['headerTimeout'] = date("Y-m-d H:i:s", strtotime("+10 minutes"));
  //echo $_SESSION['headerTimeout'];
  if ($_SESSION['DisableUntil'] <= date("Y-m-d H:i:s", time())){$_SESSION['isDisabled'] = False;} else{$_SESSION['isDisabled'] = True;}
  if (!isset($_SESSION['headerTimeout'])){
    addWebUsage($_SESSION['ID']);
    $_SESSION['webUsage'] = getWebUsage($_SESSION['ID']);
    $_SESSION['headerTimeout'] = date("Y-m-d H:i:s", strtotime("+10 minutes"));
  }elseif( strtotime($_SESSION['headerTimeout']) < strtotime('now')){
    addWebUsage($_SESSION['ID']);
    $_SESSION['webUsage'] = getWebUsage($_SESSION['ID']);
    $_SESSION['headerTimeout'] = date("Y-m-d H:i:s", strtotime("+10 minutes"));
    //echo "Refreshed!";
  }
  $webUsageAry = $_SESSION['webUsage'];
  $buyTracking = $webUsageAry[0][0];$buyBack = $webUsageAry[0][1]; $sellCoin = $webUsageAry[0][2];  $sellTracking = $webUsageAry[0][3]; $sellSaving = $webUsageAry[0][4]; $bittrexAction = $webUsageAry[0][5];
  $buyTotal = $buyTracking + $buyBack; $sellTotal = $sellCoin + $sellTracking + $sellSaving;
  $headers = array("Dashboard.php", "Transactions.php", "Stats.php","BuyCoins.php","SellCoins.php","Profit.php","bittrexOrders.php","Settings.php", "CoinAlerts.php","console.php","CoinMode.php","AdminSettings.php");
  $ref = array("Dashboard", "Transactions", "Stats","Buy Coins (".$buyTotal.")","Sell Coins (".$sellTotal.")","Profit","Bittrex Orders (".$bittrexAction.")","Settings","Coin Alerts","Console","CoinMode","Admin Settings");
  $headerLen = count($headers);
  $imgpath = '/Investment-Tracker/Cryptobot/1/Images/CBLogoSmall.png';
  ?><div class="header">
    <table>
      <tr><TH><img src='<?php echo $imgpath; ?>' width="40"> </TH>
      <TH>Logged in as: <i class="glyphicon glyphicon-user"></i>  <?php echo $_SESSION['username']; ?><?php if ($_SESSION['isDisabled']){echo " Disabled Until : ".$_SESSION['DisableUntil'];} ?></th></tr></Table><br>
     </div>
     <div class="topnav"> <?php

     echo "<ul>";
      for($x = 0; $x < $headerLen; $x++) {
        $sellQueue = "";$active = "";
        $h1 = $headers[$x];
        $r1 = $ref[$x];
        if ($ref[$x] == "Bittrex Orders" and $_SESSION['bittrexQueue'] > 0) {$sellQueue = "(".$_SESSION['bittrexQueue'].")";}
        if ($ref[$x] == "Sell Coins" and $_SESSION['sellCoinsQueue'] > 0){$sellQueue = "(".$_SESSION['sellCoinsQueue'].")"; }
        if ($n == $x) { $active = " class='active'";}
        if ($_SESSION['AccountType']==1 && $x == $headerLen){Echo "<li><a href='$h1'$active>$r1 $sellQueue</a></li>";}
        else{Echo "<li><a href='$h1'$active>$r1 $sellQueue</a></li>";}
        //$active = '';
      }
      echo "<ul>";
      //if ($n > $headerLen ){ $active = " class='active'"; }
      //if ($_SESSION['AccountType']==1){echo "<a href='AdminSettings.php'$active>Admin Settings</a>";}?>
    </div>
    <div class="row">
      <div class="settingCol1"><?php
}

function displaySubHeader($page){
  $webUsageAry = $_SESSION['webUsage'];
  $buyTracking = $webUsageAry[0][0];$buyBack = $webUsageAry[0][1]; $sellCoin = $webUsageAry[0][2];  $sellTracking = $webUsageAry[0][3]; $sellSaving = $webUsageAry[0][4]; $bittrexAction = $webUsageAry[0][5];
  if ($page == 'BuyCoin'){
    echo "<h3><a href='BuyCoins.php'>Buy Coins</a> &nbsp > &nbsp <a href='BuyCoinsFilter.php'>Buy Coins Filter</a> &nbsp > &nbsp <a href='BuyCoinsTracking.php'>Buy Coins Tracking ($buyTracking)</a>&nbsp > &nbsp <a href='BuyCoins_Spread.php'>Buy Coins Spread Bet</a>
    &nbsp > &nbsp <a href='BuyCoins_BuyBack.php'>Buy Back ($buyBack)</a></h3>";
  }elseif ($page == 'SellCoin'){
    echo "<h3><a href='SellCoins.php'>Sell Coins ($sellCoin)</a> &nbsp > &nbsp <a href='SellCoins_Tracking.php'>Tracking ($sellTracking)</a> &nbsp > &nbsp <a href='SellCoins_Saving.php'>Saving ($sellSaving)</a> &nbsp > &nbsp <a href='SellCoins_Spread.php'>Spread Bet</a> &nbsp > &nbsp <a href='SellCoins_SpreadCoin.php'>Spread Bet Coin</a>
     &nbsp > &nbsp <a href='SellCoins_SwapCoins.php'>Swap Coins</a></h3>";
  }elseif ($page == 'Profit'){
     echo "<h3><a href='Profit.php'>All Profit</a> &nbsp > &nbsp <a href='Profit_BuyBack.php'>BuyBack Profit</a> &nbsp > &nbsp <a href='Profit_SpreadBet.php'>SpreadBet Profit</a> &nbsp </h3>";
  }elseif ($page == 'Settings'){
    echo "<h3><a href='Settings.php'>User Settings</a> &nbsp > &nbsp <a href='BuySettings.php'>Buy Settings</a> &nbsp > &nbsp <a href='SellSettings.php'>Sell Settings</a> &nbsp > &nbsp <a href='Settings_Patterns.php'>Setting Patterns</a></h3>";
  }
}

function displaySideColumn(){
  //Echo "";?>
      </div>
      <div class="column side">

      </div>
    </div>

    <div class="footer">
      <hr>
      <!-- <input type="button" value="Logout">
      <a href='logout.php'>Logout</a>-->

      <input type="button" onclick="location='logout.php'" value="Logout"/>

    </div><?php
}

function displayMiddleColumn(){
  //Echo "<div class='column middle'>";
}

function displayFarSideColumn(){
  //Echo "</div>";
  //Echo "<div id='visualization' style='width: 600px; height: 400px;'></div>";
  //Echo "</div>";
  //Echo "<div class='column side'>";
}

function displayFooter(){
  //Echo "</div>";
  //Echo "</div>";
  //Echo "<div class='footer'>";
  //Echo "<hr>";
  //Echo "<input type='button' onClick='location.href=\"logout.php\"' value='Logout'/>";
  //Echo "</div>";
}

function getCoinAlerts(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `ID`,`CoinID`, `Action`, `Price`, `Symbol`, `UserName`,`Email` ,`LiveCoinPrice`,`Category`,`Live1HrChange` ,`Live24HrChange` ,`Live7DChange`,`ReocurringAlert`,`DateTimeSent`
  ,`LiveSellOrders`,`LiveBuyOrders`,`LiveMarketCap`,`UserID`,TIMESTAMPDIFF(MINUTE,`DateTimeSent`, now()) as MinsSinceSent, `CoinPricePctChange` FROM `View11_CoinAlerts`";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['ID'],$row['CoinID'],$row['Action'],$row['Price'],$row['Symbol'],$row['UserName'],$row['Email'],$row['LiveCoinPrice'],$row['Category'],$row['Live1HrChange'] //9
    ,$row['Live24HrChange'],$row['Live7DChange'],$row['ReocurringAlert'],$row['DateTimeSent'],$row['LiveSellOrders'],$row['LiveBuyOrders'],$row['LiveMarketCap'],$row['UserID'],$row['MinsSinceSent'] //18
    ,$row['CoinPricePctChange']);
  }
  $conn->close();
  return $tempAry;
}

function getMarketstats(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `Cp`.`LiveCoinPrice`,((`Cp`.`LiveCoinPrice`-`Cpc`.`Live1HrChange`)/`Cpc`.`Live1HrChange`)*100 as `Hr1ChangePctChange`
            ,((`LiveCoinPrice`-`Live24HrChange`)/`Live24HrChange`)*100 as `Hr24ChangePctChange`
            ,((`LiveCoinPrice`-`Live7DChange`)/`Live7DChange`)*100 as `D7ChangePctChange`
              ,((`LiveCoinPrice`-`LastCoinPrice`)/`LastCoinPrice`)*100 as`LiveMarketPctChange`
              ,((`LiveMarketCap`-`LastMarketCap`)/`LastMarketCap`)*100 as `MarketCapPctChange`
              , `Live1HrChange`, `Live24HrChange`, `Live7DChange`
            From `CoinPrice` `Cp`
            join `CoinPctChange` `Cpc` on `Cpc`.`CoinID` = `Cp`.`CoinID`
            join `Coin` `Cn` on `Cn`.`ID` = `Cp`.`CoinID`
            join `CoinMarketCap` `Cmc` on `Cmc`.`CoinID` = `Cp`.`CoinID`
            where `Cn`.`BuyCoin` = 1 and `Cn`.`DoNotBuy` = 0  ";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['LiveCoinPrice'],$row['Hr1ChangePctChange'],$row['Hr24ChangePctChange'],$row['D7ChangePctChange'],$row['LiveMarketPctChange'],$row['MarketCapPctChange']
  ,$row['Live1HrChange'],$row['Live24HrChange'],$row['Live7DChange']);
  }
  $conn->close();
  return $tempAry;
}

function getMarketAlerts($userID = 0){
  $tempAry = [];
  $whereClause = " where `UserID` = $userID";
  if ($userID = 0){ $whereClause = "";}
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $sql = "SELECT `UserID`, `UserName`, `Email`, `DateTimeSent`, `ReocurringAlert`, `Category`, `Action`, `Minutes`, `MarketAlertRuleID` as `MarketAlertRuleID`, `Price`
     FROM `MarketAlertsView`$whereClause";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['UserID'],$row['UserName'],$row['Email'],$row['DateTimeSent'],$row['ReocurringAlert'] //4
    ,$row['Category'],$row['Action'],$row['Minutes'],$row['MarketAlertRuleID'],$row['Price']);
  }
  $conn->close();
  return $tempAry;
}

function getMarketAlertsTotal(){
  $tempAry = [];
  //$whereClause = " where `UserID` = $userID";
  //if ($userID = 0){ $whereClause = "";}
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $sql = "SELECT `UserID`, `UserName`, `Email`, `DateTimeSent`, `ReocurringAlert`, `Category`, `Action`, `Minutes`, `MarketAlertRuleID` as `MarketAlertRuleID`, `Price`
     FROM `MarketAlertsView`";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['UserID'],$row['UserName'],$row['Email'],$row['DateTimeSent'],$row['ReocurringAlert'] //4
    ,$row['Category'],$row['Action'],$row['Minutes'],$row['MarketAlertRuleID'],$row['Price']);
  }
  $conn->close();
  return $tempAry;
}

function getSpreadBetAlerts($userID = 0){
  $tempAry = [];
  $whereClause = " where `UserID` = $userID";
  if ($userID = 0){ $whereClause = "";}
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `LiveCoinPrice`, `Live1HrChange`, `Live24HrChange`, `Live7DChange`, `LiveMarketCap`, `UserID`, `UserName`, `Email`, `DateTimeSent`, `ReocurringAlert`, `Category`, `Action`, `Minutes`, `SpreadBetAlertRuleID`, `Price`
  , `LivePricePct`,`SpreadBetRuleID`
  FROM `SpreadBetAlertsView`$whereClause group by `SpreadBetAlertRuleID`";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['LiveCoinPrice'],$row['Live1HrChange'],$row['Live24HrChange'],$row['Live7DChange'],$row['LiveMarketCap'],$row['UserID'],$row['UserName'],$row['Email'],$row['DateTimeSent'],$row['ReocurringAlert'] //9
    ,$row['Category'],$row['Action'],$row['Minutes'],$row['SpreadBetAlertRuleID'],$row['Price'],$row['LivePricePct'],$row['SpreadBetRuleID']);
  }
  $conn->close();
  return $tempAry;
}

function getSpreadBetAlertsTotal(){
  $tempAry = [];
  //$whereClause = " where `UserID` = $userID";
  //if ($userID = 0){ $whereClause = "";}
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `LiveCoinPrice`, `Live1HrChange`, `Live24HrChange`, `Live7DChange`, `LiveMarketCap`, `UserID`, `UserName`, `Email`, `DateTimeSent`, `ReocurringAlert`, `Category`, `Action`, `Minutes`, `SpreadBetAlertRuleID`, `Price`
  , `LivePricePct`
  FROM `SpreadBetAlertsView`";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['LiveCoinPrice'],$row['Live1HrChange'],$row['Live24HrChange'],$row['Live7DChange'],$row['LiveMarketCap'],$row['UserID'],$row['UserName'],$row['Email'],$row['DateTimeSent'],$row['ReocurringAlert'] //9
    ,$row['Category'],$row['Action'],$row['Minutes'],$row['SpreadBetAlertRuleID'],$row['Price'],$row['LivePricePct']);
  }
  $conn->close();
  return $tempAry;
}

function getCoinAlertsUser($userId){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `ID`,`CoinID`, `Action`, `Price`, `Symbol`, `UserName`,`Email` ,`LiveCoinPrice`,`Category`,`Live1HrChange` ,`Live24HrChange` ,`Live7DChange`,`ReocurringAlert`,`DateTimeSent`,`CoinAlertRuleID`
  FROM `View11_CoinAlerts` WHERE `UserID` = $userId group by `CoinAlertRuleID`";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['ID'],$row['CoinID'],$row['Action'],$row['Price'],$row['Symbol'],$row['UserName'],$row['Email'],$row['LiveCoinPrice'],$row['Category'],$row['Live1HrChange']
    ,$row['Live24HrChange'],$row['Live7DChange'],$row['ReocurringAlert'],$row['DateTimeSent'],$row['CoinAlertRuleID']);
  }
  $conn->close();
  return $tempAry;
}

function getCoinAlertsbyID($id){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `ID`,`CoinID`, `Action`, `Price`, `Symbol`, `UserName`,`Email` ,`LiveCoinPrice`,`Category`,`Live1HrChange` ,`Live24HrChange` ,`Live7DChange`,`ReocurringAlert`,`DateTimeSent`
  FROM `View11_CoinAlerts` WHERE `CoinAlertRuleID` = $id";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['ID'],$row['CoinID'],$row['Action'],$row['Price'],$row['Symbol'],$row['UserName'],$row['Email'],$row['LiveCoinPrice'],$row['Category'],$row['Live1HrChange']
    ,$row['Live24HrChange'],$row['Live7DChange'],$row['ReocurringAlert'],$row['DateTimeSent']);
  }
  $conn->close();
  return $tempAry;
}

function updateCoinAlertsbyID($id, $coinID, $action, $userID, $category, $reocurring, $price){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
  $sql = "UPDATE `CoinAlerts` SET `Action`= '$action', `UserID`= $userID, `Category` = '$category', `ReocurringAlert`= $reocurring, `Price` = $price WHERE `CoinAlertRuleID` = $id";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("updateCoinAlertsbyID",$sql,3,0,"SQL","CoinAlertRuleID:$id");
}

function closeCoinAlerts($id, $table){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    if ($table == 'CoinAlerts'){$sql = "UPDATE `CoinAlerts` SET `Status`= 'Closed' WHERE `ID` = $id";}
    elseif ($table == 'MarketAlerts'){$sql = "UPDATE `MarketAlerts` SET `Status`= 'Closed' WHERE `MarketAlertRuleID` = $id";}
    elseif ($table == 'SpreadBetAlerts'){$sql = "UPDATE `SpreadBetAlerts` SET `Status`= 'Closed' WHERE `SpreadBetAlertRuleID` = $id";}
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("updateCoinAlertsbyID",$sql,3,0,"SQL","CoinAlertRuleID:$id");
}

function updateAlertTime($id, $table){
  $conn = getSQLConn(rand(1,3));
  $current_date = date('Y-m-d H:i');
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    if ($table == 'CoinAlerts'){$sql = "UPDATE `CoinAlerts` SET `DateTimeSent`= now() WHERE `ID` = $id";}
    elseif ($table == 'MarketAlerts'){$sql = "UPDATE `MarketAlerts` SET `DateTimeSent`= now() WHERE `MarketAlertRuleID` = $id";}
    elseif ($table == 'SpreadBetAlerts'){$sql = "UPDATE `MarketAlerts` SET `DateTimeSent`= now() WHERE `SpreadBetAlertRuleID` = $id";}

    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("updateAlertTime",$sql,3,0,"SQL","CoinAlertRuleID:$id");
}

function updateBittrexQuantityFilled($quantFilled, $bittrexRef){
  $conn = getSQLConn(rand(1,3));
  $current_date = date('Y-m-d H:i');
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `BittrexAction` SET `QuantityFilled` = $quantFilled WHERE `BittrexRef` = '$bittrexRef'";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("updateBittrexQuantityFilled",$sql,3,0,"SQL","BittrexRef:$bittrexRef");
}

function sendAlertEmail($to, $symbol , $price, $action, $user){
    $subject = "Coin Alert: ".$coin;
    $from = 'Coin Alert <alert@investment-tracker.net>';
    $body = "Dear ".$user.", <BR/>";
    $body .= "Your coin Alert for $symbol has been triggered : "."<BR/>";
    $body .= "Coin: $symbol Action: $action Price: $price<BR/>";
    $body .= "Kind Regards\nCryptoBot.";
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    //$headers .= 'From: Alerts <Alerts@Investment-Tracker.net>' . "\r\n";
    $headers .= "From:".$from."\r\n";
    $headers .= "To:".$to."\r\n";
    mail($to, $subject, wordwrap($body,70),$headers);
}

function setStyle($isMobile){
  if ($isMobile){
      echo "<style>";
      include 'style/mStyle.css';
      echo "</style>";
  }else{
    echo "<style>";
    include 'style/style.css';
    echo "</style>";
  }
}

function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}


function isCoinMatch($bitPrice, $symbol, $livePrice, $liveSymbol, $isGreater,$lowPrice){
  //echo "<BR> isCoinMatch($bitPrice, $symbol, $livePrice, $liveSymbol, $isGreater)";
  $symbolBool = False; $priceBool = False;
  //Echo "<BR> $symbol : $liveSymbol";
  if ($symbol == $liveSymbol){
    $symbolBool = True;
    //if ($isGreater == 1){
      //Echo "<BR> LIVE Price: $livePrice High Price: $bitPrice LowPrice: $lowPrice";
      if ( $livePrice <= $bitPrice  && $livePrice >= $lowPrice){$priceBool = True;}

      //echo "<BR> if ($livePrice > $bitPrice){";
    //}else{
      //Echo "<BR> Is Less Than: $isGreater";
      //if ($bitPrice >= $livePrice && $lowPrice <= $livePrice){$priceBool = True;}
      //echo "<BR> if ($livePrice < $bitPrice){";
    //}
  }
  if ($symbolBool == True && $priceBool == True) { return True;}
  else{ return False; }

}

function coinMatchPattern($coinPattern, $livePrice, $liveSymbol, $isGreater, $pEnabled, $ruleID, $buySell,$echoEnabled,$name){
  //$pieces = explode(",", $coinPattern);
  $returnFlag = False;
  $piecesSize = count($coinPattern);
  $testTrue = False;
  //echo "<BR> Count : ".$piecesSize;
  for ($x = 0; $x < $piecesSize; $x++) {
    $buyRuleID = $coinPattern[$x][0]; $sellRuleID = $coinPattern[$x][1];
    //echo "<BR> pattern : ".$coinPattern[$x][2];
    $coinPriceMatchPrice = $coinPattern[$x][3]; $coinPriceMatchSymbol = $coinPattern[$x][4];$coinPriceMatchLowPrice = $coinPattern[$x][5];
    //Echo "<br> ".$pieces[$x];
    //$row = explode(":", $pieces[$x]);
    //echo "<BR> coinMatchPattern : $buyRuleID $sellRuleID $coinPriceMatchPrice $coinPriceMatchSymbol";
    if (($buyRuleID == $ruleID && $sellRuleID == 0 && $buySell == 0) OR ($sellRuleID == $ruleID && $buyRuleID == 0 && $buySell == 1)){
      //echo "<BR> coinMatchPattern : $buyRuleID $sellRuleID $coinPriceMatchPrice $coinPriceMatchSymbol";
      if (isCoinMatch((float)$coinPriceMatchPrice,$coinPriceMatchSymbol,$livePrice, $liveSymbol, $isGreater,$coinPriceMatchLowPrice)){ $testTrue = True;}
    //echo "<BR>isCoinMatch((float)$row[1],$row[0],$livePrice, $liveSymbol, $isGreater)";
    }
  }
    if ($pEnabled == 0){
      //print_r("True");
      $returnFlag =  True;
      //exit;
    }
    elseif($testTrue){
      //print_r("True");
      $GLOBALS['allDisabled'] = true;
      $returnFlag =  True;
      exit;
    }else{
      $GLOBALS['allDisabled'] = true;
      //print_r($buyTop >= $score);
      //print_r("False");
      $returnFlag =  False;
    }
    echoAndLog($name, "<BR> coinMatchPattern Enabled:$returnFlag | LicePrice:$livePrice | LiveSymbol:$liveSymbol | IsGreater:$isGreater | Rule:$ruleID",3,$echoEnabled,"","");
    return $returnFlag;
}
function getNewStats(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT distinct `CMCID` FROM `View20_CoinPrices`";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['CMCID']);
  }
  $conn->close();
  return $tempAry;
}

function getStats(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Symbol`,`IDCn`,`BaseCurrency`,`CMCID` FROM `View20_CoinPrices` order by `Symbol` asc";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Symbol'],$row['IDCn'],$row['BaseCurrency'],$row['CMCID']);
  }
  $conn->close();
  return $tempAry;
}

function setStats(){
  $statsAry = getStats();
  $_SESSION['StatsList'] = $statsAry;
  $_SESSION['StatsListSelected'] =  $statsAry[0][0];
  $_SESSION['StatsListTime'] = "15 Minute";
}

function getBase($selected, $statsAry){
    $statsOptionCount = Count($statsAry);
    for($x = 0; $x < $statsOptionCount; $x++) {
      //echo "<BR> If check : 1; ".$statsAry[$x][2]." : 2; ".$statsAry[$x][1]." : SELECTED; ".$selected;
      if ($statsAry[$x][0] == $selected){
        //echo "<BR> Base Currency = ".$statsAry[$x][2];
        return $statsAry[$x][2];
        exit;
      }
    }
}

function NewEcho($textStr, $isMobile, $display, $round = 0){
  // display: 0 = desktop only; 1 = mobile only; 2 = mobile and desktop
  if ($display == 0 && $isMobile == 0){
      Echo $textStr;
  }elseif ($display == 1 && $isMobile == 1){
      Echo $textStr;
  }elseif ($display == 2){
      Echo $textStr;
  }
}

function replaceStars($tempStr,$starCount){
  $returnStr = "";
  if ($starCount == 1){
    for ($k = -1; $k<3-1; $k++){
      //echo "<BR> $k ".str_replace_first("*",$k,$tempStr);
      $tempStr = str_replace_first("*",$k,$tempStr);
      //Echo "<BR> $tempStr";
    }
    $returnStr .= $tempStr;
  }elseif ($starCount == 2){
    for ($j = -1; $j<$starCount; $j++){
      for ($k = -1; $k<3-1; $k++){
        //echo "<BR> $j $k";
        //echo "<BR> $j ".str_replace_first("*",$j,$tempStr);
        $tempStr = str_replace_first("*",$j,$tempStr);
        //echo "<BR> $k ".str_replace_first("*",$k,$tempStr1);
        $tempStr = str_replace_first("*",$k,$tempStr);
        //Echo "<BR> $tempStr2";
      }
    }
    $returnStr .= $tempStr;
  }elseif ($starCount == 3){
    for ($j = -1; $j<$starCount-1; $j++){
      for ($k = -1; $k<$starCount-1; $k++){
        for ($l = -1; $l<3-1; $l++){
          //echo "<BR> $j $k $l";
          //echo "<BR> $j ".str_replace_first("*",$j,$tempStr);
          $tempStr = str_replace_first("*",$j,$tempStr);
          //echo "<BR> $k ".str_replace_first("*",$k,$tempStr1);
          $tempStr = str_replace_first("*",$k,$tempStr);
          //echo "<BR> $l ".str_replace_first("*",$l,$tempStr2);
          $tempStr = str_replace_first("*",$l,$tempStr);
          //Echo "<BR> $tempStr3";
        }
      }
    }
    $returnStr .= $tempStr;
  }
  //echo "<BR> Return String: $returnStr";
return $returnStr;
}

function str_replace_first($search, $replace, $subject) {
    $pos = strpos($subject, $search);
    if ($pos !== false) {
        return substr_replace($subject, $replace, $pos, strlen($search));
    }
    return $subject;
}

function returnWildcardStr($tempStr, $starCount){
  $returnStr = "";
  if ($starCount == 1){
    for ($n=0; $n<1; $n++){
        $returnStr .= $tempStr.",".$tempStr.",".$tempStr.",";
    }
  }elseif ($starCount == 2){
    for ($n=0; $n<3; $n++){
        $returnStr .= $tempStr.",".$tempStr.",".$tempStr.",";
    }
  }elseif ($starCount == 3){
    for ($n=0; $n<9; $n++){
        $returnStr .= $tempStr.",".$tempStr.",".$tempStr.",";
    }
  }
  //echo "<BR> Test Return Str: $returnStr";
  return $returnStr;
}

function stringsToArray($str1, $str2, $str3, $str4){
  //echo "<BR> STR1 ".$str1;
  $tmpAry1 = explode(',',$str1); $tmpAry2 = explode(',',$str2);
  $tmpAry3 = explode(',',$str3); $tmpAry4 = explode(',',$str4);

  $aryCount = count($tmpAry1); $returnAry = [];
  //echo "<BR> Count ".$aryCount." number 1 ".$tmpAry1[0];
  //$aryCount2 = count($tmpAry2);

  for ($i=0; $i<$aryCount; $i++){
    //echo "<BR> B :".$tmpAry1[$i];
    $returnAry[$i][0] = $tmpAry1[$i];
    $returnAry[$i][1] = $tmpAry2[$i];
    $returnAry[$i][2] = $tmpAry3[$i];
    $returnAry[$i][3] = $tmpAry4[$i];
  }
  return $returnAry;
}

Function removeWildcard($tempStr){
	//$tempStr = explode(',',$wildcardStr);
  $returnUserIDStr ="";$returnSellRuleIDStr = ""; $returnBuyRuleIDStr ="";
	$tempStrCount = count($tempStr);
  $returnStr = "";
	for($i=0; $i < $tempStrCount; $i++){
    //echo "<BR> Test: ".$tempStr[$i][2]." | ".strpos($tempStr[$i][2], '*');
    if (strpos($tempStr[$i][2], '*') !== false) {
        $starCount =substr_count($tempStr[$i][2],"*");
        //$returntempStr = $tempStr[$i].",";
        for ($x=0; $x<$starCount; $x++){
          //echo "<BR> returnWildcardStr(".$tempStr[$i][2].",$starCount);";
          $newStr = returnWildcardStr($tempStr[$i][2],$starCount);
          $returntempStr = replaceStars($newStr,$starCount);
          $buyRuleIDStr = returnWildcardStr($tempStr[$i][0],$starCount);
          $sellRuleIDStr = returnWildcardStr($tempStr[$i][1],$starCount);
          $userIDStr = returnWildcardStr($tempStr[$i][3],$starCount);
        }
        $returnStr .= $returntempStr;
        $returnBuyRuleIDStr .= $buyRuleIDStr;
        $returnSellRuleIDStr .= $sellRuleIDStr;
        $returnUserIDStr .= $userIDStr;
    }else{
        //no instances of * - add the string to the return string
        $returnStr .=$tempStr[$i][2].",";
        $returnBuyRuleIDStr .= $tempStr[$i][0].",";
        $returnSellRuleIDStr .= $tempStr[$i][1].",";
        $returnUserIDStr .= $tempStr[$i][3].",";
    }
	}
  //echo "<BR> returnBuyRuleIDStr $returnBuyRuleIDStr";
  //echo "<BR> returnSellRuleIDStr $returnSellRuleIDStr";
  //echo "<BR> returnStr $returnStr";
  //echo "<BR> returnUserIDStr $returnUserIDStr";
  $finalReturnStr = stringsToArray(rtrim($returnBuyRuleIDStr,','),rtrim($returnSellRuleIDStr,','),rtrim($returnStr,','),rtrim($returnUserIDStr,','));
 return $finalReturnStr;
}

function setTimeZone(){
  date_default_timezone_set('Asia/Dubai');
}

function getCoinList($coinStats, $num){
  $returnStr = "";
  $coinStatsCount = count($coinStats);
  for ($i=0; $i<$coinStatsCount; $i++){
    $returnStr .= $coinStats[$i][$num].",";
  }
  //LogToSQL("CMCStats","".rtrim($returnStr,','),3,1);
  return rtrim($returnStr,',');
}

function getCoinPriceMatchList($userID = 0){
  $conn = getSQLConn(rand(1,3));
  $whereClause = "";
  if ($userID <> 0){ $whereClause = " where `UserID` = $userID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "select `Cpmr`.`BuyRuleID` AS `BuyRuleID`,`Cpmr`.`SellRuleID` AS `SellRuleID`,`Cpm`.`CoinID` AS `CoinID`,`Cpm`.`Price` AS `Price`,`Cn`.`Symbol` AS `Symbol`
          ,`Cpm`.`LowPrice` AS `LowPrice`,`Cpm`.`UserID` AS `UserID`
          from ((`CoinPriceMatchRules` `Cpmr` join `CoinPriceMatch` `Cpm` on((`Cpm`.`ID` = `Cpmr`.`CoinPriceMatchID`))) join `Coin` `Cn` on((`Cn`.`ID` = `Cpm`.`CoinID`)))$whereClause";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['BuyRuleID'],$row['SellRuleID'],$row['CoinID'],$row['Price'],$row['Symbol'],$row['LowPrice'],$row['UserID']);
  }
  $conn->close();
  return $tempAry;
}

function getDelayCoinPurchaseTimes(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  $whereClause = "";
  //if ($userID <> 0){ $whereClause = " where `UserID` = $userID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `ID`, `CoinID`, `UserID`, `DelayTime`  FROM `View10_DelayCoinPurchase` WHERE `DelayTime` > now()";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['CoinID'],$row['UserID'],$row['DelayTime']);
  }
  $conn->close();
  return $tempAry;
}

function getCoinPriceMatchSettings($whereClause = ""){
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `CoinID`,`Price`,`Symbol`,`LowPrice`,`Name`FROM `NewCoinPriceMatchSettingsView` $whereClause";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['CoinID'],$row['Price'],$row['Symbol'],$row['LowPrice'],$row['Name'],$row['UserID']);
  }
  $conn->close();
  return $tempAry;
}

function getCoinPricePattenList($userID = 0){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  $whereClause = "";
  if ($userID <> 0){ $whereClause = " where `UserID` = $userID";}
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Cppr`.`BuyRuleID`,`Cppr`.`SellRuleID`,`Cpp`.`CoinPattern`,`Cppr`.`UserID`
            FROM `CoinPricePatternRules` `Cppr`
            join `CoinPricePattern` `Cpp` on `Cpp`.`ID` = `Cppr`.`PatternID`$whereClause";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['BuyRuleID'],$row['SellRuleID'],$row['CoinPattern'],$row['UserID']);
  }
  $conn->close();
  return $tempAry;
}

function getCoinPricePattenSettings(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Name`,`CoinPattern` FROM `NewCoinPricePatternSettingsView`";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Name'],$row['CoinPattern']);
  }
  $conn->close();
  return $tempAry;
}

function getCoin1HrPattenList($userID = 0){
  $conn = getSQLConn(rand(1,3));
  $whereClause = "";
  if ($userID <> 0){ $whereClause = " where `UserID` = $userID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Cpr`.`BuyRuleID`,`Cpr`.`SellRuleID`,`Cp`.`Pattern`,`Cpr`.`UserID`
        FROM `Coin1HrPatternRules` `Cpr`
        join `Coin1HrPattern` `Cp` on `Cp`.`ID` = `Cpr`.`Coin1HrPatternID` $whereClause order by `Cpr`.`BuyRuleID`,`Cpr`.`SellRuleID`";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['BuyRuleID'],$row['SellRuleID'],$row['Pattern'],$row['UserID']);
  }
  $conn->close();
  return $tempAry;
}

function getCoin1HrPattenSettings(){
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Name`,`Pattern` FROM `NewCoin1HrPatternSettingsView`";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Name'],$row['Pattern']);
  }
  $conn->close();
  return $tempAry;
}

function getAutoBuyPrices(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `CoinID`,`AutoBuyPrice`,`AutoSellPrice` FROM `CryptoAuto`";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['CoinID'],$row['AutoBuyPrice'],$row['AutoSellPrice']);
  }
  $conn->close();
  return $tempAry;
}

function findAutoBuyPrice($autoBuyPrice,$coinID){
  $autoBuyPriceSize = count($autoBuyPrice);
  for ($w=0;$w<$autoBuyPriceSize;$w++){
    $curretID = $autoBuyPrice[$w][0];
    if ($coinID == $curretID){
      return $autoBuyPrice[$w][1];
      exit;
    }
  }
}

function setTextColour($num, $onOffFlag){
  $colour = "";
  if ($onOffFlag == False){
    if ($num < -0.5 and $num > -0.75){ $colour = "background-color:Orange;";}
    elseif ($num < -0.76){$colour = "background-color:MediumSeaGreen;";}

  }else{
    //Echo "<BR> Test2: $num";
    if ($num == ""){ $colour = "background-color:Orange;";}
    elseif ($num == "1"){$colour = "background-color:MediumSeaGreen;";}
    //return $colour;
  }
return $colour;
}

function setTextColourTarget($num, $onOffFlag, $targetTop, $targetBtm){

  $flagNum = 0;
  $colour = "";
  if ($onOffFlag == False){
    if ($num <= $targetTop){
      $flagNum = 1;
      $colour = "background-color:LightSkyBlue;";
    }elseif ($num > $targetTop and $num <= 0){
      $flagNum = 2;
      $colour = "background-color:MediumSeaGreen;";
    }elseif ($num > abs($targetTop) and $num < (abs($targetTop) * 2)){
      $flagNum = 3;
      $colour = "background-color:Orange;";
    }else{
      $flagNum = 4;
      $colour = "background-color:Crimson;";
    }
  }else{
    //Echo "<BR> Test2: $num";
    if ($num == ""){ $colour = "background-color:Orange;";}
    elseif ($num == "1"){$colour = "background-color:MediumSeaGreen;";}
    //return $colour;
  }
  //echo "<BR> COLOUR TEST: $num, $targetTop, $targetBtm, $flagNum, $colour";
return $colour;
}

function getBuyRulesIDs($userID){
$conn = getSQLConn(rand(1,3));
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT `RuleID` FROM `UserBuyRules` WHERE `UserID` = $userID and `BuyCoin` = 1";
$result = $conn->query($sql);
//$result = mysqli_query($link4, $query);
//mysqli_fetch_assoc($result);
while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['RuleID']);
}
$conn->close();
return $tempAry;
}

function addCoinSwapIDtoTracking($coinSwapID,$transID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `TrackingCoins` SET `CoinSwapID` = $coinSwapID where `TransactionID` = $transID";

  print_r($sql);
  LogToSQL("SpreadBetTrackingSQL","$sql",3,0);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("addCoinSwapIDtoTracking: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("addCoinSwapIDtoTracking","$sql",3,0,"SQL CALL","UserID:$userID");
}

function addWebUsage($userID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "call addWebUsage($userID);";

  //print_r($sql);

  if ($conn->query($sql) === TRUE) {
      //echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("addWebUsage: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("addWebUsage","$sql",3,0,"SQL CALL","UserID:$userID");
}

function getWebUsage($userID){
$conn = getSQLConn(rand(1,3));
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT `BuyTracking`, `BuyBack`, `SellCoin`, `SellTracking`, `SellSaving`, `BittrexAction` FROM `CryptoBotWebUsageTable`
  WHERE `UserID` = $userID ";
$result = $conn->query($sql);
//$result = mysqli_query($link4, $query);
//mysqli_fetch_assoc($result);
while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['BuyTracking'],$row['BuyBack'],$row['SellCoin'],$row['SellTracking'],$row['SellSaving'],$row['BittrexAction']);
}
$conn->close();
return $tempAry;
}

function getSpreadbetCoins($baseCurrency){
$conn = getSQLConn(rand(1,3));
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT `Sbc`.`SpreadBetRuleID`,`Sbc`.`CoinID`,`Cp`.`LiveCoinPrice`,`Cn`.`BaseCurrency`
          FROM `SpreadBetCoins` `Sbc`
      	  join `Coin` `Cn` on  `Sbc`.`CoinID` = `Cn`.`ID`
          join `CoinPrice` `Cp` on `Cp`.`CoinID` = `Sbc`.`CoinID`
          WHERE `Cn`.`BaseCurrency` = '$baseCurrency'
          ORDER BY RAND()";
$result = $conn->query($sql);
//$result = mysqli_query($link4, $query);
//mysqli_fetch_assoc($result);
while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['SpreadBetRuleID'],$row['CoinID'],$row['SpreadBetTransactionID'],$row['LiveCoinPrice'],$row['BaseCurrency']);
}
$conn->close();
return $tempAry;
}

function getSpreadBetSellCoins($spreadBetTransactionID){
$conn = getSQLConn(rand(1,3));
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT `Cp`.`LiveCoinPrice`, `Tr`.`ID` as `TransactionID` ,`Tr`.`SpreadBetTransactionID`
          FROM `Transaction` `Tr`
          join `CoinPrice` `Cp` on `Cp`.`CoinID` = `Tr`.`CoinID`
          WHERE `Tr`.`Type` = 'SpreadSell' and `Tr`.`SpreadBetTransactionID` = 11947";
$result = $conn->query($sql);
//$result = mysqli_query($link4, $query);
//mysqli_fetch_assoc($result);
while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['LiveCoinPrice'],$row['TransactionID'],$row['SpreadBetTransactionID']);
}
$conn->close();
return $tempAry;
}

function addTrackingCoin($coinID, $coinPrice, $userID, $baseCurrency, $sendEmail, $buyCoin, $quantity, $ruleIDBuy, $coinSellOffsetPct, $coinSellOffsetEnabled, $buyType, $minsToCancelBuy, $sellRuleFixed, $toMerge, $noOfPurchases, $risesInPrice, $type, $originalPrice,$spreadBetTransID,$spreadBetRuleID,$overrideCoinAlloc,$callName, $savingOverride, $transID = 0){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "INSERT INTO `TrackingCoins`(`CoinID`, `CoinPrice`, `UserID`, `BaseCurrency`, `SendEmail`, `BuyCoin`, `Quantity`, `RuleIDBuy`, `CoinSellOffsetPct`, `CoinSellOffsetEnabled`, `BuyType`, `MinsToCancelBuy`, `SellRuleFixed`, `Status`, `ToMerge`
    ,`NoOfPurchases`,`OriginalPrice`,`BuyRisesInPrice`,`Type`,`LastPrice`,`SBRuleID`,`SBTransID`,`OverrideCoinAllocation`,`TransactionID`,`BaseBuyPrice`,`SavingOverride`)
  VALUES ($coinID,$coinPrice,$userID,'$baseCurrency', $sendEmail, $buyCoin, $quantity, $ruleIDBuy, $coinSellOffsetPct, $coinSellOffsetEnabled, $buyType, $minsToCancelBuy, $sellRuleFixed, 'Open', $toMerge, $noOfPurchases,$originalPrice, $risesInPrice, '$callName',$coinPrice,$spreadBetRuleID
  ,$spreadBetTransID,$overrideCoinAlloc,$transID,$coinPrice,$savingOverride)";

  print_r($sql);
  LogToSQL("SpreadBetTrackingSQL","$sql",3,0);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("AddTrackingCoin: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("NewBuySellCoins:$callName","$sql",3,0,"addTrackingCoin","UserID:$userID");
}

function updateReduceLossCounter($transID,$callName){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `TrackingCoins` SET `ReduceLossCounter`= `ReduceLossCounter` + 1 WHERE `TransactionID` = $transID";

  print_r($sql);
  LogToSQL("updateReduceLossCounter","$sql",3,0);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateReduceLossCounter: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("updateReduceLossCounter:$callName","$sql",3,1,"addTrackingCoin","TransID:$transID");
}

function addReduceLossCounterToTrans($reduceLossCounter,$coinID,$userID,$callName){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `Transaction` SET `ReduceLossCounter` = $reduceLossCounter WHERE `CoinID` = $coinID and `UserID` = $userID Order by `OrderDate` Desc Limit 1";

  print_r($sql);
  LogToSQL("addReduceLossCounterToTrans","$sql",3,0);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("addReduceLossCounterToTrans: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("addReduceLossCounterToTrans:$callName","$sql",3,1,"addTrackingCoin","TransID:$transID");
}


function runLowMarketMode($userID,$mode){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call setLowMarketMode($userID,90,$mode);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  if ($mode > 0){
    logAction("runLowMarketMode: ".$sql,'TrackingCoins', 0);
    newLogToSQL("runLowMarketMode","$sql",3,0,"SQL CALL","UserID:$userID");
  }
}

function updateCoinAllocationOverride($coinID,$userID,$overrideCoinAlloc,$toMerge){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `Transaction` SET `OverrideCoinAllocation` = $overrideCoinAlloc, `ToMerge` = $toMerge where `CoinID` = $coinID and `UserID` = $userID
            order by `OrderDate` desc
            Limit 1 ";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateCoinAllocationOverride: ".$sql, 'SQL_UPDATE', 0);
  newLogToSQL("updateCoinAllocationOverride","$sql",3,1,"SQL CALL","UserID:$userID");
}

function getNewTrackingCoins($userID = 0){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
//12
  $whereClause = " WHERE `TrackingStatus`  not in  ('Closed','Cancelled')";
  if ($userID <> 0){ $whereClause = " WHERE `UserID` = $userID and `TrackingStatus`  not in  ('Closed','Cancelled')";}
    $sql = "SELECT `CoinID`,`CoinPrice`,`TrackDate`,`Symbol`,`LiveCoinPrice`,(`LiveCoinPrice`-`LastCoinPrice`) as `PriceDifference`,((`LiveCoinPrice`-`LastCoinPrice`)/`LastCoinPrice`)*100 as `PctDifference`,`UserID`
    ,`BaseCurrency`,`SendEmail`,`BuyCoin`,`Quantity`,`RuleIDBuy`,`CoinSellOffsetPct`
      ,`CoinSellOffsetEnabled`,`BuyType`,`MinsToCancelBuy`,`SellRuleFixed`,`APIKey`,`APISecret`,`KEK`,`Email`,`UserName`,`IDTc`,TIMESTAMPDIFF(MINUTE,`TrackDate`,  NOW()) as MinsFromDate, `NoOfPurchases`,`NoOfRisesInPrice`
      ,`TotalRisesInPrice`,`DisableUntil`,`NoOfCoinPurchase`,`OriginalPrice`,`BuyRisesInPrice`,`LimitBuyAmountEnabled`, `LimitBuyAmount`,`LimitBuyTransactionsEnabled`, `LimitBuyTransactions`
      ,`NoOfBuyModeOverrides`,`CoinModeOverridePriceEnabled`,ifnull(`CoinMode`,0) as CoinMode,`TrackingType`, `LastPrice`,`SBRuleID`,`SBTransID`,`IDTc` as `TrackingID`,`quickBuyCount`,timestampdiff(MINUTE,now(),`DisableUntil`) as MinsDisabled
      ,`OverrideCoinAllocation`,`OneTimeBuyRule`,`BuyAmountCalculationEnabled`,`ATHPrice` as AllTimeHighPrice,`TransactionID`,`CoinSwapID`,`OldBuyBackTransID`,`ToMerge`,`BaseBuyPrice`,`ReduceLossCounter`,`LowMarketModeEnabled`,`SavingOverride`
      ,`HoursFlatPdcs`,`PctOfAuto`
      from `View2_TrackingBuyCoins` $whereClause order by `NoOfRisesInPrice` Desc";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['CoinID'],$row['CoinPrice'],$row['TrackDate'],$row['Symbol'],$row['LiveCoinPrice'],$row['PriceDifference'],$row['PctDifference'],$row['UserID'],$row['BaseCurrency'],$row['SendEmail'] //9
    ,$row['BuyCoin'],$row['Quantity'],$row['RuleIDBuy'],$row['CoinSellOffsetPct'],$row['CoinSellOffsetEnabled'],$row['BuyType'],$row['MinsToCancelBuy'],$row['SellRuleFixed'],$row['APIKey'],$row['APISecret'] //19
    ,$row['KEK'],$row['Email'],$row['UserName'],$row['IDTc'],$row['MinsFromDate'],$row['NoOfPurchases'],$row['NoOfRisesInPrice'],$row['TotalRisesInPrice'],$row['DisableUntil'],$row['NoOfCoinPurchase'],$row['OriginalPrice'] //30
    ,$row['BuyRisesInPrice'],$row['LimitBuyAmountEnabled'],$row['LimitBuyAmount'],$row['LimitBuyTransactionsEnabled'],$row['LimitBuyTransactions'],$row['NoOfBuyModeOverrides'],$row['CoinModeOverridePriceEnabled'] //37
    ,$row['CoinMode'],$row['TrackingType'],$row['LastPrice'],$row['SBRuleID'],$row['SBTransID'],$row['TrackingID'],$row['quickBuyCount'],$row['MinsDisabled'],$row['OverrideCoinAllocation'],$row['OneTimeBuyRule'] //47
    ,$row['BuyAmountCalculationEnabled'],$row['AllTimeHighPrice'],$row['TransactionID'],$row['CoinSwapID'],$row['OldBuyBackTransID'],$row['ToMerge'],$row['BaseBuyPrice'],$row['ReduceLossCounter'],$row['LowMarketModeEnabled']//56
    ,$row['SavingOverride'],$row['HoursFlatPdcs'],$row['PctOfAuto']); //59
  }
  $conn->close();
  return $tempAry;

}

function getOpenTransactions(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
//12
  $whereClause = " ";
  //if ($userID <> 0){ $whereClause = " WHERE `UserID` = $userID";}
  $sql = "SELECT `Uc`.`UserID`
            , ifnull(`Oct`.`NoOfTransactions`,0) as CoinModeTransactions
            , ifnull(`Orbt`.`NoOfTransactions`,0) as RuleBasedTransactions
            , ifnull(`Osbt`.`NoOfTransactions`,0) as SpreadBetTransactions
            , ifnull(count(`Ba`.`ID`),0) as OpenBittrexTransactions
            FROM `UserConfig` `Uc`
            left join `OpenCoinModeTransactions` `Oct` on `Oct`.`UserID` = `Uc`.`UserID`
            left join `OpenRuleBasedTransactions` `Orbt` on `Orbt`.`UserID` = `Uc`.`UserID`
            left join `OpenSpreadBetTransactions` `Osbt` on `Osbt`.`UserID` =  `Uc`.`UserID`
            Left Join `BittrexAction` `Ba` on `Ba`.`UserID` = `Uc`.`UserID` and `Ba`.`Status` = '1' and `Ba`.`Type` in ('SpreadBuy','Buy')";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['UserID'],$row['CoinModeTransactions'],$row['RuleBasedTransactions'],$row['SpreadBetTransactions'],$row['OpenBittrexTransactions']);
  }
  $conn->close();
  return $tempAry;
}

function setNewTrackingPrice($coinPrice, $ID, $mode){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  if ($mode == 'Buy'){
      $sql = "UPDATE `TrackingCoins` SET `BaseBuyPrice` = $coinPrice WHERE `ID` = $ID";
  }else{
    $sql = "UPDATE `TrackingSellCoins` SET `BaseSellPrice` = $coinPrice WHERE `ID` = $ID";
  }


  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("setNewTrackingPrice: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("setNewTrackingPrice",$sql,3,0,"SQL","TrackingID:$mode:$ID");
}

function setLastPrice($coinPrice, $ID, $mode){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  if ($mode == 'Buy'){
      $sql = "UPDATE `TrackingCoins` SET `LastPrice` = $coinPrice WHERE `ID` = $ID";
  }else{
    $sql = "UPDATE `TrackingSellCoins` SET `LastPrice` = $coinPrice WHERE `ID` = $ID";
  }


  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("setNewTrackingPrice: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("setNewTrackingPrice",$sql,3,0,"SQL","TrackingID:$mode:$ID");
}

function setNewTargetPrice($transactionID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `SpreadBetSellTarget` SET `SellPct`=`SellPct` + 1.5  WHERE `TransactionID` = $transactionID ";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("setNewTargetPrice: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("setNewTargetPrice",$sql,3,0,"SQL","TransID:$transactionID");
}

function closeNewTrackingCoin($ID, $deleteFlag, $verNum, $reason){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  if ($deleteFlag == True){
    $updateSQL = "DELETE from `TrackingCoins` ";
  }else{
    $updateSQL = "UPDATE `TrackingCoins` SET `Status` = 'Closed' ";
  }

  $sql = "$updateSQL WHERE `ID` = $ID";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
    echo "New record created successfully";
  } else {
    echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("closeNewTrackingCoin: ".$sql. $conn->error, 'TrackingCoins', 0);
  newLogToSQL("closeNewTrackingCoin$verNum",$sql." Reason $reason",3,0,"SQL","TrackingCoinID:$ID");
}

function reOpenBuySellProfitRule($ruleID, $userID, $coinID){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

  $sql = "call CancelBuySellProfit($ruleID, $userID, $coinID)";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("reOpenBuySellProfitRule: ".$sql. $conn->error, 'TrackingCoins', 0);
  newLogToSQL("reOpenBuySellProfitRule",$sql,3,1,"SQL","RuleID:$ruleID");
}

function updateTrackingCoinToMerge($ID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `Transaction` `Tr`
            SET `Tr`.`ToMerge`= 1  WHERE `Tr`.`ID` in ($ID,(SELECT `OldBuyBackTransID` FROM `BittrexAction` WHERE `TransactionID` = $ID))";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateTrackingCoinToMerge: ".$sql, 'SQL_UPDATE', 0);
  newLogToSQL("updateTrackingCoinToMerge",$sql,3,1,"SQL","TransactionID:$ID");
}

function updateReduceLossSettings($ID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "call updateReduceLossSettings($ID);";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateReduceLossSettings: ".$sql, 'SQL_UPDATE', 0);
  newLogToSQL("updateReduceLossSettings",$sql,3,1,"SQL","TransactionID:$ID");
}

function updateMergeAry($toMergeAry, $finalMergeAry){
  $finalMergeArySize = Count($finalMergeAry);
  $existing = False;
  for ($j=0; $j<$finalMergeArySize; $j++){
    echo "<BR> TEST ".$toMergeAry[0]."=".$finalMergeAry[$j][0]." & ".$toMergeAry[1]."=".$finalMergeAry[$j][1];
    if ($toMergeAry[0] == $finalMergeAry[$j][0] && $toMergeAry[1] == $finalMergeAry[$j][1]){
      //User/Coin exist
      echo "<BR> EXISTING is TRUE ".$toMergeAry[0]."=".$finalMergeAry[$j][0]." & ".$toMergeAry[1]."=".$finalMergeAry[$j][1];
      $existing = True;
      $finalMergeAry[$j][4] = $finalMergeAry[$j][4]+$toMergeAry[4]; //amount
      echo "<BR> adding ".$finalMergeAry[$j][4]."+".$toMergeAry[4];
      $finalMergeAry[$j][5] = $finalMergeAry[$j][5]+$toMergeAry[5]; //cost
      echo "<BR> adding ".$finalMergeAry[$j][5]."+".$toMergeAry[5];
      $finalMergeAry[$j][6] = $finalMergeAry[$j][6].$toMergeAry[3].",";
      echo "<BR> adding ".$toMergeAry[5];
      $finalMergeAry[$j][7] = $finalMergeAry[$j][7]+1; //count
      echo "<BR> adding ".$finalMergeAry[$j][7]."+1";
      $finalMergeAry[$j][8] = $finalMergeAry[$j][8];
      $finalMergeAry[$j][9] = $finalMergeAry[$j][9];
      $finalMergeAry[$j][10] = $finalMergeAry[$j][10]+$toMergeAry[8];
    }
  }
  if ($existing == False){
    echo "<BR> EXISTING is FALSE";
    //if ($finalMergeArySize == 0) {$finalMergeArySize = $finalMergeArySize;} else {$finalMergeArySize = $finalMergeArySize+1;}
    $finalMergeAry[$finalMergeArySize][0] = $toMergeAry[0];
    echo "<BR> SETTING: ".$toMergeAry[0];
    $finalMergeAry[$finalMergeArySize][1] = $toMergeAry[1];
    echo "<BR> SETTING: ".$toMergeAry[1];
    $finalMergeAry[$finalMergeArySize][2] = $toMergeAry[2];
    echo "<BR> SETTING: ".$toMergeAry[2];
    $finalMergeAry[$finalMergeArySize][3] = $toMergeAry[3];
    $finalMergeAry[$finalMergeArySize][4] = $toMergeAry[4];
    $finalMergeAry[$finalMergeArySize][5] = $toMergeAry[5];
    $finalMergeAry[$finalMergeArySize][6] = "";
    $finalMergeAry[$finalMergeArySize][7] = 1;
    $finalMergeAry[$finalMergeArySize][8] = $toMergeAry[6];
    $finalMergeAry[$finalMergeArySize][9] = $toMergeAry[7];
    $finalMergeAry[$finalMergeArySize][10] = $toMergeAry[8];
  }
  return $finalMergeAry;
}


function UpdateTransCount($count,$transactionID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call UpdateTransCount($count,$transactionID);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("UpdateTransCount($count,$transactionID)",'TrackingCoins', 0);
  newLogToSQL("UpdateTransCount","$sql",3,sQLUpdateLog,"SQL CALL","TransactionID:$transactionID");
}

function mergeTransactions($transactionID, $amount, $avCost){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call MergeTransactions($avCost,$transactionID,$amount);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("mergeTransactions($avCost,$transactionID,$amount,$lastTransID)",'TrackingCoins', 0);
  newLogToSQL("mergeTransactions","$sql",3,sQLUpdateLog,"SQL CALL","TransactionID:$transactionID");
}

function deleteOldTrans($lastTransID){
  $piecesAry = explode(",",$lastTransID);
  $piecesArySize = count($piecesAry);

  for ($i=1; $i<$piecesArySize; $i++){
    $oldTransID = $piecesAry[$i];
    closeOldTransSQL($oldTransID);
  }
}

function closeOldTransSQL($id){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `Transaction` SET `Status` = 'Merged' WHERE `ID` in ($id)";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("closeOldTransSQL: $sql",'TrackingCoins',0);
  newLogToSQL("closeOldTransSQL",$sql,3,1,"SQL","TransID:$id");
}

function updateNoOfRisesInPrice($newTrackingCoinID, $num){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `TrackingCoins` SET `NoOfRisesInPrice`= $num WHERE `ID` = $newTrackingCoinID ";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateNoOfRisesInPrice: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("updateNoOfRisesInPrice",$sql,3,0,"SQL","TrackingCoinID:$newTrackingCoinID");
}

function getNumberColour($ColourText){
  if ($ColourText >= 0){
    $colour = "#3cb371";
  }elseif ($ColourText == 0) {
    $colour = "#ffa500";
  }else{
    $colour = "#ff0000";
  }
  return $colour;
}

function getSparklineData($coin){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `LiveCoinPrice` as LiveCoinPrice
    FROM `CoinBuyHistory`
    order by `ActionDate` asc ";
    $result = $conn->query($sql);
    //$result = mysqli_query($link4, $query);
    //mysqli_fetch_assoc($result);
    while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['LiveCoinPrice']);
    }
    $conn->close();
    return $tempAry;
}

function getMultiSellRules($transID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `SellRuleID` FROM `MultiSellRuleConfig` WHERE `TransactionID` = $transID";
  //echo "<BR> $sql";
    $result = $conn->query($sql);
    //$result = mysqli_query($link4, $query);
    //mysqli_fetch_assoc($result);
    while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['SellRuleID']);
    }
    $conn->close();
    return $tempAry;
}

function getMultiSellRulesTemplate($ruleID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `MultiRuleStr` FROM `MultiSellRuleTemplate` WHERE `ID` = $ruleID";
  //echo "<BR> $sql";
  newLogToSQL("getMultiSellRulesTemplate", "$sql", 3, 1,"SQL CALL","RULEID:$ruleID");
    $result = $conn->query($sql);
    //$result = mysqli_query($link4, $query);
    //mysqli_fetch_assoc($result);
    while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['MultiRuleStr']);
    }
    $conn->close();
    return $tempAry;
}

function writeMultiRule($sellRuleIDFromTemplate,$transactionID,$userID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "call WriteMultiRule($userID,$transactionID,$sellRuleIDFromTemplate);";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("writeMultiRule: ".$sql, 'SQL_CALL', 0);
  newLogToSQL("writeMultiRule","$sql",3,1,"BittrexBuy","TransactionID:$transactionID");
}

function writeMultiRuleTemplateID($transactionID,$multiSellRuleTemplateID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `Transaction` SET `MultiSellRuleTemplateID` = $multiSellRuleTemplateID,`MultiSellRuleEnabled` = 1 where `ID` = $transactionID; ";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("writeMultiRuleTemplateID: ".$sql, 'SQL_CALL', 0);
  newLogToSQL("writeMultiRuleTemplateID","$sql",3,1,"BittrexBuy","TransactionID:$transactionID");
}

function checkMultiSellRules($sellRule, $multiRuleAry){
  $multiSellRuleArySize = count($multiRuleAry);
  $ruleFlag = false;
  //echo "<BR> Ary Size: $multiSellRuleArySize";
  for ($i=0; $i<$multiSellRuleArySize; $i++){
    //echo "<BR> MultiSellRule Check: ".$multiRuleAry[$i][0]." - $sellRule";
    if ($multiRuleAry[$i][0] == $sellRule){
      //echo "<BR> Multi Sell Rule Found: $sellRule";
      $ruleFlag = true;
    }
  }
  return $ruleFlag;
}

function dataToString($seperator, $array){
  $num = 0;
  if(!empty($array)){
      $num = count($array);
  }

  $returnStr = "";
  for ($i=0; $i<$num; $i++){
    //echo "<BR> ".$array[$i][0];
    $returnStr .= round($array[$i][0],4).",";
  }
  return rtrim($returnStr,',');
}

function saveImage($coin, $url, $savePath){
  echo "<BR>".$url;
  echo "<BR>".$savePath.$coin.'.png';
  $ch = curl_init($url);
  $fp = fopen($savePath.$coin.'.png', 'wb');
  curl_setopt($ch, CURLOPT_FILE, $fp);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_exec($ch);
  curl_close($ch);
  fclose($fp);
}

function newTrackingSellCoins($LiveCoinPrice, $userID,$transactionID,$SellCoin,$SendEmail,$sellCoinOffsetEnabled,$sellCoinOffsetPct,$fallsInPrice,$type,$callName){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "call AddTrackingSellCoin($LiveCoinPrice, $userID,$transactionID,$SellCoin,$SendEmail,$sellCoinOffsetEnabled,$sellCoinOffsetPct,$fallsInPrice,'$type');";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("newTrackingSellCoins: ".$sql, 'SQL_CALL', 0);
  newLogToSQL("BuySellCoins:$callName","$sql",3,1,"newTrackingSellCoins","TransactionID:$transactionID");
}

function setBuyPct($bounceDifference,$transactionID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `SwapCoins` SET `PctToBuy` = $bounceDifference where `TransactionID` = $transactionID ";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("setBuyPct: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("setBuyPct","$sql",3,1,"SQL CALL","TransactionID:$transactionID");
}

function setTransactionPending($id){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `Transaction` SET `Status`= 'Pending' WHERE `ID` = $id ";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("setTransactionPending: ".$sql, 'SQL_UPDATE',0);
  newLogToSQL("updateTrackingCoinToMerge",$sql,3,0,"SQL","TransactionID:$id");
}

function setTransactionStatus($id,$status){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `Transaction` SET `Status`= '$status' WHERE `ID` = $id ";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("setTransactionStatus: ".$sql, 'SQL_UPDATE', 0);
  newLogToSQL("setTransactionStatus",$sql,3,0,"SQL","TransactionID:$id");
}

function fixResidual(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `Transaction` SET `Amount` = `OriginalAmount`, `OriginalAmount` = 0 where `Status` = 'Open' and `OriginalAmount` <> 0 and `Status` <> 'Sold'";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("fixResidual: ".$sql, 'SQL_UPDATE', 0);
  newLogToSQL("fixResidual",$sql,3,0,"SQL","NA");
}

function updateSellAmount($TransactionID,$Amount,$oldAmount){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `Transaction` SET `Amount`= $Amount,`OriginalAmount` = $oldAmount WHERE `ID` = $TransactionID and `OriginalAmount` = 0";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateSellAmount: ".$sql, 'SQL_UPDATE', 0);
  newLogToSQL("updateSellAmount",$sql,3,1,"SQL","TransactionID:$TransactionID");
}

function getNewTrackingSellCoins($userID = 0){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
//12

  if ($userID <> 0){ $whereClause = " WHERE `UserID` = $userID and `StatusTsc` not in ('Closed','Cancelled')";}
  else{ $whereClause = " WHERE `StatusTsc` not in ('Closed','Cancelled')"; }

  $sql = "SELECT `CoinPrice`,`TrackDate`,`UserID`,`NoOfRisesInPrice`,`TransactionIDTsc`,`BuyRule`,`FixSellRule`,`OrderNo`,`Amount`,`CoinID`,`APIKey`,`APISecret`,`KEK`,`Email`,`UserName`
            ,`BaseCurrency`,`SendEmail`,`SellCoin`,`CoinSellOffsetEnabled`,`CoinSellOffsetPct`,`LiveCoinPrice`,`MinsFromDate`,`ProfitUSD`, `Fee`,`PctProfit` , `TotalRisesInPrice`, `Symbol`, `OgPctProfit`
            ,  `OriginalPurchasePrice`,`OriginalAmount` as `OriginalCoinPrice`,`TotalRisesInPriceSell`,`TrackStartDate`,`MinsFromStart`, `SellFallsInPrice`,`Type`,`BaseSellPrice`,`LastPrice`,`LiveTotalPrice`, `IDTsc` as `TrackingSellID`,`SaveResidualCoins`
            ,`OriginalAmount`,`TrackingType`,`OriginalSellPrice`,(`LiveCoinPrice`*`Amount`)-(`CoinPrice`*`Amount`) as `Profit`,((`LiveCoinPrice`*`Amount`)-(`CoinPrice`*`Amount`) )/(`CoinPrice`*`Amount`)*100 as `ProfitPct`
            ,`ReEnableBuyRuleEnabled`,`ReEnableBuyRule`,`BuyBackEnabled`,`TrackingCount`,`OverrideBuyBackAmount`,`OverrideBuyBackSaving`
            FROM `View6_TrackingSellCoins` $whereClause";
  //echo $sql;
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['CoinPrice'],$row['TrackDate'],$row['UserID'],$row['NoOfRisesInPrice'],$row['TransactionIDTsc'],$row['BuyRule'],$row['FixSellRule'],$row['OrderNo'],$row['Amount'] //8
    ,$row['CoinID'],$row['APIKey'],$row['APISecret'],$row['KEK'],$row['Email'],$row['UserName'],$row['BaseCurrency'],$row['SendEmail'],$row['SellCoin'],$row['CoinSellOffsetEnabled'],$row['CoinSellOffsetPct'] //19
    ,$row['LiveCoinPrice'],$row['MinsFromDate'],$row['ProfitUSD'],$row['Fee'],$row['PctProfit'],$row['TotalRisesInPrice'],$row['Symbol'],$row['OgPctProfit'],$row['OriginalPurchasePrice'],$row['OriginalCoinPrice'] //29
    ,$row['TotalRisesInPriceSell'],$row['TrackStartDate'],$row['MinsFromStart'],$row['SellFallsInPrice'], $row['Type'], $row['BaseSellPrice'], $row['LastPrice'], $row['LiveTotalPrice'], $row['TrackingSellID'] //38
    ,$row['SaveResidualCoins'], $row['OriginalAmount'], $row['TrackingType'], $row['OriginalSellPrice'], $row['Profit'], $row['ProfitPct'], $row['ReEnableBuyRuleEnabled'], $row['ReEnableBuyRule'], $row['BuyBackEnabled']//47
    ,$row['TrackingCount'],$row['OverrideBuyBackAmount'],$row['OverrideBuyBackSaving']); //50
  }
  $conn->close();
  return $tempAry;

}

function closeNewTrackingSellCoin($ID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `TrackingSellCoins` SET `Status` = 'Closed' WHERE `TransactionID` = $ID";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("closeNewTrackingSellCoin: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("closeNewTrackingSellCoin",$sql,3,1,"SQL","TrackingSellCoinsID:$ID");
}

function addBuyBackOverride($overrideBBAmount,$overrideBBSaving,$TransactionID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `TrackingSellCoins` SET `overrideBBAmount` = $overrideBBAmount, `overrideBBSaving` = $overrideBBSaving WHERE `TransactionID` = $TransactionID";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("addBuyBackOverride: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("addBuyBackOverride",$sql,3,1,"SQL","TransID:$TransactionID");
}

function setNewTrackingSellPrice($coinPrice, $ID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `TrackingSellCoins` SET `CoinPrice` = $coinPrice, `TrackDate` = CURRENT_TIMESTAMP() WHERE `TransactionID` = $ID";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("setNewTrackingSellPrice: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("setNewTrackingSellPrice",$sql,3,0,"SQL","TrackingSellCoinsID:$ID");
}

function updateNoOfRisesInSellPrice($newTrackingCoinID, $num, $price){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `TrackingSellCoins` SET `NoOfRisesInPrice`= $num, `CoinPrice` = $price WHERE `ID` = $newTrackingCoinID ";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateNoOfRisesInSellPrice: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("updateNoOfRisesInSellPrice",$sql,3,0,"SQL","TrackingSellCoinsID:$newTrackingCoinID");
}

function reopenTransaction($id){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `Transaction`
            SET `Status`= CASE
             WHEN `Type` = 'SpreadSell' THEN 'Open'
             WHEN `Type` = 'Sell' THEN 'Open'
             WHEN `Type` = 'SavingsSell' THEN 'Saving'
             END
            WHERE `ID` = $id";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("reopenTransaction: ".$sql, 'SQL_UPDATE', 0);
  newLogToSQL("reopenTransaction",$sql,3,0,"SQL","TransactionID:$id");
}

function getReservedAmount($baseCurrency, $userID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
//12

  $sql = "select (
            SELECT ifnull(sum(`CoinPrice`),0)
            from `TrackingCoins` where `BaseCurrency` = 'USDT' and `UserID` = $userID and `Status` = 'Open') as TotalCoinPriceUSDT
            ,ifnull((SELECT sum(`CoinPrice` )
            from `TrackingCoins` where `BaseCurrency` = 'BTC' and `UserID` = $userID and `Status` = 'Open'),0)as TotalCoinPriceBTC
            ,ifnull((SELECT sum(`CoinPrice` )
            from `TrackingCoins` where `BaseCurrency` = 'ETH' and `UserID` = $userID and `Status` = 'Open'),0)as TotalCoinPriceETH

            ,ifnull((SELECT sum(`Quantity` )
            from `TrackingCoins` where `BaseCurrency` = 'USDT' and `UserID` = $userID and `Status` = 'Open'),0)as TotalQuantityUSDT
            ,ifnull((SELECT sum(`Quantity` )
            from `TrackingCoins` where `BaseCurrency` = 'BTC' and `UserID` = $userID and `Status` = 'Open'),0)as TotalQuantityBTC
            ,ifnull((SELECT sum(`Quantity` )
            from `TrackingCoins` where `BaseCurrency` = 'ETH' and `UserID` = $userID and `Status` = 'Open'),0)as TotalQuantityETH";
  //echo $sql;
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['TotalCoinPriceUSDT'],$row['TotalCoinPriceBTC'],$row['TotalCoinPriceETH'],$row['TotalQuantityUSDT'],$row['TotalQuantityBTC'],$row['TotalQuantityETH']);
  }
  $conn->close();
  return $tempAry;
}

function getBasePrices(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
//12

  $sql = "select getBTCPrice(84) as BTCPrice
          , getBTCPrice(85) as ETHPrice ";
  //echo $sql;
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['BTCPrice'],$row['ETHPrice']);
  }
  $conn->close();
  return $tempAry;
}

function getUserDisabled($userID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
//12

  $sql = "SELECT `DisableUntil` FROM `User` WHERE `ID` = $userID";
  //echo $sql;
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['DisableUntil']);
  }
  $conn->close();
  return $tempAry;
}

function logHoldingTimeToSQL($coinID, $holdingMins){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "INSERT INTO `AverageCoinHolingTime`(`CoinID`, `MinsHolding`) VALUES ($coinID, $holdingMins)";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("logHoldingTimeToSQL: ".$sql, 'SellCoins', 0);
}

function cancelTrackingSell($id){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  //$sql = "UPDATE `TrackingSellCoins` SET `Status`= 'Closed' WHERE `TransactionID` = $id";
  $sql = "call cancelTrackingSellUpdateSBTransID($id);";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("cancelTrackingSell: ".$sql, 'SQL_CALL', 0);
  newLogToSQL("cancelTrackingSell",$sql,3,0,"SQL","TransactionID:$id");
}

function getNewTrackingSellCoinTrans($ID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
//12
  //$whereClause = "";
  //if ($userID <> 0){ $whereClause = " WHERE `UserID` = $userID";}
  $sql = "SELECT `CoinPrice`,`TrackDate`,`UserID`,`NoOfRisesInPrice`,`TransactionID`,`BuyRule`,`FixSellRule`,`OrderNo`,`Amount`,`CoinID`,`APIKey`,`APISecret`,`KEK`,`Email`,`UserName`,`BaseCurrency`
  ,`SendEmail`,`SellCoin`,`CoinSellOffsetEnabled`,`CoinSellOffsetPct`,`LiveCoinPrice`,TIMESTAMPDIFF(MINUTE,  NOW(),`TrackDate`) as MinsFromDate, `ProfitUSD`, `Fee`
  , (`LiveSellPrice`-`OriginalPurchasePrice`)/ `OriginalPurchasePrice` * 100 as `PctProfit`
  , `TotalRisesInPrice`, `Symbol`
  , (`LiveSellPrice`-(`OriginalCoinPrice` * `Amount`))/ (`OriginalCoinPrice` * `Amount`) * 100 as `OgPctProfit`, `OriginalPurchasePrice`,`OriginalCoinPrice`,`TotalRisesInPriceSell`
  FROM `TrackingSellCoinView` where `TransactionID` = $ID";
  //echo $sql;
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['CoinPrice'],$row['TrackDate'],$row['UserID'],$row['NoOfRisesInPrice'],$row['TransactionID'],$row['BuyRule'],$row['FixSellRule'],$row['OrderNo'],$row['Amount'] //8
    ,$row['CoinID'],$row['APIKey'],$row['APISecret'],$row['KEK'],$row['Email'],$row['UserName'],$row['BaseCurrency'],$row['SendEmail'],$row['SellCoin'],$row['CoinSellOffsetEnabled'],$row['CoinSellOffsetPct'] //19
    ,$row['LiveCoinPrice'],$row['MinsFromDate'],$row['ProfitUSD'],$row['Fee'],$row['PctProfit'],$row['TotalRisesInPrice'],$row['Symbol'],$row['OgPctProfit'],$row['OriginalPurchasePrice'],$row['OriginalCoinPrice']
    ,$row['TotalRisesInPriceSell']);
  }
  $conn->close();
  return $tempAry;

}

function setMobileVariables(){
  if ($_SESSION['MobOverride'] == False && $_SESSION['isMobile']){
    $_SESSION['MobDisplay'] = 0;
  }elseif ($_SESSION['MobOverride']){
    $_SESSION['MobDisplay'] = 2;
  }
}

function updateSQLQuantity($uuid, $quantity){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `Transaction` SET `Amount` = $quantity WHERE `BittrexRef` = '$uuid'";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateSQLQuantity: ".$sql, 'SQL_UPDATE', 0);
  newLogToSQL("updateSQLQuantity",$sql,3,1,"SQL","BittrexRef:$uuid");
}

function getCoinPriceMatchNames($userID, $table, $limit){
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Name`,`ID` FROM $table $limit";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Name'],$row['ID']);
  }
  $conn->close();
  return $tempAry;
}

function getTotalProfit(){
  $conn = getSQLConn(rand(1,3));
  $tempAry = [];
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT sum(`LiveCoinPrice` * `Amount`) as TotalLivePrice,sum(`CoinPrice` * `Amount`) as TotalPurchasePrice, sum((`LiveCoinPrice` * `Amount`)-(`CoinPrice` * `Amount`)) as TotalProfit
        ,(sum((`LiveCoinPrice` * `Amount`)-(`CoinPrice` * `Amount`))/ sum(`CoinPrice` * `Amount`))*100 as ProfitPct
        ,`UserID`
        FROM `View15_OpenTransactions` WHERE `StatusTr` in ('Open','Pending')
        group by `UserID`";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['TotalLivePrice'],$row['TotalPurchasePrice'],$row['TotalProfit'],$row['ProfitPct'],$row['UserID']);
  }
  $conn->close();
  return $tempAry;
}

function findUserProfit($userProfit, $userID){
  if (!is_null($userProfit)){
    $userProfitSize = count($userProfit);
    for ($i=0; $i<$userProfitSize; $i++){
      if ($userProfit[$i][4] == $userID){
        //echo "<BR> Return User Profit: ".$userProfit[$i][3];
        return $userProfit[$i][3];
      }
    }
  }
}

function pauseRule($id, $hours, $userID = 0){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $whereClause = "";
  if ($hours == 0){ $dateClause = "DATE_ADD(now(),interval 24 hour)";}else{ $dateClause = "DATE_ADD(now(),interval $hours hour)";}
  if ($userID <> 0){ $whereClause = " and `UserID` = $userID ";}

  $sql = "UPDATE `BuyRules` SET `DisableUntil`= $dateClause
          WHERE `ID` in ($id) $whereClause and `OverrideDisableRule` = 0";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("pauseRule: ".$sql, 'BuyCoin', 0);
  newLogToSQL("pauseRule",$sql,3,0,"SQL","BuyRuleID:$id");
}

function getDailyBalance($apikey,$apisecret){
  $timestamp = time()*1000;
  $url = "https://api.bittrex.com/v3/balances/";
  $method = "GET";
  $content = "";
  $subaccountId = "";
  $contentHash = hash('sha512', $content);
  $preSign = $timestamp . $url . $method . $contentHash . $subaccountId;
  $signature = hash_hmac('sha512', $preSign, $apisecret);

  $headers = array(
  "Accept: application/json",
  "Content-Type: application/json",
  "Api-Key: ".$apikey."",
  "Api-Signature: ".$signature."",
  "Api-Timestamp: ".$timestamp."",
  "Api-Content-Hash: ".$contentHash.""
  );

  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
  curl_setopt($ch, CURLOPT_HEADER, FALSE);
  $execResult = curl_exec($ch);
  curl_close($ch);
  $temp = json_decode($execResult, true);
  echo "<BR>getDailyBalance :";
  var_dump($temp);
  return $temp;
}

function updateBittrexBalances($symbol, $total, $price, $userID){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $sql = "Call AddNewBittrexBal('$symbol',$total,$price, $userID);";
    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("updateBittrexBalances","$sql",3,0,"SQL CALL","UserID:$userID");
}

function deleteBittrexBalances(){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $sql = "Delete FROM `BittrexBalances`";
    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("deleteBittrexBalances","$sql",3,sQLUpdateLog,"SQL CALL","UserID:$userID");
}

function addUSDTBalance($symbol, $usdtPurchase, $price, $userID){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $sql = "Call UpdateUSDTBal('$symbol',$usdtPurchase, $userID, $price);";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("addUSDTBalance","$sql",3,sQLUpdateLog,"SQL CALL","UserID:$userID");
}

function subUSDTBalance($symbol, $usdtPurchase, $price, $userID){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $sql = "Call subUSDTBal('$symbol',$usdtPurchase, $userID, $price);";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
    newLogToSQL("subUSDTBalance","$sql",3,sQLUpdateLog,"SQL CALL","UserID:$userID");
}

function pauseTracking($userID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `TrackingCoins` SET `BuyCoin` = 0 WHERE `Status` = 'Open' and `UserID` = $userID";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("pauseTracking: ".$sql, 'BuyCoin', 0);
  newLogToSQL("pauseTracking",$sql,3,0,"SQL","UserID:$userID");
}

function getMarketProfit(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT sum(`LiveCoinPrice`-`LastCoinPrice`)/sum(`LastCoinPrice`)*100  as `5MinProfitPct`
          ,sum(`LiveCoinPrice`-`Live1HrChange`)/sum(`Live1HrChange`)*100 as `1HrProfitPct` FROM `View1_BuyCoins` ";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['5MinProfitPct'],$row['1HrProfitPct']);
  }
  $conn->close();
  return $tempAry;
}

function getLiveMarketPrice($status){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `IDCn`,'Symbol'
    ,sum(`LiveBuyOrders`) as LiveBuyOrders,sum(`LastBuyOrders`) as LastBuyOrders,(sum(`LiveBuyOrders`-`LastBuyOrders`)/ sum(`LastBuyOrders`) * 100) as`BuyOrdersPctChange`
    ,sum(`LiveMarketCap`) as LiveMarketCap ,sum(`LastMarketCap`) as LastMarketCap,(sum(`LiveMarketCap`-`LastMarketCap`)/ sum(`LastMarketCap`)*100) as `MarketCapPctChange`
    ,sum(`Live1HrChange`) as Live1HrChange,sum(`Last1HrChange`) as `Last1HrChange`,(sum(`Live1HrChange`-`Last1HrChange`)/ sum(`Last1HrChange`)*100) as `Hr1ChangePctChange`
    ,sum(`Live24HrChange`) as `Live24HrChange`,sum(`Last24HrChange`) as `Last24HrChange`,(sum(`Live24HrChange`-`Last24HrChange`)/ sum(`Last24HrChange`)*100) as `Hr24ChangePctChange`
    ,sum(`Live7DChange`) as `Live7DChange`,sum(`Last7DChange`) as `Last7DChange`,(sum(`Live7DChange`-`Last7DChange`)/ sum(`Last7DChange`)*100) as `D7ChangePctChange`
    ,sum(`LiveCoinPrice`) as `LiveCoinPrice`,sum(`LastCoinPrice`) as `LastCoinPrice`,(sum(`LiveCoinPrice`-`LastCoinPrice`)/ sum(`LastCoinPrice`)*100) as `CoinPricePctChange`
    ,sum(`LiveSellOrders`) as `LiveSellOrders`,sum(`LastSellOrders`) as `LastSellOrders`,(sum(`LiveSellOrders`-`LastSellOrders`)/ sum(`LastSellOrders`)*100) as  `SellOrdersPctChange`
    ,sum(`LiveVolume`) as `LiveVolume`,sum(`LastVolume`) as `LastVolume`,(sum(`LiveVolume`-`LastVolume`)/ sum(`LastVolume`)*100) as `VolumePctChange`
    ,`BaseCurrency`
   ,`Price4Trend`, `Price3Trend`, `LastPriceTrend`, `LivePriceTrend`
   ,`Name`, 'Hr1BuyPrice', 'Hr24BuyPrice', 'D7BuyPrice',`BuyCoin`,'BullBearStatus'
   FROM `View1_BuyCoins`  WHERE `BuyCoin` = $status
   order by `Hr1ChangePctChange`+`Hr24ChangePctChange`+`D7ChangePctChange`asc";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['Symbol'],$row['LiveBuyOrders'],$row['LastBuyOrders'],$row['BuyOrdersPctChange'],$row['LiveMarketCap'],$row['LastMarketCap'],$row['MarketCapPctChange'],$row['Live1HrChange'] //8
      ,$row['Last1HrChange'],$row['Hr1ChangePctChange'],$row['Live24HrChange'],$row['Last24HrChange'],$row['Hr24ChangePctChange'],$row['Live7DChange'],$row['Last7DChange'],$row['D7ChangePctChange'],$row['LiveCoinPrice'] //17
      ,$row['LastCoinPrice'],$row['CoinPricePctChange'],$row['LiveSellOrders'],$row['LastSellOrders'],$row['SellOrdersPctChange'],$row['LiveVolume'],$row['LastVolume'],$row['VolumePctChange'],$row['BaseCurrency'] //26
    ,$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['Name'],$row['Hr1BuyPrice'],$row['Hr24BuyPrice'],$row['D7BuyPrice'],$row['Enabled'],$row['BullBearStatus']);
  }
  $conn->close();
  return $tempAry;
}

function writeMarketPrice($price){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "INSERT INTO `MarketPriceChange`(`MarketPrice`) VALUES ($price)";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("writeMarketPrice: ".$sql, 'SQL_UPDATE', 0);
  newLogToSQL("writeMarketPrice",$sql,3,0,"SQL","M_Price:$price");
}

function getRuleProfit(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT sum(`LiveCoinPrice` * `Amount`) as `TotalLivePrice`, sum(`CoinPrice` * `Amount`) as `TotalPurchasePrice`
        ,sum((`LiveCoinPrice` * `Amount`) - `CoinPrice` * `Amount`) as `TotalProfit`
        ,(sum((`LiveCoinPrice` * `Amount`) - `CoinPrice` * `Amount`) /sum(`CoinPrice` * `Amount`)) * 100 as `ProfitPct`
        ,`BuyRule`, count(`BuyRule`) as `RuleIDCount`
        FROM `View15_OpenTransactions` WHERE `StatusTr` in ('Open','Pending')
        group by `BuyRule` ";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['TotalLivePrice'],$row['TotalPurchasePrice'],$row['TotalProfit'],$row['ProfitPct'],$row['BuyRule'],$row['RuleIDCount']);
  }
  $conn->close();
  return $tempAry;
}

function assignNewSellID($transID, $sellRuleID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `Transaction` SET `FixSellRule` = $sellRuleID WHERE `Status` = 'Open' and `ID` = $transID";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("assignNewSellID: ".$sql, 'SQL_UPDATE', 0);
  newLogToSQL("assignNewSellID",$sql,3,0,"SQL","TransactionID:$transID");
}

function setTransStatus($status,$transID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `Transaction` SET `Status` = '$status', `SpreadBetRuleID` = 0,`SpreadBetTransactionID` = 0, `Type` = 'Sell'
          WHERE `ID` = $transID";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("setTransStatus: ".$sql, 'SQL_UPDATE', 0);
  newLogToSQL("setTransStatus",$sql,3,0,"SQL","TransactionID:$transID");
}

function setCustomisedSellRule($buyRule, $coinID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "Call CustomisedSellRule($buyRule,$coinID);";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("setCustomisedSellRule: ".$sql, 'SellCoin', 0);
  newLogToSQL("setCustomisedSellRule","$sql",3,sQLUpdateLog,"SQL CALL","CoinID:$coinID RuleID:$buyRule");
}

function setCustomisedSellRuleBased($buyRule, $coinID, $pctToSell){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "call CustomisedSell_RuleBased($coinID,$buyRule,$pctToSell);";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("setCustomisedSellRuleBased: ".$sql, 'SellCoin', 0);
  newLogToSQL("setCustomisedSellRuleBased","$sql",3,sQLUpdateLog,"SQL CALL","CoinID:$coinID RuleID:$buyRule");
}

function coinSwapBuyModeLookup($coinID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `CoinID` FROM `CoinModeRules` WHERE `CoinID` != $coinID and `ModeID` = 1  limit 1 ";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['CoinID']);
  }
  $conn->close();
  return $tempAry;
}

function coinSwapSell($livePrice, $transactionID,$coinID,$buyRule, $buyAmount){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "Call CoinSwapSell($livePrice, $transactionID,$coinID,$buyRule, $buyAmount);";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("coinSwapSell: ".$sql, 'SQL_CALL', 0);
  newLogToSQL("coinSwapSell","$sql",3,sQLUpdateLog,"SQL CALL","CoinID:$coinID TransactionID:$transactionID");
}

function getCoinMode($userID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `RuleID`, `CoinID`, `LiveCoinPrice`, `Avg6MonthMax`, `Avg6MonthMin`, `0MinsMin`, `15MinsMin`, `30MinsMin`, `45MinsMin`, `0MinsMax`, `15MinsMax`, `30MinsMax`
  , `45MinsMax`, `Live1HrChange`, `Last1HrChange`, `Live24HrChange`, `Last24HrChange`, `Live7DChange`, `Last7DChange`, `RuleIDSell`, `USDBuyAmount`, `1HourAvgPrice`, `ProjectedPriceMax`
  , `ProjectedPriceMin`, `UserID`, `ModeID`, `Hr1Top`, `Hr1Btm`, `Hr24Top`, `Hr24Btm`, `D7Top`, `D7Btm`, `SecondarySellRules`, `CoinModeEmails`, `Email`, `UserName`, `Symbol`
  , `CoinModeEmailsSell`, `CoinModeMinsToCancelBuy`,`PctToBuy`,`PctOfAllTimeHigh`,`1HrChange` FROM `CoinModePricesView` WHERE `UserID` = $userID ";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['RuleID'],$row['CoinID'],$row['LiveCoinPrice'],$row['Avg6MonthMax'],$row['Avg6MonthMin'],$row['0MinsMin'],$row['15MinsMin'],$row['30MinsMin'],$row['45MinsMin'],$row['0MinsMax'],$row['15MinsMax'] //10
      ,$row['30MinsMax'],$row['45MinsMax'],$row['Live1HrChange'],$row['Last1HrChange'],$row['Live24HrChange'],$row['Last24HrChange'],$row['Live7DChange'],$row['Last7DChange'],$row['RuleIDSell'],$row['USDBuyAmount'] //20
      ,$row['1HourAvgPrice'],$row['ProjectedPriceMax'],$row['ProjectedPriceMin'],$row['UserID'],$row['ModeID'],$row['Hr1Top'],$row['Hr1Btm'],$row['Hr24Top'],$row['Hr24Btm'],$row['D7Top'],$row['D7Btm'],$row['SecondarySellRules'] //32
      ,$row['CoinModeEmails'],$row['Email'],$row['UserName'],$row['Symbol'],$row['CoinModeEmailsSell'],$row['CoinModeMinsToCancelBuy'],$row['PctToBuy'],$row['PctOfAllTimeHigh'],$row['1HrChange']);
  }
  $conn->close();
  return $tempAry;
}

function updateBuyAmount($transactionID, $amount){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `Transaction` SET `Amount` = $amount WHERE `ID` = $transactionID";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateBuyAmount: ".$sql, 'SQL_UPDATE', 0);
  newLogToSQL("updateBuyAmount",$sql,3,1,"SQL","TransactionID:$transactionID");
}

function cancelTrackingBuy($ruleId,$reason){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `TrackingCoins` SET `Status` = 'Closed' where `RuleIDBuy` = $ruleId";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("cancelTrackingBuy: ".$sql, 'BuyCoin', 0);
  newLogToSQL("cancelTrackingBuy",$sql." Reason: $reason",3,1,"SQL","BuyRuleID:$ruleId");
}

function UpdateProfit(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "call UpdateNewUserProfit();";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("UpdateProfit: ".$sql, 'SQL_CALL', 0);
  newLogToSQL("UpdateProfit","$sql",3,sQLUpdateLog,"SQL CALL","All Users");
}

function updateTypeToBittrex($type,$transID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `BittrexAction` SET `Type` = '$type' where `TransactionID` = $transID ";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateTypeToBittrex: ".$sql, 'BuyCoin', 0);
  newLogToSQL("updateTypeToBittrex","$sql",3,1,"SQL CALL","All Users");
}

function updateTypeToTrans($type,$transID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `Transaction` SET `Type` = '$type' where `ID` = $transID ";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateTypeToBittrex: ".$sql, 'SQL_UPDATE', 0);
  newLogToSQL("updateTypeToBittrex","$sql",3,0,"SQL CALL","All Users");
}

function UpdateSpreadBetTotalProfit(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "call UpdateSpreadBetTotalProfit();";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("UpdateSpreadBetTotalProfit: ".$sql, 'SQL_CALL', 0);
  newLogToSQL("UpdateSpreadBetTotalProfit","$sql",3,sQLUpdateLog,"SQL CALL","All Users");
}

function extendPctToBuy($coinID, $userID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "Call AddToPct($coinID,$userID, 0.24);";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("extendPctToBuy: ".$sql, 'SellCoin', 0);
  newLogToSQL("extendPctToBuy","$sql",3,sQLUpdateLog,"SQL CALL","UserID:$userID CoinID:$coinID");
}

function getSpreadBetData(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `IDCn`, `Name`, `Live1HrChange`, `Last1HrChange`, ((`Live1HrChange`-`Last1HrChange`)/`Last1HrChange`)*100 as `Hr1ChangePctChange`, `Live24HrChange`, `Last24HrChange`, ((`Live24HrChange`-`Last24HrChange`)/`Last24HrChange`)*100 as `Hr24ChangePctChange`
  , `Live7DChange`, `Last7DChange`
          , ((`Live7DChange`-`Last7DChange`)/`Last7DChange`)*100 as `D7ChangePctChange`, `LiveCoinPrice`, `LastCoinPrice`, ((`LiveCoinPrice`-`LastCoinPrice`)/`LastCoinPrice`)*100 as `CoinPricePctChange`,  `BaseCurrency`
          , if(`Price4` -`Price5` > 0, 1, if(`Price4` -`Price5` < 0, -1, 0)) as  `Price4Trend`
          , if(`Price3` -`Price4` > 0, 1, if(`Price3` -`Price4` < 0, -1, 0)) as  `Price3Trend`
          , if(`LastCoinPrice` -`Price3` > 0, 1, if(`LastCoinPrice` -`Price3` < 0, -1, 0)) as  `LastPriceTrend`
          , if(`LiveCoinPrice` -`LastCoinPrice` > 0, 1, if(`LiveCoinPrice` -`LastCoinPrice` < 0, -1, 0)) as  `LivePriceTrend`
          , 'AutoBuyPrice'
          , '1HrPriceChangeLive', '1HrPriceChangeLast', '1HrPriceChange3', '1HrPriceChange4',`APIKey`,`APISecret`,`KEK`,`IDUs` as UserID,`Email`,`UserName`,`IDsbt` as `SpreadBetTransID`, `Hr1BuyPrice`, `Hr24BuyPrice`
          , `D7BuyPrice`,(`LiveCoinPrice`-(SELECT MAX(`MaxPrice`) FROM `MonthlyMaxPrices` WHERE `CoinID` = `CoinID` and DATE(CONCAT_WS('-', `Year`, `Month`, 01)) > DATE_SUB(now(), INTERVAL 6 MONTH))/(SELECT MAX(`MaxPrice`)
          FROM `MonthlyMaxPrices` WHERE `CoinID` = 84 and DATE(CONCAT_WS('-', `Year`, `Month`, 01)) > DATE_SUB(now(), INTERVAL 6 MONTH)))
         as `PctofSixMonthHighPrice`,((`LiveCoinPrice`-`PriceAth`)/`PriceAth`)*100 as `PctofAllTimeHighPrice`,`DisableUntil`,`IDUs`,`CalculatedFallsinPrice`,`CalculatedMinsToCancel`,`LowMarketModeEnabled`
         FROM `View3_SpreadBetBuy`
          where ((`Live24HrChange`-`Last24HrChange`)/`Last24HrChange`)*100  < 0 and  ((`Live7DChange`-`Last7DChange`)/`Last7DChange`)*100 < 0 ";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['IDCn'], $row['Name'], $row['Live1HrChange'], $row['Last1HrChange'], $row['Hr1ChangePctChange'], $row['Live24HrChange'], $row['Last24HrChange'], $row['Hr24ChangePctChange'], $row['Live7DChange'], $row['Last7DChange']//9
      , $row['D7ChangePctChange'], $row['LiveCoinPrice'], $row['LastCoinPrice'], $row['CoinPricePctChange'], $row['BaseCurrency'], $row['Price4Trend'], $row['Price3Trend'], $row['LastPriceTrend'], $row['LivePriceTrend'], $row['AutoBuyPrice']//19
      , $row['1HrPriceChangeLive'], $row['1HrPriceChangeLast'], $row['1HrPriceChange3'], $row['1HrPriceChange4'], $row['APIKey'], $row['APISecret'], $row['KEK'], $row['UserID'], $row['Email'], $row['UserName'], $row['SpreadBetTransID'] //30
      , $row['Hr1BuyPrice'], $row['Hr24BuyPrice'], $row['D7BuyPrice'], $row['PctofSixMonthHighPrice'], $row['PctofAllTimeHighPrice'], $row['DisableUntil'], $row['IDUs'], $row['CalculatedFallsinPrice'], $row['CalculatedMinsToCancel']//39
    , $row['LowMarketModeEnabled']);
  }
  $conn->close();
  return $tempAry;
}

function getSpreadBetDataFixed(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `ID`, `Name`, 'Live1HrChange', 'Last1HrChange', 'Hr1ChangePctChange', 'Live24HrChange', 'Last24HrChange', 'Hr24ChangePctChange', 'Live7DChange', 'Last7DChange'
  , 'D7ChangePctChange', 'LiveCoinPrice', 'LastCoinPrice', 'CoinPricePctChange',  `BaseCurrency`, 'Price4Trend', 'Price3Trend', 'LastPriceTrend', 'LivePriceTrend', 'AutoBuyPrice'
  , '1HrPriceChangeLive', '1HrPriceChangeLast', '1HrPriceChange3', '1HrPriceChange4',`APIKey`,`APISecret`,`KEK`,`UserID`,`Email`,`UserName`,`SpreadBetTransID`, 'Hr1BuyPrice', 'Hr24BuyPrice'
  , 'D7BuyPrice',`PctofSixMonthHighPrice`,`PctofAllTimeHighPrice`,`DisableUntil`,`UserID`,`CalculatedFallsinPrice`,`CalculatedMinsToCancel` FROM `SpreadBetCoinStatsView` ";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'], $row['Name'], $row['Live1HrChange'], $row['Last1HrChange'], $row['Hr1ChangePctChange'], $row['Live24HrChange'], $row['Last24HrChange'], $row['Hr24ChangePctChange'], $row['Live7DChange'], $row['Last7DChange']//9
      , $row['D7ChangePctChange'], $row['LiveCoinPrice'], $row['LastCoinPrice'], $row['CoinPricePctChange'], $row['BaseCurrency'], $row['Price4Trend'], $row['Price3Trend'], $row['LastPriceTrend'], $row['LivePriceTrend'], $row['AutoBuyPrice']//19
      , $row['1HrPriceChangeLive'], $row['1HrPriceChangeLast'], $row['1HrPriceChange3'], $row['1HrPriceChange4'], $row['APIKey'], $row['APISecret'], $row['KEK'], $row['UserID'], $row['Email'], $row['UserName'], $row['SpreadBetTransID'] //30
      , $row['Hr1BuyPrice'], $row['Hr24BuyPrice'], $row['D7BuyPrice'], $row['PctofSixMonthHighPrice'], $row['PctofAllTimeHighPrice'], $row['DisableUntil'], $row['UserID'], $row['CalculatedFallsinPrice'], $row['CalculatedMinsToCancel']);
  }
  $conn->close();
  return $tempAry;
}

function getSpreadCoinData($ID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `ID`, `Symbol`, `LiveBuyOrders`, `LastBuyOrders`, `BuyOrdersPctChange`, `LiveMarketCap`, `LastMarketCap`, `MarketCapPctChange`, `Live1HrChange`, `Last1HrChange`, `Hr1ChangePctChange`, `Live24HrChange`, `Last24HrChange`, `Hr24ChangePctChange`
  , `Live7DChange`, `Last7DChange`, `D7ChangePctChange`, `LiveCoinPrice`, `LastCoinPrice`, `CoinPricePctChange`, `LiveSellOrders`, `LastSellOrders`, `SellOrdersPctChange`, `LiveVolume`, `LastVolume`, `VolumePctChange`, `BaseCurrency`, `Price4Trend`
  , `Price3Trend`, `LastPriceTrend`, `LivePriceTrend`, `AutoBuyPrice`, `1HrPriceChangeLive`, `1HrPriceChangeLast`, `1HrPriceChange3`, `1HrPriceChange4`, `CMCID`, `SecondstoUpdate`, `LastUpdated`, `Name`, `Image`, `SpreadBetRuleID`,`SpreadBetTransactionID`
  FROM `SpreadBetCoinStatsCoinView` where `SpreadBetRuleID` = $ID";
  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'], $row['Symbol'], $row['LiveBuyOrders'], $row['LastBuyOrders'], $row['BuyOrdersPctChange'], $row['LiveMarketCap'], $row['LastMarketCap'], $row['MarketCapPctChange'], $row['Live1HrChange'], $row['Last1HrChange'] //9
      , $row['Hr1ChangePctChange'], $row['Live24HrChange'], $row['Last24HrChange'], $row['Hr24ChangePctChange'], $row['Live7DChange'], $row['Last7DChange'], $row['D7ChangePctChange'], $row['LiveCoinPrice'], $row['LastCoinPrice'], $row['CoinPricePctChange'] //19
      , $row['LiveSellOrders'], $row['LastSellOrders'], $row['SellOrdersPctChange'], $row['LiveVolume'], $row['LastVolume'], $row['VolumePctChange'], $row['BaseCurrency'], $row['Price4Trend'], $row['Price3Trend'], $row['LastPriceTrend'] //29
      , $row['LivePriceTrend'], $row['AutoBuyPrice'], $row['1HrPriceChangeLive'], $row['1HrPriceChangeLast'], $row['1HrPriceChange3'], $row['1HrPriceChange4'], $row['CMCID'], $row['SecondstoUpdate'], $row['LastUpdated'], $row['Name'], $row['Image']  //40
      , $row['SpreadBetRuleID'], $row['SpreadBetTransactionID']);
  }
  $conn->close();
  return $tempAry;
}

function SpreadBetBittrexCancelPartialSell($oldID,$coinID, $quantity){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
    $sql = "UPDATE `Transaction` AS `t1` JOIN `Transaction` AS `t2` ON `t2`.`ID` = $oldID
            SET    `t1`.`Type` = 'SpreadSell'
            , `t1`.`SpreadBetRuleID` = `t2`.`SpreadBetRuleID`
            , `t1`.`SpreadBetTransactionID` = `t2`.`SpreadBetTransactionID`
            WHERE `t1`.`CoinID` = $coinID and `t1`.`Amount` = $quantity
            order by `ID` Desc
            limit 1 ";
    LogToSQL("SpreadBetBittrexCancelPartialSell",$sql,3,1);
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("SpreadBetBittrexCancelPartialSell: ".$sql, 'SQL_UPDATE', 0);
  newLogToSQL("SpreadBetBittrexCancelPartialSell",$sql,3,0,"SQL","TransactionID:$oldID");
}

function SpreadBetBittrexCancelPartialBuy($transactionID,$quantity){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
    $sql = "UPDATE `Transaction` SET `Amount` =  $quantity where `ID` = $transactionID";
    LogToSQL("SpreadBetBittrexCancelPartialBuy",$sql,3,0);
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("SpreadBetBittrexCancelPartialBuy: ".$sql, 'SQL_UPDATE', 0);
  newLogToSQL("SpreadBetBittrexCancelPartialBuy",$sql,3,0,"SQL","TransactionID:$transactionID");
}

function updateTransToSpread($SBRuleID,$coinID, $userID,$SBTransID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "Call UpdateTransToSpread($SBRuleID,$coinID,$userID,$SBTransID);";
    LogToSQL("updateTransToSpread",$sql,3,1);
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateTransToSpread: ".$sql, 'SQL_CALL', 0);
  newLogToSQL("updateTransToSpread","$sql",3,sQLUpdateLog,"SQL CALL","UserID:$userID CoinID:$coinID");
}

function writeCalculatedSellPct($transID, $userID,$sellPct,$ruleIDSell){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "Call writeCalculatedSellPct($transID, $userID,$sellPct,$ruleIDSell);";
    //LogToSQL("updateTransToSpread",$sql,3,1);
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("writeCalculatedSellPct: ".$sql, 'SQL_CALL', 0);
  newLogToSQL("writeCalculatedSellPct","$sql",3,0,"SQL CALL","UserID:$userID TransID:$transID");
}

function getCoinAllocation($userID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `RuleBasedAvailable`,`CoinModeAvailable`,`SpreadBetAvailable`,`ID` FROM `CoinAmountsAvailableToBuy`
          where `ID` = $userID";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['RuleBasedAvailable'],$row['CoinModeAvailable'],$row['SpreadBetAvailable'],$row['ID']);
  }
  $conn->close();
  return $tempAry;
}

function updateToSpreadSell($transID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "UPDATE `Transaction` SET `Type`= 'SpreadSell' WHERE  `ID` = $transID";
    LogToSQL("updateToSpreadSell",$sql,3,1);
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateToSpreadSell: ".$sql, 'SQL_UPDATE', 0);
  newLogToSQL("updateToSpreadSell",$sql,3,0,"SQL","TransactionID:$transID");
}

function getSpreadBetSellData($ID = 0){
  $tempAry = [];
  $whereClause = "Where `Status` in ('Open','Pending','Sold') and `Type` = 'SpreadSell'";
  if ($ID <> 0) { $whereClause = " Where `UserID` = $ID and `Status` in ('Open','Pending','Sold') and `Type` = 'SpreadSell'";}
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `ID`, `Type`, `UserID`, sum(`CoinPrice`) as `CoinPrice`, sum(`Amount`) as `Amount`, `Status`, `OrderDate`, `CompletionDate`, sum(`LastBuyOrders`) as `LastBuyOrders`, sum(`LiveBuyOrders`) as `LiveBuyOrders`
  ,((sum(`LiveBuyOrders`-`LastBuyOrders`))/sum(`LastBuyOrders`))*100 as `BuyOrdersPctChange`, sum(`LastMarketCap`) as `LastMarketCap` , sum(`LiveMarketCap`) as `LiveMarketCap`
  ,((sum(`LiveMarketCap`-`LastMarketCap`))/sum(`LastMarketCap`))*100 as `MarketCapPctChange`, sum(`LastCoinPrice`) as `LastCoinPrice`, sum(`LiveCoinPrice`) as `LiveCoinPrice`
  , ((sum(`LiveCoinPrice`-`LastCoinPrice`))/sum(`LastCoinPrice`))*100 as `CoinPricePctChange`, sum(`LastSellOrders`) as `LastSellOrders`, sum(`LiveSellOrders`) as `LiveSellOrders`
  ,((sum(`LiveSellOrders`-`LastSellOrders`))/sum(`LastSellOrders`))*100 as `SellOrdersPctChange`, sum(`LastVolume`) as `LastVolume`, sum(`LiveVolume`) as `LiveVolume`
  , ((sum(`LiveVolume`-`LastVolume`))/sum(`LastVolume`))*100 as `VolumePctChange`, sum(`Last1HrChange`) as `Last1HrChange`, sum(`Live1HrChange`) as `Live1HrChange`
  , ((sum(`Live1HrChange`-`Last1HrChange`))/sum(`Last1HrChange`))*100 as `Hr1PctChange`, sum(`Last24HrChange`) as `Last24HrChange`, sum(`Live24HrChange`) as `Live24HrChange`
  , ((sum(`Live24HrChange`-`Last24HrChange`))/sum(`Last24HrChange`))*100 as `Hr24PctChange`, sum(`Last7DChange`) as `Last7DChange`, sum(`Live7DChange`) as `Live7DChange`
  , ((sum(`Live7DChange`-`Last7DChange`))/sum(`Last7DChange`))*100 as `D7PctChange`, `BaseCurrency`, 'AutoSellPrice'
  ,if(sum(`LiveCoinPrice`-`LastCoinPrice`) > 0, 1, if(sum(`LiveCoinPrice`-`LastCoinPrice`) < 0, -1, 0)) as  `LivePriceTrend`
  ,if(sum(`LastCoinPrice` -`Price3`) > 0, 1, if(sum(`LastCoinPrice`-`Price3`) < 0, -1, 0)) as  `LastPriceTrend`
  ,if(sum(`Price3`-`Price4`) > 0, 1, if(sum(`Price3`-`Price4`) < 0, -1, 0)) as  `Price3Trend`
  ,if(sum(`Price4`-`Price5`) > 0, 1, if(sum(`Price4`-`Price5`) < 0, -1, 0)) as  `Price4Trend`, `FixSellRule`
  , `SellRule`, `BuyRule`, `ToMerge`, `LowPricePurchaseEnabled`, `DailyBTCLimit`, `PctToPurchase`, `BTCBuyAmount`, `NoOfPurchases`, `Name`, `Image`, 10 as `MaxCoinMerges`,`APIKey`,`APISecret`,`KEK`,`Email`,`UserName`
  ,`PctProfitSell`,`SpreadBetRuleID`,`CaptureTrend`
  ,(sum(`LiveCoinPrice`*`Amount`))-(sum(`CoinPrice`*`Amount`)) as `Profit`,(sum(`CoinPrice`*`Amount`)) as `PurchasePrice`
  ,(sum(`LiveCoinPrice`*`Amount`)) as `LivePrice`,`CalculatedRisesInPrice`
  ,(sum(`CoinPrice`*`Amount`))* `BaseMultiplier` as `PurchasePriceUSDT`

  ,(sum(`LiveCoinPrice`*`Amount`))*`BaseMultiplier` as `LivePriceUSDT`
  ,(sum(`LiveCoinPrice`*`OriginalAmount`))*`BaseMultiplier` as `SoldPriceUSDT`
,((if(`Status` = 'Sold', sum(`SellPrice`*`Amount`),if(`Status` in('Open','Pending'), sum(`LiveCoinPrice`*`Amount`),0)) - sum(`CoinPrice`*`Amount`))/sum(`CoinPrice`*`Amount`)) * 100 as `ProfitPct`
  ,if(`Status` = 'Sold', sum(`SellPrice`*`Amount`),if(`Status` in('Open','Pending'), sum(`LiveCoinPrice`*`Amount`),0)) as `CurrentPrice`

  FROM `View7_SpreadBetSell` $whereClause Group by `SpreadBetTransactionID`";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array( $row['ID'],$row['Type'],$row['UserID'],$row['CoinPrice'],$row['Amount'],$row['Status'],$row['OrderDate'],$row['CompletionDate'],$row['LastBuyOrders'],$row['LiveBuyOrders'],$row['BuyOrdersPctChange'] //10
      ,$row['LastMarketCap'],$row['LiveMarketCap'],$row['MarketCapPctChange'],$row['LastCoinPrice'],$row['LiveCoinPrice'],$row['CoinPricePctChange'],$row['LastSellOrders'],$row['LiveSellOrders'],$row['SellOrdersPctChange'] //19
      ,$row['LastVolume'],$row['LiveVolume'],$row['VolumePctChange'],$row['Last1HrChange'],$row['Live1HrChange'],$row['Hr1PctChange'],$row['Last24HrChange'],$row['Live24HrChange'],$row['Hr24PctChange'],$row['Last7DChange'] //29
      ,$row['Live7DChange'],$row['D7PctChange'],$row['BaseCurrency'],$row['AutoSellPrice'],$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['FixSellRule'],$row['SellRule'],$row['BuyRule']//40
      ,$row['ToMerge'],$row['LowPricePurchaseEnabled'],$row['BTCBuyAmount'],$row['PctToPurchase'],$row['BTCBuyAmount'],$row['NoOfPurchases'],$row['Name'],$row['Image'],$row['MaxCoinMerges'],$row['APIKey'],$row['APISecret'],$row['KEK'] //52
      ,$row['Email'],$row['UserName'],$row['PctProfitSell'],$row['SpreadBetRuleID'],$row['CaptureTrend']  //57
      ,$row['Profit'],$row['PurchasePrice'],$row['LivePrice'],$row['CalculatedRisesInPrice'],$row['PurchasePriceUSDT'],$row['LivePriceUSDT'],$row['SoldPriceUSDT'],$row['ProfitPct'],$row['CurrentPrice']); //66
  }
  $conn->close();
  return $tempAry;
}

function getSpreadCoinSellData($ID = 0){
  if ($ID == 0){
    $whereclause = "";
  }else{
    $whereclause = " WHERE `SpreadBetTransactionID` = $ID ";
  }
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `ID`, `Type`, `CoinID`, `UserID`, `CoinPrice`, `Amount`, `Status`, `OrderDate`, `CompletionDate`, `BittrexID`, `OrderNo`, `Symbol`, `LastBuyOrders`, `LiveBuyOrders`
  , ((`LiveBuyOrders`-`LastBuyOrders`)/`LastBuyOrders`)*100 as `BuyOrdersPctChange`
  , `LastMarketCap`, `LiveMarketCap`,((`LiveMarketCap`-`LastMarketCap`)/`LastMarketCap`)*100 as  `MarketCapPctChange`, `LastCoinPrice`, `LiveCoinPrice`, ((`LastCoinPrice`-`LastCoinPrice`)/`LastCoinPrice`)*100 as `CoinPricePctChange`
  , `LastSellOrders`, `LiveSellOrders`
  , ((`LiveSellOrders`-`LastSellOrders`)/`LastSellOrders`)*100 as `SellOrdersPctChange`, `LastVolume`, `LiveVolume`, ((`LiveVolume`-`LastVolume`)/`LastVolume`)*100 as `VolumePctChange`, `Last1HrChange`
  , `Live1HrChange`, ((`Live1HrChange`-`Last1HrChange`)/`Last1HrChange`)*100 as `Hr1PctChange`, `Last24HrChange`, `Live24HrChange`, ((`Live24HrChange`-`Last24HrChange`)/`Last24HrChange`)*100 as `Hr24PctChange`, `Last7DChange`, `Live7DChange`
  , ((`Live7DChange`-`Last7DChange`)/`Last7DChange`)*100 as `D7PctChange`, `BaseCurrency`, 'AutoSellPrice', if(`Price4` -`Price5` > 0, 1, if(`Price4` -`Price5` < 0, -1, 0)) as `Price4Trend`
  , if(`Price3` -`Price4` > 0, 1, if(`Price3` -`Price4` < 0, -1, 0)) as   `Price3Trend`, if(`LastCoinPrice` -`Price3` > 0, 1, if(`LastCoinPrice` -`Price3` < 0, -1, 0)) as  `LastPriceTrend`
  , if(`LiveCoinPrice` -`LastCoinPrice` > 0, 1, if(`LiveCoinPrice` -`LastCoinPrice` < 0, -1, 0)) as  `LivePriceTrend`, `FixSellRule`, `SellRule`, `BuyRule`, `ToMerge`, `LowPricePurchaseEnabled`, `DailyBTCLimit`, `PctToPurchase`, `BTCBuyAmount`
  , `NoOfPurchases`, `Name`, `Image`, 10 as `MaxCoinMerges`, `SpreadBetTransactionID`
  ,`PctToSave`,`CalculatedRisesInPrice`,`SpreadBetRuleID`,`PctProfitSell`,`AutoBuyBackSell`,`TopPrice` as `BounceTopPrice`,`LowPrice` as `BounceLowPrice`,`Difference` as `BounceDifference`
  ,TimeStampDiff(MINUTE, `DelayCoinSwapUntil`, now()) as `DelayCoinSwap`,`NoOfSells`
  FROM `View7_SpreadBetSell` $whereclause";
  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['Type'],$row['CoinID'],$row['UserID'],$row['CoinPrice'],$row['Amount'],$row['Status'],$row['OrderDate'],$row['CompletionDate'],$row['BittrexID'],$row['OrderNo'] //10
      ,$row['Symbol'],$row['LastBuyOrders'],$row['LiveBuyOrders'],$row['BuyOrdersPctChange'],$row['LastMarketCap'],$row['LiveMarketCap'],$row['MarketCapPctChange'],$row['LastCoinPrice'],$row['LiveCoinPrice'] //19
      ,$row['CoinPricePctChange'],$row['LastSellOrders'],$row['LiveSellOrders'],$row['SellOrdersPctChange'],$row['LastVolume'],$row['LiveVolume'],$row['VolumePctChange'],$row['Last1HrChange'],$row['Live1HrChange'] //28
      ,$row['Hr1PctChange'],$row['Last24HrChange'],$row['Live24HrChange'],$row['Hr24PctChange'],$row['Last7DChange'],$row['Live7DChange'],$row['D7PctChange'],$row['BaseCurrency'],$row['AutoSellPrice'] //37
      ,$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['FixSellRule'],$row['SellRule'],$row['BuyRule'],$row['ToMerge'],$row['LowPricePurchaseEnabled'],$row['DailyBTCLimit'] //47
      ,$row['PctToPurchase'],$row['BTCBuyAmount'],$row['NoOfPurchases'],$row['Name'],$row['Image'],$row['MaxCoinMerges'],$row['SpreadBetTransactionID'],$row['PctToSave'],$row['CalculatedRisesInPrice'] //56
    ,$row['SpreadBetRuleID'],$row['PctProfitSell'],$row['AutoBuyBackSell'],$row['BounceTopPrice'],$row['BounceLowPrice'],$row['BounceDifference'],$row['DelayCoinSwap'],$row['NoOfSells']);
  }
  $conn->close();
  return $tempAry;
}

function getSpreadCoinSellDataAll($ID = 0){
  if ($ID == 0){
    $whereclause = "";
  }else{
    $whereclause = " WHERE `TransactionID` = $ID ";
  }
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `ID`, `Type`, `CoinID`, `UserID`, `CoinPrice`, `Amount`, `Status`, `OrderDate`, `CompletionDate`, `BittrexID`, `OrderNo`, `Symbol`, `LastBuyOrders`, `LiveBuyOrders`, `BuyOrdersPctChange`, `LastMarketCap`
  , `LiveMarketCap`, `MarketCapPctChange`, `LastCoinPrice`, `LiveCoinPrice`, `CoinPricePctChange`, `LastSellOrders`, `LiveSellOrders`, `SellOrdersPctChange`, `LastVolume`, `LiveVolume`, `VolumePctChange`, `Last1HrChange`
  , `Live1HrChange`, `Hr1PctChange`, `Last24HrChange`, `Live24HrChange`, `Hr24PctChange`, `Last7DChange`, `Live7DChange`, `D7PctChange`, `BaseCurrency`, `AutoSellPrice`, `Price4Trend`, `Price3Trend`, `LastPriceTrend`
  , `LivePriceTrend`, `FixSellRule`, `SellRule`, `BuyRule`, `ToMerge`, `LowPricePurchaseEnabled`, `PurchaseLimit`, `PctToPurchase`, `BTCBuyAmount`, `NoOfPurchases`, `Name`, `Image`, `MaxCoinMerges`, `SpreadBetTransactionID`
  ,`PctToSave`,`CalculatedRisesInPrice`,`SpreadBetRuleID`,`PctProfitSell`,`AutoBuyBackSell`,`BounceTopPrice`,`BounceLowPrice`,`BounceDifference`,`DelayCoinSwap`,`NoOfSells`
  FROM `SellCoinsAllView` $whereclause";
  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['Type'],$row['CoinID'],$row['UserID'],$row['CoinPrice'],$row['Amount'],$row['Status'],$row['OrderDate'],$row['CompletionDate'],$row['BittrexID'],$row['OrderNo'] //10
      ,$row['Symbol'],$row['LastBuyOrders'],$row['LiveBuyOrders'],$row['BuyOrdersPctChange'],$row['LastMarketCap'],$row['LiveMarketCap'],$row['MarketCapPctChange'],$row['LastCoinPrice'],$row['LiveCoinPrice'] //19
      ,$row['CoinPricePctChange'],$row['LastSellOrders'],$row['LiveSellOrders'],$row['SellOrdersPctChange'],$row['LastVolume'],$row['LiveVolume'],$row['VolumePctChange'],$row['Last1HrChange'],$row['Live1HrChange'] //28
      ,$row['Hr1PctChange'],$row['Last24HrChange'],$row['Live24HrChange'],$row['Hr24PctChange'],$row['Last7DChange'],$row['Live7DChange'],$row['D7PctChange'],$row['BaseCurrency'],$row['AutoSellPrice'] //37
      ,$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['FixSellRule'],$row['SellRule'],$row['BuyRule'],$row['ToMerge'],$row['LowPricePurchaseEnabled'],$row['PurchaseLimit'] //47
      ,$row['PctToPurchase'],$row['BTCBuyAmount'],$row['NoOfPurchases'],$row['Name'],$row['Image'],$row['MaxCoinMerges'],$row['SpreadBetTransactionID'],$row['PctToSave'],$row['CalculatedRisesInPrice'] //56
    ,$row['SpreadBetRuleID'],$row['PctProfitSell'],$row['AutoBuyBackSell'],$row['BounceTopPrice'],$row['BounceLowPrice'],$row['BounceDifference'],$row['DelayCoinSwap'],$row['NoOfSells']);
  }
  $conn->close();
  return $tempAry;
}

function getSpreadCoinSellDataFixed($ID = 0){
  if ($ID == 0){
    $whereclause = "WHERE `Status` = 'Open'";
  }else{
    $whereclause = " WHERE `ID` = $ID and `Status` = 'Open'";
  }
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `IDTr`, `Type`, `CoinID`, `UserID`, `CoinPrice`, `Amount`, `Status`, `OrderDate`, `CompletionDate`, `BittrexID`, `OrderNo`, `Symbol`, 'LastBuyOrders', 'LiveBuyOrders','BuyOrdersPctChange','LastMarketCap'
  , 'LiveMarketCap', 'MarketCapPctChange', 'LastCoinPrice', 'LiveCoinPrice', 'CoinPricePctChange', 'LastSellOrders', 'LiveSellOrders', 'SellOrdersPctChange', 'LastVolume', 'LiveVolume', 'VolumePctChange', 'Last1HrChange'
  , 'Live1HrChange', 'Hr1PctChange', 'Last24HrChange', 'Live24HrChange', 'Hr24PctChange', 'Last7DChange', 'Live7DChange', 'D7PctChange', `BaseCurrency`, 'AutoSellPrice', 'Price4Trend', 'Price3Trend', 'LastPriceTrend'
  , 'LivePriceTrend', `FixSellRule`, `SellRule`, `BuyRule`, `ToMerge`, `LowPricePurchaseEnabled`, 'PurchaseLimit', `PctToPurchase`, `BTCBuyAmount`, `NoOfPurchases`, `Name`, `Image`, 10 as `MaxCoinMerges`, `SpreadBetTransactionID`
  ,`PctToSave`,`CalculatedRisesInPrice`,`SpreadBetRuleID`,`SellPct` as `PctProfitSell`,`AutoBuyBackSell`,`TopPrice` as `BounceTopPrice`,`LowPrice` as `BounceLowPrice`,`Difference` as `BounceDifference`,`DelayCoinswapUntil` as `DelayCoinSwap`,`NoOfSells`
  FROM `View7_SpreadBetSell` $whereclause";
  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['IDTr'],$row['Type'],$row['CoinID'],$row['UserID'],$row['CoinPrice'],$row['Amount'],$row['Status'],$row['OrderDate'],$row['CompletionDate'],$row['BittrexID'],$row['OrderNo'] //10
      ,$row['Symbol'],$row['LastBuyOrders'],$row['LiveBuyOrders'],$row['BuyOrdersPctChange'],$row['LastMarketCap'],$row['LiveMarketCap'],$row['MarketCapPctChange'],$row['LastCoinPrice'],$row['LiveCoinPrice'] //19
      ,$row['CoinPricePctChange'],$row['LastSellOrders'],$row['LiveSellOrders'],$row['SellOrdersPctChange'],$row['LastVolume'],$row['LiveVolume'],$row['VolumePctChange'],$row['Last1HrChange'],$row['Live1HrChange'] //28
      ,$row['Hr1PctChange'],$row['Last24HrChange'],$row['Live24HrChange'],$row['Hr24PctChange'],$row['Last7DChange'],$row['Live7DChange'],$row['D7PctChange'],$row['BaseCurrency'],$row['AutoSellPrice'] //37
      ,$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['FixSellRule'],$row['SellRule'],$row['BuyRule'],$row['ToMerge'],$row['LowPricePurchaseEnabled'],$row['PurchaseLimit'] //47
      ,$row['PctToPurchase'],$row['BTCBuyAmount'],$row['NoOfPurchases'],$row['Name'],$row['Image'],$row['MaxCoinMerges'],$row['SpreadBetTransactionID'],$row['PctToSave'],$row['CalculatedRisesInPrice'] //56
    ,$row['SpreadBetRuleID'],$row['PctProfitSell'],$row['AutoBuyBackSell'],$row['BounceTopPrice'],$row['BounceLowPrice'],$row['BounceDifference'],$row['DelayCoinSwap'],$row['NoOfSells']);
  }
  $conn->close();
  return $tempAry;
}

function getSavingsData($ID = 0){
  if ($ID == 0){
    $whereclause = "Where `Amount` >= `MinTradeSize` and `BuyCoin` = 1 and `Status` = 'Saving'";
  }else{
    $whereclause = " WHERE `IDTr` = $ID and `Amount` >= `MinTradeSize` and `BuyCoin` = 1 and `Type` = 'Saving'";
  }
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `IDTr`, `Type`, `CoinID`, `UserID`, `CoinPrice`, `Amount`, `Status`, `OrderDate`, `CompletionDate`, `BittrexID`, `OrderNo`, `Symbol`, `LastBuyOrders`, `LiveBuyOrders`, `BuyOrdersPctChange`, `LastMarketCap`
          , `LiveMarketCap`, `MarketCapPctChange`, `LastCoinPrice`, `LiveCoinPrice`, `CoinPricePctChange`, `LastSellOrders`, `LiveSellOrders`, `SellOrdersPctChange`, `LastVolume`, `LiveVolume`, `VolumePctChange`, `Last1HrChange`
          , `Live1HrChange`, `Hr1ChangePctChange`, `Last24HrChange`, `Live24HrChange`, `Hr24ChangePctChange`, `Last7DChange`, `Live7DChange`, `D7ChangePctChange`, `BaseCurrency`, 'AutoSellPrice'
          ,   `Price4Trend`,  `Price3Trend`,  `LastPriceTrend`, `LivePriceTrend`, `FixSellRule`, `SellRule`, `BuyRule`, `ToMerge`, `LowPricePurchaseEnabled`, 'PurchaseLimit', `PctToPurchase`, `BTCBuyAmount`, `NoOfPurchases`, `Name`
          , `Image`, 10 as `MaxCoinMerges`, 'SpreadBetTransactionID','PctToSave','CalculatedRisesInPrice','SpreadBetRuleID','PctProfitSell','AutoBuyBackSell',`TopPrice`,`LowPrice`,`Difference`,`minsToDelay`,`NoOfSells`
          ,getBTCPrice(84) as BTCPrice, getBTCPrice(85) as ETHPrice, `SellSavingsEnabled`
          FROM `View5_SellCoins` $whereclause";
  echo "<BR> GET SAVINGS SQL: $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['IDTr'],$row['Type'],$row['CoinID'],$row['UserID'],$row['CoinPrice'],$row['Amount'],$row['Status'],$row['OrderDate'],$row['CompletionDate'],$row['BittrexID'],$row['OrderNo'] //10
      ,$row['Symbol'],$row['LastBuyOrders'],$row['LiveBuyOrders'],$row['BuyOrdersPctChange'],$row['LastMarketCap'],$row['LiveMarketCap'],$row['MarketCapPctChange'],$row['LastCoinPrice'],$row['LiveCoinPrice'] //19
      ,$row['CoinPricePctChange'],$row['LastSellOrders'],$row['LiveSellOrders'],$row['SellOrdersPctChange'],$row['LastVolume'],$row['LiveVolume'],$row['VolumePctChange'],$row['Last1HrChange'],$row['Live1HrChange'] //28
      ,$row['Hr1ChangePctChange'],$row['Last24HrChange'],$row['Live24HrChange'],$row['Hr24ChangePctChange'],$row['Last7DChange'],$row['Live7DChange'],$row['D7ChangePctChange'],$row['BaseCurrency'],$row['AutoSellPrice'] //37
      ,$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['FixSellRule'],$row['SellRule'],$row['BuyRule'],$row['ToMerge'],$row['LowPricePurchaseEnabled'],$row['PurchaseLimit'] //47
      ,$row['PctToPurchase'],$row['BTCBuyAmount'],$row['NoOfPurchases'],$row['Name'],$row['Image'],$row['MaxCoinMerges'],$row['SpreadBetTransactionID'],$row['PctToSave'],$row['CalculatedRisesInPrice'] //56
    ,$row['SpreadBetRuleID'],$row['PctProfitSell'],$row['AutoBuyBackSell'],$row['TopPrice'],$row['LowPrice'],$row['Difference'],$row['minsToDelay'],$row['NoOfSells'],$row['BTCPrice'],$row['ETHPrice'],$row['SellSavingsEnabled']); //67
  }
  $conn->close();
  return $tempAry;
}

function newSpreadTransactionID($UserID, $spreadBetRuleID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "Call NewSpreadBetTransaction($UserID,$spreadBetRuleID);";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("newSpreadTransactionID: ".$sql, 'SQL_CALL', 0);
  newLogToSQL("newSpreadTransactionID","$sql",3,sQLUpdateLog,"SQL CALL","UserID:$userID SBRuleID:$spreadBetRuleID");
}

function addProfitToAllocation($UserID, $totalProfitUSD,$saveMode,$baseCurrency,$overrideBBSaving){

  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "call AddToUserCoinSavings($UserID,$totalProfitUSD,'$baseCurrency',$overrideBBSaving)";

  print_r($sql);
  logToSQL("ProfitAllocation","$sql | $coinID",$UserID,1);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("addProfitToAllocation: ".$sql, 'BuyCoin', 0);
  newLogToSQL("addProfitToAllocation",$sql,3,1,"SQL","UserID:$UserID;SaveMode:$saveMode");
}

function getOpenSpreadCoins($userID, $spreadBetRuleID = 0){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  $whereClause = "";
  if ($spreadBetRuleID <> 0){
    $whereClause = " and `Tr`.`SpreadBetRuleID` = $spreadBetRuleID";
  }
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Tr`.`SpreadBetRuleID` as SpreadBetRuleID, `Tr`.`UserID` , count( DISTINCT `Tr`.`SpreadBetTransactionID`) as countOfTransactions
FROM `Transaction` `Tr`
    WHERE `Tr`.`Type` in ('SpreadBuy','SpreadSell') and `Tr`.`Status` in ('Open','Pending') and `Tr`.`UserID` = $userID $whereClause
    group by `Tr`.`SpreadBetRuleID` ";
  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['SpreadBetRuleID'],$row['UserID'],$row['countOfTransactions']);
  }
  $conn->close();
  return $tempAry;
}

function getOpenTransNo($userID, $coinID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));

  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT count(`ID`) as IDcount FROM `Transaction` WHERE `Status` = 'Open' and `UserID` = $userID and `CoinID` = $coinID
          group by `CoinID`";
  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['IDcount']);
  }
  $conn->close();
  return $tempAry;
}

function updateSpreadBuy($spreadBetRuleID){
  //$savingUsdt = $totalProfit * 0.1;
  //$typeUsdt = $totalProfit - $savingUsdt;
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `SpreadBetSettings` SET `NoOfTransactions` = (`NoOfTransactions` + 1) WHERE `SpreadBetRuleID` = $spreadBetRuleID;";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateSpreadBuy: ".$sql, 'BuyCoin', 0);
  newLogToSQL("updateSpreadBuy",$sql,3,0,"SQL","SBRuleID:$spreadBetRuleID");
}

function delaySavingBuy($transactionID,$delayMins){
  //$savingUsdt = $totalProfit * 0.1;
  //$typeUsdt = $totalProfit - $savingUsdt;
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `Transaction` SET `DelayCoinSwapUntil` = DATE_ADD(now(), INTERVAL $delayMins MINUTE) WHERE `ID` = $transactionID;";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("delaySavingBuy: ".$sql, 'SQL_UPDATE', 0);
  newLogToSQL("delaySavingBuy",$sql,3,1,"SQL","TransID:$transactionID");
}

function updateSpreadProfit($spreadBetRuleID, $pctProfit){
  //$savingUsdt = $totalProfit * 0.1;
  //$typeUsdt = $totalProfit - $savingUsdt;
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call updateSpreadProfit($spreadBetRuleID,$pctProfit);";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateSpreadProfit: ".$sql, 'BuyCoin', 0);
  newLogToSQL("updateSpreadProfit","$sql",3,sQLUpdateLog,"SQL CALL","SBRuleID:$spreadBetRuleID");
}

function updateSpreadSell($spreadBetRuleID, $orderDate){
  //$savingUsdt = $totalProfit * 0.1;
  //$typeUsdt = $totalProfit - $savingUsdt;
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call updateSpreadSell($spreadBetRuleID,'$orderDate');";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateSpreadSell: ".$sql, 'SQL_CALL', 0);
  newLogToSQL("updateSpreadSell","$sql",3,sQLUpdateLog,"SQL CALL","SBRuleID:$spreadBetRuleID");
}

function updateBuyTrend($coinID, $transactionID, $mode, $ID, $hr1, $hr24, $d7){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call UpdateBuyTrend($coinID, $transactionID, '$mode', $ID,$hr1, $hr24, $d7);";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateBuyTrend: ".$sql, 'SQL_CALL', 0);
  newLogToSQL("updateBuyTrend","$sql",3,sQLUpdateLog,"SQL CALL","TransactionID:$transactionID CoinID:$coinID");
}

function updateBuyTrendHistory($coinID, $buyDate){
  $conn = getHistorySQL(rand(1,4));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "SELECT `Hr1Pct`,`Hr24Pct`,`D7Pct` FROM `PriceHistory` WHERE `CoinID` = $coinID and `PriceDate` > '$buyDate' and `Price` =
  (SELECT Min(`Price`) FROM `PriceHistory` WHERE `CoinID` = $coinID and `PriceDate` > '$buyDate' and `Price` <> 0.0)";

  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Hr1Pct'],$row['Hr24Pct'],$row['D7Pct']);
  }
  $conn->close();
  return $tempAry;
}

function updateBuyTrendHistorySB($spreadBetRuleID, $buyDate){
  $conn = getHistorySQL(rand(1,4));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "SELECT `Hr1Pct`,`Hr24Pct`,`D7Pct` FROM `SpreadBetPriceHistory` WHERE `SpreadBetRuleID` = $spreadBetRuleID and `PriceDate` > '$buyDate' and `Price` =
  (SELECT Min(`Price`) FROM `SpreadBetPriceHistory` WHERE `SpreadBetRuleID` = $spreadBetRuleID and `PriceDate` > '$buyDate' and `Price` <> 0.0)";

  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Hr1Pct'],$row['Hr24Pct'],$row['D7Pct']);
  }
  $conn->close();
  return $tempAry;
}

function updateSpreadBetPctAmount($spreadBetRuleID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `SpreadBetSettings` SET `PctProfitSell` = (`PctProfitSell` + 0.24) WHERE `SpreadBetRuleID` = $spreadBetRuleID and  `PctProfitSell` <= 15.0;";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateSpreadBetPctAmount: ".$sql, 'BuyCoin', 0);
  newLogToSQL("updateSpreadBetPctAmount",$sql,3,0,"SQL","SBRuleID:$spreadBetRuleID");
}

function updateSpreadBetTransactionAmount($nPrice, $spreadBetRuleID, $BTCAmount){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `SpreadBetTransactions` SET `TotalAmountToBuy`= $nPrice, `AmountPerCoin` = $BTCAmount WHERE `SpreadBetRuleID` = $spreadBetRuleID and `TotalAmountToBuy` = 0.00";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateSpreadBetTransactionAmount: ".$sql, 'BuyCoin', 0);
  newLogToSQL("updateSpreadBetTransactionAmount",$sql,3,0,"SQL","SBRuleID:$spreadBetRuleID");
}

function checkOpenSpreadBet($userID, $spreadBetRuleID = 0){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  $whereClause = "";
  if ($spreadBetRuleID <> 0){ $whereClause = " and `Tr`.`SpreadBetRuleID` = $spreadBetRuleID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "SELECT `Sbt`.`SpreadBetRuleID` as SpreadBetRuleID, sum(`Tr`.`CoinPrice` * `Amount`)  as PurchasePriceUSD, `Sbt`.`TotalAmountToBuy`,`Sbt`.`AmountPerCoin`
    ,count(`Tr`.`SpreadBetRuleID`) as NoOfTransactions
      FROM `Transaction` `Tr`
      left join `SpreadBetTransactions` `Sbt` on `Sbt`.`SpreadBetRuleID` = `Tr`.`SpreadBetRuleID`
      WHERE  `Tr`.`Type` in ('SpreadBuy','SpreadSell') and `Tr`.`Status` in ('Open','Pending') and `Tr`.`UserID` = $userID $whereClause
      group by `Tr`.`SpreadBetRuleID` ";
  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['SpreadBetRuleID'],$row['PurchasePriceUSD'],$row['TotalAmountToBuy'],$row['AmountPerCoin'],$row['NoOfTransactions']);
  }
  $conn->close();
  return $tempAry;
}

function getMaxPct($date,$coinID){
  $conn = getHistorySQL(rand(1,4));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "SELECT getMaxPrice('$date',$coinID) as MaxPrice;";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['MaxPrice']);
  }
  $conn->close();
  return $tempAry;
}

function updateMaxPctToSql($price, $coinID, $mode, $ruleID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "Call UpdateSellTrendToSQL('$mode', $ruleID, $coinID, $price);";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateMaxPctToSql: ".$sql, 'BuyCoin', 0);
  newLogToSQL("updateMaxPctToSql","$sql",3,sQLUpdateLog,"SQL CALL","CoinID:$coinID RuleID:$ruleID");
}

function trackingCoinReadyToBuy($livePrice, $mins, $type, $buyPrice, $TransactionID, $NoOfRisesInPrice, $pctProfit, $minsFromDate, $lastPrice, $totalRisesInPrice, $trackingID,$quickBuyCount,$market1HrChangePct,$oneTimeBuy){
  $swingPct = 0.33;
  if ($livePrice < 0.05){
    $swingPct = 0.66;
  }
  $swingPrice = (($lastPrice/100)*$swingPct);
  $currentPrice = abs($livePrice-$lastPrice);
  $topSwing = $lastPrice + $swingPrice;
  $bottomSwing = $lastPrice - $swingPrice;

  //$bottomPrice = $livePrice-$swingPrice;
  Echo "<BR> Swing:$swingPrice Current:$currentPrice ";
  //if liveprice is stable, add 1 - -0.5 - 0.5
  if ($minsFromDate < 5){
      Echo "<BR>Less Than 5 Mins | OPT 1 : $minsFromDate";
      return 2;
  }

  if (abs($market1HrChangePct) > 0.25){
    $totalRisesInPrice = $totalRisesInPrice * (abs($market1HrChangePct)/0.25);
  }
  Echo "<BR>Sell the Coin | OPT 2 : $minsFromDate| $mins | $livePrice | $buyPrice | $NoOfRisesInPrice | $totalRisesInPrice | $quickBuyCount | $pctProfit";
  if (($minsFromDate >= 60 && $livePrice <= $buyPrice) OR ($NoOfRisesInPrice > $totalRisesInPrice && $livePrice <= $buyPrice) OR ($quickBuyCount >= 3) OR ($NoOfRisesInPrice > $totalRisesInPrice && $pctProfit >= 1.75) ){
    //if time is over 60 min and livePrice is > original price,  sell
    // if no of buys is greater than total needed - Buy
    Echo "<BR>Sell the Coin | OPT 2 : $minsFromDate| $mins | $livePrice | $NoOfRisesInPrice | $totalRisesInPrice";
    newLogToSQL("TrackingCoin", "OPT 2 : $minsFromDate| $mins | $livePrice | $NoOfRisesInPrice | $totalRisesInPrice", 3, 0,"trackingCoinReadyToBuy_2","TrackingCoinID:$trackingID");
    //reopenTransaction($TransactionID);
    logAction("runTrackingCoin; ReadyToBuy : OPT2 | $minsFromDate | $quickBuyCount ", 'BuySellFlow', 1);
    return 1;
  }
  if (($livePrice <= $topSwing) AND ($livePrice >= $bottomSwing)){
    Echo "<BR>Update No Of Rises | OPT 3 : $currentPrice | $swingPrice | $NoOfRisesInPrice | $TransactionID | $livePrice";
    newLogToSQL("TrackingCoin", "OPT 3 : $currentPrice | $swingPrice | $NoOfRisesInPrice | $TransactionID | $livePrice", 3, 0,"trackingCoinReadyToBuy_3","TrackingCoinID:$trackingID");
    if ($livePrice > $lastPrice){ updateQuickBuyCount($trackingID);}else {resetQuickBuyCount($trackingID);}
    updateNoOfRisesInPrice($trackingID, $NoOfRisesInPrice+1);
    //setNewTrackingPrice($livePrice, $trackingID, 'Buy');
    setLastPrice($livePrice,$trackingID, 'Buy');
    return 2;
  }
  //if liveprice is greater than or less than, reset to 0
  if (($livePrice > $topSwing) OR ($livePrice < $bottomSwing)){ //OR ($currentPrice < $swingPrice)
  //if ((($livePrice-$sellPrice) > $swingPrice) OR ($livePrice < $sellPrice)){
    //logToSQL("trackingCoinReadyToBuy", "OPT 4 : $currentPrice | $swingPrice - RESET TO 0 ", 3, 1);
    $tempPrice = $livePrice-$lastPrice;
    if ($livePrice > $lastPrice){ updateQuickBuyCount($trackingID);}else {resetQuickBuyCount($trackingID);}
    Echo "<BR>Outside the swing | OPT 4 : $currentPrice | $swingPrice | $tempPrice | $livePrice | $topSwing | $bottomSwing - RESET TO 0 ";
    updateNoOfRisesInPrice($trackingID, 0);
    if ($livePrice < $bottomSwing){
      echo "<BR> SET New Price Test: $livePrice | $lastPrice | $tempPrice | $swingPrice";
      setNewTrackingPrice($livePrice, $trackingID, 'Buy');
    }

    setLastPrice($livePrice,$trackingID, 'Buy');
    return 2;
  }
  if (($type == 'Buy' && $pctProfit < -3) OR ($type == 'Buy' && $pctProfit > 3)){
    //Cancel Transaction
    Echo "<BR>Cancel Transaction | OPT 5 : $type | $pctProfit";
    newLogToSQL("TrackingCoin", "OPT 5 : $type | $pctProfit", 3, 0,"trackingCoinReadyToBuy_5","TrackingCoinID:$trackingID");
    //reopenTransaction($TransactionID);
    reOpenOneTimeBuyRule($trackingID);
    closeNewTrackingCoin($trackingID, True,5,"Profit < -3 OR > 3");
    setLastPrice($livePrice,$trackingID, 'Buy');
    return 2;
  }
  echo "<BR> Exit trackingCoinReadyToBuy";
  setLastPrice($livePrice,$trackingID, 'Buy');
  return 0;
}

function resetQuickBuyCount($trackingID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `TrackingCoins` SET `quickBuyCount`= 0 WHERE `ID` = $trackingID";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("resetQuickBuyCount: ".$sql, 'BuyCoin', 0);
  newLogToSQL("resetQuickBuyCount",$sql,3,0,"SQL","TrackingID:$trackingID");
}

function updateQuickBuyCount($trackingID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `TrackingCoins` SET `quickBuyCount`= (`quickBuyCount` +1) WHERE `ID` = $trackingID";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateQuickBuyCount: ".$sql, 'BuyCoin', 0);
  newLogToSQL("updateQuickBuyCount",$sql,3,0,"SQL","TrackingID:$trackingID");
}

function trackingCoinReadyToSell($livePrice, $mins, $type, $basePrice, $TransactionID, $totalRisesInPrice, $pctProfit, $minsFromDate, $lastPrice, $NoOfRisesInPrice, $trackingSellID,$market1HrChangePct,$originalSellPrice){
    $swingPct = 0.33;
    if ($livePrice < 0.05){
      $swingPct = 0.66;
    }

    $swingPrice = (($basePrice/100)*$swingPct);
    $currentPrice = abs($livePrice-$basePrice);
    $topSwing = $basePrice + $swingPrice;
    $bottomSwing = $basePrice - $swingPrice;
    $pctFromOrig = (($livePrice-$originalSellPrice)/$originalSellPrice)*100;
    //$bottomPrice = $livePrice-$swingPrice;
    //echo "<BR> SwingPrice: $swingPrice | currentPrice: $currentPrice | LivePrice: $livePrice | sellPrice: $sellPrice";

    if (($NoOfRisesInPrice >= $totalRisesInPrice && $livePrice >= $bottomSwing && $livePrice <= $topSwing && $pctFromOrig >= 2)){
      newLogToSQL("TrackingSell", "OPT 8 (within Swing Ready to Sell): $type | $pctProfit", 3, 1,"trackingCoinReadyToSell_8","TransactionID:$TransactionID");
      echo "<BR> Option8: within Swing Ready to Sell";
      reopenTransaction($TransactionID);
      logAction("runTrackingSellCoin; ReadToSell : OPT8 | $coin | $type | $pctProfit", 'BuySellFlow', 1);
      return 1;
    }

    if (($pctProfit >= 60.0) OR ($NoOfRisesInPrice >= $totalRisesInPrice AND $livePrice >= $basePrice)){
      newLogToSQL("TrackingSell", "OPT 7 (Profit over 20%): $type | $pctProfit | $basePrice", 3, 1,"trackingCoinReadyToSell_7","TransactionID:$TransactionID");
      echo "<BR> Option7: Profit over 20% Sell";
      reopenTransaction($TransactionID);
      logAction("runTrackingSellCoin; ReadToSell : OPT7 | $coin | $type | $pctProfit", 'BuySellFlow', 1);
      return 1;
    }
    //if liveprice is stable, add 1 - -0.5 - 0.5
    if ($minsFromDate < 5){
        //: OPT 1
        return 2;
    }

    if ($type == 'SpreadSell' && $minsFromDate > 14400){
      newLogToSQL("TrackingSell", "OPT 6 (Mins over 14400): $type | $minsFromDate", 3, 0,"trackingCoinReadyToSell_6","TransactionID:$TransactionID");
      echo "<BR> Option6: Mins over 14400";
      updateSQLcancelSpreadBetTrackingSell($TransactionID);
      reopenTransaction($TransactionID);
      closeNewTrackingSellCoin($TransactionID);
      return 2;
    }

    if (abs($market1HrChangePct) > 0.25){
      $totalRisesInPrice = $totalRisesInPrice * (abs($market1HrChangePct)/0.25);
    }
    echo "<BR>trackingCoinReadyToSell_OPT2: $mins | $minsFromDate | $livePrice | $basePrice | $NoOfRisesInPrice | $totalRisesInPrice | $trackingSellID | $TransactionID | $basePrice | $topSwing | $bottomSwing ";
    if (($minsFromDate >= 60 && $pctProfit >= 3.0) OR ($NoOfRisesInPrice >= $totalRisesInPrice && $livePrice >= $basePrice && $pctFromOrig >= 0.5)){
      //if time is over 60 min and livePrice is > original price,  sell : OPT 2
      // if no of buys is greater than total needed - Buy
      echo "<BR> Option2: Sell";
      newLogToSQL("TrackingSell", "OPT 2 (Sell): $mins | $livePrice | $basePrice | $NoOfRisesInPrice | $totalRisesInPrice | $pctProfit", 3, 1,"trackingCoinReadyToSell_2","TransactionID:$TransactionID");
      reopenTransaction($TransactionID);
      logAction("runTrackingSellCoin; ReadToSell : OPT2 | $coin | $type | $pctProfit | $minsFromDate", 'BuySellFlow', 1);
      return 1;
    }
    if (($livePrice <= $topSwing) AND($livePrice >= $bottomSwing)){
      //: OPT 3
      newLogToSQL("TrackingSell", "OPT 3 (Add 1 to Counter): $currentPrice | $swingPrice | $NoOfRisesInPrice | $TransactionID | $livePrice", 3, 0,"trackingCoinReadyToSell_3","TransactionID:$TransactionID");
      //echo "<BR>updateNoOfRisesInSellPrice($trackingSellID, $NoOfRisesInPrice+1, $livePrice);";
      echo "<BR> Option3: CurrentPrice less than Swing";
      updateNoOfRisesInSellPrice($trackingSellID, $NoOfRisesInPrice+1, $livePrice);
      setLastPrice($livePrice,$trackingSellID, 'Sell');
      return 2;
    }
    //if liveprice is greater than or less than, reset to 0
    if (($livePrice > $topSwing) OR ($livePrice < $bottomSwing) ){  //OR ($currentPrice < $swingPrice)
      // : OPT 4
      //logToSQL("trackingCoinReadyToSell", "OPT 4 Current: $currentPrice | Swing: $swingPrice | Live: $livePrice | Sell: $sellPrice - RESET TO 0 ", 3, 1);
      echo "<BR> Option4: Greater/Less than Swing Reset Counter";
      updateNoOfRisesInSellPrice($trackingSellID, 0, $livePrice);
      if ($livePrice > $topSwing){
        echo "<BR> Option4: Set New Tracking BasePrice";
        setNewTrackingPrice($livePrice, $trackingSellID, 'Sell');
      }
      setLastPrice($livePrice,$trackingSellID, 'Sell');
      return 2;
    }

    if (($type == 'Sell' && $pctProfit < -3) OR ($type == 'Sell' && $pctProfit > 3)){
      //Cancel Transaction : OPT 5
      newLogToSQL("TrackingSell", "OPT 5 : $type | $pctProfit", 3, 1,"trackingCoinReadyToSell_5","TransactionID:$TransactionID");
      echo "<BR> Option5: Cancel";
      reopenTransaction($TransactionID);
      closeNewTrackingSellCoin($TransactionID);
      setLastPrice($livePrice,$trackingSellID, 'Sell');
      return 2;
    }


    setLastPrice($livePrice,$trackingSellID, 'Sell');
}

function updateSQLcancelSpreadBetTrackingSell($TransactionID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call cancelSpreadBetTrackingSell($TransactionID);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateSQLcancelSpreadBetTrackingSell: ".$sql, 'SQL_CALL', 0);
  newLogToSQL("updateSQLcancelSpreadBetTrackingSell","$sql",3,sQLUpdateLog,"SQL CALL","TransactionID:$TransactionID");
}

function enableBuyRule($buyRuleID, $buyCoin){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `BuyRules` SET `BuyCoin` = $buyCoin where `ID` = $buyRuleID;";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("enableBuyRule: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("enableBuyRule","$sql",3,0,"SQL CALL","BuyRuleID:$buyRuleID");
}

function buySellProfitEnable($coinID,$userID,$enableBuy, $enableSell,$nPct,$FixSellRule){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "Call NewBuySellProfitSetup($coinID,$userID,$enableBuy,$enableSell,$nPct,$FixSellRule);";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("buySellProfitEnable: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("buySellProfitEnable","$sql",3,1,"SQL CALL","BuyRuleID:$buyRuleID");
}

function getSpreadBetCount($SBTransID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT count(`SBTransID`) as countOfOpenRules FROM `TrackingCoins` WHERE `SBTransID` = $SBTransID";
  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['countOfOpenRules']);
  }
  $conn->close();
  return $tempAry;
}

function getSpreadBetUserSettings(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `NoOfBuysPerCoin`,`TotalNoOfBuys`,`DivideAllocation`,`UserID` FROM `SpreadBetUserSettings`";
  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['NoOfBuysPerCoin'],$row['TotalNoOfBuys'],$row['DivideAllocation'],$row['UserID']);
  }
  $conn->close();
  return $tempAry;
}

function getBuyBackData(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `IDBb`, `TransactionIDBb`, `Quantity`, `SellPriceBb`, `StatusBb`, `SpreadBetTransactionID`, `SpreadBetRuleID`, `CoinID`, `SellPrice` as `SellPriceBA`, `LiveCoinPrice`
            , (`LiveCoinPrice`- `SellPrice`) as `PriceDifferece`
            , ((`LiveCoinPrice`- `SellPrice`)/`SellPrice`)*100 as `PriceDifferecePct`, `UserID`, `Email`, `UserName`, `APIKey`, `APISecret`, `KEK`
            , (`CoinPrice`*`Amount`)-(`LiveCoinPrice`*`Amount`) as `OriginalSaleProfit`
            , (((`CoinPrice`*`Amount`)-(`LiveCoinPrice`*`Amount`))/(`CoinPrice`*`Amount`))*100 as `OriginalSaleProfitPct`, `ProfitMultiply`, `NoOfRaisesInPrice`, `BuyBackPct`
            ,`MinsToCancel`,'BullBearStatus',`Type`,`OverrideCoinAllocation`
            ,`AllBuyBackAsOverride`,getBTCPrice(84) as BTCPrice, getBTCPrice(85) as ETHPrice,`LiveCoinPrice`,TimeStampDiff(MINUTE, now(),`DelayCoinSwapUntil`) as `DelayMins`
            ,if (`OriginalAmount`=0,`Quantity`,`OriginalAmount`) as `OriginalAmount`,`HoursFlatHighPdcs`,`CoinPrice`,`SaveMode`,`CoinPriceBB`,`USDBuyBackAmount`
            ,`Hr1ChangePctChange`,`Hr24ChangePctChange`,`D7ChangePctChange`,(`SellPrice` * `Quantity`)as `TotalUSDSalePrice`,(`LiveCoinPrice` * `Quantity`) as `TotalUSDLivePrice`
            ,((`LiveCoinPrice` * `Quantity`)  - (`SellPrice` * `Quantity`)) as `ProfitUSD`,`LowMarketModeEnabled`,`BuyBackHoursFlatTarget`,ABS(`BuyBackPct`)/(0.35*(ABS(`BuyBackPct`)/10)) as AddNum
            , Abs((`BuyBackPct` /100)* (ABS(`BuyBackPct`)/(0.35*(ABS(`BuyBackPct`)/10)))) as Multiplier,if (`DelayTime` < now(),0,1) as  `DelayCoinPurchase`
            ,`PctOfAuto`,`BuyBackHoursFlatAutoEnabled`,`MaxHoursFlat`,`PctOfAutoReduceLoss`,`PctOfAutoBuyBack`,`bbMinsToCancel`,`BuyBackMinsToCancel`,`BuyBackAutoPct`,`CaaOffset`
            FROM `View9_BuyBack`
            where `StatusBb` <> 'Closed' ";
  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['IDBb'],$row['TransactionIDBb'],$row['Quantity'],$row['SellPriceBb'],$row['StatusBb'],$row['SpreadBetTransactionID'],$row['SpreadBetRuleID'],$row['CoinID'] //7
      ,$row['SellPriceBA'],$row['LiveCoinPrice'],$row['PriceDifferece'],$row['PriceDifferecePct'],$row['UserID'],$row['Email'],$row['UserName'],$row['APIKey'],$row['APISecret'],$row['KEK'] //17
      ,$row['OriginalSaleProfit'],$row['OriginalSaleProfitPct'],$row['ProfitMultiply'],$row['NoOfRaisesInPrice'],$row['BuyBackPct'],$row['MinsToCancel'],$row['BullBearStatus'],$row['Type'] //25
      ,$row['OverrideCoinAllocation'],$row['AllBuyBackAsOverride'],$row['BTCPrice'],$row['ETHPrice'],$row['LiveCoinPrice'],$row['DelayMins'],$row['OriginalAmount'],$row['HoursFlatHighPdcs'] //33
      ,$row['CoinPrice'],$row['SaveMode'],$row['CoinPriceBB'],$row['USDBuyBackAmount'],$row['Hr1ChangePctChange'],$row['Hr24ChangePctChange'],$row['D7ChangePctChange'] //40
      ,$row['TotalUSDSalePrice'],$row['TotalUSDLivePrice'],$row['ProfitUSD'],$row['LowMarketModeEnabled'],$row['BuyBackHoursFlatTarget'],$row['AddNum'],$row['Multiplier'],$row['DelayCoinPurchase'] //48
      ,$row['PctOfAuto'],$row['BuyBackHoursFlatAutoEnabled'],$row['MaxHoursFlat'],$row['PctOfAutoReduceLoss'],$row['PctOfAutoBuyBack'],$row['bbMinsToCancel'],$row['BuyBackMinsToCancel'] //55
      ,$row['BuyBackAutoPct'],$row['CaaOffset']); //57
  }
  $conn->close();
  return $tempAry;
}

function getBuyBackKittyAmount($userID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT  `USDTAmount`, `BTCAmount`,`ETHAmount`,`BuyPortion`,`BuyPortionBTC`,`BuyPortionETH` FROM `BuyBackKitty` WHERE  `UserID` = $userID; ";

  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['USDTAmount'],$row['BTCAmount'],$row['ETHAmount'],$row['BuyPortion'],$row['BuyPortionBTC'],$row['BuyPortionETH']);
  }
  $conn->close();
  return $tempAry;
}

function getMarketPrices($dateTime){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  if ($dateTime == ''){ $timeVar = "now()";} else{ $timeVar = "'$dateTime'";}
  $sql = "SELECT  `MarketPrice`, `DateTime` FROM `MarketPriceChange` where `DateTime`  >  $timeVar order by `DateTime` Desc ";

  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['MarketPrice'],$row['DateTime']);
  }
  $conn->close();
  return $tempAry;
}

function getPriceDipRules(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `RuleID`,`EnableRuleActivationAfterDipBr`,`PriceDipEnable24Hour`,  `Hr24ChangePctChangeMkt` as `Hr24ChangePctChange`
          , `D7ChangePctChangeMkt` as `D7ChangePctChange`,`PriceDipEnable7Day`,`BuyRuleIDPds`,`PriceDipEnabledPds`,`HoursFlatPds`,`DipStartTimePds`,`HoursFlat`,`PctTolerance`
          ,`24HrPriceDipPctBr`,`7DPriceDipPctBr`,`BuyCoin`
          ,(`24HrPriceDipPctBr`+`7DPriceDipPctBr`)/2 as PriceDipEnableAvg
          ,( `Hr24ChangePctChangeMkt` + `D7ChangePctChangeMkt`)/2 as LivePctChangeAvg
          ,(`24HrPriceDipPctBr`+`7DPriceDipPctBr`)/2 as PriceDipDisableAvg
          ,`MaxCoinPricePctChange`, `MaxHr1ChangePctChange`, `MaxHr24ChangePctChange`,`MaxD7ChangePctChange`, `MinCoinPricePctChange`, `MinHr1ChangePctChange`, `MinHr24ChangePctChange`, `MinD7ChangePctChange`
          ,`PctOfAuto`,`DisableAfterDipPct`
            FROM `View13_UserBuyRules`
            WHERE `EnableRuleActivationAfterDip` >= 1 ";

  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['RuleID'],$row['EnableRuleActivationAfterDipBr'],$row['PriceDipEnable24Hour'],$row['Hr24ChangePctChange'],$row['D7ChangePctChange'],$row['PriceDipEnable7Day'],$row['BuyRuleIDPds'],$row['PriceDipEnabledPds'],$row['HoursFlatPds'] //8
      ,$row['DipStartTimePds'],$row['HoursFlat'],$row['PctTolerance'],$row['24HrPriceDipPctBr'],$row['7DPriceDipPctBr'],$row['BuyCoin'],$row['PriceDipEnableAvg'],$row['LivePctChangeAvg'],$row['PriceDipDisableAvg'],$row['MaxCoinPricePctChange']//18
    ,$row['MaxHr1ChangePctChange'],$row['MaxHr24ChangePctChange'],$row['MaxD7ChangePctChange'],$row['MinCoinPricePctChange'],$row['MinHr1ChangePctChange'],$row['MinHr24ChangePctChange'],$row['MinD7ChangePctChange'],$row['PctOfAuto'],$row['DisableAfterDipPct']); //27
  }
  $conn->close();
  return $tempAry;
}

function getMarketStatistics(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `LiveCoinPrice`, `LastCoinPrice`, `Price3`, `Price4`, `Price5`, `CoinPricePctChange`, `LiveMarketCap`, `LastMarketCap`, `MarketCapPctChange`, `LiveBuyOrders`, `LastBuyOrders`, `BuyOrdersPctChange`, `LiveVolume`, `LastVolume`, `VolumePctChange`
          , `Live1HrChange`, `Last1HrChange`, `Live24HrChange`, `Last24HrChange`, `Live7DChange`, `Last7DChange`, `1HrChange3`, `1HrChange4`, `1HrChange5`, `Hr1ChangePctChange`, `Hr24ChangePctChange`, `D7ChangePctChange`, `LiveSellOrders`, `LastSellOrders`
          , `SellOrdersPctChange`, `LivePriceTrend`, `LastPriceTrend`, `Price3Trend`, `Price4Trend`, `1HrPriceChangeLive`, `1HrPriceChangeLast`, `1HrPriceChange3`, `1HrPriceChange4` FROM `View21_MarketStats` ";

  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['LiveCoinPrice'],	$row['LastCoinPrice'],	$row['Price3'],	$row['Price4'],	$row['Price5'],	$row['CoinPricePctChange'],	$row['LiveMarketCap'],	$row['LastMarketCap'],	$row['MarketCapPctChange']
      ,	$row['LiveBuyOrders'],	$row['LastBuyOrders'],	$row['BuyOrdersPctChange'],	$row['LiveVolume'],	$row['LastVolume'],	$row['VolumePctChange'],	$row['Live1HrChange'],	$row['Last1HrChange'],	$row['Live24HrChange']
      ,	$row['Last24HrChange'],	$row['Live7DChange'],	$row['Last7DChange'],	$row['1HrChange3'],	$row['1HrChange4'],	$row['1HrChange5'],	$row['Hr1ChangePctChange'],	$row['Hr24ChangePctChange'],	$row['D7ChangePctChange']
      ,	$row['LiveSellOrders'],	$row['LastSellOrders'],	$row['SellOrdersPctChange'],	$row['LivePriceTrend'],	$row['LastPriceTrend'],	$row['Price3Trend'],	$row['Price4Trend'],	$row['1HrPriceChangeLive'],	$row['1HrPriceChangeLast']
      ,	$row['1HrPriceChange3'],	$row['1HrPriceChange4']);
  }
  $conn->close();
  return $tempAry;
}

function setPriceDipEnable($ruleID,$status,$buyCoin){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "call PriceDipEnable($status,$ruleID);";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("setPriceDipEnable: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("setPriceDipEnable","$sql | $buyCoin",3,0,"SQL CALL","ruleID:$ruleID");
}

function writePriceDipHours($ruleID,$dipHourCounter){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call updatePriceDipHours($ruleID,$dipHourCounter);";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("writePriceDipHours: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("writePriceDipHours","$sql",3,0,"SQL CALL","ruleID:$ruleID");
}

function writePriceDipCoinHours($coinID,$dipHourCounter,$dipHourCounterLow,$dipHourCounterHigh){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call updatePriceDipCoinHours($coinID,$dipHourCounter,$dipHourCounterLow,$dipHourCounterHigh);";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("writePriceDipHours: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("writePriceDipHours","$sql",3,sQLUpdateLog,"SQL CALL","coinID:$coinID");
}

function writeCoinPriceDipPrice($coinID,$price){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "INSERT INTO `PriceDipCoins`(`CoinID`, `Price`) VALUES ($coinID,$price)";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("writeCoinPriceDipPrice: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("writeCoinPriceDipPrice","$sql",3,sQLUpdateLog,"SQL CALL","CoinID:$coinID");
}

function reOpenTransactionfromBuyBack($buyBackID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "Select `Tr`.`CoinID`, `Cp`.`LiveCoinPrice`, `Tr`.`UserID`, `Cn`.`BaseCurrency`,1 as SendEmail,1 as BuyCoin,
            (SELECT `SellPrice` from `BittrexAction` where `TransactionID` = (SELECT `TransactionID` FROM `BuyBack` WHERE `ID` = $buyBackID)and `Type` in ('Sell','SpreadSell')) * `Tr`.`Amount` as SalePrice, `Tr`.`BuyRule`, 0.0 as CoinOffset,0 as CoinOffsetEnabled,1 as BuyType
            ,90 as MinsToCancel, `Tr`.`FixSellRule`,0 as toMerge,0 as noOfPurchases,5 as RisesInPrice, 'BuyBack' as Type ,`Tr`.`CoinPrice` as OriginalPrice,`Tr`.`SpreadBetTransactionID`, `Tr`.`SpreadBetRuleID`,`Cn`.`Symbol`, `Tr`.`MultiSellRuleTemplateID`
            from `Transaction` `Tr`
            join `CoinPrice` `Cp` on `Cp`.`CoinID` = `Tr`.`CoinID`
            join `Coin` `Cn` on `Cn`.`ID` = `Tr`.`CoinID`
            where `Tr`.`ID` = (SELECT `TransactionID` FROM `BuyBack` WHERE `ID` = $buyBackID)";

            echo "<BR> $sql";
            $result = $conn->query($sql);
            //$result = mysqli_query($link4, $query);
            //mysqli_fetch_assoc($result);
            while ($row = mysqli_fetch_assoc($result)){
                $tempAry[] = Array($row['CoinID'],$row['LiveCoinPrice'],$row['UserID'],$row['BaseCurrency'],$row['SendEmail'],$row['BuyCoin'],$row['SalePrice'],$row['BuyRule'] //7
              ,$row['CoinOffset'],$row['CoinOffsetEnabled'],$row['BuyType'],$row['MinsToCancel'],$row['FixSellRule'],$row['toMerge'],$row['noOfPurchases'],$row['RisesInPrice'],$row['Type'],$row['OriginalPrice']  //17
            ,$row['SpreadBetTransactionID'],$row['SpreadBetRuleID'],$row['Symbol'],$row['MultiSellRuleTemplateID']); //21
            }
            $conn->close();
            return $tempAry;
}

function reOpenTransactionfromBuyBackNew($buyBackID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `BuyBack` SET `Status` = 'Open' WHERE `TransactionID` = $buyBackID;";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("reOpenTransactionfromBuyBackNew: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("reOpenTransactionfromBuyBackNew","$sql",3,1,"SQL CALL","BuyBackID:$buyBackID");
}

function addToBuyBackMultiplier($buyBackID,$addNum,$buyBackPct,$multiplier, $base){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "Call addToBuyBackMultiply($buyBackID,$addNum,$buyBackPct,$multiplier, $base);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("addToBuyBackMultiplier: ".$sql, 'TrackingCoins', 1);
  newLogToSQL("addToBuyBackMultiplier","$sql",3,0,"SQL CALL","BuyBackID:$buyBackID");
}

function closeBuyBack($buyBackID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `BuyBack` SET `Status`= 'Closed' WHERE `ID` = $buyBackID ";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("closeBuyBack: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("closeBuyBack",$sql,3,1,"SQL","BuyBackID:$buyBackID");
}

function buyBackDelay($coinID, $mins,$tmpUserID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "Call AddDelayToBuyBack($coinID,$mins,$tmpUserID);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("buyBackDelay: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("buyBackDelay",$sql,3,0,"SQL","CoinID:$coinID");
}

function sellSpreadBetCoins($spreadSellCoins){
  $spreadSellCoinsSize = count($spreadSellCoins);
  echo "<BR> Sell Spread Coins | $spreadSellCoinsSize";
  for ($q=0; $q<$spreadSellCoinsSize; $q++){

    $coin = $spreadSellCoins[$q][11];  $BaseCurrency =  $spreadSellCoins[$q][36]; $TransactionID = $spreadSellCoins[$q][0];
    $CoinID = $spreadSellCoins[$q][2]; $OrderNo = $spreadSellCoins[$q][10]; $LiveCoinPrice = $spreadSellCoins[$q][19];
    $date = date("Y-m-d H:i:s", time()); $SendEmail = 1; $SellCoin = 1; $CoinSellOffsetEnabled = 0; $CoinSellOffsetPct = 0.0;
    $Amount = $spreadSellCoins[$q][5]; $CoinPrice = $spreadSellCoins[$q][4]; $FixSellRule = $spreadSellCoins[$q][42];
    $orderDate = $spreadSellCoins[$q][7]; $pctToSave = $spreadSellCoins[$q][55]; $userID = $spreadSellCoins[$q][3];
    $type = $spreadSellCoins[$q][1]; $fallsInPrice = $spreadSellCoins[$q][56]; $spreadBetRuleID = $spreadSellCoins[$q][57];
    LogToSQL("SpreadBetSell","profitPct :$profitPct | spreadBetPctProfitSell: $spreadBetPctProfitSell | ID: $spreadBetRuleID NoOfCoins:$spreadSellCoinsSize;",3,0);
    //echo "<BR> sellCoins($APIKey, $APISecret,$coin, $Email, $userID, 0,$date, $BaseCurrency,$SendEmail,$SellCoin, $FixSellRule,$UserName,$OrderNo,$Amount,$CoinPrice,$TransactionID,$CoinID,$CoinSellOffsetEnabled,$CoinSellOffsetPct,$LiveCoinPrice, $type);";
    LogToSQL("SpreadBetSell","sellCoins($TransactionID,$CoinID);",3,1);
    //$checkSell = sellCoins($APIKey, $APISecret,$coin, $Email, $userID, 0,$date, $BaseCurrency,$SendEmail,$SellCoin, $FixSellRule,$UserName,$OrderNo,$Amount,$CoinPrice,$TransactionID,$CoinID,$CoinSellOffsetEnabled,$CoinSellOffsetPct,$LiveCoinPrice,$type);
    LogToSQL("SpreadBetTrackingSell","newTrackingSellCoins($LiveCoinPrice,$userID, $TransactionID,$SellCoin, $SendEmail,0,0.0,2);",3,0);
    newTrackingSellCoins($LiveCoinPrice,$userID, $TransactionID,$SellCoin, $SendEmail,0,0.0,$fallsInPrice);
    setTransactionPending($TransactionID);
    LogToSQL("SpreadBetTest1","newTrackingSellCoins($LiveCoinPrice,$userID, $TransactionID,1, 1,0,0.0,2);",3,0);
    //newTrackingSellCoins($LiveCoinPrice,$userID, $TransactionID,1, 1,0,0.0,2);
    LogToSQL("SpreadBetTest2","setTransactionPending($TransactionID);",3,0);
    //setTransactionPending($TransactionID);
    updateSpreadSell($spreadBetRuleID,$orderDate);
    $buyTrendPct = updateBuyTrendHistorySB($spreadBetRuleID,$orderDate);
    $Hr1Trnd = $buyTrendPct[0][0]; $Hr24Trnd = $buyTrendPct[0][1]; $d7Trnd = $buyTrendPct[0][2];
    updateBuyTrend(0, 0, 'SpreadBet', $spreadBetRuleID, $Hr1Trnd,$Hr24Trnd,$d7Trnd);
    if ($q == $spreadSellCoinsSize -1 AND $spreadSellCoinsSize > 0){
        updateSpreadBetPctAmount($spreadBetRuleID);
        LogToSQL("SpreadBetSell","updateSpreadBetPctAmount($spreadBetRuleID);",3,0);
        UpdateProfit();
        LogToSQL("SpreadBetSell","UpdateProfit();",3,0);
    }
    $profitPct = ($LiveCoinPrice-$CoinPrice)/$CoinPrice*100;
    $sellPrice = ($LiveCoinPrice*$Amount);
    $buyPrice = $CoinPrice*$Amount;
    $fee = (($sellPrice)/100)*0.25;
    $profit = number_format((float)($sellPrice-$buyPrice)-$fee, 8, '.', '');
    $pctToSave = $pctToSave / 100;
    addProfitToAllocation($userID, $profit);
    LogToSQL("SpreadBetSell","addProfitToAllocation($userID, $profit, 'SpreadBet', $pctToSave,$CoinID);",3,0);
    logAction("runSellSpreadBet; sellSpreadBetCoins : $q | $coin | $CoinID | $BaseCurrency | $LiveCoinPrice | $Amount | $CoinPrice | $type | $profitPct | $spreadBetRuleID | $TransactionID", 'BuySellFlow', 1);
  }
}

function WriteBuyBack($transactionID, $profitPct, $noOfRisesInPrice, $minsToCancel,$finalPrice,$amount,$cost,$usd_Amount,$buyBackEnabled,$overrideAmount,$overrideSaving){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "Call WriteBuyBack($transactionID,$noOfRisesInPrice,$profitPct,$minsToCancel,$finalPrice,$amount,$cost,$usd_Amount,$buyBackEnabled,$overrideAmount,$overrideSaving);";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("WriteBuyBack: ".$sql, 'SQL_CALL', 0);
  newLogToSQL("WriteBuyBack","$sql",3,1,"SQL CALL","TransactionID:$transactionID");
}

function writeProfitToWebTable($spreadBetTransactionID,$originalPurchasePrice, $liveTotalPrice, $saleTotalPrice){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "call AddProfitForWebTable($spreadBetTransactionID,$originalPurchasePrice, $liveTotalPrice, $saleTotalPrice);";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("writeProfitToWebTable: ".$sql, 'SpreadBetSell', 0);
  newLogToSQL("writeProfitToWebTable","$sql",3,sQLUpdateLog,"SQL CALL","SBTransactionID:$spreadBetTransactionID");
}

function updateBuyBackKittyAmount($tmpBaseCur,$bbKittyAmount,$tmpUserID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "call updateBuyBackKittyAmount('$tmpBaseCur', $bbKittyAmount,$tmpUserID);";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateBuyBackKittyAmount: ".$sql, 'SQL_CALL', 0);
  newLogToSQL("updateBuyBackKittyAmount","$sql",3,sQLUpdateLog,"SQL CALL","");
}

function WriteWebMarketStats($marketPctChangeHr1,$marketPctChangeHr24,$marketPctChangeD7){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `WebMarketStats` SET `1HrPrice`=$marketPctChangeHr1,`24HrPrice`=$marketPctChangeHr24,`7DPrice`=$marketPctChangeD7 WHERE `ID` = 1";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("WriteWebMarketStats: ".$sql, 'SpreadBetSell', 0);
  newLogToSQL("WriteWebMarketStats","$sql",3,sQLUpdateLog,"SQL CALL","");
}

function getTotalProfitSpreadBetSell($spreadBetTransactionID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT ifNull(sum(`CoinPrice`*`Amount`),0) as OriginalPurchasePrice ,ifNull(sum(`LiveCoinPrice` * `Amount`),0) as LiveTotalPrice,ifNull(sum(`SellPrice`*`Amount`),0) as SaleTotalPrice
    ,getBTCPrice(84) as getBTCPrice, getBTCPrice(85) as getETHPrice, ((sum((`LiveCoinPrice` * `Amount`)-(`CoinPrice`*`Amount`)))/(`CoinPrice`*`Amount`))*100 as `PctProfitSell`
            FROM `View15_OpenTransactions` $spreadBetTransactionID ";

  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['OriginalPurchasePrice'],$row['LiveTotalPrice'],$row['SaleTotalPrice'],$row['getBTCPrice'],$row['getETHPrice'],$row['PctProfitSell']);
      //13  14  15

  }
  $conn->close();
  return $tempAry;
}

function getWebMarketStats(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Hr1ChangePctChange`,`Hr24ChangePctChange`,`D7ChangePctChange` FROM `MarketCoinStats`";

  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Hr1ChangePctChange'],$row['Hr24ChangePctChange'],$row['D7ChangePctChange']);
      //13  14  15

  }
  $conn->close();
  return $tempAry;
}

function getSavingTotal($userID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `TotalUSDT`,`LivePrice`,`Profit` FROM `WebSavings` WHERE `UserID` = $userID";

  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['TotalUSDT'],$row['LivePrice'],$row['Profit']);
      //13  14  15

  }
  $conn->close();
  return $tempAry;
}

function getNewSavingTotal($userID, $baseCurrency){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  if ($baseCurrency == 'USDT'){ $nCol = '`SavingUSDT`';}
  elseif ($baseCurrency == 'BTC'){ $nCol = '`SavingBTC`';}
  elseif ($baseCurrency == 'ETH'){ $nCol = '`SavingETH`';}

  $sql = "SELECT $nCol as `Saving`, getBTCPrice(84) as `BTCPrice`, getBTCPrice(85) as `ETHPrice`  FROM `UserCoinSavings` WHERE `UserID` = $userID";

  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Saving'],$row['BTCPrice'],$row['ETHPrice']);
      //13  14  15

  }
  $conn->close();
  return $tempAry;
}

function getSoldProfitSpreadBetSell($spreadBetTransactionID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT sum(`Tr`.`CoinPrice`*`Tr`.`Amount`) as OriginalPurchasePrice, sum(`Ba`.`SellPrice`*`Tr`.`Amount`) as SellPrice
      From `BittrexAction` `Ba`
      join `Transaction` `Tr` on `Tr`.`ID` = `Ba`.`TransactionID`
      join `CoinPrice` `Cp` on `Cp`.`CoinID` = `Tr`.`CoinID`
      WHERE `Tr`.`SpreadBetTransactionID` = $spreadBetTransactionID and `Tr`.`Status` = 'Sold'
      and `Ba`.`Type` in ('Sell','SpreadSell')";

  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['OriginalPurchasePrice'],$row['SellPrice']);
  }
  $conn->close();
  return $tempAry;
}

function updateSpreadBetTotalProfitBuy($transactionID, $coinPrice, $amount){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "INSERT INTO `SpreadBetTotalProfit`( `TransactionID`, `SpreadBetTransactionID`, `CoinPrice`, `Amount`)
  VALUES ($transactionID,(SELECT `SpreadBetTransactionID` FROM `Transaction` WHERE `ID` = $transactionID),$coinPrice,$amount);";
  logToSQL("BittrexSQL", "$sql", 3, 1);
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateSpreadBetTotalProfitBuy: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("updateSpreadBetTotalProfitBuy",$sql,3,0,"SQL","TransactionID:$transactionID");
}

function updateSpreadBetTotalProfitSell($transactionID,$salePrice){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `SpreadBetTotalProfit` SET `SalePrice` = $salePrice, `Status`='Sold' WHERE `TransactionID` = $transactionID";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateSpreadBetTotalProfitSell: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("updateSpreadBetTotalProfitSell",$sql,3,0,"SQL","TransactionID:$transactionID");
}

function updateSpreadBetSellTarget($transactionID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "INSERT INTO `SpreadBetSellTarget`( `TransactionID`, `SBTransactionID`, `SellPct`)
  VALUES ($transactionID,(SELECT `SpreadBetTransactionID` FROM `Transaction` WHERE `ID` = $transactionID )
  ,(SELECT `PctProfitSell`/7 FROM `SpreadBetSettings` WHERE `SpreadBetRuleID` = (SELECT `SpreadBetRuleID` FROM `Transaction` WHERE `ID` = $transactionID )) )";
  logToSQL("BittrexSQL", "$sql", 3, 1);
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateSpreadBetSellTarget: ".$sql, 'TrackingCoins', 1);
  newLogToSQL("updateSpreadBetSellTarget",$sql,3,0,"SQL","TransactionID:$transactionID");
}

function deleteSpreadBetTotalProfit($spreadBetTransactionID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "DELETE FROM `SpreadBetTotalProfit`  WHERE `SpreadBetTransactionID` = $spreadBetTransactionID";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("deleteSpreadBetTotalProfit: ".$sql, 'TrackingCoins', 0);
}

function deleteSpreadBetTrackingCoins($spreadBetTransactionID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "DELETE FROM `TrackingCoins` WHERE `SBTransID` = $spreadBetTransactionID";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("deleteSpreadBetTrackingCoins: ".$sql, 'TrackingCoins', 0);
}

function CloseAllBuyBack($spreadBetTransactionID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `BuyBack`
            SET `Status` = 'Closed'
            WHERE `TransactionID` in (SELECT `ID` FROM `Transaction` WHERE `SpreadBetTransactionID` = $spreadBetTransactionID)";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("CloseAllBuyBack: ".$sql, 'TrackingCoins', 0);
  newLogToSQL("CloseAllBuyBack",$sql,3,0,"SQL","SBTransactionID:$spreadBetTransactionID");
}

function subPctFromProfitSB($sBTransID,$pctToSub, $transactionID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "Call UpdateOrAddSBTransSellTargetPct($transactionID, $pctToSub,$sBTransID);";
  print_r("<BR>".$sql."<BR>");
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  newLogToSQL("subPctFromProfitSB","$sql",3,sQLUpdateLog,"SQL CALL","TransactionID:$transactionID SBTransID:$sBTransID");
}

function echoText($text, $enabled){
  if ($enabled == 1){
    echo "<BR> $text";
  }
}
?>
