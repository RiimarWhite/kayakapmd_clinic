# Implementation Plan: Fix Login Page HTTP 500 Server Error

## Goal Description
When accessing the application login page (`/kayakapmd_clinic/login` or `/login`) via the Apache web server, the application returns `HTTP/1.1 500 Internal Server Error`. The goal is to resolve all underlying causes of this 500 error, ensure logging never crashes the application, prevent permission conflicts between Docker CLI commands (running as `root`) and the Apache web server (running as `www-data`), remove dev artifacts (`public/hot`) that prevent asset rendering, and verify end-to-end access with automated tests.

---

## Root Cause Analysis

### 1. File Permission Conflict on `storage/logs/laravel.log`
- **Mechanism**: When artisan CLI commands (`php artisan migrate`, `php artisan test`, etc.) run inside Docker, they execute under the `root` user, creating or updating `storage/logs/laravel.log` with permissions `-rw-r--r--` (`0644`).
- **Failure**: When web traffic arrives at Apache, PHP runs under the `www-data` user. `RequestLoggingMiddleware` executes on the incoming `GET /login` request and attempts to append to `storage/logs/laravel.log`. Monolog throws:
  `UnexpectedValueException: The stream or file "/var/www/html/kayakapmd_clinic/storage/logs/laravel.log" could not be opened in append mode: Failed to open stream: Permission denied`.

### 2. Recursive Exception Loop in Exception Handler
- **Mechanism**: In `bootstrap/app.php`, unhandled exceptions are captured by `$exceptions->reportable(function (\Throwable $e) { ... Log::error(...); })`.
- **Failure**: When Monolog throws `UnexpectedValueException` due to an unwritable log stream, the exception handler catches it and calls `Log::error()`. This attempts to write to the exact same unwritable log stream, creating a recursive failure that completely halts request execution and emits an unhandled `500 Server Error`.

### 3. Missing `ignore_exceptions` and Permission Mask in `config/logging.php`
- Laravel's default `stack` driver specifies `'ignore_exceptions' => false`. Any logging failure (e.g. disk issues, unwritable streams) crashes the entire user-facing web request.
- The `single` and `daily` channels in `config/logging.php` do not set `'permission' => 0666`, meaning newly created log files default to the umask of whichever process (CLI `root` vs web `www-data`) triggered creation first.

### 4. Lingering Vite Development Flag (`public/hot`)
- The file `public/hot` containing `http://localhost:5173` is present in the repository. When Vite's development server is not running, `@vite(...)` attempts to load hot-reloading scripts from port 5173 instead of the compiled production assets in `public/build/`.

---

## User Review Required
> [!IMPORTANT]
> - `storage/` and `bootstrap/cache/` directories will be set to `777` permissions inside the container to allow both Docker CLI (`root`) and Apache (`www-data`) full read-write execution.
> - The `stack` logging driver will have `ignore_exceptions => true` so that any unexpected logging failure will never crash critical medical consultation workflows.

---

## Proposed Changes

### Configuration & Bootstrap Layer

#### [MODIFY] [config/logging.php](file:///C:/docker/php_projects/kayakapmd_clinic/config/logging.php)
- Set `'ignore_exceptions' => env('LOG_IGNORE_EXCEPTIONS', true)` in the `stack` channel.
- Add `'permission' => 0666` to the `single` and `daily` channels so Monolog always creates log files with full read/write permissions for all processes.

```php
        'stack' => [
            'driver' => 'stack',
            'channels' => explode(',', (string) env('LOG_STACK', 'single')),
            'ignore_exceptions' => env('LOG_IGNORE_EXCEPTIONS', true),
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
            'permission' => 0666,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
            'permission' => 0666,
        ],
```

#### [MODIFY] [bootstrap/app.php](file:///C:/docker/php_projects/kayakapmd_clinic/bootstrap/app.php)
- Add a safety guard inside `$exceptions->reportable()` to immediately exit without invoking `Log::error()` if the exception is an unopenable log stream error (`UnexpectedValueException` referencing `storage/logs`).

```php
        $exceptions->reportable(function (\Throwable $e) {
            // Detailed Comment: Guard against recursive crash if Monolog itself threw an unopenable stream exception
            if ($e instanceof \UnexpectedValueException && str_contains($e->getMessage(), 'storage/logs')) {
                return false;
            }

            $request = request();
            \Illuminate\Support\Facades\Log::error('Unhandled exception captured', [ ... ]);
        });
```

---

### Environment & Asset Layer

#### [DELETE] `public/hot`
- Delete `public/hot` so Blade templates cleanly serve compiled assets from `public/build/manifest.json`.

#### [MODIFY] [setup.sh](file:///C:/docker/php_projects/kayakapmd_clinic/setup.sh)
- Add an automated directory and file permission step ensuring `storage` and `bootstrap/cache` are writable (`chmod -R 777 storage bootstrap/cache`) during environment setup.

```bash
# Detailed Comment: Ensure storage and cache directories are fully writable across both CLI and Apache web server users
chmod -R 777 storage bootstrap/cache 2>/dev/null || true
```

---

### Automated Tests Layer

#### [MODIFY] [tests/Feature/AuthTest.php](file:///C:/docker/php_projects/kayakapmd_clinic/tests/Feature/AuthTest.php)
- Add a test verifying that `GET /login` returns `HTTP 200 OK` with anti-cache headers and without logger exceptions.
- Add a test verifying that logging channel `ignore_exceptions` setting prevents simulated log stream failures from returning `HTTP 500`.

---

## Verification Plan

### Automated Tests
1. Run the full test suite in docker:
   ```bash
   docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan test
   ```
   Expected: All tests pass with 0 failures.

### Manual Verification
1. Send HTTP request via `curl.exe` to the live Apache endpoint:
   ```powershell
   curl.exe -s -D - http://localhost:10000/kayakapmd_clinic/login
   ```
   Expected: `HTTP/1.1 200 OK` with complete HTML login form, CSRF token, and anti-cache headers.
2. Verify log persistence:
   ```powershell
   docker exec -w /var/www/html/kayakapmd_clinic latest_php_server tail -n 5 storage/logs/laravel.log
   ```
   Expected: `[local.DEBUG] HTTP GET processed {"method":"GET","path":"login","status":200,...}`.
3. Test unopenable log stream resilience:
   Simulate an unwritable log file and confirm that `GET /login` continues serving `200 OK` without throwing 500.
