# Implementation Plan: Admin Credential Protection, Standalone Grouping Management, Secretary Queue & Masterlist Overhaul, and Doctor Consultation Sync

This implementation plan incorporates user feedback to establish a **standalone Grouping Management page** under Stocks & Services, bind modal group options directly to `stocks_groupings`, preserve the exact existing function of the Info button on the Patient Queue, and deliver all requested security and clinical workflow enhancements.

---

## 1. Goal Description

Implement comprehensive security, inventory grouping, and clinical workflow improvements across four primary subsystems:
1. **Admin Credential Protection**:
   - Reusable credential verification dialog requiring Admin Username and Password, a checkbox to allow access, and a duration dropdown (**1 hour**, **2 hours**, **1 day**).
   - Valid until user logs out or duration expires; stored in session (`admin_verified`, `admin_verified_username`, `admin_verified_expires_at`).
   - Logged-in Admins (`auth:admin`) bypass the prompt; Secretary sessions require Admin elevation.
   - Guards:
     - Fee editing and fee deletion in Secretary Queue.
     - Patient Masterlist record editing and deletion.
     - Patient Queue item deletion.
2. **Stocks & Services Management & Standalone Grouping Management**:
   - **Standalone Page**: `/admin/stocks/groupings` (`admin.stocks.groupings`) linked in the sidebar under Stocks & Services:
     - Full management interface to create, view, edit, and delete category-specific groupings stored in `stocks_groupings`.
     - Seeded with default Imaging groups (`xray`, `mri`, `ct scan`, `ultrasound`, `ob ultrasound`, `2d echo`) and default Drugs & Meds groups (`DRUGS AND MEDS`, `MEDICAL SUPPLIES`).
   - **Dynamic Group Dropdowns on `/admin/stocks/management`**:
     - The Group dropdown in Add Item (`#add_item_modal`) and Edit Item (`#edit_item_modal`) dynamically queries `stocks_groupings` filtered by the selected Category.
     - When Category = **Imaging**: group dropdown options are `xray`, `mri`, `ct scan`, `ultrasound`, `ob ultrasound`, `2d echo` (and any custom groups added for Imaging).
     - When Category = **Drugs & Medicines (DRUGS AND MEDS)**:
       - Add field `PhilHealth Gamot Essential` (checkbox) (`tinyint` column on `stocks_listing`).
       - Add `Dosage Form` dropdown (`N/A`, `Capsule`, `IV`, `Tablet`, or Custom Field) as a new column on `stocks_listing` and enable custom column header filter on the Stocks & Services DataTable.
3. **Secretary Queue & Patient Masterlist (`/admin/secretary` & `/secretary/queue`)**:
   - **Payment Details Tab**:
     - View settlement/payment details for the current consultation date.
     - Add **Previous Payments** table listing past payment and settlement history for the patient based on consultation history.
     - Fix fee editing and deletion with Admin Credential Protection.
   - **Patient Masterlist Card**:
     - Replace the "New/Unscheduled Patients" card with a 2-column "Patient Masterlist" (`Actions` and `Patient Name (Last, First, Middle, Suffix)`).
     - Header includes "Add Patient" button (`#btn_add_patient_masterlist`) opening `#add_patient_modal`.
     - Actions column has: Consultation History button, Import button, and an Actions dropdown (Edit, Delete — guarded by Admin Credential Protection).
   - **Consultation Card Clean-up**: Remove redundant "Add Patient" and "Patient Masterlist" buttons from the consultation card topbar.
   - **Patient Queue Actions**:
     - Replace import icon with "Show Info" eye icon button (`<i class="fa-solid fa-eye"></i>`), **retaining its full existing function** (loading patient info, demographics, vitals, medical history, and printable document links into the right-hand consultation form).
     - Merge Change Status, Reschedule, and Delete into a single dropdown button (Delete guarded by Admin Credential Protection).
   - **Patient Masterlist Modals UI**: Convert Add Patient (`#add_patient_modal`) and Edit Patient (`#editPatientModal`) into clean, responsive Bootstrap tabs (Personal & Identity, Contact & Address, Clinical / Classification) and fix HTML div nesting.
