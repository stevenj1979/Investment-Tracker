<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<?php require('includes/config.php');
include_once '../includes/newConfig.php';?>
<style>
<?php include 'style/style.css'; ?>

	    #Home:hover #homeContent{
		      display: block;
		        position: absolute;
	    }
	    #AboutUs:hover #AboutUsContent{
		      display: block;
		        position: absolute;
	    }
</style> <?php

//if not logged in redirect to login page
if(!$user->is_logged_in()){ header('Location: login.php'); exit(); }

//define page title
$title = 'CryptoBot';

//include header template
require('layout/header.php');
include_once ('/home/stevenj1979/SQLData.php');

function getCoinsfromSQLLoc($userID){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT `UserID`,`OrderNo`,`Symbol`,`Amount`,`Cost`,`TradeDate`,`SellPrice`, `Profit`, `ETHProfit`, `DateSold`, `ID` FROM `Transaction` where `Status` = 'Sold' and `UserID` = $userID order by `DateSold` desc limit 50";
    $result = $conn->query($sql);
    //$result = mysqli_query($link4, $query);
	//mysqli_fetch_assoc($result);
    while ($row = mysqli_fetch_assoc($result)){
        $tempAry[] = Array($row['UserID'],$row['OrderNo'],$row['Symbol'],$row['Amount'],$row['Cost'],$row['TradeDate'],$row['SellPrice'],$row['Profit'],$row['ETHProfit'],$row['DateSold'],$row['ID']);
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
    $limit = 500;
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

function getUserIDs($userID){
  // Create connection
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "SELECT `APIKey`, `APISecret` FROM `UserConfigView` WHERE `ID` = $userID";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['APIKey'],$row['APISecret']);
  }
  $conn->close();
  return $tempAry;
}

function getTotalProfit($userID){
  // Create connection
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "SELECT Sum(`SellPrice`) as SalePrice, sum(`PurchasePrice`)as PurchasePrice, Sum(`Profit`) as realisedprofit, count(`Day`) as NoOfTransactions
      FROM `CoinProfitView`
      WHERE `UserID` = $userID and `Status` = 'Sold' and `Type` = 'Sell'
      order by `CompletionDate` desc";
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['SalePrice'],$row['PurchasePrice'],$row['realisedprofit'],$row['NoOfTransactions']);
  }
  $conn->close();
  return $tempAry;
}

displayHeader(5);



        $date = date('d/m/Y h:i:s a', time());
        $userData = getUserIDs($_SESSION['ID']);
        $apikey = $userData[0][0]; $apisecret = $userData[0][1];
        $btcPrice = getLiveCoinPriceUSDLoc("BTC");
        $userProfit = getTotalProfit($_SESSION['ID']);
        $salePrice = number_format((float)$userProfit[0][0], 10, '.', ''); $purchasePrice = number_format((float)$userProfit[0][1], 10, '.', ''); $profit = number_format((float)$userProfit[0][2], 10, '.', '');
        $purchaseCostUSD = number_format((float)$purchasePrice*$btcPrice, 2, '.', ''); $salePriceUSD = number_format((float)$salePrice*$btcPrice, 2, '.', ''); $profitUSD = number_format((float)$profit*$btcPrice, 2, '.', '');
        $count = $userProfit[0][3];
        $bittrexBal = number_format((float)bittrexbalanceLoc($apikey,$apisecret), 10, '.', '');
        $bittrexBalUSD = number_format((float)$bittrexBal*$btcPrice, 2, '.', '');
        $totalBalance =  $profit + $bittrexBal;
        $totalBalanceUSD = number_format((float)$totalBalance*$btcPrice, 2, '.', '');
        //echo "<br><h2>Profit</h2>";
        echo "<h3><a href='Profit.php'>All Profit</a> &nbsp > &nbsp <a href='ProfitPerDay.php'>Profit Per Day</a> &nbsp > &nbsp <a href='ProfitPerMonth.php'>Profit Per Month</a> &nbsp > &nbsp <a href='ProfitTotal.php'>Total Profit</a></h3>";
        echo "<HTML><Table><TH>Purchase Price BTC</TH><TH>Sale Price BTC</TH><TH>Profit BTC</TH><TH>Bittrex Balance</TH><TH>Total Balance</TH><TH>Purchase Price USD</TH><TH>Sale Price USD</TH><TH>Profit USD</TH><TH>Bittrex Balance</TH><TH>Total Balance USD</TH><TH>No of Transactions</TH><TR>";
        print_r("<tr><td>".$purchasePrice."</td><td>".$salePrice."</td><td>".$profit."</td><td>$bittrexBal</td><td>$totalBalance</td><td> $".$purchaseCostUSD."</td><td> $".$salePriceUSD."</td><td> $".$profitUSD."</td>");
        print_r("<td>$".$bittrexBalUSD."</td><td>$".$totalBalanceUSD."</td><td>".$count."</td></tr>");
        echo "</Table>";
        //echo "<BR> $apikey $apisecret $bittrexBal";
				displaySideColumn();
//include header template
require('layout/footer.php');
?>
