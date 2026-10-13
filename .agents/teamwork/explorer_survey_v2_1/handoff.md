# Comprehensive Survey Report & Handoff — Explorer 1 (R1, R6, R7)

**Agent**: `explorer_survey_v2_1`  
**Date**: 2026-10-12T13:15:00Z  
**Parent**: `f798415c-2a2d-4c58-89d2-1aceb6a9dca8`  
**Working Directory**: `C:\xampp\htdocs\kayakapmd_clinic\.agents\teamwork\explorer_survey_v2_1\`  
**Target Scope**:  
- **R1**: Financial Report Printable (`resources/views/printables/financial_report_print.blade.php`)
- **R6**: Patient Masterlist Details Removal (`resources/js/pages/doctor/patients.js`)
- **R7**: Header Theme Switcher (`resources/views/components/navbar.blade.php`, `resources/views/layouts/app.blade.php`, `resources/js/app.js`, `resources/css/app.css`)

---

## 1. Observation

### R1. Financial Report Printable (`resources/views/printables/financial_report_print.blade.php`)

1. **Table Header Structure (`<thead>`, lines 245–262)**:
   - Contains exactly **14 columns**:
     ```html
     245:            <thead>
     246:                <tr>
     247:                    <th style="width: 30px;" class="text-center">Q#</th>
     248:                    <th>Patient Name</th>
     249:                    <th>Attending Doctor</th>
     250:                    <th style="width: 60px;" class="text-right">Gross</th>
     251:                    <th style="width: 55px;" class="text-right">PHIC</th>
     252:                    <th style="width: 55px;" class="text-right">HMO</th>
     253:                    <th style="width: 50px;" class="text-right">Discount</th>
     254:                    <th style="width: 65px;" class="text-right">Net Payable</th>
     255:                    <th style="width: 55px;" class="text-right">Cash</th>
     256:                    <th style="width: 55px;" class="text-right">Card/CTA</th>
     257:                    <th style="width: 55px;" class="text-right">Co-Pay</th>
     258:                    <th style="width: 60px;" class="text-right">Total Paid</th>
     259:                    <th style="width: 55px;" class="text-right">Balance</th>
     260:                    <th style="width: 45px;" class="text-center">Status</th>
     261:                </tr>
     262:            </thead>
     ```
   - Line 257 contains a standalone `Co-Pay` header: `<th style="width: 55px;" class="text-right">Co-Pay</th>`.
   - Line 258 contains `Total Paid`: `<th style="width: 60px;" class="text-right">Total Paid</th>`.

2. **Table Body Columns (`<tbody>`, lines 266–299)**:
   - Line 283–288:
     ```html
     283:                            <td class="text-right">{{ $item->cash > 0 ? number_format($item->cash, 2) : '-' }}</td>
     284:                            <td class="text-right">{{ $item->card > 0 ? number_format($item->card, 2) : '-' }}</td>
     285:                            <td class="text-right">{{ $item->copay > 0 ? number_format($item->copay, 2) : '-' }}</td>
     286:                            <td class="text-right" style="color: #2e7d32;"><strong>{{ number_format($item->paid, 2) }}</strong></td>
     287:                            <td class="text-right" style="color: {{ $item->balance > 0 ? '#c62828' : '#718096' }};">
     288:                                {{ $item->balance > 0 ? number_format($item->balance, 2) : '0.00' }}
     289:                            </td>
     ```
   - Line 285 renders standalone copay: `{{ $item->copay > 0 ? number_format($item->copay, 2) : '-' }}`.
   - Line 286 renders total collections: `<strong>{{ number_format($item->paid, 2) }}</strong>`. In `SecretaryController.php` (line 1559), `$paid = $cash + $card + $copay;`.

3. **Table Footer Totals (`<tfoot>`/totals `<tr>`, lines 301–314)**:
   - Lines 308–313:
     ```html
     308:                        <td class="text-right">{{ number_format($summary->total_cash, 2) }}</td>
     309:                        <td class="text-right">{{ number_format($summary->total_card, 2) }}</td>
     310:                        <td class="text-right">{{ number_format($summary->total_copay, 2) }}</td>
     311:                        <td class="text-right" style="color: #2e7d32;">{{ number_format($summary->total_paid, 2) }}</td>
     312:                        <td class="text-right" style="color: #c62828;">{{ number_format($summary->total_balance, 2) }}</td>
     313:                        <td></td>
     ```
   - Line 310 renders `$summary->total_copay` for the standalone copay column.
   - Line 311 renders `$summary->total_paid`.

4. **Empty Row Colspan (lines 316–320)**:
   ```html
   316:                    <tr>
   317:                        <td colspan="14" class="text-center" style="padding: 12px; color: #a0aec0; font-style: italic;">
   318:                            No patient consultations recorded for this date.
   319:                        </td>
   320:                    </tr>
   ```
   - Currently hardcoded to `colspan="14"`.

5. **Payment Channels Breakdown Sub-strip (lines 220–238)**:
   ```html
   220:        {{-- Payment Channels Breakdown Sub-strip --}}
   221:        <table style="width: 100%; border-collapse: collapse; font-size: 8.5px; background-color: #faf5ff; border: 1px solid #e9d8fd; border-radius: 4px; padding: 4px 8px; margin-bottom: 8px;">
   222:            <tr>
   223:                <td style="padding: 2px 6px;">
   224:                    <strong>Collections Breakdown:</strong>
   225:                </td>
   226:                <td style="padding: 2px 6px;">
   227:                    Cash: <strong>PHP {{ number_format($summary->total_cash, 2) }}</strong>
   228:                </td>
   229:                <td style="padding: 2px 6px;">
   230:                    Card / CTA: <strong>PHP {{ number_format($summary->total_card, 2) }}</strong>
   231:                </td>
   232:                <td style="padding: 2px 6px;">
   233:                    PhilHealth Yakap Co-Pay: <strong>PHP {{ number_format($summary->total_copay, 2) }}</strong>
   234:                </td>
   235:                <td style="padding: 2px 6px; text-align: right; color: #c53030;">
   236:                    Unsettled Balance: <strong>PHP {{ number_format($summary->total_balance, 2) }}</strong>
   237:                </td>
   238:            </tr>
   239:        </table>
   ```
   - Line 232–234 contains the standalone PhilHealth Yakap Co-Pay summary: `PhilHealth Yakap Co-Pay: <strong>PHP {{ number_format($summary->total_copay, 2) }}</strong>`.
   - KPI metrics box (lines 211–214) has: `<div class="kpi-title" style="color: #22543d;">Total Collected</div><div class="kpi-value" style="color: #22543d;">PHP {{ number_format($summary->total_paid, 2) }}</div>`.

---

### R6. Patient Masterlist (`resources/js/pages/doctor/patients.js`)

1. **Table Action Column Render Function (lines 71–100)**:
   ```javascript
   71:                 {
   72:                     data: null,
   73:                     render: function (data) {
   74:                         let btns = `
   75:                             <div class="d-flex gap-1 justify-content-center">
   76:                                 <!-- Detailed Comment: Direct Details action button for Doctor Patient Masterlist -->
   77:                                 <button type="button" class="btn btn-sm btn-primary view-consultation-details-btn btn-view-details" value="${data.consultationrefno || ''}" data-consultationrefno="${data.consultationrefno || ''}" data-pxrefno="${data.pxrefno || ''}" data-pincode="${data.pincode || ''}" title="View Consultation Details">
   78:                                     <i class="fa-solid fa-file-medical me-1"></i> Details
   79:                                 </button>
   80:                                 <button type="button" class="btn btn-sm btn-info text-white masterlist-view" value="${data.pxrefno || ''}" data-pincode="${data.pincode || ''}" title="View Patient Details">
   81:                                     <i class="fa-solid fa-eye"></i>
   82:                                 </button>
   83:                                 <button type="button" class="btn btn-sm btn-secondary masterlist-history" value="${data.pincode || ''}" data-pxrefno="${data.pxrefno || ''}" data-consultationrefno="${data.consultationrefno || ''}" title="Consultation History">
   84:                                     <i class="fa-solid fa-clock-rotate-left"></i>
   85:                                 </button>`;
   86: 
   87:                         if (isAdmin) {
   88:                             btns += `
   89:                                 <button type="button" class="btn btn-sm btn-primary masterlist-edit" value="${data.pxrefno || ''}" title="Edit Patient Details">
   90:                                     <i class="fa-solid fa-pen-to-square"></i>
   91:                                 </button>
   92:                                 <button type="button" class="btn btn-sm btn-danger masterlist-delete" value="${data.pxrefno || ''}" data-name="${data.patientname || ''}" title="Delete Patient Record">
   93:                                     <i class="fa-solid fa-trash"></i>
   94:                                 </button>`;
   95:                         }
   96: 
   97:                         btns += `</div>`;
   98:                         return btns;
   99:                     }
   100:                 },
   ```
2. **Consultation Details Triggers elsewhere in `patients.js`**:
   - Line 274: inside `#viewPatientModal` (Medical History tab):
     ```javascript
     <button type="button" class="btn btn-sm btn-primary view-consultation-details-btn btn-view-details" data-source="view_medhistory" data-index="${idx}" data-consultationrefno="${item.consultationrefno || ''}" title="View Consultation Details">
         <i class="fa-solid fa-eye me-1"></i> Details
     </button>
     ```
   - Line 355: inside `#patientMedhistoryModal` (`#medhistorytable`):
     ```javascript
     <button type="button" class="btn btn-sm btn-primary view-consultation-details-btn btn-view-details" data-source="medhistorytable" data-index="${meta.row}" data-consultationrefno="${row.consultationrefno || ''}" title="View Consultation Details">
         <i class="fa-solid fa-eye me-1"></i> Details
     </button>
     ```
   - Lines 1089–1125: delegated click listener:
     ```javascript
     $(document).on("click", ".view-consultation-details-btn, .btn-view-details", function () { ... });
     ```
