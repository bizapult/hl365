<div class="front_page_section front_page_section_about<?php
			$parkivia_scheme = parkivia_get_theme_option('front_page_about_scheme');
			if (!parkivia_is_inherit($parkivia_scheme)) echo ' scheme_'.esc_attr($parkivia_scheme);
			echo ' front_page_section_paddings_'.esc_attr(parkivia_get_theme_option('front_page_about_paddings'));
		?>"<?php
		$parkivia_css = '';
		$parkivia_bg_image = parkivia_get_theme_option('front_page_about_bg_image');
		if (!empty($parkivia_bg_image)) 
			$parkivia_css .= 'background-image: url('.esc_url(parkivia_get_attachment_url($parkivia_bg_image)).');';
		if (!empty($parkivia_css))
			echo ' style="' . esc_attr($parkivia_css) . '"';
?>><?php
	// Add anchor
	$parkivia_anchor_icon = parkivia_get_theme_option('front_page_about_anchor_icon');	
	$parkivia_anchor_text = parkivia_get_theme_option('front_page_about_anchor_text');	
	if ((!empty($parkivia_anchor_icon) || !empty($parkivia_anchor_text)) && shortcode_exists('trx_sc_anchor')) {
		echo do_shortcode('[trx_sc_anchor id="front_page_section_about"'
										. (!empty($parkivia_anchor_icon) ? ' icon="'.esc_attr($parkivia_anchor_icon).'"' : '')
										. (!empty($parkivia_anchor_text) ? ' title="'.esc_attr($parkivia_anchor_text).'"' : '')
										. ']');
	}
	?>
	<div class="front_page_section_inner front_page_section_about_inner<?php
			if (parkivia_get_theme_option('front_page_about_fullheight'))
				echo ' parkivia-full-height sc_layouts_flex sc_layouts_columns_middle';
			?>"<?php
			$parkivia_css = '';
			$parkivia_bg_mask = parkivia_get_theme_option('front_page_about_bg_mask');
			$parkivia_bg_color = parkivia_get_theme_option('front_page_about_bg_color');
			if (!empty($parkivia_bg_color) && $parkivia_bg_mask > 0)
				$parkivia_css .= 'background-color: '.esc_attr($parkivia_bg_mask==1
																	? $parkivia_bg_color
																	: parkivia_hex2rgba($parkivia_bg_color, $parkivia_bg_mask)
																).';';
			if (!empty($parkivia_css))
				echo ' style="' . esc_attr($parkivia_css) . '"';
	?>>
		<div class="front_page_section_content_wrap front_page_section_about_content_wrap content_wrap">
			<?php
			// Caption
			$parkivia_caption = parkivia_get_theme_option('front_page_about_caption');
			if (!empty($parkivia_caption) || (current_user_can('edit_theme_options') && is_customize_preview())) {
				?><h2 class="front_page_section_caption front_page_section_about_caption front_page_block_<?php echo !empty($parkivia_caption) ? 'filled' : 'empty'; ?>"><?php echo wp_kses($parkivia_caption, 'parkivia_kses_content' ); ?></h2><?php
			}
		
			// Description (text)
			$parkivia_description = parkivia_get_theme_option('front_page_about_description');
			if (!empty($parkivia_description) || (current_user_can('edit_theme_options') && is_customize_preview())) {
				?><div class="front_page_section_description front_page_section_about_description front_page_block_<?php echo !empty($parkivia_description) ? 'filled' : 'empty'; ?>"><?php echo wp_kses(wpautop($parkivia_description), 'parkivia_kses_content' ); ?></div><?php
			}
			
			// Content
			$parkivia_content = parkivia_get_theme_option('front_page_about_content');
			if (!empty($parkivia_content) || (current_user_can('edit_theme_options') && is_customize_preview())) {
				?><div class="front_page_section_content front_page_section_about_content front_page_block_<?php echo !empty($parkivia_content) ? 'filled' : 'empty'; ?>"><?php
					$parkivia_page_content_mask = '%%CONTENT%%';
					if (strpos($parkivia_content, $parkivia_page_content_mask) !== false) {
						$parkivia_content = preg_replace(
									'/(\<p\>\s*)?'.$parkivia_page_content_mask.'(\s*\<\/p\>)/i',
									sprintf('<div class="front_page_section_about_source">%s</div>',
												apply_filters('the_content', get_the_content())),
									$parkivia_content
									);
					}
					parkivia_show_layout($parkivia_content);
				?></div><?php
			}
			?>
		</div>
	</div>
</div>