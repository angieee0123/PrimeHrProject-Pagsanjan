import qrcode
import mysql.connector
import os
from PIL import Image, ImageDraw, ImageFont

# Database connection
conn = mysql.connector.connect(
    host='localhost',
    user='root',
    password='',
    database='primehrismagdalena'
)

cursor = conn.cursor(dictionary=True)

# Get all employees
cursor.execute("""
    SELECT e.id, e.first_name, e.last_name, ed.position 
    FROM employees e
    LEFT JOIN employment_details ed ON e.id = ed.employee_id
""")

employees = cursor.fetchall()

# Create output directory
os.makedirs('qr_codes', exist_ok=True)

print(f"Generating QR codes for {len(employees)} employees...\n")

for emp in employees:
    employee_id = emp['id']
    name = f"{emp['first_name']} {emp['last_name']}"
    position = emp['position'] or 'N/A'
    
    # Generate QR code
    qr = qrcode.QRCode(version=1, box_size=10, border=4)
    qr.add_data(str(employee_id))
    qr.make(fit=True)
    
    qr_img = qr.make_image(fill_color="black", back_color="white")
    
    # Create card with QR code and employee info
    card_width = 400
    card_height = 550
    card = Image.new('RGB', (card_width, card_height), 'white')
    draw = ImageDraw.Draw(card)
    
    # Paste QR code
    qr_img = qr_img.resize((300, 300))
    card.paste(qr_img, (50, 50))
    
    # Add text
    try:
        font_large = ImageFont.truetype("arial.ttf", 24)
        font_small = ImageFont.truetype("arial.ttf", 18)
    except:
        font_large = ImageFont.load_default()
        font_small = ImageFont.load_default()
    
    # Employee name
    draw.text((card_width//2, 380), name, fill='black', font=font_large, anchor='mm')
    
    # Position
    draw.text((card_width//2, 420), position, fill='gray', font=font_small, anchor='mm')
    
    # Employee ID
    draw.text((card_width//2, 460), f"ID: {employee_id}", fill='gray', font=font_small, anchor='mm')
    
    # Border
    draw.rectangle([(10, 10), (card_width-10, card_height-10)], outline='black', width=2)
    
    # Save
    filename = f"qr_codes/{employee_id}_{name.replace(' ', '_')}.png"
    card.save(filename)
    print(f"✓ Generated: {filename}")

cursor.close()
conn.close()

print(f"\n✅ All QR codes generated in 'qr_codes/' folder")
