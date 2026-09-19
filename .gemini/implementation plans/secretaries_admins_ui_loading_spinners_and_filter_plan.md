# Implementation Plan: Secretaries/Admin Users UI Enhancements (Loading Spinners, Labeled Actions & Column Dropdown Filter)

This plan outlines the design and implementation for upgrading the **Secretaries/Admin Users** management interface ([`resources/views/pages/admin/users/secretaries.blade.php`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/views/pages/admin/users/secretaries.blade.php) and [`resources/js/pages/admin/users/secretaries.js`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/js/pages/admin/users/secretaries.js)) to provide clear visual feedback with loading spinners, expanded labeled action buttons, and an inline column header filter dropdown menu showing the active filter label.

---

## 1. Goal Description

1. **Loading Spinners**:
   - Provide immediate visual feedback with loading spinners across all asynchronous operations on the page:
     - Table data loading & reload indicator via DataTables `processing` overlay.
     - "Add Secretary" submission button loading state (`#add_secretary_btn`).
     - "Add Admin User" submission button loading state (`#add_admin_btn`).
     - Row action buttons during data retrieval:
       - Edit button (`.edit_user`) spinner while details are fetched via `/api/fetch_secretary_details` or `/api/fetch_admin_details`.
       - Assigned Doctors button (`.assign_doctor`) spinner while doctor lists are fetched via `/api/fetch_secretary_doctors`.
     - Modal submission buttons:
       - Save Edit Secretary (`#save_edit_secretary_btn`) spinner while updating.
       - Save Edit Admin (`#save_edit_admin_btn`) spinner while updating.
       - Save Assigned Doctors (`#save_append`) spinner while saving assignments.
     - Delete action: SweetAlert loader (`Swal.showLoading()`) while deleting accounts.
2. **Expanded Action Buttons with Text Labels**:
   - Replace icon-only row buttons with expanded, labeled action buttons:
     - **Edit**: `<i class="fa-solid fa-pen-to-square me-1"></i> Edit`
     - **Assigned Doctors** (Secretaries only): `<i class="fa-solid fa-user-doctor me-1"></i> Assigned Doctors`
     - **Delete**: `<i class="fa-solid fa-trash-can me-1"></i> Delete`
   - Adjust column width and styling (`min-width: 290px`) to ensure buttons fit cleanly without awkward wrapping.
3. **Column Header Dropdown Filter with Dynamic Active Label**:
   - Remove the standalone `Filter Type:` `<select>` element from above the table.
   - Relocate the filter directly into the **Account Type** table column header (`<th>`) as a Bootstrap dropdown menu.
   - Display a dynamic badge indicator inside the header button showing which type is currently filtered on the column:
     - **All**: `Account Type: <span class="badge bg-secondary">All</span>`
     - **Secretary**: `Account Type: <span class="badge bg-info text-dark">Secretary</span>`
     - **Admin**: `Account Type: <span class="badge bg-primary">Admin</span>`
   - Dropdown menu lists options with icons and active checkmarks:
     - *All Accounts*
     - *Secretaries Only*
     - *Admins Only*
   - Clicking an option updates the column header badge, dropdown active states, and immediately filters the DataTable.

---

## 2. User Review Required

> [!NOTE]
> - Action buttons with text labels ("Edit", "Assigned Doctors", "Delete") will expand the column width to ~290px-300px. On smaller desktop resolutions, DataTables horizontal scrolling is supported cleanly via the enclosing `.table-responsive` wrapper.
> - Moving the filter to the column header removes the top filter `<select>`, giving the page header a cleaner layout.

---

## 3. Proposed Changes

### Component 1: Blade View ([`resources/views/pages/admin/users/secretaries.blade.php`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/views/pages/admin/users/secretaries.blade.php))

#### [MODIFY] [`resources/views/pages/admin/users/secretaries.blade.php`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/views/pages/admin/users/secretaries.blade.php)
1. **Remove standalone filter from above table**:
   - Remove `#account_type_filter` select container on lines 169-176.
2. **Update Table Header (`<thead>`)**:
   - Update `Actions` header width to `min-width: 290px; width: 300px;`.
   - Update `Account Type` header into a dropdown menu containing:
     - Toggle button with filter icon, title, and `#filtered_account_type_label` badge.
     - Dropdown menu items with `.filter-account-opt` data attributes (`data-filter=""`, `data-filter="Secretary"`, `data-filter="Admin"`), icons, and checkmarks.
3. **Table Processing Indicator**:
   - Add styling / container hooks for DataTables processing state.

```html
<!-- Account Type Column Header with Dropdown Menu and Active Filter Label -->
<th scope="col" style="min-width: 190px; width: 210px;" class="align-middle text-center">
    <div class="dropdown d-inline-block" id="accountTypeFilterContainer">
        <button class="btn btn-sm btn-light border border-secondary border-opacity-25 dropdown-toggle py-1 px-2 fw-bold text-dark d-flex align-items-center gap-1 mx-auto"
                type="button" id="accountTypeFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Filter by Account Type">
            <i class="fa-solid fa-filter text-success"></i>
            <span>Account Type:</span>
            <span id="filtered_account_type_label" class="badge bg-secondary">All</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-center shadow border-0" aria-labelledby="accountTypeFilterDropdown">
            <li><h6 class="dropdown-header text-uppercase text-muted fw-bold small">Filter by Account Type</h6></li>
            <li>
                <a class="dropdown-item filter-account-opt active d-flex justify-content-between align-items-center py-2" href="javascript:void(0)" data-filter="">
                    <span><i class="fa-solid fa-users me-2 text-secondary"></i> All Accounts</span>
                    <i class="fa-solid fa-check text-success filter-check-icon"></i>
                </a>
            </li>
            <li><hr class="dropdown-divider my-1"></li>
            <li>
                <a class="dropdown-item filter-account-opt d-flex justify-content-between align-items-center py-2" href="javascript:void(0)" data-filter="Secretary">
                    <span><i class="fa-solid fa-user-nurse me-2 text-info"></i> Secretaries Only</span>
                    <i class="fa-solid fa-check text-success filter-check-icon d-none"></i>
                </a>
            </li>
            <li>
                <a class="dropdown-item filter-account-opt d-flex justify-content-between align-items-center py-2" href="javascript:void(0)" data-filter="Admin">
                    <span><i class="fa-solid fa-user-shield me-2 text-primary"></i> Admins Only</span>
                    <i class="fa-solid fa-check text-success filter-check-icon d-none"></i>
                </a>
            </li>
        </ul>
    </div>
</th>
```

