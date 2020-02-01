<?php

function bittrexbalance($apikey, $apisecret, $base ){
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
}

function bittrexbuy($apikey, $apisecret, $symbol, $quant, $rate){
    $nonce=time();
    $uri='https://bittrex.com/api/v1.1/market/buylimit?apikey='.$apikey.'&market=BTC-'.$symbol.'&quantity='.$quant.'&rate='.$rate.'&nonce='.$nonce;
    $sign=hash_hmac('sha512',$uri,$apisecret);
    $ch = curl_init($uri);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
    return $obj;
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

function getMinTrade($apisecret){
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
}

function writeSQLBuy($symbol, $amounttobuy, $cost, $date, $orderNo, $userID, $baseCurrency){
    //$servername = "sql7.freemysqlhosting.net";
    //$username = "sql7253140";
    //$password = "77YxhGXAH4";
    //$dbname = "sql7253140";
    //$servername = "localhost";
    //$username = "jenkinss";
    //$password = "Butt3rcup23";
    //$dbname = "CryptoBotDb";


    // Create connection
    //$conn = new mysqli($servername, $username, $password, $dbname);
    $conn = getSQL();
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "INSERT INTO Transaction (UserID, OrderNo, Symbol, BaseCurrency, Amount, Cost, TradeDate, Status) VALUES ($userID,'$orderNo','$symbol','$baseCurrency','$amounttobuy','$cost', '$date','Open')";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();

}

function sendEmail($to, $symbol, $amount, $cost, $orderNo, $score, $subject, $user, $from){
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
}
//sendSellEmail($email, $coin, $amount, $bitPrice, $orderNo, $score,$profitPct,$bitPrice-$cost,$subject,$userName);
function sendSellEmail($to, $symbol, $amount, $cost, $orderNo, $score, $profitPct, $profit, $subject, $user, $from){
    $body = "Dear ".$user.", <BR/>";
    $body .= "Congratulations you have sold the following Coin: "."<BR/>";
    $body .= "Coin: ".$symbol." Amount: ".$amount." Price: ".$cost."<BR/>";
    $body .= "Order Number: ".$orderNo."<BR/>";
    $body .= "Score: ".$score."<BR/>";
    $body .= "Profit %: ".$profitPct."<BR/>";
    $body .= "Profit BTC: ".$profit."<BR/>";
    $body .= "Kind Regards\nCryptoBot.";
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    //$headers .= 'From: Alerts <Alerts@Investment-Tracker.net>' . "\r\n";
    $headers .= "From:".$from."\r\n";
    $headers .= "To:".$to."\r\n";
    mail($to, $subject, wordwrap($body,70),$headers);

}

function sendTestEmail($to, $stat1, $stat2, $stat3, $stat4, $score){

    //$to = $row['Email'];
    //echo $row['Email'];
    $subject = "Test Email: ".$stat1;
    $body = "Dear Steven, <BR/>";
    $body .= "Congratulations you have bought the following Coin: "."<BR/>";
    $body .= "$stat2: ".$stat3." : ".$stat4."<BR/>";
    $body .= "The Score is ".$score."<BR/>";
    $body .= "Kind Regards\nCryptoBot.";
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $headers .= 'From: Alerts <Alerts@Investment-Tracker.net>' . "\r\n";
    $headers .= "To:".$to."\r\n";
    mail($to, $subject, wordwrap($body,70),$headers);

}

function writeCoinToSQL($symbol){
    //$servername = "sql7.freemysqlhosting.net";
    //$username = "sql7253140";
    //$password = "77YxhGXAH4";
    //$dbname = "sql7253140";
    //$servername = "localhost";
    //$username = "jenkinss";
    //$password = "Butt3rcup23";
    //$dbname = "CryptoBotDb";

    // Create connection
    //$conn = new mysqli($servername, $username, $password, $dbname);
    $conn = getSQL();
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "INSERT INTO CoinPrice (Symbol, Price)
    VALUES ('$symbol', 0.00)";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();

}

function getUserIDs(){
  //$servername = "sql7.freemysqlhosting.net";
  //$username = "sql7253140";
  //$password = "77YxhGXAH4";
  //$dbname = "sql7253140";
  //$servername = "localhost";
  //$username = "jenkinss";
  //$password = "Butt3rcup23";
  //$dbname = "CryptoBotDb";

  // Create connection
  //$conn = new mysqli($servername, $username, $password, $dbname);
  $conn = getSQL();
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = " SELECT `ID`, `Username`, `email`, `api_key`, `api_secret`,`active` FROM `User`";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['Username'],$row['email'],$row['api_key'],$row['api_secret'],$row['active']);
  }
  $conn->close();
  return $tempAry;
}

