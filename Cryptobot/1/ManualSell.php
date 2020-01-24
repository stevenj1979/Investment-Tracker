<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<?php require('includes/config.php');?>
<style>
<?php include 'style/style.css'; ?>
</style> <?php
include '../../../NewSQLData.php';
echo isset($_GET['coin'])."-".$_GET['manualPrice']."-".isset($_GET['manualPrice'])."-".isset($_GET['coinTxt']);
if(isset($_GET['coin'])){
  //collect values from the url
  echo "Coin is set ".$_GET['coin'];
  $_SESSION['coin'] = trim($_GET['coin']);
  $_SESSION['amount'] = trim($_GET['amount']);
  $_SESSION['cost'] = trim($_GET['cost']);
  $_SESSION['baseCurrency'] = trim($_GET['baseCurrency']);
  $_SESSION['transactionID'] = trim($_GET['transacationID']);
  $_SESSION['orderNo'] = trim($_GET['orderNo']);
  $_SESSION['salePrice'] = trim($_GET['salePrice']);
  //$active = trim($_GET['y']);
}

if(isset($_GET['coinTxt'])){
  echo "manualPrice is set ".$_POST['manualPrice'];
  $_SESSION['coin'] = $_GET['coinTxt'];
  $_SESSION['amount'] = $_GET['amountTxt'];
  if ($_GET['priceSelect'] == 'manual'){
    $_SESSION['salePrice'] = $_GET['costTxt'];
  }elseif ($_GET['priceSelect'] == 0.25){
    $tempPrice = (($_SESSION['cost']/100 )*0.25)+$_SESSION['cost'];
    $_SESSION['salePrice'] = number_format((float)round($tempPrice,8, PHP_ROUND_HALF_UP), 8, '.', '');
  }elseif ($_GET['priceSelect'] == 0.5){
    $tempPrice = (($_SESSION['cost']/100 )*0.5)+$_SESSION['cost'];
    $_SESSION['salePrice'] = number_format((float)round($tempPrice,8, PHP_ROUND_HALF_UP), 8, '.', '');
  }elseif ($_GET['priceSelect'] == 1){
    $tempPrice = (($_SESSION['cost']/100 )*1)+$_SESSION['cost'];
    $_SESSION['salePrice'] = number_format((float)round($tempPrice,8, PHP_ROUND_HALF_UP), 8, '.', '');
  }elseif ($_GET['priceSelect'] == 1.5){
    $tempPrice = (($_SESSION['cost']/100 )*1.5)+$_SESSION['cost'];
    $_SESSION['salePrice'] = number_format((float)round($tempPrice,8, PHP_ROUND_HALF_UP), 8, '.', '');
  }elseif ($_GET['priceSelect'] == 2){
    $tempPrice = (($_SESSION['cost']/100 )*2)+$_SESSION['cost'];
    $_SESSION['salePrice'] = number_format((float)round($tempPrice,8, PHP_ROUND_HALF_UP), 8, '.', '');
  }elseif ($_GET['priceSelect'] == 2.5){
    $tempPrice = (($_SESSION['cost']/100 )*2.5)+$_SESSION['cost'];
    $_SESSION['salePrice'] = number_format((float)round($tempPrice,8, PHP_ROUND_HALF_UP), 8, '.', '');
  }elseif ($_GET['priceSelect'] == 3){
    $tempPrice = (($_SESSION['cost']/100 )*3)+$_SESSION['cost'];
    $_SESSION['salePrice'] = number_format((float)round($tempPrice,8, PHP_ROUND_HALF_UP), 8, '.', '');
  }elseif ($_GET['priceSelect'] == 5){
    $tempPrice = (($_SESSION['cost']/100 )*5)+$_SESSION['cost'];
    echo "<BR> Temp Price $tempPrice";
    $_SESSION['salePrice'] = number_format((float)round($tempPrice,8, PHP_ROUND_HALF_UP), 8, '.', '');
    echo "<BR> session price ".$_SESSION['salePrice'];
  }elseif ($_GET['priceSelect'] == 10){
    $tempPrice = (($_SESSION['cost']/100 )*10)+$_SESSION['cost'];
    $_SESSION['salePrice'] = number_format((float)round($tempPrice,8, PHP_ROUND_HALF_UP), 8, '.', '');
  }elseif ($_GET['priceSelect'] == 20){
    $tempPrice = (($_SESSION['cost']/100 )*20)+$_SESSION['cost'];
    $_SESSION['salePrice'] = number_format((float)round($tempPrice,8, PHP_ROUND_HALF_UP), 8, '.', '');
  }
  echo $_SESSION['coin']."_".$_SESSION['amount']."_".$_SESSION['cost']."_".$_SESSION['baseCurrency']."_".$_SESSION['transactionID']."_".$_SESSION['orderNo']."_".$_SESSION['salePrice'];
  //sellManualCoin($coin,$amount,$cost,$baseCurrency,$transactionID,$orderNo, $bitPrice){
  sellManualCoin($_SESSION['coin'],$_SESSION['amount'],$_SESSION['cost'],$_SESSION['baseCurrency'], $_SESSION['transactionID'], $_SESSION['orderNo'],$_SESSION['salePrice']);
  header('Location: SellCoins.php');
}

