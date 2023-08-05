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
include_once ('../../../../SQLData.php');

setStyle($_SESSION['isMobile']);

if (isset($_GET['alert'])){
  echo "<BR> GET ALERT : ".$_GET['alert'];
  if ($_GET['alert'] == 1){
      //Edit Market Alerts
    //echo "<BR> Edit Alert".$_GET['edit'];
    displayForm($_GET['edit']);
  }elseif ($_GET['alert'] == 4){
      //Delete
      //echo "<BR> Delete Alert".$_GET['edit'];
      DeleteAlert($_GET['iD']);
  }elseif ($_GET['alert'] == 2){
      //submit form
      $temp = 0;
      echo "<BR> Submit Alert".$_GET['edit'];
      $category = $_POST['priceSelect'];
      $action = $_POST['greaterThanSelect'];
      $price = $_POST['coinPriceAltTxt'];
      $reocurring = $_POST['reocurringChk'];
      $marketAlertsRuleID = $_POST['MarketAlertRuleIDTxt'];
      if (isset($reocurring)){$temp = 1;}
      if ($action == "<"){ $actionTemp = "LessThan";}else{$actionTemp = "GreaterThan";}
      echo "<BR>Values : $category | $actionTemp | $price | $temp | $marketAlertsRuleID";
      updateFormDataToSQL($category, $actionTemp, $price, $temp, $marketAlertsRuleID);
      header('Location: MarketAlerts.php');
  }elseif ($_GET['alert'] == 5){
    displayAddNewAlert();
  }elseif ($_GET['alert'] == 6){
    $temp = 0;
    $category = $_POST['priceSelect'];
    $action = $_POST['greaterThanSelect'];
    $price = $_POST['coinPriceAltTxt'];
    $reocurring = $_POST['reocurringChk'];
    if (isset($reocurring)){$temp = 1;}
    if ($action == "<"){ $actionTemp = "LessThan";}else{$actionTemp = "GreaterThan";}
    echo "Ready to Add new to SQL : $category | $actionTemp | $price | $temp";
    addNewAlert($actionTemp, $price, $category, $temp);
    callUpdateMarketAlertsRuleID();
    header('Location: MarketAlerts.php');
  }
}else{
	showMain();
}

function updateFormDataToSQL($category, $action, $price, $reocurring, $marketAlertsRuleID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `MarketAlerts` SET `Action`= '$action',`Price`= $price,`Category`= '$category',`ReocurringAlert`= $reocurring WHERE `MarketAlertRuleID` = $marketAlertsRuleID ";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateFormDataToSQL: ".$sql, 'BuyCoin', 0);
}

function callUpdateMarketAlertsRuleID(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "Call updateMarketAlertRuleID();";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("callUpdateMarketAlertsRuleID: ".$sql, 'BuyCoin', 0);
}

Function  getMarketAlertsUser($userID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `ID`, `Action`, `Price`, `UserName`,`Email` ,`Category`,`ReocurringAlert`,`DateTimeSent`,`Minutes`
  FROM `MarketAlertsView` WHERE `UserID` = $userID";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['ID'],$row['Action'],$row['Price'],$row['UserName'],$row['Email'],$row['Category'],$row['ReocurringAlert'],$row['DateTimeSent'],$row['Minutes']);
  }
  $conn->close();
  return $tempAry;
}

