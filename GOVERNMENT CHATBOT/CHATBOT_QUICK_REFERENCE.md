# CHATBOT QUICK REFERENCE - HR POLICY QUESTIONS

## Common Questions the Chatbot Can Answer

### 🕐 Late & Attendance

**Q: Sa ating system, nababawasan ba ang vacation leave kapag na-late?**
A: Oo, nababawasan ang VL. Automatic deduction from VL first, then SL. 480 minutes = 1 day.

**Q: How is late deduction calculated?**
A: Late minutes → VL first → SL second → LWOP if insufficient. 480 min = 1 day. 5-min grace period.

**Q: What is the grace period?**
A: 5 minutes for AM In (8:00) and PM In (13:00).

**Q: Paano kung kulang ang leave credits?**
A: Remaining late time becomes LWOP (Leave Without Pay) and deducted from salary.

### 📅 Working Hours & Schedule

**Q: What are the working hours?**
A: AM 8:00-12:00, PM 13:00-17:00 (8 hours total). Weekends are non-working days.

**Q: Ano ang oras ng trabaho?**
A: Umaga 8:00-12:00, Hapon 13:00-17:00 (8 oras). Sabado at Linggo ay walang pasok.

**Q: What days are working days?**
A: Monday to Friday. Weekends (Saturday/Sunday) are non-working days.

### 🏖️ Leave Types

**Q: What leave types are available?**
A: VL, SL (accrued, cumulative, monetizable), SPL (3 days), ML (105 days), PL (7 days), VAWC (10 days), Solo Parent (7 days), Study Leave, Rehabilitation Leave.

**Q: How many days of vacation leave do I get?**
A: VL is accrued based on service. Check your leave balance in the system.

**Q: What is SPL?**
A: Special Privilege Leave - 3 days annually for government employees.

### 💰 LWOP & Deductions

**Q: What is LWOP?**
A: Leave Without Pay - applied when late time exceeds available leave credits. Deducted from salary.

**Q: Ano ang LWOP?**
A: Leave Without Pay - ginagamit kapag kulang ang leave credits para sa late. Babawasan sa sahod.

**Q: What deductions are there?**
A: GSIS, PhilHealth, Pag-IBIG, loans (GSIS Salary/Policy/Emergency, Pag-IBIG MPL/Calamity), tax withholding.

### ⏱️ Accredited Hours

**Q: How is accredited hours calculated?**
A: Actual work time minus late/undertime, with 5-minute grace period applied. Full 8 hours if late is covered by leave.

**Q: Paano kinakalkula ang accredited hours?**
A: Actual na oras ng trabaho minus late/undertime, may 5 minuto grace period. Buong 8 oras kung saklaw ng leave ang late.

### 📊 Database Queries

**Q: Show leave balances for all employees**
A: (Queries database and displays results)

**Q: How many employees are there?**
A: (Queries database and displays count)

**Q: Show attendance records for this month**
A: (Queries database and displays records)

**Q: List all employees with VL balance less than 5 days**
A: (Queries database and displays filtered results)

## System Rules Summary

### Late Deduction Flow
```
Employee is late
    ↓
Check VL balance
    ↓
Deduct from VL (if available)
    ↓
Still late? Check SL balance
    ↓
Deduct from SL (if available)
    ↓
Still late? Apply LWOP
    ↓
Update accredited hours
```

### Conversion Rates
- **480 minutes = 1 work day**
- **240 minutes = 0.5 work day (half day)**
- **60 minutes = 0.125 work day**

### Grace Period Application
- **AM In**: 8:00 + 5 min = 8:05 (no late if clock in by 8:05)
- **PM In**: 13:00 + 5 min = 13:05 (no late if clock in by 13:05)

### Accredited Hours Examples

**Example 1: On Time**
- AM In: 8:00, AM Out: 12:00
- PM In: 13:00, PM Out: 17:00
- Accredited: 8 hours (480 minutes)

**Example 2: Late 30 minutes, has VL**
- AM In: 8:30 (30 min late), AM Out: 12:00
- PM In: 13:00, PM Out: 17:00
- Late: 30 minutes deducted from VL
- Accredited: 8 hours (480 minutes) - late covered by VL

**Example 3: Late 30 minutes, no VL/SL**
- AM In: 8:30 (30 min late), AM Out: 12:00
- PM In: 13:00, PM Out: 17:00
- Late: 30 minutes = LWOP
- Accredited: 7.5 hours (450 minutes)

**Example 4: Abandoned (AM In only)**
- AM In: 8:00, no AM Out, no PM In/Out
- Status: ABANDONED
- Accredited: 0 hours
- Undertime: 8 hours (480 minutes)

## Chatbot Commands

### Greetings (English)
- "Hello"
- "Hi"
- "Good morning"
- "Good afternoon"
- "Good evening"

### Greetings (Tagalog)
- "Kumusta"
- "Kamusta"

### Policy Questions
- Use natural language
- Mix English and Tagalog
- Ask about late, leave, deductions, schedule, etc.

### Database Queries
- "Show [data]"
- "List all [records]"
- "How many [items]"
- "Find [criteria]"

## Tips for Best Results

1. **Be specific**: "Show VL balance for employee PGS-0041" vs "Show balance"
2. **Use keywords**: late, leave, deduction, attendance, schedule
3. **Ask follow-up questions**: Chatbot remembers context
4. **Mix languages**: "Ano ang late deduction sa ating system?"
5. **Request clarification**: If answer is unclear, ask for more details

## Error Messages

**"I'm not sure how to answer that..."**
- Try rephrasing your question
- Use keywords like: employee, attendance, leave, deduction
- Ask about specific data or policies

**"Database error..."**
- Check database connection
- Verify table names and columns exist
- Contact system administrator

**"Sorry, an error occurred..."**
- Try again
- Check internet connection (for AI responses)
- Report persistent errors to admin

## Contact Support

For technical issues:
- Check console logs for detailed errors
- Verify database connection settings
- Ensure Groq API key is valid
- Test with `test_policy_response.py`
