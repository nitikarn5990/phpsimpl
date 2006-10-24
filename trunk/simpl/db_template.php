<?php
/**
* Database Field Template Class
*
* Used to mirror the database information about a field
*
* @author Nick DeNardis <nick.denardis@gmail.com>
*/
class DBtemp extends Field {
	/**
	* @var string 
	*/
    var $table;
	/**
	* @var string 
	*/
	var $def;
	/**
	* @var int 
	*/
	var $not_null;
	/**
	* @var int 
	*/
	var $primary_key;
	/**
	* @var int 
	*/
	var $multiple_key;
	/**
	* @var int 
	*/
	var $unique_key;
	/**
	* @var int 
	*/
	var $unsigned;
	/**
	* @var int 
	*/
	var $zerofill;
}

/**
* DB Template Class
* Needs to be extended to do anything out of the ordinary
*
* @author Nick DeNardis <nick.denardis@gmail.com>
*/
class DbTemplate extends Form {
	/**
	* @var array 
	*/
	var $data;
	/**
	* @var string 
	*/
	var $table;
	/**
	* @var string 
	*/
	var $database;
	/**
	* @var array 
	*/
	var $results = array();
	
	/**
	* Class Constructor
	*
	* Creates a DB Class with all the information to use the DB functions
	*
	* @param $data An associtive array with the key being the field and the data being the values for the class
	* @param $required An array with all the keys that are required to save into the database
	* @param $labels An associtive array with all the keys and the Output strings that will fill up the HTML label for the form
	* @param $examples An associtive arrray with all the keys and an example of what should be in the HTML form field
	* @param $table A string with the table name this class pertains to
	* @param $fields An associtive array if the class already has fields there is no need to pull from the DB or cache
	* @param $database A string containing the name of the database for this class
	* @return bool
	*/
	function DbTemplate($data, $required='', $labels='', $examples='', $table='', $fields='', $database=''){
		// Use the global mysql class
		global $db;
		
		// Set the Table that we are going to be mirroring as a class
		$this->table = $table;
		$this->database = $database;
		
		// If there is fields already set, set them.
		if (is_array($fields))
			$this->fields = $fields;
			
		// If there are fields that are required to be in the DB, this sets them into the class		
		if (is_array($required))
			$this->required = $required;	
		
		// Check to see if there is a cache yet.
		if (!is_array($this->fields)){
			clearstatcache();
			if (USE_CACHE == true && is_file(FS_SIMPL . WS_CACHE . $this->table . '.cache') && date ("Ymd", filemtime(FS_SIMPL . WS_CACHE . $this->table . '.cache')) >= date ("Ymd")){
				Debug('Contructor(), Create From Cache');
				// Grab the Cache file
				$cache = file_get_contents(FS_SIMPL . WS_CACHE . $this->table . '.cache');
				// Make the nessisary eval line
				$cache = '$this->fields = ' . substr($cache,0,-1) . ';';
				eval($cache);
				// Unserialize the classes
				foreach($this->fields as $key=>$field){
					$this->fields[$key] = unserialize($field);
					// Define the Primary Key
					if ($this->fields[$key]->primary_key == 1)
						$this->primary = $key;
				}
			}else{
				Debug('Contructor(), Create From Database');
				// Grab the list of fields from the DB
				$query = "SELECT * FROM `" . $this->table . "` LIMIT 1";
				$result = $db->Query($query, $this->database);
				
				// Get the information about each field
				while(($field = $db->FetchField($result))){
					// Get the max length of the field in the DB
					$field->length = $db->FieldLength($result,count($this->fields));
					// Set that info into the field class
					$key = $field->name;
					// Set the Label of the Key
					$field->label = ($labels[$key] != '')?$labels[$key]:'';
					// Set the Example of the Key
					$field->example = ($examples[$key] != '')?$examples[$key]:'';
					// Add this field to the list of fields
					$this->fields[$key] = $field;
					// If this is the Primary Key Save the field name
					if ($field->primary_key == 1)
						$this->primary = $key;
				}// end for each field
				
				// Write the Cache
				if (is_writable(FS_SIMPL . WS_CACHE)){
					$contents = arraytostring($this->fields);
					$filename = $this->table . '.cache';
				
					//Open and Write the File
					$fp = fopen(FS_SIMPL . WS_CACHE . $filename ,"w");
					fwrite($fp,$contents);
					fclose($fp);
					chmod (FS_SIMPL . WS_CACHE . $filename, 0777);
				}
			}
		}
		
		// Set all the Data for the Class
		if (is_array($this->fields))
			foreach($this->fields as $key=>$field){
				if ($field->type == 'date' && trim($data[$key]) != ''){
					$this->SetValue($key,date("Y-m-d",strtotime($data[$key])));
				}else if ($field->type == 'time' && trim($data[$key]) != ''){
					$this->SetValue($key,date("H:i",strtotime($data[$key])));
				}else{
					if (is_array($data[$key]))
						$this->SetValue($key,implode(',', $data[$key]));
					else
						$this->SetValue($key,($data[$key] != '')?$data[$key]:'');
				}
			}
				
		Debug($this->SimpleFormat());
		
		return true;	
	}
	
