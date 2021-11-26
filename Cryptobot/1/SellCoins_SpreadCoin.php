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
include_once ('/home/stevenj1979/SQLData.php');
$locationStr = "Location: /Investment-Tracker/Cryptobot/1/m/SellCoins.php";
setStyle($_SESSION['isMobile']);

if(isset($_GET['Mode'])){
  if ($_GET['Mode'] == 1){
    //BuyBack
    $ID = $_GET['ID']; $profitPct = $_GET['ProfitPct'];
    //echo "<BR>BuyBack ID: $ID";
    //Sell Coin
    $buyBackData = getTrackingSellData($ID);

    //Write to BuyBack Table
    if ($profitPct > 0){
        $totalMins = 10080;
        $totalRisesBuy = 10;
        $totalRisesSell = 10;
    }else{
        $totalMins = 20160;
        $totalRisesBuy = 15;
        $totalRisesSell = 1;
    }
    newTrackingSellCoins($buyBackData[0][0], $buyBackData[0][1],$ID,1,1,0,0.0,$totalRisesSell);
    setTransactionPending($ID);
    WriteBuyBack($ID,$profitPct,$totalRisesBuy, $totalMins);
  }
}

if(isset($_GET['override'])){
  if ($_SESSION['MobOverride'] == False){$_SESSION['MobOverride'] = True;$_SESSION['roundVar'] = 8;}

}

if(isset($_GET['noOverride'])){
  if ($_SESSION['MobOverride'] == True){$_SESSION['MobOverride'] = False;$_SESSION['roundVar'] = 2;}

}

if ($_SESSION['isMobile'] && $_SESSION['MobOverride'] == False){
  $_SESSION['roundVar'] = 2;
  //header('Location: SellCoins_Mobile_SB.php');
}


function getTrackingSellData($transactionID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "select `Cp`.`LiveCoinPrice`, `Tr`.`UserID`
    from `Transaction` `Tr`
    join `CoinPrice` `Cp` on `Cp`.`CoinID` = `Tr`.`CoinID`
    where `Tr`.`ID` = $transactionID";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['LiveCoinPrice'],$row['UserID']);
  }
  $conn->close();
  return $tempAry;
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

function getTrackingSellCoinsLoc($userID,$spreadBetRuleName){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "SELECT `ID`,`Type`,'CoinID',`UserID`,`CoinPrice`,`Amount`,`Status`,`OrderDate`,`CompletionDate`,'BittrexID','OrderNo',`Symbol`,`LastBuyOrders`, `LiveBuyOrders`,((`LiveBuyOrders`-`LastBuyOrders`)/`LastBuyOrders`)*100 as`BuyOrdersPctChange`
    ,`LastMarketCap`,`LiveMarketCap`,((`LiveMarketCap`-`LastMarketCap`)/`LastMarketCap`)*100 as `MarketCapPctChange`,`LastCoinPrice`,`LiveCoinPrice`,((`LiveCoinPrice`-`LastCoinPrice`)/`LastCoinPrice`)*100 as `CoinPricePctChange`,`LastSellOrders`
    ,`LiveSellOrders`,((`LiveSellOrders`-`LastSellOrders`)/`LastSellOrders`)*100 as `SellOrdersPctChange`,`LastVolume`,`LiveVolume`,((`LiveVolume`-`LastVolume`)/`LastVolume`)*100 as `VolumePctChange`,`Last1HrChange`,`Live1HrChange`
    ,((`Live1HrChange`-`Last1HrChange`)/`Last1HrChange`)*100 as `Hr1PctChange`,`Last24HrChange`,`Live24HrChange`,((`Live24HrChange`-`Last24HrChange`)/`Last24HrChange`)*100 as `Hr24PctChange`,`Last7DChange`,`Live7DChange`,((`Live7DChange`-`Last7DChange`)/`Last7DChange`)*100 as `D7PctChange`,`BaseCurrency`
    , if(`Price4` -`Price5` > 0, 1, if(`Price4` -`Price5` < 0, -1, 0)) as  `Price4Trend`
          , if(`Price3` -`Price4` > 0, 1, if(`Price3` -`Price4` < 0, -1, 0)) as  `Price3Trend`
          , if(`LastCoinPrice` -`Price3` > 0, 1, if(`LastCoinPrice` -`Price3` < 0, -1, 0)) as  `LastPriceTrend`
          , if(`LiveCoinPrice` -`LastCoinPrice` > 0, 1, if(`LiveCoinPrice` -`LastCoinPrice` < 0, -1, 0)) as  `LivePriceTrend`,`FixSellRule`,`SellRule`,`BuyRule`,`ToMerge`,`LowPricePurchaseEnabled`,'PurchaseLimit',`PctToPurchase`
          ,`BTCBuyAmount`,`NoOfPurchases`,`Name`,`Image`,10 as `MaxCoinMerges`,'NoOfCoinSwapsThisWeek'
    ,`CoinPrice`*`Amount` as OriginalPrice, ((`CoinPrice`*`Amount`)/100)*0.28 as CoinFee, `LiveCoinPrice`*`Amount` as LivePrice
    , ( `LiveCoinPrice`*`Amount`)-(`CoinPrice`*`Amount`)-( ((`CoinPrice`*`Amount`)/100)*0.28) as ProfitUSD
    , ( ( `LiveCoinPrice`*`Amount`)-(`CoinPrice`*`Amount`)-( ((`CoinPrice`*`Amount`)/100)*0.28)/(`CoinPrice`*`Amount`))*100 as ProfitPct
    ,`Name` as `SpreadBetRuleName` FROM `View7_SpreadBetSell`
    WHERE `UserID` = $userID and `SpreadBetRuleName` = '$spreadBetRuleName' and `Type` = 'SpreadSell' and `Status` = 'Open'
    ORDER BY `ProfitPct` Desc";
  $result = $conn->query($sql);
    print_r("<BR>$sql<BR>");
  //$result = mysqli_query($link4, $query);
//mysqli_fetch_assoc($result);`PctChange1Hr`, `PctChange24Hr`, `PctChange7D`
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['Type'],$row['CoinID'],$row['UserID'],$row['CoinPrice'],$row['Amount'],$row['Status'],$row['OrderDate'],$row['CompletionDate'],$row['BittrexID'],$row['OrderNo'],
      $row['Symbol'],$row['LastBuyOrders'],$row['LiveBuyOrders'],$row['BuyOrdersPctChange'],$row['LastMarketCap'],$row['LiveMarketCap'],$row['MarketCapPctChange'],$row['LastCoinPrice'],$row['LiveCoinPrice'], //19
      $row['CoinPricePctChange'],$row['LastSellOrders'],$row['LiveSellOrders'],$row['SellOrdersPctChange'],$row['LastVolume'],$row['LiveVolume'],$row['VolumePctChange'],$row['Last1HrChange'],$row['Live1HrChange'],$row['Hr1PctChange'],$row['Last24HrChange'],$row['Live24HrChange'] //31
      ,$row['Hr24PctChange'],$row['Last7DChange'],$row['Live7DChange'],$row['D7PctChange'],$row['BaseCurrency'],$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['FixSellRule'],$row['SellRule'],$row['BuyRule'] //43
      ,$row['ToMerge'],$row['LowPricePurchaseEnabled'],$row['PurchaseLimit'],$row['PctToPurchase'],$row['BTCBuyAmount'],$row['NoOfPurchases'],$row['Name'],$row['Image'],$row['MaxCoinMerges'],$row['NoOfCoinSwapsThisWeek'],$row['SpreadBetRuleName']); //54
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

