# 0042 — Disconnect a platform from the Connections hub

## Metadata

- **Status:** InReview
- **Sequence / slug:** `0042` / `disconnect-platform`
- **Branch:** `feature/0042-disconnect-platform` (stacked on `feature/0041-connections`)
- **Issue:** #18 · **PR:** _pending_
- **ICP persona:** Marcus — stay in control of what coldsocial can access
- **Depends on:** #17 (Connections hub + `PlatformConnection`)

## Context / background

#17 lets a user connect LinkedIn. This closes the loop: from the same **Connected** card, the user
can **disconnect** — revoking coldsocial's access and returning the card to **Connect**.

## User story

> As a **founder**, I want to disconnect a platform from the Connections page, so I stay in control
> of what coldsocial can access.

## In scope — the smallest valuable slice

- A **Disconnect** action on a **Connected** card, behind a confirmation dialog.
- `DELETE /connections/{platform}` deletes the user's `PlatformConnection` (best-effort provider
  token revoke first); the card returns to **Connect** with a success message.

### Screens, states & UX

- Connected card: a **Disconnect** button (outline) → shadcn `AlertDialog` ("Disconnect {platform}?
  coldsocial won't be able to publish to it until you reconnect."). Confirm → the card flips to
  **Connect**; a success banner confirms it.

## Deliberately out of scope (YAGNI)

- Handling in-flight scheduled posts for that platform beyond the eventual publish behaviour
  (#19/#20).
- Soft-delete / connection history; bulk "disconnect all".

## Acceptance criteria (testable, user-facing)

- [ ] A connected platform can be disconnected from its card; the `PlatformConnection` is removed
      and the card shows **Connect** again.
- [ ] Disconnecting requires an explicit confirmation.
- [ ] Owner-only — disconnecting a platform the user hasn't connected → 404; other users' rows are
      untouched.
- [ ] A failed remote revoke still removes the local record (best-effort) and never blocks the user.
- [ ] Guests → login. `composer ci:check` green.

## Design notes (KISS)

- `ConnectionController@destroy`: find the user's connection (`abort 404` if none), best-effort
  `LinkedInOAuth::revoke()` in a `try/catch`, then `delete()`. No soft-delete, no `connectable`
  guard (removing a stored connection is always safe).
- Reuses the `SocialPlatform` enum + hub from #17. New shadcn `AlertDialog` primitive (unified
  `radix-ui` import) for the confirm.

## Frontend (Inertia v3 + React)

- Connected card gains a Disconnect `AlertDialog`; confirm → `router.delete(destroy({ platform }))`
  with `preserveScroll`.

## Security & privacy

- Owner-scoped via `connections()`; enum-bound `{platform}`. Best-effort revoke never leaks or logs
  the token.

## Test plan (Pest — feature, `Http::fake` for revoke)

- `DisconnectConnectionTest`: guest → login; disconnect removes the row + success; not-connected →
  404; cross-user → 404 (their row intact); failed revoke still deletes; disconnecting one platform
  leaves others.

## Dependencies / new packages

- none (adds a local shadcn `AlertDialog` component; no new npm package).

## Definition of Done

- [ ] All acceptance criteria met and checked off in the PR.
- [ ] Pest tests cover the criteria and pass.
- [ ] `composer ci:check` is green.
