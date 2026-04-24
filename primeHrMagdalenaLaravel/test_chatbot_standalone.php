#!/usr/bin/env php
<?php

/**
 * PRIME HRIS Chatbot - Standalone Test Script
 * Run: php test_chatbot_standalone.php
 */

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "   PRIME HRIS CHATBOT - COMPREHENSIVE TESTING\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Configuration
$baseUrl = 'http://127.0.0.1:8000'; // Change if your Laravel runs on different port
$apiEndpoint = '/api/chatbot';

// Test Questions organized by category
$testQuestions = [
    // GREETINGS
    [
        'category' => '1. GREETINGS',
        'questions' => [
            'Hello',
            'Hi there',
            'Good morning',
            'Kumusta',
        ]
    ],
    
    // COUNTING QUERIES
    [
        'category' => '2. COUNTING - Total Employees',
        'questions' => [
            'How many employees do we have?',
            'How many people work here?',
            'Total number of staff',
            'Count all employees',
            'Ilang empleyado meron tayo?',
        ]
    ],
    
    [
        'category' => '3. COUNTING - Active Employees',
        'questions' => [
            'How many active employees?',
            'Count active staff',
            'Ilang aktibong empleyado?',
        ]
    ],
    
    [
        'category' => '4. COUNTING - Inactive Employees',
        'questions' => [
            'How many inactive employees?',
            'Count inactive staff',
        ]
    ],
    
    [
        'category' => '5. COUNTING - Permanent Employees',
        'questions' => [
            'How many permanent employees?',
            'Ilang permanenteng empleyado?',
        ]
    ],
    
    [
        'category' => '6. COUNTING - Job Order Employees',
        'questions' => [
            'How many job order employees?',
            'Count contractual staff',
        ]
    ],
    
    [
        'category' => '7. COUNTING - Departments',
        'questions' => [
            'How many departments do we have?',
            'Total number of offices',
        ]
    ],
    
    // LISTING QUERIES
    [
        'category' => '8. LISTING - All Employees',
        'questions' => [
            'Show all employees',
            'List all staff',
            'Ipakita ang lahat ng empleyado',
        ]
    ],
    
    [
        'category' => '9. LISTING - Active Employees',
        'questions' => [
            'Show active employees',
            'List active staff',
        ]
    ],
    
    [
        'category' => '10. LISTING - Departments',
        'questions' => [
            'List all departments',
            'Show all offices',
        ]
    ],
    
    // SEARCH QUERIES
    [
        'category' => '11. SEARCH - By Name',
        'questions' => [
            'Find System Admin',
            'Who is Administrator?',
            'Search for admin',
        ]
    ],
    
    [
        'category' => '12. SEARCH - By Position',
        'questions' => [
            'Find all administrators',
            'Search for system administrator',
        ]
    ],
    
    // DEPARTMENT QUERIES
    [
        'category' => '13. DEPARTMENT - Head',
        'questions' => [
            'Who is the head of Mayor office?',
            'Who heads the health office?',
        ]
    ],
    
    [
        'category' => '14. DEPARTMENT - Personnel',
        'questions' => [
            'Who works in Mayor office?',
            'Show employees in health office',
        ]
    ],
    
    // NATURAL LANGUAGE VARIATIONS
    [
        'category' => '15. NATURAL LANGUAGE',
        'questions' => [
            'How many workers do we have?',
            'Total people employed',
            'Show me all the people working here',
        ]
    ],
    
    // BILINGUAL
    [
        'category' => '16. BILINGUAL (Taglish)',
        'questions' => [
            'How many empleyado meron?',
            'Sino ang head ng Mayor office?',
        ]
    ],
];

// Statistics
$totalTests = 0;
$passedTests = 0;
$failedTests = 0;
$results = [];

// Function to make API call
function callChatbot($url, $message) {
    $ch = curl_init($url);
    
    $data = json_encode(['message' => $message]);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    return [
        'http_code' => $httpCode,
        'response' => $response,
        'error' => $error
    ];
}

