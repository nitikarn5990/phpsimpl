<?php
/**
* Base Field Class
*
* Used to create individual fields on a form
*
* @author Nick DeNardis <nick.denardis@gmail.com>
*/
class Field {
	/**
	* @var string 
	*/
	var $name;
	/**
	* @var int 
	*/
	var $max_length;
	/**
	* @var int 
	*/
	var $numeric;
	/**
	* @var int 
	*/
	var $blob;
	/**
	* @var string 
	*/
	var $type;
	/**
	* @var int 
	*/
	var $length;
	/**
	* @var string 
	*/
	var $label;
	/**
	* @var string 
	*/
	var $example;
	/**
	* @var various 
	*/
	var $value;
	/**
	* @var string 
	*/
	var $error;
	
	/**
	 * Field Constructor
	 * 
	 * @param $data An Array of all the field properties and values
	 */
	function Field($data){
		// Make sure the data for the form fields is in an array
		if (is_array($data)){
			// Loop through all the fields
			foreach($data as $key=>$data){
				// Setup the form fields
				$this->$key = $data;
			}
			
			return true;
		}
		
		return false;
	}
}

/**
* Base Class for Forms
*
* @author Nick DeNardis <nick.denardis@gmail.com>
*/
class Form {
	/**
	* @var array 
	*/
	var $required;
	/**
	* @var array 
	*/
	var $error;
	/**
	* @var array 
	*/
	var $fields;
	
	/**
	* Class Constructor
	*
	* Creates a Form Class with all the information to use the Form functions
	*
	* @param $data An Array of all the values for the fields
	* @param $required An Array of all the required keys for the form
	* @param $labels An Array of all the custom labels for the form
	* @param $examples An Array of all the exmples for each form element
	* @return bool
	*/
	function Form($data, $required=array(), $labels=array(), $examples=array()){
		// Create the Combined Array
		$this->fields = array();
		foreach($data as $key=>$item){
			$fields[$key]->value = $item;
			
		}
	}
	
	/**
	* Check the Data from the class
	*
	* @return array
	*/
	function CheckRequired(){
		if (is_array($this->required)){
			while ( list($key,$data) = each($this->required) ){
				switch ($data){
					case 'email':
						if ( isset($this->fields[$data]->value) && !ereg("^[a-zA-Z0-9_\.-]+@[a-zA-Z0-9\.-]+\.[a-zA-Z]{2,4}$", $this->fields[$data]->value) )
							$this->error[$data] = 'The Email address you entered is not valid (username@domain.com), Please try again.';
						break;
					case 'wsu_phone':
						if ( isset($this->fields[$data]->value) && !ereg("^[0-9]+-[0-9]{4}$", $this->fields[$data]->value) )
							$this->error[$data] = 'The Phone number you entered is not valid (7-1234), Please try again.';
						break;
					case 'phone':
						if( isset($this->fields[$data]->value) && !ereg("^[0-9]{3}-[0-9]{3}-[0-9]{4}$", $this->fields[$data]->value) )
							$this->error[$data] = 'The Phone number you entered is not valid (123-456-1234), Please try again.';
						break;
					default:
						if (!isset($this->fields[$data]->value) || (string)$this->fields[$data]->value == '')
							$this->error[$data] = 'The ' . (($this->fields[$data]->label == '')?ucfirst(str_replace('_', ' ' , $data)):$this->fields[$data]->label) . ' field is required, Please try again.';
						break;
				}
			}
		}
		
		return $this->error;
	}
	
	/**
	* Get Field Property
	*
	* Get a specific property about a field
	*
	* @param $property string, $field string
	* @return bool
	*/
	function Get($property,$field){
		// Make sure there is fields and return the value
		if (is_array($this->fields))
			return $this->fields[trim($field)]->$property;
	}
	
	/**
	* Set Field Property
	*
	* Set a specific property about a field
	*
	* @param $property string, $field string, $value mixed
	* @return bool
	*/
	function Set($property,$field,$value){
		// Make sure there is fields
		if (is_array($this->fields)){
			$this->fields[trim($field)]->$property = $value;
			return true;
		}
		
		return false;
	}
	
	/**
	* Get Value
	*
	* Get the value of a field
	*
	* @return MIXED
	*/
	function GetValue($field){
		// Make sure there is fields and return the value
		if (is_array($this->fields))
			return $this->fields[trim($field)]->value;
	}
	
	/**
	* Set Value
	*
	* Set the value of a field
	*
	* @return BOOL
	*/
	function SetValue($field,$value){
		// Make sure there is fields
		if (is_array($this->fields)){
			$this->fields[trim($field)]->value = $value;
			return true;
		}
		
		return false;
	}
	
	/**
	* Get Error
	*
	* Get the error of a field
	*
	* @return MIXED
	*/
	function GetError($field){
		// Make sure there is fields and return the value
		if (is_string($this->error[$field]))
			return $this->error[$field];
		// There is no error set for this field
		return NULL;
	}
	
