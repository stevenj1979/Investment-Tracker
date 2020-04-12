<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
</head>
<?php require('includes/config.php');?>
<style>
<?php include 'style/style.css'; ?>
</style> <?php
ini_set("max_execution_time", 150);
//if not logged in redirect to login page
if(!$user->is_logged_in()){ header('Location: login.php'); exit(); }

//define page title
$title = 'CryptoBot';
$current_url = $_SERVER[ 'REQUEST_URI' ];
header( "Refresh: 120; URL=$current_url" );
//include header template
require('layout/header.php');
include_once ('/home/stevenj1979/SQLData.php');

function getCoinsfromSQL(){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT `UserID`,`OrderNo`,`Symbol`,`Amount`,`Cost`,`TradeDate` FROM `Transaction` where `Status` = 'Open' order by `TradeDate` desc ";
    $result = $conn->query($sql);
    //$result = mysqli_query($link4, $query);
	//mysqli_fetch_assoc($result);
    while ($row = mysqli_fetch_assoc($result)){
        $tempAry[] = Array($row['UserID'],$row['OrderNo'],$row['Symbol'],$row['Amount'],$row['Cost'],$row['TradeDate']);
    }
    $conn->close();
    return $tempAry;
}

function getTrackingSellCoins($userID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "SELECT `ID`,`Symbol`,`LiveBuyOrders`,`LastBuyOrders`,`BuyOrdersPctChange`,`LiveMarketCap`,`LastMarketCap`,`MarketCapPctChange`,`Last1HrChange`,`Hr1ChangePctChange`,`Live24HrChange`,`Last24HrChange`,`Hr24ChangePctChange`,`Live7DChange`,`Last7DChange`,`D7ChangePctChange`,`LiveCoinPrice`,
`LastCoinPrice`,`CoinPricePctChange`,`LiveSellOrders`,`LastSellOrders`,`SellOrdersPctChange`,`LiveVolume`,`LastVolume`,`VolumePctChange`,`BaseCurrency`,`Amount`,`OrderNo`, `CoinPrice`,`Profit`,`TransactionID`, `BittrexID`,`UserID`,`ProfitPct`,`Live1HrChange` FROM `WebOwnedCoinsStats` WHERE `UserID` = $userID ORDER BY `ProfitPct` Desc";
  $result = $conn->query($sql);
    //print_r($sql);
  //$result = mysqli_query($link4, $query);
//mysqli_fetch_assoc($result);`PctChange1Hr`, `PctChange24Hr`, `PctChange7D`
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['Symbol'],$row['LiveBuyOrders'],$row['LastBuyOrders'],$row['BuyOrdersPctChange'],$row['LiveMarketCap'],$row['LastMarketCap'],$row['MarketCapPctChange'],$row['Last1HrChange'],$row['Hr1ChangePctChange'],$row['Live24HrChange'],
      $row['Last24HrChange'],$row['Hr24ChangePctChange'],$row['Live7DChange'],$row['Last7DChange'],$row['D7ChangePctChange'],$row['LiveCoinPrice'],$row['LastCoinPrice'],$row['CoinPricePctChange'],$row['LiveSellOrders'],$row['LastSellOrders'],$row['SellOrdersPctChange'],
      $row['LiveVolume'],$row['LastVolume'],$row['VolumePctChange'],$row['BaseCurrency'],$row['Amount'],$row['OrderNo'],$row['CoinPrice'],$row['Profit'],$row['TransactionID'],$row['BittrexID'],$row['UserID'],$row['ProfitPct'],
      $row['Live1HrChange']);
  }
  $conn->close();
  return $tempAry;
}

function getColour($ColourText, $target){
  if ($ColourText >= 0){
    $colour = "Green" ;
  }else {
    $colour = "Red";
  }
  return $colour;
}
function getSellColour($ColourText, $target){
  if ($ColourText > 0){
    $colour = "#D4EFDF" ;
  }elseif ($ColourText == 0 ){
    $colour = "#FCF3CF";
  }else {
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
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `CoinSalePct`,`MarketCapSellPct`,`VolumeSellPct`,`SellOrdersPct`,`MinPctGain` FROM `Config` WHERE `UserID` =  $userID";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['CoinSalePct'],$row['MarketCapSellPct'],$row['VolumeSellPct'],$row['SellOrdersPct'],$row['MinPctGain']);
  }
  $conn->close();
  return $tempAry;
}

function sendEmail($to, $symbol, $amount, $cost){

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
    //print_r($i);

    if ($fgc[$i]["symbol"] == $symbol){
      //print_r($fgc[$i]["symbol"]);
      $tmpCoinPrice = $fgc[$i]["price_btc"];

    }
  }
  return $tmpCoinPrice;
}

function profitScore($profit){
  if($profit > 60){
    $tempNumber = 10;
  }elseif ($profit < 59.999 && $profit > 40){
    $tempNumber = 5;
  }elseif ($profit < 39.999 && $profit > 20){
    $tempNumber = 2;
  }elseif ($profit < 19.999 && $profit > 0){
    $tempNumber = 0;
  }
  return $tempNumber;
}


