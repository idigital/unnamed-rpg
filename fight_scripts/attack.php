<?php/*** Lets a user attack, and handles the reaction to that.** Returns a json script, handled by the javascript on fight.php.*/define ('LOGIN', true);require_once ('../includes/notextinc.php');$Fight = $Character->getFightData();$Mob = $Fight->getMob();$turn_id = FightMessage::createTurnId ();// the reponse will be stored in here, and then json'd and echoed later$r = array ();$r['fight_stage'] = null;// the character needs to be in a fight to be hereif ($Character->getMapData()->getDetail ('phase') != "fight") {	$r['status'] = "bad phase";} else {	$r['status'] = "success";	$r['char']['attack'] = $Fight->attack ();	if ($r['char']['attack']['hit']) {		$Message = FightMessage::addMessage ($turn_id, 3, array ($Mob->getId(), $r['char']['attack']['hit_amount']));		$r['message'][] = $Message->getMessageArray ();				// is the mob guy dead? don't bother giving him a turn if so		if ($Fight->getDetail ('mob_health') <= 0) {			$Message = FightMessage::addMessage ($turn_id, 6);			$r['message'][] = $Message->getMessageArray ();		}	} else {		$Message = FightMessage::addMessage ($turn_id, 7);		$r['message'][] = $Message->getMessageArray ();	}}if ($Fight->getDetail ('mob_health') > 0) include ('mob_action.php');echo json_encode ($r);?>