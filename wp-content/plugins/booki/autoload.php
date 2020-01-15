<?php
function Booki_Autoloader($value) {
	$nameSplit = explode('_', $value);
	if ($nameSplit[0] !== 'Booki') {
		return;
	}
	$className = implode('_', array_slice($nameSplit, 1));

	$dirs = array(
		'base/' 
		, 'controller/'
		, 'controller/base/'
		, 'controller/utils/'
		, 'domainmodel/base/'
		, 'domainmodel/entities/'
		, 'domainmodel/repository/'
		, 'domainmodel/service/base/'
		, 'domainmodel/service/'
		, 'exception/'
		, 'gen/'
		, 'infrastructure/actions/'
		, 'infrastructure/ajax/base/'
		, 'infrastructure/ajax/'
		, 'infrastructure/emails/base/'
		, 'infrastructure/emails/'
		, 'infrastructure/gcal/'
		, 'infrastructure/handlers/base/'
		, 'infrastructure/handlers/'
		, 'infrastructure/payment/gateways/base/'
		, 'infrastructure/payment/gateways/'
		, 'infrastructure/session/'
		, 'infrastructure/templates/'
		, 'infrastructure/ui/builders/'
		, 'infrastructure/ui/lists/base/'
		, 'infrastructure/ui/lists/'
		, 'infrastructure/ui/'
		, 'infrastructure/utils/'
		, 'infrastructure/widgets/'
	);
	foreach( $dirs as $dir ) {
		$filePath = dirname(__FILE__) . '/lib/' . $dir . $className . '.php';
		if (file_exists($filePath)) {
			require_once($filePath);
			return;
		}
	}
}

spl_autoload_register('Booki_Autoloader');
?>