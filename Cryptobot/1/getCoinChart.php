<?php
//require('includes/config.php');
include_once ('../../../../SQLData.php');

$conn = getSQLConn(rand(1,3));
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$coinID = $_GET['coinID'];
//setTimeZone();
date_default_timezone_set('Asia/Dubai');
$time = str_replace("_"," ",$_GET['time']);
//$query = "set time_zone='+04:00';";
if(!isset($time)){$time == "6 Hour";}
$query = "SELECT `ActionDate`,FORMAT(`LiveCoinPrice`,8) as LiveCoinPrice
  FROM `CoinBuyHistory`
  WHERE  (`ActionDate` > DATE_SUB(now(), INTERVAL $time)) and ID = (select Max(`ID`) from `Coin` where `ID` = $coinID)
  order by `ActionDate` asc
  limit 500";
  //echo "<br>$query<br>";
//$query = "SELECT `ActionDate`,`LiveCoinPrice` as LiveCoinPrice FROM `CoinBuyHistory` WHERE ID = (
//  select `ID` from `Coin` where `Symbol` = '$coinID')
//   and DATE_ADD(`ActionDate`, INTERVAL 24 HOUR) >= now() order by `ActionDate` asc";
//echo "<br>$time<br>";
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
    array('label' => $coinID, 'type' => 'number')
);

$rows = array();
$result = $conn->query($query);
while ($row = mysqli_fetch_assoc($result)){
    $temp = array();
    // each column needs to have data inserted via the $temp array
    $temp[] = array('v' => $row['ActionDate']);
    $temp[] = array('v' => (float) $row['LiveCoinPrice']);

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
