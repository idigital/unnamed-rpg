<?php

/**
* This page is used to show the user what happened at the end of their fight.
*
* Handles player win, player loss, and fleeing. Can only get to this page if one of those conditions are true.
*/

define ('LOGIN', true);
define ('FORCE_PHASE', true);
require_once ('../includes/notextinc.php');

$Fight = $Character->getFightData();
$Mob = $Fight->getMob();

if (isset ($_POST['back_to_map'])) {
	// if the mob won, they'll need respawning
	if ($Fight->getStage() == "mob win") {
		// remove the XP we said we were going to remove too
		$Character->setDetail ('experience', $Character->getDetail ('experience') - $Mob->getXPLoss ($Character));
		$Character->respawn ();
	} elseif ($Fight->getStage() == "player win") {
		// give them their xp
		$Character->setDetail ('experience', $Character->getDetail ('experience') + $Mob->getXPGain ($Character));
	}
	
	// they can only be on this page if they're in a position to return to the map, fyi.
	$Character->getMapData()->setDetail ('phase', 'map');
	header ('Location: '.relroot.'/map.php');
}

$ext_css[] = "fight.css";
$ext_js[] = relroot."/js/fight.aftermath.js";

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
	
	$xp_gain = $Mob->getXPGain ($Character);
	$new_xp = $Character->getDetail ('experience') + $xp_gain;
	
	// will this XP gain push us up to another level?
	if ($Character->nextLevelIn() <= $xp_gain) {
		echo "<script type=\"text/javascript\">";
		// yes, we're gaining a new level. the javascript to update the UI needs to know what percentage we're into the new level.

		// here's the data on the new level!
		$next_Level = new BaseStats ($Character->getLevel ()+1);
		
		// how much XP will the player be into this new level?
		$next_require_xp = $next_Level->getDetail ('experience_required');
		$into_level = $new_xp - $next_require_xp;

		$new_percent_exp = ($into_level/$next_Level->xpRequiredToDing()) * 100;
		echo "levelUp (".($next_Level->getLevel()).", ".$new_xp.", ".$new_percent_exp.");";
		echo "</script>\n";
	} else {
		// we don't reach a new level, so just fill up the bar easily
		
		// animate the experience bar filling.
		
		// how much are we into this level? (we need this to create a percentage)
		$into_level = $new_xp - $Character->getBaseStats()->getDetail ('experience_required');
		
		$new_percent_exp = ($into_level > 0) ? ($into_level/$Character->getBaseStats()->xpRequiredToDing()) * 100 : 0;
		echo "<script type=\"text/javascript\">changeXP (".$new_xp.", ".$new_percent_exp.");</script>\n";
	}
	
	echo "<p>You defeated ".$Mob->getName (true)."!</p>\n";
	echo "<div class=\"portrait\"><img src=\"".relroot."/images/fight/lupe_win.gif\" /></div>\n";
	echo "<p>".ucfirst ($Mob->getName (true))." cries in pain as it falls to the ground, defeated!</p>\n";
	
	// what loot did we get?
	$loot = $Fight->discoverLoot();

	foreach ($loot as $Item) {
		echo "<p>".ucfirst ($Mob->getName (true))." was carrying <strong>".$Item->getName (true)."</strong>!</p>\n";
	}
	
	echo "<p>You gain <b>".$Mob->getXPGain ($Character)." experience points</b> for defeating this creature!</p>\n";
	
	echo "<form method=\"post\" action=\"\">\n";
	echo "<p><input type=\"submit\" value=\"Click here to return to the map\" name=\"back_to_map\" /></p>\n";
	echo "</form>\n";
	
	echo "</div>\n";
} elseif ($Fight->getStage() == "mob win") {
	$ext_title = "You passed out!";
	include_once ('../includes/header.php');

	include ('stat_bar.php');
	
	echo "<div id=\"aftermath_win\">\n";
	
	$xp_loss = $Mob->getXPLoss ($Character);
	$new_xp = $Character->getDetail ('experience') - $xp_loss;
	// animate the experience bar falling.
	
	// how much are we into this level? (we need this to create a percentage)
	$into_level = $new_xp - $Character->getBaseStats()->getDetail ('experience_required');
	
	$new_percent_exp = ($into_level > 0) ? ($into_level/$Character->getBaseStats()->xpRequiredToDing()) * 100 : 0;
	echo "<script type=\"text/javascript\">changeXP (".$new_xp.", ".$new_percent_exp.");</script>\n";
	
	echo "<p>You were defeated by ".$Mob->getName (true)."!</p>\n";
	echo "<div class=\"portrait\"><img src=\"".relroot."/images/fight/lupe_lose.gif\" /> <img src=\"".relroot."/images/fight/mobs/".$Mob->getDetail ('image')."\" /></div>\n";
	echo "<p>".ucfirst ($Mob->getName (true))." roars with satisfaction as you fall to the ground, defeated!</p>\n";
	echo "<p>You lose <b>".$xp_loss." experience points</b> for dying, and you have been sent back to Neopia City.</p>\n";
	
	echo "<form method=\"post\" action=\"\">\n";
	echo "<p><input type=\"submit\" value=\"Click here to return to Neopia City\" name=\"back_to_map\" /></p>\n";
	echo "</form>\n";
	
	echo "</div>\n";
}

include_once ('../includes/footer.php');
?>