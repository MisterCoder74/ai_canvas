<?php
/**
 * GET IMAGE
 * 
 * Serve user images with authorization check
 * 
 * Usage: get_image.php?path=data/user_XXX/images/img_123.png
 */

require_once 'google_config.php';

session_name(SESSION_NAME);
session_start();

// Check authentication
if (!isset($_SESSION['user']['logged_in']) || $_SESSION['user']['logged_in'] !== true) {
    header('HTTP/1.0 403 Forbidden');
    die('Access denied');
}

$googleId = $_SESSION['user']['google_id'];
$requestedPath = $_GET['path'] ?? '';

// Validate path
if (empty($requestedPath)) {
    header('HTTP/1.0 400 Bad Request');
    die('Missing path parameter');
}

// Security: ensure path is within user's folder
$allowedPrefix = 'data/user_' . $googleId . '/images/';
if (strpos($requestedPath, $allowedPrefix) !== 0) {
    header('HTTP/1.0 403 Forbidden');
    die('Access denied - not your image');
}

// Build full path
$fullPath = __DIR__ . '/' . $requestedPath;

// Check file exists
if (!file_exists($fullPath)) {
    header('HTTP/1.0 404 Not Found');
    die('Image not found');
}

// Detect mime type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $fullPath);
finfo_close($finfo);

// Serve image
header('Content-Type: ' . $mimeType);
header('Content-Length: ' . filesize($fullPath));
header('Cache-Control: public, max-age=31536000'); // Cache for 1 year
readfile($fullPath);
exit;
?>