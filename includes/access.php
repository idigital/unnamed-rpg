<?php

/**
* Controls if the user needs to be logged in or not, and allows them to
*/

//  You decide if the user needs to be logged into a page by defining "LOGIN" before including [notextinc.php]
//  If it is defined, or the user wants to log in willingly (by $_GET['login'] being set), and they're not
//  already logged in...
if ((defined ('LOGIN') || isset ($_GET['login'])) && !is_logged ()) {
	//  If either of the required information is blank...
	if (empty ($_POST['login_username']) || empty ($_POST['login_password'])) {
		//  Output a mini-page, with a log in box
		$ext_title = "Log in";
		// hashmask
		$ext_js[] = relroot."/js/jquery.sha1.js";
		$ext_js[] = relroot."/js/jquery.sparkline.js";
		$ext_js[] = relroot."/js/jquery.hashmask.js";
		$ext_js[] = relroot."/js/login.js";
		include_once ('header.php');
		
		echo "<p>We need you to log in before you can see this page.</p>\n";
		echo "<form action=\"".$_SERVER['REQUEST_URI']."\" method=\"post\">\n<p>Username: <input type=\"text\" name=\"login_username\" /></p>\n<p>Password: <input type=\"password\" name=\"login_password\" /></p><p><input type=\"submit\" name=\"login_submit\" value=\"Log in\" /></p>\n";
		echo "</form>\n";
		
		include_once ('footer.php');
		//  Now we need to stop the rest of the page showing
		exit;
	} else {
		//  The form has been submitted and we have our username and password. Now we need to confirm them
		//  See if there are any users that have that username and that password
		//  If there isn't a user with those details...
		$qry_userlogin = $Database->query ("SELECT `user_id` FROM `user` WHERE `username` = '".$_POST['login_username']."' AND `password` = '".User::password_hash ($_POST['login_password'])."'");
		if (!mysql_num_rows ($qry_userlogin)) {
			//  Output the form again, but this time with a message saying that they got something wrong
			$ext_title = "Try again";
			$ext_js[] = relroot."/js/jquery.sha1.js";
			$ext_js[] = relroot."/js/jquery.sparkline.js";
			$ext_js[] = relroot."/js/jquery.hashmask.js";
			$ext_js[] = relroot."/js/login.js";
			include_once ('header.php');
			
			echo "<p>There was no match for a user with that password.</p>\n";
			echo "<form action=\"".$_SERVER['REQUEST_URI']."\" method=\"post\">\n<p>Username: <input type=\"text\" name=\"login_username\" value=\"".$_POST['login_username']."\" /></p>\n<p>Password: <input type=\"password\" name=\"login_password\" /></p><p><input type=\"submit\" name=\"login_submit\" value=\"Log in\" /></p>\n";
			echo "</form>\n";
			
			include_once ('footer.php');
			exit;
		} else {
			//  They got the right details, so we can log them in
			$tmp_user_id = mysql_fetch_row ($qry_userlogin);
			$_SESSION['user'] = new User ($tmp_user_id[0]);
		}
	}
}

if (isset ($_SESSION['user'])) $User = $_SESSION['user'];

?>