# Implementation Plan: Fix Doctor Consultation Modal Charges, Fees, and PHIC YAKAP Badge

## Problem Summary
1. **Patient Charges Pricing (Tier & Special Price)**: In the Doctor Consultation Modal under Patient Charges, when selecting/adding catalog items or viewing them for a PHIC patient, the system was applying the REGULAR price instead of the PHIC tier price. Also special prices (e.g. Others tier) were not properly calculated because the backend methods checked `classification` (which holds medical classification such as `MEDICAL` or `OBSTETRICS`) rather than `modeofpayment` or `phic_pin`, and the frontend select2 only compared `pxType === 'PHIC'` rather than supporting `PHIC YAKAP`.
2. **Professional Fee (PF) Calculation**: The Professional Fee showed an uncomputed price (e.g. basic fee without VAT or without PHIC rate), because when queued in `ConsultationController`, it was initialized with raw uncomputed `doctor->pfrate` with remarks `'Default Doctor Consultation Fee'`, and `DoctorController::fetchPatientCharges` bypassed computation if any PF record already existed. Furthermore, `pf_rate` did not consider the doctor's PHIC rate for PHIC patients.
3. **Fee Editing Failure**: Clicking "Edit Fee" failed to update the fee amount with a 500 error because `DoctorController::updateCharge` attempted to query `DocChargesModel` using `where('pxchargerefno', $chargeId)`, but `doctorcharges` table has no `pxchargerefno` column (`SQLSTATE[42S22]: Column not found: 1054 Unknown column 'pxchargerefno'`). Additionally, handling for unchanged amounts caused 404 responses due to 0 affected rows.
4. **Badge Labeling**: The Doctor's Consultation Modal (`#doctor_modal_patient_type_badge`) and Consultation Details Modal (`#vcd_patient_type_badge`) displayed `PHIC` instead of `PHIC YAKAP`.

## Proposed Changes

### 1. `app/Http/Controllers/DoctorController.php`
- **`fetchPatientCharges`**:
  - Determine patient type accurately: check `$consultation->modeofpayment`, `$consultation->phic_pin`, and `$request->patient_type` in addition to `$consultation->classification`.
  - Check if doctor has PHIC enabled and has `phicrate > 0` for PHIC patients, and compute base fee + VAT when `autoAddVAT` is true.
  - If existing PF has remarks `'Default Doctor Consultation Fee'`, synchronize its `cost_ave` and `totalamt` to the properly computed rate with VAT and PHIC rate.
  - For catalog items, accurately evaluate `$isPhic`, `$isHmo`, `$isOthers` so that `$postedTier`, `$postedTierPrice`, and `$tierPrice` use `price_phic`, `price_hmo`, or `price_others` respectively.
- **`getHmoPrice`**:
  - Read `modeofpayment` from `$request->patient_type` or `$consultation->modeofpayment` / `$consultation->phic_pin`.
  - Accurately return `price_phic` for PHIC patients, `price_hmo` for HMO patients, `price_others` for Others patients, or `price_regular`.
- **`saveAppendedCharges`**:
  - Use `modeofpayment` / `phic_pin` for patient tier pricing when `inputAmount` is not provided.
- **`updateCharge`**:
  - Remove invalid query `where('pxchargerefno', $chargeId)` on `DocChargesModel` (which caused `Column not found` SQL crash).
  - Use `$ledgerItem = $query->first()` and `$ledgerItem->update([...])` so editing any charge always succeeds regardless of MySQL affected rows reporting.
  - Update `remarks` to `'Manually adjusted fee'` to prevent auto-sync overwrite.

### 2. `app/Http/Controllers/ConsultationController.php`
- In `saveConsultation` (when queueing consultation), calculate initial doctor consultation fee factoring in PHIC rate (if patient is PHIC and doctor has PHIC enabled) and VAT (if doctor has `autoAddVAT` enabled).

### 3. `resources/js/pages/doctor/consultation/form.js`
- In `#search_charge` Select2 `processResults`:
  - Support `pxType.includes('PHIC')` or `pxType === 'PHIC YAKAP'` to apply `price_phic`.
  - Pass `consultationrefno` in select2 AJAX parameters.
- In `#search_charge` select2:select:
  - Pass `patient_type` in `/api/get_hmo_price` request payload.
- In `.edit_charge_btn`:
  - Ensure `#consultationrefno` is sent, handle update charge cleanly, and re-activate `#patientChargeTab-tab` upon reload.
- In `openConsultationDetailsModal` (`#vcd_patient_type_badge`):
  - Display `PHIC YAKAP` with `bg-success text-white` for PHIC patients.

### 4. `resources/js/pages/doctor/consultation.js` and `resources/js/doctor.js`
- Set `#doctor_modal_patient_type_badge` to `PHIC YAKAP` with `bg-success text-white` for PHIC patients.

### 5. `resources/js/pages/doctor/dashboard.js`, `resources/js/pages/doctor/patients.js`, and `resources/js/pages/secretary/queue.js`
- Update `openConsultationDetailsModal` so `#vcd_patient_type_badge` shows `Type: PHIC YAKAP` with `bg-success text-white`.

## Verification Plan
1. Run automated tests with `php artisan test`.
2. Test `/api/fetch_patient_charges`, `/api/get_hmo_price`, and `/api/update_charge` in PHP tinker to verify:
   - For PHIC patient `CON10132026084148`:
     - Catalog items reflect `posted_tier = 'PHIC'` and `posted_tier_price = price_phic`.
     - `/api/get_hmo_price` returns `price_phic`.
     - PF calculates properly with PHIC rate and VAT.
     - `/api/update_charge` updates `cost_ave` and `totalamt` without SQL errors.
3. Build Vite frontend assets with `npm run build` (or verify JS syntax).
