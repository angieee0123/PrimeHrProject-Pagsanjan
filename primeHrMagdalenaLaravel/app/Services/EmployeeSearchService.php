<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Feature 1: AI Knowledge Assistant.
 *
 * Answers "who / where / which employee" questions over the employees +
 * employment_details + departments + designations cluster, scoped to whatever
 * the asking user is allowed to see.
 */
class EmployeeSearchService
{
    private const MAX_RESULTS = 50;

    public function __construct(private AiAccessPolicy $policy)
    {
    }

    /**
     * @param array<int, array{role: string, content: string}> $history
     */
    public function search(User $user, string $query, array $history = []): array
    {
        $params = $this->parseSearchQuery($query);
        $employees = $this->runSearch($user, $params);
        $rows = $this->formatEmployeeResults($employees);

        return [
            'answer' => $this->narrate($user, $query, $rows, $params, $history),
            'data' => $rows,
            'count' => $rows->count(),
        ];
    }

    /**
     * Pull structured filters out of the question. Anything we cannot parse is
     * simply left off, so a vague question degrades to a broad listing rather
     * than an empty result.
     *
     * @return array<string, mixed>
     */
    private function parseSearchQuery(string $query): array
    {
        $params = [];

        // "2024-104" style employee numbers, or a bare "employee id 57".
        if (preg_match('/\b(?:employee\s*)?(?:id|number|no\.?)\s*[:#]?\s*(\d{4}-\d{2,4}|\d+)\b/i', $query, $m)) {
            $params['employee_id'] = $m[1];
        } elseif (preg_match('/\b(\d{4}-\d{2,4})\b/', $query, $m)) {
            $params['employee_id'] = $m[1];
        }

        // Department by name, matched against what actually exists.
        foreach (Department::query()->get(['id', 'name', 'code']) as $department) {
            $needle = trim((string) $department->name);
            if ($needle !== '' && stripos($query, $needle) !== false) {
                $params['department_id'] = $department->id;
                break;
            }
        }

        // Hire-date windows: "hired in 2024", "hired after 2023".
        if (preg_match('/hired?\s+(?:in|during|on)\s+(\d{4})/i', $query, $m)) {
            $params['hired_year'] = (int) $m[1];
        } elseif (preg_match('/hired?\s+(?:after|since|from)\s+(\d{4})/i', $query, $m)) {
            $params['hired_after'] = $m[1] . '-01-01';
        } elseif (preg_match('/hired?\s+before\s+(\d{4})/i', $query, $m)) {
            $params['hired_before'] = $m[1] . '-01-01';
        }

        if (preg_match('/\b(active|permanent|casual|contractual|job\s*order|resigned|retired)\b/i', $query, $m)) {
            $params['employment_status'] = $m[1];
        }

        // A quoted or capitalised name is the most reliable signal we have;
        // fall back to the words following a search verb. The verbs are matched
        // case-insensitively via (?i:…) while the name itself stays
        // case-sensitive — capitalisation is what marks it as a name.
        // Skipped entirely once an employee number is in hand: "Employee ID
        // 2024001" would otherwise capture the literal word "Employee".
        if (!isset($params['employee_id'])) {
            if (preg_match('/"([^"]+)"/', $query, $m)) {
                $params['name'] = $this->cleanName($m[1]);
            } elseif (preg_match('/(?i:\bnamed|\bcalled|\bfor|\babout|\bof)\s+([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)/', $query, $m)) {
                $params['name'] = $this->cleanName($m[1]);
            } elseif (preg_match('/(?i:\bfind|\bshow|\bsearch|\blocate|\bwhere\s+is|\bwho\s+is|\blook\s+up)\s+(?i:me\s+)?(?i:the\s+)?(?i:employees?\s+)?([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)/', $query, $m)) {
                $params['name'] = $this->cleanName($m[1]);
            }
        }

        return array_filter($params, fn ($value) => $value !== null);
    }

    /**
     * Capitalisation alone is a weak signal for a name — sentence-initial and
     * domain words get capitalised too. Reject the ones that are never names.
     */
    private function cleanName(string $candidate): ?string
    {
        $candidate = trim($candidate);

        $notNames = ['employee', 'employees', 'department', 'departments', 'staff', 'personnel',
            'all', 'the', 'show', 'find', 'list', 'search', 'who', 'where', 'id', 'number',
            'active', 'total', 'name', 'record', 'records', 'everyone', 'anybody', 'people'];

        if ($candidate === '' || in_array(strtolower($candidate), $notNames, true)) {
            return null;
        }

        // Strip a leading noise word: "Employee Maria Santos" → "Maria Santos".
        $words = preg_split('/\s+/', $candidate) ?: [];
        while (!empty($words) && in_array(strtolower($words[0]), $notNames, true)) {
            array_shift($words);
        }

        $cleaned = implode(' ', $words);

        return $cleaned === '' ? null : $cleaned;
    }

