<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<?php require('includes/config.php');
include_once ('/home/stevenj1979/SQLData.php');
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
displayHeader(7);
if(!empty($_GET['addNew'])){ $_GET['addNew'] = null; submitNewUser(); }
if(!empty($_GET['edit'])){ displayEdit($_GET['edit']); }
if(!empty($_GET['nUReady'])){ submitNewUser(); }
if(!empty($_GET['editedUserReady'])){ updateEditedUser(); }
if(!empty($_GET['delete'])){ deleteItem($_GET['delete']); }
if(!empty($_GET['copyRule'])){ copyRule($_GET['copyRule']); }

function copyRule($ID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $userID = $_SESSION['ID'];
  $sql = "INSERT INTO `SellRules`(`UserID`, `SellCoin`, `SendEmail`, `BuyOrdersEnabled`, `BuyOrdersTop`, `BuyOrdersBtm`, `MarketCapEnabled`, `MarketCapTop`, `MarketCapBtm`, `1HrChangeEnabled`, `1HrChangeTop`
    , `1HrChangeBtm`, `24HrChangeEnabled`, `24HrChangeTop`, `24HrChangeBtm`, `7DChangeEnabled`, `7DChangeTop`, `7DChangeBtm`, `ProfitPctEnabled`, `ProfitPctTop`, `ProfitPctBtm`, `CoinPriceEnabled`, `CoinPriceTop`
    , `CoinPriceBtm`, `SellOrdersEnabled`, `SellOrdersTop`, `SellOrdersBtm`, `VolumeEnabled`, `VolumeTop`, `VolumeBtm`, `CoinOrder`, `SellCoinOffsetEnabled`, `SellCoinOffsetPct`, `SellPriceMinEnabled`, `SellPriceMin`
    , `LimitToCoin`, `LimitToCoinID`, `AutoSellCoinEnabled`, `AutoSellCoinPct`,`SellPatternEnabled`,`SellPattern`)
    select `UserID`, 0, `SendEmail`, `BuyOrdersEnabled`, `BuyOrdersTop`, `BuyOrdersBtm`, `MarketCapEnabled`, `MarketCapTop`, `MarketCapBtm`, `1HrChangeEnabled`, `1HrChangeTop`, `1HrChangeBtm`, `24HrChangeEnabled`
    , `24HrChangeTop`, `24HrChangeBtm`, `7DChangeEnabled`, `7DChangeTop`, `7DChangeBtm`, `ProfitPctEnabled`, `ProfitPctTop`, `ProfitPctBtm`, `CoinPriceEnabled`, `CoinPriceTop`, `CoinPriceBtm`, `SellOrdersEnabled`
    , `SellOrdersTop`, `SellOrdersBtm`, `VolumeEnabled`, `VolumeTop`, `VolumeBtm`, `CoinOrder`, `SellCoinOffsetEnabled`, `SellCoinOffsetPct`, `SellPriceMinEnabled`, `SellPriceMin`, `LimitToCoin`, `LimitToCoinID`
    , `AutoSellCoinEnabled`, `AutoSellCoinPct`,`SellPatternEnabled`,`SellPattern`,`CoinPricePatternEnabled`,`CoinPricePattern`
    from `SellRules`
    where `ID` = $ID";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();
  header('Location: SellSettings.php');

}

function deleteItem($id){

  $_GET['nUReady'] = null;
  // Create connection
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $userID = $_SESSION['ID'];
  $sql = "DELETE FROM `SellRules` WHERE `ID` = $id";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();
  header('Location: SellSettings.php');
}

function submitNewUser(){

  $_GET['nUReady'] = null;
  // Create connection
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $userID = $_SESSION['ID'];
  $sql = "INSERT INTO `SellRules`(`UserID`, `MarketCapEnabled`, `MarketCapTop`, `MarketCapBtm`) VALUES ($userID,1,-40,-100)";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();
  header('Location: SellSettings.php');
}

function postDataYesNo($postValue){
  if (!empty($postValue)){
    if ($postValue == "Yes"){
      return 1;
    }else{
      return 0;
    }
  }else{
    return 0;
  }
}


function postData($postValue){
  if (!empty($postValue)){
    return $postValue;
  }else{
    return 0;
  }
}

