# CoolClean Laravel Conversion

This project is a Laravel conversion of the Spring Boot application in
`C:\Users\Shukri Mutalib\Documents\CoolCleanGit\coolclean-backend`.

## Database rule

The application uses the existing `COOLCLEANDB_PHP` database. Do not create
migrations, run migrations, create tables, or rename existing columns. Models
explicitly map Laravel to the existing table and uppercase column names.

## Request flow

1. A route receives the browser or mobile request.
2. A controller validates the input and applies business rules.
3. An Eloquent model reads or updates an existing table.
4. Web controllers return Blade pages or redirects.
5. API controllers return JSON.

## Main code

- `routes/web.php`: public and admin web routes.
- `routes/api.php`: customer and driver mobile API routes.
- `app/Http/Controllers/Auth/LoginController.php`: admin login and logout.
- `app/Http/Controllers/AdminController.php`: admin pages and operations.
- `app/Http/Controllers/Api/MobileAuthController.php`: OTP, login, registration, and password reset.
- `app/Http/Controllers/Api/MobileAppController.php`: bookings, payments, wallet, jobs, messages, and profiles.
- `app/Models`: mappings and relationships for all 12 existing tables.
- `resources/views`: public, login, and admin Blade pages.
- `app/Console/Commands/ProcessEmailQueue.php`: sends pending existing email queue records.

## Run locally

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan serve --host=0.0.0.0 --port=8000
```

Run the scheduler in a second terminal when email processing is needed:

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe artisan schedule:work
```

## Safety

Never run `migrate`, `migrate:fresh`, `migrate:refresh`, or `db:wipe` against
the existing CoolClean database.
