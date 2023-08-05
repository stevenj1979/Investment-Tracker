<html>
<?php
ini_set('max_execution_time', 300);
require('includes/newConfig.php');
include_once ('/home/stevenj1979/SQLData.php');
include_once ('/home/stevenj1979/Encrypt.php');
include_once ('includes/SQLDbCommands.php');

function getUserConfig(){
    $tempAry = [];
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
     $sql = "SELECT `IDUs`,`APIKey`,`APISecret`,datediff(`ExpiryDate`, CURDATE()) as DaysRemaining, `Email`, `UserName`, `Active` ,`KEK` FROM  `View12_UserConfig`";
    $result = $conn->query($sql);
    while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['IDUs'],$row['APIKey'],$row['APISecret'],$row['DaysRemaining'],$row['Email'],$row['UserName'],$row['Active'],$row['KEK']);}
    $conn->close();
    return $tempAry;
}

function clearDailtBTCTbl($table){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "DELETE FROM $table";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function runTransaction($table, $dateWhere){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "INSERT INTO $table (select `Tr`.`UserID` AS `UserID`, sum((`Tr`.`Amount` * `Tr`.`CoinPrice`)) AS `AmountOpen`,`Cn`.`BaseCurrency` AS `BaseCurrency`
from `Transaction` `Tr`
	join `Coin` `Cn` on((`Cn`.`ID` = `Tr`.`CoinID`))
where `Tr`.`Status` in ('Open','Pending') $dateWhere
group by `Tr`.`UserID`,`Cn`.`BaseCurrency`);";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function runTracking($table, $dateWhere){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "INSERT INTO $table (select `Tc`.`UserID` AS `UserID`,sum((`Tc`.`Quantity` * `Tc`.`CoinPrice`)) AS `AmountOpen`,`Tc`.`BaseCurrency` AS `BaseCurrency`
from `TrackingCoins` `Tc`
where  `Tc`.`Status` in ('Open','Pending') $dateWhere
group by `Tc`.`UserID`,`Tc`.`BaseCurrency`); ";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
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
    /*$conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $query = "SET time_zone = 'Asia/Dubai';";
    $result = $conn->query($query);*/
    $sql = "SELECT `UserID` AS `UserID`
              ,Sum(Case When `BaseCurrency` = 'BTC'
                Then `LivePrice` Else 0 End) as LiveBTC
              ,Sum(Case When `BaseCurrency` = 'USDT'
                Then `LivePrice` Else 0 End) as LiveUSDT
              ,Sum(Case When `BaseCurrency` = 'ETH'
                Then `LivePrice` Else 0 End) as LiveETH
              FROM `View5_SellCoins` where `UserID` = $userID
              and `Status` in ('Open','Pending')
              group by `UserID` ;";
    $tempAry = mySQLSelect("userHistory: ",$sql,3,1,1,0,"Dashboard",90);
    /*print_r($sql);
    $result = $conn->query($sql);
    while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['UserID'],$row['LiveBTC'],$row['LiveUSDT'],$row['LiveETH']);}
    $conn->close();*/
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

function getOpenCoins($status){
  $tempAry = [];
  //if ($userID <> 0){ $whereclause = "Where `UserID` = $userID";}else{$whereclause = "";}
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `IDTr`,`Type`,`CoinID`,`UserID`,`CoinPrice`,`Amount`,`Status`,`OrderDate`,`CompletionDate`,`BittrexID`,`OrderNo`,`Symbol`,`LastBuyOrders`, `LiveBuyOrders`,`BuyOrdersPctChange`,`LastMarketCap`,`LiveMarketCap`,`MarketCapPctChange`,`LastCoinPrice`,`LiveCoinPrice`,`CoinPricePctChange`,`LastSellOrders`
  ,`LiveSellOrders`,`SellOrdersPctChange`,`LastVolume`,`LiveVolume`,`VolumePctChange`,`Last1HrChange`,`Live1HrChange`,`Hr1ChangePctChange`,`Last24HrChange`,`Live24HrChange`,`Hr24ChangePctChange`,`Last7DChange`,`Live7DChange`,`D7ChangePctChange`,`BaseCurrency`
  , `Price4Trend`,`Price3Trend`,`LastPriceTrend`,`LivePriceTrend`,`FixSellRule`,`SellRule`,`BuyRule`,`ToMerge`,`LowPricePurchaseEnabled`,`DailyBTCLimit`,`PctToPurchase`,`BTCBuyAmount`,`NoOfPurchases`,`Name`,`Image`,10 as `MaxCoinMerges`,`NoOfCoinSwapsThisWeek`
  ,@OriginalPrice:=`CoinPrice`*`Amount` as OriginalPrice, @CoinFee:=((`CoinPrice`*`Amount`)/100)*0.28 as CoinFee, @LivePrice:=`LiveCoinPrice`*`Amount` as LivePrice, @coinProfit:=@LivePrice-@OriginalPrice-@CoinFee as ProfitUSD, @ProfitPct:=(@coinProfit/@OriginalPrice)*100 as ProfitPct
  ,`CaptureTrend` FROM `View5_SellCoins` WHERE  `Status` = '$status' and `ToMerge` = 1 order by `IDTr` Asc ";
  $result = $conn->query($sql);
  echo $sql;
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['IDTr'],$row['Type'],$row['CoinID'],$row['UserID'],$row['CoinPrice'],$row['Amount'],$row['Status'],$row['OrderDate'],$row['CompletionDate'],$row['BittrexID'],$row['OrderNo'],
    $row['Symbol'],$row['LastBuyOrders'],$row['LiveBuyOrders'],$row['BuyOrdersPctChange'],$row['LastMarketCap'],$row['LiveMarketCap'],$row['MarketCapPctChange'],$row['LastCoinPrice'],$row['LiveCoinPrice'], //19
    $row['CoinPricePctChange'],$row['LastSellOrders'],$row['LiveSellOrders'],$row['SellOrdersPctChange'],$row['LastVolume'],$row['LiveVolume'],$row['VolumePctChange'],$row['Last1HrChange'],$row['Live1HrChange'],$row['Hr1ChangePctChange'],$row['Last24HrChange'],$row['Live24HrChange'] //31
    ,$row['Hr24ChangePctChange'],$row['Last7DChange'],$row['Live7DChange'],$row['D7ChangePctChange'],$row['BaseCurrency'],$row['Price4Trend'],$row['Price3Trend'],$row['LastPriceTrend'],$row['LivePriceTrend'],$row['FixSellRule'],$row['SellRule'],$row['BuyRule'] //43
    ,$row['ToMerge'],$row['LowPricePurchaseEnabled'],$row['DailyBTCLimit'],$row['PctToPurchase'],$row['BTCBuyAmount'],$row['NoOfPurchases'],$row['Name'],$row['Image'],$row['MaxCoinMerges'],$row['NoOfCoinSwapsThisWeek'],$row['CaptureTrend']);
  }
  $conn->close();
  return $tempAry;
}

