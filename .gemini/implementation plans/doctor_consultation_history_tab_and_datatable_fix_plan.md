# Implementation Plan: Fix DataTables Warning on medhistory_table & Separate Consultation History Tab to Card Header

## Goal Description
Resolve the `DataTables warning: table id=medhistory_table` on the Doctor Consultation Page and Consultation Modal. Restructure the doctor's consultation modal by separating the **"Consultation History"** tab from the existing vertical clinical tabs and placing it in a dedicated card header alongside the **"Consultation"** tab.

---

## Background & Root Cause Analysis

### 1. Root Cause of `DataTables warning: table id=medhistory_table`
Investigation of `resources/js/pages/doctor/consultation/form.js`, `resources/js/pages/doctor/consultation.js`, and `resources/views/modals/consultation_modal.blade.php` revealed multiple contributing bugs:
1. **The `.destroy().clear()` Method Chaining Bug**:
   In `form.js` line 46:
   ```javascript
   if ($.fn.DataTable.isDataTable("#medhistory_table")) {
       $("#medhistory_table").DataTable().destroy().clear();
   }
   ```
   Calling `.destroy()` returns a plain jQuery object, NOT the DataTables API instance. Calling `.clear()` on the plain jQuery object throws `TypeError: ...destroy(...).clear is not a function`. This unhandled exception breaks execution and leaves DataTables in an un-cleared state, leading to subsequent `Cannot reinitialise DataTable` warnings. The proper DataTables 2 lifecycle call is `$("#medhistory_table").DataTable().clear().destroy();` followed by `$("#medhistory_table tbody").empty();`.
2. **Missing `defaultContent` & Missing Column Renderers**:
   In `form.js` line 56:
   ```javascript
   columns: [
       { data: 'photo_path' },
       { data: 'consultation_date' }
   ]
   ```
   When any consultation row has a `null` or `undefined` `photo_path` (which is standard for walk-in patients without a captured webcam or uploaded photo), DataTables displays a browser alert:
   `DataTables warning: table id=medhistory_table - Requested unknown parameter 'photo_path' for row 0, column 0.`
   DataTables requires `defaultContent: ""` or an explicit renderer returning fallback HTML (`<img ... onerror="...">`).
3. **Premature Triggering Race Condition in `consultation.js`**:
   In `consultation.js` line 152:
   ```javascript
   consultationModal.show();
   $("#medhistory_btn").trigger("click");
   if (response.patient != null) {
       $("#consultationrefno").val(p.consultationrefno);
   ```
   Notice that line 152 triggers the medical history load **before** line 155 sets `$("#consultationrefno").val(...)`! Thus, `data: { consultationrefno: $("#consultationrefno").val() }` posts an empty string `""`, querying all records or returning unpredictable schemas.
4. **DataTables Error Mode**:
   Default DataTables configuration triggers native browser alert dialogs for any network hiccup or warning. Configuring `$.fn.dataTable.ext.errMode = 'none'` and logging diagnostic warnings to `console.warn` eliminates intrusive modal alert popups.

### 2. Architecture for Separating Consultation History to the Card Header
Currently, in `resources/views/modals/consultation_modal.blade.php`, the lower card (`<div class="card d-flex flex-row p-2 h-100 gap-3">`) lumps Consultation History together with the active consultation workflow pills:
- Consultation History
- Impressions & Diagnosis
- Rx & Instructions
- Diagnostic Requests
- Radiology & Laboratory
- Patient Charges

We will restructure this card to have a top **Card Header**:
```
+---------------------------------------------------------------------------------------------------+
|  [ Card Header ]                                                                                  |
|  [ Stethoscope ] Consultation (Active)     [ Clock ] Consultation History                         |
+---------------------------------------------------------------------------------------------------+
|  [ Tab 1: Consultation Body ]                                                                     |
|  +---------------------------+------------------------------------------------------------------+ |
|  | [V-Tab] Impressions & Diag | Chief Complaints, Impressions, Final Diagnosis, Admission Notes  | |
|  | [V-Tab] Rx & Instructions | Medicines Table, Dosages, Instructions, 1-Click Print Rx Link    | |
|  | [V-Tab] Diagnostic Reqs   | Laboratory & Diagnostic Procedures Table, 1-Click Print Requests | |
|  | [V-Tab] Radiology & Lab   | Uploaded Lab/Rad Result Document Cards & Previews                | |
|  | [V-Tab] Patient Charges   | Professional Fee, Services, Medicines, Supplies, Charges Ledger  | |
|  +---------------------------+------------------------------------------------------------------+ |
+---------------------------------------------------------------------------------------------------+
|  [ Tab 2: Consultation History Body ] (When 'Consultation History' tab is selected)               |
|  +----------------------------------------------------------------------------------------------+ |
|  | Previous Consultations Table (#medhistory_table)                                             | |
|  | [Action | Photo | Date | Chief Complaint | Diagnosis | Status | Doctor / Recorded By]          | |
|  | Clicking [View] opens full Consultation Details Viewer Modal with complete past record info  | |
|  +----------------------------------------------------------------------------------------------+ |
+---------------------------------------------------------------------------------------------------+
```

---

## User Review Required

