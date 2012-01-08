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
			
			// if it's zero, the mob won. XP is not deducted in here.
			if ($new_health == 0) $Character->getFightData()->setDetail ('stage', 'mob win');
			
			$Character->setDetail ('remaining_hp', $new_health);
		} else {
			$r['hit'] = false;
			$r['hit_amount'] = 0;
		}
		
		return $r;
	}
	
	/**
	* When losing a fight against this mob the Character will loss a set of XP, to be worked out here.
	*
	* Does not deduct the XP. Just works it out.
	* 
	* Algorithm makes it so that if you lose a higher level mob, you lose less XP than if you lose to the same
	* mob at a higher level. (Lose to weaker mob, lose more points.) Will never return a number higher than the
	* current Character's XP.
	*
	* If no character is given, just does a getDetail().
	*
	* @param Character
	* @return int
	*/
	public function getXPLoss (Character $Character = null) {
		$loss = $this->getDetail ('xp_loss');
		
		if (is_object ($Character)) {
			// create a percent based on the player and mob's level difference.
			$percent_loss = (100 + (($Character->getLevel() - $this->getDetail ('level')) * 10)) / 100;
			
			// user will lose $percent_loss of $loss
			$loss = $loss * $percent_loss;
			
			// no higher than the users actual XP for this level. (never make the user drop a level)
			if ($Character->xpIntoLevel() < $loss) $loss = $Character->xpIntoLevel();
		}
		
		return (int) $loss;
	}
	
	/**
	* Based largely on getXPLoss, works out how much XP a user gains when beating a Mob
	*
	* Does not add the XP, only returns the amount that should be gained.
	*
	* Inverse algorithm to getXPLoss.
	*
	* If no character is given, just does a getDetail
	*
	* @param Character
	* @return int
	*/
	public function getXPGain (Character $Character = null) {
		$gain = $this->getDetail ('xp_gain');
		
		if (is_object ($Character)) {
			// create a percent based on the player and mob's level difference.
			$percent_gain = (100 + (($this->getDetail ('level') - $Character->getLevel()) * 10)) / 100;
			
			// user will lose $percent_loss of $loss
			$gain = $gain * $percent_gain;
		}
		
		return $gain;
	}
	
	/**
	* Works out the mob's speed.
	*
	* @return int
	*/
	public function getSpeed () {
		return $this->getDetail ('speed');
	}
	
	/**
	* Gets a well formatted name of the mob.
	*
	* Specifcally useful if we need "(a|an) mob". There's no capitalisation done in here, unless the mob's name has
	* its own capitalisation.
	*
	* @return string
	*/
	public function getName ($with_indef_art=false) {
		// work out how to format their name, with "an" or "a"?
		$mob_name = $this->getDetail ('name');
		
		if ($with_indef_art && $this->getDetail ('requires_indef_art')) $mob_name = indef_article ($mob_name);
		
		return $mob_name;
	}
	
	/**
	* Figure which items a user should get at the end of a fight.
	*
	* The mob has a selection of items it could drop in `mob_drop`. It can drop zero, one, or many.
	* The chance of a particular item dropping depends on the `frequency`, which is a number out
	* of zero. An item with a frequency of 5 will have a 5% of dropping.
	*
	* The liklihood of a second item dropping is 1/2 of it's frequency. A third item is 1/3. An nth
	* item is 1/n.
	*
	* The user may not get these items though - a second check to make sure they have enough invent
	* space will have to be done before giving these items.
	*
	* @return array Array of items
	*/
	public function getDrops () {
		// which items can the mob drop?
		$qry_candrop = $this->getDatabase()->query ("SELECT * FROM `mob_drop` WHERE `mob_id` = ".$this->getId());
		
		if (mysql_num_rows ($qry_candrop)) {
			$drops = array ();
			$num_dropped = 1; // starts at one, since 5/1 = 1
		
			while ($drop = mysql_fetch_assoc ($qry_candrop)) {
				$die_roll = rand (1, 100);
				
				if ($die_roll <= (int) ($drop['frequency'] / $num_dropped)) {
					$drops[] = new Item ($drop['item_id']);
				
					$num_dropped++;
				}
			}
			
			return $drops;
		} else {
			return array ();
		}
	}
}

?>