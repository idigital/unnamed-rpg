<?php

/**
* Holds all the includes that are always needed, but only ones which *do not* output any text. This means that headers
* and things can be changed using information from the database.
*/


require_once ('database.php');
require_once ('common.php');

require_once ('user.class.php');

// session must be started after we include (define) the user class (since we're storing a User in
// a session.)
session_start ();

require_once ('access.php');
?>