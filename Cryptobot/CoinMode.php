<html>
<?php
ini_set('max_execution_time', 900);
require('includes/newConfig.php');
//require '/home/stevenj1979/repositories/Sparkline/autoload.php';
include_once ('/home/stevenj1979/SQLData.php');
$apikey=getAPIKey();
$apisecret=getAPISecret();
$logToFileSetting = getLogToFile();

function WritetoRule($coinD, $ruleID, $highPrice, $lowPrice, $buyAmount, $enable, $type, $sellRuleID,$numOfRisesInPrice, $minsToCancel,$hr1Top,$newMoinModeSellRuleEnabled,$coinModeOverridePriceEnabled,$coinPricePatternEnabled){
  $newHighPrice = round($highPrice,8);
  $newLowPrice = round($lowPrice,8);
  $numOfRisesInPrice = round($numOfRisesInPrice,0,PHP_ROUND_HALF_DOWN);
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call UpdateRulesforCoinMode($coinD,$ruleID,$newHighPrice,$newLowPrice,$buyAmount,$enable,$type,$sellRuleID,$numOfRisesInPrice,$minsToCancel,$hr1Top,$newMoinModeSellRuleEnabled,$coinModeOverridePriceEnabled,$coinPricePatternEnabled);";

  print_r("<BR>".$sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function getUserID(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `UserID`,`LowMarketModeStartPct`,`LowMarketModeIncrements`,`LowMarketModeAuto`,`PctOfAuto`,`LowMarketModeEnabled` FROM `UserConfig` where (`LowMarketModeEnabled` > 0) or (`LowMarketModeEnabled` = -1)";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['UserID'],$row['LowMarketModeStartPct'],$row['LowMarketModeIncrements'],$row['LowMarketModeAuto'],$row['PctOfAuto'],$row['LowMarketModeEnabled']);
  }
  $conn->close();
  return $tempAry;
}

function checkMarketforPctDip(){
  $userIDs = getUserID();
  $userIDsSize = count($userIDs);
  $marketStats = getNewMarketstats();
  $marketStatsSize = count($marketStats);
  echo "<BR> checkMarketforPctDip : $marketStatsSize";
  for ($y=0; $y<$marketStatsSize; $y++){
    $marketPctChangeHr1 = $marketStats[$y][0]; $marketPctChangeHr24 = $marketStats[$y][1];$marketPctChangeD7 = $marketStats[$y][2];
    $lowMarketModeStartPct = $userIDs[0][1]; $lowMarketModeIncrements = $userIDs[0][2]; $lowMarketModeAuto = $userIDs[0][3]; $pctOfAuto = $userIDs[0][4];
    $avgPctChange = ($marketPctChangeHr24 + $marketPctChangeD7)/2; $lowMarketModeEnabled = $userIDs[0][5];
    $minHr1ChangePctChange = $marketStats[$y][3]; $minHr24ChangePctChange = $marketStats[$y][4]; $minD7ChangePctChange = $marketStats[$y][5];
    if ($lowMarketModeAuto == 1){
      $lowMarketModeIncrements = ((($minHr24ChangePctChange + $minD7ChangePctChange)/2)*$pctOfAuto)/4;
      $avgPctChange = (($minHr24ChangePctChange + $minD7ChangePctChange)/2)*$pctOfAuto;
    }
    echo "<BR> Checking: 1Hr: $marketPctChangeHr1 | 24Hr: $marketPctChangeHr24 | 7D: $marketPctChangeD7 TotalUserID: $userIDsSize LowMarketStartPct:$lowMarketModeStartPct Inc:$lowMarketModeIncrements avg: $avgPctChange";
    if ($avgPctChange <= $lowMarketModeStartPct){

        for ($t=0; $t<$userIDsSize; $t++){
          $userID = $userIDs[$t][0];
          $mode = floor(abs($avgPctChange/$lowMarketModeIncrements));
          newLogToSQL("checkMarketforPctDip","Mode:$mode |  AvgPctCh: $avgPctChange | LowMMIncrements: $lowMarketModeIncrements | Auto:$lowMarketModeAuto | Min24HrPct:$minHr24ChangePctChange | Min7DPct: $minD7ChangePctChange | PctAuto: $pctOfAuto",3,1,"CoinMode","UserID:3");
          echo "<BR> Enabing LowMarketMode for: $userID Mode: $mode 24H: $marketPctChangeHr24 Inc:$lowMarketModeIncrements avg:$avgPctChange";
          if ($mode <= 0){ $mode = -1;}
          runLowMarketMode($userID,$mode);
          LogToSQL("LowMarketMode","runLowMarketMode($userID,1); $marketPctChangeHr1 : $marketPctChangeHr24 : $avgPctChange",$userID,1);
        }

    //}elseif ($marketPctChangeHr24 <= -10.0 and $marketPctChangeHr1 > 0){
    //  for ($t=0; $t<$userIDsSize; $t++){
    //    $userID = $userIDs[$t][0];
    //    echo "<BR> Enabing LowMarketMode for: $userID";
    //    runLowMarketMode($userID,2);
    //    LogToSQL("LowMarketMode","runLowMarketMode($userID,2); $marketPctChangeHr1 : $marketPctChangeHr24",$userID,1);
    //  }
  }elseif ($avgPctChange > 0 AND $lowMarketModeEnabled > 0){
      for ($t=0; $t<$userIDsSize; $t++){
        $userID = $userIDs[$t][0];
        echo "<BR> Enabing LowMarketMode for: $userID Mode: 0";
        runLowMarketMode($userID,-1);
        LogToSQL("LowMarketMode","runLowMarketMode($userID,-1); $marketPctChangeHr1 : $marketPctChangeHr24",$userID,1);
      }
    }
    WriteWebMarketStats($marketPctChangeHr1,$marketPctChangeHr24,$marketPctChangeD7);
  }
}

function getNewMarketstats(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT ((((`LiveCoinPrice`-`Live1HrChange`))/ (`Live1HrChange`))*100) as Hr1MarketPctChange
            ,((((`LiveCoinPrice`-`Live24HrChange`))/ (`Live24HrChange`))*100) as Hr24MarketPctChange
            ,((((`LiveCoinPrice`-`Live7DChange`))/ (`Live7DChange`))*100) as D7MarketPctChange
            ,`MinHr1ChangePctChange`, `MinHr24ChangePctChange`, `MinD7ChangePctChange`
            FROM `MarketCoinStats`  ";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['Hr1MarketPctChange'],$row['Hr24MarketPctChange'],$row['D7MarketPctChange'],$row['MinHr1ChangePctChange'],$row['MinHr24ChangePctChange'],$row['MinD7ChangePctChange']);
  }
  $conn->close();
  return $tempAry;
}

