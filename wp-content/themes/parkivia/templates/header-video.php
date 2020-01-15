<?php
/**
 * The template to display the background video in the header
 *
 * @package WordPress
 * @subpackage PARKIVIA
 * @since PARKIVIA 1.0.14
 */
$parkivia_header_video = parkivia_get_header_video();
$parkivia_embed_video = '';
if (!empty($parkivia_header_video) && !parkivia_is_from_uploads($parkivia_header_video)) {
	if (parkivia_is_youtube_url($parkivia_header_video) && preg_match('/[=\/]([^=\/]*)$/', $parkivia_header_video, $matches) && !empty($matches[1])) {
		?><div id="background_video" data-youtube-code="<?php echo esc_attr($matches[1]); ?>"></div><?php
	} else {
		global $wp_embed;
		if (false && is_object($wp_embed)) {
			$parkivia_embed_video = do_shortcode($wp_embed->run_shortcode( '[embed]' . trim($parkivia_header_video) . '[/embed]' ));
			$parkivia_embed_video = parkivia_make_video_autoplay($parkivia_embed_video);
		} else {
			$parkivia_header_video = str_replace('/watch?v=', '/embed/', $parkivia_header_video);
			$parkivia_header_video = parkivia_add_to_url($parkivia_header_video, array(
				'feature' => 'oembed',
				'controls' => 0,
				'autoplay' => 1,
				'showinfo' => 0,
				'modestbranding' => 1,
				'wmode' => 'transparent',
				'enablejsapi' => 1,
				'origin' => home_url(),
				'widgetid' => 1
			));
			$parkivia_embed_video = '<iframe src="' . esc_url($parkivia_header_video) . '" width="1170" height="658" allowfullscreen="0" frameborder="0"></iframe>';
		}
		?><div id="background_video"><?php parkivia_show_layout($parkivia_embed_video); ?></div><?php
	}
}
?>