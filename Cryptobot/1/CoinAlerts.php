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
$current_url = $_SERVER[ 'REQUEST_URI' ];
header( "Refresh: 120; URL=$current_url" );
$showmain = True;
//include header template
require('layout/header.php');
include_once ('../../../../SQLData.php');
$showmain = True;



if ($_SESSION['isMobile'] == True){ $roundNum = 2;}else {$roundNum = 8;}

if ($_GET['alert'] == 0 && isset($_GET['alert'])){
  $showmain = false;
  $userID = $_SESSION['ID'];$selected = "";$checked = "";
  $selectArray = Array("Price","Pct Price in 1 Hour","Pct Price in 24 Hours","Pct Price in 7 Days","Market Cap Pct Change","Live Price Pct Change");
  $selectArraySize = newCount($selectArray);
  echo "<BR> Alert : ".$_GET['alert'];
  $coin = $_GET['coinAlt']; $cost = number_format($_GET['coinPrice'],8); $baseCurrency = $_GET['baseCurrency']; $coinID = $_GET['coinID'];
  $temp = getCoinAlertsFormData($id);
  $category = $temp[0][3]; $price = $temp[0][2]; $action = $temp[0][1]; $reoccuring = $temp[0][4];
  displayHeader(8);
  ?> <h1>Coin Alert</h1>
  <h2>Enter Price1</h2>
  <form action='CoinAlerts.php?alert=2' method='post'>
    <input type="text" name="coinAltTxt" value="<?php echo $coin; ?>"><label for="coinAltTxt">Coin: </label><br>
    <input type="checkbox" id="allCoinChk" name="allCoinChk" value="allCoinChk"><label for="allCoinChk">All Coins: </label><br>
    <select name="priceSelect">
    <option value="Price" name='priceOpt'>Price</option>
    <option value="Pct Price in 1 Hour" name='pctPriceOpt'>Pct Price in 1 Hour</option>
    <option value="Pct Price in 24 Hours" name='pctPrice24Opt'>Pct Price in 24 Hours</option>
    <option value="Pct Price in 7 Days" name='pctPrice7DOpt'>Pct Price in 7 Days</option>
    <option value="Market Cap Pct Change" name='pctPriceMarkCapOpt'>Market Cap Pct Change</option>
    <option value="Live Price Pct Change" name='pctLivePriceOpt'>Live Price Pct Change</option>
    </select> <label for="priceSelect">Select Category</label><br>
    <select name="greaterThanSelect">
      <option value=">" name='greaterThanOpt'>Greater Than</option>
      <option value="<" name='lessThanOpt'>Less Than</option>
    </select><label for="greaterThanSelect">Select Option</label><br>
    <input type="text" name="coinPriceAltTxt" value="<?php echo $cost; ?>"> <label for="coinPriceAltTxt">Coin Price: </label><br>
    <input type="checkbox" id="reocurringChk" name="reocurringChk" value="ReocurringAlert"><label for="reocurringChk">Reocurring Alert: </label><br>
    <input type="text" name="BaseCurTxt" value="<?php echo $baseCurrency; ?>" style='color:Gray' readonly ><label for="BaseCurTxt">BaseCurrency: </label><br>
    <input type="text" name="CoinIDTxt" value="<?php echo $coinID; ?>" style='color:Gray' readonly ><label for="CoinIDTxt">CoinID: </label><br>
    <input type="text" name="UserIDTxt" value="<?php echo $userID; ?>" style='color:Gray' readonly ><label for="UserIDTxt">UserID: </label><br>
    <input type='submit' name='submit' value='Set Alert' class='settingsformsubmit' tabindex='36'>

  </form>
  <?php
  displaySideColumn();
}elseif ($_GET['alert'] == 1 && isset($_GET['alert'])){
  $showmain = false;
  $userID = $_SESSION['ID'];
  $selected = "";$checked = "";
    //echo "<BR> Edit : ".$_GET['edit'];
    $coinAlertRuleID = $_GET['edit'];
    $alertDetails = getCoinAlertsbyID($coinAlertRuleID);
    $coin = $alertDetails[0][4]; $cost = $alertDetails[0][3]; $baseCurrency = "USDT"; $coinID = $alertDetails[0][1];
    $category = $alertDetails[0][8]; $price = $alertDetails[0][3]; $action = $alertDetails[0][2]; $reoccuring = $alertDetails[0][12];
    //echo "<BR> Coin $coin cost $cost CoinID $coinID";
    $selectArray = Array("Price","Pct Price in 1 Hour","Pct Price in 24 Hours","Pct Price in 7 Days","Market Cap Pct Change","Live Price Pct Change");
    $selectArraySize = newCount($selectArray);
    //$temp = getCoinAlertsFormData($coinAlertRuleID);

  displayHeader(8);
  ?> <h1>Coin Alert</h1>
  <h2>Enter Price2</h2>
  <form action='CoinAlerts.php?alert=3' method='post'>
    <input type="text" name="coinAltTxt" value="<?php echo $coin; ?>" style='color:Gray' readonly ><label for="coinAltTxt">Coin: </label><br>
    <select name="priceSelect">
      <?php
        for ($r=0; $r<$selectArraySize; $r++){
          echo "<BR> TEST1: ".$selectArray[$r]. " | TEST2: $category";
            if ($selectArray[$r] == $category) { $selected = " selected"; }
            Echo "<option value='".$selectArray[$r]."' name='".str_replace(" ","",$selectArray[$r])."Opt' $selected>".$selectArray[$r]."</option>";
            $selected = "";
        }?>
    </select> <label for="priceSelect">Select Category</label><br>
    <select name="greaterThanSelect"><?php
    if ($action == "LessThan"){$lessThanSelect = "SELECTED";}else{$greaterThanSelect = "SELECTED";}
      ?>
      <option value=">" name='greaterThanOpt'<?php echo $greaterThanSelect;?> >></option>
      <option value="<" name='lessThanOpt'<?php echo $lessThanSelect; ?> ><</option>
    </select><label for="greaterThanSelect">Select Option</label><br>
    <input type="text" name="coinPriceAltTxt" value="<?php echo $cost; ?>"> <label for="coinPriceAltTxt">Coin Price: </label><br>
      <?php if ($reoccuring == 1){$checked = " checked";}?>
    <input type="checkbox" id="reocurringChk" name="reocurringChk" value="ReocurringAlert" <?php echo $checked; ?>><label for="reocurringChk">Reocurring Alert: </label><br>
    <input type="text" name="BaseCurTxt" value="<?php echo $baseCurrency; ?>" style='color:Gray' readonly ><label for="BaseCurTxt">BaseCurrency: </label><br>
    <input type="text" name="CoinIDTxt" value="<?php echo $coinID; ?>" style='color:Gray' readonly ><label for="CoinIDTxt">CoinID: </label><br>
    <input type="text" name="UserIDTxt" value="<?php echo $userID; ?>" style='color:Gray' readonly ><label for="UserIDTxt">UserID: </label><br>
      <?php  $GLOBALS['CoinEdit'] = True;?>
    <input type="text" name="CoinAlertRuleID" value="<?php echo $coinAlertRuleID; ?>" style='color:Gray' readonly ><label for="CoinAlertRuleID">CoinAlerRuleID: </label><br>
    <input type='submit' name='submit' value='Set Alert' class='settingsformsubmit' tabindex='36'>

  </form>
  <?php
  displaySideColumn();
}elseif ($_GET['alert'] == 2 && isset($_GET['alert'])){
  Echo "<BR> Add New Alert ";
  $showmain = false;
  date_default_timezone_set('Asia/Dubai');
  $date = date("Y-m-d H:i:s", time());
  $userID = $_SESSION['ID'];
  //$coin = $_POST['coinAltTxt']; $baseCurrency = $_POST['BaseCurTxt'];
  $coinID = $_POST['CoinIDTxt']; $userID = $_POST['UserIDTxt'];
  $salePrice = $_POST['coinPriceAltTxt']; $category = $_POST['priceSelect'];
  if(isset($_POST['reocurringChk'])){ $reocurring = 1; Echo "Reocurring is set";}else{ $reocurring = 0; Echo "Reocurring is NOT set!";}
  //$reocurring = $_POST['reocurringChk'];
  //$userConfig = getUserConfig($userID);
  //$UserName = $userConfig[0][0]; $APIKey = $userConfig[0][1]; $APISecret = $userConfig[0][2]; $email = $userConfig[0][3];
  //$AvgCoinPrice = $coinStats[0][1]; $MaxCoinPrice = $coinStats[0][2]; $MinCoinPrice = $coinStats[0][3];
  //$KEK = $userConfig[0][5];
  //if (!Empty($KEK)){$APISecret = decrypt($KEK,$userConfig[0][2]);}
  //echo "<BR> KEK $KEK | APISecret $APISecret | APIKey $APIKey";
  //if (isset($_POST['allCoinChk']){
  $allCoins = getAllCoins();
  $allCoinsSize = newCount($allCoins);
  //}
  $current_date = date('Y-m-d H:i');
  $newTime = date("Y-m-d H:i",strtotime("-30 mins", strtotime($current_date)));
  echo "<BR> ".$_POST['greaterThanSelect']." : ".$category;
  Echo "<BR> $userID, $salePrice,$category,$reocurring,$newTime)";
  if ($_POST['greaterThanSelect'] == "<"){
    if (isset($_POST['allCoinChk'])){
      for ($u=0; $u<$allCoinsSize; $u++){
          $AllCoinID = $allCoins[$u][0];
          $coinAlertRuleID = $allCoins[$u][1];
          AddCoinAlert($AllCoinID,'LessThan',$userID, $salePrice,$category,$reocurring,$newTime,$coinAlertRuleID);
      }
    }else{
      $coinAlertRuleID = $allCoins[0][1];
      AddCoinAlert($coinID,'LessThan',$userID, $salePrice,$category,$reocurring,$newTime,$coinAlertRuleID);
    }
  }elseif ($_POST['greaterThanSelect'] == ">"){
    if (isset($_POST['allCoinChk'])){
      for ($u=0; $u<$allCoinsSize; $u++){
          $AllCoinID = $allCoins[$u][0];
          $coinAlertRuleID = $allCoins[$u][1];
          AddCoinAlert($AllCoinID,'GreaterThan',$userID, $salePrice,$category,$reocurring,$newTime,$coinAlertRuleID);
      }
    }else{
      $coinAlertRuleID = $allCoins[0][1];
      AddCoinAlert($coinID,'GreaterThan',$userID, $salePrice,$category,$reocurring,$newTime,$coinAlertRuleID);
    }

  }
  changeCoinAlertRuleID();
  header('Location: CoinAlerts.php');
}elseif ($_GET['alert'] == 3 && isset($_GET['alert'])){
  $showmain = false;
  // UPDATE Existing ID
  $id = $_POST['CoinAlertRuleID']; $coinID = $_POST['CoinIDTxt'];  $category = $_POST['priceSelect'];
  $price = $_POST['coinPriceAltTxt']; $userID = $_SESSION['ID'];
  if ($_POST['greaterThanSelect'] == ">"){$action = "GreaterThan";} else {$action = "LessThan";}
  if(isset($_POST['reocurringChk'])){ $reocurring = 1; Echo "Reocurring is set";}else{ $reocurring = 0; Echo "Reocurring is NOT set!";}
  updateCoinAlertsbyID($id, $coinID, $action, $userID, $category, $reocurring, $price);
  header('Location: CoinAlerts.php');
}elseif ($_GET['alert'] == 4 && isset($_GET['alert'])){
  $id = $_GET['iD'];
  Echo "<BR> ID : $id";
  deleteSQLAlert($id);
  header('Location: CoinAlerts.php');
}else{
  displayHeader(8);
  $userID = $_SESSION['ID'];
  if ($_SESSION['isMobile']){ $num = 2; $fontSize = "font-size:60px"; }else{$num = 8;$fontSize = "font-size:32px"; }
  NewEcho("<h2>Coin Alerts!</h2>",$_SESSION['isMobile'] ,2);
  echo "<h3><a href='CoinAlerts.php'>Coin Alerts</a> &nbsp > &nbsp <a href='MarketAlerts.php'>Market Alerts</a>&nbsp > &nbsp <a href='SpreadBetAlerts.php'>SpreadBet Alerts</a></h3>";
  NewEcho("<Table><th>Edit</th><th>&nbspID</th><TH>&nbspCoinID</th><TH>&nbspAction</th><TH>&nbspPrice</th><TH>&nbspSymbol</th>",$_SESSION['isMobile'] ,2);
  newEcho("<TH>&nbspUserName</th><TH>&nbspEmail</th>",$_SESSION['isMobile'] ,0);
  newEcho("<TH>&nbspliveCoinPrice</th><TH>&nbspCategory</th><th>Reocurring</th><TH>&nbspDelete Alert</th><tr>",$_SESSION['isMobile'] ,2);
  $coinAlerts = getCoinAlertsUser($userID);
  $newArrLength = newCount($coinAlerts);
  for($x = 0; $x < $newArrLength; $x++) {
    $id = $coinAlerts[$x][14];$coinID = $coinAlerts[$x][1]; $action = $coinAlerts[$x][2];
    $price = round($coinAlerts[$x][3],$roundNum);$symbol = $coinAlerts[$x][4]; $userName = $coinAlerts[$x][5];
    $email = $coinAlerts[$x][6];$liveCoinPrice= round($coinAlerts[$x][7],$roundNum); $category = $coinAlerts[$x][8];
    $reocurring = $coinAlerts[$x][12]; //$coinAlertRuleID = $coinAlerts[$x][14];
    NewEcho("<td><a href='CoinAlerts.php?alert=1&edit=".$id."'><span class='glyphicon glyphicon-pencil' style='$fontSize;'></span></a></td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td>$id</td><td>$coinID</td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td>$action</td><td>$price</td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td>$symbol</td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td>$userName</td><td>$email</td>",$_SESSION['isMobile'] ,0);
    NewEcho("<td>$liveCoinPrice</td><td>$category</td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td>$reocurring</td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td><a href='CoinAlerts.php?alert=4&iD=$id'><i class='glyphicon glyphicon-trash' style='$fontSize;color:#D4EFDF'></i></a></td>",$_SESSION['isMobile'] ,2);
    NewEcho("<TR>",$_SESSION['isMobile'] ,2);
  }
  Echo "</table>";
  displaySideColumn();
  //displayMiddleColumn();
  //displayFarSideColumn();
  //displayFooter();
}

