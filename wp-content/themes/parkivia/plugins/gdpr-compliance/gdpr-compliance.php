<?php
/* The GDPR Framework support functions
------------------------------------------------------------------------------- */

// Theme init priorities:
// 9 - register other filters (for installer, etc.)
if ( ! function_exists( 'parkivia_gdpr_compliance_feed_theme_setup9' ) ) {
	add_action( 'after_setup_theme', 'parkivia_gdpr_compliance_theme_setup9', 9 );
	function parkivia_gdpr_compliance_theme_setup9() {
		if ( is_admin() ) {
			add_filter( 'parkivia_filter_tgmpa_required_plugins', 'parkivia_gdpr_compliance_tgmpa_required_plugins' );
		}
	}
}

// Filter to add in the required plugins list
if ( ! function_exists( 'parkivia_gdpr_compliance_tgmpa_required_plugins' ) ) {
	//Handler of the add_filter('parkivia_filter_tgmpa_required_plugins',	'parkivia_gdpr_compliance_tgmpa_required_plugins');
	function parkivia_gdpr_compliance_tgmpa_required_plugins( $list = array() ) {
		if ( parkivia_storage_isset( 'required_plugins', 'gdpr-compliance' ) ) {
			$list[] = array(
				'name'     => parkivia_storage_get_array( 'required_plugins', 'gdpr-compliance' ),
				'slug'     => 'wp-gdpr-compliance',
				'required' => false,
			);
		}
		return $list;
	}
}

// Check if this plugin installed and activated
if ( ! function_exists( 'parkivia_exists_gdpr_compliance' ) ) {
	function parkivia_exists_gdpr_compliance() {
		return defined( 'WP_GDPR_C_SLUG' );
	}
}
