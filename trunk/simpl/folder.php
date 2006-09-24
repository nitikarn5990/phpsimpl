<?php
/**
 * Base Folder Class
 * 
 * Used to manipulate folders on the server
 * 
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
		Debug('Constructor(), Initializing values');
		//If the  folder name doesn't end in '/', then append it at the end 
		if(substr($folder_name,-1) == '/') {
			// Set the Local variables
			$this->folder_name = $folder_name;
		} else {
			//append '/' at the end
			$this->folder_name = $folder_name . '/';
		}
		
		// If there is directory passed, set the directory
		if (isset($directory)){
			if(substr($directory,-1) == '/') {
				// Set the Local variables
				$this->directory = $directory;
			} else {
				//append '/' at the end
				$this->directory = $directory . '/';
			}
		}
			
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
					Debug('Move(), Moving the folder from ' . $this->directory . ' to ' . $new_directory);
					if(chmod($new_directory . $this->folder_name, 0775)) {
						Debug('Move(), Changing permissions for folder ' . $this->folder . ' in directory ' . $new_directory);
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
				Debug('IsWritable(), The folder ' . $this->directory . $this->folder . ' is writable.');
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
				Debug('MakeWritable(), The folder ' . $this->directory . $this->folder . ' is already writable.');
				return true;
			} else {
				//change permissions of the folder
				if(chmod($this->directory . $this->folder_name, 0755)){
					Debug('MakeWritable(), Changing permissions of folder ' . $this->directory . $this->folder);
					return true;
				}
			}
		}
		return false;
	} 
	
	/**
	 * Delete subfolders and files recursively
	 * 
	 * Private function that deletes the sub files and sub-folders. the function is called from Delete function
	 * 
	 * @param $directory directory to be deleted
	 * @return bool
	 */
	private function delete_recursive($directory) {
   	  	//loop through for each directory/file that the function scandir returns
   	  	foreach (scandir($directory) as $folderItem) {
       		Debug('delete_recursive(), Looping through directory ' . $directory);
       		//skip for these two cases
       		if ($folderItem != "." AND $folderItem != "..") {
           		//if 'file' is a directory
           		if (is_dir($directory.$folderItem.'/')) {
               		//call the function recursively 
               		$this->delete_recursive( $directory.$folderItem.'/');
           		} else {
            		//delete the files within the directory
            		Debug('delete_recursive(), Deleting file ' . $directory . $folderItem);
            		unlink($directory . $folderItem);
           		}
       		}
   		}
   		//delete the sub-directories and the directory itself
   		Debug('delete_recursive(), Deleting directory ' . $directory);
		rmdir($directory);
   		return true;
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
		if($force == false) {
			//delete the directory
			if(rmdir($this->directory . $this->folder_name)) {
				Debug('Delete(), Deleting directory ' . $this->directory . $this->folder_name);
				return true;
			}
		} else {
			Debug('Delete(), Deleting sub-folders and sub-files recursively.');
			//call this function to delete the sub folders and sub files recursively
			if($this->delete_recursive($this->directory . $this->folder_name) ) {
				return true;
			}
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
			Debug('DirList(), Getting a list of files in directory ' . $this->directory);
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
					Debug('Rename(), Renaming folder ' . $this->directory . $this->folder_name . ' to ' . $this->directory . $new_folder);
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
		Debug('Exists(), Checking if the folder ' . $this->folder_name . ' exists in the directory ' . $this->directory);
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
			Debug('Create(), The folder ' . $this->folder_name . ' already exists in the directory ' .$this->directory);
			//change persmissions of folder
			if(chmod($this->directory . $this->folder_name, 0775)){
					return true;
			}
		} else {
			//create the folder
			if(mkdir($this->directory . $this->folder_name)) {
				Debug('Create(), Created folder ' . $this->folder_name . ' in directory ' . $this->directory);
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
