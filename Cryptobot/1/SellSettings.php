<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<?php require('includes/config.php');
include_once '../includes/newConfig.php';?>
<html>
<style>
<?php include 'style/style.css'; ?>
</style>
<body>
<?php


//if not logged in redirect to login page
if(!$user->is_logged_in()){ header('Location: login.php'); exit(); }

if(isset($_POST['submit'])){

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
include_once ('../../../../SQLData.php');

function getUserIDs($userID){
  $conn = getSQLConn(rand(1,3));
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
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT
`ID`,`UserID`,`SellCoin`,`SendEmail`,`BuyOrdersEnabled`,`BuyOrdersTop`,`BuyOrdersBtm`,`MarketCapEnabled`, `MarketCapTop`, `MarketCapBtm`, `1HrChangeEnabled`,
 `1HrChangeTop`, `1HrChangeBtm`, `24HrChangeEnabled`, `24HrChangeTop`,`24HrChangeBtm`, `7DChangeEnabled`, `7DChangeTop`, `7DChangeBtm`, `ProfitPctEnabled`,
 `ProfitPctTop`, `ProfitPctBtm`, `CoinPriceEnabled`, `CoinPriceTop`, `CoinPriceBtm`, `SellOrdersEnabled`, `SellOrdersTop`, `SellOrdersBtm`, `VolumeEnabled`,
  `VolumeTop`, `VolumeBtm`, `Email`, `UserName`, `APIKey`, `APISecret`, `SellPriceMinEnabled`,`SellPriceMin`,`LimitToCoin`
  ,`AutoSellCoinEnabled`,'AutoSellPrice',`SellPatternEnabled`,`SellPattern`,`CoinPricePatternEnabled`,`CoinPricePattern`,`NameCpmn` as `CoinPriceMatchName`,`NameCppn` as `CoinPricePatternName`,`NameC1hPn` as `Coin1HrPatternName`
  ,`RuleName`,`Category`
FROM `View14_UserSellRules` WHERE `UserID` = $userID";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  //print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['UserID'],$row['SellCoin'],$row['SendEmail'],$row['BuyOrdersEnabled'],$row['BuyOrdersTop'],$row['BuyOrdersBtm'] //6
      ,$row['MarketCapEnabled'],$row['MarketCapTop'],$row['MarketCapBtm'],$row['1HrChangeEnabled'],$row['1HrChangeTop'],$row['1HrChangeBtm'],$row['24HrChangeEnabled'] //13
      ,$row['24HrChangeTop'],$row['24HrChangeBtm'],$row['7DChangeEnabled'],$row['7DChangeTop'],$row['7DChangeBtm'],$row['ProfitPctEnabled'],$row['ProfitPctTop'] //20
      ,$row['ProfitPctBtm'],$row['CoinPriceEnabled'],$row['CoinPriceTop'],$row['CoinPriceBtm'],$row['SellOrdersEnabled'],$row['SellOrdersTop'],$row['SellOrdersBtm'] //27
      ,$row['VolumeEnabled'],$row['VolumeTop'],$row['VolumeBtm'],$row['Email'],$row['UserName'],$row['APIKey'],$row['APISecret'],$row['SellPriceMinEnabled'],$row['SellPriceMin'] //36
      ,$row['LimitToCoin'],$row['AutoSellCoinEnabled'],$row['AutoSellPrice'],$row['SellPatternEnabled'],$row['SellPattern'],$row['CoinPricePatternEnabled'],$row['CoinPricePattern'] //43
      ,$row['CoinPriceMatchName'],$row['CoinPricePatternName'],$row['Coin1HrPatternName'],$row['RuleName'],$row['Category'] //48
);//35
  }
  $conn->close();
  return $tempAry;
}

