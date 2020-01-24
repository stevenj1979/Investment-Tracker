<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<?php require('includes/config.php');?>
<html>
<style>
<?php include 'style/style.css';
include '../../../NewSQLData.php'; ?>
</style>
<body>
<?php


//if not logged in redirect to login page
if(!$user->is_logged_in()){ header('Location: login.php'); exit(); }
?>

  <div class="header">
    <table><TH><table class="CompanyName"><td rowspan="2" class="CompanyName"><img src='Images/CBLogoSmall.png' width="40"></td><td class="CompanyName"><div class="Crypto">Crypto</Div><td><tr class="CompanyName">
        <td class="CompanyName"><Div class="Bot">Bot</Div></td></table></TH><TH>: Logged in as:</th><th> <i class="glyphicon glyphicon-user"></i>  <?php echo $_SESSION['username'] ?></th></Table><br>
  </div>
  <div class="topnav">
    <a href="Dashboard.php">Dashboard</a>
    <a href="Transactions.php">Transactions</a>
    <a href="Stats.php">Stats</a>
    <a href="BuyCoins.php">Buy Coins</a>
    <a href="SellCoins.php">Sell Coins</a>
    <a href="Profit.php">Profit</a>
    <a href="bittrexOrders.php">Bittrex Orders</a>
    <a href="Settings.php" >Settings</a><?php
    if ($_SESSION['AccountType']==1){echo "<a href='AdminSettings.php' class='active'>Admin Settings</a>";}
    ?>
  </div>
<div class"row">
<?php
if(!empty($_GET['addNew'])){ $_GET['addNew'] = null; submitNewCoin(); }
if(!empty($_GET['activateID'])){ displayActivate($_GET['activateID'],$_GET['activateBuyCoin']); }
if(!empty($_GET['deleteID'])){ displayDelete($_GET['deleteID']); }
if(!empty($_GET['addCoinReady'])){ runAddCoin($_POST['symbol'],$_POST['name'],$_POST['baseCurrency']); }

function submitNewCoin(){
  echo "<form action='editCoinAdmin.php?addCoinReady=Yes' method='post'>";
  addNewText('Symbol','symbol','',1,'eg BTC');
  addNewText('Name','name','',2,'eg Bit Coin');
  addNewText('Base Currency','baseCurrency','',3,'eg Base Currency');
  echo "<div class='settingsform'>
    <input type='submit' name='submit' value='Update' class='settingsformsubmit' tabindex='4'>
  </div>";
  echo "</form>";
}

function addNewText($RealName, $idName, $value, $tabIndex, $pHoolder){
  echo "<div class='settingsform'>
    <b>".$RealName."</b><br/>
    <input type='text' name='".$idName."' id='".$idName."' class='form-control input-lg' placeholder='$pHoolder' value='".$value."' tabindex='".$tabIndex."'>
  </div>";

}

function runAddCoin($symbol, $name, $baseCurrency){

  $conn = getSQL(rand(1,4));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "INSERT INTO `Coin`(`Symbol`, `Name`, `BaseCurrency`, `BuyCoin`) VALUES ('$symbol','$name','$baseCurrency',1)";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();
  header('Location: AdminSettings.php');
}

function displayActivate($iD, $buyCoin){
  Echo " ID $iD : buyCoin $buyCoin";
  $conn = getSQL(rand(1,4));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  if ($buyCoin == 0){ $sql = "UPDATE `Coin` SET `BuyCoin`= 1 WHERE `ID` = $iD"; }
  else {$sql = "UPDATE `Coin` SET `BuyCoin`= 0 WHERE `ID` = $iD";}
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();
  header('Location: AdminSettings.php');
}

function displayDelete($iD){
  Echo " ID $iD";
  $conn = getSQL(rand(1,4));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "DELETE FROM `Coin` WHERE `ID` = $iD";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();
  header('Location: AdminSettings.php');
}

?>
</body>
</html>
