# Visual Example: Updated Detailed DTR Display

## Before and After Comparison

### BEFORE (Without Late Deduction Feature):
```
Date          Day        AM In  AM Out  PM In  PM Out  Late    Accredited Hours
May 05, 2026  Tuesday    08:06  12:02   12:06  18:05   6 min   7h 54m
                                                                ✓ Grace: PM
                                                                📋 From Log

May 07, 2026  Thursday   09:00  12:02   12:07  19:07   1 hr    7 hrs
                                                                ✓ Grace: PM
                                                                📋 From Log
```

### AFTER (With Late Deduction Feature):
```
Date          Day        AM In  AM Out  PM In  PM Out  Late    Accredited Hours
May 05, 2026  Tuesday    08:06  12:02   12:06  18:05   6 min   8 hrs
                                                                ✓ Grace: PM
                                                                📋 From Log
                                                                ✓ Late Covered by VL
                                                                6 min late deducted (0.0125 days)

May 07, 2026  Thursday   09:00  12:02   12:07  19:07   1 hr    8 hrs
                                                                ✓ Grace: PM
                                                                📋 From Log
                                                                ✓ Late Covered by VL
                                                                60 min late deducted (0.1250 days)
```

---

## Complete Example Table View

```
┌─────────────────┬───────────┬───────┬────────┬───────┬────────┬───────┬────────┬───────────┬──────┬────────────┬──────────────────────────────────────┬─────────────────┬────────┐
│ Date            │ Day       │ AM In │ AM Out │ PM In │ PM Out │ OT In │ OT Out │ Undertime │ Late │ Total Hrs  │ Accredited Hours                     │ Leave Deduction │ Action │
├─────────────────┼───────────┼───────┼────────┼───────┼────────┼───────┼────────┼───────────┼──────┼────────────┼──────────────────────────────────────┼─────────────────┼────────┤
│ Apr 30, 2026    │ Thursday  │ 08:00 │ 12:00  │ 13:00 │ 17:00  │ —     │ —      │ 0 min     │ 0 min│ 8 hrs      │ 8 hrs                                │ —               │ Edit   │
│                 │           │       │        │       │        │       │        │           │      │            │ 📋 From Log                          │                 │        │
├─────────────────┼───────────┼───────┼────────┼───────┼────────┼───────┼────────┼───────────┼──────┼────────────┼──────────────────────────────────────┼─────────────────┼────────┤
│ May 01, 2026    │ Friday    │ 06:00 │ 12:00  │ 13:00 │ 17:00  │ —     │ —      │ 0 min     │ 0 min│ 10 hrs     │ 8 hrs                                │ —               │ Edit   │
│                 │           │       │        │       │        │       │        │           │      │            │ ✓ Grace: AM, PM                      │                 │        │
│                 │           │       │        │       │        │       │        │           │      │            │ 📋 From Log                          │                 │        │
├─────────────────┼───────────┼───────┼────────┼───────┼────────┼───────┼────────┼───────────┼──────┼────────────┼──────────────────────────────────────┼─────────────────┼────────┤
│ May 02, 2026    │ Saturday  │ —     │ —      │ —     │ —      │ —     │ —      │ 0 min     │ 0 min│ 0 hrs      │ Incomplete                           │ —               │ Edit   │
├─────────────────┼───────────┼───────┼────────┼───────┼────────┼───────┼────────┼───────────┼──────┼────────────┼──────────────────────────────────────┼─────────────────┼────────┤
│ May 03, 2026    │ Sunday    │ —     │ —      │ —     │ —      │ —     │ —      │ 0 min     │ 0 min│ 0 hrs      │ Incomplete                           │ —               │ Edit   │
├─────────────────┼───────────┼───────┼────────┼───────┼────────┼───────┼────────┼───────────┼──────┼────────────┼──────────────────────────────────────┼─────────────────┼────────┤
│ May 04, 2026    │ Monday    │ 08:05 │ 12:09  │ 12:56 │ 19:00  │ —     │ —      │ 0 min     │ 0 min│ 10.1 hrs   │ 8 hrs                                │ —               │ Edit   │
│                 │           │       │        │       │        │       │        │           │      │            │ ✓ Grace: AM, PM                      │                 │        │
│                 │           │       │        │       │        │       │        │           │      │            │ 📋 From Log                          │                 │        │
├─────────────────┼───────────┼───────┼────────┼───────┼────────┼───────┼────────┼───────────┼──────┼────────────┼──────────────────────────────────────┼─────────────────┼────────┤
│ May 05, 2026    │ Tuesday   │ 08:06 │ 12:02  │ 12:06 │ 18:05  │ —     │ —      │ 0 min     │ 6 min│ 9.9 hrs    │ 8 hrs                                │ —               │ Edit   │
│                 │           │       │        │       │        │       │        │           │      │            │ ✓ Grace: PM                          │                 │        │
│                 │           │       │        │       │        │       │        │           │      │            │ 📋 From Log                          │                 │        │
│                 │           │       │        │       │        │       │        │           │      │            │ ✓ Late Covered by VL                 │                 │        │
│                 │           │       │        │       │        │       │        │           │      │            │ 6 min late deducted (0.0125 days)    │                 │        │
├─────────────────┼───────────┼───────┼────────┼───────┼────────┼───────┼────────┼───────────┼──────┼────────────┼──────────────────────────────────────┼─────────────────┼────────┤
│ May 06, 2026    │ Wednesday │ 07:01 │ —      │ 13:03 │ 18:08  │ —     │ —      │ 0 min     │ 0 min│ 5.1 hrs    │ 4 hrs                                │ —               │ Edit   │
│ Incomplete      │           │       │        │       │        │       │        │           │      │            │ ✓ Grace: PM                          │                 │        │
│                 │           │       │        │       │        │       │        │           │      │            │ 📋 From Log                          │                 │        │
├─────────────────┼───────────┼───────┼────────┼───────┼────────┼───────┼────────┼───────────┼──────┼────────────┼──────────────────────────────────────┼─────────────────┼────────┤
│ May 07, 2026    │ Thursday  │ 09:00 │ 12:02  │ 12:07 │ 19:07  │ —     │ —      │ 0 min     │ 1 hr │ 10 hrs     │ 8 hrs                                │ —               │ Edit   │
│                 │           │       │        │       │        │       │        │           │      │            │ ✓ Grace: PM                          │                 │        │
│                 │           │       │        │       │        │       │        │           │      │            │ 📋 From Log                          │                 │        │
│                 │           │       │        │       │        │       │        │           │      │            │ ✓ Late Covered by VL                 │                 │        │
│                 │           │       │        │       │        │       │        │           │      │            │ 60 min late deducted (0.1250 days)   │                 │        │
└─────────────────┴───────────┴───────┴────────┴───────┴────────┴───────┴────────┴───────────┴──────┴────────────┴──────────────────────────────────────┴─────────────────┴────────┘
```

