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
	
	/**
	* Handles the mob attacking the opponent
	*
	* Will return an array, looking somewhat like this:
	* 	array (	'hit' => true // bool, if the hit connected, or missed
				'hit_amount' => 3 // int, amount of damage the attack caused. Will be zero if missed. Zero damage can also be dealt, without missing
		)
	*
	* @return array A key-value giving data on result
	*/
	public function attack (Character $Character) {
		# for now lets just make up an attack amount
		$attack = 2;
		$accuracy = 40; // percent

		# when this is all working properly, the accuracy will have take into account the mob's evaisiveness
		// check if the hit
		if (rand (0, 100) >= $accuracy) {
			$r['hit'] = true;
			$r['hit_amount'] = $attack;
			
			// don't make the health lower than zero
			$new_health = $Character->getDetail ('remaining_hp')-$attack;
			$new_health = ($new_health < 0) ? 0 : $new_health;
			
			$Character->setDetail ('remaining_hp', $new_health);
		} else {
			$r['hit'] = false;
			$r['hit_amount'] = 0;
		}
		
		return $r;
	}
}

?>