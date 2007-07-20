<?php
/**
 * DB Template Class
 * Needs to be extended to do anything out of the ordinary
 *
 * @author Nick DeNardis <nick.denardis@gmail.com>
 * @link http://code.google.com/p/phpsimpl/
 */
class DbTemplate extends Form {
	/**
	 * @var string 
	 */
	public $table;
	/**
	* @var string 
	*/
	protected $database;
	/**
	* @var string 
	*/
	public $primary;
	/**
	* @var string 
	*/
	public $search;
	/**
	* @var array 
	*/
	public $results = array();
	/**
	 * @var array
	 */
	private $join_class = array();
	/**
	 * @var array
	 */
	private $join_type = array();
	/**
	 * @var array
	 */
	private $join_on = array();
	/**
	 * @var int
	 */
	private $multi = 0;
	/**
	 * @var string
	 */
	private $conditions;

	/**
	 * DbTemplate Constructor
	 * 
	 * @param string $table Table name
	 * @param string $database Database name
	 * @return bool
	 */
	public function __construct($table, $database){
		global $db;

		$this->table = $table;
		$this->database = $database;

		// Pull the cache if available
		$cache = $this->Cache('get', 'table_' . $this->table . '.cache.php', '', '1 day');

		// Read the cache if possible
		if (USE_CACHE == true && $cache != ''){
			// Get the cached fields
			$this->fields = unserialize($cache);
			
			// Recover the primary key
			foreach($this->fields as $name=>$field){
				// Set the primary if it is
				if ($field->Get('primary') == 1)
					$this->primary = $name;
			}
		}else{
			// Get the fields and their properties
			$this->ParseTable();

			// If Use Cache try to save it
			if (USE_CACHE == true)
				$this->Cache('set', 'table_' . $this->table . '.cache.php', $this->fields);
		}
		
		// Set the local display
		$this->display = $this->GetFields();

		return true;
	}

	/**
	 * Main function that does all the work
	 *
	 * This function will soon be depricated, it was a result of lack of constructors in PHP4
	 *
	 * @param array $data In form field=>value
	 * @param array $required Field names
	 * @param array $labels array of field labels
	 * @param array $examples array of field examples
	 * @param string $table string of table name
	 * @param array $fields array of existing fields
	 * @param string $database string of the database name
	 * @return bool
	 */
	public function DbTemplate($data, $required=array(), $labels=array(), $examples=array(), $table='', $fields=array(), $database=''){
		$this->table = $table;
		$this->database = $database;

		// Pull the cache if available
		$cache = $this->Cache('get', 'table_' . $this->table . '.cache.php', '', '1 day');

		// Read the cache if possible
		if (USE_CACHE == true && $cache != ''){
			// Get the cached fields
			$this->fields = unserialize($cache);
			
			// Recover the primary key
			foreach($this->fields as $name=>$field){
				// Set the primary if it is
				if ($field->Get('primary') == 1)
					$this->primary = $name;
			}
		}else{
			// Get the fields and their properties
			$this->ParseTable();

			// If Use Cache try to save it
			if (USE_CACHE == true)
				$this->Cache('set', 'table_' . $this->table . '.cache.php', $this->fields);
		}
		
		// Set the local display
		$this->display = $this->GetFields();
		
		$this->SetRequired($required);
		$this->SetLabels($labels);
		$this->SetExamples($examples);
		$this->SetValues($data);
		
		return true;
	}

