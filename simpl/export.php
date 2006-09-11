<?php
/**
* Base Class for Exporting a XLS file
*
* @author Rob Vrabel <rvrabel@wayne.edu>
*/
class Export {
	/**
	* @var array
	*/
	var $display;
	/**
	* @var array
	*/
	var $data;
	/**
	* @var string
	*/
	var $file_name;
	/**
	* @var string
	*/
	var $output;
	
	/**
	* Class Constructor
	*
	* Creates an exported file from a given array
	*
	* @param display array, data array
	* @return NULL
	*/	
	function Export($display='', $data='', $file_name='') {
		$this->display	= $display;
		$this->data		= $data;
		$this->file_name = $file_name;
		
		// If all the data is correct call GetXLS
		(is_array($this->display) && is_array($this->data)) ? $this->GetXLS() : '';
	}
	
	/**
	* GetXLS
	*
	* Creates an output string from the class data
	*
	* @return BOOL
	*/
	function GetXLS() {
		// Make sure display is an array
		if(is_array($this->display)) {
			// Make sure data is an array
			if(is_array($this->data)) {
				// Filter these out
				$bad_output = array("\n", "\r", "\t");
				
				// Start the output
				$this->output = '';
				
				// Loop through all the fields in display to create the titles
				foreach($this->display as $key=>$title)
					$this->output .= $title . "\t";
					
				// Create a blank Line
				$this->output = substr($this->output,0,-1) . "\n";
	
				// Loop through all the data
				foreach($this->data as $id => $data){
					// Loop through all the displays
					foreach($this->display as $key => $display) {
						// Replace badchars 
						$this->output .= str_replace($bad_output, '', $data[$key]) . "\t";
					}
					// Create a new line
					$this->output = substr($this->output,0,-1) . "\n";
				}				
				return $this->output;
			}
			Debug('XLS(), Data is not an array');
		}
		Debug('XLS(), Display is not an array');
		return false;
	}

	/**
	* DisplayXLS
	*
	* Displays the XLS file
	*
	* @return NULL
	*/	
	function DisplayXLS($output='') {
		// Check if they are sending ouput, if not just use the classes output
		($output == '')? $output = $this->output : '';
		
		// Display the XLS
		header("Content-type: application/vnd.ms-excel");
		header("Content-disposition: attachment; filename=" .  str_replace(' ','_',strtolower($this->file_name)) . '_' . date("Y-m-d") . ".xls");
		print $output;
		exit;  
	}
}
?>