<?php
/**
 * Help documentation for the admin interface
 *
 * @package WPCustomContent\Admin
 */

namespace WPCustomContent\Admin;

use WPCustomContent\PostTypes\ContentPostType;

/**
 * Manages help tabs and documentation in the admin interface
 */
class HelpTabs {
    /**
     * Initialize help tabs
     */
    public function __construct() {
        add_action('admin_head', [$this, 'add_help_tabs']);
    }

    /**
     * Add help tabs to admin screens
     */
    public function add_help_tabs() {
        $screen = get_current_screen();
        
        if (!$screen) {
            return;
        }

        // Add help tabs for the content post type
        if ($screen->post_type === ContentPostType::POST_TYPE) {
            $this->add_content_post_type_help($screen);
        }

        // Add help tabs for the settings page
        if ($screen->id === 'settings_page_wpcc-settings') {
            $this->add_settings_help($screen);
        }
    }

    /**
     * Add help tabs for content post type
     *
     * @param \WP_Screen $screen Current screen
     */
    private function add_content_post_type_help($screen) {
        // Overview tab
        $screen->add_help_tab([
            'id'       => 'wpcc_overview',
            'title'    => __('Overview', 'wp-custom-content'),
            'content'  => $this->get_overview_content(),
        ]);

        // Content Fields tab
        $screen->add_help_tab([
            'id'       => 'wpcc_fields',
            'title'    => __('Content Fields', 'wp-custom-content'),
            'content'  => $this->get_fields_content(),
        ]);

        // File Management tab
        $screen->add_help_tab([
            'id'       => 'wpcc_files',
            'title'    => __('File Management', 'wp-custom-content'),
            'content'  => $this->get_file_management_content(),
        ]);

        // Integrations tab
        $screen->add_help_tab([
            'id'       => 'wpcc_integrations',
            'title'    => __('Integrations', 'wp-custom-content'),
            'content'  => $this->get_integrations_content(),
        ]);

        // Set help sidebar
        $screen->set_help_sidebar($this->get_help_sidebar());
    }

    /**
     * Add help tabs for settings page
     *
     * @param \WP_Screen $screen Current screen
     */
    private function add_settings_help($screen) {
        // Settings Overview tab
        $screen->add_help_tab([
            'id'       => 'wpcc_settings_overview',
            'title'    => __('Settings Overview', 'wp-custom-content'),
            'content'  => $this->get_settings_overview_content(),
        ]);

        // Integration Settings tab
        $screen->add_help_tab([
            'id'       => 'wpcc_integration_settings',
            'title'    => __('Integration Settings', 'wp-custom-content'),
            'content'  => $this->get_integration_settings_content(),
        ]);
    }

    /**
     * Get overview help content
     *
     * @return string
     */
    private function get_overview_content() {
        ob_start();
        ?>
        <h2><?php _e('Content Library Overview', 'wp-custom-content'); ?></h2>
        <p><?php _e('The Content Library is designed to help you manage and organize various types of content in your WordPress site. Each content item can be a document, video, presentation, or other media type.', 'wp-custom-content'); ?></p>

        <h3><?php _e('Key Features', 'wp-custom-content'); ?></h3>
        <ul>
            <li><?php _e('Organize content with categories and tags', 'wp-custom-content'); ?></li>
            <li><?php _e('Track content versions and updates', 'wp-custom-content'); ?></li>
            <li><?php _e('Manage remote files and documents', 'wp-custom-content'); ?></li>
            <li><?php _e('Integrate with media embedding services', 'wp-custom-content'); ?></li>
        </ul>
        <?php
        return ob_get_clean();
    }

