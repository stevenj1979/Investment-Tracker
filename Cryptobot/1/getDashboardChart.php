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
  return $tmpCoinPrice;
}

$conn = getSQLConn(rand(1,3));
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$userID = $_GET['ID'];
//$btcPrice = getLiveCoinPriceUSD('BTC');
//$usdtPrice = getLiveCoinPriceUSD('USDT');
//$ethPrice = getLiveCoinPriceUSD('ETH');
$query = "SELECT sum(`LiveBTC`+`BittrexBTC`)* `BTCPrice` as TotalBTC
,sum(`LiveUSDT`+`BittrexUSDT`)* `USDTPrice`as TotalUSDT
,sum(`LiveETH`+`BittrexETH`)*`ETHPrice` as TotalETH
, cast(`ActionDate` as date) AS ActionDate
FROM `UserProfit`
WHERE `UserID` = $userID and DATE(`ActionDate`) > DATE( DATE_SUB( curdate() , INTERVAL 3 WEEK ) )
group by day(`ActionDate`),Month(`ActionDate`),Year(`ActionDate`)
order by `ActionDate` asc
limit 21";
$table = array();
$table['cols'] = array(
    /* define your DataTable columns here
     * each column gets its own array
     * syntax of the arrays is:
     * label => column label
     * type => data type of column (string, number, date, datetime, boolean)
     */
    // I assumed your first column is a "string" type
    // and your second column is a "number" type
    // but you can change them if they are not
    array('label' => 'ActionDate', 'type' => 'string'),
    array('label' => 'TotalBTC', 'type' => 'number'),
    array('label' => 'TotalUSDT', 'type' => 'number'),
    array('label' => 'TotalETH', 'type' => 'number')
);

$rows = array();
$result = $conn->query($query);
while ($row = mysqli_fetch_assoc($result)){
    $temp = array();
    // each column needs to have data inserted via the $temp array
    $temp[] = array('v' => $row['ActionDate']);
    //$temp[] = array('v' => (float) $row['TotalBTC']*$btcPrice);
    $temp[] = array('v' => (float) $row['TotalBTC']);
    //$temp[] = array('v' => (float) $row['TotalUSDT']*$usdtPrice);
    $temp[] = array('v' => (float) $row['TotalUSDT']);
    //$temp[] = array('v' => (float) $row['TotalETH']*$ethPrice);
    $temp[] = array('v' => (float) $row['TotalETH']);
    // insert the temp array into $rows
    $rows[] = array('c' => $temp);
}

// populate the table with rows of data
$table['rows'] = $rows;

// encode the table as JSON
$jsonTable = json_encode($table);

// set up header; first two prevent IE from caching queries
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');

// return the JSON data
echo $jsonTable;
?>
