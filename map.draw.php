<?php

/**
* Ouputs XML document which draws the map which is loaded with javascript. Also handles movement.
*
* Concept:
* Each row in the map database is a coordinate of the world.
*
* `type`:
*   1 - normal passable square
*   2 - forest area
*   4 - special square that can be entered. A URL to take the player to is in the map_special table
*   5 - impassable square
*
* It'll output a document like this
<?xml version="1.0" ?>
<root>
	<map_html>
		<!-- new map data that can just be injected overtop the current map -->
	</map_html>
	<navigation_data>
		<!-- html that's dropped overtop of the navigation section to the right of the map -->
	</navigation_data>
</root>
*/

define ('LOGIN', true);
require_once ('includes/notextinc.php');

header ('Content-Type: text/xml');

echo "<?xml version=\"1.0\" ?>\n";

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

echo "<root>\n";

// inside here needs to be HTML that looks like the map. HTML! Not XML nodes, so the HTML chars need to be escaped.
// to keep the code looking pretty, and not litering it with &gt;'s and &lt;'s, we'll put all the output that's supposed
// to be in this node into a variable, and then run it through and escape function later at the end.
$map_data_output = "";
echo "\t<map_data>\n";

// what range do we want to be showing the user? this is also the width and height of the map. Because
// of that, it makes sense for it to be odd so that there's a centre point to the map, which we can put
// the user in.
# for now, we can just set it in here. later I'll think about adding a better place to store this.
$map_los = 3;

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

$map_data_output = "<div>";

// draw map
$in_row = 0; $rows = 0;
// loop through all the coords that we've been given. this data will include the image to show.
while ($map_array = mysql_fetch_array ($qry_mapgrid)) {
	// if the user is on this square then we just want to show the user's avatar here. Otherwise just show the actual
	// image that belongs to the coord.
	if ($User->getMapData()->getX() == $map_array['x_co'] AND $User->getMapData()->getY() == $map_array['y_co']) {
		// this is the coord the user is on - this data will be helpful later on in the navigation_data
		$user_map_data = $map_array;
	
		$image = "lupe_".$map_array['image'];
	} else {
		$image = $map_array['image'];
	}

	$map_data_output .= "<span><img src=\"".relroot."/images/map_images/".$image."\" ";
	//  if this is a dev-serv then show the map's coordinants
	if (STATUS != "LIVE") $map_data_output .= " title=\"x is ".$map_array['x_co'].", y is ".$map_array['y_co']."\" ";
	$map_data_output .= "alt=\"Map square\" style=\"width: 40px; height: 40px; background-color: #8CDE21; \" /></span>";

	// add one to count. this increases with each coord output so we know how many we've gone across the X axis.
	$in_row++;

	// check to see if it's time to drop down to a new row yet
	if ($in_row > $map_los*2) {
		$map_data_output .= "</div>";
		// set the X axis back to zero
		$in_row = 0;
		
		// we need to count the number of rows we've output so far
		$rows++;
		
		// if we've not just output the last row then we'll need to start another
		if (($map_los*2)+1 != $rows) $map_data_output .= "<div>";
	}
}

echo htmlspecialchars ($map_data_output);
echo "\t</map_data>\n";

// sometimes there are actions that can be done whilst the user is on this coordinate, like enter a special place or town,
// or pick up an item lying on the floor, or talk to someone. (these special squares have the ID 4.)
$nav_data_output = "";
echo "\t<navigation_data>\n";
if ($user_map_data['type'] == 4) {
	// get the special map data, which includes the text to output as the link, and the URL the link should take them to
	$special_map_data = $Database->query ("SELECT * FROM `map_special` WHERE `grid_id` = ".$user_map_data['grid_id']);

	$nav_data_output .= "\t\t<p><a href=\"".relroot.$special_map_data['goto_uri']."\">".$special_map_data['goto_name']."</a></p>\n";
}
echo htmlspecialchars ($nav_data_output);
echo "\t</navigation_data>\n";

echo "</root>\n";

?>