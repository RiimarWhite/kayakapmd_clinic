# Project: KayakapMD Clinic — System-Wide Enhancements & Workflows

## Architecture
- Request Flow:
  - Route (`routes/web.php`) -> Controller (`SecretaryController`, `DoctorController`, `ConsultationController`) -> Blade Views (`queue.blade.php`, `dashboard.blade.php`, `consultation.blade.php`)
  - Client-side JS (`queue.js`, `dashboard.js`, `patients.js`, `form.js`, `doctors.js`, `app.js`) -> API Route (`routes/api.php`) -> Controller / Service -> Eloquent Model (`ConsultationModel`, `PatientMasterlist`, `DoctorsProfileModel`, etc.) -> JSON Response / PDF Stream
- Responsive Form Architecture:
  - Bootstrap 5.3 grid layout (`row g-2` with responsive `col` breakpoints) replacing rigid `d-flex` layouts.
- Modal Architecture:
  - `#view_consultation_details_modal` dedicated modal with 6 vertical tabs (including Medical History).
  - `#patient_consultations_history_modal` for patient past consultation history.
  - Dedicated `#preview_modal` for laboratory and radiology file previews.
  - Strict nested modal backdrop & focus management (`document.activeElement.blur()`).
- Theme Architecture:
  - Bootstrap 5.3 `data-bs-theme` attribute on `<html>`.
  - Immediate inline script in `<head>` to prevent FOUC.
  - Event listener in `app.js` with `localStorage` persistence and dark mode styling overrides.

## Feature Inventory
| # | Feature | Description | Milestone | Source |
|---|---------|-------------|-----------|--------|
| 1 | Printable CO-PAY Header & Column Realignment | Rename 'Total Paid' to 'CO-PAY', remove standalone 'Co-Pay' column, align patient collections under 'CO-PAY', update total colspan from 14 to 13 | M1 | R1 |
| 2 | Printable CO-PAY Summary Totals | Remove standalone copay summary card, keep collections under Total CO-PAY | M1 | R1 |
| 3 | Remove Details Button from Masterlist Action Column | Remove `.view-consultation-details-btn` from `#masterlist_table` action column in `patients.js` | M1 | R6 |
| 4 | Remove Details Button from Secretary Queue Action Column | Remove `.view-consultation-details-btn` from `#patients_queue_table` action column in `queue.js` | M1 | R2 |
| 5 | Secretary Patient Consultation Form Responsiveness | Refactor patient info and vitals fields from rigid `d-flex` to responsive Bootstrap grid (`row g-2`) in `queue.blade.php` | M2 | R2 |
| 6 | Yakap Medical History Button & Surgical Label Rename | Add button `#btn_yakap_medhistory_future` with tooltip/notice and rename 'Surgical History' to 'Surgical/Operations History' | M2 | R2 |
| 7 | Patient Classification Select & Dynamic Containers | Add select above Chief Complaints with 10 classifications and dynamic fields inserted after Temperature | M2 | R2 |
| 8 | Classification DB Migration & Data Dictionary Update | Defensive migration adding `lmp_date` and `delivery_procedure` to `pxwalkinconsultation`, update `ConsultationModel::$fillable` and data dictionary | M2 | R2 |
| 9 | Patient Type Label Rename | Rename dropdown option label 'PHIC' to 'PHIC YAKAP' (preserving 'phic' value) | M2 | R2 |
| 10 | Radiology & Laboratory File Upload & Preview | Fix parameter mapping in `ConsultationController.php` (`radiologypath`/`laboratorypath`), handle file saving and dedicated `#preview_modal` viewing | M2 | R2 |
| 11 | Consultation Details Modal Medical History Tab | Add Medical History vertical tab in `view_consultation_details.blade.php` and populate from medhistory API | M2 | R2 |
| 12 | Admin Doctor Management Route Authorization & De-duplication | Fix route collision in `routes/api.php` so admins can manage doctor schedules and questions without 401 | M3 | R3 |
| 13 | Admin Doctor Management Missing Question Methods | Implement `editQuestion` and `deleteQuestion` in `SecretaryController.php` and register API routes | M3 | R3 |
| 14 | Admin Doctor Profile & User Resolution | Fix `createQuestion` and `createSchedule` to resolve admin/secretary users and doctor names (`docfname`, `docmname`, `doclname`) | M3 | R3 |
| 15 | Admin Doctor Management Table Refresh | Ensure schedule and question tables refresh cleanly upon creation/editing in `doctors.js` | M3 | R3 |
| 16 | Doctor Dashboard DataTables Column Count Fix | Align placeholder `<tbody>` cells (4 `<td>`) with header columns in `#todays_patients_table` | M4 | R4 |
| 17 | Doctor Dashboard Consultation History Modal Navigation | Clicking row or Details in Today's Patients opens `#patient_consultations_history_modal`, and row click opens full details modal | M4 | R4 |
| 18 | Doctor Consultation Queue Number Display | Include `queueno` in `DoctorController::fetchConsultationPatients` and display Queue Number next to Patient Name | M4 | R5 |
| 19 | Doctor Consultation Vertical Tab Fix | Fix unclosed `</div>` container in `#consultation_modal` restoring Medical History vertical tab layout | M4 | R5 |
| 20 | Focus Unblurring on Modal Transitions | Execute `document.activeElement.blur()` on modal show/hide to eliminate `Blocked aria-hidden` console warnings | M4 | R5 |
| 21 | Doctor Consultation Dynamic Classification & Questions | Display classification fields and answered doctor questions in `#consultation_modal` | M4 | R5 |
| 22 | Global Header Theme Switcher UI & Storage | Dropdown in navbar (Dark, Light, System) with `localStorage` persistence and `<head>` FOUC prevention | M5 | R7 |
| 23 | Bootstrap 5.3 Theme Styling & Overrides | Dark mode CSS overrides for cards, modals, tables, and form inputs | M5 | R7 |
| 24 | E2E Testing Suite (Tiers 1-4) | Opaque-box requirement-driven test suite verifying all R1-R7 features | ME2E | R8 |
| 25 | 12-Step Simulated QA Workflow & Audit | End-to-end 12-step simulated workflow, zero errors in `laravel.log`, adversarial hardening, forensic integrity audit | M6 | R8 |

