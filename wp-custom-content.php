<?php
/**
 * Plugin Name: WPCustomContent
 * Plugin URI: https://github.com/TrevorLowing/WPCustomContent
 * Description: A comprehensive WordPress content management plugin with GPT-powered analysis, advanced logging, and smart notifications.
 * Version: 1.0.0-test.7
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Trevor Lowing
 * Author URI: https://varry.io
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wpcustomcontent
 *
 * @package WPCustomContent
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WP_CUSTOM_CONTENT_VERSION', '1.0.0');
define('WP_CUSTOM_CONTENT_TEST_VERSION', '1.0.0-test.7');
define('WP_CUSTOM_CONTENT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_CUSTOM_CONTENT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_CUSTOM_CONTENT_PLUGIN_FILE', __FILE__);

// Autoloader
if (file_exists(WP_CUSTOM_CONTENT_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once WP_CUSTOM_CONTENT_PLUGIN_DIR . 'vendor/autoload.php';
} else {
    // Fallback autoloader
    spl_autoload_register(function ($class) {
        $prefix = 'WPCustomContent\\';
        $base_dir = WP_CUSTOM_CONTENT_PLUGIN_DIR . 'src/';

        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        if (file_exists($file)) {
            require $file;
        }
    });
}

// Check for Meta Box dependency
function wpcc_check_dependencies() {
    if (!defined('RWMB_VER')) {
        add_action('admin_notices', function() {
            echo '<div class="error"><p>' . 
                 sprintf(
                     __('WP Custom Content requires Meta Box to function. Please %sinstall and activate Meta Box%s.', 'wp-custom-content'),
                     '<a href="' . admin_url('plugin-install.php?tab=search&s=meta-box') . '">',
                     '</a>'
                 ) . 
                 '</p></div>';
        });
        return false;
    }
    return true;
}

// Initialize plugin
function wpcc_init() {
    if (!wpcc_check_dependencies()) {
        return;
    }
    
    try {
        return WPCustomContent\Plugin::get_instance();
    } catch (\Exception $e) {
        add_action('admin_notices', function() use ($e) {
            echo '<div class="error"><p>' . 
                 esc_html__('WP Custom Content failed to initialize: ', 'wp-custom-content') . 
                 esc_html($e->getMessage()) . 
                 '</p></div>';
        });
    }
}

// Initialize on plugins loaded
add_action('plugins_loaded', 'wpcc_init');
