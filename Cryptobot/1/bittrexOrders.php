<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.2/css/all.css" integrity="sha384-/rXc/GQVaYpyDdyxK+ecHPVYJSN9bmVFBvjA/9eOB+pb3F2w2N6fc5qB9Ew5yIns" crossorigin="anonymous">
</head>
<?php require('includes/config.php');
include_once '../includes/newConfig.php';?>
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
require($_SERVER['DOCUMENT_ROOT'].'/Investment-Tracker/Cryptobot/1/layout/header.php');
include_once ('../../../../SQLData.php');
setStyle($_SESSION['isMobile']);
//$coin = trim($_GET['coin']);
//$AreYouSure = trim($_GET['AreYouSure']);

//if(!empty($coin) && !empty($AreYouSure)){

//}
if(empty($sql_option)){
  //$GLOBALS['sql_option'] = "`Status` = '1'";
  //unset($dropArray);
  $dropArray[] = Array("Open","Closed","Cancelled","All");
}
if(isset($_POST['submit'])){if(empty($_POST['dropDown'])){
  //Print_r("I'm HERE!!!");
  changeSelection();
}}

if(!empty($_GET['Hold'])){

  $bittrexID = $_GET['Hold'];
  echo "HOLD is not empty | $bittrexID";
  runBittrexHold($bittrexID);
}

function runBittrexHold($bittrexID){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `BittrexAction` SET `Status` = 'Hold' WHERE `ID` = $bittrexID";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    newLogToSQL("runBittrexHold",$sql,3,1,"SQL","BittrexID:$bittrexID");
    LogAction("runBittrexHold:".$sql, 'SQL_UPDATE', 0);
    $conn->close();
}

function changeSelection(){
  //global $sql_option;
  global $dropArray;
  unset($dropArray);
  if ($_POST['transSelect']=='Closed'){
     $_SESSION['BittrexListSelected'] = "Closed";
     //$dropArray[] = Array("Closed","Open","All");
  }elseif ($_POST['transSelect']=='Open'){
     $_SESSION['BittrexListSelected'] = "1";
     //$dropArray[] = Array("All","Closed","Open");
  }elseif ($_POST['transSelect']=='Cancelled'){
     $_SESSION['BittrexListSelected'] = "Cancelled";
     //$dropArray[] = Array("All","Closed","Open");
  }else{
    $_SESSION['BittrexListSelected'] = "1A";
    //$dropArray[] = Array("All","Closed","Open");
  }
  //print_r($globals['sql_Option']);
}


