# CHATBOT UPDATES - PRIME HRIS SYSTEM KNOWLEDGE

## Overview
Updated the chatbot to include comprehensive knowledge about the Prime HRIS Magdalena system's HR policies, attendance rules, and leave management.

## Key Updates

### 1. System Knowledge Base Added
The chatbot now understands:

#### Attendance & Leave Policies
- **Late Deduction**: YES, vacation leave (VL) is automatically deducted when late
- **Deduction Priority**: VL first, then SL (Sick Leave)
- **Conversion Rate**: 480 minutes = 1 work day (8 hours)
- **Grace Period**: 5 minutes for both AM In and PM In
- **LWOP**: Applied when late time exceeds available leave credits

#### Working Hours
- **Standard Schedule**: AM 8:00-12:00, PM 13:00-17:00 (8 hours total)
- **Weekends**: Saturday and Sunday are non-working days
- **Overtime**: Tracked separately after PM Out

#### Attendance Status
- **Present**: All 4 time logs (AM In/Out, PM In/Out) recorded
- **Absent**: No time logs at all on working day
- **Abandoned**: Clocked in but never clocked out (single period only)
- **Incomplete**: Has some attendance but missing logs
- **On Leave**: Approved leave application

#### Leave Types
- **VL (Vacation Leave)**: Accrued, cumulative, monetizable
- **SL (Sick Leave)**: Accrued, cumulative, monetizable
- **SPL (Special Privilege Leave)**: 3 days annually
- **ML (Maternity Leave)**: 105 days
- **PL (Paternity Leave)**: 7 days
- **VAWC Leave**: 10 days
- **Solo Parent Leave**: 7 days

#### Deductions
- GSIS (Government Service Insurance System)
- PhilHealth (Philippine Health Insurance)
- Pag-IBIG (Home Development Mutual Fund)
- Loans: GSIS Salary, GSIS Policy, GSIS Emergency, Pag-IBIG MPL, Pag-IBIG Calamity
- Tax withholding

### 2. Direct Policy Answers
Added `get_policy_answer()` function for instant responses to common questions:

**Supported Questions:**
- "Sa ating system, nababawasan ba ang vacation leave kapag na-late?"
- "How is late deduction calculated?"
- "What is the grace period?"
- "What are the working hours?"
- "What is LWOP?"

**Benefits:**
- Faster response (no API call needed)
- Consistent answers
- Supports both English and Tagalog
- Fallback to AI for complex questions

### 3. Enhanced SQL Query Generation
The chatbot now includes system knowledge when generating SQL queries, allowing it to:
- Understand HR terminology
- Query relevant tables (attendances, leave_balances, leave_applications, etc.)
- Apply business rules in queries

### 4. Improved Error Handling
- Try-catch blocks for API calls
- Fallback responses if API fails
- Better session management
- Detailed error logging

### 5. Bilingual Support
- Detects user language (English/Tagalog)
- Responds in matching language
- Added Tagalog greetings: "kumusta", "kamusta"

## Example Questions & Answers

### Question 1: "Sa ating system, nababawasan ba ang vacation leave kapag na-late?"
**Answer:** 
"Oo, nababawasan ang vacation leave (VL) kapag na-late. Ang sistema ay awtomatikong nagbabawas mula sa VL, pagkatapos sa SL (Sick Leave). 480 minuto = 1 araw ng trabaho. May 5 minuto grace period para sa AM at PM."

### Question 2: "How is late deduction calculated?"
**Answer:**
"Late deduction is calculated automatically: Late minutes are deducted from VL (Vacation Leave) first, then SL (Sick Leave). The system uses 480 minutes = 1 work day. There's a 5-minute grace period for both AM In and PM In. If late is fully covered by leave, you get full 8 hours accredited. If not enough leave credits, remaining becomes LWOP (Leave Without Pay)."

### Question 3: "What is LWOP?"
**Answer:**
"LWOP (Leave Without Pay) is applied when your late time exceeds your available leave credits. For example, if you're late 180 minutes but only have 60 minutes worth of VL/SL, the remaining 120 minutes becomes LWOP and will be deducted from your salary."

### Question 4: "Show leave balances for all employees"
**Answer:**
(Generates SQL query and displays results from database)

## Technical Implementation

### File Modified
`GOVERNMENT CHATBOT/4. web application/chatbot_to_database.py`

### Key Functions Added/Modified

1. **get_policy_answer(question)**
   - Returns direct answers for common policy questions
   - Supports English and Tagalog
   - Returns answer + follow-up questions

2. **generate_sql_query(user_question, schema)**
   - Enhanced with system knowledge base
   - Better understanding of HR terminology
   - Improved query generation

3. **generate_natural_response(user_question, sql, results)**
   - Includes system knowledge in responses
   - Explains HR policies when relevant
   - Bilingual support

4. **chat() route**
   - Added policy question detection
   - Direct answer lookup before API call
   - Better error handling with fallbacks
   - Session management improvements

## Database Tables Referenced

The chatbot can now query these HRIS tables:
- `employees` - Employee master data
- `attendances` - Daily time records (AM/PM/OT In/Out)
- `accredited_hours_logs` - Computed accredited hours with late/undertime
- `leave_types_config` - Leave type definitions
- `leave_balances` - Employee leave credits by year
- `leave_applications` - Leave requests (pending/approved/rejected)
- `leave_transactions` - Leave credit/debit history
- `schedules` - Employee work schedules
- `deduction_types` - Deduction categories
- `employee_deductions` - Employee-specific deductions
- `loan_types` - Loan type definitions

## Testing

### Test File Created
`GOVERNMENT CHATBOT/4. web application/test_policy_response.py`

Run test:
```bash
cd "GOVERNMENT CHATBOT/4. web application"
python test_policy_response.py
```

### Manual Testing
1. Start the chatbot server:
   ```bash
   python chatbot_to_database.py
   ```

2. Test questions:
   - "Sa ating system, nababawasan ba ang vacation leave kapag na-late?"
   - "How is late deduction calculated?"
   - "What is the grace period?"
   - "Show leave balances"
   - "How many employees are there?"

## Benefits

1. **Accurate HR Information**: Chatbot provides correct information based on actual system implementation
2. **Faster Responses**: Direct answers for common questions without API calls
3. **Bilingual Support**: Serves both English and Tagalog speakers
4. **Database Integration**: Can query actual employee data when needed
5. **Error Resilience**: Multiple fallback mechanisms ensure users always get an answer
6. **Consistent Policies**: All answers align with LateDeductionService and AttendanceController logic

## Future Enhancements

1. Add more policy questions (overtime calculation, holiday pay, etc.)
2. Integrate with employee authentication for personalized responses
3. Add voice input/output support
4. Create admin dashboard for chatbot analytics
5. Add support for more languages (Bisaya, Ilocano, etc.)
6. Implement context-aware conversations (remember previous questions)

## Maintenance Notes

When updating HR policies in the Laravel system:
1. Update the system knowledge in `generate_sql_query()` function
2. Update the system knowledge in `generate_natural_response()` function
3. Update direct answers in `get_policy_answer()` function
4. Test with common questions to ensure consistency
5. Update this documentation

## Support

For issues or questions:
- Check error logs in console output
- Verify database connection settings
- Ensure Groq API key is valid
- Test with `test_policy_response.py` script
