# Project Agent Rules — Aplikasi Absensi SMK

## 1. Source of truth
- Read `PROMPT.md` before starting project work.
- Treat `PROMPT.md`, approved documents in `docs/`, and explicit user decisions as the project source of truth.
- If instructions conflict, stop the affected work, explain the conflict, and ask for a decision.
- Documents marked `DRAFT — BELUM DISETUJUI` are planning drafts, not permission to implement.

## 2. Mandatory approval gate
Until the user explicitly approves the relevant plan, you may inspect the repository and prepare planning documentation only. Do not write application code, install packages, create or mutate a Supabase project/schema, run migrations, seed data, connect live services, or deploy.

At the end of planning, present decisions already made, unresolved questions and assumptions, security/operational risks, planning files prepared, and the exact approval requested. An ambiguous "continue" is not approval to code. For material changes after approval, explain the delta and request approval before implementation.

## 3. Skill loading and token efficiency
Before each task, read only the relevant `SKILL.md` files in full. Load more than one skill when tasks cross domains; do not paste every skill into each prompt. Never claim to have read a missing/unreadable skill.

Expected project skills:
- `.agents/skills/attendance-system-planner/SKILL.md`
- `.agents/skills/school-attendance-ui-ux-designer/SKILL.md`
- `.agents/skills/database-architect/SKILL.md`
- `.agents/skills/full-stack-developer/SKILL.md`
- `.agents/skills/application-security-engineer/SKILL.md`
- `.agents/skills/qa-testing-engineer/SKILL.md`
- `.agents/skills/devops-deployment-engineer/SKILL.md`
- `.agents/skills/reporting-analytics-engineer/SKILL.md`
- `.agents/skills/Minimal_Code_Engineering_SKILL/Minimal_Code_Engineering_SKILL.md`
- `.agents/skills/security-supply-chain-guardian/SKILL.md`
- `.agents/skills/ui-ux-kit/SKILL.md` (optional third-party design skill, use for UI tasks only).

Use `docs/PROJECT_STATUS.md` for a concise current-state summary and `docs/DECISIONS.md` for decisions. Do not repeat full skill contents in status notes or create duplicate skills without need.

## 4. Repository safety
- Start by checking the repository root, Git status, existing files, README, local agent instructions, and available skills.
- Treat the project as empty only after checking it.
- Do not delete, overwrite, or reformat user changes without permission.
- Never display or commit secrets from `.env`, connection strings, tokens, private keys, database dumps, or student/staff personal data.
- Use synthetic data for examples and tests.

## 5. Minimal code and controlled scope
For implementation and refactoring, use `.agents/skills/Minimal_Code_Engineering_SKILL/Minimal_Code_Engineering_SKILL.md`.
- Before installing/reviewing third-party skills, accessing any secrets, running Composer/PHP updates, making network calls that expose project data, or preparing a release, use `.agents/skills/security-supply-chain-guardian/SKILL.md`.
- Prefer the smallest sufficient, correct, safe and testable change.
- Reuse Laravel/framework capabilities before creating custom infrastructure.
- Do not add unrelated features, dependencies, layers, abstractions, or files without a concrete need.
- Avoid giant files and trivial one-class-per-file fragmentation; file size is a review signal, not a strict metric.
- Do not minify source, remove meaningful comments, weaken tests, or sacrifice readability/security to hit a line-count target.
- Work in small, reviewable modules and summarize the diff and test results.

## 6. Third-party UI design skill
`ui-ux-kit` should be located at `.agents/skills/ui-ux-kit/SKILL.md` with its `references/` directory inside the same skill folder. It is optional and must not be assumed installed until verified.

Before using it:
- Read its `SKILL.md` and relevant reference files.
- Review license and contents before adapting/copying files.
- Use it alongside `school-attendance-ui-ux-designer`; the school-specific requirements remain authoritative.
- Maintain `docs/UI_DESIGN_SYSTEM.md`.
- Do not copy a whole third-party website design. Extract principles and create a design that fits the school.
- Do not let visual treatment override accessibility, clarity, performance, or approved workflows.

## 7. Project-specific guardrails
- Proposed stack, subject to approval: PHP 8.3+ / Laravel 13, Blade + Livewire + Tailwind CSS, Supabase PostgreSQL, Laravel-managed user authentication, manual and Excel/CSV imports, dashboard/PDF/Excel reports, WhatsApp text preview with human-confirmed sending.
- Render Free Web Service + Docker is the chosen target for prototype/testing only; production hosting is not selected. Do not create a Render service, connect GitHub, add secrets, run migrations, or deploy until explicitly approved.
- Render Free web services sleep after 15 minutes idle, wake with a cold start, and have ephemeral filesystem; don't store persistent uploads/database data on local disk. Free tier is not for production.
- Prepare deployment using a Dockerfile for PHP/Laravel, listen on `0.0.0.0` at Render's configured port, use Render Dashboard environment variables, keep `.env`/secrets out of Docker images, and define a safe health check.
- Do not run Laravel migrations automatically on every startup/deploy. Establish an explicit, reviewed migration and rollback process.
- Student Training and DW are special statuses, neither classroom Present nor Absent.
- Student attendance, teacher/staff attendance, daily agenda, and lesson journal are separate concepts.
- Missing data is not proof of absence.
- WhatsApp creates a preview and opens WhatsApp with prefilled text. The human chooses the recipient and presses Send. Never claim the message was sent.
- `Asia/Makassar` is only a proposed timezone pending confirmation.
- Never expose database credentials or privileged Supabase keys to the browser.

## 8. Quality after approval
- Follow approved specifications, relevant skills, repository conventions, and official docs for selected versions.
- Enforce validation and authorization server-side.
- Use transactions for atomic multi-record attendance operations where appropriate.
- Reconcile dashboard, PDF, Excel and WhatsApp metrics to the same source.
- Use synthetic fixtures, never real student records.
- Run relevant tests and report actual results.
- Do not claim success when tests were not run.
- Summarize changed files, rationale, tests, risks, and next decision.


## 9. Offline mode and data synchronization
- The school has weak/intermittent signal. Read `docs/OFFLINE_MODE_SPEC.md` before designing or implementing offline support.
- Offline support is not just a banner or a cached page. Use a PWA/service worker for the minimum app shell, IndexedDB for local drafts/outbox, and a server-side idempotent sync protocol with per-record acknowledgements and conflict handling.
- Do not cache private API/authenticated HTML broadly; minimize the student data stored on a device.
- Clearly distinguish local-pending from server-confirmed records.
- Background Sync is optional only; manual sync and retry when the app becomes active/online must work.
- Do not build offline functionality until scope, local-data retention, logout behavior, conflict policy and target browsers are approved.
- Reports must not be considered complete when required data is still pending synchronization.
