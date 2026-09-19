# Implementation Plan: Fix Doctor Consultation Modal & Charges, Secretary Queue Settlements, and Unscheduled Queue Filtering

## Context & Problem Statement
During consultation workflows and queue management across Doctor and Secretary consoles:
1. **Doctor Consultation Modal Closes When Appending Charges**:
   - In `resources/views/modals/consultation_modal.blade.php`, clicking `#append_charge_btn` or `#append_charge_btn_2` has `data-bs-toggle="modal" data-bs-target="#append_charge_modal"`, causing Bootstrap 5 to hide `#consultation_modal`.
   - In `resources/js/pages/doctor/consultation/form.js`, clicking `#save_charges_btn` calls `bootstrap.Modal.getInstance("#append_charge_modal").hide()` without restoring `#consultation_modal`, leaving the doctor with no modal visible and interrupting their consultation workflow.
2. **Charges Failing to Add & Prescriptions (Rx) Missing from Patient Charges**:
   - In `form.js`, existing charges from `stocks_ledger` were being pushed into the `appendedCharges` array on modal load, causing duplicates and blocking new charges from being saved.
   - In `DoctorController::fetchPatientCharges`, line 630 explicitly excluded `item_grouping = 'DRUGS AND MEDS'`, preventing prescribed medications from appearing in the patient charges list and preventing accurate billing totals.
   - In `DoctorController::addMedicine`, line totals and unit costs (`cost_ave`, `retails`, `totalamt`) were not calculated or stored in `StocksLedgerModel`.
3. **Secretary Queue Settlements Modal Shows Empty Generate & View Tabs**:
   - On `admin/secretary` and `secretary/queue`, opening `#settlementModal` leaves the "Generate Settlements" tab (Total, Remaining, HMO dropdown, Import buttons) and "View Settlements" tab (`#info_total`, `#info_cash`, `#info_cta`, `#info_hmo`, etc.) completely empty.
   - In `resources/js/pages/secretary/queue.js`, the `#settlement_btn` handler is a non-functioning stub referencing nonexistent HTML IDs. Event handlers for `.import-total`, `.settlement-input` (auto-dispersion), `#save_settlements`, and `#view_sett_btn` were never migrated from legacy `secretary.js`.
   - In `resources/views/modals/settlement_modal.blade.php`, `#info_cta_type` and `#info_hmo_type` were defined as `<input type="number">`, causing browsers to reject text values ("cc", "dc", HMO names) and display blank inputs.
   - In `SecretaryController::fetchHmo`, querying by `session()->get('clientcode')` returns an empty array if the session variable is unset.
4. **Scheduled Patients Appearing in "New/Unscheduled Patients" Table**:
   - In `ConsultationController::fetchConsultationPatientsUnsched`, the query only filters by `status = 'UNSCHEDULED'`.
   - Consultation records that have a valid `consultation_date` (e.g. `2026-01-19 09:00:00`) or patients (`pxrefno`) who already have scheduled consultations in `pxwalkinconsultation` still appear in the bottom "New/Unscheduled Patients" table.

---

## Proposed Architectural Solution & Modifications

### 1. Doctor Consultation: Modal Persistence, Append Charges, and Rx Inclusion
- **File**: `resources/views/modals/consultation_modal.blade.php`
  - Remove `data-bs-toggle="modal"` from `#append_charge_btn` and `#append_charge_btn_2` so clicking them does not dismiss `#consultation_modal`.
  - Set `#append_charge_modal` with high z-index (`z-index: 1060;`) as a stacked modal over `#consultation_modal` (`z-index: 1055;`).
- **File**: `resources/js/pages/doctor/consultation/form.js`
  - Show `#append_charge_modal` programmatically:
    ```javascript
    const appendModal = bootstrap.Modal.getOrCreateInstance(document.getElementById("append_charge_modal"));
    appendModal.show();
    ```
  - On `#append_charge_modal` `hidden.bs.modal`, ensure `$('body').addClass('modal-open')` so scrolling inside `#consultation_modal` remains interactive.
  - On `#save_charges_btn`:
    - Save newly appended charges via `/api/save_patient_charges`.
    - On success: hide `#append_charge_modal`, reload `#charges_table.DataTable().ajax.reload()`, and guarantee `#consultation_modal` stays open on `#patientChargeTab-tab`.
  - Reset `appendedCharges = []` when opening `#append_charge_modal` so it tracks only newly added charges in the current session.
  - Display existing patient charges in `#appended_charges_table` with saved badges so the doctor can view what is already billed, while only newly added items are sent to `/api/save_patient_charges`.
- **File**: `app/Http/Controllers/DoctorController.php`
  - In `fetchPatientCharges`: remove `$q->where('item_grouping', '!=', 'DRUGS AND MEDS')` so all charges—including prescription medicines—are returned.
  - In `addMedicine`: calculate and store `cost_ave`, `retails`, and `totalamt` on `StocksLedgerModel` when a medicine is prescribed.
  - In `saveAppendedCharges`: populate `px_pin`, `cost_ave`, `retails`, `totalamt`, and `transactiontype = 'CHARGES'`, handling stock deduction safely.

