<html>
<?php
ini_set('max_execution_time', 300);
require('includes/newConfig.php');
include_once ('/home/stevenj1979/SQLData.php');
include_once ('/home/stevenj1979/Encrypt.php');
function getUserConfig(){
    $tempAry = [];
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
     $sql = "SELECT `ID`,`APIKey`,`APISecret`,datediff(`ExpiryDate`, CURDATE()) as DaysRemaining, `Email`, `UserName`, `Active` ,`KEK` FROM `UserConfigView`";
    $result = $conn->query($sql);
    while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['ID'],$row['APIKey'],$row['APISecret'],$row['DaysRemaining'],$row['Email'],$row['UserName'],$row['Active'],$row['KEK']);}
    $conn->close();
    return $tempAry;
}

function DeleteHistory($hours){
  $conn = getHistorySQL(rand(1,4));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $date = date('Y-m-d H:i', time());
  $sql = "call Update1HrPriceChange($hours,$date);";
  //print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function userHistory($userID){
    $tempAry = [];
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $query = "SET time_zone = 'Asia/Dubai';";
    $result = $conn->query($query);
    $sql = "SELECT `UserID` AS `UserID`
              ,Sum(Case When `BaseCurrency` = 'BTC'
                Then `LivePrice` Else 0 End) as LiveBTC
              ,Sum(Case When `BaseCurrency` = 'USDT'
                Then `LivePrice` Else 0 End) as LiveUSDT
              ,Sum(Case When `BaseCurrency` = 'ETH'
                Then `LivePrice` Else 0 End) as LiveETH
              FROM `UnrealisedProfit` where `UserID` = $userID
              group by `UserID`;";
    print_r($sql);
    $result = $conn->query($sql);
    while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['UserID'],$row['LiveBTC'],$row['LiveUSDT'],$row['LiveETH']);}
    $conn->close();
    return $tempAry;
}

function updateUserProfit($userID,$liveBTC,$BittrexBTC,$liveUSDT,$BittrexUSDT,$liveETH,$BittrexETH,$btcPrice, $ethPrice, $usdtPrice){
    //set time
    setTimeZone();
    $date = date("Y-m-d H:i", time());
    if (empty($liveBTC)){$liveBTC = 0;}
    if (empty($BittrexBTC)){$BittrexBTC = 0;}
    if (empty($liveUSDT)){$liveUSDT = 0;}
    if (empty($BittrexUSDT)){$BittrexUSDT = 0;}
    if (empty($liveETH)){$liveETH = 0;}
    if (empty($BittrexETH)){$BittrexETH = 0;}
    //if (empty($actionDate)){$actionDate = $date;}
    //echo "<br> TEST1: ".isset($BTCfromCoins);
    //echo "<br> TEST2: ".empty($BTCfromCoins);
    //echo "<br> TEST3: ".isnull($BTCfromCoins);
    $tempAry = [];
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $sql = "call UpdateUserProfitNew($userID,$liveBTC, $BittrexBTC, $liveUSDT, $BittrexUSDT, $liveETH,$BittrexETH,'$date',$btcPrice, $ethPrice, $usdtPrice);";
    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}

function coinHistory($hours){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $sql = "call deleteHistory($hours)";
    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}

function updateSQLactive($userID){
  $conn = getSQL();
    // Check connection
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $sql = "UPDATE `User` SET `Active` = 'No' WHERE `ID` = $userID";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}

function sendRenewEmail($to, $subject, $user, $from, $daysRemaining){
    $body = "Dear ".$user.", <BR/>";
    $body .= "You have $daysRemaining days left before your subscription expires<BR/>";
    $body .= "Please renew on this link http://www.investment-tracker.net/Investment-Tracker/Cryptobot/1/Subscribe.php<BR/>";
    $body .= "Kind Regards\nCryptoBot.";
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    //$headers .= 'From: Alerts <Alerts@Investment-Tracker.net>' . "\r\n";
    $headers .= "From:".$from."\r\n";
    $headers .= "To:".$to."\r\n";
    mail($to, $subject, wordwrap($body,70),$headers);
}

