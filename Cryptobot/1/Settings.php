<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<?php require('includes/config.php');
  //include 'includes/functions.php';
  include_once ('/home/stevenj1979/SQLData.php');
  include_once ('/home/stevenj1979/Encrypt.php');
  include_once '../includes/newConfig.php';
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

  $sql = "SELECT `ID`,`AccountType`,`UserName`,`Active`,`APIKey`,`APISecret`,`EnableDailyBTCLimit`,`EnableTotalBTCLimit`,`DailyBTCLimit`,`TotalBTCLimit`,`Email`,`BTCBuyAmount`,`BaseCurrency`,`KEK`
  FROM `UserConfigView` WHERE `ID` = $userID";
	//echo $sql;
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['AccountType'],$row['UserName'],$row['Active'],$row['APIKey'],$row['APISecret'],$row['EnableDailyBTCLimit'],$row['EnableTotalBTCLimit'],
      $row['DailyBTCLimit'],$row['TotalBTCLimit'],$row['Email'],$row['BTCBuyAmount'],$row['BaseCurrency'],$row['KEK']);
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
  $encAry = Encrypt($apisecret);
  $enc_apiSecret = $encAry['data'];
  $enc_KEK = $encAry['secret'];
  $sql = "UPDATE `UserConfig` SET `APIKey`='$apikey', `APISecret`='$enc_apiSecret',`EnableDailyBTCLimit`=$enableDailyBTCLimitNum
         ,`EnableTotalBTCLimit`=$enableTotalBTCLimitNum,`DailyBTCLimit`=$dailyBTCLimit,`TotalBTCLimit`=$totalBTCLimit,`BTCBuyAmount`=$BTCBuyAmount, `BaseCurrency`='$userBaseCurrency',`KEK`='$enc_KEK'
         WHERE `UserID` = $userID;
         UPDATE `User` SET `UserName`='$newusername',`Email`='$email' WHERE `ID` = $userID";
  //print_r($sql);
  if ($conn->multi_query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function getSequence($userID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `SellRuleID`,`Sequence` FROM `SellSequence` WHERE `UserID` = $userID";
	//echo $sql;
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['SellRuleID'],$row['Sequence']);
  }
  $conn->close();
  return $tempAry;
}


$userDetails = getUserIDs($_SESSION['ID']);
//$userSettings = getConfig($_SESSION['ID']);
  $sequence = getSequence($_SESSION['ID']);
  $sequenceCount = count($sequence);
  displayHeader(7);
  $kek = $userDetails[0][13];
  $apisecret =Decrypt($kek,$userDetails[0][5]);
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
                <input type="text" name="API_Secret" id="API_Secret" class="form-control input-lg" placeholder="User Name" value="<?php echo $apisecret; ?>" tabindex="4">
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
                        <option value='".$option4."'>".$option4."</option></select></div><BR>";

                        Echo "<select name='$name' size='3'>";
                        for ($i=0; $i<$sequenceCount; $i++){
                          $sellRuleID = $sequence[$i][0]; $newSeq = $sequence[$i][1];
                          echo "<option value='$newSeq'>$newSeq:$sellRuleID</option>";
                        }
                        echo "</select>";
                        echo "<input type='submit' name='publishHr1' value='+'><input type='submit' name='removeHr1' value='-'>";
                        ?>
              <div class="form-group">
                <input type="submit" name="submit" value="Update" class="form-control input-lg" tabindex="8">
              </div>
            </form><?php
            displaySideColumn(); ?>

</body>
</html>