function getConfig($userID){
  //$servername = "sql7.freemysqlhosting.net";
  //$username = "sql7253140";
  //$password = "77YxhGXAH4";
  //$dbname = "sql7253140";
  //$servername = "localhost";
  //$username = "jenkinss";
  //$password = "Butt3rcup23";
  //$dbname = "CryptoBotDb";

  // Create connection
  //$conn = new mysqli($servername, $username, $password, $dbname);
  $conn = getSQL();
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `BTC`,`MarketCapBuyPct`,`VolumeBuyPct`,`BuyOrdersPct`,`BuyWithScore`,`Score`,`CoinSalePct`, `MarketCapSellPct`,`VolumeSellPct`, `SellOrdersPct`,`MinPctGain`,`SellWithScore`,`SellScore`,`ETH`,`BuywithMarketCap`,`BuywithVolume`,`BuyWithOrders`,`BuyWithPattern`,`BuyWithPctChange`,
  `AutoDumpCoin`, `EnableDailyBTCLimit`, `DailyBTCLimit`, `EnableTotalBTCLimit`, `TotalBTCLimit` FROM `Config` WHERE `UserID` =  $userID";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  //print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['BTC'],$row['MarketCapBuyPct'],$row['VolumeBuyPct'],$row['BuyOrdersPct'],$row['BuyWithScore'],$row['Score'],$row['CoinSalePct'],$row['MarketCapSellPct'],$row['VolumeSellPct'],$row['SellOrdersPct'],$row['MinPctGain'],
      $row['SellWithScore'],$row['SellScore'],$row['ETH'],$row['BuywithMarketCap'],$row['BuywithVolume'],$row['BuyWithOrders'],$row['BuyWithPattern'],$row['BuyWithPctChange'],$row['AutoDumpCoin'],$row['EnableDailyBTCLimit'],$row['DailyBTCLimit'],
      $row['EnableTotalBTCLimit'],$row['TotalBTCLimit']);
  }
  $conn->close();
  return $tempAry;
}


