# Comprehensive Implementation Plan: Secretary Queue, Doctor Console & Admin Stocks Management

**Plan Name**: `comprehensive_queue_doctor_and_admin_stocks_feature_plan.md`  
**Generated Date**: 2026-09-29  
**Status**: Execution Verified & Completed (100% Tests Passing)  

---

## 1. Goal Description

This implementation addresses all defects and feature enhancements requested across the Secretary, Doctor, and Admin modules of KayakapMD Clinic:

1. **Secretary / Queue Page & Admin Counterpart & Patient**:
   - Added a `Medical History` tab immediately after `Consultation Details` in `queue.blade.php`.
   - Fixed patient photo capture (webcam WebRTC) and file upload so images can be previewed, edited, and saved reliably.
   - Fixed missing patient address and missing landline contact details by unifying data hydration and synchronization with `pxmasterlist`.
   - Ensured all patient demographics and vitals are completely displayed without omissions.
   - Separated the patient queue dynamically by assigned doctor using a Doctor Selection Dropdown with real-time queue count badges (`DR. NAME (N waiting)`) and automatic queue switching on change.
2. **Doctor / Patients Masterlist**:
   - Fixed missing patient image in the masterlist table with fallback handlers (`onerror`).
   - Connected and displayed full patient consultation / medical history in the dedicated modal Tab 4.
3. **Doctor Console & Consultation**:
   - Fixed patient demographics and vitals display in the consultation modal (including photo, address, and landline).
   - Added configurable Doctor Professional / Consultation Fee configured on dashboard, which automatically saves to `doctors` (`pfrate`) and `doctorsrights` (`consultationfee`).
   - Strictly scoped queue visibility so doctors only see patients assigned to their specific `docrefno`.
   - Upgraded Rx (prescription) management: added individual instruction/sig per medicine, refreshed both Rx tables upon saving or deleting, and updated printed prescriptions.
4. **Admin Stocks Management (`/admin/stocks/management`)**:
   - When Category is "Drugs & Medicine", repositioned Name and PhilHealth Ref Code down, and added Generic (searchable Select2 dropdown querying `dw_lib_meds_generic`), Brand, and Dosage at the top with auto-generated Name format `Brand - Generic Dosage` (e.g., `Biogesic - Paracetamol 500mg`).
   - Modernized the stocks management table with custom column filter and ordering dropdowns matching the `/admin/hmo` architecture (via `table-column-filter.js`).
5. **Database Migration, Data Dictionary & Setup Script Integrity**:
   - Created defensive migration `2026_09_29_150000_add_instructions_to_stocks_ledger_and_photo_path_to_pxmasterlist.php`.
   - Synchronized `.gemini/database/kayakapmdv2_data_dictionary.md` under `stocks_ledger` and `pxmasterlist`.
   - Updated `setup.sh` to ensure `public/storage` symlink and patient upload directories are created and given full permissions.

---

## 2. User Alignments & Design Decisions

> [!IMPORTANT]
> **User Clarification Selections**:
> 1. **Queue Separation**: Doctor Selection Dropdown with real-time queue count badges (e.g. `DR. JUANA DE LA CRUZ (3 waiting)`) with automatic queue reloading and doctor assignment sync upon selection change.
> 2. **Drug Name Format**: Clean standard format: `Brand - Generic Dosage` (e.g., `Biogesic - Paracetamol 500mg`).
> 3. **Consultation / Professional Fee**: Configurable at doctor dashboard and profile page (`consultationfee` / `pfrate`), automatically populates when saving a consultation or opening the consultation charges tab, with editable amount.

