# Survey and Mapping Report: R3, R4, R5

**Author**: Explorer 3 (`explorer_survey_v2_3`)  
**Working Directory**: `C:\xampp\htdocs\kayakapmd_clinic\.agents\teamwork\explorer_survey_v2_3\`  
**Date**: 2026-10-12  
**Baseline Test Status**: 138 passed (1059 assertions) via `php artisan test`

---

## 1. Observation

### R3: Users Management — Admin Doctor Management

#### 1.1 Route Overwrite Collision in `routes/api.php`
In `routes/api.php`, schedule management routes are declared twice under two conflicting middleware groups:

- **Group 1 (`auth:secretary,admin`)** (lines 15–56):
  ```php
  Line 49: Route::post('fetch_doctor_schedules_specific', [SecretaryController::class, 'fetchSchedulesSpecific'])->name('secretary.fetch_schedule_specific');
  Line 50: Route::post('fetch_doctor_schedules', [SecretaryController::class, 'fetchSchedules'])->name('secretary.fetch_schedule');
  Line 51: Route::post('create_doctor_schedules', [SecretaryController::class, 'createSchedule'])->name('secretary.create_schedule');
  Line 52: Route::post('delete_schedule', [SecretaryController::class, 'deleteSchedule']);
  Line 54: Route::post('fetch_schedule_refno', [SecretaryController::class, 'fetchScheduleByRef']);
  Line 55: Route::post('edit_schedule', [SecretaryController::class, 'editSchedule']);
  ```
- **Group 2 (`auth:doctor,admin`)** (lines 85–110):
  ```php
  Line 101: Route::post('doctor/create_schedule', [DoctorController::class, 'createSchedule'])->name('doctor.create_schedule');
  Line 102: Route::post('doctor/fetch_schedule_refno', [DoctorController::class, 'fetchScheduleByRef'])->name('doctor.fetch_schedule_refno');
  Line 103: Route::post('doctor/edit_schedule', [DoctorController::class, 'editSchedule'])->name('doctor.edit_schedule');
  Line 104: Route::post('doctor/delete_schedule', [DoctorController::class, 'deleteSchedule'])->name('doctor.delete_schedule');
  // Generic schedule route aliases for doctor guard:
  Line 106: Route::post('create_doctor_schedules', [DoctorController::class, 'createSchedule']);
  Line 107: Route::post('fetch_schedule_refno', [DoctorController::class, 'fetchScheduleByRef']);
  Line 108: Route::post('edit_schedule', [DoctorController::class, 'editSchedule']);
  Line 109: Route::post('delete_schedule', [DoctorController::class, 'deleteSchedule']);
  ```

Because Laravel route registration evaluates later definitions last, lines 106–109 **silently overwrite** lines 51, 52, 54, and 55.

Inside `DoctorController::createSchedule` (line 629) and `DoctorController::deleteSchedule` (line 741):
```php
public function createSchedule(Request $request)
{
    $doctor = auth()->guard('doctor')->user();
    if (!$doctor) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    }
```
When an Admin or Secretary user submits a schedule from `#manage_doctor_modal` to `/api/create_doctor_schedules`, the request matches the overwritten route targeting `DoctorController`. Because the admin user has no doctor session, `$doctor` is `null`, resulting in HTTP 401 Unauthorized!

#### 1.2 Missing Routes and Handlers for Questions
In `routes/api.php`, only two question routes exist:
```php
Line 46: Route::post('fetch_doctor_questions', [SecretaryController::class, 'fetchQuestions'])->name('secretary.fetch_questions');
Line 47: Route::post('create_question', [SecretaryController::class, 'createQuestion'])->name('secretary.create_question');
```
There are **zero route definitions** for `edit_question` or `delete_question`.  
Furthermore, `SecretaryController.php` contains **no methods** named `editQuestion` or `deleteQuestion`.  
In `resources/js/secretary-management.js` (lines 152–153):
```javascript
return `<button class="btn btn-sm btn-primary edit-question" value="${data.docquestionrefno}"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
        <button class="btn btn-sm btn-danger delete-question" value="${data.docquestionrefno}"><i class="fa-solid fa-trash-can"></i> Delete</button>`;
```
These buttons are rendered in the DataTable, but **no click handlers** exist anywhere in `resources/js` for `.edit-question` or `.delete-question`.

