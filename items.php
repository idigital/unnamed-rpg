<?php

define ('LOGIN', 1);
define ('FORCE_PHASE', true);

require_once ('includes/notextinc.php');
$ext_title = "Invetory";
$ext_css[] = "items.css";
include_once ('includes/header.php');

include ('fight_scripts/stat_bar.php');

echo "<h1>Your items</h1>\n";

echo "<div id=\"characters\">\n";
echo "<div class=\"player main\">\n";
echo "<div class=\"portrait\"><img src=\"".relroot."/images/fight/lupe_combat.gif\" /></div>\n";
echo "<p class=\"name\">".$User->getDetail ('username')."</p>\n";
// we still have $health_bar set up from the stat_bar we included!
echo "<p>Health: <strong class=\"char_health\">".$Character->getDetail ('remaining_hp')."</strong>/".$Character->getMaxHealth()." ".$health_bar."</p>\n";
echo "</div>\n";

echo "<div class=\"clear\">&nbsp;</div>\n";

$Items = $Character->getInventory()->getItems();

if ($Items->count()) {
	echo "<h2>Inventory</h2>\n";
	echo "<ul id=\"items\">\n";
	foreach ($Items as $Il) {
		echo "<li class=\"item\">";
		echo ucfirst ($Il['Item']->getName ());
		echo "</li>\n";
	}
	echo "</ul>\n";
	
	echo "<div class=\"clear\">&nbsp;</div>\n";
} else {
	echo "<p>You don't have any items to use.</p>\n";
}

echo "<p style=\"text-align: center;\"><input type=\"button\" value=\"Click here to return to the map\" onclick=\"window.location = 'map.php'\" /></p>\n";

include_once ('includes/footer.php');
?>