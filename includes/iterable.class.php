<?php

abstract class Iterable implements Iterator {
    private $position = 0;
    private $elements;

    public function __construct() {
		$this->Database = new Database (database_server, database_user, database_password, database_name);
	
        $this->position = 0;
		
		$this->elements = $this->getElements();
    }

    function rewind() {
        $this->position = 0;
    }

    function current() {
        return $this->elements[$this->position];
    }

    function key() {
        return $this->position;
    }

    function next() {
        ++$this->position;
    }
	
	function count () {
		return count ($this->elements);
	}
	
    function valid() {
        return isset($this->elements[$this->position]);
    }
	
	abstract protected function getElements();
	
	public function getDatabase () { return $this->Database; }
}

?>