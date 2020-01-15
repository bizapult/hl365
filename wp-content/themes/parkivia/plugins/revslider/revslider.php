<?php
/* Revolution Slider support functions
------------------------------------------------------------------------------- */

// Theme init priorities:
// 9 - register other filters (for installer, etc.)
if (!function_exists('parkivia_revslider_theme_setup9')) {
	add_action( 'after_setup_theme', 'parkivia_revslider_theme_setup9', 9 );
	function parkivia_revslider_theme_setup9() {

		add_filter( 'parkivia_filter_merge_styles',				'parkivia_revslider_merge_styles' );
		
		if (is_admin()) {
			add_filter( 'parkivia_filter_tgmpa_required_plugins','parkivia_revslider_tgmpa_required_plugins' );
		}
	}
}

// Filter to add in the required plugins list
if ( !function_exists( 'parkivia_revslider_tgmpa_required_plugins' ) ) {
	//Handler of the add_filter('parkivia_filter_tgmpa_required_plugins',	'parkivia_revslider_tgmpa_required_plugins');
	function parkivia_revslider_tgmpa_required_plugins($list=array()) {
		if (parkivia_storage_isset('required_plugins', 'revslider')) {
			$path = parkivia_get_file_dir('plugins/revslider/revslider.zip');
			if (!empty($path) || parkivia_get_theme_setting('tgmpa_upload')) {
				$list[] = array(
					'name' 		=> parkivia_storage_get_array('required_plugins', 'revslider'),
					'slug' 		=> 'revslider',
					'version'	=> '6.1.6',
					'source'	=> !empty($path) ? $path : 'upload://revslider.zip',
					'required' 	=> false
				);
			}
		}
		return $list;
	}
}

// Check if RevSlider installed and activated
if ( !function_exists( 'parkivia_exists_revslider' ) ) {
	function parkivia_exists_revslider() {
		return function_exists('rev_slider_shortcode');
	}
}
	
// Merge custom styles
if ( !function_exists( 'parkivia_revslider_merge_styles' ) ) {
	//Handler of the add_filter('parkivia_filter_merge_styles', 'parkivia_revslider_merge_styles');
	function parkivia_revslider_merge_styles($list) {
		if (parkivia_exists_revslider()) {
			$list[] = 'plugins/revslider/_revslider.scss';
		}
		return $list;
	}
}
?>