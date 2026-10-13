# Detailed Survey & Analysis Report — Requirement R1: Consultation Details Modal Parity & Multi-Role Responsiveness

**Author**: `explorer_survey_1` (Read-only Survey & Exploration Agent)  
**Date**: October 12, 2026  
**Target Requirement**: R1 — Consultation Details Modal Parity & Multi-Role Responsiveness  
**Repository Working Directory**: `C:\xampp\htdocs\kayakapmd_clinic`  

---

## 1. Executive Summary

This survey provides an exhaustive investigation into Requirement **R1 (Consultation Details Modal Parity & Multi-Role Responsiveness)** across the KayakapMD Clinic codebase.

The consultation details modal (`#view_consultation_details_modal`, defined in `resources/views/modals/view_consultation_details.blade.php`) is designed to reproduce the doctor's full vertical-tab clinical layout (Demographics & Vitals, Impressions & Diagnosis, Rx & Instructions, Diagnostic Requests, Radiology & Laboratory attachments, and Patient Charges). It is utilized across four primary user journeys:
1. **Secretary Queue** (`/secretary/queue` via `resources/js/pages/secretary/queue.js` and `resources/views/pages/secretary/queue.blade.php`), accessible by both **Secretary** (`auth:secretary`) and **Admin** (`auth:admin`) users.
2. **Doctor Dashboard** (`/doctor/dashboard` via `resources/js/pages/doctor/dashboard.js` and `resources/views/pages/doctor/dashboard.blade.php`), accessible by Doctor and Admin users.
3. **Doctor Patient Masterlist** (`/doctor/patients` via `resources/js/pages/doctor/patients.js` and `resources/views/pages/doctor/patients.blade.php`), accessible by Doctor and Admin users.
4. **Doctor Consultation Form** (`/doctor/consultation` via `resources/js/pages/doctor/consultation/form.js` and `resources/views/pages/doctor/consultation.blade.php`), accessible by Doctor and Admin users.

### Key Survey Discoveries
1. **Multiple Divergent JavaScript Populators**:
   Four independent implementations populate `#view_consultation_details_modal`:
   - `openConsultationDetailsModal(record)` in `queue.js` (lines 695–862)
   - `openConsultationDetailsModal(record)` in `dashboard.js` (lines 487–657)
   - `openConsultationDetailsModal(record)` in `patients.js` (lines 875–1045)
   - `populateAndShowConsultationDetails(record)` in `form.js` (lines 350–520)
   These implementations suffer from naming discrepancies, differing field fallback keys (e.g., `foradmit` vs `isforadmission`, `admisionnotes` vs `foradmit_instructions`, `diagnosis` vs `finadiagnosis`), and naive array indexing (`list[0]` instead of matching `consultationrefno`).

2. **Trigger Selector Inconsistencies**:
   - `queue.js`, `dashboard.js`, and `patients.js` listen exclusively to `.view-consultation-details-btn`.
   - `form.js` listens exclusively to `.view-past-consultation-btn`.
   - None of the event listeners currently bind to `.btn-view-details`, a standard trigger class specified in project requirements.
   - The main Secretary Queue table (`#patients_queue_table`) currently lacks a direct "Details" button (it only has `.import-queue` with title "Show Info" which populates form tabs rather than opening the modal).
   - The Doctor Patient Masterlist table (`#masterlist_table`) currently lacks a direct "Details" action button (it only has `.masterlist-view` which opens `#viewPatientModal` and `.masterlist-history` which opens `#patientMedhistoryModal`).

