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
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call UpdateRulesforCoinMode($coinD,$ruleID,$highPrice,$lowPrice,$buyAmount,$enable,$type,$sellRuleID);";

  print_r("<BR>".$sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function isBuyMode($coinAry){
  $coinArySize = Count($coinAry);
  for ($i=0; $i<$coinArySize; $i++){
      $coinID = $coinAry[$i][0]; $Hr24Price = $coinAry[$i][4]; $D7Price = $coinAry[$i][6];
      $Hr1AveragePrice = $coinAry[$i][11]; $month6HighPrice = $coinAry[$i][2]; $month6LowPrice = $coinAry[$i][3]; $ruleID = $coinAry[$i][1];
      $buyPrice = $coinAry[$i][9]; $livePrice = $coinAry[$i][10]; $projectedMaxPrice = $coinAry[$i][12]; $projectedMinPrice = $coinAry[$i][13];
      $ruleIDSell = $coinAry[$i][8]; $userID = $coinAry[$i][14]; $modeID = $coinAry[$i][15];
      $t1 = False; $t2 = False; $t3 = False;
      echo "<BR> Checking Buy Mode: $coinID";
      //24 Hour price is down
      $pctInc24Hours = (($livePrice - $Hr24Price)/$Hr24Price)*100;
      echo "<BR> 24HourPrice: $pctInc24Hours";
      if ($pctInc24Hours <= -5.0){ $t1 = True;}
      //7Day Price is down
      $pctInc7Day = (($livePrice - $D7Price)/$D7Price)*100;
      echo "<BR> 7DayPrice: $pctInc7Day";
      if ($pctInc7Day <= -5.0){ $t2 = True;}
      //Average is flat
      if ($Hr1AveragePrice <= 0.5 and $Hr1AveragePrice >= -0.5){ $t3 = True;}
      //if all = yes = calculate Buy Amount
      echo "<BR> Checking Buy Mode: $t1 | $t2 | $t3 ";
      if ($t1 == True and $t2 == True and $t3 == True){
        //Calculate Buy Price
        $pctToBuy = ($livePrice-$month6LowPrice)/($month6HighPrice-$month6LowPrice);
        $buyAmount = ($buyPrice/100)*$pctToBuy;
        //Write Coin, High Price Limit, Low Price Limit, Buy Amount - To Rule and Enable
        echo "<BR> Activate BUY MODE";
        WritetoRule($coinID, $ruleID, $projectedMaxPrice,$projectedMinPrice,$buyAmount, 0, 1,$ruleIDSell);
        if ($modeID <> 1){ logToSQL("CoinMode","Change Coin mode to 1 for $coinID", $userID, 1);}
      }else{
        echo "<BR> Activate FLAT MODE";
        WritetoRule($coinID,$ruleID,0,0, 0, 0, 3,$ruleIDSell);
        if ($modeID <> 3){ logToSQL("CoinMode","Change Coin mode to 3 for $coinID", $userID, 1);}
      }


  }
}

  function isSellMode($coinAry){
    $coinArySize = Count($coinAry);
    for ($i=0; $i<$coinArySize; $i++){
        $coinID = $coinAry[$i][0]; $Hr24Price = $coinAry[$i][4]; $D7Price = $coinAry[$i][6]; $livePrice = $coinAry[$i][10];
        $Hr1AveragePrice = $coinAry[$i][11]; $month6HighPrice = $coinAry[$i][2]; $month6LowPrice = $coinAry[$i][3]; $ruleIDSell = $coinAry[$i][8];
        $projectedMaxPrice = $coinAry[$i][12]; $projectedMinPrice = $coinAry[$i][13]; $ruleID = $coinAry[$i][1];
        $userID = $coinAry[$i][14]; $modeID = $coinAry[$i][15];
        $t1 = False; $t2 = False; $t3 = False;
        echo "<BR> Checking Sell Mode: $coinID";
        //24 Hour price is up

        $pctInc24Hours = (($livePrice - $Hr24Price)/$Hr24Price)*100;
        echo "<BR> 24HourPrice: $pctInc24Hours";
        if ($pctInc24Hours >= 10){$t1 = True;}
        //7Day Price is Up
        $pctInc7Day = (($livePrice - $D7Price)/$D7Price)*100;
        echo "<BR> 7DayPrice: $pctInc7Day";
        if ($pctInc7Day >= 10){ $t2 = True;}
        //Average is flat
        echo "<BR> 1hourAvgPrice : $Hr1AveragePrice";
        if ($Hr1AveragePrice <= 0.5 and $Hr1AveragePrice >= -0.5){ $t3 = True;}
        echo "<BR> Checking Sell Mode: $t1 | $t2 | $t3 ";
        if ($t1 == True and $t2 == True and $t3 == True){
          //Calculate Sell Price
          echo "<BR> Activate SELL MODE";
          //Write Coin, High Price Limit, Low Price Limit  - To Rule and Enable
          WritetoRule($coinID,$ruleID,$projectedMaxPrice,$projectedMinPrice, 0, 0, 2,$ruleIDSell);
          if ($modeID <> 2){ logToSQL("CoinMode","Change Coin mode to 2 for $coinID", $userID, 1);}
        }else{
          echo "<BR> Activate FLAT MODE";
          WritetoRule($coinID,$ruleID,0,0, 0, 0, 3,$ruleIDSell);
          if ($modeID <> 3){ logToSQL("CoinMode","Change Coin mode to 3 for $coinID", $userID, 1);}
        }
    }
  }


    function isFlatMode($coinAry){
      $coinArySize = Count($coinAry);
      for ($i=0; $i<$coinArySize; $i++){
          $coinID = $coinAry[$i][0]; $Hr24Price = $coinAry[$i][4]; $D7Price = $coinAry[$i][6];
          $Hr1AveragePrice = $coinAry[$i][11]; $month6HighPrice = $coinAry[$i][2]; $month6LowPrice = $coinAry[$i][3]; $ruleIDSell = $coinAry[$i][8];
          $ruleID = $coinAry[$i][1]; $livePrice = $coinAry[$i][10];
          $userID = $coinAry[$i][14]; $modeID = $coinAry[$i][15];
          $t1 = False; $t2 = False; $t3 = False;
          echo "<BR> Checking Flat Mode: $coinID";
          $pctInc24Hours = (($livePrice - $Hr24Price)/$Hr24Price)*100;
          echo "<BR> 24HourPrice: $pctInc24Hours";
          if ($pctInc24Hours > -5.0 and $pctInc24Hours < 10.0){$t1 = True;}

          $pctInc7Day = (($livePrice - $D7Price)/$D7Price)*100;
          echo "<BR> 7DayPrice: $pctInc7Day";
          if ($pctInc7Day > -5.0 and $pctInc7Day < 10.0){ $t2 = True;}

          //Average is Increasing
          if ($Hr1AveragePrice >= 0.5 and $Hr1AveragePrice <= -0.5){ $t3 = True;}
          echo "<BR> Checking Flat Mode: $t1 | $t2 | $t3 ";
          if ($t1 == True and $t2 == True and $t3 == True){
            //Calculate Sell Price
            echo "<BR> Activate FLAT MODE";
            WritetoRule($coinID,$ruleID,0,0, 0, 0, 3,$ruleIDSell);
            if ($modeID <> 3){ logToSQL("CoinMode","Change Coin mode to 3 for $coinID", $userID, 1);}
            //Disable Rule
          }

      }
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
        ,`USDBuyAmount`,`LiveCoinPrice`,`1HourAvgPrice`,`ProjectedPriceMax`,`ProjectedPriceMin`,`UserID`,`ModeID` FROM `CoinModePricesView`";
        $result = $conn->query($sql);
        //$result = mysqli_query($link4, $query);
        //mysqli_fetch_assoc($result);
        while ($row = mysqli_fetch_assoc($result)){
            $tempAry[] = Array($row['CoinID'],$row['RuleID'],$row['Avg6MonthMax'],$row['Avg6MonthMin'],$row['Live24HrChange'],$row['Last24HrChange'],$row['Live7DChange'],$row['Last7DChange'] //7
          ,$row['RuleIDSell'],$row['USDBuyAmount'],$row['LiveCoinPrice'],$row['1HourAvgPrice'],$row['ProjectedPriceMax'],$row['ProjectedPriceMin'],$row['UserID'],$row['ModeID']); //15
        }
        $conn->close();
        return $tempAry;
      }

  $coinsAry = getCoins();
  echo "<BR> Checking Coin Mode:";
  isBuyMode($coinsAry);
  isSellMode($coinsAry);
  isFlatMode($coinsAry);


?>
</html>
