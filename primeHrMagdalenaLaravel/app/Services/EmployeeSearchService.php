<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Designation;
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

        // Office-holder lookups ("who is the mayor") answer a single person,
        // not a department roster. They take a separate path because the
        // general search would otherwise match "Mayor's Office" as a department
        // and list everyone assigned there.
        if (!empty($params['role'])) {
            $employees = $this->runRoleSearch($user, $params['role']);
            $rows = $this->formatEmployeeResults($employees);

            return [
                'answer' => $this->narrateRole($user, $params['role'], $rows),
                'data' => $rows,
                'count' => $rows->count(),
            ];
        }

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
        //
        // Both sides are punctuation-normalised first. Four of these offices
        // have an apostrophe in the name ("Mayor's Office") and the rows store
        // the straight ASCII one, while phone keyboards and anything pasted out
        // of Word produce the curly U+2019. A literal comparison therefore
        // missed the department entirely, and the query fell through to the
        // role rule below and answered about the office*holder* instead.
        $haystack = $this->normalisePunctuation($query);

        foreach (Department::query()->get(['id', 'name', 'code']) as $department) {
            $needle = $this->normalisePunctuation((string) $department->name);

            if ($needle !== '' && stripos($haystack, $needle) !== false) {
                $params['department_id'] = $department->id;
                break;
            }
        }

        // Office-holder lookup: "who is the mayor" means the employee holding
        // that office, not everyone in the Mayor's Office. Resolved from a user
        // account holding the role, and failing that from the designation title
        // — the appointment is the fact being asked about; a login is not.
        //
        // Skipped entirely once the query has named a real department. The role
        // words are all also *office* names here — "Mayor's Office", "Human
        // Resources" — so "show me employees in the Mayor's Office" matched
        // \bmayor\b and returned the single role-holder, or, when no account
        // held the role, "No employee account is currently linked to the Mayor
        // role" for a question about a department with staff in it. A named
        // department is the more specific signal and wins.
        if (isset($params['department_id'])) {
            // The department filter below answers this; a role lookup would
            // replace a roster with one person.
        } elseif (preg_match('/\bvice\s+mayor\b/i', $query)) {
            // No `vice_mayor` user role exists, so this resolves purely through
            // the designation search. It used to be left to the general search,
            // which — with no name and no department to filter on — answered
            // "who is the vice mayor" with the entire roster.
            $params['role'] = 'vice_mayor';
        } elseif (preg_match('/\bmayor\b/i', $query)) {
            $params['role'] = 'mayor';
        } elseif (preg_match('/\b(?:system\s+|municipal\s+)?administrator\b/i', $query) || preg_match('/\badmin\b/i', $query)) {
            $params['role'] = 'admin';
        } elseif (preg_match('/\b(?:hr\s*(?:officer|head|personnel)?|human\s+resources)\b/i', $query)) {
            $params['role'] = 'hr';
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
     * Fold the quote characters a user's keyboard might produce onto the ASCII
     * ones the database stores, and collapse runs of whitespace.
     */
    private function normalisePunctuation(string $value): string
    {
        $value = str_replace(
            ["\u{2018}", "\u{2019}", "\u{201B}", '´', '`', "\u{201C}", "\u{201D}"],
            ["'", "'", "'", "'", "'", '"', '"'],
            $value
        );

        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
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
                    // The same concatenation without the middle name. People are
                    // addressed as "Jeremy Pogi", but the stored row is "Jeremy
                    // Reyes Pogi", so the phrase match above needs the middle
                    // name to be absent to succeed — and every employee in this
                    // database has one. Searching any colleague by the name you
                    // would actually call them therefore returned nothing at all.
                    ->orWhereRaw("CONCAT_WS(' ', first_name, last_name) LIKE ?", ["%{$name}%"])
                    ->orWhere('first_name', 'like', "%{$name}%")
                    ->orWhere('last_name', 'like', "%{$name}%")
                    ->orWhere('employee_id', 'like', "%{$name}%");

                // Every word present somewhere in the full name, in any order.
                // Catches middle-name-in-the-middle ("Jeremy Pogi"), reversed
                // order ("Pogi, Jeremy"), and a middle name the asker does know
                // — none of which a single LIKE over one phrase can express.
                $tokens = array_filter(preg_split('/\s+/', trim($name)) ?: []);

                if (count($tokens) > 1) {
                    $q->orWhere(function (Builder $all) use ($tokens) {
                        foreach ($tokens as $token) {
                            $all->whereRaw(
                                "CONCAT_WS(' ', first_name, middle_name, last_name) LIKE ?",
                                ["%{$token}%"]
                            );
                        }
                    });
                }
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
     * The employee(s) linked to a user account holding the given role.
     *
     * `users` is deliberately excluded from generated SQL, but that ban is on
     * arbitrary model-generated statements in SafeSqlService, not on scoped
     * application code: this lookup goes through Eloquent and is re-scoped
     * through AiAccessPolicy like every other retrieval, so it cannot leak a
     * record the caller may not see.
     */
    private function runRoleSearch(User $user, string $role): Collection
    {
        $employeeIds = User::query()
            ->where('roles', 'like', '%"' . $role . '"%')
            ->get()
            ->filter(fn (User $u) => $u->hasRole($role))
            ->pluck('employee_id')
            ->filter(fn ($id) => $id !== null)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        // No account holds the role — but the office almost certainly still
        // exists as a *job title*. "Sino ang mayor natin?" was answered "no
        // employee account is currently linked to the Mayor role" while the
        // designation "Municipal Mayor" sat one join away on
        // employment_details.designation_id, held by an employee in the Mayor's
        // Office. A user role is an access-control fact about a login; a
        // designation is the appointment itself, and the appointment is what
        // the question is about.
        if ($employeeIds->isEmpty()) {
            return $this->runDesignationRoleSearch($user, $role);
        }

        $query = Employee::query()->with([
            'employmentDetail.departmentRelation',
            'employmentDetail.designationRelation',
        ])->whereIn('id', $employeeIds);

        $this->policy->scopeEmployeeQuery($query, $user);

        return $query->limit(self::MAX_RESULTS)->get();
    }

    /**
     * The office-holder by *appointment* rather than by login role.
     *
     * `designations.title` is the authoritative record of who holds an office —
     * a user account is only how somebody signs in, and this municipality has
     * offices whose holder has no account at all. Matched against the live
     * titles so a renamed designation cannot leave a stale pattern behind.
     *
     * @return Collection<int, Employee>
     */
    private function runDesignationRoleSearch(User $user, string $role): Collection
    {
        // "Municipal Vice Mayor" contains "Mayor", and answering "who is the
        // mayor" with the vice mayor is worse than answering with nothing.
        [$include, $exclude] = match ($role) {
            'mayor' => ['%mayor%', '%vice%'],
            'vice_mayor' => ['%vice mayor%', null],
            'admin' => ['%administrator%', null],
            'hr' => ['%human resource%', null],
            default => [null, null],
        };

        if ($include === null) {
            return collect();
        }

        $titles = Designation::query()->where('title', 'like', $include);

        if ($exclude !== null) {
            $titles->where('title', 'not like', $exclude);
        }

        $designationIds = $titles->pluck('id');

        if ($designationIds->isEmpty()) {
            return collect();
        }

        $query = Employee::query()
            ->with(['employmentDetail.departmentRelation', 'employmentDetail.designationRelation'])
            ->whereHas(
                'employmentDetail',
                fn (Builder $q) => $q->whereIn('designation_id', $designationIds)
            );

        $this->policy->scopeEmployeeQuery($query, $user);

        return $query->limit(self::MAX_RESULTS)->get();
    }

    /**
     * @param Collection<int, array<string, mixed>> $rows
     */
    private function narrateRole(User $user, string $role, Collection $rows): string
    {
        $label = match ($role) {
            'mayor' => 'Mayor',
            'vice_mayor' => 'Vice Mayor',
            'admin' => 'system administrator',
            'hr' => 'HR officer',
            default => $role,
        };

        $notice = $this->policy->scopeNotice($user);

        if ($rows->isEmpty()) {
            // Both routes were tried — a user account holding the role, and an
            // employee holding the matching designation. Saying only "no
            // account is linked" describes one of the two searches and reads as
            // a system-configuration problem, when the actual finding is that
            // the post is vacant in the records.
            return "I could not find anyone recorded as the {$label}: no user account holds that role, "
                . 'and no employee is currently assigned that designation.'
                . ($notice ? "\n\n{$notice}" : '');
        }

        $lines = $rows->map(function (array $row) {
            return '- ' . implode(' · ', array_filter([
                $row['name'],
                $row['employee_id'] ? "({$row['employee_id']})" : null,
                $row['designation'],
                $row['department'],
            ]));
        })->implode("\n");

        $opening = $rows->count() === 1
            ? "The {$label} is:"
            : "Employees recorded as {$label}:";

        return $opening . "\n" . $lines . ($notice ? "\n\n{$notice}" : '');
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
        // A single match is almost always a question *about that person* —
        // "employment status of X", "salary grade of Y" — so the one field the
        // asker wanted must be in the sentence. This used to print name,
        // designation and department only, which answered a different question
        // than the one asked and left the reader to hunt the attached table.
        // Above one row it stays a roster: the extra fields turn ten matches
        // into an unreadable wall.
        $detailed = $rows->count() === 1;

        $lines = $rows->take(10)->map(function (array $row) use ($detailed) {
            $parts = array_filter([
                $row['name'],
                $row['employee_id'] ? "({$row['employee_id']})" : null,
                $row['designation'],
                $row['department'],
                $detailed && !empty($row['employment_status']) ? "Status: {$row['employment_status']}" : null,
                $detailed && !empty($row['salary_grade']) ? "SG: {$row['salary_grade']}" : null,
                $detailed && !empty($row['appointment_date'])
                    ? 'Appointed: ' . $this->formatDate($row['appointment_date'])
                    : null,
                $detailed && !empty($row['email']) ? "Email: {$row['email']}" : null,
            ]);

            return '- ' . implode(' · ', $parts);
        })->implode("\n");

        $more = $rows->count() > 10 ? "\n…and " . ($rows->count() - 10) . ' more.' : '';

        return "Found {$rows->count()} employee(s):\n{$lines}{$more}";
    }

    /**
     * `employment_details` has no `updated_at` and its dates arrive as either a
     * Carbon instance or a raw string depending on the model's casts, so this
     * normalises both rather than assuming one.
     */
    private function formatDate(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('M d, Y');
        }

        $text = trim((string) $value);

        try {
            return \Carbon\Carbon::parse($text)->format('M d, Y');
        } catch (\Throwable) {
            return $text;
        }
    }
}
