<?php
/**
 * Main plugin class
 *
 * @package WPCustomContent
 */

namespace WPCustomContent;

use WPCustomContent\Admin\MenuManager;
use WPCustomContent\Admin\Settings;
use WPCustomContent\Admin\SystemStatus;
use WPCustomContent\Admin\LogViewer;
use WPCustomContent\Logger\Logger;
use WPCustomContent\Notifications\EmailNotifier;
use WPCustomContent\PostTypes\ContentPostType;
use WPCustomContent\VersionManager;

/**
 * Main plugin class
 */
class Plugin {
    private static $instance = null;
    private $settings;
    private $logger;
    private $content_post_type;
    private $menu_manager;
    private $system_status;
    private $log_viewer;
    private $email_notifier;
    private $version_manager;

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
     * Initialize the plugin
     */
    private function __construct() {
        $this->logger = Logger::get_instance();
        $this->settings = Settings::get_instance();
        $this->menu_manager = MenuManager::get_instance();
        $this->system_status = SystemStatus::get_instance();
        $this->log_viewer = LogViewer::get_instance();
        $this->email_notifier = EmailNotifier::get_instance();
        $this->version_manager = VersionManager::get_instance();

        // Initialize components
        $this->init_components();
        $this->add_hooks();

        // Log initialization with version info
        $this->logger->log('INFO', 'WP Custom Content plugin initialized', [
            'version' => WP_CUSTOM_CONTENT_VERSION,
            'test_version' => $this->version_manager->get_test_version(),
            'test_count' => $this->version_manager->get_test_count(),
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version')
        ]);
    }

    /**
     * Initialize plugin components
     */
    private function init_components() {
        // Initialize content post type
        $this->content_post_type = ContentPostType::get_instance();
    }

    /**
     * Add hooks
     */
    private function add_hooks() {
        // Add activation hook
        register_activation_hook(WP_CUSTOM_CONTENT_PLUGIN_FILE, [$this, 'activate']);
        
        // Add deactivation hook
        register_deactivation_hook(WP_CUSTOM_CONTENT_PLUGIN_FILE, [$this, 'deactivate']);
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables if needed
        $this->logger->create_tables();
        
        // Log activation
        $this->logger->log('INFO', 'Plugin activated', [
            'version' => WP_CUSTOM_CONTENT_VERSION,
            'test_version' => $this->version_manager->get_test_version()
        ]);
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Log deactivation
        $this->logger->log('INFO', 'Plugin deactivated', [
            'version' => WP_CUSTOM_CONTENT_VERSION,
            'test_version' => $this->version_manager->get_test_version()
        ]);
    }
}
