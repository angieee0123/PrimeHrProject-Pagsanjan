# CSC Time Conversion - Visual Flow Diagram

## 🔄 Conversion Flow Chart

```
┌─────────────────────────────────────────────────────────────────┐
│                    CSC TIME CONVERSION FLOW                      │
│                  (Government Service Standard)                   │
└─────────────────────────────────────────────────────────────────┘

                         ┌──────────────┐
                         │   MINUTES    │
                         │  (Storage)   │
                         └──────┬───────┘
                                │
                ┌───────────────┼───────────────┐
                │               │               │
                ↓               ↓               ↓
         ┌──────────┐    ┌──────────┐   ┌──────────┐
         │  HOURS   │    │   DAYS   │   │  LEAVE   │
         │  ÷ 60    │    │  ÷ 480   │   │ CREDITS  │
         └──────────┘    └──────────┘   └──────────┘
                │               │               │
                │               │               │
         Display/Report   Display/Report   Deduction
```

---

## 📊 CSC Standard Conversion Matrix

```
┌─────────────────────────────────────────────────────────────────┐
│                     CONVERSION MATRIX                            │
├─────────────┬──────────────┬──────────────┬────────────────────┤
│   MINUTES   │    HOURS     │     DAYS     │   LEAVE CREDITS    │
├─────────────┼──────────────┼──────────────┼────────────────────┤
│     30      │     0.5      │   0.0625     │     -0.0625        │
│     60      │     1.0      │   0.125      │     -0.125         │
│    120      │     2.0      │   0.25       │     -0.25          │
│    240      │     4.0      │   0.5        │     -0.5           │
│    480      │     8.0      │   1.0        │     -1.0           │
│    960      │    16.0      │   2.0        │     -2.0           │
│   2400      │    40.0      │   5.0        │     -5.0           │
└─────────────┴──────────────┴──────────────┴────────────────────┘

Formula:
  Hours = Minutes ÷ 60
  Days = Minutes ÷ 480
  Leave Credits = -(Minutes ÷ 480)
```

---

## 🗓️ Working Days Calculation Flow

```
┌─────────────────────────────────────────────────────────────────┐
│              WORKING DAYS CALCULATION FLOW                       │
└─────────────────────────────────────────────────────────────────┘

    START DATE                                           END DATE
        │                                                    │
        └────────────────────┬───────────────────────────────┘
                             │
                             ↓
                    ┌────────────────┐
                    │  Loop Each Day │
                    └────────┬───────┘
                             │
                             ↓
                    ┌────────────────┐
                    │  Is Weekend?   │
                    │ (Sat or Sun)   │
                    └────┬───────┬───┘
                         │       │
                    YES  │       │  NO
                         ↓       ↓
                    ┌────────┐  ┌────────────┐
                    │  SKIP  │  │ Is Holiday?│
                    └────────┘  └─────┬──────┘
                                      │
                                 YES  │  NO
                                      ↓
                                 ┌────────┐
                                 │  SKIP  │
                                 └────────┘
                                      │
                                      ↓
                                 ┌────────────┐
                                 │   COUNT    │
                                 │ Working Day│
                                 └────────────┘
                                      │
                                      ↓
                                 ┌────────────┐
                                 │   TOTAL    │
                                 │Working Days│
                                 └────────────┘
```

---

## 💰 Late Deduction Process Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                  LATE DEDUCTION PROCESS                          │
└─────────────────────────────────────────────────────────────────┘

    Employee Late
    (e.g., 60 minutes)
           │
           ↓
    ┌──────────────────┐
    │ AccreditedHoursLog│
    │ late_minutes = 60 │
    └─────────┬─────────┘
              │
              ↓
    ┌──────────────────────────┐
    │ LateDeductionService     │
    │ convertMinutesToDays(60) │
    │ Result: 0.125 days       │
    └─────────┬────────────────┘
              │
              ↓
    ┌──────────────────────┐
    │ Check VL Balance     │
    │ Available: 15 days   │
    └─────────┬────────────┘
              │
              ↓
    ┌──────────────────────┐
    │ Deduct from VL       │
    │ 15 - 0.125 = 14.875  │
    └─────────┬────────────┘
              │
              ↓
    ┌──────────────────────┐
    │ Create Transaction   │
    │ Type: debit          │
    │ Amount: -0.125       │
    └─────────┬────────────┘
              │
              ↓
    ┌──────────────────────┐
    │ Update Log           │
    │ late_deducted = true │
    │ leave_type = 'VL'    │
    └──────────────────────┘
