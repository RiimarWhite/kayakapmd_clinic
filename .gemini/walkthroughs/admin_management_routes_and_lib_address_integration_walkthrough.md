# Walkthrough: Admin Management Routes & PSGC Address Integration

## 1. Overview
This walkthrough documents the end-to-end development, integration, and verification of:
1. **5 Admin Management Page Routes**:
   - `admin/consultations/billing` (`resources/views/pages/admin/consultations/billing.blade.php`, `resources/js/pages/admin/consultations/billing.js`)
   - `admin/consultations/settlements` (`resources/views/pages/admin/consultations/settlements.blade.php`, `resources/js/pages/admin/consultations/settlements.js`)
   - `admin/hmo` (`resources/views/pages/admin/hmo.blade.php`, `resources/js/pages/admin/hmo.js`)
   - `admin/utilities/chargecat` (`resources/views/pages/admin/utilities/chargecat.blade.php`, `resources/js/pages/admin/utilities/chargecat.js`)
   - `admin/utilities/diagcat` (`resources/views/pages/admin/utilities/diagcat.blade.php`, `resources/js/pages/admin/utilities/diagcat.js`)
2. **Column Filters & Sorting Dropdown Modals on Each Column**:
   - Built reusable helper `resources/js/helpers/table-column-filter.js` providing Sort Ascending, Sort Descending, Reset Sort, free-text search inputs, picklist filters, active filter counters, and event isolation from DataTables header clicks.
3. **Add/Edit/Delete Modals with Loaders & SweetAlert2 Dialogs**:
   - Modal forms for creating and editing records based on exact database fields.
   - Real-time arithmetic calculation in billing (`total = qty * retail`, `net_total = total - discount`) and settlements (`gross = pf + meds + lab + others`, `net_payable = gross - deductions`).
   - Button spinners and disabled states during asynchronous operations.
   - SweetAlert2 confirmation dialogs before destructive deletions.
4. **PSGC Address Reference Tables Integration (`lib_region`, `lib_province`, `lib_municipality`, `lib_barangay`, `lib_zipcode`)**:
   - Created reusable cascading helper `resources/js/helpers/address-cascade.js`.
   - Integrated into:
     - **Patient Masterlist**: `resources/views/modals/add_patient.blade.php`, `resources/views/modals/edit_patient.blade.php`, `resources/js/pages/doctor/patients.js`, `resources/js/pages/secretary/queue.js`.
     - **Secretary Management**: `resources/views/pages/admin/users/secretaries.blade.php`, `resources/views/modals/edit_secretary.blade.php`, `resources/js/pages/admin/users/secretaries.js`.
     - **Doctor Management**: `resources/views/modals/add_doctor.blade.php`, `resources/views/modals/edit_doctor.blade.php`, `resources/js/pages/admin/users/doctors.js`.
     - **Admin Company Profile**: `resources/views/pages/admin/profile.blade.php`, `resources/js/pages/admin/profile.js`.

---

## 2. Changes Summary