3. **Blade Template Header (`resources/views/pages/doctor/patients.blade.php`, lines 27–41)**:
   - Header has 7 columns: Actions (width 1%), Photo (width 5%), Patient Name, PIN / Reference (width 12%), Mobile No. (width 12%), Email Address (width 15%), Last Consultation (width 12%).
   - The Actions column is generic and does not have hardcoded sub-columns.

---

### R7. Header Theme Switcher (`components/navbar.blade.php`, `layouts/app.blade.php`, `resources/js/app.js`, `resources/css/app.css`)

1. **Current Header Theme Button (`resources/views/components/navbar.blade.php`, lines 11–15)**:
   ```html
   11:         <div class="d-flex gap-2">
   12:             <button class="btn btn-light">
   13:                 <i class="fa-solid fa-sun"></i>
   14:             </button>
   ```
   - Static non-interactive placeholder button. No dropdown, no ID, no event listener attached.

2. **Current `layouts/app.blade.php` `<head>` and Styling (lines 1–146)**:
   - Line 2: `<html class="vh-100">` lacks any initial `data-bs-theme` attribute.
   - Lines 4–10: `<head>` lacks an inline theme bootstrap script, which causes Flash of Unstyled Content (FOUC) when dark theme is applied later by bundled JS.
   - Lines 88–107: Inline style tag `#app-dynamic-theme-vars` defines:
     ```css
     body {
         background-color: var(--app-bg) !important;
         color: var(--body-text) !important;
     }
     ```
   - Because `!important` is attached to body background and text color, Bootstrap 5.3's built-in CSS variable overrides for dark mode (`--bs-body-bg`, `--bs-body-color`) will be overridden by light brand variables unless explicit `[data-bs-theme="dark"]` rules are declared with equal or higher specificity.

