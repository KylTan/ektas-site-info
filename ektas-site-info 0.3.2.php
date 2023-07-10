<?php
/*
Plugin Name: Site Info
Description: Displays site details in the WordPress admin page.
*/

// Add the menu item to the admin menu
add_action('admin_menu', 'site_info_add_menu');

function site_info_add_menu() {
    add_menu_page('Site Info', 'Site Info', 'manage_options', 'site-info', 'site_info_display');
}

// Display the site details on the admin page
function site_info_display() {

    global $wpdb;
    $wpdb->show_errors();
    $db_version = $wpdb->db_version();
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD);
    $mysql_version = $mysqli->server_info;

    // Custom values for max_allowed_packet and max_connections
    $max_allowed_packet = $mysqli->query("SHOW VARIABLES LIKE 'max_allowed_packet'")->fetch_object()->Value;
    $max_connections = $mysqli->query("SHOW VARIABLES LIKE 'max_connections'")->fetch_object()->Value;

    // Retrieve database extension
    $database_extension = $mysqli->get_client_info();

    $mysqli->close();

    $gd_info = gd_info();
    $gd_version = $gd_info['GD Version'];
    $gd_formats = '';
    if (isset($gd_info['GD2'])) {
        $gd_formats = implode(', ', array_keys($gd_info['GD2']));
    } elseif (isset($gd_info['GD'])) {
        $gd_formats = implode(', ', array_keys($gd_info['GD']));
    }

    // Check if JSON request --> for json reqests but its admin so cant curl
    // if (isset($_GET['json'])) {
    //     // Set JSON headers
    //     header('Content-Type: application/json');
        
    //     // Create JSON response
    //     $site_info = array(
    //         'wordpress_version' => get_bloginfo('version'),
    //         'site_language' => get_bloginfo('language'),
    //         'user_language' => get_user_locale(),
    //         'timezone' => date_default_timezone_get(),
    //         // ...other site information...
    //     );

    //     // Output JSON response
    //     echo json_encode($site_info);
    //     exit; // Terminate further processing
    // }
    

$data = array();

$data['Site Info'] = array(
    'WordPress Version' => get_bloginfo('version'),
    'Site Language' => get_bloginfo('language'),
    'User Language' => get_user_locale(),
    'Timezone' => date_default_timezone_get(),
    'Permalink Structure' => get_option('permalink_structure'),
    'HTTPS Status' => is_ssl() ? 'Enabled' : 'Disabled',
    'Multisite' => is_multisite() ? 'Enabled' : 'Disabled'
);

$active_theme = wp_get_theme();
$data['Active Theme'] = array(
    'Name' => $active_theme->get('Name'),
    'Status' => 'Active',
    'Update' => 'None',
    'Version' => $active_theme->get('Version')
);

$plugins = get_plugins();
$pluginsData = array();
foreach ($plugins as $plugin_file => $plugin_data) {
    $plugin_name = sanitize_title($plugin_data['Name']);
    $plugin_status = is_plugin_active($plugin_file) ? 'Active' : 'Inactive';
    $plugin_update = get_plugin_updates();
    $plugin_version = $plugin_data['Version'];

    if (isset($plugin_update[$plugin_file])) {
        $plugin_update_version = $plugin_update[$plugin_file]->update->new_version;
    } else {
        $plugin_update_version = 'None';
    }

    $pluginsData[$plugin_name] = array(
        'Status' => $plugin_status,
        'Update' => $plugin_update_version,
        'Version' => $plugin_version
    );
}
$data['Installed Plugins'] = $pluginsData;

