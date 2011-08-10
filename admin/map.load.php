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
		map_data => array: single row from `map_data`,
		coords => multi-dimensional array: x first, then y, then the array row for that x,y
	);
*
* The coords will be order on the X axis first, then the Y axis meaning they're safe to output as
* you find them.
*
* @post map_id
* @post refine_x_from The smallest X coord to be returned
* @post refine_x_num How many columns of the X axis should be returned, after _from
* @post refine_y_from The smallest Y coord to be returned
* @post refine_y_num How many rows of the Y axis should be returned, after _from
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
if (!is_numeric ($_POST['map_id']) || !is_numeric ($_POST['refine_x_from']) || !is_numeric ($_POST['refine_x_num']) || !is_numeric ($_POST['refine_y_from']) || !is_numeric ($_POST['refine_y_num'])) {
	$return['status'] = 400;
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

// the refined data may come back as -99999, which is a special case that hopefully will never be a ligit coordinate.
// it means "just give me a valid default".
if ($_POST['refine_x_from'] == -99999) {
	// find what the lowest X is
	$_POST['refine_x_from'] = $Database->getSingleValue ("SELECT MIN(`x_co`) FROM `map` WHERE `map_id` = ".$_POST['map_id']);
}
if ($_POST['refine_y_from'] == -99999) {
	$_POST['refine_y_from'] = $Database->getSingleValue ("SELECT MIN(`x_co`) FROM `map` WHERE `map_id` = ".$_POST['map_id']);
}
if ($_POST['refine_x_num'] == -99999) {
	$_POST['refine_x_num'] = 50;
}
if ($_POST['refine_y_num'] == -99999) {
	$_POST['refine_y_num'] = 50;
}

// pass back the refine data so that it can populate the form there
$return['refine'] = array (
	'x' => array ('from' => $_POST['refine_x_from'], 'num' => $_POST['refine_x_num']),
	'y' => array ('from' => $_POST['refine_y_from'], 'num' => $_POST['refine_y_num'])
);

$qry_coords = $Database->query ("SELECT * FROM `map` WHERE `map_id` = ".$_POST['map_id']." AND `x_co` >= ".$_POST['refine_x_from']." AND `x_co` <= ".($_POST['refine_x_from']+$_POST['refine_x_num'])." AND `y_co` >= ".$_POST['refine_y_from']." AND `y_co` <= ".($_POST['refine_y_from']+$_POST['refine_y_num'])." ORDER BY `x_co` ASC, `y_co` ASC");
while ($coord = mysql_fetch_assoc ($qry_coords)) {
	$return['coords'][$coord['x_co']][$coord['y_co']] = $coord;
}

echo json_encode ($return);

?>