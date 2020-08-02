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
//$test6 = newBuywithPattern('-1-1-11',$coinPricePatternList,1,14,0);//True
$test7 = newBuywithPattern('-10-11',$coinPricePatternList,1,14,0);//True
//$test8 = newBuywithPattern('-1-100',$coinPricePatternList,1,14,0);//false
//$test9 = newBuywithPattern('-1-1-11',$coinPricePatternList,0,14,0);//True

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

Echo "<BR> TEST1 buyWithScore(10,5,6,1);$test1";
if ($test1) {Echo " :PASS";}//else{Echo "FAIL";}
Echo "<BR> TEST2 buyWithScore(10,5,11,1);$test2";
if ($test2 == FALSE) {Echo " :PASS";}
Echo "<BR> TEST3 buyWithScore(10,5,4,1); $test3";
if ($test3 == FALSE) {Echo " :PASS";}
Echo "<BR> TEST4 buyWithScore(10,5,6,0);$test4";
if ($test4) {Echo " :PASS";}
Echo "<BR> TEST6 newBuywithPattern('-1-1-11','*-1-11,*-101,*0-11,*001',1,14,0);$test6";
if ($test6) {Echo " :PASS";}
Echo "<BR> TEST7 newBuywithPattern('-10-11','*-1-11,*-1*0-11,*001',1,14,0);$test7"; //'*-1-11,*-101,*0-11,*001'
if ($test7) {Echo " :PASS";}
Echo "<BR> TEST8 newBuywithPattern('-1-100','*-1-11,*-101,*0-11,*001',1,14,0);$test8";
if ($test8 == False) {Echo " :PASS";}
Echo "<BR> TEST9 newBuywithPattern('-1-1-11','*-1-11,*-101,*0-11,*001',0,14,0);$test9";
if ($test9 ) {Echo " :PASS";}
Echo "<BR> TEST10 buyWithMin(1,8700,9600);$test10";
if ($test10 ) {Echo " :PASS";}
Echo "<BR> TEST11 buyWithMin(1,8200,8100);$test11";
if ($test11 == False) {Echo " :PASS";}
Echo "<BR> TEST12 buyWithMin(1,8200,8300);$test12";
if ($test12 ) {Echo " :PASS";}
Echo "<BR> TEST13 buyWithMin(0,8200,8900);$test13";
if ($test13 ) {Echo " :PASS";}
Echo "<BR> TEST14 autoBuyMain(10000,'BTC:8870.00000000:6190.000000', 1,84);$test14";
if ($test14 == False) {Echo " :PASS";}
Echo "<BR> TEST15 autoBuyMain(8600,'BTC:8870.00000000:6190.000000', 1,84);$test15";
if ($test15) {Echo " :PASS";}
Echo "<BR> TEST16 autoBuyMain(5300,'BTC:8870.00000000:6190.000000', 1,84);$test16";
if ($test16 == False) {Echo " :PASS";}
Echo "<BR> TEST17 autoBuyMain(8600,'BTC:8870.00000000:6190.000000', 0,84);$test17";
if ($test17 ) {Echo " :PASS";}
Echo "<BR> TEST18 coinMatchPattern('BTC:7200:0',9400,'BTC',0,1,14,0);$test18";
if ($test18 == False) {Echo " :PASS";}
Echo "<BR> TEST19 coinMatchPattern('BTC:7200:0',8400,'BTC',0,1,14,0);$test19";
if ($test19 == False) {Echo " :PASS";}
Echo "<BR> TEST20 coinMatchPattern('BTC:7200:0',6100,'BTC',0,1,14,0);$test20";
if ($test20) {Echo " :PASS";}
Echo "<BR> TEST21 coinMatchPattern('BTC:7200',8400,'BTC',0,0,14,0);$test21";
if ($test21 ) {Echo " :PASS";}
Echo "<BR> TEST22 isCoinMatch(6100, 'BTC', 7100, 'BTC', 0, 0);$test22";
if ($test22 == False) {Echo " :PASS";}
Echo "<BR> TEST23 isCoinMatch(8300, 'BTC', 7100, 'BTC', 0, 0);$test23";
if ($test23 ) {Echo " :PASS";}
Echo "<BR> TEST24 isCoinMatch(7100, 'BTC', 7100, 'BTC', 0, 0);$test24";
if ($test24 ) {Echo " :PASS";}
Echo "<BR> TEST25 isCoinMatch(6100, 'BTC', 7100, 'BTC', 1, 0);$test25";
if ($test25 == False ) {Echo " :PASS";}
Echo "<BR> TEST26 isCoinMatch(8100, 'BTC', 7100, 'BTC', 1, 0);$test26";
if ($test26 ) {Echo " :PASS";}
Echo "<BR> TEST27 isCoinMatch(7100, 'BTC', 7100, 'BTC', 1, 0);$test27";
if ($test27 ) {Echo " :PASS";}

Echo "<BR> TEST28 coinMatchPattern('ETH:150',212,'ETH',0,1,36,0);$test28";
if ($test28 == False) {Echo " :PASS";}


Echo "<BR> TEST29 coinMatchPattern('ETH:150',209,'ETH',0,0,37,0);$test29";
if ($test29) {Echo " :PASS";} //Disabled


Echo "<BR> TEST30 coinMatchPattern('ETH:150',209,'ETH',1,1,37,0);$test30";
if ($test30 == False) {Echo " :PASS";}
}

