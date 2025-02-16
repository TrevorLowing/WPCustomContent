<?php
/**
 * Content Post Type class
 * 
 * Manages the custom post type for content library items with Meta Box integration
 * and optional support for EmbedPress and PDF Embedder.
 * 
 * Features:
 * - Custom post type registration with proper capabilities
 * - Meta Box integration for field management
 * - Optional integrations with EmbedPress and PDF Embedder
 * - Remote file handling and version tracking
 * 
 * @package WPCustomContent\PostTypes
 */

namespace WPCustomContent\PostTypes;

use WP_Error;
use WPCustomContent\Admin\Settings;
use WPCustomContent\Logger\Logger;

/**
 * Content Post Type class
 */
class ContentPostType {
    /**
     * Post type name
     *
     * @var string
     */
    const POST_TYPE = 'wpcc_content';

    /**
     * Settings instance
     *
     * @var Settings
     */
    private $settings;

    /**
     * Cached meta box configuration
     *
     * @var array
     */
    private $meta_box_config;

    /**
     * Singleton instance
     *
     * @var self
     */
    private static $instance = null;

    /**
     * Get singleton instance
     *
     * @return self
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize the post type
     */
    private function __construct() {
        $this->settings = Settings::get_instance();
        
        // Register hooks
        add_action('init', [$this, 'register_post_type']);
        add_filter('rwmb_meta_boxes', [$this, 'register_meta_boxes']);
        add_action('save_post_' . self::POST_TYPE, [$this, 'handle_content_save'], 10, 3);
        add_filter('the_content', [$this, 'display_content']);
        add_filter('bricks/builder/i18n', [$this, 'add_bricks_translations']);
        add_filter('bricks/builder/supported_post_types', [$this, 'add_bricks_post_type']);
        add_filter('bricks/setup_theme', [$this, 'enable_bricks_template']);
        
        // Initialize meta box config cache
        $this->meta_box_config = null;
    }

    /**
     * Register the custom post type
     */
    public function register_post_type() {
        $labels = [
            'name'               => __('Custom Content', 'wp-custom-content'),
            'singular_name'      => __('Custom Content', 'wp-custom-content'),
            'menu_name'          => __('All Content', 'wp-custom-content'),
            'add_new'            => __('Add New', 'wp-custom-content'),
            'add_new_item'       => __('Add New Content', 'wp-custom-content'),
            'edit_item'          => __('Edit Content', 'wp-custom-content'),
            'new_item'           => __('New Content', 'wp-custom-content'),
            'view_item'          => __('View Content', 'wp-custom-content'),
            'search_items'       => __('Search Content', 'wp-custom-content'),
            'not_found'          => __('No content found', 'wp-custom-content'),
            'not_found_in_trash' => __('No content found in Trash', 'wp-custom-content'),
        ];
     
        $args = [
            'labels'              => $labels,
            'public'              => true,
            'show_ui'            => true,
            'show_in_menu'       => 'wpcc-settings', // Parent menu slug
            'show_in_admin_bar'  => true,
            'menu_position'      => 25,
            'menu_icon'          => 'dashicons-admin-page',
            'capability_type'    => 'post',
            'hierarchical'       => false,
            'supports'           => ['title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'],
            'has_archive'        => true,
            'rewrite'           => ['slug' => 'custom-content'],
            'show_in_rest'      => true,
        ];

        register_post_type(self::POST_TYPE, $args);
    }

    /**
     * Register meta boxes using Meta Box plugin
     * 
     * @param array $meta_boxes Existing meta boxes
     * @return array Updated meta boxes
     */
    public function register_meta_boxes($meta_boxes) {
        if ($this->meta_box_config === null) {
            $this->meta_box_config = $this->get_meta_box_config();
        }
        
        return array_merge($meta_boxes, $this->meta_box_config);
    }

