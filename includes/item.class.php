<?php

/**
* Manages an item
*/

class Item extends StandardObject {
	public function __construct ($item_id) {
		//  We'll need a database.. Fortunately, constants stick around even inside private scope!
		$Database = new Database (database_server, database_user, database_password, database_name);
	
		$config = array (
			'table' => "item",
			'database' => $Database,
			'item_id' => $item_id,
			'primary_key' => "item_id"
		);
		
		parent::__construct ($config);
	}
	
	/**
	* Gets the name of the object.
	*
	* Also lets you ask for the "a" or "an" part to be added to the name.
	*
	* @param bool If true, (a|an) will be prepended as appropriate.
	* @return string
	*/
	public function getName ($require_def=false) {
		// work out how to format the name, with "an" or "a"?
		$name = $this->getDetail ('name');
		
		if ($require_def) $name = indef_article ($name);
		
		return $name;
	}
	
	/**
	* Finds what the maximum amount a user can carry of this item.
	*
	* @return int
	*/
	public function getMaxQty () {
		return $this->getDetail ('max_quantity');
	}
	
	/**
	* When equiped how much strength does this item give the user?
	*
	* This will often return 0 when the item is unequippable. Zero is also a valid figure. Use
	* ::is_equippable to test before trying to get strength.
	*
	* @return int
	*/
	public function getStrength () {
		$strength = (int) $this->getDatabase()->getSingleValue ("SELECT `strength_increase` FROM `item_fight` WHERE `item_id` = ".$this->getId());
		
		return $strength;
	}
	
	/**
	* Declares if an item is equipable or not.
	*
	* An item is only equipable if it has an `item_equippable` row, and then it's restricted to
	* a certain position, which must be passed for $where. If the item isn't equippable in this
	* place, this function will return false
	*
	* @param string Where the item should be equiped to
	* @return bool
	*/
	public function is_equippable ($where) {
		return (bool) $this->getDatabase()->getSingleValue ("SELECT `item_id` FROM `item_equippable` WHERE `item_id` = ".$this->getId()." AND `position` = '".$this->getDatabase()->escape ($where)."'");
	}
	
	/**
	* Gets details about possible actions a $Character has with this item.
	*
	* Character is passed into this method because we need to know a few things about them before
	* we can decide if they can do certain actions. For instance, a user shouldn't be able to
	* drink a health potion whilst already at full health.
	*
	* Returns an array of actions that can be done by the Character. Required indexs are 'action_type'
	* and 'in_fight', which match up to the `item_action` columns of the same name. Also, there's an
	* 'anchor', which is a human readable description of the action, usually to be output to the user.
	*
	* There is always a 'params' index, which contains an array of data specific to that action,
	* and must be sent back with the action if it is to be carried out. For instance, the Equip action
	* requires a position to equip to. Often it's just an empty array though.
	*
	* It's possible that this returns an empty array when there are no actions available.
	*
	* @param Character Player who will be doing this action
	* @return array
	*/
	public function getActions (Character $Character) {
		$actions = array ();
		
		$qry_actions = $this->getDatabase()->query ("SELECT * FROM `item_action` WHERE `item_id` = ".$this->getId());
		while ($action = mysql_fetch_assoc ($qry_actions)) {
			// If the Character is in a fight, can we still do this action?
			if ($Character->getFightData() !== false && (bool) $action['in_fight'] == false) {
				// We can't, so just skip this iteration.
				continue;
			}
		
			if ($action['action_type'] === "Destroy") {
				$actions[] = array (
					'anchor' => "Destroy item",
					'action' => "Destroy",
					'params' => array ()
				);
			} elseif ($action['action_type'] === "Drink") {
				// The only drinks at the moment are healing ones, so only bother letting them use it if they're not
				// at full health
				if ($Character->getHealth() < $Character->getMaxHealth()) {
					$actions[] = array (
						'anchor' => "Drink to heal ".$action['modifier']." HP.",
						'action' => "Drink",
						'heal_by' => $action['modifier'], # I'll be getting rid of the modifier column soon.
						'params' => array ()
					);
				}
			} elseif ($action['action_type'] === "Equip") {
				// Where can this item be equipped?
				$qry_equippable = $this->getDatabase()->query ("SELECT `position` FROM `item_equippable` WHERE `item_id` = ".$this->getId());
				if (mysql_num_rows ($qry_equippable)) {
					
					// Define some natural language for the positions
					$language = array (
						'righthand' => "right hand",
						'lefthand' => "left hand",
						'head' => "head"
					);
				
					while ($equippable = mysql_fetch_assoc ($qry_equippable)) {
						// Does this user already have something equipped in this place?
						if ($Character->getEquippedItem ($equippable['position']) === null) {
							$actions[] = array (
								'anchor' => "Equip this to your ".$language[$equippable['position']],
								'action' => "Equip",
								'params' => array ('position' => $equippable['position'])
							);
						}
					}
				}
			}
		}
	
		return $actions;
	}
	
	public function doAction ($action, $params, Character $Character) {
		// pesimism!
		$success = false;
		
		// is this a valid action?
		foreach ($this->getActions($Character) as $actions) {
			// we only care about this action, so skip ahead till we find it...
			if ($actions['action'] === $action) {
				// have we been given all the required parameters for this action?
				if (array_diff (array_keys ($actions['params']), array_keys ($params)) !== array ()) {
					// we don't have all the same keys in our params, and since they're all required we won't
					// be able to equip this item.
					$success = false;
				} else {
					$success = true;
					
					$action_data = $actions;
				}
				
				break;
			}
		}
		
		// do they at least one of this item?
		if ($success && $Character->getInventory()->numHolding($this) > 0) {
			if ($action == 'Drink' && $this->getDetail ('type') == 'Healing Potion') {
				// So they want to be healed! Increase their hit points by the modifier
				$Character->heal ($action_data['heal_by']);
				
				// remove the item from their inventory
				$Character->getInventory()->alterBy ($this, -1);
				
				$success = true;
			} elseif ($action == 'Destroy') {
				$Character->getInventory()->alterBy ($this, -1);
			} elseif ($action == 'Equip') {
				// Unequip whatever is already there.
				$Character->unequipItem ($params['position']);
				$Character->equipItem ($params['position'], $this);
				
				$success = true;
			}
		} else {
			$success = false;
		}
		
		return $success;
	}
}

?>