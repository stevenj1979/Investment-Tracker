<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<?php require('includes/config.php');?>
<html>
<style>
<?php include 'style/style.css'; ?>
</style>
<body>
<?php


//if not logged in redirect to login page
if(!$user->is_logged_in()){ header('Location: login.php'); exit(); }

if(isset($_POST['submit'])){
  if(isset($_POST['buyWithScore'])){
    $tempChecked = 1;
  }else{
    $tempChecked = 0;
  }

  if(isset($_POST['sellWithScore'])){
    $tempSellChecked = 1;
  }else{
    $tempSellChecked = 0;
  }
	//if (!isset($_POST['username'])) $error[] = "Please fill out all fields";
	//if (!isset($_POST['email'])) $error[] = "Please fill out all fields";
  //if (!isset($_POST['API_Key'])) $error[] = "Please fill out all fields";
  //if (!isset($_POST['API_Secret'])) $error[] = "Please fill out all fields";
  if (isset($_POST['user'])){
    updateUser($_SESSION['ID'],$_POST['newusername'],$_POST['email'],$_POST['API_Key'],$_POST['API_Secret']);
  }elseif (isset($_POST['buy'])){
    echo "BUY!!! ";
    updateBuyConfig($_SESSION['ID'],$_POST['BTC'],$_POST['MarketCapBuyPct'],$_POST['VolumeBuyPct'],$_POST['BuyOrdersPct'],$tempChecked,$_POST['score']);
  }elseif (isset($_POST['sell'])){
    updateSellConfig($_SESSION['ID'],$_POST['CoinSalePct'],$_POST['MarketCapSellPct'],$_POST['VolumeSellPct'],$_POST['SellOrdersPct'],$_POST['MinPctGain'],$tempSellChecked,$_POST['SellScore']);
  }
	//Update User table


}//end if submit
//define page title
$title = 'CryptoBot';

//include header template
require('layout/header.php');
include '../../../NewSQLData.php';

function getUserIDs($userID){
  $conn = getSQL(rand(1,4));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = " SELECT `ID`, `Username`, `email`, `api_key`, `api_secret` FROM `User` where `ID` = $userID";
	//echo $sql;
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['Username'],$row['email'],$row['api_key'],$row['api_secret']);
  }
  $conn->close();
  return $tempAry;
}

function getRules($userID){
  $conn = getSQL(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT
`UserID`,`BuyOrdersEnabled`,`BuyOrdersTop`,`BuyOrdersBtm`,`MarketCapEnabled`,`MarketCapTop`,`MarketCapBtm`,`1HrChangeEnabled`,
`1HrChangeTop`,`1HrChangeBtm`,`24HrChangeEnabled`,`24HrChangeTop`,`24HrChangeBtm`,`7DChangeEnabled`,`7DChangeTop`,`7DChangeBtm`,
`CoinPriceEnabled`,`CoinPriceTop`,`CoinPriceBtm`,`SellOrdersEnabled`,`SellOrdersTop`,`SellOrdersBtm`,`VolumeEnabled`,`VolumeTop`,
`VolumeBtm`,`BuyCoin`,`SendEmail`,`BTCAmount`,`RuleID`,`BuyCoinOffsetEnabled`,`BuyCoinOffsetPct`,`PriceTrendEnabled`, `Price4Trend`, `Price3Trend`, `LastPriceTrend`, `LivePriceTrend`
, `Active`, `DisableUntil`, `BaseCurrency`, `NoOfCoinPurchase`, `TimetoCancelBuy`, `BuyType`, `TimeToCancelBuyMins`, `BuyPriceMinEnabled`, `BuyPriceMin`, `LimitToCoin`,`AutoBuyCoinEnabled`,`AutoBuyPrice`
,`BuyAmountOverrideEnabled`,`BuyAmountOverride`,`NewBuyPattern`
FROM `UserBuyRules` WHERE `UserID` =  $userID";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  //print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['UserID'],$row['BuyOrdersEnabled'],$row['BuyOrdersTop'],$row['BuyOrdersBtm'],$row['MarketCapEnabled'],$row['MarketCapTop'],
      $row['MarketCapBtm'],$row['1HrChangeEnabled'],$row['1HrChangeTop'],$row['1HrChangeBtm'],$row['24HrChangeEnabled'],$row['24HrChangeTop'],$row['24HrChangeBtm'],
      $row['7DChangeEnabled'],$row['7DChangeTop'],$row['7DChangeBtm'],$row['CoinPriceEnabled'],$row['CoinPriceTop'],$row['CoinPriceBtm'],$row['SellOrdersEnabled'],
      $row['SellOrdersTop'],$row['SellOrdersBtm'],$row['VolumeEnabled'],$row['VolumeTop'],$row['VolumeBtm'],$row['BuyCoin'],$row['SendEmail'],$row['BTCAmount'],$row['RuleID']
      ,$row['BuyCoinOffsetEnabled'],$row['BuyCoinOffsetPct'],$row['PriceTrendEnabled'],$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend']
     ,$row['Active'],$row['DisableUntil'],$row['BaseCurrency'],$row['NoOfCoinPurchase'],$row['TimetoCancelBuy'],$row['BuyType'],$row['TimeToCancelBuyMins'],$row['BuyPriceMinEnabled'],$row['BuyPriceMin']
      ,$row['LimitToCoin'],$row['AutoBuyCoinEnabled'],$row['AutoBuyPrice'],$row['BuyAmountOverrideEnabled'],$row['BuyAmountOverride'],$row['NewBuyPattern']);//35
  }
  $conn->close();
  return $tempAry;
}