4. **Doctor Consultation Modal (`/doctor/consultation`)**:
   - Display active consultation date badge in the modal header.
   - Ensure all 5 tabs (Patient Charges, Radiology & Laboratory, Diagnostics Requests, Rx & Instructions, Impressions & Diagnosis) maintain on-demand loading upon click, with data strictly filtered by the active consultation date / reference.

---

## 2. Architecture & Request Flow

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│                                    KayakapMD Clinic                                    │
│                                                                                        │
│  ┌───────────────────────────┐      ┌────────────────────────┐  ┌───────────────────┐  │
│  │ Admin Credential Guard    │      │ Stocks & Services      │  │ Secretary Queue & │  │
│  │  - Session Elevation      │      │  - Standalone Groupings│  │ Masterlist        │  │
│  │  - Reusable Auth Modal/JS │      │    Page (/groupings)   │  │  - Info (Eye Icon)│  │
│  │  - Expiry & Logout Clear  │      │  - Dynamic Groups API  │  │  - Actions Dropdn │  │
│  └─────────────┬─────────────┘      │  - Dosage Form Filter  │  │  - Payment Details│  │
│                │                    └───────────┬────────────┘  └─────────┬─────────┘  │
│                │                                │                         │            │
│                ▼                                ▼                         ▼            │
│  ┌───────────────────────────┐      ┌────────────────────────┐  ┌───────────────────┐  │
│  │ Protected Actions:        │      │ stocks_groupings table │  │ Doctor Consult    │  │
│  │  - Fee Edit/Delete        │      │ stocks_listing columns:│  │ Modal:            │  │
│  │  - Masterlist Edit/Delete │      │  - philhealth_gamot_   │  │  - 5 Tabs Scoped  │  │
│  │  - Queue Item Delete      │      │    essential (tinyint) │  │    to Date        │  │
│  └───────────────────────────┘      │  - dosage_form (varch) │  │  - Header Badge   │  │
│                                     └────────────────────────┘  └───────────────────┘  │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 3. Database Schema Evolution & Dual-Update Protocol (Rules 3 & 4)

### New Migration: `database/migrations/2026_09_30_120000_create_stocks_groupings_and_add_dosage_to_stocks_listing.php`
- **Create Table `stocks_groupings`**:
  ```php
  Schema::create('stocks_groupings', function (Blueprint $table) {
      $table->id();
      $table->string('category', 50)->index(); // DRUGS AND MEDS, IMAGING, SUPPLIES, PROCEDURES, DIAGNOSTIC, PROFESSIONAL FEE
      $table->string('group_name', 100);
      $table->text('description')->nullable();
      $table->timestamps();
  });
  ```
  - Pre-seed default Imaging groups: `xray`, `mri`, `ct scan`, `ultrasound`, `ob ultrasound`, `2d echo`.
  - Pre-seed default Drugs & Meds groups: `DRUGS AND MEDS`, `MEDICAL SUPPLIES`.
- **Alter Table `stocks_listing`**:
  ```php
  Schema::table('stocks_listing', function (Blueprint $table) {
      if (!Schema::hasColumn('stocks_listing', 'philhealth_gamot_essential')) {
          $table->tinyInteger('philhealth_gamot_essential')->default(0)->after('yakap_essential');
      }
      if (!Schema::hasColumn('stocks_listing', 'dosage_form')) {
          $table->string('dosage_form', 50)->default('N/A')->after('drug_dosage');
      }
  });
  ```

### Data Dictionary Update: `.gemini/database/kayakapmdv2_data_dictionary.md`
- Document `stocks_groupings` under **Module 6: Pharmacy & Inventory**.
- Add `philhealth_gamot_essential` and `dosage_form` to `stocks_listing` definition.

---

## 4. Proposed Changes by Component

---

### Component 1: Admin Credential Protection Framework

#### [NEW] `resources/views/modals/admin_verification_modal.blade.php`
- Reusable modal with Admin Username, Admin Password, "Allow Access" checkbox, and duration dropdown (1 hour, 2 hours, 1 day).

#### [NEW] `resources/js/helpers/admin_auth.js`
- Export `requireAdminAuth(callback)` helper function.
- Calls `/api/check_admin_elevation`. If elevated, immediately calls `callback()`.
- If not elevated, presents `#admin_verification_modal`, verifies credentials via `/api/verify_admin_credentials`, saves elevation in session, and executes `callback()`.