function displaySpreadBetCoins($trackingSell, $arrLengthSell,$roundVar, $name,$fontSize){
  echo "<h3> $name</h3><br>";
  for($x = 0; $x < $arrLengthSell; $x++) {
      //Variables
      //$roundNum = 2;
      //if($_SESSION['isMobile'] == False){$roundNum = 8;}
      $coin = $trackingSell[$x][11];  $livePrice = $trackingSell[$x][19]; $LastCoinPrice = $trackingSell[$x][18]; $baseCurrency = $trackingSell[$x][36];
      $amount = $trackingSell[$x][5];  $orderNo = $trackingSell[$x][10]; $transactionID = $trackingSell[$x][0];
       $purchaseCost = $trackingSell[$x][4]; $realAmount = $trackingSell[$x][26];
      $mrktCap = $trackingSell[$x][17];  $volume = $trackingSell[$x][26]; $sellOrders = $trackingSell[$x][23];
      $pctChange1Hr = $trackingSell[$x][29]; $pctChange24Hr = $trackingSell[$x][32]; $pctChange7D = $trackingSell[$x][35]; $spreadBetRuleName = $trackingSell[$x][54];
      $priceDiff1 = $livePrice - $LastCoinPrice;
      $fee = (($livePrice* $amount)/100)*0.28;
      $liveTotalCost = ($livePrice * $amount);
      $originalPurchaseCost = ($purchaseCost * $amount);
      $profit = ($liveTotalCost - $originalPurchaseCost - $fee);
      $profitBtc = $profit/($originalPurchaseCost)*100;
      $userID = $_SESSION['ID'];
      $name = $trackingSell[$x][50]; $image = $trackingSell[$x][51];
      echo "<table><td rowspan='4'><a href='Stats.php?coin=$coin'><img src='$image'></a></td>";
      echo "<td><p id='largeText' >$spreadBetRuleName</p></td>";
      echo "<td rowspan='2'><p id='largeText' >".round($livePrice,$roundVar)."</p></td>";
      NewEcho("<td><p id='normalText'>".round($mrktCap,$roundVar)."</p></td>",$_SESSION['isMobile'],0);
      NewEcho("<td><p id='normalText'>".round($pctChange1Hr,$roundVar)."</p></td>",$_SESSION['isMobile'],2);
      echo "<td><p id='largeText' >".round($amount,$roundVar)." $coin</p></td>";



      echo "</tr><tr>";
      //Rowspan
      echo "<td><p id='normalText'>$coin</p></td>";
      //Rowspan
      NewEcho("<td><p id='normalText'>".round($volume,$roundVar)."</p></td>",$_SESSION['isMobile'],0);

      NewEcho("<td><p id='normalText'>".round($pctChange24Hr,$roundVar)."</p></td>",$_SESSION['isMobile'],2);
      $cost = round(number_format((float)$trackingSell[$x][4], 10, '.', ''),8);
      echo "<td><p id='normalText'>$cost</p></td>";



      echo "</tr><tr>";
      //$numCol = getNumberColour($profitBtc);
      //echo "<td><p id='smallText' style='color:$numCol'>".round($profitBtc,8)."</p></td>";

      $numCol = getNumberColour($priceDiff1);
      echo "<td><p id='smallText' style='color:$numCol'>".round($priceDiff1,$roundVar)."</p></td>";
      echo "<td><p id='largeText' >".round($profit,$roundVar)." $baseCurrency</p></td>";

      NewEcho("<td><p id='normalText'>".round($sellOrders,$roundVar)."</p></td>",$_SESSION['isMobile'],0);
      NewEcho("<td><p id='normalText'>".round($pctChange7D,$roundVar)."</p></td>",$_SESSION['isMobile'],0);
      $numCol = getNumberColour($profitBtc);
      echo "<td><p id='smallText' style='color:$numCol'>".round($profitBtc,$roundVar)."</p></td>";

      //Last Line
      echo "</tr><tr>";
      echo "<td><a href='ManualSell.php?manSell=Yes&coin=$coin&amount=".$amount."&cost=$originalPurchaseCost&baseCurrency=$baseCurrency&orderNo=$orderNo&transactionID=$transactionID&salePrice=$livePrice'><i class='fas fa-shopping-cart' style='$fontSize;color:DodgerBlue'></i></a></td>";
      echo "<td><a href='ManualSell.php?splitCoin=$coin&amount=".$amount."&cost=$originalPurchaseCost&baseCurrency=$baseCurrency&orderNo=$orderNo&transactionID=$transactionID&salePrice=$livePrice'><i class='fas fa-file-archive' style='$fontSize;color:DodgerBlue'></i></a></td>";
      echo "<td><a href='ManualSell.php?trackCoin=Yes&baseCurrency=$baseCurrency&transactionID=$transactionID&salePrice=$livePrice&userID=$userID'><i class='fas fa-clock' style='$fontSize;color:DodgerBlue'></i></a></td>";
      echo "<td><a href='ManualSell.php?manReopen=Yes&transactionID=$transactionID'><i class='fas fa-hryvnia' style='$fontSize;color:DodgerBlue'></i></a></td>";
      echo "<td><a href='SellCoins_SpreadCoin.php?Mode=1&ID=$transactionID&ProfitPct=$profitBtc'>Buy Back</a></td>";
  }
  print_r("</table><br>");
}

