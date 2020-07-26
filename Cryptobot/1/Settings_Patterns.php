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

$coinPriceMatchNames = getCoinPriceMatchNamesLocal($_SESSION['ID']);
$coinPriceMatchNamesSize = count($coinPriceMatchNames);

$coinPriceMatch = getCoinPriceMatchSettings("Where `Name` = '".$_SESSION['coinPriceMatchNameSelected']."'");
$coinPriceMatchSize = count($coinPriceMatch);
$coinPricePattern = getCoinPricePattenSettings();
$coinPricePatternSize = count($coinPricePattern);
$coin1HrPattern = getCoin1HrPattenSettings();
$coin1HrPatternSize = count($coin1HrPattern);






  displayHeader(7);
  ?><h3><a href='Settings.php'>User Settings</a> &nbsp > &nbsp <a href='BuySettings.php'>Buy Settings</a> &nbsp > &nbsp <a href='SellSettings.php'>Sell Settings</a> &nbsp > &nbsp <a href='Settings_Patterns.php'>Setting Patterns</a></h3><?php
  echo "<H3>Coin Price Match</H3>";
  Echo "<select name='CoinPriceMatchNamesSelect'>";
  for ($i=0; $i<$coinPriceMatchNamesSize; $i++){
    $name = $coinPriceMatchNames[$i][0]; $nameID = $coinPriceMatchNames[$i][1];
    //$coinID = $coinPriceMatch[$i][4];$price = $coinPriceMatch[$i][2];
    //$symbol = $coinPriceMatch[$i][3];$lowPrice = $coinPriceMatch[$i][1];

    echo "<option value='$nameID'>$name</option>";
  }
  echo "</select>";
  Echo "<select name='CoinPriceMatchSelect'>";
  for ($l=0; $l<$coinPriceMatchSize; $l++){
      $name = $coinPriceMatch[$l][4]; $price = $coinPriceMatch[$l][1];
      $lowPrice = $coinPriceMatch[$l][3]; $symbol = $coinPriceMatch[$l][2]; $coinID = $coinPriceMatch[$l][0];
      echo "<option value='$coinID'>$symbol | $price | $lowPrice</option>";
  }
  echo "</select>";
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