3. **Current `resources/js/app.js` (lines 1–34)**:
   - Imports Bootstrap 5.3.8:
     ```javascript
     17: import * as bootstrap from 'bootstrap';
     18: window.bootstrap = bootstrap;
     ```
   - No theme switching controller or `localStorage` logic is currently implemented in `app.js`.

---

## 2. Logic Chain

### R1. Financial Report Printable Logic Chain

1. **Observation 1 & 2**: The table header has 14 columns including both standalone `Co-Pay` (line 257) and `Total Paid` (line 258). The body has `$item->copay` (line 285) and `$item->paid` (line 286).
2. **Requirement Alignment**: The requirement specifies:
   - Remove standalone `Co-Pay` column (`<th>` and `<td>` for `$item->copay`).
   - Rename `Total Paid` column header to `CO-PAY`.
   - Maintain patient collection totals (`$item->paid`) under the `CO-PAY` header.
   - Column count decreases by 1 (14 → 13).
3. **Observation 3 & 4**: In the footer totals row, line 310 (`$summary->total_copay`) corresponds to the removed standalone copay column, while line 311 (`$summary->total_paid`) is the grand total of patient collections. In the empty table row, `colspan="14"` must be updated to `colspan="13"` to prevent grid layout breakage in DomPDF.
4. **Observation 5**: In the Collections Breakdown sub-strip, line 232–234 contains `PhilHealth Yakap Co-Pay: <strong>PHP {{ number_format($summary->total_copay, 2) }}</strong>`. Following the prompt to "remove summary total for standalone copay and keep total paid under CO-PAY", we replace standalone copay with `Total CO-PAY: <strong>PHP {{ number_format($summary->total_paid, 2) }}</strong>` (or in KPI box title `Total Collected (CO-PAY)`).

### R6. Patient Masterlist Logic Chain

