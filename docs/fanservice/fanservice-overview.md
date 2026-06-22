# Fan Service Layer — Concept & Principles

This document captures the early product thinking for Livelatch's **fan-service
layer**: the features that turn Livelatch from a card-collecting mechanic into a
fan-identity layer. It is a concept note, not an implementation spec — the
mechanics described here are still being shaped with the repo owner.

## Guiding principle: social mechanics, no scoreboard

The fan layer deliberately borrows social-media *mechanics* (following,
reacting) while removing the social-media *scoreboard*. The goal is for the
platform to **feel alive** without becoming social media.

The toxicity of social media does not come from the follow button — it comes
from **public, comparative, audience-facing metrics**: follower counts you are
judged by, likes as a popularity contest, feeds that reward performance. So the
design rule is: **keep the relationship, drop the scoreboard.**

Practical consequences:

- No public follower / following counts, and no leaderboards. A creator may see
  *that* (and how many) fans latched on; fans never see a ranking.
- No public follower/following lists on fan profiles.
- No algorithmic feed. The creator newsletter arrives because a fan opted in,
  not because an algorithm surfaced it.
- The fan showcase profile is a **personal trophy cabinet you can choose to
  share**, not a stage you are forced to perform on. It is about *what you
  love*, not *how many people love you*.

When evaluating any future fan feature, ask: *does this create a public status
game?* If yes, redesign it to keep the warmth/relationship but drop the
scoreboard.

## Feature 1 — "Latch On" (creator follow + Pro newsletter)

"Latch on" is the follow action, placed under the username on a creator profile.
Crucially it is **not just a follow** — it is a **Pro-creator service**: latching
on subscribes the fan to that creator's newsletter (opt-in mailing list).

- Pressing **Latch on** opens a modal that asks for the fan's email and clearly
  states that by latching on they are subscribing to the creator's newsletter.
  The signalling must make this an informed, consent-aware action — not a silent
  follow. This rides the existing consent-aware Resend pipeline (see
  `docs/email/email-preferences-and-resend.md`).
- A latch is best modelled as a consent record, not just a boolean — fan ↔
  creator, with an independently revocable email opt-in. Unlatching and email
  unsubscribe should be separable (a fan can stay latched but mute emails).
- **Premium feature:** Pro creators may send their latched fans a newsletter,
  capped at **one per month** (enforced server-side; calendar-month vs rolling
  window still to be decided).
- **Extra-send request (event exception):** a creator with an upcoming event can
  request **one extra newsletter** in the month. The request needs **≥48h lead
  time** (it is a *scheduled* send, and that window doubles as the approval
  window) and is **manually approved** — expected to be granted as an occasional
  one-off, with repeated requests scrutinised. Approval likely lands in LatchOps
  (operator tooling, separate repo). Implies an extra record, e.g.
  `newsletter_send_requests { creator_id, requested_for, reason, status
  (pending/approved/rejected), decided_by, decided_at }`, and the monthly cap
  check becomes `sends_this_month <= 1 + approved_extras`. **Slice 2 concern** —
  the latch itself (Slice 1) does not depend on it.

### Latch On — anonymous opt-in flow (double opt-in)

The public link-in-bio page is mostly seen by anonymous viewers, so anonymous
latching uses a **double opt-in** flow (confirm the email before any creator can
mail it — protects GDPR lawful basis and transactional deliverability):

1. Anon viewer hits **Latch on**, enters their email, submits.
2. A `creator_latches` row is created with `status = pending`, `fan_user_id =
   null`. The profile button flips to **"Check your email to confirm"** — not
   "Latched on", because they are not subscribed yet.
3. Livelatch sends a **transactional** "confirm your latch onto {creator}" email
   with a signed, single-use, **7-day** signup link to a dedicated latch-on
   signup page.
4. On that page they create a LatchID (Google or email OTP).
5. Account created → **reconcile** binds the pending row to the new
   `fan_user_id` by email → `status = active`. Subscription confirmed.
6. Later, at `my.livelatch.com` profile settings, they manage their latches
   (unlatch / mute the creator's emails).

**Logged-in shortcut:** a viewer already signed into LatchID skips steps 3–5 —
the row is created **active** immediately (email already confirmed, account
exists).

**Pending lifecycle / abandonment (auto-cleanup):**

- **Day 0** — confirmation email sent.
- **Day 3** — if still pending, a reminder email ("finish latching onto
  {creator}").
- **Day 7** — the token expires; send a final "sorry you didn't latch on to
  {creator} — we're deleting your email; latch on again any time at {creator's
  livelatch}" email, then **hard-delete the pending row** (email purged — data
  minimisation).

A creator newsletter only ever sends to **active** rows; pending addresses are
never exposed to a creator.

**Email classes:** the three lifecycle emails (confirm / reminder / sorry) are
**transactional** (sent regardless of any marketing consent, from the
transactional sender `hello@livelatch.com`).

The creator's actual newsletter is its **own consent category — not Livelatch
marketing.** A fan who opted out of Livelatch product news may still want a
creator's newsletter, and vice-versa, so the two are governed independently:

- Livelatch marketing = the global `user_email_preferences.marketing_opt_in`.
- Creator newsletter = the **per-creator** `creator_latches.email_opt_in` (one
  consent per latch, so a fan can mute one creator without affecting other
  creators or Livelatch marketing).

For sender reputation, creator newsletters should be their own send-class
(candidate: a `creators.livelatch.com` subdomain) so one creator's complaints
cannot poison Livelatch's own marketing or transactional reputation. Slice 2
concern. See `docs/email/email-preferences-and-resend.md` and the
marketing-subdomain todo.

## Feature 2 — Fan showcase profiles

Viewers get a private portal (redeem cards, read newsletters, settings) and a
**public, shareable showcase** — a gallery of their LatchDeck cards, stickers and
trophies, with selectable themes. It is a showcase of who they are fans of.

- Fans can pick a card they are proud of and write **journal entries** about what
  that stream or event meant to them. The journal entry attaches to a specific
  *owned card instance*, and each entry should support a public/private toggle.
- URL naming is open: the private portal under `my.livelatch.com`; the public
  profile under a shareable path (e.g. `livelatch.com/@username`) is preferred
  over the internal `my.livelatch.com/ld/[username]` form. **Still to confirm.**

## Feature 3 — "I was there too" reactions

To make the platform feel alive without a like-style scoreboard, fan profiles
carry an anonymous **"I was there too"** reaction.

- It is a **presence headcount**, anchored to the shared thing (an
  event / stream / card-drop), not to a person. Tapping it on any fan's profile
  who owns that card increments the *same* shared event counter.
- **Anonymous by design:** the identity of who tapped is stored privately for
  deduplication and abuse control only, and is **never exposed** — only the
  aggregate count is shown ("847 people were there too").
- Leaning **tap-once headcount** over a repeatable clap, to stay true to the
  anti-scoreboard stance.

## Status & open questions

This is an early concept. Open decisions tracked in
`docs/todos/fanservice-follow-up.md`:

- Public profile URL scheme (`livelatch.com/@username` vs `my.livelatch.com/ld/`).
- Newsletter cap window (calendar month vs rolling 30 days).
- Reaction model: tap-once headcount vs repeatable pulse.
- What else, beyond the newsletter, a Pro creator pays for (e.g. segmentation).

First implementation slice agreed: the **`latch` data model + "Latch on" button +
subscription modal**, since both the newsletter and the presence logic depend on
the fan ↔ creator/event relationships.