function getLiveCoinPriceUSD($symbol){
    $limit = 100;
    $cnmkt = "https://api.coinmarketcap.com/v1/ticker/?limit=".$limit;
    $fgc = json_decode(file_get_contents($cnmkt), true);

  for($i=0;$i<$limit;$i++){
    //print_r($i);
    if ($fgc[$i]["symbol"] == $symbol){
      //print_r($fgc[$i]["symbol"]);
      $tmpCoinPrice = $fgc[$i]["price_usd"];
    }
  }
  logAction("$cnmkt",'CMC');
  return $tmpCoinPrice;
}

function updateUserProfitUnrealised($userID,$liveBTC,$liveUSDT,$liveETH,$btcPrice, $ethPrice, $usdtPrice){
    //set time
    setTimeZone();
    $date = date("Y-m-d H:i", time());
    $BTCtoAdd = $liveBTC * $btcPrice;
    if (empty($BTCtoAdd)) {$BTCtoAdd = 0;}
    $USDTtoAdd = $liveUSDT * $usdtPrice;
    if (empty($USDTtoAdd)) {$USDTtoAdd = 0;}
    $ETHtoAdd = $liveETH * $ethPrice;
    if (empty($ETHtoAdd)) {$ETHtoAdd = 0;}
    $totaltoAdd = $BTCtoAdd+$USDTtoAdd+$ETHtoAdd;
    //if (empty($actionDate)){$actionDate = $date;}
    //echo "<br> TEST1: ".isset($BTCfromCoins);
    //echo "<br> TEST2: ".empty($BTCfromCoins);
    //echo "<br> TEST3: ".isnull($BTCfromCoins);
    $tempAry = [];
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $sql = "call AddPendingUSDtoUserProfit($userID,$totaltoAdd);";
    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}
$apikey = ""; $apisecret = "";
$currentBTCPurchased = 0.00; $currentUSDTPurchased = 0.00; $currentETHPurchased = 0.00;
$subject = "Subscription Expiring!"; $from = "CryptoBot <subscription@investment-tracker.net>";
//Get UserID + API Keys
$conf = getUserConfig();
$btcPrice = 0.00; $ethPrice = 0.00; $usdtPrice = 0.00;
//$btcPrice = getLiveCoinPriceUSD('BTC');
//$ethPrice = getLiveCoinPriceUSD('ETH');
//$usdtPrice= getLiveCoinPriceUSD('USDT');
Echo "<BR> BTC Price: $btcPrice : ETH Price :  $ethPrice : USDT Price : $usdtPrice ";
$confSize = count($conf);
for($x = 0; $x < $confSize; $x++) {
  $userID = $conf[$x][0];
  $apikey = $conf[$x][1]; $apisecret = $conf[$x][2]; $Kek = $conf[$x][7];
  if (!empty($Kek)){$apisecret = Decrypt($Kek,$conf[$x][2]);}
  $daysRemaining = $conf[$x][3]; $active = $conf[$x][6]; $userID = $conf[$x][0]; $email = $conf[$x][4]; $userName = $conf[$x][5];
  $btcPrice = number_format((float)(bittrexCoinPrice($apikey, $apisecret,'USD','BTC')), 8, '.', '');
  $ethPrice = number_format((float)(bittrexCoinPrice($apikey, $apisecret,'USD','ETH')), 8, '.', '');
  $usdtPrice = number_format((float)(bittrexCoinPrice($apikey, $apisecret,'USD','USDT')), 8, '.', '');
  $bittrexBalBTC = bittrexbalance($apikey, $apisecret, 'BTC' );
  if (empty($bittrexBalBTC)){$bittrexBalBTC = 0;}
  $bittrexBalUSDT = bittrexbalance($apikey, $apisecret, 'USDT' );
  if (empty($bittrexBalUSDT)){$bittrexBalUSDT = 0;}
  $bittrexBalETH = bittrexbalance($apikey, $apisecret, 'ETH' );
  if (empty($bittrexBalETH)){$bittrexBalETH = 0;}
  $btcToday = userHistory($conf[$x][0]);
  if (!empty($btcToday)){
    $currentBTCPurchased = $btcToday[0][1]; $currentUSDTPurchased = $btcToday[0][2]; $currentETHPurchased = $btcToday[0][3];
  }
  echo "<BR> BTCPrice ".$btcPrice;
  Echo "<BR> Update User Profit: updateUserProfit($userID,$currentBTCPurchased,$bittrexBalBTC,$currentUSDTPurchased,$bittrexBalUSDT,$currentETHPurchased,$bittrexBalETH,$btcPrice, $ethPrice,$usdtPrice);";
  Echo "<BR> Update Unrealised Profit: updateUserProfitUnrealised($userID,$currentBTCPurchased,$currentUSDTPurchased,$currentETHPurchased,$btcPrice, $ethPrice,$usdtPrice);";
  updateUserProfit($userID,$currentBTCPurchased,$bittrexBalBTC,$currentUSDTPurchased,$bittrexBalUSDT,$currentETHPurchased,$bittrexBalETH,$btcPrice, $ethPrice,$usdtPrice);
  updateUserProfitUnrealised($userID,$currentBTCPurchased,$currentUSDTPurchased,$currentETHPurchased,$btcPrice, $ethPrice,$usdtPrice);
  //$daysRemaining = $userDates[$x][5]; $active = $userDates[$x][3]; $email = $userDates[$x][1]; $userName = $userDates[$x][4];
}

