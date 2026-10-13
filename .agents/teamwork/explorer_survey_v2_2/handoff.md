# Survey & Architecture Mapping Report: Requirement R2 (Secretary Console)

## 1. Observation

### 1.1 Patient Consultation Form Responsiveness
In `resources/views/pages/secretary/queue.blade.php`:
- **Line 236**: Demographic container uses rigid flexbox:
  ```html
  <div class="d-flex gap-3">
      <div class="d-flex flex-column gap-2 w-100">
  ```
  Coupled with a fixed width/height patient picture box at line 335:
  ```html
  style="max-height: 12rem; min-height: 12rem; max-width: 12rem; min-width: 12rem;"
  ```
- **Lines 257–281**: Patient name row uses rigid flex layout:
  ```html
  <div class="d-flex">
      <div class="p-1">...First Name...</div>
      <div class="p-1">...Middle Name...</div>
      <div class="p-1">...Last Name...</div>
      <div class="p-1" style="max-width: 5rem;">...Suffix...</div>
  </div>
  ```
- **Lines 283–316**: Demographics row 2 uses rigid flex layout with pixel/rem bounds:
  ```html
  <div class="d-flex">
      <div class="p-1" style="max-width: 7.5rem;">...Sex...</div>
      <div class="p-1">...Birthdate...</div>
      <div class="p-1" style="max-width: 5rem;">...Age...</div>
      <div class="p-1">...Mobile No....</div>
      <div class="p-1">...Landline No....</div>
  </div>
  ```
- **Lines 318–330**: Contact/Address row uses rigid flex layout:
  ```html
  <div class="d-flex">
      <div class="p-1">...Email Address...</div>
      <div class="p-1 w-100">...Address...</div>
  </div>
  ```
- **Lines 426–464**: Chief Complaints & Vitals row 1 mixes Chief Complaints (`textarea`) with Weight and Height in a side-by-side flex container:
  ```html
  <div class="d-flex gap-2">
      <div class="w-100">...Chief Complaints...</div>
      <div class="d-flex flex-column gap-2" style="min-width: 12.5rem;">
          <div>...Weight...</div>
          <div>...Height...</div>
      </div>
  </div>
  ```
- **Lines 466–505**: Vitals row 2 squeezes 4 vital signs into an un-wrapped flex container:
  ```html
  <div class="d-flex gap-2">
      <div>...Temperature (max-width: 7.5rem)...</div>
      <div>...Respiratory Rate...</div>
      <div>...Pulse Rate...</div>
      <div>...Blood Pressure...</div>
  </div>
  ```
- **Lines 506–532**: File upload row uses rigid `w-50` flex:
  ```html
  <div class="d-flex gap-2">
      <div class="w-50">...Radiology Result...</div>
      <div class="w-50">...Laboratory Result...</div>
  </div>
  ```
- **Lines 534–545**: Patient Type row uses inline flex:
  ```html
  <div class="d-flex gap-2">
      <select class="form-select" name="patient_type" id="patient_type">...
      <select class="form-select" name="hmo_input" id="hmo_input">...
  </div>
  ```

---

### 1.2 Patient Queue Action Column ("Details" Button Removal)
In `resources/js/pages/secretary/queue.js`:
- **Lines 289–292** in function `loadPatientTable()` rendering `#patients_queue_table`:
  ```javascript
  <!-- Detailed Comment: Direct Details button triggering #view_consultation_details_modal for Secretary and Admin users -->
  <button type="button" class="btn btn-sm btn-primary view-consultation-details-btn btn-view-details" data-consultationrefno="${data.consultationrefno || ''}" data-pxrefno="${data.pxrefno || ''}" data-pin="${data.pin || data.pincode || ''}" title="View Consultation Details">
      <i class="fa-solid fa-file-medical me-1"></i> Details
  </button>
  ```
- Click listener at **line 908** in `queue.js`:
  ```javascript
  $(document).on("click", ".view-consultation-details-btn, .btn-view-details", function () { ... });
  ```
  This click listener is also utilized by `#sec_medhistory_table` (line 1046) and `#patientMedhistoryModal` (line 2299), so the listener itself must remain active.

---

### 1.3 Medical History Tab Updates
In `resources/views/pages/secretary/queue.blade.php`:
- **Lines 681–686**: Tab pane `#permanent_medhistory_info` header:
  ```html
  <div class="d-flex justify-content-between align-items-center mb-2">
      <span class="fw-bold text-dark"><i class="fa-solid fa-notes-medical text-primary me-1"></i> Permanent Patient Medical History</span>
      <button type="button" class="btn btn-sm btn-primary fw-bold" id="save_sec_medhistory_btn">
          <i class="fa-solid fa-floppy-disk me-1"></i> Save Medical History
      </button>
  </div>
  ```
- **Line 722**: Surgical History label:
  ```html
  <label class="form-label fw-bold text-dark small mb-1" for="sec_surgical_history">
      <i class="fa-solid fa-bandage me-1 text-warning"></i> Surgical History:
  </label>
  ```

---

### 1.4 Patient Classification & Dynamic Fields
In `resources/views/pages/secretary/queue.blade.php`:
- **Line 426**: Start of Chief Complaints area where classification select must be inserted.
- **Lines 466–505**: Temperature and vital signs section where dynamic classification fields must be inserted.
- **Reference visual templates observed in `temp/`**:
  - `temp/patient-classification-1.jfif`: Desktop app UI showing Patient Medical Information classification dropdown (`Gynecology`, `Medical`, `New Born`, `Obstetrics`, etc.) and `OB/GYN CASE HISTORY` group: `GRAVIDA`, `PARA`, `ABORTION`, `IUFD`, `DIED`, `LMP DATE` (date picker) + `[ ] Pathologic Case` checkbox.
  - `temp/patient-classification-2.jfif` & `3.jfif`: `New Born` classification displaying `OB/New Born Procedure` radio options `( ) Admission (from outside)` vs `(•) In-Facility Delivery`, Delivery Procedure dropdown (`Normal`, `Cesarean`, `Others`), and `[ ] Pathologic Case` checkbox.
  - `temp/patient-classification-4.jfif`: `Obstetrics` classification displaying `OB/GYN CASE HISTORY` (`GRAVIDA`, `PARA`, `ABORTION`, `IUFD`, `DIED`, `LMP DATE`), Delivery Procedure dropdown (`Normal`, `Cesarean`, `Others`), and `[ ] Pathologic Case` checkbox.
  - `temp/patient-classification-5.jfif`: `Gynecology` classification displaying `OB/GYN CASE HISTORY` (`GRAVIDA`, `PARA`, `ABORTION`, `IUFD`, `DIED`, `LMP DATE`) and `[ ] Pathologic Case` checkbox.
  - `temp/patient-classification-6.jfif`: `Medical` classification displaying sub-classification dropdown (`Adult`, `Pedia`).