3. **Modal Nesting & Backdrop Deficiencies**:
   Bootstrap 5 does not natively support nested/stacked modals. When `#view_consultation_details_modal` is opened on top of parent modals (`#consultation_modal`, `#patientMedhistoryModal`, `#viewPatientModal`):
   - Closing `#view_consultation_details_modal` causes Bootstrap to strip `modal-open` from `<body>` and reset inline overflow. While three files attempt to restore `$("body").addClass("modal-open")`, none of them ensure `overflow-y: auto` on the parent modal or set active focus.
   - `form.js` completely lacks a `hidden.bs.modal` listener for `#view_consultation_details_modal`, leaving `#consultation_modal` in an unscrollable and broken state when the child details modal closes.
   - None of the scripts prune orphan `.modal-backdrop` elements. If backdrops get desynchronized or if zero modals remain visible, lingering backdrop elements freeze the page with an unclickable dark overlay.
   - The child modal is styled with `z-index: 1070;`, but Bootstrap creates backdrops with default `z-index: 1050;`. This leaves the child backdrop *behind* parent modals (which sit at `z-index: 1055`), failing to visually stack the layers.

4. **Desktop-Rigid Styling Breaking Tablet/Mobile Responsiveness**:
   In `view_consultation_details.blade.php`, the modal container has hardcoded inline styles:
   `style="min-width: 85rem;"` (1360px) and `style="height: 52rem;"` (832px).
   On laptops (<1400px wide), tablets (768px–1024px), or screens with height < 900px, the modal overflows viewports horizontally and vertically, truncating buttons and clipping tab panes.

---

## 2. Component & Architecture Inventory

### 2.1 Blade View Hierarchy

| View File Path | Line Range | Inclusions & Roles |
|---|---|---|
| `resources/views/modals/view_consultation_details.blade.php` | Lines 1–242 | Dedicated consultation viewer modal (`#view_consultation_details_modal`). Contains 5 vertical tabs: Impressions & Diagnosis, Rx & Instructions, Diagnostic Requests, Radiology & Laboratory, Patient Charges. |
| `resources/views/pages/secretary/queue.blade.php` | Lines 804–808 | Includes `modals.view_consultation_details` at line 805. Extends `layouts.app`. Accessible to Secretary and Admin guards. |
| `resources/views/pages/doctor/dashboard.blade.php` | Lines 138–143 | Includes `modals.view_consultation_details` at line 142. Extends `layouts.app`. Accessible to Doctor and Admin guards. |
| `resources/views/pages/doctor/patients.blade.php` | Lines 45–56 | Includes `modals.view_consultation_details` at line 53, `modals.view_patient` at line 47, and `modals.patient_masterlist` at line 51. |
| `resources/views/pages/doctor/consultation.blade.php` | Lines 100–106 | Includes `modals.consultation_modal` at line 101, `modals.view_patient` at line 103, and `modals.view_consultation_details` at line 105. |
| `resources/views/modals/consultation_modal.blade.php` | Lines 1–740 | Doctor consultation modal (`#consultation_modal`, z-index 1055). Tab 2 hosts `#medhistory_table` for past consultation review. |
| `resources/views/modals/patient_masterlist.blade.php` | Lines 42–76 | Fullscreen modal (`#patientMedhistoryModal`). Contains `#medhistorytable` for patient consultation history. |
| `resources/views/modals/view_patient.blade.php` | Lines 1–237 | Patient masterlist details modal (`#viewPatientModal`). Tab 4 hosts `#view_medhistory_table` for previous consultations. |

### 2.2 JavaScript Assets & Event Handlers

| Script Path | Populator Function | Trigger Click Listeners | Dismissal Listener (`hidden.bs.modal`) |
|---|---|---|---|
| `resources/js/pages/secretary/queue.js` | `openConsultationDetailsModal(record)` (L695–862) | `$(document).on("click", ".view-consultation-details-btn")` (L868–898) | Lines 901–905 (restores `modal-open` class only, no backdrop cleanup or parent scroll enforcement) |
| `resources/js/pages/doctor/dashboard.js` | `openConsultationDetailsModal(record)` (L487–657) | `$(document).on("click", ".view-consultation-details-btn")` (L660–692) | Lines 695–699 (restores `modal-open` class only) |
| `resources/js/pages/doctor/patients.js` | `openConsultationDetailsModal(record)` (L875–1045) | `$(document).on("click", ".view-consultation-details-btn")` (L1048–1080) | Lines 1083–1087 (restores `modal-open` class only) |
| `resources/js/pages/doctor/consultation/form.js` | `populateAndShowConsultationDetails(record)` (L350–520) | `$(document).on("click", ".view-past-consultation-btn")` (L526–550) | **NONE** (missing `hidden.bs.modal` listener for `#view_consultation_details_modal`) |

