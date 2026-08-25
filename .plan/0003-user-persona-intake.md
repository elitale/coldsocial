# 0003 — User persona intake (onboarding wizard)

- **Status:** Scoped
- **Branch:** `feature/0003-user-persona-intake`
- **Issue:** [#3](https://github.com/elitale/coldsocial/issues/3)
- **PR:** #<pending>
- **ICP persona:** All three (Priya, Marcus, Sofia). Every ICP must hand coldsocial a rich
  picture of who they are before we can generate content that sounds like them.

## Context

The foundation (0002) shipped auth, the shadcn UI kit, and the app shell. Nothing yet knows
_who the user is_. Before coldsocial can generate platform-native posts (the core product), it
needs a **persona**: the user's goals, voice, interests, audience, sensitivities, and social
presence. This feature captures that in a **simple, stepped wizard** and stores it as a
`Persona` linked to the user. The richer the persona, the better every future generation is —
so we ask a lot, but make it painless (mostly radios, chips, and multi-select; everything
optional; clear steps; a short "why we ask" note for trust).

> This slice only **captures and stores** the persona. Generating content from it, connecting
> platform OAuth, and importing existing posts are separate, later features.

## User story

> As **a new coldsocial user**, I want **to tell coldsocial about my goals, voice, interests
> and social accounts through a quick guided flow**, so that **the posts it generates sound
> like me and reach the right audience**.

## Problem / pain (in the user's words)

"If this is going to post as me, it has to actually know me — what I care about, how I sound,
who I'm trying to reach, and what to never say. But I'm not filling in a giant boring form.
Make it quick, mostly clicks, and tell me why you need this."

## In scope — the wizard

A guided, **multi-step** onboarding wizard (one question-group per step) with a visible
progress indicator, Back/Next, "Skip for now", and a persistent **"Why we ask"** note at the
bottom. Everything is optional; the user can finish anytime. Revisiting pre-fills saved data.

### Persona dimensions (go deep — mostly options, not typing)

1. **Primary goal** — what they want to achieve (single-select radio cards):
   Entrepreneur / founder · Tech thought leader · Creator / influencer · Business executive ·
   Coach / consultant · Personal brand for career · Community builder · Sell products/services ·
   Other (free text).
2. **Professional context** — headline/role (input), industry (select, long list),
   experience level (radio), company/brand (input, optional), location (input),
   languages (multi-select).
3. **Target audience** — who they want to reach (multi-select chips: founders, developers,
   marketers, investors, executives, students, general public, …) + free-text description.
4. **Voice & personality** — tone (multi-select: professional, casual, witty, inspirational,
   bold/opinionated, friendly, authoritative, empathetic), personality archetype (radio: the
   Expert, Storyteller, Motivator, Contrarian, Educator, Entertainer), emoji usage (radio:
   none / minimal / lots), formality (radio or slider).
5. **Interests & topics** — interests (multi-select chips: AI, startups, SaaS, marketing,
   design, finance, crypto, productivity, leadership, health, fitness, travel, food, sports,
   gaming, music, art, science, education, sustainability, …) + top **content pillars** (pick
   3–5).
6. **Likes & dislikes** — content styles they like (chips) + topics/words to **avoid**
   (chips / short text).
7. **Politics & sensitivity** — political stance in content (radio: keep it apolitical /
   occasionally / openly political), political leaning (radio, **optional**, includes "prefer
   not to say"), comfort with controversial topics (radio).
8. **Values & causes** (optional) — causes they support (multi-select chips).
9. **Social presence** — profile links per platform (URL inputs: LinkedIn, X/Twitter,
   Instagram, TikTok, YouTube, Facebook, Threads, GitHub, Substack/blog, personal website),
   primary platform (radio), platforms to focus on (multi-select).
10. **Content preferences** — preferred formats (multi-select: short text, long-form,
    carousels, video, images), posting frequency (radio: daily / few times a week / weekly).
11. **Anything else** — free-text bio.

### Suggested step grouping (≈6 steps, "Step X of N" + progress bar)

1. **Your goal** — primary goal (radio cards).
2. **About you** — professional context + target audience.
3. **Your voice** — tone, archetype, emoji, formality.
4. **Interests & lines** — interests, content pillars, likes, dislikes, politics & sensitivity,
   values.
5. **Your socials** — platform links, primary + focus platforms, formats, frequency.
6. **Review & finish** — summary + submit.

### The "Why we ask" note (trust)

A small, always-visible note at the bottom of the wizard (a dedicated component + a short code
comment explaining intent), e.g.:

> **Why we ask** — coldsocial uses your answers to write posts that sound like you and land
> with the right audience. The more you share, the more on-brand and relevant your content.
> Everything is optional, private to your account, and never shared.

## shadcn components

- **Already available:** card, button, input, label, badge, checkbox, select, separator, tabs,
  tooltip, dropdown-menu.
- **Add (via `shadcn add`, `--overwrite` to avoid the prompt hang):** `radio-group`,
  `progress`, `textarea`. (Optional: `slider` for formality — only if used.)

## Backend

- **Model + migration:** `Persona` (one-to-one with `User`, `user_id` FK, cascade on delete).
  - Typed columns: `primary_goal`, `headline`, `industry`, `experience_level`, `location`,
    `company`, `personality_archetype`, `emoji_usage`, `formality`, `political_stance`,
    `political_leaning`, `controversy_comfort`, `primary_platform`, `posting_frequency`,
    `bio`, `completed_at`.
  - JSON (array/map cast) columns: `languages`, `audiences`, `audience_note`, `tones`,
    `interests`, `content_pillars`, `likes`, `dislikes`, `values`, `content_formats`,
    `focus_platforms`, `social_links` (map of platform → url).
- **Relationship:** `User::persona()` (hasOne); `Persona::user()` (belongsTo).
- **Controller:** `PersonaController@edit` (render wizard with existing persona) and
  `@update` (validate + upsert, set `completed_at`).
- **Form Request:** `PersonaUpdateRequest` — all fields nullable; `in:` rules for enums;
  `url` rules for each social link; arrays validated with `*` element rules.
- **Routes (named, Wayfinder):** `GET /onboarding` → `onboarding.edit`;
  `PATCH /onboarding` → `onboarding.update` (auth + verified). Also linkable from the user
  menu as "Edit persona". Regenerate Wayfinder and call from the frontend (no hardcoded URLs).
- **Factory + seeder:** `PersonaFactory` (with a realistic filled state) for tests.

## Deliberately out of scope (YAGNI)

- **Generating content** from the persona (that's the next core feature).
- **Platform OAuth / connecting accounts** — we only capture profile links here.
- **Importing/scraping** existing posts or profiles to auto-fill.
- **Forcing** new users through onboarding (a redirect gate) — capture is available at
  `/onboarding`; making it mandatory is a later tweak.
- **Persona versioning/history**, AI tone analysis, or per-platform persona variants.
- Any field/option no step above actually renders.

## Acceptance criteria (testable)

Wizard / UX
- [ ] An authenticated (verified) user can open `/onboarding`; guests are redirected to login.
- [ ] The wizard shows discrete **steps** with a progress indicator and Back/Next; the user can
      move between steps without losing entered data.
- [ ] Goal, personality, emoji, politics, primary platform use **radios**; interests, tones,
      audiences, values, formats, focus platforms use **multi-select** (chips/checkboxes).
- [ ] A persistent **"Why we ask"** note is visible at the bottom of the flow.
- [ ] Every field is optional — the user can submit with only a subset filled.
- [ ] Re-opening `/onboarding` **pre-fills** previously saved answers.

Persistence / backend
- [ ] Submitting saves a `Persona` for the user with typed + JSON fields populated, and sets
      `completed_at`.
- [ ] Changing an existing persona updates the same record (one persona per user).
- [ ] Validation rejects invalid social URLs and out-of-range enum values.

Quality
- [ ] Screens use shadcn components (radio-group, progress, textarea, checkbox, card, badge …),
      themed, light + dark.
- [ ] `composer ci:check` is green (ESLint, Prettier, tsc, Pint, PHPStan, Pest).

## Test plan (Pest — feature tests)

- `OnboardingTest`: page renders for a verified user; guest → login; unverified → verification.
- `PersonaUpdateTest`: PATCH creates a persona with typed + JSON fields; second PATCH updates
  the same record; `completed_at` set.
- Validation: invalid social URL rejected; invalid enum (`primary_goal`, `political_stance`,
  …) rejected; arrays accept valid element values.
- Pre-fill: `edit` returns the saved persona to the page props.
- Use `User` + new `Persona` factories; `RefreshDatabase` (Postgres `testing` DB).

## Design notes (SOLID/KISS)

- Thin controller; validation in `PersonaUpdateRequest`; persistence via
  `user->persona()->updateOrCreate()`. No premature service layer for a single call site.
- Keep the wizard as one Inertia page with local step state (`useState`) submitting once at the
  end via a single `useForm`/`<Form>`; don't build a multi-request stepper yet (YAGNI).
- Store flexible multi-value dimensions as cast JSON; promote to columns only if a later feature
  needs to query them.
- Treat all input as untrusted; validate URLs and enums server-side.

## Skills to activate during build

`inertia-react-development`, `tailwindcss-development`, `wayfinder-development`,
`laravel-best-practices`, `pest-testing`. Use Boost `search-docs` for any version-specific API.

## Definition of Done

- [ ] All acceptance criteria met and checked off in the PR.
- [ ] Pest tests cover the criteria and pass (`php artisan test`).
- [ ] `composer ci:check` green.
- [ ] No speculative scope; only rendered fields/options exist.
- [ ] PR merged, issue closed, `.github/memory.md` updated.

## Suggested build order

1. Backend: `Persona` model + migration + factory; `User::persona()`.
2. `PersonaUpdateRequest` + `PersonaController` (edit/update) + `/onboarding` routes; Wayfinder.
3. Add shadcn `radio-group`, `progress`, `textarea`.
4. Wizard page (steps, progress, radios/chips, "Why we ask" note) wired to one `useForm`.
5. Pre-fill from saved persona; user-menu "Edit persona" link.
6. Pest tests; `composer ci:check` green; self-review; mark criteria done.