	/**
	 * Get the Item Information
	 *
	 * @param array $fields Names of all the return fields
	 * @return bool
	 */
	public function GetInfo($fields=array()){
		global $db;
		$select = '*';
		
		// Require a primary key
		if ($this->GetPrimary() == '')
			return false;

		Debug('GetInfo(), Primary Field: ' . $this->primary . ', Value: ' . $this->GetPrimary());

		// If there is a limiting field
		if (is_array($fields) && count($fields) > 0){
			foreach($fields as $field)
				$select .= ($this->IsField($field))?'`' . trim($field) . '`, ':'';
			$select = substr($select,1,-1);
		}

		// Add the rest of the query together
		$query = 'SELECT ' . $select . ' FROM `' . $this->table . '` WHERE `' . $this->primary . '` = ' . $this->GetPrimary() . ' LIMIT 1';
		$result = $db->Query($query, $this->database);
		
		Debug('GetInfo(), Query: ' . $query);

		// If there is atleast one result
		if ($db->NumRows($result) == 1){
			Debug('GetInfo(), Item Found');

			// Get the info
			$info = $db->FetchArray($result);

			// Loop through all the fields and set the values
			foreach($this->fields as $key=>$data)
				Form::SetValue($key, $info[$key]);
			
			return true;
		}

		Debug('GetInfo(), Item Not Found');
		return false;
	}

	/**
	 * Saves class information into the database table
	 *
	 * @param array $options config values
	 * @return bool
	 */
	public function Save($options=array()){
		global $db;
		$info = array();
		
		// Make sure the data validates
		if ($this->IsError() || !$this->Validate())
			return false;
			
		// Default rows to always update
		$updater = array('last_updated', 'updated_on');

		// Determine the save type
		if ($this->GetPrimary() != ''){
			$type = 'update';
			$extra = '`' . $this->primary . '` =' . $this->GetPrimary();
			
			Debug('Save(), Updating Item: ' . get_class($this) . ', Primary Key: ' . $this->GetPrimary());
		}else{
			$type = 'insert';
			$extra = '';
			
			Debug('Save(), Inserting New Item: ' . get_class($this));
			
			// Add more date fields to update
			array_push($updater, 'date_entered', 'created_on');
			
			// Automate the display order process
			if (is_object($options['display_order']) && $this->IsField('display_order')){
				// Default Display order
				$this->SetValue('display_order', 1);
				
				// Grab the next display order
				$last_item = $options['display_order']->GetAssoc('display_order', 'display_order','DESC',0,1);
				if (count($last_item) == 1)
					$this->SetValue('display_order', current($last_item) + 1);
					
				Debug('Save(), Display Order inserting at: ' . $this->GetValue('display_order'));
			}
		}
		
		// Set the new dates for updating
		foreach($updater as $key)
			if ($this->IsField($key))
				$this->SetValue($key, date("Y-m-d H:i:s"));
		
		// Get the values except for the omitted fields
		$fields = $this->GetFields();
		foreach($fields as $data)
			if ($this->Get('display', $data) >= 0)
				$info[$data] = $this->GetValue($data);

		// Check to see if we can connect
		if ($db->DbConnect()){
			// Do the Operation
			$db->Perform($this->table, $info, $type, $extra, $this->database);
	
			// Grab the ID if inserting
			if ($type == 'insert' && $this->primary != '')
				$this->SetPrimary($db->InsertID());
			
			Debug('Save(), Success Saving Item: ' . get_class($this) . ', Primary Key: ' . $this->GetPrimary());
			return true;
		}else{
			// Create file backup if no databse
			$filename = 'backup_' . $this->table . '_' . date("YmdHis") . '.php';
			$this->Cache('set', $filename, $this);

			// If the file is written we did all we can do for now
			if (is_file(FS_CACHE . $filename)){
				Debug('Save(), Datebase down, saving to: \'' . FS_CACHE . $filename . '\'');
				
				return true;
			}
		}
		
		Debug('Save(), Error Saving Item: ' . get_class($this));

		return false;
	}
	
	/**
	 * Insert the date from the object into the database
	 * Mainly used for tables without a primary key
	 *
	 * @return bool
	 */
	public function Insert(){
		return false;
	}
	
	/**
	 * Update the data in the table with a condition
	 * Mainly used for tables without a primary key
	 *
	 * @return bool
	 */
	public function Update($condition=''){
		return false;
	}
	