    /**
     * Get meta box configuration
     * 
     * @return array Meta box configuration
     */
    private function get_meta_box_config() {
        $meta_boxes = [
            [
                'title'      => __('Content Details', 'wp-custom-content'),
                'id'         => 'wpcc_content_details',
                'post_types' => [self::POST_TYPE],
                'context'    => 'normal',
                'priority'   => 'high',
                'fields'     => [
                    [
                        'name'    => __('Content Type', 'wp-custom-content'),
                        'id'      => 'content_type',
                        'type'    => 'select',
                        'options' => [
                            'document'     => __('Document', 'wp-custom-content'),
                            'video'        => __('Video', 'wp-custom-content'),
                            'presentation' => __('Presentation', 'wp-custom-content'),
                            'audio'        => __('Audio', 'wp-custom-content'),
                            'other'        => __('Other', 'wp-custom-content'),
                        ],
                        'required' => true,
                    ],
                    [
                        'name'       => __('File URL', 'wp-custom-content'),
                        'id'         => 'file_url',
                        'type'       => 'url',
                        'desc'       => __('URL to the remote file', 'wp-custom-content'),
                    ],
                    [
                        'name'       => __('File Version', 'wp-custom-content'),
                        'id'         => 'file_version',
                        'type'       => 'text',
                        'desc'       => __('Version identifier for this content', 'wp-custom-content'),
                    ],
                    [
                        'name'       => __('Last Updated', 'wp-custom-content'),
                        'id'         => 'last_updated',
                        'type'       => 'date',
                        'timestamp'  => true,
                    ],
                ],
            ],
        ];

        // Add EmbedPress meta box if enabled
        if ($this->settings->is_integration_enabled('embedpress') && $this->settings->is_embedpress_active()) {
            $meta_boxes[] = [
                'title'      => __('EmbedPress Settings', 'wp-custom-content'),
                'id'         => 'wpcc_embedpress',
                'post_types' => [self::POST_TYPE],
                'context'    => 'side',
                'priority'   => 'default',
                'fields'     => [
                    [
                        'name'    => __('EmbedPress Shortcode', 'wp-custom-content'),
                        'id'      => 'embedpress_shortcode',
                        'type'    => 'text',
                        'desc'    => __('Enter the EmbedPress shortcode for this content', 'wp-custom-content'),
                    ],
                ],
            ];
        }

        return $meta_boxes;
    }

    /**
     * Handle remote file download and storage
     *
     * @param string   $url     The remote file URL.
     * @param int|null $post_id Optional post ID to attach file to.
     * @return int|WP_Error Attachment ID on success, WP_Error on failure.
     */
    public function handle_remote_file($url, $post_id = null) {
        $logger = Logger::get_instance();

        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            $logger->log('ERROR', 'Invalid URL provided', [
                'url' => $url
            ]);
            return new WP_Error('invalid_url', __('Invalid URL provided', 'wp-custom-content'));
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        // Download file to temp dir
        $temp_file = download_url($url);

        if (is_wp_error($temp_file)) {
            $logger->log('ERROR', 'Failed to download file', [
                'url' => $url,
                'error' => $temp_file->get_error_message()
            ]);
            return $temp_file;
        }

        // Get WordPress upload directory
        $upload_dir = wp_upload_dir();
        $target_dir = $upload_dir['basedir'] . '/wpcc-content';
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            wp_mkdir_p($target_dir);
        }

        // Get file info
        $file_info = wp_check_filetype(basename($url));
        if (empty($file_info['ext'])) {
            @unlink($temp_file);
            $logger->log('ERROR', 'Invalid file type', [
                'url' => $url
            ]);
            return new WP_Error('invalid_file', __('Invalid file type', 'wp-custom-content'));
        }

        // Prepare file array
        $file = [
            'name'     => basename($url),
            'type'     => $file_info['type'],
            'tmp_name' => $temp_file,
            'error'    => 0,
            'size'     => filesize($temp_file),
        ];

        // Use WordPress file handling
        $overrides = [
            'test_form' => false,
            'test_size' => true,
        ];

        // Move file to uploads
        $results = wp_handle_sideload($file, $overrides);

        if (!empty($results['error'])) {
            @unlink($temp_file);
            $logger->log('ERROR', 'Failed to upload file', [
                'url' => $url,
                'error' => $results['error']
            ]);
            return new WP_Error('upload_error', $results['error']);
        }

        $filename  = $results['file'];
        $file_type = $results['type'];
        $file_url  = $results['url'];

        // Prepare attachment data
        $attachment = [
            'post_mime_type' => $file_type,
            'post_title'     => preg_replace('/\.[^.]+$/', '', basename($filename)),
            'post_content'   => '',
            'post_status'    => 'inherit',
            'guid'           => $file_url,
        ];

        // Insert attachment
        $attach_id = wp_insert_attachment($attachment, $filename, $post_id);

        if (is_wp_error($attach_id)) {
            @unlink($filename);
            $logger->log('ERROR', 'Failed to insert attachment', [
                'url' => $url,
                'error' => $attach_id->get_error_message()
            ]);
            return $attach_id;
        }

        // Generate metadata and thumbnails
        if (strpos($file_type, 'image/') === 0) {
            $attach_data = wp_generate_attachment_metadata($attach_id, $filename);
            wp_update_attachment_metadata($attach_id, $attach_data);
            
            // Store original URL as attachment metadata
            update_post_meta($attach_id, '_wpcc_original_url', $url);
            update_post_meta($attach_id, '_wpcc_download_date', current_time('mysql'));
        }

