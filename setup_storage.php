<?php
/**
 * Storage Setup Script for cPanel
 * Run this file via browser: http://yourdomain.com/setup_storage.php
 * After running, delete this file for security
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Security check - only allow if accessed via web
if (php_sapi_name() === 'cli') {
    die("This script must be run via browser, not CLI.");
}

echo "<h2>Storage Setup Script</h2>";
echo "<p>Current directory: " . __DIR__ . "</p>";

// Define paths
$sourcePath = __DIR__ . '/storage/app/public';
$targetPath = __DIR__ . '/public/storage';

echo "<p>Source path: $sourcePath</p>";
echo "<p>Target path: $targetPath</p>";
echo "<p>Copying files from storage/app/public to public/storage...</p>";

// Check if source exists
if (!is_dir($sourcePath)) {
    die("<p style='color: red;'>Error: Source directory not found: $sourcePath</p><p>Please check if the file is in the correct location (root project folder).</p>");
}

// Create target directory if it doesn't exist
if (!is_dir($targetPath)) {
    mkdir($targetPath, 0755, true);
    echo "<p>Created target directory: $targetPath</p>";
}

// Function to copy directory recursively
function copyDirectory($source, $destination) {
    $totalFiles = 0;
    $totalDirs = 0;
    
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $item) {
        $destPath = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
        
        if ($item->isDir()) {
            if (!is_dir($destPath)) {
                mkdir($destPath, 0755, true);
                $totalDirs++;
            }
        } else {
            copy($item, $destPath);
            $totalFiles++;
            echo "<p>Copied: " . $iterator->getSubPathName() . "</p>";
        }
    }
    
    return ['files' => $totalFiles, 'dirs' => $totalDirs];
}

// Copy files
$result = copyDirectory($sourcePath, $targetPath);

echo "<h3 style='color: green;'>Setup Complete!</h3>";
echo "<p>Total directories created: " . $result['dirs'] . "</p>";
echo "<p>Total files copied: " . $result['files'] . "</p>";
echo "<p><strong>IMPORTANT:</strong> Delete this file (setup_storage.php) from your server for security.</p>";
echo "<p><a href='/'>Return to homepage</a></p>";