## Milestones
| # | Name | Scope | Dependencies | Status |
|---|------|-------|-------------|--------|
| M1 | Financial Report Printable & Action Cleanups | Features 1, 2, 3, 4: Update financial report table columns & totals, remove Details buttons from Masterlist & Queue | none | DONE |
| M2 | Secretary Console Responsiveness, Classification & Files | Features 5, 6, 7, 8, 9, 10, 11: Bootstrap grid refactor, classification fields, migration, data dictionary, attachment uploads & preview modal, med history tab | none | DONE |
| M3 | Users Management: Admin Doctor Management | Features 12, 13, 14, 15: Fix API routes, controller authorization, profile name resolution, table refresh | none | DONE |
| M4 | Doctor Dashboard & Consultation UX Refactors | Features 16, 17, 18, 19, 20, 21: DataTables 4-td fix, history & details navigation, queue number display, unclosed div fix, focus unblurring | M2 interface contracts | DONE |
| M5 | Global Header Theme Switcher | Features 22, 23: Navbar dropdown, localStorage, `<head>` script, Bootstrap 5.3 CSS dark mode overrides | none | DONE |
| ME2E | E2E Testing Track Suite Creation | Feature 24: Comprehensive opaque-box test suite across Tiers 1-4, publish TEST_READY.md | none | DONE |
| M6 | Final Milestone: 12-Step Simulated QA & Verification | Feature 25: Pass 100% E2E tests, execute 12-step simulated workflow, adversarial coverage hardening, forensic audit | M1, M2, M3, M4, M5, ME2E | PLANNED |

