# Data Collection Logic

<!-- todo-check
created_at: 2026-06-12T19:34:32+08:00
ask_after: 2026-07-03T22:29:45+08:00
status: open
-->

Confirm the actual production data collection, compliance, source availability, and account offboarding logic for Livelatch before the privacy and Manage My Data flows are treated as launch-ready.

## Owner Follow-Up

- Confirm which processors are used in production for auth, database, billing, email, media storage, analytics, support, and notifications.
- Confirm whether Livelatch will offer automated account export, deletion, correction, and social-connection disconnect workflows at MVP.
- Confirm retention periods for profile data, billing records, logs, notifications, media files, backups, and deleted accounts.
- Confirm the account deletion request flow and whether it sends email, creates an admin task, or starts automated offboarding.
- Confirm the public GitHub/source URL to use for AGPL-3.0 compliance and the Manage My Data `View source` button.
- Confirm the privacy contact mailbox and business/legal entity details.
- Review `docs/compliance/privacy.md` and `docs/compliance/tos.md` with legal or policy support before publication.

## Owner Answers (confirmed 2026-06-17)

- **Processors:** auth = Supabase; database = Railway-hosted MySQL (Laravel) plus some logic in Supabase; billing = Stripe; media = S3 bucket hosted on Railway (some media stays with Laravel); analytics = PostHog gathers click data, which is then stored in / retrieved from Supabase.
- **Email:** not yet configured; a solution still needs to be chosen.
- **Support:** currently handled via Discord (no active users yet). Pro plan will get a support portal, likely self-hosted initially.
- **Notifications:** approach not finalized; a Supabase-backed concept exists.
- **Automated rights flows at MVP:** no. Social connecting is user-driven in the dashboard (TikTok and Discord work today). GDPR requests start as email-to-owner via a button in "My Data".
- **Account deletion flow:** user presses button -> owner gets email -> owner asks why / attempts retention -> if declined, manual offboarding for now; automation planned for release.
- **View source (AGPL-3.0):** UI "bones" exist; the link/source logic still needs to be configured.
- **Privacy contact / legal entity:** a dedicated privacy email (alias to an existing mailbox) is planned; a business entity is not set up yet but will be before payments are enabled.

## Still Open

- Choose and configure a production email / transactional provider.
- Decide and document retention periods (still to be researched for GDPR).
- Wire the "View source" button to the public AGPL source URL.
- Set up the business entity and dedicated privacy mailbox before enabling payments.
- Get `privacy.md` and `tos.md` reviewed before publication.
