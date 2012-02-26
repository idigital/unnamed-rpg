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
			'table' => "user_stats",
			'database' => $Database,
			'item_id' => $user_id,
			'primary_key' => "user_id"
		);
		
		parent::__construct ($config);
		
		$this->BaseStats = BaseStats::byXP ($this->getDetail ('experience'));
	}
	
	/**
	* How much more experience is required until the next level?
	*
	* @return int
	*/
	public function nextLevelIn () {
		return $this->getBaseStats()->nextLevelAt() - $this->getDetail ('experience');
	}
	
	/**
	* Works out how much XP the user is currently into their level
	*
	* @return int
	*/
	public function xpIntoLevel () {
		$level_start = $this->getBaseStats()->getDetail ('experience_required');
		
		return $this->getDetail ('experience') - $level_start;
	}
	
	/**
	* Finds how far into the current level a user is in
	*
	* @return in 0 - 99.999
	*/
	public function percentIntoLevel () {
		$percent = ($this->xpIntoLevel()/$this->getBaseStats()->xpRequiredToDing()) * 100;
		
		return $percent;
	}
	
	/**
	* Figures, based on the level, what the current max health is
	*
	* @return int
	*/
	public function getMaxHealth () {
		// one liner at the moment, but later armor and the like may change this figure, so it needs to be here for easy
		// forwards compatibility.
		return $this->getBaseStats()->getDetail ('hp');
	}
	
	public function getHealth () {
		$this->getDetail ('remaining_hp');
		return $this->getDetail ('remaining_hp');
	}
	
	public function getPercentHealth () {
		$this->getHealth() / $this->getMaxHealth();
		return (int) (($this->getHealth() / $this->getMaxHealth()) * 100);
	}
	
	/**
	* Works out the player's speed.
	*
	* @return int
	*/
	public function getSpeed () {
		return $this->getBaseStats()->getDetail ('speed');
	}
	
	/**
	* Finds out how much an attack should hit for
	*
	* This includes modifiers of weapons and such.
	*
	* @return int
	*/
	public function getAttack () {
		// attack is largely based on strengh, plus weapon strength
		return $this->getStrength() + $this->getWeaponStrength();
	}
	
	/**
	* Based on what the player has equiped, how much stronger are they?
	*
	* @return int
	*/
	public function getWeaponStrength () {
		// anything in right hand?
		if ($this->getEquippedItem("righthand")) {
			return $this->getEquippedItem("righthand")->getStrength();
		}
	
		return 0;
	}
	
	/**
	* Gets the strength of the player.
	*
	* Strength is mostly used to calculate attack power, but can be used for other things.
	*
	* @return int
	*/
	public function getStrength() {
		return $this->getBaseStats()->getDetail ('strength');
	}
	
	/**
	* Returns how likely it is for a standard attack to hit.
	*
	* @return int This is a percent, between 0-100, but could be higher.
	*/
	public function getHitAccuracy () {
		return $this->getBaseStats()->getDetail ('accuracy');
	}
	
	public function getEquippedItem ($where) {
		$item_id = $this->getDatabase()->getSingleValue ("SELECT `item_id` FROM `user_item_equip` WHERE `user_id` = ".$this->getId()." AND `position` = '".$where."'");
		
		return ($item_id == 0) ? null : new Item ($item_id);
	}
	
	public function unequipItem ($where) {
		// is there anything equiped..?
		$Item = $this->getEquippedItem ($where);
		if ($Item) {
			// add it back to their inventory
			$this->getInventory()->alterBy ($Item, 1);
			
			$this->getDatabase()->query ("DELETE FROM `user_item_equip` WHERE `user_id` = ".$this->getId()." AND `position` = '".$where."'");
			
			return true;
		} else {
			return false;
		}
	}
	
	/**
	* Equip an item to the character, usually a weapon.
	*
	* If the Item is not equipable, this method will return false. It will also return false if there
	* is already an item equipped in that space.
	*
	* @param string Where to be equiped
	* @param Item Item to equip
	* @return bool Announcing success
	*/
	public function equipItem ($where, Item $Item) {
		// anything already equipped there?
		$alreadyEquiped = $this->getEquippedItem ($where);
		
		// also need to test if the item is actually equippable to this place
		$equipPossible = $Item->is_equippable ($where);
		
		if ($alreadyEquiped === null && $equipPossible === true) {
			$this->getDatabase()->query ("INSERT INTO `user_item_equip` SET `user_id` = ".$this->getId().", `position` = '".$where."', `item_id` = ".$Item->getId());
			
			// remove from inventory
			$this->getInventory()->alterBy ($Item, -1);
			
			return true;
		} else {
			return false;
		}
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
		$map_data = $this->getMapData();
		
		// what's the respawn point here?
		// there's no object or method to get map meta data, so we do the "hard" way.
		$respawn_id = $this->getDatabase()->getSingleValue ("SELECT `respawn_on` FROM `map_data` WHERE `map_id` = ".$map_data->getDetail ('map_id'));
		$respawn_at = new MapGrid ($respawn_id);
		
		$map_data->setDetail ('map_id', $respawn_at->getDetail ('map_id'));
		$map_data->setDetail ('x_co', $respawn_at->getDetail ('x_co'));
		$map_data->setDetail ('y_co', $respawn_at->getDetail ('y_co'));
		
		// we don't set the phase back to "map" here. that's handled in where this function is being called.
		
		$this->setDetail ('remaining_hp', $this->getMaxHealth());
		
		return;
	}
	
	/**
	* Increases character's health by $amount.
	*
	* Can't go above the level's max health.
	*
	* @param int Number of HP to increase by
	* @return int New amount of HP.
	*/
	public function heal ($amount) {
		$amount = (int) $amount;
	
		if ($this->getDetail ('remaining_hp') + $amount > $this->getMaxHealth()) {
			$this->setDetail ('remaining_hp', $this->getMaxHealth());
		} else {
			$this->setDetail ('remaining_hp', $this->getDetail ('remaining_hp') + $amount);
		}
		
		return $this->getDetail ('remaining_hp');
	}
	
	/**
	* Gets the users' inventory
	*
	* @see Inventory
	* @return Inventory
	*/
	public function getInventory () {
		return new Inventory ($this->getId());
	}
	
	public function getMapData () { return new CharacterMap ($this->getId()); }
	public function getUser () { return new User ($user_id); }
	public function getBaseStats () { return $this->BaseStats; }
	public function getLevel () { return $this->getBaseStats()->getLevel();	}
}

?>