<?php
/**
 * GPT Trainer Integration
 *
 * @package WPCustomContent\Integrations
 */

namespace WPCustomContent\Integrations;

use WPCustomContent\PostTypes\ContentPostType;

/**
 * Manages GPT Trainer integration
 */
class GptTrainer {
    /** @var string Option name for settings */
    const OPTION_NAME = 'wpcc_gpt_trainer_settings';

    /** @var array Default settings */
    private $defaults = [
        'api_token' => '',
        'is_test_mode' => false,
        'auto_train' => false,
        'content_prompts' => [
            'document' => 'Analyze this document: {content}',
            'video' => 'Describe this video content: {content}',
            'presentation' => 'Summarize this presentation: {content}',
            'audio' => 'Transcribe and analyze this audio: {content}',
        ],
    ];

    /**
     * Initialize integration
     */
    public function __construct() {
        add_action('admin_init', [$this, 'register_settings']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post_' . ContentPostType::POST_TYPE, [$this, 'handle_content_save'], 10, 3);
        add_filter('wpcc_settings_tabs', [$this, 'add_settings_tab']);
        add_action('wpcc_settings_tab_gpt_trainer', [$this, 'render_settings_tab']);
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(
            'wpcc_settings',
            self::OPTION_NAME,
            [
                'type' => 'array',
                'sanitize_callback' => [$this, 'sanitize_settings'],
            ]
        );

        add_settings_section(
            'wpcc_gpt_trainer',
            __('GPT Trainer Settings', 'wp-custom-content'),
            [$this, 'render_settings_section'],
            'wpcc-settings'
        );

        // API Settings
        add_settings_field(
            'api_token',
            __('API Token', 'wp-custom-content'),
            [$this, 'render_api_token_field'],
            'wpcc-settings',
            'wpcc_gpt_trainer'
        );

        add_settings_field(
            'is_test_mode',
            __('Test Mode', 'wp-custom-content'),
            [$this, 'render_test_mode_field'],
            'wpcc-settings',
            'wpcc_gpt_trainer'
        );

        // Content Type Prompts
        add_settings_field(
            'content_prompts',
            __('Content Type Prompts', 'wp-custom-content'),
            [$this, 'render_content_prompts_field'],
            'wpcc-settings',
            'wpcc_gpt_trainer'
        );
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'wpcc_gpt_trainer',
            __('GPT Trainer Analysis', 'wp-custom-content'),
            [$this, 'render_meta_box'],
            ContentPostType::POST_TYPE,
            'normal',
            'default'
        );
    }

    /**
     * Handle content save
     *
     * @param int      $post_id Post ID
     * @param \WP_Post $post    Post object
     * @param bool     $update  Whether this is an update
     */
    public function handle_content_save($post_id, $post, $update) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $settings = get_option(self::OPTION_NAME, $this->defaults);
        if (empty($settings['api_token'])) {
            return;
        }

        // Get content type and prompt
        $content_type = get_post_meta($post_id, 'content_type', true);
        if (!$content_type || empty($settings['content_prompts'][$content_type])) {
            return;
        }

        // Prepare content for analysis
        $content = [
            'title' => $post->post_title,
            'content' => $post->post_content,
            'excerpt' => $post->post_excerpt,
            'meta' => get_post_meta($post_id),
        ];

