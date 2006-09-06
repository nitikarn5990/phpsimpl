<?php
/**
* Database Interaction Class
*
* Used to interact with the database in a sane manner
*
* @author Nick DeNardis <nick.denardis@gmail.com>
*/
class DB {
    /**
	* @var string 
	*/
    var $database;
    /**
	* @var int 
	*/
    var $query_count;
    
    /**
	* Class Constructor
	*
	* Creates a DB Class with all the information to use the DB
	*
	* @return NULL
	*/
    function DB(){ }
    
    /**
	 * Connect to DB
	 *
	 * Connect to the database of your choice
	 *
	 * @param $server A String with the server name
	 * @param $username A String with the user name
	 * @param $password A String with the Password
	 * @param $database A String with the databse to connect to
	 * @return bool
	 */
	function Connect($server=DB_HOST, $username=DB_USER, $password=DB_PASS, $database=DB_DEFAULT){
		// Use the Global Link
		global $db_link;
		
		// Set all the local variables
		$this->database = $database;
		$this->query_count = 0;
		
		// Connect to MySQL
		$db_link = @mysql_connect($server, $username, $password);

		// If there is a link
    	if ($db_link){
			mysql_select_db($database);
    		return true;
    	}
		
		return false;
	}
	
	/**
	 * Execute a Query
	 * 
	 * Execute a query on a particular databse
	 * 
	 * @param $query The query to be executed
	 * @param $db THe optional alternative database
	 * 
	 */
	function Query($query, $db = '') {
   		// Use the Global Link
		global $db_link;
		
		// Change the DB if needed
		if ($db != '' && $db != $this->database){
			$old_db = $this->database;
			$this->Change($db);
		}
		
		// Do the Query
    	$result = mysql_query($query, $db_link) or $this->Error($query, mysql_errno(), mysql_error());
    	// Increment the query counter
    	$this->query_count++;
    	
    	// Change the DB back is needed
    	if ($db != '' && $db != $this->database)
    		$this->Change($old_db);
    	
    	// Return the query results
    	return $result;
	}
	
	/**
	 * Perform a Query
	 * 
	 * Use a smart way to create a query from abstract data
	 * 
	 * @param $table String of the table that the query is going to act on
	 * @param $data Array of the data that is going to be inserted/updated
	 * @param $action Either "update" or "insert"
	 * @param $parameters String of the additional things that need to go on like "item_id='5'"
	 * @param $db String of a different database if this is going to happen on another location
	 * @return result
	 */
	function Perform($table, $data, $action = 'insert', $parameters = '', $db = '') {
		// Use the Global Link
		global $db_link;
		
		// Decide how to create the query
		if ($action == 'insert'){
			// Start the Query
			$query = 'INSERT INTO `' . trim($table) . '` (';
			$values = '';
			// Add each column in
			foreach($data as $column=>$value){
				// Create the First Half
				$query .= '`' . $column . '`, ';
				// Create the Second Half
				switch ((string)$value) {
					case 'now()':
						$values .= 'now(), ';
					break;
					case 'null':
						$values .= 'null, ';
					break;
					default:
						$values .= '\'' . $this->Prepare($value) . '\', ';
					break;
				}
			}
			// Conntect the columns with the values
			$query = substr($query, 0, -2) . ') VALUES (' . substr($values, 0, -2) . ')';
		}else if($action == 'update') {
			// Start the Query
			$query = 'UPDATE `' . $table . '` SET ';
			foreach($data as $column=>$value){
				switch ((string)$value) {
					case 'now()':
						$query .= '`' . $column . '` = now(), ';
					break;
					case 'null':
						$query .= '`' . $column .= '` = null, ';
					break;
					default:
						$query .= '`' . $column . '` = \'' . $this->Prepare($value) . '\', ';
					break;
				}
			}
			// Finish off the query
			$query = substr($query, 0, -2) . ' WHERE ' . $parameters;
		}
		
		return $this->Query($query, $db);
	}
	
	/**
	 * Close the database connection
	 * 
	 * @return bool
	 */
	function Close(){
		// Use the Global Link
		global $db_link;
 
    	return @mysql_close($db_link);
	}
	
	/**
	 * Change the Database
	 * 
	 * @param $database A String with the new database name
	 * @return bool
	 */
	function Change($database){
		// Use the Global Link
		global $db_link;
 		
    	if ($db_link){
			@mysql_select_db($database);
			return true;
    	}
    	
		return false;
	}
	
	/**
	 * Throw an error
	 * 
	 * Display the Error to the Screen and Record in DB then Die()
	 * 
	 * @todo Log Error in DB
	 * @param $query The query that was executed
	 * @param $errno The error number
	 * @param $error The actual text error
	 * @return null
	 */
	function Error($query, $errno, $error) {
		// Record the error in the Log
		
		// Close the Database Connection
		$this->Close();
		
		// Kill the Script
  		die('<div class="db-error"><h1>' . $errno . ' - ' . $error . '</h1><p>' . $query . '</p></div>');
	}
	
	/**
	 * Fetch the results Array
	 * 
	 * @param $result The result that was returned from the database
	 * @return array
	 */
	function FetchArray($result) {
		return mysql_fetch_array($result, MYSQL_ASSOC);
	}

	/**
	 * Number of Rows Returned
	 * 
	 * @param $result The result that was returned from the database
	 * @return int
	 */
	function NumRows($result) {
		return mysql_num_rows($result);
	}
	
	/**
	 * The ID that was just inserted
	 * 
	 * @return int
	 */
	function InsertID() {
		return mysql_insert_id();
	}

	/**
	 * Free Resulting Memory
	 * 
	 * @param $result The result that was returned from the database
	 * @return bool
	 */
	function FreeResult($result) {
		return mysql_free_result($result);
	}
	
	/**
	 * Get the Field Information
	 * 
	 * @param $result The result that was returned from the database
	 * @return object
	 */
	function FetchField($result) {
		return mysql_fetch_field($result);
	}
	
	/**
	 * Get the Field Length
	 * 
	 * @param $result The result that was returned from the database
	 * @param $field The field number that we are intrested in getting the info for
	 * @return object
	 */
	function FieldLength($result,$field) {
		return mysql_field_len($result,$field);
	}
	
	/**
	 * Format the string for output from the Database
	 * 
	 * @param $string A string to be outputted from the database
	 * @return object
	 */
	function Output($string) {
		return htmlspecialchars($string);
	}

	/**
	 * Format the string for input into the Database
	 * 
	 * @param $string A string that is going to be inserted into the database
	 * @return object
	 */
	function Prepare($string) {
		return (is_numeric($string))?addslashes($string):mysql_real_escape_string($string);
	}
}
?>
