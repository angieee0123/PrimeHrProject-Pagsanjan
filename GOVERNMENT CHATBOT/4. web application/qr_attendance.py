from flask import Blueprint, render_template, request, jsonify, session
import cv2
import numpy as np
from datetime import datetime, timedelta
import mysql.connector
import os
from collections import defaultdict

qr_attendance = Blueprint('qr_attendance', __name__)

# Rate limiting: store last scan time per employee
last_scan_times = defaultdict(lambda: None)
SCAN_COOLDOWN_SECONDS = 5

# Database connection
def get_db_connection():
    return mysql.connector.connect(
        host=os.getenv('DB_HOST', 'localhost'),
        user=os.getenv('DB_USER', 'root'),
        password=os.getenv('DB_PASSWORD', 'admin'),
        database=os.getenv('DB_NAME', 'primehrismagdalena')
    )

@qr_attendance.route('/attendance')
def attendance_page():
    return render_template('attendance.html')

@qr_attendance.route('/attendance/test')
def attendance_test_page():
    return render_template('attendance_test.html')

@qr_attendance.route('/attendance/report')
def attendance_report_page():
    return render_template('attendance_report.html')

@qr_attendance.route('/attendance/manual')
def attendance_manual_page():
    return render_template('attendance_manual.html')

@qr_attendance.route('/attendance/employees', methods=['GET'])
def get_employees():
    """Get list of all employees"""
    try:
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        
        cursor.execute("""
            SELECT e.id, e.employee_id, e.first_name, e.last_name, ed.position
            FROM employees e
            LEFT JOIN employment_details ed ON e.id = ed.employee_id
            ORDER BY e.first_name, e.last_name
        """)
        
        employees = cursor.fetchall()
        cursor.close()
        conn.close()
        
        return jsonify({'success': True, 'employees': employees})
    except Exception as e:
        return jsonify({'success': False, 'message': str(e)}), 500

@qr_attendance.route('/attendance/employee/<int:employee_id>', methods=['GET'])
def get_employee_attendance(employee_id):
    """Get employee info and today's attendance"""
    try:
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        
        # Get employee info
        cursor.execute("""
            SELECT e.id, e.employee_id, e.first_name, e.last_name, ed.position
            FROM employees e
            LEFT JOIN employment_details ed ON e.id = ed.employee_id
            WHERE e.id = %s
        """, (employee_id,))
        
        employee = cursor.fetchone()
        
        if not employee:
            cursor.close()
            conn.close()
            return jsonify({'success': False, 'message': 'Employee not found'}), 404
        
        # Get today's attendance
        cursor.execute("""
            SELECT * FROM attendance
            WHERE employee_id = %s AND date = CURDATE()
        """, (employee_id,))
        
        attendance = cursor.fetchone()
        
        # Convert time objects to strings
        if attendance:
            for key in ['am_in', 'am_out', 'pm_in', 'pm_out', 'ot_in', 'ot_out']:
                if attendance[key]:
                    attendance[key] = str(attendance[key])
        
        cursor.close()
        conn.close()
        
        return jsonify({
            'success': True,
            'employee': employee,
            'attendance': attendance
        })
    except Exception as e:
        return jsonify({'success': False, 'message': str(e)}), 500