> [!IMPORTANT]
> **Database Schema Evolution (Rule 4 Dual-Update & Setup Script Integrity)**:
> 1. Added `instructions` (`text nullable`) to `stocks_ledger` so every prescribed medicine retains its specific sig/instructions.
> 2. Added `photo_path` (`varchar(255) nullable`) to `pxmasterlist` so patient profile images are permanently stored on the master record and shared across all encounter records.
> 3. Synchronized `.gemini/database/kayakapmdv2_data_dictionary.md`.
> 4. Updated `setup.sh` with automated storage symlinking and upload directory initialization (`storage/app/private/patient_photo`, `storage/app/public/patients/photos`, `storage/app/private/radiology_results`, `storage/app/private/laboratory_results`).

---

## 3. Proposed & Implemented Changes Grouped by Component

### Component 1: Database Schema, Data Dictionary & Setup Script

#### [NEW] `database/migrations/2026_09_29_150000_add_instructions_to_stocks_ledger_and_photo_path_to_pxmasterlist.php`
- Adds `instructions` column to `stocks_ledger` after `remarks`.
- Adds `photo_path` column to `pxmasterlist` after `senior_idno`.
- Provides defensive `down()` rollback logic with `Schema::hasColumn` checks.

#### [MODIFY] `.gemini/database/kayakapmdv2_data_dictionary.md`
- Documented `instructions` under table `stocks_ledger`.
- Documented `photo_path` under table `pxmasterlist`.

#### [MODIFY] `setup.sh`
- Added automated `php artisan storage:link` execution if symlink is missing.
- Created patient photo and diagnostic attachment directories in `storage/app/`.

#### [MODIFY] `app/Models/Stocks/StocksLedgerModel.php` & `app/Models/PatientMasterlist.php`
- Added `instructions` to `$fillable` in `StocksLedgerModel`.
- Added `photo_path` to `$fillable` in `PatientMasterlist`.

#### [NEW] `app/Models/DwLibMedsGenericModel.php`
- Eloquent model mapping to `dw_lib_meds_generic` (`gen_code`, `gen_desc`).

---

### Component 2: Backend API Endpoints & Business Logic

#### [MODIFY] `app/Http/Controllers/ConsultationController.php`
- In `saveConsultation` and `updateConsultation`:
  - Handled patient image file upload (`patient_photo` / `patient_image`) and live webcam capture (`photo_base64`).
  - Decoded and saved images to `patient_photo/` disk, persisting path in `pxwalkinconsultation` and syncing to `pxmasterlist`.
  - Synchronized `address`, `landlinenumber`, `mobilenumber`, and `emailaddress` to `pxmasterlist`.
- In `fetchConsultation`:
  - Hydrated missing `address`, `landlinenumber`, and `photo_path` from `pxmasterlist`.
- Added `fetchDoctorsQueueCounts`:
  - Queries real-time queue count of active consultations grouped by `docrefno`.

#### [MODIFY] `app/Http/Controllers/DoctorController.php`
- In `fetchMedicineRx`: Included `instructions` column from `stocks_ledger`.
- In `addMedicine`: Accepted `instructions` payload and saved to `stocks_ledger`.
- In `deleteMedicine`: Removed medicine item from `stocks_ledger`.
- In `printPDF`: Passed `instructions` to prescription PDF view.
- In `updateDoctorFee`: Saved consultation fee to `doctors.pfrate` and `doctorsrights.consultationfee`.
- In `fetchTodaysPatients`: Filtered queue strictly to `auth()->guard('doctor')->user()->docrefno`.

#### [MODIFY] `app/Http/Controllers/ManagementController.php`
- In `fetchPatientDetails`: Added photo fallback prioritizing latest consultation photo, then patient masterlist photo, and finally `blank_photo.png`.
- In `fetchDrugRef`: Formatted generic search response for Select2 AJAX compatibility (`results` array with `id`, `text`, `gen_code`, `gen_desc`).
- In `saveStockItem` and `editStockItem`: Ensured `drug_grouping` is saved correctly.

#### [MODIFY] `app/Services/Admin/AdminService.php`
- Updated `getStockList` to support picklist regex matching (`whereIn`) and per-column filtering for Category (`item_grouping`).

