# KayakapMD Clinic — Test Infrastructure & 4-Tier Verification Specification (TEST_INFRA.md)

<!--
  Detailed Comment: Authoritative test infrastructure guide and specification for
  KayakapMD Clinic System-Wide Enhancements & End-to-End Workflows.
  Defines testing philosophy, environment configuration, 4-tier methodology,
  feature inventory mapping, and standardized execution commands.
-->

## 1. Test Philosophy & Core Principles

The KayakapMD Clinic test infrastructure guarantees high reliability, clinical safety, and regressions prevention across multi-role workflows (Secretary, Doctor, Admin). The testing architecture adheres to these four foundational principles:

### 1.1 Opaque-Box Requirement-Driven Verification
All test cases derive their expectations, assert conditions, and acceptance criteria strictly from the authoritative specifications in `PROJECT.md` and `ORIGINAL_REQUEST.md`. Tests evaluate observable behavior (HTTP response codes, JSON payloads, Blade view markup, database persistence, and document streams) rather than internal implementation mechanics.

### 1.2 Absolute Test Isolation & Independence
Every test is self-contained and idempotent:
- Tests operate against an in-memory SQLite database (`:memory:`) managed by `Illuminate\Foundation\Testing\RefreshDatabase`.
- Tables are freshly migrated on test suite bootstrap, ensuring zero inter-test state leakage.
- Seeders (`AdminSeeder`, `UserSeeder`) populate baseline user fixtures and clinical profiles cleanly per test execution.
- Order of test execution does not influence pass/fail outcomes.

### 1.3 Progressive Testability & Honest Defect Escalation
As a QA/Specialist agent adhering to strict write boundary constraints, test code never silently alters application source code to "force" tests to pass. When a test verifies a requirement that fails due to an application bug or an in-progress milestone, the test asserts the true requirement and records the defect for transparent escalation to the implementing agent.

### 1.4 Adversarial & Boundary Hardening
Tests exercise adverse and extreme conditions:
- Empty strings, null dates, extreme values, and missing request keys.
- Authentication and authorization boundary checks across `admin`, `secretary`, and `doctor` session guards.
- Malformed inputs, nonexistent foreign keys, and file upload edge cases.

---

## 2. Test Environment Configuration

| Setting | Configuration | Rationale |
|---|---|---|
| **Framework** | PHPUnit 11.5 / Laravel 11 Feature Harness | Native Laravel feature testing with `actingAs`, session handling, and JSON assertions |
| **Database** | SQLite In-Memory (`:memory:`) | Blazing fast execution (< 5 seconds for full suite) with guaranteed cleanup |
| **Authentication** | Multi-Guard (`secretary`, `doctor`, `admin`) | Matches application session architecture in `config/auth.php` |
| **Storage** | Fake Storage (`Storage::fake('private')`) | Safe simulation of laboratory and radiology file uploads |
| **Mail / Queue** | Array / Sync (`QUEUE_CONNECTION=sync`) | Immediate execution without external worker daemons |

---

## 3. 4-Tier Test Architecture

The test suite in `tests/Feature/SystemEnhancementsE2ETest.php` is structured into four distinct verification tiers:

```
┌────────────────────────────────────────────────────────────────────────┐
│  Tier 1: Feature Coverage (Isolated Happy Path per Feature Inventory)  │
├────────────────────────────────────────────────────────────────────────┤
│  Tier 2: Boundary & Corner Cases (Nulls, Missing Keys, 401s, 404s)     │
├────────────────────────────────────────────────────────────────────────┤
│  Tier 3: Cross-Feature Combinations (Pairwise Multi-Step Workflows)    │
├────────────────────────────────────────────────────────────────────────┤
│  Tier 4: Real-World Scenarios (End-to-End 12-Step Simulated QA Flow)   │
└────────────────────────────────────────────────────────────────────────┘
```

