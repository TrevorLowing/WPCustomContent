<?php
/**
 * Uninstall WP Custom Content
 *
 * @package WPCustomContent
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete custom post type posts
$posts = get_posts([
    'post_type' => 'content',
    'numberposts' => -1,
    'post_status' => 'any',
]);

foreach ($posts as $post) {
    wp_delete_post($post->ID, true);
}

// Delete plugin options
delete_option('wpcc_version');

// Delete custom upload directory
$upload_dir = wp_upload_dir();
$wpcc_dir = $upload_dir['basedir'] . '/wpcc-content';
if (file_exists($wpcc_dir)) {
    // Recursively delete the directory
    WP_Filesystem();
    global $wp_filesystem;
    $wp_filesystem->delete($wpcc_dir, true);
}

// Clear any cached data
wp_cache_flush();
