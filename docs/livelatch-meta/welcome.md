# Welcome to the Livelatch Documentation Library

This library turns the major work in the Livelatch fork into browsable internal documentation. The content is grounded in the actual fork commit history from `upstream/main..HEAD`, then grouped by the product areas that matter most when extending or operating the platform.

## What this library covers

- Livelatch identity and platform direction
- Supabase LatchID authentication and notifications
- Stripe billing and account provisioning
- LatchDeck studio scaffolding
- Runtime, Railway, S3 media, and public asset behavior
- Open Graph and social preview generation
- Roadmap context for systems that are planned but not yet deeply implemented

## How this documentation system works

- Every document is a standalone Markdown file inside `docs/`
- Every first-level folder becomes a category automatically
- Every `.md` file becomes a page automatically
- The studio page scans `docs/` on request, builds an index, and renders articles through HTMX without refreshing the whole dashboard shell
- Search suggestions are generated client-side from the docs index and update as you type

## Why this matters

The fork has already moved beyond stock LinkStack in several important ways:

- Livelatch branding and platform positioning replaced the default upstream framing
- Supabase now matters as the identity layer for LatchID
- Stripe is the billing source of truth for SaaS onboarding
- S3-backed private media and Open Graph generation support branded creator pages
- The studio shell is evolving toward a product suite rather than a simple link manager

## Suggested reading order

1. `Livelatch Meta / Fork History`
2. `Supabase / LatchID Authentication`
3. `Stripe / Stripe Billing Foundation`
4. `Platform Runtime / S3 Media And Proxy`
5. `Open Graph / Profile Preview System`
6. `LatchDeck / LatchDeck Studio`

## Maintenance note

When you add new project knowledge:

- create a new `.md` file in the right folder
- create a new folder if a new category emerges
- keep the documents focused on architecture, behavior, and operating context rather than raw changelog noise
