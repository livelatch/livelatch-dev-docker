# Fork History

The Livelatch fork starts by establishing its own runtime, brand direction, and product identity rather than staying a thin skin over upstream LinkStack.

## Significant milestones

### Runtime and deployment stabilization

The earliest fork work focused on getting the app to run in the target environment cleanly. Significant commits include:

- `8e19376`, `fc3db68`, `faeb738`, `a4d041f`: Composer alignment and dependency repair
- `7c92287`, `b75d51d`, `3c7fde5`: Laravel app and route fixes
- `8de0844`: `railpack.json` for Railway deployment
- `7a27f03`: post-deploy Artisan error fixes
- `d602163`: forced clean Railway redeploy

This phase matters because nearly every later feature depends on stable deployment and predictable runtime behavior.

### Platform identity shift

Livelatch stopped presenting itself as stock LinkStack and began documenting itself as a creator platform. The main signal here is:

- `2fa2778`: README revised to introduce Livelatch as a platform

That shift is not cosmetic. It frames later work around products, subscriptions, identity, collectible experiences, and a broader studio surface.

### Documentation and agent conventions

The fork also started formalizing project memory and maintenance expectations:

- `261e398`: created `summary.md` helper for the Datadog agent
- `2be8be7`: added repo-level agent instructions
- `a1c660a`, `071774d`, `7884481`, `111a4a8`: continued summary updates and fork-specific cleanup

## Major product themes that emerged after the fork

- Private S3-backed media instead of simple local avatar assumptions
- Generated Open Graph previews for public profiles
- Supabase-backed LatchID authentication and notifications
- HTMX-enhanced studio navigation and product-area placeholders
- Stripe-backed billing and account provisioning

## Practical takeaway

When reading this codebase, treat the fork as a platform rewrite in progress rather than a lightly customized upstream install. Architectural choices increasingly assume:

- LatchID identity lives outside Laravel
- billing lives in Stripe
- Laravel coordinates internal state and product entitlements
- the studio is becoming a multi-product control surface
