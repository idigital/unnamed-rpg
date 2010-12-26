<?php

/**
* This is the page where the user moves around the world. The world is displayed in a grid of images
* representing what's in that space. The world should be pretty massive, and so we don't want to be
* showing the entire world to the player; only a small range of sight.
*/

define ('LOGIN', true);
require_once ('includes/notextinc.php');

// Are we moving?
$old_x = $User->getMapData()->getX();
$old_y = $User->getMapData()->getY();
// Sort out user directions
if (isset ($_GET['left'])) {
	// work out new location
	$new_x = $old_x - 1;
	$new_y = $old_y;
	$moved = 1;
} elseif (isset ($_GET['right'])) {
	$new_x = $old_x + 1;
	$new_y = $old_y;
	$moved = 1;
} elseif (isset ($_GET['up'])) {
	$new_x = $old_x;
	$new_y = $old_y - 1;
	$moved = 1;
} elseif (isset ($_GET['down'])) {
	$new_x = $old_x;
	$new_y = $old_y + 1;
	$moved = 1;
} elseif (isset ($_GET['nw'])) {
	$new_x = $old_x - 1;
	$new_y = $old_y - 1;
	$moved = 1;
} elseif (isset ($_GET['ne'])) {
	$new_x = $old_x + 1;
	$new_y = $old_y - 1;
	$moved = 1;
} elseif (isset ($_GET['sw'])) {
	$new_x = $old_x - 1;
	$new_y = $old_y + 1;
	$moved = 1;
} elseif (isset ($_GET['se'])) {
	$new_x = $old_x + 1;
	$new_y = $old_y + 1;
	$moved = 1;
}
// if user has moved
if ($moved) {
	// get map data for new location
	$new_location = mysql_fetch_assoc ($Database->query ("SELECT * FROM `map` WHERE `x_co` = ".$new_x." AND `y_co` = ".$new_y." AND `map_id` = ".$User->getMapData()->getId()));
	$location_type = $new_location['type'];

	// check if new location is passable
	if ($location_type != 5) {
		// move to new location
		$User->getMapData()->setDetail ('x_co', $new_x);
		$User->getMapData()->setDetail ('y_co', $new_y);
	} else {
		// tell user it is not passable
		$status[] = "<p class=\"error\">You can't cross this area.</p>\n";
	}
}

$ext_title = "The World";
require_once ('includes/header.php');
echo "<h2>".$ext_title."</h2>\n";

echo "<div id=\"mapnav\">\n";
echo "<div class=\"right\"><p><img src=\"".relroot."/images/map_images/compass.png\" usemap=\"#navigation_image\" alt=\"Compass\"/></p></div>";
echo "<map name=\"navigation_image\" id=\"navigation_image\">\n<area shape=\"poly\" coords=\"47,47,81,89,87,87,89,79\" href=\"?nw\" alt=\"North west\"/>\n<area shape=\"poly\" coords=\"1,99,87,109,84,99,83,90\" href=\"?left\" alt=\"West\" />\n<area shape=\"poly\" coords=\"49,148,95,113,88,111,81,109\" href=\"?sw\" alt=\"South west\" />\n<area shape=\"poly\" coords=\"100,195,108,111,91,117\" href=\"?down\" alt=\"South\" />\n<area shape=\"poly\" coords=\"149,149,113,103,108,116\" href=\"?se\" alt=\"South east\" />\n<area shape=\"poly\" coords=\"195,99,118,107,113,99,111,87\" href=\"?right\" alt=\"East\" />\n<area shape=\"poly\" coords=\"150,46,103,83,115,89\" href=\"?ne\" alt=\"North east\" />\n<area shape=\"poly\" coords=\"99,1,89,83,108,82\" href=\"?up\" alt=\"North\" />\n<area shape=\"poly\" coords=\"47,46,83,91,89,79\" href=\"?nw\" alt=\"North west\" />\n</map>\n";
echo "</div>\n";

