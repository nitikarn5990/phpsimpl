<?php
class Post extends DbTemplate {
	/**
	 * Class Constuctor
	 * 
	 * @param $data array
	 * @return null
	 */
	function __construct(){
		// Call the parent constructor
		parent::__construct('post', DB_DEFAULT);
		
		// Set the required
		$this->SetRequired(array('title','body'));
		
		// Set the labels
		$this->SetLabels(array('status'=>'Status:', 'author_id'=>'Author:', 'category'=>'Category:'));
		
		// Set the examples
		$this->SetExamples(array('category'=>'ex. PHP, MySQL, Cars, XML, PHPSimpl'));
		
		// Set the config
		$this->SetConfig(array('status' => 'radio'));

		// Set the default
		$this->SetDefaults(array('status' => 'Draft'));
		
		// Set the Display
		$this->SetDisplay(array('title','author_id','category','status','body'));
	}
}

class Author extends DbTemplate {
	/**
	 * Class Constuctor
	 * 
	 * @param $data array
	 * @return null
	 */
	function __construct(){
		// Call the parent constructor
		parent::__construct('author', DB_DEFAULT);
		
		// Set the required
		$this->SetRequired(array('first_name','last_name','email'));
		
		// Set the Display
		$this->SetDisplay(array('first_name','last_name','email'));
	}
}
?>