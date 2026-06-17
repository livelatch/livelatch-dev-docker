# Agent Summary

This page is the readable companion to `summary.md`. It turns the fork history into a short, browsable digest for people who want the shape of the project without reading the full change log.

The rule is simple:

- when `summary.md` changes, update this page too
- keep it high level
- favor clear bullet points over deep implementation detail
- mirror each date that appears in `summary.md`

## Timeline Digest

### 2026-06-17

- Development moved from ChatGPT/Codex to Claude, starting with this session.
- The full project documentation was reviewed first to get up to speed before making any changes.
- The owner todo list was cleaned up, and finished items were closed: Discord login, the TikTok connection fix, live click analytics, and the theme catalogue.
- A few todos were kept open with clearer notes explaining why, including a YouTube setup step that is waiting on Google's review.
- The public homepage was redesigned to match the live app's clean, light, Poppins look and to explain Livelatch in plain language, using the product logos as section headings and keeping the live @alex2 page as a demo.
- Fixed the light/dark mode toggle so the background can no longer get stuck, and stopped the Dev Tools preview from leaking onto the live site.
- Gave the dashboard a proper line chart for clicks and rebuilt the Appearance page to match the rest of the new Studio.
- Reworked Dev Tools so light and dark themes can be designed side by side, and added an optional "liquid glass" look that can be switched on or off (and removed cleanly later).

### 2026-06-13

- The README was rewritten to reflect the current stack and service map.
- A new admin development timeline page was added to explain the platform history and core technologies.
- An affiliate-program doc was added around the planned Refferq integration.
- The TikTok LatchID flow was fixed so user data is parsed correctly and the callback returns to Livelatch.

### 2026-06-12

- Studio navigation was rebuilt around HTMX and Livewire sections.
- Dashboard and admin surfaces gained more product-like layouts and tooling.
- Theme, block, documentation, and manage-my-data flows were expanded so the studio feels closer to a full platform.

### 2026-06-11

- Theme settings moved toward a real browser-editable MVP instead of a static placeholder.
- Public profile rendering now reflects saved theme presets more directly.
- The theme docs were expanded to explain the current and future configuration path.

### 2026-06-10

- The Stripe foundation was formalized so every user can exist in billing from signup onward.
- LatchID and Supabase identity work became the core of the app's onboarding model.
- Documentation and repo conventions were tightened so the fork history stays usable.

### 2026-06-07

- The sidebar and notification work continued as part of the larger Studio overhaul.
- Light and dark mode controls were introduced at the layout level.
- Supabase notifications were present in the backend but still incomplete in the website UI.

### 2026-06-06

- Livelatch resumed after a pause and the Supabase project was restored.
- Google auth settings were fixed so identity flows could continue.
- The project direction moved further away from stock LinkStack behavior.

### 2026-05-18

- The first real LatchID bridge was added between Supabase and Laravel.
- Google login became the initial supported identity path.
- The app began treating Supabase user IDs as a local bridge field.

### 2026-05-16

- The homepage demo was expanded with more polished marketing-style sections.
- Light and dark mode handling was added to the public landing concept.
- This was still exploratory UI work rather than a production feature set.

### 2026-05-14

- Private S3-backed profile media and Open Graph rendering were added.
- The repo moved toward proxying media instead of exposing raw storage credentials.
- The documentation and metadata around preview generation were expanded.

### 2026-05-12

- The fork started by stabilizing runtime and deployment behavior.
- LinkStack-specific assumptions were being replaced with a Livelatch identity and deployment model.
- Early fixes focused on routes, composer alignment, and Railway readiness.

## Maintenance Note

If `summary.md` gains a new dated entry, add a matching dated section here with a few high-level bullets that a non-developer can skim quickly.
