<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
include_once ('/home/stevenj1979/SQLData.php');

setStyle($_SESSION['isMobile']);

if (isset($_GET['alert'])){
  echo "<BR> GET ALERT : ".$_GET['alert'];
  if ($_GET['alert'] == 1){
      //Edit Market Alerts
    //echo "<BR> Edit Alert".$_GET['edit'];
    displayForm($_GET['edit']);
  }elseif ($_GET['alert'] == 4){
      //Delete
      echo "<BR> Delete Alert".$_GET['edit'];
  }elseif ($_GET['alert'] == 2){
      //submit form
      echo "<BR> Submit Alert".$_GET['edit'];
  }
}else{
	showMain();
}

Function  getMarketAlertsUser($userID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `ID`, `Action`, `Price`, `UserName`,`Email` ,`LiveCoinPrice`,`Category`,`Hr1PctChange` ,`Hr24PctChange` ,`D7PctChange`,`ReocurringAlert`,`DateTimeSent`,`Minutes`,`LiveMarketPctChange`
  FROM `MarketAlertsView` WHERE `UserID` = $userID";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['ID'],$row['Action'],$row['Price'],$row['UserName'],$row['Email'],$row['LiveCoinPrice'],$row['Category'],$row['Hr1PctChange'] //7
      ,$row['Hr24PctChange'],$row['D7PctChange'],$row['ReocurringAlert'],$row['DateTimeSent'],$row['Minutes'],$row['LiveMarketPctChange']);
  }
  $conn->close();
  return $tempAry;
}

function displayForm($id){
  $userID = $_SESSION['ID'];
  $temp = getSpreadBetAlertsFormData($id);
  ?> <h1>Coin Alert</h1>
  <h2>Enter Price1</h2>
  <form action='MarketAlerts.php?alert=2' method='post'>
    <select name="priceSelect">
      <option value="Price" name='priceOpt'>Price</option>
      <option value="Pct Price in 1 Hour" name='pctPriceOpt'>Pct Price in 1 Hour</option>
      <option value="Pct Price in 24 Hours" name='pctPrice7DOpt'>Pct Price in 24 Hours</option>
      <option value="Pct Price in 7 Days" name='pctPrice24Opt'>Pct Price in 7 Days</option>
      <option value="Market Cap Pct Change" name='pctPriceMarkCapOpt'>Market Cap Pct Change</option>
      <option value="Live Price Pct Change" name='pctLivePriceOpt'>Live Price Pct Change</option>
    </select> <label for="priceSelect">Select Category</label><br>
    <select name="greaterThanSelect">
      <option value=">" name='greaterThanOpt'>></option>
      <option value="<" name='lessThanOpt'><</option>
    </select><label for="greaterThanSelect">Select Option</label><br>
    <input type="text" name="coinPriceAltTxt" value="<?php echo $cost; ?>"> <label for="coinPriceAltTxt">Coin Price: </label><br>
    <input type="checkbox" id="reocurringChk" name="reocurringChk" value="ReocurringAlert"><label for="reocurringChk">Reocurring Alert: </label><br>
    <input type="text" name="BaseCurTxt" value="<?php echo $baseCurrency; ?>" style='color:Gray' readonly ><label for="BaseCurTxt">BaseCurrency: </label><br>
    <input type="text" name="UserIDTxt" value="<?php echo $userID; ?>" style='color:Gray' readonly ><label for="UserIDTxt">UserID: </label><br>
    <input type='submit' name='submit' value='Set Alert' class='settingsformsubmit' tabindex='36'>

  </form>
  <?php
}

function getSpreadBetAlertsFormData($id){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `SpreadBetRuleID`,`Action`,`Price`,`Category`,`ReocurringAlert` FROM `SpreadBetAlertsView` WHERE `SpreadBetAlertRuleID` = $id ";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['SpreadBetRuleID'],$row['Action'],$row['Price'],$row['Category'],$row['ReocurringAlert']);
  }
  $conn->close();
  return $tempAry;
}

function DeleteAlert($id){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "DELETE FROM `SpreadBetAlerts` WHERE `SpreadBetAlertRuleID` = $id ";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("DeleteAlert: ".$sql, 'BuyCoin', 0);
}

Function showMain(){
  displayHeader(8);
  $userID = $_SESSION['ID'];
  if ($_SESSION['isMobile']){ $num = 2; $fontSize = "font-size:60px"; }else{$num = 8;$fontSize = "font-size:32px"; }
  NewEcho("<h2>Coin Alerts!</h2>",$_SESSION['isMobile'] ,2);
  echo "<h3><a href='CoinAlerts.php'>Coin Alerts</a> &nbsp > &nbsp <a href='MarketAlerts.php'>Market Alerts</a></h3>";
  NewEcho("<Table><th>Edit</th><th>&nbspID</th><TH>&nbspAction</th><TH>&nbspPrice</th>",$_SESSION['isMobile'] ,2);
  newEcho("<TH>&nbspUserName</th><TH>&nbspEmail</th>",$_SESSION['isMobile'] ,0);
  newEcho("<TH>&nbspliveCoinPrice</th><TH>&nbspCategory</th><th>Reocurring</th><TH>Price Pct Change</TH><TH>&nbspDelete Alert</th><tr>",$_SESSION['isMobile'] ,2);
  $coinAlerts = getMarketAlerts($userID);
  $newArrLength = Count($coinAlerts);
  for($x = 0; $x < $newArrLength; $x++) {
    $id = $coinAlerts[$x][13]; $action = $coinAlerts[$x][11];
    $price = round($coinAlerts[$x][14],$roundNum); $userName = $coinAlerts[$x][6];
    $email = $coinAlerts[$x][7];$liveCoinPrice= round($coinAlerts[$x][0],$roundNum); $category = $coinAlerts[$x][10];
    $reocurring = $coinAlerts[$x][9];  $marketPctChange = $coinAlerts[$x][15];
    NewEcho("<td><a href='MarketAlerts.php?alert=1&edit=".$id."'><span class='glyphicon glyphicon-pencil' style='$fontSize;'></span></a></td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td>$id</td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td>$action</td><td>$price</td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td>$userName</td><td>$email</td>",$_SESSION['isMobile'] ,0);
    NewEcho("<td>$liveCoinPrice</td><td>$category</td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td>$reocurring</td><td>$marketPctChange</td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td><a href='MarketAlerts.php?alert=4&iD=$id'><i class='glyphicon glyphicon-trash' style='$fontSize;color:#D4EFDF'></i></a></td>",$_SESSION['isMobile'] ,2);
 	   NewEcho("<TR>",$_SESSION['isMobile'] ,2);
 	 }
 	 Echo "</table>";
  	displaySideColumn();
	  //displayMiddleColumn();
	  //displayFarSideColumn();
	  //displayFooter();
	}


//include header template
require('layout/footer.php');
?>
