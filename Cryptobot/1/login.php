<?php
//include config
require_once('includes/config.php');
include_once '../includes/newConfig.php';
setStyle(isMobile());
//check if already logged in move to home page
if( $user->is_logged_in() ){ header('Location: index.php'); exit(); }

include_once ('../../../../SQLData.php');

function updateUser($nameUser){

  // Create connection
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "UPDATE `User` SET `FirstTimeLogin` = 0 where `UserName` = '$nameUser'";
  if ($conn->query($sql) === TRUE) {
      echo "New record created successfully";
  } else {
      echo "Error: " . $sql . "<br>" . $conn->error;
  }
  $conn->close();
}

function checkFirstTime($nameUser){
  $tempAry = [];

  // Create connection
  $conn = getSQLConn(rand(1,3));
  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }
  $sql = "SELECT `FirstTimeLogin`,`DisableUntil`,`ID` FROM `User` where `UserName` = '$nameUser'";
	//echo $sql;
  $result = $conn->query($sql);
  while ($row = mysqli_fetch_assoc($result)){
      $tempAry[] = Array($row['FirstTimeLogin'],$row['DisableUntil']);
  }
  $conn->close();
  return $tempAry;
}

//process login form if submitted
if(isset($_POST['submit'])){

	if (!isset($_POST['username'])) $error[] = "Please fill out all fields";
	if (!isset($_POST['password'])) $error[] = "Please fill out all fields";

	$username = $_POST['username'];
	if ( $user->isValidUsername($username)){
		if (!isset($_POST['password'])){
			$error[] = 'A password must be entered';
		}
		$password = $_POST['password'];
		//echo $password;
		if($user->login($username,$password)){
			$_SESSION['username'] = $username;
      $_SESSION['isMobile'] = False;
      if(isMobile()){ $_SESSION['isMobile'] = True;}
			$temp = checkFirstTime($username);
      $_SESSION['DisableUntil'] = $temp[0][1];
			updateUser($username);
      setStats();
      $_SESSION['TransListSelected'] = "Open";
      $_SESSION['BittrexListSelected'] = "1";
      $_SESSION['ConsoleSelected'] = "1";
      $_SESSION['ConsoleSubSelected'] = "1";
      $_SESSION['ConsoleSearchTxt'] = "1";
      $_SESSION['sellCoinsQueue'] = count(getTrackingSellCoins($temp[0][2]));
      $_SESSION['bittrexQueue'] = count(getBittrexRequestsOg($temp[0][2]));
      $ruleID = getBuyRulesIDs($temp[0][2]);
      $_SESSION['RuleIDSelected'] = $ruleID[0][0];
      $_SESSION['MobOverride'] = False;
      $_SESSION['MobDisplay'] = 0;
      $_SESSION['roundVar'] = 8;
      $coinPriceMatchNames = getCoinPriceMatchNames($temp[0][2], "`CoinPriceMatchName`","Limit 1");
      $_SESSION['coinPriceMatchNameSelected'] = $coinPriceMatchNames[0][1];
      $coinPricePatternNames = getCoinPriceMatchNames($_SESSION['ID'], "`CoinPricePatternName`","Limit 1");
      $_SESSION['coinPricePatternNameSelected'] = $coinPricePatternNames[0][1];
      $coin1HrPatternNames = getCoinPriceMatchNames($_SESSION['ID'], "`Coin1HrPatternName`","Limit 1");
      $_SESSION['coin1HrPatternNameSelected'] = $coin1HrPatternNames[0][1];
      $_SESSION['StatsListTime'] = '6 Hour';
			echo $temp[0][0];
			if ($temp[0][0] == 0){header('Location: Transactions.php');}else{header('Location: Transactions.php');}
			exit;

		} else {
			$error[] = 'Wrong username or password or your account has not been activated.'.ctype_alnum($username);
		}
	}else{
		$error[] = 'Usernames are required to be Alphanumeric, and between 3-16 characters long';
	}



}//end if submit

//define page title
$title = 'Login';

//include header template
require('layout/header.php');
?>
<div class="header">
  <table><TH><table class="CompanyName"><td rowspan="2" class="CompanyName"><img src='Images/CBLogoSmall.png' width="40"></td><td class="CompanyName"><div class="Crypto">Crypto</Div><td><tr class="CompanyName">
      <td class="CompanyName"><Div class="Bot">Bot</Div></td></table></TH></Table><br>

   </div>
   <div class="topnav">
       &nbsp
   </div>
<div class="row">
       <div class="column side">
        &npsp
      </div>
      <div class="column middle">

			<form role="form" method="post" action="" autocomplete="off">
				<h2 id='loginH2'>Please Login</h2>
				<p id='loginP'><a href='/content/1/index.php'>Back to home page</a></p>
				<hr>

				<?php
				//check for any errors
				if(isset($error)){
					foreach($error as $error){
						echo '<p class="bg-danger">'.$error.'</p>';
					}
				}

				if(isset($_GET['action'])){

					//check the action
					switch ($_GET['action']) {
						case 'active':
							echo "<h2 class='bg-success'>Your account is now active you may now log in.</h2>";
							break;
						case 'reset':
							echo "<h2 class='bg-success'>Please check your inbox for a reset link.</h2>";
							break;
						case 'resetAccount':
							echo "<h2 class='bg-success'>Password changed, you may now login.</h2>";
							break;
					}

				}


				?>

				<div class="form-group">
					<input type="text" name="username" id="username" class="form-control input-lg" placeholder="User Name" value="<?php if(isset($error)){ echo htmlspecialchars($_POST['username'], ENT_QUOTES); } ?>" tabindex="1">
				</div>

				<div class="form-group">
					<input type="password" name="password" id="password" class="form-control input-lg" placeholder="Password" tabindex="3">
				</div>
						 <a href='reset.php'>Forgot your Password?</a>
             <a href='Subscribe.php'>Subscribe</a>
				<hr>
          <input type="submit" name="submit" value="Login" class="btn btn-primary btn-block btn-lg" id="submitLogin" tabindex="5">
    </div>
    <div class="column side main">
        <img id='imageLogin' src='Images/CBLogoSmall.png' width="150">
    </div>
  </div>
<?php
//include header template
require('layout/footer.php');
?>
