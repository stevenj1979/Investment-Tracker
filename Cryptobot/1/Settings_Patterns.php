<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<?php require('includes/config.php');
  //include 'includes/functions.php';
  include_once ('../../../../SQLData.php');
  include_once ('/home/stevenj1979/Encrypt.php');
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


if (!empty($_POST['CoinPriceMatchNamesSelect']) && !empty($_GET['changeNameSelection'])){
    //echo "<BR> coin price Match Names is ".$_POST['CoinPriceMatchNamesSelect'];
    //echo "<BR>  ID is ".$_POST['CoinPriceMatchNamesSelect'];
    setNameSelection($_POST['CoinPriceMatchNamesSelect']);
}elseif (!empty($_POST['CoinPricePatternNamesSelect']) && !empty($_GET['changeNameSelection'])){
    //echo "<BR> coin price Match Names is ".$_POST['CoinPriceMatchNamesSelect'];
    //echo "<BR>  ID is ".$_POST['CoinPriceMatchNamesSelect'];
    setNameSelectionPricePattern($_POST['CoinPricePatternNamesSelect']);
}elseif (!empty($_POST['addPricePatternBtn']) && !empty($_GET['addPricePattern'])){
  $cmbo1 = $_POST['selectCmbo1Hr1'];$cmbo2 = $_POST['selectCmbo1Hr2'];
  $cmbo3 = $_POST['selectCmbo1Hr3']; $cmbo4 = $_POST['selectCmbo1Hr4'];
  $pattern = str_replace("2","*",$cmbo1.$cmbo2.$cmbo3.$cmbo4);
  echo "<BR> $pattern";
  addTrendPatterntoSQL($pattern);
}elseif (!empty($_POST['removePricePatternBtn']) && !empty($_GET['addPricePattern'])){
  $ID = $_POST['CoinPricePatternSelect'];
  //echo "<BR> Test REmove ID $ID";
  removePricePatternfromSQL($ID);
}elseif (!empty($_POST['addPriceBtn']) && !empty($_GET['addPrice'])){
      echo "<BR> addPriceBtn not empty";
      $coinID = $_POST['symbol']; $topPrice = $_POST['topPrice']; $bottomPrice =  $_POST['bttmPrice'];
      echo "<br> ADD : $symbol | Top : $topPrice | bttm: $bottomPrice";
      addpricePatterntoSQL($coinID, $topPrice, $bottomPrice);
}elseif (!empty($_POST['newNameBtn']) && !empty($_GET['addNewName'])){
      echo "<BR> New Name : ".$_POST['newNameTxt'];
      $userID = $_POST['User_ID'];
      addNewName("`CoinPriceMatchName`",$_POST['newNameTxt'],$userID);
}elseif (!empty($_POST['removePriceBtn']) && !empty($_GET['addPrice'])){
      echo "<BR> removePriceBtn not empty";
      $ID = $_POST['CoinPriceMatchSelect'];
      echo "<br> Remove : ID : $ID";
      removePricefromSQL($ID);
}elseif (!empty($_POST['Coin1HrPatternNamesSelect']) && !empty($_GET['changeHr1NameSelection'])){
    echo "<BR> Test 1Hr Refresh ".$_SESSION['coin1HrPatternNameSelected'];
    setNameSelection1HrPattern($_SESSION['coin1HrPatternNameSelected']);
}elseif (!empty($_POST['newName1HrPatternBtn']) && !empty($_GET['addNew1HrPatternName'])){
    addNewName("`Coin1HrPatternName`",$_POST['newName1HrPatterntxt'],$_Session['ID']);
}elseif (!empty($_POST['newNamePricePatternBtn']) && !empty($_GET['addNew1HrPatternName'])){
    addNewName("`CoinPricePatternName`",$_POST['newNamePricePatterntxt'],$_Session['ID']);
}elseif (!empty($_POST['add1HrPatternBtn']) && !empty($_GET['add1HrPattern'])){
    $cmbo1 = $_POST['selectCmbo1Hr1New'];$cmbo2 = $_POST['selectCmbo1Hr2New'];
    $cmbo3 = $_POST['selectCmbo1Hr3New']; $cmbo4 = $_POST['selectCmbo1Hr4New'];
    $pattern = str_replace("2","*",$cmbo1.$cmbo2.$cmbo3.$cmbo4);
    add1HrPatterntoSQL($pattern);
}elseif (!empty($_POST['remove1HrPatternBtn']) && !empty($_GET['add1HrPattern'])){
  $ID = $_POST['Coin1HrPatternSelect'];
  remove1HrPatternfromSQL($ID);
}

