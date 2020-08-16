<html>
<?php
ini_set('max_execution_time', 300);
require('includes/newConfig.php');
include_once ('/home/stevenj1979/SQLData.php');

function getTransStats(){
  $tempAry = [];
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  //$query = "SET time_zone = 'Asia/Dubai';";
  //$result = $conn->query($query);
  $sql = "SELECT `Tr`.`CoinID`,`Tr`.`UserID`, count(`Tr`.`CoinID`) as Count, `Usc`.`MergeAllCoinsDaily`, `Tr`.`ID`
FROM `Transaction` `Tr`
join `UserConfig` `Usc` on `Usc`.`UserID` = `Tr`.`UserID`
WHERE `Status` = 'Open'
Group by `CoinID`,`UserID`";
  //print_r($sql);
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){$tempAry[] = Array($row['CoinID'],$row['UserID'],$row['Count'],$row['MergeAllCoinsDaily'],$row['ID']);}
  $conn->close();
  return $tempAry;

}

function UpdateMerge($coinID,$userID){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {die("Connection failed: " . $conn->connect_error);}
  $sql = "UPDATE `Transaction` SET `ToMerge`= 1 WHERE `CoinID` = $coinID and `UserID` = $userID and `Status` = 'Open'";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();

}

$transStats = getTransStats();
$transStatsSize = count($transStats);

for ($g=0; $g<$transStatsSize; $g++){
  $coinID = $transStats[$g][0]; $userID = $transStats[$g][1];
  $count = $transStats[$g][2]; $mergeAllCoinsDaily = $transStats[$g][3];
  $ID = $transStats[$g][4];
  if ($count>=2 && $mergeAllCoinsDaily == 1){
    Echo "<BR> $coinID $userID $count $mergeAllCoinsDaily $ID";
    //Update merge for $ID
    UpdateMerge($coinID,$userID);
  }
}

?>
</html>
