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
	 * @param $directory	The directory where the file is sitting
	 * @return 				NULL
	 */
	function Folder($folder_name,$directory){
		//If the  folder name doesn't end in '/', then append it at the end 
		if(substr($folder_name,-1) == '/') {
			// Set the Local variables
			$this->folder_name = $folder_name;
		} else {
			//append '/' at the end
			$this->folder_name = $folder_name . '/';
		}
		
		// If there is directory passed, set the directory
		if (isset($directory))
			$this->directory = $directory;
	}
	
	
	/**
	 * Move the folder
	 *
	 * Move the folder to another location
	 * 
	 * @param $new_directory string the directory to which we are moving the folder
	 * @return bool
	 */  
	function Move($new_directory){
		//if the new directory exists and is writable
		if(is_dir($new_directory) && (is_writable($new_directory))){
			//if no folder with the same name exists in the new directory
			if(!is_dir($new_directory . $this->folder_name) ) {
				//move the folder to the new directory
				if( rename($this->directory . $this->folder_name, $new_directory . $this->folder_name) ) {
					if(chmod($new_directory . $this->folder_name, 0775)) {
						$this->directory = $new_directory;
						return true;
					}
				}
			}
		}
		return false;
	}
	
	/**
	 * Check if the function is writable
	 * 
	 * The function checks if the folder exists and if it is writable
	 * 
	 * @param NULL
	 * @return bool
	 */
	function IsWritable(){
		//if the folder exists
		if(is_dir($this->directory . $this->folder_name)) {
			//check if it is writable
			if(is_writable($this->directory . $this->folder_name)){
				return true;
			}
		}
		return false;
	}
	
	
	/**
	 * Make the folder writable
	 * 
	 * @param NULL
	 * @return bool
	 */
	function MakeWritable(){
		//if the folder exists
		if(is_dir($this->directory . $this->folder_name)) {
			//if it is already writable return true
			if(is_writable($this->directory . $this->folder_name)){
				return true;
			} else {
				//change permissions of the folder
				if(chmod($this->directory . $this->folder_name, 0755)){
					return true;
				}
			}
		}
		return false;
	} 
	
	/**
	 * Deletes the folder
	 * 
	 * @todo on force check for subfolders also
	 * 
	 * @param @force bool if the parameter is true the function deletes all the subfiles of the folder also
	 * @return bool;
	 */
	function Delete($force=false){
		if($force != false) {
			$path = $this->directory . $this->folder_name;
			$sub_folders = $this->DirList();
			
			
		/*
			$sub_files = $this->DirList();
			if(is_array($sub_files)) {
				foreach($sub_files as $file) {
					if(!unlink($this->directory . $this->folder_name . $file)) {
						return false;
					}
				}
			}*/
		}
	
		if(rmdir($this->directory . $this->folder_name)) {
			return true;
		} 	
		return false;	
	}
	
	/**
	 * Directory Listing
	 * 
	 * Lists the subfolders and files of directory
	 * 
	 * @param NULL
	 * @return array of the sub-folders and sub-files
	 */
	function DirList(){
		$files = scandir($this->directory . $this->folder_name);
		if(is_array($files)) {
			foreach($files as $pos => $file){
				if( ($file == '.') || ($file == '..'))
					unset($files[$pos]);
			}
			return $files;
		}
		return false;
	}
	
	/**
	 * Renames a folder
	 * 
	 * @param $new_folder string name of the new folder
	 * @return bool
	 */
	function Rename($new_folder){
		//if the folder exists
		if(is_dir($this->directory . $this->folder_name)) {
			//if folder with the new name doesnt already exist
			if(!is_dir($this->directory . $new_folder)) {
				//rename the folder to the new name
				if (rename($this->directory . $this->folder_name, $this->directory . $new_folder)){
					if(substr($new_folder,-1) == '/') {
						$this->folder_name = $new_folder;
					} else {
						$this->folder_name = $new_folder . '/';
					} 
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * Checks if the folder exists in the filesystem
	 * 
	 * @param NULL
	 * @return bool 
	 */
	function Exists() {
		//if the folder exists
		if(is_dir($this->directory . $this->folder_name)) {
			return true;
		}
		return false;
	}
	
	/**
	 * Creates a folder
	 * 
	 * Creates a folder and makes it writable
	 * 
	 * @param NULL
	 * @return bool
	 */
	function Create() {
		//if the folder exists make it writable 
		if(is_dir($this->directory . $this->folder_name)) {
			//change persmissions of folder
			if(chmod($this->directory . $this->folder_name, 0775)){
					return true;
			}
		} else {
			//create the folder
			if(mkdir($this->directory . $this->folder_name)) {
				//change persmissions of the folder
				if(chmod($this->directory . $this->folder_name, 0775)) {
					return true;
				}
			}
		}
		return false;
	}
}
?>
