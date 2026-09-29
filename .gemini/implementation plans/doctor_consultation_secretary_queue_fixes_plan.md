# Implementation Plan: Secretary Queue, Doctor Patients & Consultation Modal Fixes

**Document Name:** `doctor_consultation_secretary_queue_fixes_plan.md`  
**Location:** `.gemini/implementation plans/doctor_consultation_secretary_queue_fixes_plan.md`  
**Date:** 2026-09-29  
**Status:** In Progress / Pending Verification  

---

## 1. Overview & Objectives

This implementation plan outlines the precise architectural analysis, bug fixes, and feature enhancements requested across three primary route groups:
1. **`/admin/secretary` & `/secretary/queue`**: Default the "Update or assign new consultation date" field (`#sched_date`) and schedule time (`#sched_time`) based on the "Enter consultation date" field (`#queuedate`) or current date and the selected queue schedule (`#stime`).
2. **`/doctor/patients`**: Add full patient photo capabilities (image upload, WebRTC camera capture, preview, and backend storage/retrieval) in both the "Add New Patient" (`add_patient.blade.php`) and "Edit Patient Record" (`edit_patient.blade.php`) modals.
3. **`/doctor/consultation` & Consultation Modal**:
   - **Dashboard Default Consultation Fee**: Replicate the complete "Rates, Tax & Billing" fields from "My Doctor Profile" (`doctor_info.blade.php`) onto the dashboard "Default Consultation Fee" card (`dashboard.blade.php`), and use these configured rates when generating patient charges.
   - **Generate Rx Modal Demographics**: Fix empty patient demographic information in the Generate Rx modal by resolving duplicate HTML IDs and synchronizing data on modal show.
   - **Rx Table Refresh**: Auto-refresh the consultation "Rx & Instructions" tab table immediately upon closing the Generate Rx modal (`#rx_modal`).
   - **Rx Datatable Column Count Error**: Fix the DataTables error `table id=dashboard_rx_table - Incorrect column count` caused by `form.js` declaring 3 columns against 4 `<th>` headers.
   - **Diagnostic Request Deletion**: Fix diagnostic request deletion by binding the `.remove_request` button with the correct stock/diagnostic reference code and removing it from both `stocks_ledger` and `dd_docrequests`.
   - **Radiology & Laboratory Document/Image Preview**: Enable robust document and image preview for uploaded radiology and laboratory files, displaying images in responsive image containers and PDFs/documents in embedded viewers.
   - **Editable Professional Fee on Patient Charges**: Ensure the Professional Fee (and consultation charges in `stocks_ledger`) can be edited directly from the Patient Charges tab via `/api/update_charge`.
   - **Remove Duplicate ₱ Currency Symbol**: Fix duplicate `₱₱` display on the Patient Charges Total.

---

## 2. Root Cause Analysis & Technical Findings

### Route Group 1: `/admin/secretary` and `/secretary/queue`
- **Root Cause**:
  - In `queue.blade.php` (line 358), `#sched_date` has no default `value="{{ now()->toDateString() }}"`.
  - In `queue.js`, when `#queuedate` changes, `#sched_date` is not updated to match, nor is `#sched_time` synchronized with `#stime`.
  - In patient import/load handlers (`queue.js` lines 511 & 1166), if a patient has no pre-existing consultation date, `#sched_date` was cleared to an empty string (`$("#sched_date").val("")`).
- **Solution**:
  - Add default `value="{{ now()->toDateString() }}"` to `#sched_date` in `queue.blade.php`.
  - In `queue.js`:
    - On load: default `#sched_date` to `$("#queuedate").val() || todayStr`.
    - On `#queuedate` change: synchronize `#sched_date.val($("#queuedate").val())` and trigger `loadSchedules(2)`.
    - On `#stime` populate/change: synchronize `#sched_time.val($("#stime").val())`.
    - On patient import/load: default `#sched_date` to `$("#queuedate").val() || todayStr` instead of blank string.

### Route Group 2: `/doctor/patients` Patient Image Controls
- **Root Cause**:
  - `add_patient.blade.php` and `edit_patient.blade.php` lack image preview elements, file inputs (`patient_image`), hidden base64 fields (`photo_base64`), and upload/camera buttons.
  - `patients.js` submits forms using `$(form).serialize()`, which completely excludes binary files.
  - Neither `ConsultationController::addPatientRecord` nor `ManagementController::updatePatient` processes `patient_image` or `photo_base64`.
