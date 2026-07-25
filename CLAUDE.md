# Claude Code Instructions

You are implementing Oxy AI Readiness.

Before writing or modifying production code, read:

`docs/30-Claude-Code-Master.md`

Then read every documentation file referenced by the Master Execution Contract.

The documentation is the source of truth.

## Mandatory Workflow

1. Inspect the repository.
2. Read all documentation.
3. Create or update the `.project/` control files.
4. Build the implementation map.
5. Report conflicts and unanswered questions.
6. Execute one documented phase at a time.
7. Run tests after every phase.
8. Update progress and decision logs.
9. Never mark placeholder code as complete.
10. Never skip security, authorization, validation, tests or rollback requirements.

## Restrictions

Do not redesign the architecture.

Do not simplify documented engines into a single procedural class.

Do not store all data in WordPress options.

Do not use mock production data.

Do not bypass service contracts.

Do not implement dashboard-only functionality without an API.

Do not run heavy operations during frontend requests.

Do not make destructive changes without snapshots and rollback.

## Current Task

Start with Phase 0 from:

`docs/30-Claude-Code-Master.md`

Complete the documentation analysis only.

Do not start production implementation until Phase 0 is reviewed and approved.

At the end, return:

- Documentation index
- Requirement map
- Architecture dependency map
- Conflicts
- Questions
- Risks
- Proposed phase plan
- Files created or modified