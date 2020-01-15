<?php
// Check if plugin installed and activated
if ( !function_exists( 'trx_addons_exists_caldera_forms' ) ) {
    function trx_addons_exists_caldera_forms() {
        return class_exists('Caldera_Forms_Widget');
    }
}

// Set plugin's specific importer options
if ( !function_exists( 'trx_addons_caldera_forms_importer_set_options' ) ) {
    if (is_admin()) add_filter( 'trx_addons_filter_importer_options',    'trx_addons_caldera_forms_importer_set_options' );
    function trx_addons_caldera_forms_importer_set_options($options=array()) {
        if ( trx_addons_exists_caldera_forms() && in_array('caldera-forms', $options['required_plugins']) ) {
            $options['additional_options'][]    = 'CF_%';                    // Add slugs to export options for this plugin
            $options['additional_options'][]    = '_caldera%';                    // Add slugs to export options for this plugin
            $options['additional_options'][]    = 'cf_%';                    // Add slugs to export options for this plugin
            $options['additional_options'][]    = '_cf_%';                    // Add slugs to export options for this plugin
            $options['additional_options'][]    = 'fs_%';                    // Add slugs to export options for this plugin
            $options['additional_options'][]    = 'widget_caldera%';                    // Add slugs to export options for this plugin
            if (is_array($options['files']) && count($options['files']) > 0) {
                foreach ($options['files'] as $k => $v) {
                    $options['files'][$k]['file_with_caldera-forms'] = str_replace('name.ext', 'caldera-forms.txt', $v['file_with_']);
                }
            }
        }
        return $options;
    }
}

// Export posts
if ( !function_exists( 'trx_addons_caldera_forms_importer_export' ) ) {
    if (is_admin()) add_action( 'trx_addons_action_importer_export',    'trx_addons_caldera_forms_importer_export', 10, 1 );
    function trx_addons_caldera_forms_importer_export($importer) {
        if ( trx_addons_exists_caldera_forms() && in_array('caldera-forms', $importer->options['required_plugins']) ) {
            trx_addons_fpc($importer->export_file_dir('caldera-forms.txt'), serialize( array(
                    "cf_forms"               => $importer->export_dump("cf_forms"),
                    "cf_form_entries"        => $importer->export_dump("cf_form_entries"),
                    "cf_form_entry_meta"     => $importer->export_dump("cf_form_entry_meta"),
                    "cf_form_entry_values"   => $importer->export_dump("cf_form_entry_values"),
                    "cf_pro_messages"        => $importer->export_dump("cf_pro_messages"),
                    "cf_tracking"            => $importer->export_dump("cf_tracking"),
                    "cf_tracking_meta"       => $importer->export_dump("cf_tracking_meta")
                ) )
            );
        }
    }
}


// Display exported data in the fields
if ( !function_exists( 'trx_addons_caldera_forms_importer_export_fields' ) ) {
    if (is_admin()) add_action( 'trx_addons_action_importer_export_fields',    'trx_addons_caldera_forms_importer_export_fields', 10, 1 );
    function trx_addons_caldera_forms_importer_export_fields($importer) {
        if ( trx_addons_exists_caldera_forms() && in_array('caldera-forms', $importer->options['required_plugins']) ) {
            $importer->show_exporter_fields(array(
                    'slug'    => 'caldera-forms',
                    'title' => esc_html__('Caldera Forms', 'trx_addons')
                )
            );
        }
    }
}

// Check plugin in the required plugins
if ( !function_exists( 'trx_addons_caldera_forms_importer_required_plugins' ) ) {
    if (is_admin()) add_filter( 'trx_addons_filter_importer_required_plugins', 'trx_addons_caldera_forms_importer_required_plugins', 10, 2 );
    function trx_addons_caldera_forms_importer_required_plugins($not_installed='', $list='') {
        if (strpos($list, 'caldera-forms')!==false && !trx_addons_exists_caldera_forms() )
            $not_installed .= '<br>' . esc_html__('Caldera Forms', 'trx_addons');
        return $not_installed;
    }
}


// Add checkbox to the one-click importer
if ( !function_exists( 'trx_addons_caldera_forms_importer_show_params' ) ) {
    if (is_admin()) add_action( 'trx_addons_action_importer_params',    'trx_addons_caldera_forms_importer_show_params', 10, 1 );
    function trx_addons_caldera_forms_importer_show_params($importer) {
        if ( trx_addons_exists_caldera_forms() && in_array('caldera-forms', $importer->options['required_plugins']) ) {
            $importer->show_importer_params(array(
                'slug' => 'caldera-forms',
                'title' => esc_html__('Import Caldera Forms', 'trx_addons'),
                'part' => 1
            ));
        }
    }
}


// Clear tables
if ( !function_exists( 'trx_addons_caldera_forms_importer_clear_tables' ) ) {
    if (is_admin()) {
        add_action( 'trx_addons_action_importer_clear_tables',	'trx_addons_caldera_forms_importer_clear_tables', 10, 2 );
    }
    function trx_addons_caldera_forms_importer_clear_tables($importer, $clear_tables) {
        if (trx_addons_exists_caldera_forms() && in_array('caldera-forms', $importer->options['required_plugins'])) {
            if (strpos($clear_tables, 'caldera-forms')!==false) {
                if ($importer->options['debug']) dfl(__('Clear Caldera Forms tables', 'trx_addons'));
                global $wpdb;
                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "cf_forms");
                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "cf_forms".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "cf_form_entries");
                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "cf_form_entries".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "cf_form_entry_meta");
                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "cf_form_entry_meta".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "cf_form_entry_values");
                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "cf_form_entry_values".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "cf_pro_messages");
                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "cf_pro_messages".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "cf_tracking");
                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "cf_tracking".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );
                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "cf_tracking_meta");
                if ( is_wp_error( $res ) ) dfl( __( 'Failed truncate table "cf_tracking_meta".', 'trx_addons' ) . ' ' . ($res->get_error_message()) );

            }
        }
    }
}

// Import posts
if ( !function_exists( 'trx_addons_caldera_forms_importer_import' ) ) {
    if (is_admin()) add_action( 'trx_addons_action_importer_import',    'trx_addons_caldera_forms_importer_import', 10, 2 );
    function trx_addons_caldera_forms_importer_import($importer, $action) {
        if ( trx_addons_exists_caldera_forms() && in_array('caldera-forms', $importer->options['required_plugins']) ) {
            if ( $action == 'import_caldera-forms' ) {
                $importer->response['start_from_id'] = 0;
                $importer->import_dump('caldera-forms', esc_html__('Caldera Forms meta', 'trx_addons'));
            }
        }
    }
}


?>