function getSpreadBetIDOpen($userID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

    $sql = "SELECT `Tr`.`ID`,`Sbr`.`Name` as `SpreadBetRuleName`
    FROM `Transaction` `Tr`
    join `SpreadBetRules` `Sbr` on `Sbr`.`ID` = `Tr`.`SpreadBetRuleID`
    WHERE `Tr`.`UserID` = $userID and `Type` = 'SpreadSell' and `Status` = 'Open'
    group by `SpreadBetRuleName`";
  $result = $conn->query($sql);
    //print_r($sql);
  //$result = mysqli_query($link4, $query);
//mysqli_fetch_assoc($result);`PctChange1Hr`, `PctChange24Hr`, `PctChange7D`
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['SpreadBetRuleName']);
  }
  $conn->close();
  return $tempAry;
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
        $spreadBetID = getSpreadBetIDOpen($_SESSION['ID']);
        $spreadBetIDSize = count($spreadBetID);
        $roundVar = $_SESSION['roundVar'];
        //$userConfig = getConfig($_SESSION['ID']);
        print_r("<h2>Sell Some Coins Now!</h2>");
        echo "<h3><a href='SellCoins.php'>Sell Coins</a> &nbsp > &nbsp <a href='SellCoins_Tracking.php'>Tracking</a> &nbsp > &nbsp <a href='SellCoins_Saving.php'>Saving</a> &nbsp > &nbsp <a href='SellCoins_Spread.php'>Spread Bet</a> &nbsp > &nbsp <a href='SellCoins_SpreadCoin.php'>Spread Bet Coin</a>
         &nbsp > &nbsp <a href='SellCoins_SwapCoins.php'>Swap Coins</a></h3>";
        for ($s=0; $s<$spreadBetIDSize; $s++){
          $trackingSell = getTrackingSellCoinsLoc($_SESSION['ID'],$spreadBetID[$s][1]);
          $arrLengthSell = count($trackingSell);
          displaySpreadBetCoins($trackingSell, $arrLengthSell,$roundVar, $spreadBetID[$s][1],$fontSize);
        }


        Echo "<a href='SellCoins.php?noOverride=Yes'>View Mobile Page</a>".$_SESSION['MobOverride'];
				displaySideColumn();
//include header template
require('layout/footer.php');
$date = date('Y/m/d H:i:s', time());
echo " Last Updated :".$date;
?>
