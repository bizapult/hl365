<?php
/**
 * The Footer: widgets area, logo, footer menu and socials
 *
 * @package WordPress
 * @subpackage PARKIVIA
 * @since PARKIVIA 1.0
 */

						// Widgets area inside page content
						parkivia_create_widgets_area('widgets_below_content');
						?>				
					</div><!-- </.content> -->

					<?php
					// Show main sidebar
					get_sidebar();

					// Widgets area below page content
					parkivia_create_widgets_area('widgets_below_page');

					$parkivia_body_style = parkivia_get_theme_option('body_style');
					if ($parkivia_body_style != 'fullscreen') {
						?></div><!-- </.content_wrap> --><?php
					}
					?>
			</div><!-- </.page_content_wrap> -->

			<?php
			// Footer
			$parkivia_footer_type = parkivia_get_theme_option("footer_type");
			if ($parkivia_footer_type == 'custom' && !parkivia_is_layouts_available())
				$parkivia_footer_type = 'default';
			get_template_part( "templates/footer-{$parkivia_footer_type}");
			?>

		</div><!-- /.page_wrap -->

	</div><!-- /.body_wrap -->

	<?php if (parkivia_is_on(parkivia_get_theme_option('debug_mode')) && parkivia_get_file_dir('images/makeup.jpg')!='') { ?>
		<img src="<?php echo esc_url(parkivia_get_file_url('images/makeup.jpg')); ?>" id="makeup">
	<?php } ?>

	<?php wp_footer(); ?>

</body>
</html>