```

---

## 📝 Leave Application Validation Flow

```
┌─────────────────────────────────────────────────────────────────┐
│              LEAVE APPLICATION VALIDATION                        │
└─────────────────────────────────────────────────────────────────┘

    Employee Submits Leave
    Start: 2026-01-05 (Mon)
    End: 2026-01-11 (Sun)
    Requested: 7 days
           │
           ↓
    ┌──────────────────────────┐
    │ validateLeaveDays()      │
    │ Calculate working days   │
    └─────────┬────────────────┘
              │
              ↓
    ┌──────────────────────────┐
    │ Loop through dates       │
    │ Mon, Tue, Wed, Thu, Fri  │
    │ Sat (SKIP), Sun (SKIP)   │
    └─────────┬────────────────┘
              │
              ↓
    ┌──────────────────────────┐
    │ Actual Working Days: 5   │
    │ Requested Days: 7        │
    │ Difference: -2           │
    └─────────┬────────────────┘
              │
              ↓
    ┌──────────────────────────┐
    │ Validation Result        │
    │ is_valid: FALSE          │
    │ Message: "Mismatch..."   │
    └─────────┬────────────────┘
              │
              ↓
    ┌──────────────────────────┐
    │ Return Error 422         │
    │ "Please adjust to 5 days"│
    └──────────────────────────┘
```

---

## 🔄 Data Storage & Conversion Strategy

```
┌─────────────────────────────────────────────────────────────────┐
│           DATA STORAGE & CONVERSION STRATEGY                     │
└─────────────────────────────────────────────────────────────────┘

DATABASE LAYER (Storage)
┌────────────────────────────────────────┐
│  Store as MINUTES (Integer)            │
│  - total_accredited_minutes: 480       │
│  - late_minutes: 60                    │
│  - undertime_minutes: 30               │
│  - ot_minutes: 120                     │
└────────────────┬───────────────────────┘
                 │
                 ↓
APPLICATION LAYER (Conversion)
┌────────────────────────────────────────┐
│  CscTimeConversionService              │
│  - convertMinutesToHours()             │
│  - convertMinutesToDays()              │
│  - convertMinutesToLeaveCredits()      │
└────────────────┬───────────────────────┘
                 │
                 ↓
MODEL LAYER (Accessors)
┌────────────────────────────────────────┐
│  AccreditedHoursLog                    │
│  - $log->total_accredited_hours        │
│  - $log->total_accredited_days         │
│  - $log->late_days                     │
│  - $log->leave_deduction               │
└────────────────┬───────────────────────┘
                 │
                 ↓
PRESENTATION LAYER (Display)
┌────────────────────────────────────────┐
│  Formatted Output                      │
│  - "8.0 hrs"                           │
│  - "1.00 day"                          │
│  - "1 hr 30 min"                       │
│  - "-0.125 days"                       │
└────────────────────────────────────────┘
```

---

## 🎯 CSC Compliance Checklist

```
┌─────────────────────────────────────────────────────────────────┐
│                  CSC COMPLIANCE CHECKLIST                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ✅ 1 work day = 8 hours (not 24)                               │
│  ✅ 0.5 day = 4 hours                                           │
│  ✅ 1 hour = 0.125 days                                         │
│  ✅ 1 minute = 0.002083 days                                    │
│  ✅ Working days exclude weekends                               │
│  ✅ Working days exclude holidays                               │
│  ✅ Leave deductions use CSC standard                           │
│  ✅ Salary calculations use 8-hour day                          │
│  ✅ Validation prevents weekend inclusion                       │
│  ✅ All conversions centralized                                 │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## 📊 Before vs After Comparison

