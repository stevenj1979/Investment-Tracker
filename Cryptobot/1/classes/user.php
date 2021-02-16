<?php
include($_SERVER['DOCUMENT_ROOT'].'/Investment-Tracker/Cryptobot/1/classes/password.php');
class User extends Password{

    private $_db;

    function __construct($db){
    	parent::__construct();

    	$this->_db = $db;
    }

	private function get_user_hash($username){
    $sql = "SELECT Password, UserName, ID, AccountType FROM User WHERE UserName = '$username' AND Active='Yes'";
		try {
			$stmt = $this->_db->prepare($sql);
      //$stmt->bindParam(':username', $username, PDO::PARAM_STR, 12);
			//$stmt->execute(Array(':username' => $username));
      $stmt->execute();
			return $stmt->fetch();

		} catch(PDOException $e) {
		    echo '<p class="bg-danger">'.$e->getMessage().'</p>';
		}
	}

	public function isValidUsername($username){
		if (strlen($username) < 3) return false;
		if (strlen($username) > 17) return false;
		if (!ctype_alnum($username)) return false;
		return true;
	}

	public function login($username,$password){
		if (!$this->isValidUsername($username)) return false;
		if (strlen($password) < 3) return false;

		$row = $this->get_user_hash($username);

		if($this->password_verify($password,$row['Password']) == 1){
        //echo " This is the password: $password this is the other: ".$row['Password'];
		    $_SESSION['loggedin'] = true;
		    $_SESSION['username'] = $row['UserName'];
		    $_SESSION['ID'] = $row['ID'];
        $_SESSION['AccountType'] = $row['AccountType'];
		    return true;
		}
	}

	public function logout(){
		session_destroy();
	}

	public function is_logged_in(){
		if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true){
			return true;
		}
	}

}


?>
