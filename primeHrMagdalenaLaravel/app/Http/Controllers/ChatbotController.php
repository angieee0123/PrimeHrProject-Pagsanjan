<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class ChatbotController extends Controller
{
    // Query expansion synonyms (like government chatbot)
    private $querySynonyms = [
        'employee' => ['personnel', 'staff', 'worker', 'empleyado', 'tauhan', 'kawani', 'people', 'workers'],
        'department' => ['office', 'division', 'unit', 'opisina', 'tanggapan', 'kagawaran'],
        'active' => ['working', 'employed', 'aktibo', 'nagtatrabaho'],
        'inactive' => ['terminated', 'resigned', 'hindi aktibo', 'wala na'],
        'count' => ['how many', 'total', 'number', 'ilan', 'ilang', 'gaano karami'],
        'list' => ['show', 'display', 'enumerate', 'ipakita', 'ilista', 'magpakita'],
        'find' => ['search', 'look for', 'who is', 'hanap', 'sino', 'hanapin'],
    ];

    public function chat(Request $request)
    {
        $message = $request->input('message');

        if (empty($message)) {
            return response()->json(['error' => 'No message provided'], 400);
        }

        // Validate query (like government chatbot)
        $validation = $this->isValidQuery($message);
        if (!$validation['valid']) {
            return response()->json([
                'response' => $validation['message'],
                'follow_up_questions' => $this->getDefaultFollowUps(),
                'status' => 'success'
            ]);
        }

        // Expand query with synonyms
        $expandedQueries = $this->expandQuery($message);

        // Detect query type with fuzzy matching
        $queryType = $this->detectQueryType($expandedQueries[0]);

        // Fetch data from database (replaces FAISS search)
        $data = $this->searchDatabase($queryType, $expandedQueries);

        // Generate natural response using Groq LLM
        $response = $this->generateConversationalResponse($message, $queryType, $data);

        return response()->json([
            'response' => $response,
            'query_type' => $queryType,
            'status' => 'success'
        ]);
    }

    private function isValidQuery($query)
    {
        $query = trim($query);

        if (strlen($query) < 3) {
            return ['valid' => false, 'message' => 'Your message is too short. Could you please provide more details?'];
        }

        // Check for gibberish (too many consonants)
        $vowels = preg_match_all('/[aeiouAEIOU]/', $query);
        $consonants = preg_match_all('/[bcdfghjklmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ]/', $query);

        if ($consonants > 0 && ($vowels / max($consonants, 1)) < 0.2) {
            return ['valid' => false, 'message' => "I couldn't understand your message. Could you please rephrase your question?"];
        }

        // Check for too many special characters
        $specialChars = preg_match_all('/[^a-zA-Z0-9\s]/', $query);
        if ($specialChars > strlen($query) * 0.5) {
            return ['valid' => false, 'message' => "I couldn't understand your message. Could you please use regular text?"];
        }

        return ['valid' => true];
    }

    private function expandQuery($query)
    {
        $query_lower = strtolower($query);
        $expanded = [$query];

        foreach ($this->querySynonyms as $term => $synonyms) {
            if (strpos($query_lower, $term) !== false) {
                foreach ($synonyms as $syn) {
                    $expanded[] = str_replace($term, $syn, $query_lower);
                }
            }
            foreach ($synonyms as $syn) {
                if (strpos($query_lower, $syn) !== false) {
                    $expanded[] = str_replace($syn, $term, $query_lower);
                }
            }
        }

        return array_unique(array_slice($expanded, 0, 3));
    }

    private function detectQueryType($message)
    {
        $lower = strtolower($message);
        $lower = $this->normalizeQuery($lower);

        // Greeting detection
        if (preg_match('/^(hi|hello|hey|good morning|good afternoon|good evening|kumusta|kamusta)\b/i', $lower)) {
            return 'greeting';
        }

        // Count queries - more flexible patterns
        if (preg_match('/\b(how many|total|counts?|number of|ilang|gaano karami|ilan|people|tao)\b/i', $lower)) {
            if (preg_match('/\b(employees?|personnel|staff|empleyado|tauhan|tao|people|works?|workers?)\b/i', $lower)) {
                if (preg_match('/\b(active|aktibo)\b/i', $lower)) return 'count_active';
                if (preg_match('/\b(inactive|hindi aktibo|deactivated)\b/i', $lower)) return 'count_inactive';
                if (preg_match('/\b(permanent|permanente)\b/i', $lower)) return 'count_permanent';
                if (preg_match('/\b(job orders?|contractual)\b/i', $lower)) return 'count_job_order';
                return 'count_employees';
            }
            if (preg_match('/\b(departments?|offices?|opisina|tanggapan)\b/i', $lower)) return 'count_departments';
        }

        // List/Show queries
        if (preg_match('/\b(list|show|display|enumerate|ipakita|ilista|all|lahat)\b/i', $lower)) {
            if (preg_match('/\b(employees?|personnel|staff|empleyado)\b/i', $lower)) {
                if (preg_match('/\b(active|aktibo)\b/i', $lower)) return 'list_active_employees';
                if (preg_match('/\b(inactive)\b/i', $lower)) return 'list_inactive_employees';
                return 'list_employees';
            }
            if (preg_match('/\b(departments?|offices?|opisina)\b/i', $lower)) return 'list_departments';
        }

        // Search/Find specific employee
        if (preg_match('/\b(who is|who are|find|search|look for|hanap|sino|info|information|details|detalye)\b/i', $lower)) {
            return 'search_employee';
        }

        // Department-specific queries
        if (preg_match('/\b(mayor|assessor|health|engineers?|treasurer|agriculture|budget)\b/i', $lower)) {
            if (preg_match('/\b(employees?|personnel|staff|works?|trabaho|empleyado)\b/i', $lower)) {
                return 'employees_by_department';
            }
            if (preg_match('/\b(heads?|chiefs?|officers?|directors?|pinuno)\b/i', $lower)) {
                return 'department_head';
            }
        }

        // Position/Role queries
        if (preg_match('/\b(positions?|roles?|trabaho|tungkulin)\b/i', $lower)) {
            return 'search_by_position';
        }

        // Status queries
        if (preg_match('/\b(status|kalagayan)\b/i', $lower) && preg_match('/\b(employees?|empleyado)\b/i', $lower)) {
            return 'employee_status';
        }

        // Contact information queries
        if (preg_match('/\b(contacts?|emails?|phones?|numbers?|telepono)\b/i', $lower)) {
            return 'search_employee';
        }

        // General employee information
        if (preg_match('/\b(employees?|personnel|staff|empleyado)\b/i', $lower)) {
            return 'general_employee_info';
        }

        return 'general';
    }

    private function normalizeQuery($query)
    {
        // Remove extra spaces
        $query = preg_replace('/\s+/', ' ', $query);
        // Remove special characters but keep letters, numbers, spaces
        $query = preg_replace('/[^a-z0-9\s]/i', '', $query);
        return trim($query);
    }

    private function fuzzyMatchEmployee($searchTerm)
    {
        // Fuzzy search for employee names (like government chatbot's fuzzy_match_service)
        $employees = Employee::with(['employmentDetail.department', 'user', 'contacts'])->get();
        $matches = [];

        foreach ($employees as $emp) {
            $fullName = strtolower(trim($emp->first_name . ' ' . $emp->last_name));
            $searchLower = strtolower($searchTerm);

            // Calculate similarity ratio
            similar_text($searchLower, $fullName, $percent);

            if ($percent > 60 || strpos($fullName, $searchLower) !== false) {
                $matches[] = [
                    'employee' => $emp,
                    'score' => $percent
                ];
            }
        }

        // Sort by score descending
        usort($matches, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($matches, 0, 5);
    }

    private function searchDatabase($queryType, $expandedQueries)
    {
        // This replaces FAISS search - directly query database
        $message = $expandedQueries[0];

        switch ($queryType) {
            case 'greeting':
                return ['type' => 'greeting'];

            case 'count_employees':
                return ['count' => Employee::count()];

            case 'count_departments':
                return ['count' => Department::count()];

            case 'count_active':
                return ['count' => User::where('status', 'Active')->count()];

            case 'count_inactive':
                return ['count' => User::where('status', 'Inactive')->count()];

            case 'count_permanent':
                return ['count' => Employee::whereHas('employmentDetail', function($q) {
                    $q->where('employment_status', 'Permanent');
                })->count()];

            case 'count_job_order':
                return ['count' => Employee::whereHas('employmentDetail', function($q) {
                    $q->where('employment_status', 'Job Order');
                })->count()];

            case 'list_employees':
            case 'general_employee_info':
                return Employee::with(['employmentDetail.department', 'user'])
                    ->limit(10)
                    ->get()
                    ->map(function($emp) {
                        return [
                            'name' => trim($emp->first_name . ' ' . $emp->last_name),
                            'position' => $emp->employmentDetail->position ?? 'N/A',
                            'department' => $this->getDepartmentName($emp),
                            'status' => $emp->user->status ?? 'Inactive'
                        ];
                    });

            case 'list_active_employees':
                return Employee::with(['employmentDetail.department', 'user'])
                    ->whereHas('user', function($q) {
                        $q->where('status', 'Active');
                    })
                    ->get()
                    ->map(function($emp) {
                        return [
                            'name' => trim($emp->first_name . ' ' . $emp->last_name),
                            'position' => $emp->employmentDetail->position ?? 'N/A',
                            'department' => $this->getDepartmentName($emp)
                        ];
                    });

            case 'list_inactive_employees':
                return Employee::with(['employmentDetail.department', 'user'])
                    ->whereHas('user', function($q) {
                        $q->where('status', 'Inactive');
                    })
                    ->orWhereDoesntHave('user')
                    ->get()
                    ->map(function($emp) {
                        return [
                            'name' => trim($emp->first_name . ' ' . $emp->last_name),
                            'position' => $emp->employmentDetail->position ?? 'N/A',
                            'department' => $this->getDepartmentName($emp)
                        ];
                    });

            case 'list_departments':
                return Department::all()->map(function($dept) {
                    return [
                        'name' => $dept->name,
                        'head' => $dept->head,
                        'personnel_count' => $dept->personnel_count ?? 0
                    ];
                });

            case 'search_employee':
            case 'employee_status':
                $searchTerm = $this->extractSearchTerm($message);

                // Try exact match first
                $exactMatches = Employee::with(['employmentDetail.department', 'user', 'contacts'])
                    ->where(function($q) use ($searchTerm) {
                        $q->where('first_name', 'like', "%{$searchTerm}%")
                          ->orWhere('last_name', 'like', "%{$searchTerm}%")
                          ->orWhere('employee_id', 'like', "%{$searchTerm}%");
                    })
                    ->limit(5)
                    ->get();

                // If no exact matches, use fuzzy matching
                if ($exactMatches->isEmpty()) {
                    $fuzzyMatches = $this->fuzzyMatchEmployee($searchTerm);
                    $exactMatches = collect(array_map(function($match) {
                        return $match['employee'];
                    }, $fuzzyMatches));
                }

                return $exactMatches->map(function($emp) {
                    return [
                        'name' => trim($emp->first_name . ' ' . $emp->last_name),
                        'employee_id' => $emp->employee_id,
                        'position' => $emp->employmentDetail->position ?? 'N/A',
                        'department' => $this->getDepartmentName($emp),
                        'status' => $emp->user->status ?? 'Inactive',
                        'email' => $emp->email,
                        'contact' => $emp->contacts->first()->number ?? 'N/A'
                    ];
                });

            case 'search_by_position':
                $position = $this->extractSearchTerm($message);
                return Employee::with(['employmentDetail.department', 'user'])
                    ->whereHas('employmentDetail', function($q) use ($position) {
                        $q->where('position', 'like', "%{$position}%");
                    })
                    ->get()
                    ->map(function($emp) {
                        return [
                            'name' => trim($emp->first_name . ' ' . $emp->last_name),
                            'position' => $emp->employmentDetail->position ?? 'N/A',
                            'department' => $this->getDepartmentName($emp),
                            'status' => $emp->user->status ?? 'Inactive'
                        ];
                    });

            case 'employees_by_department':
                $deptName = $this->extractDepartmentName($message);
                $dept = Department::where('name', 'like', "%{$deptName}%")->first();

                if (!$dept) return ['error' => 'Department not found'];

                return [
                    'department' => $dept->name,
                    'employees' => Employee::with(['employmentDetail'])
                        ->whereHas('employmentDetail', function($q) use ($dept) {
                            $q->where('department', $dept->id);
                        })
                        ->get()
                        ->map(function($emp) {
                            return [
                                'name' => trim($emp->first_name . ' ' . $emp->last_name),
                                'position' => $emp->employmentDetail->position ?? 'N/A'
                            ];
                        })
                ];

            case 'department_head':
                $deptName = $this->extractDepartmentName($message);
                $dept = Department::where('name', 'like', "%{$deptName}%")->first();

                if (!$dept) return ['error' => 'Department not found'];

                return [
                    'department' => $dept->name,
                    'head' => $dept->head
                ];

            default:
                return [];
        }
    }

    private function getDepartmentName($employee)
    {
        if (!$employee->employmentDetail || !$employee->employmentDetail->department) {
            return 'N/A';
        }

        if (is_object($employee->employmentDetail->department)) {
            return $employee->employmentDetail->department->name;
        }

        $dept = Department::find($employee->employmentDetail->department);
        return $dept ? $dept->name : 'N/A';
    }

    private function extractSearchTerm($message)
    {
        $patterns = [
            '/who is ([a-zA-Z\s]+)/i',
            '/find ([a-zA-Z\s]+)/i',
            '/search ([a-zA-Z\s]+)/i',
            '/info (?:about|on) ([a-zA-Z\s]+)/i',
            '/details (?:about|on|of) ([a-zA-Z\s]+)/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                return trim($matches[1]);
            }
        }

        $words = explode(' ', $message);
        return end($words);
    }

    private function extractDepartmentName($message)
    {
        $lower = strtolower($message);

        // Match department keywords
        $deptKeywords = [
            'mayor' => 'Mayor',
            'assessor' => 'Assessor',
            'health' => 'Health',
            'engineer' => 'Engineer',
            'treasurer' => 'Treasurer',
            'agriculture' => 'Agriculture',
            'budget' => 'Budget',
            'planning' => 'Planning',
            'social welfare' => 'Social Welfare',
            'civil registry' => 'Civil Registry'
        ];

        foreach ($deptKeywords as $keyword => $deptName) {
            if (preg_match("/\b{$keyword}\b/i", $lower)) {
                return $deptName;
            }
        }

        return '';
    }

    private function generateConversationalResponse($message, $queryType, $data)
    {
        // Handle greeting
        if ($queryType === 'greeting') {
            return "Hello! I'm your PRIME HRIS Assistant. I can help you with employee information, department details, and HR statistics. What would you like to know?";
        }

        // Build context from database data
        $context = $this->buildDetailedContext($queryType, $data);

        // Create prompt for Groq LLM
        $prompt = "You are a helpful HR assistant for the Municipal Government HRIS system. ";
        $prompt .= "Answer the admin's question based on the database information provided. ";
        $prompt .= "Be conversational, clear, and provide complete information.\n\n";
        $prompt .= "Database Information:\n{$context}\n\n";
        $prompt .= "Admin's Question: \"{$message}\"\n\n";
        $prompt .= "Provide a natural, helpful response with all relevant details.";

        try {
            $response = Http::timeout(15)->withHeaders([
                'Authorization' => 'Bearer ' . config('services.groq.api_key'),
                'Content-Type' => 'application/json'
            ])->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => config('services.groq.model', 'llama-3.3-70b-versatile'),
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.7,
                'max_tokens' => 500
            ]);

            if ($response->successful()) {
                $result = $response->json();
                if (isset($result['choices'][0]['message']['content'])) {
                    return $result['choices'][0]['message']['content'];
                }
            } else {
                \Log::error('Groq API error response', ['status' => $response->status(), 'body' => $response->body()]);
            }
        } catch (\Exception $e) {
            \Log::error('Groq API Exception', ['message' => $e->getMessage()]);
        }

        // Fallback response
        return $this->fallbackResponse($queryType, $data);
    }

    private function buildDetailedContext($queryType, $data)
    {
        if (isset($data['error'])) {
            return $data['error'];
        }

        switch ($queryType) {
            case 'count_employees':
            case 'count_departments':
            case 'count_active':
            case 'count_inactive':
            case 'count_permanent':
            case 'count_job_order':
                return "Total count: " . $data['count'];

            case 'list_employees':
            case 'list_active_employees':
            case 'list_inactive_employees':
                $list = "Employees (showing first 10):\n";
                foreach ($data->take(10) as $emp) {
                    $list .= "- {$emp['name']} | {$emp['position']} | {$emp['department']}";
                    if (isset($emp['status'])) $list .= " | Status: {$emp['status']}";
                    $list .= "\n";
                }
                return $list;

            case 'list_departments':
                $list = "Departments:\n";
                foreach ($data as $dept) {
                    $list .= "- {$dept['name']} (Head: {$dept['head']})\n";
                }
                return $list;

            case 'search_employee':
            case 'employee_status':
                if ($data->isEmpty()) {
                    return "No employees found matching the search criteria.";
                }
                $list = "Found employees:\n";
                foreach ($data as $emp) {
                    $list .= "- {$emp['name']} (ID: {$emp['employee_id']})\n";
                    $list .= "  Position: {$emp['position']}\n";
                    $list .= "  Department: {$emp['department']}\n";
                    $list .= "  Status: {$emp['status']}\n";
                    if ($emp['email']) $list .= "  Email: {$emp['email']}\n";
                }
                return $list;

            case 'employees_by_department':
                if (isset($data['employees'])) {
                    $list = "Department: {$data['department']}\nEmployees:\n";
                    foreach ($data['employees'] as $emp) {
                        $list .= "- {$emp['name']} ({$emp['position']})\n";
                    }
                    return $list;
                }
                return "No data available";

            case 'department_head':
                return "Department: {$data['department']}\nHead: {$data['head']}";

            default:
                return "General HR information available";
        }
    }

    private function buildContext($queryType, $data)
    {
        if (isset($data['error'])) {
            return $data['error'];
        }

        switch ($queryType) {
            case 'count_employees':
            case 'count_departments':
            case 'count_active':
            case 'count_inactive':
            case 'count_permanent':
                return "Total count: " . $data['count'];

            case 'list_employees':
                $list = "Employees:\n";
                foreach ($data as $emp) {
                    $list .= "- {$emp['name']} ({$emp['position']}) - {$emp['department']} - Status: {$emp['status']}\n";
                }
                return $list;

            case 'list_departments':
                $list = "Departments:\n";
                foreach ($data as $dept) {
                    $list .= "- {$dept['name']} (Head: {$dept['head']}, Personnel: {$dept['personnel_count']})\n";
                }
                return $list;

            case 'search_employee':
                if ($data->isEmpty()) {
                    return "No employees found matching the search criteria.";
                }
                $list = "Found employees:\n";
                foreach ($data as $emp) {
                    $list .= "- {$emp['name']} (ID: {$emp['employee_id']}) - {$emp['position']} at {$emp['department']} - Status: {$emp['status']}\n";
                }
                return $list;

            case 'employees_by_department':
                if (isset($data['employees'])) {
                    $list = "Department: {$data['department']}\nEmployees:\n";
                    foreach ($data['employees'] as $emp) {
                        $list .= "- {$emp['name']} ({$emp['position']})\n";
                    }
                    return $list;
                }
                return "No data available";

            default:
                return "General HR information available";
        }
    }

    private function fallbackResponse($queryType, $data)
    {
        if (isset($data['error'])) {
            return $data['error'];
        }

        if (isset($data['type']) && $data['type'] === 'greeting') {
            return "Hello! I'm your HRIS Assistant. I can help you with employee information, department details, and HR data. What would you like to know?";
        }

        switch ($queryType) {
            case 'count_employees':
                return "There are currently {$data['count']} employees in the system.";
            case 'count_departments':
                return "There are {$data['count']} departments in the organization.";
            case 'count_active':
                return "There are {$data['count']} active employees.";
            case 'count_inactive':
                return "There are {$data['count']} inactive employees.";
            case 'count_permanent':
                return "There are {$data['count']} permanent employees.";
            case 'count_job_order':
                return "There are {$data['count']} job order employees.";
            case 'department_head':
                return "The head of {$data['department']} is {$data['head']}.";
            default:
                return "I found the information you requested. Please check the details above.";
        }
    }
}
