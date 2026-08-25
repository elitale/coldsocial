# Contributing to coldsocial

Thanks for your interest in improving coldsocial! This guide explains how we work and how to
get a change merged. It complements the [README](README.md) (project overview + setup) and
[`AGENTS.md`](AGENTS.md) (tech conventions). Please skim all three before your first PR.

By participating you agree to keep interactions respectful and constructive, and that your
contributions are licensed under the project's [MIT License](LICENSE).

---

## Ways to contribute

- **Fix an open issue** — see [Working on an open issue](#working-on-an-open-issue).
- **Propose a feature** — open an issue describing the *user need* so it can be scoped into a
  plan (see [Proposing a new feature](#proposing-a-new-feature)).
- **Report a bug** — see [Reporting bugs](#reporting-bugs).
- **Improve docs** — small documentation fixes are welcome as direct PRs.

## Before you start

1. Read the [README](README.md) and get the app running locally.
2. Read [`AGENTS.md`](AGENTS.md) and **activate the matching skill** in
   [`.agents/skills/`](.agents/skills/) before working in a domain (Laravel, Pest,
   Inertia+React, Tailwind, Wayfinder, Fortify).
3. Understand how we ship: the three-role **agent loop**
   ([`.github/agent.md`](.github/agent.md)) and the golden rule below.

> **One feature = one plan = one branch = one PR = one issue. No plan, no code.**

Every feature is specified in [`.plan/<NNNN>-<slug>.md`](.plan/) **before** any code is written.
The issue links its plan, and the plan is the single source of truth for scope and acceptance
criteria.

## Development setup

Follow [Getting started](README.md#getting-started) in the README. Verify your environment with
the same gate CI uses:

```bash
composer ci:check
```

If that's green (ESLint, Prettier, `tsc`, Pint, PHPStan, Pest), you're ready.

## Working on an open issue

1. **Pick an issue.** Browse the [open issues](https://github.com/elitale/coldsocial/issues).
   New here? Look for issues labeled `good first issue`.
2. **Read the linked plan** in [`.plan/`](.plan/). It defines the scope and the acceptance
   criteria you must satisfy — nothing more, nothing less.
3. **Claim it.** Comment on the issue so work isn't duplicated. We ship **serially** — avoid
   starting a second feature while one is in `Building` or `InReview`.
4. **Branch** from an up-to-date `main`:

   ```bash
   git switch main && git pull
   git switch -c feature/<NNNN>-<slug>   # matches the plan/issue, e.g. feature/0005-generate-linkedin-week
   ```

5. **Build the smallest slice** that meets the acceptance criteria, test-first with Pest. Reuse
   existing components before adding new ones.
6. **Keep it green** and formatted as you go:

   ```bash
   composer ci:check
   vendor/bin/pint --dirty && npm run format && npm run lint
   ```

7. **Open a PR** (see [Pull requests](#pull-requests)).

## Proposing a new feature

Describe the **need**, not a solution — who it's for and why it matters — mirroring how our ICP
personas think ([`.github/agents/icp.md`](.github/agents/icp.md)):

> As a **\<persona\>**, I want **\<capability\>**, so that **\<outcome\>**.

Add draft acceptance criteria phrased as observable outcomes. A maintainer (Product Owner) then
cuts it to the smallest valuable slice, writes a [`.plan`](.plan/_template.md), and opens the
issue you can build against. This keeps scope tight and avoids speculative work.

## Making changes

- **Follow the conventions** in [`AGENTS.md`](AGENTS.md) and the active skill for your domain.
- **Backend:** thin controllers; validation in Form Requests; business logic in Actions/Services
  only when a second consumer exists; typed properties, constructor promotion, explicit return
  types.
- **Frontend:** Inertia `useForm`/`<Form>`; typed routes from Wayfinder (`@/routes`,
  `@/actions`) — **never hardcode URLs**; themed shadcn components (light + dark).
- **New platform integrations** sit **behind an existing contract** (e.g. `PublishesContent`,
  `ReportsMetrics`) resolved via the container — never a `switch` over platform names.
- **Secrets** (OAuth tokens, API keys) via config/env only; never hardcode or log them; treat
  all platform API responses as untrusted.

## Testing

- Use **Pest**; feature tests are preferred. Create with `php artisan make:test --pest <Name>`.
- Use **factories** and `RefreshDatabase`. Tests run against a dedicated Postgres `testing`
  database — never touch dev data.
- Add or update a test for **each acceptance criterion** your change touches.

```bash
php artisan test              # full suite
php artisan test --filter=Xyz # one file/test
```

## Commit messages

Small, focused commits. Conventional-style prefixes are appreciated:

```
feat(persona): add custom social links
fix(onboarding): keep entered data when moving between steps
test(persona): cover invalid custom link URLs
docs(readme): document Sail setup
refactor: extract persona summary builder
```

## Pull requests

Open your PR against `main`. The description must:

- **Link the plan** (`.plan/<NNNN>-<slug>.md`) and summarize the change.
- **Close the issue** with `Closes #<issue-number>`.
- **Check off every acceptance criterion** from the plan.
- Include screenshots for UI changes (light + dark where relevant).

Then ensure:

- [ ] `composer ci:check` is green.
- [ ] Pest tests cover the acceptance criteria and pass.
- [ ] No speculative scope (YAGNI) and no single-consumer abstraction (KISS) was introduced.
- [ ] New platform work sits behind existing contracts (no `switch` on platform).
- [ ] Secrets via env/config; no tokens logged.

Maintainers review, request changes if needed, and **squash-merge** once CI is green and the
acceptance criteria are met. The issue auto-closes, and the shipped feature is validated against
the original user need.

**Never** commit straight to `main`, and **never** open a PR without a passing local
`composer ci:check`.

## Reporting bugs

Open an issue with:

- What you expected vs. what happened.
- Steps to reproduce (a failing Pest test is ideal).
- Environment: OS, PHP/Node versions, native vs. Sail.
- Relevant logs or screenshots.

## Security

**Do not** open public issues for security vulnerabilities. Report them privately through the
repository's GitHub security advisories. Handle credentials via env/config only and never log
tokens.

## License

By contributing, you agree that your contributions are licensed under the project's
[MIT License](LICENSE).
