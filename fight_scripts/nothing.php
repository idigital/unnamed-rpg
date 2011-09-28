<?php

/**
* Handles reaction to "do nothing." This is largely just the same as 'trigger mob action only'.
*
* Returns json.
*/

define ('LOGIN', true);
require_once ('../includes/notextinc.php');

$Fight = $Character->getFightData();
$Mob = $Fight->getMob();

// the reponse will be stored in here, and then json'd and echoed later
$r = array ();

// we'll be setting messages, so set up a new order.
$fight_message_order = 1;

$r['fight_stage'] = null;

$Message = FightMessage::addMessage ($turn_id, 1);
$r['message'][] = $Message->getMessageArray ();

$Fight->doNothing ();

include ('mob_action.php');

echo json_encode ($r);

?>