	/**
	 * Update an individual value in the database
	 *
	 * @param string $field
	 * @param string $value
	 * @return bool
	 */
	public function UpdateValue($field, $value){
		global $db;
		
		// Require a valid field
		if (!$this->IsField($field))
			return false;
		
		// Require a primary key
		if ($this->GetPrimary() == '')
			return false;
			
		Debug('UpdateValue(), Field: ' . $field . ', New Value: ' . $value);
		
		// Do the Operation
		$db->Perform($this->table, array($field => $value), 'update', '`' . $this->primary . '` = '. $this->GetPrimary(), $this->database);
		
		// Set the new value locally
		$this->SetValue($field, $value);
		
		return true;
	}
	
	/**
	 * Deletes the record from the database
	 *
	 * @param array $options config values
	 * @return bool
	 */
	public function Delete($options=array()){
		global $db;
		global $mySimpl;
		
		// If we can get the info then we can delete it
		if ($this->GetInfo()){
			Debug('Delete(), Item Found, Primary Field: ' . $this->primary . ', Value: ' . $this->GetPrimary());
			
			// Remove from display order if needed
			if (is_object($options['display_order']))
				while ($this->Move('down',$options)){}
		
			// Delete the entry
			$query = 'DELETE FROM `' . $this->table . '` WHERE `' . $this->primary . '` = ' . $this->GetPrimary() . ' LIMIT 1';
			$result = $db->Query($query, $this->database);
			
			// Clear the cache
			$mySimpl->Cache('clear_query');
			
			// If it did something the return that everything is gone
			if ($db->RowsAffected() == 1){
				Debug('Delete(), Success Saving Item: ' . get_class($this) . ', Primary Key: ' . $this->GetPrimary());
				return true;
			}
		}
		
		Debug('Delete(), Item Not Found, Primary Field: ' . $this->primary . ', Value: ' . $this->GetPrimary());
		
		return false;
	}
	
	/**
	 * Get a List from the Database
	 *
	 * Returns an array of objects from the Database according to criteria set
	 *
	 * @param $fields An array of field keys to return
	 * @param $order_by A string of a field key to order by (ex. "display_order")
	 * @param $sort A string on how to sort the data (ex "ASC" or "DESC")
	 * @param $offset An int on where to start returning, used mainly for page numbering
	 * @param $limit An int limit on the number of rows to be returned
	 * @return array
	 */
	public function GetList($fields=array(), $order_by='', $sort='', $offset='', $limit=''){
		global $db;
		$returns = array();
		
		// Push $this into the array
		array_unshift($this->join_class, $this);
		array_unshift($this->join_type, '');
		array_unshift($this->join_on, '');
		
		// Setup the return fields
		if (!isMultiArray($fields))
			array_unshift($returns, $fields);
		else
			$returns = $fields;
		
		// Loop through all the joined classes
		foreach($this->join_class as $key=>$class){
			// Get the values of the class
			$values = $class->GetValues();

			// Create the filters
			foreach($values as $name=>$value){
				$where .= ((string)$value != '')?'`' . $class->table . '`.' . $name . ' ' . (($class->Get('type',$name) == 'string' || $class->Get('type',$name) == 'blob')?'LIKE':'=') . ' \'' . $db->Prepare($value) . '\' AND ':'';
			
				// Create the search
				$search .= ($class->search != '')?'`' . $class->table . '`.' . $name . ' ' . (($class->Get('type',$name) == 'string' || $class->Get('type',$name) == 'blob')?'LIKE \'%' . $db->Prepare($class->search) . '%\'':' = \'' . $db->Prepare($class->search) . '\'') . ' OR ':'';
			}
			
			// Create the return fields
			if (is_array($returns[$key]) && count($returns[$key]) > 0){
				// Require primary key returned
				if ($class->primary != '' && !in_array($class->primary,$returns[$key]))
					$return .= '`' . $class->table . '`.' . $class->primary . ', ';
				
				// List all other fields to be returned
				foreach($returns[$key] as $field){
					if (!is_array($field))
						$return .= (trim($field) != '')?'`' . $class->table . '`.' . $field . ', ':'';
					else
						foreach($field as $sub_field)
							$return .= (trim($sub_field) != '')?'`' . $class->table . '`.' . $sub_field . ', ':'';
				}
			}else{
				$return .= '`' . $class->table . '`.*, ';
			}
			
			// Create the Joins
			if ($key > 0)
				$join .= ' ' . $this->join_type[$key] . ' JOIN `' . $this->join_class[$key]->table . '` ON (`' . $this->join_class[$key]->table . '`.' . $this->join_on[$key] . ' = `' . $this->table . '`.' . $this->join_on[$key] . ') ';
			
			// Add the search to the where
			if ($search != '')
				$where .= '(' . substr($search,0,-4) . ') AND ';
				
			// Reset the search
			$search = '';
		}
		
		// Special count case
		if ($returns[0] == 'count')
			$return = 'count(*) as `count`, ';
			
		// Get the order
		if(is_array($order_by)){
			foreach($order_by as $key=>$field)
				$order .= '`' . $this->table . '`.' . $field . ((is_array($sort) && $sort[$key] != '')?' ' . $sort[$key] . ', ':', ');
		}else{
			$order = ($order_by != '')?'`' . $this->table . '`.' . $order_by . ', ':'';
		}
		
		$order = substr($order,0,-2);
		
		// Get the sort
		if (!is_array($sort) && $sort != '')
			$order .= ' ' . $sort;
		
		$query = 'SELECT ' . substr($return,0,-2) . ' FROM `' . $this->table . '`';
		$query .= ($join != '')?substr($join,0,-1):'';
		$query .= ($where != '' || $this->conditions != '')?' WHERE ' . (($this->conditions != '')?$where . $this->conditions:substr($where,0,-5)):'';
		$query .= ($order != '')?' ORDER BY ' . $order:'';
		$query .= ($offset > 0 || $limit > 0)?' LIMIT ' . $offset . ', ' . $limit:'';
		
		// Do the Query
		$result = $db->Query($query, $this->database);
		Debug('GetList(), Query: ' . $query);

		// Get the results
		if ($returns[0] == 'count'){
			$info = $db->FetchArray($result);
			$this->results['count'] = $info['count'];		
		}else{
			while ($info = $db->FetchArray($result)){
				if ($this->primary != '')
					$this->results[$info[$this->primary]] = $info;
				else
					$this->results[] = $info;
			}
		}
		
		// Pop $this from the array
		array_shift($this->join_class);
		array_shift($this->join_type);
		array_shift($this->join_on);
		
		return $this->results;
	}
	
