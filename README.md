# Public Service Request System

A PHP 8+ / MySQL application for citizens to report public-service issues and for administrators to track and resolve them.

## Features

- Citizen registration, login, logout, and role-based access
- Submit requests with category, address, map pin, description, and optional image
- Unique request reference numbers
- Citizen dashboard with request history and status tracking
- Admin dashboard with counts, category breakdown, filtering, and request management
- Status-change history and administrator notes
- Server-side validation, CSRF protection, password hashing, prepared SQL statements, secure session settings, and upload validation
- Responsive navy, white, and teal interface

## Requirements

- PHP 8.1+ (8.2 recommended)
- MySQL 8.0+ or MariaDB 10.5+
- Apache/Nginx or PHP's built-in development server
- PHP extensions: `pdo_mysql`, `fileinfo`
- A writable `uploads/requests/` directory

## Setup (local development)

1. Install PHP and MySQL, then create a database and database user.
2. Import the schema:

   ```bash
   mysql -u root -p < database/schema.sql
   ```

3. Copy `config/config.example.php` to `config/config.php` and update the database settings.
4. Ensure the `uploads/requests/` directory is writable by the PHP process.
5. From the project root, start the PHP development server:

   ```bash
   php -S 127.0.0.1:8000
   ```

6. Visit `http://127.0.0.1:8000/public/`.

   For Apache, configure the document root to the project's `public/` directory. Do not expose `config/`, `database/`, or other project files as public web content.

## Create an administrator

For safety, public registration always creates a `citizen` account. Register normally, then promote that account using a database administrator account:

```sql
UPDATE users SET role = 'admin' WHERE email = 'admin@example.com';
```

Sign out and sign back in to load the new role. Never expose database credentials or allow public users to choose their role.

## Configuration

`config/config.example.php` documents the required values. Copy it to `config/config.php`. Do not commit `config/config.php` or real credentials.

- `APP_BASE_URL`: base path for the public application. Use `''` when the app is served from the domain root, or `/public` for the PHP development server command above.
- `UPLOAD_MAX_BYTES`: maximum image size (default 3 MB).
- `APP_ENV`: set to `production` on a live server. Configure PHP/server error logging and HTTPS in production.

## Project structure

```text
public-service-request-system/
├── config/
│   ├── config.example.php
│   └── bootstrap.php
├── database/schema.sql
├── includes/
│   ├── auth.php
│   ├── functions.php
│   ├── header.php
│   └── footer.php
├── public/
│   ├── uploads/requests/
│   ├── assets/css/styles.css
│   ├── assets/js/app.js
│   ├── index.php
│   ├── register.php
│   ├── login.php
│   ├── logout.php
│   ├── dashboard.php
│   ├── request-new.php
│   ├── request-view.php
│   ├── admin.php
│   └── admin-request.php

```

## Security notes

- Use HTTPS in production.
- Keep PHP, MySQL, and server packages updated.
- Set `display_errors=Off` in production and log errors securely.
- Back up the database and uploaded files.
- Configure web-server rules so only `public/` is served.
- The included upload checks validate extension, MIME type, size, and image decoding. For a high-risk public deployment, additionally scan uploads and serve them from a non-executable storage location.
- This is a starter project; review it against your institution's deployment/security requirements before public use.

## Main workflow

1. Citizen registers and logs in.
2. Citizen submits a service request.
3. The system assigns a reference number such as `PSR-2026-ABC12345`.
4. Citizen follows status from Pending → In Progress → Resolved.
5. Admin reviews requests, updates status, and adds a note.
6. The status history is recorded and shown on the request detail page.

## Suggested test checklist

- Register with valid details; try a duplicate email.
- Log in with a wrong password and verify access is denied.
- Submit valid and invalid requests; test a valid image and a non-image upload.
- Verify a citizen cannot open another citizen's request.
- Verify a citizen cannot access admin pages.
- Promote a test account to admin and test filtering/status changes.
- Confirm analytics counts change after status updates.