function getCoinAlertsFormData($id){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $sql = "SELECT `CoinAlertRuleID`, `Action`, `Price`,`Category`, `ReocurringAlert`,`CoinID`,`Symbol` FROM `CoinAlertsView` WHERE `CoinAlertRuleID` = $id limit 1";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['CoinAlertRuleID'],$row['Action'],$row['Price'],$row['Category'],$row['ReocurringAlert'],$row['CoinID'],$row['Symbol']);
  }
  $conn->close();
  return $tempAry;
}

function getAllCoins(){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `Cn`.`ID` as CoinID, `Car`.ID as CoinAlertRuleID
FROM `Coin` `Cn`
join `CoinAlertsRule` `Car`
WHERE `BuyCoin` = 1 ";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['CoinID'],$row['CoinAlertRuleID']);
  }
  $conn->close();
  return $tempAry;
}

function changeCoinAlertRuleID(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call ChangeCoinAlertRuleID('Alert Rule');";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("changeCoinAlertRuleID: ".$sql, 'BuyCoin', 0);
}

function AddCoinAlert($coinID,$action,$userID, $salePrice, $category, $reocurring,$newTime,$coinAlertRuleID){
  //
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "INSERT INTO `CoinAlerts`( `CoinID`, `Action`, `Price`, `UserID`,`Category`,`ReocurringAlert`,`DateTimeSent`,`CoinAlertRuleID`)
    VALUES ($coinID,'$action',$salePrice,$userID,'$category',$reocurring, now(),$coinAlertRuleID)";
    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}

function deleteSQLAlert($id){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "DELETE FROM `CoinAlerts` WHERE `CoinAlertRuleID` = $id";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}

if ($showmain == True){

}
//include header template
require('layout/footer.php');
$date = date('Y/m/d H:i:s', time());
echo " Last Updated :".$date;
?>
