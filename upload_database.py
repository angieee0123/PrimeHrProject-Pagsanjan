#!/usr/bin/env python3
"""
Database Upload Script for PrimeHR Magdalena
Uploads all SQL files from the database folder to the MySQL workbench
"""

import os
import sys
import mysql.connector
from mysql.connector import Error
from pathlib import Path

# Database Configuration
DB_CONFIG = {
    'host': '127.0.0.1',
    'port': 3306,
    'user': 'root',
    'password': '',  # Empty password as per .env
}

DATABASE_NAME = 'primehrismagdalena'
DATABASE_FOLDER = Path(__file__).parent / 'database'

# SQL files to execute in order (dependencies first)
SQL_FILES_ORDER = [
    'primehrismagdalena_migrations.sql',
    'primehrismagdalena_departments.sql',
    'primehrismagdalena_designations.sql',
    'primehrismagdalena_employees.sql',
    'primehrismagdalena_users.sql',
    'primehrismagdalena_addresses.sql',
    'primehrismagdalena_contacts.sql',
    'primehrismagdalena_documents.sql',
    'primehrismagdalena_educations.sql',
    'primehrismagdalena_employment_details.sql',
    'primehrismagdalena_family_members.sql',
    'primehrismagdalena_government_ids.sql',
    'primehrismagdalena_legal_requirements.sql',
    'primehrismagdalena_trainings.sql',
    'primehrismagdalena_work_experiences.sql',
    'primehrismagdalena_eligibilities.sql',
    'primehrismagdalena_attendance.sql',
    'primehrismagdalena_attendance_corrections.sql',
    'primehrismagdalena_sessions.sql',
]


def create_database(cursor):
    """Create database if it doesn't exist"""
    try:
        cursor.execute(f"CREATE DATABASE IF NOT EXISTS `{DATABASE_NAME}`")
        print(f"✓ Database '{DATABASE_NAME}' created or already exists")
    except Error as err:
        print(f"✗ Error creating database: {err}")
        return False
    return True


def select_database(cursor):
    """Select the database"""
    try:
        cursor.execute(f"USE `{DATABASE_NAME}`")
        print(f"✓ Connected to database '{DATABASE_NAME}'")
    except Error as err:
        print(f"✗ Error selecting database: {err}")
        return False
    return True


def read_sql_file(filepath):
    """Read SQL file and split into individual statements"""
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    return content


def execute_sql_file(cursor, filepath):
    """Execute all SQL statements from a file"""
    filename = os.path.basename(filepath)
    
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            sql_content = f.read()
        
        # Split by semicolon and filter empty statements
        statements = [stmt.strip() for stmt in sql_content.split(';') if stmt.strip()]
        
        print(f"\n📄 Processing: {filename} ({len(statements)} statements)")
        
        statement_count = 0
        for statement in statements:
            try:
                cursor.execute(statement)
                statement_count += 1
            except Error as err:
                # Skip certain non-critical errors (like table already exists)
                if 'already exists' in str(err).lower() or 'duplicate' in str(err).lower():
                    print(f"  ℹ️  {err} - Skipping...")
                    continue
                else:
                    print(f"  ✗ Error executing statement: {err}")
                    print(f"    Statement: {statement[:100]}...")
                    return False
        
        print(f"✓ Successfully executed {statement_count} statements from {filename}")
        return True
        
    except FileNotFoundError:
        print(f"✗ File not found: {filepath}")
        return False
    except Exception as err:
        print(f"✗ Error processing file {filename}: {err}")
        return False


def upload_database():
    """Main function to upload database"""
    print("=" * 70)
    print("PrimeHR Magdalena - Database Upload Script")
    print("=" * 70)
    print(f"Database Host: {DB_CONFIG['host']}")
    print(f"Database Port: {DB_CONFIG['port']}")
    print(f"Database User: {DB_CONFIG['user']}")
    print(f"Database Name: {DATABASE_NAME}")
    print(f"Database Folder: {DATABASE_FOLDER}")
    print("=" * 70)
    
    connection = None
    cursor = None
    
    try:
        # Connect to MySQL server (without selecting a database first)
        print("\n🔗 Connecting to MySQL server...")
        connection = mysql.connector.connect(**DB_CONFIG)
        cursor = connection.cursor()
        print("✓ Connected successfully!")
        
        # Create database if needed
        if not create_database(cursor):
            return False
        
        # Select database
        if not select_database(cursor):
            return False
        
        connection.commit()
        
        # Check if database folder exists
        if not DATABASE_FOLDER.exists():
            print(f"\n✗ Database folder not found: {DATABASE_FOLDER}")
            return False
        
        print(f"\n📁 Found database folder: {DATABASE_FOLDER}")
        
        # Get all SQL files
        sql_files = sorted(DATABASE_FOLDER.glob('*.sql'))
        
        if not sql_files:
            print("✗ No SQL files found in database folder")
            return False
        
        print(f"📊 Found {len(sql_files)} SQL files\n")
        
        # Execute files in specified order
        failed_files = []
        successful_files = []
        
        for filename in SQL_FILES_ORDER:
            filepath = DATABASE_FOLDER / filename
            if filepath.exists():
                if execute_sql_file(cursor, filepath):
                    connection.commit()
                    successful_files.append(filename)
                else:
                    failed_files.append(filename)
                    connection.rollback()
            else:
                print(f"⚠️  File not found (skipping): {filename}")
        
        # Execute any remaining files not in the ordered list
        processed_files = set(SQL_FILES_ORDER)
        for filepath in sql_files:
            if filepath.name not in processed_files:
                if execute_sql_file(cursor, filepath):
                    connection.commit()
                    successful_files.append(filepath.name)
                else:
                    failed_files.append(filepath.name)
                    connection.rollback()
        
        # Summary
        print("\n" + "=" * 70)
        print("📈 UPLOAD SUMMARY")
        print("=" * 70)
        print(f"✓ Successfully uploaded: {len(successful_files)} files")
        if successful_files:
            for f in successful_files:
                print(f"  ✓ {f}")
        
        if failed_files:
            print(f"\n✗ Failed files: {len(failed_files)}")
            for f in failed_files:
                print(f"  ✗ {f}")
            print("=" * 70)
            return False
        
        print("=" * 70)
        print("✅ Database upload completed successfully!")
        print("=" * 70)
        return True
        
    except Error as err:
        print(f"\n✗ Database error: {err}")
        return False
    except Exception as err:
        print(f"\n✗ Unexpected error: {err}")
        import traceback
        traceback.print_exc()
        return False
    finally:
        if cursor:
            cursor.close()
        if connection:
            connection.close()
        print("\n🔌 Connection closed")


if __name__ == '__main__':
    success = upload_database()
    sys.exit(0 if success else 1)
