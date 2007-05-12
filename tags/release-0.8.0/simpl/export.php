<?php
/**
 * Base Class for Exporting a XLS file
 *
 * @author Rob Vrabel <jiggilo@gmail.com>
 * @link http://code.google.com/p/phpsimpl/
 */
class Export {
	/**
	 * @var array
	 */
	private $display;
	/**
	 * @var array
	 */
	private $data;
	/**
	 * @var string
	 */
	private $file_name;
	/**
	 * @var string
	 */
	private $output;
	
	/**
	 * Class Constructor
	 *
	 * Creates an exported file from a given array
	 *
	 * @param $data Array of items
	 * @param $display Array
	 * @param $file_name String of the filename
	 * @return null
	 */	
	public function __construct($data='', $display='', $file_name='') {
		$this->display = $display;
		$this->data	= $data;
		$this->file_name = $file_name;
		
		// If all the data is correct call GetXLS
		(is_array($this->data)) ? $this->GetXLS() : '';
	}
	
	/**
	 * GetXLS
	 *
	 * Creates an output string from the class data
	 *
	 * @return bool
	 */
	public function GetXLS() {
		// Make sure data is an array
		if(is_array($this->data)) {
			// Debug
			(!is_array($this->display)) ? Debug('GetXLS(), Display is not an array, gathering display from the data array') : '';
			
			// If there is a display go by those, otherwise get all the fields from the data array
			if(!is_array($this->display))
				foreach(end($this->data) as $key => $data)
					$this->display[$key] = ucfirst(str_replace('_',' ',$key));
			
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
		// Debug
		Debug('GetXLS(), Data is not an array');
		
		return false;
	}

	/**
	* DisplayXLS
	*
	* Displays the XLS file
	*
	* @return null
	*/	
	public function DisplayXLS($output='') {
		// Check if they are sending ouput, if not just use the classes output
		($output == '')? $output = $this->output : '';
		
		// Display the XLS
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/force-download");
		header("Content-type: application/vnd.ms-excel");
		header("Content-disposition: attachment; filename=" .  str_replace(' ','_',strtolower($this->file_name)) . '_' . date("Y-m-d") . ".xls");
		header("Content-Transfer-Encoding: binary");
		print $output;
		exit;  
	}
}
?>