	/**
	 * Search the info from the DB
	 *
	 * Uses a smarter search engine to search through the fields and return certain fields, puts the results in a local $results array
	 *
	 * @param $keywords A string that we are going to be searching the DB for
	 * @param $search_fields An array of the field keys that we are going to be searching through
	 * @param $return_fields An array of the field keys that are going to be returned
	 * @return int
	 */
	public function Search($keywords, $search_fields, $return_fields){
		// Use the global db class
		global $db;
		$returns = array();
		
		// Push $this into the array
		array_unshift($this->join_class, $this);
		array_unshift($this->join_type, '');
		array_unshift($this->join_on, '');
		
		// Setup the return fields
		if (!isMultiArray($return_fields))
			array_unshift($returns, $return_fields);
		else
			$returns = $return_fields;
		
		// Split up the terms
		$terms = search_split_terms($keywords);
		$terms_db = search_db_escape_terms($terms);
		$terms_rx = search_rx_escape_terms($terms);
	
		// Create list of statements
		$parts = array();
		foreach($terms_db as $term_db){
			if (is_array($search_fields))
				foreach($search_fields as $field)
					$parts[] = '`' . $this->table . '`.' . $field . ' RLIKE \'' . $term_db . '\'';
			else
				$parts[] = '`' . $this->table . '`.' . $search_fields . ' RLIKE \'' . $term_db . '\'';
		}
			
		$parts = implode(' OR ', $parts);
		
		if ($this->conditions != '')
			$parts .= ' AND ' . $this->conditions;
		
		// Loop through all the joined classes
		foreach($this->join_class as $key=>$class){
			// Create the return fields
			if (is_array($returns[$key]) && count($returns[$key]) > 0){
				// Require primary key returned
				if (!in_array($class->primary,$returns[$key]))
					$return .= '`' . $class->table . '`.' . $class->primary . ', ';
				
				// List all other fields to be returned
				foreach($returns[$key] as $field){
					if (!is_array($field))
						$return .= (trim($field) != '')?'`' . $class->table . '`.' . $field . ', ':'';
					else
						foreach($field as $sub_field)
							$return .= (trim($sub_field) != '')?'`' . $class->table . '`.' . $sub_field . ', ':'';
				}
			}else{
				// Local class return values
				if (is_array($returns[$key])){
					foreach($returns[$key] as $field)
						$fields[] = '`' . $this->table . '`.' . $field;
					$return .= implode(', ', $fields);
				}else{
					$return .= '`' . $class->table . '`.*, ';
				}
			}
			
			
			// Create the Joins
			if ($key > 0)
				$join .= ' ' . $this->join_type[$key] . ' JOIN `' . $this->join_class[$key]->table . '` ON (`' . $this->join_class[$key]->table . '`.' . $this->join_on[$key] . ' = `' . $this->table . '`.' . $this->join_on[$key] . ') ';
		}
		
		$query = 'SELECT ' . substr($return,0,-2) . ' FROM `' . $this->table . '`' . $join . ' WHERE ' . $parts;
		$result = $db->Query($query, $this->database);
	
		$results = array();
		while($info = $db->FetchArray($result)){
			$info['score'] = 0;
			foreach($terms_rx as $term_rx){
				if (is_array($search_fields)){
					foreach($search_fields as $field)
						$info['score'] += preg_match_all("/$term_rx/i", $info[$field], $null);
				}else{
					$info['score'] += preg_match_all("/$term_rx/i", $info[$search_fields], $null);
				}
			}
			$results[] = $info;
		}
		
		// Pop $this from the array
		array_shift($this->join_class);
		array_shift($this->join_type);
		array_shift($this->join_on);
	
		uasort($results, 'search_sort_results');
		$this->results = $results;
		
		return count($results);
	}
	