---

### Component 2: Frontend JavaScript ([`resources/js/pages/admin/users/secretaries.js`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/js/pages/admin/users/secretaries.js))

#### [MODIFY] [`resources/js/pages/admin/users/secretaries.js`](file:///C:/docker/php_projects/kayakapmd_clinic/resources/js/pages/admin/users/secretaries.js)
1. **DataTables Configuration**:
   - Enable `processing: true` with a custom spinner template:
     ```javascript
     processing: true,
     language: {
         processing: '<div class="d-flex justify-content-center align-items-center py-2"><div class="spinner-border spinner-border-sm text-success me-2" role="status"></div><span class="text-secondary fw-bold">Loading accounts...</span></div>',
         emptyTable: "No users registered yet."
     },
     ```
   - Update Column 0 renderer with expanded text labels:
     ```javascript
     let buttons = `<div class="d-inline-flex flex-wrap gap-1 justify-content-center align-items-center">
         <button class="btn btn-sm btn-primary edit_user text-nowrap" data-type="${data.account_type}" value="${ref}" title="Edit User">
             <i class="fa-solid fa-pen-to-square me-1"></i> Edit
         </button>`;

     if (isSecretary) {
         buttons += `<button class="btn btn-sm btn-info text-white assign_doctor text-nowrap" value="${data.secrefno}" title="Assigned Doctors">
             <i class="fa-solid fa-user-doctor me-1"></i> Assigned Doctors
         </button>`;
     }

     buttons += `<button class="btn btn-sm btn-danger delete_user text-nowrap" data-type="${data.account_type}" value="${ref}" title="Delete User">
         <i class="fa-solid fa-trash-can me-1"></i> Delete
     </button>
     </div>`;
     ```
   - Update `columnDefs` for column 0 (`width: '300px'`) and column 1 (`width: '210px'`).
2. **Column Dropdown Filter Handler**:
   - Wire `$(document).on("click", ".filter-account-opt", ...)` to:
     - Update active option class and checkmark visibility.
     - Update `#filtered_account_type_label` text (`All`, `Secretary`, or `Admin`) and badge color classes.
     - Execute exact column regex search: `secretaryTable.column(1).search(filterVal ? `^${filterVal}$` : '', true, false).draw();`.
3. **Loading Spinners for Asynchronous Actions**:
   - Helper function or inline state management for buttons:
     - `#add_secretary_btn`: Disable and render `<span class="spinner-border spinner-border-sm me-1" role="status"></span> Adding Secretary...`.
     - `#add_admin_btn`: Disable and render `<span class="spinner-border spinner-border-sm me-1" role="status"></span> Adding Admin User...`.
     - `.edit_user`: Store original HTML on clicked button, disable, show spinner `<span class="spinner-border spinner-border-sm me-1" role="status"></span> Loading...` until modal opens or request errors.
     - `#save_edit_secretary_btn`: Disable and show spinner `<span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving...`.
     - `#save_edit_admin_btn`: Disable and show spinner `<span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving...`.
     - `.assign_doctor`: Store original HTML on clicked button, disable, show spinner `<span class="spinner-border spinner-border-sm me-1" role="status"></span> Loading...` until modal opens.
     - `#save_append`: Disable and show spinner `<span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving...`.
     - `.delete_user`: Invoke `Swal.showLoading()` while AJAX request is active.

---

## 4. Verification Plan

### Automated Tests
Execute the automated test suite in Docker container `latest_php_server` to verify no regressions in backend API endpoints or route handling:
```bash
docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan test --filter=DoctorSecretaryConsoleTest
```

### Build & Asset Compilation
Recompile frontend assets with Vite:
```bash
cmd.exe /c "npm run build"
```

### Manual Verification
1. Open `/admin/users/secretaries-admins`.
2. Observe table loading state with spinner while records are being fetched.
3. Check the **Account Type** column header:
   - Verify it shows a dropdown toggle button with initial label `Account Type: [All]`.
   - Click the dropdown; select **Secretaries Only**.
   - Verify the label changes to `Account Type: [Secretary]` with an info-badge, and the table filters only secretaries.
   - Click the dropdown; select **Admins Only**.
   - Verify the label changes to `Account Type: [Admin]` with a primary-badge, and the table filters only admins.
   - Click **All Accounts**; verify all users return and badge reverts to `[All]`.
4. Inspect row action buttons:
   - Verify text labels are visible: **Edit**, **Assigned Doctors** (Secretaries only), and **Delete**.
   - Verify buttons do not wrap awkwardly.
5. Click **Add Secretary** / **Add Admin User**:
   - Verify button shows a spinner and disables while submitting.
6. Click **Edit** on a row:
   - Verify the button shows a loading spinner while fetching account details, and modal opens cleanly.
7. Click **Assigned Doctors** on a Secretary row:
   - Verify button shows a spinner while fetching assigned doctors.
8. Click **Delete** on a user:
   - Confirm SweetAlert shows loading spinner while deletion is in progress.
