<?php
//require('includes/config.php');
include '../../../NewSQLData.php';
//$active = trim($_GET['y']);
include '../includes/newConfig.php';

?>
<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<?php require('includes/config.php');?>
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
  var newURL = "http://www.investment-tracker.net/content/CryptoBot/1/getCoinChart.php?coinID=ETH";
  var symbol = "<?php echo $_GET['coin']; ?>";
  var json = $.ajax({
    url: "http://www.investment-tracker.net/content/CryptoBot/1/getCoinChart.php?coinID=" + symbol , // make this url point to the data file
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
  $_SESSION['coin'] = trim($_GET['coin']);
  $_SESSION['cost'] = trim($_GET['coinPrice']);
  $_SESSION['baseCurrency'] = trim($_GET['baseCurrency']);
  $_SESSION['coinID'] = trim($_GET['coinID']);
  $_SESSION['UserName'] = $userConfig[0][0];
  $_SESSION['APIKey'] = $userConfig[0][1];
  $_SESSION['APISecret'] = $userConfig[0][2];
  $_SESSION['Email'] = $userConfig[0][3];
  $_SESSION['BTCBuyAmount'] = $userConfig[0][4];
  $_SESSION['AvgCoinPrice'] = $coinStats[0][1];
  $_SESSION['MaxCoinPrice'] = $coinStats[0][2];
  $_SESSION['MinCoinPrice'] = $coinStats[0][3];
  //$active = trim($_GET['y']);
}

if(isset($_POST['coinTxt'])){
//if($_POST['manualPrice'] == 'Yes'){
  date_default_timezone_set('Asia/Dubai');
  $date = date("Y-m-d H:i:s", time());
  //$_SESSION['coin'] = $_post['coinTxt'];
  $_SESSION['salePrice'] = number_format((float)$_post['coinPriceTxt'], 8, '.', '');
  $_SESSION['TimeToCancelBuyMinsTxt'] = $_post['TimeToCancelBuyMinsTxt'];
  $_SESSION['BTCBuyAmount'] = $_POST['costTxt'];
  if ($_POST['priceSelect'] == 'manual'){
    $_SESSION['salePrice'] = $_POST['coinPriceTxt'];
  }elseif ($_POST['priceSelect'] == 0.25){
    $tmpPrice = number_format((float)$_SESSION['cost']-(($_SESSION['cost']/100 )*0.25), 8, '.', '');
    $_SESSION['salePrice'] = round($tmpPrice,8, PHP_ROUND_HALF_DOWN);
  }elseif ($_POST['priceSelect'] == 0.5){
    $tmpPrice = number_format((float)$_SESSION['cost']-(($_SESSION['cost']/100 )*0.5), 8, '.', '');
    $_SESSION['salePrice'] = round($tmpPrice,8, PHP_ROUND_HALF_DOWN);
  }elseif ($_POST['priceSelect'] == 1){
    $tmpPrice = number_format((float)$_SESSION['cost']-(($_SESSION['cost']/100 )*1), 8, '.', '');
    $_SESSION['salePrice'] = round($tmpPrice,8, PHP_ROUND_HALF_DOWN);
  }elseif ($_POST['priceSelect'] == 1.5){
    $tmpPrice = number_format((float)$_SESSION['cost']-(($_SESSION['cost']/100 )*1.5), 8, '.', '');
    $_SESSION['salePrice'] = round($tmpPrice,8, PHP_ROUND_HALF_DOWN);
  }elseif ($_POST['priceSelect'] == 2){
    $tmpPrice = number_format((float)$_SESSION['cost']-(($_SESSION['cost']/100 )*2), 8, '.', '');
    $_SESSION['salePrice'] = round($tmpPrice,8, PHP_ROUND_HALF_DOWN);
  }elseif ($_POST['priceSelect'] == 2.5){
    $tmpPrice = number_format((float)$_SESSION['cost']-(($_SESSION['cost']/100 )*2.5), 8, '.', '');
    $_SESSION['salePrice'] = round($tmpPrice,8, PHP_ROUND_HALF_DOWN);
  }elseif ($_POST['priceSelect'] == 3){
    $tmpPrice = number_format((float)$_SESSION['cost']-(($_SESSION['cost']/100 )*3), 8, '.', '');
    $_SESSION['salePrice'] = round($tmpPrice,8, PHP_ROUND_HALF_DOWN);
  }elseif ($_POST['priceSelect'] == 5){
    $tmpPrice = number_format((float)$_SESSION['cost']-(($_SESSION['cost']/100 )*5), 8, '.', '');
    $_SESSION['salePrice'] = round($tmpPrice,8, PHP_ROUND_HALF_DOWN);
  }elseif ($_POST['priceSelect'] == 10){
    $tmpPrice = number_format((float)$_SESSION['cost']-(($_SESSION['cost']/100 )*10), 8, '.', '');
    $_SESSION['salePrice'] = round($tmpPrice,8, PHP_ROUND_HALF_DOWN);
  }elseif ($_POST['priceSelect'] == 20){
    $tmpPrice = number_format((float)$_SESSION['cost']-(($_SESSION['cost']/100 )*20), 8, '.', '');
    $_SESSION['salePrice'] = round($tmpPrice,8, PHP_ROUND_HALF_DOWN);
  }

  buyCoins($_SESSION['APIKey'],$_SESSION['APISecret'],$_SESSION['coin'],$_SESSION['Email'],$_SESSION['ID'],$date,$_SESSION['baseCurrency']
  ,1,1,$_SESSION['BTCBuyAmount'],99999,$_SESSION['UserName'],$_SESSION['coinID'],0,0,1,$_SESSION['TimeToCancelBuyMinsTxt']);
  //header('Location: BuyCoins.php');
}

/*function getNewSQL($number){
  $servername = "localhost";
  $dbname = "NewCryptoBotDb";

  switch ($number) {
    case 1:
        $username = "jenkinss";
        $password = "Butt3rcup23";
        break;
    case 2:
        $username = "cryptoBotWeb1";
        $password = "UnYpH7HkgK[N";
        break;
    case 3:
        $username = "cryptoBotWeb2";
        $password = "U0I^=bBc0jkf";
        break;
    case 4:
        $username = "autoCryptoBot";
        $password = "@c5WmgTgjtR+";
        break;
    default:
        $username = "cryptoBotWeb3";
        $password = "XcE)n7GJ-Twr";
    }
    $conn = new mysqli($servername, $username, $password, $dbname);
    return $conn;
}*/

function getUserConfig($userID){
  $tempAry = [];
  $conn = getNewSQL(rand(1,4));
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
  $conn = getNewSQL(rand(1,4));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `Symbol`,`AvgCoinPrice`,`MaxCoinPrice`, `MinCoinPrice` FROM `AvgCoinPriceTableWeb` WHERE `Symbol` = '$symbol'";
  //echo $sql;
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['Symbol'],$row['AvgCoinPrice'],$row['MaxCoinPrice'],$row['MinCoinPrice']);}
  $conn->close();
  return $tempAry;

}

