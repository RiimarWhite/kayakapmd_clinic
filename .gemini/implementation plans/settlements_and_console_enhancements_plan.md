<!--
  Detailed Comment: Implementation Plan for Settlements, Printable Layouts, Medical History,
  Queue Financial Report, Consultation History Fix, and Secretary Console Responsiveness.
  Persisted per KayakapMD Rule 2 into .gemini/implementation plans/.
-->
# Implementation Plan: Settlements, Printable Half-A4, Medical History, Financial Report & Console Responsiveness

## Goal Description
Enhance KayakapMD Clinic's settlement management, printable documents, clinical history tracking, queue financial reporting, and secretary console responsiveness:
1. **Settlement & Payment Enhancements**:
   - Add Letter of Authorization (LOA) reference number field for HMO coverage.
   - Add "Charge to PhilHealth Yakap" checkbox between Cash and CTA with automated Co-Pay calculation.
   - Rename settlement save button to "Record Payment".
2. **Printable Documents**:
   - Format Rx (Prescription) and Diagnostic Request printables to landscape half-A4 (A5 landscape 210mm × 148mm).
3. **Medical History (Permanent / Lifetime Data)**:
   - Establish dedicated patient medical history tracking (allergies like seafood, injection/immunization records, past conditions) distinct from per-visit consultation history.
   - Add medical history tabs and forms to both Secretary Console and Doctor Consultation Modal.
4. **Queue Financial Reporting**:
   - Add an End-of-Day Financial Report / Income Summary card at the bottom of the Patient Queue.
   - Provide summary metrics and printable Consolidated Financial Report (all patient payments).
5. **Consultation History Fix**:
   - Ensure consultation history reliably displays for patients across secretary and doctor consoles using multi-identifier matching (`pxrefno`, `pincode`, name + birthday).
   - Fix `loadMedicalHistory` runtime invocation in `doctor.js`.
6. **Secretary Console Responsiveness**:
   - Re-architect layout flow (`Patient Queue -> Patient Consultation Details -> Patient Masterlist`) removing rigid min-width bottlenecks for full multi-device responsiveness.

---

## User Review Required

> [!IMPORTANT]
> **Database Schema Changes (Dual-Update Protocol)**:
> 1. In `pxsettlements`: add `hmo_loa_no` (varchar 100), `is_philhealth_yakap` (tinyint 1), and `copay` (double 11,2).
> 2. Create new table `pxmedicalhistory` for permanent patient clinical data (`allergies`, `injections_immunization`, `past_medical_history`, `surgical_history`, `notes`).
> Both `.gemini/database/kayakapmdv2_data_dictionary.md` and database migrations will be updated synchronously.

> [!NOTE]
> **Printable Page Size**:
> Landscape half-A4 corresponds to standard A5 landscape (210mm × 148mm / 595pt × 420pt). The CSS `@page` and DomPDF paper settings for Rx and Diagnostic requests will be set to `a5` landscape.

---

## Open Questions & Proposed Assumptions
1. **PhilHealth Yakap Co-Pay Automation**:
   - *Assumption*: When "Charge to PhilHealth Yakap" is checked, any remaining balance not covered by Cash or CTA will automatically calculate and populate the `Co-pay` input field, ensuring zero unallocated balance before recording payment.
2. **Medical History Scope**:
   - *Assumption*: Medical history will store permanent clinical records (food/seafood allergies, drug allergies, immunization/injection log, chronic conditions) that persist across all future consultations for that patient.
3. **Responsive Ordering**:
   - *Assumption*: On wide displays, Patient Queue and Consultation Details are side-by-side with Masterlist below/collapsible; on mobile/tablets, they stack sequentially: Patient Queue $\rightarrow$ Consultation Details $\rightarrow$ Patient Masterlist.

---

## Proposed Changes

### Component 1: Database Migrations & Data Dictionary Synchronization

#### [NEW] `database/migrations/2026_10_08_000001_add_loa_and_yakap_to_pxsettlements.php`
- Adds `hmo_loa_no`, `is_philhealth_yakap`, and `copay` columns to `pxsettlements` with defensive `hasColumn` checks.

