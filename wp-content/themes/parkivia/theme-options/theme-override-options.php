<?php
/**
 * Theme Options and override-options support
 *
 * @package WordPress
 * @subpackage PARKIVIA
 * @since PARKIVIA 1.0.29
 */


// -----------------------------------------------------------------
// -- Override options
// -----------------------------------------------------------------

if ( !function_exists('parkivia_init_override_options') ) {
	add_action( 'after_setup_theme', 'parkivia_init_override_options' );
	function parkivia_init_override_options() {
		if ( is_admin() ) {
			add_action('admin_enqueue_scripts',	'parkivia_add_override_scripts');
			add_action('save_post',				'parkivia_save_override_options');
		}
	}
}
	
// Load required styles and scripts for admin mode
if ( !function_exists( 'parkivia_add_override_scripts' ) ) {
	//Handler of the add_action("admin_enqueue_scripts", 'parkivia_add_override_scripts');
	function parkivia_add_override_scripts() {
		// If current screen is 'Edit Page' - load font icons
		$screen = function_exists('get_current_screen') ? get_current_screen() : false;
		if (is_object($screen) && parkivia_allow_override(!empty($screen->post_type) ? $screen->post_type : $screen->id)) {
			wp_enqueue_style( 'fontello-style',  parkivia_get_file_url('css/font-icons/css/fontello-embedded.css'), array(), null );
			wp_enqueue_script( 'jquery-ui-tabs', false, array('jquery', 'jquery-ui-core'), null, true );
			wp_enqueue_script( 'jquery-ui-accordion', false, array('jquery', 'jquery-ui-core'), null, true );
			wp_enqueue_script( 'parkivia-options', parkivia_get_file_url('theme-options/theme-options.js'), array('jquery'), null, true );
			wp_localize_script( 'parkivia-options', 'parkivia_dependencies', parkivia_get_theme_dependencies() );
		}
	}
}


// Check if override options is allow
if (!function_exists('parkivia_allow_override')) {
	function parkivia_allow_override($post_type) {
		return apply_filters('parkivia_filter_allow_override', in_array($post_type, array('page', 'post')), $post_type);
	}
}

// Add overriden options
if (!function_exists('parkivia_options_override_add_options')) {
    add_filter('parkivia_filter_override_options', 'parkivia_options_override_add_options');
    function parkivia_options_override_add_options($list) {
        global $post_type;
        if (parkivia_allow_override($post_type)) {
            $list[] = array(sprintf('parkivia_override_options_%s', $post_type),
                esc_html__('Theme Options', 'parkivia'),
                'parkivia_show_override',
                $post_type,
                $post_type=='post' ? 'side' : 'advanced',
                'default'
            );
        }
        return $list;
    }
}

