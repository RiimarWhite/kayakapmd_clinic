---
name: kayakapmd-workflow
description: >-
  Standard operating procedures and guidelines for KayakapMD Clinic development.
  Use whenever planning, building, modifying, testing, or committing code in this repository.
  Covers codebase exploration with Graft MCP, context and docs review, database data dictionary
  consultation, schema dual-update sync, setup.sh integrity, Laravel testing, and structured logging.
---

<!--
  Detailed Comment: Antigravity Native Skill definition for KayakapMD Clinic.
  Enables automatic progressive disclosure and discovery of project development rules and workflows.
-->
# KayakapMD Clinic Workflow Skill

This skill guides the agent through the standard operating procedures, architectural guidelines, and operational rules for the **KayakapMD Clinic** consultation system.

## Quick References
- **Rules Specification**: [rules.md](../../../rules.md)
- **Execution Playbooks**: [skills.md](../../../skills.md)
- **Authoritative Database Data Dictionary**: [.gemini/database/kayakapmdv2_data_dictionary.md](../../../.gemini/database/kayakapmdv2_data_dictionary.md)
- **Developer Architecture Guide**: [docs/ARCHITECTURE.md](../../../docs/ARCHITECTURE.md)
- **Setup Script**: [setup.sh](../../../setup.sh)

---

## Mandatory Pre-Execution Workflow

Before planning or executing any task in this codebase, complete the following 6 steps in order:

### 1. Explore the Codebase First (Prioritize Graft MCP)
- Do not make blind edits.
- Use `graft_repo_map` to understand directory clusters and hotspots.
- Use `graft_find_code` or `graft_find_all` to pinpoint relevant symbols and code spans.
- Skim signatures with `graft_file_api`.
- Check blast radius and callers with `graft_trace_calls` before refactoring.

### 2. Review Context & Markdown Documentation Files
- Check [.gemini/database/kayakapmdv2_data_dictionary.md](../../../.gemini/database/kayakapmdv2_data_dictionary.md) for data models.
- Check past implementation plans and walkthroughs in `.gemini/implementation plans/` and `.gemini/walkthroughs/`.
- Review project root files: [README.md](../../../README.md), [NOTES.md](../../../NOTES.md), [TODO.md](../../../TODO.md), and [docs/ARCHITECTURE.md](../../../docs/ARCHITECTURE.md).

### 3. Consult kayakapmdv2_data_dictionary.md for All Database Logic
- [.gemini/database/kayakapmdv2_data_dictionary.md](../../../.gemini/database/kayakapmdv2_data_dictionary.md) is the single authoritative source of truth for all database tables, columns, data types, and nullability.
- Differentiate between legacy tables (`dd_enlistment`, `dd_diagnosticexamresult`) and billing-enriched v2 tables (`dd_enlistment_1`, `dd_diag_*`).
- Match join keys (`pHciCaseNo`, `pHciTransNo`, `px_pin`, `clientcode`).

### 4. Dual-Update Schema Changes
Whenever modifying the database schema:
1. Create an idempotent migration in `database/migrations/` using `Schema::hasTable` and `Schema::hasColumn` checks.
2. Update the corresponding table definition in [.gemini/database/kayakapmdv2_data_dictionary.md](../../../.gemini/database/kayakapmdv2_data_dictionary.md).

### 5. Update setup.sh on Initial Script Impacts
- If a change introduces new environment variables, dependencies (`composer.json`, `package.json`), initial seeders, or server runtime behaviors, update [setup.sh](../../../setup.sh) accordingly.
- Keep `setup.sh` idempotent and test with `--no-serve` if modified.

### 6. Utilize Laravel Testing & Structured Logging
- Validate bugfixes and features with automated tests (`php artisan test` or `vendor/bin/phpunit`).
- Use structured logging (`Log::info`, `Log::error` with context arrays) and check `storage/logs/laravel.log`.

### 7. Persist Implementation Plans & Walkthroughs to .gemini/
- Always persist implementation plans as `.md` files in `.gemini/implementation plans/<plan_name>.md`.
- Always persist walkthroughs as `.md` files in `.gemini/walkthroughs/<walkthrough_name>.md`.

---

## User Global Standards
1. **Detailed Comments**: Add detailed, explanatory comments on all code added or changed.
2. **Commit Message**: Output a conventional GitHub commit message upon task completion.
3. **Clarifications**: Ask up to 3 clarifying questions if requirements or edge cases are unclear.
4. **Search First**: Search the codebase before creating new helpers or models.
