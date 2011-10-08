<?php

/**
* There's a one-to-one relationship between a Character and a User; they both always have each other. The
* seperatation between the two is just because I want to keep functionality of the accounts and the playing
* character apart.
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
	
	/**
	* Gets the current level of the character, based on their character.
	*
	* We store the accumulative experience gained for the character, and not their level. The level
	* can be worked out since we have the experience brackets in a table.
	*
	* @return int
	*/
	public function getLevel () {
		$current_level = $this->getDatabase()->getSingleValue ("SELECT `level` FROM `stats_base` WHERE `experience_required` <= ".$this->getDetail ('experience')." ORDER BY `level` DESC LIMIT 1");
		
		return $current_level;
	}
	
	/**
	* Finds out how much experience is needed for the next level.
	*
	* This isn't "how much more experience". Use this::nextLevelIn for that.
	*
	* @return int
	*/
	public function nextLevelAt () {
		$next_level_at = $this->getDatabase()->getSingleValue ("SELECT `experience_required` FROM `stats_base` WHERE `level` > ".$this->getLevel ()." ORDER BY `level` ASC LIMIT 1");
		
		return $next_level_at;
	}
	
	/**
	* How much more experience is required until the next level?
	*
	* @return int
	*/
	public function nextLevelIn () {
		return $this->nextLevelAt() - $this->getDetail ('experience');
	}
	
	/**
	* Figures, based on the level, what the current max health is
	*
	* @return int
	*/
	public function getMaxHealth () {
		// I could have reused the SQL from this::getLevel, which is a fairly quick statement rather than using getLevel (which means we're doing
		// two database calls), however there may be things other than the experience which affects the level, so we have to use getLevel().
		$max_hp = $this->getDatabase()->getSingleValue ("SELECT `hp` FROM `stats_base` WHERE `level` = ".$this->getLevel()." LIMIT 1");
		
		return $max_hp;
	}
	
	/**
	* Gets the data about the fight a user has fought, or is fighting.
	*
	* If "current" or no param is passed, then the function will figure out what the current fight is.
	* If there is no current fight, then false will be returned.
	*
	* @param mixed
	* @return mixed CharacterFight if there's actually a fight, false otherwise
	*/
	public function getFightData ($fight_id="current") {
		if ($fight_id == "current") {
			// just find the last fought fight to return
			$fight_id = $this->getDatabase()->getSingleValue ("SELECT `fight_id` FROM `user_fight` WHERE `user_id` = ".$this->getId()." ORDER BY `fight_id` DESC LIMIT 1");
			
			if (empty ($fight_id)) {
				return false;
			}
		}
	
		$Fight = new CharacterFight ($fight_id);
		
		return ($Fight->exists()) ? $Fight : false;
	}
	
	/**
	* Immediately starts a fight, with the Mob given.
	*
	* @param Mob
	* @return void
	*/
	public function startFight (Mob $Mob) {
		$this->getMapData()->setDetail ('phase', 'fight');
		$this->getDatabase()->query ("INSERT INTO `user_fight` SET `user_id` = ".$this->getId().", `mob_id` = ".$Mob->getId().", `mob_health` = ".$Mob->getDetail ('hp').", `start_time` = UNIX_TIMESTAMP()");
		
		return;
	}
	
	/**
	* Spawns the user again with full health back at the spawning grid.
	*
	* @return void
	*/
	public function respawn () {
		// for now, the spawning grid is grid_id 131
		$respawn_at = new MapGrid (131);
		
		$map_data = $this->getMapData();
		$map_data->setDetail ('map_id', $respawn_at->getDetail ('map_id'));
		$map_data->setDetail ('x_co', $respawn_at->getDetail ('x_co'));
		$map_data->setDetail ('y_co', $respawn_at->getDetail ('y_co'));
		$map_data->setDetail ('phase', 'map');
		
		$this->setDetail ('remaining_hp', $this->getMaxHealth());
		
		return;
	}
	
	public function getMapData () { return new CharacterMap ($this->getId()); }
	public function getUser () { return new User ($user_id); }
}

?>