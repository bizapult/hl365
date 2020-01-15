<?php
/**
 * Setup theme-specific fonts and colors
 *
 * @package WordPress
 * @subpackage PARKIVIA
 * @since PARKIVIA 1.0.22
 */

if (!defined("PARKIVIA_THEME_FREE"))		define("PARKIVIA_THEME_FREE", false);
if (!defined("PARKIVIA_THEME_FREE_WP"))	define("PARKIVIA_THEME_FREE_WP", false);

// Theme storage
$PARKIVIA_STORAGE = array(
	// Theme required plugin's slugs
	'required_plugins' => array_merge(

		// List of plugins for both - FREE and PREMIUM versions
		//-----------------------------------------------------
		array(
			// Required plugins
			// DON'T COMMENT OR REMOVE NEXT LINES!
			'trx_addons'					=> esc_html__('ThemeREX Addons', 'parkivia'),

			// Recommended (supported) plugins fot both (lite and full) versions
			// If plugin not need - comment (or remove) it
			'contact-form-7'				=> esc_html__('Contact Form 7', 'parkivia'),
			'mailchimp-for-wp'				=> esc_html__('MailChimp for WP', 'parkivia'),
            'gdpr-compliance'               => esc_html__('WP GDPR Compliance', 'parkivia'),
			'trx_updater'               	=> esc_html__('TRX Updater', 'parkivia'),
		),

		// List of plugins for the FREE version only
		//-----------------------------------------------------
		PARKIVIA_THEME_FREE 
			? array(
					// Recommended (supported) plugins for the FREE (lite) version
					'siteorigin-panels'			=> esc_html__('SiteOrigin Panels', 'parkivia'),
					) 

		// List of plugins for the PREMIUM version only
		//-----------------------------------------------------
			: array(
					// Recommended (supported) plugins for the PRO (full) version
					// If plugin not need - comment (or remove) it
					'booked'					=> esc_html__('Booked Appointments', 'parkivia'),
					'booki'					    => esc_html__('Booki', 'parkivia'),
					'calculated-fields-form'	=> esc_html__('Calculated Fields Form', 'parkivia'),
					'essential-grid'			=> esc_html__('Essential Grid', 'parkivia'),
					'revslider'					=> esc_html__('Revolution Slider', 'parkivia'),
					'js_composer'				=> esc_html__('WPBakery Page Builder', 'parkivia'),
					'vc-extensions-bundle'		=> esc_html__('WPBakery Page Builder extensions bundle', 'parkivia'),
					)
	),

	// Key validator: market[env|loc]-vendor[axiom|ancora|themerex]
	'theme_pro_key'		=> PARKIVIA_THEME_FREE 
								? 'env-ancora'
								: '',

	// Theme-specific URLs (will be escaped in place of the output)
	'theme_demo_url'	=> 'http://parkivia.ancorathemes.com',
	'theme_doc_url'		=> 'http://parkivia.ancorathemes.com/doc',
	'theme_download_url'=> 'https://themeforest.net/item/parkivia-auto-parking-car-maintenance-wordpress-theme/22640341',

	'theme_support_url'	=> 'https://themerex.net/support/',

	'theme_video_url'	=> 'https://www.youtube.com/channel/UCdIjRh7-lPVHqTTKpaf8PLA',	// Ancora

	// Comma separated slugs of theme-specific categories (for get relevant news in the dashboard widget)
	// (i.e. 'children,kindergarten')
	'theme_categories'  => '',

	// Responsive resolutions
	// Parameters to create css media query: min, max
	'responsive'		=> array(
						// By device
						'desktop'	=> array('min' => 1680),
						'notebook'	=> array('min' => 1280, 'max' => 1679),
						'tablet'	=> array('min' =>  768, 'max' => 1279),
						'mobile'	=> array('max' =>  767),
						// By size
						'xxl'		=> array('max' => 1679),
						'xl'		=> array('max' => 1441),
						'lg'		=> array('max' => 1263),
						'md'		=> array('max' => 1023),
						'sm'		=> array('max' =>  767),
						'sm_wp'		=> array('max' =>  600),
						'xs'		=> array('max' =>  479)
						)
);

// Theme init priorities:
// Action 'after_setup_theme'
// 1 - register filters to add/remove lists items in the Theme Options
// 2 - create Theme Options
// 3 - add/remove Theme Options elements
// 5 - load Theme Options. Attention! After this step you can use only basic options (not overriden)
// 9 - register other filters (for installer, etc.)
//10 - standard Theme init procedures (not ordered)
// Action 'wp_loaded'
// 1 - detect override mode. Attention! Only after this step you can use overriden options (separate values for the shop, courses, etc.)

