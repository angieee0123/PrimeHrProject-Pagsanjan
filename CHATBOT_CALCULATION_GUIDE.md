# Chatbot Calculation & Query Enhancement Guide

## Overview
The chatbot has been enhanced to handle complex calculations, salary queries, and Filipino language questions about monetary amounts and computations.

## New Capabilities

### 1. Flexible Name Search
The chatbot now handles partial names, middle initials, and name variations:

**Works with:**
- Full name: "Jeremy Reyes Pogi"
- First + Last: "Jeremy Pogi"
- With middle initial: "Jeremy R. Pogi"
- First name only: "Jeremy"
- Last name only: "Pogi"

**Examples:**
- "Magkano ang monthly salary ni Jeremy R. Pogi?" ✅
- "What is Jeremy's salary?" ✅
- "Show me Pogi's monthly rate" ✅
- "Salary of Jeremy" ✅

### 2. Salary Queries
The chatbot can now answer questions about employee salaries:

**English Examples:**
- "What is Jeremy's monthly salary?"
- "Show me the salary of employee Jeremy"
- "How much does Jeremy earn per month?"

**Filipino Examples:**
- "Magkano ang monthly salary sahod ni Jeremy?"
- "Ano ang sweldo ni Jeremy?"
- "Ilang piso ang kita ni Jeremy buwan-buwan?"

### 2. Deduction Calculations
The chatbot can explain deductions with calculations:

**English Examples:**
- "Why was Jeremy deducted?"
- "Show me Jeremy's late deductions"
- "Calculate Jeremy's undertime deduction"

**Filipino Examples:**
- "Bakit may kaltas si Jeremy?"
- "Magkano ang kaltas ni Jeremy dahil sa late?"
- "Ipakita ang undertime deduction ni Jeremy"

### 3. Overtime Pay
**English Examples:**
- "How much overtime pay did Jeremy receive?"
- "Calculate Jeremy's OT pay"

**Filipino Examples:**
- "Magkano ang overtime pay ni Jeremy?"
- "Ilang piso ang OT ni Jeremy?"

### 4. Daily/Hourly Rates
**English Examples:**
- "What is Jeremy's daily rate?"
- "Show me the hourly rate of Jeremy"

**Filipino Examples:**
- "Magkano ang daily rate ni Jeremy?"
- "Ano ang hourly rate ni Jeremy?"

## How It Works

### Name Extraction & Cleaning
The chatbot automatically:
1. Detects name patterns ("ni [Name]", "of [Name]", "[Name]'s")
2. Extracts the employee name
3. Removes middle initials (R., Jr., etc.)
4. Builds flexible SQL conditions

**Example:**
```
Input: "Magkano ang monthly salary ni Jeremy R. Pogi?"
Extracted: "Jeremy Pogi"
SQL WHERE: (first_name LIKE '%Jeremy%' OR last_name LIKE '%Pogi%' OR CONCAT(first_name, ' ', last_name) LIKE '%Jeremy Pogi%')
```

### Database Relationships
The chatbot now understands these key relationships:

```
employees
  ├─> employment_details
  │     └─> designations (contains monthly_rate)
  ├─> daily_salary_computations (contains daily calculations)
  └─> accredited_hours_log (contains attendance minutes)
```

### Calculation Logic

#### Monthly Salary
```sql
SELECT d.monthly_rate 
FROM employees e
JOIN employment_details ed ON e.id = ed.employee_id
JOIN designations d ON ed.designation_id = d.id
WHERE e.first_name LIKE '%Jeremy%'
```

#### Daily Rate
```
daily_rate = monthly_rate / 22 working days
```

#### Hourly Rate
```
hourly_rate = daily_rate / 8 hours
```

#### Late Deduction
```
late_deduction = (late_minutes / 60) × hourly_rate
```

#### Undertime Deduction
```
undertime_deduction = (undertime_minutes / 60) × hourly_rate
```

#### Overtime Pay
```
ot_pay = (ot_minutes / 60) × hourly_rate × 1.25
```

#### Daily Gross Pay
```
daily_gross_pay = daily_basic_pay + ot_pay - late_deduction - undertime_deduction
```