function getTrackingCoins(){
  //$servername = "sql7.freemysqlhosting.net";
  //$username = "sql7253140";
  //$password = "77YxhGXAH4";
  //$dbname = "sql7253140";
  //$servername = "localhost";
  //$username = "jenkinss";
  //$password = "Butt3rcup23";
  //$dbname = "CryptoBotDb";

  // Create connection
  //$conn = new mysqli($servername, $username, $password, $dbname);
  $conn = getSQL();
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Symbol`,`MarketCapPct`,`VolumePct`,`BuyOrdersPct`,`PriceTrend4`,`PriceTrend3`,`PriceTrend2`,`PriceTrend1`,`PctChange1Hr`,`PctChange24Hr`,`PctChange7D`,`Price`,`BaseCurrency`,
  `MarketCapScore`, `VolumeScore`,`BuyOrdersScore`,`PriceTrendScore`,`PctChange1HrScore`,`PctChange24HrScore`,`PctChange7DScore`,`Enabled`,`PctChange` FROM `CryptoBotStatsScore_Buy` ";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
//mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['Symbol'],$row['MarketCapPct'],$row['VolumePct'],$row['BuyOrdersPct'],$row['PriceTrend4'],$row['PriceTrend3'],$row['PriceTrend2'],$row['PriceTrend1'],
    $row['PctChange1Hr'],$row['PctChange24Hr'],$row['PctChange7D'],$row['Price'],$row['BaseCurrency'],$row['MarketCapScore'],$row['VolumeScore'],$row['BuyOrdersScore'],$row['PriceTrendScore'],
    $row['PctChange1HrScore'],$row['PctChange24HrScore'],$row['PctChange7DScore'],$row['Enabled'],$row['PctChange']);
  }
  $conn->close();
  return $tempAry;
}

function getLiveCoinPrice($symbol){
      $limit = 200;
    $cnmkt = "https://api.coinmarketcap.com/v1/ticker/?limit=".$limit;
    $fgc = json_decode(file_get_contents($cnmkt), true);

  for($i=0;$i<$limit;$i++){
    if ($fgc[$i]["symbol"] == $symbol){
      //print_r($fgc[$i]["price_btc"]);
      $tmpCoinPrice = $fgc[$i]["price_btc"];

    }
  }
  return $tmpCoinPrice;
}

function bittrexCoinPrice($apikey, $apisecret, $baseCoin, $coin){
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
}

function buyCoins($apikey, $apisecret, $coin, $email, $userID, $score, $date,$baseCurrency, $sendEmail, $buyCoin, $btcBuyAmount, $ruleID,$userName){
  $BTCBalance = bittrexbalance($apikey, $apisecret,$baseCurrency);
  //get min trade
  $subject = "Coin Alert: ".$coin;
  $from = 'Coin Alert <alert@investment-tracker.net>';
  echo "Balance: $BTCBalance";
  $minTradeAmount = getMinTradeAmount($coin,$baseCurrency,$apisecret);
  if ($buyCoin) {
    $subject = "Coin Purchase: ".$coin;
    $from = 'Coin Purchase <purchase@investment-tracker.net>';
  }
  $btcwithCharge = (($btcBuyAmount/100)*0.25)+$btcBuyAmount;
  if ($BTCBalance > $btcwithCharge) {
    echo "buy Coin - Balance Sufficient";
    $bitPrice = number_format((float)(bittrexCoinPrice($apikey, $apisecret,$baseCurrency,$coin)), 8, '.', '');
    //$livePrice = getLiveCoinPrice($tracking[$x][0]);
    $quantity = $btcBuyAmount/$bitPrice;
    if ($quantity>$minTradeAmount){
        echo "Quantity above min trade amount";
        //buyCoins($apikey, $apisecret,$coin, $quantity, $bitPrice, $email,$minTradeAmount, $userID, $totalScore,$date, $baseCurrency);
        $orderNo = "ORD".$coin.date("YmdHis", time()).$ruleID;
        echo "Buy Coin = $buyCoin";
        if ($buyCoin){
          $obj = bittrexbuy($apikey, $apisecret, $coin, $quantity, number_format((float)(bittrexCoinPrice($apikey, $apisecret,$baseCurrency,$coin)), 8, '.', ''));
          //writeSQLBuy($coin, $quantity, $bitPrice, $date, $orderNo, $userID, $baseCurrency);
          $bittrexRef = $obj["result"]["uuid"];
          $status = $obj["success"];
          echo "$apikey, $apisecret,$coin, $email, $userID, $score,$date, $baseCurrency,$sendEmail,$buyCoin,$quantity,$bitPrice, $ruleID,$userName,'Buy',$bittrexRef,$status,$orderNo";
          writeBittrexActionBuy($apikey, $apisecret,$coin, $email, $userID, $score,$date, $baseCurrency,$sendEmail,$buyCoin,$quantity,$bitPrice, $ruleID,$userName,'Buy',$bittrexRef,$status,$orderNo, 0.00);
        }
        if ($sendEmail==1 && $buyCoin ==0){
        //if ($sendEmail){
          sendEmail($email, $coin, $quantity, $bitPrice, $orderNo, $score, $subject,$userName, $from);
        }
    }
  }
}

function getMinTradeAmount($coin, $baseCurrency, $apisecret){
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
}

function updateSQL($symbol,$amount,$livePrice, $cost, $baseCurrency, $date){
    //$servername = "localhost";
    //$username = "jenkinss";
    //$password = "Butt3rcup23";
    //$dbname = "CryptoBotDb";
    $sellPrice = ($livePrice*$amount)-(($livePrice*$amount)*0.25);
    $purchasePrice = ($cost*$amount)-(($cost*$amount)*0.25);
    $profit = $sellPrice - $purchasePrice;
    // Create connection
    //$conn = new mysqli($servername, $username, $password, $dbname);
    $conn = getSQL();
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    if ($baseCurrency == "BTC"){
        $sql = "UPDATE `Transaction` SET `Status` = 'Pending' WHERE `Symbol` = '$symbol' and `Amount` = $amount";
    }elseif ($baseCurrency == "ETH") {
        $sql = "UPDATE `Transaction` SET `Status` = 'Pending' WHERE `Symbol` = '$symbol' and `Amount` = $amount";
    }

    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();

}

function getTrackingSellCoins($userID){
  //$servername = "localhost";
  //$username = "jenkinss";
  //$password = "Butt3rcup23";
  //$dbname = "CryptoBotDb";

  // Create connection
  //$conn = new mysqli($servername, $username, $password, $dbname);
  $conn = getSQL();
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Symbol`,`MarketCapPct`,`VolumePct`,`SellOrdersPct`,`PriceTrend1`,`PriceTrend2`, `PriceTrend3`,`PriceTrend4`,`PctChange1Hr`,`PctChange24Hr`,`PctChange7D`,`BaseCurrency`,`PriceDiff0`,`Amount`,`Cost`,`Profit`,`Price`,`OrderNo`,`MarketCapScore`,`VolumeScore`,`SellOrdersScore`,`PriceTrendScore`
  ,`PctChange1HrScore`,`PctChange24HrScore`,`PctChange7DScore`,`ProfitScore`,`PctIncrease1`,`PctIncrease2`,`PctIncrease3`,`PctIncrease4`,`TransactionID` FROM `CryptoBotStatsScoreWeb_Sell` WHERE `UserID` = $userID";

  $result = $conn->query($sql);
  //print_r($sql);

  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Symbol'],$row['MarketCapPct'],$row['VolumePct'],$row['SellOrdersPct'],$row['PriceTrend1'],$row['PriceTrend2'],$row['PriceTrend3'],$row['PriceTrend4'],$row['PctChange1Hr'],$row['PctChange24Hr'],
      $row['PctChange7D'],$row['BaseCurrency'],$row['PriceDiff0'],$row['Amount'],$row['Cost'],$row['Profit'],$row['Price'],$row['OrderNo'],$row['MarketCapScore'],$row['VolumeScore'],$row['SellOrdersScore'],$row['PriceTrendScore'],
      $row['PctChange1HrScore'],$row['PctChange24HrScore'],$row['PctChange7DScore'],$row['ProfitScore'],$row['PctIncrease1'],$row['PctIncrease2'],$row['PctIncrease3'],$row['PctIncrease4'],$row['TransactionID']);

  }
  $conn->close();
  return $tempAry;
}

