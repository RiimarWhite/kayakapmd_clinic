# Handoff Report: Survey of Requirement R2 & Requirement R3

**Agent**: explorer_survey_2  
**Role**: Read-only Exploration & Survey Agent  
**Parent Orchestrator**: ea120fb8-e825-4fef-bd9a-94f4a2e87e09  
**Date**: 2026-10-12  
**Mission**: Codebase survey for Requirement R2 (Clinical Data & Attachment Integration) and R3 (Itemized Billing Calculations).

---

## 1. Observation

1. **MySQL 1054 Column Not Found on Patient Address**:
   - In `app/Http/Controllers/SecretaryController.php` (line 775), the method `fetchPatientMedhistory` queries:
     ```php
     $history = $query->select([
         'id',
         'consultationrefno',
         'pxrefno',
         'pincode',
         'caseno',
         'patientname',
         'gender',
         'birthday',
         'age',
         'mobilenumber',
         'emailaddress',
         'address', // Line 775
     ```
   - Running `php artisan tinker --execute="var_dump(App\Models\ConsultationModel::select('address')->get());"` produced:
     ```
     Illuminate\Database\QueryException  SQLSTATE[42S22]: Column not found: 1054 Unknown column 'address' in 'field list' (Connection: mysql, Host: localhost, Port: 3306, Database: kayakapmdv2, SQL: select `address` from `pxwalkinconsultation`).
     ```
   - Running `php artisan tinker --execute="var_dump(Schema::hasColumn('pxwalkinconsultation', 'address'));"` returned `bool(false)`.
   - Running `php artisan tinker --execute="var_dump(Schema::hasColumn('pxmasterlist', 'address'));"` returned `bool(true)`.
   - `pxwalkinconsultation` has 92 columns (verified via `Schema::getColumnListing('pxwalkinconsultation')`) and does not possess an `address` column.
   - `ConsultationModel.php` (lines 150-153) already defines:
     ```php
     public function patient()
     {
         return $this->belongsTo(PatientMasterlist::class, 'pxrefno', 'pxrefno');
     }
     ```
   - In `DoctorController.php` (lines 965-969), `printPDF` resolves patient address from `patient` relation:
     ```php
     if (empty($patient->address) && $patient->patient) {
         $pMaster = $patient->patient;
         $addrParts = array_filter([$pMaster->streetadrs, $pMaster->brgy, $pMaster->muncity, $pMaster->province]);
         $patient->address = !empty($addrParts) ? implode(', ', $addrParts) : ($pMaster->address ?? '');
     }
     ```

2. **Broken Laboratory & Radiology Link Generation**:
   - In `app/Http/Controllers/SecretaryController.php` (lines 805-806), `laboratorypath` and `radiologypath` are queried directly from `pxwalkinconsultation`, but lines 811-845 do NOT generate `radiology_url` or `laboratory_url`.
   - In `resources/js/pages/doctor/dashboard.js` (lines 599-615), `patients.js` (lines 987-1003), `queue.js` (lines 807-823), and `form.js` (lines 462-478), the preview button URL is set via:
     ```javascript
     const radUrl = record.radiology_url || record.radiologypath;
     $("#vcd_rad_preview_btn").attr('href', radUrl).removeClass('d-none');
     ```
   - Because `record.radiology_url` is null, `href` is set to the raw storage path (e.g. `'radiology_results/sample.png'`), which resolves to a non-existent relative URL on the current page (e.g. `http://localhost/doctor/dashboard/radiology_results/sample.png`), resulting in an **HTTP 404 error**.
   - Valid file preview routes exist in `routes/web.php` (lines 122-134):
     `Route::get('/preview-file/{path}', $previewFileHandler)->name('preview.file');`
     `Route::get('/{project}/preview-file/{path}', ...)->name('preview.file.project');`
   - In `DoctorController.php` (lines 1778-1798), `fetchRadLabFiles` correctly generates absolute URLs using `route('preview.file', ['path' => $files->radiologypath])`.

3. **Prescriptions and Diagnostics Multi-Store Fallback**:
   - `fetchPatientMedhistory` in `SecretaryController.php` lines 823-836 reads prescriptions and diagnostics solely from `stocks_ledger`.
   - In `DoctorController.php@printPDF` (lines 995-1051), prescriptions are concatenated from `stocks_ledger` and `pxrxdocuments` (`DoctorMedicinesModel`), and diagnostics are concatenated from `stocks_ledger` and `docrequests` (`DocRequestsModel`).
   - If older/imported consultations lack entries in `stocks_ledger`, `#view_consultation_details_modal` displays empty tables ("No prescriptions recorded", "No diagnostic requests recorded") even though records exist in `pxrxdocuments` or `docrequests`.

4. **Itemized Billing & Stocks Ledger Calculations**:
   - `stocks_ledger` columns: `px_consultcode_cn` (= `consultationrefno`), `transactiontype` (= `'CHARGES'`), `item_grouping` (`'DRUGS AND MEDS'`, `'DIAGNOSTIC'`, `'PROFESSIONAL FEE'`, `'PROCEDURES'`, `'SUPPLIES'`), `qty`, `cost_ave`, `retails`, `totalamt`.
   - Pricing tiers for line items are evaluated based on `classification`: PHIC (`price_phic`), HMO (`price_hmo`), OTHERS (`price_others`), or REGULAR (`price_regular`).
   - Doctor consultation fee (`PF`) is auto-created in `fetchPatientCharges` (`DoctorController.php` lines 1368-1413) if absent, computing `pfrate` + VAT (`autoAddVAT`).
   - Total gross calculation in `#view_consultation_details_modal` (`#vcd_charges_total`) is computed dynamically by summing all line items in `record.charges`.
   - PDF printable generation (`/print_pdf?type=rx&consultationrefno=...` and `/print_pdf?type=diagnostics&consultationrefno=...`) routes through `DoctorController::printPDF`, using Dompdf with half-A4 landscape (A5) configuration and streaming valid PDFs.

