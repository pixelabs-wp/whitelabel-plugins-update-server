<?php
/**
 * API Control.
 *
 * @since Version 1.0.0
 */
use Aws\S3\S3Client;
error_reporting(E_ERROR | E_PARSE);

function register_custom_api_routes()
{
    register_rest_route('wpthumbs', '/plugin-updates', array(
        'methods' => 'GET',
        'callback' => 'handle_updates_plugin',
    ));
}
add_action('rest_api_init', 'register_custom_api_routes');

function handle_updates_plugin(WP_REST_Request $request)
{
   $bearer_token = $request->get_header('custom'); // Retrieve the bearer token from request headers.


    // Check if the bearer token is provided and in the correct format.
    if (preg_match('/^Bearer (.+)$/', $bearer_token, $matches)) {
        $base64_credentials = $matches[1];
        
        // Decode the base64 token to get the username and password.
        list($username, $password) = explode(':', base64_decode($base64_credentials));
        
        // Verify the username and password against your authentication method.
        if (is_valid_credentials($username, $password)) {
            // Authentication is successful; proceed with checking the subscription.
            $user_id = get_user_id_by_username($username);
            
            if (has_active_subscription($user_id)) {
                // User has an active subscription; proceed with processing the request.
                $data = get_registered_plugins_with_latest_updates();
                $final_array = array();
                foreach ($data as $update) {
                    $filepath = $update->updated_plugin_url;
                    $signedUrl = get_aws_signed_url($filepath);
                    $update->updated_plugin_url = $signedUrl;
                    array_push($final_array, $update);
                }
                return rest_ensure_response($final_array);
            } else {
                // User does not have an active subscription; deny access.
                return new WP_Error('unauthorized', 'Unauthorized access - No Active Subscription Found', array('status' => 401));
            }
        } else {
            // Invalid username or password; deny access.
            return new WP_Error('unauthorized', 'Unauthorized access - Username Password Incorrect', array('status' => 401));
        }
    } else {
        // Bearer token is missing or in the incorrect format; deny access.
        return new WP_Error('unauthorized', 'Unauthorized access - Bearer Auth Not Provided', array('status' => 401));
    }
}

function is_valid_credentials($username, $password)
{
    // Replace this with your own method of checking username and password validity.
    // You can use WordPress authentication functions, an external API, or database checks.
    // Return true if the credentials are valid, or false if not.
    // Example: Check credentials against WordPress users.
    $user = wp_authenticate($username, $password);
    return !is_wp_error($user);
}

function get_user_id_by_username($username)
{
    // Replace this with your method to get the user ID by username.
    // You can use WordPress functions like get_user_by(), or your own database query.
    // Return the user ID if found, or 0 if not found.
    // Example: Get user ID by username using WordPress function.
    $user = get_user_by('login', $username);
    return $user ? $user->ID : 0;
}

function has_active_subscription( $user_id=null ) {
    // When a $user_id is not specified, get the current user Id
    if( null == $user_id && is_user_logged_in() ) 
        $user_id = get_current_user_id();
    // User not logged in we return false
    if( $user_id == 0 ) 
        return false;

	$active_subscriptions = new WP_Query(array(
    'numberposts' => 1, // Only one is enough
    'fields'      => 'ids', // return only IDs (instead of complete post objects)
    'post_type'      => 'ywcmbs-membership',
    'posts_per_page' => 1,
    'meta_query'     => array(
        'relation' => 'AND', // Both conditions must be met
        array(
            'key'     => '_user_id',
            'value'   => $user_id,
            'compare' => '=',
        ),
        array(
            'key'     => '_status',
            'value'   => 'active',
            'compare' => '=',
        ),
    ),
));


// Check if there are matching subscriptions
if ($active_subscriptions->have_posts()) {
    // There is at least one active subscription for the user.
    // You can access the subscription details in the loop if needed.
    while ($active_subscriptions->have_posts()) {
        $active_subscriptions->the_post();
        $subscription_id = get_the_ID();
        // Process subscription details as needed.
        // 
        return true;
    }
}
	return false;
}
function get_aws_signed_url($filepath)
{
    $aws_region = get_option('aws_s3_region', '');
    $aws_access_key = get_option('aws_s3_access_key', '');
    $aws_secret_key = get_option('aws_s3_secret_key', '');
    $bucket_name = get_option('aws_s3_bucket_name', '');
    $config = [
        'region' => $aws_region, // Replace with your desired AWS region
        'version' => 'latest',   // Use the latest API version
        'credentials' => [
            'key' => $aws_access_key,
            'secret' => $aws_secret_key,
        ],
    ];
    $s3 = new S3Client($config);



    $cmd = $s3->getCommand('GetObject', [
        'Bucket' => $bucket_name,
        'Key' => $filepath
    ]);

    $request = $s3->createPresignedRequest($cmd, '+20 minutes');

    $presignedUrl = (string) $request->getUri();
    return $presignedUrl;
}
