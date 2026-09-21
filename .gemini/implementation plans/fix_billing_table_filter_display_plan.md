# Implementation Plan: Fix Table Filter Dropdown Display, Remove Sort Badge, & Show Filter Values on Columns

Resolve the issue where table column filter dropdown menus are unable to display, cut off, or hidden inside table headers on `admin/consultations/billing` (and related admin tables) due to `.table-responsive` overflow clipping and Bootstrap 5 Popper boundary constraints. Additionally, remove the redundant ordering icon from the header filter button and display the active filter values directly on the column header.

---

## 1. Goal Description

### Core Objectives
1. **Fix Table Filter Display & Clipping on `admin/consultations/billing`**:
   Ensure column filter dropdown menus open smoothly, float above `.table-responsive` without clipping, and remain fully interactive even when tables have zero or few records (such as `billing_table` which currently has 1 charge record).
2. **Remove Ordering Icon from the Filter Button**:
   Remove the redundant `.sort-badge` (`fa-arrow-up-a-z` / `fa-arrow-down-z-a`) appended to the `.column-filter-btn`. DataTables already provides its own column sorting arrows on `<th>`, and the dropdown menu already provides directional sort controls. Removing it declutters the column headers.
3. **Display Active Filter Values on Columns**:
   Instead of a generic count badge (`1`) or checkmark (`<i class="fa-solid fa-check"></i>`), display the actual applied filter value (e.g. `CASH`, `Urinalysis`, `2026-09`, or `CASH, HMO`) directly on the column filter button/badge so users immediately see active constraints across all columns at a glance.

---

## 2. User Review Required

> [!IMPORTANT]
> - **Unified Across All Admin Tables**: Enhancing `resources/js/helpers/table-column-filter.js` automatically upgrades all 6 admin tables:
>   - `admin/consultations/billing` (`#billing_table`)
>   - `admin/consultations/settlements` (`#settlements_table`)
>   - `admin/hmo` (`#admin_hmo_table`)
>   - `admin/utilities/chargecat` (`#charge_category_table`)
>   - `admin/utilities/diagcat` (`#diag_category_table`)
>   - `admin/users/secretaries-admins` (`#secretary_table`)
> - **Filter Value Truncation**: When filter strings are long (e.g. `Complete Blood Count with Platelet`), the badge will truncate smoothly (`text-truncate`, `max-width: 90px`) and provide a full hover tooltip (`title="Filter: Complete Blood Count with Platelet"`), ensuring table headers remain balanced and don't expand excessively.

---

## 3. Proposed Changes

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                          UPDATED COLUMN HEADER FLOW                         │
│                                                                             │
│  [Column Header <th>]                                                       │
│         │                                                                   │
│         ▼                                                                   │
│  [<button class="column-filter-btn">]                                       │
│    ├── <i class="fa-solid fa-filter"></i>                                   │
│    ├── [NO Sort Icon] (Removed fa-arrow-up-a-z / fa-arrow-down-z-a)         │
│    └── [<span class="badge filter-value-badge">] (Shows: CASH, 2026-09...)  │
│         │                                                                   │
│         ▼ (Click Event)                                                     │
│  [initTableColumnFilters in table-column-filter.js]                         │
│    ├── Close any other open column filter dropdowns                         │
│    └── Initialize Dropdown with boundary: 'window' & strategy: 'fixed'      │
│         │                                                                   │
│         ▼                                                                   │
│  [Popper.js Engine]                                                         │
│    └── Renders menu with `position: fixed` relative to window viewport      │
│         │                                                                   │
│         ▼                                                                   │
│  [CSS Container Defense in app.css & billing.blade.php]                     │
│    └── .table-responsive min-height: 380px ensures spacious clearance      │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

### Component 1: Frontend Column Filter Helper (`table-column-filter.js`)

#### [MODIFY] `resources/js/helpers/table-column-filter.js`

1. **Remove Directional Sort Badge from Filter Button**:
   - In `syncFilterAndSortBadges()`, remove the block appending `.sort-badge` (`<i class="fa-solid fa-arrow-up-a-z"></i>` / `fa-arrow-down-z-a`) to `$btn`.
   - DataTables native sorting arrows and the dropdown menu's sort buttons handle sort indications cleanly.

