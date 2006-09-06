<?php
/**
 * Base Folder Class
 * 
 * Used to manipulate folders on the server
 * 
 * @todo	
 * @author 	Nick DeNardis <nick.denardis@gmail.com>
 */
class Folder {
	/**
	* @var string 
	*/
	var $directory;
	/**
	* @var string 
	*/
	var $folder_name;
	
	/**
	 * Folder Constructor
	 * 
	 * @param $folder_name	String containing the folder name that is in question
	 * @param $direcotry	The directory where the file is sitting
	 * @return 				NULL
	 */
	function File($folder_name,$directory){
		// Set the Local variables
		$this->$folder_name = $folder_name;
		// If there is directory passed, set the directory
		if (isset($directory))
			$this->directory = $directory;
	}
}
?>
