# Affiliate Program Setup

This document describes the future affiliate stack for Livelatch. The intended external engine is [Refferq](https://github.com/Refferq/Refferq), which is an open-source affiliate management platform built with Next.js and PostgreSQL.

The current Livelatch repo only exposes a placeholder Studio route at `/studio/affiliate-program`. When affiliate work starts, that route should hand users off to a dedicated affiliate portal instead of trying to host the full affiliate system inside Laravel.

## Recommended shape

- Keep Livelatch as the product and billing application.
- Run Refferq as a separate affiliate service.
- Use a dedicated subdomain such as `affiliates.livelatch.com`.
- Pass only the minimum identity data needed for a handoff.
- Keep commission, referral, payout, and affiliate messaging logic inside Refferq.

## Refferq prerequisites

Refferq’s README currently expects:

- Node.js 18 or newer
- PostgreSQL 14 or newer
- npm, yarn, or pnpm
- Resend for transactional email
- Optional Stripe keys if the affiliate deployment needs payment-related features

The documented environment variables are:

```env
DATABASE_URL="postgresql://user:password@localhost:5432/refferq"
JWT_SECRET="your-super-secret-jwt-key-min-32-chars"
RESEND_API_KEY="re_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
RESEND_FROM_EMAIL="Refferq <onboarding@resend.dev>"
ADMIN_EMAILS="admin@yourdomain.com"
NEXT_PUBLIC_APP_URL="https://affiliates.livelatch.com"
STRIPE_SECRET_KEY="sk_test_..."
STRIPE_PUBLISHABLE_KEY="pk_test_..."
```

## Livelatch-side setup

When the affiliate rollout begins, add a small amount of Livelatch configuration so the handoff stays explicit and reversible:

- `AFFILIATE_APP_URL` for the Refferq portal base URL
- a signed or server-generated affiliate handoff token
- a stable mapping between the local Livelatch user and the Refferq affiliate account
- a return URL back to the Studio affiliate page after login or enrollment

The Livelatch side should not duplicate Refferq’s commission logic. It should only:

- show the affiliate entry point in Studio
- identify the current user
- create or look up the affiliate record
- send the user to Refferq
- bring them back after the handoff completes

## Suggested flow

1. User opens `/studio/affiliate-program`.
2. Livelatch checks whether the user already has an affiliate record.
3. If not, Livelatch generates a handoff payload for Refferq.
4. Refferq creates or loads the affiliate account.
5. Refferq shows the affiliate dashboard.
6. The user can return to Livelatch with a success state after onboarding.

## Open questions before implementation

- Whether affiliates should log in with Livelatch SSO or a separate Refferq account.
- Whether referrals should be attributed from product signup links, coupon codes, or both.
- Whether payout workflows stay inside Refferq or are partially mirrored into Livelatch.
- Whether the affiliate portal needs its own branding, domain, and notification templates.

## Source

- [Refferq/Refferq](https://github.com/Refferq/Refferq)
