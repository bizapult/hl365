<?php
/**
 * Theme storage manipulations
 *
 * @package WordPress
 * @subpackage PARKIVIA
 * @since PARKIVIA 1.0
 */

// Disable direct call
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Get theme variable
if (!function_exists('parkivia_storage_get')) {
	function parkivia_storage_get($var_name, $default='') {
		global $PARKIVIA_STORAGE;
		return isset($PARKIVIA_STORAGE[$var_name]) ? $PARKIVIA_STORAGE[$var_name] : $default;
	}
}

// Set theme variable
if (!function_exists('parkivia_storage_set')) {
	function parkivia_storage_set($var_name, $value) {
		global $PARKIVIA_STORAGE;
		$PARKIVIA_STORAGE[$var_name] = $value;
	}
}

// Check if theme variable is empty
if (!function_exists('parkivia_storage_empty')) {
	function parkivia_storage_empty($var_name, $key='', $key2='') {
		global $PARKIVIA_STORAGE;
		if (!empty($key) && !empty($key2))
			return empty($PARKIVIA_STORAGE[$var_name][$key][$key2]);
		else if (!empty($key))
			return empty($PARKIVIA_STORAGE[$var_name][$key]);
		else
			return empty($PARKIVIA_STORAGE[$var_name]);
	}
}

// Check if theme variable is set
if (!function_exists('parkivia_storage_isset')) {
	function parkivia_storage_isset($var_name, $key='', $key2='') {
		global $PARKIVIA_STORAGE;
		if (!empty($key) && !empty($key2))
			return isset($PARKIVIA_STORAGE[$var_name][$key][$key2]);
		else if (!empty($key))
			return isset($PARKIVIA_STORAGE[$var_name][$key]);
		else
			return isset($PARKIVIA_STORAGE[$var_name]);
	}
}

// Inc/Dec theme variable with specified value
if (!function_exists('parkivia_storage_inc')) {
	function parkivia_storage_inc($var_name, $value=1) {
		global $PARKIVIA_STORAGE;
		if (empty($PARKIVIA_STORAGE[$var_name])) $PARKIVIA_STORAGE[$var_name] = 0;
		$PARKIVIA_STORAGE[$var_name] += $value;
	}
}

// Concatenate theme variable with specified value
if (!function_exists('parkivia_storage_concat')) {
	function parkivia_storage_concat($var_name, $value) {
		global $PARKIVIA_STORAGE;
		if (empty($PARKIVIA_STORAGE[$var_name])) $PARKIVIA_STORAGE[$var_name] = '';
		$PARKIVIA_STORAGE[$var_name] .= $value;
	}
}

// Get array (one or two dim) element
if (!function_exists('parkivia_storage_get_array')) {
	function parkivia_storage_get_array($var_name, $key, $key2='', $default='') {
		global $PARKIVIA_STORAGE;
		if (empty($key2))
			return !empty($var_name) && !empty($key) && isset($PARKIVIA_STORAGE[$var_name][$key]) ? $PARKIVIA_STORAGE[$var_name][$key] : $default;
		else
			return !empty($var_name) && !empty($key) && isset($PARKIVIA_STORAGE[$var_name][$key][$key2]) ? $PARKIVIA_STORAGE[$var_name][$key][$key2] : $default;
	}
}

// Set array element
if (!function_exists('parkivia_storage_set_array')) {
	function parkivia_storage_set_array($var_name, $key, $value) {
		global $PARKIVIA_STORAGE;
		if (!isset($PARKIVIA_STORAGE[$var_name])) $PARKIVIA_STORAGE[$var_name] = array();
		if ($key==='')
			$PARKIVIA_STORAGE[$var_name][] = $value;
		else
			$PARKIVIA_STORAGE[$var_name][$key] = $value;
	}
}

// Set two-dim array element
if (!function_exists('parkivia_storage_set_array2')) {
	function parkivia_storage_set_array2($var_name, $key, $key2, $value) {
		global $PARKIVIA_STORAGE;
		if (!isset($PARKIVIA_STORAGE[$var_name])) $PARKIVIA_STORAGE[$var_name] = array();
		if (!isset($PARKIVIA_STORAGE[$var_name][$key])) $PARKIVIA_STORAGE[$var_name][$key] = array();
		if ($key2==='')
			$PARKIVIA_STORAGE[$var_name][$key][] = $value;
		else
			$PARKIVIA_STORAGE[$var_name][$key][$key2] = $value;
	}
}

// Merge array elements
if (!function_exists('parkivia_storage_merge_array')) {
	function parkivia_storage_merge_array($var_name, $key, $value) {
		global $PARKIVIA_STORAGE;
		if (!isset($PARKIVIA_STORAGE[$var_name])) $PARKIVIA_STORAGE[$var_name] = array();
		if ($key==='')
			$PARKIVIA_STORAGE[$var_name] = array_merge($PARKIVIA_STORAGE[$var_name], $value);
		else
			$PARKIVIA_STORAGE[$var_name][$key] = array_merge($PARKIVIA_STORAGE[$var_name][$key], $value);
	}
}

