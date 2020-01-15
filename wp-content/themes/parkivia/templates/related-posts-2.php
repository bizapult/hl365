<?php
/**
 * The template 'Style 2' to displaying related posts
 *
 * @package WordPress
 * @subpackage PARKIVIA
 * @since PARKIVIA 1.0
 */

$parkivia_link = get_permalink();
$parkivia_post_format = get_post_format();
$parkivia_post_format = empty($parkivia_post_format) ? 'standard' : str_replace('post-format-', '', $parkivia_post_format);
?><div id="post-<?php the_ID(); ?>" 
	<?php post_class( 'related_item related_item_style_2 post_format_'.esc_attr($parkivia_post_format) ); ?>><?php
	parkivia_show_post_featured(array(
		'thumb_size' => apply_filters('parkivia_filter_related_thumb_size', parkivia_get_thumb_size( (int) parkivia_get_theme_option('related_posts') == 1 ? 'huge' : 'big' )),
		'show_no_image' => parkivia_get_theme_setting('allow_no_image'),
		'singular' => false
		)
	);
	?><div class="post_header entry-header">
        <h5 class="post_title entry-title"><a href="<?php echo esc_url($parkivia_link); ?>"><?php the_title(); ?></a></h5>
        <?php
		if ( in_array(get_post_type(), array( 'post', 'attachment' ) ) ) {
			?><span class="post_date"><a href="<?php echo esc_url($parkivia_link); ?>"><?php echo wp_kses_data(parkivia_get_date()); ?></a></span><?php
		}
		?>
	</div>
</div>