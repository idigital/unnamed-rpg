<?php

define ('LOGIN', 1);
define ('FORCE_PHASE', true);

require_once ('includes/notextinc.php');
$ext_title = "Invetory";
$ext_css[] = "items.css";
include_once ('includes/header.php');

include ('fight_scripts/stat_bar.php');

echo "<h1>Your items</h1>\n";

echo "<p style=\"text-align: center;\"><input type=\"button\" value=\"Click here to return to the map\" onclick=\"window.location = 'map.php'\" /></p>\n";

$Items = $Character->getInventory()->getItems();

if ($Items->count()) {
	echo "<table style=\"width: 100%;\">\n";
	echo "<tr><th>Name</th><th>Quantity</th><th>Type</th><th>Actions</th></tr>\n";
	foreach ($Items as $Il) {
		echo "<tr>";
		echo "<td>". ucfirst ($Il['Item']->getName ()) ."</td>";
		echo "<td>".$Il['qty']."</td>";
		echo "<td>".$Il['Item']->getDetail ('type')."</td>";
		echo "</tr>\n";
	}
	echo "</table>\n";
} else {
	echo "<p>You don't have any items to use.</p>\n";
}

echo "<p style=\"text-align: center;\"><input type=\"button\" value=\"Click here to return to the map\" onclick=\"window.location = 'map.php'\" /></p>\n";

include_once ('includes/footer.php');
?>