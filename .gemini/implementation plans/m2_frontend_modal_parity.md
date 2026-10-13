# Implementation Plan — Milestone 2: Frontend Modal Parity, Responsive Layout & Nested Backdrop Handling

**Author**: worker_m2 (Frontend Implementation Worker)  
**Date**: October 12, 2026  
**Milestone**: M2  

---

## 1. Overview & Objectives
Implement complete frontend modal parity and responsiveness for `#view_consultation_details_modal` across:
1. Secretary Queue (`resources/js/pages/secretary/queue.js`, `resources/views/pages/secretary/queue.blade.php`)
2. Doctor Dashboard (`resources/js/pages/doctor/dashboard.js`, `resources/views/pages/doctor/dashboard.blade.php`)
3. Doctor Patients Masterlist (`resources/js/pages/doctor/patients.js`, `resources/views/pages/doctor/patients.blade.php`)
4. Doctor Consultation Form (`resources/js/pages/doctor/consultation/form.js`)
5. Modal Template (`resources/views/modals/view_consultation_details.blade.php`)

---

## 2. Step-by-Step Execution Plan

### Step 1: Modal Viewport Responsiveness in `view_consultation_details.blade.php`
- Replace hardcoded `min-width: 85rem` with fluid responsive style: `min-width: min(85rem, 95vw); max-width: 95vw;`.
- Replace hardcoded `height: 52rem` with fluid responsive style: `min-height: min(45rem, 85vh); max-height: 90vh;`.
- Update tab container to support responsive flex layout (`flex-column flex-lg-row`) and enable smooth scrolling.
- Expand diagnostics table with category, notes, and requested date columns.

### Step 2: Update Secretary Queue (`queue.js`)
- Add direct "Details" button (`.view-consultation-details-btn.btn-view-details`) to `#patients_queue_table` actions column.
- Add `.btn-view-details` to `#sec_medhistory_table` and `#medhistorytable` buttons.
- Update click listener to handle `.view-consultation-details-btn, .btn-view-details`.
- In AJAX fallback, match exact `consultationrefno` via `list.find(item => item.consultationrefno === refno) || list[0]`.
- Add `resolveAttachmentUrl` helper supporting `/preview-file/` routes.
- Standardize admission checking (`foradmit || isforadmission` and `foradmit_instructions || admisionnotes || instructions`).
- Standardize prescription and diagnostics rendering.
- Implement 3-step nested backdrop and parent scroll cleanup.

### Step 3: Update Doctor Dashboard (`dashboard.js`)
- Add `.btn-view-details` to today's patients action button.
- Update click listener to handle `.view-consultation-details-btn, .btn-view-details`.
- In AJAX fallback, match exact `consultationrefno` via `list.find(item => item.consultationrefno === refno) || list[0]`.
- Add `resolveAttachmentUrl` helper.
- Standardize admission, prescription badges, and diagnostics rendering.
- Implement 3-step nested backdrop and parent scroll cleanup.

### Step 4: Update Doctor Patient Masterlist (`patients.js`)
- Add direct "Details" action button (`.view-consultation-details-btn.btn-view-details`) to `#masterlist_table` actions column.
- Add `.btn-view-details` to `#view_medhistory_table` and `#medhistorytable` buttons.
- Update click listener to handle `.view-consultation-details-btn, .btn-view-details`.
- In AJAX fallback, match exact `consultationrefno` via `list.find(item => item.consultationrefno === refno) || list[0]`.
- Add `resolveAttachmentUrl` helper.
- Standardize admission, prescription badges, and diagnostics rendering.
- Implement 3-step nested backdrop and parent scroll cleanup.

### Step 5: Update Doctor Consultation Form (`form.js`)
- Expose `window.openConsultationDetailsModal = populateAndShowConsultationDetails;`.
- Update click listener to handle `.view-consultation-details-btn, .btn-view-details, .view-past-consultation-btn`.
- In AJAX fallback, match exact `consultationrefno` via `list.find(item => item.consultationrefno === refno) || list[0]`.
- Add `resolveAttachmentUrl` helper.
- Standardize admission, prescription badges, and diagnostics rendering.
- Implement 3-step nested backdrop and parent scroll cleanup.

### Step 6: Build & Verification
- Run `npm.cmd run build` to verify clean Vite build (exit code 0).
- Run `php artisan test --filter=ConsultationTabAndDetailsModalTest` and `php artisan test` to verify zero regressions.
- Validate modal layout and event bindings.
