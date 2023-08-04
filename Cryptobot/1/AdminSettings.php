<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
</head>
<?php require('includes/config.php');
  include_once ('../../../../SQLData.php');
  include_once '../includes/newConfig.php';
?>
<html>
<style>
<?php include 'style/style.css'; ?>
</style>
<body>
<?php


//if not logged in redirect to login page
if(!$user->is_logged_in()){ header('Location: login.php'); exit(); }



//define page title
$title = 'CryptoBot';

//include header template
require('layout/header.php');

if(!empty($_GET['ExtendExpiryID'])){ displayExtend($_GET['ExtendExpiryID']); }
if(!empty($_GET['ToggleAdminID'])){ displayToggleAdmin($_GET['ToggleAdminID']); }

function displayExtend($id){
  //echo "The ID is $id ";
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `User` SET `ExpiryDate`=DATE_ADD(`ExpiryDate`, INTERVAL 6 Month) WHERE `ID` = $id";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();
  header('Location: AdminSettings.php');
}

function displayToggleAdmin($id){
  //echo "The ID is $id ";
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `User`
          SET  `AccountType` = IF(`AccountType` = 1, 0, 1)
          WHERE `id` = $id";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();
  header('Location: AdminSettings.php');
}

function getSubscription(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `ID`,`UserName`,`SubscriptionLength`,`DateSubmitted`,`TransactionID`,`Status`,`UserID` FROM `UserSubscription` WHERE `Status` = 'Open'";
	//echo $sql;
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['UserName'],$row['SubscriptionLength'],$row['DateSubmitted'],$row['TransactionID'],$row['Status'],$row['UserID']);
  }
  $conn->close();
  return $tempAry;
}

function getConfig($userID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `BTC`,`CoinSalePct`,`MarketCapBuyPct`,`MarketCapSellPct`,`VolumeBuyPct`,`VolumeSellPct`,`BuyOrdersPct`,`SellOrdersPct`, `minPctGain`,`BuyWithScore`, `Score`,
    `SellWithScore`, `SellScore`,`AutoDumpCoin`,`EnableDailyBTCLimit`,`DailyBTCLimit`,`EnableTotalBTCLimit`,`TotalBTCLimit` FROM `Config` WHERE `UserID` =  $userID";
  $result = $conn->query($sql);
  //print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['BTC'],$row['CoinSalePct'],$row['MarketCapBuyPct'],$row['MarketCapSellPct'],$row['VolumeBuyPct'],$row['VolumeSellPct'],$row['BuyOrdersPct'],$row['SellOrdersPct'],$row['minPctGain'],$row['BuyWithScore'],$row['Score'],
      $row['SellWithScore'],$row['SellScore'],$row['AutoDumpCoin'],$row['EnableDailyBTCLimit'],$row['DailyBTCLimit'],$row['EnableTotalBTCLimit'],$row['TotalBTCLimit']);
  }
  $conn->close();
  return $tempAry;
}

function getCoins(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `ID`,`Symbol`,`Name`,`BaseCurrency`,`BuyCoin`,`DoNotBuy` FROM `Coin` Order by `BuyCoin` DESC";
  $result = $conn->query($sql);
  //print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['Symbol'],$row['Name'],$row['BaseCurrency'],$row['BuyCoin'],$row['DoNotBuy']);
  }
  $conn->close();
  return $tempAry;
}

function getUsers(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `IDUs` as `ID`,`AccountType`,`UserName`,`Active`,`Email`,`ExpiryDate`,`DisableUntil` FROM `View12_UserConfig`";
  $result = $conn->query($sql);
  //print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['AccountType'],$row['UserName'],$row['Active'],$row['Email'],$row['ExpiryDate'],$row['DisableUntil']);
  }
  $conn->close();
  return $tempAry;
}

function startCoinTbl(){
  Echo "<TABLE>";
  Echo "<TH>Activate Coin</TH>";
  Echo "<TH>ID</TH>";
  Echo "<TH>Symbol</TH>";
  Echo "<TH>Name</TH>";
  Echo "<TH>BaseCurrency</TH>";
  Echo "<TH>BuyCoin</TH>";
  Echo "<TH>DoNotBuy</TH>";
  Echo "<TH>Delete Coin</TH>";
  Echo "<TR>";
}

