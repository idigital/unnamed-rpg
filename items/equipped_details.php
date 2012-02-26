<?php

/**
* Script, usually called by AJAX, which returns a JSON string, giving details about a particular
* item that's equipped on the $_GET['position'].
*
* Just has to return the name and description of the item. If there's no item there, then item_found
* will be false.
*/

define ('LOGIN', 1);
require_once ('../includes/notextinc.php');
header('Content-type: application/json');

$return = array ();
$return['status'] = "success";

if (empty ($_GET['position'])) {
	$return['status'] = "failure";
	$return['status_message'] = "Missing position parameter.";
} else {
	$return['position'] = $_GET['position'];

	$Item = $Character->getEquippedItem ($_GET['position']);

	if ($Item) {
		$return['item_found'] = true;
		
		$return['item']['id'] = $Item->getId();
		$return['item']['name'] = $Item->getName();
		$return['item']['description'] = "<p>You're wielding this item.</p>";
	} else {
		$return['item_found'] = false;
	}
}

echo json_encode ($return);

?>