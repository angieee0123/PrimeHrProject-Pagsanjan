# Visual Guide: Absence & Abandoned Logic

## Quick Answer

**YES!** Your system automatically marks employees as ABSENT or ABANDONED when they don't have AM Out and PM In.

## Visual Flow Chart

```
┌─────────────────────────────────────────────────────────────┐
│           Employee Attendance Record Check                   │
└─────────────────────────────────────────────────────────────┘
                            ↓
        ┌───────────────────────────────────────┐
        │   Does employee have ANY time records? │
        └───────────────────────────────────────┘
                    ↓                    ↓
                   YES                  NO
                    ↓                    ↓
    ┌───────────────────────┐    ┌──────────────┐
    │ Check time patterns   │    │   ABSENT     │
    └───────────────────────┘    │ (No records) │
                ↓                 └──────────────┘
    ┌───────────────────────────────────────┐
    │ Pattern: AM In ONLY (no AM Out, no PM In)? │
    └───────────────────────────────────────┘
            ↓                        ↓
           YES                      NO
            ↓                        ↓
    ┌──────────────┐        ┌────────────────┐
    │  ABANDONED   │        │ Check if       │
    │ (Suspicious) │        │ INCOMPLETE     │
    └──────────────┘        └────────────────┘
            ↓
    ┌──────────────────────────────┐
    │ Accredited Hours = 0         │
    │ Undertime = 480 min (8 hrs)  │
    │ Deduct 1.0 day from VL/SL    │
    └──────────────────────────────┘
```

## Attendance Patterns & Results

### Pattern 1: ABANDONED 🔴
```
┌─────────────────────────────────────────┐
│ Time Records:                           │
│ ┌─────────┬─────────┬─────────┬────────┐│
│ │ AM In   │ AM Out  │ PM In   │ PM Out ││
│ ├─────────┼─────────┼─────────┼────────┤│
│ │ 08:00   │  NULL   │  NULL   │  NULL  ││
│ └─────────┴─────────┴─────────┴────────┘│
│                                          │
│ Status: ABANDONED                        │
│ Color: 🟠 Orange (#d97706)              │
│ Accredited: 0 minutes                    │
│ Undertime: 480 minutes                   │
│ Leave Deduction: 1.0 day                 │
└─────────────────────────────────────────┘
```

### Pattern 2: ABSENT 🔴
```
┌─────────────────────────────────────────┐
│ Time Records:                           │
│ ┌─────────┬─────────┬─────────┬────────┐│
│ │ AM In   │ AM Out  │ PM In   │ PM Out ││
│ ├─────────┼─────────┼─────────┼────────┤│
│ │  NULL   │  NULL   │  NULL   │  NULL  ││
│ └─────────┴─────────┴─────────┴────────┘│
│                                          │
│ Status: ABSENT                           │
│ Color: 🔴 Red (#8e1e18)                 │
│ Accredited: 0 minutes                    │
│ Leave Deduction: None (different logic) │
└─────────────────────────────────────────┘
```

### Pattern 3: INCOMPLETE 🟡
```
┌─────────────────────────────────────────┐
│ Time Records:                           │
│ ┌─────────┬─────────┬─────────┬────────┐│
│ │ AM In   │ AM Out  │ PM In   │ PM Out ││
│ ├─────────┼─────────┼─────────┼────────┤│
│ │ 08:00   │ 12:00   │  NULL   │  NULL  ││
│ └─────────┴─────────┴─────────┴────────┘│
│                                          │
│ Status: INCOMPLETE                       │
│ Color: 🟡 Yellow (#d9bb00)              │
│ Accredited: 240 minutes (AM only)       │
│ Undertime: 240 minutes (PM missing)     │
│ Leave Deduction: 0.5 day                 │
└─────────────────────────────────────────┘
```

### Pattern 4: PRESENT ✅
```
┌─────────────────────────────────────────┐
│ Time Records:                           │
│ ┌─────────┬─────────┬─────────┬────────┐│
│ │ AM In   │ AM Out  │ PM In   │ PM Out ││
│ ├─────────┼─────────┼─────────┼────────┤│
│ │ 08:00   │ 12:00   │ 13:00   │ 17:00  ││
│ └─────────┴─────────┴─────────┴────────┘│
│                                          │
│ Status: PRESENT                          │
│ Color: 🟢 Green (#15803d)               │
│ Accredited: 480 minutes (full day)      │
│ Leave Deduction: None                    │
└─────────────────────────────────────────┘
```

## Code Location Map

```
📁 primeHrMagdalenaLaravel/
│
├── 📁 app/
│   └── 📁 Http/
│       └── 📁 Controllers/
│           └── 📄 AttendanceController.php ⭐ MAIN LOGIC
│               │
│               ├── Lines 708-747: Abandoned Detection (DTR View)
│               │   └── if ($attendance->am_in && !$attendance->am_out && !$attendance->pm_in)
│               │
│               └── Lines 1035-1055: Abandoned Detection (Computation)
│                   └── if ($amIn && !$amOut && !$pmIn)
│
└── 📁 resources/
    └── 📁 views/
        │
        ├── 📁 permanent/
        │   └── 📁 attendance/
        │       ├── 📄 permanentAttendance.blade.php
        │       │   ├── Line 442: isAbsent check
        │       │   └── Lines 676-680: Abandoned display
        │       │
        │       └── 📁 modals/
        │           └── 📄 detailedDtrModal.blade.php
        │               └── Lines 291-296: Abandoned counter
        │
        └── 📁 admin/
            └── 📁 attendance/
                └── 📁 partials/
                    └── 📄 detailed-time-record-tab.blade.php
                        └── Lines 74-78: Absence/incomplete styling
```

