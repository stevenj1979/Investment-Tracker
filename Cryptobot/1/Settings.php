<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<?php require('includes/config.php');
  //include 'includes/functions.php';
  include_once ('/home/stevenj1979/SQLData.php');
?>
<html>
<style>
<?php include 'style/style.css'; ?>
</style>
<body>
<?php


//if not logged in redirect to login page
if(!$user->is_logged_in()){ header('Location: login.php'); exit(); }

if(isset($_POST['submit'])){

  if (!empty($_GET['user'])){
    if(empty($_POST['BTCBuyAmount'])){$_POST['BTCBuyAmount'] = 0;}
    if(empty($_POST['dailyBTCLimit'])){$_POST['dailyBTCLimit'] = 0;}
    if(empty($_POST['enableDailyBTCLimit'])){$_POST['enableDailyBTCLimit'] = 0;}
    if(empty($_POST['totalBTCLimit'])){$_POST['totalBTCLimit'] = 0;}
    if(empty($_POST['enableTotalBTCLimit'])){$_POST['enableTotalBTCLimit'] = 0;}
    echo "Here1!";
    updateUser($_SESSION['ID'],$_POST['newusername'],$_POST['email'],$_POST['API_Key'],$_POST['API_Secret'],$_POST['dailyBTCLimit'],$_POST['totalBTCLimit'],$_POST['enableDailyBTCLimit'],$_POST['enableTotalBTCLimit'],$_POST['BTCBuyAmount'],$_POST['userBaseCurrency']);
    echo "Here2!";

    //header('Location: Settings.php');
  }


}//end if submit
//define page title
$title = 'CryptoBot';

//include header template
require('layout/header.php');


function getUserIDs($userID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `ID`,`AccountType`,`UserName`,`Active`,`APIKey`,`APISecret`,`EnableDailyBTCLimit`,`EnableTotalBTCLimit`,`DailyBTCLimit`,`TotalBTCLimit`,`Email`,`BTCBuyAmount`,`BaseCurrency`
  FROM `UserConfigView` WHERE `ID` = $userID";
	//echo $sql;
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['AccountType'],$row['UserName'],$row['Active'],$row['APIKey'],$row['APISecret'],$row['EnableDailyBTCLimit'],$row['EnableTotalBTCLimit'],
      $row['DailyBTCLimit'],$row['TotalBTCLimit'],$row['Email'],$row['BTCBuyAmount'],$row['BaseCurrency']);
  }
  $conn->close();
  return $tempAry;
}


