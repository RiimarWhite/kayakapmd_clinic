# Walkthrough — Fix `/api/delete_patient_charge` 500 Error (Truncated incorrect INTEGER value: 'null')

## Overview
This walkthrough documents the investigation, root cause diagnosis, architectural resolution, and full test suite verification of the 500 Internal Server Error encountered on `/api/delete_patient_charge`:
```text
PDOException(code: 22007): SQLSTATE[22007]: Invalid datetime format: 1292 Truncated incorrect INTEGER value: 'null' 
at /var/www/html/kayakapmd_clinic/vendor/laravel/framework/src/Illuminate/Database/Connection.php:609
SQL: delete from `stocks_ledger` where (`px_consultcode_cn` = 'CON...' and `id` = 'null')
```

---

## Root Causes Identified & Resolved

1. **Missing Auto-Increment Primary Key on `stocks_ledger`**:
   - The initial migration defined `id` as `$table->bigInteger('id')->nullable();` without `autoIncrement()` and without a primary key.
   - Consequently, records inserted via Eloquent `StocksLedgerModel::create(...)` (from prescribed medicines, lab/diagnostic requests, or manual charges) had `id` set to `NULL` (14 out of 15 records in the live MySQL database had `id = NULL`).
   - **Resolution**:
     - Created migration [`database/migrations/2026_09_20_140000_add_primary_key_autoincrement_to_stocks_ledger_table.php`](file:///C:/docker/php_projects/kayakapmd_clinic/database/migrations/2026_09_20_140000_add_primary_key_autoincrement_to_stocks_ledger_table.php) to backfill existing NULL records with sequential integers and modify `id` to `BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY`.
     - Updated baseline migration [`database/migrations/2026_09_19_111708_create_stocks_ledger_table.php`](file:///C:/docker/php_projects/kayakapmd_clinic/database/migrations/2026_09_19_111708_create_stocks_ledger_table.php) with `$table->bigIncrements('id')` for new database environments (e.g. SQLite test runners).
     - Updated [`.gemini/database/kayakapmdv2_data_dictionary.md`](file:///C:/docker/php_projects/kayakapmd_clinic/.gemini/database/kayakapmdv2_data_dictionary.md) per Rule 4.

2. **Frontend String Interpolation of `null`**:
   - In [`resources/js/pages/doctor/consultation/form.js`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/js/pages/doctor/consultation/form.js) and [`resources/js/doctor.js`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/js/doctor.js), `<button class="remove_charge_btn" value="${data.id}">` evaluated `null` to the string `"null"`.
   - On button click, jQuery transmitted `{ chargeid: "null", consultationrefno: ... }`.
   - **Resolution**:
     - Updated table column render functions to store `data-id="${data.id || ''}"` and `data-prodcode="${data.prodcode || ''}"`.
     - In the click handlers, sanitized `chargeid` against `"null"`, `"undefined"`, and empty strings, sending both `chargeid` and `prodcode` fallback.

3. **Brittle Eloquent Query in `deleteCharge`**:
   - `DoctorController::deleteCharge` blindly ran `StocksLedgerModel::where(['px_consultcode_cn' => ..., 'id' => $request->chargeid])->delete();`.
   - When `$request->chargeid` was string `"null"`, MySQL in strict mode threw PDOException 22007 (500 error).
   - When `$request->chargeid` was absent or null, it executed `WHERE id IS NULL`, which would delete all charges with null id for that consultation.
   - **Resolution**:
     - Validated `consultationrefno`.
     - Sanitized `chargeid` against non-numeric values, `'null'`, and `'undefined'`.
     - Supported multi-identifier fallback: numeric `id`, `prodcode`, or `chargerefno`.
     - Returns HTTP 422 with a clean error message if no valid identifier is supplied (no 500 error).
     - Returns HTTP 404 if the charge was already removed or not found.
     - Added structured logging on deletion attempts.

---

## Key Modified Files

- [`database/migrations/2026_09_20_140000_add_primary_key_autoincrement_to_stocks_ledger_table.php`](file:///C:/docker/php_projects/kayakapmd_clinic/database/migrations/2026_09_20_140000_add_primary_key_autoincrement_to_stocks_ledger_table.php): Adds primary key and auto-increment with backfill.
- [`database/migrations/2026_09_19_111708_create_stocks_ledger_table.php`](file:///C:/docker/php_projects/kayakapmd_clinic/database/migrations/2026_09_19_111708_create_stocks_ledger_table.php): Uses `bigIncrements('id')`.
- [`.gemini/database/kayakapmdv2_data_dictionary.md`](file:///C:/docker/php_projects/kayakapmd_clinic/.gemini/database/kayakapmdv2_data_dictionary.md): Dual-update sync for `stocks_ledger.id`.
- [`app/Models/Stocks/StocksLedgerModel.php`](file:///C:/docker/php_projects/kayakapmd_clinic/app/Models/Stocks/StocksLedgerModel.php): Defined `protected $primaryKey = 'id'` and `public $incrementing = true`.
- [`app/Http/Controllers/DoctorController.php`](file:///C:/docker/php_projects/kayakapmd_clinic/app/Http/Controllers/DoctorController.php): Refactored `deleteCharge` with defensive sanitization and multi-identifier fallback.
- [`resources/js/pages/doctor/consultation/form.js`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/js/pages/doctor/consultation/form.js): Data attributes, sanitized values, and dual identifier payload.
- [`resources/js/doctor.js`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/js/doctor.js): Data attributes, sanitized values, and dual identifier payload.
- [`resources/js/pages/secretary/queue.js`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/js/pages/secretary/queue.js): Data-id attribute and payload synchronization.
- [`tests/Feature/PatientManagementAndPrintFixesTest.php`](file:///C:/docker/php_projects/kayakapmd_clinic/tests/Feature/PatientManagementAndPrintFixesTest.php): Added feature test cases for numeric ID deletion, string 'null' safe handling, and missing identifier rejection.

---

## Verification Results

### 1. Database Migration & Rollback Test
```bash
docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan migrate
docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan migrate:rollback --step=1
docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan migrate
```
**Result**: Up (176ms), rollback (150ms), and re-apply (151ms) all executed successfully with zero errors.

### 2. Dedicated Feature Tests
```bash
docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan test tests/Feature/PatientManagementAndPrintFixesTest.php
```
**Result**: `10 passed (47 assertions)`

### 3. Full Regression Test Suite
```bash
docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan test
```
**Result**: `50 passed, 0 failed (285 assertions)` across:
- `Tests\Unit\ExampleTest` (1 test)
- `Tests\Feature\AuthTest` (18 tests)
- `Tests\Feature\ConsultationChargesAndSettlementsTest` (3 tests)
- `Tests\Feature\DoctorSecretaryConsoleTest` (12 tests)
- `Tests\Feature\ExampleTest` (1 test)
- `Tests\Feature\OpdConsultationWorkflowTest` (5 tests)
- `Tests\Feature\PatientManagementAndPrintFixesTest` (10 tests)

### 4. Frontend Asset Build
```bash
npm run build
```
**Result**: Vite production build succeeded in `3.20s`.
