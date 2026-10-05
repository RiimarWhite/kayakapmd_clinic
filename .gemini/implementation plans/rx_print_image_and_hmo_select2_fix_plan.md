# Implementation Plan: Fix Rx Printable Image '?' Glyph and HMO Searchable Dropdown on Settlement Modal

## Goal Description
Resolve two reported frontend/reporting defects:
1. **Rx Printable '?' Mark**: The Rx prescription printable PDF displays a literal question mark (`?`) instead of the stylized Rx symbol. This occurs because the template rendered the Unicode character entity `&#8478;` (`℞`, U+211E), which is absent in Dompdf's standard core font glyph set (Helvetica / Times-Roman ISO-8859-1).
2. **HMO Searchable Dropdown Inoperability**: The HMO dropdown on the settlement modal from the secretary pages (used by both Admin and Secretary users) is unable to display or function as a searchable Select2 dropdown. This is caused by an ID casing mismatch (`#settlementModal` vs `$("#settlement_modal")`), backdrop z-index conflicts, and Bootstrap's modal `tabindex="-1"` focus-trapping.

---

## User Review Required
> [!NOTE]
> - The modal ID in `resources/views/modals/settlement_modal.blade.php` will be standardized to `id="settlement_modal"` (snake_case) matching the rest of the KayakapMD modals. Corresponding button `data-bs-target` attributes in `queue.blade.php` and `secretary.blade.php` will be updated to `#settlement_modal`.
> - A high-resolution blue Rx icon (`rx_icon_blue.png`, `#286aa0`) will be generated in `public/images/` to match the prescription stationery styling. If the image file is ever absent, the template will fall back to literal Latin text `Rx` (ASCII), completely preventing `?` glyph errors.

---

## Proposed Changes

### Component 1: Rx Printable Symbol Fix

#### [NEW] [public/images/rx_icon_blue.png](file:///C:/xampp/htdocs/kayakapmd_clinic/public/images/rx_icon_blue.png)
- Generate a theme-matching blue (`#286aa0`) version of the existing `public/images/rx_icon.png` using PHP GD image processing.

#### [MODIFY] [resources/views/printables/rx_print.blade.php](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/views/printables/rx_print.blade.php)
- Replace `<div class="rx-symbol">&#8478;</div>` with a robust image tag:
  ```html
  <td style="vertical-align: top; width: 50px;">
      @php
          $rxImgPath = file_exists(public_path('images/rx_icon_blue.png'))
              ? public_path('images/rx_icon_blue.png')
              : (file_exists(public_path('images/rx_icon.png')) ? public_path('images/rx_icon.png') : null);
      @endphp
      @if ($rxImgPath)
          <img src="{{ $rxImgPath }}" style="width: 44px; height: auto;" alt="Rx">
      @else
          <div style="font-size: 32px; font-weight: bold; font-family: 'Times New Roman', serif; color: #286aa0; line-height: 1;">Rx</div>
      @endif
  </td>
  ```
- Retain the clean column format: `{name/description} newline Sig: {instructions}`, with general instructions positioned at the bottommost section.

---

### Component 2: Settlement Modal HMO Select2 Fix

#### [MODIFY] [resources/views/modals/settlement_modal.blade.php](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/views/modals/settlement_modal.blade.php)
- Change `<div class="modal fade" data-bs-backdrop="static" id="settlementModal" tabindex="-1">` to:
  `<div class="modal fade" data-bs-backdrop="static" id="settlement_modal">` (removing `tabindex="-1"` to prevent Bootstrap focus-trapping Select2 search inputs).
- Ensure the container around `#hmo_type` has `style="min-width: 0;"` so the Select2 widget never collapses inside the flex input group:
  ```html
  <div class="input-group input-group-sm flex-nowrap">
      <span class="input-group-text fw-bold">HMO</span>
      <div class="flex-grow-1" style="min-width: 0;">
          <select class="form-select form-select-sm w-100" name="hmo_type" id="hmo_type">
              ...
          </select>
      </div>
  </div>
  ```

#### [MODIFY] [resources/views/pages/secretary/queue.blade.php](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/views/pages/secretary/queue.blade.php)
- Update button `data-bs-target="#settlementModal"` to `data-bs-target="#settlement_modal"`.

#### [MODIFY] [resources/views/secretary.blade.php](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/views/secretary.blade.php)
- Update button `data-bs-target="#settlementModal"` to `data-bs-target="#settlement_modal"`.

#### [MODIFY] [resources/views/layouts/app.blade.php](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/views/layouts/app.blade.php)
- Add global Select2 dropdown z-index rule ensuring Select2 dropdowns always render in front of modals and backdrops:
  ```css
  .select2-container {
      width: 100% !important;
  }
  .select2-dropdown {
      z-index: 9999 !important;
  }
  ```

#### [MODIFY] [resources/js/pages/secretary/queue.js](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/js/pages/secretary/queue.js)
- Fix Select2 initialization for `#hmo_type`:
  - Hook into `$('#settlement_modal').on('shown.bs.modal', ...)` to guarantee Select2 is initialized/refreshed when the modal is fully visible.
  - Set `dropdownParent: $('#settlement_modal')` and `width: '100%'`.
  - Re-trigger change to update preselected values without layout jitter.

#### [MODIFY] [resources/js/secretary.js](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/js/secretary.js)
- Fix Select2 initialization for `#hmo_type` on `#settlement_modal` with `dropdownParent: $('#settlement_modal')` and `width: '100%'`.

#### [MODIFY] [resources/js/pages/admin/consultations/settlements.js](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/js/pages/admin/consultations/settlements.js)
- Ensure `#add_stl_hmo_type` and `#edit_stl_hmo_type` initialize Select2 on `shown.bs.modal` of their respective modals (`#add_settlement_modal`, `#edit_settlement_modal`) so width calculations are accurate.

---

## Verification Plan

### Automated Tests
1. **PHP Feature Tests**:
   - Run `php artisan test tests/Feature/ConsultationChargesAndSettlementsTest.php` to verify PDF generation and settlement calculations.
   - Run complete suite `php artisan test` (all 81+ tests must pass).
2. **Frontend Build**:
   - Run `cmd /c npm run build` to verify Vite asset compilation.

### Manual Verification
1. **Rx Printable Check**:
   - Navigate to Doctor Consultation or print `/print_pdf?consultationrefno=...&type=rx`.
   - Verify the Rx symbol displays as a medical Rx mark in blue without any `?` symbol.
2. **Secretary Queue Settlement Modal Check**:
   - Access `secretary/queue` (as Secretary or Admin).
   - Select a patient and click **Settlements**.
   - Verify the HMO field renders as a searchable Select2 dropdown.
   - Click the dropdown and type search characters (e.g., "Maxicare" or "Intellicare"). Verify typing is accepted and dropdown filters the options.
3. **Standalone Secretary Page Check**:
   - Access `secretary`.
   - Click **Settlements** and confirm the HMO dropdown is searchable and selects properly.
