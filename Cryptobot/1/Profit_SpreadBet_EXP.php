<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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

setStyle($_SESSION['isMobile']);

function getCoinsfromSQL($userID){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    //$sql = "SELECT `UserID`,`OrderNo`,`Symbol`,`Amount`,`Cost`,`TradeDate`,`SellPrice`, `Profit`, `ETHProfit`, `DateSold`, `ID` FROM `Transaction` where `Status` = 'Sold' and `UserID` = $userID order by `DateSold` desc limit 50";
    $sql = "SELECT `CoinPrice`*if(`OriginalAmount`=0,`Amount`,`OriginalAmount`) as PurchasePrice,year(`CompletionDate`) as `Year`,month(`CompletionDate`) as `Month`,day(`CompletionDate`) as `Day`,`SellPrice`*if(`OriginalAmount`=0,`Amount`,`OriginalAmount`) as SellPrice
          ,(((`SellPrice`*if(`OriginalAmount`=0,`Amount`,`OriginalAmount`) )/100)*0.28) as Fee
          , ((`SellPrice`*if(`OriginalAmount`=0,`Amount`,`OriginalAmount`) )-(`CoinPrice`* if(`OriginalAmount`=0,`Amount`,`OriginalAmount`))-(((`SellPrice`*if(`OriginalAmount`=0,`Amount`,`OriginalAmount`) )/100)*0.28)) as Profit,`Symbol`
          ,if(`BaseCurrency` = 'BTC'
            ,((`SellPrice`*if(`OriginalAmount`=0,`Amount`,`OriginalAmount`) )-(`CoinPrice`* if(`OriginalAmount`=0,`Amount`,`OriginalAmount`))-(((`SellPrice`*if(`OriginalAmount`=0,`Amount`,`OriginalAmount`) )/100)*0.28)),0) as BTCProfit, if(`BaseCurrency` = 'USDT'
              ,((`SellPrice`*if(`OriginalAmount`=0,`Amount`,`OriginalAmount`) )-(`CoinPrice`* if(`OriginalAmount`=0,`Amount`,`OriginalAmount`))-(((`SellPrice`*if(`OriginalAmount`=0,`Amount`,`OriginalAmount`) )/100)*0.28)),0) as USDTProfit
          ,if(`BaseCurrency` = 'ETH',((`SellPrice`*if(`OriginalAmount`=0,`Amount`,`OriginalAmount`) )-(`CoinPrice`* if(`OriginalAmount`=0,`Amount`,`OriginalAmount`))-(((`SellPrice`*if(`OriginalAmount`=0,`Amount`,`OriginalAmount`) )/100)*0.28)),0) as ETHProfit,
          if(`BaseCurrency` = 'BTC',((`SellPrice`*if(`OriginalAmount`=0,`Amount`,`OriginalAmount`) )-(`CoinPrice`* if(`OriginalAmount`=0,`Amount`,`OriginalAmount`))-(((`SellPrice`*if(`OriginalAmount`=0,`Amount`,`OriginalAmount`) )/100)*0.28)* 1) ,if(`BaseCurrency` = 'ETH'
            ,((`SellPrice`*if(`OriginalAmount`=0,`Amount`,`OriginalAmount`) )-(`CoinPrice`* if(`OriginalAmount`=0,`Amount`,`OriginalAmount`))-(((`SellPrice`*if(`OriginalAmount`=0,`Amount`,`OriginalAmount`) )/100)*0.28)* 1) ,if(`BaseCurrency` = 'USDT'
              ,((`SellPrice`* if(`OriginalAmount`=0,`Amount`,`OriginalAmount`))-(`CoinPrice`* if(`OriginalAmount`=0,`Amount`,`OriginalAmount`))-(((`SellPrice`* if(`OriginalAmount`=0,`Amount`,`OriginalAmount`))/100)*0.28)) ,0)))as USDProfit
             ,`SpreadBetRuleID`,`SpreadBetTransactionID` FROM `View15_OpenTransactions`
          WHERE `UserID` = $userID and `Type` = 'SpreadSell' and `StatusTr` = 'Sold' and `SpreadBetRuleID` <> 0
          order by `CompletionDate` desc";
    $result = $conn->query($sql);
    //echo "<BR> $sql";
    //$result = mysqli_query($link4, $query);
	//mysqli_fetch_assoc($result);
    while ($row = mysqli_fetch_assoc($result)){
        $tempAry[] = Array($row['PurchasePrice'],$row['Year'],$row['Month'],$row['Day'],$row['SellPrice'],$row['Fee'],$row['Profit'],$row['Symbol'],$row['BTCProfit'],$row['USDTProfit'],$row['ETHProfit']
        ,$row['USDProfit'],$row['SpreadBetRuleID'],$row['SpreadBetTransactionID']);
    }
    $conn->close();
    return $tempAry;
}

