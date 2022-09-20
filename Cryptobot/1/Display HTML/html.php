<html>
<head>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
</head>
<?php require('includes/config.php');
include_once '../includes/newConfig.php';

//if not logged in redirect to login page
if(!$user->is_logged_in()){ header('Location: login.php'); exit(); }

function displayDropDown($arr, $selected, $numText, $numValue, $nameID){
  $arrSize = count($arr);
  echo "<select name='$nameID' id='$nameID' class='enableTextBox'>";
  for ($y=0; $y<$arrSize; $y++){
    $itemName = $arr[$y][$numText]; $itemVal = $arr[$y][$numValue];
    if ($itemName == $selected){
      echo "<option value='".$itemVal."'>".$itemName."</option>";
    }else{
      echo "<option value='".$itemVal."'>".$itemName."</option>";
    }
  }
  echo "</Select>";
}


?>
</HTML>
