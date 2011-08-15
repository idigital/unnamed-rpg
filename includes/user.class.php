<?php

/**
* Manages a user
*/

class User extends StandardObject {
	public function __construct ($user_id) {
		//  We'll need a database.. Fortunately, constants stick around even inside private scope!
		$Database = new Database (database_server, database_user, database_password, database_name);
	
		$config = array (
			'table' => "user",
			'database' => $Database,
			'item_id' => $user_id,
			'primary_key' => "user_id"
		);
		
		parent::__construct ($config);
	}
	
	public function getMapData () { return new UserMap ($this->getId()); }
	
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