function updateEditedUser(){

  $id = $_GET['editedUserReady'];
  $_GET['editedUserReady'] = null;

  if (!empty($_POST['MarketCapEnable'])){if ($_POST['MarketCapEnable'] == "Yes"){$MarketCapEnable = 1;}else{$MarketCapEnable = 0;}}else{ $MarketCapEnable = 0;}
  if (!empty($_POST['MarketCapTop'])){$MarketCapTop = $_POST['MarketCapTop'];}else{$MarketCapTop = 0;}
  if (!empty($_POST['MarketCapBtm'])){$MarketCapBtm = $_POST['MarketCapBtm'];}else{$MarketCapBtm = 0;}
  if (!empty($_POST['VolumeEnable'])){if ($_POST['VolumeEnable'] == "Yes"){$VolumeEnable = 1;}else{$VolumeEnable = 0;}}else{ $VolumeEnable = 0;}
  if (!empty($_POST['VolumeTop'])){$VolumeTop = $_POST['VolumeTop'];}else{$VolumeTop = 0;}
  if (!empty($_POST['VolumeBtm'])){$VolumeBtm = $_POST['VolumeBtm'];}else{$VolumeBtm = 0;}
  if (!empty($_POST['BuyOrdersEnabled'])){if ($_POST['BuyOrdersEnabled'] == "Yes"){$BuyOrdersEnabled = 1;}else{$BuyOrdersEnabled = 0;}}else{ $BuyOrdersEnabled = 0;}
  //if (!empty($_POST['BuyOrdersEnabled'])){$BuyOrdersEnabled = $_POST['BuyOrdersEnabled'];}else{$BuyOrdersEnabled = 0;}
  if (!empty($_POST['BuyOrdersTop'])){$BuyOrdersTop = $_POST['BuyOrdersTop'];}else{$BuyOrdersTop = 0;}
  if (!empty($_POST['BuyOrdersBtm'])){$BuyOrdersBtm = $_POST['BuyOrdersBtm'];}else{$BuyOrdersBtm = 0;}
  if (!empty($_POST['1HrEnable'])){if ($_POST['1HrEnable'] == "Yes"){$oneHrEnable = 1;}else{$oneHrEnable = 0;}}else{ $oneHrEnable = 0;}
  //if (!empty($_POST['1HrEnable'])){$oneHrEnable = $_POST['1HrEnable'];}else{$oneHrEnable = 0;}
  if (!empty($_POST['PriceChange1HrTop'])){$PriceChange1HrTop = $_POST['PriceChange1HrTop'];}else{$PriceChange1HrTop = 0;}
  if (!empty($_POST['PriceChange1HrBtm'])){$PriceChange1HrBtm = $_POST['PriceChange1HrBtm'];}else{$PriceChange1HrBtm = 0;}
  if (!empty($_POST['24HrEnable'])){if ($_POST['24HrEnable'] == "Yes"){$t4HrEnable = 1;}else{$t4HrEnable = 0;}}else{ $t4HrEnable = 0;}
  //if (!empty($_POST['24HrEnable'])){$t4HrEnable = $_POST['24HrEnable'];}else{$t4HrEnable = 0;}
  if (!empty($_POST['PriceChange24HrTop'])){$PriceChange24HrTop = $_POST['PriceChange24HrTop'];}else{$PriceChange24HrTop = 0;}
  if (!empty($_POST['PriceChange24HrBtm'])){$PriceChange24HrBtm = $_POST['PriceChange24HrBtm'];}else{$PriceChange24HrBtm = 0;}
  if (!empty($_POST['7DEnable'])){if ($_POST['7DEnable'] == "Yes"){$sDEnable = 1;}else{$sDEnable = 0;}}else{ $sDEnable = 0;}
  //if (!empty($_POST['7DEnable'])){$sDEnable = $_POST['7DEnable'];}else{$sDEnable = 0;}
  if (!empty($_POST['PriceChange7DTop'])){$PriceChange7DTop = $_POST['PriceChange7DTop'];}else{$PriceChange7DTop = 0;}
  if (!empty($_POST['PriceChange7DBtm'])){$PriceChange7DBtm = $_POST['PriceChange7DBtm'];}else{$PriceChange7DBtm = 0;}
  if (!empty($_POST['BuyPatternEnabled'])){if ($_POST['BuyPatternEnabled'] == "Yes"){$BuyPatternEnabled = 1;}else{$BuyPatternEnabled = 0;}}else{ $BuyPatternEnabled = 0;}
  //if (!empty($_POST['BuyPatternEnabled'])){$BuyPatternEnabled = $_POST['BuyPatternEnabled'];}else{$BuyPatternEnabled = 0;}
  //if (!empty($_POST['BuyPattern1'])){$BuyPattern1 = $_POST['BuyPattern1'];}else{$BuyPattern1 = "";}
  //if (!empty($_POST['BuyPattern2'])){$BuyPattern2 = $_POST['BuyPattern2'];}else{$BuyPattern2 = "";}
  //if (!empty($_POST['BuyPattern3'])){$BuyPattern3 = $_POST['BuyPattern3'];}else{$BuyPattern3 = "";}
  //if (!empty($_POST['BuyPattern4'])){$BuyPattern4 = $_POST['BuyPattern4'];}else{$BuyPattern4 = "";}
  if (!empty($_POST['PriceDiff1Enable'])){if ($_POST['PriceDiff1Enable'] == "Yes"){$PriceDiff1Enable = 1;}else{$PriceDiff1Enable = 0;}}else{ $PriceDiff1Enable = 0;}
  //if (!empty($_POST['PriceDiff1Enable'])){$PriceDiff1Enable = $_POST['PriceDiff1Enable'];}else{$PriceDiff1Enable = 0;}
  if (!empty($_POST['PriceDiff1Top'])){$PriceDiff1Top = $_POST['PriceDiff1Top'];}else{$PriceDiff1Top = 0;}
  if (!empty($_POST['PriceDiff1Btm'])){$PriceDiff1Btm = $_POST['PriceDiff1Btm'];}else{$PriceDiff1Btm = 0;}
  //if (!empty($_POST['PriceDiff2Enable'])){if ($_POST['PriceDiff2Enable'] == "Yes"){$PriceDiff2Enable = 1;}else{$PriceDiff2Enable = 0;}}else{ $PriceDiff2Enable = 0;}
  //if (!empty($_POST['PriceDiff2Enable'])){$PriceDiff2Enable = $_POST['PriceDiff2Enable'];}else{$PriceDiff2Enable = 0;}
  //if (!empty($_POST['PriceDiff2Top'])){$PriceDiff2Top = $_POST['PriceDiff2Top'];}else{$PriceDiff2Top = 0;}
  //if (!empty($_POST['PriceDiff2Btm'])){$PriceDiff2Btm = $_POST['PriceDiff2Btm'];}else{$PriceDiff2Btm = 0;}
  //if (!empty($_POST['PriceDiff3Enable'])){if ($_POST['PriceDiff3Enable'] == "Yes"){$PriceDiff3Enable = 1;}else{$PriceDiff3Enable = 0;}}else{ $PriceDiff3Enable = 0;}
  //if (!empty($_POST['PriceDiff3Enable'])){$PriceDiff3Enable = $_POST['PriceDiff3Enable'];}else{$PriceDiff3Enable = 0;}
  //if (!empty($_POST['PriceDiff3Top'])){$PriceDiff3Top = $_POST['PriceDiff3Top'];}else{$PriceDiff3Top = 0;}
  //if (!empty($_POST['PriceDiff3Btm'])){$PriceDiff3Btm = $_POST['PriceDiff3Btm'];}else{$PriceDiff3Btm = 0;}
  //if (!empty($_POST['PriceDiff4Enable'])){if ($_POST['PriceDiff4Enable'] == "Yes"){$PriceDiff4Enable = 1;}else{$PriceDiff4Enable = 0;}}else{ $PriceDiff4Enable = 0;}
  //if (!empty($_POST['PriceDiff4Enable'])){$PriceDiff4Enable = $_POST['PriceDiff4Enable'];}else{$PriceDiff4Enable = 0;}
  //if (!empty($_POST['PriceDiff4Top'])){$PriceDiff4Top = $_POST['PriceDiff4Top'];}else{$PriceDiff4Top = 0;}
  //if (!empty($_POST['PriceDiff4Btm'])){$PriceDiff4Btm = $_POST['PriceDiff4Btm'];}else{$PriceDiff4Btm = 0;}
  if (!empty($_POST['sendEmail'])){if ($_POST['sendEmail'] == "Yes"){$sendEmail = 1;}else{$sendEmail = 0;}}else{ $sendEmail = 0;}
  //if (!empty($_POST['sendEmail'])){ $sendEmail = $_POST['sendEmail'];}else{ $sendEmail = 0;}
  if (!empty($_POST['sellCoin'])){if ($_POST['sellCoin'] == "Yes"){$sellCoin = 1;}else{$sellCoin = 0;}}else{ $sellCoin = 0;}
  //if (!empty($_POST['sellCoin'])){ $sellCoin = $_POST['sellCoin'];}else{ $sellCoin = 0;}
  if (!empty($_POST['ProfitSaleEnable'])){if ($_POST['ProfitSaleEnable'] == "Yes"){$ProfitSaleEnable = 1;}else{$ProfitSaleEnable = 0;}}else{ $ProfitSaleEnable = 0;}
  //if (!empty($_POST['ProfitSaleEnable'])){$ProfitSaleEnable = $_POST['ProfitSaleEnable'];}else{$ProfitSaleEnable = 0;}
  if (!empty($_POST['ProfitSaleTop'])){$ProfitSaleTop = $_POST['ProfitSaleTop'];}else{$ProfitSaleTop = 0;}
  if (!empty($_POST['ProfitSaleBtm'])){$ProfitSaleBtm = $_POST['ProfitSaleBtm'];}else{$ProfitSaleBtm = 0;}

  $sellPriceMinEnabled = postDataYesNo($_POST['sellPriceMinEnabled']);
  $sellPriceMin = postData($_POST['sellPriceMin']);
  $limitToCoin = $_POST['limitToCoin'];
  $sellPatternEnabled = postDataYesNo($_POST['SellPatternEnabled']);
  $sellPattern =  $_POST['SellPattern'];
  $autoSellCoinEnabled = postDataYesNo($_POST['AutoSellCoinEnabled']);
  $coinPricePatternEnabled = postDataYesNo($_POST['CoinPricePatternEnabled']);
  $coinPricePattern = $_POST['CoinPricePattern'];
  // Create connection
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $userID = $_SESSION['ID'];
  $sql = "UPDATE `SellRules` SET `SellCoin`=$sellCoin,`SendEmail`=$sendEmail,`MarketCapEnabled`=$MarketCapEnable,`MarketCapTop`=$MarketCapTop,
  `MarketCapBtm`=$MarketCapBtm,`1HrChangeEnabled`=$oneHrEnable,`1HrChangeTop`=$PriceChange1HrTop,`1HrChangeBtm`=$PriceChange1HrBtm,`24HrChangeEnabled`=$t4HrEnable,`24HrChangeTop`=$PriceChange24HrTop,`24HrChangeBtm`=$PriceChange24HrBtm,`7DChangeEnabled`=$sDEnable,
  `7DChangeTop`=$PriceChange7DTop,`7DChangeBtm`=$PriceChange7DBtm,`ProfitPctEnabled`=$ProfitSaleEnable,`ProfitPctTop`=$ProfitSaleTop,`ProfitPctBtm`=$ProfitSaleBtm,`CoinPriceEnabled`=$PriceDiff1Enable,`CoinPriceTop`=$PriceDiff1Top,`CoinPriceBtm`=$PriceDiff1Btm,
  `SellOrdersEnabled`=$BuyOrdersEnabled,`SellOrdersTop`=$BuyOrdersTop,`SellOrdersBtm`=$BuyOrdersBtm,`VolumeEnabled`=$VolumeEnable,`VolumeTop`=$VolumeTop,`VolumeBtm`=$VolumeBtm ,`sellPriceMinEnabled`=$sellPriceMinEnabled,`sellPriceMin`=$sellPriceMin
  ,`AutoSellCoinEnabled` = $autoSellCoinEnabled, `LimitToCoinID` = (SELECT `ID` FROM `Coin` WHERE `Symbol` = '$limitToCoin' and `BuyCoin` = 1), `LimitToCoin` = '$limitToCoin', `SellPatternEnabled` = $sellPatternEnabled, `SellPattern` = '$sellPattern',
  `CoinPricePatternEnabled` = $coinPricePatternEnabled, `CoinPricePattern` = '$coinPricePattern'
  WHERE `ID` = $id";
  print_r($sql);


  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();
  header('Location: SellSettings.php');
}

