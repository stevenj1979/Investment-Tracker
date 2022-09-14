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
    //if($lowMarketModeEnabled == "Yes"){ $setLowMarket = -1;} else {$setLowMarket = 0;}
    if ($saveMode > 0 ){ $saveResidualCoins = 'No';}
    $holdCoinForBuyOut = $_POST['HoldCoinForBuyOut'];
    if(empty($_POST['HoldCoinForBuyOut'])){$holdCoinForBuyOut = 0;}
    $coinForBuyOutPct = $_POST['CoinForBuyOutPct'];
    if(empty($_POST['CoinForBuyOutPct'])){$coinForBuyOutPct = 0;}

    //echo "Here1! $lowMarketModeEnabled _ $setLowMarket";
    $settingsUpdateAry = Array($userID,$userName,$email,$APIKey,$APISecret,$dailyBTCLimit,$totalBTCLimit,$enableDailyBTCLimit,$enableTotalBTCLimit,$btcBuyAmount,$baseCurrency,$enableLowPurchasePrice,$noOfPurchases,$pctToPurchase,$totalRisesInPrice,$totalRisesInPriceSell,$noOfCoinPurchase,  //16
    $hoursFlatTolerance,$lowMarketModeEnabled,$minsToPauseAfterPurchase,$saveResidualCoins,$reduceLossEnabled,$redirectPurchasesToSpread,$redirectPurchasesToSpreadID,$buyBackEnabled,$allBuyBackAsOverride,$sellSavingsEnabled,$rebuySavingsEnabled,$autoMergeSavings,$mergeSavingWithPurchase,   //29
    $usdtAlloc,$btcAlloc,$ethAlloc,$pctOnLow,$lowMarketModeStartPct,$lowMarketModeIncrements,$saveMode,$pctToSave,$sellPct,$originalPriceMultiplier,$reduceLossMaxCounter,$pauseCoinIDAfterPurchaseEnabled,$daysToPauseCoinIDAfterPurchase,$bbHoursFlat,$reduceLossHoursFlat,$holdCoinForBuyOut,   //45
    $coinForBuyOutPct);
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
  , `PauseCoinIDAfterPurchaseEnabled`, `DaysToPauseCoinIDAfterPurchase`,`BuyBackHoursFlatTarget`,`HoursFlatRls`,`HoldCoinForBuyOut`,`CoinForBuyOutPct`
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
      ,$row['BuyBackHoursFlatTarget'],$row['HoursFlatRls'],$row['HoldCoinForBuyOut'],$row['CoinForBuyOutPct']); //49
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
  $lowMarketModeStartPct = $settingsUpdateAry[34]; $lowMarketModeIncrements = $settingsUpdateAry[35];
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
  $reduceLossHoursFlat = $settingsUpdateAry[44];
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
        `HoldCoinForBuyOut` = $holdCoinForBuyOut, `CoinForBuyOutPct` = $coinForBuyOutPct
         WHERE `UserID` = $userID;
         UPDATE `User` SET `UserName`='$newusername',`Email`='$email' WHERE `ID` = $userID;
         UPDATE `ReduceLossSettings` SET `Enabled`= $reduceLossEnabled, `SellPct` = $sellPct, `OriginalPriceMultiplier` = $originalPriceMultiplier, `ReduceLossMaxCounter` = $reduceLossMaxCounter, `HoursFlat` = $reduceLossHoursFlat WHERE `UserID` = $userID;
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
              <div class="form-group">
                <b>UserName: </b><br/>
                <input type="text" name="newusername" id="newusername" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][2]; ?>" tabindex="1">
              </div>
              <div class="form-group">
                <b>Email: </b><br/>
                <input type="text" name="email" id="email" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][10]; ?>" tabindex="2">
              </div>
              <div class="form-group">
                <b>API Key: </b><br/>
                <input type="text" name="API_Key" id="API_Key" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][4]; ?>" tabindex="3">
                <p class="comments">Bittrex API Key</p>
              </div>
              <div class="form-group">
                <b>API Secret: </b><br/>
                <input type="text" name="API_Secret" id="API_Secret" class="form-control input-lg" placeholder="User Name" value="<?php echo $apisecret; ?>" tabindex="4">
                <p class="comments">Bittrex Secret API Key</p>
              </div>
              <div class="form-group">
                <b>BTC Buy Amount: </b><br/>
                <input type="text" name="BTCBuyAmount" id="BTCBuyAmount" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][11]; ?>" tabindex="5">
                <p class="comments">Amount in BTC for each buy</p>
              </div>
                  <?php if ($userDetails[0][6] == 1){ $option1 = "Yes"; $option2 = "No";}else{$option1 = "No"; $option2 = "Yes";}?>
                    <div class='settingsform'>
                      <b>Enable Daily BTC Limit: </b><br/><select name='enableDailyBTCLimit' id='enableDailyBTCLimit' class='enableTextBox'><?php
                        echo "<option value='".$option1."'>".$option1."</option>
                        <option value='".$option2."'>".$option2."</option></select></div>";?>
                  <div class="form-group">
                    <b>Daily BTC Limit: </b><br/>
                    <input type="text" name="dailyBTCLimit" id="dailyBTCLimit" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][8]; ?>" tabindex="6">
                    <p class="comments">Amount in BTC for each buy</p>
                  </div>
                  <?php if ($userDetails[0][7] == 1){ $option1 = "Yes"; $option2 = "No";}else{$option1 = "No"; $option2 = "Yes";}?>
                    <div class='settingsform'>
                      <b>Enable Total BTC Limit: </b><br/><select name='enableTotalBTCLimit' id='enableTotalBTCLimit' class='enableTextBox'><?php
                        echo "<option value='".$option1."'>".$option1."</option>
                        <option value='".$option2."'>".$option2."</option></select></div>";?>
                  <div class="form-group">
                    <b>Total BTC Limit: </b><br/>
                    <input type="text" name="totalBTCLimit" id="totalBTCLimit" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][9]; ?>" tabindex="7">
                    <p class="comments">Amount in BTC for each buy</p>
                  </div>
                  <?php if ($userDetails[0][12] == 'BTC'){ $option1 = "BTC"; $option2 = "USDT";$option3 = "ETH";$option4 = "All";}
                  elseif ($userDetails[0][12] == 'USDT'){$option1 = "USDT"; $option2 = "BTC";$option3 = "ETH";$option4 = "All";}
                  elseif ($userDetails[0][12] == 'ETH'){$option1 = "ETH"; $option2 = "BTC";$option3 = "USDT";$option4 = "All";}
                  elseif ($userDetails[0][12] == 'All'){$option1 = "All"; $option2 = "BTC";$option3 = "ETH";$option4 = "USDT";}?>
                    <div class='settingsform'>
                      <b>User Base Currency: </b><br/><select name='userBaseCurrency' id='userBaseCurrency' class='enableTextBox'><?php
                        echo "<option value='".$option1."'>".$option1."</option>
                        <option value='".$option2."'>".$option2."</option>
                        <option value='".$option3."'>".$option3."</option>
                        <option value='".$option4."'>".$option4."</option></select></div><BR>";

                        Echo "<select name='$name' size='3'>";
                        for ($i=0; $i<$sequenceCount; $i++){
                          $sellRuleID = $sequence[$i][0]; $newSeq = $sequence[$i][1];
                          echo "<option value='$newSeq'>$newSeq:$sellRuleID</option>";
                        }
                        echo "</select>";
                        echo "<input type='submit' name='publishHr1' value='+'><input type='submit' name='removeHr1' value='-'>";
                        ?>
                        <?php if ($userDetails[0][14] == 1){ $option1 = "Yes"; $option2 = "No";}else{$option1 = "No"; $option2 = "Yes";}?>
                          <div class='settingsform'>
                            <b>Low Price Purchase Enabled: </b><br/><select name='lowPricePurchaseEnabled' id='enableDailyBTCLimit' class='enableTextBox'><?php
                              echo "<option value='".$option1."'>".$option1."</option>
                              <option value='".$option2."'>".$option2."</option></select></div>";?>
              <div class="form-group">
                  <b>Number of Purchases: </b><br/>
                  <input type="text" name="NoOfPurchases" id="totalBTCLimit" class="form-control input-lg" placeholder="2" value="<?php echo $userDetails[0][15]; ?>" tabindex="5">
                  <p class="comments">Amount in BTC for each buy</p>
                </div>
                <div class="form-group">
                    <b>% to Purchase: </b><br/>
                    <input type="text" name="PctToPurchase" id="totalBTCLimit" class="form-control input-lg" placeholder="-10" value="<?php echo $userDetails[0][16]; ?>" tabindex="6">
                    <p class="comments">Amount in BTC for each buy</p>
                  </div>
                  <div class="form-group">
                      <b>Total Rises In Price: </b><br/>
                      <input type="text" name="TotalRisesInPrice" id="totalBTCLimit" class="form-control input-lg" placeholder="-10" value="<?php echo $userDetails[0][17]; ?>" tabindex="7">
                      <p class="comments">Amount in BTC for each buy</p>
                    </div>
                    <div class="form-group">
                        <b>Total Rises In Price Sell: </b><br/>
                        <input type="text" name="TotalRisesInPriceSell" id="totalBTCLimit" class="form-control input-lg" placeholder="-10" value="<?php echo $userDetails[0][18]; ?>" tabindex="8">
                        <p class="comments">Amount in BTC for each buy</p>
                      </div>
                      <div class="form-group">
                          <b>No of Coin Purchase: </b><br/>
                          <input type="text" name="NoOfCoinPurchase" id="noOfCoinPurchase" class="form-control input-lg" placeholder="-10" value="<?php echo $userDetails[0][19]; ?>" tabindex="9">
                          <p class="comments">Amount in BTC for each buy</p>
                        </div>
                        <div class="form-group">
                            <b>Buy Admin: </b><br/>
                            <div class="form-group">
                    <b>HoursFlatTolerance: </b><br/>
                    <input type="text" name="hoursFlatTol" id="hoursFlatTol" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][30]; ?>" tabindex="10">
                    <p class="comments">Amount in BTC for each buy</p>
                  </div>

                        <div class="form-group">
                    <b>Mins To Pause After Purchase: </b><br/>
                    <?php if ($userDetails[0][44] == 1){ $option1 = "Yes"; $option2 = "No";}else{$option1 = "No"; $option2 = "Yes";}?>
                    <input type="text" name="minsPauseAfterPurchase" id="minsPauseAfterPurchase" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][27]; ?>" tabindex="11">
                    <p class="comments">Amount in BTC for each buy</p>
                    <b>Pause CoinID After Purchase Enabled: </b><br/>
                    <select name='PauseCoinIDAfterPurchaseEnabled' id='PauseCoinIDAfterPurchaseEnabled' class='enableTextBox'><?php
                      echo "<option value='".$option1."'>".$option1."</option>
                      <option value='".$option2."'>".$option2."</option></select></div>";?>
                    <input type="text" name="DaysToPauseCoinIDAfterPurchase" id="DaysToPauseCoinIDAfterPurchase" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][45]; ?>" tabindex="11">
                    <p class="comments">Amount in BTC for each buy</p>
                  </div>

                  <?php if ($userDetails[0][24] == 1){ $option1 = "Yes"; $option2 = "No";}else{$option1 = "No"; $option2 = "Yes";}?>
                                      <div class='settingsform'>
                                        <b>Enable Save Residual: </b><br/><select name='saveResidual' id='saveResidual' class='enableTextBox'><?php
                                          echo "<option value='".$option1."'>".$option1."</option>
                                          <option value='".$option2."'>".$option2."</option></select></div>";?>


                  <?php if ($userDetails[0][20] == 1){ $option1 = "Yes"; $option2 = "No";}else{$option1 = "No"; $option2 = "Yes";}?>
                    <div class='settingsform'>
                      <b>Enable Reduce Loss: </b><br/><select name='enableReduceLoss' id='enableReduceLoss' class='enableTextBox'><?php
                        echo "<option value='".$option1."'>".$option1."</option>
                        <option value='".$option2."'>".$option2."</option></select>";?>
                        <input type="text" name="SellPct" id="SellPct" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][41]; ?>" tabindex="22">
                        <p class="comments">Reduce Loss Sell %</p>
                        <input type="text" name="OriginalPriceMultiplier" id="OriginalPriceMultiplier" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][42]; ?>" tabindex="22">
                        <p class="comments">Reduce Loss Original Price Multiplier</p>
                        <input type="text" name="ReduceLossMaxCounter" id="ReduceLossMaxCounter" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][43]; ?>" tabindex="22">
                        <p class="comments">Reduce Loss Max Counter</p>
                        <input type="text" name="ReduceLossHoursFlat" id="ReduceLossHoursFlat" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][47]; ?>" tabindex="22">
                        <p class="comments">Reduce Loss Hours Flat</p>
                        <?php if ($userDetails[0][48] == 1){ $option1 = "Yes"; $option2 = "No";}else{$option1 = "No"; $option2 = "Yes";}?>
                        <b>Hold Coin for buyout Enabled: </b><br/><select name='HoldCoinForBuyOut' id='HoldCoinForBuyOut' class='enableTextBox'><?php
                          echo "<option value='".$option1."'>".$option1."</option>
                          <option value='".$option2."'>".$option2."</option></select>";?>

                        <input type="text" name="CoinForBuyOutPct" id="CoinForBuyOutPct" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][49]; ?>" tabindex="22">
                        <p class="comments">Hold Coin buyout Pct</p>
                        </div>
        <div class='settingsform'>
                <b>Redirect: </b><br/>
                <?php if ($userDetails[0][26] == 1){ $option1 = "Yes"; $option2 = "No";}else{$option1 = "No"; $option2 = "Yes";}?>

                    <b>Redirect All Purchases To SpreadBet: </b><br/><select name='enableRedirectToSB' id='enableRedirectToSB' class='enableTextBox'><?php
                      echo "<option value='".$option1."'>".$option1."</option>
                      <option value='".$option2."'>".$option2."</option></select>";?>

                                          <b>Redirect SpreadBet ID: </b><br/>
                                          <input type="text" name="redirectSBID" id="redirectSBID" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][25]; ?>" tabindex="13">
                                          <p class="comments">Amount in BTC for each buy</p>

        </div>
        <div class='settingsform'>
                <b>Buyback: </b><br/>
                <?php if ($userDetails[0][23] == 1){ $option1 = "Yes"; $option2 = "No";}else{$option1 = "No"; $option2 = "Yes";}?>

                                      <b>Enable BuyBack: </b><br/><select name='enableBuyBack' id='enableBuyBack' class='enableTextBox'><?php
                                        echo "<option value='".$option1."'>".$option1."</option>
                                        <option value='".$option2."'>".$option2."</option></select>";?>
                <?php if ($userDetails[0][29] == 1){ $option1 = "Yes"; $option2 = "No";}else{$option1 = "No"; $option2 = "Yes";}?>

                                      <b>Enable All BuyBack as Override: </b><br/><select name='enableAllBBasOverride' id='enableAllBBasOverride' class='enableTextBox'><?php
                                        echo "<option value='".$option1."'>".$option1."</option>
                                        <option value='".$option2."'>".$option2."</option></select>";?>
                                        <b>Hours Flat: </b><br/>
                                        <input type="text" name="buyBackHoursFlat" id="buyBackHoursFlat" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][46]; ?>" tabindex="13">
                    </div>
                    <div class='settingsform'>
                            <b>Savings: </b><br/>
                            <?php if ($userDetails[0][22] == 1){ $option1 = "Yes"; $option2 = "No";}else{$option1 = "No"; $option2 = "Yes";}?>

                                                  <b>Enable Sell Savings: </b><br/><select name='enableSellSavings' id='enableSellSavings' class='enableTextBox'><?php
                                                    echo "<option value='".$option1."'>".$option1."</option>
                                                    <option value='".$option2."'>".$option2."</option></select>";?>
                            <?php if ($userDetails[0][21] == 1){ $option1 = "Yes"; $option2 = "No";}else{$option1 = "No"; $option2 = "Yes";}?>

                                                <b>Enable ReBuy Savings: </b><br/><select name='enableReBuySaving' id='enableReBuySaving' class='enableTextBox'><?php
                                                  echo "<option value='".$option1."'>".$option1."</option>
                                                  <option value='".$option2."'>".$option2."</option></select>";?>
                            <?php if ($userDetails[0][32] == 1){ $option1 = "Yes"; $option2 = "No";}else{$option1 = "No"; $option2 = "Yes";}?>

                                                  <b>Enable Auto Merge Savings: </b><br/><select name='enableAutoMerge' id='enableAutoMerge' class='enableTextBox'><?php
                                                    echo "<option value='".$option1."'>".$option1."</option>
                                                    <option value='".$option2."'>".$option2."</option></select>";?>
                            <?php if ($userDetails[0][31] == 1){ $option1 = "Yes"; $option2 = "No";}else{$option1 = "No"; $option2 = "Yes";}?>

                                  <b>Enable Merge Saving With Purchase: </b><br/><select name='enableMergeWithPurchase' id='enableMergeWithPurchase' class='enableTextBox'><?php
                                    echo "<option value='".$option1."'>".$option1."</option>
                                    <option value='".$option2."'>".$option2."</option></select>";?>
                    </DIV>
                    <div class='settingsform'>
                        <b>Coin Allocation: </b><br/>
                        <input type="text" name="usdtAllocTxt" id="usdtAllocTxt" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][33]; ?>" tabindex="14">
                        <p class="comments">USDT Allocation</p>
                        <input type="text" name="btcAllocTxt" id="btcAllocTxt" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][34]; ?>" tabindex="15">
                        <p class="comments">BTC Allocation</p>
                        <input type="text" name="ethAllocTxt" id="ethAllocTxt" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][35]; ?>" tabindex="16">
                        <p class="comments">ETH Allocation</p>
                        <input type="text" name="pctOnLowTxt" id="pctOnLowTxt" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][36]; ?>" tabindex="17">
                        <p class="comments">% on Low Market Mode</p>
                    </DIV>
                    <div class='settingsform'>
                      <b>Low Market Mode: </b><br/>
                      <?php if ($userDetails[0][28] == 0){ $option1 = "No"; $option2 = "Yes";}else{$option1 = "Yes"; $option2 = "No";}?>

                          <select name='enableLowMarketMode' id='enableLowMarketMode' class='enableTextBox'><?php
                            echo "<option value='".$option1."'>".$option1."</option>
                            <option value='".$option2."'>".$option2."</option></select><br>";?>
                      <input type="text" name="LowMarketModeNum" id="LowMarketModeNum" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][28]; ?>" tabindex="18">
                      <p class="comments">Low Market Mode Number</p>
                      <input type="text" name="LowMarketModeStartPct" id="LowMarketModeStartPct" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][37]; ?>" tabindex="19">
                      <p class="comments">Low Market Mode Start Pct</p>
                      <input type="text" name="LowMarketModeIncrements" id="LowMarketModeIncrements" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][38]; ?>" tabindex="20">
                      <p class="comments">Low Market Mode Increments</p>

                      </DIV>

                    <div class='settingsform'>
                        <b>Save Mode: </b><br/>
                        <input type="text" name="SaveMode" id="SaveMode" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][39]; ?>" tabindex="21">
                        <p class="comments">Save Mode</p>
                        <input type="text" name="PctToSave" id="PctToSave" class="form-control input-lg" placeholder="User Name" value="<?php echo $userDetails[0][40]; ?>" tabindex="22">
                        <p class="comments">Low Market Mode Increments</p>
                      </div>
                <input type="submit" name="submit" value="Update" class="form-control input-lg" tabindex="23">
              </div>
            </form><?php
            displaySideColumn(); ?>

</body>
</html>
