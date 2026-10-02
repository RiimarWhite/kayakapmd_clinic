# Implementation Plan: Fix Secretary Queue Patient Import & Category 404 Errors

**Document Name:** `fix_secretary_queue_import_and_category_404_plan.md`  
**Location:** `.gemini/implementation plans/fix_secretary_queue_import_and_category_404_plan.md`  
**Date:** 2026-09-30  
**Status:** Awaiting User Approval  

---

## 1. Problem Description & Background

When accessing the Secretary Queue page at `/secretary/queue` or `/admin/secretary`, two critical frontend failures prevent staff from importing patients into the Consultation Form and create browser console errors:

1. **Console Error 1 — Category 404 Not Found on Page Load**:
   ```
   fetchGroupManagementCategory:1 Failed to load resource: the server responded with a status of 404 (Not Found)
   app-PDPVJbXh.js:50 Error fetching categories: Not Found
   ```
   - **Root Cause**: In `resources/js/secretary-management.js`, the `loadServiceCategories()` function executes on page load whenever `#secretaryPage` exists. It makes an AJAX POST request using a relative path: `url: "fetchGroupManagementCategory"`.
   - On nested routes like `/secretary/queue` and `/admin/secretary`, the browser resolves this to `POST /secretary/fetchGroupManagementCategory` or `POST /admin/fetchGroupManagementCategory`. Neither route exists.
   - The actual route is registered in `routes/api.php` line 24 as `POST /api/fetchGroupManagementCategory`.
   - In addition, 17 other AJAX calls in `resources/js/secretary-management.js` and 1 in `resources/js/logout.js` use relative URLs lacking the leading `/api/` prefix, exposing them to identical 404 failures.

2. **Console Error 2 & Broken Patient Import — `Uncaught TypeError: Cannot read properties of undefined (reading 'getInstance')`**:
   ```
   Uncaught TypeError: Cannot read properties of undefined (reading 'getInstance')
       at HTMLButtonElement.<anonymous> (queue-CZ-_dmWa.js:71:1550)
       at HTMLDocument.dispatch (vendor-Xrjdmsh6.js:1:40637)
       at Ve.handle (vendor-Xrjdmsh6.js:1:38554)
   ```
   - **Root Cause**: In `resources/js/app.js`, Bootstrap JS is exported globally as:
     ```javascript
     import { Modal, Dropdown, Tooltip, Popover } from 'bootstrap';
     window.bootstrap = { Modal, Dropdown, Tooltip, Popover };
     ```
     Notice that `Tab` (and `Toast`) are **not imported**, leaving `window.bootstrap.Tab` as `undefined`.
   - In `resources/js/pages/secretary/queue.js`:
     - Line 622 (in `.import-queue` click handler, "Show Info" eye icon on Patient Queue card):
       `bootstrap.Tab.getInstance(consulTabBtn)?.show() || new bootstrap.Tab(consulTabBtn).show();`
     - Line 2252 (in `.btn_px_import` click handler, "Import" button on Patient Masterlist card):
       `bootstrap.Tab.getInstance(consulTabBtn)?.show() || new bootstrap.Tab(consulTabBtn).show();`
     - Line 2231 (in `.btn_px_history` click handler, "History" button):
       `bootstrap.Tab.getInstance(medTabBtn)?.show() || new bootstrap.Tab(medTabBtn).show();`
   - Because `bootstrap.Tab` is `undefined`, accessing `.getInstance` throws `Uncaught TypeError: Cannot read properties of undefined (reading 'getInstance')`.
   - This unhandled exception abruptly halts JavaScript execution **before** the `$.ajax({ url: "/api/fetch_consultation", ... })` call is dispatched.
   - Consequently, clicking "Show Info" on a queue patient or "Import" on a masterlist patient completely fails to load the patient's record into the consultation form.

---

## 2. User Review Required