- **Database inspection of `pxwalkinconsultation`**:
  - Artisan tinker schema inspection:
    - Present columns: `classification` (varchar 80), `subclassification` (varchar 80), `gravida` (double), `para` (double), `abortion` (double), `iufd` (double), `died` (double), `pathologic` (tinyint), `infacilitydelivery` (tinyint), `linkaccount` (varchar 20).
    - Missing columns: `lmp_date` (date) and `delivery_procedure` (varchar 50).
  - Data Dictionary: `.gemini/database/kayakapmdv2_data_dictionary.md` lines 591–687 documents `pxwalkinconsultation` without `lmp_date` and `delivery_procedure`.
  - Eloquent Model: `app/Models/ConsultationModel.php` lines 31–94 does NOT include `gravida`, `para`, `abortion`, `iufd`, `died`, `pathologic`, `infacilitydelivery`, `lmp_date`, or `delivery_procedure` in `$fillable`.

---

### 1.5 Patient Type Option Renaming
In `resources/views/pages/secretary/queue.blade.php`:
- **Lines 537–542**:
  ```html
  <select class="form-select" name="patient_type" id="patient_type">
      <option value="regular" selected>REGULAR</option>
      <option value="hmo">HMO</option>
      <option value="phic">PHIC</option>
      <option value="others">OTHERS</option>
  </select>
  ```

---

### 1.6 Radiology & Laboratory File Handling
In `app/Http/Controllers/ConsultationController.php`:
- **Lines 804–805** in `saveConsultation()`:
  ```php
  $consultationData['radiologypath'] = $request->hasFile('radiology_file') ? $request->file('radiology_file')->store('radiology_results', 'private') : null;
  $consultationData['laboratorypath'] = $request->hasFile('laboratory_file') ? $request->file('laboratory_file')->store('laboratory_results', 'private') : null;
  ```
  `queue.blade.php` file inputs are named `radiologypath` and `laboratorypath` (lines 512, 525). Therefore `$request->hasFile('radiology_file')` evaluates to false, causing uploaded files to be dropped upon initial save!
- **Lines 993–994** in `updateConsultation()`:
  ```php
  $consultationData['radiologypath'] = $request->hasFile('radiologypath') ? $request->file('radiologypath')->store('radiology_results', 'private') : $record->radiologypath;
  $consultationData['laboratorypath'] = $request->hasFile('laboratorypath') ? $request->file('laboratorypath')->store('laboratory_results', 'private') : $record->laboratorypath;
  ```
  Here `radiologypath` and `laboratorypath` are checked, revealing the parameter name inconsistency with `saveConsultation()`.

In `resources/js/pages/secretary/queue.js`:
- Buttons `#preview_radiology` and `#preview_laboratory` have NO click event handlers in `queue.js`.
- File inputs `#radiologypath` and `#laboratorypath` have NO change event handlers to enable or preview local files.
- In `.import-queue` (lines 1290–1408), `radiologypath` and `laboratorypath` are NOT attached to `#preview_radiology` or `#preview_laboratory`.
- `#preview_modal` is defined in `resources/views/modals/consultation_modal.blade.php` (line 717), but is NOT included in `queue.blade.php`.

---

### 1.7 Consultation Details Modal Tabs
In `resources/views/modals/view_consultation_details.blade.php`:
- **Lines 86–111**: Navigation pills only define 5 tabs:
  1. `vcd_pane_impdiag`: Impressions & Diagnosis
  2. `vcd_pane_rx`: Rx & Instructions
  3. `vcd_pane_dreqs`: Diagnostic Requests
  4. `vcd_pane_radlab`: Radiology & Laboratory
  5. `vcd_pane_charges`: Patient Charges
- Tab for **Medical History** is completely missing.
- Patient Classification and dynamic OB/GYN or New Born fields are not rendered.
- Answered Doctor's Questions are not displayed.

---

## 2. Logic Chain

1. **Responsiveness Refactoring**:
   - The current `queue.blade.php` uses multiple nested `d-flex` containers with fixed `max-width: 5rem`, `max-width: 7.5rem`, and `min-width: 12.5rem`.
   - On screens under 1400px (standard laptops) and tablet/mobile viewports, flex child elements overflow their cards, truncating text and causing horizontal scrollbars.
   - Refactoring to `<div class="row g-2">` with Bootstrap breakpoints (`col-12 col-sm-6 col-md-4 col-lg-3`, etc.) ensures fluid wrapping, consistent gutters, and full responsiveness across all viewport sizes without breaking form serialization.

2. **Queue Table "Details" Button**:
   - In `queue.js:289-292`, `#patients_queue_table` includes a "Details" button (`.view-consultation-details-btn`).
   - The user requirement explicitly dictates removing this button from the `patients_queue_table` action column.
   - Rows in the queue are imported into the active consultation form via the "Show Info" button (`.import-queue`).
   - Removing the button from lines 289–292 simplifies the action column while preserving the `.view-consultation-details-btn` event handler at line 908 for consultation history modals.

