# Walkthrough: Custom Theme Management & Admin Settings Integration

**Document Version:** 1.0  
**Completion Date:** 2026-10-09  
**Verified Feature:** Facility Theme Customization, Two-Color Preset, Settings Sidebar Navigation, and Global Layout Styling

---

## 1. Overview of Changes

We implemented a full custom branding and theme management system for the **KayakAPMD Clinic** application:
1. **Database Schema & Model**:
   - Created the `theme` table via migration [`2026_10_09_000001_create_theme_table.php`](file:///C:/xampp/htdocs/kayakapmd_clinic/database/migrations/2026_10_09_000001_create_theme_table.php).
   - Documented the schema under Section 2 ("Facility & Access Configuration") in [.gemini/database/kayakapmdv2_data_dictionary.md](file:///C:/xampp/htdocs/kayakapmd_clinic/.gemini/database/kayakapmdv2_data_dictionary.md).
   - Created [`ThemeModel`](file:///C:/xampp/htdocs/kayakapmd_clinic/app/Models/ThemeModel.php) with full fillable attributes, type casting, `profile()` relation, and static `getActiveTheme()` helper.
   - Added `theme()` relation to [`KayakapProfileModel`](file:///C:/xampp/htdocs/kayakapmd_clinic/app/Models/KayakapProfileModel.php).
2. **Global View Composer**:
   - In [`AppServiceProvider`](file:///C:/xampp/htdocs/kayakapmd_clinic/app/Providers/AppServiceProvider.php), registered a defensive `View::composer('*')` sharing `$activeTheme` and `$activeProfile` across all views.
3. **Backend Controllers & Routes**:
   - In [`ManagementController`](file:///C:/xampp/htdocs/kayakapmd_clinic/app/Http/Controllers/ManagementController.php), implemented `themeSettingsPage()`, `fetchThemeSettings()`, `updateThemeSettings()`, and `resetThemeSettings()`.
   - In [`routes/web.php`](file:///C:/xampp/htdocs/kayakapmd_clinic/routes/web.php), added `admin/settings/theme` (`admin.settings.theme`).
   - In [`routes/api.php`](file:///C:/xampp/htdocs/kayakapmd_clinic/routes/api.php), added `/api/admin/theme/fetch`, `/api/admin/theme/update`, and `/api/admin/theme/reset`.
4. **Navigation & Layout**:
   - In [`components/sidebar.blade.php`](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/views/components/sidebar.blade.php), replaced the static Profile link with a collapsible **Settings** accordion menu (`#settingsBtn`) containing **Theme Settings** and **Facility Profile**.
   - In [`pages/admin/profile.blade.php`](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/views/pages/admin/profile.blade.php), added a top header button linking to Theme & Branding Settings.
   - In [`components/navbar.blade.php`](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/views/components/navbar.blade.php), bound the navbar background, logo path, and bottom accent line to dynamic theme CSS variables.
   - In [`layouts/app.blade.php`](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/views/layouts/app.blade.php), injected `:root` CSS custom variables (`--app-bg`, `--header-bg`, `--header-text`, `--header-accent`, `--footer-bg`, `--footer-text`, `--sidebar-bg`, `--sidebar-text`, `--primary-btn-bg`, `--primary-btn-text`, `--secondary-btn-bg`, `--secondary-btn-text`, `--body-text`) and added an application footer bar.
5. **Theme Customizer UI & JavaScript**:
   - Built [`resources/views/pages/admin/settings/theme.blade.php`](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/views/pages/admin/settings/theme.blade.php) featuring organized color cards, logo upload, live preview, and preset buttons.
   - Built [`resources/js/pages/admin/settings/theme.js`](file:///C:/xampp/htdocs/kayakapmd_clinic/resources/js/pages/admin/settings/theme.js) with two-way color picker/hex synchronization, instant in-page live preview simulation, Teal & Lime 2-color brand preset (`#027f9f` / `#b2c10e`), and SweetAlert2 AJAX handling.
   - Added `theme.js` to [`vite.config.js`](file:///C:/xampp/htdocs/kayakapmd_clinic/vite.config.js) and compiled via `vite build`.

---

## 2. Test Verification Results

### Automated Feature Tests
Executed feature test suite:
```bash
php artisan test --filter=AdminThemeSettingsTest
```
Result:
```text
   PASS  Tests\Feature\AdminThemeSettingsTest
  ✓ admin can access theme settings page                                                                                                                                                                                                                              0.88s  
  ✓ unauthenticated user redirected from theme settings page                                                                                                                                                                                                          0.04s  
  ✓ admin can fetch theme settings api                                                                                                                                                                                                                                0.05s  
  ✓ admin can update theme settings                                                                                                                                                                                                                                   0.05s  
  ✓ admin can upload custom logo                                                                                                                                                                                                                                      0.06s  
  ✓ admin can reset theme to defaults                                                                                                                                                                                                                                 0.06s  
  ✓ theme links to kayakapmd profile                                                                                                                                                                                                                                  0.15s  

  Tests:    7 passed (29 assertions)
  Duration: 1.53s
```

Full regression test suite:
```bash
php artisan test
```
Result: **94 passed (611 assertions)** across all modules with 0 failures.

### Frontend Asset Compilation
```bash
cmd /c npm run build
```
Result: Built successfully in 3.91s, generating `public/build/assets/theme-Bfln-ZSi.js`.

---

## 3. Manual Verification Steps for User

1. Log in as an Administrator (`/login`).
2. Notice the left sidebar now features a collapsible **Settings** accordion menu:
   - Click **Settings** to reveal **Theme Settings** and **Facility Profile**.
3. Click **Theme Settings** (`/admin/settings/theme`):
   - Click the **Teal & Lime (2-Color Brand)** preset button.
   - Observe that the in-page **Live Real-Time Preview** card immediately switches to `#027f9f` (Teal) navbar and `#b2c10e` (Lime) accent strip/button.
4. Try choosing custom colors using the HTML5 color pickers or hex text fields.
5. Choose a custom logo file and observe the live thumbnail update.
6. Click **Save Theme Changes**:
   - Receive the SweetAlert success notification.
   - Observe that the actual application top navbar, bottom accent strip, buttons, and bottom footer reflect the new branding.
7. Click **Reset to Defaults** to restore factory clinic colors whenever desired.
