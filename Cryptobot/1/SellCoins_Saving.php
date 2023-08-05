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
ini_set("max_execution_time", 150);
//if not logged in redirect to login page
if(!$user->is_logged_in()){ header('Location: login.php'); exit(); }

//define page title
$title = 'CryptoBot';
$current_url = $_SERVER[ 'REQUEST_URI' ];
header( "Refresh: 120; URL=$current_url" );
//include header template
require('layout/header.php');
include_once ('../../../../SQLData.php');
$locationStr = "Location: /Investment-Tracker/Cryptobot/1/m/SellCoins.php";
setStyle($_SESSION['isMobile']);


if(isset($_GET['override'])){
  if ($_SESSION['MobOverride'] == False){$_SESSION['MobOverride'] = True;$_SESSION['roundVar'] = 8;}

}

if(isset($_GET['noOverride'])){
  if ($_SESSION['MobOverride'] == True){$_SESSION['MobOverride'] = False;$_SESSION['roundVar'] = 2;}

}

if ($_SESSION['isMobile'] && $_SESSION['MobOverride'] == False){
  $_SESSION['roundVar'] = 2;
  //header('Location: SellCoins_Saving_Mob.php');
}

if (!isset($_SESSION['savingProfitSelect'])){
    $_SESSION['savingProfitSelect'] = "none";
}elseif(isset($_POST['savingProfitSelect'])){
  //Print_r("I'm HERE!!!".$_POST['submit']);
  //changeSelection();
  //$_SESSION['savingProfitSelect'] = "having ProfitPct > 40";
  changeAmountSelection($_POST['savingProfitSelect'],$_SESSION['savingProfitSelect']);
}//else{ $_SESSION['savingProfitSelect'] = "";}

if (!isset($_SESSION['savingTotalSelect'])){
  $_SESSION['savingTotalSelect'] = 'none';
}elseif(isset($_POST['savingTotalSelect'])){
  //$_SESSION['savingTotalSelect'] = "TotalUSD > 20";
  changeTotalSelection($_POST['savingTotalSelect'],$_SESSION['savingTotalSelect']);
}//else{$_SESSION['savingTotalSelect'] = "";}

function changeAmountSelection($postAmount, $sessionAmount){
  if (isset($_POST['savingProfitSelect'])){
    if ($postAmount <> $sessionAmount){
      if ($postAmount <> "" ){
        $_SESSION['savingProfitSelect'] = $postAmount;
      }
    }
  }
}

function changeTotalSelection($postTotal, $sessionTotal){
  if (isset($_POST['savingTotalSelect'])){
    if ($postTotal <> $sessionAmount){
      if ($postTotal <> "" ){
        $_SESSION['savingTotalSelect'] = $postTotal;
      }
    }
  }
}

