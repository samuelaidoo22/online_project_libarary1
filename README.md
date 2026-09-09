# GCTU Online Project Library

## Overview

This repository is a PHP-based digital library for Ghana Communication Technology University student projects, dissertations, and research papers.

## Architecture

- Core entrypoints use the shared bootstrap and layout helpers; the admin dashboard uses the lightweight controller under `app/`.
- Shared functionality lives in `includes/bootstrap.php`, `includes/functions.php`, `includes/auth.php`, and `includes/csrf.php`.
- `app/Controllers/AdminController.php` contains the admin review workflow.
- Layout components remain in `includes/header.php` and `includes/footer.php`; browse and upload each have one active root entrypoint.

## Recent Fixes

- Admin login redirect and approval flow now use root-relative paths, avoiding nested `/admin/admin/...` loops.
- PDF upload validation now degrades gracefully when the `fileinfo` extension is unavailable:
  - uses `finfo_open()` when present
  - falls back to `mime_content_type()` when available
  - inspects the first bytes of the file if needed
- Uploads are stored under `storage/uploads` and served only through secure download logic.
- Admin dashboard approve/reject actions now redirect correctly to `/admin/dashboard.php`.

## Security Improvements

- Secure session cookie settings:
  - `session.use_strict_mode = 1`
  - `httponly` and `SameSite=Lax`
  - `secure` when HTTPS is available
- Form CSRF protection through `includes/csrf.php`.
- Upload validation includes extension and MIME checks.
- File downloads are served only through the allowed upload directory and controlled download flow.
- Admin access is protected by role-based checks using shared auth helpers.
- Basic HTTP security headers are sent on every request.
- Uploaded files are kept outside the web root in `storage/uploads/`.

## Setup

1. Copy `config/env.php` and update database credentials if needed.
2. Ensure `storage/uploads/` is writable by the web server and exists.
3. Import the schema from `config/schema.sql` and seed data if desired.
4. Start your PHP server in the project root.
5. Open the application in your browser.

## Usage

- Register or log in as a user before uploading a PDF.
- Approved projects are visible in `browse.php`, `search.php`, and project detail pages.
- Admin users may sign in and review pending uploads from `/admin/dashboard.php`.
- Students receive in-app notifications when an administrator approves or rejects a submission. SMTP email delivery can be enabled with the variables in `.env.example`; set them in the server environment before starting PHP.
- Run `php migrate.php` after deployment to apply pending database migrations. Run `php retry_notifications.php` from a scheduled task to retry failed email deliveries, up to three attempts.
- Install dependencies with `composer install` to enable PHPMailer email delivery.
- Use a secure, environment-specific administrator account for deployment. Do not use development seed credentials in production.

## Notes

- `upload.php` and `download.php` are protected by authentication.
- The root browse, upload, project, search, and authentication entrypoints are the active application pages.
- `storage/uploads/.htaccess` is included to block direct access on Apache.