## Interface Contracts
### Secretary Console (M2) ↔ Doctor Consultation & Dashboard (M4)
- Table: `pxwalkinconsultation`
  - Columns: `classification` (VARCHAR), `subclassification` (VARCHAR), `gravida` (INT), `para` (INT), `abortion` (INT), `iufd` (INT), `died` (INT), `lmp_date` (DATE|null), `delivery_procedure` (VARCHAR|null), `pathologic` (TINYINT/BOOLEAN), `infacilitydelivery` (VARCHAR/TINYINT), `radiologypath` (VARCHAR|null), `laboratorypath` (VARCHAR|null).
- Model: `App\Models\ConsultationModel`
  - Fillable attributes include `lmp_date` and `delivery_procedure`.
- Routes:
  - `POST /api/save_consultation` accepts `radiologypath` and `laboratorypath` uploaded files and classification payload.
  - `GET /api/fetch_patient_medhistory` returns `laboratory_url` and `radiology_url` resolving to `route('preview.file')`.
- Modals:
  - `#preview_modal` with `#preview_content` accepts image/PDF URLs and supports clean backdrop dismissal.

### Admin Users Management (M3)
- Routes:
  - `POST /api/create_doctor_schedules` -> `SecretaryController::createSchedule` (guard: `admin,secretary`, parameter: `docid`, `day`, `start_time`, `end_time`)
  - `GET /api/fetch_doctor_schedules/{docid}` -> `SecretaryController::fetchDoctorSchedules`
  - `POST /api/edit_schedule` -> `SecretaryController::editSchedule`
  - `DELETE /api/delete_schedule/{id}` -> `SecretaryController::deleteSchedule`
  - `POST /api/create_question` -> `SecretaryController::createQuestion` (guard: `admin,secretary`, resolves `docfname`, `docmname`, `doclname` from `DoctorsProfileModel`)
  - `GET /api/fetch_doctor_questions/{docid}` -> `SecretaryController::fetchDoctorQuestions`
  - `POST /api/edit_question` -> `SecretaryController::editQuestion`
  - `DELETE /api/delete_question/{id}` -> `SecretaryController::deleteQuestion`

## Code Layout
### Exclusive Write Boundaries:
- **Worker M1**:
  - `resources/views/printables/financial_report_print.blade.php`
  - `resources/js/pages/doctor/patients.js`
- **Worker M2**:
  - `resources/views/pages/secretary/queue.blade.php`
  - `resources/js/pages/secretary/queue.js`
  - `app/Http/Controllers/ConsultationController.php`
  - `app/Models/ConsultationModel.php`
  - `database/migrations/2026_10_12_000001_add_classification_fields_to_pxwalkinconsultation.php`
  - `.gemini/database/kayakapmdv2_data_dictionary.md`
  - `resources/views/modals/view_consultation_details.blade.php`
- **Worker M3**:
  - `routes/api.php`
  - `app/Http/Controllers/SecretaryController.php`
  - `resources/js/pages/admin/doctors.js`
  - `resources/js/pages/secretary/secretary-management.js`
- **Worker M4**:
  - `resources/views/pages/doctor/dashboard.blade.php`
  - `resources/js/pages/doctor/dashboard.js`
  - `app/Http/Controllers/DoctorController.php`
  - `resources/views/pages/doctor/consultation.blade.php`
  - `resources/views/modals/consultation_modal.blade.php`
  - `resources/js/pages/doctor/consultation.js`
  - `resources/js/pages/doctor/form.js`
- **Worker M5**:
  - `resources/views/components/navbar.blade.php`
  - `resources/views/layouts/app.blade.php`
  - `resources/js/app.js`
  - `resources/css/app.css`
- **Worker ME2E**:
  - `tests/Feature/SystemEnhancementsE2ETest.php`
  - `TEST_INFRA.md`
  - `TEST_READY.md`
- **Worker M6**:
  - Verification logs, tests execution, simulated workflow runner
