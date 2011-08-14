<?php

/**
* Takes a coordinate from the edit canvas and stores it in the database.
*
* Returns json.
*
* @post x
* @post y
* @post map_id
* @post image The image to be stored at the coordinate
* @post locality The string shown when the user is at that coord
*/

define ('LOGIN', 1);

require_once ('../includes/notextinc.php');

$return = array ();

// needs to be an admin user...
if ($User->getDetail ('role') != 'admin') {
	$return['status'] = 401;
	echo json_encode ($return);
	exit;
}

if (!is_numeric ($_POST['map_id']) || !is_numeric ($_POST['x']) || !is_numeric ($_POST['y']) || empty ($_POST['image']) || empty ($_POST['locality'])) {
	$return['status'] = 400;
	echo json_encode ($return);
	exit;
}

$return['qry_result'] = (bool) $Database->query ("UPDATE `map` SET `image` = '".$Databse->escape ($_POST['image'])."', `locality` = '".$Database->escape ($_POST['locality'])."' WHERE `x_co` = ".(int) $_POST['x']." AND `y_co` = ".(int) $_POST['y']." AND `map_id` = ".$_POST['map_id']);
$return['status'] = 200;

echo json_encode ($return);

?>