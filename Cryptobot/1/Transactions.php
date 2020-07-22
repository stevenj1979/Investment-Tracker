<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
</head>
<?php require('includes/config.php');
include_once '../includes/newConfig.php';?>
<style>
<?php setStyle($_SESSION['isMobile']); ?>
</style> <?php

//if not logged in redirect to login page
if(!$user->is_logged_in()){ header('Location: login.php'); exit(); }

//define page title
$title = 'CryptoBot';

//include header template
$title = 'CryptoBot';
$current_url = $_SERVER[ 'REQUEST_URI' ];
header( "Refresh: 120; URL=$current_url" );
//include header template
require($_SERVER['DOCUMENT_ROOT'].'/Investment-Tracker/Cryptobot/1/layout/header.php');
include_once ('/home/stevenj1979/SQLData.php');
$locationStr = "Location: /Investment-Tracker/Cryptobot/1/m/BuyCoins.php";
setStyle($_SESSION['isMobile']);
if(isset($_GET['override'])){
  $_SESSION['MobDisplay'] = 2;
}

if(isset($_GET['noOverride'])){
  $_SESSION['MobDisplay'] = 0;
}
//$globals['sql_Option'] = "`Status` = 'Open'";
//if(empty($globals['sql_Option'])){$globals['sql_Option']= "`Status` = 'Open'";}

date_default_timezone_set('Asia/Dubai');
if ($_SESSION['DisableUntil']<date("Y-m-d H:i:s", time())) { $liveCoinStatus = "Active";} else { $liveCoinStatus = "Disabled Until: ".$_SESSION['DisableUntil']." - ".date("Y-m-d H:i:s", time());}
displayHeader(1);

if($_POST['transSelect'] <> ""){
  //Print_r("I'm HERE!!!".$_POST['submit']);
  changeSelection();
}elseif ($_GET['changefixSell'] <> ""){
  //echo "1";
  displayChangeFix($_GET['FixSellRule'],$_GET['SellRule']);
}elseif ($_POST['transID'] <> ""){
  //echo "2";
  updateSellRule();
  header('Location: Transactions.php');
}elseif ($_GET['merge'] <> ""){
  //echo "1";
  updateMerge($_GET['SellRule']);
  //displayMerge($_GET['FixSellRule'],$_GET['SellRule']);
  header('Location: Transactions.php');
}else{
  //echo "3".$_POST['newSellRule']."-".$_POST['SellRule'];
  displayDefault();
}


function changeSelection(){
  //global $sql_option;
  //global $dropArray;
  //echo "<BR> TransSelect : ".$_POST['transSelect'];
  if ($_POST['transSelect']=='Open'){
     $_SESSION['TransListSelected'] = "Open";
     //$dropArray[] = Array("Open","Sold","All");
  }elseif ($_POST['transSelect']=='Sold'){
    $_SESSION['TransListSelected'] = "Sold";
    //$dropArray[] = Array("Sold","Open","All");
  }elseif ($_POST['transSelect']=='Pending'){
    $_SESSION['TransListSelected'] = "Pending";
    //$dropArray[] = Array("Sold","Open","All");
  }elseif ($_POST['transSelect']=='All'){
    $_SESSION['TransListSelected'] = "1";
    //$dropArray[] = Array("All","Open","Sold");
  }
  //print_r($globals['sql_Option']);
  //echo "<BR> TransSelect AFTER : ".$_POST['TransListSelected'];
  displayDefault();
}

function displayChangeFix($fixSellRule, $transID){
  //$fixSellRule = $_POST['FixSellRule'];
  echo "<form action='Transactions.php?newSellRule=Yes' method='post'>";
  echo "<input type='text' name='fixedSellID' value='$fixSellRule' style='color:Gray' readonly ><label for='fixedSellID'>Current Fixed Sell ID: </label><br>";
  echo "<input type='text' name='transID' value='$transID' style='color:Gray' readonly ><label for='transID'>Transaction ID: </label><br>";
  echo "<input type='text' name='newSellID'><label for='newSellID'>New Fixed Sell ID: </label><br>";
  echo "<input type='submit' name='submit' value='Update' class='settingsformsubmit' tabindex='36'></form>";
}