#### 1.3 Profile and Model Resolution Bug in `SecretaryController::createQuestion`
In `app/Http/Controllers/SecretaryController.php` (lines 137–154):
```php
public function createQuestion(Request $request)
{
    // Secretary pero admin ni na user kay gipamove diri from secretary
    $secretary = auth()->guard('admin')->user();
    $doctor = DoctorsProfileModel::where(['docrefno' => $request->docrefno])->first();

    DoctorQuestionsModel::create([
        'docquestionrefno' => Date::now()->format('mdYHis') . "QT",
        "secrefno" => $secretary->adminrefno,
        "question" => $request->question,
        "docrefno" => $request->docrefno,
        "docname" => $doctor->fname . ' ' . $doctor->mname . ' ' . $doctor->lname,
        "recordeddate" => Date::now(),
        "recordedby" => $secretary->username,
    ]);

    return response()->json(['success' => true]);
}
```
Observations:
1. `$secretary = auth()->guard('admin')->user();`: If a secretary user is logged in, `$secretary` evaluates to `null`. Attempting to read `$secretary->adminrefno` throws:  
   `Error: Attempt to read property "adminrefno" on null`.
2. `$doctor->fname . ' ' . $doctor->mname . ' ' . $doctor->lname`: In `app/Models/DoctorsProfileModel.php` (lines 18–23), the database attributes are `doclname`, `docfname`, `docmname`, and `docname`. Attributes `fname`, `mname`, `lname` do not exist. Accessing them produces `null` and evaluates to `'  '` (empty spaces) or deprecation warnings.
3. If `$request->docrefno` is invalid or not found, `$doctor` is `null`. Accessing `$doctor->fname` throws:  
   `Error: Attempt to read property "fname" on null` (500 Server Error) instead of returning a clean 404 response.

#### 1.4 Table Refresh Behavior in `#manage_doctor_modal`
In `resources/js/secretary-management.js`:
- For questions (line 198–210):
  ```javascript
  success: function (response) {
      if (response.success) {
          Swal.fire({ ... }).then((result) => {
              if (result.isConfirmed) {
                  $("#questions_tab").trigger("click");
                  $("#create_question_form")[0].reset();
              }
          });
      }
  }
  ```
  If the alert is dismissed via escape, outside click, or timer, `result.isConfirmed` is `false`, and the form is not reset.
  Additionally, clicking `#questions_tab` destroys and recreates the DataTable without checking `$.fn.DataTable.isDataTable('#doctor_questions_tab')`, causing visual flicker or re-initialization errors.
  There is no `error:` callback on `$.ajax`, so if a 401, 404, or 500 error occurs, no feedback is provided.
- For schedules (line 448–462):
  ```javascript
  $.ajax({
      url: "/api/create_doctor_schedules",
      ...
  ```
  Because the endpoint hit `DoctorController::createSchedule` and failed with 401 Unauthorized, and had no `error:` callback, the schedule table was never updated.

---

### R4: Doctor Dashboard

#### 2.1 DataTables Column Count Warning on `#todays_patients_table`
In `resources/views/pages/doctor/dashboard.blade.php` (lines 21–38):
```html
<table class="table table-sm table-bordered" id="todays_patients_table">
    <thead class="table-primary">
        <tr>
            <th scope="col">Queue</th>
            <th scope="col">Patient Name</th>
            <th scope="col">Status</th>
            <th scope="col" class="text-center" style="width: 100px;">Action</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </tbody>
</table>
```
- The header defines **4 columns** (`Queue`, `Patient Name`, `Status`, `Action`).
- The placeholder `<tbody>` contains **only 3 `<td>` cells**.
- DataTables initialization in `resources/js/pages/doctor/dashboard.js` (line 4) triggers the browser console warning:  
  `DataTables warning: table id=todays_patients_table - Column count mismatch: 4 columns in the header vs 3 columns in the body`.