function displayForm($id){
  displayHeader(8);
  $userID = $_SESSION['ID'];
  $selected = "";$checked = "";
  if ($_SESSION['isMobile']){ $num = 2; $fontSize = "font-size:60px"; }else{$num = 8;$fontSize = "font-size:32px"; }
  $selectArray = Array("Price","Pct Price in 1 Hour","Pct Price in 24 Hours","Pct Price in 7 Days","Market Cap Pct Change","Live Price Pct Change");
  $selectArraySize = newCount($selectArray);
  $temp = getMarketAlertsFormData($id);
  $category = $temp[0][3]; $price = $temp[0][2]; $action = $temp[0][1]; $reoccuring = $temp[0][4];
  ?> <h1>Market Alerts</h1>
  <h2>Enter Price1</h2>
  <form action='MarketAlerts.php?alert=2' method='post'>
    <select name="priceSelect"><?php
      for ($r=0; $r<$selectArraySize; $r++){
        //echo "<BR> TEST1: ".$selectArray[$r]. " | TEST2: $category";
          if ($selectArray[$r] == $category) { $selected = " selected"; }
          Echo "<option value='".$selectArray[$r]."' name='".str_replace(" ","",$selectArray[$r])."Opt' $selected>".$selectArray[$r]."</option>";
          $selected = "";
      }
      //<option value="Price" name='priceOpt'>Price</option>
      //<option value="Pct Price in 1 Hour" name='pctPriceOpt'>Pct Price in 1 Hour</option>
      //<option value="Pct Price in 24 Hours" name='pctPrice7DOpt'>Pct Price in 24 Hours</option>
      //<option value="Pct Price in 7 Days" name='pctPrice24Opt'>Pct Price in 7 Days</option>
      //<option value="Market Cap Pct Change" name='pctPriceMarkCapOpt'>Market Cap Pct Change</option>
      //<option value="Live Price Pct Change" name='pctLivePriceOpt'>Live Price Pct Change</option>?>
    </select> <label for="priceSelect">Select Category</label><br>
    <select name="greaterThanSelect"> <?php
    if ($action == "LessThan"){$lessThanSelect = "SELECTED";}else{$greaterThanSelect = "SELECTED";}
      ?>
      <option value=">" name='greaterThanOpt'<?php echo $greaterThanSelect;?> >></option>
      <option value="<" name='lessThanOpt'<?php echo $lessThanSelect; ?> ><</option>
    </select><label for="greaterThanSelect">Select Option</label><br>
    <input type="text" name="coinPriceAltTxt" value="<?php echo $price; ?>"> <label for="coinPriceAltTxt">Coin Price: </label><br>
      <?php if ($reoccuring == 1){$checked = " checked";}?>
    <input type="checkbox" id="reocurringChk" name="reocurringChk" value="ReocurringAlert" <?php echo $checked; ?>><label for="reocurringChk">Reocurring Alert: </label><br>
    <input type="text" name="UserIDTxt" value="<?php echo $userID; ?>" style='color:Gray' readonly ><label for="UserIDTxt">UserID: </label><br>
    <input type="text" name="MarketAlertRuleIDTxt" value="<?php echo $id; ?>" style='color:Gray' readonly ><label for="MarketAlertRuleIDTxt">Market Alert Rule ID: </label><br>
    <input type='submit' name='submit' value='Set Alert' class='settingsformsubmit' tabindex='36'>

  </form>
  <?php
  displaySideColumn();
}

function displayAddNewAlert(){
  displayHeader(8);
  $userID = $_SESSION['ID'];
  if ($_SESSION['isMobile']){ $num = 2; $fontSize = "font-size:60px"; }else{$num = 8;$fontSize = "font-size:32px"; }
  $selectArray = Array("Price","Pct Price in 1 Hour","Pct Price in 24 Hours","Pct Price in 7 Days","Market Cap Pct Change","Live Price Pct Change");
  $selectArraySize = newCount($selectArray);
  //$temp = getSpreadBetAlertsFormData($id);
  $category = ""; $price = ""; $action = ""; $reoccuring = 0;
  ?> <h1>Market Alerts Alerts</h1>
  <h2>Enter Price1</h2>
  <form action='MarketAlerts.php?alert=6' method='post'>
    <select name="priceSelect"><?php
      for ($r=0; $r<$selectArraySize; $r++){
        //echo "<BR> TEST1: ".$selectArray[$r]. " | TEST2: $category";
          if ($selectArray[$r] == $category) { $selected = " selected"; }
          Echo "<option value='".$selectArray[$r]."' name='".str_replace(" ","",$selectArray[$r])."Opt' $selected>".$selectArray[$r]."</option>";
          $selected = "";
      }
      //<option value="Price" name='priceOpt'>Price</option>
      //<option value="Pct Price in 1 Hour" name='pctPriceOpt'>Pct Price in 1 Hour</option>
      //<option value="Pct Price in 24 Hours" name='pctPrice7DOpt'>Pct Price in 24 Hours</option>
      //<option value="Pct Price in 7 Days" name='pctPrice24Opt'>Pct Price in 7 Days</option>
      //<option value="Market Cap Pct Change" name='pctPriceMarkCapOpt'>Market Cap Pct Change</option>
      //<option value="Live Price Pct Change" name='pctLivePriceOpt'>Live Price Pct Change</option>?>
    </select> <label for="priceSelect">Select Category</label><br>
    <select name="greaterThanSelect"> <?php
    if ($action == "LessThan"){$lessThanSelect = "SELECTED";}else{$greaterThanSelect = "SELECTED";}
      ?>
      <option value=">" name='greaterThanOpt'<?php echo $greaterThanSelect;?> >></option>
      <option value="<" name='lessThanOpt'<?php echo $lessThanSelect; ?> ><</option>
    </select><label for="greaterThanSelect">Select Option</label><br>
    <input type="text" name="coinPriceAltTxt" value="<?php echo $price; ?>"> <label for="coinPriceAltTxt">Coin Price: </label><br>
      <?php if ($reoccuring == 1){$checked = " checked";}?>
    <input type="checkbox" id="reocurringChk" name="reocurringChk" value="ReocurringAlert" <?php echo $checked; ?>><label for="reocurringChk">Reocurring Alert: </label><br>
    <input type="text" name="UserIDTxt" value="<?php echo $userID; ?>" style='color:Gray' readonly ><label for="UserIDTxt">UserID: </label><br>
    <input type="text" name="MarketAlertRuleIDTxt" value="<?php echo $id; ?>" style='color:Gray' readonly ><label for="MarketAlertRuleIDTxt">Market Alert Rule ID: </label><br>
    <input type='submit' name='submit' value='Set Alert' class='settingsformsubmit' tabindex='36'>

  </form>
  <?php
  displaySideColumn();
}

