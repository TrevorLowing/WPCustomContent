<?php
/**
 * System Status admin page
 *
 * @package WPCustomContent\Admin
 */

namespace WPCustomContent\Admin;

use WPCustomContent\Logger\Logger;

/**
 * System Status class for displaying system information and diagnostics
 */
class SystemStatus {
    private static $instance = null;
    private $logger;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize the system status
     */
    private function __construct() {
        $this->logger = Logger::get_instance();
        add_action('admin_menu', [$this, 'add_menu_item']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_wpcc_export_logs', [$this, 'export_logs']);
        add_action('wp_ajax_wpcc_export_settings', [$this, 'export_settings']);
        add_action('wp_ajax_wpcc_import_settings', [$this, 'import_settings']);
        add_action('wp_ajax_wpcc_run_diagnostics', [$this, 'run_diagnostics']);
    }

    /**
     * Add menu item
     */
    public function add_menu_item() {
        add_submenu_page(
            'wpcc-settings',
            __('System Status', 'wp-custom-content'),
            __('System Status', 'wp-custom-content'),
            'manage_options',
            'wpcc-status',
            [$this, 'render_page']
        );
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts($hook) {
        if ('wp-custom-content_page_wpcc-status' !== $hook) {
            return;
        }

        wp_enqueue_style('wpcc-admin');
        wp_enqueue_script(
            'wpcc-system-status',
            plugins_url('assets/js/system-status.js', WP_CUSTOM_CONTENT_PLUGIN_FILE),
            ['jquery'],
            WP_CUSTOM_CONTENT_VERSION,
            true
        );

        wp_localize_script('wpcc-system-status', 'wpccSystemStatus', [
            'nonce' => wp_create_nonce('wpcc_system_status'),
            'ajaxUrl' => admin_url('admin-ajax.php'),
        ]);
    }

    /**
     * Render the system status page
     */
    public function render_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $system_info = $this->get_system_info();
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('System Status', 'wp-custom-content'); ?></h1>

            <div class="wpcc-status-actions">
                <button class="button" id="wpcc-export-logs">
                    <?php echo esc_html__('Export Logs', 'wp-custom-content'); ?>
                </button>
                <button class="button" id="wpcc-export-settings">
                    <?php echo esc_html__('Export Settings', 'wp-custom-content'); ?>
                </button>
                <button class="button" id="wpcc-import-settings">
                    <?php echo esc_html__('Import Settings', 'wp-custom-content'); ?>
                </button>
                <button class="button" id="wpcc-run-diagnostics">
                    <?php echo esc_html__('Run Diagnostics', 'wp-custom-content'); ?>
                </button>
            </div>

            <div id="wpcc-diagnostics-results" style="display: none;"></div>

            <div class="wpcc-status-section">
                <h2><?php echo esc_html__('WordPress Environment', 'wp-custom-content'); ?></h2>
                <table class="widefat" cellspacing="0">
                    <tbody>
                        <?php foreach ($system_info['wordpress'] as $key => $value) : ?>
                            <tr>
                                <td><?php echo esc_html($key); ?></td>
                                <td><?php echo esc_html($value); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="wpcc-status-section">
                <h2><?php echo esc_html__('Server Environment', 'wp-custom-content'); ?></h2>
                <table class="widefat" cellspacing="0">
                    <tbody>
                        <?php foreach ($system_info['server'] as $key => $value) : ?>
                            <tr>
                                <td><?php echo esc_html($key); ?></td>
                                <td><?php echo esc_html($value); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="wpcc-status-section">
                <h2><?php echo esc_html__('Plugin Status', 'wp-custom-content'); ?></h2>
                <table class="widefat" cellspacing="0">
                    <tbody>
                        <?php foreach ($system_info['plugin'] as $key => $value) : ?>
                            <tr>
                                <td><?php echo esc_html($key); ?></td>
                                <td><?php echo esc_html($value); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    /**
     * Get system information
     */
    private function get_system_info() {
        global $wpdb;

        return [
            'wordpress' => [
                'WordPress Version' => get_bloginfo('version'),
                'Site URL' => get_site_url(),
                'Home URL' => get_home_url(),
                'Is Multisite' => is_multisite() ? 'Yes' : 'No',
                'Max Upload Size' => size_format(wp_max_upload_size()),
                'Memory Limit' => WP_MEMORY_LIMIT,
                'Permalink Structure' => get_option('permalink_structure') ?: 'Default',
                'Language' => get_locale(),
                'Debug Mode' => defined('WP_DEBUG') && WP_DEBUG ? 'Enabled' : 'Disabled',
            ],
            'server' => [
                'PHP Version' => PHP_VERSION,
                'MySQL Version' => $wpdb->db_version(),
                'Web Server' => $_SERVER['SERVER_SOFTWARE'],
                'PHP Memory Limit' => ini_get('memory_limit'),
                'PHP Time Limit' => ini_get('max_execution_time'),
                'PHP Max Input Vars' => ini_get('max_input_vars'),
                'PHP Post Max Size' => ini_get('post_max_size'),
                'cURL Version' => function_exists('curl_version') ? curl_version()['version'] : 'Not Installed',
                'OpenSSL Version' => defined('OPENSSL_VERSION_TEXT') ? OPENSSL_VERSION_TEXT : 'Not Available',
            ],
            'plugin' => [
                'Version' => WP_CUSTOM_CONTENT_VERSION,
                'Database Version' => get_option('wpcc_db_version', 'Not Set'),
                'Logging Enabled' => Settings::get_instance()->get_options()['enable_logging'] ? 'Yes' : 'No',
                'Log Retention' => Settings::get_instance()->get_options()['log_retention_days'] . ' days',
                'GPT Integration' => !empty(Settings::get_instance()->get_options()['gpt_trainer_api_key']) ? 'Configured' : 'Not Configured',
                'Last Error' => $this->get_last_error(),
            ],
        ];
    }

    /**
     * Get the last error from logs
     */
    private function get_last_error() {
        $logs = $this->logger->get_logs([
            'level' => 'ERROR',
            'limit' => 1,
        ]);

        if (!empty($logs)) {
            $log = reset($logs);
            return sprintf(
                '%s: %s',
                wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log->timestamp)),
                $log->message
            );
        }

        return 'No recent errors';
    }

