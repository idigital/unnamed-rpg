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
	
	/**
	* Handles the player attacking the opponent
	*
	* Will return an array, looking somewhat like this:
	* 	array (	'hit' => true // bool, if the hit connected, or missed
				'hit_amount' => 3 // int, amount of damage the attack caused. Will be zero if missed. Zero damage can also be dealt, without missing
		)
	*
	* @return array A key-value giving data on result
	*/
	public function attack () {
		# for now lets just make up an attack amount
		$attack = 3;
		$accuracy = 40; // percent

		# when this is all working properly, the accuracy will have take into account the mob's evaisiveness
		// check if the hit
		if (rand (0, 100) >= $accuracy) {
			$r['hit'] = true;
			$r['hit_amount'] = $attack;
			
			// adjust the mob's health
			// make sure it never goes below zero
			$new_health = $this->getDetail ('mob_health')-$attack;
			$new_health = ($new_health < 0) ? 0 : $new_health;
			
			$this->setDetail ('mob_health', $new_health);
		} else {
			$r['hit'] = false;
			$r['hit_amount'] = 0;
		}
		
		return $r;
	}
	
	/**
	* Handles the 'do nothing' action.
	*
	* This is just a place holder function.
	*
	* @return void
	*/
	public function doNothing () {
		return;
	}
	
	public function getCharacter () { return new Character ($user_id); }
}

?>