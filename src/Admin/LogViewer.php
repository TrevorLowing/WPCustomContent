<?php
/**
 * Log Viewer admin page
 *
 * @package WPCustomContent\Admin
 */

namespace WPCustomContent\Admin;

use WPCustomContent\Logger\Logger;

/**
 * Log Viewer class for displaying and managing logs
 */
class LogViewer {
    private static $instance = null;
    private $per_page = 20;

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
     * Initialize the log viewer
     */
    private function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts($hook) {
        if ('wp-custom-content_page_wpcc-logs' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'wpcc-admin-style',
            plugins_url('assets/css/admin.css', WP_CUSTOM_CONTENT_PLUGIN_FILE),
            [],
            WP_CUSTOM_CONTENT_VERSION
        );
    }

    /**
     * Render the log viewer page
     */
    public function render_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $logger = Logger::get_instance();
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $level_filter = isset($_GET['level']) ? sanitize_text_field($_GET['level']) : '';
        $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
        $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';

        $args = [
            'level' => $level_filter,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'limit' => $this->per_page,
            'offset' => ($current_page - 1) * $this->per_page,
        ];

        $logs = $logger->get_logs($args);
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('System Logs', 'wp-custom-content'); ?></h1>

            <div class="tablenav top">
                <form method="get">
                    <input type="hidden" name="page" value="wpcc-logs">
                    
                    <select name="level">
                        <option value=""><?php echo esc_html__('All Levels', 'wp-custom-content'); ?></option>
                        <?php foreach (['ERROR', 'WARNING', 'INFO', 'DEBUG'] as $level) : ?>
                            <option value="<?php echo esc_attr($level); ?>" <?php selected($level_filter, $level); ?>>
                                <?php echo esc_html($level); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <input type="date" name="date_from" value="<?php echo esc_attr($date_from); ?>" placeholder="<?php echo esc_attr__('From Date', 'wp-custom-content'); ?>">
                    <input type="date" name="date_to" value="<?php echo esc_attr($date_to); ?>" placeholder="<?php echo esc_attr__('To Date', 'wp-custom-content'); ?>">

                    <input type="submit" class="button" value="<?php echo esc_attr__('Filter', 'wp-custom-content'); ?>">
                </form>
            </div>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php echo esc_html__('Time', 'wp-custom-content'); ?></th>
                        <th><?php echo esc_html__('Level', 'wp-custom-content'); ?></th>
                        <th><?php echo esc_html__('Message', 'wp-custom-content'); ?></th>
                        <th><?php echo esc_html__('Context', 'wp-custom-content'); ?></th>
                        <th><?php echo esc_html__('User', 'wp-custom-content'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log) : ?>
                        <tr>
                            <td><?php echo esc_html($log->timestamp); ?></td>
                            <td>
                                <span class="log-level log-level-<?php echo esc_attr(strtolower($log->level)); ?>">
                                    <?php echo esc_html($log->level); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html($log->message); ?></td>
                            <td>
                                <?php
                                $context = json_decode($log->context, true);
                                if ($context) {
                                    echo '<pre>' . esc_html(json_encode($context, JSON_PRETTY_PRINT)) . '</pre>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                $user = get_userdata($log->user_id);
                                echo $user ? esc_html($user->display_name) : '—';
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php
            $total_logs = $logger->get_logs(['count' => true]);
            $total_pages = ceil($total_logs / $this->per_page);

            if ($total_pages > 1) {
                echo '<div class="tablenav bottom">';
                echo paginate_links([
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => __('&laquo;'),
                    'next_text' => __('&raquo;'),
                    'total' => $total_pages,
                    'current' => $current_page,
                ]);
                echo '</div>';
            }
            ?>
        </div>
        <?php
    }
}
