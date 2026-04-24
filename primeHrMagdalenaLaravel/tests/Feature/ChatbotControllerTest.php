<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Employee;
use App\Models\Department;
use App\Models\User;

class ChatbotControllerTest extends TestCase
{
    public function testChatbotEndpoint()
    {
        echo "\n\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "   PRIME HRIS CHATBOT - COMPREHENSIVE TESTING\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";

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
                'category' => '2. COUNTING QUERIES - Total Employees',
                'questions' => [
                    'How many employees do we have?',
                    'How many people work here?',
                    'Total number of staff',
                    'Count all employees',
                    'Ilang empleyado meron tayo?',
                ]
            ],
            
            [
                'category' => '3. COUNTING QUERIES - Active Employees',
                'questions' => [
                    'How many active employees?',
                    'Count active staff',
                    'Total active personnel',
                    'Ilang aktibong empleyado?',
                ]
            ],
            
            [
                'category' => '4. COUNTING QUERIES - Inactive Employees',
                'questions' => [
                    'How many inactive employees?',
                    'Count inactive staff',
                    'Total deactivated personnel',
                ]
            ],
            
            [
                'category' => '5. COUNTING QUERIES - Permanent Employees',
                'questions' => [
                    'How many permanent employees?',
                    'Count permanent staff',
                    'Ilang permanenteng empleyado?',
                ]
            ],
            
            [
                'category' => '6. COUNTING QUERIES - Job Order Employees',
                'questions' => [
                    'How many job order employees?',
                    'Count contractual staff',
                ]
            ],
            
            [
                'category' => '7. COUNTING QUERIES - Departments',
                'questions' => [
                    'How many departments do we have?',
                    'Total number of offices',
                    'Count all departments',
                ]
            ],
            
            // LISTING QUERIES
            [
                'category' => '8. LISTING QUERIES - All Employees',
                'questions' => [
                    'Show all employees',
                    'List all staff',
                    'Display all personnel',
                    'Ipakita ang lahat ng empleyado',
                ]
            ],
            
            [
                'category' => '9. LISTING QUERIES - Active Employees',
                'questions' => [
                    'Show active employees',
                    'List active staff',
                    'Display active personnel',
                ]
            ],
            
            [
                'category' => '10. LISTING QUERIES - Inactive Employees',
                'questions' => [
                    'Show inactive employees',
                    'List inactive staff',
                ]
            ],
            
            [
                'category' => '11. LISTING QUERIES - Departments',
                'questions' => [
                    'List all departments',
                    'Show all offices',
                    'Display all departments',
                ]
            ],
            
            // SEARCH QUERIES
            [
                'category' => '12. SEARCH QUERIES - By Name',
                'questions' => [
                    'Find John Doe',
                    'Who is Maria Santos?',
                    'Search for Juan dela Cruz',
                    'Hanap si Pedro Garcia',
                ]
            ],
            
            [
                'category' => '13. SEARCH QUERIES - By Position',
                'questions' => [
                    'Find all administrators',
                    'Who are the system administrators?',
                    'Search for engineers',
                ]
            ],
            
            [
                'category' => '14. SEARCH QUERIES - Employee Status',
                'questions' => [
                    'What is the status of John Doe?',
                    'Check employee status',
                ]
            ],
            
            // DEPARTMENT QUERIES
            [
                'category' => '15. DEPARTMENT QUERIES - Department Head',
                'questions' => [
                    'Who is the head of Mayor office?',
                    'Who heads the health office?',
                    'Sino ang pinuno ng Mayor office?',
                ]
            ],
            
            [
                'category' => '16. DEPARTMENT QUERIES - Department Personnel',
                'questions' => [
                    'Who works in Mayor office?',
                    'Show employees in health office',
                    'List staff in engineering department',
                ]
            ],
            
            // NATURAL LANGUAGE VARIATIONS
            [
                'category' => '17. NATURAL LANGUAGE VARIATIONS',
                'questions' => [
                    'How many workers do we have?',
                    'Total people employed',
                    'Show me all the people working here',
                    'Find someone named administrator',
                ]
            ],
            
            // BILINGUAL (TAGLISH)
            [
                'category' => '18. BILINGUAL QUERIES (Taglish)',
                'questions' => [
                    'How many empleyado meron?',
                    'Show me all aktibong staff',
                    'Sino ang head ng engineering?',
                ]
            ],
        ];

        $totalTests = 0;
        $passedTests = 0;
        $failedTests = 0;

        foreach ($testQuestions as $category) {
            echo "\n";
            echo "───────────────────────────────────────────────────────────────\n";
            echo "  {$category['category']}\n";
            echo "───────────────────────────────────────────────────────────────\n";

            foreach ($category['questions'] as $question) {
                $totalTests++;
                
                echo "\n📝 Question: \"{$question}\"\n";
                
                try {
                    $response = $this->postJson('/api/chatbot', [
                        'message' => $question
                    ]);

                    if ($response->status() === 200) {
                        $data = $response->json();
                        
                        echo "✅ Status: SUCCESS\n";
                        echo "🔍 Query Type: " . ($data['query_type'] ?? 'N/A') . "\n";
                        echo "💬 Response: " . substr($data['response'], 0, 150) . "...\n";
                        
                        if (isset($data['follow_up_questions']) && count($data['follow_up_questions']) > 0) {
                            echo "🔗 Follow-ups: " . implode(', ', array_slice($data['follow_up_questions'], 0, 2)) . "\n";
                        }
                        
                        $passedTests++;
                    } else {
                        echo "❌ Status: FAILED (HTTP {$response->status()})\n";
                        echo "⚠️  Error: " . ($response->json()['error'] ?? 'Unknown error') . "\n";
                        $failedTests++;
                    }
                } catch (\Exception $e) {
                    echo "❌ Status: EXCEPTION\n";
                    echo "⚠️  Error: " . $e->getMessage() . "\n";
                    $failedTests++;
                }
            }
        }

        // SUMMARY
        echo "\n\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "   TEST SUMMARY\n";
        echo "═══════════════════════════════════════════════════════════════\n";
        echo "Total Tests:  {$totalTests}\n";
        echo "✅ Passed:    {$passedTests}\n";
        echo "❌ Failed:    {$failedTests}\n";
        echo "Success Rate: " . round(($passedTests / $totalTests) * 100, 2) . "%\n";
        echo "═══════════════════════════════════════════════════════════════\n\n";

        $this->assertTrue($passedTests > 0, 'At least some tests should pass');
    }
}
