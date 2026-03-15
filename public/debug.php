<?php
/**
 * Debug script untuk check Laravel setup
 * Access: https://helpdesk.atline.com.my/debug.php
 * DELETE selepas guna!
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Laravel Debug Check</h2>";
echo "<pre>";

// 1. Check PHP version
echo "1. PHP Version: " . phpversion() . "\n";
echo "   Required: 8.2+\n\n";

// 2. Check current directory
echo "2. Current Dir: " . __DIR__ . "\n";
echo "   Parent Dir: " . dirname(__DIR__) . "\n\n";

// 3. Check if key files exist
$laravelRoot = dirname(__DIR__);
$files = [
    'vendor/autoload.php',
    'bootstrap/app.php',
    '.env',
    'artisan',
];

echo "3. File Check (from $laravelRoot):\n";
foreach ($files as $file) {
    $path = $laravelRoot . '/' . $file;
    $exists = file_exists($path) ? '✅ EXISTS' : '❌ MISSING';
    echo "   $file: $exists\n";
}
echo "\n";

// 4. Check folder permissions
$folders = ['storage', 'storage/logs', 'storage/framework', 'bootstrap/cache'];
echo "4. Folder Permissions:\n";
foreach ($folders as $folder) {
    $path = $laravelRoot . '/' . $folder;
    if (file_exists($path)) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        $writable = is_writable($path) ? '✅ Writable' : '❌ Not Writable';
        echo "   $folder: $perms - $writable\n";
    } else {
        echo "   $folder: ❌ MISSING\n";
    }
}
echo "\n";

// 5. Try to load autoloader
echo "5. Autoloader Test:\n";
$autoloadPath = $laravelRoot . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    try {
        require $autoloadPath;
        echo "   ✅ Autoloader loaded successfully\n";
    } catch (Exception $e) {
        echo "   ❌ Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ❌ vendor/autoload.php not found - run 'composer install'\n";
}
echo "\n";

// 6. Check .env content
echo "6. ENV Check:\n";
$envPath = $laravelRoot . '/.env';
if (file_exists($envPath)) {
    $env = file_get_contents($envPath);
    if (preg_match('/APP_KEY=(.+)/', $env, $matches)) {
        $key = trim($matches[1]);
        echo "   APP_KEY: " . (strlen($key) > 10 ? '✅ Set (' . strlen($key) . ' chars)' : '❌ Empty or too short') . "\n";
    }
    if (preg_match('/APP_ENV=(.+)/', $env, $matches)) {
        echo "   APP_ENV: " . trim($matches[1]) . "\n";
    }
    if (preg_match('/APP_DEBUG=(.+)/', $env, $matches)) {
        echo "   APP_DEBUG: " . trim($matches[1]) . "\n";
    }
} else {
    echo "   ❌ .env file not found\n";
}

echo "</pre>";
echo "<p><strong>⚠️ DELETE this file after debugging!</strong></p>";
