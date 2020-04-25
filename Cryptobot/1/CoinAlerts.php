<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
</head>
<?php require('includes/config.php');
include_once '../includes/newConfig.php';?>
<style>
<?php include 'style/style.css'; ?>
</style> <?php

//if not logged in redirect to login page
if(!$user->is_logged_in()){ header('Location: login.php'); exit(); }

//define page title
$title = 'CryptoBot';
$current_url = $_SERVER[ 'REQUEST_URI' ];
header( "Refresh: 120; URL=$current_url" );
//include header template
require('layout/header.php');
include_once ('/home/stevenj1979/SQLData.php');

if($_GET['iD'] <> ""){
  $id = $_GET['iD'];
  Echo "<BR> ID : $id";
  //header('Location: CoinAlerts.php');
  deleteSQLAlert($id);
}

if ($_GET['edit'] <> ""){
  $userID = $_SESSION['ID'];
  ?> <h1>Coin Alert</h1>
  <h2>Enter Price</h2>
  <form action='CoinAlert.php?manualAlert=Yes' method='post'>
    Coin: <input type="text" name="coinAltTxt" value="<?php echo $GLOBALS['coin']; ?>"><br>

    <select name="priceSelect">
      <option value="Price" name='priceOpt'>Price</option>
      <option value="Pct Price in 1 Hour" name='pctPriceOpt'>Pct Price in 1 Hour</option>
    </select>
    <select name="greaterThanSelect">
      <option value=">" name='greaterThanOpt'>></option>
      <option value="<" name='lessThanOpt'><</option>
    </select>
    Coin Price: <input type="text" name="coinPriceAltTxt" value="<?php echo $GLOBALS['cost']; ?>"> <br>
    <input type="checkbox" id="reocurringChk" name="reocurringChk" value="ReocurringAlert"><label for="reocurringChk"> Reocurring Alert: </label><br>
    BaseCurrency: <input type="text" name="BaseCurTxt" value="<?php echo $GLOBALS['baseCurrency']; ?>" style='color:Gray' readonly ><br>
    CoinID: <input type="text" name="CoinIDTxt" value="<?php echo $GLOBALS['coinID']; ?>" style='color:Gray' readonly ><br>
    UserID: <input type="text" name="UserIDTxt" value="<?php echo $userID; ?>" style='color:Gray' readonly ><br>
    <input type='submit' name='submit' value='Set Alert' class='settingsformsubmit' tabindex='36'>
  </form>
  <?php
}

if(isset($_POST['coinAltTxt'])){
  date_default_timezone_set('Asia/Dubai');
  $date = date("Y-m-d H:i:s", time());
  $userID = $_SESSION['ID'];
  //$coin = $_POST['coinAltTxt']; $baseCurrency = $_POST['BaseCurTxt'];
  $coinID = $_POST['CoinIDTxt']; $userID = $_POST['UserIDTxt'];
  $salePrice = $_POST['coinPriceAltTxt']; $category = $_POST['priceSelect'];
  if(isset($_POST['reocurringChk'])){ $reocurring = 1; Echo "Reocurring is set";}else{ $reocurring = 0; Echo "Reocurring is NOT set!";}
  //$reocurring = $_POST['reocurringChk'];
  //$userConfig = getUserConfig($userID);
  //$UserName = $userConfig[0][0]; $APIKey = $userConfig[0][1]; $APISecret = $userConfig[0][2]; $email = $userConfig[0][3];
  //$AvgCoinPrice = $coinStats[0][1]; $MaxCoinPrice = $coinStats[0][2]; $MinCoinPrice = $coinStats[0][3];
  //$KEK = $userConfig[0][5];
  //if (!Empty($KEK)){$APISecret = decrypt($KEK,$userConfig[0][2]);}
  //echo "<BR> KEK $KEK | APISecret $APISecret | APIKey $APIKey";
  $current_date = date('Y-m-d H:i');
  $newTime = date("Y-m-d H:i",strtotime("-30 mins", strtotime($current_date)));
  echo "<BR> ".$_POST['greaterThanSelect']." : ".$category;
  Echo "<BR> $userID, $salePrice,$category,$reocurring,$newTime)";
  if ($_POST['greaterThanSelect'] == "<"){
    AddCoinAlert($coinID,'LessThan',$userID, $salePrice,$category,$reocurring,$newTime);
  }elseif ($_POST['greaterThanSelect'] == ">"){
    AddCoinAlert($coinID,'GreaterThan',$userID, $salePrice,$category,$reocurring,$newTime);
  }
  header('Location: CoinAlerts.php');
}

function AddCoinAlert($coinID,$action,$userID, $salePrice, $category, $reocurring,$newTime){
  //
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "INSERT INTO `CoinAlerts`( `CoinID`, `Action`, `Price`, `UserID`,`Category`,`ReocurringAlert`,`DateTimeSent`) VALUES ($coinID,'$action',$salePrice,$userID,'$category',$reocurring, '$newTime')";
    print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}

function deleteSQLAlert($id){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "DELETE FROM `CoinAlerts` WHERE `ID` = $id";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}

        displayHeader(8);
        $userID = $_SESSION['ID'];
        echo "<h2>Coin Alerts!</h2><Table><th>Edit</th><th>&nbspID</th><TH>&nbspCoinID</th><TH>&nbspAction</th><TH>&nbspPrice</th><TH>&nbspSymbol</th><TH>&nbspUserName</th><TH>&nbspEmail</th><TH>&nbspliveCoinPrice</th>
        <TH>&nbspCategory</th><th>Reocurring</th><TH>&nbspDelete Alert</th><tr>";
        $coinAlerts = getCoinAlertsUser($userID);
        $newArrLength = Count($coinAlerts);
				for($x = 0; $x < $newArrLength; $x++) {
          $id = $coinAlerts[$x][0];$coinID = $coinAlerts[$x][1]; $action = $coinAlerts[$x][2];
          $price = $coinAlerts[$x][3];$symbol = $coinAlerts[$x][4]; $userName = $coinAlerts[$x][5];
          $email = $coinAlerts[$x][6];$liveCoinPrice= $coinAlerts[$x][7]; $category = $coinAlerts[$x][8];
          $reocurring = $coinAlerts[$x][12];
          echo "<td><a href='CoinAlerts.php?edit=".$id."'><span class='glyphicon glyphicon-pencil' style='font-size:22px;'></span></a></td>";
          echo "<td>$id</td><td>$coinID</td>";
          echo "<td>$action</td><td>$price</td>";
          echo "<td>$symbol</td><td>$userName</td>";
          echo "<td>$email</td><td>$liveCoinPrice</td><td>$category</td>";
          Echo "<td>$reocurring</td>";
          echo "<td><a href='CoinAlerts.php?iD=$id'><i class='glyphicon glyphicon-trash' style='font-size:20px;color:#D4EFDF'></i></a></td>";
          echo "<TR>";
        }
        Echo "</table>";
        displaySideColumn();
        //displayMiddleColumn();
				//displayFarSideColumn();
        //displayFooter();

//include header template
require('layout/footer.php');
$date = date('Y/m/d H:i:s', time());
echo " Last Updated :".$date;
?>