	/**
	 * Get an Associative array
	 *
	 * @param string $field
	 * @param string $order_by field to order by (ex. "display_order")
	 * @param string $sort order of sort (ex. "ASC" or "DESC")
	 * @param int $offset
	 * @param int $limit
	 * @return array
	 */
	public function GetAssoc($field, $order_by='', $sort='', $offset='', $limit=''){
		$return = array();
		$this->results = array();
		
		Debug('GetAssoc(), Item: ' . get_class($this) . ', Field: ' . $field);
		
		// Get the list
		$this->GetList(array($field), $order_by, $sort, $offset, $limit);
		
		// If there are results returned
		if (is_array($this->results)){			
			// Loop through each results
			foreach($this->results as $data){
				if ($this->primary != '')
					$return[$data[$this->primary]] = $data[$field];
				else
					$return[] = $data[$field];
			}
		}
		
		Debug('GetAssoc(), Found: ' . count($return) . ' Items');
		
		return $return;
	}
	
	/**
	 * Set the conditions to be used for the GetList and Search
	 *
	 * @param string $conditions (ex. "date_entered LIKE '2007%'")
	 * @return bool
	 */
	public function SetConditions($conditions){
		// Set the conditions
		$this->conditions = $conditions;
		
		Debug('SetConditions(), Conditions on ' . get_class($this) . ' Set to: ' . $condititions);
		
		return true;
	}
	
