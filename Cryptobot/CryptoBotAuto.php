<html>
<?php
ini_set('max_execution_time', 600);
require('includes/newConfig.php');

include_once ('/home/stevenj1979/SQLData.php');
include_once ('includes/SQLDbCommands.php');
$apikey=getAPIKey();
$apisecret=getAPISecret();
$logToFileSetting = getLogToFile();
$manRun = False;
echo "<BR> API Secret: $apisecret";
$tmpTime = "+5 seconds";
if (!empty($argv[1])){
  parse_str($argv[1], $params);
  $tmpTime = str_replace('_', ' ', $params['mins']);
  //echo $params['code'];
  //error_log($argv[1], 0);
}
echo "<BR> isEmpty : ".empty($_GET['mins']);
if (!empty($_GET['mins'])){
  $tmpTime = str_replace('_', ' ', $_GET['mins']);
  echo "<br> GETMINS: ".$_GET['mins'];
}else{
  $manRun = True;
}

function timerReady($start, $seconds){
  $newDate = date("Y-m-d H:i:s",strtotime("+".$seconds." seconds", strtotime($start)));
  $current_date = date('Y-m-d H:i:s', time());
  echo "<BR> NewDate $newDate :: current date $current_date";
  if ($newDate <= $current_date){return true;}else{return false;}

}

function saveCMCtoSQL($CMCID, $Hr1, $Hr24, $D7, $D30, $cmcRank){
  /*$conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }*/
  $sql = "Call addCMCData($CMCID, $Hr1, $Hr24, $D7, $D30, $cmcRank);";
  SQLInsertUpdateCall("saveCMCtoSQL: ",$sql,3, 1, 1, 0, "CryptoBotAuto", 90);
  /*print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
  logAction("saveCMCtoSQL: ".$sql, 'BuyCoin', 0);*/
}

function getUserVariables(){
  $tempAry = [];
  /*$conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }*/

  $sql = "SELECT `ID`,`AccountType`,`UserName`,`Active`,`APIKey`,`APISecret`,`EnableDailyBTCLimit`,`EnableTotalBTCLimit`,`DailyBTCLimit`,`TotalBTCLimit` FROM `UserConfigView`  ";
  $tempAry = mySQLSelect("getUserVariables: ",$sql,3,1,1,0,"CryptoBotAuto",90);
  /*$result = $conn->query($sql);
  print_r($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['ID'],$row['AccountType'],$row['UserName'],$row['Active'],$row['APIKey'],$row['APISecret'],$row['EnableDailyBTCLimit'],$row['EnableTotalBTCLimit'],$row['DailyBTCLimit'],$row['TotalBTCLimit']);
  }
  $conn->close();*/
  return $tempAry;
}

function findCoinStats($CMCStats, $symbol){
  $tempStats = [];
  $statsLength = newCount($CMCStats);
  $findStatsFlag = False;
  echo "<BR> FIND: $symbol | length: $statsLength";
  for($y = 0; $y < $statsLength; $y++) {
    //echo "<br> FindCoin=".$CMCStats[$y][0];
    if ($CMCStats[$y][0]== $symbol){
      echo "<br> $statsLength Error Line ".$CMCStats[$y][0].",".$CMCStats[$y][1].",".$CMCStats[$y][2].",".$CMCStats[$y][3].",".$CMCStats[$y][4]."<br>";
      $tempStats[] = Array($CMCStats[$y][0],$CMCStats[$y][1],$CMCStats[$y][2],$CMCStats[$y][3],$CMCStats[$y][4]);
      return $tempStats;
      $findStatsFlag = True;
    }
  }
  if ($findStatsFlag == False){
    newLogToSQL("findCoinStats", "Cannot Find: $symbol", 3, 1,"CryptoBotAuto","Symbol:$symbol");
  }
  return $tempStats;
}

function getSymbols(){
  $tempAry = [];
  /*$conn = getSQLConn(rand(1,3));
  //$whereClause = "";
  //if ($UserID <> 0){ $whereClause = " where `UserID` = $UserID";}
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }*/

  $sql = "SELECT `Symbol`,`BaseCurrency` FROM `Coin` WHERE `BuyCoin` = 1";
  $tempAry = mySQLSelect("getSymbols: ",$sql,3,1,1,0,"CryptoBotAuto",90);
  /*echo "<BR> $sql";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Symbol'],$row['BaseCurrency']);
  }
  $conn->close();*/
  return $tempAry;
}

