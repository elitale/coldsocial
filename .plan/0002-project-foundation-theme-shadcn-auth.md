# 0002 — Project foundation: design system, shadcn/ui, and authentication

- **Status:** Scoped
- **Branch:** `feature/0002-project-foundation`
- **Issue:** [#1](https://github.com/elitale/coldsocial/issues/1)
- **PR:** [#2](https://github.com/elitale/coldsocial/pull/2)
- **ICP persona:** All three (Priya, Marcus, Sofia) — every ICP must sign in before any
  content feature exists. This is the enabling foundation the rest of the loop builds on.

## Context

`coldsocial` is the Laravel **blank** React starter kit: Tailwind v4, Inertia v3, React 19,
Wayfinder, Pest. The UI/auth layer was stripped out, but the hooks remain — `app.blade.php`
already renders a `$appearance` dark-mode class and loads pages per-component, `lib/utils.ts`
already exports `cn()`, `types/auth.ts` already declares `User`/`Auth`, and
`HandleInertiaRequests` is registered. This feature restores the standard, production-ready
foundation so real product features (content generation, publishing, metrics) have a themed,
authenticated shell to live in.

> This is deliberately a larger _enabling_ feature — you cannot ship "half of auth." Scope is
> held to the **standard starter-kit auth surface**, with explicit YAGNI cuts below. New
> platforms and product features remain separate, later plans.

## User story

> As **any coldsocial user**, I want **to create an account, sign in securely, and manage my
> profile in a polished themed UI**, so that **my social presence and data are private to me
> and the app feels trustworthy from the first screen**.

## Problem / pain (in the user's words)

"If I'm going to connect my LinkedIn and let this thing post as me, I need to trust it. That
starts with a real login, my own account, and an app that doesn't look like a raw form."

## In scope — feature breakdown

### A. Design system / CSS theme

- Tailwind v4 theme tokens in `resources/css/app.css` using CSS variables (`oklch`) for
  **light and dark**: background, foreground, card, popover, primary, secondary, muted,
  accent, destructive, border, input, ring, chart, and sidebar tokens.
- `@theme inline` mapping tokens → Tailwind color utilities; `.dark {}` variable overrides.
- Radius scale (`--radius`) and animation utilities (accordion/collapsible keyframes).
- Sensible default sans font (keep Instrument Sans already wired via Bunny fonts).

### B. shadcn/ui setup

- Add `components.json` (style `new-york`, base color `neutral`, `@/components`,
  `@/lib/utils`, lucide icons) so future `shadcn add` works.
- Add UI dependencies: `class-variance-authority`, `lucide-react`, and the `@radix-ui/*`
  primitives actually used below (`clsx` + `tailwind-merge` already present).
- Scaffold **only the primitives these screens need** (YAGNI): `button`, `input`, `label`,
  `card`, `checkbox`, `dropdown-menu`, `avatar`, `separator`, `dialog`/`alert-dialog`,
  `tooltip`, `icon`, `skeleton`. Add more later when a screen needs them.

### C. Appearance / dark-mode system

- `HandleAppearance` middleware sharing the `appearance` cookie → blade `$appearance`.
- `use-appearance` hook + `initializeTheme()` (light / dark / system, persisted to cookie +
  localStorage, reacts to OS changes).
- Appearance toggle available in settings and the user menu.

### D. Inertia app bootstrap

- Ensure `resources/js/app.tsx` mounts React, resolves pages from `./pages/**`, wires the
  progress bar, and calls `initializeTheme()` on boot. Confirm the blank kit actually renders
  (add `resolve`/`setup` if missing).

### E. Layouts

- `auth` layout (centered card, logo, for login/register/password screens).
- `app` layout (authenticated shell: sidebar + header, user menu, responsive, sidebar state
  persisted via cookie).
- `settings` layout (nested tabs: profile / password / appearance).

### F. Authentication (Laravel **Fortify**, headless)

Rationale: Fortify is the official headless auth backend the Laravel React starter kit uses;
it pairs cleanly with Inertia pages and leaves us owning the React UI. Install + configure,
back it with Inertia pages and controllers/actions:

- **Register** — name, email, password + confirmation; creates user, logs in, fires
  `Registered` (sends verification email).
- **Login** — email/password, "remember me", throttled; validation errors surfaced.
- **Logout** — invalidates session.
- **Forgot password** — request reset link.
- **Reset password** — token + new password.
- **Email verification** — notice screen, signed verify link, resend.
- **Confirm password** — gate for sensitive actions.

### G. Settings

- **Profile** — update name/email; changing email resets verification.
- **Password** — update (requires current password).
- **Appearance** — light/dark/system.
- **Delete account** — requires password confirmation.

### H. Shared Inertia props

- `HandleInertiaRequests` shares `auth.user`, app `name`, `sidebarOpen`, and flash
  (`status`/errors) to every page.

### I. Routing & Wayfinder

- `web.php`: public `welcome`; authenticated+verified `dashboard` landing; settings routes;
  Fortify routes registered. Use **named routes**; regenerate Wayfinder types and call them
  from the frontend (no hardcoded URLs).

### J. Dashboard placeholder

- Minimal authenticated `dashboard` page (empty-state shell) so login has a destination.
  Real product widgets come in later plans.

## Deliberately out of scope (YAGNI)

- **Two-factor authentication** — Fortify supports it; add as its own later plan when an ICP asks.
- **Social/OAuth sign-in** (Google/GitHub). Distinct from social-platform publishing OAuth,
  which is its own future feature.
- **Teams / organizations / roles / permissions** — single-user accounts only for now.
- **Avatar upload / media storage** — use initials placeholder; no uploads yet.
- **Any social-media product feature** (content generation, scheduling, publishing, metrics).
- **Production SSR server** — dev SSR via `@inertiajs/vite` is automatic; no prod SSR now.
- **i18n / localization**, custom transactional email design beyond framework defaults.
- Any UI primitive not used by the screens above.

## Acceptance criteria (testable)

Authentication

- [ ] A guest can register; the account is created, they're logged in, and a verification
      email is sent.
- [ ] A registered user can log in with valid credentials and is rejected with an error on
      invalid ones; "remember me" persists the session.
- [ ] A user can log out.
- [ ] A user can request a password-reset link and set a new password via the token.
- [ ] An unverified user hitting a verified-only route sees the verification notice; using the
      signed link marks the email verified.
- [ ] Sensitive routes require password confirmation.

Settings

- [ ] A user can update name/email; changing email clears `email_verified_at`.
- [ ] A user can change their password only by supplying the correct current password.
- [ ] A user can delete their account after confirming their password.
- [ ] A user can switch appearance (light/dark/system) and it persists across reloads.

UI / theme

- [ ] Every auth + settings + dashboard screen renders with shadcn components and theme tokens
      (no raw hex), in both light and dark mode.
- [ ] `dashboard` requires auth **and** a verified email; `welcome` stays public.

Quality gate

- [ ] `composer ci:check` is green (Pint, Prettier, ESLint, PHPStan, Pest).

## Design notes (SOLID/KISS)

- Keep controllers thin; use Fortify actions/features for auth behavior rather than
  hand-rolling. Add app-specific actions (e.g. delete account) as single-responsibility
  invokable actions.
- No premature abstraction: scaffold only the UI primitives the listed screens use.
- Treat all auth input as untrusted; never log credentials or tokens; secrets via env only.
- Prefer named routes + Wayfinder-generated functions over hardcoded paths.

## Test plan (Pest — feature tests preferred)

- `RegistrationTest` — screen renders; user can register; verification notification sent.
- `AuthenticationTest` — login screen renders; login succeeds/fails; logout; remember-me.
- `PasswordResetTest` — request link; reset with valid token.
- `EmailVerificationTest` — notice for unverified; signed link verifies; already-verified
  redirect.
- `PasswordConfirmationTest` — gate prompts and accepts correct password.
- `ProfileUpdateTest` — update profile; email change resets verification.
- `PasswordUpdateTest` — requires correct current password.
- `DeleteAccountTest` — requires password; deletes user + logs out.
- `DashboardTest` — guests redirected to login; unverified redirected to verification;
  verified user sees dashboard.
- Use `RefreshDatabase` and the `User` factory (`unverified()` state where needed).

## Skills / references to activate during build

`inertia-react-development`, `tailwindcss-development`, `wayfinder-development`,
`laravel-best-practices`, `pest-testing`. Use Boost `search-docs` for Fortify + Inertia v3
version-specific APIs before wiring them.

## Definition of Done

- [ ] All acceptance criteria met and checked off in the PR.
- [ ] Pest tests cover the criteria and pass (`php artisan test`).
- [ ] `composer ci:check` is green.
- [ ] No speculative scope; only the primitives/routes the screens require were added.
- [ ] PR merged, issue closed, `.github/memory.md` updated.

## Suggested build order (small, reviewable steps within the branch)

1. Design tokens in `app.css` + `initializeTheme` + `HandleAppearance` + app.tsx bootstrap.
2. `components.json` + UI dependencies + base primitives + `cn` (exists).
3. Layouts (auth, app, settings).
4. Install/configure Fortify; wire register/login/logout + Inertia pages + tests.
5. Password reset + email verification + confirm password + tests.
6. Settings (profile, password, appearance, delete) + tests.
7. Dashboard placeholder + protected routing + shared Inertia props.
8. Wayfinder regenerate, `composer ci:check` green, self-review, mark criteria done.
