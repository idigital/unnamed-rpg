<?php

/**
* Manages an inventory of a character
*/

class Inventory {
	private $char_id, $User;

	function __construct ($char_id) {
		$this->char_id = $char_id;
		$this->Character = new Character ($char_id);
	}
	
	public function getItems () {
		return new InventoryIterator ($this->getCharacter());
	}
	
	/**
	* Checks if the character will be able to hold $qty more of this item.
	*
	* Each item has a `max_quantity` which is the most of the item a single character can carry.
	*
	* @param Item Item to check against
	* @param int Number to try to add
	* @return bool
	*/
	public function canHoldMore (Item $Item, $qty = 1) {
		$currently_holding = $this->numberHolding ($Item);
		$max_holding = $Item->getMaxQty ();
		
		return (($max_holding - $currently_holding) > 0);
	}
	
	/**
	* Finds out how many of an item the character is currently in posession of.
	*
	* @param Item Which item you're looking up
	* @return int
	*/
	public function numHolding (Item $Item) {
		return (int) $this->getDatabase()->getSingleValue ("SELECT `qty` FROM `user_item` WHERE `user_id` = ".$this->getId()." AND `item_id` = ".$Item->getId());
	}
	
	public function getId () { return $this->char_id; }
	public function getCharacter () { return $this->Character; }
}

/**
* Iterator to get all the items which the user has. It will return an array for each item with an 'Item' element,
* and a 'qty' element.
*/
class InventoryIterator extends Iterable {
	private $Char;
	
	function __construct (Character $Char) {
		$this->Char = $Char;
		
		parent::__construct ();
	}

	protected function getElements () {
		$all_items = $this->getDatabase()->query ("SELECT * FROM `user_item` WHERE `user_id` = ".$this->Char->getId()." AND `qty` > 0");
		
		$elements = array ();
		
		while ($item_line = mysql_fetch_assoc ($all_items)) {
			$elements[] = array (
				'Item' => new Item ($item_line['item_id']),
				'qty' => $item_line['qty']
			);
		}
		
		return $elements;
	}
}

?>