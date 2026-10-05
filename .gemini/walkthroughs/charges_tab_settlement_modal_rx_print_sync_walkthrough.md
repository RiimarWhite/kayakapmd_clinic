# Walkthrough: Patient Tier Pricing, Doctor Rates Sync, Rx Print Layout & Settlement Modal Redesign

## Summary of Completed Tasks

This walkthrough documents the end-to-end implementation and verification of the clinical, billing, and settlement synchronization features across Doctor, Secretary, and Admin modules in KayakapMD Clinic.

---

## 1. Patient Tier-Based Pricing for Doctor & Secretary Charges
- **Controller Logic (`DoctorController.php`)**:
  - `saveMedicineRx` & `addMedicine`: Unit price dynamically resolved based on patient classification (`PHIC` → `price_phic`, `HMO` → `price_hmo`, `OTHERS` → `price_others`, `REGULAR` → `price_regular`).
  - `saveRequests` & `saveDiagnosticRequest`: Diagnostic unit prices dynamically adhere to the patient type tier pricing.
  - `fetchPatientCharges`: Charges transformation resolves item `current_price` and `sellingprice` based on patient classification; automatically generates the initial Professional Fee charge with VAT applied when `autoAddVAT` is enabled.
- **Secretary Queue View & Script (`resources/views/pages/secretary/queue.blade.php`, `resources/js/pages/secretary/queue.js`)**:
  - Added `Unit Price` column to `#pxcharges_table` matching doctor console.
  - Updated Select2 charge search to display tiered pricing breakdown in option labels (`REG: ₱X | PHIC: ₱Y | HMO: ₱Z | OTH: ₱W`) and automatically set `#sec_charge_amount` based on active patient type.

---

## 2. Doctor Dashboard Default Rates & Profile Modal Sync
- **Modal Binding (`resources/js/pages/doctor/dashboard.js`)**:
  - Correctly bound Tab 4 (Rates, Tax & Billing) inputs to the database attributes:
    - `#prof_pfrate` ↔ `pfrate`
    - `#prof_rodrate` ↔ `rodrate`
    - `#prof_tax` ↔ `tax`
    - `#prof_vatrate` ↔ `vatrate`
    - `#prof_coacode` ↔ `coacode`
    - `#prof_accountno` ↔ `accountno`
    - `#prof_vatable` ↔ `vatable`
    - `#prof_autoAddVAT` ↔ `autoAddVAT`
    - `#prof_issuehospOR` ↔ `issuehospOR`
  - Two-way sync: opening modal populates fields from dashboard widget / profile; saving profile updates dashboard widget immediately.
- **Model Fillable (`app/Models/DoctorsProfileModel.php`)**:
  - Added `vatrate` to `$fillable`.

---

## 3. Professional Fee Charge Calculation & Edit Dialog Fixes
- **Auto Add VAT**:
  - In `DoctorController::fetchPatientCharges`, when auto-creating initial PF charge, if `autoAddVAT` is true, VAT is calculated and added (`Fee = BaseFee + (BaseFee * (vatrate / 100))`).
- **Edit Charge Modal (`resources/js/pages/doctor/consultation/form.js`)**:
  - Removed discount input field from the `.edit_charge_btn` dialog.
  - Displays only the Unit Price input (`Unit Price (₱)`).
  - Submits `discount: 0` to `/api/update_charge`.

---

## 4. Rx Prescription Printable PDF Layout
- **Blade Template (`resources/views/printables/rx_print.blade.php`)**:
  - Medicines ordered as:
    ```
    {name/description} (Qty: {quantity})
    Sig: {instructions}
    ```
  - General instructions (`$patient->instructions`) relocated to the bottommost section of the Rx card.

---

## 5. Settlement Modal Redesign & Net Billing Recomputation
- **Database Schema Sync**:
  - Migration created: `database/migrations/2026_10_05_000001_add_discount_and_claim_fields_to_pxsettlements.php`.
  - Added `srpwd_refno` (VARCHAR 80), `phic_icd_rvs` (VARCHAR 100), `discount_description` (VARCHAR 255) to `pxsettlements`.
  - Updated authoritative Data Dictionary: `.gemini/database/kayakapmdv2_data_dictionary.md`.
  - Updated `app/Models/SettlementsModel.php` `$fillable` array.