## Enhanced Features

### 1. Filipino Language Support
The chatbot now recognizes these Filipino keywords:
- **magkano** = how much
- **sahod/sweldo** = salary
- **kaltas** = deduction
- **bayad** = payment
- **bakit** = why
- **dahilan** = reason
- **halaga** = amount

### 2. Automatic Translation
When you ask in Filipino, the chatbot:
1. Detects Filipino keywords
2. Translates to English internally
3. Generates SQL query
4. Returns answer in natural language

### 3. Calculation Explanations
The chatbot provides:
- The final amount
- How it was calculated
- The breakdown (minutes, rates, etc.)
- Context from attendance records

## Example Conversations

### Example 1: Monthly Salary
**User:** "Magkano ang monthly salary sahod ni Jeremy?"

**Chatbot:** "Jeremy's monthly salary is PHP 121,264.00. This is his base monthly rate as Municipal Mayor according to the designations table."

### Example 2: Deduction with Explanation
**User:** "Bakit may kaltas si Jeremy?"

**Chatbot:** "Jeremy has a late deduction of PHP 0.00 and an undertime deduction of PHP 0.00 for May 1, 2026. Based on his attendance record, he had 0 late minutes and 0 undertime minutes. His hourly rate is PHP 689.00, so the calculation is: (0 minutes / 60) × PHP 689 = PHP 0.00."

### Example 3: Overtime Pay
**User:** "How much overtime pay did Jeremy receive?"

**Chatbot:** "Jeremy received PHP 0.00 in overtime pay for May 1, 2026. He worked 0 overtime minutes. The OT rate is calculated as: (0 minutes / 60) × PHP 689 × 1.25 = PHP 0.00."

## Testing the Enhanced Chatbot

### Test Questions

1. **Salary Queries:**
   - "Magkano ang monthly salary sahod ni Jeremy?"
   - "What is the salary of employee Jeremy?"
   - "Show me Jeremy's monthly rate"

2. **Deduction Queries:**
   - "Bakit may kaltas si Jeremy?"
   - "Why was Jeremy deducted?"
   - "Show me all deductions for Jeremy"

3. **Calculation Queries:**
   - "Calculate Jeremy's daily rate"
   - "What is Jeremy's hourly rate?"
   - "Show me Jeremy's gross pay"

4. **Aggregate Queries:**
   - "What is the total salary of all employees?"
   - "Show me the average daily rate"
   - "Count how many employees have deductions"

## Troubleshooting

### Issue: "Sorry, I encountered an error"
**Solution:** Check if:
1. Database connection is working
2. Employee name exists in database
3. Employee has employment_details and designation records

### Issue: Wrong calculation
**Solution:** Verify:
1. designations.monthly_rate is set correctly
2. daily_salary_computations has records
3. accredited_hours_log has attendance data

### Issue: Filipino translation not working
**Solution:** Ensure:
1. Groq API key is valid
2. Question contains Filipino keywords
3. Translation model is accessible

## Configuration

### Database Config
```python
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': 'admin',
    'database': 'primehrismagdalena',
    'auth_plugin': 'mysql_native_password'
}
```

### Groq API
```python
groq_client = Groq(api_key="your_api_key_here")
```

## Future Enhancements

1. **Period-based calculations** (monthly, quarterly, yearly)
2. **Comparison queries** (compare salaries between employees)
3. **Trend analysis** (salary changes over time)
4. **Bulk calculations** (total payroll for department)
5. **Tax calculations** (withholding tax, SSS, PhilHealth, Pag-IBIG)

## Support

For issues or questions:
1. Check the database schema
2. Review the SQL query generated
3. Verify employee data exists
4. Check Groq API logs
5. Review chat_history table for debugging

## Notes

- All monetary values are in Philippine Peso (PHP)
- Calculations follow Philippine labor law standards
- Monthly rate is divided by 22 working days for daily rate
- Daily rate is divided by 8 hours for hourly rate
- OT rate is 1.25× the hourly rate
- Deductions are based on actual attendance minutes