        try {
            // Initialize API
            $api = new \PiperPrivacySorn\Services\GptTrainerApi();
            
            // Send content for analysis
            $response = $api->analyze_content(
                $settings['content_prompts'][$content_type],
                $content
            );

            // Store analysis results
            update_post_meta($post_id, '_wpcc_gpt_analysis', $response);
            
        } catch (\Exception $e) {
            // Log error but don't disrupt save process
            error_log(sprintf(
                '[WP Custom Content] GPT Trainer analysis failed for post %d: %s',
                $post_id,
                $e->getMessage()
            ));
        }
    }

    /**
     * Add settings tab
     *
     * @param array $tabs Existing tabs
     * @return array Modified tabs
     */
    public function add_settings_tab($tabs) {
        $tabs['gpt_trainer'] = __('GPT Trainer', 'wp-custom-content');
        return $tabs;
    }

    /**
     * Render settings tab
     */
    public function render_settings_tab() {
        $settings = get_option(self::OPTION_NAME, $this->defaults);
        ?>
        <div class="wpcc-settings-section">
            <h2><?php _e('GPT Trainer Integration', 'wp-custom-content'); ?></h2>
            <p><?php _e('Configure GPT Trainer API integration for content analysis.', 'wp-custom-content'); ?></p>
            
            <form method="post" action="options.php">
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
     * Render settings section
     */
    public function render_settings_section() {
        ?>
        <p><?php _e('Configure your GPT Trainer API settings and content type prompts.', 'wp-custom-content'); ?></p>
        <?php
    }

    /**
     * Render API token field
     */
    public function render_api_token_field() {
        $settings = get_option(self::OPTION_NAME, $this->defaults);
        ?>
        <input type="password" 
               name="<?php echo esc_attr(self::OPTION_NAME); ?>[api_token]" 
               value="<?php echo esc_attr($settings['api_token']); ?>"
               class="regular-text">
        <p class="description">
            <?php _e('Enter your GPT Trainer API token. Keep this secure!', 'wp-custom-content'); ?>
        </p>
        <?php
    }

    /**
     * Render test mode field
     */
    public function render_test_mode_field() {
        $settings = get_option(self::OPTION_NAME, $this->defaults);
        ?>
        <label>
            <input type="checkbox" 
                   name="<?php echo esc_attr(self::OPTION_NAME); ?>[is_test_mode]" 
                   value="1" 
                   <?php checked($settings['is_test_mode']); ?>>
            <?php _e('Enable test mode', 'wp-custom-content'); ?>
        </label>
        <p class="description">
            <?php _e('Use test mode for development and testing.', 'wp-custom-content'); ?>
        </p>
        <?php
    }

    /**
     * Render content prompts field
     */
    public function render_content_prompts_field() {
        $settings = get_option(self::OPTION_NAME, $this->defaults);
        $content_types = [
            'document' => __('Document', 'wp-custom-content'),
            'video' => __('Video', 'wp-custom-content'),
            'presentation' => __('Presentation', 'wp-custom-content'),
            'audio' => __('Audio', 'wp-custom-content'),
        ];

        foreach ($content_types as $type => $label) {
            ?>
            <div class="wpcc-prompt-field">
                <label>
                    <strong><?php echo esc_html($label); ?></strong>
                    <textarea name="<?php echo esc_attr(self::OPTION_NAME); ?>[content_prompts][<?php echo esc_attr($type); ?>]"
                              class="large-text"
                              rows="3"><?php echo esc_textarea($settings['content_prompts'][$type] ?? ''); ?></textarea>
                </label>
            </div>
            <?php
        }
        ?>
        <p class="description">
            <?php _e('Configure prompts for each content type. Use {content} placeholder for the actual content.', 'wp-custom-content'); ?>
        </p>
        <?php
    }

    /**
     * Sanitize settings
     *
     * @param array $input Input array
     * @return array Sanitized array
     */
    public function sanitize_settings($input) {
        $output = $this->defaults;

        if (is_array($input)) {
            // Sanitize API token
            $output['api_token'] = sanitize_text_field($input['api_token'] ?? '');
            
            // Sanitize test mode
            $output['is_test_mode'] = !empty($input['is_test_mode']);
            
            // Sanitize content prompts
            if (!empty($input['content_prompts']) && is_array($input['content_prompts'])) {
                foreach ($input['content_prompts'] as $type => $prompt) {
                    if (isset($this->defaults['content_prompts'][$type])) {
                        $output['content_prompts'][$type] = sanitize_textarea_field($prompt);
                    }
                }
            }
        }

        return $output;
    }
}