function getWrongSpreadBet(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `ID`,`SpreadBetRuleID`, `SpreadBetTransactionID` FROM `Transaction` WHERE `SpreadBetRuleID` <> 2 and `Status` in ('Open','Pending')";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['ID'],$row['SpreadBetRuleID'],$row['SpreadBetTransactionID']);
  }
  $conn->close();
  return $tempAry;
}

function SpreadBetTest(){
  $sbTrans = getWrongSpreadBet();
  $sbTransSize = count($sbTrans);
  if (isset($sbTransSize)){
    newLogToSQL("SpreadBetTest",$sql,3,0,"Non-ZERO","Count:$sbTransSize");
  }else{
    newLogToSQL("SpreadBetTest",$sql,3,0,"ZERO","Count:$sbTransSize");
  }
}

function addBuyCount($buyModeCount, $ruleID, $coinID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "UPDATE `CoinModeRules` SET  `BuyModeCount` = $buyModeCount + 1  where `RuleID` = $ruleID and `CoinID` = $coinID";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("addBuyCount: ".$sql, 'CoinModeBuy', 0);
}

function resetBuyCount($ruleID, $coinID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "UPDATE `CoinModeRules` SET  `BuyModeCount` = 0  where `RuleID` = $ruleID and `CoinID` = $coinID";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("addBuyCount: ".$sql, 'CoinModeBuy', 0);
}


