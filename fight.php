<?php

/**
* The UI for the fight actions.
*
* It's safe to assume that if they're on this page, they're in a fight.
*/

define ('LOGIN', true);
define ('FORCE_PHASE', true);
require_once ('includes/notextinc.php');
$ext_css[] = "fight.css";
$ext_js[] = relroot."/js/fight.js";
$ext_title = "Fight!";
include_once ('includes/header.php');

$Fight = $Character->getFightData();
$Mob = $Fight->getMob();

// let the browser know about some of the important stats they should keep track of
echo "<script type=\"text/javascript\">\n";
echo "game_state['char'] = {hp: ".$Character->getDetail ('remaining_hp').", max_hp: ".$Character->getMaxHealth()."};";
echo "game_state['mob'] = {hp: ".$Fight->getDetail ('mob_health').", max_hp: ".$Mob->getDetail ('hp')."};";
echo "</script>\n";

include ('stat_bar.php');

echo "<div id=\"vs_pane\">\n";

echo "<div id=\"mob\">\n";
echo "<div class=\"portrait\"><img src=\"".relroot."/images/fight/mobs/".$Mob->getDetail ('image')."\" /></div>\n";
echo "<p class=\"name\">".ucfirst ($Mob->getName (true))."</p>\n";

// work out the health bar here, to keep the line cleaner.
$mob_health_bar = "<span class=\"stat_bar\">";
// what percent of health does the user have left?
$mob_percent_health = ($Fight->getDetail ('mob_health')/$Mob->getDetail ('hp')) * 100;
$mob_health_bar .= "<span class=\"mob_health_bar\" style=\"display: inline-block; width: ".$mob_percent_health."px; background-color: rgb(0, 255, 0);\">&nbsp;</span>";
$mob_health_bar .= "</span>\n";
echo "<p>Health: <strong class=\"mob_health\">".$Fight->getDetail ('mob_health')."</strong>/".$Mob->getDetail ('hp')." ".$mob_health_bar."</p>\n";

echo "<p>Level: <strong>".$Mob->getDetail ('level')."</strong></p>\n";
echo "</div>\n";

echo "<div id=\"player\">\n";
echo "<div class=\"portrait\"><img src=\"".relroot."/images/fight/lupe_combat.gif\" /></div>\n";
echo "<p class=\"name\">".$User->getDetail ('username')."</p>\n";
// we still have $health_bar set up from the stat_bar we included!
echo "<p>Health: <strong class=\"char_health\">".$Character->getDetail ('remaining_hp')."</strong>/".$Character->getMaxHealth()." ".$health_bar."</p>\n";
echo "</div>\n";

echo "<div class=\"clear\">&nbsp;</div>\n";
echo "</div>\n";

echo "<div id=\"round_feedback\">\n<ul></ul>\n</div>\n";
// are there any old messages, from this fight?
$messages = $Fight->getPreviousTurnMessages ();
// let javascript handle them. why have two sets of code to do the same thing? browsers are fast enough to rely on
// javascript these days.
echo "<script>\n$(function () {\n";
foreach ($messages as $Message) {
	echo "addHistory ('".addslashes ($Message->getString())."', '".$Message->getColour()."');\n";
}
echo "});\n</script>\n";

echo "<div id=\"actions\">\n";
// depending on what state the game is, depends on what actions are availiable.
// player dead?
if ($Fight->getStage() == "mob win") {
	echo "<p><a href=\"".relroot."/fight_scripts/aftermath.php\"><strong>Click here</strong> to see the aftermath...</a></p>";
} elseif ($Fight->getStage() == "player win") {
	echo "<p><a href=\"".relroot."/fight_scripts/aftermath.php\"><strong>Click here</strong> to see what you found!</a></p>";
} elseif ($Fight->getStage() == "player flee success") {
	echo "<p><a href=\"".relroot."/fight_scripts/aftermath.php\"><strong>Click here</strong> to see what happened...</a></p>";
} else {
	echo "<p><span id=\"act_attack\" class=\"link\">Attack</span></p>\n";
	echo "<p style=\"padding-top: 20px;\"><span id=\"act_flee\" class=\"link\">Flee</span></p>\n";
	echo "<p><span id=\"act_nothing\" class=\"link\">Do nothing</span></p>\n";
}
echo "</div>\n";

include_once ('includes/footer.php');
?>