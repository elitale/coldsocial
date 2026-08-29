# 0041 — Connections hub (connect social platforms via OAuth, LinkedIn first)

## Metadata

- **Status:** InReview
- **Sequence / slug:** `0041` / `connections`
- **Branch:** `feature/0041-connections` (off `feature/0038` = main + the #56 gate hotfix, since main is red until #56 lands; retargets main after #56)
- **Issue:** #17 · **PR:** _pending_
- **ICP persona:** Priya (solo founder) — connect once, publish later
- **Target platform(s):** LinkedIn (live); Instagram, TikTok, YouTube, Facebook (coming soon)
- **Depends on:** none (parallel track). Enables #18 (disconnect), #19/#20 (publish), #22 (metrics)

## Context / background

Drafting works, but coldsocial can't publish without a linked account. This ships the
**Connections hub** — a dedicated page where the user connects social accounts via OAuth and sees,
at a glance, which are **Connected**, **Connect**-able, or **Coming soon**. LinkedIn is the first
real integration; the others are placeholders so future platforms slot in with no UI change.

## User story

> As a **solo founder**, I want to connect my social accounts from one place and see which are
> linked, so coldsocial can publish on my behalf once I approve content.

## In scope — the smallest valuable slice

- A `/connections` page (own sidebar item) rendering a platform **registry** with per-user status.
- Three states: `connected`, `available` (**Connect**), `coming_soon`.
- A working **LinkedIn** OAuth connect flow (redirect + callback) storing an encrypted connection.
- Instagram, TikTok, YouTube, Facebook rendered as **Coming soon**.

### Screens, states & UX

- Responsive card grid; each card = brand icon + name + status action (Connected badge + account
  name / **Connect** button / dimmed **Coming soon** pill). Inline success/error banner after a
  connect attempt. Themed shadcn, light + dark.

## Deliberately out of scope (YAGNI)

- Disconnect (#18); publishing/metrics (#19/#20/#22); posting from this tab.
- Real OAuth for any platform other than LinkedIn (placeholders only).
- Multiple accounts per platform; token auto-refresh scheduling.
- A `ConnectsPlatform` driver interface — introduce with the **second** connectable platform.

## Acceptance criteria (testable, user-facing)

- [ ] `/connections` lists all five platforms, each with the correct state; guests → login.
- [ ] LinkedIn (no connection) shows **Connect**; clicking redirects to LinkedIn's consent screen
      (and stores an OAuth `state`).
- [ ] A successful callback stores an encrypted `PlatformConnection` and flips LinkedIn to
      **Connected** with the account name.
- [ ] Instagram/TikTok/YouTube/Facebook show **Coming soon** and can't start OAuth (404);
      an unknown `{platform}` → 404.
- [ ] Denied/tampered callback → friendly error, no record.
- [ ] Tokens encrypted at rest, never logged or in the client payload.
- [ ] `composer ci:check` green.

## Design notes (SOLID / KISS)

- **Registry (Open/closed):** `SocialPlatform` enum (`Linkedin`/`Instagram`/`Tiktok`/`Youtube`/`Facebook`)
  with `label()` + `connectable()`. The page renders from `SocialPlatform::cases()` — **no `switch`
  on platform names** in the UI. New platform = add a case + flip `connectable()`.
- **Status:** `connected` if a `PlatformConnection` exists; else `available` when `connectable()`;
  else `coming_soon`.
- **KISS connect:** one `ConnectionController` + a small `LinkedInOAuth` service using the `Http`
  client. Defer a `ConnectsPlatform` contract until a second connectable platform (repo rule:
  promote on the 2nd consumer).
- **No Socialite:** the latest Socialite caps at guzzle `^7`, but this app is on guzzle 8 (required
  by the framework). Rather than downgrade a core HTTP lib, LinkedIn OAuth is implemented directly
  with `Http` — **zero new dependencies**, fully faked in tests.

## Data & backend

- **Model:** `PlatformConnection belongsTo User` — `platform` (enum cast), `external_id`,
  `display_name`, `avatar_url?`, `access_token` (**encrypted**, hidden), `refresh_token?`
  (encrypted, hidden), `expires_at?`, `scopes?`. Unique `(user_id, platform)`. Migration + factory.
- **`LinkedInOAuth`:** `redirectUrl(state)` (authorize URL), `connect(code)` (token exchange +
  `/v2/userinfo`) → profile array.
- **Routes** (auth + verified + persona group): `GET /connections` (index),
  `GET /connections/{platform}/redirect`, `GET /connections/{platform}/callback`. `{platform}` is
  bound to the enum (invalid → 404); non-`connectable` → 404.

## Frontend (Inertia v3 + React)

- **`connections/index.tsx`:** card grid from `platforms` props; `PlatformIcon` (brand SVGs);
  **Connect** is a native `<a>` to the redirect route (full-page OAuth navigation); success/error
  banner from local flash props.
- **`components/platform-icon.tsx`:** brand glyphs (LinkedIn/Instagram/TikTok/YouTube/Facebook).
- **Sidebar:** a **Connections** item.

## Security & privacy

- Access/refresh tokens `encrypted` at rest, `Hidden`, never logged, never in Inertia props.
- OAuth `state` stored in session and verified on callback; denied consent → no partial record.
- `{platform}` validated against the enum; only `connectable` platforms can begin OAuth.

## Config / env

- `config/services.php` `linkedin` (client_id / client_secret / redirect) via `.env`
  (`LINKEDIN_CLIENT_ID` / `LINKEDIN_CLIENT_SECRET` / `LINKEDIN_REDIRECT_URI`). Real connects need
  these set; the LinkedIn posting scope (`w_member_social`) may require LinkedIn app review — a
  dependency for publishing (#19), not for connecting.

## Test plan (Pest — feature, `Http::fake`)

- `ConnectionsTest`: guest → login; hub lists 5 with correct states; connected card shows the name;
  tokens encrypted + absent from the client; LinkedIn redirect → consent URL + stored `state`;
  coming-soon/unknown → 404; callback stores an encrypted connection; denied/tampered → nothing.
  Acting users get a persona for the onboarding gate.

## Dependencies / new packages

- none (LinkedIn OAuth via the built-in `Http` client — no Socialite/guzzle churn).

## Definition of Done

- [ ] All acceptance criteria met and checked off in the PR.
- [ ] Pest tests cover the criteria and pass.
- [ ] `composer ci:check` is green.
