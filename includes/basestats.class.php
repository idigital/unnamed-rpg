<?php

/**
* Allows reading of base attributes of each level.
*
* Each level has a certain number of XP (stored as `experience_required`) which is needed to reach.
*
* "Base" means without any modifiers that a specific character might have.
*/

class BaseStats extends StandardObject {
	public function __construct ($level) {
		//  We'll need a database.. Fortunately, constants stick around even inside private scope!
		$Database = new Database (database_server, database_user, database_password, database_name);
	
		$config = array (
			'table' => "stats_base",
			'database' => $Database,
			'item_id' => $level,
			'primary_key' => "level"
		);
		
		parent::__construct ($config);
	}
	
	
	/**
	* The following are largely functions concerning levels.
	*/
	
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
	* Returns how much XP this level consists of
	*
	* @return int
	*/
	public function xpRequiredToDing () {
		return $this->nextLevelAt() - $this->getDetail ('experience_required');
	}
	
	public function getLevel () { return $this->getId(); }
	
	/**
	* Gets a BaseStats object when you only know the XP value, no the level.
	*
	* This function is very useful since we don't store the level of a character, only their accumulated XP.
	*
	* @param int XP
	* @return BaseStats
	*/
	public static function byXP ($xp) {
		$Database = new Database (database_server, database_user, database_password, database_name);
		
		$level = $Database->getSingleValue ("SELECT `level` FROM `stats_base` WHERE `experience_required` <= ".(int) $xp." ORDER BY `level` DESC LIMIT 1");
		
		return new self ($level);
	}
}

?>