<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<?php require('includes/config.php');?>
<style>
<?php include 'style/style.css'; ?>
</style> <?php

//if not logged in redirect to login page
if(!$user->is_logged_in()){ header('Location: login.php'); exit(); }

//define page title
$title = 'CryptoBot';

//include header template
require('layout/header.php');
include_once ('/home/stevenj1979/SQLData.php');

//$globals['sql_Option'] = "`Status` = 'Open'";
//if(empty($globals['sql_Option'])){$globals['sql_Option']= "`Status` = 'Open'";}
if(empty($_SESSION['sql_option'])){
  $_SESSION['sql_option'] = "`Status` = 'Open'";
  $dropArray[] = Array("Open","Sold","Pending","All");
  //echo "<BR> $sql_option";
}else {
  //Print_r("I'm HERE!!!".$_POST['submit']);
  changeSelection();
}


function changeSelection(){
  global $sql_option;
  global $dropArray;
  if ($_POST['transSelect']=='Open'){
     $_SESSION['sql_option'] = "`Status` = 'Open'";
     $dropArray[] = Array("Open","Sold","All");
  }elseif ($_POST['transSelect']=='Sold'){
    $_SESSION['sql_option'] = "`Status` = 'Sold'";
    $dropArray[] = Array("Sold","Open","All");
  }elseif ($_POST['transSelect']=='Pending'){
    $_SESSION['sql_option'] = "`Status` = 'Pending'";
    $dropArray[] = Array("Sold","Open","All");
  }elseif ($_POST['transSelect']=='All'){
    $_SESSION['sql_option'] = "1";
    $dropArray[] = Array("All","Open","Sold");
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
          FROM `TransactionsView` WHERE ".$_SESSION['sql_option']." and `UserID` = $userID order by `OrderDate` desc ";
    print_r($sql);
    $result = $conn->query($sql);
    //$result = mysqli_query($link4, $query);
	   //mysqli_fetch_assoc($result);
    while ($row = mysqli_fetch_assoc($result)){
        $tempAry[] = Array($row['ID'],$row['Type'],$row['CoinID'],$row['CoinPrice'],$row['Amount'],$row['Status'],$row['OrderDate'],$row['CompletionDate'],$row['BittrexID'],$row['Symbol'],$row['BittrexRef'],
        $row['BittrexStatus'],$row['LiveCoinPrice'],$row['OrderNo'],$row['Symbol']);
    }
    $conn->close();
    return $tempAry;
}

date_default_timezone_set('Asia/Dubai');
if ($_SESSION['DisableUntil']<date("Y-m-d H:i:s", time())) { $liveCoinStatus = "Active";} else { $liveCoinStatus = "Disabled Until: ".$_SESSION['DisableUntil']." - ".date("Y-m-d H:i:s", time());}
?>


      <div class="header">
        <table><TH><table class="CompanyName"><td rowspan="2" class="CompanyName"><img src='Images/CBLogoSmall.png' width="40"></td><td class="CompanyName"><div class="Crypto">Crypto</Div><td><tr class="CompanyName">
            <td class="CompanyName"><Div class="Bot">Bot</Div></td></table></TH><TH>: Logged in as:</th><th> <i class="glyphicon glyphicon-user"></i>  <?php echo $_SESSION['username']." : ".$liveCoinStatus ?></th></Table><br>
        </div>
        <div class="topnav">
          <a href="Dashboard.php">Dashboard</a>
          <a href="Transactions.php" class="active">Transactions</a>
          <a href="Stats.php">Stats</a>
          <a href="BuyCoins.php">Buy Coins</a>
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

				$coin = getCoinsfromSQL($_SESSION['ID']);
        $Id = $coin[$x][0]; $coinPrice = $coin[$x][3]; $amount  = $coin[$x][4]; $status  = $coin[$x][5]; $orderDate = $coin[$x][6]; $bittrexRef = $coin[$x][9];
        $orderNo = $coin[$x][13];
				$arrlength = count($coin);
        echo "<html><h2>Transactions</h2>";
        echo "<form action='Transactions.php?dropdown=Yes' method='post'>";
        echo "<select name='transSelect' id='transSelect' class='enableTextBox'>
           <option value='Open'>Open</option>
          <option value='Sold'>Sold</option>
            <option value='Pending'>Pending</option></select>
            <input type='submit' name='submit' value='Update' class='settingsformsubmit' tabindex='36'>
           </form>";
				print_r("<Table><th>ID</th><th>OrderNo</th><th>Symbol</th><th>Amount</th><th>Cost</th><th>TradeDate</th><th>Status</th><tr>");
				for($x = 0; $x < $arrlength; $x++) {
				 print_r("<td>$Id</td><td>$orderNo</td><td>$bittrexRef</td><td>$amount</td><td>$coinPrice</td><td>$orderDate</td><td>$status</td><tr>");
				}
				print_r("</Table>");
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

<?php
//include header template
require('layout/footer.php');
?>
