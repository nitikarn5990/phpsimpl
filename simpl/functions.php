<?php
/**
* Display an array of alerts with a div class
*
* @param $alerts An Array with the alerts
* @param $type A string with the type of alert, usually ("error","success")
* @return NULL
*/
function Alert($alerts, $type=''){
	// Decide what class to display
	$class = ($type == '')?'Error':$type;
	
	//Display all errors to user
	if ( is_array($alerts) && count($alerts) > 0){
		while ( list($key,$data) = each($alerts) ){
			echo '<div class="form' . ucfirst($class) . '" id="form' . ucfirst($class) . '"><p>' . $data . '</p></div>'. "\n";
		}
	}else if ( is_string($alerts) ){
		echo '<div class="form' . ucfirst($class) . '" id="form' . ucfirst($class) . '"><p>' . $alerts . '</p></div>'. "\n";
	}
}

/**
 * Set a string as an alert
 * 
 * @param $alert A string with the Alert text in it
 * @param $type A string with the type of alert, usually ("error","success")
 * @return bool
 */
function SetAlert($alert,$type='error'){
	// Set the Alert into the correct session type
	$_SESSION[$type][] = $alert;
	
	return true;
}

/**
 * Is there a certain type of alerts waiting
 * 
 * @param $type A string containing the type of alert to return
 * @return array
 */
function IsAlert($type){
	// Return if there are strings waiting the the session type array
	return (is_array($_SESSION[$type]) && count($_SESSION[$type]) > 0);
}

/**
 * Get the Alert from the session
 * This will clear the session alerts when done.
 * 
 * @param $type A string containing the type of alert to return
 * @return array
 */
function GetAlert($type){
	// Get the array
	$return = $_SESSION[$type];
	// Reset the array
	$_SESSION[$type] = array();
	// Return the array
	return $return;
}

/**
* Display text or an array in HTML <pre> tags
*
* @param $text A mixed set, anything with a predefined format
* @return null
*/
function Pre($text){
	echo '<pre>';
	print_r($text);
	echo '</pre>';
}

/**
* Make an array into a string
*
* @param $array An array or class that needs to be formatted to a string
* @return string
*/
function arraytostring($array){
	// Start the output
	$text.="array(";
	// Get the numver of array items
	$count=count($array);
	$x=0;
	// Loop through each item
	foreach ($array as $key=>$value){
		$x++;
		// If there is an array within the array recursive it.
		if (is_array($value)) {$text.="\"" . $key . "\"=>".arraytostring($value); continue;}
		if (is_object($value)){$text.="\"" . $key . "\"=>'".serialize($value) . "',\n"; continue;}
		// Add more the output
		$text.="\"$key\"=>\"" . urlencode($value) . "\"";
		if ($count!=$x) $text.=",";
	}
	// End this complete array
	$text.="),";
	// Return the final result
	return $text;
}

/**
* Display Debug Information if set
*
* @param $output A mixed variable that needs to be outputted with predefined formatting
* @return NULL
*/
function Debug($output){
	if (DEBUG === true){
		echo '<pre class="debug">DEBUG:' . "\n";
		print_r($output);
		echo '</pre>';
	}
}

function search_split_terms($terms){
	$terms = preg_replace("/\"(.*?)\"/e", "search_transform_term('\$1')", $terms);
	$terms = preg_split("/\s+|,/", $terms);

	$out = array();
	foreach($terms as $term){
		$term = preg_replace("/\{WHITESPACE-([0-9]+)\}/e", "chr(\$1)", $term);
		$term = preg_replace("/\{COMMA\}/", ",", $term);
		$out[] = $term;
	}

	return $out;
}

function search_transform_term($term){
	$term = preg_replace("/(\s)/e", "'{WHITESPACE-'.ord('\$1').'}'", $term);
	$term = preg_replace("/,/", "{COMMA}", $term);
	return $term;
}

function search_escape_rlike($string){
	return preg_replace("/([.\[\]*^\$])/", '\\\$1', $string);
}

function search_db_escape_terms($terms){
	$out = array();
	foreach($terms as $term){
		$out[] = '[[:<:]]'.AddSlashes(search_escape_rlike($term)).'[[:>:]]';
	}
	return $out;
}

function search_rx_escape_terms($terms){
	$out = array();
	foreach($terms as $term){
		$out[] = '\b'.preg_quote($term, '/').'\b';
	}
	return $out;
}

function search_sort_results($a, $b){
	$ax = $a[score];
	$bx = $b[score];

	if ($ax == $bx){ return 0; }
	return ($ax > $bx) ? -1 : 1;
}

function search_html_escape_terms($terms){
	$out = array();

	foreach($terms as $term){
		if (preg_match("/\s|,/", $term)){
			$out[] = '"'.HtmlSpecialChars($term).'"';
		}else{
			$out[] = HtmlSpecialChars($term);
		}
	}

	return $out;	
}

function search_pretty_terms($terms_html){
	if (count($terms_html) == 1){
		return array_pop($terms_html);
	}

	$last = array_pop($terms_html);
	return implode(', ', $terms_html)." and $last";
}
?>