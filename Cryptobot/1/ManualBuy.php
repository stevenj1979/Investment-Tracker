<?php
//require('includes/config.php');
include_once ('/home/stevenj1979/SQLData.php');
include '../includes/newConfig.php';

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
  $coinStats = getCoinStats($_GET['coin']);
  echo "Coin is set ".$_GET['coin'];
  $coin = trim($_GET['coin']);
  $cost = trim($_GET['coinPrice']);
  $baseCurrency = trim($_GET['baseCurrency']);
  $coinID = trim($_GET['coinID']);

  //$active = trim($_GET['y']);
}

if(isset($_POST['coinTxt'])){
//if($_POST['manualPrice'] == 'Yes'){
  date_default_timezone_set('Asia/Dubai');
  $date = date("Y-m-d H:i:s", time());
  //$_SESSION['coin'] = $_post['coinTxt'];
  $salePrice = number_format((float)$_post['coinPriceTxt'], 8, '.', ''); $coin = $_post['coinTxt']; $baseCurrency = $_post['BaseCurTxt'];
  $coinID = $_post['CoinIDTxt']; $userID = $_SESSION['ID'];
  $TimeToCancelBuyMins = $_post['TimeToCancelBuyMinsTxt'];
  $BTCBuyAmount = $_POST['costTxt']; $cost = $GLOBALS['cost'];
  $userConfig = getUserConfig($userID);
  $UserName = $userConfig[0][0]; $APIKey = $userConfig[0][1]; $APISecret = $userConfig[0][2]; $Email = $userConfig[0][3];
  $BTCBuyAmount = $userConfig[0][4]; $AvgCoinPrice = $coinStats[0][1]; $MaxCoinPrice = $coinStats[0][2]; $MinCoinPrice = $coinStats[0][3];
  if ($_POST['priceSelect'] == 'manual'){
    $salePrice = $_POST['coinPriceTxt'];
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

  //buyCoins($APIKey,$APISecret,$coin,$Email,$userID,$date,$baseCurrency,1,1,$BTCBuyAmount,99999,$UserName,$coinID,0,0,1,$TimeToCancelBuyMins,'ALL');
  echo "buyCoins($APIKey,$APISecret,$coin,$Email,$userID,$date,$baseCurrency,1,1,$BTCBuyAmount,99999,$UserName,$coinID,0,0,1,$TimeToCancelBuyMins,'ALL');";

  //header('Location: BuyCoins.php');
}



function getUserConfig($userID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `UserName`,`APIKey`,`APISecret`,`Email`,`BTCBuyAmount` FROM `UserConfigView` WHERE `ID` = $userID";
  //echo $sql;
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['UserName'],$row['APIKey'],$row['APISecret'],$row['Email'],$row['BTCBuyAmount']);}
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



$userID = $_SESSION['ID'];
?>
<div class="header">
  <table><TH><table class="CompanyName"><td rowspan="2" class="CompanyName"><img src='Images/CBLogoSmall.png' width="40"></td><td class="CompanyName"><div class="Crypto">Crypto</Div><td><tr class="CompanyName">
      <td class="CompanyName"><Div class="Bot">Bot</Div></td></table></TH><TH>: Logged in as:</th><th> <i class="glyphicon glyphicon-user"></i>  <?php echo $_SESSION['username'] ?></th></Table><br>
  </div>
  <div class="topnav">
    <a href="Dashboard.php">Dashboard</a>
    <a href="Transactions.php">Transactions</a>
    <a href="Stats.php">Stats</a>
    <a href="BuyCoins.php" class="active">Buy Coins</a>
    <a href="SellCoins.php">Sell Coins</a>
    <a href="Profit.php">Profit</a>
    <a href="bittrexOrders.php">Bittrex Orders</a>
    <a href="Settings.php">Settings</a><?php
    if ($_SESSION['AccountType']==1){echo "<a href='AdminSettings.php'>Admin Settings</a>";}
    $apiKey = $GLOBALS['APIKey']; $apiSecret = $GLOBALS['APISecret'] ; $baseCurrency = $GLOBALS['baseCurrency'];
    $BTCBalance = bittrexbalance($apiKey, $apiSecret,$baseCurrency);
    echo "BTCBalance = bittrexbalance($apiKey, $apiSecret,$baseCurrency)";

    ?>
  </div>
  <div class="row">
       <div class="column side">
          &nbsp
      </div>
      <div class="column middle">
          <h1>Manual Buy Coin</h1>
          <h2>Enter Price</h2>
          <form action='ManualBuy.php?manualPrice=Yes' method='post'>
            Coin: <input type="text" name="coinTxt" value="<?php echo $GLOBALS['coin']; ?>"><br>
            BTC Buy Amount: <input type="text" name="costTxt" value="<?php echo $GLOBALS['BTCBuyAmount']; ?>"> 0 equals full bittrex balance | Current Balance is : <?php echo $BTCBalance ?> <br>
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
              $tmpPrice = number_format((float)$GLOBALS['cost']-(($GLOBALS['cost']/100 )*1), 8, '.', '');
              $_SESSION['salePrice'] = round($tmpPrice,8, PHP_ROUND_HALF_DOWN);
              echo "<p> 1% = ".number_format((float)$salePrice, 8, '.', '');
              ?>
            <p>Max Coin Price = <?php  echo $GLOBALS['MaxCoinPrice']; ?>
            <p>Min Coin Price = <?php  echo $GLOBALS['MinCoinPrice']; ?>
            BaseCurrency: <input type="text" name="BaseCurTxt" value="<?php echo $GLOBALS['baseCurrency']; ?>" style='color:Gray' readonly ><br>
            CoinID: <input type="text" name="CoinIDTxt" value="<?php echo $GLOBALS['coinID']; ?>" style='color:Gray' readonly ><br>
            <input type='submit' name='submit' value='Buy Coin' class='settingsformsubmit' tabindex='36'>
          </form>
          <h2 align="center">Coin Price History</h2>
          <div id="visualization" style="width: 600px; height: 400px;"></div>
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
  </body>
</html>
