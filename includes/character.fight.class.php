<?php

/**
* Methods which handle fight actions.
*/

class CharacterFight extends StandardObject {
	public function __construct ($user_id) {
		//  We'll need a database.. Fortunately, constants stick around even inside private scope!
		$Database = new Database (database_server, database_user, database_password, database_name);
	
		$config = array (
			'table' => "user_mob",
			'database' => $Database,
			'item_id' => $user_id,
			'primary_key' => "user_id"
		);
		
		parent::__construct ($config);
	}
	
	/**
	* Gets the static data about the mob the user is currently up against
	*
	* @return Mob
	*/
	public function getMob () {
		return new Mob ($this->getDetail ('mob_id'));
	}
	
	public function getCharacter () { return new Character ($user_id); }
}

?>