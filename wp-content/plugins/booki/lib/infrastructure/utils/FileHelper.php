<?php
class Booki_FileHelper{
	public static function read($path){
		$content = '';
		if ($handle = fopen($path, 'rb')) {
			$len = filesize($path);
			if ($len > 0){
				$content = fread($handle, $len);
			}
			fclose($handle);
		}
		return trim($content);
	}
	public static function parseINI($filepath, $processSections = false) {
		$funcs = explode(',', ini_get('disable_functions'));
		if(!in_array('parse_ini_file', $funcs) && function_exists('parse_ini_file')){
			return parse_ini_file($filepath, $processSections);
		}
		return null;
	}
}
?>