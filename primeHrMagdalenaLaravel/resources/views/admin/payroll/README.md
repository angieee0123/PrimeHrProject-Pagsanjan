# Payroll Module Structure

## Overview
The payroll module has been reorganized into a clean, modular structure with tabs for better organization and maintainability.

## File Structure

```
resources/views/admin/payroll/
├── adminPayroll.blade.php          # Main payroll page with tabs
├── partials/
│   ├── payroll-register.blade.php  # Payroll Register tab content
│   └── generate-payroll.blade.php  # Generate Payroll tab content
└── modals/
    └── (future modals will go here)
```

## Tabs

### 1. Payroll Register Tab
- **File**: `partials/payroll-register.blade.php`
- **Features**:
  - View all payroll records
  - Filter by date range, employee name, department, status
  - View modes: Daily, By Employee, Monthly Summary
  - Export functionality
  - Payroll summary bar with totals
  - Employee name dropdown filter (NEW)

### 2. Generate Payroll Tab
- **File**: `partials/generate-payroll.blade.php`
- **Features**:
  - Configure payroll period (start date, end date, pay date)
  - Select payroll type (Regular, 13th Month, Bonus, Special)
  - Filter by department and employment status
  - Payroll options (deductions, loans, overtime, auto-approve)
  - Preview summary panel
  - Generate payroll button

## Routes

### GET Routes
- `GET /admin/payroll` - Main payroll page
  - Query params: `tab` (register|generate), `start_date`, `end_date`, `employee_name`, `department`, `status`, `view_mode`

### POST Routes
- `POST /admin/payroll/generate` - Generate payroll
  - Params: `start_date`, `end_date`, `pay_date`, `payroll_type`, `department`, `employment_status`

## Usage

### Switching Between Tabs
Tabs are accessible via URL parameter:
- Payroll Register: `/admin/payroll?tab=register` (default)
- Generate Payroll: `/admin/payroll?tab=generate`

### Adding New Tabs
1. Create a new partial file in `partials/` folder
2. Add tab link in `adminPayroll.blade.php`
3. Add `@include` directive in the tab content section

### Adding Modals
Place modal files in the `modals/` folder and include them in the appropriate partial file.

## Benefits of This Structure

1. **Separation of Concerns**: Each tab has its own file
2. **Maintainability**: Easier to locate and update specific features
3. **Scalability**: Easy to add new tabs or modals
4. **Readability**: Smaller, focused files instead of one large file
5. **Reusability**: Partials can be reused in other views if needed

## Future Enhancements

- Add modals for payroll actions (edit, approve, etc.)
- Create additional tabs (Payroll History, Reports, etc.)
- Add more filtering options
- Implement real-time preview calculations
