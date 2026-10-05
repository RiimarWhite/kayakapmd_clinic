# Implementation Plan: Searchable HMO Dropdown, Rx Printable Redesign, Doctor Billing Rates SQL Fix & Enhanced Charge Details

## Goal Description
This plan addresses 5 interconnected clinical, billing, and document presentation enhancements requested across the Doctor, Secretary, and Admin modules:
1. **Searchable HMO Dropdown**: Convert the HMO selection dropdown into an interactive, searchable Select2 dropdown on the consultation settlement modal (`#settlement_modal`) and admin settlement modals (`#add_settlement_modal`, `#edit_settlement_modal`) with proper modal focus containment (`dropdownParent`).
2. **Rx Printable Prescription Pad Redesign**: Restructure [`resources/views/printables/rx_print.blade.php`](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/views/printables/rx_print.blade.php) according to the visual reference image (`temp/bd4af504-fdfd-4636-9ae8-7c84d1f0ec64.jfif`), featuring a framed blue border, top header with doctor credentials, qualification, and clinic title, structured 4-line light blue patient info box including `Diagnosis:`, stylish Rx symbol, medications ordered by `{name/description} newline Sig: {instructions}`, general instructions at the bottommost part, doctor's signature line, bottom 2x2 contact footer, and subtle watermark curves.
3. **Fix Doctor Dashboard Billing Rates SQL Error**: Fix the `SQLSTATE[42S22]: Column not found` error when saving "Default Consultation Fee & Billing Rates" from the doctor dashboard widget by removing nonexistent columns (`withholdingtax`, `autoAddVAT`, `issuehospOR`) from the `DoctorModel` (`doctorsrights` table) update and ensuring all full rates and tax settings update cleanly on `DoctorsProfileModel` (`doctors` table).
4. **Professional Fee Charge Details**: Display detailed billing parameters on Professional Fee charge line items across the Doctor consultation charges tab, Secretary queue, Secretary console, and Admin console:
   - Base PF Rate / Consultation Fee (PHP)
   - Withholding Tax %
   - ROD Rate (PHP)
   - Vatable Status (Checkbox / badge)
   - Auto Add VAT status
5. **Other Charge Details (Posted Tier vs. Regular Price)**: For non-PF charges (diagnostic requests, medicines, supplies, procedures), display which tier price was applied based on the patient classification (`PHIC`, `HMO`, `OTHERS`, `REGULAR`) compared against the regular catalog price on the Patient Charges tab and inside the Edit Charge modal dialog.

---

## User Review Required

> [!NOTE]
> - **Select2 Integration**: Select2 will be bound to `#hmo_type` on the settlement modal and `#add_stl_hmo_type` / `#edit_stl_hmo_type` on the admin settlement page. Because these controls reside inside Bootstrap modal dialogs, Select2 will use `dropdownParent: $(modalId)` to ensure the search input receives focus and renders properly above modal backdrops.
> - **Dompdf Layout Compatibility**: The Rx printable template will use strict table-based and absolute/relative CSS structures rather than CSS Grid or Flexbox, ensuring 100% pixel-perfect PDF rendering without Dompdf layout distortion.
> - **Database Schema Safety**: No database migrations are required for this phase as all fields (`pfrate`, `rodrate`, `tax`, `vatrate`, `vatable`, `autoAddVAT`, `issuehospOR`, `price_regular`, `price_phic`, `price_hmo`, `price_others`) already exist in `doctors` and `stocks_listing` tables.

---

## Proposed Changes

```
┌────────────────────────────────────────────────────────────────────────┐
│                        PROPOSED ARCHITECTURE FLOW                      │
└────────────────────────────────────────────────────────────────────────┘

 [Doctor Dashboard]             [Doctor / Secretary / Admin Charges]
         │                                       │
         ▼                                       ▼
 DoctorController::updateDoctorFee      DoctorController::fetchPatientCharges
   - Updates DoctorsProfileModel          - Enriches PF with rate/tax/vat details
   - Safely updates DoctorModel           - Enriches Other items with regular vs posted
         │                                       │
         ▼                                       ▼
  (No SQL 1054 error!)               [Charges Table & Edit Charge Modal]
                                       - Badges: PF Rate, Tax, ROD, Vatable
                                       - Badges: HMO/PHIC Tier vs Regular Price
                                                 │
 [Settlement Modal] ◄────────────────────────────┘
   - Searchable Select2 HMO Dropdown
   - Preserves selection from hmo_masterlist
         │
         ▼
 [Printable Rx PDF]
   - Styled after temp/bd4af504-fdfd-4636-9ae8-7c84d1f0ec64.jfif
   - Framed blue border, patient diagnosis line, doctor qualification
   - Name/desc + Sig: instructions + bottommost general advice + footer
```

