<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<?php require('includes/config.php');
include_once '../includes/newConfig.php';?>
<html>
<style>
<?php include 'style/style.css';
include_once ('../../../../SQLData.php');
?>
</style>
<body>
<?php
$apiVersion = 3;

//if not logged in redirect to login page
if(!$user->is_logged_in()){ header('Location: login.php'); exit(); }
displayHeader(6);
echo "UUID ".$_GET['uuid']." | ".$_GET['apikey']." | ".$_GET['apisecret']." | ".$_GET['transactionID'];
//echo "<BR> EMPTY ".empty($_GET['uuid']);
Echo "<BR> HERE 1 | ".$_GET['uuid'];
if(!empty($_GET['Ref'])){
  $oldRef = $_GET['Ref'];
  echo "<form action='bittrexOrders.php?updateUUID=Yes' method='get'>";
  echo "Current Ref: <input type='text' name='refTxt' readonly value='$oldRef'>";
  echo "New Ref: <input type='text' name='refTxt' readonly value=''>";
  echo "<input type='submit' name='submit' value='UpdateRef' class='settingsformsubmit' tabindex='1'></form>";
}

if(!empty($_GET['uuid'])){
  Echo "<BR> HERE 2 | ";
  $resultOrd = bittrexOrder($_GET['apikey'],$_GET['apisecret'],$_GET['uuid'],$apiVersion);
  //logAction("bittrexOrder: ".$resultOrd, 'BuySell');
  echo "CANCEL ".$_GET['uuid'];
  var_dump($resultOrd);
  if ($resultOrd['status'] == 'OPEN'){

    $orderQty = $resultOrd["quantity"];$qtySold = $resultOrd["fillQuantity"];
    $orderQtyRemaining = $orderQty-$qtySold;
    echo "<BR> OrderQtyRemaining: $orderQtyRemaining = $orderQty-$qtySold;";
    logAction("bittrexOrder: orderQty $orderQty | orderQtyRemaining $orderQtyRemaining | qtySold $qtySold", 'BuySell',0);
    Echo "<BR> HERE 3 | Type: ".$_GET['type']." Qty: $orderQty | QtyRemaining $orderQtyRemaining";
    $uuid = $_GET['uuid']; $apikey = $_GET['apikey']; $apisecret = $_GET['apisecret']; $transID =  $_GET['transactionID'];
    if ($orderQty == $orderQtyRemaining) {
      if (($_GET['type'] == 'Sell') OR ($_GET['type'] == 'SpreadSell')){
        Echo "<BR> HERE 4 | ";
        echo "<br>bittrexSellCancel(".$_GET['uuid'].", ".$_GET['transactionID'].")";

        $result = bittrexCancel($apikey,$apisecret,$uuid,$apiVersion);
        $canStatus = $result['status'];
        if ($canStatus == 'CLOSED'){
          bittrexSellCancel($uuid, $transID,'ManualBittrexSellCancel');
          newLogToSQL("bittrexSellCancel","bittrexSellCancel($uuid, $transID,'ManualBittrexCancel'); $canStatus",3,1,"SQL CALL","UUID:$uuid");
          //header('Location: bittrexOrders.php');
        }
        logAction("Bittrex Cancel 1 : ".json_encode($result), 'BuySell',0);

      }else{
        echo "<br>bittrexBuyCancel(".$_GET['uuid'].", ".$_GET['transactionID'].")";

        $result = bittrexCancel($apikey,$apisecret,$uuid,$apiVersion);
        $canStatus = $result['status'];
        if ($canStatus == 'CLOSED'){
          bittrexBuyCancel($uuid, $transID,'ManualBittrexBuyCancel');
          newLogToSQL("bittrexBuyCancel","bittrexBuyCancel($uuid, $transID,'ManualBittrexBuyCancel'); $canStatus",3,1,"SQL CALL","UUID:$uuid");
          //header('Location: bittrexOrders.php');
        }
        logAction("Bittrex Cancel 2 : ".json_encode($result), 'BuySell',0);

      }
    }else{
      if (($_GET['type'] == 'Sell') OR ($_GET['type'] == 'SpreadSell')){
        //bittrexCopyTransNewAmount($_GET['transactionID'],$orderQtyRemaining);
        //Update QTY
        //bittrexUpdateSellQty($_GET['transactionID'],$orderQty-$orderQtyRemaining);
        //bittrexSellCancel($_GET['uuid'], $_GET['transactionID']);
        //New Transaction
        //$result = bittrexCancel($_GET['apikey'],$_GET['apisecret'],$_GET['uuid']);
          Echo "<BR> HERE 5 | ";
        $result = bittrexCancel($apikey,$apisecret,$uuid,$apiVersion);
        $canStatus = $result['status'];
        if ($canStatus == 'CLOSED'){
          $newOrderNo = "ORD".$coin.date("YmdHis", time())."0";
          //sendtoSteven($transactionID,$orderQtyRemaining."_".$qtySold."_".$orderQty, $newOrderNo."_".$orderNo, "SELL - Greater 28 days");
            Echo "<BR> bittrexCopyTransNewAmount($transID,$orderQtyRemaining,$newOrderNo); ";
          bittrexCopyTransNewAmount($transID,$orderQtyRemaining,$newOrderNo);
          //Update QTY
          Echo "<BR> bittrexUpdateSellQty($transID,$qtySold); ";
          bittrexUpdateSellQty($transID,$qtySold);
          Echo "<BR> bittrexSellCancel($uuid, $transID,'ManualBittrexSellCancelPartialBuy'); ";
          bittrexSellCancel($uuid, $transID,'ManualBittrexSellCancelPartialBuy');

          if ($sendEmail){
            $subject = "Coin Sale: ".$coin." RuleID:"."0"." Qty: ".$orderQty." : ".$orderQtyRemaining;
            $from = 'Coin Sale <sale@investment-tracker.net>';
            //sendSellEmail($email, $coin, $orderQty-$orderQtyRemaining, $finalPrice, $orderNo, $totalScore,$profitPct,$profit,$subject,$userName,$from);
          }

          //break;
        }
        logAction("Bittrex Cancel 3 : ".json_encode($result), 'BuySell',0);
      }else {
          Echo "<BR> HERE 6 | ";
        bittrexUpdateBuyQty($transID, $orderQty-$orderQtyRemaining);
        $result = bittrexCancel($apikey,$apisecret,$uuid,$apiVersion);
        $canStatus = $result['status'];
        if ($canStatus == 'CLOSED'){
          bittrexBuyCancel($uuid, $transID,'ManualBittrexBuyCancel');
        }
        logAction("Bittrex Cancel 4 : ".json_encode($result), 'BuySell',0);
      }
    }
  }elseif ($resultOrd['status'] == 'CLOSED'){
    if (($_GET['type'] == 'Sell') OR ($_GET['type'] == 'SpreadSell')){
      $uuid = $_GET['uuid']; $apikey = $_GET['apikey']; $apisecret = $_GET['apisecret']; $transID =  $_GET['transactionID'];
      $orderQty = $resultOrd["quantity"];$qtySold = $resultOrd["fillQuantity"];
      echo "<BR> ORDER Closed: $orderQtyRemaining = $orderQty-$qtySold; ";
      if ($qtySold == 0){
        bittrexSellCancel($uuid, $transID,'ManualBittrexBuyCancel');
        echo "<BR> bittrexSellCancel($uuid, $transID,'ManualBittrexBuyCancel'); ";
      }
    }
  }
  header('Location: bittrexOrders.php');
}


