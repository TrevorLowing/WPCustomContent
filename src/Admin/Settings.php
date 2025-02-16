<?php
/**
 * Admin Settings class
 *
 * @package WPCustomContent\Admin
 */

namespace WPCustomContent\Admin;

/**
 * Settings class for managing plugin options
 */
class Settings {
    private const OPTION_NAME = 'wpcc_settings';
    private $defaults;
    private static $instance = null;
    private $registered = false;

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
     * Initialize the settings
     */
    private function __construct() {
        $this->defaults = [
            'enable_embedpress' => false,
            'enable_pdf_embedder' => false,
            'gpt_trainer_api_key' => '',
            'gpt_trainer_endpoint' => 'https://api.gpttrainer.com/v1',
            'gpt_analysis_enabled' => true,
            'gpt_auto_analyze' => false,
            'gpt_model' => 'gpt-4',
            'gpt_temperature' => 0.7,
            'enable_logging' => true,
            'log_level' => 'ERROR',
            'log_retention_days' => 30,
            'enable_error_notifications' => true,
            'notification_email' => get_option('admin_email'),
            'notification_levels' => ['ERROR', 'CRITICAL'],
        ];

        // Only add hooks if WordPress is loaded and we're in admin
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_head', [$this, 'add_help_tabs']);
    }