5. **Test Suite Baseline & Build Status**:
   - `php artisan test` executes 99 passed tests (726 assertions) with 0 failures and 0 errors.
   - `npm.cmd run build` executes cleanly in 3.01s with exit code 0.

---

## 2. Logic Chain

1. **Premise**: In production MySQL, `pxwalkinconsultation` has no `address` column.
   - **Observation 1**: Executing `ConsultationModel::select('address')` throws `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'address' in 'field list'`.
   - **Observation 2**: `pxmasterlist` contains the authoritative `address` column, and `ConsultationModel` defines a `belongsTo` relationship `patient` with `pxmasterlist`.
   - **Inference**: Removing `'address'` from `ConsultationModel::select()` in `SecretaryController.php` and populating `$record->address` via `$record->patient` will prevent the MySQL 1054 fatal error and supply valid addresses without "N/A" placeholders.

2. **Premise**: Clicking "Preview / Download" for radiology and laboratory files must open the actual attachment.
   - **Observation 1**: `pxwalkinconsultation` stores relative disk paths (`radiology_results/file.png`).
   - **Observation 2**: `preview-file/{path}` in `routes/web.php` securely serves files from `storage/app/private/` with inline disposition.
   - **Observation 3**: `SecretaryController.php` does not generate `radiology_url` or `laboratory_url`, causing the frontend to set `href="radiology_results/file.png"` which 404s.
   - **Inference**: Generating `radiology_url` and `laboratory_url` via `route('preview.file', ['path' => ...])` in `SecretaryController.php`, combined with a client-side `resolvePreviewUrl()` fallback helper across all JS scripts, will guarantee working links in all environments (including Apache `/kayakapmd_clinic` subfolder deployments).

3. **Premise**: The Consultation Details modal must accurately reflect all historical and active consultations without missing data.
   - **Observation 1**: Modern consultations persist medications and diagnostics to `stocks_ledger`.
   - **Observation 2**: Legacy or external entries may exist in `pxrxdocuments` and `docrequests`.
   - **Inference**: Implementing the multi-source merge in `fetchPatientMedhistory` (as already implemented in `printPDF`) ensures complete display of prescriptions and diagnostics without unneeded "N/A" or empty states.

---

## 3. Caveats

1. **SQLite vs. MySQL Discrepancy**:
   - The test environment uses SQLite in-memory (`:memory:`), which handled the missing `address` column differently during certain selects, whereas MySQL immediately rejects the query with error 1054. Fixes must be validated against both MySQL schema and SQLite tests.
2. **Read-Only Scope**:
   - As an explorer agent, no source code was altered during this survey. Proposed changes are documented in `analysis.md` and this handoff report for implementers to execute.
3. **Storage Disk Existence**:
   - Files stored in `storage/app/private/` require existing files to return HTTP 200 via `preview-file/{path}`. If a file was deleted from disk, `preview-file/{path}` returns HTTP 404 (by design).

---

## 4. Conclusion

The architecture for Requirements R2 and R3 is well-structured but contains two high-impact defects and several data synchronization gaps:
1. **Critical Defect**: `SecretaryController.php@fetchPatientMedhistory` specifies `address` in `select()` on `pxwalkinconsultation`, causing MySQL 1054 crash or `"N/A"` address rendering.
2. **Critical Defect**: `radiology_url` and `laboratory_url` are not generated by `fetchPatientMedhistory`, causing broken 404 links on attachment preview/download buttons.
3. **Data Gaps**: Legacy prescriptions (`pxrxdocuments`) and diagnostics (`docrequests`) are not merged when `stocks_ledger` is empty; demographic fields (`pincode`, `birthday`, `gender`, calculated `age`) lack fallback derivation from `pxmasterlist`.
4. **Billing Integrity**: `stocks_ledger` correctly tracks line items and computes gross totals, while `printPDF` provides functional PDF streaming for prescriptions (`type=rx`) and diagnostic requests (`type=diagnostics`).

---

## 5. Verification Method

To independently verify the survey findings:

1. **Verify Address Column Absence in `pxwalkinconsultation`**:
   ```powershell
   php artisan tinker --execute="var_dump(Schema::hasColumn('pxwalkinconsultation', 'address'));"
   # Output: bool(false)
   ```

2. **Verify Address Column Presence in `pxmasterlist`**:
   ```powershell
   php artisan tinker --execute="var_dump(Schema::hasColumn('pxmasterlist', 'address'));"
   # Output: bool(true)
   ```

3. **Verify MySQL 1054 Error on selecting address from `pxwalkinconsultation`**:
   ```powershell
   php artisan tinker --execute="var_dump(App\Models\ConsultationModel::select('address')->get());"
   # Throws: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'address' in 'field list'
   ```

4. **Verify Existing Feature Tests Pass**:
   ```powershell
   php artisan test --filter=ConsultationTabAndDetailsModalTest
   php artisan test --filter=PatientManagementAndPrintFixesTest
   php artisan test --filter=OpdConsultationWorkflowTest
   php artisan test --filter=ConsultationChargesAndSettlementsTest
   ```

5. **Verify Full Test Suite & Build**:
   ```powershell
   php artisan test
   npm.cmd run build
   ```

---
**Detailed Analysis Reference**: `C:\xampp\htdocs\kayakapmd_clinic\.agents\teamwork\explorer_survey_2\analysis.md`
