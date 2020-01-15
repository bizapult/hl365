<div class="front_page_section front_page_section_contacts<?php
			$parkivia_scheme = parkivia_get_theme_option('front_page_contacts_scheme');
			if (!parkivia_is_inherit($parkivia_scheme)) echo ' scheme_'.esc_attr($parkivia_scheme);
			echo ' front_page_section_paddings_'.esc_attr(parkivia_get_theme_option('front_page_contacts_paddings'));
		?>"<?php
		$parkivia_css = '';
		$parkivia_bg_image = parkivia_get_theme_option('front_page_contacts_bg_image');
		if (!empty($parkivia_bg_image)) 
			$parkivia_css .= 'background-image: url('.esc_url(parkivia_get_attachment_url($parkivia_bg_image)).');';
		if (!empty($parkivia_css))
			echo ' style="' . esc_attr($parkivia_css) . '"';
?>><?php
	// Add anchor
	$parkivia_anchor_icon = parkivia_get_theme_option('front_page_contacts_anchor_icon');	
	$parkivia_anchor_text = parkivia_get_theme_option('front_page_contacts_anchor_text');	
	if ((!empty($parkivia_anchor_icon) || !empty($parkivia_anchor_text)) && shortcode_exists('trx_sc_anchor')) {
		echo do_shortcode('[trx_sc_anchor id="front_page_section_contacts"'
										. (!empty($parkivia_anchor_icon) ? ' icon="'.esc_attr($parkivia_anchor_icon).'"' : '')
										. (!empty($parkivia_anchor_text) ? ' title="'.esc_attr($parkivia_anchor_text).'"' : '')
										. ']');
	}
	?>
	<div class="front_page_section_inner front_page_section_contacts_inner<?php
			if (parkivia_get_theme_option('front_page_contacts_fullheight'))
				echo ' parkivia-full-height sc_layouts_flex sc_layouts_columns_middle';
			?>"<?php
			$parkivia_css = '';
			$parkivia_bg_mask = parkivia_get_theme_option('front_page_contacts_bg_mask');
			$parkivia_bg_color = parkivia_get_theme_option('front_page_contacts_bg_color');
			if (!empty($parkivia_bg_color) && $parkivia_bg_mask > 0)
				$parkivia_css .= 'background-color: '.esc_attr($parkivia_bg_mask==1
																	? $parkivia_bg_color
																	: parkivia_hex2rgba($parkivia_bg_color, $parkivia_bg_mask)
																).';';
			if (!empty($parkivia_css))
				echo ' style="' . esc_attr($parkivia_css) . '"';
	?>>
		<div class="front_page_section_content_wrap front_page_section_contacts_content_wrap content_wrap">
			<?php

			// Title and description
			$parkivia_caption = parkivia_get_theme_option('front_page_contacts_caption');
			$parkivia_description = parkivia_get_theme_option('front_page_contacts_description');
			if (!empty($parkivia_caption) || !empty($parkivia_description) || (current_user_can('edit_theme_options') && is_customize_preview())) {
				// Caption
				if (!empty($parkivia_caption) || (current_user_can('edit_theme_options') && is_customize_preview())) {
					?><h2 class="front_page_section_caption front_page_section_contacts_caption front_page_block_<?php echo !empty($parkivia_caption) ? 'filled' : 'empty'; ?>"><?php
						echo wp_kses($parkivia_caption, 'parkivia_kses_content' );
					?></h2><?php
				}
			
				// Description
				if (!empty($parkivia_description) || (current_user_can('edit_theme_options') && is_customize_preview())) {
					?><div class="front_page_section_description front_page_section_contacts_description front_page_block_<?php echo !empty($parkivia_description) ? 'filled' : 'empty'; ?>"><?php
						echo wp_kses(wpautop($parkivia_description), 'parkivia_kses_content');
					?></div><?php
				}
			}

			// Content (text)
			$parkivia_content = parkivia_get_theme_option('front_page_contacts_content');
			$parkivia_layout = parkivia_get_theme_option('front_page_contacts_layout');
			if ($parkivia_layout == 'columns' && (!empty($parkivia_content) || (current_user_can('edit_theme_options') && is_customize_preview()))) {
				?><div class="front_page_section_columns front_page_section_contacts_columns columns_wrap">
					<div class="column-1_3">
				<?php
			}

			if ((!empty($parkivia_content) || (current_user_can('edit_theme_options') && is_customize_preview()))) {
				?><div class="front_page_section_content front_page_section_contacts_content front_page_block_<?php echo !empty($parkivia_content) ? 'filled' : 'empty'; ?>"><?php
					echo wp_kses($parkivia_content, 'parkivia_kses_content' );
				?></div><?php
			}

			if ($parkivia_layout == 'columns' && (!empty($parkivia_content) || (current_user_can('edit_theme_options') && is_customize_preview()))) {
				?></div><div class="column-2_3"><?php
			}
		
			// Shortcode output
			$parkivia_sc = parkivia_get_theme_option('front_page_contacts_shortcode');
			if (!empty($parkivia_sc) || (current_user_can('edit_theme_options') && is_customize_preview())) {
				?><div class="front_page_section_output front_page_section_contacts_output front_page_block_<?php echo !empty($parkivia_sc) ? 'filled' : 'empty'; ?>"><?php
					parkivia_show_layout(do_shortcode($parkivia_sc));
				?></div><?php
			}

			if ($parkivia_layout == 'columns' && (!empty($parkivia_content) || (current_user_can('edit_theme_options') && is_customize_preview()))) {
				?></div></div><?php
			}
			?>			
		</div>
	</div>
</div>