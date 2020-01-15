<?php
/**
 * The template to display the copyright info in the footer
 *
 * @package WordPress
 * @subpackage PARKIVIA
 * @since PARKIVIA 1.0.10
 */

// Copyright area
?> 
<div class="footer_copyright_wrap<?php
				if (!parkivia_is_inherit(parkivia_get_theme_option('copyright_scheme')))
					echo ' scheme_' . esc_attr(parkivia_get_theme_option('copyright_scheme'));
 				?>">
	<div class="footer_copyright_inner">
		<div class="content_wrap">
			<div class="copyright_text"><?php
				$parkivia_copyright = parkivia_get_theme_option('copyright');
				if (!empty($parkivia_copyright)) {
					// Replace {{Y}} or {Y} with the current year
					$parkivia_copyright = str_replace(array('{{Y}}', '{Y}'), date('Y'), $parkivia_copyright);
					// Replace {{...}} and ((...)) on the <i>...</i> and <b>...</b>
					$parkivia_copyright = parkivia_prepare_macros($parkivia_copyright);
					// Display copyright
					echo wp_kses(nl2br($parkivia_copyright), 'parkivia_kses_content' );
				}
			?></div>
		</div>
	</div>
</div>
