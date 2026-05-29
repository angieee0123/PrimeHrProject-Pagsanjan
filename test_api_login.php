<?php
/**
 * Test API Login Endpoint
 * 
 * This script tests the mobile API login endpoint to ensure it works correctly.
 * Run with: php test_api_login.php
 */

// Configuration
$apiUrl = 'http://localhost:8000/api/auth/login';
$testEmail = 'permanent@gmail.com'; // Change to your test email
$testPassword = 'password'; // Change to your test password

echo "=== Testing API Login ===\n\n";
echo "API URL: $apiUrl\n";
echo "Test Email: $testEmail\n\n";

// Prepare request data
$data = [
    'email' => $testEmail,
    'password' => $testPassword,
];

// Initialize cURL
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
]);

// Execute request
echo "Sending login request...\n\n";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Check for cURL errors
if ($error) {
    echo "❌ cURL Error: $error\n";
    echo "\nMake sure Laravel server is running: php artisan serve\n";
    exit(1);
}

// Parse response
$responseData = json_decode($response, true);

// Display results
echo "HTTP Status Code: $httpCode\n";
echo "Response:\n";
echo json_encode($responseData, JSON_PRETTY_PRINT) . "\n\n";

// Validate response
if ($httpCode === 200) {
    echo "✅ Login Successful!\n\n";
    
    if (isset($responseData['data']['token'])) {
        $token = $responseData['data']['token'];
        echo "Token: " . substr($token, 0, 20) . "...\n";
    }
    
    if (isset($responseData['data']['user'])) {
        $user = $responseData['data']['user'];
        echo "User ID: {$user['id']}\n";
        echo "Name: {$user['name']}\n";
        echo "Email: {$user['email']}\n";
        echo "Role: {$user['role']}\n";
        echo "Status: {$user['status']}\n";
    }
    
    if (isset($responseData['data']['employee'])) {
        $employee = $responseData['data']['employee'];
        echo "\nEmployee Info:\n";
        echo "Full Name: {$employee['full_name']}\n";
        echo "Employment Status: {$employee['employment_status']}\n";
        echo "Department: {$employee['department']}\n";
        echo "Designation: {$employee['designation']}\n";
    }
    
    echo "\n✅ All checks passed! API is working correctly.\n";
    
} elseif ($httpCode === 422) {
    echo "❌ Validation Error\n\n";
    
    if (isset($responseData['errors'])) {
        echo "Errors:\n";
        foreach ($responseData['errors'] as $field => $messages) {
            foreach ($messages as $message) {
                echo "  - $field: $message\n";
            }
        }
    }
    
    echo "\nPossible issues:\n";
    echo "1. Wrong email or password\n";
    echo "2. User account is not Active (check 'status' field in users table)\n";
    echo "3. User doesn't exist in database\n";
    
} else {
    echo "❌ Unexpected Error\n\n";
    echo "Possible issues:\n";
    echo "1. Laravel server not running (run: php artisan serve)\n";
    echo "2. Route not defined (check routes/api.php)\n";
    echo "3. Controller error (check Laravel logs)\n";
}

echo "\n=== Test Complete ===\n";
