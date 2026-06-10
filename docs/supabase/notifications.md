# Notifications

The notification work landed primarily on 2026-06-07 across commits such as `ca416db`, `d0b7698`, `7730ca5`, `912c080`, `28e74ce`, `a65c3d6`, `fb964de`, `f1a3ee8`, and `99ee17f`.

## What changed

- a Livelatch notification service was introduced
- the sidebar began rendering notifications directly
- global notifications were added to the query path
- exception handling improved around failed notification fetches
- debug routes were added during development to inspect Supabase-backed notification data

## Architectural direction

The fork is treating Supabase as more than just auth. It is also becoming a lightweight event and notification data source, with Laravel presenting those events inside the studio shell.

## Current behavior

- the sidebar attempts to fetch recent notifications for the current user
- if the notification fetch fails, the layout falls back safely instead of breaking the shell
- unread counts are surfaced in the UI
- debug routes exist under admin access for inspecting notification data during development

## Why this matters

This work is an early example of cross-system orchestration:

- Supabase stores or exposes the event data
- Laravel shapes the experience and rendering
- the studio shell becomes the operator-facing control surface

## Risks to keep in mind

- debug routes should not survive unchanged into production hardening
- notification payload shape should remain stable if more product areas start publishing events
- if notifications become central to UX, they will eventually need clearer read-state handling and pagination
