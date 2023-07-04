<?php



function displayBox($boxAry){
  $boxArySize = count($boxAry);
  echo "<div class='flex-CoinBox'>";
  for ($row = 0; $row < $boxArySize; $row++) {
    $name = $boxAry[$row][0]; $data = $boxAry[$row][1]; $size = $boxAry[$row][3]; $image = $boxAry[$row][2];
      if ($name == "Image"){
        echo "<DIV class='flex-CoinBoxItem'><a href='$data'><img src='$image'></a> </DIV>";
      }elseif ($name == "Link"){
        echo "<DIV class='flex-CoinBoxItem'><a href='$data'>$name</a> </DIV>";
      }else{
        echo "<DIV class='flex-CoinBoxItem'>$data</DIV>";
      }

  }
  echo "</DIV>";
}

?>
