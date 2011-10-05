<?php

/**
* This page is used to show the user what happened at the end of their fight.
*
* Handles player win, player loss, and fleeing. Can only get to this page if one of those conditions are true.
*/

define ('LOGIN', true);
define ('FORCE_PHASE', true);
require_once ('../includes/notextinc.php');

if (isset ($_POST['back_to_map'])) {
	// they can only be on this page if they're in a position to return to the page, fyi.
	$Character->getMapData()->setDetail ('phase', 'map');
	header ('Location: '.relroot.'/map.php');
}

$ext_css[] = "fight.css";

$Fight = $Character->getFightData();
$Mob = $Fight->getMob();

// what condition are we in?
if ($Fight->getStage() == "player flee success") {
	$ext_title = "You fled!";
	include_once ('../includes/header.php');

	include ('stat_bar.php');
	
	echo "<div id=\"aftermath_flee\">\n";
	
	echo "<div class=\"portrait\"><img src=\"".relroot."/images/fight/mobs/".$Mob->getDetail ('image')."\" /></div>\n";
	echo "<p>You escaped from ".$Mob->getName (true)."!</p>\n";
	echo "<p>".ucfirst ($Mob->getName (true))." at you as you flee!</p>\n";
	
	echo "<form method=\"post\" action=\"\">\n";
	echo "<p><input type=\"submit\" value=\"Click here to return to the map\" name=\"back_to_map\" /></p>\n";
	echo "</form>\n";
	
	echo "</div>\n";
} elseif ($Fight->getStage() == "player win") {
	$ext_title = "You won!";
	include_once ('../includes/header.php');

	include ('stat_bar.php');
	
	echo "<div id=\"aftermath_win\">\n";
	
	echo "<p>You defeated ".$Mob->getName (true)."!</p>\n";
	echo "<div class=\"portrait\"><img src=\"".relroot."/images/fight/lupe_win.gif\" /></div>\n";
	echo "<p>".ucfirst ($Mob->getName (true))." cries in pain as it falls to the ground, defeated!</p>\n";
	echo "<p>You gain <b>73 experience points</b> for defeating this creature!</p>\n";
	
	echo "<form method=\"post\" action=\"\">\n";
	echo "<p><input type=\"submit\" value=\"Click here to return to the map\" name=\"back_to_map\" /></p>\n";
	echo "</form>\n";
	
	echo "</div>\n";
} elseif ($Fight->getStage() == "mob win") {
	$ext_title = "You passed out!";
	include_once ('../includes/header.php');

	include ('stat_bar.php');
	
	echo "<div id=\"aftermath_win\">\n";
	
	echo "<p>You were defeated by ".$Mob->getName (true)."!</p>\n";
	echo "<div class=\"portrait\"><img src=\"".relroot."/images/fight/lupe_lose.gif\" /> <img src=\"".relroot."/images/fight/mobs/".$Mob->getDetail ('image')."\" /></div>\n";
	echo "<p>".ucfirst ($Mob->getName (true))." roars with satisfaction as you fall to the ground, defeated!</p>\n";
	echo "<p>You lose 220 experience points for dying, and you have been sent back to Neopia City.</p>\n";
	
	echo "<form method=\"post\" action=\"\">\n";
	echo "<p><input type=\"submit\" value=\"Click here to return to Neopia City\" name=\"back_to_map\" /></p>\n";
	echo "</form>\n";
	
	echo "</div>\n";
}

include_once ('../includes/footer.php');
?>