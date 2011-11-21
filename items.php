<?php

define ('LOGIN', 1);
define ('FORCE_PHASE', true);

require_once ('includes/notextinc.php');
$ext_title = "Inventory";
$ext_css[] = "items.css";
$ext_js[] = relroot."/js/items.js";
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
	echo "<div id=\"invent\">\n";

	echo "<div id=\"item-details\">\n";
	echo "<h2 class=\"item-name\">Inventory</h2>\n";
	echo "<div class=\"item-description\"><p>Click on an item to see more data about it.</p></div>\n";
	echo "</div>\n";
	
	echo "<div id=\"items\"><ul id=\"items-list\">\n";
	foreach ($Items as $Il) {
		echo "<li class=\"item\" id=\"itemid".$Il['Item']->getId()."\">";
		echo ucfirst ($Il['Item']->getName ()) . " (x<strong>".$Il['qty']."</strong>/".$Il['Item']->getMaxQty().")";
		echo "</li>\n";
	}
	echo "</ul>\n";
	echo "<div class=\"clear\">&nbsp;</div>\n";
	echo "</div>\n";
	
	echo "<div class=\"clear\">&nbsp;</div>\n";
	
	echo "</div>\n";
} else {
	echo "<p>You don't have any items to use.</p>\n";
}

echo "<p style=\"text-align: center;\"><input type=\"button\" value=\"Click here to return to the map\" onclick=\"window.location = 'map.php'\" /></p>\n";

include_once ('includes/footer.php');
?>