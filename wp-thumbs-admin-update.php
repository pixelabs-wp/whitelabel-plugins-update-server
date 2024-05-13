<?php

/**
 * Plugin Name: WPThumbs Admin Update
 * Description: A WordPress plugin for managing and publishing updates for custom plugins.
 * Version: 1.0.0
 * Author: Syed Ali Haider Hamdani
 * Author URI: https://www.fiverr.com/syedali157/
 * License: MIT
 */

// Define plugin constants
define('WP_THUMBS_UPDATE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_THUMBS_UPDATE_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Activation hook
register_activation_hook(__FILE__, 'wp_thumbs_update_activation');
function wp_thumbs_update_activation()
{
    create_plugin_registry_table();
    create_plugin_updates_table();
    create_api_keys_table();
	addAuthRules();
	
}

function addAuthRules() {
    // Define the rule you want to add
    $htaccess_rule = "RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]";

    // Get the current content of .htaccess
    $htaccess_content = @file_get_contents(ABSPATH . '.htaccess');

    if ($htaccess_content !== false) {
        // Check if the rule already exists in .htaccess
        if (strpos($htaccess_content, $htaccess_rule) === false) {
            // Add the rule to .htaccess
            $htaccess_content .= "\n" . $htaccess_rule;
            file_put_contents(ABSPATH . '.htaccess', $htaccess_content);
        }
    }
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'wp_thumbs_update_deactivation');
function wp_thumbs_update_deactivation()
{
    // Add deactivation actions here, if needed
    // E.g., remove database tables, clean up options, etc.
}

// Include necessary files
require_once WP_THUMBS_UPDATE_PLUGIN_DIR . 'includes/plugin-registration/register-plugin.php';
require_once WP_THUMBS_UPDATE_PLUGIN_DIR . 'includes/plugin-registration/update-plugin.php';
require_once WP_THUMBS_UPDATE_PLUGIN_DIR . 'includes/plugin-registration/api-auth.php';
require_once WP_THUMBS_UPDATE_PLUGIN_DIR . 'includes/database/db.php';
require_once WP_THUMBS_UPDATE_PLUGIN_DIR . 'includes/utilities/api.php';
// uncomment if not using other s3 Clients
//require_once WP_THUMBS_UPDATE_PLUGIN_DIR . 'aws/aws-autoloader.php';


// Additional initialization and plugin logic can go here



add_action('woocommerce_order_status_completed', 'register_key_custom');

function register_key_custom($order_id)
{
    $order = wc_get_order($order_id);
    $user = $order->get_user();
    if ($user) {
        // Send the API Key to the user
        $user_email = $user->user_email;
        $subject = 'Your API Key and Order Details';

        // Load the email template
        $message = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Your Purchase Details</title>
</head>
<body>
    <p>Dear ' . $user->display_name . ',</p>

    <p>Thank you for your recent purchase with us. We are excited to provide you with access to our plugin update client</p>

    <h2>Order Details</h2>
    <table>
        <tr>
            <th>Order Number:</th>
            <td>' . $order_id . '</td>
        </tr>
        <tr>
            <th>Order Date:</th>
            <td>' . $order->get_date_created()->format('Y-m-d H:i:s') . '</td>
        </tr>
        <tr>
            <th>Order Total:</th>
            <td>' . wc_price($order->get_total()) . '</td>
        </tr>
    </table>



    <h2>How to Activate WPThumbs Updater</h2>
    <ol>
        <li>Download <a href="https://www.wpthumbs.com/wp-content/uploads/2023/10/wp-thumbs-updater.zip">WPThumbs Updater</a></li>
        <li>Install & Navigate to the plugin settings page</li>
        <li>Add your username & password (from wpthumbs.com)</li>
        <li>Click the "Activate" or "Update" button to enable automatic updates for your purchased plugin(s).</li>
    </ol>

    <p>If you have any questions or encounter any issues, please feel free to contact our support team for assistance. We are here to help!</p>

    <p>Thank you for choosing our products. We appreciate your business.</p>

    <p>Best regards,<br>WP Thumbs</p>
</body>
</html>';
		
		$attachments = array('https://www.wpthumbs.com/wp-content/uploads/2023/10/wp-thumbs-updater.zip');
$headers = array('Content-Type: text/html; charset=UTF-8');

        // Send the email
        wp_mail($user_email, $subject, $message,$headers, $attachments);
    }
}
