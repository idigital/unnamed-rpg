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
$ext_title = "Fight!";
include_once ('includes/header.php');

$Mob = $Character->getFightData()->getMob();

include ('stat_bar.php');

echo "<div id=\"vs_pane\">\n";

echo "<div id=\"mob\">\n";
echo "<div class=\"portrait\"><img src=\"".relroot."/images/fight/mobs/400001_asnowimp.gif\" /></div>\n";
echo "<p class=\"name\">A snow imp</p>\n";
echo "<p>Health: <strong>10</strong>/10</p>\n";
echo "<p>Level: <strong>1</strong></p>\n";
echo "</div>\n";

echo "<div id=\"player\">\n";
echo "<div class=\"portrait\"><img src=\"".relroot."/images/fight/lupe_combat.gif\" /></div>\n";
echo "<p class=\"name\">".$User->getDetail ('username')."</p>\n";
// we still have $health_bar set up from the stat_bar we included!
echo "<p>Health: <strong>".$Character->getDetail ('remaining_hp')."</strong>/".$Character->getMaxHealth()." ".$health_bar."</p>\n";
echo "</div>\n";

echo "<div class=\"clear\">&nbsp;</div>\n";
echo "</div>\n";

echo "<div id=\"actions\">\n";
echo "<p><span id=\"act_attack\">Attack</span></p>\n";
echo "<p style=\"padding-top: 20px;\"><span id=\"act_flee\">Flee</span></p>\n";
echo "<p><span id=\"act_nothing\">Attack</span></p>\n";
echo "</div>\n";

include_once ('includes/footer.php');
?>