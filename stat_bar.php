<?php

/**
* Displays some of the character's data.
* 
* This is used in both the map and fight page, and are identical. Can only be included, not directly accessed.
*/

if (!defined ('LOGIN')) exit;

echo "<div id=\"character_data\">\n";

// work out the health bar here, to keep the line cleaner.
$health_bar = "<span class=\"stat_bar\">";
// what percent of health does the user have left?
$percent_health = ($Character->getDetail ('remaining_hp')/$Character->getMaxHealth()) * 100;
$health_bar .= "<span style=\"display: inline-block; width: ".$percent_health."px; background-color: rgb(0, 255, 0);\">&nbsp;</span>";
$health_bar .= "</span>\n";

$experience_bar = "<span class=\"stat_bar\">";
$percent_exp = $Character->getDetail ('experience') > 0 ? ($Character->getDetail ('experience')/$Character->nextLevelAt ()) * 100 : 0;
$experience_bar .= "<span style=\"display: inline-block; width: ".$percent_exp."px; background-color: rgb(255, 221, 0);\">&nbsp;</span>";
$experience_bar .= "</span>";

echo "<p>Name: <strong>".$User->getDetail ('username')."</strong> | Level: <strong>".$Character->getLevel ()."</strong> | Health: <strong>".$Character->getDetail ('remaining_hp')."</strong>/".$Character->getMaxHealth()." ".$health_bar."</p>\n";
echo "<p>Experience: <strong>".$Character->getDetail ('experience')."</strong> ".$experience_bar."</p>\n";
echo "</div>\n";

?>