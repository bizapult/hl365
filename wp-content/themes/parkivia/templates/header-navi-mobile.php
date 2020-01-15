<?php
/**
 * The template to show mobile menu
 *
 * @package WordPress
 * @subpackage PARKIVIA
 * @since PARKIVIA 1.0
 */
?>
<div class="menu_mobile_overlay"></div>
<div class="menu_mobile menu_mobile_<?php echo esc_attr(parkivia_get_theme_option('menu_mobile_fullscreen') > 0 ? 'fullscreen' : 'narrow'); ?> scheme_dark">
	<div class="menu_mobile_inner">
		<a class="menu_mobile_close icon-cancel"></a><?php

		// Logo
		set_query_var('parkivia_logo_args', array('type' => 'mobile'));
		get_template_part( 'templates/header-logo' );
		set_query_var('parkivia_logo_args', array());

		// Mobile menu
		$parkivia_menu_mobile = parkivia_get_nav_menu('menu_mobile');
		if (empty($parkivia_menu_mobile)) {
			$parkivia_menu_mobile = apply_filters('parkivia_filter_get_mobile_menu', '');
			if (empty($parkivia_menu_mobile)) $parkivia_menu_mobile = parkivia_get_nav_menu('menu_main');
			if (empty($parkivia_menu_mobile)) $parkivia_menu_mobile = parkivia_get_nav_menu();
		}
		if (!empty($parkivia_menu_mobile)) {
			if (!empty($parkivia_menu_mobile))
				$parkivia_menu_mobile = str_replace(
					array('menu_main', 'id="menu-', 'sc_layouts_menu_nav', 'sc_layouts_hide_on_mobile', 'hide_on_mobile'),
					array('menu_mobile', 'id="menu_mobile-', '', '', ''),
					$parkivia_menu_mobile
					);
			if (strpos($parkivia_menu_mobile, '<nav ')===false)
				$parkivia_menu_mobile = sprintf('<nav class="menu_mobile_nav_area">%s</nav>', $parkivia_menu_mobile);
			parkivia_show_layout(apply_filters('parkivia_filter_menu_mobile_layout', $parkivia_menu_mobile));
		}

		// Search field
		do_action('parkivia_action_search', 'normal', 'search_mobile', false);
		
		// Social icons
		parkivia_show_layout(parkivia_get_socials_links(), '<div class="socials_mobile">', '</div>');
		?>
	</div>
</div>
