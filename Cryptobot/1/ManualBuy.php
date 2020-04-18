<?php
//require('includes/config.php');
include_once ('/home/stevenj1979/SQLData.php');
include '../includes/newConfig.php';
require('includes/config.php');
if(!$user->is_logged_in()){ header('Location: login.php'); exit(); }
?>
<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>

<style>
<?php include 'style/style.css'; ?>
</style>
<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript">
google.load('visualization', '1', {packages: ['corechart']});
</script>
<script type="text/javascript">
function drawVisualization() {
  var jsonData = null;
  var newURL = "http://www.investment-tracker.net/Investment-Tracker/Cryptobot/1/getCoinChart.php?coinID=ETH";
  var symbol = "<?php echo $_GET['coin']; ?>";
  var json = $.ajax({
    url: "http://www.investment-tracker.net/Investment-Tracker/Cryptobot/1/getCoinChart.php?coinID=" + symbol , // make this url point to the data file
    dataType: "json",
    async: false,
    success: (
  function(data) {
      jsonData = data;
  })
  }).responseText;



  // Create and populate the data table.
  var data = new google.visualization.DataTable(jsonData);


  // Create and draw the visualization.
var chart= new google.visualization.LineChart(document.getElementById('visualization')).
      draw(data, {curveType: "function",
                  width: 1300, height: 400,
                 }
          );
}

function getUrlVars() {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
        vars[key] = value;
    });
    return vars;
}

google.setOnLoadCallback(drawVisualization);
</script>
<Body><?php
//getUrlVars()["coin"]

if($_GET['coin'] <> ""){
  //collect values from the url
  $userConfig = getUserConfig($_SESSION['ID']);
  $btcBuyAmount = $userConfig[0][4];
  $coinStats = getCoinStats($_GET['coin']);
  echo "Coin is set ".$_GET['coin'];
  $coin = trim($_GET['coin']);
  $cost = trim($_GET['coinPrice']);
  $baseCurrency = trim($_GET['baseCurrency']);
  $coinID = trim($_GET['coinID']);
  $KEK = $userConfig[0][5];
  $apikey = $userConfig[0][1]; $apiSecret = $userConfig[0][2];
  //$coinPrice = trim($_GET['coinPrice']);
  //$active = trim($_GET['y']);
      displayHeader(3);
      displaySideColumn();
      displayMiddleColumn();
      setGlobalVars();
      displayCoinForm();
      displayFarSideColumn();
      ?>&nbsp<?php
      displayFooter();
}

if($_GET['alert'] <> ""){
  displayHeader(3);
  displaySideColumn();
  displayMiddleColumn();
  $GLOBALS['coin'] = $_GET['coinAlt']; $GLOBALS['cost'] = $_GET['coinPrice']; $GLOBALS['baseCurrency'] = $_GET['baseCurrency'];
  $GLOBALS['coinID'] = $_GET['coinID'];
  displayAlertForm();
  displayFarSideColumn();
  ?>&nbsp<?php
  displayFooter();
}

if(isset($_POST['coinAltTxt'])){
  date_default_timezone_set('Asia/Dubai');
  $date = date("Y-m-d H:i:s", time());
  $userID = $_SESSION['ID'];
  //$coin = $_POST['coinAltTxt']; $baseCurrency = $_POST['BaseCurTxt'];
  $coinID = $_POST['CoinIDTxt']; $userID = $_POST['UserIDTxt'];
  $salePrice = $_POST['coinPriceAltTxt'];
  $userConfig = getUserConfig($userID);
  $UserName = $userConfig[0][0]; $APIKey = $userConfig[0][1]; $APISecret = $userConfig[0][2]; $email = $userConfig[0][3];
  //$AvgCoinPrice = $coinStats[0][1]; $MaxCoinPrice = $coinStats[0][2]; $MinCoinPrice = $coinStats[0][3];
  $KEK = $userConfig[0][5];
  //if (!Empty($KEK)){$APISecret = decrypt($KEK,$userConfig[0][2]);}
  //echo "<BR> KEK $KEK | APISecret $APISecret | APIKey $APIKey";

  if ($_POST['greaterThanSelect'] == "<" ){
    AddCoinAlert($coinID,'LessThan',$userID, $salePrice);
  }else{
    AddCoinAlert($coinID,'GreaterThan',$userID, $salePrice);
  }
  //header('Location: BuyCoins.php');
}

function AddCoinAlert($coinID,$action,$userID, $salePrice){
  //
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "INSERT INTO `CoinAlerts`( `CoinID`, `Action`, `Price`, `UserID`) VALUES ($coinID,'$action',$salePrice,$userID)";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}