> [!IMPORTANT]
> - **Zero Schema Changes**: No database migrations or schema alterations are needed. The issue is strictly frontend routing and Bootstrap module exposure.
> - **Asset Recompilation**: After updating the JS source files, Vite assets must be recompiled via `npm run build` so that the production bundles (`public/build/assets/...`) incorporate the fixes.
> - **Backward Compatibility**: Full namespace export `import * as bootstrap from 'bootstrap'` ensures that all Bootstrap 5 components (`Modal`, `Dropdown`, `Tab`, `Toast`, `Tooltip`, `Popover`, `Collapse`, etc.) are globally accessible without breaking existing code.

---

## 3. Proposed Changes

```
========================================================================================
[MODIFY] resources/js/app.js
----------------------------------------------------------------------------------------
Export the entire Bootstrap package (* as bootstrap) onto window.bootstrap instead of a
partial object. This guarantees bootstrap.Tab, bootstrap.Toast, bootstrap.Modal, etc.
are always defined.
========================================================================================

========================================================================================
[MODIFY] resources/js/pages/secretary/queue.js
----------------------------------------------------------------------------------------
1. Implement safe tab switching helper `safeShowTab(selectorOrEl)` with defensive
   fallback to `el.click()` if bootstrap.Tab is absent.
2. Implement safe modal hide helper `safeHideModal(selectorOrEl)` to prevent any
   TypeError on null modal instances.
3. Replace raw `bootstrap.Tab.getInstance(...)` calls with `safeShowTab(...)` in:
   - `.import-queue` (Patient Queue card "Show Info" button)
   - `.btn_px_import` (Patient Masterlist card "Import" button)
   - `.btn_px_history` (Patient Masterlist card "Consultation History" button)
4. Protect modal hide calls in `.import-btn` and `#add_patient_btn`.
========================================================================================

========================================================================================
[MODIFY] resources/js/secretary-management.js
----------------------------------------------------------------------------------------
Prefix all relative AJAX endpoints with `/api/` to prevent 404 errors regardless of the
current URL pathname (/secretary/queue or /admin/secretary):
- fetchGroupManagementCategory -> /api/fetchGroupManagementCategory
- fetch_doctor_info -> /api/fetch_doctor_info
- create_question -> /api/create_question
- fetch_doctor_schedules -> /api/fetch_doctor_schedules
- fetch_schedule_refno -> /api/fetch_schedule_refno
- edit_schedule -> /api/edit_schedule
- delete_schedule -> /api/delete_schedule
- create_doctor_schedules -> /api/create_doctor_schedules
- fetchGroupManagement -> /api/fetchGroupManagement
- createGroupManagement -> /api/createGroupManagement
- editGroupManagement -> /api/editGroupManagement
- deleteGroupManagement -> /api/deleteGroupManagement
- updateGroupManagement -> /api/updateGroupManagement
- fetchDoctorServices -> /api/fetchDoctorServices
- createServicesManagement -> /api/createServicesManagement
- editServiceManagement -> /api/editServiceManagement
- updateServiceManagement -> /api/updateServiceManagement
- deleteServiceManagement -> /api/deleteServiceManagement
========================================================================================

========================================================================================
[MODIFY] resources/js/logout.js
----------------------------------------------------------------------------------------
Prefix `fetch_doctors_from_secretary` with `/api/` (line 6) so doctor selection works
consistently when accessed from `/secretary/queue` or `/admin/secretary`.
========================================================================================

========================================================================================
[NEW] tests/Feature/SecretaryQueueImportTest.php
----------------------------------------------------------------------------------------
Automated tests for:
1. Category fetching: POST /api/fetchGroupManagementCategory returns 200 with categories
   for secretary and admin users.
2. Consultation import: POST /api/fetch_consultation returns 200 with patient details
   when queried by pxrefno, pincode, or consultationrefno.