function bittrexbalance($apikey, $apisecret){
    $nonce=time();
    $uri='https://bittrex.com/api/v1.1/account/getbalance?apikey='.$apikey.'&currency=BTC&nonce='.$nonce;
    $sign=hash_hmac('sha512',$uri,$apisecret);
    $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
    $balance = $obj["result"]["Available"];
    return $balance;
}

function getNewSQL($number){
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
}

function bittrexSellAdd($coinID, $transactionID, $userID, $type, $bittrexRef, $status, $bitPrice, $ruleID){
  $conn = getNewSQL(rand(1,4));
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "call AddBittrexSell($coinID, $transactionID, $userID, '$type', '$bittrexRef', '$status', $bitPrice, $ruleID);";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function bittrexsell($apikey, $apisecret, $symbol, $quant, $rate){
    $nonce=time();
    $uri='https://bittrex.com/api/v1.1/market/selllimit?apikey='.$apikey.'&market=BTC-'.$symbol.'&quantity='.$quant.'&rate='.$rate.'&nonce='.$nonce;
    echo "<BR>$uri<BR>";
    $sign=hash_hmac('sha512',$uri,$apisecret);
    $ch = curl_init($uri);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
    return $obj;
}


function getTrackingSellCoinsMan($transactionID){
  $conn = getNewSQL(rand(1,4));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT
`ID`,`Type`,`CoinID`,`UserID`,`CoinPrice`,`Amount`,`Status`,`OrderDate`,`CompletionDate`,`BittrexID`,`OrderNo`,`Symbol`,`LastBuyOrders`,`LiveBuyOrders`,`BuyOrdersPctChange`,`LastMarketCap`,`LiveMarketCap`,`MarketCapPctChange`,`LastCoinPrice`,`LiveCoinPrice`,`CoinPricePctChange`,
`LastSellOrders`,`LiveSellOrders`,`SellOrdersPctChange`,`LastVolume`,`LiveVolume`,`VolumePctChange`,`Last1HrChange`,`Live1HrChange`,`Hr1PctChange`,`Last24HrChange`,`Live24HrChange`,`Hr24PctChange`,`Last7DChange`,`Live7DChange`,`D7PctChange`,`BaseCurrency`,`Email`,`UserName`,`APIKey`,`APISecret`,`BTCBuyAmount`
FROM `ManualSell` WHERE `ID` = $transactionID";
  $result = $conn->query($sql);
  print_r($sql);

  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['Type'],$row['CoinID'],$row['UserID'],$row['CoinPrice'],$row['Amount'],$row['Status'],$row['OrderDate'],$row['CompletionDate'],$row['BittrexID'],$row['OrderNo'],
      $row['Symbol'],$row['LastBuyOrders'],$row['LiveBuyOrders'],$row['BuyOrdersPctChange'],$row['LastMarketCap'],$row['LiveMarketCap'],$row['MarketCapPctChange'],$row['LastCoinPrice'],$row['LiveCoinPrice'],
      $row['CoinPricePctChange'],$row['LastSellOrders'],$row['LiveSellOrders'],$row['SellOrdersPctChange'],$row['LastVolume'],$row['LiveVolume'],$row['VolumePctChange'],$row['Last1HrChange'],$row['Live1HrChange'],
      $row['Hr1PctChange'],$row['Last24HrChange'],$row['Live24HrChange'],$row['Hr24PctChange'],$row['Last7DChange'],$row['Live7DChange'],$row['D7PctChange'],$row['BaseCurrency'],$row['Email'],$row['UserName'],
      $row['APIKey'],$row['APISecret'],$row['BTCBuyAmount']);
  }
  $conn->close();
  return $tempAry;
}



