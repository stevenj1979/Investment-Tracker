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



        displayHeader(8);

        Print_r("<h2>Buy Some Coins Now!</h2><Table><th>&nbspCoin</th><TH>&nbspBase Currency</th><TH>&nbspPrice</th><TH>&nbspMarket Cap %</th><TH>&nbspVolume by %</th><TH>&nbspBuy Orders %</th><TH>&nbspPrice Diff 1</th><TH>&nbspPrice Change</th><TH>&nbsp% Change 1Hr</th><TH>&nbsp% Change 24 Hrs</th>
        <TH>&nbsp% Change 7 Days</th><TH>&nbspBuy Pattern</th><TH>&nbspManual Buy</th><TH>&nbspSet Alert</th><tr>");
        $newArrLength = 3;
				for($x = 0; $x < $newArrLength; $x++) {
          echo "<BR> $x";

        }

        displaySideColumn();
        //displayMiddleColumn();
				//displayFarSideColumn();
        //displayFooter();

//include header template
require('layout/footer.php');
$date = date('Y/m/d H:i:s', time());
echo " Last Updated :".$date;
?>
