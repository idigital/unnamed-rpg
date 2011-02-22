<?php

/**
* Draws the map which is loaded with javascript. Also handles movement.
*/

define ('LOGIN', true);
require_once ('includes/notextinc.php');

// Are we moving?
$old_x = $User->getMapData()->getX();
$old_y = $User->getMapData()->getY();
// Sort out user directions
if ($_POST['move'] == "west") {
	// work out new location
	$new_x = $old_x - 1;
	$new_y = $old_y;
	$moved = 1;
} elseif ($_POST['move'] == "east") {
	$new_x = $old_x + 1;
	$new_y = $old_y;
	$moved = 1;
} elseif ($_POST['move'] == "north") {
	$new_x = $old_x;
	$new_y = $old_y - 1;
	$moved = 1;
} elseif ($_POST['move'] == "south") {
	$new_x = $old_x;
	$new_y = $old_y + 1;
	$moved = 1;
} elseif ($_POST['move'] == "nw") {
	$new_x = $old_x - 1;
	$new_y = $old_y - 1;
	$moved = 1;
} elseif ($_POST['move'] == "ne") {
	$new_x = $old_x + 1;
	$new_y = $old_y - 1;
	$moved = 1;
} elseif ($_POST['move'] == "sw") {
	$new_x = $old_x - 1;
	$new_y = $old_y + 1;
	$moved = 1;
} elseif ($_POST['move'] == "se") {
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

?>