<?php
/**
 * Email Notifier class
 *
 * @package WPCustomContent\Notifications
 */

namespace WPCustomContent\Notifications;

use WPCustomContent\Admin\Settings;

/**
 * Email Notifier class for sending error notifications
 */
class EmailNotifier {
    private static $instance = null;
    private $settings;

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
     * Initialize the notifier
     */
    private function __construct() {
        $this->settings = Settings::get_instance();
        add_filter('wp_mail_content_type', [$this, 'set_html_content_type']);
    }

    /**
     * Set email content type to HTML
     */
    public function set_html_content_type() {
        return 'text/html';
    }

    /**
     * Send error notification
     *
     * @param string $level   Error level
     * @param string $message Error message
     * @param array  $context Error context
     */
    public function send_notification($level, $message, $context = []) {
        $options = $this->settings->get_options();
        
        // Check if notifications are enabled and we should notify for this level
        if (empty($options['enable_error_notifications']) || 
            empty($options['notification_email']) || 
            !in_array($level, ['ERROR', 'CRITICAL'])) {
            return;
        }

        $site_name = get_bloginfo('name');
        $subject = sprintf('[%s] %s Alert: %s', $site_name, $level, substr($message, 0, 50));

        $body = $this->get_email_template($level, $message, $context);
        
        wp_mail(
            $options['notification_email'],
            $subject,
            $body
        );
    }

    /**
     * Get email template
     *
     * @param string $level   Error level
     * @param string $message Error message
     * @param array  $context Error context
     */
    private function get_email_template($level, $message, $context) {
        $site_name = get_bloginfo('name');
        $site_url = get_site_url();
        $admin_url = admin_url('admin.php?page=wpcc-logs');
        $timestamp = current_time('mysql');

        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background: #f1f1f1; padding: 20px; margin-bottom: 20px; }
                .content { padding: 20px; }
                .error-message { background: #fff3f3; border-left: 4px solid #dc3545; padding: 15px; margin: 10px 0; }
                .context { background: #f8f9fa; padding: 15px; margin: 10px 0; }
                .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h2><?php echo esc_html($site_name); ?> - <?php echo esc_html($level); ?> Alert</h2>
            </div>
            
            <div class="content">
                <p>An error has occurred on your website:</p>
                
                <div class="error-message">
                    <?php echo esc_html($message); ?>
                </div>
                
                <?php if (!empty($context)) : ?>
                    <h3>Additional Information:</h3>
                    <div class="context">
                        <pre><?php echo esc_html(json_encode($context, JSON_PRETTY_PRINT)); ?></pre>
                    </div>
                <?php endif; ?>
                
                <p>
                    <strong>Timestamp:</strong> <?php echo esc_html($timestamp); ?><br>
                    <strong>View Logs:</strong> <a href="<?php echo esc_url($admin_url); ?>"><?php echo esc_html($admin_url); ?></a>
                </p>
            </div>
            
            <div class="footer">
                This is an automated message from <?php echo esc_html($site_name); ?> (<?php echo esc_url($site_url); ?>).<br>
                To stop receiving these notifications, update your settings in the WordPress admin panel.
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}
