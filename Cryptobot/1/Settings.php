[<html>
<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<?php require('includes/config.php');
  //include 'includes/functions.php';
  include_once ('../../../../SQLData.php');
  include_once ('/home/stevenj1979/Encrypt.php');
  include_once '../includes/newConfig.php';
?>
<html>
<style>
<?php include 'style/style.css'; ?>
</style>
<body>
<?php


//if not logged in redirect to login page
if(!$user->is_logged_in()){ header('Location: login.php'); exit(); }

if(isset($_POST['submit'])){

  if (!empty($_GET['user'])){
    $userID = $_SESSION['ID']; $userName = $_POST['newusername']; $email = $_POST['email']; $APIKey = $_POST['API_Key']; $APISecret = $_POST['API_Secret'];
    $dailyBTCLimit = $_POST['dailyBTCLimit']; $totalBTCLimit = $_POST['totalBTCLimit']; $enableDailyBTCLimit = $_POST['enableDailyBTCLimit']; $enableTotalBTCLimit = $_POST['enableTotalBTCLimit'];
    $btcBuyAmount = $_POST['BTCBuyAmount']; $baseCurrency = $_POST['userBaseCurrency']; $enableLowPurchasePrice = $_POST['lowPricePurchaseEnabled'];
    $noOfPurchases = $_POST['NoOfPurchases']; $pctToPurchase = $_POST['PctToPurchase']; $totalRisesInPrice = $_POST['TotalRisesInPrice'];$totalRisesInPriceSell = $_POST['TotalRisesInPriceSell'];
    $noOfCoinPurchase = $_POST['NoOfCoinPurchase'];
    $hoursFlatTolerance = $_POST['hoursFlatTol'];$lowMarketModeEnabled = $_POST['enableLowMarketMode'];$minsToPauseAfterPurchase = $_POST['minsPauseAfterPurchase'];$saveResidualCoins = $_POST['saveResidual'];
    $reduceLossEnabled = $_POST['enableReduceLoss'];$redirectPurchasesToSpread = $_POST['enableRedirectToSB'];$redirectPurchasesToSpreadID = $_POST['redirectSBID'];$buyBackEnabled = $_POST['enableBuyBack'];
    $allBuyBackAsOverride = $_POST['enableAllBBasOverride'];$sellSavingsEnabled = $_POST['enableSellSavings'];$rebuySavingsEnabled = $_POST['enableReBuySaving'];$autoMergeSavings = $_POST['enableAutoMerge'];$mergeSavingWithPurchase = $_POST['enableMergeWithPurchase'];
    $usdtAlloc = $_POST['usdtAllocTxt']; $btcAlloc = $_POST['btcAllocTxt']; $ethAlloc = $_POST['ethAllocTxt']; $pctOnLow = $_POST['pctOnLowTxt'];
    $lowMarketModeStartPct = $_POST['LowMarketModeStartPct'];$lowMarketModeIncrements = $_POST['LowMarketModeIncrements'];
    $sellPct = $_POST['SellPct']; $originalPriceMultiplier = $_POST['OriginalPriceMultiplier']; $reduceLossMaxCounter = $_POST['ReduceLossMaxCounter'];
    $saveMode = $_POST['SaveMode']; $pctToSave = $_POST['PctToSave'];
    $pauseCoinIDAfterPurchaseEnabled = $_POST['PauseCoinIDAfterPurchaseEnabled'];
    $daysToPauseCoinIDAfterPurchase = $_POST['DaysToPauseCoinIDAfterPurchase'];
    $bbHoursFlat = $_POST['buyBackHoursFlat'];
    $reduceLossHoursFlat = $_POST['ReduceLossHoursFlat'];
    $enableSavePctofTotal = $_POST[' EnableSavePctofTotal'];
    $savingPctOfTotal = $_POST['SavingPctOfTotal'];
    $reduceLossMinsCancel = $_POST['ReduceLossMinsToCancel'];
    $buyBackAutoPct = $_POST['buyBackAutoPct'];
    $reduceLossAutoPct = $_POST['ReduceLossAutoPct'];
    $buyBackMinsToCancel = $_POST['buyBackMinsToCancel'];
    $buyBackCounter = $_POST['buyBackCounter'];
    $enableBBAutoPct = $_POST['enableBBAutoPct'];
    if(empty($_POST['BTCBuyAmount'])){$btcBuyAmount = 0;}
    if(empty($_POST['dailyBTCLimit'])){$dailyBTCLimit = 0;}
    if(empty($_POST['enableDailyBTCLimit'])){$enableDailyBTCLimit = 0;}
    if(empty($_POST['totalBTCLimit'])){$totalBTCLimit = 0;}
    if(empty($_POST['enableTotalBTCLimit'])){$enableTotalBTCLimit = 0;}
    if(empty($_POST['lowPricePurchaseEnabled'])){$enableLowPurchasePrice = 0;}
    if(empty($_POST['NoOfPurchases'])){$noOfPurchases = 0;}
    if(empty($_POST['PctToPurchase'])){$pctToPurchase = 0;}
    if(empty($_POST['TotalRisesInPrice'])){$totalRisesInPrice = 0;}
    if(empty($_POST['TotalRisesInPriceSell'])){$totalRisesInPriceSell = 0;}
    if(empty($_POST['NoOfCoinPurchase'])){$noOfCoinPurchase = 0;}
    if(empty($_POST['minsPauseAfterPurchase'])){$minsToPauseAfterPurchase = 0;}
    if(empty($_POST['hoursFlatTol'])){$hoursFlatTolerance = 0;}
    if(empty($_POST['redirectSBID'])){$redirectPurchasesToSpreadID = 0;}
    if(empty($_POST['SavingPctOfTotal'])){$savingPctOfTotal = 0;}
    if(empty($_POST['buyBackAutoPct'])){$buyBackAutoPct = 0;}
    if(empty($_POST['ReduceLossAutoPct'])){ $reduceLossAutoPct = 0;}
    if(empty($_POST['ReduceLossMinsToCancel'])){ $reduceLossMinsCancel = 0;}
    if(empty($_POST['buyBackMinsToCancel'])){ $buyBackMinsToCancel = 0;}
    if(empty($_POST['buyBackCounter'])){ $buyBackCounter = 0;}
    $spreadBetSellIndPct = $_POST['SpreadBetSellIndPct'];
    $enableSpreadBetSellInd = $_POST['EnableSpreadBetSellInd'];
    //if($lowMarketModeEnabled == "Yes"){ $setLowMarket = -1;} else {$setLowMarket = 0;}
    if ($saveMode > 0 ){ $saveResidualCoins = 'No';}
    $holdCoinForBuyOut = $_POST['HoldCoinForBuyOut'];
    if(empty($_POST['HoldCoinForBuyOut'])){$holdCoinForBuyOut = 0;}
    $coinForBuyOutPct = $_POST['CoinForBuyOutPct'];
    if(empty($_POST['CoinForBuyOutPct'])){$coinForBuyOutPct = 0;}
    $pctAuto = $_POST['pctAuto'];
    $enableBBAutoHoursFlat = $_POST['enableBBAutoHoursFlat'];

    //echo "Here1! $lowMarketModeEnabled _ $setLowMarket";
    $settingsUpdateAry = Array($userID,$userName,$email,$APIKey,$APISecret,$dailyBTCLimit,$totalBTCLimit,$enableDailyBTCLimit,$enableTotalBTCLimit,$btcBuyAmount,$baseCurrency,$enableLowPurchasePrice,$noOfPurchases,$pctToPurchase,$totalRisesInPrice,$totalRisesInPriceSell,$noOfCoinPurchase,  //16
    $hoursFlatTolerance,$lowMarketModeEnabled,$minsToPauseAfterPurchase,$saveResidualCoins,$reduceLossEnabled,$redirectPurchasesToSpread,$redirectPurchasesToSpreadID,$buyBackEnabled,$allBuyBackAsOverride,$sellSavingsEnabled,$rebuySavingsEnabled,$autoMergeSavings,$mergeSavingWithPurchase,   //29
    $usdtAlloc,$btcAlloc,$ethAlloc,$pctOnLow,$lowMarketModeStartPct,$lowMarketModeIncrements,$saveMode,$pctToSave,$sellPct,$originalPriceMultiplier,$reduceLossMaxCounter,$pauseCoinIDAfterPurchaseEnabled,$daysToPauseCoinIDAfterPurchase,$bbHoursFlat,$reduceLossHoursFlat,$holdCoinForBuyOut,   //45
    $coinForBuyOutPct,$enableSavePctofTotal,$savingPctOfTotal,$pctAuto,$enableBBAutoHoursFlat,$buyBackAutoPct,$reduceLossAutoPct,$reduceLossMinsCancel,$buyBackMinsToCancel,$buyBackCounter,$enableBBAutoPct,$spreadBetSellIndPct,$enableSpreadBetSellInd);
    updateUser($settingsUpdateAry);
    //echo "Here2! $userID,$userName,$email,$APIKey,$APISecret,$dailyBTCLimit,$totalBTCLimit,$enableDailyBTCLimit,$enableTotalBTCLimit,$btcBuyAmount,$baseCurrency,$enableLowPurchasePrice,$noOfPurchases,$pctToPurchase,$totalRisesInPrice,$totalRisesInPriceSell,$noOfCoinPurchase,
    //$hoursFlatTolerance,$lowMarketModeEnabled,$minsToPauseAfterPurchase,$saveResidualCoins,$reduceLossEnabled,$redirectPurchasesToSpread,$redirectPurchasesToSpreadID,$buyBackEnabled,$allBuyBackAsOverride,$sellSavingsEnabled,$rebuySavingsEnabled,$autoMergeSavings,$mergeSavingWithPurchase,
    //$usdtAlloc,$btcAlloc,$ethAlloc,$pctOnLow,$lowMarketModeStartPct,$lowMarketModeIncrements);";

    //header('Location: Settings.php');
  }


}//end if submit
//define page title
$title = 'CryptoBot';

//include header template
require('layout/header.php');


function getUserIDs($userID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `IDUs`,`AccountType`,`UserName`,`Active`,`APIKey`,`APISecret`,`EnableDailyBTCLimit`,`EnableTotalBTCLimit`,`DailyBTCLimit`,`TotalBTCLimit`,`Email`,`BTCBuyAmount`,`BaseCurrency`,`KEK`
  ,`LowPricePurchaseEnabled`,`NoOfPurchases`,`PctToPurchase`,`TotalRisesInPrice`,`TotalRisesInPriceSell`,`NoOfCoinPurchase`,`ReduceLossEnabled`,`RebuySavingsEnabled`,`SellSavingsEnabled`,`BuyBackEnabled`
  ,`SaveResidualCoins`,`RedirectPurchasesToSpreadID`,`RedirectPurchasesToSpread`,`MinsToPauseAfterPurchase`,`LowMarketModeEnabled`,`AllBuyBackAsOverride`,`HoursFlatTolerance`,`MergeSavingWithPurchase`
  ,`AutoMergeSavings`,`USDTAlloc`,`BTCAlloc`,`ETHAlloc`,`PctOnLow`,`LowMarketModeStartPct`,`LowMarketModeIncrements`,`SaveMode`,`PctToSave`,`SellPct`,`OriginalPriceMultiplier`,`ReduceLossMaxCounter`
  , `PauseCoinIDAfterPurchaseEnabled`, `DaysToPauseCoinIDAfterPurchase`,`BuyBackHoursFlatTarget`,`HoursFlatRls`,`HoldCoinForBuyOut`,`CoinForBuyOutPct`,`SavingPctOfTotalEnabled`,`SavingPctOfTotal`
  ,`PctOfAuto`,`BuyBackHoursFlatAutoEnabled`,`PctOfAutoBuyBack`,`PctOfAutoReduceLoss`,`ReduceLossMinsToCancel`,`BuyBackMinsToCancel`,`BuyBackMax`,`BuyBackAutoPct`, `SpreadBetSellIndEnabled`, `SpreadBetPctToSellInd`
  FROM `View4_BittrexBuySell` WHERE `IDUs` = $userID";
	//echo $sql;
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['IDUs'],$row['AccountType'],$row['UserName'],$row['Active'],$row['APIKey'],$row['APISecret'],$row['EnableDailyBTCLimit'],$row['EnableTotalBTCLimit'] //7
      ,$row['DailyBTCLimit'],$row['TotalBTCLimit'],$row['Email'],$row['BTCBuyAmount'],$row['BaseCurrency'],$row['KEK'],$row['LowPricePurchaseEnabled'],$row['NoOfPurchases'],$row['PctToPurchase'] //16
      ,$row['TotalRisesInPrice'],$row['TotalRisesInPriceSell'],$row['NoOfCoinPurchase'],$row['ReduceLossEnabled'],$row['RebuySavingsEnabled'],$row['SellSavingsEnabled'],$row['BuyBackEnabled'] //23
      ,$row['SaveResidualCoins'],$row['RedirectPurchasesToSpreadID'],$row['RedirectPurchasesToSpread'],$row['MinsToPauseAfterPurchase'],$row['LowMarketModeEnabled'],$row['AllBuyBackAsOverride'] //29
      ,$row['HoursFlatTolerance'],$row['MergeSavingWithPurchase'],$row['AutoMergeSavings'],$row['USDTAlloc'],$row['BTCAlloc'],$row['ETHAlloc'],$row['PctOnLow'],$row['LowMarketModeStartPct'],$row['LowMarketModeIncrements'] //38
      ,$row['SaveMode'],$row['PctToSave'],$row['SellPct'],$row['OriginalPriceMultiplier'],$row['ReduceLossMaxCounter'],$row['PauseCoinIDAfterPurchaseEnabled'],$row['DaysToPauseCoinIDAfterPurchase'] //45
      ,$row['BuyBackHoursFlatTarget'],$row['HoursFlatRls'],$row['HoldCoinForBuyOut'],$row['CoinForBuyOutPct'],$row['SavingPctOfTotalEnabled'],$row['SavingPctOfTotal'],$row['PctOfAuto'] //52
      ,$row['BuyBackHoursFlatAutoEnabled'],$row['PctOfAutoBuyBack'],$row['PctOfAutoReduceLoss'],$row['ReduceLossMinsToCancel'],$row['BuyBackMinsToCancel'],$row['BuyBackMax'],$row['BuyBackAutoPct']//59
      ,$row['SpreadBetSellIndEnabled'],$row['SpreadBetPctToSellInd']); //61
  }
  $conn->close();
  return $tempAry;
}