if ( !function_exists('parkivia_customizer_theme_setup1') ) {
	add_action( 'after_setup_theme', 'parkivia_customizer_theme_setup1', 1 );
	function parkivia_customizer_theme_setup1() {

		// -----------------------------------------------------------------
		// -- ONLY FOR PROGRAMMERS, NOT FOR CUSTOMER
		// -- Internal theme settings
		// -----------------------------------------------------------------
		parkivia_storage_set('settings', array(
			
			'duplicate_options'		=> 'child',		// none  - use separate options for the main and the child-theme
													// child - duplicate theme options from the main theme to the child-theme only
													// both  - sinchronize changes in the theme options between main and child themes

			'customize_refresh'		=> 'auto',		// Refresh method for preview area in the Appearance - Customize:
													// auto - refresh preview area on change each field with Theme Options
													// manual - refresh only obn press button 'Refresh' at the top of Customize frame

			'max_load_fonts'		=> 5,			// Max fonts number to load from Google fonts or from uploaded fonts

			'comment_maxlength'		=> 1000,		// Max length of the message from contact form

			'comment_after_name'	=> true,		// Place 'comment' field before the 'name' and 'email'

			'socials_type'			=> 'icons',		// Type of socials:
													// icons - use font icons to present social networks
													// images - use images from theme's folder trx_addons/css/icons.png

			'icons_type'			=> 'icons',		// Type of other icons:
													// icons - use font icons to present icons
													// images - use images from theme's folder trx_addons/css/icons.png

			'icons_selector'		=> 'internal',	// Icons selector in the shortcodes:
													// vc (default) - standard VC or Elementor's icons selector (very slow and don't support images)
													// internal - internal popup with plugin's or theme's icons list (fast)
			'check_min_version'		=> true,		// Check if exists a .min version of .css and .js and return path to it
													// instead the path to the original file
													// (if debug_mode is off and modification time of the original file < time of the .min file)
			'autoselect_menu'		=> false,		// Show any menu if no menu selected in the location 'main_menu'
													// (for example, the theme is just activated)
			'disable_jquery_ui'		=> false,		// Prevent loading custom jQuery UI libraries in the third-party plugins
		
			'use_mediaelements'		=> true,		// Load script "Media Elements" to play video and audio
			
			'tgmpa_upload'			=> false,		// Allow upload not pre-packaged plugins via TGMPA
			
			'allow_no_image'		=> false,		// Allow use image placeholder if no image present in the blog, related posts, post navigation, etc.

			'separate_schemes'		=> true 		// Save color schemes to the separate files __color_xxx.css (true) or append its to the __custom.css (false)
		));


		// -----------------------------------------------------------------
		// -- Theme fonts (Google and/or custom fonts)
		// -----------------------------------------------------------------
		
		// Fonts to load when theme start
		// It can be Google fonts or uploaded fonts, placed in the folder /css/font-face/font-name inside the theme folder
		// Attention! Font's folder must have name equal to the font's name, with spaces replaced on the dash '-'
		// For example: font name 'TeX Gyre Termes', folder 'TeX-Gyre-Termes'
		parkivia_storage_set('load_fonts', array(
			// Google font
			array(
				'name'	 => 'Nunito',
				'family' => 'sans-serif',
				'styles' => '400,400i,700,700i'		// Parameter 'style' used only for the Google fonts
				),
			// Font-face packed with theme
			array(
				'name'   => 'Montserrat',
				'family' => 'sans-serif',
                'styles' => '400,500,600,700'		// Parameter 'style' used only for the Google fonts
				)
		));
		
		// Characters subset for the Google fonts. Available values are: latin,latin-ext,cyrillic,cyrillic-ext,greek,greek-ext,vietnamese
		parkivia_storage_set('load_fonts_subset', 'latin,latin-ext');
		
		// Settings of the main tags
		// Attention! Font name in the parameter 'font-family' will be enclosed in the quotes and no spaces after comma!
		// For example:	'font-family' => '"Nunito",sans-serif'	- is correct

		parkivia_storage_set('theme_fonts', array(
			'p' => array(
				'title'				=> esc_html__('Main text', 'parkivia'),
				'description'		=> esc_html__('Font settings of the main text of the site. Attention! For correct display of the site on mobile devices, use only units "rem", "em" or "ex"', 'parkivia'),
				'font-family'		=> '"Nunito",sans-serif',
				'font-size' 		=> '16px',
				'font-weight'		=> '400',
				'font-style'		=> 'normal',
				'line-height'		=> '23px',
				'text-decoration'	=> 'none',
				'text-transform'	=> 'none',
				'letter-spacing'	=> '0px',
				'margin-top'		=> '0em',
				'margin-bottom'		=> '1.4em'
				),
			'h1' => array(
				'title'				=> esc_html__('Heading 1', 'parkivia'),
				'font-family'		=> '"Montserrat",sans-serif',
				'font-size' 		=> '3em',
				'font-weight'		=> '700',
				'font-style'		=> 'normal',
				'line-height'		=> '1.21em',
				'text-decoration'	=> 'none',
				'text-transform'	=> 'none',
				'letter-spacing'	=> '0px',
				'margin-top'		=> '9.4rem',
				'margin-bottom'		=> '1.6rem'
				),
			'h2' => array(
				'title'				=> esc_html__('Heading 2', 'parkivia'),
				'font-family'		=> '"Montserrat",sans-serif',
				'font-size' 		=> '2.25em',
				'font-weight'		=> '700',
				'font-style'		=> 'normal',
				'line-height'		=> '1.28em',
				'text-decoration'	=> 'none',
				'text-transform'	=> 'none',
				'letter-spacing'	=> '0px',
                'margin-top'		=> '9.1rem',
                'margin-bottom'		=> '1.6rem'
				),
			'h3' => array(
				'title'				=> esc_html__('Heading 3', 'parkivia'),
				'font-family'		=> '"Montserrat",sans-serif',
				'font-size' 		=> '1.875em',
				'font-weight'		=> '700',
				'font-style'		=> 'normal',
				'line-height'		=> '1.27em',
				'text-decoration'	=> 'none',
				'text-transform'	=> 'none',
				'letter-spacing'	=> '0px',
                'margin-top'		=> '8.8rem',
                'margin-bottom'		=> '1.45rem'
				),
			'h4' => array(
				'title'				=> esc_html__('Heading 4', 'parkivia'),
				'font-family'		=> '"Montserrat",sans-serif',
				'font-size' 		=> '1.5em',
				'font-weight'		=> '700',
				'font-style'		=> 'normal',
				'line-height'		=> '1.29em',
				'text-decoration'	=> 'none',
				'text-transform'	=> 'none',
				'letter-spacing'	=> '0px',
                'margin-top'		=> '8.7rem',
                'margin-bottom'		=> '1.6rem'
				),
			'h5' => array(
				'title'				=> esc_html__('Heading 5', 'parkivia'),
				'font-family'		=> '"Montserrat",sans-serif',
				'font-size' 		=> '1.125em',
				'font-weight'		=> '700',
				'font-style'		=> 'normal',
				'line-height'		=> '1.33em',
				'text-decoration'	=> 'none',
				'text-transform'	=> 'none',
				'letter-spacing'	=> '0px',
                'margin-top'		=> '8.3rem',
                'margin-bottom'		=> '1.4rem'
				),
			'h6' => array(
				'title'				=> esc_html__('Heading 6', 'parkivia'),
				'font-family'		=> '"Nunito",sans-serif',
				'font-size' 		=> '1em',
				'font-weight'		=> '700',
				'font-style'		=> 'normal',
				'line-height'		=> '1.38em',
				'text-decoration'	=> 'none',
				'text-transform'	=> 'none',
				'letter-spacing'	=> '0px',
                'margin-top'		=> '8.1rem',
                'margin-bottom'		=> '1.1rem'
				),
			'logo' => array(
				'title'				=> esc_html__('Logo text', 'parkivia'),
				'description'		=> esc_html__('Font settings of the text case of the logo', 'parkivia'),
				'font-family'		=> '"Montserrat",sans-serif',
				'font-size' 		=> '1.8em',
				'font-weight'		=> '700',
				'font-style'		=> 'normal',
				'line-height'		=> '1.25em',
				'text-decoration'	=> 'none',
				'text-transform'	=> 'none',
				'letter-spacing'	=> '0px'
				),
			'button' => array(
				'title'				=> esc_html__('Buttons', 'parkivia'),
                'font-family'		=> 'inherit',
				'font-size' 		=> '1em',
				'font-weight'		=> '700',
				'font-style'		=> 'normal',
				'line-height'		=> '22px',
				'text-decoration'	=> 'none',
				'text-transform'	=> 'none',
				'letter-spacing'	=> '0'
				),
			'input' => array(
				'title'				=> esc_html__('Input fields', 'parkivia'),
				'description'		=> esc_html__('Font settings of the input fields, dropdowns and textareas', 'parkivia'),
				'font-family'		=> 'inherit',
				'font-size' 		=> '1em',
				'font-weight'		=> '700',
				'font-style'		=> 'normal',
				'line-height'		=> '1.5em',	// Attention! Firefox don't allow line-height less then 1.5em in the select
				'text-decoration'	=> 'none',
				'text-transform'	=> 'none',
				'letter-spacing'	=> '0px'
				),
			'info' => array(
				'title'				=> esc_html__('Post meta', 'parkivia'),
				'description'		=> esc_html__('Font settings of the post meta: date, counters, share, etc.', 'parkivia'),
				'font-family'		=> 'inherit',
				'font-size' 		=> '14px',
				'font-weight'		=> '400',
				'font-style'		=> 'normal',
				'line-height'		=> '1.5em',
				'text-decoration'	=> 'none',
				'text-transform'	=> 'none',
				'letter-spacing'	=> '0px',
				'margin-top'		=> '0.4em',
				'margin-bottom'		=> ''
				),
			'menu' => array(
				'title'				=> esc_html__('Main menu', 'parkivia'),
				'description'		=> esc_html__('Font settings of the main menu items', 'parkivia'),
                'font-family'		=> 'inherit',
				'font-size' 		=> '1em',
				'font-weight'		=> '700',
				'font-style'		=> 'normal',
				'line-height'		=> '1.5em',
				'text-decoration'	=> 'none',
				'text-transform'	=> 'none',
				'letter-spacing'	=> '0px'
				),
			'submenu' => array(
				'title'				=> esc_html__('Dropdown menu', 'parkivia'),
				'description'		=> esc_html__('Font settings of the dropdown menu items', 'parkivia'),
                'font-family'		=> 'inherit',
				'font-size' 		=> '1em',
				'font-weight'		=> '700',
				'font-style'		=> 'normal',
				'line-height'		=> '1.5em',
				'text-decoration'	=> 'none',
				'text-transform'	=> 'none',
				'letter-spacing'	=> '0px'
				)
		));
		
		
		// -----------------------------------------------------------------
		// -- Theme colors for customizer
		// -- Attention! Inner scheme must be last in the array below
		// -----------------------------------------------------------------
		parkivia_storage_set('scheme_color_groups', array(
			'main'	=> array(
							'title'			=> esc_html__('Main', 'parkivia'),
							'description'	=> esc_html__('Colors of the main content area', 'parkivia')
							),
			'alter'	=> array(
							'title'			=> esc_html__('Alter', 'parkivia'),
							'description'	=> esc_html__('Colors of the alternative blocks (sidebars, etc.)', 'parkivia')
							),
			'extra'	=> array(
							'title'			=> esc_html__('Extra', 'parkivia'),
							'description'	=> esc_html__('Colors of the extra blocks (dropdowns, price blocks, table headers, etc.)', 'parkivia')
							),
			'inverse' => array(
							'title'			=> esc_html__('Inverse', 'parkivia'),
							'description'	=> esc_html__('Colors of the inverse blocks - when link color used as background of the block (dropdowns, blockquotes, etc.)', 'parkivia')
							),
			'input'	=> array(
							'title'			=> esc_html__('Input', 'parkivia'),
							'description'	=> esc_html__('Colors of the form fields (text field, textarea, select, etc.)', 'parkivia')
							),
			)
		);
		parkivia_storage_set('scheme_color_names', array(
			'bg_color'	=> array(
							'title'			=> esc_html__('Background color', 'parkivia'),
							'description'	=> esc_html__('Background color of this block in the normal state', 'parkivia')
							),
			'bg_hover'	=> array(
							'title'			=> esc_html__('Background hover', 'parkivia'),
							'description'	=> esc_html__('Background color of this block in the hovered state', 'parkivia')
							),
			'bd_color'	=> array(
							'title'			=> esc_html__('Border color', 'parkivia'),
							'description'	=> esc_html__('Border color of this block in the normal state', 'parkivia')
							),
			'bd_hover'	=>  array(
							'title'			=> esc_html__('Border hover', 'parkivia'),
							'description'	=> esc_html__('Border color of this block in the hovered state', 'parkivia')
							),
			'text'		=> array(
							'title'			=> esc_html__('Text', 'parkivia'),
							'description'	=> esc_html__('Color of the plain text inside this block', 'parkivia')
							),
			'text_dark'	=> array(
							'title'			=> esc_html__('Text dark', 'parkivia'),
							'description'	=> esc_html__('Color of the dark text (bold, header, etc.) inside this block', 'parkivia')
							),
			'text_light'=> array(
							'title'			=> esc_html__('Text light', 'parkivia'),
							'description'	=> esc_html__('Color of the light text (post meta, etc.) inside this block', 'parkivia')
							),
			'text_link'	=> array(
							'title'			=> esc_html__('Link', 'parkivia'),
							'description'	=> esc_html__('Color of the links inside this block', 'parkivia')
							),
			'text_hover'=> array(
							'title'			=> esc_html__('Link hover', 'parkivia'),
							'description'	=> esc_html__('Color of the hovered state of links inside this block', 'parkivia')
							),
			'text_link2'=> array(
							'title'			=> esc_html__('Link 2', 'parkivia'),
							'description'	=> esc_html__('Color of the accented texts (areas) inside this block', 'parkivia')
							),
			'text_hover2'=> array(
							'title'			=> esc_html__('Link 2 hover', 'parkivia'),
							'description'	=> esc_html__('Color of the hovered state of accented texts (areas) inside this block', 'parkivia')
							),
			'text_link3'=> array(
							'title'			=> esc_html__('Link 3', 'parkivia'),
							'description'	=> esc_html__('Color of the other accented texts (buttons) inside this block', 'parkivia')
							),
			'text_hover3'=> array(
							'title'			=> esc_html__('Link 3 hover', 'parkivia'),
							'description'	=> esc_html__('Color of the hovered state of other accented texts (buttons) inside this block', 'parkivia')
							)
			)
		);
		parkivia_storage_set('schemes', array(
		
			// Color scheme: 'default'
			'default' => array(
				'title'	 => esc_html__('Default', 'parkivia'),
				'internal' => true,
				'colors' => array(
					
					// Whole block border and background
					'bg_color'			=> '#ffffff',
					'bd_color'			=> '#dee0e1',
		
					// Text and links colors
					'text'				=> '#888d93',
					'text_light'		=> '#b1b4b7',
					'text_dark'			=> '#21303e',
					'text_link'			=> '#13c33e',
					'text_hover'		=> '#1690d3',
					'text_link2'		=> '#16d445',
					'text_hover2'		=> '#14be3d',
					'text_link3'		=> '#008fd6',
					'text_hover3'		=> '#0084c5',
		
					// Alternative blocks (sidebar, tabs, alternative blocks, etc.)
					'alter_bg_color'	=> '#f6f7f8',
					'alter_bg_hover'	=> '#e6e8eb',
					'alter_bd_color'	=> '#e5e5e5',
					'alter_bd_hover'	=> '#dadada',
					'alter_text'		=> '#888d93',
					'alter_light'		=> '#b1b4b7',
					'alter_dark'		=> '#22313e',
					'alter_link'		=> '#16d445',
					'alter_hover'		=> '#008fd6',
					'alter_link2'		=> '#8be77c',
					'alter_hover2'		=> '#80d572',
					'alter_link3'		=> '#eec432',
					'alter_hover3'		=> '#ddb837',
		
					// Extra blocks (submenu, tabs, color blocks, etc.)
					'extra_bg_color'	=> '#294256',
					'extra_bg_hover'	=> '#1a2631',
					'extra_bd_color'	=> '#2e4051',
					'extra_bd_hover'	=> '#3d3d3d',
					'extra_text'		=> '#888d93',
					'extra_light'		=> '#b1b4b7',
					'extra_dark'		=> '#9da0a4',
					'extra_link'		=> '#ffffff',
					'extra_hover'		=> '#ffffff',
					'extra_link2'		=> '#80d572',
					'extra_hover2'		=> '#8be77c',
					'extra_link3'		=> '#ddb837',
					'extra_hover3'		=> '#eec432',
		
					// Input fields (form's fields and textarea)
					'input_bg_color'	=> '#ffffff',
					'input_bg_hover'	=> '#ffffff',
					'input_bd_color'	=> '#bcbcbc',
					'input_bd_hover'	=> '#13c33e',
					'input_text'		=> '#888d93',
					'input_light'		=> '#888d93',
					'input_dark'		=> '#22313e',
					
					// Inverse blocks (text and links on the 'text_link' background)
					'inverse_bd_color'	=> '#67bcc1',
					'inverse_bd_hover'	=> '#5aa4a9',
					'inverse_text'		=> '#ffffff',
					'inverse_light'		=> '#13c33e',
					'inverse_dark'		=> '#21303e',
					'inverse_link'		=> '#ffffff',
					'inverse_hover'		=> '#1d1d1d'
				)
			),
		
			// Color scheme: 'dark'
			'dark' => array(
				'title'  => esc_html__('Dark', 'parkivia'),
				'internal' => true,
				'colors' => array(
					
					// Whole block border and background
					'bg_color'			=> '#0e0d12',
					'bd_color'			=> '#2e2c33',
		
					// Text and links colors
					'text'				=> '#9da0a4',
					'text_light'		=> '#5f5f5f',
					'text_dark'			=> '#ffffff',
                    'text_link'			=> '#13c33e',
                    'text_hover'		=> '#1690d3',
                    'text_link2'		=> '#16d445',
                    'text_hover2'		=> '#14be3d',
                    'text_link3'		=> '#008fd6',
                    'text_hover3'		=> '#0084c5',

					// Alternative blocks (sidebar, tabs, alternative blocks, etc.)
					'alter_bg_color'	=> '#15202a',
					'alter_bg_hover'	=> '#333333',
					'alter_bd_color'	=> '#464646',
					'alter_bd_hover'	=> '#4a4a4a',
					'alter_text'		=> '#888d93',
					'alter_light'		=> '#5f5f5f',
					'alter_dark'		=> '#ffffff',
					'alter_link'		=> '#ffaa5f',
					'alter_hover'		=> '#fe7259',
					'alter_link2'		=> '#8be77c',
					'alter_hover2'		=> '#80d572',
					'alter_link3'		=> '#eec432',
					'alter_hover3'		=> '#ddb837',

					// Extra blocks (submenu, tabs, color blocks, etc.)
					'extra_bg_color'	=> '#1e1d22',
					'extra_bg_hover'	=> '#28272e',
					'extra_bd_color'	=> '#464646',
					'extra_bd_hover'	=> '#4a4a4a',
					'extra_text'		=> '#a6a6a6',
					'extra_light'		=> '#5f5f5f',
					'extra_dark'		=> '#ffffff',
					'extra_link'		=> '#ffaa5f',
					'extra_hover'		=> '#fe7259',
					'extra_link2'		=> '#80d572',
					'extra_hover2'		=> '#8be77c',
					'extra_link3'		=> '#ddb837',
					'extra_hover3'		=> '#eec432',

					// Input fields (form's fields and textarea)
					'input_bg_color'	=> '#2e2d32',
					'input_bg_hover'	=> '#2e2d32',
					'input_bd_color'	=> '#2e2d32',
					'input_bd_hover'	=> '#353535',
					'input_text'		=> '#888d93',
					'input_light'		=> '#646b72',
					'input_dark'		=> '#ffffff',
					
					// Inverse blocks (text and links on the 'text_link' background)
					'inverse_bd_color'	=> '#e36650',
					'inverse_bd_hover'	=> '#cb5b47',
					'inverse_text'		=> '#1d1d1d',
					'inverse_light'		=> '#1b2a37',
					'inverse_dark'		=> '#21303e',
					'inverse_link'		=> '#ffffff',
					'inverse_hover'		=> '#1d1d1d'
				)
			)
		
		));
		
		// Simple schemes substitution
		parkivia_storage_set('schemes_simple', array(
			// Main color	// Slave elements and it's darkness koef.
			'text_link'		=> array('alter_hover' => 1,	'extra_link' => 1, 'inverse_bd_color' => 0.85, 'inverse_bd_hover' => 0.7),
			'text_hover'	=> array('alter_link' => 1,		'extra_hover' => 1),
			'text_link2'	=> array('alter_hover2' => 1,	'extra_link2' => 1),
			'text_hover2'	=> array('alter_link2' => 1,	'extra_hover2' => 1),
			'text_link3'	=> array('alter_hover3' => 1,	'extra_link3' => 1),
			'text_hover3'	=> array('alter_link3' => 1,	'extra_hover3' => 1)
		));

		// Additional colors for each scheme
		// Parameters:	'color' - name of the color from the scheme that should be used as source for the transformation
		//				'alpha' - to make color transparent (0.0 - 1.0)
		//				'hue', 'saturation', 'brightness' - inc/dec value for each color's component
		parkivia_storage_set('scheme_colors_add', array(
			'bg_color_0'		=> array('color' => 'bg_color',			'alpha' => 0),
			'bg_color_02'		=> array('color' => 'bg_color',			'alpha' => 0.2),
			'bg_color_07'		=> array('color' => 'bg_color',			'alpha' => 0.7),
			'bg_color_08'		=> array('color' => 'bg_color',			'alpha' => 0.8),
			'bg_color_09'		=> array('color' => 'bg_color',			'alpha' => 0.9),
			'alter_bg_color_07'	=> array('color' => 'alter_bg_color',	'alpha' => 0.7),
			'alter_bg_color_04'	=> array('color' => 'alter_bg_color',	'alpha' => 0.4),
			'alter_bg_color_02'	=> array('color' => 'alter_bg_color',	'alpha' => 0.2),
			'alter_bd_color_02'	=> array('color' => 'alter_bd_color',	'alpha' => 0.2),
			'alter_link_02'		=> array('color' => 'alter_link',		'alpha' => 0.2),
			'alter_link_07'		=> array('color' => 'alter_link',		'alpha' => 0.7),
			'extra_bg_color_07'	=> array('color' => 'extra_bg_color',	'alpha' => 0.7),
			'extra_link_02'		=> array('color' => 'extra_link',		'alpha' => 0.2),
			'extra_link_07'		=> array('color' => 'extra_link',		'alpha' => 0.7),
			'text_dark_07'		=> array('color' => 'text_dark',		'alpha' => 0.7),
			'text_link_02'		=> array('color' => 'text_link',		'alpha' => 0.2),
			'text_link_07'		=> array('color' => 'text_link',		'alpha' => 0.7),
			'text_link_09'		=> array('color' => 'text_link',		'alpha' => 0.9),
			'inverse_link_02'		=> array('color' => 'inverse_link',		'alpha' => 0.2),
			'inverse_link_03'		=> array('color' => 'inverse_link',		'alpha' => 0.3),
			'inverse_link_04'		=> array('color' => 'inverse_link',		'alpha' => 0.7),
			'inverse_link_06'		=> array('color' => 'inverse_link',		'alpha' => 0.6),
            'text_link_blend'	=> array('color' => 'text_link',		'hue' => 2, 'saturation' => -5, 'brightness' => 5),
			'alter_link_blend'	=> array('color' => 'alter_link',		'hue' => 2, 'saturation' => -5, 'brightness' => 5)
		));
		
		// Parameters to set order of schemes in the css
		parkivia_storage_set('schemes_sorted', array(
													'color_scheme', 'header_scheme', 'menu_scheme', 'sidebar_scheme', 'footer_scheme'
													));
		
		
		// -----------------------------------------------------------------
		// -- Theme specific thumb sizes
		// -----------------------------------------------------------------
		parkivia_storage_set('theme_thumbs', apply_filters('parkivia_filter_add_thumb_sizes', array(
			// Width of the image is equal to the content area width (without sidebar)
			// Height is fixed
			'parkivia-thumb-huge'		=> array(
												'size'	=> array(1170, 658, true),
												'title' => esc_html__( 'Huge image', 'parkivia' ),
												'subst'	=> 'trx_addons-thumb-huge'
												),
			// Width of the image is equal to the content area width (with sidebar)
			// Height is fixed
			'parkivia-thumb-big' 		=> array(
												'size'	=> array( 770, 428, true),
												'title' => esc_html__( 'Large image', 'parkivia' ),
												'subst'	=> 'trx_addons-thumb-big'
												),

			// Width of the image is equal to the 1/3 of the content area width (without sidebar)
			// Height is fixed
			'parkivia-thumb-med' 		=> array(
												'size'	=> array( 370, 220, true),
												'title' => esc_html__( 'Medium image', 'parkivia' ),
												'subst'	=> 'trx_addons-thumb-medium'
												),

			// Small square image (for avatars in comments, etc.)
			'parkivia-thumb-tiny' 		=> array(
												'size'	=> array(  90,  90, true),
												'title' => esc_html__( 'Small square avatar', 'parkivia' ),
												'subst'	=> 'trx_addons-thumb-tiny'
												),

			// Width of the image is equal to the content area width (with sidebar)
			// Height is proportional (only downscale, not crop)
			'parkivia-thumb-masonry-big' => array(
												'size'	=> array( 770,   0, false),		// Only downscale, not crop
												'title' => esc_html__( 'Masonry Large (scaled)', 'parkivia' ),
												'subst'	=> 'trx_addons-thumb-masonry-big'
												),

			// Width of the image is equal to the 1/3 of the full content area width (without sidebar)
			// Height is proportional (only downscale, not crop)
			'parkivia-thumb-masonry'		=> array(
												'size'	=> array( 370,   0, false),		// Only downscale, not crop
												'title' => esc_html__( 'Masonry (scaled)', 'parkivia' ),
												'subst'	=> 'trx_addons-thumb-masonry'
												)
			))
		);
	}
}




