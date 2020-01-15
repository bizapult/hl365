<?php
function paypal_api_php_client_autoload_booki($className) {
	$dirs = array(
		'lib/'
		, 'lib/auth/' 
		, 'lib/exceptions/'
		, 'lib/formatters/'
		, 'lib/handlers/'
		, 'lib/ipn/'
		, 'lib/services/'
		, 'lib/services/PayPalAPIInterfaceService/'
	);
	foreach( $dirs as $dir ) {
		$filePath = dirname(__FILE__) . '/' . $dir . $className . '.php';
		if (file_exists($filePath)) {
			require_once($filePath);
			return;
		}
	}
}

spl_autoload_register('paypal_api_php_client_autoload_booki');
