<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
</head>
<?php require('includes/config.php');
include_once '../includes/newConfig.php';?>
<style>
<?php include 'style/style.css'; ?>
</style> <?php

//if not logged in redirect to login page
if(!$user->is_logged_in()){ header('Location: login.php'); exit(); }

//define page title
$title = 'CryptoBot';
$current_url = $_SERVER[ 'REQUEST_URI' ];
header( "Refresh: 120; URL=$current_url" );
//include header template
require('layout/header.php');
include_once ('/home/stevenj1979/SQLData.php');

if($_GET['iD'] <> ""){
  Echo "<BR> ID : ".$_GET['iD'];
}


        displayHeader(8);

        echo "<h2>Coin Alerts!</h2><Table><th>&nbspID</th><TH>&nbspCoinID</th><TH>&nbspAction</th><TH>&nbspPrice</th><TH>&nbspSymbol</th><TH>&nbspUserName</th><TH>&nbspEmail</th><TH>&nbspliveCoinPrice</th><tr>";
        $coinAlerts = getCoinAlertsUser($userID);
        $newArrLength = Count($coinAlerts);
				for($x = 0; $x < $newArrLength; $x++) {
          $id = $coinAlerts[$x][0];$coinID = $coinAlerts[$x][1]; $action = $coinAlerts[$x][2];
          $price = $coinAlerts[$x][3];$symbol = $coinAlerts[$x][4]; $userName = $coinAlerts[$x][5];
          $email = $coinAlerts[$x][6];$liveCoinPrice= $coinAlerts[$x][7];
          echo "<td>$id</td><td>$coinID</td>";
          echo "<td>$action</td><td>$price</td>";
          echo "<td>$symbol</td><td>$userName</td>";
          echo "<td>$email</td><td>$liveCoinPrice</td>";
          echo "<td><a href='CoinAlerts.php?iD=$id'><i class='fas fa-shopping-cart' style='font-size:24px;color:#D4EFDF'></i></a></td>";
          echo "<TR>";
        }
        Echo "</table>";
        displaySideColumn();
        //displayMiddleColumn();
				//displayFarSideColumn();
        //displayFooter();

//include header template
require('layout/footer.php');
$date = date('Y/m/d H:i:s', time());
echo " Last Updated :".$date;
?>