//------------------------------------------------------------------------
// One-click import support
//------------------------------------------------------------------------

// Set theme specific importer options
if ( !function_exists( 'parkivia_importer_set_options' ) ) {
	add_filter( 'trx_addons_filter_importer_options', 'parkivia_importer_set_options', 9 );
	function parkivia_importer_set_options($options=array()) {
		if (is_array($options)) {
			// Save or not installer's messages to the log-file
			$options['debug'] = false;
			// Prepare demo data
			$options['demo_url'] = esc_url(parkivia_get_protocol() . '://demofiles.ancorathemes.com/parkivia/');
			// Required plugins
			$options['required_plugins'] = array_keys(parkivia_storage_get('required_plugins'));
			// Set number of thumbnails to regenerate when its imported (if demo data was zipped without cropped images)
			// Set 0 to prevent regenerate thumbnails (if demo data archive is already contain cropped images)
			$options['regenerate_thumbnails'] = 3;
			// Default demo
			$options['files']['default']['title'] = esc_html__('Parkivia Demo', 'parkivia');
			$options['files']['default']['domain_dev'] = esc_url(parkivia_get_protocol().'://parkivia.dv.ancorathemes.com');		// Developers domain
			$options['files']['default']['domain_demo']= esc_url(parkivia_get_protocol().'://parkivia.ancorathemes.com');		// Demo-site domain
			// If theme need more demo - just copy 'default' and change required parameter
			// Banners
			$options['banners'] = array(
				array(
					'image' => parkivia_get_file_url('theme-specific/theme-about/images/frontpage.png'),
					'title' => esc_html__('Front Page Builder', 'parkivia'),
					'content' => wp_kses(__("Create your front page right in the WordPress Customizer. There's no need in WPBakery Page Builder, or any other builder. Simply enable/disable sections, fill them out with content, and customize to your liking.", 'parkivia'), 'parkivia_kses_content' ),
					'link_url' => esc_url('//www.youtube.com/watch?v=VT0AUbMl_KA'),
					'link_caption' => esc_html__('Watch Video Introduction', 'parkivia'),
					'duration' => 20
					),
				array(
					'image' => parkivia_get_file_url('theme-specific/theme-about/images/layouts.png'),
					'title' => esc_html__('Layouts Builder', 'parkivia'),
					'content' => wp_kses(__('Use Layouts Builder to create and customize header and footer styles for your website. With a flexible page builder interface and custom shortcodes, you can create as many header and footer layouts as you want with ease.', 'parkivia'), 'parkivia_kses_content' ),
					'link_url' => esc_url('//www.youtube.com/watch?v=pYhdFVLd7y4'),
					'link_caption' => esc_html__('Learn More', 'parkivia'),
					'duration' => 20
					),
				array(
					'image' => parkivia_get_file_url('theme-specific/theme-about/images/documentation.png'),
					'title' => esc_html__('Read Full Documentation', 'parkivia'),
					'content' => wp_kses(__('Need more details? Please check our full online documentation for detailed information on how to use Parkivia.', 'parkivia'), 'parkivia_kses_content' ),
					'link_url' => esc_url(parkivia_storage_get('theme_doc_url')),
					'link_caption' => esc_html__('Online Documentation', 'parkivia'),
					'duration' => 15
					),
				array(
					'image' => parkivia_get_file_url('theme-specific/theme-about/images/video-tutorials.png'),
					'title' => esc_html__('Video Tutorials', 'parkivia'),
					'content' => wp_kses(__('No time for reading documentation? Check out our video tutorials and learn how to customize Parkivia in detail.', 'parkivia'), 'parkivia_kses_content' ),
					'link_url' => esc_url(parkivia_storage_get('theme_video_url')),
					'link_caption' => esc_html__('Video Tutorials', 'parkivia'),
					'duration' => 15
					),
				array(
					'image' => parkivia_get_file_url('theme-specific/theme-about/images/studio.png'),
					'title' => esc_html__('Website Customization Studio', 'parkivia'),
					'content' => wp_kses(__("Need a website fast? Order our custom service, and we'll build a website based on this theme for a very fair price. We can also implement additional functionality such as website translation, setting up WPML, and much more.", 'parkivia'), 'parkivia_kses_content' ),
					'link_url' => esc_url('//themerex.net/offers/?utm_source=offers&utm_medium=click&utm_campaign=themedash'),
					'link_caption' => esc_html__('Contact Us', 'parkivia'),
					'duration' => 25
					)
				);
		}
		return $options;
	}
}




