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


//define page title
$title = 'CryptoBot';

//include header template
require('layout/header.php');


if (!empty($_POST['CoinPriceMatchNamesSelect'])){
    //echo "<BR> coin price Match Names is ".$_POST['CoinPriceMatchNamesSelect'];
    //echo "<BR>  ID is ".$_POST['CoinPriceMatchNamesSelect'];
    setNameSelection($_POST['CoinPriceMatchNamesSelect']);
}

if (!empty($_POST['CoinPricePatternNamesSelect'])){
    //echo "<BR> coin price Match Names is ".$_POST['CoinPriceMatchNamesSelect'];
    //echo "<BR>  ID is ".$_POST['CoinPriceMatchNamesSelect'];
    setNameSelectionPricePattern($_POST['CoinPricePatternNamesSelect']);
}


if (!empty($_POST['addPriceBtn'])){
      echo "<BR> addPriceBtn not empty";
      $coinID = $_POST['symbol']; $topPrice = $_POST['topPrice']; $bottomPrice =  $_POST['bttmPrice'];
      echo "<br> ADD : $symbol | Top : $topPrice | bttm: $bottomPrice";
      addpricePatterntoSQL($coinID, $topPrice, $bottomPrice);
}

if (!empty($_POST['newNameBtn'])){
      echo "<BR> New Name : ".$_POST['newNameTxt'];
}

if (!empty($_POST['removePriceBtn'])){
      echo "<BR> removePriceBtn not empty";
      $ID = $_POST['CoinPriceMatchSelect'];
      echo "<br> Remove : ID : $ID";
      removePricePatternfromSQL($ID);
}

function addpricePatterntoSQL($coinID, $price, $lowPrice){
  $userID = $_SESSION['ID'];
  $nameID = $_SESSION['coinPriceMatchNameSelected'];
  echo "$ruleID $symbol $price $userID";
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call addNewCoinPriceMatchBuy($price,$coinID,$userID,$lowPrice, $nameID);";
  //echo $sql;
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  header('Location: Settings_Patterns.php');
}

function removePricePatternfromSQL($price){
  $splitPrice = explode('+',$price);
  $coinPriceMatchNameID = $splitPrice[1]; $coinPriceMatchID = $splitPrice[0]; $userID = $splitPrice[2];
  $userID = $_SESSION['ID'];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "DELETE FROM `CoinPriceMatchRules` WHERE `CoinPriceMatchNameID` = $coinPriceMatchNameID and `CoinPriceMatchID` = $coinPriceMatchID ";
  echo $sql;
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  header('Location: Settings_Patterns.php');
}

function setNameSelection($newSelected){
  $_SESSION['coinPriceMatchNameSelected'] = $newSelected;
}

function setNameSelectionPricePattern($newSelected){
  $_SESSION['coinPricePatternNameSelected'] = $newSelected;
}

function getCoinPriceMatchSettingsLocal($whereClause = ""){
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `CoinID`,`Price`,`Symbol`,`LowPrice`,`Name`,`UserID`, `ID`,`CoinPriceMatchNameID` FROM `NewCoinPriceMatchSettingsView` $whereClause";
  echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['CoinID'],$row['Price'],$row['Symbol'],$row['LowPrice'],$row['Name'],$row['UserID'],$row['ID'],$row['CoinPriceMatchNameID']);
  }
  $conn->close();
  return $tempAry;
}

function getCoinPricePatternSettingsLocal($whereClause = ""){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Name`,`CoinPattern`,`CoinPricePatternNameID`,`ID`,`UserID` FROM `NewCoinPricePatternSettingsView` $whereClause";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Name'],$row['CoinPattern'],$row['CoinPricePatternNameID'],$row['ID'],$row['UserID']);
  }
  $conn->close();
  return $tempAry;
}

function getCoinsLocal(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Symbol`,`ID` FROM `Coin` where `buyCoin` = 1";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Symbol'],$row['ID']);
  }
  $conn->close();
  return $tempAry;
}

