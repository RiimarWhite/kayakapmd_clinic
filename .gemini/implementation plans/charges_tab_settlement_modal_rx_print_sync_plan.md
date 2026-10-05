# Implementation Plan: Consultation Charges, Settlement Modal, Billing Rates Sync & Rx Printing

**Document Name:** `charges_tab_settlement_modal_rx_print_sync_plan.md`  
**Location:** `.gemini/implementation plans/charges_tab_settlement_modal_rx_print_sync_plan.md`  
**Date:** 2026-10-05  
**Status:** In Plan Mode / Awaiting Clarification & Execution  

---

## 1. Overview & Objectives

This implementation plan addresses the 5 core feature requests and bug fixes specified by the user:

1. **Patient Type-Based Item Pricing across Consultation Charges & Payment Details**:
   - Include current prices of items in the doctor consultation charges tab and secretary payment details tab (including the admin version) dynamically resolved by patient classification (`REGULAR` -> `price_regular`, `PHIC` -> `price_phic`, `HMO` -> `price_hmo`, `OTHERS` -> `price_others`).
   - Add the "Unit Price" column to Secretary `#pxcharges_table` to align with the doctor's consultation table.
   - Auto-display and pre-fill the tiered price in the Select2 search dropdowns in both doctor and secretary append charges modals.

2. **Bidirectional Synchronization of Doctor Dashboard Fee & Profile Billing Rates**:
   - Synchronize the 9 Rates, Tax & Billing fields between the Doctor Dashboard Default Consultation Fee widget (`dashboard.blade.php`) and "My Doctor Profile" Modal Tab 4 (`doctor_info.blade.php`): `pfrate`, `rodrate`, `tax`, `vatrate`, `coacode`, `accountno`, `vatable`, `autoAddVAT`, `issuehospOR`.
   - Correct input ID mismatches in `dashboard.js` (`#prof_consultationfee` -> `#prof_pfrate`, etc.).
   - Add two-way live field updating upon saving either form without needing page reload.

3. **Professional Fee Auto-Population & Simplified Edit Charge Modal**:
   - For auto-generated Professional Fee in `stocks_ledger`: account for doctor's billing rates configuration (e.g. `autoAddVAT` applying `vatrate` to the consultation fee when enabled).
   - In the "Edit Charge" modal: remove the discount field and display only the Unit Price field for updating fee amounts.

4. **Prescription (Rx) Print Formatting**:
   - In `resources/views/printables/rx_print.blade.php`:
     - Format medicine items in the order: `{name/description}\nSig: {instructions}`.
     - Move general Rx instructions (`$patient->instructions`) to the bottommost section of the prescription body above the doctor's signature.

5. **Settlement Modal Redesign, Net Billing Recomputation & DB Dual-Update**:
   - In `resources/views/modals/settlement_modal.blade.php`:
     - Move Cash and CTA (Card) payment fields to the bottom.
     - Add Senior/PWD discount checkbox with sub-fields: Ref Number (`srpwd_refno`) and Amount (`less_srpwd`).
     - Add PHIC with ICD/RVS code (`phic_icd_rvs`) and Amount (`less_phic`).
     - Add HMO with HMO dropdown (`hmo_type`) and Amount (`less_hmo`).
     - Add Other discount with Description (`discount_description`) and Amount (`less_discount`).
     - Dynamically compute Net Billing: `Net Billing = Gross - (Senior/PWD + PHIC + HMO + Other Discount)`.
     - Compute remaining balance / change against Net Billing with Cash + CTA.
   - In `pxsettlements` database table:
     - Create an idempotent Laravel migration adding `srpwd_refno`, `phic_icd_rvs`, and `discount_description`.
     - Update `.gemini/database/kayakapmdv2_data_dictionary.md` per Rule 4.
     - Update `SettlementsModel.php` fillables and mutators.
     - Update `SecretaryController::saveSettlements` and `fetchSettlements`.

---

## 2. Technical Findings & Root Cause Analysis

### Area 1: Patient Type-Based Item Pricing
- **Root Cause**:
  - In `DoctorController::fetchPatientCharges` (shared by `/api/fetch_patient_charges` and `/api/fetch_pxcharges`), if unit price or total was missing or required lookup, it unconditionally queried `$listing->price_regular` regardless of whether the patient was PHIC, HMO, or OTHERS.
  - In `queue.blade.php`, `#pxcharges_table` has only 4 columns (`Actions`, `Description`, `Quantity`, `Amount`), omitting `Unit Price`.
  - In `queue.js`, `initSecSearchCharge` did not show tiered pricing in the Select2 option text.
