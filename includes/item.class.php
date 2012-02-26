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
	* This will often return 0 when the item is unequipable.
	*
	* @return int
	*/
	public function getStrength () {
		$strength = (int) $this->getDatabase()->getSingleValue ("SELECT `strength_increase` FROM `item_fight` WHERE `item_id` = ".$this->getId());
		
		return $strength;
	}
	
	/**
	* Gets details about possible actions this item has.
	*
	* Returns an array copy of the item_action table, which has elements 'item_id', 'action_type', and
	* 'modifier'.
	*
	* @return array
	*/
	public function getActions () {
		$actions = array ();
		
		$qry_actions = $this->getDatabase()->query ("SELECT * FROM `item_action` WHERE `item_id` = ".$this->getId());
		while ($action = mysql_fetch_assoc ($qry_actions)) {
			$actions[] = $action;
		}
	
		return $actions;
	}
	
	public function doAction ($action, Character $Character) {
		// pesimism!
		$success = false;
		
		// is this a valid action?
		foreach ($this->getActions() as $g_action) {
			if ($g_action['action_type'] == $action) {
				$success = true;
				$modifier = $g_action['modifier'];
				break;
			}
		}
		
		// do they at least one of this item?
		if ($success && $Character->getInventory()->numHolding($this)) {
			if ($action == 'Drink' && $this->getDetail ('type') == 'Healing Potion') {
				// So they want to be healed! Increase their hit points by the modifier
				$Character->heal ($modifier);
				
				// remove the item
				$Character->getInventory()->alterBy ($this, -1);
				
				$success = true;
			} elseif ($action == 'Destroy') {
				$Character->getInventory()->alterBy ($this, -1);
			} elseif ($action == 'Equip') {
				// Right hand is the only equipable place at the moment. Unequip whatever is there.
				$Character->unequipItem ('righthand');
				
				$Character->equipItem ('righthand', $this);
				$success = true;
			}
		} else {
			$success = false;
		}
		
		return $success;
	}
}

?>