/*function bittrexbalance($apikey, $apisecret, $base ){
    $nonce=time();
    $uri='https://bittrex.com/api/v1.1/account/getbalance?apikey='.$apikey.'&currency='.$base.'&nonce='.$nonce;
    $sign=hash_hmac('sha512',$uri,$apisecret);
    $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
    $balance = $obj["result"]["Available"];
    return $balance;
}*/

/*function getMinTradeAmount($coin, $baseCurrency, $apisecret){
  $minTradeSize = getMinTrade($apisecret);
  $tradeArraySize = count($minTradeSize['result']);
  //print_r($tradeArraySize);
  for($y = 0; $y < $tradeArraySize; $y++) {
    if($minTradeSize['result'][$y]['MarketCurrency']==$coin && $minTradeSize['result'][$y]['BaseCurrency']==$baseCurrency){
      $minTradeAmount= $minTradeSize['result'][$y]['MinTradeSize'];
      return $minTradeAmount;
      exit;
    }
  }
}*/

/*function getMinTrade($apisecret){
  $nonce=time();
  //$uri='https://bittrex.com/api/v1.1/account/getbalance?apikey='.$apikey.'&currency=BTC&nonce='.$nonce;
  $uri="https://bittrex.com/api/v1.1/public/getmarkets";
  $sign=hash_hmac('sha512',$uri,$apisecret);
  $ch = curl_init($uri);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $execResult = curl_exec($ch);
  $obj = json_decode($execResult, true);
  //$balance = $obj["result"]["MinTradeSize"];
  return $obj;
}*/

