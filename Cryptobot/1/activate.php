<?php
require('includes/config.php');

//collect values from the url
$memberID = trim($_GET['x']);
$active = trim($_GET['y']);

//if id is number and the active token is not empty carry on
if(is_numeric($memberID) && !empty($active)){

	//update users record set the active column to Yes where the memberID and active value match the ones provided in the array
	$stmt = $db->prepare("UPDATE User SET Active = 'Yes' WHERE ID = :memberID AND Active = :active");
	$stmt->execute(array(
		':memberID' => $memberID,
		':active' => $active
	));
	//echo $stmt;
	//if the row was updated redirect the user
	if($stmt->rowCount() == 1){
		$stmt = $db->prepare("INSERT INTO `UserConfig`( `UserID`) VALUES (:memberID)");
		$stmt->execute(array(
			':memberID' => $memberID
		));
		//redirect to login page
		header('Location: login.php?action=active');
		exit;

	} else {
		echo "Your account could not be activated.";
	}

}
?>
