# Implementation Plan: Reschedule, Button Loaders, PDF Printing & Charges Fixes

## Problem Summary
1. **Queued Patients `api/reschedule_patient` 500 Error**: `pxwalkinconsultation` has no `casecode` column, causing SQL 1054 error when rescheduling.
2. **Button Click Loaders**: Need loading spinners and disabled state on all action buttons across:
   - `admin/users/secretaries-admins`
   - `admin/secretary`
   - `secretary/queue`
   - `doctor/consultation`
3. **PDF Printing (`print_pdf?type=instructions`, `/print_pdf?type=rx`, `/doctor/print_diagnostics`)**:
   - Guard mismatch (`auth:doctor,admin` blocks secretary access, or doctor user is null when accessed by admin).
   - Missing fallback when `$doctor` or `$patient` is null.
   - Rx medicines query checks `pxrxdocuments` (`DoctorMedicinesModel`) while medicines are stored in `stocks_ledger` (`StocksLedgerModel`).
   - Diagnostics query checks legacy `DocRequestsModel` while active diagnostics are stored in `stocks_ledger`.
   - Relative URL `print_diagnostics` issues.
4. **Doctor Consultation Append Charges Modal & Patient Charges Table**:
   - `stocks_ledger` rows created without `cost_ave` and `totalamt`, resulting in `null` in the modal's Total column.
   - Patient Charges table Amount column is null/empty and `total += parseFloat(charge.totalamt)` results in `NaN`.

---

## Proposed Changes

### 1. `app/Http/Controllers/ConsultationController.php`
- **Method `reschedulePatient`**:
  - Replace `where(['casecode' => ...])` with `where('consultationrefno', $request->consultationrefno)->orWhere('caseno', $request->consultationrefno)`.
  - Fallback `docrefno` to existing consultation's `docrefno` if empty.
  - Parse date and time with Carbon, calculate new queue number, set status to `WAITING`, and update record.
  - Add structured logging with `Illuminate\Support\Facades\Log`.

### 2. Button Click Loaders
- **`resources/js/pages/secretary/queue.js`**:
  - Add `setBtnLoading($btn, text)` and `resetBtnLoading($btn)` helper functions.
  - Add loading state to `#reschedule_btn`, `#add_patient_btn`, `.import-queue`, `.import-btn`, `.update-queue`, `#settlement_btn`, `#sprevious`, `#snext`.
- **`resources/js/pages/admin/users/secretaries.js`**:
  - Ensure all action buttons have explicit loading state and disabled prop.
- **`resources/js/pages/doctor/consultation.js`**:
  - Add loading state to `#consult` ("Proceed Consultation"), `#save_consul` ("Save Consultation"), `#previous`, `#next`.
- **`resources/js/pages/doctor/consultation/form.js`**:
  - Add `setBtnLoading` / `resetBtnLoading` helper.
  - Add loading states to `#save_impressions_diagnosis`, `#add_rx`, `.delete_rx`, `#save_rx_btn`, `#save_requests`, `.remove_request`, `#update_files_btn`, `#append_to_charges_btn`, `#save_charges_btn`, `.remove_charge_btn`, `.edit_charge_btn`.

### 3. PDF Printing (`routes/web.php`, `DoctorController.php`, `rx_print.blade.php`)
- **`routes/web.php`**:
  - Move `print_pdf` and `doctor/print_diagnostics` to `Route::middleware('auth:doctor,secretary,admin')`.
  - Add alias route `print_diagnostics`.
- **`app/Http/Controllers/DoctorController.php`**:
  - In `printPDF`:
    - Safely resolve `$doctor` from consultation's `docrefno` or logged-in user guard, with safe fallback object.
    - Safely resolve `$patient` and attach address from `PatientMasterlist` if not present.
    - Query medicines from both `StocksLedgerModel` (`item_grouping = 'DRUGS AND MEDS'`) and `DoctorMedicinesModel`, mapping to uniform structure `['medicinename', 'medicinedosage', 'medicineduration', 'medicinequantity']`.
    - Provide fallback for `$profile` (`KayakapProfileModel`).
  - In `printDiagnostics`:
    - Safely resolve `$doctor`, `$patient`, `$profile`.
    - Query diagnostics from `StocksLedgerModel` (`item_grouping = 'DIAGNOSTIC'`) and `DocRequestsModel`, mapping to uniform objects with `diagnostic_name`.
- **`resources/views/printables/rx_print.blade.php`**:
  - Safe null-coalescing on all properties (`$patient->patientname`, `$patient->address`, `$doctor->Licno`, `$doctor->PTR`, `$doctor->S2no`, `$doctor->docname`).
  - Render diagnostic requests safely: `{{ $request->diagnostic_name ?? $request->item_dscr ?? '' }}`.
- **`resources/js/pages/doctor/consultation/form.js` & `resources/js/pages/doctor/consultation.js`**:
  - Ensure `#print_diagnostics` href is set to `/doctor/print_diagnostics?consultationrefno=...`.

### 4. Append Charges Modal & Patient Charges Table
- **`app/Http/Controllers/DoctorController.php`**:
  - In `saveAppendedCharges`:
    - Read `$chargeData['amount']`, calculate `$unitPrice` and `$totalAmount = $unitPrice * $quantity`.
    - Persist `cost_ave`, `retails`, and `totalamt` in `StocksLedgerModel`.
  - In `fetchPatientCharges`:
    - Transform returned charges: if `totalamt` or `cost_ave` is null or zero, look up price from `StocksListingModel` and format with `number_format(..., 2, '.', '')`.
- **`resources/views/modals/consultation_modal.blade.php`**:
  - Update `#charges_table` thead columns to: Actions | Description | Quantity | Unit Price | Total Amount.
- **`resources/js/pages/doctor/consultation/form.js`**:
  - In `#append_charges_table` dynamic render: display formatted total `(parseFloat(amount || 0) * parseFloat(quantity || 1)).toFixed(2)`.
  - In `#charges_table` DataTables config:
    - Safe total calculation: `let val = parseFloat(charge.totalamt || charge.amount || 0); if (!isNaN(val)) total += val;`.
    - Column renderers with `.toFixed(2)` fallback `0.00`.

---

## Verification Plan
1. Run PHPUnit test suite: `docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan test`.
2. Compile frontend assets: `npm run build`.
3. Test `api/reschedule_patient` endpoint directly with test consultation reference number.
4. Test `print_pdf` and `doctor/print_diagnostics` rendering via tinker / test script.
5. Persist walkthrough to `.gemini/walkthroughs/reschedule_loaders_print_and_charges_fixes_walkthrough.md`.
