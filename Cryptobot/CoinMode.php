<html>
<?php
ini_set('max_execution_time', 900);
require('includes/newConfig.php');
//require '/home/stevenj1979/repositories/Sparkline/autoload.php';
include_once ('/home/stevenj1979/SQLData.php');
$apikey=getAPIKey();
$apisecret=getAPISecret();
$logToFileSetting = getLogToFile();

function WritetoRule($coinD, $ruleID, $highPrice, $lowPrice, $buyAmount, $enable, $type, $sellRuleID){
  $newHighPrice = round($highPrice,8);
  $newLowPrice = round($lowPrice,8);
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call UpdateRulesforCoinMode($coinD,$ruleID,$newHighPrice,$newLowPrice,$buyAmount,$enable,$type,$sellRuleID);";

  print_r("<BR>".$sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function isBuyMode($coinAry, $minBuyAmount){
  //$coinArySize = Count($coinAry);
  //for ($i=0; $i<$coinArySize; $i++){
      $coinID = $coinAry[0]; $Hr24Price = $coinAry[4]; $D7Price = $coinAry[6];
      $Hr1AveragePrice = $coinAry[11]; $month6HighPrice = $coinAry[2]; $month6LowPrice = $coinAry[3]; $ruleID = $coinAry[1];
      $buyPrice = $coinAry[9]; $livePrice = $coinAry[10]; $projectedMaxPrice = $coinAry[12]; $projectedMinPrice = $coinAry[13];
      $ruleIDSell = $coinAry[8]; $userID = $coinAry[14]; $modeID = $coinAry[15];
      $hr1Top = $coinAry[16]; $hr1Btm = $coinAry[17]; $hr24Target = $coinAry[19]; $d7Target = $coinAry[21];
      $t1 = False; $t2 = False; $t3 = False;

      //24 Hour price is down
      $pctInc24Hours = (($livePrice - $Hr24Price)/$Hr24Price)*100;

      if ($pctInc24Hours <= $hr24Target){ $t1 = True;}
      //7Day Price is down
      $pctInc7Day = (($livePrice - $D7Price)/$D7Price)*100;

      if ($pctInc7Day <= $d7Target){ $t2 = True;}
      //Average is flat
      if ($Hr1AveragePrice <= $hr1Top and $Hr1AveragePrice >= $hr1Btm){ $t3 = True;}

      echo "<BR> Checking Buy Mode: $coinID | 24HourPrice: $pctInc24Hours | 7DayPrice: $pctInc7Day | Avg 1Hr Price: $Hr1AveragePrice | Checking Buy Mode: $t1 | $t2 | $t3 ";
      if ($t1 == True and $t2 == True and $t3 == True){
        //Calculate Buy Price
        if ($livePrice < $month6LowPrice){ $new6MonthLowPrice = $livePrice;} else {$new6MonthLowPrice = $month6LowPrice; }
        if ($livePrice > $month6HighPrice){ $new6MonthHighPrice = $livePrice;} else {$new6MonthHighPrice = $month6HighPrice; }
        $pctToBuy = ($new6MonthHighPrice-$livePrice)/($new6MonthHighPrice-$new6MonthLowPrice);
        echo "<BR> pctToBuy: ($new6MonthHighPrice-$livePrice)/($new6MonthHighPrice-$new6MonthLowPrice)";
        $buyAmount = ($buyPrice*$pctToBuy);
        echo "<BR> buyAmount: ($buyPrice*$pctToBuy)";
        echo "<BR> Total Buy AMOUNT: $buyAmount | $buyPrice | $pctToBuy | $livePrice | $month6HighPrice | $new6MonthLowPrice";
        //Write Coin, High Price Limit, Low Price Limit, Buy Amount - To Rule and Enable
        if ($buyAmount >= $minBuyAmount){
          echo "<BR> Activate BUY MODE";
          if ($pctInc7Day <= -15){$newProjectedMaxPrice = $new6MonthHighPrice; $newProjectedMinPrice = $new6MonthLowPrice;}
          else{ $newProjectedMaxPrice = $projectedMaxPrice; $newProjectedMinPrice = $projectedMinPrice;}
          WritetoRule($coinID, $ruleID, $newProjectedMaxPrice,$newProjectedMinPrice,$buyAmount, 1, 1,$ruleIDSell);
          if ($modeID <> 1){ logToSQL("CoinModeBuy","Change Coin mode to 1 for $coinID | $livePrice | $new6MonthHighPrice | $new6MonthLowPrice", $userID, 1);}

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
        $Hr1AveragePrice = $coinAry[11]; $month6HighPrice = $coinAry[2]; $month6LowPrice = $coinAry[3]; $ruleIDSell = $coinAry[8];
        $projectedMaxPrice = $coinAry[12]; $projectedMinPrice = $coinAry[13]; $ruleID = $coinAry[1];
        $userID = $coinAry[14]; $modeID = $coinAry[15];
        $hr1Top = $coinAry[16]; $hr1Btm = $coinAry[17]; $hr24Target = $coinAry[18]; $d7Target = $coinAry[20];
        $secondarySellRulesAry = split($coinAry[22],',');
        $secondarySellRulesSize = Count($secondarySellRulesAry);
        $t1 = False; $t2 = False; $t3 = False;

        //24 Hour price is up
        $pctInc24Hours = (($livePrice - $Hr24Price)/$Hr24Price)*100;
        if ($pctInc24Hours >= $hr24Target){$t1 = True;}
        //7Day Price is Up
        $pctInc7Day = (($livePrice - $D7Price)/$D7Price)*100;

        if ($pctInc7Day >= $d7Target){ $t2 = True;}
        //Average is flat
        if ($Hr1AveragePrice <= $hr1Top and $Hr1AveragePrice >= $hr1Btm){ $t3 = True;}
        echo "<BR> Checking Sell Mode: $coinID | 24HourPrice: $pctInc24Hours | 7DayPrice: $pctInc7Day | 1hourAvgPrice : $Hr1AveragePrice | Checking Sell Mode: $t1 | $t2 | $t3 ";
        if ($t1 == True and $t2 == True and $t3 == True){
          //Calculate Sell Price
          echo "<BR> Activate SELL MODE";
          if ($pctInc7Day >= 15.0){ $newProjectedMaxPrice = $month6HighPrice; $newProjectedMinPrice = $month6LowPrice;}
          else{ $newProjectedMaxPrice = $projectedMaxPrice; $newProjectedMinPrice = $projectedMinPrice;}
          //Write Coin, High Price Limit, Low Price Limit  - To Rule and Enable
          for ($i=0; $i<$secondarySellRulesSize; $i++){
            WritetoRule($coinID,$ruleID,$newProjectedMaxPrice,$newProjectedMinPrice, 0, 1, 2,$secondarySellRulesAry[$i]);
          }
          WritetoRule($coinID,$ruleID,$newProjectedMaxPrice,$newProjectedMinPrice, 0, 1, 2,$ruleIDSell);
          if ($modeID <> 2){ logToSQL("CoinModeSell","Change Coin mode to 2 for $coinID | $livePrice", $userID, 1);}
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
          $coinID = $coinAry[0]; $Hr24Price = $coinAry[4]; $D7Price = $coinAry[6];
          $Hr1AveragePrice = $coinAry[11]; $month6HighPrice = $coinAry[2]; $month6LowPrice = $coinAry[3]; $ruleIDSell = $coinAry[8];
          $ruleID = $coinAry[1]; $livePrice = $coinAry[10];
          $userID = $coinAry[14]; $modeID = $coinAry[15];
          $hr1Top = $coinAry[16]; $hr1Btm = $coinAry[17]; $hr24TargetTop = $coinAry[18]; $hr24TargetBtm = $coinAry[19]; $d7TargetTop = $coinAry[20]; $d7TargetBtm = $coinAry[21];
          $secondarySellRulesAry = split($coinAry[22],',');
          $secondarySellRulesSize = Count($secondarySellRulesAry);
          $t1 = False; $t2 = False; $t3 = False;

          $pctInc24Hours = (($livePrice - $Hr24Price)/$Hr24Price)*100;

          if ($pctInc24Hours > $hr24TargetBtm and $pctInc24Hours < $hr24TargetTop){$t1 = True;}
          $pctInc7Day = (($livePrice - $D7Price)/$D7Price)*100;
          if ($pctInc7Day > $d7TargetBtm and $pctInc7Day < $d7TargetTop){ $t2 = True;}

          //Average is Increasing
          if ($Hr1AveragePrice <= $hr1Top and $Hr1AveragePrice >= $hr1Btm){ $t3 = True;}
          echo "<BR> Checking Flat Mode: $coinID | 24HourPrice: $pctInc24Hours| 7DayPrice: $pctInc7Day | 1hourAvgPrice : $Hr1AveragePrice | Checking Flat Mode: $t1 | $t2 | $t3 ";
          if ($t1 == True and $t2 == True and $t3 == True or $forceFlat = 1){
            //Calculate Sell Price
            for ($i=0; $i<$secondarySellRulesSize; $i++){
              WritetoRule($coinID,$ruleID,0,0, 0, 0, 3, $secondarySellRulesAry[$i]);
            }
            echo "<BR> Activate FLAT MODE";
            WritetoRule($coinID,$ruleID,0,0, 0, 0, 3,$ruleIDSell);
            if ($modeID <> 3){ logToSQL("CoinMode","Change Coin mode to 3 for $coinID", $userID, 1);}
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

        $sql = "SELECT `CoinID`,`RuleID`,`Avg6MonthMax`,`Avg6MonthMin`,`Live24HrChange`,`Last24HrChange`,`Live7DChange`,`Last7DChange`,`RuleIDSell`
        ,`USDBuyAmount`,`LiveCoinPrice`,`1HourAvgPrice`,`ProjectedPriceMax`,`ProjectedPriceMin`,`UserID`,`ModeID`,`Hr1Top` ,`Hr1Btm` ,`Hr24Top` ,`Hr24Btm`
        ,`D7Top`,`D7Btm`,`SecondarySellRules` FROM `CoinModePricesView`";
        $result = $conn->query($sql);
        //$result = mysqli_query($link4, $query);
        //mysqli_fetch_assoc($result);
        while ($row = mysqli_fetch_assoc($result)){
            $tempAry[] = Array($row['CoinID'],$row['RuleID'],$row['Avg6MonthMax'],$row['Avg6MonthMin'],$row['Live24HrChange'],$row['Last24HrChange'],$row['Live7DChange'],$row['Last7DChange'] //7
          ,$row['RuleIDSell'],$row['USDBuyAmount'],$row['LiveCoinPrice'],$row['1HourAvgPrice'],$row['ProjectedPriceMax'],$row['ProjectedPriceMin'],$row['UserID'],$row['ModeID'] //15
        ,$row['Hr1Top'],$row['Hr1Btm'],$row['Hr24Top'],$row['Hr24Btm'],$row['D7Top'],$row['D7Btm'],$row['SecondarySellRules']); //21
        }
        $conn->close();
        return $tempAry;
      }

  $buyFlag = False; $sellFlag = False;
  $coinsAry = getCoins();
  $coinsArySize = count($coinsAry);
  //echo "<BR> Checking Coin Mode:";
  for ($x=0; $x<$coinsArySize; $x++){
    Echo "<BR> --------- Checking NEW Coin for Coin Mode: ".$coinAry[$x][0];
    $buyFlag = isBuyMode($coinsAry[$x],10.0);
    echo " || $buyFlag";
    $sellFlag = isSellMode($coinsAry[$x]);
    echo " || $sellFlag";
    if ($buyFlag == False AND $sellFlag == False){
      isFlatMode($coinsAry[$x], 1);
    }
  }




?>
</html>