// what range do we want to be showing the user? this is also the width and height of the map. Because
// of that, it makes sense for it to be odd so that there's a centre point to the map, which we can put
// the user in.
# for now, we can just set it in here. later I'll think about adding a better place to store this.
$map_los = 5;

// now we can work out what coordinates the user can see
$x_smallest = $User->getMapData()->getX() - $map_los;
$x_largest = $User->getMapData()->getX() + $map_los;
$y_smallest = $User->getMapData()->getY() - $map_los;
$y_largest = $User->getMapData()->getY() + $map_los;

// Make sure that the max/min coordinates don't go other the side of the possible map squares...
// what's the smallest x?
$map_x_min = $Database->getSingleValue ("SELECT `x_co` FROM `map` WHERE `map_id` = ".$User->getMapData()->getId()." ORDER BY `x_co` ASC LIMIT 1");
$map_y_min = $Database->getSingleValue ("SELECT `y_co` FROM `map` WHERE `map_id` = ".$User->getMapData()->getId()." ORDER BY `y_co` ASC LIMIT 1");
$map_x_max = $Database->getSingleValue ("SELECT `x_co` FROM `map` WHERE `map_id` = ".$User->getMapData()->getId()." ORDER BY `x_co` DESC LIMIT 1");
$map_y_max = $Database->getSingleValue ("SELECT `y_co` FROM `map` WHERE `map_id` = ".$User->getMapData()->getId()." ORDER BY `y_co` DESC LIMIT 1");

// check left boundary
if ($x_smallest < $map_x_min) {
	$x_smallest = $map_x_min;
	$x_largest = $map_x_min + ($map_los*2);
}

// check right boundary
if ($x_largest > $map_x_max) {
	$x_smallest = $map_x_max - ($map_los*2);
	$x_largest = $map_x_max;
}

// check top boundary
if ($y_smallest < $map_y_min) {
	$y_smallest = $map_y_min;
	$y_largest = $map_y_min + ($map_los*2);
}

// check bottom boundary
if ($y_largest > $map_y_max) {
	$y_smallest = $map_y_max;
	$y_largest = $map_y_max + ($map_los*2);
}

// nab all the coords that we've decided we can show on the map that the user is on
$qry_mapgrid = $Database->query ("SELECT * FROM `map` WHERE `x_co` >= ".$x_smallest." AND `x_co` <= ".$x_largest." AND `y_co` >= ".$y_smallest." AND `y_co` <= ".$y_largest." AND `map_id` = ".$User->getMapData()->getId());

echo "<div id=\"map\"><div class=\"left\">\n<table cellpadding=\"0\" cellspacing=\"0\">\n";
echo "\t<tr>\n";

// draw map
$in_row = 0; $rows = 0;
while ($map_array = mysql_fetch_array ($qry_mapgrid)) {
	// get data for image
	if ($User->getMapData()->getX() == $map_array['x_co'] AND $User->getMapData()->getY() == $map_array['y_co']) {
		$image = "person";
	} else {
		$image = $map_array['image'];
	}

	// output image
	echo "\t\t<td style=\"margin:0; background-color: #00FF01; border-style:none;\"><img src=\"".relroot."/images/map_images/".$image.".jpg\" ";
	//  if this is a dev-serv, then show the map's coordinants
	if (STATUS != LIVE) echo " title=\"x is ".$map_array['x_co'].", y is ".$map_array['y_co']."\" ";
	echo "alt=\"Map square\" style=\" width: 40px; height: 40px; \" /></td>\n";

	// add one to count
	$in_row++;

	// check how many in row
	if ($in_row > $map_los*2) {
		// close row, add one to row count
		echo "\t</tr>\n";
		$rows++;

		// if it isn't the last row, start a new row, and zero count
		echo "\t<tr>\n";
		$in_row = 0;
	}
}

echo "</table></div>\n</div>\n";

require_once ('includes/footer.php');
?>