### 2.3 Backend Controller Methods & API Routes

| HTTP Route | Controller & Action | Authentication Guard | Purpose & Payload |
|---|---|---|---|
| `POST /api/fetch_patient_medhistory` | `SecretaryController::fetchPatientMedhistory` (L670–856) | `web`, `auth:secretary,doctor,admin` | Multi-key patient consultation history lookup (`pincode`, `pxrefno`, `consultationrefno`, `patientname`, `birthday`). Enriches records with vitals, clinical impressions, `rx_items` (with `dispensed_flag`), `diagnostic_items`, itemized `charges`, and calculated `charges_total`. |
| `POST /api/fetch_todays_patients` | `DoctorController::fetchTodaysPatients` (L590–604) | `web`, `auth:doctor,admin` | Returns today's queued patients for authenticated doctor (`todays_patients_table`). Returns minimal patient records; full details are fetched asynchronously via `/api/fetch_patient_medhistory`. |
| `POST /api/fetch_consultation_masterlist` | `DoctorController::fetchAllPatients` (L382–500) | `web`, `auth:doctor,admin` | ServerSide DataTables query joining `pxmasterlist` with subquery `latestConsultation` (`pxwalkinconsultation`), providing `consultationrefno` and `consultation_date`. |
| `GET /print_pdf` | `DoctorController::printPDF` | `web`, `auth:secretary,doctor,admin` | Direct PDF stream generation for prescriptions (`type=rx`) and diagnostic requests (`type=diagnostics`). |

---

## 3. Deep Dive: Consultation Details Modal Parity Across Roles

### 3.1 Secretary Queue Console (`/secretary/queue`)
- **Authentication Parity**:
  - `SecretaryController::queuePage()` explicitly handles both Secretary and Admin users:
    ```php
    $secUser = auth()->guard('secretary')->user();
    $isAdmin = auth()->guard('admin')->check();
    if ($isAdmin) {
        $doctors = DoctorsProfileModel::select(['docrefno', 'docname'])->get();
    } else {
        $assigned_doctors = $secUser ? SecretaryDoctorsModel::where('secrefno', $secUser->secrefno)->pluck('docrefno') : collect();
        $doctors = DoctorsProfileModel::select(['docrefno', 'docname'])->whereIn('docrefno', $assigned_doctors)->get();
    }
    ```
  - For Admin sessions, `queue.blade.php` triggers a SweetAlert modal (`#secretary_select`) allowing the admin to switch context to any secretary, calling `/api/get_assigned_doctors`.
- **Trigger Buttons in Secretary View**:
  1. **Right Card — Consultation History Tab** (`#sec_medhistory_table`):
     Populated by `loadSecretaryMedhistory()`. Each row contains:
     `<button type="button" class="btn btn-sm btn-primary view-consultation-details-btn" data-index="${index}" title="View Consultation Details"><i class="fa-solid fa-eye me-1"></i> View</button>`.
  2. **Patient Masterlist History Modal** (`#patientMedhistoryModal`):
     Populated by `loadPatientMasterlistQueueTable()`. Each row in `#medhistorytable` contains:
     `<button type="button" class="btn btn-sm btn-primary view-consultation-details-btn" data-source="medhistorytable" data-index="${meta.row}" data-consultationrefno="${row.consultationrefno || ''}" title="View Consultation Details"><i class="fa-solid fa-eye me-1"></i> View</button>`.
  3. **Main Queue Table** (`#patients_queue_table`):
     In `loadPatientTable()` (lines 286–316), the table renders an eye icon `.import-queue` ("Show Info") that fills form tabs on the right card, but **lacks a direct button or dropdown action to open `#view_consultation_details_modal`**. Adding `.view-consultation-details-btn` / `.btn-view-details` with text "Details" ensures direct parity.