	/**
	 * Move Item Up or Down in display_order
	 *
	 * @param string $direction (ex. "up" or "down")
	 * @param array $options config values
	 * @return bool
	 */
	public function Move($direction, $options = array()){
		global $db;
		
		// Get the Fields
		$fields = $this->GetFields();
		
		// Loop through each option
		foreach($options as $field=>$class){
			// Check to see if this key is in our fields
			if (in_array($field,$fields) && is_object($class)){
				// Get the option filters
				$filters = $class->GetValues();
				
				// Create the filter query
				foreach($filters as $filter=>$value){
					if ($value != '')
						$q_filter .= '`' . $filter . '` = \'' . $value . '\' AND ';
				}
				
				// Add the move type
				$q_filter .= '`' . $field . '`' . (($direction == 'up')?'<':'>') . '\'' . $this->GetValue($field) . '\' AND ';
				
				// Make the query
				$query = 'SELECT `' . $class->primary . '`,`' . $field . '` FROM `' . $this->table . '` WHERE ' . substr($q_filter,0,-4) . ' ORDER BY `' . $field . '` ' . (($direction == 'up')?'DESC ':'ASC ') . 'LIMIT 1';
				$result = $db->Query($query, $this->database);
				Debug('Move(), Query: ' . $query);
			
				// If there is a place to move
				if ($db->NumRows($result) == 1){
					// Get the new item item
					$new_order = $db->FetchArray($result);
					
					// Make sure they are not the same numbers
					if ($new_order[$field] != $this->GetValue($field)){
						// Update the old one
						$oldArray = array($field => $this->GetValue($field));
						$db->Perform($class->table, $oldArray, 'update', '`' . $class->primary . '`=\'' . $new_order[$class->primary] . '\'');
						
						// Update the New one
						$newArray = array($field => $new_order[$field]);
						$db->Perform($this->table, $newArray, 'update', '`' . $this->primary . '`=\'' . $this->GetPrimary() . '\'');
						
						// Set the value to this class
						$this->SetValue($field,$new_order[$field]);
						
						return true;
					}
				}
			}
		}
		
		
		return false;
	}
	
	/**
	 * Display Form
	 * 
	 * @param $display array
	 * @param $hidden array
	 * @param $options array
	 * @param $config array
	 * @param $omit array
	 * @return string
	 */
	public function Form($display='', $hidden=array(), $options=array(), $config=array(), $omit=array(), $multi=false){ 
		// Set the Displays
		$this->SetDisplay($display);
		$this->SetHidden($hidden);
		$this->SetOmit($omit);
		
		// If this is a mutli form, increment the
		if ($multi) $this->multi++;
		
		// Start the fieldset
		echo '<fieldset id="table_' . $this->table . (($multi)?'_' . $this->multi:'') . '"><legend>' . ucfirst(str_replace('_',' ',$this->table)) . ' Information</legend>' . "\n";
		
		// Show the fields
		foreach($this->display as $field)
				$this->fields[$field]->Form($options[$field], $config[$field], $multi, $this->prefix);
		
		// End the fieldset
		echo '</fieldset>' . "\n";
	}
	
	/**
	 * Display the Item in Plain Text
	 *
	 * @param $display An array of field keys that are going to be displayed to the screen
	 * @param $display An array of field keys that are going to be displayed to the screen
	 * @return null
	 */
	public function View($display='', $options = array()){
		// Set the Display
		$this->SetDisplay($display);

		echo '<table width="98%" border="0" cellspacing="0" cellpadding="2" class="item" summary="View Individual Information">';
		
		// Show the fields
		foreach($this->display as $field)
			$this->fields[$field]->View($options[$field]);
		
		echo '</table>';
	}
	
