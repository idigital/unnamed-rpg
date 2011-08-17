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
		
		return $this->getDetail ('experience');
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
	
	public function getMapData () { return new CharacterMap ($this->getId()); }
	public function getFightData () { return new CharacterFight ($this->getId()); }
	public function getUser () { return new User ($user_id); }
}

?>