<?php
/**
 * Plugin Installer
 *
 * @package WPCustomContent
 */

namespace WPCustomContent;

/**
 * Handles plugin installation and updates
 */
class Installer {
    /**
     * Run installer
     */
    public function install() {
        $this->create_tables();
        $this->create_analysis_table();
        $this->set_version();
    }

    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wpcc_gpt_analysis (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            post_id bigint(20) unsigned NOT NULL,
            analysis_type varchar(50) NOT NULL,
            analysis_data longtext NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY post_id (post_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * Create analysis table
     */
    private function create_analysis_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wpcc_gpt_analysis';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            analysis_type varchar(50) NOT NULL,
            analysis_data longtext NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY post_id (post_id),
            KEY analysis_type (analysis_type),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Set plugin version
     */
    private function set_version() {
        update_option('wpcc_version', WPCC_VERSION);
    }
}
