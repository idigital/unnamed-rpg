<?php

/**
* There's a one-to-one relationship between a Character and a User; they both always have each other. The
* seperate between the two is just because I want to keep functionality of the accounts and the playing character
* apart.
*/

class Character extends StandardObject {
	function __construct ($user_id) {
		//  We'll need a database.. Fortunately, constants stick around even inside private scope!
		$Database = new Database (database_server, database_user, database_password, database_name);
	
		$config = array (
			'table' => "character_stats",
			'database' => $Database,
			'item_id' => $user_id,
			'primary_key' => "user_id"
		);
		
		parent::__construct ($config);
	}
	
	public function getMapData () { return new CharacterMap ($this->getId()); }
	public function getUser () { return new User ($user_id); }
}

?>