1. **Observation 1**: In `resources/js/pages/doctor/patients.js`, `loadMasterlist()` builds `btns` for each row in `#masterlist_table`. Lines 76–79 inject the `<button class="view-consultation-details-btn btn-view-details">` directly into the Actions column.
2. **Masterlist Domain Context**: Unlike the today's queue table where each row is an active consultation, `#masterlist_table` lists patients from `pxmasterlist`. A patient may have multiple consultations or zero consultations.
3. **Observation 2**: Viewing consultation details is already fully supported inside `#viewPatientModal` (Medical History tab, line 274) and `#patientMedhistoryModal` (line 355). Both use `.view-consultation-details-btn` with specific consultation contexts (`data-consultationrefno`).
4. **Conclusion**: Removing the Details button from `loadMasterlist()` (lines 76–79) removes it cleanly from `#masterlist_table` action column without affecting `.masterlist-view` (View Patient), `.masterlist-history` (Consultation History), or `.masterlist-edit`/`.masterlist-delete`. Crucially, the delegated click handler at line 1089 MUST BE PRESERVED so that Details buttons inside history tables continue working.

### R7. Header Theme Switcher Logic Chain

1. **Observation 1**: The navbar has a placeholder `<button class="btn btn-light"><i class="fa-solid fa-sun"></i></button>`. Replacing this with a Bootstrap 5.3 dropdown containing `Light`, `Dark`, and `System` provides standard accessibility and intuitive UX.
2. **Observation 2**: If theme resolution only occurs in `app.js`, users will experience FOUC because the HTML renders in light mode before JS runs. Putting a tiny synchronous script in `<head>` before any CSS renders reads `localStorage.getItem('theme')` (fallback to system media query) and immediately applies `data-bs-theme="dark"` or `"light"` onto `<html>`.
3. **Observation 3**: In `layouts/app.blade.php`, `body` background is forced to `--app-bg` with `!important`. To make Dark mode visually cohesive and prevent white card/table background clashes, we must add comprehensive `[data-bs-theme="dark"]` rules covering `body`, `.card`, `.modal-content`, `.table`, form inputs, navbar, sidebar, and footer.
4. **Observation 4**: In `resources/js/app.js`, a dedicated controller binds the dropdown options, writes user choice to `localStorage`, applies the theme attribute, updates the active icon and checkmarks, and listens for OS `prefers-color-scheme` changes when in `System` mode.

---

## 3. Caveats

1. **DomPDF Table Width Constraints (R1)**:
   - When dropping from 14 to 13 columns, the total table width (100% in A4 landscape) distributes among the remaining 13 columns.
   - Adjusting column widths (e.g. giving `CO-PAY` width `65px`) ensures numbers up to `999,999.99` wrap cleanly without overflowing page margins.
2. **Delegated Click Event Listener Scope (R6)**:
   - The listener `$(document).on("click", ".view-consultation-details-btn, .btn-view-details", ...)` in `patients.js` (line 1089) MUST NOT BE DELETED. Only the button HTML generation in `loadMasterlist()` (lines 76–79) should be removed.
3. **Dynamic Clinic Theme Interaction (R7)**:
   - In light mode, the database-driven branding palette (`$activeTheme`) takes precedence. In dark mode, accessibility and readability take precedence, so dark slate/charcoal backgrounds override light branding colors.
4. **Third-party Component Theme Support (R7)**:
   - Select2 and jQuery UI components use custom container styles. The CSS overrides should ensure `.select2-dropdown` and `.ui-autocomplete` remain readable in dark mode.

---

## 4. Conclusion & Proposed Code Implementations

### A. Proposed Code Changes for R1 (`resources/views/printables/financial_report_print.blade.php`)

#### 1. Header Row (`<thead>`, replace lines 246–261):
```html
<<<< BEFORE (Line 246-261)
                <tr>
                    <th style="width: 30px;" class="text-center">Q#</th>
                    <th>Patient Name</th>
                    <th>Attending Doctor</th>
                    <th style="width: 60px;" class="text-right">Gross</th>
                    <th style="width: 55px;" class="text-right">PHIC</th>
                    <th style="width: 55px;" class="text-right">HMO</th>
                    <th style="width: 50px;" class="text-right">Discount</th>
                    <th style="width: 65px;" class="text-right">Net Payable</th>
                    <th style="width: 55px;" class="text-right">Cash</th>
                    <th style="width: 55px;" class="text-right">Card/CTA</th>
                    <th style="width: 55px;" class="text-right">Co-Pay</th>
                    <th style="width: 60px;" class="text-right">Total Paid</th>
                    <th style="width: 55px;" class="text-right">Balance</th>
                    <th style="width: 45px;" class="text-center">Status</th>
                </tr>
==== AFTER
                <tr>
                    <th style="width: 30px;" class="text-center">Q#</th>
                    <th>Patient Name</th>
                    <th>Attending Doctor</th>
                    <th style="width: 60px;" class="text-right">Gross</th>
                    <th style="width: 55px;" class="text-right">PHIC</th>
                    <th style="width: 55px;" class="text-right">HMO</th>
                    <th style="width: 50px;" class="text-right">Discount</th>
                    <th style="width: 65px;" class="text-right">Net Payable</th>
                    <th style="width: 55px;" class="text-right">Cash</th>
                    <th style="width: 55px;" class="text-right">Card/CTA</th>
                    <th style="width: 65px;" class="text-right">CO-PAY</th>
                    <th style="width: 55px;" class="text-right">Balance</th>
                    <th style="width: 45px;" class="text-center">Status</th>
                </tr>
>>>>
```