### Tier 1: Feature Coverage (Isolated Happy Path)
Verifies each feature independently to ensure foundational functionality meets documented specifications:
- **T1.1 Financial Report Printable Header & Colspan**: Validates removal of standalone Co-Pay column, rename of `Total Paid` to `CO-PAY`, alignment of collection totals under `CO-PAY`, and `colspan="13"`.
- **T1.2 Masterlist Action Column Details Button Removal**: Validates that `.view-consultation-details-btn` is removed from `#masterlist_table` actions in `patients.js`.
- **T1.3 Secretary Queue Action Column Details Button Removal**: Validates that `.view-consultation-details-btn` is removed from `#patients_queue_table` actions in `queue.js`.
- **T1.4 Yakap Medical History Button & Surgical Label Rename**: Validates presence of `#btn_yakap_medhistory_future` with tooltip and rename to `Surgical/Operations History`.
- **T1.5 Patient Classification Select & Dynamic Fields Persistence**: Validates 10 classification options and persistence of OB/GYN & New Born dynamic fields in `pxwalkinconsultation`.
- **T1.6 Database Migration Columns**: Validates existence of `lmp_date` and `delivery_procedure` in `pxwalkinconsultation` and data dictionary synchronization.
- **T1.7 Patient Type Label Rename**: Validates `<option value="phic">PHIC YAKAP</option>` in Secretary queue consultation card.
- **T1.8 Radiology & Laboratory Uploads & Preview URLs**: Validates file saving for `radiologypath` and `laboratorypath` and valid preview URLs via `preview.file`.
- **T1.9 Dedicated Consultation Details Modal Medical History Tab**: Validates Medical History vertical tab button and pane in `view_consultation_details.blade.php`.
- **T1.10 Admin Doctor Schedules CRUD API**: Validates admin can create, fetch, edit, and delete doctor schedules via `SecretaryController`.
- **T1.11 Admin Doctor Questions CRUD API**: Validates admin can create questions resolving `docfname`, `docmname`, `doclname`, fetch, edit, and delete questions.
- **T1.12 Doctor Dashboard Table Column Alignment**: Validates `#todays_patients_table` header columns match the 4 placeholder `<td>` cells to eliminate DataTables errors.
- **T1.13 Doctor Consultation Queue Number Display**: Validates queue number is returned alongside patient demographics in doctor consultation endpoints.
- **T1.14 Doctor Consultation Modal Vertical Tab Container Fix**: Validates unclosed container div in `#consultation_modal` is fixed so Medical History tab displays cleanly.
- **T1.15 Header Theme Switcher Dropdown & Markup**: Validates Dark, Light, System dropdown in navbar, `localStorage` persistence, and `<head>` FOUC prevention.

### Tier 2: Boundary & Corner Cases (Negative & Edge Conditions)
Tests system resilience against erroneous, unexpected, or malicious input:
- **T2.1 Empty/Missing Classification**: Null/empty classification values fall back safely to default without database or API exceptions.
- **T2.2 Null LMP Date**: Submitting null or empty string for `lmp_date` persists as `null` without Carbon date parsing errors.
- **T2.3 Nonexistent Doctor Reference**: Calling schedule or question creation with an invalid doctor reference returns HTTP 404 (Doctor profile not found).
- **T2.4 Invalid Schedule Parameters**: Submitting invalid day or missing required schedule time bounds triggers validation rejection.
- **T2.5 Missing File Attachments**: Saving consultations without file attachments preserves `null` paths safely; preview endpoints return 404 cleanly for absent files.
- **T2.6 Unauthorized Access Protection**: Unauthenticated requests to administrative doctor management endpoints are rejected with HTTP 401 Unauthorized.

### Tier 3: Cross-Feature Combinations (Integrated Pipelines)
Tests multi-component interactions across system boundaries:
- **T3.1 Secretary to Doctor Consultation Synchronization**: Secretary registers patient with classification and radiology upload -> Doctor console fetches exact consultation with populated classification fields, answered questions, and file preview URLs.
- **T3.2 Admin Doctor Management to Doctor Dashboard**: Admin configures doctor schedules and clinical questions -> Doctor user logs in and retrieves updated schedules and questions in dashboard.
- **T3.3 PHIC YAKAP Queue to Settlement to Financial Report**: Queuing patient as PHIC YAKAP persists status through consultation, settlement, and daily income printable report with `[PH Yakap]` badge.

### Tier 4: Real-World Scenarios (12-Step Simulated QA Workflow)
Executes an end-to-end, multi-role clinical simulation matching the exact 12-step user journey:
1. **Step 1**: Secretary adds patient to masterlist (`PatientMasterlist`).
2. **Step 2**: Secretary imports patient demographics into consultation form.
3. **Step 3**: Secretary fills clinical details, sets classification to `OBSTETRICS` with LMP date & delivery procedure, sets type to `PHIC YAKAP`, and uploads mock radiology file.
4. **Step 4**: Secretary saves consultation to queue; verifies sequential queue number assigned and queue table contains no Details button.
5. **Step 5**: Doctor Jackson logs in, views Dashboard today's queue (column count aligned), and inspects consultation history list modal.
6. **Step 6**: Doctor Jackson accesses Consultation page, verifies Queue Number displayed on list.
7. **Step 7**: Doctor Jackson proceeds to consultation; verifies Patient Info, OBSTETRICS classification fields, medical history tab, and radiology file preview.
8. **Step 8**: Doctor Jackson issues prescription (Rx) and diagnostic laboratory request.
9. **Step 9**: Doctor Jackson completes consultation (`status = FOR_BILLING`).
10. **Step 10**: Secretary retrieves completed consultation in queue.
11. **Step 11**: Secretary navigates to Payment Details tab and executes billing settlement with PhilHealth Yakap co-pay allocation.
12. **Step 12**: Secretary verifies printables: Prescription PDF, Diagnostics PDF, and Daily Financial Report with `CO-PAY` replacing `Total Paid` and `colspan="13"`.

---

## 4. Feature Inventory Mapping Matrix

