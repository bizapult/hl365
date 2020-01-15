<?php
/* caldera-forms support functions
------------------------------------------------------------------------------- */

// Theme init priorities:
// 9 - register other filters (for installer, etc.)
if (!function_exists('parkivia_calform_theme_setup9')) {
	add_action( 'after_setup_theme', 'parkivia_calform_theme_setup9', 9 );
	function parkivia_calform_theme_setup9() {
		
		if (is_admin()) {
			add_filter( 'parkivia_filter_tgmpa_required_plugins',			'parkivia_calform_tgmpa_required_plugins' );
		}
	}
}

// Filter to add in the required plugins list
if ( !function_exists( 'parkivia_calform_tgmpa_required_plugins' ) ) {
	//Handler of the add_filter('parkivia_filter_tgmpa_required_plugins',	'parkivia_calform_tgmpa_required_plugins');
	function parkivia_calform_tgmpa_required_plugins($list=array()) {
		if (parkivia_storage_isset('required_plugins', 'caldera-forms')) {
			// CF plugin
			$list[] = array(
					'name' 		=> parkivia_storage_get_array('required_plugins', 'caldera-forms'),
					'slug' 		=> 'caldera-forms',
					'required' 	=> false
			);
		}
		return $list;
	}
}



// Check if cf7 installed and activated
if ( !function_exists( 'parkivia_exists_calform' ) ) {
	function parkivia_exists_calform() {
		return class_exists('Caldera_Forms_Widget');
	}
}
?>