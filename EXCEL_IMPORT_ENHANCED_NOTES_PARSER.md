# SOLUTION: Enhanced Excel Import to Parse Notes Column

## 🎯 OBJECTIVE

Enhance the Excel import system to:
1. **Parse the Notes column** (Column B) for notation codes
2. **Extract meaningful information** from T(0-2-10), VL1, SL(1-2-10), etc.
3. **Store structured data** instead of just text
4. **Enable business logic** based on notation patterns

---

## 📊 WHAT WE'RE PARSING

### Current Column B Content:
```
T(0-2-10)      → Likely: Undertime/Tardiness (Time shortage)
VL1            → Vacation Leave (1st instance)
FL1            → Forced Leave (1st instance)
SL(1-2-10)     → Sick Leave (1-2 days, 10-day annual limit?)
(blank)        → No special notation
```

### Pattern Analysis:
```
Pattern 1: T(X-Y-Z)     = Code with 3 parameters
Pattern 2: VL1, FL1     = Code + single digit
Pattern 3: SL(X-Y-Z)    = Code with range/parameters
Pattern 4: (blank)      = No notation
```

---

## 🔧 IMPLEMENTATION APPROACH

### Step 1: Create Notation Mapping Table

```sql
CREATE TABLE leave_notation_mappings (
    id BIGINT UNSIGNED PRIMARY KEY,
    notation_code VARCHAR(20) NOT NULL UNIQUE,
    leave_code VARCHAR(10),
    description VARCHAR(255),
    parameters JSON,
    business_rule TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (leave_code) REFERENCES leave_types_config(leave_code)
);

INSERT INTO leave_notation_mappings VALUES
(1, 'T', 'UT', 'Undertime/Tardiness', '{"hours": 0, "minutes": 2}', 'Deduction for time shortage', NOW(), NOW()),
(2, 'VL', 'VL', 'Vacation Leave', NULL, 'Standard vacation leave', NOW(), NOW()),
(3, 'FL', 'FL', 'Forced Leave', NULL, 'Mandatory leave', NOW(), NOW()),
(4, 'SL', 'SL', 'Sick Leave', NULL, 'Medical/health leave', NOW(), NOW()),
(5, 'BL', 'BL', 'Bereavement Leave', NULL, 'Death in family', NOW(), NOW());
```

### Step 2: Create Notation Parser Class

Create: `app/Services/LeaveNotationParser.php`

```php
<?php

namespace App\Services;

class LeaveNotationParser
{
    /**
     * Parse notation from notes column
     * Examples: T(0-2-10), VL1, FL1, SL(1-2-10)
     */
    public static function parse(string $notation): array
    {
        if (empty($notation)) {
            return [
                'code' => null,
                'type' => null,
                'parameters' => [],
                'raw' => '',
                'is_valid' => false,
            ];
        }

        // Pattern: CODE(param1-param2-param3)
        if (preg_match('/^([A-Z]+)\(([^)]+)\)$/', $notation, $matches)) {
            $code = $matches[1];
            $params = explode('-', $matches[2]);
            
            return [
                'code' => $code,
                'type' => self::getType($code),
                'parameters' => $params,
                'raw' => $notation,
                'is_valid' => true,
            ];
        }

        // Pattern: CODE + digit (e.g., VL1, FL1)
        if (preg_match('/^([A-Z]+)(\d+)$/', $notation, $matches)) {
            $code = $matches[1];
            $digit = $matches[2];
            
            return [
                'code' => $code,
                'type' => self::getType($code),
                'parameters' => ['instance' => $digit],
                'raw' => $notation,
                'is_valid' => true,
            ];
        }

        // Pattern: Just code (e.g., VL, SL)
        if (preg_match('/^[A-Z]+$/', $notation)) {
            return [
                'code' => $notation,
                'type' => self::getType($notation),
                'parameters' => [],
                'raw' => $notation,
                'is_valid' => true,
            ];
        }

        // Unknown pattern
        return [
            'code' => null,
            'type' => null,
            'parameters' => [],
            'raw' => $notation,
            'is_valid' => false,
        ];
    }

    /**
     * Get leave type from notation code
     */
    private static function getType(string $code): ?string
    {
        $mapping = [
            'T' => 'UT',      // Undertime
            'VL' => 'VL',     // Vacation Leave
            'SL' => 'SL',     // Sick Leave
            'FL' => 'FL',     // Forced Leave
            'BL' => 'BL',     // Bereavement Leave
            'AL' => 'AL',     // Adoption Leave
            'ML' => 'ML',     // Maternity Leave
            'PL' => 'PL',     // Paternity Leave
        ];

        return $mapping[$code] ?? null;
    }

    /**
     * Extract parameters from T(0-2-10) type notation
     */
    public static function extractParameters(array $parsed): array
    {
        if (!$parsed['is_valid'] || empty($parsed['parameters'])) {
            return [];
        }

        $params = $parsed['parameters'];
        $code = $parsed['code'];

        // Handle T(0-2-10): hours-minutes-seconds
        if ($code === 'T' && count($params) === 3) {
            return [
                'hours' => (int)$params[0],
                'minutes' => (int)$params[1],
                'seconds' => (int)$params[2],
                'total_minutes' => ((int)$params[0] * 60) + (int)$params[1],
            ];
        }

        // Handle SL(1-2-10): min_days-max_days-annual_limit
        if ($code === 'SL' && count($params) === 3) {
            return [
                'min_days' => (int)$params[0],
                'max_days' => (int)$params[1],
                'annual_limit' => (int)$params[2],
            ];
        }

        // Instance number (VL1, FL1, etc.)
        if (isset($params['instance'])) {
            return ['instance' => (int)$params['instance']];
        }

        return [];
    }
}
```

