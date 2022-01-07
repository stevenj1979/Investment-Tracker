<html>
<?php
ini_set('max_execution_time', 300);
require('includes/newConfig.php');

include_once ('/home/stevenj1979/SQLData.php');
include_once ('/home/stevenj1979/Encrypt.php');

$apikey=getAPIKey();
$apisecret=getAPISecret();

function clearWeeklyCoinSwaps(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `Transaction` SET `NoOfCoinSwapsThisWeek` = 0 WHERE `Status` = 'Open'";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("clearWeeklyCoinSwaps: ".$sql, 'SellCoin', 0);
}

function getSpreadBetSettings(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `ID`, `SpreadBetRuleID`, `Hr1BuyPrice`, `Hr24BuyPrice`, `D7BuyPrice`, `NextReviewDate`, `PctProfitSell`, `NoOfTransactions`, `LowestPctProfit`, `AvgTimeToSell`
  FROM `SpreadBetSettings`";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['SpreadBetRuleID'],$row['Hr1BuyPrice'],$row['Hr24BuyPrice'],$row['D7BuyPrice'],$row['NextReviewDate'],$row['PctProfitSell'],$row['NoOfTransactions']
      ,$row['LowestPctProfit'],$row['AvgTimeToSell']);
  }
  $conn->close();
  return $tempAry;
}

function updateSpreadBetSettings($Hr24BuyPrice,$D7BuyPrice, $pctProfitSell,$ID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `SpreadBetSettings` SET `Hr24BuyPrice`= $Hr24BuyPrice,`D7BuyPrice`= $D7BuyPrice,`NextReviewDate`= date_add(now(),INTERVAL 1 MONTH),`PctProfitSell`= $pctProfitSell
  WHERE `SpreadBetRuleID` = $ID;";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateSpreadBetSettings: ".$sql, 'SellCoin', 0);
}

function resetSpreadBetSettings(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `SpreadBetSettings` SET `NoOfTransactions`= 0,`LowestPctProfit`= 0,`AvgTimeToSell`= 0, `NextReviewDate`= date_add(`NextReviewDate`, INTERVAL 1 MONTH)";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("resetSpreadBetSettings: ".$sql, 'SellCoin', 0);
}

function spreadBetSettingsUpdate(){
  $spreadBet = getSpreadBetSettings();
  $spreadBetSize = count($spreadBet);
  $resetFlag = False;
  for ($i=0;$i<$spreadBetSize; $i++){
    $ID = $spreadBet[$i][0]; $spreadBetRuleID  = $spreadBet[$i][1]; $nextReviewDate = $spreadBet[$i][5]; $pctProfitSell = $spreadBet[$i][6]; $noOfTrans = $spreadBet[$i][7];
    $lowestPctProfit  = $spreadBet[$i][8]; $avgTimeToSell  = $spreadBet[$i][9]; $Hr24BuyPrice = $spreadBet[$i][3]; $D7BuyPrice = $spreadBet[$i][4];
    if ($noOfTrans == 0){
      //Raise Pct to Buy
      if ($Hr24BuyPrice < -0.5 and $D7BuyPrice < -0.5){
        $Hr24BuyPrice = $Hr24BuyPrice + 0.25;
        $D7BuyPrice = $D7BuyPrice + 0.25;
      }
    }elseif ($nextReviewDate <= date("Y-m-d H:i:s", time())){
        $resetFlag = True;
        if ($lowestPctProfit <= -9){
          //Lower 24 and 7D Pct to Buy
          if ($Hr24BuyPrice > -10 and $D7BuyPrice > -10){
            $Hr24BuyPrice = $Hr24BuyPrice - 0.25;
            $D7BuyPrice = $D7BuyPrice - 0.25;
          }
        }elseif ($avgTimeToSell >= 20200){
          //lower Pct to Sell
          if ($pctProfitSell > 0.5){
            $pctProfitSell = $pctProfitSell - 0.25;
          }
        }
    }
    updateSpreadBetSettings($Hr24BuyPrice,$D7BuyPrice, $pctProfitSell,$ID);
  }

  if ($resetFlag){
    //Reset AvgTime and No of Transactions
    $avgTimeToSell = 0; $lowestPctProfit = 0; $noOfTrans = 0;
    resetSpreadBetSettings();
  }
}

function getOpenBuyBackData(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "SELECT `ID`, `MinsFromAdd`,`UserID` FROM `BuyBackView`";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['MinsFromAdd'],$row['UserID']);
  }
  $conn->close();
  return $tempAry;
}

function closeOpenBuyBack($id,$userID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "call SaveBuyBackKitty($id,$userID);";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("closeBuyBack: ".$sql, 'SellCoin', 0);
}


function clearBuyBack($mins){
  $buyBackAry = getOpenBuyBackData();
  $buyBackArySize = count($buyBackAry);
  for ($b=0; $b<$buyBackArySize; $b++){
    $bBID =$buyBackAry[$b][0]; $minsFromAdd = $buyBackAry[$b][1]; $userID = $buyBackAry[$b][2];
    if ($minsFromAdd >= $mins){
      closeOpenBuyBack($bBID,$userID);
      LogToSQL("WeeklyScript","clearBuyBack($mins) $minsFromAdd",3,1);
    }
  }
}

function setBuySellPriceforProfit(){
  $coinIDs = getCoinIDs();
  $coinIDsSize = count($coinIDs);
  $userIDs = getUserID();
  $userIDsSize = count($userIDs);
  for ($e=0; $e<$coinIDsSize; $e++){
    $CoinID = $coinIDs[$e][0];
    for ($w=0; $w<$userIDsSize; $w++){
        $userID = $userIDs[$w][0]
        buySellProfitEnable($CoinID,$userID,0,0);
    }
  }
}

function clearSQLLog($days){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "Delete FROM `ActionLog` WHERE datediff(now(),`DateTime`) > $days";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("clearSQLLog: ".$sql, 'SellCoin', 0);

}

// MAIN PROGRAMME
clearWeeklyCoinSwaps();
spreadBetSettingsUpdate();
clearBuyBack(5760);
clearSQLLog(90);
setBuySellPriceforProfit();
?>
</html>