        $logger->log('INFO', 'File uploaded successfully', [
            'url' => $url,
            'file' => basename($filename)
        ]);

        return $attach_id;
    }

    /**
     * Handle content saving
     *
     * @param int     $post_id Post ID.
     * @param WP_Post $post    Post object.
     * @param bool    $update  Whether this is an existing post being updated.
     */
    public function handle_content_save($post_id, $post, $update) {
        $logger = Logger::get_instance();

        // Skip autosaves and revisions
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Get meta values
        $content_type = rwmb_meta('content_type');
        $original_url = rwmb_meta('file_url');

        if (!$content_type || !$original_url) {
            return;
        }

        // Handle PDF documents
        if ($content_type === 'document') {
            $attachment_id = $this->handle_remote_file($original_url, $post_id);
            
            if ($attachment_id && !is_wp_error($attachment_id)) {
                // Store the attachment ID
                update_post_meta($post_id, '_wpcc_local_file_id', $attachment_id);
                
                // Get the local file URL for display
                $local_url = wp_get_attachment_url($attachment_id);
                update_post_meta($post_id, '_wpcc_local_file_url', $local_url);
                
                // Update post content for Bricks compatibility
                $post_content = sprintf(
                    '[pdf-embedder url="%s"]',
                    esc_url($local_url)
                );
                
                // Update the post
                wp_update_post([
                    'ID'           => $post_id,
                    'post_content' => $post_content,
                ]);

                $logger->log('INFO', 'Content saved successfully', [
                    'post_id' => $post_id,
                    'content_type' => $content_type
                ]);
            }
        }
    }

    /**
     * Display content with EmbedPress integration
     *
     * @param string $content Post content.
     * @return string Modified content.
     */
    public function display_content($content) {
        $logger = Logger::get_instance();

        if (is_singular(self::POST_TYPE)) {
            global $post;
            
            if (!$post || $post->post_type !== self::POST_TYPE) {
                return $content;
            }
            
            $post_id = $post->ID;
            
            // Get meta values
            $content_type = rwmb_meta('content_type');
            $original_url = rwmb_meta('file_url');
            $local_file_id = get_post_meta($post_id, '_wpcc_local_file_id', true);
            $embedpress_shortcode = rwmb_meta('embedpress_shortcode');
            $summary = rwmb_meta('summary');
            $tagline = rwmb_meta('tagline');
            
            ob_start();
            
            // Display tagline if exists
            if ($tagline) {
                printf('<div class="content-tagline">%s</div>', esc_html($tagline));
            }
            
            // Display summary if exists
            if ($summary) {
                printf('<div class="content-summary">%s</div>', wp_kses_post($summary));
            }
            
            // Display content based on type
            switch ($content_type) {
                case 'video':
                    if ($embedpress_shortcode) {
                        echo do_shortcode($embedpress_shortcode);
                    } elseif ($original_url) {
                        printf('[embedpress]%s[/embedpress]', esc_url($original_url));
                    }
                    break;
                    
                case 'document':
                    if ($local_file_id) {
                        $local_url = wp_get_attachment_url($local_file_id);
                        if ($local_url) {
                            printf('[pdf-embedder url="%s"]', esc_url($local_url));
                        }
                    }
                    break;
                    
                default:
                    echo $content;
                    break;
            }
            
            $logger->log('INFO', 'Content displayed successfully', [
                'post_id' => $post_id,
                'content_type' => $content_type
            ]);

            return ob_get_clean();
        }
        
        return $content;
    }

    /**
     * Add Bricks Builder translations
     *
     * @param array $i18n Translations array.
     * @return array Modified translations array.
     */
    public function add_bricks_translations($i18n) {
        $logger = Logger::get_instance();

        $i18n['content'] = esc_html__('Content Library', 'wp-custom-content');
        $logger->log('INFO', 'Bricks translations added', [
            'translations' => $i18n
        ]);

        return $i18n;
    }

    /**
     * Add post type to Bricks Builder
     *
     * @param array $post_types Post types array.
     * @return array Modified post types array.
     */
    public function add_bricks_post_type($post_types) {
        $logger = Logger::get_instance();

        $post_types[] = self::POST_TYPE;
        $logger->log('INFO', 'Post type added to Bricks Builder', [
            'post_types' => $post_types
        ]);

        return $post_types;
    }

    /**
     * Enable Bricks template for content post type
     */
    public function enable_bricks_template() {
        $logger = Logger::get_instance();

        add_post_type_support(self::POST_TYPE, 'bricks');
        $logger->log('INFO', 'Bricks template enabled', [
            'post_type' => self::POST_TYPE
        ]);
    }
}