function getCoinsfromSQLLoc(){
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

function getSavingSellCoins($userID){
  $tempAry = [];
  if ($userID <> 0){
    $whereclause3 = "Where `UserID` = $userID and `Status` = 'Saving'";
  }
  if (isset($_SESSION['savingProfitSelect'])){
    if ($_SESSION['savingProfitSelect'] <> "none" and $_SESSION['savingProfitSelect'] <> ""){
      if ($_SESSION['savingProfitSelect'] = "By Profit"){
        $whereclause = "having ProfitPct > 40";
      }

    }
  }
  if (isset($_SESSION['savingTotalSelect'])){
    if ($_SESSION['savingTotalSelect'] <> "" and $_SESSION['savingTotalSelect'] <> ""){
      if ($_SESSION['savingTotalSelect'] == "By Total"){
        if (!isset($whereclause)){
          $whereclause2 = "having TotalUSD > 20";
        }else{
          $whereclause2 = "and TotalUSD > 20";
        }
      }
    }
  }
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "SELECT `IDTr`,`Type`,`CoinID`,`UserID`,`CoinPrice`,`Amount`,`Status`,`OrderDate`,`CompletionDate`,`BittrexID`,`OrderNo`,`Symbol`,`LastBuyOrders`, `LiveBuyOrders`
          , `BuyOrdersPctChange`,`LastMarketCap`,`LiveMarketCap`, `MarketCapPctChange`,`LastCoinPrice`,`LiveCoinPrice`, `CoinPricePctChange`,`LastSellOrders`,`LiveSellOrders`,
          `SellOrdersPctChange`,`LastVolume`,`LiveVolume`,`VolumePctChange`,`Last1HrChange`,`Live1HrChange`,`Hr1ChangePctChange`,`Last24HrChange`,`Live24HrChange`, `Hr24ChangePctChange`,`Last7DChange`,`Live7DChange`
          ,`D7ChangePctChange`,`BaseCurrency`, `Price4Trend`, `Price3Trend`, `LastPriceTrend`,  `LivePriceTrend`,`FixSellRule`,`SellRule`,`BuyRule`,`ToMerge`,`LowPricePurchaseEnabled`,'PurchaseLimit'
          ,`PctToPurchase`,`BTCBuyAmount`,`NoOfPurchases`,`Name`,`Image`,10 as `MaxCoinMerges`,getBTCPrice(84) as BTCPrice, getBTCPrice(85) as ETHPrice, `ProfitPct`
          ,if(`BaseCurrency` = 'BTC',(`LivePrice`)*getBTCPrice(84),if(`BaseCurrency` = 'ETH',(`LivePrice`)*getBTCPrice(85) ,(`LivePrice`))) as TotalUSD
          FROM `View5_SellCoins`  $whereclause3 $whereclause $whereclause2 Order by `ProfitPct` desc";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  //echo "$sql";
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['IDTr'],$row['Type'],$row['CoinID'],$row['UserID'],$row['CoinPrice'],$row['Amount'],$row['Status'],$row['OrderDate'],$row['CompletionDate'],$row['BittrexID'],$row['OrderNo'],
    $row['Symbol'],$row['LastBuyOrders'],$row['LiveBuyOrders'],$row['BuyOrdersPctChange'],$row['LastMarketCap'],$row['LiveMarketCap'],$row['MarketCapPctChange'],$row['LastCoinPrice'],$row['LiveCoinPrice'], //19
    $row['CoinPricePctChange'],$row['LastSellOrders'],$row['LiveSellOrders'],$row['SellOrdersPctChange'],$row['LastVolume'],$row['LiveVolume'],$row['VolumePctChange'],$row['Last1HrChange'],$row['Live1HrChange'],$row['Hr1ChangePctChange'],$row['Last24HrChange'],$row['Live24HrChange'] //31
    ,$row['Hr24ChangePctChange'],$row['Last7DChange'],$row['Live7DChange'],$row['D7ChangePctChange'],$row['BaseCurrency'],$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['FixSellRule'],$row['SellRule'],$row['BuyRule'] //43
    ,$row['ToMerge'],$row['LowPricePurchaseEnabled'],$row['PurchaseLimit'],$row['PctToPurchase'],$row['BTCBuyAmount'],$row['NoOfPurchases'],$row['Name'],$row['Image'],$row['MaxCoinMerges'],$row['BTCPrice'],$row['ETHPrice'],$row['ProfitPct']
    ,$row['TotalUSD']);
  }
  $conn->close();
  return $tempAry;
}