3. **Medical History Header & Surgical Operations Label**:
   - Adding `#btn_yakap_medhistory_future` in the header bar of `#permanent_medhistory_info` alongside `#save_sec_medhistory_btn` provides immediate visual parity with Yakap profiling while displaying a non-intrusive tooltip and SweetAlert notice ("For future updates only").
   - Renaming "Surgical History:" to "Surgical/Operations History:" matches standard clinical terminology and aligns with `profile-details.blade.php`.

4. **Patient Classification & Database Sync**:
   - The prompt requires 10 classifications: `MEDICAL`, `PEDIA`, `OBSTETRICS`, `GYNECOLOGY`, `NEW BORN`, `ORTHO`, `DENTISTRY`, `DERMA`, `HEMODIALYSIS`, `OTHERS`.
   - Visual reference files in `temp/` establish that 4 classifications trigger dynamic sub-forms:
     - `MEDICAL`: Sub-classification (`Adult`, `Pedia`)
     - `OBSTETRICS`: `gravida`, `para`, `abortion`, `iufd`, `died`, `lmp_date`, `delivery_procedure` (`Normal`, `Cesarean`, `Others`), and `pathologic` checkbox
     - `GYNECOLOGY`: `gravida`, `para`, `abortion`, `iufd`, `died`, `lmp_date`, and `pathologic` checkbox
     - `NEW BORN`: `infacilitydelivery` (radio: Admission vs In-Facility Delivery), `delivery_procedure` (`Normal`, `Cesarean`, `Others`), and `pathologic` checkbox
   - In `pxwalkinconsultation`, `classification`, `subclassification`, `gravida`, `para`, `abortion`, `iufd`, `died`, `pathologic`, and `infacilitydelivery` already exist.
   - `lmp_date` and `delivery_procedure` must be added via a defensive migration:
     `Schema::table('pxwalkinconsultation', function (Blueprint $table) { ... })`.
   - Per Rule 4 (Dual-Update Protocol), `.gemini/database/kayakapmdv2_data_dictionary.md` must be updated concurrently.
   - `ConsultationModel::$fillable` must be updated to include all classification attributes, preventing Eloquent mass-assignment silent drops.

5. **Patient Type Option Renaming**:
   - Changing the label of option `<option value="phic">PHIC</option>` to `<option value="phic">PHIC YAKAP</option>` preserves the underlying value `'phic'`.
   - Preserving `'phic'` ensures zero breaking changes to existing pricing tier lookups (`DoctorController::fetchPrice`), settlement calculations, and test assertions in `ConsultationChargesAndSettlementsTest.php`.

6. **Radiology & Laboratory File Handling**:
   - In `saveConsultation()`, checking only `'radiology_file'` and `'laboratory_file'` causes files submitted under `'radiologypath'` and `'laboratorypath'` to be ignored.
   - Normalizing both `saveConsultation()` and `updateConsultation()` to check `$request->file('radiologypath') ?: $request->file('radiology_file')` guarantees robust file persistence.
   - Extracting `#preview_modal` to `resources/views/modals/preview_modal.blade.php` and including it in `queue.blade.php` gives the Secretary console immediate access to document previews.
   - Hooking `#preview_radiology` and `#preview_laboratory` click handlers in `queue.js` enables instant preview of local files via `URL.createObjectURL(file)` or server-stored files via `route('preview.file')`.

7. **Consultation Details Modal Tab Expansion**:
   - Adding a 6th vertical tab (`#vcd_pane_medhistory`) to `resources/views/modals/view_consultation_details.blade.php` ensures complete parity with Doctor Consultation.
   - `openConsultationDetailsModal()` in `queue.js` will populate permanent medical history, patient classification badges, dynamic case history, and answered doctor questions.

---

## 3. Caveats

1. **Backwards Compatibility for `classification` Column**:
   - Historically, some tests and controllers stored patient billing types (`'REGULAR'`, `'PHIC'`, `'HMO'`) directly in `consultation->classification`.
   - When introducing clinical classifications (`MEDICAL`, `OBSTETRICS`, etc.), `ConsultationController` must defensively handle both:
     - Save clinical classification in `classification` when `patient_classification` is supplied.
     - Save billing patient type in `modeofpayment` (or synchronize accordingly).
     - Maintain fallback logic in `DoctorController` when resolving HMO/PHIC pricing tiers:
       `$classification = strtolower($request->patient_type ?? $consultation->modeofpayment ?? $consultation->classification ?? '');`
2. **`#preview_modal` Placement**:
   - To avoid duplicate modal IDs in the DOM, `#preview_modal` in `consultation_modal.blade.php` should be refactored into a single partial `@include('modals.preview_modal')` included in layout or target pages.
3. **No Unplanned File Writes**:
   - Per explorer role constraints, all code changes below are proposed architecture specifications. Implementation must be carried out by implementer agents.

---

## 4. Conclusion & Actionable Proposals

### 4.1 Proposed Code Changes for `resources/views/pages/secretary/queue.blade.php`

