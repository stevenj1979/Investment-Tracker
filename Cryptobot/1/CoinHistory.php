<?php
include '../../../NewSQLData.php';
$coin = trim($_GET['coin']);
$conn = getSQL(rand(1,4));
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$query = "SELECT `LiveCoinPrice`,`ActionDate` FROM `CoinBuyHistory` WHERE `Symbol` = '$coin' order by `ActionDate` desc";
//echo $query;
$result = $conn->query($query);
$rows = array();
$table = array();

$table['cols'] = array(
 array(
  "label" => "Date Time",
  "type" => "datetime"
 ),
 array(
  "label" => "Live Price",
  "type" => "number"
 )
);
while ($row = mysqli_fetch_assoc($result)){
  $sub_array = array();
 $datetime = JSdate($row['ActionDate'],datetime);
 $sub_array[] =  array(
      "v" => $datetime
     );
 $sub_array[] =  array(
      "v" => $row['LiveCoinPrice']
     );
 $rows[] =  array(
     "c" => $sub_array
    );
}
$table['rows'] = $rows;
$jsonTable = json_encode($table);
$conn->close();

//$jsonTable =
//echo var_dump($jsonTable);
?>
<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
  <script type="text/javascript">
   google.charts.load('current', {'packages':['corechart']});
   google.charts.setOnLoadCallback(drawChart);
   function drawChart(){
    var data = new google.visualization.DataTable(<?php echo $jsonTable; ?>);
        //data.addColumn("datetime", "Action_Date");
        //data.addColumn("number", "Live_Price");
        //data.addRows();

    var options = {
     title:'Sensors Data',
     legend:{position:'bottom'},
     chartArea:{width:'95%', height:'65%'}
    };

    var chart = new google.visualization.LineChart(document.getElementById('line_chart'));

    chart.draw(data, options);
   }
   </script>
</head>
<?php require('includes/config.php');?>
<style>
<?php include 'style/style.css'; ?>
.page-wrapper
  {
   width:1000px;
   margin:0 auto;
 }
</style> <?php

//if not logged in redirect to login page
if(!$user->is_logged_in()){ header('Location: login.php'); exit(); }

//define page title
$title = 'CryptoBot';
$current_url = $_SERVER[ 'REQUEST_URI' ];
header( "Refresh: 300; URL=$current_url" );
//include header template
require('layout/header.php');




