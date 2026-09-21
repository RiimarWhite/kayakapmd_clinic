# Implementation Plan: Server-Side DataTables & Users Masterlist Column Filters

Transition the 5 admin management tables (`admin/consultations/billing`, `admin/consultations/settlements`, `admin/hmo`, `admin/utilities/chargecat`, `admin/utilities/diagcat`) and the Users masterlist (`admin/users/secretaries-admins`) to server-side DataTables with robust server-side ordering, searching, and column-specific filtering dropdown modals.

---

## 1. Goal Description
The user identified an issue where column filters and ordering dropdown modals are not functioning properly because the tables on:
- `admin/consultations/billing`
- `admin/consultations/settlements`
- `admin/hmo`
- `admin/utilities/chargecat`
- `admin/utilities/diagcat`
are currently client-side (`serverSide: false` / unpaged raw array payloads) rather than server-side. Additionally, the Users masterlist on `/admin/users/secretaries-admins` currently lacks column filter dropdown modals and has `ordering: false` hardcoded.

This change accomplishes:
1. **Server-Side DataTables Processing**: Converting all 5 admin tables and the Users masterlist to `serverSide: true`.
2. **Backend Server-Side Query Engine**: Upgrading `ManagementController` endpoints (`fetchAdminBillings`, `fetchAdminSettlements`, `fetchAllHmo`, `fetchChargeCategories`, `fetchDiagnosticCategory`, and `fetchSecretaries`) to handle:
   - DataTables `draw`, `start`, `length`, `recordsTotal`, and `recordsFiltered`.
   - Directional sorting (`order[0][column]`, `order[0][dir]`) mapped to database columns.
   - Global searching (`search.value`).
   - Column-specific filtering (`columns[i][search][value]`) supporting both text queries and regex picklists (e.g. `^(HMO|COMPANY)$`).
   - Retaining backward-compatibility for legacy callers and existing test suites (e.g., returning keys `hmos`, `categories`, `secretaries`, `data`).