#### [MODIFY] `app/Http/Controllers/LoginController.php`
- In `logout()`, explicitly invalidate elevation session keys:
  ```php
  $request->session()->forget(['admin_verified', 'admin_verified_username', 'admin_verified_expires_at']);
  ```

#### [NEW] `app/Http/Controllers/Api/AdminVerificationController.php`
- `checkAdminElevation()`: returns `{ elevated: true/false }`. Direct admin users (`auth:admin`) always return `true`.
- `verifyAdminCredentials(Request $request)`:
  - Validates `username`, `password`, `remember_access` (boolean), `duration` (`1_hour`, `2_hours`, `1_day`).
  - Checks against `AdminModel` credentials via `Hash::check()`.
  - On success, sets session `admin_verified_expires_at`:
    - `1_hour`: `now()->addHour()->timestamp`
    - `2_hours`: `now()->addHours(2)->timestamp`
    - `1_day`: `now()->addDay()->timestamp`

---

### Component 2: Stocks & Services Management & Standalone Grouping Page

#### [NEW] `app/Models/Stocks/StocksGroupingModel.php`
- Eloquent model mapping to `stocks_groupings`.

#### [NEW] `resources/views/pages/admin/stocks/groupings.blade.php`
- Standalone page for managing category groupings.
- Header: Title "Grouping Management", breadcrumbs, and "Add New Grouping" button.
- Filter: Category selector tabs/dropdown (All, Drugs & Meds, Imaging, Supplies, Procedures, Diagnostic, Professional Fee).
- Table `#groupings_table`: `#`, `Category`, `Group Name`, `Description`, `Actions` (Edit, Delete).

#### [NEW] `resources/js/pages/admin/stocks/groupings.js`
- Handles DataTables initialization, category filtering, Add/Edit modal submission (`/api/stocks/save_grouping`, `/api/stocks/update_grouping`), and deletion (`/api/stocks/delete_grouping`).

#### [MODIFY] `routes/web.php` & `resources/views/components/sidebar.blade.php`
- Register `Route::get('admin/stocks/groupings', [ManagementController::class, 'stocksGroupingsPage'])->name('admin.stocks.groupings');` in `routes/web.php`.
- In `sidebar.blade.php` under Stocks & Services collapse: add "Grouping Management" navigation link.

#### [MODIFY] `resources/views/pages/admin/stocks/management.blade.php`
- Add "Grouping Management" direct link button in the toolbar.
- Add Dosage Form column filter header (`#th_stock_dosage_form`).

#### [MODIFY] `resources/views/modals/admin/add_item.blade.php` & `resources/views/modals/admin/edit_item.blade.php`
- Add `PhilHealth Gamot Essential` checkbox (`#philhealth_gamot_essential` / `#ephilhealth_gamot_essential`).
- Add `Dosage Form` dropdown (`#dosage_form` / `#edosage_form`) with options: `N/A`, `Capsule`, `IV`, `Tablet`, `Custom Field...` (toggles custom text input).
- Update the Group selector (`#item_group_select` / `#eitem_group_select` or `#drug_group` / `#edrug_group`) to be dynamically loaded from `/api/stocks/fetch_groupings?category=...`.
- Conditional display logic:
  - When Category = `DRUGS AND MEDS`: displays drug fields, generic Select2, brand, dosage, dosage form, PhilHealth Gamot Essential, and dynamic groups.
  - When Category = `IMAGING`: dynamically populates groups (`xray`, `mri`, `ct scan`, `ultrasound`, `ob ultrasound`, `2d echo`, etc.).

#### [MODIFY] `resources/js/pages/admin/stocks/stock_management.js`
- Integrate dynamic group options querying `/api/stocks/fetch_groupings?category=...`.
- Add dosage form custom column filter using `renderColumnFilterHeader('Dosage Form', ...)`.
- Pass `philhealth_gamot_essential` and `dosage_form` in add/edit payloads.

