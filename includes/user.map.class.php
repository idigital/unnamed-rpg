<?php

/**
* Gets the data about the user's current map area
*/

class UserMap {
	private $user_id = null, $Database;
	
	public function __construct ($user_id) {
		//  We'll need a database.. Fortunately, constants stick around even inside private scope!
		$this->Database = new Database (database_server, database_user, database_password, database_name);
		$this->user_id = (int) $user_id;
		
		// set all its attributes by nabbing them from the database
		$data = mysql_fetch_assoc ($this->Database->query ("SELECT * FROM `user_map` WHERE `user_id` = ".$this->user_id));
		// if nothing was set (meaning nothing was found) then set a flag to say the item doesn't exist
		$this->user_exists = true;
		if (empty ($data)) {
			$this->user_exists = false;
		} else {
			// it does exist, so we can set properties
			$this->properties = $data;
		}
	}
	
	public function getDetail ($detail) {
		return $this->properties[$detail];
	}
	
	/**
	* Updates the database and our local property. Parameters are escaped by the method so no need before hand.
	*
	* @param string Attribute name
	* @param string New value
	* @return User $this, for chaining
	*/
	public function setDetail ($detail, $value) {
		// Silently fail if we try to change the ID. That should never be changed
		if ($detail == 'user_id') {
			// just a notice, probably won't even show up in most environments
			trigger_error ('Attempted to change ID');
			return $this;
		}
	
		// try update the database first. capture the result
		$result = $this->getDatabase()->query ("UPDATE `user_map` SET `".$this->getDatabase()->escape ($detail)."` = '".$this->getDatabase()->escape ($value)."' WHERE `user_id` = ".$this->getId());
		
		// if the database updated well, then updated our local property
		if ($result) $this->properties[$detail] = $value;
		
		return $this;
	}
	
	public function getId () { return $this->getDetail ('map_id'); }
	public function getUser () { return new User ($this->getDetail ('user_id')); }
	public function getX () { return $this->getDetail ('x_co'); }
	public function getY () { return $this->getDetail ('y_co'); }
	public function getDatabase () { return $this->Database; }
}

?>