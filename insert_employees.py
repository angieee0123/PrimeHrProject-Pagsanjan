import mysql.connector
from datetime import datetime, date

# Database configuration
db_config = {
    'host': '127.0.0.1',
    'user': 'root',
    'password': 'admin',
    'database': 'primehrismagdalena'
}

# Sample employee data
employees = [
    {
        'employee_id': '2024001',
        'first_name': 'Maria',
        'middle_name': 'Santos',
        'last_name': 'Cruz',
        'suffix': None,
        'birth_date': '1990-05-15',
        'place_of_birth': 'Manila',
        'sex': 'Female',
        'civil_status': 'Married',
        'citizenship': 'Filipino',
        'height': 160,
        'weight': 55,
        'blood_type': 'O+',
        'email': 'maria.cruz@primehr.com'
    },
    {
        'employee_id': '2024002',
        'first_name': 'Juan',
        'middle_name': 'Reyes',
        'last_name': 'Dela Cruz',
        'suffix': None,
        'birth_date': '1988-03-20',
        'place_of_birth': 'Quezon City',
        'sex': 'Male',
        'civil_status': 'Single',
        'citizenship': 'Filipino',
        'height': 170,
        'weight': 70,
        'blood_type': 'A+',
        'email': 'juan.delacruz@primehr.com'
    },
    {
        'employee_id': '2024003',
        'first_name': 'Ana',
        'middle_name': 'Garcia',
        'last_name': 'Ramos',
        'suffix': None,
        'birth_date': '1992-07-10',
        'place_of_birth': 'Caloocan',
        'sex': 'Female',
        'civil_status': 'Single',
        'citizenship': 'Filipino',
        'height': 158,
        'weight': 52,
        'blood_type': 'B+',
        'email': 'ana.ramos@primehr.com'
    },
    {
        'employee_id': '2024004',
        'first_name': 'Pedro',
        'middle_name': 'Mendoza',
        'last_name': 'Santos',
        'suffix': 'Jr.',
        'birth_date': '1985-11-25',
        'place_of_birth': 'Pasig',
        'sex': 'Male',
        'civil_status': 'Married',
        'citizenship': 'Filipino',
        'height': 175,
        'weight': 80,
        'blood_type': 'O+',
        'email': 'pedro.santos@primehr.com'
    },
    {
        'employee_id': '2024005',
        'first_name': 'Rosa',
        'middle_name': 'Flores',
        'last_name': 'Bautista',
        'suffix': None,
        'birth_date': '1995-02-14',
        'place_of_birth': 'Makati',
        'sex': 'Female',
        'civil_status': 'Single',
        'citizenship': 'Filipino',
        'height': 162,
        'weight': 58,
        'blood_type': 'AB+',
        'email': 'rosa.bautista@primehr.com'
    },
    {
        'employee_id': '2024006',
        'first_name': 'Carlos',
        'middle_name': 'Torres',
        'last_name': 'Gonzales',
        'suffix': None,
        'birth_date': '1987-09-30',
        'place_of_birth': 'Taguig',
        'sex': 'Male',
        'civil_status': 'Married',
        'citizenship': 'Filipino',
        'height': 168,
        'weight': 72,
        'blood_type': 'A+',
        'email': 'carlos.gonzales@primehr.com'
    },
    {
        'employee_id': '2024007',
        'first_name': 'Luz',
        'middle_name': 'Aquino',
        'last_name': 'Villanueva',
        'suffix': None,
        'birth_date': '1993-06-18',
        'place_of_birth': 'Paranaque',
        'sex': 'Female',
        'civil_status': 'Single',
        'citizenship': 'Filipino',
        'height': 165,
        'weight': 60,
        'blood_type': 'O+',
        'email': 'luz.villanueva@primehr.com'
    },
    {
        'employee_id': '2024008',
        'first_name': 'Miguel',
        'middle_name': 'Castro',
        'last_name': 'Rivera',
        'suffix': None,
        'birth_date': '1991-12-05',
        'place_of_birth': 'Las Pinas',
        'sex': 'Male',
        'civil_status': 'Single',
        'citizenship': 'Filipino',
        'height': 172,
        'weight': 75,
        'blood_type': 'B+',
        'email': 'miguel.rivera@primehr.com'
    },
    {
        'employee_id': '2024009',
        'first_name': 'Elena',
        'middle_name': 'Morales',
        'last_name': 'Fernandez',
        'suffix': None,
        'birth_date': '1989-04-22',
        'place_of_birth': 'Muntinlupa',
        'sex': 'Female',
        'civil_status': 'Married',
        'citizenship': 'Filipino',
        'height': 160,
        'weight': 56,
        'blood_type': 'A+',
        'email': 'elena.fernandez@primehr.com'
    },
    {
        'employee_id': '2024010',
        'first_name': 'Roberto',
        'middle_name': 'Diaz',
        'last_name': 'Mercado',
        'suffix': 'Sr.',
        'birth_date': '1986-08-12',
        'place_of_birth': 'Valenzuela',
        'sex': 'Male',
        'civil_status': 'Married',
        'citizenship': 'Filipino',
        'height': 178,
        'weight': 82,
        'blood_type': 'O+',
        'email': 'roberto.mercado@primehr.com'
    }
]

try:
    conn = mysql.connector.connect(**db_config)
    cursor = conn.cursor()
    
    print("Inserting 10 new employees...")
    
    for emp in employees:
        cursor.execute("""
            INSERT INTO employees 
            (employee_id, first_name, middle_name, last_name, suffix, birth_date, 
             place_of_birth, sex, civil_status, citizenship, height, weight, 
             blood_type, email, created_at)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """, (
            emp['employee_id'],
            emp['first_name'],
            emp['middle_name'],
            emp['last_name'],
            emp['suffix'],
            emp['birth_date'],
            emp['place_of_birth'],
            emp['sex'],
            emp['civil_status'],
            emp['citizenship'],
            emp['height'],
            emp['weight'],
            emp['blood_type'],
            emp['email'],
            datetime.now()
        ))
        print(f"[OK] Inserted: {emp['first_name']} {emp['last_name']} (ID: {emp['employee_id']})")
    
    conn.commit()
    print(f"\n[SUCCESS] Successfully inserted {len(employees)} employees!")
    
    cursor.close()
    conn.close()
    
except mysql.connector.Error as e:
    print(f"[ERROR] Database error: {e}")
except Exception as e:
    print(f"[ERROR] Error: {e}")