#### 2.2 Patient Row Click vs Details Button in Today's Patients
In `resources/js/pages/doctor/dashboard.js` (line 697):
```javascript
$(document).on("click", ".view-consultation-details-btn, .btn-view-details", function () {
    ...
    openConsultationDetailsModal(record);
});
```
- Currently, only the button `.view-consultation-details-btn` has a click listener. Clicking anywhere on the row (`#todays_patients_table tbody tr`) does nothing.
- Clicking the button directly opens `#view_consultation_details_modal` for that single consultation.
- It does **not** open `#patient_consultations_history_modal` as required.
- `#todays_patients_table` should open `#patient_consultations_history_modal` (or `#patientMedhistoryModal`), displaying all past and current consultations for that patient. Selecting a history item then opens `#view_consultation_details_modal`.

#### 2.3 Missing Components in `#view_consultation_details_modal`
In `resources/views/modals/view_consultation_details.blade.php`:
1. **Medical History Tab**: Missing. Vertical tabs include only:
   - Impressions & Diagnosis (`#vcd_pane_impdiag`)
   - Rx & Instructions (`#vcd_pane_rx`)
   - Diagnostic Requests (`#vcd_pane_dreqs`)
   - Radiology & Laboratory (`#vcd_pane_radlab`)
   - Patient Charges (`#vcd_pane_charges`)
   There is no Medical History tab displaying permanent medical history (allergies, immunizations, maintenance medications, past medical/surgical history, family history).
2. **Patient Classification & Dynamic Fields**: Missing. Only a generic type badge (`Type: REGULAR`) is rendered. No display exists for OB/GYN history (Gravida, Para, Abortion, IUFD, Died, LMP Date), Delivery Procedure, Pathologic Case, or New Born procedure source.
3. **Answered Doctor's Questions**: Missing. Impressions & Diagnosis displays only chief complaint, impressions, and diagnosis. Answered doctor questions from `walkinconsultation_answer` are omitted.
4. **Dedicated Preview Modal**: Buttons for Radiology and Laboratory attachments currently have `target="_blank"`, opening a new browser tab rather than opening the dedicated `#preview_modal`. `#preview_modal` is not included in `dashboard.blade.php`.

---

### R5: Doctor Consultation Page

#### 3.1 Patient List in Consultation Table Omits Queue Number
In `app/Http/Controllers/DoctorController.php` (lines 576–584):
```php
public function fetchConsultationPatients(Request $request)
{
    $patients = ConsultationModel::select('consultationrefno', 'status', 'patientname', 'pxmidname', 'pxlastname', 'pxsuffix', 'reasonforconsultation')
        ->where(['docrefno' => auth()->guard('doctor')->user()->docrefno])
        ->whereDate('consultation_date', $request->date)
        ->orderBy('queueno', 'ASC')
        ->get();
    return response()->json(['patients' => $patients]);
}
```
The query orders by `queueno`, but `queueno` is **omitted from the `select` list**!  
In `resources/js/pages/doctor/consultation.js` (lines 56–58):
```javascript
columns: [
    { data: null, render: function (data, type, row) { return `<span data-consultation="${row.consultationrefno}">${data.patientname}</span>`; } },
    { data: 'status', ... }
]
```
The table displays only `patientname`. The Queue Number is completely absent.

