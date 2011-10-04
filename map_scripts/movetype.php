<?php

/**
* Changes the movement type for the longed in user.
*
* @param s What it should be set to
*/

define ('LOGIN', true);
require_once ('../includes/notextinc.php');

// assume the worst.
$type = "invalid";

// format the 's' we've been given.
$s = strtolower ($_POST['s']);
switch ($s) {
	case 'hunt':
	case 'hunting':
		$type = "hunt";
		break;
	case 'normal':
		$type = "normal";
		break;
	case 'sneak':
	case 'sneaking':
		$type = "sneak";
}

if ($type != "invalid") $Character->getMapData()->setDetail ('move_type', $type);

?>