function displayTrendSymbols($symbolList, $name, $enabled){
  $symbolListCount = count($symbolList);
  $readOnly = "";
  //echo "<BR> ENABLED: ".$enabled;
  if ($enabled == 0){$readOnly = " style='color:Gray' readonly ";}
  Echo "<select name='$name' $readOnly>";
  for ($i=0; $i<$symbolListCount; $i++){
    $symbol = $symbolList[$i];
    $num = $i-1;
    //$name = str_replace('-1','Minus1',$name);
    echo "<option value='$num'>$symbol</option>";
  }
  echo "</select>";
}

$coinPriceMatchNames = getCoinPriceMatchNames($_SESSION['ID'], "`CoinPriceMatchName`","");
$coinPriceMatchNamesSize = count($coinPriceMatchNames);
$coins = getCoinsLocal();
$coinsSize = count($coins);
$coinPriceMatch = getCoinPriceMatchSettingsLocal("Where `CoinPriceMatchNameID` = '".$_SESSION['coinPriceMatchNameSelected']."'");
$coinPriceMatchSize = count($coinPriceMatch);

$coinPricePatternNames = getCoinPriceMatchNames($_SESSION['ID'], "`CoinPricePatternName`","");
$coinPricePatternNamesSize = count($coinPricePatternNames);
$coinPricePattern = getCoinPricePatternSettingsLocal("Where `CoinPriceMatchNameID` = '".$_SESSION['coinPriceMatchNameSelected']."'");
$coinPricePatternSize = count($coinPricePattern);
$coin1HrPattern = getCoin1HrPattenSettings();
$coin1HrPatternSize = count($coin1HrPattern);



