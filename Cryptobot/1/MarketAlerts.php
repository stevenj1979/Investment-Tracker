<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<?php require('includes/config.php');
include_once '../includes/newConfig.php';

//if not logged in redirect to login page
if(!$user->is_logged_in()){ header('Location: login.php'); exit(); }

//define page title
$title = 'CryptoBot';
$current_url = $_SERVER[ 'REQUEST_URI' ];
header( "Refresh: 120; URL=$current_url" );
//include header template
require($_SERVER['DOCUMENT_ROOT'].'/Investment-Tracker/Cryptobot/1/layout/header.php');
include_once ('/home/stevenj1979/SQLData.php');

setStyle($_SESSION['isMobile']);

if (isset($_get['alert'])){
  echo "<BR> GET ALERT : ".$_get['alert'];
  if ($_get['alert'] == 1){
      //Edit Market Alerts
      echo "<BR> Edit Alert".$_get['edit'];
  }elseif ($_get['alert'] == 4){
      //Delete
      echo "<BR> Delete Alert".$_get['edit'];
  }
}else{
	showMain();
}

Function  getMarketAlertsUser($userID){
  $conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `ID`, `Action`, `Price`, `UserName`,`Email` ,`LiveCoinPrice`,`Category`,`Hr1PctChange` ,`Hr24PctChange` ,`D7PctChange`,`ReocurringAlert`,`DateTimeSent`,`Minutes`,`LiveMarketPctChange`
  FROM `MarketAlertsView` WHERE `UserID` = $userID";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['ID'],$row['Action'],$row['Price'],$row['UserName'],$row['Email'],$row['LiveCoinPrice'],$row['Category'],$row['Hr1PctChange'] //7
      ,$row['Hr24PctChange'],$row['D7PctChange'],$row['ReocurringAlert'],$row['DateTimeSent'],$row['Minutes'],$row['LiveMarketPctChange']);
  }
  $conn->close();
  return $tempAry;
}

Function showMain(){
  displayHeader(8);
  $userID = $_SESSION['ID'];
  if ($_SESSION['isMobile']){ $num = 2; $fontSize = "font-size:60px"; }else{$num = 8;$fontSize = "font-size:32px"; }
  NewEcho("<h2>Coin Alerts!</h2>",$_SESSION['isMobile'] ,2);
  echo "<h3><a href='CoinAlerts.php'>Coin Alerts</a> &nbsp > &nbsp <a href='MarketAlerts.php'>Market Alerts</a></h3>";
  NewEcho("<Table><th>Edit</th><th>&nbspID</th><TH>&nbspAction</th><TH>&nbspPrice</th>",$_SESSION['isMobile'] ,2);
  newEcho("<TH>&nbspUserName</th><TH>&nbspEmail</th>",$_SESSION['isMobile'] ,0);
  newEcho("<TH>&nbspliveCoinPrice</th><TH>&nbspCategory</th><th>Reocurring</th><TH>Price Pct Change</TH><TH>&nbspDelete Alert</th><tr>",$_SESSION['isMobile'] ,2);
  $coinAlerts = getMarketAlertsUser($userID);
  $newArrLength = Count($coinAlerts);
  for($x = 0; $x < $newArrLength; $x++) {
    $id = $coinAlerts[$x][0]; $action = $coinAlerts[$x][1];
    $price = round($coinAlerts[$x][2],$roundNum); $userName = $coinAlerts[$x][3];
    $email = $coinAlerts[$x][4];$liveCoinPrice= round($coinAlerts[$x][5],$roundNum); $category = $coinAlerts[$x][6];
    $reocurring = $coinAlerts[$x][10];  $marketPctChange = $coinAlerts[$x][13];
    NewEcho("<td><a href='MarketAlerts.php?alert=1&edit=".$id."'><span class='glyphicon glyphicon-pencil' style='$fontSize;'></span></a></td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td>$id</td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td>$action</td><td>$price</td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td>$userName</td><td>$email</td>",$_SESSION['isMobile'] ,0);
    NewEcho("<td>$liveCoinPrice</td><td>$category</td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td>$reocurring</td><td>$marketPctChange</td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td><a href='MarketAlerts.php?alert=4&iD=$id'><i class='glyphicon glyphicon-trash' style='$fontSize;color:#D4EFDF'></i></a></td>",$_SESSION['isMobile'] ,2);
 	   NewEcho("<TR>",$_SESSION['isMobile'] ,2);
 	 }
 	 Echo "</table>";
  	displaySideColumn();
	  //displayMiddleColumn();
	  //displayFarSideColumn();
	  //displayFooter();
	}


//include header template
require('layout/footer.php');
?>
