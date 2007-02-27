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
	private $name;
	/**
	* @var string 
	*/
	private $label;
	/**
	* @var string 
	*/
	private $example;
	/**
	* @var various 
	*/
	private $value;
	/**
	* @var string 
	*/
	private $error;
	/**
	* @var string 
	*/
	private $validate;
	/**
	* @var boolean 
	*/
	private $required = false;
	/**
	* @var int 
	*/
	private $length;
	/**
	* @var string 
	*/
	private $type;
	/**
	 * @var int
	 */
	private $primary = 0;
	
	/**
	 * Field Constructor
	 * 
	 * @return bool
	 */
	public function __construct(){
		return true;
	}
	
	/**
	* Get Field Property
	*
	* @param $property string
	* @return bool
	*/
	public function Get($property){
		// Retrun the value of the property
		return $this->$property;
	}
	
	/**
	* Set Field Property
	*
	* @param $property string
	* @param $value mixed
	* @return bool
	*/
	public function Set($property, $value){
		// Set the new value to the property
		
		
		if ($value != '' && $property == 'value' && $this->Get('type') == 'int'){
			$this->$property = (int)$value;
		}else
			$this->$property = $value;
			
		return true;
	}
	
	/**
	 * Validate the Field against the vaildate type
	 * 
	 * @return bool
	 */
	public function Validate(){
		global $myValidator;
		
		// Check to see if there is already an errror
		if ($this->Get('error') != '')
			return false;
			
		// Check to see if it is required first
		if ($this->Get('required') == true && $this->Get('value') == NULL){
			// Set the Error
			$this->Set('error', $this->Label() . ' is required.');
			return false;
		}
		
		// Validate agaist the regular expression
		if ($this->Get('validate') != '' && $myValidator->Check($this->Get('validate'), $this->Get('value'))){
			// Set the Error
			$this->Set('error', $this->ErrorString());
			return false;
		}
		
		// Make sure it is within the correct length
		if (strlen((string)$this->Get('value')) > $this->Get('length')){
			// Set the Error
			$this->Set('error', $this->Get('label') . ' is too long.');
			return false;
		}
		
		return true;
	}
	
	/**
	 * Get a human readable label for the field
	 * 
	 * @param $append string of test to append to the label
	 * @return string
	 */
	public function Label($append=''){
		// Get the label
		$str = $this->Output($this->Get('label'));
		
		// If no label use the name
		if (trim($str) == '')
			$str = ucfirst(str_replace('_',' ',$this->Get('name')));

		// Remove any ":" from the label
		if (substr($str,-1) == ':')
			$str = substr($str,0,-1);
		
		return $str . $append;
	}

	/**
	 * XHTML Form field
	 * 
	 * @return bool
	 */
	public function Form($options='', $config=''){
		$output = '<div class="field_' . $this->Get('name') . '">';
		$output .= '<label for="' . $this->Get('name') . '">';
		$output .= ($this->Get('required'))?'<em>*</em>':'';
		$output .= $this->Label(':');
		$output .= '</label>';
		$output .= ($this->Get('error') != '')?'<div class="error">':'';

		// If passing in a class call its Form function
		if(is_object($options)){
			switch(get_class($options)){
				// If we are uploading a file
				case 'Upload':
					// If there is something in the field
					if ($this->Get('value') != ''){
						$output .=  '<div id="form_' . $this->Get('name') . '">' . $this->Get('value') . ' <input name="remove[]" type="checkbox" value="' . $this->Get('name') . '" id="remove_' . $this->Get('name') . '" /> Remove File</div>';
						$output .=  '<input name="' . $this->Get('name') . '" id="' . $this->Get('name') . '" type="hidden" value="' . $this->Get('value') . '" />' . "\n";
					}else{
						$output .=  '<input name="' . $this->Get('name') . '" id="' . $this->Get('name') . '" type="file" />';
					}
					break;
				default:
					// Custom Form
					$output .= $options->Form($config='');
					break;
			}
		}else if (is_array($options)){
			// Multi Options
			switch($config){
				case 'radio':
				foreach($options as $key=>$value){
					$selected = ($this->Get('value') == (string)$value)?' checked="checked"':'';
					$each .= '<div><input name="' . $this->Get('name') . '" type="radio" value="' . $key . '" id="' . $this->Get('name') . '_' . $key . '"' . $selected . ' /><label for="' . $this->Get('name') . '_' . $key . '">' . $this->Output($value) . '</label></div>';
				}
				break;
				case 'checkbox':
				$split = split(',',$this->Get('value'));
				foreach($options as $key=>$value){
					$selected = (in_array($key,$split))?' checked="checked"':'';
					$each .= '<div><input name="' . $this->Get('name') . '[]" type="checkbox" value="' . $key . '" id="' . $this->Get('name') . '_' . $key . '"' . $selected . ' /><label for="' . $this->Get('name') . '_' . $key . '">' . $this->Output($value) . '</label></div>';
				}
				break;
				default:
				$each .= '<select name="' . $this->Get('name') . '" id="' . $this->Get('name') . '">' . "\n";
				foreach($options as $key=>$value){
					$selected = ($this->Get('value') == (string)$key)?' selected="selected"':'';
					$each .= '<option value="' . $key . '"' . $selected . '>' . $this->Output($value) . '</option>' . "\n";
				}
				$each .= '</select>';
				break;
			}
			$output .= '<div class="' . $config . '">' . $each . '</div>';
		}elseif($this->Get('type') == 'blob'){
			// Textarea
			$output .= '<div><textarea name="' . $this->Get('name') . '" id="' . $this->Get('name') . '" cols="50" rows="4">' . $this->Output($this->Get('value')) . '</textarea></div>' . "\n";
		}elseif($this->Get('type') == 'date'){
			// Date Field
			$value = ($this->Get('value') != '0000-00-00' && $this->Get('value') != '')?date("F j, Y",strtotime($this->Get('value'))):'';
			$output .= '<input name="' . $this->Get('name') . '" id="' . $this->Get('name') . '" type="text" size="18" maxlength="18" value="' . $this->Get('value') . '" /><button type="reset" id="' . $this->Get('name') . '_b">...</button>';	
			$output .= '<script type="text/javascript">Calendar.setup({ inputField : "' . $this->Get('name') . '", ifFormat : "%B %e, %Y", button : "' . $this->Get('name') . '_b"});</script>';
		}else{
			// Single Field
			$type = ($config[$this->Get('name')] != 'text')?$config[$this->Get('name')]:'text';
			$size = ($this->Get('length') < 30)?$this->Get('length'):30;
			$output .= '<input name="' . $this->Get('name') . '" id="' . $this->Get('name') . '" type="' . $type . '" size="' . $size . '" maxlength="' . $this->Get('length') . '" value="' .$this->Output($this->Get('value')) . '" />';
		}

		$output .= ($this->Get('example') != '')?'<div class="example"><p>' . $this->Output($this->Get('example')) . '</p></div>':'';
		$output .= ($this->Get('error') != '')?'<p>' . $this->Output($this->Get('error')) . '</p></div>':'';
		$output .= '</div>';
		echo $output;
	}
	
	public function View($options = array()){
		$output = '<tr>' . 
		$output .= '<th scope="row">' . $this->Label(':') . '</th>' .
		$output .= '<td>' . (($options[$this->Get('value')] != '')?$options[$this->Get('value')]:$this->Output($this->Get('value'))) . '</td>' .
		$output .= '</tr>';
		
		echo $output;
	}
	
	/**
	 * Get the plain text error type
	 * 
	 * @return string
	 */
	private function ErrorString(){
		// Depending on the type return the correct error
		return 'Field is not a valid [type]';
	}

	/**
	 * Format output
	 *
	 * @param $string string
	 * @return string
	 */
	private function Output($string){
		return htmlspecialchars(stripslashes($string));
	}
}
?>