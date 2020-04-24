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
include_once ('/home/stevenj1979/SQLData.php');

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
  ,`AutoSellCoinEnabled`,`AutoSellPrice`,`SellPatternEnabled`,`SellPattern`,`CoinPricePatternEnabled`,`CoinPricePattern`
FROM `UserSellRules` WHERE `UserID` = $userID";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  //print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['UserID'],$row['SellCoin'],$row['SendEmail'],$row['BuyOrdersEnabled'],$row['BuyOrdersTop'],$row['BuyOrdersBtm'],
      $row['MarketCapEnabled'],$row['MarketCapTop'],$row['MarketCapBtm'],$row['1HrChangeEnabled'],$row['1HrChangeTop'],$row['1HrChangeBtm'],$row['24HrChangeEnabled'],
      $row['24HrChangeTop'],$row['24HrChangeBtm'],$row['7DChangeEnabled'],$row['7DChangeTop'],$row['7DChangeBtm'],$row['ProfitPctEnabled'],$row['ProfitPctTop'],
      $row['ProfitPctBtm'],$row['CoinPriceEnabled'],$row['CoinPriceTop'],$row['CoinPriceBtm'],$row['SellOrdersEnabled'],$row['SellOrdersTop'],$row['SellOrdersBtm'],
      $row['VolumeEnabled'],$row['VolumeTop'],$row['VolumeBtm'],$row['Email'],$row['UserName'],$row['APIKey'],$row['APISecret'],$row['SellPriceMinEnabled'],$row['SellPriceMin']
      ,$row['LimitToCoin'],$row['AutoSellCoinEnabled'],$row['AutoSellPrice'],$row['SellPatternEnabled'],$row['SellPattern'],$row['CoinPricePatternEnabled'],$row['CoinPricePattern']
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