function getCoinPriceLoc(){

    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT `Symbol`,`Price`,`LastUpdated` FROM `TrackingCoins`";
    $result = $conn->query($sql);
    //$result = mysqli_query($link4, $query);
	//mysqli_fetch_assoc($result);
    while ($row = mysqli_fetch_assoc($result)){
        $tempAry[] = Array($row['Symbol'],$row['Price'],$row['LastUpdated']);
    }
    $conn->close();
    return $tempAry;
}

function getTrackingCoinsLoc(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Symbol`,`BuyPattern`,`MarketCapHigherThan5Pct`,`VolumeHigherThan5Pct`,`BuyOrdersHigherThan5Pct`, `PctChange` FROM `CryptoBotCoinPurchaseDecisionView`";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
//mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Symbol'],$row['BuyPattern'],$row['MarketCapHigherThan5Pct'],$row['VolumeHigherThan5Pct'],$row['BuyOrdersHigherThan5Pct'],$row['PctChange']);
  }
  $conn->close();
  return $tempAry;
}

function getProfitTotalLoc($userID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT sum(`Profit`) as Profit FROM `CoinProfitView` WHERE `UserID` = $userID and `Status` = 'Sold' ";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
//mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Profit']);
  }
  $conn->close();
  return $tempAry;
}

function getColour($ColourText){
  if ($ColourText == "True"){
    $colour = "Green" ;
  }else if ($ColourText == "False"){
    $colour = "Red";
  }
  return $colour;
}

function sendEmailLoc($to, $symbol, $amount, $cost){

    //$to = $row['Email'];
    //echo $row['Email'];
    $subject = "Coin Sale: ".$symbol;
    $body = "Dear Steven, <BR/>";
    $body .= "Congratulations you have sold the following Coin: "."<BR/>";
    $body .= "Coin: ".$symbol." Amount: ".$amount." Price: ".$cost."<BR/>";
    $body .= "Kind Regards\nCryptoBot.";
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $headers .= 'From: Alerts <Alerts@Investment-Tracker.net>' . "\r\n";
    $headers .= "To:".$to."\r\n";
    mail($to, $subject, wordwrap($body,70),$headers);

}

function bittrexbalanceLoc($apikey, $apisecret){
    $nonce=time();
    $uri='https://bittrex.com/api/v1.1/account/getbalance?apikey='.$apikey.'&currency=BTC&nonce='.$nonce;
    $sign=hash_hmac('sha512',$uri,$apisecret);
    $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
    $balance = $obj["result"]["Available"];
    return $balance;
}

function getLiveCoinPriceLoc($symbol){
    $limit = 500;
    $cnmkt = "https://api.coinmarketcap.com/v1/ticker/?limit=".$limit;
    $fgc = json_decode(file_get_contents($cnmkt), true);

  for($i=0;$i<$limit;$i++){
    //print_r($i);

    if ($fgc[$i]["symbol"] == $symbol){
      //print_r($fgc[$i]["symbol"]);
      $tmpCoinPrice = $fgc[$i]["price_btc"];

    }
  }
  logAction("$cnmkt",'CMC');
  return $tmpCoinPrice;
}

function getLiveCoinPriceUSDLoc($symbol){
    $limit = 100;
    $cnmkt = "https://api.coinmarketcap.com/v1/ticker/?limit=".$limit;
    $fgc = json_decode(file_get_contents($cnmkt), true);

  for($i=0;$i<$limit;$i++){
    //print_r($i);

    if ($fgc[$i]["symbol"] == $symbol){
      //print_r($fgc[$i]["symbol"]);
      $tmpCoinPrice = $fgc[$i]["price_usd"];

    }
  }
  logAction("$cnmkt",'CMC');
  return $tmpCoinPrice;
}

function tableHeader($th1,$th2,$th3,$th4,$th5,$th6,$th7,$th8,$th9,$th10,$th11,$th12){
   Echo "<Table><TH>$th1</TH><TH>$th2</TH><TH>$th3</TH><TH>$th4</TH><TH>$th5</TH><TH>$th6</TH><TH>$th7</TH><TH>$th8</TH><TH>$th9</TH><TH>$th10</TH><TH>$th11</TH><TH>$th12</TH><TR>";
}

function tableRow($td1,$td2,$td3,$td4,$td5,$td6,$td7,$td8,$td9,$td10,$td11,$td12){
    Echo "<td>$td1</td><td>$td2</td><td>$td3</td><td>$td4</td><td>$td5</td><td>$td6</td><td>$td7</td><td>$td8</td><td>$td9</td><td>$td10</td><td>$td11</td><td>$td12</td><tr>";
}

function tableEnd($sumUSDT, $sumUSD, $sumETH, $sumBTC){
  echo "<td class='totalRow'></td><td class='totalRow'></td><td class='totalRow'></td><td class='totalRow'></td><td class='totalRow'>$sumBTC</td><td class='totalRow'>$sumUSDT</td><td class='totalRow'>$sumETH</td><td class='totalRow'>$sumUSD</td>";
  echo "<td class='totalRow'></td><td class='totalRow'></td><td class='totalRow'></td><tr></Table>";
}

?>

<!--<div class="container">

	<div class="row">

	    <div class="col-xs-12 col-sm-8 col-md-8 col-sm-offset-2">-->


				<?php
        displayHeader(5);
        $totalProfitSumUSD  = null; $totalProfitSumUSDT = null; $totalProfitSumETH = null; $totalProfitSumBTC = null;
        $coins = getCoinsfromSQL($_SESSION['ID']);
        //$CoinPrice = getCoinPriceLoc();
        $totalProfitSum = 0;

        $date = date('d/m/Y h:i:s a', time());
        $percentGain = 2.0;
        $arrlength = count($coins);
        //$pricelength = count($CoinPrice);
        //$btcPrice = getLiveCoinPriceUSDLoc("BTC");
        //echo "<br><h2>Profit</h2>";
        //echo "<h3><a href='Profit.php'>All Profit</a> &nbsp > &nbsp <a href='Profit_BuyBack.php'>BuyBack Profit</a> &nbsp > &nbsp <a href='Profit_SpreadBet.php'>SpreadBet Profit</a> &nbsp > &nbsp <a href='ProfitPerDay.php'>Profit Per Day</a> &nbsp > &nbsp <a href='ProfitPerMonth.php'>Profit Per Month</a> &nbsp > &nbsp <a href='ProfitTotal.php'>Total Profit</a></h3>";
        displaySubHeader("Profit");
        Echo "<BR><h3><a href='Profit_SpreadBet.php'>Group Together</a></h3>";
        //echo "<HTML><Table><TH>Symbol</TH><TH>Original Purchase Price</TH><TH>Sale Price</TH><TH>Fee</TH><TH>Profit BTC</TH><TH>Original Purchase Price USD</TH><TH>Sale Price USD</TH><TH>Fee USD</TH><TH>Profit USD</TH><TH>Year Sold</TH><TH>Month Sold</TH><TH>Day Sold</TH><TR>";
        tableHeader('Symbol','SB_TransactionID','Original Purchase Price','Sale Price','Fee','Profit BTC','Profit USDT','Profit ETH','Profit USD','Year Sold','Month Sold','Day Sold');
        for($x = 0; $x < $arrlength; $x++) {

                    //$price = $coins[$x][9];

                    $purchasePrice = number_format((float)$coins[$x][0], 8, '.', '');
                    $sellYear = $coins[$x][1];
                    $sellMonth = $coins[$x][2];
                    $sellDay = $coins[$x][3];
                    $sellPrice = number_format((float)$coins[$x][4], 8, '.', '');
                    $fee = number_format((float)$coins[$x][5], 8, '.', '');
                    $profit = number_format((float)$coins[$x][6], 8, '.', '');
                    $sb_TransID = $coins[$x][13];
                    $symbol = $coins[$x][7];
                    //$tmpNumber = number_format((float)($price-$purchasePrice), 8, '.', '');
                    //$percentProfit = number_format(($tmpNumber/$purchasePrice)*100, 3, '.', '');


                    //$dateSold =  $coins[$x][7];
                    //$usdProfit = number_format((float)$profit*$btcPrice, 2, '.', '');
                    //$purchasePriceUSD = number_format((float)$purchasePrice, 2, '.', '');
                    //$sellPriceUSD = number_format((float)$sellPrice, 2, '.', '');
                    //$feeUSD = number_format((float)$fee*$btcPrice, 2, '.', '');
                    $profitBTC = $coins[$x][8]; $profitUSDT = $coins[$x][9]; $profitETH = $coins[$x][10];
                    $profitUSD = (($sellPrice-$purchasePrice-$fee)/$purchasePrice)*100;
                    //$profitUSD = $coins[$x][11];
                    $totalProfitSumUSD = $totalProfitSumUSD + $profitUSD;
                    $totalProfitSumUSDT = $totalProfitSumUSDT + $profitUSDT;
                    $totalProfitSumETH = $totalProfitSumETH + $profitETH;
                    $totalProfitSumBTC = $totalProfitSumBTC + $profitBTC;
                    $spreadBetTransID = $coins[$x][13];
                    //print_r("<tr><td>".$symbol."</td><td>".$purchasePrice."</td><td>".$sellPrice."</td><td>".$fee."</td><td>".$profit."</td>");
                    //print_r("<td>$".$purchasePriceUSD."</td><td>$".$sellPriceUSD."</td><td>$".$feeUSD."</td><td>$".$usdProfit."</td><td>$sellYear</td><td>$sellMonth</td><td>$sellDay</td></tr>");
                    tableRow($symbol,$spreadBetTransID,$purchasePrice,$sellPrice,$fee,$profitBTC, $profitUSDT, $profitETH, $profitUSD,$sellYear,$sellMonth,$sellDay);


        }
        //$profitTtl = getProfitTotalLoc($_SESSION['ID']);
        //$TotalBTCProfit = number_format((float)$profitTtl[0][0], 8, '.', '');
        //$usdPrice = number_format((float)($totalProfitSum*$btcPrice), 2, '.', '');
        //echo "<td class='totalRow'></td><td class='totalRow'></td><td class='totalRow'></td><td class='totalRow'></td><td class='totalRow'>".$totalProfitSum."</td><td class='totalRow'></td><td class='totalRow'></td><td class='totalRow'></td>";
        //echo "<td class='totalRow'>$usdPrice</td><td class='totalRow'></td><td class='totalRow'></td><tr>";
        tableEnd($totalProfitSumUSDT,$totalProfitSumUSD,$totalProfitSumETH,$totalProfitSumBTC);
        //$totalBTC = ($profitTtl[0][0]*getLiveCoinPriceLoc("BTC")));

        //echo "<td class='totalRow'></td><td class='totalRow'></td><td class='totalRow'>BTC Total</td><td class='totalRow'>".$totalBTC."</td><td class='totalRow'>$".round($usdPrice,2)."</td><td class='totalRow'></td><td class='totalRow'></td><tr>";
        //echo "</Table>"
				displaySideColumn();
//include header template
require('layout/footer.php');
?>