#### [NEW] `database/migrations/2026_10_08_000002_create_pxmedicalhistory_table.php`
- Creates table `pxmedicalhistory` with fields:
  - `id`
  - `pxrefno` (varchar 50, indexed)
  - `pincode` (varchar 50, nullable)
  - `allergies` (text nullable)
  - `injections_immunization` (text nullable)
  - `past_medical_history` (text nullable)
  - `surgical_history` (text nullable)
  - `maintenance_medications` (text nullable)
  - `notes` (text nullable)
  - `recordedby`, `updatedby`, `created_at`, `updated_at`

#### [MODIFY] `.gemini/database/kayakapmdv2_data_dictionary.md`
- Update table entry for `pxsettlements` with the 3 new columns.
- Add table entry for `pxmedicalhistory` under Module 4 (Patient Master Data).

#### [MODIFY] `app/Models/SettlementsModel.php`
- Add `hmo_loa_no`, `is_philhealth_yakap`, `copay` to `$fillable`.

#### [NEW] `app/Models/PxMedicalHistoryModel.php`
- Eloquent model for `pxmedicalhistory` with guarded `id` and timestamps enabled.

---

### Component 2: Settlement Modal & Payment Logic

#### [MODIFY] `resources/views/modals/settlement_modal.blade.php`
- In the HMO Coverage card:
  - Add Letter of Authorization Ref # input field: `<input type="text" name="hmo_loa_no" id="hmo_loa_no" placeholder="LOA Reference Number">`.
- In the Payment settlement section:
  - Row 1: CASH input + Import Net Billing button.
  - Row 2 (Between Cash and CTA):
    - Switch/Checkbox: "Charge to PhilHealth Yakap" (`#is_philhealth_yakap`).
    - Co-Pay input group: `<input type="number" step="0.01" min="0.00" name="copay" id="copay" placeholder="0.00">`.
  - Row 3: CTA input + Card Type select + Import Net Billing button.
- In View Settlements tab:
  - Add displays for `info_hmo_loa` and `info_copay`.
- In Modal Footer:
  - Change `<button id="save_settlements">Save</button>` to `<button id="save_settlements"><i class="fa-solid fa-receipt me-1"></i> Record Payment</button>`.

#### [MODIFY] `resources/js/pages/secretary/queue.js`
- Update `calculateSettlementNetBilling()` and `calculateSettlementRemaining()`:
  - Include `#copay` in payment calculations if PhilHealth Yakap is checked.
  - When `#cash` or `#cta` changes and PhilHealth Yakap is checked, automatically disperse remainder into `#copay`.
  - Vice versa, when Co-Pay is adjusted, compute remaining to settle.
- Populate `hmo_loa_no`, `is_philhealth_yakap`, and `copay` on form load and in `populateViewSettlements`.

#### [MODIFY] `app/Http/Controllers/SecretaryController.php`
- Update `saveSettlements()`:
  - Extract and save `hmo_loa_no`, `is_philhealth_yakap`, and `copay`.
  - Log settlement payment audit trail with new attributes.

---

### Component 3: Printable Documents (Rx & Diagnostic Requests Landscape Half-A4)

#### [MODIFY] `app/Http/Controllers/DoctorController.php`
- In `printPdf()`:
  - For `type === 'rx'` and `type === 'diagnostics'`: set paper to `a5`, `landscape`:
    ```php
    $paperSize = in_array($type, ['rx', 'diagnostics']) ? 'a5' : 'a4';
    $orientation = in_array($type, ['rx', 'diagnostics']) ? 'landscape' : 'portrait';
    return Pdf::loadView('printables.rx_print', ...)
        ->setPaper($paperSize, $orientation)
        ->stream(...);
    ```
- In `printDiagnostics()`:
  - Set paper to `'a5', 'landscape'`.