function getRules($id){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT
`ID`,`UserID`,`SellCoin`,`SendEmail`,`BuyOrdersEnabled`,`BuyOrdersTop`,`BuyOrdersBtm`,`MarketCapEnabled`, `MarketCapTop`, `MarketCapBtm`, `1HrChangeEnabled`,
 `1HrChangeTop`, `1HrChangeBtm`, `24HrChangeEnabled`, `24HrChangeTop`,`24HrChangeBtm`, `7DChangeEnabled`, `7DChangeTop`, `7DChangeBtm`, `ProfitPctEnabled`,
 `ProfitPctTop`, `ProfitPctBtm`, `CoinPriceEnabled`, `CoinPriceTop`, `CoinPriceBtm`, `SellOrdersEnabled`, `SellOrdersTop`, `SellOrdersBtm`, `VolumeEnabled`,
  `VolumeTop`, `VolumeBtm`, `Email`, `UserName`, `APIKey`, `APISecret`,`SellPriceMinEnabled`,`SellPriceMin`,`LimitToCoin`,`AutoSellCoinEnabled`, `AutoSellPrice`
  ,`SellPatternEnabled`, `SellPattern`,`CoinPricePatternEnabled`,`CoinPricePattern`
FROM `UserSellRules` WHERE `ID` = $id";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  //print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['UserID'],$row['SellCoin'],$row['SendEmail'],$row['BuyOrdersEnabled'],$row['BuyOrdersTop'],$row['BuyOrdersBtm'],
      $row['MarketCapEnabled'],$row['MarketCapTop'],$row['MarketCapBtm'],$row['1HrChangeEnabled'],$row['1HrChangeTop'],$row['1HrChangeBtm'],$row['24HrChangeEnabled'],
      $row['24HrChangeTop'],$row['24HrChangeBtm'],$row['7DChangeEnabled'],$row['7DChangeTop'],$row['7DChangeBtm'],$row['ProfitPctEnabled'],$row['ProfitPctTop'],
      $row['ProfitPctBtm'],$row['CoinPriceEnabled'],$row['CoinPriceTop'],$row['CoinPriceBtm'],$row['SellOrdersEnabled'],$row['SellOrdersTop'],$row['SellOrdersBtm'],
      $row['VolumeEnabled'],$row['VolumeTop'],$row['VolumeBtm'],$row['Email'],$row['UserName'],$row['APIKey'],$row['APISecret'],$row['SellPriceMinEnabled']
      ,$row['SellPriceMin'],$row['LimitToCoin'],$row['AutoSellCoinEnabled'],$row['AutoSellPrice'],$row['SellPatternEnabled'],$row['SellPattern'],$row['CoinPricePatternEnabled'],$row['CoinPricePattern']
    );
  }
  $conn->close();
  return $tempAry;
}


