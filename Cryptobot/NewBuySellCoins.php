<html>
<?php
ini_set('max_execution_time',1200);
require('includes/newConfig.php');
require('Functions/BuySellFunc.php');

include_once ('/home/stevenj1979/SQLData.php');
include_once ('/home/stevenj1979/Encrypt.php');
$apikey=getAPIKey();
$apisecret=getAPISecret();
$logToSQLSetting = getLogToSQL();
//$GLOBALS['logToFileSetting']  = getLogToFile();
$GLOBALS['logToSQLSetting'] = getLogToSQL();
$GLOBALS['logToFileSetting'] = getLogToFile();

//$buyCancelTime = "01:0";
//$noOfBuys = 5;
$buyCounter = [];
$tmpTime = "+5 seconds";
if (!empty($argv[1])){
  parse_str($argv[1], $params);
  $tmpTime = str_replace('_', ' ', $params['mins']);
  //echo $tmpTime;
  //error_log($argv[1], 0);
}
//echo "<BR> isEmpty : ".empty($_GET['mins']);
if (!empty($_GET['mins'])){
  $tmpTime = str_replace('_', ' ', $_GET['mins']);
  //echo "<br> GETMINS: ".$_GET['mins'];
}

//set time
setTimeZone();
$i=0;
$date = date("Y-m-d H:i", time());
$current_date = date('Y-m-d H:i');
$priceDipTimer = date('Y-m-d H:i');
$spreadBetTimer = date('Y-m-d H:i');
$trackingCoinTimer = date('Y-m-d H:i');
$trackingSellCoinTimer = date('Y-m-d H:i');
$sellSpreadBetTimer = date('Y-m-d H:i');
$buyCoinTimer = date('Y-m-d H:i');
$sellCoinTimer = date('Y-m-d H:i');
$sharedVariablesTimer = date('Y-m-d H:i');
$alertRunTimer = date('Y-m-d H:i');
$bittrexReqsTimer = date('Y-m-d H:i');
$completeFlag = False;
$reRunBuySavingsFlag = False;
$runTrackingSellCoinFlag = False;
$runNewTrackingCoinFlag = False;
$runSpreadBetSellAndBuybackFlag = False;
$runSellSavingsFlag = False;
$runBuyBackFlag = False;
$runSellSpreadBet = False;
$runSpreadBetFlag = False;
$runSellCoinsFlag = False;
$runBuyCoinsFlag = False;
$buyToReduceLossFlag = False;
$apiVersion = 3;
$trackCounter = [];
$clearCoinQueue = [];
$buyCounter = [];
$openTransactionFlag = True;
$refreshBittrexFlag = True;
$refreshAlertsFlag = True;
$newTime = date("Y-m-d H:i",strtotime($tmpTime, strtotime($current_date)));
logAction("Buy Sell Coins Start : End set to $newTime : $date", 'BuySellTiming', $GLOBALS['logToFileSetting'] );