function updateUser($userID, $newusername, $email, $apikey, $apisecret,$dailyBTCLimit, $totalBTCLimit,$enableDailyBTCLimit, $enableTotalBTCLimit, $BTCBuyAmount, $userBaseCurrency){
  if ($enableDailyBTCLimit == "Yes"){$enableDailyBTCLimitNum = 1;}else{$enableDailyBTCLimitNum = 0;}
  if ($enableTotalBTCLimit == "Yes"){$enableTotalBTCLimitNum = 1;}else{$enableTotalBTCLimitNum = 0;}
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
    echo "Error";
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call updateUserConfig('$newusername','$email','$apikey', '$apisecret',$enableDailyBTCLimitNum,$enableTotalBTCLimitNum,$dailyBTCLimit,$totalBTCLimit,$BTCBuyAmount, $userID, '$userBaseCurrency') ";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}


$userDetails = getUserIDs($_SESSION['ID']);
//$userSettings = getConfig($_SESSION['ID']);
?>
<div class='header'>
<table><TH><table class='CompanyName'><td rowspan='2' class='CompanyName'><img src='Images/CBLogoSmall.png' width='40'></td><td class='CompanyName'><div class='Crypto'>Crypto</Div><td><tr class='CompanyName'>
<td class='CompanyName'><Div class='Bot'>Bot</Div></td></table></TH><TH>: Logged in as:</th><th> <i class='glyphicon glyphicon-user'></i>  <?php echo $_SESSION['username'] ?></th></Table><br>
</div>
<?php
//$tempOutput = getNewHeader();
?>
  <div class="topnav">
    <a href="Dashboard.php">Dashboard</a>
    <a href="Transactions.php">Transactions</a>
    <a href="Stats.php">Stats</a>
    <a href="BuyCoins.php">Buy Coins</a>
    <a href="SellCoins.php">Sell Coins</a>
    <a href="Profit.php">Profit</a>
    <a href="bittrexOrders.php">Bittrex Orders</a>
    <a href="Settings.php" class="active">Settings</a><?php
    if ($_SESSION['AccountType']==1){echo "<a href='AdminSettings.php'>Admin Settings</a>";}
//echo $tempOutput;
?>
  </div>
      <div class="row">
            <div class="settingCol1">
                <!--<h3>User Settings</h3>-->
                <h3><a href='Settings.php'>User Settings</a> &nbsp > &nbsp <a href='BuySettings.php'>Buy Settings</a> &nbsp > &nbsp <a href='SellSettings.php'>Sell Settings</a></h3>
                <form action="Settings.php?user=Yes" method="post">
              <div class="form-group">
                <b>UserName: </b><br/>
                <input type="text" name="newusername" id="newusername" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][2]; ?>" tabindex="1">
              </div>
              <div class="form-group">
                <b>Email: </b><br/>
                <input type="text" name="email" id="email" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][10]; ?>" tabindex="2">
              </div>
              <div class="form-group">
                <b>API Key: </b><br/>
                <input type="text" name="API_Key" id="API_Key" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][4]; ?>" tabindex="3">
                <p class="comments">Bittrex API Key</p>
              </div>
              <div class="form-group">
                <b>API Secret: </b><br/>
                <input type="text" name="API_Secret" id="API_Secret" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][5]; ?>" tabindex="4">
                <p class="comments">Bittrex Secret API Key</p>
              </div>
              <div class="form-group">
                <b>BTC Buy Amount: </b><br/>
                <input type="text" name="BTCBuyAmount" id="BTCBuyAmount" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][11]; ?>" tabindex="5">
                <p class="comments">Amount in BTC for each buy</p>
              </div>
                  <?php if ($userDetails[0][6] == 1){ $option1 = "Yes"; $option2 = "No";}else{$option1 = "No"; $option2 = "Yes";}?>
                    <div class='settingsform'>
                      <b>Enable Daily BTC Limit: </b><br/><select name='enableDailyBTCLimit' id='enableDailyBTCLimit' class='enableTextBox'><?php
                        echo "<option value='".$option1."'>".$option1."</option>
                        <option value='".$option2."'>".$option2."</option></select></div>";?>
                  <div class="form-group">
                    <b>Daily BTC Limit: </b><br/>
                    <input type="text" name="dailyBTCLimit" id="dailyBTCLimit" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][8]; ?>" tabindex="6">
                    <p class="comments">Amount in BTC for each buy</p>
                  </div>
                  <?php if ($userDetails[0][7] == 1){ $option1 = "Yes"; $option2 = "No";}else{$option1 = "No"; $option2 = "Yes";}?>
                    <div class='settingsform'>
                      <b>Enable Total BTC Limit: </b><br/><select name='enableTotalBTCLimit' id='enableTotalBTCLimit' class='enableTextBox'><?php
                        echo "<option value='".$option1."'>".$option1."</option>
                        <option value='".$option2."'>".$option2."</option></select></div>";?>
                  <div class="form-group">
                    <b>Total BTC Limit: </b><br/>
                    <input type="text" name="totalBTCLimit" id="totalBTCLimit" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][9]; ?>" tabindex="7">
                    <p class="comments">Amount in BTC for each buy</p>
                  </div>
                  <?php if ($userDetails[0][12] == 'BTC'){ $option1 = "BTC"; $option2 = "USDT";$option3 = "ETH";$option4 = "All";}
                  elseif ($userDetails[0][12] == 'USDT'){$option1 = "USDT"; $option2 = "BTC";$option3 = "ETH";$option4 = "All";}
                  elseif ($userDetails[0][12] == 'ETH'){$option1 = "ETH"; $option2 = "BTC";$option3 = "USDT";$option4 = "All";}
                  elseif ($userDetails[0][12] == 'All'){$option1 = "All"; $option2 = "BTC";$option3 = "ETH";$option4 = "USDT";}?>
                    <div class='settingsform'>
                      <b>User Base Currency: </b><br/><select name='userBaseCurrency' id='userBaseCurrency' class='enableTextBox'><?php
                        echo "<option value='".$option1."'>".$option1."</option>
                        <option value='".$option2."'>".$option2."</option>
                        <option value='".$option3."'>".$option3."</option>
                        <option value='".$option4."'>".$option4."</option></select></div>";?>
              <div class="form-group">
                <input type="submit" name="submit" value="Update" class="form-control input-lg" tabindex="8">
              </div>
            </form>
            </div>
       </div>

       <div class="footer">
         <hr>
         <input type="button" onclick="location='logout.php'" value="Logout"/>
       </div>

</body>
</html>
