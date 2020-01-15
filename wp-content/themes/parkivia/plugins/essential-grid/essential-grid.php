<?php
/* Essential Grid support functions
------------------------------------------------------------------------------- */


// Theme init priorities:
// 9 - register other filters (for installer, etc.)
if (!function_exists('parkivia_essential_grid_theme_setup9')) {
	add_action( 'after_setup_theme', 'parkivia_essential_grid_theme_setup9', 9 );
	function parkivia_essential_grid_theme_setup9() {
		
		add_filter( 'parkivia_filter_merge_styles',						'parkivia_essential_grid_merge_styles' );

		if (is_admin()) {
			add_filter( 'parkivia_filter_tgmpa_required_plugins',		'parkivia_essential_grid_tgmpa_required_plugins' );
		}
	}
}

// Filter to add in the required plugins list
if ( !function_exists( 'parkivia_essential_grid_tgmpa_required_plugins' ) ) {
	//Handler of the add_filter('parkivia_filter_tgmpa_required_plugins',	'parkivia_essential_grid_tgmpa_required_plugins');
	function parkivia_essential_grid_tgmpa_required_plugins($list=array()) {
		if (parkivia_storage_isset('required_plugins', 'essential-grid')) {
			$path = parkivia_get_file_dir('plugins/essential-grid/essential-grid.zip');
			if (!empty($path) || parkivia_get_theme_setting('tgmpa_upload')) {
				$list[] = array(
						'name' 		=> parkivia_storage_get_array('required_plugins', 'essential-grid'),
						'slug' 		=> 'essential-grid',
						'version'	=> '2.3.6',
						'source'	=> !empty($path) ? $path : 'upload://essential-grid.zip',
						'required' 	=> false
				);
			}
		}
		return $list;
	}
}

// Check if plugin installed and activated
if ( !function_exists( 'parkivia_exists_essential_grid' ) ) {
	function parkivia_exists_essential_grid() {
		return defined('EG_PLUGIN_PATH');
	}
}
	
// Merge custom styles
if ( !function_exists( 'parkivia_essential_grid_merge_styles' ) ) {
	//Handler of the add_filter('parkivia_filter_merge_styles', 'parkivia_essential_grid_merge_styles');
	function parkivia_essential_grid_merge_styles($list) {
		if (parkivia_exists_essential_grid()) {
			$list[] = 'plugins/essential-grid/_essential-grid.scss';
		}
		return $list;
	}
}
?>