import mysql.connector
from datetime import datetime, date
import random

db_config = {
    'host': '127.0.0.1',
    'user': 'root',
    'password': 'admin',
    'database': 'primehrismagdalena'
}

# Get employee IDs
employee_ids = ['2024001', '2024002', '2024003', '2024004', '2024005', 
                '2024006', '2024007', '2024008', '2024009', '2024010']

try:
    conn = mysql.connector.connect(**db_config)
    cursor = conn.cursor(dictionary=True)
    
    # Get internal IDs for the new employees
    cursor.execute("SELECT id, employee_id, first_name, last_name FROM employees WHERE employee_id IN (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                   tuple(employee_ids))
    employees = cursor.fetchall()
    
    print(f"Found {len(employees)} employees to process\n")
    
    for emp in employees:
        emp_id = emp['id']
        emp_code = emp['employee_id']
        name = f"{emp['first_name']} {emp['last_name']}"
        
        print(f"Processing: {name} (ID: {emp_code})")
        
        # 1. Insert into addresses
        cursor.execute("""
            INSERT INTO addresses (employee_id, type, house_no, street, barangay, city, province, zip_code)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
        """, (emp_id, "residential", f"{random.randint(1, 999)}", "Main Street", "Barangay 1", "Quezon City", "Metro Manila", "1100"))
        
        # 2. Insert into contacts
        cursor.execute("""
            INSERT INTO contacts (employee_id, type, number)
            VALUES (%s, %s, %s)
        """, (emp_id, "mobile", f"0917{random.randint(1000000, 9999999)}"))
        
        # 3. Insert into employment_details
        positions = ["Administrative Assistant", "HR Officer", "Accountant", "IT Specialist", "Manager"]
        statuses = ["Active", "Permanent"]
        cursor.execute("""
            INSERT INTO employment_details (employee_id, position, department, employment_status, appointment_date)
            VALUES (%s, %s, %s, %s, %s)
        """, (emp_id, random.choice(positions), "Human Resources", random.choice(statuses), date(2024, 1, 15)))
        
        # 4. Insert into educations
        schools = ["University of the Philippines", "Ateneo de Manila", "De La Salle University", "University of Santo Tomas"]
        degrees = ["Bachelor of Science in Business Administration", "Bachelor of Science in Computer Science", "Bachelor of Arts in Psychology"]
        cursor.execute("""
            INSERT INTO educations (employee_id, level, school_name, degree, year_graduated)
            VALUES (%s, %s, %s, %s, %s)
        """, (emp_id, "College", random.choice(schools), random.choice(degrees), "2014"))
        
        # 5. Insert into trainings
        trainings = ["Leadership Training", "Data Privacy Seminar", "Customer Service Excellence", "Project Management"]
        cursor.execute("""
            INSERT INTO trainings (employee_id, title, date_from, date_to, hours, conducted_by)
            VALUES (%s, %s, %s, %s, %s, %s)
        """, (emp_id, random.choice(trainings), date(2023, 6, 1), date(2023, 6, 3), 24, "CSC"))
        
        # 6. Insert into work_experiences
        companies = ["ABC Corporation", "XYZ Company", "Tech Solutions Inc", "Business Partners Ltd"]
        cursor.execute("""
            INSERT INTO work_experiences (employee_id, company_name, position, from_date, to_date, is_government)
            VALUES (%s, %s, %s, %s, %s, %s)
        """, (emp_id, random.choice(companies), "Junior Staff", date(2015, 1, 1), date(2020, 12, 31), 0))
        
        # 7. Insert into government_ids
        cursor.execute("""
            INSERT INTO government_ids (employee_id, gsis_no, philhealth_no, pagibig_no, tin_no)
            VALUES (%s, %s, %s, %s, %s)
        """, (emp_id, 
              f"{random.randint(10000000, 99999999)}",
              f"{random.randint(10, 99)}-{random.randint(100000000, 999999999)}-{random.randint(0, 9)}",
              f"{random.randint(1000, 9999)}-{random.randint(1000, 9999)}-{random.randint(1000, 9999)}",
              f"{random.randint(100, 999)}-{random.randint(100, 999)}-{random.randint(100, 999)}-000"))
        
        # 8. Insert into eligibilities
        eligibilities = ["Career Service Professional", "Career Service Sub-Professional", "RA 1080"]
        cursor.execute("""
            INSERT INTO eligibilities (employee_id, type, rating, exam_date)
            VALUES (%s, %s, %s, %s)
        """, (emp_id, random.choice(eligibilities), f"{random.randint(75, 95)}.{random.randint(0, 99)}", date(2020, 3, 15)))
        
        print(f"  [OK] Inserted data for {name}")
    
    conn.commit()
    print(f"\n[SUCCESS] Inserted related data for {len(employees)} employees!")
    
    cursor.close()
    conn.close()
    
except Exception as e:
    print(f"[ERROR] {e}")
    import traceback
    traceback.print_exc()
