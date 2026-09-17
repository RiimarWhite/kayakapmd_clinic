# Walkthrough: Fix Login Page HTTP 500 Server Error

## Executive Summary
This walkthrough documents the root-cause diagnosis, implementation, and automated/live verification for resolving the `HTTP/1.1 500 Internal Server Error` encountered on the application login page (`/kayakapmd_clinic/login` and `/login`).

---

## 1. Problem Diagnosis & Root Causes

### 1.1 Root User CLI vs. `www-data` Web Server Permission Conflict
- **Diagnosis**: Automated test suites (`php artisan test`) and migrations run inside Docker under the `root` user. When Monolog wrote logs, it created `storage/logs/laravel.log` with permissions `-rw-r--r--` (`0644`) owned by `root:root`.
- **Impact**: When incoming web requests were handled by Apache (executing PHP as `www-data`), `RequestLoggingMiddleware` attempted to log the request at `DEBUG`/`INFO`. Monolog could not open `storage/logs/laravel.log` in append mode, throwing `UnexpectedValueException: Permission denied`.

### 1.2 Recursive Exception Handler Crash
- **Diagnosis**: When Monolog threw the `UnexpectedValueException`, Laravel's exception handler caught it and called the reportable callback in `bootstrap/app.php`.
- **Impact**: The callback executed `Log::error('Unhandled exception captured', ...)`, which immediately attempted to write to the exact same unopenable log file. This created a recursive logging failure that crashed the request handler and emitted an unhandled HTTP 500 error.

### 1.3 Missing Monolog Permission Mask & `ignore_exceptions`
- **Diagnosis**: In `config/logging.php`, the `stack` driver was configured with `'ignore_exceptions' => false`, causing any logging failure to abort the entire HTTP lifecycle. Furthermore, the `single` and `daily` channels lacked a `'permission' => 0666` mask to enforce world-writable permissions regardless of which user created the file.

### 1.4 Stale Vite Development Flag (`public/hot`)
- **Diagnosis**: A leftover `public/hot` file pointing to `http://localhost:5173` caused Blade templates to generate script tags targeting the offline Vite development server instead of serving compiled production assets from `public/build/manifest.json`.

---

## 2. Changes Implemented

### 2.1 Configuration Layer
- [`config/logging.php`](file:///C:/docker/php_projects/kayakapmd_clinic/config/logging.php):
  - Added `'ignore_exceptions' => env('LOG_IGNORE_EXCEPTIONS', true)` to the `stack` channel to prevent logging errors from failing user requests.
  - Added `'permission' => 0666` to both `single` and `daily` channels so Monolog always creates log files with full read/write access for all processes.

### 2.2 Application Bootstrap Layer
- [`bootstrap/app.php`](file:///C:/docker/php_projects/kayakapmd_clinic/bootstrap/app.php):
  - Injected an anti-recursion guard in `$exceptions->reportable()` that checks for `UnexpectedValueException` from unopenable log streams and returns `false` to prevent recursive crashes.

### 2.3 Asset Management & Setup Script
- **Asset Cleanliness**: Deleted stale `public/hot` development artifact so views load compiled assets from `public/build/assets/`.
- [`setup.sh`](file:///C:/docker/php_projects/kayakapmd_clinic/setup.sh):
  - Added an automated directory permission step (`chmod -R 777 storage bootstrap/cache`) to guarantee proper permissions across both CLI and web server execution.

### 2.4 Automated Testing Layer
- [`tests/Feature/AuthTest.php`](file:///C:/docker/php_projects/kayakapmd_clinic/tests/Feature/AuthTest.php):
  - Added `test_login_page_renders_successfully_with_http_200`: validates HTTP 200 status, form inputs, and CSRF tokens.
  - Added `test_logging_configuration_has_ignore_exceptions_enabled`: validates that the stack logger ignores exceptions.
  - Added `test_file_logging_channels_configure_permissive_mask`: validates that file channels enforce the `0666` mask.

---

## 3. Verification & Validation Results

### 3.1 Automated Tests (`php artisan test`)
All 20 tests pass with 64 assertions:
```
PASS  Tests\Unit\ExampleTest
✓ that true is true                                                    0.49s  

PASS  Tests\Feature\AuthTest
✓ unauthenticated user redirected from doctor dashboard                9.57s  
✓ unauthenticated user redirected from admin dashboard                 0.23s  
✓ unauthenticated user redirected from secretary queue                 0.20s  
✓ doctor can login via username                                        1.72s  
✓ doctor can login via email                                           0.17s  
✓ doctor can login via docrefno                                        0.29s  
✓ secretary can login via username                                     0.65s  
✓ secretary can login via lastname                                     0.40s  
✓ secretary can login via email                                        0.40s  
✓ secretary can login via id number                                    0.41s  
✓ admin can login via username                                         0.40s  
✓ invalid credentials rejected                                         0.39s  
✓ web logout invalidates session and redirects to login                0.40s  
✓ ajax logout returns json with redirect url                           0.56s  
✓ prevent back history middleware sets anticache headers               0.43s  
✓ login page renders successfully with http 200                        0.41s  
✓ logging configuration has ignore exceptions enabled                  0.15s  
✓ file logging channels configure permissive mask                      0.16s  

PASS  Tests\Feature\ExampleTest
✓ the application returns a successful response                        0.21s  

Tests:    20 passed (64 assertions)
Duration: 22.74s
```

### 3.2 Live Web Server Verification
1. **HTTP Status & Headers**:
   ```
   curl.exe -s -D - http://localhost:10000/kayakapmd_clinic/login
   ```
   Output:
   ```
   HTTP/1.1 200 OK
   Server: Apache/2.4.67 (Debian)
   X-Powered-By: PHP/8.5.7
   Cache-Control: max-age=0, must-revalidate, no-cache, no-store, private
   Pragma: no-cache
   Content-Type: text/html; charset=utf-8
   ```

2. **Compiled Asset Rendering**:
   HTML response confirms that compiled production assets are loaded from `build/assets/` rather than failing to reach `localhost:5173`:
   ```html
   <link rel="preload" as="style" href="http://localhost:10000/kayakapmd_clinic/build/assets/vendor-20mIKp7c.css" />
   <link rel="preload" as="style" href="http://localhost:10000/kayakapmd_clinic/build/assets/app-DBo_cllb.css" />
   <script type="module" src="http://localhost:10000/kayakapmd_clinic/build/assets/app-CKqlvkLz.js"></script>
   ```

3. **Log Persistence**:
   `storage/logs/laravel.log` shows clean structured log entry without errors:
   ```
   [2026-09-18 00:37:15] local.DEBUG: HTTP GET processed {"method":"GET","path":"login","status":200,"duration_ms":604.66,"ip":"172.18.0.1","guard":null,"user_id":null}
   ```