#### [MODIFY] `resources/views/printables/rx_print.blade.php`
- Update `@page` CSS:
  - Conditionally apply `@page { size: A5 landscape; margin: 6mm 8mm; }` for Rx and Diagnostics.
  - Scale outer frame, two-column layout for medications / requests, patient info bar, and doctor signature block to fit half-A4 landscape without vertical overflow.

---

### Component 4: Medical History (Permanent Lifetime Tracking)

#### [MODIFY] `app/Http/Controllers/SecretaryController.php` & `app/Http/Controllers/DoctorController.php`
- Add API endpoints:
  - `fetchPatientMedicalHistory(Request $request)`: looks up `PxMedicalHistoryModel` by `pxrefno` / `pincode` / `consultationrefno`.
  - `savePatientMedicalHistory(Request $request)`: creates or updates `PxMedicalHistoryModel` with allergies, injections, past history, and audit details.

#### [MODIFY] `routes/api.php`
- Register `POST /api/fetch_patient_medical_history` and `POST /api/save_patient_medical_history`.

#### [MODIFY] `resources/views/pages/secretary/queue.blade.php`
- Add "Medical History" tab (`#patient_lifetime_medhistory_tab`) in right-column consultation card.
- Form inputs for:
  - Food & Environmental Allergies (e.g. Seafood, etc.)
  - Drug Allergies (e.g. Penicillin, etc.)
  - Injection & Immunization History
  - Past Medical Conditions (Hypertension, Diabetes, Asthma, etc.)
  - Surgical History & Hospitalizations
  - Maintenance Medications & Clinical Remarks
  - "Save Medical History" button with SweetAlert confirmation.

#### [MODIFY] `resources/views/modals/consultation_modal.blade.php`
- **Card-Header Navigation Tabs**:
  - Add third top-level tab alongside "Consultation" and "Consultation History":
    `<button class="nav-link fw-bold text-dark px-3" id="main_permanent_medhistory_tab_btn" data-bs-toggle="tab" data-bs-target="#main_permanent_medhistory_pane" type="button" role="tab"><i class="fa-solid fa-file-medical text-primary me-2"></i> Medical History</button>`
- **Patient Information Banner Alert**:
  - Add an Allergy / Medical Alert badge bar directly inside `rx_patient_card` below patient vitals:
    `<div class="alert alert-warning py-1 px-2 m-0 mb-2 d-flex align-items-center justify-content-between small" id="doc_allergy_alert_bar"><span><i class="fa-solid fa-triangle-exclamation text-danger me-1"></i> <strong>Allergies:</strong> <span id="doc_header_allergies" class="text-danger fw-bold">None recorded</span></span><button type="button" class="btn btn-sm btn-outline-dark py-0 px-2 fw-semibold" id="btn_quick_view_medhistory"><i class="fa-solid fa-file-medical me-1"></i> View / Edit</button></div>`
- **Dedicated Medical History Pane (`#main_permanent_medhistory_pane`)**:
  - Full-featured clinical intake interface with structured form cards:
    - **Allergies & Adverse Reactions** (Seafood, Penicillin, NSAIDs, food/environmental allergies)
    - **Injection & Immunization History** (Vaccines, tetanus, routine shots)
    - **Past Medical History & Chronic Conditions** (Hypertension, Diabetes, Asthma, CVD, CKD)
    - **Past Surgeries & Hospitalizations**
    - **Family Medical History** (Hereditary conditions)
    - **Current Maintenance Medications**
    - **Clinical Warnings & Remarks**
  - Save button: `<button type="button" class="btn btn-primary fw-bold" id="save_doctor_medhistory_btn"><i class="fa-solid fa-floppy-disk me-1"></i> Save Medical History</button>`.

#### [MODIFY] `resources/js/pages/secretary/queue.js` & `resources/js/pages/doctor/consultation/form.js`
- Implement AJAX loaders (`loadPatientMedicalHistory`) and save handlers (`savePatientMedicalHistory`) in both Secretary and Doctor workflows.
- Update patient header allergy badge whenever medical history is loaded or saved.