// Add array element after the key
if (!function_exists('parkivia_storage_set_array_after')) {
	function parkivia_storage_set_array_after($var_name, $after, $key, $value='') {
		global $PARKIVIA_STORAGE;
		if (!isset($PARKIVIA_STORAGE[$var_name])) $PARKIVIA_STORAGE[$var_name] = array();
		if (is_array($key))
			parkivia_array_insert_after($PARKIVIA_STORAGE[$var_name], $after, $key);
		else
			parkivia_array_insert_after($PARKIVIA_STORAGE[$var_name], $after, array($key=>$value));
	}
}

// Add array element before the key
if (!function_exists('parkivia_storage_set_array_before')) {
	function parkivia_storage_set_array_before($var_name, $before, $key, $value='') {
		global $PARKIVIA_STORAGE;
		if (!isset($PARKIVIA_STORAGE[$var_name])) $PARKIVIA_STORAGE[$var_name] = array();
		if (is_array($key))
			parkivia_array_insert_before($PARKIVIA_STORAGE[$var_name], $before, $key);
		else
			parkivia_array_insert_before($PARKIVIA_STORAGE[$var_name], $before, array($key=>$value));
	}
}

// Push element into array
if (!function_exists('parkivia_storage_push_array')) {
	function parkivia_storage_push_array($var_name, $key, $value) {
		global $PARKIVIA_STORAGE;
		if (!isset($PARKIVIA_STORAGE[$var_name])) $PARKIVIA_STORAGE[$var_name] = array();
		if ($key==='')
			array_push($PARKIVIA_STORAGE[$var_name], $value);
		else {
			if (!isset($PARKIVIA_STORAGE[$var_name][$key])) $PARKIVIA_STORAGE[$var_name][$key] = array();
			array_push($PARKIVIA_STORAGE[$var_name][$key], $value);
		}
	}
}

// Pop element from array
if (!function_exists('parkivia_storage_pop_array')) {
	function parkivia_storage_pop_array($var_name, $key='', $defa='') {
		global $PARKIVIA_STORAGE;
		$rez = $defa;
		if ($key==='') {
			if (isset($PARKIVIA_STORAGE[$var_name]) && is_array($PARKIVIA_STORAGE[$var_name]) && count($PARKIVIA_STORAGE[$var_name]) > 0) 
				$rez = array_pop($PARKIVIA_STORAGE[$var_name]);
		} else {
			if (isset($PARKIVIA_STORAGE[$var_name][$key]) && is_array($PARKIVIA_STORAGE[$var_name][$key]) && count($PARKIVIA_STORAGE[$var_name][$key]) > 0) 
				$rez = array_pop($PARKIVIA_STORAGE[$var_name][$key]);
		}
		return $rez;
	}
}

// Inc/Dec array element with specified value
if (!function_exists('parkivia_storage_inc_array')) {
	function parkivia_storage_inc_array($var_name, $key, $value=1) {
		global $PARKIVIA_STORAGE;
		if (!isset($PARKIVIA_STORAGE[$var_name])) $PARKIVIA_STORAGE[$var_name] = array();
		if (empty($PARKIVIA_STORAGE[$var_name][$key])) $PARKIVIA_STORAGE[$var_name][$key] = 0;
		$PARKIVIA_STORAGE[$var_name][$key] += $value;
	}
}

// Concatenate array element with specified value
if (!function_exists('parkivia_storage_concat_array')) {
	function parkivia_storage_concat_array($var_name, $key, $value) {
		global $PARKIVIA_STORAGE;
		if (!isset($PARKIVIA_STORAGE[$var_name])) $PARKIVIA_STORAGE[$var_name] = array();
		if (empty($PARKIVIA_STORAGE[$var_name][$key])) $PARKIVIA_STORAGE[$var_name][$key] = '';
		$PARKIVIA_STORAGE[$var_name][$key] .= $value;
	}
}

// Call object's method
if (!function_exists('parkivia_storage_call_obj_method')) {
	function parkivia_storage_call_obj_method($var_name, $method, $param=null) {
		global $PARKIVIA_STORAGE;
		if ($param===null)
			return !empty($var_name) && !empty($method) && isset($PARKIVIA_STORAGE[$var_name]) ? $PARKIVIA_STORAGE[$var_name]->$method(): '';
		else
			return !empty($var_name) && !empty($method) && isset($PARKIVIA_STORAGE[$var_name]) ? $PARKIVIA_STORAGE[$var_name]->$method($param): '';
	}
}

// Get object's property
if (!function_exists('parkivia_storage_get_obj_property')) {
	function parkivia_storage_get_obj_property($var_name, $prop, $default='') {
		global $PARKIVIA_STORAGE;
		return !empty($var_name) && !empty($prop) && isset($PARKIVIA_STORAGE[$var_name]->$prop) ? $PARKIVIA_STORAGE[$var_name]->$prop : $default;
	}
}
?>