function addNewName($table, $name, $userID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "INSERT INTO $table(`Name`, `UserID`, `BuySell`) VALUES ('$name',$userID,'Buy')";
  //echo $sql;
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}


function removePricePatternfromSQL($ruleID){
  $userID = $_SESSION['ID'];
  $nameID = $_SESSION['coinPricePatternNameSelected'];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "Delete FROM `CoinPricePatternRules` WHERE `PatternID` = $ruleID and `CoinPricePatternNameID` = $nameID";
  //echo $sql;
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  header('Location: Settings_Patterns.php');
}

function addTrendPatterntoSQL($pattern){
  $nameID = $_SESSION['coinPricePatternNameSelected'];
  $userID = $_SESSION['ID'];
  //echo "$ruleID $symbol $price $userID";
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call addPricePattern('$pattern', $userID, $nameID);";
  //echo $sql;
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  header('Location: Settings_Patterns.php');
}

function add1HrPatterntoSQL($pattern){
  $nameID = $_SESSION['coin1HrPatternNameSelected'];
  $userID = $_SESSION['ID'];
  //echo "$ruleID $symbol $price $userID";
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call add1HrPattern('$pattern', $userID, $nameID);";
  //echo $sql;
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  header('Location: Settings_Patterns.php');
}



function addpricePatterntoSQL($coinID, $price, $lowPrice){
  $userID = $_SESSION['ID'];
  $nameID = $_SESSION['coinPriceMatchNameSelected'];
  //echo "$ruleID $symbol $price $userID";
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "call addNewCoinPriceMatchBuy($price,$coinID,$userID,$lowPrice, $nameID);";
  //echo $sql;
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  header('Location: Settings_Patterns.php');
}

function removePricefromSQL($price){
  $splitPrice = explode('+',$price);
  $coinPriceMatchNameID = $splitPrice[1]; $coinPriceMatchID = $splitPrice[0]; $userID = $splitPrice[2];
  $userID = $_SESSION['ID'];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "DELETE FROM `CoinPriceMatchRules` WHERE `CoinPriceMatchNameID` = $coinPriceMatchNameID and `CoinPriceMatchID` = $coinPriceMatchID ";
  //echo $sql;
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  header('Location: Settings_Patterns.php');
}

function remove1HrPatternfromSQL(){
  $userID = $_SESSION['ID'];
  $nameID = $_SESSION['coinPricePatternNameSelected'];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "Delete FROM `Coin1HrPatternRules` WHERE `Coin1HrPatternID` = $ruleID and `Coin1HrPatternNameID` = $nameID";
  //echo $sql;
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  header('Location: Settings_Patterns.php');
}

function setNameSelection($newSelected){
  $_SESSION['coinPriceMatchNameSelected'] = $newSelected;
}

function setNameSelectionPricePattern($newSelected){
  $_SESSION['coinPricePatternNameSelected'] = $newSelected;
}

function setNameSelection1HrPattern($newSelected){
  $_SESSION['coin1HrPatternNameSelected'] = $newSelected;
}

function getCoinPriceMatchSettingsLocal($whereClause = ""){
  $conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `CoinID`,`Price`,`Symbol`,`LowPrice`,`Name`,`UserID`, `ID`,`CoinPriceMatchNameID` FROM `NewCoinPriceMatchSettingsView` $whereClause";
  //echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['CoinID'],$row['Price'],$row['Symbol'],$row['LowPrice'],$row['Name'],$row['UserID'],$row['ID'],$row['CoinPriceMatchNameID']);
  }
  $conn->close();
  return $tempAry;
}

