<?php
/**
 * Autoload classes (no need to include them one by one)
 *
 * @param $className string
 */
function __autoload($className){
	if (is_file(FS_SIMPL . strtolower($className) . '.php'))
		include_once(FS_SIMPL . strtolower($className) . '.php');
}

/**
 * Display an array of alerts with a div class
 *
 * @param $alerts An Array with the alerts
 * @param $type A string with the type of alert, usually ("error","success")
 * @return NULL
 */
if (!function_exists('Alert')){
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
}

/**
 * Set a string as an alert
 * 
 * @param $alert A string with the Alert text in it
 * @param $type A string with the type of alert, usually ("error","success")
 * @return bool
 */
if (!function_exists('SetAlert')){
	function SetAlert($alert,$type='error'){
		// Set the Alert into the correct session type
		if (is_array($alert))
			foreach($alert as $value)
				$_SESSION[$type][] = $value;
		else
			$_SESSION[$type][] = $alert;
		
		return true;
	}
}

/**
 * Is there a certain type of alerts waiting
 * 
 * @param $type A string containing the type of alert to return
 * @return array
 */
if (!function_exists('IsAlert')){
	function IsAlert($type){
		// Return if there are strings waiting the the session type array
		return (is_array($_SESSION[$type]) && count($_SESSION[$type]) > 0);
	}
}

/**
 * Get the Alert from the session
 * This will clear the session alerts when done.
 * 
 * @param $type A string containing the type of alert to return
 * @return array
 */
if (!function_exists('GetAlert')){
	function GetAlert($type){
		// Get the array
		$return = $_SESSION[$type];
		// Reset the array
		$_SESSION[$type] = array();
		// Return the array
		return $return;
	}
}

/**
 * Display text or an array in HTML <pre> tags
 *
 * @param $text A mixed set, anything with a predefined format
 * @return null
 */
if (!function_exists('Pre')){
	function Pre($text, $ip=''){
		$ready = true;
		
		if (is_string($ip) && $ip != '' && $_SERVER['REMOTE_ADDR'] != $ip)
			$ready = false;
		else if (is_array($ip) && !in_array($_SERVER['REMOTE_ADDR'], $ip))
			$ready = false;
		
		if ($ready == true){
			echo '<pre>';
			print_r($text);
			echo '</pre>';
		}
	}
}

/**
 * Display Debug Information if set
 *
 * @param $output A mixed variable that needs to be outputted with predefined formatting
 * @return NULL
 */
if (!function_exists('Debug')){
	function Debug($output, $class=''){
		if (DEBUG === true){
			echo '<pre class="debug' . (($class != '')?' ' . $class:'') . '">DEBUG:' . "\n";
			print_r($output);
			echo '</pre>';
		}
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

/**
 * Checks for multiarray (2 or more levels deep)
 * 
 * @param $multiarray Array
 * @return bool
 */
function isMultiArray($multiarray) {
  if (is_array($multiarray))
   foreach ($multiarray as $array)
     if (is_array($array))
       return true;
  return false;
}
?>