function updateUser($settingsUpdateAry){
  //var_dump($settingsUpdateAry);
  $userID = $settingsUpdateAry[0]; $newusername = $settingsUpdateAry[1]; $email = $settingsUpdateAry[2]; $apiKey = $settingsUpdateAry[3]; $apisecret = $settingsUpdateAry[4];
  $dailyBTCLimit = $settingsUpdateAry[5]; $totalBTCLimit = $settingsUpdateAry[6];$enableDailyBTCLimit = $settingsUpdateAry[7]; $enableTotalBTCLimit = $settingsUpdateAry[8];
  $BTCBuyAmount = $settingsUpdateAry[9]; $userBaseCurrency = $settingsUpdateAry[10]; $lowPricePurchaseEnabled = $settingsUpdateAry[11]; $noOfPurchases = $settingsUpdateAry[12];
  $pctToPurchase = $settingsUpdateAry[13];$totalRisesInPrice = $settingsUpdateAry[14];$totalRisesInPriceSell = $settingsUpdateAry[15];$noOfCoinPurchase = $settingsUpdateAry[16];
  $hoursFlatTolerance = $settingsUpdateAry[17];$lowMarketModeEnabled = $settingsUpdateAry[18];$minsToPauseAfterPurchase = $settingsUpdateAry[19];$saveResidualCoins = $settingsUpdateAry[20];
  $reduceLossEnabled = $settingsUpdateAry[21];$redirectPurchasesToSpread = $settingsUpdateAry[22];$redirectPurchasesToSpreadID = $settingsUpdateAry[23];$buyBackEnabled = $settingsUpdateAry[24];
  $allBuyBackAsOverride = $settingsUpdateAry[25];$sellSavingsEnabled = $settingsUpdateAry[26];$rebuySavingsEnabled = $settingsUpdateAry[27];$autoMergeSavings = $settingsUpdateAry[28];
  $mergeSavingWithPurchase = $settingsUpdateAry[29]; $saveMode = $settingsUpdateAry[36];  $pctToSave = $settingsUpdateAry[37];
  $sellPct = $settingsUpdateAry[38]; $originalPriceMultiplier = $settingsUpdateAry[39]; $reduceLossMaxCounter = $settingsUpdateAry[40];
  $usdtAlloc = $settingsUpdateAry[30]; $btcAlloc = $settingsUpdateAry[31]; $ethAlloc = $settingsUpdateAry[32]; $pctOnLow = $settingsUpdateAry[33];
  $pauseCoinIDAfterPurchaseEnabled  = $settingsUpdateAry[41];
  $daysToPauseCoinIDAfterPurchase = $settingsUpdateAry[42];
  $bbHoursFlat = $settingsUpdateAry[43]; $holdCoinForBuyOut = $settingsUpdateAry[45]; $coinForBuyOutPct = $settingsUpdateAry[46];
  $enableSavePctofTotal = $settingsUpdateAry[47]; $savingPctOfTotal = $settingsUpdateAry[48];
  $lowMarketModeStartPct = $settingsUpdateAry[34]; $lowMarketModeIncrements = $settingsUpdateAry[35];
  $enableBBAutoHoursFlat = $settingsUpdateAry[50];
  $buyBackAutoPct = $settingsUpdateAry[51];
  $reduceLossAutoPct = $settingsUpdateAry[52];
  $reduceLossMinsCancel = $settingsUpdateAry[53];
  $buyBackMinsToCancel = $settingsUpdateAry[54];
  $buyBackCounter = $settingsUpdateAry[55];
  $enableBBAutoPct = $settingsUpdateAry[56];
  $spreadBetSellIndPct = $settingsUpdateAry[57];
  $enableSpreadBetSellInd = $settingsUpdateAry[58];
  if ($enableDailyBTCLimit == "Yes"){$enableDailyBTCLimitNum = 1;}else{$enableDailyBTCLimitNum = 0;}
  if ($enableTotalBTCLimit == "Yes"){$enableTotalBTCLimitNum = 1;}else{$enableTotalBTCLimitNum = 0;}
  if ($lowPricePurchaseEnabled == "Yes"){$lowPricePurchaseEnabled = 1;}else{$lowPricePurchaseEnabled = 0;}
  if ($lowMarketModeEnabled == "Yes"){$lowMarketModeEnabled = -1;}else{ $lowMarketModeEnabled = 0;}
  if ($saveResidualCoins == "Yes"){$saveResidualCoins = 1;}else{$saveResidualCoins = 0;}
  if ($reduceLossEnabled == "Yes"){$reduceLossEnabled = 1;}else{$reduceLossEnabled = 0;}
  if ($redirectPurchasesToSpread == "Yes"){$redirectPurchasesToSpread = 1;}else{$redirectPurchasesToSpread = 0;}
  if ($buyBackEnabled == "Yes"){$buyBackEnabled = 1;}else{$buyBackEnabled = 0;}
  if ($allBuyBackAsOverride == "Yes"){$allBuyBackAsOverride = 1;}else{$allBuyBackAsOverride = 0;}
  if ($sellSavingsEnabled == "Yes"){$sellSavingsEnabled = 1;}else{$sellSavingsEnabled = 0;}
  if ($rebuySavingsEnabled == "Yes"){$rebuySavingsEnabled = 1;}else{$rebuySavingsEnabled = 0;}
  if ($autoMergeSavings == "Yes"){$autoMergeSavings = 1;}else{$autoMergeSavings = 0;}
  if ($mergeSavingWithPurchase == "Yes"){$mergeSavingWithPurchase = 1;}else{$mergeSavingWithPurchase = 0;}
  if ($pauseCoinIDAfterPurchaseEnabled == "Yes"){$pauseCoinIDAfterPurchaseEnabled = 1;}else{$pauseCoinIDAfterPurchaseEnabled = 0;}
  if ($holdCoinForBuyOut == "Yes"){$holdCoinForBuyOut = 1;}else{$holdCoinForBuyOut = 0;}
  if ($enableSavePctofTotal == "Yes"){$enableSavePctofTotal = 1;}else{$enableSavePctofTotal = 0;}
  if ($enableBBAutoHoursFlat == "Yes"){$enableBBAutoHoursFlat = 1;}else{$enableBBAutoHoursFlat = 0;}
  if ($enableBBAutoPct == "Yes"){$enableBBAutoPct = 1;}else{$enableBBAutoPct = 0;}
  if ($enableSpreadBetSellInd == "Yes"){$enableSpreadBetSellInd = 1;}else{$enableSpreadBetSellInd = 0;}
  $reduceLossHoursFlat = $settingsUpdateAry[44];
  $pctAuto = $settingsUpdateAry[49];
  //echo "<BR> Email $email ".$settingsUpdateAry[2]." APIKey $apiKey ".$settingsUpdateAry[3]."<br>";
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
    echo "Error";
      die("Connection failed: " . $conn->connect_error);
  }
  $encAry = Encrypt($apisecret);
  $enc_apiSecret = $encAry['data'];
  $enc_KEK = $encAry['secret'];
  $sql = "UPDATE `UserConfig` SET `APIKey`='$apiKey', `APISecret`='$enc_apiSecret',`EnableDailyBTCLimit`=$enableDailyBTCLimitNum
         ,`EnableTotalBTCLimit`=$enableTotalBTCLimitNum,`DailyBTCLimit`=$dailyBTCLimit,`TotalBTCLimit`=$totalBTCLimit,`BTCBuyAmount`=$BTCBuyAmount, `BaseCurrency`='$userBaseCurrency',`KEK`='$enc_KEK'
         ,`LowPricePurchaseEnabled` = $lowPricePurchaseEnabled, `NoOfPurchases` = $noOfPurchases,`PctToPurchase` = $pctToPurchase,`TotalRisesInPrice` = $totalRisesInPrice, `TotalRisesInPriceSell` = $totalRisesInPriceSell
         ,`NoOfCoinPurchase` = $noOfCoinPurchase,`HoursFlatTolerance`=$hoursFlatTolerance,`LowMarketModeEnabled`=$lowMarketModeEnabled,`MinsToPauseAfterPurchase`=$minsToPauseAfterPurchase,`SaveResidualCoins`=$saveResidualCoins
         ,`RedirectPurchasesToSpread`=$redirectPurchasesToSpread,`RedirectPurchasesToSpreadID`=$redirectPurchasesToSpreadID,`BuyBackEnabled`=$buyBackEnabled,`AllBuyBackAsOverride`=$allBuyBackAsOverride,`SellSavingsEnabled`=$sellSavingsEnabled
         ,`RebuySavingsEnabled`=$rebuySavingsEnabled,`AutoMergeSavings`=$autoMergeSavings,`MergeSavingWithPurchase`=$mergeSavingWithPurchase, `LowMarketModeStartPct` = $lowMarketModeStartPct, `LowMarketModeIncrements` = $lowMarketModeIncrements
         ,`SaveMode` = $saveMode, `PctToSave` = $pctToSave, `PauseCoinIDAfterPurchaseEnabled` = $pauseCoinIDAfterPurchaseEnabled, `DaysToPauseCoinIDAfterPurchase` = $daysToPauseCoinIDAfterPurchase,`BuyBackHoursFlatTarget` = $bbHoursFlat,
        `HoldCoinForBuyOut` = $holdCoinForBuyOut, `CoinForBuyOutPct` = $coinForBuyOutPct,`SavingPctOfTotalEnabled` = $enableSavePctofTotal,`SavingPctOfTotal` = $savingPctOfTotal, `PctOfAuto` = $pctAuto, `BuyBackHoursFlatAutoEnabled` = $enableBBAutoHoursFlat
        ,`PctOfAutoBuyBack` = $buyBackAutoPct, `PctOfAutoReduceLoss` = $reduceLossAutoPct, `BuyBackMinsToCancel` = $buyBackMinsToCancel,`BuyBackMax`= $buyBackCounter, `BuyBackAutoPct` = $enableBBAutoPct, `SpreadBetSellIndEnabled` = $enableSpreadBetSellInd
        , `SpreadBetPctToSellInd` = $spreadBetSellIndPct
         WHERE `UserID` = $userID;
         UPDATE `User` SET `UserName`='$newusername',`Email`='$email' WHERE `ID` = $userID;
         UPDATE `ReduceLossSettings` SET `Enabled`= $reduceLossEnabled, `SellPct` = $sellPct, `OriginalPriceMultiplier` = $originalPriceMultiplier, `ReduceLossMaxCounter` = $reduceLossMaxCounter, `HoursFlat` = $reduceLossHoursFlat
         , `ReduceLossMinsToCancel` = $reduceLossMinsCancel WHERE `UserID` = $userID;
         UPDATE `NewCoinAllocations` SET `USDTAlloc` = $usdtAlloc,`BTCAlloc` = $btcAlloc, `ETHAlloc` = $ethAlloc, `PctOnLow` = $pctOnLow WHERE `UserID` = $userID";
  //print_r("<br>".$sql."<br>");
  if ($conn->multi_query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function getSequence($userID){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT `SellRuleID`,`Sequence` FROM `SellSequence` WHERE `UserID` = $userID";
	//echo $sql;
  $result = $conn->query($sql);
  //$result = mysqli_query($link4, $query);
  //mysqli_fetch_assoc($result);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['SellRuleID'],$row['Sequence']);
  }
  $conn->close();
  return $tempAry;
}

