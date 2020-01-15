<?php
/**
 * The default template to display the content
 *
 * Used for index/archive/search.
 *
 * @package WordPress
 * @subpackage PARKIVIA
 * @since PARKIVIA 1.0
 */

$parkivia_post_format = get_post_format();
$parkivia_post_format = empty($parkivia_post_format) ? 'standard' : str_replace('post-format-', '', $parkivia_post_format);
$parkivia_animation = parkivia_get_theme_option('blog_animation');

?><article id="post-<?php the_ID(); ?>" 
	<?php post_class( 'post_item post_layout_excerpt post_format_'.esc_attr($parkivia_post_format) ); ?>
	<?php echo (!parkivia_is_off($parkivia_animation) ? ' data-animation="'.esc_attr(parkivia_get_animation_classes($parkivia_animation)).'"' : ''); ?>
	><?php

	// Featured image
	parkivia_show_post_featured(array( 'thumb_size' => parkivia_get_thumb_size( strpos(parkivia_get_theme_option('body_style'), 'full')!==false ? 'full' : 'big' ) ));

	?><div class="post-body"><?php

	// Title and post meta
	if (get_the_title() != '') {
		?>
		<div class="post_header entry-header">
			<?php
			do_action('parkivia_action_before_post_title'); 

			// Post title
			the_title( sprintf( '<h2 class="post_title entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );

			?>
		</div><!-- .post_header --><?php
	}
	
	// Post content
	?><div class="post_content entry-content"><?php
		if (parkivia_get_theme_option('blog_content') == 'fullpost') {
			// Post content area
			?><div class="post_content_inner"><?php
				the_content( '' );
			?></div><?php
			// Inner pages
			wp_link_pages( array(
				'before'      => '<div class="page_links"><span class="page_links_title">' . esc_html__( 'Pages:', 'parkivia' ) . '</span>',
				'after'       => '</div>',
				'link_before' => '<span>',
				'link_after'  => '</span>',
				'pagelink'    => '<span class="screen-reader-text">' . esc_html__( 'Page', 'parkivia' ) . ' </span>%',
				'separator'   => '<span class="screen-reader-text">, </span>',
			) );

		} else {

			$parkivia_show_learn_more = !in_array($parkivia_post_format, array('link', 'aside', 'status', 'quote'));

			// Post content area
			?><div class="post_content_inner"><?php
				if (has_excerpt()) {
					the_excerpt();
				} else if (strpos(get_the_content('!--more'), '!--more')!==false) {
					the_content( '' );
				} else if (in_array($parkivia_post_format, array('link', 'aside', 'status'))) {
					the_content();
				} else if ($parkivia_post_format == 'quote') {
					if (($quote = parkivia_get_tag(get_the_content(), '<blockquote>', '</blockquote>'))!='')
						parkivia_show_layout(wpautop($quote));
					else
						the_excerpt();
				} else if (substr(get_the_content(), 0, 4)!='[vc_') {
					the_excerpt();
				}
			?></div><div class="post-bottom"><?php
			// More button
			if ( $parkivia_show_learn_more ) {
				?><a href="<?php echo esc_url(get_permalink()); ?>" class="sc_button color_style_default sc_button_default sc_button_size_small sc_button_with_icon sc_button_icon_left sc_button_hover_slide_left"><span class="sc_button_icon"><span class="icon-double-right"></span></span><span class="sc_button_text"><span class="sc_button_title"><?php esc_html_e('Read more', 'parkivia'); ?></span></span><!-- /.sc_button_text --></a><?php
			}
            do_action('parkivia_action_before_post_meta');

            // Post meta
            $parkivia_components = parkivia_array_get_keys_by_value(parkivia_get_theme_option('meta_parts'));
            $parkivia_counters = parkivia_array_get_keys_by_value(parkivia_get_theme_option('counters'));

            if (!empty($parkivia_components))
                parkivia_show_post_meta(apply_filters('parkivia_filter_post_meta_args', array(
                        'components' => $parkivia_components,
                        'counters' => $parkivia_counters,
                        'seo' => false
                    ), 'excerpt', 1)
                );

		}
            ?></div></div></div><!-- .entry-content -->
</article>