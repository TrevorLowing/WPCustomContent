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
    /**
     * Option name in wp_options table
     *
     * @var string
     */
    const OPTION_NAME = 'wpcc_settings';

    /**
     * Default settings
     *
     * @var array
     */
    private $defaults = [
        'enable_embedpress' => false,
        'enable_pdf_embedder' => false,
        'gpt_trainer_api_key' => '',
        'gpt_trainer_endpoint' => 'https://api.gpttrainer.com/v1',
        'gpt_analysis_enabled' => true,
        'gpt_auto_analyze' => false,
        'gpt_model' => 'gpt-4',
        'gpt_temperature' => 0.7,
    ];

    /**
     * Initialize the settings
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Add settings page to admin menu
     */
    public function add_settings_page() {
        add_menu_page(
            __('WP Custom Content', 'wp-custom-content'),  // Menu title
            'manage_options',
            'wpcc-settings',  
            [$this, 'render_settings_page'],
            'dashicons-admin-page',
            25
        );

        add_submenu_page(
            'wpcc-settings',
            __('Settings', 'wp-custom-content'),
            'manage_options',
            'wpcc-settings'
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        // Prevent duplicate registration
        static $registered = false;
        if ($registered) {
            return;
        }
        $registered = true;

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
        echo '<p>' . esc_html__('Configure optional integrations with other plugins.', 'wp-custom-content') . '</p>';
    }

    /**
     * Render GPT Trainer section
     */
    public function render_gpt_trainer_section() {
        echo '<p>' . esc_html__('Configure GPT Trainer API settings for content analysis.', 'wp-custom-content') . '</p>';
    }

    /**
     * Render checkbox field
     */
    public function render_checkbox_field($args) {
        $settings = get_option(self::OPTION_NAME, $this->defaults);
        $field = $args['field'];
        ?>
        <label>
            <input type="checkbox" 
                   name="<?php echo esc_attr(self::OPTION_NAME . '[' . $field . ']'); ?>"
                   value="1"
                   <?php checked(isset($settings[$field]) ? $settings[$field] : false); ?>>
            <?php echo esc_html__('Enable', 'wp-custom-content'); ?>
        </label>
        <?php
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
        $sanitized = [];

        // Sanitize checkboxes
        $checkboxes = ['enable_embedpress', 'enable_pdf_embedder', 'gpt_analysis_enabled', 'gpt_auto_analyze'];
        foreach ($checkboxes as $checkbox) {
            $sanitized[$checkbox] = isset($input[$checkbox]) ? true : false;
        }

        // Sanitize text fields
        if (isset($input['gpt_trainer_endpoint'])) {
            $sanitized['gpt_trainer_endpoint'] = esc_url_raw($input['gpt_trainer_endpoint']);
        }

        // Sanitize API key
        if (isset($input['gpt_trainer_api_key'])) {
            $sanitized['gpt_trainer_api_key'] = sanitize_text_field($input['gpt_trainer_api_key']);
        }

        // Sanitize select fields
        if (isset($input['gpt_model'])) {
            $sanitized['gpt_model'] = sanitize_text_field($input['gpt_model']);
        }

        // Sanitize number fields
        if (isset($input['gpt_temperature'])) {
            $sanitized['gpt_temperature'] = floatval($input['gpt_temperature']);
            if ($sanitized['gpt_temperature'] < 0) $sanitized['gpt_temperature'] = 0;
            if ($sanitized['gpt_temperature'] > 1) $sanitized['gpt_temperature'] = 1;
        }

        return $sanitized;
    }

    /**
     * Get options
     *
     * @return array Options
     */
    private function get_options() {
        return get_option(self::OPTION_NAME, $this->defaults);
    }
}
