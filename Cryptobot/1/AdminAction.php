<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<?php require('includes/config.php');?>
<html>
<style>
<?php include 'style/style.css';
include_once ('../../../../SQLData.php');
?>
</style>
<body>
<?php


//if not logged in redirect to login page
if(!$user->is_logged_in()){ header('Location: login.php'); exit(); }
?>
<div class"row">
    <div class="header">
      <table><TH>CryptoBot: Logged in as:</th><th>  <i class="glyphicon glyphicon-user"></i> <?php echo $_SESSION['username'] ?></th></Table><br>
    </div>
    <div class="topnav">
      <a href="Dashboard.php">Dashboard</a>
      <a href="Transactions.php">Transactions</a>
      <a href="Stats.php">Stats</a>
      <a href="BuyCoins.php">Buy Coins</a>
      <a href="SellCoins.php">Sell Coins</a>
      <a href="Profit.php">Profit</a>
      <a href="Settings.php">User Settings</a>
      <a href="BuySettings.php" class="active">Buy Settings</a>
      <a href="SellSettings.php">Sell Settings</a>
  </div>
<?php
echo "OMG!! ".$_POST['New_Amount'];
if(!empty($_GET['id'])){
  processSubscription($_GET['length'],$_GET['UserID']);
  closeSubscription($_GET['id'],$_GET['username']);
}
if ($_GET['promote'] == 'Yes'){
   PromoteAdmin($_POST['User_Name']);
}
if (isset($_GET['fixTransaction'])){
  //echo "<BR>".$_POST['New_Amount']." : ".$_POST['Trans_ID'];
   fixTransaction($_POST['New_Amount'],$_POST['Trans_ID']);
}
if (isset($_GET['buyCoin'])){
  $id = $_GET['buyCoin'];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `Coin`
            SET `BuyCoin`= CASE
             WHEN `BuyCoin` = 1 THEN 0
             WHEN `BuyCoin` = 0 THEN 1
             END
            WHERE `ID` = $id";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  //logAction("reopenTransaction: ".$sql, 'SQL_UPDATE', 0);
  //newLogToSQL("reopenTransaction",$sql,3,0,"SQL","TransactionID:$id");
}
if (isset($_GET['doNotBuy'])){
  $id = $_GET['doNotBuy'];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `Coin`
            SET `DoNotBuy`= CASE
             WHEN `DoNotBuy` = 1 THEN 0
             WHEN `DoNotBuy` = 0 THEN 1
             END
            WHERE `ID` = $id";

  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  //logAction("reopenTransaction: ".$sql, 'SQL_UPDATE', 0);
  //newLogToSQL("reopenTransaction",$sql,3,0,"SQL","TransactionID:$id");
}


function fixTransaction($amount, $transactionID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call CompleteBittrexBuyUpdateAmount($transactionID, $amount);";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {echo "Error: " . $sql . "<br>" . $conn->error;}
  $conn->close();
}

function PromoteAdmin($user_name){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "UPDATE `User` SET `AccountType`= 1 WHERE `UserName` =  $user_name";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {echo "Error: " . $sql . "<br>" . $conn->error;}
  $conn->close();
}

function closeSubscription($id,$userID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "UPDATE `Subscription` SET `Status`= 'Closed' WHERE `ID` = $id ";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {echo "Error: " . $sql . "<br>" . $conn->error;}
  $conn->close();
}

function processSubscription($length,$userID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "UPDATE `User` SET `Active`= 'Yes', `ExpiryDate`= if(`ExpiryDate` < CURRENT_DATE, DATE_ADD(CURRENT_DATE, INTERVAL $length MONTH),DATE_ADD(`ExpiryDate`, INTERVAL $length MONTH) ) WHERE `ID` = $userID";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {echo "Error: " . $sql . "<br>" . $conn->error;}
  $conn->close();
}



header('Location: AdminSettings.php');
?>
</body>
</html>
