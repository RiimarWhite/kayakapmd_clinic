# Implementation Plan — Consultation Tab and Detailed Consultation Modal Testing & Integration

## Context
The user requested:
> "test the consultation tab of every patients on the secretary dashboard (secretary and admin logins) and doctors dashboard and check if it is able to create some detailed modal for their consultation details"

Following **rules.md** and **skills.md**, this plan outlines the testing, gap analysis, and implementation required to ensure that:
1. On the **Secretary Dashboard** (for both **Secretary** and **Admin** logins), the consultation tab of every patient properly displays consultation histories and opens the dedicated, comprehensive Consultation Details Modal (`#view_consultation_details_modal`).
2. On the **Doctor Dashboard** (including Doctor Dashboard, Consultation page, and Patients Masterlist), the consultation tab and histories allow opening the comprehensive Consultation Details Modal.
3. Any missing Action columns, missing modal includes, null pointer errors on Admin logins, or data field mapping mismatches in prescriptions and diagnostics are resolved.

---

## Identified Issues & Required Enhancements

### 1. Admin Login on Secretary Dashboard Issues
- **`SecretaryController::queuePage()`**: Currently retrieves assigned doctors only via `auth()->guard('secretary')->user()`. When an Admin logs in, `$secUser` is null, resulting in an empty doctor list on `/secretary/queue`.
- **`SecretaryController::fetchDoctorsFromSecretary()`**: Directly calls `auth()->guard('secretary')->user()->secrefno`, causing an uncaught "Attempt to read property 'secrefno' on null" exception when called by an Admin.
- **Fix**: When `auth()->guard('admin')->check()` is true, fetch all active doctors cleanly.

### 2. Missing "View Details" Actions in Patient Consultation History Modals
- **`#patientMedhistoryModal` (Patient Consultation History Modal)**:
  - Invoked from Secretary queue masterlist (`.btn_px_history`) and Doctor patient masterlist (`.masterlist-history`).
  - Table `#medhistorytable` currently only renders 6 columns: `photo`, `date`, `reason`, `status`, `recordedby`, `recordeddate`.
  - **Issue**: It lacks an **Action** column and button to open `#view_consultation_details_modal`.
  - **Fix**: Add a dedicated "Action" column (`<button class="view-consultation-details-btn">View</button>`) to `#medhistorytable` in both `resources/js/pages/secretary/queue.js` and `resources/js/pages/doctor/patients.js`.
- **`#viewPatientModal` (Comprehensive Patient Details Modal)**:
  - Tab 4 ("Medical History" / `#view_medhistory_table`).
  - **Issue**: Table `#view_medhistory_table` lacks an Action column with View button, preventing modal opening from the patient details dialog.
  - **Fix**: Add the Action column to `resources/views/modals/view_patient.blade.php` and populate it in `doctor/patients.js`.

### 3. Missing Modal Include on Doctor Patient Masterlist & Dashboard
- **`resources/views/pages/doctor/patients.blade.php`**: Does not include `@include('modals.view_consultation_details')`. When viewing consultation history from the Patients Masterlist, the details modal does not exist in the DOM.
- **`resources/views/pages/doctor/dashboard.blade.php`**: Today's Patients table lacks an Action button to view consultation details, and does not include the details modal.
- **Fix**: Include `@include('modals.view_consultation_details')` in both views and wire up the View button in `resources/js/pages/doctor/dashboard.js`.

### 4. Rx and Diagnostics Field Name Mapping in Queue Details Modal
- In `queue.js`, `openConsultationDetailsModal` attempts to read `item.medname` and `item.sig` for prescriptions, and `item.requestname` for diagnostics.
- However, `StocksLedgerModel` records from `fetchPatientMedhistory` return `item_dscr`, `remarks`, and `dispensed_flag`.
- **Fix**: Add complete fallback logic (`item.item_dscr || item.medname`, `item.remarks || item.sig`, `item.dispensed_flag == 1 || item.dispensed == 1`) so prescription names and diagnostic names never display as "N/A".

### 5. Stacked Modal Backdrop & Scroll Behavior
- When `#view_consultation_details_modal` is opened on top of `#consultation_modal`, `#patientMedhistoryModal`, or `#viewPatientModal`:
  - Set `style="z-index: 1070;"` on `#view_consultation_details_modal`.
  - Add an event handler on `hidden.bs.modal` to restore `modal-open` class to `document.body` if any parent modal is still open, ensuring smooth scrolling.

---

## Implementation Steps

1. **Update `app/Http/Controllers/SecretaryController.php`**:
   - Update `queuePage()` to check `auth()->guard('admin')->check()` and provide all doctors if admin.
   - Update `fetchDoctorsFromSecretary()` to return all doctors if admin.
2. **Update Blade Views**:
   - `resources/views/modals/view_consultation_details.blade.php`: Add `style="z-index: 1070;"`.
   - `resources/views/modals/patient_masterlist.blade.php`: Add Action column to `#medhistorytable`.
   - `resources/views/modals/view_patient.blade.php`: Add Action column to `#view_medhistory_table`.
   - `resources/views/pages/doctor/patients.blade.php`: Add `@include('modals.view_consultation_details')`.
   - `resources/views/pages/doctor/dashboard.blade.php`: Add Action column to `#todays_patients_table` and `@include('modals.view_consultation_details')`.
3. **Update JavaScript Files**:
   - `resources/js/pages/secretary/queue.js`:
     - Add Action column rendering to `#medhistorytable`.
     - Wire up `.view-consultation-details-btn` to `openConsultationDetailsModal`.
     - Fix `rx_items` and `diagnostic_items` mapping with complete fallbacks.
     - Add modal-open restore on `hidden.bs.modal`.
   - `resources/js/pages/doctor/patients.js`:
     - Add Action column rendering to `#medhistorytable` and `#view_medhistory_table`.
     - Implement `openConsultationDetailsModal` and click delegation for `.view-consultation-details-btn`.
     - Add modal-open restore on `hidden.bs.modal`.
   - `resources/js/pages/doctor/dashboard.js`:
     - Add Action column rendering to `#todays_patients_table`.
     - Implement `openConsultationDetailsModal` and click delegation for `.view-consultation-details-btn`.
   - `resources/js/pages/doctor/consultation/form.js`:
     - Add modal-open restore on `hidden.bs.modal`.
4. **Testing**:
   - Create `tests/Feature/ConsultationTabAndDetailsModalTest.php` testing:
     - Secretary and Admin access to secretary queue and doctors.
     - Consultation history fetching with complete Rx, diagnostic, and charges payload.
     - Doctor dashboard and patient masterlist consultation endpoints.
   - Run `php artisan test` and compile assets with `npm run build`.
