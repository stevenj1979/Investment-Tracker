<html>
<?php
ini_set('max_execution_time', 300);
require('includes/newConfig.php');

include_once ('/home/stevenj1979/SQLData.php');
include_once ('/home/stevenj1979/Encrypt.php');

$apikey=getAPIKey();
$apisecret=getAPISecret();


function isSaleComplete($saleAry,$num){
  $apiVersion = 3;
  $TransactionID = $saleAry[$num][0]; $status  = $saleAry[$num][1]; $bittrexRef  = $saleAry[$num][14]; $newCoinID  = $saleAry[$num][3]; $newCoinPrice = $saleAry[$num][4];
  $baseCurrency  = $saleAry[$num][5]; $totalAmount = $saleAry[$num][6]; $originalPurchasePrice  = $saleAry[$num][7]; $apikey = $saleAry[$num][8]; $apisecret = $saleAry[$num][9];
  $Kek = $saleAry[$num][10];
  if (!Empty($Kek)){ $apiSecret = Decrypt($Kek,$saleAry[$num][9]);}
  Echo "<BR>bittrexOrder($apikey, $apiSecret, $bittrexRef, $apiVersion);";
  $resultOrd = bittrexOrder($apikey, $apiSecret, $bittrexRef, $apiVersion);
  echo "<BR> Status: ".$resultOrd["status"];
  if ($resultOrd["status"] == 'CLOSED'){
    //$finalPrice = number_format((float)$resultOrd["result"]["PricePerUnit"], 8, '.', '');
    $tempPrice = number_format((float)$resultOrd["proceeds"], 8, '.', '');
    $orderQty = $resultOrd["quantity"];
    //$cancelInit = $resultOrd["result"]["CancelInitiated"];
    $finalPrice = $tempPrice/$orderQty;
    $qtySold = $resultOrd["fillQuantity"];
    $saleStatus = $resultOrd["status"];
    $orderQtyRemaining = $orderQty-$qtySold;
    newLogToSQL("CoinSwap","return Array($saleStatus,$finalPrice,$orderQty,$qtySold);$tempPrice",3,1,"updateCoinSwapStatus","TransID:$TransactionID");
    return Array($saleStatus,$finalPrice,$orderQty,$qtySold);
  }

}

function writeFinalPrice($TransactionID,$finalPrice){
  $conn = getSQLConn(rand(1,3));
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $sql = "UPDATE `Transaction` SET `CoinPrice`= $finalPrice where `ID` = $TransactionID";
    //print_r($sql);
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    newLogToSQL("writeFinalPrice",$sql,3,1,"SQL","BittrexID:$bittrexRef");
    $conn->close();
}

function isBuyComplete($buyAry,$num){
  $apiVersion = 3;
  $TransactionID = $buyAry[$num][0]; $status  = $buyAry[$num][1]; $bittrexRef  = $buyAry[$num][2]; $newCoinID  = $buyAry[$num][3]; $newCoinPrice = $buyAry[$num][4];
  $baseCurrency  = $buyAry[$num][5]; $totalAmount = $buyAry[$num][6]; $originalPurchasePrice  = $buyAry[$num][7]; $apikey = $buyAry[$num][8]; $apisecret = $buyAry[$num][9];
  $Kek = $buyAry[$num][10];
  if (!Empty($Kek)){ $apiSecret = Decrypt($Kek,$buyAry[$num][9]);}
  echo "<BR>bittrexOrder($apikey, $apiSecret, $bittrexRef, $apiVersion);";
  $resultOrd = bittrexOrder($apikey, $apiSecret, $bittrexRef, $apiVersion);
  if ($resultOrd["status"] == 'CLOSED'){
    $tempPrice = number_format((float)$resultOrd["proceeds"], 8, '.', '');
    $orderQty = $resultOrd["quantity"];
    $finalPrice = $tempPrice/$orderQty;
    //$cancelInit = $resultOrd["result"]["CancelInitiated"];
    $qtySold = $resultOrd["fillQuantity"];
    $saleStatus = $resultOrd["status"];
    $orderQtyRemaining = $orderQty-$qtySold;
    writeFinalPrice($TransactionID,$finalPrice);
    return Array($saleStatus,$finalPrice,$orderQty,$qtySold);
  }
}

