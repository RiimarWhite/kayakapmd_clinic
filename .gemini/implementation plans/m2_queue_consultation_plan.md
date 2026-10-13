# Implementation Plan: Milestone 2 — Secretary Console Responsiveness, Patient Classification & File Attachments

**Target Milestone**: M2 (Requirement R2)
**Assigned Worker**: Worker M2 (`worker_m2_v2`)
**Exclusive Write Boundaries**:
- `resources/views/pages/secretary/queue.blade.php`
- `resources/js/pages/secretary/queue.js`
- `app/Http/Controllers/ConsultationController.php`
- `app/Models/ConsultationModel.php`
- `database/migrations/2026_10_12_000001_add_classification_fields_to_pxwalkinconsultation.php`
- `.gemini/database/kayakapmdv2_data_dictionary.md`
- `resources/views/modals/view_consultation_details.blade.php`

---

## 1. Problem Statement & Scope
Milestone 2 addresses key clinical, workflow, and UX requirements in the Secretary Console:
1. **Grid Responsiveness**: Patient consultation form demographics and vital signs use rigid `d-flex` containers with fixed `max-width` and `min-width` styles, leading to card overflow and horizontal clipping on viewports under 1400px. Refactor to responsive Bootstrap 5.3 grid (`row g-2` with `col` breakpoints).
2. **Queue Table Details Button**: Remove the redundant "Details" button (`.view-consultation-details-btn`) from the `#patients_queue_table` action column while maintaining the `.import-queue` and action dropdowns, and retaining the details click handler for history tables.
3. **Medical History Enhancements**:
   - Add "Yakap Medical History/Profiling" button (`#btn_yakap_medhistory_future`) with tooltip and notice "For future updates only".
   - Rename "Surgical History" label to "Surgical/Operations History".
4. **Patient Classification & Dynamic Fields**:
   - Add Patient Classification select above Chief Complaints with 10 options: `MEDICAL`, `PEDIA`, `OBSTETRICS`, `GYNECOLOGY`, `NEW BORN`, `ORTHO`, `DENTISTRY`, `DERMA`, `HEMODIALYSIS`, `OTHERS`.
   - Add dynamic classification container inserted directly after Temperature & vital signs.
   - Dynamically toggle sub-fields:
     - `MEDICAL`: Sub-classification (`Adult`, `Pedia`).
     - `OBSTETRICS`: OB/GYN Case History (`Gravida`, `Para`, `Abortion`, `IUFD`, `Died`, `LMP Date`), Delivery Procedure (`Normal`, `Cesarean`, `Others`), `Pathologic Case` checkbox.
     - `GYNECOLOGY`: OB/GYN Case History (`Gravida`, `Para`, `Abortion`, `IUFD`, `Died`, `LMP Date`), `Pathologic Case` checkbox.
     - `NEW BORN`: Procedure Source (`Admission (from outside)`, `In-Facility Delivery`), Delivery Procedure (`Normal`, `Cesarean`, `Others`), `Pathologic Case` checkbox.
5. **Database Migration & Data Dictionary**:
   - Create defensive migration `2026_10_12_000001_add_classification_fields_to_pxwalkinconsultation.php` adding `lmp_date` (nullable DATE) and `delivery_procedure` (nullable VARCHAR(255)) to `pxwalkinconsultation`.
   - Update `app/Models/ConsultationModel.php` fillable array.
   - Update `.gemini/database/kayakapmdv2_data_dictionary.md`.
6. **Patient Type Option Renaming**:
   - Rename option label from `"PHIC"` to `"PHIC YAKAP"` while retaining value `"phic"`.
7. **Radiology & Laboratory File Handling**:
   - Fix file upload handling in `ConsultationController.php` `saveConsultation()` and `updateConsultation()` to check both `radiologypath` and `radiology_file`, and `laboratorypath` and `laboratory_file`.
   - Add file preview handlers in `queue.js` and dedicate `#preview_modal` included in `queue.blade.php`.
8. **Consultation Details Modal Tab Parity**:
   - Ensure `resources/views/modals/view_consultation_details.blade.php` has a 6th vertical tab for Medical History populated with permanent medical history records.