function startUserTbl(){
  Echo "<TABLE>";
  Echo "<TH>ID</TH>";
  Echo "<TH>AccountType</TH>";
  Echo "<TH>UserName</TH>";
  Echo "<TH>Active</TH>";
  Echo "<TH>Email</TH>";
  Echo "<TH>ExpiryDate</TH>";
  Echo "<TH>DisableUntil</TH>";
  Echo "<TH>Extend Expiry</TH>";
  Echo "<TH>Toggle Admin</TH>";
  Echo "<TR>";
}

function coinTblRow($iD, $symbol, $name, $baseCurrency, $buyCoin, $doNotBuy){
  //Enable Icon
  echo "<td><a href='editCoinAdmin.php?activateID=$iD&activateBuyCoin=$buyCoin'><span class='fas fa-check' style='font-size:18px;'></span></a></td>";
  Echo "<TD>$iD</TD>";
  Echo "<TD>$symbol</TD>";
  Echo "<TD>$name</TD>";
  Echo "<TD>$baseCurrency</TD>";
  Echo "<TD><a href='AdminAction.php?buyCoin=$iD'>$buyCoin</a></TD>";
  Echo "<TD><a href='AdminAction.php?doNotBuy=$iD'>$doNotBuy</a></TD>";
  //Delete Icon
  echo "<td><a href='editCoinAdmin.php?deleteID=$iD'><span class='glyphicon glyphicon-trash' style='font-size:18px;'></span></a></td>";
  Echo "<TR>";
}

function userTblRow($iD, $accountType, $userName, $active, $email, $expiryDate, $disableUntil){
  //Enable Icon
  //echo "<td><a href='editCoinAdmin.php?activateID=$iD&activateBuyCoin=$buyCoin'><span class='fas fa-check' style='font-size:18px;'></span></a></td>";
  Echo "<TD>$iD</TD>";
  Echo "<TD>$accountType</TD>";
  Echo "<TD>$userName</TD>";
  Echo "<TD>$active</TD>";
  Echo "<TD>$email</TD>";
  Echo "<TD>$expiryDate</TD>";
  Echo "<TD>$disableUntil</TD>";
  //Delete Icon
  echo "<td><a href='AdminSettings.php?ExtendExpiryID=$iD'><span class='fas fa-arrow-alt-circle-up' style='font-size:18px;'></span></a></td>";
  echo "<td><a href='AdminSettings.php?ToggleAdminID=$iD'><span class='fab fa-adn' style='font-size:18px;'></span></a></td>";
  Echo "<TR>";
}

function endCoinTbl(){
  echo "</table>";
  Echo "<a href='editCoinAdmin.php?addNew=Yes'><span class='glyphicon glyphicon-plus' style='font-size:18px;'></span></a><br>";
    //Add New Coin icon
}

function endUserTbl(){
  echo "</table>";
  //Echo "<a href='editCoinAdmin.php?addNew=Yes'><span class='glyphicon glyphicon-plus' style='font-size:18px;'></span></a><br>";
    //Add New Coin icon
}

$userSub = getSubscription();
if (isset($userSub)){
    $userSubSize = count($userSub);
}else{
  $userSubSize = 0;
}

//$userSettings = getConfig($_SESSION['ID']);

