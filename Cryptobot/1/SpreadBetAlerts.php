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
if ($_SESSION['isMobile'] == True){ $roundNum = 2;}else {$roundNum = 8;}
//define page title
$title = 'CryptoBot';
$current_url = $_SERVER[ 'REQUEST_URI' ];
header( "Refresh: 120; URL=$current_url" );
//include header template
require($_SERVER['DOCUMENT_ROOT'].'/Investment-Tracker/Cryptobot/1/layout/header.php');
include_once ('../../../../SQLData.php');

setStyle($_SESSION['isMobile']);

if (isset($_GET['alert'])){
  //echo "<BR> GET ALERT : ".$_GET['alert'];
  if ($_GET['alert'] == 1){
      //Edit Market Alerts
    //echo "<BR> Edit Alert".$_GET['edit'];
    displayForm($_GET['edit']);
  }elseif ($_GET['alert'] == 4){
      //Delete
      //echo "<BR> Delete Alert".$_GET['edit'];
      DeleteAlert($_GET['iD']);
      header('Location: SpreadBetAlerts.php');
  }elseif ($_GET['alert'] == 2){
      //EDIT Alert

      $temp = 0;
      echo "<BR> Submit Alert".$_GET['edit'];
      $category = $_POST['priceSelect'];
      $action = $_POST['greaterThanSelect'];
      $price = $_POST['coinPriceAltTxt'];
      $reocurring = $_POST['reocurringChk'];
      $marketAlertsRuleID = $_POST['MarketAlertRuleIDTxt'];
      $allRuleCheck = $_POST['allCoinChk'];
      $spreadBetRuleID = $_POST['SpreadBetRuleIDTxt'];
      if (isset($reocurring)){$temp = 1;}
      if ($action == "<"){ $actionTemp = "LessThan";}else{$actionTemp = "GreaterThan";}


      echo "<BR>Values : $category | $actionTemp | $price | $temp | $marketAlertsRuleID | $tempSpreadBetID";
      updateFormDataToSQL($category, $actionTemp, $price, $temp, $marketAlertsRuleID, $spreadBetRuleID);
      header('Location: SpreadBetAlerts.php');
  }elseif ($_GET['alert'] == 5){
      displayAddNewAlert($_GET['SBID']);
  }elseif ($_GET['alert'] == 6){
    //ADD NEW Alert - Submit
    $userID = $_SESSION['ID'];
    $temp = 0;
    $category = $_POST['priceSelect'];
    $action = $_POST['greaterThanSelect'];
    $price = $_POST['coinPriceAltTxt'];
    $reocurring = $_POST['reocurringChk'];
    $spreadBetRuleID = $_POST['SpreadBetRuleIDTxt'];
    $allRuleCheck = $_POST['allCoinChk'];
    if (isset($allRuleCheck)){ $allRules = getAllRules($userID); $allRulesSize = newCount($allRules);}else{$allRulesSize = 1;}
    if (isset($reocurring)){$temp = 1;}
    if ($action == "<"){ $actionTemp = "LessThan";}else{$actionTemp = "GreaterThan";}
    for ($o=0; $o<$allRulesSize; $o++){
      if (isset($allRuleCheck)){$tempSpreadBetID = $allRules[$o][0];}else{ $tempSpreadBetID = $spreadBetRuleID;}
      Echo "<BR> addNewAlert($category,$actionTemp,$price,$temp,$tempSpreadBetID); | $allRulesSize";
      addNewAlert($category,$actionTemp,$price,$temp,$tempSpreadBetID);
    }
    newSpreadBetRuleID();
    header('Location: SpreadBetAlerts.php');
  }
}else{
	showMain();
}

function getAllRules($userID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `ID` FROM `SpreadBetRules` WHERE `UserID` = $userID";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['ID']);
  }
  $conn->close();
  return $tempAry;
}

function updateFormDataToSQL($category, $action, $price, $reocurring, $marketAlertsRuleID, $spreadBetRuleID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `SpreadBetAlerts` SET `Action`= '$action',`Price`= $price,`Category`= '$category',`ReocurringAlert`= $reocurring, `SpreadBetRuleID` = $spreadBetRuleID
  WHERE `MarketAlertRuleID` = $marketAlertsRuleID ";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("updateFormDataToSQL: ".$sql, 'BuyCoin', 0);
}

Function  getMarketAlertsUser($userID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `SpreadBetRuleID`, `Action`, `Price`, `UserName`,`Email` ,`LiveCoinPrice`,`Category`,`Live1HrChange` ,`Live24HrChange` ,`Live7DChange`,`ReocurringAlert`,`DateTimeSent`,`Minutes`,`LivePricePct`
  FROM `SpreadBetAlertsView` WHERE `UserID` =  $userID";
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
  displayHeader(8);
  $userID = $_SESSION['ID'];
  $selected = "";$checked = "";
  if ($_SESSION['isMobile']){ $num = 2; $fontSize = "font-size:60px"; }else{$num = 8;$fontSize = "font-size:32px"; }
  $selectArray = Array("Price","Pct Price in 1 Hour","Pct Price in 24 Hours","Pct Price in 7 Days","Market Cap Pct Change","Live Price Pct Change");
  $selectArraySize = newCount($selectArray);
  $temp = getSpreadBetAlertsFormData($id);
  $category = $temp[0][3]; $price = $temp[0][2]; $action = $temp[0][1]; $reoccuring = $temp[0][4]; $spreadBetRuleID = $temp[0][5];
  ?> <h1>SpreadBet Alerts</h1>
  <h2>Enter Price1</h2>
  <form action='SpreadBetAlerts.php?alert=2' method='post'>
    <input type="checkbox" id="allCoinChk" name="allCoinChk" value="allCoinChk"><label for="allCoinChk">All Rules: </label><br>
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
    <input type="checkbox" id="allCoinChk" name="allCoinChk" value="allCoinChk"><label for="allCoinChk">All Rules: </label><br>
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
      <input type="text" name="SpreadBetRuleIDTxt" value="<?php echo $spreadBetRuleID; ?>" style='color:Gray' readonly ><label for="SpreadBetRuleIDTxt">SpreadBet Rule ID: </label><br>
    <input type='submit' name='submit' value='Set Alert' class='settingsformsubmit' tabindex='36'>

  </form>
  <?php
  displaySideColumn();
}

