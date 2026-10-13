# Implementation Plan: Adjust Weight, Height, and Temperature UI in Secretary Console

## Context
In the Secretary Console (`resources/views/pages/secretary/queue.blade.php`), the Weight, Height, and Temperature vital sign fields are currently cramped in a single row where all 6 vital signs are forced into `col-md-2` (1/6th of card width). On desktops, laptops, and tablets, each 1/6th column is only ~80px-115px wide. Inside an `.input-group`, the number input and unit dropdown (`#pxweight_unit`, `#pxheight_unit`, `#pxtemp_unit`) compete for space, causing the dropdown values ("kgs", "cm", "°C") to be clipped, truncated by the dropdown arrow icon, or pushed off the card across display sizes.

## Proposed Changes

### 1. `resources/views/pages/secretary/queue.blade.php`
- Reorganize the vital signs grid into two balanced, responsive 3-column rows (`col-12 col-sm-4`):
  - **Row 1 (Physical Measurements with Units)**: Weight, Height, and Temperature.
  - **Row 2 (Hemodynamic & Clinical Vitals)**: Respiratory Rate, Pulse Rate, and Blood Pressure.
- Apply dedicated styling (`.vital-unit-select`) to the unit `<select>` elements (`#pxweight_unit`, `#pxheight_unit`, `#pxtemp_unit`):
  - `flex: 0 0 auto; width: 4.8rem; min-width: 4.8rem; max-width: 5.2rem;`
  - `padding-right: 1.5rem; padding-left: 0.5rem;`
  - Ensures unit labels ("kgs", "lbs", "cm", "m", "ft", "°C", "°F") are fully legible without overlap from the select dropdown arrow.
- Ensure number inputs have `flex: 1 1 auto; min-width: 0;` so they expand and contract smoothly without container overflow.
- Ensure option sets include alternate units (`lbs` for weight, `m` & `ft` for height, `°F` for temperature) while keeping `kgs`, `cm`, and `°C` selected by default.

### 2. `resources/css/app.css`
- Add `.vital-unit-select` CSS rules for consistent rendering across browsers and themes.

### 3. `resources/views/secretary.blade.php`
- Update the legacy secretary view's vital signs inputs with the same `.vital-unit-select` class and unit options for consistency.

## Verification Plan
1. Run `npm.cmd run build` to compile CSS and frontend assets.
2. Run `php artisan test` to verify all automated tests pass.
3. Verify markup and responsive columns across breakpoints (phone <576px, tablet 576-768px, laptop 768-1200px, desktop >1200px).
