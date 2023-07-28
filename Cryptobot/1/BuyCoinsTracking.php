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
include_once ('../../../../SQLData.php');
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
        //echo "<h3><a href='BuyCoins.php'>Buy Coins</a> &nbsp > &nbsp <a href='BuyCoinsFilter.php'>Buy Coins Filter</a> &nbsp > &nbsp <a href='BuyCoinsTracking.php'>Buy Coins Tracking</a>&nbsp > &nbsp <a href='BuyCoins_Spread.php'>Buy Coins Spread Bet</a>
        //&nbsp > &nbsp <a href='BuyCoins_BuyBack.php'>Buy Back</a></h3>";
        displaySubHeader("BuyCoin");
        //if($_SESSION['isMobile'] == False){

          NewEcho("<Table><th>&nbspCoin</th>",$_SESSION['isMobile'],2);
          NewEcho("<TH>&nbspBase Currency</th>",$_SESSION['isMobile'],0);
          NewEcho("<TH>&nbspPrice</th><TH>&nbspDif To Buy</th><TH>&nbspDif To Buy %</th>",$_SESSION['isMobile'],2);
          NewEcho("<TH>&nbspUserName</th><TH>&nbspBuyCoin</th><TH>&nbspSendEmail</th>",$_SESSION['isMobile'],0);
          NewEcho("<TH>&nbspQuantity</th>",$_SESSION['isMobile'],2);
          NewEcho("<TH>&nbspRuleID</th><TH>&nbspBuyType</th>",$_SESSION['isMobile'],0);
        //}

          NewEcho("<TH>&nbspTime To Cancel Mins</th>",$_SESSION['isMobile'],0);
          NewEcho("<TH>&nbspFixed Sell Rule</th><TH>&nbspLive Coin Price</th>",$_SESSION['isMobile'],2);
          NewEcho("<TH>&nbspPct Profit</th><TH>&nbspMinutes From Buy</th><TH>&nbspNo Of Rises In Price</th><TH>&nbspOriginal Price</th><TH>&nbspType</th><TH>&nbspCancel</th><tr>",$_SESSION['isMobile'],2);
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
          $minsFromBuy = $tracking[$x][24]; $coinPrice = $tracking[$x][1]; $totalRisesInPrice = $tracking[$x][31];
          $originalPrice = $tracking[$x][30]; $NoOfRisesInPrice = $tracking[$x][26]; $trackingType = $tracking[$x][39];
          //TestRules
          Echo "<TR>";
          $differenceToBuy = round($liveCoinPrice - $originalPrice,$num);
          //$differenceToBuyPct = round(1-($differenceToBuy/$originalPrice)*100,$num);
          $differenceToBuyPct = ($differenceToBuy/$originalPrice)*100;
          NewEcho("<td>&nbsp$symbol</td>",$_SESSION['isMobile'],2);
          NewEcho("<td>&nbsp$baseCurrency</td>",$_SESSION['isMobile'],0);
          NewEcho("<td>&nbsp".round($coinPrice,$num)."</td>",$_SESSION['isMobile'],2);
          NewEcho("<td>&nbsp".number_format($differenceToBuy,$num,".",",")."</td>",$_SESSION['isMobile'],2);
          NewEcho("<td>&nbsp".round($differenceToBuyPct,$num)."</td>",$_SESSION['isMobile'],2);
          NewEcho("<td>&nbsp$UserName</td>",$_SESSION['isMobile'],0);
          NewEcho("<td>&nbsp$BuyCoin</td>",$_SESSION['isMobile'],0);
          NewEcho("<td>&nbsp$SendEmail</td>",$_SESSION['isMobile'],0);
          NewEcho("<td>&nbsp".round($BTCAmount,$num)."</td>",$_SESSION['isMobile'],2);
          NewEcho("<td>&nbsp$ruleIDBuy</td>",$_SESSION['isMobile'],0);
          NewEcho("<td>&nbsp$buyType</td>",$_SESSION['isMobile'],0);
          NewEcho("<td>&nbsp$timeToCancelBuyMins</td>",$_SESSION['isMobile'],0);
          NewEcho("<td>&nbsp$SellRuleFixed</td>",$_SESSION['isMobile'],2);
          NewEcho("<td>&nbsp".round($liveCoinPrice,$num)."</td>",$_SESSION['isMobile'],2);
          NewEcho("<td>&nbsp".round($pctProfit,$num)."</td>",$_SESSION['isMobile'],2);
          NewEcho("<td>&nbsp$minsFromBuy</td>",$_SESSION['isMobile'],2);
          NewEcho("<td>&nbsp$NoOfRisesInPrice / $totalRisesInPrice</td>",$_SESSION['isMobile'],2);
          NewEcho("<td>&nbsp".round($originalPrice,$num)."</td><td>$trackingType</td>",$_SESSION['isMobile'],2);

          NewEcho("<td><div title='Manual Buy!'><a href='ManualBuy.php?canTrack=Yes&trackID=$newTrackingCoinID'><i class='fas fa-shopping-cart' style='$fontSize;color:#D4EFDF'></i></a></DIV></td>",$_SESSION['isMobile'],2);
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
