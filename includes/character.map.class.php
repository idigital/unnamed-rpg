<?php

/**
* Gets the data about the character's current map area
*/

class CharacterMap extends StandardObject {
	public function __construct ($user_id) {
		//  We'll need a database.. Fortunately, constants stick around even inside private scope!
		$Database = new Database (database_server, database_user, database_password, database_name);
	
		$config = array (
			'table' => "user_map",
			'database' => $Database,
			'item_id' => $user_id,
			'primary_key' => "user_id"
		);
		
		parent::__construct ($config);
	}
	
	/**
	* Taking the map, user's state and mobs that are in the area into consideration, should we trigger
	* a fight?
	*
	* Will do the test to see if a fight is possible, and then rolls a dice to see if it does start. If
	* so, it'll put the player into a fight.
	*
	* @return bool true on fight start
	*/
	public function rollSpawnMob () {
		// are the actually any mobs around here?
		$mobs = $this->getGrid()->getMobs();
		if (count ($mobs)) {
			// pick one!
			$Mob = $mobs[rand (0,count ($mobs)-1)];
			
			$dice_roll = rand (1, 100);
			
			if ($dice_roll < $this->getGrid()->getSpawnChance()) {
				// start a fight!
				$this->getCharacter()->startFight($Mob);
				
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	public function getId () { return $this->getDetail ('map_id'); }
	public function getGrid () { return MapGrid::byCoord ($this->getId(), $this->getX(), $this->getY()); }
	public function getCharacter () { return new Character ($this->getDetail ('user_id')); }
	public function getX () { return $this->getDetail ('x_co'); }
	public function getY () { return $this->getDetail ('y_co'); }
}

?>