function testSellCoins(){
  $coinPriceMatch = getCoinPriceMatchList();
  $coinPricePatternList = getCoinPricePattenList();
  $coin1HrPatternList = getCoin1HrPattenList();
  $autoBuyPrice = getAutoBuyPrices();
  $sTest1 = sellWithScore(1.5,1.0,0.9,1);//False
  $sTest2 = sellWithScore(10.0,9.0,9.5,1);//True
  $sTest3 = sellWithScore(-1.0,-6.0,-2.0,1);//True
  $sTest4 = sellWithScore(1.5,1.0,0.9,0);//True disabled
  $sTest5 = sellWithScore(10.0,9.0,8.0,1);//False
  $sTest6 = sellWithScore(-1.0,-6.0,-2.0,1);//True
  $sTest7 = sellWithScore(0,-5,-4,1);//True
  $sTest8 = sellWithScore(10,8,9,1);//True
  $sTest9 = sellWithScore(15,14,13,1);//False
  $sTest10 = sellWithScore(10.0,1.0,3.0,1);//True
  $sTest11 = sellWithScore(20.0,18.0,16.0,1);//False
  $sTest12 = sellWithScore(-1,-20,-21,1);//False

  $sTest19 = newBuywithPattern("111-1",$coinPricePatternList,1,8,1);//True
  $sTest20 = newBuywithPattern("1111",$coinPricePatternList,1,8,1);//false
  $sTest21 = newBuywithPattern("111-1",$coinPricePatternList,0,8,1);//True
  $sTest22 = sellWithMin(1,9600,$LiveCoinPrice,$LiveBTCPrice);
  $sTest23 = sellWithMin($sellPriceMinEnabled,$sellPriceMin,$LiveCoinPrice,$LiveBTCPrice);
  $sTest24 = sellWithMin($sellPriceMinEnabled,$sellPriceMin,$LiveCoinPrice,$LiveBTCPrice);

  $sTest31 = coinMatchPattern($coinPriceMatch,9600,'BTC',1,1,8,1);
  $sTest32 = coinMatchPattern($coinPriceMatch,200,'ETH',1,1,8,1);
  $sTest33 = coinMatchPattern($coinPriceMatch,8000,'BTC',1,1,8,1);
  $sTest34 = autoSellMain(9002,$autoBuyPrice,1,84);
  $sTest35 = autoSellMain(200,$autoBuyPrice,1,85);
  $sTest36 = autoSellMain(9000,$autoBuyPrice,1,84);
  Echo "<BR> Sell TEST1 sellWithScore(1.5,1.0,0.9,1);";
  if ($sTest1 == False) {Echo " :PASS";}
  Echo "<BR> Sell TEST2 sellWithScore(10.0,9.0,9.5,1);";
  if ($sTest2) {Echo " :PASS";}
  Echo "<BR> Sell TEST3 sellWithScore(-1.0,-6.0,-2.0,1);";
  if ($sTest3) {Echo " :PASS";}
  Echo "<BR> Sell TEST4 sellWithScore(1.5,1.0,0.9,0);";
  if ($sTest4) {Echo " :PASS";}
  Echo "<BR> Sell TEST5 sellWithScore(10.0,9.0,8.0,1);";
  if ($sTest5 == False) {Echo " :PASS";}
  Echo "<BR> Sell TEST6 sellWithScore(-1.0,-6.0,-2.0,1);";
  if ($sTest6) {Echo " :PASS";}
  Echo "<BR> Sell TEST7 sellWithScore(0,-5,-4,1);";
  if ($sTest7) {Echo " :PASS";}
  Echo "<BR> Sell TEST8 sellWithScore(10,8,9,1);";
  if ($sTest8) {Echo " :PASS";}
  Echo "<BR> Sell TEST9 sellWithScore(15,14,13,1);";
  if ($sTest9 == False) {Echo " :PASS";}
  Echo "<BR> Sell TEST10 sellWithScore(10.0,1.0,3.0,1);";
  if ($sTest10) {Echo " :PASS";}
  Echo "<BR> Sell TEST11 sellWithScore(20.0,18.0,16.0,1);";
  if ($sTest11 == False) {Echo " :PASS";}
  Echo "<BR> Sell TEST12 sellWithScore(-1,-20,-21,1);";
  if ($sTest12 == False) {Echo " :PASS";}
  Echo "<BR> Sell TEST19 newBuywithPattern('111-1','111-1',1,8,1);";
  if ($sTest19) {Echo " :PASS";}
  Echo "<BR> Sell TEST20 newBuywithPattern('1111','111-1',1,8,1);";
  if ($sTest20 == False) {Echo " :PASS";}
  Echo "<BR> Sell TEST21 newBuywithPattern('111-1','111-1',0,8,1);";
  if ($sTest21) {Echo " :PASS";}
  Echo "<BR> Sell TEST31 coinMatchPattern(20000:8200,9600,'BTC',1,1,8,1);";
  if ($sTest31) {Echo " :PASS";}
  Echo "<BR> Sell TEST32 coinMatchPattern(1000:260,200,'ETH',1,1,8,1);";
  if ($sTest32 == False) {Echo " :PASS";}
  Echo "<BR> Sell TEST33 coinMatchPattern(20000:8200,8000,'BTC',1,1,8,1);";
  if ($sTest33 == False) {Echo " :PASS";}
  Echo "<BR> Sell TEST34 autoSellMain(9002,9001,1,84);";
  if ($sTest34) {Echo " :PASS";}
  Echo "<BR> Sell TEST35 autoSellMain(200,207,1,85);";
  if ($sTest35 == False) {Echo " :PASS";}
  Echo "<BR> Sell TEST36 autoSellMain(9000,9001,1,84);";
  if ($sTest36 == False) {Echo " :PASS";}
}

function testBittrex(){

}

function testAlerts(){

}

testBuyCoins();
testSellCoins();
?>
</html>
