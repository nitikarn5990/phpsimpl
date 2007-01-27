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
     * @var bool
     */
    var $connected;
    /**
     * @var array
     */
    var $results;
    /**
     * @var string
     */
    var $config;
    
    /**
	* Class Constructor
	*
	* Creates a DB Class with all the information to use the DB
	*
	* @return NULL
	*/
    function DB(){
		$this->connected = false;	
    	$this->query_count = 0;
    }
    
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
		// Save the config till we are ready to connect
		if (!$this->connected)
			$this->config = array($server,$username,$password,$database);
		
		return true;
	}
	
	function DbConnect(){
		if ($this->connected)
			return true;
			
		// Use the Global Link
		global $db_link;
		
		// If we are not connected
		if (is_array($this->config)){
			// Set all the local variables
			$this->database = $this->config[3];
			
			// Connect to MySQL
			$db_link = @mysql_connect($this->config[0], $this->config[1], $this->config[2]);
	
			// If there is a link
	    	if ($db_link){
				mysql_select_db($this->database);
				$this->connected = true;
				unset($this->config);
	    		return true;
	    	}
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
	function Query($query, $db = '', $cache = true) {
   		// Use the Global Link
		global $db_link;
		
		// Change the DB if needed
		if ($db != '' && $db != $this->database){
			$old_db = $this->database;
			$this->Change($db);
		}
		
		if (DEBUG_QUERY === true){
			echo '<pre class="debug query">QUERY:' . "\n";
			print_r($query);
			echo '</pre>';
		}
		
		$is_cache = false;
		
		// Check for Query Cache
		if ($cache && QUERY_CACHE && strtolower(substr($query,0,6)) == 'select'){
			$cache_file = FS_CACHE . 'query_' . bin2hex(md5($query, TRUE)) . '.cache.php';
			$is_cache = true;
			
			if (is_file($cache_file)){
				$this->results = unserialize(file_get_contents($cache_file));
				return $this->results;
			}
		}
		
		// Make sure we are connected
		$this->DbConnect();
		
		// Do the Query
    	$result = mysql_query($query, $db_link) or $this->Error($query, mysql_errno(), mysql_error());
    	// Increment the query counter
    	$this->query_count++;
    	
    	// Change the DB back is needed
    	if ($db != '' && $db != $this->database)
    		$this->Change($old_db);
    		
    	// Cache the Query if possible
    	if ($is_cache){
    		// Create the results array
    		while($info = mysql_fetch_array($result))
    			$this->results[] = $info;
    		
    		$cache = serialize($this->results);
    		$fp = fopen($cache_file ,"w");
			fwrite($fp,$cache);
			fclose($fp);
			chmod ($cache_file, 0777);
    	}
    	
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
		global $mySimpl;
		
		// Make sure we are connected
		$this->DbConnect();
		
		// Clear the Query Cache
		$mySimpl->Cache('clear_query');
		
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
		// If Connected
 		if ($this->connected){
			// Use the Global Link
			global $db_link;
 		
    		return @mysql_close($db_link);
 		}
 		
    	return true;
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
		if (QUERY_CACHE)
			return array_shift($this->results);
		else
			return mysql_fetch_array($result, MYSQL_ASSOC);
	}

	/**
	 * Number of Rows Returned
	 * 
	 * @param $result The result that was returned from the database
	 * @return int
	 */
	function NumRows($result) {
		if (QUERY_CACHE)
			return count($this->results);
		else
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