$data['Server Information'] = array(
    'Server Architecture' => $_SERVER['SERVER_SOFTWARE'],
    'HTTPD Software' => $_SERVER['SERVER_SOFTWARE'],
    'PHP Version' => phpversion(),
    'PHP SAPI' => php_sapi_name(),
    'Max Input Variables' => ini_get('max_input_vars'),
    'Time Limit' => ini_get('max_execution_time') . ' seconds',
    'Memory Limit' => ini_get('memory_limit'),
    'Max Input Time' => ini_get('max_input_time') . ' seconds',
    'Upload Max Filesize' => ini_get('upload_max_filesize'),
    'PHP Post Max Size' => ini_get('post_max_size'),
    'CURL Version' => curl_version()['version'],
    'Suhosin' => extension_loaded('suhosin') ? 'Enabled' : 'Disabled',
    'Imagick Availability' => extension_loaded('imagick') ? 'Enabled' : 'Disabled',
    'Pretty Permalinks' => get_option('permalink_structure') ? 'Enabled' : 'Disabled',
    '.htaccess Extra Rules' => get_option('rewrite_rules') ? 'Enabled' : 'Disabled'
);

$data['Constants'] = array(
    'WP_HOME' => defined('WP_HOME') ? WP_HOME : 'undefined',
    'WP_SITEURL' => defined('WP_SITEURL') ? WP_SITEURL : 'undefined',
    'WP_CONTENT_DIR' => WP_CONTENT_DIR,
    'WP_PLUGIN_DIR' => WP_PLUGIN_DIR,
    'WP_MEMORY_LIMIT' => WP_MEMORY_LIMIT,
    'WP_MAX_MEMORY_LIMIT' => WP_MAX_MEMORY_LIMIT,
    'WP_DEBUG' => WP_DEBUG ? 'true' : 'false',
    'WP_DEBUG_DISPLAY' => WP_DEBUG_DISPLAY ? 'true' : 'false',
    'WP_DEBUG_LOG' => WP_DEBUG_LOG ? 'true' : 'false',
    'SCRIPT_DEBUG' => SCRIPT_DEBUG ? 'true' :'false',
    'WP_CACHE' => WP_CACHE ? 'true' : 'false',
    'CONCATENATE_SCRIPTS' => defined('CONCATENATE_SCRIPTS') ? CONCATENATE_SCRIPTS : 'undefined',
    'COMPRESS_SCRIPTS' => defined('COMPRESS_SCRIPTS') ? COMPRESS_SCRIPTS : 'undefined',
    'COMPRESS_CSS' => defined('COMPRESS_CSS') ? COMPRESS_CSS : 'undefined',
    'WP_ENVIRONMENT_TYPE' => WP_ENVIRONMENT_TYPE,
    'DB_CHARSET' => DB_CHARSET,
    'DB_COLLATE' => defined('DB_COLLATE') ? DB_COLLATE : 'undefined'
);

$filesystem = array(
    'wordpress' => is_writable(ABSPATH) ? 'writable' : 'not writable',
    'wp-content' => is_writable(WP_CONTENT_DIR) ? 'writable' : 'not writable',
    'uploads' => is_writable(wp_upload_dir()['basedir']) ? 'writable' : 'not writable',
    'plugins' => is_writable(WP_PLUGIN_DIR) ? 'writable' : 'not writable',
    'themes' => is_writable(get_theme_root()) ? 'writable' : 'not writable'
);
$data['wp-filesystem'] = $filesystem;

$data['wp-core'] = array(
    'WordPress Version' => get_bloginfo('version'),
    'PHP Version' => phpversion(),
    'MySQL Version' => $mysql_version
);

$data['wp-environment'] = array(
    'WP_DEBUG' => WP_DEBUG ? 'true' : 'false',
    'WP_DEBUG_DISPLAY' => WP_DEBUG_DISPLAY ? 'true' : 'false',
    'WP_DEBUG_LOG' => WP_DEBUG_LOG ? 'true' : 'false',
    'WP_ENVIRONMENT_TYPE' => WP_ENVIRONMENT_TYPE,
    'DB_CHARSET' => DB_CHARSET,
    'DB_COLLATE' => defined('DB_COLLATE') ? DB_COLLATE : 'undefined'
);

$data['wp-filesystem'] = $filesystem;