function updateMergeSaving(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "UPDATE `Transaction` `Tr`
            join `UserConfig` `Uscf` on `Tr`.`UserID` = `Uscf`.`UserID`
            SET `Tr`.`ToMerge` = 1
            WHERE `Tr`.`Status` = 'Saving' and `Uscf`.`AutoMergeSavings` = 1 ";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function mergeCoins($sellTrackingCoins, $nStatus){
  $sellTrackingCoinsSize = newCount($sellTrackingCoins);
  $z = 0;$toMergeAry = []; $finalMergeAry = [];
  echo "<BR> Tracking Coins to Merge. Count: $sellTrackingCoinsSize";
  for($x = 0; $x < $sellTrackingCoinsSize; $x++) {
    $toMerge = $sellTrackingCoins[$x][44]; $userID = $sellTrackingCoins[$x][3]; $coinID = $sellTrackingCoins[$x][2]; $symbol = $sellTrackingCoins[$x][11];
    $transactionID = $sellTrackingCoins[$x][0]; $amount = $sellTrackingCoins[$x][5]; $cost = $sellTrackingCoins[$x][4]; $MaxCoinMerge = $sellTrackingCoins[$x][52];
    $noOfPurchases = $sellTrackingCoins[$x][49];
    $usdAmount = $cost * $amount;
    if ($toMerge == 1 && $sellTrackingCoinsSize >= 2){
      $toMergeAry = Array($userID,$coinID,$symbol,$transactionID,$amount,$cost,$MaxCoinMerge, $noOfPurchases,$usdAmount);
      echo "<BR> ARRAY($userID,$coinID,$symbol,$transactionID,$amount,$cost,$MaxCoinMerge, $noOfPurchases,$usdAmount);";
      //newLogToSQL("Dashboard","$userID,$coinID,$symbol,$transactionID,$amount,$cost,$MaxCoinMerge, $noOfPurchases);",3,1,"MergeCoins","TransactionID:$transactionID");
      $finalMergeAry = updateMergeAry($toMergeAry,$finalMergeAry);
    }
  }
  echo "<BR>".var_dump($finalMergeAry)."<BR>";
  $finalMergeArySize = newCount($finalMergeAry);
  echo "<BR> Tracking Coins to FinalMerge. Count: $finalMergeArySize";
  for($x = 0; $x < $finalMergeArySize; $x++) {
    $userID = $finalMergeAry[$x][0]; $coinID = $finalMergeAry[$x][1]; $symbol = $finalMergeAry[$x][2]; $transactionID = $finalMergeAry[$x][3];
    $amount = $finalMergeAry[$x][4]; $cost = $finalMergeAry[$x][5]; $lastTransID = $finalMergeAry[$x][6]; $count = $finalMergeAry[$x][7]; $MaxCoinMerge = $finalMergeAry[$x][8];
    $usdAmount = $finalMergeAry[$x][10];
    $avCost = $usdAmount/$amount; $noOfPurchases = $finalMergeAry[$x][9];
    echo "<BR> Count: $count";
    if ($count >= 2){
      echo "<BR> mergeTransactions($transactionID, $amount, $avCost, $lastTransID);";
      mergeTransactions($transactionID, $amount, $avCost);
      UpdateTransCount($count-1, $transactionID);
      closeOldTransSQL(rtrim($lastTransID, ','));
      //logToSQL("TrackingCoins", "mergeTransactions($transactionID, $amount, $avCost, $lastTransID);", $userID);
      newLogToSQL("Dashboard","$nStatus | mergeTransactions($transactionID, $amount, $avCost);",3,1,"MergeCoinsTotal","TransactionID:$transactionID");
    }
  }
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
$confSize = newCount($conf);
for($x = 0; $x < $confSize; $x++) {
  $userID = $conf[$x][0];
  $apikey = $conf[$x][1]; $apisecret = $conf[$x][2]; $Kek = $conf[$x][7];
  if (!empty($Kek)){$apisecret = Decrypt($Kek,$conf[$x][2]);}
  $daysRemaining = $conf[$x][3]; $active = $conf[$x][6]; $userID = $conf[$x][0]; $email = $conf[$x][4]; $userName = $conf[$x][5];
  $btcPrice = number_format((float)(bittrexCoinPriceNew('USD','BTC')), 8, '.', '');
  $ethPrice = number_format((float)(bittrexCoinPriceNew('USD','ETH')), 8, '.', '');
  $usdtPrice = number_format((float)(bittrexCoinPriceNew('USD','USDT')), 8, '.', '');
  $bittrexBalBTC = bittrexbalance($apikey, $apisecret, 'BTC',3);
  if (empty($bittrexBalBTC)){$bittrexBalBTC = 0;}
  $bittrexBalUSDT = bittrexbalance($apikey, $apisecret, 'USDT',3);
  if (empty($bittrexBalUSDT)){$bittrexBalUSDT = 0;}
  $bittrexBalETH = bittrexbalance($apikey, $apisecret, 'ETH',3);
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

updateMergeSaving();
$savingCoins = getOpenCoins('Saving');
mergeCoins($savingCoins,'Saving');
$sellTrackingCoins = getOpenCoins('Open');
mergeCoins($sellTrackingCoins,'Open');


clearDailtBTCTbl("`DailyBTCTbl`");
runTransaction("`DailyBTCTbl`"," and dayofmonth(`OrderDate`) = dayofmonth(now()) and month(`OrderDate`) = month(now()) and Year(`OrderDate`) = Year(now())");
runTracking("`DailyBTCTbl`", " and dayofmonth(`TrackDate`) = dayofmonth(now()) and month(`TrackDate`) = month(now()) and Year(`TrackDate`) = Year(now())");
clearDailtBTCTbl("`AllTimeBTCTbl`");
runTransaction("`AllTimeBTCTbl`","");
runTracking("`AllTimeBTCTbl`","");

//coinHistory(10);
//DeleteHistory(168);

?>
</html>
