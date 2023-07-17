<?PHP
include_once ('/home/stevenj1979/SQLData.php');
include_once ('newConfig.php');

function SQLInsertUpdateCall($name,$sql,$UserID, $echo, $enabled, $history, $fileName, $daysToKeep, $noError = 0){
    if($history == 1){
      $conn = getHistorySQL(rand(1,6));
    }else{
      $conn = getSQLConn(rand(1,6));
    }

    // Check connection
    if ($conn->connect_error) {
        if ($noError == 0){
          errorLogToSQL($name,$sql,$UserID,$enabled,$fileName,$conn->error,$daysToKeep);
        }
        die("Connection failed: " . $conn->connect_error);
    }

    if($echo == 1){
        print_r($sql);
    }

    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
        if ($noError == 0){
          errorLogToSQL($name,$sql,$UserID,$enabled,$fileName,$conn->error,$daysToKeep);
        }
    }
    $conn->close();
}

function SQLSelect($sql) {
  /*echo "<BR> Start";
  $res = mysql_query($sql) or trigger_error("db: ".mysql_error()." in ".$sql);
  $a   = array();
  if ($res) {
    while($row = mysql_fetch_assoc($res)) $a[]=$row;
  }else{
    errorLogToSQL($name,$sql,$UserID,$enabled,$fileName,$conn->error,$daysToKeep);
  }
  return $a;*/
}

function mySQLSelect($name,$sql,$UserID, $echo, $enabled, $history, $fileName, $daysToKeep){
  $tempAry = array();
  if($history == 1){
    $conn = getHistorySQL(rand(1,4));
  }else{
    $conn = getSQLConn(rand(1,3));
  }
  if($echo == 1){
      print_r($sql);
  }
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $result = $conn->query($sql);
  if ($result){
      //while ($row = mysqli_fetch_assoc($result)) {$tempAry[] = $row;}
      //while ($row = $result->fetch_array(MYSQLI_NUM));
      while($row = mysqli_fetch_array($result, MYSQLI_NUM)) {$tempAry[] = $row;}
  }else{
    //error here
    errorLogToSQL($name,$sql,$UserID,$enabled,$fileName,$conn->error,$daysToKeep);
  }
  $conn->close();
  return $tempAry;
}

?>