---

## Detailed Breakdown of Accredited Hours Column

### Case 1: Normal Day (No Late)
```
8 hrs
📋 From Log
```
**Color:** Green (#15803d)
**Meaning:** Full 8 hours credited, no issues

---

### Case 2: With Grace Period (No Late)
```
8 hrs
✓ Grace: AM, PM
📋 From Log
```
**Color:** Green (#15803d)
**Meaning:** Full 8 hours credited, grace period applied

---

### Case 3: Late Covered by Vacation Leave
```
8 hrs
✓ Grace: PM
📋 From Log
✓ Late Covered by VL
6 min late deducted (0.0125 days)
```
**Colors:**
- Line 1: Green (#15803d) - Full 8 hours
- Line 2: Green (#15803d) - Grace applied
- Line 3: Gray (#6b6a8a) - From log
- Line 4: Purple (#0b044d) - Late covered (bold)
- Line 5: Gray (#6b6a8a) - Deduction details

**Meaning:** Employee was 6 minutes late, but it was covered by VL, so full 8 hours credited

---

### Case 4: Late Covered by Sick Leave
```
8 hrs
✓ Late Covered by SL
60 min late deducted (0.1250 days)
```
**Colors:**
- Line 1: Green (#15803d) - Full 8 hours
- Line 2: Purple (#0b044d) - Late covered (bold)
- Line 3: Gray (#6b6a8a) - Deduction details

**Meaning:** Employee was 60 minutes late, VL was insufficient, so SL was used

---

### Case 5: Late NOT Covered (Insufficient Leave)
```
7h 54m
✓ Grace: PM
📋 From Log
```
**Color:** Orange (#a16207) - Less than 8 hours
**Meaning:** Employee was late, but no leave balance to cover it

---

### Case 6: Incomplete Record
```
4 hrs
✓ Grace: PM
📋 From Log
```
**Color:** Orange (#a16207)
**Meaning:** Missing some time entries

---

### Case 7: On Approved Leave
```
8 hrs
✓ On Leave
```
**Color:** Green (#15803d)
**Meaning:** Employee was on approved leave (VL, SL, etc.)

---

## Leave Balance Impact

### Example: Employee with 7.95 days VL

#### Day 1: May 05, 2026 (6 minutes late)
```
Before:
VL Balance: 7.95 days

After:
VL Balance: 7.9375 days (7.95 - 0.0125)
Accredited Hours: 8 hrs ✓
```

#### Day 2: May 07, 2026 (60 minutes late)
```
Before:
VL Balance: 7.9375 days

After:
VL Balance: 7.8125 days (7.9375 - 0.1250)
Accredited Hours: 8 hrs ✓
```

#### Summary:
```
Total Late: 66 minutes (1 hr 6 min)
Total Deducted: 0.1375 days
Final VL Balance: 7.8125 days
Total Accredited Hours: 16 hrs (2 days × 8 hrs)
```

---

## Color Legend

| Color | Hex Code | Usage |
|-------|----------|-------|
| Green | #15803d | Full hours, grace applied, on leave |
| Orange | #a16207 | Partial hours, late without coverage |
| Red | #8e1e18 | Absent, zero hours |
| Purple | #0b044d | Late covered by leave (bold) |
| Gray | #6b6a8a | Additional info, deduction details |

---

## Icon Legend

| Icon | Meaning |
|------|---------|
| ✓ | Checkmark - Grace applied, late covered, on leave |
| 📋 | From Log - Data from accredited hours log |
| ⚠️ | Warning - Absent, incomplete |

---

## Mobile/Responsive View

On smaller screens, the Accredited Hours column will stack vertically:

```
Accredited Hours:
┌─────────────────────────────────┐
│ 8 hrs                           │
│ ✓ Grace: PM                     │
│ 📋 From Log                     │
│ ✓ Late Covered by VL            │
│ 6 min late deducted (0.0125 d)  │
└─────────────────────────────────┘
```

---

## Tooltip/Hover Information (Future Enhancement)

When hovering over the late deduction note:

```
┌─────────────────────────────────────────────┐
│ Late Deduction Details                      │
├─────────────────────────────────────────────┤
│ Late Minutes: 6                             │
│ Late Days: 0.0125                           │
│ Deducted From: Vacation Leave (VL)          │
│ VL Balance Before: 7.95 days                │
│ VL Balance After: 7.9375 days               │
│ Transaction Date: May 05, 2026              │
│ Processed By: Admin                         │
└─────────────────────────────────────────────┘
```

---

## Print View

When printing the DTR, the display will be simplified:

```
Date: May 05, 2026
Accredited Hours: 8 hrs (Late: 6 min covered by VL)

Date: May 07, 2026
Accredited Hours: 8 hrs (Late: 60 min covered by VL)
```

---

## Summary Footer

At the bottom of the DTR modal:

```
┌──────────────────────────────────────────────────────────────────┐
│ Total Days: 7                                                    │
│ Present: 5                                                       │
│ Absent: 0                                                        │
│ Late: 2 times                                                    │
│ Total Late: 1 hr 6 min                                           │
│ Total Undertime: 0 min                                           │
│ Late Covered by Leave: 0.1375 days (VL)                          │
└──────────────────────────────────────────────────────────────────┘
```
