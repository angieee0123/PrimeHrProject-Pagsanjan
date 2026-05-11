import subprocess
import os
import sys

HOST = "localhost"
USER = "root"
PASSWORD = ""
DATABASE = "primehrismagdalena"

SQL_FOLDER = os.path.join(os.path.dirname(__file__), "database")
MYSQL_BIN = r"C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe"

ORDER = [
    "primehrismagdalena_migrations.sql",
    "primehrismagdalena_cache.sql",
    "primehrismagdalena_cache_locks.sql",
    "primehrismagdalena_failed_jobs.sql",
    "primehrismagdalena_jobs.sql",
    "primehrismagdalena_job_batches.sql",
    "primehrismagdalena_password_reset_tokens.sql",
    "primehrismagdalena_departments.sql",
    "primehrismagdalena_designations.sql",
    "primehrismagdalena_employees.sql",
    "primehrismagdalena_users.sql",
    "primehrismagdalena_addresses.sql",
    "primehrismagdalena_contacts.sql",
    "primehrismagdalena_government_ids.sql",
    "primehrismagdalena_educations.sql",
    "primehrismagdalena_eligibilities.sql",
    "primehrismagdalena_work_experiences.sql",
    "primehrismagdalena_trainings.sql",
    "primehrismagdalena_family_members.sql",
    "primehrismagdalena_documents.sql",
    "primehrismagdalena_legal_requirements.sql",
    "primehrismagdalena_employment_details.sql",
    "primehrismagdalena_schedules.sql",
    "primehrismagdalena_attendance.sql",
    "primehrismagdalena_attendance_corrections.sql",
    "primehrismagdalena_accredited_hours_log.sql",
    "primehrismagdalena_daily_salary_computations.sql",
    "primehrismagdalena_salary_computations.sql",
    "primehrismagdalena_leave_types_config.sql",
    "primehrismagdalena_leave_accrual_rates.sql",
    "primehrismagdalena_sessions.sql",
]

def mysql_cmd(sql, db=None):
    cmd = [MYSQL_BIN, f"-h{HOST}", f"-u{USER}", f"-p{PASSWORD}", "--force"]
    if db:
        cmd.append(db)
    return subprocess.run(cmd, input=sql.encode("utf-8"), capture_output=True)

def run():
    r = mysql_cmd(f"CREATE DATABASE IF NOT EXISTS `{DATABASE}`;")
    if r.returncode != 0:
        print("Failed to create database:", r.stderr.decode())
        sys.exit(1)
    print(f"Database `{DATABASE}` ready.\n")

    for filename in ORDER:
        filepath = os.path.join(SQL_FOLDER, filename)
        if not os.path.exists(filepath):
            print(f"  [SKIP] {filename} not found")
            continue
        with open(filepath, "r", encoding="utf-8") as f:
            sql = f.read()
        r = mysql_cmd(sql, db=DATABASE)
        if r.returncode == 0:
            print(f"  [OK] {filename}")
        else:
            print(f"  [ERROR] {filename}: {r.stderr.decode().strip()}")

    print("\nDone.")

if __name__ == "__main__":
    run()
