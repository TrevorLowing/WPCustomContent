<?php
/**
 * Plugin Packaging Script
 * 
 * This script creates a clean distribution package of the plugin
 * with only the essential files needed for production.
 */

// Configuration
$plugin_slug = 'wp-custom-content';
$version = trim(file_get_contents(__DIR__ . '/../VERSION'));
$output_dir = __DIR__ . '/../build';
$package_name = "{$plugin_slug}-{$version}";
$package_dir = "{$output_dir}/{$package_name}";

// Essential files and directories to include
$includes = [
    'src/',
    'assets/',
    'languages/',
    'vendor/',  // Will be populated with production dependencies only
    'wp-custom-content.php',
    'uninstall.php',
    'README.md',
    'LICENSE',
];

// Files and directories to exclude
$excludes = [
    '.git',
    '.github',
    'tests',
    'scripts',
    'phpunit.xml',
    'phpcs.xml',
    '.gitignore',
    '.editorconfig',
    'composer.json',
    'composer.lock',
    'package.json',
    'package-lock.json',
    'node_modules',
    '.DS_Store',
    'Thumbs.db',
    '*.log',
    '*.sql',
    '*.zip',
    '*.tar.gz',
    'docs/',
];

// Create build directory if it doesn't exist
if (!file_exists($output_dir)) {
    mkdir($output_dir, 0755, true);
}

// Clean up existing package directory
if (file_exists($package_dir)) {
    system('rm -rf ' . escapeshellarg($package_dir));
}
mkdir($package_dir, 0755, true);

// Function to copy files recursively
function copy_directory($source, $dest) {
    global $excludes;
    
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
            copy($item->getPathname(), $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
        }
    }
}

// Install production dependencies
echo "Installing production dependencies...\n";
system('composer install --no-dev --optimize-autoloader --working-dir=' . escapeshellarg(__DIR__ . '/../'));

// Copy files
echo "Copying files...\n";
foreach ($includes as $item) {
    $source = __DIR__ . '/../' . $item;
    $destination = $package_dir . '/' . $item;
    
    if (is_file($source)) {
        copy($source, $destination);
    } elseif (is_dir($source)) {
        copy_directory($source, $destination);
    }
}

// Create zip archive
echo "Creating zip archive...\n";
$zip_file = "{$output_dir}/{$package_name}.zip";
if (file_exists($zip_file)) {
    unlink($zip_file);
}

$zip = new ZipArchive();
if ($zip->open($zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($package_dir),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    
    foreach ($files as $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($package_dir) + 1);
            $zip->addFile($filePath, $relativePath);
        }
    }
    $zip->close();
    
    echo "Package created successfully: {$zip_file}\n";
} else {
    echo "Failed to create zip archive\n";
    exit(1);
}

// Clean up
system('rm -rf ' . escapeshellarg($package_dir));
system('composer install --working-dir=' . escapeshellarg(__DIR__ . '/../')); // Restore dev dependencies

echo "Done!\n";