### 3.2 Doctor Dashboard (`/doctor/dashboard`)
- **Queue Table** (`#todays_patients_table`):
  In `dashboard.js` (lines 31–36), the table column 3 renders:
  ```javascript
  return `<button type="button" class="btn btn-sm btn-primary view-consultation-details-btn" data-index="${meta.row}" data-consultationrefno="${row.consultationrefno || ''}" title="View Consultation Details">
      <i class="fa-solid fa-eye me-1"></i> Details
  </button>`;
  ```
- **Async Data Enrichment**:
  When clicked, `dashboard.js` lines 669–688 inspects if `record.rx_items` is already present. Since `/api/fetch_todays_patients` returns minimal consultation columns, it performs an AJAX POST to `/api/fetch_patient_medhistory` with `{ consultationrefno: refno }`, obtaining the fully enriched consultation record.

### 3.3 Doctor Patient Masterlist (`/doctor/patients`)
- **Masterlist Table** (`#masterlist_table`):
  In `patients.js` lines 74–95, actions column currently renders:
  1. `.masterlist-view`: View Patient Details (opens `#viewPatientModal`)
  2. `.masterlist-history`: Consultation History (opens `#patientMedhistoryModal`)
- **Modal Viewers**:
  - In `#viewPatientModal` Tab 4 (`#view_medhistory_table`), rows render `.view-consultation-details-btn` with text "View".
  - In `#patientMedhistoryModal` (`#medhistorytable`), rows render `.view-consultation-details-btn` with text "View".
- **Identified Gap**:
  Neither `#masterlist_table` nor its modal tables have a button labeled "Details" with class `.btn-view-details`. Adding `.view-consultation-details-btn.btn-view-details` to `#masterlist_table` (using the joined `data.consultationrefno`) and labeling the buttons in the modals "Details" establishes complete parity with acceptance criteria.

---

## 4. Discrepancy Matrix Across Modal Populators

| Feature / Field | `queue.js` (`openConsultationDetailsModal`) | `dashboard.js` (`openConsultationDetailsModal`) | `patients.js` (`openConsultationDetailsModal`) | `form.js` (`populateAndShowConsultationDetails`) | Authoritative Target / Fix |
|---|---|---|---|---|---|
| **Function Name** | `openConsultationDetailsModal` | `openConsultationDetailsModal` | `openConsultationDetailsModal` | `populateAndShowConsultationDetails` | Standardize or alias both globally |
| **Trigger Selectors** | `.view-consultation-details-btn` | `.view-consultation-details-btn` | `.view-consultation-details-btn` | `.view-past-consultation-btn` | Bind `.view-consultation-details-btn, .btn-view-details, .view-past-consultation-btn` |
| **Admission Flag Check** | `parseInt(record.isforadmission) === 1` | `parseInt(record.foradmit \|\| record.isforadmission) === 1` | `parseInt(record.foradmit \|\| record.isforadmission) === 1` | `parseInt(record.foradmit \|\| record.isforadmission) === 1` | `parseInt(record.foradmit \|\| record.isforadmission) === 1` (matches DB column `foradmit`) |
| **Admission Notes** | `record.admisionnotes \|\| record.instructions` | `record.foradmit_instructions \|\| record.admisionnotes \|\| record.instructions` | `record.foradmit_instructions \|\| record.admisionnotes \|\| record.instructions` | `record.foradmit_instructions \|\| record.instructions` | `record.foradmit_instructions \|\| record.admisionnotes \|\| record.instructions \|\| ''` |
| **Diagnosis Field** | `record.finadiagnosis` | `record.finadiagnosis` | `record.finadiagnosis` | `record.finadiagnosis \|\| record.diagnosis` | `record.finadiagnosis \|\| record.diagnosis \|\| ''` |
| **Dispensed Flag** | `item.dispensed == 1 \|\| item.dispensed_flag == 1` | `item.dispensed == 1 \|\| item.dispensed_flag == 1` | `item.dispensed == 1 \|\| item.dispensed_flag == 1` | `item.dispensed_flag == 1` | `item.dispensed == 1 \|\| item.dispensed_flag == 1` |
| **Async List Match** | Naive `list[0]` | Naive `list[0]` | Naive `list[0]` | Naive `list[0]` | Match `list.find(r => r.consultationrefno === refno) \|\| list[0]` |
| **Dismissal Listener** | Yes (basic `body.modal-open`) | Yes (basic `body.modal-open`) | Yes (basic `body.modal-open`) | **MISSING** (No listener) | Add robust backdrop & parent scroll handler |