function getCoinsfromSQL(){
    //$servername = "localhost";
    //$username = "jenkinss";
    //$password = "Butt3rcup23";
    //$dbname = "CryptoBotDb";

    // Create connection
    //$conn = new mysqli($servername, $username, $password, $dbname);
    $conn = getSQL();
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT `UserID`,`OrderNo`,`Symbol`,`Amount`,`Cost` FROM `Transaction` where `Status` = 'Open'";
    $result = $conn->query($sql);

    while ($row = mysqli_fetch_assoc($result)){
        $tempAry[] = Array($row['UserID'],$row['OrderNo'],$row['Symbol'],$row['Amount'],$row['Cost']);
    }
    $conn->close();
    return $tempAry;
}

function getCoinPrice(){
    //$servername = "localhost";
    //$username = "jenkinss";
    //$password = "Butt3rcup23";
    //$dbname = "CryptoBotDb";

    // Create connection
    //$conn = new mysqli($servername, $username, $password, $dbname);
    $conn = getSQL();
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT `Symbol`,`Price` FROM `CoinPrice`";
    $result = $conn->query($sql);

    while ($row = mysqli_fetch_assoc($result)){
        $tempAry[] = Array($row['Symbol'],$row['Price']);
    }
    $conn->close();
    return $tempAry;
}

