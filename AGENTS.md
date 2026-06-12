# Agent Instructions

This repository is the Livelatch Laravel/LinkStack fork. Follow the existing app patterns and keep changes scoped to the requested behavior.

## Change Summary

After making code, config, migration, route, view, asset, tooling, or documentation changes, append a dated entry to `summary.md`.

Each entry should briefly cover:

- what changed
- why it changed
- files or work areas affected
- validation performed

Keep entries concise. As `summary.md` grows, compress older detailed entries into shorter dated summaries instead of deleting history.

Maintain the current `summary.md` format:

- keep **Recent Changes** at the top
- use `### YYYY-MM-DD` date headings
- write short bullets under each date
- keep the fork-history section separate from recent work
- when adding commit-history details, list exact commit hashes only for the fork-specific range being discussed
- do not reintroduce inherited upstream LinkStack history into the fork summary

Manual context notes in `summary.md`:

- lines or bullets beginning with `!!` are manually entered by the repo owner
- treat `!!` notes as additional context and justification to consider when writing reports
- preserve `!!` notes when editing or compressing `summary.md`
- do not rewrite owner-authored `!!` notes unless explicitly asked

## Documentation Updates

When significant change is made, update the documentation library under `docs/` or add a new entry there.

A change counts as significant when any 2 of the following are true:

- a commit of 100 or more lines of code is made to a file
- a new system is introduced
- a system is updated
- a new integration is defined
- another change clearly adds enough product, architecture, operational, or workflow context that future readers would benefit from documentation

When documentation is updated:

- keep it human readable so someone without deep technical knowledge can still understand what changed and why it matters
- format all code blocks correctly and always include the language
- when a product is mentioned, include a link to its public webpage when one is known
- include references whenever possible
- make sure the entry gives enough context that someone reading older entries can still understand the significance
- do not change documentation formatting conventions unless the repo owner explicitly approves it

Before adding or editing documentation:

- quickly review related existing docs in `docs/` to confirm they are still relevant
- if related docs appear stale, inconsistent, or incomplete, tell the repo owner what should be updated and ask permission before changing that existing documentation format or content beyond the requested scope

After completing work:

- ask the repo owner if they want the documentation updated when the change meets the significant-change threshold, or when there is a reasonable case that the docs should be refreshed

Agent-facing implementation notes for this workflow may be stored in `agent-docs-instructions.md`. Future agents may use and update that file as an internal reference.

## Owner Todo Follow-Ups

Todo requests for the repo owner live under `docs/todos/`.

At the start of a work session, check whether `docs/todos/` exists and contains Markdown files. If it does, inspect each file for a `todo-check` metadata block:

```text
<!-- todo-check
created_at: 2026-06-12T19:34:32+08:00
ask_after: 2026-06-13T19:34:32+08:00
status: open
-->
```

Compare `ask_after` with the current date/time from the active environment context. If `ask_after` is in the past or equal to now, ask the repo owner whether the existing todos have been completed before continuing broad new work.

If the owner says yes, delete the completed todo file or the whole `docs/todos/` folder when all todo files are completed. If the owner says no, update the file's `ask_after` value to 24 hours after the current environment time and keep the todo open. Preserve the rest of the todo content.

When new owner follow-up work is discovered, create a new Markdown file in `docs/todos/` with a clear title, concrete checklist, and a `todo-check` block using the current environment time plus a 24-hour `ask_after`.

## Open Graph Images

When implementing generated Open Graph preview cards from the internal editor output, assume the production output should be PNG unless explicitly instructed otherwise.
