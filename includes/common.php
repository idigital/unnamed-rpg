<?php

/*
* Holds common functions that're used throughout this website
*/

//  Define the project relative root, no trailing slash
define ('relroot', '/unnamedrpg');
define ('sitename', 'Unnamed RPG');

/**
* Checks if there's a user currently logged in
*
* @return Bool
*/ 
function is_logged () {
	//  If a user object is stored, then someone's logged in
	if (isset ($_SESSION['user'])) return true; else return false;
}

/*
* Make a better empty() function, which can handle multiple arguments. It'll check each one, if any of them
* are empty then it returns true. If any of the args are not empty ( http://php.net/empty ) then return false.
* ie. Only return true when everything is empty
*
* @param mixed Any variables
* @return True is anything is empty
*/
function is_empty () {
	//  There needs to be at least one arg
	if (func_num_args() < 1) trigger_error ("At least one parameter expected", E_USER_ERROR);
	
	$bool_empty = false;

	$args = func_get_args();
	foreach ($args as $arg) if (empty ($arg)) $bool_empty = true;
	
	return $bool_empty;
}

/**
* Works out the time since the parameter in words
*
* @param int Timestamp that needs checking
* @return String
*/
function time_since ($original) {
	if ($original > time()) return false;

	// array of time period chunks
	$chunks = array(
	array(60 * 60 * 24 * 365 , 'year'),
	array(60 * 60 * 24 * 30 , 'month'),
	array(60 * 60 * 24 * 7, 'week'),
	array(60 * 60 * 24 , 'day'),
	array(60 * 60 , 'hour'),
	array(60 , 'minute'),
	array(1 , 'second'),
	);

	$today = time();
	$since = $today - $original;

	// $j saves performing the count function each time around the loop
	for ($i = 0, $j = count($chunks); $i < $j; $i++) {
		$seconds = $chunks[$i][0];
		$name = $chunks[$i][1];

		// finding the biggest chunk (if the chunk fits, break)
		if (($count = floor($since / $seconds)) != 0) break;
	}

	$print = ($count == 1) ? '1 '.$name : "$count {$name}s";

	if ($i + 1 < $j) {
		// now getting the second item
		$seconds2 = $chunks[$i + 1][0];
		$name2 = $chunks[$i + 1][1];

		// add second item if it's greater than 0
		if (($count2 = floor(($since - ($seconds * $count)) / $seconds2)) != 0) {
			$print .= ($count2 == 1) ? ', 1 '.$name2 : ", $count2 {$name2}s";
		}
	}
	return $print;
}

/**
* Returns the time until a certain timestamp in words
*
* @see time_since
* @param int Timestamp to check
* @return String Phrase describing how long till the point
*/
function time_until ($original) {
	if ($original < time()) return false;
	
	//  The difference between the furture date, and now?
	$difference = $original - time();
	$past_time = time() - $difference;
	
	return time_since ($past_time);
}

// http://bit.ly/SGUPO
/* creates a compressed zip file */
function create_zip ($files = array(), $destination = '', $overwrite = false) {
	//if the zip file already exists and overwrite is false, return false
	if (file_exists ($destination) && !$overwrite) return false;
	//vars
	$valid_files = array();
	//if files were passed in...
	if(is_array($files)) {
		//cycle through each file
		foreach($files as $file) {
			//make sure the file exists
			if(file_exists($file)) {
				$valid_files[] = $file;
			}
		}
	}
	//if we have good files...
	if(count($valid_files)) {
		//create the archive
		$zip = new ZipArchive();
		if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) return false;

		//add the files
		foreach($valid_files as $file) {
			// Update specifically for the new-setup.php page, so figure out a better way to do this if this
			// function is needed elsewhere.
			$zip->addFile($file, substr ($file, strpos ($file, '/', 11) + 1));
		}
		//debug
		//echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;
		
		//close the zip -- done!
		$zip->close();
		
		//check to make sure the file exists
		return file_exists($destination);
	}
	else
	{
		return false;
	}
}

function recurse_copy($src,$dst) {
	$dir = opendir($src);
	@mkdir($dst);
	while(false !== ( $file = readdir($dir)) ) {
		if (( $file != '.' ) && ( $file != '..' )) {
			if ( is_dir($src . '/' . $file) ) {
				recurse_copy($src . '/' . $file,$dst . '/' . $file);
			}
			else {
				copy($src . '/' . $file,$dst . '/' . $file);
			}
		}
	}
	closedir($dir);
}


/**
* Strips out punctuation
*/
function strip_punctuation ($text) {
	$urlbrackets	= '\[\]\(\)';
	$urlspacebefore	= ':;\'_\*%@&?!' . $urlbrackets;
	$urlspaceafter	= '\.,:;\'\-_\*@&\/\\\\\?!#' . $urlbrackets;
	$urlall			= '\.,:;\'\-_\*%@&\/\\\\\?!#' . $urlbrackets;
 
	$specialquotes	= '\'"\*<>';
 
	$fullstop		= '\x{002E}\x{FE52}\x{FF0E}';
	$comma			= '\x{002C}\x{FE50}\x{FF0C}';
	$arabsep		= '\x{066B}\x{066C}';
	$numseparators  = $fullstop . $comma . $arabsep;
 
	$numbersign	 = '\x{0023}\x{FE5F}\x{FF03}';
	$percent		= '\x{066A}\x{0025}\x{066A}\x{FE6A}\x{FF05}\x{2030}\x{2031}';
	$prime		  = '\x{2032}\x{2033}\x{2034}\x{2057}';
	$nummodifiers   = $numbersign . $percent . $prime;
 
	return preg_replace (
		array(
		// Remove separator, control, formatting, surrogate,
		// open/close quotes.
			'/[\p{Z}\p{Cc}\p{Cf}\p{Cs}\p{Pi}\p{Pf}]/u',
		// Remove other punctuation except special cases
			'/\p{Po}(?<![' . $specialquotes .
				$numseparators . $urlall . $nummodifiers . '])/u',
		// Remove non-URL open/close brackets, except URL brackets.
			'/[\p{Ps}\p{Pe}](?<![' . $urlbrackets . '])/u',
		// Remove special quotes, dashes, connectors, number
		// separators, and URL characters followed by a space
			'/[' . $specialquotes . $numseparators . $urlspaceafter .
				'\p{Pd}\p{Pc}]+((?= )|$)/u',
		// Remove special quotes, connectors, and URL characters
		// preceded by a space
			'/((?<= )|^)[' . $specialquotes . $urlspacebefore . '\p{Pc}]+/u',
		// Remove dashes preceded by a space, but not followed by a number
			'/((?<= )|^)\p{Pd}+(?![\p{N}\p{Sc}])/u',
		// Remove consecutive spaces
			'/ +/',
		),
		' ',
		$text
	);
}

/**
* Makes a "mini-page" automatically
*
* I'm frequently making small pages which say nothing more than "You need to have [some GET variable]
* selected" or a small phrase. This is just a conveniance method to help with that.
*
* @param string Title of the page (in the window title bar)
* @param string Body of the page
* @param string The place to find the includes
* @return void
*/
function minipage ($page_title, $page_body, $wheresincludes) {
	$ext_title = $page_title;
	include ($wheresincludes.'includes/header.php');
	
	echo $page_body;
	
	include ($wheresincludes.'includes/footer.php');
	exit;
}

?>