<?php
// Simple PHP Upload Test - No Laravel
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create uploads directory if not exists
$uploadDir = __DIR__ . '/test-uploads';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Upload Test Results</h2>";
    
    // Display PHP configuration
    echo "<h3>PHP Configuration:</h3>";
    echo "<pre>";
    echo "upload_tmp_dir: " . ini_get('upload_tmp_dir') . "\n";
    echo "sys_temp_dir: " . sys_get_temp_dir() . "\n";
    echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
    echo "post_max_size: " . ini_get('post_max_size') . "\n";
    echo "file_uploads: " . (ini_get('file_uploads') ? 'Enabled' : 'Disabled') . "\n";
    echo "</pre>";
    
    // Display $_FILES
    echo "<h3>\$_FILES Content:</h3>";
    echo "<pre>";
    print_r($_FILES);
    echo "</pre>";
    
    // Check if file was uploaded
    if (isset($_FILES['testfile']) && $_FILES['testfile']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['testfile']['tmp_name'];
        $originalName = $_FILES['testfile']['name'];
        $fileSize = $_FILES['testfile']['size'];
        
        // Generate unique filename
        $newFilename = date('Y-m-d_His') . '_' . $originalName;
        $destination = $uploadDir . '/' . $newFilename;
        
        // Move uploaded file
        if (move_uploaded_file($tmpName, $destination)) {
            echo "<div style='padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; color: #155724; border-radius: 5px; margin: 20px 0;'>";
            echo "<h3 style='margin: 0 0 10px 0;'>✅ SUCCESS!</h3>";
            echo "<p><strong>File uploaded successfully!</strong></p>";
            echo "<p>Original name: {$originalName}</p>";
            echo "<p>File size: " . number_format($fileSize) . " bytes</p>";
            echo "<p>Saved as: {$newFilename}</p>";
            echo "<p>Location: {$destination}</p>";
            echo "</div>";
        } else {
            echo "<div style='padding: 15px; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 5px; margin: 20px 0;'>";
            echo "<h3 style='margin: 0 0 10px 0;'>❌ FAILED</h3>";
            echo "<p>Failed to move uploaded file to: {$destination}</p>";
            echo "<p>Check directory permissions: " . $uploadDir . "</p>";
            echo "</div>";
        }
    } else {
        $errorCode = $_FILES['testfile']['error'] ?? 'No file uploaded';
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder (upload_tmp_dir not configured)',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension',
        ];
        
        $errorMsg = $errorMessages[$errorCode] ?? "Unknown error: {$errorCode}";
        
        echo "<div style='padding: 15px; background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3 style='margin: 0 0 10px 0;'>❌ UPLOAD FAILED</h3>";
        echo "<p><strong>Error:</strong> {$errorMsg}</p>";
        echo "<p><strong>Error Code:</strong> {$errorCode}</p>";
        echo "</div>";
    }
    
    echo "<p><a href='test-upload.php' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Try Again</a></p>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Upload Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-top: 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 2px dashed #ddd;
            border-radius: 5px;
            cursor: pointer;
        }
        button {
            background: #28a745;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background: #218838;
        }
        .info {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 PHP Upload Test</h1>
        
        <div class="info">
            <strong>Purpose:</strong> Test if PHP can receive file uploads without Laravel.
            <br><strong>Note:</strong> This will help diagnose if the issue is with PHP configuration or Laravel code.
        </div>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="testfile">Select a file to upload:</label>
                <input type="file" name="testfile" id="testfile" required>
            </div>
            
            <button type="submit">Upload Test File</button>
        </form>
        
        <div style="margin-top: 30px; padding: 15px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px;">
            <strong>⚠️ Important:</strong> After testing, delete this file for security:
            <br><code>rm public/test-upload.php</code>
            <br><code>rm -rf public/test-uploads/</code>
        </div>
    </div>
</body>
</html>