---

### Component 5: Patient Queue Financial Report (End-of-Day Income Summary)

#### [MODIFY] `resources/views/pages/secretary/queue.blade.php`
- Add Financial Summary card at the bottom of the Patient Queue column:
  - Live summary metrics: Total Queued, Total Settled, Gross Billing, Total Deductions, Net Collections, Cash vs CTA vs Yakap.
  - Buttons:
    - "Breakdown": opens modal with itemized queue patients and payments.
    - "Print Financial Report": generates consolidated daily income report PDF.

#### [NEW] `resources/views/printables/financial_report_print.blade.php`
- Printable landscape/portrait daily collection sheet showing all patient charges, senior/PWD discounts, PhilHealth deductions, HMO deductions, cash, card, and total received for the day.

#### [MODIFY] `app/Http/Controllers/SecretaryController.php`
- Add `fetchQueueFinancialSummary(Request $request)` returning aggregated queue billing totals.
- Add `printFinancialReport(Request $request)` streaming consolidated income report PDF.

#### [MODIFY] `routes/web.php` & `routes/api.php`
- Register `/print_financial_report` and `/api/fetch_queue_financial_summary`.

---

### Component 6: Consultation History Bug Fixes & Doctor Modal Invocation

#### [MODIFY] `app/Http/Controllers/SecretaryController.php` & `app/Http/Controllers/DoctorController.php`
- Update `fetchPatientMedhistory()` and `fetchPatientHistory()`:
  - Resolve patient's master record first to collect all possible identifiers: `pxrefno`, `pincode`, and name + birthday.
  - Query `pxwalkinconsultation` using multi-key matching so past visits are never omitted even if `pincode` is null or `pxrefno` differs.

#### [MODIFY] `resources/js/doctor.js` & `resources/js/pages/doctor/consultation/form.js`
- Export `loadMedicalHistory()` to `window.loadMedicalHistory` and guard execution with safe checks.
- Prevent `ReferenceError` crashes when `doctor.js` executes.

---

### Component 7: Secretary Console Responsiveness

#### [MODIFY] `resources/views/pages/secretary/queue.blade.php`
- Replace rigid `<div style="min-width: 45rem;">` with responsive Bootstrap grid / flexbox (`row`, `col-12 col-xl-5`, `col-12 col-xl-7`).
- Flow structure:
  - Patient Queue (`#patients_queue_card`)
  - Patient Consultation Details (`#consultation_card`)
  - Patient Masterlist (`#patient_masterlist_card`) positioned cleanly with collapsible toggle and fluid tables.
- Adjust tables with `table-responsive` and fluid layout so nothing breaks on laptops or smaller viewports.

---

## Verification Plan

### Automated Tests
```bash
# Run unit and feature tests
php artisan test --filter=SettlementAndMedicalHistoryTest
php artisan test --filter=DoctorConsultationHistoryFixTest
```

### Manual Verification
1. **Settlement Modal**:
   - Open settlement modal: verify LOA Ref # in HMO card.
   - Test "Charge to PhilHealth Yakap" switch: verify Co-Pay input shows and remainder auto-populates.
   - Verify button reads "Record Payment" and saves data accurately.
2. **Printable Rx & Diagnostics**:
   - Click Print Rx and Print Diagnostics: verify PDF generates in landscape half-A4 (A5 landscape).
3. **Medical History**:
   - Update patient allergies (e.g., seafood) and injection history in Secretary Console; save.
   - Open Doctor Consultation Modal for the same patient: verify allergies and injections appear.
4. **Queue Financial Report**:
   - Verify financial summary at the bottom of the Patient Queue card reflects total gross, discounts, cash, card, and net.
   - Test "Print Financial Report" button and inspect consolidated PDF.
5. **Consultation History Fix**:
   - Select patient with prior consultations: verify consultation history table shows all records.
6. **Responsiveness**:
   - Resize browser window from 1920px down to 1024px and 768px: verify queue, consultation details, and masterlist stack cleanly without horizontal overflow.