function getCoinPrice($coin){
    $tempAry = [];
    $conn = getSQL(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT `LiveCoinPrice`,`ActionDate` FROM `CoinBuyHistory` WHERE `Symbol` = '$coin' order by `ActionDate` desc ";
    $result = $conn->query($sql);
    $rows = array();
    $table = array();

    $table['cols'] = array(
     array(
      'label' => 'Action_Date',
      'type' => 'datetime'
     ),
     array(
      'label' => 'Live_Price',
      'type' => 'number'
     )
    );

    while ($row = mysqli_fetch_assoc($result)){
     $sub_array = array();
     $datetimeArray = explode(" ",$row["ActionDate"]);
     $dateArray = explode("-",$datetimeArray[0]);
     $year = $dateArray[0];
     $month = $dateArray[1] - 1; // adjust for javascript's 0-indexed months
     $day = $dateArray[2];
     $timeArray = explode(':', $datetimeArray[1]);
      $hours = $timeArray[0];
      $minutes = $timeArray[1];
      $seconds = $timeArray[2];
     $datetime = "New Date($year, $month, $day, $hours, $minutes, $seconds, 0)";
     $sub_array[] =  array(
          "v" => $datetime
         );
     $sub_array[] =  array(
          "v" => $row["LiveCoinPrice"]
         );
     $rows[] =  array(
         "c" => $sub_array
        );
    }
    $table['rows'] = $rows;
    $jsonTable = json_encode($table);
    return $jsonTable;
}

function JSdate($in,$type){
      if($type=='date'){
        //Dates are patterned 'yyyy-MM-dd'
        preg_match('/(\d{4})-(\d{2})-(\d{2})/', $in, $match);
    } elseif($type=='datetime'){
        //Datetimes are patterned 'yyyy-MM-dd hh:mm:ss'
        preg_match('/(\d{4})-(\d{2})-(\d{2})\s(\d{2}):(\d{2}):(\d{2})/', $in, $match);
    }
    //echo $in;
    $year = (int) $match[1];
    $month = (int) $match[2] - 1; // Month conversion between indexes
    $day = (int) $match[3];

    if ($type=='date'){
        return "Date($year, $month, $day)";
    } elseif ($type=='datetime'){
        $hours = (int) $match[4];
        $minutes = (int) $match[5];
        $seconds = (int) $match[6];
        return "new Date($year, $month, $day, $hours, $minutes, $seconds)";
    }
}

function getTrackingCoins(){
  $conn = getSQL(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `Symbol`,`MarketCapPct`,`VolumebyPct`,`BuyOrdersPct`,`PriceDiff4`,`PriceDiff3`,`PriceDiff2`,`PriceDiff1` FROM `CryptoBotCoinPurchaseStatsView`";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
//mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Symbol'],$row['MarketCapPct'],$row['VolumebyPct'],$row['BuyOrdersPct'],$row['PriceDiff4'],$row['PriceDiff3'],$row['PriceDiff2'],$row['PriceDiff1']);
  }
  $conn->close();
  return $tempAry;
}

function getNumberColour($ColourText, $target){
  if ($ColourText >= $target){
    $colour = "green";
  }else{
    $colour = "red";
  }
  //echo $colour;
  return $colour;
}

function getConfig($userID){
  $conn = getSQL(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `MarketCapBuyPct`,`VolumeBuyPct`,`BuyOrdersPct`,`MinPctGain`  FROM `Config` WHERE `UserID` =  $userID";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['MarketCapBuyPct'],$row['VolumeBuyPct'],$row['BuyOrdersPct'],$row['MinPctGain']);
  }
  $conn->close();
  return $tempAry;
}

function sendEmail($to, $symbol, $amount, $cost){

    //$to = $row['Email'];
    //echo $row['Email'];
    $subject = "Coin Sale: ".$symbol;
    $body = "Dear Steven, <BR/>";
    $body .= "Congratulations you have sold the following Coin: "."<BR/>";
    $body .= "Coin: ".$symbol." Amount: ".$amount." Price: ".$cost."<BR/>";
    $body .= "Kind Regards\nCryptoBot.";
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $headers .= 'From: Alerts <Alerts@Investment-Tracker.net>' . "\r\n";
    $headers .= "To:".$to."\r\n";
    mail($to, $subject, wordwrap($body,70),$headers);

}

function bittrexbalance($apikey, $apisecret){
    $nonce=time();
    $uri='https://bittrex.com/api/v1.1/account/getbalance?apikey='.$apikey.'&currency=BTC&nonce='.$nonce;
    $sign=hash_hmac('sha512',$uri,$apisecret);
    $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $execResult = curl_exec($ch);
    $obj = json_decode($execResult, true);
    $balance = $obj["result"]["Available"];
    return $balance;
}

function getLiveCoinPrice($symbol){
    $limit = 500;
    $cnmkt = "https://api.coinmarketcap.com/v1/ticker/?limit=".$limit;
    $fgc = json_decode(file_get_contents($cnmkt), true);

  for($i=0;$i<$limit;$i++){
    //print_r($i);

    if ($fgc[$i]["symbol"] == $symbol){
      //print_r($fgc[$i]["symbol"]);
      $tmpCoinPrice = $fgc[$i]["price_btc"];

    }
  }
  return $tmpCoinPrice;
}



?>

<!-- <div class="container">

	<div class="row">

    <div class="col-xs-12 col-sm-8 col-md-8 col-sm-offset-2">-->
<div class="header">
  <table><TH><table class="CompanyName"><td rowspan="2" class="CompanyName"><img src='Images/CBLogoSmall.png' width="40"></td><td class="CompanyName"><div class="Crypto">Crypto</Div><td><tr class="CompanyName">
      <td class="CompanyName"><Div class="Bot">Bot</Div></td></table></TH><TH>: Logged in as:</th><th> <i class="glyphicon glyphicon-user"></i>  <?php echo $_SESSION['username'] ?></th></Table><br>
</div>
<div class="topnav">
  <a href="Dashboard.php">Dashboard</a>
  <a href="Transactions.php">Transactions</a>
  <a href="Stats.php">Stats</a>
  <a href="BuyCoins.php" class="active">Buy Coins</a>
  <a href="SellCoins.php">Sell Coins</a>
  <a href="Profit.php">Profit</a>
  <a href="bittrexOrders.php">Bittrex Orders</a>
  <a href="Settings.php">Settings</a><?php
  if ($_SESSION['AccountType']==1){echo "<a href='AdminSettings.php'>Admin Settings</a>";}
  ?>
</div>

  <div class="statscolumn">

  				<?php
				      //$stats = getCoinPrice($coin);
              //echo $stats;
				?>


        <div class="page-wrapper">
           <br />
           <h2 align="center">Display Google Line Chart with JSON PHP & Mysql</h2>
           <div id="line_chart" style="width: 100%; height: 500px"></div>
        </div>
      </div>

      <div class="footer">
          <hr>
          <!-- <input type="button" value="Logout">
          <a href='logout.php'>Logout</a>-->

          <input type="button" onclick="location='logout.php'" value="Logout"/>

      </div>

<?php
//include header template
require('layout/footer.php');
?>