#### 3.2 HTML Hierarchy Bug: Unclosed Tab Container in `#consultation_modal`
In `resources/views/modals/consultation_modal.blade.php` (lines 330–361):
```html
330: <div class="tab-pane fade h-100" id="main_medhistory_pane" role="tabpanel" aria-labelledby="main_medhistory_tab_btn" tabindex="0">
331:     <div class="d-flex flex-column h-100 p-2 overflow-y-auto">
...
342:         <div class="table-responsive flex-grow-1">
343:             <table class="table table-sm table-bordered table-hover align-middle w-100" id="medhistory_table">
...
355:                 <tbody></tbody>
356:             </table>
357:         </div>
358:     </div>
359: 
360: <!-- TAB 3: PERMANENT MEDICAL HISTORY -->
361: <div class="tab-pane fade h-100" id="main_permanent_medhistory_pane" role="tabpanel" aria-labelledby="main_permanent_medhistory_tab_btn" tabindex="0">
```
- Line 330 opens `<div class="tab-pane ... id="main_medhistory_pane">`.
- Line 331 opens `<div class="d-flex ...">`.
- Line 342 opens `<div class="table-responsive ...">`.
- Line 357 closes `table-responsive`.
- Line 358 closes `d-flex`.
- **The closing `</div>` for `#main_medhistory_pane` (line 330) is completely missing.**
- Consequently, `#main_permanent_medhistory_pane` (line 361) is **nested inside** `#main_medhistory_pane`!
- At lines 446–449, four orphaned closing `</div>` tags were appended to compensate:
  ```html
  446: </div>
  447: </div>
  448: </div>
  449: </div>
  450: 
  451: <div class="modal-footer">
  ```
Because `#main_permanent_medhistory_pane` is nested inside an inactive `fade` tab pane (`#main_medhistory_pane`), activating the "Medical History" tab causes vertical tab clipping, overlapping, or complete failure to display.

#### 3.3 "Blocked aria-hidden on an element because its descendant retained focus" Console Warning
When transitioning between modals (e.g. from `#consultation_modal` to `#rx_modal`, `#diagnostic_modal`, `#preview_modal`, or closing a modal via a button with `data-bs-dismiss="modal"`):
- The trigger button currently retains document focus (`document.activeElement`).
- Bootstrap applies `aria-hidden="true"` to the modal container while the descendant button still holds focus.
- Chromium logs:  
  `Blocked aria-hidden on an element because its descendant retained focus. The element was <div ... id="consultation_modal"> and its descendant was <button id="close-consultation-modal">.`
- There is currently no `document.activeElement.blur()` executed during `show.bs.modal` or `hide.bs.modal` transitions.

---

## 2. Logic Chain

1. **R3 Route Collision & Authorization**:
   - `routes/api.php` line 106 defines `Route::post('create_doctor_schedules', [DoctorController::class, 'createSchedule'])`.
   - Because it follows line 51 (`SecretaryController::class, 'createSchedule'`), it takes precedence.
   - `DoctorController::createSchedule` checks `auth()->guard('doctor')->user()`.
   - For an Admin user managing doctor schedules in `/admin/users/doctors`, `auth()->guard('doctor')->user()` is `null`.
   - Therefore, the request returns 401 Unauthorized.
   - Removing the duplicate routes on lines 106–109 in the doctor group allows the `SecretaryController` endpoints (protected by `auth:secretary,admin`) to handle admin and secretary requests. Doctor self-service routes are already dedicated on lines 101–104 (`doctor/create_schedule`, `doctor/edit_schedule`, `doctor/delete_schedule`, `doctor/fetch_schedule_refno`).

2. **R3 Missing Question Methods & Doctor Profile Attributes**:
   - `DoctorQuestionsModel` records who created the question (`secrefno`, `recordedby`) and doctor identity (`docrefno`, `docname`).
   - Resolving `$user = auth()->guard('admin')->user() ?? auth()->guard('secretary')->user();` ensures neither role triggers property access on `null`.
   - `DoctorsProfileModel` attributes are `docfname`, `docmname`, `doclname`, and `docname`.
   - Resolving `$docname = $doctor->docname ?: trim("{$doctor->docfname} {$doctor->docmname} {$doctor->doclname}");` guarantees a valid doctor name is persisted.
   - Adding `editQuestion` and `deleteQuestion` in `SecretaryController` and registering routes in `routes/api.php` completes CRUD capability.

