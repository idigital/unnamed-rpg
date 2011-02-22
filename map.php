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

echo "<div id=\"map\"><div class=\"left\">\n<table cellpadding=\"0\" cellspacing=\"0\" id=\"map_table\">\n";
echo "</table></div>\n</div>\n";

echo "<div id=\"mapnav\">\n";
echo "<div class=\"right\"><p><img src=\"".relroot."/images/map_images/compass.png\" usemap=\"#navigation_image\" alt=\"Compass\"/></p></div>";
echo "<map name=\"navigation_image\" id=\"navigation_image\">\n<area shape=\"poly\" coords=\"47,47,81,89,87,87,89,79\" onclick=\"move('nw')\" alt=\"North west\"/>\n<area shape=\"poly\" coords=\"1,99,87,109,84,99,83,90\" onclick=\"move('left')\" alt=\"West\" />\n<area shape=\"poly\" coords=\"49,148,95,113,88,111,81,109\" onclick=\"move('sw')\" alt=\"South west\" />\n<area shape=\"poly\" coords=\"100,195,108,111,91,117\" onclick=\"move('down')\" alt=\"South\" />\n<area shape=\"poly\" coords=\"149,149,113,103,108,116\" onclick=\"move('se')\" alt=\"South east\" />\n<area shape=\"poly\" coords=\"195,99,118,107,113,99,111,87\" onclick=\"move('right')\" alt=\"East\" />\n<area shape=\"poly\" coords=\"150,46,103,83,115,89\" onclick=\"move('ne')\" alt=\"North east\" />\n<area shape=\"poly\" coords=\"99,1,89,83,108,82\" onclick=\"move('up')\" alt=\"North\" />\n<area shape=\"poly\" coords=\"47,46,83,91,89,79\" onclick=\"move('nw')\" alt=\"North west\" />\n</map>\n";
echo "</div>\n";

require_once ('includes/footer.php');
?>