$coinPriceMatchNameSelected = $_SESSION['coinPriceMatchNameSelected'];
$coinPricePatternNameSelected = $_SESSION['coinPricePatternNameSelected'];
$comboList = Array('-1','0','1','*');
  displayHeader(7);
  ?><h3><a href='Settings.php'>User Settings</a> &nbsp > &nbsp <a href='BuySettings.php'>Buy Settings</a> &nbsp > &nbsp <a href='SellSettings.php'>Sell Settings</a> &nbsp > &nbsp <a href='Settings_Patterns.php'>Setting Patterns</a></h3><?php
  echo "<H3>Coin Price Match</H3>";
  echo "<div><form action='Settings_Patterns.php?changeNameSelection=Y' method='post'>";
  Echo "<select name='CoinPriceMatchNamesSelect'>";
  for ($i=0; $i<$coinPriceMatchNamesSize; $i++){
    $name = $coinPriceMatchNames[$i][0]; $nameID = $coinPriceMatchNames[$i][1];
    //$coinID = $coinPriceMatch[$i][4];$price = $coinPriceMatch[$i][2];
    //$symbol = $coinPriceMatch[$i][3];$lowPrice = $coinPriceMatch[$i][1];
    Echo "<BR> $name | $coinPriceMatchNameSelected";
    if ($nameID == $coinPriceMatchNameSelected){
      echo "<option value='$nameID' selected>$name</option>";
    }else{
      echo "<option value='$nameID'>$name</option>";
    }

  }
  echo "</select>";
  echo "<input type='submit' name='publishTrend' value='Refresh'></form>";
  echo "<form action='Settings_Patterns.php?addNewName=Y' method='post'>";
    echo "<input type='text' name='newNameTxt' id='newNametxt' class='form-control input-lg' placeholder='Name' value='' tabindex='1'>";
    echo "<input type='submit' name='newNameBtn' value='Add New Name'>";
  echo "</form>";
  echo "<form action='Settings_Patterns.php?addPrice=Y' method='post'>";
  echo "<select name='CoinPriceMatchSelect' size='8'>";
  for ($l=0; $l<$coinPriceMatchSize; $l++){
      $name = $coinPriceMatch[$l][4]; $price = $coinPriceMatch[$l][1];
      $lowPrice = $coinPriceMatch[$l][3]; $symbol = $coinPriceMatch[$l][2]; $coinID = $coinPriceMatch[$l][0];
      $ID = $coinPriceMatch[$l][6]; $coinMatchNameID = $coinPriceMatch[$l][7]; $userID = $coinPriceMatch[$l][5];
      echo "<option value='$ID+$coinMatchNameID+$userID'>$symbol | $price | $lowPrice</option>";
  }
  echo "</select>";
  echo "<select name='symbol'>";
  for ($m = 0; $m<$coinsSize; $m++){
    $symbol = $coins[$m][0]; $coinID = $coins[$m][1];
    echo "<option value='$coinID'>$symbol</option>";
    //echo "<input type='text' name='symbol' id='symbol' class='form-control input-lg' placeholder='BTC' value='' tabindex='1'>";
  }
  echo "</select>";
  echo "<input type='text' name='topPrice' id='topPrice' class='form-control input-lg' placeholder='8000.00' value='' tabindex='2'>";
  echo "<input type='text' name='bttmPrice' id='bttmPrice' class='form-control input-lg' placeholder='0.00' value='' tabindex='3'>";
  echo "<input type='submit' name='addPriceBtn' value='+'>";
  echo "<input type='submit' name='removePriceBtn' value='-'>";
  echo "</form></div>";


  echo "<H3>Coin Price Pattern</H3>";
  echo "<div><form action='Settings_Patterns.php?changeNameSelection=Y' method='post'>";
  Echo "<select name='CoinPricePatternNamesSelect'>";
  for ($i=0; $i<$coinPricePatternNamesSize; $i++){
    $name = $coinPricePatternNames[$i][0]; $nameID = $coinPricePatternNames[$i][1];
    //$coinID = $coinPriceMatch[$i][4];$price = $coinPriceMatch[$i][2];
    //$symbol = $coinPriceMatch[$i][3];$lowPrice = $coinPriceMatch[$i][1];
    Echo "<BR> $name | $coinPricePatternNameSelected";
    if ($nameID == $coinPricePatternNameSelected){
      echo "<option value='$nameID' selected>$name</option>";
    }else{
      echo "<option value='$nameID'>$name</option>";
    }
  }
  echo "</select>";
  echo "<input type='submit' name='publishTrendPricePattern' value='Refresh'></form>";
  echo "<form action='Settings_Patterns.php?addNewPricePatternName=Y' method='post'>";
    echo "<input type='text' name='newNamePricePatterntxt' id='newNamePricePatterntxt' class='form-control input-lg' placeholder='Name' value='' tabindex='1'>";
    echo "<input type='submit' name='newNamePricePatternBtn' value='Add New Name'>";
  echo "</form>";
  echo "<form action='Settings_Patterns.php?addPricePattern=Y' method='post'>";
  echo "<select name='CoinPricePatternSelect' size='8'>";
  for ($j=0; $j<$coinPricePatternSize; $j++){
    $name = $coinPricePattern[$j][0]; $pattern = $coinPricePattern[$j][1];
    $nameID = $coinPricePattern[$j][2]; $patternID = $coinPricePattern[$j][3];
    echo "<option value='$patternID'>$pattern</option>";
  }
  echo "</select>";
  displayTrendSymbols($comboList,'selectCmbo1Hr1', 1);
  displayTrendSymbols($comboList,'selectCmbo1Hr2', 1);
  displayTrendSymbols($comboList,'selectCmbo1Hr3', 1);
  displayTrendSymbols($comboList,'selectCmbo1Hr4', 1);
  echo "<input type='submit' name='addPriceBtn' value='+'>";
  echo "<input type='submit' name='removePriceBtn' value='-'>";
  echo "</form></div>";



  echo "</table><H3>Coin 1 Hour Pattern</H3><table>";
  for ($k=0; $k<$coin1HrPatternSize; $k++){
    $name = $coin1HrPattern[$k][0];
    $coinPattern = $coin1HrPattern[$k][1];
    echo "<tr>";
    echo "<td>$name</td>";
    echo "<td>$coinPattern</td>";
    echo "<tr>";
  }
  echo "</table>";
  displaySideColumn(); ?>

</body>
</html>
