<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
</head>
<?php require('includes/config.php');
include_once '../includes/newConfig.php';
setStyle($_SESSION['isMobile']);


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
include_once ('../../../../SQLData.php');
$locationStr = "Location: /Investment-Tracker/Cryptobot/1/m/BuyCoins.php";
//setStyle($_SESSION['isMobile']);

setMobileVariables();

if(isset($_GET['override'])){
  $_SESSION['MobDisplay'] = 2;
  $_SESSION['roundVar'] = 8;

}

if(isset($_GET['noOverride'])){
  $_SESSION['MobDisplay'] = 0;
  $_SESSION['roundVar'] = 3;
}
//$globals['sql_Option'] = "`Status` = 'Open'";
//if(empty($globals['sql_Option'])){$globals['sql_Option']= "`Status` = 'Open'";}

date_default_timezone_set('Asia/Dubai');
if ($_SESSION['DisableUntil']<date("Y-m-d H:i:s", time())) { $liveCoinStatus = "Active";} else { $liveCoinStatus = "Disabled Until: ".$_SESSION['DisableUntil']." - ".date("Y-m-d H:i:s", time());}
displayHeader(1);

if($_POST['transSelect'] <> ""){
  //Print_r("I'm HERE!!!".$_POST['submit']);
  changeSelection();
}elseif($_POST['newSelect'] <> ""){
    $temp = explode("_",$_POST['newSelect']);
    $returnVal = $temp[0];
    $id = $temp[1];
    echo "Return: $returnVal | $id";
    switch ($returnVal) {
      case "Change Fixed Sell Rule":

        break;
      case "Merge":
        //$sellrule = $temp[2];
        //echo "SellRule: $sellrule";
        updateMerge($id);
        //displayMerge($_GET['FixSellRule'],$_GET['SellRule']);
        header('Location: Transactions.php');
        break;
      case "Fix Coin Amount":

        break;
      case "Add To Spread":

        break;
      case "Run Stop BuyBack":

        break;
      case "Run override Reduce Loss":

        break;
      case "Run override Savings":

        break;
      case "Run override Bittrex":

        break;
      case "Run Stop Reduce Loss":

        break;
    }
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
}elseif ($_GET['fixCoinAmount'] <> ""){
  //echo "1";
  $transID = $_GET['SellRule'];
  $userID = $_GET['UserID'];
  $amount = $_GET['Amount'];
  $coinID = $_GET['CoinID'];

  //displayMerge($_GET['FixSellRule'],$_GET['SellRule']);
  //header('Location: Transactions.php');
  ?>
  <form action='Transactions.php?updateCoinAmount=Yes' method='post'>
    CoinID: <input type="text" name="coin_ID" value="<?php echo $coinID; ?>" style='color:Gray' readonly ><br>
    TransactionID: <input type="text" name="Transaction_ID" value="<?php echo $transID; ?>" style='color:Gray' readonly ><br>
    Amount: <input type="text" name="Coin_Amount" value="<?php echo $amount; ?>"><br>
    UserID: <input type="text" name="User_ID" value="<?php echo $userID; ?>" style='color:Gray' readonly ><br>
    <input type='submit' name='submit' value='Set Alert' class='settingsformsubmit' tabindex='36'>
  </form>
  <?php

}elseif ($_GET['addToSpread'] <> ""){//
  $transID = $_GET['SellRule'];
  $userID = $_SESSION['ID'];
  $rules = getRuleNames($userID);
  $rulesSize = count($rules);
  //echo "AddToSpread $transID";
  ?>
  <form action='Transactions.php?updateSpreadBet=Yes' method='post'>
    <select name="Spread_Rules" id="Spread_Rules_ID">
    <?php
      for ($e=0; $e<$rulesSize; $e++){
        $ruleName = $rules[$e][0]; $ruleID = $rules[$e][1];
          echo "<option value='$ruleID'>$ruleName</option>";
      }
      echo "<option value='-1'>Remove from Spread</option>";
      ?>
      </select>
      <input type="text" name="Trans_ID" value="<?php echo $transID; ?>" style='color:Gray' readonly ><br>
    <input type='submit' name='submit' value='Add to Spread' class='settingsformsubmit' tabindex='36'>
  </form>
  <?php
}elseif($_POST['Spread_Rules'] <> ""){
  $transID = $_POST['Trans_ID'];
  if ($_POST['Spread_Rules'] == -1){
    removeFromSpread($transID);
  }else{
    $ruleID = $_POST['Spread_Rules'];
    updateToSpread($ruleID, $transID);
  }

  //echo "Update SpreadRules $ruleID";
  header('Location: Transactions.php');
}elseif($_GET['stopBuyBack'] <> ""){
  $transID = $_GET['SellRule'];
  runStopBuyBack($transID);
  header('Location: Transactions.php');
}elseif($_GET['overrideReduceLoss'] <> ""){
    $transID = $_GET['SellRule'];
    runOverrideReduceLoss($transID);
    header('Location: Transactions.php');
}elseif($_GET['overrideSavings'] <> ""){
    $transID = $_GET['SellRule'];
    runOverrideSavings($transID);
    header('Location: Transactions.php');
}elseif($_GET['overrideBittrex'] <> ""){
    $transID = $_GET['SellRule'];
    runOverrideBittrex($transID);
    header('Location: Transactions.php');
}elseif($_GET['stopReduceLoss'] <> ""){
  $transID = $_GET['SellRule'];
  runStopReduceLoss($transID);
  header('Location: Transactions.php');
}else{

  //echo "3".$_POST['newSellRule']."-".$_POST['SellRule'];
  displayDefault();
}

