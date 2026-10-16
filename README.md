# GymBear

A PHP and MySQL fitness club site with account registration, sign-in, membership plans, and a contact form. The interface is primarily in Romanian.

## What it does

- Registers accounts and signs users in with PHP sessions.
- Displays membership plans and records an active plan and expiry date on the account.
- Provides an account page and administration page.
- Sends messages from the contact form through PHPMailer and SMTP.

## Stack and layout

PHP, MySQL (mysqli), HTML/CSS, and PHPMailer. `pages/` contains page controllers and templates, `functions/` contains membership and email actions, `includes/` contains shared markup, `style/` contains CSS, and `resources/images/` contains assets. `db.php` creates the database connection.

## Local setup

1. Install PHP with the mysqli extension, MySQL, and Composer.
2. From the repository root, run `composer install` using the `composer.json` manifest, which pins the PHPMailer version found in the tracked vendor files. `vendor/` is still tracked in the existing repository until its removal is approved.
3. Create a MySQL database and the tables expected by the PHP code. **No SQL schema or migration is included in this repository**, so a fresh database cannot currently be reconstructed from the repository alone.
4. Fill in your local MySQL connection settings in `db.php`. Its existing values are empty; never commit live credentials.
5. Set `SMTP_USER` and `SMTP_PASSWORD` in the PHP process environment before using the contact form. `SMTP_HOST` defaults to `smtp.gmail.com`.
6. Serve the repository root with a PHP-capable web server, for example `php -S localhost:8000`, then open `http://localhost:8000/`. Routes that use absolute asset paths assume the repository is at the web root.

## Data and security notes

The code references `accounts` and `abonamente` tables. Registration uses `password_hash`; login uses `password_verify`. The membership page updates `subscription_name` and `subscription_end` on `accounts`; it is a demo flow, not a payment integration. Some membership changes use GET requests. There is no automated test suite in the repository.

**Credential incident:** a live-looking SMTP password was previously committed to `functions/send_email.php`. Removing it from the latest source does not revoke it or erase Git history. Revoke the old app password with the mail provider, issue a new one, configure it outside Git, and review repository history before public promotion. The SMTP configuration fails email sending when SMTP environment variables are absent.

## Next steps

Add a versioned database schema, add a Composer lockfile, move database configuration into environment variables, make membership changes POST-only with CSRF protection, and add integration tests.
