<?php

/**
* Gives a UI for the creation and editing of maps.
*
* My vision for this page is to have a toolbox on the right to select a map type, and then you can
* just "paint" it onto the map. There should be a block fill tool, and a pencil tool, for speed.
*
* On success, where status is 200, ther json will look like this:
	$return = array (
		'status' => 200,
		map_data => [array: single row from `map_data`],
		coords => array ( [array: row for first coord of map], [array: row for second coord of map], ..., ...)
	);
*
* The coords will be order on the X axis first, then the Y axis meaning they're safe to output as
* you find them.
*/

define ('LOGIN', 1);

require_once ('../includes/notextinc.php');

$return = array ();

// needs to be an admin user...
if ($User->getDetail ('role') != 'admin') {
	$return['status'] = 401;
	echo json_encode ($return);
	exit;
}

// we need a map id, that's an int
if ((int) $_POST['map_id'] != $_POST['map_id'] || !isset ($_POST['map_id'])) {
	$return['status'] = 502;
	echo json_encode ($return);
	exit;
}

// it exists, right?
$qry_map = $Database->query ("SELECT * FROM `map_data` WHERE `map_id` = ".$_POST['map_id']);
if (!mysql_num_rows ($qry_map)) {
	$return['status'] = 404;
	echo json_encode ($return);
	exit;
}

$return['status'] = 200;
$return['map_data'] = mysql_fetch_assoc ($qry_map);

// and now its coords data
$return['coords'] = array ();

$qry_coords = $Database->query ("SELECT * FROM `map` WHERE `map_id` = ".$_POST['map_id']." ORDER BY `x_co` ASC, `y_co` ASC");
while ($coord = mysql_fetch_assoc ($qry_coords)) {
	$return['coords'][] = $coord;
}

echo json_encode ($return);

?>