date_default_timezone_set('Asia/Dubai');
$date = date('Y/m/d H:i:s', time());


?>

<!--<div class="container">

	<div class="row">

	    <div class="col-xs-12 col-sm-8 col-md-8 col-sm-offset-2">-->

      <div class="header">
        <table><TH><table class="CompanyName"><td rowspan="2" class="CompanyName"><img src='Images/CBLogoSmall.png' width="40"></td><td class="CompanyName"><div class="Crypto">Crypto</Div><td><tr class="CompanyName">
            <td class="CompanyName"><Div class="Bot">Bot</Div></td></table></TH><TH>: Logged in as:</th><th> <i class="glyphicon glyphicon-user"></i>  <?php echo $_SESSION['username'] ?></th></Table><br>

         </div>
         <div class="topnav">
           <a href="Dashboard.php">Dashboard</a>
           <a href="Transactions.php">Transactions</a>
           <a href="Stats.php">Stats</a>
           <a href="BuyCoins.php">Buy Coins</a>
           <a href="SellCoins.php" class="active">Sell Coins</a>
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
        $trackingSell = getTrackingSellCoins($_SESSION['ID']);
        $arrLengthSell = count($trackingSell);
        //$userConfig = getConfig($_SESSION['ID']);
        print_r("<h2>Sell Some Coins Now!</h2><Table><th>&nbspCoin</th><th>&nbspPrice</th>&nbsp<th>&nbspMarket Cap by %&nbsp</th>&nbsp<th>&nbspVolume by %</th>&nbsp<th>&nbspSell Orders by %</th>&nbsp<th>Price Trend 1</th>&nbsp<th>&nbsp% Change 1Hr</th>&nbsp<th>&nbsp% Change 24Hr</th>&nbsp<th>&nbsp% Change 7 Days</th>&nbsp<th>&nbspAmount</th>&nbsp<th>&nbspCost</th>&nbsp<th>&nbspProfit%</th>&nbsp<th>&nbspProfit BTC</th>&nbsp<th>&nbspManual Sell</th>&nbsp<tr>");
        for($x = 0; $x < $arrLengthSell; $x++) {
            //Variables
            $coin = $trackingSell[$x][1]; $mrktCap = round($trackingSell[$x][7],2); $pctChange1Hr = round($trackingSell[$x][9],2);$pctChange24Hr = round($trackingSell[$x][12],2);
            $pctChange7D = $trackingSell[$x][13]; $livePrice = $trackingSell[$x][16]; $LastCoinPrice = $trackingSell[$x][17]; $sellOrders = round($trackingSell[$x][21],2);
            $volume = round($trackingSell[$x][24],4); $baseCurrency = $trackingSell[$x][25]; $amount = round($trackingSell[$x][26],10);  $orderNo = $trackingSell[$x][27]; $transactionID = $trackingSell[$x][30];
            $profitPct = round($trackingSell[$x][33],2);$cost = $trackingSell[$x][28]; $realAmount = $trackingSell[$x][26];
            $priceDiff1 = number_format((float)$livePrice-$LastCoinPrice, 10, '.', ''); $buyAmount = $amount * $cost;
            $sellAmount = $livePrice * $amount; $fee = ($sellAmount/100)*0.25; $profitBtc = $sellAmount - $buyAmount - $fee;
            echo "<td>$coin</td>";
            echo "<td>$livePrice</td>";
            echo "<td>$mrktCap</td>";
            echo "<td>$volume</td>";
            echo "<td>$sellOrders</td>";
            $diffColour = 'Red';
            echo "<td bgcolor='".upAndDownColour($priceDiff1)."'>$priceDiff1</td>";
            echo "<td>".$pctChange1Hr."</td><td>".$pctChange24Hr."</td><td>".$pctChange7D."</td>";
            echo "<td>$amount</td>";
            $cost = number_format((float)$trackingSell[$x][28], 10, '.', '');
            echo "<td>$cost</td>";
            if ($profitPct > 0){
              $profitColour = "Green";
            }else{
              $profitColour = "Red";
            }
            echo "<td bgcolor='".getSellColour($profitPct,0)."'>$profitPct</td>";
            echo "<td>".number_format((float)$profitBtc, 8, '.', '')."</td>";
            echo "<td><a href='ManualSell.php?coin=$coin&amount=".$realAmount."&cost=$cost&baseCurrency=$baseCurrency&orderNo=$orderNo&transactionID=$transactionID&salePrice=$livePrice'><i class='fas fa-shopping-cart' style='font-size:24px;color:#F1948A'></i></a></td>";
            echo "<tr>";
        }
        print_r("</table>");
				?>
      </div>
      <div class="column side">
          <img src='Images/CBLogoSmall.png' width="150">
      </div>
    </div>

      <div class="footer">
          <hr>
          <!-- <input type="button" value="Logout">
          <a href='logout.php'>Logout</a>-->

          <input type="button" onclick="location='logout.php'" value="Logout"/>

      </div>
<?php
//include header template
require('layout/footer.php');
$date = date('Y/m/d H:i:s', time());
echo " Last Updated :".$date;
?>
