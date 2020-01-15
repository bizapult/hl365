<?php
/* WPBakery Page Builder Extensions Bundle support functions
------------------------------------------------------------------------------- */

// Theme init priorities:
// 9 - register other filters (for installer, etc.)
if (!function_exists('parkivia_vc_extensions_theme_setup9')) {
	add_action( 'after_setup_theme', 'parkivia_vc_extensions_theme_setup9', 9 );
	function parkivia_vc_extensions_theme_setup9() {

		add_filter( 'parkivia_filter_merge_styles',						'parkivia_vc_extensions_merge_styles' );
	
		if (is_admin()) {
			add_filter( 'parkivia_filter_tgmpa_required_plugins',		'parkivia_vc_extensions_tgmpa_required_plugins' );
		}
	}
}

// Filter to add in the required plugins list
if ( !function_exists( 'parkivia_vc_extensions_tgmpa_required_plugins' ) ) {
	//Handler of the add_filter('parkivia_filter_tgmpa_required_plugins',	'parkivia_vc_extensions_tgmpa_required_plugins');
	function parkivia_vc_extensions_tgmpa_required_plugins($list=array()) {
		if (parkivia_storage_isset('required_plugins', 'vc-extensions-bundle')) {
			$path = parkivia_get_file_dir('plugins/vc-extensions-bundle/vc-extensions-bundle.zip');
			if (!empty($path) || parkivia_get_theme_setting('tgmpa_upload')) {
				$list[] = array(
					'name' 		=> parkivia_storage_get_array('required_plugins', 'vc-extensions-bundle'),
					'slug' 		=> 'vc-extensions-bundle',
					'version'	=> '3.5.4',
					'source'	=> !empty($path) ? $path : 'upload://vc-extensions-bundle.zip',
					'required' 	=> false
				);
			}
		}
		return $list;
	}
}

// Check if VC Extensions installed and activated
if ( !function_exists( 'parkivia_exists_vc_extensions' ) ) {
	function parkivia_exists_vc_extensions() {
		return class_exists('Vc_Manager') && class_exists('VC_Extensions_CQBundle');
	}
}
	
// Merge custom styles
if ( !function_exists( 'parkivia_vc_extensions_merge_styles' ) ) {
	//Handler of the add_filter('parkivia_filter_merge_styles', 'parkivia_vc_extensions_merge_styles');
	function parkivia_vc_extensions_merge_styles($list) {
		if (parkivia_exists_visual_composer()) {
			$list[] = 'plugins/vc-extensions-bundle/_vc-extensions-bundle.scss';
		}
		return $list;
	}
}
?>