/*function bittrexCoinPrice($apikey, $apisecret, $baseCoin, $coin){
      $nonce=time();
      $uri='https://bittrex.com/api/v1.1/public/getticker?market='.$baseCoin.'-'.$coin;
      $sign=hash_hmac('sha512',$uri,$apisecret);
      $ch = curl_init($uri);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $execResult = curl_exec($ch);
      $obj = json_decode($execResult, true);
      $balance = $obj["result"]["Last"];
      return $balance;
}*/

/*function bittrexBuyAdd($coinID, $userID, $type, $bittrexRef, $status, $ruleID, $cost, $amount, $orderNo,$timeToCancelBuyMins){
  $conn = getNewSQL(rand(1,4));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  //$sql = "call AddBittrexBuy($coinID, $userID, '$type', '$bittrexRef', '$status', $ruleID, $cost, $amount, '$orderNo');";
  $sql = "call addBittrexBuy($coinID, $userID, '$type', '$bittrexRef', '$status', $ruleID, $cost, $amount, '$orderNo',$timeToCancelBuyMins);";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}*/

/*function bittrexbuy($apikey, $apisecret, $symbol, $quant, $rate,$baseCurrency){
    $nonce=time();
    $uri='https://bittrex.com/api/v1.1/market/buylimit?apikey='.$apikey.'&market='.$baseCurrency.'-'.$symbol.'&quantity='.$quant.'&rate='.$rate.'&nonce='.$nonce;
    echo $uri."<BR>";
    $sign=hash_hmac('sha512',$uri,$apisecret);
    $ch = curl_init($uri);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
    return $obj;
}*/

/*function sendEmail($to, $symbol, $amount, $cost, $orderNo, $score, $subject, $user, $from){
    $body = "Dear ".$user.", <BR/>";
    $body .= "Congratulations you have bought the following Coin: "."<BR/>";
    $body .= "Coin: ".$symbol." Amount: ".$amount." Price: ".$cost."<BR/>";
    $body .= "Order Number: ".$orderNo."<BR/>";
    $body .= "Score: ".$score."<BR/>";
    $body .= "Kind Regards\nCryptoBot.";
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    //$headers .= 'From: Alerts <Alerts@Investment-Tracker.net>' . "\r\n";
    $headers .= "From:".$from."\r\n";
    $headers .= "To:".$to."\r\n";
    mail($to, $subject, wordwrap($body,70),$headers);
}*/

