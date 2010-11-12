<?php

/**
* Manages a user
*/

class User {
	private $user_id = null;
	private $Database;
	
	public function __construct ($user_id) {
		//  We'll need a database.. Fortunately, constants stick around even inside private scope!
		$this->Database = new Database (database_server, database_user, database_password, database_name);
	
		$this->user_id = (int) $user_id;
		
		// set all its attributes by nabbing them from the database
		$data = mysql_fetch_assoc ($this->Database->query ("SELECT * FROM `user` WHERE `user_id` = ".$this->user_id));
		// if nothing was set (meaning nothing was found) then set a flag to say the item doesn't exist
		$this->user_exists = true;
		if (empty ($data)) {
			$this->user_exists = false;
		} else {
			// it does exist, so we can set properties
			$this->properties = $data;
		}
	}
	
	public function getUserId () { return $this->user_id; }	public function getId () { return $this->user_id; }
	
	/**
	* Kind of lazy accessor. Give it an attribute name and it'll return what the database has. The properties list
	* is kept in sync with the database so this function doesn't actually need to talk to the database each time.
	*
	* You should bare in mind thought that this type of caching can only be done where you're not expecting any data
	* to change by concurrent users. If there's a single field that's likely to change, just add an exception and you'll
	* still get the benefits of certain cached data.
	*
	* @param string Attribute name
	* @return mixed Whatever the value of $detail is. If $detail isn't an attribute it'll return null, but null could
	* 				also be a valid value
	*/
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
		$result = $this->master_db->query ("UPDATE `website` SET `".$this->getDatabase()->escape ($detail)."` = '".$this->master_db->escape ($value)."' WHERE `website_id` = ".$this->getId());
		
		// if the database updated well, then updated our local property
		if ($result) $this->properties[$detail] = $value;
		
		return $this;
	}
	
	public function getDatabase () { return $this->Database; }
	
	/**
	* Works out the stored version of the user's password
	*
	* @return String Encoded password
	*/
	public static function password_hash ($password) {
		return md5 (md5 ($password));
	}
}

?>