# Attendance Status Logic - Standardized Reference

## Status Definitions

### 1. **Present** (Green Badge)
**Condition:** All 4 time logs are complete
- ✅ AM In
- ✅ AM Out
- ✅ PM In
- ✅ PM Out

**Example:**
```
AM In: 08:00  |  AM Out: 12:00  |  PM In: 13:00  |  PM Out: 17:00
Status: Present
```

---

### 2. **Absent** (Red Badge)
**Condition:** No clock in at all (no AM In AND no PM In)
- ❌ AM In
- ❌ PM In

**Example:**
```
AM In: --:--  |  AM Out: --:--  |  PM In: --:--  |  PM Out: --:--
Status: Absent
```

---

### 3. **Abandoned** (Orange Badge)
**Condition:** Clocked in but never clocked out (single period only)
- Scenario A: Has AM In but no AM Out, and no PM attendance
- Scenario B: Has PM In but no PM Out, and no AM attendance

**Examples:**

**Scenario A - Abandoned in Morning:**
```
AM In: 08:00  |  AM Out: --:--  |  PM In: --:--  |  PM Out: --:--
Status: Abandoned
```

**Scenario B - Abandoned in Afternoon:**
```
AM In: --:--  |  AM Out: --:--  |  PM In: 13:00  |  PM Out: --:--
Status: Abandoned
```

**Why "Abandoned"?**
- Employee clocked in but left without clocking out
- Suggests the employee abandoned their post
- Only applies when there's a single period with In but no Out

---

### 4. **Incomplete** (Yellow Badge)
**Condition:** Has some attendance logs but not all 4, and doesn't fit "Abandoned" criteria
- Has at least one time log
- Missing some logs
- Not a complete Present status
- Not an Abandoned status (has attendance in multiple periods or has Out without In)

**Examples:**

**Example 1 - Missing AM Out:**
```
AM In: 08:00  |  AM Out: --:--  |  PM In: 13:00  |  PM Out: 17:00
Status: Incomplete (has PM attendance, so not abandoned)
```

**Example 2 - Missing PM In:**
```
AM In: 08:00  |  AM Out: 12:00  |  PM In: --:--  |  PM Out: 17:00
Status: Incomplete
```

**Example 3 - Only AM Complete:**
```
AM In: 08:00  |  AM Out: 12:00  |  PM In: --:--  |  PM Out: --:--
Status: Incomplete (has complete AM, so not abandoned)
```

**Example 4 - Only PM Complete:**
```
AM In: --:--  |  AM Out: --:--  |  PM In: 13:00  |  PM Out: 17:00
Status: Incomplete (has complete PM, so not abandoned)
```

**Example 5 - Has Out but no In:**
```
AM In: --:--  |  AM Out: 12:00  |  PM In: --:--  |  PM Out: --:--
Status: Incomplete (unusual case - clocked out without clocking in)
```

---

### 5. **On Leave** (Gray Badge)
**Condition:** Employee has approved leave for that day
- Leave application is approved
- Leave covers the date

**Example:**
```
Date: 2024-01-15
Leave Type: Vacation Leave
Status: On Leave
```

---

## Decision Tree

```
Is employee on approved leave?
├─ YES → On Leave
└─ NO
   ├─ Has AM In OR PM In?
   │  ├─ NO → Absent
   │  └─ YES
   │     ├─ Has AM In, AM Out, PM In, PM Out?
   │     │  ├─ YES → Present
   │     │  └─ NO
   │     │     ├─ Has AM In but no AM Out AND no PM attendance?
   │     │     │  ├─ YES → Abandoned
   │     │     │  └─ NO
   │     │     │     ├─ Has PM In but no PM Out AND no AM attendance?
   │     │     │     │  ├─ YES → Abandoned
   │     │     │     │  └─ NO → Incomplete
```

---

## Visual Examples

### Complete Day (Present)
```
┌─────────┬─────────┬─────────┬─────────┐
│ AM In   │ AM Out  │ PM In   │ PM Out  │
├─────────┼─────────┼─────────┼─────────┤
│ 08:00   │ 12:00   │ 13:00   │ 17:00   │
└─────────┴─────────┴─────────┴─────────┘
Status: ✅ Present
```

### No Attendance (Absent)
```
┌─────────┬─────────┬─────────┬─────────┐
│ AM In   │ AM Out  │ PM In   │ PM Out  │
├─────────┼─────────┼─────────┼─────────┤
│ --:--   │ --:--   │ --:--   │ --:--   │
└─────────┴─────────┴─────────┴─────────┘
Status: ❌ Absent
```

