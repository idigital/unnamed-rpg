<?php

/**
* Manages a user
*
*/

class User {
	private $user_id = null;
	private $username = null;
	
	private $Database;
	
	public function __construct ($user_id) {
		$this->user_id = (int) $user_id;
		
		// get a database connection
		$this->Database = new Database (database_server, database_user, database_password, database_name);
	}
	
	public function getUserId () { return $this->user_id; }
	public function getId () { return $this->user_id; }
	
	public function getUsername () {
		if ($this->username == null) {
			$tmp_username = mysql_fetch_assoc ($this->Databasequery ("SELECT `username` FROM `user` WHERE `user_id` = ".$this->user_id));
			$this->username = $tmp_username[0];
		}
		
		return $this->username;
	}
	
	/**
	* Works out the stored version of the user's password
	*
	* @return String Encoded password
	*/
	public static function password_hash ($password) {
		return md5 (md5 ($password));
	}
}

?>