<?php

/**
* Gets the data about the user's current map area
*/

class UserMap extends StandardObject {
	public function __construct ($user_id) {
		//  We'll need a database.. Fortunately, constants stick around even inside private scope!
		$Database = new Database (database_server, database_user, database_password, database_name);
	
		$config = array (
			'table' => "user_map",
			'database' => $Database,
			'item_id' => $user_id,
			'primary_key' => "user_id"
		);
		
		parent::__construct ($config);
	}
	
	public function getId () { return $this->getDetail ('map_id'); }
	public function getUser () { return new User ($this->getDetail ('user_id')); }
	public function getX () { return $this->getDetail ('x_co'); }
	public function getY () { return $this->getDetail ('y_co'); }
}

?>