function sellManualCoin($coin,$amount,$cost,$baseCurrency,$transactionID,$orderNo, $bitPrice){
  date_default_timezone_set('Asia/Dubai');
  $date = date("Y-m-d H:i:s", time());
  $userID = $_SESSION['ID'];
    //Get Tracking $coins
    echo "<br> OMG I'm HERE!!";
      $userConfig = getTrackingSellCoinsMan($transactionID);
      $livePrice = $userConfig[0][19];$coinID = $userConfig[0][2];$type = $userConfig[0][1];
      $userName = $userConfig[0][38]; $email = $userConfig[0][37];$apikey = $userConfig[0][39]; $apisecret = $userConfig[0][40];
      $bitPrice = number_format((float)($bitPrice), 8, '.', '');
      $profit = $livePrice/$cost;

      $obj = bittrexsell($apikey, $apisecret, $coin ,$amount, $bitPrice);
      echo "<BR>bittrexsell($apikey, $apisecret, $coin ,$amount, $bitPrice)";

      //$bittrexRef = $obj['result'][0]['uuid'];
      $bittrexRef = $obj["result"]["uuid"];
      echo "<br>$bittrexRef";
      $status = $obj["success"];
      echo "<br>$status";
      if ($status == 1){
        //updateSQL($coin, $amount, $bitPrice, $cost, $baseCurrency,$date,$transactionID);
        echo "<BR>bittrexSellAdd($coinID, $transactionID, $userID, 'Sell', $bittrexRef, $status, $bitPrice, 0);";
        //writeBittrexAction($apikey, $apisecret, $coin, $email, $userID, 0, $date,$baseCurrency, 1, 1, "_M",$userName, $orderNo,$amount,$cost,'Sell',$bittrexRef,$status,$bitPrice,$transactionID);
        bittrexSellAdd($coinID, $transactionID, $userID, 'Sell', $bittrexRef, $status, $bitPrice, 0);
      }


}


echo isset($_GET['coin'])."_".isset($_POST['manualPrice']);
?>
<div class="header">
  <table><TH><table class="CompanyName"><td rowspan="2" class="CompanyName"><img src='Images/CBLogoSmall.png' width="40"></td><td class="CompanyName"><div class="Crypto">Crypto</Div><td><tr class="CompanyName">
      <td class="CompanyName"><Div class="Bot">Bot</Div></td></table></TH><TH>: Logged in as:</th><th> <i class="glyphicon glyphicon-user"></i>  <?php echo $_SESSION['username'] ?></th></Table><br>
  </div>
  <div class="topnav">
    <a href="Dashboard.php">Dashboard</a>
    <a href="Transactions.php">Transactions</a>
    <a href="Stats.php">Stats</a>
    <a href="BuyCoins.php">Buy Coins</a>
    <a href="SellCoins.php" class="active">Sell Coins</a>
    <a href="Profit.php">Profit</a>
    <a href="bittrexOrders.php">Bittrex Orders</a>
    <a href="Settings.php">Settings</a><?php
    if ($_SESSION['AccountType']==1){echo "<a href='AdminSettings.php'>Admin Settings</a>";}
    ?>
  </div>
<div class="row">
       <div class="column side">
          &nbsp
      </div>
      <div class="column middle">
<h1>Manual Sell Coin</h1>
<h2>Enter Price</h2>
                <form action='ManualSell.php?manualPrice=Yes' method='get'>
                Coin: <input type="text" name="coinTxt" value="<?php echo $_SESSION['coin']; ?>"><br>
                Amount: <input type="text" name="amountTxt" value="<?php echo $_SESSION['amount']; ?>"><br>
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
                Cost: <input type="text" name="costTxt" value="<?php echo $_SESSION['salePrice']; ?>"><br>
                <input type='submit' name='submit' value='Sell Coin' class='settingsformsubmit' tabindex='36'>
                </form>
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
</html>