function getArrayPrice($coinAry, $symbol, $baseCurrency){
  $coinArySize = newCount($coinAry);
  echo "<BR> Size : $coinArySize";
  $nPrice = 0.0;
  for ($j=0; $j<$coinArySize; $j++){
    if ($coinAry[$j]['symbol'] == $symbol."-".$baseCurrency){
      //echo "<BR> ".$coinAry[$j]['symbol']." == $symbol."-".$baseCurrency";
      return Array($coinAry[$j]['lastTradeRate'],$coinAry[$j]['bidRate'],$coinAry[$j]['askRate']);
      //break;
    }
  }
  //return $nPrice;
}

function testBittrexCoinPrice($apikey, $apisecret, $baseCoin, $coin, $versionNum){
      $nonce=time();
      if ($versionNum == 1){
          $uri='https://bittrex.com/api/v1.1/public/getticker?market='.$baseCoin.'-'.$coin;
          $sign=hash_hmac('sha512',$uri,$apisecret);
          $ch = curl_init($uri);
              curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          $execResult = curl_exec($ch);
          $obj = json_decode($execResult, true);
          $balance = $obj["result"]["Last"];
      }elseif ($versionNum == 3){
        $timestamp = time()*1000;
        $url = "https://api.bittrex.com/v3/markets/tickers";
        echo "<BR>".$url;
        $method = "GET";
        $content = "";
        $subaccountId = "";
        $contentHash = hash('sha512', $content);
        $preSign = $timestamp . $url . $method . $contentHash . $subaccountId;
        $signature = hash_hmac('sha512', $preSign, $apisecret);

        $headers = array(
        "Accept: application/json",
        "Content-Type: application/json",
        "Api-Key: ".$apikey."",
        "Api-Signature: ".$signature."",
        "Api-Timestamp: ".$timestamp."",
        "Api-Content-Hash: ".$contentHash.""
        );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        $balance = curl_exec($ch);
        curl_close($ch);
        $temp = json_decode($balance, true);
        //var_dump($temp);
        //$balance = $temp['lastTradeRate'];
      }
      return $temp;
}