@qr_attendance.route('/attendance/manual', methods=['POST'])
def record_manual_attendance():
    """Manually record attendance time"""
    try:
        data = request.json
        employee_id = data.get('employee_id')
        field = data.get('field')  # am_in, am_out, pm_in, pm_out, ot_in, ot_out
        
        if not employee_id or not field:
            return jsonify({'success': False, 'message': 'Missing required fields'}), 400
        
        # Validate field name
        valid_fields = ['am_in', 'am_out', 'pm_in', 'pm_out', 'ot_in', 'ot_out']
        if field not in valid_fields:
            return jsonify({'success': False, 'message': 'Invalid field'}), 400
        
        # Rate limiting check
        scan_key = f"{employee_id}_{field}"
        now = datetime.now()
        
        if last_scan_times[scan_key]:
            time_diff = (now - last_scan_times[scan_key]).total_seconds()
            if time_diff < SCAN_COOLDOWN_SECONDS:
                remaining = int(SCAN_COOLDOWN_SECONDS - time_diff)
                return jsonify({
                    'success': False,
                    'message': f'Please wait {remaining} seconds before scanning again'
                }), 429
        
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        
        # Get current time
        now = datetime.now()
        current_time = now.time()
        current_date = now.date()
        
        # Check if attendance record exists for today
        cursor.execute("""
            SELECT * FROM attendance
            WHERE employee_id = %s AND date = %s
        """, (employee_id, current_date))
        
        attendance = cursor.fetchone()
        
        if attendance:
            # Check if field is already recorded
            if attendance[field]:
                cursor.close()
                conn.close()
                return jsonify({
                    'success': False,
                    'message': f'{field.replace("_", " ").title()} already recorded'
                }), 400
            
            # Update existing record
            cursor.execute(f"""
                UPDATE attendance
                SET {field} = %s
                WHERE employee_id = %s AND date = %s
            """, (current_time, employee_id, current_date))
        else:
            # Create new record
            cursor.execute(f"""
                INSERT INTO attendance (employee_id, date, {field})
                VALUES (%s, %s, %s)
            """, (employee_id, current_date, current_time))
        
        conn.commit()
        cursor.close()
        conn.close()
        
        # Update last scan time
        last_scan_times[scan_key] = now
        
        return jsonify({
            'success': True,
            'message': f'{field.replace("_", " ").title()} recorded successfully',
            'time': now.strftime('%I:%M %p')
        })
        
    except Exception as e:
        return jsonify({'success': False, 'message': str(e)}), 500

@qr_attendance.route('/attendance/report/data', methods=['GET'])
def get_attendance_report():
    try:
        date_from = request.args.get('date_from')
        date_to = request.args.get('date_to')
        employee_id = request.args.get('employee_id')
        
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        
        # Build query - INNER JOIN ensures only registered employees are fetched
        query = """
            SELECT 
                a.id,
                a.employee_id,
                a.date,
                a.am_in,
                a.am_out,
                a.pm_in,
                a.pm_out,
                a.ot_in,
                a.ot_out,
                CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                e.employee_id as emp_id
            FROM attendance a
            INNER JOIN employees e ON a.employee_id = e.id
            WHERE 1=1
        """
        params = []
        
        if date_from:
            query += " AND a.date >= %s"
            params.append(date_from)
        
        if date_to:
            query += " AND a.date <= %s"
            params.append(date_to)
        
        if employee_id:
            query += " AND a.employee_id = %s"
            params.append(employee_id)
        
        query += " ORDER BY a.date DESC, e.first_name ASC"
        
        print(f"Query: {query}")
        print(f"Params: {params}")
        
        cursor.execute(query, tuple(params))
        records = cursor.fetchall()
        
        # Convert all fields to JSON-serializable format
        for record in records:
            # Convert date to string
            if record.get('date'):
                record['date'] = str(record['date'])
            
            # Convert time fields (timedelta) to string
            for key in ['am_in', 'am_out', 'pm_in', 'pm_out', 'ot_in', 'ot_out']:
                if record.get(key) is not None:
                    # timedelta to time string
                    td = record[key]
                    total_seconds = int(td.total_seconds())
                    hours = total_seconds // 3600
                    minutes = (total_seconds % 3600) // 60
                    seconds = total_seconds % 60
                    record[key] = f"{hours:02d}:{minutes:02d}:{seconds:02d}"
        
        # Calculate stats
        stats = calculate_stats(cursor, date_from, date_to)
        
        cursor.close()
        conn.close()
        
        return jsonify({
            'success': True,
            'records': records,
            'stats': stats
        })
        
    except Exception as e:
        import traceback
        print(f"Error in get_attendance_report: {str(e)}")
        print(traceback.format_exc())
        return jsonify({'success': False, 'message': str(e)}), 500

