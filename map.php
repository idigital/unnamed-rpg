<?php

/**
* This is the page where the user moves around the world. The world is displayed in a grid of images
* representing what's in that space. The world should be pretty massive, and so we don't want to be
* showing the entire world to the player; only a small range of sight.
*/

define ('LOGIN', true);
require_once ('includes/notextinc.php');

$ext_title = "The World";
$ext_css[] = "map.css";
$ext_js[] = relroot."/js/map.js";
require_once ('includes/header.php');

echo "<div id=\"map\"><div class=\"left\">\n<div id=\"map_table\">\n";
echo "</div>\n</div>\n";

echo "<div id=\"mapnav\">\n";
echo "</div>\n";

require_once ('includes/footer.php');
?>