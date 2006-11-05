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
		$labels = array('is_published'=>'Published:','user'=>'Author:','category'=>'Category:');
		$examples = array('category'=>'ex. PHP, MySQL, Cars, XML, PHPSIMPL');
		
		$this->DbTemplate($data, $this->required, $labels, $examples, $this->table);
	}
}
?>
