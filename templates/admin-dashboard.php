<?php

/**
 * Admin Dashboard Template.
 *
 * @since Version 1.0.0
 */


// Define a callback function to render the Admin Dashboard page
function wp_thumbs_update_admin_dashboard_page()
{

    if (isset($_POST['submit-register'])) {
        // Retrieve form data and sanitize it
        $plugin_name = sanitize_text_field($_POST['plugin-name']);
        $plugin_slug = sanitize_text_field($_POST['plugin-slug']);
        $plugin_version = sanitize_text_field($_POST['plugin-version']);
        $plugin_uri = esc_url_raw($_POST['plugin-uri']); // Ensure the URL is sanitized

        // Validate the data (you can add your own validation logic here)

        // Call the register_plugin function to save the registered plugin to the database
        register_plugin($plugin_name, $plugin_slug, $plugin_version, $plugin_uri);

        // Show a success message or perform any other actions
        echo '<div class="updated"><p>Plugin registered successfully!</p></div>';
    }


    if (isset($_POST['submit-update'])) {
        // Retrieve form data and sanitize it
        $selected_plugin_id = intval($_POST['update-plugin-name']); // Assuming you have a dropdown for selecting the plugin
        $new_version = sanitize_text_field($_POST['new-version']);
        $updated_plugin_url = $_POST['updated-plugin-url'];

        // Validation (add your own validation logic here)
        if (empty($selected_plugin_id) || empty($new_version) || empty($updated_plugin_url)) {
            // Handle validation errors, e.g., display an error message and return.
        }

        // Use the create_plugin_update function to insert the update
        create_plugin_update($selected_plugin_id, $new_version, $updated_plugin_url);

        // Show a success message or perform any other actions
        echo '<div class="updated"><p>Plugin update published successfully!</p></div>';
    }




?>
    <div class="wrap">
        <h2>WPThumbs Admin Update - Register Plugins</h2>

        <!-- Plugin Registration Form -->
        <!-- Plugin Registration Form -->
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="plugin-name">Plugin Name</label>
                    </th>
                    <td>
                        <input type="text" name="plugin-name" id="plugin-name" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="plugin-slug">Plugin Slug</label>
                    </th>
                    <td>
                        <input type="text" name="plugin-slug" id="plugin-slug" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="plugin-version">Plugin Current Version</label>
                    </th>
                    <td>
                        <input type="text" name="plugin-version" id="plugin-version" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="plugin-uri">Plugin URI</label>
                    </th>
                    <td>
                        <input type="url" name="plugin-uri" id="plugin-uri" class="regular-text">
                    </td>
                </tr>

            </table>
            <p class="submit">
                <input type="submit" name="submit-register" id="submit-register" class="button button-primary" value="Register Plugin">
            </p>
        </form>

        <!-- Registered Plugins Table -->
        <h3>Registered Plugins</h3>
        <table class="wp-list-table widefat">
            <thead>
                <tr>
                    <th>Plugin Name</th>
                    <th>Current Version</th>
                    <th>Plugin URI</th>
                    <th>Latest Update</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>

                <?php
                $registered_plugins_with_updates = get_registered_plugins_with_latest_updates();


                foreach ($registered_plugins_with_updates as $plugin) {
                    $plugin_version = $plugin->new_version == null ? $plugin->version : $plugin->new_version;
                    $updated_date = $plugin->update_date == null ? "No Updates" : $plugin->update_date;

                    echo '<tr>
                    <td>' . $plugin->plugin_name . '</td>
                    <td>' . $plugin_version . '</td>
                    <td>' . $plugin->plugin_uri . '</td>
                    <td>' . $updated_date . '</td>
                    <td><a class="button-primary" href="' . admin_url("admin.php?page=wp-thumbs-update-history&plugin_id=$plugin->id") . '">Update History</a>     <button class="button delete-plugin-button" onclick="deletePlugin(' . $plugin->id . ')">Delete</a>
</td>
                    </tr>';
                }


                ?>
                <!-- Loop through registered plugins and display them in rows -->


            </tbody>
        </table>
    </div>
    <div class="wrap">
        <h2>WPThumbs Admin Update - Update Plugins</h2>

        <!-- Plugin Update Form -->
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="update-plugin-name">Plugin Name</label>
                    </th>
                    <td>
                        <select name="update-plugin-name" id="update-plugin-name">

                            <?php
                            // Populate the dropdown with registered plugin names
                            foreach ($registered_plugins_with_updates as $plugin) {
                                echo '<option value="' . esc_attr($plugin->id) . '"';
                                if ($selected_plugin === $plugin->id) {
                                    echo ' selected';
                                }
                                echo '>' . esc_html($plugin->plugin_name) . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="new-version">New Version</label>
                    </th>
                    <td>
                        <input type="text" name="new-version" id="new-version" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="updated-plugin-url">Update File (S3)</label>
                    </th>
                    <td>
                        <input type="text" name="updated-plugin-url" id="updated-plugin-url" class="regular-text" required>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="submit-update" id="submit-update" class="button button-primary" value="Update Plugin">
            </p>
        </form>
    </div>

    <script>
        // Function to delete a plugin via AJAX
        function deletePlugin(pluginId) {
            if (confirm("Are you sure you want to delete this plugin?")) {
                jQuery.ajax({
                    type: "POST",
                    url: ajaxurl, // WordPress AJAX URL
                    data: {
                        action: "delete_plugin", // Action to trigger in WordPress
                        plugin_id: pluginId,
                    },
                    success: function(response) {
                        if (response === "success") {
                            // Plugin deleted successfully
                            // You may want to refresh the plugin list or perform other actions.
                            window.location.reload();
                        } else {
                            // Handle any errors or display a message.
                            alert(response);
                            window.location.reload();
                        }
                    },
                });
            }
        }
    </script>
<?php

}
