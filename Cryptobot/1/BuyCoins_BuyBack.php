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

if(isset($_GET['override'])){
if ($_SESSION['MobOverride'] == False){$_SESSION['MobOverride'] = True;}
}

if(isset($_GET['noOverride'])){
if ($_SESSION['MobOverride'] == True){$_SESSION['MobOverride'] = False;}
}

//Echo "<BR> isMobile: ".$_SESSION['isMobile']." | MobOverride: ".$_SESSION['MobOverride'];

if ($_SESSION['isMobile'] && $_SESSION['MobOverride'] == False){
//header('Location: BuyCoins_Mobile.php');
}
//echo "<BR> TESTING: ".$_GET['Mode']." | ".$_POST['Mode'];
if (isset($_GET['Mode']) OR (isset($_POST['Mode']))){
  if ($_GET['Mode'] == 1){
    $ID = $_GET['ID'];
    $symbol = $_GET['Symbol'];
    $quantity = $_GET['Quantity'];
    $livePrice = $_GET['LivePrice'];
    $sellPrice = $_GET['SellPrice'];
    $usd_Amount = $_GET['usd'];
    //echo "<BR> ID is $ID | $symbol | $quantity | $livePrice | $sellPrice";
    //Symbol=$symbol&Quantity=$quantity&LivePrice=$liveCoinPrice&SellPrice=$sellPriceBA
    displayEditHTML($ID, $symbol, $quantity,$livePrice,$sellPrice,$usd_Amount);
  }elseif($_GET['Mode'] == 2){
    if (isset($_POST['refreshBtn'])){
      $ID = $_POST['ID'];
      $symbol = $_POST['Symbol'];
      $quantity = $_POST['Quantity'];
      $livePrice = $_POST['LivePrice'];
      $sellPrice = $_POST['SellPrice'];
      $priceUSD = $_POST['PriceUSD'];
      $newQuant = $priceUSD / $livePrice;
      displayEditHTML($ID, $symbol, $newQuant,$livePrice,$sellPrice,$priceUSD);
    }elseif (isset($_POST['submitBtn'])){
      echo "<BR>Submit button";
      $ID = $_POST['ID'];
      $symbol = $_POST['Symbol'];
      $quantity = $_POST['Quantity'];
      $livePrice = $_POST['LivePrice'];
      $sellPrice = $_POST['SellPrice'];
      $priceUSD = $_POST['PriceUSD'];
      writeBuyBackToSQL($ID,$priceUSD);
      header('Location: BuyCoins_BuyBack.php');
    }elseif (isset($_POST['backBtn'])){
      header('Location: BuyCoins_BuyBack.php');
    }
  }elseif($_GET['Mode'] == 3){
      $bbID = $_GET['ID'];
      //Echo "Delete $bbID";
      deleteBuyBackToSQL($bbID);
      header('Location: BuyCoins_BuyBack.php');
  }

}else{
  displayMain();
}

function deleteBuyBackToSQL($ID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  //$sql = "UPDATE `BuyBack` SET `Status`= 'Closed',`DateClosed` = now()  WHERE `ID` = $ID ";
  $sql = "UPDATE `BuyBack` SET `Status`= CASE
                    WHEN `Status` = 'Open' THEN 'Closed'
                    WHEN `Status` = 'Closed' THEN 'Open'
                    end
                ,`DateClosed` = now()
                  WHERE `ID` = $ID
  'Closed',`DateClosed` = now()  WHERE `ID` = $ID ";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("deleteBuyBackToSQL: ".$sql, 'TrackingCoins', 0);
}

function writeBuyBackToSQL($ID, $usd_Amount){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `BuyBack` SET `USDBuyBackAmount` = $usd_Amount  WHERE `ID` = $ID ";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("writeBuyBackToSQL: ".$sql, 'TrackingCoins', 0);
}

