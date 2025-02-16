<?php
/**
 * Local Development Deployment Script
 * 
 * This script deploys the plugin to the local WordPress installation
 * for testing purposes.
 */

// Configuration
$plugin_slug = 'wp-custom-content';
$local_wp_path = 'C:\Users\trevo\Local Sites\fedxio';
$plugin_dir = dirname(__DIR__);
$target_dir = "{$local_wp_path}\app\public\wp-content\plugins\{$plugin_slug}";

// Ensure we start fresh
if (file_exists($target_dir)) {
    echo "Removing existing plugin installation...\n";
    remove_directory($target_dir);
}

// Create target directory
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0755, true);
}

// Files and directories to exclude from development deployment
$excludes = [
    '.git',
    '.github',
    'build',
    'node_modules',
    '.DS_Store',
    'Thumbs.db',
    '*.log',
    '*.zip',
    '*.tar.gz',
    'tests',
    'scripts',
];

/**
 * Remove directory and its contents
 */
function remove_directory($dir) {
    if (!file_exists($dir)) {
        return;
    }
    
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    
    foreach ($files as $file) {
        if ($file->isDir()) {
            rmdir($file->getRealPath());
        } else {
            unlink($file->getRealPath());
        }
    }
    rmdir($dir);
}

/**
 * Copy files recursively
 */
function copy_directory($source, $dest, $excludes) {
    if (!file_exists($dest)) {
        mkdir($dest, 0755, true);
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $item) {
        // Skip excluded patterns
        foreach ($excludes as $exclude) {
            if (strpos($item->getPathname(), $exclude) !== false) {
                continue 2;
            }
        }
        
        if ($item->isDir()) {
            $dir = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
        } else {
            $target = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            copy($item->getPathname(), $target);
            echo "Copied: " . $iterator->getSubPathName() . "\n";
        }
    }
}

echo "Deploying to LocalWP...\n";

// Copy plugin files
echo "Copying plugin files...\n";
copy_directory($plugin_dir, $target_dir, $excludes);

// Copy required vendor files
$vendor_files = [
    'composer/installers/src/Composer/Installers/WordPressInstaller.php',
    'composer/installers/src/Composer/Installers/BaseInstaller.php',
    'composer/installers/src/Composer/Installers/Installer.php',
];

echo "\nCopying vendor files...\n";
foreach ($vendor_files as $file) {
    $source = $plugin_dir . '/vendor/' . $file;
    $dest = $target_dir . '/vendor/' . $file;
    
    if (file_exists($source)) {
        $dest_dir = dirname($dest);
        if (!file_exists($dest_dir)) {
            mkdir($dest_dir, 0755, true);
        }
        copy($source, $dest);
        echo "Copied vendor file: $file\n";
    }
}

echo "\nDeployment complete!\n";
echo "Plugin deployed to: {$target_dir}\n";
echo "WordPress URL: http://fedxio.local\n";
echo "WordPress admin: http://fedxio.local/wp-admin\n";
