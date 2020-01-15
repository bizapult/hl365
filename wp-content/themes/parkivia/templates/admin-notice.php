<?php
/**
 * The template to display Admin notices
 *
 * @package WordPress
 * @subpackage PARKIVIA
 * @since PARKIVIA 1.0.1
 */
 
$parkivia_theme_obj = wp_get_theme();
?>
<div class="parkivia_admin_notice parkivia_welcome_notice update-nag"><?php
	// Theme image
	if ( ($parkivia_theme_img = parkivia_get_file_url('screenshot.jpg')) != '') {
		?><div class="parkivia_notice_image"><img src="<?php echo esc_url($parkivia_theme_img); ?>" alt="<?php esc_html_e('Image', 'parkivia')?>"></div><?php
	}

	// Title
	?><h3 class="parkivia_notice_title"><?php
		// Translators: Add theme name and version to the 'Welcome' message
		echo esc_html(sprintf(__('Welcome to %1$s v.%2$s', 'parkivia'),
				$parkivia_theme_obj->name . (PARKIVIA_THEME_FREE ? ' ' . __('Free', 'parkivia') : ''),
				$parkivia_theme_obj->version
				));
	?></h3><?php

	// Description
	?><div class="parkivia_notice_text"><?php
		echo str_replace('. ', '.<br>', wp_kses_data($parkivia_theme_obj->description));
		if (!parkivia_exists_trx_addons()) {
			echo (!empty($parkivia_theme_obj->description) ? '<br><br>' : '')
					. wp_kses_data(__('Attention! Plugin "ThemeREX Addons" is required! Please, install and activate it!', 'parkivia'));
		}
	?></div><?php

	// Buttons
	?><div class="parkivia_notice_buttons"><?php
		// Link to the page 'About Theme'
		?><a href="<?php echo esc_url(admin_url().'themes.php?page=parkivia_about'); ?>" class="button button-primary"><i class="dashicons dashicons-nametag"></i> <?php
			// Translators: Add theme name
			echo esc_html(sprintf(__('About %s', 'parkivia'), $parkivia_theme_obj->name));
		?></a><?php
		// Link to the page 'Install plugins'
		if (parkivia_get_value_gp('page')!='tgmpa-install-plugins') {
			?>
			<a href="<?php echo esc_url(admin_url().'themes.php?page=tgmpa-install-plugins'); ?>" class="button button-primary"><i class="dashicons dashicons-admin-plugins"></i> <?php esc_html_e('Install plugins', 'parkivia'); ?></a>
			<?php
		}
		// Link to the 'One-click demo import'
		if (function_exists('parkivia_exists_trx_addons') && parkivia_exists_trx_addons() && class_exists('trx_addons_demo_data_importer')) {
			?>
			<a href="<?php echo esc_url(admin_url().'themes.php?page=trx_importer'); ?>" class="button button-primary"><i class="dashicons dashicons-download"></i> <?php esc_html_e('One Click Demo Data', 'parkivia'); ?></a>
			<?php
		}
		// Link to the Customizer
		?><a href="<?php echo esc_url(admin_url().'customize.php'); ?>" class="button"><i class="dashicons dashicons-admin-appearance"></i> <?php esc_html_e('Theme Customizer', 'parkivia'); ?></a><?php
		// Link to the Theme Options
		if (!PARKIVIA_THEME_FREE) {
			?><span> <?php esc_html_e('or', 'parkivia'); ?> </span>
        	<a href="<?php echo esc_url(admin_url().'themes.php?page=theme_options'); ?>" class="button"><i class="dashicons dashicons-admin-appearance"></i> <?php esc_html_e('Theme Options', 'parkivia'); ?></a><?php
        }
        // Dismiss this notice
        ?><a href="#" class="parkivia_hide_notice"><i class="dashicons dashicons-dismiss"></i> <span class="parkivia_hide_notice_text"><?php esc_html_e('Dismiss', 'parkivia'); ?></span></a>
	</div>
</div>