#### 2. Body Row (`<tbody>`, replace lines 283–289):
```html
<<<< BEFORE (Line 283-289)
                            <td class="text-right">{{ $item->cash > 0 ? number_format($item->cash, 2) : '-' }}</td>
                            <td class="text-right">{{ $item->card > 0 ? number_format($item->card, 2) : '-' }}</td>
                            <td class="text-right">{{ $item->copay > 0 ? number_format($item->copay, 2) : '-' }}</td>
                            <td class="text-right" style="color: #2e7d32;"><strong>{{ number_format($item->paid, 2) }}</strong></td>
                            <td class="text-right" style="color: {{ $item->balance > 0 ? '#c62828' : '#718096' }};">
                                {{ $item->balance > 0 ? number_format($item->balance, 2) : '0.00' }}
                            </td>
==== AFTER
                            <td class="text-right">{{ $item->cash > 0 ? number_format($item->cash, 2) : '-' }}</td>
                            <td class="text-right">{{ $item->card > 0 ? number_format($item->card, 2) : '-' }}</td>
                            <td class="text-right" style="color: #2e7d32;"><strong>{{ number_format($item->paid, 2) }}</strong></td>
                            <td class="text-right" style="color: {{ $item->balance > 0 ? '#c62828' : '#718096' }};">
                                {{ $item->balance > 0 ? number_format($item->balance, 2) : '0.00' }}
                            </td>
>>>>
```

#### 3. Footer Row (`TOTALS`, replace lines 308–313):
```html
<<<< BEFORE (Line 308-313)
                        <td class="text-right">{{ number_format($summary->total_cash, 2) }}</td>
                        <td class="text-right">{{ number_format($summary->total_card, 2) }}</td>
                        <td class="text-right">{{ number_format($summary->total_copay, 2) }}</td>
                        <td class="text-right" style="color: #2e7d32;">{{ number_format($summary->total_paid, 2) }}</td>
                        <td class="text-right" style="color: #c62828;">{{ number_format($summary->total_balance, 2) }}</td>
==== AFTER
                        <td class="text-right">{{ number_format($summary->total_cash, 2) }}</td>
                        <td class="text-right">{{ number_format($summary->total_card, 2) }}</td>
                        <td class="text-right" style="color: #2e7d32;">{{ number_format($summary->total_paid, 2) }}</td>
                        <td class="text-right" style="color: #c62828;">{{ number_format($summary->total_balance, 2) }}</td>
>>>>
```

#### 4. Empty Row (`colspan`, replace line 317):
```html
<<<< BEFORE (Line 317)
                        <td colspan="14" class="text-center" style="padding: 12px; color: #a0aec0; font-style: italic;">
==== AFTER
                        <td colspan="13" class="text-center" style="padding: 12px; color: #a0aec0; font-style: italic;">
>>>>
```