- **Solution**:
  - Add standard image preview thumbnail (`patient_picture_preview`), file input, hidden `photo_path` and `photo_base64`, and "Upload Image" + "Take Photo" buttons in both modals.
  - Include `resources/views/modals/take_photo.blade.php` in `patients.blade.php`.
  - Update `patients.js` to handle image file selection preview, WebRTC camera capture via `takePhotoModal`, and submit via `FormData`.
  - Update `ConsultationController::addPatientRecord` and `ManagementController::updatePatient` to store patient photos in `storage/app/private/patient_photo/` and save `photo_path` on `PatientMasterlist` and `pxwalkinconsultation`.

### Route Group 3: `/doctor/consultation` & Consultation Modal
- **Dashboard Rates & Billing**:
  - `dashboard.blade.php` only has `pfrate`.
  - Copy Tab 4 fields from `doctor_info.blade.php`: `pfrate`, `rodrate`, `tax`, `vatrate`, `coacode`, `accountno`, `vatable`, `autoAddVAT`, `issuehospOR`.
  - Update `DoctorController::updateDoctorFee` and `dashboard.js` to persist all these fields to `DoctorModel` / `DoctorsProfileModel`.
- **Empty Patient Info in Generate Rx Modal**:
  - `consultation_modal.blade.php` defined identical IDs (`#rx_patient_info`, `#genname`, `#gensex`, etc.) in both the sidebar patient card and the `#rx_modal` accordion.
  - When `consultation.js` ran `$("#genname").text(...)`, jQuery only updated the first instance (in the sidebar). The accordion in `#rx_modal` remained blank.
  - Fix by assigning unique IDs (or syncing all matching fields via class/scope on `show.bs.modal`).
- **Rx Table Refresh on Modal Close**:
  - Add event listener on `#rx_modal` `hidden.bs.modal` to call `loadDashboardRx()` to immediately refresh the Rx table on the Rx & Instructions tab.
- **`dashboard_rx_table` Column Count Mismatch**:
  - In `form.js` line 293, `loadDashboardRx()` defines 3 columns (`item_dscr`, `qty`, `dispensed_status`), whereas `consultation_modal.blade.php` has 4 headers (`Medicine Name`, `Instructions / Sig`, `Quantity`, `Status`).
  - Update `form.js` to define 4 columns matching `consultation.js` and the table header.
- **Diagnostic Request Deletion**:
  - In `form.js` line 381, `.remove_request` button rendered `value="${data.diagnostic_id}"`. In `DoctorController::getDiagnosticRequests`, the items returned from `StocksListingModel` have `prodcode`, not `diagnostic_id`.
  - Change to `value="${data.prodcode || data.diagnostic_id || data.diagnosticrefno}"`.
  - Fix `DoctorController::deleteDiagnostic` to delete from `DocRequestsModel` and `StocksLedgerModel` using `prodcode` and `consultationrefno`.
- **Radiology and Laboratory Preview**:
  - In `form.js`, `preview_modal` used an `<iframe>` for all files. For image files (`.jpg`, `.jpeg`, `.png`), an iframe often attempts download or displays poorly.
  - Update `consultation_modal.blade.php` to include both an `<iframe>` (for PDFs/documents) and an `<img>` (for images).
  - In `form.js`, inspect file type/extension; show the `<img>` for images and `<iframe>` for PDFs/documents.
  - In `routes/web.php`, update `/preview-file/{path}` route to allow authenticated guards (`auth:web,doctor,secretary`) and serve with `Content-Disposition: inline`.
- **Editable Professional Fee on Patient Charges**:
  - In `form.js` line 1140, `edit_charge_btn` called relative `url: "update_charge"` and lost the button context (`$(this).val()`).
  - In `DoctorController::updateCharge` (line 1351), the logic updated legacy `DocChargesModel` instead of `StocksLedgerModel`.
  - Update `DoctorController::updateCharge` to find the charge in `StocksLedgerModel` by `id` or `prodcode`, updating `qty`, `cost_ave`, `retails`, and `totalamt`.
  - Update `form.js` to pre-fill the edit dialog, preserve button data attributes, and call `/api/update_charge`.