#### A. Patient Information Form (Demographics) Refactoring
**Replace Lines 235–331** with responsive Bootstrap grid:
```html
<!-- Patient Information -->
<div class="row g-3">
    <div class="col-12 col-lg-8 col-xl-9">
        {{-- Detailed Comment: Prominent Secretary Allergy Alert Banner --}}
        <div class="alert alert-danger py-1 px-3 mb-2 d-none align-items-center gap-2 border-danger" id="sec_allergy_alert_bar" role="alert">
            <i class="fa-solid fa-triangle-exclamation text-danger fs-5"></i>
            <div class="small">
                <strong class="text-danger">KNOWN ALLERGIES:</strong> <span id="sec_allergy_alert_text" class="fw-bold"></span>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-3 mb-2">
            <input type="hidden" name="pincode" id="pincode">
            <input type="hidden" name="consultationrefno" id="hidden_consultationrefno">
            <p class="text-secondary m-0 small"><span class="fw-bold">Consultation Ref #:</span> <span id="pxconsultationrefno" class="text-dark fw-semibold"></span></p>
            <p class="text-secondary m-0 small"><span class="fw-bold">Patient ID:</span> <span id="pxidno" class="text-dark fw-semibold"></span></p>
        </div>

        <fieldset readonly>
            {{-- Row 1: Patient Names --}}
            <div class="row g-2 mb-2">
                <div class="col-12 col-sm-6 col-md-4">
                    <label class="form-label fw-bold m-0 small" for="pxfname">First Name</label>
                    <input type="text" class="form-control form-control-sm" name="pxfname" id="pxfname">
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label fw-bold m-0 small" for="pxmname">Middle Name</label>
                    <input type="text" class="form-control form-control-sm" name="pxmname" id="pxmname">
                </div>
                <div class="col-12 col-sm-8 col-md-3">
                    <label class="form-label fw-bold m-0 small" for="pxlname">Last Name</label>
                    <input type="text" class="form-control form-control-sm" name="pxlname" id="pxlname">
                </div>
                <div class="col-12 col-sm-4 col-md-2">
                    <label class="form-label fw-bold m-0 small" for="pxsuffix">Suffix</label>
                    <input type="text" class="form-control form-control-sm" name="pxsuffix" id="pxsuffix">
                </div>
            </div>

            {{-- Row 2: Demographics & Contact --}}
            <div class="row g-2 mb-2">
                <div class="col-4 col-sm-3 col-md-2">
                    <label class="form-label fw-bold m-0 small" for="pxsex">Sex</label>
                    <select class="form-select form-select-sm" name="pxsex" id="pxsex">
                        <option value="M">M</option>
                        <option value="F">F</option>
                    </select>
                </div>
                <div class="col-8 col-sm-5 col-md-3">
                    <label class="form-label fw-bold m-0 small" for="pxbday">Birthdate</label>
                    <input type="date" class="form-control form-control-sm" name="pxbday" id="pxbday">
                </div>
                <div class="col-4 col-sm-4 col-md-2">
                    <label class="form-label fw-bold m-0 small" for="pxage">Age</label>
                    <input type="text" class="form-control form-control-sm" name="pxage" id="pxage">
                </div>
                <div class="col-8 col-sm-6 col-md-2-5 col-lg-3">
                    <label class="form-label fw-bold m-0 small" for="pxcellnumber">Mobile No.</label>
                    <input type="tel" class="form-control form-control-sm" name="pxcellnumber" id="pxcellnumber">
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label fw-bold m-0 small" for="pxlandlinenumber">Landline</label>
                    <input type="tel" class="form-control form-control-sm" name="pxlandlinenumber" id="pxlandlinenumber">
                </div>
            </div>

            {{-- Row 3: Email & Address --}}
            <div class="row g-2">
                <div class="col-12 col-md-4">
                    <label class="form-label fw-bold m-0 small" for="pxemail">Email Address</label>
                    <input type="email" class="form-control form-control-sm" name="pxemail" id="pxemail">
                </div>
                <div class="col-12 col-md-8">
                    <label class="form-label fw-bold m-0 small" for="pxaddress">Address</label>
                    <input type="text" class="form-control form-control-sm" name="pxaddress" id="pxaddress">
                </div>
            </div>
        </fieldset>
    </div>

    {{-- Patient Photo & Capture --}}
    <div class="col-12 col-lg-4 col-xl-3 d-flex flex-column align-items-center justify-content-center">
        <img class="border border-secondary rounded shadow-sm mb-2"
            style="height: 10rem; width: 10rem; object-fit: cover;"
            src="{{ asset('images/blank_photo.png') }}" alt="patient-image"
            id="patient_picture_preview">
        <div class="input-group input-group-sm justify-content-center" style="max-width: 12rem;">
            <input type="hidden" name="photo_path" id="photo_path">
            <input type="hidden" name="photo_base64" id="photo_base64">
            <button type="button" class="btn btn-sm btn-primary fw-bold" id="upload_patient_image">
                <i class="fa-solid fa-upload"></i> Upload
            </button>
            <button type="button" class="btn btn-sm btn-secondary" id="take_photo" title="Capture with Camera">
                <i class="fa-solid fa-camera"></i>
            </button>
        </div>
        <input class="d-none" type="file" accept="image/*" name="patient_image" id="patient_image">
    </div>
</div>
```