## Real-World Example: Jeremy's Case

### Scenario
Jeremy clocked in at 8:00 AM but left and never came back.

```
┌──────────────────────────────────────────────────────────┐
│ Date: May 18, 2026                                       │
│                                                           │
│ Original Record:                                         │
│ ┌─────────┬─────────┬─────────┬────────┐               │
│ │ AM In   │ AM Out  │ PM In   │ PM Out │               │
│ ├─────────┼─────────┼─────────┼────────┤               │
│ │ 08:00   │  NULL   │  NULL   │  NULL  │ ← ABANDONED  │
│ └─────────┴─────────┴─────────┴────────┘               │
│                                                           │
│ System Action:                                           │
│ ✓ Detected: am_in && !am_out && !pm_in                  │
│ ✓ Status: ABANDONED                                     │
│ ✓ Accredited Hours: 0 minutes                           │
│ ✓ Undertime: 480 minutes                                │
│ ✓ Leave Deduction: 1.0 day from VL                      │
│                                                           │
│ Leave Transaction Created:                               │
│ - Type: DEBIT                                            │
│ - Amount: -1.000000 days                                 │
│ - Reference: manual_adjustment                           │
│ - Remarks: "Undertime deduction: 480 minutes"           │
└──────────────────────────────────────────────────────────┘
```

### After Admin Correction

```
┌──────────────────────────────────────────────────────────┐
│ Date: May 18, 2026                                       │
│                                                           │
│ Corrected Record:                                        │
│ ┌─────────┬─────────┬─────────┬────────┐               │
│ │ AM In   │ AM Out  │ PM In   │ PM Out │               │
│ ├─────────┼─────────┼─────────┼────────┤               │
│ │ 08:00   │ 12:00   │ 13:06   │ 17:09  │ ← PRESENT    │
│ └─────────┴─────────┴─────────┴────────┘               │
│                                                           │
│ System Action:                                           │
│ ✓ Detected: Attendance was corrected                    │
│ ✓ Status: PRESENT (6 min late from PM In)               │
│ ✓ Accredited Hours: 474 minutes                         │
│ ✓ Reversal: +1.0 day credited back to VL                │
│ ✓ New Deduction: -0.012500 days (6 min late)            │
│                                                           │
│ Leave Transactions Created:                              │
│ 1. CREDIT: +1.000000 days (reversal)                    │
│    - Reference: attendance_correction_reversal           │
│    - Remarks: "Reversal of previous undertime..."       │
│                                                           │
│ 2. DEBIT: -0.012500 days (new late deduction)           │
│    - Reference: manual_adjustment                        │
│    - Remarks: "Late deduction: 6 minutes"               │
│                                                           │
│ Net Result: +0.987500 days restored to VL ✅            │
└──────────────────────────────────────────────────────────┘
```

## Key Takeaways

### ✅ What the System DOES
1. **Automatically detects** when employee has AM In but no AM Out and no PM In
2. **Marks as ABANDONED** with orange badge
3. **Sets accredited hours to 0**
4. **Deducts 1.0 day** from leave balance (VL first, then SL)
5. **Records transaction** in leave history
6. **Allows correction** by admin
7. **Automatically reverses** deduction when corrected

### ❌ What the System DOES NOT DO
1. Does NOT ignore abandoned records
2. Does NOT allow employees to self-correct
3. Does NOT send automatic notifications (yet)
4. Does NOT apply to weekends (Saturday/Sunday)
5. Does NOT affect employees on approved leave

## Files You Need to Check

### Priority 1: Backend Logic ⭐
```
c:\Users\eyouth\Desktop\PrimeHrProjectMagdalena\primeHrMagdalenaLaravel\
app\Http\Controllers\AttendanceController.php
```
**Lines to check:** 708-747, 1035-1055

### Priority 2: Frontend Display
```
c:\Users\eyouth\Desktop\PrimeHrProjectMagdalena\primeHrMagdalenaLaravel\
resources\views\permanent\attendance\permanentAttendance.blade.php
```
**Lines to check:** 442-443, 676-680

### Priority 3: Modal Display
```
c:\Users\eyouth\Desktop\PrimeHrProjectMagdalena\primeHrMagdalenaLaravel\
resources\views\permanent\attendance\modals\detailedDtrModal.blade.php
```
**Lines to check:** 291-296

## Quick Search Commands

### In VS Code
Press `Ctrl + Shift + F` and search for:
- `isAbandoned`
- `am_in && !am_out && !pm_in`
- `ABANDONED`

### In Command Line
```bash
cd c:\Users\eyouth\Desktop\PrimeHrProjectMagdalena\primeHrMagdalenaLaravel
grep -rn "isAbandoned" app/
grep -rn "am_in.*!am_out.*!pm_in" app/
```

---

**Created:** May 22, 2026  
**Purpose:** Visual guide for absence/abandoned logic  
**Status:** ✅ System is working as designed
