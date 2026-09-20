# Implementation Plan — Fix `/api/delete_patient_charge` 500 Error (Truncated incorrect INTEGER value: 'null')

## Goal Description
When a user attempts to delete a patient charge via `/api/delete_patient_charge`, MySQL throws a 500 Internal Server Error:
```text
PDOException(code: 22007): SQLSTATE[22007]: Invalid datetime format: 1292 Truncated incorrect INTEGER value: 'null' 
at /var/www/html/kayakapmd_clinic/vendor/laravel/framework/src/Illuminate/Database/Connection.php:609
SQL: delete from `stocks_ledger` where (`px_consultcode_cn` = 'CON...' and `id` = 'null')
```

### Root Cause Analysis
1. **Schema & Database Flaw**: The `stocks_ledger` table migration (`2026_09_19_111708_create_stocks_ledger_table.php`) defined `id` as `$table->bigInteger('id')->nullable();` without `autoIncrement()` and without a primary key. As a result, rows inserted into `stocks_ledger` (from Rx medicines, diagnostic requests, or manual charges) had `id` set to `NULL` (14 out of 15 rows currently in the database have `id = NULL`).
2. **Frontend String Evaluation Flaw**: In [`resources/js/pages/doctor/consultation/form.js`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/js/pages/doctor/consultation/form.js) (line 952), the delete button was rendered as `<button class="remove_charge_btn" value="${data.id}">`. Because `data.id` is `null`, JavaScript template string interpolation coerced it into the 4-character string `"null"`.
3. **Frontend AJAX Payload**: The click handler sent `chargeid: $btn.val()`, transmitting `{ chargeid: "null", consultationrefno: "CON..." }`.
4. **Backend SQL Execution Flaw**: In [`DoctorController::deleteCharge`](file:///C:/docker/php_projects/kayakapmd_clinic/app/Http/Controllers/DoctorController.php) (line 1181), Eloquent executed `StocksLedgerModel::where(['px_consultcode_cn' => ..., 'id' => $request->chargeid])->delete();`. MySQL in strict mode cannot compare an integer column `id` with the string literal `'null'`, throwing PDOException `1292 Truncated incorrect INTEGER value: 'null'`. Furthermore, if `chargeid` is absent, it executes `WHERE id IS NULL`, which would indiscriminately wipe all charges with NULL ids for that consultation.

---

## User Review Required
> [!IMPORTANT]
> A new database migration will be added to alter `stocks_ledger.id` into `BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY` and backfill any existing NULL `id` values with sequential integers. This adheres strictly to Rule 4 (Dual-Update Schema Synchronization Protocol) per [`rules.md`](file:///C:/docker/php_projects/kayakapmd_clinic/rules.md).

> [!NOTE]
> The backend `deleteCharge` endpoint will be enhanced to accept either `chargeid` (integer) or `prodcode` / `chargerefno` (string), and defensively filter out `'null'`, `'undefined'`, and empty strings, eliminating any possible 500 error.

---

## Proposed Changes

### Component 1: Database Migration & Schema Synchronization
#### [NEW] [`database/migrations/2026_09_20_140000_add_primary_key_autoincrement_to_stocks_ledger_table.php`](file:///C:/docker/php_projects/kayakapmd_clinic/database/migrations/2026_09_20_140000_add_primary_key_autoincrement_to_stocks_ledger_table.php)
- Sequentially populate existing `stocks_ledger` rows that have `id IS NULL`.
- In MySQL, execute `ALTER TABLE stocks_ledger MODIFY COLUMN id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)`.
- Support safe `down()` rollback.

#### [MODIFY] [`.gemini/database/kayakapmdv2_data_dictionary.md`](file:///C:/docker/php_projects/kayakapmd_clinic/.gemini/database/kayakapmdv2_data_dictionary.md)
- Update `stocks_ledger` table entry under Section 6:
  - Change `id` from `bigint | NULL` to `bigint unsigned | NO | auto_increment | Primary key (auto-incrementing ledger entry ID)`.

---

### Component 2: Eloquent Model
#### [MODIFY] [`app/Models/Stocks/StocksLedgerModel.php`](file:///C:/docker/php_projects/kayakapmd_clinic/app/Models/Stocks/StocksLedgerModel.php)
- Explicitly define:
  ```php
  protected $primaryKey = 'id';
  public $incrementing = true;
  ```
- Ensure `'id' => 'integer'` is cast in `casts()`.

---

### Component 3: Backend Controller & Route Logic
#### [MODIFY] [`app/Http/Controllers/DoctorController.php`](file:///C:/docker/php_projects/kayakapmd_clinic/app/Http/Controllers/DoctorController.php)
- Refactor `deleteCharge(Request $request)`:
  - Validate required `consultationrefno`.
  - Sanitize `$chargeId = $request->input('chargeid')`: strip non-numeric strings, `'null'`, `'undefined'`.
  - Extract fallback identifiers: `$prodcode = $request->input('prodcode')`, `$chargerefno = $request->input('chargerefno')`.
  - Build query defensively:
    - If `$chargeId`: `where('id', (int)$chargeId)`
    - Else if `$prodcode`: `where('prodcode', $prodcode)`
    - Else if `$chargerefno`: `where(function($q) { $q->where('prodcode', $chargerefno)->orWhere('phic_reference_code', $chargerefno); })`
    - Else: return `response()->json(['success' => false, 'message' => 'No valid charge identifier provided.'], 422)`.
  - Add structured logging via `Log::info('Patient charge deleted', [...])`.
  - Return JSON status and handle 404 gracefully if no row matched.

---

### Component 4: Frontend JavaScript
#### [MODIFY] [`resources/js/pages/doctor/consultation/form.js`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/js/pages/doctor/consultation/form.js)
- In `charges_table` column definition:
  - Pass both `data-id="${data.id || ''}"` and `data-prodcode="${data.prodcode || ''}"` to `.remove_charge_btn` and `.edit_charge_btn`.
- In `.remove_charge_btn` click handler:
  - Read `chargeId` and `prodcode`.
  - Clean payload: omit `chargeid` if empty or string `"null"`, and provide `prodcode`.

#### [MODIFY] [`resources/js/doctor.js`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/js/doctor.js)
- Ensure `.remove_charge_btn` handler defensively sanitizes `chargeid` and passes `prodcode` as fallback.

#### [MODIFY] [`resources/js/pages/secretary/queue.js`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/js/pages/secretary/queue.js)
- Ensure `.remove_charge` passes both `prodcode` and `chargeid` (if available).

---

## Verification Plan

### Automated Tests
1. **New Test Cases in [`tests/Feature/PatientManagementAndPrintFixesTest.php`](file:///C:/docker/php_projects/kayakapmd_clinic/tests/Feature/PatientManagementAndPrintFixesTest.php)**:
   - `test_delete_charge_by_numeric_id`: Create charge in `stocks_ledger`, verify it has an auto-incremented `id`, delete by `chargeid`, assert HTTP 200 and database missing.
   - `test_delete_charge_handles_string_null_safely`: Send `{ chargeid: 'null', prodcode: 'MED-01', consultationrefno: '...' }`, verify no 500 error, assert HTTP 200 and charge deleted by prodcode.
   - `test_delete_charge_rejects_missing_identifiers`: Send `{ consultationrefno: '...' }` with no id or prodcode, assert HTTP 422 (Unprocessable Entity).
2. **Migration Rollback & Re-apply**:
   - `docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan migrate`
   - `docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan migrate:rollback --step=1`
   - `docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan migrate`
3. **Full Test Suite Regression Check**:
   - `docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan test` (Verify all 47+ tests pass).
4. **Frontend Asset Build**:
   - `npm run build`

### Manual Verification
- In Doctor Consultation: add an Rx medicine or diagnostic charge, open Patient Charges tab, click the trash icon, and confirm the charge is deleted cleanly without any 500 error or console exception.
