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
		$this->SetLabels(array('author_id'=>'Author:', 'category'=>'Category:'));
		
		// Set the examples
		$this->SetExamples(array('category'=>'ex. PHP, MySQL, Cars, XML, PHPSimpl'));

		// Set the default
		$this->SetDefaults(array('status' => 'Draft'));
		
		// Set the Display
		$this->SetDisplay(array('title','author_id','category','body'));
	}
	
	/**
	 * Display Form
	 * 
	 * @param $display array
	 * @param $hidden array
	 * @param $options array
	 * @param $config array
	 * @param $omit array
	 * @return string
	 */
	public function Form($display='', $hidden=array(), $options=array(), $config=array(), $omit=array(), $multi=false){ 
		// Get a list of all the authors
		$myAuthor = new Author;
		$authors = $myAuthor->GetList(array('first_name', 'last_name'),'last_name','DESC');
		$author_list = array();
		
		// Format the author list how we would like
		foreach($authors as $author_id=>$author)
			$author_list[$author_id] = $author['first_name'] . ' ' . $author['last_name'];
		
		// Add State Options
		$this->SetOption('author_id', $author_list, 'Please Select');
	
		return parent::Form($display, $hidden, $options, $config, $omit, $multi);
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

class Tag extends DbTemplate {
	/**
	 * Class Constuctor
	 * 
	 * @param $data array
	 * @return null
	 */
	function __construct(){
		// Call the parent constructor
		parent::__construct('tag', DB_DEFAULT);
		
		// Set the required
		$this->SetRequired(array('tag'));
	}
}

class PostTag extends DbTemplate {
	/**
	 * Class Constuctor
	 * 
	 * @param $data array
	 * @return null
	 */
	function __construct(){
		// Call the parent constructor
		parent::__construct('post_tag', DB_DEFAULT);
		
		// Set the required
		$this->SetRequired(array('tag_id', 'post_id'));
	}
	
	public function Sync($tag_list){
		// Split up the categories and make sure there is tags
		$tags = split(',', $tag_list);		
		
		// Get a list of all the tags currently
	
		
		$post_tags = array();
		foreach($tags as $tag){
			$myTag->ResetValues();
			$myTag->SetValue('tag', trim($tag));
			$myTag->GetList('tag_id', 'tag_id', 'ASC', 0, 1);
			if (count($myTag->results) == 1){
			
			}else{
				$myTag->Save();
				$post_tag = array($tag, $myTag->GetPrimary());
			}			
		}
		
		Pre($category_list);
	}
}
?>