---

### Component 1: Doctor Billing Rates SQL Fix

#### [MODIFY] [`app/Http/Controllers/DoctorController.php`](file:///C:/xampp/htdocs/kayakapmd_clinic/app/Http/Controllers/DoctorController.php)
- In `updateDoctorFee`:
  - Retain update of `DoctorsProfileModel` (`doctors` table) for: `pfrate`, `rodrate`, `tax`, `vatrate`, `coacode`, `accountno`, `vatable`, `autoAddVAT`, `issuehospOR`.
  - Fix `DoctorModel::where('docrefno', $doctorAuth->docrefno)->update(...)`:
    - Remove nonexistent columns: `'withholdingtax'`, `'autoAddVAT'`, `'issuehospOR'`.
    - Only update valid columns that actually exist on `doctorsrights`: `'consultationfee' => $pfrate`, `'taxpercent' => $tax`, `'bankacct' => $accountno`.
- In `fetchPatientCharges`:
  - Enrich transformed charge objects:
    - If `prodcode === 'PF'` or `item_grouping === 'PROFESSIONAL FEE'`:
      - Attach `is_pf: true`, `pf_rate`, `rod_rate`, `tax_percent`, `vat_rate`, `vatable`, `auto_add_vat`.
    - If other item:
      - Attach `is_pf: false`, `regular_price`, `posted_tier`, `posted_tier_price`, `price_phic`, `price_hmo`, `price_others`.

---

### Component 2: Searchable HMO Dropdown

#### [MODIFY] [`resources/views/modals/settlement_modal.blade.php`](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/views/modals/settlement_modal.blade.php)
- Update `<select name="hmo_type" id="hmo_type">` wrapper styling to integrate seamlessly with Select2 in an input group.
- Keep pre-rendered `<option>` items from `\App\Models\HMOModel`.

#### [MODIFY] [`resources/js/pages/secretary/queue.js`](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/js/pages/secretary/queue.js)
- Initialize Select2 on `#hmo_type` with `dropdownParent: $("#settlement_modal")`, `placeholder: "-- Select HMO --"`, and `allowClear: true`.
- Update value assignments to trigger `$("#hmo_type").val(...).trigger("change")`.
- Reset Select2 selection on modal open/reset.

#### [MODIFY] [`resources/js/secretary.js`](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/js/secretary.js)
- Initialize Select2 on `#hmo_type` with `dropdownParent: $("#settlement_modal")`.
- Trigger `change` on value updates.

#### [MODIFY] [`resources/views/pages/admin/consultations/settlements.blade.php`](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/views/pages/admin/consultations/settlements.blade.php)
- Update `#add_stl_hmo_type` and `#edit_stl_hmo_type` select wrappers for Select2 styling.

#### [MODIFY] [`resources/js/pages/admin/consultations/settlements.js`](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/js/pages/admin/consultations/settlements.js)
- Initialize Select2 on `#add_stl_hmo_type` with `dropdownParent: $("#add_settlement_modal")`.
- Initialize Select2 on `#edit_stl_hmo_type` with `dropdownParent: $("#edit_settlement_modal")`.
- Trigger `change` when setting values in `edit-stl-btn`.

---

### Component 3: Rx Printable Prescription Pad Redesign

#### [MODIFY] [`resources/views/printables/rx_print.blade.php`](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/views/printables/rx_print.blade.php)
- Redesign the layout based on `temp/bd4af504-fdfd-4636-9ae8-7c84d1f0ec64.jfif`:
  - **Outer Frame**: 3px solid primary blue border (`#2b6cb0`) framing the page.
  - **Top Header**:
    - Left side: Stethoscope & heart vector/image.
    - Doctor Name: `Dr. {{ $doctor->docname }}` (large bold primary blue).
    - Qualification: `{{ strtoupper($doctor->expertise ?: ($doctor->proftype ?: 'General Physician')) }}` with a clean accent underline.
    - Credentials: `Lic No. {{ $doctor->Licno }} | PTR No. {{ $doctor->PTR }} | S2 No. {{ $doctor->S2no }}`.
    - Right side: `{{ strtoupper($profile->HOSP_NAME ?? 'KAYAKAPMD CLINIC') }}` and `{{ $profile->HOSP_ADDBRGY ?? 'Healthcare Clinic & Diagnostics' }}`.
  - **Patient Info Section**:
    - Light blue container (`#f0f7fd` with `#d0e6f9` border).
    - Underline fillable layout:
      - Line 1: `Patient Name: {{ $patient->patientname }} ___________________________`
      - Line 2: `Address: {{ $patient->address }} ___________________________________`
      - Line 3: `Age: {{ $patient->age }} _______   Sex: {{ $patient->gender }} _______   Date: {{ now()->format('m/d/Y') }} _______`
      - Line 4: `Diagnosis: {{ $patient->finadiagnosis ?: ($patient->impression ?: 'Clinical evaluation') }} _________________`
  - **Prescription Area**:
    - Stylish bold blue `Rx` symbol.
    - Medicines listed in requested order:
      ```
      {name/description} (Qty: {qty})
      Sig: {instructions}
      ```
    - General instructions at the bottommost part:
      `Special Instructions: {{ $patient->instructions }}`
  - **Footer & Signature**:
    - Physician signature line on the right side over `Dr. {{ $doctor->docname }}`.
    - Bottom 2x2 contact grid with icons: Phone, Email, Clinic Address, Web URL.
    - Subtle blue watermark curve in the bottom-right corner.