#### [MODIFY] `app/Http/Controllers/ManagementController.php` & `app/Services/Admin/AdminService.php`
- Add endpoints:
  - `stocksGroupingsPage()`: returns `pages.admin.stocks.groupings`.
  - `fetchGroupings(Request $request)`: returns groups from `stocks_groupings` optionally filtered by category.
  - `saveGrouping(Request $request)`, `updateGrouping(Request $request)`, `deleteGrouping(Request $request)`.
- Update `saveStockItem`, `editStockItem`, and `getStockList` to handle `philhealth_gamot_essential`, `dosage_form`, and dynamic groupings.

---

### Component 3: Secretary Queue & Patient Masterlist Overhaul

#### [MODIFY] `resources/views/pages/secretary/queue.blade.php`
- **Right Column (Consultation Card Topbar)**:
  - Remove "Add New Patient Record" (`#add_new_patient_btn`) and "Patient Masterlist" (`#view_masterlist_btn`).
- **Left Column**:
  - Replace "New/Unscheduled Patients" card with "Patient Masterlist" card.
  - Card Header: Title "Patient Masterlist" + "Add Patient" button (`#btn_add_patient_masterlist`).
  - Table `#patient_masterlist_queue_table`:
    - Columns: `Actions` (width 1%), `Patient Name (Last, First, Middle, Suffix)`.
- **Patient Queue Table**:
  - Action column:
    - Eye icon "Show Info" button (`.import-queue` / `.show-info-queue` with `<i class="fa-solid fa-eye"></i>`), **maintaining existing function** (loading patient demographics, vitals, medical history, and printable document links into consultation form).
    - Merged single dropdown button (`.queue-actions-dropdown`):
      - "Change Status"
      - "Reschedule"
      - "Delete Queue Record" (danger text, triggers Admin Credential Protection)
- **Payment Details Tab (`#payment_info`)**:
  - Sub-section 1: **Current Consultation Charges & Payment**
    - Charges table with Actions (Edit & Delete fee, both Admin Credential Protected).
    - Payment & Settlement summary card: Gross Total, PHIC Deduction, HMO Deduction, Net Payable, Cash, Card, Status Badge, TRX Ref, Cashier/Date.
  - Sub-section 2: **Previous Payments (Consultation History)**
    - Table `#px_previous_payments_table`: Consultation Date, TRX Ref, Gross, PHIC, HMO, Net Payable, Cash, Card, Settled By, Status.

#### [MODIFY] `resources/views/modals/add_patient.blade.php` & `resources/views/modals/edit_patient.blade.php`
- Refactor `#add_patient_modal` into standard Bootstrap Tabs:
  - **Tab 1: Personal & Identity**: Photo upload + camera capture, First/Middle/Last/Suffix, Sex, DOB, Age, PhilHealth PIN, Religion, Nationality, Senior ID, PWD status. Fix unclosed div syntax errors.
  - **Tab 2: Contact & Address**: Mobile No, Email, Landline, PSGC Cascading Selects (Region, Province, City, Barangay, Street, Zip, Full Address).
  - **Tab 3: Clinical / Classification**: Risk classification, emergency contacts, notes.
- Mirror same clean tab structure and responsiveness on `#editPatientModal`.

#### [MODIFY] `resources/js/pages/secretary/queue.js`
- Integrate `admin_auth.js`:
  - Wrap `.remove_charge` and `.edit_charge_btn_sc` in `requireAdminAuth()`.
  - Implement full SweetAlert2 edit modal for `.edit_charge_btn_sc` posting to `/api/update_charge`.
  - Wrap Masterlist Edit and Delete buttons in `requireAdminAuth()`.
  - Wrap Queue Delete button in `requireAdminAuth()`.
- Implement Masterlist table initialization on the left column querying `/api/fetch_patient_masterlist_sec`.
  - Action buttons: Consultation History, Import, and Actions dropdown (Edit, Delete).
- Retain exact existing handler for `.import-queue` / `.show-info-queue` button using eye icon.
- Implement Queue Actions dropdown (Change Status, Reschedule, Delete Queue).
- Implement Current Settlement and Previous Payments loader on `#patient_charges_btn` click:
  - Fetches current settlement from `/api/fetch_settlements`.
  - Fetches past payments history for this patient via `/api/fetch_patient_payment_history`.

