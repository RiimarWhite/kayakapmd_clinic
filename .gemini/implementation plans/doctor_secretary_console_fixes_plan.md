# Implementation Plan: Doctor, Secretary & Admin Console Repairs and Feature Enhancements

Comprehensive technical plan to resolve Doctor schedule routing and viewing, Doctor account creation and profile page updates, Secretary/Admin user unification, doctor assignment repairs, and Secretary Console consultation actions ("Save as New Record", "Update Consultation Details", and "Mark as Complete").

---

## 1. Goal Description

This implementation addresses four core areas across the KayakapMD Clinic consultation and administration system:
1. **Doctor Schedules & Profile**:
   - Fix 404 routing errors on saving (`admin/users/create_doctor_schedules`) and viewing (`admin/users/fetch_doctor_schedules`) doctor schedules from the admin doctors management screen.
   - Align the Add and Edit Doctor forms and UI with the `doctors` table schema (adding `username` defaulting to last name, `S2no`, `PTR`, `clinicroom`, `clinichours`, `pfrate`, etc.).
   - Update the Doctor account profile page/modal to present the enhanced credentials, specialization, and room details.
2. **Secretaries & Admin Users Sub-System**:
   - Rebrand the sub-menu label and page header from **"Secretaries"** to **"Secretaries/Admin Users"** and update routes to `/admin/users/secretaries-admins` (with backwards-compatible aliases).
   - Add/Update Add Secretary and Add Admin forms and UI based on `secretaryrights` and `adminrights`, with `username` defaulting to their last name.
   - Repair the critical bug preventing `secretary.dummy@gmail.com` from selecting/adding all available doctors (caused by null `secrefno` in seeders, corrupted `docrefno: null` rows in `secretary_doctor`, and SQL `NOT IN (NULL)` logic failure).
   - Update the management table with a new **"Account Type"** column (`Secretary` vs `Admin`), filter icons on column headers, and a maximum rows display dropdown.
   - Implement record editing for both Secretary and Admin users, while restricting the **"Assigned Doctors"** button exclusively to Secretaries.
   - Update the account profile screens for Secretaries and Admin users.
3. **Secretary Console & Queue Dashboard (`admin/secretary` and `secretary/queue`)**:
   - Repair **"Save as New Record"** (`.save_consultation_btn`): fix fillable attributes on `ConsultationModel`, handle Admin guard contexts, resolve `$secretary->secusername` mismatch, and guard strict MySQL fields (`finadiagnosis`, `caseno`).
   - Repair **"Update Consultation Details"** (`.update_consultation_btn`): prevent 500 errors when no record is loaded, preserve photo paths, and validate date/time parsing.
   - Implement **"Mark as Complete"** button: wire up the unhandled button in `queue.blade.php` to `/api/update_queue_status`, update queue status to `COMPLETED`, show SweetAlert feedback, and refresh active queues.
4. **Adherence to Operational Rules & Skills**:
   - Dual-update database schema and authoritative data dictionary (`.gemini/database/kayakapmdv2_data_dictionary.md`).
   - Write comprehensive feature tests in `tests/Feature/DoctorSecretaryConsoleTest.php`.
   - Maintain `setup.sh` integrity and provide detailed code comments and conventional Git commit message.

---

## 2. User Review & Confirmed Decisions

The user has reviewed and confirmed the following design choices:
1. **Account Type in Table**: The new column in the unified table displays **"Secretary"** and **"Admin"** (Doctors retain their dedicated management page at `/admin/users/doctors`).
2. **Admin Schema Evolution**: New columns (`adminfname`, `adminmname`, `adminlname`, `admincontactno`, `adminemail`) will be added to `adminrights` via an idempotent migration and dual-updated in the data dictionary, enabling full admin profile management.
3. **Route Structure**: The page route will be `/admin/users/secretaries-admins` with route name `admin.users.secretaries_admin`, while maintaining `/admin/users/secretaries` as a backwards-compatible alias.

---

## 3. Architecture & Data Flow