- **Modal Redesign (`resources/views/modals/settlement_modal.blade.php`)**:
  - Senior/PWD discount checkbox (`#is_srpwd`) toggles sub-fields: Ref / ID # (`#srpwd_refno`) and Amount (`#less_srpwd`).
  - PhilHealth (PHIC) coverage: ICD/RVS code (`#phic_icd_rvs`) and Amount (`#phic`).
  - HMO coverage: HMO provider select (`#hmo_type`) and Amount (`#hmo`).
  - Other discount: Description (`#discount_description`) and Amount (`#less_discount`).
  - Dynamic **Net Billing** display (`#net_billing_display` & hidden `#net_payable_input`) dynamically recomputed:
    $$\text{Net Billing} = \max(0, \text{Gross Total} - (\text{Senior/PWD} + \text{PHIC} + \text{HMO} + \text{Other Discount}))$$
  - Payment settlement channels (**CASH** and **CTA**) moved to bottom of the modal.
  - Added `.import-net-billing` buttons to import remaining net billing balance into Cash or CTA.
  - Comprehensive itemized breakdown in the **View Settlements** tab (`#view_sett`).
- **JavaScript & Backend Sync**:
  - `resources/js/pages/secretary/queue.js` and `resources/js/secretary.js`: dynamic deduction bounding, Net Billing recomputation, payment dispersion, and View Settlements population.
  - `SecretaryController.php` (`saveSettlements` & `fetchSettlements`): handles `less_srpwd`, `srpwd_refno`, `phic_icd_rvs`, `less_hmo`, `hmo_type`, `less_discount`, `discount_description`, and recalculates `net_payable`.
  - `ManagementController.php` (`addAdminSettlement` & `editAdminSettlement`): handles all deduction and reference fields.
  - `resources/views/pages/admin/consultations/settlements.blade.php` & `resources/js/pages/admin/consultations/settlements.js`: Add and Edit modals updated with the new fields and math calculations.

---

## 7. HMO Dropdown Options Sourced From `hmo_masterlist`
- **Authoritative Database Source**:
  - Bound HMO selection dropdowns directly to `hmo_masterlist` (via `\App\Models\HMOModel`).
- **Blade Pre-Rendering & Elimination of Race Conditions**:
  - `resources/views/modals/settlement_modal.blade.php`: Pre-renders `<option value="{{ $hmoItem->hmocode }}">{{ $hmoItem->hmoname }}</option>` directly from `\App\Models\HMOModel::whereNotNull('hmoname')->where('hmoname', '!=', '')->orderBy('hmoname', 'ASC')->get()`. Eliminates race conditions where asynchronous AJAX calls could wipe out preselected HMO options.
  - `resources/views/pages/admin/consultations/settlements.blade.php`: Updated `add_stl_hmo_type` and `edit_stl_hmo_type` from text inputs to `<select>` dropdowns pre-rendered with options from `HMOModel`.
- **Backend API & Controller Query Synchronization**:
  - `ConsultationController::fetchHMO` and `SecretaryController::fetchHmo`: Updated queries to order records alphabetically by `hmoname ASC` with clientcode fallbacks.
  - `SecretaryController::saveSettlements` & `ManagementController::addAdminSettlement` / `editAdminSettlement`: Resolve both `hmocode` and `hmoname` from `HMOModel` (`hmo_masterlist`) when saving settlements into `pxsettlements`.
- **Frontend Preselection & Value Preservation**:
  - `resources/js/pages/secretary/queue.js`, `resources/js/secretary.js`, and `resources/js/pages/admin/consultations/settlements.js`:
    - Preserves pre-rendered HMO `<option>` elements on modal open.
    - Accurately preselects the active HMO option whether matching `hmocode` or `hmoname`.
    - Correctly displays the human-readable HMO company name in the **View Settlements** tab (`#view_sett`).

---

