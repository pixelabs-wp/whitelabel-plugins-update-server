<?php

/**
 * Database.
 *
 * @since Version 1.0.0
 */

function register_plugin($plugin_name, $plugin_slug, $version, $plugin_uri)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'plugin_registry';

    $wpdb->insert(
        $table_name,
        array(
            'plugin_name' => $plugin_name,
            'plugin_slug' => $plugin_slug,
            'version' => $version,
            'plugin_uri' => $plugin_uri,
        )
    );
}

function create_plugin_update($plugin_id, $new_version, $updated_plugin_url)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'plugin_updates';

    $wpdb->insert(
        $table_name,
        array(
            'plugin_id' => $plugin_id,
            'new_version' => $new_version,
            'updated_plugin_url' => $updated_plugin_url,
            'update_date' => current_time('mysql'),
        )
    );
}

function delete_plugin($plugin_id)
{
    global $wpdb;
    $table_registry = $wpdb->prefix . 'plugin_registry';
    $table_updates = $wpdb->prefix . 'plugin_updates';

    // Delete the plugin from the registry
    $wpdb->delete($table_registry, array('id' => $plugin_id));

    // Delete update history for the plugin
    $wpdb->delete($table_updates, array('plugin_id' => $plugin_id));
}

function get_registered_plugins_with_updates()
{
    global $wpdb;
    $table_registry = $wpdb->prefix . 'plugin_registry';
    $table_updates = $wpdb->prefix . 'plugin_updates';

    $query = $wpdb->prepare(
        "SELECT pr.*, pu.*
        FROM $table_registry AS pr
        LEFT JOIN $table_updates AS pu ON pr.id = pu.plugin_id"
    );

    $results = $wpdb->get_results($query);

    return $results;
}

function get_registered_plugins_with_latest_updates()
{
    global $wpdb;
    $table_registry = $wpdb->prefix . 'plugin_registry';
    $table_updates = $wpdb->prefix . 'plugin_updates';

    $subquery = $wpdb->prepare(
        "(SELECT pu1.plugin_id, MAX(pu1.update_date) AS latest_update_date
        FROM $table_updates AS pu1
        GROUP BY pu1.plugin_id)"
    );

    $query = $wpdb->prepare(
        "SELECT pr.*, pu.plugin_id, pu.new_version, pu.updated_plugin_url, pu.update_date
        FROM $table_registry AS pr
        LEFT JOIN $subquery AS latest_update
        ON pr.id = latest_update.plugin_id
        LEFT JOIN $table_updates AS pu
        ON latest_update.plugin_id = pu.plugin_id AND latest_update.latest_update_date = pu.update_date"
    );

    $results = $wpdb->get_results($query);

    return $results;
}

function get_plugin_details_and_updates($plugin_id)
{
    global $wpdb;
    $table_registry = $wpdb->prefix . 'plugin_registry';
    $table_updates = $wpdb->prefix . 'plugin_updates';

    $query = $wpdb->prepare(
        "SELECT pr.id AS pr_id, pu.id AS pu_id, pr.*, pu.plugin_id, pu.new_version, pu.updated_plugin_url, pu.update_date
        FROM $table_registry AS pr
        LEFT JOIN $table_updates AS pu
        ON pr.id = pu.plugin_id
        WHERE pr.id = %d
        ORDER BY pu.update_date DESC", // Sort by update_date in descending order
        $plugin_id
    );

    $results = $wpdb->get_results($query);

    return $results;
}

function rollback_to_update($update_id)
{
    global $wpdb;
    $table_updates = $wpdb->prefix . 'plugin_updates';

    // Get the current date and time
    $current_date = current_time('mysql');

    // Update the update_date for the specified update_id
    $wpdb->update(
        $table_updates,
        array('update_date' => $current_date),
        array('id' => $update_id),
        array('%s'),
        array('%d')
    );
}
add_action('wp_ajax_rollback_update', 'rollback_update_callback');

function rollback_update_callback()
{
    $update_id = isset($_POST['update_id']) ? intval($_POST['update_id']) : 0;

    if ($update_id > 0) {
        // Call the rollback_to_update function
        rollback_to_update($update_id);
        wp_send_json_success('Update rolled back successfully.');
    } else {
        wp_send_json_error('Invalid update_id.');
    }

    wp_die(); // Required to terminate AJAX processing.
}

function create_api_auth($api_key, $email = "")
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'authorized_api_keys';

    $wpdb->insert(
        $table_name,
        array(
            'api_key' => $api_key,
            'email' => $email,
            'status' => 1,
            'date_created' => current_time('mysql'),
        )
    );
}

function get_api_auth()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'authorized_api_keys';

    $api_keys = $wpdb->get_results("SELECT * FROM $table_name");
    return $api_keys;
}

function toggle_api_access($api_key)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'authorized_api_keys';

    $current_status = $wpdb->get_var(
        $wpdb->prepare("SELECT status FROM $table_name WHERE api_key = %s", $api_key)
    );

    if ($current_status === '1') {
        $new_status = '0'; // Revoke access
    } else {
        $new_status = '1'; // Enable access
    }

    $wpdb->update(
        $table_name,
        array('status' => $new_status),
        array('api_key' => $api_key)
    );
}

function toggle_api_access_callback()
{
    if (isset($_POST['api_key'])) {
        $api_key = sanitize_text_field($_POST['api_key']);
        toggle_api_access($api_key);
    }
    wp_die(); // Terminate script execution.
}
add_action('wp_ajax_toggle_api_access', 'toggle_api_access_callback');


// Add an AJAX action for deleting a plugin
add_action("wp_ajax_delete_plugin", "delete_plugin_callback");

// Callback function to delete a plugin
function delete_plugin_callback()
{
    // Check if the user is logged in and has the necessary permissions
    if (current_user_can("manage_options")) {
        // Get the plugin ID from the AJAX request
        $plugin_id = isset($_POST["plugin_id"]) ? intval($_POST["plugin_id"]) : 0;

        if ($plugin_id > 0) {
            // Add your code here to delete the plugin from the database
            // You can use $wpdb to perform the deletion
            // Example:
            global $wpdb;
            $table_name = $wpdb->prefix . 'plugin_registry';
            $wpdb->delete($table_name, array("id" => $plugin_id));

            // Return a success message
            echo "success";
        } else {
            // Return an error message
            echo "error";
        }
    } else {
        // Return an error message if the user doesn't have permission
        echo "permission_error";
    }

    // Always exit to prevent extra output
    wp_die();
}
