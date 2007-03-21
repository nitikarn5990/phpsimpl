<?php 
/**
 * Base Form Class
 * 
 * Used to create xhtml and validate forms
 *
 * @author Nick DeNardis <nick.denardis@gmail.com>
 */
class Form {
	/**
	 * @var array 
	 */
	protected $fields = array();	

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
	public function __construct($data, $required=array(), $labels=array(), $examples=array()){
		// Loop through all the data
		foreach ($data as $key=>$data){
			// Set all the field info
			$tmpField = new Field;
			$tmpField->Set('name', $key);
			$tmpField->Set('value', $data);
			$tmpField->Set('required', $required[$key]);
			$tmpField->Set('label', $labels[$key]);
			$tmpField->Set('example', $examples[$key]);
			
			// Add the field to the list
			$this->fields[$key] = $tmpField;
		}
	}

	/**
	 * Validate the Form
	 *
	 * @return bool
	 */
	public function Validate(){
		$valid = true;

		// Loop through the fields
		foreach ($this->fields as $name=>$field){
			// Validate the Field
			if (!$field->Validate() && $valid)
				$valid = false;
		}

		return $valid;
	}

	/**
	 * Check the Data from the class
	 *
	 * @return array
	 */
	public function CheckRequired(){
		// Validate the Form
		$this->Validate();

		// Return the error
		return $this->GetErrors();
	}

	/**
	 * Get Field Property
	 *
	 * @param $property string
	 * @param $field string
	 * @return mixed
	 */
	public function Get($property, $field){
		// Return the field property
		if ($this->IsField($field))
			return $this->fields[$field]->Get($property);
		
		return '';
	}

	/**
	 * Set Field Property
	 *
	 * Set a specific property about a field
	 *
	 * @param $property string
	 * @param $field string
	 * @param $value mixed
	 * @return bool
	 */
	public function Set($property, $field, $value){
		// Set the fields property
		if ($this->IsField($field))
			return $this->fields[$field]->Set($property, $value);
		
		return false;
	}

	/**
	 * Get Value
	 *
	 * @param $field string
	 * @return mixed
	 */
	public function GetValue($field){
		// Get the value of the field
		return $this->Get('value', $field);
	}

	/**
	 * Set Value
	 *
	 * @param $field string
	 * @param $value mixed
	 * @return bool
	 */
	public function SetValue($field, $value){
		// Set the value of the field
		return $this->Set('value', $field, $value);
	}

	/**
	 * Get Error
	 *
	 * @param $field string
	 * @return mixed
	 */
	public function GetError($field){
		// Get the error of the field
		return $this->Get('error', $field);
	}

	/**
	 * Set Error
	 *
	 * @param $field string
	 * @param $value mixed
	 * @return bool
	 */
	public function SetError($field, $value){
		// Set the error of the field
		$str = (is_array($value))?implode('<br />', $value):$value;

		return $this->Set('error', $field, $str);
	}
	
