# Implementation Plan: Secretary Console Consultation Fields & Yakap Medical History/Profiling

## Goal Description
Enhance the Secretary Console (accessible by both Admin and Secretary users) with UI layout improvements, new clinical consultation narrative fields ("Brief History of Present Illness" and "Pertinent Past Medical History"), tab header update ("Patient Medical History"), and a comprehensive codebase exploration and extraction of the Yakap Medical History/Profiling subsystem from `temp/kayakapmd`.

---

## User Review Required

> [!IMPORTANT]
> **Database Schema Changes**: Adding `brief_history_present_illness` and `pertinent_past_medhistory` as nullable `LONGTEXT` columns to the `pxwalkinconsultation` table via a defensive, idempotent migration.
> Both columns will be updated synchronously in [.gemini/database/kayakapmdv2_data_dictionary.md](file:///C:/xampp/htdocs/kayakapmd_clinic/.gemini/database/kayakapmdv2_data_dictionary.md) per Rule 4.

> [!NOTE]
> **Yakap Medical History / Profiling Button**: In the Secretary Console (`resources/views/pages/secretary/queue.blade.php`), the button `#btn_yakap_medhistory_future` currently displays an alert *"For future updates only"*. We have thoroughly explored `temp/kayakapmd` and detailed the full code architecture below. We provide an open question below on whether you would also like this button wired to open the Yakap Profiling modal now or keep it as an extracted architectural reference.

---

## Open Questions

> [!IMPORTANT]
> 1. **Yakap Profiling Integration**: Would you like us to wire up the `#btn_yakap_medhistory_future` button in the Secretary Queue to launch the PhilHealth Yakap Profile Modal (`#profile-details-modal`) for the currently selected patient, or keep the button reserved while providing the full code reference and extraction in this task?
> 2. **Consultation Details & Doctor Consultation Modals**: Should the new fields ("Brief History of Present Illness" and "Pertinent Past Medical History") also be displayed as read-only fields in the Doctor Consultation and Consultation Details Viewer modal (`view_consultation_details.blade.php`), ensuring the doctor can review them during the consultation? *(Recommended: Yes)*

---

## Code Exploration & Extraction: Yakap Medical History / Profiling

Following a detailed investigation of `temp/kayakapmd`, here is the complete breakdown of the code responsible for PhilHealth Yakap Medical History and Profiling:

### 1. Database Schema & Tables
| Table | Module / Purpose | Key Columns |
|---|---|---|
| `dd_ref_mdisease` | Medical Disease Library (lookup options) | `mdisease_code`, `mdisease_desc`, `sort_no`, `lib_stat` |
| `dd_profile` | Yakap Profile Master Record | `p_TransNo`, `dw_clientcode`, `px_pin`, `en_caseno`, `dProfDate`, `dIsWalkedIn`, `dATC`, `dPatientType` |
| `dd_profile_medhist` | Selected Medical Conditions | `id`, `dw_clientcode`, `px_pin`, `p_TransNo`, `en_caseno`, `dMdiseaseCode` |
| `dd_profile_mhspecific` | Specific Description for Medical Conditions | `id`, `dw_clientcode`, `px_pin`, `p_TransNo`, `en_caseno`, `dMdiseaseCode`, `dSpecificDesc` |
| `dd_profile_surghist` | Surgical History Records | `id`, `dw_clientcode`, `px_pin`, `p_TransNo`, `en_caseno`, `dSurgDesc`, `dSurgDate` |
| `dd_enlistment_1` / `dd_enlistment` | Patient PhilHealth Enlistment Header | `dCaseNo`, `px_pin`, `clientcode`, `dEffyear`, `phic_pin` |

### 2. Eloquent Models
- [`App\Models\Libraries\MDiseaseLibModel`](file:///C:/xampp/htdocs/kayakapmd_clinic/app/Models/Libraries/MDiseaseLibModel.php) (table: `dd_ref_mdisease`)
- [`App\Models\Profiles\ProfileMedHistModel`](file:///C:/xampp/htdocs/kayakapmd_clinic/app/Models/Profiles/ProfileMedHistModel.php) (table: `dd_profile_medhist`)
- [`App\Models\Profiles\ProfileMhSpecificModel`](file:///C:/xampp/htdocs/kayakapmd_clinic/app/Models/Profiles/ProfileMhSpecificModel.php) (table: `dd_profile_mhspecific`)
- [`App\Models\Profiles\ProfileSurgHistModel`](file:///C:/xampp/htdocs/kayakapmd_clinic/app/Models/Profiles/ProfileSurgHistModel.php) (table: `dd_profile_surghist`)
- [`App\Models\ProfileModel`](file:///C:/xampp/htdocs/kayakapmd_clinic/app/Models/ProfileModel.php) (table: `dd_profile`)

### 3. Backend Services & Controllers
- **Controller**: `App\Http\Controllers\YakapManagementController`
  - Loads `$mDiseaseLibrary = MDiseaseLibModel::where('lib_stat', 1)->orderBy('sort_no')->get();`
- **API Controller**: `App\Http\Controllers\Api\YakapManagementApiController`
  - `GET /api/profile/{transNo}` (`fetchPatientProfile`): Loads profile data including `medicalHistory`, `mhSpecific`, `surgicalHistory`.
  - `POST /api/saveProfileData` (`saveProfileData`): Handles full profile payload and delegates to `ProfileService`.
- **Service**: `App\Services\YakapManagement\ProfileService`
  - `saveMedicalHistory(array $medicalHistory)`:
    Iterates over `$medicalHistory['chkMedHistDiseases']`, creates records in `ProfileMedHistModel` and `ProfileMhSpecificModel`.
  - `saveSurgicalHistory(array $surgicalHistory)`:
    Iterates over `$surgicalHistory['surgicalHistory']`, creates records in `ProfileSurgHistModel`.

### 4. Blade Views & UI Components
- **Modal**: `temp/kayakapmd/resources/views/modals/yakap-management/profile-details.blade.php`
  - Tab 2: Medical & Surgical History: `@include('components.yakap-management.profile-tabs.medical-history')`
- **Medical History Component**: `temp/kayakapmd/resources/views/components/yakap-management/profile-tabs/medical-history.blade.php`
  - Form `#medicalHistoryForm`:
    - Iterates over `$mDiseaseLibrary` with checkboxes (`chkMedHistDiseases[{code}][selected]`) and toggleable specify input (`chkMedHistDiseases[{code}][specify]`).
    - Code 999 ("None") checkbox.
  - Form `#surgicalHistoryForm`:
    - Table `#tblMedHistOpHist` with Operation (`surgicalHistory[operation][]`), Date (`surgicalHistory[date][]`), and dynamic Add/Remove buttons (`addOperationHist`/`removeOperationHist`).

### 5. Frontend JavaScript
- **File**: `temp/kayakapmd/resources/js/pages/admin/philhealth/yakap-management.js`
  - `populateMedicalAndSurgicalHistory(medicalHistory, mhSpecific, surgicalHistory)`:
    Populates checkboxes `#diseaseCode${item.dMdiseaseCode}`, specific inputs `#specificCode${item.dMdiseaseCode}`, and surgical rows `#tblMedHistOpHist`.
  - Checkbox toggle event listener toggling `#divdiseaseCode${code}` visibility.
  - Serialization and POST to `/api/saveProfileData`.

---

## Proposed Changes

### Database & Schema Synchronization

#### [NEW] `database/migrations/2026_10_13_000001_add_hpi_and_pmh_to_pxwalkinconsultation.php`
- Defensive idempotent migration adding `brief_history_present_illness` and `pertinent_past_medhistory` as `LONGTEXT NULL` to `pxwalkinconsultation`.

#### [MODIFY] [.gemini/database/kayakapmdv2_data_dictionary.md](file:///C:/xampp/htdocs/kayakapmd_clinic/.gemini/database/kayakapmdv2_data_dictionary.md)
- Add documentation for `brief_history_present_illness` and `pertinent_past_medhistory` under `pxwalkinconsultation`.

#### [MODIFY] [app/Models/ConsultationModel.php](file:///C:/xampp/htdocs/kayakapmd_clinic/app/Models/ConsultationModel.php)
- Add `'brief_history_present_illness'` and `'pertinent_past_medhistory'` to `$fillable`.

---

### Backend Controller Handling

#### [MODIFY] [app/Http/Controllers/ConsultationController.php](file:///C:/xampp/htdocs/kayakapmd_clinic/app/Http/Controllers/ConsultationController.php)
- In `saveConsultation()`:
  - Save `$request->brief_history_present_illness ?? ''`
  - Save `$request->pertinent_past_medhistory ?? ''`
- In `updateConsultation()`:
  - Update `$request->brief_history_present_illness ?? ($record->brief_history_present_illness ?? '')`
  - Update `$request->pertinent_past_medhistory ?? ($record->pertinent_past_medhistory ?? '')`

---

### Blade Views & UI Layout

#### [MODIFY] [resources/views/pages/secretary/queue.blade.php](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/views/pages/secretary/queue.blade.php)
1. **Patient Classification Width Adjustment**:
   - Wrap Patient Classification in a `<div class="row g-2 mb-2"><div class="col-12 col-sm-4">...</div></div>` container so its column class (`col-12 col-sm-4`) exactly matches the Weight input column (`col-12 col-sm-4`).
2. **New Narrative Fields**:
   - Directly below Chief Complaints textarea, insert:
     - "Brief History of Present Illness": `<textarea class="form-control form-control-sm" rows="3" name="brief_history_present_illness" id="pxbriefhistory" placeholder="State brief history of present illness..."></textarea>`
     - "Pertinent Past Medical History": `<textarea class="form-control form-control-sm" rows="3" name="pertinent_past_medhistory" id="pxpastmedhistory" placeholder="State pertinent past medical history..."></textarea>`
3. **Medical History Tab Header**:
   - Update header line:
     From: `<span class="fw-bold text-dark"><i class="fa-solid fa-notes-medical text-primary me-1"></i> Permanent Patient Medical History</span>`
     To: `<span class="fw-bold text-dark"><i class="fa-solid fa-notes-medical text-primary me-1"></i> Patient Medical History</span>`

#### [MODIFY] [resources/views/modals/view_consultation_details.blade.php](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/views/modals/view_consultation_details.blade.php)
- Add readonly preview fields for Brief History of Present Illness and Pertinent Past Medical History so reviewing past consultations shows these fields.

---

### Frontend JavaScript

#### [MODIFY] [resources/js/pages/secretary/queue.js](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/js/pages/secretary/queue.js)
- When a patient consultation is selected or imported (in `importPatient` or queue row selection):
  - Populate `$('#pxbriefhistory').val(p.brief_history_present_illness || '')`
  - Populate `$('#pxpastmedhistory').val(p.pertinent_past_medhistory || '')`
- In `form.reset()` handlers (after saving, updating, or clearing):
  - Reset `$('#pxbriefhistory').val('')`
  - Reset `$('#pxpastmedhistory').val('')`
- In `view_consultation_details` modal population:
  - Populate `#vcd_brief_history` and `#vcd_past_medical_history` if record contains them.

---

## Verification Plan

### Automated Tests
1. Run PHPUnit/Artisan tests:
   ```bash
   php artisan test --filter=SecretaryConsultationHpiAndPmhTest
   ```
2. Run database migration tests:
   ```bash
   php artisan migrate
   php artisan migrate:rollback --step=1
   php artisan migrate
   ```

### Manual Verification
1. Access the Secretary Queue (`/secretary/queue`) as Secretary and as Admin.
2. Verify Patient Classification dropdown width matches the Weight input column width across desktop and mobile screens.
3. Verify "Brief History of Present Illness" and "Pertinent Past Medical History" textboxes appear directly below Chief Complaints.
4. Enter test values, save a new consultation, and verify values persist in the database and reload properly upon clicking the consultation.
5. Click on the "Medical History" tab and verify the header reads "Patient Medical History" (without "Permanent").
