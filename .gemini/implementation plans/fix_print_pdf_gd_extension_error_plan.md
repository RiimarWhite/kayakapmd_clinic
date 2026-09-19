# Implementation Plan: Fix PHP GD Extension Error on PDF Generation (`print_pdf`)

## Problem Description
When accessing the PDF printable endpoint (`/print_pdf` or `/doctor/print_diagnostics`), Dompdf attempts to parse PNG images (`company_logo.png`, `rx_icon.png`) containing alpha/transparency channels. In `vendor/dompdf/dompdf/lib/Cpdf.php:6226`, Dompdf checks `!function_exists("imagecreatefrompng")` and throws an unhandled exception:
```
The PHP GD extension is required, but is not installed.
```
This leads to an HTTP 500 error logged at `local.ERROR`. The Apache web server serving the request runs inside Docker container `latest_php_server` (PHP 8.5.7 on Debian 13 Trixie), where the native `ext-gd` C extension is currently not compiled or enabled.

The user also asked:
> *"is there a way to install this in composer?"*

### Answering the Composer Question
1. **Can Composer install the PHP GD extension?**
   - **No.** Composer is a userland dependency manager for PHP libraries (downloading PHP source code into `vendor/`). Native PHP extensions like GD (`ext-gd`) are compiled C binaries (`gd.so` on Linux or `php_gd.dll` on Windows) that must be installed into the OS and linked into the PHP runtime itself. Composer cannot compile or install native C binaries into the system PHP runtime.
2. **What can Composer do?**
   - We can declare `"ext-gd": "*"` in the `"require"` section of `composer.json`. This turns `ext-gd` into a platform requirement. When anyone runs `composer install` or `composer check-platform-reqs`, Composer will verify that the runtime environment has the GD extension installed and fail with an explicit diagnostic message if it is missing.

---

## User Review Required
> [!IMPORTANT]
> To permanently fix the runtime error in the active web server, we will install the native PHP GD extension directly into the running Docker container `latest_php_server` (and restart Apache). No database changes or data loss will occur.

> [!NOTE]
> In addition to installing the extension, we will add defensive fallback logic in [`rx_print.blade.php`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/views/printables/rx_print.blade.php) and [`DoctorController.php`](file:///C:/docker/php_projects/kayakapmd_clinic/app/Http/Controllers/DoctorController.php) so that if GD is ever missing or encounters an image decoding failure, the PDF will still generate and stream cleanly without crashing with an HTTP 500 error.

---

## Proposed Changes

### Component 1: Docker Runtime Environment (`latest_php_server`)
Install `ext-gd` with FreeType, JPEG, and WebP support inside the Debian-based Docker container:
1. Update package lists and install development libraries:
   ```bash
   docker exec latest_php_server apt-get update
   docker exec latest_php_server apt-get install -y libfreetype-dev libjpeg62-turbo-dev libpng-dev libwebp-dev
   ```
2. Configure and compile PHP GD extension:
   ```bash
   docker exec latest_php_server docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp
   docker exec latest_php_server docker-php-ext-install -j$(nproc) gd
   ```
3. Gracefully reload Apache to activate the extension:
   ```bash
   docker exec latest_php_server apache2ctl graceful
   ```
4. Verify `php -m` outputs `gd`.

---

### Component 2: Project Dependency Specification (`composer.json`)
#### [MODIFY] [`composer.json`](file:///C:/docker/php_projects/kayakapmd_clinic/composer.json)
- Add `"ext-gd": "*"` under `"require"`.
```json
    "require": {
        "php": "^8.2",
        "ext-gd": "*",
        "barryvdh/laravel-dompdf": "^3.1",
        "laravel/framework": "^12.0",
        "laravel/tinker": "^2.10.1"
    },
```

---

### Component 3: Defensive PDF View & Controller Fallbacks
#### [MODIFY] [`resources/views/printables/rx_print.blade.php`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/views/printables/rx_print.blade.php)
- Guard PNG image tags (`company_logo.png`, `rx_icon.png`) with `@if (function_exists('imagecreatefrompng') && file_exists(...))`.
- When GD is not loaded, provide clean textual / SVG styled headers instead of attempting to parse PNGs with alpha channels that Dompdf cannot process without GD.

#### [MODIFY] [`app/Http/Controllers/DoctorController.php`](file:///C:/docker/php_projects/kayakapmd_clinic/app/Http/Controllers/DoctorController.php)
- Wrap PDF generation in `printPDF` and `printDiagnostics` with defensive `try...catch (\Throwable $e)` blocks.
- If an unexpected rendering exception occurs, log structured debug data (`consultationrefno`, `type`, `message`) and fall back to streaming a clean PDF without crashing.

---

### Component 4: Prerequisites Automation (`setup.sh`)
#### [MODIFY] [`setup.sh`](file:///C:/docker/php_projects/kayakapmd_clinic/setup.sh)
- Add Section 1 prerequisite check for PHP `gd` extension:
  ```bash
  if php -r 'exit(extension_loaded("gd") ? 0 : 1);' 2>/dev/null; then
      echo -e "  [✓] PHP GD extension is installed."
  else
      echo -e "  [!] Warning: PHP GD extension is missing. Required for PDF generation (Dompdf)."
  fi
  ```

---

### Component 5: Automated Testing
#### [NEW] [`tests/Feature/PrintPdfGenerationTest.php`](file:///C:/docker/php_projects/kayakapmd_clinic/tests/Feature/PrintPdfGenerationTest.php)
- Test `GET /print_pdf?type=rx&consultationrefno=...` returns HTTP 200 with `application/pdf` header.
- Test `GET /print_pdf?type=instructions&consultationrefno=...` returns HTTP 200 with `application/pdf` header.
- Test `GET /doctor/print_diagnostics?consultationrefno=...` returns HTTP 200 with `application/pdf` header.
- Test unauthenticated requests are redirected or denied properly.

---

## Verification Plan

### Automated Tests
1. Run new print PDF feature test:
   ```bash
   docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan test --filter=PrintPdfGenerationTest
   ```
2. Run full test suite to ensure 0 regressions:
   ```bash
   docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan test
   ```

### Manual Verification
1. Access `http://localhost/print_pdf?type=rx&consultationrefno=CON_...` from doctor or admin session in browser.
2. Confirm the prescription PDF opens, displays clinic logo, patient info, and medicines without 500 error.
3. Check `storage/logs/laravel.log` to confirm no GD errors are logged.
