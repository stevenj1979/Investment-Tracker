<?PHP
require('../../../SQLDb.php');

function SQLInsertUpdateCall($name,$sql,$UserID, $echo, $enabled, $history, $fileName, $daysToKeep){
    if($history == 1){
      $conn = getHistorySQL(rand(1,4));
    }else{
      $conn = getNewSQL(rand(1,4));
    }

    // Check connection
    if ($conn->connect_error) {
        errorLogToSQL($name,$sql,$UserID,$enabled,$fileName,$conn->error,$daysToKeep);
        die("Connection failed: " . $conn->connect_error);
    }

    if($echo == 1){
        print_r($sql);
    }

    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
        errorLogToSQL($name,$sql,$UserID,$enabled,$fileName,$conn->error,$daysToKeep);
    }
    $conn->close();
}

function SQLSelect($sql) {
  echo "<BR> Start";
  $res = mysql_query($sql) or trigger_error("db: ".mysql_error()." in ".$sql);
  $a   = array();
  if ($res) {
    while($row = mysql_fetch_assoc($res)) $a[]=$row;
  }else{
    errorLogToSQL($name,$sql,$UserID,$enabled,$fileName,$conn->error,$daysToKeep);
  }
  return $a;
}

?>
