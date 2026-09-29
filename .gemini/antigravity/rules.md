<!--
  Detailed Comment: Project Operational Rules and Development Guidelines for KayakapMD Clinic.
  Defines mandatory, non-negotiable rules for all AI agents and developers before planning,
  writing, modifying, testing, or committing code in this repository.
-->
# KayakapMD Clinic — Developer & AI Operational Rules

This document establishes the mandatory operational rules, constraints, and protocols for **KayakapMD Clinic**. Every AI agent and developer must strictly adhere to these rules on **every prompt, feature build, bugfix, refactoring, and update** before touching any code.

---

## Pre-Execution Workflow Checklist

Before modifying any code or running any state-altering commands, complete this mandatory checklist in order:

```
[ ] 1. Explore Codebase First (Use Graft MCP tools / inspect definitions before modifying)
[ ] 2. Review Context & Markdown Documentation (All .md in .gemini/ and project root)
[ ] 3. Consult kayakapmdv2_data_dictionary.md (Authoritative DB source of truth)
[ ] 4. Check Schema Changes (Dual-update: migration + data dictionary if altering schema)
[ ] 5. Check Setup Script Impact (Update setup.sh if dependencies, env, or steps change)
[ ] 6. Utilize Laravel Testing & Structured Logging (Verify fixes and record diagnostic logs)
[ ] 7. Save Implementation Plans & Walkthroughs to .gemini/ (Mandatory persistence)
[ ] 8. Add Detailed Code Comments & Provide Git Commit Message (Standard user rules)
```

---

## Core Operational Rules

### Rule 1: Explore the Codebase First Before Modifying Code (Prioritize Graft MCP)
- **Zero Blind Edits**: Never guess file paths, function signatures, class namespaces, or variable structures. Always explore the codebase first.
- **Use Graft MCP Tools When Available**:
  - Call `graft_repo_map` first when orienting to a feature area or exploring directory clusters.
  - Call `graft_find_code` or `graft_find_all` with semantic queries and literal symbols (`Controller`, `Model`, table names, error strings).
  - Call `graft_file_api` to skim the definition skeleton (classes, methods, parameters) without loading the entire file into context.
  - Call `graft_trace_calls` (both inbound and outbound directions) to evaluate blast radius and callers before refactoring.
