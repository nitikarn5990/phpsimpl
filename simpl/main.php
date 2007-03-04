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
	public function __construct(){
		// Clear the Cache if needed
		if (isset($_GET['clear']) || CLEAR_CACHE === true)
			$this->Cache('clear');
	}
    
	/**
	 * Load a class file when needed.
	 * 
	 * @depricated
	 * @param $class A string containing the class name
	 * @return bool
	 */
	 public function Load($class){
	 	// Depricated but used for backwards compatibility
	 	if (!class_exists($class)){
	 		switch($class){
	 			case 'Feed':
	 				include_once(FS_SIMPL . 'feed.php');
	 				break;
	 		}
	 	}
	 	
	 	return true;
	 }

	 /**
	  * Does various Actions with the Cache
	  * 
	  * @param string $action
	  * @return bool
	  */
	 public function Cache($action){
	 	switch($action){
	 		case 'clear':
	 			$files = glob(FS_CACHE . "*.cache.php");
	 			break;
	 		case 'clear_query':
	 			$files = glob(FS_CACHE . "query_*.cache.php");
	 			break;
	 		case 'clear_table':
	 			$files = glob(FS_CACHE . "table_*.cache.php");
	 			break;
	 	}
	 	
	 	if (is_array($files)) 
		 	foreach($files as $file)
		 		unlink($file);
	 	
	 	return true;
	 }
}
?>