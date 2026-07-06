# Alpha go-live — execution prompt

Copy-paste companion to [alpha-mvp-golive.pdf](alpha-mvp-golive.pdf) (section 9).
Paste the block below into a Claude Code session in `G:\repos\livelatch-dev-docker`
when ready to execute the go-live.

```text
Take fan.livelatch.com from mock to alpha, following the guide in
docs/fanservice/alpha-mvp-golive (read the PDF first). Work phase by
phase; at the end of each phase show me what changed and WAIT for my
go-ahead before starting the next phase.

MY DECISIONS (treat as fixed unless you flag a real blocker):
1. Auth: per-app callback at fan.livelatch.com/auth/callback behind the
   existing LatchID modal. Do NOT modify dev.livelatch.com's
   /callback/google or the Laravel session bridge in this task.
2. Railway: new service "fan-livelatch" inside the existing
   livelatch-dev-docker project. Do not touch the Laravel or MySQL
   services.
3. Encore: dedicated LATCHDECK_FAN_API_KEY (never reuse the Livelatch
   service key). Target staging first; we promote to a prod env after
   verification.
4. Identity: derive fan/creator status from capability rows
   (latchdeck_fans / latchdeck_creators). Never write
   profiles.user_type from the fan flow. Fans must never create rows
   in Laravel MySQL.

PHASE 1 - Encore (G:\repos\ld\latchdeck):
- Additive migration: latchdeck_fans; latchdeck_card_instances with
  UNIQUE (card_id, serial_no) and UNIQUE (card_id, owner_id);
  latchdeck_ownership_events (append-only).
- Endpoints: POST /redeem (transactional serial allocation, typed
  errors: invalid_code/closed/sold_out/already_owned), POST /fans
  (username claim, checked against username_blacklist and
  impersonation_names), GET /fans/by-username/:username,
  GET /fans/:latchid_user_id/collection.
- Add LATCHDECK_FAN_API_KEY handling to auth.ts. Rate-limit /redeem
  per user and per IP.
- CHECK: show me the full migration SQL and STOP for my explicit OK
  before applying anything to the live LatchID Supabase project.

PHASE 2 - Fan app live wiring (G:\repos\fan-livelatch):
- Implement lib/latchdeck/encore.ts against the Phase 1 endpoints
  (server-side Bearer key only; never NEXT_PUBLIC).
- Implement lib/auth/session.ts with @supabase/ssr (Google + email
  OTP) and an /auth/callback route; the LatchID modal stays the only
  sign-in UI. Passkeys are post-alpha.
- Keep APP_DATA_MODE=mock as the default; live mode only via env.
- PostHog project 201558 (EU, cookieless): fan_signed_up,
  card_redeemed, cabinet_viewed. Create user_email_preferences rows
  (all off) on fan signup.
- CHECK: demo the full flow to me locally against Encore staging
  before we deploy anywhere.

PHASE 3 - Deploy. These are MANUAL steps that are mine; ask me to
confirm each one as you reach it, do not attempt them yourself:
[ ] I have created the private GitHub repo and pushed fan-livelatch
    (I do my own commits/pushes via GitHub Desktop).
[ ] I have connected the repo as a new Railway service and you may
    set its variables.
[ ] I have added https://fan.livelatch.com/** to the Supabase Auth
    redirect allowlist (ask me whether I want the *.livelatch.com
    wildcard instead - my call).
[ ] I have set LATCHDECK_FAN_API_KEY in Encore Cloud secrets.
[ ] I have added the DNS CNAME for fan.livelatch.com.

PHASE 4 - Verify before you call it done:
- Fresh test account on the deployed URL: code -> sign-in -> username
  claim -> mint with correct serial -> public cabinet.
- All four redeem error states, live.
- Laravel MySQL users table row count unchanged by the fan flow.
- PostHog events arriving; supabase get_advisors reports nothing new.

HARD SAFETY RAILS (non-negotiable):
- Encore stays the single writer of latchdeck_* tables.
- Additive-only DB changes; no destructive migrations; no RLS changes
  to existing tables; no edits to existing latchdeck_* columns.
- Zero changes under dev.livelatch.com (auth, routes, Stripe).
- If anything fails against the live Supabase project, stop and
  report - do not retry with variations.
- Log the work in summary.md per repo convention when finished.
```