### Clocked In, Never Out (Abandoned)
```
┌─────────┬─────────┬─────────┬─────────┐
│ AM In   │ AM Out  │ PM In   │ PM Out  │
├─────────┼─────────┼─────────┼─────────┤
│ 08:00   │ --:--   │ --:--   │ --:--   │
└─────────┴─────────┴─────────┴─────────┘
Status: ⚠️ Abandoned
```

### Missing Some Logs (Incomplete)
```
┌─────────┬─────────┬─────────┬─────────┐
│ AM In   │ AM Out  │ PM In   │ PM Out  │
├─────────┼─────────┼─────────┼─────────┤
│ 08:00   │ --:--   │ 13:00   │ 17:00   │
└─────────┴─────────┴─────────┴─────────┘
Status: ⚠️ Incomplete
```

---

## Color Coding

| Status | Color | Hex Code | Background |
|--------|-------|----------|------------|
| Present | Green | #15803d | #15803d18 |
| Absent | Red | #8e1e18 | #8e1e1818 |
| Abandoned | Orange | #d97706 | #d9770618 |
| Incomplete | Yellow | #d9bb00 | #d9bb0018 |
| On Leave | Gray | #6b6a8a | #6b6a8a18 |

---

## Implementation Locations

### 1. Admin Detailed DTR Modal
**File:** `resources/views/permanent/attendance/modals/detailedDtrModal.blade.php`
**Function:** `displayDetailedDTR()`

### 2. Permanent Detailed Time Record Tab
**File:** `resources/views/permanent/attendance/permanentAttendance.blade.php`
**Function:** `displayDetailedTable()`

---

## Common Scenarios

### Scenario 1: Half-Day Work (Morning Only)
```
AM In: 08:00  |  AM Out: 12:00  |  PM In: --:--  |  PM Out: --:--
Status: Incomplete
Reason: Has complete AM but no PM attendance
```

### Scenario 2: Half-Day Work (Afternoon Only)
```
AM In: --:--  |  AM Out: --:--  |  PM In: 13:00  |  PM Out: 17:00
Status: Incomplete
Reason: Has complete PM but no AM attendance
```

### Scenario 3: Forgot to Clock Out in Morning
```
AM In: 08:00  |  AM Out: --:--  |  PM In: 13:00  |  PM Out: 17:00
Status: Incomplete
Reason: Has PM attendance, so not abandoned
```

### Scenario 4: Forgot to Clock In After Lunch
```
AM In: 08:00  |  AM Out: 12:00  |  PM In: --:--  |  PM Out: 17:00
Status: Incomplete
Reason: Missing PM In but has PM Out
```

### Scenario 5: Emergency - Left Without Clocking Out
```
AM In: 08:00  |  AM Out: --:--  |  PM In: --:--  |  PM Out: --:--
Status: Abandoned
Reason: Clocked in but never clocked out, no other attendance
```

### Scenario 6: Came Late, Left Early
```
AM In: --:--  |  AM Out: --:--  |  PM In: 14:00  |  PM Out: 16:00
Status: Incomplete
Reason: Only has PM attendance, but both In and Out are present
```

---

## Key Differences: Abandoned vs Incomplete

### Abandoned
- **Single period** attendance only
- Has **In** but no **Out** for that period
- No attendance in the other period
- Suggests employee left without properly clocking out

### Incomplete
- Has attendance in **multiple periods** OR
- Has **complete** attendance in one period OR
- Has unusual patterns (Out without In)
- Suggests missing logs but not abandonment

---

## Testing Checklist

- [ ] Present: All 4 logs → Green badge
- [ ] Absent: No AM In, No PM In → Red badge
- [ ] Abandoned: AM In only, no AM Out, no PM → Orange badge
- [ ] Abandoned: PM In only, no PM Out, no AM → Orange badge
- [ ] Incomplete: AM complete, no PM → Yellow badge
- [ ] Incomplete: PM complete, no AM → Yellow badge
- [ ] Incomplete: AM In, no AM Out, has PM → Yellow badge
- [ ] Incomplete: Has AM, PM In, no PM Out → Yellow badge
- [ ] On Leave: Approved leave → Gray badge

---

## Summary

The standardized logic ensures consistent status display across:
- Admin Detailed DTR Modal
- Permanent Employee Detailed Time Record Tab
- Any future attendance reports

**Priority Order:**
1. On Leave (if approved leave exists)
2. Absent (if no clock in at all)
3. Abandoned (if single period with In but no Out)
4. Present (if all 4 logs complete)
5. Incomplete (everything else)
