<?php
/**
* Database Field Template Class
*
* Used to mirror the database information about a field
*
* @author Nick DeNardis <nick@design-man.com>
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

/*
* DB Template Class
* Needs to be extended to do anything out of the ordinary
*
* @author Nick DeNardis <nick@design-man.com>
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
	var $list;
	
	/**
	* Class Constructor
	*
	* Creates a DB Class with all the information to use the DB functions
	*
	* @param array, array, string, array
	* @return NULL
	*/
	function DbTemplate($data, $required='', $labels='', $examples='', $table='', $fields='', $database=''){
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
					$tmpField = unserialize($field);
					$this->fields[$key] = $tmpField;
					if ($tmpField->primary_key == 1)
						$this->primary = $key;
				}
			}else{
				Debug('Contructor(), Create From Database');
				// Grab the list of fields from the DB
				$query = "SELECT * FROM `" . $this->table . "` LIMIT 1";
				$result = fnc_db_query($query, $this->database);
				
				// Get the information about each field
				while(($field = mysql_fetch_field($result))){
					// Get the max length of the field in the DB
					$field->length = mysql_field_len($result,count($this->fields));
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
					// Transform the purchased on Date
					$tmp_date = split('[/.-]', $data[$key]);
					$this->SetValue($key,date("Y-m-d",strtotime($tmp_date[2].'-'.$tmp_date[0].'-'.$tmp_date[1])));
					unset($tmp_date);
				}else if ($field->type == 'time' && trim($data[$key]) != ''){
					$this->SetValue($key,date("H:i",strtotime($data[$key])));
				}else{
					if (is_array($data[$key]))
						$this->SetValue($key,implode(",", $data[$key]));
					else
						$this->SetValue($key,($data[$key] != '')?$data[$key]:'');
				}
			}
				
		Debug($this);	
	}
	
	/**
	* Set Primary
	*
	* Sets the Primary Key of the Databse Structure
	*
	* @return BOOL
	*/
	function SetPrimary($value){
		// Get the Primary Key Name
		if (is_array($this->fields) && $this->primary != ''){
			// Set the Primary Key
			$this->fields[$this->primary]->value = $value;
			// Yay Primary Key Found
			return true;
		}
		// Primary key not found?
		return false;
	}
	
	/**
	* Get Primary
	*
	* Gets the Primary Key of the Databse Structure
	*
	* @return BOOL
	*/
	function GetPrimary(){
		// Make sure there is fields and return the value
		if (is_array($this->fields))
			return $this->fields[$this->primary]->value;
		
		return NULL;
	}
	
	/**
	* Get the Item Information
	*
	* Gets the item information from the DB with the criteria set
	*
	* @return BOOL
	*/
	function GetInfo($fields=''){
		// Make Sure there is a Primary Key set
		if ($this->primary != ''){
			// Create a One Word Variable
			$primary = $this->primary;
			// Display the Debug
			Debug('GetInfo(), Primary Field: ' . $primary . ', Value: ' . $this->fields[$this->primary]->value);
			// Start the Query
			$query = 'SELECT ';
			// If there is a limiting field
			if (is_array($fields)){
				// Add the list up of fields
				foreach($fields as $data)
					$query .= '`' . $data . '`, ';
				// Trim off the last comma
				$query = substr($query,0,-2) . ' ';
			}else
				$query .= '* ';
			$query .= 'FROM `' . $this->table . '` WHERE `' . $primary . '` = \'' . $this->fields[$this->primary]->value . '\' LIMIT 1';
			
			// Do the Query
			$result = fnc_db_query($query, $this->database);
			Debug('GetInfo(), Query: ' . $query);
		
			// If there is atleast one result
			if (fnc_db_num_rows($result) == 1){
				Debug('GetInfo(), Item Found');
				// Get the Info for the record
				$info = fnc_db_fetch_array($result);
				// If there are fields to fill in set them all
				if (is_array($this->fields)){
					// Loop thought all the fields and set the values
					foreach($this->fields as $key=>$data)
						$this->fields[$key]->value = $info[$key];
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
	* @return BOOL
	* @todo Check the Cache file for any not entered records
	*/
	function Save(){
		// Do not save if there is errors
		if (is_array($this->error))
			return false;
		
		// Get the Primary Key
		$primary = $this->primary;
		// Decide to Insert or Update
		switch($this->fields[$this->primary]->value){
			case '':
				$type = 'insert';
				$extra = '';
				Debug('Save(), Inserting');
				
				// Check for fields that can only be set on insert
				if (is_array($this->fields))
					foreach($this->fields as $key=>$data)
						if ($key == 'date_entered' || $key == 'created_on' || $key == 'last_updated' || $key == 'updated_on')
							$this->fields[$key]->value = date("Y-m-d H:i:s");
				break;
			default: 
				$type = 'update';
				$extra = '`' . $primary . '`=\'' . $this->fields[$this->primary]->value . '\'';
				Debug('Save(), Updating, Primary Field: ' . $primary . ', Value: ' . $this->fields[$this->primary]->value);
				
				// Check for fields that need to be updated on each saved
				if (is_array($this->fields))
					foreach($this->fields as $key=>$data)
						if ($key == 'last_updated' || $key == 'updated_on')
							$this->fields[$key]->value = date("Y-m-d H:i:s");
				break;
		}
		
		// Create the array of items to insert or update
		$infoArray = array();
		if (is_array($this->fields))
			foreach($this->fields as $key=>$data){
				$infoArray[$key] = $this->fields[$key]->value;
			}
			
		if (DB_STATUS != false){
			Debug('Save(), Database Found');
			
			// Do the Operation
			fnc_db_perform($this->table, $infoArray, $type, $extra, $this->database);
	
			// Grab the ID if inserting
			if ($type == 'insert')
				$this->fields[$this->primary]->value = fnc_db_insert_id();
			
			// If the primary key is set then we are all good
			if ($this->fields[$this->primary]->value != '')
				return true;
		}else{
			// Write to File if DB is down
			$contents = arraytostring($infoArray);
			$filename = $this->table . '_' . date("YmdHis") . '.txt';
			
			Debug('Save(), Database Down, Saving to File: ' . $filename);
			
			//Open and Write the File
			$fp = fopen(FS_SIMPL . WS_CACHE . $filename ,"w");
			fwrite($fp,$contents);
			fclose($fp);
			chmod (FS_SIMPL . WS_CACHE . $filename, 0777);
			
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
	* @return BOOL
	*/
	function Delete(){
		// Only Grab the Primary Key
		$fields = array($this->primary);
		// If we can get the info then we can delete it
		if ($this->GetInfo($fields)){
			// fields [0] is always primary key
			$primary = $this->primary;
			Debug('Delete(), Item Found, Primary Field: ' . $primary . ', Value: ' . $this->fields[$this->primary]->value);
		
			// Delete the row
			$query = "DELETE FROM `" . $this->table . "` WHERE `" . $primary . "` = '" . $this->fields[$this->primary]->value . "' LIMIT 1";
			$result = fnc_db_query($query, $this->database);

			// If it did something the return that everything is gone
			if ($result)
				return true;
		}else
			Debug('Delete(), Item Not Found, Primary Field: ' . $primary . ', Value: ' . $this->fields[$this->primary]->value);
		
		return false;
	}
	
	/**
	* Search the info from the DB
	*
	* Uses a smarter search engine to search through the fields and return certain fields
	*
	* @return INT
	*/
	function Search($keywords,$search_fields,$return_fields){
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
	
		$result = fnc_db_query($sql);
		while($row = fnc_db_fetch_array($result)){
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
	* @param string, string, int, int
	* @return ARRAY
	*/
	function GetList($fields='', $order_by='', $sort='', $offset='', $limit=''){
		// If they request an order build the query
		if ( isset($order_by) && $order_by != '' ){
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
				if (isset($this->fields[$key]->value) && (string)$this->fields[$key]->value != ''){
					Debug('GetList(), Filter Item: ' . $key . ', Value: ' . $this->fields[$key]->value);
					// Determine how to search in the database
					if (is_string($this->fields[$key]->value))
						$extra .= " `" . $key . "` LIKE '" . $this->fields[$key]->value . "' AND";
					else		
						$extra .= " `" . $key . "` = '" . $this->fields[$key]->value . "' AND";
				}
			}
			
		if ($this->search != ''){
			if(is_array($this->fields)){
				$extra .= ' (';
				foreach($this->fields as $key=>$data){
						// Determine how to search in the database
						if ($this->fields[$key]->type == 'blob' || $this->fields[$key]->type == 'string')
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
		}else
			$query .= '* '; 
		// Finish the query
		$query .= 'FROM `' . $this->table . '` ' . $extra . ' ' . $order . ' ' . $sort;
		
		// Put in the Offset
		if ($offset >0 || $limit >0)
			$query .= ' LIMIT ' . $offset . ', ' . $limit;
		
		// Do the Query
		$result = fnc_db_query($query, $this->database);
		Debug('GetList(), Query: ' . $query);
		
		// If there is atleast one result
		if (fnc_db_num_rows($result) > 0){
			Debug('GetList(), Number Found: ' . fnc_db_num_rows($result));
			// Create the return array
			$this->list = array();
			// For each result make a class
			while ($info = fnc_db_fetch_array($result))
				$this->list[] = $info;
			// Return the list of object
			return $this->list;
		}// if there is atleast one template

		return false;
	}
	
	/**
	* Create a Form with the Data
	*
	* Displays a Form for Adding/Editing this DB class
	* Hidden Array takes presidence over $display
	*
	* @param array, array, array
	* @todo Write this Function
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
					if (!array_key_exists($key,$show) && !in_array($key,$hidden))
						$hidden[] = $key;
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
				if ($options[$key] != ''){
					switch($config[$key]){
						case 'radio':
							echo '<div class="radio">' . "\n";
							// Loop though each option
							foreach($options[$key] as $opt_key=>$opt_value){
								echo "\t" . '<div><input name="' . $key . '" type="radio" value="' . $opt_key . '" id="' . $key . '_' . $opt_key . '"' . (((string)$this->fields[$key]->value == (string)$opt_key)?' checked="checked"':'') . ' /> <label for="' . $key . '_' . $opt_key . '">' . stripslashes($opt_value) . '</label></div>' . "\n";
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
								echo "\t" . '<option value="' . $opt_key . '"' . (((string)$this->fields[$key]->value == (string)$opt_key)?' selected="selected"':'') . '>' . stripslashes($opt_value) . '</option>' . "\n";
							}
							// End the Select Box
							echo '</select><br />' . "\n";
							break;
					}
				}elseif($this->fields[$key]->type == 'blob'){
					// If it is a blob or text in the DB then make it a text area
					echo '<textarea name="' . $key . '" id="' . $key . '" cols="50" rows="4">' . stripslashes($this->fields[$key]->value) . '</textarea><br />' . "\n";
				}elseif($this->fields[$key]->type == 'date'){
					// Create the Javascript Date Menu
					echo '<span id="cal_' . $key . '"></span>';
					echo '<script type="text/javascript">';
					echo 'createCalendarWidget(\'' . $key . '\',\'NO_EDIT\', \'ICON\',\'' . ADDRESS . WS_SIMPL . WS_SIMPL_IMAGE . 'cal.gif\');';
					if ($this->fields[$key]->value != '')
						echo 'setCalendar(\'' . $key . '\',' . date("Y,n,j",strtotime($this->fields[$key]->value)) . ');';
					echo '</script>';
				}else{
					// Set the display size, if it is a small field then limit it
					$size = ($this->fields[$key]->length <= 30)?$this->fields[$key]->length:30;
					// Display the Input Field
					echo '<input name="' . $key . '" id="' . $key . '" type="text" size="' . $size . '" maxlength="64" value="' . stripslashes($this->fields[$key]->value) . '" />';
				}
				// If there is an error show it and end the field div
				echo (($this->error[$key] != '')?'<p>' . stripslashes($this->error[$key]) . '</p></div>':'') . '</div>' . "\n";
			}
		}
		
		// Make all the Hidden Fields
		foreach($hidden as $field)
			echo '<input name="' . $field . '" id="' . $field . '" type="hidden" value="' . stripslashes($this->fields[$field]->value) . '" />' . "\n";
		
		// End the Fieldset
		echo '</fieldset>' . "\n";
	}
	
	/**
	* Display the Item in Plain Text
	*
	* Displays the single items to be viewed only.
	*
	* @param array, string
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
	* Display a list of Classes
	*
	* Displays a list of classes according the the list criteria and sort order
	*
	* @param array, string, array
	* @todo Write this Function
	* @return NULL
	*/
	function DisplayList($display='',$format=array(),$options=array()){
		// Setup the Sort Sessions
		$_SESSION[$this->table . '_sort'] = ($_GET['sort'] != '')?$_GET['sort']:$_SESSION[$this->table . '_sort'];
		$_SESSION[$this->table . '_order'] = ($_GET['order'] != '')?$_GET['order']:$_SESSION[$this->table . 'order'];
		
		// Get the List of Customers
		$list = $this->GetList($display, $_SESSION[$this->table . '_sort'], $_SESSION[$this->table . '_order']);
		
		// If there is items
		if (is_array($list)){
			// Simplify the order
			$order = ($_SESSION[$this->table . '_order'] == 'desc')?'asc':'desc';
			// Start the table
			echo '<table border="0" cellspacing="0" cellpadding="2" width="99%" class="small" summary="List of Records from ' . htmlspecialchars(stripslashes($this->table)) . '">' . "\n";
			
			// Rearrange the Fields if there is a custom display
			$show = array();
			if(is_array($display)){
				foreach($display as $key=>$data)
					$show[$data] = ($this->fields[$data]->label != '')?$this->fields[$data]->label:ucfirst(str_replace('_',' ',$data));
			}else{
				if (is_array($this->fields))
					foreach($this->fields as $key=>$data)
						$show[$key] = ($data->label != '')?$data->label:ucfirst(str_replace('_',' ',$key));
			}
			// Display the Header
			echo "\n" . '<tr>';
			foreach($show as $key=>$column)
				echo "\t" . '<th scope="col"><a href="' . basename($_SERVER['PHP_SELF']) . '?sort=' . $key . '&amp;order=' . $order . '" title="Order by ' . $column . '">' . $column . (($_SESSION[$this->table . '_sort'] == $key)? '<img src="' . WS_SIMPL . WS_SIMPL_IMAGE . $_SESSION[$this->table . '_order'] . '.gif" align="top" width="17" height="17" alt="' . $_SESSION[$this->table . '_order'] . '" />' : '') . '</a></th>' . "\n";
			echo '</tr>' . "\n";
			
			// Loop through all the items
			$i=1;
			foreach($list as $field=>$data){
				echo '<tr' . (($i%2 == 0)?' class="odd"':'') . '>' . "\n";
				foreach($show as $key=>$column){
					// Overwrite the DB data with usable data
					if (is_array($options[$key])){
						// Create Local Value
						$value = $data[$key];
						// Get the Option Value
						$data[$key] = $options[$key][$value];
					}
					// Display the Data
					echo "\t" . '<td>';
					$end = '';
					// If there is links
					if (is_array($format) && $format[$key] != ''){
						printf($format[$key],$data[$this->primary],$data[$key]);
					}else{
						echo ($data[$key] != '')?$data[$key]:'&nbsp;';
					}
					echo $end;
					echo '</td>' . "\n";
				}
				
				echo '</tr>' . "\n";
				$i++;
			}
			// End the Table
			echo '</table>';
		}else{
			// If there is not items in the list
			echo '<p>Currently there is no Items, please try again later.</p>' . "\n";
		}
	}
}
?>