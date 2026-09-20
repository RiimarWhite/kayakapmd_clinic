# Implementation Plan: Investigate and Fix Vite Manifest Error in Secretary Queue

## Problem & Goal Description
The Laravel log recorded a critical HTTP 500 `ViewException`:
```text
[2026-09-20 10:38:30] local.ERROR: Vite manifest not found at: /var/www/html/kayakapmd_clinic/public/build/manifest.json (View: /var/www/html/kayakapmd_clinic/resources/views/pages/secretary/queue.blade.php) {"userId":22,"exception":"[object] (Illuminate\\View\\ViewException(code: 0): Vite manifest not found at: /var/www/html/kayakapmd_clinic/public/build/manifest.json (View: /var/www/html/kayakapmd_clinic/resources/views/pages/secretary/queue.blade.php) at /var/www/html/kayakapmd_clinic/vendor/laravel/framework/src/Illuminate/Foundation/Vite.php:946)"}
```

### Root Cause Analysis
1. **Build Wiping Window**: During `npm run build`, Vite by default applies `build.emptyOutDir: true`, wiping the entire `public/build` directory at the start of the build process. During the 3–4 seconds while 112 modules are being transformed and compiled, `public/build/manifest.json` **does not exist** on disk.
2. **Container Volume Latency**: The Docker container mounts the Windows host directory via `C:/docker/php_projects:/var/www/html`. When `manifest.json` is temporarily absent on the host, the container's virtiofs/FUSE filesystem reflects this absence immediately.
3. **Strict Laravel Exception**: Laravel's default `Illuminate\Foundation\Vite` strictly executes `if (! is_file($path)) throw new ViteManifestNotFoundException(...)` with zero fallback or tolerance. If any user accesses `/secretary/queue` (or any other page invoking `@vite(...)`) during an asset rebuild or file synchronization lag, Laravel crashes with an unhandled HTTP 500 exception.

---

## User Review Required
> [!IMPORTANT]
> - We will implement a resilient `SafeVite` service provider extending Laravel's `Illuminate\Foundation\Vite` that introduces a two-tier fallback: (1) memory cache and (2) disk backup `manifest.json.bak`.
> - In `vite.config.js`, we will configure `build.emptyOutDir: false` so that Vite compiles assets in place and atomic-replaces `manifest.json` at the conclusion of builds rather than deleting the entire directory at the start.
> - No breaking changes to frontend scripts or views are introduced.

---

## Proposed Changes

### 1. Backend Core (`app/Support` & `app/Providers`)

#### [NEW] `app/Support/SafeVite.php`
- Extend `Illuminate\Foundation\Vite`.
- Override `manifest($buildDirectory)`:
  - When `public/build/manifest.json` is present, read and decode it, store an in-memory cache, and maintain a backup copy at `public/build/manifest.json.bak`.
  - If `manifest.json` is missing or temporarily locked on disk during a build or filesystem latency event, fall back to the in-memory cache or `manifest.json.bak`, log an informative warning with context, and avoid throwing a fatal 500 `ViteManifestNotFoundException`.
  - In `local` and `testing` environments, if no manifest or backup exists at all, return a graceful fallback descriptor rather than crashing the view.
- Override `chunk($manifest, $file)`:
  - If an asset entry point is missing from the manifest during a hot compile or rebuild, log a warning and return a safe unbundled entry point descriptor instead of throwing an unhandled `ViteException`.

#### [MODIFY] `app/Providers/AppServiceProvider.php`
- Register `SafeVite` as the container singleton for `Illuminate\Foundation\Vite::class`.
- Ensures all `@vite` directives, `Vite` facades, and Blade views transparently inherit the resilience logic.

---

### 2. Frontend Configuration & Setup Script

#### [MODIFY] `vite.config.js`
- Set `build.emptyOutDir: false` in `build` options.
- Preserves existing compiled assets and `manifest.json` while Vite compiles new chunks, eliminating the deletion window during `npm run build`.

#### [MODIFY] `setup.sh`
- Update Section 8 directory permissions to include `public/build`:
  `chmod -R 777 storage bootstrap/cache public/build 2>/dev/null || true`

---

### 3. Automated Test Suite (Rule 6)

#### [NEW] `tests/Feature/ViteResilienceTest.php`
- Test that `SafeVite` successfully resolves manifest from disk backup when primary `manifest.json` is missing.
- Test that accessing `/secretary/queue` while authenticated as secretary renders successfully with HTTP 200 without throwing `ViteManifestNotFoundException`.
- Test that missing chunk in local/testing returns fallback without throwing 500 error.

---

## Verification Plan

### Automated Tests
Run the feature test suite inside Docker:
```bash
docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan test --filter=ViteResilienceTest
```
Run existing authentication and queue tests:
```bash
docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan test --filter=AuthTest
docker exec -w /var/www/html/kayakapmd_clinic latest_php_server php artisan test --filter=OpdConsultationWorkflowTest
```

### Manual & Endpoint Verification
1. Rebuild assets: `npm.cmd run build`.
2. Verify `public/build/manifest.json` and `public/build/manifest.json.bak` exist.
3. Fetch `http://localhost:10000/build/assets/queue-BGTtIR9E.js` via `curl.exe -I` to verify HTTP 200 OK.
4. Verify `bash -n setup.sh` syntax check.