function displayEditHTML($ID, $symbol, $quantity,$livePrice,$sellPrice,$usd_Amount){
  displayHeader(3);
  echo "<form action='BuyCoins_BuyBack.php?Mode=2' method='post'>";
  echo "<input type='text' name='ID' id='ID' class='' placeholder='' value='$ID' style='color:Gray' readonly tabindex=''>";
  echo "<input type='text' name='Symbol' id='Symbol' class='' placeholder='' value='$symbol' style='color:Gray' readonly tabindex=''>";
  echo "<input type='text' name='Quantity' id='Quantity' class='' placeholder='' value='$quantity' style='color:Gray' readonly tabindex=''>";
  echo "<input type='text' name='LivePrice' id='LivePrice' class='' placeholder='' value='$livePrice' style='color:Gray' readonly tabindex=''>";
  echo "<input type='text' name='SellPrice' id='SellPrice' class='' placeholder='' value='$sellPrice' style='color:Gray' readonly tabindex=''>";
  //$priceUSD =  $livePrice * $quantity;
  echo "<input type='text' name='PriceUSD' id='PriceUSD' class='' placeholder='' value='$usd_Amount' tabindex=''>";
  echo "<BR><input type='submit' name='refreshBtn' value='Refresh'>";
  echo "<BR><input type='submit' name='submitBtn' value='Submit'>";
  echo "<BR><input type='submit' name='backBtn' value='Back'>";
  echo "</FORM>";

  displaySideColumn();
}

function getCoinsfromSQL(){
  $conn = getSQLConn(rand(1,3));
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

function getTrackingCoinsLoc($userID, $WhereClause, $open){
$conn = getSQLConn(rand(1,3));
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if ($open == 1){
  $WhereClause2 = "`StatusBb` <> 'Closed'";
}else{
  $WhereClause2 = "`StatusBb` <> 'Closed' and `hoursSinceClosed` < 400";
}
  $sql = "SELECT `IDBb`, `TransactionID`, `Quantity`, `SellPrice`, `StatusBb`, `SpreadBetTransactionID`, `SpreadBetRuleID`, `CoinID`, `SellPrice` as `SellPriceBA`, `LiveCoinPrice`
            , (`LiveCoinPrice`- `SellPrice`) as `PriceDifferece`, ((`LiveCoinPrice`- `SellPrice`)/`SellPrice`)*100 as `PriceDifferecePct`, `UserID`, `Email`, `UserName`, `ApiKey`, `ApiSecret`
            , `KEK`, (`CoinPrice`*`Amount`)-(`LiveCoinPrice`*`Amount`) as `OriginalSaleProfit`
            , (((`CoinPrice`*`Amount`)-(`LiveCoinPrice`*`Amount`))/(`CoinPrice`*`Amount`))*100 as `OriginalSaleProfitPct`, `ProfitMultiply`, `NoOfRaisesInPrice`, `BuyBackPct`,`Image`,`Symbol`
            ,`USDBuyBackAmount`,`HoursFlatLowPdcs`,`HoursFlatHighPdcs`,`Hr1ChangePctChange`,`HoursFlatPdcs`,`BuyBackHoursFlatTarget`,`BaseCurrency`,`MinsUntilEnable`,`PctOfAutoBuyBack`,`BuyBackHoursFlatAutoEnabled`,`MaxHoursFlat`
            ,`hoursSinceClosed`
            FROM `View9_BuyBack`
            where $WhereClause2 and `UserID` = $userID $WhereClause";
            echo "<BR>$sql<BR>";
   //echo $sql.getHost();
$result = $conn->query($sql);
while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['IDBb'],$row['TransactionID'],$row['Quantity'],$row['SellPrice'],$row['StatusBb'],$row['SpreadBetTransactionID'],$row['SpreadBetRuleID'],$row['CoinID'],$row['SellPriceBA'] //8
    ,$row['LiveCoinPrice'],$row['PriceDifferece'],$row['PriceDifferecePct'],$row['UserID'],$row['Email'],$row['UserName'],$row['ApiKey'],$row['ApiSecret'],$row['KEK'] //17
    ,$row['OriginalSaleProfit'],$row['OriginalSaleProfitPct'],$row['ProfitMultiply'],$row['NoOfRaisesInPrice'],$row['BuyBackPct'],$row['Image'],$row['Symbol'],$row['USDBuyBackAmount'] //25
    ,$row['HoursFlatLowPdcs'],$row['HoursFlatHighPdcs'],$row['Hr1ChangePctChange'],$row['HoursFlatPdcs'],$row['BuyBackHoursFlatTarget'],$row['BaseCurrency'],$row['MinsUntilEnable'] //32
    ,$row['PctOfAutoBuyBack'],$row['BuyBackHoursFlatAutoEnabled'],$row['MaxHoursFlat'],$row['hoursSinceClosed']); //36
}
$conn->close();
return $tempAry;
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
$sql = "SELECT `MarketCapBuyPct`,`VolumeBuyPct`,`BuyOrdersPct`,`MinPctGain`  FROM `Config` WHERE `UserID` =  $userID";
$result = $conn->query($sql);
while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['MarketCapBuyPct'],$row['VolumeBuyPct'],$row['BuyOrdersPct'],$row['MinPctGain']);
}
$conn->close();
return $tempAry;
}

