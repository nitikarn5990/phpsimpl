<?php
// Include the Folder Class
$mySimpl->Load('Folder');

/**
* Base File Class
*
* Used to manipulate files on the server
*
* @author Nick DeNardis <nick.denardis@gmail.com>
*/
class File extends Folder {
	/**
	* @var string 
	*/
	var $filename;
	
	/**
	 * File Constructor
	 * 
	 * @param $filename		String containing the filename that is in question
	 * @param $direcotry	The directory where the file is sitting
	 * @return 				NULL
	 */
	function File($filename,$directory=''){
		// Set the Local variables
		$this->filename = $filename;
		// If there is directory passed, set the directory
		if (isset($directory))
			$this->directory = $directory;
	}
}
?>