### A. Database Models & Eloquent Definitions
- [`app/Models/PxChargesModel.php`](file:///C:/docker/php_projects/kayakapmd_clinic/app/Models/PxChargesModel.php): Created Eloquent model mapping to `pxcharges` table with complete fillable attributes.
- [`app/Models/DiagnosticsCategoryModel.php`](file:///C:/docker/php_projects/kayakapmd_clinic/app/Models/DiagnosticsCategoryModel.php): Verified mapping to `diagnostic_category`.
- [`app/Models/SettlementsModel.php`](file:///C:/docker/php_projects/kayakapmd_clinic/app/Models/SettlementsModel.php): Configured with virtual accessors and mutators (`net_total`, `cash`, `cta`, `hmo`, `phic`) preserving backward compatibility.
- [`app/Models/KayakapProfileModel.php`](file:///C:/docker/php_projects/kayakapmd_clinic/app/Models/KayakapProfileModel.php): Mapped to `kayakapmd_profile` for hospital metadata and PSGC codes (`HOSP_ADDREG`, `HOSP_ADDPROV`, `HOSP_ADDMUN`, `HOSP_ADDBRGY`, `HOSP_ADDZIPCODE`).

### B. Backend Controller & Routing
- [`app/Http/Controllers/ManagementController.php`](file:///C:/docker/php_projects/kayakapmd_clinic/app/Http/Controllers/ManagementController.php):
  - Added PSGC geographic cascading endpoints: `getRegions()`, `getProvinces()`, `getMunicipalities()`, `getBarangays()`, `getZipcode()`.
  - Added Diagnostic Category edit method: `editDiagnosticCategory()`.
  - Updated `saveDiagnosticCategory()` and `deleteDiagnosticCategory()` with dual field alias support.
  - Added HMO CRUD: `fetchAllHmo()`, `addHmo()`, `editHmo()`, `deleteHmo()`.
  - Added Consultation Billing CRUD: `fetchActiveConsultations()`, `fetchAdminBillings()`, `addAdminBilling()`, `editAdminBilling()`, `deleteAdminBilling()`.
  - Added Consultation Settlements CRUD: `fetchAdminSettlements()`, `addAdminSettlement()`, `editAdminSettlement()`, `deleteAdminSettlement()`.
  - Updated `loadCompanyProfile()` and `updateProfile()` with direct record updates and PSGC columns.
- [`routes/api.php`](file:///C:/docker/php_projects/kayakapmd_clinic/routes/api.php):
  - Registered `/api/address/*` routes under shared `auth:secretary,doctor,admin` group.
  - Registered HMO, Diagnostic Category, Charge Category, Billing, and Settlements routes under `auth:admin`.

### C. Frontend Helpers
- [`resources/js/helpers/address-cascade.js`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/js/helpers/address-cascade.js): Asynchronous cascading fetch for Region -> Province -> Municipality -> Barangay -> Zipcode, automatic composite address generation, and programmatic setter for Edit modals.
- [`resources/js/helpers/table-column-filter.js`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/js/helpers/table-column-filter.js): Header dropdown modal renderer and event interceptor supporting Sort Asc/Desc/Reset, search queries, picklist checkbox filters, and active filter badges.

### D. Views and Page Controllers
- **Charge Categories (`admin/utilities/chargecat`)**:
  - Blade: [`resources/views/pages/admin/utilities/chargecat.blade.php`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/views/pages/admin/utilities/chargecat.blade.php)
  - JS: [`resources/js/pages/admin/utilities/chargecat.js`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/js/pages/admin/utilities/chargecat.js)
- **Diagnostic Categories (`admin/utilities/diagcat`)**:
  - Blade: [`resources/views/pages/admin/utilities/diagcat.blade.php`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/views/pages/admin/utilities/diagcat.blade.php)
  - JS: [`resources/js/pages/admin/utilities/diagcat.js`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/js/pages/admin/utilities/diagcat.js)
- **HMO Masterlist (`admin/hmo`)**:
  - Blade: [`resources/views/pages/admin/hmo.blade.php`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/views/pages/admin/hmo.blade.php)
  - JS: [`resources/js/pages/admin/hmo.js`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/js/pages/admin/hmo.js)
- **Consultations Billing (`admin/consultations/billing`)**:
  - Blade: [`resources/views/pages/admin/consultations/billing.blade.php`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/views/pages/admin/consultations/billing.blade.php)
  - JS: [`resources/js/pages/admin/consultations/billing.js`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/js/pages/admin/consultations/billing.js)
- **Consultations Settlements (`admin/consultations/settlements`)**:
  - Blade: [`resources/views/pages/admin/consultations/settlements.blade.php`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/views/pages/admin/consultations/settlements.blade.php)
  - JS: [`resources/js/pages/admin/consultations/settlements.js`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/js/pages/admin/consultations/settlements.js)
- **Vite Configuration**:
  - Registered all new entry points in [`vite.config.js`](file:///C:/docker/php_projects/kayakapmd_clinic/vite.config.js) and built bundles via `npm run build`.

---

## 3. Verification Results

### 1. Frontend Asset Compilation
```bash
cmd.exe /c "npm run build"
```
**Result**: Build succeeded in 3.25s. All bundles generated:
- `billing-CGsMLH7O.js` (9.19 kB)
- `settlements-scCxGdL3.js` (10.40 kB)
- `hmo-BL6FYjvi.js` (6.02 kB)
- `chargecat-N-eomxYi.js` (4.94 kB)
- `diagcat-DvPm-M6J.js` (4.92 kB)
- `address-cascade-dbOZLV6D.js` (6.01 kB)
- `table-column-filter-Cn0U9a5h.js` (6.32 kB)

### 2. Feature Test Suite Execution
```bash
docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan test tests/Feature/AdminManagementAndAddressIntegrationTest.php
```
**Result**:
```
PASS  Tests\Feature\AdminManagementAndAddressIntegrationTest
✓ psgc address cascading endpoints                                     7.99s  
✓ company profile load and update with address                         0.19s  
✓ hmo masterlist crud                                                  0.29s  
✓ diagnostic category crud                                             0.17s  
✓ charge category crud                                                 0.16s  
✓ consultations billing crud                                           0.17s  
✓ consultations settlements crud                                       0.18s  

Tests:    7 passed (76 assertions)
Duration: 10.39s
```

### 3. Full Repository Test Suite Execution
```bash
docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan test
```
**Result**:
```
PASS  Tests\Unit\ExampleTest
PASS  Tests\Feature\AdminManagementAndAddressIntegrationTest
PASS  Tests\Feature\AuthTest
PASS  Tests\Feature\ConsultationChargesAndSettlementsTest
PASS  Tests\Feature\DoctorSecretaryConsoleTest
PASS  Tests\Feature\ExampleTest
PASS  Tests\Feature\OpdConsultationWorkflowTest
PASS  Tests\Feature\PatientManagementAndPrintFixesTest

Tests:    57 passed (361 assertions)
Duration: 79.72s
```

### 4. Graft Repository Context Graph
```bash
cmd.exe /c "graft build"
```
**Result**: Graph refreshed with 2,437 nodes, 4,109 edges, 503 cards.
