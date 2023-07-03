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
    
    ?>
    <div class="wrap">
        <h1>Site Info</h1>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Setting</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>WordPress Version</td>
                    <td><?php echo get_bloginfo('version'); ?></td>
                </tr>
                <tr>
                    <td>Site Language</td>
                    <td><?php echo get_bloginfo('language'); ?></td>
                </tr>
                <tr>
                    <td>User Language</td>
                    <td><?php echo get_user_locale(); ?></td>
                </tr>
                <tr>
                    <td>Timezone</td>
                    <td><?php echo date_default_timezone_get();?></td>
                    <!-- timezone_offset_get(new DateTimeZone(date_default_timezone_get()), new DateTime()) / 360 -->
                    
                </tr>
                <tr>
                    <td>Permalink Structure</td>
                    <td><?php echo get_option('permalink_structure'); ?></td>
                </tr>
                <tr>
                    <td>HTTPS Status</td>
                    <td><?php echo is_ssl() ? 'Enabled' : 'Disabled'; ?></td>
                </tr>
                <tr>
                    <td>Multisite</td>
                    <td><?php echo is_multisite() ? 'Enabled' : 'Disabled'; ?></td>
                </tr>
            </tbody>
        </table>

        <h2>Active Theme</h2>
        <?php
        $active_theme = wp_get_theme();
        echo '<p>Name: ' . $active_theme->get('Name') . '</p>';
        echo '<p>Status: Active</p>';
        echo '<p>Update: None</p>';
        echo '<p>Version: ' . $active_theme->get('Version') . '</p>';
        ?>

        <h2>Installed Plugins</h2>
        <?php
        $plugins = get_plugins();
        if ($plugins) {
            echo '<table class="widefat">';
            echo '<thead><tr><th>Name</th><th>Status</th><th>Update</th><th>Version</th></tr></thead>';
            echo '<tbody>';
            foreach ($plugins as $plugin_file => $plugin_data) {
                $plugin_name = sanitize_title($plugin_data['Name']);
                $plugin_status = is_plugin_active($plugin_file) ? 'Active' : 'Inactive';
                $plugin_update = get_plugin_updates();
                $plugin_version = $plugin_data['Version'];
                echo "<tr><td>$plugin_name</td><td>$plugin_status</td><td>";
                if (isset($plugin_update[$plugin_file])) {
                    echo $plugin_update[$plugin_file]->update->new_version;
                } else {
                    echo 'None';
                }
                echo "</td><td>$plugin_version</td></tr>";
            }
            echo '</tbody></table>';
        } else {
            echo '<p>No plugins found.</p>';
        }
        ?>

        <h2>Server Information</h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Setting</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Server Architecture</td>
                    <td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
                </tr>
                <tr>
                    <td>HTTPD Software</td>
                    <td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
                </tr>
                <tr>
                    <td>PHP Version</td>
                    <td><?php echo phpversion(); ?></td>
                </tr>
                <tr>
                    <td>PHP SAPI</td>
                    <td><?php echo php_sapi_name(); ?></td>
                </tr>
                <tr>
                    <td>Max Input Variables</td>
                    <td><?php echo ini_get('max_input_vars'); ?></td>
                </tr>
                <tr>
                    <td>Time Limit</td>
                    <td><?php echo ini_get('max_execution_time'); ?> seconds</td>
                </tr>
                <tr>
                    <td>Memory Limit</td>
                    <td><?php echo ini_get('memory_limit'); ?></td>
                </tr>
                <tr>
                    <td>Max Input Time</td>
                    <td><?php echo ini_get('max_input_time'); ?> seconds</td>
                </tr>
                <tr>
                    <td>Upload Max Filesize</td>
                    <td><?php echo ini_get('upload_max_filesize'); ?></td>
                </tr>
                <tr>
                    <td>PHP Post Max Size</td>
                    <td><?php echo ini_get('post_max_size'); ?></td>
                </tr>
                <tr>
                    <td>CURL Version</td>
                    <td><?php echo curl_version()['version']; ?></td>
                </tr>
                <tr>
                    <td>Suhosin</td>
                    <td><?php echo extension_loaded('suhosin') ? 'Enabled' : 'Disabled'; ?></td>
                </tr>
                <tr>
                    <td>Imagick Availability</td>
                    <td><?php echo extension_loaded('imagick') ? 'Enabled' : 'Disabled'; ?></td>
                </tr>
                <tr>
                    <td>Pretty Permalinks</td>
                    <td><?php echo get_option('permalink_structure') ? 'Enabled' : 'Disabled'; ?></td>
                </tr>
                <tr>
                    <td>.htaccess Extra Rules</td>
                    <td><?php echo get_option('rewrite_rules') ? 'Enabled' : 'Disabled'; ?></td>
                </tr>
            </tbody>
        </table>

  

        <h2>Constants</h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Constant</th>
                    <th>Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>WP_HOME</td>
                    <td><?php echo defined('WP_HOME') ? WP_HOME : 'undefined'; ?></td>
                </tr>
                <tr>
                    <td>WP_SITEURL</td>
                    <td><?php echo defined('WP_SITEURL') ? WP_SITEURL : 'undefined'; ?></td>
                </tr>
                <tr>
                    <td>WP_CONTENT_DIR</td>
                    <td><?php echo WP_CONTENT_DIR; ?></td>
                </tr>
                <tr>
                    <td>WP_PLUGIN_DIR</td>
                    <td><?php echo WP_PLUGIN_DIR; ?></td>
                </tr>
                <tr>
                    <td>WP_MEMORY_LIMIT</td>
                    <td><?php echo WP_MEMORY_LIMIT; ?></td>
                </tr>
                <tr>
                    <td>WP_MAX_MEMORY_LIMIT</td>
                    <td><?php echo WP_MAX_MEMORY_LIMIT; ?></td>
                </tr>
                <tr>
                    <td>WP_DEBUG</td>
                    <td><?php echo WP_DEBUG ? 'true' : 'false'; ?></td>
                </tr>
                <tr>
                    <td>WP_DEBUG_DISPLAY</td>
                    <td><?php echo WP_DEBUG_DISPLAY ? 'true' : 'false'; ?></td>
                </tr>
                <tr>
                    <td>WP_DEBUG_LOG</td>
                    <td><?php echo WP_DEBUG_LOG ? 'true' : 'false'; ?></td>
                </tr>
                <tr>
                    <td>SCRIPT_DEBUG</td>
                    <td><?php echo SCRIPT_DEBUG ? 'true' : 'false'; ?></td>
                </tr>
                <tr>
                    <td>WP_CACHE</td>
                    <td><?php echo WP_CACHE ? 'true' : 'false'; ?></td>
                </tr>
                <tr>
                    <td>CONCATENATE_SCRIPTS</td>
                    <td><?php echo defined('CONCATENATE_SCRIPTS') ? CONCATENATE_SCRIPTS : 'undefined'; ?></td>
                </tr>
                <tr>
                    <td>COMPRESS_SCRIPTS</td>
                    <td><?php echo defined('COMPRESS_SCRIPTS') ? COMPRESS_SCRIPTS : 'undefined'; ?></td>
                </tr>
                <tr>
                    <td>COMPRESS_CSS</td>
                    <td><?php echo defined('COMPRESS_CSS') ? COMPRESS_CSS : 'undefined'; ?></td>
                </tr>
                <tr>
                    <td>WP_ENVIRONMENT_TYPE</td>
                    <td><?php echo WP_ENVIRONMENT_TYPE; ?></td>
                </tr>
                <tr>
                    <td>DB_CHARSET</td>
                    <td><?php echo DB_CHARSET; ?></td>
                </tr>
                <tr>
                    <td>DB_COLLATE</td>
                    <td><?php echo defined('DB_COLLATE') ? DB_COLLATE : 'undefined'; ?></td>
                </tr>
            </tbody>
        </table>

        <h3>wp-filesystem</h3>
        <ul>
            <li>wordpress: <?php echo is_writable(ABSPATH) ? 'writable' : 'not writable'; ?></li>
            <li>wp-content: <?php echo is_writable(WP_CONTENT_DIR) ? 'writable' : 'not writable'; ?></li>
            <li>uploads: <?php echo is_writable(wp_upload_dir()['basedir']) ? 'writable' : 'not writable'; ?></li>
            <li>plugins: <?php echo is_writable(WP_PLUGIN_DIR) ? 'writable' : 'not writable'; ?></li>
            <li>themes: <?php echo is_writable(get_theme_root()) ? 'writable' : 'not writable'; ?></li>
        </ul>

        <h3>wp-media</h3>
        <ul>
            <li>image_editor: <?php echo wp_image_editor_supports() ? 'Available' : 'Not available'; ?></li>           
            <li>imagick_module_version: <?php echo defined('IMAGICK_MODULE_VERSION') ? IMAGICK_MODULE_VERSION : 'Not available'; ?></li>
            <li>imagemagick_version: <?php echo defined('IMAGEMAGICK_VERSION') ? IMAGEMAGICK_VERSION : 'Not available'; ?></li>
            <li>file_uploads: <?php echo ini_get('file_uploads') ? 'File uploads is turned on' : 'File uploads is turned off'; ?></li>
            <li>post_max_size: <?php echo ini_get('post_max_size'); ?></li>
            <li>upload_max_filesize: <?php echo ini_get('upload_max_filesize'); ?></li>
            <li>max_effective_size: <?php echo ini_get('upload_max_filesize'); ?></li>
            <li>max_file_uploads: <?php echo ini_get('max_file_uploads'); ?></li>
            <li>gd_version: <?php echo gd_info()['GD Version']; ?></li>
            <li>gd_formats: <?php echo $gd_formats; ?></li>
            <li>ghostscript_version:<?php echo (function_exists('gs_version') ? gs_version() : ' not available'); ?></li>
        </ul>

        <h3>wp-database</h3>
        <ul>
            <li>extension: <?php echo $database_extension; ?></li>
            <li>server_version: <?php echo $db_version; ?></li>
            <li>client_version: <?php echo $mysql_version; ?></li>
            <li>max_allowed_packet: <?php echo $max_allowed_packet; ?></li>
            <li>max_connections: <?php echo $max_connections; ?></li>
        </ul>

    </div>
    <?php
}