function sendEmailLoc($to, $symbol, $amount, $cost){
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

function getLiveCoinPrice($symbol){
  $limit = 500;
  $cnmkt = "https://api.coinmarketcap.com/v1/ticker/?limit=".$limit;
  $fgc = json_decode(file_get_contents($cnmkt), true);
for($i=0;$i<$limit;$i++){
  if ($fgc[$i]["symbol"] == $symbol){
    $tmpCoinPrice = $fgc[$i]["price_btc"];
  }
}
logAction("$cnmkt",'CMC');
return $tmpCoinPrice;
}

function bittrexCoinPriceLoc($apikey, $apisecret, $baseCoin, $coin){
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
$conn = getSQLConn(rand(1,3));
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

function displayTable($tracking, $header, $linkName){
  if ($_SESSION['isMobile']){ $num = 2; $fontSize = "font-size:60px"; }else{$num = 8;$fontSize = "font-size:32px"; }
  $newArrLength = count($tracking);
  echo "<h2>$header</H2><table id='t01'>";
  //Table

  for($x = 0; $x < $newArrLength; $x++) {
    //Variables
    $ID = $tracking[$x][0];
    $transactionID = $tracking[$x][1];
    $quantity = $tracking[$x][2];
    $status = $tracking[$x][4];
    $spreadBetTransactionID = $tracking[$x][5];
    $spreadBetRuleID = $tracking[$x][6];
    $coinID = $tracking[$x][7];
    $sellPriceBA = $tracking[$x][8];
    $liveCoinPrice = $tracking[$x][9];
    $priceDifferece = $tracking[$x][10];
    $priceDifferecePct = $tracking[$x][11];
    $originalSaleProfit = $tracking[$x][18];
    $originalSaleProfitPct = $tracking[$x][19];
    $noOfRaisesInPrice = $tracking[$x][21];
    $buyBackPct = $tracking[$x][22];
    $image = $tracking[$x][23];
    $symbol = $tracking[$x][24];
    $USD_Amount = $tracking[$x][25];
    $hoursFlatLow = $tracking[$x][26];
    $hoursFlatHigh = $tracking[$x][27];
    $hr1PctChange = $tracking[$x][28];
    $hoursFlat = $tracking[$x][29];
    $hoursFlatTarget = $tracking[$x][30];
    $baseCurrency = $tracking[$x][31];
    $minsUntilEnable = $tracking[$x][32];
    $pctOfAuto = $tracking[$x][33];
    $buyBackHoursFlatAutoEnabled = $tracking[$x][34];
    $maxHoursFlat = $tracking[$x][35];

    if ($buyBackHoursFlatAutoEnabled == 1){
      $hoursFlatTarget = Floor(($maxHoursFlat/100)*$pctOfAuto);
    }

    echo "<tr><td rowspan='3'><a href='Stats.php?coin=$coinID'><img src='$image'></img></a></td>";
    Echo "<td>$symbol</td>";
    Echo "<td>".round($buyBackPct,$num)." %</td>";

    //$tdColour = setTextColour($Live1HrChange, False);
    echo "<td>Sell: ".round($sellPriceBA,$num)."</td>";

    echo "<td>Qty: ".round($quantity,$num)."</td>";

    //Echo "<td></td>";
    //Echo "<td></td>";
    $hoursFlatOriginalTarget = floor(($maxHoursFlat/100)*(($pctOfAuto)));
    if ($priceDifferecePct < $buyBackPct){
      //$pctOfAuto = 100 + $priceDifferecePct;
      //$hoursFlatTarget = floor(($maxHoursFlat/100)*$pctOfAuto);
      $hoursFlatTarget = floor(($maxHoursFlat/100)*(($pctOfAuto/100)*Abs(100+$priceDifferecePct)));

    }

    echo "</tr><tr>";
    Echo "<td>Live: ".round($liveCoinPrice,$num)."</td>";
    Echo "<td>HoursFlat: $hoursFlat / $hoursFlatTarget ($hoursFlatOriginalTarget)</td>";
    Echo "<td>1Hr Pct Change: $hr1PctChange</td>";
    Echo "<td>$baseCurrency</td>";
    echo "</tr><tr>";
    //$numCol = getNumberColour($priceDiff1);
    Echo "<td>Price Dif: ".round($priceDifferecePct,$num)." %</td>";
    Echo "<td>Org: ".round($originalSaleProfitPct,$num)."</td>";
    Echo "<td>USD".round($USD_Amount,$num)."</td>";
    Echo "<td>$minsUntilEnable</td>";
    echo "</tr><tr>";
    Echo "<td></td>";
    Echo "<td><a href='BuyCoins_BuyBack.php?Mode=1&ID=$ID&Symbol=$symbol&Quantity=$quantity&LivePrice=$liveCoinPrice&SellPrice=$sellPriceBA&usd=$USD_Amount'>Edit</a></td>";
    Echo "<td><a href='BuyCoins_BuyBack.php?Mode=3&ID=$ID'>$linkName</a></td>";
    Echo "<td></td>";
    Echo "<td></td></tr>";
  }//end for
  print_r("</table>");
}

function displayMain(){
  displayHeader(3);

  $userID = $_SESSION['ID'];
  $tracking = getTrackingCoinsLoc($userID, " and `BBRuleDisabled` = 0", 1);

  //echo $newArrLength;
  //$userConfig = getConfig($_SESSION['ID']);
  //$user = getUserIDs($_SESSION['ID']);
  //print_r("<HTML><Table><th>Coin</th><th>BuyPattern</th><th>MarketCapHigherThan5Pct</th><th>VolumeHigherThan5Pct</th><th>BuyOrdersHigherThan5Pct</th><th>PctChange</th><tr>");

  //echo "<h3><a href='BuyCoins.php'>Buy Coins</a> &nbsp > &nbsp <a href='BuyCoinsFilter.php'>Buy Coins Filter</a> &nbsp > &nbsp <a href='BuyCoinsTracking.php'>Buy Coins Tracking</a>&nbsp > &nbsp <a href='BuyCoins_Spread.php'>Buy Coins Spread Bet</a>
  //&nbsp > &nbsp <a href='BuyCoins_BuyBack.php'>Buy Back</a></h3>";
  displaySubHeader("BuyCoin");
  //if($_SESSION['isMobile'] == False){
  //print_r("<Table><th>&nbspCoin</th><TH>&nbspBase Currency</th><TH>&nbspPrice</th>");
  //  NewEcho("<TH>&nbspMarket Cap %</th><TH>&nbspVolume by %</th><TH>&nbspBuy Orders %</th>",$_SESSION['isMobile'],0);
  //  echo "<TH>&nbsp% Change 1Hr</th>";
  //  NewEcho("<TH>&nbsp% Change 24 Hrs</th><TH>&nbsp% Change 7 Days</th>",$_SESSION['isMobile'],0);
  //}

  //echo "<TH>&nbspPrice Diff 1</th><TH>&nbspPrice Change</th>";
  //echo "<TH>&nbspBuy Pattern</th><TH>&nbsp1HR Change Pattern</th><TH>&nbspManual Buy</th><TH>&nbspSet Alert</th><tr>";
  //$roundNum = 2;
  displayTable($tracking,"Enabled","Delete");
  $tracking = getTrackingCoinsLoc($userID, " and `BBRuleDisabled` = 1", 1);
  displayTable($tracking,"Disabled","Delete");
  //Echo "<a href='BuyCoins.php?noOverride=Yes'>View Mobile Page</a>".$_SESSION['MobOverride'];
  $tracking = getTrackingCoinsLoc($userID, " ", 0);
  displayTable($tracking,"Closed","Undelete");

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