// -----------------------------------------------------------------
// -- Theme options for customizer
// -----------------------------------------------------------------
if (!function_exists('parkivia_create_theme_options')) {

	function parkivia_create_theme_options() {

		// Message about options override. 
		// Attention! Not need esc_html() here, because this message put in wp_kses_data() below
		$msg_override = __('Attention! Some of these options can be overridden in the following sections (Blog, Plugins settings, etc.) or in the settings of individual pages', 'parkivia');
		
		// Color schemes number: if < 2 - hide fields with selectors
		$hide_schemes = count(parkivia_storage_get('schemes')) < 2;
		
		parkivia_storage_set('options', array(
		
			// 'Logo & Site Identity'
			'title_tagline' => array(
				"title" => esc_html__('Logo & Site Identity', 'parkivia'),
				"desc" => '',
				"priority" => 10,
				"type" => "section"
				),
			'logo_info' => array(
				"title" => esc_html__('Logo in the header', 'parkivia'),
				"desc" => '',
				"priority" => 20,
				"type" => "info",
				),
			'logo_text' => array(
				"title" => esc_html__('Use Site Name as Logo', 'parkivia'),
				"desc" => wp_kses_data( __('Use the site title and tagline as a text logo if no image is selected', 'parkivia') ),
				"class" => "parkivia_column-1_2 parkivia_new_row",
				"priority" => 30,
				"std" => 1,
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "checkbox"
				),
			'logo_retina_enabled' => array(
				"title" => esc_html__('Allow retina display logo', 'parkivia'),
				"desc" => wp_kses_data( __('Show fields to select logo images for Retina display', 'parkivia') ),
				"class" => "parkivia_column-1_2",
				"priority" => 40,
				"refresh" => false,
				"std" => 0,
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "checkbox"
				),
			'logo_zoom' => array(
				"title" => esc_html__('Logo zoom', 'parkivia'),
				"desc" => wp_kses_data( __("Zoom the logo. 1 - original size. Maximum size of logo depends on the actual size of the picture", 'parkivia') ),
				"std" => 1,
				"min" => 0.2,
				"max" => 2,
				"step" => 0.1,
				"refresh" => false,
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "slider"
				),
			// Parameter 'logo' was replaced with standard WordPress 'custom_logo'
			'logo_retina' => array(
				"title" => esc_html__('Logo for Retina', 'parkivia'),
				"desc" => wp_kses_data( __('Select or upload site logo used on Retina displays (if empty - use default logo from the field above)', 'parkivia') ),
				"class" => "parkivia_column-1_2",
				"priority" => 70,
				"dependency" => array(
					'logo_retina_enabled' => array(1)
				),
				"std" => '',
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "image"
				),
			'logo_mobile_header' => array(
				"title" => esc_html__('Logo for the mobile header', 'parkivia'),
				"desc" => wp_kses_data( __('Select or upload site logo to display it in the mobile header (if enabled in the section "Header - Header mobile"', 'parkivia') ),
				"class" => "parkivia_column-1_2 parkivia_new_row",
				"std" => '',
				"type" => "image"
				),
			'logo_mobile_header_retina' => array(
				"title" => esc_html__('Logo for the mobile header for Retina', 'parkivia'),
				"desc" => wp_kses_data( __('Select or upload site logo used on Retina displays (if empty - use default logo from the field above)', 'parkivia') ),
				"class" => "parkivia_column-1_2",
				"dependency" => array(
					'logo_retina_enabled' => array(1)
				),
				"std" => '',
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "image"
				),
			'logo_mobile' => array(
				"title" => esc_html__('Logo mobile', 'parkivia'),
				"desc" => wp_kses_data( __('Select or upload site logo to display it in the mobile menu', 'parkivia') ),
				"class" => "parkivia_column-1_2 parkivia_new_row",
				"std" => '',
				"type" => "image"
				),
			'logo_mobile_retina' => array(
				"title" => esc_html__('Logo mobile for Retina', 'parkivia'),
				"desc" => wp_kses_data( __('Select or upload site logo used on Retina displays (if empty - use default logo from the field above)', 'parkivia') ),
				"class" => "parkivia_column-1_2",
				"dependency" => array(
					'logo_retina_enabled' => array(1)
				),
				"std" => '',
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "image"
				),
			'logo_side' => array(
				"title" => esc_html__('Logo side', 'parkivia'),
				"desc" => wp_kses_data( __('Select or upload site logo (with vertical orientation) to display it in the side menu', 'parkivia') ),
				"class" => "parkivia_column-1_2 parkivia_new_row",
				"std" => '',
				"type" => "image"
				),
			'logo_side_retina' => array(
				"title" => esc_html__('Logo side for Retina', 'parkivia'),
				"desc" => wp_kses_data( __('Select or upload site logo (with vertical orientation) to display it in the side menu on Retina displays (if empty - use default logo from the field above)', 'parkivia') ),
				"class" => "parkivia_column-1_2",
				"dependency" => array(
					'logo_retina_enabled' => array(1)
				),
				"std" => '',
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "image"
				),
			
		
		
			// 'General settings'
			'general' => array(
				"title" => esc_html__('General Settings', 'parkivia'),
				"desc" => wp_kses_data( $msg_override ),
				"priority" => 20,
				"type" => "section",
				),

			'general_layout_info' => array(
				"title" => esc_html__('Layout', 'parkivia'),
				"desc" => '',
				"type" => "info",
				),
			'body_style' => array(
				"title" => esc_html__('Body style', 'parkivia'),
				"desc" => wp_kses_data( __('Select width of the body content', 'parkivia') ),
				"override" => array(
					'mode' => 'page,cpt_team,cpt_services,cpt_dishes,cpt_competitions,cpt_rounds,cpt_matches,cpt_cars,cpt_properties,cpt_courses,cpt_portfolio',
					'section' => esc_html__('Content', 'parkivia')
				),
				"refresh" => false,
				"std" => 'wide',
				"options" => parkivia_get_list_body_styles(),
				"type" => "select"
				),
			'page_width' => array(
				"title" => esc_html__('Page width', 'parkivia'),
				"desc" => wp_kses_data( __("Total width of the site content and sidebar (in pixels). If empty - use default width", 'parkivia') ),
				"dependency" => array(
					'body_style' => array('boxed', 'wide')
				),
				"std" => 1170,
				"min" => 1000,
				"max" => 1400,
				"step" => 10,
				"refresh" => false,
				"customizer" => 'page',		// SASS name to preview changes 'on fly'
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "slider"
				),
			'boxed_bg_image' => array(
				"title" => esc_html__('Boxed bg image', 'parkivia'),
				"desc" => wp_kses_data( __('Select or upload image, used as background in the boxed body', 'parkivia') ),
				"dependency" => array(
					'body_style' => array('boxed')
				),
				"override" => array(
					'mode' => 'page,cpt_team,cpt_services,cpt_dishes,cpt_competitions,cpt_rounds,cpt_matches,cpt_cars,cpt_properties,cpt_courses,cpt_portfolio',
					'section' => esc_html__('Content', 'parkivia')
				),
				"std" => '',
				"hidden" => true,
				"type" => "image"
				),
			'remove_margins' => array(
				"title" => esc_html__('Remove margins', 'parkivia'),
				"desc" => wp_kses_data( __('Remove margins above and below the content area', 'parkivia') ),
				"override" => array(
					'mode' => 'page,cpt_team,cpt_services,cpt_dishes,cpt_competitions,cpt_rounds,cpt_matches,cpt_cars,cpt_properties,cpt_courses,cpt_portfolio',
					'section' => esc_html__('Content', 'parkivia')
				),
				"refresh" => false,
				"std" => 0,
				"type" => "checkbox"
				),

			'general_sidebar_info' => array(
				"title" => esc_html__('Sidebar', 'parkivia'),
				"desc" => '',
				"type" => "info",
				),
			'sidebar_position' => array(
				"title" => esc_html__('Sidebar position', 'parkivia'),
				"desc" => wp_kses_data( __('Select position to show sidebar', 'parkivia') ),
				"override" => array(
					'mode' => 'page,cpt_team,cpt_services,cpt_dishes,cpt_competitions,cpt_rounds,cpt_matches,cpt_cars,cpt_properties,cpt_courses,cpt_portfolio',
					'section' => esc_html__('Widgets', 'parkivia')
				),
				"std" => 'right',
				"options" => array(),
				"type" => "switch"
				),
			'sidebar_widgets' => array(
				"title" => esc_html__('Sidebar widgets', 'parkivia'),
				"desc" => wp_kses_data( __('Select default widgets to show in the sidebar', 'parkivia') ),
				"override" => array(
					'mode' => 'page,cpt_team,cpt_services,cpt_dishes,cpt_competitions,cpt_rounds,cpt_matches,cpt_cars,cpt_properties,cpt_courses,cpt_portfolio',
					'section' => esc_html__('Widgets', 'parkivia')
				),
				"dependency" => array(
					'sidebar_position' => array('left', 'right')
				),
				"std" => 'sidebar_widgets',
				"options" => array(),
				"type" => "select"
				),
			'sidebar_width' => array(
				"title" => esc_html__('Sidebar width', 'parkivia'),
				"desc" => wp_kses_data( __("Width of the sidebar (in pixels). If empty - use default width", 'parkivia') ),
				"std" => 370,
				"min" => 150,
				"max" => 500,
				"step" => 10,
				"refresh" => false,
				"customizer" => 'sidebar',		// SASS name to preview changes 'on fly'
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "slider"
				),
			'sidebar_gap' => array(
				"title" => esc_html__('Sidebar gap', 'parkivia'),
				"desc" => wp_kses_data( __("Gap between content and sidebar (in pixels). If empty - use default gap", 'parkivia') ),
				"std" => 30,
				"min" => 0,
				"max" => 100,
				"step" => 1,
				"refresh" => false,
				"customizer" => 'gap',		// SASS name to preview changes 'on fly'
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "slider"
				),
			'expand_content' => array(
				"title" => esc_html__('Expand content', 'parkivia'),
				"desc" => wp_kses_data( __('Expand the content width if the sidebar is hidden', 'parkivia') ),
				"refresh" => false,
				"std" => 1,
				"type" => "checkbox"
				),


			'general_widgets_info' => array(
				"title" => esc_html__('Additional widgets', 'parkivia'),
				"desc" => '',
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "info",
				),
			'widgets_above_page' => array(
				"title" => esc_html__('Widgets at the top of the page', 'parkivia'),
				"desc" => wp_kses_data( __('Select widgets to show at the top of the page (above content and sidebar)', 'parkivia') ),
				"override" => array(
					'mode' => 'page,cpt_team,cpt_services,cpt_dishes,cpt_competitions,cpt_rounds,cpt_matches,cpt_cars,cpt_properties,cpt_courses,cpt_portfolio',
					'section' => esc_html__('Widgets', 'parkivia')
				),
				"std" => 'hide',
				"options" => array(),
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "select"
				),
			'widgets_above_content' => array(
				"title" => esc_html__('Widgets above the content', 'parkivia'),
				"desc" => wp_kses_data( __('Select widgets to show at the beginning of the content area', 'parkivia') ),
				"override" => array(
					'mode' => 'page,cpt_team,cpt_services,cpt_dishes,cpt_competitions,cpt_rounds,cpt_matches,cpt_cars,cpt_properties,cpt_courses,cpt_portfolio',
					'section' => esc_html__('Widgets', 'parkivia')
				),
				"std" => 'hide',
				"options" => array(),
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "select"
				),
			'widgets_below_content' => array(
				"title" => esc_html__('Widgets below the content', 'parkivia'),
				"desc" => wp_kses_data( __('Select widgets to show at the ending of the content area', 'parkivia') ),
				"override" => array(
					'mode' => 'page,cpt_team,cpt_services,cpt_dishes,cpt_competitions,cpt_rounds,cpt_matches,cpt_cars,cpt_properties,cpt_courses,cpt_portfolio',
					'section' => esc_html__('Widgets', 'parkivia')
				),
				"std" => 'hide',
				"options" => array(),
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "select"
				),
			'widgets_below_page' => array(
				"title" => esc_html__('Widgets at the bottom of the page', 'parkivia'),
				"desc" => wp_kses_data( __('Select widgets to show at the bottom of the page (below content and sidebar)', 'parkivia') ),
				"override" => array(
					'mode' => 'page,cpt_team,cpt_services,cpt_dishes,cpt_competitions,cpt_rounds,cpt_matches,cpt_cars,cpt_properties,cpt_courses,cpt_portfolio',
					'section' => esc_html__('Widgets', 'parkivia')
				),
				"std" => 'hide',
				"options" => array(),
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "select"
				),

			'general_effects_info' => array(
				"title" => esc_html__('Design & Effects', 'parkivia'),
				"desc" => '',
				"type" => "info",
				),
			'border_radius' => array(
				"title" => esc_html__('Border radius', 'parkivia'),
				"desc" => wp_kses_data( __("Specify the border radius of the form fields and buttons in pixels", 'parkivia') ),
				"std" => 5,
				"min" => 0,
				"max" => 20,
				"step" => 1,
				"refresh" => false,
				"customizer" => 'rad',		// SASS name to preview changes 'on fly'
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "slider"
				),

			'general_misc_info' => array(
				"title" => esc_html__('Miscellaneous', 'parkivia'),
				"desc" => '',
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "info",
				),
			'seo_snippets' => array(
				"title" => esc_html__('SEO snippets', 'parkivia'),
				"desc" => wp_kses_data( __('Add structured data markup to the single posts and pages', 'parkivia') ),
				"std" => 0,
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "checkbox"
				),
            'privacy_text' => array(
                "title" => esc_html__("Text with Privacy Policy link", 'parkivia'),
                "desc"  => wp_kses_data( __("Specify text with Privacy Policy link for the checkbox 'I agree ...'", 'parkivia') ),
                "std"   => wp_kses( __( 'I agree that my submitted data is being collected and stored.', 'parkivia'), 'parkivia_kses_content' ),
                "type"  => "text"
            ),
		
		
			// 'Header'
			'header' => array(
				"title" => esc_html__('Header', 'parkivia'),
				"desc" => wp_kses_data( $msg_override ),
				"priority" => 30,
				"type" => "section"
				),

			'header_style_info' => array(
				"title" => esc_html__('Header style', 'parkivia'),
				"desc" => '',
				"type" => "info"
				),
			'header_type' => array(
				"title" => esc_html__('Header style', 'parkivia'),
				"desc" => wp_kses_data( __('Choose whether to use the default header or header Layouts (available only if the ThemeREX Addons is activated)', 'parkivia') ),
				"override" => array(
					'mode' => 'page,cpt_team,cpt_services,cpt_dishes,cpt_competitions,cpt_rounds,cpt_matches,cpt_cars,cpt_properties,cpt_courses,cpt_portfolio',
					'section' => esc_html__('Header', 'parkivia')
				),
				"std" => 'default',
				"options" => parkivia_get_list_header_footer_types(),
				"type" => PARKIVIA_THEME_FREE || !parkivia_exists_trx_addons() ? "hidden" : "switch"
				),
			'header_style' => array(
				"title" => esc_html__('Select custom layout', 'parkivia'),
				"desc" => wp_kses( __("Select custom header from Layouts Builder", 'parkivia'), 'parkivia_kses_content' ),
				"override" => array(
					'mode' => 'page,cpt_team,cpt_services,cpt_dishes,cpt_competitions,cpt_rounds,cpt_matches,cpt_cars,cpt_properties,cpt_courses,cpt_portfolio',
					'section' => esc_html__('Header', 'parkivia')
				),
				"dependency" => array(
					'header_type' => array('custom')
				),
				"std" => PARKIVIA_THEME_FREE ? 'header-default' : 'header-default',
				"options" => array(),
				"type" => "select"
				),
			'header_position' => array(
				"title" => esc_html__('Header position', 'parkivia'),
				"desc" => wp_kses_data( __('Select position to display the site header', 'parkivia') ),
				"override" => array(
					'mode' => 'page,cpt_team,cpt_services,cpt_dishes,cpt_competitions,cpt_rounds,cpt_matches,cpt_cars,cpt_properties,cpt_courses,cpt_portfolio',
					'section' => esc_html__('Header', 'parkivia')
				),
				"std" => 'default',
				"options" => array(),
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "switch"
				),
			'header_fullheight' => array(
				"title" => esc_html__('Header fullheight', 'parkivia'),
				"desc" => wp_kses_data( __("Enlarge header area to fill whole screen. Used only if header have a background image", 'parkivia') ),
				"override" => array(
					'mode' => 'page,cpt_team,cpt_services,cpt_dishes,cpt_competitions,cpt_rounds,cpt_matches,cpt_cars,cpt_properties,cpt_courses,cpt_portfolio',
					'section' => esc_html__('Header', 'parkivia')
				),
				"std" => 0,
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "checkbox"
				),
			'header_zoom' => array(
				"title" => esc_html__('Header zoom', 'parkivia'),
				"desc" => wp_kses_data( __("Zoom the header title. 1 - original size", 'parkivia') ),
				"std" => 1,
				"min" => 0.3,
				"max" => 2,
				"step" => 0.1,
				"refresh" => false,
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "slider"
				),
			'header_wide' => array(
				"title" => esc_html__('Header fullwidth', 'parkivia'),
				"desc" => wp_kses_data( __('Do you want to stretch the header widgets area to the entire window width?', 'parkivia') ),
				"override" => array(
					'mode' => 'page,cpt_team,cpt_services,cpt_dishes,cpt_competitions,cpt_rounds,cpt_matches,cpt_cars,cpt_properties,cpt_courses,cpt_portfolio',
					'section' => esc_html__('Header', 'parkivia')
				),
				"dependency" => array(
					'header_type' => array('default')
				),
				"std" => 1,
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "checkbox"
				),

			'header_widgets_info' => array(
				"title" => esc_html__('Header widgets', 'parkivia'),
				"desc" => wp_kses_data( __('Here you can place a widget slider, advertising banners, etc.', 'parkivia') ),
				"type" => "info"
				),
			'header_widgets' => array(
				"title" => esc_html__('Header widgets', 'parkivia'),
				"desc" => wp_kses_data( __('Select set of widgets to show in the header on each page', 'parkivia') ),
				"override" => array(
					'mode' => 'page,cpt_team,cpt_services,cpt_dishes,cpt_competitions,cpt_rounds,cpt_matches,cpt_cars,cpt_properties,cpt_courses,cpt_portfolio',
					'section' => esc_html__('Header', 'parkivia'),
					"desc" => wp_kses_data( __('Select set of widgets to show in the header on this page', 'parkivia') ),
				),
				"std" => 'hide',
				"options" => array(),
				"type" => "select"
				),
			'header_columns' => array(
				"title" => esc_html__('Header columns', 'parkivia'),
				"desc" => wp_kses_data( __('Select number columns to show widgets in the Header. If 0 - autodetect by the widgets count', 'parkivia') ),
				"override" => array(
					'mode' => 'page,cpt_team,cpt_services,cpt_dishes,cpt_competitions,cpt_rounds,cpt_matches,cpt_cars,cpt_properties,cpt_courses,cpt_portfolio',
					'section' => esc_html__('Header', 'parkivia')
				),
				"dependency" => array(
					'header_type' => array('default'),
					'header_widgets' => array('^hide')
				),
				"std" => 0,
				"options" => parkivia_get_list_range(0,6),
				"type" => "select"
				),

			'menu_info' => array(
				"title" => esc_html__('Main menu', 'parkivia'),
				"desc" => wp_kses_data( __('Select main menu style, position and other parameters', 'parkivia') ),
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "info"
				),
			'menu_style' => array(
				"title" => esc_html__('Menu position', 'parkivia'),
				"desc" => wp_kses_data( __('Select position of the main menu', 'parkivia') ),
				"override" => array(
					'mode' => 'page,cpt_team,cpt_services,cpt_dishes,cpt_competitions,cpt_rounds,cpt_matches,cpt_cars,cpt_properties,cpt_courses,cpt_portfolio',
					'section' => esc_html__('Header', 'parkivia')
				),
				"std" => 'top',
				"options" => array(
					'top'	=> esc_html__('Top',	'parkivia'),
					'left'	=> esc_html__('Left',	'parkivia'),
					'right'	=> esc_html__('Right',	'parkivia')
				),
				"type" => PARKIVIA_THEME_FREE || !parkivia_exists_trx_addons() ? "hidden" : "switch"
				),
			'menu_side_stretch' => array(
				"title" => esc_html__('Stretch sidemenu', 'parkivia'),
				"desc" => wp_kses_data( __('Stretch sidemenu to window height (if menu items number >= 5)', 'parkivia') ),
				"override" => array(
					'mode' => 'page,cpt_team,cpt_services,cpt_dishes,cpt_competitions,cpt_rounds,cpt_matches,cpt_cars,cpt_properties,cpt_courses,cpt_portfolio',
					'section' => esc_html__('Header', 'parkivia')
				),
				"dependency" => array(
					'menu_style' => array('left', 'right')
				),
				"std" => 0,
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "checkbox"
				),
			'menu_side_icons' => array(
				"title" => esc_html__('Iconed sidemenu', 'parkivia'),
				"desc" => wp_kses_data( __('Get icons from anchors and display it in the sidemenu or mark sidemenu items with simple dots', 'parkivia') ),
				"override" => array(
					'mode' => 'page,cpt_team,cpt_services,cpt_dishes,cpt_competitions,cpt_rounds,cpt_matches,cpt_cars,cpt_properties,cpt_courses,cpt_portfolio',
					'section' => esc_html__('Header', 'parkivia')
				),
				"dependency" => array(
					'menu_style' => array('left', 'right')
				),
				"std" => 1,
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "checkbox"
				),
			'menu_mobile_fullscreen' => array(
				"title" => esc_html__('Mobile menu fullscreen', 'parkivia'),
				"desc" => wp_kses_data( __('Display mobile and side menus on full screen (if checked) or slide narrow menu from the left or from the right side (if not checked)', 'parkivia') ),
				"std" => 1,
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "checkbox"
				),

			'header_image_info' => array(
				"title" => esc_html__('Header image', 'parkivia'),
				"desc" => '',
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "info"
				),
			'header_image_override' => array(
				"title" => esc_html__('Header image override', 'parkivia'),
				"desc" => wp_kses_data( __("Allow override the header image with the page's/post's/product's/etc. featured image", 'parkivia') ),
				"override" => array(
					'mode' => 'page',
					'section' => esc_html__('Header', 'parkivia')
				),
				"std" => 0,
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "checkbox"
				),

			'header_mobile_info' => array(
				"title" => esc_html__('Mobile header', 'parkivia'),
				"desc" => wp_kses_data( __("Configure the mobile version of the header", 'parkivia') ),
				"priority" => 500,
				"dependency" => array(
					'header_type' => array('default')
				),
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "info"
				),
			'header_mobile_enabled' => array(
				"title" => esc_html__('Enable the mobile header', 'parkivia'),
				"desc" => wp_kses_data( __("Use the mobile version of the header (if checked) or relayout the current header on mobile devices", 'parkivia') ),
				"dependency" => array(
					'header_type' => array('default')
				),
				"std" => 0,
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "checkbox"
				),
			'header_mobile_additional_info' => array(
				"title" => esc_html__('Additional info', 'parkivia'),
				"desc" => wp_kses_data( __('Additional info to show at the top of the mobile header', 'parkivia') ),
				"std" => '',
				"dependency" => array(
					'header_type' => array('default'),
					'header_mobile_enabled' => array(1)
				),
				"refresh" => false,
				"teeny" => false,
				"rows" => 20,
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "text_editor"
				),
			'header_mobile_hide_info' => array(
				"title" => esc_html__('Hide additional info', 'parkivia'),
				"std" => 0,
				"dependency" => array(
					'header_type' => array('default'),
					'header_mobile_enabled' => array(1)
				),
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "checkbox"
				),
			'header_mobile_hide_logo' => array(
				"title" => esc_html__('Hide logo', 'parkivia'),
				"std" => 0,
				"dependency" => array(
					'header_type' => array('default'),
					'header_mobile_enabled' => array(1)
				),
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "checkbox"
				),
			'header_mobile_hide_login' => array(
				"title" => esc_html__('Hide login/logout', 'parkivia'),
				"std" => 0,
				"dependency" => array(
					'header_type' => array('default'),
					'header_mobile_enabled' => array(1)
				),
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "checkbox"
				),
			'header_mobile_hide_search' => array(
				"title" => esc_html__('Hide search', 'parkivia'),
				"std" => 0,
				"dependency" => array(
					'header_type' => array('default'),
					'header_mobile_enabled' => array(1)
				),
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "checkbox"
				),
			'header_mobile_hide_cart' => array(
				"title" => esc_html__('Hide cart', 'parkivia'),
				"std" => 0,
				"dependency" => array(
					'header_type' => array('default'),
					'header_mobile_enabled' => array(1)
				),
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "checkbox"
				),


		
			// 'Footer'
			'footer' => array(
				"title" => esc_html__('Footer', 'parkivia'),
				"desc" => wp_kses_data( $msg_override ),
				"priority" => 50,
				"type" => "section"
				),
			'footer_type' => array(
				"title" => esc_html__('Footer style', 'parkivia'),
				"desc" => wp_kses_data( __('Choose whether to use the default footer or footer Layouts (available only if the ThemeREX Addons is activated)', 'parkivia') ),
				"override" => array(
					'mode' => 'page,cpt_team,cpt_services,cpt_dishes,cpt_competitions,cpt_rounds,cpt_matches,cpt_cars,cpt_properties,cpt_courses,cpt_portfolio',
					'section' => esc_html__('Footer', 'parkivia')
				),
				"std" => 'default',
				"options" => parkivia_get_list_header_footer_types(),
				"type" => PARKIVIA_THEME_FREE || !parkivia_exists_trx_addons() ? "hidden" : "switch"
				),
			'footer_style' => array(
				"title" => esc_html__('Select custom layout', 'parkivia'),
				"desc" => wp_kses( __("Select custom footer from Layouts Builder", 'parkivia'), 'parkivia_kses_content' ),
				"override" => array(
					'mode' => 'page,cpt_team,cpt_services,cpt_dishes,cpt_competitions,cpt_rounds,cpt_matches,cpt_cars,cpt_properties,cpt_courses,cpt_portfolio',
					'section' => esc_html__('Footer', 'parkivia')
				),
				"dependency" => array(
					'footer_type' => array('custom')
				),
				"std" => PARKIVIA_THEME_FREE ? 'footer-default' : 'footer-default',
				"options" => array(),
				"type" => "select"
				),
			'footer_widgets' => array(
				"title" => esc_html__('Footer widgets', 'parkivia'),
				"desc" => wp_kses_data( __('Select set of widgets to show in the footer', 'parkivia') ),
				"override" => array(
					'mode' => 'page,cpt_team,cpt_services,cpt_dishes,cpt_competitions,cpt_rounds,cpt_matches,cpt_cars,cpt_properties,cpt_courses,cpt_portfolio',
					'section' => esc_html__('Footer', 'parkivia')
				),
				"dependency" => array(
					'footer_type' => array('default')
				),
				"std" => 'footer_widgets',
				"options" => array(),
				"type" => "select"
				),
			'footer_columns' => array(
				"title" => esc_html__('Footer columns', 'parkivia'),
				"desc" => wp_kses_data( __('Select number columns to show widgets in the footer. If 0 - autodetect by the widgets count', 'parkivia') ),
				"override" => array(
					'mode' => 'page,cpt_team,cpt_services,cpt_dishes,cpt_competitions,cpt_rounds,cpt_matches,cpt_cars,cpt_properties,cpt_courses,cpt_portfolio',
					'section' => esc_html__('Footer', 'parkivia')
				),
				"dependency" => array(
					'footer_type' => array('default'),
					'footer_widgets' => array('^hide')
				),
				"std" => 0,
				"options" => parkivia_get_list_range(0,6),
				"type" => "select"
				),
			'footer_wide' => array(
				"title" => esc_html__('Footer fullwidth', 'parkivia'),
				"desc" => wp_kses_data( __('Do you want to stretch the footer to the entire window width?', 'parkivia') ),
				"override" => array(
					'mode' => 'page,cpt_team,cpt_services,cpt_dishes,cpt_competitions,cpt_rounds,cpt_matches,cpt_cars,cpt_properties,cpt_courses,cpt_portfolio',
					'section' => esc_html__('Footer', 'parkivia')
				),
				"dependency" => array(
					'footer_type' => array('default')
				),
				"std" => 0,
				"type" => "checkbox"
				),
			'logo_in_footer' => array(
				"title" => esc_html__('Show logo', 'parkivia'),
				"desc" => wp_kses_data( __('Show logo in the footer', 'parkivia') ),
				'refresh' => false,
				"dependency" => array(
					'footer_type' => array('default')
				),
				"std" => 0,
				"type" => "checkbox"
				),
			'logo_footer' => array(
				"title" => esc_html__('Logo for footer', 'parkivia'),
				"desc" => wp_kses_data( __('Select or upload site logo to display it in the footer', 'parkivia') ),
				"dependency" => array(
					'footer_type' => array('default'),
					'logo_in_footer' => array(1)
				),
				"std" => '',
				"type" => "image"
				),
			'logo_footer_retina' => array(
				"title" => esc_html__('Logo for footer (Retina)', 'parkivia'),
				"desc" => wp_kses_data( __('Select or upload logo for the footer area used on Retina displays (if empty - use default logo from the field above)', 'parkivia') ),
				"dependency" => array(
					'footer_type' => array('default'),
					'logo_in_footer' => array(1),
					'logo_retina_enabled' => array(1)
				),
				"std" => '',
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "image"
				),
			'socials_in_footer' => array(
				"title" => esc_html__('Show social icons', 'parkivia'),
				"desc" => wp_kses_data( __('Show social icons in the footer (under logo or footer widgets)', 'parkivia') ),
				"dependency" => array(
					'footer_type' => array('default')
				),
				"std" => 0,
				"type" => !parkivia_exists_trx_addons() ? "hidden" : "checkbox"
				),
			'copyright' => array(
				"title" => esc_html__('Copyright', 'parkivia'),
				"desc" => wp_kses_data( __('Copyright text in the footer. Use {Y} to insert current year and press "Enter" to create a new line', 'parkivia') ),
				"translate" => true,
				"std" => esc_html__('Copyright &copy; {Y} by Parkivia. All rights reserved.', 'parkivia'),
				"dependency" => array(
					'footer_type' => array('default')
				),
				"refresh" => false,
				"type" => "textarea"
				),
			

			// 'Blog'
			'blog' => array(
				"title" => esc_html__('Blog', 'parkivia'),
				"desc" => wp_kses_data( __('Options of the the blog archive', 'parkivia') ),
				"priority" => 70,
				"type" => "panel",
				),
		
				// Blog - Posts page
				'blog_general' => array(
					"title" => esc_html__('Posts page', 'parkivia'),
					"desc" => wp_kses_data( __('Style and components of the blog archive', 'parkivia') ),
					"type" => "section",
					),
				'blog_general_info' => array(
					"title" => esc_html__('General settings', 'parkivia'),
					"desc" => '',
					"type" => "info",
					),
				'blog_style' => array(
					"title" => esc_html__('Blog style', 'parkivia'),
					"desc" => '',
					"override" => array(
						'mode' => 'page',
						'section' => esc_html__('Content', 'parkivia')
					),
					"dependency" => array(
						'#page_template' => array( 'blog.php' ),
						'.editor-page-attributes__template select' => array( 'blog.php' ),
					),
					"std" => 'excerpt',
					"options" => array(),
					"type" => "select"
					),
				'first_post_large' => array(
					"title" => esc_html__('First post large', 'parkivia'),
					"desc" => wp_kses_data( __('Make your first post stand out by making it bigger', 'parkivia') ),
					"override" => array(
						'mode' => 'page',
						'section' => esc_html__('Content', 'parkivia')
					),
					"dependency" => array(
						'#page_template' => array( 'blog.php' ),
						'.editor-page-attributes__template select' => array( 'blog.php' ),
						'blog_style' => array('classic', 'masonry')
					),
					"std" => 0,
					"type" => "checkbox"
					),
				"blog_content" => array( 
					"title" => esc_html__('Posts content', 'parkivia'),
					"desc" => wp_kses_data( __("Display either post excerpts or the full post content", 'parkivia') ),
					"std" => "excerpt",
					"dependency" => array(
						'blog_style' => array('excerpt')
					),
					"options" => array(
						'excerpt'	=> esc_html__('Excerpt',	'parkivia'),
						'fullpost'	=> esc_html__('Full post',	'parkivia')
					),
					"type" => "switch"
					),
				'excerpt_length' => array(
					"title" => esc_html__('Excerpt length', 'parkivia'),
					"desc" => wp_kses_data( __("Length (in words) to generate excerpt from the post content. Attention! If the post excerpt is explicitly specified - it appears unchanged", 'parkivia') ),
					"dependency" => array(
						'blog_style' => array('excerpt'),
						'blog_content' => array('excerpt')
					),
					"std" => 30,
					"type" => "text"
					),
				'blog_columns' => array(
					"title" => esc_html__('Blog columns', 'parkivia'),
					"desc" => wp_kses_data( __('How many columns should be used in the blog archive (from 2 to 4)?', 'parkivia') ),
					"std" => 2,
					"options" => parkivia_get_list_range(2,4),
					"type" => "hidden"
					),
				'post_type' => array(
					"title" => esc_html__('Post type', 'parkivia'),
					"desc" => wp_kses_data( __('Select post type to show in the blog archive', 'parkivia') ),
					"override" => array(
						'mode' => 'page',
						'section' => esc_html__('Content', 'parkivia')
					),
					"dependency" => array(
						'#page_template' => array( 'blog.php' ),
						'.editor-page-attributes__template select' => array( 'blog.php' ),
					),
					"linked" => 'parent_cat',
					"refresh" => false,
					"hidden" => true,
					"std" => 'post',
					"options" => array(),
					"type" => "select"
					),
				'parent_cat' => array(
					"title" => esc_html__('Category to show', 'parkivia'),
					"desc" => wp_kses_data( __('Select category to show in the blog archive', 'parkivia') ),
					"override" => array(
						'mode' => 'page',
						'section' => esc_html__('Content', 'parkivia')
					),
					"dependency" => array(
						'#page_template' => array( 'blog.php' ),
						'.editor-page-attributes__template select' => array( 'blog.php' ),
					),
					"refresh" => false,
					"hidden" => true,
					"std" => '0',
					"options" => array(),
					"type" => "select"
					),
				'posts_per_page' => array(
					"title" => esc_html__('Posts per page', 'parkivia'),
					"desc" => wp_kses_data( __('How many posts will be displayed on this page', 'parkivia') ),
					"override" => array(
						'mode' => 'page',
						'section' => esc_html__('Content', 'parkivia')
					),
					"dependency" => array(
						'#page_template' => array( 'blog.php' ),
						'.editor-page-attributes__template select' => array( 'blog.php' ),
					),
					"hidden" => true,
					"std" => '',
					"type" => "text"
					),
				"blog_pagination" => array( 
					"title" => esc_html__('Pagination style', 'parkivia'),
					"desc" => wp_kses_data( __('Show Older/Newest posts or Page numbers below the posts list', 'parkivia') ),
					"override" => array(
						'mode' => 'page',
						'section' => esc_html__('Content', 'parkivia')
					),
					"std" => "pages",
					"dependency" => array(
						'#page_template' => array( 'blog.php' ),
						'.editor-page-attributes__template select' => array( 'blog.php' ),
					),
					"options" => array(
						'pages'	=> esc_html__("Page numbers", 'parkivia'),
						'links'	=> esc_html__("Older/Newest", 'parkivia'),
						'more'	=> esc_html__("Load more", 'parkivia'),
						'infinite' => esc_html__("Infinite scroll", 'parkivia')
					),
					"type" => "select"
					),
				'show_filters' => array(
					"title" => esc_html__('Show filters', 'parkivia'),
					"desc" => wp_kses_data( __('Show categories as tabs to filter posts', 'parkivia') ),
					"override" => array(
						'mode' => 'page',
						'section' => esc_html__('Content', 'parkivia')
					),
					"dependency" => array(
						'#page_template' => array( 'blog.php' ),
						'.editor-page-attributes__template select' => array( 'blog.php' ),
						'blog_style' => array('portfolio', 'gallery')
					),
					"hidden" => true,
					"std" => 0,
					"type" => PARKIVIA_THEME_FREE ? "hidden" : "checkbox"
					),
	
				'blog_sidebar_info' => array(
					"title" => esc_html__('Sidebar', 'parkivia'),
					"desc" => '',
					"type" => "info",
					),
				'sidebar_position_blog' => array(
					"title" => esc_html__('Sidebar position', 'parkivia'),
					"desc" => wp_kses_data( __('Select position to show sidebar', 'parkivia') ),
					"std" => 'right',
					"options" => array(),
					"type" => "switch"
					),
				'sidebar_widgets_blog' => array(
					"title" => esc_html__('Sidebar widgets', 'parkivia'),
					"desc" => wp_kses_data( __('Select default widgets to show in the sidebar', 'parkivia') ),
					"dependency" => array(
						'sidebar_position_blog' => array('left', 'right')
					),
					"std" => 'sidebar_widgets',
					"options" => array(),
					"type" => "select"
					),
				'expand_content_blog' => array(
					"title" => esc_html__('Expand content', 'parkivia'),
					"desc" => wp_kses_data( __('Expand the content width if the sidebar is hidden', 'parkivia') ),
					"refresh" => false,
					"std" => 1,
					"type" => "checkbox"
					),
	
	
				'blog_widgets_info' => array(
					"title" => esc_html__('Additional widgets', 'parkivia'),
					"desc" => '',
					"type" => PARKIVIA_THEME_FREE ? "hidden" : "info",
					),
				'widgets_above_page_blog' => array(
					"title" => esc_html__('Widgets at the top of the page', 'parkivia'),
					"desc" => wp_kses_data( __('Select widgets to show at the top of the page (above content and sidebar)', 'parkivia') ),
					"std" => 'hide',
					"options" => array(),
					"type" => PARKIVIA_THEME_FREE ? "hidden" : "select"
					),
				'widgets_above_content_blog' => array(
					"title" => esc_html__('Widgets above the content', 'parkivia'),
					"desc" => wp_kses_data( __('Select widgets to show at the beginning of the content area', 'parkivia') ),
					"std" => 'hide',
					"options" => array(),
					"type" => PARKIVIA_THEME_FREE ? "hidden" : "select"
					),
				'widgets_below_content_blog' => array(
					"title" => esc_html__('Widgets below the content', 'parkivia'),
					"desc" => wp_kses_data( __('Select widgets to show at the ending of the content area', 'parkivia') ),
					"std" => 'hide',
					"options" => array(),
					"type" => PARKIVIA_THEME_FREE ? "hidden" : "select"
					),
				'widgets_below_page_blog' => array(
					"title" => esc_html__('Widgets at the bottom of the page', 'parkivia'),
					"desc" => wp_kses_data( __('Select widgets to show at the bottom of the page (below content and sidebar)', 'parkivia') ),
					"std" => 'hide',
					"options" => array(),
					"type" => PARKIVIA_THEME_FREE ? "hidden" : "select"
					),

				'blog_advanced_info' => array(
					"title" => esc_html__('Advanced settings', 'parkivia'),
					"desc" => '',
					"type" => "info",
					),
				'no_image' => array(
					"title" => esc_html__('Image placeholder', 'parkivia'),
					"desc" => wp_kses_data( __('Select or upload an image used as placeholder for posts without a featured image', 'parkivia') ),
					"std" => '',
					"type" => "image"
					),
				'time_diff_before' => array(
					"title" => esc_html__('Easy Readable Date Format', 'parkivia'),
					"desc" => wp_kses_data( __("For how many days to show the easy-readable date format (e.g. '3 days ago') instead of the standard publication date", 'parkivia') ),
					"std" => 5,
					"type" => "text"
					),
				'sticky_style' => array(
					"title" => esc_html__('Sticky posts style', 'parkivia'),
					"desc" => wp_kses_data( __('Select style of the sticky posts output', 'parkivia') ),
					"std" => 'inherit',
					"options" => array(
						'inherit' => esc_html__('Decorated posts', 'parkivia'),
						'columns' => esc_html__('Mini-cards',	'parkivia')
					),
					"type" => PARKIVIA_THEME_FREE ? "hidden" : "select"
					),
				"blog_animation" => array( 
					"title" => esc_html__('Animation for the posts', 'parkivia'),
					"desc" => wp_kses_data( __('Select animation to show posts in the blog. Attention! Do not use any animation on pages with the "wheel to the anchor" behaviour (like a "Chess 2 columns")!', 'parkivia') ),
					"override" => array(
						'mode' => 'page',
						'section' => esc_html__('Content', 'parkivia')
					),
					"dependency" => array(
						'#page_template' => array( 'blog.php' ),
						'.editor-page-attributes__template select' => array( 'blog.php' ),
					),
					"std" => "none",
					"options" => array(),
					"type" => PARKIVIA_THEME_FREE ? "hidden" : "select"
					),
				'meta_parts' => array(
					"title" => esc_html__('Post meta', 'parkivia'),
					"desc" => wp_kses_data( __("If your blog page is created using the 'Blog archive' page template, set up the 'Post Meta' settings in the 'Theme Options' section of that page. Counters and Share Links are available only if plugin ThemeREX Addons is active", 'parkivia') )
								. '<br>'
								. wp_kses_data( __("<b>Tip:</b> Drag items to change their order.", 'parkivia') ),
					"override" => array(
						'mode' => 'page',
						'section' => esc_html__('Content', 'parkivia')
					),
					"dependency" => array(
						'#page_template' => array( 'blog.php' ),
						'.editor-page-attributes__template select' => array( 'blog.php' ),
					),
					"dir" => 'vertical',
					"sortable" => true,
					"std" => 'categories=0|date=1|counters=1|author=0|share=0|edit=0',
					"options" => array(
						'categories' => esc_html__('Categories', 'parkivia'),
						'date'		 => esc_html__('Post date', 'parkivia'),
						'author'	 => esc_html__('Post author', 'parkivia'),
						'counters'	 => esc_html__('Views, Likes and Comments', 'parkivia'),
						'share'		 => esc_html__('Share links', 'parkivia'),
						'edit'		 => esc_html__('Edit link', 'parkivia')
					),
					"type" => PARKIVIA_THEME_FREE ? "hidden" : "checklist"
				),
				'counters' => array(
					"title" => esc_html__('Views, Likes and Comments', 'parkivia'),
					"desc" => wp_kses_data( __("Likes and Views are available only if ThemeREX Addons is active", 'parkivia') ),
					"override" => array(
						'mode' => 'page',
						'section' => esc_html__('Content', 'parkivia')
					),
					"dependency" => array(
						'#page_template' => array( 'blog.php' ),
						'.editor-page-attributes__template select' => array( 'blog.php' ),
					),
					"dir" => 'vertical',
					"sortable" => true,
					"std" => 'views=0|likes=0|comments=1',
					"options" => array(
						'views' => esc_html__('Views', 'parkivia'),
						'likes' => esc_html__('Likes', 'parkivia'),
						'comments' => esc_html__('Comments', 'parkivia')
					),
					"type" => PARKIVIA_THEME_FREE || !parkivia_exists_trx_addons() ? "hidden" : "checklist"
				),

				
				// Blog - Single posts
				'blog_single' => array(
					"title" => esc_html__('Single posts', 'parkivia'),
					"desc" => wp_kses_data( __('Settings of the single post', 'parkivia') ),
					"type" => "section",
					),
				'hide_featured_on_single' => array(
					"title" => esc_html__('Hide featured image on the single post', 'parkivia'),
					"desc" => wp_kses_data( __("Hide featured image on the single post's pages", 'parkivia') ),
					"override" => array(
						'mode' => 'page,post',
						'section' => esc_html__('Content', 'parkivia')
					),
					"std" => 0,
					"type" => "checkbox"
					),
				'hide_sidebar_on_single' => array(
					"title" => esc_html__('Hide sidebar on the single post', 'parkivia'),
					"desc" => wp_kses_data( __("Hide sidebar on the single post's pages", 'parkivia') ),
					"std" => 0,
					"type" => "checkbox"
					),
				'show_post_meta' => array(
					"title" => esc_html__('Show post meta', 'parkivia'),
					"desc" => wp_kses_data( __("Display block with post's meta: date, categories, counters, etc.", 'parkivia') ),
					"std" => 1,
					"type" => "checkbox"
					),
				'meta_parts_post' => array(
					"title" => esc_html__('Post meta', 'parkivia'),
					"desc" => wp_kses_data( __("Meta parts for single posts. Counters and Share Links are available only if plugin ThemeREX Addons is active", 'parkivia') )
								. '<br>'
								. wp_kses_data( __("<b>Tip:</b> Drag items to change their order.", 'parkivia') ),
					"dependency" => array(
						'show_post_meta' => array(1)
					),
					"dir" => 'vertical',
					"sortable" => true,
					"std" => 'categories=1|date=1|counters=1|author=0|share=0|edit=0',
					"options" => array(
						'categories' => esc_html__('Categories', 'parkivia'),
						'date'		 => esc_html__('Post date', 'parkivia'),
						'author'	 => esc_html__('Post author', 'parkivia'),
						'counters'	 => esc_html__('Views, Likes and Comments', 'parkivia'),
						'share'		 => esc_html__('Share links', 'parkivia'),
						'edit'		 => esc_html__('Edit link', 'parkivia')
					),
					"type" => PARKIVIA_THEME_FREE ? "hidden" : "checklist"
				),
				'counters_post' => array(
					"title" => esc_html__('Views, Likes and Comments', 'parkivia'),
					"desc" => wp_kses_data( __("Likes and Views are available only if plugin ThemeREX Addons is active", 'parkivia') ),
					"dependency" => array(
						'show_post_meta' => array(1)
					),
					"dir" => 'vertical',
					"sortable" => true,
					"std" => 'views=0|likes=0|comments=1',
					"options" => array(
						'views' => esc_html__('Views', 'parkivia'),
						'likes' => esc_html__('Likes', 'parkivia'),
						'comments' => esc_html__('Comments', 'parkivia')
					),
					"type" => PARKIVIA_THEME_FREE || !parkivia_exists_trx_addons() ? "hidden" : "checklist"
				),
				'show_share_links' => array(
					"title" => esc_html__('Show share links', 'parkivia'),
					"desc" => wp_kses_data( __("Display share links on the single post", 'parkivia') ),
					"std" => 1,
					"type" => !parkivia_exists_trx_addons() ? "hidden" : "checkbox"
					),
				'show_author_info' => array(
					"title" => esc_html__('Show author info', 'parkivia'),
					"desc" => wp_kses_data( __("Display block with information about post's author", 'parkivia') ),
					"std" => 1,
					"type" => "checkbox"
					),
				'blog_single_related_info' => array(
					"title" => esc_html__('Related posts', 'parkivia'),
					"desc" => '',
					"type" => "info",
					),
				'show_related_posts' => array(
					"title" => esc_html__('Show related posts', 'parkivia'),
					"desc" => wp_kses_data( __("Show section 'Related posts' on the single post's pages", 'parkivia') ),
					"override" => array(
						'mode' => 'page,post',
						'section' => esc_html__('Content', 'parkivia')
					),
					"std" => 1,
					"type" => "checkbox"
					),
				'related_posts' => array(
					"title" => esc_html__('Related posts', 'parkivia'),
					"desc" => wp_kses_data( __('How many related posts should be displayed in the single post? If 0 - no related posts are shown.', 'parkivia') ),
					"dependency" => array(
						'show_related_posts' => array(1)
					),
					"std" => 2,
					"options" => parkivia_get_list_range(1,9),
					"type" => PARKIVIA_THEME_FREE ? "hidden" : "select"
					),
				'related_columns' => array(
					"title" => esc_html__('Related columns', 'parkivia'),
					"desc" => wp_kses_data( __('How many columns should be used to output related posts in the single page (from 2 to 4)?', 'parkivia') ),
					"dependency" => array(
						'show_related_posts' => array(1)
					),
					"std" => 2,
					"options" => parkivia_get_list_range(1,4),
					"type" => PARKIVIA_THEME_FREE ? "hidden" : "switch"
					),
				'related_style' => array(
					"title" => esc_html__('Related posts style', 'parkivia'),
					"desc" => wp_kses_data( __('Select style of the related posts output', 'parkivia') ),
					"dependency" => array(
						'show_related_posts' => array(1)
					),
					"std" => 2,
					"options" => parkivia_get_list_styles(1,2),
					"type" => PARKIVIA_THEME_FREE ? "hidden" : "switch"
					),
			'blog_end' => array(
				"type" => "panel_end",
				),
			
		
		
			// 'Colors'
			'panel_colors' => array(
				"title" => esc_html__('Colors', 'parkivia'),
				"desc" => '',
				"priority" => 300,
				"type" => "section"
				),

			'color_schemes_info' => array(
				"title" => esc_html__('Color schemes', 'parkivia'),
				"desc" => wp_kses_data( __('Color schemes for various parts of the site. "Inherit" means that this block is used the Site color scheme (the first parameter)', 'parkivia') ),
				"hidden" => $hide_schemes,
				"type" => "info",
				),
			'color_scheme' => array(
				"title" => esc_html__('Site Color Scheme', 'parkivia'),
				"desc" => '',
				"override" => array(
					'mode' => 'page,cpt_team,cpt_services,cpt_dishes,cpt_competitions,cpt_rounds,cpt_matches,cpt_cars,cpt_properties,cpt_courses,cpt_portfolio',
					'section' => esc_html__('Colors', 'parkivia')
				),
				"std" => 'default',
				"options" => array(),
				"refresh" => false,
				"type" => $hide_schemes ? 'hidden' : "switch"
				),
			'header_scheme' => array(
				"title" => esc_html__('Header Color Scheme', 'parkivia'),
				"desc" => '',
				"override" => array(
					'mode' => 'page,cpt_team,cpt_services,cpt_dishes,cpt_competitions,cpt_rounds,cpt_matches,cpt_cars,cpt_properties,cpt_courses,cpt_portfolio',
					'section' => esc_html__('Colors', 'parkivia')
				),
				"std" => 'inherit',
				"options" => array(),
				"refresh" => false,
				"type" => $hide_schemes ? 'hidden' : "switch"
				),
			'menu_scheme' => array(
				"title" => esc_html__('Sidemenu Color Scheme', 'parkivia'),
				"desc" => '',
				"override" => array(
					'mode' => 'page,cpt_team,cpt_services,cpt_dishes,cpt_competitions,cpt_rounds,cpt_matches,cpt_cars,cpt_properties,cpt_courses,cpt_portfolio',
					'section' => esc_html__('Colors', 'parkivia')
				),
				"std" => 'inherit',
				"options" => array(),
				"refresh" => false,
				"type" => $hide_schemes || PARKIVIA_THEME_FREE ? "hidden" : "switch"
				),
			'sidebar_scheme' => array(
				"title" => esc_html__('Sidebar Color Scheme', 'parkivia'),
				"desc" => '',
				"override" => array(
					'mode' => 'page,cpt_team,cpt_services,cpt_dishes,cpt_competitions,cpt_rounds,cpt_matches,cpt_cars,cpt_properties,cpt_courses,cpt_portfolio',
					'section' => esc_html__('Colors', 'parkivia')
				),
				"std" => 'default',
				"options" => array(),
				"refresh" => false,
				"type" => $hide_schemes ? 'hidden' : "switch"
				),
			'footer_scheme' => array(
				"title" => esc_html__('Footer Color Scheme', 'parkivia'),
				"desc" => '',
				"override" => array(
					'mode' => 'page,cpt_team,cpt_services,cpt_dishes,cpt_competitions,cpt_rounds,cpt_matches,cpt_cars,cpt_properties,cpt_courses,cpt_portfolio',
					'section' => esc_html__('Colors', 'parkivia')
				),
				"std" => 'dark',
				"options" => array(),
				"refresh" => false,
				"type" => $hide_schemes ? 'hidden' : "switch"
				),

			'color_scheme_editor_info' => array(
				"title" => esc_html__('Color scheme editor', 'parkivia'),
				"desc" => wp_kses_data(__('Select color scheme to modify. Attention! Only those sections in the site will be changed which this scheme was assigned to', 'parkivia') ),
				"type" => "info",
				),
			'scheme_storage' => array(
				"title" => esc_html__('Color scheme editor', 'parkivia'),
				"desc" => '',
				"std" => '$parkivia_get_scheme_storage',
				"refresh" => false,
				"colorpicker" => "tiny",
				"type" => "scheme_editor"
				),


			// 'Hidden'
			'media_title' => array(
				"title" => esc_html__('Media title', 'parkivia'),
				"desc" => wp_kses_data( __('Used as title for the audio and video item in this post', 'parkivia') ),
				"override" => array(
					'mode' => 'post',
					'section' => esc_html__('Content', 'parkivia')
				),
				"hidden" => true,
				"std" => '',
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "text"
				),
			'media_author' => array(
				"title" => esc_html__('Media author', 'parkivia'),
				"desc" => wp_kses_data( __('Used as author name for the audio and video item in this post', 'parkivia') ),
				"override" => array(
					'mode' => 'post',
					'section' => esc_html__('Content', 'parkivia')
				),
				"hidden" => true,
				"std" => '',
				"type" => PARKIVIA_THEME_FREE ? "hidden" : "text"
				),


			// Internal options.
			// Attention! Don't change any options in the section below!
			// Use huge priority to call render this elements after all options!
			'reset_options' => array(
				"title" => '',
				"desc" => '',
				"std" => '0',
				"priority" => 10000,
				"type" => "hidden",
				),

			'last_option' => array(		// Need to manually call action to include Tiny MCE scripts
				"title" => '',
				"desc" => '',
				"std" => 1,
				"type" => "hidden",
				),

		));


		// Prepare panel 'Fonts'
		// -------------------------------------------------------------
		$fonts = array(
		
			// 'Fonts'
			'fonts' => array(
				"title" => esc_html__('Typography', 'parkivia'),
				"desc" => '',
				"priority" => 200,
				"type" => "panel"
				),

			// Fonts - Load_fonts
			'load_fonts' => array(
				"title" => esc_html__('Load fonts', 'parkivia'),
				"desc" => wp_kses_data( __('Specify fonts to load when theme start. You can use them in the base theme elements: headers, text, menu, links, input fields, etc.', 'parkivia') )
						. '<br>'
						. wp_kses_data( __('Attention! Press "Refresh" button to reload preview area after the all fonts are changed', 'parkivia') ),
				"type" => "section"
				),
			'load_fonts_subset' => array(
				"title" => esc_html__('Google fonts subsets', 'parkivia'),
				"desc" => wp_kses_data( __('Specify comma separated list of the subsets which will be load from Google fonts', 'parkivia') )
						. '<br>'
						. wp_kses_data( __('Available subsets are: latin,latin-ext,cyrillic,cyrillic-ext,greek,greek-ext,vietnamese', 'parkivia') ),
				"class" => "parkivia_column-1_3 parkivia_new_row",
				"refresh" => false,
				"std" => '$parkivia_get_load_fonts_subset',
				"type" => "text"
				)
		);

		for ($i=1; $i<=parkivia_get_theme_setting('max_load_fonts'); $i++) {
			if (parkivia_get_value_gp('page') != 'theme_options') {
				$fonts["load_fonts-{$i}-info"] = array(
					// Translators: Add font's number - 'Font 1', 'Font 2', etc
					"title" => esc_html(sprintf(__('Font %s', 'parkivia'), $i)),
					"desc" => '',
					"type" => "info",
					);
			}
			$fonts["load_fonts-{$i}-name"] = array(
				"title" => esc_html__('Font name', 'parkivia'),
				"desc" => '',
				"class" => "parkivia_column-1_3 parkivia_new_row",
				"refresh" => false,
				"std" => '$parkivia_get_load_fonts_option',
				"type" => "text"
				);
			$fonts["load_fonts-{$i}-family"] = array(
				"title" => esc_html__('Font family', 'parkivia'),
				"desc" => $i==1 
							? wp_kses_data( __('Select font family to use it if font above is not available', 'parkivia') )
							: '',
				"class" => "parkivia_column-1_3",
				"refresh" => false,
				"std" => '$parkivia_get_load_fonts_option',
				"options" => array(
					'inherit' => esc_html__("Inherit", 'parkivia'),
					'serif' => esc_html__('serif', 'parkivia'),
					'sans-serif' => esc_html__('sans-serif', 'parkivia'),
					'monospace' => esc_html__('monospace', 'parkivia'),
					'cursive' => esc_html__('cursive', 'parkivia'),
					'fantasy' => esc_html__('fantasy', 'parkivia')
				),
				"type" => "select"
				);
			$fonts["load_fonts-{$i}-styles"] = array(
				"title" => esc_html__('Font styles', 'parkivia'),
				"desc" => $i==1 
							? wp_kses_data( __('Font styles used only for the Google fonts. This is a comma separated list of the font weight and styles. For example: 400,400italic,700', 'parkivia') )
								. '<br>'
								. wp_kses_data( __('Attention! Each weight and style increase download size! Specify only used weights and styles.', 'parkivia') )
							: '',
				"class" => "parkivia_column-1_3",
				"refresh" => false,
				"std" => '$parkivia_get_load_fonts_option',
				"type" => "text"
				);
		}
		$fonts['load_fonts_end'] = array(
			"type" => "section_end"
			);

		// Fonts - H1..6, P, Info, Menu, etc.
		$theme_fonts = parkivia_get_theme_fonts();
		foreach ($theme_fonts as $tag=>$v) {
			$fonts["{$tag}_section"] = array(
				"title" => !empty($v['title']) 
								? $v['title'] 
								// Translators: Add tag's name to make title 'H1 settings', 'P settings', etc.
								: esc_html(sprintf(__('%s settings', 'parkivia'), $tag)),
				"desc" => !empty($v['description']) 
								? $v['description'] 
								// Translators: Add tag's name to make description
								: wp_kses_post( sprintf(__('Font settings of the "%s" tag.', 'parkivia'), $tag) ),
				"type" => "section",
				);
	
			foreach ($v as $css_prop=>$css_value) {
				if (in_array($css_prop, array('title', 'description'))) continue;
				$options = '';
				$type = 'text';
				$load_order = 1;
				$title = ucfirst(str_replace('-', ' ', $css_prop));
				if ($css_prop == 'font-family') {
					$type = 'select';
					$options = array();
					$load_order = 2;		// Load this option's value after all options are loaded (use option 'load_fonts' to build fonts list)
				} else if ($css_prop == 'font-weight') {
					$type = 'select';
					$options = array(
						'inherit' => esc_html__("Inherit", 'parkivia'),
						'100' => esc_html__('100 (Light)', 'parkivia'), 
						'200' => esc_html__('200 (Light)', 'parkivia'), 
						'300' => esc_html__('300 (Thin)',  'parkivia'),
						'400' => esc_html__('400 (Normal)', 'parkivia'),
						'500' => esc_html__('500 (Semibold)', 'parkivia'),
						'600' => esc_html__('600 (Semibold)', 'parkivia'),
						'700' => esc_html__('700 (Bold)', 'parkivia'),
						'800' => esc_html__('800 (Black)', 'parkivia'),
						'900' => esc_html__('900 (Black)', 'parkivia')
					);
				} else if ($css_prop == 'font-style') {
					$type = 'select';
					$options = array(
						'inherit' => esc_html__("Inherit", 'parkivia'),
						'normal' => esc_html__('Normal', 'parkivia'), 
						'italic' => esc_html__('Italic', 'parkivia')
					);
				} else if ($css_prop == 'text-decoration') {
					$type = 'select';
					$options = array(
						'inherit' => esc_html__("Inherit", 'parkivia'),
						'none' => esc_html__('None', 'parkivia'), 
						'underline' => esc_html__('Underline', 'parkivia'),
						'overline' => esc_html__('Overline', 'parkivia'),
						'line-through' => esc_html__('Line-through', 'parkivia')
					);
				} else if ($css_prop == 'text-transform') {
					$type = 'select';
					$options = array(
						'inherit' => esc_html__("Inherit", 'parkivia'),
						'none' => esc_html__('None', 'parkivia'), 
						'uppercase' => esc_html__('Uppercase', 'parkivia'),
						'lowercase' => esc_html__('Lowercase', 'parkivia'),
						'capitalize' => esc_html__('Capitalize', 'parkivia')
					);
				}
				$fonts["{$tag}_{$css_prop}"] = array(
					"title" => $title,
					"desc" => '',
					"class" => "parkivia_column-1_5",
					"refresh" => false,
					"load_order" => $load_order,
					"std" => '$parkivia_get_theme_fonts_option',
					"options" => $options,
					"type" => $type
				);
			}
			
			$fonts["{$tag}_section_end"] = array(
				"type" => "section_end"
				);
		}

		$fonts['fonts_end'] = array(
			"type" => "panel_end"
			);

		// Add fonts parameters to Theme Options
		parkivia_storage_set_array_before('options', 'panel_colors', $fonts);


		// Add Header Video if WP version < 4.7
		// -----------------------------------------------------
		if (!function_exists('get_header_video_url')) {
			parkivia_storage_set_array_after('options', 'header_image_override', 'header_video', array(
				"title" => esc_html__('Header video', 'parkivia'),
				"desc" => wp_kses_data( __("Select video to use it as background for the header", 'parkivia') ),
				"override" => array(
					'mode' => 'page',
					'section' => esc_html__('Header', 'parkivia')
				),
				"std" => '',
				"type" => "video"
				)
			);
		}


		// Add option 'logo' if WP version < 4.5
		// or 'custom_logo' if current page is 'Theme Options'
		// ------------------------------------------------------
		if (!function_exists('the_custom_logo') || (isset($_REQUEST['page']) && $_REQUEST['page']=='theme_options')) {
			parkivia_storage_set_array_before('options', 'logo_retina', function_exists('the_custom_logo') ? 'custom_logo' : 'logo', array(
				"title" => esc_html__('Logo', 'parkivia'),
				"desc" => wp_kses_data( __('Select or upload the site logo', 'parkivia') ),
				"class" => "parkivia_column-1_2 parkivia_new_row",
				"priority" => 60,
				"std" => '',
				"type" => "image"
				)
			);
		}

	}
}