```mermaid
flowchart TD
    subgraph Admin_Doctor_Management ["Doctor Management (/admin/users/doctors)"]
        D1["Doctor Table & Modals"] -->|Add/Edit Doctor| API_Doc["/api/add_doctor & /api/edit_doctor"]
        API_Doc --> DB_Doctors["doctors & doctorsrights"]
        D1 -->|Manage Schedules| Sched_Route["POST /admin/users/create_doctor_schedules & /admin/users/fetch_doctor_schedules"]
        Sched_Route --> SecretaryCtrl["SecretaryController::createSchedule & fetchSchedules"]
        SecretaryCtrl --> DB_Sched["docschedules"]
    end

    subgraph Admin_Staff_Management ["Staff Management (/admin/users/secretaries-admins)"]
        S1["Secretaries & Admin Table"] -->|Filter & Pagination| DT["DataTables (Length Menu: 10,25,50,100)"]
        S1 -->|Add/Edit Secretary| API_Sec["/api/add_secretary & /api/edit_secretary"]
        S1 -->|Add/Edit Admin| API_Adm["/api/add_admin & /api/edit_admin"]
        S1 -->|Assigned Doctors (Secretary Only)| API_Assigned["/api/fetch_secretary_doctors & /api/save_appended_doctors"]
        API_Assigned --> DB_SecDoc["secretary_doctor (Filtered non-null)"]
    end

    subgraph Secretary_Console ["Secretary Console (/admin/secretary & /secretary/queue)"]
        C1["Queue Screen & Consultation Form"] -->|Save as New Record| Save_API["/api/save_patient_consultation"]
        C1 -->|Update Consultation| Upd_API["/api/update_patient_consultation"]
        C1 -->|Mark as Complete| Comp_API["/api/update_queue_status (status=COMPLETED)"]
        Save_API & Upd_API & Comp_API --> ConsultationCtrl["ConsultationController"]
        ConsultationCtrl --> DB_Cons["pxwalkinconsultation"]
    end
```

---

## 4. Proposed Changes

### Component 1: Database Migration & Authoritative Data Dictionary Dual-Update

#### [NEW] `database/migrations/2026_09_19_121500_update_adminrights_and_clean_secretary_doctors.php`
- Add `adminfname`, `adminmname`, `adminlname`, `admincontactno`, and `adminemail` to `adminrights` table using `Schema::hasColumn` checks.
- Clean up corrupted `secretary_doctor` records where `docrefno IS NULL OR docrefno = ''`.
- Ensure all existing secretaries in `secretaryrights` have a valid, unique `secrefno` generated if null.

#### [MODIFY] `.gemini/database/kayakapmdv2_data_dictionary.md`
- Update Module 2 / `adminrights` table documentation to record the newly added columns (`adminfname`, `adminmname`, `adminlname`, `admincontactno`, `adminemail`).

---

### Component 2: Models & Fillable Attributes

#### [MODIFY] `app/Models/AdminModel.php`
- Add newly defined columns to `$fillable`:
  `'adminrefno'`, `'username'`, `'password'`, `'adminfname'`, `'adminmname'`, `'adminlname'`, `'admincontactno'`, `'adminemail'`, `'clientcode'`.

#### [MODIFY] `app/Models/ConsultationModel.php`
- Add missing columns to `$fillable`:
  `'caseno'`, `'queueno'`, `'secrefno'`, `'doccode'`, `'doccoaopd'`, `'finadiagnosis'`.
- In `booted()` event listener:
  - When `auth()->guard('admin')->check()`, set `$model->source_data = 'ADMIN'` and `$model->recordedby = auth()->guard('admin')->user()->username`.
  - Fix secretary recordedby accessor to safely handle `username` and fallback names.

---

### Component 3: Doctor Routes, Controller & UI Form Repairs

#### [MODIFY] `routes/web.php`
- Add the route aliases directly under the `admin` middleware group:
  ```php
  Route::post('admin/users/create_doctor_schedules', [SecretaryController::class, 'createSchedule']);
  Route::post('admin/users/fetch_doctor_schedules', [SecretaryController::class, 'fetchSchedules']);
  Route::post('admin/users/delete_schedule', [SecretaryController::class, 'deleteSchedule']);
  Route::post('admin/users/edit_schedule', [SecretaryController::class, 'editSchedule']);
  Route::post('admin/users/fetch_schedule_refno', [SecretaryController::class, 'fetchScheduleByRef']);
  ```

