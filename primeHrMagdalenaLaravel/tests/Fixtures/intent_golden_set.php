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

    // ── data_query ──────────────────────────────────────────────────────────
    'list all employees with 2 or more absences' => 'data_query',
    'table of leave applications this month' => 'data_query',
    'breakdown of deductions by type' => 'data_query',
    'what is the average salary by department' => 'data_query',
    'show attendance records for October' => 'data_query',
    'leave applications approved in June' => 'data_query',
    // Naming a transactional record beats the employee-lookup rule: none of
    // these can be answered by a name/department/hire-date search.
    'employees with more than 5 late arrivals' => 'data_query',
    'who is on leave today' => 'data_query',
    'find employees with unused leave credits' => 'data_query',
    'spreadsheet of overtime hours' => 'data_query',
];