function sellCoins($apikey, $apisecret, $coin, $email, $userID, $score, $date,$baseCurrency, $sendEmail, $sellCoin, $ruleID,$userName, $orderNo,$amount,$cost,$transactionID){
  echo "$apikey, $apisecret, $coin, $email, $userID, $score, $date,$baseCurrency, $sendEmail, $sellCoin, $ruleID,$userName, $orderNo,$amount,$cost";
  $subject = "Coin Alert: ".$coin."_".$ruleID;
  $from = 'Coin Alert <alerts@investment-tracker.net>';
    //sell Coin
  $bitPrice = number_format((float)(bittrexCoinPrice($apikey, $apisecret,$baseCurrency,$coin)), 8, '.', '');
  date_default_timezone_set('Asia/Dubai');
  $date = date("Y-m-d H:i:s", time());
  if ($sellCoin){
    $subject = "Coin Sale: ".$coin."_".$ruleID;
    $from = 'Coin Sale <sale@investment-tracker.net>';
    $obj = bittrexsell($apikey, $apisecret, $coin ,$amount, number_format((float)(bittrexCoinPrice($apikey, $apisecret,$baseCurrency,$coin)), 8, '.', ''));
    updateSQL($coin, $amount, $bitPrice, $cost, $baseCurrency,$date);
    //$bittrexRef = $obj['result'][0]['uuid'];
    $bittrexRef = $obj["result"]["uuid"];
    $status = $obj["success"];
    if ($status == 1){
      $totalBTC = getTotalLimit($userID);
      writeBittrexAction($apikey, $apisecret, $coin, $email, $userID, $score, $date,$baseCurrency, $sendEmail, $sellCoin, $ruleID,$userName, $orderNo,$amount,$cost,'Sell',$bittrexRef,$status,$bitPrice,$transactionID,$totalBTC);
    }
  }
  if ($sendEmail==1 &&  $sellCoin ==0){
  //if ($sendEmail){
    echo "$email, $coin, $amount, $bitPrice, $orderNo, $score,$profitPct,$bitPrice-$cost,$subject,$userName";
    $profitPct = ($bitPrice-$cost)/$cost*100;
    $buyPrice = ($cost*$amount);
    $sellPrice = ($bitPrice*$amount);
    $fee = (($sellPrice)/100)*0.25;
    $profit = $sellPrice - $buyPrice - $fee;
    sendSellEmail($email, $coin, $amount, $bitPrice, $orderNo.$ruleID, $score,$profitPct,$profit,$subject,$userName,$from);
  }
}

function writeBuyHistory($coin,$bitPrice,$marketCapPct,$volumePct,$buyOrdersPct,$priceDiff4,$priceDiff3,$priceDiff2,$priceDiff1,$pctChange1Hr,$pctChange24Hrs,$pctChange7D,$date,$totalScore, $baseCurrency){
  //$servername = "localhost";
  //$username = "autoCryptoBot";
  //$password = "@c5WmgTgjtR+";
  //$dbname = "CryptoBotHistory";

  // Create connection
  //$conn = new mysqli($servername, $username, $password, $dbname);
  $conn = getSQL();
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "INSERT INTO `BuyHistory`(`Symbol`, `Price`, `MarketCapPct`, `VolumePct`, `BuyOrdersPct`, `PriceDiff4`, `PriceDiff3`, `PriceDiff2`, `PriceDiff1`, `PriceChangePct1Hr`, `PriceChangePct24Hr`, `PriceChangePct7D`, `LastUpdated`,`Score`,`BaseCurrency`)
  VALUES ('$coin',$bitPrice,$marketCapPct,$volumePct,$buyOrdersPct,'$priceDiff4','$priceDiff3','$priceDiff2','$priceDiff1',$pctChange1Hr,$pctChange24Hrs,$pctChange7D,'$date',$totalScore, '$baseCurrency')";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();
}