// Run tests
foreach ($testQuestions as $category) {
    echo "\n";
    echo "───────────────────────────────────────────────────────────────\n";
    echo "  {$category['category']}\n";
    echo "───────────────────────────────────────────────────────────────\n";

    foreach ($category['questions'] as $question) {
        $totalTests++;
        
        echo "\n📝 Question: \"{$question}\"\n";
        
        $result = callChatbot($baseUrl . $apiEndpoint, $question);
        
        if ($result['error']) {
            echo "❌ Status: CONNECTION ERROR\n";
            echo "⚠️  Error: {$result['error']}\n";
            echo "💡 Tip: Make sure Laravel is running (php artisan serve)\n";
            $failedTests++;
            $results[] = [
                'question' => $question,
                'status' => 'error',
                'error' => $result['error']
            ];
            continue;
        }
        
        if ($result['http_code'] === 200) {
            $data = json_decode($result['response'], true);
            
            if ($data && isset($data['status']) && $data['status'] === 'success') {
                echo "✅ Status: SUCCESS\n";
                echo "🔍 Query Type: " . ($data['query_type'] ?? 'N/A') . "\n";
                
                $responseText = $data['response'] ?? 'No response';
                $shortResponse = strlen($responseText) > 100 
                    ? substr($responseText, 0, 100) . "..." 
                    : $responseText;
                echo "💬 Response: {$shortResponse}\n";
                
                if (isset($data['follow_up_questions']) && count($data['follow_up_questions']) > 0) {
                    $followUps = array_slice($data['follow_up_questions'], 0, 2);
                    echo "🔗 Follow-ups: " . implode(', ', $followUps) . "\n";
                }
                
                $passedTests++;
                $results[] = [
                    'question' => $question,
                    'status' => 'success',
                    'query_type' => $data['query_type'] ?? 'N/A',
                    'response' => $shortResponse
                ];
            } else {
                echo "❌ Status: INVALID RESPONSE\n";
                echo "⚠️  Response: " . substr($result['response'], 0, 200) . "\n";
                $failedTests++;
                $results[] = [
                    'question' => $question,
                    'status' => 'invalid',
                    'response' => $result['response']
                ];
            }
        } else {
            echo "❌ Status: HTTP ERROR {$result['http_code']}\n";
            echo "⚠️  Response: " . substr($result['response'], 0, 200) . "\n";
            $failedTests++;
            $results[] = [
                'question' => $question,
                'status' => 'http_error',
                'http_code' => $result['http_code']
            ];
        }
        
        // Small delay to avoid overwhelming the server
        usleep(100000); // 0.1 second
    }
}

// SUMMARY
echo "\n\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "   TEST SUMMARY\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "Total Tests:  {$totalTests}\n";
echo "✅ Passed:    {$passedTests} (" . round(($passedTests / $totalTests) * 100, 1) . "%)\n";
echo "❌ Failed:    {$failedTests} (" . round(($failedTests / $totalTests) * 100, 1) . "%)\n";
echo "═══════════════════════════════════════════════════════════════\n";

// Detailed failure report
if ($failedTests > 0) {
    echo "\n";
    echo "───────────────────────────────────────────────────────────────\n";
    echo "   FAILED TESTS DETAILS\n";
    echo "───────────────────────────────────────────────────────────────\n";
    
    $failureCount = 0;
    foreach ($results as $result) {
        if ($result['status'] !== 'success') {
            $failureCount++;
            echo "\n{$failureCount}. \"{$result['question']}\"\n";
            echo "   Status: {$result['status']}\n";
            if (isset($result['error'])) {
                echo "   Error: {$result['error']}\n";
            }
            if (isset($result['http_code'])) {
                echo "   HTTP Code: {$result['http_code']}\n";
            }
        }
    }
}

// Success message
echo "\n";
if ($passedTests === $totalTests) {
    echo "🎉 ALL TESTS PASSED! Your chatbot is working perfectly!\n";
} elseif ($passedTests > $totalTests * 0.8) {
    echo "✨ GREAT! Most tests passed. Check failed tests above.\n";
} elseif ($passedTests > 0) {
    echo "⚠️  PARTIAL SUCCESS. Some features need attention.\n";
} else {
    echo "❌ ALL TESTS FAILED. Check if Laravel server is running.\n";
    echo "💡 Run: php artisan serve\n";
}
echo "\n";

// Exit code
exit($failedTests > 0 ? 1 : 0);
