<?php

/**
 * Laravel Cache Clear Script
 * Access via: https://helpdesk.atline.com.my/clear-cache.php?key=YOUR_SECRET_KEY
 * 
 * IMPORTANT: Delete this file after use for security!
 */

// Security key - tukar kepada sesuatu yang susah diteka
$secretKey = 'atline2026clearme';

// Check security key
if (!isset($_GET['key']) || $_GET['key'] !== $secretKey) {
    die('Unauthorized access. Please provide valid key.');
}

// Change to Laravel root directory
// Jika struktur: /helpdesk.atline.com.my/public/clear-cache.php
// Laravel root: /helpdesk.atline.com.my/
chdir(dirname(__DIR__));

// Define commands to run
$commands = [
    'config:clear' => 'php artisan config:clear',
    'cache:clear' => 'php artisan cache:clear',
    'route:clear' => 'php artisan route:clear',
    'view:clear' => 'php artisan view:clear',
];

// Optional commands (uncomment if needed)
// $commands['migrate'] = 'php artisan migrate --force';
// $commands['storage:link'] = 'php artisan storage:link';

echo "<html><head><title>Laravel Cache Clear</title>";
echo "<style>body{font-family:Arial,sans-serif;padding:20px;background:#1a1a2e;color:#eee;}";
echo ".success{color:#4ade80;}.error{color:#f87171;}.command{background:#16213e;padding:10px;margin:10px 0;border-radius:5px;}";
echo "h1{color:#818cf8;}</style></head><body>";
echo "<h1>🧹 Laravel Cache Clear</h1>";

foreach ($commands as $name => $command) {
    echo "<div class='command'>";
    echo "<strong>Running: {$name}</strong><br>";
    
    $output = [];
    $returnCode = 0;
    exec($command . ' 2>&1', $output, $returnCode);
    
    $outputText = implode("\n", $output);
    
    if ($returnCode === 0) {
        echo "<span class='success'>✅ Success</span><br>";
        echo "<pre>" . htmlspecialchars($outputText) . "</pre>";
    } else {
        echo "<span class='error'>❌ Error (code: {$returnCode})</span><br>";
        echo "<pre>" . htmlspecialchars($outputText) . "</pre>";
    }
    echo "</div>";
}

echo "<br><p><strong>⚠️ PENTING:</strong> Delete file ini selepas guna untuk keselamatan!</p>";
echo "<p>Path: " . __FILE__ . "</p>";
echo "</body></html>";
