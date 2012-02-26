<?php

/**
* JSON output. Lets a player use an item during a fight, and then sends back the
* outcome of using that item. The mob also has their turn, since using an item
* takes up a turn.
*
* A lot of this script is taken from item/use-item.php, and the two really should
* be merged at some point. They aren't at the moment because of the added responsibility
* to this script to handle a fight turn also.
*/

define ('LOGIN', 1);

require_once ('../includes/notextinc.php');

$r = array ();
$r['status'] = "success"; // optimism

// make sure they gave us a good item ID.
if (!is_numeric ($_GET['item_id'])) {
	$r['status'] = "failure";
	$r['message'] = "No valid item ID given.";
	
	echo json_encode ($r);
	exit;
} else {
	$Item = new Item ($_GET['item_id']);
	
	// make sure it exists.
	if (!$Item->exists()) {
		$r['status'] = "failure";
		$r['message'] = "No valid item ID given.";
		
		echo json_encode ($r);
		exit;		
	}
}

// Checkout the action now
if (!isset ($_GET['action'])) {
	$r['status'] = "failure";
	$r['message'] = "No valid action given.";
	
	echo json_encode ($r);
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
		$r['status'] = "failure";
		$r['message'] = "No valid action given, or parameters were incorrect.";
		
		echo json_encode ($r);
		exit;
	}
}

$Fight = $Character->getFightData();
$Mob = $Fight->getMob();
$turn_id = FightMessage::createTurnId ($Fight->getId());

// how much health do we have now? we should store this, before the we do the action, since it's the only
// way to tell how much was healed by any potions. (Although potions do a defined 10 damage, for instance,
// it could be the case that they only heal five, because the player is already close their max health.)
$pre_health = $Character->getHealth();

// the character needs to be in a fight to be here
if ($Character->getMapData()->getDetail ('phase') != "fight") {
	$r['status'] = "failure";
	$r['status_message'] = "Player must be in a fight to view this page.";
} else {
	$r['status'] = "success";
	
	$r['use_status'] = ($Item->doAction ($_GET['action'], $params, $Character)) ? "success" : "failure";
	
	if ($r['use_status'] == "success") {
		if ($r['use_status'] == "success" && $_GET['action'] == "Drink" && $Item->getDetail ('type') == 'Healing Potion') {
			$r['action'] = "heal";
			
			// the action was to drink a healing potion, so they probably increased in health. What's their health now?
			$r['current_health'] = $Character->getHealth();
			$healed = $Character->getHealth() - $pre_health;
			
			$Message = FightMessage::addMessage ($turn_id, 10, array ($healed));
			$r['message'][] = $Message->getMessageArray ();
		}
	}
	
	// (no other items can be used in combat yet.)
	
	if ($Fight->getDetail ('mob_health') > 0) include ('mob_action.php');

	$r['fight_stage'] = $Fight->getStage();
}
echo json_encode ($r);

?>