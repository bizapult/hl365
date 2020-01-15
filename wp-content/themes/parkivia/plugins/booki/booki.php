<?php
/* Booki support functions
------------------------------------------------------------------------------- */

// Theme init priorities:
// 9 - register other filters (for installer, etc.)
if (!function_exists('parkivia_booki_theme_setup9')) {
	add_action( 'after_setup_theme', 'parkivia_booki_theme_setup9', 9 );
	function parkivia_booki_theme_setup9() {
		
		if (is_admin()) {
			add_filter( 'parkivia_filter_tgmpa_required_plugins',			'parkivia_booki_tgmpa_required_plugins' );
		}
	}
}

// Filter to add in the required plugins list
if ( !function_exists( 'parkivia_booki_tgmpa_required_plugins' ) ) {
	//Handler of the add_filter('parkivia_filter_tgmpa_required_plugins',	'parkivia_booki_tgmpa_required_plugins');
	function parkivia_booki_tgmpa_required_plugins($list=array()) {
		if (parkivia_storage_isset('required_plugins', 'booki')) {
            $path = parkivia_get_file_dir('plugins/booki/booki.zip');
			// Booki plugin
			$list[] = array(
					'name' 		=> parkivia_storage_get_array('required_plugins', 'booki'),
					'slug' 		=> 'booki',
					'version'	=> '7.0',
                    'source'	=> !empty($path) ? $path : 'upload://booki.zip',
					'required' 	=> false
			);
		}
		return $list;
	}
}



// Check if cf7 installed and activated
if ( !function_exists( 'parkivia_exists_booki' ) ) {
	function parkivia_exists_booki() {
		return class_exists('Booki');
	}
}
?>