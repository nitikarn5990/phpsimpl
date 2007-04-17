<?php
class Post extends DbTemplate {
	/**
	 * Class Constuctor
	 * 
	 * @param $data array
	 * @return null
	 */
	function __construct($data=''){
		// Pull the defined yes/no array
		global $yesno;
		
		$required = array('title','body');
		$table = 'post';
		$labels = array('is_published'=>'Published:','author_id'=>'Author:','category'=>'Category:');
		$examples = array('category'=>'ex. PHP, MySQL, Cars, XML, PHPSimpl');
		
		$this->DbTemplate($data, $required, $labels, $examples, $table);
		
		// Set the Display
		$this->SetDisplay(array('title','author_id','category','is_published','body'));
		
		// Set the Options
		$this->SetOptions(array('is_published' => $yesno));
	}
}

class Author extends DbTemplate {
	/**
	 * Class Constuctor
	 * 
	 * @param $data array
	 * @return null
	 */
	function __construct($data=''){
		$required = array('first_name','last_name','email');
		$table = 'author';
		$labels = array();
		$examples = array();
		
		$this->DbTemplate($data, $required, $labels, $examples, $table);
		
		// Set the Display
		$this->SetDisplay(array('first_name','last_name','email'));
	}
}
?>