	/**
	 * Display a list of items
	 *
	 * @param $display An array of field keys that are going to be displayed in the list
	 * @param $format A string that will be passed through a sprintf() and use the ID and the String as the first and second parameters
	 * @param $options An associtive array with the key being the value for a field if there are output options for that field (ex. array("is_active"=>array("no","yes")))
	 * @param $force_check A bool to flag if this function should pull a GetList or not, default it will be in the case a get list has already been done it can be set to false
	 * @return NULL
	 */
	public function DisplayList($display='',$format=array(),$options=array(),$force_check=true){
		// Setup the Sort Sessions
		$_SESSION[$this->table . '_sort'] = ($_GET['sort'] != '')?$_GET['sort']:$_SESSION[$this->table . '_sort'];
		$_SESSION[$this->table . '_order'] = ($_GET['order'] != '')?$_GET['order']:$_SESSION[$this->table . '_order'];
		
		// Get the list of items if forced
		if ($force_check == true )
			$this->GetList($display, $_SESSION[$this->table . '_sort'], $_SESSION[$this->table . '_order']);
		
		// Setup the locals
		$count = count($this->results);
		$output = '';
		
		if ($count > 0){
			// Localize the sorts
			$sort = $_SESSION[$this->table . '_sort'];
			$order = ($_SESSION[$this->table . '_order'] == 'desc')?'asc':'desc';
			
			// Figure out the formatting
			$find = array('{$item_id}','{$data}');
			
			// Rearrange the Fields if there is a custom display
			$show = array();
			if(is_array($display)){
				foreach($display as $key=>$field)
					$show[$field] = ($this->IsField($field))?$this->fields[$field]->Label():ucfirst(str_replace('_',' ',$field));
			}else{
				foreach($this->fields as $key=>$field)
					$show[$key] = $field->Label();
			}
			$col_count = count($show);
			
			// Start the table
			$output .= '<table border="0" cellspacing="0" cellpadding="2" width="98%" class="small" summary="List of Records from ' . $this->Output($this->table) . '" id="table_' . $this->Output($this->table) . '">' . "\n";
			
			// Display the header
			$output .= '<tr>';
			foreach($show as $key=>$column){
				$output .= '<th scope="col" class="col_' . $key .'"><a href="' . $_SERVER['PHP_SELF'] . '?sort=' . $key . '&amp;order=' . $order . '" title="Order by ' . $column . '">';
				$output .= $column;
				if ($sort == $key)
					$output .= ($order == 'asc')?' &uarr;':' &darr;';
				$output .= '</a></th>';
			}
			$output .= '</tr>' . "\n";
			
			// Display each row
			$row = 1;
			foreach($this->results as $key=>$field){
				$output .= '<tr' . (($row%2 == 0)?' class="odd"':'') . '>';
				$col = 1;
				foreach($show as $name=>$value){
					$output .= '<td' . (($col == $col_count)?' class="last"':'') . '>';
					//$str = stripslashes($field[$name]);
					$str = $this->Output($field[$name]);
					
					if (is_array($options[$name]))
						$str = $options[$name][$str];
					else if ($options[$name] == 'move')
						$str = '<div class="center">' . (($row != 1)?'<a href="?item=' . $field[$this->primary] . '&amp;move=up">&uarr;</a>':'') . (($row != $count)?'<a href="?item=' . $field[$this->primary] . '&amp;move=down">&darr;</a>':'') . '</div>';
					
					if ($format[$name] != ''){
						$replace = array($field[$this->primary], $str);
						$str = str_replace($find, $replace, $format[$name]);
					}
					
					$int_options = $this->Get('options', $name);
					if (is_array($int_options) && $int_options[$str] != '')
						$str = $int_options[$str];
					
					$output .= $str . '</td>' . "\n";
					$col++;
				}
				
				$output .= '</tr>' . "\n";
				$row++;
			}
			
			// End the table
			$output .= '</table>';
		}else{
			$output .= '<p>Currently there are no items, please try again later.</p>' . "\n";
		}
		
		echo $output;
	}

	/**
	 * Set Primary Key Value
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public function SetPrimary($value){
		return $this->SetValue($this->primary, $value);
	}

	/**
	 * Get Primary Key Value
	 *
	 * @return mixed
	 */
	public function GetPrimary(){
		return $this->GetValue($this->primary);
	}

	/**
	 * Set Value of a Field
	 *
	 * @param string $field
	 * @param mixed $value
	 * @return bool
	 */
	public function SetValue($field, $value){
		// Set the Field Values
		switch($this->Get('type', $field)){
			case 'date':
				if ($value != '')
					Form::SetValue($field,date("Y-m-d",strtotime($value)));
				break;
			case 'time':
				if ($value != '')
					Form::SetValue($field,date("H:i",strtotime($value)));
				break;
			default:
				if (is_array($value))
					Form::SetValue($field,implode(',', $value));
				else
					Form::SetValue($field,$value);
				break;
		}		

		return true;
	}
	
