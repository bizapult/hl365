<?php
// Add plugin-specific colors and fonts to the custom CSS
if (!function_exists('parkivia_trx_addons_get_mycss')) {
	add_filter('parkivia_filter_get_css', 'parkivia_trx_addons_get_mycss', 10, 4);
	function parkivia_trx_addons_get_mycss($css, $args) {

        if (isset($css['fonts']) && isset($args['fonts'])) {
            $fonts = $args['fonts'];
            $css['fonts'] .= <<<CSS
            
            .sc_services_list .sc_services_item_featured_left .sc_services_item_title,
            .sc_testimonials_item_content:before,
            .sc_services .sc_services_item_number,
            body .body_wrap .BigWhiteText2,
            body .body_wrap .BigWhiteText,
            body .cq-titlebar,
            .sc_services_default .sc_services_item:before,
            .sc_services_light .sc_services_item_title,
            .nav-links-old,
            .sc_price_item_price,
            .widget_calendar caption,
            table th,
            blockquote,
            blockquote:before,
            .trx_addons_dropcap {
                {$fonts['h1_font-family']}
            }

            body .body_wrap .SmallWhiteText,
            body .body_wrap .SliderForm,
            .mejs-controls .mejs-time * {
                {$fonts['p_font-family']}
            }

CSS;
        }

        if (isset($css['colors']) && isset($args['colors'])) {
            $colors = $args['colors'];
            $css['colors'] .= <<<CSS
            
            /* Inline colors */
            .trx_addons_accent,
            .trx_addons_accent_big,
            .trx_addons_accent > a,
            .trx_addons_accent > * {
                color: {$colors['text_link']};
            }
            .trx_addons_accent_hovered,
            .trx_addons_accent_hovered,
            .trx_addons_accent_hovered > a,
            .trx_addons_accent_hovered > * {
                color: {$colors['text_hover']};
            }
            .trx_addons_accent_bg {
                background-color: {$colors['text_link']};
                color: {$colors['inverse_link']};
            }

            
            /* Tooltip */
            .trx_addons_tooltip {
                color: {$colors['text_dark']};
                
            }
            .trx_addons_tooltip:before {
                background-color: {$colors['text_dark']};
                color: {$colors['bg_color']};
            }
            .trx_addons_tooltip:after {
                border-top-color: {$colors['text_dark']};
            }
            
            
            /* Dropcaps */
            .trx_addons_dropcap_style_1 {
                background: linear-gradient(-21deg, {$colors['text_hover2']} 0%, {$colors['text_link2']} 100%);
                color: {$colors['bg_color']};
            }
            .trx_addons_dropcap_style_2 {
                background-color: {$colors['bg_color_0']};
                border-color: {$colors['text_link']};
                color: {$colors['text_link']};
            }
            
            /* Blockqoute */
            blockquote {
                color: {$colors['inverse_link']};
                background: linear-gradient(-21deg, {$colors['text_hover2']} 0%, {$colors['text_link2']} 100%);
            }
            blockquote cite a,
            blockquote > a, blockquote > p > a,
            blockquote > cite, blockquote > p > cite {
                color: {$colors['inverse_link']};
            }
            blockquote cite a:hover,
            blockquote > a, blockquote > p > a:hover {
                color: {$colors['text_dark']};
            }
            blockquote:before {
                color: {$colors['inverse_link']};
            }
            
            /* Images */
            figure figcaption,
            .wp-caption .wp-caption-text,
            .wp-caption .wp-caption-dd,
            .wp-caption-overlay .wp-caption .wp-caption-text,
            .wp-caption-overlay .wp-caption .wp-caption-dd {
                color: {$colors['inverse_link']};
                background-color: {$colors['text_link_09']};
            }
            
            
            /* Lists */
            ul[class*="trx_addons_list"].trx_addons_list_without li{
                color: {$colors['alter_text']};
            }
            
          
            
            /* Table */
            table th {
                color: {$colors['extra_dark']};
                background-color: {$colors['text_dark']};
            }
            table th, table th + th, table td + th  {
                border-color: {$colors['extra_bd_color']};
            }
            table tr:last-child td,
            table td, table th + td, table td + td, table td {
                color: {$colors['text']};
                border-color: {$colors['bd_color']};
            }
            table > tbody > tr:nth-child(2n+1) > td {
                background-color: {$colors['alter_bg_color']};
            }
            table > tbody > tr:nth-child(2n) > td {
                background-color: {$colors['bg_color']};
            }
            th a {
                color: {$colors['text_link']};
            }

            /* Main menu */
            .sc_layouts_menu_nav>li>a {
                color: {$colors['text']} !important;
            }
            .sc_layouts_menu_nav>li>a:hover,
            .sc_layouts_menu_nav>li.sfHover>a,
            .sc_layouts_menu_nav>li.current-menu-item>a,
            .sc_layouts_menu_nav>li.current-menu-parent>a,
            .sc_layouts_menu_nav>li.current-menu-ancestor>a {
                color: {$colors['text_dark']} !important;
            }
            /* Dropdown menu */
            .sc_layouts_menu_nav>li>ul:before,
            .sc_layouts_menu_nav>li ul {
                background-color: {$colors['text_dark']};
            }
            .sc_layouts_menu_popup .sc_layouts_menu_nav>li>a,
            .sc_layouts_menu_nav>li li>a {
                color: {$colors['text']} !important;
            }
            .sc_layouts_menu_nav>li ul li>a {
                color: {$colors['text_light']} !important;
            }
            .sc_layouts_menu_nav>li li>a:hover:after,
            .sc_layouts_menu_popup .sc_layouts_menu_nav>li>a:hover,
            .sc_layouts_menu_popup .sc_layouts_menu_nav>li.sfHover>a,
            .sc_layouts_menu_nav>li li>a:hover,
            .sc_layouts_menu_nav>li li.sfHover>a,
            .sc_layouts_menu_nav>li li.current-menu-item>a,
            .sc_layouts_menu_nav>li li.current-menu-parent>a,
            .sc_layouts_menu_nav>li li.current-menu-ancestor>a {
                color: {$colors['bg_color']} !important;
                background-color: {$colors['bg_color_0']};
            }
            
            /* Breadcrumbs */
            .sc_layouts_title_caption {
                color: {$colors['text_dark']};
            }
            .sc_layouts_title_breadcrumbs,
            .sc_layouts_title_breadcrumbs a {
                color: {$colors['text_dark']} !important;
            }
            .breadcrumbs_item.current{
                color: {$colors['text_dark']} !important;
            }
            .sc_layouts_title_breadcrumbs a:hover {
                color: {$colors['text_dark_07']} !important;
            }
            
            /* Slider */
            .slider_container .slider_pagination_wrap .swiper-pagination-bullet,
            .slider_outer .slider_pagination_wrap .swiper-pagination-bullet,
            .swiper-pagination-custom .swiper-pagination-button {
                border-color: {$colors['text_dark']};
                background-color: {$colors['text_dark']};
            }
            .swiper-pagination-custom .swiper-pagination-button.swiper-pagination-button-active,
            .slider_container .slider_pagination_wrap .swiper-pagination-bullet.swiper-pagination-bullet-active,
            .slider_outer .slider_pagination_wrap .swiper-pagination-bullet.swiper-pagination-bullet-active,
            .slider_container .slider_pagination_wrap .swiper-pagination-bullet:hover,
            .slider_outer .slider_pagination_wrap .swiper-pagination-bullet:hover {
                border-color: {$colors['text_link']};
                background-color: {$colors['text_link']};
            }
            
            .sc_testimonials .slider_container .slider_pagination_wrap .swiper-pagination-bullet,
            .sc_testimonials .slider_outer .slider_pagination_wrap .swiper-pagination-bullet,
            .sc_testimonials .swiper-pagination-custom .swiper-pagination-button {
                border-color: {$colors['bg_color']};
                background-color: {$colors['bg_color']};
            }
            .sc_testimonials .swiper-pagination-custom .swiper-pagination-button.swiper-pagination-button-active,
            .sc_testimonials .slider_container .slider_pagination_wrap .swiper-pagination-bullet.swiper-pagination-bullet-active,
            .sc_testimonials .slider_outer .slider_pagination_wrap .swiper-pagination-bullet.swiper-pagination-bullet-active,
            .sc_testimonials .slider_container .slider_pagination_wrap .swiper-pagination-bullet:hover,
            .sc_testimonials .slider_outer .slider_pagination_wrap .swiper-pagination-bullet:hover {
                border-color: {$colors['text_link']};
                background-color: {$colors['text_link']};
            }
            
            .sc_slider_controls .slider_controls_wrap > a,
            .slider_container.slider_controls_side .slider_controls_wrap > a,
            .slider_outer_controls_side .slider_controls_wrap > a {
                color: {$colors['inverse_link']};
                background-color: {$colors['text_hover']};
                border-color: {$colors['text_hover']};
            }
            .sc_slider_controls .slider_controls_wrap > a:hover,
            .slider_container.slider_controls_side .slider_controls_wrap > a:hover,
            .slider_outer_controls_side .slider_controls_wrap > a:hover {
                color: {$colors['inverse_link']};
                background-color: {$colors['text_link']};
                border-color: {$colors['text_link']};
            }
            
            
            /* Layouts */
            .sc_layouts_logo .logo_text {
                color: {$colors['text_dark']};
            }
            

            /* Shortcodes */
            .sc_skills_pie.sc_skills_compact_off .sc_skills_total {
                color: {$colors['text_dark']};
            }
            .sc_skills_pie.sc_skills_compact_off .sc_skills_item_title {
                color: {$colors['text']};
            }
            .sc_countdown .sc_countdown_label{
                color: {$colors['text_dark']};
                background: {$colors['bg_color_0']};
            }
            .sc_countdown_default .sc_countdown_digits span {
                color: {$colors['text_hover']};
                background: {$colors['bg_color_0']};
            }
            
            /* Audio */
            .trx_addons_audio_player.without_cover,
            .format-audio .post_featured.without_thumb .post_audio {
                background: {$colors['text_link3']} !important;
            }
            .format-audio .post_featured.without_thumb .mejs-controls,
            .trx_addons_audio_player.without_cover .mejs-controls {
                background: {$colors['text_link3']};
            }
            .format-audio .post_featured.without_thumb .mejs-container,
            .trx_addons_audio_player.without_cover .mejs-container {
                background: {$colors['text_link3']};
            }
            .format-audio .post_featured.without_thumb .post_audio_author,
            .trx_addons_audio_player.without_cover .audio_author,
            .format-audio .post_featured.without_thumb .mejs-controls .mejs-time,
            .trx_addons_audio_player.without_cover .mejs-time {
                color: {$colors['text']};
            }
            .trx_addons_audio_player.without_cover,
            .format-audio .post_featured.without_thumb .post_audio {
                background-color: {$colors['alter_bg_color']};
                border-color: {$colors['alter_bg_color']};
            }
            .mejs-controls .mejs-button > button {
                background: {$colors['bg_color_0']} !important;
                color: {$colors['inverse_link']}!important;
            }
            .mejs-controls .mejs-button > button:hover {
                background: {$colors['bg_color_0']} !important;
                color: {$colors['inverse_link_06']}!important;
            }
            .mejs-controls .mejs-time-rail .mejs-time-total,
            .mejs-controls .mejs-time-rail .mejs-time-loaded,
            .mejs-controls .mejs-volume-slider .mejs-volume-total,
            .mejs-controls .mejs-horizontal-volume-slider .mejs-horizontal-volume-total {
                background: {$colors['inverse_link_02']};
            }
            .mejs-controls .mejs-time-rail .mejs-time-hovered,
            .mejs-controls .mejs-time-rail .mejs-time-current,
            .mejs-controls .mejs-volume-slider .mejs-volume-current,
            .mejs-controls .mejs-horizontal-volume-slider .mejs-horizontal-volume-current {
                background: {$colors['inverse_link']};
            }
            .mejs-controls .mejs-time *,
            .trx_addons_audio_player.without_cover .audio_author,
            .format-audio .post_featured.without_thumb .post_audio_author,
            .trx_addons_audio_player.without_cover .audio_caption,
            .format-audio .post_featured.without_thumb .post_audio_title {
                color: {$colors['inverse_link']};
            }
            .format-audio .post_featured.without_thumb .post_audio:after, 
            .trx_addons_audio_player.without_cover:after {
                background-color: {$colors['inverse_link']};
            }
            
            /* Video */
            .trx_addons_video_player.with_cover .video_hover,
            .format-video .post_featured.with_thumb .post_video_hover {
                color: {$colors['text_link']};
                background-color: {$colors['inverse_link']};
            }
            .trx_addons_video_player.with_cover .video_hover:hover,
            .format-video .post_featured.with_thumb .post_video_hover:hover {
                color: {$colors['inverse_link']};
                background-color: {$colors['text_link']};
            }
            
            
            /* Price */
            .sc_price_item {
                color: {$colors['text']};
                background-color: {$colors['bg_color']};
                border-color: {$colors['input_bd_color']};
            }
            .sc_price_item:hover {
                color: {$colors['text']};
                background-color: {$colors['bg_color']};
                border-color: {$colors['text_link']};
            }
            .sc_price_item .sc_price_item_price,
            .price-top {
                background-color: {$colors['bg_color_0']};
            }
            .sc_price_item:hover .sc_price_item_title,
            .sc_price_item .sc_price_item_title,
            .sc_price_item .sc_price_item_title a {
                color: {$colors['text_dark']};
            }
            .sc_price_item:hover .sc_price_item_title a {
                color: {$colors['text_dark']};
            }
            .sc_price_item .sc_price_item_price {
                color: {$colors['text_link']};
            }
            .sc_price_item_light .sc_price_item_link:hover .sc_price_item_title{
                color: {$colors['text_link']};
            }
            .sc_price_light .sc_price_item:hover{
                border-color: {$colors['text_link']};
            }
            .sc_price_item .sc_price_item_description{
                color: {$colors['text']};
            }
            .sc_price_item .sc_price_item_details {
                color: {$colors['text']};
            }
                         
            /* Widget Searcch Dark */
            .scheme_self.sidebar .sidebar_inner .widget.widget_search{
                background: linear-gradient(-21deg, {$colors['extra_bg_color']} 0%, {$colors['text_dark']} 100%);
                color: {$colors['inverse_link']};
            }
            .scheme_self.sidebar .sidebar_inner .widget.widget_search .widget_title{
                color: {$colors['inverse_link']};
            }
            .scheme_self.sidebar .sidebar_inner .widget.widget_search .widget_title:before{
                background: {$colors['text_link']} !important;
            }
            .sidebar[class*="scheme_"] .widget.widget_search form:after {
                color: {$colors['inverse_link']};
            }
            .sidebar[class*="scheme_"] .widget.widget_search form:hover:after {
                color: {$colors['inverse_link']};
            }
            .sidebar[class*="scheme_"] .widget.widget_search input[type="search"] {
                background-color: {$colors['bg_color_0']};
                color: {$colors['inverse_link']};
                border-color: {$colors['inverse_link_03']};
            }
            .sidebar[class*="scheme_"] .widget.widget_search input[type="search"]::-webkit-input-placeholder {
                color: {$colors['inverse_link_04']};
            }
            .sidebar[class*="scheme_"] .widget.widget_search input[type="search"]::-moz-placeholder {
                color: {$colors['inverse_link_04']};
            }
            
            /* Other */
            .sidebar[class*="scheme_"] .widget  .widget_title:after  {
                background: linear-gradient(-21deg, {$colors['text_hover2']} 0%, {$colors['text_link2']} 100%);
            }
            .comment-author-link,  comment-author-link a {
                color: {$colors['text_dark']};
            }
            .related_wrap .related_item_style_2 .post_header,
            .post-body {
                border-color: {$colors['bd_color']};
            }
            .post_counters_item.post_counters_comments:before,
            .post_meta_item.post_date:before {
                color: {$colors['text_link']};
            }
            .post_layout_excerpt.sticky .post-body {
                border-color: {$colors['text_hover']};
                background: {$colors['text_hover']};
                color: {$colors['inverse_link']};
            }
            .post_layout_excerpt.sticky .post-body .post_counters_item.post_counters_comments:before,
            .post_layout_excerpt.sticky .post-body .post_meta_item.post_date:before,
            .post_layout_excerpt.sticky .post-body a {
                color: {$colors['inverse_link']};
            }
            .post_layout_excerpt.sticky .post-body a:hover {
                color: {$colors['inverse_link_06']};
            }
            .post_layout_excerpt.sticky .post-body .sc_button {
                background: {$colors['inverse_link']} !important;
                color: {$colors['text_hover']} !important;
            }
            .post_layout_excerpt.sticky .post-body .sc_button:hover {
                background: {$colors['inverse_link']} !important;
                color: {$colors['text_dark']} !important;
            }
            .nav-links-old a {
                color: {$colors['text_dark']};
            }
            .nav-links-old a:hover {
                color: {$colors['text_link']};
            }
            .post_item_single .post_content .post_meta .post_share .social_item:nth-child(4n+1) .social_icon {
                background-color: {$colors['text_dark']};
                color: {$colors['bg_color']} !important;
            }
            .post_item_single .post_content .post_meta .post_share .social_item:nth-child(4n+2) .social_icon {
                background-color: {$colors['text_hover']};
            }
            .post_item_single .post_content .post_meta .post_share .social_item:nth-child(4n+4) .social_icon {
                background-color: {$colors['text']};
            }
            .post_item_single .post_content .post_meta .post_share .social_item:nth-child(4n+2):hover .social_icon {
                background-color: {$colors['text_link']} !important;
            }
            h2.sc_item_title.sc_item_title_style_default:not(.sc_item_title_tag):before {
                background: linear-gradient(-21deg, {$colors['text_hover2']} 0%, {$colors['text_link2']} 100%);
            }
            
            /* Price */
            .sc_price_inverse .sc_price_item {
                color: {$colors['inverse_link']};
                background-color: {$colors['text_dark']};
                border-color: {$colors['text_dark']};
            }
            .sc_price_inverse .sc_price_item:hover {
                color: {$colors['inverse_link']};
                background-color: {$colors['text_dark']};
                border-color: {$colors['text_dark']};
            }
            .sc_price_inverse .sc_price_item .sc_price_item_price,
            .sc_price_inverse .price-top {
                background-color: {$colors['bg_color_0']};
            }
            .sc_price_inverse .sc_price_item:hover .sc_price_item_title,
            .sc_price_inverse .sc_price_item .sc_price_item_title,
            .sc_price_inverse .sc_price_item .sc_price_item_title a {
                color: {$colors['inverse_link']};
            }
            .sc_price_inverse .sc_price_item:hover .sc_price_item_title a {
                color: {$colors['inverse_link']};
            }
            .sc_price_inverse .sc_price_item_price {
                border-color: {$colors['inverse_link']};
            }
            .sc_price_inverse .sc_price_item .sc_button_simple,
            .sc_price_inverse .sc_price_item .sc_price_item_price {
                color: {$colors['text_link']};
            }
            .sc_price_inverse .sc_price_item .sc_price_item_description{
                color: {$colors['inverse_link']};
            }
            .sc_price_inverse .sc_price_item:hover .sc_price_item_price,
            .sc_price_inverse .sc_price_item:hover .sc_button_simple,
            .sc_price_inverse .sc_price_item .sc_price_item_details {
                color: {$colors['inverse_link']} !important;
            }
            .sc_price_inverse .sc_price_item.with_image .sc_price_item_mask {
                 background-color: {$colors['extra_bg_hover']};
            }
            .sc_price_inverse .sc_price_item.with_image:hover .sc_price_item_mask {
                background-color: {$colors['text_link']};
            }
            .sc_price_light .sc_price_item {
                border-color: {$colors['bd_color']};
            }
            .sc_services_light .sc_services_item_icon {
                border-color: {$colors['bd_color']};
            }
            .sc_services_list .sc_services_item_icon {
                border-color: {$colors['bd_color']};
                background-color: {$colors['bg_color']};
            }
            .sc_services_list .sc_services_item_title a {
                color: {$colors['text']};
            }
            .sc_promo .sc_promo_descr strong a:hover,
            .sc_services_list .sc_services_item_title a:hover {
                color: {$colors['text_link']};
            }
            .sc_promo .sc_promo_descr strong a,
            .scheme_self.vc_row .sc_item_descr,
            .scheme_self.wpb_column .sc_item_descr {
                color: {$colors['text_dark']};
            }
            .scheme_self.vc_row .sc_item_descr.sc_title_descr{
                color: {$colors['text']};
            }
            .scheme_self.vc_row h2.sc_item_title.sc_item_title_style_default:not(.sc_item_title_tag):before {
                background: {$colors['text_dark']};
            }
            .sc_promo.sc_promo_size_normal .sc_promo_text_inner {
                border-color: {$colors['text_link']};
            }
            .sc_promo .sc_promo_descr {
                color: {$colors['text']};
            }
            body .booked-modal .booked-form button:hover, body .booked-modal button.cancel:hover, 
            .booked-form input[type=submit].button-primary:hover,
            .booked-calendar-wrap .booked-appt-list .timeslot .timeslot-people button:hover {
                background: {$colors['text_hover']} !important;
                color: {$colors['inverse_link']} !important;
            }
            .wpcf7-form .columns_wrap{
                background: {$colors['bg_color']};  
            }
            .wpcf7-form .columns_wrap:not(.contactF) input:not(.wpcf7-submit),
            .wpcf7-form .columns_wrap:not(.contactF) .select_container:after,
            .wpcf7-form .columns_wrap:not(.contactF) .select_container select,
            .wpcf7-form .columns_wrap:not(.contactF) input::-webkit-input-placeholder {
                color: {$colors['text_dark']};
            }
            .wpcf7-form .columns_wrap > [class*="column-"] + [class*="column-"]:before {
                background: {$colors['bd_color']}; 
            }
            .rev_slider .wpcf7-list-item-label,
			.revslider-initialised .wpcf7-list-item-label{
                color: {$colors['inverse_text']};
            }
            .sc_float_center .wpcf7-list-item-label{
                color: {$colors['inverse_text']};
            }
            .sc_services_default .sc_services_item {
                background: {$colors['bg_color']};  
                border-color: {$colors['bd_color']};
            }
            .sc_services_default .sc_services_item_icon {
                color: {$colors['text_link']};
            }
            .sc_services_default.sc_services_featured_top .with_number.without_content{
                background: {$colors['alter_bg_color']};
            }
            .sc_services_default .sc_services_item:hover .sc_services_item_icon {
                color: {$colors['bg_color']};
            } 
            .caldera-grid .control-label,
            .cff-calculated-field label,
            .cff-date-field > label {
                color: {$colors['text_dark']};
            }
            .booki input[type="radio"] + span:before,
            .caldera-grid input[type="radio"] + span:before,
            #fbuilder input[type="radio"] + span:before {
                color: {$colors['text_link']};
                border-color: {$colors['bd_color']};
            }
            .caldera-grid .cf-toggle-group-buttons .btn.btn-success {
                color: {$colors['inverse_link']};
                border-color: {$colors['text_link2']};
                background: linear-gradient(-21deg, {$colors['text_hover2']} 0%, {$colors['text_link2']} 100%);
            }
            .caldera-grid .cf-toggle-group-buttons .btn {
                color: {$colors['text']};
                border-color: {$colors['bd_color']};
            }
            #fbuilder .section_break {
                border-top-color: {$colors['bd_color']};
            }
            .booki .input-group:before,
            .dateclass > div:before,
            .cff-date-field .dfield:before {
                background: linear-gradient(-21deg, {$colors['text_hover2']} 0%, {$colors['text_link2']} 100%);
                color: {$colors['bg_color']};
            }
            .sc_button.sc_button_apl:not(.sc_button_simple):not(.sc_button_bordered):not(.sc_button_bg_image) {
                color: {$colors['text_dark']};
                border-color: {$colors['bd_color']};
                background: {$colors['alter_bg_color']};  
            }
            .sc_button.sc_button_apl:not(.sc_button_simple):not(.sc_button_bordered):not(.sc_button_bg_image):hover {
                color: {$colors['text_link']};
                border-color: {$colors['text_link']};
                background: {$colors['bg_color']};  
            }
            .sc_button.sc_button_apl:not(.sc_button_simple):not(.sc_button_bordered):not(.sc_button_bg_image) .sc_button_icon {
                color: {$colors['text_dark']};
            }
            .sc_button.sc_button_apl:not(.sc_button_simple):not(.sc_button_bordered):not(.sc_button_bg_image):hover .sc_button_icon {
                color: {$colors['text_link']};
            }
            .sc_icons .sc_icons_item_title {
                color: {$colors['text_dark']};
            }
            .cq-stepcard-item p,
            .sc_icons_item_description {
                color: {$colors['text']};
            }
            .cq-stepcard-mint .cq-stepcard-stepcontainer .cq-stepcard-step,
            .cq-stepcard-mint .cq-stepcard-cardbar {
                background: {$colors['text_link']};  
            }
            .sc_icons_inv .sc_icons_item {
                background: {$colors['inverse_link']};
            }
            .sc_icons_inv .sc_icons_item_title {
                color: {$colors['inverse_dark']};
            }
            
            .cq-infobox.gray .cq-titlebar, .cq-infobox.gray .cq-titlecontainer {
                background: {$colors['text_link']};
            }
            .cq-infobox.gray {
                border-color: {$colors['text_link']};
            }
            .cq-infobox.gray:before {
                border-color: {$colors['text_link']} transparent;
            }
            .cq-highlight-container:nth-child(3n+1) .cq-highlight {
                background: {$colors['text_hover']};
            }
            .cq-highlight-container:nth-child(3n+2) .cq-highlight {
                background: {$colors['text_link']};
            }
            .cq-highlight-container:nth-child(3n+3) .cq-highlight {
                background: {$colors['text_dark']};
            }
            body .cq-highlight-container .cq-highlight-label {
                color: {$colors['text_dark']};
            }
            .sc_recent_news .post_item .post_header {
                border-color: {$colors['bd_color']};
            }
            .sc_services .sc_services_item_number {
                color: {$colors['inverse_light']};
            }
            .sc_services .sc_services_item:hover .sc_services_item_number {
                color: {$colors['text_link']};
            }
            .sc_services_default .sc_services_item:hover {
                border-color: {$colors['text_link']};
            }
            .sc_services_default .sc_services_item:hover .sc_services_item_title a{
                color: {$colors['text_link']};
            }
            .sc_services_default .with_icon.sc_services_item:hover{
                background: {$colors['text_link']};
            }
            .sc_services_default .with_icon.sc_services_item:hover .sc_services_item_title a,
            .sc_services_default .with_icon.sc_services_item:hover .sc_services_item_content{
                color: {$colors['bg_color']};
            }
            .vc_col-sm-12 .sc_services_light .sc_services_item_featured_left:hover  {
                border-color: {$colors['bd_color']};
                background: {$colors['bg_color']};
            }
            .sc_testimonials_item:before {
                background: {$colors['bg_color']};
            }
            .sc_testimonials_item_author_subtitle,
            .sc_testimonials_item_content {
                color: {$colors['text']};
            }
            .sc_testimonials_item_author_title {
                color: {$colors['text_dark']};
            }
            .BigWhiteText2:before {
                background: {$colors['inverse_link']};
            }
            .sc_services_list.sc_services_featured_left .sc_services_item {
                border-color: {$colors['bd_color']};
                background: {$colors['bg_color']};
            }
            .sc_services_list .sc_services_item_title a {
                color: {$colors['text_dark']};
            }
            .sc_services_list .sc_services_item_featured_left:hover {
                border-color: {$colors['text_link2']};
                background: {$colors['text_link2']};
                color: {$colors['inverse_link']};
            }
            .sc_services_list .sc_services_item_featured_left .sc_services_item_icon {
                color: {$colors['text_link2']};
                background: {$colors['bg_color_0']};
            }
            .sc_services_list .sc_services_item_featured_left:hover .sc_services_item_title a,
            .sc_services_list .sc_services_item_featured_left:hover .sc_services_item_icon {
                color: {$colors['inverse_link']};
                background: {$colors['bg_color_0']};
            }
            .booki .nav-tabs>li>a:hover,
            .booki .form-horizontal .control-label {
                color: {$colors['text_dark']};
            }
            .booki .badge {
                background: {$colors['text_link2']};
                color: {$colors['inverse_link']};
            }
          

CSS;
		}

		return $css;
	}
}
?>