#### [MODIFY] `app/Http/Controllers/SecretaryController.php`
- In `createSchedule`:
  - Validate `docrefno`, `schedule_day`, `sched_from`, `sched_to`.
  - Prevent duplicate schedules for the same doctor, day, and time window.
  - Log schedule creation with structured context.
- In `fetchSchedules`:
  - Order schedules by natural weekday order (Sunday through Saturday) using MySQL `FIELD(day, 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday')`.

#### [MODIFY] `app/Http/Controllers/ManagementController.php`
- In `addDoctor`:
  - Validate and set `username` in `DoctorModel` (`doctorsrights`), defaulting to `$request->doclname` if blank.
  - Save `S2no`, `PTR`, `clinicroom`, `clinichours`, `pfrate` into `DoctorsProfileModel` (`doctors`).
- In `editDoctor`:
  - Support updating `username`, `S2no`, `PTR`, `clinicroom`, `clinichours`, `pfrate`.
  - Make `epass` optional: only re-hash and update password if `$request->filled('epass')`.

#### [MODIFY] `app/Http/Controllers/DoctorController.php`
- In `fetchDoctorUser`:
  - Query and attach `username` from `DoctorModel` to the returned doctor profile response object.

#### [MODIFY] `resources/views/modals/add_doctor.blade.php` & `resources/views/modals/edit_doctor.blade.php`
- Add `Username` input field with help text.
- Add `S2 License #` (`S2no`), `PTR #` (`PTR`), `Clinic Room` (`clinicroom`), `Clinic Hours` (`clinichours`), `Consultation Fee` (`pfrate`).
- In `edit_doctor.blade.php`: remove `required` attribute from `#epass` and add helper text ("Leave blank to keep existing password").

#### [MODIFY] `resources/js/pages/admin/users/doctors.js`
- Add auto-fill handler: typing in `#doclname` automatically populates `#username` (lowercased) unless manually modified.
- Include all new fields in form serialization and edit population (`loadDoctor`).

#### [MODIFY] `resources/views/modals/doctor_info.blade.php` & `resources/js/doctor.js`
- Display `Username`, `PRC License`, `PTR`, `S2 License`, `Room`, and `Clinic Hours` in the Doctor profile modal.

---

### Component 4: Secretaries & Admin Users Module Rebranding and Unification

#### [MODIFY] `routes/web.php`
- Register `admin/users/secretaries-admins` named `admin.users.secretaries_admin`.
- Maintain `admin/users/secretaries` as a redirect / alias to `admin.users.secretaries_admin`.

#### [MODIFY] `routes/api.php`
- Register API endpoints for Admin user management:
  - `fetch_admins`: get list of admin users.
  - `add_admin`: create new admin user.
  - `edit_admin`: update admin user details.
  - `delete_admin`: delete admin user (preventing self-deletion).
  - `fetch_admin_details`: get single admin record.

#### [MODIFY] `resources/views/components/sidebar.blade.php`
- Update sidebar navigation sub-item:
  - Label: **"Secretaries/Admin Users"**
  - Href: `{{ route('admin.users.secretaries_admin') }}`
  - Active check: `request()->routeIs('admin.users.secretaries_admin') || request()->routeIs('admin.users.secretaries')`.

#### [MODIFY] `resources/views/pages/admin/users/secretaries.blade.php`
- Update page header to **"Secretaries/Admin Users"**.
- Provide toggle buttons or tabs to switch between **"Add Secretary"** and **"Add Admin"** forms.
- For Secretary form: add explicit `username` field (defaults to last name).
- For Admin form: add fields based on `adminrights`: `adminfname`, `adminmname`, `adminlname`, `username`, `password`, `admincontactno`, `adminemail`.
- Table modifications:
  - Add **"Account Type"** column.
  - Place a filter icon on each column header.
  - Set table caption to "List of Secretaries & Admin Users".
