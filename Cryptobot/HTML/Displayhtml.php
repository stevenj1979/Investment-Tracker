<?php



function displayBox($boxAry){
  $boxArySize = count($boxAry);
  echo "<div class='flex-CoinBox'>";
  for ($row = 0; $row < $boxArySize; $row++) {
    $name = $boxAry[$row][0]; $data = $boxAry[$row][1]; $size = $boxAry[$row][2];
      echo "<DIV class='flex-CoinBoxItem'>name: $name | data: $data | size: $size </DIV>";
  }
  echo "</DIV>";
}

?>
