# Implementation Plan: Yakap Medical History/Profiling Modal Integration (Secretary Console)

## Executive Summary
This document outlines the architectural extraction of the Yakap Medical History & Profiling subsystem from `temp/kayakapmd` and its seamless integration into the Secretary Console (`pages.secretary.queue`, accessible to both **Secretary** and **Admin** roles). The placeholder alert button (`#btn_yakap_medhistory_future`) on the **Medical History** tab will be replaced with an interactive button (`#btn_yakap_medhistory_modal`) triggering a dedicated Yakap Medical History & Profiling modal (`#yakap_medhistory_modal`).

---

## Architecture & Subsystem Analysis

### 1. Data Models & Tables
- **Reference Library**: `App\Models\Libraries\MDiseaseLibModel` (table: `dw_lib_mdiseases`).
  - Contains 20 conditions (Allergy, Asthma, Cancer, Diabetes, Hypertension, None [999], etc.).
- **Profile Master**: `App\Models\ProfileModel` (table: `dd_profile_1`).
  - Primary identification: `dTransNo` (e.g. `PH1003703420251018173`), `en_CaseNo`, `px_pin`, `dw_clientcode`.
- **Medical History**: `App\Models\Profiles\ProfileMedHistModel` (table: `dd_profile_medhist`).
  - Columns: `dw_clientcode`, `px_pin`, `p_TransNo`, `en_caseno`, `dMdiseaseCode`.
- **Disease Specific Descriptions**: `App\Models\Profiles\ProfileMhSpecificModel` (table: `dd_profile_mhspecific`).
  - Columns: `dw_clientcode`, `px_pin`, `en_caseno`, `p_TransNo`, `dMdiseaseCode`, `dSpecificDesc`.
- **Surgical History**: `App\Models\Profiles\ProfileSurgHistModel` (table: `dd_profile_surghist`).
  - Columns: `dw_clientcode`, `px_pin`, `p_TransNo`, `en_caseno`, `dSurgDesc`, `dSurgDate`.

### 2. Integration Challenges & Defensive Solutions
1. **Patient Identifier Matching**:
   - In the Secretary Console, patients may be walk-ins with `pxrefno`, `pincode`, and `caseno` / `consultationrefno`, or enlisted Yakap patients with `px_pin` and `dCaseNo`.
   - The backend fetch and save endpoints must look up existing profiles by `px_pin`, `pincode`, `pxrefno`, or `en_caseno`. If a walk-in patient does not yet have a `dd_profile_1` record, auto-create/link a profile header with generated `dTransNo` or save directly to `dd_profile_medhist` and `dd_profile_surghist` with fallback linkages.
2. **Controller View Data**:
   - `SecretaryController::queuePage()` will supply `$mDiseaseLibrary` (`MDiseaseLibModel::where('lib_stat', 1)->orderBy('sort_no')->get()`) to `resources/views/pages/secretary/queue.blade.php`.
3. **Modal Component**:
   - Build a clean, dedicated modal `resources/views/modals/yakap_medhistory_modal.blade.php` focusing on the Yakap Medical & Surgical History section (with patient header, disease checkboxes, dynamic specify inputs, None exclusion behavior, dynamic surgical history table rows, and save action).

---

## Proposed Changes

### Backend Changes

#### 1. `app/Http/Controllers/SecretaryController.php`
- In `queuePage()`:
  - Query active disease library: `$mDiseaseLibrary = \App\Models\Libraries\MDiseaseLibModel::where('lib_stat', 1)->orderBy('sort_no')->get();`
  - Pass `$mDiseaseLibrary` to the view `pages.secretary.queue`.
- Add endpoint `fetchPatientYakapMedicalHistory(Request $request)`:
  - Inputs: `pxrefno`, `pincode`, `caseno`, `consultationrefno`.
  - Resolves patient PIN and existing profile in `dd_profile_1`.
  - Fetches associated `dd_profile_medhist`, `dd_profile_mhspecific`, and `dd_profile_surghist`.
  - Returns JSON with profile info, conditions list, and surgeries list.