function writeSellHistory($coin,$bitPrice,$marketCapbyPct,$priceDiff4,$priceDiff3,$priceDiff2,$priceDiff1,$totalScore,$profit,$date,$userID,$baseCurrency, $volumePct,$sellOrdersPct,$pctChange1Hr,$pctChange24Hrs,$pctChange7D){
  //$servername = "localhost";
  //$username = "autoCryptoBot";
  //$password = "@c5WmgTgjtR+";
  //$dbname = "CryptoBotHistory";

  // Create connection
  //$conn = new mysqli($servername, $username, $password, $dbname);
  $conn = getSQL();
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "INSERT INTO `SellHistory`(`Symbol`,`BaseCurrency`, `Price`, `MarketCapPct`, `PriceDiff4`, `PriceDiff3`, `PriceDiff2`, `PriceDiff1`, `TotalScore`, `Profit`, `LastUpdated`,`UserID`,`VolumePct`,`sellOrders`,`PctChange1Hr`,`PctChange24Hr`,`PctChange7D`)
  VALUES ('$coin','$baseCurrency',$bitPrice,$marketCapbyPct,'$priceDiff4','$priceDiff3','$priceDiff2','$priceDiff1',$totalScore,$profit,'$date',$userID, $volumePct, $sellOrdersPct,$pctChange1Hr,$pctChange24Hrs,$pctChange7D)";

  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function writeBittrexAction($apikey, $apisecret,$coin, $email, $userID, $totalScore,$date, $baseCurrency,$sendEmail,$sellCoin,$ruleID,$userName,$orderNo,$amount,$cost,$type,$bittrexRef,$status,$sellPrice,$transactionID,$totalBTC){

    //$servername = "localhost";
    //$username = "jenkinss";
    //$password = "Butt3rcup23";
    //$dbname = "CryptoBotDb";

    // Create connection
    //$conn = new mysqli($servername, $username, $password, $dbname);
    $conn = getSQL();
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "INSERT INTO `BittrexAction`(`Type`, `apiKey`, `apiSecret`, `coin`, `email`, `userID`, `totalScore`, `actionDate`, `baseCurrency`, `sendEmail`, `sellCoin`, `ruleID`, `userName`, `orderNo`, `amount`, `cost`, `status`,`bittrexRef`,";
    $sql .= "`sellPrice`,`TransactionID`,`TotalBTC`) VALUES ('$type','$apikey', '$apisecret','$coin', '$email', $userID, $totalScore,'$date', '$baseCurrency',$sendEmail,$sellCoin,'$ruleID','$userName','$orderNo',$amount,$cost,'$status','$bittrexRef',$sellPrice,$transactionID,$totalBTC)";
    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}


function writeBittrexActionBuy($apikey, $apisecret,$coin, $email, $userID, $totalScore,$date, $baseCurrency,$sendEmail,$buyCoin,$newBTCAmount,$bitPrice,$ruleID,$userName,$type,$bittrexRef,$status,$orderNo,$sellPrice){

    //$servername = "localhost";
    //$username = "autoCryptoBot";
    //$password = "@c5WmgTgjtR+";
    //$dbname = "CryptoBotDb";

    // Create connection
    //$conn = new mysqli($servername, $username, $password, $dbname);
    $conn = getSQL();
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "INSERT INTO `BittrexAction`(`Type`, `apiKey`, `apiSecret`, `coin`, `email`, `userID`, `totalScore`, `actionDate`, `baseCurrency`, `sendEmail`, `sellCoin`, `ruleID`, `userName`, `orderNo`, `amount`, `cost`, `status`,`bittrexRef`) ";
    $sql .= "VALUES ('$type','$apikey', '$apisecret','$coin', '$email', $userID, $totalScore,'$date', '$baseCurrency',$sendEmail,$buyCoin,'$ruleID','$userName','$orderNo',$newBTCAmount,$bitPrice,'$status','$bittrexRef')";
    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();

}

function getOldSQL(){
  $servername = "localhost";
  $username = "autoCryptoBot";
  $password = "@c5WmgTgjtR+";
  $dbname = "CryptoBotDb";

  // Create connection
  $conn = new mysqli($servername, $username, $password, $dbname);
  return $conn;
}

function getTotalLimit($userID){
  // Create connection
  $conn = getSQL();
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "SELECT sum(`Amount`*`Cost`) as SumBTCAmount,Date(`TradeDate`), `UserID` FROM `Transaction` WHERE `UserID` = $userID and `Status` = 'Open' or `Status` = 'Pending'";
  echo "$sql <br>";
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $temp = $row['SumBTCAmount'];
  }
  $conn->close();
  return $temp;
}



?>
