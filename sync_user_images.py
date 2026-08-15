"""
Materialise employee photos into Laravel's public storage.

`employees.photo` stores a URL path (`/storage/employees/photos/<ts>_<n>.png`),
never the image bytes -- so the photos have to come from somewhere on disk.
`user_images/` holds them, numbered `<n>.png`, which is the suffix the stored
filename carries.

This reads the DB to learn which filenames the app expects, then copies the
matching source file into `storage/app/public/...` under that name.

    python3 sync_user_images.py            # copy what is missing
    python3 sync_user_images.py --dry-run  # report only, write nothing
    python3 sync_user_images.py --force    # recopy even if already present
"""

import os
import re
import shutil
import subprocess
import sys

ROOT = os.path.dirname(os.path.abspath(__file__))
LARAVEL_APP_DIR = os.path.join(ROOT, "primeHrMagdalenaLaravel")
SOURCE_FOLDER = os.path.join(ROOT, "user_images")
PUBLIC_DISK = os.path.join(LARAVEL_APP_DIR, "storage", "app", "public")
ENV_FILE = os.path.join(LARAVEL_APP_DIR, ".env")
MYSQL_BIN = r"C:\Program Files\MySQL\MySQL Server 8.4\bin\mysql.exe" if sys.platform == "win32" else "mysql"

# `1782459335_1.png` -> `1`
NUMBERED_NAME = re.compile(r"^\d+_(\d+)(\.[A-Za-z0-9]+)$")


def read_env():
    """Pull DB credentials from the Laravel .env so they live in one place."""
    if not os.path.exists(ENV_FILE):
        sys.exit(f"No .env at {ENV_FILE}")

    env = {}
    with open(ENV_FILE, "r", encoding="utf-8") as f:
        for line in f:
            line = line.strip()
            if not line or line.startswith("#") or "=" not in line:
                continue
            key, _, value = line.partition("=")
            env[key.strip()] = value.strip().strip('"').strip("'")

    return {
        "host": env.get("DB_HOST", "127.0.0.1"),
        "port": env.get("DB_PORT", "3306"),
        "user": env.get("DB_USERNAME", "root"),
        "password": env.get("DB_PASSWORD", ""),
        "database": env.get("DB_DATABASE", "primehrismagdalena"),
    }


def query(db, sql):
    """Run a read-only query and return rows as lists of column values."""
    cmd = [
        MYSQL_BIN,
        f"-h{db['host']}",
        f"-P{db['port']}",
        f"-u{db['user']}",
        "--batch",
        "--raw",
        "--skip-column-names",
    ]
    if db["password"]:
        cmd.append(f"-p{db['password']}")
    cmd.append(db["database"])

    r = subprocess.run(cmd, input=sql.encode("utf-8"), capture_output=True)
    if r.returncode != 0:
        sys.exit("Query failed: " + r.stderr.decode().strip())

    rows = []
    for line in r.stdout.decode("utf-8").splitlines():
        if line.strip():
            rows.append(line.split("\t"))
    return rows


def local_target(photo):
    """`/storage/employees/photos/x.png` -> absolute path on the public disk."""
    path = photo.strip().lstrip("/")
    if not path.startswith("storage/"):
        return None
    return os.path.join(PUBLIC_DISK, *path[len("storage/"):].split("/"))


def find_source(filename):
    """Locate the source image for a stored filename, by its numeric suffix."""
    candidates = []
    match = NUMBERED_NAME.match(filename)
    if match:
        candidates.append(match.group(1) + match.group(2))
    candidates.append(filename)

    for name in candidates:
        candidate = os.path.join(SOURCE_FOLDER, name)
        if os.path.exists(candidate):
            return candidate
    return None


def run(dry_run=False, force=False):
    if not os.path.isdir(SOURCE_FOLDER):
        sys.exit(f"No source folder at {SOURCE_FOLDER}")

    db = read_env()
    rows = query(db, "SELECT employee_id, photo FROM employees "
                     "WHERE photo IS NOT NULL AND photo <> '' ORDER BY employee_id;")

    if not rows:
        print("No employees carry a photo path.")
        return

    copied = skipped = missing = unmapped = 0

    for employee_id, photo in rows:
        target = local_target(photo)
        if target is None:
            print(f"  [SKIP] {employee_id}: photo is not on the public disk ({photo})")
            unmapped += 1
            continue

        filename = os.path.basename(target)

        if os.path.exists(target) and not force:
            print(f"  [HAVE] {employee_id}: {filename}")
            skipped += 1
            continue

        source = find_source(filename)
        if source is None:
            print(f"  [MISS] {employee_id}: no source in user_images/ for {filename}")
            missing += 1
            continue

        if dry_run:
            print(f"  [WOULD] {employee_id}: {os.path.basename(source)} -> {filename}")
            copied += 1
            continue

        os.makedirs(os.path.dirname(target), exist_ok=True)
        shutil.copy2(source, target)
        print(f"  [OK] {employee_id}: {os.path.basename(source)} -> {filename}")
        copied += 1

    print(f"\n{copied} copied, {skipped} already present, "
          f"{missing} without a source, {unmapped} off-disk.")

    link = os.path.join(LARAVEL_APP_DIR, "public", "storage")
    if not os.path.exists(link):
        print("\nWarning: public/storage symlink is missing, so these files will "
              "not be served. Run `php artisan storage:link`.")


if __name__ == "__main__":
    run(dry_run="--dry-run" in sys.argv, force="--force" in sys.argv)
