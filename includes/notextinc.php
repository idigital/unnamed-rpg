<?php

/**
* Holds all the includes that are always needed, but only ones which *do not* output any text. This means that headers
* and things can be changed using information from the database.
*/

require_once ('config.php');

require_once ('StandardObject.class.php');

require_once ('database.php');
require_once ('common.php');

require_once ('user.class.php');

require_once ('character.class.php');
require_once ('character.map.class.php');

// session must be started after we include (define) the user class (since we're storing a User in
// a session.)
session_start ();

if (isset ($_SESSION['user'])) $User = $_SESSION['user'];

require_once ('access.php');

if (isset ($Character) && defined ('FORCE_PHASE')) {
	// this is a good place to sort out 'phases' and to explain what they are. a 'phase' is just the stage of the game the
	// user is currently at: be it walking around the map, fighting a mob, or on a special page. A user can be standing on
	// a special page coordinate, but not actually have selected to view the page yet. Similarly, if a user is in a mob fight
	// buy then goes to map.php, they should be redirected back to the fight - can't escape that easily!
	$current_phase = $Character->getMapData()->getDetail ('phase');
	
	$current_page = basename ($_SERVER['PHP_SELF']);
	
	// this if branch will just get skipped over if they are on the correct page.
	if ($current_page != "map.php" && $current_phase == 'map') {
		header ('Location: '.relroot.'/map.php');
	} elseif ($current_page != "fight.php" && $current_phase == 'fight') {
		header ('Location: '.relroot.'/fight.php');
	} elseif ($current_phase == 'special') {
		// not developed yet.
	}
}
?>