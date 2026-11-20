<?php

/**
 * Laravel Smart Log Viewer Helper for cPanel
 * Place this file in your public/ folder, e.g. public/view-logs.php
 * Access it via: yourdomain.com/view-logs.php
 */

header('Content-Type: text/plain; charset=utf-8');

$logFile = __DIR__ . '/../storage/logs/laravel.log';

echo "==================================================\n";
echo "           LARAVEL LOG VIEWER HELPER               \n";
echo "==================================================\n\n";

if (!file_exists($logFile)) {
    echo "Log file not found at: $logFile\n";
    exit;
}

$content = file_get_contents($logFile);
$lines = explode("\n", $content);

// Find the last error occurrence
$errorIndices = [];
foreach ($lines as $index => $line) {
    if (str_contains($line, '.ERROR:') || str_contains($line, 'local.WARNING:')) {
        $errorIndices[] = $index;
    }
}

if (empty($errorIndices)) {
    echo "No errors found in laravel.log!\n";
    exit;
}

// Show the last 2 errors with their messages and 15 lines of stack trace
$lastErrorsToShow = array_slice($errorIndices, -3);

foreach ($lastErrorsToShow as $i => $errIndex) {
    echo "=== ERROR #" . ($i + 1) . " (Line $errIndex in log) ===\n";
    
    // Print the error line and the next 20 lines
    for ($j = 0; $j < 25; $j++) {
        if (isset($lines[$errIndex + $j])) {
            echo $lines[$errIndex + $j] . "\n";
        }
    }
    echo "--------------------------------------------------\n\n";
}
