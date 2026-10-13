# KayakapMD Clinic Workflow Skill (Local Snapshot)

This skill guides the agent through the standard operating procedures, architectural guidelines, and operational rules for the **KayakapMD Clinic** consultation system.

## Quick References
- **Rules Specification**: rules.md
- **Authoritative Database Data Dictionary**: .gemini/database/kayakapmdv2_data_dictionary.md
- **Developer Architecture Guide**: docs/ARCHITECTURE.md
- **Setup Script**: setup.sh

---

## Mandatory Pre-Execution Workflow
1. Explore the Codebase First (Prioritize Graft MCP)
2. Review Context & Markdown Documentation Files
3. Consult kayakapmdv2_data_dictionary.md for All Database Logic
4. Dual-Update Schema Changes (migration + data dictionary)
5. Update setup.sh on Initial Script Impacts
6. Utilize Laravel Testing & Structured Logging
7. Persist Implementation Plans & Walkthroughs to .gemini/

## User Global Standards
1. Detailed Comments on all code additions/changes
2. Conventional GitHub Commit Message upon task completion
3. Clarifications (max 3) if requirements unclear
4. Search first before creating new helpers/models