if(isset($_POST['coinTxt'])){
//if($_POST['manualPrice'] == 'Yes'){
  date_default_timezone_set('Asia/Dubai');
  $date = date("Y-m-d H:i:s", time());
  //$_SESSION['coin'] = $_post['coinTxt'];
  $salePrice = number_format((float)$_POST['coinPriceTxt'], 8, '.', ''); $coin = $_POST['coinTxt']; $baseCurrency = $_POST['BaseCurTxt'];
  $coinID = $_POST['CoinIDTxt']; $userID = $_POST['UserIDTxt'];
  $TimeToCancelBuyMins = $_POST['TimeToCancelBuyMinsTxt'];
  $BTCBuyAmount = $_POST['costTxt']; $cost = $_POST['coinPriceTxt'];
  $userConfig = getUserConfig($userID);
  $UserName = $userConfig[0][0]; $APIKey = $userConfig[0][1]; $APISecret = $userConfig[0][2]; $Email = $userConfig[0][3];
  //$AvgCoinPrice = $coinStats[0][1]; $MaxCoinPrice = $coinStats[0][2]; $MinCoinPrice = $coinStats[0][3];
  $KEK = $userConfig[0][5];
  if (!Empty($KEK)){$APISecret = decrypt($KEK,$userConfig[0][2]);}
  echo "<BR> KEK $KEK | APISecret $APISecret | APIKey $APIKey";
  if ($_POST['priceSelect'] == 'manual'){
    $salePrice = $cost;
  }elseif ($_POST['priceSelect'] == 0.25){
    $tmpPrice = number_format((float)$cost-(($cost/100 )*0.25), 8, '.', '');
    $salePrice = round($tmpPrice,8, PHP_ROUND_HALF_DOWN);
  }elseif ($_POST['priceSelect'] == 0.5){
    $tmpPrice = number_format((float)$cost-(($cost/100 )*0.5), 8, '.', '');
    $salePrice = round($tmpPrice,8, PHP_ROUND_HALF_DOWN);
  }elseif ($_POST['priceSelect'] == 1){
    $tmpPrice = number_format((float)$cost-(($cost/100 )*1), 8, '.', '');
    $salePrice = round($tmpPrice,8, PHP_ROUND_HALF_DOWN);
  }elseif ($_POST['priceSelect'] == 1.5){
    $tmpPrice = number_format((float)$cost-(($cost/100 )*1.5), 8, '.', '');
    $salePrice = round($tmpPrice,8, PHP_ROUND_HALF_DOWN);
  }elseif ($_POST['priceSelect'] == 2){
    $tmpPrice = number_format((float)$cost-(($cost/100 )*2), 8, '.', '');
    $salePrice = round($tmpPrice,8, PHP_ROUND_HALF_DOWN);
  }elseif ($_POST['priceSelect'] == 2.5){
    $tmpPrice = number_format((float)$cost-(($cost/100 )*2.5), 8, '.', '');
    $salePrice = round($tmpPrice,8, PHP_ROUND_HALF_DOWN);
  }elseif ($_POST['priceSelect'] == 3){
    $tmpPrice = number_format((float)$cost-(($cost/100 )*3), 8, '.', '');
    $salePrice = round($tmpPrice,8, PHP_ROUND_HALF_DOWN);
  }elseif ($_POST['priceSelect'] == 5){
    $tmpPrice = number_format((float)$cost-(($cost/100 )*5), 8, '.', '');
    $salePrice = round($tmpPrice,8, PHP_ROUND_HALF_DOWN);
  }elseif ($_POST['priceSelect'] == 10){
    $tmpPrice = number_format((float)$cost-(($cost/100 )*10), 8, '.', '');
    $salePrice = round($tmpPrice,8, PHP_ROUND_HALF_DOWN);
  }elseif ($_POST['priceSelect'] == 20){
    $tmpPrice = number_format((float)$cost-(($cost/100 )*20), 8, '.', '');
    $salePrice = round($tmpPrice,8, PHP_ROUND_HALF_DOWN);
  }

  buyCoins($APIKey,$APISecret,$coin,$Email,$userID,$date,$baseCurrency,1,1,$BTCBuyAmount,99999,$UserName,$coinID,0,0,1,$TimeToCancelBuyMins,'ALL',$salePrice);
  //echo "buyCoins($APIKey,$APISecret,$coin,$Email,$userID,$date,$baseCurrency,1,1,$BTCBuyAmount,99999,$UserName,$coinID,0,0,1,$TimeToCancelBuyMins,'ALL',$salePrice);";

  header('Location: BuyCoins.php');
}



function getUserConfig($userID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `UserName`,`APIKey`,`APISecret`,`Email`,`BTCBuyAmount`, `KEK` FROM `UserConfigView` WHERE `ID` = $userID";
  echo $sql;
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['UserName'],$row['APIKey'],$row['APISecret'],$row['Email'],$row['BTCBuyAmount'],$row['KEK']);}
  $conn->close();
  return $tempAry;

}

function getCoinStats($symbol){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `Symbol`,`AvgCoinPrice`,`MaxCoinPrice`, `MinCoinPrice` FROM `AvgCoinPriceTableWeb` WHERE `Symbol` = '$symbol'";
  //echo $sql;
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['Symbol'],$row['AvgCoinPrice'],$row['MaxCoinPrice'],$row['MinCoinPrice']);}
  $conn->close();
  return $tempAry;

}

