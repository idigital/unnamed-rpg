<?php

define ('LOGIN', 1);

require_once ('../includes/notextinc.php');

if (!is_numeric ($_GET['item_id'])) exit; else $Item = new Item ($_GET['item_id']);
if (!isset ($_GET['action'])) exit;

$Fight = $Character->getFightData();
$Mob = $Fight->getMob();

// how much health do we have now? we should store this, before the we do the action, since it's the only
// way to tell how much was healed by any potions.
$pre_health = $Character->getHealth();

$turn_id = FightMessage::createTurnId ($Fight->getId());

// the character needs to be in a fight to be here
if ($Character->getMapData()->getDetail ('phase') != "fight") {
	$r['status'] = "bad phase";
} else {
	$r['status'] = "success";
	
	$r['item_use'] = $Item->doAction ($_GET['action'], $Character);
	
	if ($r['item_use'] == true) {
		if ($_GET['action'] == "Drink" && $Item->getDetail ('type') == 'Healing Potion') {
			$r['action'] = "heal";
			
			// the action was to drink a healing potion, so they probably increased in health. What's their health now?
			$r['current_health'] = $Character->getHealth();
			$healed = $Character->getHealth() - $pre_health;
			
			$Message = FightMessage::addMessage ($turn_id, 10, array ($healed));
			$r['message'][] = $Message->getMessageArray ();
		}
	}
	
	if ($Fight->getDetail ('mob_health') > 0) include ('mob_action.php');

	$r['fight_stage'] = $Fight->getStage();
}

echo json_encode ($r);

?>