    /**
     * Get fields help content
     *
     * @return string
     */
    private function get_fields_content() {
        ob_start();
        ?>
        <h2><?php _e('Content Fields Reference', 'wp-custom-content'); ?></h2>
        
        <h3><?php _e('Standard Fields', 'wp-custom-content'); ?></h3>
        <ul>
            <li><strong><?php _e('Title', 'wp-custom-content'); ?></strong> - <?php _e('The name or title of your content item', 'wp-custom-content'); ?></li>
            <li><strong><?php _e('Content', 'wp-custom-content'); ?></strong> - <?php _e('A full description or the main content', 'wp-custom-content'); ?></li>
            <li><strong><?php _e('Excerpt', 'wp-custom-content'); ?></strong> - <?php _e('A brief summary of the content', 'wp-custom-content'); ?></li>
            <li><strong><?php _e('Featured Image', 'wp-custom-content'); ?></strong> - <?php _e('A cover image or thumbnail', 'wp-custom-content'); ?></li>
        </ul>

        <h3><?php _e('Custom Fields', 'wp-custom-content'); ?></h3>
        <ul>
            <li>
                <strong><?php _e('Content Type', 'wp-custom-content'); ?></strong>
                <p><?php _e('Categorizes your content item. Available types:', 'wp-custom-content'); ?></p>
                <ul>
                    <li><em><?php _e('Document', 'wp-custom-content'); ?></em> - <?php _e('PDF files, text documents, spreadsheets', 'wp-custom-content'); ?></li>
                    <li><em><?php _e('Video', 'wp-custom-content'); ?></em> - <?php _e('Video content from various platforms', 'wp-custom-content'); ?></li>
                    <li><em><?php _e('Presentation', 'wp-custom-content'); ?></em> - <?php _e('Slideshows and presentations', 'wp-custom-content'); ?></li>
                    <li><em><?php _e('Audio', 'wp-custom-content'); ?></em> - <?php _e('Audio files and podcasts', 'wp-custom-content'); ?></li>
                    <li><em><?php _e('Other', 'wp-custom-content'); ?></em> - <?php _e('Any other content type', 'wp-custom-content'); ?></li>
                </ul>
            </li>
            <li>
                <strong><?php _e('File URL', 'wp-custom-content'); ?></strong>
                <p><?php _e('The URL to the remote file. This could be a direct link to a PDF, a YouTube video URL, or any other content source.', 'wp-custom-content'); ?></p>
            </li>
            <li>
                <strong><?php _e('File Version', 'wp-custom-content'); ?></strong>
                <p><?php _e('Track different versions of your content. Use semantic versioning (e.g., 1.0.0) or any other version identifier.', 'wp-custom-content'); ?></p>
            </li>
            <li>
                <strong><?php _e('Last Updated', 'wp-custom-content'); ?></strong>
                <p><?php _e('The date when the content was last modified or reviewed.', 'wp-custom-content'); ?></p>
            </li>
        </ul>
        <?php
        return ob_get_clean();
    }

    /**
     * Get file management help content
     *
     * @return string
     */
    private function get_file_management_content() {
        ob_start();
        ?>
        <h2><?php _e('File Management', 'wp-custom-content'); ?></h2>
        <p><?php _e('The Content Library can handle both remote and local files:', 'wp-custom-content'); ?></p>

        <h3><?php _e('Remote Files', 'wp-custom-content'); ?></h3>
        <ul>
            <li><?php _e('Enter the URL in the File URL field', 'wp-custom-content'); ?></li>
            <li><?php _e('The plugin will automatically download and cache the file', 'wp-custom-content'); ?></li>
            <li><?php _e('Updates to the remote file will be tracked', 'wp-custom-content'); ?></li>
        </ul>

        <h3><?php _e('Local Files', 'wp-custom-content'); ?></h3>
        <ul>
            <li><?php _e('Upload files using the WordPress Media Library', 'wp-custom-content'); ?></li>
            <li><?php _e('Files are stored securely in your WordPress installation', 'wp-custom-content'); ?></li>
            <li><?php _e('Automatic backup with your regular WordPress backups', 'wp-custom-content'); ?></li>
        </ul>
        <?php
        return ob_get_clean();
    }

