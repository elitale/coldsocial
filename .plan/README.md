# `.plan/` — feature specifications

Every feature starts as a plan here **before any code is written**. One feature = one plan =
one branch = one PR = one issue. This is enforced by the agent loop
([`.github/copilot-instructions.md`](../.github/copilot-instructions.md)).

## Naming

`.plan/<NNNN>-<slug>.md`

- `<NNNN>` — next zero-padded sequence number (`0001`, `0002`, …).
- `<slug>` — short kebab-case description (`generate-linkedin-week`).

The matching branch is `feature/<NNNN>-<slug>` and the GitHub issue links back to the plan.

## Who writes what

- The **Product Owner** ([`.github/agents/product-owner.md`](../.github/agents/product-owner.md))
  creates the plan by copying [`_template.md`](_template.md) and filling every section, cutting
  scope with YAGNI.
- The **Product Developer**
  ([`.github/agents/product-developer.md`](../.github/agents/product-developer.md)) implements
  it and checks off acceptance criteria in the PR.
- The **ICP agent** ([`.github/agents/icp.md`](../.github/agents/icp.md)) validates the shipped
  result against the original need.

## Rules

- **No plan, no code.** If there's no plan file, stop and write one first.
- Every plan must have a non-trivial **"Deliberately out of scope (YAGNI)"** list.
- Acceptance criteria must be testable (a Pest test can assert it, or a reviewer can click it).
- Keep the plan updated if scope legitimately changes — but new needs go back to the ICP as a
  fresh plan, they do not silently grow this one.

See [`0001-example-generate-linkedin-week.md`](0001-example-generate-linkedin-week.md) for a
worked example (illustrative — not an active feature).