	/**
	* Set Primary
	*
	* Sets the Primary Key of the Databse Structure
	*
	* @param $value Usually an INT that is the primary key value for this object
	* @return bool
	*/
	function SetPrimary($value){
		// Get the Primary Key Name
		if (is_array($this->fields) && $this->primary != ''){
			// Set the Primary Key
			$this->SetValue($this->primary,$value);
			// Primary Key Found
			return true;
		}
		// Primary key not found
		return false;
	}
	
	/**
	* Get Primary
	*
	* Gets the Primary Key of the Databse Structure
	*
	* @return bool
	*/
	function GetPrimary(){
		// Make sure there is fields and return the value
		if (is_array($this->fields))
			return $this->GetValue($this->primary);
		
		return NULL;
	}
	
	/**
	* Get the Item Information
	*
	* Gets the item information from the DB with the criteria set
	*
	* @param $fields List of all the field keys that should be returned
	* @return bool
	*/
	function GetInfo($fields=''){
		// Use the global mysql class
		global $db;
		
		// Make Sure there is a Primary Key set
		if ($this->primary != ''){
			// Display the Debug
			Debug('GetInfo(), Primary Field: ' . $this->primary . ', Value: ' . $this->GetPrimary());
			// Start the Query
			$query = 'SELECT ';
			// If there is a limiting field
			if (is_array($fields)){
				// Add the list up of fields
				foreach($fields as $data)
					$query .= '`' . trim($data) . '`, ';
				// Trim off the last comma
				$query = substr($query,0,-2) . ' ';
			}else{
				$query .= '* ';
			}
			// Add the rest of the query together
			$query .= 'FROM `' . $this->table . '` WHERE `' . $this->primary . '` = \'' . $this->GetPrimary() . '\' LIMIT 1';
			
			// Do the Query
			$result = $db->Query($query, $this->database);
			Debug('GetInfo(), Query: ' . $query);
		
			// If there is atleast one result
			if ($db->NumRows($result) == 1){
				Debug('GetInfo(), Item Found');
				// Get the Info for the record
				$info = $db->FetchArray($result);
				// If there are fields to fill in set them all
				if (is_array($this->fields)){
					// Loop thought all the fields and set the values
					foreach($this->fields as $key=>$data)
						$this->SetValue($key,$info[$key]);
					// Return that all went well
					return true;
				}
			}else
				Debug('GetInfo(), Item Not Found');
		}
		
		// Return Issues
		return false;
	}
	