    /**
     * Export logs
     */
    public function export_logs() {
        check_ajax_referer('wpcc_system_status', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(-1);
        }

        $logs = $this->logger->get_logs(['limit' => 1000]);
        $filename = 'wpcc-logs-' . date('Y-m-d') . '.json';

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');

        echo wp_json_encode($logs, JSON_PRETTY_PRINT);
        wp_die();
    }

    /**
     * Export settings
     */
    public function export_settings() {
        check_ajax_referer('wpcc_system_status', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(-1);
        }

        $settings = Settings::get_instance()->get_options();
        $filename = 'wpcc-settings-' . date('Y-m-d') . '.json';

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');

        echo wp_json_encode($settings, JSON_PRETTY_PRINT);
        wp_die();
    }

    /**
     * Import settings
     */
    public function import_settings() {
        check_ajax_referer('wpcc_system_status', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        if (!isset($_FILES['settings_file'])) {
            wp_send_json_error('No file uploaded');
        }

        $file = $_FILES['settings_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error('File upload failed');
        }

        $content = file_get_contents($file['tmp_name']);
        $settings = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error('Invalid JSON file');
        }

        update_option(Settings::OPTION_NAME, $settings);
        $this->logger->log('INFO', 'Settings imported successfully');

        wp_send_json_success('Settings imported successfully');
    }

    /**
     * Run diagnostics
     */
    public function run_diagnostics() {
        check_ajax_referer('wpcc_system_status', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $results = [];

        // Check file permissions
        $upload_dir = wp_upload_dir();
        $results['upload_dir'] = [
            'name' => 'Upload Directory',
            'status' => is_writable($upload_dir['basedir']) ? 'success' : 'error',
            'message' => is_writable($upload_dir['basedir']) 
                ? 'Writable' 
                : 'Not writable - please check permissions',
        ];

        // Check API connectivity
        $settings = Settings::get_instance()->get_options();
        if (!empty($settings['gpt_trainer_api_key'])) {
            $response = wp_remote_get($settings['gpt_trainer_endpoint']);
            $results['api_connection'] = [
                'name' => 'API Connection',
                'status' => !is_wp_error($response) ? 'success' : 'error',
                'message' => !is_wp_error($response) 
                    ? 'Connected successfully' 
                    : 'Connection failed: ' . $response->get_error_message(),
            ];
        }

        // Check database tables
        global $wpdb;
        $table_name = $wpdb->prefix . Logger::LOG_TABLE;
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        $results['database'] = [
            'name' => 'Database Tables',
            'status' => $table_exists ? 'success' : 'error',
            'message' => $table_exists 
                ? 'All required tables exist' 
                : 'Missing required tables - please deactivate and reactivate the plugin',
        ];

        // Log the diagnostics results
        $this->logger->log('INFO', 'Diagnostics completed', [
            'results' => $results,
        ]);

        wp_send_json_success($results);
    }
}
