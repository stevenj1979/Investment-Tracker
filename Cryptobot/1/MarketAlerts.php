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

if (isset($_get[‘alert’])){

}else{
	showMain();
}

Function  getMarketAlertsUser($userID){

$conn = getSQLConn(rand(1,3));
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "SELECT `ID`, `Action`, `Price`, `UserName`,`Email` ,`LiveCoinPrice`,`Category`,`Hr1PctChange` ,`Hr24PctChange` ,`D7PctChange`,`ReocurringAlert`,`DateTimeSent`
  FROM `MarketAlertsView` WHERE `UserID` = $userID";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
    $tempAry[] = Array($row['ID'],$row['Action'],$row['Price'],$row['UserName'],$row['Email'],$row['LiveCoinPrice'],$row['Category'],$row['Hr1PctChange']
    ,$row['Hr24PctChange'],$row['D7PctChange'],$row['ReocurringAlert'],$row['DateTimeSent']);
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
  NewEcho("<Table><th>Edit</th><th>&nbspID</th><TH>&nbspCoinID</th><TH>&nbspAction</th><TH>&nbspPrice</th><TH>&nbspSymbol</th>",$_SESSION['isMobile'] ,2);
  newEcho("<TH>&nbspUserName</th><TH>&nbspEmail</th>",$_SESSION['isMobile'] ,0);
  newEcho("<TH>&nbspliveCoinPrice</th><TH>&nbspCategory</th><th>Reocurring</th><TH>&nbspDelete Alert</th><tr>",$_SESSION['isMobile'] ,2);
  $coinAlerts = getMarketAlertsUser($userID);
  $newArrLength = Count($coinAlerts);
  for($x = 0; $x < $newArrLength; $x++) {
    $id = $coinAlerts[$x][0];$coinID = $coinAlerts[$x][1]; $action = $coinAlerts[$x][2];
    $price = round($coinAlerts[$x][3],$roundNum);$symbol = $coinAlerts[$x][4]; $userName = $coinAlerts[$x][5];
    $email = $coinAlerts[$x][6];$liveCoinPrice= round($coinAlerts[$x][7],$roundNum); $category = $coinAlerts[$x][8];
    $reocurring = $coinAlerts[$x][12]; $coinAlertRuleID = $coinAlerts[$x][14];
    NewEcho("<td><a href='CoinAlerts.php?alert=1&edit=".$coinAlertRuleID."'><span class='glyphicon glyphicon-pencil' style='$fontSize;'></span></a></td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td>$id</td><td>$coinID</td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td>$action</td><td>$price</td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td>$symbol</td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td>$userName</td><td>$email</td>",$_SESSION['isMobile'] ,0);
    NewEcho("<td>$liveCoinPrice</td><td>$category</td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td>$reocurring</td>",$_SESSION['isMobile'] ,2);
    NewEcho("<td><a href='CoinAlerts.php?alert=4&iD=$coinAlertRuleID'><i class='glyphicon glyphicon-trash' style='$fontSize;color:#D4EFDF'></i></a></td>",$_SESSION['isMobile'] ,2);
 	   NewEcho("<TR>",$_SESSION['isMobile'] ,2);
 	 }
 	 Echo "</table>";
  	displaySideColumn();
	  //displayMiddleColumn();
	  //displayFarSideColumn();
	  //displayFooter();
	}
}

//include header template
require('layout/footer.php');
?>
