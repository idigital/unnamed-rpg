<?php

/**
* Manages historic messages stored about the user's actions within a fight.
*
* In order to save redundancy, instead of storing "The imp attacked you for 10 damage", we instead store just the variables
* within that string, "the imp" and the 10. To avoid redundancy, we just store "[1] attacked you for [2] damage" once. The
* variables are switched out when they're needed.
*
* This system also means we can make statistics from actions within the battles. For instance, we could see how much damage
* a level 2 character has on a level 1 mob typically. We couldn't do that if we just stored the string.
*
* I'm not sure how common this system is, but I generally call it the "n0ded messaging system" whenever I use it, since that's
* the first game I developed it for.
*/

class FightMessage extends StandardObject {
	public function __construct ($fight_msg_id) {
		//  We'll need a database.. Fortunately, constants stick around even inside private scope!
		$Database = new Database (database_server, database_user, database_password, database_name);
	
		$config = array (
			'table' => "firstmessage_fight",
			'database' => $Database,
			'item_id' => $fight_msg_id,
			'primary_key' => "fight_msg_id"
		);
		
		parent::__construct ($config);
	}
	
	public function getString () {
		// nab the template string
		$t_string = $this->getDatabase()->getSingleValue ("SELECT `text` FROM `fightmessage_text` WHERE `msg_id` = ".$this->getDetail ('msg_id'));
		
		// go through each of the variables we have stored
		$qry_vars = $this->getDatabase()->query ("SELECT `value` FROM `fightmessage_fight_vars` WHERE `fight_msg_id` = ".$this->getId()." ORDER BY `num` ASC");
		$i = 1;
		if (mysql_num_rows ($qry_vars)) {
			while ($var = mysql_fetch_assoc ($qry_vars)) {
				// what's the type of variable is this?
				$var_type = $this->getDatabase()->getSingleValue ("SELECT `var_type` FROM `fightmessage_vars` WHERE `msg_id` = ".$this->getDetail ('msg_id')." AND `var_num` = ".$i);
				
				if ($var_type == "mob_name") {
					$Mob = new Mob ($var['value']);
					
					// work out how to format their name, with "an" or "a"?
					$mob_name = $Mob->getDetail ('name');
					
					if ($Mob->getDetail ('requires_indef_art')) {
						// do we need "a" or "an"?
						$fc = $mob_name[0];
						if ($fc == "a" || $fc == "e" || $fc == "i" || $fc == "o" || $fc == "u") {
							$mob_name = "an ".$mob_name;
						} else {
							$mob_name = "a ".$mob_name;
						}
					}
					
					$t_string = str_replace ("[".$i."]", $mob_name, $t_string);
				} else {
					$t_string = str_replace ("[".$i."]", $var['value'], $t_string);
				}
				
				$i++;
			}
		}
		
		$t_string = ucfirst ($t_string);
		
		return $t_string;
	}
	
	/**
	* Gets object which holds all the user's game information, like HP and XP.
	*
	* @return Character
	*/
	public function getCharacter () {
		return new Character ($this->getDetail ('user_id'));
	}
	
	public function getMob () {
		return new Mob ($this->getDetail ('mob_id'));
	}
	
	public static function addMessage ($msg_id, $user_id, $mob_id, $vars, Database $Database) {
		$Database->query ("INSERT INTO `fightmessage_fight` SET `msg_id` = ".(int) $msg_id.", `user_id` = ".(int) $user_id.", `mob_id` = ".(int) $mob_id.", `time` = UNIX_TIMESTAMP ()");
		$f_msg_id = $Database->getLastId();
		
		if (count ($vars)) {
			foreach ($i=0,$num_vars=count ($vars); $i<=$num_vars; $i++) {
				$Database->query ("INSERT INTO `fightmessage_fight_vars` SET `fight_msg_id` = ".$f_msg_id.", `num` = ".$i.", `value` = '".$vars[$i]."'");
			}
		}
		
		return $f_msg_id;
	}
}

?>