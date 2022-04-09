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

if (!empty($_POST['submit'])){
  changeSetting($_POST['transSelect'],$_POST['transSubSelect']);
  main();
}else{
  main();
}

function changeSetting($change,$subChange){
  $_SESSION['ConsoleSelected'] = $change;
  $_SESSION['ConsoleSubSelected'] = $subChange;
}

function getHeaders(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT DISTINCT(`Subject`) as `Subject`  FROM `ActionLog`  order by `Subject` ";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['Subject']);
  }
  $conn->close();
  return $tempAry;
}

function getsubHeaders(){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT DISTINCT(`SubTitle`) as `SubTitle` FROM `ActionLog` order by `SubTitle` ";
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['SubTitle']);
  }
  $conn->close();
  return $tempAry;
}

function getConsoleData($console, $userID, $consolsub){
  if ($console == 1){$sql_option = $console;} else {$sql_option = "`Subject` = '$console'";}
  if ($consolsub == 'ALL'){$sql_option2 = $consolsub;} else {$sql_option2 = "`SubTitle` = '$consolsub'";}
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `DateTime`,`Subject`,`Comment`, TimeStampDiff(MINUTE, now(),`DateTime`) As MinsSinceLog, `SubTitle`, `Reference` FROM `ActionLogView` WHERE `UserID` = $userID and $sql_option and $sql_option2 Limit 100";
  //echo $sql;
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['DateTime']."| ".$row['Subject'].": ".$row['Comment'].": ".$row['MinsSinceLog'].": ".$row['SubTitle'].": ".$row['Reference']);
  }
  $conn->close();
  return $tempAry;
}

function displayDropDown($headers,$selected){
  $headerCount = count($headers);
  //echo $headerCount;
  //var_dump($headers);

  for ($i=0; $i<$headerCount; $i++){
    $nText = $headers[$i][0];
    if ($selected == $nText){Echo "<option  selected='selected' value='$nText'>$nText</option>";}else{Echo "<option value='$nText'>$nText</option>";}
  }
  if ($selected == 1){Echo "<option  selected='selected' value='1'>ALL</option>";}else{Echo "<option value='1'>ALL</option>";}

}

function main(){
  displayHeader(9);
  $headers = getHeaders();
  $subHeaders = getsubHeaders();
  $consoleData = getConsoleData($_SESSION['ConsoleSelected'], $_SESSION['ID'],$_SESSION['ConsoleSubSelected']);
  $dataCount = count($consoleData);
  print_r("<h2>Console</h2>");
  echo "<form action='console.php?dropdown=Yes' method='post'>";
  echo "<select name='transSelect' id='transSelect' class='enableTextBox'>";
    displayDropDown($headers, $_SESSION['ConsoleSelected']);
    echo "</select>";

  echo "<select name='transSubSelect' id='transSelect' class='enableTextBox'>";
      displayDropDown($subHeaders, $_SESSION['ConsoleSubSelected']);
      echo "</select>";
      echo "<input type='submit' name='submit' value='Update' class='settingsformsubmit' tabindex='36'></form>";
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