#### B. Classification Select, Chief Complaints & Vital Signs Refactoring
**Replace Lines 426–532** with responsive grid and insertion points:
```html
<div class="d-flex flex-column gap-2 mt-3">
    <div>
        <label class="form-label fw-bold m-0" for="questions">Questions:</label>
        <div class="d-flex flex-column" id="questions_container"></div>
    </div>

    {{-- Detailed Comment: Patient Classification dropdown inserted directly above Chief Complaints per Requirement R2 --}}
    <div class="mb-2">
        <label class="form-label fw-bold m-0 small" for="patient_classification">
            <i class="fa-solid fa-tags text-primary me-1"></i> Patient Classification
        </label>
        <select class="form-select form-select-sm" name="patient_classification" id="patient_classification">
            <option value="MEDICAL" selected>MEDICAL</option>
            <option value="PEDIA">PEDIA</option>
            <option value="OBSTETRICS">OBSTETRICS</option>
            <option value="GYNECOLOGY">GYNECOLOGY</option>
            <option value="NEW BORN">NEW BORN</option>
            <option value="ORTHO">ORTHO</option>
            <option value="DENTISTRY">DENTISTRY</option>
            <option value="DERMA">DERMA</option>
            <option value="HEMODIALYSIS">HEMODIALYSIS</option>
            <option value="OTHERS">OTHERS</option>
        </select>
    </div>

    {{-- Chief Complaints --}}
    <div class="mb-2">
        <label class="form-label fw-bold m-0 small" for="reason_for_consultation">Chief Complaints</label>
        <textarea class="form-control form-control-sm" rows="3" name="reason_for_consultation" id="pxreasonforconsultation" placeholder="State patient symptoms or chief complaints..."></textarea>
    </div>

    {{-- Vital Signs Responsive Grid --}}
    <div class="row g-2 mb-2">
        <div class="col-6 col-sm-4 col-md-2">
            <label class="form-label fw-bold m-0 small" for="weight">Weight</label>
            <div class="input-group input-group-sm">
                <input type="number" step="0.01" class="form-control form-control-sm" min="0.00" value="0.00" name="weight" id="pxweight">
                <select class="form-select form-select-sm" style="max-width: 4rem;" name="w_unit" id="pxweight_unit">
                    <option value="kg">kg</option>
                    <option value="lbs">lbs</option>
                </select>
            </div>
        </div>

        <div class="col-6 col-sm-4 col-md-2">
            <label class="form-label fw-bold m-0 small" for="height">Height</label>
            <div class="input-group input-group-sm">
                <input type="number" step="0.01" class="form-control form-control-sm" min="0.00" value="0.00" name="height" id="pxheight">
                <select class="form-select form-select-sm" style="max-width: 4.5rem;" name="h_unit" id="pxheight_unit">
                    <option value="cm">cm</option>
                    <option value="m">m</option>
                    <option value="ft">ft</option>
                </select>
            </div>
        </div>

        <div class="col-6 col-sm-4 col-md-2">
            <label class="form-label fw-bold m-0 small" for="temperature">Temperature</label>
            <div class="input-group input-group-sm">
                <input type="number" step="0.01" class="form-control form-control-sm" min="0.00" value="0.00" name="temperature" id="pxtemp">
                <select class="form-select form-select-sm" style="max-width: 4rem;" name="t_unit" id="pxtemp_unit">
                    <option value="C">°C</option>
                    <option value="F">°F</option>
                </select>
            </div>
        </div>

        <div class="col-6 col-sm-4 col-md-2">
            <label class="form-label fw-bold m-0 small" for="respiratory_rate">Resp. Rate</label>
            <input type="number" step="0.01" class="form-control form-control-sm" min="0.00" value="0.00" name="respiratory_rate" id="pxrespiratory">
        </div>

        <div class="col-6 col-sm-4 col-md-2">
            <label class="form-label fw-bold m-0 small" for="pulse_rate">Pulse Rate</label>
            <input type="number" step="0.01" class="form-control form-control-sm" min="0.00" value="0.00" name="pulse_rate" id="pxpulse">
        </div>

        <div class="col-12 col-sm-4 col-md-2">
            <label class="form-label fw-bold m-0 small" for="pxbpnumerator">Blood Pressure</label>
            <div class="input-group input-group-sm">
                <input type="number" class="form-control form-control-sm" min="0" placeholder="Sys" name="bp_numerator" id="pxbpnumerator">
                <span class="input-group-text p-1">/</span>
                <input type="number" class="form-control form-control-sm" min="0" placeholder="Dia" name="bp_denominator" id="pxbpdenominator">
            </div>
        </div>
    </div>

    {{-- Detailed Comment: Dynamic Classification Fields Container inserted immediately after Temperature & Vitals --}}
    <div id="classification_dynamic_container" class="card border border-info bg-light p-2 mb-2 d-none">
        {{-- 1. MEDICAL Dynamic Fields --}}
        <div id="dynamic_fields_medical" class="dynamic-class-pane d-none">
            <label class="form-label fw-bold m-0 small text-info"><i class="fa-solid fa-user-doctor me-1"></i> Medical Sub-classification</label>
            <select class="form-select form-select-sm w-auto" name="medical_subclassification" id="medical_subclassification">
                <option value="Adult" selected>Adult</option>
                <option value="Pedia">Pedia</option>
            </select>
        </div>

        {{-- 2. OB/GYN CASE HISTORY (Obstetrics & Gynecology) --}}
        <div id="dynamic_fields_obgyn" class="dynamic-class-pane d-none">
            <div class="fw-bold small text-primary mb-1"><i class="fa-solid fa-person-pregnant me-1"></i> OB/GYN Case History</div>
            <div class="row g-2 mb-2">
                <div class="col-4 col-md-2">
                    <label class="form-label m-0 small" for="ob_gravida">Gravida</label>
                    <input type="number" min="0" class="form-control form-control-sm" name="gravida" id="ob_gravida" value="0">
                </div>
                <div class="col-4 col-md-2">
                    <label class="form-label m-0 small" for="ob_para">Para</label>
                    <input type="number" min="0" class="form-control form-control-sm" name="para" id="ob_para" value="0">
                </div>
                <div class="col-4 col-md-2">
                    <label class="form-label m-0 small" for="ob_abortion">Abortion</label>
                    <input type="number" min="0" class="form-control form-control-sm" name="abortion" id="ob_abortion" value="0">
                </div>
                <div class="col-4 col-md-2">
                    <label class="form-label m-0 small" for="ob_iufd">IUFD</label>
                    <input type="number" min="0" class="form-control form-control-sm" name="iufd" id="ob_iufd" value="0">
                </div>
                <div class="col-4 col-md-2">
                    <label class="form-label m-0 small" for="ob_died">Died</label>
                    <input type="number" min="0" class="form-control form-control-sm" name="died" id="ob_died" value="0">
                </div>
                <div class="col-8 col-md-2">
                    <label class="form-label m-0 small" for="ob_lmp_date">LMP Date</label>
                    <input type="date" class="form-control form-control-sm" name="lmp_date" id="ob_lmp_date">
                </div>
            </div>
        </div>

        {{-- 3. Delivery Procedure (Obstetrics & New Born) --}}
        <div id="dynamic_fields_delivery" class="dynamic-class-pane d-none mb-2">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-6" id="delivery_proc_col">
                    <label class="form-label m-0 small fw-semibold" for="delivery_procedure">Delivery Procedure</label>
                    <select class="form-select form-select-sm" name="delivery_procedure" id="delivery_procedure">
                        <option value="Normal" selected>Normal</option>
                        <option value="Cesarean">Cesarean</option>
                        <option value="Others">Others</option>
                    </select>
                </div>
                {{-- New Born Procedure Source --}}
                <div class="col-12 col-md-6 d-none" id="newborn_source_col">
                    <label class="form-label m-0 small fw-semibold d-block">Procedure Source</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="infacilitydelivery" id="src_admission" value="0">
                            <label class="form-check-label small" for="src_admission">Admission (from outside)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="infacilitydelivery" id="src_infacility" value="1" checked>
                            <label class="form-check-label small" for="src_infacility">In-Facility Delivery</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. Pathologic Case Checkbox (OB, GYN, New Born) --}}
        <div id="dynamic_fields_pathologic" class="dynamic-class-pane d-none">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="pathologic" id="class_pathologic" value="1">
                <label class="form-check-label fw-bold text-danger small" for="class_pathologic">
                    Pathologic Case
                </label>
            </div>
        </div>
    </div>

    {{-- File Uploads (Radiology & Laboratory) --}}
    <div class="row g-2 mb-2">
        <div class="col-12 col-md-6">
            <label class="form-label fw-bold m-0 small" for="radiologypath">Radiology Result</label>
            <div class="input-group input-group-sm">
                <input type="file" class="form-control form-control-sm" name="radiologypath" id="radiologypath">
                <button type="button" class="btn btn-sm btn-primary" id="preview_radiology" title="Preview Radiology Document">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <label class="form-label fw-bold m-0 small" for="laboratorypath">Laboratory Result</label>
            <div class="input-group input-group-sm">
                <input type="file" class="form-control form-control-sm" name="laboratorypath" id="laboratorypath">
                <button type="button" class="btn btn-sm btn-primary" id="preview_laboratory" title="Preview Laboratory Document">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Patient Type with "PHIC YAKAP" --}}
    <div class="mb-2">
        <label class="form-label fw-bold small m-0" for="patient_type">Patient Type</label>
        <div class="row g-2">
            <div class="col-12 col-md-6">
                <select class="form-select form-select-sm" name="patient_type" id="patient_type">
                    <option value="regular" selected>REGULAR</option>
                    <option value="hmo">HMO</option>
                    <option value="phic">PHIC YAKAP</option>
                    <option value="others">OTHERS</option>
                </select>
            </div>
            <div class="col-12 col-md-6">
                <select class="form-select form-select-sm" name="hmo_input" id="hmo_input"></select>
            </div>
        </div>
    </div>
```

