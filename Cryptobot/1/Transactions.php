<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
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

//include header template
$title = 'CryptoBot';
$current_url = $_SERVER[ 'REQUEST_URI' ];
header( "Refresh: 120; URL=$current_url" );
//include header template
require($_SERVER['DOCUMENT_ROOT'].'/Investment-Tracker/Cryptobot/1/layout/header.php');
include_once ('/home/stevenj1979/SQLData.php');
$locationStr = "Location: /Investment-Tracker/Cryptobot/1/m/BuyCoins.php";
setStyle($_SESSION['isMobile']);

//$globals['sql_Option'] = "`Status` = 'Open'";
//if(empty($globals['sql_Option'])){$globals['sql_Option']= "`Status` = 'Open'";}
if(isset($_POST['dropdown'])){
  //Print_r("I'm HERE!!!".$_POST['submit']);
  changeSelection();
}


function changeSelection(){
  //global $sql_option;
  //global $dropArray;
  echo "<BR> TransSelect : ".$_POST['transSelect'];
  if ($_POST['transSelect']=='Open'){
     $_SESSION['TransListSelected'] = "`Status` = 'Open'";
     //$dropArray[] = Array("Open","Sold","All");
  }elseif ($_POST['transSelect']=='Sold'){
    $_SESSION['TransListSelected'] = "`Status` = 'Sold'";
    //$dropArray[] = Array("Sold","Open","All");
  }elseif ($_POST['transSelect']=='Pending'){
    $_SESSION['TransListSelected'] = "`Status` = 'Pending'";
    //$dropArray[] = Array("Sold","Open","All");
  }elseif ($_POST['transSelect']=='All'){
    $_SESSION['TransListSelected'] = "1";
    //$dropArray[] = Array("All","Open","Sold");
  }
  //print_r($globals['sql_Option']);
}

function getCoinsfromSQL($userID){
    global $sql_option;
    $tempAry = [];
    // Create connection
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $sql = "SELECT `ID`,`Type`,`CoinID`,`CoinPrice`,`Amount`,`Status`,`OrderDate`,`CompletionDate`,`BittrexID`,`OrderNo`,`Symbol`,`BittrexRef`,`BittrexStatus`,`LiveCoinPrice`,`UserID`,`OrderNo`,`Symbol`
    ,`FixSellRule`
          FROM `TransactionsView` WHERE ".$_SESSION['TransListSelected']." and `UserID` = $userID order by `OrderDate` desc ";
    print_r($sql);
    $result = $conn->query($sql);
    //$result = mysqli_query($link4, $query);
	   //mysqli_fetch_assoc($result);
    while ($row = mysqli_fetch_assoc($result)){
        $tempAry[] = Array($row['ID'],$row['Type'],$row['CoinID'],$row['CoinPrice'],$row['Amount'],$row['Status'],$row['OrderDate'],$row['CompletionDate'],$row['BittrexID'],$row['Symbol'],$row['BittrexRef'],
        $row['BittrexStatus'],$row['LiveCoinPrice'],$row['UserID'],$row['OrderNo'],$row['Symbol'],$row['FixSellRule']);
    }
    $conn->close();
    return $tempAry;
}

date_default_timezone_set('Asia/Dubai');
if ($_SESSION['DisableUntil']<date("Y-m-d H:i:s", time())) { $liveCoinStatus = "Active";} else { $liveCoinStatus = "Disabled Until: ".$_SESSION['DisableUntil']." - ".date("Y-m-d H:i:s", time());}
displayHeader(1);

				$coin = getCoinsfromSQL($_SESSION['ID']);

				$arrlength = count($coin);
        echo "<html><h2>Transactions</h2>";
        echo "<form action='Transactions.php?dropdown=Yes' method='post'>";
        echo "<select name='transSelect' id='transSelect' class='enableTextBox'>
           <option value='Open'>Open</option>
          <option value='Sold'>Sold</option>
            <option value='Pending'>Pending</option>
            <option value='All'>All</option></select>
            <input type='submit' name='submit' value='Update' class='settingsformsubmit' tabindex='36'>
           </form>";
				print_r("<Table><th>ID</th><th>OrderNo</th><th>Symbol</th><th>Amount</th><th>Cost</th><th>BaseCurrency</th><th>Purchase Price</th><th>TradeDate</th><th>Status</th><th>FixSellRule</th><tr>");
				for($x = 0; $x < $arrlength; $x++) {
            $Id = $coin[$x][0]; $coinPrice = $coin[$x][3]; $amount  = $coin[$x][4]; $status  = $coin[$x][5]; $orderDate = $coin[$x][6]; $bittrexRef = $coin[$x][9];
            $orderNo = $coin[$x][14];$symbol = $coin[$x][15]; $fixSellRule = $coin[$x][16];
            $purchasePrice = round($amount*$coinPrice,2);
				    print_r("<td>$Id</td><td>$orderNo</td><td>$symbol</td><td>$amount</td><td>$coinPrice</td><td></td><td>$purchasePrice</td><td>$orderDate</td><td>$status</td><td>$fixSellRule</td><tr>");
				}
				print_r("</Table>");
				displaySideColumn();
//include header template
require('layout/footer.php');
?>
