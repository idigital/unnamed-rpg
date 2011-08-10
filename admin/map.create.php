<?php

/**
* Creates a map, based on the parameters it's been given.
*
* @nodirectaccess
* @post name Name of the map
* @post height Height of map
* @post width Width of map
* @post image Default block to fill
* @return json
*/

define ('LOGIN', 1);

require_once ('../includes/notextinc.php');

$return = array ();

// needs to be an admin user...
if ($User->getDetail ('role') != 'admin') {
	$return['status'] = "no auth";
	echo json_encode ($return);
	exit;
}

if (is_empty ($_POST['name'], $_POST['width'], $_POST['height'], $_POST['image'])) {
	$return['status'] = "missing para";
	echo json_encode ($return);
	exit;
}

$Database->query ("INSERT INTO `map_data` SET `map_name` = '".$Database->escape ($_POST['name'])."'");
$map_id = $Database->getLastId ();

// add each coord
for ($x=0;$x<(int)$_POST['width'];$x++) {
	for ($y=0;$y<(int)$_POST['height'];$y++) {
		$Database->query ("INSERT INTO `map` SET `x_co` = ".$x.", `y_co` = ".$y.", `image` = '".$Database->escape ($_POST['image'])."', `map_id` = ".$map_id.", `locality` = '".$Database->escape ($_POST['name'])."', `type` = 1");
	}
}

$return['status'] = "complete";
$return['map_id'] = $map_id;

echo json_encode ($return);

?>