3. **Fixed Event Handling in Dropdown Helper**: Updating `resources/js/helpers/table-column-filter.js` to eliminate event bubbling conflicts between Bootstrap 5's delegated `document` dropdown triggers and DataTables `<th>` sorting handlers, and configuring Popper `fixed` strategy so dropdowns never clip inside `.table-responsive`.
4. **Users Masterlist Filter Integration**: Adding column filter dropdown headers across all relevant columns on `/admin/users/secretaries-admins` (Account Type, Source Table, Username, Full Name, Contact #, Email Address) and enabling server-side ordering and filtering.

---

## 2. User Review Required
> [!IMPORTANT]
> - All endpoints will continue to return their legacy keys (`hmos`, `categories`, `secretaries`, `data`) in addition to DataTables-compliant `draw`, `recordsTotal`, `recordsFiltered`, and `data` pagination objects so existing unit tests (e.g., `DoctorSecretaryConsoleTest`) and secondary consumers continue operating with zero breaking changes.
> - On `/admin/users/secretaries-admins`, the existing Account Type dropdown filter will be modernized into the unified column filter format, allowing seamless filtering by Secretary, Admin, or All Accounts, alongside column-level sorting and text searches on Username, Full Name, Contact, and Email.

---

## 3. Open Questions
None. Requirements, schema constraints, and DataTables protocols are fully aligned with the codebase architecture.

---

## 4. Proposed Changes

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                            ARCHITECTURAL FLOW                               │
│                                                                             │
│  [Blade View] ──(Header <th>)──► [table-column-filter.js]                   │
│        │                               │ (Popper fixed / prevent DT sort)   │
│        ▼                               ▼                                    │
│  [DataTables JS] ──────────(serverSide: true)──────────────────────────┐    │
│   - billing.js                                                         │    │
│   - settlements.js                                                     │    │
│   - hmo.js                                                             │    │
│   - chargecat.js                                                       │    │
│   - diagcat.js                                                         │    │
│   - secretaries.js                                                     │    │
│        │                                                               │    │
│        ▼ (AJAX POST: draw, start, length, order, search, columns)      │    │
│  [routes/api.php]                                                      │    │
│        │                                                               │    │
│        ▼                                                               │    │
│  [ManagementController.php]                                            │    │
│   - fetchAdminBillings()      - fetchAllHmo()            - fetchSecretaries()│
│   - fetchAdminSettlements()   - fetchChargeCategories()  - fetchDiagnosticCategory()
│   ├── applyFiltersAndSorting() helper                                       │
│   └── returns { draw, recordsTotal, recordsFiltered, data, legacy_keys }    │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

### Component 1: Frontend Column Filter Dropdown Helper

#### [MODIFY] `resources/js/helpers/table-column-filter.js`
- Prevent DataTables `th` default click sorting specifically when clicking the filter button or menu, while allowing the click to bubble to Bootstrap's dropdown listeners.
- Use Bootstrap 5 `data-bs-popper-config='{"strategy":"fixed"}'` on the dropdown menu so it is never clipped by `.table-responsive`.
- When Asc, Desc, or Clear Sort is clicked, trigger `dataTable.order([colIdx, dir]).draw()`.
- When Filter Apply is clicked, trigger `dataTable.column(colIdx).search(filterValue).draw()`.
- When Filter Clear is clicked, reset inputs and trigger `dataTable.column(colIdx).search('').draw()`.

---

### Component 2: Backend Controllers & DataTables Query Engine

#### [MODIFY] `app/Http/Controllers/ManagementController.php`
Add a robust reusable helper method or logic to apply DataTables server-side queries to Eloquent / Query Builder:
1. **`fetchAdminBillings(Request $request)`**:
   - Total records count on `pxcharges`.
   - Filter by `dw_clientcode`.
   - Apply global search across `pxname`, `consultationrefno`, `servicename`, `group_category`, `payment_type`, `px_pin`.
   - Apply column searches (`date`, `consultationrefno`, `pxname`, `servicename`, `group_category`, `payment_type`, `total`, `discount`, `net_total`).
   - Apply column ordering mapped from column index 1–9.
   - Paginate with `skip($start)->take($length)` and return `{ draw, recordsTotal, recordsFiltered, data }`.

2. **`fetchAdminSettlements(Request $request)`**:
   - Total records count on `pxsettlements`.
   - Apply global search across `consultationrefno`, `docname`, `docrefno`, `px_pin`.
   - Apply column searches (`created`, `consultationrefno`, `docname`, `total_gross`, `cash`, `cta`, `hmo`, `phic`, `net_payable`).
   - Apply column ordering mapped from column index 1–9.
   - Paginate with `skip($start)->take($length)` and return `{ draw, recordsTotal, recordsFiltered, data }`.

3. **`fetchAllHmo(Request $request)`**:
   - Total records count on `hmo`.
   - Filter by `dw_clientcode` with fallback.
   - Apply global search across `hmocode`, `hmoname`, `hmotype`, `accre_no`, `hmoaddress`.
   - Apply column searches (`hmocode`, `hmoname`, `hmotype`, `accre_no`, `hmoaddress`).
   - Apply column ordering mapped from column index 1–5.
   - Paginate with `skip($start)->take($length)` and return `{ draw, recordsTotal, recordsFiltered, data, hmos: $data, hmo: $data }`.

4. **`fetchChargeCategories(Request $request)`**:
   - If `$request->has('draw')`, apply server-side search, order (`categoryrefno`, `categoryname`), pagination, and return `{ draw, recordsTotal, recordsFiltered, data, categories: $data }`. If no draw, return all for backwards compatibility.

5. **`fetchDiagnosticCategory(Request $request)`**:
   - If `$request->has('draw')`, apply server-side search, order (`category_refno`, `category_name`), pagination, and return `{ draw, recordsTotal, recordsFiltered, data, categories: $data }`. If no draw, return all for backwards compatibility.

6. **`fetchSecretaries(Request $request)`**:
   - Fetch secretaries (`SecretaryModel`) and admins (`AdminModel`).
   - Merge collection.
   - Apply `account_type` filter (`Secretary`, `Admin`, or all).
   - Apply global search across `username`, `secfname`, `seclname`, `seccontactno`, `secemail`, `source_table`, `account_type`.
   - Apply column-specific filters across columns 1 to 6.
   - Apply ordering by selected column (`account_type`, `source_table`, `username`, `fullname`, `seccontactno`, `secemail`).
   - If `$request->has('draw')`, paginate slice `slice($start, $length)` and return `{ draw, recordsTotal, recordsFiltered, data: $paged, secretaries: $paged, success: true }`.

---

### Component 3: Frontend DataTables Configurations

#### [MODIFY] `resources/js/pages/admin/consultations/billing.js`
- Configure DataTables with `serverSide: true`, `processing: true`.
- Update `ajax` configuration to standard DataTables format.
- Ensure column headers bind to `renderColumnFilterHeader` and `initTableColumnFilters`.

#### [MODIFY] `resources/js/pages/admin/consultations/settlements.js`
- Configure DataTables with `serverSide: true`, `processing: true`.
- Ensure column headers bind to `renderColumnFilterHeader` and `initTableColumnFilters`.

#### [MODIFY] `resources/js/pages/admin/hmo.js`
- Configure DataTables with `serverSide: true`, `processing: true`.
- Ensure column headers bind to `renderColumnFilterHeader` and `initTableColumnFilters`.

#### [MODIFY] `resources/js/pages/admin/utilities/chargecat.js`
- Configure DataTables with `serverSide: true`, `processing: true`.
- Ensure column headers bind to `renderColumnFilterHeader` and `initTableColumnFilters`.

#### [MODIFY] `resources/js/pages/admin/utilities/diagcat.js`
- Configure DataTables with `serverSide: true`, `processing: true`.
- Ensure column headers bind to `renderColumnFilterHeader` and `initTableColumnFilters`.

#### [MODIFY] `resources/views/pages/admin/users/secretaries.blade.php` & `resources/js/pages/admin/users/secretaries.js`
- In Blade view, assign IDs to the `<th>` tags:
  - `th_sec_account_type`: Account Type (picklist: `['Secretary', 'Admin']`)
  - `th_sec_source_table`: Source Table (picklist: `['secretaryrights', 'adminrights']`)
  - `th_sec_username`: Username
  - `th_sec_fullname`: Full Name
  - `th_sec_contact`: Contact #
  - `th_sec_email`: Email Address
- In JavaScript:
  - Initialize column filter headers via `renderColumnFilterHeader`.
  - Set `serverSide: true`, `processing: true`, and `ordering: true`.
  - Attach `initTableColumnFilters(secretaryTable, '#secretary_table')`.

---

### Component 4: Build & Test Suite Verification

#### [MODIFY] `tests/Feature/AdminManagementAndAddressIntegrationTest.php`
- Add assertions testing DataTables server-side request payloads (`draw`, `start`, `length`, `order`, `search`, column filters) across:
  - `/api/admin/fetch_billings`
  - `/api/admin/fetch_settlements`
  - `/api/admin/fetch_hmo`
  - `/api/fetch_charge_categories`
  - `/api/fetch_diagnostic_category`
  - `/api/fetch_secretaries`
- Verify correct filtered count and paginated data slicing.

---

## 5. Verification Plan

### Automated Tests
Execute feature tests in Docker container `latest_php_server`:
```powershell
docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan test --filter=AdminManagementAndAddressIntegrationTest
docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan test --filter=DoctorSecretaryConsoleTest
docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan test
```

### Frontend Asset Compilation
```powershell
cmd.exe /c "npm run build"
cmd.exe /c "graft build"
```

### Manual Verification
1. Navigate to `admin/hmo`:
   - Open column filter on "HMO Name", enter search string, click Apply -> table reloads server-side with matching records.
   - Click "Sort Descending" -> table orders by HMO Name descending server-side.
   - Click "Clear" -> filter/sort resets.
2. Navigate to `admin/consultations/billing` and `admin/consultations/settlements`:
   - Verify column filtering by Payment Type / Consultation Ref / Patient Name works server-side with accurate page pagination.
3. Navigate to `admin/users/secretaries-admins`:
   - Verify column filtering on Account Type (`Secretary` / `Admin`) and text search on Username / Full Name works seamlessly.