function addNewAlert($action, $price, $category, $reoccuring){
  $userID = $_SESSION['ID'];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "INSERT INTO `MarketAlerts`(`Action`, `Price`, `UserID`, `Category`, `ReocurringAlert`, `MarketAlertRuleID`)
  VALUES ('$action', $price, $userID, '$category', $reoccuring, (SELECT `ID` FROM `MarketAlertsRule`))";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("addNewAlert: ".$sql, 'BuyCoin', 0);
}

function getMarketAlertsFormData($id){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `MarketAlertRuleID`, `Action`, `Price`,`Category`, `ReocurringAlert` FROM `MarketAlertsView` WHERE `MarketAlertRuleID` = $id ";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['MarketRuleID'],$row['Action'],$row['Price'],$row['Category'],$row['ReocurringAlert']);
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
  $sql = "DELETE FROM `MarketAlerts` WHERE `MarketAlertRuleID` = $id ";
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
  if ($_SESSION['isMobile']){ $num = 3; $fontSize = "font-size:60px"; }else{$num = 8;$fontSize = "font-size:32px"; }
  NewEcho("<h2>Coin Alerts!</h2>",$_SESSION['isMobile'] ,2);
  echo "<h3><a href='CoinAlerts.php'>Coin Alerts</a> &nbsp > &nbsp <a href='MarketAlerts.php'>Market Alerts</a>&nbsp > &nbsp <a href='SpreadBetAlerts.php'>SpreadBet Alerts</a></h3>";
  NewEcho("<Table><th>Edit</th><th>&nbspID</th><TH>&nbspAction</th><TH>&nbspPrice</th>",$_SESSION['isMobile'] ,2);
  newEcho("<TH>&nbspUserName</th><TH>&nbspEmail</th>",$_SESSION['isMobile'] ,0);
  newEcho("<TH>&nbspliveCoinPrice</th><TH>&nbspCategory</th><th>Reocurring</th><TH>Price Pct Change</TH><TH>&nbspDelete Alert</th><tr>",$_SESSION['isMobile'] ,2);
  $coinAlerts = getMarketAlerts($userID);
  $newArrLength = newCount($coinAlerts);
  $marketStats = getMarketstats();
  //echo "<BR> Array Len : $newArrLength";
  for($x = 0; $x < $newArrLength; $x++) {
    $id = $coinAlerts[$x][8]; $action = $coinAlerts[$x][6];
    $price = $coinAlerts[$x][9]; $userName = $coinAlerts[$x][1];
    $email = $coinAlerts[$x][2];$liveCoinPrice= $marketStats[0][0]; $category = $coinAlerts[$x][5];
    $reocurring = $coinAlerts[$x][4];  $marketPctChange = $marketStats[0][4];
    NewEcho("<td><a href='MarketAlerts.php?alert=1&edit=".$id."'><span class='glyphicon glyphicon-pencil' style='$fontSize;'></span></a></td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td>$id</td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td>$action</td><td>$price</td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td>$userName</td><td>$email</td>",$_SESSION['isMobile'] ,0);
    NewEcho("<td>$liveCoinPrice</td><td>$category</td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td>$reocurring</td><td>$marketPctChange</td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td><a href='MarketAlerts.php?alert=4&iD=$id'><i class='glyphicon glyphicon-trash' style='$fontSize;color:#D4EFDF'></i></a></td>",$_SESSION['isMobile'] ,2);
 	   NewEcho("<TR>",$_SESSION['isMobile'] ,2);
 	 }
 	 Echo "</table><br><a href='MarketAlerts.php?alert=5'><span class='glyphicon glyphicon-plus' style='font-size:48px;'></span></a>";
  	displaySideColumn();
	  //displayMiddleColumn();
	  //displayFarSideColumn();
	  //displayFooter();
	}


//include header template
require('layout/footer.php');
?>
