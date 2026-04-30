# Database Upload Guide

## Overview
This script automatically uploads all SQL database files from the `database/` folder to your MySQL workbench.

## Prerequisites
- Python 3.7 or higher
- MySQL Server running on `127.0.0.1:3306`
- Database credentials in `.env` file (or modify the script)

## Installation Steps

### 1. Install Python Dependencies
```bash
pip install -r requirements_db.txt
```

Or install mysql-connector-python directly:
```bash
pip install mysql-connector-python
```

### 2. Configure Database Connection
The script uses these default settings from your `.env`:
- **Host**: `127.0.0.1`
- **Port**: `3306`
- **User**: `root`
- **Password**: (empty)
- **Database**: `primehrismagdalena`

If your settings are different, edit `upload_database.py` and update the `DB_CONFIG` dictionary.

## Running the Script

### Option 1: Direct Execution
```bash
python upload_database.py
```

### Option 2: From PowerShell
```powershell
python upload_database.py
```

### Option 3: Make it Executable (Linux/Mac)
```bash
chmod +x upload_database.py
./upload_database.py
```

## What the Script Does
1. ✓ Connects to MySQL server
2. ✓ Creates the database if it doesn't exist
3. ✓ Reads all SQL files from the `database/` folder
4. ✓ Executes them in the correct order (dependencies first)
5. ✓ Handles errors gracefully
6. ✓ Commits changes after each file
7. ✓ Provides a detailed summary

## File Execution Order
The script processes SQL files in this order:
1. Migrations
2. Departments & Designations
3. Employees
4. Users
5. Addresses, Contacts, Documents
6. Educations, Employment Details
7. Family Members, Government IDs
8. Legal Requirements, Trainings
9. Work Experiences, Eligibilities
10. Attendance & Attendance Corrections
11. Sessions

## Troubleshooting

### Connection Error: "Can't connect to MySQL server"
- Make sure MySQL is running
- Check that host and port are correct
- Verify your credentials

### Permission Error: "Access denied for user 'root'"
- Update the password in `DB_CONFIG` if needed
- Make sure MySQL user has CREATE DATABASE privileges

### "Table already exists" Warning
- This is normal! The script skips duplicate table creation
- Your data will be updated/preserved

### Encoding Issues
- The script uses UTF-8 encoding by default
- If issues persist, check your SQL files encoding

## Output Example
```
======================================================================
PrimeHR Magdalena - Database Upload Script
======================================================================
Database Host: 127.0.0.1
Database Port: 3306
Database User: root
Database Name: primehrismagdalena
Database Folder: f:\PrimeHrProject-Magdalena\database
======================================================================

🔗 Connecting to MySQL server...
✓ Connected successfully!
✓ Database 'primehrismagdalena' created or already exists
✓ Connected to database 'primehrismagdalena'

📁 Found database folder: f:\PrimeHrProject-Magdalena\database
📊 Found 19 SQL files

📄 Processing: primehrismagdalena_migrations.sql (45 statements)
✓ Successfully executed 45 statements from primehrismagdalena_migrations.sql
...

======================================================================
📈 UPLOAD SUMMARY
======================================================================
✓ Successfully uploaded: 19 files
✓ primehrismagdalena_migrations.sql
✓ primehrismagdalena_departments.sql
...
======================================================================
✅ Database upload completed successfully!
======================================================================
```

## Advanced Configuration

### Custom Database Connection
Edit `upload_database.py` and modify:
```python
DB_CONFIG = {
    'host': 'your_host',
    'port': 3306,
    'user': 'your_user',
    'password': 'your_password',
}
DATABASE_NAME = 'your_database'
```

### Skip Specific Files
Comment out files in `SQL_FILES_ORDER` to skip them:
```python
SQL_FILES_ORDER = [
    'primehrismagdalena_migrations.sql',
    # 'primehrismagdalena_departments.sql',  # Skip this
    ...
]
```

## Support
For issues or questions, check the error messages in the output or review the SQL files in the `database/` folder.
