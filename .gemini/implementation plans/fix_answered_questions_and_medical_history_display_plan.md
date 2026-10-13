# Implementation Plan: Fix Answered Doctor Questions and Medical History Display

## Context
1. In the Impressions & Diagnosis tab of both the active Doctor Consultation modal (`#consultation_modal`) and the historical Consultation Details modal (`#view_consultation_details_modal`), answered doctor questions and patient answers were not rendering with their actual question text or were failing to display.
2. In the Consultation Details modal (`#view_consultation_details_modal`), patient permanent medical history under Tab 6 (`#vcd_pane_medhistory`) displayed "None recorded" across all fields, and on the Doctor Patients page (`patients.js`), Tab 6 was never populated.
3. Root Causes:
   - `walkinconsultation_answer` only stores `questionrefno`, `answer`, `pxconsultationrefno`. The actual question text resides in `docquestion.question`. Backend queries did not join `docquestion`.
   - `SecretaryController::fetchPatientMedhistory` did not pre-hydrate `$record->answers` or `$record->permanent_medhistory`.
   - `/api/fetch_patient_medical_history` was registered only as a `POST` route in `routes/api.php`, but JavaScript calls were making `GET` requests without CSRF headers, resulting in HTTP 405 Method Not Allowed errors.
   - `DoctorController::fetchPatientData` did not return `answers` or `medical_history`.
   - Doctor Consultation modal opening (`proceedConsultationModal`) did not trigger `loadDoctorPermanentMedicalHistory` upon initialization.

## User Review Required
> [!NOTE]
> No database schema migrations are needed. All existing tables (`walkinconsultation_answer`, `docquestion`, `pxmedicalhistory`) already contain the appropriate data and foreign references.

## Proposed Changes

### Backend Updates
1. **`app/Http/Controllers/ConsultationController.php`**:
   - In `fetchConsultation`: Update `ConsultationAnswerModel` query to perform `leftJoin('docquestion', 'walkinconsultation_answer.questionrefno', '=', 'docquestion.docquestionrefno')` and select `walkinconsultation_answer.*`, `docquestion.question`.
   - In `fetchConsultation`: Hydrate and return `medical_history` and `permanent_medhistory` from `PxMedicalHistoryModel`.
   - In `deleteConsultation`: Correct column from `consultationrefno` to `pxconsultationrefno` when deleting from `walkinconsultation_answer`.

2. **`app/Http/Controllers/DoctorController.php`**:
   - In `fetchPatientData`: Add `answers` (joining `docquestion`) and `medical_history` (from `PxMedicalHistoryModel`) to the returned JSON payload.

3. **`app/Http/Controllers/SecretaryController.php`**:
   - In `fetchPatientMedhistory`: Pre-load all answers with `docquestion.question` joined in bulk, and pre-load `PxMedicalHistoryModel` records. Attach `$record->answers` and `$record->permanent_medhistory` to each consultation history record. Also return `permanent_medhistory` at the root response level.
   - In `fetchPatientMedicalHistory` and `savePatientMedicalHistory`: Defensively resolve `pxrefno` and `pincode` via `consultationrefno` and `patientname` + `birthday` when inputs are missing or literal strings 'undefined'/'null'.

4. **`routes/api.php`**:
   - Change `Route::post('fetch_patient_medical_history', ...)` to `Route::match(['get', 'post'], 'fetch_patient_medical_history', ...)` to prevent 405 Method Not Allowed exceptions regardless of request method.

### Blade View Updates
5. **`resources/views/modals/view_consultation_details.blade.php`**:
   - Ensure `#vcd_doctor_questions_box` and `#vcd_answered_questions_list` are statically rendered directly in the Impressions & Diagnosis tab so they don't depend on DOM injection.

### Frontend JavaScript Updates
6. **`resources/js/pages/doctor/consultation.js`**:
   - In `proceedConsultationModal`: Call `loadDoctorPermanentMedicalHistory(p.pxrefno, p.pincode, p.consultationrefno)` on modal show.
   - In `proceedConsultationModal`: Render answers immediately from `response.answers` or fall back to `/api/fetch_consultation`.

7. **`resources/js/pages/doctor/consultation/form.js`**:
   - In `populateAndShowConsultationDetails`: Use `POST` with CSRF headers for the fallback medical history AJAX request.
   - In `#impDiagBtn` click / `shown.bs.tab`: Render answered questions from `res.answers` or `/api/fetch_consultation`.

8. **`resources/js/pages/doctor/dashboard.js`**:
   - In `populateAndShowConsultationDetails`: Use `POST` with CSRF headers for fallback medical history fetch.

9. **`resources/js/pages/secretary/queue.js`**:
   - In `populateAndShowConsultationDetails`: Use `POST` with CSRF headers for fallback medical history fetch.

10. **`resources/js/pages/doctor/patients.js`**:
    - In `openConsultationDetailsModal`: Populate Tab 6 (`#vcd_pane_medhistory`) fields from `record.permanent_medhistory` or via AJAX fallback.

## Verification Plan
1. Run `php artisan test` to verify all automated feature/unit tests pass.
2. Run `npm.cmd run build` to verify frontend assets build without errors.
3. Test with tinker to verify queries return joined question text and permanent medical history.
