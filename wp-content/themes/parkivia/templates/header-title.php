<?php
/**
 * The template to display the page title and breadcrumbs
 *
 * @package WordPress
 * @subpackage PARKIVIA
 * @since PARKIVIA 1.0
 */

// Page (category, tag, archive, author) title

if ( parkivia_need_page_title() ) {
	parkivia_sc_layouts_showed('title', true);
	parkivia_sc_layouts_showed('postmeta', true);
	?>
	<div class="top_panel_title sc_layouts_row sc_layouts_row_type_normal">
		<div class="content_wrap">
			<div class="sc_layouts_column sc_layouts_column_align_center">
				<div class="sc_layouts_item">
					<div class="sc_layouts_title sc_align_center">
						<?php
						// Post meta on the single post
						if ( is_single() )  {
							?><div class="sc_layouts_title_meta"><?php
								parkivia_show_post_meta(apply_filters('parkivia_filter_post_meta_args', array(
									'components' => parkivia_array_get_keys_by_value(parkivia_get_theme_option('meta_parts')),
									'counters' => parkivia_array_get_keys_by_value(parkivia_get_theme_option('counters')),
									'seo' => parkivia_is_on(parkivia_get_theme_option('seo_snippets'))
									), 'header', 1)
								);
							?></div><?php
						}
						
						// Blog/Post title
						?><div class="sc_layouts_title_title"><?php
							$parkivia_blog_title = parkivia_get_blog_title();
							$parkivia_blog_title_text = $parkivia_blog_title_class = $parkivia_blog_title_link = $parkivia_blog_title_link_text = '';
							if (is_array($parkivia_blog_title)) {
								$parkivia_blog_title_text = $parkivia_blog_title['text'];
								$parkivia_blog_title_class = !empty($parkivia_blog_title['class']) ? ' '.$parkivia_blog_title['class'] : '';
								$parkivia_blog_title_link = !empty($parkivia_blog_title['link']) ? $parkivia_blog_title['link'] : '';
								$parkivia_blog_title_link_text = !empty($parkivia_blog_title['link_text']) ? $parkivia_blog_title['link_text'] : '';
							} else
								$parkivia_blog_title_text = $parkivia_blog_title;
							?>
							<h1 itemprop="headline" class="sc_layouts_title_caption<?php echo esc_attr($parkivia_blog_title_class); ?>"><?php
								$parkivia_top_icon = parkivia_get_category_icon();
								if (!empty($parkivia_top_icon)) {
									$parkivia_attr = parkivia_getimagesize($parkivia_top_icon);
									?><img src="<?php echo esc_url($parkivia_top_icon); ?>" alt="<?php esc_html_e('Image', 'parkivia')?>" <?php if (!empty($parkivia_attr[3])) parkivia_show_layout($parkivia_attr[3]);?>><?php
								}
								echo wp_kses_post ($parkivia_blog_title_text );
							?></h1>
							<?php
							if (!empty($parkivia_blog_title_link) && !empty($parkivia_blog_title_link_text)) {
								?><a href="<?php echo esc_url($parkivia_blog_title_link); ?>" class="theme_button theme_button_small sc_layouts_title_link"><?php echo esc_html($parkivia_blog_title_link_text); ?></a><?php
							}
							
							// Category/Tag description
							if ( is_category() || is_tag() || is_tax() ) 
								the_archive_description( '<div class="sc_layouts_title_description">', '</div>' );
		
						?></div><?php
	
						// Breadcrumbs
						?><div class="sc_layouts_title_breadcrumbs"><?php
							do_action( 'parkivia_action_breadcrumbs');
						?></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}
?>