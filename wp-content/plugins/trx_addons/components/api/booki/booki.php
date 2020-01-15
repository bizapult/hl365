<?php
// Check if plugin installed and activated
if ( !function_exists( 'trx_addons_exists_booki' ) ) {
    function trx_addons_exists_booki() {
        return class_exists('Booki');
    }
}

// Set plugin's specific importer options
if ( !function_exists( 'trx_addons_booki_importer_set_options' ) ) {
    if (is_admin()) add_filter( 'trx_addons_filter_importer_options',    'trx_addons_booki_importer_set_options' );
    function trx_addons_booki_importer_set_options($options=array()) {
        if ( trx_addons_exists_booki() && in_array('booki', $options['required_plugins']) ) {
            $options['additional_options'][]    = 'booki%';                    // Add slugs to export options for this plugin
            $options['additional_options'][]    = 'widget_booki%';                    // Add slugs to export options for this plugin
            if (is_array($options['files']) && count($options['files']) > 0) {
                foreach ($options['files'] as $k => $v) {
                    $options['files'][$k]['file_with_booki'] = str_replace('name.ext', 'booki.txt', $v['file_with_']);
                }
            }
        }
        return $options;
    }
}

// Export posts
if ( !function_exists( 'trx_addons_booki_importer_export' ) ) {
    if (is_admin()) add_action( 'trx_addons_action_importer_export',    'trx_addons_booki_importer_export', 10, 1 );
    function trx_addons_booki_importer_export($importer) {
        if ( trx_addons_exists_booki() && in_array('booki', $importer->options['required_plugins']) ) {
            trx_addons_fpc($importer->export_file_dir('booki.txt'), serialize( array(
                    "booki_calendar"               => $importer->export_dump("booki_calendar"),
//                    "booki_calendar_day"        => $importer->export_dump("booki_calendar_day"),
//                    "booki_cascading_item"     => $importer->export_dump("booki_cascading_item"),
//                    "booki_cascading_list"   => $importer->export_dump("booki_cascading_list"),
//                    "booki_coupons"        => $importer->export_dump("booki_coupons"),
//                    "booki_event_log"            => $importer->export_dump("booki_event_log"),
                    "booki_form_element"       => $importer->export_dump("booki_form_element"),
//                    "booki_gcal"       => $importer->export_dump("booki_gcal"),
//                    "booki_gcal_events"       => $importer->export_dump("booki_gcal_events"),
//                    "booki_gcal_projects"       => $importer->export_dump("booki_gcal_projects"),
                    "booki_optional"       => $importer->export_dump("booki_optional"),
//                    "booki_order"       => $importer->export_dump("booki_order"),
//                    "booki_order_cascading_item"       => $importer->export_dump("booki_order_cascading_item"),
//                    "booki_order_days"       => $importer->export_dump("booki_order_days"),
//                    "booki_order_form_elements"       => $importer->export_dump("booki_order_form_elements"),
//                    "booki_order_optionals"       => $importer->export_dump("booki_order_optionals"),
//                    "booki_order_quantity_element"       => $importer->export_dump("booki_order_quantity_element"),
                    "booki_project"       => $importer->export_dump("booki_project"),
//                    "booki_quantity_element"       => $importer->export_dump("booki_quantity_element"),
//                    "booki_quantity_element_calendar"       => $importer->export_dump("booki_quantity_element_calendar"),
//                    "booki_quantity_element_calendarday"       => $importer->export_dump("booki_quantity_element_calendarday"),
//                    "booki_quantity_element_item"       => $importer->export_dump("booki_quantity_element_item"),
//                    "booki_reminders"       => $importer->export_dump("booki_reminders"),
//                    "booki_roles"       => $importer->export_dump("booki_roles"),
                    "booki_settings"       => $importer->export_dump("booki_settings"),
//                    "booki_trashed"       => $importer->export_dump("booki_trashed"),
//                    "booki_trashed_project"       => $importer->export_dump("booki_trashed_project")
                ) )
            );
        }
    }
}


// Display exported data in the fields
if ( !function_exists( 'trx_addons_booki_importer_export_fields' ) ) {
    if (is_admin()) add_action( 'trx_addons_action_importer_export_fields',    'trx_addons_booki_importer_export_fields', 10, 1 );
    function trx_addons_booki_importer_export_fields($importer) {
        if ( trx_addons_exists_booki() && in_array('booki', $importer->options['required_plugins']) ) {
            $importer->show_exporter_fields(array(
                    'slug'    => 'booki',
                    'title' => esc_html__('Booki', 'trx_addons')
                )
            );
        }
    }
}

