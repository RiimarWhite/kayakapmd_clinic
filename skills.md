<!--
  Detailed Comment: Project Operational Skills and Execution Playbooks for KayakapMD Clinic.
  Provides step-by-step procedures, tool invocation patterns, command snippets, and diagnostic
  guides for executing every operational rule defined in rules.md.
-->
# KayakapMD Clinic — Developer & AI Operational Skills

This guide contains step-by-step operational playbooks and procedures corresponding to [rules.md](file:///C:/docker/php_projects/kayakapmd_clinic/rules.md). AI agents and developers should follow these playbooks to ensure reliable, disciplined, and standardized execution across the codebase.

---

## Skill 1: Codebase Exploration & Graft MCP Playbook

Use this playbook to explore the repository, discover definitions, and map dependencies without blind guessing or reading entire files.

### Step 1: High-Level Repository Orientation
When starting work on an unfamiliar area or broad feature:
1. **Graft Repository Map**:
   - Call `graft_repo_map` (or CLI `npx @nanonets/graft map`) to view directory clusters, hubs, and hotspots.
2. **Review High-Level Architecture**:
   - Inspect [docs/ARCHITECTURE.md](file:///C:/docker/php_projects/kayakapmd_clinic/docs/ARCHITECTURE.md) to understand routing, controllers, blade templates, and API structure.

### Step 2: Semantic & Symbol Search via Graft
1. **Find Code Spans**:
   - Call `graft_find_code` with a specific symbol, model, or feature name (e.g. `"EnlistmentApiController"`, `"dd_diag_cbc"`, `"SoapService"`).
   - Use the returned line numbers (`covers: file:line`) to view only the exact crux of the code.
2. **Skim Definition Skeletons**:
   - Call `graft_file_api` on a target file to view class signatures, public methods, and properties without loading large files into context.
3. **Analyze Impact & Callers (Blast Radius)**:
   - Call `graft_trace_calls` with symbol name and direction:
     - `Direction: "in"` to see who calls this function/class before modifying it.
     - `Direction: "out"` to see what dependencies this function/class relies on.

### Step 3: Fallback Code Searching
If a file or pattern is not yet indexed in `graft/`:
- Use `grep_search` with exact query or regex.
- Use `find_by_name` to locate specific file types or filenames across directories.

---

## Skill 2: Context & Documentation Review Playbook

Before generating an implementation plan or writing code, systematically review context files to ensure alignment with existing decisions.

### Checklist:
1. **Database Documentation**:
   - Read relevant table definitions in [.gemini/database/kayakapmdv2_data_dictionary.md](file:///C:/docker/php_projects/kayakapmd_clinic/.gemini/database/kayakapmdv2_data_dictionary.md).
2. **Project Context & Notes**:
   - Check [NOTES.md](file:///C:/docker/php_projects/kayakapmd_clinic/NOTES.md) for critical environment pointers (e.g., `APP_URL`, `vite.config.js` `base`, logout endpoint).
   - Check [TODO.md](file:///C:/docker/php_projects/kayakapmd_clinic/TODO.md) to see active, pending, or completed modules.
3. **Previous Implementation Plans & Walkthroughs**:
   - Check `.gemini/implementation plans/` (e.g., `setup_script_and_docker_db_plan.md`, `investigate_and_configure_vite_url_plan.md`, `enable_app_logging_and_auth_fix_plan.md`).
   - Check `.gemini/walkthroughs/` for previous fixes and verified solutions.
4. **Architectural Guides**:
   - Check [docs/ARCHITECTURE.md](file:///C:/docker/php_projects/kayakapmd_clinic/docs/ARCHITECTURE.md) for standard MVC + Vite + API patterns.
   - Check [docs/setup_guide.md](file:///C:/docker/php_projects/kayakapmd_clinic/docs/setup_guide.md) for environment requirements.
   - Check [docs/PAGINATION_USAGE.md](file:///C:/docker/php_projects/kayakapmd_clinic/docs/PAGINATION_USAGE.md) when building paginated tables or lists.

### Mandatory Artifact Persistence:
- **Implementation Plan Saving**: Every plan formulated during planning mode or before significant code changes must be saved to `.gemini/implementation plans/<plan_name>.md`.
- **Walkthrough Saving**: Every post-execution walkthrough detailing changes made, test results, and verification steps must be saved to `.gemini/walkthroughs/<walkthrough_name>.md`.

---

## Skill 3: Database Consultation & Data Dictionary Workflow

Whenever writing Eloquent queries, models, form requests, or migrations, consult [.gemini/database/kayakapmdv2_data_dictionary.md](file:///C:/docker/php_projects/kayakapmd_clinic/.gemini/database/kayakapmdv2_data_dictionary.md) using this procedure:

### Step 1: Locate the Target Table in the 15 Functional Modules
1. Module 1: System / Framework (Laravel) Tables (`users`, `sessions`, `jobs`, `migrations`, `cache`)
2. Module 2: Facility & Access Configuration (`kayakapmd_profile`, `hci_profile`)
3. Module 3: Doctors, Secretaries & Staff (`doctors`, `doctorcharges`, `docschedules`, `docquestion`, etc.)
4. Module 4: Patient Master Data & Walk-in Consultations (`pxmasterlist`, `pxwalkinconsultation`, etc.)
5. Module 5: Billing, Charges & Settlements (`pxcharges`, `charges`, `charges_category`, `pxsettlements`)
6. Module 6: Pharmacy & Inventory (`stocks_listing`, `stocks_ledger`)
7. Module 7: PhilHealth TSEKAP — Enlistment & APE Profile (`dd_enlistment`, `dd_profile`, etc.)
8. Module 8: PhilHealth TSEKAP — SOAP Consultation Notes (`dd_soap_consultation`, `dd_soap_diagnosis`, etc.)
9. Module 9: PhilHealth TSEKAP — Diagnostic/Laboratory Exam Results (`dd_cbc`, `dd_chestxray`, `dd_fbs`, etc.)
10. Module 10: PhilHealth TSEKAP — Diagnostic Results (Billing-enriched / Transmittal copies: `dd_diag_*`)
11. Module 11: `dd_` Module Reference Libraries (`dd_ref_*`, `dd_icd`, `dd_advice`)
12. Module 12: `dw_lib_` Clinical Reference Libraries (`dw_lib_diagnostic`, `dw_lib_medicine`, etc.)
13. Module 13: Geographic Reference / PSGC (`lib_region`, `lib_province`, `lib_city`, `lib_barangay`)
14. Module 14: PhilHealth / HMO Compliance & e-Claims Transmittal
15. Module 15: Uncategorized

### Step 2: Validate Columns and Types
- Verify column names against the table markdown:
  - Do NOT assume standard Laravel timestamp columns (`created_at`, `updated_at`) exist on legacy `dd_*` or `px*` tables unless confirmed in the dictionary.
  - Check whether `id` is an auto-increment integer or nullable bigint.
  - Note de-facto join keys: `dw_clientcode` / `clientcode`, `pHciCaseNo` / `en_CaseNo`, `pHciTransNo` / `s_TransNo`, `px_pin`.

### Step 3: Align Eloquent Models
In `app/Models/`:
```php
class DiagCbcModel extends Model
{
    // Explicitly define the table name matching the data dictionary
    protected $table = 'dd_diag_cbc';

    // Disable timestamps if not present in schema
    public $timestamps = false;

    // Define primary key if non-standard
    protected $primaryKey = 'id';

    // Define fillable fields according to the data dictionary columns
    protected $fillable = [
        'dw_clientcode',
        'px_pin',
        'en_CaseNo',
        's_TransNo',
        'hematocrit',
        'hemoglobin',
        // ...
    ];
}
```

---

## Skill 4: Schema Evolution & Migration Dual-Update Protocol

When creating or modifying database tables, follow this dual-update protocol:

### Step 1: Create a Defensive, Idempotent Migration
Run:
```bash
php artisan make:migration <descriptive_migration_name>
```

In the migration class, always wrap modifications with `hasTable` and `hasColumn` checks:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Detailed Comment: Adds new columns to table XYZ with idempotency checks.
     */
    public function up(): void
    {
        if (Schema::hasTable('table_name')) {
            Schema::table('table_name', function (Blueprint $table) {
                if (!Schema::hasColumn('table_name', 'new_column')) {
                    $table->string('new_column', 100)->nullable()->after('existing_column');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('table_name')) {
            Schema::table('table_name', function (Blueprint $table) {
                if (Schema::hasColumn('table_name', 'new_column')) {
                    $table->dropColumn('new_column');
                }
            });
        }
    }
};
```

### Step 2: Test Migration Application and Rollback
Test both up and down transitions:
```bash
php artisan migrate
php artisan migrate:rollback --step=1
php artisan migrate
```

### Step 3: Update Data Dictionary
Update [.gemini/database/kayakapmdv2_data_dictionary.md](file:///C:/docker/php_projects/kayakapmd_clinic/.gemini/database/kayakapmdv2_data_dictionary.md):
- Navigate to the table entry.
- Add or update the row in the column table:
  ```markdown
  | Column | MySQL Type | Null | Default | Inferred Meaning / Application Notes |
  |---|---|---|---|---|
  | `new_column` | `varchar(100)` | YES | `NULL` | Explanation of purpose and usage |
  ```

---

## Skill 5: `setup.sh` Maintenance & Validation Playbook

Whenever making changes that affect the runtime environment, dependencies, database setup, or server start commands, update [setup.sh](file:///C:/docker/php_projects/kayakapmd_clinic/setup.sh).

### Maintenance Matrix:
| Trigger / Change Type | Affected `setup.sh` Section | Action Required |
|---|---|---|
| New composer package / PHP version update | Section 1 (Prerequisites) & Section 4 (`composer install`) | Update PHP version check or composer command arguments if needed |
| New frontend package / Node update | Section 1 (Prerequisites) & Section 5 (`npm install`) / Section 8 (`npm run build`) | Update node/npm checks or build script flags |
| New required environment variable in `.env.example` | Section 2 (Environment Configuration) | Add interactive prompt and `update_env_key` call with a sensible default |
| New critical seeder or migration order change | Section 7 (Database Migration & Seeder) | Add conditional execution (e.g. `php artisan db:seed --class=NewSeeder`) |
| New background service or dev server parameter | Section 9 (Concurrent Server Launch) | Update `trap cleanup` and start commands |

### Validation:
After modifying `setup.sh`:
- Run syntax check: `bash -n setup.sh`
- Test with the no-serve flag: `bash setup.sh --no-serve` (or `./setup.sh --no-serve`) to verify prerequisite checks, dependency checks, and migrations execute smoothly without hanging.

---

## Skill 6: Laravel Testing & Logging Diagnostics Playbook

### Running Automated Tests
- Run all tests:
  ```bash
  php artisan test
  ```
- Run a specific test suite or test class:
  ```bash
  php artisan test --filter=ExampleTest
  ```
- Run directly via PHPUnit:
  ```bash
  vendor/bin/phpunit
  ```

### Writing Feature & Unit Tests
When adding a feature or endpoint:
1. Create test:
   ```bash
   php artisan make:test Feature/FeatureNameTest
   ```
2. Test response status, JSON structure, and database persistence:
   ```php
   public function test_api_returns_expected_json_structure(): void
   {
       $response = $this->postJson('/fetch_feature_data');
       $response->assertStatus(200)
                ->assertJsonStructure(['data']);
   }
   ```

### Logging for Debugging
1. **Add Structured Logs in Code**:
   Always include context arrays:
   ```php
   use Illuminate\Support\Facades\Log;

   Log::info('Initiating PhilHealth patient enlistment', [
       'clientcode' => $clientcode,
       'caseno'     => $caseNo,
       'pin'        => $patientPin,
   ]);

   try {
       // Logic
   } catch (\Throwable $e) {
       Log::error('Enlistment processing failed', [
           'error' => $e->getMessage(),
           'file'  => $e->getFile(),
           'line'  => $e->getLine(),
       ]);
       throw $e;
   }
   ```
2. **Reviewing Logs**:
   - Inspect the latest entries in `storage/logs/laravel.log`:
     ```powershell
     Get-Content storage/logs/laravel.log -Tail 50
     ```
     Or in bash:
     ```bash
     tail -n 50 storage/logs/laravel.log
     ```

---

## Skill 7: Code Commenting & Git Commit Playbook

### Detailed Code Comments
On every file edit or new file:
- Add a top-of-file header explaining the file's role and architectural connection.
- Add descriptive inline comments explaining the *rationale* behind non-obvious code:
  ```php
  // Detailed Comment: Check if the patient is already enlisted in the current year episode
  // to avoid creating duplicate case numbers under PhilHealth TSEKAP guidelines.
  ```

### Conventional Git Commit Message
At the conclusion of every task, generate a structured commit message following this format:
```text
<type>(<scope>): <short imperative description>

- Detailed bullet point explaining key change 1
- Detailed bullet point explaining key change 2
- Verification steps executed

Types: feat, fix, docs, refactor, test, chore, perf
```
Example:
```text
docs(guidelines): establish rules.md and skills.md operational framework

- Add rules.md defining mandatory pre-execution checklist and operational rules
- Add skills.md providing step-by-step playbooks for Graft MCP, DB data dictionary, and setup.sh
- Configure Antigravity native skill definition under .agents/skills/kayakapmd-workflow/
```
