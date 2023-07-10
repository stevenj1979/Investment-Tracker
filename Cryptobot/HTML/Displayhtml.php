<?php



function displayBox($boxAry){
  $boxArySize = count($boxAry);
  echo "<div class='flex-CoinBox'>";
  for ($row = 0; $row < $boxArySize; $row++) {
    $name = $boxAry[$row][0]; $data = $boxAry[$row][1]; $size = $boxAry[$row][3]; $image = $boxAry[$row][2];$type = $boxAry[$row][4]; $group = $boxAry[$row][5];
    $colour = $boxAry[$row][6]; $pct = $boxAry[$row][7]; $dataType = $boxAry[$row][8]; $roundVar = $boxAry[$row][9];
    if ($row == 0){
      $currentGroup = $group;
      echo "<DIV class='flex-CoinBoxItem'>";
    }
    if ($currentGroup <> $group){
      $currentGroup = $group;
      echo "</DIV><DIV class='flex-CoinBoxItem'>";
    }
    if ($dataType == 'Float'){
      //$data = round((float)$data+0,$roundVar);
      $data = number_format($data+0, $roundVar, '.', '');
    }
    if ($name == "") {$seperator = "";}else{ $seperator = ":";}

    if ($type == "Image"){
      echo "<DIV class='flex-CoinBoxImage'><a href='$data'><img src='$image'  width=60 height=60></a> </DIV>";
    }elseif ($type == "Link"){
      echo "<DIV class='flex-CoinBoxLink'><a href='$data'>$name</a> </DIV>";
    }elseif ($type == "Colour"){
      if ($data > 0){
        echo "<DIV class='flex-CoinBoxItemRow'>$name $seperator <span class='greenText'>$data</span> $pct</DIV>";
      }elseif ($data == 0){
        echo "<DIV class='flex-CoinBoxItemRow'>$name $seperator <span class='amberText'>$data</span> $pct</DIV>";
      }else{
        echo "<DIV class='flex-CoinBoxItemRow'>$name $seperator <span class='redText'>$data</span> $pct</DIV>";
      }
    }else{
      echo "<DIV class='flex-CoinBoxItemRow'>$name $seperator $data $pct</DIV>";
    }

  }
  echo "</DIV></DIV><br>";
}

?>