- **Fall Back to Code Search**: When Graft MCP is not applicable or working with unindexed files, use targeted ripgrep (`grep_search`) and directory exploration (`find_by_name`, `list_dir`).
- **Respect Established Architecture**: Follow the request flow documented in [docs/ARCHITECTURE.md](file:///C:/docker/php_projects/kayakapmd_clinic/docs/ARCHITECTURE.md):
  `Route (routes/web.php) → Controller → Blade View (@push('scripts')) → JS (axios) → API Route (routes/api.php) → Controller/Service → Eloquent Model → JSON Response`.

---

### Rule 2: Review Context, Markdown Documentation & Mandatory Persistence Protocols
- **Mandatory Document Inspection**: On every prompt, task, or feature implementation, inspect all relevant markdown documentation files before planning or executing:
  1. **`.gemini/` Directory**:
     - [.gemini/database/kayakapmdv2_data_dictionary.md](file:///C:/docker/php_projects/kayakapmd_clinic/.gemini/database/kayakapmdv2_data_dictionary.md): Primary data dictionary.
     - [.gemini/settings.json](file:///C:/docker/php_projects/kayakapmd_clinic/.gemini/settings.json): Environment and tool settings.
     - `.gemini/implementation plans/*.md`: Historical implementation plans and context on past architectural decisions.
     - `.gemini/walkthroughs/*.md`: Records of completed tasks, solutions to prior bugs (e.g. Vite asset URL configuration, Docker 403 handling).
  2. **Project Root Markdown Files**:
     - [README.md](file:///C:/docker/php_projects/kayakapmd_clinic/README.md): Project overview, requirements, and basic setup instructions.
     - [NOTES.md](file:///C:/docker/php_projects/kayakapmd_clinic/NOTES.md): Critical developer notes (e.g., APP_URL, vite.config.js base URL, logout URL).
     - [TODO.md](file:///C:/docker/php_projects/kayakapmd_clinic/TODO.md): Feature roadmap and module progress (Secretary, Doctor, Admin).
     - [docs/ARCHITECTURE.md](file:///C:/docker/php_projects/kayakapmd_clinic/docs/ARCHITECTURE.md): Application architecture, routing rules, naming conventions, and file structure.
     - [docs/setup_guide.md](file:///C:/docker/php_projects/kayakapmd_clinic/docs/setup_guide.md): Environment details, PHP requirements, and seed data instructions.
     - [docs/PAGINATION_USAGE.md](file:///C:/docker/php_projects/kayakapmd_clinic/docs/PAGINATION_USAGE.md): Pagination conventions and guidelines.
- **Mandatory Plan & Walkthrough Persistence Protocol**:
  - **Implementation Plans**: Whenever creating or updating an implementation plan, you **must persist it as a Markdown file in `.gemini/implementation plans/<plan_name>.md`** in addition to any system artifact. Never rely solely on transient chat memory.
  - **Walkthroughs**: Upon completing and verifying any implementation, you **must persist the walkthrough as a Markdown file in `.gemini/walkthroughs/<walkthrough_name>.md`**.
- **Align with Existing Standards**: Do not introduce conflicting design patterns, libraries, or configurations that violate documented conventions.

---

### Rule 3: Authoritative Database Source of Truth
- **Consult Data Dictionary First**:
  - [.gemini/database/kayakapmdv2_data_dictionary.md](file:///C:/docker/php_projects/kayakapmd_clinic/.gemini/database/kayakapmdv2_data_dictionary.md) is the **authoritative single source of truth** for this application's database.
  - Whenever implementing code changes involving database queries (raw SQL or Query Builder), Eloquent models, validation rules, or API payloads, you **must consult the data dictionary** to verify exact table names, column names, nullability, default values, and data types.
- **Beware of Legacy vs. V2 Schemas**:
  - The database contains two parallel generations of tables (e.g., `dd_enlistment` vs. `dd_enlistment_1`, `dd_diagnosticexamresult` vs. `dd_diag_examresult_master`, and `dd_<exam>` vs. `dd_diag_<exam>`).
  - Always verify whether the active feature uses the legacy format or the billing-enriched `dd_diag_*` format.
- **De-Facto Join Keys**:
  - Verify recurring cross-table identifiers (`dw_clientcode` / `clientcode`, `pHciCaseNo` / `en_CaseNo`, `pHciTransNo` / `s_TransNo`, `px_pin` / `pxrefno`, `docrefno`, `consultationrefno`).

---

### Rule 4: Dual-Update Schema Synchronization Protocol
- **Synchronous Updates Required**:
  - Every schema alteration (adding/modifying/dropping tables, columns, indexes, foreign keys, or enum values) **strictly requires updating both**:
    1. **A new Laravel database migration** in `database/migrations/`.
    2. **The Markdown data dictionary** in [.gemini/database/kayakapmdv2_data_dictionary.md](file:///C:/docker/php_projects/kayakapmd_clinic/.gemini/database/kayakapmdv2_data_dictionary.md).
- **Migration Requirements**:
  - Always use defensive, idempotent checks (`Schema::hasTable`, `Schema::hasColumn`) in migrations to prevent failures in existing environments.
  - Ensure the `down()` method cleanly rolls back the change.
- **Data Dictionary Requirements**:
  - Update the matching table definition under the relevant module in [.gemini/database/kayakapmdv2_data_dictionary.md](file:///C:/docker/php_projects/kayakapmd_clinic/.gemini/database/kayakapmdv2_data_dictionary.md).
  - Document column type, nullability, default, and descriptive commentary.

---

### Rule 5: Environment & Setup Script (`setup.sh`) Integrity
- **Maintain Automated Setup**:
  - [setup.sh](file:///C:/docker/php_projects/kayakapmd_clinic/setup.sh) is the primary automated onboarding and deployment script.
  - If any change affects:
    - System prerequisites (PHP version, Composer, Node.js, npm).
    - Environment variables (new entries in `.env.example` or required defaults).
    - Database setup (new essential seeders like `AdminSeeder` or baseline migrations).
    - Package dependencies (`composer.json` or `package.json`).
    - Build or dev server processes (Vite commands, Apache aliases, port mappings).
  - You **must update [setup.sh](file:///C:/docker/php_projects/kayakapmd_clinic/setup.sh)** in lockstep with your changes.
- **Preserve Portability & Safety**:
  - Keep `setup.sh` idempotent and compatible across Windows (Git Bash / MSYS2 / WSL) and Linux / Docker container environments.
  - Never break the `--no-serve` flag or service cleanup traps.

---

### Rule 6: Utilize Laravel Testing & Structured Logging
- **Testing for Validation and Regression Prevention**:
  - For every new feature or bugfix, write or update relevant tests in `tests/Feature/` or `tests/Unit/`.
  - Execute tests using `php artisan test` or `./vendor/bin/phpunit` before marking any task as complete.
  - Ensure newly added logic does not break existing test suites.
- **Structured Logging for Debugging**:
  - Do not use arbitrary `echo`, `print_r`, or `var_dump` in production or backend code.
  - Use Laravel's `Illuminate\Support\Facades\Log` facade:
    - `Log::info('Action performed', ['user_id' => $id, 'context' => $data]);`
    - `Log::error('Operation failed', ['error' => $exception->getMessage(), 'trace' => $exception->getTraceAsString()]);`
  - Inspect `storage/logs/laravel.log` during debugging, troubleshooting, and test runs to capture backend exceptions and warnings.

---

### Rule 7: User Global Standards & Code Integrity
- **Detailed Code Comments**:
  - Add detailed, explanatory comments on **every code change and code addition**.
  - Explain *why* the change is made, any non-obvious business logic, and any integration requirements.
- **Clarifying Questions**:
  - If anything is ambiguous, underspecified, or conflicting, ask the user clarifying questions (maximum 3 questions) rather than making assumptions.
- **Search Before Assuming**:
  - Always search the codebase to locate existing implementations and patterns before adding duplicate utilities or models.
- **Git Commit Message**:
  - Upon completing a task, provide a clear, standardized conventional Git commit message formatted with type, scope, and bullet points describing key modifications.