---

## 2. Step-by-Step Implementation Steps

### Step 1: Database Migration & Model Update
- Create `database/migrations/2026_10_12_000001_add_classification_fields_to_pxwalkinconsultation.php` with defensive `Schema::hasTable` and `Schema::hasColumn` checks.
- Add `lmp_date` (nullable DATE) and `delivery_procedure` (nullable VARCHAR(255)) after `died`.
- Run `php artisan migrate`.
- Update `app/Models/ConsultationModel.php` `$fillable` array to include `lmp_date`, `delivery_procedure`, `modeofpayment`, `gravida`, `para`, `abortion`, `iufd`, `died`, `pathologic`, `infacilitydelivery`, `linkaccount`.
- Update `.gemini/database/kayakapmdv2_data_dictionary.md`.

### Step 2: Controller Enhancements (`app/Http/Controllers/ConsultationController.php`)
- In `saveConsultation()`:
  - Check `$request->file('radiologypath') ?: $request->file('radiology_file')` and store to `radiology_results` on `private` disk.
  - Check `$request->file('laboratorypath') ?: $request->file('laboratory_file')` and store to `laboratory_results` on `private` disk.
  - Set `classification`:
    ```php
    $consultationData['classification'] = strtoupper($request->patient_classification ?? $request->classification ?? $request->patient_type ?? 'MEDICAL');
    ```
  - Set `subclassification` from `medical_subclassification` or `subclassification`.
  - Set `gravida`, `para`, `abortion`, `iufd`, `died`, `lmp_date`, `delivery_procedure`, `pathologic`, `infacilitydelivery`, `modeofpayment`.
- In `updateConsultation()`:
  - Mirror the identical file and classification persistence logic.

### Step 3: Blade View Refactoring (`resources/views/pages/secretary/queue.blade.php`)
- Patient Information demographics: replace rigid `d-flex` with `<div class="row g-2">` and responsive breakpoints.
- Insert Patient Classification select above Chief Complaints.
- Vital Signs: replace rigid `d-flex` with `<div class="row g-2">`.
- Insert dynamic classification container (`#classification_dynamic_container`) with panes for `MEDICAL`, `OB/GYN`, `Delivery Procedure`, `New Born Procedure Source`, and `Pathologic Case`.
- File upload inputs: ensure clean grid layout.
- Rename Patient Type option `"PHIC"` to `"PHIC YAKAP"`.
- Medical History tab: Add `#btn_yakap_medhistory_future` and rename label to `"Surgical/Operations History:"`.
- Include `#preview_modal` in `@push('modals')`.

### Step 4: JavaScript Logic Updates (`resources/js/pages/secretary/queue.js`)
- In `loadPatientTable()` lines 289-292: Remove Details button from action column.
- Add click handler for `#btn_yakap_medhistory_future` showing SweetAlert notice.
- Implement `updateClassificationFields()` function and bind change event to `#patient_classification`.
- Implement `setupFilePreview()` for `#preview_radiology` and `#preview_laboratory` supporting local `URL.createObjectURL` and stored paths via `#preview_modal`.
- Populate classification fields and attach file preview paths in `.import-queue`.
- Reset classification fields in `#clear_form`.
- Enhance `openConsultationDetailsModal(record)` to populate Medical History tab fields (`#vcd_allergies`, `#vcd_injections`, `#vcd_maintenance_meds`, `#vcd_past_medical_history`, `#vcd_surgical_history`, `#vcd_family_history`, `#vcd_clinical_notes`).

### Step 5: Modal Enhancement (`resources/views/modals/view_consultation_details.blade.php`)
- Add 6th vertical tab button: `<i class="fa-solid fa-file-waveform me-2"></i> Medical History`.
- Add tab pane `#vcd_pane_medhistory` displaying all permanent medical history fields.

### Step 6: Verification & Test Execution
- Run `php artisan migrate`.
- Run `php artisan test`.
- Verify zero syntax or lint errors.
- Create walkthrough documentation in `.gemini/walkthroughs/m2_queue_consultation_walkthrough.md`.
