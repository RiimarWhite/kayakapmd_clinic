# Implementation Plan — Milestone M5: Global Header Theme Switcher

## Overview
Implement an accessible, production-grade Theme Switcher in KayakapMD Clinic across the top navigation bar, layout head script, client JavaScript bundle, and application stylesheet. Supports Light, Dark, and System color schemes with persistent `localStorage` memory, zero Flash of Unstyled Content (FOUC), and cohesive Bootstrap 5.3 dark mode overrides.

## Exclusive Write Boundaries
- `resources/views/components/navbar.blade.php`
- `resources/views/layouts/app.blade.php`
- `resources/js/app.js`
- `resources/css/app.css`

## Step-by-Step Execution Plan

### Step 1: Navbar Theme Switcher Dropdown (`resources/views/components/navbar.blade.php`)
- Replace the static placeholder `<button class="btn btn-light"><i class="fa-solid fa-sun"></i></button>` with an accessible Bootstrap 5.3 theme switcher dropdown:
  - Container `#theme_switcher_dropdown`
  - Trigger button `#bd-theme` with `data-bs-toggle="dropdown"`, active icon `#theme-active-icon`, and active label `#theme-active-text`.
  - Dropdown menu `#theme_options_menu` with 3 items (`data-bs-theme-value="light"`, `data-bs-theme-value="dark"`, `data-bs-theme-value="system"`).
  - Each item includes an icon, mode label, and checkmark `.theme-check-icon`.

### Step 2: Head FOUC Prevention Script & Root Support (`resources/views/layouts/app.blade.php`)
- Add an inline synchronous `<script>` block in `<head>` before stylesheets:
  - Reads `localStorage.getItem('theme')` (defaulting to 'system').
  - Resolves 'system' via `window.matchMedia('(prefers-color-scheme: dark)')`.
  - Sets `document.documentElement.setAttribute('data-bs-theme', resolvedTheme)` synchronously before the first paint to eliminate FOUC.
- In `#app-dynamic-theme-vars` `<style>`, add `[data-bs-theme="dark"]` body background and color rules so the dynamic inline brand styles do not force white/light background in dark mode.

### Step 3: Client Theme Controller (`resources/js/app.js`)
- Implement `initThemeSwitcher()`:
  - Resolves stored preference from `localStorage`.
  - Sets `data-bs-theme` attribute on `<html>`.
  - Dispatches `themeChanged` event on `window`.
  - Updates active button checkmark (`.theme-check-icon`) and trigger icon/text.
  - Registers `window.matchMedia('(prefers-color-scheme: dark)')` change listener for dynamic OS theme switching when 'system' is selected.
  - Attaches delegated click listener on `[data-bs-theme-value]` buttons to persist and apply selections.

### Step 4: Dark Mode CSS Overrides (`resources/css/app.css`)
- Implement comprehensive `[data-bs-theme="dark"]` CSS rules for:
  - Body, background, and typography.
  - Cards: `.card`, `.card-header`, `.card-footer`, `.card-body`.
  - Modals: `.modal-content`, `.modal-header`, `.modal-footer`, `.modal-title`, `.btn-close` (inverted).
  - Tables: `.table`, `th`, `td`, zebra striping, hover states, borders.
  - Forms: `.form-control`, `.form-select`, `.input-group-text`, checkboxes, labels.
  - Navbar, sidebars (`#admin_sidebar`, `#management_sidebar`), and footer (`.app-footer`).
  - Dropdowns: `.dropdown-menu`, `.dropdown-item`.
  - Third-party widgets: Select2 containers and jQuery UI autocomplete.

### Step 5: Verification & Asset Compilation
- Run `npm.cmd run build` to ensure assets compile cleanly without errors.
- Run `php artisan test --filter=AdminThemeSettingsTest` to ensure no backend regressions.
- Verify DOM attributes and build artifacts.
