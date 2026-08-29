# 0044 — Studio P1: text LinkedIn composer with a live preview + schedule

## Metadata

- **Status:** InReview
- **Sequence / slug:** `0044` / `studio-text-preview`
- **Branch:** `feature/0044-studio-text-preview` (off `main`)
- **Issue:** #65 · **Epic:** #64 · **PR:** _pending_
- **ICP persona:** Priya — compose a confident, on-brand LinkedIn post
- **Depends on:** #14 (schedule), persona/voice, `TextGenerator`

## Context / background

First slice of the Post Generator Studio (#64): a composer for a **LinkedIn text post** with a
**live, LinkedIn-accurate preview**, then **save draft** or **schedule** (reusing #14). Other
platforms/formats show as "coming soon". "Post now" waits on publishing (#19).

## In scope — the smallest valuable slice

- `GET /studio` composer (LinkedIn + Text fixed/active; other platforms/formats disabled).
- AI **Generate** (`GenerateStudioDraft`: persona voice + optional prompt → caption + hashtags,
  reusing `PersonaVoice` + `TextGenerator`); graceful inline error with no text model.
- Composer: caption editor + live **character counter** vs the LinkedIn limit + live **hashtag
  count/guidance** (hashtags inline in the body).
- **`LinkedInPreview`** component (feed-accurate, hashtag highlighting, see-more, engagement bar,
  light + dark).
- **Save draft** / **Schedule** (reuse #14's tz → UTC via a shared `ScheduleTime` helper). **Post
  now** disabled ("coming with #19").
- **`PlatformSpec`** registry (LinkedIn: charLimit 3000, hashtags 3–5) shared by client + server.

## Deliberately out of scope (YAGNI)

- No `PostFormat` column (text-only → arrives in P3 with a 2nd format).
- No preview registry (render `<LinkedInPreview>` directly → registry in P7).
- Media/upload, image/video/carousel, other platforms, multi-publish, post-now (#19).

## Acceptance criteria (testable)

- [ ] `GET /studio` renders `studio/index` (LinkedIn + Text) with the spec + a live preview.
- [ ] Generate returns a caption + 3–5 hashtags from persona voice + optional prompt; no model → a
      friendly inline error, nothing saved.
- [ ] Char counter warns past the limit; hashtag count reflects `#tokens` with 3–5 guidance.
- [ ] Preview renders the caption LinkedIn-accurately (name/avatar, hashtag highlight, see-more,
      engagement bar), light + dark.
- [ ] Save draft creates a `linkedin` draft; Schedule creates a `scheduled` post with UTC
      `scheduled_at` from the user's tz (reuses #14); past time rejected; owner-scoped; guests →
      login.
- [ ] `composer ci:check` green.

## Design notes (SOLID / KISS)

- `PlatformSpec::for(SocialPlatform)` → `{charLimit, hashtagMin, hashtagMax}` — one source of truth.
- `GenerateStudioDraft->for(User, ?prompt)` mirrors `GenerateLinkedInDraft` but from a prompt.
- `ScheduleTime::fromUserInput(input, tz): ?Carbon` — extracted from #14's schedule (now 2 consumers:
  `PostController@schedule` + `StudioController@store`).
- `StudioController` — `create` / `generate` (flashes the caption back) / `store` (draft or
  scheduled). `LinkedInPreview` rendered directly (no registry yet).

## Data & backend

- **No migration.** Routes (auth+verified+persona): `GET /studio` (create), `POST /studio/generate`,
  `POST /studio` (store). `store` validates `body` (required ≤ spec limit) + optional `scheduled_at`.

## Frontend

- `resources/js/pages/studio/index.tsx` (composer + preview), `components/previews/linkedin-preview.tsx`,
  Wayfinder `studio.*`, **Studio** sidebar item.

## Test plan (Pest)

- `StudioTest`: guest → login; `create` renders with the spec; `generate` returns a caption (AI
  faked) + errors with no model; `store` creates a draft; `store` + future `scheduled_at` → scheduled
  (Asia/Kolkata → UTC); past rejected; owner-scoped.

## Definition of Done

- [ ] Criteria met; Pest green (AI faked); `composer ci:check` green; ledger updated.