- Add endpoint `savePatientYakapMedicalHistory(Request $request)`:
  - Inputs: `pxrefno`, `pincode`, `caseno`, `consultationrefno`, `patientname`, `chkMedHistDiseases` (array), `surgicalHistory` (array).
  - Validates and saves records into `dd_profile_medhist`, `dd_profile_mhspecific`, and `dd_profile_surghist`.
  - Uses DB transactions with structured logging.

#### 2. `routes/api.php`
- Under secretary/admin authenticated API route group:
  - `Route::post('fetch_patient_yakap_medhistory', [SecretaryController::class, 'fetchPatientYakapMedicalHistory'])->name('secretary.fetch_yakap_medhistory');`
  - `Route::post('save_patient_yakap_medhistory', [SecretaryController::class, 'savePatientYakapMedicalHistory'])->name('secretary.save_yakap_medhistory');`

---

### Frontend / View Changes

#### 3. `resources/views/modals/yakap_medhistory_modal.blade.php`
- New blade modal:
  - Modal ID: `#yakap_medhistory_modal`
  - Patient Header summary: Name, PIN/RefNo, Consultation Ref, Age/Gender.
  - Form 1 (`#yakap_medhist_form`):
    - Medical Conditions checklist generated from `$mDiseaseLibrary`.
    - Checkbox toggle displays specify text input for diseases.
    - Selecting "None" unchecks other diseases; checking any disease unchecks "None".
  - Form 2 (`#yakap_surghist_form`):
    - Table `#tblYakapMedHistOpHist` with Operation, Date, and Add/Remove buttons.
  - Modal Footer:
    - Close button and "Save Yakap History" button (`#btn_save_yakap_medhistory`).

#### 4. `resources/views/pages/secretary/queue.blade.php`
- In Medical History tab:
  - Change button `#btn_yakap_medhistory_future` to `#btn_yakap_medhistory_modal` (remove "For future updates only" tooltip).
- Under `@push('modals')`:
  - Add `@include('modals.yakap_medhistory_modal')`.

#### 5. `resources/js/pages/secretary/queue.js`
- Remove placeholder alert on `#btn_yakap_medhistory_future`.
- Bind click handler on `#btn_yakap_medhistory_modal`:
  - Check if a patient is loaded (via `#pxfname` or `#pxidno` or `#pxconsultationrefno`). If none, prompt with warning SweetAlert.
  - Show modal `#yakap_medhistory_modal`, display spinner/loading state.
  - Perform AJAX POST to `/api/fetch_patient_yakap_medhistory`.
  - Populate checkboxes, specify fields, and surgical table rows.
- Implement checkbox toggle and None logic.
- Implement dynamic surgical history rows (`addYakapOperationHist`, `removeYakapOperationHist`).
- Bind click handler on `#btn_save_yakap_medhistory`:
  - Gather form payload and submit via AJAX POST to `/api/save_patient_yakap_medhistory`.
  - Display success SweetAlert toast, update UI state.

---

## Verification Plan

### Automated Tests
1. Create `tests/Feature/SecretaryYakapMedHistoryModalIntegrationTest.php`:
   - Test authenticated Secretary and Admin can access `/secretary/queue` with `$mDiseaseLibrary` populated.
   - Test `POST /api/fetch_patient_yakap_medhistory` retrieves empty/existing medical and surgical history records.
   - Test `POST /api/save_patient_yakap_medhistory` persists conditions in `dd_profile_medhist`, `dd_profile_mhspecific`, and surgeries in `dd_profile_surghist`.
   - Test unauthorized requests are rejected.
2. Run full test suite:
   - `php artisan test --filter=SecretaryYakapMedHistoryModalIntegrationTest`

### Frontend Build
- Run `cmd /c npm run build` to recompile assets and verify zero compilation errors.