## 8. Searchable HMO Dropdowns (Select2)
- **Select2 Modal Integration**:
  - `resources/views/modals/settlement_modal.blade.php`: Wrapped `#hmo_type` in a responsive flex container inside the input group to maintain full-width Select2 rendering and prevent layout breakage.
  - `resources/views/pages/admin/consultations/settlements.blade.php`: Added `w-100` classes to `#add_stl_hmo_type` and `#edit_stl_hmo_type`.
  - `resources/js/pages/secretary/queue.js`: Initialized Select2 with `dropdownParent: $("#settlement_modal")`, placeholder `"-- Select HMO --"`, and `allowClear: true`. Attached change event listeners to trigger net billing recomputations.
  - `resources/js/secretary.js`: Initialized Select2 on `#hmo_type` with modal parent scoping.
  - `resources/js/pages/admin/consultations/settlements.js`: Initialized Select2 on `#add_stl_hmo_type` (parent `#add_settlement_modal`) and `#edit_stl_hmo_type` (parent `#edit_settlement_modal`).

---

## 9. Rx Printable Redesign (`temp/bd4af504-fdfd-4636-9ae8-7c84d1f0ec64.jfif`)
- **Visual & Structural Architecture (`resources/views/printables/rx_print.blade.php`)**:
  - **Outer Frame**: 3.5px solid blue border (`#286aa0`) with rounded corners enclosing the entire stationery pad.
  - **Header Layout**:
    - Left: Scalable vector icon of stethoscope forming a medical heart.
    - Center: Attending Physician name in bold blue (`Dr. ...`), specialization/qualification in uppercase tracked font, horizontal accent rule, and License No., PTR No., S2 No.
    - Right: Hospital / Clinic name (`$profile->HOSP_NAME`) and clinical department tagline.
  - **Patient Info Box**: Soft shaded light-blue background (`#edf5fc`) with 4 distinct underline rows:
    - Row 1: Patient Name
    - Row 2: Address
    - Row 3: Age, Sex, and Date
    - Row 4: Diagnosis (populated from `finadiagnosis` or `impression`)
  - **Rx Body**:
    - Prominent classic blue stylized `Rx` symbol (`&#8478;`) on the left.
    - Medicine items formatted strictly as:
      ```
      {name/description} (dosage) # {qty}
      Sig: {instructions} (Duration: {duration})
      ```
    - General / special instructions placed strictly at the bottommost part of the Rx section.
  - **Footer & Signature**:
    - Physician signature line over printed doctor name and license details (right-aligned).
    - Bottom 2x2 contact info grid (Phone & Address on left, Email & Website in middle).
    - Translucent watermark curve / loop in bottom-right corner matching reference stationery.

---

## 10. Doctor Dashboard Rates SQL Fix & Charge Details Enrichment
- **Doctor Rates SQL Fix (`DoctorController::updateDoctorFee`)**:
  - Resolved MySQL Error 1054 (`Unknown column 'withholdingtax' in 'field list'`): Removed invalid columns from `DoctorModel` (`doctorsrights`) update, keeping only `consultationfee`, `taxpercent`, and `bankacct`.
  - Accurately persisted `pfrate`, `rodrate`, `tax`, `vatrate`, `autoAddVAT`, `vatable`, `coacode`, `accountno`, `issuehospOR` to `DoctorsProfileModel` (`doctors`).
  - Added `consultationfee` accessor to `DoctorsProfileModel` for transparent mapping to `pfrate`.
- **Backend Charge Transformation (`DoctorController::fetchPatientCharges`)**:
  - Enriched Professional Fee items (`is_pf: true`): includes `pf_rate`, `rod_rate`, `tax_percent`, `vat_rate`, `vatable`, and `auto_add_vat`.
  - Enriched catalog items (`is_pf: false`): includes `regular_price`, `posted_tier`, `posted_tier_price`, `price_phic`, `price_hmo`, `price_others`.
- **Frontend Charges Table & Edit Modal (`form.js`, `queue.js`, `secretary.js`)**:
  - **Professional Fee Display**: Renders badges for Base PF Rate, Vatable (12%) / Non-VAT, +VAT Added, Withholding Tax %, and ROD Rate.
  - **Other Items Display**: Renders applied classification tier badge (PHIC, HMO, OTHERS, REGULAR) and compares with regular catalog price (displaying savings badges e.g. `Saved ₱X.XX` or `+₱Y.YY`).
  - **Edit Modal**:
    - For PF: Displays a styled **Professional Fee & Billing Rates** card with doctor fee parameters, disabled vatable checkbox, and editable unit price.
    - For Other Items: Displays a styled **Catalog Pricing Comparison** card with Regular, PHIC, HMO, and Others prices, and editable unit price and quantity.
    - **Discount Field Removed**: Completely eliminated the discount input field from the UI across all edit modals.