if(isset($_POST['coin_ID'])){
  $coinID = $_POST['coin_ID'];
  $transID = $_POST['Transaction_ID'];
  $amount = $_POST['Coin_Amount'];
  $userID = $_POST['User_ID'];
  //Echo "CoinID:$coinID | TransactionID: $transID | Amount:$amount |  UserID:$userID";
  updateCoinAmount($transID,$amount);
  header('Location: Transactions.php');
}

function runOverrideReduceLoss($transID){
  //$newID = $_POST['newSellID'];
  //$transID = $_POST['transID'];

  $conn = getSQLConn(rand(1,3));
  //$current_date = date('Y-m-d H:i');
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `Transaction` SET `OverrideReduceLoss`= CASE
             WHEN `OverrideReduceLoss`= 1 THEN 0
             ELSE 1 end WHERE `ID` = $transID";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}

function runStopReduceLoss($transID){
  //$newID = $_POST['newSellID'];
  //$transID = $_POST['transID'];

  $conn = getSQLConn(rand(1,3));
  //$current_date = date('Y-m-d H:i');
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `Transaction` SET `StopReduceLoss`= CASE
             WHEN `StopReduceLoss`= 1 THEN 0
             ELSE 1 end WHERE `ID` = $transID";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}

function runOverrideSavings($transID){
  //$newID = $_POST['newSellID'];
  //$transID = $_POST['transID'];

  $conn = getSQLConn(rand(1,3));
  //$current_date = date('Y-m-d H:i');
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `Transaction` SET `DelayCoinSwapUntil` = now(), `SavingOverride`= CASE
             WHEN `SavingOverride`= 1 THEN 0
             ELSE 1 end WHERE `ID` = $transID";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}

function runOverrideBittrex($transID){
  //$newID = $_POST['newSellID'];
  //$transID = $_POST['transID'];

  $conn = getSQLConn(rand(1,3));
  //$current_date = date('Y-m-d H:i');
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `Transaction` SET `OverrideBittrexCancellation`= CASE
             WHEN `OverrideBittrexCancellation`= 1 THEN 0
             ELSE 1 end WHERE `ID` = $transID";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}

function runStopBuyBack($transID){
  //$newID = $_POST['newSellID'];
  //$transID = $_POST['transID'];

  $conn = getSQLConn(rand(1,3));
  //$current_date = date('Y-m-d H:i');
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `Transaction` SET `StopBuyBack`= CASE
             WHEN `StopBuyBack`= 1 THEN 0
             ELSE 1 end WHERE `ID` = $transID";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}


function getRuleNames($userID){
  $tempAry = [];
  // Create connection
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `Name`,`ID` FROM `SpreadBetRules` WHERE `UserID` = $userID";
  //print_r($sql);
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
   //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Name'],$row['ID']);
  }
  $conn->close();
  return $tempAry;
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

function updateCoinAmount($transID,$amount){

  $conn = getSQLConn(rand(1,3));
  $current_date = date('Y-m-d H:i');
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "call FixCoinAmount($amount,$transID);";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}

function updateToSpread($sbRuleID,$transID){

  $conn = getSQLConn(rand(1,3));
  $current_date = date('Y-m-d H:i');
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "call AddToSpreadBet($sbRuleID,$transID);";
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
    //$sql = "UPDATE `Transaction` SET `ToMerge`= 1 WHERE `ID` = $transID";
    $sql = "UPDATE `Transaction` SET `ToMerge`= CASE
             WHEN `ToMerge`= 1 THEN 0
             ELSE 1 end WHERE `ID` = $transID";
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
    $sql = "SELECT `IDTr`,`Type`,`CoinID`,`CoinPrice`,`Amount`,`Status`,`OrderDate`,`CompletionDate`,`BittrexID`,`OrderNo`,`Symbol`,`BittrexRef`,'BittrexStatus',`LiveCoinPrice`,`UserID`,`OrderNo`,`Symbol`
            ,`FixSellRule`,`ToMerge`,`BaseCurrency`,`StopBuyBack`,`OverrideReduceLoss`,`SavingOverride`,`OverrideBittrexCancellation`,`StopReduceLoss`
            FROM `View5_SellCoins` WHERE ".$statusA.$status.$statusB." and `UserID` = $userID order by `OrderDate` desc ";
    //print_r($sql);
    $result = $conn->query($sql);
    //$result = mysqli_query($link4, $query);
	   //mysqli_fetch_assoc($result);
    while ($row = mysqli_fetch_assoc($result)){
        $tempAry[] = Array($row['IDTr'],$row['Type'],$row['CoinID'],$row['CoinPrice'],$row['Amount'],$row['Status'],$row['OrderDate'],$row['CompletionDate'],$row['BittrexID'],$row['Symbol'],$row['BittrexRef'] //10
        ,$row['BittrexStatus'],$row['LiveCoinPrice'],$row['UserID'],$row['OrderNo'],$row['Symbol'],$row['FixSellRule'],$row['ToMerge'],$row['BaseCurrency'],$row['StopBuyBack'],$row['OverrideReduceLoss'] //20
        ,$row['SavingOverride'],$row['OverrideBittrexCancellation'],$row['StopReduceLoss']); //23
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
  $roundNum = $_SESSION['roundVar'];
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
  print_r("<th>Status</th><th>FixSellRule</th><th>Type</th>");

  print_r("<th>To Merge</th><th>Savings Override</th><th>StopBuyBack</th><th>OverrideReduceLoss</th>");
  print_r("<th>Override Bittrex</th>");
  print_r("<th>StopReduceLoss</th>");
  print_r("<th>Change Fixed Sell Rule</th>");
  print_r("<th>Merge</th>");
  print_r("<th>Fix Coin Amount</th>");
  print_r("<th>Add To Spread</th>");
  print_r("<th>Run Stop BuyBack</th>");
  print_r("<th>Run override Reduce Loss</th>");
  print_r("<th>Run override Savings</th>");
  print_r("<th>Run override Bittrex</th>");
  print_r("<th>Run Stop Reduce Loss</th>");
  print_r("<th>Action</th>");
  print_r("<th>Action Button</th>");
  print_r("<tr>");
  for($x = 0; $x < $arrlength; $x++) {
      $Id = $coin[$x][0]; $coinPrice = $coin[$x][3]; $amount  = $coin[$x][4]; $status  = $coin[$x][5]; $coinID = $coin[$x][2]; $userID = $coin[$x][13];
      $orderDate = $coin[$x][6]; $type = $coin[$x][1];
      $bittrexRef = $coin[$x][9];$orderNo = $coin[$x][14];$symbol = $coin[$x][15]; $fixSellRule = $coin[$x][16]; $toMerge = $coin[$x][17]; $baseCurrency = $coin[$x][18];
      $purchasePrice = ($amount*$coinPrice); $stopBuyBack = $coin[$x][19]; $overrideReduceLoss = $coin[$x][20]; $savingsOverride = $coin[$x][21];
      $overrideBittrex = $coin[$x][22]; $stopReduceLoss = $coin[$x][23];
      print_r("<td>$Id</td>");
      NewEcho("<td>$orderNo</td>",$_SESSION['isMobile'],$mobNum);
      print_r("<td>$symbol</td><td>".round($amount,$roundNum)."</td><td>".round($coinPrice,$roundNum)."</td>");
      newEcho("<td>$baseCurrency</td>",$_SESSION['isMobile'],$mobNum);
      print_r("<td>".round($purchasePrice,$roundNum)."</td>");
      newEcho("<td>$orderDate</td>",$_SESSION['isMobile'],$mobNum);
      print_r("<td>$status</td><td>$fixSellRule</td><td>$type</td>");
      print_r("<td>$toMerge</td>");
      print_r("<td>$savingsOverride</td>");
      print_r("<td>$stopBuyBack</td>");
      print_r("<td>$overrideReduceLoss</td>");
      print_r("<td>$overrideBittrex</td>");
      print_r("<td>$stopReduceLoss</td>");
      print_r("<td><a href='Transactions.php?changefixSell=Yes&SellRule=$Id&FixSellRule=$fixSellRule'>$fontSize</i></a></td>");
      print_r("<td><a href='Transactions.php?merge=Yes&SellRule=$Id'>$fontSize</i></a></td>");
      print_r("<td><a href='Transactions.php?fixCoinAmount=Yes&SellRule=$Id&CoinID=$coinID&UserID=$userID&Amount=$amount'>$fontSize</i></a></td>");
      print_r("<td><a href='Transactions.php?addToSpread=Yes&SellRule=$Id'>$fontSize</i></a></td>");
      print_r("<td><a href='Transactions.php?stopBuyBack=Yes&SellRule=$Id'>$fontSize</i></a></td>");
      print_r("<td><a href='Transactions.php?overrideReduceLoss=Yes&SellRule=$Id'>$fontSize</i></a></td>");
      print_r("<td><a href='Transactions.php?overrideSavings=Yes&SellRule=$Id'>$fontSize</i></a></td>");
      print_r("<td><a href='Transactions.php?overrideBittrex=Yes&SellRule=$Id'>$fontSize</i></a></td>");
      print_r("<td><a href='Transactions.php?stopReduceLoss=Yes&SellRule=$Id'>$fontSize</i></a></td>");
      ?>

        <form action='Transactions.php?dropdown=Yes' method='post'>
          <td>
        <select name='newSelect' id='newSelect' class='enableTextBox'>
          <?php echo "<option  selected='selected' value='Change Fixed Sell Rule_".$Id."'>Change Fixed Sell Rule</option>";
          echo "<option  value='Merge_".$Id."_".$fixSellRule."'>Merge</option>";
          echo "<option  value='Fix Coin Amount_".$Id."'>Fix Coin Amount</option>";
          echo "<option  value='Add To Spread_".$Id."'>Add To Spread</option>";
          echo "<option  value='Run Stop BuyBack_".$Id."'>Run Stop BuyBack</option>";
          echo "<option  value='Run override Reduce Loss_".$Id."'>Run override Reduce Loss</option>";
          echo "<option  value='Run override Savings_".$Id."'>Run override Savings</option>";
          echo "<option  value='Run override Bittrex_".$Id."'>Run override Bittrex</option>";
          echo "<option  value='Run Stop Reduce Loss_".$Id."'>Run Stop Reduce Loss</option>";
          ?>
        </td><td>
          <input type='submit' name='submit' value='Update' class='settingsformsubmit' tabindex='36'>
      </td></form>
      <?php
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