// Returns a list of options that can be overridden for CPT
if (!function_exists('parkivia_options_get_list_cpt_options')) {
	function parkivia_options_get_list_cpt_options($cpt, $title='') {
		if (empty($title)) $title = ucfirst($cpt);
		return array(
					"header_info_{$cpt}" => array(
						"title" => esc_html__('Header', 'parkivia'),
						"desc" => '',
						"type" => "info",
						),
					"header_type_{$cpt}" => array(
						"title" => esc_html__('Header style', 'parkivia'),
						"desc" => wp_kses_data( __('Choose whether to use the default header or header Layouts (available only if the ThemeREX Addons is activated)', 'parkivia') ),
						"std" => 'inherit',
						"options" => parkivia_get_list_header_footer_types(true),
						"type" => PARKIVIA_THEME_FREE ? "hidden" : "switch"
						),
					"header_style_{$cpt}" => array(
						"title" => esc_html__('Select custom layout', 'parkivia'),
						// Translators: Add CPT name to the description
						"desc" => wp_kses_data( sprintf(__('Select custom layout to display the site header on the %s pages', 'parkivia'), $title) ),
						"dependency" => array(
							"header_type_{$cpt}" => array('custom')
						),
						"std" => 'inherit',
						"options" => array(),
						"type" => PARKIVIA_THEME_FREE ? "hidden" : "select"
						),
					"header_position_{$cpt}" => array(
						"title" => esc_html__('Header position', 'parkivia'),
						// Translators: Add CPT name to the description
						"desc" => wp_kses_data( sprintf(__('Select position to display the site header on the %s pages', 'parkivia'), $title) ),
						"std" => 'inherit',
						"options" => array(),
						"type" => PARKIVIA_THEME_FREE ? "hidden" : "switch"
						),
					"header_image_override_{$cpt}" => array(
						"title" => esc_html__('Header image override', 'parkivia'),
						"desc" => wp_kses_data( __("Allow override the header image with the post's featured image", 'parkivia') ),
						"std" => 'inherit',
						"options" => array(
							'inherit' => esc_html__('Inherit', 'parkivia'),
							1 => esc_html__('Yes', 'parkivia'),
							0 => esc_html__('No', 'parkivia'),
						),
						"type" => PARKIVIA_THEME_FREE ? "hidden" : "switch"
						),
					"header_widgets_{$cpt}" => array(
						"title" => esc_html__('Header widgets', 'parkivia'),
						// Translators: Add CPT name to the description
						"desc" => wp_kses_data( sprintf(__('Select set of widgets to show in the header on the %s pages', 'parkivia'), $title) ),
						"std" => 'hide',
						"options" => array(),
						"type" => "select"
						),
						
					"sidebar_info_{$cpt}" => array(
						"title" => esc_html__('Sidebar', 'parkivia'),
						"desc" => '',
						"type" => "info",
						),
					"sidebar_position_{$cpt}" => array(
						"title" => esc_html__('Sidebar position', 'parkivia'),
						// Translators: Add CPT name to the description
						"desc" => wp_kses_data( sprintf(__('Select position to show sidebar on the %s pages', 'parkivia'), $title) ),
						"std" => 'left',
						"options" => array(),
						"type" => "switch"
						),
					"sidebar_widgets_{$cpt}" => array(
						"title" => esc_html__('Sidebar widgets', 'parkivia'),
						// Translators: Add CPT name to the description
						"desc" => wp_kses_data( sprintf(__('Select sidebar to show on the %s pages', 'parkivia'), $title) ),
						"dependency" => array(
							"sidebar_position_{$cpt}" => array('left', 'right')
						),
						"std" => 'hide',
						"options" => array(),
						"type" => "select"
						),
					"hide_sidebar_on_single_{$cpt}" => array(
						"title" => esc_html__('Hide sidebar on the single pages', 'parkivia'),
						"desc" => wp_kses_data( __("Hide sidebar on the single page", 'parkivia') ),
						"std" => 'inherit',
						"options" => array(
							'inherit' => esc_html__('Inherit', 'parkivia'),
							1 => esc_html__('Hide', 'parkivia'),
							0 => esc_html__('Show', 'parkivia'),
						),
						"type" => "switch"
						),
						
					"footer_info_{$cpt}" => array(
						"title" => esc_html__('Footer', 'parkivia'),
						"desc" => '',
						"type" => "info",
						),
					"footer_type_{$cpt}" => array(
						"title" => esc_html__('Footer style', 'parkivia'),
						"desc" => wp_kses_data( __('Choose whether to use the default footer or footer Layouts (available only if the ThemeREX Addons is activated)', 'parkivia') ),
						"std" => 'inherit',
						"options" => parkivia_get_list_header_footer_types(true),
						"type" => PARKIVIA_THEME_FREE ? "hidden" : "switch"
						),
					"footer_style_{$cpt}" => array(
						"title" => esc_html__('Select custom layout', 'parkivia'),
						"desc" => wp_kses_data( __('Select custom layout to display the site footer', 'parkivia') ),
						"std" => 'inherit',
						"dependency" => array(
							"footer_type_{$cpt}" => array('custom')
						),
						"options" => array(),
						"type" => PARKIVIA_THEME_FREE ? "hidden" : "select"
						),
					"footer_widgets_{$cpt}" => array(
						"title" => esc_html__('Footer widgets', 'parkivia'),
						"desc" => wp_kses_data( __('Select set of widgets to show in the footer', 'parkivia') ),
						"dependency" => array(
							"footer_type_{$cpt}" => array('default')
						),
						"std" => 'footer_widgets',
						"options" => array(),
						"type" => "select"
						),
					"footer_columns_{$cpt}" => array(
						"title" => esc_html__('Footer columns', 'parkivia'),
						"desc" => wp_kses_data( __('Select number columns to show widgets in the footer. If 0 - autodetect by the widgets count', 'parkivia') ),
						"dependency" => array(
							"footer_type_{$cpt}" => array('default'),
							"footer_widgets_{$cpt}" => array('^hide')
						),
						"std" => 0,
						"options" => parkivia_get_list_range(0,6),
						"type" => "select"
						),
					"footer_wide_{$cpt}" => array(
						"title" => esc_html__('Footer fullwidth', 'parkivia'),
						"desc" => wp_kses_data( __('Do you want to stretch the footer to the entire window width?', 'parkivia') ),
						"dependency" => array(
							"footer_type_{$cpt}" => array('default')
						),
						"std" => 0,
						"type" => "checkbox"
						),
						
					"widgets_info_{$cpt}" => array(
						"title" => esc_html__('Additional panels', 'parkivia'),
						"desc" => '',
						"type" => PARKIVIA_THEME_FREE ? "hidden" : "info",
						),
					"widgets_above_page_{$cpt}" => array(
						"title" => esc_html__('Widgets at the top of the page', 'parkivia'),
						"desc" => wp_kses_data( __('Select widgets to show at the top of the page (above content and sidebar)', 'parkivia') ),
						"std" => 'hide',
						"options" => array(),
						"type" => PARKIVIA_THEME_FREE ? "hidden" : "select"
						),
					"widgets_above_content_{$cpt}" => array(
						"title" => esc_html__('Widgets above the content', 'parkivia'),
						"desc" => wp_kses_data( __('Select widgets to show at the beginning of the content area', 'parkivia') ),
						"std" => 'hide',
						"options" => array(),
						"type" => PARKIVIA_THEME_FREE ? "hidden" : "select"
						),
					"widgets_below_content_{$cpt}" => array(
						"title" => esc_html__('Widgets below the content', 'parkivia'),
						"desc" => wp_kses_data( __('Select widgets to show at the ending of the content area', 'parkivia') ),
						"std" => 'hide',
						"options" => array(),
						"type" => PARKIVIA_THEME_FREE ? "hidden" : "select"
						),
					"widgets_below_page_{$cpt}" => array(
						"title" => esc_html__('Widgets at the bottom of the page', 'parkivia'),
						"desc" => wp_kses_data( __('Select widgets to show at the bottom of the page (below content and sidebar)', 'parkivia') ),
						"std" => 'hide',
						"options" => array(),
						"type" => PARKIVIA_THEME_FREE ? "hidden" : "select"
						)
					);
	}
}