//set time
setTimeZone();
$date = date("Y-m-d H:i:s", time());
$current_date = date('Y-m-d H:i:s');
$history_date = $current_date; $marketCap_Date = $current_date;
//$newTime = date("Y-m-d H:i",strtotime("+5 minutes", strtotime($current_date)));
$newTime = date("Y-m-d H:i:s",strtotime($tmpTime, strtotime($current_date)));
$coinStr = "";
logAction('CryptoBotAuto Start','CoinPrice', $logToFileSetting);
$i = 0;
$coins = getTrackingCoins("WHERE `BuyCoin` = 1 ORDER BY `Symbol` ASC","FROM `View1_BuyCoins` ");
$coinLength = newCount($coins);
$coinStr = getCoinList(getNewStats(),0);
echo "<BR>CoinStr: $coinStr";
echo "<br> coinLength= $coinLength NEWTime=".$newTime." StartTime $date";
$historyFlag = False; $marketCapFlag = False; $marketCapStatsUpdateFlag = True;
//$marketCap_date = $current_date;
$bitPrice = 0.00;
$apiVersion = 3;
//echo "<BR> NewTEST: ".diff($date,$newTime);
$firstTimeFlag = True;
$timeFlag = False;
$timeAry = []; $marketCap_date = date('Y-m-d H:i:s');
$CMCStats = newCoinMarketCapStats($coinStr);
$CMCStatsSize = newCount($CMCStats);
Echo "<BR>Date:$date | NewTime:$newTime tmp: $tmpTime cL: $coinLength";
while($date <= $newTime){
  echo "NEW LOOP ";
  $coinAry = testBittrexCoinPrice($apikey,$apisecret, "", "", $apiVersion);
  for($x = 0; $x < $coinLength; $x++) {
    //variables
    $coinID = $coins[$x][0]; $symbol = $coins[$x][1]; $baseCurrency = $coins[$x][26]; $liveCoinPrice = $coins[$x][17];
    $secondstoUpdate = $coins[$x][35]; $Hr1Pct = $coins[$x][10]; $Hr24Pct = $coins[$x][13]; $D7Pct = $coins[$x][16];
    if ($firstTimeFlag){$timeAry[$coinID] = $coins[$x][36];}
    //LOG
    echo "<br> i=$i CoinID=$coinID Coin=$symbol baseCurrency=$baseCurrency ";

    if ($apiVersion == 1){
      //Update Price
      echo "<BR>$bitPrice = number_format((float)(bittrexCoinPrice($apikey,$apisecret,$baseCurrency,$symbol)), 8, '.', '');";
      $bitPrice = number_format((float)(bittrexCoinPrice($apikey,$apisecret,$baseCurrency,$symbol,$apiVersion)), 8, '.', '');
      //$bitPrice = $bitPriceAry['lastTradeRate'];
      //$askPrice = $bitPriceAry['ask'];
      echo "<br> PRICE_UPDATE COIN= $symbol CoinPrice= $bitPrice time ".date("Y-m-d H:i:s", time());
      $lastUpdateTime = $timeAry[$coinID];
      echo "<BR> TimeTest $coinID : $lastUpdateTime : $secondstoUpdate : ".date("Y-m-d H:i:s", time())." : ".timerReady($lastUpdateTime,$secondstoUpdate);
      if (timerReady($lastUpdateTime,$secondstoUpdate)){
        copyCoinPrice($coinID,$bitPrice);
        Echo "<BR> copyCoinPrice($coinID,$bitPrice);";
        $timeAry[$coinID] = date("Y-m-d H:i:s", time());
        logAction("Update Coin Price for $coinID to $bitPrice",'CoinPrice', $logToFileSetting);
      //}elseif (!isset($lastUpdateTime)){
      //  copyCoinPrice($coinID,$bitPrice);
      //  $timeAry[$coinID] = date("Y-m-d H:i", time());
      }
    }else{
      $bitPriceAry = getArrayPrice($coinAry,$symbol,$baseCurrency);
      if (!isSet($bitPriceAry[0])){
        newerLogToSQL("CryptoBotAuto","getArrayPrice(Ary,$symbol,$baseCurrency);",3,0,"ZeroMissing","CoinID:$coinID",90);
      }else{
        $bitPrice = $bitPriceAry[0];
      }
      if (!isSet($bitPriceAry[1])){
        newerLogToSQL("CryptoBotAuto","getArrayPrice(Ary,$symbol,$baseCurrency);",3,0,"OneMissing","CoinID:$coinID",90);
      }else{
        $bidPrice = $bitPriceAry[1];
      }
      if (!isSet($bitPriceAry[2])){
        newerLogToSQL("CryptoBotAuto","getArrayPrice(Ary,$symbol,$baseCurrency);",3,0,"TwoMissing","CoinID:$coinID",90);
      }else{
        $askPrice = $bitPriceAry[2];
      }
      //var_dump($bitPriceAry);

      copyCoinPrice($coinID,$bitPrice,$askPrice,$bidPrice);
      Echo "<BR> copyCoinPrice($coinID,$bitPrice,$askPrice,$bidPrice);";
      logAction("Update Coin Price for $coinID to $bitPrice ,$askPrice,$bidPrice",'CoinPrice', $logToFileSetting);
    }

    echo "<br>";
    echo "getCoinMarketCapStats Refresh ";
    if ($marketCapFlag == True OR $manRun == True){
      if ($marketCapStatsUpdateFlag == True){
        $marketCapStatsUpdateFlag = False; logAction("newCoinMarketCapStats('$coinStr')",'CMC', $logToFileSetting);
        for ($k=0; $k<$CMCStatsSize; $k++){
          $CMCID = $CMCStats[$k][6];
          $Hr1P = $CMCStats[$k][2];
          $Hr24P = $CMCStats[$k][3];
          $D7P = $CMCStats[$k][4];
          $D30P = $CMCStats[$k][5];
          $cmcRank = $CMCStats[$k][7];
          Echo "<BR>saveCMCtoSQL($CMCID,$Hr1P,$Hr24P,$D7P,$D30P,$cmcRank);";
          saveCMCtoSQL($CMCID,$Hr1P,$Hr24P,$D7P,$D30P,$cmcRank);
          //LogToSQL("CMCStats","saveCMCtoSQL($CMCID,$Hr1P,$Hr24P,$D7P,$D30P);",3,1);
        }
      }
      //if ($marketCapFlag){$CMCStats = newCoinMarketCapStats();}
      Echo "<BR> Market Cap flag Update ";
      //echo "<br> Count=".count($CMCStats);
      if ($symbol == "REPV2"){
        $symbol = "REP";
      }
      $statsForCoin = findCoinStats($CMCStats,$symbol);
      if ($symbol == "REP"){
        $symbol = "REPV2";
      }
      //$statsForCoin = newCoinMarketCapStats($symbol);
      //echo "<br> Market Cap ".$statsForCoin[0][1];
      //copyNewMarketCap($coinID, $statsForCoin[0][1]); //Temp Disable
      //copyNewPctChange($coinID, $statsForCoin[0][2], $statsForCoin[0][3], $statsForCoin[0][4]);
      //echo "<br> MarketCap=".$statsForCoin[0][1]."PCTChange= ".$statsForCoin[0][2]." ".$statsForCoin[0][3]." ".$statsForCoin[0][4];
      Echo "<BR>CoinMarketCapStatstoSQL($coinID,".$statsForCoin[0][1].",".$statsForCoin[0][2].",".$statsForCoin[0][3].",".$statsForCoin[0][4].");";
      CoinMarketCapStatstoSQL($coinID,$statsForCoin[0][1],$statsForCoin[0][2],$statsForCoin[0][3],$statsForCoin[0][4]);
      //logAction("CoinMarketCapStatstoSQL($coinID,".$statsForCoin[0][1].",".$statsForCoin[0][2].",".$statsForCoin[0][3].",".$statsForCoin[0][4].",)",'CMC');
      $price1Hr = get1HrChange($coinID);
      update1HrPriceChange($price1Hr[0][0],$coinID);
      $price24Hr = get24HrChange($coinID);
      update24HrPriceChange($price24Hr[0][0],$coinID);
      $price7Day = get7DayChange($coinID);
      update7DPriceChange($price7Day[0][0],$coinID);
      //updatePctChange($coinID,$price7Day[0][0],$price24Hr[0][0],$price1Hr[0][0]);
      //update24HrPriceChange($statsForCoin[0][3],$coinID);
      $bittrexStats = bittrexCoinStats($apikey,$apisecret,$symbol,$baseCurrency,$apiVersion);
      $coinVolData = getVolumeStats($bittrexStats, $apiVersion);
      BittrexStatstoSQL($coinID, $coinVolData[0][0],$coinVolData[0][1],$coinVolData[0][2]);
      //logAction("BittrexStatstoSQL($coinID, ".$coinVolData[0][0].",".$coinVolData[0][1].",".$coinVolData[0][2].")",'CMC');
      //copyCoinVolume($coinID, $coinVolData[0][0]);
      //copyCoinBuyOrders($coinID, $coinVolData[0][1]);
      //copyCoinSellOrders($coinID, $coinVolData[0][2]);
      echo "<br> Volume=".$coinVolData[0][0]." BuyOrders=".$coinVolData[0][1]." SellOrders=".$coinVolData[0][2];
      //$marketCapFlag = False; //$marketCapStatsUpdateFlag = True;
      logAction('Market Cap Update Set','CoinPrice', $logToFileSetting);
    }
    //if ($i == 1){$historyFlag = True;}
    if ($historyFlag ==  True OR $manRun == True){
      Echo "<BR> History flag Update ";
      if ($timeFlag == False){
        $coinPriceHistoryTime = date("Y-m-d H:i:s", time());
        $timeFlag = True;
      }
      //copyCoinHistory($coinID);
      //copyBuyHistory($coinID);
      copyWebTable($coinID);
      updateWebCoinStatsTable($coinID);
      //$price1Hr = get1HrChange($coinID);
      //$hr1Pct = (($bitPrice-$price1Hr[0][0])/$price1Hr[0][0])*100;
      //$price24Hr = get24HrChange($coinID);
      //$hr24Pct = (($bitPrice-$price24Hr[0][0])/$price24Hr[0][0])*100;
      //$price7Day = get7DayChange($coinID);
      //$d7Pct = (($bitPrice-$price7Day[0][0])/$price7Day[0][0])*100;
      //logAction("coinPriceHistory($coinID,$bitPrice,$baseCurrency,".date("Y-m-d H:i:s", time()).",$Hr1Pct,$Hr24Pct,$D7Pct); ",'CryptoBoyAuto', 1);
      Echo "<BR> Hr1Pct : $Hr1Pct";
      if (empty($Hr1Pct)){ $Hr1Pct = 0;}
      if (empty($Hr24Pct)){ $Hr24Pct = 0;}
      if (empty($D7Pct)){ $D7Pct = 0;}
      coinPriceHistory($coinID,$bitPrice,$baseCurrency,$coinPriceHistoryTime,$Hr1Pct,$Hr24Pct,$D7Pct);
      newLogToSQL("CryptoBotAuto","copyCoinBuyHistoryStats($coinID,$bitPrice,$baseCurrency,$coinPriceHistoryTime);",3,0,"copyCoinBuyHistoryStats","CoinID:$coinID");
      copyCoinBuyHistoryStats($coinID,$bitPrice,$baseCurrency,$coinPriceHistoryTime);
      //LogToSQL("CryptobotAuto","coinPriceHistory($coinID,$bitPrice,$baseCurrency,$coinPriceHistoryTime,$Hr1Pct,$Hr24Pct,$D7Pct);",3,1);
      //$Hr1Date = date("Y-m-d H",strtotime("-1 Hour"));
      //echo "<BR> get1HrChange($coinID,$Hr1Date);";
      //$Hr1Price = get1HrChange($coinID,$Hr1Date);
      //update1HrPriceChange($Hr1Price[0][0],$coinID);
      //$Hr8Date = date("Y-m-d H",strtotime("-1 Day"));
      //$Hr8Price = get1HrChange($coinID,$Hr8Date);
      //update8HrPriceChange($Hr8Price[0][0],$coinID);
      //$Hr24Date = date("Y-m-d H",strtotime("-1 Day"));
      //$Hr24Price = get1HrChange($coinID,$Hr24Date);
      //update24HrPriceChange($Hr24Price[0][0],$coinID);
    }

    //sleep(1);
  }//loop Coins
  echo "<br> SLEEP START: ".date("Y-m-d H:i:s", time());
  $firstTimeFlag = False;
  $historyFlag = False; if ($marketCapStatsUpdateFlag == True) {$marketCapFlag = True;} else {$marketCapFlag = False;}
  sleep(30);
  //wait(10000000);
  echo "<br> SLEEP END: ".date("Y-m-d H:i:s", time());
  $pauseStart = date("Y-m-d H:i", time());
  $tmpTimeAdd = "+5 seconds";
  $pauseEnd = date("Y-m-d H:i",strtotime($tmpTimeAdd, strtotime($pauseStart)));
  $pauseExit = date("Y-m-d H:i", time());
  //while($pauseExit <= $pauseEnd){
      echo "<BR> Waiting for time... ";
      $pauseExit = date("Y-m-d H:i", time());
  //}

  $i = $i+1;
  //if ($i >= 2){$historyFlag = False; $marketCapFlag = Flase;}
  $date = date("Y-m-d H:i", time());
  if (timerReady($history_date,720)){$historyFlag=True; $history_date = date('Y-m-d H:i');$timeFlag = False; Echo "<BR> History Timer ";logAction('Update History Set','CoinPrice', $logToFileSetting);}
  if (timerReady($marketCap_date,360)){$marketCapFlag=True; $marketCap_date = date('Y-m-d H:i'); $marketCapStatsUpdateFlag = True; Echo "<BR> Market Cap Timer "; logAction('Market Cap Update Set','CoinPrice', $logToFileSetting);}

}//while loop
echo "EndTime ".date("Y-m-d H:i", time());
logAction('CryptoBotAuto End - Number of loops : '.$i,'CoinPrice', $logToFileSetting);
//sendEmail('stevenj1979@gmail.com',$i,0,$date,0,'CryptoAuto Loop Finished', 'stevenj1979', 'Coin Purchase <purchase@investment-tracker.net>');
?>
</html>
