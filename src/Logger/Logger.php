<?php
/**
 * Logger class for WP Custom Content
 *
 * @package WPCustomContent\Logger
 */

namespace WPCustomContent\Logger;

/**
 * Logger class for handling debug and error logging
 */
class Logger {
    private const LOG_TABLE = 'wpcc_logs';
    private const LOG_LEVELS = ['ERROR', 'WARNING', 'INFO', 'DEBUG', 'CRITICAL'];
    private static $instance = null;

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
     * Initialize the logger
     */
    private function __construct() {
        add_action('admin_init', [$this, 'maybe_create_log_table']);
    }

    /**
     * Create log table if it doesn't exist
     */
    public function maybe_create_log_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . self::LOG_TABLE;
        $charset_collate = $wpdb->get_charset_collate();

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $sql = "CREATE TABLE $table_name (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                timestamp datetime DEFAULT CURRENT_TIMESTAMP,
                level varchar(10) NOT NULL,
                message text NOT NULL,
                context text,
                user_id bigint(20),
                PRIMARY KEY  (id),
                KEY level (level),
                KEY timestamp (timestamp)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    /**
     * Log a message
     *
     * @param string $level   Log level (ERROR, WARNING, INFO, DEBUG)
     * @param string $message Log message
     * @param array  $context Additional context data
     */
    public function log($level, $message, $context = []) {
        if (!in_array(strtoupper($level), self::LOG_LEVELS)) {
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . self::LOG_TABLE;

        $data = [
            'level' => strtoupper($level),
            'message' => $message,
            'context' => json_encode($context),
            'user_id' => get_current_user_id(),
        ];

        $wpdb->insert($table_name, $data);

        // Send email notification for critical errors
        if (in_array(strtoupper($level), ['ERROR', 'CRITICAL'])) {
            $notifier = \WPCustomContent\Notifications\EmailNotifier::get_instance();
            $notifier->send_notification($level, $message, $context);
        }
    }

    /**
     * Get logs with filtering
     *
     * @param array $args Query arguments
     * @return array Logs
     */
    public function get_logs($args = []) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::LOG_TABLE;

        $defaults = [
            'level' => '',
            'date_from' => '',
            'date_to' => '',
            'limit' => 100,
            'offset' => 0,
            'orderby' => 'timestamp',
            'order' => 'DESC',
        ];

        $args = wp_parse_args($args, $defaults);
        $where = [];
        $values = [];

        if ($args['level']) {
            $where[] = 'level = %s';
            $values[] = $args['level'];
        }

        if ($args['date_from']) {
            $where[] = 'timestamp >= %s';
            $values[] = $args['date_from'];
        }

        if ($args['date_to']) {
            $where[] = 'timestamp <= %s';
            $values[] = $args['date_to'];
        }

        $where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $query = $wpdb->prepare(
            "SELECT * FROM $table_name 
            $where_clause
            ORDER BY {$args['orderby']} {$args['order']}
            LIMIT %d OFFSET %d",
            array_merge($values, [$args['limit'], $args['offset']])
        );

        return $wpdb->get_results($query);
    }

    /**
     * Clear old logs
     *
     * @param int $days Number of days to keep logs
     */
    public function clear_old_logs($days = 30) {
        global $wpdb;
        $table_name = $wpdb->prefix . self::LOG_TABLE;
        
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $table_name WHERE timestamp < DATE_SUB(NOW(), INTERVAL %d DAY)",
                $days
            )
        );
    }
}
