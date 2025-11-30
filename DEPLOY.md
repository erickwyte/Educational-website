Production deployment notes

1) Create a .env file from .env.example and fill sensitive values (do NOT commit .env):
   cp .env.example .env
   Edit .env and set DB credentials, LOG_DIR, SECURE_UPLOAD_ROOT.

2) Install Composer dependencies on the server:
   composer install --no-dev --optimize-autoloader

3) Set LOG_DIR to a secure location outside the webroot and ensure PHP can write to it.

4) Set SECURE_UPLOAD_ROOT outside the DocumentRoot and make sure the webserver/PHP process has appropriate access (read/write for upload directories, but not listable by public).

5) Optionally migrate existing uploads into SECURE_UPLOAD_ROOT and update DB entries accordingly (backup DB + files first):
   - Move files
   - Update DB file_path values to the filename only (use SUBSTRING_INDEX or equivalent)

6) Enforce HTTPS and production session/cookie settings in server configuration; ensure session.cookie_secure = 1, session.cookie_httponly = 1, and SameSite as appropriate.

7) Configure log rotation (logrotate) and monitoring/alerts.

If you'd like, I added a migration script that moves existing uploaded PDFs into the secure folder and updates DB paths. See the next section.

Migration helper
----------------
- Script: `scripts/migrate_user_pdfs.php`
- What it does: looks at rows in `user_pdfs_uploads`, attempts to find files in common legacy upload folders (e.g., `uploads/user_pdf_uploads`, `uploads/`), moves them to `SECURE_PDF_DIR`, stores a safe filename in `file_path` and keeps the original filename in `original_name`.
- Safe options:
   - Dry run (no changes): `php scripts/migrate_user_pdfs.php --dry-run`
   - Run and update DB: `php scripts/migrate_user_pdfs.php --confirm`
   - Limit rows: `php scripts/migrate_user_pdfs.php --confirm --limit=50`

IMPORTANT: Always back up your database and uploads before running the script. The script keeps a `migrate_backup` folder inside `SECURE_UPLOAD_ROOT` where copies of moved files are placed.

Generic migration
-----------------
Use `scripts/migrate_uploads.php` for other tables (notes_pdfs, questions_pdfs, profile_photos, blog images).

Example:
```bash
# dry run for notes
php scripts/migrate_uploads.php --table=notes_pdfs --file=file_path --id=id --dry-run --limit=50

# confirm for questions
php scripts/migrate_uploads.php --table=questions_pdfs --file=file_path --confirm
```
8) Run composer audit or other SCA tooling to check package vulnerabilities.

If you'd like, I can implement a migration script to move files and update the DB for you next.
