"""
Test script for flexible name search in chatbot
"""
import re

def extract_name_from_question(question):
    """Extract employee name from question and clean it"""
    # Common patterns: "ni [Name]", "of [Name]", "for [Name]", "[Name]'s"
    patterns = [
        r'\bni\s+([A-Z][a-z]+(?:\s+[A-Z]\.?)?(?:\s+[A-Z][a-z]+)?)',  # ni Jeremy R. Pogi
        r'\bkay\s+([A-Z][a-z]+(?:\s+[A-Z]\.?)?(?:\s+[A-Z][a-z]+)?)',  # kay Jeremy
        r'\bof\s+([A-Z][a-z]+(?:\s+[A-Z]\.?)?(?:\s+[A-Z][a-z]+)?)',  # of Jeremy
        r'\bfor\s+([A-Z][a-z]+(?:\s+[A-Z]\.?)?(?:\s+[A-Z][a-z]+)?)',  # for Jeremy
        r"([A-Z][a-z]+(?:\s+[A-Z]\.?)?(?:\s+[A-Z][a-z]+)?)'s",  # Jeremy's
    ]
    
    for pattern in patterns:
        match = re.search(pattern, question)
        if match:
            full_name = match.group(1)
            # Remove middle initial and period
            name_parts = full_name.split()
            # Filter out single letters or initials (like "R." or "R")
            cleaned_parts = [part for part in name_parts if len(part.replace('.', '')) > 1]
            return ' '.join(cleaned_parts)
    
    return None

def build_flexible_name_condition(name):
    """Build SQL WHERE condition for flexible name matching"""
    if not name:
        return ""
    
    name_parts = name.split()
    conditions = []
    
    # Add condition for each name part
    for part in name_parts:
        conditions.append(f"first_name LIKE '%{part}%'")
        conditions.append(f"last_name LIKE '%{part}%'")
    
    # Add condition for full name
    conditions.append(f"CONCAT(first_name, ' ', last_name) LIKE '%{name}%'")
    
    return " OR ".join(conditions)

# Test cases
test_questions = [
    "Magkano ang monthly salary ni Jeremy R. Pogi?",
    "What is Jeremy's salary?",
    "Show me the salary of Jeremy Pogi",
    "Salary for Jeremy R. Pogi",
    "How much does Jeremy earn?",
    "Ano ang sweldo ni Juan Dela Cruz?",
    "Show me Maria's monthly rate",
]

print("=" * 80)
print("FLEXIBLE NAME SEARCH TEST")
print("=" * 80)

for question in test_questions:
    print(f"\nQuestion: {question}")
    extracted = extract_name_from_question(question)
    if extracted:
        print(f"[OK] Extracted: '{extracted}'")
        condition = build_flexible_name_condition(extracted)
        print(f"SQL WHERE: ({condition})")
    else:
        print("[FAIL] No name extracted")

print("\n" + "=" * 80)
print("TEST COMPLETE")
print("=" * 80)

# Test the actual SQL that would be generated
print("\n\nEXAMPLE SQL QUERIES:\n")

test_name = "Jeremy Pogi"
condition = build_flexible_name_condition(test_name)

sql1 = f"""SELECT e.first_name, e.last_name, d.monthly_rate
FROM employees e
JOIN employment_details ed ON e.id = ed.employee_id
JOIN designations d ON ed.designation_id = d.id
WHERE ({condition})"""

print("Query for salary:")
print(sql1)

print("\n" + "-" * 80 + "\n")

sql2 = f"""SELECT e.first_name, e.last_name, 
       dsc.late_deduction, dsc.undertime_deduction, dsc.ot_pay
FROM employees e
JOIN daily_salary_computations dsc ON e.id = dsc.employee_id
WHERE ({condition})"""

print("Query for deductions:")
print(sql2)
