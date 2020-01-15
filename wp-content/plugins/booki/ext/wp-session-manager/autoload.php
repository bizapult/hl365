<?php
function wp_session_manager_autoload_booki($className) {
	$nameSplit = explode('_', $className);
	if ($nameSplit[0] === 'Booki') {
		$className = implode('_', array_slice($nameSplit, 1));
	}
	$filePath = dirname(__FILE__) . '/' . $className . '.php';

	if (file_exists($filePath)) {
		require_once($filePath);
		return;
	}
}

spl_autoload_register('wp_session_manager_autoload_booki');