> [!IMPORTANT]
> 1. **Default Tab Opening**:
>    When the doctor clicks "Proceed Consultation", the modal will open to the **"Consultation"** tab by default (with "Impressions & Diagnosis" active), allowing the doctor to immediately begin charting the current visit. The **"Consultation History"** tab is right beside it on the card header.
> 2. **Consultation History Data Viewing**:
>    Clicking the **"View"** button on any previous consultation history row in `#medhistory_table` will open the dedicated `view_consultation_details_modal`, showing the complete diagnosis, prescriptions, diagnostic requests, and charges from that visit.

---

## Proposed Changes

### Component 1: Doctor Consultation Modal View

#### [MODIFY] `resources/views/modals/consultation_modal.blade.php`
- Add a dedicated `<div class="card-header bg-light p-2 pb-0 border-bottom">` containing horizontal navigation tabs:
  - `#main_consultation_tab_btn` ("Consultation")
  - `#main_medhistory_tab_btn` ("Consultation History")
- Wrap the existing vertical clinical workflow in `#main_consultation_pane`:
  - Retain vertical pills: Impressions & Diagnosis (`#impDiagBtn`), Rx & Instructions (`#rx_sidebar_btn`), Diagnostic Requests (`#dReqsTabBtn`), Radiology & Laboratory (`#radLabTabBtn`), Patient Charges (`#patient_charge_tab_btn`).
  - Set Impressions & Diagnosis as the active pill by default.
- Create `#main_medhistory_pane` for the separated Consultation History:
  - Add header with title and refresh button.
  - Upgrade `#medhistory_table` with 7 comprehensive columns:
    1. Action (View Details button)
    2. Photo
    3. Consultation Date
    4. Chief Complaint
    5. Diagnosis
    6. Status
    7. Doctor / Recorded By

#### [MODIFY] `resources/views/pages/doctor/consultation.blade.php` & `resources/views/doctor.blade.php`
- Include `@include('modals.view_consultation_details')` so the consultation details modal can be launched when viewing past consultation records from the history table.

---

### Component 2: Frontend Scripts

#### [MODIFY] `resources/js/pages/doctor/consultation/form.js`
- Overhaul `loadMedicalHistory()`:
  - Check `$.fn.DataTable.isDataTable('#medhistory_table')` and execute `table.DataTable().clear().destroy();` followed by `$('#medhistory_table tbody').empty();`.
  - Pass `{ consultationrefno, pxrefno, pincode }` from modal state.
  - Configure `dataSrc: function (json) { return json.history || json.medhistory || json.data || []; }`.
  - Add explicit column definitions with `defaultContent: ""` and renderers:
    - Action: `<button type="button" class="btn btn-sm btn-outline-primary view-past-consultation-btn" data-index="${index}"><i class="fa-solid fa-eye me-1"></i> View</button>`
    - Photo: `<img>` tag with fallback to `/images/blank_photo.png`
    - Status: `<span class="badge ${badgeClass}">${status}</span>`
    - Dates: formatted date string
  - Attach click handler to `.view-past-consultation-btn` to load and display `#view_consultation_details_modal`.
  - Bind click handler on `#main_medhistory_tab_btn` and `#refresh_medhistory_btn` to call `loadMedicalHistory()`.
  - Bind `shown.bs.tab` event to call `columns.adjust()` on `#medhistory_table` when the tab becomes visible.

#### [MODIFY] `resources/js/pages/doctor/consultation.js`
- In `loadConsultModal`:
  - Populate `$("#consultationrefno").val(p.consultationrefno)` **before** tab operations.
  - Store `pincode`, `pxrefno`, and `consultationrefno` in modal data attributes.
  - Set `#main_consultation_tab_btn` and `#impDiagBtn` active.
  - If the doctor switches to `#main_medhistory_tab_btn`, `loadMedicalHistory()` executes with validated patient reference keys.

---

### Component 3: Feature Testing & Verification

#### [NEW] `tests/Feature/DoctorConsultationHistoryFixTest.php`
- Test that `/api/fetch_patient_history` accepts `consultationrefno`, `pxrefno`, and `pincode` and returns structured array with `'history'`, `'medhistory'`, and `'data'`.
- Test that records contain null-safe `photo_path` and `consultation_date`.

---

## Verification Plan

### Automated Tests
1. Run syntax verification:
   ```powershell
   php -l resources/views/modals/consultation_modal.blade.php
   php -l app/Http/Controllers/DoctorController.php
   php -l tests/Feature/DoctorConsultationHistoryFixTest.php
   ```
2. Build frontend assets with Vite:
   ```powershell
   cmd /c "npm run build"
   ```

### Manual Verification
1. Navigate to `/doctor/consultation` (Doctor Consultation Page).
2. Select a queued patient and click **"Proceed Consultation"**.
3. Verify that the Consultation Modal opens with **NO DataTables warning alert**.
4. Verify that the card header displays **"Consultation"** and **"Consultation History"** tabs.
5. In the "Consultation" tab, verify the vertical tabs (Impressions & Diagnosis, Rx & Instructions, Diagnostics, Rad/Lab, Charges) work smoothly.
6. Click the **"Consultation History"** tab on the card header:
   - Verify `#medhistory_table` loads cleanly with columns: Action, Photo, Date, Complaint, Diagnosis, Status, Doctor.
   - Click the **"View"** button on a history row to open the complete consultation details modal.