---

## 11. Rx Printable Image Fix & Settlement Modal Select2 HMO Dropdown Fix
- **Rx Printable Symbol Image Fix (`resources/views/printables/rx_print.blade.php`, `public/images/rx_icon_blue.png`)**:
  - **Root Cause**: Dompdf's default core fonts (`Helvetica`, `Times-Roman`) use standard 8-bit WinAnsi/ISO-8859-1 encoding. The Unicode HTML entity `&#8478;` (`℞`, U+211E) is not part of that character set, resulting in Dompdf rendering an ASCII `?` glyph on printed prescription PDFs.
  - **Solution**:
    - Created `public/images/rx_icon_blue.png` (themed with exact brand blue `#286aa0`).
    - Updated `resources/views/printables/rx_print.blade.php` to render `<img>` with absolute file path pointing to `public_path('images/rx_icon_blue.png')` (falling back to `public_path('images/rx_icon.png')`).
    - Added a pure ASCII fallback `<div style="font-size: 32px; font-weight: bold; font-family: 'Times New Roman', serif; color: #286aa0; line-height: 1;">Rx</div>` so a question mark can never be displayed even in the absence of image files.
- **Settlement Modal Searchable Select2 Dropdown Fix**:
  - **Root Causes**:
    1. **Modal ID Mismatch**: In Blade (`settlement_modal.blade.php`), the modal container was defined with `id="settlementModal"` (capitalized), whereas JavaScript (`queue.js` and `secretary.js`) targeted `dropdownParent: $("#settlement_modal")` (lowercase with underscore). Because the selector matched nothing, Select2 attached its dropdown directly to `<body>`.
    2. **Backdrop & Z-Index Clamping**: When attached to `<body>`, Select2 defaulted to z-index 1051, rendering behind the Bootstrap modal dialog (z-index 1055).
    3. **Focus Trapping**: Bootstrap modals with `tabindex="-1"` enforce focus containment; attempting to focus or type in a Select2 search box attached outside the modal caused Bootstrap to immediately steal focus back.
    4. **Flexbox Width Collapse**: Inside the `.input-group`, the flexbox wrapper lacked `min-width: 0`, causing Select2's container to collapse or measure zero width when opened.
  - **Solutions Implemented**:
    - **Modal ID & Focus**: Standardized `id="settlement_modal"` across `resources/views/modals/settlement_modal.blade.php`, `resources/views/pages/secretary/queue.blade.php`, and `resources/views/secretary.blade.php`. Removed `tabindex="-1"` from `settlement_modal.blade.php` and admin modals `add_settlement_modal` and `edit_settlement_modal`.
    - **Flex Container**: Added `style="min-width: 0;"` to the `<div class="flex-grow-1">` wrapping `#hmo_type`.
    - **Global CSS Rules**: Added `.select2-container { width: 100% !important; }` and `.select2-dropdown { z-index: 9999 !important; }` to both `resources/views/layouts/app.blade.php` and `resources/css/app.css`.
    - **Lifecycle Event Hooks**: Attached `shown.bs.modal` event listeners in `queue.js`, `secretary.js`, and `settlements.js` to recalculate Select2 width and bindings when the modals are fully visible in the DOM.

---

## 12. Verification & Automated Tests
- **Automated Tests**:
  - Executed `php artisan test`: **81 passed (558 assertions)**.
  - Validated in `ConsultationChargesAndSettlementsTest`:
    - `test_unscheduled_patients_table_excludes_scheduled_patients` (PASSED)
    - `test_patient_charges_include_prescribed_drugs_and_meds` (PASSED)
    - `test_settlements_save_fetch_and_hmo_population` (PASSED)
    - `test_settlements_save_with_senior_pwd_phic_and_custom_discounts` (PASSED)
    - `test_medicine_rx_pricing_adheres_to_patient_type` (PASSED)
    - `test_doctor_professional_fee_auto_adds_vat` (PASSED)
    - `test_rx_printable_view_order_and_instructions` (PASSED)
    - `test_fetch_hmo_retrieves_and_orders_from_hmo_masterlist` (PASSED)
    - `test_settlement_resolves_hmocode_and_hmoname_from_hmo_masterlist` (PASSED)
    - `test_doctor_fee_update_does_not_throw_sql_error` (PASSED)
    - `test_fetch_patient_charges_enriches_pf_and_catalog_pricing` (PASSED)
    - `test_rx_printable_pdf_generation` (PASSED)
