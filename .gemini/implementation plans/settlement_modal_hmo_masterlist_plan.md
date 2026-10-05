# Implementation Plan: HMO Dropdown on Settlement Modal from hmo_masterlist

## Objective
Ensure the HMO dropdown (`#hmo_type`) on the consultation settlement modal retrieves and displays options directly from the `hmo_masterlist` database table across Blade pre-rendering, AJAX API retrieval, and View Settlements population.

## Affected Components
1. **Blade Modal View (`resources/views/modals/settlement_modal.blade.php`)**:
   - Query `\App\Models\HMOModel` (which maps to `hmo_masterlist`) directly in the Blade component to pre-render `<option>` tags.
   - Pre-rendering eliminates asynchronous race conditions when loading existing consultation settlements.
2. **Admin Consultation Settlements View (`resources/views/pages/admin/consultations/settlements.blade.php`)**:
   - Update `add_stl_hmo_type` and `edit_stl_hmo_type` dropdowns to populate from `hmo_masterlist`.
3. **API Controllers (`ConsultationController.php` & `SecretaryController.php`)**:
   - Ensure `/api/fetch_hmo` selects from `HMOModel` (`hmo_masterlist`) ordered by `hmoname` ASC with defensive fallback.
   - In `SecretaryController::saveSettlements`, look up the selected `hmocode` in `HMOModel` to also populate `hmoname` in `pxsettlements`.
4. **Client-Side Scripts (`queue.js` & `secretary.js`)**:
   - Handle asynchronous option population safely, preserving preselected consultation HMO without wiping out values.
   - Update `populateViewSettlements` to resolve HMO display name from the `hmo_masterlist` options.
5. **Automated Verification**:
   - Update tests in `tests/Feature/ConsultationChargesAndSettlementsTest.php` to verify HMO dropdown population from `hmo_masterlist`.
   - Run complete test suite (`php artisan test`) and compile assets (`npm run build`).