#### C. Medical History Tab Header & Surgical Label Updates
In `queue.blade.php`:
- **Line 681–686**:
  ```html
  <div class="d-flex justify-content-between align-items-center mb-2">
      <span class="fw-bold text-dark"><i class="fa-solid fa-notes-medical text-primary me-1"></i> Permanent Patient Medical History</span>
      <div class="d-flex gap-2">
          <button type="button" class="btn btn-sm btn-outline-info fw-bold" id="btn_yakap_medhistory_future" data-bs-toggle="tooltip" title="For future updates only">
              <i class="fa-solid fa-file-medical me-1"></i> Yakap Medical History/Profiling
          </button>
          <button type="button" class="btn btn-sm btn-primary fw-bold" id="save_sec_medhistory_btn">
              <i class="fa-solid fa-floppy-disk me-1"></i> Save Medical History
          </button>
      </div>
  </div>
  ```
- **Line 722**:
  Rename `<i class="fa-solid fa-bandage me-1 text-warning"></i> Surgical History:`
  To: `<i class="fa-solid fa-bandage me-1 text-warning"></i> Surgical/Operations History:`

#### D. Include Preview Modal in Queue Blade
In `@push('modals')` at line 790 of `queue.blade.php`:
```blade
@push('modals')
    @include('modals.preview_modal')
    ...
@endpush
```

---

### 4.2 Proposed Code Changes for `resources/js/pages/secretary/queue.js`

1. **Remove "Details" button from Queue table**:
   In `loadPatientTable()` lines 288–293:
   Remove:
   ```javascript
   <button type="button" class="btn btn-sm btn-primary view-consultation-details-btn btn-view-details" data-consultationrefno="${data.consultationrefno || ''}" data-pxrefno="${data.pxrefno || ''}" data-pin="${data.pin || data.pincode || ''}" title="View Consultation Details">
       <i class="fa-solid fa-file-medical me-1"></i> Details
   </button>
   ```

2. **Handle `#btn_yakap_medhistory_future` click**:
   ```javascript
   $(document).on("click", "#btn_yakap_medhistory_future", function (e) {
       e.preventDefault();
       Swal.fire({
           icon: 'info',
           title: 'Yakap Medical History / Profiling',
           text: 'For future updates only.',
           confirmButtonColor: '#0d6efd'
       });
   });
   ```

3. **Dynamic Patient Classification Field Toggler**:
   ```javascript
   function updateClassificationFields(classification) {
       const val = (classification || $("#patient_classification").val() || "").toUpperCase();
       const $container = $("#classification_dynamic_container");
       $(".dynamic-class-pane").addClass("d-none");

       if (val === "MEDICAL") {
           $container.removeClass("d-none");
           $("#dynamic_fields_medical").removeClass("d-none");
       } else if (val === "OBSTETRICS") {
           $container.removeClass("d-none");
           $("#dynamic_fields_obgyn").removeClass("d-none");
           $("#dynamic_fields_delivery").removeClass("d-none");
           $("#delivery_proc_col").removeClass("d-none");
           $("#newborn_source_col").addClass("d-none");
           $("#dynamic_fields_pathologic").removeClass("d-none");
       } else if (val === "GYNECOLOGY") {
           $container.removeClass("d-none");
           $("#dynamic_fields_obgyn").removeClass("d-none");
           $("#dynamic_fields_delivery").addClass("d-none");
           $("#dynamic_fields_pathologic").removeClass("d-none");
       } else if (val === "NEW BORN") {
           $container.removeClass("d-none");
           $("#dynamic_fields_obgyn").addClass("d-none");
           $("#dynamic_fields_delivery").removeClass("d-none");
           $("#delivery_proc_col").removeClass("d-none");
           $("#newborn_source_col").removeClass("d-none");
           $("#dynamic_fields_pathologic").removeClass("d-none");
       } else {
           $container.addClass("d-none");
       }
   }

   $("#patient_classification").on("change", function () {
       updateClassificationFields($(this).val());
   });
   ```