	/**
	 * Display the results in various formats
	 * 
	 * @param string $type
	 * @return mixed
	 */
	public function Results($type='array'){
		switch($type){
			case 'json':
				// Create the JSON class
				$json = new Json();

				// Encode the Results
				return $json->encode($this->results);
				break;
			case 'array':
			default:
				// Return the raw results
				return $this->results;
				break;
		}
		
		return 0;
	}
	
	/**
	 * Join this table with another
	 * 
	 * @param class $join_class DbTemplate
	 * @param string $join_on field
	 * @param string $type (ex. "INNER" or "LEFT")
	 * @return bool
	 */
	public function Join($join_class, $join_on, $type='INNER'){
		// Save the state of the joined object
		if (is_object($join_class)){
			$this->join_class[] = $join_class;
			$this->join_type[] = $type;
			$this->join_on[] = $join_on;
			return true;
		}
	}

	/**
	 * Get or Set Cache
	 *
	 * @param string $action (ex. "get" or "set")
	 * @param string $filename
	 * @param mixed $data
	 * @param string $max_age
	 * @return string
	 */
	private function Cache($action, $filename, $data='', $max_age=''){
		// Set the full path
		$cache_file = FS_CACHE . $filename;
		$cache = '';

		if ($action == 'get'){
			// Clear the file stats
			clearstatcache();

			if (is_file($cache_file))
				if ($max_age != '' && date ($max_age, filemtime($cache_file)) >= date($max_age))
					$cache = file_get_contents($cache_file);
		}else{
			if (is_writable(FS_CACHE)){
				// Serialize the Fields
				$store = serialize($data);

				//Open and Write the File
				$fp = fopen($cache_file ,"w");
				fwrite($fp,$store);
				fclose($fp);
				chmod ($cache_file, 0777);

				$cache = strlen($store);
			}
		}

		return $cache;
	}

	/**
	 * Get the Valid Type of the field
	 *
	 * @param StdObj $field
	 * @return string
	 */
	private function ValidType(&$field){
		if (!is_object($field))
			return NULL;
		
		if ($field->unsigned == 1)
			return 'unsigned';

		if ($field->type == 'real')
			return 'float';

		if ($field->numeric == 1)
			return 'int';

		if ($field->name == 'email')
			return 'email';

		return NULL;
	}
	
	/**
	 * Parse the table information into an array format
	 *
	 * @return boolean
	 */
	private function ParseTable(){
		global $db;
		
		// Query for one record
		$result = $db->Query('SELECT * FROM `' . $this->table . '` LIMIT 1', $this->database, false);

		// Loop through all the fields
		while($field = $db->FetchField($result)){
			// Set all the field info
			$field_count = count($this->fields);
			$tmpField = new Field;
			$tmpField->Set('name', $field->name);
			$tmpField->Set('type', $field->type);
			$tmpField->Set('length', $db->FieldLength($result,$field_count));
			$tmpField->Set('validate', $this->ValidType($field));
			$tmpField->Set('primary', $field->primary_key);
			$tmpField->Set('display', ($field_count+1));
			
			// Set the primary if it is
			if ($field->primary_key == 1)
				$this->primary = $field->name;

			// Add the field to the list
			$this->fields[$field->name] = $tmpField;
		}
		
		if (USE_ENUM == true){
			// Query for the ENUM information
			$result2 = $db->Query('DESCRIBE ' . $this->table, $this->database, false);
			
			// Loop through all the fields
			while ($info = $db->FetchArray($result2)){
				$options = array();
				// Split up the type
				ereg('^([^ (]+)(\((.+)\))?([ ](.+))?$',$info['Type'],$field);
				if ($field[1] == 'enum' || $field[1] == 'set'){
					// Split the options
					$opts = split("','",substr($field[3],1,-1));
					foreach($opts as $key=>$value)
						$options[$value] = $value;
					$this->SetOption($info['Field'], $options);
				}
			}
		}

		return true;
	}
}
?>