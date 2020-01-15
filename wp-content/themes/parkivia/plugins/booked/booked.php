<?php
/* Booked Appointments support functions
------------------------------------------------------------------------------- */

// Theme init priorities:
// 9 - register other filters (for installer, etc.)
if (!function_exists('parkivia_booked_theme_setup9')) {
	add_action( 'after_setup_theme', 'parkivia_booked_theme_setup9', 9 );
	function parkivia_booked_theme_setup9() {
		add_filter( 'parkivia_filter_merge_styles', 						'parkivia_booked_merge_styles' );
		if (is_admin()) {
			add_filter( 'parkivia_filter_tgmpa_required_plugins',		'parkivia_booked_tgmpa_required_plugins' );
		}
	}
}

// Filter to add in the required plugins list
if ( !function_exists( 'parkivia_booked_tgmpa_required_plugins' ) ) {
	//Handler of the add_filter('parkivia_filter_tgmpa_required_plugins',	'parkivia_booked_tgmpa_required_plugins');
	function parkivia_booked_tgmpa_required_plugins($list=array()) {
		if (parkivia_storage_isset('required_plugins', 'booked')) {
			$path = parkivia_get_file_dir('plugins/booked/booked.zip');
			if (!empty($path) || parkivia_get_theme_setting('tgmpa_upload')) {
				$list[] = array(
					'name' 		=> parkivia_storage_get_array('required_plugins', 'booked'),
					'slug' 		=> 'booked',
					'version'	=> '2.2.5',
					'source' 	=> !empty($path) ? $path : 'upload://booked.zip',
					'required' 	=> false
				);
			}
		}
		return $list;
	}
}

// Check if plugin installed and activated
if ( !function_exists( 'parkivia_exists_booked' ) ) {
	function parkivia_exists_booked() {
		return class_exists('booked_plugin');
	}
}
	
// Merge custom styles
if ( !function_exists( 'parkivia_booked_merge_styles' ) ) {
	//Handler of the add_filter('parkivia_filter_merge_styles', 'parkivia_booked_merge_styles');
	function parkivia_booked_merge_styles($list) {
		if (parkivia_exists_booked()) {
			$list[] = 'plugins/booked/_booked.scss';
		}
		return $list;
	}
}


// Add plugin-specific colors and fonts to the custom CSS
if (parkivia_exists_booked()) { require_once PARKIVIA_THEME_DIR . 'plugins/booked/booked-styles.php'; }
?>