function updateUser($userID, $newusername, $email, $apikey, $apisecret){
  $conn = getSQL(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `User` SET `Username` = '$newusername', `email` = '$email', `api_key` = '$apikey', `api_secret` = '$apisecret' where `ID` = $userID";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();

}

function updateBuyConfig($userID, $BTC,$MarketCapBuyPct,  $VolumeBuyPct,  $BuyOrdersPct,  $buyWithScore, $score){
  $conn = getSQL(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `Config` SET `BTC` = $BTC,  `MarketCapBuyPct` = $MarketCapBuyPct,  `VolumeBuyPct` = $VolumeBuyPct, `BuyOrdersPct` = $BuyOrdersPct,
   `BuyWithScore` = $buyWithScore,`Score` = $score  where `UserID` = $userID";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();

}

function updateSellConfig($userID, $CoinSalePct, $MarketCapSellPct,  $VolumeSellPct, $SellOrdersPct, $minProfitPct,  $sellWithScore, $sellScore){
  $conn = getSQL(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE `Config` SET `CoinSalePct` = $CoinSalePct,  `MarketCapSellPct` = $MarketCapSellPct,  `VolumeSellPct` = $VolumeSellPct, `SellOrdersPct` = $SellOrdersPct,
  `MinPctGain` = $minProfitPct, `SellWithScore` = $sellWithScore,`SellScore` = $sellScore where `UserID` = $userID";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();

}

function drawCheckbox($checked){
  if ($checked == 1){
    ?> <input type="checkbox" name="buyWithScore" id="buyWithScore" class="form-control input-lg"  value="1" checked tabindex="14"> <?php
  }else{
    ?> <input type="checkbox" name="buyWithScore" id="buyWithScore" class="form-control input-lg"  value="0" tabindex="14"> <?php
  }
}

function drawSellCheckbox($checked){
  if ($checked == 1){
    ?> <input type="checkbox" name="sellWithScore" id="sellWithScore" class="form-control input-lg"  value="1" checked tabindex="16"> <?php
  }else{
    ?> <input type="checkbox" name="sellWithScore" id="sellWithScore" class="form-control input-lg"  value="0" tabindex="16"> <?php
  }
}



$userSettings = getRules($_SESSION['ID']);
$userSettingsLen = count($userSettings);
//echo $userDetails[0][1];

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
          <a href="Settings.php" class="active">Settings</a><?php
          if ($_SESSION['AccountType']==1){echo "<a href='AdminSettings.php'>Admin Settings</a>";}
          ?>
      </div>

      <div class"row">

           <h3><a href='Settings.php'>User Settings</a> &nbsp > &nbsp <a href='BuySettings.php'>Buy Settings</a> &nbsp > &nbsp <a href='SellSettings.php'>Sell Settings</a></h3>
           <table>
             <th>&nbspEdit</th><th>&nbspCopy</th><th>&nbspDelete</th><TH>&nbspRuleID</TH><TH>&nbspUserID</TH><TH>&nbspBuyOrdersEnabled</TH><TH>&nbspBuyOrdersTop</TH><TH>&nbspBuyOrdersBtm</TH><TH>&nbspMarketCapEnabled</TH><TH>&nbspMarketCapTop</TH><TH>&nbspMarketCapBtm</TH>
             <TH>&nbsp1HrChangeEnabled</TH><TH>&nbsp1HrChangeTop</TH><TH>&nbsp1HrChangeBtm</TH><TH>&nbsp24HrChangeEnabled</TH><TH>&nbsp24HrChangeTop</TH><TH>&nbsp24HrChangeBtm</TH><TH>&nbsp7DChangeEnabled</TH><TH>&nbsp7DChangeTop</TH>
             <TH>&nbsp7DChangeBtm</TH><TH>&nbspCoinPriceEnabled</TH><TH>&nbspCoinPriceTop</TH><TH>&nbspCoinPriceBtm</TH><TH>&nbspSellOrdersEnabled</TH><TH>&nbspSellOrdersTop</TH><TH>&nbspSellOrdersBtm</TH><TH>&nbspVolumeEnabled</TH>
             <TH>&nbspVolumeTop</TH><TH>&nbspVolumeBtm</TH><TH>&nbspBuyCoin</TH><TH>&nbspSendEmail</TH><TH>&nbspBTCAmount</TH><TH>&nbspBuyCoinOffsetEnabled</TH><TH>&nbspBuyCoinOffsetPct</TH><TH>&nbspPriceTrendEnabled</TH><TH>&nbspPrice4Trend</TH>
             <TH>&nbspPrice3Trend</TH><TH>&nbspLastPriceTrend</TH><TH>&nbspLivePriceTrend</TH>
            <TH>&nbspActive</TH><TH>&nbspDisableUntil</TH><TH>&nbspBaseCurrency</TH><TH>&nbspNoOfCoinPurchase</TH><TH>&nbspTimetoCancelBuy</TH><TH>&nbspBuyType</TH><TH>&nbspTimeToCancelBuyMins</TH><TH>&nbspBuyPriceMinEnabled</TH><TH>&nbspBuyPriceMin</TH>
            <TH>&nbspLimitToCoin</TH><TH>&nbspAutoBuyCoinEnabled</TH><TH>&nbspAutoBuyPrice</TH><TH>&nbspBuyAmountOverrideEnabled</TH><TH>&nbspBuyAmountOverride</TH><TH>&nbspNewBuyPattern</TH>
             <tr>
          <?php
          for($x = 0; $x < $userSettingsLen; $x++) {
            //addBuyTableLine($userSettings[$x][28],$userSettings[$x][0],$userSettings[$x][1],$userSettings[$x][2],$userSettings[$x][3])
            echo "<td><a href='AddNewSetting.php?edit=".$userSettings[$x][28]."'><span class='glyphicon glyphicon-pencil' style='font-size:22px;'></span></a></td>";
            echo "<td><a href='AddNewSetting.php?copyRule=".$userSettings[$x][28]."'><span class='glyphicon glyphicon-pencil' style='font-size:22px;'></span></a></td>";
            echo "<td><a href='AddNewSetting.php?delete=".$userSettings[$x][28]."'><span class='glyphicon glyphicon-trash' style='font-size:22px;'></span></a></td>";
            echo "<td>".$userSettings[$x][28]."</td>";echo "<td>".$userSettings[$x][0]."</td>";
            echo "<td>".$userSettings[$x][1]."</td>";echo "<td>".$userSettings[$x][2]."</td>";echo "<td>".$userSettings[$x][3]."</td>";
            echo "<td>".$userSettings[$x][4]."</td>";echo "<td>".$userSettings[$x][5]."</td>";echo "<td>".$userSettings[$x][6]."</td>";
            echo "<td>".$userSettings[$x][7]."</td>";echo "<td>".$userSettings[$x][8]."</td>";echo "<td>".$userSettings[$x][9]."</td>";
            echo "<td>".$userSettings[$x][10]."</td>";echo "<td>".$userSettings[$x][11]."</td>";echo "<td>".$userSettings[$x][12]."</td>";
            echo "<td>".$userSettings[$x][13]."</td>";echo "<td>".$userSettings[$x][14]."</td>";echo "<td>".$userSettings[$x][15]."</td>";
            echo "<td>".$userSettings[$x][16]."</td>";echo "<td>".$userSettings[$x][17]."</td>";echo "<td>".$userSettings[$x][18]."</td>";
            echo "<td>".$userSettings[$x][19]."</td>";echo "<td>".$userSettings[$x][23]."</td>";echo "<td>".$userSettings[$x][22]."</td>";echo "<td>".$userSettings[$x][21]."</td>";echo "<td>".$userSettings[$x][20]."</td>";
            echo "<td>".$userSettings[$x][24]."</td>";echo "<td>".$userSettings[$x][25]."</td>";echo "<td>".$userSettings[$x][26]."</td>";
            echo "<td>".$userSettings[$x][27]."</td><td>".$userSettings[$x][29]."</td><td>".$userSettings[$x][30]."</td>";
            echo "<td>".$userSettings[$x][31]."</td><td>".$userSettings[$x][32]."</td><td>".$userSettings[$x][33]."</td><td>".$userSettings[$x][34]."</td><td>".$userSettings[$x][35]."</td>";
            echo "<td>".$userSettings[$x][36]."</td><td>".$userSettings[$x][37]."</td><td>".$userSettings[$x][38]."</td><td>".$userSettings[$x][39]."</td><td>".$userSettings[$x][40]."</td>";
            echo "<td>".$userSettings[$x][41]."</td><td>".$userSettings[$x][42]."</td><td>".$userSettings[$x][43]."</td><td>".$userSettings[$x][44]."</td>";
            echo "<td>".$userSettings[$x][45]."</td><td>".$userSettings[$x][46]."</td><td>".$userSettings[$x][47]."</td>";
            echo "<td>".$userSettings[$x][48]."</td><td>".$userSettings[$x][49]."</td>";
            echo "<td>".$userSettings[$x][50]."</td><tr>";
          }
          echo "</table> <br><a href='AddNewSetting.php?addNew=Yes'><span class='glyphicon glyphicon-plus' style='font-size:48px;'></span></a>";
          //echo "</table> <br><a href='AddNewSetting.php?addNew=Yes'>Add New</a>";
          ?>
      </div>
      <hr>
      <div class="footer">
        <hr>
        <input type="button" onclick="location='logout.php'" value="Logout"/>
      </div>

</body>
</html>