// Check plugin in the required plugins
if ( !function_exists( 'trx_addons_booki_importer_required_plugins' ) ) {
    if (is_admin()) add_filter( 'trx_addons_filter_importer_required_plugins', 'trx_addons_booki_importer_required_plugins', 10, 2 );
    function trx_addons_booki_importer_required_plugins($not_installed='', $list='') {
        if (strpos($list, 'booki')!==false && !trx_addons_exists_booki() )
            $not_installed .= '<br>' . esc_html__('Booki', 'trx_addons');
        return $not_installed;
    }
}


// Add checkbox to the one-click importer
if ( !function_exists( 'trx_addons_booki_importer_show_params' ) ) {
    if (is_admin()) add_action( 'trx_addons_action_importer_params',    'trx_addons_booki_importer_show_params', 10, 1 );
    function trx_addons_booki_importer_show_params($importer) {
        if ( trx_addons_exists_booki() && in_array('booki', $importer->options['required_plugins']) ) {
            $importer->show_importer_params(array(
                'slug' => 'booki',
                'title' => esc_html__('Import Booki', 'trx_addons'),
                'part' => 1
            ));
        }
    }
}


// Clear tables
if ( !function_exists( 'trx_addons_booki_importer_clear_tables' ) ) {
    if (is_admin()) {
        add_action( 'trx_addons_action_importer_clear_tables',	'trx_addons_booki_importer_clear_tables', 10, 2 );
    }
    function trx_addons_booki_importer_clear_tables($importer, $clear_tables) {
        if (trx_addons_exists_booki() && in_array('booki', $importer->options['required_plugins'])) {
            if (strpos($clear_tables, 'booki')!==false) {
                if ($importer->options['debug']) dfl(__('Clear Booki tables', 'trx_addons'));
                global $wpdb;
                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "booki_calendar");
                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "booki_calendar".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
//                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "booki_calendar_day");
//                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "booki_calendar_day".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
//                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "booki_cascading_item");
//                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "booki_cascading_item".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
//                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "booki_cascading_list");
//                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "booki_cascading_list".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
//                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "booki_coupons");
//                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "booki_coupons".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
//                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "booki_event_log");
//                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "booki_event_log".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "booki_form_element");
                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "booki_form_element".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
//                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "booki_gcal");
//                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "booki_gcal".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
//                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "booki_gcal_events");
//                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "booki_gcal_events".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
//                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "booki_gcal_projects");
//                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "booki_gcal_projects".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "booki_optional");
                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "booki_optional".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
//                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "booki_order");
//                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "booki_order".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
//                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "booki_order_cascading_item");
//                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "booki_order_cascading_item".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
//                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "booki_order_days");
//                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "booki_order_days".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
//                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "booki_order_form_elements");
//                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "booki_order_form_elements".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
//                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "booki_order_optionals");
//                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "booki_order_optionals".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
//                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "booki_order_quantity_element");
//                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "booki_order_quantity_element".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "booki_project");
                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "booki_project".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
//                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "booki_quantity_element");
//                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "booki_quantity_element".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
//                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "booki_quantity_element_calendar");
//                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "booki_quantity_element_calendar".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
//                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "booki_quantity_element_calendarday");
//                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "booki_quantity_element_calendarday".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
//                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "booki_quantity_element_item");
//                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "booki_quantity_element_item".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
//                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "booki_reminders");
//                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "booki_reminders".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
//                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "booki_roles");
//                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "booki_roles".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "booki_settings");
                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "booki_settings".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
//                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "booki_trashed");
//                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "booki_trashed".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
//                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "booki_trashed_project");
//                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "booki_trashed_project".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );

            }
        }
    }
}

// Import posts
if ( !function_exists( 'trx_addons_booki_importer_import' ) ) {
    if (is_admin()) add_action( 'trx_addons_action_importer_import',    'trx_addons_booki_importer_import', 10, 2 );
    function trx_addons_booki_importer_import($importer, $action) {
        if ( trx_addons_exists_booki() && in_array('booki', $importer->options['required_plugins']) ) {
            if ( $action == 'import_booki' ) {
                $importer->response['start_from_id'] = 0;
                $importer->import_dump('booki', esc_html__('Booki meta', 'trx_addons'));
            }
        }
    }
}


?>