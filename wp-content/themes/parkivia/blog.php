<?php
/**
 * The template to display blog archive
 *
 * @package WordPress
 * @subpackage PARKIVIA
 * @since PARKIVIA 1.0
 */

/*
Template Name: Blog archive
*/

/**
 * Make page with this template and put it into menu
 * to display posts as blog archive
 * You can setup output parameters (blog style, posts per page, parent category, etc.)
 * in the Theme Options section (under the page content)
 * You can build this page in the WordPress editor or any Page Builder to make custom page layout:
 * just insert %%CONTENT%% in the desired place of content
 */

// Get template page's content
$parkivia_content = '';
$parkivia_blog_archive_mask = '%%CONTENT%%';
$parkivia_blog_archive_subst = sprintf('<div class="blog_archive">%s</div>', $parkivia_blog_archive_mask);
if ( have_posts() ) {
	the_post();
	if (($parkivia_content = apply_filters('the_content', get_the_content())) != '') {
		if (($parkivia_pos = strpos($parkivia_content, $parkivia_blog_archive_mask)) !== false) {
			$parkivia_content = preg_replace('/(\<p\>\s*)?'.$parkivia_blog_archive_mask.'(\s*\<\/p\>)/i', $parkivia_blog_archive_subst, $parkivia_content);
		} else
			$parkivia_content .= $parkivia_blog_archive_subst;
		$parkivia_content = explode($parkivia_blog_archive_mask, $parkivia_content);
		// Add VC custom styles to the inline CSS
		$vc_custom_css = get_post_meta( get_the_ID(), '_wpb_shortcodes_custom_css', true );
		if ( !empty( $vc_custom_css ) ) parkivia_add_inline_css(strip_tags($vc_custom_css));
	}
}

// Prepare args for a new query
$parkivia_args = array(
	'post_status' => current_user_can('read_private_pages') && current_user_can('read_private_posts') ? array('publish', 'private') : 'publish'
);
$parkivia_args = parkivia_query_add_posts_and_cats($parkivia_args, '', parkivia_get_theme_option('post_type'), parkivia_get_theme_option('parent_cat'));
$parkivia_page_number = get_query_var('paged') ? get_query_var('paged') : (get_query_var('page') ? get_query_var('page') : 1);
if ($parkivia_page_number > 1) {
	$parkivia_args['paged'] = $parkivia_page_number;
	$parkivia_args['ignore_sticky_posts'] = true;
}
$parkivia_ppp = parkivia_get_theme_option('posts_per_page');
if ((int) $parkivia_ppp != 0)
	$parkivia_args['posts_per_page'] = (int) $parkivia_ppp;
// Make a new main query
$GLOBALS['wp_the_query']->query($parkivia_args);


// Add internal query vars in the new query!
if (is_array($parkivia_content) && count($parkivia_content) == 2) {
	set_query_var('blog_archive_start', $parkivia_content[0]);
	set_query_var('blog_archive_end', $parkivia_content[1]);
}

get_template_part('index');
?>