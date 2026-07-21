<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CS Form No. 6 - Application for Leave</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', monospace;
            font-size: 10pt;
            color: #000;
            background: white;
            padding: 0.4in;
            line-height: 1.1;
        }
        .page {
            width: 8.5in;
            height: 11in;
            margin: 0 auto;
            position: relative;
        }
        .header-section {
            text-align: center;
            margin-bottom: 0.2in;
            border-bottom: 2px solid #000;
            padding-bottom: 0.1in;
        }
        .logo-header {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.05in;
        }
        .logo-header img {
            height: 0.5in;
            margin-right: 0.15in;
        }
        .header-text {
            text-align: center;
            font-weight: bold;
            line-height: 1.2;
        }
        .header-text div {
            font-size: 10pt;
        }
        .form-title {
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 0.05in;
        }
        .form-subtitle {
            font-size: 10pt;
            font-weight: bold;
        }
        .agency-name {
            font-size: 9pt;
            margin-top: 2px;
        }
        .form-body {
            font-size: 10pt;
            line-height: 1.3;
        }
        .item {
            margin-bottom: 0.12in;
            page-break-inside: avoid;
        }
        .item-label {
            font-weight: bold;
            margin-bottom: 0.02in;
        }
        .item-content {
            margin-left: 0.1in;
            border-bottom: 1px solid #000;
            min-height: 0.22in;
            padding: 2px 3px;
            font-size: 10pt;
        }
        .item-content.short {
            min-height: 0.18in;
        }
        .item-content.long {
            min-height: 0.35in;
        }
        .two-col {
            display: table;
            width: 100%;
            margin-bottom: 0.08in;
        }
        .two-col-item {
            display: table-cell;
            width: 50%;
            padding-right: 0.15in;
            vertical-align: top;
        }
        .three-col {
            display: table;
            width: 100%;
            margin-bottom: 0.08in;
        }
        .three-col-item {
            display: table-cell;
            width: 33.33%;
            padding-right: 0.1in;
            vertical-align: top;
        }
        .divider {
            border-top: 1px solid #000;
            margin: 0.1in 0;
        }
        .status-badge {
            display: inline-block;
            padding: 1px 6px;
            border: 1px solid #000;
            font-weight: bold;
            font-size: 9pt;
            background: #fff;
        }
        .status-approved {
            background: #d4edda;
        }
        .status-pending {
            background: #fff3cd;
        }
        .status-rejected {
            background: #f8d7da;
        }
        .signature-section {
            margin-top: 0.25in;
            display: table;
            width: 100%;
        }
        .signature-box {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 0 0.08in;
            vertical-align: bottom;
        }
        .signature-line {
            border-top: 1px solid #000;
            padding-top: 0.02in;
            min-height: 0.7in;
        }
        .signature-label {
            font-size: 9pt;
            font-weight: bold;
            margin-top: 0.03in;
        }
        .footer {
            font-size: 8pt;
            text-align: center;
            margin-top: 0.15in;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- HEADER -->
        <div class="header-section">
            <div class="logo-header">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo">
                @endif
                <div class="header-text">
                    <div>Republic of the Philippines</div>
                    <div class="form-title">CS FORM NO. 6</div>
                    <div class="form-title">REVISED 2020</div>
                    <div class="form-subtitle">APPLICATION FOR LEAVE</div>
                    <div class="agency-name">Municipality of Pagsanjan</div>
                </div>
            </div>
        </div>

        <!-- FORM BODY -->
        <div class="form-body">
            <!-- Item 1: Name -->
            <div class="item">
                <div class="item-label">1. Name (Last, First, Middle Initial):</div>
                <div class="item-content">{{ $employee->last_name }}, {{ $employee->first_name }} {{ $employee->middle_name ? substr($employee->middle_name, 0, 1) . '.' : '' }}</div>
            </div>

            <!-- Item 2: Employee ID and Salary Grade -->
            <div class="two-col">
                <div class="two-col-item">
                    <div class="item-label">2. Employee ID Number:</div>
                    <div class="item-content short">{{ $employee->employee_id }}</div>
                </div>
                <div class="two-col-item">
                    <div class="item-label">Salary Grade:</div>
                    <div class="item-content short">{{ $employee->employmentDetail?->salary_grade ?? '' }}</div>
                </div>
            </div>

            <!-- Item 3: Department and Position -->
            <div class="two-col">
                <div class="two-col-item">
                    <div class="item-label">3. Office/Department:</div>
                    <div class="item-content short">{{ $department }}</div>
                </div>
                <div class="two-col-item">
                    <div class="item-label">Position/Designation:</div>
                    <div class="item-content short">{{ $designation }}</div>
                </div>
            </div>

            <!-- Item 4: Type of Leave -->
            <div class="item">
                <div class="item-label">4. Type of Leave to be Charged:</div>
                <div class="item-content short">{{ $leaveType->leave_name ?? 'N/A' }}</div>
            </div>

            <!-- Item 5: Dates and Days -->
            <div class="three-col">
                <div class="three-col-item">
                    <div class="item-label">5. a. Date From:</div>
                    <div class="item-content short">{{ $application->start_date->format('m/d/Y') }}</div>
                </div>
                <div class="three-col-item">
                    <div class="item-label">b. Date To:</div>
                    <div class="item-content short">{{ $application->end_date->format('m/d/Y') }}</div>
                </div>
                <div class="three-col-item">
                    <div class="item-label">c. Number of Days:</div>
                    <div class="item-content short">{{ number_format($application->number_of_days, 1) }}</div>
                </div>
            </div>

            <!-- Item 6: Reason -->
            <div class="item">
                <div class="item-label">6. Reason/Justification:</div>
                <div class="item-content long">{{ $application->reason }}</div>
            </div>

            <!-- Item 7: Status -->
            <div class="item">
                <div class="item-label">7. Application Status:</div>
                <div class="item-content short">
                    @if($application->status === 'approved')
                        <span class="status-badge status-approved">APPROVED</span>
                    @elseif($application->status === 'pending')
                        <span class="status-badge status-pending">PENDING</span>
                    @elseif($application->status === 'rejected')
                        <span class="status-badge status-rejected">DISAPPROVED</span>
                    @else
                        <span class="status-badge">{{ strtoupper($application->status) }}</span>
                    @endif
                </div>
            </div>

            <!-- Item 8: Application Number and Date -->
            <div class="two-col">
                <div class="two-col-item">
                    <div class="item-label">8. Application Number:</div>
                    <div class="item-content short">{{ $application->application_number }}</div>
                </div>
                <div class="two-col-item">
                    <div class="item-label">Date Submitted:</div>
                    <div class="item-content short">{{ $application->created_at->format('m/d/Y') }}</div>
                </div>
            </div>

            <!-- Approval Details -->
            @if($application->status === 'approved' && $application->approved_at)
                <div class="item">
                    <div class="item-label">9. Date Approved:</div>
                    <div class="item-content short">{{ $application->approved_at->format('m/d/Y') }}</div>
                </div>
            @elseif($application->status === 'rejected' && $application->approver_remarks)
                <div class="item">
                    <div class="item-label">9. Disapproval Remarks:</div>
                    <div class="item-content long">{{ $application->approver_remarks }}</div>
                </div>
            @endif

            <!-- Divider -->
            <div class="divider"></div>

            <!-- Certification -->
            <div style="font-size: 9pt; line-height: 1.4; margin-bottom: 0.15in;">
                <strong>CERTIFICATION:</strong> I hereby certify that all the information provided in this leave application is true and correct.
            </div>

            <!-- Signatures -->
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-label">Employee Signature</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-label">Authorized Approver</div>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                Document ID: {{ $application->application_number }} | Generated: {{ $generatedDate }}
            </div>
        </div>
    </div>
</body>
</html>
