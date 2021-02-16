<html>
<head>

</Head>
<style>
<?php include 'style/style.css'; ?>
</style>

<?php
include_once ('/home/stevenj1979/SQLData.php');
if(isset($_POST['submit'])){
  //Echo "Here!";
  $username = $_POST['username'];
  $subscriptionLength = $_POST['subscriptionLength'];
  $transactionRef = $_POST['TransactionReference'];
  updateSQL($username,$subscriptionLength,$transactionRef);
  header('Location: index.php');
}
  //Echo "Here2!";
function updateSQL($username,$subscriptionLength, $transactionRef){
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
    echo "Error";
      die("Connection failed: " . $conn->connect_error);
  }

  //$sql = "INSERT INTO `Subscription`(`UserName`, `SubscriptionLength`, `TransactionID`) VALUES ('$username',$subscriptionLength,'$transactionRef')";
  $sql = "INSERT INTO `Subscription`(`UserID`, `SubscriptionLength`, `TransactionID`, `Action`)
Select `ID`,$subscriptionLength,'$transactionRef','Extend' from `User` where `Username` = '$username'";
  print_r($sql);
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }

  $conn->close();

}
?>
<h1>CryptoBot</H1>
  <h2>Renew Subscription</h2>
<form role="form" method="post" action="" autocomplete="off">
  UserName
  <div class="form-group">
    <input type="text" name="username" id="username" class="form-control input-lg" placeholder="User Name" value="" tabindex="1">
  </div>
  Subscription Length
  <div class="form-group">
    <select name="subscriptionLength" class="form-control input-lg">
      <option value="12">12 Months BTC 0.01</option>
      <option value="6">6 Months BTC 0.005</option>
      <option value="3">3 Months BTC 0.0025</option>
    </select>
  </div>
  Please send your BTC to 1E9Q7An9hCY6qVuBA8Y6cAHS1VmgEMjfn6<br>
  <img src="Images/qr.png"><br>
  Transaction Reference
  <div class="form-group">
    <input type="text" name="TransactionReference" id="TransactionReference" class="form-control input-lg" placeholder="Transaction Reference" value="" tabindex="3">
  </div>
  <div class="row">
    <div class="col-xs-6 col-md-6"><input type="submit" name="submit" value="Submit" class="btn btn-primary btn-block btn-lg" tabindex="4"></div>
  </div>

</form>
<a href='login.php'>Back to Login</a>
<?php

?>
</html>