function getBTTrackingCoins($userID){
  $tempAry = [];
  $sqlOption = $_SESSION['BittrexListSelected'];
  if ($sqlOption == "1A"){$statusA = ""; $statusB = ""; $sqlOption = "1";}else {$statusA = "`StatusBa` = '"; $statusB = "'";}
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Type`,`BittrexRefBa` as `BittrexRef`,`ActionDate`,`CompletionDate`,`StatusBa`,`SellPrice`,`UserName`,`APIKey`,`APISecret`,`Symbol`,`Amount`,`CoinPrice`,`UserIDBa`,`Email`,`OrderNo`,
          `TransactionID`,`BaseCurrency`,`LiveCoinPrice`,`QuantityFilled`,`KEK`,`MinsSinceAction`,`MinsToCancelAction`,`MinsRemaining`,`Image`,`CoinID4`,`IDBa`
  FROM `View4_BittrexBuySell` WHERE `userIDBa` = $userID and ".$statusA.$sqlOption.$statusB." order by `ActionDate` desc limit 50";
  //echo "<BR>$sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
//mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Type'],$row['BittrexRef'],$row['ActionDate'],$row['CompletionDate'],$row['StatusBa'],$row['SellPrice'],$row['UserName'],$row['APIKey'],$row['APISecret'],$row['Symbol'] //9
      ,$row['Amount'],$row['CoinPrice'],$row['UserID'],$row['Email'],$row['OrderNo'],$row['TransactionID'],$row['BaseCurrency'],$row['LiveCoinPrice'],$row['QuantityFilled'],$row['KEK'] //19
    ,$row['MinsSinceAction'],$row['MinsToCancelAction'],$row['MinsRemaining'],$row['Image'],$row['CoinID4'],$row['IDBa']);  //25
  }
  $conn->close();
  return $tempAry;
}

function getNumberColourLoc($ColourText, $target){
  if ($ColourText >= $target){
    $colour = "green";
  }else{
    $colour = "red";
  }
  //echo $colour;
  return $colour;
}

function getConfig($userID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `MarketCapBuyPct`,`VolumeBuyPct`,`BuyOrdersPct`,`MinPctGain`  FROM `Config` WHERE `UserID` =  $userID";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['MarketCapBuyPct'],$row['VolumeBuyPct'],$row['BuyOrdersPct'],$row['MinPctGain']);
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

function displayOption($name){
  $tempStr = $_SESSION['BittrexListSelected'];
  if ($tempStr == "1" and $name = "Open"){
    echo "<option  selected='selected' value='$name'>$name</option>";
  }elseif ($tempStr == "1A" and $name == "All"){
    echo "<option  selected='selected' value='$name'>$name</option>";
  }else{
    echo "<option value='$name'>$name</option>";
  }

}

        displayHeader(6);
				$tracking = getBTTrackingCoins($_SESSION['ID']);
				$newArrLength = count($tracking);
        //$userConfig = getConfig($_SESSION['ID']);
        //$user = getUserIDs($_SESSION['ID']);
				//print_r("<HTML><Table><th>Coin</th><th>BuyPattern</th><th>MarketCapHigherThan5Pct</th><th>VolumeHigherThan5Pct</th><th>BuyOrdersHigherThan5Pct</th><th>PctChange</th><tr>");
				print_r("<h2>Bittrex Orders</h2>");
        echo "<form action='bittrexOrders.php?dropdown=Yes' method='post'>";
        $sqlOption = $_SESSION['BittrexListSelected'];
        $selected1 = ''; $selected2 = ''; $selected3 = ''; $selected4 = '';$selected5 = '';
        //Echo "<BR> $sqlOption | ".$dropArray[0][0].$dropArray[0][1].$dropArray[0][2].$dropArray[0][3];
        if ($sqlOption == "1"){ $selected1 = ' selected';}
        elseif ($sqlOption == $dropArray[0][1]){ $selected2 = ' selected';}
        elseif ($sqlOption == $dropArray[0][2]){ $selected3 = ' selected';}
        //elseif ($sqlOption == "1"){ $selected4 = ' selected';}
        elseif ($sqlOption == "1A"){ $selected5 = ' selected';}
            echo "<select name='transSelect' id='transSelect' class='enableTextBox'>
           <option value='".$dropArray[0][0]."'$selected1>".$dropArray[0][0]."</option>
            <option value='".$dropArray[0][1]."'$selected2>".$dropArray[0][1]."</option>
            <option value='".$dropArray[0][2]."'$selected3>".$dropArray[0][2]."</option>
            <option value='".$dropArray[0][3]."'$selected4 $selected5>".$dropArray[0][3]."</option></select>
            <input type='submit' name='submit' value='Update' class='settingsformsubmit' tabindex='36'>
            </form>";

              echo "<Table><TH>&nbspType&nbsp</TH><TH>&nbspcoin&nbsp</TH>";
              NewEcho("<TH>&nbspuserID&nbsp</TH><TH>&nbspactionDate&nbsp</TH><TH>&nbspbaseCurrency&nbsp</TH>",$_SESSION['isMobile'],0);

              NewEcho("<TH>&nbspuserName&nbsp</TH><TH>&nbsporderNo&nbsp</TH>",$_SESSION['isMobile'],0);
              echo "<TH>&nbspamount&nbsp</TH><TH>&nbspcost&nbsp</TH><TH>&nbspstatus&nbsp</TH>";
              NewEcho("<TH>&nbspbittrex Ref&nbsp</TH>",$_SESSION['isMobile'],0);
              echo "<TH>&nbspsellPrice&nbsp</TH><TH>&nbsplivePrice&nbsp</TH><TH>% Difference Sale</TH><TH>% Difference Live</TH><TH>% Quantity Filled</TH><TH>Time Until Cancel</TH><TH>&nbspCancel&nbsp</TH><TH>&nbspHold&nbsp</TH><TR>";

				for($x = 0; $x < $newArrLength; $x++) {
          $type = $tracking[$x][0]; $apiKey = $tracking[$x][7];$apiSecret = $tracking[$x][8];$coin = $tracking[$x][9];$email = $tracking[$x][13];$userID = $tracking[$x][12];
          $actionDate = $tracking[$x][2]; $baseCurrency = $tracking[$x][16]; $liveCoinPrice = $tracking[$x][17];
          $userName = $tracking[$x][6];$orderNo = $tracking[$x][14];$amount = $tracking[$x][10];$cost = $tracking[$x][11];$status = $tracking[$x][4];$bittrexRef = $tracking[$x][1];
          $sellPrice = $tracking[$x][5]; $transactionID = $tracking[$x][15]; $quantityFilled = $tracking[$x][18]; $KEK = $tracking[$x][19]; $minsFromAction = $tracking[$x][20];
          $minsUntilCancel = $tracking[$x][21];$minsRemaining = $tracking[$x][22]; $image = $tracking[$x][23];  $coinID = $tracking[$x][24]; $bittrexID = $tracking[$x][25];
          if (!Empty($KEK)){$apiSecret = decrypt($KEK,$tracking[$x][8]);}
          echo "<td>&nbsp$type</td>";
          echo "<td>&nbsp<a href='Stats.php?coin=$coinID'><img src='$image' width=60 height=60></a></td>";
          //echo "<td>$totalScore</td>";
          NewEcho("<td>&nbsp$userID</td><td>&nbsp$actionDate</td><td>&nbsp$baseCurrency</td>",$_SESSION['isMobile'],0);
          //echo "<td>$sendEmail</td>";
          //echo "<td>$sellCoin</td>";
          //echo "<td>$ruleID</td>";
          NewEcho("<td>&nbsp$userName</td>",$_SESSION['isMobile'],0);
          $roundNum = 8;
          if ($_SESSION['isMobile']){
              $roundNum = 4;
          }
          NewEcho("<td>&nbsp$orderNo</td>",$_SESSION['isMobile'],0);
          echo "<td>&nbsp".round($amount,$roundNum)."</td>";
          echo "<td>&nbsp".round($cost,$roundNum)."</td>"; echo "<td>&nbsp$status</td>";
          NewEcho("<td>&nbsp$bittrexRef</td>",$_SESSION['isMobile'],0);
          echo "<td>&nbsp".round($sellPrice,$roundNum)."</td>";
          //$liveCoinPrice = number_format((float)bittrexCoinPriceLoc($apiKey,$apiSecret,$baseCurrency,$coin), 10, '.', '');
          if ($type == 'Buy'){
            $pctDifference = number_format((float)(($liveCoinPrice-$cost)/$cost)*100, $roundNum, '.', '');
            $livePricePct = 0;
          }else{
            $pctDifference = number_format((float)(($liveCoinPrice-$sellPrice)/$sellPrice)*100, $roundNum, '.', '');
            $livePricePct = number_format((float)(($liveCoinPrice-$cost)/$cost)*100,$roundNum, '.', '');
          }
          //240 - 52

          echo "<td>&nbsp".round($liveCoinPrice,$roundNum)."</td>";
          echo "<td>&nbsp".round($pctDifference,$roundNum)."</td>";
          echo "<td>&nbsp".round($livePricePct,$roundNum)."</td>";
          echo "<td>&nbsp".round($quantityFilled,$roundNum)."</td>";
          echo "<td>&nbsp$minsRemaining</td>";
          echo "<td><a href='bittrexCancel.php?uuid=$bittrexRef&apikey=$apiKey&apisecret=$apiSecret&orderNo=$orderNo&transactionID=$transactionID&type=$type' onClick=\"javascript:return confirm('are you sure you want to cancel this order?');\"><i class='fas fa-ban' style='font-size:21px;color:#C0392B'></i></td>";
          echo "<td><a href='bittrexOrders.php?hold=$bittrexID' onClick=\"javascript:return confirm('are you sure you want to cancel this order?');\">Hold</a></td><tr>";
				}
				print_r("</table>");
				displaySideColumn();
//include header template
require('layout/footer.php');
$date = date('Y/m/d H:i:s', time());
echo " Last Updated :".$date;
?>
