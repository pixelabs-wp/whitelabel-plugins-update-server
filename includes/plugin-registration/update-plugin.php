<?php

/**
 * Summary.
 *
 * Description.
 *
 * @since Version 3 digits
 */



function create_plugin_updates_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'plugin_updates';

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        plugin_id INT NOT NULL,
        new_version VARCHAR(20) NOT NULL,
        updated_plugin_url VARCHAR(255) NOT NULL,
        update_date DATETIME NOT NULL,
        PRIMARY KEY (id)
    );";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
