<html>
<head>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
</head>
<?php require('includes/config.php');
include_once '../includes/newConfig.php';

//if not logged in redirect to login page
if(!$user->is_logged_in()){ header('Location: login.php'); exit(); }

//define page title
$title = 'CryptoBot';
$current_url = $_SERVER[ 'REQUEST_URI' ];
header( "Refresh: 120; URL=$current_url" );
//include header template
require($_SERVER['DOCUMENT_ROOT'].'/Investment-Tracker/Cryptobot/1/layout/header.php');
include_once ('/home/stevenj1979/SQLData.php');
$locationStr = "Location: /Investment-Tracker/Cryptobot/1/m/BuyCoins.php";
setStyle($_SESSION['isMobile']);

if(isset($_GET['override'])){
if ($_SESSION['MobOverride'] == False){$_SESSION['MobOverride'] = True;}
}

if(isset($_GET['noOverride'])){
if ($_SESSION['MobOverride'] == True){$_SESSION['MobOverride'] = False;}
}

//Echo "<BR> isMobile: ".$_SESSION['isMobile']." | MobOverride: ".$_SESSION['MobOverride'];

if ($_SESSION['isMobile'] && $_SESSION['MobOverride'] == False){
header('Location: BuyCoins_Mobile.php');
}

displayHeader(10);

        if ($_SESSION['isMobile']){ $num = 2; $fontSize = "font-size:60px"; }else{$num = 8;$fontSize = "font-size:32px"; }
				$tracking = getCoinMode($_SESSION['ID']);
				$newArrLength = count($tracking);
        echo "<TABLE><TH>Symbol</TH><TH>Mode</TH><TH>Buy Rule</TH><TH>Sell Rule</TH><TH>Secondary Sell Rules</TH><TH>1 Hr Avg Price</TH><TH>24 Hr Avg Price</TH><TH>7 Day Avg Price</TH>";
        echo "<TR>";
				for($x = 0; $x < $newArrLength; $x++) {
          //Variables
          $symbol = $tracking[$x][36]; $coinMode = $tracking[$x][25]; $buyRule = $tracking[$x][0]; $sellRule = $tracking[$x][19];
          $secondarySellRules = $tracking[$x][32];$livePrice = $tracking[$x][2]; $Hr24Price = $tracking[$x][15];$D7Price= $tracking[$x][17];
          $Hr1AveragePrice = round($tracking[$x][21],3);
          //Calculations
          $pctInc24Hours = round((($livePrice - $Hr24Price)/$Hr24Price)*100,3);
          $pctInc7Day = round((($livePrice - $D7Price)/$D7Price)*100,3);
          //Table
          echo "<td>$symbol</td>"; if ($coinMode == 1){ Echo "<TD>Buy Mode</TD>";} elseif ($coinMode == 2) {Echo "<TD>Sell Mode</TD>";}
          else{Echo "<TD>Flat Mode</TD>";}
          Echo "<TD>$buyRule</TD>";Echo "<TD>$sellRule</TD>";Echo "<TD>$secondarySellRules</TD>";
          Echo "<TD>$Hr1AveragePrice</TD>";Echo "<TD>$pctInc24Hours</TD>";Echo "<TD>$pctInc7Day</TD>";
          echo "<TR>";
				}//end for
				print_r("</table>");

        displaySideColumn();
        //displayMiddleColumn();
				//displayFarSideColumn();
        //displayFooter();

//include header template
require($_SERVER['DOCUMENT_ROOT'].'/Investment-Tracker/Cryptobot/1/layout/footer.php');
?>
