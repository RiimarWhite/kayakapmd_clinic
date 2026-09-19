# Walkthrough: Fix Consultation Charges, Settlements Modal, and Unscheduled Queue Filtering

## Overview
This walkthrough outlines the comprehensive resolutions implemented for four key operational issues across the Doctor and Secretary consoles:
1. **Doctor Consultation Modal Lifecycle During Append Charges**:
   - Resolved the issue where clicking "Append Charges" in `doctor/consultation` prematurely closed `#consultation_modal` and left the screen without open modals upon saving charges.
2. **Patient Charges Synchronization with Rx & Instructions**:
   - Prescribed medications (`DRUGS AND MEDS`) under the "Rx & Instructions" tab now reliably display in the "Patient Charges" table with correct unit and total pricing, and bidirectional deletion synchronization is maintained.
3. **Secretary Queue Settlements Modal Generation & Viewing**:
   - Populated the "Generate Settlements" and "View Settlements" tabs with real-time charge dispersion, dynamic remaining balance calculation, HMO selector population, and persistent settlement storage in `pxsettlements`.
4. **New/Unscheduled Patients Filtering**:
   - Filtered out all patients who possess an active/valid scheduled consultation from the "New/Unscheduled Patients" queue.

---

## Key Changes

### 1. Unscheduled Queue Patient Filtering
**File:** [`app/Http/Controllers/ConsultationController.php`](file:///C:/docker/php_projects/kayakapmd_clinic/app/Http/Controllers/ConsultationController.php)
- Updated `fetchConsultationPatientsUnsched` to exclude any `pxrefno` that possesses an existing consultation with a non-null, valid scheduled date (excluding legacy placeholder dates `'1901-01-01 00:00:00'`, `'0000-00-00 00:00:00'`, and empty strings).
- Added `groupBy('pxrefno')` to ensure patients with multiple historical unscheduled records are listed as unique patient rows.

### 2. Prescribed Medicines (Rx) & Patient Charges Integration
**File:** [`app/Http/Controllers/DoctorController.php`](file:///C:/docker/php_projects/kayakapmd_clinic/app/Http/Controllers/DoctorController.php)
- In `fetchPatientCharges`: Removed the exclusion condition `$q->where('item_grouping', '!=', 'DRUGS AND MEDS')`, enabling all prescribed medicines and appended items to show under Patient Charges.
- Added catalog price resolution from `StocksListingModel` to ensure medicines added with zero initial prices retrieve the default catalog `price_regular` or `price_hospital`.
- In `addMedicine`: Configured automatic calculation of `cost_ave`, `retails`, and `totalamt` directly upon insertion into `stocks_ledger` with `transactiontype = 'CHARGES'`.
- In `saveAppendedCharges`: Added `px_pin` and explicit `transactiontype = 'CHARGES'`.
- In `removePatientCharge`: Maintained synchronization so removing a medication from Patient Charges simultaneously refreshes the active Rx list.

### 3. Modal Stacking & Form Lifecycle
**Files:**
- [`resources/views/modals/consultation_modal.blade.php`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/views/modals/consultation_modal.blade.php)
- [`resources/js/pages/doctor/consultation/form.js`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/js/pages/doctor/consultation/form.js)
- Removed Bootstrap `data-bs-toggle="modal"` from append charge buttons to prevent Bootstrap modal conflict from closing `#consultation_modal`.
- Adjusted `#append_charge_modal` styling with `z-index: 1060;` to render over `#consultation_modal` (which has `z-index: 1050;`).
- Programmatically managed opening `#append_charge_modal` while keeping `#consultation_modal` displayed in the background.
- Isolated the `appendedCharges` array to newly selected charge line items to prevent array contamination with pre-existing charges.
- On dismiss/close of `#append_charge_modal`, restored `modal-open` class to `document.body` and automatically reloaded `#charges_table` and `#charges_appended_table`.

### 4. Settlements Modal (Generation, Dispersion & View Tab)
**Files:**
- [`resources/views/modals/settlement_modal.blade.php`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/views/modals/settlement_modal.blade.php)
- [`resources/js/pages/secretary/queue.js`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/js/pages/secretary/queue.js)
- [`app/Http/Controllers/SecretaryController.php`](file:///C:/docker/php_projects/kayakapmd_clinic/app/Http/Controllers/SecretaryController.php)
- [`app/Models/SettlementsModel.php`](file:///C:/docker/php_projects/kayakapmd_clinic/app/Models/SettlementsModel.php)
- [`database/migrations/2026_09_19_174500_add_cta_type_to_pxsettlements_table.php`](file:///C:/docker/php_projects/kayakapmd_clinic/database/migrations/2026_09_19_174500_add_cta_type_to_pxsettlements_table.php)
- [`routes/api.php`](file:///C:/docker/php_projects/kayakapmd_clinic/routes/api.php)
- Converted `#info_*` fields in View Settlements to readonly formatted currency text fields.
- Implemented real-time dynamic dispersion calculation: entering amounts in Cash, Card (CTA), or HMO automatically recalculates the remaining balance and enforces upper limits based on the gross total charges.
- Added "Import" shortcut buttons allowing one-click filling of remaining balances into any channel.
- Added `cta_type` column to `pxsettlements` to record card subtypes (`cc` for Credit Card, `dc` for Debit Card).
- Updated `SettlementsModel` with `public $timestamps = false;`, `protected $primaryKey = 'consultationrefno';`, `$fillable` configuration, and `$appends = ['net_total', 'cash', 'cta', 'hmo']` with mutators and accessors mapping to `total_gross`, `net_payable`, `payment_cash`, `payment_card`, and `less_hmo`.
- Updated `saveSettlements` in `SecretaryController` to capture patient pincode, doctor reference, doctor name, and audit creator fields.
- Updated `fetchHmo` to query by tenant client code with fallback to all active HMOs.

### 5. Schema & Documentation Dual-Sync
**Files:**
- [`.gemini/database/kayakapmdv2_data_dictionary.md`](file:///C:/docker/php_projects/kayakapmd_clinic/.gemini/database/kayakapmdv2_data_dictionary.md)
- Updated `pxsettlements` table specification in the database data dictionary to document the new `cta_type` column.
- Verified [`setup.sh`](file:///C:/docker/php_projects/kayakapmd_clinic/setup.sh) executes migrations cleanly.

---

## Verification & Test Results

### 1. Feature Test Suite: `ConsultationChargesAndSettlementsTest`
Executed targeted feature tests verifying all critical paths:
```bash
docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan test --filter=ConsultationChargesAndSettlementsTest
```
**Output:**
```
PASS  Tests\Feature\ConsultationChargesAndSettlementsTest
✓ unscheduled patients table excludes scheduled patients               7.98s  
✓ patient charges include prescribed drugs and meds                    0.22s  
✓ settlements save fetch and hmo population                            0.18s  

Tests:    3 passed (20 assertions)
Duration: 9.75s
```

### 2. Full Application Test Suite
Executed the entire Laravel test suite across Unit and Feature test suites:
```bash
docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan test
```
**Output:**
```
PASS  Tests\Unit\ExampleTest
✓ that true is true

PASS  Tests\Feature\AuthTest
✓ unauthenticated user redirected from doctor dashboard
✓ unauthenticated user redirected from admin dashboard
✓ unauthenticated user redirected from secretary queue
✓ doctor can login via username
✓ doctor can login via email
✓ doctor can login via docrefno
✓ secretary can login via username
✓ secretary can login via lastname
✓ secretary can login via email
✓ secretary can login via id number
✓ admin can login via username
✓ invalid credentials rejected
✓ web logout invalidates session and redirects to login
✓ ajax logout returns json with redirect url
✓ prevent back history middleware sets anticache headers
✓ login page renders successfully with http 200
✓ logging configuration has ignore exceptions enabled
✓ file logging channels configure permissive mask

PASS  Tests\Feature\ConsultationChargesAndSettlementsTest
✓ unscheduled patients table excludes scheduled patients
✓ patient charges include prescribed drugs and meds
✓ settlements save fetch and hmo population

PASS  Tests\Feature\DoctorSecretaryConsoleTest
✓ doctor schedules create and fetch
✓ add doctor defaults username to lastname
✓ unified secretaries admins module
✓ add secretary and admin with default username
✓ secretary dummy doctor assignment resolution
✓ consultation save update and mark as complete
✓ admin can save and update consultation

PASS  Tests\Feature\ExampleTest
✓ the application returns a successful response

Tests:    30 passed (148 assertions)
Duration: 30.48s
```

### 3. Production Asset Compilation
Rebuilt frontend assets using Vite:
```bash
npm run build
```
Build succeeded with 0 errors, bundling `queue.js`, `form.js`, `secretaries.js`, and vendor assets.
