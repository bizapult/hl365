<?php
/**
 * The template for homepage posts with "Portfolio" style
 *
 * @package WordPress
 * @subpackage PARKIVIA
 * @since PARKIVIA 1.0
 */

parkivia_storage_set('blog_archive', true);

get_header(); 

if (have_posts()) {

	parkivia_show_layout(get_query_var('blog_archive_start'));

	$parkivia_stickies = is_home() ? get_option( 'sticky_posts' ) : false;
	$parkivia_sticky_out = parkivia_get_theme_option('sticky_style')=='columns' 
							&& is_array($parkivia_stickies) && count($parkivia_stickies) > 0 && get_query_var( 'paged' ) < 1;
	
	// Show filters
	$parkivia_cat = parkivia_get_theme_option('parent_cat');
	$parkivia_post_type = parkivia_get_theme_option('post_type');
	$parkivia_taxonomy = parkivia_get_post_type_taxonomy($parkivia_post_type);
	$parkivia_show_filters = parkivia_get_theme_option('show_filters');
	$parkivia_tabs = array();
	if (!parkivia_is_off($parkivia_show_filters)) {
		$parkivia_args = array(
			'type'			=> $parkivia_post_type,
			'child_of'		=> $parkivia_cat,
			'orderby'		=> 'name',
			'order'			=> 'ASC',
			'hide_empty'	=> 1,
			'hierarchical'	=> 0,
			'taxonomy'		=> $parkivia_taxonomy,
			'pad_counts'	=> false
		);
		$parkivia_portfolio_list = get_terms($parkivia_args);
		if (is_array($parkivia_portfolio_list) && count($parkivia_portfolio_list) > 0) {
			$parkivia_tabs[$parkivia_cat] = esc_html__('All', 'parkivia');
			foreach ($parkivia_portfolio_list as $parkivia_term) {
				if (isset($parkivia_term->term_id)) $parkivia_tabs[$parkivia_term->term_id] = $parkivia_term->name;
			}
		}
	}
	if (count($parkivia_tabs) > 0) {
		$parkivia_portfolio_filters_ajax = true;
		$parkivia_portfolio_filters_active = $parkivia_cat;
		$parkivia_portfolio_filters_id = 'portfolio_filters';
		?>
		<div class="portfolio_filters parkivia_tabs parkivia_tabs_ajax">
			<ul class="portfolio_titles parkivia_tabs_titles">
				<?php
				foreach ($parkivia_tabs as $parkivia_id=>$parkivia_title) {
					?><li><a href="<?php echo esc_url(parkivia_get_hash_link(sprintf('#%s_%s_content', $parkivia_portfolio_filters_id, $parkivia_id))); ?>" data-tab="<?php echo esc_attr($parkivia_id); ?>"><?php echo esc_html($parkivia_title); ?></a></li><?php
				}
				?>
			</ul>
			<?php
			$parkivia_ppp = parkivia_get_theme_option('posts_per_page');
			if (parkivia_is_inherit($parkivia_ppp)) $parkivia_ppp = '';
			foreach ($parkivia_tabs as $parkivia_id=>$parkivia_title) {
				$parkivia_portfolio_need_content = $parkivia_id==$parkivia_portfolio_filters_active || !$parkivia_portfolio_filters_ajax;
				?>
				<div id="<?php echo esc_attr(sprintf('%s_%s_content', $parkivia_portfolio_filters_id, $parkivia_id)); ?>"
					class="portfolio_content parkivia_tabs_content"
					data-blog-template="<?php echo esc_attr(parkivia_storage_get('blog_template')); ?>"
					data-blog-style="<?php echo esc_attr(parkivia_get_theme_option('blog_style')); ?>"
					data-posts-per-page="<?php echo esc_attr($parkivia_ppp); ?>"
					data-post-type="<?php echo esc_attr($parkivia_post_type); ?>"
					data-taxonomy="<?php echo esc_attr($parkivia_taxonomy); ?>"
					data-cat="<?php echo esc_attr($parkivia_id); ?>"
					data-parent-cat="<?php echo esc_attr($parkivia_cat); ?>"
					data-need-content="<?php echo (false===$parkivia_portfolio_need_content ? 'true' : 'false'); ?>"
				>
					<?php
					if ($parkivia_portfolio_need_content) 
						parkivia_show_portfolio_posts(array(
							'cat' => $parkivia_id,
							'parent_cat' => $parkivia_cat,
							'taxonomy' => $parkivia_taxonomy,
							'post_type' => $parkivia_post_type,
							'page' => 1,
							'sticky' => $parkivia_sticky_out
							)
						);
					?>
				</div>
				<?php
			}
			?>
		</div>
		<?php
	} else {
		parkivia_show_portfolio_posts(array(
			'cat' => $parkivia_cat,
			'parent_cat' => $parkivia_cat,
			'taxonomy' => $parkivia_taxonomy,
			'post_type' => $parkivia_post_type,
			'page' => 1,
			'sticky' => $parkivia_sticky_out
			)
		);
	}

	parkivia_show_layout(get_query_var('blog_archive_end'));

} else {

	if ( is_search() )
		get_template_part( 'content', 'none-search' );
	else
		get_template_part( 'content', 'none-archive' );

}

get_footer();
?>