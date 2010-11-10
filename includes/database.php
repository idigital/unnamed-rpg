<?php

/**
* Handles database connections. Very much just a utility class.
*/
class Database {
	private $server, $user, $password, $database = null;
	
	/**
	* Construtor
	*
	* @param String Server to connect to
	* @param String User to connect with
	* @param String Password to authenticate with
	* @param String Database name
	*/
	function __construct ($server, $user, $password, $database) {
		$this->server = $server;
		$this->user = $user;
		$this->password = $password;
		$this->database = $database;
	}
	
	/**
	* Creates a connection to the database
	*
	* This needs to happen on every relavant place since PHP closes connections on file end, which
	* means we can't just store the connection.
	*
	* @return Resource mysqllink that can be used on queries
	*/
	private function connect () {
		//  Connect to the server
		$connection = mysql_connect ($this->server, $this->user, $this->password);
		
		//  If the connection is false, it means we couldn't connect for some reason. Since this is a database
		//  driven site, we should do a fatal error, not just the warning that we usually get.
		if ($connection === false) trigger_error ("Database connection could not be established", E_USER_ERROR);
		
		mysql_select_db ($this->database);
		
		return $connection;
	}
	
	/**
	* Runs a query, and gives it's result to be passed to another function
	*
	* Since we need to run connect() every time, it's just easier to do that in here.
	*
	* @see mysql_query
	* @param String SQL query to be run. No formatting is done on this
	* @return Resource Can be passed to mysql_fetch_array, or whatever
	*/
	public function query ($sql) {
		return mysql_query ($sql, $this->connect());
	}
	
	/**
	* Convenience method to escape strings
	*
	* @param String to be escaped
	* @return String escaped
	*/
	public function escape ($string) {
		return mysql_real_escape_string ($string, $this->connect());
	}
	
	/**
	* Gets the value returned by a query
	*
	* Convenience method because I'm tired of doing a mysql_fetch_assoc for one value from the
	* query, and having to put the array returned into a seperate variable first. This function
	* simply returns the value.
	* 
	* Remember that the query must evaluate to a single field of a single row returned.
	*
	* @param string An SQL line to run
	* @return mixed Whatever the result of the query is
	*/
	public function getSingleValue ($query) {
		$tmp_value = mysql_fetch_row ($this->query ($query));
		return $tmp_value[0];
	}
	
	public function getLastInsertId () { return mysql_insert_id ($this->connect()); }
	public function getLastId () { return $this->getLastInsertId(); }

	/**
	* Echos the SQL given to it, and the error message it may have caused
	*
	* To be run after the query has been (and likely failed). Obviously only for debugging.
	*
	* @param string SQL
	* @return void. It does echo though
	*/
	public function SQLDebug ($qry) {
		echo "<p>The SQL query that was just run was: <b>".$qry."</b></p>\n";
		// was there an error message?
		$error_message = mysql_error ();
		if (!empty ($error_message)) echo "<p>The error message we were given was: <b>".$error_message."</b>. Remember that that could be an old error message, from a previous query though.</p>\n";
	}
}

define ('database_server', 'localhost'); //  You can usually leave this as it is
define ('database_user', 'root');
define ('database_password', '');
define ('database_name', 'unnamedrpg');

$Database = new Database (database_server, database_user, database_password, database_name);

?>