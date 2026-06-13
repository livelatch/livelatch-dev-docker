# Agent Summary

This page is the readable companion to `summary.md`. It is meant to turn the fork history into a short, browsable digest that can be shown to people who want to understand what has been changing without reading the full change log.

The rule is simple:

- when `summary.md` changes, update this page too
- keep it high level
- favor clear bullet points over deep implementation detail
- summarize what changed, why it mattered, and which product area it touched

## Current digest

### 2026-06-13

- The TikTok LatchID flow was fixed so TikTok user info responses are parsed correctly and the callback now returns users to Livelatch after linking.
- A future affiliate setup path was documented around Refferq so the repo has a clear direction for a separate affiliate portal later on.
- The Livelatch docs library now has a dedicated affiliate-program reference page and a standing follow-up style for future platform expansion.
- Supabase-backed identity work continued across Discord, YouTube, and TikTok so Studio connections stay organized around one account surface.
- The Studio shell and admin surfaces keep evolving toward a product suite instead of a basic link manager, with more explicit navigation, docs, and tooling.

## Maintenance note

If `summary.md` gains a new dated entry, mirror the top-level themes here in a few bullets and keep the tone readable for non-developers.