========================================================================================
```

---

## 4. Detailed Component Modifications & Diffs

### Component A: Bootstrap Global Exposure (`resources/js/app.js`)

#### [MODIFY] `resources/js/app.js`

```diff
-// Bootstrap JS
-import { Modal, Dropdown, Tooltip, Popover } from 'bootstrap';
-window.bootstrap = { Modal, Dropdown, Tooltip, Popover };
+// Bootstrap JS
+// Detailed Comment: Import complete Bootstrap bundle to expose Tab, Modal, Dropdown, Toast, Tooltip, Popover, and Collapse on window.bootstrap
+import * as bootstrap from 'bootstrap';
+window.bootstrap = bootstrap;
```

---

### Component B: Queue Tab & Modal Helper Defenses (`resources/js/pages/secretary/queue.js`)

#### [MODIFY] `resources/js/pages/secretary/queue.js`

1. Add defensive helper functions at the top of the closure:

```javascript
    /**
     * Detailed Comment: Safely activates a Bootstrap tab element.
     * Uses bootstrap.Tab.getOrCreateInstance if available, and gracefully falls back
     * to native element click to prevent uncaught TypeErrors from halting execution.
     */
    function safeShowTab(tabElementOrSelector) {
        const el = typeof tabElementOrSelector === 'string'
            ? document.querySelector(tabElementOrSelector)
            : tabElementOrSelector;
        if (!el) return;
        try {
            if (window.bootstrap && window.bootstrap.Tab) {
                const tabInstance = typeof window.bootstrap.Tab.getOrCreateInstance === 'function'
                    ? window.bootstrap.Tab.getOrCreateInstance(el)
                    : (window.bootstrap.Tab.getInstance(el) || new window.bootstrap.Tab(el));
                if (tabInstance && typeof tabInstance.show === 'function') {
                    tabInstance.show();
                    return;
                }
            }
        } catch (err) {
            console.warn('Bootstrap Tab show warning:', err);
        }
        if (typeof el.click === 'function') {
            el.click();
        }
    }

    /**
     * Detailed Comment: Safely hides a Bootstrap modal without throwing if instance is null.
     */
    function safeHideModal(modalElementOrSelector) {
        const el = typeof modalElementOrSelector === 'string'
            ? document.querySelector(modalElementOrSelector)
            : modalElementOrSelector;
        if (!el) return;
        try {
            if (window.bootstrap && window.bootstrap.Modal) {
                const modalInstance = typeof window.bootstrap.Modal.getOrCreateInstance === 'function'
                    ? window.bootstrap.Modal.getOrCreateInstance(el)
                    : (window.bootstrap.Modal.getInstance(el) || new window.bootstrap.Modal(el));
                if (modalInstance && typeof modalInstance.hide === 'function') {
                    modalInstance.hide();
                    return;
                }
            }
        } catch (err) {
            console.warn('Bootstrap Modal hide warning:', err);
        }
        if (window.jQuery && typeof $(el).modal === 'function') {
            $(el).modal('hide');
        }
    }
