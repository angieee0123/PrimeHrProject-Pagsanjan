# LATE DEDUCTION FLOW DIAGRAM

## Visual Flow Chart

```
┌─────────────────────────────────────────────────────────────────┐
│                    EMPLOYEE CLOCKS IN LATE                      │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
                    ┌────────────────┐
                    │ Calculate Late │
                    │    Minutes     │
                    └────────┬───────┘
                             │
                             ▼
                    ┌────────────────┐
                    │  Apply 5-min   │
                    │  Grace Period  │
                    └────────┬───────┘
                             │
                ┌────────────┴────────────┐
                │                         │
                ▼                         ▼
        ┌──────────────┐          ┌──────────────┐
        │ Within Grace │          │ Beyond Grace │
        │   (≤ 5 min)  │          │   (> 5 min)  │
        └──────┬───────┘          └──────┬───────┘
               │                         │
               ▼                         ▼
        ┌──────────────┐          ┌──────────────┐
        │   NO LATE    │          │  LATE TIME   │
        │  Full Hours  │          │   Recorded   │
        └──────────────┘          └──────┬───────┘
                                         │
                                         ▼
                                  ┌──────────────┐
                                  │  Check VL    │
                                  │   Balance    │
                                  └──────┬───────┘
                                         │
                        ┌────────────────┼────────────────┐
                        │                │                │
                        ▼                ▼                ▼
                ┌──────────────┐  ┌──────────────┐  ┌──────────────┐
                │  VL Balance  │  │  VL Balance  │  │   No VL      │
                │   Sufficient │  │   Partial    │  │   Balance    │
                └──────┬───────┘  └──────┬───────┘  └──────┬───────┘
                       │                 │                 │
                       ▼                 ▼                 ▼
                ┌──────────────┐  ┌──────────────┐  ┌──────────────┐
                │ Deduct from  │  │ Deduct from  │  │  Check SL    │
                │  VL (Full)   │  │  VL (Part)   │  │   Balance    │
                └──────┬───────┘  └──────┬───────┘  └──────┬───────┘
                       │                 │                 │
                       ▼                 │         ┌───────┴───────┐
                ┌──────────────┐         │         │               │
                │ Credit Full  │         │         ▼               ▼
                │   8 Hours    │         │  ┌──────────────┐  ┌──────────────┐
                └──────────────┘         │  │  SL Balance  │  │   No SL      │
                                        │  │   Available  │  │   Balance    │
                                        │  └──────┬───────┘  └──────┬───────┘
                                        │         │                 │
                                        │         ▼                 │
                                        │  ┌──────────────┐         │
                                        │  │ Deduct from  │         │
                                        │  │  SL (Rest)   │         │
                                        │  └──────┬───────┘         │
                                        │         │                 │
                                        └─────────┼─────────────────┘
                                                  │
                                                  ▼
                                          ┌──────────────┐
                                          │ Still Late?  │
                                          └──────┬───────┘
                                                 │
                                    ┌────────────┴────────────┐
                                    │                         │
                                    ▼                         ▼
                            ┌──────────────┐          ┌──────────────┐
                            │ Fully Covered│          │   Remaining  │
                            │   by Leave   │          │  Late Time   │
                            └──────┬───────┘          └──────┬───────┘
                                   │                         │
                                   ▼                         ▼
                            ┌──────────────┐          ┌──────────────┐
                            │ Credit Full  │          │  Apply LWOP  │
                            │   8 Hours    │          │  (No Pay)    │
                            └──────────────┘          └──────┬───────┘
                                                             │
                                                             ▼
                                                      ┌──────────────┐
                                                      │ Restore Time │
                                                      │ Covered by   │
                                                      │    Leave     │
                                                      └──────┬───────┘
                                                             │
                                                             ▼
                                                      ┌──────────────┐
                                                      │ Deduct LWOP  │
                                                      │ from Salary  │
                                                      └──────────────┘
```

## Detailed Examples