	/**
	 * Is Error
	 *
	 * @return bool
	 */
	public function IsError(){
		// Loop through the fields
		foreach ($this->fields as $name=>$field){
			// Check for Error
			if ($field->Get('error') != '')
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
	public function GetFields(){
		return array_keys($this->fields);
	}

	/**
	 * Is Field
	 *
	 * Check to see if a field exists
	 *
	 * @param $field string of the field
	 * @return bool
	 */
	public function IsField($field){
		return (is_object($this->fields[$field]));
	}

	/**
	 * Set Values
	 *
	 * Set all the Values of a Class
	 *
	 * @param $data An associtive array with all the keys and values for the object
	 * @return bool
	 */
	public function SetValues($data){
		Debug($data);

		// Loop through all the values
		foreach($this->fields as $name=>$field){
			// Set the Field Values
			switch($field->Get('type')){
				case 'date':
					if ($data[$name] != '')
						$this->Set('value', $name, date("Y-m-d",strtotime($data[$name])));
					break;
				case 'time':
					if ($data[$name] != '')
						$this->Set('value', $name, date("H:i",strtotime($data[$name])));
					break;
				default:
					if (is_array($data[$name]))
						$this->Set('value', $name, implode(',', $data[$name]));
					else
						$this->Set('value', $name, $data[$name]);
					break;
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
	public function ResetValues(){
		// Loop through all the fields
		foreach($this->fields as $name=>$field){
			$this->Set('value', $name, '');
			$this->Set('error', $name, '');
		}

		return true;
	}

	/**
	 * Get Values
	 *
	 * Get a list of all the fields and values in the class
	 *
	 * @return array
	 */
	public function GetValues(){
		$data = array();
		
		// Loop through all the fields
		foreach($this->fields as $name=>$field)
			$data[$name] = $field->Get('value');

		return $data;
	}

	/**
	 * Get Required
	 *
	 * Get a list of all the required fields in the class
	 *
	 * @return array
	 */
	public function GetRequired(){
		$data = array();
		
		// Loop through all the fields
		foreach($this->fields as $name=>$field)
			if ($field->Get('required') == true)
				$data[] = $name;

		return $data;
	}
	
	/**
	 * Set Required
	 *
	 * Set the required fields in the class
	 *
	 * @param $fields array
	 * @return bool
	 */
	public function SetRequired($fields){
		// Require an array
		if (!is_array($fields))
			return false;
		
		// Get the field names
		$keys = $this->GetFields();
			
		// Loop through all the fields
		foreach($keys as $name)
			$this->Set('required', $name, (in_array($name,$fields)));
		
		return true;
	}

	/**
	 * Get Errors
	 *
	 * Get a list of all the errors in the class
	 *
	 * @return array
	 */
	public function GetErrors(){
		$data = array();
		
		// Loop through all the fields
		foreach($this->fields as $name=>$field)
			if ($field->Get('error') != '')
				$data[$name] = $field->Get('error');

		return $data;
	}

	/**
	 * Simple Format
	 *
	 * Formats $this is a easy to read compact way to be used for Debug
	 * 
	 * @return string
	 */
	public function SimpleFormat(){
		// Start the Output
		$output = '';
		$required = $this->GetRequired();
		$errors = $this->GetErrors();
		$fields = $this->GetFields();
		
		// Format a nice Summary
		$output .= '<strong>Name:</strong>' . "\t" . get_class($this) . ' ' . "\t" . '<strong>Parent:</strong>' . "\t" . get_parent_class($this) . '' . "\n";
		$output .= '<strong>Required:</strong>' . "\n\t" . ((is_array($required))?implode(', ',$required):'No Required Fields') . "\n";
		$output .= '<strong>Errors:</strong>' . "\n";
		if (count($errors) > 0)
			foreach($errors as $name=>$error)
				$output .= "\t" . $name . ' => ' . $error . "\n";
		else
			$output .= "\t" . 'No Errors' . "\n";
		$output .= '<strong>Fields:</strong>' . "\n";
		if (count($fields) > 0)
			foreach($fields as $name=>$field)
				$output .= "\t" . $name . ' => ' . $field->Get('value') . (($errors[$name] != '')?' <strong>:</strong> ' . $errors[$name]:'') . "\n";
		else
			$output .= "\t" . 'No Fields' . "\n";
		
		return $output;
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
	public function Form($display='', $hidden=array(), $options=array(), $config=array(), $omit=array()){ 
		// Make sure things are arrays if required
		if(!is_array($omit))
			$omit = array($omit);
		
		// Rearrange the Fields if there is a custom display
		$show = array();
		if(is_array($display)){
			// If there is a custome Display make the array
			foreach($display as $key=>$data)
				$show[] = $data;
				
			// Loop through all the fields to find orphans and add them to the hidden array so we dont loose data
			foreach($this->fields as $key=>$data)
				if (!in_array($key,$show) && !in_array($key,$hidden) && !in_array($key,$omit)){
					if ($this->GetError($key) != '')
						Alert($this->GetError($key));
					$hidden[] = $key;
				}
		}else{
			// If there is fields in the db table make the show array
			foreach($this->fields as $key=>$data)
				if (!in_array($key,$hidden) && !in_array($key,$omit))
					$show[] = $key;
		}
		
		// Start the fieldset
		echo '<fieldset><legend>Information</legend>' . "\n";
		
		// Show the fields
		foreach($show as $field)
			if (!in_array($field, $omit))
				$this->fields[$field]->Form($options[$field], $config[$field]);
		
		// Show the hidden
		foreach($hidden as $field)
			echo '<input name="' . $field . '" type="hidden" value="' . stripslashes($this->fields[$field]->Get('value')) . '" />' . "\n";
		
		// End the fieldset
		echo '</fieldset>' . "\n";
	}
	
	/**
	 * Format output
	 *
	 * @param $string string
	 * @return string
	 */
	protected function Output($string){
		return htmlspecialchars(stripslashes($string));
	}
}
?>