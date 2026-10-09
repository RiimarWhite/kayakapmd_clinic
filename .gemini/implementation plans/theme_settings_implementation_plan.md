# Implementation Plan: Custom Theme Management & Admin Settings Integration

**Document Version:** 2.0 (Updated with User Confirmation)  
**Target Module:** Admin Dashboard / Facility Configuration / App Layout  
**Authoritative Data Source:** [.gemini/database/kayakapmdv2_data_dictionary.md](file:///C:/xampp/htdocs/kayakapmd_clinic/.gemini/database/kayakapmdv2_data_dictionary.md)  
**Preset Reference Source:** `temp/7ff09159-09b0-4991-8e0d-138c622001ad.jfif` (Teal `#027f9f` & Lime `#b2c10e`)  
**Rules & Protocols Followed:** [rules.md](file:///C:/xampp/htdocs/kayakapmd_clinic/rules.md) & [skills.md](file:///C:/xampp/htdocs/kayakapmd_clinic/skills.md)

---

## 1. Goal Description

Implement an end-to-end customizable theme system for the **KayakAPMD Clinic** application that allows administrators to personalize clinic branding, navbar, footer, buttons, background, text colors, and logo:
1. **Database Layer:** Create a new `theme` table linked to the master clinic facility profile (`kayakapmd_profile` via `clientcode`).
2. **Admin Navigation:**
   - Add a collapsible **Settings** menu on the Admin sidebar containing both **Theme Settings** (`/admin/settings/theme`) and **Facility Profile** (`/admin/profile`).
   - Add a direct tab/link button on the existing Profile page (`/admin/profile`) leading seamlessly to Theme Settings.
3. **Theme Settings Management:**
   - Color pickers for:
     - **App Background**
     - **Header (Navbar) Background**
     - **Header Text / Icon Color**
     - **Header Bottom Accent Line Color**
     - **Footer Background**
     - **Footer Text Color**
     - **Primary Button Background & Text Color**
     - **Secondary Button Background & Text Color**
     - **General Body Text Color**
   - **Logo Upload & Preview:** Live preview with validation, replacing the global navbar logo.
   - **One-Click Theme Preset:** Built specifically using the user-provided two-color palette from `temp/7ff09159-09b0-4991-8e0d-138c622001ad.jfif`:
     - **Primary Brand / Header Color:** `#027f9f` (Teal Blue)
     - **Accent Strip / Highlight Color:** `#b2c10e` (Lime Green)
   - **Live Interactive Preview Box:** In-page preview simulating the Navbar, Sidebar, Card with buttons/text, and Footer in real-time.
   - **Reset to Defaults:** Ability to restore default factory clinic branding.
4. **Global Layout & Dynamic Footer:**
   - Inject dynamic CSS custom properties into [resources/views/layouts/app.blade.php](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/views/layouts/app.blade.php).
   - Add a clean, responsive bottom application footer in `app.blade.php` displaying clinic name, copyright year, and version, styled with the theme's footer background and text colors.

---

## 2. Architecture & Data Flow Diagram

```mermaid
flowchart TD
    Admin["Admin User"] -->|Sidebar: Settings -> Theme Settings| ThemePage["Theme Settings Blade View"]
    Admin -->|Profile Page: Theme Settings Tab/Link| ThemePage
    
    subgraph UI ["Theme Customizer"]
        PresetBtn["2-Color Preset Button (#027f9f / #b2c10e)"] --> LivePreview["Real-Time Live Preview Box"]
        ColorPickers["Color Pickers (Header, Footer, Bg, Buttons, Text)"] --> LivePreview
        LogoUpload["Logo File Upload"] --> LivePreview
        SaveBtn["Save Theme Button"]
    end
    
    SaveBtn -->|AJAX POST /api/admin/theme/update| Controller["ManagementController"]
    Controller -->|Validates & Saves Image| Disk["public/images/ OR public/uploads/"]
    Controller -->|Persists by clientcode| ThemeModel["ThemeModel (Eloquent)"]
    ThemeModel --> DB[("MySQL: theme table")]
    DB -.->|Foreign business key clientcode| Profile[("kayakapmd_profile")]

    AppServiceProvider["AppServiceProvider (boot)"] -->|View::composer('*') with activeTheme| Layout["layouts/app.blade.php"]
    Layout -->|CSS :root variables| GlobalStyles["Navbar, Accent Strip, Footer, Buttons, Body"]
```

---

## 3. User Decisions & Design Specifications

1. **Sidebar Navigation**:
   - Collapsible accordion menu **Settings** (`#settingsMenuBtn`) under Admin in [components/sidebar.blade.php](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/views/components/sidebar.blade.php).
   - Sub-items:
     - `Theme Settings` (`route('admin.settings.theme')` -> `/admin/settings/theme`)
     - `Facility Profile` (`route('admin.profile')` -> `/admin/profile`)
   - On `pages/admin/profile.blade.php`, include a top quick-action navigation tab/link to "Theme Settings".
2. **Two-Color Preset from Image**:
   - Primary Header Color: `#027f9f` (Teal)
   - Accent Strip Color: `#b2c10e` (Lime)
   - Matching Primary Button: `#027f9f`
   - Matching Secondary Button: `#b2c10e` (with dark text for readability)
   - Header text: `#ffffff`
3. **Application Footer**:
   - Embedded cleanly at the bottom of [resources/views/layouts/app.blade.php](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/views/layouts/app.blade.php).
   - Shows `&copy; {{ date('Y') }} {{ $activeProfile->HOSP_NAME ?? config('app.name') }}` and system version.
   - Fully styled by `--footer-bg` and `--footer-text` CSS variables.

---

## 4. Proposed Changes

### Component 1: Database & Data Dictionary Sync (Rules 3 & 4)

#### [NEW] `database/migrations/2026_10_09_000001_create_theme_table.php`
- Create defensive, idempotent migration creating table `theme`.
- Columns:
  - `id` (bigint unsigned auto-increment primary key)
  - `clientcode` (varchar 12, nullable, index)
  - `theme_name` (varchar 100, default `'Default Theme'`)
  - `app_background` (varchar 50, default `'#f8f9fa'`)
  - `header_bg` (varchar 50, default `'#f4c79f'`)
  - `header_text_color` (varchar 50, default `'#212529'`)
  - `header_accent_color` (varchar 50, default `'#ffa500'`)
  - `footer_bg` (varchar 50, default `'#f8f9fa'`)
  - `footer_text_color` (varchar 50, default `'#6c757d'`)
  - `sidebar_bg` (varchar 50, default `'#e9ecef'`)
  - `sidebar_text_color` (varchar 50, default `'#212529'`)
  - `primary_button_bg` (varchar 50, default `'#0d6efd'`)
  - `primary_button_text` (varchar 50, default `'#ffffff'`)
  - `secondary_button_bg` (varchar 50, default `'#6c757d'`)
  - `secondary_button_text` (varchar 50, default `'#ffffff'`)
  - `text_color` (varchar 50, default `'#212529'`)
  - `logo_path` (varchar 255, nullable, default `'images/logo.png'`)
  - `is_active` (boolean, default `true`)
  - `timestamps()`

#### [MODIFY] `.gemini/database/kayakapmdv2_data_dictionary.md`
- Synchronize data dictionary under **Section 2: Facility & Access Configuration** by appending the full markdown table definition for `theme`, detailing all columns, types, nullability, defaults, and foreign relationship notes.

---

### Component 2: Eloquent Models & Service Providers

#### [NEW] `app/Models/ThemeModel.php`
- Define `ThemeModel` representing `theme` table.
- Define fillable properties, default casts, and `profile()` belongsTo relationship with `KayakapProfileModel` (`clientcode`).
- Provide static helper `getActiveTheme(?string $clientcode = null)`.

#### [MODIFY] `app/Models/KayakapProfileModel.php`
- Add `theme()` hasOne relationship to `ThemeModel` matching on `clientcode`.

#### [MODIFY] `app/Providers/AppServiceProvider.php`
- In `boot()`, register defensive `View::composer('*', ...)` that injects `$activeTheme` into every view if `Schema::hasTable('theme')` is true.

---

### Component 3: Controllers & Routing Layer

#### [MODIFY] `app/Http/Controllers/ManagementController.php`
- Add `themeSettingsPage()`: Returns view `pages.admin.settings.theme` loaded with active theme and profile records.
- Add `fetchThemeSettings()`: Returns current active theme as JSON.
- Add `updateThemeSettings(Request $request)`: Validates color hex codes (`regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/`) and logo image upload (`image|mimes:jpg,jpeg,png,svg|max:2048`). Stores uploaded logo in `public/images/custom_logo.png` or `public/uploads/logos/` and persists theme record.
- Add `resetThemeSettings()`: Reverts theme values back to default system colors.

#### [MODIFY] `routes/web.php`
- Register `Route::get('admin/settings/theme', [ManagementController::class, 'themeSettingsPage'])->name('admin.settings.theme');` within `auth:admin` middleware group.

#### [MODIFY] `routes/api.php`
- Register API endpoints within `auth:admin` middleware group:
  - `Route::post('admin/theme/fetch', [ManagementController::class, 'fetchThemeSettings'])->name('admin.theme.fetch');`
  - `Route::post('admin/theme/update', [ManagementController::class, 'updateThemeSettings'])->name('admin.theme.update');`
  - `Route::post('admin/theme/reset', [ManagementController::class, 'resetThemeSettings'])->name('admin.theme.reset');`

---

### Component 4: Layout & UI Customization

#### [MODIFY] `resources/views/layouts/app.blade.php`
- Add dynamic `<style id="app-dynamic-theme-vars">` block defining CSS custom variables (`--app-bg`, `--header-bg`, `--header-text`, `--header-accent`, `--footer-bg`, `--footer-text`, `--sidebar-bg`, `--sidebar-text`, `--primary-btn-bg`, `--primary-btn-text`, `--secondary-btn-bg`, `--secondary-btn-text`, `--body-text`).
- Add application footer container `<footer class="app-footer">` styled via CSS variables.

#### [MODIFY] `resources/views/components/navbar.blade.php`
- Replace hardcoded background colors with CSS variables.
- Update logo image source to dynamically use `asset($activeTheme->logo_path ?? 'images/logo.png')`.
- Update accent line below navbar to use `var(--header-accent, orange)`.

#### [MODIFY] `resources/views/components/sidebar.blade.php`
- Add a collapsible **Settings** menu item under Admin:
  - Sub-menu 1: `Theme Settings` (`route('admin.settings.theme')`)
  - Sub-menu 2: `Facility Profile` (`route('admin.profile')`)

#### [MODIFY] `resources/views/pages/admin/profile.blade.php`
- Add a top nav link / button linking directly to `route('admin.settings.theme')`.

---

### Component 5: Views & Frontend Scripts

#### [NEW] `resources/views/pages/admin/settings/theme.blade.php`
- Comprehensive settings interface with:
  1. **Branding & Logo Card:** Display current logo, file upload input with live preview, and image dimension guides.
  2. **Color Palette Controls Card:** Grouped color pickers with synced hex text inputs for:
     - App background
     - Header / Navbar (background, text/icons, bottom accent line)
     - Footer (background, text)
     - Primary button (background, text)
     - Secondary button (background, text)
     - Body & Sidebar text
  3. **One-Click Theme Preset:** Teal & Lime (`#027f9f` / `#b2c10e`) preset button from `temp/7ff09159-09b0-4991-8e0d-138c622001ad.jfif`.
  4. **Live Interactive Preview Box:** A miniature mock rendering of the Navbar, Sidebar, Content cards, and Footer that updates dynamically as color pickers change.
  5. **Actions:** Save Theme button (AJAX + SweetAlert2) and Reset to Defaults button.

#### [NEW] `resources/js/pages/admin/settings/theme.js`
- Handles real-time live preview synchronization on color inputs.
- Handles logo file drag/drop and change preview.
- Preset selection logic for the Teal & Lime palette.
- AJAX submission to `/api/admin/theme/update` and `/api/admin/theme/reset`.

#### [MODIFY] `vite.config.js`
- Register `resources/js/pages/admin/settings/theme.js` in `input: [...]` bundle array.

---

## 5. Verification Plan

### Automated Tests
Execute Laravel test suite:
```bash
php artisan test --filter=AdminThemeSettingsTest
```
And full regression test verification:
```bash
php artisan test
```

### Manual Verification
1. Log in as Administrator (`admin`).
2. Verify the new **Settings** collapsible menu and **Theme Settings** sub-menu in the left sidebar.
3. Open `/admin/settings/theme`.
4. Click the Teal & Lime 2-color preset button (`#027f9f` and `#b2c10e`) and verify the live preview and inputs immediately reflect the palette.
5. Upload a logo file and click **Save Theme Changes**.
6. Check that the navbar, bottom strip, buttons, and footer update dynamically.
7. Click **Reset to Defaults** and verify full restoration of original clinic colors.