4. **Radiology & Laboratory File Preview Integration**:
   ```javascript
   function setupFilePreview(btnId, inputId) {
       $(document).on("click", btnId, function () {
           const fileInput = document.getElementById(inputId);
           const storedPath = $(this).attr("data-filepath");
           let fileURL = "";
           let fileName = "Document Preview";
           let isImage = false;

           if (fileInput && fileInput.files && fileInput.files.length > 0) {
               const file = fileInput.files[0];
               fileURL = URL.createObjectURL(file);
               fileName = file.name;
               isImage = (file.type && file.type.startsWith("image/")) || /\.(jpe?g|png|gif|webp|bmp)$/i.test(file.name);
           } else if (storedPath && storedPath.trim() !== "") {
               fileURL = resolveAttachmentUrl(storedPath);
               fileName = storedPath.split("/").pop();
               isImage = /\.(jpe?g|png|gif|webp|bmp)$/i.test(storedPath);
           } else {
               return Swal.fire({
                   toast: true,
                   position: 'top-end',
                   icon: 'info',
                   title: 'No document attached to preview.',
                   showConfirmButton: false,
                   timer: 2000
               });
           }

           const previewModalEl = document.getElementById("preview_modal");
           if (previewModalEl) {
               previewModalEl.dataset.src = fileURL;
               previewModalEl.dataset.isimage = isImage ? "1" : "0";
               previewModalEl.dataset.filename = fileName;
               bootstrap.Modal.getOrCreateInstance(previewModalEl).show();
           }
       });
   }
   setupFilePreview("#preview_radiology", "radiologypath");
   setupFilePreview("#preview_laboratory", "laboratorypath");
   ```

5. **Populate Classification & RadLab in `.import-queue`**:
   - In `.import-queue` success callback (around line 1340):
     - Set `#patient_classification` to `p.classification || 'MEDICAL'`, trigger change.
     - If `p.subclassification`, set `#medical_subclassification`.
     - Set `#ob_gravida` (`p.gravida || 0`), `#ob_para` (`p.para || 0`), `#ob_abortion` (`p.abortion || 0`), `#ob_iufd` (`p.iufd || 0`), `#ob_died` (`p.died || 0`), `#ob_lmp_date` (`p.lmp_date || ''`).
     - Set `#delivery_procedure` (`p.delivery_procedure || 'Normal'`).
     - Set `#class_pathologic` (`p.pathologic == 1`).
     - Set radio `name="infacilitydelivery"` (`p.infacilitydelivery == 1` ? `#src_infacility` : `#src_admission`).
     - If `p.radiologypath`, set `$("#preview_radiology").attr("data-filepath", p.radiologypath)`.
     - If `p.laboratorypath`, set `$("#preview_laboratory").attr("data-filepath", p.laboratorypath)`.

6. **Reset Classification & RadLab in `#clear_form`**:
   - In `#clear_form` handler:
     - Reset `#patient_classification` to `'MEDICAL'` and call `updateClassificationFields('MEDICAL')`.
     - Reset all OB/GYN, delivery, and pathologic inputs.
     - Clear `data-filepath` on `#preview_radiology` and `#preview_laboratory`.

---

### 4.3 Proposed Controller & Model Changes

#### `app/Http/Controllers/ConsultationController.php`:
1. In `saveConsultation()` (lines 804–810):
   ```php
   // Support both input naming conventions (radiologypath / radiology_file, laboratorypath / laboratory_file)
   $consultationData['radiologypath'] = ($request->hasFile('radiologypath') ? $request->file('radiologypath') : ($request->hasFile('radiology_file') ? $request->file('radiology_file') : null))?->store('radiology_results', 'private');
   $consultationData['laboratorypath'] = ($request->hasFile('laboratorypath') ? $request->file('laboratorypath') : ($request->hasFile('laboratory_file') ? $request->file('laboratory_file') : null))?->store('laboratory_results', 'private');

   // Persist clinical classification and dynamic fields
   $consultationData['classification'] = strtoupper($request->patient_classification ?? $request->classification ?? $request->patient_type ?? 'MEDICAL');
   $consultationData['subclassification'] = $request->medical_subclassification ?? $request->subclassification ?? null;
   $consultationData['gravida'] = $request->filled('gravida') ? floatval($request->gravida) : null;
   $consultationData['para'] = $request->filled('para') ? floatval($request->para) : null;
   $consultationData['abortion'] = $request->filled('abortion') ? floatval($request->abortion) : null;
   $consultationData['iufd'] = $request->filled('iufd') ? floatval($request->iufd) : null;
   $consultationData['died'] = $request->filled('died') ? floatval($request->died) : null;
   $consultationData['lmp_date'] = $request->filled('lmp_date') ? $request->lmp_date : null;
   $consultationData['delivery_procedure'] = $request->delivery_procedure ?? null;
   $consultationData['pathologic'] = $request->has('pathologic') ? 1 : 0;
   $consultationData['infacilitydelivery'] = $request->filled('infacilitydelivery') ? intval($request->infacilitydelivery) : null;
   $consultationData['modeofpayment'] = strtoupper($request->patient_type ?? 'REGULAR');
   ```
2. In `updateConsultation()` (lines 993–1000):
   Apply the identical updates for file and classification extraction.

#### `app/Models/ConsultationModel.php`:
Add to `$fillable`:
```php
'subclassification',
'gravida',
'para',
'abortion',
'iufd',
'died',
'lmp_date',
'delivery_procedure',
'pathologic',
'infacilitydelivery',
'modeofpayment',
```

#### `app/Http/Controllers/SecretaryController.php`:
In `fetchPatientMedhistory()` (lines 780–820):
Add `gravida`, `para`, `abortion`, `iufd`, `died`, `lmp_date`, `delivery_procedure`, `pathologic`, `infacilitydelivery`, `modeofpayment` to the `select([...])` array.