function runCoinSwaps(){
  $coinSwaps = getOpenCoinSwaps();
  $coinSwapsSize = newCount($coinSwaps);
  $apiVersion = 3; $ruleID = 111111;
  for ($y=0; $y<$coinSwapsSize; $y++){
    $status = $coinSwaps[$y][1];
    echo "<BR> Running SwapCoins:  loop size: $coinSwapsSize | Status: $status";
    if ($status == 'AwaitingSale'){
      //Check if sale is complete
      $orderSale = isSaleComplete($coinSwaps,$y);
      Echo "<BR> AwaitingSale: ".$orderSale[0];
      if ($orderSale[0] == 'CLOSED'){
        //Buy new COIN
        $apikey = $coinSwaps[$y][8];$apisecret = $coinSwaps[$y][9];$KEK = $coinSwaps[$y][10];
        $bitPrice = number_format($coinSwaps[$y][4],8); $baseCurrency = $coinSwaps[$y][5]; $totalAmount = $coinSwaps[$y][6];
        $btcBuyAmount =  number_format($totalAmount/$bitPrice,10); $transID = $coinSwaps[$y][0];
        $newCoinSwap = getNewSwapCoin($baseCurrency);
        $coinSwapSize = newCount($newCoinSwap);
        if ($coinSwapSize > 0){
          $coin = $newCoinSwap[0][0]; $liveCoinPrice = $newCoinSwap[0][2];
          $symbol = $newCoinSwap[0][5]; //$totalAmount = $newCoinSwap[0][6];
          $rate = $newCoinSwap[0][4];
          $quant = $totalAmount/$rate;
          Echo "<BR> Quanitiy: $totalAmount/$rate | $quant";
          if (!Empty($KEK)){ $apisecret = Decrypt($KEK,$coinSwaps[$y][9]);}
          echo"<BR> bittrexbuy($apikey, $apisecret, $symbol, $quant, $rate, $baseCurrency,$apiVersion,FALSE);";
          $obj = bittrexbuy($apikey, $apisecret, $symbol, $quant, $rate, $baseCurrency,$apiVersion,FALSE);
          //Save Reference
          $bittrexRef = $obj["id"];
          if ($bittrexRef <> ""){
            Echo "<BR> Bittrex ID: $bittrexRef";
            updateCoinSwapBittrexID($bittrexRef,$transID,$coin,$liveCoinPrice,'Buy',$orderSale[1]);
            //Change Status to AwaitingBuy
            updateCoinSwapStatus('AwaitingBuy',$transID);
            logAction("CoinSwap; AwaitingSale : $symbol | $quant | $rate | $baseCurrency | $bittrexRef | SET TO: AwaitingBuy", 'BuySellFlow', 1);
          }
        }

      }

    }else if ($status == 'AwaitingBuy'){
      //Check if buy is complete
      $orderBuy = isBuyComplete($coinSwaps,$y);
      Echo "<BR> AwaitingBuy: ".$orderBuy[0];
      if ($orderBuy[0] == 'CLOSED'){
        //Update Transaction
        updateCoinSwapCoinDetails($coinSwaps[$y][3],$orderBuy[1],$orderBuy[2],"ORD".$coin.date("YmdHis", time()).$ruleID,"Open",$coinSwaps[$y][0]);
        //Close CoinSwap
        updateCoinSwapStatus('Closed',$coinSwaps[$y][0]);
        logAction("CoinSwap; AwaitingBuy : $symbol | $quant | $rate | $baseCurrency | ".$orderBuy[1]." | ".$orderBuy[2]." | SET TO: Closed", 'BuySellFlow', 1);
        //Change Transaction Status to Open
        //updateCoinSwapTransactionStatus('Open',$transactionID);
      }
    }else if ($status == 'AwaitingSavingsSale'){
      $transID = $coinSwaps[$y][0];
      $orderSale = isSaleComplete($coinSwaps,$y);
      Echo "<BR> AwaitingSavingsSale: ".$orderSale[0];
      if ($orderSale[0] == 'CLOSED'){
        $finalPrice = $orderSale[1];
        newLogToSQL("CoinSwap","updateCoinSwapStatus('AwaitingSavingsBuy',$transID,$finalPrice);",3,1,"updateCoinSwapStatus","TransID:$transID");
        updateCoinSwapStatusFinalPrice('AwaitingSavingsBuy',$transID,$finalPrice);
        updateCoinSwapTransactionStatus('SavingsSell',$transID);
        logAction("CoinSwap; AwaitingSavingsSale : $symbol | $baseCurrency | $finalPrice | $transID | SET TO: AwaitingSavingsBuy", 'BuySellFlow', 1);
      }
    /*}else if ($status == 'AwaitingSavingsBuy'){
      $apikey = $coinSwaps[$y][8];$apisecret = $coinSwaps[$y][9];$KEK = $coinSwaps[$y][10];$ogCoinID = $coinSwaps[$y][12];$ogSymbol = $coinSwaps[$y][13];
      $bitPrice = number_format($coinSwaps[$y][16],8); $baseCurrency = $coinSwaps[$y][5]; $totalAmount = $coinSwaps[$y][6]; $transID = $coinSwaps[$y][0];
      $finalPrice = $coinSwaps[$y][15];
      //$orderSale = isSaleComplete($coinSwaps,$y);
      $sellPct = 15;
      $sellPctwithTolerance = $sellPct-(($sellPct/100)*5);
      $lowPrice = $finalPrice-(($finalPrice/100)*$sellPctwithTolerance);
      echo "<BR> TEST Buy: $lowPrice | $bitPrice";
      if ($bitPrice <= $lowPrice){
        if (!Empty($KEK)){ $apisecret = Decrypt($KEK,$coinSwaps[$y][9]);}
        $liveCoinPrice = $bitPrice;
        $rate = $liveCoinPrice;
        $quant = $totalAmount/$rate;
        echo"<BR> bittrexbuy($apikey, $apisecret, $ogSymbol, $quant, $rate, $baseCurrency,$apiVersion,FALSE);";
        $obj = bittrexbuy($apikey, $apisecret, $ogSymbol, $quant, $rate, $baseCurrency,$apiVersion,FALSE);
        $bittrexRef = $obj["id"];
        if ($bittrexRef <> ""){
          Echo "<BR> Bittrex ID: $bittrexRef";
          updateCoinSwapBittrexID($bittrexRef,$transID,$ogCoinID,$liveCoinPrice,'Buy');
          //Change Status to AwaitingBuy
          updateCoinSwapStatus('AwaitingSavingsPurchase',$transID);
        }
      }*/
    }else if ($status == 'AwaitingSavingsPurchase'){
      //Check if buy is complete
      $orderBuy = isBuyComplete($coinSwaps,$y);
      Echo "<BR> AwaitingSavingsPurchase: ".$orderBuy[0];
      if ($orderBuy[0] == 'CLOSED'){
        $ogCoinID = $coinSwaps[$y][12];$ogSymbol = $coinSwaps[$y][13]; $finalPrice = $orderBuy[1]; $orderQty = $orderBuy[2];
        $transID = $coinSwaps[$y][0];
        updateCoinSwapCoinDetails($ogCoinID,$finalPrice,$orderQty,"ORD".$ogSymbol.date("YmdHis", time()).$ruleID,"Saving",$transID);
        //Close CoinSwap
        updateCoinSwapStatus('Closed',$transID);
        logAction("CoinSwap; AwaitingSavingsPurchase : $ogSymbol | $ogCoinID | $orderQty | $baseCurrency | $finalPrice | $transID | SET TO: Saving", 'BuySellFlow', 1);
      }
    }
  }
}


// MAIN PROGRAMME
runCoinSwaps();
fixResidual();
?>
</html>