- **Frontend Build**:
  - Executed `cmd /c npm run build`: compiled client assets in 3.25s with 0 errors.
- **Repository Context Graph**:
  - Executed `cmd /c graft build`: synchronized 2,574 nodes and 4,358 edges.

---

## 13. Secretary Console Payment Details Charges Display Fix
- **Problem Statement**:
  - In the Secretary Console (both modern queue view `resources/views/pages/secretary/queue.blade.php` and classic view `resources/views/secretary.blade.php`), opening the **Payment Details** tab failed to render the consultation charges table (`#pxcharges_table`).
- **Root Cause Analysis**:
  1. **DataTables 2 Row-Renderer Crash**: In `resources/js/pages/secretary/queue.js` and `resources/js/secretary.js`, columns configured with `data: null` declared renderers as `function (data) { ... data.cost_ave ... }` or `data.pxchargerefno`. Under DataTables 2, when `data: null`, the first parameter is literal `null`. Accessing properties threw `TypeError: Cannot read properties of null`, aborting table initialization.
  2. **Table Header & Column Definition Mismatch**: The Blade view declared 5 `<th>` columns (`Actions`, `Description`, `Quantity`, `Unit Price`, `Amount`). However, when initialized before a consultation was selected or in classic views, only 4 column definitions existed, triggering a fatal DataTables column count mismatch alert.
  3. **Relative URL Routing Incompatibility**: In `resources/js/secretary.js`, calls used relative URLs like `fetch_pxcharges`, `fetch_charge_categories_sc`, and `save_patient_charges`. On routes under `/secretary`, these resolved to `/secretary/fetch_pxcharges` (yielding 404 Not Found) instead of the registered API endpoint `/api/fetch_pxcharges`.
  4. **Dynamic Consultation Reference Handling**: In `resources/js/secretary.js`, `$("#pxconsultationrefno")` is a `<span>` element. Code calling `$("#pxconsultationrefno").val()` evaluated to `undefined`, passing empty strings in AJAX payloads.
- **Solutions Implemented**:
  1. **Null-Safe Row Renderers (`queue.js`, `secretary.js`)**: Updated all `data: null` renderers to accept `(data, type, row)` and safely resolve data via `const r = row || data || {}`.
  2. **Standardized 5-Column Schema**: Updated both views and JavaScript DataTables configurations to consistently define 5 columns: `Actions`, `Description`, `Quantity`, `Unit Price`, and `Amount`.
  3. **API Prefix Normalization**: Prefixed all charge-related endpoints with `/api/` (`/api/fetch_pxcharges`, `/api/fetch_charge_categories_sc`, `/api/fetch_all_charges`, `/api/save_patient_charges`, `/api/delete_patient_charge`, `/api/update_charge`, `/api/fetch_settlements`, `/api/save_settlements`).
  4. **Robust Reference Getter**: Standardized reference retrieval across all handlers using `String($("#hidden_consultationrefno").val() || $("#pxconsultationrefno").text() || $("#pxconsultationrefno").val() || "").trim()`.
  5. **Clean DataTable Lifecycle & Tab Adjustment**:
     - Checked `$.fn.DataTable.isDataTable("#pxcharges_table")` before initializing. If initialized, reloaded via `table.ajax.reload(..., false)`.
     - Hooked into Bootstrap's `shown.bs.tab` event to call `loadPatientCharges()` and `columns.adjust().draw(false)` whenever the Payment Details tab becomes visible.
  6. **Backend Safeguard (`DoctorController::fetchPatientCharges`)**:
     - Added an early return `if (empty($request->consultationrefno)) return response()->json(['charges' => []]);` to prevent unnecessary or malformed database queries.
     - Extended the authenticated user fallback for charge operations to include the `secretary` guard.