	/**
	* Saves Info to the DB
	*
	* Saves all the fields and their current values into the DB, it either 
	* inserts or updated depending on if the primary key is set.
	*
	* @param $options An associtive array to specify options for each field, display_order for example on how to find the next display_order
	* @return bool
	*/
	function Save($options = array()){
		// Use the global mysql class
		global $db;
		
		// Do not save if there are errors
		if (is_array($this->error))
			return false;
		
		// Decide to Insert or Update
		switch($this->GetPrimary()){
			case '':
				$type = 'insert';
				$extra = '';
				Debug('Save(), Inserting');
				
				// Check for fields that can only be set on insert
				if (is_array($this->fields))
					foreach($this->fields as $key=>$data)
						if ($key == 'date_entered' || $key == 'created_on' || $key == 'last_updated' || $key == 'updated_on')
							$this->SetValue($key,date("Y-m-d H:i:s"));
						else if ($key == 'display_order' && is_object($options['display_order'])){
							// Find out what the next display order is
							$last_item = $options['display_order']->GetList(array('display_order'),'display_order','DESC',0,1);
							if (is_array($last_item) && count($last_item) == 1){
								foreach($last_item as $item)
									$this->SetValue($key,((int)$item['display_order']+1));
							} else {
								$this->SetValue($key,1);
							}
						}
				break;
			default: 
				$type = 'update';
				$extra = '`' . $this->primary . '`=\'' . $this->GetPrimary() . '\'';
				Debug('Save(), Updating, Primary Field: ' . $this->primary . ', Value: ' . $this->GetPrimary());
				
				// Check for fields that need to be updated on each saved
				if (is_array($this->fields))
					foreach($this->fields as $key=>$data)
						if ($key == 'last_updated' || $key == 'updated_on')
							$this->SetValue($key,date("Y-m-d H:i:s"));	
				break;
		}
		
		// Create the array of items to insert or update
		$infoArray = array();
		if (is_array($this->fields))
			foreach($this->fields as $key=>$data)
				if ($data->table != '')
					$infoArray[$key] = $this->GetValue($key);
		
		if (DB_STATUS != false){
			Debug('Save(), Database Found');
			// Check to see if there is any items that were left without a database
			$orphans = glob(FS_SIMPL . WS_CACHE . "*.db.php");
			if (is_array($orphans)){
				Debug('Save(), Found ' . count($orphans) . ' Orphans');
				// Create an array to hold them all
				$found = array();
				foreach ($orphans as $orphan){
					// Transform the "file safe" text into a PHP class
					$data = urldecode(file_get_contents($orphan));
					$data = '$found[] = unserialize(\'' . $data . substr($data,0,-1) . '\');';
					eval($data);
					// Remove the File, it will be recreated if there is any DB loss again
					unlink($orphan);
				}
				// Make sure the items are objects and save their info in the DB
				foreach($found as $item)
					if (is_object($item))
						if ($item->Save())
							Debug('Save(), Orphan ' . $item->table . ' #' . $item->GetPrimary() . ' Saved');
			}
			// Get all the txt files waiting to be entered
			
			
			// Do the Operation
			$db->Perform($this->table, $infoArray, $type, $extra, $this->database);
	
			// Grab the ID if inserting
			if ($type == 'insert')
				$this->SetPrimary($db->InsertID());
			
			// If the primary key is set then we are all good
			if ($this->GetPrimary() != '')
				return true;
		}else{
			// Write to File if DB is down
			$contents = urlencode(serialize($this));
			$filename = $this->table . '_' . date("YmdHis") . '.db.php';
			
			Debug('Save(), Database Down, Saving to File: ' . $filename);
			
			//Open and Write the File
			$fp = fopen(FS_SIMPL . WS_CACHE . $filename ,"w");
			fwrite($fp,$contents);
			fclose($fp);
			chmod (FS_SIMPL . WS_CACHE . $filename, 0777);
			
			// If the file is written we did all we can do for now
			if (is_file(FS_SIMPL . WS_CACHE . $filename))
				return true;
		}
		
		return false;
	}
	
	/**
	* Deletes the info from the DB
	*
	* Deletes the info from the DB accourding to the primary key
	*
	* @return bool
	*/
	function Delete($options = array()){
		// Use the global mysql class
		global $db;
		
		// If we can get the info then we can delete it
		if ($this->GetInfo()){
			Debug('Delete(), Item Found, Primary Field: ' . $this->primary . ', Value: ' . $this->GetPrimary());
			
			// Check to see if we need to cleanup the display order first
			if (is_object($options['display_order'])){
				// Move the item all the way down to the bottom
				while ($this->Move('down',$options)){}
			}
		
			// Delete the row
			$query = "DELETE FROM `" . $this->table . "` WHERE `" . $this->primary . "` = '" . $this->GetPrimary() . "' LIMIT 1";
			$result = $db->Query($query, $this->database);

			// If it did something the return that everything is gone
			if ($result)
				return true;
		}else{
			Debug('Delete(), Item Not Found, Primary Field: ' . $this->primary . ', Value: ' . $this->GetPrimary());
		}
		
		return false;
	}
	