    /**
     * @param array<string, mixed> $params
     * @return Collection<int, Employee>
     */
    private function runSearch(User $user, array $params): Collection
    {
        $query = Employee::query()->with([
            'employmentDetail.departmentRelation',
            'employmentDetail.designationRelation',
        ]);

        if (!empty($params['name'])) {
            $name = $params['name'];
            // Grouped so the OR branches cannot leak past the other filters.
            $query->where(function (Builder $q) use ($name) {
                $q->whereRaw("CONCAT_WS(' ', first_name, middle_name, last_name) LIKE ?", ["%{$name}%"])
                    ->orWhere('first_name', 'like', "%{$name}%")
                    ->orWhere('last_name', 'like', "%{$name}%")
                    ->orWhere('employee_id', 'like', "%{$name}%");
            });
        }

        if (!empty($params['employee_id'])) {
            $query->where('employee_id', $params['employee_id']);
        }

        $employmentFilters = array_filter([
            'department_id' => $params['department_id'] ?? null,
            'hired_year' => $params['hired_year'] ?? null,
            'hired_after' => $params['hired_after'] ?? null,
            'hired_before' => $params['hired_before'] ?? null,
            'employment_status' => $params['employment_status'] ?? null,
        ], fn ($value) => $value !== null);

        if (!empty($employmentFilters)) {
            $query->whereHas('employmentDetail', function (Builder $q) use ($employmentFilters) {
                if (isset($employmentFilters['department_id'])) {
                    $q->where('department_id', $employmentFilters['department_id']);
                }
                if (isset($employmentFilters['hired_year'])) {
                    $q->whereYear('appointment_date', $employmentFilters['hired_year']);
                }
                if (isset($employmentFilters['hired_after'])) {
                    $q->whereDate('appointment_date', '>=', $employmentFilters['hired_after']);
                }
                if (isset($employmentFilters['hired_before'])) {
                    $q->whereDate('appointment_date', '<', $employmentFilters['hired_before']);
                }
                if (isset($employmentFilters['employment_status'])) {
                    $q->where('employment_status', 'like', '%' . $employmentFilters['employment_status'] . '%');
                }
            });
        }

        $this->policy->scopeEmployeeQuery($query, $user);

        return $query->limit(self::MAX_RESULTS)->get();
    }

    /**
     * @param Collection<int, Employee> $employees
     * @return Collection<int, array<string, mixed>>
     */
    private function formatEmployeeResults(Collection $employees): Collection
    {
        return $employees->map(function (Employee $employee) {
            $employment = $employee->employmentDetail;

            return [
                'employee_id' => $employee->employee_id,
                'name' => trim(implode(' ', array_filter([
                    $employee->first_name,
                    $employee->middle_name,
                    $employee->last_name,
                    $employee->suffix,
                ]))),
                'email' => $employee->email,
                'department' => $employment?->departmentRelation?->name,
                'designation' => $employment?->designationRelation?->title,
                'employment_status' => $employment?->employment_status,
                'appointment_date' => $employment?->appointment_date,
                'salary_grade' => $employment?->salary_grade,
            ];
        })->values();
    }

    /**
     * @param Collection<int, array<string, mixed>> $rows
     * @param array<string, mixed> $params
     * @param array<int, array{role: string, content: string}> $history
     */
    private function narrate(User $user, string $query, Collection $rows, array $params, array $history): string
    {
        // An employee reaching this capability gets their own record back, so
        // addressing them as HR staff reads as though they were looking at
        // someone else's file. AiAccessPolicy owns the distinction.
        $audience = $this->policy->audienceLabel($user);
        $notice = $this->policy->scopeNotice($user);

        if ($rows->isEmpty()) {
            $applied = empty($params)
                ? 'no specific filters'
                : implode(', ', array_map(
                    fn ($key, $value) => "{$key}={$value}",
                    array_keys($params),
                    array_values($params)
                ));

            // "No employees matched" would otherwise assert the person does not
            // exist, when for a self-scoped caller it only means they are not
            // the person searched for.
            return "No employees matched that search ({$applied}). Try a different name, department, or year."
                . ($notice ? "\n\n{$notice}" : '');
        }

        $scopeNote = $this->policy->scopePromptNote($user);

        $system = <<<PROMPT
You are the PRIME HRIS Assistant. You are given the result of an employee
lookup that has ALREADY been filtered to what this user is allowed to see.

{$scopeNote}

Summarise the results conversationally for {$audience}:
- Lead with how many employees matched.
- Mention the notable ones by name with their department and position.
- Point out any pattern worth noting (department concentration, hire dates).
- Never invent employees, fields, or numbers that are not in the data.
- Keep it under 150 words. Do not output raw JSON or a markdown table.
PROMPT;

        $payload = json_encode($rows->take(25)->all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $messages = $history;
        $messages[] = [
            'role' => 'user',
            'content' => "Question: {$query}\n\nMatched {$rows->count()} employee(s):\n{$payload}",
        ];

        $answer = AiChatService::chat($user, $system, $messages, 0.3, 700);

        // Appended rather than left to the prompt: the scope note above steers
        // the model, but the disclosure has to hold even when it ignores it.
        return ($answer ?: $this->fallbackNarration($rows))
            . ($notice ? "\n\n{$notice}" : '');
    }

    /**
     * @param Collection<int, array<string, mixed>> $rows
     */
    private function fallbackNarration(Collection $rows): string
    {
        $lines = $rows->take(10)->map(function (array $row) {
            $parts = array_filter([
                $row['name'],
                $row['employee_id'] ? "({$row['employee_id']})" : null,
                $row['designation'],
                $row['department'],
            ]);

            return '- ' . implode(' · ', $parts);
        })->implode("\n");

        $more = $rows->count() > 10 ? "\n…and " . ($rows->count() - 10) . ' more.' : '';

        return "Found {$rows->count()} employee(s):\n{$lines}{$more}";
    }
}
