# CoolClean PHP — Intern Local Setup Guide

This package contains the CoolClean Laravel source code. It does **not** contain
database credentials, Git history, installed dependencies, or private data.

CoolClean uses an existing MySQL database named `COOLCLEANDB_PHP` and existing
uppercase tables. **Do not run Laravel migrations.**

## 1. What you need

- Windows 10 or 11
- Laragon with PHP 8.3 or newer
- MySQL 8
- Composer
- A code editor such as Visual Studio Code
- The existing CoolClean database, or an authorized SQL backup from your trainer

Check the tools in a Laragon terminal:

```powershell
php -v
composer --version
mysql --version
```

## 2. Extract the source

1. Extract `coolclean-php-intern-source.zip`.
2. Place the extracted folder inside a working directory, for example:

```text
C:\laragon\www\coolclean-php
```

3. Open that folder in Visual Studio Code.

The folder containing `artisan` and `composer.json` is the Laravel project root.

## 3. Install PHP dependencies

Open a Laragon terminal in the project root and run:

```powershell
composer install
```

Composer recreates the `vendor` folder. It is intentionally not included in the
ZIP because it is generated and can be large.

## 4. Create the local environment file

Copy the example file:

```powershell
Copy-Item .env.example .env
```

Generate a unique Laravel application key:

```powershell
php artisan key:generate
```

Never share or commit `.env` because it can contain passwords.

## 5. Prepare the existing database

### Option A — Database already exists locally

Start MySQL in Laragon and confirm that `COOLCLEANDB_PHP` is available.

```sql
USE COOLCLEANDB_PHP;
SHOW TABLES;
```

Expected CoolClean tables:

```text
BOOKINGS
BOOKING_EARNINGS
BOOKING_STATUS_HISTORY
CUSTOMERS
CUSTOMER_WALLET_TRANSACTIONS
DRIVERS
EMAIL_QUEUE
LAUNDRY_LOCATIONS
PAYMENTS
SERVICES
SYSTEM_SETTINGS
USERS
```

### Option B — Restore an authorized SQL backup

The database backup is supplied separately by the trainer because it may contain
private information. Do not use a production backup unless it has been approved
and sanitized.

Create the database first:

```sql
CREATE DATABASE COOLCLEANDB_PHP
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
```

Open MySQL from a Laragon terminal:

```powershell
mysql -u root -p
```

If the local root password is blank, press Enter when MySQL requests it. Then
run these commands inside MySQL (forward slashes are easiest on Windows):

```sql
USE COOLCLEANDB_PHP;
SOURCE C:/path/to/approved-coolclean.sql;
SHOW TABLES;
```

## 6. Configure the database connection

Open `.env` and update only the values required by your local MySQL installation:

```env
APP_NAME=CoolClean
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=COOLCLEANDB_PHP
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

Leave `DB_PASSWORD` blank only when the local MySQL user genuinely has no password.

## 7. Test the database connection

Clear any old cached configuration:

```powershell
php artisan optimize:clear
```

Test that Laravel can read the existing uppercase `USERS` table:

```powershell
php artisan tinker
```

Inside Tinker:

```php
App\Models\User::count();
exit
```

A number means the connection works. An SQL error normally means the database
name, username, password, MySQL port, or table names are incorrect.

## 8. Start CoolClean

```powershell
php artisan serve
```

Open:

```text
http://127.0.0.1:8000
```

Admin login page:

```text
http://127.0.0.1:8000/login
```

Use an account supplied by the trainer. Passwords are not included in this ZIP.

## 9. Optional Mailpit setup

CoolClean can use Mailpit to display development emails without sending real mail.
The default local settings are:

```env
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
```

Mailpit web inbox normally opens at:

```text
http://127.0.0.1:8025
```

## 10. How a login request moves through the project

```text
resources/views/auth/login.blade.php
                |
                | POST /login
                v
routes/web.php
                |
                v
app/Http/Controllers/Auth/LoginController.php
                |
                v
app/Models/User.php
                |
                v
COOLCLEANDB_PHP.USERS
                |
                v
Redirect to dashboard OR return an error to the login page
```

See `docs/intern-laravel-guide.html` for the illustrated teaching guide.

## 11. Important project rules

1. Do not run `php artisan migrate`.
2. Do not create replacement tables with lowercase names.
3. Keep the existing uppercase table and column mappings in the models.
4. Do not commit `.env`, SQL backups, passwords, API keys, logs, or user data.
5. Make a database backup before changing production data.
6. Test changes locally before deployment.

## 12. Common problems

### `Access denied for user 'root'`

The MySQL username or password in `.env` is incorrect.

### `Unknown database 'COOLCLEANDB_PHP'`

The database has not been created or restored yet.

### `Table ... doesn't exist`

Confirm you selected `COOLCLEANDB_PHP` and that the existing tables are uppercase.
Do not solve this by running migrations.

### Laravel still uses old `.env` values

```powershell
php artisan optimize:clear
```

### Port 8000 is already in use

```powershell
php artisan serve --port=8001
```

Then open `http://127.0.0.1:8001`.

### CSS is missing

Run `php artisan optimize:clear`, restart the server, then press `Ctrl + F5` in
the browser.

## 13. Recommended first exercise

Ask the intern to trace one login attempt through these four files:

1. `resources/views/auth/login.blade.php`
2. `routes/web.php`
3. `app/Http/Controllers/Auth/LoginController.php`
4. `app/Models/User.php`

They should explain what data enters each layer, what validation happens, which
database column is queried, and how success or failure returns to the browser.