#### [MODIFY] `routes/api.php`
- Registered routes:
  - `POST /api/fetch_doctors_queue_counts`
  - `POST /api/doctor/update_fee`
  - `POST /api/fetch_drug_generic` (alias to `fetchDrugRef`)

---

### Component 3: Frontend Views & Modals

#### [MODIFY] `resources/views/pages/secretary/queue.blade.php`
- Added "Medical History" nav tab after Consultation Details.
- Added `#medhistory_info` tab pane with consultation history table.
- Added hidden `#photo_base64` input for webcam capture data.

#### [MODIFY] `resources/views/modals/view_patient.blade.php`
- Added Tab 4 "Medical History" with `#view_medhistory_table`.

#### [MODIFY] `resources/views/modals/consultation_modal.blade.php`
- Enhanced patient details header with `#genphoto`, `#genpincode`, `#genpxrefno`, and `#genlandline`.
- Added `Instructions / Sig` column to `#dashboard_rx_table` and `#rx_table`.
- Added `#myinstructions` input to `#myrx_form`.

#### [MODIFY] `resources/views/pages/doctor/dashboard.blade.php`
- Added "Default Consultation Fee" card with `#doctor_pfrate` input and save button.

#### [MODIFY] `resources/views/printables/rx_print.blade.php`
- Updated prescription print layout to render individual medicine instructions/sig.

#### [MODIFY] `resources/views/modals/admin/add_item.blade.php` & `resources/views/modals/admin/edit_item.blade.php`
- Repositioned `#drug_fields` (Generic Name, Brand Name, Dosage, Group) ABOVE Name and PhilHealth Ref Code.
- Converted `#drug_generic` and `#edrug_generic` into Select2 searchable dropdowns.
- Repositioned Name and PhilHealth Ref Code row BELOW drug fields.

#### [MODIFY] `resources/views/pages/admin/stocks/management.blade.php`
- Added column header IDs (`#th_stock_actions`, `#th_stock_desc`, `#th_stock_group`, `#th_stock_phic`, `#th_stock_reg`, etc.).

---

### Component 4: Frontend JavaScript Controllers

#### [MODIFY] `resources/js/pages/secretary/queue.js`
- Implemented `updateDoctorQueueBadges()` for real-time doctor queue count badges.
- Synchronized `#doctor_for_consult` with `#doctor_id`.
- Implemented file upload preview and WebRTC webcam capture modal.
- Added `loadSecretaryMedhistory()` on patient select.

#### [MODIFY] `resources/js/pages/doctor/patients.js`
- Added `onerror` image fallback in masterlist table.
- Loaded patient consultation history into `#view_medhistory_table`.

#### [MODIFY] `resources/js/pages/doctor/consultation.js` & `form.js`
- Hydrated photo, PIN, ref no, landline in consultation modal.
- Supported `instructions` in prescription entry and rendering.
- Refreshed both Rx tables upon saving or deleting medicine.

#### [MODIFY] `resources/js/pages/doctor/dashboard.js`
- Handled `#save_doctor_fee_btn` with SweetAlert2 confirmation.

#### [MODIFY] `resources/js/pages/admin/stocks/stock_management.js`
- Integrated `table-column-filter.js` for custom filter and ordering headers.
- Integrated Select2 on `#drug_generic` and `#edrug_generic`.
- Auto-generated Name based on `Brand - Generic Dosage`.

---

## 4. Verification & Testing

### Automated Test Execution
- Executed `php artisan test` inside the Docker container (`latest_php_server`).
- **Result**: `58 passed (390 assertions)` across all test suites (`AdminManagementAndAddressIntegrationTest`, `AuthTest`, `ConsultationChargesAndSettlementsTest`, `DoctorSecretaryConsoleTest`, `ExampleTest`, `OpdConsultationWorkflowTest`, `PatientManagementAndPrintFixesTest`).

### Frontend Asset Compilation
- Executed `npm run build` with Vite.
- **Result**: Built successfully with zero compilation errors.