function displayYesNoAuto($selection,$name,$title){
  if ($selection == 1){
    $option1 = "Yes"; $option2 = "No";$option3 = "Auto";
  }elseif ($selection == 2){
    $option1 = "Auto"; $option2 = "Yes";$option3 = "No";
  }else{
    $option1 = "No"; $option2 = "Yes";$option3 = "Auto";
  }
  echo "<b>$title: </b>";
  echo "<select name='$name' id='$name' class='enableTextBox'>";
  echo "<option value='".$option1."'>".$option1."</option>
    <option value='".$option2."'>".$option2."</option>
    <option value='".$option3."'>".$option3."</option></select><br>";
}

function displayText($name, $text,$value,$tab, $comment){
  echo "<label for='$name'>$text</label><br>";
  echo "<input type='text' name='$name' id='$name' class='form-control input-lg' placeholder='$text' value= $value tabindex='$tab'>";
  echo "<p class='comments'>$comment</p>";
}

function displayMainSectionStart(){
?>

  <div class="settingsformMain">
<?php
}

function displayMainSectionEnd(){
  ?>
  </div>
  <?php
}

function displaySubSectionStart($name){
?>
<h3><b><u><?php echo $name; ?>: </h3></b></u></BR>
<div class="settingsform">
<?php
}

