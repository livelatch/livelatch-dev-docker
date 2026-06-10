# agent-docs-instructions

Agent-only working notes for the Livelatch documentation workflow.

## Purpose

This file is allowed to be utilitarian rather than human-friendly prose. It exists so future agents can track how docs are expected to evolve without overloading `AGENTS.md`.

## Significant change threshold

Create or update docs in `docs/` when at least 2 of these are true:

- a commit changes 100 or more lines in a file
- a new system is introduced
- an existing system is updated in a meaningful way
- a new integration is defined
- the change adds notable product, architecture, operational, or workflow context

## Default workflow

1. Check whether the completed work crosses the significant-change threshold.
2. Review related docs in `docs/` for relevance before editing.
3. If existing docs look stale or incomplete, mention that in the final response and ask permission before broader doc cleanup.
4. If docs are updated, keep them easy to read for non-experts.
5. Use fenced code blocks with language tags in every doc.
6. Add product links when products are mentioned and a public page is known.
7. Include references where possible.
8. Preserve established formatting unless the owner explicitly approves a formatting change.
9. Update `summary.md` after documentation or instruction-file changes.
10. In the final response, ask whether the owner wants documentation updated whenever the threshold is met or there is a reasonable documentation case.

## Current docs categories

- `docs/livelatch-meta`
- `docs/supabase`
- `docs/latchdeck`
- `docs/stripe`
- `docs/encore`
- `docs/platform-runtime`
- `docs/open-graph`

## Candidate future docs topics

- notifications operating model
- sidebar/HTMX shell architecture
- billing lifecycle and webhook handling once added
- product-area docs for account, community, and analytics surfaces
