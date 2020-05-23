<html>
<head>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
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


main();

function getHeaders(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT DISTINCT(`Subject`) as `Subject`  FROM `ActionLog` ";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Subject']);
  }
  $conn->close();
  return $tempAry;
}

function getConsoleData(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `DateTime`,`Subject`,`Comment` FROM `ActionLogView` WHERE `UserID` = 3";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['DateTime']."| ".$row['Subject'].": ".$row['Comment']);
  }
  $conn->close();
  return $tempAry;
}

function displayDropDown($headers){
  $headerCount = count($headers);
  echo $headerCount;
  var_dump($headers);
  echo "<form action='console.php?dropdown=Yes' method='post'>";
  for ($i=0; $i<$headerCount; $i++){
    $nText = $headers[$i][0];
    Echo "<option value='$nText'>$nText</option>";
  }
  echo "<input type='submit' name='submit' value='Update' class='settingsformsubmit' tabindex='36'></form>";
}

function main(){
  displayHeader(9);
  $headers = getHeaders();
  $consoleData = getConsoleData();
  $dataCount = count($consoleData);
  print_r("<h2>Console</h2>");
    displayDropDown($headers);
    echo "<textarea class='FormElement' name='term' id='term' style='width: 100%; height: 90%;'>";
    for ($i=0; $i<$dataCount; $i++){
        echo $consoleData[$i][0]."\n";
    }
    echo "</textarea>";

  displaySideColumn();
}





      //displayMiddleColumn();
      //displayFarSideColumn();
      //displayFooter();

//include header template
require($_SERVER['DOCUMENT_ROOT'].'/Investment-Tracker/Cryptobot/1/layout/footer.php');
$date = date('Y/m/d H:i:s', time());
echo " Last Updated :".$date;
?>
