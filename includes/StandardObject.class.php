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
			$this->___Database = $___config['database'];
		
			// are there more than one attributes that make up the primary key?
			if (is_array ($___config['primary_key'])) {
				$___config['primary_sql'] = "";
				
				foreach ($___config['primary_key'] as $ele_id => $attr) {
					$___config['primary_sql'] .= "`".$attr."` = ".$___config['item_id'][$ele_id]." AND ";
				}
				$___config['primary_sql'] = trim ($___config['primary_sql'], "' AND '");
			} else {
				$___config['primary_sql'] = "`".$___config['primary_key']."` = ".$___config['item_id'];
			}
		
			// Get all the item's attributes
			$data = mysql_fetch_assoc ($___config['database']->query ("SELECT * FROM `".$___config['table']."` WHERE ".$___config['primary_sql']." LIMIT 1"));
			// Optimistic existance flag
			$this->___itemExists = true;
			
			// make sure that the primary_sql variable is added into ___config
			$this->___config['primary_sql'] = $___config['primary_sql'];
			
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
		// Make sure this item exists before trying to pull it's data. This is such a harsh error (it will halt the script)
		// because it should never happen. You should always check if an item exists yourself, and handle the error that way.
		// Allowing this function to continue whilst the item doesn't exist will through an error in array_key_exists, and
		// you won't get back what you're expecting.
		if (!$this->exists ()) {
			trigger_error ("Item doesn't exist, and so can't find the detail \"".$detail."\"", E_USER_ERROR);
		}
		
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
		$result = $this->getDatabase()->query ("UPDATE `".$this->___config['table']."` SET `".$this->getDatabase()->escape ($detail)."` = '".$this->getDatabase()->escape ($value)."' WHERE ".$this->___config['primary_sql']);
		
		// if the database updated well, then updated our local property
		if ($result) $this->___properties[$detail] = $value;
		
		return $this;
	}
	
	/**
	* Gets the ID of the object we're looking at.
	*
	* This is almost always the parameter that was passed to create the object.
	*
	* It's not necessarily an INT, but is usually since id's are often autoincremented ints.
	*
	* If this is an object created with a multiattribute primary key then don't use this function. Use
	* this::getIds() instead.
	*
	* @return mixed
	*/
	public function getId () {
		// don't bother if this is a multi primary key object
		if (is_array ($this->___config['primary_key'])) return null;
	
		return $this->getDetail ($this->___config['primary_key']);
	}
	
	/**
	* Returns an array of the attributes made up for the key.
	*
	* This'll be in the form of
	* 	array ('attr_name' => "value", 'attr_name' => "value", ...)
	*
	* @return array
	*/
	public function getIds () {
		if (!is_array ($this->___config['primary_key'])) return null;
		
		$ids = array ();
		
		foreach ($this->___config['primary_key'] as $key) {
			$ids[$key] = $this->getDetail ($key);
		}
		
		return $ids;
	}
	
	/**
	* Returns the part of the SQL which uniquely identifies this object.
	*
	* Usually that means the "`user_id` = 12" bit, but this is more helpful when there's more than one
	* primary key.
	*
	* @return string
	*/
	public function getSQLWhereClause () {
		return $this->___config['primary_sql'];
	}
	
	protected function getDatabase () { return $this->___Database; }
}

?>