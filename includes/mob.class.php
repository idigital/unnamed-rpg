<?php

/**
* Mobs are NPCs in the game which are usually not friendly. They're randomly evoked as the user walks around the map,
* though some are triggered.
*
* Unlike Characters, a mob's skills and stats are typically always the same and they don't level up or gain XP.
*/

class Mob extends StandardObject {
	function __construct ($mob_id) {
		$Database = new Database (database_server, database_user, database_password, database_name);
	
		$config = array (
			'table' => "mob",
			'database' => $Database,
			'item_id' => $mob_id,
			'primary_key' => "mob_id"
		);
		
		parent::__construct ($config);
	}
}

?>