2. **Display Exact Active Filter Values on the Column Header**:
   - In `syncFilterAndSortBadges()`:
     - Remove old `.filter-badge` elements.
     - When `searchVal` is non-empty:
       - If picklist regex `^(A|B)$`: parse values into an array of items. Format label:
         - 1 item: `Item` (e.g. `CASH`)
         - 2 items: `Item1, Item2` (e.g. `CASH, HMO`)
         - 3+ items: `Item1, Item2 (+N)` (e.g. `CASH, HMO (+1)`)
       - If free-text search: use the trimmed query string as the label.
       - Append a clean, styled badge inside the button:
         ```javascript
         $btn.addClass('btn-primary text-white').removeClass('btn-light')
             .append(`<span class="badge bg-danger ms-1 filter-badge text-truncate" style="max-width: 90px; vertical-align: middle;" title="Filtered by: ${displayLabel}">${displayLabel}</span>`);
         ```
     - When `searchVal` is empty:
       - Restore button styling: `$btn.removeClass('btn-primary text-white').addClass('btn-light')`.

3. **Configure Popper Window Boundary & Fixed Strategy**:
   - In `renderColumnFilterHeader`:
     - Attach `data-bs-boundary="window"`, `data-bs-display="dynamic"`, and `data-bs-popper-config='{"strategy":"fixed"}'` to `<button class="column-filter-btn">`.
     - Align `.dropdown-menu-end` only for right-half columns (`colIdx >= 6`), using default start-alignment for left/center columns (indices 1–5) to prevent negative horizontal clipping.
   - In `initTableColumnFilters`:
     - Close any sibling `.column-filter-btn` instances when opening a dropdown so multiple filter menus never overlap.
     - Instantiate with `{ boundary: 'window', display: 'dynamic', popperConfig: { strategy: 'fixed' } }`.

---

### Component 2: CSS Stylesheet & View Layout

#### [MODIFY] `resources/css/app.css`
Add supportive rules for table clearance and elevated z-index:
```css
/* Ensure table responsive containers have adequate vertical clearance for header dropdowns */
.table-responsive {
    min-height: 380px;
}

/* Ensure column filter menu displays above all table elements with proper elevation */
.column-filter-menu {
    z-index: 1070 !important;
}
```

#### [MODIFY] `resources/views/pages/admin/consultations/billing.blade.php`
- Add `style="min-height: 380px;"` to `.table-responsive` to guarantee spacious vertical clearance on the billing view regardless of record count.

---

### Component 3: Build & Asset Compilation

- Recompile frontend assets via `cmd.exe /c "npm run build"`.
- Verify output hashes in `public/build/manifest.json`.

---

## 4. Verification Plan

### Automated Tests
Run PHPUnit test suite to ensure API queries and server-side filtering continue operating without regressions:
```bash
docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan test --filter=AdminManagementAndAddressIntegrationTest
```

### Manual Verification
1. Navigate to `http://localhost:10000/kayakapmd_clinic/admin/consultations/billing`.
2. Inspect the table headers:
   - Verify there is **NO** sort icon (`fa-arrow-up-a-z`) attached to the filter buttons.
3. Click the filter icon on **Date** (column 1):
   - Verify the dropdown menu opens downwards and is fully visible without being cut off by `.table-responsive`.
   - Enter a date substring (e.g. `2026`) and click "Apply".
   - Verify the column header button updates to show a badge with the value **`2026`**.
4. Click the filter icon on **Payment Type** (column 6):
   - Check **CASH** and click "Apply".
   - Verify the column header button updates to show a badge with **`CASH`**.
   - Check **CASH** and **HMO** and click "Apply"; verify badge shows **`CASH, HMO`**.
5. Click "Clear" on any filter:
   - Verify the filter value badge is removed and the button returns to its neutral state.
6. Click between different filter icons:
   - Verify opening one filter dropdown automatically closes any previously open dropdowns.