#### 5. Summary Section (Sub-strip & KPI, replace lines 210–215 and 220–238):
```html
<<<< BEFORE (Line 210-215)
                <td style="width: 15%; padding: 2px;">
                    <div class="kpi-box" style="background-color: #f0fff4; border-color: #c6f6d5;">
                        <div class="kpi-title" style="color: #22543d;">Total Collected</div>
                        <div class="kpi-value" style="color: #22543d;">PHP {{ number_format($summary->total_paid, 2) }}</div>
                    </div>
                </td>
==== AFTER
                <td style="width: 15%; padding: 2px;">
                    <div class="kpi-box" style="background-color: #f0fff4; border-color: #c6f6d5;">
                        <div class="kpi-title" style="color: #22543d;">Total Collected (CO-PAY)</div>
                        <div class="kpi-value" style="color: #22543d;">PHP {{ number_format($summary->total_paid, 2) }}</div>
                    </div>
                </td>
>>>>
```
```html
<<<< BEFORE (Line 220-238)
        {{-- Payment Channels Breakdown Sub-strip --}}
        <table style="width: 100%; border-collapse: collapse; font-size: 8.5px; background-color: #faf5ff; border: 1px solid #e9d8fd; border-radius: 4px; padding: 4px 8px; margin-bottom: 8px;">
            <tr>
                <td style="padding: 2px 6px;">
                    <strong>Collections Breakdown:</strong>
                </td>
                <td style="padding: 2px 6px;">
                    Cash: <strong>PHP {{ number_format($summary->total_cash, 2) }}</strong>
                </td>
                <td style="padding: 2px 6px;">
                    Card / CTA: <strong>PHP {{ number_format($summary->total_card, 2) }}</strong>
                </td>
                <td style="padding: 2px 6px;">
                    PhilHealth Yakap Co-Pay: <strong>PHP {{ number_format($summary->total_copay, 2) }}</strong>
                </td>
                <td style="padding: 2px 6px; text-align: right; color: #c53030;">
                    Unsettled Balance: <strong>PHP {{ number_format($summary->total_balance, 2) }}</strong>
                </td>
            </tr>
        </table>
==== AFTER
        {{-- Payment Channels Breakdown Sub-strip --}}
        <table style="width: 100%; border-collapse: collapse; font-size: 8.5px; background-color: #faf5ff; border: 1px solid #e9d8fd; border-radius: 4px; padding: 4px 8px; margin-bottom: 8px;">
            <tr>
                <td style="padding: 2px 6px;">
                    <strong>Collections Breakdown:</strong>
                </td>
                <td style="padding: 2px 6px;">
                    Cash: <strong>PHP {{ number_format($summary->total_cash, 2) }}</strong>
                </td>
                <td style="padding: 2px 6px;">
                    Card / CTA: <strong>PHP {{ number_format($summary->total_card, 2) }}</strong>
                </td>
                <td style="padding: 2px 6px;">
                    Total CO-PAY: <strong>PHP {{ number_format($summary->total_paid, 2) }}</strong>
                </td>
                <td style="padding: 2px 6px; text-align: right; color: #c53030;">
                    Unsettled Balance: <strong>PHP {{ number_format($summary->total_balance, 2) }}</strong>
                </td>
            </tr>
        </table>
>>>>
```

---

### B. Proposed Code Changes for R6 (`resources/js/pages/doctor/patients.js`)

In `loadMasterlist()` (lines 74–86), remove the Details button:
```javascript
<<<< BEFORE (Line 74-86)
                    render: function (data) {
                        let btns = `
                            <div class="d-flex gap-1 justify-content-center">
                                <!-- Detailed Comment: Direct Details action button for Doctor Patient Masterlist -->
                                <button type="button" class="btn btn-sm btn-primary view-consultation-details-btn btn-view-details" value="${data.consultationrefno || ''}" data-consultationrefno="${data.consultationrefno || ''}" data-pxrefno="${data.pxrefno || ''}" data-pincode="${data.pincode || ''}" title="View Consultation Details">
                                    <i class="fa-solid fa-file-medical me-1"></i> Details
                                </button>
                                <button type="button" class="btn btn-sm btn-info text-white masterlist-view" value="${data.pxrefno || ''}" data-pincode="${data.pincode || ''}" title="View Patient Details">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary masterlist-history" value="${data.pincode || ''}" data-pxrefno="${data.pxrefno || ''}" data-consultationrefno="${data.consultationrefno || ''}" title="Consultation History">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </button>`;