- Include modals:
  - `@include('modals.edit_secretary')`
  - `@include('modals.edit_admin')`
  - `@include('modals.assigned_doctors')`

#### [MODIFY] `app/Http/Controllers/ManagementController.php`
- In `secretariesPage()` / `secretariesAdminsPage()`: serve view with necessary context.
- In `fetchSecretaries`:
  - Fetch all records from `secretaryrights` mapped with `account_type => 'Secretary'`.
  - Fetch all records from `adminrights` mapped with `account_type => 'Admin'`.
  - Merge and return unified collection.
- In `addAdmin`:
  - Validate `username` (unique in `adminrights`), `password` (min: 5), names, and email.
  - Default `username` to lowercased `adminlname` if omitted.
  - Hash password, assign `adminrefno`, set `clientcode`, and log action.
- In `editAdmin`:
  - Update admin user details and conditionally update password.
- In `deleteAdmin`:
  - Guard against deleting the currently authenticated admin (`auth()->guard('admin')->user()->adminrefno`).
- In `fetchSecretaryDoctors`:
  - Explicitly filter: `SecretaryDoctorsModel::where('secrefno', $request->secrefno)->whereNotNull('docrefno')->pluck('docrefno')`.
  - Guarantees `whereNotIn` receives only valid string doctor reference numbers, eliminating the SQL NULL failure.
- In `saveAppendedDoctors`:
  - Filter incoming `$request->doctors` array to remove any `null`, empty, or invalid elements.
  - Sync doctors cleanly to ensure deletions and additions reflect accurately.

#### [MODIFY] `resources/js/pages/admin/users/secretaries.js`
- Configure DataTables:
  - Set `lengthChange: true` and `lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]` for the max rows dropdown.
  - Column 0: Actions.
  - Column 1: Account Type badge (`<span class="badge bg-primary">Secretary</span>` or `<span class="badge bg-dark">Admin</span>`).
  - Column 2: Full Name / Username.
  - Column 3: Contact #.
  - Column 4: Email Address.
- Actions rendering:
  - If `data.account_type === 'Secretary'`: Render **"Edit"**, **"Assigned Doctors"**, and **"Delete"** buttons.
  - If `data.account_type === 'Admin'`: Render **"Edit"** and **"Delete"** buttons ONLY (no Assigned Doctors button).
- Auto-fill `username` from last name on both Secretary and Admin add forms.
- Add interactive column filter click handlers on table headers.
- Fix `#availdoctors` option append bug when removing assigned doctors: use `$(this).attr('id')` instead of `$(this).val()`.
- Add **"Assign All Doctors"** and **"Remove All"** buttons in `modals/assigned_doctors.blade.php`.

#### [MODIFY] `database/seeders/UserSeeder.php`
- Ensure `secretary.dummy@gmail.com` is seeded with a stable `secrefno = '03282026035828TASK'` and clean doctor assignments.

---

### Component 5: Secretary Console Consultation Actions Repairs

#### [MODIFY] `resources/views/pages/secretary/queue.blade.php`
- In card footer:
  - Update Mark as Complete button:
    ```html
    <button class="btn btn-sm btn-primary fw-bold" id="mark_as_complete_btn" type="button">
        <i class="fa-solid fa-square-check"></i> Mark as Complete
    </button>
    ```

#### [MODIFY] `resources/js/pages/secretary/queue.js`
- **Date Initialization**:
  - Default `#sched_date` to today's date if empty on page load.
- **Save as New Record (`.save_consultation_btn`)**:
  - Provide descriptive alert if doctor or required patient fields are missing.
  - Submit `FormData` via `/api/save_patient_consultation`.
  - On success: clear form, reload queue, and reset consultation reference.
- **Update Consultation Details (`.update_consultation_btn`)**:
  - Verify `#pxconsultationrefno` is populated before submitting. If empty, notify user to import a patient record first.
  - Submit `FormData` via `/api/update_patient_consultation`.
