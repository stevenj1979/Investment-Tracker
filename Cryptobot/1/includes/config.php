<?php
ob_start();
session_start();

//set timezone
date_default_timezone_set('Asia/Dubai');
include_once ('/home/stevenj1979/SQLData.php');
//database credentials
$host = getHost();
$userName = getUserName();
$dbName = getDBName();
$pass = getDBPass();

//Echo "$host";

//application address
define('DIR','http://www.investment-tracker.net/Investment-Tracker/Cryptobot/1/');
define('SITEEMAIL','Alerts@investment-tracker.net');

try {

	//create PDO connection
	$db = new PDO("mysql:host=".$host.";charset=utf8mb4;dbname=".$dbName, $userName, $pass);
    //$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);//Suggested to uncomment on production websites
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);//Suggested to comment on production websites
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

} catch(PDOException $e) {
	//show error
    echo '<p class="bg-danger">'.$e->getMessage().'</p>';
    exit;
}

//include the user class, pass in the database connection
include($_SERVER['DOCUMENT_ROOT'].'/Investment-Tracker/Cryptobot/1/classes/user.php');
include($_SERVER['DOCUMENT_ROOT'].'/Investment-Tracker/Cryptobot/1/classes/phpmailer/mail.php');
$user = new User($db);
?>