$userSettings = getRules($_SESSION['ID']);
$userSettingsLen = count($userSettings);
//echo $userDetails[0][1];
displayHeader(7);
?>

           <h3><a href='Settings.php'>User Settings</a> &nbsp > &nbsp <a href='BuySettings.php'>Buy Settings</a> &nbsp > &nbsp <a href='SellSettings.php'>Sell Settings</a></h3>
           <table>
             <th>&nbspEdit</th><th>&nbspCopy</th><th>&nbspDelete</th><TH>&nbspRuleID</TH><TH>&nbspUserID</TH><TH>&nbspSellCoin</TH><TH>&nbspSendEmail</TH><TH>&nbspBuyOrdersEnabled</TH><TH>&nbspBuyOrdersTop</TH><TH>&nbspBuyOrdersBtm</TH><TH>&nbspMarketCapEnabled</TH>
             <TH>&nbspMarketCapTop</TH><TH>&nbspMarketCapBtm</TH><TH>&nbsp1HrChangeEnabled</TH><TH>&nbsp1HrChangeTop</TH><TH>&nbsp1HrChangeBtm</TH><TH>&nbsp24HrChangeEnabled</TH><TH>&nbsp24HrChangeTop</TH><TH>&nbsp24HrChangeBtm</TH>
             <TH>&nbsp7DChangeEnabled</TH><TH>&nbsp7DChangeTop</TH><TH>&nbsp7DChangeBtm</TH><TH>&nbspProfitPctEnabled</TH><TH>&nbspProfitPctTop</TH><TH>&nbspProfitPctBtm</TH><TH>&nbspCoinPriceEnabled</TH><TH>&nbspCoinPriceTop</TH>
             <TH>&nbspCoinPriceBtm</TH><TH>&nbspSellOrdersEnabled</TH><TH>&nbspSellOrdersTop</TH><TH>&nbspSellOrdersBtm</TH><TH>&nbspVolumeEnabled</TH><TH>&nbspVolumeTop</TH><TH>&nbspVolumeBtm</TH><TH>&nbspEmail</TH><TH>&nbspUserName</TH>
             <TH>&nbspAPIKey</TH><TH>&nbspAPISecret</TH><TH>&nbspSellPriceMinEnabled</TH><TH>&nbspSellPriceMin</TH><TH>&nbspLimitToCoin</TH><TH>&nbspAutoSellCoinEnabled</TH><TH>&nbspAutoSellPrice</TH>
             <TH>&nbspSellPatternEnabled</TH><TH>&nbspSellPattern</TH><TH>&nbspCoinPricePatternEnabled</TH><TH>&nbspCoinPricePattern</TH>
             <TR>
          <?php
          //echo "<BR>".$userSettingsLen;
          for($x = 0; $x < $userSettingsLen; $x++) {
            echo "<td><a href='AddNewSettingSell.php?edit=".$userSettings[$x][0]."'><span class='glyphicon glyphicon-pencil' style='font-size:22px;'></span></a></td>";
            echo "<td><a href='AddNewSettingSell.php?copyRule=".$userSettings[$x][0]."'><span class='glyphicon glyphicon-copy' style='font-size:22px;'></span></a></td>";
            echo "<td><a href='AddNewSettingSell.php?delete=".$userSettings[$x][0]."'><span class='glyphicon glyphicon-trash' style='font-size:22px;'></span></a></td>";
            echo "<td>".$userSettings[$x][0]."</td>";
            echo "<td>".$userSettings[$x][1]."</td>";echo "<td>".$userSettings[$x][2]."</td>";echo "<td>".$userSettings[$x][3]."</td>";//Market Cap
            echo "<td>".$userSettings[$x][4]."</td>";echo "<td>".$userSettings[$x][5]."</td>";echo "<td>".$userSettings[$x][6]."</td>";//Volume
            echo "<td>".$userSettings[$x][7]."</td>";echo "<td>".$userSettings[$x][8]."</td>";echo "<td>".$userSettings[$x][9]."</td>";//Buy Orders
            echo "<td>".$userSettings[$x][10]."</td>";echo "<td>".$userSettings[$x][11]."</td>";echo "<td>".$userSettings[$x][12]."</td>";//PriceChange 1Hr
            echo "<td>".$userSettings[$x][13]."</td>";echo "<td>".$userSettings[$x][14]."</td>";echo "<td>".$userSettings[$x][15]."</td>";//Price Change 24Hr
            echo "<td>".$userSettings[$x][16]."</td>";echo "<td>".$userSettings[$x][17]."</td>";echo "<td>".$userSettings[$x][18]."</td>";//PriceChange 7D
            echo "<td>".$userSettings[$x][19]."</td>";echo "<td>".$userSettings[$x][23]."</td>";echo "<td>".$userSettings[$x][22]."</td>";echo "<td>".$userSettings[$x][21]."</td>";echo "<td>".$userSettings[$x][20]."</td>";
            echo "<td>".$userSettings[$x][24]."</td>";echo "<td>".$userSettings[$x][25]."</td>";echo "<td>".$userSettings[$x][26]."</td>";
            echo "<td>".$userSettings[$x][27]."</td>";echo "<td>".$userSettings[$x][28]."</td>";echo "<td>".$userSettings[$x][29]."</td>";
            echo "<td>".$userSettings[$x][30]."</td>";echo "<td>".$userSettings[$x][31]."</td>";echo "<td>".$userSettings[$x][32]."</td>";
            echo "<td>".$userSettings[$x][33]."</td>";echo "<td>".$userSettings[$x][34]."</td>";
            echo "<td>".$userSettings[$x][35]."</td>";echo "<td>".$userSettings[$x][36]."</td>";echo "<td>".$userSettings[$x][37]."</td>";
            echo "<td>".$userSettings[$x][38]."</td>";echo "<td>".$userSettings[$x][39]."</td>";
            echo "<td>".$userSettings[$x][40]."</td>";echo "<td>".$userSettings[$x][41]."</td>";
            echo "<td>".$userSettings[$x][42]."</td>";echo "<td>".$userSettings[$x][43]."</td>";
            echo "<tr>";
          }
          echo "</table> <br><a href='AddNewSettingSell.php?addNew=Yes'><span class='glyphicon glyphicon-plus' style='font-size:48px;'></span></a>";
          //echo "</table> <br><a href='AddNewSetting.php?addNew=Yes'>Add New</a>";
          displaySideColumn();?>

</body>
</html>