function sendCoinModeEmail($to, $symbol, $hr1Price, $hr24Price, $d7Price, $subject, $user, $mode, $pctToBuy,$numOfRisesInPrice,$new6MonthHighPrice,$new6MonthLowPrice,$livePrice,$newProjectedMaxPrice,$newProjectedMinPrice,$buyAmount, $buySellPrice){
    $from = 'Coin Alert <alert@investment-tracker.net>';
    $date = date("Y-m-d H:i", time());
    $body = "Dear ".$user.", <BR/>";
    $body .= "$mode is activated for $symbol at $date"."<BR/>";
    $body .= "1 Hour Price: $hr1Price <BR/>";
    $body .= "24 Hour Price: $hr24Price <BR/>";
    $body .= "7 Days Price: $d7Price <BR/>";
    $body .= "Pct To Buy: $pctToBuy <BR/>";
    $body .= "No Of Rises: $numOfRisesInPrice <BR/>";
    $body .= "6 Month High: $new6MonthHighPrice <BR/>";
    $body .= "6 Month Low: $new6MonthLowPrice <BR/>";
    $body .= "Live Price: $livePrice <BR/>";
    $body .= "Projected Sell Price High: $newProjectedMaxPrice <BR/>";
    $body .= "Projected Sell Price Low: $newProjectedMinPrice <BR/>";
    $body .= "Buy Amount: $buyAmount <BR/>";
    $body .= "Buy Price: $buySellPrice <BR/>";
    $body .= "Kind Regards\nCryptoBot.";
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    //$headers .= 'From: Alerts <Alerts@Investment-Tracker.net>' . "\r\n";
    $headers .= "From:".$from."\r\n";
    $headers .= "To:".$to."\r\n";
    mail($to, $subject, wordwrap($body,70),$headers);
}

