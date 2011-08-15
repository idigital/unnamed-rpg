<?php

/**
*
* @abstract
*/
abstract class StandardObject {
	private $___Database, $___properties;
	public $___config;
	
	/**
	* Sets up the object ready to be used with accessors, all that good stuff.
	*
	* Every class that extends this one should call `parent::__construct()` and pass it a configuration array
	* which you set up just before it. The array should like look this:
	*
	* array (
	*	'table' => the table name which the primary object data will be collected from,
	*	'primary_key' => the unique identifier for each row and to be used in other tables as the foreign keys
	*	'database' => Database object to converse with,
	*	'item_id' => the ID (primary key) of the row you want made into an object. Only handles int IDs.
	* )
	*/
	function __construct ($___config) {
		$this->___config = $___config;
		
		// set an optimistic flag so we know how everything went later
		$success = true;
		
		// we need a Database, which should be an object
		if (is_empty ($___config['database']) || !is_object ($___config['database'])) {
			trigger_error ('Configuration option ["database"] expected to be Database but is not', E_USER_WARNING);
			
			$success = false;
		}
		// so it's an object, but is it a Database object?
		if (get_class ($___config['database']) !== "Database") {
			trigger_error ('Configuration option ["database"] expected to be Database, '.get_class ($___config['database']).' give', E_USER_WARNING);
			
			$success = false;
		}
		
		// It's definitely what we're looking for. We also need a table name, otherwise we have no idea where to get the data from.
		if (is_empty ($___config['table'])) {
			trigger_error ('Congifuration option ["table"] must be set', E_USER_WARNING);
			
			$success = false;
		}
		
		// And finally we might need a primary key to identify which entity we want to make into an object
		if (is_empty ($___config['primary_key'])) {
			// For now, just drop out. But in the future we could probably guess the primary key.
			trigger_error ('Congifuration option ["table"] must be set', E_USER_WARNING);
			
			$success = false;
		}
		
		// Only bother trying if we were successful
		if ($success) {
			// Get all the item's attributes
			$data = mysql_fetch_assoc ($___config['database']->query ("SELECT * FROM `".$___config['table']."` WHERE `".$___config['primary_key']."` = ".$___config['item_id']." LIMIT 1"));
			// Optimistic existance flag
			$this->___itemExists = true;
			// Does it actually exist?
			if (!empty ($data)) {
				// it does exist, so we can set properties
				$this->___properties = $data;
			} else {
				$this->___itemExists = false;
			}
		}
	}
	
	/**
	* Using data from the construct, is this object usable?
	*
	* It'd be not usable if the item ID we were given wasn't found. That would make most of this object useless.
	* Because of that, you should always call this after you've made an object which extends this class.
	*
	* @return bool Does the item exist, or not?
	*/
	public function exists () {
		return (bool) $this->___itemExists;
	}
	
	/**
	* Kind of lazy accessor. Give it an attribute name and it'll return what the database has. The properties list
	* is kept in sync with the database on each ::setDetail so this function doesn't actually need to talk to the
	* database each time.
	*
	* You should bare in mind though that this type of caching can only be done where you're not expecting any data
	* to change by concurrent users. If there's a single field that's likely to change, just add an exception (by 
	* overriding this class) and you'll still get the benefits of certain cached data.
	*
	* @param string Table attribute name
	* @return mixed Whatever the value of $detail is. If $detail isn't an attribute it'll return PHP null, but null could
	*               also be a valid value
	*/
	public function getDetail ($detail) {
		if (array_key_exists ($detail, $this->___properties)) {
			return $this->___properties[$detail];
		} else {
			return null;
		}
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
		if ($detail === $this->___config['primary_key']) {
			// just a notice, probably won't even show up in most environments
			trigger_error ('Attempted to change ID');
			return $this;
		}
	
		// try update the database first. capture the result
		$result = $this->getDatabase()->query ("UPDATE `".$this->___config['table']."` SET `".$this->getDatabase->escape ($detail)."` = '".$this->getDatabase->escape ($value)."' WHERE `".$this->___config['primary_key']."` = ".$this->getId());
		
		// if the database updated well, then updated our local property
		if ($result) $this->properties[$detail] = $value;
		
		return $this;
	}
	
	protected function getDatabase () { return $this->___Database; }
	public function getId () { return $this->getDetail ($this->___config['primary_key']); }
}

?>