- **Mark as Complete (`#mark_as_complete_btn`)**:
  - Check if `#pxconsultationrefno` is present. If none, warn user with SweetAlert.
  - Confirm completion with modal dialog.
  - Send POST request to `/api/update_queue_status` with `consultationrefno: $("#pxconsultationrefno").text()` and `status: 'COMPLETED'`.
  - On response: notify user with success toast/alert, reset consultation form, and reload queue (`loadPatientTable()`, `refreshQueue()`).

#### [MODIFY] `app/Http/Controllers/ConsultationController.php`
- In `saveConsultation`:
  - Detect authenticated user from either `auth()->guard('secretary')->user()` or `auth()->guard('admin')->user()`.
  - Fix username lookup: `$secretary->username ?? $admin->username`.
  - Set `'caseno' => $this->generateCaseCode()`.
  - Default `'finadiagnosis' => ''` if empty.
  - Add structured logging with consultation reference number.
- In `updateConsultation`:
  - Check if `$record` exists; if not, return 404 with JSON message `Consultation record not found`.
  - Safely parse date and time.
  - Detect authenticated guard properly.
- In `updateQueueStatus`:
  - Accept `consultationrefno`, `casecode`, or `caseno`:
    ```php
    $query = ConsultationModel::query();
    if ($request->filled('consultationrefno')) {
        $query->where('consultationrefno', $request->consultationrefno);
    } elseif ($request->filled('casecode')) {
        $query->where('caseno', $request->casecode);
    } elseif ($request->filled('caseno')) {
        $query->where('caseno', $request->caseno);
    }
    ```
  - Update status and log state transition.

---

## 5. Verification Plan

### Automated Tests
Run comprehensive Laravel tests inside Docker container:
```bash
docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan test --filter=DoctorSecretaryConsoleTest
docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan test
```

Create `tests/Feature/DoctorSecretaryConsoleTest.php` covering:
1. `test_doctor_schedules_can_be_created_and_fetched_via_admin_routes()`
2. `test_doctor_created_with_username_defaulting_to_lastname_and_full_profile()`
3. `test_admin_user_can_be_added_listed_and_edited()`
4. `test_secretaries_admin_table_returns_both_account_types()`
5. `test_secretary_dummy_can_fetch_and_assign_all_doctors()`
6. `test_secretary_can_save_new_consultation_record()`
7. `test_secretary_can_update_consultation_details()`
8. `test_secretary_can_mark_consultation_as_completed()`

### Manual Verification
1. **Doctors**:
   - Navigate to `/admin/users/doctors`.
   - Click "Manage" on a doctor -> Click "Schedules" tab -> Verify schedules load without 404.
   - Add a schedule (Day + Time From + Time To) -> Click "Add Schedule" -> Verify schedule appears in table without 404.
   - Click "Add Doctor" -> Fill in details with Last Name "Smith" -> Verify username auto-populates as "smith". Fill in S2no, PTR, Clinic Room -> Save -> Verify doctor created.
   - Log in as doctor -> Open Profile modal -> Verify username, PTR, license, and room appear.
2. **Secretaries/Admin Users**:
   - Check sidebar: Sub-menu shows "Secretaries/Admin Users" and navigates to `/admin/users/secretaries-admins`.
   - Table displays "Account Type" column with "Secretary" and "Admin" badges.
   - Verify maximum rows dropdown allows changing page length (10, 25, 50, 100).
   - Verify filter icons are present on column headers.
   - Verify "Assigned Doctors" button appears ONLY on Secretary rows, not on Admin rows.
   - Click "Assigned Doctors" for `secretary.dummy@gmail.com` -> Verify available doctors appear in dropdown, can be selected, assigned, and saved without error.
   - Add an Admin account -> Verify admin appears in table with Account Type "Admin" and can be edited.
3. **Secretary Console & Queue**:
   - Log in as secretary or admin at `/admin/secretary` or `/secretary/queue`.
   - Click "Save as New Record" with valid schedule -> Verify record saves successfully and appears in queue table.
   - Click "Import" on patient queue item -> Form populates -> Change vitals -> Click "Update Consultation Details" -> Verify record updates.
   - With imported patient -> Click "Mark as Complete" -> Confirm SweetAlert dialog -> Verify patient status transitions to `COMPLETED` and queue refreshes.
