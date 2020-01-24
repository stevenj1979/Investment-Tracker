<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
</head>
<?php require('includes/config.php');?>
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
include '../../../NewSQLData.php';

function getCoinsfromSQL(){
    $conn = getSQL(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "SELECT `UserID`,`OrderNo`,`Symbol`,`Amount`,`Cost`,`TradeDate` FROM `Transaction` where `Status` = 'Open'";
    $result = $conn->query($sql);
    while ($row = mysqli_fetch_assoc($result)){
        $tempAry[] = Array($row['UserID'],$row['OrderNo'],$row['Symbol'],$row['Amount'],$row['Cost'],$row['TradeDate']);
    }
    $conn->close();
    return $tempAry;
}

function getTrackingCoins(){
  $conn = getSQL(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
    $sql = "SELECT `ID`,`Symbol`,`LiveBuyOrders`,`LastBuyOrders`,`BuyOrdersPctChange`,`LiveMarketCap`,`LastMarketCap`,`MarketCapPctChange`,`Live1HrChange`,`Last1HrChange`,`Hr1ChangePctChange`,`Live24HrChange`,`Last24HrChange`,`Hr24ChangePctChange`,`Live7DChange`,
     `Last7DChange`,`D7ChangePctChange`,`LiveCoinPrice`,`LastCoinPrice`,`CoinPricePctChange`,`LiveSellOrders`,`LastSellOrders`, `SellOrdersPctChange`,`LiveVolume`,`LastVolume`,`VolumePctChange`,`BaseCurrency`
     ,`Price4Trend`, `Price3Trend`, `LastPriceTrend`, `LivePriceTrend`
     FROM `CoinStatsView` order by `CoinPricePctChange` asc,`Live1HrChange` asc";
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['Symbol'],$row['LiveBuyOrders'],$row['LastBuyOrders'],$row['BuyOrdersPctChange'],$row['LiveMarketCap'],$row['LastMarketCap'],$row['MarketCapPctChange'],$row['Live1HrChange']
      ,$row['Last1HrChange'],$row['Hr1ChangePctChange'],$row['Live24HrChange'],$row['Last24HrChange'],$row['Hr24ChangePctChange'],$row['Live7DChange'],$row['Last7DChange'],$row['D7ChangePctChange'],$row['LiveCoinPrice']
      ,$row['LastCoinPrice'],$row['CoinPricePctChange'],$row['LiveSellOrders'],$row['LastSellOrders'],$row['SellOrdersPctChange'],$row['LiveVolume'],$row['LastVolume'],$row['VolumePctChange'],$row['BaseCurrency']
    ,$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend']);
  }
  $conn->close();
  return $tempAry;
}

function getNumberColour($ColourText, $target){
  if ($ColourText >= 0){
    $colour = "#D4EFDF";
  }elseif ($ColourText == 0) {
    $colour = "#FCF3CF";
  }else{
    $colour = "#F1948A";
  }
  return $colour;
}

function upAndDownColour($direction){
  if ($direction == 'Up'){
      $tempDir = '#D4EFDF';
  }else{
    $tempDir = '#F1948A';
  }
  return $tempDir;
}

function getConfig($userID){
  $conn = getSQL(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "SELECT `MarketCapBuyPct`,`VolumeBuyPct`,`BuyOrdersPct`,`MinPctGain`  FROM `Config` WHERE `UserID` =  $userID";
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['MarketCapBuyPct'],$row['VolumeBuyPct'],$row['BuyOrdersPct'],$row['MinPctGain']);
  }
  $conn->close();
  return $tempAry;
}