==== AFTER
                    render: function (data) {
                        let btns = `
                            <div class="d-flex gap-1 justify-content-center">
                                <!-- Detailed Comment: Patient Details view trigger modal -->
                                <button type="button" class="btn btn-sm btn-info text-white masterlist-view" value="${data.pxrefno || ''}" data-pincode="${data.pincode || ''}" title="View Patient Details">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                                <!-- Detailed Comment: Consultation History trigger modal -->
                                <button type="button" class="btn btn-sm btn-secondary masterlist-history" value="${data.pincode || ''}" data-pxrefno="${data.pxrefno || ''}" data-consultationrefno="${data.consultationrefno || ''}" title="Consultation History">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </button>`;
>>>>
```

---

### C. Proposed Code Changes for R7 (Theme Switcher)

#### 1. Header Dropdown (`resources/views/components/navbar.blade.php`, replace lines 11–15):
```blade
<<<< BEFORE (Line 11-15)
        <div class="d-flex gap-2">
            <button class="btn btn-light">
                <i class="fa-solid fa-sun"></i>
            </button>
==== AFTER
        <div class="d-flex gap-2">
            {{-- Detailed Comment: Global Header Theme Switcher dropdown supporting Dark, Light, and System modes --}}
            <div class="dropdown" id="theme_switcher_dropdown">
                <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-1" id="bd-theme" type="button" aria-expanded="false" data-bs-toggle="dropdown" aria-label="Toggle theme">
                    <i class="fa-solid fa-circle-half-stroke me-1" id="theme-active-icon"></i>
                    <span class="d-none d-md-inline" id="theme-active-text">System</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bd-theme" id="theme_options_menu">
                    <li>
                        <button type="button" class="dropdown-item d-flex align-items-center justify-content-between" data-bs-theme-value="light" id="theme_option_light">
                            <span><i class="fa-solid fa-sun me-2 text-warning"></i> Light</span>
                            <i class="fa-solid fa-check text-primary theme-check-icon d-none"></i>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item d-flex align-items-center justify-content-between" data-bs-theme-value="dark" id="theme_option_dark">
                            <span><i class="fa-solid fa-moon me-2 text-info"></i> Dark</span>
                            <i class="fa-solid fa-check text-primary theme-check-icon d-none"></i>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item d-flex align-items-center justify-content-between" data-bs-theme-value="system" id="theme_option_system">
                            <span><i class="fa-solid fa-circle-half-stroke me-2 text-secondary"></i> System</span>
                            <i class="fa-solid fa-check text-primary theme-check-icon d-none"></i>
                        </button>
                    </li>
                </ul>
            </div>
>>>>
```

#### 2. Layout `<head>` FOUC Prevention & Dark Theme Overrides (`resources/views/layouts/app.blade.php`):
Insert immediate script in `<head>` (right after line 4):
```html
        {{-- Detailed Comment: Immediate synchronous theme initialization to prevent Flash of Unstyled Content (FOUC) --}}
        <script>
            (function () {
                const storedTheme = localStorage.getItem('theme') || 'system';
                const getPreferredTheme = () => {
                    if (storedTheme === 'dark' || storedTheme === 'light') {
                        return storedTheme;
                    }
                    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                };
                document.documentElement.setAttribute('data-bs-theme', getPreferredTheme());
            })();
        </script>
```

Add CSS overrides inside `<style id="app-dynamic-theme-vars">` or in `resources/css/app.css`:
```css
        /* Detailed Comment: Comprehensive Bootstrap 5.3 Dark Mode Overrides for cards, modals, tables, and chrome */
        [data-bs-theme="dark"] body {
            background-color: #121212 !important;
            color: #e0e0e0 !important;
        }

        [data-bs-theme="dark"] .card {
            background-color: #1e1e1e !important;
            color: #e0e0e0 !important;
            border-color: #2d3748 !important;
        }

        [data-bs-theme="dark"] .modal-content {
            background-color: #1e1e1e !important;
            color: #e0e0e0 !important;
            border-color: #2d3748 !important;
        }

        [data-bs-theme="dark"] .table {
            --bs-table-bg: #1e1e1e !important;
            --bs-table-color: #e0e0e0 !important;
            color: #e0e0e0 !important;
            border-color: #2d3748 !important;
        }

        [data-bs-theme="dark"] .table thead th,
        [data-bs-theme="dark"] .table-light,
        [data-bs-theme="dark"] .table-success {
            background-color: #2a2e33 !important;
            color: #ffffff !important;
            border-color: #373b3e !important;
        }

        [data-bs-theme="dark"] .table-striped > tbody > tr:nth-of-type(odd) > * {
            background-color: #262626 !important;
            color: #e0e0e0 !important;
        }

        [data-bs-theme="dark"] .navbar {
            background-color: #1b1e22 !important;
            color: #dee2e6 !important;
        }

        [data-bs-theme="dark"] .navbar .btn-light {
            background-color: #2b3035 !important;
            color: #dee2e6 !important;
            border-color: #495057 !important;
        }

        [data-bs-theme="dark"] #admin_sidebar,
        [data-bs-theme="dark"] #management_sidebar {
            background-color: #181a1d !important;
            color: #dee2e6 !important;
        }

        [data-bs-theme="dark"] .app-footer {
            background-color: #181a1d !important;
            color: #adb5bd !important;
            border-color: #373b3e !important;
        }

        [data-bs-theme="dark"] .dropdown-menu {
            background-color: #212529 !important;
            color: #dee2e6 !important;
            border-color: #373b3e !important;
        }

        [data-bs-theme="dark"] .dropdown-item {
            color: #dee2e6 !important;
        }

        [data-bs-theme="dark"] .dropdown-item:hover,
        [data-bs-theme="dark"] .dropdown-item:focus {
            background-color: #2b3035 !important;
            color: #ffffff !important;
        }