def calculate_stats(cursor, date_from, date_to):
    """Calculate attendance statistics for registered employees only"""
    stats = {
        'total_records': 0,
        'present_today': 0,
        'avg_hours': '0h',
        'total_ot': '0h'
    }
    
    try:
        # Total records - only for registered employees
        query = """
            SELECT COUNT(*) as count 
            FROM attendance a
            INNER JOIN employees e ON a.employee_id = e.id
            WHERE 1=1
        """
        params = []
        
        if date_from:
            query += " AND a.date >= %s"
            params.append(date_from)
        if date_to:
            query += " AND a.date <= %s"
            params.append(date_to)
        
        cursor.execute(query, tuple(params))
        result = cursor.fetchone()
        stats['total_records'] = result['count'] if result else 0
        
        # Present today - only registered employees
        cursor.execute("""
            SELECT COUNT(DISTINCT a.employee_id) as count 
            FROM attendance a
            INNER JOIN employees e ON a.employee_id = e.id
            WHERE a.date = CURDATE() 
            AND (a.am_in IS NOT NULL OR a.pm_in IS NOT NULL OR a.ot_in IS NOT NULL)
        """)
        result = cursor.fetchone()
        stats['present_today'] = result['count'] if result else 0
        
        # Calculate average hours and total OT - only for registered employees
        query = """
            SELECT a.am_in, a.am_out, a.pm_in, a.pm_out, a.ot_in, a.ot_out
            FROM attendance a
            INNER JOIN employees e ON a.employee_id = e.id
            WHERE 1=1
        """
        params = []
        if date_from:
            query += " AND a.date >= %s"
            params.append(date_from)
        if date_to:
            query += " AND a.date <= %s"
            params.append(date_to)
        
        cursor.execute(query, tuple(params))
        all_records = cursor.fetchall()
        
        total_minutes = 0
        total_ot_minutes = 0
        
        for record in all_records:
            # Calculate regular hours (AM + PM)
            if record['am_in'] and record['am_out']:
                total_minutes += calculate_minutes_diff(record['am_in'], record['am_out'])
            if record['pm_in'] and record['pm_out']:
                total_minutes += calculate_minutes_diff(record['pm_in'], record['pm_out'])
            
            # Calculate OT hours
            if record['ot_in'] and record['ot_out']:
                ot_mins = calculate_minutes_diff(record['ot_in'], record['ot_out'])
                total_minutes += ot_mins
                total_ot_minutes += ot_mins
        
        if len(all_records) > 0:
            avg_minutes = total_minutes // len(all_records)
            stats['avg_hours'] = f"{avg_minutes // 60}h {avg_minutes % 60}m"
        
        if total_ot_minutes > 0:
            stats['total_ot'] = f"{total_ot_minutes // 60}h {total_ot_minutes % 60}m"
        
    except Exception as e:
        print(f"Stats calculation error: {e}")
        import traceback
        print(traceback.format_exc())
    
    return stats

def calculate_minutes_diff(time_in, time_out):
    """Calculate difference in minutes between two times"""
    if isinstance(time_in, str):
        time_in = datetime.strptime(time_in, '%H:%M:%S').time()
    if isinstance(time_out, str):
        time_out = datetime.strptime(time_out, '%H:%M:%S').time()
    
    in_minutes = time_in.hour * 60 + time_in.minute
    out_minutes = time_out.hour * 60 + time_out.minute
    
    return out_minutes - in_minutes

