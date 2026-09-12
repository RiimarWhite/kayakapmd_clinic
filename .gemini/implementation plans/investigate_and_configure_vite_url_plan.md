# Implementation Plan: Vite URL Investigation & App URL Asset Configuration

## Goal Description
Investigate how Vite URLs are generated in the application, whether they can be changed to use the **App URL** (`http://localhost:10000/kayakapmd_clinic`), and implement the chosen strategy to ensure assets load properly both in production/standard serving and during development.

---

### Investigation Findings

#### 1. Why Did `http://[::1]:5173` Appear?
In Laravel, the `@vite([...])` Blade directive operates in one of two distinct modes based on whether the temporary file [`public/hot`](file:///C:/docker/php_projects/kayakapmd_clinic/public/hot) exists on disk:

1. **Development / Hot Module Reloading (HMR) Mode (`public/hot` exists)**:
   - When running `npm run dev`, Vite starts a local Node.js development server.
   - `laravel-vite-plugin` writes Vite's dev server URL to `public/hot`.
   - On Windows, Node defaults to IPv6 (`[::1]`), so it wrote `http://[::1]:5173` to `public/hot`.
   - Laravel's `@vite` helper reads `public/hot` and injects:
     ```html
     <script type="module" src="http://[::1]:5173/@vite/client"></script>
     <script type="module" src="http://[::1]:5173/resources/js/app.js"></script>
     <link rel="stylesheet" href="http://[::1]:5173/resources/css/app.css" />
     ```
   - **The Issue**: When `npm run dev` stopped or when accessing via Docker web server without an active Node dev server running, the stale `public/hot` remained on disk. Laravel assumed the dev server was still active, causing browsers to try to connect to port 5173 and fail.

2. **Compiled / Production Mode (`public/hot` absent)**:
   - When `npm run build` compiles assets into `public/build/`, Laravel reads [`public/build/manifest.json`](file:///C:/docker/php_projects/kayakapmd_clinic/public/build/manifest.json).
   - In this mode, Laravel generates all URLs using `asset(...)`, which **directly uses the App URL** (`APP_URL`):
     ```html
     <link rel="stylesheet" href="http://localhost:10000/kayakapmd_clinic/build/assets/app-DBo_cllb.css" />
     <script type="module" src="http://localhost:10000/kayakapmd_clinic/build/assets/app-CdyEeg2F.js"></script>
     ```
   - All assets are served natively through Apache port 10000 without requiring any Node.js dev server or port 5173.

---

## User Review Required
> [!NOTE]
> The user confirmed the preferred strategy:
> 1. Remove the orphan `public/hot` file so the application serves compiled production assets directly from the **App URL** (`http://localhost:10000/kayakapmd_clinic/build/assets/...`).
> 2. Configure [`vite.config.js`](file:///C:/docker/php_projects/kayakapmd_clinic/vite.config.js) with clean `server.host: 'localhost'` and `server.origin: 'http://localhost:5173'` so that when `npm run dev` is actively run, it generates a clean `http://localhost:5173` dev URL instead of `[::1]`.
> 3. Update [`setup.sh`](file:///C:/docker/php_projects/kayakapmd_clinic/setup.sh) cleanup trap to automatically delete `public/hot` upon stopping.

---

## Proposed Changes

### Component 1: Vite Configuration

#### [MODIFY] [`C:\docker\php_projects\kayakapmd_clinic\vite.config.js`](file:///C:/docker/php_projects/kayakapmd_clinic/vite.config.js)
Configure the Vite development server to bind explicitly to `localhost` and specify a clean `origin`:

```javascript
// Detailed comment: Configure Vite dev server to bind explicitly to localhost
server: {
    host: 'localhost',
    port: 5173,
    strictPort: true,
    origin: 'http://localhost:5173',
    watch: {
        ignored: ['**/storage/framework/views/**'],
    }
},
```

---

### Component 2: Stale Hot File Cleanup & Script Update

#### [DELETE] [`C:\docker\php_projects\kayakapmd_clinic\public\hot`](file:///C:/docker/php_projects/kayakapmd_clinic/public/hot)
Delete the orphan hot file so Laravel immediately switches to serving compiled assets via the App URL.

#### [MODIFY] [`C:\docker\php_projects\kayakapmd_clinic\setup.sh`](file:///C:/docker/php_projects/kayakapmd_clinic/setup.sh)
Update the `cleanup()` function in `setup.sh` to remove `public/hot` when terminating the services:

```bash
cleanup() {
    echo -e "\n${COLOR_YELLOW}Terminating services...${COLOR_RESET}"
    if [ -n "$SERVE_PID" ] && kill -0 "$SERVE_PID" 2>/dev/null; then
        kill "$SERVE_PID" 2>/dev/null || true
        wait "$SERVE_PID" 2>/dev/null || true
    fi
    # Detailed comment: Remove Vite hot file so Laravel does not remain in dev mode after exit
    if [ -f public/hot ]; then
        rm -f public/hot
    fi
    echo -e "${COLOR_GREEN}All processes stopped.${COLOR_RESET}"
    exit 0
}
```

---

## Verification Plan

### Automated Tests
1. **Verify Hot File Removal**:
   ```powershell
   Test-Path C:\docker\php_projects\kayakapmd_clinic\public\hot
   ```
   *Expected*: `False`.

2. **Verify HTML Asset Tags in Login Page**:
   ```powershell
   curl.exe -i http://localhost:10000/kayakapmd_clinic/login
   ```
   *Expected*:
   - NO occurrences of `[::1]:5173` or port `5173`.
   - Asset URLs rendered using `http://localhost:10000/kayakapmd_clinic/build/assets/...`.

3. **Verify Asset HTTP 200 OK**:
   Extract CSS and JS URLs from the rendered login page and fetch them:
   ```powershell
   curl.exe -I http://localhost:10000/kayakapmd_clinic/build/assets/vendor-20mIKp7c.css
   curl.exe -I http://localhost:10000/kayakapmd_clinic/build/assets/app-CdyEeg2F.js
   ```
   *Expected*: `HTTP/1.1 200 OK`.

### Manual Verification
1. Open `http://localhost:10000/` in the browser.
2. Open Browser Developer Tools (F12) -> Network tab.
3. Verify that all CSS and JS files load with status `200 OK` from `http://localhost:10000/kayakapmd_clinic/build/assets/...` and there are no network connection failures.
