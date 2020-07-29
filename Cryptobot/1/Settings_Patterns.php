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

echo "<BR> Test : ".$_POST['addPrice'];
if (!empty($_POST['CoinPriceMatchNamesSelect'])){
    //echo "<BR> coin price Match Names is ".$_POST['CoinPriceMatchNamesSelect'];
    //echo "<BR>  ID is ".$_POST['CoinPriceMatchNamesSelect'];
    setNameSelection($_POST['CoinPriceMatchNamesSelect']);
}elseif (!empty($_POST['addPrice'])){
    echo "<BR> Add Price not empty!";
    if (!empty($_POST['addPriceBtn'])){
      echo "<BR> addPriceBtn not empty";
      $symbol = $_POST['symbol']; $topPrice = $_POST['topPrice']; $bottomPrice =  $_POST['bttmPrice'];
      echo "<br> ADD : $symbol | Top : $topPrice | bttm: $bottomPrice";
    }
    if (!empty($_POST['removePriceBtn'])){
      echo "<BR> removePriceBtn not empty";
      $ID = $_POST['CoinPriceMatchSelect'];
      echo "<br> Remove : ID : $ID";
    }
}

function setNameSelection($newSelected){
  $_SESSION['coinPriceMatchNameSelected'] = $newSelected;
}

function getCoinPriceMatchNamesLocal($userID){
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Name`,`ID` FROM `CoinPriceMatchName`";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Name'],$row['ID']);
  }
  $conn->close();
  return $tempAry;
}

function getCoinPriceMatchSettingsLocal($whereClause = ""){
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `CoinID`,`Price`,`Symbol`,`LowPrice`,`Name`, `ID`,`CoinPriceMatchNameID` FROM `NewCoinPriceMatchSettingsView` $whereClause";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['CoinID'],$row['Price'],$row['Symbol'],$row['LowPrice'],$row['Name'],$row['UserID'],$row['ID'],$row['CoinPriceMatchNameID']);
  }
  $conn->close();
  return $tempAry;
}

function getCoinPricePattenSettingsLocal(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Name`,`CoinPattern` FROM `NewCoinPricePatternSettingsView`";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Name'],$row['CoinPattern']);
  }
  $conn->close();
  return $tempAry;
}

$coinPriceMatchNames = getCoinPriceMatchNamesLocal($_SESSION['ID']);
$coinPriceMatchNamesSize = count($coinPriceMatchNames);

$coinPriceMatch = getCoinPriceMatchSettingsLocal("Where `CoinPriceMatchNameID` = '".$_SESSION['coinPriceMatchNameSelected']."'");
$coinPriceMatchSize = count($coinPriceMatch);

$coinPricePattern = getCoinPricePattenSettingsLocal();
$coinPricePatternSize = count($coinPricePattern);
$coin1HrPattern = getCoin1HrPattenSettings();
$coin1HrPatternSize = count($coin1HrPattern);






  displayHeader(7);
  ?><h3><a href='Settings.php'>User Settings</a> &nbsp > &nbsp <a href='BuySettings.php'>Buy Settings</a> &nbsp > &nbsp <a href='SellSettings.php'>Sell Settings</a> &nbsp > &nbsp <a href='Settings_Patterns.php'>Setting Patterns</a></h3><?php
  echo "<H3>Coin Price Match</H3>";
  echo "<div><form action='Settings_Patterns.php?click=changeNameSelection' method='post'>";
  Echo "<select name='CoinPriceMatchNamesSelect'>";
  for ($i=0; $i<$coinPriceMatchNamesSize; $i++){
    $name = $coinPriceMatchNames[$i][0]; $nameID = $coinPriceMatchNames[$i][1];
    //$coinID = $coinPriceMatch[$i][4];$price = $coinPriceMatch[$i][2];
    //$symbol = $coinPriceMatch[$i][3];$lowPrice = $coinPriceMatch[$i][1];

    echo "<option value='$nameID'>$name</option>";
  }
  echo "</select>";
  echo "<input type='submit' name='publishTrend' value='Refresh'></form>";
  echo "<form action='Settings_Patterns.php?click=addPrice' method='post'>";
  echo "<select name='CoinPriceMatchSelect' size='8'>";
  for ($l=0; $l<$coinPriceMatchSize; $l++){
      $name = $coinPriceMatch[$l][4]; $price = $coinPriceMatch[$l][1];
      $lowPrice = $coinPriceMatch[$l][3]; $symbol = $coinPriceMatch[$l][2]; $coinID = $coinPriceMatch[$l][0];
      $ID = $coinPriceMatch[$l][6]; $coinMatchNameID = $coinPriceMatch[$l][7]; $userID = $coinPriceMatch[$l][5];
      echo "<option value='$ID_$coinMatchNameID_$userID'>$symbol | $price | $lowPrice</option>";
  }
  echo "</select>";
  echo "<input type='text' name='symbol' id='symbol' class='form-control input-lg' placeholder='BTC' value='' tabindex='1'>";
  echo "<input type='text' name='topPrice' id='topPrice' class='form-control input-lg' placeholder='8000.00' value='' tabindex='2'>";
  echo "<input type='text' name='bttmPrice' id='bttmPrice' class='form-control input-lg' placeholder='0.00' value='' tabindex='3'>";
  echo "<input type='submit' name='addPriceBtn' value='+'>";
  echo "<input type='submit' name='removePriceBtn' value='-'>";
  echo "</form></div>";
  echo "<H3>Coin Price Pattern</H3><table>";
  for ($j=0; $j<$coinPricePatternSize; $j++){
    $name = $coinPricePattern[$j][0];
    $coinPattern = $coinPricePattern[$j][1];
    echo "<tr>";
    echo "<td>$name</td>";
    echo "<td>$coinPattern</td>";
    echo "</tr>";
  }

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