function sendEmail($to, $symbol, $amount, $cost){
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

function bittrexbalance($apikey, $apisecret){
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

function getLiveCoinPrice($symbol){
    $limit = 500;
    $cnmkt = "https://api.coinmarketcap.com/v1/ticker/?limit=".$limit;
    $fgc = json_decode(file_get_contents($cnmkt), true);
  for($i=0;$i<$limit;$i++){
    if ($fgc[$i]["symbol"] == $symbol){
      $tmpCoinPrice = $fgc[$i]["price_btc"];
    }
  }
  return $tmpCoinPrice;
}

function bittrexCoinPrice($apikey, $apisecret, $baseCoin, $coin){
      $nonce=time();
      $uri='https://bittrex.com/api/v1.1/public/getticker?market='.$baseCoin.'-'.$coin;
      $sign=hash_hmac('sha512',$uri,$apisecret);
      $ch = curl_init($uri);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $execResult = curl_exec($ch);
      $obj = json_decode($execResult, true);
      $balance = $obj["result"]["Last"];
      return $balance;
  }

function getUserIDs($userID){
  $conn = getSQL(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = " SELECT `ID`, `Username`, `email`, `api_key`, `api_secret` FROM `User` where `ID` = $userID";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['Username'],$row['email'],$row['api_key'],$row['api_secret']);
  }
  $conn->close();
  return $tempAry;
}



?>

<!--<div class="container">-->

	<!--<div class="row">-->

	   <!-- <div class="col-xs-12 col-sm-8 col-md-8 col-sm-offset-2">-->
     <div class="header">
       <table><TH><table class="CompanyName"><td rowspan="2" class="CompanyName"><img src='Images/CBLogoSmall.png' width="40"></td><td class="CompanyName"><div class="Crypto">Crypto</Div><td><tr class="CompanyName">
           <td class="CompanyName"><Div class="Bot">Bot</Div></td></table></TH><TH>: Logged in as:</th><th> <i class="glyphicon glyphicon-user"></i>  <?php echo $_SESSION['username'] ?></th></Table><br>

        </div>
        <div class="topnav">
          <a href="Dashboard.php">Dashboard</a>
          <a href="Transactions.php">Transactions</a>
          <a href="Stats.php">Stats</a>
          <a href="BuyCoins.php" class="active">Buy Coins</a>
          <a href="SellCoins.php">Sell Coins</a>
          <a href="Profit.php">Profit</a>
          <a href="bittrexOrders.php">Bittrex Orders</a>
          <a href="Settings.php">Settings</a><?php
          if ($_SESSION['AccountType']==1){echo "<a href='AdminSettings.php'>Admin Settings</a>";}
          ?>
        </div>
<div class="row">
       <div class="column side">
          &nbsp
      </div>
      <div class="column middle">

  				<?php
				$tracking = getTrackingCoins();
				$newArrLength = count($tracking);
        //$userConfig = getConfig($_SESSION['ID']);
        //$user = getUserIDs($_SESSION['ID']);
				//print_r("<HTML><Table><th>Coin</th><th>BuyPattern</th><th>MarketCapHigherThan5Pct</th><th>VolumeHigherThan5Pct</th><th>BuyOrdersHigherThan5Pct</th><th>PctChange</th><tr>");
				print_r("<h2>BuyCoins</h2><Table><th>&nbspCoin</th><TH>&nbspBase Currency</th><TH>&nbspPrice</th><TH>&nbspMarket Cap %</th><TH>&nbspVolume by %</th><TH>&nbspBuy Orders %</th><TH>&nbspPrice Diff 1</th><TH>&nbspPrice Change</th><TH>&nbsp% Change 1Hr</th><TH>&nbsp% Change 24 Hrs</th>
        <TH>&nbsp% Change 7 Days</th><TH>&nbspBuy Pattern</th><TH>&nbspManual Buy</th><tr>");
				for($x = 0; $x < $newArrLength; $x++) {
          //Variables
          $coin = $tracking[$x][1]; $buyOrders = round($tracking[$x][4],2); $MarketCap = round($tracking[$x][7],2);
          $Live1HrChange = round($tracking[$x][10],2); $Live24HrChange = round($tracking[$x][13],2); $Live7DChange = $tracking[$x][14];
          $bitPrice = $tracking[$x][17]; $LastCoinPrice = $tracking[$x][18];$coinID = $tracking[$x][0];
          $volume = round($tracking[$x][25],2); $baseCurrency = $tracking[$x][26];
          $price4Trend = $tracking[$x][27];$price3Trend = $tracking[$x][28]; $lastPriceTrend = $tracking[$x][29]; $LivePriceTrend = $tracking[$x][30];
          $priceChange = number_format((float)$bitPrice-$LastCoinPrice, 8, '.', '');
          $priceDiff1 = number_format((float)$tracking[$x][19], 2, '.', '');
          //Table
          echo "<td><a href='CoinHistory.php?coin=$coin'>$coin</a></td>";
          echo "<td>".$baseCurrency."</td>";
          echo "<td>".$bitPrice."</td>";
          echo "<td>$MarketCap</td>";
          echo "<td>$volume</td>";
          echo "<td>$buyOrders</td>";
          echo "<td>% $priceDiff1</td>";
          echo "<td>".$priceChange." ".$baseCurrency."</td>";
          echo "<td>".$Live1HrChange."</td>";
          echo "<td>".$Live24HrChange."</td>";
          echo "<td>".$Live7DChange."</td>";
          echo "<td>".$price4Trend." ".$price3Trend." ".$lastPriceTrend." ".$LivePriceTrend."</td>";
          echo "<td><a href='ManualBuy.php?coin=$coin&baseCurrency=$baseCurrency&coinID=$coinID&coinPrice=$bitPrice'><i class='fas fa-shopping-cart' style='font-size:24px;color:#D4EFDF'></i></a></td>";
          echo "<tr>";
				}//end for
				print_r("</table>");


				?>
      </div>
      <div class="column side">
          &nbsp
      </div>
    </div>

      <div class="footer">
          <hr>
          <!-- <input type="button" value="Logout">
  				<a href='logout.php'>Logout</a>-->

          <input type="button" onclick="location='logout.php'" value="Logout"/>

      </div>



    <!--  </div>
  	</div>


  </div>-->


<?php
//include header template
require('layout/footer.php');
$date = date('Y/m/d H:i:s', time());
echo " Last Updated :".$date;
?>