### Example 1: Late 30 minutes, VL Balance = 2 days
```
Late Time: 30 minutes
VL Balance: 2 days (960 minutes)

Process:
1. 30 minutes < 960 minutes (VL sufficient)
2. Deduct 30 minutes from VL
3. VL Balance after: 1.9375 days (930 minutes)
4. Accredited Hours: 8 hours (480 minutes) ✓
5. LWOP: 0 minutes
```

### Example 2: Late 180 minutes, VL = 60 min, SL = 60 min
```
Late Time: 180 minutes (3 hours)
VL Balance: 60 minutes (0.125 days)
SL Balance: 60 minutes (0.125 days)

Process:
1. Deduct 60 minutes from VL → VL = 0
2. Remaining late: 120 minutes
3. Deduct 60 minutes from SL → SL = 0
4. Remaining late: 60 minutes
5. Apply LWOP: 60 minutes
6. Restore time covered by leave: 120 minutes
7. Accredited Hours: 7 hours (420 minutes)
   - Original: 300 minutes (5 hours - 3 hours late)
   - + Leave covered: 120 minutes
   - = 420 minutes (7 hours)
8. LWOP: 60 minutes (deducted from salary)
```

### Example 3: Late 30 minutes, No VL/SL
```
Late Time: 30 minutes
VL Balance: 0 minutes
SL Balance: 0 minutes

Process:
1. No VL available
2. No SL available
3. Apply LWOP: 30 minutes
4. Accredited Hours: 7.5 hours (450 minutes)
5. LWOP: 30 minutes (deducted from salary)
```

### Example 4: Within Grace Period
```
Scheduled AM In: 8:00
Actual AM In: 8:03
Grace Period: 5 minutes

Process:
1. Late time: 3 minutes
2. 3 minutes ≤ 5 minutes (within grace)
3. No late recorded
4. Accredited Hours: 8 hours (480 minutes) ✓
5. No deduction from leave
```

## Conversion Table

| Minutes | Days    | Hours |
|---------|---------|-------|
| 480     | 1.0     | 8.0   |
| 240     | 0.5     | 4.0   |
| 120     | 0.25    | 2.0   |
| 60      | 0.125   | 1.0   |
| 30      | 0.0625  | 0.5   |
| 15      | 0.03125 | 0.25  |
| 5       | 0.01042 | 0.083 |

## Key Points

1. **Grace Period**: 5 minutes for AM In and PM In
2. **Deduction Priority**: VL → SL → LWOP
3. **Conversion**: 480 minutes = 1 work day
4. **Full Coverage**: If late is fully covered by leave, employee gets full 8 hours accredited
5. **Partial Coverage**: Restore time covered by leave, apply LWOP for remaining
6. **No Coverage**: All late time becomes LWOP

## Database Updates

When late deduction occurs:

1. **accredited_hours_logs** table:
   - `late_minutes` - Total late minutes
   - `late_deducted_from_leave` - Boolean flag
   - `late_deduction_leave_type` - Which leave was used (VL, SL, VL+SL)
   - `lwop_minutes` - Minutes not covered by leave
   - `total_accredited_minutes` - Final accredited hours

2. **leave_balances** table:
   - `used_credits` - Increased by deducted amount
   - `available_credits` - Decreased by deducted amount

3. **leave_transactions** table:
   - New record with type 'debit'
   - Amount: negative (deduction)
   - Remarks: "Late deduction: X minutes"

4. **attendances** table:
   - `accredited_hours` - Updated with final accredited minutes

## Code Reference

Implementation in Laravel:
- `app/Services/LateDeductionService.php` - Main logic
- `app/Http/Controllers/AttendanceController.php` - Attendance processing
- `app/Models/AccreditedHoursLog.php` - Log model
- `app/Models/LeaveBalance.php` - Balance model
- `app/Models/LeaveTransaction.php` - Transaction model

## Chatbot Integration

The chatbot now understands this entire flow and can:
- Explain the process in English or Tagalog
- Answer specific questions about each step
- Query the database for actual employee data
- Provide examples and calculations

**Test it with:**
- "Sa ating system, nababawasan ba ang vacation leave kapag na-late?"
- "How is late deduction calculated?"
- "What happens if I don't have enough leave credits?"
- "Show my leave balance"
