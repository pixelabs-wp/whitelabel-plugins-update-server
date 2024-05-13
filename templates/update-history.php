<?php

/**
 * Updates History.
 *
 * @since Version 1.0.0
 */

// Define a callback function to render the Update History page
function wp_thumbs_update_update_history_page()
{
    if (isset($_GET["plugin_id"])) {
        $update_history = get_plugin_details_and_updates($_GET["plugin_id"]);
    } else {
        $update_history = get_registered_plugins_with_latest_updates();
    }

?>
    <div class="wrap">
        <h2>WPThumbs Admin Update - Update History</h2>
        <p>View the history of updates for your registered plugins.</p>

        <!-- Update History Table -->
        <table class="wp-list-table widefat">
            <thead>
                <tr>
                    <th>Plugin Name</th>
                    <th>Version</th>
                    <th>Date Updated</th>
                    <th>Update File Url</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($update_history as $key => $update) {

                    $action = ($key == 0)   ? "<span style='color: green;'> Current Version</span>" : '<a href="#TB_inline?width=600&height=300&inlineId=rollback-' . $update->pu_id . '" class="button thickbox">Rollback</a>';

                    echo '<tr>';
                    echo '<td>' . esc_html($update->plugin_name)  . '</td>';
                    echo '<td>' . esc_html($update->new_version) . '</td>';
                    echo '<td> ' . esc_html($update->update_date) . '</td>';
                    echo '<td> ' . esc_html($update->updated_plugin_url) . '</td>';
                    echo '<td>' . $action . '</td>';
                    echo '</tr>';
                ?>
                    <?php add_thickbox(); ?>
                    <div id="rollback-<?php echo $update->pu_id; ?>" style="display:none;">
                        <div class="thickbox-title">
                            <h3>Rollback Update</h3>
                        </div>
                        <div class="thickbox-content">
                            <p class="subtitle">Are you sure you want to rollback this update?</p>
                            <p><strong>Plugin Name:</strong> <?php echo esc_html($update->plugin_name); ?></p>
                            <p><strong>Version:</strong> <?php echo esc_html($update->new_version); ?></p>
                            <p><strong>Date Updated:</strong> <?php echo esc_html($update->update_date); ?></p>
                            <p><strong>Update File URL:</strong> <?php echo esc_html($update->updated_plugin_url); ?></p>
                            <p class="action-button">
                                <button class="button" onclick="rollbackUpdate(<?php echo $update->pu_id; ?>)">Confirm</button>
                            </p>
                        </div>
                    </div>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
        function rollbackUpdate(updateId) {
            // Confirm the rollback action

            // Make an AJAX request to trigger the rollback action
            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'rollback_update', // Action hook name
                    update_id: updateId // Pass the update_id
                },
                success: function(response) {
                    if (response.success) {
                        // Handle success, e.g., show a success message
                        alert(response.data);
                        location.reload();

                        // Close the ThickBox or perform any other necessary actions
                        tb_remove();
                    } else {
                        // Handle error, e.g., show an error message
                        alert(response.data);
                        location.reload();

                    }
                }
            });

        }
    </script>
<?php
}
