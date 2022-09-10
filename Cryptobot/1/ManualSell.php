<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<?php include_once ('../../../../SQLData.php');
include '../includes/newConfig.php';?>
<style>
<?php include 'style/style.css'; ?>
</style> <?php
//if(!$user->is_logged_in()){ header('Location: login.php'); exit(); }
echo isset($_GET['coin'])."-".$_GET['manualPrice']."-".isset($_GET['manualPrice'])."-".isset($_GET['coinTxt']);
if(isset($_GET['manSell'])){
  //collect values from the url
  echo "Coin is set ".$_GET['coin']. " TransactionID : ".$_GET['transactionID'];
  $coin = trim($_GET['coin']);
  $amount = trim($_GET['amount']);
  $cost = trim($_GET['cost']);
  $baseCurrency = trim($_GET['baseCurrency']);
  $transactionID = trim($_GET['transactionID']);
  $orderNo = trim($_GET['orderNo']);
  $salePrice = trim($_GET['salePrice']);
  //$active = trim($_GET['y']);
}

if(isset($_GET['manSave'])){
  $transactionID = trim($_GET['transactionID']);
  setTransStatus("Open",$transactionID);
  header('Location: SellCoins.php');
}

if(isset($_GET['manReopen'])){
  $transactionID = trim($_GET['transactionID']);
  setTransStatus("Saving",$transactionID);
  header('Location: SellCoins_Saving.php');
}



if(isset($_GET['trackCoin'])){
  $baseCurrency = trim($_GET['baseCurrency']);
  $transactionID = trim($_GET['transactionID']);
  $salePrice = trim($_GET['salePrice']);
  $userID = trim($_GET['userID']);
  //echo "<BR> newTrackingSellCoins($salePrice, $userID,$transactionID,1, 1,0,0,3);";
  newTrackingSellCoins($salePrice, $userID,$transactionID,1, 1,0,0,3);
  setTransactionPending($transactionID);
  header('Location: SellCoins.php');
}

if(isset($_GET['coinTxt'])){
  echo "manualPrice is set ".$_POST['manualPrice'];
  date_default_timezone_set('Asia/Dubai');
  $date = date("Y-m-d H:i:s", time());
  $coin = $_GET['coinTxt']; $amount = $_GET['amountTxt']; $baseCurrency = $_GET['BaseCurTxt'];
  $orderNo = $_GET['OrderNoTxt']; $cost = $_GET['origCostTxt']; $transactionID = $_GET['TranIDTxt'];
  Echo "<BR> TransactionID $transactionID | $baseCurrency | $orderNo | ";
  $userConfig = getTrackingSellCoinsMan($transactionID);
  $livePrice = $userConfig[0][19];$coinID = $userConfig[0][2];$type = $userConfig[0][1];
  $userName = $userConfig[0][38]; $email = $userConfig[0][37];$apikey = $userConfig[0][39]; $apisecret = $userConfig[0][40]; $KEK = $userConfig[0][42];
  $userID = $userConfig[0][3];
  if (!Empty($KEK)){$apisecret = decrypt($KEK,$userConfig[0][40]);}
  $bitPrice = number_format((float)($bitPrice), 8, '.', '');
  $profit = $livePrice/$cost;
  if ($_GET['priceSelect'] == 'manual'){
    $salePrice = $_GET['costTxt'];
  }elseif ($_GET['priceSelect'] == 0.25){
    $tempPrice = (($cost/100 )*0.25)+$cost;
    $salePrice = number_format((float)round($tempPrice,8, PHP_ROUND_HALF_UP), 8, '.', '');
  }elseif ($_GET['priceSelect'] == 0.5){
    $tempPrice = (($cost/100 )*0.5)+$cost;
    $salePrice = number_format((float)round($tempPrice,8, PHP_ROUND_HALF_UP), 8, '.', '');
  }elseif ($_GET['priceSelect'] == 1){
    $tempPrice = (($cost/100 )*1)+$cost;
    $salePrice = number_format((float)round($tempPrice,8, PHP_ROUND_HALF_UP), 8, '.', '');
  }elseif ($_GET['priceSelect'] == 1.5){
    $tempPrice = (($cost/100 )*1.5)+$cost;
    $salePrice = number_format((float)round($tempPrice,8, PHP_ROUND_HALF_UP), 8, '.', '');
  }elseif ($_GET['priceSelect'] == 2){
    $tempPrice = (($cost/100 )*2)+$cost;
    $salePrice = number_format((float)round($tempPrice,8, PHP_ROUND_HALF_UP), 8, '.', '');
  }elseif ($_GET['priceSelect'] == 2.5){
    $tempPrice = (($cost/100 )*2.5)+$cost;
    $salePrice = number_format((float)round($tempPrice,8, PHP_ROUND_HALF_UP), 8, '.', '');
  }elseif ($_GET['priceSelect'] == 3){
    $tempPrice = (($cost/100 )*3)+$cost;
    $salePrice = number_format((float)round($tempPrice,8, PHP_ROUND_HALF_UP), 8, '.', '');
  }elseif ($_GET['priceSelect'] == 5){
    $tempPrice = (($cost/100 )*5)+$cost;
    echo "<BR> Temp Price $tempPrice";
    $salePrice = number_format((float)round($tempPrice,8, PHP_ROUND_HALF_UP), 8, '.', '');
    echo "<BR> session price ".$GLOBALS['salePrice'];
  }elseif ($_GET['priceSelect'] == 10){
    $tempPrice = (($cost/100 )*10)+$cost;
    $salePrice = number_format((float)round($tempPrice,8, PHP_ROUND_HALF_UP), 8, '.', '');
  }elseif ($_GET['priceSelect'] == 20){
    $tempPrice = (($cost/100 )*20)+$cost;
    $salePrice = number_format((float)round($tempPrice,8, PHP_ROUND_HALF_UP), 8, '.', '');
  }
  sellCoins($apikey, $apisecret, $coin, $email, $userID, 0, $date,$baseCurrency, 1, 1, 552,$userName, $orderNo ,$amount,$cost,$transactionID,$coinID,0,0,$salePrice,'Sell');
  //echo "sellCoins($apikey, $apisecret, $coin, $email, $userID, 0, $date,$baseCurrency, 1, 1, 99999,$userName, $orderNo ,$amount,$cost,$transactionID,$coinID,0,0,$salePrice);";
  header('Location: SellCoins.php');
}
elseif (isset($_GET['splitCoin'])){

    $coin = $_GET['splitCoin']; $transactionID = $_GET['transactionID']; $ruleIDBTSell = "9999";
    $amount = $_GET['amount']; $qtySold = round($amount/2,8); $orderQtyRemaining = $amount-$qtySold;
    $newOrderNo = "ORD".$coin.date("YmdHis", time()).$ruleIDBTSell;

    bittrexCopyTransNewAmount($transactionID,$qtySold,$orderQtyRemaining,$newOrderNo);
    header('Location: SellCoins.php');
}