function bittrexBuyCancelLoc($bittrexRef, $transactionID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call CancelBittrexBuy('$bittrexRef', $transactionID);";
  print_r("<br>".$sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("bittrexBuyCancel: ".$sql, 'BuySell',0);
}

function bittrexUpdateBuyQtyLoc($transactionID, $quantity){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call CompleteBittrexBuyUpdateAmount($transactionID,$quantity);";
  print_r("<br>".$sql);
  if ($conn->query($sql) === TRUE) {echo "New record created successfully";
  } else {echo "Error: " . $sql . "<br>" . $conn->error;}
  $conn->close();
}

function bittrexUpdateSellQtyLoc($transactionID, $quantity){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call CompleteBittrexSellUpdateAmount($transactionID,$quantity);";
  print_r("<br>".$sql);
  if ($conn->query($sql) === TRUE) {echo "New record created successfully";
  } else {echo "Error: " . $sql . "<br>" . $conn->error;}
  $conn->close();
}

function bittrexCopyTransNewAmountLoc($transactionID, $quantity){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call CopyTransNewAmount($transactionID,$quantity);";
  print_r("<br>".$sql);
  if ($conn->query($sql) === TRUE) {echo "New record created successfully";
  } else {echo "Error: " . $sql . "<br>" . $conn->error;}
  $conn->close();
}


function bittrexSellCancelLoc($bittrexRef, $transactionID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call CancelBittrexSell('$bittrexRef', $transactionID);";
  print_r("<br>".$sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("bittrexSellCancel: ".$sql, 'BuySell',0);
}

function bittrexCancelLoc($apikey, $apisecret, $uuid){
    $nonce=time();
    $uri='https://bittrex.com/api/v1.1/market/cancel?apikey='.$apikey.'&uuid='.$uuid.'&nonce='.$nonce;
    $sign=hash_hmac('sha512',$uri,$apisecret);
    $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
    $balance = $obj["success"];
    return $balance;
    logAction("bittrexCancel: ".$uri, 'BuySell',0);
}

function changeTransStatusLoc($orderNo, $transactionID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $userID = $_SESSION['ID'];
  $sql = "UPDATE `Transaction` SET `Status`= 'Open' WHERE `OrderNo` = '$orderNo' and `Status`= 'Pending' and `ID` = $transactionID";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();

}

function deleteItemLoc($id){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $userID = $_SESSION['ID'];
  $sql = "DELETE FROM `BittrexAction` WHERE `bittrexRef` = '$id'";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();
  //header('Location: bittrexOrders.php');
}



?>
</body>
</html>