// Return lists with choises when its need in the admin mode
if (!function_exists('parkivia_options_get_list_choises')) {
	add_filter('parkivia_filter_options_get_list_choises', 'parkivia_options_get_list_choises', 10, 2);
	function parkivia_options_get_list_choises($list, $id) {
		if (is_array($list) && count($list)==0) {
			if (strpos($id, 'header_style')===0)
				$list = parkivia_get_list_header_styles(strpos($id, 'header_style_')===0);
			else if (strpos($id, 'header_position')===0)
				$list = parkivia_get_list_header_positions(strpos($id, 'header_position_')===0);
			else if (strpos($id, 'header_widgets')===0)
				$list = parkivia_get_list_sidebars(strpos($id, 'header_widgets_')===0, true);
			else if (strpos($id, '_scheme') > 0)
				$list = parkivia_get_list_schemes($id!='color_scheme');
			else if (strpos($id, 'sidebar_widgets')===0)
				$list = parkivia_get_list_sidebars(strpos($id, 'sidebar_widgets_')===0, true);
			else if (strpos($id, 'sidebar_position')===0)
				$list = parkivia_get_list_sidebars_positions(strpos($id, 'sidebar_position_')===0);
			else if (strpos($id, 'widgets_above_page')===0)
				$list = parkivia_get_list_sidebars(strpos($id, 'widgets_above_page_')===0, true);
			else if (strpos($id, 'widgets_above_content')===0)
				$list = parkivia_get_list_sidebars(strpos($id, 'widgets_above_content_')===0, true);
			else if (strpos($id, 'widgets_below_page')===0)
				$list = parkivia_get_list_sidebars(strpos($id, 'widgets_below_page_')===0, true);
			else if (strpos($id, 'widgets_below_content')===0)
				$list = parkivia_get_list_sidebars(strpos($id, 'widgets_below_content_')===0, true);
			else if (strpos($id, 'footer_style')===0)
				$list = parkivia_get_list_footer_styles(strpos($id, 'footer_style_')===0);
			else if (strpos($id, 'footer_widgets')===0)
				$list = parkivia_get_list_sidebars(strpos($id, 'footer_widgets_')===0, true);
			else if (strpos($id, 'blog_style')===0)
				$list = parkivia_get_list_blog_styles(strpos($id, 'blog_style_')===0);
			else if (strpos($id, 'post_type')===0)
				$list = parkivia_get_list_posts_types();
			else if (strpos($id, 'parent_cat')===0)
				$list = parkivia_array_merge(array(0 => esc_html__('- Select category -', 'parkivia')), parkivia_get_list_categories());
			else if (strpos($id, 'blog_animation')===0)
				$list = parkivia_get_list_animations_in();
			else if ($id == 'color_scheme_editor')
				$list = parkivia_get_list_schemes();
			else if (strpos($id, '_font-family') > 0)
				$list = parkivia_get_list_load_fonts(true);
		}
		return $list;
	}
}
?>