/*function buyManualCoins($apikey, $apisecret, $coin, $email, $userID, $date,$baseCurrency, $sendEmail, $buyCoin, $btcBuyAmount, $ruleID,$userName, $coinID,$CoinSellOffsetPct,$CoinSellOffsetEnabled,$buyType,$timeToCancelBuyMins){
  $BTCBalance = bittrexbalance($apikey, $apisecret,$baseCurrency);
  //get min trade
  if ($buyType == 2){
    //$btcBuyAmount = ($BTCBalance/100.28)*100;
      $btcBuyAmount = ($BTCBalance/100.28)*$btcBuyAmount;
  }
  if ($baseCurrency == 'USDT') {
    $bitPriceNew = number_format((float)(bittrexCoinPrice($apikey, $apisecret,$baseCurrency,$coin)), 8, '.', '');
    $btcBuyAmount = ($btcBuyAmount*$bitPriceNew);
  }

  $subject = "Coin Alert: ".$coin;
  $from = 'Coin Alert <alert@investment-tracker.net>';
  echo "Balance: $BTCBalance";
  $minTradeAmount = getMinTradeAmount($coin,$baseCurrency,$apisecret);
  if ($buyCoin) {
    $subject = "Coin Purchase: ".$coin;
    $from = 'Coin Purchase <purchase@investment-tracker.net>';
  }
  $btcwithCharge = (($btcBuyAmount/100)*0.25)+$btcBuyAmount;
  echo "$btcwithCharge = (($btcBuyAmount/100)*0.25)+$btcBuyAmount;";
  if ($BTCBalance >= $btcwithCharge) {
    echo "buy Coin - Balance Sufficient";
    $bitPrice = number_format((float)(bittrexCoinPrice($apikey, $apisecret,$baseCurrency,$coin)), 8, '.', '');
    if ($CoinSellOffsetEnabled == 1){

      $bitPrice = number_format((float)newPrice($bitPrice,$CoinSellOffsetPct, "Buy"), 8, '.', '');
    }
    //$livePrice = getLiveCoinPrice($tracking[$x][0]);
    //$avgCoinPrice = getAveragePrice($coin);
    //echo "<BR>AvgCoinPrice: ".$avgCoinPrice[0][0]." CoinPrice: ".$bitPrice;
    //if ($avgCoinPrice > $bitPrice){ return; }
    $quantity = Round($btcBuyAmount/$bitPrice,8,PHP_ROUND_HALF_UP);
    Echo "This is a test : bittrexbuy($apikey, $apisecret, $coin, $quantity, $bitPrice, $baseCurrency);";
    if ($quantity>$minTradeAmount){
        echo "Quantity above min trade amount";
        //buyCoins($apikey, $apisecret,$coin, $quantity, $bitPrice, $email,$minTradeAmount, $userID, $totalScore,$date, $baseCurrency);
        $orderNo = "ORD".$coin.date("YmdHis", time()).$ruleID;
        echo "Buy Coin = $buyCoin";
        if ($buyCoin){
          $obj = bittrexbuy($apikey, $apisecret, $coin, $quantity, $bitPrice, $baseCurrency);
          //writeSQLBuy($coin, $quantity, $bitPrice, $date, $orderNo, $userID, $baseCurrency);
          $bittrexRef = $obj["result"]["uuid"];
          $status = $obj["success"];
          if ($status == 1){
            echo "bittrexBuyAdd($coinID, $userID, 'Buy', $bittrexRef, $status, $ruleID, $bitPrice, $quantity, $orderNo);";
            date_default_timezone_set('Asia/Dubai');
            //$tmpTime = $timeToCancelBuyMins;
            //$newDate = date("Y-m-d H:i:s", time());
            //$current_date = date('Y-m-d H:i:s');
            //$newTime = date("Y-m-d H:i:s",strtotime('+'.$timeToCancelBuyMins.'Mins', strtotime($current_date)));
            //$buyCancelTime = strtotime( '+ 16 minute');
            bittrexBuyAdd($coinID, $userID, 'Buy', $bittrexRef, $status, $ruleID, $bitPrice, $quantity, $orderNo,$timeToCancelBuyMins);
            //writeBittrexActionBuy($coinID,$userID,'Buy',$bittrexRef,$date,$status,$bitPrice,$ruleID);
          }
        }
        if ($sendEmail==1 && $buyCoin ==0){
        //if ($sendEmail){
          sendEmail($email, $coin, $quantity, $bitPrice, $orderNo, $score, $subject,$userName, $from);
        }
    }else{ echo "<BR> BITTREX BALANCE INSUFFICIENT $quantity>$minTradeAmount"; }
  }
}*/


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
    $apiKey = $_SESSION['APIKey']; $apiSecret = $_SESSION['APISecret'] ; $baseCurrency = $_SESSION['baseCurrency'];
    $BTCBalance = bittrexbalance($apiKey, $apiSecret,$baseCurrency);
    echo "BTCBalance = bittrexbalance($apiKey, $apiSecret,$baseCurrency)";

    //http://www.investment-tracker.net/content/CryptoBot/1/ManualBuy.php?coin=BTC&baseCurrency=USDT&coinID=84&coinPrice=8147.90910738

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
            Coin: <input type="text" name="coinTxt" value="<?php echo $_SESSION['coin']; ?>"><br>
            BTC Buy Amount: <input type="text" name="costTxt" value="<?php echo $_SESSION['BTCBuyAmount']; ?>"> 0 equals full bittrex balance | Current Balance is : <?php echo $BTCBalance ?> <br>
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
            Coin Price: <input type="text" name="coinPriceTxt" value="<?php echo $_SESSION['cost']; ?>"> <br>
            Time To Cancel in Mins: <input type="text" name="TimeToCancelBuyMinsTxt" value=90> <br>
            <p>Average Coin Price = <?php echo $_SESSION['AvgCoinPrice'];
              $tmpPrice = number_format((float)$_SESSION['cost']-(($_SESSION['cost']/100 )*1), 8, '.', '');
              $_SESSION['salePrice'] = round($tmpPrice,8, PHP_ROUND_HALF_DOWN);
              echo "<p> 1% = ".number_format((float)$_SESSION['salePrice'], 8, '.', '');
              ?>
            <p>Max Coin Price = <?php  echo $_SESSION['MaxCoinPrice']; ?>
            <p>Min Coin Price = <?php  echo $_SESSION['MinCoinPrice']; ?>
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