@qr_attendance.route('/scan_qr', methods=['POST'])
def scan_qr():
    try:
        file = request.files.get('qr_image')
        if not file:
            return jsonify({'success': False, 'message': 'No image provided'}), 400
        
        # Read image
        img_bytes = np.frombuffer(file.read(), np.uint8)
        img = cv2.imdecode(img_bytes, cv2.IMREAD_COLOR)
        
        # Use OpenCV's QRCodeDetector (no external DLL needed)
        qr_detector = cv2.QRCodeDetector()
        data, bbox, straight_qrcode = qr_detector.detectAndDecode(img)
        
        if not data:
            return jsonify({'success': False, 'message': 'No QR code detected'}), 400
        
        employee_id = data.strip()
        
        # Rate limiting check - prevent rapid scans from same employee
        now = datetime.now()
        scan_key = f"{employee_id}_scan"
        
        if last_scan_times[scan_key]:
            time_diff = (now - last_scan_times[scan_key]).total_seconds()
            if time_diff < SCAN_COOLDOWN_SECONDS:
                remaining = int(SCAN_COOLDOWN_SECONDS - time_diff)
                return jsonify({
                    'success': False,
                    'message': f'Please wait {remaining} seconds before scanning again'
                }), 429
        
        # Get employee info
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        
        cursor.execute("""
            SELECT e.id, e.first_name, e.last_name, ed.position 
            FROM employees e
            LEFT JOIN employment_details ed ON e.id = ed.employee_id
            WHERE e.id = %s
        """, (employee_id,))
        
        employee = cursor.fetchone()
        
        if not employee:
            cursor.close()
            conn.close()
            return jsonify({'success': False, 'message': 'Employee not found'}), 404
        
        # Get current time and determine period
        now = datetime.now()
        current_time = now.time()
        current_date = now.date()
        
        # Define time periods
        # AM: 8:00 AM - 12:00 PM
        # PM: 1:00 PM - 5:00 PM  
        # OT: 6:00 PM - 6:00 AM (next day)
        am_start = datetime.strptime('08:00', '%H:%M').time()
        am_end = datetime.strptime('12:00', '%H:%M').time()
        pm_start = datetime.strptime('13:00', '%H:%M').time()
        pm_end = datetime.strptime('17:00', '%H:%M').time()
        ot_start = datetime.strptime('18:00', '%H:%M').time()
        ot_end = datetime.strptime('06:00', '%H:%M').time()
        
        # Get today's attendance record
        cursor.execute("""
            SELECT * FROM attendance 
            WHERE employee_id = %s AND date = %s
        """, (employee_id, current_date))
        
        attendance = cursor.fetchone()
        
        # Determine action based on time and existing records
        action = None
        field_to_update = None
        
        # Check which period we're in
        if am_start <= current_time < am_end:
            # AM Period (8:00 AM - 12:00 PM)
            if not attendance or attendance['am_in'] is None:
                field_to_update = 'am_in'
                action = 'AM TIME IN'
            elif attendance['am_out'] is None:
                field_to_update = 'am_out'
                action = 'AM TIME OUT'
            else:
                cursor.close()
                conn.close()
                return jsonify({'success': False, 'message': 'AM attendance already completed'}), 400
                
        elif pm_start <= current_time < pm_end:
            # PM Period (1:00 PM - 5:00 PM)
            if not attendance or attendance['pm_in'] is None:
                field_to_update = 'pm_in'
                action = 'PM TIME IN'
            elif attendance['pm_out'] is None:
                field_to_update = 'pm_out'
                action = 'PM TIME OUT'
            else:
                cursor.close()
                conn.close()
                return jsonify({'success': False, 'message': 'PM attendance already completed'}), 400
                
        else:
            # OT Period (6:00 PM - 6:00 AM)
            # This includes: 6:00 PM to 11:59 PM and 12:00 AM to 6:00 AM
            if not attendance or attendance['ot_in'] is None:
                field_to_update = 'ot_in'
                action = 'OT TIME IN'
            elif attendance['ot_out'] is None:
                field_to_update = 'ot_out'
                action = 'OT TIME OUT'
            else:
                cursor.close()
                conn.close()
                return jsonify({'success': False, 'message': 'OT attendance already completed'}), 400
        
        # Update or insert attendance
        if not attendance:
            cursor.execute("""
                INSERT INTO attendance (employee_id, date, {}) 
                VALUES (%s, %s, %s)
            """.format(field_to_update), (employee_id, current_date, current_time))
        else:
            cursor.execute("""
                UPDATE attendance 
                SET {} = %s 
                WHERE employee_id = %s AND date = %s
            """.format(field_to_update), (current_time, employee_id, current_date))
        
        conn.commit()
        cursor.close()
        conn.close()
        
        # Update last scan time for rate limiting
        last_scan_times[scan_key] = now
        
        return jsonify({
            'success': True,
            'action': action,
            'employee': {
                'name': f"{employee['first_name']} {employee['last_name']}",
                'position': employee['position'],
                'time': now.strftime('%I:%M %p')
            }
        })
        
    except Exception as e:
        return jsonify({'success': False, 'message': str(e)}), 500

@qr_attendance.route('/generate_qr/<int:employee_id>')
def generate_qr(employee_id):
    import qrcode
    from io import BytesIO
    import base64
    
    # Generate QR code
    qr = qrcode.QRCode(version=1, box_size=10, border=4)
    qr.add_data(str(employee_id))
    qr.make(fit=True)
    
    img = qr.make_image(fill_color="black", back_color="white")
    
    # Convert to base64
    buffer = BytesIO()
    img.save(buffer, format='PNG')
    img_str = base64.b64encode(buffer.getvalue()).decode()
    
    return jsonify({'qr_code': f'data:image/png;base64,{img_str}'})