function updateUser($userID, $newusername, $email, $apikey, $apisecret){
  $conn = getSQLConn(rand(1,3));
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
  $conn = getSQLConn(rand(1,3));
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
  $conn = getSQLConn(rand(1,3));
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

function showSellRules($userSettings, $title, $flag, $userSettingsLen, $findCat = 'All'){
  echo "<H3>$title</H3>"; ?>
  <table>
    <th>&nbspEdit</th><th>&nbspCopy</th><th>&nbspDelete</th><TH>&nbspRuleID</TH><TH>&nbspRuleName</TH><TH>&nbspUserID</TH><TH>&nbspSellCoin</TH><TH>&nbspSendEmail</TH><TH>&nbspBuyOrdersEnabled</TH><TH>&nbspBuyOrdersTop</TH><TH>&nbspBuyOrdersBtm</TH><TH>&nbspMarketCapEnabled</TH>
    <TH>&nbspMarketCapTop</TH><TH>&nbspMarketCapBtm</TH><TH>&nbsp1HrChangeEnabled</TH><TH>&nbsp1HrChangeTop</TH><TH>&nbsp1HrChangeBtm</TH><TH>&nbsp24HrChangeEnabled</TH><TH>&nbsp24HrChangeTop</TH><TH>&nbsp24HrChangeBtm</TH>
    <TH>&nbsp7DChangeEnabled</TH><TH>&nbsp7DChangeTop</TH><TH>&nbsp7DChangeBtm</TH><TH>&nbspProfitPctEnabled</TH><TH>&nbspProfitPctTop</TH><TH>&nbspProfitPctBtm</TH><TH>&nbspCoinPriceEnabled</TH><TH>&nbspCoinPriceTop</TH>
    <TH>&nbspCoinPriceBtm</TH><TH>&nbspSellOrdersEnabled</TH><TH>&nbspSellOrdersTop</TH><TH>&nbspSellOrdersBtm</TH><TH>&nbspVolumeEnabled</TH><TH>&nbspVolumeTop</TH><TH>&nbspVolumeBtm</TH><TH>&nbspEmail</TH><TH>&nbspUserName</TH>
    <TH>&nbspAPIKey</TH><TH>&nbspAPISecret</TH><TH>&nbspSellPriceMinEnabled</TH><TH>&nbspSellPriceMin</TH><TH>&nbspLimitToCoin</TH><TH>&nbspAutoSellCoinEnabled</TH><TH>&nbspAutoSellPrice</TH>
    <!--<TH>&nbspcoinPriceMatchNameEnabled</TH>
    <TH>&nbspcoinPriceMatchName</TH>-->
    <TH>&nbspSellPatternEnabled</TH>
    <TH>&nbspCoinPricePatternName</TH>
    <!--<TH>&nbspSellPattern</TH>-->
    <TH>&nbspCoinPricePatternEnabled</TH>
    <!--<TH>&nbspCoinPricePattern</TH>-->
    <TH>&nbspCoin1HrPatternName</TH>
    <TR>
 <?php
 //echo "<BR>".$userSettingsLen;
 for($x = 0; $x < $userSettingsLen; $x++) {
   $catFlag = False;
   $iD = $userSettings[$x][0];$userID = $userSettings[$x][1];
   $SellCoin = $userSettings[$x][2];$SendEmail = $userSettings[$x][3];
   $buyOrdersEnabled = $userSettings[$x][4];  $buyOrdersTop = $userSettings[$x][5];$buyOrdersBtm = $userSettings[$x][6];
   $marketCapEnabled = $userSettings[$x][7];$marketCapTop = $userSettings[$x][8];$marketCapBtm = $userSettings[$x][9];
   $hr1ChangeEnabled = $userSettings[$x][10];  $hr1ChangeTop = $userSettings[$x][11];  $hr1ChangeBtm = $userSettings[$x][12];
   $hr24ChangeEnabled = $userSettings[$x][13];  $hr24ChangeTop = $userSettings[$x][14];  $hr24ChangeBtm = $userSettings[$x][15];
   $d7ChangeEnabled = $userSettings[$x][16];$d7ChangeTop = $userSettings[$x][17];$d7ChangeBtm = $userSettings[$x][18];
   $profitPctEnabled = $userSettings[$x][19];  $profitPctTop = $userSettings[$x][20];$profitPctBtm = $userSettings[$x][21];
   $coinPriceEnabled = $userSettings[$x][22];$coinPriceTop = $userSettings[$x][23];$coinPriceBtm  = $userSettings[$x][24];
   $sellOrdersEnabled = $userSettings[$x][25];  $sellOrdersTop = $userSettings[$x][26];$sellOrdersBtm = $userSettings[$x][27];
   $volumeEnabled = $userSettings[$x][28];$volumeTop = $userSettings[$x][29];$volumeBtm = $userSettings[$x][30];
   $email = $userSettings[$x][31];$userName = $userSettings[$x][32];$aPIKey = $userSettings[$x][33];$aPISecret = $userSettings[$x][34];
   $sellPriceMinEnabled = $userSettings[$x][35];$sellPriceMin = $userSettings[$x][36];
   $limitToCoin = $userSettings[$x][37];$autoSellCoinEnabled = $userSettings[$x][38];$autoSellPrice = $userSettings[$x][39];
   $sellPatternEnabled = $userSettings[$x][40];$sellPattern = $userSettings[$x][41];$coinPricePatternEnabled = $userSettings[$x][42];$coinPricePattern = $userSettings[$x][43];
   $coinPriceMatchName = $userSettings[$x][44];$coinPricePatternName = $userSettings[$x][45];$coin1HrPatternName = $userSettings[$x][46];
   $ruleName = $userSettings[$x][47]; $category = $userSettings[$x][48];
   //echo "$SellCoin == $flag";
   if ($findCat <> 'All'){
      //if ($findCat == ''){ $catFlag = True;}
      if ($category == $findCat){ $catFlag = True;}
      else{$catFlag = False;}
   }else{
     $catFlag = True;
   }
   if ($SellCoin == $flag AND $catFlag == True){
     echo "<td><a href='AddNewSettingSell.php?edit=".$iD."'><span class='glyphicon glyphicon-pencil' style='font-size:22px;'></span></a></td>";
     echo "<td><a href='AddNewSettingSell.php?copyRule=".$iD."'><span class='glyphicon glyphicon-copy' style='font-size:22px;'></span></a></td>";
     echo "<td><a href='AddNewSettingSell.php?delete=".$iD."'><span class='glyphicon glyphicon-trash' style='font-size:22px;'></span></a></td>";
     echo "<td>".$iD."</td>";
     echo "<td>".$ruleName."</td>";
     echo "<td>".$userID."</td>";echo "<td>".$SellCoin."</td>";echo "<td>".$SendEmail."</td>";//Market Cap
     echo "<td>".$buyOrdersEnabled."</td>";echo "<td>".$buyOrdersTop."</td>";echo "<td>".$buyOrdersBtm."</td>";//Volume
     echo "<td>".$marketCapEnabled."</td>";echo "<td>".$marketCapTop."</td>";echo "<td>".$marketCapBtm."</td>";//Buy Orders
     echo "<td>".$hr1ChangeEnabled."</td>";echo "<td>".$hr1ChangeTop."</td>";echo "<td>".$hr1ChangeBtm."</td>";//PriceChange 1Hr
     echo "<td>".$hr24ChangeEnabled."</td>";echo "<td>".$hr24ChangeTop."</td>";echo "<td>".$hr24ChangeBtm."</td>";//Price Change 24Hr
     echo "<td>".$d7ChangeEnabled."</td>";echo "<td>".$d7ChangeTop."</td>";echo "<td>".$d7ChangeBtm."</td>";//PriceChange 7D
     echo "<td>".$profitPctEnabled."</td>";echo "<td>".$coinPriceTop."</td>";echo "<td>".$coinPriceEnabled."</td>";echo "<td>".$profitPctBtm."</td>";echo "<td>".$profitPctTop."</td>";
     echo "<td>".$coinPriceBtm."</td>";echo "<td>".$sellOrdersEnabled."</td>";echo "<td>".$sellOrdersTop."</td>";
     echo "<td>".$sellOrdersBtm."</td>";echo "<td>".$volumeEnabled."</td>";echo "<td>".$volumeTop."</td>";
     echo "<td>".$volumeBtm."</td>";echo "<td>".$email."</td>";echo "<td>".$userName."</td>";
     echo "<td>".$aPIKey."</td>";echo "<td>".$aPISecret."</td>";
     echo "<td>".$sellPriceMinEnabled."</td>";echo "<td>".$sellPriceMin."</td>";echo "<td>".$limitToCoin."</td>";
     echo "<td>".$autoSellCoinEnabled."</td>";echo "<td>".$autoSellPrice."</td>";
     //echo "<td></td>";
     //echo "<td>$coinPriceMatchName</td>";
     echo "<td>".$sellPatternEnabled."</td>";
     //echo "<td>".$sellPattern."</td>";
     echo "<td>$coinPricePatternName</td>";
     echo "<td>".$coinPricePatternEnabled."</td>";
     //echo "<td>".$coinPricePattern."</td>";
     echo "<td>$coinPriceMatchName</td>";
     echo "<tr>";
   }
 }
 echo "</table> <br><a href='AddNewSettingSell.php?addNew=Yes'><span class='glyphicon glyphicon-plus' style='font-size:48px;'></span></a>";
}


$userSettings = getRules($_SESSION['ID']);
$userSettingsLen = count($userSettings);
//echo $userDetails[0][1];
displayHeader(7);


           //<h3><a href='Settings.php'>User Settings</a> &nbsp > &nbsp <a href='BuySettings.php'>Buy Settings</a> &nbsp > &nbsp <a href='SellSettings.php'>Sell Settings</a> &nbsp > &nbsp <a href='Settings_Patterns.php'>Setting Patterns</a></h3>
            displaySubHeader("Settings");
            showSellRules($userSettings, "Enabled Rules: All",1,$userSettingsLen,'');
            showSellRules($userSettings, "Enabled Rules: Coin Mode",1,$userSettingsLen,'CoinMode');
            showSellRules($userSettings, "Disabled Rules",0,$userSettingsLen);
          //echo "</table> <br><a href='AddNewSetting.php?addNew=Yes'>Add New</a>";
          displaySideColumn();?>

</body>
</html>
