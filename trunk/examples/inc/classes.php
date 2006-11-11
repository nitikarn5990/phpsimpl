<?php
/**
 * Created on Nov 1, 2006
 * Filename classes.php
 */
class Post extends DbTemplate {
	/**
	 * Class Constuctor
	 * 
	 * @param $data array
	 * @return null
	 */
	function __construct($data=''){
		$this->required = array('title','body');
		$this->table = TB_POST;
		$labels = array('is_published'=>'Published:','author_id'=>'Author:','category'=>'Category:');
		$examples = array('category'=>'ex. PHP, MySQL, Cars, XML, PHPSimpl');
		
		$this->DbTemplate($data, $this->required, $labels, $examples, $this->table);
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
		$this->required = array('first_name','last_name','email');
		$this->table = TB_AUTHOR;
		$labels = array();
		$examples = array();
		
		$this->DbTemplate($data, $this->required, $labels, $examples, $this->table);
	}
}
?>
