# Implementation Plan: Fix Charges Display on Payment Details in Secretary Console

## 1. Context & Problem Statement
On the Secretary Console (both Secretary Queue and Admin Secretary views), navigating to the **Payment Details** tab fails to show the current consultation charges in the `#pxcharges_table` DataTable. 

### Root Causes:
1. **Uncaught TypeError in DataTables Row Renderer**:
   In [`resources/js/pages/secretary/queue.js`](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/js/pages/secretary/queue.js), column 3 (Unit Price) and column 0 (Actions) are configured with `data: null`. When `data: null`, DataTables passes `data = null` to the `render` callback. The callback attempted `data.cost_ave` and `data.id`, throwing `Uncaught TypeError: Cannot read properties of null (reading 'cost_ave')`. This exception crashes DataTables mid-rendering and aborts table generation.
2. **Column Count Mismatch on Empty Initialization (`!refno`)**:
   When no consultation reference is selected, `loadPatientCharges()` initialized DataTables with only 4 column definitions, whereas the HTML `<thead>` in [`queue.blade.php`](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/views/pages/secretary/queue.blade.php) contains 5 `<th>` columns (Actions, Description, Quantity, Unit Price, Amount). DataTables 2 throws an `Incorrect column count` warning and corrupts table structure.
3. **DataTables Configuration Syntax & Sorting on Non-Orderable Column**:
   In `columnDefs`, `target` (singular) was used instead of `targets`. `order: [[1, 'asc']]` was set on column index 1 despite column 1 being marked `orderable: false`.
4. **Duplicate Initialization & Tab Lifecycle Race Conditions**:
   `loadPatientCharges()` was bound to both `#patient_charges_btn` `click` (firing when the tab pane is still hidden with `display: none`) and `shown.bs.tab`. Calling `DataTable().clear().destroy()` repeatedly caused race conditions.
5. **Legacy Secretary View (`secretary.blade.php` & `secretary.js`)**:
   [`resources/js/secretary.js`](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/js/secretary.js) used relative URL `fetch_pxcharges` (missing `/api/` prefix), referenced nonexistent `charge.net_total` and `servicename`, and [`resources/views/secretary.blade.php`](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/views/secretary.blade.php) still had 4 columns with `Discount` instead of `Quantity` and `Unit Price`.
6. **Backend Guard Consistency**:
   In `DoctorController::fetchPatientCharges`, an empty `consultationrefno` performed database queries on empty keys instead of immediately returning `['charges' => []]`.

---

## 2. Proposed Changes

### Component A: `resources/js/pages/secretary/queue.js`
- Fix DataTables column render callbacks to use `(data, type, row)` safely:
  - Column 0: Extract ID from `row` (`(row && (row.id || row.pxchargerefno)) || ''`).
  - Column 3: Extract unit price from `row` (`parseFloat(data || (row && (row.cost_ave || row.sellingprice || row.current_price)) || 0)`).
  - Column 4: Extract amount from `row` (`parseFloat(data || (row && row.totalamt) || 0)`).
- Fix `if (!refno)` block: Provide all 5 column definitions or cleanly clear the table without column count mismatch.
- Support `table.ajax.reload()` when already initialized, with `ajax.data` as a dynamic callback function `function(d) { d.consultationrefno = ... }`.
- In `shown.bs.tab`, call `table.columns.adjust().draw(false)` once visible. Remove redundant click listener.

### Component B: `app/Http/Controllers/DoctorController.php` & `routes/api.php`
- In `DoctorController::fetchPatientCharges`: If `$request->consultationrefno` is empty, return `response()->json(['charges' => []])` immediately.
- In `routes/api.php`: Ensure both `fetch_pxcharges` and `fetch_patient_charges` are accessible across `auth:secretary,doctor,admin`.

### Component C: `resources/views/secretary.blade.php` & `resources/js/secretary.js`
- Update table headers in `secretary.blade.php` to 5 columns (`Actions`, `Description`, `Quantity`, `Unit Price`, `Amount`).
- Update `secretary.js` to call `/api/fetch_pxcharges`, calculate total from `c.totalamt`, and render 5 columns safely.

---

## 3. Verification Plan
- `npm run build`: Confirm clean compilation of assets.
- `php artisan test`: Run test suite to guarantee 100% pass rate.
- Manual logic verification of charges table render.