	/**
	* Move Item
	*
	* Moves the Menu Item up or down depending on the direction
	*
	* @todo Clean this function up.
	* @param $direction A string containing either 'up' or 'down'
	* @param $options An array containing the search criteria for the move, if there is a cetain category or list to stay within
	* @return bool
	*/
	function Move($direction,$options){
		// Use the global mysql class
		global $db;
		
		// Loop through all the options
		if (is_array($options)){
			foreach($options as $key=>$item){
				// Make sure the variable to move is in the field list
				if (in_array($key,$this->GetFields())){
					// Depending on the Move find the next/previous key
					// Limit the Search
					$extra = '';
					// Run trough each each field looking for values inside of each variable
					if(is_array($item->fields))
						foreach($item->fields as $key2=>$data){
							// Make sure the variable has something in it
							if ((string)$item->GetValue($key2) != ''){
								Debug('Move(), Filter Item: ' . $key2 . ', Value: ' . $item->GetValue($key2));
								// Determine how to search in the database
								if ($this->Get('blob',$key2) == 1)
									$extra .= " `" . $key2 . "` LIKE '" . $item->GetValue($key2) . "' AND";
								else		
									$extra .= " `" . $key2 . "` = '" . $item->GetValue($key2) . "' AND";
							}
						}
					
					// Add the Move Variable
					if ($direction == 'up')
						$extra .= " `" . $key . "` < '" . $this->GetValue($key) . "' AND";
					else
						$extra .= " `" . $key . "` > '" . $this->GetValue($key) . "' AND";
					
					// Format it for MySQL
					$extra = ($extra != '')?'WHERE ' . substr($extra,0,-4):'';
			
					// Make the Query
					$query = 'SELECT `' . $item->primary . '`,`' . $key . '` ';
						
					// Finish the query
					$query .= 'FROM `' . $this->table . '` ' . $extra . ' ORDER BY `' . $key . '` ' . (($direction == 'up')?'DESC':'ASC');
					
					// Put in the Offset
					$query .= ' LIMIT 1';
					
					// Do the Query
					$result = $db->Query($query, $this->database);
					Debug('Move(), Query: ' . $query);
					
					// Make sure there is one result
					if ($db->NumRows($result) == 1){
						// Get the New Order
						$new_order = $db->FetchArray($result);
						// Make sure they are not the same numbers
						if ($new_order[$key] != $this->GetValue($key)){
							// Update the Old One
							$oldArray = array($key => $this->GetValue($key));
							$db->Perform($item->table, $oldArray, 'update', '`' . $item->primary . '`=\'' . $new_order[$item->primary] . '\'');
							// Update the New one
							$newArray = array($key => $new_order[$key]);
							$db->Perform($this->table, $newArray, 'update', '`' . $this->primary . '`=\'' . $this->GetPrimary() . '\'');
							
							// Set the value to this class
							$this->SetValue($key,$new_order[$key]);
							
							return true;
						}
					}
				}
			}
		}
		
		return false;
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
	function Search($keywords,$search_fields,$return_fields){
		// Use the global mysql class
		global $db;
		
		$terms = search_split_terms($keywords);
		$terms_db = search_db_escape_terms($terms);
		$terms_rx = search_rx_escape_terms($terms);
	
		$parts = array();
		foreach($terms_db as $term_db){
			if (is_array($search_fields)){
				foreach($search_fields as $field){
					$parts[] = "$field RLIKE '$term_db'";
				}
			}else{
				$parts[] = "`" . $search_fields . "` RLIKE '$term_db'";
			}
		}
		$parts = implode(' AND ', $parts);
	
		// Create the Return String
		if (is_array($return_fields)){
			foreach($return_fields as $field)
				$fields[] = '`' . $field . '`';
			$return = implode(', ', $fields);
		}else{
			$return = '*';
		}
		$sql = "SELECT " . $return . " FROM `" . $this->table . "` WHERE $parts";
	
		$rows = array();
	
		$result = $db->Query($sql, $this->database);
		while($row = $db->FetchArray($result)){
			$row[score] = 0;
			foreach($terms_rx as $term_rx){
				if (is_array($search_fields)){
					foreach($search_fields as $field){
						$row[score] += preg_match_all("/$term_rx/i", $row[$field], $null);
					}
				}else{
					$row[score] += preg_match_all("/$term_rx/i", $row[$search_fields], $null);
				}
			}
			$rows[] = $row;
		}
	
		uasort($rows, 'search_sort_results');
		$this->results = $rows;
		
		return count($rows);
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
	function GetList($fields='', $order_by='', $sort='', $offset='', $limit=''){
		// Use the global mysql class
		global $db;
		
		// If they request an order build the query
		if ( isset($order_by) && $order_by != '' ){
			// If its an array handle the order_by and sort together
			if(is_array($order_by)) {
				$i = 0;
				$order = 'ORDER BY'; 
				foreach($order_by as $item) {
					$order .= ' `'.$item.'`';
					(is_array($sort) && isset($sort[$i])) ? $order .= ' ' . $sort[$i] : '' ;
					$order .= ',';
					$i++; 
				}
				// Delete the comma
				$order = substr($order, 0, -1);
				// If sort is an array make it nothing in the query string 
				(is_array($sort)) ? $sort = '' : '';
			}else
				$order = 'ORDER BY `' . $order_by . '` ';
		}else{
			// Make sure if order_by is not passed then sort cannot have a value
			$order = '';
			$sort = '';
		}
		
		// Limit the Search
		$extra = '';
		
		// Run trough each each field looking for values inside of each variable
		if(is_array($this->fields))
			foreach($this->fields as $key=>$data){
				// Make sure the variable has something in it
				if ((string)$this->GetValue($key) != ''){
					Debug('GetList(), Filter Item: ' . $key . ', Value: ' . $this->GetValue($key));
					// Determine how to search in the database
					if ($this->Get('blob',$key) == 1)
						$extra .= " `" . $key . "` LIKE '" . $this->GetValue($key) . "' AND";
					else		
						$extra .= " `" . $key . "` = '" . $this->GetValue($key) . "' AND";
				}
			}
			
		if ($this->search != ''){
			if(is_array($this->fields)){
				$extra .= ' (';
				foreach($this->fields as $key=>$data){
						// Determine how to search in the database
						if ($this->Get('type',$key) == 'string')
							$extra .= ' `' . $key . '` LIKE \'%' . $this->search . '%\' OR';
						else		
							$extra .= ' `' . $key . '` = \'' . $this->search . '\' OR';
				}
				$extra = ($extra != '')?substr($extra,0,-3):'';
				$extra .= ') AND';
			}
		}
		
		// Format it for MySQL
		$extra = ($extra != '')?'WHERE ' . substr($extra,0,-4):'';

		// Make the Query
		$query = 'SELECT ';
		// If there is a limiting field
		if (is_array($fields)){
			// Always get the primary key	
			if (!in_array($this->primary,$fields))
				$query .= '`' . $this->primary . '`, ';
			// Add the list up of fields
			foreach($fields as $data)
				$query .= '`' . $data . '`, ';
			// Trim off the last comma
			$query = substr($query,0,-2) . ' ';
		}elseif($fields == 'count'){
			$query .= 'count(*) as `count`'; 
		}else{
			$query .= '* '; 
		}
		// Finish the query
		$query .= 'FROM `' . $this->table . '` ' . $extra . ' ' . $order . ' ' . $sort;
		
		// Put in the Offset
		if ($offset >0 || $limit >0)
			$query .= ' LIMIT ' . $offset . ', ' . $limit;
		
		// Do the Query
		$result = $db->Query($query, $this->database);
		
		Debug('GetList(), Query: ' . $query);
		
		// If there is atleast one result
		if ($db->NumRows($result) > 0){
			Debug('GetList(), Number Found: ' . $db->NumRows($result));
			// Create the return array
			$this->results = array();
			// For each result make a class
			if($fields == 'count') {
				$info = $db->FetchArray($result);
				$this->results['count'] = $info['count'];		
			}else{
				while ($info = $db->FetchArray($result))
					$this->results[$info[$this->primary]] = $info;
			}		
		}// if there is atleast one template

		return $this->results;
	}
	
	/**
	* Get an Associative array
	*
	* Does the same thing as "GetList()" but returns an associtive array to be used for drop downs
	*
	* @param $field A string of a field that it will return
	* @param $order_by A string of a field key to order by (ex. "display_order")
	* @param $sort A string on how to sort the data (ex. "ASC" or "DESC")
	* @param $offset An int on where to start returning, used mainly for page numbering
	* @param $limit An int limit on the number of rows to be returned
	* @return array
	*/
	function GetAssoc($field, $order_by='', $sort='', $offset='', $limit=''){
		// Create the Array for the Get List funciton
		$fields = array($field);
		
		// Call the regular Get List
		$this->GetList($fields, $order_by, $sort, $offset, $limit);
		
		// If there are results returned
		if (is_array($this->results)){
			// Create the return array
			$tmp_list = array();
			// Loop through each results
			foreach($this->results as $data){
				// Create the ASSOC array
				$tmp_list[$data[$this->primary]] = $data[$field];
			}
			// Return the ASSOC array
			return $tmp_list;
		}
		
		return array();
	}
	
	/**
	* Create a Form with the Data
	*
	* Displays a Form for Adding/Editing this DB class
	* Hidden Array takes presidence over $display
	*
	* @param array, array, array
	* @return NULL
	*/
	function Form($display='', $hidden=array(), $options=array(), $config=array()){
		// Rearrange the Fields if there is a custom display
		$show = array();
		if(is_array($display)){
			// If there is a custome Display make the array
			foreach($display as $key=>$data)
				$show[$data] = ($this->fields[$data]->label != '')?$this->fields[$data]->label:ucfirst(str_replace('_',' ',$data)) . ':';
			
			// Loop through all the fields to find orphans and add them to the hidden array so we dont loose data
			if (is_array($this->fields))
				foreach($this->fields as $key=>$data)
					if (!array_key_exists($key,$show) && !in_array($key,$hidden)){
						if ($this->GetError($key) != '')
							Alert($this->GetError($key));
						$hidden[] = $key;
					}
		}else{
			// If there is fields in the db table make the show array
			if (is_array($this->fields))
				foreach($this->fields as $key=>$data){
					if (!in_array($key,$hidden))
						$show[$key] = ($data->label != '')?$data->label:ucfirst(str_replace('_',' ',$key)) . ':';
				}
		}
		
		// Start the Fieldset
		echo '<fieldset id="table_' . $this->table . '"><legend>' . ucfirst(str_replace('_',' ',$this->table)) . ' Information</legend>' . "\n";
		
		// Show all the Visible Fields
		foreach($show as $key=>$field){
			// If the field is not in the hidden array
			if (!in_array($key,$hidden)){
				// Create the Field Div with the example, label and error if need be
				echo '<div class="field_' . $key . '">' . (($this->fields[$key]->example != '')?'<div class="example"><p>' . stripslashes($this->fields[$key]->example) . '</p></div>':'') . '<label for="' . $key . '">' . ((in_array($key,$this->required))?'<em>*</em>':'') . $field . '</label>' . (($this->error[$key] != '')?'<div class="error">':'');
				
				// If there is specialty options
				if(is_object($options[$key])){
					switch(get_class($options[$key])){
						case 'Upload':
							// Check to see if it is set or not
							if (stripslashes($this->fields[$key]->value) != ''){
								// If there is something in the field
								echo '<p id="form_' . $key . '">' . $this->GetValue($key) . ' <a href="' . $options[$key]->directory . $this->GetValue($key) . '" target="_blank"><img src="' . DIR_IMAGES . 'picture_go.png" width="16" height="16" alt="View ' . $this->GetValue($key) . '" align="top" /></a> <a href="?id=' . $this->GetPrimary() . '&amp;remove=image" class="funcDeleteImage()"><img src="' . DIR_IMAGES . 'picture_delete.png" width="16" height="16" alt="Remove ' . $this->GetValue($key) . '" align="top" /></a></p>';
								// Add this field to the Hidden List
								if (!in_array($key,$hidden))
									$hidden[]=$key;
							}else{
								// If there not anything uploaded yet
								echo '<input name="' . $key . '" id="' . $key . '" type="file" />';
							}
							break;
						default:
							echo '<p>Unknown</p>';
							break;
					}
				}elseif (is_array($options[$key])){
					switch($config[$key]){
						case 'radio':
							echo '<div class="radio">' . "\n";
							// Loop though each option
							foreach($options[$key] as $opt_key=>$opt_value){
								echo "\t" . '<div><input name="' . $key . '" type="radio" value="' . $opt_key . '" id="' . $key . '_' . $opt_key . '"' . (((string)$this->GetValue($key) == (string)$opt_key)?' checked="checked"':'') . ' /> <label for="' . $key . '_' . $opt_key . '">' . stripslashes($opt_value) . '</label></div>' . "\n";
							}
							echo '</div>';
							break;
						case 'checkbox':
							// Split values
							$split = split(',',$this->GetValue($key));
													
							echo '<div class="checkbox">' . "\n";
							// Loop though each option
							foreach($options[$key] as $opt_key=>$opt_value){
								echo "\t" . '<div><input name="' . $key . '[]" type="checkbox" value="' . $opt_key . '" id="' . $key . '_' . $opt_key . '"' . (is_array($split) && in_array((string)$opt_key,$split)?' checked="checked"':'') . ' /> <label for="' . $key . '_' . $opt_key . '">' . stripslashes($opt_value) . '</label></div>' . "\n";
							}
							echo '</div>';
							break;
						default:
							// Start the Select Box
							echo '<select name="' . $key . '" id="' . $key . '">' . "\n";
							// Loop though each option
							foreach($options[$key] as $opt_key=>$opt_value){
								echo "\t" . '<option value="' . $opt_key . '"' . (((string)$this->GetValue($key) == (string)$opt_key)?' selected="selected"':'') . '>' . stripslashes($opt_value) . '</option>' . "\n";
							}
							// End the Select Box
							echo '</select><br />' . "\n";
							break;
					}
				}elseif($this->fields[$key]->type == 'blob'){
					// If it is a blob or text in the DB then make it a text area
					echo '<textarea name="' . $key . '" id="' . $key . '" cols="50" rows="4">' . stripslashes($this->GetValue($key)) . '</textarea><br />' . "\n";
				}elseif($this->fields[$key]->type == 'date'){
					// Display the Input Field
					echo '<input name="' . $key . '" id="' . $key . '" type="text" size="18" maxlength="18" value="' . (($this->GetValue($key) != '')?date("F j, Y",strtotime(stripslashes($this->GetValue($key)))):'') . '" /><button type="reset" id="' . $key . '_b">...</button>';					
					echo '<script type="text/javascript">
						Calendar.setup(
							{
							  inputField  : "'.$key.'",     // ID of the input field
							  ifFormat    : "%B %e, %Y",    // the date format
							  button      : "'.$key.'_b"    // ID of the button
							}
						);
					</script>';
				}else{
					// Set the display size, if it is a small field then limit it
					$size = ($this->fields[$key]->length <= 30)?$this->fields[$key]->length:30;
					// Display the Input Field
					echo '<input name="' . $key . '" id="' . $key . '" type="' . ((is_string($config[$key]) && trim(strtolower($config[$key])) == 'password')?$config[$key]:'text') . '"' . ((is_string($config[$key]) && trim(strtolower($config[$key])) == 'readonly')?' readonly="readonly"':'') . ' size="' . $size . '" maxlength="64" value="' . stripslashes($this->GetValue($key)) . '" />';
				}
				// If there is an error show it and end the field div
				echo (($this->error[$key] != '')?'<p>' . stripslashes($this->error[$key]) . '</p></div>':'') . '</div>' . "\n";
			}
		}
		
		// Make all the Hidden Fields
		foreach($hidden as $field)
			echo '<input name="' . $field . '" id="' . $field . '" type="hidden" value="' . stripslashes($this->GetValue($field)) . '" />' . "\n";
		
		// End the Fieldset
		echo '</fieldset>' . "\n";
	}
	
	/**
	* Display the Item in Plain Text
	*
	* Displays the single items to be viewed only.
	*
	* @todo Change the order of the output to reflect the order of the $display
	* @todo Add a flag to return the output instead of echoing it to the screen
	* @param $display An array of field keys that are going to be displayed to the screen
	* @return NULL
	*/
	function View($display=''){
		// Make sure there are fields
		if (is_array($this->fields)){
			echo '<table width="98%" border="0" cellspacing="0" cellpadding="2" class="item" summary="View Individual Information">';
			// Loop through each field
			foreach($this->fields as $key=>$data){
				$disp = false;
				// If there is a Display Limit
				if (is_array($display)){
					if(in_array($key, $display))
						$disp = true;
				}else
					$disp = true;
				// If it is cool to show
				if($disp == true) { ?>
					<tr>
						<th scope="row"><?php echo ($data->label != '')?$data->label:str_replace('_', ' ', ucfirst($key)); ?>: </th>
						<td><?php echo stripslashes($data->value); ?></td>
					</tr>
				<?php }
			}
			echo '</table>';
		}
	}
	
	/**
	* Display a list of items
	*
	* Displays a list of items according the the list criteria and sort order
	*
	* @todo Change over to use the Get() helper functions instead of accessing the information directly
	* @param $display An array of field keys that are going to be displayed in the list
	* @param $format A string that will be passed through a sprintf() and use the ID and the String as the first and second parameters
	* @param $options An associtive array with the key being the value for a field if there are output options for that field (ex. array("is_active"=>array("no","yes")))
	* @param $force_check A bool to flag if this function should pull a GetList or not, default it will be in the case a get list has already been done it can be set to false
	* @return NULL
	*/
	function DisplayList($display='',$format=array(),$options=array(),$force_check=true){
		// Setup the Sort Sessions
		$_SESSION[$this->table . '_sort'] = ($_GET['sort'] != '')?$_GET['sort']:$_SESSION[$this->table . '_sort'];
		$_SESSION[$this->table . '_order'] = ($_GET['order'] != '')?$_GET['order']:$_SESSION[$this->table . '_order'];
		
		// Get the List of Items If they are not already set
		if (count($this->results) == 0 && $force_check == true )
			$this->GetList($display, $_SESSION[$this->table . '_sort'], $_SESSION[$this->table . '_order']);
		
		// If there is items
		if (count($this->results) > 0){
			// Simplify the order
			$order = ($_SESSION[$this->table . '_order'] == 'desc')?'asc':'desc';
			// Start the table
			echo '<table border="0" cellspacing="0" cellpadding="2" width="99%" class="small" summary="List of Records from ' . htmlspecialchars(stripslashes($this->table)) . '">' . "\n";
			
			// Rearrange the Fields if there is a custom display
			$show = array();
			if(is_array($display)){
				foreach($display as $key=>$data)
					$show[$data] = ($this->Get('label',$data) != '')?$this->Get('label',$data):ucfirst(str_replace('_',' ',$data));
			}else{
				if (is_array($this->fields))
					foreach($this->fields as $key=>$data)
						$show[$key] = ($data->label != '')?$data->label:ucfirst(str_replace('_',' ',$key));
			}
			// Display the Header
			echo "\n" . '<tr>';
			foreach($show as $key=>$column){
				echo "\t" . '<th scope="col"><a href="' . $_SERVER['PHP_SELF'] . '?sort=' . $key . '&amp;order=' . $order . '" title="Order by ' . $column . '">' . $column;
				if ($_SESSION[$this->table . '_sort'] == $key){
					if ($_SESSION[$this->table . '_order'] == 'asc')
						echo ' &uarr;';
					else if ($_SESSION[$this->table . '_order'] == 'desc')
						echo ' &darr;';
				}
				echo '</a></th>' . "\n";
			}
			echo '</tr>' . "\n";
			
			// Loop through all the items
			$i=1;
			foreach($this->results as $field=>$data){
				echo '<tr' . (($i%2 == 0)?' class="odd"':'') . '>' . "\n";
				$j=1;
				foreach($show as $key=>$column){
					// Overwrite the DB data with usable data
					if (is_array($options[$key])){
						// Create Local Value
						$value = $data[$key];
						// Get the Option Value
						$data[$key] = $options[$key][$value];
					}else if ($options[$key] == 'move'){
						$data[$key] = '<div class="center">' . (($i != 1)?'<a href="?item=' . $data[$this->primary] . '&amp;move=up"><img src="' . ADDRESS . WS_SIMPL . WS_SIMPL_IMAGE . 'asc.gif" align="top" width="17" height="17" alt="Move Item Up" /></a>':'') . (($i != count($this->results))?'<a href="?item=' . $data[$this->primary] . '&amp;move=down"><img src="' . ADDRESS . WS_SIMPL . WS_SIMPL_IMAGE . 'desc.gif" align="top" width="17" height="17" alt="Move Item Down" /></a>':'') . '</div>'; 
					}
					// Display the Data
					echo "\t" . '<td' . (($j == count($show))?' class="last"':'') . '>';
					$end = '';
					// If there is links
					if (is_array($format) && $format[$key] != ''){
						// Parse the custom format
						echo str_replace(array('{$item_id}','{$data}'),array($data[$this->primary],stripslashes($data[$key])),$format[$key]);
					}else{
						echo ($data[$key] != '')?stripslashes($data[$key]):'&nbsp;';
					}
					echo $end;
					echo '</td>' . "\n";
					$j++;
				}
				
				echo '</tr>' . "\n";
				$i++;
			}
			// End the Table
			echo '</table>';
		}else{
			// If there is not items in the list
			echo '<p>Currently there are no items, please try again later.</p>' . "\n";
		}
	}
}
?>