3. **R4 Column Count Warning**:
   - `todays_patients_table` header defines 4 `<th>` elements.
   - Body placeholder defines 3 `<td>` elements.
   - Adding a 4th `<td></td>` in `dashboard.blade.php` eliminates the column count mismatch warning.

4. **R4 Modal Navigation Flow**:
   - Clicking a row or "Details" button in `#todays_patients_table` must trigger `#patient_consultations_history_modal` (or `#patientMedhistoryModal`), passing the patient's `pxrefno`, `pincode`, and `consultationrefno`.
   - `/api/fetch_patient_medhistory` retrieves all historical consultations for that patient.
   - In the history table, clicking a consultation row opens `#view_consultation_details_modal` populated with that consultation's details.

5. **R5 Queue Number & Unclosed HTML Structure**:
   - Adding `'queueno'` to `ConsultationModel::select(...)` in `DoctorController::fetchConsultationPatients` ensures `queueno` is included in the JSON response.
   - In `consultation.js`, rendering a `#${row.queueno}` badge alongside `patientname` fulfills the requirement.
   - Closing `#main_medhistory_pane` with `</div>` before `#main_permanent_medhistory_pane` restores proper sibling status to both tab panes inside `#consultation_main_tab_content`, fixing tab overlapping.
   - Invoking `if (document.activeElement?.blur) document.activeElement.blur();` on modal show/hide eliminates the Chromium `blocked aria-hidden` warning.

---

## 3. Caveats

1. **Dual Modal Identity**:
   - `#patientMedhistoryModal` in `resources/views/modals/patient_masterlist.blade.php` already serves as a patient consultation history viewer.
   - To support both specifications, we recommend creating a dedicated `resources/views/modals/patient_consultations_history.blade.php` with ID `patient_consultations_history_modal` (or aliasing `#patientMedhistoryModal` with `id="patient_consultations_history_modal"`) and including it in both `dashboard.blade.php` and `consultation.blade.php`.
2. **Dedicated Preview Modal Scope**:
   - `#preview_modal` is currently defined at the bottom of `consultation_modal.blade.php`.
   - Extracting it into a clean partial `resources/views/modals/preview_modal.blade.php` allows it to be included cleanly in `dashboard.blade.php`, `consultation.blade.php`, and `queue.blade.php`.
3. **Database Schema on Dynamic Classification Fields**:
   - Classification fields `gravida`, `para`, `abortion`, `iufd`, `died`, `pathologic`, `infacilitydelivery` exist in `pxwalkinconsultation`.
   - Migration for `lmp_date` and `delivery_procedure` (per R2) must use defensive checks (`Schema::hasColumn('pxwalkinconsultation', ...)`) so that `fetchPatientMedhistory` does not fail on unmigrated test environments.

---

## 4. Conclusion & Proposed Code Blueprints

### Blueprint 1: `routes/api.php`
Remove duplicate schedule routes from Doctor group (lines 106–109) and add missing question routes to Secretary/Admin group:

```php
// In Secretary/Admin group (auth:secretary,admin):
Route::post('fetch_doctor_questions', [SecretaryController::class, 'fetchQuestions'])->name('secretary.fetch_questions');
Route::post('create_question', [SecretaryController::class, 'createQuestion'])->name('secretary.create_question');
Route::post('edit_question', [SecretaryController::class, 'editQuestion'])->name('secretary.edit_question');
Route::post('delete_question', [SecretaryController::class, 'deleteQuestion'])->name('secretary.delete_question');

Route::post('fetch_doctor_schedules_specific', [SecretaryController::class, 'fetchSchedulesSpecific'])->name('secretary.fetch_schedule_specific');
Route::post('fetch_doctor_schedules', [SecretaryController::class, 'fetchSchedules'])->name('secretary.fetch_schedule');
Route::post('create_doctor_schedules', [SecretaryController::class, 'createSchedule'])->name('secretary.create_schedule');
Route::post('delete_schedule', [SecretaryController::class, 'deleteSchedule']);
Route::post('fetch_schedule_refno', [SecretaryController::class, 'fetchScheduleByRef']);
Route::post('edit_schedule', [SecretaryController::class, 'editSchedule']);

// In Doctor group (auth:doctor,admin):
// Retain doctor/* prefixed routes (lines 101-104) and REMOVE lines 106-109:
// - Route::post('create_doctor_schedules', ...);  <- REMOVE
// - Route::post('fetch_schedule_refno', ...);      <- REMOVE
// - Route::post('edit_schedule', ...);             <- REMOVE
// - Route::post('delete_schedule', ...);           <- REMOVE
```