| # | Feature Name | Target Scope | Tier & Test Method |
|---|---|---|---|
| 1 | Printable CO-PAY Header & Column Realignment | `resources/views/printables/financial_report_print.blade.php` | `test_tier1_financial_report_copay_header_and_colspan13` |
| 2 | Printable CO-PAY Summary Totals | `resources/views/printables/financial_report_print.blade.php` | `test_tier1_financial_report_copay_header_and_colspan13` |
| 3 | Remove Details Button from Masterlist Action Column | `resources/js/pages/doctor/patients.js` | `test_tier1_masterlist_details_button_removed` |
| 4 | Remove Details Button from Secretary Queue Action Column | `resources/js/pages/secretary/queue.js` | `test_tier1_queue_details_button_removed` |
| 5 | Secretary Patient Form Grid Responsiveness | `resources/views/pages/secretary/queue.blade.php` | `test_tier1_patient_form_grid_responsiveness` |
| 6 | Yakap Medical History Button & Surgical Label Rename | `resources/views/pages/secretary/queue.blade.php` | `test_tier1_yakap_medhistory_button_and_surgical_label_rename` |
| 7 | Patient Classification Select & Dynamic Containers | `resources/views/pages/secretary/queue.blade.php`, `queue.js` | `test_tier1_patient_classification_options_and_dynamic_fields_persistence` |
| 8 | Classification DB Migration & Data Dictionary Sync | `database/migrations/`, `ConsultationModel.php` | `test_tier1_migration_columns_lmp_date_and_delivery_procedure_in_database` |
| 9 | Patient Type Label Rename (PHIC YAKAP) | `resources/views/pages/secretary/queue.blade.php` | `test_tier1_patient_type_label_phic_yakap` |
| 10 | Radiology & Laboratory File Upload & Preview | `ConsultationController.php`, `routes/web.php` | `test_tier1_radiology_and_laboratory_file_upload_and_preview_urls` |
| 11 | Consultation Details Modal Medical History Tab | `resources/views/modals/view_consultation_details.blade.php` | `test_tier1_consultation_details_modal_medical_history_tab` |
| 12 | Admin Doctor Management Route Authorization | `routes/api.php`, `SecretaryController.php` | `test_tier1_admin_doctor_schedules_crud_api` |
| 13 | Admin Doctor Management Missing Question Methods | `SecretaryController.php`, `routes/api.php` | `test_tier1_admin_doctor_questions_crud_api_with_doctor_profile_resolution` |
| 14 | Admin Doctor Profile & User Resolution | `SecretaryController.php`, `DoctorsProfileModel.php` | `test_tier1_admin_doctor_questions_crud_api_with_doctor_profile_resolution` |
| 15 | Admin Doctor Management Table Refresh Wiring | `resources/js/pages/admin/users/doctors.js` | `test_tier1_admin_doctor_schedules_crud_api` |
| 16 | Doctor Dashboard DataTables Column Count Fix | `resources/views/pages/doctor/dashboard.blade.php` | `test_tier1_doctor_dashboard_table_column_count_alignment` |
| 17 | Doctor Dashboard Consultation History Navigation | `resources/js/pages/doctor/dashboard.js` | `test_tier3_secretary_saves_consultation_with_classification_files_and_doctor_loads_details` |
| 18 | Doctor Consultation Queue Number Display | `DoctorController.php`, `consultation.blade.php` | `test_tier1_doctor_consultation_queue_number_display` |
| 19 | Doctor Consultation Vertical Tab Overlap Fix | `resources/views/modals/consultation_modal.blade.php` | `test_tier1_doctor_consultation_unclosed_tab_container_fix` |
| 20 | Focus Unblurring on Modal Transitions | `resources/js/pages/doctor/consultation/form.js` | `test_tier1_focus_unblurring_modal_event_handlers` |
| 21 | Doctor Consultation Dynamic Classification & Questions | `consultation_modal.blade.php`, `form.js` | `test_tier3_secretary_saves_consultation_with_classification_files_and_doctor_loads_details` |
| 22 | Global Header Theme Switcher UI & Storage | `resources/views/components/navbar.blade.php`, `app.js` | `test_tier1_navbar_theme_switcher_dropdown_and_markup` |
| 23 | Bootstrap 5.3 Dark Mode Overrides & FOUC Prevention | `resources/views/layouts/app.blade.php`, `app.css` | `test_tier1_navbar_theme_switcher_dropdown_and_markup` |
| 24 | E2E Testing Suite (Tiers 1–4) | `tests/Feature/SystemEnhancementsE2ETest.php` | All test methods in suite |
| 25 | 12-Step Simulated QA Workflow Verification | End-to-end multi-role system flow | `test_tier4_complete_12_step_simulated_qa_clinical_workflow` |

---

## 5. Execution Guide & Quality Gates

### Running the E2E Feature Test Suite
Execute the dedicated test command from the repository root:
```bash
php artisan test --filter=SystemEnhancementsE2ETest
```

### Running the Full Test Suite
Execute all test suites across the project:
```bash
php artisan test
```

### Asset Compilation Validation
Verify production assets compile cleanly with zero errors:
```bash
npm run build
# or on Windows:
npm.cmd run build
```