- **Duplicate Currency Symbol (₱) on Patient Charges Total**:
  - In `form.js` line 1034: `el.innerHTML = '<h4 class="fw-bold m-0">Total: ₱<span class="fw-normal ms-2" id="charges_total">0.00</span></h4>';`
  - In `form.js` line 973: `$("#charges_total").text('₱' + total.toFixed(2));`
  - Result: `Total: ₱₱1500.00`.
  - Fix line 973 to set `total.toFixed(2)` without the extra `₱`.

---

## 3. Step-by-Step Implementation Tasks

```
[ ] Step 1: Update Secretary Queue Date & Schedule Defaults
    - resources/views/pages/secretary/queue.blade.php
    - resources/js/pages/secretary/queue.js
[ ] Step 2: Implement Patient Image Capture & Edit in Doctor Patients
    - resources/views/modals/add_patient.blade.php
    - resources/views/modals/edit_patient.blade.php
    - resources/views/pages/doctor/patients.blade.php
    - resources/js/pages/doctor/patients.js
    - app/Http/Controllers/ConsultationController.php
    - app/Http/Controllers/ManagementController.php
[ ] Step 3: Enhance Doctor Dashboard Default Consultation Fee Widget
    - resources/views/pages/doctor/dashboard.blade.php
    - resources/js/pages/doctor/dashboard.js
    - app/Http/Controllers/DoctorController.php
[ ] Step 4: Fix Consultation Modal Rx Demographics & Auto-Refresh
    - resources/views/modals/consultation_modal.blade.php
    - resources/js/pages/doctor/consultation.js
[ ] Step 5: Fix Rx Table Datatable Column Count Mismatch
    - resources/js/pages/doctor/consultation/form.js
[ ] Step 6: Fix Diagnostic Requests Deletion
    - resources/js/pages/doctor/consultation/form.js
    - app/Http/Controllers/DoctorController.php
[ ] Step 7: Enhance Radiology & Laboratory Document/Image Preview
    - resources/views/modals/consultation_modal.blade.php
    - resources/js/pages/doctor/consultation/form.js
    - routes/web.php
[ ] Step 8: Fix Editable Professional Fee & Remove Duplicate Total Symbol
    - resources/js/pages/doctor/consultation/form.js
    - app/Http/Controllers/DoctorController.php
[ ] Step 9: Rebuild Frontend Assets and Run Test Suite
    - npm run build
    - docker exec latest_php_server php artisan test
[ ] Step 10: Create Walkthrough Documentation & Git Commit
    - .gemini/walkthroughs/doctor_consultation_secretary_queue_fixes_walkthrough.md
```

---

## 4. Verification & Testing Strategy

1. **Secretary Queue Testing**:
   - Access `/secretary/queue` and `/admin/secretary`.
   - Verify `#sched_date` defaults to current date on initial load.
   - Change `#queuedate` and verify `#sched_date` automatically synchronizes.
   - Verify `#sched_time` matches `#stime`.
2. **Doctor Patients Testing**:
   - Open `/doctor/patients`.
   - Click "Add New Patient". Select an image file or take a webcam snapshot. Submit form and verify `photo_path` is saved.
   - Click "Edit Patient Record". Verify existing photo is displayed. Replace with new photo, save, and verify update.
3. **Doctor Consultation Testing**:
   - Check Dashboard "Default Consultation Fee" card. Confirm all Rates, Tax & Billing fields are present and saveable.
   - Open consultation modal:
     - Open "Generate Rx" modal. Verify patient demographics are properly displayed.
     - Add medicine and close modal. Verify Rx table on "Rx & Instructions" tab auto-refreshes without errors.
     - Confirm no DataTables column count error on `#dashboard_rx_table`.
     - Request diagnostic, then click "Remove". Confirm request is deleted from list and `stocks_ledger`.
     - Upload image and PDF under Radiology & Laboratory. Click "Preview" on both and verify image and PDF render correctly.
     - In Patient Charges tab, click edit button on Professional Fee. Change amount and save. Verify `stocks_ledger` updates and total recalculates.
     - Verify Total shows single `₱` currency symbol (e.g. `Total: ₱1,500.00`).
4. **Automated Testing**:
   - Run `php artisan test` inside the Docker container to ensure zero regressions across existing test suites.
