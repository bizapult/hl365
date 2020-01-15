<?php
/* TRX Updater support functions
------------------------------------------------------------------------------- */

// Theme init priorities:
// 9 - register other filters (for installer, etc.)
if (!function_exists('parkivia_trx_updater_theme_setup9')) {
	add_action( 'after_setup_theme', 'parkivia_trx_updater_theme_setup9', 9 );
	function parkivia_trx_updater_theme_setup9() {

		if (is_admin()) {
			add_filter( 'parkivia_filter_tgmpa_required_plugins',			'parkivia_trx_updater_tgmpa_required_plugins' );
		}
	}
}

// Filter to add in the required plugins list
if ( !function_exists( 'parkivia_trx_updater_tgmpa_required_plugins' ) ) {
	//Handler of the add_filter('parkivia_filter_tgmpa_required_plugins',	'parkivia_trx_updater_tgmpa_required_plugins');
	function parkivia_trx_updater_tgmpa_required_plugins($list=array()) {
		if (parkivia_storage_isset('required_plugins', 'trx_updater')) {
			$path = parkivia_get_file_dir('plugins/trx_updater/trx_updater.zip');
			// TRX Updater plugin
			$list[] = array(
				'name' 		=> parkivia_storage_get_array('required_plugins', 'trx_updater'),
				'slug' 		=> 'trx_updater',
				'version'	=> '1.3.3',
				'source'	=> !empty($path) ? $path : 'upload://trx_updater.zip',
				'required' 	=> false
			);
		}
		return $list;
	}
}

// Check if this plugin installed and activated
if ( !function_exists( 'parkivia_exists_trx_updater' ) ) {
	function parkivia_exists_trx_updater() {
		return function_exists( 'trx_updater_load_plugin_textdomain' );
	}
}
