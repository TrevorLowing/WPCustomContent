<?php
/**
 * GPT Prompt Library
 *
 * @package WPCustomContent\Integrations
 */

namespace WPCustomContent\Integrations;

/**
 * Manages GPT prompts library and pinned prompts
 */
class GptPromptLibrary {
    /** @var string Option name for prompt library */
    const LIBRARY_OPTION = 'wpcc_gpt_prompt_library';

    /** @var string Option name for pinned prompts */
    const PINNED_OPTION = 'wpcc_gpt_pinned_prompts';

    /** @var array Default prompt categories */
    private $categories = [
        'title' => 'Title Generation',
        'content' => 'Content Analysis',
        'excerpt' => 'Excerpt Creation',
        'meta' => 'Metadata Generation',
    ];

    /**
     * Initialize the library
     */
    public function __construct() {
        add_action('admin_init', [$this, 'register_settings']);
        add_action('wp_ajax_wpcc_save_prompt', [$this, 'ajax_save_prompt']);
        add_action('wp_ajax_wpcc_pin_prompt', [$this, 'ajax_pin_prompt']);
        add_action('wp_ajax_wpcc_delete_prompt', [$this, 'ajax_delete_prompt']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('wpcc_settings', self::LIBRARY_OPTION);
        register_setting('wpcc_settings', self::PINNED_OPTION);
    }

    /**
     * Enqueue assets
     */
    public function enqueue_assets() {
        $screen = get_current_screen();
        if (!$screen || !in_array($screen->id, ['post', 'wpcc_content', 'settings_page_wpcc-settings'])) {
            return;
        }

        wp_enqueue_style(
            'wpcc-prompt-library',
            plugins_url('assets/css/prompt-library.css', WPCC_PLUGIN_FILE),
            [],
            WPCC_VERSION
        );

        wp_enqueue_script(
            'wpcc-prompt-library',
            plugins_url('assets/js/prompt-library.js', WPCC_PLUGIN_FILE),
            ['jquery', 'wp-util'],
            WPCC_VERSION,
            true
        );

        wp_localize_script('wpcc-prompt-library', 'wpccPromptLibrary', [
            'nonce' => wp_create_nonce('wpcc_prompt_library'),
            'strings' => [
                'saveSuccess' => __('Prompt saved successfully', 'wp-custom-content'),
                'pinSuccess' => __('Prompt pinned successfully', 'wp-custom-content'),
                'deleteSuccess' => __('Prompt deleted successfully', 'wp-custom-content'),
                'error' => __('An error occurred', 'wp-custom-content'),
            ],
        ]);
    }

    /**
     * Get all prompts
     *
     * @return array Prompts array
     */
    public function get_prompts(): array {
        return get_option(self::LIBRARY_OPTION, []);
    }

    /**
     * Get pinned prompts
     *
     * @return array Pinned prompts array
     */
    public function get_pinned_prompts(): array {
        return get_option(self::PINNED_OPTION, []);
    }

    /**
     * Save a prompt
     *
     * @param array $prompt Prompt data
     * @return int|false Prompt ID on success, false on failure
     */
    public function save_prompt(array $prompt) {
        if (empty($prompt['content']) || empty($prompt['category'])) {
            return false;
        }

        $prompts = $this->get_prompts();
        $id = time() . '_' . wp_generate_password(6, false);
        
        $prompts[$id] = [
            'id' => $id,
            'content' => sanitize_textarea_field($prompt['content']),
            'category' => sanitize_key($prompt['category']),
            'title' => sanitize_text_field($prompt['title'] ?? ''),
            'description' => sanitize_textarea_field($prompt['description'] ?? ''),
            'created_by' => get_current_user_id(),
            'created_at' => current_time('mysql'),
            'variables' => $this->extract_variables($prompt['content']),
        ];

        if (update_option(self::LIBRARY_OPTION, $prompts)) {
            return $id;
        }

        return false;
    }

    /**
     * Pin/unpin a prompt
     *
     * @param string $prompt_id Prompt ID
     * @param string $field     Field to pin for
     * @return bool Success status
     */
    public function toggle_pin(string $prompt_id, string $field): bool {
        $pinned = $this->get_pinned_prompts();
        
        if (isset($pinned[$field]) && $pinned[$field] === $prompt_id) {
            unset($pinned[$field]);
        } else {
            $pinned[$field] = $prompt_id;
        }

        return update_option(self::PINNED_OPTION, $pinned);
    }

    /**
     * Delete a prompt
     *
     * @param string $prompt_id Prompt ID
     * @return bool Success status
     */
    public function delete_prompt(string $prompt_id): bool {
        $prompts = $this->get_prompts();
        if (!isset($prompts[$prompt_id])) {
            return false;
        }

        unset($prompts[$prompt_id]);
        
        // Also remove from pinned prompts
        $pinned = $this->get_pinned_prompts();
        foreach ($pinned as $field => $pinned_id) {
            if ($pinned_id === $prompt_id) {
                unset($pinned[$field]);
            }
        }

        update_option(self::PINNED_OPTION, $pinned);
        return update_option(self::LIBRARY_OPTION, $prompts);
    }

    /**
     * Extract variables from prompt content
     *
     * @param string $content Prompt content
     * @return array Variables list
     */
    private function extract_variables(string $content): array {
        preg_match_all('/\{([^}]+)\}/', $content, $matches);
        return array_unique($matches[1]);
    }

    /**
     * AJAX handler for saving prompts
     */
    public function ajax_save_prompt() {
        check_ajax_referer('wpcc_prompt_library', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }

        $prompt = filter_input_array(INPUT_POST, [
            'content' => FILTER_SANITIZE_STRING,
            'category' => FILTER_SANITIZE_STRING,
            'title' => FILTER_SANITIZE_STRING,
            'description' => FILTER_SANITIZE_STRING,
        ]);

        $id = $this->save_prompt($prompt);
        if ($id) {
            wp_send_json_success(['id' => $id]);
        }

        wp_send_json_error('Failed to save prompt');
    }

    /**
     * AJAX handler for pinning prompts
     */
    public function ajax_pin_prompt() {
        check_ajax_referer('wpcc_prompt_library', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }

        $prompt_id = filter_input(INPUT_POST, 'prompt_id', FILTER_SANITIZE_STRING);
        $field = filter_input(INPUT_POST, 'field', FILTER_SANITIZE_STRING);

        if ($this->toggle_pin($prompt_id, $field)) {
            wp_send_json_success();
        }

        wp_send_json_error('Failed to pin prompt');
    }

    /**
     * AJAX handler for deleting prompts
     */
    public function ajax_delete_prompt() {
        check_ajax_referer('wpcc_prompt_library', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }

        $prompt_id = filter_input(INPUT_POST, 'prompt_id', FILTER_SANITIZE_STRING);

        if ($this->delete_prompt($prompt_id)) {
            wp_send_json_success();
        }

        wp_send_json_error('Failed to delete prompt');
    }

    /**
     * Render prompt library UI
     *
     * @param string $field Field name
     */
    public function render_library_ui(string $field) {
        $prompts = $this->get_prompts();
        $pinned = $this->get_pinned_prompts();
        $current_pinned = $pinned[$field] ?? '';
        ?>
        <div class="wpcc-prompt-library" data-field="<?php echo esc_attr($field); ?>">
            <div class="wpcc-prompt-library-header">
                <h3><?php _e('Prompt Library', 'wp-custom-content'); ?></h3>
                <button type="button" class="button wpcc-add-prompt">
                    <?php _e('Add New Prompt', 'wp-custom-content'); ?>
                </button>
            </div>

            <?php if ($current_pinned && isset($prompts[$current_pinned])): ?>
                <div class="wpcc-pinned-prompt">
                    <h4><?php _e('Current Pinned Prompt', 'wp-custom-content'); ?></h4>
                    <div class="wpcc-prompt-card pinned">
                        <div class="wpcc-prompt-content">
                            <?php echo esc_html($prompts[$current_pinned]['content']); ?>
                        </div>
                        <div class="wpcc-prompt-actions">
                            <button type="button" class="button wpcc-unpin-prompt" 
                                    data-id="<?php echo esc_attr($current_pinned); ?>">
                                <?php _e('Unpin', 'wp-custom-content'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="wpcc-prompt-list">
                <?php foreach ($this->categories as $cat_key => $cat_label): ?>
                    <div class="wpcc-prompt-category">
                        <h4><?php echo esc_html($cat_label); ?></h4>
                        <?php
                        $cat_prompts = array_filter($prompts, function($prompt) use ($cat_key) {
                            return $prompt['category'] === $cat_key;
                        });
                        
                        if (empty($cat_prompts)): ?>
                            <p class="wpcc-no-prompts">
                                <?php _e('No prompts in this category', 'wp-custom-content'); ?>
                            </p>
                        <?php else: foreach ($cat_prompts as $prompt): ?>
                            <div class="wpcc-prompt-card" data-id="<?php echo esc_attr($prompt['id']); ?>">
                                <?php if (!empty($prompt['title'])): ?>
                                    <div class="wpcc-prompt-title">
                                        <?php echo esc_html($prompt['title']); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="wpcc-prompt-content">
                                    <?php echo esc_html($prompt['content']); ?>
                                </div>
                                <?php if (!empty($prompt['description'])): ?>
                                    <div class="wpcc-prompt-description">
                                        <?php echo esc_html($prompt['description']); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="wpcc-prompt-actions">
                                    <button type="button" class="button wpcc-use-prompt">
                                        <?php _e('Use', 'wp-custom-content'); ?>
                                    </button>
                                    <button type="button" class="button wpcc-pin-prompt" 
                                            data-id="<?php echo esc_attr($prompt['id']); ?>"
                                            <?php echo $current_pinned === $prompt['id'] ? 'disabled' : ''; ?>>
                                        <?php _e('Pin', 'wp-custom-content'); ?>
                                    </button>
                                    <button type="button" class="button wpcc-delete-prompt" 
                                            data-id="<?php echo esc_attr($prompt['id']); ?>">
                                        <?php _e('Delete', 'wp-custom-content'); ?>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="wpcc-prompt-modal" class="wpcc-modal" style="display: none;">
                <div class="wpcc-modal-content">
                    <h3><?php _e('Add New Prompt', 'wp-custom-content'); ?></h3>
                    <form id="wpcc-prompt-form">
                        <p>
                            <label>
                                <?php _e('Title (optional)', 'wp-custom-content'); ?>
                                <input type="text" name="title" class="widefat">
                            </label>
                        </p>
                        <p>
                            <label>
                                <?php _e('Category', 'wp-custom-content'); ?>
                                <select name="category" required>
                                    <?php foreach ($this->categories as $key => $label): ?>
                                        <option value="<?php echo esc_attr($key); ?>">
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </p>
                        <p>
                            <label>
                                <?php _e('Prompt Content', 'wp-custom-content'); ?>
                                <textarea name="content" class="widefat" rows="5" required></textarea>
                            </label>
                        </p>
                        <p>
                            <label>
                                <?php _e('Description (optional)', 'wp-custom-content'); ?>
                                <textarea name="description" class="widefat" rows="3"></textarea>
                            </label>
                        </p>
                        <p class="wpcc-modal-actions">
                            <button type="submit" class="button button-primary">
                                <?php _e('Save Prompt', 'wp-custom-content'); ?>
                            </button>
                            <button type="button" class="button wpcc-modal-close">
                                <?php _e('Cancel', 'wp-custom-content'); ?>
                            </button>
                        </p>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
}
