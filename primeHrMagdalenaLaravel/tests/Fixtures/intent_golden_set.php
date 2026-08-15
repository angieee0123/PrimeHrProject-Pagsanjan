<?php

/**
 * The intent golden set — questions people actually type, and where each one
 * must be routed.
 *
 * AiQueryService::detectIntent() is a single `match(true)` whose ordering is
 * load-bearing: a rule added in the wrong place silently steals questions from
 * the rule below it, and nothing else in the suite would notice. This file is
 * the scoreboard for that. IntentRoutingGoldenSetTest runs every entry and
 * reports the misroutes together.
 *
 * Adding a case is the cheapest way to pin behaviour you care about. Add one
 * BEFORE reordering or extending the rules, not after.
 *
 * Format: question => expected intent.
 */
return [

    // ── capabilities ────────────────────────────────────────────────────────
    // "What can you do?" is usually the first message in any thread.
    'what can you do?' => 'capabilities',
    'what can i ask you' => 'capabilities',
    'what can you help me with' => 'capabilities',
    'who are you' => 'capabilities',
    'anong kaya mo' => 'capabilities',
    // Tagalog particles between every part of the phrase — the form the
    // question is actually typed in, which the fixed "ano ang pwede" sequence
    // missed. It reached text-to-SQL and came back as a table of absences.
    'ano lang ba ang mga pwede na itanong sayo?' => 'capabilities',
    'ano ang mga tanong na pwede ko sayo' => 'capabilities',
    'ano pa ang pwede kong itanong' => 'capabilities',
    'ano ang magagawa mo' => 'capabilities',
    'help' => 'capabilities',

    // ── chart ───────────────────────────────────────────────────────────────
    'show me a bar chart of headcount by department' => 'chart',
    'plot attendance trends for this month' => 'chart',
    'visualize leave usage by department' => 'chart',
    'pie chart of employment status' => 'chart',
    'graph the absences per month' => 'chart',
    'line chart of payroll cost over the year' => 'chart',

    // ── how_to ──────────────────────────────────────────────────────────────
    // Questions about the system, not the data in it. Checked before the
    // stored-file rule because "file" is a verb here at least as often as a
    // noun, and before self-service because "how do I check my leave balance"
    // is an instruction request, not a lookup of that balance.
    'how do i file a leave application?' => 'how_to',
    'how to add a training record' => 'how_to',
    'how do i check my leave balance' => 'how_to',
    'how can i view my payslip' => 'how_to',
    'how does an employee request leave' => 'how_to',
    'what is the process for a travel order' => 'how_to',
    'what is the policy on late deductions' => 'how_to',
    'paano mag-file ng leave' => 'how_to',
    'pano mag time in' => 'how_to',

    // ── document_search ─────────────────────────────────────────────────────
    "show me juan's 201 file" => 'document_search',
    'find the medical certificate of Maria Santos' => 'document_search',
    "show me ana's photo" => 'document_search',
    'her philhealth scan' => 'document_search',
    'list all documents of Juan' => 'document_search',
    'open the contract for Pedro Reyes' => 'document_search',
    'display his government id' => 'document_search',
    "download maria's diploma" => 'document_search',

    // ── report ──────────────────────────────────────────────────────────────
    'generate an attendance summary report' => 'report',
    'generate the payroll report for June' => 'report',
    'create a leave summary report' => 'report',
    'export the DTR report' => 'report',
    'produce a monthly attendance report' => 'report',
    'i need the absences report' => 'report',

    // ── workflow ────────────────────────────────────────────────────────────
    // "draft a certificate" produces a new document; the stored-file rule used
    // to claim it on the noun "certificate" and answer with a search for
    // existing ones. Only authoring verbs suppress that rule — see the
    // retrieval cases below, which must stay document_search.
    'draft a certificate of employment for Juan' => 'workflow',
    'issue a certificate of employment' => 'workflow',
    'generate an onboarding checklist' => 'workflow',
    'prepare a payroll preview' => 'workflow',
    'write a memo about the new policy' => 'workflow',
    'approval summary for this week' => 'workflow',

    // ── self_service ────────────────────────────────────────────────────────
    // The caller's own records. Must beat both the dashboard rule ("how many
    // credits do I have") and the data_query rule ("my attendance"), or an
    // employee gets refused for asking about themselves.
    'what is my leave balance' => 'self_service',
    'show my latest payslip' => 'self_service',
    'how many VL credits do I have left?' => 'self_service',
    'do I have any pending leave' => 'self_service',
    'my attendance this month' => 'self_service',
    'what are my deductions' => 'self_service',
    'show my trainings' => 'self_service',
    'my travel orders' => 'self_service',
    'aking payslip' => 'self_service',
    'who am i' => 'self_service',
    'my profile' => 'self_service',

    // ── dashboard ───────────────────────────────────────────────────────────
    'how many employees are on leave today' => 'dashboard',
    'how many employees do we have' => 'dashboard',
    'total number of departments' => 'dashboard',
    'which department has the most absences' => 'dashboard',
    'pending leave approvals' => 'dashboard',
    'count of active employees' => 'dashboard',
    'give me an overview of this month' => 'dashboard',
    'how much is the total payroll cost' => 'dashboard',

    // ── employee_search ─────────────────────────────────────────────────────
    'who is the head of the accounting department' => 'employee_search',
    'find employees hired in 2024' => 'employee_search',
    'where is Juan dela Cruz assigned' => 'employee_search',
    'show me employees in the treasury office' => 'employee_search',
    'employees appointed last year' => 'employee_search',
    'who is the department head of HR' => 'employee_search',
    // Office-holder lookups must resolve to the person holding the role, not
    // to a department roster or to a "who's" the classifier has to guess at.
    // A named person plus one of their personnel-record fields. These carry no
    // interrogative and no transactional noun, so every rule declined them and
    // they fell to the model classifier — the one branch that stops working
    // when the provider does.
    'Employment Status of Jeremy Pogi' => 'employee_search',
    'Department of Ana Ramos' => 'employee_search',
    "what is Juan Dela Cruz's position" => 'employee_search',
    'salary grade of Pedro Santos' => 'employee_search',
    'who is the mayor' => 'employee_search',
    "who's the mayor" => 'employee_search',
    'sino ang mayor' => 'employee_search',
    'who is the hr officer' => 'employee_search',
    'who is the system administrator' => 'employee_search',

    // ── data_query ──────────────────────────────────────────────────────────
    'list all employees with 2 or more absences' => 'data_query',
    'table of leave applications this month' => 'data_query',
    'breakdown of deductions by type' => 'data_query',
    'what is the average salary by department' => 'data_query',
    'show attendance records for October' => 'data_query',
    'leave applications approved in June' => 'data_query',
    // Naming a transactional record beats the employee-lookup rule: none of
    // these can be answered by a name/department/hire-date search.
    // The establishment itself — which posts an office holds and what they pay.
    // `designations` carries title, department_id and monthly_rate, so these are
    // one table away; unrouted, they reached the knowledge base and were
    // answered with invented job titles.
    'ano ano ang mga job designation sa accounting office?' => 'data_query',
    'what designations are in the accounting office' => 'data_query',
    'ano ang mga posisyon sa treasury office' => 'data_query',
    'magkano ang sinasahod ng isang accounting clerk?' => 'data_query',
    'how much does a bookkeeper earn' => 'data_query',
    // Group travel. The other travellers are rows in travel_order_companions,
    // so a companion question is about that table — not about the employee
    // roster, which is where naming a person used to send it.
    'sino ang kasama sa travel order ni Juan' => 'data_query',
    'who is travelling with Jeremy next week' => 'data_query',
    'who are the companions on that travel order' => 'data_query',
    'employees with more than 5 late arrivals' => 'data_query',
    'who is on leave today' => 'data_query',
    'find employees with unused leave credits' => 'data_query',
    'spreadsheet of overtime hours' => 'data_query',
];