$sellTrackingCoins = getTrackingSellCoins();
$sellTrackingCoinsSize = Count($sellTrackingCoins);
$z = 0;$toMergeAry = []; $finalMergeAry = [];
echo "<BR> Tracking Coins to Merge. Count: $sellTrackingCoinsSize";
for($x = 0; $x < $sellTrackingCoinsSize; $x++) {
  $toMerge = $sellTrackingCoins[$x][44]; $userID = $sellTrackingCoins[$x][3]; $coinID = $sellTrackingCoins[$x][2]; $symbol = $sellTrackingCoins[$x][11];
  $transactionID = $sellTrackingCoins[$x][0]; $amount = $sellTrackingCoins[$x][5]; $cost = $sellTrackingCoins[$x][4];

  if ($toMerge == 1 && $sellTrackingCoinsSize >= 2){
    $toMergeAry[0] = Array($userID,$coinID,$symbol,$transactionID,$amount,$cost);
    echo "<BR> ARRAY($userID,$coinID,$symbol,$transactionID,$amount,$cost);";
    $finalMergeAry = updateMergeAry($toMergeAry,$finalMergeAry);
  }
}
$finalMergeArySize = Count($finalMergeAry);
echo "<BR> Tracking Coins to FinalMerge. Count: $finalMergeArySize";
for($x = 0; $x < $finalMergeArySize; $x++) {
  $userID = $finalMergeAry[$x][0]; $coinID = $finalMergeAry[$x][1]; $symbol = $finalMergeAry[$x][2]; $transactionID = $finalMergeAry[$x][3];
  $amount = $finalMergeAry[$x][4]; $cost = $finalMergeAry[$x][5]; $lastTransID = $finalMergeAry[$x][6]; $count = $finalMergeAry[$x][7];
  $avCost = $cost/$count;
  echo "<BR> Count: $count";
  if ($count >= 2){
    echo "<BR> mergeTransactions($transactionID, $amount, $avCost, $lastTransID);";
    mergeTransactions($transactionID, $amount, $avCost, $lastTransID);
    logToSQL("TrackingCoins", "mergeTransactions($transactionID, $amount, $avCost, $lastTransID);", $userID);
  }
}
//coinHistory(10);
//DeleteHistory(168);
?>
</html>