function setGlobalVars(){

    $apikey = $GLOBALS['apikey']; $apiSecret = $GLOBALS['apiSecret'] ; $baseCurrency = $GLOBALS['baseCurrency']; $KEK = $GLOBALS['KEK'];
    if (!Empty($KEK)){$apiSecret = decrypt($KEK,$apiSecret);}
    $BTCBalance = bittrexbalance($apikey, $apiSecret,$baseCurrency);
    $cost = $GLOBALS['cost'];
}

function displayCoinForm(){
  $userID = $_SESSION['ID'];
  ?> <h1>Manual Buy Coin</h1>
  <h2>Enter Price</h2>
  <form action='ManualBuy.php?manualPrice=Yes' method='post'>
    Coin: <input type="text" name="coinTxt" value="<?php echo $GLOBALS['coin']; ?>"><br>
    BTC Buy Amount: <input type="text" name="costTxt" value="<?php echo $GLOBALS['btcBuyAmount']; ?>"> 0 equals full bittrex balance | Current Balance is : <?php echo $BTCBalance.$apiKey.$apiSecret.$baseCurrency.$KEK ?> <br>
    <select name="priceSelect">
      <option value="manual" name='manualOpt'>Manual Price (Below)</option>
      <option value="0.25" name='zeroTwoFivePctOpt'>0% (Break Even)</option>
      <option value="0.5" name='zeroFivePctOpt'>0.5%</option>
      <option value="1" name='onePctOpt'>1%</option>
      <option value="1.5" name='onePointFivePctOpt'>1.5%</option>
      <option value="2" name='twoPctOpt'>2%</option>
      <option value="2.5" name='twoPointFivePctOpt'>2.5%</option>
      <option value="3" name='threePctOpt'>3%</option>
      <option value="5" name='fivePctOpt'>5%</option>
      <option value="10" name='tenPctOpt'>10%</option>
      <option value="20" name='twentyPctOpt'>20%</option>
    Coin Price: <input type="text" name="coinPriceTxt" value="<?php echo $GLOBALS['cost']; ?>"> <br>
    Time To Cancel in Mins: <input type="text" name="TimeToCancelBuyMinsTxt" value=90> <br>
    <p>Average Coin Price = <?php echo $GLOBALS['AvgCoinPrice'];
      $tmpPrice = number_format((float)$cost-(($cost/100 )*1), 8, '.', '');
      $cost = round($tmpPrice,8, PHP_ROUND_HALF_DOWN);
      echo "<p> 1% = ".number_format((float)$cost, 8, '.', '');
      ?>
    <p>Max Coin Price = <?php  echo $GLOBALS['MaxCoinPrice']; ?>
    <p>Min Coin Price = <?php  echo $GLOBALS['MinCoinPrice']; ?>
    BaseCurrency: <input type="text" name="BaseCurTxt" value="<?php echo $GLOBALS['baseCurrency']; ?>" style='color:Gray' readonly ><br>
    CoinID: <input type="text" name="CoinIDTxt" value="<?php echo $GLOBALS['coinID']; ?>" style='color:Gray' readonly ><br>
    UserID: <input type="text" name="UserIDTxt" value="<?php echo $userID; ?>" style='color:Gray' readonly ><br>
    <input type='submit' name='submit' value='Buy Coin' class='settingsformsubmit' tabindex='36'>
  </form>
  <h2 align="center">Coin Price History</h2><?php
}

function displayAlertForm(){
  $userID = $_SESSION['ID'];
  ?> <h1>Coin Alert</h1>
  <h2>Enter Price</h2>
  <form action='ManualBuy.php?manualAlert=Yes' method='post'>
    Coin: <input type="text" name="coinAltTxt" value="<?php echo $GLOBALS['coin']; ?>"><br>
    <select name="greaterThanSelect">
      <option value=">" name='greaterThanOpt'>></option>
      <option value="<" name='lessThanOpt'><</option>

    Coin Price: <input type="text" name="coinPriceAltTxt" value="<?php echo $GLOBALS['cost']; ?>"> <br>
    BaseCurrency: <input type="text" name="BaseCurTxt" value="<?php echo $GLOBALS['baseCurrency']; ?>" style='color:Gray' readonly ><br>
    CoinID: <input type="text" name="CoinIDTxt" value="<?php echo $GLOBALS['coinID']; ?>" style='color:Gray' readonly ><br>
    UserID: <input type="text" name="UserIDTxt" value="<?php echo $userID; ?>" style='color:Gray' readonly ><br>
    <input type='submit' name='submit' value='Set Alert' class='settingsformsubmit' tabindex='36'>
  </form>
  <?php
}






    //include header template
    require('layout/footer.php');
    ?>
  </body>
</html>
