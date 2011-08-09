<?php

/**
* Gives a UI for the creation and editing of maps.
*
* My vision for this page is to have a toolbox on the right to select a map type, and then you can
* just "paint" it onto the map. There should be a block fill tool, and a pencil tool, for speed.
*/

define ('LOGIN', 1);

require_once ('../includes/notextinc.php');

// needs to be an admin user...
if ($User->getDetail ('role') != 'admin') minipage ("Admin Only", "<p>You're not welcome here.</p>\n", "../");

$ext_title = "Map Editor - Admin";
$ext_css[] = "admin.map.edit.css";
$ext_js[] = relroot."/js/admin.map.edit.js";
include_once ('../includes/header.php');

echo "<div id=\"toolbox\">\n";
echo "<h1>Tools</h1>\n";

echo "<h2>Select block</h2>\n";

// go through each of the images that are in the images/map_images/ folder, and output them to be selected
$map_images = scandir ('../images/map_images/');

echo "<ul id=\"blocks\">\n";
foreach ($map_images as $image) {
	if ($image == '.' || $image == '..') continue;
	// Valid blocks are all 40x40px
	$image_sizes = getimagesize ('../images/map_images/'.$image);
	if ($image_sizes[0] != 40 || $image_sizes[1] != 40) continue;
	
	echo "<li><img src=\"".relroot."/images/map_images/".$image."\"/></li>\n";
}
echo "</ul>\n";

echo "</div>\n";

include_once ('../includes/footer.php');
?>