//echo $userDetails[0][1];

    displayHeader(11) ?>
           Renew Subscription
           <table><th>Action</th><th>Username</th><th>SubscriptionLength</th><th>Date</th><th>TransactionID</th><tr>
           <?php
            for($x = 0; $x < $userSubSize; $x++) {
              $id = $userSub[$x][0]; $username = $userSub[$x][1]; $length = $userSub[$x][2]; $dateSubmittd = $userSub[$x][3]; $transRef = $userSub[$x][4]; $userID = $userSub[$x][6];
              //echo "<td><a href'AdminAction.php?id=".$userSub[$x][0]."&length=".$userSub[$x][2]."&username=".$userSub[$x][1]."'><i class='fas fa-arrow-alt-circle-right'></i></a></td>";
              echo "<td><a href='AdminAction.php?id=$id&length=$length&username=$username&UserID=$userID'><i class='fas fa-arrow-alt-circle-right' style='font-size:24px;color:#C0392B'></i></a></td>";
              echo "<td>".$userSub[$x][1]."</td>";
              echo "<td>".$userSub[$x][2]."</td>";
              echo "<td>".$userSub[$x][3]."</td>";
              echo "<td>".$userSub[$x][4]."</td>";
            }

           ?>
             </table>
             <hr>
             Promote to Admin
             <form action='AdminAction.php?promote=Yes' method='post'>
               Username <input type="text" name="User_Name"><br>
               <input type="submit" value="Submit">
             </form>
             <hr>
             Fix Transaction Amount
             <form action='AdminAction.php?fixTransaction=Yes' method='post'>
               Transaction ID <input type="text" name="Trans_ID"><br>
               New Amount <input type="text" name="New_Amount"><br>
               <input type="submit" value="Submit">
             </form>
             Live Coins<br>
             <?php
             $coinAry = getCoins();
             $coinArySize = 0;
             if (isset($coinAry)){ $coinArySize = count($coinAry);}
             startCoinTbl();
             for($x = 0; $x < $coinArySize; $x++) {
               coinTblRow($coinAry[$x][0],$coinAry[$x][1],$coinAry[$x][2],$coinAry[$x][3],$coinAry[$x][4],$coinAry[$x][5]);
             }
             endCoinTbl();
             ?>

             Users <br>
             <?php
             $userAry = getUsers();
             $userArySize = 0;
             if (isset($userAry)){$userArySize = count($userAry);}
             startUserTbl();
             for($x = 0; $x < $userArySize; $x++) {
               userTblRow($userAry[$x][0],$userAry[$x][1],$userAry[$x][2],$userAry[$x][3],$userAry[$x][4],$userAry[$x][5],$userAry[$x][6]);
             }
             endUserTbl();
             ?>

             <br>Quick Links<br>
             <a href="http://www.investment-tracker.net/Investment-Tracker/Cryptobot/NewBuySellCoins.php"> BuySellCoins </a> 10 mins
             <br>
             <a href="http://www.investment-tracker.net/Investment-Tracker/Cryptobot/testfile.php"> Test </a>
             <br>
            <!-- <a href="https://n1plcpnl0035.prod.ams1.secureserver.net:2083/logout/?locale=en"> cPanel </a> -->
             <a href="https://sxb1plzcpnl489902.prod.sxb1.secureserver.net:2083/logout/?locale=en"> cPanel </a>
             <br>
             <a href="http://www.investment-tracker.net/Investment-Tracker/Cryptobot/AutoUpdatePrice.php"> Auto Update Price </a> 15 mins
             <br>
             <a href="http://www.investment-tracker.net/Investment-Tracker/Cryptobot/AllCoinStatus.php"> All Coin Status </a> 5 Mins
             <br>
             <a href="http://www.investment-tracker.net/Investment-Tracker/Cryptobot/CoinAdmin.php"> Coin Admin </a> once per day,  20:45
             <br>
             <a href="http://www.investment-tracker.net/Investment-Tracker/Cryptobot/Dashboard.php"> Dashboard </a> 15 mins
             <br>
             <a href="http://www.investment-tracker.net/Investment-Tracker/Cryptobot/CryptoBotAuto.php"> Cryptobot Auto </a> 10 mins
             <br>
             <a href="http://www.investment-tracker.net/Investment-Tracker/Cryptobot/testScript.php"> Test Script </a>
             <br>
             <a href="http://www.investment-tracker.net/Investment-Tracker/Cryptobot/PriceProjection.php"> Price Projection </a> 15 mins
             <br>
             <a href="http://www.investment-tracker.net/Investment-Tracker/Cryptobot/MonthlyHighLow.php"> Monthly HighLow </a> 1st of every month
             <br>
             <a href="http://www.investment-tracker.net/Investment-Tracker/Cryptobot/CoinHourly.php"> Coin Hourly </a> 2 mins past every hour
             <br>
             <a href="http://www.investment-tracker.net/Investment-Tracker/Cryptobot/CoinMode.php"> Coin Mode </a> every 5 mins
             <br>
             <a href="http://www.investment-tracker.net/Investment-Tracker/Cryptobot/PctChangeProcess.php"> PCT Change Process </a> every 5 mins
             <br>
             <a href="http://www.investment-tracker.net/Investment-Tracker/Cryptobot/SpreadBetProcess.php"> SpreadBet Process </a> every 5 mins
             <br>
             <a href="http://www.investment-tracker.net/Investment-Tracker/Cryptobot/WeeklyScript.php"> Weekly Process </a> once per week, every saturday
             <br>
             <a href="http://www.investment-tracker.net/Investment-Tracker/Cryptobot/CoinSwap.php"> CoinSwap </a> every 12 mins
             <br>
             <a href="http://www.investment-tracker.net/Investment-Tracker/Cryptobot/CryptoBotDirector.php"> CryptoBotDirector </a> every 30 mins
             <br>

<?php
  displaySideColumn();

//include header template
//require('layout/footer.php');
?>
</body>
</html>
