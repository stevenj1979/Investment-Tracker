<html>
<?php
ini_set('max_execution_time', 900);
require('includes/newConfig.php');
//require '/home/stevenj1979/repositories/Sparkline/autoload.php';
include_once ('/home/stevenj1979/SQLData.php');
$apikey=getAPIKey();
$apisecret=getAPISecret();
$logToFileSetting = getLogToFile();

function WritetoRule($coinD, $ruleID, $highPrice, $lowPrice, $buyAmount, $enable, $type){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  if ($type == 1){$sql = "";}
  elseif ($type == 2){$sql = "";}
  else ($type == 3){$sql = "";}

  //print_r("<BR>".$sql);
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
      $t1 = False; $t2 = False; $t3 = False;
      //24 Hour price is down
      if ($Hr24Price <= -5.0){ $t1 = True;}
      //7Day Price is down
      if ($D7Price <= -5.0){ $t2 = True;}
      //Average is flat
      if ($Hr1AveragePrice <= 0.5 and $Hr1AveragePrice >= -0.5){ $t3 = True;}
      //if all = yes = calculate Buy Amount
      if ($t1 == True and $t2 == True and $t3 == True){
        //Calculate Buy Price
        $pctToBuy = (($livePrice/$month6LowPrice)*100)/($month6HighPrice-$month6LowPrice);
        $buyAmount = ($buyPrice/100)*$pctToBuy;
        //Write Coin, High Price Limit, Low Price Limit, Buy Amount - To Rule and Enable
        WritetoRule($coinID, $ruleID, $projectedMaxPrice,$projectedMinPrice,$buyAmount, 0, 1);
      }


  }

  function isSellMode($coinAry){
    $coinArySize = Count($coinAry);
    for ($i=0; $i<$coinArySize; $i++){
        $coinID = $coinAry[$i][0]; $Hr24Price = $coinAry[$i][4]; $D7Price = $coinAry[$i][6];
        $Hr1AveragePrice = $coinAry[$i][11]; $month6HighPrice = $coinAry[$i][2]; $month6LowPrice = $coinAry[$i][3]; $ruleIDSell = $coinAry[$i][8];
        $projectedMaxPrice = $coinAry[$i][12]; $projectedMinPrice = $coinAry[$i][13];
        $t1 = False; $t2 = False; $t3 = False;
        //24 Hour price is up
        if ($Hr24Price >= 10){$t1 = True;}
        //7Day Price is Up
        if ($D7Price >= 10){ $t2 = True;}
        //Average is flat
        if ($Hr1AveragePrice <= 0.5 and $Hr1AveragePrice >= -0.5){ $t3 = True;}
        if ($t1 == True and $t2 == True and $t3 == True){
          //Calculate Sell Price

          //Write Coin, High Price Limit, Low Price Limit  - To Rule and Enable
          WritetoRule($coinID,$ruleIDSell,$projectedMaxPrice,$projectedMinPrice, 0, 0, 2);
        }
    }


    function isFlatMode($coinAry){
      $coinArySize = Count($coinAry);
      for ($i=0; $i<$coinArySize; $i++){
          $coinID = $coinAry[$i][0]; $Hr24Price = $coinAry[$i][4]; $D7Price = $coinAry[$i][6];
          $Hr1AveragePrice = $coinAry[$i][11]; $month6HighPrice = $coinAry[$i][2]; $month6LowPrice = $coinAry[$i][3]; $ruleIDSell = $coinAry[$i][8];
          $t3 = False;

          //Average is Increasing
          if ($Hr1AveragePrice >= 0.5 and $Hr1AveragePrice <= -0.5){ $t3 = True;}
          if ($t3 == True){
            //Calculate Sell Price
            WritetoRule($coinID,$ruleIDSell,0,0, 0, 0, 3);
            //Disable Rule
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
        ,`USDBuyAmount`,`LiveCoinPrice`,`1HourAvgPrice`,`ProjectedPriceMax`,`ProjectedPriceMin` FROM `CoinModePricesView`";
        $result = $conn->query($sql);
        //$result = mysqli_query($link4, $query);
        //mysqli_fetch_assoc($result);
        while ($row = mysqli_fetch_assoc($result)){
            $tempAry[] = Array($row['CoinID'],$row['RuleID'],$row['Avg6MonthMax'],$row['Avg6MonthMin'],$row['Live24HrChange'],$row['Last24HrChange'],$row['Live7DChange'],$row['Last7DChange']
          ,$row['RuleIDSell'],$row['USDBuyAmount'],$row['LiveCoinPrice'],$row['1HourAvgPrice'],$row['ProjectedPriceMax'],$row['ProjectedPriceMin']);
        }
        $conn->close();
        return $tempAry;
      }

  $coinsAry = getCoins();
  isBuyMode($coinsAry);
  isSellMode($coinsAry);
  isFlatMode($coinsAry);
}

?>
</html>