function addNewText($RealName, $idName, $value, $tabIndex){
  echo "<b>".$RealName."</b><br/>
    <input type='text' name='".$idName."' id='".$idName."' class='form-control input-lg' placeholder='User Name' value='".$value."' tabindex='".$tabIndex."'>";

}

function addNewTwoOption($RealName, $idName, $value){
  if ($value == 1 || $value == 'Yes' ){ $option1 = "Yes"; $option2 = "No";}else{$option1 = "No"; $option2 = "Yes";}
  echo "<b>$RealName</b><br/><select name='$idName' id='$idName' class='enableTextBox'>
   <option value='".$option1."'>".$option1."</option>
    <option value='".$option2."'>".$option2."</option></select>";
}

function addNewThreeOption($RealName, $idName, $value){
  if ($value == 1){$nOption1 = "Up"; $nOption2 = "Equal";$nOption3 = "Down";}
  elseif ($RealName == -1){$nOption1 = "Down"; $nOption2 = "Equal";$nOption3 = "Up";}
  else{$nOption1 = "Equal"; $nOption2 = "Down";$nOption3 = "Up";}
  echo "<b>$RealName</b><br/><select name='$idName' id='$idName' class='enableTextBox'>
    <option value='".$nOption1."'>".$nOption1."</option>
    <option value='".$nOption2."'>".$nOption2."</option>
    <option value='".$nOption3."'>".$nOption3."</option></select>";
}