	/**
	* Set Error
	*
	* Set the error of a field
	*
	* @return BOOL
	*/
	function SetError($field,$value){
		// Make sure there is fields
		if ($value != ''){
			$this->error[$field] = $value;
			return true;
		}
		
		return false;
	}
	
	/**
	* Get Fields
	*
	* Get a list of all the fields in the database
	*
	* @return array
	*/
	function GetFields(){
		// Make sure there is fields and return the value
		if (is_array($this->fields)){
			$list = array();
			foreach($this->fields as $key=>$data)
				$list[] = $data->name;
			return $list;
		}
		
		return 0;
	}
	
	/**
	* Set Values
	*
	* Set all the Values of a Class
	*
	* @param $data An associtive array with all the keys and values for the object
	* @return bool
	*/
	function SetValues($data = array()){
		Debug($data);
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
		
		return true;
	}
	
	/**
	* Reset Values
	*
	* Reset all the Values of a Class
	*
	* @return bool
	*/
	function ResetValues(){
		// Reset all the Data for the Class
		if (is_array($this->fields))
			foreach($this->fields as $key=>$field){
				$this->SetValue($key,'');
				$this->SetError($key,'');
			}
		// Reset List and search
		$this->results = array();
		$this->search = '';
		
		return true;
	}
	
	/**
	 * Simple Format
	 * Formats $this is a easy to read compact way to be used for Debug
	 * 
	 * @return string
	 */
	function SimpleFormat(){
		// Start the Output
		$output = '';
		
		// Format a nice Summary
		$output .= '<strong>Name:</strong>' . "\t" . get_class($this) . ' ' . "\t" . '<strong>Parent:</strong>' . "\t" . get_parent_class($this) . '' . "\n";
		$output .= '<strong>Table:</strong>' . "\t" . $this->table . ' ' . "\t" . '<strong>Primary Key:</strong>' . "\t" . $this->primary . ' ' . "\t" . '<strong>Database:</strong>' . "\t" . $this->database . "\n";
		$output .= '<strong>Required:</strong>' . "\n\t" . ((is_array($this->required))?implode(', ',$this->required):'No Required Fields') . "\n";
		$output .= '<strong>Errors:</strong>' . "\n";
		if (is_array($this->error))
			foreach($this->error as $key=>$data)
				$output .= "\t" . $key . ' => ' . $data . "\n";
		else
			$output .= "\t" . 'No Errors' . "\n";
		$output .= '<strong>Fields:</strong>' . "\n";
		if (is_array($this->fields))
			foreach($this->fields as $key=>$data)
				$output .= "\t" . $key . ' => ' . $data->value . (($data->error != '')?' <strong>:</strong> ' . $data->error:'') . "\n";
		else
			$output .= "\t" . 'No Fields' . "\n";
		
		return $output;
	}
	
	function DisplayForm($display='', $hidden=array(), $options=array()){ 
		// Rearrange the Fields if there is a custom display
		$show = array();
		if(is_array($display)){
			// If there is a custome Display make the array
			foreach($display as $key=>$data)
				$show[$data] = ($this->fields[$data]->label != '')?$this->fields[$data]->label:ucfirst(str_replace('_',' ',$data));
			
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
						$show[$key] = ($data->label != '')?$data->label:ucfirst(str_replace('_',' ',$key));
				}
		}
		
		// Start the Fieldset
		echo '<fieldset><legend>Information</legend>' . "\n";
		
		// Show all the Visible Fields
		foreach($show as $key=>$field){
			// If the field is not in the hidden array
			if (!in_array($key,$hidden)){
				// Create the Field Div with the example, label and error if need be
				echo '<div>' . (($this->fields[$key]->example != '')?'<div class="example"><p>' . stripslashes($this->fields[$key]->example) . '</p></div>':'') . '<label for="' . $key . '">' . ((in_array($key,$this->required))?'<em>*</em>':'') . $field . ':</label>' . (($this->error[$key] != '')?'<div class="error">':'');
				
				// If there is specialty options
				if ($options[$key] != ''){
					// Start the Select Box
					echo '<select name="' . $key . '" id="' . $key . '">' . "\n";
					// Loop though each option
					foreach($options[$key] as $opt_key=>$opt_value){
						echo "\t" . '<option value="' . $opt_key . '"' . (($this->fields[$key]->value == $opt_key)?' selected="selected"':'') . '>' . stripslashes($opt_value) . '</option>' . "\n";
					}
					// End the Select Box
					echo '</select><br />' . "\n";
				}elseif($this->fields[$key]->type == 'blob'){
					// If it is a blob or text in the DB then make it a text area
					echo '<textarea name="' . $key . '" id="' . $key . '" cols="50" rows="4">' . stripslashes($this->fields[$key]->value) . '</textarea><br />' . "\n";
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
			echo '<input name="' . $field . '" type="hidden" value="' . stripslashes($this->fields[$field]->value) . '" />' . "\n";
		
		// End the Fieldset
		echo '</fieldset>' . "\n";
	}
}
?>