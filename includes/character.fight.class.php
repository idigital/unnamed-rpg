<?php

/**
* Methods which handle fight actions.
*/

class CharacterFight extends StandardObject {
	public function __construct ($fight_id) {
		//  We'll need a database.. Fortunately, constants stick around even inside private scope!
		$Database = new Database (database_server, database_user, database_password, database_name);
	
		$config = array (
			'table' => "user_fight",
			'database' => $Database,
			'item_id' => $fight_id,
			'primary_key' => "fight_id"
		);
		
		parent::__construct ($config);
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
			
			// if the mob's health is zero, then the player has won
			if ($new_health == 0) $this->setDetail ('stage', 'player win');
			
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
	
	/**
	* The character attempting to flee a fight.
	*
	* Takes into account the mob's speed, and the user's speed, and does a dice roll to see if the user is successful.
	*
	* @return bool True on success.
	*/
	public function flee () {
		# these will be dynamic when we get to dealing with stats proper
		$mob_speed = $this->getMob()->getSpeed();
		$user_speed = $this->getCharacter()->getSpeed();
		
		$roll = rand (1, ($mob_speed+$user_speed));
		
		$success = ($roll <= $user_speed);
		
		if ($success) $this->setDetail ('stage', 'player flee success');
		
		return $success;
	}
	
	/**
	* Finds out what state the fight is currently in.
	*
	* "States" are flags that have to be set which mark what the fight is going. For instance, the character could have won,
	* or fled. We need to store this data since the user could come to the fight page and have already won (a fight they won
	* before closing the browser, or just by refreshing) and would still need to see the "you've defeated the mob!" page until
	* they've clicked to move on.
	*
	* The default stage is "current".
	*
	* @return string "player win", "mob win", "player flee success", "current"
	*/
	public function getStage () {
		// we don't want to just use getDetail, since we want to avoid caching.
		$stage = $this->getDatabase()->getSingleValue ("SELECT `stage` FROM `user_fight` WHERE ".$this->getSQLWhereClause());
		
		return $stage;
	}
	
	/**
	* Gets the message which were created within the last turn.
	*
	* The messages will be sent as a FightMessage, so you can get the actual text of the message with
	* $FightMessage->getString().
	*
	* @return array
	*/
	public function getPreviousTurnMessages () {
		$last_turn_id = $this->getDatabase()->getSingleValue ("SELECT `turn_id` FROM `fightmessage_turn` WHERE `fight_id` = ".$this->getId()." ORDER BY `turn_id` DESC LIMIT 1");
	
		if ($last_turn_id) {
			$messages = array ();
			
			$qry_messages = $this->getDatabase()->query ("SELECT `msg_id` FROM `fightmessage_turn_message` WHERE `turn_id` = ".$last_turn_id." ORDER BY `order` ASC");
			while ($message = mysql_fetch_assoc ($qry_messages)) {
				$messages[] = new FightMessage ($last_turn_id, $message['msg_id']);
			}
			
			return $messages;
		} else {
			return array ();
		}
	}
	
	/**
	* Checks what the user has won, but doesn't give it to them just yet.
	*
	* Adds the loot the user has won into the database, unless it's already been added. Safe to
	* call repeatedly.
	*
	* If it has already been decided, then each item_id being given will be stored in a serialized
	* array, under `reward`. Despite that, the function will return an array of Item elements.
	*
	* @retur array Of Items the user has won
	*/
	public function discoverLoot () {
		// have we already decided what we're going to win?
		$stored = $this->getDetail ('reward');
		
		if ($stored == null) {
			// we've not, so we need to decide, and add that to the database now
			$drops = $this->getMob()->getDrops();
			
			if ($drops) {
				$won = array ();
				
				foreach ($drops as $Item) {
					if ($this->getCharacter()->getInventory()->canHoldMore ($Item)) {
						$won[] = $Item;
						$won_ids[] = $Item->getId();
					}
				}
				
				$this->setDetail ('reward', serialize ($won_ids));
				
				return $won;
			} else {
				$this->setDetail ('reward', "a:0:{}");
			
				return array ();
			}
		} else {
			$won = unserialize ($this->getDetail ('reward'));
			
			$loot = array ();
			
			foreach ($won as $item_id) {
				$loot[] = new Item ($item_id);
			}
			
			return $loot;
		}
	}
	
	/**
	* Actually gives the user the loot which they get from this fight.
	*
	* Doesn't check if the fight is finished, so only do this if you're sure it has.
	*
	* @return array Items given
	*/
	public function takeLoot () {
		$to_take = $this->discoverLoot ();
		
		$taken = array ();
		
		foreach ($to_take as $Item) {
			// make sure they can hold it again. I don't know why this would have changed between the discovering
			// and now, but make sure.
			if ($this->getCharacter()->getInventory()->canHoldMore ($Item)) {
				$this->getCharacter()->getInventory()->alterBy ($Item, 1);
				
				$taken[] = $Item;
			}
		}
		
		return $taken;
	}
	
	public function getCharacter () { return new Character ($this->getDetail('user_id')); }

	/**
	* Gets the static data about the mob the user is currently up against.
	*
	* By static, I mean data not dynamic to the fight. You should use this to get max health, but
	* not current health.
	*
	* @return Mob
	*/
	public function getMob () {
		return new Mob ($this->getDetail ('mob_id'));
	}
}

?>