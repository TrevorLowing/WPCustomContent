<?php
/**
 * Menu Manager class
 *
 * @package WPCustomContent\Admin
 */

namespace WPCustomContent\Admin;

/**
 * Menu Manager class for handling admin menu registration
 */
class MenuManager {
    private static $instance = null;
    private $parent_slug = 'wpcc-settings';
    private $capability = 'manage_options';

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
     * Initialize the menu manager
     */
    private function __construct() {
        add_action('admin_menu', [$this, 'register_menus']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_filter('plugin_action_links', [$this, 'add_plugin_action_links'], 10, 2);
    }

    /**
     * Register all admin menus
     */
    public function register_menus() {
        if (!is_admin() || !function_exists('add_menu_page')) {
            return;
        }

        // Main menu
        add_menu_page(
            __('WP Custom Content', 'wp-custom-content'),
            __('WP Custom Content', 'wp-custom-content'),
            $this->capability,
            $this->parent_slug,
            [$this, 'render_settings_page'],
            'dashicons-admin-page',
            25
        );

        // Settings submenu (same as parent to avoid duplicate)
        add_submenu_page(
            $this->parent_slug,
            __('Settings', 'wp-custom-content'),
            __('Settings', 'wp-custom-content'),
            $this->capability,
            $this->parent_slug,
            [$this, 'render_settings_page']
        );

        // System Status submenu
        add_submenu_page(
            $this->parent_slug,
            __('System Status', 'wp-custom-content'),
            __('System Status', 'wp-custom-content'),
            $this->capability,
            'wpcc-status',
            [SystemStatus::get_instance(), 'render_page']
        );

        // Logs submenu
        add_submenu_page(
            $this->parent_slug,
            __('System Logs', 'wp-custom-content'),
            __('System Logs', 'wp-custom-content'),
            $this->capability,
            'wpcc-logs',
            [LogViewer::get_instance(), 'render_page']
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only load on our admin pages
        if (strpos($hook, 'wp-custom-content') === false) {
            return;
        }

        // Admin styles
        wp_enqueue_style(
            'wpcc-admin-style',
            plugins_url('assets/css/admin.css', WP_CUSTOM_CONTENT_PLUGIN_FILE),
            [],
            WP_CUSTOM_CONTENT_VERSION
        );

        // System Status page specific assets
        if ('wp-custom-content_page_wpcc-status' === $hook) {
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
                'i18n' => [
                    'confirmImport' => __('Are you sure you want to import these settings? This will override your current settings.', 'wp-custom-content'),
                    'importSuccess' => __('Settings imported successfully.', 'wp-custom-content'),
                    'importError' => __('Failed to import settings. Please try again.', 'wp-custom-content'),
                    'runningDiagnostics' => __('Running diagnostics...', 'wp-custom-content'),
                ]
            ]);
        }
    }

    /**
     * Add plugin action links
     */
    public function add_plugin_action_links($links, $plugin) {
        if (plugin_basename(WP_CUSTOM_CONTENT_PLUGIN_FILE) === $plugin) {
            $settings_link = sprintf(
                '<a href="%s">%s</a>',
                admin_url('admin.php?page=' . $this->parent_slug),
                __('Settings', 'wp-custom-content')
            );
            array_unshift($links, $settings_link);
        }
        return $links;
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        Settings::get_instance()->render_settings_page();
    }
}
