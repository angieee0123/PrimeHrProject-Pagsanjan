import subprocess

MYSQL = r"C:\Program Files\MySQL\MySQL Server 8.4\bin\mysql.exe"
ARGS  = [MYSQL, "-hlocalhost", "-uroot", "-padmin", "primehrismagdalena"]

statements = [
    "ALTER TABLE trainings ADD COLUMN position_type VARCHAR(50) NULL AFTER conducted_by",
    "ALTER TABLE trainings ADD COLUMN venue VARCHAR(255) NULL AFTER position_type",
    "ALTER TABLE trainings ADD COLUMN cert_no VARCHAR(100) NULL AFTER venue",
    "ALTER TABLE trainings ADD COLUMN ref_doc_no VARCHAR(100) NULL AFTER cert_no",
    "ALTER TABLE trainings ADD COLUMN certificate_path VARCHAR(500) NULL AFTER ref_doc_no",
    "ALTER TABLE trainings ADD COLUMN status ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending' AFTER certificate_path",
    "ALTER TABLE trainings ADD COLUMN verified_by BIGINT UNSIGNED NULL AFTER status",
    "ALTER TABLE trainings ADD COLUMN verified_at TIMESTAMP NULL AFTER verified_by",
    "ALTER TABLE trainings ADD COLUMN rejected_reason TEXT NULL AFTER verified_at",
    "ALTER TABLE trainings ADD COLUMN created_at TIMESTAMP NULL AFTER rejected_reason",
    "ALTER TABLE trainings ADD COLUMN updated_at TIMESTAMP NULL AFTER created_at",
]

for sql in statements:
    r = subprocess.run(ARGS, input=(sql + ";").encode(), capture_output=True)
    out = r.stdout.decode().strip()
    err = r.stderr.decode().strip()
    # filter warning about password
    err = "\n".join(l for l in err.splitlines() if "password" not in l.lower())
    status = "OK" if r.returncode == 0 and not err else f"ERR: {err}"
    print(f"{sql[:60]}... [{status}]")

# Verify
r = subprocess.run(ARGS, input=b"DESCRIBE trainings;", capture_output=True)
print("\n--- DESCRIBE trainings ---")
print(r.stdout.decode())