function getColour($ColourText){
  if ($ColourText >= 0){
    $colour = "Green" ;
  }else {
    $colour = "Red";
  }
  return $colour;
}
function getSellColour($ColourText){
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

function displayOptions($option, $selected){
  if ($option == $selected){
    echo "<option  selected='selected' value='$option'>$option</option>";

  }else{
    echo "<option  value='$option'>$option</option>";
  }
}


date_default_timezone_set('Asia/Dubai');
$date = date('Y/m/d H:i:s', time());


?>

<!--<div class="container">

	<div class="row">

	    <div class="col-xs-12 col-sm-8 col-md-8 col-sm-offset-2">-->


				<?php
        if ($_SESSION['isMobile']){ $num = 2; $fontSize = "font-size:60px"; }else{$num = 8;$fontSize = "font-size:32px"; }
        displayHeader(4);
        $trackingSell = getSavingSellCoins($_SESSION['ID']);
        $arrLengthSell = newCount($trackingSell);
        $roundVar = $_SESSION['roundVar'];
        $savingTotal = getSavingTotal($_SESSION['ID']);
        //$userConfig = getConfig($_SESSION['ID']);
        print_r("<h2>Sell Some Coins Now!</h2>");
        //echo "<h3><a href='SellCoins.php'>Sell Coins</a> &nbsp > &nbsp <a href='SellCoins_Tracking.php'>Tracking</a> &nbsp > &nbsp <a href='SellCoins_Saving.php'>Saving</a> &nbsp > &nbsp <a href='SellCoins_Spread.php'>Spread Bet</a> &nbsp > &nbsp <a href='SellCoins_SpreadCoin.php'>Spread Bet Coin</a>
        // &nbsp > &nbsp <a href='SellCoins_SwapCoins.php'>Swap Coins</a></h3>";
        displaySubHeader("SellCoin");
        Echo "<BR><H3>TotalSavings: ".round($savingTotal[0][1],2)." Profit: ".round($savingTotal[0][2],2)."</H3><BR>";
        ?>
        <form action='SellCoins_Saving.php?id=1' method='post'>
        <select name='savingProfitSelect' id='savingOrderSelect' class='enableTextBox'>
          <?php displayOptions ("none",$_SESSION['savingProfitSelect']);
          displayOptions ("By Profit",$_SESSION['savingProfitSelect']);
          ?></SELECT>
          <option  value='By Profit'>By Profit</option></SELECT>
          <select name='savingTotalSelect' id='savingOrderSelect' class='enableTextBox'>
            <?php displayOptions ("none",$_SESSION['savingTotalSelect']);
            displayOptions ("By Total",$_SESSION['savingTotalSelect']);
            ?>
          <input type='submit' name='submit' value='Update' class='settingsformsubmit' tabindex='36'>
        </form>

        <?php
        for($x = 0; $x < $arrLengthSell; $x++) {
            //Variables
            //$roundNum = 2;
            //if($_SESSION['isMobile'] == False){$roundNum = 8;}
            $coin = $trackingSell[$x][11];  $livePrice = $trackingSell[$x][19]; $LastCoinPrice = $trackingSell[$x][18]; $baseCurrency = $trackingSell[$x][36];
            $amount = $trackingSell[$x][5];  $orderNo = $trackingSell[$x][10]; $transactionID = $trackingSell[$x][0];
             $purchaseCost = $trackingSell[$x][4]; $realAmount = $trackingSell[$x][26];
            $mrktCap = $trackingSell[$x][17];  $volume = $trackingSell[$x][26]; $sellOrders = $trackingSell[$x][23];
            $pctChange1Hr = $trackingSell[$x][29]; $pctChange24Hr = $trackingSell[$x][32]; $pctChange7D = $trackingSell[$x][35];
            $sellRule = $trackingSell[$x][41]; $coinID = $trackingSell[$x][2];
            $profitPct = $trackingSell[$x][55];
            $priceDiff1 = $livePrice - $LastCoinPrice;
            $fee = (($livePrice* $amount)/100)*0.28;
            $liveTotalCost = ($livePrice * $amount);
            $originalPurchaseCost = ($purchaseCost * $amount);
            $profit = ($liveTotalCost - $originalPurchaseCost - $fee);
            $profitBtc = $profit/($originalPurchaseCost)*100;
            $userID = $_SESSION['ID'];
            $name = $trackingSell[$x][50]; $image = $trackingSell[$x][51];
            $btcPrice = $trackingSell[$x][53]; $ethPrice = $trackingSell[$x][54];
            if ($baseCurrency == 'BTC'){ $baseMultiplier = $btcPrice; $baseNum = 8; } elseif ($baseCurrency == 'ETH'){ $baseMultiplier = $ethPrice; $baseNum = 8;}
            else{ $baseMultiplier =1; $baseNum = 2;}
            echo "<table><td rowspan='3'><a href='Stats.php?coin=$coin'><img src='$image' width=60 height=60></a></td>";
            echo "<td><p id='largeText' >$name</p></td>";
            echo "<td rowspan=><p id='largeText' >".number_format($livePrice,$baseNum)."</p></td>";
            NewEcho("<td><p id='normalText'>MktCap: ".round($mrktCap,$roundVar)."</p></td>",$_SESSION['isMobile'],0);
            NewEcho("<td><p id='normalText'>1HrPct: ".round($pctChange1Hr,$roundVar)."</p></td>",$_SESSION['isMobile'],2);
            echo "<td><p id='largeText' >".round($amount,$roundVar)." $coin</p></td>";

            echo "<td rowspan='3'><a href='ManualSell.php?manSave=Yes&transactionID=$transactionID'><i class='fas fa-hryvnia' style='$fontSize;color:DodgerBlue'></i></a></td>";
            //echo "<td rowspan='3'><a href='ManualSell.php?splitCoin=$coin&amount=".$amount."&cost=$originalPurchaseCost&baseCurrency=$baseCurrency&orderNo=$orderNo&transactionID=$transactionID&salePrice=$livePrice'><i class='fas fa-file-archive' style='$fontSize;color:DodgerBlue'></i></a></td>";
            //echo "<td rowspan='3'><a href='ManualSell.php?trackCoin=Yes&baseCurrency=$baseCurrency&transactionID=$transactionID&salePrice=$livePrice&userID=$userID'><i class='fas fa-clock' style='$fontSize;color:DodgerBlue'></i></a></td>";
            echo "<td rowspan='3'><a href='Transactions.php?fixCoinAmount=Yes&SellRule=$transactionID&CoinID=$coinID&UserID=$userID&Amount=$amount'><i class='fas fa-bolt' style='$fontSize;color:DodgerBlue'></a></td>";
            echo "</tr><tr>";
            echo "<td><p id='normalText'>$coin</p></td>";
            echo "<td><p id='largeText' >ProfitPct: ".number_format($profitPct,$baseNum)." %</p></td>";
            NewEcho("<td><p id='normalText'>Vol: ".round($volume,$roundVar)."</p></td>",$_SESSION['isMobile'],0);
            NewEcho("<td><p id='normalText'>24HrPct: ".round($pctChange24Hr,$roundVar)."</p></td>",$_SESSION['isMobile'],2);
            $cost = round(number_format((float)$trackingSell[$x][4], 10, '.', ''),8);
            echo "<td><p id='normalText'>LivePrice: ".number_format($liveTotalCost,$baseNum)." $baseCurrency</p></td>";

            echo "</tr><tr>";


            //$numCol = getNumberColour($profitBtc);
            //echo "<td><p id='smallText' style='color:$numCol'>".round($profitBtc,8)."</p></td>";

            $numCol = getNumberColour($priceDiff1);
            echo "<td><p id='smallText' style='color:$numCol'>Price Diff: ".round($priceDiff1,$roundVar)."</p></td>";
            echo "<td><p id='largeText' >Profit: ".number_format($profit,$baseNum)." $baseCurrency</p></td>";

            NewEcho("<td><p id='normalText'>Sell Ords: ".round($sellOrders,$roundVar)."</p></td>",$_SESSION['isMobile'],0);
            NewEcho("<td><p id='normalText'>7D:".round($pctChange7D,$roundVar)."</p></td>",$_SESSION['isMobile'],0);
            $numCol = getNumberColour($profitBtc);

            $liveWithBase = number_format($liveTotalCost * $baseMultiplier,2);
            echo "<td>LivePrice: $liveWithBase USDT</td>";
        }
        print_r("</table>");
        Echo "<a href='SellCoins.php?noOverride=Yes'>View Mobile Page</a>".$_SESSION['MobOverride'];
				displaySideColumn();
//include header template
require('layout/footer.php');
$date = date('Y/m/d H:i:s', time());
echo " Last Updated :".$date;
?>
