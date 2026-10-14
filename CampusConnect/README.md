# CampusConnect

A web-based capstone/thesis project management system for schools. Students form
groups, submit proposals, get matched with advisers, track project progress,
request consultations, upload submissions, and receive evaluations — all in one
place. Advisers, program coordinators, and system admins each get a dedicated
workspace scoped to their role.

## Roles

| Role        | Can do |
|-------------|--------|
| Student     | Create/join a group, submit proposals, manage a project, request consultations, upload submissions, view evaluations & feedback |
| Adviser     | Review proposals, track advisee progress, set milestones, give feedback, record evaluations, manage consultations |
| Coordinator | Assign advisers to groups, review proposals, post announcements, monitor the whole program, generate reports |
| Admin       | Manage user accounts & roles, system settings, activity logs, database backups |

## Requirements

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- A web server (Apache/Nginx) or `php -S` for local development

## Setup

1. **Create the database**
   ```bash
   mysql -u root -p -e "CREATE DATABASE campusconnect"
   mysql -u root -p campusconnect < database/campusconnect.sql
   ```

2. **(Optional) Load seed/demo data**

   First regenerate a real bcrypt hash for the demo password, since the
   placeholder hash shipped in `seed_data.sql` is not valid:
   ```bash
   php -r "echo password_hash('Password123', PASSWORD_DEFAULT), PHP_EOL;"
   ```
   Paste the result into the `@pw` variable at the top of
   `database/seed_data.sql`, then import:
   ```bash
   mysql -u root -p campusconnect < database/seed_data.sql
   ```
   Demo accounts (all use the password you hashed above):
   - `admin@campusconnect.edu` — Admin
   - `coordinator@campusconnect.edu` — Coordinator
   - `adviser@campusconnect.edu` — Adviser
   - `anna.reyes@campusconnect.edu` — Student (group leader)
   - `mark.lopez@campusconnect.edu` — Student (group member)

3. **Configure the database connection**

   Edit `config/database.php` and set `DB_HOST`, `DB_NAME`, `DB_USER`,
   `DB_PASS` to match your environment.

4. **Set `BASE_URL` if deployed in a subfolder**

   In `config/constants.php`, set `BASE_URL` to your app's path, e.g.
   `define('BASE_URL', '/CampusConnect');` if served from
   `http://localhost/CampusConnect`.

5. **Make `uploads/` writable**
   ```bash
   chmod -R 775 uploads/
   ```

6. **Run it**
   ```bash
   php -S localhost:8000
   ```
   Then visit `http://localhost:8000/login.php`.

## Folder Structure

```
CampusConnect/
├── index.php, login.php, logout.php, register.php, dashboard.php   # entry points
├── config/            # DB connection, session bootstrap, constants
├── includes/          # shared header/footer/navbar/sidebar/functions/auth_check
├── assets/            # css, js, images, fonts
├── auth/              # login/register/password-reset processing scripts
├── student/           # student-facing modules (groups, proposals, projects, ...)
├── adviser/            # adviser-facing pages
├── coordinator/        # program-wide oversight pages
├── admin/               # user/role/system management
├── uploads/            # user-uploaded files (proposals, submissions, photos, ...)
├── reports/             # printable program reports
├── api/                  # lightweight JSON endpoints (notifications, projects, ...)
└── database/             # SQL schema, seed data, and generated backups
```

## Security Notes

- Passwords are hashed with `password_hash()` / verified with `password_verify()`.
- All forms are protected with CSRF tokens (`includes/functions.php::csrfField()` /
  `verifyCsrf()`).
- All database queries use PDO prepared statements.
- Sessions use `httponly`, `SameSite=Lax` cookies and a 30-minute idle timeout.
- Set `APP_DEBUG` to `false` in `config/constants.php` before deploying to
  production, and enable HTTPS so the `secure` session cookie flag applies.

## Notes on Scope

This is a functional reference implementation intended as a solid starting
point: the core workflows (auth, groups, proposals, projects, consultations,
submissions, evaluations, feedback, announcements, reporting, admin) are fully
wired to the database end-to-end. Before production use you should add:
server-side pagination on large tables, email delivery for notifications and
password resets (currently logged via `error_log()` as a placeholder), file
type/virus scanning on uploads, and rate limiting on the auth endpoints.
