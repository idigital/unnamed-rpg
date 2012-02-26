<?php

/**
* Returns JSON. Used to trigger an action on an item, pass with $_GET['item_id']).
*
* Also needs and $_GET['action'], and $_GET['params'].
*/

define ('LOGIN', 1);
// doesn't need to be forced to a phase, since items can be used pretty much anywhere.if an
// item can only be used in a certain phase (ie, in a fight), then check it within the code.

require_once ('../includes/notextinc.php');
header('Content-type: application/json');

$return = array ();
$return['status'] = "success"; // optimism

// make sure they gave us a good item ID.
if (!is_numeric ($_GET['item_id'])) {
	$return['status'] = "failure";
	$return['message'] = "No valid item ID given.";
	
	echo json_encode ($return);
	exit;
} else {
	$Item = new Item ($_GET['item_id']);
	
	// make sure it exists.
	if (!$Item->exists()) {
		$return['status'] = "failure";
		$return['message'] = "No valid item ID given.";
		
		echo json_encode ($return);
		exit;		
	}
}

// Checkout the action now
if (!isset ($_GET['action'])) {
	$return['status'] = "failure";
	$return['message'] = "No valid action given.";
	
	echo json_encode ($return);
	exit;
} else {
	// jquery won't send an empty array for the get param, so normalise that here.
	$params = (empty ($_GET['params'])) ? array () : $_GET['params'];

	// Is this a valid action for this item?
	$action_success = false; // pessimism
	foreach ($Item->getActions($Character) as $actions) {
		// we only care about this action, so skip ahead till we find it...
		if ($actions['action'] === $_GET['action']) {
			// have we been given all the required parameters for this action?
			if (array_diff (array_keys ($actions['params']), array_keys ($params)) !== array ()) {
				// we don't have all the same keys in our params, and since they're all required we won't
				// be able to equip this item.
				$action_success = false;
			} else {
				$action_success = true;
			}
			
			break;
		}
	}
	
	if ($action_success == false) {
		$return['status'] = "failure";
		$return['message'] = "No valid action given, or parameters were incorrect.";
		
		echo json_encode ($return);
		exit;
	}
}

// now we have a real item, a real action, and the correct parameters for it. Try out the action!
$return['use_status'] = ($Item->doAction ($_GET['action'], $params, $Character)) ? "success" : "failure";

if ($return['use_status'] == "success" && $_GET['action'] == "Drink" && $Item->getDetail ('type') == 'Healing Potion') {
	$return['action'] = "heal";
	
	// the action was to drink a healing potion, so they probably increased in health. What's their health now?
	$return['current_health'] = $Character->getHealth();
	// what percent health is that?
	$return['current_health_percent'] = $Character->getPercentHealth();
} elseif ($return['use_status'] == "success" && $_GET['action'] == "Destroy") {
	$return['action'] = "destroy";
} elseif ($return['use_status'] == "success" && $_GET['action'] == "Equip") {
	$return['action'] = "equip";
}

echo json_encode ($return);

?>