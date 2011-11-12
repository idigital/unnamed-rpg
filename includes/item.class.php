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
	
	public function getName ($require_def) {
		// work out how to format the name, with "an" or "a"?
		$name = $this->getDetail ('name');
		
		if ($with_indef_art) $name = indef_article ($name);
		
		return $name;
	}
	
	public function getMaxQty () {
		return $this->getDetail ('max_quantity');
	}
}

?>