---

### Component 4: Charge Details on Patient Charges Tab & Edit Modal

#### [MODIFY] [`resources/js/pages/doctor/consultation/form.js`](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/js/pages/doctor/consultation/form.js)
- In `#charges_table` DataTables render:
  - If `is_pf || prodcode === 'PF'`:
    - Display sub-badges: Base PF Rate (`₱{pf_rate}`), Withholding Tax (`{tax_percent}%`), ROD Rate if configured (`₱{rod_rate}`), Vatable status (`Vatable` or `Non-VAT`), and Auto Add VAT badge.
  - If other charge:
    - Display sub-badges: Applied Tier (`{posted_tier}: ₱{cost_ave}`), Regular Catalog (`Regular: ₱{regular_price}`), and Savings difference (`Saved: ₱{diff}`).
- In `.edit_charge_btn` Swal dialog:
  - If `is_pf`:
    - Render a "Professional Fee Breakdown" summary card displaying Base PF Rate, Withholding Tax %, ROD Rate, and Vatable status above the unit price input.
  - If other charge:
    - Render a "Catalog Pricing Comparison" summary card displaying Regular Price, Posted Tier Rate, and Difference above the unit price input.

#### [MODIFY] [`resources/js/pages/secretary/queue.js`](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/js/pages/secretary/queue.js)
- In `#pxcharges_table` DataTables render:
  - Mirror the detailed PF badges (Base PF, Withholding Tax %, ROD Rate, Vatable status) and Other Charges badges (Applied Tier vs. Regular Price).
- In `.edit_charge_btn_sc` modal dialog:
  - Render the matching PF Breakdown card or Catalog Pricing Comparison card so secretaries and admins see the exact billing and tier comparison when adjusting fees.

#### [MODIFY] [`resources/js/secretary.js`](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/js/secretary.js)
- Mirror the detailed charge badges in `#pxcharges_table` and the breakdown cards in `.edit_charge_btn_sc`.

---

## Verification Plan

### Automated Tests
1. Run PHPUnit feature tests to verify:
   - Doctor dashboard billing rates update without SQL error:
     ```bash
     php artisan test --filter=DoctorSecretaryConsoleTest
     ```
   - Charges endpoint returns enriched PF details and tier price comparisons:
     ```bash
     php artisan test --filter=ConsultationChargesAndSettlementsTest
     ```
   - Rx printable PDF streams and includes new layout elements (Diagnosis, qualification, name/sig order):
     ```bash
     php artisan test --filter=ConsultationChargesAndSettlementsTest
     ```
   - Run entire test suite to guarantee 0 regressions:
     ```bash
     php artisan test
     ```

### Manual Verification
1. **Searchable HMO Dropdown**:
   - Open `#settlement_modal` in Secretary Queue and verify `#hmo_type` has a searchable Select2 dropdown that filters options dynamically as you type.
   - Open `#add_settlement_modal` and `#edit_settlement_modal` in Admin Settlements and verify `#add_stl_hmo_type` and `#edit_stl_hmo_type` are searchable Select2 dropdowns.
2. **Doctor Dashboard Billing Rates**:
   - Log into doctor account, edit "Default Consultation Fee & Billing Rates" on dashboard widget, click "Update Consultation Fee", and verify success toast appears without SQL 1054 error.
3. **Professional Fee & Other Charge Details**:
   - Open Doctor consultation charges tab: verify PF row displays PF Rate, Withholding Tax %, ROD Rate, and Vatable badges; verify diagnostic/medicine rows display Posted Tier vs. Regular Price.
   - Click "Edit Charge" on PF: verify Breakdown card is visible.
   - Click "Edit Charge" on medicine/diagnostic: verify Catalog Pricing Comparison card is visible.
4. **Rx Printable Layout**:
   - Generate printable prescription PDF via `/print_pdf?consultationrefno=...` and verify the visual layout matches `bd4af504-fdfd-4636-9ae8-7c84d1f0ec64.jfif`.