function isBuyMode($coinAry, $minBuyAmount){
  //$coinArySize = Count($coinAry);
  //for ($i=0; $i<$coinArySize; $i++){
      $coinID = $coinAry[0]; $Hr24Price = $coinAry[4]; $D7Price = $coinAry[6];
      $Hr1AveragePrice = $coinAry[38]; $month6HighPrice = $coinAry[2]; $month6LowPrice = $coinAry[3]; $ruleID = $coinAry[1];
      $buyPrice = $coinAry[9]; $livePrice = $coinAry[10]; $projectedMaxPrice = $coinAry[12]; $projectedMinPrice = $coinAry[13];
      $ruleIDSell = $coinAry[8]; $userID = $coinAry[14]; $modeID = $coinAry[15];
      $hr1Top = $coinAry[16]; $hr1Btm = $coinAry[17]; $hr24Target = $coinAry[18]; $d7Target = $coinAry[20];
      $coinModeEmailsEnabled = $coinAry[23]; $email = $coinAry[24]; $userName = $coinAry[25]; $symbol = $coinAry[26];
      $minsToCancelBuy = $coinAry[28]; $coinModeBuyRuleEnabled = $coinAry[29];$coinModeSellRuleEnabled = $coinAry[30];
      $countForBuyMode = $coinAry[32]; $buyModeCount = $coinAry[33];
      $allTimeHigh =$coinAry[34]; $allTimeLow = $coinAry[35]; $pctOfAllTimeHigh = $coinAry[36]; $bullBearStatus = $coinAry[37];
      $t1 = False; $t2 = False; $t3 = False;
      echo "<BR> BULBEAR MODE = $bullBearStatus";
      //24 Hour price is down
      $pctInc24Hours = (($livePrice - $Hr24Price)/$Hr24Price)*100;
      if ($bullBearStatus == 'BULL'){
        $hr24Target = 10.0;
        $hr1Top = 10.0;
        $hr1Btm = 0.0;
        $d7Target = 10.0;
      }
      Echo "<BR> 24 Hour Price Test: $pctInc24Hours | $hr24Target";
      if ($pctInc24Hours <= $hr24Target){ $t1 = True;}
      //7Day Price is down

      $pctInc7Day = (($livePrice - $D7Price)/$D7Price)*100;
      echo "<BR> TEST 7D Price: $pctInc7Day = (($livePrice - $D7Price)/$D7Price)*100;";
      if ($pctInc7Day <= $d7Target){ $t2 = True;}
      //Average is flat
      if ($Hr1AveragePrice <= $hr1Top and $Hr1AveragePrice >= $hr1Btm){ $t3 = True;}

      echo "<BR> Checking Buy Mode: $symbol ($coinID) | 24HourPrice: $pctInc24Hours/$hr24Target | 7DayPrice: $pctInc7Day/$d7Target | Avg 1Hr Price: $Hr1AveragePrice/$hr1Top-$hr1Btm | Checking Buy Mode: $t1 | $t2 | $t3 ";
      if ($t1 == True and $t2 == True and $t3 == True){
        //Calculate Buy Price
        if ($livePrice < $month6LowPrice){ $new6MonthLowPrice = $livePrice;} else {$new6MonthLowPrice = $month6LowPrice; }
        if ($livePrice > $month6HighPrice){ $new6MonthHighPrice = $livePrice;} else {$new6MonthHighPrice = $month6HighPrice; }
        //$pctToBuy = ($new6MonthHighPrice-$livePrice)/($new6MonthHighPrice-$new6MonthLowPrice);
        $pctToBuy = ($livePrice-$new6MonthLowPrice)/($new6MonthHighPrice-$new6MonthLowPrice);
        echo "<BR> pctToBuy: ($new6MonthHighPrice-$livePrice)/($new6MonthHighPrice-$new6MonthLowPrice)";
        $multiplier = 2.5-(2.5/$pctOfAllTimeHigh);
        if ($pctOfAllTimeHigh <= 60){
          $buyAmount = (($buyPrice*$multiplier)*(1-$pctToBuy));
        //}elseif ($pctOfAllTimeHigh >= 60){
        //  $buyAmount = (($buyPrice*$multiplier)*(1-$pctToBuy));
        //else{
        //  $buyAmount = ($buyPrice*(1-$pctToBuy));
        }
        echo "<BR> buyAmount: ($buyPrice*$pctToBuy)";
        echo "<BR> Total Buy AMOUNT: $buyAmount | $buyPrice | $pctToBuy | $livePrice | $month6HighPrice | $new6MonthLowPrice";
        if ($coinModeSellRuleEnabled == 0){$newMoinModeSellRuleEnabled = 1;}else{$newMoinModeSellRuleEnabled = 0;}
        //Write Coin, High Price Limit, Low Price Limit, Buy Amount - To Rule and Enable
        addBuyCount($buyModeCount,$ruleID,$coinID);
        if ($buyAmount >= $minBuyAmount AND $coinModeBuyRuleEnabled == 1 AND $countForBuyMode >= $buyModeCount AND $buyModeCount <> 0 AND $pctOfAllTimeHigh <= 80.0){
          echo "<BR> Activate BUY MODE";
          if ($pctInc7Day <= -15){$newProjectedMaxPrice = $new6MonthHighPrice; $newProjectedMinPrice = $new6MonthLowPrice;}
          else{ $newProjectedMaxPrice = $projectedMaxPrice; $newProjectedMinPrice = $projectedMinPrice;}
          $numOfRisesInPrice = (10*$pctToBuy);
          if ($pctToBuy <= 0.2){ $coinModeOverridePriceEnabled = 1; $coinPricePatternEnabled = 0;  }else{$coinModeOverridePriceEnabled = 0;$coinPricePatternEnabled = 1;}
          $newHighPrice = $newProjectedMaxPrice+(($newProjectedMaxPrice/100)*$pctToBuy);
          $newLowPrice = $newProjectedMinPrice-(($newProjectedMinPrice/100)*$pctToBuy);
          $newMinsToCancelBuy = (60 * (1-$pctToBuy))+$minsToCancelBuy;
          if ($$bullBearStatus == 'BULL'){
            $buyAmount = $buyPrice;
            $numOfRisesInPrice = 2;
            $newMinsToCancelBuy = 10080;
          }
          WritetoRule($coinID, $ruleID, $newLowPrice,$newProjectedMinPrice,$buyAmount, 1, 1,$ruleIDSell,$numOfRisesInPrice,$newMinsToCancelBuy,$hr1Top,$newMoinModeSellRuleEnabled,$coinModeOverridePriceEnabled,$coinPricePatternEnabled);
          echo "<BR>WritetoRule($coinID, $ruleID, $newLowPrice,$newProjectedMinPrice,$buyAmount, 1, 1,$ruleIDSell,$numOfRisesInPrice,$newMinsToCancelBuy,$hr1Top,$newMoinModeSellRuleEnabled,$coinModeOverridePriceEnabled,$coinPricePatternEnabled);";
          if ($modeID <> 1){
            logToSQL("CoinModeBuy","Change Coin mode to 1 for: $symbol ($coinID) | $livePrice | $new6MonthHighPrice | $new6MonthLowPrice", $userID, 0);
            if ($coinModeEmailsEnabled == 1){
              sendCoinModeEmail($email,$symbol,$Hr1AveragePrice,$pctInc24Hours,$pctInc7Day, "$symbol Buy Mode Activated",$userName, "Buy Mode",$pctToBuy,$numOfRisesInPrice,$new6MonthHighPrice,$new6MonthLowPrice,$livePrice,$newProjectedMaxPrice,$newProjectedMinPrice,$buyAmount,$newLowPrice);
            }
          }

        }else{ echo "<BR> EXIT: Amount less than $minBuyAmount";}
        return True;
      }else{
        //echo "<BR> Activate FLAT MODE";
        //WritetoRule($coinID,$ruleID,0,0, 0, 0, 3,$ruleIDSell);
        //if ($modeID <> 3){ logToSQL("CoinMode","Change Coin mode to 3 for $coinID", $userID, 1);}
        return False;
      }


  //}
}

  function isSellMode($coinAry){
    //$coinArySize = Count($coinAry);
  //  for ($i=0; $i<$coinArySize; $i++){
        $coinID = $coinAry[0]; $Hr24Price = $coinAry[4]; $D7Price = $coinAry[6]; $livePrice = $coinAry[10];
        $Hr1AveragePrice = $coinAry[38]; $month6HighPrice = $coinAry[2]; $month6LowPrice = $coinAry[3]; $ruleIDSell = $coinAry[8];
        $projectedMaxPrice = $coinAry[12]; $projectedMinPrice = $coinAry[13]; $ruleID = $coinAry[1];
        $userID = $coinAry[14]; $modeID = $coinAry[15];
        $hr1Top = $coinAry[16]; $hr1Btm = $coinAry[17]; $hr24Target = $coinAry[18]; $d7Target = $coinAry[20];
        $secondarySellRulesAry = explode(',',$coinAry[22]);
        $secondarySellRulesSize = Count($secondarySellRulesAry);
        $coinModeEmailsEnabled = $coinAry[27]; $email = $coinAry[24]; $userName = $coinAry[25]; $symbol = $coinAry[26];
        $minsToCancelBuy = $coinAry[28];$coinModeSellRuleEnabled = $coinAry[30]; $bullBearStatus = $coinAry[37];
        $t1 = False; $t2 = False; $t3 = False;
        echo "<BR> BULBEAR MODE = $bullBearStatus";
        //24 Hour price is up
        $pctInc24Hours = (($livePrice - $Hr24Price)/$Hr24Price)*100;
        if ($bullBearStatus == 'BEAR'){
          $hr24Target = -10.0;
          $d7Target = -10.0;
          $hr1Top = -10.0;
          $hr1Btm = -15.0;
        }
        if ($pctInc24Hours >= $hr24Target){$t1 = True;}
        //7Day Price is Up
        $pctInc7Day = (($livePrice - $D7Price)/$D7Price)*100;
        if ($pctInc7Day >= $d7Target){ $t2 = True;}
        //Average is flat
        if ($Hr1AveragePrice <= $hr1Top and $Hr1AveragePrice >= $hr1Btm){ $t3 = True;}
        echo "<BR> Checking Sell Mode: $symbol ($coinID) | 24HourPrice: $pctInc24Hours | 7DayPrice: $pctInc7Day | 1hourAvgPrice : $Hr1AveragePrice | Checking Sell Mode: $t1 | $t2 | $t3 ";
        if ($t1 == True and $t2 == True and $t3 == True AND $coinModeSellRuleEnabled == 1){
          //Calculate Sell Price
          if ($livePrice < $month6LowPrice){ $new6MonthLowPrice = $livePrice;} else {$new6MonthLowPrice = $month6LowPrice; }
          if ($livePrice > $month6HighPrice){ $new6MonthHighPrice = $livePrice;} else {$new6MonthHighPrice = $month6HighPrice; }
          $pctToBuy = ($livePrice-$new6MonthLowPrice)/($new6MonthHighPrice-$new6MonthLowPrice);
          $numOfRisesInPrice = (10*(1-$pctToBuy));
          $newMinsToCancelSell = (60*$pctToBuy)+$minsToCancelBuy;
          echo "<BR> Activate SELL MODE";
          if ($pctInc7Day >= 15.0){ $newProjectedMaxPrice = $month6HighPrice; $newProjectedMinPrice = $month6LowPrice; $coinPricePatternEnabled = 0;}
          else{ $newProjectedMaxPrice = $projectedMaxPrice; $newProjectedMinPrice = $projectedMinPrice; $coinPricePatternEnabled = 1;}
          //Write Coin, High Price Limit, Low Price Limit  - To Rule and Enable
          $newLowPrice = ($newProjectedMinPrice+($newProjectedMinPrice/50))*(1-$pctToBuy);
          for ($i=0; $i<$secondarySellRulesSize; $i++){
            if ($secondarySellRulesAry[$i] <> ""){
              WritetoRule($coinID,$ruleID,$newProjectedMaxPrice,$newLowPrice, 0, 1, 2,$secondarySellRulesAry[$i],$numOfRisesInPrice,$newMinsToCancelSell,0,0,0,$coinPricePatternEnabled);
              Echo "<BR>WritetoRule($coinID,$ruleID,$newProjectedMaxPrice,$newLowPrice, 0, 1, 2,$secondarySellRulesAry[$i],$numOfRisesInPrice,$newMinsToCancelSell,0,0);";
            }
          }
          WritetoRule($coinID,$ruleID,$newProjectedMaxPrice,$newLowPrice, 0, 1, 2,$ruleIDSell,$numOfRisesInPrice,$newMinsToCancelSell,0,0,0,$coinPricePatternEnabled);
          Echo "<BR>WritetoRule($coinID,$ruleID,$newProjectedMaxPrice,$newLowPrice, 0, 1, 2,$ruleIDSell,$numOfRisesInPrice,$newMinsToCancelSell,0,0);";
          if ($modeID <> 2 ){
            logToSQL("CoinModeSell","Change Coin mode to 2 for: $symbol ($coinID) | $livePrice", $userID, 0);
            if ($coinModeEmailsEnabled == 1){
              sendCoinModeEmail($email,$symbol,$Hr1AveragePrice,$pctInc24Hours,$pctInc7Day, "$symbol Sell Mode Activated",$userName, "Sell Mode",$pctToBuy,$numOfRisesInPrice,$newProjectedMaxPrice,$newProjectedMinPrice,$livePrice,$newProjectedMaxPrice,$newProjectedMinPrice,0,$newLowPrice);
            }
          }
          return True;
        }else{
          //echo "<BR> Activate FLAT MODE";
          //WritetoRule($coinID,$ruleID,0,0, 0, 0, 3,$ruleIDSell);
          //if ($modeID <> 3){ logToSQL("CoinMode","Change Coin mode to 3 for $coinID", $userID, 1);}
          return False;
        }
  //  }
  }


    function isFlatMode($coinAry, $forceFlat){
      //$coinArySize = Count($coinAry);
      //for ($i=0; $i<$coinArySize; $i++){
          $coinID = $coinAry[0]; $Hr24Price = $coinAry[4]; $D7Price = $coinAry[6]; $symbol = $coinAry[26];
          $Hr1AveragePrice = $coinAry[38]; $month6HighPrice = $coinAry[2]; $month6LowPrice = $coinAry[3]; $ruleIDSell = $coinAry[8];
          $ruleID = $coinAry[1]; $livePrice = $coinAry[10];
          $userID = $coinAry[14]; $modeID = $coinAry[15];
          $hr1Top = $coinAry[16]; $hr1Btm = $coinAry[17]; $hr24TargetTop = $coinAry[18]; $hr24TargetBtm = $coinAry[19]; $d7TargetTop = $coinAry[20]; $d7TargetBtm = $coinAry[21];
          $minsToCancelBuy = $coinAry[28]; $coinModeBuyRuleEnabled = $coinAry[29];$coinModeSellRuleEnabled = $coinAry[30];
          $secondarySellRulesAry = explode(',',$coinAry[22]);
          $secondarySellRulesSize = Count($secondarySellRulesAry);
          $t1 = False; $t2 = False; $t3 = False;

          $pctInc24Hours = (($livePrice - $Hr24Price)/$Hr24Price)*100;

          if ($pctInc24Hours > $hr24TargetBtm and $pctInc24Hours < $hr24TargetTop){$t1 = True;}
          $pctInc7Day = (($livePrice - $D7Price)/$D7Price)*100;
          if ($pctInc7Day > $d7TargetBtm and $pctInc7Day < $d7TargetTop){ $t2 = True;}

          //Average is Increasing
          if ($Hr1AveragePrice <= $hr1Top and $Hr1AveragePrice >= $hr1Btm){ $t3 = True;}
          echo "<BR> Checking Flat Mode: $symbol ($coinID) | 24HourPrice: $pctInc24Hours| 7DayPrice: $pctInc7Day | 1hourAvgPrice : $Hr1AveragePrice | Checking Flat Mode: $t1 | $t2 | $t3 ";
          if ($t1 == True and $t2 == True and $t3 == True or $forceFlat = 1){
            //Calculate Sell Price
            for ($i=0; $i<$secondarySellRulesSize; $i++){
              if ($secondarySellRulesAry[$i] <> ""){
                if ($coinModeSellRuleEnabled == 0){$newMoinModeSellRuleEnabled = 1;}else{$newMoinModeSellRuleEnabled = 0;}
                WritetoRule($coinID,$ruleID,0,0, 0, 0, 3, $secondarySellRulesAry[$i],0,$minsToCancelBuy,0,$newMoinModeSellRuleEnabled,0,0);
                Echo "<BR>WritetoRule($coinID,$ruleID,0,0, 0, 0, 3, ".$secondarySellRulesAry[$i].",0,$minsToCancelBuy,0,$newMoinModeSellRuleEnabled);";
              }
            }
            echo "<BR> Activate FLAT MODE";
            resetBuyCount($ruleID,$coinID);
            WritetoRule($coinID,$ruleID,0,0, 0, 0, 3,$ruleIDSell,0,$minsToCancelBuy,0,0,0,0);
            Echo "<BR>WritetoRule($coinID,$ruleID,0,0, 0, 0, 3,$ruleIDSell,0,$minsToCancelBuy,0,0);";
            if ($modeID <> 3){ logToSQL("CoinMode","Change Coin mode to 3 for: $symbol ($coinID)", $userID, 1);}
            //Disable Rule
          }

      //}
    }

      function getCoins(){
        $conn = getSQLConn(rand(1,3));
        //$whereClause = "";
        //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = " SELECT `CoinID`,`RuleID`,`SixMonthHighPrice` as `Avg6MonthMax`,`SixMonthMinPrice` as `Avg6MonthMin`,`Live24HrChange`,`Last24HrChange`,`Live7DChange`,`Last7DChange`,`RuleIDSell`
        ,`USDBuyAmount`,`LiveCoinPrice`,`Hr1ChangePctChange` as `1HourAvgPrice`,`15MinPppm` as `ProjectedPriceMax`,`15MinPppi` as`ProjectedPriceMin`,`UserID`,`ModeID`,`Hr1Top` ,`Hr1Btm` ,`Hr24Top` ,`Hr24Btm`
        ,`D7Top`,`D7Btm`,`SecondarySellRules`,`CoinModeEmails`,`Email`,`UserName`, `Symbol`,`CoinModeEmailsSell`,`CoinModeMinsToCancelBuy`,`CoinModeBuyRuleEnabled`
        ,`CoinModeSellRuleEnabled`,`PctToBuy`,`CountToActivateBuyMode`,`BuyModeCount`,`PriceAth` as `AllTimeHighPrice`,`PriceAtl` as `AllTimeLowPrice`,(`LiveCoinPrice`/`PriceAth`)*100 as `PctOfAllTimeHigh`,'BullBearStatus',`Hr1ChangePctChange` as `1HrChange`
        FROM `View18_CoinMode`";
        $result = $conn->query($sql);
        //$result = mysqli_query($link4, $query);
        //mysqli_fetch_assoc($result);
        while ($row = mysqli_fetch_assoc($result)){
            $tempAry[] = Array($row['CoinID'],$row['RuleID'],$row['Avg6MonthMax'],$row['Avg6MonthMin'],$row['Live24HrChange'],$row['Last24HrChange'],$row['Live7DChange'],$row['Last7DChange'] //7
          ,$row['RuleIDSell'],$row['USDBuyAmount'],$row['LiveCoinPrice'],$row['1HourAvgPrice'],$row['ProjectedPriceMax'],$row['ProjectedPriceMin'],$row['UserID'],$row['ModeID'] //15
        ,$row['Hr1Top'],$row['Hr1Btm'],$row['Hr24Top'],$row['Hr24Btm'],$row['D7Top'],$row['D7Btm'],$row['SecondarySellRules'],$row['CoinModeEmails'],$row['Email'],$row['UserName'] //25
        ,$row['Symbol'],$row['CoinModeEmailsSell'],$row['CoinModeMinsToCancelBuy'],$row['CoinModeBuyRuleEnabled'],$row['CoinModeSellRuleEnabled'],$row['PctToBuy'],$row['CountToActivateBuyMode'] //32
        ,$row['BuyModeCount'],$row['AllTimeHighPrice'],$row['AllTimeLowPrice'],$row['PctOfAllTimeHigh'],$row['BullBearStatus'],$row['1HrChange']); //33
        }
        $conn->close();
        return $tempAry;
      }

  $buyFlag = False; $sellFlag = False;
  $coinsAry = getCoins();
  $coinsArySize = count($coinsAry);
  //echo "<BR> Checking Coin Mode:";
  for ($x=0; $x<$coinsArySize; $x++){
    Echo "<BR> --------- Checking NEW Coin for Coin Mode: ".$coinsAry[$x][0];
    $buyFlag = isBuyMode($coinsAry[$x],10.0);
    echo " || $buyFlag";
    $sellFlag = isSellMode($coinsAry[$x]);
    echo " || $sellFlag";
    if ($buyFlag == False AND $sellFlag == False){
      isFlatMode($coinsAry[$x], 1);
    }
  }

checkMarketforPctDip();
//SpreadBetTest();

?>
</html>