$data['wp-media'] = array(
    'image_editor' => wp_image_editor_supports() ? 'Available' : 'Not available',
    'imagick_module_version' => defined('IMAGICK_MODULE_VERSION') ? IMAGICK_MODULE_VERSION : 'Not available',
    'imagemagick_version' => defined('IMAGEMAGICK_VERSION') ? IMAGEMAGICK_VERSION : 'Not available',
    'file_uploads' => ini_get('file_uploads') ? 'File uploads is turned on' : 'File uploads is turned off',
    'post_max_size' => ini_get('post_max_size'),
	 'upload_max_filesize' => ini_get('upload_max_filesize'),
    'max_effective_size' => size_format(wp_max_upload_size()),
    'max_file_uploads' => (int) ini_get('max_file_uploads'),
    'gd_version' => $gd_version,
    'gd_formats' => $gd_formats,
    'ghostscript_version' => 'Not available'
	
);

// header('Content-Type: application/json');
// echo json_encode($data, JSON_PRETTY_PRINT);

// Format the data
foreach ($data as $section => $sectionData) {
    $sectionFormattedData = [];
    
    foreach ($sectionData as $key => $value) {
        if (is_array($value)) {
            $subData = [];
            foreach ($value as $subKey => $subValue) {
                $subData[$subKey] = $subValue;
            }
            $sectionFormattedData[$key] = $subData;
        } else {
            $sectionFormattedData[$key] = $value;
        }
    }
    
    $formattedData[$section] = $sectionFormattedData;
}

// Output the data with JSON formatting
$data_string = json_encode($data, JSON_PRETTY_PRINT);
$data_string_formatted = ($data_string); // Convert newlines to HTML line breaks

// Echo the formatted data with a copy button
echo '<div>';
echo '<textarea id="json-output" rows="40" cols="80">';
echo $data_string_formatted;
echo '</textarea>';
echo '<br>';
echo '<button onclick="copyToClipboard()">Copy</button>';
echo '</div>';

// Form for specifying the endpoint, username, and password
echo '<div>';
echo '<form method="POST">';
echo 'Endpoint: <input type="text" name="endpoint" placeholder="http://example.com/api-endpoint"><br>';
echo 'Username: <input type="text" name="username"><br>';
echo 'Password: <input type="password" name="password"><br>';
echo '<input type="submit" value="Send POST Request">';
echo '</form>';
echo '</div>';


// Handle the form submission
if (isset($_POST['endpoint'], $_POST['username'], $_POST['password'])) {
    $endpoint = sanitize_text_field($_POST['endpoint']);
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Create an array containing the site info and credentials
    $site_info = array(
        'wordpress_version' => get_bloginfo('version'),
        'site_language' => get_bloginfo('language'),
        'user_language' => get_user_locale(),
        'timezone' => date_default_timezone_get(),
        // ...other site information...
    );

    // Include the username and password in the site info array
     $site_info['username'] = $username;
     $site_info['password'] = $password;

    // Convert the site info array to JSON
    $data_string = json_encode($site_info);

    $current_user = wp_get_current_user();
    var_dump($current_user->roles); // Check the user's roles
    var_dump($current_user->allcaps); // Check the user's capabilities

    // Send a REST API POST request
    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);

    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode($username . ':' . $password)
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    // Check the response
    if ($response === false) {
        echo 'Failed to send the POST request.';
    } else {
        echo 'POST request sent successfully.';
        echo '<br>';
        echo 'Response from the endpoint:<br>';
        echo '<pre>' . htmlentities($response) . '</pre>';
		
	
	
    }
}}
?>


<script>
    // JavaScript function to copy the contents of the textarea to the clipboard
    function copyToClipboard() {
        const textarea = document.getElementById('json-output');
        textarea.select();
        textarea.setSelectionRange(0, 99999); // For mobile devices

        // Copy the text to the clipboard
        document.execCommand("copy");

        // Provide visual feedback to the user
        alert("Data copied to clipboard!");
    }
</script>