---

### Blueprint 2: `app/Http/Controllers/SecretaryController.php`
Fix `createQuestion`, add `editQuestion` and `deleteQuestion`:

```php
public function createQuestion(Request $request)
{
    $request->validate([
        'docrefno' => 'required|string',
        'question' => 'required|string',
    ]);

    $user = auth()->guard('admin')->user() ?? auth()->guard('secretary')->user();
    if (!$user) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    $doctor = DoctorsProfileModel::where('docrefno', $request->docrefno)->first();
    if (!$doctor) {
        return response()->json(['success' => false, 'message' => 'Doctor profile not found.'], 404);
    }

    $docname = $doctor->docname ?: trim(($doctor->docfname ?? '') . ' ' . ($doctor->docmname ?? '') . ' ' . ($doctor->doclname ?? ''));
    $secrefno = $user->adminrefno ?? $user->secrefno ?? 'ADMIN';
    $recordedby = $user->username ?? 'admin';

    $question = DoctorQuestionsModel::create([
        'docquestionrefno' => Date::now()->format('mdYHis') . "QT",
        'secrefno' => $secrefno,
        'question' => $request->question,
        'docrefno' => $request->docrefno,
        'docname' => $docname,
        'recordeddate' => Date::now(),
        'recordedby' => $recordedby,
    ]);

    Log::info('Doctor question created', [
        'docquestionrefno' => $question->docquestionrefno,
        'docrefno' => $request->docrefno,
        'recordedby' => $recordedby,
    ]);

    return response()->json(['success' => true, 'question' => $question]);
}

public function editQuestion(Request $request)
{
    $request->validate([
        'docquestionrefno' => 'required|string',
        'question' => 'required|string',
    ]);

    $updated = DoctorQuestionsModel::where('docquestionrefno', $request->docquestionrefno)
        ->update(['question' => $request->question]);

    if (!$updated) {
        return response()->json(['success' => false, 'message' => 'Question not found.'], 404);
    }

    Log::info('Doctor question updated', ['docquestionrefno' => $request->docquestionrefno]);

    return response()->json(['success' => true]);
}

public function deleteQuestion(Request $request)
{
    $request->validate([
        'docquestionrefno' => 'required|string',
    ]);

    $deleted = DoctorQuestionsModel::where('docquestionrefno', $request->docquestionrefno)->delete();

    if (!$deleted) {
        return response()->json(['success' => false, 'message' => 'Question not found.'], 404);
    }

    Log::info('Doctor question deleted', ['docquestionrefno' => $request->docquestionrefno]);

    return response()->json(['success' => true]);
}
```

---

### Blueprint 3: `resources/views/pages/doctor/dashboard.blade.php`
Fix column count warning by adding 4th `<td>` in line 36:

```html
<tbody>
    <tr>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
</tbody>
```

Include `#patient_consultations_history_modal` and `#preview_modal` in `@push('modals')`:
```blade
@push('modals')
    @include('modals.doctor_info')
    @include('modals.doctor_schedule')
    @include('modals.patient_consultations_history')
    @include('modals.view_consultation_details')
    @include('modals.preview_modal')
@endpush
```

---

