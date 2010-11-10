<?php

/**
* This is the page where the user moves around the world. The world is displayed in a grid of images
* representing what's in that space. The world should be pretty massive, and so we don't want to be
* showing the entire world to the player; only a small range of sight.
*/

define ('LOGIN', true);
require_once ('includes/notextinc.php');

$ext_title = "The World";
require_once ('includes/header.php');
echo "<h2>".$ext_title."</h2>\n";

// what range do we want to be showing the user? this is also the width and height of the map. Because
// of that, it makes sense for it to be odd so that there's a centre point to the map, which we can put
// the user in.
# for now, we can just set it in here. later I'll think about adding a better place to store this.
$map_los = 9;

// now we can work out what coordinates the user can see
$x_smallest = $User->getMapData()->getX() - floor ($map_los/2);
$x_largest = $User->getMapData()->getX() + floor ($map_los/2);
$y_smallest = $User->getMapData()->getY() - floor ($map_los/2);
$y_largest = $User->getMapData()->getY() + floor ($map_los/2);

/**
* Note on why there's a map_id: in the future I want to make it so there are multiple maps for different
* areas. At the moment there'll only the "The World", and so I'll just hardcode in map_id 1. Future proofing!
*/

$qry_mapgrid = $Database->query ("SELECT * FROM `map` WHERE `x_co` >= ".$x_smallest." AND `x_co` <= ".$x_largest." AND `y_co` >= ".$y_smallest." AND `y_co` <= ".$y_largest." AND `map_id` = 1");

require_once ('includes/footer.php');
?>