function updateSellRule(){
  $newID = $_POST['newSellID'];
  $transID = $_POST['transID'];

  $conn = getSQLConn(rand(1,3));
  $current_date = date('Y-m-d H:i');
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `Transaction` SET `FixSellRule`= $newID WHERE `ID` = $transID";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}

function updateMerge($transID){

  $conn = getSQLConn(rand(1,3));
  $current_date = date('Y-m-d H:i');
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `Transaction` SET `ToMerge`= 1 WHERE `ID` = $transID";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}

function getCoinsfromSQL($userID){
    global $sql_option;
    $status = $_SESSION['TransListSelected'];
    if ($status == "1"){ $statusA = ''; $statusB = '';} else{$statusA = "`Status` = '" ;$statusB = "'";}
    $tempAry = [];
    // Create connection
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $sql = "SELECT `ID`,`Type`,`CoinID`,`CoinPrice`,`Amount`,`Status`,`OrderDate`,`CompletionDate`,`BittrexID`,`OrderNo`,`Symbol`,`BittrexRef`,`BittrexStatus`,`LiveCoinPrice`,`UserID`,`OrderNo`,`Symbol`
    ,`FixSellRule`,`ToMerge`
          FROM `TransactionsView` WHERE ".$statusA.$status.$statusB." and `UserID` = $userID order by `OrderDate` desc ";
    //print_r($sql);
    $result = $conn->query($sql);
    //$result = mysqli_query($link4, $query);
	   //mysqli_fetch_assoc($result);
    while ($row = mysqli_fetch_assoc($result)){
        $tempAry[] = Array($row['ID'],$row['Type'],$row['CoinID'],$row['CoinPrice'],$row['Amount'],$row['Status'],$row['OrderDate'],$row['CompletionDate'],$row['BittrexID'],$row['Symbol'],$row['BittrexRef'],
        $row['BittrexStatus'],$row['LiveCoinPrice'],$row['UserID'],$row['OrderNo'],$row['Symbol'],$row['FixSellRule'],$row['ToMerge']);
    }
    $conn->close();
    return $tempAry;
}

function displayOption($nText){
  if ($_SESSION['TransListSelected'] == $nText){
    Echo "<option  selected='selected' value='$nText'>$nText</option>";
  }elseif ($_SESSION['TransListSelected'] == "1" and $nText == "All" ){
    Echo "<option  selected='selected' value='$nText'>$nText</option>";
  }else{
    Echo "<option value='$nText'>$nText</option>";
  }

}

function displayDefault(){
  $coin = getCoinsfromSQL($_SESSION['ID']);
  $mobNum = $_SESSION['MobDisplay'];
  if ($_SESSION['isMobile']){
    $num = 2; $fontSize = "<i class='fas fa-bolt' style='font-size:60px;color:#D4EFDF'>"; $dformat ="YYYY-mm-dd";
  }else{
    $num = 8; $fontSize = "<i class='fas fa-bolt' style='font-size:32px;color:#D4EFDF'>"; $dformat ="YYYY-mm-dd H:i:s";
  }
  $arrlength = count($coin);
  echo "<html><h2>Transactions</h2>";
  echo "<form action='Transactions.php?dropdown=Yes' method='post'>";
  echo "<select name='transSelect' id='transSelect' class='enableTextBox'>";
    displayOption("Open");
    displayOption("Sold");
    displayOption("Pending");
    displayOption("All");
        echo "<input type='submit' name='submit' value='Update' class='settingsformsubmit' tabindex='36'>
     </form>";
  print_r("<Table><th>ID</th>");
  newEcho("<th>OrderNo</th>",$_SESSION['isMobile'],$mobNum);
  print_r("<th>Symbol</th><th>Amount</th><th>Cost</th>");
  newecho("<th>BaseCurrency</th>",$_SESSION['isMobile'],$mobNum);
  print_r("<th>Purchase Price</th>");
  newEcho("<th>TradeDate</th>",$_SESSION['isMobile'],$mobNum);
  print_r("<th>Status</th><th>FixSellRule</th>");
  print_r("<th>To Merge</th>");
  print_r("<th>Change Fixed Sell Rule</th>");
  print_r("<th>Merge</th>");
  print_r("<tr>");
  for($x = 0; $x < $arrlength; $x++) {
      $Id = $coin[$x][0]; $coinPrice = round($coin[$x][3],$num); $amount  = round($coin[$x][4],$num); $status  = $coin[$x][5];
      $orderDate = $coin[$x][6];
      $bittrexRef = $coin[$x][9];$orderNo = $coin[$x][14];$symbol = $coin[$x][15]; $fixSellRule = $coin[$x][16]; $toMerge = $coin[$x][17];
      $purchasePrice = round($amount*$coinPrice,$num);
      print_r("<td>$Id</td>");
      NewEcho("<td>$orderNo</td>",$_SESSION['isMobile'],$mobNum);
      print_r("<td>$symbol</td><td>$amount</td><td>$coinPrice</td>");
      newEcho("<td></td>",$_SESSION['isMobile'],$mobNum);
      print_r("<td>$purchasePrice</td>");
      newEcho("<td>$orderDate</td>",$_SESSION['isMobile'],$mobNum);
      print_r("<td>$status</td><td>$fixSellRule</td>");
      print_r("<td>$toMerge</td>");
      print_r("<td><a href='Transactions.php?changefixSell=Yes&SellRule=$Id&FixSellRule=$fixSellRule'>$fontSize</i></a></td>");
      print_r("<td><a href='Transactions.php?merge=Yes&SellRule=$Id'>$fontSize</i></a></td>");
      print_r("<tr>");
  }
  print_r("</Table>");
  if ($mobNum == 0){
    Echo "<a href='Transactions.php?override=Yes'>View Desktop Page</a>";
  } else{
    Echo "<a href='Transactions.php?noOverride=Yes'>View Mobile Page</a>";
  }
}



				displaySideColumn();
//include header template
require('layout/footer.php');
?>
