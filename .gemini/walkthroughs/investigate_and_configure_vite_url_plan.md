# Walkthrough: Setup Script, Docker 403 Resolution & Vite App URL Configuration

## Overview
We created an automated setup bash script (`setup.sh`), resolved container networking and idempotent database migrations, fixed the Docker **HTTP 403 Forbidden** error while keeping [`.htaccess`](file:///C:/docker/php_projects/kayakapmd_clinic/.htaccess) untouched for XAMPP, and investigated and resolved Vite asset URL generation so that all assets load natively via the **App URL** (`http://localhost:10000/kayakapmd_clinic/build/assets/...`).

---

## Key Changes Made

### 1. Vite URL Investigation & App URL Configuration
- **Investigation**:
  - Found that `@vite` in Laravel checks if [`public/hot`](file:///C:/docker/php_projects/kayakapmd_clinic/public/hot) exists.
  - When `npm run dev` was previously executed, it wrote `http://[::1]:5173` into `public/hot`. After the dev server stopped, this orphan file caused Laravel to continue injecting `<script src="http://[::1]:5173/...">` tags, leading to connection failures.
  - In compiled build mode (without `public/hot`), Laravel's `@vite` helper automatically uses the **App URL** (`http://localhost:10000/kayakapmd_clinic/build/assets/...`) to serve pre-built assets from [`public/build/`](file:///C:/docker/php_projects/kayakapmd_clinic/public/build/).
- **Changes**:
  - Removed the orphan `public/hot` file.
  - Updated [vite.config.js](file:///C:/docker/php_projects/kayakapmd_clinic/vite.config.js):
    - Added `server.host: 'localhost'`, `server.port: 5173`, `server.strictPort: true`, and `server.origin: 'http://localhost:5173'` to prevent ambiguous IPv6 `[::1]` addresses when running in dev mode.
  - Updated [setup.sh](file:///C:/docker/php_projects/kayakapmd_clinic/setup.sh):
    - Added automatic cleanup of `public/hot` in the `cleanup()` trap when terminating services so the app never gets stuck in stale dev mode.
  - Ran `npm run build` to ensure all production assets are compiled.

---

### 2. Docker HTTP 403 Forbidden Resolution & URL Routing
- **Problem**: Accessing `http://localhost:10000/` or `http://localhost:10000/kayakapmd_clinic/` returned **HTTP 403 Forbidden** because Apache had no index file at `/var/www/html` and Laravel's front controller is in `public/index.php`. The user requested to keep `.htaccess` untouched for XAMPP.
- **Solution**:
  - Created [kayakapmd.conf](file:///C:/docker/kayakapmd.conf):
    - `RedirectMatch 302 ^/$ /kayakapmd_clinic/`
    - `Alias /kayakapmd_clinic /var/www/html/kayakapmd_clinic/public`
    - `FallbackResource /kayakapmd_clinic/index.php` with `AllowOverride None` to avoid redirect loops.
  - Updated [php_docker.yml](file:///C:/docker/php_docker.yml) to mount `kayakapmd.conf`.
  - Updated [Dockerfile](file:///C:/docker/Dockerfile) to copy and enable `kayakapmd.conf`.
  - Configured `APP_URL=http://localhost:10000/kayakapmd_clinic` in [.env](file:///C:/docker/php_projects/kayakapmd_clinic/.env).
  - Preserved repository `.htaccess` completely untouched.

---

### 3. Created Automated Setup Script
- **File**: [setup.sh](file:///C:/docker/php_projects/kayakapmd_clinic/setup.sh)
- **Features**: Prerequisites validation (`php`, `composer`, `node`, `npm`), interactive `.env` configuration with Docker networking tips, idempotent migrations and seeding, asset build, and graceful service termination.

---

## Verification Results

### 1. Rendered Asset Tags on Login Page
```powershell
curl.exe -s http://localhost:10000/kayakapmd_clinic/login | Select-String -Pattern "(link|script)"
```
Output:
```html
<link rel="preload" as="style" href="http://localhost:10000/kayakapmd_clinic/build/assets/vendor-20mIKp7c.css" />
<link rel="preload" as="style" href="http://localhost:10000/kayakapmd_clinic/build/assets/app-DBo_cllb.css" />
<link rel="modulepreload" as="script" href="http://localhost:10000/kayakapmd_clinic/build/assets/app-CdyEeg2F.js" />
<link rel="modulepreload" as="script" href="http://localhost:10000/kayakapmd_clinic/build/assets/vendor-Xrjdmsh6.js" />
<link rel="stylesheet" href="http://localhost:10000/kayakapmd_clinic/build/assets/vendor-20mIKp7c.css" />
<link rel="stylesheet" href="http://localhost:10000/kayakapmd_clinic/build/assets/app-DBo_cllb.css" />
<script type="module" src="http://localhost:10000/kayakapmd_clinic/build/assets/app-CdyEeg2F.js"></script>
```
**Result**: Verified zero references to `[::1]:5173`. All assets use the App URL `http://localhost:10000/kayakapmd_clinic/build/assets/...`.

---

### 2. Asset HTTP Responses
```powershell
curl.exe -I http://localhost:10000/kayakapmd_clinic/build/assets/vendor-20mIKp7c.css
curl.exe -I http://localhost:10000/kayakapmd_clinic/build/assets/app-DBo_cllb.css
curl.exe -I http://localhost:10000/kayakapmd_clinic/build/assets/app-CdyEeg2F.js
curl.exe -I http://localhost:10000/kayakapmd_clinic/build/assets/vendor-Xrjdmsh6.js
```
**Result**: All 4 assets return `HTTP/1.1 200 OK` directly through Apache port 10000.
