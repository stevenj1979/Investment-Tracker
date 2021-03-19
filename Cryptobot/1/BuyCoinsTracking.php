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
        echo "<h3><a href='BuyCoins.php'>Buy Coins</a> &nbsp > &nbsp <a href='BuyCoinsFilter.php'>Buy Coins Filter</a> &nbsp > &nbsp <a href='BuyCoinsTracking.php'>Buy Coins Tracking</a>&nbsp > &nbsp <a href='BuyCoins_Spread.php'>Buy Coins Spread Bet</a>
        &nbsp > &nbsp <a href='BuyCoins_BuyBack.php'>Buy Back</a></h3>";
        //if($_SESSION['isMobile'] == False){

          print_r("<Table><th>&nbspCoin</th><TH>&nbspBase Currency</th><TH>&nbspPrice</th><TH>&nbspDifference To Buy</th>");
          NewEcho("<TH>&nbspUserName</th><TH>&nbspBuyCoin</th><TH>&nbspSendEmail</th>",$_SESSION['isMobile'],0);
          echo "<TH>&nbspQuantity</th>";
          NewEcho("<TH>&nbspRuleID</th><TH>&nbspBuyType</th>",$_SESSION['isMobile'],0);
        //}

        echo "<TH>&nbspTimeToCancelMins</th><TH>&nbspFixedSellRule</th>";
        echo "<TH>&nbspLiveCoinPrice</th><TH>&nbspPctProfit</th><TH>&nbspMinutesFromBuy</th><TH>&nbspNoOfRisesInPrice</th><TH>&nbspOriginalPrice</th><tr>";
        //$roundNum = 2;
        for($x = 0; $x < $newArrLength; $x++) {
          //Variables
          $APIKey = $tracking[$x][18];$APISecret = $tracking[$x][19];$KEK = $tracking[$x][20];
          $symbol = $tracking[$x][3];$baseCurrency = $tracking[$x][8];
          $Email = $tracking[$x][21];$userID = $tracking[$x][7];$UserName = $tracking[$x][22];
          $SendEmail = $tracking[$x][9];$BuyCoin = $tracking[$x][10];$BTCAmount = $tracking[$x][11];
          $ruleIDBuy = $tracking[$x][12];$coinID = $tracking[$x][0];$CoinSellOffsetPct = $tracking[$x][13];$CoinSellOffsetEnabled = $tracking[$x][14];
          $buyType = $tracking[$x][15];$timeToCancelBuyMins = $tracking[$x][16];$SellRuleFixed = $tracking[$x][17];
          $pctProfit = $tracking[$x][6]; $newTrackingCoinID = $tracking[$x][23]; $liveCoinPrice = $tracking[$x][4];
          $minsFromBuy = $tracking[$x][24]; $coinPrice = $tracking[$x][1]; $NoOfRisesInPrice = $tracking[$x][26];
          $originalPrice = $tracking[$x][30];
          //TestRules
          Echo "<TR>";
          $differenceToBuy = $liveCoinPrice - $coinPrice;
          NewEcho("<td>$symbol</td>",$_SESSION['isMobile'],2);
          NewEcho("<td>$baseCurrency</td>",$_SESSION['isMobile'],0);
          NewEcho("<td>$coinPrice</td>",$_SESSION['isMobile'],2);
          NewEcho("<td>$differenceToBuy</td>",$_SESSION['isMobile'],2);
          NewEcho("<td>$UserName</td>",$_SESSION['isMobile'],0);
          NewEcho("<td>$BuyCoin</td>",$_SESSION['isMobile'],0);
          NewEcho("<td>$SendEmail</td>",$_SESSION['isMobile'],0);
          NewEcho("<td>$BTCAmount</td>",$_SESSION['isMobile'],2);
          NewEcho("<td>$ruleIDBuy</td>",$_SESSION['isMobile'],0);
          NewEcho("<td>$buyType</td>",$_SESSION['isMobile'],0);
          NewEcho("<td>$timeToCancelBuyMins</td>",$_SESSION['isMobile'],0);
          NewEcho("<td>$SellRuleFixed</td>",$_SESSION['isMobile'],2);
          NewEcho("<td>$liveCoinPrice</td>",$_SESSION['isMobile'],2);
          NewEcho("<td>$pctProfit</td>",$_SESSION['isMobile'],2);
          NewEcho("<td>$minsFromBuy</td>",$_SESSION['isMobile'],2);
          NewEcho("<td>$NoOfRisesInPrice</td>",$_SESSION['isMobile'],2);
          NewEcho("<td>$originalPrice</td>",$_SESSION['isMobile'],2);


          Echo "</TR>";
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