---

## 5. Modal Nesting, Scrollability & Backdrop Overlay Dynamics

### 5.1 Root Causes of Bootstrap 5 Modal Stacking Failures
1. **Body Scroll Lock Stripping**:
   When any modal hides, Bootstrap 5 executes `Modal.prototype._hideModal()`. This sets `document.body.style.overflow = ''` and removes the CSS class `modal-open`. When `#view_consultation_details_modal` is dismissed, Bootstrap's handler executes and strips body locks even though a parent modal (`#consultation_modal`, `#patientMedhistoryModal`, `#viewPatientModal`) is still open.
2. **Loss of Parent Modal Scrollability**:
   In Bootstrap 5, scrollable modals rely on `.modal { overflow-y: auto; }`. When the child modal closes, keyboard focus and wheel event capture on the parent modal can be interrupted, causing parent modal contents to lock up.
3. **Orphan `.modal-backdrop` Overlays**:
   When nested modals open, Bootstrap creates multiple `.modal-backdrop` elements. If modal instances are destroyed or re-created via `getOrCreateInstance`, backdrops can become desynchronized. If an extra backdrop remains in the DOM with `position: fixed; inset: 0;`, it intercepts all pointer and scroll events.
4. **Z-Index Layer Inversion**:
   In `view_consultation_details.blade.php`:
   - `#view_consultation_details_modal` is hardcoded to `z-index: 1070;`.
   - Parent modals (`#consultation_modal`, `#patientMedhistoryModal`, `#viewPatientModal`) operate at default `z-index: 1055;`.
   - Bootstrap generates `.modal-backdrop` with default `z-index: 1050;`.
   - Because `1050 < 1055`, the second backdrop sits behind the parent modal rather than shading the space between the parent modal and the details modal.

### 5.2 Unified Nested Modal & Backdrop Handler Architecture
To permanently resolve this behavior across all roles and pages, implement a centralized event lifecycle handler in JavaScript (e.g. in `resources/js/helpers/modal-helper.js` imported by `app.js` and mirrored across page scripts):

