<?php



function displayBox($boxAry){
  $boxArySize = count($boxAry);
  echo "<div class='flex-CoinBox'>";
  for ($row = 0; $row < $boxArySize; $row++) {
    $name = $boxAry[$row][0]; $data = $boxAry[$row][1]; $size = $boxAry[$row][3]; $image = $boxAry[$row][2];$type = $boxAry[$row][4]; $group = $boxAry[$row][5];
    $colour = $boxAry[$row][6];
    if ($row == 0){
      $currentGroup = $group;
      echo "<DIV class='flex-CoinBoxItem'>";
    }
    if ($currentGroup <> $group){
      $currentGroup = $group;
      echo "</DIV><DIV class='flex-CoinBoxItem'>";
    }
      if ($type == "Image"){
        echo "<DIV class='flex-CoinBoxItemRow'><a href='$data'><img src='$image'></a> </DIV>";
      }elseif ($type == "Link"){
        echo "<DIV class='flex-CoinBoxItemRow'><a href='$data'>$name</a> </DIV>";
      }elseif ($type == "Colour"){
        echo "<DIV class='flex-CoinBoxItemRow'>$name : <p id='smallText' style='color:$colour'>$data</p></DIV>";
      }elseif ($type == "Pct"){
        echo "<DIV class='flex-CoinBoxItemRow'>$name : $data %</DIV>";
      }else{
        echo "<DIV class='flex-CoinBoxItemRow'>$name : $data</DIV>";
      }

  }
  echo "</DIV></DIV><br>";
}

?>
