<?php
/**
 * Version Manager class
 *
 * @package WPCustomContent
 */

namespace WPCustomContent;

/**
 * Handles version management and auto-incrementing for testing
 */
class VersionManager {
    private static $instance = null;
    private $version_file;
    private $version_data;

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
     * Initialize version manager
     */
    private function __construct() {
        $this->version_file = WP_CUSTOM_CONTENT_PLUGIN_DIR . '/version.json';
        $this->load_version_data();
    }

    /**
     * Load version data from file
     */
    private function load_version_data() {
        if (file_exists($this->version_file)) {
            $json_data = file_get_contents($this->version_file);
            $this->version_data = json_decode($json_data, true);
        } else {
            $this->version_data = [
                'version' => WP_CUSTOM_CONTENT_VERSION,
                'test_version' => WP_CUSTOM_CONTENT_VERSION . '-test.1',
                'last_test' => time(),
                'test_count' => 0
            ];
            $this->save_version_data();
        }
    }

    /**
     * Save version data to file
     */
    private function save_version_data() {
        file_put_contents($this->version_file, json_encode($this->version_data, JSON_PRETTY_PRINT));
    }

    /**
     * Get current test version
     * 
     * @return string Current test version
     */
    public function get_test_version() {
        return $this->version_data['test_version'];
    }

    /**
     * Increment test version
     * 
     * @return string New test version
     */
    public function increment_test_version() {
        $base_version = WP_CUSTOM_CONTENT_VERSION;
        $this->version_data['test_count']++;
        $this->version_data['test_version'] = $base_version . '-test.' . $this->version_data['test_count'];
        $this->version_data['last_test'] = time();
        $this->save_version_data();
        
        return $this->version_data['test_version'];
    }

    /**
     * Get test count
     * 
     * @return int Number of tests run
     */
    public function get_test_count() {
        return $this->version_data['test_count'];
    }

    /**
     * Get last test timestamp
     * 
     * @return int Timestamp of last test
     */
    public function get_last_test_time() {
        return $this->version_data['last_test'];
    }
}
