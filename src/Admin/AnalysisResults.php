<?php
/**
 * Analysis Results Display
 *
 * @package WPCustomContent\Admin
 */

namespace WPCustomContent\Admin;

use WPCustomContent\PostTypes\ContentPostType;

/**
 * Manages the display of GPT analysis results
 */
class AnalysisResults {
    /**
     * Initialize the display
     */
    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_wpcc_analyze_content', [$this, 'handle_manual_analysis']);
        add_filter('manage_' . ContentPostType::POST_TYPE . '_posts_columns', [$this, 'add_columns']);
        add_action('manage_' . ContentPostType::POST_TYPE . '_posts_custom_column', [$this, 'render_column'], 10, 2);
        add_filter('bulk_actions-edit-' . ContentPostType::POST_TYPE, [$this, 'add_bulk_actions']);
        add_filter('handle_bulk_actions-edit-' . ContentPostType::POST_TYPE, [$this, 'handle_bulk_actions'], 10, 3);
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'wpcc_analysis_results',
            __('Content Analysis Results', 'wp-custom-content'),
            [$this, 'render_meta_box'],
            ContentPostType::POST_TYPE,
            'normal',
            'high'
        );
    }

    /**
     * Enqueue assets
     */
    public function enqueue_assets() {
        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== ContentPostType::POST_TYPE) {
            return;
        }

        wp_enqueue_style(
            'wpcc-analysis',
            plugins_url('assets/css/analysis-results.css', WPCC_PLUGIN_FILE),
            [],
            WPCC_VERSION
        );

        wp_enqueue_script(
            'wpcc-analysis',
            plugins_url('assets/js/analysis-results.js', WPCC_PLUGIN_FILE),
            ['jquery', 'wp-util'],
            WPCC_VERSION,
            true
        );

        wp_localize_script('wpcc-analysis', 'wpccAnalysis', [
            'nonce' => wp_create_nonce('wpcc_analysis'),
            'strings' => [
                'analyzing' => __('Analyzing content...', 'wp-custom-content'),
                'success' => __('Analysis complete', 'wp-custom-content'),
                'error' => __('Analysis failed', 'wp-custom-content'),
            ],
        ]);
    }

    /**
     * Render meta box
     *
     * @param \WP_Post $post Post object
     */
    public function render_meta_box($post) {
        $analysis = $this->get_latest_analysis($post->ID);
        ?>
        <div class="wpcc-analysis-results">
            <div class="wpcc-analysis-header">
                <h3><?php _e('Analysis Results', 'wp-custom-content'); ?></h3>
                <button type="button" class="button wpcc-analyze-content" data-post-id="<?php echo esc_attr($post->ID); ?>">
                    <?php _e('Analyze Now', 'wp-custom-content'); ?>
                </button>
            </div>

            <div class="wpcc-analysis-content">
                <?php if ($analysis): ?>
                    <div class="wpcc-analysis-timestamp">
                        <?php printf(
                            /* translators: %s: datetime */
                            __('Last analyzed: %s', 'wp-custom-content'),
                            wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($analysis->created_at))
                        ); ?>
                    </div>

                    <div class="wpcc-analysis-data">
                        <?php $this->render_analysis_data(json_decode($analysis->analysis_data, true)); ?>
                    </div>
                <?php else: ?>
                    <p class="wpcc-no-analysis">
                        <?php _e('No analysis results available. Click "Analyze Now" to analyze this content.', 'wp-custom-content'); ?>
                    </p>
                <?php endif; ?>
            </div>

            <div class="wpcc-analysis-loading" style="display: none;">
                <span class="spinner is-active"></span>
                <?php _e('Analyzing content...', 'wp-custom-content'); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render analysis data
     *
     * @param array $data Analysis data
     */
    private function render_analysis_data($data) {
        if (!is_array($data)) {
            return;
        }

        echo '<div class="wpcc-analysis-sections">';

        // Summary section
        if (!empty($data['summary'])) {
            echo '<div class="wpcc-analysis-section">';
            echo '<h4>' . esc_html__('Summary', 'wp-custom-content') . '</h4>';
            echo '<p>' . esc_html($data['summary']) . '</p>';
            echo '</div>';
        }

        // Key points
        if (!empty($data['key_points'])) {
            echo '<div class="wpcc-analysis-section">';
            echo '<h4>' . esc_html__('Key Points', 'wp-custom-content') . '</h4>';
            echo '<ul>';
            foreach ($data['key_points'] as $point) {
                echo '<li>' . esc_html($point) . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }

        // Suggestions
        if (!empty($data['suggestions'])) {
            echo '<div class="wpcc-analysis-section">';
            echo '<h4>' . esc_html__('Suggestions', 'wp-custom-content') . '</h4>';
            echo '<ul class="wpcc-suggestions">';
            foreach ($data['suggestions'] as $suggestion) {
                echo '<li>' . esc_html($suggestion) . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }

        // Metadata
        if (!empty($data['metadata'])) {
            echo '<div class="wpcc-analysis-section">';
            echo '<h4>' . esc_html__('Metadata', 'wp-custom-content') . '</h4>';
            echo '<dl class="wpcc-metadata">';
            foreach ($data['metadata'] as $key => $value) {
                echo '<dt>' . esc_html($key) . '</dt>';
                echo '<dd>' . esc_html($value) . '</dd>';
            }
            echo '</dl>';
            echo '</div>';
        }

        echo '</div>';
    }

    /**
     * Get latest analysis
     *
     * @param int $post_id Post ID
     * @return object|null Analysis object or null
     */
    private function get_latest_analysis($post_id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}wpcc_gpt_analysis 
            WHERE post_id = %d 
            ORDER BY created_at DESC 
            LIMIT 1",
            $post_id
        ));
    }

    /**
     * Handle manual analysis request
     */
    public function handle_manual_analysis() {
        check_ajax_referer('wpcc_analysis', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Permission denied');
        }

        $post_id = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }

        try {
            $post = get_post($post_id);
            $content_type = get_post_meta($post_id, 'content_type', true);

            // Initialize API
            $api = new \PiperPrivacySorn\Services\GptTrainerApi();
            
            // Get prompt from library
            $library = new \WPCustomContent\Integrations\GptPromptLibrary();
            $pinned = $library->get_pinned_prompts();
            $prompts = $library->get_prompts();
            
            $prompt = '';
            if (!empty($pinned['content']) && isset($prompts[$pinned['content']])) {
                $prompt = $prompts[$pinned['content']]['content'];
            } else {
                // Fallback to default prompt
                $prompt = "Analyze this {$content_type} content: {content}";
            }

            // Prepare content
            $content = [
                'title' => $post->post_title,
                'content' => $post->post_content,
                'excerpt' => $post->post_excerpt,
                'meta' => get_post_meta($post_id),
            ];

            // Get analysis
            $response = $api->analyze_content($prompt, $content);

            // Save analysis
            global $wpdb;
            $wpdb->insert(
                $wpdb->prefix . 'wpcc_gpt_analysis',
                [
                    'post_id' => $post_id,
                    'analysis_type' => 'content',
                    'analysis_data' => wp_json_encode($response),
                    'created_at' => current_time('mysql'),
                ],
                ['%d', '%s', '%s', '%s']
            );

            wp_send_json_success([
                'html' => $this->get_analysis_html($response),
            ]);

        } catch (\Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * Get analysis HTML
     *
     * @param array $data Analysis data
     * @return string HTML
     */
    private function get_analysis_html($data) {
        ob_start();
        $this->render_analysis_data($data);
        return ob_get_clean();
    }

    /**
     * Add custom columns
     *
     * @param array $columns Existing columns
     * @return array Modified columns
     */
    public function add_columns($columns) {
        $new_columns = [];
        
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'title') {
                $new_columns['analysis_status'] = __('Analysis', 'wp-custom-content');
            }
        }
        
        return $new_columns;
    }

    /**
     * Render custom column
     *
     * @param string $column  Column name
     * @param int    $post_id Post ID
     */
    public function render_column($column, $post_id) {
        if ($column !== 'analysis_status') {
            return;
        }

        $analysis = $this->get_latest_analysis($post_id);
        if ($analysis) {
            echo '<span class="wpcc-analysis-status analyzed" title="' . 
                esc_attr(sprintf(
                    /* translators: %s: datetime */
                    __('Last analyzed: %s', 'wp-custom-content'),
                    wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($analysis->created_at))
                )) . 
                '">' . esc_html__('Analyzed', 'wp-custom-content') . '</span>';
        } else {
            echo '<span class="wpcc-analysis-status not-analyzed">' . 
                esc_html__('Not Analyzed', 'wp-custom-content') . 
                '</span>';
        }
    }

    /**
     * Add bulk actions
     *
     * @param array $actions Existing actions
     * @return array Modified actions
     */
    public function add_bulk_actions($actions) {
        $actions['analyze'] = __('Analyze Content', 'wp-custom-content');
        return $actions;
    }

    /**
     * Handle bulk actions
     *
     * @param string $redirect_url Redirect URL
     * @param string $action       Action name
     * @param array  $post_ids     Post IDs
     * @return string Modified redirect URL
     */
    public function handle_bulk_actions($redirect_url, $action, $post_ids) {
        if ($action !== 'analyze') {
            return $redirect_url;
        }

        $analyzed = 0;
        $failed = 0;

        foreach ($post_ids as $post_id) {
            try {
                $post = get_post($post_id);
                if (!$post || $post->post_type !== ContentPostType::POST_TYPE) {
                    continue;
                }

                // Trigger analysis
                do_action('wpcc_analyze_content', $post_id);
                $analyzed++;

            } catch (\Exception $e) {
                $failed++;
            }
        }

        $redirect_url = add_query_arg([
            'analyzed' => $analyzed,
            'failed' => $failed,
        ], $redirect_url);

        return $redirect_url;
    }
}
