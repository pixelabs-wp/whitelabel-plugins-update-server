<?php

function api_auth_settings_page()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'authorized_api_keys';

    // Handle the form submission to add API keys
    if (isset($_POST['add_api_key'])) {
        $api_key = sanitize_text_field($_POST['api_key']);

        // Add your logic to insert the API key into the database
        // (e.g., using $wpdb->insert or your custom function).
        create_api_auth($api_key);
    }

    if (isset($_POST['add_aws_config'])) {
        // Retrieve the AWS S3 configuration details from the form
        $aws_region = sanitize_text_field($_POST['aws_region']);
        $aws_access_key = sanitize_text_field($_POST['aws_access_key']);
        $aws_secret_key = sanitize_text_field($_POST['aws_secret_key']);
        $bucket_name = sanitize_text_field($_POST['bucket_name']);

        // Store the configuration values separately
        update_option('aws_s3_region', $aws_region);
        update_option('aws_s3_access_key', $aws_access_key);
        update_option('aws_s3_secret_key', $aws_secret_key);
        update_option('aws_s3_bucket_name', $bucket_name);
    }

    // Retrieve the AWS S3 configuration values from options
    $aws_region = get_option('aws_s3_region', '');
    $aws_access_key = get_option('aws_s3_access_key', '');
    $aws_secret_key = get_option('aws_s3_secret_key', '');
    $bucket_name = get_option('aws_s3_bucket_name', '');
?>
    <div class="wrap">
        <h2>API Authentication Settings</h2>
       

      
        <!-- Display the table of authorized API keys -->
       
        
        <h3>Configure AWS S3</h3>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th><label for="aws_region">AWS Region:</label></th>
                    <td><input type="text" name="aws_region" id="aws_region" placeholder="Enter AWS Region (e.g., us-east-1)" value="<?php echo esc_attr($aws_region); ?>"></td>
                </tr>
                <tr>
                    <th><label for="aws_access_key">AWS Access Key:</label></th>
                    <td><input type="text" name="aws_access_key" id="aws_access_key" placeholder="Enter AWS Access Key" value="<?php echo esc_attr($aws_access_key); ?>"></td>
                </tr>
                <tr>
                    <th><label for="aws_secret_key">AWS Secret Key:</label></th>
                    <td><input type="text" name="aws_secret_key" id="aws_secret_key" placeholder="Enter AWS Secret Key" value="<?php echo esc_attr($aws_secret_key); ?>"></td>
                </tr>
                <tr>
                    <th><label for="bucket_name">S3 Bucket Name:</label></th>
                    <td><input type="text" name="bucket_name" id="bucket_name" placeholder="Enter S3 Bucket Name" value="<?php echo esc_attr($bucket_name); ?>"></td>
                </tr>
            </table>
            <input type="submit" name="add_aws_config" value="Save AWS Configuration" class="button-primary">
        </form>

        <script>
            jQuery(document).ready(function($) {
                $('.toggle-access').on('click', function() {
                    var api_key = $(this).data('api-key');
                    var action = $(this).data('action');

                    // Make an AJAX request to toggle access using the API key
                    $.ajax({
                        type: 'POST',
                        url: ajaxurl, // WordPress AJAX URL
                        data: {
                            action: 'toggle_api_access',
                            api_key: api_key,
                        },
                        success: function(response) {
                            // Refresh the page or update the UI as needed
                            window.location.reload()
                        },
                    });
                });
            });
        </script>
    </div>
<?php
}
