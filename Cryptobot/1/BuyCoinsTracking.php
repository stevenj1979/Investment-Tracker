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


showMain();




function showMain(){
  displayHeader(3);

        if ($_SESSION['isMobile']){ $num = 2; $fontSize = "font-size:60px"; }else{$num = 8;$fontSize = "font-size:32px"; }
        $tracking = getNewTrackingCoins($_SESSION['ID']);
        $newArrLength = count($tracking);
        //$buyRuleAry = getBuyRules($_SESSION['ID']);
        //$autoBuyPrice = getAutoBuyPrices();
        //$coinPricePatternList = getCoinPricePattenList();
        //$coin1HrPatternList = getCoin1HrPattenList();
        //$coinPriceMatch = getCoinPriceMatchList();
        //save Rules

        //print_r("<h2>Buy Some Coins Now!</h2><Table><th>&nbspCoin</th><TH>&nbspBase Currency</th><TH>&nbspPrice</th>");
        echo "<h3><a href='BuyCoins.php'>Buy Coins</a> &nbsp > &nbsp <a href='BuyCoinsFilter.php'>Buy Coins Filter</a> &nbsp > &nbsp <a href='BuyCoinsTracking.php'>Buy Coins Tracking</a></h3>";
        //if($_SESSION['isMobile'] == False){

          print_r("<Table><th>&nbspCoin</th><TH>&nbspBase Currency</th><TH>&nbspPrice</th>");
          NewEcho("<TH>&nbspUserName</th><TH>&nbspBuyCoin</th><TH>&nbspSendEmail</th>",$_SESSION['isMobile'],0);
          echo "<TH>&nbspQuantity</th>";
          NewEcho("<TH>&nbspRuleID</th><TH>&nbspBuyType</th>",$_SESSION['isMobile'],0);
        //}

        echo "<TH>&nbspTimeToCancelMins</th><TH>&nbspFixedSellRule</th>";
        echo "<TH>&nbspLiveCoinPrice</th><TH>&nbspPctProfit</th><tr>";
        //$roundNum = 2;
        for($x = 0; $x < $newArrLength; $x++) {
          //Variables
          $APIKey = $newTrackingCoins[$a][18];$APISecret = $newTrackingCoins[$a][19];$KEK = $newTrackingCoins[$a][20];
          $symbol = $newTrackingCoins[$a][3];$baseCurrency = $newTrackingCoins[$a][8];
          $Email = $newTrackingCoins[$a][21];$userID = $newTrackingCoins[$a][7];$UserName = $newTrackingCoins[$a][22];
          $SendEmail = $newTrackingCoins[$a][9];$BuyCoin = $newTrackingCoins[$a][10];$BTCAmount = $newTrackingCoins[$a][11];
          $ruleIDBuy = $newTrackingCoins[$a][12];$coinID = $newTrackingCoins[$a][0];$CoinSellOffsetPct = $newTrackingCoins[$a][13];$CoinSellOffsetEnabled = $newTrackingCoins[$a][14];
          $buyType = $newTrackingCoins[$a][15];$timeToCancelBuyMins = $newTrackingCoins[$a][16];$SellRuleFixed = $newTrackingCoins[$a][17];
          $pctProfit = $newTrackingCoins[$a][6]; $newTrackingCoinID = $newTrackingCoins[$a][23]; $liveCoinPrice = $newTrackingCoins[$a][4];
          //TestRules
          NewEcho("<td>$symbol</td>",$_SESSION['isMobile'],0);
          NewEcho("<td>$baseCurrency</td>",$_SESSION['isMobile'],0);
          NewEcho("<td>$UserName</td>",$_SESSION['isMobile'],0);
          NewEcho("<td>$BuyCoin</td>",$_SESSION['isMobile'],0);
          NewEcho("<td>$SendEmail</td>",$_SESSION['isMobile'],0);
          NewEcho("<td>$BTCAmount</td>",$_SESSION['isMobile'],0);
          NewEcho("<td>$ruleIDBuy</td>",$_SESSION['isMobile'],0);
          NewEcho("<td>$buyType</td>",$_SESSION['isMobile'],0);
          NewEcho("<td>$timeToCancelBuyMins</td>",$_SESSION['isMobile'],0);
          NewEcho("<td>$SellRuleFixed</td>",$_SESSION['isMobile'],0);
          NewEcho("<td>$liveCoinPrice</td>",$_SESSION['isMobile'],0);
          NewEcho("<td>$pctProfit</td>",$_SESSION['isMobile'],0);

        }//end for
        print_r("</table>");

        displaySideColumn();
        //displayMiddleColumn();
        //displayFarSideColumn();
        //displayFooter();
}


//include header template
require($_SERVER['DOCUMENT_ROOT'].'/Investment-Tracker/Cryptobot/1/layout/footer.php');
$date = date('Y/m/d H:i:s', time());
echo " Last Updated :".$date;
?>
