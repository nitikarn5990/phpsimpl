<?php
/**
 * Active Record Class
 * Extends the DbTemplate class to give relational ability to DB tables
 *
 * @author Nick DeNardis <nick.denardis@gmail.com>
 * @link http://code.google.com/p/phpsimpl/
 */
class ActiveRecord extends DbTemplate {
	/**
	 * @var array
	 */
	private $belongs_to = array();
	/**
	 * @var array
	 */
	private $has_one = array();
	/**
	 * @var array
	 */
	private $has_many = array();
	/**
	 * @var array
	 */
	private $has_and_belongs_to_many = array();
	
	/**
	 * Active Record Constructor
	 * 
	 * @param string $table Table name
	 * @param string $database Database name
	 * @return bool
	 */
	public function __construct($table, $database){
		parent::__construct($table, $database);
	}
	
	/**
	 * Belongs To
	 * Foreign Key in $class table
	 * 
	 * @param object $class ActiveRecord
	 * @return bool
	 */
	public function BelongsTo($class){
		return array_push($this->belongs_to, $class);
	}
	
	/**
	 * Has One
	 * Foreign Key in $class table
	 * 
	 * @param object $class ActiveRecord
	 * @return bool
	 */
    public function HasOne($class){
		return array_push($this->has_one, $class);
    }
    
    /**
	 * Has Many
	 * Foreign Key in $class table
	 * 
	 * @param object $class ActiveRecord
	 * @return bool
	 */
    public function HasMany($class){
		return array_push($this->has_many, $class);
    }
    
     /**
	 * Has and Belongs to Many
	 * Foreign Key in $class table
	 * 
	 * @param object $class ActiveRecord
	 * @return bool
	 */
    public function HasAndBelongsToMany($class){
		return array_push($this->has_and_belongs_to_many, $class);
    }
	
	public function __destruct() {
		// Get a list of all the class variables
		unset($this);
	}
}
?>