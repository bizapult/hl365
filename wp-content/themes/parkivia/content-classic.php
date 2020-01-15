<?php
/**
 * The Classic template to display the content
 *
 * Used for index/archive/search.
 *
 * @package WordPress
 * @subpackage PARKIVIA
 * @since PARKIVIA 1.0
 */

$parkivia_blog_style = explode('_', parkivia_get_theme_option('blog_style'));
$parkivia_columns = empty($parkivia_blog_style[1]) ? 2 : max(2, $parkivia_blog_style[1]);
$parkivia_expanded = !parkivia_sidebar_present() && parkivia_is_on(parkivia_get_theme_option('expand_content'));
$parkivia_post_format = get_post_format();
$parkivia_post_format = empty($parkivia_post_format) ? 'standard' : str_replace('post-format-', '', $parkivia_post_format);
$parkivia_animation = parkivia_get_theme_option('blog_animation');
$parkivia_components = parkivia_array_get_keys_by_value(parkivia_get_theme_option('meta_parts'));
$parkivia_counters = parkivia_array_get_keys_by_value(parkivia_get_theme_option('counters'));

?><div class="<?php echo 'classic' == $parkivia_blog_style[0] ? 'column' : 'masonry_item masonry_item'; ?>-1_<?php echo esc_attr($parkivia_columns); ?>"><article id="post-<?php the_ID(); ?>" 
	<?php post_class( 'post_item post_format_'.esc_attr($parkivia_post_format)
					. ' post_layout_classic post_layout_classic_'.esc_attr($parkivia_columns)
					. ' post_layout_'.esc_attr($parkivia_blog_style[0]) 
					. ' post_layout_'.esc_attr($parkivia_blog_style[0]).'_'.esc_attr($parkivia_columns)
					); ?>
	<?php echo (!parkivia_is_off($parkivia_animation) ? ' data-animation="'.esc_attr(parkivia_get_animation_classes($parkivia_animation)).'"' : ''); ?>>
	<?php

	// Sticky label
	if ( is_sticky() && !is_paged() ) {
		?><span class="post_label label_sticky"></span><?php
	}

	// Featured image
	parkivia_show_post_featured( array( 'thumb_size' => parkivia_get_thumb_size($parkivia_blog_style[0] == 'classic'
													? (strpos(parkivia_get_theme_option('body_style'), 'full')!==false 
															? ( $parkivia_columns > 2 ? 'big' : 'huge' )
															: (	$parkivia_columns > 2
																? ($parkivia_expanded ? 'med' : 'small')
																: ($parkivia_expanded ? 'big' : 'med')
																)
														)
													: (strpos(parkivia_get_theme_option('body_style'), 'full')!==false 
															? ( $parkivia_columns > 2 ? 'masonry-big' : 'full' )
															: (	$parkivia_columns <= 2 && $parkivia_expanded ? 'masonry-big' : 'masonry')
														)
								) ) );

	if ( !in_array($parkivia_post_format, array('link', 'aside', 'status', 'quote')) ) {
		?>
		<div class="post_header entry-header">
			<?php 
			do_action('parkivia_action_before_post_title'); 

			// Post title
			the_title( sprintf( '<h4 class="post_title entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h4>' );

			do_action('parkivia_action_before_post_meta'); 

			// Post meta
			if (!empty($parkivia_components))
				parkivia_show_post_meta(apply_filters('parkivia_filter_post_meta_args', array(
					'components' => $parkivia_components,
					'counters' => $parkivia_counters,
					'seo' => false
					), $parkivia_blog_style[0], $parkivia_columns)
				);

			do_action('parkivia_action_after_post_meta'); 
			?>
		</div><!-- .entry-header -->
		<?php
	}		
	?>

	<div class="post_content entry-content">
		<div class="post_content_inner">
			<?php
			$parkivia_show_learn_more = false;
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
			?>
		</div>
		<?php
		// Post meta
		if (in_array($parkivia_post_format, array('link', 'aside', 'status', 'quote'))) {
			if (!empty($parkivia_components))
				parkivia_show_post_meta(apply_filters('parkivia_filter_post_meta_args', array(
					'components' => $parkivia_components,
					'counters' => $parkivia_counters
					), $parkivia_blog_style[0], $parkivia_columns)
				);
		}
		// More button
		if ( $parkivia_show_learn_more ) {
			?><p><a class="more-link" href="<?php echo esc_url(get_permalink()); ?>"><?php esc_html_e('Read more', 'parkivia'); ?></a></p><?php
		}
		?>
	</div><!-- .entry-content -->

</article></div>