function displaySubSectionEnd(){
  ?>
  </div>
  <?php
}


$userDetails = getUserIDs($_SESSION['ID']);
//$userSettings = getConfig($_SESSION['ID']);
  $sequence = getSequence($_SESSION['ID']);
  $sequenceCount = count($sequence);
  displayHeader(7);
  $kek = $userDetails[0][13];
  $apisecret =Decrypt($kek,$userDetails[0][5]);
?>
  </div>
      <div class="row">
            <div class="settingCol1">
                <!--<h3>User Settings</h3>-->
                <?php displaySubHeader("Settings"); ?>

              <form action="Settings.php?user=Yes" method="post">

                <?php
                displayMainSectionStart();
                  displaySubSectionStart("Main");
                    displayText("newusername", "UserName: ",$userDetails[0][2],1,"");
                    displayText("email", "Email: ",$userDetails[0][10],2,"");
                    displayText("API_Key", "API Key: ",$userDetails[0][4],3,"Bittrex API Key");
                    displayText("API_Secret", "API Secret: ",$apisecret,4,"Bittrex Secret API Key");
                    displayText("BTCBuyAmount", "BTC Buy Amount: ",$userDetails[0][11],5,"Amount in BTC for each buy");
                    displayYesNoAuto($userDetails[0][6],'enableDailyBTCLimit',"Enable Daily BTC Limit");
                    displayText("dailyBTCLimit", "Daily BTC Limit: ",$userDetails[0][8],6,"");
                    displayYesNoAuto($userDetails[0][7],'enableTotalBTCLimit',"Enable Total BTC Limit");
                    displayText("totalBTCLimit", "Total BTC Limit: ",$userDetails[0][9],7,"");

                    if ($userDetails[0][12] == 'BTC'){ $option1 = "BTC"; $option2 = "USDT";$option3 = "ETH";$option4 = "All";}
                    elseif ($userDetails[0][12] == 'USDT'){$option1 = "USDT"; $option2 = "BTC";$option3 = "ETH";$option4 = "All";}
                    elseif ($userDetails[0][12] == 'ETH'){$option1 = "ETH"; $option2 = "BTC";$option3 = "USDT";$option4 = "All";}
                    elseif ($userDetails[0][12] == 'All'){$option1 = "All"; $option2 = "BTC";$option3 = "ETH";$option4 = "USDT";}?>
                      <div class='settingsform'>
                        <b>User Base Currency: </b> <select name='userBaseCurrency' id='userBaseCurrency' class='enableTextBox'><?php
                          echo "<option value='".$option1."'>".$option1."</option>
                          <option value='".$option2."'>".$option2."</option>
                          <option value='".$option3."'>".$option3."</option>
                          <option value='".$option4."'>".$option4."</option></select></div><BR>";


                    displayYesNoAuto($userDetails[0][14],'lowPricePurchaseEnabled',"Low Price Purchase Enabled");
                    displayText("NoOfPurchases", "Number of Purchases: ",$userDetails[0][15],8,"");
                    displayText("PctToPurchase", "% to Purchase: ",$userDetails[0][16],9,"");
                    displayText("TotalRisesInPrice", "Total Rises In Price: ",$userDetails[0][17],10,"");
                    displayText("TotalRisesInPriceSell", "Total Rises In Price Sell: ",$userDetails[0][18],11,"");
                    displayText("NoOfCoinPurchase", "No of Coin Purchase: ",$userDetails[0][19],12,"");
                  displaySubSectionEnd();

                  displaySubSectionStart("Buy Admin");
                    displayText("hoursFlatTol", "HoursFlatTolerance: ",$userDetails[0][30],13,"");
                    displayText("pctAuto", "Auto Buy: ",$userDetails[0][52],14,"");
                    displayText("minsPauseAfterPurchase", "Mins To Pause After Purchase: ",$userDetails[0][27],15,"");
                    displayYesNoAuto($userDetails[0][44],'PauseCoinIDAfterPurchaseEnabled',"Pause CoinID After Purchase Enabled");
                    displayText("DaysToPauseCoinIDAfterPurchase", "Days To Pause CoinID After Purchase: ",$userDetails[0][45],16,"");
                    displayYesNoAuto($userDetails[0][24],'saveResidual',"Enable Save Residual");
                    displayYesNoAuto($userDetails[0][20],'enableReduceLoss',"Enable Reduce Loss");
                    displayText("SellPct", "Reduce Loss Sell %: ",$userDetails[0][41],17,"");
                  displaySubSectionEnd();

                  displaySubSectionStart("Reduce Loss");
                    displayText("OriginalPriceMultiplier", "Reduce Loss Original Price Multiplier: ",$userDetails[0][42],18,"");
                    displayText("ReduceLossMaxCounter", "Reduce Loss Max Counter: ",$userDetails[0][43],19,"");
                    displayText("ReduceLossHoursFlat", "Reduce Loss Hours Flat: ",$userDetails[0][47],20,"");
                    displayYesNoAuto($userDetails[0][48],'HoldCoinForBuyOut',"Hold Coin for buyout Enabled");
                    displayText("CoinForBuyOutPct", "Hold Coin buyout Pct: ",$userDetails[0][49],21,"");
                    displayText("ReduceLossAutoPct", "Auto Pct: ",$userDetails[0][55],22,"");
                    displayText("ReduceLossMinsToCancel", "Mins To Cancel: ",$userDetails[0][56],22,"");
                  displaySubSectionEnd();

                  displaySubSectionStart("Redirect");
                    displayYesNoAuto($userDetails[0][26],'enableRedirectToSB',"Redirect All Purchases To SpreadBet");
                    displayText("redirectSBID", "Redirect SpreadBet ID: ",$userDetails[0][25],23,"");
                  displaySubSectionEnd();

                  displaySubSectionStart("Buyback");
                    displayYesNoAuto($userDetails[0][23],'enableBuyBack',"Enable BuyBack");
                    displayYesNoAuto($userDetails[0][29],'enableAllBBasOverride',"Enable All BuyBack as Override");
                    displayText("buyBackHoursFlat", "Hours Flat: ",$userDetails[0][46],24,"");
                    displayYesNoAuto($userDetails[0][53],'enableBBAutoHoursFlat',"Enable BuyBack Auto Hours Flat");
                    displayText("buyBackAutoPct", "Auto Pct: ",$userDetails[0][54],25,"");
                    displayText("buyBackMinsToCancel", "Mins to Cancel: ",$userDetails[0][57],26,"");
                    displayText("buyBackCounter", "Max BuyBack Count: ",$userDetails[0][58],27,"");
                    displayYesNoAuto($userDetails[0][59],'enableBBAutoPct',"Enable BuyBack Auto Pct");
                  displaySubSectionEnd();

                  displaySubSectionStart("Savings");
                    displayYesNoAuto($userDetails[0][22],'enableSellSavings',"Enable Sell Savings");
                    displayYesNoAuto($userDetails[0][21],'enableReBuySaving',"Enable ReBuy Savings");
                    displayYesNoAuto($userDetails[0][32],'enableAutoMerge',"Enable Auto Merge Savings");
                    displayYesNoAuto($userDetails[0][31],'enableMergeWithPurchase',"Enable Merge Saving With Purchase");
                  displaySubSectionEnd();

                  displaySubSectionStart("Coin Allocation");
                    displayText("usdtAllocTxt", "USDT Allocation: ",$userDetails[0][33],26,"");
                    displayText("btcAllocTxt", "BTC Allocation: ",$userDetails[0][34],27,"");
                    displayText("ethAllocTxt", "ETH Allocation: ",$userDetails[0][35],28,"");
                    displayText("pctOnLowTxt", "% on Low Market Mode: ",$userDetails[0][36],29,"");
                  displaySubSectionEnd();

                  displaySubSectionStart("Low Market Mode");
                    displayYesNoAuto($userDetails[0][28],"enableLowMarketMode","Enable Low Market Mode");// if ($userDetails[0][28] == 0){ $option1 = "No"; $option2 = "Yes";}else{$option1 = "Yes"; $option2 = "No";}
                    displayText("LowMarketModeNum", "Low Market Mode Number: ",$userDetails[0][28],30,"");
                    displayText("LowMarketModeStartPct", "Low Market Mode Start Pct: ",$userDetails[0][37],31,"");
                    displayText("LowMarketModeIncrements", "Low Market Mode Increments: ",$userDetails[0][38],32,"");
                  displaySubSectionEnd();

                  displaySubSectionStart("Save Mode");
                    displayText("SaveMode", "Save Mode: ",$userDetails[0][39],33,"");
                    displayText("PctToSave", "Pct To Save: ",$userDetails[0][40],34,"");
                    displayYesNoAuto($userDetails[0][50],"EnableSavePctofTotal","Enable Save Pct of Total");
                    displayText("SavingPctOfTotal", "Saving Pct Of Total: ",$userDetails[0][51],35,"");
                  displaySubSectionEnd();

                  displaySubSectionStart("SpreadBet");
                    displayYesNoAuto($userDetails[0][60],"EnableSpreadBetSellInd","Enable SpreadBet Sell Ind");
                    displayText("SpreadBetSellIndPct", "Spread Bet Sell Ind Pct: ",$userDetails[0][61],30,"");
                  displaySubSectionEnd();
                displayMainSectionEnd();
                ?>
                <input type="submit" name="submit" value="Update" class="form-control input-lg" tabindex="23">
          </div>
        </form><?php
        displaySideColumn(); ?>

</body>
</html>
