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


$coinPriceMatch = getCoinPriceMatchList($_SESSION['ID']);
$coinPriceMatchSize = count($coinPriceMatch);
$coinPricePattern = getCoinPricePattenList($_SESSION['ID']);
$coinPricePatternSize = count($coinPricePattern);
$coin1HrPattern = getCoin1HrPattenList($_SESSION['ID']);
$coin1HrPatternSize = count($coin1HrPattern);






  displayHeader(7);
  ?><h3><a href='Settings.php'>User Settings</a> &nbsp > &nbsp <a href='BuySettings.php'>Buy Settings</a> &nbsp > &nbsp <a href='SellSettings.php'>Sell Settings</a> &nbsp > &nbsp <a href='Settings_Patterns.php'>Setting Patterns</a></h3><?php
  echo "<H3>Coin Price Match</H3><table>";
  for ($i=0; $i<$coinPriceMatchSize; $i++){
    $buyRuleID = $coinPriceMatch[$i][0]; $sellRuleID = $coinPriceMatch[$i][1];
    $coinID = $coinPriceMatch[$i][2];$price = $coinPriceMatch[$i][3];
    $symbol = $coinPriceMatch[$i][4];$lowPrice = $coinPriceMatch[$i][5];
    echo "<tr>";
    echo "<td>$buyRuleID</td>";
    echo "<td>$sellRuleID</td>";
    echo "<td>$coinID</td>";
    echo "<td>$symbol</td>";
    echo "</tr>";
  }

  echo "</table><H3>Coin Price Pattern</H3><table>";
  for ($j=0; $j<$coinPricePatternSize; $j++){
    $buyRuleID = $coinPricePattern[$j][0]; $sellRuleID = $coinPricePattern[$j][1];
    $coinPattern = $coinPricePattern[$j][2];$userID = $coinPricePattern[$j][3];
    echo "<tr>";
    echo "<td>$buyRuleID</td>";
    echo "<td>$sellRuleID</td>";
    echo "<td>$userID</td>";
    echo "</tr>";
  }

  echo "</table><H3>Coin 1 Hour Pattern</H3><table>";
  for ($k=0; $k<$coin1HrPatternSize; $k++){
    $buyRuleID = $coin1HrPattern[$k][0]; $sellRuleID = $coin1HrPattern[$k][1];
    $coinPattern = $coin1HrPattern[$k][2];$userID = $coin1HrPattern[$k][3];
    echo "<tr>";
    echo "<td>$buyRuleID</td>";
    echo "<td>$sellRuleID</td>";
    echo "<td>$userID</td>";
    echo "<tr>";
  }
  echo "</table>";
  displaySideColumn(); ?>

</body>
</html>
