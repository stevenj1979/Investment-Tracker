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

$coinPriceMatchNames = getCoinPriceMatchNames($_SESSION['ID']);
$coinPriceMatchNamesSize = count($coinPriceMatchNames);

$coinPriceMatch = getCoinPriceMatchSettings();
$coinPriceMatchSize = count($coinPriceMatch);
$coinPricePattern = getCoinPricePattenSettings();
$coinPricePatternSize = count($coinPricePattern);
$coin1HrPattern = getCoin1HrPattenSettings();
$coin1HrPatternSize = count($coin1HrPattern);






  displayHeader(7);
  ?><h3><a href='Settings.php'>User Settings</a> &nbsp > &nbsp <a href='BuySettings.php'>Buy Settings</a> &nbsp > &nbsp <a href='SellSettings.php'>Sell Settings</a> &nbsp > &nbsp <a href='Settings_Patterns.php'>Setting Patterns</a></h3><?php
  echo "<H3>Coin Price Match</H3>";
  Echo "<select name='$name'>";
  for ($i=0; $i<$coinPriceMatchNamesSize; $i++){
    $name = $coinPriceMatch[$i][0]; $nameID = $coinPriceMatch[$i][1];
    //$coinID = $coinPriceMatch[$i][4];$price = $coinPriceMatch[$i][2];
    //$symbol = $coinPriceMatch[$i][3];$lowPrice = $coinPriceMatch[$i][1];
    
    echo "<option value='$nameID'>$name</option>";
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