// Callback function to show fields in override options
if (!function_exists('parkivia_show_override')) {
	function parkivia_show_override($post=false, $args=false) {
		if (empty($post) || !is_object($post) || empty($post->ID)) {
			global $post, $post_type;
			$mb_post_id = $post->ID;
			$mb_post_type = $post_type;
		} else {
			$mb_post_id = $post->ID;
			$mb_post_type = $post->post_type;
		}
		if (parkivia_allow_override($mb_post_type)) {
			// Load saved options 
			$meta = get_post_meta($mb_post_id, 'parkivia_options', true);
			$tabs_titles = $tabs_content = array();
			global $PARKIVIA_STORAGE;
			// Refresh linked data if this field is controller for the another (linked) field
			// Do this before show fields to refresh data in the $PARKIVIA_STORAGE
			foreach ($PARKIVIA_STORAGE['options'] as $k=>$v) {
				if (!isset($v['override']) || strpos($v['override']['mode'], $mb_post_type)===false) continue;
				if (!empty($v['linked'])) {
					$v['val'] = isset($meta[$k]) ? $meta[$k] : 'inherit';
					if (!empty($v['val']) && !parkivia_is_inherit($v['val']))
						parkivia_refresh_linked_data($v['val'], $v['linked']);
				}
			}
			// Show fields
			foreach ($PARKIVIA_STORAGE['options'] as $k=>$v) {
				if (!isset($v['override']) || strpos($v['override']['mode'], $mb_post_type)===false || $v['type'] == 'hidden') continue;
				if (empty($v['override']['section']))
					$v['override']['section'] = esc_html__('General', 'parkivia');
				if (!isset($tabs_titles[$v['override']['section']])) {
					$tabs_titles[$v['override']['section']] = $v['override']['section'];
					$tabs_content[$v['override']['section']] = '';
				}
				$v['val'] = isset($meta[$k]) ? $meta[$k] : 'inherit';
				$tabs_content[$v['override']['section']] .= parkivia_options_show_field($k, $v, $mb_post_type);
			}
			if (count($tabs_titles) > 0) {
				?>
				<div class="parkivia_options parkivia_override_options">
					<input type="hidden" name="override_options_post_nonce" value="<?php echo esc_attr(wp_create_nonce(admin_url())); ?>" />
					<input type="hidden" name="override_options_post_type" value="<?php echo esc_attr($mb_post_type); ?>" />
					<div id="parkivia_options_tabs" class="parkivia_tabs">
						<ul><?php
							$cnt = 0;
							foreach ($tabs_titles as $k=>$v) {
								$cnt++;
								?><li><a href="#parkivia_options_<?php echo esc_attr($cnt); ?>"><?php echo esc_html($v); ?></a></li><?php
							}
						?></ul>
						<?php
							$cnt = 0;
							foreach ($tabs_content as $k=>$v) {
								$cnt++;
								?>
								<div id="parkivia_options_<?php echo esc_attr($cnt); ?>" class="parkivia_tabs_section parkivia_options_section">
									<?php parkivia_show_layout($v); ?>
								</div>
								<?php
							}
						?>
					</div>
				</div>
				<?php		
			}
		}
	}
}


// Save data from override options
if (!function_exists('parkivia_save_override_options')) {
	//Handler of the add_action('save_post', 'parkivia_save_override_options');
	function parkivia_save_override_options($post_id) {

		// verify nonce
		if ( !wp_verify_nonce( parkivia_get_value_gp('override_options_post_nonce'), admin_url() ) )
			return $post_id;

		// check autosave
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return $post_id;
		}

		$post_type = wp_kses_data(wp_unslash(isset($_POST['override_options_post_type']) ? $_POST['override_options_post_type'] : $_POST['post_type']));

		// check permissions
		$capability = 'page';
		$post_types = get_post_types( array( 'name' => $post_type), 'objects' );
		if (!empty($post_types) && is_array($post_types)) {
			foreach ($post_types  as $type) {
				$capability = $type->capability_type;
				break;
			}
		}
		if (!current_user_can('edit_'.($capability), $post_id)) {
			return $post_id;
		}

		// Save meta
		$meta = array();
		$options = parkivia_storage_get('options');
		foreach ($options as $k=>$v) {
			// Skip not overriden options
			if (!isset($v['override']) || strpos($v['override']['mode'], $post_type)===false) continue;
			// Skip inherited options
			if (!empty($_POST["parkivia_options_inherit_{$k}"])) continue;
			// Skip hidden options
			if (!isset($_POST["parkivia_options_field_{$k}"]) && $v['type']=='hidden') continue;
			// Get option value from POST
			$meta[$k] = isset($_POST["parkivia_options_field_{$k}"])
							? parkivia_get_value_gp("parkivia_options_field_{$k}")
							: ($v['type']=='checkbox' ? 0 : '');
		}
		$meta = apply_filters( 'parkivia_filter_update_post_meta', $meta, $post_id );
		update_post_meta($post_id, 'parkivia_options', $meta);
		
		// Save separate meta options to search template pages
		if ($post_type=='page' && !empty($_POST['page_template']) && $_POST['page_template']=='blog.php') {
			update_post_meta($post_id, 'parkivia_options_post_type', isset($meta['post_type']) ? $meta['post_type'] : 'post');
			update_post_meta($post_id, 'parkivia_options_parent_cat', isset($meta['parent_cat']) ? $meta['parent_cat'] : 0);
		}
	}
}
?>