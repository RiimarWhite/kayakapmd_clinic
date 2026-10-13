# Walkthrough: Doctor Consultation Modal Charges, PF Computation & PHIC YAKAP Badge Fix

## Overview
This walkthrough documents the investigation, root causes, changes, and verification performed to resolve the issues in the **Doctor's Consultation Modal** and **Consultation Details Modal**:
1. **Tier & Special Price Calculation**: Ensured PHIC-type patients receive the `price_phic` (PhilHealth tier price) rather than defaulting to `REGULAR` pricing when adding charges.
2. **Professional Fee (PF) Computation**: Automatically calculated Doctor's Professional Fee incorporating PhilHealth doctor rates (`phicrate` when `phicenable` is on) and automatic VAT calculation (`autoAddVAT` and `vatrate`).
3. **Charge Editing Failure**: Resolved a SQL error 1054 (`Column not found: pxchargerefno` on `doctorcharges`) when updating charge amounts via `/api/update_charge`, enabling smooth inline amount updates and automatic consultation modal state preservation.
4. **Badge Rendering**: Updated Doctor's Consultation Modal and Consultation Details Modal across all Doctor and Secretary pages to display `PHIC YAKAP` with a distinctive green badge (`bg-success text-white`) whenever the patient is PhilHealth.

---

## Root Causes Identified

1. **Patient Tier Classification Mismatch**:
   - In `pxwalkinconsultation`, the column `classification` stores clinical specialty (e.g. `'MEDICAL'`, `'OBSTETRICS'`), whereas `modeofpayment` stores payment classification (e.g. `'PHIC'`, `'HMO'`, `'REGULAR'`, `'OTHERS'`).
   - Prior code inspected `$consultation->classification`, causing PHIC patients to be evaluated as `'medical'`, which fell back to `REGULAR` pricing.
   
2. **Uncomputed Doctor Professional Fee**:
   - Queued consultation records inserted raw base PF (`pfrate`) into `pxcharges` without checking if the patient was PHIC (to apply `doctor->phicrate`) or whether `doctor->autoAddVAT` was enabled.
   
3. **Database Schema Mismatch in `updateCharge`**:
   - In `DoctorController@updateCharge`, the code executed:
     ```php
     DocChargesModel::where('pxchargerefno', $chargeId)->update([...]);
     ```
   - Table `doctorcharges` does **not** have a `pxchargerefno` column (it uses `servicerefno`), which caused `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'pxchargerefno'` and prevented editing charges.
   
4. **Badge Display Text**:
   - Frontends were explicitly displaying `'PHIC'` instead of the mandated `'PHIC YAKAP'`.

---

## Changes Implemented

### 1. Backend Controllers

#### `app/Http/Controllers/DoctorController.php`
- **`fetchPatientCharges`**:
  - Corrected payment mode evaluation to check `modeofpayment` and `phic_pin` (not just `classification`).
  - Evaluated `doctor->phicrate` if patient is PHIC and `doctor->phicenable` is set to `1`.
  - Added VAT computation if `doctor->autoAddVAT` is true (`baseFee + round(baseFee * vatrate / 100, 2)`).
  - Automatically synchronized default PF records that were uncomputed.
  - Correctly determined `posted_tier = 'PHIC'` and `posted_tier_price = price_phic` for patient charges.
- **`saveAppendedCharges`**:
  - Corrected pricing tier selection prioritizing `modeofpayment` and `phic_pin`.
- **`getHmoPrice`**:
  - Prioritized `request->patient_type`, `consultation->modeofpayment`, and `phic_pin` to return the appropriate tier price (`price_phic`, `price_hmo`, `price_others`, or `price_regular`).
- **`updateCharge`**:
  - Removed invalid `DocChargesModel::where('pxchargerefno', $chargeId)` call.
  - Loaded `PatientChargesModel` first and safely updated `doctorcharges` via `servicerefno` only if the table exists.

#### `app/Http/Controllers/ConsultationController.php`
- **`saveConsultation`**:
  - Updated queued consultation PF insertion to compute doctor PF with PHIC rate and VAT.

---

### 2. Frontend Scripts

#### `resources/js/pages/doctor/consultation.js` & `resources/js/doctor.js`
- Updated `#doctor_modal_patient_type_badge` to render `PHIC YAKAP` with `bg-success text-white`.

#### `resources/js/pages/doctor/consultation/form.js`
- Updated `#search_charge` Select2 `processResults` and select handler to send `patient_type` and `consultationrefno` to `/api/get_hmo_price`.
- Updated `.edit_charge_btn` handler to keep Doctor Consultation Modal open, stay on `#patientChargeTab`, and smoothly reload charges.
- Updated `#vcd_patient_type_badge` in consultation details view to show `Type: PHIC YAKAP`.

#### `resources/js/pages/doctor/dashboard.js`, `resources/js/pages/doctor/patients.js`, `resources/js/pages/secretary/queue.js`
- Updated `#vcd_patient_type_badge` rendering to consistently show `Type: PHIC YAKAP` with `bg-success text-white`.

---

## Verification & Testing

1. **Automated Feature Tests**:
   - Created test suite [tests/Feature/DoctorConsultationChargesAndBadgeFixTest.php](file:///C:/xampp/htdocs/kayakapmd_clinic/tests/Feature/DoctorConsultationChargesAndBadgeFixTest.php).
   - Verified:
     - PHIC tier pricing returns `price_phic`.
     - Computed PF incorporates doctor `phicrate` and `vatrate`.
     - `/api/get_hmo_price` returns PHIC price when `patient_type` is `PHIC` / `PHIC YAKAP`.
     - `/api/update_charge` successfully updates without SQL 1054 error.
   - Ran `php artisan test`: **171 tests passed (1,297 assertions)**.

2. **Frontend Asset Build**:
   - Executed `npm run build`: All Vite bundles built cleanly without syntax errors.