```javascript
// 1. Stacking Z-Index Management when opening #view_consultation_details_modal
$(document).on('shown.bs.modal', '#view_consultation_details_modal', function () {
    const $otherModals = $('.modal.show').not('#view_consultation_details_modal');
    if ($otherModals.length > 0) {
        // Position backdrop between parent modal (1055) and child modal (1070)
        $('.modal-backdrop').last().css('z-index', 1065);
    }
});

// 2. Dismissal Lifecycle & Scroll Restoration
$(document).on('hidden.bs.modal', '#view_consultation_details_modal', function () {
    const $remainingModals = $('.modal.show');
    if ($remainingModals.length > 0) {
        // Parent modal is still active: maintain body lock and restore parent scroll
        $('body').addClass('modal-open').css('overflow', 'hidden');
        const $topModal = $remainingModals.last();
        $topModal.css('overflow-y', 'auto').focus();

        // Prune excess backdrops so backdrops match visible modal count
        const $backdrops = $('.modal-backdrop');
        if ($backdrops.length > $remainingModals.length) {
            $backdrops.slice($remainingModals.length).remove();
        }
        $('.modal-backdrop').last().css('z-index', 1050);
    } else {
        // No modals remain open: clean up all backdrops and reset body styles
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css({ 'overflow': '', 'padding-right': '' });
    }
});

// 3. Safety Catch-All for Any Dismissed Modal
$(document).on('hidden.bs.modal', '.modal', function () {
    if ($('.modal.show').length === 0) {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css({ 'overflow': '', 'padding-right': '' });
    }
});
```

---

## 6. Responsiveness Analysis

### 6.1 Viewport Truncation in `view_consultation_details.blade.php`
- **Line 8**: `<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl" style="min-width: 85rem;">`
  - `min-width: 85rem` is equivalent to 1360px.
  - On standard laptop displays (1366×768) and tablets (768px, 1024px), the modal dialog overflows the viewport horizontally by up to 592px, clipping the modal header close button and right-hand content.
- **Line 9**: `<div class="modal-content" style="height: 52rem;">`
  - `height: 52rem` is equivalent to 832px.
  - On displays with viewport height < 900px (such as 1366×768 or 1280×800 laptops), the modal overflows the vertical boundary, pushing the modal footer ("Close") offscreen.
- **Line 80**: `<div class="card d-flex flex-row p-2 h-100 gap-3 border shadow-sm">`
  - Hardcoded `flex-row` forces the vertical navigation pills (`w-25`) and tab panes (`w-75`) into side-by-side columns even on mobile screens.

### 6.2 Proposed Responsive CSS/HTML Enhancements
1. Replace fixed `min-width: 85rem;` with fluid, constrained boundaries:
   `style="min-width: min(85rem, 95vw); max-width: 95vw;"`
2. Replace fixed `height: 52rem;` with max-height constraint:
   `style="height: min(52rem, 90vh); max-height: 90vh;"`
3. Add responsive flex classes for tab layouts:
   `<div class="card d-flex flex-column flex-md-row p-2 h-100 gap-3 border shadow-sm">`
   `<nav class="nav nav-pills d-flex flex-row flex-md-column w-100 w-md-25 gap-1">`
   `<div class="tab-content d-flex overflow-y-auto w-100 w-md-75 px-md-3">`

---

## 7. Actionable Implementation & Verification Plan

### 7.1 Proposed Changes Summary
1. **Unify Populator & Button Trigger Logic**:
   - In `queue.js`: Add `foradmit` / `foradmit_instructions` fallbacks; match record by `consultationrefno`; bind both `.view-consultation-details-btn` and `.btn-view-details`.
   - In `dashboard.js`: Match record by `consultationrefno`; bind both trigger classes.
   - In `patients.js`: Add "Details" button to `#masterlist_table` actions; match record by `consultationrefno`; bind both trigger classes.
   - In `form.js`: Add `hidden.bs.modal` listener; bind `.view-consultation-details-btn, .btn-view-details, .view-past-consultation-btn`.
2. **Backdrop & Scroll Preservation**:
   - Implement the 3-step modal stacking and orphan backdrop cleanup handler across all four JS files and in `resources/js/helpers/` or `app.js`.
3. **Responsive Modal Layout**:
   - Update `resources/views/modals/view_consultation_details.blade.php` to replace rigid rem dimensions with `min(..., 95vw)` and `min(..., 90vh)`.

### 7.2 Verification Commands
- `php artisan test --filter=ConsultationTabAndDetailsModalTest`
- `php artisan test` (verify all 99+ test suites pass with 0 regressions)
- `npm.cmd run build` (verify Vite production bundle builds cleanly)