```

2. Replace fragile Tab and Modal invocations:
- In `.import-queue` click handler (lines 620-623):
  ```javascript
  // Detailed Comment: Automatically switch to Consultation Details tab safely
  safeShowTab('button[data-bs-target="#consul_info"]');
  ```
- In `.btn_px_history` click handler (lines 2229-2232):
  ```javascript
  // Detailed Comment: Automatically switch to Medical History tab safely
  safeShowTab('#patient_medhistory_tab_btn');
  ```
- In `.btn_px_import` click handler (lines 2250-2253):
  ```javascript
  // Detailed Comment: Automatically switch to Consultation Details tab safely
  safeShowTab('button[data-bs-target="#consul_info"]');
  ```
- In modal hide handlers:
  Use `safeHideModal(document.getElementById('add_patient_modal'))`, `safeHideModal('#reschedule_modal')`, `safeHideModal('#patientMasterlistModal')`.

---

### Component C: Secretary Management API Endpoints (`resources/js/secretary-management.js`)

#### [MODIFY] `resources/js/secretary-management.js`

Update all relative URLs to `/api/...`:
- Line 111: `url: "/api/fetch_doctor_info"`
- Line 186: `url: "/api/create_question"`
- Line 215: `url: "/api/fetch_doctor_schedules"`
- Line 331: `url: "/api/fetch_schedule_refno"`
- Line 373: `url: "/api/edit_schedule"`
- Line 407: `url: "/api/delete_schedule"`
- Line 435: `url: "/api/create_doctor_schedules"`
- Line 466: `url: "/api/fetchGroupManagement"`
- Line 527: `url: "/api/createGroupManagement"`
- Line 572: `url: "/api/editGroupManagement"`
- Line 638: `url: "/api/deleteGroupManagement"`
- Line 723: `url: "/api/updateGroupManagement"`
- Line 798: `url: "/api/fetchDoctorServices"`
- Line 872: `url: "/api/createServicesManagement"`
- Line 933: `url: "/api/editServiceManagement"`
- Line 1014: `url: "/api/updateServiceManagement"`
- Line 1092: `url: "/api/deleteServiceManagement"`
- Line 1140: `url: "/api/fetchGroupManagementCategory"`

---

### Component D: Logout Doctor Selector (`resources/js/logout.js`)

#### [MODIFY] `resources/js/logout.js`

- Line 6: Change `url: "fetch_doctors_from_secretary"` to `url: "/api/fetch_doctors_from_secretary"`

---

### Component E: Frontend Production Asset Rebuild

Run:
```bash
npm run build
```
This updates `public/build/assets/` with the new compiled bundles so both development and production environments reflect the fixes.

---

### Component F: Automated Testing (`tests/Feature/SecretaryQueueImportTest.php`)

#### [NEW] `tests/Feature/SecretaryQueueImportTest.php`

Test coverage:
1. `test_secretary_can_fetch_group_management_categories()`:
   Ensures `POST /api/fetchGroupManagementCategory` responds with 200 and a JSON structure containing `groupManagement`.
2. `test_admin_can_fetch_group_management_categories()`:
   Ensures admin role can also fetch categories.
3. `test_secretary_can_fetch_consultation_by_pxrefno()`:
   Verifies `POST /api/fetch_consultation` returns 200 with patient details and pre-fills demographic data.
4. `test_secretary_can_fetch_consultation_fallback_to_pxmasterlist()`:
   Verifies patient masterlist fallback when importing unscheduled patients.

---

## 5. Verification Plan

### Automated Verification
Run the new and existing test suites inside the Docker container:
```bash
docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan test --filter=SecretaryQueueImportTest
docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan test
```

### Build Verification
Execute the frontend production build:
```powershell
npm run build
```
Verify that the build exits with code 0 and outputs fresh bundles in `public/build/assets/`.

### Manual Browser Verification
1. Navigate to `http://localhost:10000/secretary/queue` (or `/kayakapmd_clinic/secretary/queue`).
2. Open Browser DevTools Console.
3. Verify that on page load, `fetchGroupManagementCategory` returns HTTP 200 OK and NO `404 Not Found` or `Error fetching categories` error appears.
4. Click the **"Show Info"** (eye icon) button on any patient in the Patient Queue card.
   - Verify NO `Cannot read properties of undefined (reading 'getInstance')` error occurs.
   - Verify the Consultation Details tab activates.
   - Verify the patient's information and photo load cleanly into the Consultation Form.
5. In the **Patient Masterlist** card (left column), click the **"Import"** button (`.btn_px_import`) on any patient record.
   - Verify NO console errors appear.
   - Verify the Consultation Details tab activates.
   - Verify the patient data is imported into the form and the success toast appears.
6. In the **Patient Masterlist** card, click the **"History"** button (`.btn_px_history`).
   - Verify the Medical History tab activates and historical records load cleanly.
7. Repeat on `http://localhost:10000/admin/secretary` (or `/kayakapmd_clinic/admin/secretary`) to ensure identical behavior for admin users.
