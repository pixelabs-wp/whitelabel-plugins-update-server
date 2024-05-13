<?php

require_once WP_THUMBS_UPDATE_PLUGIN_DIR . 'templates/admin-dashboard.php';
require_once WP_THUMBS_UPDATE_PLUGIN_DIR . 'templates/update-history.php';

// Hook the admin dashboard page into the WordPress admin menu
function wp_thumbs_update_admin_menu()
{
    add_menu_page(
        'WPThumbs Admin Update',
        'WPThumbs Plugins',
        'manage_options',
        'wp-thumbs-admin-update',
        'wp_thumbs_update_admin_dashboard_page',
        'dashicons-admin-plugins' // You can change the icon
    );

    add_submenu_page(
        'wp-thumbs-admin-update',
        'Plugin Updates',
        'Plugin Updates',
        'manage_options',
        'wp-thumbs-update-history',
        'wp_thumbs_update_update_history_page'
    );

    add_submenu_page(
        'wp-thumbs-admin-update',
        'WPThumbs Authentication',
        'WPThumbs Authentication',
        'manage_options',
        'wp-thumbs-admin-apis',
        'api_auth_settings_page'
    );
}

add_action('admin_menu', 'wp_thumbs_update_admin_menu');


function create_plugin_registry_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'plugin_registry';

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        plugin_name VARCHAR(255) NOT NULL,
        plugin_slug VARCHAR(255) NOT NULL,
        version VARCHAR(20) NOT NULL,
        plugin_uri VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
    );";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function create_api_keys_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'authorized_api_keys';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        api_key varchar(100) NOT NULL,
        email varchar(100) NOT NULL,
        date_created DATETIME NOT NULL,
        status INT,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
