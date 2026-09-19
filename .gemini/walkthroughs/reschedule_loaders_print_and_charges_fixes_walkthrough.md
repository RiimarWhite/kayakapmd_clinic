# Walkthrough — Fix Queued Patients Reschedule 500, Button Loaders, PDF Printing, and Consultation Charges

This walkthrough details the verification and architectural changes across the Secretary and Doctor consoles resolving four major functional areas:
1. Reschedule patient error 500 (`/api/reschedule_patient`).
2. Interactive button loaders across `admin/users/secretaries-admins`, `admin/secretary`, `secretary/queue`, and `doctor/consultation`.
3. Printable PDF routes for Rx prescriptions, patient instructions, and diagnostics (`/print_pdf?type=instructions`, `/print_pdf?type=rx`, and `/doctor/print_diagnostics`).
4. Doctor consultation charges fixes: eliminating `null` in the Append Charges modal total column and resolving `NaN` on the Patient Charges table.

---

## Changes Made

### 1. Fixed `api/reschedule_patient` Error 500
- **File**: [`app/Http/Controllers/ConsultationController.php`](file:///C:/docker/php_projects/kayakapmd_clinic/app/Http/Controllers/ConsultationController.php)
  - **Issue**: `reschedulePatient` attempted to query `ConsultationModel::where('casecode', $request->consultationrefno)`, which failed because `casecode` does not exist on `pxwalkinconsultation`.
  - **Fix**: Updated query to search by `consultationrefno` and `caseno`:
    ```php
    $consultation = ConsultationModel::where('consultationrefno', $request->consultationrefno)
        ->orWhere('caseno', $request->consultationrefno)
        ->first();
    ```
  - Formatted schedule dates and times using `Carbon::parse(...)`, calculated the target queue number for the new schedule slot, set queue status back to `'WAITING'`, and added structured `Log::info`/`Log::error` logging.

---

### 2. Multi-Guard Access & Data Aggregation for PDF Printing
- **Files**:
  - [`routes/web.php`](file:///C:/docker/php_projects/kayakapmd_clinic/routes/web.php)
  - [`app/Http/Controllers/DoctorController.php`](file:///C:/docker/php_projects/kayakapmd_clinic/app/Http/Controllers/DoctorController.php)
  - [`resources/views/printables/rx_print.blade.php`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/views/printables/rx_print.blade.php)
- **Fixes**:
  - Moved printable endpoints into the shared `auth:secretary,doctor,admin` middleware group:
    - `/print_pdf`
    - `/doctor/print_diagnostics`
    - `/print_diagnostics`
  - In `DoctorController::printPDF`:
    - Resolved the attending doctor defensively across `doctor`, `secretary`, and `admin` guards. When accessed by non-doctor roles, the doctor is looked up using `docrefno` from the consultation record.
    - Aggregated medicines from `stocks_ledger` (`item_grouping = 'DRUGS AND MEDS'`) and `DoctorMedicinesModel` to guarantee prescriptions are populated regardless of storage generation.
    - Added fallback for patient address by querying `PatientMasterlist` when `pxwalkinconsultation` has an empty address.
  - In `DoctorController::printDiagnostics`:
    - Safely resolved physician, patient, and clinic/hospital profile (`KayakapProfileModel`).
    - Aggregated diagnostic items from `stocks_ledger` (`item_grouping = 'DIAGNOSTIC'`) and legacy `DocRequestsModel`.
  - In `DoctorController::getHmoPrice`:
    - Fixed fatal error where `->first()->value('hmocode')` was called on a model instance; added fallback to `price_regular`.
  - In `resources/views/printables/rx_print.blade.php`:
    - Added defensive null-coalescing (`??`) for all patient, doctor, and clinic profile attributes to prevent fatal rendering exceptions.

---

### 3. Fixed Append Charges Modal & Patient Charges Table (`null` & `NaN`)
- **Files**:
  - [`app/Http/Controllers/DoctorController.php`](file:///C:/docker/php_projects/kayakapmd_clinic/app/Http/Controllers/DoctorController.php)
  - [`resources/views/modals/consultation_modal.blade.php`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/views/modals/consultation_modal.blade.php)
  - [`resources/js/pages/doctor/consultation/form.js`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/js/pages/doctor/consultation/form.js)
- **Root Cause**:
  - When charges were appended, `saveAppendedCharges` inserted records into `stocks_ledger` without computing `cost_ave`, `retails`, or `totalamt`. This left those columns as `NULL` in the database.
  - In the Append Charges modal, the Total column displayed raw `null`.
  - In the Patient Charges table, `parseFloat(null)` evaluated to `NaN`, poisoning the running total and displaying `NaN`.
- **Fixes**:
  - In `DoctorController::saveAppendedCharges`:
    - Computed unit price (`$unitPrice = $charge->price_regular`) and line total (`$totalAmt = $unitPrice * $quantity`).
    - Written `cost_ave`, `retails`, and `totalamt` to `stocks_ledger`.
  - In `DoctorController::fetchPatientCharges`:
    - Transformed charge rows so any missing `totalamt` or unit price is dynamically retrieved from `stocks_listing` and formatted to 2 decimal places.
  - In `consultation_modal.blade.php`:
    - Corrected column headers from `Discount` & `Amount` to `Unit Price` & `Total Amount`.
  - In `form.js`:
    - Formatted row totals using `parseFloat(val).toFixed(2)`.
    - In `#patient_charge_tab_btn` DataTable initialization: added defensive check preventing `NaN` when calculating total amount.

---

### 4. Interactive Button Loaders Across All Target Pages
- **Target Screens**:
  - `admin/users/secretaries-admins`:
    - `#add_secretary_btn`, `#add_admin_btn`, `.edit_user`, `#save_edit_secretary_btn`, `#save_edit_admin_btn`, `.delete_user`, `.assign_doctor`, `#save_append`.
  - `admin/secretary` & `secretary/queue`:
    - `#sprevious` & `#snext`: visual spin and delay-reset during queue pagination.
    - `#add_patient_btn`: `setBtnLoading($btn, "Adding...")`.
    - `.import-queue`: row-level loading spinner during consultation detail fetch.
    - `#reschedule_btn`: `setBtnLoading($btn, "Rescheduling...")`.
    - `.update-queue`: SweetAlert loading overlay (`Swal.showLoading()`) during status updates.
    - `#view_masterlist_btn`: `setBtnLoading($btn, "Loading...")`.
    - `.import-btn`: `setBtnLoading($btn, "Importing...")` in patient masterlist modal.
    - `.remove_charge`: delete confirmation with button spinner.
    - Preserved and wired `#mark_as_complete_btn` in queue form card footer with explicit `type="button"`, validation, confirmation, and button loading state (`setBtnLoading($btn, "Completing...")`).
  - `doctor/consultation`:
    - `#previous` & `#next`: visual spin during date pagination.
    - `#consult` ("Proceed Consultation"): `setBtnLoading($btn, "Loading...")` until modal opens.
    - `#save_consul` ("Save Consultation"): `setBtnLoading($btn, "Saving...")` during `/api/complete_consultation`.
    - `#save_impressions_diagnosis`: `setBtnLoading($btn, "Saving...")`.
    - `#save_rx_btn`: `setBtnLoading($btn, "Saving...")`.
    - `.delete_rx`: button spinner.
    - `#save_requests`: `setBtnLoading($btn, "Saving...")`.
    - `.remove_request`: button spinner.
    - `#update_files_btn`: `setBtnLoading($btn, "Saving...")`.
    - `#save_charges_btn`: `setBtnLoading($btn, "Saving...")`.
    - `.remove_charge_btn`: button spinner.

---

## Verification
1. **Frontend Asset Build**:
   - `npm run build` compiled all Vite bundles in 3.09s with zero errors:
     - `public/build/assets/secretaries-BrB7qI4s.js`
     - `public/build/assets/queue-BtZZdhJJ.js`
     - `public/build/assets/consultation-DOu7e3ox.js`
     - `public/build/assets/form-DS3JX9o1.js`
2. **Backend Automated Tests**:
   - Running `docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan test`.
