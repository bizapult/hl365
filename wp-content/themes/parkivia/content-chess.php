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
$parkivia_columns = empty($parkivia_blog_style[1]) ? 1 : max(1, $parkivia_blog_style[1]);
$parkivia_expanded = !parkivia_sidebar_present() && parkivia_is_on(parkivia_get_theme_option('expand_content'));
$parkivia_post_format = get_post_format();
$parkivia_post_format = empty($parkivia_post_format) ? 'standard' : str_replace('post-format-', '', $parkivia_post_format);
$parkivia_animation = parkivia_get_theme_option('blog_animation');

?><article id="post-<?php the_ID(); ?>" 
	<?php post_class( 'post_item post_layout_chess post_layout_chess_'.esc_attr($parkivia_columns).' post_format_'.esc_attr($parkivia_post_format) ); ?>
	<?php echo (!parkivia_is_off($parkivia_animation) ? ' data-animation="'.esc_attr(parkivia_get_animation_classes($parkivia_animation)).'"' : ''); ?>>

	<?php
	// Add anchor
	if ($parkivia_columns == 1 && shortcode_exists('trx_sc_anchor')) {
		echo do_shortcode('[trx_sc_anchor id="post_'.esc_attr(get_the_ID()).'" title="'.esc_attr(get_the_title()).'" icon="'.esc_attr(parkivia_get_post_icon()).'"]');
	}

	// Sticky label
	if ( is_sticky() && !is_paged() ) {
		?><span class="post_label label_sticky"></span><?php
	}

	// Featured image
	parkivia_show_post_featured( array(
											'class' => $parkivia_columns == 1 ? 'parkivia-full-height' : '',
											'show_no_image' => true,
											'thumb_bg' => true,
											'thumb_size' => parkivia_get_thumb_size(
																	strpos(parkivia_get_theme_option('body_style'), 'full')!==false
																		? ( $parkivia_columns > 1 ? 'huge' : 'original' )
																		: (	$parkivia_columns > 2 ? 'big' : 'huge')
																	)
											) 
										);

	?><div class="post_inner"><div class="post_inner_content"><?php 

		?><div class="post_header entry-header"><?php 
			do_action('parkivia_action_before_post_title'); 

			// Post title
			the_title( sprintf( '<h3 class="post_title entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h3>' );
			
			do_action('parkivia_action_before_post_meta'); 

			// Post meta
			$parkivia_components = parkivia_array_get_keys_by_value(parkivia_get_theme_option('meta_parts'));
			$parkivia_counters = parkivia_array_get_keys_by_value(parkivia_get_theme_option('counters'));
			$parkivia_post_meta = empty($parkivia_components) 
										? '' 
										: parkivia_show_post_meta(apply_filters('parkivia_filter_post_meta_args', array(
												'components' => $parkivia_components,
												'counters' => $parkivia_counters,
												'seo' => false,
												'echo' => false
												), $parkivia_blog_style[0], $parkivia_columns)
											);
			parkivia_show_layout($parkivia_post_meta);
		?></div><!-- .entry-header -->
	
		<div class="post_content entry-content">
			<div class="post_content_inner">
				<?php
				$parkivia_show_learn_more = !in_array($parkivia_post_format, array('link', 'aside', 'status', 'quote'));
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
				parkivia_show_layout($parkivia_post_meta);
			}
			// More button
			if ( $parkivia_show_learn_more ) {
				?><p><a class="more-link" href="<?php echo esc_url(get_permalink()); ?>"><?php esc_html_e('Read more', 'parkivia'); ?></a></p><?php
			}
			?>
		</div><!-- .entry-content -->

	</div></div><!-- .post_inner -->

</article>