function displayEdit($id){
  $formSettings = getRules($id);
  $_GET['edit'] = null;
  echo "<h3><a href='Settings.php'>User Settings</a> &nbsp > &nbsp <a href='BuySettings.php'>Buy Settings</a> &nbsp > &nbsp <a href='SellSettings.php'>Sell Settings</a></h3>";
  echo "<form action='AddNewSettingSell.php?editedUserReady=".$id."' method='post'>";
  echo "<div class='settingsform'>";
    echo "<H3>Market Cap</H3>";
  addNewTwoOption('MarketCapEnable: ','VolumeEnable',$formSettings[0][7]);
  addNewText('MarketCapTop: ','VolumeTop',$formSettings[0][8],37);
  addNewText('MarketCapBtm: ','VolumeBtm',$formSettings[0][9],37);
  echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>Volume</H3>";
  //if ($formSettings[0][7] == 1){ $option1 = "Yes"; $option2 = "No";}else{$option1 = "No"; $option2 = "Yes";}
  //echo "<div class='settingsform'>
  //  <b>MarketCapEnable: </b><br/><select name='MarketCapEnable' id='MarketCapEnable' class='enableTextBox'>
   //<option value='".$option1."'>".$option1."</option>
    //<option value='".$option2."'>".$option2."</option></select></div>";

  //echo "<div class='settingsform'>
  //  <b>MarketCapEnable: </b><br/>
  //  <input type='text' name='MarketCapEnable' id='MarketCapEnable' class='enableTextBox' placeholder='User Name' value='".$formSettings[0][0]."' tabindex='1'>
  //</div>";
  //echo "<div class='settingsform'>
  //  <b>MarketCapTop: </b><br/>
  //  <input type='text' name='MarketCapTop' id='MarketCapTop' class='form-control input-lg' placeholder='User Name' value='".$formSettings[0][8]."' tabindex='2'>
  //</div>";
  //echo "<div class='settingsform'>
  //  <b>MarketCapBtm: </b><br/>
  //  <input type='text' name='MarketCapBtm' id='MarketCapBtm' class='form-control input-lg' placeholder='User Name' value='".$formSettings[0][9]."' tabindex='3'>
  //</div>";
  addNewTwoOption('VolumeEnable: ','VolumeEnable',$formSettings[0][28]);
  addNewText('VolumeTop: ','VolumeTop',$formSettings[0][29],37);
  addNewText('VolumeBtm: ','VolumeBtm',$formSettings[0][30],37);
  //if ($formSettings[0][28] == 1){ $option1 = "Yes"; $option2 = "No";}else{$option1 = "No"; $option2 = "Yes";}
  //echo "<div class='settingsform'>
  //  <b>VolumeEnable: </b><br/><select name='VolumeEnable' id='VolumeEnable' class='enableTextBox'>
   //<option value='".$option1."'>".$option1."</option>
    //<option value='".$option2."'>".$option2."</option></select></div>";
  //echo "<div class='settingsform'>
  //  <b>VolumeEnable: </b><br/>
  //  <input type='text' name='VolumeEnable' id='VolumeEnable' class='enableTextBox' placeholder='User Name' value='".$formSettings[0][3]."' tabindex='4'>
  //</div>";
  //echo "<div class='settingsform'>
  //  <b>VolumeTop: </b><br/>
  //  <input type='text' name='VolumeTop' id='VolumeTop' class='form-control input-lg' placeholder='User Name' value='".$formSettings[0][29]."' tabindex='5'>
  //</div>";
  //echo "<div class='settingsform'>
  //  <b>VolumeBtm: </b><br/>
  //  <input type='text' name='VolumeBtm' id='VolumeBtm' class='form-control input-lg' placeholder='User Name' value='".$formSettings[0][30]."' tabindex='6'>
  //</div><br>";
  echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>Sell Orders</H3>";
  addNewTwoOption('SellOrdersEnabled: ','VolumeEnable',$formSettings[0][25]);
  addNewText('SellOrdersTop: ','BuyOrdersTop',$formSettings[0][26],37);
  addNewText('SellOrdersBtm: ','BuyOrdersBtm',$formSettings[0][27],37);
  //if ($formSettings[0][25] == 1){ $option1 = "Yes"; $option2 = "No";}else{$option1 = "No"; $option2 = "Yes";}
  //echo "<div class='settingsform'>
  //  <b>SellOrdersEnabled: </b><br/><select name='BuyOrdersEnabled' id='BuyOrdersEnabled' class='enableTextBox'>
  // <option value='".$option1."'>".$option1."</option>
  //  <option value='".$option2."'>".$option2."</option></select></div>";
  //echo "<div class='settingsform'>
  //  <b>BuyOrdersEnabled: </b><br/>
  //  <input type='text' name='BuyOrdersEnabled' id='BuyOrdersEnabled' class='enableTextBox' placeholder='User Name' value='".$formSettings[0][6]."' tabindex='7'>
  //</div>";
  //echo "<div class='settingsform'>
  //  <b>SellOrdersTop: </b><br/>
  //  <input type='text' name='BuyOrdersTop' id='BuyOrdersTop' class='form-control input-lg' placeholder='User Name' value='".$formSettings[0][26]."' tabindex='8'>
  //</div>";
  //echo "<div class='settingsform'>
  //  <b>SellOrdersBtm: </b><br/>
  //  <input type='text' name='BuyOrdersBtm' id='BuyOrdersBtm' class='form-control input-lg' placeholder='User Name' value='".$formSettings[0][27]."' tabindex='9'>
  //</div>";
  echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>1 Hour Price</H3>";
  addNewTwoOption('1HrEnable: ','1HrEnable',$formSettings[0][10]);
  addNewText('PriceChange1HrTop: ','PriceChange1HrTop',$formSettings[0][11],37);
  addNewText('PriceChange1HrBtm: ','PriceChange1HrBtm',$formSettings[0][12],37);
  //if ($formSettings[0][10] == 1){ $option1 = "Yes"; $option2 = "No";}else{$option1 = "No"; $option2 = "Yes";}
  //echo "<div class='settingsform'>
  //  <b>1HrEnable: </b><br/><select name='1HrEnable' id='1HrEnable' class='enableTextBox'>
  // <option value='".$option1."'>".$option1."</option>
  //  <option value='".$option2."'>".$option2."</option></select></div>";
  //echo "<div class='settingsform'>
  //  <b>1HrEnable: </b><br/>
  //  <input type='text' name='1HrEnable' id='1HrEnable' class='enableTextBox' placeholder='User Name' value='".$formSettings[0][9]."' tabindex='10'>
  //</div>";
  //echo "<div class='settingsform'>
  //  <b>PriceChange1HrTop: </b><br/>
  //  <input type='text' name='PriceChange1HrTop' id='PriceChange1HrTop' class='form-control input-lg' placeholder='User Name' value='".$formSettings[0][11]."' tabindex='11'>
  //</div>";
  //echo "<div class='settingsform'>
  //  <b>PriceChange1HrBtm: </b><br/>
  //  <input type='text' name='PriceChange1HrBtm' id='PriceChange1HrBtm' class='form-control input-lg' placeholder='User Name' value='".$formSettings[0][12]."' tabindex='12'>
  //</div>";
  echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>24 Hour Price</H3>";
  addNewTwoOption('24HrEnable:','24HrEnable',$formSettings[0][13]);
  addNewText('PriceChange24HrTop: ','PriceChange24HrTop',$formSettings[0][14],37);
  addNewText('PriceChange24HrBtm: ','PriceChange24HrBtm',$formSettings[0][15],37);
  //if ($formSettings[0][13] == 1){ $option1 = "Yes"; $option2 = "No";}else{$option1 = "No"; $option2 = "Yes";}
  //echo "<div class='settingsform'>
  //  <b>24HrEnable: </b><br/><select name='24HrEnable' id='24HrEnable' class='enableTextBox'>
  // <option value='".$option1."'>".$option1."</option>
  //  <option value='".$option2."'>".$option2."</option></select></div>";
  //echo "<div class='settingsform'>
  //  <b>24HrEnable: </b><br/>
  //  <input type='text' name='24HrEnable' id='24HrEnable' class='enableTextBox' placeholder='User Name' value='".$formSettings[0][12]."' tabindex='13'>
  //</div>";
  //echo "<div class='settingsform'>
  //  <b>PriceChange24HrTop: </b><br/>
  //  <input type='text' name='PriceChange24HrTop' id='PriceChange24HrTop' class='form-control input-lg' placeholder='User Name' value='".$formSettings[0][14]."' tabindex='14'>
  //</div>";
  //echo "<div class='settingsform'>
  //  <b>PriceChange24HrBtm: </b><br/>
  //  <input type='text' name='PriceChange24HrBtm' id='PriceChange24HrBtm' class='form-control input-lg' placeholder='User Name' value='".$formSettings[0][15]."' tabindex='15'>
  //</div>";
  echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>7 Day Price</H3>";
  addNewTwoOption('7DEnable: ','7DEnable',$formSettings[0][16]);
  addNewText('PriceChange7DTop: ','PriceChange7DTop',$formSettings[0][17],37);
  addNewText('PriceChange7DBtm: ','PriceChange7DBtm',$formSettings[0][18],37);
  //if ($formSettings[0][16] == 1){ $option1 = "Yes"; $option2 = "No";}else{$option1 = "No"; $option2 = "Yes";}
  //echo "<div class='settingsform'>
  //  <b>7DEnable: </b><br/><select name='7DEnable' id='7DEnable' class='enableTextBox'>
   //<option value='".$option1."'>".$option1."</option>
    //<option value='".$option2."'>".$option2."</option></select></div>";
  //echo "<div class='settingsform'>
  //  <b>7DEnable: </b><br/>
  //  <input type='text' name='7DEnable' id='7DEnable' class='enableTextBox' placeholder='User Name' value='".$formSettings[0][15]."' tabindex='16'>
  //</div>";
  //echo "<div class='settingsform'>
  //  <b>PriceChange7DTop: </b><br/>
  //  <input type='text' name='PriceChange7DTop' id='PriceChange7DTop' class='form-control input-lg' placeholder='User Name' value='".$formSettings[0][17]."' tabindex='17'>
  //</div>";
  //echo "<div class='settingsform'>
  //  <b>PriceChange7DBtm: </b><br/>
  //  <input type='text' name='PriceChange7DBtm' id='PriceChange7DBtm' class='form-control input-lg' placeholder='User Name' value='".$formSettings[0][18]."' tabindex='18'>
  //</div>";
  echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>Price Difference</H3>";
  addNewTwoOption('PriceDiff1Enable: ','PriceDiff1Enable',$formSettings[0][22]);
  addNewText('PriceDiff1Top: ','PriceDiff1Top',$formSettings[0][23],37);
  addNewText('PriceDiff1Btm: ','PriceDiff1Btm',$formSettings[0][24],37);
  //if ($formSettings[0][22] == 1){ $option1 = "Yes"; $option2 = "No";}else{$option1 = "No"; $option2 = "Yes";}
  //echo "<div class='settingsform'>
  //  <b>PriceDiff1Enable: </b><br/><select name='PriceDiff1Enable' id='PriceDiff1Enable' class='enableTextBox'>
  // <option value='".$option1."'>".$option1."</option>
  //  <option value='".$option2."'>".$option2."</option></select></div>";
  //echo "<div class='settingsform'>
  //  <b>PriceDiff1Enable: </b><br/>
  //  <input type='text' name='PriceDiff1Enable' id='PriceDiff1Enable' class='enableTextBox' placeholder='User Name' value='".$formSettings[0][23]."' tabindex='24'>
  //</div>";
  //echo "<div class='settingsform'>
  //  <b>PriceDiff1Top: </b><br/>
  //  <input type='text' name='PriceDiff1Top' id='PriceDiff1Top' class='form-control input-lg' placeholder='User Name' value='".$formSettings[0][23]."' tabindex='25'>
  //</div>";
  //echo "<div class='settingsform'>
  //  <b>PriceDiff1Btm: </b><br/>
  //  <input type='text' name='PriceDiff1Btm' id='PriceDiff1Btm' class='form-control input-lg' placeholder='User Name' value='".$formSettings[0][24]."' tabindex='26'>
  //</div>";
  echo "</div>";
  echo "<div class='settingsform'>";

  echo "<div class='settingsform'>";
  echo "<H3>Profit Sale</H3>";
  addNewTwoOption('Profit Sale Enable: ','ProfitSaleEnable',$formSettings[0][19]);
  addNewText('Profit Sale Top: ','ProfitSaleTop',$formSettings[0][20],37);
  addNewText('rofit Sale Btm: ','ProfitSaleBtm',$formSettings[0][21],37);
  //if ($formSettings[0][19] == 1){ $option1 = "Yes"; $option2 = "No";}else{$option1 = "No"; $option2 = "Yes";}
  //echo "<div class='settingsform'>
  //  <b>Profit Sale Enable: </b><br/><select name='ProfitSaleEnable' id='ProfitSaleEnable' class='enableTextBox'>
  // <option value='".$option1."'>".$option1."</option>
  //  <option value='".$option2."'>".$option2."</option></select></div>";
  //echo "<div class='settingsform'>
  //  <b>Profit Sale Enable: </b><br/>
  //  <input type='text' name='ProfitSaleEnable' id='ProfitSaleEnable' class='enableTextBox' placeholder='User Name' value='".$formSettings[0][37]."' tabindex='33'>
  //</div>";
  //echo "<div class='settingsform'>
  //  <b>Profit Sale Top: </b><br/>
  //  <input type='text' name='ProfitSaleTop' id='ProfitSaleTop' class='form-control input-lg' placeholder='User Name' value='".$formSettings[0][20]."' tabindex='34'>
  //</div>";
  //echo "<div class='settingsform'>
  //  <b>Profit Sale Btm: </b><br/>
  //  <input type='text' name='ProfitSaleBtm' id='ProfitSaleBtm' class='form-control input-lg' placeholder='User Name' value='".$formSettings[0][21]."' tabindex='35'>
  //</div>";
  echo "</div>";

  echo "<div class='settingsform'>";
  echo "<H3>Sell Pattern</H3>";
  addNewTwoOption('Sell Pattern Enabled:','SellPatternEnabled',$formSettings[0][40]);
  addNewText('Sell Pattern: ','SellPattern',$formSettings[0][41],40);
  echo "</div>";
  echo "<div class='settingsform'>";
  echo "<H3>Coin Price Pattern</H3>";
  addNewTwoOption('Coin Price Pattern Enabled:','CoinPricePatternEnabled',$formSettings[0][42]);
  addNewText('Coin Price Pattern: ','CoinPricePattern',$formSettings[0][43],41);
  echo "</div>";
  echo "<H3>Admin</H3>";
  addNewTwoOption('Send Email: ','sendEmail',$formSettings[0][3]);
  //if ($formSettings[0][3] == 1){ $option1 = "Yes"; $option2 = "No";}else{$option1 = "No"; $option2 = "Yes";}
  //echo "<div class='settingsform'>
  //  <b>Send Email: </b><br/><select name='sendEmail' id='sendEmail' class='enableTextBox'>
  // <option value='".$option1."'>".$option1."</option>
  //  <option value='".$option2."'>".$option2."</option></select></div>";
  //echo "<div class='settingsform'>
  //  <b>Send Email: </b><br/>
  //  <input type='text' name='sendEmail' id='sendEmail' class='form-control input-lg' placeholder='User Name' value='".$formSettings[0][35]."' tabindex='36'>
  //</div>";
  addNewTwoOption('Sell Coin: ','sellCoin',$formSettings[0][2]);
  addNewTwoOption('Sell Price Min Enabled:','sellPriceMinEnabled',$formSettings[0][35]);
  addNewText('Sell Price Min: ','sellPriceMin',$formSettings[0][36],37);

  addNewText('Limit To Coin: ','limitToCoin',$formSettings[0][37],38);
  addNewTwoOption('Auto Sell Enabled:','AutoSellCoinEnabled',$formSettings[0][38]);
  addNewText('Auto Sell Price: ','AutoSellPrice',$formSettings[0][39],39);
  //if ($formSettings[0][2] == 1){ $option1 = "Yes"; $option2 = "No";}else{$option1 = "No"; $option2 = "Yes";}
  //echo "<div class='settingsform'>
  //  <b>Sell Coin: </b><br/><select name='sellCoin' id='sellCoin' class='enableTextBox'>
  // <option value='".$option1."'>".$option1."</option>
  //  <option value='".$option2."'>".$option2."</option></select></div>";
  //echo "<div class='settingsform'>
  //  <b>Sell Coin: </b><br/>
  //  <input type='text' name='sellCoin' id='sellCoin' class='form-control input-lg' placeholder='User Name' value='".$formSettings[0][36]."' tabindex='37'>
  //</div>";
  echo "</div>";
  echo "<div class='settingsform'>
    <input type='submit' name='submit' value='Update' class='settingsformsubmit' tabindex='39'>
  </div>";
  echo "</form>";
}
displaySideColumn();
?>
</body>
</html>