### Blueprint 4: `resources/js/pages/doctor/dashboard.js`
Update row click on `#todays_patients_table`:

```javascript
// Open patient consultation history modal when clicking any row or Details button in Today's Patients
$(document).on("click", "#todays_patients_table tbody tr, .view-consultation-details-btn", function (e) {
    // If clicking an action button, prevent double execution
    const table = $("#todays_patients_table").DataTable();
    const rowEl = $(this).closest("tr");
    const rowData = table.row(rowEl).data();
    if (!rowData) return;

    openPatientConsultationHistoryModal(rowData);
});
```

---

### Blueprint 5: `resources/views/modals/consultation_modal.blade.php`
Close `#main_medhistory_pane` at line 359 before `#main_permanent_medhistory_pane`:

```html
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div> <!-- Close #main_medhistory_pane -->

            <!-- TAB 3: PERMANENT MEDICAL HISTORY -->
            <div class="tab-pane fade h-100" id="main_permanent_medhistory_pane" role="tabpanel" aria-labelledby="main_permanent_medhistory_tab_btn" tabindex="0">
```
And adjust trailing closing `</div>` tags before line 451 accordingly.

---

### Blueprint 6: `resources/js/pages/doctor/consultation.js` and `DoctorController.php`
In `DoctorController::fetchConsultationPatients`:
```php
$patients = ConsultationModel::select(
    'queueno',
    'consultationrefno',
    'status',
    'patientname',
    'pxmidname',
    'pxlastname',
    'pxsuffix',
    'reasonforconsultation'
)
    ->where(['docrefno' => auth()->guard('doctor')->user()->docrefno])
    ->whereDate('consultation_date', $request->date)
    ->orderBy('queueno', 'ASC')
    ->get();
```

In `consultation.js`:
```javascript
columns: [
    {
        data: null,
        render: function (data, type, row) {
            const queueBadge = row.queueno ? `<span class="badge bg-secondary me-2">#${row.queueno}</span>` : '';
            return `<span data-consultation="${row.consultationrefno}">${queueBadge}<strong>${data.patientname}</strong></span>`;
        }
    },
    { data: 'status', ... }
]
```

Add modal transition unblurring in `consultation.js` and `dashboard.js`:
```javascript
$(document).on("show.bs.modal hide.bs.modal", ".modal", function () {
    if (document.activeElement && typeof document.activeElement.blur === "function") {
        document.activeElement.blur();
    }
});
```

---

## 5. Verification Method

1. **Automated Test Verification**:
   ```bash
   php artisan test
   ```
   Verify 0 failures and 0 errors across all 15 feature test files.
2. **Specific Route & Authorization Test**:
   - Write feature tests in `tests/Feature/AdminDoctorManagementScheduleTest.php` to verify:
     - Admin can `POST /api/create_doctor_schedules` (returns 200).
     - Admin can `POST /api/create_question`, `edit_question`, and `delete_question` (returns 200).
     - Secretary can `POST /api/create_question` without `adminrefno` null error.
3. **DataTables Warning Verification**:
   - Open Doctor Dashboard (`/doctor/dashboard`) in browser with DevTools Console open.
   - Verify that NO `DataTables warning: table id=todays_patients_table - Column count mismatch` appears.
4. **Modal Navigation Verification**:
   - On Doctor Dashboard, click a patient row in Today's Patients.
   - Verify `#patient_consultations_history_modal` opens displaying previous consultations.
   - Click a past consultation row; verify `#view_consultation_details_modal` opens with:
     - Medical History tab
     - Patient Info & Vitals with Classification fields
     - Impressions & Diagnosis with answered doctor questions
     - Preview button that opens `#preview_modal` for rad/lab files.
5. **Aria-Hidden Console Warning Verification**:
   - On Doctor Consultation page (`/doctor/consultation`), click through modals (Consultation, Rx, Diagnostics, Preview, Close).
   - Verify zero `Blocked aria-hidden on an element because its descendant retained focus` warnings in DevTools console.