function displayAddNewAlert($spreadBetRuleID){
  displayHeader(8);
  $userID = $_SESSION['ID'];
  if ($_SESSION['isMobile']){ $num = 2; $fontSize = "font-size:60px"; }else{$num = 8;$fontSize = "font-size:32px"; }
  $selectArray = Array("Price","Pct Price in 1 Hour","Pct Price in 24 Hours","Pct Price in 7 Days","Market Cap Pct Change","Live Price Pct Change");
  $selectArraySize = newCount($selectArray);
  //$temp = getSpreadBetAlertsFormData($id);
  $category = ""; $price = ""; $action = ""; $reoccuring = 0;
  ?> <h1>SpreadBet Alerts</h1>
  <h2>Enter Price1</h2>
  <form action='SpreadBetAlerts.php?alert=6' method='post'>
    <input type="checkbox" id="allCoinChk" name="allCoinChk" value="allCoinChk"><label for="allCoinChk">All Rules: </label><br>
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
    <input type="text" name="SpreadBetRuleIDTxt" value="<?php echo $spreadBetRuleID; ?>" style='color:Gray' readonly ><label for="SpreadBetRuleIDTxt">SpreadBet Rule ID: </label><br>
    <input type='submit' name='submit' value='Set Alert' class='settingsformsubmit' tabindex='36'>

  </form>
  <?php
  displaySideColumn();
}

function addNewAlert($category,$action,$price,$reocurring,$SpreadBetRuleID){
  $userID = $_SESSION['ID'];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "INSERT INTO `SpreadBetAlerts`(`SpreadBetRuleID`, `Action`, `Price`, `UserID`, `Category`, `ReocurringAlert`, `SpreadBetAlertRuleID`)
  VALUES ($SpreadBetRuleID,'$action',$price,$userID,'$category',$reocurring,(SELECT `ID` FROM `SpreadBetAlertsRule`))";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("addNewAlert: ".$sql, 'BuyCoin', 0);
}

function getSpreadBetAlertsFormData($id){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $sql = "SELECT `SpreadBetAlertRuleID`, `Action`, `Price`,`Category`, `ReocurringAlert`, `SpreadBetRuleID` FROM `SpreadBetAlertsView` WHERE `SpreadBetAlertRuleID` = $id ";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['MarketRuleID'],$row['Action'],$row['Price'],$row['Category'],$row['ReocurringAlert'],$row['SpreadBetRuleID']);
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

function newSpreadBetRuleID(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "Call updateSpreadBetRuleID();";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("newSpreadBetRuleID: ".$sql, 'BuyCoin', 0);
}

Function showMain(){
  displayHeader(8);
  $userID = $_SESSION['ID'];
  if ($_SESSION['isMobile']){ $num = 3; $fontSize = "font-size:60px"; }else{$num = 8;$fontSize = "font-size:32px"; }
  NewEcho("<h2>SpreadBet Alerts!</h2>",$_SESSION['isMobile'] ,2);
  echo "<h3><a href='CoinAlerts.php'>Coin Alerts</a> &nbsp > &nbsp <a href='MarketAlerts.php'>Market Alerts</a>&nbsp > &nbsp <a href='SpreadBetAlerts.php'>SpreadBet Alerts</a></h3>";
  NewEcho("<Table><th>Edit</th><th>&nbspSpreadBetRuleID</th><TH>&nbspAction</th><TH>&nbspPrice</th>",$_SESSION['isMobile'] ,2);
  newEcho("<TH>&nbspUserName</th><TH>&nbspEmail</th>",$_SESSION['isMobile'] ,2);
  newEcho("<TH>&nbspliveCoinPrice</th><TH>&nbspCategory</th><th>Reocurring</th><TH>Price Pct Change</TH><TH>&nbspDelete Alert</th><tr>",$_SESSION['isMobile'] ,2);
  $coinAlerts = getSpreadBetAlerts($userID);
  $newArrLength = newCount($coinAlerts);
  for($x = 0; $x < $newArrLength; $x++) {
    $id = $coinAlerts[$x][13]; $action = $coinAlerts[$x][11];
    $price = $coinAlerts[$x][14]; $userName = $coinAlerts[$x][6];
    $user_email = $coinAlerts[$x][7]; $liveCoinPrice= $coinAlerts[$x][0]; $category = $coinAlerts[$x][10];
    $reocurring = $coinAlerts[$x][9];  $marketPctChange = $coinAlerts[$x][15]; $spreadBetRuleID = $coinAlerts[$x][16];
    NewEcho("<td><a href='SpreadBetAlerts.php?alert=1&edit=".$id."'><span class='glyphicon glyphicon-pencil' style='$fontSize;'></span></a></td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td>$spreadBetRuleID</td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td>$action</td><td>$price</td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td>$userName</td><td>$user_email</td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td>$liveCoinPrice</td><td>$category</td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td>$reocurring</td><td>$marketPctChange</td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td><a href='SpreadBetAlerts.php?alert=4&iD=$id'><i class='glyphicon glyphicon-trash' style='$fontSize;color:#D4EFDF'></i></a></td>",$_SESSION['isMobile'] ,2);
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