- **Remedy**:
  - In `DoctorController::fetchPatientCharges`, resolve patient type from `$consultation->classification` / `phic_pin` / `hmocode`. Map price to `price_phic`, `price_hmo`, `price_others`, or `price_regular`.
  - Update `queue.blade.php` to include `Unit Price` header in `#pxcharges_table`.
  - Update `queue.js` and `form.js` to render the unit price column and display tiered prices in Select2 results.

### Area 2: Doctor Dashboard & Profile Modal Rates Sync
- **Root Cause**:
  - In `dashboard.js` lines 306-317, when `#doctor_profile_modal` opened, jQuery set `#prof_consultationfee`, `#prof_emergencyfee`, etc. None of these elements exist in `doctor_info.blade.php`! Tab 4 actually uses `#prof_pfrate`, `#prof_rodrate`, `#prof_tax`, `#prof_vatrate`, `#prof_coacode`, `#prof_accountno`, `#prof_vatable`, `#prof_autoAddVAT`, `#prof_issuehospOR`.
  - When `#save_doctor_fee_btn` or `#save_doctor_profile_btn` saved, neither synchronized its inputs to the other form.
- **Remedy**:
  - Fix element selectors in `dashboard.js` to populate Tab 4 with `u.pfrate`, `u.rodrate`, `u.tax`, `u.vatrate`, `u.coacode`, `u.accountno`, `u.vatable`, `u.autoAddVAT`, `u.issuehospOR`.
  - Ensure bidirectional sync upon AJAX success on both buttons.
  - Add `'vatrate'` to `$fillable` in `DoctorsProfileModel.php`.

### Area 3: Professional Fee Auto-Calculation & Edit Charge Modal
- **Root Cause**:
  - In `DoctorController::fetchPatientCharges` (line 1283), `$fee` only considered `docProfile->pfrate` or `phicrate`, ignoring `autoAddVAT` and `vatrate`.
  - In `form.js` (lines 1603-1625), `.edit_charge_btn` opened a SweetAlert modal containing `Quantity`, `Unit Price (₱)`, and `Discount (₱)`.
- **Remedy**:
  - In `fetchPatientCharges`, if `autoAddVAT` is enabled on `docProfile` with `vatrate > 0`, calculate VAT and add to the default fee.
  - In `form.js`, simplify `.edit_charge_btn` modal to show ONLY the Unit Price input field and remove the discount field.

### Area 4: Rx Print Formatting
- **Root Cause**:
  - In `resources/views/printables/rx_print.blade.php` (lines 68-78), medicines were printed with Dosage, Duration, Quantity inline, and Sig separately. General instructions (`$patient->instructions`) were only printed when `$type === 'instructions'`, not on Rx prescriptions.
- **Remedy**:
  - In `rx_print.blade.php`, format each item as:
    ```html
    <div style="margin-bottom: 12px;">
        <div style="font-weight: bold; font-size: 14px;">{{ $medName }}</div>
        <div style="font-size: 13px; color: #333; margin-top: 2px;">Sig: {{ $instruction ?: 'As directed by physician' }}</div>
    </div>
    ```
  - Below the list of medicines, add a dedicated bottommost section for General Rx Instructions (`$patient->instructions`).

### Area 5: Settlement Modal & Database Dual-Update
- **Root Cause**:
  - `settlement_modal.blade.php` placed CASH, CTA, PHIC, HMO together at the top without Senior/PWD discount, PHIC ICD/RVS, or Other discount description.
  - `pxsettlements` table lacked `srpwd_refno`, `phic_icd_rvs`, and `discount_description`.
