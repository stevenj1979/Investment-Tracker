<html>
<?php
ini_set('max_execution_time', 300);
require('includes/newConfig.php');

include_once ('/home/stevenj1979/SQLData.php');
include_once ('/home/stevenj1979/Encrypt.php');

//$apikey=getAPIKey();
$apisecret=getAPISecret();
//echo "<BR>API Secret is:  $apisecret";


function testBuyCoins(){
  $coinPriceMatch = getCoinPriceMatchList();
  $coinPricePatternList = getCoinPricePattenList();
  $coin1HrPatternList = getCoin1HrPattenList();
  $autoBuyPrice = getAutoBuyPrices();
$test1 = buyWithScore(10,5,6,1); //true

$test2 = buyWithScore(10,5,11,1); //False
$test3 = buyWithScore(10,5,4,1); //false
$test4 = buyWithScore(10,5,6,0); //True
//$test5 = buyWithScore($MarketCapTop,$MarketCapBtm,$MarketCapPctChange,$MarketCapEnabled);
$test6 = newBuywithPattern('-1-1-11',$coinPricePatternList,1,14,0);//True
$test7 = newBuywithPattern('-10-11',$coinPricePatternList,1,14,0);//True
$test8 = newBuywithPattern('-1-100',$coinPricePatternList,1,14,0);//false
$test9 = newBuywithPattern('-1-1-11',$coinPricePatternList,0,14,0);//True

$test10 = buyWithMin(1,8700,9600);//True
$test11 = buyWithMin(1,8200,8100);//False
$test12 = buyWithMin(1,8200,8300);//True
$test13 = buyWithMin(0,8200,8900);//True

$test14 = autoBuyMain(10000,$autoBuyPrice, 1,84);//False
$test15 = autoBuyMain(8600,$autoBuyPrice, 1,84);//True
$test16 = autoBuyMain(5300,$autoBuyPrice, 1,84);//FALSE
$test17 = autoBuyMain(8600,$autoBuyPrice, 0,84);//True

$test18 = coinMatchPattern($coinPriceMatch,9400,'BTC',0,1,14,0);//True
$test19 = coinMatchPattern($coinPriceMatch,8400,'BTC',0,1,14,0);//True
$test20 = coinMatchPattern($coinPriceMatch,6100,'BTC',0,1,14,0);//FALSE
$test21 = coinMatchPattern($coinPriceMatch,8400,'BTC',0,0,14,0);//True

//isCoinMatch($bitPrice, $symbol, $livePrice, $liveSymbol, $isGreater)
$test22 = isCoinMatch(6100, 'BTC', 7100, 'BTC', 0, 0);//True
$test23 = isCoinMatch(8300, 'BTC', 7100, 'BTC', 0, 0);//FALSE
$test24 = isCoinMatch(7100, 'BTC', 7100, 'BTC', 0, 0);//True

$test25 = isCoinMatch(6100, 'BTC', 7100, 'BTC', 1, 0);//FALSE
$test26 = isCoinMatch(8100, 'BTC', 7100, 'BTC', 1, 0);//TRUE
$test27 = isCoinMatch(7100, 'BTC', 7100, 'BTC', 1, 0);//TRUE

$test28 = coinMatchPattern($coinPriceMatch,212,'ETH',0,1,36,0);//True

$test29 = coinMatchPattern($coinPriceMatch,209,'ETH',0,0,37,0);//True
$test30 = coinMatchPattern($coinPriceMatch,209,'ETH',0,1,37,0);//True

Echo "<BR> TEST1 buyWithScore(10,5,6,1);";
if ($test1) {Echo " :PASS";}//else{Echo "FAIL";}
Echo "<BR> TEST2 buyWithScore(10,5,11,1);";
if ($test2 == FALSE) {Echo " :PASS";}
Echo "<BR> TEST3 buyWithScore(10,5,4,1); ";
if ($test3 == FALSE) {Echo " :PASS";}
Echo "<BR> TEST4 buyWithScore(10,5,6,0);";
if ($test4) {Echo " :PASS";}
Echo "<BR> TEST6 newBuywithPattern('-1-1-11','*-1-11,*-101,*0-11,*001',1,14,0);";
if ($test6) {Echo " :PASS";}
Echo "<BR> TEST7 newBuywithPattern('-10-11','*-1-11,*-101,*0-11,*001',1,14,0);";
if ($test7) {Echo " :PASS";}
Echo "<BR> TEST8 newBuywithPattern('-1-100','*-1-11,*-101,*0-11,*001',1,14,0);";
if ($test8 == False) {Echo " :PASS";}
Echo "<BR> TEST9 newBuywithPattern('-1-1-11','*-1-11,*-101,*0-11,*001',0,14,0);";
if ($test9 ) {Echo " :PASS";}
Echo "<BR> TEST10 buyWithMin(1,8700,9600);";
if ($test10 ) {Echo " :PASS";}
Echo "<BR> TEST11 buyWithMin(1,8200,8100);";
if ($test11 == False) {Echo " :PASS";}
Echo "<BR> TEST12 buyWithMin(1,8200,8300);";
if ($test12 ) {Echo " :PASS";}
Echo "<BR> TEST13 buyWithMin(0,8200,8900);";
if ($test13 ) {Echo " :PASS";}
Echo "<BR> TEST14 autoBuyMain(10000,'BTC:8870.00000000:6190.000000', 1,84);";
if ($test14 == False) {Echo " :PASS";}
Echo "<BR> TEST15 autoBuyMain(8600,'BTC:8870.00000000:6190.000000', 1,84);";
if ($test15) {Echo " :PASS";}
Echo "<BR> TEST16 autoBuyMain(5300,'BTC:8870.00000000:6190.000000', 1,84);";
if ($test16 == False) {Echo " :PASS";}
Echo "<BR> TEST17 autoBuyMain(8600,'BTC:8870.00000000:6190.000000', 0,84);";
if ($test17 ) {Echo " :PASS";}
Echo "<BR> TEST18 coinMatchPattern('BTC:7200:0',9400,'BTC',0,1,14,0);";
if ($test18 == False) {Echo " :PASS";}
Echo "<BR> TEST19 coinMatchPattern('BTC:7200:0',8400,'BTC',0,1,14,0);";
if ($test19 == False) {Echo " :PASS";}
Echo "<BR> TEST20 coinMatchPattern('BTC:7200:0',6100,'BTC',0,1,14,0);";
if ($test20) {Echo " :PASS";}
Echo "<BR> TEST21 coinMatchPattern('BTC:7200',8400,'BTC',0,0,14,0);";
if ($test21 ) {Echo " :PASS";}
Echo "<BR> TEST22 isCoinMatch(6100, 'BTC', 7100, 'BTC', 0, 0);";
if ($test22 == False) {Echo " :PASS";}
Echo "<BR> TEST23 isCoinMatch(8300, 'BTC', 7100, 'BTC', 0, 0);";
if ($test23 ) {Echo " :PASS";}
Echo "<BR> TEST24 isCoinMatch(7100, 'BTC', 7100, 'BTC', 0, 0);";
if ($test24 ) {Echo " :PASS";}
Echo "<BR> TEST25 isCoinMatch(6100, 'BTC', 7100, 'BTC', 1, 0);";
if ($test25 == False ) {Echo " :PASS";}
Echo "<BR> TEST26 isCoinMatch(8100, 'BTC', 7100, 'BTC', 1, 0);";
if ($test26 ) {Echo " :PASS";}
Echo "<BR> TEST27 isCoinMatch(7100, 'BTC', 7100, 'BTC', 1, 0);";
if ($test27 ) {Echo " :PASS";}

Echo "<BR> TEST28 coinMatchPattern('ETH:150',212,'ETH',0,1,36,0);";
if ($test28 == False) {Echo " :PASS";}


Echo "<BR> TEST29 coinMatchPattern('ETH:150',209,'ETH',0,0,37,0);";
if ($test29 == False) {Echo " :PASS";}


Echo "<BR> TEST30 coinMatchPattern('ETH:150',209,'ETH',0,1,37,0);";
if ($test30 == False) {Echo " :PASS";}
}

function testSellCoins(){

}

function testBittrex(){

}

function testAlerts(){

}

testBuyCoins();
?>
</html>