    /**
     * Register settings
     */
    public function register_settings() {
        // Ensure we're in WordPress admin context
        if (!defined('ABSPATH')) {
            return;
        }

        // Prevent duplicate registration
        if ($this->registered) {
            return;
        }

        // Load WordPress admin functions if not already loaded
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        require_once ABSPATH . 'wp-includes/pluggable.php';
        require_once ABSPATH . 'wp-admin/includes/template.php';

        $this->registered = true;

        register_setting(
            'wpcc_settings',
            self::OPTION_NAME,
            [$this, 'sanitize_settings']
        );

        // Integration Settings Section
        add_settings_section(
            'wpcc_integrations',
            __('Integration Settings', 'wp-custom-content'),
            [$this, 'render_integrations_section'],
            'wpcc-settings'
        );

        add_settings_field(
            'enable_embedpress',
            __('Enable EmbedPress', 'wp-custom-content'),
            [$this, 'render_checkbox_field'],
            'wpcc-settings',
            'wpcc_integrations',
            ['field' => 'enable_embedpress']
        );

        add_settings_field(
            'enable_pdf_embedder',
            __('Enable PDF Embedder', 'wp-custom-content'),
            [$this, 'render_checkbox_field'],
            'wpcc-settings',
            'wpcc_integrations',
            ['field' => 'enable_pdf_embedder']
        );

        // GPT Trainer Settings Section
        add_settings_section(
            'wpcc_gpt_trainer',
            __('GPT Trainer Settings', 'wp-custom-content'),
            [$this, 'render_gpt_trainer_section'],
            'wpcc-settings'
        );

        add_settings_field(
            'gpt_trainer_api_key',
            __('API Key', 'wp-custom-content'),
            [$this, 'render_password_field'],
            'wpcc-settings',
            'wpcc_gpt_trainer',
            ['field' => 'gpt_trainer_api_key']
        );

        add_settings_field(
            'gpt_trainer_endpoint',
            __('API Endpoint', 'wp-custom-content'),
            [$this, 'render_text_field'],
            'wpcc-settings',
            'wpcc_gpt_trainer',
            ['field' => 'gpt_trainer_endpoint']
        );

        add_settings_field(
            'gpt_analysis_enabled',
            __('Enable Content Analysis', 'wp-custom-content'),
            [$this, 'render_checkbox_field'],
            'wpcc-settings',
            'wpcc_gpt_trainer',
            ['field' => 'gpt_analysis_enabled']
        );

        add_settings_field(
            'gpt_auto_analyze',
            __('Auto-analyze New Content', 'wp-custom-content'),
            [$this, 'render_checkbox_field'],
            'wpcc-settings',
            'wpcc_gpt_trainer',
            ['field' => 'gpt_auto_analyze']
        );

        add_settings_field(
            'gpt_model',
            __('GPT Model', 'wp-custom-content'),
            [$this, 'render_select_field'],
            'wpcc-settings',
            'wpcc_gpt_trainer',
            [
                'field' => 'gpt_model',
                'options' => [
                    'gpt-4' => 'GPT-4',
                    'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
                ]
            ]
        );

        add_settings_field(
            'gpt_temperature',
            __('Temperature', 'wp-custom-content'),
            [$this, 'render_number_field'],
            'wpcc-settings',
            'wpcc_gpt_trainer',
            [
                'field' => 'gpt_temperature',
                'min' => 0,
                'max' => 1,
                'step' => 0.1
            ]
        );

        // Logging Settings Section
        add_settings_section(
            'wpcc_logging',
            __('Logging', 'wp-custom-content'),
            [$this, 'render_logging_section'],
            'wpcc-settings'
        );

        add_settings_field(
            'enable_logging',
            __('Enable Logging', 'wp-custom-content'),
            [$this, 'render_checkbox_field'],
            'wpcc-settings',
            'wpcc_logging',
            ['field' => 'enable_logging']
        );

        add_settings_field(
            'log_level',
            __('Log Level', 'wp-custom-content'),
            [$this, 'render_select_field'],
            'wpcc-settings',
            'wpcc_logging',
            [
                'field' => 'log_level',
                'options' => [
                    'DEBUG' => 'Debug',
                    'INFO' => 'Info',
                    'WARNING' => 'Warning',
                    'ERROR' => 'Error',
                ]
            ]
        );

        add_settings_field(
            'log_retention_days',
            __('Log Retention Days', 'wp-custom-content'),
            [$this, 'render_number_field'],
            'wpcc-settings',
            'wpcc_logging',
            [
                'field' => 'log_retention_days',
                'min' => 1,
                'max' => 365,
                'step' => 1
            ]
        );

        // Notification Settings Section
        add_settings_section(
            'wpcc_notifications',
            __('Notification Settings', 'wp-custom-content'),
            [$this, 'render_notification_section'],
            'wpcc-settings'
        );

        add_settings_field(
            'enable_error_notifications',
            __('Enable Error Notifications', 'wp-custom-content'),
            [$this, 'render_checkbox_field'],
            'wpcc-settings',
            'wpcc_notifications',
            ['field' => 'enable_error_notifications']
        );

        add_settings_field(
            'notification_email',
            __('Notification Email', 'wp-custom-content'),
            [$this, 'render_text_field'],
            'wpcc-settings',
            'wpcc_notifications',
            ['field' => 'notification_email']
        );

        add_settings_field(
            'notification_levels',
            __('Notification Levels', 'wp-custom-content'),
            [$this, 'render_multiselect_field'],
            'wpcc-settings',
            'wpcc_notifications',
            [
                'field' => 'notification_levels',
                'options' => [
                    'DEBUG' => 'Debug',
                    'INFO' => 'Info',
                    'WARNING' => 'Warning',
                    'ERROR' => 'Error',
                    'CRITICAL' => 'Critical',
                ]
            ]
        );
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        ?>
        <div class="wrap">
            <h2><?php echo esc_html__('WP Custom Content Settings', 'wp-custom-content'); ?></h2>
            <form action="options.php" method="post">
                <?php
                settings_fields('wpcc_settings');
                do_settings_sections('wpcc-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render integrations section
     */
    public function render_integrations_section() {
        echo '<p>' . esc_html__('Configure integration settings with third-party plugins.', 'wp-custom-content') . '</p>';
        
        // Check plugin dependencies
        $dependencies = $this->check_dependencies();
        
        echo '<div class="wpcc-dependencies">';
        echo '<h4>' . esc_html__('Plugin Dependencies', 'wp-custom-content') . '</h4>';
        echo '<table class="widefat" style="margin-bottom: 20px;">';
        echo '<thead><tr>';
        echo '<th>' . esc_html__('Plugin', 'wp-custom-content') . '</th>';
        echo '<th>' . esc_html__('Status', 'wp-custom-content') . '</th>';
        echo '<th>' . esc_html__('Required For', 'wp-custom-content') . '</th>';
        echo '</tr></thead><tbody>';
        
        foreach ($dependencies as $plugin) {
            $status_class = $plugin['active'] ? 'active' : 'inactive';
            echo '<tr>';
            echo '<td>' . esc_html($plugin['name']) . '</td>';
            echo '<td><span class="wpcc-status ' . esc_attr($status_class) . '">' . 
                 esc_html($plugin['active'] ? __('Active', 'wp-custom-content') : __('Not Installed/Inactive', 'wp-custom-content')) . 
                 '</span></td>';
            echo '<td>' . esc_html($plugin['required_for']) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
        echo '</div>';
    }

    /**
     * Check plugin dependencies
     * 
     * @return array Array of dependencies and their status
     */
    private function check_dependencies() {
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        return [
            [
                'name' => 'Meta Box',
                'active' => is_plugin_active('meta-box/meta-box.php'),
                'required_for' => __('Required - Core functionality', 'wp-custom-content'),
            ],
            [
                'name' => 'EmbedPress',
                'active' => is_plugin_active('embedpress/embedpress.php'),
                'required_for' => __('Optional - Enhanced media embedding', 'wp-custom-content'),
            ],
            [
                'name' => 'PDF Embedder',
                'active' => is_plugin_active('pdf-embedder/pdf-embedder.php'),
                'required_for' => __('Optional - PDF document display', 'wp-custom-content'),
            ],
        ];
    }

    /**
     * Check plugin installation and activation status
     * 
     * @param string $plugin_file Plugin file path relative to plugins directory
     * @return array Status information
     */
    private function check_plugin_status($plugin_file) {
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $installed = file_exists(WP_PLUGIN_DIR . '/' . $plugin_file);
        $active = is_plugin_active($plugin_file);

        return [
            'installed' => $installed,
            'active' => $active
        ];
    }

    /**
     * Render GPT Trainer section
     */
    public function render_gpt_trainer_section() {
        echo '<p>' . esc_html__('Configure GPT Trainer API settings for content analysis.', 'wp-custom-content') . '</p>';
    }

    /**
     * Render logging section
     */
    public function render_logging_section() {
        echo '<p>' . esc_html__('Configure logging options for the plugin.', 'wp-custom-content') . '</p>';
    }

    /**
     * Render notification section
     */
    public function render_notification_section() {
        echo '<p>' . esc_html__('Configure notification options for the plugin.', 'wp-custom-content') . '</p>';
    }

    /**
     * Render checkbox field
     */
    public function render_checkbox_field($args) {
        $field = $args['field'];
        $options = $this->get_options();
        
        // Check if this is an integration field
        $is_integration = in_array($field, ['enable_embedpress', 'enable_pdf_embedder']);
        $disabled = false;
        $disabled_message = '';
        $force_unchecked = false;
        
        if ($is_integration) {
            // Get plugin file and status based on field
            $plugin_file = $field === 'enable_embedpress' ? 'embedpress/embedpress.php' : 'pdf-embedder/pdf-embedder.php';
            $plugin_name = $field === 'enable_embedpress' ? 'EmbedPress' : 'PDF Embedder';
            $status = $this->check_plugin_status($plugin_file);

            if (!$status['installed']) {
                $disabled = true;
                $force_unchecked = true;
                $disabled_message = sprintf(
                    __('%s plugin must be installed first', 'wp-custom-content'),
                    $plugin_name
                );
            } elseif (!$status['active']) {
                $disabled = true;
                $force_unchecked = true;
                $disabled_message = sprintf(
                    __('%s plugin is installed but needs to be activated', 'wp-custom-content'),
                    $plugin_name
                );
            }
        }

        // If plugin is not installed or not active, force the option to be false
        if ($force_unchecked) {
            $options[$field] = false;
            update_option(self::OPTION_NAME, $options);
        }

        $value = isset($options[$field]) ? $options[$field] : false;

        printf(
            '<label><input type="checkbox" name="%s[%s]" value="1" %s %s> %s</label>%s',
            esc_attr(self::OPTION_NAME),
            esc_attr($field),
            checked($value, true, false),
            $disabled ? 'disabled="disabled"' : '',
            esc_html__('Enable', 'wp-custom-content'),
            $disabled_message ? '<p class="description" style="color: #dc3545;">' . esc_html($disabled_message) . '</p>' : ''
        );
    }

    /**
     * Render text field
     */
    public function render_text_field($args) {
        $settings = get_option(self::OPTION_NAME, $this->defaults);
        $field = $args['field'];
        ?>
        <input type="text" 
               class="regular-text"
               name="<?php echo esc_attr(self::OPTION_NAME . '[' . $field . ']'); ?>"
               value="<?php echo esc_attr($settings[$field] ?? ''); ?>">
        <?php
    }

    /**
     * Render password field
     */
    public function render_password_field($args) {
        $settings = get_option(self::OPTION_NAME, $this->defaults);
        $field = $args['field'];
        ?>
        <input type="password" 
               class="regular-text"
               name="<?php echo esc_attr(self::OPTION_NAME . '[' . $field . ']'); ?>"
               value="<?php echo esc_attr($settings[$field] ?? ''); ?>">
        <?php
    }

    /**
     * Render select field
     */
    public function render_select_field($args) {
        $settings = get_option(self::OPTION_NAME, $this->defaults);
        $field = $args['field'];
        $options = $args['options'];
        ?>
        <select name="<?php echo esc_attr(self::OPTION_NAME . '[' . $field . ']'); ?>">
            <?php foreach ($options as $value => $label) : ?>
                <option value="<?php echo esc_attr($value); ?>"
                        <?php selected($settings[$field] ?? '', $value); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * Render multiselect field
     */
    public function render_multiselect_field($args) {
        $settings = get_option(self::OPTION_NAME, $this->defaults);
        $field = $args['field'];
        $options = $args['options'];
        ?>
        <select name="<?php echo esc_attr(self::OPTION_NAME . '[' . $field . '][]'); ?>" multiple>
            <?php foreach ($options as $value => $label) : ?>
                <option value="<?php echo esc_attr($value); ?>"
                        <?php selected(in_array($value, $settings[$field] ?? [])); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    /**
     * Render number field
     */
    public function render_number_field($args) {
        $settings = get_option(self::OPTION_NAME, $this->defaults);
        $field = $args['field'];
        ?>
        <input type="number" 
               class="small-text"
               name="<?php echo esc_attr(self::OPTION_NAME . '[' . $field . ']'); ?>"
               value="<?php echo esc_attr($settings[$field] ?? ''); ?>"
               min="<?php echo esc_attr($args['min'] ?? 0); ?>"
               max="<?php echo esc_attr($args['max'] ?? 100); ?>"
               step="<?php echo esc_attr($args['step'] ?? 1); ?>">
        <?php
    }

    /**
     * Add help tabs to the settings page
     */
    public function add_help_tabs() {
        $screen = get_current_screen();
        
        if (!$screen || $screen->id !== 'toplevel_page_wpcc-settings') {
            return;
        }

        $screen->add_help_tab([
            'id' => 'wpcc_general_help',
            'title' => __('General Settings', 'wp-custom-content'),
            'content' => '<h2>' . __('General Settings Help', 'wp-custom-content') . '</h2>' .
                        '<p>' . __('Configure the core settings for WP Custom Content Library. Each setting includes a detailed description of its purpose and impact.', 'wp-custom-content') . '</p>'
        ]);

        $screen->add_help_tab([
            'id' => 'wpcc_gpt_help',
            'title' => __('GPT Settings', 'wp-custom-content'),
            'content' => '<h2>' . __('GPT Settings Help', 'wp-custom-content') . '</h2>' .
                        '<p>' . __('Configure how the GPT-powered content analysis works. The temperature setting affects how creative vs. precise the analysis will be.', 'wp-custom-content') . '</p>'
        ]);

        $screen->add_help_tab([
            'id' => 'wpcc_logging_help',
            'title' => __('Logging', 'wp-custom-content'),
            'content' => '<h2>' . __('Logging Help', 'wp-custom-content') . '</h2>' .
                        '<p>' . __('Configure how the plugin logs events and errors. Higher log levels will capture more information but use more storage.', 'wp-custom-content') . '</p>'
        ]);

        $screen->add_help_tab([
            'id' => 'wpcc_notifications_help',
            'title' => __('Notifications', 'wp-custom-content'),
            'content' => '<h2>' . __('Notifications Help', 'wp-custom-content') . '</h2>' .
                        '<p>' . __('Configure how the plugin sends notifications for errors and other events.', 'wp-custom-content') . '</p>'
        ]);
    }

    /**
     * Check if an integration is enabled
     *
     * @param string $integration Integration name (e.g., 'embedpress', 'pdf_embedder')
     * @return bool Whether the integration is enabled
     */
    public function is_integration_enabled($integration) {
        $options = $this->get_options();
        $key = 'enable_' . $integration;
        return isset($options[$key]) ? (bool) $options[$key] : false;
    }

    /**
     * Check if EmbedPress is active
     *
     * @return bool Whether EmbedPress is active
     */
    public function is_embedpress_active() {
        return defined('EMBEDPRESS_IS_LOADED') && EMBEDPRESS_IS_LOADED;
    }

    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        if (!is_array($input)) {
            return $this->defaults;
        }

        $output = $this->get_options();

        // Handle integration settings
        $integrations = [
            'enable_embedpress' => 'embedpress/embedpress.php',
            'enable_pdf_embedder' => 'pdf-embedder/pdf-embedder.php'
        ];

        foreach ($integrations as $option => $plugin) {
            if (isset($input[$option])) {
                $status = $this->check_plugin_status($plugin);
                if ($status['installed'] && $status['active']) {
                    $output[$option] = (bool) $input[$option];
                } else {
                    $output[$option] = false;
                }
            } else {
                $output[$option] = false;
            }
        }

        // Handle other settings...
        foreach ($input as $key => $value) {
            if (!array_key_exists($key, $integrations)) {
                $output[$key] = $this->sanitize_setting($key, $value);
            }
        }

        return $output;
    }

    /**
     * Sanitize individual setting
     */
    private function sanitize_setting($key, $value) {
        switch ($key) {
            case 'gpt_trainer_api_key':
            case 'gpt_trainer_endpoint':
                return sanitize_text_field($value);
            case 'gpt_temperature':
                return floatval($value);
            case 'log_retention_days':
                return intval($value);
            case 'notification_levels':
                return is_array($value) ? array_map('sanitize_text_field', $value) : ['ERROR'];
            default:
                return (bool) $value;
        }
    }

    /**
     * Get options
     *
     * @return array Options
     */
    private function get_options() {
        return get_option(self::OPTION_NAME, $this->defaults);
    }

    /**
     * Get all plugin options
     *
     * @return array Plugin options with defaults applied
     */
    public function get_all_options() {
        return $this->get_options();
    }
}
