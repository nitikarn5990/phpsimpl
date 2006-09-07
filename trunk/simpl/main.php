<?php
/**
 * Created on Sep 5, 2006
 * main.php
 */
class Simpl {
	/**
	* Class Constructor
	*
	* Creates a Simpl Class with nothing in it
	*
	* @return NULL
	*/
    function Simpl(){ }
    
	/**
	 * Load a class file when needed.
	 * 
	 * @param $class A string containing the class name
	 * @return bool
	 */
	 function Load($class){
	 	// First check to make sure the class does not already exist
	 	if (!class_exists($class)){
	 		// Include the correct file for the class
	 		switch($class){
	 			case 'Field':
	 			case 'Form':
	 				include_once(FS_SIMPL . 'form.php');
	 				break;
	 			case 'Db':
	 				include_once(FS_SIMPL . 'db.php');
	 				break;
	 			case 'DbTemplate':
	 				include_once(FS_SIMPL . 'db_template.php');
	 				break;
	 			case 'Export':
	 				include_once(FS_SIMPL . 'export.php');
	 				break;
	 			case 'Upload':
	 				include_once(FS_SIMPL . 'upload.php');
	 				break;
	 			case 'Email':
	 				include_once(FS_SIMPL . 'email.php');
	 				break;
	 			case 'Feed':
	 				include_once(FS_SIMPL . 'feed.php');
	 				break;
	 			case 'File':
	 				include_once(FS_SIMPL . 'file.php');
	 				break;
	 			case 'Folder':
	 				include_once(FS_SIMPL . 'folder.php');
	 				break;
	 			case 'Image':
	 				include_once(FS_SIMPL . 'image.php');
	 				break;
	 		}
	 	}
	 	
	 	return false;
	 }
}
?>