while($completeFlag == False){
  if (date("Y-m-d H:i", time()) >= $sharedVariablesTimer){
    $SVcurrent_date = date('Y-m-d H:i');
    $sharedVariablesTimer = date("Y-m-d H:i",strtotime("+2 minutes 50 seconds", strtotime($SVcurrent_date)));
    $coinPriceMatch = getCoinPriceMatchList();
    $coinPricePatternList = getCoinPricePattenList();
    $coin1HrPatternList = getCoin1HrPattenList();
    $autoBuyPrice = getAutoBuyPrices();
  }
  echo "<blockquote><BR> CHECK Re-Buy Savings!! $i";
          if (($i == 0) or ($reRunBuySavingsFlag == True)){
            $reBuySavingsFixed = getOpenCoinSwaps();
            $reRunBuySavingsFlag = False;
          }
          $reRunBuySavingsFlag = runReBuySavings($reBuySavingsFixed);
  echo "</blockquote><BR> CHECK Sell Savings!! $i<blockquote>";
          if ($i == 0or $runSellSavingsFlag == True){
            $spreadBuyBack = getSavingsData();
            $runSellSavingsFlag = False;
          }
          $runSellSavingsFlag = runSellSavings($spreadBuyBack);
  echo "</blockquote><BR>CHECK PriceDip Rule Enable!! $i<blockquote>";
        if (date("Y-m-d H:i", time()) >= $priceDipTimer){
          $PDcurrent_date = date('Y-m-d H:i');
          $priceDipTimer = date("Y-m-d H:i",strtotime("+2 minutes 40 seconds", strtotime($PDcurrent_date)));
          $priceDipRules = getPriceDipRules();
        }
        runPriceDipRule($priceDipRules);
  echo "</blockquote><BR> CHECK BuyBack!! $i<blockquote>";
        if ($i == 0 or $runBuyBackFlag == True){
          $buyBackCoins = getBuyBackData();
          $runBuyBackFlag = False;
        }
        $runBuyBackFlag = runBuyBack($buyBackCoins);
  echo "</blockquote><BR> CHECK Spreadbet Sell & BuyBack!! $i<blockquote>";
        if ($i == 0 OR $runSpreadBetSellAndBuybackFlag == True ){
          $spreadBuyBack = getSpreadCoinSellDataFixed();
          $runSpreadBetSellAndBuybackFlag = False;
        }
        $runSpreadBetSellAndBuybackFlag = runSpreadBetSellAndBuyback($spreadBuyBack);
  echo "</blockquote><BR>CHECK Sell Spread Bet!! $i<blockquote>";
        if (date("Y-m-d H:i", time()) >= $sellSpreadBetTimer or $runSellSpreadBet == True){
          $sSBcurrent_date = date('Y-m-d H:i');
          $sellSpreadBetTimer = date("Y-m-d H:i",strtotime("+2 minutes 28 seconds", strtotime($sSBcurrent_date)));
          $sellSpread = getSpreadBetSellData();
          $runSellSpreadBet = False;
        }
        $runSellSpreadBet = runSellSpreadBet($sellSpread);
  echo "</blockquote><BR>CHECK Spread Bet!! $i<blockquote>";
        if ($i == 0 or $runSpreadBetFlag == True){
          $SpreadBetUserSettings = getSpreadBetUserSettings();
        }
        if (date("Y-m-d H:i", time()) >= $spreadBetTimer or $runSpreadBetFlag == True){
          $SBcurrent_date = date('Y-m-d H:i');
          $spreadBetTimer = date("Y-m-d H:i",strtotime("+3 minutes 18 seconds", strtotime($SBcurrent_date)));
          $spread = getSpreadBetData();
          $runSpreadBetFlag = False;
        }
        $runSpreadBetFlag = runSpreadBet($spread,$SpreadBetUserSettings);

  echo "</blockquote><BR> Tracking COINS!! $i<blockquote>";
        if (date("Y-m-d H:i", time()) >= $trackingCoinTimer OR $runNewTrackingCoinFlag == True){
          $TCcurrent_date = date('Y-m-d H:i');
          $trackingCoinTimer = date("Y-m-d H:i",strtotime("+2 minutes 8 seconds", strtotime($TCcurrent_date)));
          $newTrackingCoins = getNewTrackingCoins();
          $marketStats = getMarketstats();
          $baseMultiplier = getBasePrices();
          $ruleProfit = getRuleProfit();
          $coinPurchaseSettings = getCoinPurchaseSettings();
          $openTransactions = getOpenTransactions();
          $delayCoinPurchase = getDelayCoinPurchaseTimes();
          $runNewTrackingCoinFlag = False;
        }
        $runNewTrackingCoinFlag = runNewTrackingCoins($newTrackingCoins,$marketStats,$baseMultiplier,$ruleProfit,$coinPurchaseSettings,$clearCoinQueue,$openTransactions,$delayCoinPurchase);
  echo "</blockquote><BR> Tracking SELL COINS!! $i<blockquote>";
        if ((date("Y-m-d H:i", time()) >= $trackingSellCoinTimer) Or ($runTrackingSellCoinFlag == True)) {
          $TSCcurrent_date = date('Y-m-d H:i');
          $trackingSellCoinTimer = date("Y-m-d H:i",strtotime("+2 minutes 15 seconds", strtotime($TSCcurrent_date)));
          $newTrackingSellCoins = getNewTrackingSellCoins();
          $marketStats = getMarketstats();
          $runTrackingSellCoinFlag = False;
        }
        $runTrackingSellCoinFlag = runTrackingSellCoin($newTrackingSellCoins,$marketStats);
  echo "</blockquote><BR> BUY COINS!! $i<blockquote>";
        if ($i == 0 OR $runBuyCoinsFlag == True){$buyRules = getUserRules();}
        if (date("Y-m-d H:i", time()) >= $buyCoinTimer or $runBuyCoinsFlag == True){
          $BCcurrent_date = date('Y-m-d H:i');
          $buyCoinTimer = date("Y-m-d H:i",strtotime("+2 minutes 30 seconds", strtotime($BCcurrent_date)));
          $userProfit = getTotalProfit();
          $marketProfit = getMarketProfit();
          $ruleProfit = getRuleProfit();
          $totalBTCSpent = getTotalBTC();
          $dailyBTCSpent = getDailyBTC();
          $baseMultiplier = getBasePrices();
          $delayCoinPurchase = getDelayCoinPurchaseTimes();
          $coins = getTrackingCoins("WHERE `DoNotBuy` = 0 and `BuyCoin` = 1 ORDER BY `Symbol` ASC","FROM `View1_BuyCoins` ");
          $runBuyCoinsFlag = False;
        }
        $runBuyCoinsFlag = runBuyCoins($coins,$userProfit,$marketProfit,$ruleProfit,$totalBTCSpent,$dailyBTCSpent,$baseMultiplier,$delayCoinPurchase,$buyRules,$coinPriceMatch,$coinPricePatternList,$coin1HrPatternList,$autoBuyPrice,$trackCounter,$buyCounter);
  echo "</blockquote><BR> SELL COINS!! $i<blockquote>";
        if ($i == 0 OR $runSellCoinsFlag == True){$sellRules = getUserSellRules();}
        if (date("Y-m-d H:i", time()) >= $sellCoinTimer or $runSellCoinsFlag == True){
          $SCcurrent_date = date('Y-m-d H:i');
          $sellCoinTimer = date("Y-m-d H:i",strtotime("+2 minutes 25 seconds", strtotime($SCcurrent_date)));
          $sellCoins = getTrackingSellCoins("Sell");
          $userProfit = getTotalProfit();
          $runSellCoinsFlag = False;
          $buyToReduceLossFlag = True;
        }
        $runSellCoinsFlag = runSellCoins($sellRules,$sellCoins,$userProfit,$coinPriceMatch,$coinPricePatternList,$coin1HrPatternList,$autoBuyPrice);
  echo "</blockquote><BR> CHECK BITTREX!! $i<blockquote>";
        if (date("Y-m-d H:i", time()) >= $bittrexReqsTimer or$refreshBittrexFlag == True){
          $BRcurrent_date = date('Y-m-d H:i');
          $bittrexReqsTimer = date("Y-m-d H:i",strtotime("+2 minutes 34 seconds", strtotime($BRcurrent_date)));
          $BittrexReqs = getBittrexRequests();
          $refreshBittrexFlag = False;
        }
        $refreshBittrexFlag = runBittrex($BittrexReqs,$apiVersion);
  echo "</blockquote><BR> CHECK Alerts!! $i<blockquote>";
        if ($refreshAlertsFlag == True){
          $coinAlerts = getCoinAlerts();
          $marketAlerts = getMarketAlertsTotal();
          $spreadBetAlerts = getSpreadBetAlertsTotal();
          $refreshAlertsFlag = False;
        }
        if (date("Y-m-d H:i", time()) >= $alertRunTimer){
          $ALcurrent_date = date('Y-m-d H:i');
          $alertRunTimer = date("Y-m-d H:i",strtotime("+3 minutes 10 seconds", strtotime($ALcurrent_date)));
          $refreshAlertsFlag = runCoinAlerts($coinAlerts,$marketAlerts,$spreadBetAlerts);
        }
  echo "</blockquote><BR> CHECK BUY TO REDUCE LOSS!! $i<blockquote>";
        if ($buyToReduceLossFlag == True){
          $lossCoins = getTrackingSellCoinsAll();
          $buyToReduceLossFlag = False;
          $runSellCoinsFlag = True;
        }
        $buyToReduceLossFlag = buyToreduceLoss($lossCoins);


  sleep(15);
  $i = $i+1;
  $date = date("Y-m-d H:i:s", time());
  if (date("Y-m-d H:i", time()) >= $newTime){ $completeFlag = True;}
}//end While
logAction("Buy Sell Coins End $date : $i", 'BuySellTiming', $GLOBALS['logToFileSetting'] );
//$to, $symbol, $amount, $cost, $orderNo, $score, $subject, $user, $from){
//sendEmail('stevenj1979@gmail.com',$i,0,$date,0,'BuySell Loop Finished', 'stevenj1979', 'Coin Purchase <purchase@investment-tracker.net>');
echo "<br>EndTime ".date("Y-m-d H:i:s", time());
?>
</html>