```
┌─────────────────────────────────────────────────────────────────┐
│                    BEFORE (24-HOUR DAY)                          │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  60 minutes late                                                │
│  ↓                                                               │
│  60 ÷ 1440 = 0.0417 days  ❌ WRONG                              │
│  ↓                                                               │
│  Deduct 0.0417 from VL                                          │
│  ↓                                                               │
│  UNDER-DEDUCTED by 66.7%                                        │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                    AFTER (8-HOUR WORK DAY)                       │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  60 minutes late                                                │
│  ↓                                                               │
│  CscTimeConversionService::convertMinutesToDays(60)             │
│  ↓                                                               │
│  60 ÷ 480 = 0.125 days  ✅ CORRECT                              │
│  ↓                                                               │
│  Deduct 0.125 from VL                                           │
│  ↓                                                               │
│  ACCURATE CSC-COMPLIANT DEDUCTION                               │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🔗 System Integration Map

```
┌─────────────────────────────────────────────────────────────────┐
│                  SYSTEM INTEGRATION MAP                          │
└─────────────────────────────────────────────────────────────────┘

                    CscTimeConversionService
                             │
        ┌────────────────────┼────────────────────┐
        │                    │                    │
        ↓                    ↓                    ↓
┌───────────────┐   ┌────────────────┐   ┌──────────────┐
│LateDeduction  │   │LeaveApplication│   │ Attendance   │
│   Service     │   │   Observer     │   │  Controller  │
└───────┬───────┘   └────────┬───────┘   └──────┬───────┘
        │                    │                   │
        ↓                    ↓                   ↓
┌───────────────┐   ┌────────────────┐   ┌──────────────┐
│AccreditedHours│   │   Attendance   │   │   Working    │
│     Log       │   │    Records     │   │     Days     │
└───────────────┘   └────────────────┘   └──────────────┘
        │                    │                   │
        └────────────────────┼───────────────────┘
                             ↓
                    ┌────────────────┐
                    │ Leave Balance  │
                    │  Transactions  │
                    └────────────────┘
                             ↓
                    ┌────────────────┐
                    │     Salary     │
                    │  Computation   │
                    └────────────────┘
```

---

## 📈 Conversion Accuracy Graph

```
Late Minutes vs Leave Deduction

Days
1.0 │                                              ●
    │
0.8 │
    │
0.6 │
    │
0.4 │                          ●
    │
0.2 │              ●
    │  ●
0.0 └───────┬───────┬───────┬───────┬───────┬──────→ Minutes
           60      120     240     360     480

Legend:
  ● = CSC Standard (Correct)
  
Before (24-hour): All points would be 66.7% lower ❌
After (8-hour):   All points follow CSC standard ✅
```

---

## 🎯 Key Takeaways

```
┌─────────────────────────────────────────────────────────────────┐
│                        KEY TAKEAWAYS                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  1. ALWAYS use CscTimeConversionService                         │
│     ✅ DO: CSC::convertMinutesToDays($minutes)                  │
│     ❌ DON'T: $minutes / 1440                                   │
│                                                                  │
│  2. STORE as minutes, CONVERT on-the-fly                        │
│     ✅ Database: 480 minutes                                    │
│     ✅ Display: "8.0 hrs" or "1.00 day"                         │
│                                                                  │
│  3. VALIDATE working days for leave                             │
│     ✅ Use: validateLeaveDays()                                 │
│     ✅ Excludes: Weekends + Holidays                            │
│                                                                  │
│  4. USE accessor methods in models                              │
│     ✅ $log->total_accredited_days                              │
│     ✅ $log->late_days                                          │
│     ✅ $log->leave_deduction                                    │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

**Visual Guide Version:** 1.0  
**Last Updated:** 2026-01-XX  
**For:** Prime HRIS - CSC Implementation
