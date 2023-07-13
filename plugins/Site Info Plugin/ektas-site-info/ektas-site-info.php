<?php
/*
Plugin Name: Site Info
Description: Displays site details in the WordPress admin page.
Version: 0.5.6
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
    

 $data['wp-core'] = array(
        'version' => get_bloginfo('version'),
        'site_language' => get_bloginfo('language'),
        'user_language' => get_user_locale(),
        'timezone' => date_default_timezone_get(),
        'permalink' => get_option('permalink_structure'),
        'https_status' => is_ssl() ? 'true' : 'false',
        'multisite' => is_multisite() ? 'true' : 'false'
    );

    $data['wp-theme-list'] = array();
    $themes = wp_get_themes();
    foreach ($themes as $slug => $theme) {
        $data['wp-theme-list'][] = array(
            'slug' => $slug,
            'status' => 'active',
            'update' => 'none',
            'version' => $theme->get('Version')
        );
    }

    $data['wp-plugin-list'] = array();
    $plugins = get_plugins();
    $plugin_updates = get_plugin_updates();
    foreach ($plugins as $plugin_file => $plugin_data) {
        $plugin_slug = dirname($plugin_file);
		$data['wp-plugin-list'][] = array(
            'name' => $plugin_slug,
            'status' => is_plugin_active($plugin_file) ? 'active' : 'inactive',
            'update' => isset($plugin_updates[$plugin_file]) ? 'available' : 'none',
            'version' => $plugin_data['Version']
        );
    }

    $data['wp-server'] = array(
        'server_architecture' => php_uname('s') . ' ' . php_uname('r'),
        'httpd_software' => $_SERVER['SERVER_SOFTWARE'],
        'php_version' => phpversion(),
        'php_sapi' => php_sapi_name(),
        'max_input_variables' => ini_get('max_input_vars'),
        'time_limit' => ini_get('max_execution_time'),
        'memory_limit' => ini_get('memory_limit'),
        'max_input_time' => ini_get('max_input_time'),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'php_post_max_size' => ini_get('post_max_size'),
        'curl_version' => curl_version()['version'],
        'suhosin' => extension_loaded('suhosin') ? 'true' : 'false',
        'imagick_availability' => extension_loaded('imagick') ? 'true' : 'false',
        'pretty_permalinks' => get_option('permalink_structure') ? 'true' : 'false',
        'htaccess_extra_rules' => get_option('rewrite_rules') ? 'true' : 'false'
    );

    $data['wp-database'] = array(
        'extension' => 'mysqli',
        'server_version' => $wpdb->db_version(),
        'client_version' => $wpdb->db_version('mysql'),
        'max_allowed_packet' => $max_allowed_packet,
        'max_connections' => $max_connections
    );

    $data['wp-constants'] = array(
        'WP_HOME' => defined('WP_HOME') ? WP_HOME : 'undefined',
        'WP_SITEURL' => defined('WP_SITEURL') ? WP_SITEURL : 'undefined',
        'WP_CONTENT_DIR' => WP_CONTENT_DIR,
        'WP_PLUGIN_DIR' => WP_PLUGIN_DIR,
        'WP_MEMORY_LIMIT' => WP_MEMORY_LIMIT,
        'WP_MAX_MEMORY_LIMIT' => WP_MAX_MEMORY_LIMIT,
        'WP_DEBUG' => WP_DEBUG ? 'true' : 'false',
        'WP_DEBUG_DISPLAY' => WP_DEBUG_DISPLAY ? 'true' : 'false',
        'WP_DEBUG_LOG' => WP_DEBUG_LOG ? 'true' : 'false',
        'SCRIPT_DEBUG' => SCRIPT_DEBUG ? 'true' : 'false',
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

$data['wp-media'] = array(
    'image_editor' => (class_exists('WP_Image_Editor_GD') ? 'WP_Image_Editor_GD' : 'Not available'),
    'imagick_module_version' => defined('IMAGICK_MODULE_VERSION') ? 'IMAGICK_MODULE_VERSION' : 'Not available',
    'imagemagick_version' => defined('IMAGEMAGICK_VERSION') ? 'IMAGEMAGICK_VERSION' : 'Not available',
    'file_uploads' => ini_get('file_uploads') ? 'File uploads is turned on' : 'File uploads is turned off',
    'post_max_size' => ini_get('post_max_size'),
	 'upload_max_filesize' => ini_get('upload_max_filesize'),
    'max_effective_size' => size_format(wp_max_upload_size()),
    'max_file_uploads' => (int) ini_get('max_file_uploads'),
    'gd_version' => $gd_version,
    'gd_formats' => $gd_formats,
    'ghostscript_version' => (function_exists('gs_version') ? gs_version() : 'Not available')
	
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

<?php

// Echo the formatted data with a copy button
echo '<div style=" padding:15px; padding-bottom: 0px;">';
echo '<h1> EKTAS Site Info Plugin </h1>';
echo '<h4> Export site info at the click of a button! </h4>';
echo '<hr></hr>';
echo "<h3> Your Site's Info JSON</h3>";
echo '<textarea id="json-output" rows="20" cols="70">';
echo $data_string_formatted;
echo '</textarea>';
echo '<br>';
echo '<button onclick="copyToClipboard()">Copy to Clipboard</button>';
echo '</div>';

// Form for specifying the endpoint, username, and password
echo '<div style="padding: 10px;">';
echo '<h4> Make sure your endpoint can recieve a post in the post_content, and that you are using a valid 
        application password</h4>';
echo '<form method="POST">';
echo '<label for="endpoint">Endpoint:</label><br>';
echo '<input type="text" name="endpoint" id="endpoint" placeholder="http://example.com/api-endpoint"><br>';
echo '<label for="username">Username:</label><br>';
echo '<input type="text" name="username" id="username"><br>';
echo '<label for="password">Password:</label><br>';
echo '<input type="password" name="password" id="password"><br>';
echo '<input type="submit" value="Send POST Request" style="margin-top: 15px;">';
echo '</form>';
echo '</div>';


// Handle the form submission
if (isset($_POST['endpoint'], $_POST['username'], $_POST['password'])) {
    $endpoint = sanitize_text_field($_POST['endpoint']);
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Create the post data
    $post_data = array(
        'title' => get_bloginfo('name') . ' Info',
        'content' => json_encode($data, JSON_PRETTY_PRINT),
        'status' => 'publish',
        'type' => 'site'
    );

    // Convert the post data to JSON
    $data_string = json_encode($post_data);

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

    //Check the response for debug
    // if ($response === false) {
    //     echo 'Failed to send the POST request.';
    // } else {
    //     echo 'POST request sent successfully.';
    //     echo '<br>';
    //     echo 'Response from the endpoint:<br>';
    //     echo '<pre style="white-space: pre-wrap; word-wrap: break-word;">' . htmlentities($response) . '</pre>';
    // }


    // Decode the response JSON to extract the post ID
    $response_data = json_decode($response, true);
?>

<style>
  .collapsible-content {
    display: none;
  }

  .collapsible-trigger {
    display: inline-block;
    cursor: pointer;
    padding: 6px 12px;
    font-size: 14px;
    line-height: 1.42857143;
    text-align: center;
    white-space: nowrap;
    vertical-align: middle;
    border: 1px solid #ccc;
    border-radius: 4px;
    background-image: linear-gradient(to bottom, #fff 0%, #f2f2f2 100%);
    box-shadow: 0 2px 2px rgba(0, 0, 0, 0.15);
    transition: background-color 0.3s;
  }
</style>


<?php //output response with a collapsible button ?>
<script>
  function toggleCollapsible() {
    const content = document.getElementById('collapsible-content');
    content.style.display = content.style.display === 'none' ? 'block' : 'none';
  }
</script>

<div class="collapsible">
  <div class="collapsible-trigger" onclick="toggleCollapsible()">Reveal Response</div>
  <div id="collapsible-content" class="collapsible-content">
    <pre><?php echo htmlentities(var_export($response_data, true)); ?></pre>
  </div>
</div>

<?php
    if (isset($response_data['id'])) {
        $post_id = $response_data['id'];

        // Update the post content
        $updated_post = array(
            'ID' => $post_id,
            'post_content' => $data_string,
        );
        wp_update_post($updated_post);

        echo 'ACF post created successfully with ID: ' . $post_id;
    } else {
        echo 'Failed to create ACF post.';

    }
  }
}
?>




