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
	
	public function canHoldMore (Item $Item) {
		$currently_holding = $this->numberHolding ($Item);
		$max_holding = $Item->getMaxQty ();
		
		return (bool) $max_holding - $currently_holding;
	}
	
	/**
	* @return int
	*/
	public function numHolding (Item $Item) {
		return (int) $this->getDatabase()->getSingleValue ("SELECT `qty` FROM `user_item` WHERE `user_id` = ".$this->getId()." AND `item_id` = ".$Item->getId());
	}
	
	public function getId () { return $this->char_id; }
	public function getCharacter () { return $this->Character; }
}

?>