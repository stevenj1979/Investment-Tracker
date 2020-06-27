<?php
//require('includes/config.php');
include_once ('/home/stevenj1979/SQLData.php');

function getLiveCoinPriceUSD($symbol){
    $limit = 100;
    $cnmkt = "https://api.coinmarketcap.com/v1/ticker/?limit=".$limit;
    $fgc = json_decode(file_get_contents($cnmkt), true);
  for($i=0;$i<$limit;$i++){
    if ($fgc[$i]["symbol"] == $symbol){$tmpCoinPrice = $fgc[$i]["price_usd"];}
  }
  logAction("$cnmkt",'CMC');
  return $tmpCoinPrice;
}
$coinID = 'BTC';
$conn = getSQLConn(rand(1,3));
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//$btcPrice = getLiveCoinPriceUSD('BTC');
//$usdtPrice = getLiveCoinPriceUSD('USDT');
//$ethPrice = getLiveCoinPriceUSD('ETH');
$query = "SELECT `LiveCoinPrice` as LiveCoinPrice
  FROM `CoinBuyHistory`
  WHERE  (`ActionDate` > DATE_SUB((select Max(`ActionDate`) from `CoinBuyHistory`), INTERVAL 1 Hour)) and `ID` = (select Max(`ID`) from `Coin` where `Symbol` = 'BTC')
  order by `ActionDate` asc ";


  $result = $conn->query($query);
  while ($row = mysqli_fetch_assoc($result)){
      // each column needs to have data inserted via the $temp array
      $temp[] = array((float) $row['LiveCoinPrice']);

      // insert the temp array into $rows
  }

  // populate the table with rows of data

  // encode the table as JSON
  $jsonTable = json_encode($temp);

  // set up header; first two prevent IE from caching queries
  header('Cache-Control: no-cache, must-revalidate');
  header('Content-type: application/json');

  // return the JSON data
  echo $jsonTable;
?>