```

#### 3. Client Theme Switcher Module in `resources/js/app.js`:
Add to `resources/js/app.js`:
```javascript
/**
 * Detailed Comment: Global Theme Switcher Controller.
 * Handles state synchronization between localStorage, documentElement data-bs-theme attribute,
 * system media queries, and top navbar dropdown UI indicators.
 */
function initThemeSwitcher() {
    const THEME_KEY = 'theme';

    const getStoredTheme = () => {
        try {
            return localStorage.getItem(THEME_KEY) || 'system';
        } catch (e) {
            return 'system';
        }
    };

    const setStoredTheme = (theme) => {
        try {
            localStorage.setItem(THEME_KEY, theme);
        } catch (e) {
            console.warn('localStorage is not accessible for theme persistence');
        }
    };

    const getPreferredTheme = () => {
        const storedTheme = getStoredTheme();
        if (storedTheme === 'dark' || storedTheme === 'light') {
            return storedTheme;
        }
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    };

    const setTheme = (theme) => {
        const applied = theme === 'system'
            ? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
            : theme;
        document.documentElement.setAttribute('data-bs-theme', applied);
    };

    const showActiveTheme = (theme) => {
        const themeSwitcher = document.querySelector('#bd-theme');
        if (!themeSwitcher) return;

        const activeIcon = document.querySelector('#theme-active-icon');
        const activeText = document.querySelector('#theme-active-text');

        if (theme === 'dark') {
            if (activeIcon) activeIcon.className = 'fa-solid fa-moon me-1 text-info';
            if (activeText) activeText.textContent = 'Dark';
        } else if (theme === 'light') {
            if (activeIcon) activeIcon.className = 'fa-solid fa-sun me-1 text-warning';
            if (activeText) activeText.textContent = 'Light';
        } else {
            if (activeIcon) activeIcon.className = 'fa-solid fa-circle-half-stroke me-1 text-secondary';
            if (activeText) activeText.textContent = 'System';
        }

        document.querySelectorAll('[data-bs-theme-value]').forEach(btn => {
            const val = btn.getAttribute('data-bs-theme-value');
            const checkIcon = btn.querySelector('.theme-check-icon');
            if (val === theme) {
                btn.classList.add('active');
                if (checkIcon) checkIcon.classList.remove('d-none');
            } else {
                btn.classList.remove('active');
                if (checkIcon) checkIcon.classList.add('d-none');
            }
        });
    };

    const currentTheme = getStoredTheme();
    setTheme(currentTheme);
    showActiveTheme(currentTheme);

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        const stored = getStoredTheme();
        if (stored === 'system') {
            setTheme('system');
        }
    });

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-bs-theme-value]');
        if (!btn) return;
        const selected = btn.getAttribute('data-bs-theme-value');
        setStoredTheme(selected);
        setTheme(selected);
        showActiveTheme(selected);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initThemeSwitcher);
} else {
    initThemeSwitcher();
}
```

---

## 5. Verification Method

1. **Independent Verification Commands**:
   - `php artisan test --filter=SettlementsAndConsoleEnhancementsTest`
     - Validates PDF generation for `daily_financial_report` at `/print_financial_report?date=...`.
   - `php artisan test --filter=ConsultationDetailsModalE2ETest`
     - Validates masterlist view rendering, nested modal structures, and modal accessibility.
   - `npm run build`
     - Validates frontend asset compilation (Vite) with the new theme switcher JS and dropdown Blade components.
2. **File Content Inspection**:
   - `resources/views/printables/financial_report_print.blade.php`: Check that 13 `<th>` and `<td>` elements exist per row, header is `CO-PAY`, standalone copay column is gone, and empty row is `colspan="13"`.
   - `resources/js/pages/doctor/patients.js`: Check that `loadMasterlist()` action column does not contain `.view-consultation-details-btn`.
   - `resources/views/components/navbar.blade.php`: Check that `#theme_switcher_dropdown` is present with Dark, Light, System options.
   - `resources/views/layouts/app.blade.php`: Check inline script in `<head>` and dark mode overrides.
3. **Invalidation Conditions**:
   - If `resources/views/printables/financial_report_print.blade.php` still contains `<th ...>Co-Pay</th>` alongside `CO-PAY`, or `colspan="14"`, the PDF table will be misaligned or broken in DomPDF.
   - If `.view-consultation-details-btn` click handler in `patients.js` (line 1089) is accidentally removed, opening consultation details from the Patient Consultation History modal will fail.