function getCoinPricePatternSettingsLocal($whereClause = ""){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Name`,`CoinPattern`,`CoinPricePatternNameID`,`ID`,`UserID` FROM `NewCoinPricePatternSettingsView` $whereClause";
  $result = $conn->query($sql);
  //echo $sql;
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Name'],$row['CoinPattern'],$row['CoinPricePatternNameID'],$row['ID'],$row['UserID']);
  }
  $conn->close();
  return $tempAry;
}

function getCoin1HrPatternSettingsLocal($whereClause = ""){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Name`,`Pattern`,`Coin1HrPatternNameID`,`ID`,`UserID` FROM `NewCoin1HrPatternSettingsView` $whereClause";
  $result = $conn->query($sql);
  echo $sql;
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Name'],$row['Pattern'],$row['Coin1HrPatternNameID'],$row['ID'],$row['UserID']);
  }
  $conn->close();
  return $tempAry;
}

function getCoinsLocal(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Symbol`,`ID` FROM `Coin` where `buyCoin` = 1";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Symbol'],$row['ID']);
  }
  $conn->close();
  return $tempAry;
}

function displayTrendSymbols($symbolList, $name, $enabled){
  $symbolListCount = newCount($symbolList);
  $readOnly = "";
  //echo "<BR> ENABLED: ".$enabled;
  if ($enabled == 0){$readOnly = " style='color:Gray' readonly ";}
  Echo "<select name='$name' $readOnly>";
  for ($i=0; $i<$symbolListCount; $i++){
    $symbol = $symbolList[$i];
    $num = $i-1;
    //$name = str_replace('-1','Minus1',$name);
    echo "<option value='$num'>$symbol</option>";
  }
  echo "</select>";
}

$coinPriceMatchNames = getCoinPriceMatchNames($_SESSION['ID'], "`CoinPriceMatchName`","");
$coinPriceMatchNamesSize = newCount($coinPriceMatchNames);
$coins = getCoinsLocal();
$coinsSize = newCount($coins);
$coinPriceMatch = getCoinPriceMatchSettingsLocal("Where `CoinPriceMatchNameID` = '".$_SESSION['coinPriceMatchNameSelected']."'");
$coinPriceMatchSize = newCount($coinPriceMatch);

$coinPricePatternNames = getCoinPriceMatchNames($_SESSION['ID'], "`CoinPricePatternName`","");
$coinPricePatternNamesSize = newCount($coinPricePatternNames);
$coinPricePattern = getCoinPricePatternSettingsLocal("Where `CoinPricePatternNameID` = '".$_SESSION['coinPricePatternNameSelected']."'");
$coinPricePatternSize = newCount($coinPricePattern);


$coin1HrPatternNames = getCoinPriceMatchNames($_SESSION['ID'], "`Coin1HrPatternName`","");
$coin1HrPatternNamesSize = newCount($coin1HrPatternNames);
$coin1HrPattern = getCoin1HrPatternSettingsLocal("Where `Coin1HrPatternNameID` = '".$_SESSION['coin1HrPatternNameSelected']."'");
$coin1HrPatternSize = newCount($coin1HrPattern);
//$coin1HrPattern = getCoin1HrPattenSettings();
//$coin1HrPatternSize = newCount($coin1HrPattern);

Echo "<BR> 1Hr pattern size : $coin1HrPatternSize";

$coinPriceMatchNameSelected = $_SESSION['coinPriceMatchNameSelected'];
$coinPricePatternNameSelected = $_SESSION['coinPricePatternNameSelected'];
$coin1HrPatternNameSelected = $_SESSION['coin1HrPatternNameSelected'];
$readOnly = " style='color:Gray' readonly ";
$comboList = Array('-1','0','1','*');
  displayHeader(7);
  //<h3><a href='Settings.php'>User Settings</a> &nbsp > &nbsp <a href='BuySettings.php'>Buy Settings</a> &nbsp > &nbsp <a href='SellSettings.php'>Sell Settings</a> &nbsp > &nbsp <a href='Settings_Patterns.php'>Setting Patterns</a></h3>
  displaySubHeader("Settings");
  echo "<H3>Coin Price Match</H3>";
  echo "<div><form action='Settings_Patterns.php?changeNameSelection=Y' method='post'>";
  Echo "<select name='CoinPriceMatchNamesSelect'>";
  for ($i=0; $i<$coinPriceMatchNamesSize; $i++){
    $name = $coinPriceMatchNames[$i][0]; $nameID = $coinPriceMatchNames[$i][1];
    //$coinID = $coinPriceMatch[$i][4];$price = $coinPriceMatch[$i][2];
    //$symbol = $coinPriceMatch[$i][3];$lowPrice = $coinPriceMatch[$i][1];
    Echo "<BR> $name | $coinPriceMatchNameSelected";
    if ($nameID == $coinPriceMatchNameSelected){
      echo "<option value='$nameID' selected>$name</option>";
    }else{
      echo "<option value='$nameID'>$name</option>";
    }

  }
  echo "</select>";
  echo "<input type='submit' name='publishTrend' value='Refresh'></form>";
  echo "<form action='Settings_Patterns.php?addNewName=Y' method='post'>";
    echo "<input type='text' name='newNameTxt' id='newNametxt' class='form-control input-lg' placeholder='Name' value='' tabindex='1'>";
    echo "<input type='submit' name='newNameBtn' value='Add New Name'>";
    echo "<input type='text' name='User_ID' $readOnly value='".$_SESSION['ID']."' tabindex='2'>";
  echo "</form>";
  echo "<form action='Settings_Patterns.php?addPrice=Y' method='post'>";
    echo "<select name='CoinPriceMatchSelect' size='$coinPriceMatchSize'>";
    for ($l=0; $l<$coinPriceMatchSize; $l++){
        $name = $coinPriceMatch[$l][4]; $price = $coinPriceMatch[$l][1];
        $lowPrice = $coinPriceMatch[$l][3]; $symbol = $coinPriceMatch[$l][2]; $coinID = $coinPriceMatch[$l][0];
        $ID = $coinPriceMatch[$l][6]; $coinMatchNameID = $coinPriceMatch[$l][7]; $userID = $coinPriceMatch[$l][5];
        echo "<option value='$ID+$coinMatchNameID+$userID'>$symbol | $price | $lowPrice</option>";
    }
    echo "</select>";
    echo "<select name='symbol'>";
    for ($m = 0; $m<$coinsSize; $m++){
      $symbol = $coins[$m][0]; $coinID = $coins[$m][1];
      echo "<option value='$coinID'>$symbol</option>";
      //echo "<input type='text' name='symbol' id='symbol' class='form-control input-lg' placeholder='BTC' value='' tabindex='1'>";
    }
    echo "</select>";
    echo "<input type='text' name='topPrice' id='topPrice' class='form-control input-lg' placeholder='8000.00' value='' tabindex='2'>";
    echo "<input type='text' name='bttmPrice' id='bttmPrice' class='form-control input-lg' placeholder='0.00' value='' tabindex='3'>";
    echo "<input type='submit' name='addPriceBtn' value='+'>";
    echo "<input type='submit' name='removePriceBtn' value='-'>";
  echo "</form></div>";


  echo "<H3>Coin Price Pattern</H3>";
  echo "<div><form action='Settings_Patterns.php?changeNameSelection=Y' method='post'>";
    Echo "<select name='CoinPricePatternNamesSelect'>";
    for ($i=0; $i<$coinPricePatternNamesSize; $i++){
      $name = $coinPricePatternNames[$i][0]; $nameID = $coinPricePatternNames[$i][1];
      //$coinID = $coinPriceMatch[$i][4];$price = $coinPriceMatch[$i][2];
      //$symbol = $coinPriceMatch[$i][3];$lowPrice = $coinPriceMatch[$i][1];
      Echo "<BR> $name | $coinPricePatternNameSelected";
      if ($nameID == $coinPricePatternNameSelected){
        echo "<option value='$nameID' selected>$name</option>";
      }else{
        echo "<option value='$nameID'>$name</option>";
      }
    }
    echo "</select>";
    echo "<input type='submit' name='publishTrendPricePattern' value='Refresh'></form>";
  echo "<form action='Settings_Patterns.php?addNewPricePatternName=Y' method='post'>";
    echo "<input type='text' name='newNamePricePatterntxt' id='newNamePricePatterntxt' class='form-control input-lg' placeholder='Name' value='' tabindex='1'>";
    echo "<input type='submit' name='newNamePricePatternBtn' value='Add New Name'>";
  echo "</form>";
  echo "<form action='Settings_Patterns.php?addPricePattern=Y' method='post'>";
    echo "<select name='CoinPricePatternSelect' size='$coinPricePatternSize'>";
      for ($j=0; $j<$coinPricePatternSize; $j++){
        $name = $coinPricePattern[$j][0]; $pattern = $coinPricePattern[$j][1];
        $nameID = $coinPricePattern[$j][2]; $patternID = $coinPricePattern[$j][3];
        echo "<option value='$patternID'>$pattern</option>";
      }
    echo "</select>";
    displayTrendSymbols($comboList,'selectCmbo1Hr1', 1);
    displayTrendSymbols($comboList,'selectCmbo1Hr2', 1);
    displayTrendSymbols($comboList,'selectCmbo1Hr3', 1);
    displayTrendSymbols($comboList,'selectCmbo1Hr4', 1);
    echo "<input type='submit' name='addPricePatternBtn' value='+'>";
    echo "<input type='submit' name='removePricePatternBtn' value='-'>";
  echo "</form></div>";



  echo "<H3>Coin 1 Hour Pattern</H3>";
  echo "<div><form action='Settings_Patterns.php?changeHr1NameSelection=Y' method='post'>";
    Echo "<select name='Coin1HrPatternNamesSelect'>";
    for ($k=0; $k<$coin1HrPatternNamesSize; $k++){
      $name = $coin1HrPatternNames[$k][0]; $nameID = $coin1HrPatternNames[$k][1];
      //$coinID = $coinPriceMatch[$i][4];$price = $coinPriceMatch[$i][2];
      //$symbol = $coinPriceMatch[$i][3];$lowPrice = $coinPriceMatch[$i][1];
      Echo "<BR> $name | $coin1HrPatternNameSelected";
      if ($nameID == $coin1HrPatternNameSelected){
        echo "<option value='$nameID' selected>$name</option>";
      }else{
        echo "<option value='$nameID'>$name</option>";
      }
    }
    echo "</select>";
    echo "<input type='submit' name='publishTrend1HrPattern' value='Refresh'></form>";
    echo "<form action='Settings_Patterns.php?addNew1HrPatternName=Y' method='post'>";
      echo "<input type='text' name='newName1HrPatterntxt' id='newName1HrPatterntxt' class='form-control input-lg' placeholder='Name' value='' tabindex='1'>";
      echo "<input type='submit' name='newName1HrPatternBtn' value='Add New Name'>";
    echo "</form>";
    echo "<form action='Settings_Patterns.php?add1HrPattern=Y' method='post'>";
      echo "<select name='Coin1HrPatternSelect' size='$coin1HrPatternSize'>";
        for ($n=0; $n<$coin1HrPatternSize; $n++){
          $name = $coin1HrPattern[$n][0]; $pattern = $coin1HrPattern[$n][1];
          $nameID = $coin1HrPattern[$n][2]; $patternID = $coin1HrPattern[$n][3];
          echo "<option value='$patternID'>$pattern</option>";
        }
      echo "</select>";
      displayTrendSymbols($comboList,'selectCmbo1Hr1New', 1);
      displayTrendSymbols($comboList,'selectCmbo1Hr2New', 1);
      displayTrendSymbols($comboList,'selectCmbo1Hr3New', 1);
      displayTrendSymbols($comboList,'selectCmbo1Hr4New', 1);
      echo "<input type='submit' name='add1HrPatternBtn' value='+'>";
      echo "<input type='submit' name='remove1HrPatternBtn' value='-'>";
    echo "</form></div>";

  displaySideColumn(); ?>

</body>
</html>
