<?php

/**
* This page is used to show the user what happened at the end of their fight.
*
* Handles player win, player loss, and fleeing. Can only get to this page if one of those conditions are true.
*/

define ('LOGIN', true);
define ('FORCE_PHASE', true);
require_once ('../includes/notextinc.php');

$ext_css[] = "fight.css";
$ext_title = "You fled!";
include_once ('../includes/header.php');

$Fight = $Character->getFightData();
$Mob = $Fight->getMob();

// what condition are we in?
if ($Fight->getStage() == "player flee success") {
	include ('stat_bar.php');
	
	echo "<div class=\"portrait\"><img src=\"".relroot."/images/fight/mobs/".$Mob->getDetail ('image')."\" /></div>\n";
	echo "<p>You escaped from ".$Mob->getName (true)."</p>\n";
	echo "<p>".ucfirst ($Mob->getName (true))." at you as you flee!</p>\n";
	
	echo "<p><input type=\"submit\" value=\"Click here to return to the map\" /></p>\n";
}

include_once ('../includes/footer.php');
?>