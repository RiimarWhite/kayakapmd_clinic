# Walkthrough: Yakap Medical History/Profiling Modal Integration (Secretary Console)

## Overview
Successfully integrated the PhilHealth Yakap Medical History & Profiling modal into the Secretary Console (`resources/views/pages/secretary/queue.blade.php`), accessible to both **Secretary** and **Admin** roles. The placeholder button (`#btn_yakap_medhistory_future`) on the **Medical History** tab has been replaced with an active, interactive modal button (`#btn_yakap_medhistory_modal`) linked to the extracted backend subsystem from `temp/kayakapmd`.

---

## Changes Implemented

### 1. Data Models & References
- **`MDiseaseLibModel`**: Added `public $timestamps = false;` to match `dw_lib_mdiseases` schema.
- **`ProfileModel`**: Added `px_pin` to `$fillable` array to guarantee patient PIN persistence.
- Referenced models: `ProfileMedHistModel` (`dd_profile_medhist`), `ProfileMhSpecificModel` (`dd_profile_mhspecific`), `ProfileSurgHistModel` (`dd_profile_surghist`), and `ProfileModel` (`dd_profile_1`).

### 2. Controller & Endpoints (`SecretaryController.php`)
- **`queuePage()`**: Loads active conditions (`MDiseaseLibModel::where('lib_stat', 1)->orderBy('sort_no')->get()`) and passes `$mDiseaseLibrary` to `pages.secretary.queue`.
- **`fetchPatientYakapMedicalHistory(Request $request)`**: Resolves patient via `pxrefno`, `pincode`, `caseno`, or `consultationrefno`. Queries existing Yakap profile, medical conditions (`dd_profile_medhist`), specific descriptions (`dd_profile_mhspecific`), and surgical operations (`dd_profile_surghist`).
- **`savePatientYakapMedicalHistory(Request $request)`**: Persists medical conditions, specific description notes, and dynamic surgical operations into their respective tables within a DB transaction. If the patient does not yet have a `dd_profile_1` header, creates one with a unique Yakap profile transaction number.

### 3. API Routing (`routes/api.php`)
- Added within the authenticated secretary and admin middleware group:
  - `POST /api/fetch_patient_yakap_medhistory`
  - `POST /api/save_patient_yakap_medhistory`

### 4. Modal View (`resources/views/modals/yakap_medhistory_modal.blade.php`)
- Dedicated modal `#yakap_medhistory_modal`:
  - **Patient Summary Header**: Patient Name, PIN, Reference No, Consultation Ref, Profile Transaction No.
  - **Past Medical Conditions**: Dynamic checklist generated from `$mDiseaseLibrary`, expandable specify text inputs for selected conditions, and exclusive "None" (code `999`) toggle behavior.
  - **Past Surgical Operations**: Dynamic table `#tblYakapMedHistOpHist` allowing addition and removal of surgical operations and dates.
  - **Actions**: "Save Yakap History" button (`#btn_save_yakap_medhistory`) and Cancel button.

### 5. Secretary Queue View & JavaScript (`queue.blade.php` & `queue.js`)
- Replaced `#btn_yakap_medhistory_future` with `#btn_yakap_medhistory_modal` on the Medical History tab.
- Included `@include('modals.yakap_medhistory_modal')` inside `@push('modals')`.
- In `queue.js`:
  - Bound `#btn_yakap_medhistory_modal` click: verifies active patient, populates header, triggers AJAX fetch, populates checkboxes, specify fields, and surgical table rows.
  - Added dynamic checkbox toggle and mutual exclusion with "None".
  - Implemented `addYakapOperationHist()` and `removeYakapOperationHist()`.
  - Bound `#btn_save_yakap_medhistory` click: submits form payload via AJAX, shows success toast alert, updates profile trans no, and closes modal.

---

## Verification & Testing

1. **Automated Feature Tests**:
   - `tests/Feature/SecretaryYakapMedHistoryModalIntegrationTest.php`:
     - `test_queue_page_renders_with_disease_library_and_yakap_modal`: **PASS** (Secretary and Admin console loads `$mDiseaseLibrary` and modal).
     - `test_fetch_patient_yakap_medhistory_endpoint`: **PASS** (Returns structured JSON with existing records).
     - `test_save_patient_yakap_medhistory_persists_records`: **PASS** (Persists records in `dd_profile_1`, `dd_profile_medhist`, `dd_profile_mhspecific`, and `dd_profile_surghist`).
   - Full test suite: **178 passed (1,342 assertions)** with zero failures or regressions.

2. **Frontend Asset Compilation**:
   - Executed `cmd /c "npm run build"`:
     - Vite build succeeded with zero errors (`public/build/assets/queue-Bc-q9kfB.js`).
