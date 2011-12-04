<?php

/**
* Ajax script. Used to trigger an action on an item, pass with $_GET['item_id']).
*
* Also needs and $_GET['action'].
*/

define ('LOGIN', 1);
// doesn't need to be forced to a phase, since items can be used pretty much anywhere.
// if an item can only be used in a certain place, then check it within the code.

require_once ('../includes/notextinc.php');

if (!is_numeric ($_GET['item_id'])) exit; else $Item = new Item ($_GET['item_id']);
if (!isset ($_GET['action'])) exit;

$return = $Item->doAction ($_GET['action'], $Character);

echo json_encode ($return);

?>