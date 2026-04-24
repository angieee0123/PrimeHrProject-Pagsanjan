<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Http;

$groqApiKey = '***REMOVED-GROQ-KEY***';

echo "Testing Groq API connection...\n\n";

try {
    $response = \Illuminate\Support\Facades\Http::timeout(15)->withHeaders([
        'Authorization' => 'Bearer ' . $groqApiKey,
        'Content-Type' => 'application/json'
    ])->post('https://api.groq.com/openai/v1/chat/completions', [
        'model' => 'llama-3.3-70b-versatile',
        'messages' => [
            ['role' => 'user', 'content' => 'Say "Hello from Groq!" in one sentence.']
        ],
        'temperature' => 0.7,
        'max_tokens' => 50
    ]);

    echo "Status Code: " . $response->status() . "\n";
    echo "Response Body:\n";
    print_r($response->json());
    
    if ($response->successful()) {
        $result = $response->json();
        if (isset($result['choices'][0]['message']['content'])) {
            echo "\n✅ SUCCESS! Groq API Response:\n";
            echo $result['choices'][0]['message']['content'] . "\n";
        }
    } else {
        echo "\n❌ FAILED! Response:\n";
        echo $response->body() . "\n";
    }
    
} catch (\Exception $e) {
    echo "\n❌ EXCEPTION: " . $e->getMessage() . "\n";
}
