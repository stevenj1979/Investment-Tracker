<?php
require('../../SQLDb.php');

function getBittrexRequestsLoc(){
  $tempAry = [];
  $conn = getNewSQL(rand(1,4));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Type`,`BittrexRef`,`ActionDate`,`CompletionDate`,`Status`,`SellPrice`,`UserName`,`APIKey`,`APISecret`,`Symbol`,`Amount`,`CoinPrice`,`UserID`, `Email`,`OrderNo`,`TransactionID`,`BaseCurrency`,`RuleID`,`DaysOutstanding`,`timeSinceAction`,`CoinID`,
  `RuleIDSell`,`LiveCoinPrice`,`TimetoCancelBuy`,`BuyOrderCancelTime` FROM `BittrexOutstandingRequests` WHERE `Status` = '1'";
  $conn->query("SET time_zone = '+04:00';");
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['Type'],$row['BittrexRef'],$row['ActionDate'],$row['CompletionDate'],$row['Status'],$row['SellPrice'],$row['UserName'],$row['APIKey'],$row['APISecret'],$row['Symbol'],$row['Amount'],
    $row['CoinPrice'],$row['UserID'],$row['Email'],$row['OrderNo'],$row['TransactionID'],$row['BaseCurrency'],$row['RuleID'],$row['DaysOutstanding'],$row['timeSinceAction'],$row['CoinID'],$row['RuleIDSell'],$row['LiveCoinPrice'],
    $row['TimetoCancelBuy'],$row['BuyOrderCancelTime']);
  }
  $conn->close();
  return $tempAry;
}

function deleteFromBittrexActionLoc($bittrexRef){
  $conn = getNewSQL(rand(1,4));
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
}

function writeBittrexActionBuyLoc($coinID,$transactionID,$userID,$type,$bittrexRef,$date,$status,$sellPrice,$ruleID){
    $conn = getNewSQL(rand(1,4));
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
}
?>