#### [NEW / MODIFY] Backend Endpoints:
- `ConsultationController::deletePatientQueue`: deletes consultation queue record with Admin Protection check.
- `SecretaryController::fetchPatientPaymentHistory`: queries `pxsettlements` for all past consultations matching `pincode` or `pxrefno`.

---

### Component 4: Doctor Consultation Modal Date-Based Synchronization

#### [MODIFY] `resources/views/modals/consultation_modal.blade.php`
- Display active consultation date badge in modal header:
  ```html
  <span class="badge bg-primary fs-6 ms-3" id="modal_consultation_date_badge">Consultation Date: </span>
  ```

#### [MODIFY] `resources/js/pages/doctor/consultation.js` & `resources/js/pages/doctor/consultation/form.js`
- Update `loadConsultModal()`:
  - Set active consultation date on `#modal_consultation_date_badge`.
  - On tab click (on-demand loading per user clarification):
    - **Patient Charges**: `#patient_charge_tab_btn` loads charges scoped strictly to `consultationrefno`.
    - **Radiology & Laboratory**: `#radLabTabBtn` loads files and preview links scoped to `consultationrefno`.
    - **Diagnostics Requests**: `#dReqsTabBtn` loads diagnostics scoped to `consultationrefno`.
    - **Rx & Instructions**: `#rx_sidebar_btn` loads prescription medicines and sig scoped to `consultationrefno`.
    - **Impressions & Diagnosis**: `#impDiagBtn` loads impressions, diagnosis, reasons, and admission orders scoped to `consultationrefno`.
  - Clear tab tables/forms on modal close/reset to prevent stale patient data bleeding across consultations.

---

## 5. Verification Plan

### Automated Tests
1. **Admin Credential Verification Test**:
   - `tests/Feature/AdminCredentialProtectionTest.php`:
     - Test `/api/verify_admin_credentials` with valid and invalid credentials.
     - Test session expiration persistence across 1 hour, 2 hours, 1 day.
     - Verify session clearance on logout.
     - Verify 403 on protected delete/update endpoints when un-elevated.
2. **Stocks Groupings & Custom Filter Test**:
   - `tests/Feature/StocksGroupingAndFiltersTest.php`:
     - Test CRUD for `stocks_groupings` on the standalone page endpoints.
     - Test `stocks_listing` migration adding `philhealth_gamot_essential` and `dosage_form`.
     - Test filtering by dosage form.
3. **Secretary Masterlist & Queue Actions Test**:
   - `tests/Feature/SecretaryQueueAndMasterlistTest.php`:
     - Test patient queue delete endpoint with elevation.
     - Test patient payment history retrieval.
4. **Existing Regression Suite**:
   - Run complete suite: `php artisan test`.

### Manual Verification
1. Log in as Secretary (`/secretary/queue`):
   - Click "Delete" on Patient Queue → Admin Verification modal opens. Enter valid admin credentials with "1 hour" → successfully deletes queue record. Perform another protected delete → bypasses prompt (active session elevation).
   - Click eye icon ("Show Info") on Patient Queue → patient details, demographics, vitals, medical history, and print links load into the consultation form smoothly without altering queue status.
   - Log out and log back in → prompt reappears (session elevation invalidated on logout).
   - Test Payment Details tab: view charges, view payment summary, view previous payments list.
   - Click "Edit Fee" on charge item → opens edit modal, updates price/qty.
   - Inspect Patient Masterlist card: click "Add Patient" → clean tabbed modal opens. Test Import, History, Edit, Delete.
2. Log in as Admin (`/admin/stocks/groupings` & `/admin/stocks/management`):
   - Navigate to `/admin/stocks/groupings` via sidebar → test creating, editing, and deleting category groupings.
   - Open Add Item modal in `/admin/stocks/management`:
     - Select Category "Imaging" → group dropdown shows `xray`, `mri`, `ct scan`, etc.
     - Select Category "DRUGS AND MEDS" → shows Generic Select2, PhilHealth Gamot Essential checkbox, Dosage Form dropdown (Capsule, IV, Tablet, Custom).
   - Filter Stocks table by Dosage Form.
3. Log in as Doctor (`/doctor/consultation`):
   - Select consultation date and proceed to consultation.
   - Verify modal header displays active consultation date.
   - Click each of the 5 tabs → verify contents match the selected consultation record.