- **Remedy**:
  - Create idempotent migration adding `srpwd_refno`, `phic_icd_rvs`, `discount_description`.
  - Update `.gemini/database/kayakapmdv2_data_dictionary.md`.
  - Update `SettlementsModel.php`.
  - Restructure `settlement_modal.blade.php`:
    - Top: Gross Total.
    - Deductions: Senior/PWD (Checkbox + Ref # + Amount), PHIC (ICD/RVS + Amount), HMO (Dropdown + Amount), Other (Description + Amount).
    - Middle: Net Billing.
    - Bottom: Cash and CTA (Card), Remaining Balance / Change.
  - Update `queue.js`, `secretary.js`, and `SecretaryController::saveSettlements`.

---

## 3. Step-by-Step Execution Plan

```
[ ] Step 1: Database Migration & Data Dictionary Update
    - Create migration: database/migrations/2026_10_05_000001_add_discount_and_claim_fields_to_pxsettlements.php
    - Apply migration: php artisan migrate
    - Update .gemini/database/kayakapmdv2_data_dictionary.md (pxsettlements table)
    - Update app/Models/SettlementsModel.php ($fillable, appends, mutators)

[ ] Step 2: Synchronize Doctor Dashboard Billing Rates & Profile Modal
    - app/Models/DoctorsProfileModel.php (add vatrate to $fillable)
    - app/Http/Controllers/DoctorController.php (ensure vatrate and autoAddVAT handled in updateDoctorProfile & updateDoctorFee)
    - resources/js/pages/doctor/dashboard.js (fix Tab 4 element selectors and bidirectional syncing)

[ ] Step 3: Patient Type Pricing on Consultation Charges & Payment Details
    - app/Http/Controllers/DoctorController.php (fetchPatientCharges: tiered price resolution + autoAddVAT for PF)
    - resources/views/pages/secretary/queue.blade.php (add Unit Price column to #pxcharges_table)
    - resources/js/pages/secretary/queue.js (render Unit Price column in #pxcharges_table, tiered Select2 search)
    - resources/js/pages/doctor/consultation/form.js (tiered Select2 search)

[ ] Step 4: Simplify Edit Charge Modal & Remove Discount Field
    - resources/js/pages/doctor/consultation/form.js (edit_charge_btn: show only Unit Price input, remove discount field)
    - app/Http/Controllers/DoctorController.php (updateCharge: handle unit price update without discount)

[ ] Step 5: Update Prescription (Rx) PDF Print Template
    - resources/views/printables/rx_print.blade.php (order: {name/description}\nSig: {instructions}, general instructions at bottom)

[ ] Step 6: Overhaul Settlement Modal on Secretary & Admin Payment Details
    - resources/views/modals/settlement_modal.blade.php (reorganize layout: Senior/PWD, PHIC ICD/RVS, HMO, Other, Net Billing, Cash & CTA at bottom)
    - resources/js/pages/secretary/queue.js (recompute Net Billing, bind new inputs, handle Senior/PWD toggle)
    - resources/js/secretary.js (keep in sync)
    - app/Http/Controllers/SecretaryController.php (saveSettlements: save all 5 deduction channels + compute net_payable)

[ ] Step 7: Testing, Asset Compilation & Verification
    - Compile frontend: npm run build
    - Run automated test suite: php artisan test
    - Verify logs: storage/logs/laravel.log

[ ] Step 8: Walkthrough Documentation & Git Commit
    - Persist walkthrough: .gemini/walkthroughs/charges_tab_settlement_modal_rx_print_sync_walkthrough.md
    - Generate conventional Git commit message
```

---

## 4. Verification & Testing Strategy

1. **Patient Type Pricing**:
   - Test consultation for Regular, PHIC, HMO, and Others patient types.
   - Verify unit price in doctor `#charges_table` and secretary `#pxcharges_table` reflects the specific patient tier price.
2. **Dashboard & Profile Modal Sync**:
   - Change PF rate and VAT settings on Dashboard. Save and open Doctor Profile modal. Verify Tab 4 matches exactly.
   - Edit billing rates in Doctor Profile modal. Save and verify Dashboard widget immediately updates.
3. **Edit Charge Modal**:
   - Click Edit on a charge in Doctor Consultation. Confirm only Unit Price field is displayed; confirm discount field is removed. Save and verify updated total.
4. **Rx Printing**:
   - Generate Rx with 2+ medicines and general instructions.
   - Print Rx PDF. Verify each medicine shows name/description followed by newline `Sig: {instructions}`, and general instructions appear at the bottom.
5. **Settlement Modal**:
   - Open Settlements modal on Secretary queue.
   - Check Senior/PWD checkbox, enter Ref No and amount.
   - Enter PHIC ICD/RVS and amount.
   - Select HMO and enter HMO amount.
   - Enter Other discount description and amount.
   - Verify Net Billing correctly recalculates: `Gross - (Senior/PWD + PHIC + HMO + Other)`.
   - Enter Cash and CTA at the bottom. Verify Remaining Balance / Change recalculates.
   - Save settlement and verify persistence in `pxsettlements`.