### 2. Secretary Queue: Settlements Lifecycle & Modal Data Binding
- **File**: `resources/views/modals/settlement_modal.blade.php`
  - Change `#info_cta_type`, `#info_hmo_type`, `#info_total`, `#info_cash`, `#info_cta`, `#info_hmo` from `type="number"` to `type="text"` with read-only formatting.
  - Add missing option placeholders and ensure field IDs match controller payloads.
- **File**: `app/Http/Controllers/SecretaryController.php`
  - In `fetchHmo`: defensively fallback if `session()->get('clientcode')` is null (checking config/env or returning active HMOs with valid names).
  - In `saveSettlements`: support `$request->cta_type ?? $request->card_type` so card type persists seamlessly.
- **File**: `resources/js/pages/secretary/queue.js`
  - On `#settlement_btn` click:
    - Retrieve active consultation reference number from `$("#pxconsultationrefno").text() || $("#pxconsultationrefno").val()`.
    - Check for missing consultation with SweetAlert2 reminder.
    - Set `#sett_consultationrefno` and reset `#settlement_form`.
    - Fetch HMO options via `/api/fetch_hmo` and populate `#hmo_type`.
    - Fetch charges via `/api/fetch_pxcharges` to calculate `totalAmount`, display `#total_amount`, and set `#remaining`.
    - Fetch existing settlements via `/api/fetch_settlements` to populate Generate tab inputs and View tab fields (`#info_total`, `#info_cash`, `#info_cta`, `#info_cta_type`, `#info_hmo`, `#info_hmo_type`).
  - Add `.import-total` click handler to transfer remaining balance into the active payment channel.
  - Add `.settlement-input` input handler to compute remaining balance and prevent over-dispersion.
  - Add `#save_settlements` click handler to validate full dispersion (`calculateRemaining() <= 0`), prompt confirmation with SweetAlert2, and submit form data to `/api/save_settlements`.
  - Add `#view_sett_btn` click handler to refresh View Settlements tab whenever selected.
  - Add DataTable layout bottomStart total indicator in `#pxcharges_table` so charges total is always visible on the queue page.

### 3. Secretary Queue: Filter Scheduled Patients from Unscheduled Table
- **File**: `app/Http/Controllers/ConsultationController.php`
  - In `fetchConsultationPatientsUnsched`:
    - Identify all patients (`pxrefno`) with an existing scheduled consultation in `pxwalkinconsultation`:
      ```php
      $scheduledPxrefnos = ConsultationModel::whereNotNull('consultation_date')
          ->whereNotIn('consultation_date', ['1901-01-01 00:00:00', '0000-00-00 00:00:00', ''])
          ->pluck('pxrefno')
          ->filter()
          ->unique();
      ```
    - Refine `$baseQuery`:
      ```php
      $baseQuery = ConsultationModel::where('status', 'UNSCHEDULED')
          ->where(function ($q) {
              $q->whereNull('consultation_date')
                ->orWhereIn('consultation_date', ['1901-01-01 00:00:00', '0000-00-00 00:00:00', '']);
          })
          ->whereNotIn('pxrefno', $scheduledPxrefnos);
      ```
    - Group or distinct by `pxrefno` so each unscheduled patient appears at most once.

---

## Verification & Testing Plan
1. **Vite Compilation**:
   - Run `npm run build` to verify clean compilation of `resources/js/pages/doctor/consultation/form.js` and `resources/js/pages/secretary/queue.js`.
2. **Doctor Consultation Test**:
   - Open doctor consultation modal (`doctor/consultation`).
   - Navigate to "Rx & Instructions", prescribe a medicine (`DRUGS AND MEDS`).
   - Open "Patient Charges" tab: verify prescribed medicine appears with quantity, unit price, and total amount.
   - Click "Append Charges": verify `#append_charge_modal` appears without closing `#consultation_modal`.
   - Append a new supply/procedure charge and click "Save": verify `#append_charge_modal` closes, `#charges_table` reloads with new charge, and `#consultation_modal` remains open and fully interactive.
3. **Secretary Queue Settlements Test**:
   - In `admin/secretary` or `secretary/queue`, select a queued consultation with patient charges.
   - Click "Settlements" (`#settlementModal`):
     - Verify `#total_amount` and `#remaining` display correct total amount.
     - Verify HMO dropdown `#hmo_type` is populated.
     - Click `.import-total` next to Cash or CTA: verify value fills and remaining updates to 0.00.
     - Click "Save": verify confirmation prompt and successful save.
     - Click "View Settlements" tab: verify `#info_total`, `#info_cash`, `#info_cta`, `#info_cta_type`, `#info_hmo`, `#info_hmo_type` display saved settlement details without blank fields.
4. **Unscheduled Patients Queue Test**:
   - In secretary queue, inspect "New/Unscheduled Patients" table.
   - Verify that patients with schedules (`SUA, TOMBOC URZA`, `MAGONCIA, ELVIE ACAS`, or any patient with `consultation_date`) no longer appear in the unscheduled list.
   - Verify that only patients genuinely without schedules (`Carney`, `Mullen`, `Armstrong`, `Estes`) are displayed.
5. **Automated Testing**:
   - Run `docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan test` to verify no regressions.