---

### 4.4 Database Migration & Data Dictionary

#### Migration: `database/migrations/2026_10_12_000001_add_lmp_date_and_delivery_procedure_to_pxwalkinconsultation_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pxwalkinconsultation', function (Blueprint $table) {
            if (!Schema::hasColumn('pxwalkinconsultation', 'lmp_date')) {
                $table->date('lmp_date')->nullable()->after('died');
            }
            if (!Schema::hasColumn('pxwalkinconsultation', 'delivery_procedure')) {
                $table->string('delivery_procedure', 50)->nullable()->after('lmp_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pxwalkinconsultation', function (Blueprint $table) {
            if (Schema::hasColumn('pxwalkinconsultation', 'delivery_procedure')) {
                $table->dropColumn('delivery_procedure');
            }
            if (Schema::hasColumn('pxwalkinconsultation', 'lmp_date')) {
                $table->dropColumn('lmp_date');
            }
        });
    }
};
```

#### Data Dictionary: `.gemini/database/kayakapmdv2_data_dictionary.md`
Under `### pxwalkinconsultation`:
Insert after `died`:
```markdown
| `lmp_date` | date | NULL | NULL | Last Menstrual Period date for Obstetrics and Gynecology consultations |
| `delivery_procedure` | varchar(50) | NULL | NULL | Delivery procedure type (Normal, Cesarean, Others) |
```

---

### 4.5 Consultation Details Modal (`modals/view_consultation_details.blade.php`)

Add 6th Tab for Medical History:
```html
<li class="nav-item">
    <button class="nav-link w-100 text-start text-dark fw-bold" data-bs-toggle="tab" data-bs-target="#vcd_pane_medhistory" role="tab" type="button">
        <i class="fa-solid fa-file-waveform me-2"></i> Medical History
    </button>
</li>
```
And Tab Pane:
```html
<div class="tab-pane w-100" id="vcd_pane_medhistory" role="tabpanel">
    <h5 class="fw-bold mb-3"><i class="fa-solid fa-notes-medical text-primary me-2"></i> Patient Permanent Medical History</h5>
    <div class="row g-2">
        <div class="col-12 col-md-6">
            <div class="p-2 border rounded bg-light mb-2">
                <span class="fw-bold text-danger small d-block mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> Allergies &amp; Drug Reactions:</span>
                <div id="vcd_allergies" class="small text-dark fw-semibold">None recorded</div>
            </div>
            <div class="p-2 border rounded bg-light mb-2">
                <span class="fw-bold text-dark small d-block mb-1"><i class="fa-solid fa-syringe me-1 text-primary"></i> Injections &amp; Immunizations:</span>
                <div id="vcd_injections" class="small text-dark">None recorded</div>
            </div>
            <div class="p-2 border rounded bg-light">
                <span class="fw-bold text-dark small d-block mb-1"><i class="fa-solid fa-pills me-1 text-info"></i> Maintenance Medications:</span>
                <div id="vcd_maintenance_meds" class="small text-dark">None recorded</div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="p-2 border rounded bg-light mb-2">
                <span class="fw-bold text-dark small d-block mb-1"><i class="fa-solid fa-heart-pulse me-1 text-secondary"></i> Past Medical Conditions:</span>
                <div id="vcd_past_medical_history" class="small text-dark">None recorded</div>
            </div>
            <div class="p-2 border rounded bg-light mb-2">
                <span class="fw-bold text-dark small d-block mb-1"><i class="fa-solid fa-bandage me-1 text-warning"></i> Surgical/Operations History:</span>
                <div id="vcd_surgical_history" class="small text-dark">None recorded</div>
            </div>
            <div class="p-2 border rounded bg-light">
                <span class="fw-bold text-dark small d-block mb-1"><i class="fa-solid fa-users me-1 text-success"></i> Family Medical History:</span>
                <div id="vcd_family_history" class="small text-dark">None recorded</div>
            </div>
        </div>
        <div class="col-12">
            <div class="p-2 border rounded bg-light">
                <span class="fw-bold text-dark small d-block mb-1"><i class="fa-solid fa-comment-medical me-1 text-secondary"></i> Special Clinical Notes &amp; Warnings:</span>
                <div id="vcd_clinical_notes" class="small text-dark">None recorded</div>
            </div>
        </div>
    </div>
</div>
```

---

## 5. Verification Method

1. **Automated Unit & Feature Tests**:
   Execute standard test suite:
   ```powershell
   php artisan test
   ```
   *Expected outcome*: 138/138 tests pass with 0 failures.
   Add a dedicated Feature test `tests/Feature/SecretaryConsoleR2EnhancementsTest.php` to verify:
   - Form responsiveness element IDs render in `GET /secretary/queue`.
   - `pxwalkinconsultation` successfully accepts and persists `patient_classification`, `lmp_date`, `delivery_procedure`, `pathologic`, `gravida`, etc. via `POST /api/save_patient_consultation` and `POST /api/update_patient_consultation`.
   - `radiologypath` and `laboratorypath` files are saved to `private` disk.
   - Queue action column does NOT contain `.view-consultation-details-btn`.

2. **Database Migration Verification**:
   ```powershell
   php artisan migrate
   php artisan tinker --execute="dump(Schema::hasColumn('pxwalkinconsultation', 'lmp_date'), Schema::hasColumn('pxwalkinconsultation', 'delivery_procedure'));"
   ```
   *Expected outcome*: Both return `true`.

3. **Asset Compilation**:
   ```powershell
   npm.cmd run build
   ```
   *Expected outcome*: Exit code 0 with clean Vite bundle generation.

4. **Invalidation Conditions**:
   - Failure if `saveConsultation` drops uploaded files because `radiology_file` was expected instead of `radiologypath`.
   - Failure if `npm run build` fails due to syntax errors in `queue.js`.
   - Failure if existing tests (`QueueDoctorConsultationAndStocksTest`) fail due to breaking changes in `classification` or `patient_type`.