function bittrexbalanceMan($apikey, $apisecret){
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


function bittrexSellAddMan($coinID, $transactionID, $userID, $type, $bittrexRef, $status, $bitPrice, $ruleID){
  $conn = getSQLConn(rand(1,3));
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

function bittrexsellMan($apikey, $apisecret, $symbol, $quant, $rate){
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
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT
    `IDTr`,`Type`,`CoinID`,`UserID`,`CoinPrice`,`Amount`,`Status`,`OrderDate`,`CompletionDate`,`BittrexID`,`OrderNo`,`Symbol`,`LastBuyOrders`,`LiveBuyOrders`,`BuyOrdersPctChange`,`LastMarketCap`,`LiveMarketCap`,`MarketCapPctChange`,`LastCoinPrice`,`LiveCoinPrice`,`CoinPricePctChange`,
    `LastSellOrders`,`LiveSellOrders`,`SellOrdersPctChange`,`LastVolume`,`LiveVolume`,`VolumePctChange`,`Last1HrChange`,`Live1HrChange`,`Hr1ChangePctChange`,`Last24HrChange`,`Live24HrChange`,`Hr24ChangePctChange`,`Last7DChange`,`Live7DChange`,`D7ChangePctChange`,`BaseCurrency`,`Email`,`UserName`,`APIKey`,`APISecret`,`BTCBuyAmount`
    ,`KEK`
      FROM `View5_SellCoins` WHERE `IDTr` = $transactionID";
  $result = $conn->query($sql);
  print_r($sql);

  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['IDTr'],$row['Type'],$row['CoinID'],$row['UserID'],$row['CoinPrice'],$row['Amount'],$row['Status'],$row['OrderDate'],$row['CompletionDate'],$row['BittrexID'],$row['OrderNo'],
      $row['Symbol'],$row['LastBuyOrders'],$row['LiveBuyOrders'],$row['BuyOrdersPctChange'],$row['LastMarketCap'],$row['LiveMarketCap'],$row['MarketCapPctChange'],$row['LastCoinPrice'],$row['LiveCoinPrice'],
      $row['CoinPricePctChange'],$row['LastSellOrders'],$row['LiveSellOrders'],$row['SellOrdersPctChange'],$row['LastVolume'],$row['LiveVolume'],$row['VolumePctChange'],$row['Last1HrChange'],$row['Live1HrChange'],
      $row['Hr1ChangePctChange'],$row['Last24HrChange'],$row['Live24HrChange'],$row['Hr24ChangePctChange'],$row['Last7DChange'],$row['Live7DChange'],$row['D7ChangePctChange'],$row['BaseCurrency'],$row['Email'],$row['UserName'],
      $row['APIKey'],$row['APISecret'],$row['BTCBuyAmount'],$row['KEK']);
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
displayHeader(4);?>

<h1>Manual Sell Coin</h1>
<h2>Enter Price</h2>
                <form action='ManualSell.php?manualPrice=Yes' method='get'>
                Coin: <input type="text" name="coinTxt" value="<?php echo $GLOBALS['coin']; ?>"><br>
                Amount: <input type="text" name="amountTxt" value="<?php echo $GLOBALS['amount']; ?>"><br>
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
                Cost: <input type="text" name="costTxt" value="<?php echo $GLOBALS['salePrice']; ?>"><br>
                TransactionID: <input type="text" name="TranIDTxt" value="<?php echo $GLOBALS['transactionID']; ?>" style='color:Gray' readonly ><br>
                BaseCurrency: <input type="text" name="BaseCurTxt" value="<?php echo $GLOBALS['baseCurrency']; ?>" style='color:Gray' readonly ><br>
                OrderNo: <input type="text" name="OrderNoTxt" value="<?php echo $GLOBALS['orderNo']; ?>" style='color:Gray' readonly ><br>
                OriginalCost: <input type="text" name="origCostTxt" value="<?php echo $GLOBALS['cost']; ?>" style='color:Gray' readonly ><br>
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