### Step 3: Update LeaveImportService

Modify `app/Services/LeaveImportService.php`:

```php
// Add to imports at top:
use App\Services\LeaveNotationParser;

// Update buildRemark() method:
private static function buildRemark(string $action, string $monthLabel, string $notes, float $amount): string
{
    $parsed = LeaveNotationParser::parse($notes);
    
    $remark = "[IMPORT] {$action} {$amount} credits";
    
    if ($monthLabel !== '') {
        $remark .= " ({$monthLabel})";
    }
    
    if ($parsed['is_valid']) {
        $remark .= " [{$parsed['code']}]";  // Add parsed code
    }
    
    if ($notes !== '') {
        $remark .= " - {$notes}";  // Keep original for reference
    }

    return $remark;
}

// Add new method to handle notation:
private static function processNotation(
    int $employeeId,
    string $notes,
    int $year,
    int $month,
    float $amount,
    string $action
): ?array {
    $parsed = LeaveNotationParser::parse($notes);
    
    if (!$parsed['is_valid']) {
        return null;
    }

    $params = LeaveNotationParser::extractParameters($parsed);
    
    return [
        'notation' => $parsed['raw'],
        'code' => $parsed['code'],
        'leave_type' => $parsed['type'],
        'parameters' => $params,
        'parsed_at' => now(),
    ];
}
```

### Step 4: Update LeaveTransaction Model

Add to `app/Models/LeaveTransaction.php`:

```php
class LeaveTransaction extends Model
{
    protected $fillable = [
        // ... existing fields ...
        'notation_code',      // NEW: T, VL1, SL(1-2-10)
        'notation_parameters', // NEW: JSON
    ];

    protected $casts = [
        // ... existing casts ...
        'notation_parameters' => 'array',
    ];

    /**
     * Get parsed notation
     */
    public function getParsedNotation(): array
    {
        return [
            'code' => $this->notation_code,
            'parameters' => $this->notation_parameters ?? [],
        ];
    }
}
```

### Step 5: Update Database Migration

```sql
ALTER TABLE leave_transactions ADD COLUMN (
    notation_code VARCHAR(20) DEFAULT NULL COMMENT 'Parsed notation code (T, VL, SL, etc.)',
    notation_parameters JSON DEFAULT NULL COMMENT 'Extracted parameters',
    notation_parsed_at TIMESTAMP DEFAULT NULL COMMENT 'When notation was parsed'
);

CREATE INDEX idx_notation_code ON leave_transactions(notation_code);
```

---

## 🔄 ENHANCED IMPORT FLOW

```
Excel File (Column B: "T(0-2-10)")
    ↓
LeaveImportService reads data
    ↓
LeaveNotationParser.parse("T(0-2-10)")
    ↓
Returns:
{
    code: "T",
    type: "UT",
    parameters: ["0", "2", "10"],
    is_valid: true
}
    ↓
LeaveNotationParser.extractParameters()
    ↓
Returns:
{
    hours: 0,
    minutes: 2,
    seconds: 10,
    total_minutes: 2
}
    ↓
Creates Transaction with:
- notation_code: "T"
- notation_parameters: {hours: 0, minutes: 2, seconds: 10}
- remarks: "[IMPORT] Earned 1.25 credits (March) [T] - T(0-2-10)"
    ↓
✅ Data stored with parsed notation
```

---

## 💾 DATABASE RESULT

