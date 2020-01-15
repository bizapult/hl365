<?php
/* Calculate Fields Form support functions
------------------------------------------------------------------------------- */

// Theme init priorities:
// 9 - register other filters (for installer, etc.)
if (!function_exists('parkivia_calculated_fields_form_theme_setup9')) {
	add_action( 'after_setup_theme', 'parkivia_calculated_fields_form_theme_setup9', 9 );
	function parkivia_calculated_fields_form_theme_setup9() {

		add_filter( 'parkivia_filter_merge_styles',						'parkivia_calculated_fields_form_merge_styles' );
		
		if (parkivia_exists_calculated_fields_form()) {
			add_action( 'wp_enqueue_scripts', 							'parkivia_calculated_fields_form_frontend_scripts', 1100 );
		}
		if (is_admin()) {
			add_filter( 'parkivia_filter_tgmpa_required_plugins',		'parkivia_calculated_fields_form_tgmpa_required_plugins' );
		}
	}
}

// Filter to add in the required plugins list
if ( !function_exists( 'parkivia_calculated_fields_form_tgmpa_required_plugins' ) ) {
	//Handler of the add_filter('parkivia_filter_tgmpa_required_plugins',	'parkivia_calculated_fields_form_tgmpa_required_plugins');
	function parkivia_calculated_fields_form_tgmpa_required_plugins($list=array()) {
		if (parkivia_storage_isset('required_plugins', 'calculated-fields-form')) {
			$list[] = array(
					'name' 		=> parkivia_storage_get_array('required_plugins', 'calculated-fields-form'),
					'slug' 		=> 'calculated-fields-form',
					'required' 	=> false
			);
		}
		return $list;
	}
}

// Check if plugin installed and activated
if ( !function_exists( 'parkivia_exists_calculated_fields_form' ) ) {
	function parkivia_exists_calculated_fields_form() {
		return defined( 'CP_CALCULATEDFIELDSF_VERSION' );
	}
}
	
// Enqueue plugin's custom styles
if ( !function_exists( 'parkivia_calculated_fields_form_frontend_scripts' ) ) {
	//Handler of the add_action( 'wp_enqueue_scripts', 'parkivia_calculated_fields_form_frontend_scripts', 1100 );
	function parkivia_calculated_fields_form_frontend_scripts() {
		// Remove jquery_ui from frontend
		if (parkivia_get_theme_setting('disable_jquery_ui')) {
			global $wp_styles;
			$wp_styles->done[] = 'cpcff_jquery_ui';
		}
	}
}
	
// Merge custom styles
if ( !function_exists( 'parkivia_calculated_fields_form_merge_styles' ) ) {
	//Handler of the add_filter('parkivia_filter_merge_styles', 'parkivia_calculated_fields_form_merge_styles');
	function parkivia_calculated_fields_form_merge_styles($list) {
		if (parkivia_exists_calculated_fields_form()) {
			$list[] = 'plugins/calculated-fields-form/_calculated-fields-form.scss';
		}
		return $list;
	}
}

?>