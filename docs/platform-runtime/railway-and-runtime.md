# Railway And Runtime

The first phase of the fork was primarily about getting Livelatch to boot, deploy, and recover cleanly in its target environment. The critical commits are `8e19376`, `faeb738`, `7c92287`, `b75d51d`, `3c7fde5`, `8de0844`, `7a27f03`, and `d602163`.

## What changed

- Composer dependencies were repaired and aligned with the fork's PHP and Laravel runtime needs
- `railpack.json` was added to support Railway builds
- Laravel configuration and route issues were corrected
- post-deploy Artisan errors were fixed
- clean redeploy behavior was used to recover the environment during setup

## Why this matters

Without this phase, later work such as Supabase auth, S3 media, and Stripe billing would have had no reliable deployment base. This is foundational platform work, not setup trivia.

## Ongoing operational expectations

- treat dependency changes as deployment-sensitive
- test route and config changes with Railway assumptions in mind
- keep boot-time and post-deploy behavior simple because this fork has already experienced runtime fragility during early setup

## Practical takeaway

If a new feature behaves inconsistently across local and deployed environments, revisit this layer first:

- config resolution
- build-time dependency behavior
- post-deploy Artisan expectations
- Railway-specific assumptions
