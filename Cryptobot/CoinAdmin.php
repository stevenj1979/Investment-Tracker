<html>
<?php
ini_set('max_execution_time', 300);
require('includes/newConfig.php');
include_once ('/home/stevenj1979/SQLData.php');
function getUserConfig(){
    $tempAry = [];
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
     $sql = "SELECT `ID`,`APIKey`,`APISecret`,datediff(`ExpiryDate`, CURDATE()) as DaysRemaining, `Email`, `UserName`, `Active` FROM `UserConfigView`";
    $result = $conn->query($sql);
    while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['ID'],$row['APIKey'],$row['APISecret'],$row['DaysRemaining'],$row['Email'],$row['UserName'],$row['Active']);}
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

function updateUserProfit($userID,$liveBTC,$BittrexBTC,$liveUSDT,$BittrexUSDT,$liveETH,$BittrexETH){
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
    $sql = "call UpdateUserProfit($userID,$liveBTC, $BittrexBTC, $liveUSDT, $BittrexUSDT, $liveETH,$BittrexETH,'$date');";
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
  $conn = getSQLConn(rand(1,3));
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

function getSequenceData(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  //$query = "SET time_zone = 'Asia/Dubai';";
  //$result = $conn->query($query);
  $sql = "SELECT `UserID`,`ID` as `SellRuleID`,`CoinOrder` FROM `SellRules` order by `UserID`,`CoinOrder` asc ";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['UserID'],$row['SellRuleID'],$row['Sequence']);}
  $conn->close();
  return $tempAry;
}

function getTransData(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  //$query = "SET time_zone = 'Asia/Dubai';";
  //$result = $conn->query($query);
  $sql = "SELECT `ID`,`UserID`, `OrderNo`,`FixSellRule`, mod(datediff(curdate(),`OrderDate`),7) as DaysOver FROM `TransactionsView` WHERE `Status` = 'Open'";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['ID'],$row['UserID'],$row['OrderNo'],$row['FixSellRule'],$row['DaysOver']);}
  $conn->close();
  return $tempAry;
}

function updateFixSellRule($newFixRule, $transactionID){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $sql = "UPDATE `Transaction` SET `FixSellRule`= $newFixRule WHERE `ID` = $transactionID";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}

function updateBittrexBalances($symbol, $total, $price){
    $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
    $sql = "Call AddBittrexBal($symbol,$total,$price);";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}

function checkSellSequence(){
  //get sell sequesnce info
  //get all open trans
  $trans = getTransData();
  $transcount = count($trans);
  $nFlag = False;
  $sequence = getSequenceData();
  $sequenceCount = count($sequence);
  for($x = 0; $x < $transcount; $x++) {
    if ($trans[$x][4] == 0){  //check if the buy Tran Mod 7 days = 0;
      $userID = $trans[$x][1]; $fixSellRule = $trans[$x][3]; $transactionID = $trans[$x][0];
      //get the current sequence number
      //update the fixed sell ID
      for($y = 0; $y < $sequenceCount; $y++) {
        if ($sequence[$y][0] == $userID and $sequence[$y][1] == $fixSellRule){
          //$nFlag = True;
          for ($z =$y+1; $z<$sequenceCount; $z ++ ){
              if ( $sequence[$z][0] == $userID){
                updateFixSellRule($sequence[$z][1],$transactionID);
                logToSQL("Sell Coin Sequence", "Change Fixed Sell Rule from ".$sequence[$y][1]." to ".$sequence[$z][1]." TransactionID: $transactionID", $userID);
              }
          }

        }
      }
    }
  }
}

$subject = "Subscription Expiring!"; $from = "CryptoBot <subscription@investment-tracker.net>";
//Get UserID + API Keys
$conf = getUserConfig();
$confSize = count($conf);
for($x = 0; $x < $confSize; $x++) {
  $daysRemaining = $conf[$x][3]; $active = $conf[$x][6]; $userID = $conf[$x][0]; $email = $conf[$x][4]; $userName = $conf[$x][5];
  //$bittrexBalBTC = bittrexbalance($conf[$x][1], $conf[$x][2], 'BTC' );
  //$bittrexBalUSDT = bittrexbalance($conf[$x][1], $conf[$x][2], 'USDT' );
  //$bittrexBalETH = bittrexbalance($conf[$x][1], $conf[$x][2], 'ETH' );
  //$btcToday = userHistory($conf[$x][0]);
  //updateUserProfit($conf[$x][0],$btcToday[0][1],$bittrexBalBTC,$btcToday[0][2],$bittrexBalUSDT,$btcToday[0][3],$bittrexBalETH);
  //$daysRemaining = $userDates[$x][5]; $active = $userDates[$x][3]; $userID = $userDates[$x][0]; $email = $userDates[$x][1]; $userName = $userDates[$x][4];

  if ($active == 'Yes' && $daysRemaining <= 0) {
    echo "Success 1 _ ".$daysRemaining;
    //Update SQL
    updateSQLactive($userID);
  } elseif ($active == 'Yes' && $daysRemaining == 7) {
    echo "Success 2 _ ".$daysRemaining;
    sendRenewEmail($email, $subject, $userName, $from, $daysRemaining);
  } elseif ($active == 'Yes' && $daysRemaining == 3) {
    echo "Success 3 _ ".$daysRemaining;
    sendRenewEmail($email, $subject, $userName, $from, $daysRemaining);
  } elseif ($active == 'Yes' && $daysRemaining == 1) {
    echo "Success 4 _ ".$daysRemaining;
    sendRenewEmail($email, $subject, $userName, $from, $daysRemaining);
  }
}

coinHistory(10);
DeleteHistory(96);
checkSellSequence();
$apisecret=getAPISecret();
$apikey=getAPIKey();
getMinTradeAmount($apisecret);

$bittrexBals = getDailyBalance($apikey,$apisecret);
$bittrexBalsSize = count($bittrexBals);
echo "<BR> Array Size : $bittrexBalsSize";
foreach ($bittrexBals as $value){
    Echo $value["currencySymbol"];
    Echo $value["total"];
    Echo $value["available"];
    echo "<BR>";
}

?>
</html>
