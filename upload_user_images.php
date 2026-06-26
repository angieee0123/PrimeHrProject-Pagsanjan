<?php

/**
 * Upload user_images/ photos to employees records.
 * - Copies each image to public/storage/employees/photos/
 * - Assigns them round-robin to employees that have no photo
 * - Also re-assigns ALL employees for a fresh consistent set
 *
 * Run from project root:
 *   php upload_user_images.php
 */

$host     = '127.0.0.1';
$port     = '3306';
$db       = 'primehrismagdalena';
$user     = 'root';
$password = 'admin';

$sourceDir = __DIR__ . '/user_images';
$destDir   = __DIR__ . '/primeHrMagdalenaLaravel/public/storage/employees/photos';

// Collect source images sorted numerically
$images = [];
foreach (glob($sourceDir . '/*.png') as $file) {
    $images[basename($file)] = $file;
}
uksort($images, function($a, $b) {
    return intval($a) - intval($b);
});
$images = array_values($images);

if (empty($images)) {
    die("No images found in user_images/\n");
}

echo "Found " . count($images) . " images in user_images/\n";

// Connect DB
try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage() . "\n");
}

// Get all employees ordered by id
$employees = $pdo->query("SELECT id FROM employees ORDER BY id ASC")->fetchAll(PDO::FETCH_COLUMN);
echo "Found " . count($employees) . " employees\n\n";

$updated = 0;
$imgCount = count($images);

foreach ($employees as $index => $empId) {
    // Pick image round-robin
    $srcFile  = $images[$index % $imgCount];
    $origName = basename($srcFile);
    $timestamp = time() + $index; // unique per employee
    $destName = $timestamp . '_' . $origName;
    $destPath = $destDir . '/' . $destName;
    $dbPath   = '/storage/employees/photos/' . $destName;

    // Copy file
    if (!copy($srcFile, $destPath)) {
        echo "  FAILED to copy: $origName\n";
        continue;
    }

    // Update DB
    $stmt = $pdo->prepare("UPDATE employees SET photo = ? WHERE id = ?");
    $stmt->execute([$dbPath, $empId]);

    echo "  Employee ID $empId => $origName ($dbPath)\n";
    $updated++;
}

echo "\nDone! Updated $updated employees with photos.\n";
