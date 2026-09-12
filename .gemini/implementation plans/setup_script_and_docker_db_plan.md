# Implementation Plan: Setup Bash Script & Docker Database Configuration

## Goal Description
Create a standardized bash setup script (`setup.sh`) for the KayakapMD project that:
1. Validates required system prerequisites:
   - Node.js installed
   - Composer installed
   - PHP installed and PHP version is `>= 8.2`
2. Checks for `.env`. If missing:
   - Copies `.env.example` to `.env`
   - Sets a flag indicating `.env` was created during this run
   - Interactively prompts the user for:
     - `DB_CONNECTION` (default: `mysql`)
     - `DB_HOST` (default: `127.0.0.1`)
     - `DB_PORT` (default: `3306`)
     - `DB_DATABASE` (default: `kayakapmdv2`)
     - `DB_USERNAME` (default: `root`)
     - `DB_PASSWORD` (default: empty / prompt)
     - `APP_URL` (default: `http://localhost:8000`)
   - Includes intelligent connectivity diagnostics: tests MySQL connectivity to `DB_HOST:DB_PORT`. If connection fails and Docker containers (`latest_php_server` / `mysql-db`) are detected, alerts the user to container networking nuances (e.g. `mysql-db:3306` for container-to-container, or `127.0.0.1:4406` / `host.docker.internal:4406` if accessing the mapped Docker port).
3. Executes application setup commands in sequence:
   - `composer install`
   - `npm install`
   - `php artisan key:generate`
   - `php artisan migrate`
   - `php artisan db:seed --class=AdminSeeder` (executed **only** if `.env` was newly created during this run)
   - `npm run build`
   - Starts both `php artisan serve` and `npm run dev` concurrently with clean `trap` shutdown on Ctrl+C.
4. Fixes database migration and seeder issues:
   - Guard migrations so they are idempotent (`Schema::hasTable` checks) to avoid `1050 Table already exists` or `1146 Table doesn't exist`.
   - Ensure `AdminSeeder` is safe against duplicate entries.

---

## User Review Required

> [!IMPORTANT]
> **Prerequisites Verification:**
> Per requirements, `setup.sh` strictly validates:
> - `node` & `npm` availability
> - `composer` availability
> - `php` availability and `PHP_VERSION_ID >= 80200` (PHP >= 8.2)
> If running on an environment where any of these are missing or below version 8.2, `setup.sh` displays a clear error and exits with code 1.

> [!NOTE]
> **DB_HOST Diagnostic in Docker vs Host:**
> - When running on Host: MySQL container `mysql-db` maps internal 3306 to host `4406`. If the user specifies `127.0.0.1:3306`, but only port `4406` is open from Docker, the script proactively diagnoses this and suggests `DB_PORT=4406`.
> - When running inside Docker: `127.0.0.1` refers to the container itself. The script detects if running inside a container and advises using `DB_HOST=mysql-db` on port `3306`.

---

## Proposed Changes

### 1. Root Directory

#### [NEW] [setup.sh](file:///C:/docker/php_projects/kayakapmd_clinic/setup.sh)
A comprehensive, portable bash script compatible with Git Bash, WSL, and Linux/Docker environments.
- Implements strict prerequisite checks (Node.js, Composer, PHP >= 8.2).
- Interactively configures `.env` with requested defaults (`DB_HOST=127.0.0.1`, `DB_PORT=3306`, etc.).
- Verifies DB connection and offers helpful diagnostic hints if unreachable.
- Runs:
  1. `composer install`
  2. `npm install`
  3. `php artisan key:generate`
  4. `php artisan migrate`
  5. `php artisan db:seed --class=AdminSeeder` (conditional on `.env` created)
  6. `npm run build`
  7. Concurrently spawns `php artisan serve` and `npm run dev` with signal trapping (`trap` cleanup on SIGINT/SIGTERM/EXIT).

---

### 2. Database Migrations

#### [MODIFY] [database/migrations/0001_01_01_000000_create_users_table.php](file:///C:/docker/php_projects/kayakapmd_clinic/database/migrations/0001_01_01_000000_create_users_table.php)
- Guard `users`, `password_reset_tokens`, and `sessions` table creations with `if (!Schema::hasTable(...))`.

#### [MODIFY] [database/migrations/0001_01_01_000001_create_cache_table.php](file:///C:/docker/php_projects/kayakapmd_clinic/database/migrations/0001_01_01_000001_create_cache_table.php)
- Guard `cache` and `cache_locks` table creations with `if (!Schema::hasTable(...))`.

#### [MODIFY] [database/migrations/0001_01_01_000002_create_jobs_table.php](file:///C:/docker/php_projects/kayakapmd_clinic/database/migrations/0001_01_01_000002_create_jobs_table.php)
- Guard `jobs`, `job_batches`, and `failed_jobs` table creations with `if (!Schema::hasTable(...))`.

#### [MODIFY] [database/migrations/2026_01_23_015002_make_adminrights.php](file:///C:/docker/php_projects/kayakapmd_clinic/database/migrations/2026_01_23_015002_make_adminrights.php)
- Guard `adminrights` table creation with `if (!Schema::hasTable('adminrights'))`.

#### [MODIFY] [database/migrations/2026_05_12_094818_add_clientcode_to_tables.php](file:///C:/docker/php_projects/kayakapmd_clinic/database/migrations/2026_05_12_094818_add_clientcode_to_tables.php)
- Guard column additions with `if (Schema::hasTable('secretaryrights') && !Schema::hasColumn('secretaryrights', 'clientcode'))` and `if (Schema::hasTable('adminrights') && !Schema::hasColumn('adminrights', 'clientcode'))`.

---

### 3. Database Seeders

#### [MODIFY] [database/seeders/AdminSeeder.php](file:///C:/docker/php_projects/kayakapmd_clinic/database/seeders/AdminSeeder.php)
- Prevent duplicate `admin` user insertion by checking `if (!AdminModel::where('username', 'admin')->exists())`.

---

## Verification Plan

### Automated / Command Verification
1. **Prerequisites Check Verification**:
   - Run `bash setup.sh` with varying conditions (e.g. valid vs invalid PHP version) to verify prerequisite checks catch incompatibilities and report helpful guidance.
2. **Interactive .env Configuration**:
   - Test `.env` generation by backing up existing `.env` and running with inputs.
   - Verify all keys (`DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `APP_URL`) are correctly written.
3. **Database Migrations & Seeder**:
   - Run `php artisan migrate` to ensure no collisions occur with existing tables.
   - Run `php artisan db:seed --class=AdminSeeder` to ensure idempotency.
4. **Build & Execution**:
   - Run `npm run build` and verify output.
   - Verify concurrent serve & dev execution and clean termination.