    /**
     * Get integrations help content
     *
     * @return string
     */
    private function get_integrations_content() {
        ob_start();
        ?>
        <h2><?php _e('Integration Features', 'wp-custom-content'); ?></h2>

        <h3><?php _e('EmbedPress Integration', 'wp-custom-content'); ?></h3>
        <p><?php _e('When EmbedPress is enabled, you can:', 'wp-custom-content'); ?></p>
        <ul>
            <li><?php _e('Embed content from 100+ providers', 'wp-custom-content'); ?></li>
            <li><?php _e('Customize embedding options', 'wp-custom-content'); ?></li>
            <li><?php _e('Use advanced display features', 'wp-custom-content'); ?></li>
        </ul>

        <h3><?php _e('PDF Embedder Integration', 'wp-custom-content'); ?></h3>
        <p><?php _e('With PDF Embedder enabled:', 'wp-custom-content'); ?></p>
        <ul>
            <li><?php _e('Display PDFs directly in your pages', 'wp-custom-content'); ?></li>
            <li><?php _e('Mobile-friendly document viewing', 'wp-custom-content'); ?></li>
            <li><?php _e('Secure document display options', 'wp-custom-content'); ?></li>
        </ul>
        <?php
        return ob_get_clean();
    }

    /**
     * Get settings overview content
     *
     * @return string
     */
    private function get_settings_overview_content() {
        ob_start();
        ?>
        <h2><?php _e('Settings Overview', 'wp-custom-content'); ?></h2>
        <p><?php _e('The Content Library settings allow you to customize how the plugin works and which features are enabled.', 'wp-custom-content'); ?></p>

        <h3><?php _e('General Settings', 'wp-custom-content'); ?></h3>
        <ul>
            <li><?php _e('Configure plugin behavior', 'wp-custom-content'); ?></li>
            <li><?php _e('Manage integration options', 'wp-custom-content'); ?></li>
            <li><?php _e('Set default preferences', 'wp-custom-content'); ?></li>
        </ul>
        <?php
        return ob_get_clean();
    }

    /**
     * Get integration settings help content
     *
     * @return string
     */
    private function get_integration_settings_content() {
        ob_start();
        ?>
        <h2><?php _e('Integration Settings', 'wp-custom-content'); ?></h2>
        <p><?php _e('Manage how the Content Library integrates with other plugins:', 'wp-custom-content'); ?></p>

        <h3><?php _e('Available Integrations', 'wp-custom-content'); ?></h3>
        <ul>
            <li>
                <strong><?php _e('EmbedPress', 'wp-custom-content'); ?></strong>
                <p><?php _e('Enable to use advanced embedding features from various content providers.', 'wp-custom-content'); ?></p>
            </li>
            <li>
                <strong><?php _e('PDF Embedder', 'wp-custom-content'); ?></strong>
                <p><?php _e('Enable for enhanced PDF document display capabilities.', 'wp-custom-content'); ?></p>
            </li>
        </ul>

        <h3><?php _e('Requirements', 'wp-custom-content'); ?></h3>
        <ul>
            <li><?php _e('Integrations only work when the required plugins are installed and activated', 'wp-custom-content'); ?></li>
            <li><?php _e('Each integration can be enabled/disabled independently', 'wp-custom-content'); ?></li>
            <li><?php _e('Settings are preserved even when integrations are temporarily disabled', 'wp-custom-content'); ?></li>
        </ul>
        <?php
        return ob_get_clean();
    }

    /**
     * Get help sidebar content
     *
     * @return string
     */
    private function get_help_sidebar() {
        $content = '<p><strong>' . __('For more information:', 'wp-custom-content') . '</strong></p>';
        $content .= '<p>' . sprintf(
            /* translators: %s: Link to documentation */
            __('Visit the <a href="%s">documentation</a> for detailed guides and tutorials.', 'wp-custom-content'),
            esc_url('https://docs.example.com/wp-custom-content')
        ) . '</p>';
        $content .= '<p>' . sprintf(
            /* translators: %s: Link to support */
            __('Get <a href="%s">support</a> if you have questions.', 'wp-custom-content'),
            esc_url('https://support.example.com/wp-custom-content')
        ) . '</p>';
        
        return $content;
    }
}