```sql
-- In leave_transactions table after enhanced import:

SELECT 
    transaction_date,
    amount,
    remarks,
    notation_code,
    notation_parameters
FROM leave_transactions
WHERE reference_type = 'leave_import';

Results:
──────────────────────────────────────────────────────────────────────
2024-03-31 | 1.25  | [IMPORT] Earned 1.25 ... [T] - T(0-2-10)    | T | {"hours": 0, "minutes": 2, "seconds": 10}
2024-03-31 | 0.271 | [IMPORT] Used 0.271 ... [T] - T(0-2-10)     | T | {"hours": 0, "minutes": 2, "seconds": 10}
2024-03-31 | 1.25  | [IMPORT] Earned 1.25 ... [VL] - VL1        | VL| {"instance": 1}
2024-02-28 | 1.0   | [IMPORT] Earned 1.0 ... [FL] - FL1          | FL| {"instance": 1}
2024-04-30 | 0.833 | [IMPORT] Earned 0.833 ... [SL] - SL(1-2-10) | SL| {"min_days": 1, "max_days": 2, "annual_limit": 10}
```

---

## 🎯 BENEFITS

✅ **Structured Data:** Notation codes stored separately  
✅ **Parsed Parameters:** Extract meaning from T(0-2-10)  
✅ **Business Logic:** Can validate based on parameters  
✅ **Reporting:** Filter/report by notation type  
✅ **Audit Trail:** Original notation + parsed data both stored  
✅ **Backward Compatible:** Original import still works  

---

## 📋 USAGE EXAMPLES

### Query by Notation:
```sql
-- Find all undertime entries
SELECT * FROM leave_transactions 
WHERE notation_code = 'T';

-- Find all entries with specific parameters
SELECT * FROM leave_transactions 
WHERE JSON_EXTRACT(notation_parameters, '$.hours') = 0;
```

### Generate Reports:
```sql
-- Undertime summary by parameter
SELECT 
    notation_parameters->>'$.total_minutes' as total_minutes,
    COUNT(*) as count,
    SUM(amount) as total_amount
FROM leave_transactions
WHERE notation_code = 'T'
GROUP BY notation_parameters->>'$.total_minutes';
```

### Business Logic:
```php
if ($transaction->notation_code === 'T') {
    $params = $transaction->notation_parameters;
    if ($params['total_minutes'] > 60) {
        // Apply different rule for > 1 hour undertime
    }
}
```

---

## 🚀 IMPLEMENTATION STEPS

1. **Create LeaveNotationParser.php** (40 minutes)
   - Add parse() method
   - Add extractParameters() method
   - Add type mapping

2. **Update LeaveImportService.php** (20 minutes)
   - Modify buildRemark() to use parser
   - Add processNotation() method
   - Update transaction creation

3. **Update LeaveTransaction Model** (10 minutes)
   - Add notation_code field
   - Add notation_parameters field
   - Add accessor method

4. **Create Migration** (5 minutes)
   - Add columns to leave_transactions
   - Create index on notation_code

5. **Test** (30 minutes)
   - Test with sample data
   - Verify parsing
   - Verify storage

**Total Time: ~2 hours**

---

## ⚠️ CURRENT STATE VS ENHANCED

### Current System:
```
Input: "T(0-2-10)"
Storage: remarks = "... - T(0-2-10)"
Result: Text only, no interpretation
```

### Enhanced System:
```
Input: "T(0-2-10)"
Parsing: code=T, type=UT, hours=0, minutes=2, sec=10
Storage: 
  - remarks = "... [T] - T(0-2-10)"
  - notation_code = "T"
  - notation_parameters = {"hours": 0, "minutes": 2, "seconds": 10}
Result: Structured data, fully interpretable
```

---

## 📝 RECOMMENDATION

### Phase 1: Use Current System
✅ Import your data now
✅ All data accurate
✅ Notes stored as text reference

### Phase 2: Enhance (Optional)
🔧 Build notation parser
🔧 Extract parameters
🔧 Enable business logic
🔧 Create specialized reports

**Timeline:** Phase 1 now, Phase 2 later if needed

---

## 📊 TESTING SAMPLE DATA

```php
// Test cases for LeaveNotationParser:

$test_cases = [
    'T(0-2-10)' => ['code' => 'T', 'params' => 3, 'valid' => true],
    'VL1' => ['code' => 'VL', 'params' => 1, 'valid' => true],
    'FL1' => ['code' => 'FL', 'params' => 1, 'valid' => true],
    'SL(1-2-10)' => ['code' => 'SL', 'params' => 3, 'valid' => true],
    '' => ['code' => null, 'params' => 0, 'valid' => false],
    'VL' => ['code' => 'VL', 'params' => 0, 'valid' => true],
];

foreach ($test_cases as $notation => $expected) {
    $parsed = LeaveNotationParser::parse($notation);
    assert($parsed['code'] === $expected['code']);
    assert(count($parsed['parameters']) === $expected['params']);
    assert($parsed['is_valid'] === $expected['valid']);
}
```

---

## ✨ SUMMARY

**Current State:** Notes column is text only (works fine for basic import)  
**Enhanced State:** Notes parsed into structured data (enables advanced features)  
**Your Choice:** Use now, enhance later  
**Data Integrity:** Safe either way - all data correct and auditable  

Ready to implement when needed!
